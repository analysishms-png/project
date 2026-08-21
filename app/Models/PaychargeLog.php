<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaychargeLog extends Model
{
    protected $guarded = [];

    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'paychargelog';

    /**
     * FINANCIAL SAFETY (mission §9): never silently delete paycharge rows.
     *
     * Writes an audit row to paychargelog for every paycharge row about to be
     * deleted — user, timestamp, reason, original amounts (amtdr + amtcr) and
     * full linkage (docid, vno, folionodocid, refdocid) — so the transaction
     * stays traceable and reconciliation reports can account for it.
     *
     * Accepts a single paycharge row/model or an iterable of them.
     */
    public static function auditDeleted($rows, string $reason, ?string $user = null): void
    {
        if ($rows instanceof \Illuminate\Database\Eloquent\Model || is_object($rows) && !is_iterable($rows)) {
            $rows = [$rows];
        }

        $user = $user ?? (Auth::user()->u_name ?? Auth::user()->name ?? 'system');

        $log = [];
        foreach ($rows as $row) {
            $log[] = [
                'propertyid'   => $row->propertyid,
                'docid'        => $row->docid,
                'sno'          => $row->sno,
                'vtype'        => $row->vtype,
                'vno'          => $row->vno,
                'vprefix'      => $row->vprefix,
                'vdate'        => $row->vdate,
                'vtime'        => $row->vtime,
                'paycode'      => $row->paycode,
                'paytype'      => $row->paytype,
                'comments'     => $row->comments,
                'guestprof'    => $row->guestprof,
                'roomno'       => $row->roomno,
                'amtcr'        => $row->amtcr,
                'amtdr'        => $row->amtdr,
                'roomcat'      => $row->roomcat,
                'roomtype'     => $row->roomtype,
                'foliono'      => $row->foliono,
                'folionodocid' => $row->folionodocid,
                'refdocid'     => $row->refdocid,
                'restcode'     => $row->restcode,
                'billamount'   => $row->billamount,
                'taxper'       => $row->taxper,
                'onamt'        => $row->onamt,
                'taxcondamt'   => $row->taxcondamt,
                'taxstru'      => $row->taxstru,
                'remarks'      => $reason . (($row->u_name ?? '') !== '' ? ' (original u_name: ' . ($row->u_name ?? '') . ', original u_entdt: ' . ($row->u_entdt ?? '') . ')' : ''),
                'u_entdt'      => now(),
                'u_name'       => $user,
                'u_ae'         => 'e',
            ];
        }

        if (!empty($log)) {
            self::insert($log);
        }
    }
}
