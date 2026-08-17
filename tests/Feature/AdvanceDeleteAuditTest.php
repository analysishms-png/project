<?php

namespace Tests\Feature;

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Advance-deleted path audit regression tests (QA session 2026-08-17).
 *
 * Chain audited: deleteadvancedeposit → paychargelog → advreconreportfetch
 * DelAmount linkage.
 *
 * BUG-QA-011 (P1, found via browser walkthrough): openupdatereservation /
 * openwalkin (updatewalkin) passed a closure to MasterDataCache::availableRooms
 * that referenced the foreach variable `$row` without capturing it in use(...).
 * The closure runs inside Cache::remember, so on cache miss the page 500'd with
 * "Undefined variable $row" — the reservation-edit page (which hosts the
 * advance-delete button) was completely broken. Fix: capture the room category
 * as a plain scalar ($roomCat) and use it inside the closure.
 *
 * BUG-QA-012 (P2): deleteguestledger wrote its paychargelog audit row WITHOUT
 * refdocid and amtcr — the two fields the reconciliation report's DelAmount
 * subquery needs to link a deletion to its booking. 466 ADRES/ARRES deletions
 * on live data are invisible to the report because of this. Fix: copy
 * refdocid + amtcr (+ paytype), scope the log fetch by vtype (was: by vno only,
 * so it could log rows of a DIFFERENT vtype sharing the vno), and wrap log
 * insert + delete in one transaction.
 *
 * All assertions are structural or read-only against the live DB; skipped when
 * the DB is unreachable.
 */
class AdvanceDeleteAuditTest extends TestCase
{
    private function sourceOf(string $methodName): string
    {
        $rc = new ReflectionClass(CompanyController::class);
        $rm = $rc->getMethod($methodName);
        $start = $rm->getStartLine();
        $end = $rm->getEndLine();
        $lines = file($rc->getFileName());
        return implode('', array_slice($lines, $start - 1, $end - $start + 1));
    }

    public function test_available_rooms_closure_captures_room_cat_in_updatereservation(): void
    {
        $src = $this->sourceOf('openupdatereservation');

        // The closure must capture $roomCat and use it inside (not the loop var $row).
        $this->assertStringContainsString(
            "function () use (\$checkindate, \$previousdate, \$roomCat)",
            $src,
            'availableRooms closure in openupdatereservation must capture $roomCat'
        );
        $this->assertStringContainsString(
            "->where('room_cat', \$roomCat)",
            $src,
            'closure body must reference the captured $roomCat'
        );
        $this->assertStringNotContainsString(
            "function () use (\$checkindate, \$previousdate) {\n                return DB::table('room_mast')",
            $src,
            'no uncaptured-$row closure may remain in openupdatereservation'
        );
    }

    public function test_available_rooms_closure_captures_room_cat_in_updatewalkin(): void
    {
        $src = $this->sourceOf('openupdatewalkin');

        $this->assertStringContainsString(
            "function () use (\$checkindate, \$previousdate, \$roomCat)",
            $src,
            'availableRooms closure in openwalkin must capture $roomCat'
        );
        $this->assertStringContainsString(
            "->where('room_cat', \$roomCat)",
            $src,
            'closure body must reference the captured $roomCat'
        );
    }

    public function test_deleteguestledger_audit_row_keeps_refdocid_and_amtcr(): void
    {
        $src = $this->sourceOf('deleteguestledger');

        // The audit row must preserve the reservation link + credit amount,
        // otherwise the deletion is invisible to the report's DelAmount.
        $this->assertStringContainsString("'refdocid' => \$existingrows->refdocid,", $src);
        $this->assertStringContainsString("'amtcr' => \$existingrows->amtcr,", $src);
        $this->assertStringContainsString("'paytype' => \$existingrows->paytype,", $src);
    }

    public function test_deleteguestledger_log_fetch_is_scoped_by_vtype(): void
    {
        $src = $this->sourceOf('deleteguestledger');

        // The log fetch must match the delete scope (vno + vtype) so the audit
        // trail only records rows that are actually deleted.
        $this->assertStringContainsString(
            "->where('vno', \$dataid)\n                    ->where('vtype', \$datavalue)\n                    ->get();",
            $src
        );
    }

    public function test_deleteguestledger_is_transactional(): void
    {
        $src = $this->sourceOf('deleteguestledger');

        $this->assertStringContainsString('DB::beginTransaction();', $src);
        $this->assertStringContainsString('DB::commit();', $src);
        $this->assertStringContainsString('DB::rollBack();', $src);
    }

    public function test_deleteadvancedeposit_audit_row_is_complete(): void
    {
        $src = $this->sourceOf('deleteadvancedeposit');

        // The audited path must preserve every linkage field the report needs.
        foreach (["'refdocid' => \$row->refdocid,", "'amtcr' => \$row->amtcr,", "'folionodocid' => \$row->folionodocid,", "'u_ae' => 'e',"] as $needle) {
            $this->assertStringContainsString($needle, $src);
        }
        $this->assertStringContainsString('DB::beginTransaction();', $src);
        $this->assertStringContainsString('DB::commit();', $src);
    }

    public function test_reconcile_del_amount_links_deleted_advance_on_live_data(): void
    {
        try {
            $pdo = DB::connection()->getPdo();
            $pdo->query('SELECT 1')->fetchColumn();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Live DB unreachable');
            return;
        }

        // The report links a deleted advance to its booking through the
        // paychargelog refdocid (condition 1) or the docid of a still-existing
        // ADRES/ARRES paycharge row for the booking (condition 2). Invariant:
        // for any booking, DelAmount must exactly equal the deleted ADRES/ARRES
        // amount with refdocid = booking (i.e. deletions are never invisible
        // and never double counted).
        $deleted = DB::table('paychargelog AS PL')
            ->join('booking AS B', 'B.DocId', '=', 'PL.refdocid')
            ->where('PL.vtype', 'ADRES')
            ->where('PL.amtcr', '>', 0)
            ->select('B.DocId', 'B.Property_ID')
            ->groupBy('B.DocId', 'B.Property_ID')
            ->orderByDesc('B.DocId')
            ->first();

        if (!$deleted) {
            $this->markTestSkipped('No refdocid-linked ADRES deletion found on live DB (audited delete path never run here)');
            return;
        }

        $docid = $deleted->DocId;
        $pid = (int) $deleted->Property_ID;

        $resAdv = (float) DB::table('paycharge')
            ->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])
            ->selectRaw('COALESCE(SUM(amtcr - amtdr), 0) AS total')->value('total');
        $folioAdv = (float) DB::table('paycharge')
            ->where('propertyid', $pid)->where('refdocid', $docid)
            ->whereNotNull('folionodocid')->where('folionodocid', '<>', '')->where('vtype', '<>', 'REV')
            ->selectRaw('COALESCE(SUM(amtcr - amtdr), 0) AS total')->value('total');
        $delAmount = (float) DB::table('paychargelog')
            ->where('propertyid', $pid)
            ->where(function ($q) use ($docid, $pid) {
                $q->where('refdocid', $docid)
                    ->orWhereIn('docid', DB::table('paycharge')->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])->pluck('docid'));
            })
            ->selectRaw('COALESCE(SUM(COALESCE(amtcr,0) - COALESCE(amtdr,0)), 0) AS total')->value('total');

        $expectedDeleted = (float) DB::table('paychargelog')
            ->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])
            ->selectRaw('COALESCE(SUM(COALESCE(amtcr,0) - COALESCE(amtdr,0)), 0) AS total')->value('total');

        // Every refdocid-linked ADRES deletion must be counted by DelAmount
        // exactly once — never invisible, never double counted.
        $this->assertEqualsWithDelta($expectedDeleted, $delAmount, 0.01, 'DelAmount must capture every linked deletion');
        $this->assertEqualsWithDelta($resAdv - $folioAdv - $delAmount, $resAdv - $folioAdv - $expectedDeleted, 0.01, 'Recon consistent with deletion ledger');
    }

    public function test_report_del_amount_subqueries_reject_uncaptured_row_closures(): void
    {
        // Sanity: the reconcile report itself must not reference $row inside
        // any cache closure (it doesn't use availableRooms, but guard the chain).
        $rc = new ReflectionClass(\App\Http\Controllers\Reporting::class);
        $src = file($rc->getFileName());
        $joined = implode('', $src);
        $this->assertStringNotContainsString('availableRooms(', $joined);
    }
}
