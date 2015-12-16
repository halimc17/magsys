<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kodekelompok=checkPostGet('kodekelompok','');
$deskripsi=checkPostGet('deskripsi','');
$method=checkPostGet('method','');

switch($method){
	case 'loadData':  
		listData();
	break;
	
	case 'insert':
		if($kodekelompok==''){
			echo "Gagal : Kode kelompok harus diisi.";
			exit();
		}
		$str="select * from ".$dbname.".sdm_5kldiagnosa where kodekelompok='".$kodekelompok."'";
		$qry=mysql_query($str) or die(mysql_error());
		$numRows=mysql_num_rows($qry);
		if($numRows>=1){
			echo "Error: Kode kelompok sudah pernah terdaftar sebelumnya.";
		}else{
			$strIns="insert into ".$dbname.".sdm_5kldiagnosa (kodekelompok,deskripsi) 
			values ('".$kodekelompok."','".$deskripsi."')";
			if(mysql_query($strIns)){
				listData();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		}
	break;
	
	case 'update':
		$str="update ".$dbname.".sdm_5kldiagnosa set deskripsi='".$deskripsi."' where kodekelompok='".$kodekelompok."'";
		if(mysql_query($str)){
			listData();
		}else{
			echo "DB Error : ".mysql_error($conn);
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".sdm_5kldiagnosa where kodekelompok='".$kodekelompok."'";
		if(mysql_query($str)){
		}else{
			echo "DB Error : ".mysql_error($conn);
		}
	break;
	
	default:
	break;
}

function listData(){
	global $dbname;
	global $conn;
	
	$str="select * from ".$dbname.".sdm_5kldiagnosa order by deskripsi";
	$qry=mysql_query($str) or die(mysql_error($conn));
	
	if(mysql_num_rows($qry)<=0){
		echo"<tr class=rowcontent><td colspan='4' style='text-align:center;'>".$_SESSION['lang']['datanotfound']."</td></tr>";
	}else{
		while($row=mysql_fetch_assoc($qry)){
			echo"<tr class=rowcontent>
				<td>".$row['kodekelompok']."</td>
				<td>".$row['deskripsi']."</td>
				<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$row['kodekelompok']."','".$row['deskripsi']."')\"></td>
				<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteData('".$row['kodekelompok']."')\"></td>
			<tr>";
		}
	}
}

?>