<?php
//Posting Bongkar Muat
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
    $param['notransaksi']."'");
	
$dataH = fetchData($queryH);

#====cek periode===============================
$tgl = str_replace("-","",$dataH[0]['tanggal']);
if($_SESSION['org']['period']['start']>$tgl)
    exit('Error:Tanggal diluar periode aktif');

# Detail
//$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".$param['notransaksi']."'");
$queryD  = "select a.*,b.nik as nikkaryawan,b.namakaryawan from ".$dbname.".kebun_prestasi a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.nik
			where notransaksi='".$param['notransaksi']."'";
$dataD = fetchData($queryD);

#=== Cek if posted ===
$error0 = "";
if($dataH[0]['jurnal']==1) {
    $error0 .= $_SESSION['lang']['errisposted'];
}
if($error0!='') {
    echo "Data Error :\n".$error0;
    exit;
}

#=== Cek if data not exist ===
$error1 = "";
if(count($dataH)==0) {
    $error1 .= $_SESSION['lang']['errheadernotexist']."\n";
}
if(count($dataD)==0) {
    $error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
}
if($error1!='') {
    echo "Data Error :\n".$error1;
    exit;
}
#======================== Kegiatan Bongkar Muat ===========================
$kodeJurnal = 'BM';
$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
    " jurnalid='".$kodeJurnal."'");
$resParam = fetchData($queryParam);

	  $akunkredit=$resParam[0]['noakunkredit']; 
      $akundebet =$resParam[0]['noakundebet'];
//default kodekegiatan panen/potong buah      
$kodekegiatan= $akundebet."03";
      
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Transform No Jurnal dari No Transaksi
$tmpNoJurnal = explode('/',$param['notransaksi']);
$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;
#======================== Nomor Jurnal =============================

    // cari hari
    $day = date('D', strtotime($dataH[0]['tanggal']));
    if($day=='Sun')$libur=true; else $libur=false;
    // kamus hari libur
    $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$dataH[0]['tanggal']."'";
    $queorg=mysql_query($strorg) or die(mysql_error());
    while($roworg=mysql_fetch_assoc($queorg))
    {
//            $libur=true;
        if($roworg['keterangan']=='libur')$libur=true;
        if($roworg['keterangan']=='masuk')$libur=false;
    }        
    $tulisanpanen='Bongkar Muat';
    //if($libur)$tulisanpanen.=' (Kontanan)';


#=== Transform Data ===
$dataRes['header'] = array();
$dataRes['detail'] = array();

#1. Data Header
$dataRes['header'] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$kodeJurnal,
    'tanggal'=>$dataH[0]['tanggal'],
    'tanggalentry'=>date('Ymd'),
    'posting'=>'0',
    'totaldebet'=>'0',
    'totalkredit'=>'0',
    'amountkoreksi'=>'0',
    'noreferensi'=>$dataH[0]['notransaksi'],
    'autojurnal'=>'1',
    'matauang'=>'IDR',
    'kurs'=>'1',
    'revisi'=>'0'
);

#2. Data Detail
# Get Data from Kegiatan
$i = 0;

# Detail (Debet)
$noUrut = 1;
$totalJumlah = 0;
foreach($dataD as $row) {
    $dataRes['detail'][] = array(
        'nojurnal'=>$nojurnal,
        'tanggal'=>$dataH[0]['tanggal'],
        'nourut'=>$noUrut,
        'noakun'=>$akundebet,
        'keterangan'=>$tulisanpanen." [".$row['nikkaryawan']."]-".$row['namakaryawan'],
        'jumlah'=>($row['jumlahhk'] * $row['umr']) + $row['upahpremi'] + $row['upahkerja'] - $row['upahpenalty'] + $row['premibasis'] - $row['rupiahpenalty'],
        'matauang'=>'IDR',
        'kurs'=>'1',
        'kodeorg'=>substr($row['kodeorg'],0,4),
        'kodekegiatan'=>$kodekegiatan,
        'kodeasset'=>'',
        'kodebarang'=>'',
        'nik'=>$row['nik'],
        'kodecustomer'=>'',
        'kodesupplier'=>'',
        'noreferensi'=>$row['notransaksi'],
        'noaruskas'=>'',
        'kodevhc'=>'',
        'nodok'=>'',
		'kodeblok'=>$row['kodeorg'],
		'revisi'=>'0',
		'kodesegment' => $row['kodesegment']
    );
    $totalJumlah += ($row['jumlahhk'] * $row['umr']) + $row['upahpremi'] + $row['upahkerja'] - $row['upahpenalty'] + $row['premibasis'] - $row['rupiahpenalty'];
    $noUrut++;
}

# Detail (Kredit)
//`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`, `kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`, `kodevhc`, `nodok`, `kodeblok`
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$dataH[0]['tanggal'],
    'nourut'=>$noUrut,
    'noakun'=>$akunkredit,
    'keterangan'=>$tulisanpanen,
    'jumlah'=>$totalJumlah*(-1),
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>substr($row['kodeorg'],0,4),
    'kodekegiatan'=>$kodekegiatan,
    'kodeasset'=>'',
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>'',
    'noreferensi'=>$dataH[0]['notransaksi'],
    'noaruskas'=>'',
    'kodevhc'=>'',
    'nodok'=>'',
    'kodeblok'=>'',
    'revisi'=>'0',
	'kodesegment' => $row['kodesegment']
);

# Total D/K
$dataRes['header']['totaldebet'] = $totalJumlah;
$dataRes['header']['totalkredit'] = $totalJumlah;

#=== Insert Data ===
$errorDB = "";

# Header
$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);

if(!mysql_query($queryH)) {
    $errorDB .= "Header :".mysql_error()."\n test";
}

# Detail
if($errorDB=='') {
    foreach($dataRes['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		//echo "warning".$queryD;
        if(!mysql_query($queryD)) {
            $errorDB .= "Detail ".$key." :".mysql_error()."\n ini yang error";
        }
    }

    if($errorDB=='') {
		#=== Switch Jurnal to 1 ===
		# Cek if already posted
		$queryJ = selectQuery($dbname,'kebun_aktifitas',"jurnal","notransaksi='".
			$param['notransaksi']."'");
		$isJ = fetchData($queryJ);
		if($isJ[0]['jurnal']==1) {
			$errorDB .= "Data posted by another user";
		} else {
			$queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1),
				"notransaksi='".$dataH[0]['notransaksi']."'");
			if(!mysql_query($queryToJ)) {
				$errorDB .= "Posting Mark Error :".mysql_error()."\n";
			}
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
			if(!mysql_query($queryKonter)) {
				$errorDB .= "Update Counter Error :".mysql_error()."\n".$errorDB."___".$queryKonter;
			}
		}
	}
}

if($errorDB!="") {
    // Rollback
    $where = "nojurnal='".$nojurnal."'";
    $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
    $queryRB2 = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>0),
        "notransaksi='".$dataH[0]['notransaksi']."'");
    $queryRBKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
    if(!mysql_query($queryRB)) {
        $errorDB .= "Rollback 1 Error :".mysql_error()."\n";
    }
    if(!mysql_query($queryRB2)) {
        $errorDB .= "Rollback 2 Error :".mysql_error()."\n";
    }
    if(!mysql_query($queryRBKonter)) {
        $errorDB .= "Rollback Counter Error :".mysql_error()."\n";
    }
    echo "DB Error :\n".$errorDB."___".$queryRB2;
    //echo "DB Error :\n".$errorDB."___".$queryRB2;
    exit;
} else {
    // Posting Success
    
}
?>