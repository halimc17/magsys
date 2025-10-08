<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$kodeorglama=checkPostGet('kodeorglama','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$tglkeluar=tanggalsystem(checkPostGet('tglkeluar',''));
$karyawanid=checkPostGet('karyawanid','');
$karyawanidlama=checkPostGet('karyawanidlama','');
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
$carikodeorg=checkPostGet('carikodeorg','');
$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
$carikaryawanid=checkPostGet('carikaryawanid','');
switch($proses){
	case'loadData':
		$where="";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where.="True";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.="a.kodeorg not like '%HO' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
		}else{
			$where.="a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		if($carikodeorg!=''){
			$where.=" and a.kodeorg like '".$carikodeorg."%'";
		}
		if($caritanggal1!='' and $caritanggal2==''){
			$caritanggal2=$caritanggal1;
		}
		if($caritanggal1=='' and $caritanggal2!=''){
			$caritanggal1=$caritanggal2;
		}
		if($caritanggal1!='' and $caritanggal2!=''){
			$where.=" and a.tanggal>='".$caritanggal1."' and a.tanggal<='".$caritanggal2."'";
		}
		if($carikaryawanid!=''){
			$where.=" and a.karyawanid='".$carikaryawanid."'";
		}
		$strb="select a.* from ".$dbname.".sdm_phkcuti a
				where ".$where." 
				order by a.kodeorg,a.tanggal,a.karyawanid";
		//exit('Warning: '.$strb);
		$resb=mysql_query($strb);
		$jlhbrs=mysql_num_rows($resb);
		$limit=25;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',0);
			if((($page*$limit)+1)>$jlhbrs)
				$page=$page-1;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		//$str="select a.*,b.nik,b.namakaryawan,c.sisa from ".$dbname.".sdm_phkcuti a
		//		left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
		//		left join ".$dbname.".sdm_cutiht c on c.karyawanid=a.karyawanid and c.kodeorg=a.kodeorg and c.periodecuti=left(a.tglkeluar,4)
		//		where ".$where." 
		//		order by a.kodeorg,a.tanggal,a.karyawanid limit ".$offset.",".$limit."";
		$str="select a.*,b.nik,b.namakaryawan,c.sisa from ".$dbname.".sdm_phkcuti a
				left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				left join ".$dbname.".sdm_cutiht c on c.karyawanid=a.karyawanid and c.periodecuti=left(a.tglkeluar,4)
				where ".$where." 
				order by a.kodeorg,a.tanggal,a.karyawanid limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=center>".$bar->nik."</td>
					<td ".$drcl." align=left>".$bar->namakaryawan."</td>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl." align=center>".$bar->tglkeluar."</td>
					<td ".$drcl." align=right>".number_format($bar->sisa,2)."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center>";
			if($bar->posting==0){
				echo"	<img src='images/skyblue/edit.png' class='resicon' title='Edit'
						onclick=\"fillfield('".$bar->kodeorg."','".$bar->karyawanid."','".tanggalnormal($bar->tanggal)."','".tanggalnormal($bar->tglkeluar)."','".$bar->keterangan."')\">&nbsp&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->karyawanid."');\">&nbsp&nbsp
						<img src=images/skyblue/posting.png class=resicon title='ReCalculating' onclick=\"calcdata('".$bar->kodeorg."','".$bar->karyawanid."','".tanggalnormal($bar->tglkeluar)."');\">";
			}else{
				echo"	<img src=images/skyblue/posted.png class=resicon title='ReCalculated'>";
			}
			echo"	</td>
				</tr>";	
		}
		echo"
		<tr class=rowheader>
			<td colspan=7 align=center>
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>&nbsp
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."&nbsp
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
	break;

	case'delData':
		$strx="delete from ".$dbname.".sdm_phkcuti 
				where kodeorg='".$kodeorg."' and karyawanid='".$karyawanid."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			if($kodeorglama!=$kodeorg or $karyawanidlama!=$karyawanid){
				$strs="select * from ".$dbname.".sdm_phkcuti
						where (kodeorg='".$kodeorg."' and karyawanid='".$karyawanid."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".sdm_phkcuti set kodeorg='".$kodeorg."',karyawanid='".$karyawanid."',tanggal='".$tanggal."',tglkeluar='".$tglkeluar."'
				,keterangan='".$keterangan."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".$kodeorglama."' and karyawanid='".$karyawanidlama."'";
		}else{
			$strx="insert into ".$dbname.".sdm_phkcuti
				(kodeorg,karyawanid,tanggal,tglkeluar,keterangan,lastuser,lastdate) values ('".$kodeorg."','".$karyawanid."','".$tanggal."','".$tglkeluar."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
			exit;
		}
	break;

	case'calcData':
		$strx="update ".$dbname.".datakaryawan set tanggalkeluar='".$tglkeluar."'
				where karyawanid='".$karyawanid."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
			exit;
		}
		$strx="select * from ".$dbname.".sdm_cutiht where karyawanid='".$karyawanid."' and periodecuti='".substr($tglkeluar,0,4)."'";
		$resx=mysql_query($strx);
		$hakawalcuti=0;
		$haktambahan=0;
		while($barx=mysql_fetch_object($resx)){
			if(substr($barx->kodeorg,2,2)=='HO'){
				if($barx->hakcuti>=25){
					$hakawalcuti=25;
					$haktambahan=$barx->hakcuti - 25;
				}else{
					$hakawalcuti=12;
					$haktambahan=$barx->hakcuti - 12;
				}
			}else{
				$hakawalcuti=$barx->hakcuti;
				$haktambahan=0;
			}
		}
		$hakcuti=(substr($tglkeluar,4,2)/12*$hakawalcuti)+$haktambahan;
		//exit('warning: tgl='.substr($tglkeluar,4,2).' hakawalcuti='.$hakawalcuti.' haktambahan='.$haktambahan.' hakcuti='.$hakcuti);
		$strx="update ".$dbname.".sdm_cutiht set hakcuti='".$hakcuti."',sisa=".$hakcuti."-diambil
				where karyawanid='".$karyawanid."' and periodecuti='".substr($tglkeluar,0,4)."'";
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
			exit;
		}
		$strx="update ".$dbname.".sdm_phkcuti set posting=1,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".$kodeorg."' and karyawanid='".$karyawanid."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
			exit;
		}
	break;

	case'getKary':
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
					where tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
					where lokasitugas not like '%HO' and kodeorganisasi='".$_SESSION['empl']['induk']."' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
		}else{
			$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
					where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
		}
		/*
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
				where lokasitugas='".$kodeorg."' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
		*/
		$optKary="<option value=''></option>";
		$qKary=mysql_query($sKary);
		while($rKary=mysql_fetch_object($qKary)){
			$optKary.="<option value='".$rKary->karyawanid."'>[".$rKary->nik.'] '.$rKary->namakaryawan."</option>";
		}
		echo $optKary;
    break;

	case'getUnit':
		$unitKary=$_SESSION['empl']['lokasitugas'];
		$sKary="select lokasitugas from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$qKary=mysql_query($sKary);
		while($rKary=mysql_fetch_object($qKary)){
			$unitKary=$rKary->lokasitugas;
		}
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and length(kodeorganisasi)=4 order by kodeorganisasi";
			$optUnit="<option value=''></option>";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and length(kodeorganisasi)=4 and	induk='".$_SESSION['empl']['induk']."' order by kodeorganisasi";
			$optUnit="<option value=''></option>";
		}else{
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
			$optUnit="";
		}
		$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
		while($rUnit=mysql_fetch_assoc($qUnit)){
			$optUnit.="<option value='".$rUnit['kodeorganisasi']."' ".($unitKary==$rUnit['kodeorganisasi'] ? 'selected' : '').">[".$rUnit['kodeorganisasi'].'] - '.$rUnit['namaorganisasi']."</option>";
		}
		echo $optUnit;
    break;

	default:
	break;
}
?>
