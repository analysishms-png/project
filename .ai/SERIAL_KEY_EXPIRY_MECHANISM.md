# HMS Licensing / Serial Key Mechanism — Full Analysis
## Date: 26 August 2026

---

## Overview

AnalysisHMS uses an **encrypted expiry-date license model**. Each hotel property
has an expiration date stored as AES-256-CBC ciphertext in the `enviro_general`
table. On login, the system decrypts this date and compares it against the
property's software date (`ncur`). If expired, login is blocked.

This is NOT a traditional "serial key / activation code" system — it's a
**time-based license** where the vendor manually sets an expiry date and a
license fee amount, both stored encrypted.

---

## Database Schema

**Table:** `enviro_general` (one row per property)

| Column | Type | Purpose |
|--------|------|---------|
| `propertyid` | int | FK to property |
| `ncur` | date | **Software date** (current business date, manually advanced via night audit) |
| `expdate` | text | **AES-256-CBC encrypted** expiry date (e.g., `"2026-09-01"`) |
| `amount` | text | **AES-256-CBC encrypted** license fee amount (e.g., `"11800.00"`) |

Note: `expdate` and `amount` are `TEXT` columns because they store Laravel
encryption output (base64-encoded ciphertext with IV), not plain dates/numbers.

---

## How It Works — Complete Flow

### 1. LICENSE PROVISIONING (Vendor Side)

The vendor (admin) sets expiry dates via the admin panel:

**Route:** `GET /expirymodule` → `MainController::showUpdateForm()`
**Admin Blade:** `resources/views/admin/paymode.blade.php`

The admin panel:
1. Lists all properties (excluding property 103 = demo)
2. Shows each property's current encrypted expdate/amount (decrypted for display)
3. Has a form to set new expiry date + amount for any property

**Code: Admin Panel (MainController.php:989-1003)**
```php
public function showUpdateForm()
{
    $properties = Companyreg::groupBy('propertyid')->get();

    $envgeneral = DB::table('enviro_general')
        ->leftJoin('company', 'company.propertyid', '=', 'enviro_general.propertyid')
        ->select(
            'company.comp_name',
            'enviro_general.propertyid',
            'enviro_general.ncur',
            'enviro_general.expdate',   // encrypted
            'enviro_general.amount'     // encrypted
        )
        ->where('enviro_general.propertyid', '!=', '103')
        ->orderBy('enviro_general.propertyid')
        ->get();

    return view('admin.paymode', compact('properties', 'envgeneral'));
}
```

### 2. ENCRYPT & SAVE (Vendor Sets Expiry)

**Route:** `POST /property/update-expiry` → `PropertyController::updateExpiry()`

**Code (PropertyController.php:571-590)**
```php
public function updateExpiry(Request $request)
{
    $request->validate([
        'propertyid' => 'required',
        'amount'     => 'required|numeric',
        'expdate'    => 'required|date',
    ]);

    // AES-256-CBC encryption using Laravel's Crypt facade
    // Key comes from APP_KEY in .env
    $encryptedAmount = Crypt::encryptString($request->amount);
    $encryptedDate   = Crypt::encryptString(
        Carbon::parse($request->expdate)->format('Y-m-d')
    );

    DB::table('enviro_general')
        ->where('propertyid', $request->propertyid)
        ->update([
            'amount'  => $encryptedAmount,
            'expdate' => $encryptedDate,
        ]);

    return back()->with('success', 'Expiry date & amount updated successfully.');
}
```

**Encryption details:**
- Algorithm: AES-256-CBC (configured in `config/app.php` as `CIPHER`)
- Key: Laravel `APP_KEY` from `.env`
- Method: `Crypt::encryptString()` → base64-encoded ciphertext
- Storage: TEXT column in MySQL

### 3. LOGIN CHECK (Enforcement)

**Code (Auth/LoginController.php:96-103)**
```php
$envgeneral = EnviroGeneral::where('propertyid', $userWithEmail->propertyid)->first();

if ($envgeneral && $envgeneral->expdate && $envgeneral->propertyid != 103) {
    $expdate = Crypt::decryptString($envgeneral->expdate);  // decrypt to "2026-09-01"
    $ncurdate = $envgeneral->ncur;                            // software date, e.g., "2026-08-05"

    if ($expdate < $ncurdate) {
        return back()->withErrors([
            'u_name' => 'Your account is expired. Please contact your software vendor.'
        ]);
    }
}
```

**Key details:**
- Property 103 (demo/internal) is **exempt** from expiry checks
- Compares decrypted expdate against `ncur` (software date), NOT server date
- If `ncur > expdate` → login blocked with error message
- Expiry check happens BEFORE password verification (early exit)

### 4. READ EXPIRY DATA (API for Admin Panel)

**Route:** `GET /get-expiry-data/{propertyid}` → `PropertyController::getExpiryData()`

**Code (PropertyController.php:555-568)**
```php
public function getExpiryData($propertyid)
{
    $data = DB::table('enviro_general')
        ->where('propertyid', $propertyid)
        ->first();

    if ($data) {
        return response()->json([
            'amount'  => Crypt::decryptString($data->amount),
            'expdate' => Carbon::parse(Crypt::decryptString($data->expdate))
                             ->format('Y-m-d')
        ]);
    }
    return response()->json(null);
}
```

### 5. BULK ENCRYPTION (Migration Script)

**File:** `app/Http/Controllers/General/UpgradingExpCrypt.php`

A one-time migration script that encrypts plaintext expiry dates/amounts for
existing properties. Used when converting from unencrypted to encrypted storage.

```php
class UpgradingExpCrypt extends Controller
{
    public function encryptexp(Request $request)
    {
        $data = [
            127 => ['ncur' => '2025-07-15', 'expdate' => '2025-09-01', 'amount' => '11800.00'],
            121 => ['ncur' => '2025-09-26', 'expdate' => '2025-09-27', 'amount' => '15000.00'],
            // ... 17 properties total
        ];

        foreach ($data as $propertyid => $values) {
            $record = EnviroGeneral::where('propertyid', $propertyid)->first();
            if ($record) {
                $record->expdate = Crypt::encryptString($values['expdate']);
                $record->amount  = Crypt::encryptString($values['amount']);
                $record->save();
            }
        }
    }
}
```

Route (commented out): `GET /cryptoencryption` → `UpgradingExpCrypt::encryptexp()`

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     VENDOR (Admin Panel)                     │
│                                                             │
│  /expirymodule                                              │
│  ┌─────────────────────────────────────────────┐            │
│  │ Select Property → Set Date → Set Amount     │            │
│  └──────────────┬──────────────────────────────┘            │
│                 │ POST /property/update-expiry               │
│                 ▼                                           │
│  ┌─────────────────────────────────────────────┐            │
│  │  Crypt::encryptString("2026-09-01")         │            │
│  │  → base64(ciphertext)                       │            │
│  │  → UPDATE enviro_general SET expdate = ...   │            │
│  └─────────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────────┘
                          │
                          │ MySQL
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                   enviro_general TABLE                       │
│                                                             │
│  propertyid │ ncur       │ expdate (TEXT)   │ amount (TEXT) │
│  ───────────┼────────────┼─────────────────┼───────────────│
│  101        │ 2026-08-05 │ AES256("10-31") │ AES256("11800")│
│  103        │ 2026-08-05 │ NULL (exempt)    │ NULL           │
│  106        │ 2026-08-05 │ AES256("04-01") │ AES256("11800")│
└─────────────────────────────────────────────────────────────┘
                          │
                          │ Login attempt
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                   LOGIN FLOW                                │
│                                                             │
│  1. User enters credentials                                │
│  2. System loads enviro_general for that property           │
│  3. IF propertyid != 103 AND expdate IS NOT NULL:           │
│     a. Decrypt expdate: Crypt::decryptString(expdate)       │
│     b. Compare: if (decrypted_expdate < ncur) → BLOCK       │
│  4. IF exempt or not expired: proceed with Auth::attempt()  │
└─────────────────────────────────────────────────────────────┘
```

---

## Security Properties

1. **Encrypted at rest** — expdate/amount stored as AES-256-CBC ciphertext
   (requires APP_KEY to decrypt)
2. **Property 103 exempt** — demo/internal property never expires
3. **Compares software date, not server date** — the `ncur` date is manually
   advanced via night audit, so the check respects the hotel's business calendar
4. **Early exit** — expiry check happens before password verification (prevents
   timing attacks that could distinguish "expired" from "wrong password")

---

## Known Limitations

1. **No grace period** — binary expired/not-expired, no warning before expiry
2. **ncur can be manipulated** — if someone manually edits `enviro_general.ncur`
   in the database, they can bypass the expiry check
3. **Single expiry per property** — no per-module or per-user licensing
4. **No offline validation** — if database is unreachable, expiry can't be checked
   (but login also fails for other reasons)
5. **Property 103 always exempt** — hardcoded bypass, no way to expire the demo

---

## Routes Summary

| Method | URI | Controller | Purpose |
|--------|-----|-----------|---------|
| GET | `/expirymodule` | `MainController::showUpdateForm` | Admin panel to manage expiry |
| POST | `/property/update-expiry` | `PropertyController::updateExpiry` | Save new expiry date/amount |
| GET | `/get-expiry-data/{id}` | `PropertyController::getExpiryData` | API to fetch decrypted expiry |
| GET | `/cryptoencryption` | `UpgradingExpCrypt::encryptexp` | Bulk encrypt (commented out) |
