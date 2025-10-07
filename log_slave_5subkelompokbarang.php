<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kdKlBarang=checkPostGet('kdKlBarang','');
	$kdSubKl=checkPostGet('kdSubKl','');
	$namaSubKl=checkPostGet('namaSubKl','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'getKodeSub':
			$str="select * from ".$dbname.".log_5subklbarang where kelompok like '%".$kdKlBarang."%' order by kode desc limit 1";
			$res=mysql_query($str);
			while($bar=mysql_fetch_object($res)){
				$noTrkhr = $bar->kode;
			}
			if($noTrkhr==''){
				$noTrkhr=$kdKlBarang.'01';
			}else{
				$noTrkhr+=1;
			}			
			echo $noTrkhr;
		break;
		
		case 'insert':
			if($kdSubKl==''||$namaSubKl==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".log_5subklbarang where kode='".$kdSubKl."'";
			$qry=mysql_query($str) or die(mysql_error());
			$numRows=mysql_num_rows($qry);
			if($numRows>=1){
				echo "Error: Kode sub kelompok barangn sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".log_5subklbarang (kode,namasubkelompok,kelompok) 
				values ('".$kdSubKl."','".$namaSubKl."','".$kdKlBarang."')";
				// echo $strIns;
				if(mysql_query($strIns)){
					getContainer();
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
		break;
			
		case 'edit':
			if($namaSubKl==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="update ".$dbname.".log_5subklbarang set namasubkelompok='".$namaSubKl."' where kode='".$kdSubKl."'";
			if(mysql_query($str)){
				getContainer();
			}else{
				echo "DB Error : ".mysql_error($conn);
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".log_5subklbarang where kode='".$kdSubKl."'";
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
		
		$str="select * from ".$dbname.".log_5subklbarang";
		$qry=mysql_query($str) or die(mysql_error());
		
		if(mysql_num_rows($qry)<=0){
			echo "<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($res=mysql_fetch_object($qry))
			{
				$no+=1;
				echo"<tr class=rowcontent>
						<td style='text-align:right;'>".$no."</td>
						<td>".$res->kelompok."</td>
						<td>".$res->kode."</td>
						<td>".$res->namasubkelompok."</td>
						<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kelompok."','".$res->kode."','".$res->namasubkelompok."')\"></td>
						<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->kode."')\"></td>
					</tr>";
			}
		}
	}
?>