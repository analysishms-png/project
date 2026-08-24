<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

echo 'driver=' . config('cache.default') . ' store=' . CacheService::store() . PHP_EOL;

Cache::forget('ver:testg');
$v1 = CacheService::version('testg');
$val = CacheService::remember('testkey', 60, function () {
    return ['hello' => 'world'];
});
$hit = CacheService::remember('testkey', 60, function () {
    return ['different' => 'value'];
});
echo 'v1=' . $v1 . ' first=' . json_encode($val) . ' cached=' . json_encode($hit) . PHP_EOL;

CacheService::bump('testg');
$v2 = CacheService::version('testg');
$after = CacheService::remember('testkey2', 60, function () {
    return 'fresh-after-bump';
});
echo 'v2=' . $v2 . ' new-key-ok=' . $after . PHP_EOL;
echo 'redisUp=' . (CacheService::redisUp() ? 'yes' : 'no') . PHP_EOL;
echo 'ALL OK' . PHP_EOL;
