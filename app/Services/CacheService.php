<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Central cache gateway with automatic Redis -> File fallback.
 *
 * Safety rules (REDIS_JS_PLAN.md):
 * - Redis failure must NEVER break a page (health-wrapper fallback).
 * - Works identically on file driver today and redis driver tomorrow.
 *
 * Usage:
 *   CacheService::remember("perm:103:admin:121111", 300, fn() => MenuHelp::...);
 *   CacheService::forget("perm:103:admin:121111");
 *   CacheService::bump("permver:103:admin");       // driver-agnostic group invalidation
 *   CacheService::version("permver:103:admin");    // read current version
 */
class CacheService
{
    /** @var bool|null|null Null = not checked this request */
    protected static $redisHealthy = null;

    /**
     * Resolve the cache store to use. When the configured default is redis,
     * verify TCP reachability first (cheap socket probe, 150ms cap); fall back
     * to file if the Redis server is down so pages never fatal.
     */
    public static function store()
    {
        $default = config('cache.default');

        if ($default !== 'redis') {
            return $default;
        }

        if (self::$redisHealthy === null) {
            self::$redisHealthy = self::redisUp();
        }

        return self::$redisHealthy ? 'redis' : 'file';
    }

    /**
     * Cheap TCP reachability probe against the configured Redis server.
     * Uses fsockopen with a hard 150ms timeout instead of a protocol PING
     * so a hung/unavailable daemon can never stall a request.
     */
    public static function redisUp()
    {
        try {
            $host = config('database.redis.default.host', '127.0.0.1');
            $port = (int) config('database.redis.default.port', 6379);
            $fp = @fsockopen($host, $port, $errno, $errstr, 0.15);

            if ($fp === false) {
                return false;
            }

            fclose($fp);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function remember($key, $ttl, callable $callback)
    {
        return Cache::store(self::store())->remember($key, $ttl, $callback);
    }

    public static function get($key, $default = null)
    {
        return Cache::store(self::store())->get($key, $default);
    }

    public static function put($key, $value, $ttl)
    {
        return Cache::store(self::store())->put($key, $value, $ttl);
    }

    public static function forget($key)
    {
        Cache::store(self::store())->forget($key);
    }

    /**
     * Read the current integer version for a group key (initializes to 1).
     * Include the returned value inside dependent cache keys; bumping the
     * version instantly invalidates every old key on ANY cache driver.
     */
    public static function version($groupKey)
    {
        return (int) Cache::store(self::store())->rememberForever('ver:' . $groupKey, function () {
            return 1;
        });
    }

    /**
     * Increment a group version, instantly invalidating all keys that embed it.
     */
    public static function bump($groupKey)
    {
        $store = Cache::store(self::store());
        $key = 'ver:' . $groupKey;

        $current = (int) ($store->get($key) ?? 0);
        $store->forever($key, $current + 1);

        return $current + 1;
    }

    /**
     * Purge all cached report results for a property (Phase 3).
     * Call from any POST endpoint that mutates folio/sale/ledger/stock data.
     */
    public static function purgeReports($propertyid = null)
    {
        $propertyid = $propertyid ?: (optional(auth()->user())->propertyid ?: 'all');

        self::bump('rpt:' . $propertyid);
    }

    /**
     * Short-TTL result cache for read-only report fetch endpoints (Phase 3).
     * Key embeds the property report-version so purgeReports() invalidates
     * instantly on any data mutation; TTL (60s default) bounds staleness even
     * if a mutation path forgets to purge.
     *
     * Usage inside a fetch method:
     *   $rows = CacheService::reportRemember(__FUNCTION__, $this->propertyid,
     *       [$fd, $td, $status, $bank], 60, fn () => $q->orderBy(...)->get());
     */
    public static function reportRemember($method, $propertyid, array $params, $ttl, callable $callback)
    {
        $propertyid = $propertyid ?: 'all';
        $ver = self::version('rpt:' . $propertyid);
        $hash = md5(json_encode($params));

        return self::remember(
            "rpt:{$propertyid}:{$method}:v{$ver}:{$hash}",
            $ttl,
            $callback
        );
    }
}
