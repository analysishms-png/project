# Analysis HMS — MODULE STATUS

> Status per module. STATUS values: COMPLETE / PARTIAL / MISSING / IMPROVED / UNKNOWN.
> Verified 2026-08-16. Source of truth: Laravel code + legacy HMS comparison.

---

| # | Module | Laravel Presence | Legacy (HMS.bas form) | STATUS | Notes |
|---|--------|------------------|------------------------|--------|-------|
| 01 | Front Office | ✅ CompanyController, property/ views | FrmFOM, FrmControlPanel | COMPLETE | Core flows work; controller is a 22K-line god controller |
| 02 | Reservation | ✅ Booking/BookingSource/RoomCat models, reservation views | FrmBookingInquiry, FrmReservationStatus | COMPLETE | Business rules in CompanyController |
| 03 | Advance Deposit | ✅ Paycharge, frmAdvanceDepDialog legacy | frmAdvanceDepDialog | PARTIAL | Advance→folio traceability needs a reconciliation report (MISSING) |
| 04 | Check-in | ✅ CompanyController checkin + RoomOcc/Guestfolio creation | — | COMPLETE | Transaction-safety review PENDING (see MISSING_LOGIC) |
| 05 | Check-out | ✅ CompanyController checkout, bill print | — | COMPLETE | Settlement/ledger posting via AccountPosting |
| 06 | In-house | ✅ RoomOcc-based screens | FrmHouseStatus | COMPLETE | |
| 07 | Guest Management | ✅ GuestProf model + profile forms | frmGuestInfo, FrmGuestWakeUp, FrmHouGuestMsg, frmGuestComments, FormCReport | PARTIAL | Core profile/ledger/folio COMPLETE; wake-up (GM-01), guest messages (GM-02), C-Form/foreign (GM-04) MISSING — see GUEST_MANAGEMENT_GAPS.md |
| 08 | Guest Ledger | ✅ Ledger, Suntran | — | COMPLETE | |
| 09 | Guest Folio | ✅ Guestfolio model | FrmMultiBill | COMPLETE | |
| 10 | Room Management | ✅ RoomMast/RoomCat | FrmRoomMast, FrmRoomCatMast | COMPLETE | |
| 11 | Room Status | ✅ HouseKeeping, property/housekeeping.blade | FrmHouseStatus | COMPLETE | COLLATE workaround perf issue (PERF-06) |
| 12 | Room Change | ✅ CompanyController | FrmChangeDepart | COMPLETE | |
| 13 | Housekeeping | ✅ HouseKeeping controller + views | FrmHouseStatus, FrmItemIssuedOnCleaning, **FrmGuestWakeUp (NEW)**, **FrmHouGuestMsg (NEW)** | COMPLETE | GM-01/02 added 2026-08-18 |
| 14 | Laundry | ✅ present (inventory-ish) | — | PARTIAL | Verify coverage vs legacy |
| 15 | Lost & Found | ✅ HouseKeeping lostfound CRUD + register + print | FrmLostFound | COMPLETE | Verified 2026-08-16: lostfoundform/store/list/edit/print/register routes + `property/housekeeping/lostfound.blade.php` + `lostfound` table |
| 16 | POS | ✅ Pointofsale controller, KOT, kot/ routes | FrmPOS, FrmPOSBillDeletion, FrmPOSRecycleData | COMPLETE | POSBillModificationDatewise/ItemGroupwise — verify parity |
| 17 | KOT | ✅ Kot controller | FrmChangeKitch | COMPLETE | |
| 18 | Restaurant | ✅ POS-based | — | COMPLETE | |
| 19 | Banquet | ✅ Banquet controller (4.7K lines), HallSale/HallBook | FrmEventMast, FrmVenueMast | COMPLETE | |
| 20 | Inventory | ✅ InventoryController (5.9K), Stock/ItemMast | FrmItemMast, FrmConsumMast, FrmOPStock, FrmStockTransfer | COMPLETE (BUG-039 audit fix 2026-08-16) | INV-01..03: not-delivered-order report, Stocklog on MR-edit, valuation filter parity |
| 21 | Purchase | ✅ Purch1/Purch2, Porder, Gin, Indent | FrmPurch | COMPLETE | BUG-040/041/042 fixed 2026-08-16 (PO delete guard, Indent/PO linkage release); 6 orphaned POs prop 103 await approval |
| 22 | Store | ✅ Stock/Indent | — | PARTIAL | Verify stock issue/transfer flows |
| 23 | Accounts | ✅ Suntran, Ledger, AccountPosting | FrmACC, FrmAdjust, FrmMergeCharge, FrmRevMergeCharge | COMPLETE | ✅ audited 2026-08-16; report parity COMPLETE 2026-08-17 (Led/DayBook/CashBook/BankBook/JournalBook); Aging/DueList need bucket decision (P2) |
| 24 | Finance | ✅ Finance module docs | FrmExpense, FrmClaimEntry | PARTIAL | |
| 25 | GST | ✅ Gstr1 helper, TaxStru | FrmTaxMast, FrmTaxStruMast | COMPLETE | |
| 26 | Tax | ✅ calculateTax, taxstru | FrmTaxStruMast | COMPLETE | |
| 27 | E-Invoice | ✅ Einvoice module (EInvoiceBill model) | — | PARTIAL | |
| 28 | Night Audit | ✅ nightauditlog/ views, nightinfo session | — | COMPLETE | |
| 29 | Reports | ✅ Reporting controller (5.4K), reporting.php routes | many | COMPLETE | Missing reports tracked in MISSING_REPORTS.md |
| 30 | MIS | ✅ part of Reporting | FrmRevenueWiseBudget | PARTIAL | |
| 31 | Membership | ✅ MemberCategory | FrMember | PARTIAL | |
| 32 | Reward Points | ⚠️ | — | UNKNOWN | Verify |
| 33 | Smart Card | ⚠️ | — | UNKNOWN | Verify |
| 34 | Cash Card | ⚠️ | — | UNKNOWN | Verify |
| 35 | Cashier | ✅ POS cashier flows | FrmUserPaymentCollection | COMPLETE | |
| 36 | Denomination | ❌ | FrmDenomination (DenominationDetail) | MISSING | Verified 2026-08-16: no route/view/model/table — legacy POS Reports "Denomination Detail" cashier closeout |
| 37 | Telephone | ⚠️ | — | UNKNOWN | Verify |
| 38 | EPABX | ⚠️ | — | UNKNOWN | Verify |
| 39 | SMS | ✅ whatsappparameter, SMS logs (whatsapp_logs) | FrmSMSEnviro, FrmSMSMultiType | COMPLETE | |
| 40 | WhatsApp/Communication | ✅ whatsapp_logs, WhatsappSend helper | — | COMPLETE | |
| 41 | HR | ✅ hr/ views, Employee model | — | PARTIAL | |
| 42 | Payroll | ✅ EnviroPayroll, payrollparameter | — | PARTIAL | |
| 43 | Attendance | ⚠️ | — | UNKNOWN | Verify |
| 44 | Assets | ⚠️ | — | UNKNOWN | Verify |
| 45 | Maintenance | ✅ maintenance/ views | FrmJobScheduler, FrmMeterReading | PARTIAL | |
| 46 | Channel Manager | ✅ ChannelController, channel.php, channelpushes | — | COMPLETE | |
| 47 | Online Reservation | ✅ frontend reservation views | — | PARTIAL | |
| 48 | Guest Portal | ✅ frontend/ | — | PARTIAL | |
| 49 | Room QR | ✅ QR printing (endroid/qr-code, test_final_qr.png) | — | COMPLETE | |
| 50 | Admin | ✅ SuperAdmin controllers, admin/ views | FrmControlPanel | COMPLETE | |
| 51 | User Management | ✅ User model, userpermission | — | COMPLETE | |
| 52 | Permissions | ✅ MenuHelp, userpermission, middleware | — | COMPLETE | |
| 53 | Backup | ⚠️ storage/app/backups/ dir only | — | PARTIAL | No automated backup; MySQL dump command documented |
| 54 | Tools | ✅ ToolsController (table mgmt, data tools, tickets) | FrmPOSRecycleData, FrmPOSSaleDataTransfer | COMPLETE | Table-management SQL verified safe (BUG-023) |
| 55 | API | ✅ 13 routes, ApiAuth | — | PARTIAL | No OpenAPI docs (BUG-019) |
| 56 | Integrations | ✅ channel, whatsapp, e-invoice | — | PARTIAL | |
| 57 | Notifications | ✅ SupportTicket notification polling, sound | — | COMPLETE | |
| 58 | WebSocket/Reverb | ✅ Reverb ^1.0, 2 events | — | PARTIAL | REVERB_APP_* keys empty in dev |
| 59 | Security | ✅ custom auth stack | — | COMPLETE | See SECURITY_AUDIT.md |
| 60 | System Configuration | ✅ Enviro* tables + parameter helpers | FrmSmsCenterSettings | COMPLETE | |

---

## Legacy-only modules — verified 2026-08-16

| Legacy form | Purpose | Laravel? | Classification |
|-------------|---------|----------|----------------|
| FrmLostFound | Lost & Found | ✅ HouseKeeping lostfound (CRUD+register+print), `lostfound` table | EXISTS |
| FrmDenomination | Cashier denomination | ❌ nothing — `DenominationDetail` table absent | MISSING (POS Reports) |
| FrmForExRec / FrmForeignExMast | Foreign exchange | ❌ nothing — no currency/`ForEx` table | MISSING |
| FrmMeterReading | Meter reading (maintenance) | ❌ nothing — maintenance has location (godown_mast) + assets only, no `FMReading` | MISSING |
| FrmGuestWakeUp | Guest wake-up call | ❌ nothing — static dashboard label only (GM-01, see GUEST_MANAGEMENT_GAPS) | MISSING |
| FrmPaxDetails | Pax details | ⚠️ embedded sub-form of FrmGuestWakeUp (not standalone); pax fields pervasive (kot.pax, roomocc adult/children, banquet noofpax) | OBSOLETE (not standalone) |
| FrmUnSettledBillsInfo | Unsettled bills info | ⚠️ pos_saledeletereport (del/unsettle), pendingkotreport, dashboard UnsettledRooms | REPLACED (partial) |
| FrmHotKey | Voucher-entry hotkey helper dialog | ⚠️ VoucherEntry UI supersedes (helper, not standalone module) | OBSOLETE |

> Verified by scanning routes + controllers + views + live DB (SHOW TABLES). Do NOT implement MISSING items without business confirmation.
