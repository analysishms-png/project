<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * Check-in / Check-out regression tests.
 *
 * These tests assert DATA-MODEL INVARIANTS that the check-in / check-out flows
 * must maintain. They are READ-ONLY (SELECT only) and run against the live
 * database; they are skipped when the DB is unreachable so the unit-only
 * suite (helpers/config) still passes on machines without the DB.
 *
 * Invariants:
 *  - INV-1 (BUG-033 regression): no two ADRES receipts may share a docid/vno
 *    after the vno off-by-one fix (2026-08-16).
 *  - INV-2 (BUG-034 regression): CHK advance rows' msno1 must equal the leader
 *    roomocc sno1 (a room change must not clobber the leader grouping).
 *  - INV-3: every CHK charge's folionodocid must have a roomocc row.
 *  - INV-4: every CHK charge's refdocid must link to a booking row.
 *  - INV-5: folio advance transferred (CHK) must not exceed reservation
 *    advance (ADRES/ARRES) — never duplicate a payment.
 *  - INV-6: a checked-out roomocc (type='O') must carry a checkout date.
 *
 * Known historical exceptions are documented inline; only NEW violations fail.
 */
class CheckInOutRegressionTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    /** Cutoff: anything entered before this is historical, not a regression. */
    private const FIX_DATE = '2026-08-16 00:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping read-only invariants: ' . $e->getMessage());
        }
    }

    /**
     * INV-1 — BUG-033 regression: ADRES voucher collisions after the fix.
     * The vno off-by-one (Api/Reservation.php, ChannelPublic.php) was fixed on
     * 2026-08-16; any (docid, vno) pair with >1 row created AFTER the fix is a
     * regression. 5 historical collided docids / 10 rows predate the fix.
     */
    public function test_no_new_adres_docid_collisions_since_fix()
    {
        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM (
                        SELECT docid, vno
                        FROM paycharge
                        WHERE vtype = 'ADRES'
                          AND u_entdt >= '" . self::FIX_DATE . "'
                        GROUP BY docid, vno
                        HAVING COUNT(*) > 1
                     ) t")
            ->fetchColumn();

        $this->assertEquals(0, (int) $count, 'New duplicate ADRES docid/vno detected since the 2026-08-16 fix (BUG-033 regression).');
    }

    /**
     * INV-2 — BUG-034 regression: CHK msno1 must match the leader room.
     * submitroomchange used `if ($olddata->leaderyn = 'Y')` (assignment), so
     * EVERY room change rewrote msno1 on all folio paycharges. Fixed with `==`.
     * One historical corrupt row exists (109CHK|2026|152, msno1=2 vs leader=6);
     * no NEW mismatches may appear.
     */
    public function test_no_new_chk_msno1_leader_mismatches_since_fix()
    {
        $new = $this->pdo
            ->query("SELECT COUNT(*) FROM paycharge pc
                     JOIN roomocc ro
                       ON ro.docid = pc.docid
                      AND ro.leaderyn = 'Y'
                      AND ro.sno = 1
                     WHERE pc.vtype = 'CHK'
                       AND pc.msno1 IS NOT NULL
                       AND pc.msno1 <> ''
                       AND pc.msno1 <> ro.sno1
                       AND pc.u_entdt >= '" . self::FIX_DATE . "'")
            ->fetchColumn();

        $this->assertEquals(0, (int) $new, 'New CHK msno1/leader mismatch since the 2026-08-16 fix (BUG-034 regression).');
    }

    /**
     * INV-3 — every CHK folio charge must trace to a roomocc row.
     * A CHK row whose folionodocid has no roomocc indicates a check-in that
     * created paycharge rows without (or after deleting) the occupancy.
     */
    public function test_no_orphan_chk_folio_charges()
    {
        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM paycharge pc
                     LEFT JOIN roomocc ro ON ro.docid = pc.folionodocid
                     WHERE pc.vtype = 'CHK'
                       AND ro.docid IS NULL
                       AND pc.u_entdt >= '2026-01-01 00:00:00'")
            ->fetchColumn();

        $this->assertEquals(0, (int) $count, 'Orphan CHK folio charge (folionodocid without roomocc) detected.');
    }

    /**
     * INV-4 — every CHK charge must link back to a booking (reservation).
     * At check-in, CHK rows carry refdocid = reservation DocId; a missing
     * booking row means the reservation link was lost.
     */
    public function test_no_chk_charges_without_booking()
    {
        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM paycharge pc
                     LEFT JOIN booking b ON b.DocId = pc.refdocid
                     WHERE pc.vtype = 'CHK'
                       AND b.DocId IS NULL
                       AND pc.u_entdt >= '2026-01-01 00:00:00'")
            ->fetchColumn();

        $this->assertEquals(0, (int) $count, 'CHK charge without a linked booking row detected.');
    }

    /**
     * INV-5 — folio advance must never exceed reservation advance.
     * Check-in transfers reservation advance (ADRES/ARRES) onto the folio as
     * CHK rows; the transfer must not create money (never duplicate a payment).
     * Historical rounding noise (±0.08) and 2 legacy anomalies predate 2026-08;
     * assert on rows created after the reconciliation work.
     */
    public function test_folio_advance_never_exceeds_reservation_advance()
    {
        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM (
                        SELECT pc.refdocid,
                               SUM(CASE WHEN pc.vtype = 'CHK' THEN pc.amtcr ELSE 0 END) AS chk,
                               SUM(CASE WHEN pc.vtype IN ('ADRES','ARRES') THEN pc.amtcr ELSE 0 END) AS adres
                        FROM paycharge pc
                        WHERE pc.refdocid IS NOT NULL
                          AND pc.refdocid <> ''
                          AND pc.u_entdt >= '2026-08-01 00:00:00'
                        GROUP BY pc.refdocid
                        HAVING chk > adres + 1.00
                     ) t")
            ->fetchColumn();

        $this->assertEquals(0, (int) $count, 'Folio advance (CHK) exceeds reservation advance (ADRES/ARRES) — duplicate/missing payment risk.');
    }

    /**
     * INV-6 — checked-out roomocc rows must carry a checkout date.
     * submitRoomSettle sets type='O' + chkoutdate together; a type='O' row
     * without a date signals a half-completed checkout. Two historical rows
     * (115CHK|2026|166 room 102, 157CHK|2026|360 room 202) predate the sweep.
     */
    public function test_no_checked_out_room_without_checkout_date_since_fix()
    {
        $new = $this->pdo
            ->query("SELECT COUNT(*) FROM roomocc
                     WHERE type = 'O'
                       AND (chkoutdate IS NULL OR chkoutdate = '' OR chkoutdate = '0000-00-00')
                       AND u_entdt >= '" . self::FIX_DATE . "'")
            ->fetchColumn();

        $this->assertEquals(0, (int) $new, 'Checked-out room (type=O) without a checkout date since the 2026-08-16 sweep.');
    }
}
