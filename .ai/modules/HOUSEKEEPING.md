# Housekeeping Module

## Overview

The Housekeeping module handles room cleaning, inspection, and assignment operations.

---

## Components

### Controllers
- `HouseKeeping` - Housekeeping operations
- `HkQrLoginController` - HK QR login

### Models
- `Hkroomassign` - Room assignment
- `HkFloor` - Floor definitions
- `HkSupervisor` - Supervisors
- `HkChecklistMast` - Checklist items
- `HkAmentiesMaster` - Amenities
- `HousekeeperMast` - Housekeepers

---

## Workflows

### Room Cleaning Flow
1. Assign housekeeper
2. Provide checklist
3. Complete cleaning
4. Inspect room
5. Update status

### Room Inspection Flow
1. Check cleanliness
2. Check amenities
3. Check maintenance
4. Approve or reject
5. Update status

---

## Database Tables

- `hkroomassign` - Room assignment
- `hkcleaninghdr` - Cleaning header
- `hkcleaningftr` - Cleaning footer
- `hkinspectionhdr` - Inspection header
- `hkinspectionftr` - Inspection footer
- `hkdamage` - Damage reports
- `hkfloors` - Floor definitions
- `housekeeparmast` - Housekeepers
- `hksupervisor` - Supervisors

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/housekeeping` | HouseKeeping@index | housekeeping.index |
| POST | `/housekeeping/assign` | HouseKeeping@assign | housekeeping.assign |
| POST | `/housekeeping/inspect` | HouseKeeping@inspect | housekeeping.inspect |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
