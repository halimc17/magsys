<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$uname=trim($_POST['uname']);
$p1=$_POST['p1'];
$p2=$_POST['p2'];

//pastikan penggantian atas nama dia sendiri sama dengan yang login
$str="select * from ".$dbname.".user where namauser='".$uname."' 
      and karyawanid='".$_SESSION['standard']['userid']."'";   
if(mysql_num_rows(mysql_query($str))<1){
   exit("Error: you are not the user as defined");
}	  

$str="select * from ".$dbname.".user where namauser='".$uname."'
      and password=MD5('".$p1."')";  
$res=mysql_query($str);
//echo 'error';
//$res=mysql_fetch_array($res);	  	
if(mysql_num_rows($res)<1)
{
	echo " Gagal:Old password doesn't match";
}
else
{
	
	$str="update ".$dbname.".user
	      set password=MD5('".$p2."'),
		  lastuser='".$_SESSION['standard']['username']."' 
		  where namauser='".$uname."'";

   if(mysql_query($str))
   {	  
   }
	else
	{
		echo " Gagal,".addslashes(mysql_error($conn));
	}
}
?>
