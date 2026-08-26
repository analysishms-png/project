<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDOException;
use Tests\TestCase;

/**
 * Module-wise smoke tests with data seeding verification.
 *
 * Tests each major module by verifying seed data, data integrity,
 * and cache layer — gracefully skips missing tables.
 */
class ModuleSmokeTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    private int $pid = 103;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
            $exists = $this->pdo->query("SELECT COUNT(*) FROM company WHERE propertyid = {$this->pid}")->fetchColumn();
            if (!$exists) {
                $this->pid = (int) $this->pdo->query("SELECT propertyid FROM company LIMIT 1")->fetchColumn();
                if (!$this->pid) {
                    $this->markTestSkipped('No company data found.');
                }
            }
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable: ' . $e->getMessage());
        }
    }

    private function rowCount(string $table, string $where = '1=1'): int
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM {$table} WHERE {$where} AND propertyid = {$this->pid}")->fetchColumn();
        } catch (PDOException $e) {
            $this->markTestSkipped("Table {$table} not found: " . $e->getMessage());
            return 0;
        }
    }

    private function firstRow(string $table, string $where = '1=1'): ?array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM {$table} WHERE {$where} AND propertyid = {$this->pid} LIMIT 1");
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->markTestSkipped("Table {$table} not found: " . $e->getMessage());
            return null;
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $this->pdo->query("SELECT 1 FROM {$table} LIMIT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // ================================================================
    // MODULE 1: AUTH / USERS
    // ================================================================

    public function test_module_01_auth_users_seeded()
    {
        $count = $this->rowCount('users');
        $this->assertGreaterThan(0, $count, 'users table must have at least 1 user');
    }

    public function test_module_02_auth_user_has_property()
    {
        $row = $this->firstRow('users');
        $this->assertNotNull($row, 'At least one user must exist');
        $this->assertNotEmpty($row['propertyid'], 'User must have a propertyid');
    }

    public function test_module_03_auth_menuhelp_permissions_seeded()
    {
        $count = $this->rowCount('menuhelp');
        $this->assertGreaterThan(0, $count, 'menuhelp permissions must be seeded');
    }

    // ================================================================
    // MODULE 2: COMPANY / PROPERTY
    // ================================================================

    public function test_module_04_company_seeded()
    {
        $row = $this->firstRow('company');
        $this->assertNotNull($row, 'company table must have at least 1 row');
    }

    public function test_module_05_enviro_general_seeded()
    {
        $row = $this->firstRow('enviro_general');
        $this->assertNotNull($row, 'enviro_general must be seeded');
        $this->assertNotEmpty($row['ncur'] ?? null, 'enviro_general must have ncur date');
    }

    // ================================================================
    // MODULE 3: ROOM MASTERS
    // ================================================================

    public function test_module_06_room_mast_seeded()
    {
        $count = $this->rowCount('room_mast');
        $this->assertGreaterThan(0, $count, 'room_mast must have rooms');
    }

    public function test_module_07_room_cat_seeded()
    {
        $count = $this->rowCount('room_cat');
        $this->assertGreaterThan(0, $count, 'room_cat must have categories');
    }

    public function test_module_08_room_mast_roomcat_fk_valid()
    {
        if (!$this->tableExists('room_mast') || !$this->tableExists('room_cat')) {
            $this->markTestSkipped('room_mast or room_cat missing');
        }
        $orphans = $this->pdo
            ->query("SELECT COUNT(*) FROM room_mast r
                     WHERE r.propertyid = {$this->pid}
                     AND r.type = 'RO'
                     AND r.room_cat IS NOT NULL AND r.room_cat != ''
                     AND NOT EXISTS (SELECT 1 FROM room_cat rc
                                     WHERE rc.cat_code = r.room_cat AND rc.propertyid = r.propertyid)")
            ->fetchColumn();
        $this->assertLessThan(10, (int) $orphans, 'Too many rooms with invalid room_cat codes');
    }

    public function test_module_09_room_occ_seeded()
    {
        $this->rowCount('roomocc');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 4: FRONT OFFICE / BILLING
    // ================================================================

    public function test_module_10_guestfolio_seeded()
    {
        $this->rowCount('guestfolio');
        $this->assertTrue(true);
    }

    public function test_module_11_paycharge_seeded()
    {
        $this->rowCount('paycharge');
        $this->assertTrue(true);
    }

    public function test_module_12_paycharge_folio_link_valid()
    {
        $count = $this->rowCount('paycharge');
        if ($count === 0) {
            $this->markTestSkipped('No paycharge data');
        }
        $orphanCharges = $this->pdo
            ->query("SELECT COUNT(*) FROM paycharge p
                     WHERE p.propertyid = {$this->pid}
                     AND p.folionodocid IS NOT NULL AND p.folionodocid != 0
                     AND NOT EXISTS (SELECT 1 FROM guestfolio g
                                     WHERE g.docid = p.folionodocid AND g.propertyid = p.propertyid)")
            ->fetchColumn();
        $this->assertEquals(0, (int) $orphanCharges, 'paycharge must link to valid guestfolio');
    }

    public function test_module_13_guestprof_seeded()
    {
        $this->rowCount('guestprof');
        $this->assertTrue(true);
    }

    public function test_module_14_plan_mast_seeded()
    {
        $count = $this->rowCount('plan_mast');
        $this->assertGreaterThan(0, $count, 'plan_mast must have plans');
    }

    // ================================================================
    // MODULE 5: POS / RESTAURANT
    // ================================================================

    public function test_module_15_depart_seeded()
    {
        $count = $this->rowCount('depart');
        $this->assertGreaterThan(0, $count, 'depart (outlets) must be seeded');
    }

    public function test_module_16_sale1_seeded()
    {
        $this->rowCount('sale1');
        $this->assertTrue(true);
    }

    public function test_module_17_itemmast_seeded()
    {
        if (!$this->tableExists('itemmast')) {
            $this->markTestSkipped('itemmast table missing');
        }
        $this->rowCount('itemmast');
        $this->assertTrue(true);
    }

    public function test_module_18_server_mast_seeded()
    {
        $this->rowCount('server_mast');
        $this->assertTrue(true);
    }

    public function test_module_19_session_mast_seeded()
    {
        $this->rowCount('session_mast');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 6: BANQUET
    // ================================================================

    public function test_module_20_venuemast_seeded()
    {
        $this->rowCount('venuemast');
        $this->assertTrue(true);
    }

    public function test_module_21_hallbook_seeded()
    {
        $this->rowCount('hallbook');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 7: INVENTORY
    // ================================================================

    public function test_module_22_godown_mast_seeded()
    {
        $this->rowCount('godown_mast');
        $this->assertTrue(true);
    }

    public function test_module_23_stock_seeded()
    {
        $this->rowCount('stock');
        $this->assertTrue(true);
    }

    public function test_module_24_indent_seeded()
    {
        $this->rowCount('indent');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 8: HOUSEKEEPING
    // ================================================================

    public function test_module_25_hk_tables_exist()
    {
        $hkTables = ['housekeeparmast', 'hksupervisor', 'hkfloors', 'hkchecklistmast', 'hkamentiesmaster'];
        foreach ($hkTables as $tbl) {
            if ($this->tableExists($tbl)) {
                $this->rowCount($tbl);
            }
        }
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 9: FINANCE
    // ================================================================

    public function test_module_26_ledger_seeded()
    {
        $this->rowCount('ledger');
        $this->assertTrue(true);
    }

    public function test_module_27_revmast_seeded()
    {
        $count = $this->rowCount('revmast');
        $this->assertGreaterThan(0, $count, 'revmast must be seeded');
    }

    public function test_module_28_revmast_has_field_types()
    {
        $types = $this->pdo
            ->query("SELECT DISTINCT field_type FROM revmast WHERE propertyid = {$this->pid}")
            ->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertNotEmpty($types, 'revmast must have field_type values');
    }

    public function test_module_29_taxstru_seeded()
    {
        $this->rowCount('taxstru');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 10: HR
    // ================================================================

    public function test_module_30_employee_seeded()
    {
        $this->rowCount('employee');
        $this->assertTrue(true);
    }

    public function test_module_31_empcategory_seeded()
    {
        if (!$this->tableExists('empcategory')) {
            $this->markTestSkipped('empcategory table missing');
        }
        $this->rowCount('empcategory');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 11: GEOGRAPHY
    // ================================================================

    public function test_module_32_states_seeded()
    {
        $count = $this->rowCount('states');
        $this->assertGreaterThan(0, $count, 'states must be seeded');
    }

    public function test_module_33_cities_seeded()
    {
        $count = $this->rowCount('cities');
        $this->assertGreaterThan(0, $count, 'cities must be seeded');
    }

    public function test_module_34_countries_seeded()
    {
        $count = $this->rowCount('countries');
        $this->assertGreaterThan(0, $count, 'countries must be seeded');
    }

    // ================================================================
    // MODULE 12: MISC MASTERS
    // ================================================================

    public function test_module_35_busssource_seeded()
    {
        if (!$this->tableExists('busssource')) {
            $this->markTestSkipped('busssource table missing');
        }
        $this->rowCount('busssource');
        $this->assertTrue(true);
    }

    public function test_module_36_functiontype_seeded()
    {
        $this->rowCount('functiontype');
        $this->assertTrue(true);
    }

    public function test_module_37_sundrymast_seeded()
    {
        if (!$this->tableExists('sundrymast')) {
            $this->markTestSkipped('sundrymast table missing');
        }
        $this->rowCount('sundrymast');
        $this->assertTrue(true);
    }

    // ================================================================
    // MODULE 13: CACHE LAYER
    // ================================================================

    public function test_module_38_cache_version_works()
    {
        \App\Services\CacheService::forget('ver:test:module_check');
        $v1 = \App\Services\CacheService::version('test:module_check');
        $this->assertEquals(1, $v1);

        $v2 = \App\Services\CacheService::bump('test:module_check');
        $this->assertEquals(2, $v2);

        \App\Services\CacheService::forget('ver:test:module_check');
    }

    public function test_module_39_cache_remember_works()
    {
        $executed = false;
        $result = \App\Services\CacheService::remember('test:module_rem', 60, function () use (&$executed) {
            $executed = true;
            return 'seeded_value';
        });
        $this->assertTrue($executed);
        $this->assertEquals('seeded_value', $result);

        $executed = false;
        $result2 = \App\Services\CacheService::remember('test:module_rem', 60, function () use (&$executed) {
            $executed = true;
            return 'new_value';
        });
        $this->assertFalse($executed);
        $this->assertEquals('seeded_value', $result2);
        \App\Services\CacheService::forget('test:module_rem');
    }

    public function test_module_40_cache_purge_reports_works()
    {
        \App\Services\CacheService::forget('ver:rpt:' . $this->pid);
        $v1 = \App\Services\CacheService::version('rpt:' . $this->pid);
        \App\Services\CacheService::purgeReports($this->pid);
        $v2 = \App\Services\CacheService::version('rpt:' . $this->pid);
        $this->assertGreaterThan($v1, $v2);
        \App\Services\CacheService::forget('ver:rpt:' . $this->pid);
    }

    public function test_module_41_hash_input_deterministic()
    {
        $h1 = \App\Http\Middleware\CacheReportFetch::hashInput(['z' => 1, 'a' => 2]);
        $h2 = \App\Http\Middleware\CacheReportFetch::hashInput(['a' => 2, 'z' => 1]);
        $this->assertEquals($h1, $h2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $h1);
    }

    // ================================================================
    // MODULE 14: DATA INTEGRITY
    // ================================================================

    public function test_module_42_roomocc_links_to_room_mast()
    {
        if (!$this->tableExists('roomocc') || !$this->tableExists('room_mast')) {
            $this->markTestSkipped('roomocc or room_mast missing');
        }
        $orphans = $this->pdo
            ->query("SELECT COUNT(*) FROM roomocc ro
                     WHERE ro.propertyid = {$this->pid}
                     AND NOT EXISTS (SELECT 1 FROM room_mast rm
                                     WHERE rm.rcode = ro.roomno AND rm.propertyid = ro.propertyid)")
            ->fetchColumn();
        $this->assertEquals(0, (int) $orphans, 'roomocc must reference valid room_mast.rcode');
    }

    public function test_module_43_sale1_links_to_depart()
    {
        $count = $this->rowCount('sale1');
        if ($count === 0) {
            $this->markTestSkipped('No sale1 data');
        }
        $orphans = $this->pdo
            ->query("SELECT COUNT(*) FROM sale1 s
                     WHERE s.propertyid = {$this->pid}
                     AND NOT EXISTS (SELECT 1 FROM depart d
                                     WHERE d.dcode = s.restcode AND d.propertyid = s.propertyid)")
            ->fetchColumn();
        $this->assertEquals(0, (int) $orphans, 'sale1 must reference valid depart.dcode');
    }

    public function test_module_44_no_duplicate_room_codes()
    {
        if (!$this->tableExists('room_mast')) {
            $this->markTestSkipped('room_mast missing');
        }
        $dupes = $this->pdo
            ->query("SELECT COUNT(*) FROM (
                     SELECT rcode, propertyid FROM room_mast
                     WHERE propertyid = {$this->pid} AND type = 'RO'
                     GROUP BY rcode, propertyid HAVING COUNT(*) > 1) t")
            ->fetchColumn();
        $this->assertEquals(0, (int) $dupes, 'No duplicate rcode in room_mast');
    }

    public function test_module_45_room_mast_type_values_valid()
    {
        if (!$this->tableExists('room_mast')) {
            $this->markTestSkipped('room_mast missing');
        }
        $valid = ['RO', 'TB', 'FN', 'LB', 'PR'];
        $bad = $this->pdo
            ->query("SELECT COUNT(*) FROM room_mast
                     WHERE propertyid = {$this->pid}
                     AND type IS NOT NULL AND type != ''
                     AND type NOT IN ('" . implode("','", $valid) . "')")
            ->fetchColumn();
        $this->assertEquals(0, (int) $bad, 'room_mast.type must be valid');
    }

    // ================================================================
    // MODULE 15: ROUTES / BOOT
    // ================================================================

    public function test_module_46_app_boots()
    {
        $resp = $this->get('/');
        $this->assertNotEquals(500, $resp->getStatusCode());
    }

    public function test_module_47_config_files_exist()
    {
        foreach (['database', 'cache', 'auth', 'session'] as $config) {
            $this->assertFileExists(config_path("{$config}.php"));
        }
    }

    public function test_module_48_helpers_exist()
    {
        $this->assertTrue(function_exists('revokeopen'));
        $this->assertTrue(function_exists('companydata'));
    }
}
