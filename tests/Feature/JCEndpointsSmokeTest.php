<?php

namespace Tests\Feature;

use App\Http\Controllers\ChainController;
use App\Http\Controllers\ChannelPush;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RevenueManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * Phase J-C endpoint smoke tests (REDIS_JS_PLAN.md).
 *
 * Hits the new JSON feeds added for the JS rollout:
 *   chain/report/data, revenue/rate-comparison/data,
 *   invdashboard/summary, channel/dashboard/counts,
 *   channel/availability/data
 *
 * Read-only; skipped when the DB is unreachable.
 */
class JCEndpointsSmokeTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping JC endpoint tests: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a controller with its middleware-filled protected properties
     * set manually (container resolution skips constructor middleware).
     *
     * @return object
     */
    private function makeController(string $class)
    {
        $userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = 103 AND u_name = 'sa' LIMIT 1")
            ->fetchColumn();
        if (!$userId) {
            $this->markTestSkipped('Fixture user sa@103 not found.');
        }

        Auth::loginUsingId($userId);

        $ctrl = app()->make($class);
        $ref = new \ReflectionClass($ctrl);
        foreach ([
            'propertyid' => '103',
            'username' => 'sa',
            'email' => 'sa@example.com',
            'ncurdate' => date('Y-m-d'),
            'currenttime' => date('Y-m-d H:i:s'),
            'ptlngth' => 5,
            'prpid' => '103',
        ] as $prop => $val) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue($ctrl, $val);
            }
        }

        return $ctrl;
    }

    public function test_chain_report_data_returns_rows_and_totals()
    {
        $ctrl = $this->makeController(ChainController::class);
        $res = $ctrl->crossPropertyReportData(
            Request::create('/x', 'GET', ['start' => '2026-08-01', 'end' => '2026-08-24'])
        );

        $this->assertEquals(200, $res->getStatusCode());
        $data = json_decode($res->getContent(), true);
        $this->assertIsArray($data['rows']);
        $this->assertArrayHasKey('total', $data['totals']);
        $this->assertNotEmpty($data['rows']);
    }

    public function test_rate_comparison_data_supports_both_occtypes()
    {
        $ctrl = $this->makeController(RevenueManagementController::class);

        foreach (['singleuser', 'multiuser'] as $occ) {
            $res = $ctrl->rateComparisonData(Request::create('/x', 'GET', ['occtype' => $occ]));
            $this->assertEquals(200, $res->getStatusCode());
            $data = json_decode($res->getContent(), true);
            $this->assertSame($occ, $data['occtype']);
            $this->assertIsArray($data['rows']);
        }
    }

    public function test_invdashboard_summary_structure()
    {
        $ctrl = $this->makeController(InventoryController::class);
        $res = $ctrl->lookupSummary();

        $this->assertEquals(200, $res->getStatusCode());
        $data = json_decode($res->getContent(), true);
        $this->assertArrayHasKey('suppliers', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('mrThisMonth', $data);
    }

    public function test_channel_dashboard_counts_structure()
    {
        // Regression guard: ChannelPushes model import must not fatal.
        $ctrl = $this->makeController(ChannelPush::class);
        $res = $ctrl->dashboardCounts();

        $this->assertEquals(200, $res->getStatusCode());
        $data = json_decode($res->getContent(), true);
        $this->assertArrayHasKey('roomcats', $data);
        $this->assertArrayHasKey('todayBookings', $data);
    }

    public function test_channel_availability_data_all_and_mapped()
    {
        $ctrl = $this->makeController(ChannelPush::class);

        foreach ([0, 1] as $mapped) {
            $res = $ctrl->availabilityData(
                Request::create('/x', 'GET', ['mapped' => $mapped])
            );
            $this->assertEquals(200, $res->getStatusCode());
            $data = json_decode($res->getContent(), true);
            $this->assertArrayHasKey('availability', $data);
            $this->assertCount(14, $data['dates']);

            if ($mapped) {
                foreach ($data['categories'] as $cat) {
                    $this->assertNotEmpty($cat['map_code'], 'mapped=1 returned an unmapped category');
                }
            }
        }
    }
}
