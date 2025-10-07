<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
//====================================

if(isTransactionPeriod())//check if transaction period is normal
{
        $induk=$_POST['induk'];
        $subunitx=$_POST['subunitx'];
        $blehh="<option value=''></option>";
        $str="select distinct kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$induk."' and tipe not like '%gudang%' order by kodeorganisasi";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
			if(($bar->kodeorganisasi) == $subunitx){
				$blehh.="<option value='".$bar->kodeorganisasi."' selected>".$bar->namaorganisasi."</option>";
			}else{
				$blehh.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
			}            
        }

    #ambil proect
   if(substr($induk,0,2)=='AK' or substr($induk,0,2)=='PB')
   {
       $blehh='';
       $str="select kode,nama from ".$dbname.".project where kode='".$induk."'";    
      $res=mysql_query($str);
      while($bar=mysql_fetch_object($res))
       {
        $blehh.="<option value='".$bar->kode."'>".$bar->kode."-".$bar->nama."</option>";
        }          
   }
   else{
       $str="select kode,nama from ".$dbname.".project where kodeorg='".$induk."' and posting=0";    
      $res=mysql_query($str);
      while($bar=mysql_fetch_object($res))
       {
        $blehh.="<option value='".$bar->kode."'>Project: ".$bar->kode."-".$bar->nama."</option>";
        }          
   }          
            //$blehh.=ambilSubUnit('',$induk);
            echo $blehh;
}
else
{
	echo " Error: Transaction Period missing";
}
?>