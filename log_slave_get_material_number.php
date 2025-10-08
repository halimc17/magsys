<?php
require_once('master_validation.php');
require_once('config/connection.php');
$num=0;
$mayor=$_POST['mayor'];
$subkelompokbarang=$_POST['subkelompokbarang'];
$method=$_POST['method'];

switch($method){
	case 'getSubKlBarang':
		$str="select kode,namasubkelompok from ".$dbname.".log_5subklbarang 
			where kelompok = '".$mayor."' order by kode asc";
		$op="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$op.="<option value='".$bar->kode."'>".$bar->namasubkelompok."</option>";
		}
		echo $op;
		exit();
	break;
	
	case 'getKodeMaterial':
		$str="select * from ".$dbname.".log_5masterbarang where
			  kodebarang like '%".$subkelompokbarang."%' order by kodebarang desc limit 1";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$num=$bar->kodebarang;
		}
		if($subkelompokbarang==''){
			$num='';
		}else{
			if($num==''){
				$num=$subkelompokbarang.'001';
			}else{
				$num+=1;
			}
		}
			
		// $num=intval($num)+1;
		// //echo $num;

		// switch($num)
		// {
			// case $num<10:
				// $n=$mayor.'0000'.$num;
				// break;	
			// case $num<100:
				// $n=$mayor.'000'.$num;
				// break;	
			// case $num<1000:
				// $n=$mayor.'00'.$num;
				// break;		
			// case $num<10000:
				// $n=$mayor.'0'.$num;	
				// break;
			// default:
				// $n=$num;				
		// }
		echo $num;
	break;
	
	default:
	break;
}

?>
