# Analysis HMS - Graph Memory

## Graph Overview

This document defines the project knowledge graph for Analysis HMS, connecting routes, controllers, models, services, views, database, APIs, business rules, modules, and dependencies.

---

## Component Graph

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                    ANALYSIS HMS                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │   Routes    │───▶│ Controllers │───▶│   Models    │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
│         │                  │                  │              │
│         ▼                  ▼                  ▼              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │  Middleware │    │  Services   │    │  Database   │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
│         │                  │                  │              │
│         ▼                  ▼                  ▼              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │   Views     │    │   Helpers   │    │   APIs      │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Route-Controller Relationships

### Web Routes (`web.php`)

```
Route                          Controller                    Method
─────────────────────────────────────────────────────────────────
/                              HomeController                index
/loginpy                       PythonAuth                    login
/auto-login                    AutoLoginController           loginUser
/logout                        LoginController               logout
/superadmin                    MainController                index
/company                       PropertyController            loadProperty
/tools                         HomeController                tools
/tools/dashboard               ToolsController               toolsdashboard
/tools/tablemanagement         ToolsController               tablemanagement
/dailyfunctionsheet            ReportController              dailyFunctionSheet
/my-tickets                    CompanyController             myTickets
/admin/activity-logs           ActivityLogController         index
```

### Tools Routes (`tools.php`)

```
Route                          Controller                    Method
─────────────────────────────────────────────────────────────────
/tools/tablemanagement         ToolsController               tablemanagement
/tools/fetch_tables            ToolsController               fetchtables
/tools/update_table_cell       ToolsController               updatetablecell
/tools/roomchargepost          ToolsController               roomchargepost
/tools/advchargetool           ToolsController               openadvancecharge
/tools/changebilldate          ToolsController               changebilldate
/tools/posrecycle              ToolsController               posrecycle
/tools/submit-ticket           ToolsController               submitTicket
/tools/tickets                 ToolsController               viewTickets
/meta                          MetaController                index
```

### User Parameter Routes (`userparam.php`)

```
Route                          Controller                    Method
─────────────────────────────────────────────────────────────────
/submipermusermodule           UserParam                     submipermusermodule
/validatecheck                 UserParam                     validatecheck
/getmainmenu                   UserParam                     getmainmenu
/fetchsubmenu                  UserParam                     fetchsubmenu
/userpermision                 UserParam                     userpermision
/menulist                      UserParam                     menulist
/userparamsubmit               UserParam                     userparamsubmit
```

---

## Controller-Model Relationships

### MainController

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
MainController         index                        Companyreg
                       companyregister              Companyreg
                       store                        Companyreg
                       loadcompanylist              Companyreg
                       updatecompany                Companyreg
                       disableusermaster            User
                       enableusermaster             User
```

### RoomController

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
RoomController         index                        RoomMast, RoomCat
                       data                         RoomMast, RoomOcc
                       fetchrooms                   RoomMast
                       updatestatus                 RoomOcc
```

### Reservation

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
Reservation            index                        Bookings, GuestProf
                       data                         Bookings, BookingDetail
                       store                        Bookings, BookingDetail
                       update                       Bookings, BookingDetail
```

### Pointofsale

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
Pointofsale            index                        Sale1, Sale2, Kot
                       data                         Sale1, Sale2
                       store                        Sale1, Sale2, Kot
                       bill                         Sale1, Sale2
```

### Banquet

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
Banquet                index                        HallBook, HallSale1
                       data                         HallBook, HallSale1
                       store                        HallBook, HallSale1
                       update                       HallBook, HallSale1
```

### InventoryController

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
InventoryController    index                        Stock, Items
                       data                         Stock, Items
                       store                        Stock, Stocklog
                       update                       Stock, Stocklog
```

### HouseKeeping

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
HouseKeeping           index                        Hkroomassign, RoomMast
                       data                         Hkroomassign
                       assign                       Hkroomassign
                       inspect                      Hkinspectionhdr
```

### FinanceController

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
FinanceController      index                        Ledger, Suntran
                       data                         Ledger, Suntran
                       ledger                       Ledger, LedgerLog
```

### ReportController

```
Controller             Methods                      Models Used
─────────────────────────────────────────────────────────────────
ReportController       dailyFunctionSheet           Bookings, HallBook
                       bookingEnquiryDetail         BookingInquiry
                       outStandingreport            Ledger, Paycharge
                       companyWiseSaleReport        Sale1, Sale2
                       itemWiseSaleReport           Sale1, Sale2, Items
```

---

## Model-Database Relationships

### Core Hotel Models

```
Model                  Table                        Relationships
─────────────────────────────────────────────────────────────────
RoomMast               room_mast                    hasOne RoomOcc
                                              hasMany RoomClean
                                              belongsTo RoomCat
RoomCat                roomcat                      hasMany RoomMast
RoomOcc                roomocc                      belongsTo RoomMast
RoomClean              roomclean                    belongsTo RoomMast
```

### Booking Models

```
Model                  Table                        Relationships
─────────────────────────────────────────────────────────────────
Bookings               booking                      hasMany BookingDetail
                                              hasMany GrpBookinDetail
                                              belongsTo GuestProf
BookingDetail          bookingdetail                belongsTo Bookings
                                              belongsTo RoomMast
GrpBookinDetail        grpbookingdetails            belongsTo Bookings
GuestProf              guestprof                    hasMany Bookings
                                              hasMany Guestfolio
```

### Financial Models

```
Model                  Table                        Relationships
─────────────────────────────────────────────────────────────────
Ledger                 ledger                       hasMany Suntran
                                              hasMany Paycharge
                                              belongsTo SubGroup
Suntran                suntran                      belongsTo Ledger
Paycharge              paycharge                    belongsTo Ledger
SubGroup               subgroup                     hasMany Ledger
```

### POS Models

```
Model                  Table                        Relationships
─────────────────────────────────────────────────────────────────
Sale1                  sale1                        hasMany Sale2
                                              hasMany Kot
Sale2                  sale2                        belongsTo Sale1
                                              belongsTo Items
Kot                    kot                          belongsTo Sale1
Items                  items                        hasMany Sale2
                                              hasMany Kot
```

### Banquet Models

```
Model                  Table                        Relationships
─────────────────────────────────────────────────────────────────
HallBook               hallbook                     hasMany HallSale1
                                              belongsTo VenueMast
HallSale1              hallsale1                    belongsTo HallBook
                                              hasMany HallSale2
HallSale2              hallsale2                    belongsTo HallSale1
VenueMast              venuemast                    hasMany HallBook
```

---

## Service Relationships

### AccountPosting

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
AccountPosting         Ledger, Suntran, Paycharge    Financial posting
                       SubGroup, LedgerLog           Account management
```

### BanquetLedgerPosting

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
BanquetLedgerPosting   HallBook, HallSale1           Banquet posting
                       Ledger, Suntran               Financial integration
```

### LedgerLogService

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
LedgerLogService       LedgerLog, Ledger             Ledger logging
                       Suntranlog                    Transaction logging
```

### RoomKeyService

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
RoomKeyService         Roomkey, RoomMast             Room key management
                       RoomOcc                       Occupancy tracking
```

### DailyReportSnapshotService

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
DailyReportSnapshotService  DailyReportSnapshot      Daily reports
                       Bookings, Sale1               Data aggregation
```

### RoomInclusivePosting

```
Service                Dependencies                 Purpose
─────────────────────────────────────────────────────────────────
RoomInclusivePosting   RoomInclusive, RoomInclusiveLog   Inclusive charges
                       Paycharge, Ledger                 Financial posting
```

---

## Helper Relationships

### Helpers.php

```
Helper                 Functions                    Dependencies
─────────────────────────────────────────────────────────────────
Helpers                allcompanies                 Companyreg
                       percompdata                  Companyreg
                       companydata                  Companyreg
                       userdata                     User, UserPermission
                       calculateTax                 TaxStructure
                       maxvno                       VoucherPrefix
                       allcities                    Cities
                       allstates                    States
```

### ResHelper.php

```
Helper                 Functions                    Dependencies
─────────────────────────────────────────────────────────────────
ResHelper              UpdateCancel                 Bookings, GrpBookinDetail
                       updateammendstay             Bookings, Paycharge
                       updateadvance                Bookings, Paycharge
```

### PrintHelper.php

```
Helper                 Functions                    Dependencies
─────────────────────────────────────────────────────────────────
PrintHelper            buildPrintDataFOM            Bookings, GuestProf
                                               RoomOcc, PlanMast
```

### DateHelper.php

```
Helper                 Functions                    Dependencies
─────────────────────────────────────────────────────────────────
DateHelper             getNcurDate                  None
                       calculateDateRanges          None
                       Uniqueyears                  Bookings
```

### Gstr1.php

```
Helper                 Functions                    Dependencies
─────────────────────────────────────────────────────────────────
Gstr1                  banquetquery1                HallBook, HallSale1
                       banquetquery2                HallBook, HallSale1
                       getbanquetdata               HallBook, HallSale1
                       advancequerybanquetad1       HallBook, Paycharge
```

---

## View Relationships

### Layout Views

```
View                           Components                    Usage
─────────────────────────────────────────────────────────────────
layouts/app.blade.php          header, sidebar, footer       Main layout
layouts/auth.blade.php         login form                    Auth pages
layouts/guest.blade.php        public header, footer         Public pages
```

### Module Views

```
Module                 Views                         Controllers
─────────────────────────────────────────────────────────────────
Front Office           roomstatus, reservation       RoomController, Reservation
                       checkin, checkout              Reservation
POS                    pos, kot, salebill             Pointofsale, Kot, SaleBill
Banquet                banquet, hallsale              Banquet
Inventory              inventory, stock              InventoryController
Housekeeping           housekeeping, inspection      HouseKeeping
Finance                finance, ledger               FinanceController
Reports                reports, dailyreport          ReportController
Tools                  tools, tablemanagement        ToolsController
Admin                  admin, superadmin             MainController
```

---

## Business Rule Relationships

### Reservation Flow

```
Business Rule                    Implementation                Models
─────────────────────────────────────────────────────────────────
Create Reservation               Reservation::store           Bookings
                                 GuestProf::create            GuestProf
Update Reservation               Reservation::update          Bookings
                                 GrpBookinDetail::update      GrpBookinDetail
Cancel Reservation               ResHelper::UpdateCancel      Bookings
                                 BookingDetail::delete         BookingDetail
```

### Check-In Flow

```
Business Rule                    Implementation                Models
─────────────────────────────────────────────────────────────────
Check-In Guest                   Reservation::checkin         Bookings
                                 RoomOcc::update              RoomOcc
                                 Guestfolio::create           Guestfolio
Check-Out Guest                  Reservation::checkout        Bookings
                                 RoomOcc::update              RoomOcc
                                 Paycharge::create            Paycharge
```

### POS Flow

```
Business Rule                    Implementation                Models
─────────────────────────────────────────────────────────────────
Create KOT                        Kot::store                   Kot
                                 Kot::update                  Kot
Add Items                         Sale2::store                 Sale2
Generate Bill                     SaleBill::store              Sale1
                                 Paycharge::create            Paycharge
```

### Night Audit Flow

```
Business Rule                    Implementation                Models
─────────────────────────────────────────────────────────────────
Room Charge Posting               CronController::autoCharge   Paycharge
                                 RoomInclusivePosting          RoomInclusive
Revenue Posting                   AccountPosting::post         Suntran
                                 Ledger::update               Ledger
Daily Settlement                   NightAuditLog::store        NightAuditLog
```

---

## Dependency Graph

### Package Dependencies

```
Package                          Dependencies                  Purpose
─────────────────────────────────────────────────────────────────
laravel/framework                 php ^8.1                     Core framework
barryvdh/laravel-dompdf           php ^8.1                     PDF generation
endroid/qr-code                   php ^8.1                     QR codes
guzzlehttp/guzzle                 php ^7.2                     HTTP client
phpoffice/phpspreadsheet          php ^8.1                     Excel files
yajra/laravel-datatables-oracle   php ^8.0                     DataTables
beyondcode/laravel-websockets     php ^7.2                     WebSockets
laravel/sanctum                   php ^8.0                     API auth
```

### Internal Dependencies

```
Component                        Dependencies                  Consumers
─────────────────────────────────────────────────────────────────
Controllers                      Models, Services, Helpers     Routes
Services                         Models, Repositories          Controllers
Helpers                          Models, Facades               Controllers, Services
Models                           Database, Eloquent            Services, Controllers
Views                            Controllers, Models           Routes
```

---

## Module Interaction Graph

### Front Office Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
RoomController          ───────▶ RoomMast, RoomOcc, RoomCat
Reservation             ───────▶ Bookings, BookingDetail, GuestProf
RoomStatus              ───────▶ RoomOcc, RoomMast
CheckRegister           ───────▶ Ledger, Paycharge
```

### POS Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
Pointofsale             ───────▶ Sale1, Sale2, Kot, Items
Pos                     ───────▶ Sale1, Sale2, Kot
Kot                     ───────▶ Kot, Sale2, Items
SaleBill                ───────▶ Sale1, Sale2, Paycharge
```

### Banquet Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
Banquet                 ───────▶ HallBook, HallSale1, HallSale2
VenueMast               ───────▶ VenueMast, VenueOcc
```

### Inventory Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
InventoryController     ───────▶ Stock, Stocklog, Items
PurchaseController      ───────▶ Purch1, Purch2, Indent
```

### Housekeeping Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
HouseKeeping            ───────▶ Hkroomassign, RoomMast
HkInspection            ───────▶ Hkinspectionhdr, Hkinspectionftr
```

### Finance Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
FinanceController       ───────▶ Ledger, Suntran, Paycharge
AccountPosting          ───────▶ Ledger, Suntran, Paycharge
LedgerLogService        ───────▶ LedgerLog, Suntranlog
```

### Reports Module

```
Component                        Interactions
─────────────────────────────────────────────────────────────────
ReportController        ───────▶ Bookings, Sale1, Sale2, Ledger
Reporting               ───────▶ Bookings, GuestProf, RoomOcc
```

---

## Data Flow Graph

### Reservation Flow

```
User Input               Processing                  Database
─────────────────────────────────────────────────────────────────
Guest Details    ───────▶ GuestProf::create   ───────▶ guestprof
Booking Details  ───────▶ Bookings::create    ───────▶ booking
Room Assignment  ───────▶ BookingDetail::create ─────▶ bookingdetail
Plan Details     ───────▶ BookinPlanDetail::create ──▶ bookingplandetails
```

### Check-In Flow

```
User Input               Processing                  Database
─────────────────────────────────────────────────────────────────
Guest Arrival    ───────▶ Bookings::update    ───────▶ booking
Room Assignment  ───────▶ RoomOcc::update     ───────▶ roomocc
Folio Creation   ───────▶ Guestfolio::create  ───────▶ guestfolio
```

### POS Flow

```
User Input               Processing                  Database
─────────────────────────────────────────────────────────────────
Order Creation   ───────▶ Kot::create         ───────▶ kot
Item Addition    ───────▶ Sale2::create       ───────▶ sale2
Bill Generation  ───────▶ Sale1::create       ───────▶ sale1
Payment          ───────▶ Paycharge::create   ───────▶ paycharge
```

### Financial Flow

```
User Input               Processing                  Database
─────────────────────────────────────────────────────────────────
Charge Posting   ───────▶ Paycharge::create   ───────▶ paycharge
Revenue Posting  ───────▶ Suntran::create     ───────▶ suntran
Ledger Update    ───────▶ Ledger::update      ───────▶ ledger
```

---

## Graph Maintenance

### Update Triggers
1. New route added
2. New controller created
3. New model created
4. New service created
5. New helper created
6. New view created
7. New business rule added
8. New module added

### Validation
1. Check for orphaned components
2. Verify all relationships
3. Test data flows
4. Validate business rules

### Documentation
1. Update graph on changes
2. Document new relationships
3. Archive deprecated components
4. Maintain version history

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
