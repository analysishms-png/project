<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EInvoicePushLog extends Model
{
    use HasFactory;

    protected $table = 'einvoice_push_logs';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'docid',
        'company',
        'http_code',
        'success_yn',
        'status_cd',
        'curl_error',
        'post_data',
        'response_json'
    ];
}
