<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tahunbulan = implode("",explode('-',$param['periode']));
#1. Ambil semua aktiva yang aktif
if($_SESSION['language']=='EN'){
    $zz="b.namatipe1 as namatipe";
}else{
    $zz="b.namatipe";
}
$rinci = array();
 $str="select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,".$zz." 
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."' and persendecline=0";

 $res=  mysql_query($str);
 $ass=array();
 $nama=array();
 $pass=array();
 while($bar=mysql_fetch_object($res))
 {
     $x=mktime(0,0,0,  intval(substr($bar->awalpenyusutan,5,2)+($bar->jlhblnpenyusutan-1)),15,substr($bar->awalpenyusutan,0,4));
     $maxperiod=date('Y-m',$x);
     if($param['periode']<$maxperiod) {
		if(!isset($ass[$bar->tipeasset])) $ass[$bar->tipeasset]=0;
        $ass[$bar->tipeasset]+=$bar->bulanan;
		//$rinci[] = array($bar->kodeasset, $bar->bulanan);
     }
     
     $nama[$bar->tipeasset]=$bar->namatipe;
	 if(substr($_SESSION['empl']['lokasitugas'],-2)=='HO'){
		$pass[$bar->tipeasset]='DPH'.substr($bar->tipeasset,0,2);
	 }else{
		$pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);
	 }
 }
 


 
//Ambil double declining
  $str="select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.persendecline,a.hargaperolehan,".$zz." 
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.persendecline>'0'";
 $res=  mysql_query($str);

while($bar=mysql_fetch_object($res)){
	$thnawal=substr($bar->awalpenyusutan,0,4);
	$blnawal=substr($bar->awalpenyusutan,5,2);
	$total=($thnawal*12)+$blnawal;

	$thnNow=substr($param['periode'],0,4);
	$blnNow=substr($param['periode'],5,2);
	
	$totalBulanAwal = 12-$blnawal+1;
	$totalTahun = $thnNow-$thnawal-1;
	
	$totalNow=($thnNow*12)+$blnNow+1;
	$selisih=$totalNow-$total;
	$out=0;
	$akumNow = $sekarang = 0;
	
	// Depresiasi s/d akhir tahun
	$before = $sekarang = $bar->hargaperolehan;
	if($totalTahun>-1) {
		$akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
	}
	$sekarang -= $akumNow;
	
	// Depresiasi per Tahun
	if($totalTahun>0) {
		for($i=0;$i<$totalTahun;$i++) {
			$before = $sekarang;
			$akumNow += $sekarang*$bar->persendecline/100;
			$sekarang -= $sekarang*$bar->persendecline/100;
		}
	}
	
	// Depresiasi per Bulan
	$out = $sekarang*($bar->persendecline/100)/12;
	//if($bar->jlhblnpenyusutan==$selisihNow) {
	if($bar->jlhblnpenyusutan<$selisih) {
		$sekarang = $out = 0;
		//if($totalTahun>-1) {
		//	$out = $sekarang - ($blnNow*$sekarang);
		//} else {
		//	$out = $sekarang - (($blnNow-$bulanawal+1)*$sekarang);
		//}
		//if(intval($blnNow)>0) {
		//	$out = $sekarang - ($sisaBulan*$out);
		//} else {
		//	$out = $before/12;
		//}
	}
	
	if(isset($ass[$bar->tipeasset])) {
		$ass[$bar->tipeasset]+=$out;
	} else {
		$ass[$bar->tipeasset]=$out;
	}
	//$rinci[] = array($bar->kodeasset, $out);
	$nama[$bar->tipeasset]=$bar->namatipe;
	$pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);      
}

echo"<button class=mybutton onclick=prosesPenyusutan(1) id=btnproses>Process</button>
	<table class=sortable cellspacing=1 border=0>
	<thead>
	<tr class=rowheader>
	<td>No</td>
	<td>Asset Type</td>
	<td>Journal Code</td>
	<td>Period</td>
	<td>".$_SESSION['lang']['keterangan']."</td>
	<td>".$_SESSION['lang']['jumlah']."</td>
	</tr>
	</thead>
	<tbody>";

$no=0;
foreach($ass as $key=>$val)
{ 
	$no+=1;

	echo"<tr class=rowcontent id='row".$no."'>
	<td>".$no."</td>
	<td id='tipeasset".$no."'>".$key."</td>
	<td id='kodejurnal".$no."'>".$pass[$key]."</td>    
	<td id='periode".$no."'>".$param['periode']."</td>
	<td id='keterangan".$no."'>".$nama[$key]."</td>
	<td align=right id='jumlah".$no."'>".number_format($ass[$key],2,'.','')."</td>
	</tr>";
}
echo"</tbody><tfoot></tfoot></table>";
?>