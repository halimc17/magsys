<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kode=checkPostGet('kode','');
	$deskripsi=checkPostGet('deskripsi','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loadData':
			getContainer();
		break;
		
		case 'insert':
			if($kode==''||$deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".kebun_5kodedendapanen where kodedenda='".$kode."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numRows=mysql_num_rows($qry);
			if($numRows>=1){
				echo "Error: Kode denda sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5kodedendapanen (kodedenda,deskripsi) 
				values ('".$kode."','".$deskripsi."')";
				// echo $strIns;
				if(mysql_query($strIns)){
					getContainer();
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
		break;
			
		case 'edit':
			if($deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="update ".$dbname.".kebun_5kodedendapanen set deskripsi='".$deskripsi."' where kodedenda='".$kode."'";
			if(mysql_query($str)){
				getContainer();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".kebun_5kodedendapanen where kodedenda='".$kode."'";
			if(mysql_query($str)){
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
		
		$str="select * from ".$dbname.".kebun_5kodedendapanen order by kodedenda ASC";
		$qry=mysql_query($str) or die(mysql_error());
		
		while($res=mysql_fetch_object($qry))
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodedenda."</td>
					<td>".$res->deskripsi."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kodedenda."','".$res->deskripsi."')\"></td>
					<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->kodedenda."')\"></td>
				</tr>";
		}
	}
?>