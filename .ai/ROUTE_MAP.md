# Analysis HMS — ROUTE MAP

> 14 route files, loaded by `app/Providers/RouteServiceProvider` (web middleware for most; tools/channel/etc. get custom groups). Full route enumeration available via `php artisan route:list` — this doc records structure + notable security-relevant facts.

---

## Route files → middleware

| File | Middleware | Notes |
|------|-----------|-------|
| `routes/web.php` | web | Main entry |
| `routes/company.php` | web | Company portal |
| `routes/property.php` | web | Property portal |
| `routes/admin.php` | web | Admin/superadmin |
| `routes/pointofsale.php` | pointofsale | POS |
| `routes/pointofsale/` | (kot group) | KOT |
| `routes/reporting.php` | reporting | Reports |
| `routes/userparam.php` | userparam | User params |
| `routes/tools.php` | **tools (NO auth middleware — controller self-guards)** | Tools/support/tickets/table-mgmt |
| `routes/channel.php` | channel | Channel manager |
| `routes/api.php` | api + sanctum + ApiAuth | 13 API endpoints |
| `routes/channels.php` | — | Broadcast channels |
| `routes/console.php` | — | Artisan commands/schedule |

## Notable endpoints (verified)

- `tools/tablemanagement`, `tools/fetch_tables`, `tools/fetch_table_data`, `tools/bulk_update_records`, `tools/update_table_cell`, `tools/insert_record`, `tools/delete_table_record`, `tools/delete_multiple_records` — DB admin tool, **controller-constructor-gated** (auth + superadmin/property-20).
- `tools/submitTicket`, `tools/viewTickets`, `tools/getTicketMessages`, `tools/sendTicketMessage`, `tools/transferTicket`, etc. — support tickets (CSRF protected).
- POS print events (`SaleBillPrintEvent`, `PrintEvent`) broadcast via Reverb (try/catch-wrapped).

## Actions

- Run `php artisan route:list` and diff against `.ai/ROUTES.md` (prior doc) to reconcile.
- When adding routes: preserve existing route names (mission §1); add new routes to the appropriate group file; keep CSRF (POST) coverage.
