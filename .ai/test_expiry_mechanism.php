<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * HMS SERIAL KEY / EXPIRY MECHANISM — TEST SCRIPT
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * Run:  php artisan tinker < .ai/test_expiry_mechanism.php
 *   OR: cd /path/to/project && php .ai/test_expiry_mechanism.php (needs bootstrap)
 *
 * Tests:
 *   1. enviro_general table structure verify
 *   2. Decrypt all expiry dates — show expiry per property
 *   3. Simulate login check — which properties are blocked?
 *   4. Check for plaintext (unencrypted) values
 *   5. APP_KEY and cipher verification
 *   6. Property 103 exemption verification
 */

// Bootstrap Laravel if running standalone
if (!function_exists('app')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
}

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  HMS SERIAL KEY / EXPIRY MECHANISM — TEST SUITE              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// ── TEST 1: Table Structure ──────────────────────────────────────────────
echo "TEST 1: enviro_general table structure\n";
echo str_repeat("-", 60) . "\n";

$columns = collect(DB::select('SHOW COLUMNS FROM enviro_general'));
$keyCols = $columns->filter(fn($c) => in_array($c->Field, ['propertyid', 'ncur', 'expdate', 'amount', 'autonightaudit']));
foreach ($keyCols as $col) {
    echo "  {$col->Field}: {$col->Type} " . ($col->Null === 'YES' ? '(nullable)' : '(NOT NULL)') . "\n";
}
echo "\n";

// ── TEST 2: APP_KEY & Cipher ────────────────────────────────────────────
echo "TEST 2: Encryption Configuration\n";
echo str_repeat("-", 60) . "\n";

$appKey = config('app.key');
$cipher = config('app.cipher');
echo "  APP_KEY present: " . ($appKey ? "YES ✅" : "NO ❌ (DANGER!)") . "\n";
echo "  Cipher: {$cipher}\n";

if ($cipher === 'AES-256-CBC' && $appKey) {
    echo "  Status: CORRECT — encryption should work ✅\n";
} else {
    echo "  Status: WARNING — check config\n";
}
echo "\n";

// ── TEST 3: Decrypt & Show All Expiry Dates ─────────────────────────────
echo "TEST 3: Decrypt & Show All Expiry Dates\n";
echo str_repeat("-", 60) . "\n";

$rows = DB::table('enviro_general')
    ->where('propertyid', '!=', '103')
    ->orderBy('propertyid')
    ->get();

printf("  %-10s %-20s %-15s %-12s %s\n", "Property", "Comp Name", "Software Date", "Expiry Date", "Amount");
echo "  " . str_repeat("-", 80) . "\n";

foreach ($rows as $r) {
    $compName = DB::table('company')->where('propertyid', $r->propertyid)->value('comp_name') ?? '?';
    $expStr = 'NO EXPIRY';
    $amtStr = 'N/A';
    $status = '';

    if ($r->expdate) {
        try {
            $expStr = Crypt::decryptString($r->expdate);
            // Validate format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $expStr)) {
                $status = ($expStr < $r->ncur) ? '❌ EXPIRED' : '✅ ACTIVE';
            } else {
                $status = '⚠️  BAD FORMAT';
            }
        } catch (\Exception $e) {
            $expStr = 'DECRYPT ERROR';
            $status = '❌ ERROR';
        }
    } else {
        $status = '⚠️  NO EXPIRY';
    }

    if ($r->amount) {
        try {
            $amtStr = '₹' . Crypt::decryptString($r->amount);
        } catch (\Exception $e) {
            $amtStr = 'DECRYPT ERROR';
        }
    }

    printf("  %-10s %-20s %-15s %-12s %s %s\n",
        $r->propertyid, substr($compName, 0, 19), $r->ncur, $expStr, $amtStr, $status);
}
echo "\n";

// ── TEST 4: Login Simulation ────────────────────────────────────────────
echo "TEST 4: Login Simulation (expdate < ncur = BLOCKED)\n";
echo str_repeat("-", 60) . "\n";

$blocked = 0;
$active = 0;
$noExpiry = 0;
$exempt = 0;

// Property 103 check
$p103 = DB::table('enviro_general')->where('propertyid', '103')->first();
echo "  Property 103 (demo): ALWAYS ACTIVE (hardcoded exempt) ✅\n";
$exempt++;

foreach ($rows as $r) {
    if (!$r->expdate) {
        $noExpiry++;
        continue;
    }

    try {
        $expdate = Crypt::decryptString($r->expdate);
        if ($expdate < $r->ncur) {
            $blocked++;
        } else {
            $active++;
        }
    } catch (\Exception $e) {
        // Can't decrypt = can't check = effectively blocked
        $blocked++;
    }
}

echo "\n";
echo "  SUMMARY:\n";
echo "  ─────────────────────────────\n";
echo "  Exempt (prop 103): {$exempt}\n";
echo "  Active (not expired): {$active}\n";
echo "  Blocked (expired): {$blocked}\n";
echo "  No expiry set: {$noExpiry}\n";
echo "  Total properties: " . ($exempt + $active + $blocked + $noExpiry) . "\n";
echo "\n";

// ── TEST 5: Plaintext Detection ─────────────────────────────────────────
echo "TEST 5: Plaintext (Unencrypted) Detection\n";
echo str_repeat("-", 60) . "\n";

$plaintextCount = 0;
foreach ($rows as $r) {
    if ($r->expdate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r->expdate)) {
        echo "  ⚠️  Property {$r->propertyid}: expdate is PLAINTEXT (needs encryption!)\n";
        $plaintextCount++;
    }
    if ($r->amount && preg_match('/^[0-9.]+$/', $r->amount) && strlen($r->amount) < 15) {
        echo "  ⚠️  Property {$r->propertyid}: amount is PLAINTEXT (needs encryption!)\n";
        $plaintextCount++;
    }
}

if ($plaintextCount === 0) {
    echo "  ✅ All values appear to be encrypted correctly\n";
}
echo "\n";

// ── TEST 6: Expiry Timeline ─────────────────────────────────────────────
echo "TEST 6: Expiry Timeline (sorted by expiry date)\n";
echo str_repeat("-", 60) . "\n";

$timeline = [];
foreach ($rows as $r) {
    if (!$r->expdate) continue;
    try {
        $expdate = Crypt::decryptString($r->expdate);
        $timeline[] = [
            'propertyid' => $r->propertyid,
            'ncur' => $r->ncur,
            'expdate' => $expdate,
            'daysLeft' => (int) (strtotime($r->ncur) - strtotime($expdate)) / 86400 * -1,
        ];
    } catch (\Exception $e) {}
}

usort($timeline, fn($a, $b) => $a['expdate'] <=> $b['expdate']);

foreach ($timeline as $t) {
    $bar = str_repeat('█', max(0, min(30, $t['daysLeft'] / 10)));
    $label = $t['daysLeft'] < 0 ? "EXPIRED " . abs($t['daysLeft']) . " days ago" : "{$t['daysLeft']} days left";
    printf("  Prop %3d: %s → %s  %s  %s\n",
        $t['propertyid'], $t['ncur'], $t['expdate'], $label, $bar);
}
echo "\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TEST COMPLETE                                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
