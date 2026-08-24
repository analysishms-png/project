<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DEV STANDARDS COMPLIANCE: Add created_at and updated_at to legacy tables
     * that are missing these mandatory columns.
     *
     * SAFETY: Uses ALTER TABLE ADD COLUMN which is non-destructive.
     * Existing data is NOT modified. Columns are nullable to avoid breaking inserts.
     *
     * NOTE: This migration is staged for review. Run manually after approval:
     *   php artisan migrate --path=database/migrations/2026_08_24_000001_add_timestamps_to_legacy_tables.php
     */
    public function up(): void
    {
        // Get all tables that are missing created_at
        $tables = DB::select("
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = '" . config('database.connections.mysql.database') . "' 
            AND TABLE_TYPE = 'BASE TABLE'
            AND TABLE_NAME NOT IN (
                SELECT DISTINCT TABLE_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = '" . config('database.connections.mysql.database') . "' 
                AND COLUMN_NAME = 'created_at'
            )
            AND TABLE_NAME NOT LIKE 'cache%'
            AND TABLE_NAME NOT LIKE 'sessions%'
            AND TABLE_NAME NOT LIKE 'failed_jobs%'
            AND TABLE_NAME NOT LIKE '%_backup_%'
            AND TABLE_NAME NOT LIKE 'demo%'
            ORDER BY TABLE_NAME
        ");

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Add created_at if not exists
                if (!Schema::hasColumn($tableName, 'created_at')) {
                    $table->timestamp('created_at')->nullable()->after('id');
                }
                // Add updated_at if not exists
                if (!Schema::hasColumn($tableName, 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });
        }
    }

    public function down(): void
    {
        // Reverse: Remove added timestamps
        $tables = DB::select("
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = '" . config('database.connections.mysql.database') . "' 
            AND TABLE_TYPE = 'BASE TABLE'
        ");

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            if (in_array($tableName, ['activity_logs', 'migrations', 'users', 'password_resets', 'failed_jobs'])) {
                continue; // Don't touch Laravel core tables
            }
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['created_at', 'updated_at']);
                });
            } catch (\Exception $e) {
                // Skip if column doesn't exist
            }
        }
    }
};
