<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');

	$gudang=$_POST['gudang'];

//ambil namapt
$str="select kodeorg, periode from ".$dbname.".setup_periodeakuntansi 
      where kodeorg='".$gudang."' order by periode desc";
    if($gudang=='sumatera')
        $str="select distinct kodeorg, periode from ".$dbname.".setup_periodeakuntansi 
              where kodeorg in('MRKE10','SKSE10','SOGM20','SSRO21','WKNE10') group by periode";
    if($gudang=='kalimantan')
        $str="select distinct kodeorg, periode from ".$dbname.".setup_periodeakuntansi 
              where kodeorg in('SBME10','SBNE10','SMLE10','SMTE10','SSGE10','STLE10') group by periode";
$res=mysql_query($str);
//	$hasil='<option value="">Seluruhnya</option>';
//$hasil="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($res))
{
	$no+=1;
	if($no==1){
         $hasil.="<option value='".substr($bar->periode,0,4)."'>".substr($bar->periode,0,4)."</option>";
	}else
	if(substr($bar->periode,5,2)=='12'){
         $hasil.="<option value='".substr($bar->periode,0,4)."'>".substr($bar->periode,0,4)."</option>";
	}
	$hasil.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
echo $hasil;
?>