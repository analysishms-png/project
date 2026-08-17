<?php

namespace Tests\Feature;

use App\Http\Controllers\Banquet;
use App\Http\Controllers\HolidayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDOException;
use ReflectionClass;
use Tests\TestCase;

/**
 * QA pass regression tests.
 *
 * 1. Banquet financial write paths (ML-04-style audit):
 *    - performaInvoiceSubmit had its transaction COMMENTED OUT with an orphan
 *      active commit (mid-run failure would leave a day's postings half-written).
 *    - deletebanquetbill (6-table financial delete + audit), deletePerformaInvoice
 *      (5-table delete; catch also masked errors as 'success' => true),
 *      deleteadvancebanquet (paychargelog + PaychargeH/Ledger deletes),
 *      deletebanquet (inquiry + HallBook/VenueOcc), banquetbillsubmit
 *      (settlement delete+reinsert) — all had no transaction.
 * 2. HolidayController had NO auth guard at all (every sibling controller has a
 *    constructor middleware); GET /holiday/data leaked all holiday rows
 *    unauthenticated.
 *
 * All assertions are structural/decision-level (transaction markers present and
 * balanced, unauthenticated requests redirected) — read-only, no data mutation.
 */
class BanquetHolidayQATest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping Banquet/Holiday QA tests: ' . $e->getMessage());
        }
    }

    private function methodBody(Banquet $controller, string $method): string
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

    public function testPerformaInvoiceSubmitTransactionReenabled(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'performaInvoiceSubmit'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'), 'beginTransaction must be active (was commented out)');
        $this->assertSame(1, substr_count($body, 'DB::commit()'), 'exactly one commit');
        $this->assertGreaterThanOrEqual(2, substr_count($body, 'DB::rollBack()'), 'rollback on early returns + catch');
    }

    public function testDeleteBanquetBillIsTransactional(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'deletebanquetbill'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
        // commit must precede the success return; rollback before the error return
        $this->assertLessThan(
            strpos($body, 'Bill Deleted Successfully'),
            strpos($body, 'DB::commit()')
        );
    }

    public function testDeletePerformaInvoiceTransactionalAndErrorNotMasked(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'deletePerformaInvoice'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
        // catch previously returned 'success' => true on failure — must be false
        $catch = substr($body, strpos($body, 'catch (Exception'));
        $this->assertMatchesRegularExpression("/'success'\s*=>\s*false/", $catch, 'catch must not mask errors as success');
    }

    public function testDeleteAdvanceBanquetTransactional(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'deleteadvancebanquet'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
    }

    public function testDeleteBanquetTransactional(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'deletebanquet'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
    }

    public function testBanquetBillSubmitTransactional(): void
    {
        $body = $this->stripComments($this->methodBody(new Banquet(), 'banquetbillsubmit'));
        $this->assertSame(1, substr_count($body, 'DB::beginTransaction()'));
        $this->assertSame(1, substr_count($body, 'DB::commit()'));
        $this->assertSame(1, substr_count($body, 'DB::rollBack()'));
    }

    public function testHolidayControllerHasAuthGuard(): void
    {
        // Unauthenticated requests to holiday routes must redirect (constructor middleware),
        // not return holiday data.
        $response = $this->get('/holiday/data');
        $this->assertTrue(in_array($response->getStatusCode(), [302, 401, 403], true), 'unauthenticated /holiday/data must not return data');

        $response2 = $this->get('/holidaymaster');
        $this->assertTrue(in_array($response2->getStatusCode(), [302, 401, 403], true), 'unauthenticated /holidaymaster must not render');

        // Controller must have constructor middleware
        $ref = new ReflectionClass(HolidayController::class);
        $ctor = $ref->getConstructor();
        $this->assertNotNull($ctor, 'HolidayController must define __construct');
        $file = file($ref->getFileName());
        $body = implode('', array_slice($file, $ctor->getStartLine() - 1, $ctor->getEndLine() - $ctor->getStartLine() + 1));
        $this->assertStringContainsString('Auth::user()', $body, 'constructor must check authentication');
    }
}
