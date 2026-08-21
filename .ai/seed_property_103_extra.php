<?php
/**
 * ADDITIONAL DATA SEEDING — PROPERTY 103 (Fixed version)
 * Banquet, Purchase, Stock, POS Room Charges, Advances, Housekeeping, Blockout, Cancellations
 * Run: php .ai/seed_property_103_extra.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;dbname=analysis;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pid = 103; $u = 'sa'; $now = date('Y-m-d H:i:s');
$sd = new DateTime('2026-04-01');
$ed = new DateTime('2026-08-21');

function rd($s,$e){ return date('Y-m-d',random_int($s->getTimestamp(),$e->getTimestamp())); }
function rt(){ return sprintf('%02d:%02d:%02d',random_int(6,23),random_int(0,59),random_int(0,59)); }
function rp($a){ return $a[array_rand($a)]; }

// ═══ PHASE 6: BANQUET (use simple SET syntax) ═══
echo "\n═══ PHASE 6: Banquet Bookings ═══\n";
$venues = ['1103','2103','3103'];
$functions = ['1103','2103','3103','4103'];
$partyNames = ['SHARMA WEDDING','GUPTA BIRTHDAY','KUMAR RECEPTION','SINGH ANNIVERSARY','MEHTA CORPORATE','VERMA CONFERENCE','AGARWAL ENGAGEMENT','JOSHI MEHENDI','TRIPATHI HALDI','MISHRA SANGEET','PANDEYA RECEPTION','CHAUHAN BDAY','RASTOGI POOJA','BAJPAI PAATH','SRIVASTAVA WEDDING','PATEL CEREMONY','CHANDOILYA BDAY','DWIVEDI RECEPTION','CHAUDHARY PARTY','TIWARI CELEBRATION'];

$hbCount = 0; $hs1Count = 0;
$maxHBvno = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM hallbook WHERE propertyid=103")->fetchColumn();
$maxHS1vno = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM hallsale1 WHERE propertyid=103")->fetchColumn();

for ($i = 0; $i < 30; $i++) {
    $bookDate = rd($sd, $ed);
    $funcDate = date('Y-m-d', strtotime($bookDate.' +'.random_int(5,60).' days'));
    if ($funcDate > '2026-08-25') $funcDate = '2026-08-25';
    $venue = rp($venues);
    $func = rp($functions);
    $pn = rp($partyNames);
    $pax = random_int(50, 500);
    $coverRate = random_int(300, 1200);
    $guarPax = round($pax * 0.7);
    $advance = round($coverRate * $guarPax * 0.3);
    $total = $pax * $coverRate;
    $taxable = round($total / 1.05, 2);
    $cgst = round($taxable * 5 / 100, 2);
    $sgst = round($taxable * 5 / 100, 2);
    $netamt = round($taxable + $cgst + $sgst, 0);

    $maxHBvno++;
    $hbDocId = "103BBA".date('Ymd',strtotime($bookDate)).sprintf("%04d",$maxHBvno);

    // HallBook using SET syntax (no counting needed)
    try {
        $m = '9'.random_int(100000000,999999999);
        $pdo->exec("INSERT IGNORE INTO hallbook SET propertyid=$pid, docid='$hbDocId', vtype='BBA', vno=$maxHBvno, vtime='".rt()."', vprefix=103, vdate='$bookDate', partyname='".addslashes($pn)."', add1='', add2='', city='', panno='', mobileno='$m', mobileno1='', func_name='$func', restcode='$venue', housekeeping='', frontoff='', engg='', security='', chef='', board='', menuspl1='', menuspl2='', menuspl3='', menuspl4='', menuspl5='', menuspl6='', menuspl7='', expatt=$pax, guaratt=$guarPax, coverrate=$coverRate, companycode='', remark='', bookingagent='', u_name='$u', u_entdt='$now', u_ae='a'");
        $hbCount++;
    } catch (Exception $e) { echo "  HB err: ".$e->getMessage()."\n"; if ($i > 2) break; }

    // HallSale1 using SET syntax
    $maxHS1vno++;
    $hs1DocId = "103BBA".date('Ymd',strtotime($funcDate)).sprintf("%04d",$maxHS1vno);
    try {
        $pdo->exec("INSERT INTO hallsale1 SET propertyid=$pid, docid='$hs1DocId', vtype='BBA', vprefix='103', vno=$maxHS1vno, vdate='$funcDate', restcode='$venue', party='".addslashes($pn)."', comp_code='', total=$total, discper=0, discamt=0, nontaxable=0, taxable=$taxable, roundoff=0, netamt=$netamt, u_name='$u', u_entdt='$now', u_ae='a', remark='', noofpax=$pax, rateperpax=$coverRate, totalpercover=$total, advance=$advance, rectno=0, rectdate=NULL, bookdocid='$hbDocId', narration='', narration1='', cgst=$cgst, sgst=$sgst");
        $hs1Count++;
    } catch (Exception $e) { echo "  HS1 err: ".$e->getMessage()."\n"; if ($i > 2) break; }

    // HallSale2 tax details
    try {
        $ssn2 = 1;
        $pdo->exec("INSERT INTO hallsale2 SET propertyid=$pid, docid='$hs1DocId', sno=$ssn2, sno1=0, vtype='BBA', vno=$maxHS1vno, vprefix=103, vdate='$funcDate', restcode='$venue', taxcode='CGSS103', basevalue=$taxable, taxper=5, taxamt=$cgst, u_name='$u', u_entdt='$now', u_ae='a'");
        $pdo->exec("INSERT INTO hallsale2 SET propertyid=$pid, docid='$hs1DocId', sno=2, sno1=0, vtype='BBA', vno=$maxHS1vno, vprefix=103, vdate='$funcDate', restcode='$venue', taxcode='SGSS103', basevalue=$taxable, taxper=5, taxamt=$sgst, u_name='$u', u_entdt='$now', u_ae='a'");
    } catch (Exception $e) {}

    // SunTran
    try {
        $ssn = (int)$pdo->query("SELECT COALESCE(MAX(sn),0) FROM suntran WHERE propertyid=103")->fetchColumn() + 1;
        $pdo->exec("INSERT INTO suntran SET propertyid=$pid, docid='$hs1DocId', sno=$ssn, vtype='BBA', vno=$maxHS1vno, vdate='$funcDate', partycode='', suncode='80103', dispname='BANQUET SALE', calcformula='1', svalue=0, amount=$netamt, baseamount=$taxable, u_name='$u', u_entdt='$now', u_ae='a', sunappdate='$funcDate', revcode='', restcode='$venue', delflag='N'");
    } catch (Exception $e) {}
}
echo "✅ $hbCount hall bookings, $hs1Count hall sales\n";

// ═══ PHASE 7: PURCHASE ORDERS ═══
echo "\n═══ PHASE 7: Purchase Orders ═══\n";
$suppliers = ['55103','69103','72103','82103','458103','461103'];
$purchItems = [
    [100103,'DAL',25,80],[101103,'RICE',50,40],[102103,'CHICKEN',30,200],
    [103103,'PANEER',10,350],[104103,'ONION',20,30],[105103,'TOMATO',15,25],
    [106103,'BUTTER',5,400],[107103,'MILK',40,55],[108103,'BREAD',30,30],
    [109103,'OIL',20,150],
];
$maxPVno = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM purch1 WHERE propertyid=103")->fetchColumn();
$purchCount = 0;

for ($i = 0; $i < 50; $i++) {
    $pDate = rd($sd, $ed);
    $supCode = $suppliers[$i % count($suppliers)];
    $maxPVno++;
    $pDocId = "103PURC".date('Ymd',strtotime($pDate)).sprintf("%04d",$maxPVno);

    $totalBill = 0;
    $numItems = random_int(2, 5);
    for ($j = 0; $j < $numItems; $j++) {
        $pi = rp($purchItems);
        $qty = random_int(5, 50);
        $rate = $pi[2] + random_int(-5, 10);
        $totalBill += $qty * $rate;
    }
    $taxable = $totalBill;
    $cgst = round($taxable * 5 / 100, 2);
    $sgst = round($taxable * 5 / 100, 2);
    $netamt = round($taxable + $cgst + $sgst, 0);

    try {
        $pdo->exec("INSERT INTO purch1 SET propertyid=$pid, docid='$pDocId', vno=$maxPVno, vdate='$pDate', vtype='PURC', vprefix='103', restcode='RES103', Party='$supCode', partytextname='', total=$totalBill, discper=0, discamt=0, nontaxable=0, taxable=$taxable, tax=0, servicecharge=0, addamt=0, dedamt=0, roundoff=0, netamt=$netamt, u_name='$u', u_entdt='$now', u_ae='a', delflag='N', partybillno='', partybilldt='$pDate', cashparty='', gstno='', remark='', invoicetype='', invoiceno=0, cgst=$cgst, sgst=$sgst, igst=0, payable=$netamt, billimagepath=''");
        $purchCount++;
    } catch (Exception $e) { echo "  Purch err: ".$e->getMessage()."\n"; if ($i > 2) break; }
}
echo "✅ $purchCount purchase orders\n";

// ═══ PHASE 8: STOCK CONSUMPTION ═══
echo "\n═══ PHASE 8: Stock / Kitchen Consumption ═══\n";
$stockCount = 0;
$itemCodes = $pdo->query("SELECT Code FROM itemmast WHERE Property_ID=103 AND RestCode IN ('RES103','RS103') GROUP BY Code LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);

for ($i = 0; $i < 200; $i++) {
    $sDate = rd($sd, $ed);
    $rest = rp(['RES103','RS103']);
    $itemCode = rp($itemCodes);
    $qty = random_int(1, 10);
    $rate = random_int(20, 200);
    $amt = $qty * $rate;
    $docId = sprintf("103CONS%s%04d", date('Ymd',strtotime($sDate)), $i+1);

    try {
        $pdo->exec("INSERT INTO stock SET propertyid=$pid, docid='$docId', sno=1, vtype='CONS', vno=0, vprefix='103', vdate='$sDate', partycode='', restcode='$rest', roomcat='', roomtype='', roomno='', contradocid='', contrasno=0, item=$itemCode, qtyiss=0, qtyrec=$qty, unit='KG', rate=$rate, amount=$amt, taxper=0, taxamt=0, discper=0, discamt=0, description='', voidyn='N', remarks='', kotdocid='', kotsno=0, vtime='".rt()."', u_name='$u', u_entdt='$now', u_ae='a', total=$amt, discapp='N', roundoff='0', departcode='', godowncode='', chalqty=0, recdqty=0, accqty=0, rejqty=0, recdunit='', specification='', itemrate=0, delflag='N', delremark='', landval=0, convratio=0, indentdocid='', indentsno=0, issqty=0, issueunit='', freesno=0, schemecode='', seqno=0, company='', itemrestcode='', schrgapp='', schrgper=0, schrgamt=0, refdocid='', ExpDate=NULL, mergedwith=''");
        $stockCount++;
    } catch (Exception $e) {}
}
echo "✅ $stockCount stock consumption entries\n";

// ═══ PHASE 9: HOUSEKEEPING ═══
echo "\n═══ PHASE 9: Housekeeping ═══\n";
$housekeepers = ['SITA DEVI','RAMA DEVI','GEETA','KAMLA','SUNITA','REKHA','ASHA','Pushpa'];
$roomNos = ['301','302','303','304','305','306','307','308','401','402','101','102','103','104','105','106','107','108','201','202','203','204','205','206','207','208','501','502','503','504','601','602'];
$hcCount = 0;
try { $pdo->exec("DELETE FROM roomclean WHERE u_entdt >= '2026-04-01'"); } catch (Exception $e) {}

for ($i = 0; $i < 400; $i++) {
    $hDate = rd($sd, $ed);
    $hk = rp($housekeepers);
    $rm = rp($roomNos);
    $type = rp(['D','C']);
    try {
        $pdo->exec("INSERT INTO roomclean SET propertyid=$pid, hosuekeeper='".addslashes($hk)."', roomno='$rm', remarks='', type='$type', u_name='$u', u_entdt='".addslashes($hDate.' '.rt())."', u_ae='a'");
        $hcCount++;
    } catch (Exception $e) {}
}
echo "✅ $hcCount housekeeping entries\n";

// ═══ PHASE 10: ROOM BLOCKOUT ═══
echo "\n═══ PHASE 10: Room Blockout ═══\n";
$rbCount = 0;
$blockReasons = ['MAINTENANCE','PAINTING','PLUMBING','ELECTRICAL','DEEP CLEAN','RENOVATION','AC REPAIR'];
$blockRooms = ['305','308','401','402','105','205','503','504','601','602'];

for ($i = 0; $i < 15; $i++) {
    $fromDate = rd($sd, $ed);
    $toDate = date('Y-m-d', strtotime($fromDate.' +'.random_int(1,5).' days'));
    if ($toDate > '2026-08-25') $toDate = '2026-08-25';
    $rm = rp($blockRooms);
    $reason = rp($blockReasons);
    try {
        $pdo->exec("INSERT INTO roomblockout SET propertyid=$pid, roomcode='$rm', block='B', reasons='$reason', fromdate='$fromDate', todate='$toDate', type='M', u_name='$u', u_entdt='$now', u_ae='a', vtime='".rt()."', guestname='', mobileno=''");
        $rbCount++;
    } catch (Exception $e) {}
}
echo "✅ $rbCount room blockout entries\n";

// ═══ PHASE 11: POS ROOM CHARGES ═══
echo "\n═══ PHASE 11: POS Room Charges ═══\n";
$rpcCount = 0;
$folls = $pdo->query("SELECT docid, folioNo, guestprof, roomno, roomcat FROM roomocc WHERE propertyid=103 AND docid LIKE '103CHK2026%' ORDER BY sn DESC LIMIT 100")->fetchAll();

$menuItems = [
    ['100103',80],['101103',150],['102103',275],['103103',120],['104103',100],
    ['105103',60],['109103',200],['113103',40],['114103',20],['115103',30],
    ['117103',20],['118103',30],
];

$maxSV = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM sale1 WHERE propertyid=103")->fetchColumn();
$maxKV = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM kot WHERE propertyid='103'")->fetchColumn();

for ($i = 0; $i < 80; $i++) {
    $f = rp($folls);
    $sDate = rd($sd, $ed);
    $rest = 'RS103';
    $maxSV++;
    $sdid = "103SALE".date('Ymd',strtotime($sDate)).sprintf("%04d",$maxSV);

    $ni = random_int(1,3);
    $total = 0;
    for ($j = 0; $j < $ni; $j++) {
        $item = rp($menuItems);
        $qty = rp([1,1,2]);
        $amt = $item[1] * $qty;
        $total += $amt;
        $maxKV++;
        try {
            $kd = sprintf("103KOT%s%04d", date('Ymd',strtotime($sDate)), $maxKV);
            $pdo->exec("INSERT INTO kot SET propertyid='$pid', docid='$kd', sno=".($j+1).", vtype='KOT', vtime='".rt()."', vno=$maxKV, vprefix=103, vdate='$sDate', restcode='$rest', roomcat='".$f['roomcat']."', roomtype='', roomno='".$f['roomno']."', item='".$item[0]."', qty=$qty, rate=".$item[1].", amount=$amt, voidyn='N', waiter='W001', pending='N', u_name='$u', u_entdt='$now', u_ae='a'");
        } catch (Exception $e) {}
    }

    $cg = round($total*5/100,2);
    $sg = round($total*5/100,2);
    $bt = round($total + $cg + $sg, 0);
    $ro = $bt - ($total + $cg + $sg);

    try {
        $pdo->exec("INSERT INTO sale1 SET propertyid=$pid, docid='$sdid', vtype='SALE', vno=$maxSV, vtime='".rt()."', vprefix=103, vdate='$sDate', restcode='$rest', roomcat='RO', roomtype='', roomno='".$f['roomno']."', foliono=".$f['folioNo'].", sno1=$ni, party='', total=$bt, discper=0, discamt=0, nontaxable=0, taxable=$total, servicecharge=0, addamt=0, dedamt=0, roundoff=$ro, custname='', phoneno='', `add`='', city='', cashrecd=0, folionodocid='".$f['docid']."', u_name='$u', u_entdt='$now', u_ae='a'");
        $rpcCount++;
    } catch (Exception $e) { echo "  RPC err: ".$e->getMessage()."\n"; if ($i > 2) break; }
}
echo "✅ $rpcCount POS room charge bills\n";

// ═══ PHASE 12: ADVANCE DEPOSITS ═══
echo "\n═══ PHASE 12: Advance Deposits ═══\n";
$advCount = 0;
$bookings = $pdo->query("SELECT DocId, BookNo, GuestName, GuestProf FROM booking WHERE Property_ID=103 AND DocId LIKE '103RES2026%' LIMIT 50")->fetchAll();

foreach ($bookings as $b) {
    if (random_int(1, 100) <= 40) {
        $advAmt = random_int(1000, 5000);
        $advDate = rd($sd, $ed);
        try {
            $pdo->exec("INSERT INTO paycharge SET propertyid=$pid, docid='".addslashes($b['DocId'])."', sno=5, sno1=1, vtype='ADV', vno=".$b['BookNo'].", vprefix=103, vdate='$advDate', vtime='".rt()."', guestprof='".$b['GuestProf']."', paycode='CASH', paytype='ADVANCE', amtcr=0, amtdr=$advAmt, roomcat='', roomno='', foliono=0, u_name='$u', u_entdt='$now', u_ae='a', posted=''");
            $advCount++;
        } catch (Exception $e) {}
    }
}
echo "✅ $advCount advance deposits\n";

// ═══ PHASE 13: ADDITIONAL CHARGES ═══
echo "\n═══ PHASE 13: Additional Charges ═══\n";
$addChargeCount = 0;
$chargeTypes = [
    ['70103','LAUNDRY','Laundry Charge'],['70103','LAUNDRY','Dry Cleaning'],
    ['70103','LAUNDRY','Wash & Iron'],['75103','MINI BAR','Soft Drinks'],
    ['75103','MINI BAR','Snacks'],['75103','MINI BAR','Water Bottle'],
    ['15103','EXTRA BED','Extra Bed Charge'],['15103','EXTRA BED','Extra Person Charge'],
];

for ($i = 0; $i < 60; $i++) {
    $f = rp($folls);
    $ct = rp($chargeTypes);
    $cDate = rd($sd, $ed);
    $amt = random_int(100, 800);
    $sno = random_int(10, 20);
    try {
        $pdo->exec("INSERT INTO paycharge SET propertyid=$pid, docid='".$f['docid']."', sno=$sno, sno1=1, vtype='CHG', vno=0, vprefix=103, vdate='$cDate', vtime='".rt()."', guestprof='".$f['guestprof']."', paycode='".$ct[0]."', paytype='".$ct[1]."', amtcr=$amt, amtdr=0, roomcat='RO', roomno='".$f['roomno']."', foliono=".$f['folioNo'].", comments='".$ct[2]."', u_name='$u', u_entdt='$now', u_ae='a', posted=''");
        $addChargeCount++;
    } catch (Exception $e) {}
}
echo "✅ $addChargeCount additional charges\n";

// ═══ PHASE 14: ROOM CHANGES ═══
echo "\n═══ PHASE 14: Room Changes ═══\n";
$rcCount = 0;
$activeOcc = $pdo->query("SELECT docid, roomno, roomcat, guestprof, folioNo FROM roomocc WHERE propertyid=103 AND docid LIKE '103CHK2026%' AND (chkoutdate IS NULL OR chkoutdate > CURDATE()) LIMIT 30")->fetchAll();

foreach ($activeOcc as $occ) {
    if (random_int(1, 100) <= 20) {
        $newRoom = rp(['301','302','303','304','101','102','103','104','201','202']);
        $changeDate = rd($sd, $ed);
        try {
            $pdo->exec("UPDATE roomocc SET newroomno='$newRoom', chngdate='$changeDate', reasonrchange='Guest Request' WHERE propertyid=$pid AND docid='".$occ['docid']."' AND roomno='".$occ['roomno']."' AND roomcat='".$occ['roomcat']."'");
            $rcCount++;
        } catch (Exception $e) {}
    }
}
echo "✅ $rcCount room changes\n";

// ═══ PHASE 15: CANCELLED RESERVATIONS ═══
echo "\n═══ PHASE 15: Cancelled Reservations ═══\n";
$cancelCount = 0;
$allBookings = $pdo->query("SELECT DocId, vdate FROM booking WHERE Property_ID=103 AND DocId LIKE '103RES2026%' AND Cancel='N' LIMIT 200")->fetchAll();

foreach ($allBookings as $b) {
    if (random_int(1, 100) <= 8) {
        $cancelDate = date('Y-m-d', strtotime($b['vdate'].' +'.random_int(0,5).' days'));
        try {
            $pdo->exec("UPDATE booking SET Cancel='Y', CancelDate='$cancelDate', CancelUName='sa' WHERE Property_ID=$pid AND DocId='".addslashes($b['DocId'])."'");
            $cancelCount++;
        } catch (Exception $e) {}
    }
}
echo "✅ $cancelCount cancelled reservations\n";

// ═══ FINAL COUNTS ═══
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📊 FINAL COUNTS — ALL MODULES\n";
echo "═══════════════════════════════════════════════════════════════\n";

$tbls = [
    ['Guest Prof','guestprof','propertyid'],['Booking','booking','Property_ID'],
    ['RoomOcc','roomocc','propertyid'],['GuestFolio','guestfolio','propertyid'],
    ['PayCharge','paycharge','propertyid'],['Sale1','sale1','propertyid'],
    ['KOT','kot','propertyid'],['SunTran','suntran','propertyid'],
    ['Night Audit','nightauditlog','propertyid'],['HallBook','hallbook','propertyid'],
    ['HallSale1','hallsale1','propertyid'],['HallSale2','hallsale2','propertyid'],
    ['Purch1','purch1','propertyid'],['Stock','stock','propertyid'],
    ['RoomClean','roomclean','propertyid'],['RoomBlockout','roomblockout','propertyid'],
    ['Item Mast','itemmast','Property_ID'],['Room Mast','room_mast','propertyid'],
    ['SubGroup','subgroup','propertyid'],['Employee','employee','propertyid'],
];
foreach ($tbls as $t) {
    try {
        $c = $pdo->query("SELECT COUNT(*) FROM {$t[1]} WHERE {$t[2]}=$pid")->fetchColumn();
        printf("   %-20s %6d\n", $t[0], $c);
    } catch (Exception $e) { printf("   %-20s ❌\n", $t[0]); }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ ALL SEEDING COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n";
