# Analysis HMS — Data Seeding Plan (Property 103)

> **Property**: Analysis Demonstration Package (ID: 103)
> **Period**: April 1, 2026 → August 21, 2026 (143 days)
> **User**: sa (superadmin)

---

## SEEDING OVERVIEW

| Module | Records | Priority |
|--------|---------|----------|
| **Master Data** | ~150 | P0 — Foundation |
| **Room Category/Rooms** | ~30 | P0 — Foundation |
| **Tax Structure** | ~20 | P0 — Foundation |
| **Plan/Master** | ~10 | P0 — Foundation |
| **Reservation** | ~200 | P1 — Core |
| **Check-in/RoomOcc** | ~150 | P1 — Core |
| **Guest Profile** | ~200 | P1 — Core |
| **Guest Folio** | ~150 | P1 — Core |
| **PayCharge** | ~500 | P1 — Core |
| **Room Charges (Night Audit)** | ~400 | P1 — Core |
| **POS/KOT** | ~300 | P1 — Core |
| **POS Bills** | ~200 | P1 — Core |
| **POS Payments** | ~200 | P1 — Core |
| **Banquet** | ~30 | P2 — Secondary |
| **Inventory/Stock** | ~100 | P2 — Secondary |
| **Purchase** | ~50 | P2 — Secondary |
| **Ledger/Suntran** | ~600 | P1 — Financial |
| **Member** | ~20 | P3 — Optional |
| **HR/Attendance** | ~50 | P3 — Optional |
| **TOTAL** | ~3,360 | |

---

## PHASE 1: MASTER DATA (Run First)

### 1.1 Company/Property Setup
```sql
-- Property 103 already exists in company table
-- Verify: SELECT * FROM company WHERE propertyid = 103;
```

### 1.2 Room Categories
```sql
-- Insert room categories if not exists
INSERT IGNORE INTO roomcat (propertyid, cat_code, name, rackrate, inclcount, rev_code, u_entdt, u_name, u_ae)
VALUES
(103, 'DLX103', 'Deluxe Room', 3500.00, 'Y', 'REV103103', NOW(), 'sa', 'a'),
(103, 'STD103', 'Standard Room', 2500.00, 'Y', 'REV103103', NOW(), 'sa', 'a'),
(103, 'SUT103', 'Suite Room', 5500.00, 'Y', 'REV103103', NOW(), 'sa', 'a'),
(103, 'EXD103', 'Executive Deluxe', 4500.00, 'Y', 'REV103103', NOW(), 'sa', 'a');
```

### 1.3 Rooms
```sql
-- Insert rooms for each category
INSERT IGNORE INTO room_mast (propertyid, rcode, rname, type, roomcat, floor, status, u_entdt, u_name, u_ae)
VALUES
-- Standard Rooms (101-110)
(103, '101', 'Room 101', 'RO', 'STD103', '1', 'V', NOW(), 'sa', 'a'),
(103, '102', 'Room 102', 'RO', 'STD103', '1', 'V', NOW(), 'sa', 'a'),
(103, '103', 'Room 103', 'RO', 'STD103', '1', 'V', NOW(), 'sa', 'a'),
(103, '104', 'Room 104', 'RO', 'STD103', '1', 'V', NOW(), 'sa', 'a'),
(103, '105', 'Room 105', 'RO', 'STD103', '1', 'V', NOW(), 'sa', 'a'),
(103, '106', 'Room 106', 'RO', 'STD103', '2', 'V', NOW(), 'sa', 'a'),
(103, '107', 'Room 107', 'RO', 'STD103', '2', 'V', NOW(), 'sa', 'a'),
(103, '108', 'Room 108', 'RO', 'STD103', '2', 'V', NOW(), 'sa', 'a'),
(103, '109', 'Room 109', 'RO', 'STD103', '2', 'V', NOW(), 'sa', 'a'),
(103, '110', 'Room 110', 'RO', 'STD103', '2', 'V', NOW(), 'sa', 'a'),
-- Deluxe Rooms (201-210)
(103, '201', 'Room 201', 'RO', 'DLX103', '3', 'V', NOW(), 'sa', 'a'),
(103, '202', 'Room 202', 'RO', 'DLX103', '3', 'V', NOW(), 'sa', 'a'),
(103, '203', 'Room 203', 'RO', 'DLX103', '3', 'V', NOW(), 'sa', 'a'),
(103, '204', 'Room 204', 'RO', 'DLX103', '3', 'V', NOW(), 'sa', 'a'),
(103, '205', 'Room 205', 'RO', 'DLX103', '3', 'V', NOW(), 'sa', 'a'),
(103, '206', 'Room 206', 'RO', 'DLX103', '4', 'V', NOW(), 'sa', 'a'),
(103, '207', 'Room 207', 'RO', 'DLX103', '4', 'V', NOW(), 'sa', 'a'),
(103, '208', 'Room 208', 'RO', 'DLX103', '4', 'V', NOW(), 'sa', 'a'),
(103, '209', 'Room 209', 'RO', 'DLX103', '4', 'V', NOW(), 'sa', 'a'),
(103, '210', 'Room 210', 'RO', 'DLX103', '4', 'V', NOW(), 'sa', 'a'),
-- Executive Deluxe (301-305)
(103, '301', 'Room 301', 'RO', 'EXD103', '5', 'V', NOW(), 'sa', 'a'),
(103, '302', 'Room 302', 'RO', 'EXD103', '5', 'V', NOW(), 'sa', 'a'),
(103, '303', 'Room 303', 'RO', 'EXD103', '5', 'V', NOW(), 'sa', 'a'),
(103, '304', 'Room 304', 'RO', 'EXD103', '5', 'V', NOW(), 'sa', 'a'),
(103, '305', 'Room 305', 'RO', 'EXD103', '5', 'V', NOW(), 'sa', 'a'),
-- Suite (401-405)
(103, '401', 'Room 401', 'RO', 'SUT103', '6', 'V', NOW(), 'sa', 'a'),
(103, '402', 'Room 402', 'RO', 'SUT103', '6', 'V', NOW(), 'sa', 'a'),
(103, '403', 'Room 403', 'RO', 'SUT103', '6', 'V', NOW(), 'sa', 'a'),
(103, '404', 'Room 404', 'RO', 'SUT103', '6', 'V', NOW(), 'sa', 'a'),
(103, '405', 'Room 405', 'RO', 'SUT103', '6', 'V', NOW(), 'sa', 'a');
```

### 1.4 Tax Structure
```sql
-- Insert GST tax structure
INSERT IGNORE INTO revmast (propertyid, rev_code, name, field_type, type, ac_code, u_entdt, u_name, u_ae)
VALUES
(103, 'CGST103103', 'CGST 6%', 'T', 'Cr', 'CGSTAC103', NOW(), 'sa', 'a'),
(103, 'SGST103103', 'SGST 6%', 'T', 'Cr', 'SGSTAC103', NOW(), 'sa', 'a'),
(103, 'REV103103', 'Room Rent', 'C', 'Dr', 'RMNTAC103', NOW(), 'sa', 'a'),
(103, 'DISC103', 'Discount', 'C', 'Cr', 'DISCAC103', NOW(), 'sa', 'a'),
(103, 'ROFF103', 'Round Off', 'C', 'Dr', 'ROFFAC103', NOW(), 'sa', 'a'),
(103, 'TOUT103', 'Transfer Out', 'C', 'Dr', 'TOUTAC103', NOW(), 'sa', 'a'),
(103, 'RMCH103', 'Room Charge', 'C', 'Dr', 'RMCHAC103', NOW(), 'sa', 'a');

-- Tax structure (rate slab)
INSERT IGNORE INTO taxstru (propertyid, str_code, sno, tax_code, rate, limits, limit1, comp_operator, u_entdt, u_name, u_ae)
VALUES
(103, 'TAXSTR103', 1, 'CGST103103', 6.00, 0, NULL, '<=', NOW(), 'sa', 'a'),
(103, 'TAXSTR103', 2, 'SGST103103', 6.00, 0, NULL, '<=', NOW(), 'sa', 'a');
```

### 1.5 Plan/Master
```sql
INSERT IGNORE INTO plan_mast (propertyid, pcode, name, room_cat, rate, activeYN, u_entdt, u_name, u_ae)
VALUES
(103, 'AP103', 'American Plan', 'DLX103', 4500.00, 'Y', NOW(), 'sa', 'a'),
(103, 'BP103', 'Bed & Breakfast', 'STD103', 3000.00, 'Y', NOW(), 'sa', 'a'),
(103, 'EP103', 'European Plan', 'STD103', 2500.00, 'Y', NOW(), 'sa', 'a'),
(103, 'CP103', 'Continental Plan', 'DLX103', 3800.00, 'Y', NOW(), 'sa', 'a'),
(103, 'MAP103', 'Modified American Plan', 'SUT103', 6000.00, 'Y', NOW(), 'sa', 'a');
```

### 1.6 Booking Source
```sql
INSERT IGNORE INTO busssource (propertyid, code, name, u_entdt, u_name, u_ae)
VALUES
(103, 'BS01', 'Direct', NOW(), 'sa', 'a'),
(103, 'BS02', 'Website', NOW(), 'sa', 'a'),
(103, 'BS03', 'Phone', NOW(), 'sa', 'a'),
(103, 'BS04', 'Walk-in', NOW(), 'sa', 'a'),
(103, 'BS05', 'Travel Agent', NOW(), 'sa', 'a'),
(103, 'BS06', 'Corporate', NOW(), 'sa', 'a'),
(103, 'BS07', 'OTA - MakeMyTrip', NOW(), 'sa', 'a'),
(103, 'BS08', 'OTA - Goibibo', NOW(), 'sa', 'a'),
(103, 'BS09', 'OTA - Booking.com', NOW(), 'sa', 'a'),
(103, 'BS10', 'OTA - OYO', NOW(), 'sa', 'a');
```

### 1.7 POS Outlets (Depart)
```sql
INSERT IGNORE INTO depart (propertyid, dcode, name, short_name, nature, rest_type, u_entdt, u_name, u_ae)
VALUES
(103, 'RST103', 'Main Restaurant', 'RST', 'Outlet', 'F&B', NOW(), 'sa', 'a'),
(103, 'BAR103', 'Bar', 'BAR', 'Outlet', 'F&B', NOW(), 'sa', 'a'),
(103, 'RSV103', 'Room Service', 'RSV', 'Room Service', 'F&B', NOW(), 'sa', 'a'),
(103, 'LBY103', 'Lobby Bar', 'LBY', 'Outlet', 'F&B', NOW(), 'sa', 'a');
```

### 1.8 Item Groups & Items
```sql
-- Item Groups
INSERT IGNORE INTO itemgrp (propertyid, igroup, name, parent, u_entdt, u_name, u_ae)
VALUES
(103, 'FOOD103', 'Food', '', NOW(), 'sa', 'a'),
(103, 'BEV103', 'Beverages', '', NOW(), 'sa', 'a'),
(103, 'LIQ103', 'Liquor', 'BEV103', NOW(), 'sa', 'a'),
(103, 'NALL103', 'Non-Alcoholic', 'BEV103', NOW(), 'sa', 'a'),
(103, 'MISC103', 'Miscellaneous', '', NOW(), 'sa', 'a');

-- Items
INSERT IGNORE INTO itemmast (propertyid, Code, name, igroup, rate, u_entdt, u_name, u_ae)
VALUES
(103, 'ITM001', 'Butter Chicken', 'FOOD103', 350.00, NOW(), 'sa', 'a'),
(103, 'ITM002', 'Paneer Tikka', 'FOOD103', 280.00, NOW(), 'sa', 'a'),
(103, 'ITM003', 'Dal Makhani', 'FOOD103', 220.00, NOW(), 'sa', 'a'),
(103, 'ITM004', 'Jeera Rice', 'FOOD103', 150.00, NOW(), 'sa', 'a'),
(103, 'ITM005', 'Naan', 'FOOD103', 60.00, NOW(), 'sa', 'a'),
(103, 'ITM006', 'Coca Cola', 'NALL103', 40.00, NOW(), 'sa', 'a'),
(103, 'ITM007', 'Fresh Lime Soda', 'NALL103', 60.00, NOW(), 'sa', 'a'),
(103, 'ITM008', 'Kingfisher Beer', 'LIQ103', 150.00, NOW(), 'sa', 'a'),
(103, 'ITM009', 'Old Monk Rum', 'LIQ103', 180.00, NOW(), 'sa', 'a'),
(103, 'ITM010', 'Mixed Veg', 'FOOD103', 180.00, NOW(), 'sa', 'a');
```

---

## PHASE 2: TRANSACTION DATA (April - August 2026)

### 2.1 Guest Names (Indian names for realism)
```sql
-- Guest profiles will be created dynamically during seeding
-- Sample names:
-- Rajesh Kumar, Priya Sharma, Amit Singh, Neha Gupta, Vikram Patel,
-- Anita Desai, Suresh Reddy, Kavita Joshi, Ravi Verma, Pooja Nair,
-- etc. (50+ unique guests)
```

### 2.2 Reservation Pattern
- **April**: 15-20 reservations/month (low season)
- **May**: 20-25 reservations/month (moderate)
- **June**: 25-30 reservations/month (moderate)
- **July**: 30-40 reservations/month (monsoon/off-season)
- **August**: 20-30 reservations/month (partial)

### 2.3 Check-in Pattern
- **Occupancy**: 40-80% depending on season
- **Average Stay**: 2-3 nights
- **Room Mix**: 60% Standard, 25% Deluxe, 10% Executive, 5% Suite

### 2.4 POS Pattern
- **Restaurant**: 20-40 bills/day
- **Room Service**: 5-10 bills/day
- **Bar**: 10-20 bills/day
- **Average Bill**: ₹800-2500

### 2.5 Financial Pattern
- **Room Charges**: Daily auto-post via night audit
- **Tax**: 12% GST on room rent
- **Payments**: Mix of Cash, Card, UPI, Company
- **Settlement**: Most at checkout, some advance settlements

---

## PHASE 3: SEEDING SCRIPTS

### Files to Create:
1. `database/seeders/MasterDataSeeder.php` — Phase 1
2. `database/seeders/ReservationSeeder.php` — Phase 2.1
3. `database/seeders/CheckinSeeder.php` — Phase 2.2
4. `database/seeders/GuestProfileSeeder.php` — Phase 2.3
5. `database/seeders/POSSeeder.php` — Phase 2.4
6. `database/seeders/FinancialSeeder.php` — Phase 2.5
7. `database/seeders/NightAuditSeeder.php` — Phase 2.6

---

## EXECUTION ORDER

1. Run MySQL/XAMPP
2. `php artisan db:seed --class=MasterDataSeeder`
3. `php artisan db:seed --class=GuestProfileSeeder`
4. `php artisan db:seed --class=ReservationSeeder`
5. `php artisan db:seed --class=CheckinSeeder`
6. `php artisan db:seed --class=POSSeeder`
7. `php artisan db:seed --class=FinancialSeeder`
8. `php artisan db:seed --class=NightAuditSeeder`

---

## VERIFICATION QUERIES

```sql
-- After seeding, verify:
SELECT 'Room Categories' as tbl, COUNT(*) as cnt FROM roomcat WHERE propertyid=103
UNION ALL SELECT 'Rooms', COUNT(*) FROM room_mast WHERE propertyid=103
UNION ALL SELECT 'Tax Structure', COUNT(*) FROM revmast WHERE propertyid=103
UNION ALL SELECT 'Plans', COUNT(*) FROM plan_mast WHERE propertyid=103
UNION ALL SELECT 'Booking Sources', COUNT(*) FROM busssource WHERE propertyid=103
UNION ALL SELECT 'Outlets', COUNT(*) FROM depart WHERE propertyid=103
UNION ALL SELECT 'Items', COUNT(*) FROM itemmast WHERE propertyid=103
UNION ALL SELECT 'Guest Profiles', COUNT(*) FROM guestprof WHERE propertyid=103
UNION ALL SELECT 'Reservations', COUNT(*) FROM grpbookingdetails WHERE propertyid=103
UNION ALL SELECT 'Room Occ', COUNT(*) FROM roomocc WHERE propertyid=103
UNION ALL SELECT 'Guest Folio', COUNT(*) FROM guestfolio WHERE propertyid=103
UNION ALL SELECT 'PayCharge', COUNT(*) FROM paycharge WHERE propertyid=103
UNION ALL SELECT 'POS Bills', COUNT(*) FROM sale1 WHERE propertyid=103
UNION ALL SELECT 'POS Items', COUNT(*) FROM sale2 WHERE propertyid=103
UNION ALL SELECT 'KOT', COUNT(*) FROM kot WHERE propertyid=103
UNION ALL SELECT 'Suntran', COUNT(*) FROM suntran WHERE propertyid=103
UNION ALL SELECT 'Ledger', COUNT(*) FROM ledger WHERE propertyid=103;
```

---

*Generated: 2026-08-21*
*Property: 103 (Analysis Demonstration Package)*
*Period: April 1, 2026 → August 21, 2026*

## Seeding Results — August 21, 2026

### Seeded Data Summary

| Module | Records | Period |
|--------|---------|--------|
| Guest Profiles | 328+ new | Apr-Aug 2026 |
| Bookings | 150 new | Apr-Aug 2026 |
| Room Occupancy | 150 new (157 total) | Apr-Aug 2026 |
| Guest Folios | 150 new (155 total) | Apr-Aug 2026 |
| PayCharge | 622 total | Apr-Aug 2026 |
| POS Bills (Sale1) | 250 new (262 total) | Apr-Aug 2026 |
| KOTs | 750 new (792 total) | Apr-Aug 2026 |
| SunTran (Accounting) | 1255 total | Apr-Aug 2026 |
| Night Audit Logs | 95 entries | Apr-Aug 2026 |

### Room Category Distribution
| Category | Code | Rooms | Check-ins |
|----------|------|-------|-----------|
| SUPER DELUXE | 1103 | 16 | 39 |
| DELUXE | 2103 | 18 | 45 |
| SUITE | 3103 | 4 | 29 |
| EXECUTIVE | 4103 | 16 | 44 |

### Monthly Revenue (POS)
| Month | Bills | Revenue |
|-------|-------|---------|
| 2026-04 | 44 | ₹11,055 |
| 2026-05 | 52 | ₹17,595 |
| 2026-06 | 54 | ₹16,515 |
| 2026-07 | 68 | ₹21,790 |
| 2026-08 | 44 | ₹19,314 |

### Financial Summary
- Room Rent: ₹10,21,216 (credits)
- CGST: ₹54,708
- SGST: ₹54,708
- Payments (CASH): ₹7,94,504
- Outstanding: ~₹2,80,000

### How to Test
1. Run `php artisan serve`
2. Login: `sa` / `balaji` / `103`
3. Dashboard shows real data
4. Reports filter by Apr-Aug 2026 dates
