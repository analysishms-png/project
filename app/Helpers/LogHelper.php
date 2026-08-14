<?php

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

function logData($type, $message, $propertyid = null, $username = null, $line = null, $file = null)
{
    $user = Auth::user();

    $pid = $propertyid ?? ($user ? $user->propertyid : null);
    
    $uname = $username ?? ($user ? $user->name : 'system');

    Log::create([
        'propertyid' => $pid,
        'username' => $uname,
        'log_type' => $type,
        'message' => $message,
        'line' => $line,
        'file' => $file,
        'ip_address' => request()->ip()
    ]);
}
