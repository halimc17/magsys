<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$lokasibaru=$_POST['lokasibaru'];
if($_SESSION['empl']['lokasitugas']==$lokasibaru) exit("Warning: Lokasi Tugas sama dengan lokasi saat ini");
$qPt = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$lokasibaru."'";
$resPt = fetchData($qPt);
$pt=$resPt[0]['induk'];
	$data = array(
		'lokasitugas' => array(
			'old' => $_SESSION['empl']['lokasitugas'],
			'new' => $lokasibaru
		),
		'kodeorganisasi' => array(
			'old' => $_SESSION['org']['kodeorganisasi'],
			'new' => $pt
		)
	);
	
	$strInsHis="insert into ".$dbname.".hist_datakaryawan(updatetime,updateby,karyawanid,data) values ('".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".json_encode($data)."')";
	// echo $strInsHis;exit('error');
	mysql_query($strInsHis);
	
	$str="update ".$dbname.".datakaryawan set kodeorganisasi='".$pt."',
	      lokasitugas='".$lokasibaru."', updateby=".$_SESSION['standard']['userid']."
	       where karyawanid=".$_SESSION['standard']['userid'];
	if(mysql_query($str))
	{
		echo "Updated";
	}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));}
?>
