<?
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	$param=$_POST;
	$where="";
	if($param['kodeorg']!=''){
		$where="and a.unit='".$param['kodeorg']."'";
	}
	if($param['kode']!=''){
		$where="and a.kode='".$param['kode']."'";
	}
	if($param['periode']!=''){
		$where="and a.tanggal like '".$param['periode']."%'";
	}
	$str1="select a.*,b.namakaryawan from ".$dbname.".rencana_gis_file a
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
			where a.karyawanid='".$_SESSION['standard']['userid']."' ".$where."
			order by a.lastupdate desc";
	//exit('Warning: '.$str1);
	$res1=mysql_query($str1);
    $no=0;
    while($bar1=mysql_fetch_object($res1)){
		$no+=1;
		echo"<tr class=rowcontent>
				<td>".$no."</td>
				<td>".$bar1->unit."</td>
				<td>".$bar1->kode."</td>
				<td>".tanggalnormal($bar1->tanggal)."</td>
				<td>".$bar1->namakaryawan."</td>
				<td>".$bar1->lastupdate."</td>
				<td>".$bar1->keterangan."</td>
				<td>".$bar1->namafile."</td>
				<td align=right>".$bar1->ukuran."</td>
				<td>".$bar1->namakaryawan."</td>
				<td>";
		if($bar1->karyawanid==$_SESSION['standard']['userid']){
			echo"<img class=resicon src=images/skyblue/delete.png   title='Edit' onclick=\"delFile('".$bar1->unit."','".$bar1->kode."','".$bar1->namafile."');\"> &nbsp  &nbsp  &nbsp"; 
		}
		echo "<img class=resicon src=images/skyblue/zoom.png   title='Open' onclick=\"download('".$bar1->namafile."');\"></td></tr>";
	}
?>
