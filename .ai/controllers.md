# Analysis HMS - Controllers Documentation

## Controller Overview

Total Controllers: 54+

## Main Controllers

### Authentication & User Management

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `MainController` | Super admin operations | index, companyregister, store, loadcompanylist |
| `LoginController` | User authentication | logout |
| `AutoLoginController` | Auto login | loginUser |
| `PythonAuth` | Python API auth | login |
| `UserParam` | User parameters | userpermision, menulist, userparamsubmit |

### Front Office

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `RoomController` | Room management | index, data, fetchrooms |
| `RoomStatus` | Room status | index, data, updatestatus |
| `Reservation` | Reservations | index, data, store, update |
| `BookingFollowUp` | Booking follow-ups | index, data, store, comments |
| `BookingInquiryController` | Booking inquiries | index, data, store |
| `ChargePosting` | Charge posting | index, data, store |

### Point of Sale (POS)

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `Pointofsale` | POS operations | index, data, store, bill |
| `Pos` | POS alternative | index, data, store |
| `Kot` | Kitchen orders | index, data, store, update |
| `SaleBill` | Sale bills | index, data, store, print |

### Banquet & Events

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `Banquet` | Banquet operations | index, data, store, update |
| `HappyhourController` | Happy hour | index, data, store |

### Inventory & Purchase

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `InventoryController` | Inventory ops | index, data, store, stock |
| `PurchaseController` | Purchases | index, data, store |

### Housekeeping

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `HouseKeeping` | Housekeeping | index, data, assign, inspect |
| `HkQrLoginController` | HK QR login | login |

### Finance & Accounting

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `FinanceController` | Finance ops | index, data, ledger |
| `FinancialPush` | Financial push | index, push |
| `CheckRegister` | Check register | index, data |

### Reports

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `ReportController` | Reports | index, data, export |
| `Reporting` | Reporting | index, data, fetchcheckinregdata |

### Company & Property

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `CompanyController` | Company mgmt | index, data, store, update |
| `PropertyController` | Property mgmt | loadProperty, updateExpiry |
| `Property` | Property ops | index, data |

### Tools & Utilities

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `ToolsController` | Tools | index, data, various tools |
| `DeveloperTools` | Dev tools | opendevelopertools, generate |
| `ConfigController` | Configuration | index, update |
| `CronController` | Cron jobs | autoCharge |

### Communication

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `MailController` | Email | send |
| `ChannelPush` | Channel push | index, push |
| `ChannelPublic` | Public channel | index, data |

### E-Invoice & Printing

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `EInvoiceParameter` | E-invoice | index, data, store |
| `PrintController` | Printing | index, print |
| `Printing` | Print ops | index |

### HR & Payroll

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `HrpayrollsController` | HR/Payroll | index, data, store |

### Support & Tickets

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `FeedbackMasterController` | Feedback | index, data, store |
| `DemoRequestController` | Demo requests | store |
| `MaintenanceController` | Maintenance | index |

### Data Management

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `Fetch` | Data fetching | index, various fetch methods |
| `ExcelController` | Excel export | index, export |
| `PartyMaster` | Party master | index, data, store |

### Location & Geography

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `Location` | Locations | index, data |
| `GeneralController` | General ops | index, data |

### Testing & Debugging

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `TestingControlller` | Testing | generateQr, delayedResult |
| `NightAuditlogController` | Night audit | index, data |

### Webhooks & Integrations

| Controller | Purpose | Key Methods |
|------------|---------|-------------|
| `WPParameter` | WhatsApp params | index, data, store |
| `GatePassController` | Gate pass | index, data, store |
| `GatePassInController` | Gate pass in | index, data, store |

## Controller Directories

```
app/Http/Controllers/
├── Admin/           # Admin controllers
├── Api/             # API controllers
├── Auth/            # Authentication controllers
├── Cron/            # Cron job controllers
├── Essl/            # ESSL integration
├── Finance/         # Finance controllers
├── FrontOffice/     # Front office controllers
└── SuperAdmin/      # Super admin controllers
```

## Common Controller Patterns

### Standard CRUD Pattern
```php
public function index()      // List view
public function data()       // AJAX data
public function store()      // Create
public function update()     // Update
public function destroy()    // Delete
```

### Data Table Pattern
```php
public function index()      // Return view
public function data()       // Return JSON for DataTables
```

### Export Pattern
```php
public function index()      // Return view
public function export()     // Export data
```

## Middleware Usage

### Company Middleware
- `company` - Company-level access
- Used on: Property routes, Reporting routes

### Super Admin Middleware
- `superadmin` - Super admin access
- Used on: Admin routes, System routes

### Staff Middleware
- `staff` - Staff access
- Used on: Operational routes

## Route Groups

### Web Routes
- Main routes in `web.php`
- Property routes in `property.php`
- POS routes in `pointofsale.php`
- Reporting routes in `reporting.php`
- Tools routes in `tools.php`

### API Routes
- API routes in `api.php`
- Sanctum authentication

## Last Updated
- Date: August 7, 2026
- Version: 1.0
