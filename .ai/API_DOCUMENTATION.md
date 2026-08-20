# Analysis HMS — API Documentation

> API endpoints for the Analysis HMS Hotel Management System.

---

## Authentication

### Web Login (Session)

```http
POST /login
Content-Type: application/x-www-form-urlencoded

u_name=admin&password=secret
```

**Response**: Redirect to dashboard (session cookie)

### API Token (Sanctum)

```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "secret"
}
```

**Response**:
```json
{
    "token": "1|abc123...",
    "user": { ... }
}
```

### Using Token

```http
Authorization: Bearer 1|abc123...
```

---

## API Endpoints

### Reservation API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/reservation` | Create new reservation | Token |
| `PUT` | `/api/reservation/{docid}` | Update reservation | Token |
| `DELETE` | `/api/reservation/{docid}` | Cancel reservation | Token |

#### Create Reservation

```http
POST /api/reservation
Content-Type: application/json
Authorization: Bearer {token}

{
    "ArrDate": "2026-08-20",
    "DepDate": "2026-08-22",
    "RoomCat": "DLX",
    "RoomNo": "101",
    "Name": "John Doe",
    "Mobile": "9876543210",
    "Adults": 2,
    "Children": 0,
    "Rate": 5000,
    "Plan": "CP",
    "CompanyCode": "",
    "TACode": "",
    "SourceCode": "",
    "Advance": 1000,
    "PaymentMode": "CASH"
}
```

**Response**:
```json
{
    "status": "success",
    "docid": "102RES 2026 100",
    "message": "Reservation created"
}
```

### Check-in API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/checkin` | Check-in guest (from reservation) | Token |

```http
POST /api/checkin
Content-Type: application/json
Authorization: Bearer {token}

{
    "resdocid": "102RES 2026 100",
    "roomno": "101",
    "name": "John Doe",
    "mobile": "9876543210",
    "adults": 2,
    "children": 0,
    "rate": 5000,
    "plan": "CP"
}
```

### Room Status API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/api/rooms` | List all rooms | Token |
| `GET` | `/api/rooms/available` | Available rooms | Token |
| `GET` | `/api/rooms/status` | Room status board | Token |

```http
GET /api/rooms/available?checkin=2026-08-20&checkout=2026-08-22&category=DLX
Authorization: Bearer {token}
```

**Response**:
```json
{
    "rooms": [
        {
            "roomno": "101",
            "category": "DLX",
            "floor": "1",
            "status": "V",
            "rate": 5000
        }
    ]
}
```

### Guest API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/api/guests` | List guests | Token |
| `GET` | `/api/guests/{docid}` | Guest details | Token |
| `GET` | `/api/guests/{docid}/folio` | Guest folio | Token |
| `GET` | `/api/guests/{docid}/ledger` | Guest ledger | Token |

### POS API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/pos/kot` | Create KOT | Token |
| `POST` | `/api/pos/bill` | Create bill | Token |
| `POST` | `/api/pos/settle` | Settle bill | Token |

### Reports API

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/reports/occupancy` | Room occupancy report | Token |
| `POST` | `/api/reports/revenue` | Revenue report | Token |
| `POST` | `/api/reports/guests` | Guest list report | Token |

---

## Web AJAX Endpoints

These are internal AJAX endpoints used by the frontend.

### Front Office

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/getindex` | Dashboard data |
| `POST` | `/walkinprefilled` | Prefilled check-in form |
| `POST` | `/openupdatereservation` | Edit reservation form |
| `POST` | `/fetchallemptyrooms` | Available rooms for check-in |
| `POST` | `/fetchplancacl` | Plan/rate calculation |
| `POST` | `/guesthistory` | Guest search history |

### Room Management

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/getRoomswalkin` | Rooms for walk-in |
| `POST` | `/getRooms` | Rooms for reservation |
| `POST` | `/changeroomstore` | Execute room change |
| `POST` | `/roomsettlestore` | Room settlement |
| `POST` | `/advchargeformstore` | Additional charge |

### POS

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/pos/saleregfetch` | POS bill list |
| `POST` | `/pos/settlereportfetch` | Settlement report |
| `POST` | `/pos/displaytable` | Table layout |
| `POST` | `/pos/submitkotentry` | Submit KOT |
| `POST` | `/pos/salebillsubmit` | Submit sale bill |
| `POST` | `/pos/salebillsettle` | Settle POS bill |

### Reports (reporting.php)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/generalledger` | General ledger report |
| `POST` | `/generalledgerfetch` | General ledger data |
| `GET` | `/daybook` | Day book report |
| `POST` | `/daybookfetch` | Day book data |
| `GET` | `/cashbankbook` | Cash/Bank book |
| `GET` | `/journalbook` | Journal book |
| `GET` | `/gstconsolidatedregister` | GST register |
| `GET` | `/nightauditrecon` | Night audit reconciliation |
| `GET` | `/amrmorningreport` | AMR morning report |
| `GET` | `/checkedinguestdetail` | Checked-in guest detail |
| `GET` | `/roomwiseroomrevenue` | Room-wise revenue |
| `GET` | `/formcreport` | Form C (foreign guests) |
| `GET` | `/fosettlereport` | FO settlement report |
| `GET` | `/reservationstatus` | Reservation status dashboard |
| `GET` | `/roomrentaudit` | Room rent audit report |

### Housekeeping

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/savehousecleaning` | Save cleaning entry |
| `POST` | `/roomcleaningentry` | Room cleaning form |
| `POST` | `/submitstartcleaning` | Start cleaning |
| `POST` | `/submitcleaningentry` | Complete cleaning |

### Guest Management

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/updatenewprofile` | Update guest profile |
| `POST` | `/submitwakeup` | Wake-up call |
| `POST` | `/submitguestmessage` | Guest message |

---

## Data Formats

### Date Format
- **Input**: `YYYY-MM-DD` (ISO 8601)
- **Display**: `DD-MMM-YYYY` (e.g., `19-Aug-2026`)

### Currency Format
- **Internal**: Decimal (e.g., `5000.00`)
- **Display**: ₹ prefix (e.g., `₹5,000.00`)
- **Helper**: `formatCurrency($amount)` in `app/Helpers/Helpers.php`

### DocID Format
- **Reservation**: `{propertyid}RES {year} {vno}` (e.g., `102RES 2026 100`)
- **Check-in**: `{propertyid}CHK {year} {vno}` (e.g., `102CHK 2026 409`)
- **ADRES (Advance)**: `{propertyid}ADRES {year} {vno}`
- **POS Bill**: `{propertyid}SL1 {year} {vno}`

### Permission Codes
6-digit codes: `XXYYZZ`
- `XX` = Module (11=Accounts, 12=Tax, 13=FO, 14=Reports, 15=HK, 16=Purchase, 17=POS, 19=NightAudit, 20=Admin)
- `YY` = Sub-module
- `ZZ` = Operation (11=view, 12=ins, 13=del)

---

## Error Responses

```json
{
    "success": false,
    "message": "Unauthorized access"
}
```

HTTP Status Codes:
- `200` — Success
- `400` — Validation error
- `401` — Unauthenticated
- `403` — Unauthorized (no permission)
- `404` — Not found
- `500` — Server error

---

## Rate Limiting

API endpoints use Sanctum token-based auth. No explicit rate limiting configured in current codebase. Consider adding:

```php
// In RouteServiceProvider or RouteServiceProvider
Route::middleware('throttle:60,1')->group(function () {
    Route::prefix('api')->group(function () {
        // API routes
    });
});
```
