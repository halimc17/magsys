<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zJournal.php');

$param = $_POST;
$proses = $_GET['proses'] or '';
if($proses=='listExcel'){
	$param = $_GET;
}
switch($proses) {
	case 'list':
		$tanggal1 = tanggalsystemn($param['tanggal1']);
		$tanggal2 = tanggalsystemn($param['tanggal2']);
		if($tanggal1 > $tanggal2) exit("Warning: Tanggal awal tidak boleh dari tanggal akhir");
		if($param['komoditi']!=''){
			$whdt.=" and a.kodebarang='".$param['komoditi']."'";
		}
		if($param['kdpt']!=''){
			if($param['komoditi']!=''){
				$whdt="";
				$whr="";
				$whr="and kodebarang='".$param['komoditi']."'";
			}
			$whdt="and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where kodept='".$param['kdpt']."' ".$whr.") ";
		}
		if($param['nokontrak']!=''){
			$whdt.="  and a.nokontrak like '%".$param['nokontrak']."%'";
		}
		// Get Data
		$qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*
			FROM ".$dbname.".pabrik_timbangan a
			INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
			LEFT JOIN ".$dbname.".log_5supplier b on a.kodecustomer = b.kodetimbangan
			LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang 
			WHERE left(a.tanggal,10) between '".$tanggal1."' and '".$tanggal2."'  ".$whdt." and a.millcode like '%".$param['pabrik']."%'";
			//echo $qData;
		$resData = fetchData($qData);
	 
		$tab = "<img src='images/excel.jpg' class='resicon' title='MS Excel' onclick=getExcel(event,'keu_slave_pengakuanjual.php')><table class=data border=0 cellspacing=1 cellpadding=3>";
		$tab .= "<thead><tr class=rowheader>";
		$tab .= "<td>".$_SESSION['lang']['noTiket']."</td>";
		$tab .= "<td>".$_SESSION['lang']['pabrik']."</td>";
		$tab .= "<td>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab .= "<td>".$_SESSION['lang']['noinvoice']."</td>";
		$tab .= "<td>".$_SESSION['lang']['nmcust']."</td>";
		$tab .= "<td>".$_SESSION['lang']['namabarang']."</td>";
		$tab .= "<td colspan=2>".$_SESSION['lang']['jumlah']."</td>";
		$tab .= "<td>".$_SESSION['lang']['hargasatuan']."</td>";
		$tab .= "<td>".$_SESSION['lang']['totalharga']."</td>";
		$tab .= "<td>".$_SESSION['lang']['tanggal']." Pengakuan</td>";
		$tab .= "<td>".$_SESSION['lang']['action']."</td>";
		$tab .= "</tr></thead>";
		
		$tab .= "<tbody>";
		foreach($resData as $row) {
			$tmpTgl = explode(' ',$row['tanggal']);
			$scek="select nokontrak from ".$dbname.".pmn_kontrakjualdt where nokontrak_ref='".$row['nokontrak']."'";
			$qcek=mysql_query($scek) or die(mysql_error($conn));
			$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
			if($optNm[$row['millcode']]==substr($row['nokontrak'],4,3)){
				$rcek=0;
			}else{
				//$rcek=mysql_num_rows($qcek);
				$rcek=1;
			}
			//if($row['notransaksi']=='CBS20221201'){
			//	exit('Warning: '.$row['notransaksi'].' '.$optNm[$row['millcode']].'=='.substr($row['nokontrak'],4,3).' => '.$rcek.' '.$scek);
			//}
			$scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual where notransaksi='".$row['notransaksi']."'";
			$qcek2=mysql_query($scek2) or die(mysql_error($conn));
			$rcek2=mysql_fetch_assoc($qcek2);

			$scek3="select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$row['nokontrak']."'";
			$qcek3=mysql_query($scek3) or die(mysql_error($conn));
			$rcek3=mysql_fetch_assoc($qcek3);
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>".$row['notransaksi']."</td>";
			$tab .= "<td>".$row['millcode']."</td>";
			$tab .= "<td>".$row['nokontrak']."</td>";
			$tab .= "<td>".$rcek3['noinvoice']."</td>";
			$tab .= "<td>".$row['namasupplier']."</td>";
			$tab .= "<td>".$row['namabarang']."</td>";
			$tab .= "<td align=right>".number_format($row['beratbersih'],0)."</td>";
			$tab .= "<td>".$row['satuan']."</td>";
			$tab .= "<td align=right>".number_format($row['hargasatuan'],0)."</td>";
			$tab .= "<td align=right>".number_format($row['beratbersih'] * $row['hargasatuan'],0)."</td>";
			if($rcek2['posting']==1) {
				$tab .= "<td>".tanggalnormal($tmpTgl[0])."</td>";
				$tab .= "<td><img class=zImgBtn id=imgPost_".$row['notransaksi']." src='images/skyblue/posted.png'></td>";
			} else {
				$tab .= "<td>".makeElement('tanggal_'.$row['notransaksi'],'date',
					tanggalnormal($tmpTgl[0]),array('disabled'=>'disabled'))."</td>";
				$tab .= "<td><img class=zImgBtn id=imgPost_".$row['notransaksi']." src='images/skyblue/posting.png' ".
					"onclick=\"pilKontrak(this,'".$row['notransaksi']."','".$row['millcode']."','".$rcek."',event)\"></td>";
				/*$tab .= "<td><img class=zImgBtn src='images/skyblue/posting.png' ".
					"onclick=\"post(this,'".$row['notransaksi']."','".$row['millcode']."')\"></td>";*/
			}
			$tab .= "</tr>";
		}
		$tab .= "</tbody>";
		$tab .= "</table>";
		
		echo $tab;
		break;
	case 'listExcel':
		$tanggal1 = tanggalsystemn($param['tanggal1']);
		$tanggal2 = tanggalsystemn($param['tanggal2']);
		if($tanggal1 > $tanggal2) exit("Warning: Tanggal awal tidak boleh dari tanggal akhir");
		if($param['komoditi']!=''){
			$whdt.=" and a.kodebarang='".$param['komoditi']."'";
		}
		if($param['kdpt']!=''){
			if($param['komoditi']!=''){
				$whdt="";
			}
			$whdt="and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where kodept='".$param['kdpt']."' and kodebarang='".$param['komoditi']."') ";
		}
		if($param['nokontrak']!=''){
			$whdt.="  and a.nokontrak like '%".$param['nokontrak']."%'";
		}
		// Get Data
		$qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*
			FROM ".$dbname.".pabrik_timbangan a
			INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
			LEFT JOIN ".$dbname.".log_5supplier b on a.kodecustomer = b.kodetimbangan
			LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang 
			WHERE left(a.tanggal,10) between '".$tanggal1."' and '".$tanggal2."'  ".$whdt." and a.millcode like '%".$param['pabrik']."%'";
			
		$resData = fetchData($qData);
	 
		$tab = "<table class=data border=1 cellspacing=1 cellpadding=3>";
		$tab .= "<thead><tr>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['noTiket']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['pabrik']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['noinvoice']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['nmcust']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['namabarang']."</td>";
		$tab .= "<td colspan=2  bgcolor='#dedede'>".$_SESSION['lang']['jumlah']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['hargasatuan']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['totalharga']."</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['tanggal']." Pengakuan</td>";
		$tab .= "<td  bgcolor='#dedede'>".$_SESSION['lang']['status']."</td>";
		$tab .= "</tr></thead>";
		
		$tab .= "<tbody>";
		foreach($resData as $row) {
			$tmpTgl = explode(' ',$row['tanggal']);
			$scek="select nokontrak from ".$dbname.".pmn_kontrakjualdt where nokontrak_ref='".$row['nokontrak']."'";
			$qcek=mysql_query($scek) or die(mysql_error($conn));
			$rcek=mysql_num_rows($qcek);
			
			$scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual where notransaksi='".$row['notransaksi']."'";
			$qcek2=mysql_query($scek2) or die(mysql_error($conn));
			$rcek2=mysql_fetch_assoc($qcek2);

			$scek3="select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$row['nokontrak']."'";
			$qcek3=mysql_query($scek3) or die(mysql_error($conn));
			$rcek3=mysql_fetch_assoc($qcek3);
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>".$row['notransaksi']."</td>";
			$tab .= "<td>".$row['millcode']."</td>";
			$tab .= "<td>".$row['nokontrak']."</td>";
			$tab .= "<td>".$rcek3['noinvoice']."</td>";
			$tab .= "<td>".$row['namasupplier']."</td>";
			$tab .= "<td>".$row['namabarang']."</td>";
			$tab .= "<td align=right>".number_format($row['beratbersih'],0)."</td>";
			$tab .= "<td>".$row['satuan']."</td>";
			$tab .= "<td align=right>".number_format($row['hargasatuan'],0)."</td>";
			$tab .= "<td align=right>".number_format($row['beratbersih'] * $row['hargasatuan'],0)."</td>";
			if($rcek2['posting']==1) {
				$tab .= "<td>".$tmpTgl[0]."</td>";
				$tab .= "<td>Posted</td>";
			} else {
				$tab .= "<td>".$tmpTgl[0]."</td>";
				$tab .= "<td>Belum Posting</td>";
				/*$tab .= "<td><img class=zImgBtn src='images/skyblue/posting.png' ".
					"onclick=\"post(this,'".$row['notransaksi']."','".$row['millcode']."')\"></td>";*/
			}
			$tab .= "</tr>";
		}
		$tab .= "</tbody>";
		$tab .= "</table>";
		$dtwkt=date("YmdHis");
		$nop_="pengakuanpenjualan_".$dtwkt;
		if(strlen($tab)>0)
		{
		if ($handle = opendir('tempExcel')) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
		            @unlink('tempExcel/'.$file);
		        }
		    }	
		   closedir($handle);
		}
		 $handle=fopen("tempExcel/".$nop_.".xls",'w');
		 if(!fwrite($handle,$tab))
		 {
		  echo "<script language=javascript1.2>
		        parent.window.alert('Can't convert to excel format');
		        </script>";
		   exit;
		 }
		 else
		 {
		  echo "<script language=javascript1.2>
		        window.location='tempExcel/".$nop_.".xls';
		        </script>";
		 }
		fclose($handle);
		}
		break;
	case 'post':
		// Init
		$zJ = new zJournal();
		$kodeJurnal = 'SLE';
		$scek="select noreferensi from ".$dbname.".keu_jurnalht where noreferensi='".$param['notiket']."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rcek=mysql_num_rows($qcek);
		if($rcek!=0){
			exit('warning: '.$param['notiket']." Sudah terposting");
		}
		// Validasi Back Date Periode
		if(tanggalsystem($param['tanggal']) < $_SESSION['org']['period']['start'])
			exit("Warning: Tanggal Pengakuan tidak boleh lebih kecil dari periode aktif");
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		// Get Data
		$qData = "SELECT * FROM ".$dbname.".pabrik_timbangan a
			INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
			WHERE a.notransaksi='".$param['notiket']."' and a.millcode='".$param['millcode']."'";
		$resData = fetchData($qData);
		if(empty($resData)) exit("Warning: Tiket ".$param['notiket']." tidak ada");
		$data = $resData[0];
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
			$qKntrkDt="select kodept,hargasatuan,koderekanan,ppn,toleransi from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['nokontrakDt']."'";
			$resKntrkDt = fetchData($qKntrkDt);
			$dataDet =$resKntrkDt[0];
			#noinvoice dari kontrak detail
			$scek3="select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$param['nokontrakDt']."'";
			$qcek3=mysql_query($scek3) or die(mysql_error($conn));
			$rcek3=mysql_fetch_assoc($qcek3);
			$noinvoiceDet=$rcek3['noinvoice'];
		}
		#noinvoice
		$scek3="select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$resData[0]['nokontrak']."'";
		$qcek3=mysql_query($scek3) or die(mysql_error($conn));
		$rcek3=mysql_fetch_assoc($qcek3);
		$noinvoice=$rcek3['noinvoice'];
		
		// Get Supplier
		$qSupp = selectQuery($dbname,'log_5supplier',"supplierid",
			"kodetimbangan = '".$data['kodecustomer']."'");
		$resSupp = fetchData($qSupp);
		$kodeSupp = $resSupp[0]['supplierid'];
		
		// Define Parameter tergantung Barang
		switch($data['kodebarang']) {
			case '40000001':
				$kodeApp = 'SCPO';
				break;
			case '40000002':
				$kodeApp = 'SKER';
				break;
			case '40000003':
				$kodeApp = 'STBS';
				break;
			case '40000004':
				$kodeApp = 'SJJK';
				break;
			case '40000005':
				$kodeApp = 'SCGK';
				break;
			case '40000011':
				$kodeApp = 'SSLD';
				break;
			case '40000016':
				$kodeApp = 'SFBR';
				break;
			default:
				$kodeApp = "SEXT";
		}
		
		
		// Get HO dari PT Kontrak
		$qHo = selectQuery($dbname,'organisasi','kodeorganisasi',
			"induk='".$data['kodept']."' and tipe='HOLDING'");
			   
		$resHo = fetchData($qHo);
		$kodeorg = $resHo[0]['kodeorganisasi'];
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
			$qHo2= selectQuery($dbname,'organisasi','kodeorganisasi',
			"induk='".$dataDet['kodept']."' and tipe='HOLDING'");
			$resHo2 = fetchData($qHo2);
			$kodeorg2 = $resHo2[0]['kodeorganisasi'];
		}
		
		// Get Parameter Jurnal
		$paramJ = $zJ->getParam($_SESSION['org']['induk'],$kodeApp,$kodeJurnal);
		if(empty($paramJ)) exit("Warning: Parameter Jurnal ".$kodeApp." belum ada\n".
			"Silahkan hubungi pihak IT");
			
		
		// Generate No Journal
		$tanggalJ = tanggalsystemn($param['tanggal']);
		$counter = $zJ->getCounter($data['kodept'],$kodeJurnal);
		$counter++;
		$nojurnal = $zJ->genNoJournal($tanggalJ,$kodeorg,$kodeJurnal,$counter);
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
			$tanggalJ = tanggalsystemn($param['tanggal']);
			$counter2 = $zJ->getCounter($dataDet['kodept'],$kodeJurnal);
			$counter2++;
			$nojurnal2 = $zJ->genNoJournal($tanggalJ,$kodeorg2,$kodeJurnal,$counter2);
		}
		
		
		// Jumlah
                #jika include ppn maka nilai hargasatuan di kurangi dengan hargasatuan*10/100
                if($data['ppn']==1){
                    $data['hargasatuan']=$data['hargasatuan']/1.11;
                }
		$jumlah = $data['beratbersih'] * $data['hargasatuan'];
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        #jika include ppn maka nilai hargasatuan di kurangi dengan hargasatuan*10/100
                        if($dataDet['ppn']==1){
                            $dataDet['hargasatuan']=$dataDet['hargasatuan']/1.11;
                        }
			$jumlah2 = $data['beratbersih'] * $dataDet['hargasatuan'];
		}
		
		// Prepare Data
		$dataRes = array();
		$dataRes2 = array();
		$dataRes['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodeJurnal,
			'tanggal'=>$tanggalJ,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>$jumlah,
			'totalkredit'=>$jumlah*(-1),
			'amountkoreksi'=>'0',
			'noreferensi'=>$param['notiket'],
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		$dataRes['detail'][0] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggalJ,
			'nourut'=>1,
			'noakun'=>$paramJ['noakundebet'],
			'keterangan'=>'Pengakuan Penjualan No. Tiket '.$param['notiket'],
			'jumlah'=>$jumlah,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$kodeorg,
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>$data['kodebarang'],
			'nik'=>'',
			'kodecustomer'=>$data['koderekanan'],
			'kodesupplier'=>$kodeSupp,
			'noreferensi'=>$param['notiket'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$noinvoice,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
		
		$dataRes['detail'][1] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggalJ,
			'nourut'=>2,
			'noakun'=>$paramJ['noakunkredit'],
			'keterangan'=>$resData[0]['beratbersih'],
			'jumlah'=>$jumlah * (-1),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$kodeorg,
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>$data['kodebarang'],
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>$kodeSupp,
			'noreferensi'=>$param['notiket'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$noinvoice,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
					$dataRes2['header'] = array(
					'nojurnal'=>$nojurnal2,
					'kodejurnal'=>$kodeJurnal,
					'tanggal'=>$tanggalJ,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>$jumlah2,
					'totalkredit'=>$jumlah2*(-1),
					'amountkoreksi'=>'0',
					'noreferensi'=>$param['notiket'].$kodeorg2,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
					);
			
					$dataRes2['detail'][0] = array(
					'nojurnal'=>$nojurnal2,
					'tanggal'=>$tanggalJ,
					'nourut'=>1,
					'noakun'=>$paramJ['noakundebet'],
					'keterangan'=>'Pengakuan Penjualan No. Tiket '.$param['notiket'].$kodeorg2,
					'jumlah'=>$jumlah2,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$kodeorg2,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$data['kodebarang'],
					'nik'=>'',
					'kodecustomer'=>$dataDet['koderekanan'],
					'kodesupplier'=>$kodeSupp,
					'noreferensi'=>$param['notiket'].$kodeorg2,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$noinvoiceDet,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
		
				$dataRes2['detail'][1] = array(
				'nojurnal'=>$nojurnal2,
				'tanggal'=>$tanggalJ,
				'nourut'=>2,
				'noakun'=>$paramJ['noakunkredit'],
				'keterangan'=>$resData[0]['beratbersih'],
				'jumlah'=>$jumlah2 * (-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$kodeorg2,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>$data['kodebarang'],
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$kodeSupp,
				'noreferensi'=>$param['notiket'].$kodeorg2,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noinvoiceDet,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
		}
		// Do Journal
		$zJ->doJournal($data['kodept'],$kodeJurnal,
			$dataRes,$counter,"Pengakuan Penjualan");
			
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
			$zJ->doJournal($dataDet['kodept'],$kodeJurnal,
				$dataRes2,$counter2,"Pengakuan Penjualan");
		}
		
		// Insert Pengakuan Penjualan
		$dataIns = array(
			'notransaksi' => $data['notransaksi'],
			'millcode' => $data['millcode'],
			'tanggalpengakuan' => $tanggalJ,
			'posting' => 1
		);
                $qCekData= selectQuery($dbname,'keu_pengakuanjual','notransaksi',
                "notransaksi='".$param['notiket']."' ");
                $resCekdata = fetchData($qCekData);
                if(!empty($resCekdata)){
                    $sdel="delete from ".$dbname.".keu_pengakuanjual where notransaksi='".$param['notiket']."'";
                    if(!mysql_query($sdel)){
                        echo "Delete Transaksi Error: ".mysql_error()."___".$sdel;
                        $zJ->rbJournal($nojurnal);
                    }
                }
                $qIns = insertQuery($dbname,'keu_pengakuanjual',$dataIns);
                if(!mysql_query($qIns)) {
                        echo "Insert Transaksi Error: ".mysql_error();
                        $zJ->rbJournal($nojurnal);
                }

		
		if(!empty($param['nokontrakDt'])){
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
			$dataIns2 = array(
				'notransaksi' => $param['notiket'].$kodeorg2,
				'millcode' => $data['millcode'],
				'tanggalpengakuan' => $tanggalJ,
				'posting' => 1
			);
                        $qCekData2= selectQuery($dbname,'keu_pengakuanjual','notransaksi',
			"notransaksi='".$param['notiket'].$kodeorg2."' ");
			$resCekData2 = fetchData($qCekData2);
                        if(!empty($resCekData2)){
                            $sdel2="delete from ".$dbname.".keu_pengakuanjual where notransaksi='".$param['notiket'].$kodeorg2."'";
                            if(!mysql_query($sdel2)){
                                echo "Delete Transaksi Error: ".mysql_error()."___".$sdel2;
                            }
                        }
                        $qIns2 = insertQuery($dbname,'keu_pengakuanjual',$dataIns2);
                        if(!mysql_query($qIns2)) {
                                echo "Insert Transaksi Error: ".mysql_error();
                                $zJ->rbJournal($nojurnal2);
                                $zJ2->rbJournal($nojurnal);
                        }
		}
		if(!empty($param['nokontrakDt'])){
		        //insert fisik pada pabrik timbangan untuk nokontrak detail
			//jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $qCekData2= selectQuery($dbname,'pabrik_timbangan','notransaksi',
			"notransaksi='".$param['notiket'].$kodeorg2."' ");
			$resCekData2 = fetchData($qCekData2);
                        if(!empty($resCekData2)){
                            $sdel2="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$param['notiket'].$kodeorg2."'";
                            if(!mysql_query($sdel2)){
                                echo "Delete Transaksi Error: ".mysql_error()."___".$sdel2;
                            }
                        }
                        
                        #ngitung ulang yang dah di terima terakhir brp
                        //$sCek="select sum(beratbersih) as totaltrima from ".$dbname.".pabrik_timbangan where left(notransaksi,7) in (select notransaksi from //".$dbname.".pabrik_timbangan where nokontrak='".$data['nokontrak']."') "
                        //         . " and nokontrak='".$param['nokontrakDt']."' and char_length(notransaksi)=11";
                        $sCek="select sum(beratbersih) as totaltrima from ".$dbname.".pabrik_timbangan where left(notransaksi,7) in (select left(notransaksi,7) from ".$dbname.".pabrik_timbangan where nokontrak='".$data['nokontrak']."') "
                                 . " and nokontrak='".$param['nokontrakDt']."'";
                        $qCek=  mysql_query($sCek) or die(mysql_error($conn));
                        $rCek=  mysql_fetch_assoc($qCek);
                        $supdate="update ".$dbname.".pmn_kontrakjualdt set terpenuhi='".($rCek['totaltrima']+$data['beratbersih'])."' where nokontrak='".$param['nokontrakDt']."' and nokontrak_ref='".$data['nokontrak']."'";
                        if(!mysql_query($supdate)){
                            exit("warning: ".mysql_error($conn)."___".$supdate);
                        }
                        #nginsert data tambahannya
                        $sInsert="insert into ".$dbname.".pabrik_timbangan (notransaksi,tanggal,kodecustomer,kodebarang,jammasuk,beratmasuk,jamkeluar,beratkeluar,nokendaraan,supir,timbangonoff,statussortasi,nokontrak,nosipb,username,sloc,kodeorg,millcode,beratbersih,kgpembeli) values ";
                        $sInsert.=" ('".$param['notiket'].$kodeorg2."','".$data['tanggal']."','".$data['kodecustomer']."','".$data['kodebarang']."','".$data['jammasuk']."','".$data['beratmasuk']."','".$data['jamkeluar']."','".$data['beratkeluar']."','".$data['nokendaraan']."','".$data['supir']."','".$data['timbangonoff']."','".$data['statussortasi']."','".$param['nokontrakDt']."','".$data['nosipb']."','".$data['username']."','".$data['sloc']."','','".$param['millcode']."','".$data['beratbersih']."','".$data['kgpembeli']."')";
                        if(!mysql_query($sInsert)){
                                exit("warning:".mysql_error($conn)."___".$sInsert);
                        }
		}
		break;
		case'pilKontrak':
		//exit("error:".$param['obc']);
		#ambil nokontrak induk dan kodept
		//print_r('disini');
		$sNkntrk="select distinct nokontrak from ".$dbname.".pabrik_timbangan where 
		          notransaksi='".$param['notiket']."' and millcode='".$param['millcode']."'";
		$qNkntrk=mysql_query($sNkntrk) or die(mysql_error($conn));
		$rNkntrk=mysql_fetch_assoc($qNkntrk);
		$nokontrak=$rNkntrk['nokontrak'];
		
		$sPt="select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['millcode']."'";
		$qPt=mysql_query($sPt) or die(mysql_error($conn));
		$rPt=mysql_fetch_assoc($qPt);
		
		#ambil detail data
		$optKontrak.="<option value=''>No.Kontrak-Kuota-Terpenuhi-Sisa</option>";
		$sDet="select c.nokontrak,kuota,terpenuhi,toleransi  from ".$dbname.".pmn_kontrakjual b 
                       left join ".$dbname.".pmn_kontrakjualdt c on b.nokontrak=c.nokontrak
                       where c.nokontrak_ref='".$nokontrak."' and kodept='".$rPt['induk']."'";
                
		$qDet=mysql_query($sDet) or die(mysql_error($conn));
		while($rDet=mysql_fetch_assoc($qDet)){
			@$dtTol=$rDet['kuota']*(intval($rDet['toleransi'])/100);
			$rDet['kuota']=$rDet['kuota']+$dtTol;
			if($rDet['terpenuhi']<$rDet['kuota']){
                                $rDet['sisa']=$rDet['kuota']-$rDet['terpenuhi'];
				$optKontrak.="<option value='".$rDet['nokontrak']."'>".$rDet['nokontrak']."-".$rDet['kuota']."-".$rDet['terpenuhi']."-".$rDet['sisa']."</option>";
			}
		}
		$formdata.="<table cellpadding=1 cellspacing=1 border=0>";
		$formdata.="<tr>";
		$formdata.="<td>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['detail']."</td>";
		$formdata.="<td><select id=nokontrakDt>".$optKontrak."</select></td>";
		$formdata.="</tr>";
		$formdata.="<tr>";
		$formdata.="<td>".$_SESSION['lang']['tanggal']."</td>";  
		$formdata.="<td><input type=text id=tanggal_".$param['notiket']." value='".$param['tanggal']."' class=myinputtext readonly=readonly /></td>";
		$formdata.="</tr>";
		$formdata.="<tr><td colspan=2><button onclick=post('','".$param['notiket']."','".$param['millcode']."',".$param['rw'].") class=mybutton>".$_SESSION['lang']['posting']."</button></td></tr>";
		$formdata.="</table>";
		echo $formdata;
		break;
		case'getPt':
			$optDt.="<option value=''>".$_SESSION['lang']['all']."</option>";
			$sDt="select distinct c.kodeorganisasi,c.namaorganisasi from ".$dbname.".pmn_kontrakjual b inner join ".$dbname.".pabrik_timbangan a on b.nokontrak=a.nokontrak 
				  left join ".$dbname.".organisasi c on b.kodept=c.kodeorganisasi
			      where  millcode='".$param['pabrik']."' and date(tanggal) between '".tanggalsystem($param['tanggal1'])."' and '".tanggalsystem($param['tanggal2'])."'  ";
			$qDt=mysql_query($sDt)  or die(mysql_error($conn));
			while($rDt=mysql_fetch_assoc($qDt)){
				$optDt.="<option value='".$rDt['kodeorganisasi']."'>".$rDt['namaorganisasi']."</option>";
			}     
			echo $optDt;
		break;
}