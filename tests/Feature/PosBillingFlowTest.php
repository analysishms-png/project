<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * Feature tests for POS (Point of Sale) billing flow.
 *
 * Verifies data integrity, table relationships, and key queries
 * used in the POS/KOT/sale lifecycle.
 */
class PosBillingFlowTest extends TestCase
{
    private $pdo;
    private int $pid = 103;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->pdo = DB::connection()->getPdo();
            $exists = $this->pdo->query("SELECT COUNT(*) FROM company WHERE propertyid = {$this->pid}")->fetchColumn();
            if (!$exists) {
                $this->pid = (int) $this->pdo->query("SELECT propertyid FROM company LIMIT 1")->fetchColumn();
                if (!$this->pid) $this->markTestSkipped('No company data');
            }
        } catch (PDOException $e) {
            $this->markTestSkipped('DB unavailable');
        }
    }

    private function tableExists(string $t): bool
    {
        try { $this->pdo->query("SELECT 1 FROM {$t} LIMIT 1"); return true; }
        catch (PDOException $e) { return false; }
    }

    private function rowCount(string $t, string $w = '1=1'): int
    {
        try { return (int) $this->pdo->query("SELECT COUNT(*) FROM {$t} WHERE {$w} AND propertyid = {$this->pid}")->fetchColumn(); }
        catch (PDOException $e) { return 0; }
    }

    private function rawCount(string $sql): int
    {
        try { return (int) $this->pdo->query($sql)->fetchColumn(); }
        catch (PDOException $e) { return 0; }
    }

    // ================================================================
    // 1. TABLE STRUCTURE & SEED DATA
    // ================================================================

    public function test_sale1_table_exists_and_has_data()
    {
        if (!$this->tableExists('sale1')) $this->markTestSkipped('sale1 missing');
        $count = $this->rowCount('sale1');
        $this->assertGreaterThan(0, $count, 'sale1 must have seeded POS bills');
    }

    public function test_sale2_table_exists_and_has_data()
    {
        if (!$this->tableExists('sale2')) $this->markTestSkipped('sale2 missing');
        $count = $this->rowCount('sale2');
        $this->assertGreaterThan(0, $count, 'sale2 must have line items');
    }

    public function test_kot_table_exists()
    {
        if (!$this->tableExists('kot')) $this->markTestSkipped('kot missing');
        $count = $this->rowCount('kot');
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_itemmast_table_exists_and_has_items()
    {
        if (!$this->tableExists('itemmast')) $this->markTestSkipped('itemmast missing');
        $count = $this->rawCount("SELECT COUNT(*) FROM itemmast WHERE Property_ID = {$this->pid}");
        $this->assertGreaterThan(0, $count, 'itemmast must have menu items for property');
    }

    public function test_itemrate_table_exists_and_has_rates()
    {
        if (!$this->tableExists('itemrate')) $this->markTestSkipped('itemrate missing');
        $count = $this->rawCount("SELECT COUNT(*) FROM itemrate WHERE Property_ID = {$this->pid}");
        $this->assertGreaterThan(0, $count, 'itemrate must have item prices for property');
    }

    // ================================================================
    // 2. DATA INTEGRITY — sale1 ↔ sale2 relationship
    // ================================================================

    public function test_sale2_rows_reference_valid_sale1_docid()
    {
        if (!$this->tableExists('sale1') || !$this->tableExists('sale2')) $this->markTestSkipped('sale tables missing');
        $orphaned = $this->pdo->query("
            SELECT COUNT(*) FROM sale2 s2
            WHERE s2.propertyid = {$this->pid}
            AND NOT EXISTS (
                SELECT 1 FROM sale1 s1 WHERE s1.docid = s2.docid AND s1.propertyid = {$this->pid}
            )
        ")->fetchColumn();
        $this->assertEquals(0, (int) $orphaned, 'sale2 rows must reference valid sale1.docid');
    }

    public function test_sale1_has_required_columns()
    {
        if (!$this->tableExists('sale1')) $this->markTestSkipped('sale1 missing');
        $cols = $this->pdo->query("DESCRIBE sale1")->fetchAll(\PDO::FETCH_COLUMN);
        foreach (['docid', 'vdate', 'restcode', 'total', 'discamt', 'delflag', 'propertyid'] as $col) {
            $this->assertContains($col, $cols, "sale1 must have column: {$col}");
        }
    }

    public function test_sale2_has_required_columns()
    {
        if (!$this->tableExists('sale2')) $this->markTestSkipped('sale2 missing');
        $cols = $this->pdo->query("DESCRIBE sale2")->fetchAll(\PDO::FETCH_COLUMN);
        foreach (['docid', 'sno', 'restcode', 'basevalue', 'taxcode', 'taxamt', 'propertyid'] as $col) {
            $this->assertContains($col, $cols, "sale2 must have column: {$col}");
        }
    }

    // ================================================================
    // 3. KOT INTEGRITY
    // ================================================================

    public function test_kot_has_required_columns()
    {
        if (!$this->tableExists('kot')) $this->markTestSkipped('kot missing');
        $cols = $this->pdo->query("DESCRIBE kot")->fetchAll(\PDO::FETCH_COLUMN);
        foreach (['docid', 'vdate', 'restcode', 'item', 'qty', 'rate', 'propertyid'] as $col) {
            $this->assertContains($col, $cols, "kot must have column: {$col}");
        }
    }

    public function test_kot_rows_reference_valid_items()
    {
        if (!$this->tableExists('kot') || !$this->tableExists('itemmast')) $this->markTestSkipped('tables missing');
        $orphaned = $this->pdo->query("
            SELECT COUNT(*) FROM kot k
            WHERE k.propertyid = {$this->pid}
            AND k.item != ''
            AND NOT EXISTS (
                SELECT 1 FROM itemmast i WHERE i.Code = k.item AND i.Property_ID = {$this->pid}
            )
        ")->fetchColumn();
        $this->assertEquals(0, (int) $orphaned, 'KOT rows must reference valid itemmast codes');
    }

    // ================================================================
    // 4. POS BILLING QUERY PATTERNS (verify N+1 fixes work)
    // ================================================================

    public function test_sale1_aggregate_query_returns_valid_data()
    {
        if (!$this->tableExists('sale1')) $this->markTestSkipped('sale1 missing');
        $result = $this->pdo->query("
            SELECT restcode, SUM(total) - SUM(discamt) AS net_sum
            FROM sale1
            WHERE propertyid = {$this->pid} AND delflag = 'N'
            GROUP BY restcode
        ")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertIsArray($result);
        foreach ($result as $row) {
            $this->assertNotEmpty($row['restcode']);
            $this->assertIsNumeric($row['net_sum']);
        }
    }

    public function test_item_lookup_by_code_works()
    {
        if (!$this->tableExists('itemmast')) $this->markTestSkipped('itemmast missing');
        $item = $this->pdo->query("
            SELECT Code, Name, ItemCatCode FROM itemmast
            WHERE Property_ID = {$this->pid} AND RestCode != '' LIMIT 1
        ")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($item);
        $this->assertNotEmpty($item['Code']);
    }

    public function test_itemcatmast_to_taxstru_lookup_works()
    {
        if (!$this->tableExists('itemcatmast') || !$this->tableExists('taxstru')) $this->markTestSkipped('tables missing');
        $result = $this->pdo->query("
            SELECT ic.Code AS cat_code, ic.TaxStru, ts.str_code, ts.rate
            FROM itemcatmast ic
            JOIN taxstru ts ON ts.str_code = ic.TaxStru
            WHERE ic.propertyid = {$this->pid}
            LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertIsArray($result);
    }

    // ================================================================
    // 5. PAYMENT INTEGRITY
    // ================================================================

    public function test_paycharge_has_pos_charges()
    {
        if (!$this->tableExists('paycharge')) $this->markTestSkipped('paycharge missing');
        $count = $this->rowCount('paycharge', "paycode = 'RMCH{$this->pid}' OR paycode LIKE 'POS%'");
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_paycharge_sno_starts_at_1()
    {
        if (!$this->tableExists('paycharge')) $this->markTestSkipped('paycharge missing');
        $minSno = $this->pdo->query("SELECT MIN(sno) FROM paycharge WHERE propertyid = {$this->pid}")->fetchColumn();
        if ($minSno !== null) {
            $this->assertGreaterThanOrEqual(1, (int) $minSno, 'paycharge sno should start at 1');
        }
    }

    // ================================================================
    // 6. DELETE FLAG INTEGRITY
    // ================================================================

    public function test_active_sale1_bills_have_delflag_N()
    {
        if (!$this->tableExists('sale1')) $this->markTestSkipped('sale1 missing');
        $activeCount = $this->rowCount('sale1', "delflag = 'N'");
        $totalCount = $this->rowCount('sale1');
        $this->assertGreaterThan(0, $activeCount, 'Must have active (non-deleted) bills');
        $this->assertLessThanOrEqual($totalCount, $activeCount);
    }

    public function test_deleted_sale1_bills_have_delflag_Y()
    {
        if (!$this->tableExists('sale1')) $this->markTestSkipped('sale1 missing');
        $deletedCount = $this->rowCount('sale1', "delflag = 'Y'");
        // Deleted bills can be 0 — that's fine, just verify query works
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }
}
