<?
$dbserver='192.168.0.201';
$dbport  ='3306';
$dbname  ='owl';
#$uname   ='production';
#$passwd  ='passwordFORProduction';
$uname	='root';
$passwd	='M3dc0@20';

$conn=mysql_connect($dbserver.":".$dbport,$uname,$passwd) or die("Error/Gagal :Unable to Connect to database ".$dbserver);
@require_once('activity_log.php');
?>
