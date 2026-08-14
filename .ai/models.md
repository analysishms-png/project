# Analysis HMS - Models Documentation

## Model Overview

Total Models: 161+

## Core Hotel Models

### Room Management

| Model | Table | Purpose |
|-------|-------|---------|
| `RoomMast` | room_mast | Room master data |
| `RoomCat` | roomcat | Room categories |
| `RoomOcc` | roomocc | Room occupancy |
| `RoomClean` | roomclean | Cleaning status |
| `RoomBlockout` | roomblockout | Room blockouts |
| `Roomkey` | roomkey | Room key tracking |
| `RoomInclusive` | roominclusive | Room inclusive charges |
| `RoomInclusiveLog` | roominclusivelog | Inclusive log |
| `Rooms` | rooms | Room definitions |

### Housekeeping

| Model | Table | Purpose |
|-------|-------|---------|
| `Hkroomassign` | hkroomassign | Room assignment |
| `HkFloor` | hkfloors | Floor definitions |
| `HkSupervisor` | hksupervisor | Supervisors |
| `HkChecklistMast` | hkchecklistmast | Checklist items |
| `HkAmentiesMaster` | hkamentiesmast | Amenities |
| `HousekeeperMast` | housekeeparmast | Housekeepers |

### Booking & Reservations

| Model | Table | Purpose |
|-------|-------|---------|
| `Bookings` | booking | Main bookings |
| `BookingDetail` | bookingdetail | Booking details |
| `GrpBookinDetail` | grpbookingdetails | Group bookings |
| `BookinPlanDetail` | bookingplandetails | Plan details |
| `BookingSource` | bookingsource | Booking sources |
| `BookingInquiry` | bookinginquiry | Inquiries |
| `BookingFollowUp` | bookingfollowup | Follow-ups |
| `Reservations` | reservations | Reservations |

### Guest Management

| Model | Table | Purpose |
|-------|-------|---------|
| `GuestProf` | guestprof | Guest profiles |
| `Guestfolio` | guestfolio | Guest folios |
| `GuestFolioProfDetail` | guestfolioprofdetail | Folio details |
| `GuestReward` | guestreward | Rewards |
| `GuestStats` | gueststats | Statistics |

## Financial Models

### Ledger & Accounting

| Model | Table | Purpose |
|-------|-------|---------|
| `Ledger` | ledger | Main ledger |
| `LedgerLog` | ledgerlog | Ledger log |
| `LedgerTds` | ledgertds | TDS ledger |
| `Suntran` | suntran | Transactions |
| `SuntranH` | suntranh | Transaction header |
| `Suntranlog` | suntranlog | Transaction log |
| `SuntranEst` | suntranest | Estimate transactions |
| `SuntranhEst` | suntranhEst | Estimate header |
| `SubGroup` | subgroup | Account groups |
| `ACGroup` | acgroup | Account groups |
| `Revmast` | revmast | Revenue heads |

### Payments & Charges

| Model | Table | Purpose |
|-------|-------|---------|
| `Paycharge` | paycharge | Payments/charges |
| `PaychargeH` | paychargeh | Payment header |
| `PaychargeLog` | paychargelog | Payment log |
| `FomBillDetail` | fombilldetail | FOM bill details |

### Tax & GST

| Model | Table | Purpose |
|-------|-------|---------|
| `TaxStructure` | taxstru | Tax structure |
| `TdsCategory` | tdscategory | TDS categories |

## POS Models

| Model | Table | Purpose |
|-------|-------|---------|
| `Sale1` | sale1 | Sale header |
| `Sale2` | sale2 | Sale items |
| `Sale1log` | sale1log | Sale header log |
| `Sale2log` | sale2log | Sale items log |
| `Kot` | kot | Kitchen orders |
| `KotLog` | kotlog | KOT log |
| `Items` | items | Item definitions |
| `ItemMast` | itemmast | Item master |
| `ItemRate` | itemrate | Item rates |
| `ItemGrp` | itemgrp | Item groups |
| `ItemCatMast` | itemcatmast | Item categories |

## Banquet Models

| Model | Table | Purpose |
|-------|-------|---------|
| `HallBook` | hallbook | Hall bookings |
| `HallSale1` | hallsale1 | Hall sale header |
| `HallSale2` | hallsale2 | Hall sale items |
| `HallSale1Est` | hallsale1est | Estimate header |
| `HallSale2Est` | hallsale2est | Estimate items |
| `HallStock` | hallstock | Hall stock |
| `HallStockEst` | hallstockest | Estimate stock |
| `VenueMast` | venuemast | Venue master |
| `VenueOcc` | venueocc | Venue occupancy |
| `FunctionType` | functiontype | Function types |

## Inventory Models

| Model | Table | Purpose |
|-------|-------|---------|
| `Stock` | stock | Current stock |
| `Stocklog` | stocklog | Stock movements |
| `Purch1` | purch1 | Purchase header |
| `Purch2` | purch2 | Purchase items |
| `Indent` | indent | Indent header |
| `Indent1` | indent1 | Indent items |
| `Gin` | gin | Goods received |
| `GodownMast` | godownmast | Godown master |
| `UnitMast` | unitmast | Unit master |

## HR Models

| Model | Table | Purpose |
|-------|-------|---------|
| `Employee` | employee | Employee master |
| `EmpCategory` | empcategory | Employee categories |
| `Attendance` | attendance | Attendance |
| `Salary` | salary | Salary records |
| `Hrpayrolls` | hrpayrolls | Payroll data |
| `Overtime` | overtime | Overtime |
| `Loan` | loan | Loans |
| `Depart` | depart | Departments |
| `Depart1` | depart1 | Department details |

## Channel Models

| Model | Table | Purpose |
|-------|-------|---------|
| `ChannelDerived` | channelderived | Derived rates |
| `ChannelPushes` | channelpushes | Channel pushes |
| `ChannelEnviro` | channelenviro | Channel config |
| `ChannelRate` | channelrate | Channel rates |

## System Models

| Model | Table | Purpose |
|-------|-------|---------|
| `User` | users | User accounts |
| `UserPermission` | userpermission | Permissions |
| `UserModule` | usermodule | Modules |
| `TblUserModule` | tbl_usermodule | Module table |
| `UserUpdate` | userupdate | User updates |
| `UpdateLog` | updatelog | Update logs |

## Company & Property Models

| Model | Table | Purpose |
|-------|-------|---------|
| `Companyreg` | companyreg | Company registration |
| `CompanyLog` | companylog | Company log |
| `CompanyDiscount` | companydiscount | Discounts |
| `CompServiceFacilities` | compservicefacilities | Service facilities |

## Configuration Models

| Model | Table | Purpose |
|-------|-------|---------|
| `EnviroFom` | enviro_fom | FOM settings |
| `EnviroFinance` | enviro_finance | Finance settings |
| `EnviroInventory` | enviro_inventory | Inventory settings |
| `EnviroBanquet` | enviro_banquet | Banquet settings |
| `EnviroPos` | enviro_pos | POS settings |
| `EnviroGeneral` | enviro_general | General settings |
| `EnviroPayroll` | enviro_payroll | Payroll settings |
| `EnviroWhatsapp` | enviro_whatsapp | WhatsApp settings |
| `EnviroEinvoice` | enviro_einvoice | E-invoice settings |

## Support Models

| Model | Table | Purpose |
|-------|-------|---------|
| `SupportTicket` | support_tickets | Tickets |
| `SupportTicketMessage` | support_ticket_messages | Messages |
| `SupportTicketTransfer` | support_ticket_transfers | Transfers |

## Additional Models

| Model | Table | Purpose |
|-------|-------|---------|
| `WhatsappLog` | whatsapp_logs | WhatsApp logs |
| `EInvoiceBill` | einvoicebill | E-invoice data |
| `EInvoicePushLog` | einvoicepushlog | E-invoice logs |
| `Billprintthermal` | billprintthermal | Thermal print |
| `Billprintthermallog` | billprintthermallog | Thermal print log |
| `PrintingSetup` | printersetup | Printer config |
| `PrintDelay` | printdelay | Print delay |
| `MetaTag` | meta_tags | Meta tags |
| `Page` | pages | Dynamic pages |
| `Holiday` | holidays | Holidays |
| `RateList` | ratelist | Rate list |
| `PlanMast` | plan_mast | Plan master |
| `PlanDetail` | plandetails | Plan details |
| `Plan1` | plan1 | Plan type 1 |
| `Contact` | contacts | Contact form |
| `DemoRequest` | demorequests | Demo requests |
| `FeedbackMaster` | feedbackmaster | Feedback |
| `DailyReportSnapshot` | dailyreportsnapshots | Daily reports |
| `ActivityLog` | activitylog | Activity logs |
| `ErrorLog` | errorlog | Error logs |
| `Log` | logs | System logs |
| `NightAuditLog` | nightauditlog | Night audit |
| `OrderRequest` | orderrequests | Order requests |
| `MuzztechSession` | muzztechsessions | Muzztech sessions |
| `BussSource` | busssource | Business sources |
| `TravelAgent` | travelagents | Travel agents |
| `SundryMast` | sundrymast | Sundry master |
| `Sundrytype` | sundrytype | Sundry types |
| `SundryTypeFix` | sundrytypefix | Sundry type fixes |
| `SessionMast` | session_mast | Session master |
| `ServerMast` | server_mast | Server master |
| `VoucherType` | vouchertype | Voucher types |
| `VoucherPrefix` | voucherprefix | Voucher prefixes |
| `NctypeMast` | nctype_mast | NC type master |
| `MenuHelp` | menuhelp | Menu help |
| `MemberCategory` | member_categories | Member categories |
| `MemberFamily` | memberfamily | Member family |
| `MemberFacilityMast` | memfacilitymast | Member facilities |
| `RewardParameter` | rewardparameter | Reward parameters |
| `Happyhour` | happyhour | Happy hour |
| `PurchaseOrder` | purchase_orders | Purchase orders |
| `PurchaseOrderItem` | purchase_order_items | PO items |
| `Quotation` | quotations | Quotations |
| `QuotationItem` | quotation_items | Quotation items |
| `GatePassOut` | gate_pass_out | Gate pass out |
| `GatePassIn` | gate_passin | Gate pass in |
| `Sagar` | sagar | Sagar |
| `Focc` | focc | FOC |
| `ChequeDesign` | chequedesign | Cheque design |
| `Demo1` | demo1 | Demo table |

## Model Relationships

### Common Relationships

```php
// Booking relationships
Bookings hasMany BookingDetail
Bookings belongsTo GuestProf
BookingDetail belongsTo RoomMast

// Room relationships
RoomMast belongsTo RoomCat
RoomOcc belongsTo RoomMast
RoomClean belongsTo RoomMast

// Financial relationships
Ledger belongsTo SubGroup
Suntran belongsTo Ledger
Paycharge belongsTo Ledger

// POS relationships
Sale1 hasMany Sale2
Sale2 belongsTo Items
Kot belongsTo Sale1

// Banquet relationships
HallBook hasMany HallSale1
HallSale1 hasMany HallSale2
HallSale1 belongsTo VenueMast

// HR relationships
Employee belongsTo EmpCategory
Attendance belongsTo Employee
Salary belongsTo Employee
```

## Last Updated
- Date: August 7, 2026
- Version: 1.0
