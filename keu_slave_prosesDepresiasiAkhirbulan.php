<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
//hilangkan koma
$param['hartot']=str_replace(",","",isset($param['hartot'])? $param['hartot']: 0);

#contoh format parameter
#Array
#(
#       kodejurnal    
//      periode
//      keterangan
//      jumlah 
#)

#proses data
$kodeJurnal = $param['kodejurnal'];
$tipeasset = substr($kodeJurnal,3,2);
$blm=str_replace("-","",$param['periode']);

// Delete jurnal yang sudah terbentuk, jika proses di row 1
if($param['row']==1) {
	$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal 
		  like '%".$blm."28/".substr($_SESSION['empl']['lokasitugas'],0,4)."/".substr($kodeJurnal,0,3)."%'");
	if(!mysql_query($qDel)) {
		exit("Proses Delete Jurnal Error: ".mysql_error($conn));
	}
}

#ambil noakun pada table parameterjurnal
$str="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal 
	  where jurnalid='".$kodeJurnal."'";
$res=mysql_query($str);
if(mysql_num_rows($res)<1) {
	exit("Error: Tidak ada kode jurnal untuk ".$kodeJurnal);
} else {
	while($bar=mysql_fetch_object($res)) {
		$debet=$bar->noakundebet;
		$kredit=$bar->noakunkredit;
	}
	
	#periksa jika sudah pernah dilakukan
	$str="select * from ".$dbname.".keu_jurnalht where nojurnal 
		  like '%".$blm."28/".substr($_SESSION['empl']['lokasitugas'],0,4)."/".$kodeJurnal."%'";
	$res=mysql_query($str);
	if(mysql_num_rows($res)>0)
	{
	   exit("Error: Proses penarikan data Penyusutan sudah pernah dilakukan"); 
	}   
	
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$konter ='001';
	$tanggal=$param['periode']."-28";
	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-","",$tanggal)."/".substr($_SESSION['empl']['lokasitugas'],0,4)."/".$kodeJurnal."/".$konter;
	#======================== /Nomor Jurnal ============================
	
	// Default Segment
	$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
	
	# Prep Header
	$dataRes['header'] = array(
		'nojurnal'=>$nojurnal,
		'kodejurnal'=>$kodeJurnal,
		'tanggal'=>$tanggal,
		'tanggalentry'=>date('Ymd'),
		'posting'=>1,
		'totaldebet'=>$param['jumlah'],
		'totalkredit'=>-1*$param['jumlah'],
		'amountkoreksi'=>'0',
		'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
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
		'tanggal'=>$tanggal,
		'nourut'=>$noUrut,
		'noakun'=>$debet,
		'keterangan'=>$param['keterangan']." Periode:".$_POST['periode'],
		'jumlah'=>$param['jumlah'],
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$_SESSION['empl']['lokasitugas'],
		'kodekegiatan'=>'',
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment'=>$defSegment
	);
	$noUrut++;
	
	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tanggal,
		'nourut'=>$noUrut,
		'noakun'=>$kredit,
		'keterangan'=>$param['keterangan']." Periode:".$_POST['periode'],
		'jumlah'=>-1*$param['jumlah'],
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$_SESSION['empl']['lokasitugas'],
		'kodekegiatan'=>'',
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment'=>$defSegment
	);
	$noUrut++;
	
	#======================== Detail Kendaraan =============================
	if($tipeasset=='AB' || $tipeasset=='KD') {
		// Get All Vehicle
		$qVhc = selectQuery($dbname,'vhc_5master',"*",
							"kodeasset is not null and kodeorg='".$_SESSION['empl']['lokasitugas'].
							"' and kelompokvhc = '".$tipeasset."'","",true);
		$resVhc = fetchData($qVhc);
		$listAsset = array();
		$vhcAsset = array();
		foreach($resVhc as $row) {
			$listAsset[$row['kodeasset']] = $row['kodeasset'];
			$vhcAsset[$row['kodeasset']] = $row['kodevhc'];
		}
		 
		// Hitung Penyusutan Masing2 Kendaraan
		$qAsset = selectQuery($dbname,'sdm_daftarasset',"*","kodeasset in ('".implode("','",$listAsset)."') 
	and jlhblnpenyusutan-(((".substr($param['periode'],0,4)."*12)+".substr($param['periode'],5,2).")-((left(awalpenyusutan,4)*12)+RIGHT(awalpenyusutan,2)))>0
		and status=1
		 and awalpenyusutan <= '".$param['periode']."'");
		$resAsset = fetchData($qAsset);
		$nilaiDep = array();
		foreach($resAsset as $row) {
			if($row['persendecline']>0){ // Double Declining
				$thnawal=substr($row['awalpenyusutan'],0,4);
				$blnawal=substr($row['awalpenyusutan'],5,2);
				$total=($thnawal*12)+$blnawal;
			
				$thnNow=$_SESSION['org']['period']['tahun'];
				$blnNow=$_SESSION['org']['period']['bulan'];
				
				$totalBulanAwal = 12-$blnawal+1;
				$totalTahun = $thnNow-$thnawal-1;
				
				$totalNow=($thnNow*12)+$blnNow+1;
				$selisih=$totalNow-$total;
				$out=0;
				$akumNow = $sekarang = 0;
				
				// Depresiasi s/d akhir tahun
				$before = $sekarang = $row['hargaperolehan'];
				if($totalTahun>-1) {
					$akumNow += $totalBulanAwal/12 * $row['persendecline']/100 * $sekarang;
				}
				$sekarang -= $akumNow;
				
				// Depresiasi per Tahun
				if($totalTahun>0) {
					for($i=0;$i<$totalTahun;$i++) {
						$before = $sekarang;
						$akumNow += $sekarang * $row['persendecline']/100;
						$sekarang -= $sekarang * $row['persendecline']/100;
					}
				}
				
				// Depresiasi per Bulan
				$out = $sekarang*($row['persendecline']/100)/12;
				if($row['jlhblnpenyusutan']<$selisih) {
					$sekarang = $out = 0;
					//if($totalTahun>-1) {
					//	$out = $sekarang - ($bulanNow*$sekarang);
					//} else {
					//	$out = $sekarang - (($bulanNow-$bulanawal+1)*$sekarang);
					//}
				}
				
				$nilaiDep[$row['kodeasset']] = $out;
			}else { // Proporsional
				$x=mktime(0,0,0,  intval(substr($row['awalpenyusutan'],5,2)+$row['jlhblnpenyusutan']),15,substr($row['awalpenyusutan'],0,4));
			    $maxperiod=date('Y-m',$x);
			    if($_POST['periode']<=$maxperiod) {
					$nilaiDep[$row['kodeasset']] = $row['bulanan'];	 
			    }else{
			    	continue;
			    }
				
			}
		}
		
		// Get No Akun dari parameter VHCKL
		$qParam = selectQuery($dbname,'keu_5parameterjurnal',"*",
							  "kodeaplikasi='VHC' and jurnalid='VHCKL'");
		$resParam = fetchData($qParam);
		if(empty($resParam)) {
			exit("Error: Parameter Jurnal VHCKL belum terdaftar.\nSilahkan hubungi IT");
		}
		
		// Register Detail Jurnal Debet
		foreach($nilaiDep as $asset=>$nilai) {
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>$noUrut,
				'noakun'=>$resParam[0]['noakundebet'],
				'keterangan'=>"Depresiasi per Kendaraan: ".$vhcAsset[$asset],
				'jumlah'=>$nilai,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
				'noaruskas'=>'',
				'kodevhc'=>$vhcAsset[$asset],
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment'=>$defSegment
			);
			$noUrut++;
			
			// Register Detail Jurnal Kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>$noUrut,
				'noakun'=>$resParam[0]['noakunkredit'],
				'keterangan'=>"Depresiasi per Kendaraan: ".$vhcAsset[$asset],
				'jumlah'=>(-1)*$nilai,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment'=>$defSegment
			);
			$noUrut++;
		}
	}
	#======================== /Detail Kendaraan =============================
	
	#===========EXECUTE
	$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	if(!mysql_query($insHead)) {
		$headErr .= 'Insert Header Error : '.mysql_error()."\n";
	}

    if(empty($headErr)) {
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
        $detailErr = '';
        foreach($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
            if(!mysql_query($insDet)) {
                $detailErr .= "Insert Detail Error : ".mysql_error()."\n";
                break;
            }
        }

        if($detailErr=='') {
            # Header and Detail inserted
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
            }
        else {
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
}