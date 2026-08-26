<?php

namespace App\Http\Middleware;

use App\Services\CacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Phase 3 (REDIS_JS_PLAN.md): short-TTL response cache for read-only
 * report fetch endpoints.
 *
 * Scope guard rails:
 * - Only POST requests whose path contains "fetch" are cached.
 * - Only JSON 200 responses are stored.
 * - Key embeds property + user + full input payload hash + the shared
 *   report version, so CacheService::purgeReports() invalidates instantly.
 * - TTL is a hard 60s ceiling on staleness even without purges.
 */
class CacheReportFetch
{
    public function handle(Request $request, Closure $next)
    {
        $isFetchPost = $request->isMethod('post')
            && Str::contains(strtolower($request->path()), 'fetch');

        if (!$isFetchPost || !auth()->check()) {
            return $next($request);
        }

        $propertyid = auth()->user()->propertyid;
        $username = auth()->user()->name;
        $ver = CacheService::version('rpt:' . $propertyid);
        $payload = self::hashInput($request->except(['_token', '_method']));

        $key = "rptresp:{$propertyid}:{$username}:{$ver}:"
            . Str::slug($request->path()) . ":{$payload}";

        $cached = CacheService::get($key);

        if ($cached !== null) {
            return response()->json($cached['body'], $cached['status']);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && Str::contains(
            (string) $response->headers->get('Content-Type'),
            'json'
        )) {
            try {
                $body = json_decode($response->getContent(), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    CacheService::put($key, ['body' => $body, 'status' => 200], 60);
                }
            } catch (\Throwable $e) {
                // never let caching break the response
            }
        }

        return $response;
    }

    /**
     * Order-independent hash of the request input: keys are sorted
     * recursively before encoding, so the same payload sent with a
     * different key order still hits the same cache entry.
     */
    public static function hashInput(array $data): string
    {
        self::sortKeys($data);

        return md5(json_encode($data));
    }

    private static function sortKeys(array &$data): void
    {
        ksort($data);

        foreach ($data as &$value) {
            if (is_array($value)) {
                self::sortKeys($value);
            }
        }
    }
}
