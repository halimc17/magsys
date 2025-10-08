<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$stasiun=checkPostGet('stasiun','');
$jenis=checkPostGet('jenis','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));//merubah dari 10-10-2014 menjadi 20141010
$tgllama=tanggalsystem(checkPostGet('tgllama',''));//merubah dari 10-10-2014 menjadi 20141010
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
$whr="";
if($kodeorg!=''){
	$whr=" and a.kodeorg='".$kodeorg."'";
}
if($stasiun!=''){
	$whr=" and a.kodestasiun='".$stasiun."'";
}
$kodeorgP=checkPostGet('kodeorgP','');
$stasiunP=checkPostGet('stasiunP','');
$jenisP=checkPostGet('jenisP','');
$tanggalP=tanggalsystem(checkPostGet('tanggalP',''));//merubah dari 10-10-2014 menjadi 20141010
$tgllamaP=tanggalsystem(checkPostGet('tgllamaP',''));//merubah dari 10-10-2014 menjadi 20141010
$keteranganP=checkPostGet('keteranganP','');
$addeditP=checkPostGet('addeditP','');
$whrP="";
if($kodeorgP!=''){
	$whrP=" and a.kodeorg='".$kodeorgP."'";
}
if($stasiunP!=''){
	$whrP=" and a.kodestasiun='".$stasiunP."'";
}
//exit('Warning: '.$proses.' '.$_POST['kodeorg'].' '.$_GET['kodeorg']);
switch($proses){
	case'loadData':
		$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_preventifpanel a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodestasiun
				where jenis='Real' ".$whr."
				order by a.kodeorg,a.kodestasiun,a.jenis,a.tanggal desc";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo "
			<tr class=rowcontent >
				<td ".$drcl." align=center width='4%'>".$bar->kodeorg."</td>
				<td ".$drcl." align=center width='6%'>".$bar->kodestasiun."</td>
				<td ".$drcl." align=left width='30%'>".$bar->namaorganisasi."</td>
				<td ".$drcl." align=center width='4%'>".$bar->jenis."</td>
				<td ".$drcl." align=center width='6%'>".tanggalnormal($bar->tanggal)."</td>
				<td ".$drcl." align=left>".$bar->keterangan."</td>
				<td align=center width='7%'>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick =\"fillfield('".$bar->kodeorg."','".$bar->kodestasiun."','".$bar->jenis."','".tanggalnormal($bar->tanggal)."','".$bar->keterangan."')\">&nbsp
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick =\"deldata('".$bar->kodeorg."','".$bar->kodestasiun."','".$bar->jenis."','".tanggalnormal($bar->tanggal)."','".$bar->keterangan."');\">&nbsp
					<img src=images/zoom.png class=resicon title='Detail' onclick =\"showpopup('".$bar->kodestasiun."','".substr($bar->tanggal,0,7)."','".$bar->kodeorg."','".$bar->namaorganisasi."','','preview',event);\">&nbsp
					<img src=images/skyblue/excel.jpg class=resicon title='Detail' onclick =\"showpopup('".$bar->kodestasiun."','".substr($bar->tanggal,0,7)."','".$bar->kodeorg."','".$bar->namaorganisasi."','','excel',event);\">
				</td>
			</tr>";	
		}
	break;

	case'loadDataP':
		$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_preventifpanel a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodestasiun
				where jenis='Plan' ".$whrP."
				order by a.kodeorg,a.kodestasiun,a.jenis,a.tanggal desc";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo "
			<tr class=rowcontent >
				<td ".$drcl." align=center width='4%'>".$bar->kodeorg."</td>
				<td ".$drcl." align=center width='6%'>".$bar->kodestasiun."</td>
				<td ".$drcl." align=left width='30%'>".$bar->namaorganisasi."</td>
				<td ".$drcl." align=center width='4%'>".$bar->jenis."</td>
				<td ".$drcl." align=center width='6%'>".tanggalnormal($bar->tanggal)."</td>
				<td ".$drcl." align=left>".$bar->keterangan."</td>
				<td align=center width='7%'>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick =\"fillfieldP('".$bar->kodeorg."','".$bar->kodestasiun."','".$bar->jenis."','".tanggalnormal($bar->tanggal)."','".$bar->keterangan."')\">&nbsp
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick =\"deldataP('".$bar->kodeorg."','".$bar->kodestasiun."','".$bar->jenis."','".tanggalnormal($bar->tanggal)."','".$bar->keterangan."');\">&nbsp
					<img src=images/zoom.png class=resicon title='Detail' onclick =\"showpopup('".$bar->kodestasiun."','".substr($bar->tanggal,0,7)."','".$bar->kodeorg."','".$bar->namaorganisasi."','','preview',event);\">&nbsp
					<img src=images/skyblue/excel.jpg class=resicon title='Detail' onclick =\"showpopup('".$bar->kodestasiun."','".substr($bar->tanggal,0,7)."','".$bar->kodeorg."','".$bar->namaorganisasi."','','excel',event);\">
				</td>
			</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_preventifpanel 
				where kodeorg='".$kodeorg."' and kodestasiun='".$stasiun."' and jenis='".$jenis."' and tanggal='".$tanggal."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'delDataP':
		$strx="delete from ".$dbname.".pabrik_preventifpanel 
				where kodeorg='".$kodeorgP."' and kodestasiun='".$stasiunP."' and jenis='".$jenisP."' and tanggal='".$tanggalP."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			$strs="select * from ".$dbname.".pabrik_preventifpanel
					where kodeorg='".$kodeorg."' and kodestasiun='".$stasiun."' and jenis='".$jenis."' and tanggal='".$tgllama."'";
			$ress=mysql_query($strs);
			while($bars=mysql_fetch_object($ress)){
				$kodeorglama=$bars->kodeorg;
				$kodestasiunlama=$bars->kodestasiun;
				$jenislama=$bars->jenis;
				$tanggallama=tanggalsystem(tanggalnormal($bars->tanggal));
				$keteranganlama=$bars->keterangan;
			}
			if($kodeorglama==$kodeorg and $kodestasiunlama==$stasiun and $jenislama==$jenis){
				//exit('Warning: masuk'.$kodeorglama.'=='.$kodeorg.' '.$kodemesinlama.'=='.$mesin.' '.$tanggallama.'=='.$tanggal.' '.$hmawallama.'=='.round($hmawal,2).' '.$hmakhirlama.'=='.round($hmakhir,2).' '.$teballama.'=='.round($tebal,2));
			}else{
				$strs="select * from ".$dbname.".pabrik_preventifpanel
						where kodeorg='".$kodeorg."' and kodestasiun='".$stasiun."' and jenis='".$jenis."' and tanggal='".$tanggal."'";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".pabrik_preventifpanel set tanggal='".$tanggal."',keterangan='".$keterangan."'
				,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".substr($stasiun,0,4)."' and kodestasiun='".$stasiun."' and jenis='".$jenis."' and tanggal='".$tgllama."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_preventifpanel
				(kodeorg,kodestasiun,jenis,tanggal,keterangan,lastuser,lastdate)
				values('".substr($stasiun,0,4)."','".$stasiun."','".$jenis."','".$tanggal."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'saveDataP':
		if($addeditP=='update'){
			$strs="select * from ".$dbname.".pabrik_preventifpanel
					where kodeorg='".$kodeorgP."' and kodestasiun='".$stasiunP."' and jenis='".$jenisP."' and tanggal='".$tgllamaP."'";
			$ress=mysql_query($strs);
			while($bars=mysql_fetch_object($ress)){
				$kodeorglama=$bars->kodeorg;
				$kodestasiunlama=$bars->kodestasiun;
				$jenislama=$bars->jenis;
				$tanggallama=tanggalsystem(tanggalnormal($bars->tanggal));
				$keteranganlama=$bars->keterangan;
			}
			if($kodeorglama==$kodeorgP and $kodestasiunlama==$stasiunP and $jenislama==$jenisP){
				//exit('Warning: masuk'.$kodeorglama.'=='.$kodeorg.' '.$kodemesinlama.'=='.$mesin.' '.$tanggallama.'=='.$tanggal.' '.$hmawallama.'=='.round($hmawal,2).' '.$hmakhirlama.'=='.round($hmakhir,2).' '.$teballama.'=='.round($tebal,2));
			}else{
				$strs="select * from ".$dbname.".pabrik_preventifpanel
						where kodeorg='".$kodeorgP."' and kodestasiun='".$stasiunP."' and jenis='".$jenisP."' and tanggal='".$tanggalP."'";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!'.$strs);
				}
			}
			$strx="update ".$dbname.".pabrik_preventifpanel set tanggal='".$tanggalP."',keterangan='".$keteranganP."'
				,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".substr($stasiunP,0,4)."' and kodestasiun='".$stasiunP."' and jenis='".$jenisP."' and tanggal='".$tgllamaP."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_preventifpanel
				(kodeorg,kodestasiun,jenis,tanggal,keterangan,lastuser,lastdate)
				values('".substr($stasiunP,0,4)."','".$stasiunP."','".$jenisP."','".$tanggalP."','".$keteranganP."','".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getStasiun':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($kodeorg!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and induk='".$kodeorg."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}
		}
		echo $optStation;
    break;

	case'getStasiunP':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($kodeorgP!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and induk='".$kodeorgP."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}
		}
		echo $optStation;
    break;

	default:
	break;
}
?>
