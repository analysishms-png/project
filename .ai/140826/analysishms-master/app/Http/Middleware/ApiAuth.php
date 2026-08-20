<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiClient;

class ApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->route('api_key');

        if (!$apiKey) {
            return response()->json(['status' => false, 'message' => 'API key missing'], 401);
        }

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json(['status' => false, 'message' => 'Bearer token missing'], 401);
        }

        $token = $matches[1];

        $client = ApiClient::where('api_key', $apiKey)
            ->where('bearer_token', $token)
            ->where('is_active', 1)
            ->first();

        if (!$client) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->attributes->set('api_client', $client);

        return $next($request);
    }
}
