# PURCHASE MODULE — GAP ANALYSIS (Laravel vs Legacy HMS)

**DATE:** 2026-08-16
**SCOPE:** Purchase Order (PO) flow, PO↔Indent linkage, PO↔MR (purchase bill) consumption markers, purchase reports.
**METHOD:** Live Laravel source trace + legacy HMS.bas/HMS.text comparison + live-DB verification (read-only).

---

## 1. Lifecycle Trace (Laravel)

```
Indent (requisition, pending: refdocId='')
   │  purchaseorder  → pendingindentitems (refdocId='' AND delflag='N')
   ▼
PurchaseOrder (porder)  ── purchaseordersubmit ── sets Indent.refdocId = PO docid
   │
   │  MR Entry (mrentrysubmit) → selectedpos → porder.mrcontradocId = MR docid, mrsno = MR vno
   ▼
Purchase Bill / MR (purch1/purch2/sale2/stock/suntran/ledger)
   │
   │  deletepurchbill / mrentryupdate / purchasebillupdate (delete+re-post)
   ▼
PO consumption marker should be released → PO back to pending (pendingpo: mrcontradocId IS NULL)
```

## 2. Verified Defects FIXED this pass

### BUG-040 (HIGH, authorization)
- `deletepurchaseorder` had **NO `revokeopen(161114)` permission guard** — every other PO method (list, update-open, update-submit) is guarded. Any authenticated user could delete POs (and their items) via direct GET.
- **FIX:** Guard added (`edit` permission, same as update flows). PO-not-found now returns a clean error instead of silently succeeding.

### BUG-041 (HIGH, orphan linkage)
- `deletepurchaseorder` deleted the PO **without resetting `Indent.refdocId`** — the indent stayed permanently linked to a deleted PO docid and could never be re-PO'd (`pendingindentitems` requires `refdocId=''`).
- **FIX:** PO delete now runs in a transaction: releases `Indent.refdocId=''` for linked indents, then deletes PO + items. (Matches legacy HMS re-open: `Update Indent Set ClearYN='' Where DocId In (Select distinct Contradocid From Stock Where DocID=...)`.)

### BUG-042 (HIGH, orphan consumption marker)
- `deletepurchbill` (MR delete) **never released `porder.mrcontradocId/mrsno`** — a PO stayed "consumed" forever after its MR was deleted, so `pendingpo` (which filters `mrcontradocId IS NULL`) never offered it again.
- `mrentryupdate` (edit re-post) re-linked POs from `selectedpos` but never released deselected POs — deselected POs stayed consumed forever.
- **FIX:** `deletepurchbill` releases linked POs before returning; `mrentryupdate` releases all POs previously linked to the MR docid **before** re-linking from `selectedpos` (release-then-relink).

## 3. Safety guards added

- **Consumed-PO deletion blocked:** `deletepurchaseorder` refuses to delete a PO with `mrcontradocId`/`mrsno` set — the MR references it. User must delete the MR first. (Same class as Banquet "Bill Submitted can not update".)

## 4. NOT changed (verified safe / out of scope)

- `purchaseordersubmit` numbering (`VoucherPrefix` increment) — unchanged, project convention.
- `purchaseorderupdate` re-post (delete items + re-insert) — non-financial (porder only, no ledger/suntran/stock). Consistent with KOT/Stock delete class.
- PO print, pending-PO reports (`pendingpurchaseorder`, `purchaseregister`, `purchasesummary`, `supplierwisepurchase`) — all exist.
- `purchasebillupdate` re-post to same docid — linkage persists correctly (no release needed).
- Legacy `FrmPurch` = combined purchase bill entry; Laravel's separate PO module is a **superset** (legacy has no standalone PO form — Laravel improved).

## 5. REPORT PARITY

| Legacy | Laravel | Status |
|---|---|---|
| (no standalone PO form) | Purchase Order entry/update/print | IMPROVED |
| PurchReg / Purchase Register | `purchaseregister` | EXISTS |
| Purchase Summary | `purchasesummary` | EXISTS |
| Pending Purchase Order | `pendingpurchaseorder` | EXISTS |
| Supplier-wise Purchase | `supplierwisepurchase` | EXISTS |
| Pending Indent (PIndent → IndentReg) | `pendingindentitems` (PO screen) + `pendingmr` | EXISTS |

## 6. LIVE-DB FINDINGS (read-only, 2026-08-16)

- Total POs: 17.
- **6 orphaned POs on property 103** — `porder.mrcontradocId` points to MR docids that exist in **neither** `purch1` **nor** `gin` (verified with U+200E/space normalization): `103PO 2025 5`, `103PO 2026 2/4/9/10/11` → dangling `103MRCR 2025 5`, `103MRCR 2026 1/2/5/6`, `103MRCH 2026 4`. These POs are permanently stuck "consumed" in the UI (pendingpo never shows them).
- 2 POs (property 126, 177) link to MRs that exist in `gin` (delflag N) — valid, not orphaned.
- **ACTION: PENDING USER APPROVAL** — releasing the 6 orphaned `mrcontradocId` markers back to NULL is a 6-row data repair. Financial safety: the MRs are gone entirely (no ledger/stock trace found), so release is likely safe, but a PO may represent a genuinely-completed past purchase — release could enable double-ordering. **Held per mission rule (financial-adjacent data mutation).**

## 7. Next steps

- [ ] P0-awaiting-approval: release 6 orphaned PO consumption markers (property 103) after confirmation.
- [ ] P0-awaiting-approval (existing): Res 49 advance restore ₹1,300; ADRES docid-collision report; msno1 repair `109CHK|2026|152`.
- [ ] P1: legacy-only module verification (WakeUp, Denomination, ForEx).
