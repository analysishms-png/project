# Analysis HMS - Testing Documentation

## Testing Overview

This document describes the testing strategy and implementation for the Analysis HMS project.

---

## Testing Strategy

### Testing Pyramid

```
           ┌─────────────┐
           │   E2E Tests  │  (Few)
           ├─────────────┤
          │ Integration   │  (Some)
          │    Tests      │
         ├───────────────┐
        │   Unit Tests    │  (Many)
        └───────────────┘
```

### Testing Levels

| Level | Type | Coverage | Speed |
|-------|------|----------|-------|
| 1 | Unit Tests | Individual functions | Fast |
| 2 | Feature Tests | Complete workflows | Medium |
| 3 | Integration Tests | Component interaction | Slow |
| 4 | E2E Tests | User journeys | Very Slow |

---

## PHPUnit Tests

### Configuration

```php
// phpunit.xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Unit Tests

#### Example: Model Test
```php
<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RoomMast;

class RoomMastTest extends TestCase
{
    public function test_room_has_category()
    {
        $room = RoomMast::factory()->create();
        $this->assertNotNull($room->room_cat);
    }

    public function test_room_belongs_to_category()
    {
        $room = RoomMast::factory()->create();
        $this->assertInstanceOf(RoomCat::class, $room->roomCat);
    }
}
```

#### Example: Service Test
```php
<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AccountPosting;

class AccountPostingTest extends TestCase
{
    public function test_post_revenue()
    {
        $posting = new AccountPosting();
        $result = $posting->postRevenue(1, '2026-08-07', 1000);
        $this->assertTrue($result);
    }
}
```

### Feature Tests

#### Example: Controller Test
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reservation()
    {
        $response = $this->post('/reservation/store', [
            'guest_name' => 'John Doe',
            'arrival' => '2026-08-10',
            'departure' => '2026-08-12',
            'room_no' => '101',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('booking', [
            'guest_name' => 'John Doe',
        ]);
    }

    public function test_can_view_reservation()
    {
        $booking = Booking::factory()->create();
        $response = $this->get('/reservation/' . $booking->docid);
        $response->assertStatus(200);
    }
}
```

#### Example: API Test
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ApiReservationTest extends TestCase
{
    public function test_can_get_reservations()
    {
        Sanctum::actingAs($user = User::factory()->create());

        $response = $this->getJson('/api/reservations');
        $response->assertStatus(200);
    }

    public function test_can_create_reservation_via_api()
    {
        Sanctum::actingAs($user = User::factory()->create());

        $response = $this->postJson('/api/reservations', [
            'guest_name' => 'John Doe',
            'arrival' => '2026-08-10',
        ]);

        $response->assertStatus(201);
    }
}
```

---

## Pest Tests

### Configuration

```php
// Pest.php
uses(Tests\TestCase::class)->in('Unit', 'Feature');
```

### Example: Pest Test
```php
<?php
test('room has category', function () {
    $room = RoomMast::factory()->create();
    expect($room->room_cat)->not->toBeNull();
});

test('can create reservation', function () {
    $response = $this->post('/reservation/store', [
        'guest_name' => 'John Doe',
    ]);
    $response->assertStatus(200);
});
```

---

## Laravel Dusk Tests

### Configuration

```php
// tests/Browser/ExampleTest.php
use Laravel\Dusk\Browser;

class ExampleTest extends TestCase
{
    public function test_basic_example()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }
}
```

### Example: Dusk Test
```php
<?php
namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ReservationTest extends DuskTestCase
{
    public function test_create_reservation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/reservation/create')
                    ->type('guest_name', 'John Doe')
                    ->type('arrival', '2026-08-10')
                    ->click('button[type="submit"]')
                    ->assertSee('Reservation created');
        });
    }
}
```

---

## Playwright Tests

### Configuration

```javascript
// tests/Feature/reservation.spec.js
const { test, expect } = require('@playwright/test');

test('can create reservation', async ({ page }) => {
    await page.goto('/reservation/create');
    await page.fill('#guest_name', 'John Doe');
    await page.fill('#arrival', '2026-08-10');
    await page.click('button[type="submit"]');
    await expect(page.locator('.success')).toBeVisible();
});
```

---

## Regression Tests

### Purpose
Prevent previously fixed bugs from recurring.

### Implementation
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;

class RegressionTest extends TestCase
{
    public function test_bug_123_room_status_update()
    {
        // Bug: Room status not updating after check-in
        $booking = Booking::factory()->create();
        $response = $this->post('/reservation/checkin', [
            'docid' => $booking->docid,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('roomocc', [
            'room_no' => $booking->room_no,
            'status' => 'OCCUPIED',
        ]);
    }
}
```

---

## Edge Case Tests

### Example: Edge Case Tests
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;

class EdgeCaseTest extends TestCase
{
    public function test_reservation_with_past_date()
    {
        $response = $this->post('/reservation/store', [
            'arrival' => '2020-01-01', // Past date
        ]);
        $response->assertStatus(422);
    }

    public function test_reservation_with_invalid_room()
    {
        $response = $this->post('/reservation/store', [
            'room_no' => 'INVALID',
        ]);
        $response->assertStatus(422);
    }

    public function test_checkin_without_reservation()
    {
        $response = $this->post('/reservation/checkin', [
            'docid' => 'NONEXISTENT',
        ]);
        $response->assertStatus(404);
    }
}
```

---

## Smoke Tests

### Purpose
Verify basic functionality after deployment.

### Implementation
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_homepage_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_loads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_dashboard_loads()
    {
        $this->actingAs($user = User::factory()->create());
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }
}
```

---

## Performance Tests

### Example: Performance Test
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;

class PerformanceTest extends TestCase
{
    public function test_room_list_performance()
    {
        $start = microtime(true);
        
        $response = $this->get('/room');
        
        $duration = microtime(true) - $start;
        $this->assertLessThan(2, $duration); // Under 2 seconds
        $response->assertStatus(200);
    }

    public function test_report_generation_performance()
    {
        $start = microtime(true);
        
        $response = $this->post('/reports/daily', [
            'date' => '2026-08-07',
        ]);
        
        $duration = microtime(true) - $start;
        $this->assertLessThan(5, $duration); // Under 5 seconds
        $response->assertStatus(200);
    }
}
```

---

## Test Data Management

### Factories

```php
<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomMastFactory extends Factory
{
    public function definition()
    {
        return [
            'room_no' => $this->faker->unique()->numberBetween(1, 500),
            'room_cat' => 'DLX',
            'floor' => $this->faker->numberBetween(1, 10),
        ];
    }
}
```

### Seeders

```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        RoomMast::factory()->count(50)->create();
    }
}
```

---

## Test Coverage

### Configuration

```xml
<!-- phpunit.xml -->
<coverage>
    <include>
        <directory suffix=".php">app</directory>
    </include>
</coverage>
```

### Target Coverage
- **Unit Tests**: 80%+
- **Feature Tests**: 60%+
- **Overall**: 70%+

---

## Test Automation

### CI/CD Integration

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test
```

---

## Test Reporting

### Generate Report
```bash
php artisan test --coverage --min=70
```

### View Report
```bash
open coverage/index.html
```

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
