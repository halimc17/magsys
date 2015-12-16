<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kode=checkPostGet('kode','');
	$jenistraining=checkPostGet('jenistraining','');
	$status=checkPostGet('status','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($kode==''||$jenistraining==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".sdm_5jenistraining where kodetraining='".$kode."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numRows=mysql_num_rows($qry);
			if($numRows>=1){
				echo "Warning: Kode training sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".sdm_5jenistraining (kodetraining,jenistraining,status,updateby) 
				values ('".$kode."','".$jenistraining."','1','".$_SESSION['standard']['userid']."')";
				// echo $strIns;
				if(mysql_query($strIns)){
					getContainer();
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
		break;
			
		case 'edit':
			if($jenistraining==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="update ".$dbname.".sdm_5jenistraining set jenistraining='".$jenistraining."', updateby = '".$_SESSION['standard']['userid']."' where kodetraining='".$kode."'";
			if(mysql_query($str)){
				getContainer();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		break;
		
		case 'delete':
			$str="select * from ".$dbname.".sdm_karyawantraining where jenistraining='".$kode."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numRows=mysql_num_rows($qry);
			if($numRows>=1){
				echo "Warning: Jenis training ini sudah terdaftar/digunakan untuk data karyawan.";
			}else{
				$strDel="delete from ".$dbname.".sdm_5jenistraining where kodetraining='".$kode."'";
				if(mysql_query($strDel)){
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
		break;
		
		case 'updStatus':
			if($status == 1){
				$stat = 0;
			}else{
				$stat = 1;
			}
			$str="update ".$dbname.".sdm_5jenistraining set status = '".$stat."', updateby = '".$_SESSION['standard']['userid']."' where kodetraining='".$kode."'";
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
		
		$str="select * from ".$dbname.".sdm_5jenistraining order by status desc, jenistraining asc";
		$qry=mysql_query($str) or die(mysql_error());
		
		while($res=mysql_fetch_object($qry))
		{
			$no+=1;
			$opt='';
			$bg="class=rowcontent";
			if($res->status==0){
					$opt.="<input type=checkbox id=".$res->kodetraining." title='Click to activate' onclick=\"updateStatus('".$res->kodetraining."','".$res->status."');\">";
					$bg="bgcolor=orange";
			}else{
					$opt.="<input type=checkbox id=".$res->kodetraining." checked  title='Click to deActivate' onclick=\"updateStatus('".$res->kodetraining."','".$res->status."');\">";
			}
			echo"<tr ".$bg.">
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodetraining."</td>
					<td>".$res->jenistraining."</td>";
					if($res->status == 0){
						$stat= "Not Active";
					}else{
						$stat= "Active";
					}
			echo"<td>".$opt." ".$stat."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kodetraining."','".$res->jenistraining."')\"></td>
					<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->kodetraining."')\"></td>
				</tr>";
		}
	}
?>