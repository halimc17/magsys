<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kd_org=checkPostGet('kd_org','');
	$kd_denda=checkPostGet('kd_denda','');
	$jenisdenda=checkPostGet('jenisdenda','');
	$ketdenda=checkPostGet('ketdenda','');
	$nilaidenda=checkPostGet('nilaidenda','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($kd_org==''||$kd_denda==''||$nilaidenda==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".kebun_5dendapanen where kodeorg='".$kd_org."' and kodedenda='".$kd_denda."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numRows=mysql_num_rows($qry);
			if($numRows>=1){
				echo "Error: Kebun atau Kode denda sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5dendapanen (kodeorg,kodedenda,deskripsi,jenisdenda,denda) 
				values ('".$kd_org."','".$kd_denda."','".$ketdenda."','".$jenisdenda."','".$nilaidenda."')";
				// echo $strIns;
				if(mysql_query($strIns)){
					getContainer();
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
		break;
			
		case 'edit':
			if($kd_org==''||$kd_denda==''||$nilaidenda==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="update ".$dbname.".kebun_5dendapanen set jenisdenda='".$jenisdenda."', denda='".$nilaidenda."', deskripsi='".$ketdenda."' where kodeorg='".$kd_org."' and kodedenda='".$kd_denda."'";
			if(mysql_query($str)){
				getContainer();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".kebun_5dendapanen where kodeorg='".$kd_org."' and kodedenda='".$kd_denda."'";
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
		
		$str="select t1.*, t2.deskripsi as deskripsikodedenda from ".$dbname.".kebun_5dendapanen t1
			left join ".$dbname.".kebun_5kodedendapanen t2
			on t1.kodedenda = t2.kodedenda";
		$qry=mysql_query($str) or die(mysql_error());
		
		while($res=mysql_fetch_object($qry))
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodeorg."</td>
					<td>".$res->kodedenda." - ".$res->deskripsikodedenda."</td>
					<td>".$res->jenisdenda."</td>
					<td style='text-align:right;'>".$res->denda."</td>
					<td>".$res->deskripsi."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kodeorg."','".$res->kodedenda."','".$res->jenisdenda."','".$res->denda."','".$res->deskripsi."')\"></td>
					<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->kodeorg."','".$res->kodedenda."')\"></td>
				</tr>";
		}
	}
?>