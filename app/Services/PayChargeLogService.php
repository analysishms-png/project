<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Centralized audit-log writes for the paychargelog table.
 *
 * Financial audit trails (advance deletion, charge removal, banquet advance
 * deletion, cheque restore) must never be written directly from controllers —
 * rule 10.6 of the HMS development standards. This service is the single
 * source of truth for paychargelog inserts so the log structure and business
 * rules stay consistent and auditable.
 *
 * NOTE: this is a pure insert wrapper. It deliberately does NOT alter the
 * existing column payloads built by the callers — behavior is identical to
 * the former inline DB::table('paychargelog')->insert(...) calls.
 */
class PayChargeLogService
{
    /**
     * Insert a single audit row into paychargelog.
     *
     * @param  array<string, mixed>  $row
     */
    public static function store(array $row): void
    {
        if (empty($row)) {
            return;
        }

        DB::table('paychargelog')->insert($row);
    }

    /**
     * Insert multiple audit rows into paychargelog in one statement.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function storeMany(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        DB::table('paychargelog')->insert($rows);
    }
}
