<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid=$_POST['karid'];

// Gaji
$optGaji = makeOption($dbname,"sdm_5gajipokok","idkomponen,jumlah",
					  "karyawanid=".$karyawanid);

// Data Karyawan
$str="select * from ".$dbname.".datakaryawan where karyawanid=".$karyawanid ." limit 1";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    echo"<?xml version='1.0' ?>
	<karyawan>
		<tipekaryawan>".($bar->tipekaryawan!=""?$bar->tipekaryawan:"*")."</tipekaryawan>
		<kodejabatan>".($bar->kodejabatan!=""?$bar->kodejabatan:"*")."</kodejabatan>
		<kodegolongan>".($bar->kodegolongan!=""?$bar->kodegolongan:"*")."</kodegolongan>
		<lokasitugas>".($bar->lokasitugas!=""?$bar->lokasitugas:"*")."</lokasitugas>
		<bagian>".($bar->bagian!=""?$bar->bagian:"*")."</bagian>";
	echo "<gaji1>".(isset($optGaji[1])? number_format($optGaji[1],2): 0)."</gaji1>";
	echo "<gaji2>".(isset($optGaji[2])? number_format($optGaji[2],2): 0)."</gaji2>";
	echo "</karyawan>";	
}
?>