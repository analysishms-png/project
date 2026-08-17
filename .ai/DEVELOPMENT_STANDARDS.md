# Analysis Hotel Management System (HMS) — Development Standards & Coding Guidelines

Source: official project standards (provided by the project owner). These are **mandatory** for all developers working on the Analysis HMS project. The goal is to maintain consistency, scalability, performance, readability, and long-term maintainability across the entire application.

---

## 1. Objective

These guidelines are mandatory for all developers working on the Analysis HMS project. The goal is to maintain consistency, scalability, performance, readability, and long-term maintainability across the entire application.

---

## 2. Database Standards

### 2.1 Table Collation

All newly created tables must use:

```
utf8mb4_0900_ai_ci
```

Example:

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
) COLLATE=utf8mb4_0900_ai_ci;
```

### 2.2 Naming Convention

**Table Names** — use plural table names only.

Examples:

```
users
customers
voucherentries
roombookings
purchasemasters
```

**Column Names** — all column names must be lowercase.

Correct:

```
customerid
voucherdate
created_at
updated_at
```

Wrong:

```
CustomerId
VoucherDate
CreatedAt
```

### 2.3 Mandatory Columns

Every table must contain:

- `id`
- `u_name`
- `created_at`
- `updated_at`

The first column must always be `id`.

Example:

```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
u_name VARCHAR(50) NOT NULL DEFAULT ''
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

### 2.4 Boolean Fields

For all boolean values use `TINYINT(1)`.

- `0` = No / False
- `1` = Yes / True

Examples:

```sql
isactive TINYINT(1) DEFAULT 1
isdeleted TINYINT(1) DEFAULT 0
```

Avoid: `ENUM('Y','N')`, `VARCHAR`, `CHAR`.

---

## 3. Laravel Standards

### 3.1 Eloquent First Approach

For **Insert, Update and Delete** operations always use Eloquent Models.

Allowed:

```php
User::create($data);
$user->update($data);
$user->delete();
```

Avoid:

```php
DB::insert();
DB::update();
DB::delete();
```

### 3.2 Report Queries

Complex reporting and analytics queries may use `DB::table()`, `DB::raw()`, or Query Builder — only when required for performance or aggregation. Examples: Group By, Aggregate Reports, Financial Reports, Dashboard Reports.

### 3.3 Model Naming

Model names should be singular, table names plural.

```php
// Model (singular)
User
Customer
VoucherEntry
RoomBooking
```

```sql
-- Table (plural)
users
customers
voucherentries
roombookings
```

---

## 4. Controller Standards

### 4.1 Existing Project Structure

Controllers must follow the existing HMS structure.

```
app/
└── Http/
    └── Controllers/
        └── Finance/
            └── TransAction/
                └── VoucherEntry.php
```

Example: `analysishms/app/Http/Controllers/Finance/TransAction/VoucherEntry.php`

### 4.2 Constructor Usage

Every controller must contain a constructor.

```php
public function __construct()
{
    $this->propertyid = propertyid();
}
```

Use existing project helper methods — `$this->propertyid`, `ncurdate()`. Do not duplicate property retrieval logic.

### 4.3 Reusable Logic

If identical business logic is used in multiple controllers, do NOT duplicate code. Create a Helper Function, Service Class, Trait, or Common Repository instead.

Examples: `getCustomerBalance()`, `getVoucherSeries()`, `getFinancialYear()`.

Single source of truth must be maintained.

---

## 5. Blade Standards

Blade files must follow the existing HMS folder structure.

```
resources/
└── views/
    └── property/
        └── finance/
            └── transaction/
                └── voucherentry.blade.php
```

Example: `analysishms/resources/views/property/finance/transaction/voucherentry.blade.php`

---

## 6. UI / Frontend Standards

### 6.1 Bootstrap First

Always use Bootstrap components before writing custom CSS. Priority: (1) Bootstrap, (2) Existing Project CSS, (3) Custom CSS (only if absolutely required).

Avoid unnecessary CSS:

```css
/* Wrong */
.mt-custom {
    margin-top: 13px;
}
```

Use Bootstrap: `mt-3`.

### 6.2 Responsive Design

All screens must work on Desktop, Tablet, and Mobile. Bootstrap grid system must be used: `col-12`, `col-md-6`, `col-lg-4`.

### 6.3 Minimal CSS

Custom CSS should be minimized. Use Bootstrap utility classes wherever possible instead of creating custom styles.

- Use Bootstrap utility classes for layout, spacing, colors, borders, typography, sizing, and alignment.
- Avoid writing custom CSS when an equivalent Bootstrap class is available.
- Create custom CSS only when a requirement cannot be achieved using Bootstrap utilities.
- Prefer reusable Bootstrap components: Cards, Tables, Forms, Alerts, Modals, Grid System.

| Requirement | Bootstrap Classes |
|---|---|
| Border | `border`, `border-0`, `border-top`, `border-bottom`, `rounded`, `rounded-3` |
| Text Alignment | `text-start`, `text-center`, `text-end` |
| Text Color | `text-primary`, `text-secondary`, `text-success`, `text-danger`, `text-warning`, `text-info` |
| Background Color | `bg-primary`, `bg-secondary`, `bg-success`, `bg-danger`, `bg-warning`, `bg-light`, `bg-dark` |
| Font Weight | `fw-bold`, `fw-semibold`, `fw-normal`, `fw-light` |
| Font Size | `fs-1` … `fs-6` |
| Margin | `m-*`, `mt-*`, `mb-*`, `ms-*`, `me-*`, `mx-*`, `my-*` |
| Padding | `p-*`, `pt-*`, `pb-*`, `ps-*`, `pe-*`, `px-*`, `py-*` |
| Width | `w-25`, `w-50`, `w-75`, `w-100` |
| Height | `h-25`, `h-50`, `h-75`, `h-100` |
| Display | `d-none`, `d-block`, `d-inline`, `d-flex` |
| Flex Layout | `d-flex`, `flex-row`, `flex-column`, `justify-content-*`, `align-items-*` |
| Grid Columns | `row`, `col`, `col-md-6`, `col-lg-4`, `col-xl-3` |
| Gap Between Elements | `gap-1` … `gap-5` |
| Shadows / Boxes | `shadow-sm`, `shadow`, `shadow-lg`, `card` |
| Positioning | `position-relative`, `position-absolute`, `top-0`, `start-0`, `end-0`, `bottom-0` |
| Overflow | `overflow-auto`, `overflow-hidden`, `overflow-scroll` |
| Table Styling | `table`, `table-striped`, `table-hover`, `table-bordered`, `table-sm`, `table-responsive` |

**Example** — instead of custom CSS:

```css
.custom-box {
    border: 1px solid #dee2e6;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 6px;
}
```

Use Bootstrap utilities:

```html
<div class="border rounded p-3 mb-2">
    Content
</div>
```

This reduces CSS maintenance, improves UI consistency, and leverages Bootstrap's optimized utility framework.

---

## 7. Form Standards

### 7.1 AJAX Submission Mandatory

All forms should be submitted through AJAX whenever possible. Avoid full page refresh submissions.

### 7.2 Submit Button Handling

When submit is clicked:

```js
$('#submitbtn').prop('disabled', true);
```

If validation or server error occurs:

```js
$('#submitbtn').prop('disabled', false);
```

Prevent duplicate submissions.

### 7.3 Success Response Flow

After successful save: show success notification via `Swal.fire()` and/or push notification. Redirect based on action: Save & New, Save & Print, Save & Close — according to user action.

### 7.4 Error Handling

Always return standard JSON responses:

```json
{
    "success": false,
    "message": "Validation failed"
}
```

```json
{
    "success": true,
    "message": "Voucher saved successfully",
    "data": $voucherrows
}
```

---

## 8. Data Listing Standards

### 8.1 DataTables

For most listing pages use DataTables with: Pagination, Search, Sorting, Filtering, Export — enabled wherever applicable.

### 8.2 Server Side Processing

For large datasets, Server Side DataTables must be used. Avoid loading thousands of rows at once.

---

## 9. Export Standards

### 9.1 Excel Export

Use `maatwebsite/excel` (https://packagist.org/packages/maatwebsite/excel). Create export classes:

```bash
php artisan make:export UsersExport --model=User
```

Always prefer dedicated export classes.

### 9.2 PDF Export

Use `barryvdh/laravel-dompdf` (https://github.com/barryvdh/laravel-dompdf) for: Reports, Invoices, Receipts, Statements, Print Formats.

### 9.3 Print Functionality

Use `barryvdh/laravel-dompdf` for printable output generation.

### 9.4 Small Dataset Export

For small DataTables, DataTable Export or Tabulator Export may be used directly.

### 9.5 Large Dataset Export

For mass records always use Laravel Excel Export Classes to prevent memory issues.

---

## 10. Code Quality Standards

### 10.1 Avoid Duplicate Code

Never repeat identical logic in multiple places. Create a Helper, Service, Trait, or Common Function instead.

### 10.2 Keep Code Clean

Avoid: Unused Variables, Unused Functions, Dead Code, Unnecessary Comments.

### 10.3 Naming Consistency

Use meaningful names. Good: `customerBalance`, `voucherDate`, `roomBooking`. Avoid: `x`, `temp`, `abc`, `data1`.

### 10.4 Performance First

Always: select required columns only, use indexes properly, avoid N+1 queries, use eager loading, optimize joins.

### 10.5 Security

Always: validate requests, use CSRF protection, escape output, use mass-assignment protection, sanitize inputs.

### 10.6 Service Classes for Logging

Any functionality that inserts records into a log table must be implemented through a dedicated Service Class. Developers must not write log insertion logic directly inside Controllers, Middleware, Commands, Jobs, or Views.

Why this rule exists:
- Ensures logging logic remains centralized and reusable
- Prevents code duplication across multiple controllers and modules
- Simplifies future changes to log structures and business rules
- Improves maintainability, readability, and testability
- Guarantees consistent logging behavior throughout the application

---

## 11. Final Development Rules

Mandatory checklist before code submission:

- Use `utf8mb4_0900_ai_ci` collation
- Use lowercase column names
- Table names must be plural
- First column must be `id`
- Include `created_at` and `updated_at`
- Boolean values must use `TINYINT(1)`
- Use Eloquent for Insert/Update/Delete
- Reports may use Query Builder
- Follow existing HMS folder structure
- Use constructor in every controller
- Use `$this->propertyid` and `ncurdate()`
- Avoid duplicate code
- Bootstrap first, custom CSS last
- AJAX form submission preferred
- Disable submit button during processing
- Use Swal and Push Notifications
- Use DataTables for listings
- Use Laravel Excel for large exports
- Use DomPDF for PDF and print
- Write clean, optimized, production-ready code
- Avoid unnecessary comments and unused code
