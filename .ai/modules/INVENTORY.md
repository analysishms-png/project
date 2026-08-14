# Inventory Module

## Overview

The Inventory module handles stock management, purchase, indent, and godown operations.

---

## Components

### Controllers
- `InventoryController` - Inventory operations
- `PurchaseController` - Purchase operations

### Models
- `Stock` - Current stock
- `Stocklog` - Stock movements
- `Purch1` - Purchase header
- `Purch2` - Purchase items
- `Indent` - Indent header
- `Indent1` - Indent items
- `Gin` - Goods received
- `GodownMast` - Godown master
- `UnitMast` - Unit master

---

## Workflows

### Stock Receipt Flow
1. Receive items
2. Verify against PO
3. Check quality
4. Update stock
5. Generate receipt

### Stock Issue Flow
1. Create indent
2. Approve indent
3. Issue items
4. Update stock
5. Generate slip

---

## Database Tables

- `stock` - Current stock
- `stocklog` - Stock movements
- `purch1` - Purchase header
- `purch2` - Purchase items
- `indent` - Indent header
- `indent1` - Indent items
- `gin` - Goods received
- `godownmast` - Godown master
- `unitmast` - Unit master

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/inventory` | InventoryController@index | inventory.index |
| POST | `/inventory/store` | InventoryController@store | inventory.store |
| GET | `/purchase` | PurchaseController@index | purchase.index |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
