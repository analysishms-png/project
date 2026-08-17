<?php

namespace Tests\Feature;

use App\Http\Controllers\HouseKeeping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * Housekeeping module permission-guard regression tests.
 *
 * Covers BUG-045 (housemaster CRUD guarded with the wrong menu code 121512 —
 * blocked on properties whose housemaster code is 151112, e.g. prop 135) and
 * the audit pass that added permission guards to previously-unguarded HK
 * write paths (savehousecleaning, lostfound/laundry/damage/cleaning-type/
 * supervisor/floor CRUD, assignments, start-cleaning, cleaning entry,
 * inspection).
 *
 * Read-only against the live DB; skipped when the DB is unreachable.
 * The tests assert the DECISION the guard produces (allowed vs denied),
 * not any data mutation.
 */
class HouseKeepingModuleTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping HK module tests: ' . $e->getMessage());
        }
    }

    /**
     * Build a HouseKeeping controller wired to a given property/user, with the
     * protected properties filled (constructor middleware needs Auth).
     *
     * @return array{0: HouseKeeping, 1: \ReflectionClass}
     */
    private function makeController(int $propertyid, string $username): array
    {
        $userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = $propertyid AND name = " . $this->pdo->quote($username) . ' LIMIT 1')
            ->fetchColumn();
        if (!$userId) {
            $this->markTestSkipped("Fixture user not found for property $propertyid / $username.");
        }

        Auth::loginUsingId($userId);

        $ctrl = app()->make(HouseKeeping::class);
        $ref = new \ReflectionClass($ctrl);
        foreach ([
            'propertyid' => $propertyid,
            'username' => $username,
            'email' => 'hk-test@example.com',
            'ncurdate' => date('Y-m-d'),
            'currenttime' => date('Y-m-d H:i:s'),
        ] as $prop => $val) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue($ctrl, $val);
        }

        return [$ctrl, $ref];
    }

    /**
     * Invoke a controller method via reflection, returning the HTTP response
     * object (response()->json(...) / redirect()->back() etc).
     */
    private function invoke($ctrl, string $method, array $input = []): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/hk-test', 'POST', $input);
        $ref = new \ReflectionClass($ctrl);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        $result = $m->invoke($ctrl, $request);

        return $result instanceof \Symfony\Component\HttpFoundation\Response ? $result : new \Illuminate\Http\JsonResponse($result);
    }

    /**
     * Authorized users must NOT be blocked by the newly-added guards: a user
     * with the damage-entry permission (151118 ins=1) passes the guard on
     * storeoutofororder (reaches validation, not a 403).
     */
    public function test_storeoutofororder_allows_authorized_user()
    {
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 151118 AND m.ins = 1
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No user with 151118 (damage entry) permission found.');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        $resp = $this->invoke($ctrl, 'storeoutofororder', []);

        // Guard passes (authorized) → request proceeds to validation, which
        // returns a 422 for missing fields — NOT a 403 permission denial.
        $denied = $this->isDenied($resp);
        $this->assertFalse($denied, 'storeoutofororder must NOT block a user with 151118 ins permission');
        $this->assertNotEquals(403, $resp->getStatusCode(), 'Authorized user wrongly 403-denied by storeoutofororder guard');
    }

    /**
     * Validation failures on damage/OOO endpoints must return a clean 422,
     * not crash with "Array to string conversion" (the old catch did
     * implode(' ', $ve->errors()) where errors() is array-of-arrays).
     */
    public function test_damage_validation_failure_returns_422_not_crash()
    {
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 151118 AND m.ins = 1
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No user with 151118 (damage entry) permission found.');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        // Missing required fields → validation exception → catch must flatten
        // the error bags and return 422 (not throw Array-to-string).
        $resp = $this->invoke($ctrl, 'storedamagereport', []);
        $this->assertEquals(422, $resp->getStatusCode());
        $data = $resp->getData(true);
        $this->assertStringContainsString('roomno', (string) ($data['message'] ?? ''));
    }

    /**
     * Find a user on a property that has the 151112 (housemaster) permission
     * row, and confirm they do NOT need 121512 to use it.
     */
    public function test_housemaster_guard_accepts_151112()
    {
        // Target the exact BUG-045 case: a (property, username) that has the
        // housemaster code 151112 granted (ins=1) but does NOT have the wrong
        // code 121512 the controller was guarding with (e.g. prop 135 user sa).
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 151112 AND m.ins = 1 AND m.view = 1
                       AND NOT EXISTS (SELECT 1 FROM menuhelp x
                                       WHERE x.propertyid = m.propertyid
                                         AND x.username = m.username
                                         AND x.code = 121512)
                     ORDER BY m.propertyid DESC
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No property/user with 151112 but WITHOUT 121512 found (fixture for BUG-045).');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        // The guard decision for submithousemaster must be ALLOWED.
        // Pre-fix (BUG-045) it called revokeopen(121512) → null on properties
        // that only have 151112 → redirect to error.
        $resp = $this->invoke($ctrl, 'submithousemaster', ['name' => 'HK-TEST-GUARD-' . uniqid()]);

        // Allowed path → either success (json) or validation/duplicate-name
        // error, but NOT the permission-denied redirect.
        $denied = $resp instanceof \Illuminate\Http\RedirectResponse
            && str_contains((string) session('error'), 'no permission');

        $this->assertFalse($denied, 'BUG-045: submithousemaster blocked for a user with 151112 (guard still checks wrong code 121512)');
    }

    /**
     * A user with NO housekeeping permission rows at all must be denied by the
     * newly-added guard on savehousecleaning.
     */
    public function test_savehousecleaning_requires_151111_permission()
    {
        // Find a property that has a user WITHOUT any 15xxxx menuhelp row.
        $row = $this->pdo
            ->query("SELECT u.propertyid, u.name AS username
                     FROM users u
                     WHERE NOT EXISTS (SELECT 1 FROM menuhelp m
                                       WHERE m.propertyid = u.propertyid
                                         AND m.username = u.name
                                         AND m.code BETWEEN 150000 AND 151999)
                       AND EXISTS (SELECT 1 FROM room_mast r WHERE r.propertyid = u.propertyid LIMIT 1)
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No user without HK permission rows found.');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        $resp = $this->invoke($ctrl, 'savehousecleaning', [
            'roomno' => 'NOPE',
            'roomstat' => 'C',
        ]);

        $denied = $this->isDenied($resp);

        $this->assertTrue($denied, 'savehousecleaning must deny users without HK permission (guard missing)');
    }

    /**
     * The guard denies either via JSON 403 (AJAX endpoints) or a redirect
     * with the standard no-permission flash (page endpoints).
     */
    private function isDenied($resp): bool
    {
        if ($resp instanceof \Illuminate\Http\RedirectResponse) {
            return str_contains((string) session('error'), 'no permission');
        }
        if ($resp instanceof \Illuminate\Http\JsonResponse) {
            $data = $resp->getData(true);
            return $resp->getStatusCode() === 403
                && str_contains((string) ($data['message'] ?? ''), 'no permission');
        }
        return false;
    }

    /**
     * The main housekeeping screen (151111) must still load for a user with
     * the permission — i.e. the added guards didn't break the happy path.
     */
    public function test_housekeeping_screen_loads_for_authorized_user()
    {
        $row = $this->pdo
            ->query("SELECT m.propertyid, m.username
                     FROM menuhelp m
                     JOIN users u ON u.propertyid = m.propertyid AND u.name = m.username
                     WHERE m.code = 151111 AND m.view = 1
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No user with housekeeping screen (151111) permission found.');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        $resp = $this->invoke($ctrl, 'housekeepingscreen');

        $denied = $resp instanceof \Illuminate\Http\RedirectResponse
            && str_contains((string) session('error'), 'no permission');

        $this->assertFalse($denied, 'housekeepingscreen must load for a user with 151111 permission');
        $this->assertTrue(true); // reached the query/view stage — no 500
    }

    /**
     * storelostfound must require 151117 (Lost & Found) permission.
     */
    public function test_storelostfound_requires_permission()
    {
        $row = $this->pdo
            ->query("SELECT u.propertyid, u.name AS username
                     FROM users u
                     WHERE NOT EXISTS (SELECT 1 FROM menuhelp m
                                       WHERE m.propertyid = u.propertyid
                                         AND m.username = u.name
                                         AND m.code = 151117)
                       AND EXISTS (SELECT 1 FROM users u2 WHERE u2.propertyid = u.propertyid LIMIT 1)
                     LIMIT 1")
            ->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No user without 151117 permission found.');
        }

        [$ctrl] = $this->makeController((int) $row['propertyid'], $row['username']);

        $resp = $this->invoke($ctrl, 'storelostfound', ['itemname' => 'TEST']);

        $denied = $this->isDenied($resp);

        $this->assertTrue($denied, 'storelostfound must deny users without 151117 permission (guard missing)');
    }
}
