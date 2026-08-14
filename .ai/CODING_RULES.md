# Analysis HMS - Coding Rules

## Coding Standards Overview

This document defines coding standards for the Analysis HMS project.

---

## PSR Standards

### PSR-1: Basic Coding Standard

#### PHP Tags
```php
// Use PHP tags
<?php

// Use short echo tags
<?= $variable ?>
```

#### Character Encoding
```php
// Use UTF-8
header('Content-Type: text/html; charset=utf-8');
```

#### Side Effects
```php
// Avoid side effects in class files
// File should either define class, function, etc.
// Or execute logic
```

---

### PSR-4: Autoloading

#### Directory Structure
```
app/
├── Models/
│   └── User.php           # App\Models\User
├── Http/
│   └── Controllers/
│       └── UserController.php  # App\Http\Controllers\UserController
```

#### Namespace Convention
```php
namespace App\Models;

class User extends Model
{
    // ...
}
```

---

### PSR-12: Coding Style Guide

#### General Rules
- Use 4 spaces for indentation
- Use blank lines to separate blocks
- Use blank lines after namespace
- Use one `use` keyword per declaration

#### Class Rules
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // ...
}
```

#### Method Rules
```php
public function getUser($id)
{
    // ...
}
```

---

## Laravel Standards

### Controllers

#### Naming
- PascalCase for class names
- Plural for controller names
- Resource controllers

```php
class UserController extends Controller
{
    public function index()
    {
        // ...
    }
}
```

#### Structure
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        User::create($validated);

        return redirect()->route('users.index');
    }
}
```

---

### Models

#### Naming
- PascalCase for class names
- Singular for model names
- Table name is plural snake_case

```php
class RoomMast extends Model
{
    protected $table = 'room_mast';
    protected $primaryKey = 'room_no';
    public $incrementing = false;
    protected $keyType = 'string';
}
```

#### Relationships
```php
class Booking extends Model
{
    public function guestProf()
    {
        return $this->belongsTo(GuestProf::class, 'docid');
    }

    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class, 'docid');
    }
}
```

---

### Routes

#### Naming
- kebab-case for URLs
- camelCase for route names

```php
Route::get('/room-status', [RoomController::class, 'index'])
    ->name('room.status');

Route::post('/reservation/store', [ReservationController::class, 'store'])
    ->name('reservation.store');
```

#### Grouping
```php
Route::middleware(['auth', 'company'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});
```

---

### Views

#### Naming
- kebab-case for file names
- snake_case for variables

```blade
{{-- resources/views/room-status/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Room Status</h1>
    @foreach($rooms as $room)
        <p>{{ $room->room_no }}</p>
    @endforeach
@endsection
```

---

### Migrations

#### Naming
- snake_case for table names
- Timestamp prefix

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('room_mast', function (Blueprint $table) {
            $table->string('room_no')->primary();
            $table->string('room_cat');
            $table->integer('floor');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_mast');
    }
};
```

---

## Naming Conventions

### Classes
```php
// PascalCase
class UserController extends Controller
class RoomMast extends Model
class AccountPosting extends Service
```

### Methods
```php
// camelCase
public function getUser()
public function storeReservation()
public function calculateTax()
```

### Variables
```php
// camelCase
$user = User::find(1);
$roomMast = new RoomMast();
$reservationData = [];
```

### Constants
```php
// UPPER_SNAKE_CASE
const MAX_LOGIN_ATTEMPTS = 5;
const SESSION_LIFETIME = 120;
```

### Files
```php
// PascalCase for classes
UserController.php
RoomMast.php

// snake_case for config
app.php
database.php

// kebab-case for views
room-status.blade.php
```

---

## Comment Standards

### PHPDoc
```php
/**
 * Get user by ID
 *
 * @param int $id
 * @return User|null
 */
public function getUser($id)
{
    return User::find($id);
}
```

### Inline Comments
```php
// Calculate tax amount
$tax = $amount * ($taxPercent / 100);

/* 
 * Process reservation
 * - Validate input
 * - Create booking
 * - Assign room
 */
```

---

## Error Handling

### Try-Catch
```php
try {
    $result = $this->processData($data);
} catch (\Exception $e) {
    Log::error('Error processing data', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    throw new \Exception('Failed to process data');
}
```

### Validation
```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
]);
```

---

## Logging

### Log Levels
```php
Log::emergency($message);
Log::alert($message);
Log::critical($message);
Log::error($message);
Log::warning($message);
Log::notice($message);
Log::info($message);
Log::debug($message);
```

### Context
```php
Log::info('User logged in', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
]);
```

---

## Dependency Injection

### Constructor Injection
```php
class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
}
```

### Method Injection
```php
public function store(Request $request)
{
    // ...
}
```

---

## Validation

### Form Request
```php
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}
```

### Inline Validation
```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
]);
```

---

## Testing

### Test Structure
```php
<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_user_has_name()
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->name);
    }
}
```

### Test Naming
```php
// test_[what]_[condition]_[expected result]
public function test_user_with_valid_email_can_login()
public function test_room_with_invalid_status_throws_exception()
```

---

## Refactoring Rules

### When to Refactor
- Code duplication
- Long methods
- Complex conditionals
- Poor naming
- Missing tests

### Refactoring Steps
1. Understand current code
2. Write tests
3. Refactor
4. Run tests
5. Document changes

---

## Code Review Checklist

### Functionality
- [ ] Code does what it's supposed to do
- [ ] Edge cases are handled
- [ ] Error handling is implemented

### Code Quality
- [ ] Follows naming conventions
- [ ] No code duplication
- [ ] Functions are focused
- [ ] Comments are helpful

### Performance
- [ ] No N+1 queries
- [ ] Proper indexing
- [ ] Efficient algorithms

### Security
- [ ] Input validation
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] CSRF protection

### Testing
- [ ] Unit tests present
- [ ] Feature tests present
- [ ] Tests pass

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
