<?php
/**
 * DATA SEEDING SCRIPT — PROPERTY 103 (Apr 1 - Aug 21, 2026)
 * Run: php .ai/seed_property_103.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;dbname=analysis;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pid = 103; $u = 'sa'; $now = date('Y-m-d H:i:s');

$rooms = [
    '1103' => ['301','302','303','304','305','306','307','308','401','402','403','404','405','406','407','408'],
    '2103' => ['101','102','103','104','105','106','107','108','110','201','202','203','204','205','206','207','208'],
    '3103' => ['109','209','309','409'],
    '4103' => ['501','502','503','504','505','506','507','508','601','602','603','604','605','606','607','608'],
];
$rates = ['1103'=>[1000,1200,1500], '2103'=>[1800,2200,2500,3500], '3103'=>[4000,5000,6000], '4103'=>[3000,3500,4000,5000]];

$guests = [
    ['RAHUL SHARMA','M','9876543210','DELHI'],['PRIYA GUPTA','F','9123456789','MUMBAI'],['AMIT KUMAR','M','9234567890','KANPUR'],
    ['NEHA VERMA','F','9345678901','LUCKNOW'],['VIKRAM SINGH','M','9456789012','JAIPUR'],['SUNITA DEVI','F','9567890123','AGRA'],
    ['ROHIT MEHTA','M','9678901234','BHOPAL'],['ANITA RANI','F','9789012345','INDORE'],['SANDEEP YADAV','M','9890123456','DELHI'],
    ['KAVITA SHARMA','F','9901234567','MUMBAI'],['MANOJ TIWARI','M','9012345678','KANPUR'],['POOJA SINGH','F','9112345678','VARANASI'],
    ['AJAY MISHRA','M','9212345678','PRAYAGRAJ'],['SEEMA PATEL','F','9312345678','GWALIOR'],['DEEPAK JOSHI','M','9412345678','BHOPAL'],
    ['RITU AGARWAL','F','9512345678','INDORE'],['KISHAN LAL','M','9612345678','JHANSI'],['MEERA BAIG','F','9712345678','DELHI'],
    ['SURESH PANDYA','M','9812345678','MUMBAI'],['USHA RATHORE','F','9912345678','JAIPUR'],['BHARAT SONI','M','9022345678','AGRA'],
    ['GEETA AWASTHI','F','9122345678','LUCKNOW'],['RAJESH DUBEY','M','9222345678','KANPUR'],['NAINA KAPOOR','F','9322345678','MUMBAI'],
    ['MOHAN LAL','M','9422345678','DELHI'],['SHREYA JAIN','F','9522345678','INDORE'],['ARVIND CHAUHAN','M','9622345678','BHOPAL'],
    ['NITIKA GOYAL','F','9722345678','VARANASI'],['PRAMOD YADAV','M','9822345678','PRAYAGRAJ'],['ANJU BAJPAI','F','9922345678','GWALIOR'],
    ['VIVEK TRIPATHI','M','9032345678','KANPUR'],['ARCHANA SRIVASTAVA','F','9132345678','LUCKNOW'],['RAJEEV KUMAR','M','9232345678','DELHI'],
    ['POONAM CHANDEL','F','9332345678','JAIPUR'],['NARESH GUPTA','M','9432345678','MUMBAI'],['SARITA RATHI','F','9532345678','AGRA'],
    ['SUNIL PANDEY','M','9632345678','KANPUR'],['MEENAKSHI SAXENA','F','9732345678','DELHI'],['DINESH RASTOGI','M','9832345678','LUCKNOW'],
    ['NISHA BHATT','F','9932345678','BHOPAL'],['ANIL SRIVASTAVA','M','9042345678','MUMBAI'],['REENA SINGH','F','9142345678','JAIPUR'],
    ['PANKAJ MISHRA','M','9242345678','INDORE'],['SHWETA AGARWAL','F','9342345678','VARANASI'],['KAMAL NARAYAN','M','9442345678','PRAYAGRAJ'],
    ['SUDHA CHATURVEDI','F','9542345678','GWALIOR'],['YOGESH PANDYA','M','9642345678','DELHI'],['ALKA MEHROTRA','F','9742345678','KANPUR'],
    ['TARUN BHATIA','M','9842345678','MUMBAI'],['KIRAN BAJRANGI','F','9942345678','LUCKNOW'],['GAJENDRA PAL','M','9052345678','BHOPAL'],
    ['MADHU SHARMA','F','9152345678','AGRA'],
];

$menuItems = [
    ['100103','VEG THALI',80],['101103','NON-VEG THALI',150],['102103','BUTTER CHICKEN',275],
    ['103103','PANEER TIKKA',120],['104103','DAL MAKHANI',100],['105103','JEERA RICE',60],
    ['106103','NAAN',30],['107103','SAMBHAR',40],['108103','MIXED VEG',90],
    ['109103','CHICKEN BIRYANI',200],['110103','FRIED RICE',80],['111103','MANCHURIAN',100],
    ['112103','MOMOS',60],['113103','SWEET LASSI',40],['114103','MASALA CHAI',20],
    ['115103','COFFEE',30],['116103','ORANGE JUICE',50],['117103','MINERAL WATER',20],
    ['118103','COLD DRINKS',30],['119103','BEER',150],['120103','TOAST',30],
    ['121103','CORNFLAKES',50],['122103','OMLETTE',40],['123103','PARATHA',35],
];
$rests = ['RES103','RS103'];
$waiters = ['W001','W002','W003','W004','W005'];

$maxGC = (int)$pdo->query("SELECT COALESCE(MAX(CAST(guestcode AS UNSIGNED)),10310013) FROM guestprof WHERE propertyid=103")->fetchColumn();
$maxBN = (int)$pdo->query("SELECT COALESCE(MAX(BookNo),0) FROM booking WHERE Property_ID=103")->fetchColumn();
$maxFN = (int)$pdo->query("SELECT COALESCE(MAX(folio_no),0) FROM guestfolio WHERE propertyid=103")->fetchColumn();
$maxSV = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM sale1 WHERE propertyid=103")->fetchColumn();
$maxKV = (int)$pdo->query("SELECT COALESCE(MAX(vno),0) FROM kot WHERE propertyid='103'")->fetchColumn();
echo "Max: GC=$maxGC BN=$maxBN FN=$maxFN SV=$maxSV KV=$maxKV\n";

function rd($s,$e){ return date('Y-m-d',random_int($s->getTimestamp(),$e->getTimestamp())); }
function rt(){ return sprintf('%02d:%02d:%02d',random_int(6,23),random_int(0,59),random_int(0,59)); }
function rp($a){ return $a[array_rand($a)]; }

$sd = new DateTime('2026-04-01');
$ed = new DateTime('2026-08-21');

// ═══ PHASE 1: Guest Profiles ═══
echo "\n═══ PHASE 1: Guest Profiles ═══\n";
$gc = $maxGC; $cnt = 0;
foreach ($guests as $g) {
    $gc++;
    try {
        $pdo->prepare("INSERT IGNORE INTO guestprof (propertyid, docid, folio_no, sno1, bill_to, guestcode, name, add1, city, state_code, country_code, type, mobile_no, email_id, nationality, gender, guest_status, u_name, u_entdt, u_ae) VALUES (?, '', 0, 1, 'H', ?, ?, 'Address', ?, '09', 'IN', 'Individual', ?, ?, 'Indian', ?, 'R', 'sa', ?, 'a')")->execute([$pid, (string)$gc, $g[0], $g[3], $g[2], strtolower(str_replace(' ','.',$g[0])).'@test.com', $g[1], $now]);
        $cnt++;
    } catch (Exception $e) { break; }
}
echo "✅ $cnt guest profiles\n";

// ═══ PHASE 2: Bookings & Check-ins ═══
echo "\n═══ PHASE 2: Bookings & Check-ins ═══\n";
$bc = 0; $rc = 0; $fc = 0;

// FIRST: Delete previously seeded records (from earlier failed runs)
try { $pdo->exec("DELETE FROM roomocc WHERE docid LIKE '103CHK2026%'"); } catch (Exception $e) {}
try { $pdo->exec("DELETE FROM paycharge WHERE docid LIKE '103CHK2026%'"); } catch (Exception $e) {}
try { $pdo->exec("DELETE FROM guestfolio WHERE docid LIKE '103CHK2026%'"); } catch (Exception $e) {}
try { $pdo->exec("DELETE FROM guestprof WHERE docid LIKE '103CHK2026%'"); } catch (Exception $e) {}
try { $pdo->exec("DELETE FROM booking WHERE DocId LIKE '103RES2026%'"); } catch (Exception $e) {}

echo "  Cleaned previous seeded data\n";

for ($i = 0; $i < 150; $i++) {
    $bd = rd($sd, $ed);
    $ad = date('Y-m-d', strtotime($bd.' +'.random_int(0,10).' days'));
    if ($ad > '2026-08-21') $ad = '2026-08-21';
    $sn = random_int(1,3);
    $dd = date('Y-m-d', strtotime($ad." +$sn days"));
    if ($dd > '2026-08-25') { $dd='2026-08-25'; $sn=max(1,(int)(strtotime($dd)-strtotime($ad))/86400); }

    $gi = ($maxBN+$i) % count($guests);
    $g = $guests[$gi];
    $bn = $maxBN + $i + 1;
    $rcat = rp(array_keys($rooms));
    $rmno = rp($rooms[$rcat]);
    $rate = rp($rates[$rcat]);
    $cgst = round($rate*12/200,2);
    $sgst = round($rate*12/200,2);
    $tr = $rate + $cgst + $sgst;
    $giCode = (string)(10310200 + $bn*3);
    $docId = "103RES".date('Ymd',strtotime($bd)).sprintf("%04d",$bn);
    $chkDoc = "103CHK".date('Ymd',strtotime($ad)).sprintf("%04d",$bn);

    // Booking
    try {
        $pdo->prepare("INSERT IGNORE INTO booking (Property_ID, DocId, Vtype, BookNo, Vprefix, vdate, NoofRooms, Remarks, pickupdrop, Authorization, Company, GuestProf, TravelAgency, BussSource, MarketSeg, ArrFrom, Destination, BookedBy, ResMode, TravelMode, CancelDate, Cancel, GuestName, U_Name, U_EntDt, U_AE, MobNo, Email, RRTaxInc, RDisc, RSDisc, RRServiceChrg, ResStatus, Verified, RefBookNo) VALUES (?, ?, 'RES', ?, 2026, ?, ?, '', '', '', '', ?, '', '3103', '', 'DELHI', '', '', 'Direct', 'Car', NULL, 'N', ?, ?, ?, 'a', '', '', 'Y', 0, 0, 'N', 'Confirm', 'YES', '')")->execute([$pid,$docId,$bn,$bd,1,$giCode,$g[0],$u,$now]);
        $bc++;
    } catch (Exception $e) { continue; }

    // GuestProf for check-in
    try {
        $pdo->prepare("INSERT INTO guestprof (propertyid, docid, folio_no, sno1, bill_to, guestcode, name, city, gender, guest_status, u_name, u_entdt, u_ae) VALUES (?, ?, ?, 1, 'H', ?, ?, ?, 'R', 'sa', ?, 'a')")->execute([$pid,$chkDoc,$bn,$giCode,$g[0],$g[3],$g[1],$now]);
    } catch (Exception $e) {}

    // RoomOcc - 29 columns, values must match exactly
    try {
        $pdo->prepare("INSERT INTO roomocc (propertyid, docid, name, sno, sno1, folioNo, vtype, vprefix, guestprof, roomcat, roomtype, roomno, ratecode, roomrate, chkindate, chkintime, adult, children, depdate, deptime, nodays, chkoutdate, chkouttime, u_name, u_entdt, u_ae, sysYN, activeYN, roomcount) VALUES (?, ?, ?, ?, ?, ?, 'CHK', 103, ?, ?, 'RO', ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sa', ?, 'a', 'Y', 'Y', 1)")->execute([$pid,$chkDoc,$g[0],$bn,1,$bn,$giCode,$rcat,$rmno,$rate,$ad,rt(),2,0,$dd,rt(),$sn,$dd,rt(),$now]);
        $rc++;
    } catch (Exception $e) { echo "  RO err($i): ".$e->getMessage()."\n"; if ($i > 2) break; }

    // GuestFolio
    try {
        $pdo->prepare("INSERT INTO guestfolio (propertyid, docid, folio_no, sno1, nochargepost, vtype, vdate, vprefix, guestprof, name, city, nodays, pickupdrop, u_name, u_entdt, u_ae) VALUES (?, ?, ?, 1, 'N', 'CHK', ?, 103, ?, ?, ?, ?, '', 'sa', ?, 'a')")->execute([$pid,$chkDoc,$bn,$ad,$giCode,$g[0],$g[3],$sn,$now]);
        $fc++;
    } catch (Exception $e) {}

    // PayCharge — Room Rent
    $tRate = $tr * $sn;
    try {
        $pdo->prepare("INSERT INTO paycharge (propertyid, docid, sno, sno1, vtype, vno, vprefix, vdate, vtime, guestprof, paycode, paytype, amtcr, amtdr, roomcat, roomno, foliono, u_name, u_entdt, u_ae, posted) VALUES (?, ?, 1, 1, 'CHG', ?, 103, ?, ?, ?, '15103', 'ROOM CHARGE', ?, 0, ?, ?, ?, 'sa', ?, 'a', '')")->execute([$pid,$chkDoc,$bn,$ad,rt(),$giCode,$tRate,$rcat,$rmno,$bn,$now]);
    } catch (Exception $e) {}
    // CGST
    try {
        $pdo->prepare("INSERT INTO paycharge (propertyid, docid, sno, sno1, vtype, vno, vprefix, vdate, vtime, guestprof, paycode, paytype, amtcr, amtdr, roomcat, roomno, foliono, u_name, u_entdt, u_ae, posted) VALUES (?, ?, 2, 1, 'CHG', ?, 103, ?, ?, ?, '8103', 'CGST', ?, 0, ?, ?, ?, 'sa', ?, 'a', '')")->execute([$pid,$chkDoc,$bn,$ad,rt(),$giCode,$cgst*$sn,$rcat,$rmno,$bn,$now]);
    } catch (Exception $e) {}
    // SGST
    try {
        $pdo->prepare("INSERT INTO paycharge (propertyid, docid, sno, sno1, vtype, vno, vprefix, vdate, vtime, guestprof, paycode, paytype, amtcr, amtdr, roomcat, roomno, foliono, u_name, u_entdt, u_ae, posted) VALUES (?, ?, 3, 1, 'CHG', ?, 103, ?, ?, ?, '9103', 'SGST', ?, 0, ?, ?, ?, 'sa', ?, 'a', '')")->execute([$pid,$chkDoc,$bn,$ad,rt(),$giCode,$sgst*$sn,$rcat,$rmno,$bn,$now]);
    } catch (Exception $e) {}

    // Payment
    $payChance = random_int(1,100);
    if ($payChance <= 70) {
        try {
            $pdo->prepare("INSERT INTO paycharge (propertyid, docid, sno, sno1, vtype, vno, vprefix, vdate, vtime, guestprof, paycode, paytype, amtcr, amtdr, roomcat, roomno, foliono, settledate, modeset, u_name, u_entdt, u_ae, posted) VALUES (?, ?, 4, 1, 'RCP', ?, 103, ?, ?, ?, 'CASH', 'PAYMENT', 0, ?, ?, ?, ?, ?, ?, 'sa', ?, 'a', '')")->execute([$pid,$chkDoc,$bn,$dd,rt(),$giCode,$tRate,$rcat,$rmno,$bn,$dd,'Y',$now]);
        } catch (Exception $e) {}
    } elseif ($payChance <= 90) {
        $partial = round($tRate * random_int(30,70) / 100);
        try {
            $pdo->prepare("INSERT INTO paycharge (propertyid, docid, sno, sno1, vtype, vno, vprefix, vdate, vtime, guestprof, paycode, paytype, amtcr, amtdr, roomcat, roomno, foliono, settledate, modeset, u_name, u_entdt, u_ae, posted) VALUES (?, ?, 4, 1, 'RCP', ?, 103, ?, ?, ?, 'CASH', 'PAYMENT', 0, ?, ?, ?, ?, ?, ?, 'sa', ?, 'a', '')")->execute([$pid,$chkDoc,$bn,date('Y-m-d',strtotime($ad.'+1 day')),rt(),$giCode,$partial,$rcat,$rmno,$bn,date('Y-m-d',strtotime($ad.'+1 day')),'Y',$now]);
        } catch (Exception $e) {}
    }
}
echo "✅ $bc bookings, $rc room occ, $fc folios\n";

// ═══ PHASE 3: POS / Restaurant ═══
echo "\n═══ PHASE 3: POS / Restaurant ═══\n";
$sc = 0; $kc = 0;

// Clean previous POS
try { $pdo->exec("DELETE FROM sale1 WHERE docid LIKE '103SALE2026%'"); } catch (Exception $e) {}
try { $pdo->exec("DELETE FROM kot WHERE docid LIKE '103KOT2026%'"); } catch (Exception $e) {}

for ($i = 0; $i < 250; $i++) {
    $sd2 = rd($sd, $ed);
    $rest = rp($rests);
    $maxSV++;
    $sdid = "103SALE".date('Ymd',strtotime($sd2)).sprintf("%04d",$maxSV);

    $ni = random_int(1,5);
    $total = 0;
    for ($j = 0; $j < $ni; $j++) {
        $item = rp($menuItems);
        $qty = rp([1,1,1,2]);
        $amt = $item[2] * $qty;
        $total += $amt;
        $maxKV++;
        $kd = "103KOT".date('Ymd',strtotime($sd2)).sprintf("%04d",$maxKV);
        try {
            // KOT: 21 columns
            $pdo->prepare("INSERT INTO kot (propertyid, docid, sno, vtype, vtime, vno, vprefix, vdate, restcode, roomcat, roomtype, roomno, item, qty, rate, amount, voidyn, waiter, pending, u_name, u_entdt, u_ae) VALUES (?, ?, ?, 'KOT', ?, ?, 103, ?, ?, 'RO', '', '', ?, ?, ?, ?, 'N', ?, 'N', 'sa', ?, 'a')")->execute([$pid,$kd,$j+1,rt(),$maxKV,$sd2,$rest,$item[0],$qty,$item[2],$amt,rp($waiters),$now]);
            $kc++;
        } catch (Exception $e) {}
    }

    $tax = $total;
    $ro = round($total,0) - $total;
    $bt = round($total,0);

    // sale1: 32 columns, need exactly 12 ? tokens
    try {
        $pdo->prepare("INSERT INTO sale1 (propertyid, docid, vtype, vno, vtime, vprefix, vdate, restcode, roomcat, roomtype, roomno, foliono, sno1, party, total, discper, discamt, nontaxable, taxable, servicecharge, addamt, dedamt, roundoff, custname, phoneno, \x60add\x60, city, cashrecd, folionodocid, u_name, u_entdt, u_ae) VALUES (?, ?, 'SALE', ?, ?, 103, ?, ?, 'RO', '', '', 0, ?, '', ?, 0, 0, ?, ?, 0, 0, 0, ?, '', '', '', '', 0, '', 'sa', ?, 'a')")->execute([$pid,$sdid,$maxSV,rt(),$sd2,$rest,$ni,$bt,$tax,$tax,$ro,$now]);
        $sc++;
    } catch (Exception $e) { echo "  Sale1 err($i): ".$e->getMessage()."\n"; if ($i > 2) break; }
}
echo "✅ $sc sale bills, $kc KOTs\n";

// ═══ PHASE 4: Accounting (SunTran) ═══
echo "\n═══ PHASE 4: Accounting (SunTran) ═══\n";
$stc = 0;
$ssn = (int)$pdo->query("SELECT COALESCE(MAX(sn),0) FROM suntran WHERE propertyid=103")->fetchColumn();

$posBills = $pdo->query("SELECT docid, vno, vdate, restcode, taxable, total FROM sale1 WHERE propertyid=103 AND docid LIKE '103SALE2026%' ORDER BY sn DESC LIMIT 250")->fetchAll();
foreach ($posBills as $pos) {
    $tax = (float)$pos['taxable'];
    $cg = round($tax*5/100,2);
    $sg = round($tax*5/100,2);
    $entries = [['RSFD103','Amount',$tax,$tax],['CGSS103','CGST',$cg,$tax],['SGSS103','SGST',$sg,$tax]];
    foreach ($entries as $e) {
        $ssn++;
        try {
            $pdo->prepare("INSERT INTO suntran (propertyid, docid, sno, vtype, vno, vdate, partycode, suncode, dispname, calcformula, svalue, amount, baseamount, u_name, u_entdt, u_ae, sunappdate, revcode, restcode, delflag) VALUES (?, ?, ?, 'SALE', ?, ?, '', ?, ?, '1-2', 0, ?, ?, 'sa', ?, 'a', ?, '', ?, 'N')")->execute([$pid,$pos['docid'],$ssn,$pos['vno'],$pos['vdate'],$e[0],$e[1],$e[2],$e[3],$now,$pos['vdate'],$pos['restcode']]);
            $stc++;
        } catch (Exception $e) {}
    }
}

// Night Audit
$naC = 0;
$cur = clone $sd;
while ($cur <= $ed) {
    try {
        $pdo->prepare("INSERT INTO nightauditlog (propertyid, vdate, u_name, u_entdt) VALUES (?, ?, 'sa', ?)")->execute([$pid,$cur->format('Y-m-d'),$cur->format('Y-m-d').' 23:59:59']);
        $naC++;
    } catch (Exception $e) {}
    $cur->modify('+1 day');
}
echo "✅ $stc SunTran entries, $naC night audit logs\n";

// ═══ FINAL ═══
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📊 FINAL COUNTS — PROPERTY 103\n";
echo "═══════════════════════════════════════════════════════════════\n";
$tbls = [
    ['Guest Prof','guestprof','propertyid'],['Booking','booking','Property_ID'],['RoomOcc','roomocc','propertyid'],
    ['GuestFolio','guestfolio','propertyid'],['PayCharge','paycharge','propertyid'],['Sale1','sale1','propertyid'],
    ['KOT','kot','propertyid'],['SunTran','suntran','propertyid'],['Night Audit','nightauditlog','propertyid'],
    ['Item Mast','itemmast','Property_ID'],['Rate List','rate_list','propertyid'],['Room Mast','room_mast','propertyid'],
    ['SubGroup','subgroup','propertyid'],['Stock','stock','propertyid'],['Employee','employee','propertyid'],
];
foreach ($tbls as $t) {
    try {
        $c = $pdo->query("SELECT COUNT(*) FROM {$t[1]} WHERE {$t[2]}=$pid")->fetchColumn();
        printf("   %-20s %6d\n", $t[0], $c);
    } catch (Exception $e) { printf("   %-20s ❌\n", $t[0]); }
}
echo "\n✅ SEEDING COMPLETE\n";
