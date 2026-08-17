<?php

namespace Tests\Feature;

use App\Helpers\MasterDataCache;
use App\Http\Controllers\Api\InhouseRoomGet;
use App\Http\Controllers\NightAudit\Reports\DailyReport;
use App\Services\DailyReportSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * Query-count regression tests for the PERF-02 batching fixes.
 *
 * These tests assert the QUERY COUNT (not wall-clock time — flaky) of the
 * hot list/report paths after per-row lookups were batched into grouped
 * queries. They run READ-ONLY against the live database (no RefreshDatabase —
 * never wipe real data) and are skipped when the DB is unreachable.
 *
 * Baselines measured on the live DB (property 135, 2026-06-27):
 *   - Daily Report (dailyreportfetch):  224 queries  -> 66
 *   - reservedrooms (in-house list):    880 queries  -> 5
 *
 * The thresholds below have wide headroom: the batched implementations are
 * O(1) in entity count, while the pre-fix loops scaled 4x per revenue code
 * and 3x per department x category cell. A regression back to per-row
 * lookups blows past the bound as soon as the dataset exceeds a few rows.
 */
class PerformanceEagerLoadTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping read-only query-count tests: ' . $e->getMessage());
        }
    }

    /**
     * Find a property that actually uses the Daily Report (FOM charge codes).
     * Returns [propertyid, sample date with paycharge rows] or null.
     */
    private function findDailyReportFixture(): ?array
    {
        $row = $this->pdo
            ->query("SELECT r.propertyid, MAX(p.vdate) AS sample_date
                     FROM revmast r
                     JOIN paycharge p ON p.propertyid = r.propertyid
                     WHERE r.flag_type = 'FOM'
                       AND r.field_type = 'C'
                     GROUP BY r.propertyid
                     ORDER BY COUNT(DISTINCT r.rev_code) DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$row || empty($row['propertyid'])) {
            return null;
        }

        return [(int) $row['propertyid'], $row['sample_date']];
    }

    public function test_daily_report_query_count_is_batched(): void
    {
        $fixture = $this->findDailyReportFixture();
        if ($fixture === null) {
            $this->markTestSkipped('No property with FOM charge codes found — cannot exercise the Daily Report.');
        }
        [$propertyid, $fordate] = $fixture;

        // Mock the snapshot service so the fetch is read-only (no insert).
        $mockSnapshot = $this->createMock(DailyReportSnapshotService::class);
        $mockSnapshot->method('buildPayload')->willReturn(['mocked' => true]);
        $mockSnapshot->method('storeSnapshot')->willReturn('mock-key');

        $ctrl = app()->make(DailyReport::class);
        $ref = new \ReflectionClass($ctrl);
        foreach (['propertyid' => $propertyid, 'username' => 'perf-test', 'email' => 'perf-test@example.com'] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $req = Request::create('/dailyreportfetch', 'POST', ['fordate' => $fordate]);
        $out = $ctrl->dailyreportfetch($req, $mockSnapshot);

        // Must produce a report (not the "no revenue codes" error path).
        $this->assertIsString($out, 'dailyreportfetch did not return JSON.');
        $data = json_decode($out, true);
        $this->assertNotNull($data, 'dailyreportfetch returned invalid JSON.');
        $this->assertArrayNotHasKey('error', $data, 'Daily Report errored on fixture data: ' . ($data['error'] ?? ''));

        $this->assertLessThanOrEqual(
            120,
            $count,
            "Daily Report issued {$count} queries — expected <= 120. " .
            'A regression to per-revcode/per-cell loops would scale past this bound (was 224 before the PERF-02 batching).'
        );
    }

    public function test_lookuproomtype_daily_counts_are_batched(): void
    {
        $row = $this->pdo
            ->query("SELECT propertyid FROM room_cat WHERE inclcount = 'y' GROUP BY propertyid ORDER BY COUNT(*) DESC LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No room categories with inclcount=y found — cannot exercise lookuproomtypefetch.');
        }
        $propertyid = (int) $row['propertyid'];

        // Find a fromdate with activity for this property.
        $sample = $this->pdo
            ->query("SELECT MIN(ArrDate) d FROM grpbookingdetails WHERE Property_ID = $propertyid AND Cancel = 'N'")
            ->fetchColumn();
        $fromdate = $sample ? substr($sample, 0, 10) : date('Y-m-d');

        $ctrl = app()->make(\App\Http\Controllers\Reporting::class);
        $ref = new \ReflectionClass($ctrl);
        foreach (['propertyid' => $propertyid, 'username' => 'perf-test', 'email' => 'perf-test@example.com'] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $req = Request::create('/lookuproomtypefetch', 'POST', ['fromdate' => $fromdate, 'resstatus' => 'all']);
        $out = $ctrl->lookuproomtypefetch($req);

        $this->assertTrue($out->status() < 500, 'lookuproomtypefetch returned an error response.');
        $this->assertLessThanOrEqual(
            20,
            $count,
            "lookuproomtypefetch issued {$count} queries — expected <= 20. " .
            'A regression to per-category x per-day queries would blow past this bound (was 310 before the PERF-02 batching).'
        );
    }

    public function test_roominventory_balances_are_batched(): void
    {
        $row = $this->pdo
            ->query("SELECT propertyid FROM roomocc WHERE chkoutdate IS NULL GROUP BY propertyid ORDER BY COUNT(*) DESC LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No currently-occupied rooms found — cannot exercise roominventoryfetch.');
        }
        $propertyid = (int) $row['propertyid'];

        $ctrl = app()->make(\App\Http\Controllers\Reporting::class);
        $ref = new \ReflectionClass($ctrl);
        foreach (['propertyid' => $propertyid, 'username' => 'perf-test', 'email' => 'perf-test@example.com'] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $req = Request::create('/roominventoryfetch', 'POST', ['status' => 'OCCUPIED']);
        $out = $ctrl->roominventoryfetch($req);

        $this->assertTrue($out->status() < 500, 'roominventoryfetch returned an error response.');
        $this->assertLessThanOrEqual(
            20,
            $count,
            "roominventoryfetch issued {$count} queries — expected <= 20. " .
            'A regression to the per-room balance/advance lookups would blow past this bound (was 110 before the PERF-02 batching).'
        );
    }

    public function test_reservedrooms_advance_lookup_is_batched(): void
    {
        $row = $this->pdo
            ->query("SELECT Property_ID FROM grpbookingdetails WHERE Cancel = 'N' GROUP BY Property_ID ORDER BY COUNT(*) DESC LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No grpbookingdetails rows found — cannot exercise reservedrooms.');
        }
        $propertyid = (int) $row['Property_ID'];

        $ctrl = app()->make(InhouseRoomGet::class);

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $req = Request::create('/api/reservedrooms', 'GET');
        $req->attributes->set('api_client', (object) ['propertyid' => $propertyid]);
        $out = $ctrl->reservedrooms($req);

        $this->assertTrue($out->status() < 500, 'reservedrooms returned an error response.');

        $this->assertLessThanOrEqual(
            50,
            $count,
            "reservedrooms issued {$count} queries — expected <= 50. " .
            'A regression to the per-row advance lookup would scale with the room count (was 880 before the PERF-02 batching).'
        );
    }

    public function test_getroomswalkin_availability_is_cached(): void
    {
        $row = $this->pdo
            ->query("SELECT propertyid FROM room_mast GROUP BY propertyid ORDER BY COUNT(*) DESC LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No room_mast rows found — cannot exercise getRoomswalkin.');
        }
        $propertyid = (int) $row['propertyid'];
        $cat = $this->pdo->query("SELECT room_cat FROM room_mast WHERE propertyid = $propertyid LIMIT 1")->fetchColumn();
        if (!$cat) {
            $this->markTestSkipped('No room category found for fixture property.');
        }

        $ctrl = app()->make(\App\Http\Controllers\RoomController::class);
        $ref = new \ReflectionClass($ctrl);
        foreach (['username' => 'perf-test', 'email' => 'perf-test@example.com', 'propertyid' => $propertyid] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        $req = Request::create('/getroomswalkin', 'POST', [
            'cid' => $cat,
            'checkindate' => date('Y-m-d'),
            'checkoutdate' => date('Y-m-d', strtotime('+2 days')),
        ]);

        // One listener for all three phases; reset the counter between phases.
        // (Registering multiple listeners would multiply the count — each
        // listener captures the same &$count reference.)
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        // Cold: flush availability, expect exactly 1 query (the cache miss).
        MasterDataCache::flushAvailability($propertyid);
        $count = 0;
        $html1 = $ctrl->getRoomswalkin($req);
        $this->assertIsString($html1, 'getRoomswalkin did not return HTML.');
        $this->assertEquals(1, $count, 'Cold availability lookup should issue exactly 1 query.');

        // Warm: same request must be served from cache (0 queries), byte-identical.
        $count = 0;
        $html2 = $ctrl->getRoomswalkin($req);
        $this->assertEquals(0, $count, 'Warm availability lookup should hit the cache (0 queries).');
        $this->assertSame($html1, $html2, 'Cached availability HTML differs from freshly-queried HTML.');

        // Flush: next lookup must go back to the DB (invalidation works).
        MasterDataCache::flushAvailability($propertyid);
        $count = 0;
        $ctrl->getRoomswalkin($req);
        $this->assertEquals(1, $count, 'After flushAvailability, the next lookup must hit the DB again.');
    }

    public function test_walkin_page_master_data_stays_cached_across_requests(): void
    {
        // Fixture: a property that has BOTH the openwalkin permission row
        // (menuhelp 141112, view=1) and actual travel-agent master data, plus
        // a user on that property to authenticate as (revokeopen() reads
        // Auth::user()->propertyid / ->name).
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 141112 AND m.view = 1
                       AND EXISTS (SELECT 1 FROM subgroup s
                                   WHERE s.propertyid = m.propertyid
                                     AND s.comp_type = 'Travel Agency')
                     GROUP BY m.propertyid, m.username
                     ORDER BY (SELECT COUNT(*) FROM subgroup s
                               WHERE s.propertyid = m.propertyid
                                 AND s.comp_type = 'Travel Agency') DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No property with both openwalkin permission and travel-agent master data found.');
        }
        $propertyid = (int) $row['propertyid'];
        $username = $row['username'];
        $userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = $propertyid AND name = " . $this->pdo->quote($username) . ' LIMIT 1')
            ->fetchColumn();
        if (!$userId) {
            $this->markTestSkipped('Fixture user not found for property ' . $propertyid . '.');
        }

        Auth::loginUsingId($userId);

        $ctrl = app()->make(\App\Http\Controllers\CompanyController::class);
        $ref = new \ReflectionClass($ctrl);
        foreach ([
            'propertyid' => $propertyid,
            'username' => $username,
            'email' => 'perf-test@example.com',
            'ncurdate' => date('Y-m-d'),
            'currenttime' => date('Y-m-d H:i:s'),
        ] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        // One listener; reset the counter between phases (see note in the
        // availability test — multiple listeners multiply the same &$count).
        $count = 0;
        $subgroupQueries = 0;
        DB::listen(function ($q) use (&$count, &$subgroupQueries) {
            $count++;
            if (strpos($q->sql, 'from `subgroup`') !== false) {
                $subgroupQueries++;
            }
        });

        // Cold: flush the master-data cache, load the walkin page. The travel
        // agents + corporates lists are fetched from the DB (2 subgroup queries).
        MasterDataCache::flush($propertyid);
        $count = 0;
        $subgroupQueries = 0;
        ob_start();
        $resp = $ctrl->openwalkin();
        ob_end_clean();
        $this->assertNotInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp, 'openwalkin redirected — permission fixture broken.');
        $this->assertGreaterThanOrEqual(
            1,
            $subgroupQueries,
            "Cold walkin load should query subgroup master data — got {$subgroupQueries}. " .
            'If the cache is already warm, the fixture property may have been used by an earlier test.'
        );

        // Warm: second load in the same process must serve travel agents +
        // corporates from cache — ZERO subgroup queries.
        $count = 0;
        $subgroupQueries = 0;
        ob_start();
        $ctrl->openwalkin();
        ob_end_clean();
        $this->assertEquals(
            0,
            $subgroupQueries,
            "Warm walkin load issued {$subgroupQueries} subgroup queries — master data should come from cache. " .
            'A regression to raw DB::table queries on the walkin page would re-fetch subgroup on every load.'
        );

        // Flush: the next load must go back to the DB (invalidation works).
        MasterDataCache::flush($propertyid);
        $count = 0;
        $subgroupQueries = 0;
        ob_start();
        $ctrl->openwalkin();
        ob_end_clean();
        $this->assertGreaterThanOrEqual(
            1,
            $subgroupQueries,
            'After MasterDataCache::flush(), the walkin page must re-query subgroup.'
        );
    }

    public function test_reservation_page_master_data_stays_cached_across_requests(): void
    {
        // Fixture: a property with the new-reservation permission row
        // (menuhelp 131111, view=1), actual travel-agent master data AND rooms
        // (openreservations also caches room_mast via MasterDataCache::rooms).
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 131111 AND m.view = 1
                       AND EXISTS (SELECT 1 FROM subgroup s
                                   WHERE s.propertyid = m.propertyid
                                     AND s.comp_type = 'Travel Agency')
                       AND EXISTS (SELECT 1 FROM room_mast r
                                   WHERE r.propertyid = m.propertyid)
                     GROUP BY m.propertyid, m.username
                     ORDER BY (SELECT COUNT(*) FROM subgroup s
                               WHERE s.propertyid = m.propertyid
                                 AND s.comp_type = 'Travel Agency') DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No property with both reservation permission and master data (agents + rooms) found.');
        }
        $propertyid = (int) $row['propertyid'];
        $username = $row['username'];
        $userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = $propertyid AND name = " . $this->pdo->quote($username) . ' LIMIT 1')
            ->fetchColumn();
        if (!$userId) {
            $this->markTestSkipped('Fixture user not found for property ' . $propertyid . '.');
        }

        Auth::loginUsingId($userId);

        $ctrl = app()->make(\App\Http\Controllers\CompanyController::class);
        $ref = new \ReflectionClass($ctrl);
        foreach ([
            'propertyid' => $propertyid,
            'username' => $username,
            'email' => 'perf-test@example.com',
            'ncurdate' => date('Y-m-d'),
            'currenttime' => date('Y-m-d H:i:s'),
        ] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        $req = Request::create('/openreservations', 'GET');

        // One listener; reset the counters between phases.
        $count = 0;
        $subgroupQueries = 0;
        $roomMastQueries = 0;
        DB::listen(function ($q) use (&$count, &$subgroupQueries, &$roomMastQueries) {
            $count++;
            if (strpos($q->sql, 'from `subgroup`') !== false) {
                $subgroupQueries++;
            }
            if (strpos($q->sql, 'from `room_mast`') !== false) {
                $roomMastQueries++;
            }
        });

        // Cold: flush, load the reservation page. Travel agents + corporates
        // (subgroup) and rooms (room_mast) come from the DB.
        MasterDataCache::flush($propertyid);
        $count = 0;
        $subgroupQueries = 0;
        $roomMastQueries = 0;
        ob_start();
        $resp = $ctrl->openreservations($req);
        ob_end_clean();
        $this->assertNotInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp, 'openreservations redirected — permission fixture broken.');
        $this->assertGreaterThanOrEqual(
            1,
            $subgroupQueries,
            "Cold reservation load should query subgroup master data — got {$subgroupQueries}."
        );
        $this->assertGreaterThanOrEqual(
            1,
            $roomMastQueries,
            "Cold reservation load should query room_mast master data — got {$roomMastQueries}."
        );

        // Warm: second load must serve all three cached lists — ZERO subgroup
        // AND ZERO room_mast queries.
        $count = 0;
        $subgroupQueries = 0;
        $roomMastQueries = 0;
        ob_start();
        $ctrl->openreservations($req);
        ob_end_clean();
        $this->assertEquals(
            0,
            $subgroupQueries,
            "Warm reservation load issued {$subgroupQueries} subgroup queries — master data should come from cache. " .
            'A regression to raw DB::table queries would re-fetch subgroup on every reservation page load.'
        );
        $this->assertEquals(
            0,
            $roomMastQueries,
            "Warm reservation load issued {$roomMastQueries} room_mast queries — rooms should come from cache. " .
            'A regression to raw DB::table queries would re-fetch room_mast on every reservation page load.'
        );

        // Flush: next load must go back to the DB (invalidation works).
        MasterDataCache::flush($propertyid);
        $count = 0;
        $subgroupQueries = 0;
        $roomMastQueries = 0;
        ob_start();
        $ctrl->openreservations($req);
        ob_end_clean();
        $this->assertGreaterThanOrEqual(
            1,
            $subgroupQueries,
            'After MasterDataCache::flush(), the reservation page must re-query subgroup.'
        );
        $this->assertGreaterThanOrEqual(
            1,
            $roomMastQueries,
            'After MasterDataCache::flush(), the reservation page must re-query room_mast.'
        );
    }

    public function test_fom_charge_list_stays_cached_across_requests(): void
    {
        // Fixture: a property with the plan-master permission row
        // (menuhelp 121215, view=1), actual FOM charge codes in revmast, and a
        // matching user for Auth/revokeopen. openplanaster renders the FOM
        // charge dropdown from MasterDataCache::fomCharges() (revmast
        // field_type='C', Desk_code='FOM'.propertyid).
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 121215 AND m.view = 1
                       AND EXISTS (SELECT 1 FROM revmast r
                                   WHERE r.propertyid = m.propertyid
                                     AND r.field_type = 'C'
                                     AND r.Desk_code = CONCAT('FOM', m.propertyid))
                     GROUP BY m.propertyid, m.username
                     ORDER BY (SELECT COUNT(*) FROM revmast r
                               WHERE r.propertyid = m.propertyid
                                 AND r.field_type = 'C'
                                 AND r.Desk_code = CONCAT('FOM', m.propertyid)) DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No property with plan-master permission and FOM charge codes found.');
        }
        $propertyid = (int) $row['propertyid'];
        $username = $row['username'];
        $userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = $propertyid AND name = " . $this->pdo->quote($username) . ' LIMIT 1')
            ->fetchColumn();
        if (!$userId) {
            $this->markTestSkipped('Fixture user not found for property ' . $propertyid . '.');
        }

        Auth::loginUsingId($userId);

        $ctrl = app()->make(\App\Http\Controllers\CompanyController::class);
        $ref = new \ReflectionClass($ctrl);
        foreach ([
            'propertyid' => $propertyid,
            'username' => $username,
            'email' => 'perf-test@example.com',
            'ncurdate' => date('Y-m-d'),
            'currenttime' => date('Y-m-d H:i:s'),
        ] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        // One listener; reset the counters between phases.
        $count = 0;
        $revmastQueries = 0;
        DB::listen(function ($q) use (&$count, &$revmastQueries) {
            $count++;
            if (strpos($q->sql, 'from `revmast`') !== false) {
                $revmastQueries++;
            }
        });

        // Cold: flush, load the plan-master page. The FOM charge list comes
        // from the DB (a revmast query).
        MasterDataCache::flush($propertyid);
        $count = 0;
        $revmastQueries = 0;
        ob_start();
        $resp = $ctrl->openplanaster();
        ob_end_clean();
        $this->assertNotInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp, 'openplanaster redirected — permission fixture broken.');
        $this->assertGreaterThanOrEqual(
            1,
            $revmastQueries,
            "Cold plan-master load should query revmast (FOM charges) — got {$revmastQueries}."
        );

        // Warm: second load must serve the FOM charge list from cache — ZERO
        // revmast queries.
        $count = 0;
        $revmastQueries = 0;
        ob_start();
        $ctrl->openplanaster();
        ob_end_clean();
        $this->assertEquals(
            0,
            $revmastQueries,
            "Warm plan-master load issued {$revmastQueries} revmast queries — FOM charges should come from cache. " .
            'A regression to a raw DB::table revmast query on the page would re-fetch the FOM charge list on every load.'
        );

        // Flush: next load must go back to the DB (invalidation works).
        MasterDataCache::flush($propertyid);
        $count = 0;
        $revmastQueries = 0;
        ob_start();
        $ctrl->openplanaster();
        ob_end_clean();
        $this->assertGreaterThanOrEqual(
            1,
            $revmastQueries,
            'After MasterDataCache::flush(), the plan-master page must re-query revmast.'
        );
    }
}
