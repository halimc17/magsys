<?php
require_once('master_validation.php');
require_once('config/connection.php');

$kodebarang=$_POST['kodebarangx'];
$spec=$_POST['spec'];
$path='photobarang';

if(is_dir($path))
{
	writeFile($path,$conn,$dbname);
}else{
	if(mkdir($path, 0777))
	{
		writeFile($path,$conn,$dbname);
	}
	else
	{
		echo " Gagal, Can't create folder for uploaded file";
		exit(0);
	}
} 
  
function writeFile($path,$conn,$dbname){ 
	$lokasi=Array();
	$dir=$path;
	$countError = 0;
	for($x=0;$x<count($_FILES['file']['name']);$x++){
		$extension = ".".end(explode('.', $_FILES['file']['name'][$x]));
		$path = $dir."/".basename($_FILES['file']['name'][$x]);
		
		if($path!='photobarang/'){
			$lokasi[$x]=$path;
		}else{
		    $lokasi[$x]='';
		}

		$size=$_FILES['file']['size'][$x];
		$max=100000;
		
		// echo $extension."<br>";
		// echo $_FILES['file']['error'][$x]."<p>";
		
		if($_FILES['file']['name'][$x] != ''){
			if(strtolower($extension) == '.jpg' || strtolower($extension) == '.jpeg' || strtolower($extension) == '.png'){
				if($_FILES['file']['error'][$x]==2){
					$countError += 1;
				}else{
					move_uploaded_file($_FILES['file']['tmp_name'][$x], $path);
				}
			}else{
				$countError += 1;
			}
		}
	}
	
	// echo $mesage;
	
	if($_POST['spec'] == ''){
		$str="delete from ".$dbname.".log_5photobarang where kodebarang='".$_POST['kodebarangx']."'";
		mysql_query($str);
		echo "Detail item has been deleted";
	}else{
		if($countError > 0){
			echo '<font color=red>Error : <br>1. file size beyond limit (100kb) , or<br>2. file extension must .jpg(.jpeg) or .png</font>';
		}else{
			$str="delete from ".$dbname.".log_5photobarang where kodebarang='".$_POST['kodebarangx']."'";
			mysql_query($str);
			$str="insert into ".$dbname.".log_5photobarang(kodebarang,depan,samping,atas,spesifikasi)
				 values('".$_POST['kodebarangx']."','".$lokasi[0]."','".$lokasi[1]."','".$lokasi[2]."','".$_POST['spec']."')";
			mysql_query($str);
			echo 'Detail item has been saved';
		}
	}
}
?>