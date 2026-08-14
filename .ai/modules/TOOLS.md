# Tools Module

## Overview

The Tools module handles utilities, data management, support tickets, and system tools.

---

## Components

### Controllers
- `ToolsController` - Main tools operations
- `DeveloperTools` - Developer utilities
- `MetaController` - Meta tag management

### Models
- `SupportTicket` - Support tickets
- `SupportTicketMessage` - Ticket messages
- `SupportTicketTransfer` - Ticket transfers
- `SupportTicketQueue` - Ticket queue
- `SupportNotificationSoundSettings` - Notification settings
- `MetaTag` - Meta tags
- `Page` - Dynamic pages

### Services
- None (uses controllers directly)

---

## Workflows

### Support Ticket Flow
1. Create ticket
2. Assign to staff
3. Add messages
4. Track progress
5. Resolve ticket
6. Get feedback

### Data Management Flow
1. Select table
2. View data
3. Edit records
4. Bulk updates
5. Export data

### Room Charge Posting Flow
1. Select folio
2. Calculate charges
3. Post to ledger
4. Update balances

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `support_tickets` | Tickets | id, subject, status |
| `support_ticket_messages` | Messages | ticket_id, message |
| `support_ticket_transfers` | Transfers | ticket_id, from, to |
| `support_ticket_queue` | Queue | ticket_id, priority |
| `support_notification_sound_settings` | Settings | id, setting |
| `meta_tags` | Meta tags | id, page, content |
| `pages` | Dynamic pages | id, slug, content |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools` | HomeController@tools | tools |
| GET | `/tools/dashboard` | ToolsController@dashboard | tools.dashboard |
| GET | `/tools/tablemanagement` | ToolsController@tableManagement | tools.tableManagement |
| POST | `/tools/fetch_tables` | ToolsController@fetchTables | tools.fetchTables |
| POST | `/tools/update_table_cell` | ToolsController@updateTableCell | tools.updateTableCell |
| GET | `/tools/roomchargepost` | ToolsController@roomChargePost | tools.roomChargePost |
| POST | `/tools/roomchargepostsubmit` | ToolsController@roomChargePostSubmit | tools.roomChargePostSubmit |
| GET | `/tools/advchargetool` | ToolsController@advanceCharge | tools.advanceCharge |
| POST | `/tools/advancechargesubmit` | ToolsController@advanceChargeSubmit | tools.advanceChargeSubmit |
| GET | `/tools/changebilldate` | ToolsController@changeBillDate | tools.changeBillDate |
| POST | `/tools/changebilldatesubmit` | ToolsController@changeBillDateSubmit | tools.changeBillDateSubmit |
| GET | `/tools/posrecycle` | ToolsController@posRecycle | tools.posRecycle |
| POST | `/tools/posrecyclesubmit` | ToolsController@posRecycleSubmit | tools.posRecycleSubmit |
| GET | `/tools/tickets` | ToolsController@viewTickets | tools.viewTickets |
| POST | `/tools/submit-ticket` | ToolsController@submitTicket | tools.submitTicket |
| POST | `/tools/update-ticket-status` | ToolsController@updateTicketStatus | tools.updateTicketStatus |
| GET | `/tools/getlogreport` | ToolsController@logReport | tools.logReport |
| POST | `/tools/fetchlogreport` | ToolsController@fetchLogReport | tools.fetchLogReport |

---

## Key Features

1. **Table Management** - Direct database table management
2. **Room Charge Posting** - Post room charges to ledger
3. **Advance Charge Tool** - Manage advance charges
4. **Bill Date Change** - Change bill dates
5. **POS Recycle** - Recycle POS data
6. **Support Tickets** - Manage support tickets
7. **Log Reports** - View application logs
8. **Meta Tag Management** - Manage SEO meta tags
9. **Page Management** - Manage dynamic pages

---

## Developer Tools

### API Client Generation
- Generate API clients
- Download client code
- Test API endpoints

### Maintenance Mode
- Enable/disable maintenance
- Custom maintenance page

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
