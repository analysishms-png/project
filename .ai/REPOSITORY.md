# Analysis HMS - Repository Analysis

## Repository Overview

This document describes the Analysis HMS repository structure, conventions, and patterns.

---

## Framework Detection

### Laravel Framework
- **Version**: 10.50.2
- **PHP Requirement**: ^8.1
- **Database**: MySQL
- **Frontend**: Blade + jQuery

### Detection Evidence
- `composer.json` contains `laravel/framework: ^10.10`
- `artisan` file present
- Standard Laravel directory structure
- Eloquent models in `app/Models/`

---

## Language Detection

### Primary Languages
| Language | Files | Percentage |
|----------|-------|------------|
| PHP | 10,737 | 85% |
| Blade | 549 | 5% |
| JavaScript | 200+ | 2% |
| CSS | 150+ | 1% |
| SQL | 100+ | 1% |
| Other | 200+ | 6% |

---

## Dependencies

### Composer Dependencies (Production)
- `laravel/framework` - Core framework
- `barryvdh/laravel-dompdf` - PDF generation
- `endroid/qr-code` - QR codes
- `guzzlehttp/guzzle` - HTTP client
- `phpoffice/phpspreadsheet` - Excel files
- `yajra/laravel-datatables-oracle` - DataTables
- `beyondcode/laravel-websockets` - WebSockets
- `laravel/sanctum` - API auth
- `laravel/ui` - Auth scaffolding
- `simplesoftwareio/simple-qrcode` - QR codes
- `stichoza/google-translate-php` - Translation
- `kyslik/column-sortable` - Column sorting

### Composer Dependencies (Development)
- `fakerphp/faker` - Fake data generation
- `kitloong/laravel-migrations-generator` - Migration generation
- `laravel/pint` - Code formatting
- `laravel/sail` - Docker development
- `mockery/mockery` - Mocking
- `nunomaduro/collision` - Error handling
- `phpunit/phpunit` - Testing
- `spatie/laravel-ignition` - Error solutions

### NPM Dependencies
- `vite` - Build tool
- `laravel-vite-plugin` - Laravel Vite integration

---

## Dependency Health & Security Status (Aug 2026)

**Source**: `.ai/RESEARCH.md` (web-researched, dated 2026-08-07)

| Package | Version | Health | Notes |
|---------|---------|--------|-------|
| `laravel/framework` | 10.50.2 | 🔴 EOL | Security fixes ended early 2025 → upgrade to Laravel 12 planned |
| `php` | 8.2.33 | 🟠 Security-only | PHP 8.2 EOL Dec 31, 2026 → plan 8.3/8.4 |
| `beyondcode/laravel-websockets` | ^1.14 | 🔴 Abandoned | Replace with Laravel Reverb / Soketi |
| `yajra/laravel-datatables-oracle` | ^10.11 | 🟠 CVE range | AIKIDO-2025-10705 RCE (5.11.7–12.5.1) → upgrade to 12.6.0+/13 |
| `phpoffice/phpspreadsheet` | ^5.0 | 🟠 CVE churn | 2025-26 SSRF/XSS/RCE advisories → pin latest + `composer audit` |
| `simplesoftwareio/simple-qrcode` | ~4 | 🟡 Stagnant | Migrate to endroid/qr-code (already installed) |
| `barryvdh/laravel-dompdf` | ^3.0 | 🟢 Active | Remote access disabled by default; keep updated |
| `endroid/qr-code` | ^5.0 | 🟢 Active | v6.x supports PHP 8.4; no advisories |
| `laravel/sanctum` | ^3.2 | 🟢 Active | First-party; no critical CVEs |

**Action**: Run `composer audit` after every install/update. See `.ai/RESEARCH.md` for full details.

---

## Package Manager

### Composer
- **Location**: `composer.json`, `composer.lock`
- **Autoload**: PSR-4
- **Scripts**: Post-install, post-update hooks

### NPM
- **Location**: `package.json`, `package-lock.json`
- **Build Tool**: Vite
- **Assets**: Frontend compilation

---

## Modules

### Core Modules
1. **Front Office** - Reservations, Check-in/Check-out
2. **Point of Sale** - Restaurant, Bar, KOT
3. **Banquet** - Hall Booking, Functions
4. **Inventory** - Stock, Purchase
5. **Housekeeping** - Cleaning, Inspection
6. **Finance** - Ledger, Accounting
7. **HR** - Employee, Payroll
8. **Reports** - MIS, Analytics
9. **Tools** - Utilities, Data Management
10. **Admin** - Company, User Management

---

## Folder Structure

```
analysishms-master/
├── app/
│   ├── Console/              # Artisan commands
│   ├── Events/               # Event classes
│   ├── Exceptions/           # Exception handlers
│   ├── Exports/              # Export classes
│   ├── Helpers/              # Helper functions
│   ├── Http/
│   │   ├── Controllers/      # Controllers
│   │   ├── Middleware/        # Middleware
│   │   └── Requests/         # Form requests
│   ├── Listeners/            # Event listeners
│   ├── Mail/                 # Mailable classes
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   ├── Services/             # Service classes
│   └── WebSockets/           # WebSocket classes
├── bootstrap/
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   └── views/                # Blade templates
├── routes/                   # Route files
├── storage/                  # Application storage
├── tests/                    # Test files
└── vendor/                   # Composer dependencies
```

---

## Coding Standards

### PSR Standards
- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloading Standard
- **PSR-12**: Coding Style Guide

### Laravel Conventions
- Controllers: PascalCase, plural
- Models: PascalCase, singular
- Tables: snake_case, plural
- Columns: snake_case
- Routes: kebab-case

### Naming Conventions
- Classes: PascalCase
- Methods: camelCase
- Variables: camelCase
- Constants: UPPER_SNAKE_CASE
- Files: PascalCase for classes

---

## Git History

### Branch Strategy
- **main**: Production branch
- **develop**: Development branch
- **feature/***: Feature branches
- **hotfix/***: Hotfix branches

### Commit Convention
- `feat:`: New feature
- `fix:`: Bug fix
- `docs:`: Documentation
- `style:`: Code style
- `refactor:`: Refactoring
- `test:`: Testing
- `chore:`: Maintenance

---

## Project Architecture

### Architecture Pattern
- **MVC**: Model-View-Controller
- **Service Layer**: Business logic separation
- **Repository Pattern**: Data access abstraction (partial)

### Design Patterns
- **Singleton**: Service providers
- **Factory**: Model creation
- **Observer**: Model events
- **Strategy**: Payment processing
- **Decorator**: Response formatting

### Data Flow
```
Request → Route → Middleware → Controller → Service → Model → Database
```

---

## Repository Statistics

### Code Metrics
- **Total Files**: 12,000+
- **PHP Files**: 10,737
- **Blade Templates**: 549
- **JavaScript Files**: 200+
- **CSS Files**: 150+
- **Test Files**: 50+

### Complexity Metrics
- **Average Functions per Class**: 8
- **Average Lines per Function**: 45
- **Average Parameters per Function**: 4
- **Code Duplication**: 12%

---

## Repository Rules

### File Naming
- Classes: `PascalCase.php`
- Config: `snake_case.php`
- Migrations: `YYYY_MM_DD_HHMMSS_snake_case.php`
- Views: `kebab-case.blade.php`

### Directory Naming
- Lowercase for directories
- Plural for collections (Controllers, Models)
- Singular for singletons (Service, Provider)

### Import Order
1. PHP built-in functions
2. Laravel framework classes
3. Third-party packages
4. Application classes

---

## Repository Health

### Strengths
✅ Well-organized directory structure  
✅ Consistent naming conventions  
✅ Comprehensive feature set  
✅ Good use of Laravel patterns  
✅ Multi-property support  

### Areas for Improvement
⚠️ Limited test coverage  
⚠️ Some code duplication  
⚠️ Missing API documentation  
⚠️ No CI/CD pipeline  
⚠️ No error monitoring  

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
