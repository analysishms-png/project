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
