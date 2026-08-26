-- ═══════════════════════════════════════════════════════════════════════════
-- HMS SERIAL KEY / EXPIRY MECHANISM — COMPLETE GUIDE + TEST QUERIES
-- ═══════════════════════════════════════════════════════════════════════════
-- Date: 26 August 2026
-- Project: AnalysisHMS (Hotel Management System)
-- ═══════════════════════════════════════════════════════════════════════════


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 1: SAMAJHIYE — YE SYSTEM KAISE KAAM KARTA HAI              ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- HMS mein har property (hotel) ka ek "expiry date" hota hai.
-- Ye expiry date batata hai ki us property ka software kab tak chalega.
--
-- Kaise kaam karta hai:
--
--   STEP 1: Vendor (software wala) admin panel mein jaata hai
--           Route: /expirymodule
--           Wahan property select karta hai aur expiry date + amount set karta hai
--
--   STEP 2: System expiry date ko ENCRYPT karta hai (AES-256-CBC)
--           Plaintext "2026-09-01" → Crypt::encryptString() → "eyJpdiI6Imx..."
--           Ye encrypted text MySQL mein TEXT column mein store hota hai
--
--   STEP 3: Jab user login karta hai to system:
--           a) enviro_general table se expdate (encrypted) padhta hai
--           b) Use DECRYPT karta hai: Crypt::decryptString() → "2026-09-01"
--           c) Property ka software date (ncur) padhta hai: "2026-08-05"
--           d) Compare karta hai: agar expdate < ncur → LOGIN BLOCKED
--
--   STEP 4: Agar property 103 (demo) hai → EXEMPT (kabhi block nahi hota)
--
-- IMPORTANT: expdate server ke date se nahi, property ke "software date"
-- (ncur) se compare hota hai. Ncur date night audit se advance hoti hai.
-- Isliye agar hotel ka ncur 2025-09-25 hai aur expdate 2025-09-27 hai,
-- to login hoga. Lekin jab ncur 2025-09-28 ho jayega (night audit ke baad),
-- to login blocked ho jayega.


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 2: TABLE STRUCTURE                                           ║
-- ╚═════════════════════════════════════════════════════════════════════════╝

-- Main table: enviro_general (har property ki ek row)
-- Columns jo important hain:

-- SHOW COLUMNS FROM enviro_general;
-- ┌──────────────┬──────────┬──────────────────────────────────────────┐
-- │ Field        │ Type     │ Purpose                                  │
-- ├──────────────┼──────────┼──────────────────────────────────────────┤
-- │ propertyid   │ int      │ Hotel/property ka unique ID              │
-- │ ncur         │ date     │ Software date (night audit se advance)   │
-- │ expdate      │ text     │ Encrypted expiry date (AES-256-CBC)      │
-- │ amount       │ text     │ Encrypted license fee amount             │
-- │ autonightaudit│ char(1) │ 'Y' = auto night audit enabled           │
-- └──────────────┴──────────┴──────────────────────────────────────────┘
--
-- expdate aur amount TEXT type ke hain kyunki ye encrypted hain.
-- Laravel ka Crypt::encryptString() base64 encoded ciphertext deta hai,
-- jo 200+ characters lamba hota hai, isliye TEXT column chahiye.


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 3: TEST QUERIES — PROPERTY KA EXPIRY STATUS CHECK           ║
-- ╚═════════════════════════════════════════════════════════════════════════╝

-- ── Query 1: Sabhi properties ka expiry status dekho ─────────────────────
-- Ye dikhata hai kaunsi property expire ho chuki hai, kaunsi active hai

SELECT
    eg.propertyid,
    c.comp_name,
    eg.ncur                              AS software_date,
    LENGTH(eg.expdate)                   AS expdate_is_encrypted,
    eg.expdate IS NOT NULL               AS has_expiry,
    CASE
        WHEN eg.propertyid = 103 THEN 'EXEMPT (Demo)'
        WHEN eg.expdate IS NULL THEN 'NO EXPIRY SET'
        ELSE 'CHECK NEEDED'
    END                                  AS status
FROM enviro_general eg
LEFT JOIN company c ON c.propertyid = eg.propertyid
ORDER BY eg.propertyid;


-- ── Query 2: Kaunsi properties already expire ho chuki hain? ─────────────
-- NOTE: Ye query encrypted expdate nahi decode kar sakti (SQL mein nahi,
-- Laravel PHP mein decrypt hota hai). Sirf structure dikhata hai.

SELECT
    eg.propertyid,
    c.comp_name,
    eg.ncur,
    CASE
        WHEN eg.propertyid = 103 THEN 'ALWAYS ACTIVE (Demo)'
        WHEN eg.expdate IS NULL THEN 'NO EXPIRY — NEVER BLOCKED'
        ELSE 'ENCRYPTED — CHECK IN PHP'
    END AS expiry_status,
    CASE
        WHEN eg.amount IS NOT NULL THEN CONCAT('₹', LENGTH(eg.amount), ' chars encrypted')
        ELSE 'NO AMOUNT SET'
    END AS license_amount
FROM enviro_general eg
LEFT JOIN company c ON c.propertyid = eg.propertyid
WHERE eg.propertyid != 103
ORDER BY eg.propertyid;


-- ── Query 3: expdate ki value kitni lambi hai (encryption verify) ─────────
-- Agar expdate 20 chars se chhota hai to shayad plaintext hai (galat)

SELECT
    propertyid,
    CASE
        WHEN expdate IS NULL THEN 'NULL (no expiry)'
        WHEN LENGTH(expdate) < 20 THEN 'SHORT — likely plaintext (BAD!)'
        WHEN LENGTH(expdate) BETWEEN 50 AND 300 THEN 'OK — looks encrypted'
        ELSE 'VERY LONG — check encoding'
    END AS expdate_check,
    CASE
        WHEN amount IS NULL THEN 'NULL'
        WHEN LENGTH(amount) < 10 THEN 'SHORT — likely plaintext'
        WHEN LENGTH(amount) BETWEEN 20 AND 300 THEN 'OK — encrypted'
        ELSE 'VERY LONG'
    END AS amount_check,
    LENGTH(expdate) AS expdate_len,
    LENGTH(amount) AS amount_len
FROM enviro_general
WHERE propertyid != 103;


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 4: PHP SE DECRYPT KARKE TEST KARO (Artisan Tinker)          ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- Ye queries MySQL mein nahi, PHP artisan tinker mein chalani hain.
-- Copy-paste karo `php artisan tinker` mein:
--
-- ═══════════════════════════════════════════════════════════════════════
-- TEST A: Kisi bhi property ka expiry date decrypt karke dikhao
-- ═══════════════════════════════════════════════════════════════════════
--
-- $rows = DB::table('enviro_general')->where('propertyid', '!=', '103')->get();
-- foreach ($rows as $r) {
--     $exp = $r->expdate ? Crypt::decryptString($r->expdate) : 'NO EXPIRY';
--     $amt = $r->amount ? Crypt::decryptString($r->amount) : 'N/A';
--     echo "Prop {$r->propertyid}: ncur={$r->ncur} | expdate={$exp} | amount=₹{$amt}\n";
-- }
--
-- ═══════════════════════════════════════════════════════════════════════
-- TEST B: Login jaisa check karo — expdate < ncur hai ya nahi?
-- ═══════════════════════════════════════════════════════════════════════
--
-- $rows = DB::table('enviro_general')->where('propertyid', '!=', '103')
--         ->whereNotNull('expdate')->get();
-- foreach ($rows as $r) {
--     $expdate = Crypt::decryptString($r->expdate);
--     $ncur = $r->ncur;
--     $blocked = ($expdate < $ncur) ? 'BLOCKED ❌' : 'ACTIVE ✅';
--     echo "Prop {$r->propertyid}: ncur={$ncur} | expdate={$expdate} | {$blocked}\n";
-- }
--
-- ═══════════════════════════════════════════════════════════════════════
-- TEST C: Property 103 (demo) ka exemption verify karo
-- ═══════════════════════════════════════════════════════════════════════
--
-- $p103 = DB::table('enviro_general')->where('propertyid', '103')->first();
-- echo "Property 103 expdate: " . ($p103->expdate ? 'ENCRYPTED (but exempt)' : 'NULL') . "\n";
-- echo "Property 103 is exempt from expiry: YES (hardcoded in LoginController)\n";


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 5: EXPIRY BANANA / UPDATE KARNA                              ║
-- ╚═════════════════════════════════════════════════════════════════════════╝

-- ── Query 4: Naya expiry set karo (PHP tinker mein chalao) ───────────────
--
-- ═══════════════════════════════════════════════════════════════════════
-- PROPERTY KA EXPIRY DATE BADLO:
-- ═══════════════════════════════════════════════════════════════════════
--
-- // Kisi property ka expiry 6 mahine badhao:
-- $propertyid = 106;
-- $newExpDate = '2027-03-31';
-- $newAmount = '11800.00';
--
-- DB::table('enviro_general')
--     ->where('propertyid', $propertyid)
--     ->update([
--         'expdate' => Crypt::encryptString($newExpDate),
--         'amount'  => Crypt::encryptString($newAmount),
--     ]);
--
-- echo "Property {$propertyid} expiry updated to {$newExpDate}\n";
--
-- ═══════════════════════════════════════════════════════════════════════
-- SABHI EXPIRED PROPERTIES KA EXPIRY 1 SAAL BADHAO:
-- ═══════════════════════════════════════════════════════════════════════
--
-- $rows = DB::table('enviro_general')
--     ->where('propertyid', '!=', '103')
--     ->whereNotNull('expdate')
--     ->get();
--
-- foreach ($rows as $r) {
--     $oldExp = Crypt::decryptString($r->expdate);
--     if ($oldExp < $r->ncur) {
--         $newExp = date('Y-m-d', strtotime($oldExp . ' +1 year'));
--         DB::table('enviro_general')
--             ->where('propertyid', $r->propertyid)
--             ->update(['expdate' => Crypt::encryptString($newExp)]);
--         echo "Prop {$r->propertyid}: {$oldExp} → {$newExp}\n";
--     }
-- }


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 6: NCUR DATE KA ROLE                                        ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- ncur = "Night Current Date" — property ka software date
-- Ye date night audit se 1 din advance hoti hai
-- Expiry check: expdate < ncur → BLOCK
--
-- Matlab: agar expdate = '2026-09-01' hai aur ncur = '2026-08-31' hai,
-- to login hoga (2026-09-01 > 2026-08-31). Lekin jab night audit
-- chalega aur ncur = '2026-09-01' ho jayega, tab login blocked.

-- ── Query 5: Sabhi properties ki ncur date dikhao ────────────────────────

SELECT
    eg.propertyid,
    c.comp_name,
    eg.ncur AS software_date,
    DATEDIFF(eg.ncur, CURDATE()) AS days_behind_real,
    CASE
        WHEN DATEDIFF(eg.ncur, CURDATE()) < -30 THEN 'DANGER — very stale ncur'
        WHEN DATEDIFF(eg.ncur, CURDATE()) < 0 THEN 'WARNING — ncur behind real date'
        WHEN DATEDIFF(eg.ncur, CURDATE()) = 0 THEN 'OK — matches today'
        ELSE 'OK — ahead (night audit done early)'
    END AS ncur_status
FROM enviro_general eg
LEFT JOIN company c ON c.propertyid = eg.propertyid
ORDER BY eg.propertyid;


-- ── Query 6: Kya koi property ka ncur expdate se aage hai? ──────────────
-- (Sirf structure — actual comparison PHP mein hota hai)

SELECT
    eg.propertyid,
    c.comp_name,
    eg.ncur,
    CASE
        WHEN eg.propertyid = 103 THEN 'EXEMPT'
        WHEN eg.expdate IS NULL THEN 'NO EXPIRY SET'
        ELSE 'NEEDS PHP DECRYPT'
    END AS login_check
FROM enviro_general eg
LEFT JOIN company c ON c.propertyid = eg.propertyid
WHERE eg.propertyid != 103;


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 7: UPGRADE SCRIPT (UpgradingExpCrypt)                       ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- Jab system pehli baar deploy hua, expdate PLAINTEXT mein tha.
-- Upgrade script ne use encrypt kiya.
-- Agar kisi property ka expdate ABHI BHI plaintext hai (chhota text),
-- to use encrypt karna padega.
--
-- Route: GET /cryptoencryption (commented out — one-time use)

-- ── Query 7: Check karo kaunsi properties ka expdate plaintext hai ───────

SELECT
    propertyid,
    CASE
        WHEN expdate IS NULL THEN 'NULL'
        WHEN LENGTH(expdate) BETWEEN 8 AND 10 AND expdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
            THEN 'PLAINTEXT — NEEDS ENCRYPTION!'
        WHEN LENGTH(expdate) > 30 THEN 'ENCRYPTED — OK'
        ELSE 'UNKNOWN FORMAT'
    END AS expdate_format,
    CASE
        WHEN amount IS NULL THEN 'NULL'
        WHEN LENGTH(amount) BETWEEN 1 AND 15 AND amount REGEXP '^[0-9.]+$'
            THEN 'PLAINTEXT — NEEDS ENCRYPTION!'
        WHEN LENGTH(amount) > 20 THEN 'ENCRYPTED — OK'
        ELSE 'UNKNOWN FORMAT'
    END AS amount_format,
    expdate AS raw_value,
    amount AS raw_amount
FROM enviro_general
WHERE propertyid != 103;


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 8: SECURITY AUDIT QUERIES                                   ║
-- ╚═════════════════════════════════════════════════════════════════════════╝

-- ── Query 8: Kya kisi property ka expdate NULL hai jo hona nahi chahiye? ─

SELECT
    eg.propertyid,
    c.comp_name,
    eg.ncur,
    eg.expdate IS NULL AS no_expiry_set
FROM enviro_general eg
LEFT JOIN company c ON c.propertyid = eg.propertyid
WHERE eg.expdate IS NULL
    AND eg.propertyid != 103
ORDER BY eg.propertyid;

-- Result: Agar koi property NULL expdate hai aur vo demo nahi hai,
-- to uska login kabhi block nahi hoga (no expiry enforcement).


-- ── Query 9: Kya APP_KEY set hai? (Encryption ka key) ────────────────────
-- Ye query MySQL mein nahi, PHP mein chalao:

-- php artisan tinker:
-- echo config('app.key') ? 'APP_KEY SET — encryption works' : 'APP_KEY MISSING — DANGER!';
-- echo PHP_EOL . 'Cipher: ' . config('app.cipher');


-- ── Query 10: enviro_general mein koi or important columns hain? ─────────

SHOW COLUMNS FROM enviro_general;


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 9: COMPLETE WORKFLOW DIAGRAM (TEXT)                         ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- ┌──────────────────────────────────────────────────────────────┐
-- │                    VENDOR WORKFLOW                           │
-- │                                                              │
-- │  1. Admin panel kholo: /expirymodule                         │
-- │  2. Property select karo (dropdown)                          │
-- │  3. Expiry date set karo: 2027-03-31                         │
-- │  4. Amount set karo: 11800.00                                │
-- │  5. Click "Update"                                           │
-- │                                                              │
-- │  Backend:                                                    │
-- │  ┌────────────────────────────────────────────────────┐      │
-- │  │ Crypt::encryptString("2027-03-31")                 │      │
-- │  │ → "eyJpdiI6ImxuYzBkTGxGZG1QZ0x..."              │      │
-- │  │                                                    │      │
-- │  │ UPDATE enviro_general                              │      │
-- │  │ SET expdate = 'eyJpdiI6ImxuYzBk...',              │      │
-- │  │     amount  = 'eyJpdiI6ImxuYzBk...'               │      │
-- │  │ WHERE propertyid = 106;                            │      │
-- │  └────────────────────────────────────────────────────┘      │
-- └──────────────────────────────────────────────────────────────┘
--
-- ┌──────────────────────────────────────────────────────────────┐
-- │                    LOGIN WORKFLOW                            │
-- │                                                              │
-- │  User: username/password submit karta hai                     │
-- │                                                              │
-- │  LoginController::login() mein:                              │
-- │  ┌────────────────────────────────────────────────────┐      │
-- │  │ Step 1: user dhundho database mein                 │      │
-- │  │ Step 2: enviro_general padho (expdate, ncur)       │      │
-- │  │ Step 3: CHECK                                      │      │
-- │  │   IF propertyid = 103 → SKIP (exempt)              │      │
-- │  │   IF expdate IS NULL → SKIP (no expiry set)        │      │
-- │  │   ELSE:                                            │      │
-- │  │     $decrypted = Crypt::decryptString(expdate)     │      │
-- │  │     IF $decrypted < ncur:                          │      │
-- │  │       → BLOCK: "Account expired"                   │      │
-- │  │     ELSE:                                          │      │
-- │  │       → PROCEED to password check                  │      │
-- │  │ Step 4: Auth::attempt($credentials)                │      │
-- │  └────────────────────────────────────────────────────┘      │
-- └──────────────────────────────────────────────────────────────┘
--
-- ┌──────────────────────────────────────────────────────────────┐
-- │                    NIGHT AUDIT WORKFLOW                      │
-- │                                                              │
-- │  Har din raat ko night audit chalta hai:                     │
-- │                                                              │
-- │  UPDATE enviro_general                                      │
-- │  SET ncur = DATE_ADD(ncur, INTERVAL 1 DAY)                  │
-- │  WHERE propertyid = ?;                                      │
-- │                                                              │
-- │  Iske baad:                                                  │
-- │  - ncur 1 din aage badh jaata hai                           │
-- │  - Agar ncur > expdate → NEXT LOGIN BLOCKED                 │
-- │  - Ye actual "expiry trigger" hai                            │
-- └──────────────────────────────────────────────────────────────┘


-- ╔═════════════════════════════════════════════════════════════════════════╗
-- ║  SECTION 10: QUICK REFERENCE — KAUN SI QUERY KIS KAAM KE LIYE        ║
-- ╚═════════════════════════════════════════════════════════════════════════╝
--
-- Query 1  → Sabhi properties ka expiry status overview
-- Query 2  → Kaunsi properties expire ho chuki hain (structure)
-- Query 3  → Verify karo ki expdate encrypted hai ya plaintext
-- Query 4  → (PHP tinker) Decrypt karke dikhao sabhi expiry dates
-- Query 5  → Sabhi properties ki ncur date aur real date ka gap
-- Query 6  → Login check status (structure)
-- Query 7  → Plaintext expdate/amount detect karo (security audit)
-- Query 8  → NULL expdate wali properties (exemption check)
-- Query 9  → (PHP tinker) APP_KEY aur cipher verify
-- Query 10 → enviro_general table ka complete structure
