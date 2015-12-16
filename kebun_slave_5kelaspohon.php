<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$kelaspohon=checkPostGet('kelaspohon','');
$basishari=checkPostGet('basishari','');
$basisbulan=checkPostGet('basisbulan','');
$namakelas=checkPostGet('namakelas','');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		if($kelaspohon==''||$namakelas=='')
		{
			echo"warning : Field tidak boleh kosong";
			exit();
		}
		else
		{
			$str="select * from ".$dbname.".kebun_5kelaspohon where kelas='".$kelaspohon."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numrows=mysql_num_rows($qry);
			
			if($numrows>0){
				echo "warning : Gagal. Kelas pohon sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5kelaspohon (kelas,basishari,basisbulan,nama) values ('".$kelaspohon."','".$basishari."','".$basisbulan."','".$namakelas."')";
				if(mysql_query($strIns)){
					loadlist();
				}else{
					echo"Gagal:Db Error".$strIns."__".mysql_error();
				}
			}
		}
	break;
	
	case'edit':
		$strEdt="update ".$dbname.".kebun_5kelaspohon set nama='".$namakelas."',basishari='".$basishari."',basisbulan='".$basisbulan."' where kelas='".$kelaspohon."'";
		if(mysql_query($strEdt)){
			loadlist();
		}else{
			echo"Gagal:Db Error".$strEdt."__".mysql_error();
		}		
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5kelaspohon where kelas='".$kelaspohon."'";
		if(mysql_query($str)){
			loadlist();
		}else{
			echo"Gagal:Db Error".$str."__".mysql_error();
		}
	break;
	
	default:
	break;
}

function loadlist(){
	global $conn;
	global $dbname;
	$strList="select * from ".$dbname.".kebun_5kelaspohon";
	$qrtList=mysql_query($strList) or die(mysql_error($conn));
	$nourut=0;
	while($rowList=mysql_fetch_object($qrtList)){
		$nourut+=1;
		echo"<tr class='rowcontent'>
				<td>".$rowList->kelas."</td>
				<td style='text-align:right;'>".$rowList->basishari."</td>
				<td style='text-align:right;'>".$rowList->basisbulan."</td>
				<td>".$rowList->nama."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList->kelas."','".$rowList->basishari."','".$rowList->basisbulan."','".$rowList->nama."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$rowList->kelas."')\"></td>
			</tr>";
	}
}
?>