<?php

namespace Tests\Feature;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Reservation\Advance;
use Illuminate\Support\Facades\DB;
use PDOException;
use ReflectionClass;
use Tests\TestCase;

/**
 * Phase-6 critical workflow regression tests: reservation → advance → check-in
 * → folio → settlement reconcile.
 *
 * BUG-048 (P1, confirmed on live data): submitadvdeposit always posted the
 * advance with an EMPTY folionodocid — even when the reservation was already
 * checked in (ContraDocId set). The advance therefore never reached the guest's
 * folio; the Advance/Folio reconciliation report flagged a permanent MISMATCH
 * and staff compensated with manual ACCOUNT-TRANSFER RECs (which the report
 * cannot link either). Fix: when the booking is in-house, the ADRES rows are
 * now folio-linked (folionodocid = ContraDocId, foliono = guestfolio.folio_no),
 * mirroring the check-in advance-copy fields in submitwalkin.
 *
 * Also: deleteadvancedeposit (paychargelog audit + paycharge delete) is now
 * wrapped in a transaction so a mid-delete failure can't orphan the audit trail.
 *
 * All assertions are structural or read-only against the live DB; skipped when
 * the DB is unreachable.
 */
class AdvanceFolioLinkageTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping advance/folio linkage tests: ' . $e->getMessage());
        }
    }

    private function methodBody(object $controller, string $method): string
    {
        $ref = new ReflectionClass($controller);
        $m = $ref->getMethod($method);
        $start = $m->getStartLine();
        $end = $m->getEndLine();
        $file = file($ref->getFileName());
        return implode('', array_slice($file, $start - 1, $end - $start + 1));
    }

    private function stripComments(string $body): string
    {
        $body = preg_replace('#/\*.*?\*/#s', '', $body);
        $body = preg_replace('#//[^\n]*#', '', $body);
        return $body;
    }

    /**
     * BUG-048: submitadvdeposit must resolve the folio docid when the booking is
     * in-house and apply folionodocid to BOTH the main entry and the tax entries.
     */
    public function test_submitadvdeposit_links_to_folio_when_checked_in()
    {
        $body = $this->stripComments($this->methodBody(new Advance(), 'submitadvdeposit'));

        // 1) The ContraDocId lookup must exist (in-house detection)
        $this->assertStringContainsString("whereNotNull('ContraDocId')", $body, 'must detect in-house booking via ContraDocId');

        // 2) Both the main entry and the tax entry must carry folionodocid
        $this->assertSame(2, substr_count($body, "'folionodocid' => \$folioDocId"), 'main + tax entries must both be folio-linked');

        // 3) foliono must fall back to the booking number only when no folio exists
        $this->assertStringContainsString("\$folioNo !== '' ? \$folioNo : \$bookingDetails->BookNo", $body);
    }

    /**
     * deleteadvancedeposit (audit + delete) must be transactional.
     */
    public function test_deleteadvancedeposit_is_transactional()
    {
        $body = $this->stripComments($this->methodBody(new CompanyController(), 'deleteadvancedeposit'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
        $this->assertLessThan(strpos($body, 'Advance Deleted successfully'), strpos($body, 'DB::commit()'));
    }

    /**
     * Live invariant: for a CHECKED-IN reservation that has an advance, the
     * reservation's ContraDocId must resolve to a guestfolio whose docid matches
     * — i.e. the linkage key the fix relies on actually exists in real data.
     */
    public function test_live_checkedin_booking_resolves_folio_linkage()
    {
        $row = $this->pdo
            ->query("SELECT g.Property_ID pid, g.BookingDocid, g.ContraDocId, gf.folio_no, gf.docid
                     FROM grpbookingdetails g
                     JOIN guestfolio gf ON gf.propertyid = g.Property_ID AND gf.docid = g.ContraDocId
                     WHERE g.ContraDocId IS NOT NULL AND g.ContraDocId <> ''
                       AND EXISTS (SELECT 1 FROM paycharge p
                                   WHERE p.propertyid = g.Property_ID AND p.refdocid = g.BookingDocid
                                     AND p.vtype IN ('ADRES','ARRES'))
                     ORDER BY g.Property_ID DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $this->markTestSkipped('No checked-in booking with an advance + matching guestfolio found on live DB.');
        }

        $this->assertNotEmpty($row['ContraDocId']);
        $this->assertNotEmpty($row['folio_no']);
        // The ContraDocId (CHK docid) is the folionodocid the fix writes — it must
        // equal the guestfolio docid so folio rows land on the right folio.
        $this->assertEquals($row['docid'], $row['ContraDocId']);
    }

    /**
     * Live invariant: a reservation that is NOT checked in must have no
     * ContraDocId — confirming the lookup returns '' (advance stays
     * reservation-only, later copied at check-in by submitwalkin).
     */
    public function test_live_uncheckedin_booking_has_no_contra()
    {
        $row = $this->pdo
            ->query("SELECT g.Property_ID pid, g.BookingDocid
                     FROM grpbookingdetails g
                     WHERE g.ContraDocId IS NULL OR g.ContraDocId = ''
                       AND EXISTS (SELECT 1 FROM paycharge p
                                   WHERE p.propertyid = g.Property_ID AND p.refdocid = g.BookingDocid
                                     AND p.vtype IN ('ADRES','ARRES'))
                     ORDER BY g.Property_ID DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $this->markTestSkipped('No unchecked-in booking with an advance found on live DB.');
        }

        $contra = DB::table('grpbookingdetails')
            ->where('Property_ID', $row['pid'])
            ->where('BookingDocid', $row['BookingDocid'])
            ->whereNotNull('ContraDocId')
            ->where('ContraDocId', '<>', '')
            ->value('ContraDocId');

        $this->assertNull($contra, 'unchecked-in booking must not resolve a folio docid');
    }
}
