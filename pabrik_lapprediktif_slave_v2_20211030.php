<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

//$proses=$_GET['proses'];
$proses = checkPostGet('proses','');
$pabrik = checkPostGet('pabrikv','');
$station = checkPostGet('stationv','');
$machine = checkPostGet('mesinv','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1v',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2v',''));
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");
if($tgl1v=='--'){
    $tgl1v='';
}
if($tgl2v=='--'){
    $tgl2v='';
}

if($proses=='excel'){
    $border="border=1";
}else{
    $border="border=0";
}

//bgcolor=#CCCCCC border='1'

  $stream="<table cellspacing='1' $border class='sortable'>";
      /*$stream.="<thead><tr class=rowheader>
       
            <td align=center>No</td>
    
        </tr></thead>
      <tbody>";*/
      
$stationTambah='';
if($station!=''){
    $stationTambah.="and statasiun='".$station."'";
}
if($machine!=''){
    $stationTambah.=" and mesin='".$machine."'";
}
      
$iSta="SELECT distinct(statasiun) as statasiun  FROM ".$dbname.".pabrik_predictiveht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." order by statasiun asc";
$nSta=  mysql_query($iSta) or die (mysql_error($conn));
while($dSta= mysql_fetch_assoc($nSta)){
    $liststasiun[$dSta['statasiun']]=$dSta['statasiun'];
}

$iList="SELECT * FROM ".$dbname.".pabrik_predictiveht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." order by statasiun,mesin asc";
$nList=mysql_query($iList) or die (mysql_error($conn));	
while($dList=mysql_fetch_assoc($nList)){
    $listmesin[$dList['statasiun']][$dList['mesin']][$dList['notransaksi']] = $dList;
}

$iBarang="select * from ".$dbname.".pabrik_predictivedt "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_predictiveht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,kodebarang";
$nBarang=  mysql_query($iBarang) or die (mysql_error($conn));
while($dBarang=  mysql_fetch_assoc($nBarang)){
    $listbarang[$dBarang['kodebarang']]=$dBarang['kodebarang'];
    $barang[$dBarang['notransaksi']][]=$dBarang['kodebarang'];
    $satuanbarang[$dBarang['notransaksi']][$dBarang['kodebarang']]=$dBarang['satuan'];
    $jumlahbarang[$dBarang['notransaksi']][$dBarang['kodebarang']]=$dBarang['jumlah'];
	$hargabarang[$dBarang['notransaksi']][$dBarang['kodebarang']]=$dBarang['harga'];
}

#karyawan
$iKar="select * from ".$dbname.".pabrik_predictivedt_karyawan "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_predictiveht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,karyawanid";

$nKar=  mysql_query($iKar) or die (mysql_error($conn));
while($dKar=  mysql_fetch_assoc($nKar)){
    $listkar[$dKar['karyawanid']]=$dKar['karyawanid'];
    $kar[$dKar['notransaksi']][]=$dKar['karyawanid'];
}

if(is_array($listmesin)){
	foreach ($listmesin as $stasiun=>$row){
		foreach($row as $mesin=>$row2){
			foreach($row2 as $notransaksi=>$list){
				$listmesin[$stasiun][$mesin][$notransaksi]['barang'] = $barang[$notransaksi];
			}
		}
	}
}else{
	$listmesin='';
}

$nmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikKar= makeOption($dbname,'datakaryawan','karyawanid,nik');

$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
    "pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");

$noList=0;

if(is_array($listmesin)){
	$gtjmlharga=0;
	foreach ($listmesin as $stasiun=>$row){
		if(in_array($stasiun, $liststasiun)) {
			$ttjmlharga=0;
			$stream.="<thead>";
			$stream.="<tr class=rowheader><td align=left colspan=11><b>Station : ".$stasiun." - ".$nmOrg[$stasiun]."</td></tr>";
			$stream.="<tr class=rowheader><td align=left colspan=11><b>Pabrik : ".$pabrik." - ".$nmOrg[$pabrik]."</td></tr>";
			$stream.="</thead><tbody>";
			/*
			$stream.="<td align=left>Station : ".$stasiun."</td>
					<td colspan=7></td></tr>";
			$stream.="<td align=left>Pabrik : ".$pabrik."</td>
					<td colspan=7></td></tr></thead>";
					  */
			foreach($row as $mesin=>$row2){
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center colspan=11><b>".$mesin." - ".$nmOrg[$mesin]."</td>";
				$stream.="</tr>";
				//No.	Tanggal	Uraian Kerusakan / Kegiatan	Bagian yang diganti / rusak	
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center><b>".$_SESSION['lang']['notransaksi']."</td>";
				$stream.="<td align=center><b>No</td>";
				$stream.="<td align=center><b>Tanggal</td>";
				$stream.="<td align=center><b>Uraian Kerusakan / Kegiatan</td>";
				$stream.="<td align=center><b>Bagian yang diganti / rusak</td>";
				$stream.="<td align=center><b>Jumlah</td>";
				$stream.="<td align=center><b>Satuan</td>";
				//$stream.="<td align=center><b>Harga</td>";
				//$stream.="<td align=center><b>Jumlah Harga</td>";
				$stream.="<td align=center><b>Mekanik</td>";
				//$stream.="<td align=center><b>Status</td>";
				$stream.="</tr>";
				$stjmlharga=0;
				$no=0;
				foreach($row2 as $notransaksi=>$list){
					$no+=1;
					$i=0;
					if(count($list['barang'])>=count($kar[$notransaksi])){
						$rowspan=count($list['barang']);
					}else{
						$rowspan=count($kar[$notransaksi]);
					}
					$rowspan=$rowspan==0 ? 1 : $rowspan;
					$stream.="<tr class=rowcontent>";
					$stream.="<td rowspan='".$rowspan."'>".$notransaksi.' '.$rowspan.' '.$rowk."</td>";
					$stream.="<td rowspan='".$rowspan."'>".$no."</td>";
					$stream.="<td rowspan='".$rowspan."'>".$list['tanggal']."</td>";
					$stream.="<td rowspan='".$rowspan."'>".$list['kegiatan']."</td>";
					//Uraian Kerusakan / Kegiatan
					//$stream.="<td rowspan='".$colspan."'>".$notransaksi."</td>";
					//$stream.="<td rowspan='".$colspan."'>".$list['kegiatan']."</td>";
					if(empty($list['barang']) and empty($kar[$notransaksi])){
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
						//$stream.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
						$stream.="</tr>";
					}else {
						if(count($list['barang'])>0){
							foreach ($list['barang'] as $brg){
								if($i>0){
									$stream.="<tr class=rowcontent>";
								}
								$jmlharga=$jumlahbarang[$notransaksi][$brg]*$hargabarang[$notransaksi][$brg];
								$stream.="<td>".$nmBrg[$brg]."</td>";
								$stream.="<td align='right'>".$jumlahbarang[$notransaksi][$brg]."</td>";
								$stream.="<td>".$satuanbarang[$notransaksi][$brg]."</td>";
								//$stream.="<td align='right'>".number_format($hargabarang[$notransaksi][$brg],2)."</td>";
								//$stream.="<td align='right'>".number_format($jmlharga,2)."</td>";
								$stream.="<td align='right'>".$nmKar[$kar[$notransaksi][$i]]."</td>";
								$stjmlharga+=$jmlharga;
								$ttjmlharga+=$jmlharga;
								$gtjmlharga+=$jmlharga;
								$i++;
								if($i==1){
									//$stream.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
								}
								if($i>0){
									$stream.="</tr>";
								}
							}
						}
						if(count($kar[$notransaksi])>count($list['barang'])){
							$sisa=count($kar[$notransaksi])-count($list['barang']);
							for($x=0;$x<$sisa;$x++){
								if($x>0){
									$stream.="<tr class=rowcontent>";
								}
								$stream.="<td class=rowcontent></td>";
								$stream.="<td class=rowcontent></td>";
								$stream.="<td class=rowcontent></td>";
								$stream.="<td class=rowcontent></td>";
								$stream.="<td class=rowcontent></td>";
								if(count($list['barang'])==0){
									$stream.="<td class=rowcontent align='right'>".$nmKar[$kar[$notransaksi][$x]]."</td>";
									//$stream.="<td class=rowcontent>".$list['statusketuntasan']."</td>";
								}else{
									$stream.="<td class=rowcontent align='right'>".$nmKar[$kar[$notransaksi][$x+$i]]."</td>";
								}
								if($x>0){
									$stream.="</tr>";
								}
							}
						}
					}
				}
				/*
				if($no>0){
					$stream.="<tr class=rowcontent>";
					$stream.="<td></td>";
					$stream.="<td colspan=7 align='center'>Sub Total</td>";
					$stream.="<td align='right'>".number_format($stjmlharga,2)."</td>";
					$stream.="<td></td>";
					$stream.="<td></td>";
					$stream.="</tr>";
				}
				*/
			}
			/*
			if($no>0){
				$stream.="<tr class=rowcontent>";
				$stream.="<td></td>";
				$stream.="<td colspan=7 align='center'>Total</td>";
				$stream.="<td align='right'>".number_format($ttjmlharga,2)."</td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="</tr>";
			}
			*/
		}
	}
	/*
	if($no>0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td></td>";
		$stream.="<td colspan=7 align='center'>Grand Total</td>";
		$stream.="<td align='right'>".number_format($gtjmlharga,2)."</td>";
		$stream.="<td></td>";
		$stream.="<td></td>";
		$stream.="</tr>";
	}
	*/
}else{
  echo "No data found";
}
$stream.="</tbody></table>";
//exit('Warning: '.$stream);

#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses){
    case'getStationv':
        $optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
        $iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$pabrik."' ";     
        $nStation=mysql_query($iStation) or die(mysql_error($conn));
        while($dStation=mysql_fetch_assoc($nStation)){
            $optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
        }  
        echo $optStation;
    break;

######HTML
	case 'preview':
		if($tgl1=='' || $tgl2=='' || $pabrik==''){
			exit("Please Complate the form");
		}
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		if($tgl1=='' || $tgl2=='' || $pabrik==''){
			exit("Please Complate the form");
		}
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_PERAWATAN_MESIN_V2_".$tglSkrg;
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
	
	default:
	break;
}
?>
