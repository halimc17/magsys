<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
$param = $_POST;
$nilaisal=$param['jumlah']*$param['harga'];
$hargaInput = $param['harga'];

#periksa periode gudang
$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodegudang']."' and tutupbuku=0";
$res=mysql_query($str);

if($param['jenis']=='out' and $param['jumlah']<=0) {
	exit('Warning: Untuk transaksi keluar, jumlah harus lebih besar dari 0');
}

if(mysql_num_rows($res)<1) { // Validasi Periode Gudang
    exit('Warning: Periode gudang belum ada');
} else {
    while($bar=mysql_fetch_object($res)){
        $periode=$bar->periode;
		$tglmax = $bar->tanggalsampai;
		$tglmin = $bar->tanggalmulai;
    }  
    #periksa apakah transaksi sudah ada:
    $str="select * from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
    $res=mysql_query($str);
	
	if(date('Y-m-d')<$tglmin) {
		exit("Warning: Periode aktif lebih besar dari hari ini");
	}
	
	// Ganti tanggal hari ini menjadi tanggal akhir periode jika periode berbeda
	if(date('Y-m-d')>$tglmax) {
		$currTgl = $tglmax;
	} else {
		$currTgl = date('Y-m-d');
	}
	
	/**
	 * [START] Rekalkulasi
	 */
	// Get All Transaksi
	$qHist = selectQuery($dbname,'log_transaksi_vw',"*","tanggal like '".$periode."%' and kodebarang='".
						 $param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and statussaldo=1","tanggal asc, tipetransaksi asc");
	$resHist = fetchData($qHist);
	
	// Get Saldo Awal
	if(mysql_num_rows($res)>0) {
		while($bar=mysql_fetch_object($res)) {
            $awalQty=$bar->saldoawalqty;
            $awalHarga=$bar->hargaratasaldoawal;
            $awalNilai=$bar->nilaisaldoawal;
        }
	} else {
		$awalQty=0;
		$awalHarga=0;
		$awalNilai=0;
	}
	$qtyIn = 0;
	$qtyOut = 0;
	
	// Rekalkulasi Transaksi
	$currHarga = $awalHarga;
	$currQty = $awalQty;
	$lastIn = $lastOut = $currHarga;
	foreach($resHist as $row) {
		if($currTgl<$row['tanggal']) $currTgl=$row['tanggal']; // Update Tanggal jika ada transaksi yang lebih akhir
		if($row['tipetransaksi']<5) { // Penerimaan
			$nilai1 = $row['jumlah']*$row['hargasatuan']; // Nilai Transaksi
			$nilai2 = $currQty*$currHarga; // Nilai Stok
			$currHarga = ($nilai1+$nilai2) / ($row['jumlah']+$currQty);
			$currQty += $row['jumlah'];
			$qtyIn += $row['jumlah'];
			$lastIn = $currHarga;
		} else { // Pengeluaran
			$currQty -= $row['jumlah'];
			$lastOut = $currHarga;
			$qtyOut += $row['jumlah'];
		}
	}
	/**
	 * [END] Rekalkulasi
	 */
	
	/**
	 * [START] Transaksi Adjustment
	 */
	// Jenis Transaksi
	$beforeQty = $currQty;
	if($param['jenis']=='in') {
		$tipeTrans = 'GR';
		$tipe = 4;
		$qtyIn += $param['jumlah'];
		
		// Rekalkulasi dengan transaksi adjustment
		if($param['harga'] > 0) {
			$nilai1 = $param['jumlah']*$param['harga']; // Nilai Transaksi
		} else {
			$nilai1 = 0;
		}
		$nilai2 = $currQty*$currHarga; // Nilai Stok
		$currHarga = ($nilai1+$nilai2) / ($param['jumlah']+$currQty);
		$currQty += $param['jumlah'];
		$lastIn = $currHarga;
	} elseif($param['jenis']=='out') {
		$tipeTrans = 'GI';
		$tipe = 8;
		$qtyOut += $param['jumlah'];
		// Rekalkulasi dengan transaksi adjustment

		if($param['harga']==0){
				$nilai1 = 0;
				$nilai2 = $currQty*$currHarga; // Nilai Stok
                                if(($currQty-$param['jumlah'])<=0)
                                  $currHarga=0;
                                else
                                  $currHarga = ($nilai1+$nilai2) / ($currQty-$param['jumlah']);
                                //ini dia
		} 
		$currQty -= $param['jumlah'];
		$lastOut = $currHarga;
		
		if($currQty<0) {
			exit("Warning: Saldo tidak mencukupi\nSaldo: ".$beforeQty);
		}
	}
	
	// Generate No Transaksi
	$whereTrans = "right(notransaksi,9)='".$tipeTrans."-".$param['kodegudang'].
		"' and left(notransaksi,6)='".str_replace('-','',$periode)."' and substr(notransaksi,7,1)<>'M'";
	$qTrans = "select max(left(notransaksi,11)) as maxnum from ".$dbname.
		".log_transaksiht where ".$whereTrans;
	$resTrans = fetchData($qTrans);
	if(empty($resTrans[0]['maxnum'])) {
		$noTrans = str_replace('-','',$periode)."00001-".$tipeTrans."-".$param['kodegudang'];
	} else {
		$noTrans = ($resTrans[0]['maxnum']+1)."-".$tipeTrans."-".$param['kodegudang'];
	}
	
	// Get PT
	$qPt = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".
					   substr($param['kodegudang'],0,4)."'");
	$resPt = fetchData($qPt);
	if(empty($resPt)) {
		exit("Validation Error: No PT tidak ada");
	}
	
	// Get Satuan
	$optSat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',
						 "kodebarang='".$param['kodebarang']."'");
	
	// Data Header
	$dataH = array(
		'tipetransaksi' => $tipe,
		'notransaksi' => $noTrans,
		'tanggal' => $currTgl,
		'kodept' => $resPt[0]['induk'],
		'keterangan' => empty($param['keterangan'])? $param['keterangan']: '',
		'notransaksireferensi' => empty($param['notransreferensi'])? $param['notransreferensi']: '',
		'statusjurnal' => 1,
		'kodegudang' => $param['kodegudang'],
		'user' => $_SESSION['standard']['userid'],
		'post' => 1,
		'postedby' => $_SESSION['standard']['userid']
	);
	$colsH = array();
	foreach($dataH as $key=>$row) {
		$colsH[] = $key;
	}
	
	// Data Detail
	$dataD = array(
		'notransaksi' => $noTrans,
		'kodebarang' => $param['kodebarang'],
		'satuan' => $optSat[$param['kodebarang']],
		'jumlah' => $param['jumlah'],
		'jumlahlalu' => $beforeQty,
		'hargasatuan' => $param['harga'] > 0? $param['harga'] : 0,
		'updateby' => $_SESSION['standard']['userid'],
		'statussaldo' => 1,
		'hargarata' => $param['harga']//$beforeQty <= 0 ? $param['harga'] : $currHarga
	);
	$colsD = array();
	foreach($dataD as $key=>$row) {
		$colsD[] = $key;
	}
	
	/**
	 * [START] Journal Adjustment
	 */
	$kodePt = $_SESSION['org']['kodeorganisasi'];
	
	if(($param['jenis']=='in' and $param['harga']>0 and $currQty>0) // Kondisi dilakukannya jurnal
	   or ($param['jenis']=='out' and $param['jumlah']>0 and $param['harga']>0)) {
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		// Kode Jurnal
		if($param['jenis']=='in') { // Transaksi Masuk
			$kodeJurnal = 'INVM4';
		} elseif($param['jenis']=='out') { // Transaksi Keluar
			$kodeJurnal = 'INVK4';
		}
		
		// Parameter Jurnal
		$qParam = selectQuery($dbname,'keu_5parameterjurnal',"*",
							  "kodeaplikasi='INV' and jurnalid='".$kodeJurnal."'");
		$resParam = fetchData($qParam);
		if(empty($resParam)) exit("Warning: Parameter Jurnal ".$kodeJurnal." belum ada.\n".
								  "Silahkan hubungi IT dengan melampirkan pesan error ini");
		
		// Akun Persediaan
		$qKel = selectQuery($dbname,'log_5klbarang','noakun',"kode='".substr($param['kodebarang'],0,3)."'");
		$resKel = fetchData($qKel);
		if(empty($resKel)) exit("Warning: No Akun untuk kelompok barang ".
								substr($param['kodebarang'],0,3)." belum ada");
		$akunBarang = $resKel[0]['noakun'];
		
		// Get Journal Counter
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$kodePt."' and kodekelompok='".$kodeJurnal."'");
		$tmpKonter = fetchData($queryJ);
		if(empty($tmpKonter)) exit("Warning: Kelompok Jurnal ".$kodeJurnal." untuk PT ".$kodePt.
								   " belum ada.\nSilahkan hubungi IT dengan melampirkan pesan error ini");
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);
		
		// No Jurnal
		$nojurnal = str_replace('-','',$currTgl)."/".substr($param['kodegudang'],0,4)."/".
			$kodeJurnal."/".$konter;
		
		// Jika kuantitas 0, maka lakukan jurnal selisih harga
		if($param['jenis']=='in' and $param['jumlah']==0) {
			$rupiah = $currQty * $param['harga'];
		} else {
			$rupiah = $param['jumlah'] * $param['harga'];
		}
		
		// Header Jurnal
		$dataRes['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodeJurnal,
			'tanggal'=>$currTgl,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>$rupiah,
			'totalkredit'=>$rupiah,
			'amountkoreksi'=>'0',
			'noreferensi'=>$noTrans,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		// Prepare Data
		if($param['jenis']=='in') { // Transaksi Masuk
			if($param['harga']>0 and $param['jumlah']>0) {
				// Adjust kuantitas dan harga
				$akunKredit = $resParam[0]['noakunkredit'];
			} elseif($param['harga']>0) {
				// Adjust harga
				$akunKredit = $resParam[0]['sampaikredit'];
			}
			
			// Detail Debet
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$currTgl,
				'nourut'=>1,
				'noakun'=>$akunBarang,
				'keterangan'=>"Adjustment ".$noTrans." untuk barang ".$param['kodebarang'],
				'jumlah'=>$rupiah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($param['kodegudang'],0,4),
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>$param['kodebarang'],
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noTrans,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>empty($param['notransreferensi'])? $param['notransreferensi']: '',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => ''
			);
			
			// Detail Kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$currTgl,
				'nourut'=>2,
				'noakun'=>$akunKredit,
				'keterangan'=>"Adjustment ".$noTrans." untuk barang ".$param['kodebarang'],
				'jumlah'=>$rupiah * (-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($param['kodegudang'],0,4),
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noTrans,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>empty($param['notransreferensi'])? $param['notransreferensi']: '',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => ''
			);
		} elseif($param['jenis']=='out') { // Transaksi Keluar
			// Jurnal hanya terjadi jika ada kuantitas dan harga
			if($param['harga']>0 and $param['jumlah']>0) {
				$akunDebet = $resParam[0]['noakundebet'];
				
				// Detail Debet
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$currTgl,
					'nourut'=>1,
					'noakun'=>$akunDebet,
					'keterangan'=>"Adjustment ".$noTrans." untuk barang ".$param['kodebarang'],
					'jumlah'=>$rupiah,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($param['kodegudang'],0,4),
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$noTrans,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>empty($param['notransreferensi'])? $param['notransreferensi']: '',
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);
				
				// Detail Kredit
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$currTgl,
					'nourut'=>2,
					'noakun'=>$akunBarang,
					'keterangan'=>"Adjustment ".$noTrans." untuk barang ".$param['kodebarang'],
					'jumlah'=>$rupiah * (-1),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($param['kodegudang'],0,4),
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$param['kodebarang'],
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$noTrans,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>empty($param['notransreferensi'])? $param['notransreferensi']: '',
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);
			}
		}
		
		// Lakukan Jurnal
		doJournal($dataRes, $konter, $kodePt, $kodeJurnal);
	}
	/**
	 * [END] Journal Adjustment
	 */
	
	// Generate Insert Query
	if($param['jumlah']>0) {
		$qInsH = insertQuery($dbname,'log_transaksiht',$dataH,$colsH);
		$qInsD = insertQuery($dbname,'log_transaksidt',$dataD,$colsD);
		
		// Prepare Query for Rollback
		$qRB = deleteQuery($dbname,'log_transaksiht',"notransaksi='".$noTrans."'");
		
		// Insert Header
		if(!mysql_query($qInsH)) { // Gagal Insert Header
			// Rollback
			$tmpErr = mysql_error();
			if(!mysql_query($qRB)) {
				exit("Rollback Transaction Error: \n".mysql_error());
			} else {
				exit("Header Transaction Error: \n".$tmpErr);
			}
		} else {
			if(!mysql_query($qInsD)) { // Gagal Insert Detail
				// Rollback
				$tmpErr = mysql_error();
				if(!mysql_query($qRB)) {
					exit("Rollback Transaction Error: \n".mysql_error());
				} else {
					exit("Detail Transaction Error: \n".$tmpErr);
				}
			}
		}
	}
	/**
	 * [END] Transaksi Adjustment
	 */
	
	// Jika adjustment hanya penyesuaian harga
	if($param['jumlah']==0) {
		$currHarga += $param['harga'];
	}
	#masuk
	$sMasuk = "select sum(jumlah) as jlhmasuk, sum(hargasatuan*jumlah) as rpmasuk from ".$dbname.".log_transaksi_vw where tanggal like '".$periode."%' and kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and statussaldo=1 and tipetransaksi < 5";
	$qMasuk = mysql_query($sMasuk) or die(mysql_error($conn));
	$rMasuk = mysql_fetch_assoc($qMasuk);


	#keluar
	$sKeluar = "select sum(jumlah) as jlhkeluar, sum(hargarata*jumlah) as rpkeluar from ".$dbname.".log_transaksi_vw where tanggal like '".$periode."%' and kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and statussaldo=1 and tipetransaksi > 4";
	$qKeluar = mysql_query($sKeluar) or die(mysql_error($conn));
	$rKeluar = mysql_fetch_assoc($qKeluar);
	
    if(mysql_num_rows($res)>0) { // Saldobulanan sudah ada, lakukan update
        while($bar=mysql_fetch_object($res)) {
            $currqty=$bar->saldoakhirqty;
            $curnil=$bar->hargarata;
            $curtot=$bar->nilaisaldoakhir;
        }
		
		is_null($rMasuk['rpmasuk'])?$rMasuk['rpmasuk']=0:$rMasuk['rpmasuk']=$rMasuk['rpmasuk'];
		is_null($rKeluar['rpkeluar'])?$rKeluar['rpkeluar']=0:$rKeluar['rpkeluar']=$rKeluar['rpkeluar'];
		#ada maka update saldobulanan
        $str="update ".$dbname.".log_5saldobulanan set
			saldoakhirqty=".$currQty.", hargarata=".$currHarga.",nilaisaldoakhir=".($currQty*$currHarga).",
			qtymasuk=".$qtyIn.",qtykeluar=".$qtyOut.",qtymasukxharga=".$rMasuk['rpmasuk'].",qtykeluarxharga=".$rKeluar['rpkeluar']."
			where periode='".$periode."' and kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
        
		#update masterbarangdt 
        $str2="update ".$dbname.".log_5masterbarangdt set saldoqty=".$currQty.",hargalastin=".$lastIn.",hargalastout=".$lastOut."
			where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
        //exit("Error:$str2");
        if(mysql_query($str2)) {
            if(mysql_query($str)) {
                #write log
                $str="insert into ".$dbname.".log_stopname_log(kodegudang,kodebarang,updateby,oldqty,oldharga,newqty,newharga)
                          values('".$param['kodegudang']."','".$param['kodebarang']."',".$_SESSION['standard']['userid'].",".$currqty.",".$curnil.",".$currQty.",".$currHarga.")";
                $res=mysql_query($str);
            } else {
                #rollback masterbarangdt
                $str2="update ".$dbname.".log_5masterbarangdt set saldoqty=".$currqty." where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
                mysql_query($str2);
				if(!mysql_query($qRB)) {
					exit("Rollback Transaction Error: \n".mysql_error());
				} else {
					echo "Error saldobulanan:".mysql_error($conn);
				}
                exit();
            }
        } else {
            if(!mysql_query($qRB)) {
				exit("Rollback Transaction Error: \n".mysql_error());
			} else {
				echo "Error masterbarangdt:".mysql_error($conn);
			}
            exit();
        }     
    } else {
        #ambil kode pt    
		$str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($param['kodegudang'],0,4)."'";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
            $kodept=$bar->induk;
        }
		is_null($rMasuk['rpmasuk'])?$rMasuk['rpmasuk']=0:$rMasuk['rpmasuk']=$rMasuk['rpmasuk'];
		is_null($rKeluar['rpkeluar'])?$rKeluar['rpkeluar']=0:$rKeluar['rpkeluar']=$rKeluar['rpkeluar'];
        #tidak ada maka insert
        $str="insert into ".$dbname.".log_5saldobulanan(`kodeorg`, `kodebarang`, `saldoakhirqty`, `hargarata`, `lastuser`, `periode`, `nilaisaldoakhir`, `kodegudang`, `qtymasuk`, `qtykeluar`, `qtymasukxharga`, `qtykeluarxharga`, `saldoawalqty`, `hargaratasaldoawal`, `nilaisaldoawal`) 
            values ('".$kodept."', '".$param['kodebarang']."', ".$currQty.", ".$currHarga.", ".$_SESSION['standard']['userid'].", '".$periode."', ".($currQty*$currHarga).", '".$param['kodegudang']."', ".$qtyIn.", ".$qtyOut.", ".$rMasuk['rpkeluar'].", ".$rKeluar['rpmasuk'].", 0, 0, 0);";
       
		$str2= "insert into ".$dbname.".log_5masterbarangdt (`kodeorg`, `kodebarang`, `saldoqty`, `hargalastin`, `hargalastout`, `stockbataspesan`, `stockminimum`, `lastuser`, `kodegudang`) values
			('".$kodept."', '".$param['kodebarang']."', ".$currQty.", ".$lastIn.", ".$lastOut.", 0, 0, ".$_SESSION['standard']['userid'].",  '".$param['kodegudang']."')";
		if(mysql_query($str2)){
			if(mysql_query($str)){
				#write log
                $str="insert into ".$dbname.".log_stopname_log(kodegudang,kodebarang,updateby,oldqty,oldharga,newqty,newharga)
                          values('".$param['kodegudang']."','".$param['kodebarang']."',".$_SESSION['standard']['userid'].",0,0,".$param['jumlah'].",".$param['harga'].")";
                $res=mysql_query($str);               
			} else {
				#rollback masterbarangdt 
                $str2="delete from ".$dbname.".log_5masterbarangdt  where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
                mysql_query($str2);
				if(!mysql_query($qRB)) {
					exit("Rollback Transaction Error: \n".mysql_error());
				} else {
					echo "Error insert saldobulanan:".mysql_error($conn);
				}
                exit();                   
	        }
		} else {
            if(!mysql_query($qRB)) {
				exit("Rollback Transaction Error: \n".mysql_error());
			} else {
				echo "Error insert masterbarangdt:".mysql_error($conn);
			}
            exit(); 
		}
    }
}

/**
 * doJournal
 * Jurnal Transaksi Adjustment
 */
function doJournal($dataRes, $counterJurnal, $kodePt, $kodeJurnal) {
	global $dbname;
	
	$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	if(!mysql_query($queryH)) {
		exit("DB Error: ".mysql_error());
	} else {
		foreach($dataRes['detail'] as $det) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$det);
			if(!mysql_query($queryD)) {
				echo "DB Error: ".mysql_error();
				rbJournal($dataRes['header']['nojurnal']);
			}
		}
	}
	
	// Get Journal Counter
	$whereUpd = "kodeorg='".$kodePt."' and kodekelompok='".$kodeJurnal."'";
	$data = array('nokounter'=>$counterJurnal);
	$updateC = updateQuery($dbname,'keu_5kelompokjurnal',$data,$whereUpd);
	if(!mysql_query($updateC)) {
		echo "DB Error: ".mysql_error();
		rbJournal($dataRes['header']['nojurnal']);
	}
}

/**
 * rbJournal
 * Rollback Jurnal Transaksi Adjustment
 */
function rbJournal($nojurnal) {
	global $dbname;
	
	$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
	if(!mysql_query($qDel)) {
		echo "Rollback Error: ".mysql_error();
	}
}