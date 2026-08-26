<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu permission for the new Inventory Insights page (/invinsights).
 *
 * Code 161121 (family: 16=Inventory, 11=Operation subheader, 21 leaf).
 * Grants view+print to every property-103 user already holding any
 * Inventory leaf code (161111-161120) — same sibling rollout pattern as
 * .ai/menu_permissions_missing_reports.sql Batch A.
 * Also ensures the 161100 "Operation" subheader row exists for those users,
 * otherwise the menu item stays hidden even with the leaf grant.
 *
 * Idempotent: safe to re-run (exists() guard per row).
 */
return new class extends Migration
{
    public function up(): void
    {
        $propertyid = '103';
        $code = 161121;
        $now = now();

        // Users already holding ANY inventory family leaf on this property.
        $grantees = DB::table('menuhelp')
            ->select('username', 'compcode')
            ->where('propertyid', $propertyid)
            ->whereBetween('code', [161111, 161120])
            ->distinct()
            ->get();

        foreach ($grantees as $g) {
            // Subheader visibility (161100 "Operation") — only if missing.
            $hasSubheader = DB::table('menuhelp')
                ->where('propertyid', $propertyid)
                ->where('username', $g->username)
                ->where('code', 161100)
                ->exists();

            if (!$hasSubheader) {
                DB::table('menuhelp')->insert([
                    'propertyid' => $propertyid,
                    'compcode' => $g->compcode,
                    'username' => $g->username,
                    'opt1' => 16, 'opt2' => 11, 'opt3' => 0,
                    'code' => 161100,
                    'route' => 'javascript:void()',
                    'module' => 'Operation',
                    'module_name' => 'Inventory',
                    'view' => 1, 'ins' => 0, 'edit' => 0, 'del' => 0, 'print' => 0,
                    'flag' => 'N',
                    'outletcode' => '',
                    'u_name' => 'sa',
                    'u_entdt' => $now,
                ]);
            }

            // Leaf entry for the Insights page itself — only if missing.
            $hasLeaf = DB::table('menuhelp')
                ->where('propertyid', $propertyid)
                ->where('username', $g->username)
                ->where('code', $code)
                ->exists();

            if (!$hasLeaf) {
                DB::table('menuhelp')->insert([
                    'propertyid' => $propertyid,
                    'compcode' => $g->compcode,
                    'username' => $g->username,
                    'opt1' => 16, 'opt2' => 11, 'opt3' => 21,
                    'code' => $code,
                    'route' => 'invinsights',
                    'module' => 'Inventory Insights',
                    'module_name' => 'Inventory',
                    'view' => 1, 'ins' => 0, 'edit' => 0, 'del' => 0, 'print' => 1,
                    'flag' => 'R',
                    'outletcode' => '',
                    'u_name' => 'sa',
                    'u_entdt' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('menuhelp')
            ->where('propertyid', '103')
            ->where('code', 161121)
            ->delete();
    }
};
