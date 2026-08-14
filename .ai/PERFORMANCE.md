# Analysis HMS - Performance Optimization

## Performance Overview

This document describes performance optimization strategies for the Analysis HMS project.

---

## Performance Metrics

### Key Metrics

| Metric | Target | Current |
|--------|--------|---------|
| Page Load Time | < 2s | 2.5s |
| API Response Time | < 500ms | 600ms |
| Database Query Time | < 100ms | 150ms |
| Memory Usage | < 256MB | 300MB |
| CPU Usage | < 70% | 75% |

---

## Database Optimization

### Query Optimization

#### 1. Eager Loading
```php
// Bad: N+1 query
$bookings = Booking::all();
foreach ($bookings as $booking) {
    echo $booking->guestProf->name; // Additional query
}

// Good: Eager loading
$bookings = Booking::with('guestProf')->get();
foreach ($bookings as $booking) {
    echo $booking->guestProf->name; // No additional query
}
```

#### 2. Select Specific Columns
```php
// Bad: Select all columns
$rooms = RoomMast::all();

// Good: Select specific columns
$rooms = RoomMast::select('room_no', 'room_cat', 'floor')->get();
```

#### 3. Use Indexes
```php
// Add indexes to frequently queried columns
Schema::table('booking', function (Blueprint $table) {
    $table->index('arrival');
    $table->index('departure');
    $table->index('guest_name');
});
```

#### 4. Avoid Functions in Queries
```php
// Bad: Function in WHERE clause
$bookings = Booking::whereRaw('YEAR(arrival) = 2026')->get();

// Good: Use range
$bookings = Booking::where('arrival', '>=', '2026-01-01')
    ->where('arrival', '<=', '2026-12-31')
    ->get();
```

### Database Configuration

```php
// config/database.php
'mysql' => [
    'sticky' => true,
    'strict' => false, // Disable strict mode for performance
    'engine' => null, // Use InnoDB by default
],
```

---

## Caching Strategies

### File Caching

```php
// Cache a query result
$rooms = Cache::remember('rooms', 60 * 60, function () {
    return RoomMast::all();
});

// Cache a value
Cache::put('key', 'value', 60 * 60);

// Get cached value
$value = Cache::get('key', 'default');
```

### Redis Caching

```php
// Configure Redis
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
],

// Use Redis
Cache::store('redis')->get('key');
```

### Database Caching

```php
// Cache query results
$rooms = DB::table('room_mast')
    ->cacheFor(60 * 60)
    ->get();
```

---

## Queue Processing

### Background Jobs

```php
// Create job
php artisan make:job ProcessReportJob

// Dispatch job
ProcessReportJob::dispatch($reportId);

// Process queue
php artisan queue:work
```

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

---

## Memory Optimization

### Chunking Large Datasets

```php
// Bad: Load all records
$bookings = Booking::all();
foreach ($bookings as $booking) {
    // Process
}

// Good: Chunk records
Booking::chunk(100, function ($bookings) {
    foreach ($bookings as $booking) {
        // Process
    }
});
```

### Cursor for Large Datasets

```php
// Use cursor for memory efficiency
$cursor = Booking::cursor();
foreach ($cursor as $booking) {
    // Process
}
```

### Unset Variables

```php
// Free memory
unset($largeArray);
gc_collect_cycles();
```

---

## Frontend Optimization

### Asset Compilation

```bash
# Build assets
npm run build

# Development
npm run dev
```

### Lazy Loading

```blade
<!-- Lazy load images -->
<img src="placeholder.jpg" data-src="image.jpg" class="lazyload">

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.lazyload');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    observer.unobserve(img);
                }
            });
        });
        images.forEach(img => observer.observe(img));
    });
</script>
```

### Minification

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        minify: 'terser',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['jquery'],
                },
            },
        },
    },
});
```

---

## API Optimization

### Pagination

```php
// Bad: Load all records
$bookings = Booking::all();

// Good: Paginate
$bookings = Booking::paginate(20);
```

### Response Caching

```php
// Cache API response
Route::get('/api/rooms', function () {
    return Cache::remember('api.rooms', 60 * 60, function () {
        return RoomMast::all();
    });
});
```

### Rate Limiting

```php
// Limit API requests
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/rooms', [RoomController::class, 'index']);
});
```

---

## Server Optimization

### PHP Configuration

```ini
; php.ini
memory_limit = 512M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
```

### Apache Configuration

```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Monitoring

### Laravel Telescope

```php
// Install Telescope
composer require laravel/telescope
php artisan telescope:install

// Access dashboard
Route::get('/telescope', [TelescopeController::class, 'index'])
    ->middleware('auth', 'admin');
```

### Logging

```php
// Log performance
$start = microtime(true);

// Code to measure

$duration = microtime(true) - $start;
Log::info('Performance', [
    'action' => 'booking_search',
    'duration' => $duration,
]);
```

---

## Performance Testing

### Load Testing

```php
// tests/Feature/PerformanceTest.php
public function test_room_list_performance()
{
    $start = microtime(true);
    
    $response = $this->get('/room');
    
    $duration = microtime(true) - $start;
    $this->assertLessThan(2, $duration);
    $response->assertStatus(200);
}
```

### Benchmarking

```php
// Benchmark a function
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    // Function to benchmark
}
$duration = microtime(true) - $start;
echo "Duration: {$duration}s\n";
```

---

## Performance Checklist

### Database
- [ ] Add indexes to frequently queried columns
- [ ] Use eager loading for relationships
- [ ] Avoid N+1 queries
- [ ] Use pagination for large datasets
- [ ] Optimize slow queries

### Caching
- [ ] Cache frequently accessed data
- [ ] Use Redis for caching
- [ ] Implement cache invalidation
- [ ] Monitor cache hit rates

### Frontend
- [ ] Compile and minify assets
- [ ] Lazy load images
- [ ] Enable gzip compression
- [ ] Use browser caching

### Server
- [ ] Enable OPcache
- [ ] Configure PHP memory limit
- [ ] Optimize Apache/Nginx
- [ ] Monitor server resources

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
