<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param = $_POST;



$postJabatan = getPostingJabatan('baspk');
    
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
		exit("Error:Anda tidak memiliki hak untuk melakukan posting");
	}
}

//hilangkan koma
$param['jumlahrealisasi']=str_replace(",","",$param['jumlahrealisasi']);

#=== Get Data ===
# Get PT
$pt = getPT($dbname,$param['kodeorg']);
if($pt==false) {
    $pt = getHolding($dbname,$param['kodeorg']);
}

# Convert Tanggal
$tgl = tanggalsystem($param['tanggal']);

#periksa tanggal periode akuntansi===============
if($_SESSION['org']['period']['start']>$tgl)
    exit('Error:Tanggal diluar periode aktif');

// SPK
$queryH = selectQuery($dbname,'log_spkht',"*","notransaksi='".$param['notransaksi']."'");
$resH = fetchData($queryH);

# BASPK
$query = selectQuery($dbname,'log_baspk',"*",
    "notransaksi='".$param['notransaksi'].
    "' and kodeblok='".$param['blokalokasi'].
    "' and kodekegiatan='".$param['kodekegiatan'].
    "' and tanggal='".$tgl.
	"' and kodesegment='".$param['kodesegment']."'");
//print_r($query);exit;	
$data = fetchData($query);

#=== Cek if posted ===
$error0 = "";
if($data[0]['statusjurnal']==1) {
    $error0 .= $_SESSION['lang']['errisposted'];
}
if($error0!='') {
    echo "Data Error :\n".$error0;
    exit;
}

#=== Cek if data not exist ===
$error1 = "";
if(count($data)==0) {
    $error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
}
if($error1!='') {
    echo "Data Error :\n".$error1;
    exit;
}

# Get Akun
$kodeJurnal = 'SPK1';
$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun',
    "kodekegiatan='".$param['kodekegiatan']."'");
$optSupp = makeOption($dbname,'log_5klsupplier','kode,noakun',
    "kode='".substr($param['koderekanan'],0,4)."'");

#======================== Nomor Jurnal =============================
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Transform No Jurnal dari No Transaksi
$nojurnal = $tgl."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
#======================== /Nomor Jurnal ============================
# Alokasi Blok
if(strlen($param['blokalokasi'])>5) {//di edit ama ginting dari 10 jadi 5
    $blok = $param['blokalokasi'];
} else {
    $blok = '';
}

#kusus jika project
$kodeasset='';
if(substr($param['blokalokasi'],0,2)=='AK' or substr($param['blokalokasi'],0,2)=='PB')
{
	#ambil akun aktiva dalam konstruksi
	$tipeasset=substr($param['blokalokasi'],3,3);
	$tipeasset=  str_replace("0","",$tipeasset);
	$str="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."'";
	$res=mysql_query($str);
	if(mysql_num_rows($res)<1)
	{
		exit(" Error: Akun aktiva dalam konstruksi untuk ".$tipeasset." belum disetting dari keuangan->setup->tipeasset");
	}
	else
	{
		while($bar=mysql_fetch_object($res))
		{
		   if($bar->akunak==''){
			   exit(" Error: Akun aktiva dalam konstruksi untuk ".$tipeasset." belum disetting dari keuangan->setup->tipeasset");
		   }
		   else{
//                $param['kodekegiatan']='';
			   $kodeasset=$param['blokalokasi'];    
			   $blok='';
			   $optKeg[$param['kodekegiatan']]=$bar->akunak;
		   }
		}
	} 
}







## cek kurs jika bukan idr

$str="select matauang from ".$dbname.".log_spkht where notransaksi='".$param['notransaksi']."' ";
$res=mysql_query($str) or die (mysql_error($conn));
$bar=mysql_fetch_assoc($res);
	$mtuang=$bar['matauang'];

$str="select kurs from ".$dbname.".setup_matauangrate where daritanggal='".$tgl."' and kode='".$mtuang."' ";
$res=mysql_query($str) or die (mysql_error($conn));
$bar=mysql_fetch_assoc($res);
	$kurs=$bar['kurs'];

if($mtuang!='IDR'){
	if($kurs==0 || $kurs==''){
		exit("Warning : Please Input Kurs ".$kode." in ".tanggalnormal($tgl)." ");
	}else{
		$kurs=$kurs;
	}
}else{
	$kurs=1;
}	

$param['jumlahrealisasi']=$param['jumlahrealisasi']*$kurs;







// Get Akun Ppn
$qAkun = selectQuery($dbname,'setup_parameterappl',"nilai",
					 "kodeaplikasi='TX' and kodeparameter='PPNINV'");
$resAkun = fetchData($qAkun);
$akunPpn = empty($resAkun)? "": $resAkun[0]['nilai'];

// Get Nilai PPn & Pph
$optPajak = makeOption($dbname,'log_spk_tax',"noakun,nilai","notransaksi='".
					   $param['notransaksi']."' and kodeorg='".$param['kodeorg']."'");

// Proporsi Rupiah
$proporsi = $param['jumlahrealisasi'] / $resH[0]['nilaikontrak'];

// Pisah Ppn dan Pph
$ppn = $pph = 0;
foreach($optPajak as $noakun=>$nilai) {
	if($noakun==$akunPpn) {
		$ppn += $nilai * $proporsi;
	} else {
		$pph += $nilai * $proporsi;
	}
}


$hutang = $param['jumlahrealisasi'] + $ppn - $pph;

# Prep Header
$dataRes['header'] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$kodeJurnal,
    'tanggal'=>$tgl,
    'tanggalentry'=>date('Ymd'),
    'posting'=>0,
    'totaldebet'=>$param['jumlahrealisasi'],
    'totalkredit'=>-1*$param['jumlahrealisasi'],
    'amountkoreksi'=>'0',
    'noreferensi'=>$param['notransaksi'],
    'autojurnal'=>'1',
    'matauang'=>'IDR',
    'kurs'=>'1',
    'revisi'=>'0'
);

# Data Detail
$noUrut = 1;

# Debet
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tgl,
    'nourut'=>$noUrut,
    'noakun'=>$optKeg[$param['kodekegiatan']],
    'keterangan'=>'Realisasi SPK '.$param['kodeorg'].'/'.$param['notransaksi'],
    'jumlah'=>$param['jumlahrealisasi'],
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$param['kodeorg'],
    'kodekegiatan'=>$param['kodekegiatan'],
    'kodeasset'=>$kodeasset,
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>'',
    'noreferensi'=>$param['notransaksi'],
    'noaruskas'=>'',
    'kodevhc'=>'',
    'nodok'=>'',
    'kodeblok'=>$blok,
    'revisi'=>'0',
	'kodesegment' => $param['kodesegment']
);
$noUrut++;

# Kredit
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tgl,
    'nourut'=>$noUrut,
    'noakun'=>$optSupp[substr($param['koderekanan'],0,4)],
    'keterangan'=>'Realisasi SPK '.$param['kodeorg'].'/'.$param['notransaksi'],
    'jumlah'=>-1*$hutang,
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$param['kodeorg'],
    'kodekegiatan'=>$param['kodekegiatan'],
    'kodeasset'=>'',
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>$param['koderekanan'],
    'noreferensi'=>$param['notransaksi'],
    'noaruskas'=>'',
    'kodevhc'=>'',
    'nodok'=>'',
    'kodeblok'=>$blok,
    'revisi'=>'0',
	'kodesegment' => $param['kodesegment']
);
$noUrut++;

foreach($optPajak as $noakun=>$nilai) {
	if($noakun!=$akunPpn) {
		$nilai = $nilai * (-1);
		$strPajak = 'Pph';
	} else {
		$strPajak = 'PPn';
	}
	$nilai = $nilai * $proporsi;
	
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tgl,
		'nourut'=>$noUrut,
		'noakun'=>$noakun,
		'keterangan'=>$strPajak.' BASPK '.$param['kodeorg'].'/'.$param['notransaksi'],
		'jumlah'=>$nilai,
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$param['kodeorg'],
		'kodekegiatan'=>$param['kodekegiatan'],
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>$param['koderekanan'],
		'noreferensi'=>$param['notransaksi'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>$blok,
		'revisi'=>'0',
		'kodesegment' => $param['kodesegment']
	);
	$noUrut++;
}

# Total D/K
$dataRes['header']['totaldebet'] = $hutang + $pph;
$dataRes['header']['totalkredit'] = $hutang + $pph;

#========================== Proses Insert dan Update ==========================
#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
$headErr = '';


$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
if(!mysql_query($insHead)) {
    $headErr .= 'Insert Header Error : '.mysql_error()."\n";
}

if($headErr=='') {
    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
    $detailErr = '';
    foreach($dataRes['detail'] as $row) {
        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
        if(!mysql_query($insDet)) {
            $detailErr .= "Insert Detail Error : ".mysql_error()."\n".$insDet;
            break;
        }
    }
    
    if($detailErr=='') {
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
            "kodeorg='".$_SESSION['org']['kodeorganisasi'].
            "' and kodekelompok='".$kodeJurnal."'");
        if(!mysql_query($updJurnal)) {
            echo "Update Kode Jurnal Error : ".mysql_error()."\n";
            # Rollback if Update Failed
            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
            if(!mysql_query($RBDet)) {
                echo "Rollback Delete Header Error : ".mysql_error()."\n";
                exit;
            }
            exit;
        } else {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Status Posting
            $updTrans = updateQuery($dbname,'log_baspk',array('statusjurnal'=>1),
                "notransaksi='".$param['notransaksi'].
                "' and kodeblok='".$param['blokalokasi'].
                "' and kodekegiatan='".$param['kodekegiatan'].
                "' and tanggal='".$tgl.
				"' and kodesegment='".$param['kodesegment']."'");
            if(!mysql_query($updTrans)) {
                echo "Update Status Jurnal Error : ".mysql_error()."\n";
                # Rollback if Update Failed
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                if(!mysql_query($RBDet)) {
                    echo "Rollback Delete Header Error : ".mysql_error()."\n";
                    exit;
                }
                $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                    "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                    "' and kodekelompok='".$kodeJurnal."'");
                if(!mysql_query($RBJurnal)) {
                    echo "Rollback Update Jurnal Error : ".mysql_error()."\n";
                    exit;
                }
                exit;
            } else {
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Success
                echo '1';
            }
        }
    } else {
        echo $detailErr;
        # Rollback, Delete Header
        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
        if(!mysql_query($RBDet)) {
            echo "Rollback Delete Header Error : ".mysql_error();
            exit;
        }
    }
} else {
    echo $headErr;
    exit;
}
?>