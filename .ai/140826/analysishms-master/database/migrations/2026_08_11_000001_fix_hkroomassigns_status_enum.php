<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix hkroomassigns.status enum mismatch.
 *
 * Code (HouseKeeping@submitcleaningentry) writes status = 'clean' when a room's
 * cleaning is completed, but the DB column was enum('cleaned','dirty'), so MySQL
 * silently stored '' (empty string) instead of 'clean'.
 *
 * Fix:
 *  1. Add 'clean' to the enum (keep 'cleaned' so existing values stay valid).
 *  2. Repair rows that were stored as '' (should have been 'clean').
 *
 * NOTE: `php artisan migrate` should NEVER be run in full on this project
 * (many production tables are created manually, not via migrations).
 * Run ONLY this file:
 *   php artisan migrate --path=database/migrations/2026_08_11_000001_fix_hkroomassigns_status_enum.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Broaden the enum so 'clean' (used by code) is accepted,
        //    keeping 'cleaned' for any legacy rows.
        DB::statement("ALTER TABLE hkroomassigns MODIFY COLUMN status ENUM('cleaned','clean','dirty') NOT NULL DEFAULT 'dirty'");

        // 2. Repair rows that silently stored '' instead of 'clean'.
        //    Scoped to Completed cleanings — the exact flow that wrote 'clean'.
        $repaired = DB::table('hkroomassigns')
            ->where('status', '')
            ->where('cleaningstatus', 'Completed')
            ->update(['status' => 'clean']);

        if ($repaired > 0) {
            \Illuminate\Support\Facades\Log::info("hkroomassigns.status enum fixed: {$repaired} empty rows repaired to 'clean'.");
        }
    }

    public function down(): void
    {
        // Map 'clean' rows to the legacy enum value BEFORE reverting, so the
        // data stays valid and is not silently coerced back to ''.
        DB::table('hkroomassigns')->where('status', 'clean')->update(['status' => 'cleaned']);
        DB::statement("ALTER TABLE hkroomassigns MODIFY COLUMN status ENUM('cleaned','dirty') NOT NULL DEFAULT 'dirty'");
    }
};
