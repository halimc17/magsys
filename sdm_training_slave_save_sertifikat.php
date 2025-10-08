<?php
require_once('master_validation.php');
require_once('config/connection.php');

$nomor=$_POST['nomorx'];
$strk="select a.karyawanid,b.nik,b.namakaryawan,a.scansertifikat from ".$dbname.".sdm_karyawantraining a
		left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
		where a.nomor=".$nomor."";
$karyawanid='';
$nik='';
$namakaryawan='';
$scansertifikatlama='';
$resk=mysql_query($strk);
while($bark=mysql_fetch_object($resk)){
	$karyawanid		=$bark->karyawanid;
	$nik			=$bark->nik;
	$namakaryawan	=$bark->namakaryawan;
	$scansertifikatlama=$bark->scansertifikat;
}
if($karyawanid==''){
	exit("Error employees name...!");
}
$path='scansertifikatkaryawan';
//exit('Warning: id='.$karyawanid.' nik='.$nik.' Nama='.$namakaryawan.' scansert='.$scansertifikatlama);
//exit('Warning: '.$_FILES['photo']['size']);
if(($_FILES['photo']['size']) <= $_POST['MAX_FILE_SIZE'] and !empty($_FILES['photo']['tmp_name'])){
	if(is_dir($path)){
		writeFile($path,$nomor,$namakaryawan,$scansertifikatlama,$conn,$dbname);
		exit('Done.');
	}else{
		if(mkdir($path, 0755, true)){
			writeFile($path,$nomor,$namakaryawan,$scansertifikatlama,$conn,$dbname);
			exit('Done.');
		}else{
			exit("Can't create folder for uploaded file...!");
		}
	}
}else{
	exit("File size is ".filesize($_FILES['photo']['tmp_name']).", greater then allowed...!");
} 
  
function writeFile($path,$nomor,$namakaryawan,$scansertifikatlama,$conn,$dbname){ 
	$dir=$path;
	$countError = 0;
	$ext=split('[.]', basename( $_FILES['photo']['name']));
	$ext=$ext[count($ext)-1];
	$ext=strtolower($ext);
	if($ext=='zip' or $ext=='rar' or $ext=='gz' or $ext=='tgz' or $ext=='7z' or $ext=='tar' or $ext=='png' or $ext=='jpg' or $ext=='jpeg' or $ext=='pdf'){
		if($scansertifikatlama!=''){
			// Delete File Lama
			if(file_exists($scansertifikatlama)){
				unlink($scansertifikatlama);
			}
		}
		$path = $dir."/".$namakaryawan."_".basename( $_FILES['photo']['name']);
//exit('Warning: '.$scansertifikatlama);
		try{
			if(move_uploaded_file($_FILES['photo']['tmp_name'], $path)){
				$strs="update ".$dbname.".sdm_karyawantraining set scansertifikat='".$path."' where nomor=".$nomor."";
				if(mysql_query($strs)){
				}else{
					echo "DB Error : ".mysql_error($conn).' '.$strs;
				}
				//chmod($path, 0775);					
			}
		}
		catch(Exception $e){
			echo "<script>alert(\"Error Writing File".addslashes($e->getMessage())."\");</script>";
		}
	}else{
		echo "<script>alert('Filetype not support:".$ext." or too large');</script>";		 	
	}
}
?>
