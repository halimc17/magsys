<?php
$dbserver='localhost';
$dbport  ='3306';
$dbname  ='erpmill';
#$uname   ='production';
#$passwd  ='passwordFORProduction';
$uname	='root';
$passwd	='';

$conn=mysql_connect($dbserver.":".$dbport,$uname,$passwd) or die("Error/Gagal :Unable to Connect to database ".$dbserver);
@require_once('activity_log.php');
?>
