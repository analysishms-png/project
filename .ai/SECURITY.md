# Analysis HMS - Security Documentation

## Security Overview

This document describes security practices and OWASP compliance for the Analysis HMS project.

**Sources**: `.ai/ANALYSIS_REPORT.md` (deep analysis), `.ai/RESEARCH.md` (2026 web research), `.ai/UPGRADE_PLAN.md` (remediation roadmap)

## ⚠️ Verified by composer audit (2026-08-07)

`composer audit` result: **29 advisories / 6 packages + 1 abandoned**.

| Package | Installed | Fixed In | Fix on L10? |
|---------|-----------|----------|-------------|
| dompdf/dompdf | 3.1.5 | 3.1.6 | ✅ `composer update` |
| guzzlehttp/guzzle | 7.11.0 | 7.15.2 | ✅ `composer update` |
| guzzlehttp/psr7 | 2.11.0 | 2.12.3 | ✅ `composer update` |
| league/commonmark | 2.8.2 | 2.9.0 | ✅ `composer update` |
| phpoffice/phpspreadsheet | 5.7.0 | 5.8.1+ | ✅ `composer update` |
| laravel/framework | 10.50.2 | 12.60.0+ | ❌ needs L12 upgrade (EOL) |
| beyondcode/laravel-websockets | 1.14.1 | — | ⚠️ migrate to Reverb |

**✅ EXECUTED 2026-08-07 (Phase 1)**: dompdf→3.1.6, guzzle→7.15.3, psr7→2.13.0, commonmark→2.9.0, phpspreadsheet→5.9.0. Audit now **3 advisories / 1 package** (only EOL laravel/framework). beyondcode websockets removed → **Laravel Reverb ^1.0** migrated (BROADCAST_DRIVER=reverb).

---

## 📅 2026 Research & CVE Snapshot (verified 2026-08-07)

### Framework / Runtime Lifecycle Status

| Component | Status | Risk | Action |
|-----------|--------|------|--------|
| Laravel 10.50.2 | 🔴 **EOL** — security fixes ended early 2025 | HIGH | Upgrade to Laravel 12 (`.ai/UPGRADE_PLAN.md` Phase 2–3) |
| PHP 8.2.33 | 🟠 **Security-only** — EOL Dec 31, 2026 | MED | Upgrade to PHP 8.3/8.4 (Phase 4); CVE-2024-4577 (Windows CGI) fixed in point releases |
| Git | 🔴 **No repository** | HIGH | `git init` immediately (no rollback capability) |

### Dependency CVEs & Advisories (2025–2026)

| Package | Installed | Advisory / CVE | Severity | Required Action |
|---------|-----------|----------------|----------|-----------------|
| `phpoffice/phpspreadsheet` | ^5.0 (5.7.0) | **CVE-2026-34084** (RCE) + bypass **CVE-2026-45034**; **CVE-2025-54370** (SSRF via HTML/WEBSERVICE); **CVE-2025-22131** (XSS); GHSA-xh5m-36r6-47m3 & GHSA-2mrg-gjxq-2gvr (memory exhaustion, Jul 2026) | 🔴 HIGH | Pin latest patched 5.x/6.x + `composer audit` |
| `yajra/laravel-datatables-oracle` | ^10.11 | **AIKIDO-2025-10705** (RCE via dynamic Blade template eval) — affects 5.11.7 → 12.5.1; **our ^10.11 is IN RANGE** | 🔴 HIGH | Upgrade to **v12.6.0+ / v13** (with Laravel 12) |
| `beyondcode/laravel-websockets` | ^1.14 | **ABANDONED** on Packagist; no CVEs logged but unmaintained; incompatible with Laravel 11/12 | 🟠 MED | Migrate to **Laravel Reverb** / Soketi (Phase 1) |
| `simplesoftwareio/simple-qrcode` | ~4 | Stagnant; no direct CVE; depends on old bacon/qr-code | 🟡 LOW | Replace with `endroid/qr-code` (already installed, ^5.0 → upgrade 6.x) |
| `barryvdh/laravel-dompdf` | ^3.0 | Active; remote access **disabled by default** since 3.x (good); keep updated | 🟢 OK | Update to latest 3.x |
| `endroid/qr-code` | ^5.0 | Active; no advisories | 🟢 OK | Upgrade to 6.x when convenient |
| `laravel/sanctum` | ^3.2 | Active (first-party); no critical CVEs | 🟢 OK | Upgrade to ^4.0 with Laravel 11+ |

### Ongoing Checks (make this a habit)
```bash
composer audit                       # run after every install/update + in CI
composer update phpoffice/phpspreadsheet --with-dependencies
```

---

## 🔍 Project-Specific Findings (from deep analysis 2026-08-07)

| ID | Finding | OWASP | Severity | Location / Fix |
|----|---------|-------|----------|----------------|
| SEC-01 | **No Git repository** — zero version control, .env unprotected in practice | A09/A05 | 🔴 HIGH | `git init` + baseline commit |
| SEC-02 | **APP_DEBUG=true, APP_ENV=local** — stack traces/env leak | A05 | 🔴 HIGH | Set `APP_DEBUG=false`, `APP_ENV=production` before deployment |
| SEC-03 | **Stored XSS**: `{!! $ticket->problem !!}` (unescaped user content) | A03 | 🔴 HIGH | `resources/views/tools/tickets.blade.php:394`, `admin/tools/tickets.blade.php:315`, `property/mytickets.blade.php:305` → use `{{ }}` or HTMLPurifier |
| SEC-04 | Raw CMS output `{!! $page->content !!}` | A03 | 🟠 MED | `frontend/page.blade.php:8,13` — OK only if superadmin-only editor |
| SEC-05 | Raw JSON in inline `<script>`: `session('infosale')['printdata']`, `session('nightinfo')['bills']` | A03 | 🟠 MED | `property/layouts/header.blade.php:670,768` — json_encode safely |
| SEC-06 | Dynamic SQL interpolation in `DB::raw()` (paycode/alias vars) | A03 | 🟠 MED | `Reporting.php`, `CheckRegister.php` — verify sources; bind parameters |
| SEC-07 | `DB::select("SHOW TABLES LIKE '{$tableName}'")`, `whereRaw($sqlWhere)` in SuperAdmin Tools | A03 | 🟠 MED | `Tools/ToolsController.php:2428,2431,2508,2534,2942,2958,2997,3080` — confirm whitelist-only |
| SEC-08 | `@shell_exec('chmod -R 777 ...')` | A05 | 🟡 LOW | `MainController.php:984`, `SetFolderPermissions.php:80` — restrict to 755/775 |
| SEC-09 | INFO-level logging of doc IDs / reasons | A09 | 🟡 LOW | `PythonRoomKeyController.php:20,59,98` — reduce log verbosity |
| SEC-10 | Abandoned websockets dependency | A06 | 🟠 MED | Migrate to Reverb (Phase 1) |

**Verified good**: All models use `$fillable` (mass-assignment protected ✅), config uses `env()` only (no committed secrets ✅), CSRF middleware active ✅, custom auth middleware stack (company/staff/superadmin/user/ApiAuth/ProtectRoute) ✅, `.env` in `.gitignore` ✅.

---

## OWASP Top 10 Compliance

### A01: Broken Access Control

#### Prevention
- Use Laravel's built-in authorization
- Implement role-based access control
- Validate user permissions
- Log access attempts

#### Implementation
```php
// Middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Policy
class RoomPolicy
{
    public function view(User $user, RoomMast $room)
    {
        return $user->can('view-room');
    }
}
```

---

### A02: Cryptographic Failures

#### Prevention
- Use bcrypt for passwords
- Encrypt sensitive data
- Use HTTPS
- Protect encryption keys

#### Implementation
```php
// Password hashing
Hash::make($password);

// Encryption
$encrypted = Crypt::encryptString($sensitiveData);
$decrypted = Crypt::decryptString($encrypted);

// Environment variables
APP_KEY=base64:...
```

---

### A03: Injection

#### Prevention
- Use Eloquent ORM
- Use prepared statements
- Validate input
- Sanitize output

#### Implementation
```php
// Eloquent (safe)
$users = User::where('email', $request->email)->get();

// Query Builder (safe)
$users = DB::table('users')
    ->where('email', '=', $request->email)
    ->get();

// Raw query (use with caution)
$users = DB::select('SELECT * FROM users WHERE email = ?', [$request->email]);
```

---

### A04: Insecure Design

#### Prevention
- Follow secure design principles
- Implement defense in depth
- Use threat modeling
- Review design regularly

#### Implementation
```php
// Multi-tenant isolation
class PropertyScope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('property_id', auth()->user()->property_id);
    }
}
```

---

### A05: Security Misconfiguration

#### Prevention
- Use environment variables
- Disable debug mode in production
- Remove default credentials
- Update software regularly

#### Implementation
```env
# .env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...

DB_PASSWORD=strong_password
```

---

### A06: Vulnerable Components

#### Prevention
- Regular dependency updates
- Monitor security advisories
- Use composer audit
- Remove unused dependencies

#### Implementation
```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update
```

---

### A07: Auth Failures

#### Prevention
- Implement rate limiting
- Use multi-factor authentication
- Secure password reset
- Log authentication attempts

#### Implementation
```php
// Rate limiting
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});

// Login attempt logging
Auth::attempt($credentials);
Log::info('Login attempt', ['user' => $credentials['email']]);
```

---

### A08: Data Integrity Failures

#### Prevention
- Verify file uploads
- Use checksums
- Validate data integrity
- Implement audit trails

#### Implementation
```php
// File upload validation
$request->validate([
    'file' => 'required|file|max:2048|mimes:pdf,jpg,png',
]);

// Checksum verification
$checksum = hash_file('sha256', $filePath);
```

---

### A09: Logging Failures

#### Prevention
- Log security events
- Protect log files
- Monitor logs
- Set up alerts

#### Implementation
```php
// Security logging
Log::channel('security')->info('Login failed', [
    'email' => $request->email,
    'ip' => $request->ip(),
]);

// Activity logging
Activity::log('User logged in', ['user_id' => auth()->id()]);
```

---

### A10: SSRF

#### Prevention
- Validate URLs
- Use whitelist
- Block internal IPs
- Monitor requests

#### Implementation
```php
// URL validation
$validator = Validator::make($request->all(), [
    'url' => 'required|url',
]);

// Block internal IPs
$ip = parse_url($url, PHP_URL_HOST);
if (in_array($ip, ['127.0.0.1', 'localhost', '::1'])) {
    throw new \Exception('Internal URLs not allowed');
}
```

---

## Authentication

### Laravel Sanctum

```php
// API token authentication
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}

// Issue token
$token = $user->createToken('auth-token')->plainTextToken;

// Authenticate
Sanctum::actingAs($user);
```

### Session Authentication

```php
// Web authentication
Auth::attempt($credentials);

// Check authentication
if (Auth::check()) {
    // User is logged in
}

// Logout
Auth::logout();
```

---

## Authorization

### Policies

```php
// RoomPolicy.php
class RoomPolicy
{
    public function viewAny(User $user)
    {
        return $user->can('view-any-room');
    }

    public function view(User $user, RoomMast $room)
    {
        return $user->can('view-room');
    }

    public function update(User $user, RoomMast $room)
    {
        return $user->can('update-room');
    }
}
```

### Gates

```php
// AuthServiceProvider.php
Gate::define('admin', function ($user) {
    return $user->role === 'admin';
});

Gate::define('manager', function ($user) {
    return in_array($user->role, ['admin', 'manager']);
});
```

---

## CSRF Protection

### Automatic Protection
Laravel automatically verifies CSRF tokens for POST, PUT, PATCH, DELETE requests.

### Manual Protection
```blade
<form method="POST" action="/reservation">
    @csrf
    <input type="text" name="guest_name">
</form>
```

### Excluding Routes
```php
// VerifyCsrfToken.php
protected $except = [
    'api/*',
    'webhook/*',
];
```

---

## XSS Prevention

### Blade Escaping
```blade
<!-- Auto-escaped -->
{{ $variable }}

<!-- Raw (use with caution) -->
{!! $variable !!}
```

> ⚠️ **Project violation found**: `{!! $ticket->problem !!}` in 3 ticket views (SEC-03). Never use `{!! !!}` with user-supplied content unless sanitized (e.g., HTMLPurifier).

### Input Sanitization
```php
// Sanitize input
$clean = strip_tags($request->input('name'));
$clean = htmlspecialchars($request->input('name'), ENT_QUOTES, 'UTF-8');
```

---

## SQL Injection Prevention

### Eloquent (Safe)
```php
$users = User::where('email', $request->email)->get();
```

### Query Builder (Safe)
```php
$users = DB::table('users')
    ->where('email', $request->email)
    ->get();
```

### Raw Queries (Use with Caution)
```php
$users = DB::select('SELECT * FROM users WHERE email = ?', [$request->email]);
```

> ⚠️ **Project review needed**: `DB::raw()` string interpolation in Reporting/CheckRegister and `SHOW TABLES`/`whereRaw` in Tools (SEC-06/07). Always bind parameters: `whereRaw('col = ?', [$val])`; never concatenate request input into raw SQL. Confirm `$allowedTables` is a hardcoded whitelist.

---

## File Upload Security

### Validation
```php
$request->validate([
    'file' => [
        'required',
        'file',
        'max:2048', // 2MB
        'mimes:pdf,jpg,png',
    ],
]);
```

### Storage
```php
// Store in private storage
$path = $request->file('file')->store('private', 'local');

// Store with unique name
$path = $request->file('file')->storeAs('uploads', uniqid() . '.' . $file->getClientOriginalExtension());
```

### Access Control
```php
// Route
Route::get('/download/{file}', [DownloadController::class, 'download'])
    ->middleware('auth');
```

---

## Rate Limiting

### Global Rate Limiting
```php
// AppServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Route-Specific Rate Limiting
```php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});
```

---

## Encryption

### Configuration
```php
// .env
APP_KEY=base64:...
```

### Usage
```php
// Encrypt
$encrypted = Crypt::encryptString($value);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);
```

---

## Environment Variables

### Best Practices
1. Never commit `.env` file
2. Use different `.env` for each environment
3. Rotate secrets regularly
4. Use strong passwords

### Example
```env
# .env
APP_ENV=production
APP_DEBUG=false
DB_PASSWORD=strong_password
MAIL_PASSWORD=mail_password
```

---

## Security Monitoring

### Log Channels
```php
// config/logging.php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
    ],
],
```

### Alerting
```php
// Log critical events
Log::channel('security')->critical('Unauthorized access attempt', [
    'user' => $request->user(),
    'ip' => $request->ip(),
    'url' => $request->url(),
]);
```

---

## Security Checklist

### Before Deployment
- [ ] **Initialize Git** (SEC-01) — no production without version control
- [ ] Disable debug mode — `APP_DEBUG=false`, `APP_ENV=production` (SEC-02)
- [ ] **Fix stored XSS** in 3 ticket views (SEC-03)
- [ ] **Run `composer audit`** — fix phpspreadsheet + datatables advisories (CVE table above)
- [ ] **Upgrade Laravel 10 (EOL) → 12** and PHP 8.2 (EOL Dec 2026) → 8.3/8.4 per `.ai/UPGRADE_PLAN.md`
- [ ] Set strong APP_KEY
- [ ] Use HTTPS
- [ ] Remove default credentials
- [ ] Review access controls
- [ ] Enable CSRF protection
- [ ] Configure rate limiting
- [ ] Set up logging
- [ ] Test security measures

### Regular Maintenance
- [ ] Monitor security logs
- [ ] Update dependencies
- [ ] Review user access
- [ ] Audit permissions
- [ ] Test backups
- [ ] Review configurations

---

## Last Updated
- Date: August 7, 2026
- Version: 2.0 (added 2026 CVE snapshot + project findings)
