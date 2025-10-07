<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodebarang=checkPostGet('kodebarang','');
$kodegudang=checkPostGet('kodegudang','');
$nodok=checkPostGet('nodok','');
$nopo=checkPostGet('nopo','');
$kodetrp=checkPostGet('kodetrp','');
$jumlah=checkPostGet('jumlah','');
$method=checkPostGet('method','');
$namaBarangCari=checkPostGet('namaBarangCari','');
$namaDokCari=checkPostGet('namaDokCari','');
$nodoksch=checkPostGet('nodoksch','');

$nmBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

switch($method)
{
    case'getListBarang':
        echo"	
            <fieldset  style='float:left;' >
            <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                            <tr>
                                <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                                <td colspan=5>: 
                                        <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                        <button class=mybutton onclick=cariListBarang()>cari</button>
                                <td>
                            <tr>
                            </table>

                            <table id=listCariBarang >
                            <thead>
                            <tr class=rowheader>
                                    <td>No</td>
                                    <td>".$_SESSION['lang']['kodebarang']."</td>
                                    <td>".$_SESSION['lang']['namabarang']."</td>
                                    <td>".$_SESSION['lang']['satuan']."</td>
                            </tr></thead>";

                    if($namaBarangCari=='')
                    {}
                    else
                    {
                        $i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where (namabarang like '%".$namaBarangCari."%' or kodebarang like '%".$namaBarangCari."%')";
                      
                        $n=mysql_query($i) or die (mysql_error($conn));
                        while ($d=mysql_fetch_assoc($n))
                        {
                            $no+=1;
                            echo"
                                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."');\">
                                            <td>".$no."</td>
                                            <td>".$d['kodebarang']."</td>
                                            <td>".$nmBrg[$d['kodebarang']]."</td>
                                            <td>".$satBrg[$d['kodebarang']]."</td>
                                    </tr>";
                        }
                    }
                    echo"</table>
            </fieldset>";
	
    break;
    
    case'getListDok':
        echo"	
            <fieldset  style='float:left;' >
            <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['nodok']."</legend>
                <table cellspacing=1 border=0 class=data>
					<tr>
						<td colspan=2>".$_SESSION['lang']['nodok']."</td>
						<td colspan=5>: 
								<input type=text id=namaDokCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=cariListDok()>cari</button>
						<td>
					<tr>
					</table>
					
					<table id=listCariDok >
					<thead>
					<tr class=rowheader>
							<td>No</td>
							<td>".$_SESSION['lang']['nopo']."</td>
					</tr></thead>";
					
                    if(!empty($namaDokCari)) {
                        $i="select nopo from ".$dbname.".log_poht
							where nopo like '%".$namaDokCari."%'
							and nopo not in (select nodok from ".$dbname.".log_biayakirim)
							order by nopo";
						
                        $n=mysql_query($i) or die (mysql_error($conn));
                        while ($d=mysql_fetch_assoc($n))
                        {
                            $no+=1;
                            echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataDok('".$d['nopo']."');\">
										<td>".$no."</td>
										<td>".$d['nopo']."</td>
										
								</tr>";
                        }
                    }
                    echo"</table>
            </fieldset>";
		break;
	
	case 'getBarang':
		$qBarang = "SELECT a.kodebarang, b.namabarang FROM ".$dbname.".log_podt a
			LEFT JOIN ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
			where a.nopo = '".$nopo."'";
		$resBarang = fetchData($qBarang);
		$optBarang = array();
		foreach($resBarang as $row) {
			$optBarang[$row['kodebarang']] = $row['namabarang'];
		}
		echo json_encode($optBarang);
		break;

    case 'insert':
            $i="insert into ".$dbname.".log_biayakirim (kodebarang,nodok,kodegudang,kodetrp,jumlah,updateby)
            values ('".$kodebarang."','".$nodok."','".$kodegudang."','".$kodetrp."','".$jumlah."','".$_SESSION['standard']['userid']."')";
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;

    case 'update':
            $i="update ".$dbname.".log_biayakirim set updateby='".$_SESSION['standard']['userid']."',
				kodetrp='".$kodetrp."', jumlah='".$jumlah."'
				where kodebarang='".$kodebarang."' and nodok='".$nodok."' and kodegudang='".$kodegudang."'";
            //exit("Error.$i");
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;
	
		
    case'loadData':
        $sch = "";
        if($nodoksch!='')
        {
            $sch.=" and nodok like '%".$nodoksch."%' ";
        }
		echo"
		<div id=container>
		<table class=sortable cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['nodok']."</td>    
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['gudang']."</td>
					<td align=center>".$_SESSION['lang']['transporter']."</td>
					<td align=center>".$_SESSION['lang']['jumlah']."</td>
					<td align=center>".$_SESSION['lang']['updateby']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
			
		}
		else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
		{
			$sch.=" and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi 
					where tipe in ('GUDANG','GUDANGTEMP') and induk in 
					(select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')
					and kodeorganisasi not like '%HO')";
		}
		else
		{
			$sch.=" and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi 
					where tipe in ('GUDANG','GUDANGTEMP') and induk='".$_SESSION['empl']['lokasitugas']."') ";
		}
		
	
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".log_biayakirim where 1=1 ".$sch." ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$i="select a.*,b.namaorganisasi as namagudang,c.namasupplier from ".$dbname.".log_biayakirim a
			LEFT JOIN ".$dbname.".organisasi b ON a.kodegudang=b.kodeorganisasi
			LEFT JOIN ".$dbname.".log_5supplier c ON a.kodetrp=c.supplierid
			where 1=1 ".$sch." order by posting,nodok desc limit ".$offset.",".$limit."";
		
		$n=mysql_query($i) or die(mysql_error());
		$no=$maxdisplay;
		while($d=mysql_fetch_assoc($n))
		{
			$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
							  "karyawanid='".$d['updateby']."'");
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>".$no."</td>";
			echo "<td align=left>".$d['nodok']."</td>";
			echo "<td align=right>".$d['kodebarang']."</td>";
			echo "<td align=left>".$nmBrg[$d['kodebarang']]."</td>";
			echo "<td align=right>".$d['namagudang']."</td>";
			echo "<td align=right>".$d['namasupplier']."</td>";
			echo "<td align=right>".number_format($d['jumlah'])."</td>";
			echo "<td align=left>".$nmKar[$d['updateby']]." ".tanggalnormal(substr($d['updatetime'],0,10))." ".substr($d['updatetime'],11,30)."</td>";
			//echo "<td align=left>".$d['updatetime']."</td>";
			echo "<td align=center>";
			if($d['posting']==0) {
				echo "<img id='".$d['kodebarang'].$d['nodok']."_edit' src=images/001_45.png class=resicon  caption='Edit' onclick=\"edit('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$d['nodok']."','".$d['kodegudang']."','".$d['kodetrp']."','".$d['jumlah']."');\">
					<img id='".$d['kodebarang'].$d['nodok']."_delete' src=images/delete_32.png class=resicon  caption='Delete' onclick=\"del('".$d['kodebarang']."','".$d['nodok']."','".$d['kodegudang']."');\">";
				echo "<img src=images/hot.png id='".$d['kodebarang'].$d['nodok']."' class=resicon caption='Posting' onclick=\"posting('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$d['nodok']."','".$d['kodegudang']."');\">  
					</td>";
			} else {
				echo "<img src=images/buttongreen.png class=resicon>
					</td>";
			}
			echo "</tr>";
		}
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
    break;

	case 'delete':
		$i="delete from ".$dbname.".log_biayakirim where kodebarang='".$kodebarang.
			"' and nodok='".$nodok."' and kodegudang='".$kodegudang."'";
		//exit("Error.$str");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
		break;
	
	case 'getGudang':
		$qGudang = "SELECT a.kodegudang, b.namaorganisasi FROM ".$dbname.".log_transaksi_vw a
			LEFT JOIN ".$dbname.".organisasi b on a.kodegudang=b.kodeorganisasi
			where a.tipetransaksi=1 and statussaldo=1 and a.nopo='".$nopo."'";//echo $qGudang;
		$resGudang = fetchData($qGudang);
		$optGudang = array();
		foreach($resGudang as $row) {
			$optGudang[$row['kodegudang']] = $row['namaorganisasi'];
		}
		echo json_encode($optGudang);
		break;
	
	case 'posting':
		$kodeJurnal = "EXP01";
		
		// Tanggal sekarang
		$tgl = date('Y-m-d');
		$tglEntry = date('Y-m-d');
		$tglPeriod = $_SESSION['gudang'][$kodegudang]['tahun']."-".
			$_SESSION['gudang'][$kodegudang]['bulan'];
		
		if($tglPeriod != date('Y-m')) {
			$lastday = cal_days_in_month(CAL_GREGORIAN,
										 $_SESSION['gudang'][$kodegudang]['bulan'],
										 $_SESSION['gudang'][$kodegudang]['tahun']);
			$tgl = $tglPeriod.'-'.$lastday;
		}
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		// Get Data
		$qTrans = selectQuery($dbname,'log_biayakirim','*',"nodok='".$nopo.
							  "' and kodebarang='".$kodebarang."' and kodegudang='".$kodegudang."'");
		$resTrans = fetchData($qTrans);
		$data = $resTrans[0];
		if($data['posting']==1) {
			exit("Transaksi sudah pernah di posting");
		}
		#lakukan pengecekan fisik masih ada atau tidak
		$sCek="select saldoakhirqty from ".$dbname.".log_5saldobulanan where kodegudang='".$kodegudang."' and kodebarang='".$kodebarang."' and periode='".substr($tgl,0,7)."'";
		$qCek=mysql_query($sCek) or die(mysql_error($conn));
		$rCek=mysql_fetch_assoc($qCek);
		if(abs($rCek['saldoakhirqty'])==0){
			exit("warning: Saldo fisik tidak boleh 0");
		}
		// Get Header PO
		$qPO = selectQuery($dbname,'log_poht','*',"nopo = '".$nopo."'");
		$resPO = fetchData($qPO);
		$kodept = $resPO[0]['kodeorg'];
		
		// Get Akun Parameter
		//$qAkun = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
		//					 "kodeaplikasi='LOG' and jurnalid='".$kodeJurnal."'");
		//$resAkun = fetchData($qAkun);
		//if(empty($resAkun)) exit("Warning: Parameter Akun untuk Jurnal EXP01 belum ada".
		//						 "\nSilahkan hubungi IT dengan melampirkan pesan error ini");
		//$akunKredit = $resAkun[0]['noakunkredit'];
		
		// Get Akun Kredit
		$qAkun = selectQuery($dbname,'log_5klsupplier','noakun',
							 "kode='".substr($data['kodetrp'],0,4)."'");
		$resAkun = fetchData($qAkun);
		$akunKredit = $resAkun[0]['noakun'];
		
		// Get Counter Journal
		$qCounter = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
								"kodekelompok='".$kodeJurnal."' and kodeorg='".
								$kodept."'");
		$resCounter = fetchData($qCounter);
		if(empty($resCounter)) exit("Warning: Kelompok Jurnal ".$kodeJurnal.
									" untuk PT.".$kodept." belum ada".
									"\nSilahkan hubungi IT dengan melampirkan pesan error ini");
		$counter = $resCounter[0]['nokounter'];
		
		// Create No Jurnal
		$nojurnal = str_replace('-','',$tgl)."/".substr($kodegudang,0,4)."/".
			$kodeJurnal."/".str_pad($counter+1,3,'0',STR_PAD_LEFT);
		
		// Get Akun Barang
		$qBarang = selectQuery($dbname,'log_5klbarang','noakun',
							   "kode='".substr($kodebarang,0,3)."'");
		$resBarang = fetchData($qBarang);
		$akunDebet = $resBarang[0]['noakun'];
		
		// Data Jurnal Header
		$dataRes['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodeJurnal,
			'tanggal'=>$tgl,
			'tanggalentry'=>$tglEntry,
			'posting'=>'0',
			'totaldebet'=>$data['jumlah'],
			'totalkredit'=>$data['jumlah'],
			'amountkoreksi'=>'0',
			'noreferensi'=>"EXP01-".$nopo."-".$kodebarang."-".$kodegudang,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		// Data Jurnal Detail - Debet
		$dataRes['detail'][0] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tgl,
			'nourut'=>1,
			'noakun'=>$akunDebet,
			'keterangan'=>'Biaya Kirim PO.'.$nopo." Barang ".$kodebarang,
			'jumlah'=>$data['jumlah'],
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>substr($kodegudang,0,4),
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>$kodebarang,
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>"EXP01-".$nopo."-".$kodebarang."-".$kodegudang,
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>"EXP01-".$nopo."-".$kodebarang."-".$kodegudang,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
		
		// Data Jurnal Detail - Kredit
		$dataRes['detail'][1] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tgl,
			'nourut'=>2,
			'noakun'=>$akunKredit,
			'keterangan'=>'Biaya Kirim PO.'.$nopo." Barang ".$kodebarang,
			'jumlah'=>$data['jumlah'] * (-1),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>substr($kodegudang,0,4),
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>$data['kodetrp'],
			'noreferensi'=>"EXP01-".$kodept."-".$kodebarang."-".$kodegudang,
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>"EXP01-".$kodept."-".$kodebarang."-".$kodegudang,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
		
		// Ambil Data Saldo Bulanan
		$pt = $kodept;
		$qS = selectQuery($dbname,'log_5saldobulanan',"*",
								  "kodeorg='".$kodept."' and
								  kodebarang='".$kodebarang."' and
								  kodegudang='".$kodegudang."' and
								  periode='".$tglPeriod."'");
		$resSaldo = fetchData($qS);
		if(empty($resSaldo)) exit("Warning: Saldo bulanan untuk PT ".$kodept.
							 " untuk barang ".$kodebarang." pada gudang ".
							 $kodegudang." pada periode ".$tglPeriod." belum ada");
		else $resSaldo = $resSaldo[0];
		
		/***********************************************************************
		 * Insert Data
		 */
		$errorDB = "";
		
		// Query Delete Jurnal
		$delJurnal = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
		
		# Header
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		if(!mysql_query($queryH)) {
			$errorDB .= "Header :".mysql_error()."\n";
		}
		
		# Detail
		if($errorDB=='') {
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				if(!mysql_query($queryD)) {
					$errorDB .= "Detail ".$key." :".mysql_error()."\n";
				}
			}
			
			if($errorDB=='') {
				// Update Flag
				$updBy = updateQuery($dbname,'log_biayakirim',array('posting'=>1,'postingby'=>$_SESSION['standard']['userid']),
									 "nodok='".$nopo."' and kodebarang='".$kodebarang.
									 "' and kodegudang='".$kodegudang."'");
				if(!mysql_query($updBy)) {
					echo "Error DB: ".mysql_error()."\n";
					if(!mysql_query($delJurnal)) {
						exit(mysql_error());
					}
				} else {
					#=== Update rupiah dan harga rata2 di Saldo bulanan ===
					// Hitung harga rata2
					$nilai = ($resSaldo['hargarata'] * $resSaldo['saldoakhirqty']) + $data['jumlah'];
					$harga = $nilai / $resSaldo['saldoakhirqty'];
					
					$data = array(
						'hargarata' => $harga,
						'nilaisaldoakhir' => $nilai,
						'qtymasukxharga'=> $data['jumlah']+$resSaldo['qtymasukxharga']//update tambahan harga masuk
					);
					
					$tmpPo = explode('/',$nopo);
					
					// Update setelah perhitungan
					$querySaldo = updateQuery($dbname,'log_5saldobulanan',$data,
											  "kodeorg='".$tmpPo[5]."' and
											  kodebarang='".$kodebarang."' and
											  kodegudang='".$kodegudang."' and
											  periode='".$tglPeriod."'");
					if(!mysql_query($querySaldo)) {
						$updRB = updateQuery($dbname,'log_biayakirim',array('posting'=>0),
									 "nodok='".$nopo."' and kodebarang='".$kodebarang.
									 "' and kodegudang='".$kodegudang."'");
						echo "Error DB: ".mysql_error()."\n";
						if(!mysql_query($updRB)) { // Rollback posting
							echo mysql_error()."\n";
						}
						if(!mysql_query($delJurnal)) { // Rollback Delete Jurnal
							echo mysql_error();
						}
						exit;
					}
				}
			} else {
				// Rollback, delete jurnal
				echo "Error DB: \n".$errorDB;
				if(!mysql_query($delJurnal)) {
					exit(mysql_error());
				} else {
					exit;
				}
			}
		} else {
			exit("Error DB: ".$errorDB);
		}
		
		// Posting Success
		#=== Add Counter Jurnal ===
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1),
			"kodeorg='".$pt."' and kodekelompok='".$kodeJurnal."'");
		$errCounter = "";
		if(!mysql_query($queryJ)) {
			$errCounter.= "Update Counter Parameter Jurnal Error :".mysql_error()."\n";
		}
		if($errCounter!="") {
			$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter),
				"kodeorg='".$pt."' and kodekelompok='".$kodeJurnal."'");
			$errCounter = "";
			if(!mysql_query($queryJRB)) {
				$errorJRB .= "Rollback Parameter Jurnal Error :".mysql_error()."\n";
			}
			echo "DB Error :\n".$errorJRB;
			exit;
		}
		break;
	
	default:
}
?>
