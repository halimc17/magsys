<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kelompokvhc=checkPostGet('kelompokvhc','');
$jenisvhc=checkPostGet('jenisvhc','');
$kodeorg=checkPostGet('kodeorg','');
$method=checkPostGet('method','');
$kodevhc=str_replace(" ","",checkPostGet('kodevhc',''));
$tahunperolehan=checkPostGet('tahunperolehan','');
$noakun=checkPostGet('noakun','');
$beratkosong=checkPostGet('beratkosong','');
$nomorrangka=checkPostGet('nomorrangka','');
$nomormesin=checkPostGet('nomormesin','');
$detailvhc=checkPostGet('detailvhc','');
$kodebarang=checkPostGet('kodebarang','');
$kepemilikan=checkPostGet('kepemilikan','');
$kodetraksi=checkPostGet('kodetraksi','');
$kodeasset=checkPostGet('kodeasset','NULL');
$tglakhirstnk=tanggalsystem(checkPostGet('tglakhirstnk','00-00-0000'));
$tglakhirkir=tanggalsystem(checkPostGet('tglakhirkir','00-00-0000'));
$tglakhirijinbm=tanggalsystem(checkPostGet('tglakhirijinbm','00-00-0000'));
$tglakhirijinang=tanggalsystem(checkPostGet('tglakhirijinang','00-00-0000'));

if($kodeasset!='NULL') {
	$kodeasset = "'".$kodeasset."'";
}
if($beratkosong=='') $beratkosong=0;
$strx="select 1=1";

switch($method){
    case 'delete':
        $strx="delete from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
		break;
    case 'update':
		$strx="update ".$dbname.".vhc_5master set jenisvhc='".$jenisvhc."',
			kelompokvhc='".$kelompokvhc."', 
			kodeorg='".$kodeorg."', tahunperolehan='".$tahunperolehan."',
			beratkosong='".$beratkosong."', nomorrangka='".$nomorrangka."' ,
			nomormesin='".$nomormesin."',detailvhc='".$detailvhc."',
			kodebarang='".$kodebarang."',kepemilikan=".$kepemilikan.",
			kodetraksi='".$kodetraksi."', tglakhirstnk='".$tglakhirstnk."',
			tglakhirkir='".$tglakhirkir."',tglakhirijinbm='".$tglakhirijinbm."',
			tglakhirijinang='".$tglakhirijinang."',kodeasset=".$kodeasset."
		where kodevhc='".$kodevhc."'";
		break;	
    case 'insert':
		$strx="insert into ".$dbname.".vhc_5master(
			kodevhc,kelompokvhc,kodeorg,jenisvhc,
			tahunperolehan,beratkosong,nomorrangka,
			nomormesin,detailvhc,kodebarang,kepemilikan,kodetraksi,
			tglakhirstnk,tglakhirkir,tglakhirijinbm,tglakhirijinang,kodeasset)
		values('".$kodevhc."','".$kelompokvhc."',
			'".$kodeorg."','".$jenisvhc."',".$tahunperolehan.",
			".$beratkosong.",'".$nomorrangka."','".$nomormesin."',
			'".$detailvhc."','".$kodebarang."',".$kepemilikan.",
			'".$kodetraksi."','".$tglakhirstnk."','".$tglakhirkir."',
			'".$tglakhirijinbm."','".$tglakhirijinang."',".$kodeasset.")";
		break;
    case'deactive':
        if($_POST['status']==1){
            $_POST['status']=0;
        }else{
            $_POST['status']=1;
        }
          $strx="update ".$dbname.".vhc_5master set status='".$_POST['status']."' 
                 where kodevhc='".$_POST['kodevhc']."'";
        break;
    default:
		break;
}
if(!mysql_query($strx)) {
	exit(" Gagal,".addslashes(mysql_error($conn)));
}	

$where='1=1';
if($kodeorg!='')
   $where.=" and kodeorg='".$kodeorg."' ";
if($kelompokvhc!='')
   $where.=" and kelompokvhc='".$kelompokvhc."' ";   
if($jenisvhc!='')
   $where.=" and jenisvhc='".$jenisvhc."' ";
   
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $str="select * from ".$dbname.".vhc_5master where ".$where." 
		order by status desc,kodeorg,kodevhc asc";
} else{
    $str="select * from ".$dbname.".vhc_5master where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%' and ".$where." 
		order by status desc,kodeorg,kodevhc asc";
}

$res=mysql_query($str);
$no=0;
$listAsset = array();
while($bar1=mysql_fetch_object($res))
{
	$no+=1;
	$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar1->kodebarang."'";
	$res1=mysql_query($str);
	$namabarang='';
	while($bar=mysql_fetch_object($res1)) {
		$namabarang=$bar->namabarang;
	}
	
	if($bar1->kepemilikan==1) {
	  $dptk=$_SESSION['lang']['miliksendiri'];	
	} else {
		$dptk=$_SESSION['lang']['sewa'];
	}
	$sttd="";
	$sttd="Deactivate";
	$bgcrcolor="class=rowcontent";
	if($bar1->status==0){
		$bgcrcolor="bgcolor=orange";
		$sttd="";
		$sttd="Actived";
	}
	$clidt=" style='cursor:pointer' title='".$sttd." ".$bar1->kodevhc."' onclick=deAktif('".$bar1->kodevhc."','".$bar1->status."')";
	echo"<tr ".$bgcrcolor.">
		<td ".$clidt." >".$no."</td>
		<td ".$clidt." >".$bar1->kodeorg."</td>
		<td ".$clidt." >".$bar1->kelompokvhc."</td>				 
		<td ".$clidt." >".$bar1->jenisvhc."</td>			 		
		<td ".$clidt." >".$bar1->kodevhc."</td>
		<td ".$clidt." >".$namabarang."</td>
		<td ".$clidt." >".$bar1->tahunperolehan."</td>
		<input type=hidden value=".$bar1->beratkosong.">
		<input type=hidden value=".$bar1->nomorrangka.">
		<td ".$clidt." >".$bar1->nomormesin."</td> 
		<td ".$clidt." >".$bar1->detailvhc."</td> 	
		<td ".$clidt." >".$dptk."</td> 
		<td ".$clidt." >".$bar1->kodetraksi."</td>
		<td>
			<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillMasterField('".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."','".$bar1->beratkosong."',
				'".$bar1->nomorrangka."','".$bar1->nomormesin."','".$bar1->tahunperolehan."','".$bar1->kodebarang."','".$bar1->kepemilikan."','".$bar1->kodetraksi."','".tanggalnormal($bar1->tglakhirstnk)."','".tanggalnormal($bar1->tglakhirkir)."',
				'".tanggalnormal($bar1->tglakhirijinbm)."','".tanggalnormal($bar1->tglakhirijinang)."','".$bar1->kodeasset."','".$bar1->detailvhc."');\">
			<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"deleteMasterVhc('".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."');\">
		</td></tr>";
	
	if($bar1->kodeasset!=str_replace("'",'',$kodeasset)) {
		$listAsset[] = $bar1->kodeasset;
	}
}

echo '#####';
// Get Kode Asset
if(!empty($kodeorg)) {
	$whereAsset = "kodeorg='".$kodeorg."'";
	if(!empty($listAsset)) {
		$whereAsset .= " and kodeasset not in ('".implode("','",$listAsset)."')";
	}
	$optAsset = makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',
						   $whereAsset,'0',true);
	echo json_encode($optAsset);
}