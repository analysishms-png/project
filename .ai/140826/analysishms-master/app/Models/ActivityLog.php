<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'propertyid',
        'username',
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'url',
        'method',
        'subject_id',
        'subject_type',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
