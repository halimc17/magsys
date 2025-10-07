<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$lokasibpjs=checkPostGet('lokasibpjs','');
	$jenisbpjs=checkPostGet('jenisbpjs','');
	$bebankaryawan=checkPostGet('bebankaryawan','0');
	$bebanperusahaan=checkPostGet('bebanperusahaan','0');
	$maxgaji=checkPostGet('maxgaji','0');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
			
		case 'update':
			$str="update ".$dbname.".sdm_5bpjs set bebankaryawan='".$bebankaryawan."', bebanperusahaan='".$bebanperusahaan."', maxgaji='".$maxgaji."' where lokasibpjs='".$lokasibpjs."' and jenisbpjs='".$jenisbpjs."'";
			// print_r($str);
			if(mysql_query($str)){
				getContainer();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer(){
		global $conn;
		global $dbname;
		
		$str="select * from ".$dbname.".sdm_5bpjs";
		$qry=mysql_query($str) or die(mysql_error());
		
		while($res=mysql_fetch_object($qry))
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->lokasibpjs."</td>
					<td>".$res->jenisbpjs."</td>
					<td style='text-align:right;'>".$res->bebankaryawan."</td>
					<td style='text-align:right;'>".$res->bebanperusahaan."</td>
					<td style='text-align:right;'>".number_format($res->maxgaji,2)."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->lokasibpjs."','".$res->jenisbpjs."','".$res->bebankaryawan."','".$res->bebanperusahaan."','".$res->maxgaji."')\"></td>
				</tr>";
		}
	}
?>