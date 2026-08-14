# Front Office Module

## Overview

The Front Office module handles reservations, check-in/check-out, room management, and guest services.

---

## Components

### Controllers
- `RoomController` - Room management
- `RoomStatus` - Room status updates
- `Reservation` - Reservation management
- `BookingFollowUp` - Booking follow-ups
- `BookingInquiryController` - Booking inquiries
- `ChargePosting` - Charge posting

### Models
- `Bookings` - Main bookings
- `BookingDetail` - Booking details
- `GrpBookinDetail` - Group bookings
- `GuestProf` - Guest profiles
- `Guestfolio` - Guest folios
- `RoomMast` - Room master
- `RoomOcc` - Room occupancy

### Services
- `ResHelper` - Reservation utilities

---

## Workflows

### Reservation Flow
1. Guest inquiry
2. Check availability
3. Create reservation
4. Collect advance
5. Confirm reservation

### Check-In Flow
1. Verify reservation
2. Collect guest ID
3. Assign room
4. Generate key
5. Create folio

### Check-Out Flow
1. Collect key
2. Verify room
3. Calculate bill
4. Process payment
5. Generate invoice

---

## Database Tables

- `booking` - Main bookings
- `bookingdetail` - Booking details
- `grpbookingdetails` - Group bookings
- `guestprof` - Guest profiles
- `guestfolio` - Guest folios
- `room_mast` - Room master
- `roomocc` - Room occupancy

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/room` | RoomController@index | room.index |
| POST | `/reservation/store` | Reservation@store | reservation.store |
| POST | `/checkin` | Reservation@checkin | reservation.checkin |
| POST | `/checkout` | Reservation@checkout | reservation.checkout |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
