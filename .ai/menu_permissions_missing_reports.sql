-- ============================================================================
-- MENU PERMISSIONS — HMS.text MISSING REPORTS BATCH A (Front Office+Reservation)
-- Property: 103 | Grants to every user already holding generic reports code 131211
-- Precedent: meter reading code 122313 rollout (see .ai/MISSING_LOGIC.md ML-17)
-- Idempotent: safe to re-run (NOT EXISTS guard)
-- Date: 2026-08-23
-- ============================================================================
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT src.propertyid, src.compcode, src.username,
       FLOOR(n.code/10000), FLOOR(n.code/100)%100, n.code%100,
       n.code, n.route, n.module, n.module_name,
       src.view, 0, 0, 0, src.print, 'R', '', 'sa', NOW()
FROM (
  SELECT 131214 AS code, 'bookingdetail'       AS route, 'Booking Detail'        AS module, 'Front Office' AS module_name UNION ALL
  SELECT 131215, 'daysforecastrep',            'Days Forecast Report',           'Front Office' UNION ALL
  SELECT 131216, 'guestbilldetails',           'Guest Bill Details',             'Front Office' UNION ALL
  SELECT 131217, 'guestchgjournal',            'Guest Charge Journal',           'Front Office' UNION ALL
  SELECT 131218, 'guestchgjournallog',         'Guest Charge Journal Log',       'Front Office' UNION ALL
  SELECT 131219, 'guestobservrep',             'Guest Observation Report',       'Front Office' UNION ALL
  SELECT 131220, 'inhousecount',               'Instant House Count',            'Front Office' UNION ALL
  SELECT 131221, 'guestinhousereport',         'Guest In House',                 'Front Office' UNION ALL
  SELECT 131222, 'delbillunsetbill',           'Deleted/Unsettled Bills',        'Front Office' UNION ALL
  SELECT 131223, 'resvadvrecd',                'Reservation Advance Received',   'Reservation'  UNION ALL
  SELECT 131224, 'resvadvrecdarr',             'Advance Received - Arrivals',    'Reservation'  UNION ALL
  SELECT 131225, 'resvadvrecdinhouse',         'Advance Received - In-House',    'Reservation'
) n
CROSS JOIN (
  SELECT mh.propertyid, mh.compcode, mh.username, mh.view, mh.print
  FROM menuhelp mh
  WHERE mh.propertyid = 103 AND mh.code = 131211
) src
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x
  WHERE x.propertyid = 103 AND x.username = src.username AND x.code = n.code
);

-- ============================================================================
-- BATCH B (ACCOUNTS) - codes 131226-131230 | Applied 2026-08-23 via script
-- ============================================================================
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT src.propertyid, src.compcode, src.username,
       FLOOR(n.code/10000), FLOOR(n.code/100)%100, n.code%100,
       n.code, n.route, n.module, n.module_name,
       src.view, 0, 0, 0, src.print, 'R', '', 'sa', NOW()
FROM (
  SELECT 131226 AS code, 'bankreg'              AS route, 'Bank Register'               AS module, 'Accounts' AS module_name UNION ALL
  SELECT 131227, 'ledgercred',           'Ledger - Creditors/Parties',   'Accounts' UNION ALL
  SELECT 131228, 'controlledaccounts',   'Controlled Accounts',          'Accounts' UNION ALL
  SELECT 131229, 'partywiseoutstanding', 'Party-wise Outstanding',       'Accounts' UNION ALL
  SELECT 131230, 'pmtbycashier',         'Payments by Cashier',          'Accounts'
) n
CROSS JOIN (
  SELECT mh.propertyid, mh.compcode, mh.username, mh.view, mh.print
  FROM menuhelp mh
  WHERE mh.propertyid = 103 AND mh.code = 131211
) src
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x
  WHERE x.propertyid = 103 AND x.username = src.username AND x.code = n.code
);

-- ============================================================================
-- BATCH C (2026-08-24) — codes referenced by controllers but granted to NOBODY
-- on property 103 (audit: revokeopen() sites vs menuhelp grants).
-- Precedent: Batch A/B above; flags mirror live sibling rows on prop 104/147.
-- Idempotent: safe to re-run (NOT EXISTS guard). Grants property 103 only.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- C1. 172315 "Table Change Entry" (Pointofsale: salebillsettle/possalebillsettle/
-- tablechangesubmit guards). PK is (propertyid,compcode,username,opt1,opt2,opt3,code)
-- so ONE row per user; route targets the user's home outlet (from their own
-- 172111 row) falling back to RES103.
-- Users: everyone already holding a POS bill-family code (172011-172220).
-- Flags copied from the user's own 172111 row (closest sibling), else default.
-- ----------------------------------------------------------------------------
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', s.username,
       17, 23, 15, 172315, CONCAT('tablechangeentry?dcode=', s.dcode),
       'Table Change Entry', 'Pointofsale',
       1, COALESCE(sib.ins,1), COALESCE(sib.edit,1),
       COALESCE(sib.del,0), COALESCE(sib.print,1), 'E', s.dcode, 'sa', NOW()
FROM (
  SELECT t.username,
         CASE WHEN COUNT(o.dcode) > 0 THEN MIN(t.home_dcode) ELSE 'RES103' END AS dcode
  FROM (
    SELECT mh.username,
           SUBSTRING_INDEX(SUBSTRING_INDEX(COALESCE(s2.route,''),'dcode=',-1),'?',1) AS home_dcode
    FROM menuhelp mh
    LEFT JOIN menuhelp s2
           ON s2.propertyid='103' AND s2.username=mh.username AND s2.code=172111
    WHERE mh.propertyid='103' AND mh.code BETWEEN 172011 AND 172220
  ) t
  LEFT JOIN depart o
         ON o.propertyid='103' AND o.nature='Outlet' AND o.dcode=t.home_dcode
  GROUP BY t.username
) s
LEFT JOIN (
  SELECT username, MAX(view) AS view, MAX(ins) AS ins, MAX(edit) AS edit,
         MAX(del) AS del, MAX(print) AS print
  FROM menuhelp
  WHERE propertyid='103' AND code=172111
  GROUP BY username
) sib ON sib.username = s.username
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x
  WHERE x.propertyid='103' AND x.username=s.username AND x.code=172315
);

-- ----------------------------------------------------------------------------
-- C2. Membership masters (BUG-048 guard codes had zero rows anywhere):
--   171111 Member Category (/membercategory)
--   171112 Member Master   (/membermaster)
--   171113 Member Facility Master (/memberfacility)
-- Subheader 171100 makes them visible under Point Of Sale for the grantees.
-- Users: sa, ADMIN, ADMIN1.
-- ----------------------------------------------------------------------------
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', u.username, 17, 11, 0, 171100, 'javascript:void()',
       'Membership', 'Pointofsale', 1, 0, 0, 0, 0, 'N', '', 'sa', NOW()
FROM (SELECT 'sa' username UNION ALL SELECT 'ADMIN' UNION ALL SELECT 'ADMIN1') u
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x WHERE x.propertyid='103' AND x.username=u.username AND x.code=171100
);

INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', u.username, 17, 11, n.opt3, n.code, n.route, n.module, 'Pointofsale',
       1, 1, 1, 1, 0, 'R', '', 'sa', NOW()
FROM (
  SELECT 171111 AS code, 1 AS opt3, 'membercategory' AS route, 'Member Category' AS module UNION ALL
  SELECT 171112, 2, 'membermaster', 'Member Master' UNION ALL
  SELECT 171113, 3, 'memberfacility', 'Member Facility Master'
) n
CROSS JOIN (SELECT 'sa' username UNION ALL SELECT 'ADMIN' UNION ALL SELECT 'ADMIN1') u
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x WHERE x.propertyid='103' AND x.username=u.username AND x.code=n.code
);

-- ----------------------------------------------------------------------------
-- C3. 141611 Banquet billing/delete actions (deletebanquet, deleteadvancebanquet,
-- performaInvoiceSubmit, deletebanquetbill, banquetbillsubmit).
-- Action code only — leaf rows stay invisible (no 141600 subheader anywhere).
-- Users: sa, ADMIN, ADMIN1.
-- ----------------------------------------------------------------------------
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', u.username, 14, 16, 11, 141611, '', 'Banquet Billing Actions',
       'Frontoffice', 1, 1, 1, 1, 0, 'R', '', 'sa', NOW()
FROM (SELECT 'sa' username UNION ALL SELECT 'ADMIN' UNION ALL SELECT 'ADMIN1') u
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x WHERE x.propertyid='103' AND x.username=u.username AND x.code=141611
);

-- ----------------------------------------------------------------------------
-- C4. 201111 Admin actions (MainController setup writes, ToolsController
-- deletedate/resetOutletData/deletetablerecord/deletemultiplerecords,
-- Hrpayrolls updateEmployee/deleteEmployee).
-- Action code only — invisible (no subheader row created).
-- ----------------------------------------------------------------------------
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', u.username, 20, 1, 11, 201111, '', 'Admin Actions',
       'Adminsetup', 1, 1, 1, 1, 0, 'R', '', 'sa', NOW()
FROM (SELECT 'sa' username UNION ALL SELECT 'ADMIN' UNION ALL SELECT 'ADMIN1') u
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x WHERE x.propertyid='103' AND x.username=u.username AND x.code=201111
);

-- ----------------------------------------------------------------------------
-- C5. 998765 Housekeeping Status Report (/housekeepingstatusreport).
-- Users: sa, ADMIN, ADMIN1, HOUSKEEPING. View+print only.
-- ----------------------------------------------------------------------------
INSERT INTO menuhelp
  (propertyid, compcode, username, opt1, opt2, opt3, code, route, module, module_name,
   view, ins, edit, del, print, flag, outletcode, u_name, u_entdt)
SELECT '103', 'ANA003', u.username, 99, 87, 65, 998765, 'housekeepingstatusreport',
       'Housekeeping Status Report', 'Housekeeping', 1, 0, 0, 0, 1, 'R', '', 'sa', NOW()
FROM (SELECT 'sa' username UNION ALL SELECT 'ADMIN' UNION ALL SELECT 'ADMIN1' UNION ALL SELECT 'HOUSKEEPING') u
WHERE NOT EXISTS (
  SELECT 1 FROM menuhelp x WHERE x.propertyid='103' AND x.username=u.username AND x.code=998765
);
