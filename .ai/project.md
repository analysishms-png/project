# Analysis HMS - Project Overview

## Project Name
Analysis HMS (Hotel Management System)

## Description
Enterprise-grade Hotel Management System built with Laravel framework for managing hotel operations including reservations, front office, point of sale, banquet, inventory, housekeeping, finance, and HR.

## Technology Stack

| Component | Version |
|-----------|---------|
| **PHP** | 8.2.33 |
| **Laravel** | 10.50.2 |
| **Database** | MySQL |
| **Composer** | 2.10.2 |
| **Server** | XAMPP (Apache + MySQL) |

## Project Structure

```
analysishms-master/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Events/           # Event classes
│   ├── Exceptions/       # Exception handlers
│   ├── Exports/          # Export classes
│   ├── Helpers/          # Helper functions
│   ├── Http/
│   │   ├── Controllers/  # 54+ controllers
│   │   ├── Middleware/    # 16+ middleware
│   │   └── Requests/     # Form requests
│   ├── Listeners/        # Event listeners
│   ├── Mail/             # Mailable classes
│   ├── Models/           # 161+ Eloquent models
│   ├── Providers/        # Service providers
│   ├── Services/         # 6+ service classes
│   └── WebSockets/       # WebSocket classes
├── bootstrap/
├── config/               # 18 configuration files
├── database/
│   ├── migrations/       # 100+ migrations
│   └── seeders/
├── public/
├── resources/
│   └── views/            # 549 Blade templates
├── routes/               # 12 route files
├── storage/
├── tests/
└── vendor/
```

## Key Statistics

- **Total PHP Files**: 10,737
- **Blade Templates**: 549
- **Eloquent Models**: 161+
- **Controllers**: 54+
- **Middleware**: 16+
- **Services**: 6+
- **Helpers**: 8+
- **Route Files**: 12
- **Database Migrations**: 100+

## Core Modules

1. **Front Office** - Reservations, Check-in/Check-out, Room Management
2. **Point of Sale** - Restaurant, Bar, KOT, Billing
3. **Banquet** - Hall Booking, Function Management
4. **Inventory** - Stock, Purchase, Indent, Godown
5. **Housekeeping** - Room Cleaning, Inspection, Assignment
6. **Finance** - Ledger, Accounting, GST, Night Audit
7. **HR** - Employee, Attendance, Payroll, Salary
8. **Reports** - MIS, Sales, Revenue, Statistics
9. **Tools** - Utilities, Data Management, Support Tickets
10. **Admin** - Company Management, User Permissions, Settings

## Last Updated
- Date: August 7, 2026
- PHP Version: 8.2.33 (Upgraded from 8.0.30)
- Composer Dependencies: Installed successfully
