<?
//server jakarta
$dbserver='192.168.0.201';
$dbport  ='3306';
$dbname  ='owl';
$uname   ='production';
$passwd  ='passwordFORProduction';
$conn=mysql_connect($dbserver.":".$dbport,$uname,$passwd) or exit(mysql_error($conn)."Error production connection:".$dbname);

//PKS
$str="select ip,username,password,port,dbname from ".$dbname.".setup_remotetimbangan
      where lokasi='SMA'";
//	  echo $str;	
$res=mysql_query($str,$conn);
$idAdd='';
while($bar=mysql_fetch_object($res))
{
    $idAdd=$bar->ip;//ip pks ada di dalam database owl
    $prt=$bar->port;
    $usrName=$bar->username;
    $pswrd=$bar->password;
    $dbnm=$bar->dbname;
}
if($idAdd=='')
{ echo "Error: Koneksi PKS (".$idAdd.") gagal";}
else
  $corn=mysql_connect($idAdd.":".$prt,$usrName,$pswrd) or exit(mysql_error($corn).":Cloud not Connect to remote Computer".$dbnm);
?>
