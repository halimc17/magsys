<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param = $_POST;
if(!isTransactionPeriod())//check if transaction period is normal
{
	echo " Error: Transaction Period missing";
} else {
	// Tipe Transaksi = 7
	$tipetransaksi=7;
	$packinglistId="";
	
	// Get Data Detail
	$qSJ = selectQuery($dbname,'log_suratjalandt',"*","nosj='".$param['nosj']."'");
	$resSJ = fetchData($qSJ);
	
	// Get List of Kode Barang
	$listBarang = '';
	$saldoSJ = array();
	foreach($resSJ as $row) {
		if($row['jenis']=='PL') {
			$qPL = selectQuery($dbname,'log_packingdt',"*","notransaksi='".
							   $row['kodebarang']."'");
			$resPL = fetchData($qPL);
			
			foreach($resPL as $row) {
				if(!empty($listBarang)) $listBarang .= ',';
				$listBarang .= "'".$row['kodebarang']."'";
				
				if(!isset($saldoSJ[$row['kodebarang']]))
					$saldoSJ[$row['kodebarang']]=0;
				$saldoSJ[$row['kodebarang']] += $row['jumlah'];
			}
		} else {
			if(!empty($listBarang)) $listBarang .= ',';
			$listBarang .= "'".$row['kodebarang']."'";
			
			if(!isset($saldoSJ[$row['kodebarang']]))
				$saldoSJ[$row['kodebarang']]=0;
			$saldoSJ[$row['kodebarang']] += $row['jumlah'];
		}
	}
	if(empty($listBarang)) {
		exit('Warning: Surat Jalan '.$param['nosj'].
			 ' tidak memiliki daftar barang');
	}
	
	/** Pengecekan jika no transaksi sudah ada */
	#jamhari menambahkan antrian jika no transaksi sudah di gunakan dengan user yang lain
	#cek detail sudah terisi atau belum, jika sudah terisi langsung insert jika belum cek notransaksi sudah ada atau tidak
	// comment by adi, diganti query dibawah
//	$str="select user from ".$dbname.".log_transaksiht where notransaksi='".$param['notransaksi']."'";
//	$res=mysql_query($str);
//	$hsl=  mysql_fetch_assoc($res);
//    if($_SESSION['standard']['userid']!=$hsl['user']){
//		$status=0;
//		$user1=$_SESSION['standard']['userid'];
//		$gudang=$param['gudang'];
//		$antri=0;
//		$num=1;//default value 
//		do {
//			$str="select max(notransaksi) notransaksi from ".$dbname.".log_transaksiht where tipetransaksi>4 and tanggal>=".$_SESSION['gudang'][$gudang]['start']." and tanggal<=".$_SESSION['gudang'][$gudang]['end']."
//				  and kodegudang='".$gudang."' order by notransaksi desc limit 1";	
//			if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
//				$str="select max(notransaksi) notransaksi from ".$dbname.".log_transaksiht where tipetransaksi>4 and tanggal>=".$_SESSION['gudang'][$gudang]['start']." and tanggal<=".$_SESSION['gudang'][$gudang]['end']."
//					  and kodegudang='".$gudang."' and substr( `notransaksi` , 7, 1 ) not like '%M%' order by notransaksi desc limit 1";	
//			}
//			
//			if($res=mysql_query($str))
//			{
//				while($bar=mysql_fetch_object($res))
//				{
//					$num=$bar->notransaksi;
//					if($num!='')
//					{
//                        $num=intval(substr($num,6,5))+1;
//                    } else {
//                        $num=1;
//                    }
//                }
//				$num = str_pad($num, 5, "0", STR_PAD_LEFT); // Menambahkan angka 0 dikiri num sampai panjang = 5
//				$param['notransaksi']=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan'].$num."-GI-".$gudang;
//				$str2="select * from ".$dbname.".log_transaksiht where notransaksi='".$param['notransaksi']."'";
//				$res2=mysql_query($str2);
//				$hslcek=mysql_num_rows($res2);
//                if($hslcek==0) {
//                    $antri=1;
//                    break;
//                } else {
//                    $antri=0;
//                }
//            }
//        }while($antri==0);
//    } else {
//		$status=1;
//    }
	
	/**
	 * Cek Saldo
	 */
	// Ambil saldo barang aktif, log_5masterbarangdt
	$resSaldo = makeOption($dbname,'log_5masterbarangdt',"kodebarang,saldoqty","kodegudang='".$param['gudang']."' and kodebarang in (".$listBarang.")");
	
	// Ambil transaksi keluar yang belum posting (Potensi pengeluaran barang)
	$qTrans = selectQuery($dbname,'log_transaksi_vw','kodebarang,sum(jumlah) as jumlah',
						  "kodegudang='".$param['gudang']."' and tipetransaksi>4 and kodebarang in (".$listBarang.")")." and statussaldo=0 group by kodebarang";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		$resSaldo[$row['kodebarang']] -= $row['jumlah'];
	}
	
	// List Approved & Not Approved Barang
	$notApp = array();
	foreach($saldoSJ as $barang=>$saldo) {
		if(!isset($resSaldo[$barang]) or $saldo>$resSaldo[$barang]) {
			$notApp[$barang] = isset($resSaldo[$barang])? $resSaldo[$barang]: 0;
			if($notApp[$barang]<0) $notApp[$barang] = 0;
		}
	}
	
	// Insert Header jika belum ada
	/** Jika User pertama kali melakukan insert, maka ambil kembali nomor transaksi */
	$status=0;
	if(isset($_POST['isNewTrans']) and $_POST['isNewTrans']==0) {
		// Get Nomor Transaksi Terakhir
		$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht
			where tipetransaksi>4 
			and substr(notransaksi,1,6) = '".$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan']."'
			and kodegudang='".$param['gudang']."' order by notransaksi desc limit 1";	
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
			$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht
				where tipetransaksi>4
				and substr(notransaksi,1,6) = '".$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan']."'
				and kodegudang='".$param['gudang']."' and substr( `notransaksi` , 7, 1 ) not like '%M%'
				order by notransaksi desc limit 1";	
		}
		// Execute Query
		if($res=mysql_query($str)) {
			$num=1;
			while($bar=mysql_fetch_object($res)) {
				$num=$bar->notransaksi;
				if(!empty($num)) {
					$num=intval(substr($num,6,5))+1;
				}
			}
			$num = str_pad($num, 5, "0", STR_PAD_LEFT);
			$num=$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan'].$num."-GI-".$param['gudang'];
			$param['notransaksi'] = $num;
		} else {
			echo "DB Error: ".addslashes(mysql_error($conn));
		}
	}else{
		$status=1;
		$scek="select * from ".$dbname.".log_transaksiht where notransaksi='".$param['notransaksi']."'";
		$qcek=  mysql_query($scek) or die(mysql_error($conn));
		$rcek=  mysql_num_rows($qcek);
		if($rcek==0){
			$status=0;
		}
	}
	
	if($status==0) {
		$sKdPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".substr($param['kegudang'],0,4)."'";
		$qKdPt=mysql_query($sKdPt) or die(mysql_error($sKdPt));
		$rKdpt=mysql_fetch_assoc($qKdPt);
		if(empty($rKdpt['induk'])) exit("Kode PT Penerima Kosong");
		
		$dataH = array(
			'tipetransaksi' => $tipetransaksi,
			'notransaksi' => $param['notransaksi'],
			'tanggal' => tanggalsystem($param['tanggal']),
			'kodept' => $param['pemilikbarang'],
			'untukpt' => $rKdpt['induk'],
			'keterangan' => $param['catatan'],
			'nosj' => $param['nosj'],
			'kodegudang' => $param['gudang'],
			'user' => $_SESSION['standard']['userid'],
			'post' => 0,
			'gudangx' => $param['kegudang']
		);
		$cols = array();
		foreach($dataH as $key=>$val) {
			$cols[] = $key;
		}
		$qIns = insertQuery($dbname,'log_transaksiht',$dataH,$cols);
		if(!mysql_query($qIns)) {
			exit("DB Error Insert Header:\n".mysql_error());
		}
    }else{
		$supd="update ".$dbname.".log_transaksiht set keterangan='".$param['catatan']."' where notransaksi='".$param['notransaksi']."'";
		if(!mysql_query($supd)){
				exit("DB Error Insert Header:\n".mysql_error());
		}
	}
	
	// Masukkan Barang ke transaksidt
	$res = "";$no=0;
	$errorDt = '';
	$errBrg = $jmlhBrg = array();
	$errBrg2 = $jmlhBrg2 = array();//print_r($notApp);exit;
	$jumlahlalu = array();
	foreach($resSJ as $row) {
		//==================ambil jumlah lalu====================
		$str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi,a.waktutransaksi 
			from ".$dbname.".log_transaksidt a,
				 ".$dbname.".log_transaksiht b
			where a.notransaksi=b.notransaksi 
				and a.kodebarang='".$row['kodebarang']."'
				and a.notransaksi<='".$param['notransaksi']."'
				and tipetransaksi>4
				and b.kodegudang='".$param['gudang']."'
			order by notransaksi desc, waktutransaksi desc limit 1";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)) {
			setIt($jumlahlalu[$row['kodebarang']],0);
			$jumlahlalu[$row['kodebarang']] += $bar->jumlah;
		}
		
		if(substr($row['kodebarang'],0,2)=='PL') {
			// Jika Packing List
			$qPL = selectQuery($dbname,'log_packingdt',"*","notransaksi='".
							   $row['kodebarang']."'");
			$resPL = fetchData($qPL);
			$dataD = array();
			foreach($resPL as $row2) {
				setIt($jumlahlalu[$row2['kodebarang']],0);
				$dataD[] = array(
					'notransaksi' =>$param['notransaksi'],
					'kodebarang' =>$row2['kodebarang'],
					'nopp' => $row2['nopp'],
					'nopo' => $row2['nopo'],
					'satuan' =>$row2['satuanpo'],
					'jumlah' =>$row2['jumlah'],
					'jumlahlalu' =>$jumlahlalu[$row2['kodebarang']],
					'updateby' =>$_SESSION['standard']['userid']
				);
				$jumlahlalu[$row2['kodebarang']] += $row2['jumlah'];
			}
		} else {
			setIt($jumlahlalu[$row['kodebarang']],0);
			// Jika Barang (PO / Material)
			$dataD = array(
				'notransaksi' =>$param['notransaksi'],
				'kodebarang' =>$row['kodebarang'],
				'nopp' => $row['nopp'],
				'nopo' => $row['nopo'],
				'satuan' =>$row['satuanpo'],
				'jumlah' =>$row['jumlah'],
				'jumlahlalu' =>$jumlahlalu[$row['kodebarang']],
				'updateby' =>$_SESSION['standard']['userid']
			);
			$jumlahlalu[$row['kodebarang']] += $row['jumlah'];
		}
		$colD = array('notransaksi','kodebarang','nopp','nopo','satuan',
			'jumlah','jumlahlalu','updateby');
		
		//if(substr($row['kodebarang'],0,2)=='PL') {
		//	$wht="notransaksireferensi!='' and notransaksi='".$row['kodebarang']."'";
		//	$optCek = makeOption($dbname, 'log_packingdt', 'notransaksi,notransaksireferensi',$wht);
		//} else {
			$wht="notransaksireferensi!='' and nosj='".$param['nosj']."' and kodebarang='".
					$row['kodebarang']."' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'";
			$optCek = makeOption($dbname, 'log_suratjalandt', 'kodebarang,notransaksireferensi',$wht);
		//}
		
		$brgNotApp = '';
		if(empty($optCek[$row['kodebarang']])){ // Validasi sudah pernah dimutasikan
			if(isset($notApp[$row['kodebarang']])) { // Validasi tidak cukup stok
				$errBrg2[$row['kodebarang']]=$row['kodebarang'];
				$jmlhBrg2[$row['kodebarang']]=$row['jumlah'];
			} else {
				$qInsD = insertQuery($dbname,'log_transaksidt',$dataD,$colD);
				if(!mysql_query($qInsD)) {
						$errorDt .= "DB Error Insert Detail Barang ".$row['kodebarang'].":\n".mysql_error()."\n";
				}else{
					// Update No Transaksi Referensi
					$data = array('notransaksireferensi'=>$param['notransaksi']);
					if($packinglistId!='') {
                        $qUpd = updateQuery($dbname,'log_packingdt',$data,"notransaksi='".
											$packinglistId."' and kodebarang='".$row['kodebarang'].
											"' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'");
                        if(mysql_query($qUpd)) {
                            $qUpd = updateQuery($dbname,'log_suratjalandt',$data,"nosj='".
                                                $param['nosj']."' and kodebarang='".$packinglistId."'");
							if(!mysql_query($qUpd)) {
								exit("DB Error SJ not updated\n".mysql_error());
							}
                        } else {
                            exit("DB Error SJ not updated\n".mysql_error());
                        }
                    } else {
                        $qUpd = updateQuery($dbname,'log_suratjalandt',$data,"nosj='".
											$param['nosj']."' and kodebarang='".$row['kodebarang'].
											"' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'");
                        if(!mysql_query($qUpd)) {
							exit("DB Error SJ not updated\n".mysql_error());
						}
					}
				}
			}
		} else {
			$errBrg[$row['kodebarang']]=$row['kodebarang'];
			$jmlhBrg[$row['kodebarang']]=$row['jumlah'];
		}
	}
	if(!empty($errorDt)) {
		// Rollback
		$qRB = deleteQuery($dbname,'log_transaksidt',"notransaksi='".$param['notransaksi']."'");
		if(mysql_query($qRB)) {
			$qRB2 = deleteQuery($dbname,'log_transaksiht',"notransaksi='".$param['notransaksi']."'");
			mysql_query($qRB2);
		}
		exit("Detail Error\n".$errorDt);
	}  
	
	//ambil data untuk ditampilkan
	$strj="select a.* from ".$dbname.".log_transaksidt a 
		where a.notransaksi='".$param['notransaksi']."'";
	$resj=mysql_query($strj);
	$no=0;$tab='';
	while($barj=mysql_fetch_object($resj)) {
        $no+=1;
        //ambil namabarang
        $namabarangk='';
        $strk="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
        $resk=mysql_query($strk);
        while($bark=mysql_fetch_object($resk))
        {
            $namabarangk=$bark->namabarang;
        }
        $tab.="<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$barj->kodebarang."</td>
			<td>".$namabarangk."</td>
			<td>".$barj->satuan."</td>
			<td align=right>".number_format($barj->jumlah,2,'.',',')."</td>
			<td>
			&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delMutasi('".$param['notransaksi']."','".$barj->kodebarang."');\">
			</td>
        </tr>";
	}
	
	// Tampilkan barang dengan saldo yang tidak mencukupi
	if(count($errBrg2)>0){
		$tab.="<tr><td colspan=6>Material yang berwarna merah, tidak memiliki saldo yang cukup</td></tr>";
		foreach($errBrg2 as $lstBrg){
			$no+=1;
			$strk="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$lstBrg."'";
			$resk=mysql_query($strk);
			while($bark=mysql_fetch_object($resk)){
				$namabarangk=$bark->namabarang;
				$satuank = $bark->satuan;
			}
			$tab.="<tr bgcolor=red>
				<td>".$no."</td>
				<td>".$lstBrg."</td>
				<td>".$namabarangk."</td>
				<td>".$satuank."</td>
				<td align=right>".number_format($jmlhBrg2[$lstBrg],2,'.',',')."</td>
				<td>Saldo = ".$notApp[$lstBrg]."</td>
				</tr>";
		}
	}
	
	// Tampilkan barang yang sudah pernah dimutasi
	if(count($errBrg)>0){
		$tab.="<tr><td colspan=6>Material yang berwarna oranye, sudah pernah di mutasikan</td></tr>";
		foreach($errBrg as $lstBrg) {
			$no+=1;
			$strk="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$lstBrg."'";
			$resk=mysql_query($strk);
			while($bark=mysql_fetch_object($resk)){
				$namabarangk=$bark->namabarang;
				$satuank = $bark->satuan;
			}
			echo"<tr bgcolor=orange>
			<td>".$no."</td>
			<td>".$lstBrg."</td>
			<td>".$namabarangk."</td>
			<td>".$satuank."</td>
			<td align=right>".number_format($jmlhBrg[$lstBrg],2,'.',',')."</td>
			<td>&nbsp</td>
			</tr>";
		}
	}
}
echo $tab."#####".$param['notransaksi'];