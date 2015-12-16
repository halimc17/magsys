<?php
require_once('master_validation.php');
require_once('config/connection.php');

$proses=$_POST['proses'];
$tipeasset=$_POST['tipeasset'];
$kodesubasset=$_POST['kodesubasset'];
$namasubasset=$_POST['namasubasset'];
$umurpenyusutan=$_POST['umurpenyusutan'];

switch($proses)
{
	case 'simpan':
		// $strcount="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$tipeasset."' and kodesub=".$kodesubasset."";		
		// $rescount=mysql_query($strcount);
		// $num_rows = mysql_num_rows($rescount);
		
		$strsimpan="insert into ".$dbname.".sdm_5subtipeasset values('".$tipeasset."','".$kodesubasset."','".$namasubasset."','".$umurpenyusutan."')";
		
		if(mysql_query($strsimpan)){
		}else{
			echo " Gagal,".addslashes(mysql_error($conn));
		}
	break;
	case 'edit':
		$strsimpan="update ".$dbname.".sdm_5subtipeasset set namasub='".$namasubasset."', umurpenyusutan='".$umurpenyusutan."' where kodetipe='".$tipeasset."' and kodesub='".$kodesubasset."'";
		if(mysql_query($strsimpan)){
		}else{
			echo " Gagal,".addslashes(mysql_error($conn));
		}
	break;
	default:
	break;
}

### BEGIN GET TYPE ASSET ###
$str="select kodetipe, namatipe from ".$dbname.".sdm_5tipeasset";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namatipe[$bar->kodetipe]=$bar->namatipe;
}
### END GET TYPE ASSET ###

$str1="select * from ".$dbname.".sdm_5subtipeasset order by namasub";
if($res1=mysql_query($str1))
{
	while($bar1=mysql_fetch_object($res1))
	{
		echo"<tr class=rowcontent>
			 <td align=center>".$bar1->kodesub."</td>
			 <td>".$bar1->namasub."</td>
			 <td>".$bar1->umurpenyusutan."</td>
			 <td>".$namatipe[$bar1->kodetipe]."</td>
			 <td style='text-align:center'><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editSubTipeAset('".$bar1->kodesub."','".$bar1->namasub."','".$bar1->umurpenyusutan."','".$bar1->kodetipe."');\"></td></tr>";
	}	 
}
?>