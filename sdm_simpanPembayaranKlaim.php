<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=$_POST['notransaksi'];
$bayar		=$_POST['bayar'];
$tglbayar	=tanggalsystem($_POST['tglbayar']);

$scek="select karyawanid,tahunplafon,kodebiaya,updatetime from ".$dbname.".sdm_pengobatanht 
       where notransaksi='".$notransaksi."'";
$qcek=mysql_query($scek) or die(mysql_error($conn));
$rcek=mysql_fetch_assoc($qcek);

$sgapok="select distinct sum(jumlah) as jmlhgapok from ".$dbname.".sdm_5gajipokok where
        karyawanid='".$rcek['karyawanid']."' and tahun='".$rcek['karyawanid']."' and idkomponen =1";
$qgapok=mysql_query($sgapok) or die(mysql_error($conn));
$rgapok=mysql_fetch_assoc($qgapok);

$sbayar="select sum(bebanperusahaan) as sudahbayar from ".$dbname.".sdm_pengobatanht
         where karyawanid='".$rcek['karyawanid']."' and tanggalbayar !='0000-00-00' 
         and jlhbayar!=0 and tahunplafon='".$rcek['karyawanid']."' and kodebiaya like '".$rcek['kodebiaya']."'";
$qbayar=mysql_query($sbayar) or die(mysql_error($conn));
$rbayar=mysql_fetch_assoc($qbayar); 

$bebanperusahaan=$rgapok['jmlhgapok']-$rbayar['sudahbayar'];
$str="update ".$dbname.".sdm_pengobatanht set jlhbayar=".$bayar.",
      tanggalbayar=".$tglbayar.",posting=1
      where notransaksi='".$notransaksi."'";
//exit("error: ".$str);
if(mysql_query($str))
{
    $str1="update ".$dbname.".sdm_pengobatanht set bebanperusahaan=".$bebanperusahaan."
          where karyawanid='".$rcek['karyawanid']."' and tanggalbayar ='0000-00-00' 
          and jlhbayar=0 and tahunplafon='".$rcek['tahunplafon']."' and kodebiaya like '".$rcek['kodebiaya']."'
          and kodeorg ='".substr($notransaksi,0,4)."'";
//    exit("error: ".$str1);
    if(mysql_query($str1)){}
}
else
{
	echo " Gagal ".addslashes(mysql_error($conn));
}	  
?>
