<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$afdeling=checkPostGet('afdeling','');

$str="select t1.kodeorg as kodeorganisasi, t1.kodeorg as namaorganisasi from ".$dbname.".setup_blok t1, ".$dbname.".organisasi t2
	 where t1.kodeorg=t2.kodeorganisasi and t2.tipe='BLOK' and t2.induk='".$afdeling."' and t1.intiplasma='P' 
	 order by t1.kodeorg ASC";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	echo "kodeblok.options[kodeblok.options.length] = new Option('".$bar->kodeorganisasi."','".$bar->namaorganisasi."');";
}
?>