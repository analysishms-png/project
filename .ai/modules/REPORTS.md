# Reports Module

## Overview

The Reports module handles business reporting, analytics, MIS reports, and data export.

---

## Components

### Controllers
- `ReportController` - Main report operations
- `Reporting` - Reporting utilities
- `ExcelController` - Excel export

### Models
- `DailyReportSnapshot` - Daily report snapshots
- Various models used for report data

### Services
- `DailyReportSnapshotService` - Daily report generation

---

## Workflows

### Daily Report Flow
1. Collect daily data
2. Calculate occupancy
3. Calculate revenue
4. Generate report
5. Save snapshot

### Monthly Report Flow
1. Aggregate daily data
2. Calculate trends
3. Generate charts
4. Create summary
5. Export report

### Export Flow
1. Select report type
2. Apply filters
3. Generate data
4. Create file (Excel/PDF)
5. Download file

---

## Report Types

### Front Office Reports
| Report | Description |
|--------|-------------|
| Room Occupancy | Room occupancy statistics |
| Guest Statistics | Guest demographics |
| Reservation Report | Reservation details |
| Check-In/Check-Out | Daily arrivals/departures |

### Financial Reports
| Report | Description |
|--------|-------------|
| Daily Revenue | Daily revenue summary |
| Revenue by Category | Revenue breakdown |
| Outstanding Report | Pending payments |
| Tax Report | GST/Tax collection |

### POS Reports
| Report | Description |
|--------|-------------|
| Sales Report | Sales by outlet |
| Item Sales | Item-wise sales |
| KOT Report | Kitchen order tickets |
| Payment Report | Payment summary |

### Banquet Reports
| Report | Description |
|--------|-------------|
| Booking Report | Hall bookings |
| Function Report | Functions scheduled |
| Banquet Revenue | Banquet revenue |

### Inventory Reports
| Report | Description |
|--------|-------------|
| Stock Report | Current stock levels |
| Purchase Report | Purchase history |
| Stock Movement | Stock in/out |
| Consumption Report | Item consumption |

### HR Reports
| Report | Description |
|--------|-------------|
| Attendance Report | Employee attendance |
| Salary Report | Salary disbursement |
| Payroll Report | Monthly payroll |
| Leave Report | Leave summary |

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `dailyreportsnapshots` | Daily snapshots | id, date, data |
| Various | Report data | Multiple tables |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/reports` | ReportController@index | reports.index |
| GET | `/reports/daily` | ReportController@daily | reports.daily |
| POST | `/reports/daily/data` | ReportController@dailyData | reports.dailyData |
| GET | `/reports/revenue` | ReportController@revenue | reports.revenue |
| GET | `/reports/occupancy` | ReportController@occupancy | reports.occupancy |
| GET | `/reports/export/{type}` | ExcelController@export | reports.export |

---

## Key Features

1. **Daily Reports** - Generate daily business reports
2. **Revenue Reports** - Track revenue by category
3. **Occupancy Reports** - Room occupancy analytics
4. **Guest Reports** - Guest statistics and demographics
5. **Financial Reports** - Financial summaries and analysis
6. **Export功能** - Export to Excel/PDF
7. **Custom Reports** - Create custom report filters
8. **Scheduled Reports** - Automate report generation

---

## Report Formats

### Excel Export
- Uses PhpSpreadsheet
- Multiple worksheets
- Charts and graphs
- Custom formatting

### PDF Export
- Uses DomPDF
- Professional layout
- Company branding
- Print-ready format

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
