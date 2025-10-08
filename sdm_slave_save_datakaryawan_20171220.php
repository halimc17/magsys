<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$nik		=$_POST['nik'];
$namakaryawan	=$_POST['namakaryawan'];
$tempatlahir	=$_POST['tempatlahir'];
$tanggallahir	=tanggalsystem($_POST['tanggallahir']);
$noktp		=$_POST['noktp'];	
$nopassport	=$_POST['nopassport'];
$npwp		=$_POST['npwp'];
$bpjs		=$_POST['bpjs'];
$kodepos	=$_POST['kodepos'];
$alamataktif	=$_POST['alamataktif'];
$kota		=$_POST['kota'];
$noteleponrumah	=$_POST['noteleponrumah'];
$nohp		=$_POST['nohp'];
$norekeningbank	=$_POST['norekeningbank'];
$namabank	=$_POST['namabank'];
$alokasi	=$_POST['alokasi'];
$jms            =$_POST['jms'];

$tanggalmasuk	=tanggalsystem($_POST['tanggalmasuk']);
if($_POST['tanggalpengangkatan']=='')
    $_POST['tanggalpengangkatan']='00-00-0000';
$tanggalpengangkatan=tanggalsystem($_POST['tanggalpengangkatan']);
if($_POST['tanggalkeluar']=='')
    $_POST['tanggalkeluar']='00-00-0000';
$tanggalkeluar	=tanggalsystem($_POST['tanggalkeluar']);
$jumlahanak		=$_POST['jumlahanak'];
if($jumlahanak=='')
  $jumlahanak=0;
$jumlahtanggungan=$_POST['jumlahtanggungan'];
if($jumlahtanggungan=='')
   $jumlahtanggungan=0;
if($_POST['tanggalmenikah']=='')
    $_POST['tanggalmenikah']='00-00-0000';
$tanggalmenikah     =tanggalsystem($_POST['tanggalmenikah']);
$notelepondarurat   =$_POST['notelepondarurat'];
$email              =$_POST['email'];
$jeniskelamin       =$_POST['jeniskelamin'];
$agama              =$_POST['agama'];
$bagian             =$_POST['bagian'];
$kodejabatan        =$_POST['kodejabatan'];
$kodegolongan       =$_POST['kodegolongan'];
$lokasitugas        =$_POST['lokasitugas'];
$kodeorganisasi     =$_POST['kodeorganisasi'];
$tipekaryawan       =$_POST['tipekaryawan'];
$warganegara        =$_POST['warganegara'];
$lokasipenerimaan   =$_POST['lokasipenerimaan'];
$statuspajak        =$_POST['statuspajak'];
$provinsi           =$_POST['provinsi'];
$sistemgaji         =$_POST['sistemgaji'];
$golongandarah      =$_POST['golongandarah'];
$statusperkawinan   =$_POST['statusperkawinan'];
$levelpendidikan    =$_POST['levelpendidikan'];	
$method             =$_POST['method'];
$karyawanid         =$_POST['karyawanid'];
$subbagian          =$_POST['subbagian'];
$catu               =$_POST['catu'];
if($subbagian=='0')
{
    $subbagian='';
}
$param = $_POST;
$param['tanggallahir'] = tanggalsystemn($_POST['tanggallahir']);
$param['tanggalmasuk'] = tanggalsystemn($_POST['tanggalmasuk']);
$param['tanggalpengangkatan'] = tanggalsystemn($_POST['tanggalpengangkatan']);
$param['tanggalkeluar'] = tanggalsystemn($_POST['tanggalkeluar']);
$param['tanggalmenikah'] = tanggalsystemn($_POST['tanggalmenikah']);

switch($method){
	case 'delete':
		$strx="delete from ".$dbname.".datakaryawan where karyawanid=".$karyawanid;
	break;
	case 'update':
		$qData = selectQuery($dbname,'datakaryawan','*',"karyawanid='".$karyawanid."'");
		$resData = fetchData($qData);
		$oldData = $resData[0];
		$strx="update ".$dbname.".datakaryawan set 
			`namakaryawan`	='".$namakaryawan."',
			`tempatlahir`	='".$tempatlahir."',
			`tanggallahir`	=".$tanggallahir.",
			`warganegara`            ='".$warganegara."',
			`jeniskelamin`	='".$jeniskelamin."',
			`statusperkawinan`       ='".$statusperkawinan."',
			`tanggalmenikah`	=".$tanggalmenikah.",
			`agama`			='".$agama."',
			`golongandarah`	='".$golongandarah."',
			`levelpendidikan`        =".$levelpendidikan.",
			`alamataktif`	='".$alamataktif."',
			`provinsi`		='".$provinsi."',
			`kota`		='".$kota."',
			`kodepos`		='".$kodepos."',
			`noteleponrumah`         ='".$noteleponrumah."',
			`nohp`		='".$nohp."',
			`norekeningbank`         ='".$norekeningbank."',
			`namabank`		='".$namabank."',
			`sistemgaji`		='".$sistemgaji."',
			`no_keluarga`	='".$nopassport."',
			`noktp`			='".$noktp."',
			`notelepondarurat`   ='".$notelepondarurat."',
			`tanggalmasuk`           =".$tanggalmasuk.",
			`tanggalpengangkatan`    =".$tanggalpengangkatan.",
			`tanggalkeluar`	=".$tanggalkeluar.",
			`tipekaryawan`           =".$tipekaryawan.",
			`jumlahanak`		=".$jumlahanak.",
			`jumlahtanggungan`       =".$jumlahtanggungan.",
			`statuspajak`	='".$statuspajak."',
			`npwp`			='".$npwp."',
			`bpjs`			='".$bpjs."',
			`lokasipenerimaan`   ='".$lokasipenerimaan."',
			`kodeorganisasi`	='".$kodeorganisasi."',
			`bagian`			='".$bagian."',
			`kodejabatan`	=".$kodejabatan.",
			`kodegolongan`	='".$kodegolongan."',
			`lokasitugas`            ='".$lokasitugas."',
			`email`		='".$email."',
			`alokasi`		=".$alokasi.",
			`subbagian`		='".$subbagian."',
			`jms`                ='".$jms."' , 
			`kodecatu`               ='".$catu."', 
			`statpremi`          ='".$_POST['statPremi']."',
			`updateby`	= '".$_SESSION['standard']['userid']."'
			where karyawanid=".$karyawanid;
		logData($oldData,$param);
		break;
	
	case 'insert':
		//Generate NIK Karyawan
		$sReg="select t1.kodepenerimaankaryawan, t1.regional from ".$dbname.".bgt_regional t1
				left join ".$dbname.".bgt_regional_assignment t2
				on t1.regional = t2.regional
				where t2.kodeunit = '".$lokasitugas."' limit 1";
		$qReg=mysql_query($sReg) or die(mysql_error($conn));
		while($bReg=mysql_fetch_object($qReg)){
			$regionalId=$bReg->kodepenerimaankaryawan;
			$resRegional=$bReg->regional;
		}
		
		$time = strtotime($tanggalmasuk);
		$sKar="select max(substring(nik,2,3)) as noUrut from ".$dbname.".datakaryawan where lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment"
                        . " where regional='".$resRegional."') and MONTH(tanggalmasuk) = '".date('m',$time)."' and YEAR(tanggalmasuk)='".date('Y',$time)."'";
		$qKar = mysql_query($sKar) or die(mysql_error());
		$rKar = mysql_fetch_assoc($qKar);
		
		$genNik=$regionalId."".addZero(($rKar['noUrut'])+1,3)."".date('m',$time)."".date('y',$time);
				
		$strx="insert into ".$dbname.".datakaryawan(
		  `nik`,`namakaryawan`,
		  `tempatlahir`,`tanggallahir`,
		  `warganegara`,`jeniskelamin`,
		  `statusperkawinan`,`tanggalmenikah`,
		  `agama`,`golongandarah`,
		  `levelpendidikan`,`alamataktif`,
		  `provinsi`,`kota`,`kodepos`,
		  `noteleponrumah`,`nohp`,
		  `norekeningbank`,`namabank`,
		  `sistemgaji`,`no_keluarga`,
		  `noktp`,`notelepondarurat`,
		  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
		  `tipekaryawan`,`jumlahanak`,
		  `jumlahtanggungan`,`statuspajak`,
		  `npwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
		  `bagian`,`kodejabatan`,`kodegolongan`,
		  `lokasitugas`,`email`,`alokasi`,`subbagian`,`jms`,kodecatu,statpremi,updateby)
		values('".$genNik."','".$namakaryawan."',
		  '".$tempatlahir."',".$tanggallahir.",
		  '".$warganegara."','".$jeniskelamin."',
		  '".$statusperkawinan."',".$tanggalmenikah.",
		  '".$agama."','".$golongandarah."',
		  ".$levelpendidikan.",'".$alamataktif."',
		  '".$provinsi."','".$kota."','".$kodepos."',
		  '".$noteleponrumah."','".$nohp."',
		  '".$norekeningbank."','".$namabank."',
		  '".$sistemgaji."','".$nopassport."',
		  '".$noktp."','".$notelepondarurat."',
		  ".$tanggalmasuk.",".$tanggalpengangkatan.",".$tanggalkeluar.",
		  ".$tipekaryawan.",".$jumlahanak.",
		  ".$jumlahtanggungan.",'".$statuspajak."',
		  '".$npwp."','".$bpjs."','".$lokasipenerimaan."','".$kodeorganisasi."',
		  '".$bagian."',".$kodejabatan.",'".$kodegolongan."',
		  '".$lokasitugas."','".$email."',".$alokasi.",
		  '".$subbagian."','".$jms."','".$catu."','".$_POST['statPremi']."','".$_SESSION['standard']['userid']."')";	   
	break;
	default:
	  $strx="select 1=1";
	break;	
}
if(mysql_query($strx))
{
   //whenever not deleting, return value as below to javascript
	if($method!='delete')
	{
		$karid='';
		$nama='';
		$str="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where
			  namakaryawan='".$namakaryawan."' and tanggallahir='".$tanggallahir."'";
		$res=mysql_query($str);
		//echo $str;
		while($bar=mysql_fetch_object($res))
		{
			$karid=$bar->karyawanid;
			$nama=$bar->namakaryawan;
			$nik=$bar->nik;
		}
		//return XML format
		echo"<?xml version='1.0' ?>
			 <karyawan>
			 <karyawanid>".$karid."</karyawanid>
			 <namakaryawan>".$nama."</namakaryawan>
			 <nik>".$nik."</nik>
			 </karyawan>";
	}
} else {
	echo " Gagal:".addslashes(mysql_error($conn)).$strx;
}

/**
 * Log History
 */
function logData($oldData,$newData) {
	global $karyawanid;
	global $dbname;
	
	// Stat Premi
	$newData['statpremi'] = $newData['statPremi'];
	unset($newData['statPremi']);
	
	// Cek Data
	$dataChange = array();
	foreach($oldData as $key=>$row) {
		if(isset($newData[$key])) {
			if($row != $newData[$key]) {
				$dataChange[$key] = array(
					'old' => $row,
					'new' => $newData[$key]
				);
			}
		}
	}
	
	if(!empty($dataChange)) {
		$dataHist = array(
			'updatetime' => date('Y-m-d H:i:s'),
			'updateby' => $_SESSION['standard']['userid'],
			'karyawanid' => $karyawanid,
			'data' => json_encode($dataChange)
		);
		$qHist = insertQuery($dbname,'hist_datakaryawan',$dataHist);
		mysql_query($qHist);
	}
}