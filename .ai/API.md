# Analysis HMS - API Documentation

## API Overview

This document describes the API endpoints for the Analysis HMS project.

---

## Authentication

### Login
```
POST /api/login
```

**Request**:
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response**:
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

### Logout
```
POST /api/logout
Authorization: Bearer {token}
```

**Response**:
```json
{
    "message": "Successfully logged out"
}
```

---

## Rooms

### Get Rooms
```
GET /api/rooms
Authorization: Bearer {token}
```

**Response**:
```json
{
    "data": [
        {
            "room_no": "101",
            "room_cat": "DLX",
            "floor": 1,
            "status": "VACANT"
        }
    ]
}
```

### Get Room
```
GET /api/rooms/{room_no}
Authorization: Bearer {token}
```

**Response**:
```json
{
    "data": {
        "room_no": "101",
        "room_cat": "DLX",
        "floor": 1,
        "status": "VACANT",
        "category": {
            "cat_code": "DLX",
            "cat_name": "Deluxe",
            "rate": 5000
        }
    }
}
```

### Update Room Status
```
PUT /api/rooms/{room_no}/status
Authorization: Bearer {token}
```

**Request**:
```json
{
    "status": "OCCUPIED"
}
```

**Response**:
```json
{
    "message": "Room status updated"
}
```

---

## Reservations

### Get Reservations
```
GET /api/reservations
Authorization: Bearer {token}
```

**Response**:
```json
{
    "data": [
        {
            "docid": "RES001",
            "guest_name": "John Doe",
            "arrival": "2026-08-10",
            "departure": "2026-08-12",
            "room_no": "101",
            "status": "CONFIRMED"
        }
    ]
}
```

### Create Reservation
```
POST /api/reservations
Authorization: Bearer {token}
```

**Request**:
```json
{
    "guest_name": "John Doe",
    "phone": "1234567890",
    "email": "john@example.com",
    "arrival": "2026-08-10",
    "departure": "2026-08-12",
    "room_no": "101",
    "room_cat": "DLX"
}
```

**Response**:
```json
{
    "message": "Reservation created",
    "data": {
        "docid": "RES001",
        "guest_name": "John Doe"
    }
}
```

### Update Reservation
```
PUT /api/reservations/{docid}
Authorization: Bearer {token}
```

**Request**:
```json
{
    "arrival": "2026-08-11",
    "departure": "2026-08-13"
}
```

**Response**:
```json
{
    "message": "Reservation updated"
}
```

### Cancel Reservation
```
DELETE /api/reservations/{docid}
Authorization: Bearer {token}
```

**Response**:
```json
{
    "message": "Reservation cancelled"
}
```

---

## Check-In

### Check-In Guest
```
POST /api/checkin
Authorization: Bearer {token}
```

**Request**:
```json
{
    "docid": "RES001",
    "id_proof": "passport",
    "id_number": "AB123456",
    "advance": 1000
}
```

**Response**:
```json
{
    "message": "Check-in successful",
    "data": {
        "room_no": "101",
        "folio_no": "F001",
        "key_no": "K001"
    }
}
```

---

## Check-Out

### Check-Out Guest
```
POST /api/checkout
Authorization: Bearer {token}
```

**Request**:
```json
{
    "folio_no": "F001",
    "payment_method": "cash",
    "amount": 5000
}
```

**Response**:
```json
{
    "message": "Check-out successful",
    "data": {
        "invoice_no": "INV001",
        "total": 5000,
        "payment": 5000,
        "balance": 0
    }
}
```

---

## POS

### Create KOT
```
POST /api/pos/kot
Authorization: Bearer {token}
```

**Request**:
```json
{
    "table_no": "T1",
    "items": [
        {
            "item_code": "ITEM001",
            "qty": 2,
            "rate": 100
        }
    ]
}
```

**Response**:
```json
{
    "message": "KOT created",
    "data": {
        "kot_no": "KOT001",
        "table_no": "T1"
    }
}
```

### Generate Bill
```
POST /api/pos/bill
Authorization: Bearer {token}
```

**Request**:
```json
{
    "kot_no": "KOT001",
    "payment_method": "cash"
}
```

**Response**:
```json
{
    "message": "Bill generated",
    "data": {
        "bill_no": "BILL001",
        "total": 200,
        "tax": 36,
        "grand_total": 236
    }
}
```

---

## Reports

### Daily Report
```
GET /api/reports/daily?date=2026-08-07
Authorization: Bearer {token}
```

**Response**:
```json
{
    "data": {
        "date": "2026-08-07",
        "occupancy": 75,
        "revenue": 150000,
        "guests": 45,
        "arrivals": 12,
        "departures": 8
    }
}
```

### Revenue Report
```
GET /api/reports/revenue?from=2026-08-01&to=2026-08-07
Authorization: Bearer {token}
```

**Response**:
```json
{
    "data": {
        "total_revenue": 1050000,
        "room_revenue": 750000,
        "pos_revenue": 300000,
        "daily": [
            {
                "date": "2026-08-01",
                "revenue": 150000
            }
        ]
    }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
    "error": "Bad request",
    "message": "Invalid input"
}
```

### 401 Unauthorized
```json
{
    "error": "Unauthorized",
    "message": "Invalid credentials"
}
```

### 403 Forbidden
```json
{
    "error": "Forbidden",
    "message": "Insufficient permissions"
}
```

### 404 Not Found
```json
{
    "error": "Not found",
    "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
    "error": "Validation failed",
    "message": {
        "email": "The email field is required."
    }
}
```

### 500 Server Error
```json
{
    "error": "Server error",
    "message": "An unexpected error occurred"
}
```

---

## Rate Limiting

- **Authenticated**: 60 requests per minute
- **Unauthenticated**: 30 requests per minute

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
