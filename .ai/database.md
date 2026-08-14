# Analysis HMS - Database Analysis

## Database Overview

This document provides deep database analysis instructions and documentation for the Analysis HMS project.

---

## Database Engine

### MySQL
- **Version**: 5.7+ / 8.0+
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB

### Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_analysishms
DB_USERNAME=root
DB_PASSWORD=
```

---

## Table Categories

### 1. Core Hotel Tables

#### Room Management
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `rooms` | Room definitions | room_no, room_cat, status |
| `room_mast` | Room master | room_no, floor, category |
| `roomcat` | Room categories | cat_code, cat_name, rate |
| `roomocc` | Room occupancy | docid, room_no, status |
| `roomclean` | Cleaning status | room_no, status, datetime |
| `roomfeature` | Room features | room_no, feature_code |
| `roomblockout` | Blockouts | room_no, from_date, to_date |
| `roomkey` | Key tracking | room_no, key_no, status |

#### Housekeeping
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `hkroomassign` | Assignment | room_no, staff_id, status |
| `hkcleaninghdr` | Cleaning header | docid, date, status |
| `hkcleaningftr` | Cleaning footer | docid, room_no, checklist |
| `hkinspectionhdr` | Inspection header | docid, date, inspector |
| `hkinspectionftr` | Inspection footer | docid, room_no, status |
| `hkdamage` | Damage reports | room_no, description, date |
| `hkfloors` | Floor definitions | floor_no, floor_name |
| `housekeeparmast` | Housekeepers | staff_id, name, status |
| `hksupervisor` | Supervisors | staff_id, name, status |
| `hkchecklistmast` | Checklist items | checklist_id, description |
| `hkamentiesmast` | Amenities | amenity_id, description |

### 2. Booking Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `booking` | Main bookings | docid, guest_name, arrival |
| `bookingdetail` | Details | docid, room_no, rate |
| `bookingplandetails` | Plan details | docid, plan_code, amount |
| `grpbookingdetails` | Group bookings | docid, sno, room_det |
| `reservations` | Reservations | docid, status, dates |
| `bookinginquiry` | Inquiries | inqno, guest_name, phone |
| `bookingfollowup` | Follow-ups | inqno, status, notes |
| `bookingsource` | Sources | source_code, source_name |

### 3. Guest Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `guestprof` | Guest profiles | docid, name, phone, email |
| `guestfolio` | Guest folios | docid, folio_no, balance |
| `guestfolioprofdetail` | Folio details | docid, profile_id |
| `gueststats` | Statistics | docid, total_stays |
| `guestreward` | Rewards | docid, points, level |

### 4. Financial Tables

#### Ledger & Accounting
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ledger` | Main ledger | docid, sub_code, amount |
| `ledgerlog` | Ledger log | docid, sub_code, entry |
| `ledgertds` | TDS ledger | docid, tds_amount |
| `suntran` | Transactions | docid, v_date, amount |
| `suntranh` | Transaction header | docid, v_type, amount |
| `suntranlog` | Transaction log | docid, entry_no |
| `suntranest` | Estimate trans | docid, amount |
| `subgroup` | Account groups | group_code, group_name |

#### Payments & Charges
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `paycharge` | Payments/charges | docid, amount, type |
| `paychargeh` | Payment header | docid, v_date |
| `paychargelog` | Payment log | docid, entry_no |
| `fombilldetail` | FOM bill details | docid, item, amount |

#### Tax & GST
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `taxstru` | Tax structure | tax_code, tax_name, rate |
| `revmast` | Revenue heads | rev_code, rev_name |
| `nctype_mast` | NC type | nc_code, nc_name |

### 5. POS Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sale1` | Sale header | docid, v_date, total |
| `sale2` | Sale items | docid, item_code, qty |
| `sale1log` | Sale header log | docid, entry_no |
| `sale2log` | Sale items log | docid, entry_no |
| `kot` | Kitchen orders | docid, table_no, status |
| `kotlog` | KOT log | docid, entry_no |
| `items` | Item definitions | item_code, item_name |
| `itemmast` | Item master | item_code, category |
| `itemrate` | Item rates | item_code, rate |
| `itemgrp` | Item groups | grp_code, grp_name |
| `itemcatmast` | Item categories | cat_code, cat_name |

### 6. Banquet Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `hallbook` | Hall bookings | docid, hall_no, date |
| `hallsale1` | Hall sale header | docid, booking_id |
| `hallsale2` | Hall sale items | docid, item_code, qty |
| `hallsale1est` | Estimate header | docid, date |
| `hallsale2est` | Estimate items | docid, item_code |
| `venuemast` | Venue master | venue_id, venue_name |
| `venueocc` | Venue occupancy | venue_id, date, status |
| `venuefeatures` | Venue features | venue_id, feature |
| `functiontype` | Function types | type_code, type_name |

### 7. Inventory Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `stock` | Current stock | item_code, qty, godown |
| `stocklog` | Stock movements | item_code, qty, type |
| `purch1` | Purchase header | docid, supplier, date |
| `purch2` | Purchase items | docid, item_code, qty |
| `indent` | Indent header | docid, from_godown, date |
| `indent1` | Indent items | docid, item_code, qty |
| `gin` | Goods received | docid, indent_no, date |
| `godownmast` | Godown master | godown_id, godown_name |
| `unitmast` | Unit master | unit_code, unit_name |

### 8. HR Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `employee` | Employee master | emp_id, name, department |
| `empcategory` | Categories | cat_code, cat_name |
| `attendance` | Attendance | emp_id, date, status |
| `salary` | Salary records | emp_id, month, amount |
| `hrpayrolls` | Payroll data | emp_id, month, gross |
| `overtime` | Overtime | emp_id, hours, date |
| `loan` | Loans | emp_id, amount, status |
| `depart` | Departments | dept_code, dept_name |
| `depart1` | Dept details | dept_code, detail |

### 9. System Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | id, name, email, role |
| `userpermission` | Permissions | user_id, module, access |
| `usermodule` | Modules | module_id, module_name |
| `usercrudperm` | CRUD perms | user_id, crud_type |
| `enviro_*` | Settings | 15+ enviro tables |
| `companyreg` | Company reg | company_id, name |
| `companylog` | Company log | company_id, action |

---

## Indexes

### Performance Indexes

#### Room Tables
```sql
CREATE INDEX idx_room_mast_room_no ON room_mast(room_no);
CREATE INDEX idx_room_mast_category ON room_mast(room_cat);
CREATE INDEX idx_roomocc_room_no ON roomocc(room_no);
CREATE INDEX idx_roomocc_status ON roomocc(status);
```

#### Booking Tables
```sql
CREATE INDEX idx_booking_docid ON booking(docid);
CREATE INDEX idx_booking_arrival ON booking(arrival);
CREATE INDEX idx_bookingdetail_docid ON bookingdetail(docid);
CREATE INDEX idx_grpbookingdetails_docid ON grpbookingdetails(docid);
```

#### Financial Tables
```sql
CREATE INDEX idx_ledger_sub_code ON ledger(sub_code);
CREATE INDEX idx_ledger_v_date ON ledger(v_date);
CREATE INDEX idx_suntran_v_date ON suntran(v_date);
CREATE INDEX idx_paycharge_docid ON paycharge(docid);
```

#### POS Tables
```sql
CREATE INDEX idx_sale1_v_date ON sale1(v_date);
CREATE INDEX idx_sale2_docid ON sale2(docid);
CREATE INDEX idx_kot_docid ON kot(docid);
CREATE INDEX idx_items_item_code ON items(item_code);
```

---

## Relationships

### Key Relationships

```
booking ──────┬─── bookingdetail
              ├─── grpbookingdetails
              ├─── bookingplandetails
              └─── guestprof

room_mast ────┬─── roomocc
              ├─── roomclean
              └─── hkroomassign

ledger ───────┬─── suntran
              ├─── paycharge
              └─── ledgerlog

sale1 ────────┬─── sale2
              └─── kot

hallbook ─────┬─── hallsale1
              └─── hallsale2

employee ─────┬─── attendance
              ├─── salary
              └─── hrpayrolls
```

### Foreign Key Constraints

```sql
ALTER TABLE bookingdetail ADD CONSTRAINT fk_bookingdetail_booking
FOREIGN KEY (docid) REFERENCES booking(docid);

ALTER TABLE roomocc ADD CONSTRAINT fk_roomocc_room_mast
FOREIGN KEY (room_no) REFERENCES room_mast(room_no);

ALTER TABLE ledger ADD CONSTRAINT fk_ledger_subgroup
FOREIGN KEY (sub_code) REFERENCES subgroup(group_code);

ALTER TABLE sale2 ADD CONSTRAINT fk_sale2_sale1
FOREIGN KEY (docid) REFERENCES sale1(docid);
```

---

## Stored Procedures

### Night Audit Procedure
```sql
DELIMITER //
CREATE PROCEDURE sp_night_audit(IN p_propertyid INT, IN p_date DATE)
BEGIN
    -- Room charge posting
    -- Revenue posting
    -- Daily settlement
    -- Audit logging
END //
DELIMITER ;
```

### Revenue Posting Procedure
```sql
DELIMITER //
CREATE PROCEDURE sp_revenue_posting(IN p_propertyid INT, IN p_date DATE)
BEGIN
    -- Calculate room charges
    -- Post to ledger
    -- Update suntran
    -- Log transactions
END //
DELIMITER ;
```

---

## Triggers

### Room Status Update Trigger
```sql
DELIMITER //
CREATE TRIGGER trg_room_status_update
AFTER UPDATE ON roomocc
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO roomstatuslog (room_no, old_status, new_status, changed_at)
        VALUES (NEW.room_no, OLD.status, NEW.status, NOW());
    END IF;
END //
DELIMITER ;
```

### Stock Balance Update Trigger
```sql
DELIMITER //
CREATE TRIGGER trg_stock_balance_update
AFTER INSERT ON stocklog
FOR EACH ROW
BEGIN
    UPDATE stock 
    SET qty = qty + NEW.qty 
    WHERE item_code = NEW.item_code 
    AND godown = NEW.godown;
END //
DELIMITER ;
```

---

## Views

### Room Availability View
```sql
CREATE VIEW vw_room_availability AS
SELECT 
    rm.room_no,
    rm.room_cat,
    rc.cat_name,
    rm.floor,
    ro.status,
    ro.docid
FROM room_mast rm
LEFT JOIN roomcat rc ON rm.room_cat = rc.cat_code
LEFT JOIN roomocc ro ON rm.room_no = ro.room_no;
```

### Revenue Summary View
```sql
CREATE VIEW vw_revenue_summary AS
SELECT 
    s.v_date,
    SUM(s.total) AS total_revenue,
    COUNT(s.docid) AS total_bills
FROM sale1 s
GROUP BY s.v_date;
```

---

## Slow Query Analysis

### Common Slow Queries

#### 1. Room Availability Query
```sql
-- Problem: N+1 query for room status
-- Solution: Use eager loading
SELECT rm.*, ro.status 
FROM room_mast rm
LEFT JOIN roomocc ro ON rm.room_no = ro.room_no;
```

#### 2. Booking Search Query
```sql
-- Problem: Full table scan
-- Solution: Add indexes
SELECT b.*, bd.* 
FROM booking b
JOIN bookingdetail bd ON b.docid = bd.docid
WHERE b.guest_name LIKE '%search%';
```

#### 3. Revenue Report Query
```sql
-- Problem: Aggregation on large dataset
-- Solution: Use materialized view
SELECT v_date, SUM(total) 
FROM sale1 
GROUP BY v_date;
```

---

## Duplicate Data Detection

### Check for Duplicates
```sql
-- Check duplicate bookings
SELECT docid, COUNT(*) 
FROM booking 
GROUP BY docid 
HAVING COUNT(*) > 1;

-- Check duplicate guests
SELECT name, phone, COUNT(*) 
FROM guestprof 
GROUP BY name, phone 
HAVING COUNT(*) > 1;
```

### Remove Duplicates
```sql
-- Remove duplicate records
DELETE t1 FROM table_name t1
INNER JOIN table_name t2
WHERE t1.id < t2.id 
AND t1.column_name = t2.column_name;
```

---

## Missing Indexes

### Detection Query
```sql
-- Find tables without primary keys
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'db_analysishms' 
AND TABLE_NAME NOT IN (
    SELECT TABLE_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE CONSTRAINT_NAME = 'PRIMARY'
);
```

---

## Database Maintenance

### Regular Maintenance
1. **Optimize Tables** - Monthly
2. **Analyze Tables** - Weekly
3. **Backup Database** - Daily
4. **Clean Logs** - Weekly
5. **Monitor Performance** - Daily

### Backup Strategy
```bash
mysqldump -u root -p db_analysishms > backup_$(date +%Y%m%d).sql
```

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
