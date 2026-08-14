# E-Invoice Module

## Overview

The E-Invoice module handles GST e-invoice generation, IRN (Invoice Reference Number) management, and compliance.

---

## Components

### Controllers
- `EInvoiceParameter` - E-invoice parameters

### Models
- `EInvoiceBill` - E-invoice bills
- `EInvoicePushLog` - Push logs
- `EnviroEinvoice` - E-invoice settings

### Services
- None (uses controllers directly)

---

## Workflows

### E-Invoice Generation Flow
1. Create invoice
2. Validate invoice data
3. Generate IRN
4. Sign invoice
5. Store e-invoice
6. Print QR code

### E-Way Bill Flow
1. Select invoice
2. Enter transport details
3. Generate e-way bill
4. Print e-way bill

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `einvoicebill` | E-invoice bills | docid, irn, status |
| `einvoicepushlog` | Push logs | docid, status, response |
| `enviro_einvoice` | Settings | propertyid, config |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/einvoice` | EInvoiceParameter@index | einvoice.index |
| POST | `/einvoice/generate` | EInvoiceParameter@generate | einvoice.generate |
| GET | `/einvoice/status/{docid}` | EInvoiceParameter@status | einvoice.status |
| POST | `/einvoice/cancel/{docid}` | EInvoiceParameter@cancel | einvoice.cancel |

---

## E-Invoice Fields

### Required Fields
- Supplier GSTIN
- Supplier Name
- Supplier Address
- Document Type
- Document Number
- Document Date
- Total Value
- Total Taxable Value
- Total CGST
- Total SGST
- Total IGST

### Item Fields
- Item Name
- HSN Code
- Quantity
- Unit
- Total Value
- Taxable Value
- CGST Rate
- CGST Amount
- SGST Rate
- SGST Amount

---

## Key Features

1. **IRN Generation** - Generate Invoice Reference Number
2. **QR Code** - Generate QR code for e-invoice
3. **E-Way Bill** - Generate e-way bill
4. **Cancellation** - Cancel e-invoices
5. **Reports** - E-invoice reports
6. **Bulk Generation** - Bulk e-invoice generation

---

## GST Compliance

### Invoice Types
- B2B (Business to Business)
- B2C (Business to Consumer)
- DE (Deemed Export)
- SEZ (Special Economic Zone)

### Tax Types
- CGST (Central GST)
- SGST (State GST)
- IGST (Integrated GST)
- CESS (Cess)

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
