<link rel=stylesheet type=text/css href='style/generic.css'>
<?
require_once('config/connection.php');

if(isset($_GET['what'])){
  $what=$_GET['what'];
}
else
{
 $what='';
}
$tanggal = date('d-m-Y', time());
$hariini = date('Y-m-d', time());
$bulan = date('m', time());
$tahun = date('Y', time());

$updatetime=date('d M Y H:i:s', time());

//                $hariini = '2015-12-31';
//                $bulan = '12';
//                $tahun = '2012';

$dt = strtotime($hariini);
$deadline = strtotime('2014-12-31');

$timeDiff = $deadline - $dt;
$numberDays = $timeDiff/86400;  // 86400 seconds in one day
// and you might want to convert to integer
$numberDays = intval($numberDays);

echo "</br></br><center>Batas Sertifikasi ISPO:</center></br>";
echo "<center><b style='font-size:16px;'>31 Desember 2014</b></center></br>";
if($numberDays>0)
echo "<center>tinggal <b style='font-size:16px;'>".$numberDays."</b> hari</center></br></br>";
if($numberDays==0)
echo "<center><b style='font-size:16px;'>HARI INI</b></center></br></br>";
if($numberDays<0)
echo "<center>lewat <b style='font-size:16px;'>".abs($numberDays)."</b> hari</center></br></br>";

?>

<img src="images/logoispo.jpg" width="220" style="position:fixed;right:0;bottom:0;z-index:-999;">

<?

?>