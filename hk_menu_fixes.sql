-- ════════════════════════════════════════════════════════════════════
-- Housekeeping module — MENU (menuhelp) fixes  ·  BUG-HK-006
-- Property 103 (Analysis HMS)
--
-- Run against the LIVE database (and any other property DBs with the
-- same structure) after deploying the code fixes.
-- ════════════════════════════════════════════════════════════════════

-- 1) Fix "House Keepar" typo → "Housekeeper" (all users)
UPDATE menuhelp
   SET module = 'Housekeeper Master'
 WHERE propertyid = 103
   AND module = 'House Keepar Master';

-- 2) Fix duplicate menu code: Amenities Report shares code 151213 with
--    Assignment Report → give it 151214 (all users that have both)
UPDATE menuhelp
   SET code = 151214, opt3 = 14
 WHERE propertyid = 103
   AND route = 'amenitiesreport'
   AND code  = 151213;

-- 3) Add missing menu entries (Lost & Found Register, Laundry Send,
--    Laundry Receive) for user 'sa'. For other users, copy this pattern:
--    run the same INSERT with their username, or extend via the
--    INSERT ... SELECT template at the bottom.

INSERT INTO menuhelp
    (propertyid, compcode, username, opt1, opt2, opt3, code, route,
     module, module_name, view, ins, edit, del, print, flag, outletcode,
     u_name, u_entdt)
VALUES
    -- Lost & Found Register → House Keeping ▸ Reports
    (103, 'ANA003', 'sa', 15, 12, 17, 151217, 'lostfoundregister',
     'Lost & Found Register', 'Housekeeping', 1, 1, 1, 1, 1, 'R', '',
     'sa', NOW()),
    -- Laundry Send → House Keeping ▸ LAUNDRY
    (103, 'ANA003', 'sa', 15, 14, 14, 151414, 'laundrysend',
     'Laundry Send', 'Housekeeping', 1, 1, 1, 1, 1, 'E', '',
     'sa', NOW()),
    -- Laundry Receive → House Keeping ▸ LAUNDRY
    (103, 'ANA003', 'sa', 15, 14, 15, 151415, 'laundryreceive',
     'Laundry Receive', 'Housekeeping', 1, 1, 1, 1, 1, 'E', '',
     'sa', NOW());

-- ────────────────────────────────────────────────────────────────────
-- Template: add the 3 missing entries for ANY OTHER user who already
-- has the House Keeping menu group (opt1 = 15). Replace <USERNAME>.
-- ────────────────────────────────────────────────────────────────────
-- INSERT INTO menuhelp
--     (propertyid, compcode, username, opt1, opt2, opt3, code, route,
--      module, module_name, view, ins, edit, del, print, flag, outletcode,
--      u_name, u_entdt)
-- SELECT propertyid, compcode, '<USERNAME>', 15, 12, 17, 151217, 'lostfoundregister',
--        'Lost & Found Register', 'Housekeeping', 1, 1, 1, 1, 1, 'R', '',
--        '<USERNAME>', NOW()
--   FROM menuhelp
--  WHERE propertyid = 103 AND username = '<USERNAME>' AND opt1 = 15 AND opt2 = 0
--  LIMIT 1;
--
-- INSERT INTO menuhelp
--     (propertyid, compcode, username, opt1, opt2, opt3, code, route,
--      module, module_name, view, ins, edit, del, print, flag, outletcode,
--      u_name, u_entdt)
-- SELECT propertyid, compcode, '<USERNAME>', 15, 14, 14, 151414, 'laundrysend',
--        'Laundry Send', 'Housekeeping', 1, 1, 1, 1, 1, 'E', '',
--        '<USERNAME>', NOW()
--   FROM menuhelp
--  WHERE propertyid = 103 AND username = '<USERNAME>' AND opt1 = 15 AND opt2 = 0
--  LIMIT 1;
--
-- INSERT INTO menuhelp
--     (propertyid, compcode, username, opt1, opt2, opt3, code, route,
--      module, module_name, view, ins, edit, del, print, flag, outletcode,
--      u_name, u_entdt)
-- SELECT propertyid, compcode, '<USERNAME>', 15, 14, 15, 151415, 'laundryreceive',
--        'Laundry Receive', 'Housekeeping', 1, 1, 1, 1, 1, 'E', '',
--        '<USERNAME>', NOW()
--   FROM menuhelp
--  WHERE propertyid = 103 AND username = '<USERNAME>' AND opt1 = 15 AND opt2 = 0
--  LIMIT 1;
