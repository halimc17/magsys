<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$karyawanid		=$_POST['karyawanid'];
$pt=$_POST['pt'];
$lokasitugas=$_POST['lokasitugas'];
$str="update ".$dbname.".datakaryawan set kodeorganisasi='".$pt."', subbagian='',
      lokasitugas='".$lokasitugas."' where karyawanid=".$karyawanid;
	  mysql_query($str);	  
if(mysql_affected_rows($conn)==1)
{
}	
else
{
	echo " Gagal:";
}  
?>