<?php

namespace App\Services;

use Illuminate\Cache\CacheManager;

/**
 * Redis-resilient cache manager (REDIS_JS_PLAN.md Phase 0 safety rule:
 * "Redis failure must NEVER break a page").
 *
 * Any resolve('redis') — including plain Cache:: facade calls anywhere in the
 * app — transparently resolves to the file store while the Redis server is
 * unreachable (cheap 150ms TCP probe, cached per request via CacheService).
 */
class ResilientCacheManager extends CacheManager
{
    public function resolve($name)
    {
        if ($name === 'redis' && !CacheService::redisUp()) {
            $name = 'file';
        }

        return parent::resolve($name);
    }
}
