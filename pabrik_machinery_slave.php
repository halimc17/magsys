<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$stasiuncr=checkPostGet('stasiuncr','');
$kodeorg=checkPostGet('kodeorg','');
$stasiun=checkPostGet('stasiun','');
$mesin=checkPostGet('mesin','');
$submesin=checkPostGet('submesin','');
$stsmesin=checkPostGet('stsmesin','');
$unit1=checkPostGet('unit1','');
$stsunit1=checkPostGet('stsunit1','');
$unit2=checkPostGet('unit2','');
$stsunit2=checkPostGet('stsunit2','');
$merk=checkPostGet('merk','');
$model=checkPostGet('model','');
$ratio=checkPostGet('ratio','');
$rpm=checkPostGet('rpm','');
$kw=checkPostGet('kw','');
$ampere=checkPostGet('ampere','');
$tahunbuat=checkPostGet('tahunbuat','');
$sn=checkPostGet('sn','');
$sproket1=checkPostGet('sproket1','');
$sproket2=checkPostGet('sproket2','');
$sproket3=checkPostGet('sproket3','');
$stssproket=checkPostGet('stssproket','');
$chain1=checkPostGet('chain1','');
$chain2=checkPostGet('chain2','');
$stschain=checkPostGet('stschain','');
$pully1=checkPostGet('pully1','');
$pully2=checkPostGet('pully2','');
$vbelt=checkPostGet('vbelt','');
$coupling=checkPostGet('coupling','');
$bearing1=checkPostGet('bearing1','');
$bearing2=checkPostGet('bearing2','');
$bearing3=checkPostGet('bearing3','');
$merkhm=checkPostGet('merkhm','');
$addedit=checkPostGet('addedit','');

switch($proses){
	case'loadNewData':
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where.="";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."') ";
		}else{
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
		}
		if($stasiuncr!=''){
			$where.=" and left(a.kodemesin,6)='".$stasiuncr."' ";
		}
		echo"<table cellspacing='1' border='0' class='sortable'>
				<thead align=center>
					<tr class=rowheader>
						<td width='30px'>No.</td>
						<td width='40px'>". $_SESSION['lang']['unit'] ."</td>
						<td width='50px'>". $_SESSION['lang']['kode'] ."</td>
						<td width='250px'>". $_SESSION['lang']['station'] ."</td>
						<td width='80px'>". $_SESSION['lang']['kode'] ."</td>
						<td width='300px'>". $_SESSION['lang']['mesin']."</td>
						<td width='50px'>Action</td>
					</tr>
				</thead><tbody>";
		$limit=20;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',1);
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$ql2="select count(DISTINCT(a.kodemesin)) as jmlhrow from ".$dbname.".pabrik_machinery a where true ".$where." order by a.kodemesin";
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
        $slvhc="select a.kodeorg,a.kodemesin,b.namaorganisasi as namastasiun,c.namaorganisasi as namamesin from ".$dbname.".pabrik_machinery a
				left join ".$dbname.".organisasi b on b.kodeorganisasi=left(a.kodemesin,6)
				left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodemesin
				where true ".$where." group by a.kodemesin order by a.kodemesin limit ".$offset.",".$limit."";
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$user_online=$_SESSION['standard']['userid'];
		$no=($page*$limit);
		while($rlvhc=mysql_fetch_assoc($qlvhc)){
			$no+=1;
			$bgclr=" title='Click untuk melihat detail..!' style=\"cursor: pointer\" onclick=showpopup('".substr($rlvhc['kodemesin'],0,4)."','".substr($rlvhc['kodemesin'],0,6)."','".$rlvhc['kodemesin']."','',event)";
			echo"
			<tr class=rowcontent>
				<td ".$bgclr." align=center>".$no."</td>
                <td ".$bgclr." align=center>".$rlvhc['kodeorg']."</td>
                <td ".$bgclr." align=center>".substr($rlvhc['kodemesin'],0,6)."</td>
                <td ".$bgclr.">".$rlvhc['namastasiun']."</td>
                <td ".$bgclr." align=center>".$rlvhc['kodemesin']."</td>
                <td ".$bgclr.">".$rlvhc['namamesin']."</td>
                <td align=center>
					<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editData('".$rlvhc['kodeorg']."','".$rlvhc['kodemesin']."');\">&nbsp
					<!-- <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".$rlvhc['kodemesin']."');\">&nbsp -->
					<img src=images/excel.jpg class=resicon title='Detail Excel' onclick=\"showpopup('".substr($rlvhc['kodemesin'],0,4)."','".substr($rlvhc['kodemesin'],0,6)."','".$rlvhc['kodemesin']."','excel',event);\">
				</td>
			</tr>";
		}
		echo"
		<tr class=rowheader>
			<td colspan=7 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
		echo"</tbody></table>";
		break;

	case'getStasiun':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($kodeorg!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION' and induk='".$kodeorg."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}  
		}
		echo $optStation;
		break;

    case'getMesin':
        $optMesin.="";
		if($stasiun==''){
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='STENGINE' and induk like '".$kodeorg."%' and length(kodeorganisasi)=10";
		}else{
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$stasiun."'";
		}
		$nMesin=mysql_query($iMesin) or die (mysql_error($conn));
		while($dMesin=mysql_fetch_assoc($nMesin)){
			if($mesin==$dMesin['kodeorganisasi'])
				{$select="selected=selected";}
			else
				{$select="";}
			$optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">[".$dMesin['kodeorganisasi']."] ".$dMesin['namaorganisasi']."</option>";
		}
        echo $optMesin;
		break;

	case'loadDetailData':
		$str="select a.*,b.namaorganisasi as namamesin from ".$dbname.".pabrik_machinery a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				where a.kodemesin like '".$mesin."%'
				order by a.kodemesin,a.kodesubmesin";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent valign='center'>
					<td ".$drcl." align=left>".$bar->kodemesin."</td>
					<td ".$drcl." align=left style='width:600px;'>".$bar->namamesin."</td>
					<td ".$drcl." align=left>".$bar->namasubmesin."</td>";
			if($bar->statusmesin*100==0){
				echo"<td align=right></td>";
			}else{
				echo"<td align=right>".@number_format($bar->statusmesin*100,0)."%</td>";
			}
			echo"	<td>".$bar->unit1."</td>";
			if($bar->stsunit1*100==0){
				echo"<td align=right></td>";
			}else{
				echo"<td align=right>".@number_format($bar->stsunit1*100,0)."%</td>";
			}
			echo"	<td>".$bar->unit2."</td>";
			if($bar->stsunit2*100==0){
				echo"<td align=right></td>";
			}else{
				echo"<td align=right>".@number_format($bar->stsunit2*100,0)."%</td>";
			}
			echo"	<td>".$bar->merk."</td>";
			echo"	<td>".$bar->model."</td>";
			echo"	<td align=right>".$bar->ratio."</td>";
			echo"	<td align=right>".$bar->rpm."</td>";
			echo"	<td align=right>".$bar->kw."</td>";
			echo"	<td align=right>".$bar->ampere."</td>";
			echo"	<td align=right>".$bar->manufacturedyear."</td>";
			echo"	<td>".$bar->serialnumber."</td>";
			echo"	<td>".$bar->sproket1."</td>";
			echo"	<td>".$bar->sproket2."</td>";
			echo"	<td>".$bar->sproket3."</td>";
			if($bar->stssproket*100==0){
				echo"<td align=right></td>";
			}else{
				echo"<td align=right>".@number_format($bar->stssproket*100,0)."%</td>";
			}
			echo"	<td>".$bar->chain1."</td>";
			echo"	<td>".$bar->chain2."</td>";
			if($bar->stschain*100==0){
				echo"<td align=right></td>";
			}else{
				echo"<td align=right>".@number_format($bar->stschain*100,0)."%</td>";
			}
			echo"	<td>".$bar->pully1."</td>";
			echo"	<td>".$bar->pully2."</td>";
			echo"	<td>".$bar->vbelt."</td>";
			echo"	<td>".$bar->coupling."</td>";
			echo"	<td>".$bar->bearing1."</td>";
			echo"	<td>".$bar->bearing2."</td>";
			echo"	<td>".$bar->bearing3."</td>";
			echo"	<td>".$bar->merkhm."</td>";
			echo"	<td align=center width='4%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodemesin."','".$bar->kodesubmesin."','".($bar->statusmesin*100)."','".$bar->unit1."','".($bar->stsunit1*100)."','".$bar->unit2."','".($bar->stsunit2*100)."','".$bar->merk."','".$bar->model."','".$bar->ratio."','".$bar->rpm."','".$bar->kw."','".$bar->ampere."','".$bar->manufacturedyear."','".$bar->serialnumber."','".$bar->sproket1."','".$bar->sproket2."','".$bar->sproket3."','".($bar->stssproket*100)."','".$bar->chain1."','".$bar->chain2."','".($bar->stschain*100)."','".$bar->pully1."','".$bar->pully2."','".$bar->vbelt."','".$bar->coupling."','".$bar->bearing1."','".$bar->bearing2."','".$bar->bearing3."','".$bar->merkhm."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"delDetail('".$bar->kodeorg."','".$bar->kodemesin."','".$bar->kodesubmesin."','".$bar->namasubmesin."');\">&nbsp
					</td>
				</tr>";
		}
		break;

	case'saveData':
		if($submesin=='1'){
			$namasubmesin='Elektromotor';
		}elseif($submesin=='2'){
			$namasubmesin='Generator';
		}elseif($submesin=='3'){
			$namasubmesin='Door';
		}elseif($submesin=='4'){
			$namasubmesin='GearBox';
		}elseif($submesin=='5'){
			$namasubmesin='Unit';
		}elseif($submesin=='6'){
			$namasubmesin='Auxilary';
		}elseif($submesin=='7'){
			$namasubmesin='HeaterBank';
		}else{
			$namasubmesin='';
		}
		$stbl="select * from ".$dbname.".pabrik_machinery where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and kodesubmesin='".$submesin."'";
		$qtbl=mysql_query($stbl);
		if(mysql_num_rows($qtbl) > 0){
		//if($addedit=='update'){
			$strx="update ".$dbname.".pabrik_machinery set
					kodeorg='".$kodeorg."',kodemesin='".$mesin."',kodesubmesin='".$submesin."',namasubmesin='".$namasubmesin."'
					,statusmesin='".($stsmesin/100)."',unit1='".$unit1."',stsunit1='".($stsunit1/100)."',unit2='".$unit2."',stsunit2='".($stsunit2/100)."'
					,merk='".$merk."',model='".$model."',ratio='".$ratio."',rpm='".$rpm."',kw='".$kw."',ampere='".$ampere."',manufacturedyear='".$tahunbuat."'
					,serialnumber='".$sn."',sproket1='".$sproket1."',sproket2='".$sproket2."',sproket3='".$sproket3."',stssproket='".($stssproket/100)."'
					,chain1='".$chain1."',chain2='".$chain2."',stschain='".($stschain/100)."',pully1='".$pully1."',pully2='".$pully2."',vbelt='".$vbelt."'
					,coupling='".$coupling."',bearing1='".$bearing1."',bearing2='".$bearing2."',bearing3='".$bearing3."'
					,merkhm='".$merkhm."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and kodesubmesin='".$submesin."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_machinery (kodeorg,kodemesin,kodesubmesin,namasubmesin,statusmesin,unit1,stsunit1,unit2,stsunit2,merk,model
					,ratio,rpm,kw,ampere,manufacturedyear,serialnumber,sproket1,sproket2,sproket3,stssproket,chain1,chain2,stschain,pully1,pully2,vbelt,coupling
					,bearing1,bearing2,bearing3,merkhm,lastuser,lastdate)
					values('".$kodeorg."','".$mesin."','".$submesin."','".$namasubmesin."','".($stsmesin/100)."','".$unit1."','".($stsunit1/100)."'
					,'".$unit2."','".($stsunit2/100)."','".$merk."','".$model."','".$ratio."','".$rpm."','".$kw."','".$ampere."','".$tahunbuat."','".$sn."'
					,'".$sproket1."','".$sproket2."','".$sproket3."','".($stssproket/100)."','".$chain1."','".$chain2."','".($stschain/100)."'
					,'".$pully1."','".$pully2."','".$vbelt."','".$coupling."','".$bearing1."','".$bearing2."','".$bearing3."','".$merkhm."'
					,'".$_SESSION['standard']['username']."',now())";
		}
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
		break;

	case'delDetail':
		$strx="delete from ".$dbname.".pabrik_machinery where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and kodesubmesin='".$submesin."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_machinery where kodeorg='".$kodeorg."' and kodemesin='".$mesin."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	default:
		break;
	}
?>
