# Banquet Module

## Overview

The Banquet module handles hall booking, function management, banquet sales, and event operations.

---

## Components

### Controllers
- `Banquet` - Main banquet operations
- `HappyhourController` - Happy hour management

### Models
- `HallBook` - Hall bookings
- `HallSale1` - Hall sale header
- `HallSale2` - Hall sale items
- `HallSale1Est` - Estimate header
- `HallSale2Est` - Estimate items
- `HallStock` - Hall stock
- `HallStockEst` - Estimate stock
- `VenueMast` - Venue master
- `VenueOcc` - Venue occupancy
- `FunctionType` - Function types

### Services
- `BanquetLedgerPosting` - Banquet ledger posting

---

## Workflows

### Hall Booking Flow
1. Check venue availability
2. Collect event details
3. Create booking
4. Collect advance payment
5. Confirm booking

### Function Sheet Flow
1. Create function sheet
2. Select menu items
3. Calculate estimate
4. Send to kitchen
5. Track preparation

### Billing Flow
1. Add items to bill
2. Calculate total
3. Apply taxes
4. Generate invoice
5. Process payment

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `hallbook` | Hall bookings | docid, hall_no, date |
| `hallsale1` | Sale header | docid, booking_id |
| `hallsale2` | Sale items | docid, item_code, qty |
| `hallsale1est` | Estimate header | docid, date |
| `hallsale2est` | Estimate items | docid, item_code |
| `venuemast` | Venue master | venue_id, venue_name |
| `venueocc` | Venue occupancy | venue_id, date, status |
| `venuefeatures` | Venue features | venue_id, feature |
| `functiontype` | Function types | type_code, type_name |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/banquet` | Banquet@index | banquet.index |
| POST | `/banquet/store` | Banquet@store | banquet.store |
| GET | `/banquet/booking/{id}` | Banquet@booking | banquet.booking |
| POST | `/banquet/bill` | Banquet@bill | banquet.bill |

---

## Key Features

1. **Venue Management** - Manage venues, features, and availability
2. **Booking Management** - Create, modify, and cancel bookings
3. **Function Sheets** - Create and manage function sheets
4. **Menu Management** - Manage menu items and pricing
5. **Billing** - Generate bills and process payments
6. **Estimates** - Create and send estimates to customers

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
