# Point of Sale (POS) Module

## Overview

The POS module handles restaurant, bar, KOT (Kitchen Order Ticket), and billing operations.

---

## Components

### Controllers
- `Pointofsale` - Main POS operations
- `Pos` - Alternative POS
- `Kot` - Kitchen orders
- `SaleBill` - Sale bills

### Models
- `Sale1` - Sale header
- `Sale2` - Sale items
- `Kot` - Kitchen orders
- `Items` - Item definitions
- `ItemMast` - Item master
- `ItemRate` - Item rates

---

## Workflows

### KOT Flow
1. Create new KOT
2. Add items
3. Send to kitchen
4. Track preparation
5. Mark complete

### Billing Flow
1. Select items
2. Calculate total
3. Apply taxes
4. Generate bill
5. Process payment

---

## Database Tables

- `sale1` - Sale header
- `sale2` - Sale items
- `kot` - Kitchen orders
- `items` - Item definitions
- `itemmast` - Item master
- `itemrate` - Item rates

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/pos` | Pointofsale@index | pos.index |
| POST | `/pos/kot` | Kot@store | kot.store |
| POST | `/pos/bill` | SaleBill@store | salebill.store |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
