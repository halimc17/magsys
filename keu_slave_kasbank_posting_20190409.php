<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

$kegiatan="SELECT * FROM ".$dbname.". setup_parameterappl WHERE kodeaplikasi = 'TX'";
$query=mysql_query($kegiatan) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $excludeacc[$res['nilai']]=$res['nilai'];
}

#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".
    $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
    "' and noakun='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."' limit 1");
$dataH = fetchData($queryH);

# Detail
$queryD = selectQuery($dbname,'keu_kasbankdt',"*","notransaksi='".
    $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
    "' and noakun2a='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'");
$dataD = fetchData($queryD);

#=== Cek Jumlah Detail dan Header harus sama ===
$tmpJml = 0;
foreach($dataD as $row) {
    $tmpJml += $row['jumlah'];
}
$selisih = abs($tmpJml - $dataH[0]['jumlah']);
if($selisih > 0.001) {
    echo "Warning : Amount on header difference to the amount in detail\n";
    echo "Posting Failed";
    exit;
}

#=== Cek if posted ===
$error0 = "";
if($dataH[0]['posting']==1) {
    $error0 .= $_SESSION['lang']['errisposted'];
}
if($error0!='') {
    echo "Data Error :\n".$error0;
    exit;
}
#====cek periode
$tgl = str_replace("-","",$dataH[0]['tanggal']);
if($_SESSION['org']['period']['start']>$tgl)
    exit('Error:Date beyond active period');

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
#=======cek kurs mata uang header dan detail
$ceko=0;
foreach($dataD as $rowdt=>$isiData){
	if($dataH[0]['matauang']!=$isiData['matauang']){
		$ceko+=1;
	}
}
if($ceko!=0){
	exit('warning: Matauang header dan detail berbeda!!');
}


#=== Cek if hutang unit ========================================================
if($dataH[0]['hutangunit']==1) {
    $pembayarhutang=$param['kodeorg'];    
    $pemilikhutang=$dataH[0]['pemilikhutang'];
    
    #cek jika pemilik hutang dengan kodeorg pemilik akun piutang sama atau tidak
    $rwError=0;
	$sCek="select distinct noakun from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and hutangunit1=1  and kodeorg='".$param['kodeorg'].
    "' and noakun like '1210%' and tipetransaksi='".$param['tipetransaksi']."'";
	$qCek=mysql_query($sCek) or die(mysql_error($conn));
	while($rCek=mysql_fetch_assoc($qCek)){
		$whrdt="akunpiutang='".$rCek['noakun']."'";
		$optCek=makeOption($dbname,'keu_5caco','akunpiutang,kodeorg',$whrdt);
		if($optCek[$rCek['noakun']]!=$pemilikhutang){
			$rwError+=1;
			$dtAkun[$rCek['noakun']]=$rCek['noakun'];
		}
	}
	if($rwError!=0){
		echo"<pre>";
		print_r($dtAkun);
		echo"</pre>";
		exit('warning: Noakun diatas bukan milik '.$pemilikhutang);
	}

    // kalo periode akuntansi unit beda, ga bisa diposting...
    $periodepembayar=makeOption($dbname,'setup_periodeakuntansi','kodeorg,periode',"kodeorg = '".$pembayarhutang."' and tutupbuku = 0");
    $periodepemilik=makeOption($dbname,'setup_periodeakuntansi','kodeorg,periode',"kodeorg = '".$pemilikhutang."' and tutupbuku = 0");
    if($periodepembayar[$pembayarhutang]!=$periodepemilik[$pemilikhutang]){
        echo "Warning : ".$_SESSION['lang']['periodeakuntansi']." do not match.\n".$pembayarhutang." : ".$periodepembayar[$pembayarhutang]."\n".$pemilikhutang." : ".$periodepemilik[$pemilikhutang];
        exit;
    }
    
    $noakunhutang=$dataH[0]['noakunhutang'];
    $kodejurnal='M';
    $tanggal=$dataH[0]['tanggal'];
    $tanggal=tanggalnormal($tanggal);
    $tanggal=tanggalsystem($tanggal);

    #=============== Get Induk Pemilik Hutang
    $whereNomilhut = "kodeorganisasi='".
        $pemilikhutang."'";
    $query = selectQuery($dbname,'organisasi','induk',
        $whereNomilhut);
    $noKon = fetchData($query);
    $indukpemilikhutang = $noKon[0]['induk'];
    
    #=============== Get Induk Pembayar Hutang
    $whereNoyarhut = "kodeorganisasi='".
        $param['kodeorg']."'";
    $query = selectQuery($dbname,'organisasi','induk',
        $whereNoyarhut);
    $noKon = fetchData($query);
    $indukpembayarhutang = $noKon[0]['induk'];
    
    if($indukpemilikhutang==$indukpembayarhutang)$jenisinduk='intra'; else $jenisinduk='inter';

    #=============== Get Nomor Jurnal Otomatis (pemilikhutang)
//    $whereNo = "kodekelompok='".$kodejurnal."' and kodeorg='".
//        $pemilikhutang."'";
    $whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".
        $indukpemilikhutang."'";
    $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
        $whereNoindukph);
    
    $noKon = fetchData($query);
    $tmpC = $noKon[0]['nokounter'];
    $tmpC++;
    $konteroto = addZero($tmpC,3);
    $nojuroto = $tanggal."/".
        $pemilikhutang."/".$kodejurnal."/".
        $konteroto;
        
    #=============== Get Nomor Akun Caco
    // ini ga dipake soale dipilih secara manual sama usernya pas nginput kasbank
    $whereNocaco = "jenis='".$jenisinduk."' and kodeorg='".
        $pemilikhutang."'";
    $query = selectQuery($dbname,'keu_5caco','akunpiutang',
        $whereNocaco);
    $noKon = fetchData($query);
    $noakuncaco = $noKon[0]['akunpiutang'];

    #=============== Get Nomor Akun Caco Lawannya
    // ini yang dipake
    $whereNocacol = "jenis='".$jenisinduk."' and kodeorg='".
        $pembayarhutang."'";
    $query = selectQuery($dbname,'keu_5caco','akunpiutang',
        $whereNocacol);
    $noKon = fetchData($query);
    $noakuncacol = $noKon[0]['akunpiutang'];
}else{
	#cek jika detail ada hutang unit tetapi headernya belum tercentang
	$sCek="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and hutangunit1=1  and kodeorg='".$param['kodeorg'].
    "' and noakun2a='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'";
	$qCek=mysql_query($sCek) or die(mysql_error($conn));
	$rCek=mysql_num_rows($qCek);
	if($rCek>0){
		exit('warning: Hutang unit pada form header belum tersimpan.');
	}
}

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

#=== Transform Data ===
$dataRes['header'] = array();
$dataRes['detail'] = array();
$dataResoto['header'] = array();
$dataResoto['detail'] = array();

#1. Data Header
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$dataD[0]['kode']."'");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Prep No Jurnal
$nojurnal = str_replace('-','',$dataH[0]['tanggal'])."/".$dataH[0]['kodeorg']."/".
    $dataD[0]['kode']."/".$konter;

# Prep Header
$dataRes['header'] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$dataD[0]['kode'],
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

# Prep Header Otomatis =========================================================
if(isset($nojuroto)) {
	$dataResoto['header'] = array(
		'nojurnal'=>$nojuroto,
		'kodejurnal'=>$kodejurnal,
		'tanggal'=>$dataH[0]['tanggal'],
		'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>'0',
		'totalkredit'=>'0',
		'amountkoreksi'=>'0',
		'noreferensi'=>$pembayarhutang.$dataH[0]['notransaksi'],
		'autojurnal'=>'1',
		'matauang'=>'IDR',
		'kurs'=>'1',
		'revisi'=>'0'    
	);
}

// Jika mata uang selain IDR, cek apakah ada selisih kurs
/**
 * [START] Jurnal Selisih Kurs
 */
$noUrut = 1;
$totalJumlah = 0;
if($dataH[0]['matauang']!='IDR') {
	$invRp = $invKurs = $invList = $invSupp = $invSegment = array();
	foreach($dataD as $row) {
		setIt($invRp[$row['keterangan1']],0);
		$invRp[$row['keterangan1']] += $row['jumlah'];
		$invList[$row['keterangan1']] = $row['keterangan1'];
		$invKurs[$row['keterangan1']] = $row['kurs'];
		$invSupp[$row['keterangan1']] = $row['kodesupplier'];
		$invSegment[$row['keterangan1']] = $row['kodesegment'];
	}
	
	// Get Kurs PO
	$invPoKurs = makeOption($dbname,'keu_tagihanht','noinvoice,kurs',
							"noinvoice in ('".implode("','",$invList)."')");
	if(empty($invPoKurs)) {
		$invPoKurs = makeOption($dbname,'keu_penagihanht','noinvoice,kurs',
							"noinvoice in ('".implode("','",$invList)."')");
	}
	
	// Iterasi tiap Invoice
	foreach($invPoKurs as $invoice=>$kurs) {
		if($kurs>0 and $kurs!=$invKurs[$invoice]) {
			// Get Akun Selisih Kurs
			$qParam = selectQuery($dbname,'keu_5parameterjurnal',"noakundebet,noakunkredit",
								  "kodeaplikasi='KURS' and jurnalid='KRS01'");
			$resParam = fetchData($qParam);
			if(empty($resParam)) {
				exit("Warning: Akun selisih kurs belum ada\n
					 Silahkan hubungi IT dengan melampirkan pesan error ini");
			} else {
				$kursDebet = $resParam[0]['noakundebet'];
				$kursKredit = $resParam[0]['noakunkredit'];
			}
			$selKurs = abs($kurs - $invKurs[$invoice]);
			echo $kurs.'|';
			echo $invKurs[$invoice].'|';
			if($dataH[0]['tipetransaksi']=='K') {
				/**
				 * Transaksi Keluar
				 */
				if($kurs < $invKurs[$invoice]) {
					// Trans Keluar Selisih Rugi
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$kursDebet,
						'keterangan'=>'Selisih Kurs Invoice '.$invoice,
						'jumlah'=>$invRp[$invoice] * $selKurs,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>'',
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$invSupp[$invoice],
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$invoice,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $invSegment[$invoice]
					);
					$totalJumlah+=$invRp[$invoice] * $selKurs;
					$noUrut++;
				} else {
					// Trans Keluar Selisih Untung
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$kursKredit,
						'keterangan'=>'Selisih Kurs Invoice '.$invoice,
						'jumlah'=>$invRp[$invoice] * $selKurs * (-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>'',
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$invSupp[$invoice],
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$invoice,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $invSegment[$invoice]
					);
					$noUrut++;
					$totalJumlah+=$invRp[$invoice] * $selKurs * (-1);
				}
			} elseif($dataH[0]['tipetransaksi']=='M') {
				/**
				 * Transaksi Masuk
				 */
				if($kurs < $invKurs[$invoice]) {
					// Trans Masuk Selisih Untung
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$kursKredit,
						'keterangan'=>'Selisih Kurs Invoice '.$invoice,
						'jumlah'=>$invRp[$invoice] * $selKurs * (-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>'',
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$invSupp[$invoice],
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$invoice,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $invSegment[$invoice]
					);
					$noUrut++;
					$totalJumlah+=$invRp[$invoice] * $selKurs * (-1);
				} else {
					// Trans Masuk Selisih Rugi
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$kursDebet,
						'keterangan'=>'Selisih Kurs Invoice '.$invoice,
						'jumlah'=>$invRp[$invoice] * $selKurs,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>'',
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$invSupp[$invoice],
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$invoice,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $invSegment[$invoice]
					);
					$noUrut++;
					$totalJumlah+=$invRp[$invoice] * $selKurs;
				}
			}
		}
	}
}

/**
 * [END] Jurnal Selisih Kurs
 */

#2. Data Detail
# Detail (Many)
foreach($dataD as $row) {    
    if(substr($row['kode'],1,1)=='M') {
        $jumlah = $row['jumlah']*(-1);
    } else {
        $jumlah = $row['jumlah'];
    }
    $dKurs=1;
    $dMtUang='IDR';
    if($row['matauang']!='IDR')
    {
		if(isset($invPoKurs[$row['keterangan1']])) {
			$dKurs=$invPoKurs[$row['keterangan1']];
		} else {
			$dKurs=$row['kurs'];
		}
		$jumlah = $jumlah * $dKurs;
    }
    $dataRes['detail'][] = array(
        'nojurnal'=>$nojurnal,
        'tanggal'=>$dataH[0]['tanggal'],
        'nourut'=>$noUrut,
        'noakun'=>$row['noakun'],
        'keterangan'=>$row['keterangan2'],
        'jumlah'=>$jumlah,
        'matauang'=>'IDR',
        'kurs'=>'1',
        'kodeorg'=>$row['kodeorg'],
        'kodekegiatan'=>$row['kodekegiatan'],
        'kodeasset'=>$row['kodeasset'],
        'kodebarang'=>$row['kodebarang'],
        'nik'=>$row['nik'],
        'kodecustomer'=>$row['kodecustomer'],
        'kodesupplier'=>$row['kodesupplier'],
        'noreferensi'=>$dataH[0]['notransaksi'],
        'noaruskas'=>$row['noaruskas'],
        'kodevhc'=>$row['kodevhc'],
        'nodok'=>$row['nodok'],
        'kodeblok'=>$row['orgalokasi'],
		'revisi'=>'0',
		'kodesegment' => $row['kodesegment']
    );
	$totalJumlah += $jumlah;
	$noUrut++;
}

# Detail (One)
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$dataH[0]['tanggal'],
    'nourut'=>$noUrut,
    'noakun'=>$dataH[0]['noakun'],
    'keterangan'=>$dataH[0]['keterangan'],
    'jumlah'=>$totalJumlah * (-1),
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$dataH[0]['kodeorg'],
    'kodekegiatan'=>'',
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

#2. Data Detail Otomatis =======================================================
# Detail (Many)
$noUrut = 1;
$totalJumlahOto = 0;
foreach($dataD as $row) {
    
    // default: lempar ke unit
    $ok=true;
    if(!empty($excludeacc))foreach($excludeacc as $acc){
        if(substr($row['noakun'],0,3)==$acc){
            // kalo exclude, jangan lempar ke unit
            $ok=false;
        }
    }
    
    // kalo detailnya bukan hutang unit, jangan lempar ke unit
    if($row['hutangunit1']==0)$ok=false;
    
    // kalo OK, lempar ke unit
    if($ok){
        if(substr($row['kode'],1,1)=='M') {
            $jumlah = $row['jumlah']*(-1);
        } else {
            $jumlah = $row['jumlah'];
        }
        $dKurs=1;
        $dMtUang='IDR';
        if($row['matauang']!='IDR')
        {
            //$dMtUang=$row['matauang'];
            $dKurs=$row['kurs'];
            $jumlah=$jumlah*$dKurs;
        }
        $dataResoto['detail'][] = array(
            'nojurnal'=>$nojuroto,
            'tanggal'=>$dataH[0]['tanggal'],
            'nourut'=>$noUrut,
            'noakun'=>$noakunhutang,
            'keterangan'=>$row['keterangan2'],
            'jumlah'=>$jumlah,
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$pemilikhutang,
            'kodekegiatan'=>$row['kodekegiatan'],
            'kodeasset'=>$row['kodeasset'],
            'kodebarang'=>$row['kodebarang'],
            'nik'=>$row['nik'],
            'kodecustomer'=>$row['kodecustomer'],
            'kodesupplier'=>$row['kodesupplier'],
            'noreferensi'=>$pembayarhutang.$dataH[0]['notransaksi'],
            'noaruskas'=>$row['noaruskas'],
            'kodevhc'=>$row['kodevhc'],
            'nodok'=>$row['nodok'],
            'kodeblok'=>$row['orgalokasi'],
			'revisi'=>'0',
			'kodesegment' => $row['kodesegment']
        );
        $totalJumlahOto += $jumlah;
        $noUrut++;                    
    }
}


# Detail (One) Otomatis ========================================================
if(isset($nojuroto)) {
	$dataResoto['detail'][] = array(
		'nojurnal'=>$nojuroto,
		'tanggal'=>$dataH[0]['tanggal'],
		'nourut'=>$noUrut,
		'noakun'=>$noakuncacol,
		'keterangan'=>$dataH[0]['keterangan'],
		'jumlah'=>$totalJumlahOto*(-1),
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$pemilikhutang,
		'kodekegiatan'=>'',
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>$pembayarhutang.$dataH[0]['notransaksi'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment' => $row['kodesegment']
	);
}

# Total D/K
$dataRes['header']['totaldebet']=$totalJumlah; 
$dataRes['header']['totalkredit']=$totalJumlah*(-1); 
$dataResoto['header']['totaldebet']=$totalJumlahOto; 
$dataResoto['header']['totalkredit']=$totalJumlahOto*(-1);

#=== Insert Data ===
$errorDB = "";

# Header
$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
if(!mysql_query($queryH)) {
    $errorDB .= "Header :".mysql_error()."\n";
}

# Header Otomatis ==============================================================
if($dataH[0]['hutangunit']==1) { 
    $queryH = insertQuery($dbname,'keu_jurnalht',$dataResoto['header']);
    if(!mysql_query($queryH)) {
        $errorDB .= "Header :".mysql_error()."\n";
    }    
}

# Detail
if($errorDB=='') {
    foreach($dataRes['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);

        if(!mysql_query($queryD)) {
            $errorDB .= "Detail ".$key." :".mysql_error()."\n";
        }
    }

    #=== Switch Jurnal to 1 ===
    # Cek if already posted
    $queryJ = selectQuery($dbname,'keu_kasbankht',"posting","notransaksi='".
        $param['notransaksi']."' and kodeorg='".$param['kodeorg']."'");
    $isJ = fetchData($queryJ);
    if($isJ[0]['posting']==1) {
        $errorDB .= "Data changed by other user";
    } else {
        $queryToJ = updateQuery($dbname,'keu_kasbankht',array('posting'=>1),
            "notransaksi='".$dataH[0]['notransaksi']."' and kodeorg='".$dataH[0]['kodeorg']."' and tanggal='".$dataH[0]['tanggal']."'");
        if(!mysql_query($queryToJ)) {
            $errorDB .= "Posting Flag Error :".mysql_error()."\n";
        }
    }
}

# Detail Otomatis ==============================================================
if($dataH[0]['hutangunit']==1) {
if($errorDB=='') {
    foreach($dataResoto['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
        if(!mysql_query($queryD)) {
            $errorDB .= "Detail ".$key." :".mysql_error()."\n";
        }
    }
}
}

if($errorDB!="") {
    // Rollback
    $where = "nojurnal='".$nojurnal."'";
    $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
    $queryRB2 = updateQuery($dbname,'keu_kasbankht',array('posting'=>0),
        "notransaksi='".$dataH[0]['notransaksi']."' and kodeorg='".$dataH[0]['kodeorg']."'");
    if(!mysql_query($queryRB)) {
        $errorDB .= "Rollback 1 Error :".mysql_error()."\n";
    }
    if(!mysql_query($queryRB2)) {
        $errorDB .= "Rollback 2 Error :".mysql_error()."\n";
    }
    
    // Rollback Otomatis =======================================================
if($dataH[0]['hutangunit']==1) {    
    $whereoto = "nojurnal='".$nojuroto."'";
    $queryRBoto = "delete from `".$dbname."`.`keu_jurnalht` where ".$whereoto;
    if(!mysql_query($queryRBoto)) {
        $errorDB .= "Rollback 3 Error :".mysql_error()."\n";
    }
}    
    echo "DB Error :\n".$errorDB;
    exit;
} else {
    // Posting Success
    #=== Add Counter Jurnal ===
    $queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$dataD[0]['kode']."'");
    $errCounter = "";
    if(!mysql_query($queryJ)) {
        $errCounter.= "Update Counter Parameter Jurnal Error :".mysql_error()."\n";
    }
    if($errCounter!="") {
        $queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$dataD[0]['kode']."'");
        $errCounter = "";
        if(!mysql_query($queryJRB)) {
            $errorJRB .= "Rollback Parameter Jurnal Error :".mysql_error()."\n";
        }
        echo "DB Error :\n".$errorJRB;
        exit;
    }
    #=== Add Counter Jurnal Otomatis === =======================================
if($dataH[0]['hutangunit']==1) {    
    $queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konteroto),
        "kodeorg='".$indukpemilikhutang."' and kodekelompok='".$kodejurnal."'");
    $errCounter = "";
    if(!mysql_query($queryJ)) {
        $errCounter.= "Update Counter Parameter Jurnal Error :".mysql_error()."\n";
    }
    
    if($errCounter!="") {
        $queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array($noKon[0]['nokounter']),
            "kodeorg='".$indukpemilikhutang."' and kodekelompok='".$kodejurnal."'");
        $errCounter = "";
        if(!mysql_query($queryJRB)) {
            $errorJRB .= "Rollback Parameter Jurnal Error :".mysql_error()."\n";
        }
        echo "DB Error :\n".$errorJRB;
        exit;
    }
} 

    #=== Update Perdin dibayar sdm_pjdinasht === =======================================
	if($param['tipetransaksi']=='K'){
		$nodok='';
		$ada=0;
		foreach($dataD as $row) {
		if($row['nodok']!=''){
			if($nodok!=$row['nodok']){
				$nodok=$row['nodok'];
				$ada=0;
			}
			$querySlcperdin = "select notransaksi from ".$dbname.".sdm_pjdinasht where dibayar=0 and notransaksi='".$row['nodok']."'";
			$resperdin=mysql_query($querySlcperdin);
			while($bar=mysql_fetch_object($resperdin)){
				$ada=1;
			}
			if(substr($row['nodok'],0,4)==$_SESSION['empl']['lokasitugas']){
				if($ada==1){
					$queryUpdperdin = "update ".$dbname.".sdm_pjdinasht set dibayar=dibayar+".$row['jumlah'].",tglbayar='".$dataH[0]['tanggal']."' where notransaksi='".$row['nodok']."'";
					if(!mysql_query($queryUpdperdin)){
						$errorDB .= "Update Nilai Perdin Error :".mysql_error()."\n";
					}
				}else{
					$queryUpdperdin = "update ".$dbname.".sdm_pjdinasdt set jumlahdibayar=jumlahdibayar+".$row['jumlah']." where jumlahhrd=".$row['jumlah']." and notransaksi='".$row['nodok']."'";
					if(!mysql_query($queryUpdperdin)){
						$errorDB .= "Update Nilai Perdin Error :".mysql_error()."\n";
					}
				}
			}
		}
		}
	}
}
?>