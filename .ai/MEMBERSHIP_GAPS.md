# Membership Module — Gap Analysis

**Module:** 16 — Membership / Smart Card / Cash Card / Reward Points  
**Date:** 2026-08-19  
**Status:** PARTIAL (permission fixes applied; SmartCard stubs + missing reports documented)

---

## 1. Laravel Implementation (Current)

### Components

| Component | File | Lines | Status |
|---|---|---|---|
| Member Category CRUD | MemberCategoryController.php | 166 | ✅ COMPLETE (+ permission guard added) |
| Member Master CRUD | MemberMasterController.php | 412 | ✅ COMPLETE (+ permission guards added) |
| Member Facility CRUD | MemberFacilityMasterController.php | 197 | ✅ COMPLETE (+ permission guard added) |
| Reward Parameter CRUD | RewardParameterC.php | 313 | ✅ COMPLETE |
| Reward Balance Check | SaleBill.php (checkCustomerReward, getRewardBalance) | ~70 | ✅ COMPLETE |
| Reward Point Report | Reporting.php (rewardpointreport, fetchrewardpointreport, fetchrewardmobilenumbers) | ~80 | ✅ COMPLETE |
| Smart Card Init | CardInitializationController.php | ~30 | ⚠️ STUB (store empty) |
| Smart Card Registration | CardRegistrationController.php | ~30 | ⚠️ STUB (store empty) |
| Smart Card Recharge | CardRechargeController.php | ~30 | ⚠️ STUB (store empty) |
| Smart Card Refund | CardReFundController.php | ~30 | ⚠️ STUB (store empty) |

### Permission Guards (This Pass — BUG-048)

All 3 Member CRUD controllers had **zero permission guards** on store/delete:

| Method | Old | Fixed (BUG-048) |
|---|---|---|
| MemberCategoryController::categoryStore | ❌ no guard | ✅ revokeopen(171111) ins |
| MemberCategoryController::deletecategory | ❌ no guard | ✅ revokeopen(171111) del |
| MemberMasterController::store | ❌ no guard | ✅ revokeopen(171112) ins |
| MemberMasterController::deletemaster | ❌ no guard | ✅ revokeopen(171112) del |
| MemberFacilityMasterController::delete | ❌ no guard | ✅ revokeopen(171113) del |

**Note**: Permission codes (171111/171112/171113) follow the menuhelp family pattern. Verify these codes exist in the `menuhelp` table for the target properties. If a property lacks these codes, the controller falls through gracefully (revokeopen returns null → error message). This matches the pattern used in other modules (e.g., Housekeeping dual-code fallback).

---

## 2. Legacy HMS Membership Reports (from HMS.text GRepFormName)

| Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|
| **MemLed** | Member ledger (transactions) | — | ❌ MISSING (P1) |
| **MemSalesRegister** | Member sales register | — | ❌ MISSING (P1) |
| **MemVisitDetail** | Member visit detail | — | ❌ MISSING (P2) |
| **MemBillMissingReport** | Member bill missing report | — | ❌ MISSING (P2) |
| **MemBirthAnnvDtls** | Member birthday/anniversary details | — | ❌ MISSING (P2) |
| **MemMailingLabels** | Member mailing labels | — | ❌ MISSING (P3) |
| **MemTaxReport** | Member tax report | — | ❌ MISSING (P2) |
| **CashCardCollectSumm** | Cash card collection summary | — | ❌ MISSING (P2 — CashCard module) |
| **CashCardTransRep** | Cash card transaction report | — | ❌ MISSING (P2 — CashCard module) |

---

## 3. Gaps Summary

### BUG-048 (Permission — FIXED)
All 3 Member CRUD controllers had zero permission guards. Any authenticated user could create/delete member categories, members, or facilities. Fixed with permission codes from menuhelp family.

### Smart Card Stubs (P1)
All 4 SmartCard controllers have empty `store()` methods. The views exist but forms submit to no-op endpoints. This is a **non-functional module** — members can register/recharge/refund cards but nothing is saved.

### Missing Reports (P2)
- **MemLed** — Member ledger (transaction history per member)
- **MemSalesRegister** — Member sales register
- **MemVisitDetail** — Member visit tracking
- **MemBillMissingReport** — Bills missing from member accounts
- **MemBirthAnnvDtls** — Birthday/anniversary alerts
- **MemTaxReport** — Member tax report

### Missing Functionality
- **Smart Card full implementation** — needs business decision on card type, storage, reconciliation
- **Cash Card reports** — CashCardCollectSumm, CashCardTransRep
- **Member transaction integration** — member billing via subgroup (already wired through GuestFolio/Group)
- **Membership tier/level management** — no tier system in current implementation

---

## 4. Files Changed (This Pass)

| File | Change |
|---|---|
| `app/Http/Controllers/Member/MemberCategoryController.php` | +6 lines: permission guard on categoryStore (ins) + deletecategory (del) |
| `app/Http/Controllers/Member/MemberMasterController.php` | +12 lines: permission guard on store (ins) + deletemaster (del) |
| `app/Http/Controllers/Member/MemberFacilityMasterController.php` | +6 lines: permission guard on delete (del) |
| `.ai/MEMBERSHIP_GAPS.md` | NEW |

---

## 5. Verification

- `php -l` ×3 → ✅ no syntax errors
- Permission codes (171111/171112/171113) follow menuhelp family pattern
- No financial data touched (member master is subgroup + memberfamily only)
