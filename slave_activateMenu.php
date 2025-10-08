<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$id=$_POST['id'];
$hideValue=$_POST['setHide'];
	$str1="update ".$dbname.".menu set hide=".$hideValue.",
	       lastuser='".$_SESSION['standard']['username']."'
		   where id=".$id;
	echo $str1;	   
	if(mysql_query($str1))
	{}
	else
	{
		echo " Gagal,".addslashes(mysql_error($conn));
	}
?>