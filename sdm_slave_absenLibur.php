<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$jnlibur=$_POST['jnlibur'];
$tgllibur=tanggalsystem($_POST['tgllibur']);
#periksa jl libur, jika Minggu (M) maka periksa tgllibur
$t=substr($tgllibur,0,4)."-".substr($tgllibur,4,2)."-".substr($tgllibur,6,2);
$hari=date('D',  strtotime($t));
if($hari=='Sun' and $jnlibur!='MG'){
    exit('Error: Date '.$_POST['tgllibur']." is Sunday, absence code incorrect");
}
else if($jnlibur=='MG' and $hari!='Sun'){
      exit('Error: Date '.$_POST['tgllibur']." is not Sunday, absence code incorrect");  
}
#ambil periode gaji
$str="select periode from ".$dbname.".sdm_5periodegaji where '".$t."'<=tanggalsampai and   '".$t."'>=tanggalmulai and jenisgaji='H' 
          and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $periode=$bar->periode;
}
if($periode==''){
    exit("Error: Payroll period required");
}



$iOrg="select distinct subbagian from ".$dbname.".datakaryawan where tipekaryawan in(1,2,3,6) and 
    lokasitugas='".$_SESSION['empl']['lokasitugas']."' and 
    (tanggalkeluar>='".$t."' or tanggalkeluar='0000-00-00') and alokasi=0
    and ( tanggalmasuk<='".$t."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)";

$nOrg=  mysql_query($iOrg) or die (mysql_error($conn));
while($dOrg=  mysql_fetch_assoc($nOrg))
{
    if($dOrg['subbagian']=='')
    {
        $subbag=$_SESSION['empl']['lokasitugas'];
        $sortSubbag="  subbagian=''";
    }
    else
    {
        $subbag=$dOrg['subbagian'];
        $sortSubbag="  subbagian='".$dOrg['subbagian']."' ";
    }
    
    
    $iHt="INSERT INTO ".$dbname.".`sdm_absensiht` (`tanggal`, `kodeorg`, `periode`)
        VALUES ('".$t."', '".$subbag."', '".$periode."')";
    if(mysql_query($iHt))
    {  
        #insert dtnya
        #ambil list kary di subbag itu
        $iKar="select distinct karyawanid from ".$dbname.".datakaryawan where tipekaryawan in(1,2,3,6) and 
            lokasitugas='".$_SESSION['empl']['lokasitugas']."' and ".$sortSubbag." and
            (tanggalkeluar>='".$t."' or tanggalkeluar='0000-00-00') and alokasi=0
            and ( tanggalmasuk<='".$t."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)";
        $nKar=  mysql_query($iKar) or die (mysql_error($conn));
        while($dKar=  mysql_fetch_assoc($nKar))
        {
            $iDt="insert into ".$dbname.".sdm_absensidt(kodeorg,tanggal,karyawanid,absensi,jam,jamPlg,catu)
              VALUES ('".$subbag."','".$t."','".$dKar['karyawanid']."','".$jnlibur."','00:00:00','00:00:00',0)";        
            if(mysql_query($iDt))
            {   
            }
            else
            {  //echo " Gagal,".addslashes(mysql_error($conn));
            }
        }
        
    }
    else
    {
        #insert dtnya
        #ambil list kary di subbag itu
        $iKar="select distinct karyawanid from ".$dbname.".datakaryawan where tipekaryawan in(1,2,3,6) and 
            lokasitugas='".$_SESSION['empl']['lokasitugas']."' and ".$sortSubbag." and
            (tanggalkeluar>='".$t."' or tanggalkeluar='0000-00-00') and alokasi=0
            and ( tanggalmasuk<='".$t."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)";
        $nKar=  mysql_query($iKar) or die (mysql_error($conn));
        while($dKar=  mysql_fetch_assoc($nKar))
        {
            $iDt="insert into ".$dbname.".sdm_absensidt(kodeorg,tanggal,karyawanid,absensi,jam,jamPlg,catu)
              VALUES ('".$subbag."','".$t."','".$dKar['karyawanid']."','".$jnlibur."','00:00:00','00:00:00',0)";        
            if(mysql_query($iDt))
            {   
            }
            else
            {  //echo " Gagal,".addslashes(mysql_error($conn));
            }
        }
    }
}



?>