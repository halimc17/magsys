<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];
$nourut=$_POST['nourut'];
$tipe=$_POST['tipe'];

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

if($bulan=='01' or $bulan=='1'){
  $bulanlalu=12;
 }else{ 
  $bulanlalu=$bulan-1;
} 
if($bulanlalu<10)$bulanlalu='0'.$bulanlalu; // bulan lalu dia digit
$periodelalu=$tahun.'-'.$bulanlalu; // periode lalu
if($bulan==1)$periodelalu=$tahunlalu.'-12';

$desemberlalu=$tahunlalu.'-12'; // periode desember tahun lalu

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namapt=strtoupper($bar->namaorganisasi);
}

$kodelaporan='LK - LABA RUGI';

$periodesaldo=str_replace("-", "", $periode);

//periode db
$periodeCUR=str_replace("-", "", $periode);
$periodePRF=str_replace("-", "", $periodelalu);
//$periodeLSD=str_replace("-", "", $desemberlalu);

//kolom db
$kolomCUR='debet'.$bulan.'-kredit'.$bulan;
$kolomPRF='debet'.$bulanlalu.'-kredit'.$bulanlalu;
$kolomLSD='awal'.$bulan.'+debet'.$bulan.'-kredit'.$bulan;

//title table
$t=mktime(0,0,0,substr($periodeCUR,4,2),15,substr($periodeCUR,0,4));
$captionCUR=date('M-Y',$t);
$t=mktime(0,0,0,substr($periodePRF,4,2),15,substr($periodePRF,0,4));
$captionPRF=date('M-Y',$t);
//$t=mktime(0,0,0,substr($periodeLSD,4,2),15,substr($periodeLSD,0,4));
//$captionLSD=date('M-Y',$t);

//involving units
if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" ='".$unit."'";

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    foreach($qwe as $rty){
        //if((number_format($rty)!=0)){
        if($rty!=0){
            $emaknya[$rty]=$bar->nourut;
            $adaemaknya[$rty]=$rty;
        }
    }
    $whrakun="noakun between '".$bar->noakundari."'  and '".$bar->noakunsampai."'";
    switch ($bar->nourut) {
        case '211102':
          $whrakun=" noakun in (".$bar->noakundisplay.")";
        break;
    }
    /*$st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and periode like'".$tahun."%' and kodeorg ".$where." order by periode";*/

    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,sum(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan from ".$dbname.".keu_saldobulanan 
           where  ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode  order by periode";
    $res12=mysql_query($st12);
    while($ba12=mysql_fetch_object($res12)){
        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
        if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
        if($bulan>=$ba12->bulan){
            if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
            if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }
    switch ($bar->nourut){
            case'100009':
            case'213102':
            case'213301':
            case'211109':
            case'213501':
            case'213103':
            case'215999':
            case'216999':
                $dt=explode(",",$bar->noakundisplay);
                $coundAja=count($dt);
                for ($i = 1; $i <= $bulan; $i++) {
                    $totalDt=0;
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                    for($ard=0;$ard<$coundAja;$ard++){
                        $totalDt+=$dzArr[$dt[$ard]][$ii];
                    }
                    $dzArr[$bar->nourut][$ii]=$totalDt;
                    $dzArr[$bar->nourut]['sd']+=$totalDt;
                }
            break;
            
            case'213302':

                $dt=explode(",",$bar->noakundisplay);
                $coundAja=count($dt);
                for ($i = 1; $i <= $bulan; $i++) {
                    $totalDt=0;
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                    $totalDt+=$dzArr[$dt[0]][$ii];
                    for($ard=1;$ard<$coundAja;$ard++){
                        $totalDt-=$dzArr[$dt[$ard]][$ii];
                    }
                    $dzArr[$bar->nourut][$ii]=$totalDt;
                    $dzArr[$bar->nourut]['sd']+=$totalDt;
                }
            break;
            
    }  
}


if($tipe=='Detail'){

        //report format
        $str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' and nourut = '".$nourut."'";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $dzArr[$bar->nourut]['nourut']=$bar->nourut;
            $dzArr[$bar->nourut]['tipe']=$bar->tipe;
            if($_SESSION['language']=='ID'){
                $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
            else{
                $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
            }
            $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
            $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
            
            // dari total
            $qwe=explode(",",$bar->noakundisplay);
            if(!empty($qwe))foreach($qwe as $rty){
                if((intval($rty)!=0)){
                    $emaknya[$rty]=$bar->nourut;
                    $adaemaknya[$rty]=$rty;
                }
            }           
            $whrakun="noakun between '".$bar->noakundari."'  and '".$bar->noakunsampai."'";
            switch ($bar->nourut) {
                case '211102':
                  $whrakun=" noakun in (".$bar->noakundisplay.")";
                break;
            }
            
            $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
            $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
                from ".$dbname.".keu_saldobulanan where ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode order by periode";
            $res12=mysql_query($st12);
            while($ba12=mysql_fetch_object($res12))
            {
                $daftar[$ba12->noakun]=$ba12->noakun;
            }              

        }
        if(isset($daftar) and !is_null($daftar)) sort($daftar);

        $st12="select noakun, namaakun, namaakun1
            from ".$dbname.".keu_5akun where level=5";
        $res12=mysql_query($st12);
        while($ba12=mysql_fetch_object($res12))
        {
            if($_SESSION['language']=='ID'){
                $akun[$ba12->noakun]=$ba12->namaakun;}
            else{
                $akun[$ba12->noakun]=$ba12->namaakun1;
            }
        }      

        $stream="<table class=sortable border=0 cellspacing=0>";
        if(!empty($daftar))foreach($daftar as $akunnya){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td style='width:10px'></td>
                <td style='width:10px'></td>
                <td style='width:510px'>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= $bulanlalu; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$ii],2)."</td>";
                }            
                $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya]['sd'],2)."</td>
                    <td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
        }
        $stream.="</table>";            
    

}

//////////////////////////////////////////////////////////////////////////HEADER

echo $stream;
 














































/*
$noakunjualcpo='5110103';
$noakunjualker='5110104';

//_____________________________________________________________ nilai awal tahun
$st12="select sum(kuantitas) as cpo, sum(kernelquantity) as ker
    from ".$dbname.".pabrik_masukkeluartangki where tanggal = '".$tahun."-01-01' and kodeorg ".$where."";
$res12=mysql_query($st12);
while($ba12=mysql_fetch_object($res12))
{
    $fisikawal['CPO']['01']=$ba12->cpo;
    $fisikawal['KER']['01']=$ba12->ker;
}

//$hargaawal['CPO']['01']=8233;
//$hargaawal['KER']['01']=5060;

// ambil persediaan awal dari 1150201 dan 1150202
$st12="select noakun,awal01 as awal, substr(periode,5,2) as bulan
    from ".$dbname.".keu_saldobulanan where noakun in('1150201','1150202') and periode like'".$tahun."%' and kodeorg ".$where." order by periode";
$res12=mysql_query($st12);
$nilaiawal['CPO']['01']=0;
$nilaiawal['KER']['01']=0;
while($ba12=mysql_fetch_object($res12))
{
    if($ba12->noakun=='1150201')$nilaiawal['CPO']['01']+=$ba12->awal;
    if($ba12->noakun=='1150202')$nilaiawal['KER']['01']+=$ba12->awal;
}  

@$hargaawal['CPO']['01']=$nilaiawal['CPO']['01']/$fisikawal['CPO']['01'];
@$hargaawal['KER']['01']=$nilaiawal['KER']['01']/$fisikawal['KER']['01'];

//_____________________________________________________ambil produksi CPO dan PK
$st12="select sum(oer) as cpo, sum(oerpk) as ker, substr(tanggal,6,2) as bulan
    from ".$dbname.".pabrik_produksi where tanggal like '".$tahun."%' and kodeorg ".$where." group by substr(tanggal,6,2) order by tanggal";
$res12=mysql_query($st12);
$fisikprod['CPO']['sd']=0;
$fisikprod['KER']['sd']=0;
while($ba12=mysql_fetch_object($res12))
{
    $fisikprod['CPO'][$ba12->bulan]=$ba12->cpo;
    $fisikprod['KER'][$ba12->bulan]=$ba12->ker;
    if($bulan>=$ba12->bulan){
        $fisikprod['CPO']['sd']+=$ba12->cpo;
        $fisikprod['KER']['sd']+=$ba12->ker;
    }
}

//_______________________________________________________ ambil harga CPO dan PK
$st12="select a.kodebarang, a.nokontrak, a.nodo, (b.hargasatuan*a.beratbersih) as nilai, (a.beratbersih) as fisik, b.catatanlain, substr(a.tanggal,6,2) as bulan
    from ".$dbname.".pabrik_timbangan a
    left join ".$dbname.".pmn_kontrakjual b on a.nokontrak = b.nokontrak 
        where a.kodebarang in ('40000001', '40000002') and a.tanggal like '".$tahun."%' and a.millcode ".$where." order by a.tanggal";
$res12=mysql_query($st12);
while($ba12=mysql_fetch_object($res12))
{
    if($ba12->kodebarang == '40000001')$komoditi='CPO';
    if($ba12->kodebarang == '40000002')$komoditi='KER';
    $fisikjual[$komoditi][$ba12->bulan]+=$ba12->fisik;
    if($ba12->catatanlain=='exclude')$nilai[$komoditi][$ba12->bulan]+=$ba12->nilai; else $nilai[$komoditi][$ba12->bulan]+=($ba12->nilai/1.1);
    @$hargajual[$komoditi][$ba12->bulan]=$nilai[$komoditi][$ba12->bulan]/$fisik[$komoditi][$ba12->bulan];
    if($bulan>=$ba12->bulan){
        $fisikjual[$komoditi]['sd']+=$ba12->fisik;
        if($ba12->catatanlain=='exclude')$nilai[$komoditi]['sd']+=$ba12->nilai; else $nilai[$komoditi]['sd']+=($ba12->nilai/1.1);
        @$hargajual[$komoditi]['sd']=$nilai[$komoditi]['sd']/$fisik[$komoditi]['sd'];
    }    
}


//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    foreach($qwe as $rty){
        //if((number_format($rty)!=0)){
        if($rty!=0){
            $emaknya[$rty]=$bar->nourut;
            $adaemaknya[$rty]=$rty;
        }
    }
	#NGUMPULIN DATA TBS #
	if($bar->nourut==212999){
		$dzArrFsk[$bar->nourut]['sd']=0;
		//TBS TOTAL KESELURUHAN
		$sData="select sum(beratbersih-kgpotsortasi) as jmlhKg,kodeorg,substr(tanggal,6,2) as bln from ".$dbname.".pabrik_timbangan where kodeorg ".$where." and (left(tanggal,7)<= '".$periode."' and left(tanggal,4)='".substr($periode,0,4)."') and nospb in (select distinct nospb from ".$dbname.".kebun_spbht where kodeorg ".$where." and  (left(tanggal,7)<= '".$periode."' and left(tanggal,4)='".substr($periode,0,4)."') and posting=1) group by left(tanggal,7),kodeorg";
		//exit("error:".$sData);
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			$dzArrFsk[$bar->nourut][$rData['bln']]+=$rData['jmlhKg'];
			$dzArrFsk[$bar->nourut]['sd']+=$rData['jmlhKg'];
		}
		$dzArrFskKrm[$bar->nourut]['sd']=0;
		//TBS TOTAL PENGIRIMAN EKSTERNAL
		$sData="select sum(beratbersih-kgpotsortasi) as jmlhKg,kodeorg,substr(tanggal,6,2) as bln from ".$dbname.".pabrik_timbangan where kodeorg ".$where." and (left(tanggal,7)<= '".$periode."' and left(tanggal,4)='".substr($periode,0,4)."') and nospb in (select distinct nospb from ".$dbname.".kebun_spbht where kodeorg ".$where." and (left(tanggal,7)<= '".$periode."' and left(tanggal,4)='".substr($periode,0,4)."') and tujuan=2 and posting=1) group by left(tanggal,7),kodeorg";
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			$dzArrFskKrm[$bar->nourut][$rData['bln']]+=$rData['jmlhKg'];
			$dzArrFskKrm[$bar->nourut]['sd']+=$rData['jmlhKg'];
		}
	}
	
    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and periode like'".$tahun."%' and kodeorg ".$where." order by periode";
//    if($bar->nourut=='211001') {echo $bulan.' '.$st12; exit;}
    $res12=mysql_query($st12);
    while($ba12=mysql_fetch_object($res12)){
		if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
		if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
        if($bulan>=$ba12->bulan){
			if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
			if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
}

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    @$hargajual['CPO'][$ii]=$dzArr2[$noakunjualcpo][$ii]/$fisikjual['CPO'][$ii];
    @$hargajual['KER'][$ii]=$dzArr2[$noakunjualker][$ii]/$fisikjual['KER'][$ii];
}
@$hargajual['CPO']['sd']=$dzArr2[$noakunjualcpo]['sd']/$fisikjual['CPO']['sd'];
@$hargajual['KER']['sd']=$dzArr2[$noakunjualker]['sd']/$fisikjual['KER']['sd'];

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    @$persenjual['CPO'][$ii]=$fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii]/(($fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii])+($fisikprod['KER'][$ii]*$hargajual['KER'][$ii]));
    @$persenjual['KER'][$ii]=$fisikprod['KER'][$ii]*$hargajual['KER'][$ii]/(($fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii])+($fisikprod['KER'][$ii]*$hargajual['KER'][$ii]));
}
@$persenjual['CPO']['sd']=$fisikprod['CPO']['sd']*$hargajual['CPO']['sd']/(($fisikprod['CPO']['sd']*$hargajual['CPO']['sd'])+($fisikprod['KER']['sd']*$hargajual['KER']['sd']));
@$persenjual['KER']['sd']=$fisikprod['KER']['sd']*$hargajual['KER']['sd']/(($fisikprod['CPO']['sd']*$hargajual['CPO']['sd'])+($fisikprod['KER']['sd']*$hargajual['KER']['sd']));
    
for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    $j=$i-1;
    if($j<10)$jj='0'.$j; else $jj=$j;
	if(!isset($fisikawal['CPO'][$ii])) $fisikawal['CPO'][$ii]=0;
	if(!isset($fisikprod['CPO'][$ii])) $fisikprod['CPO'][$ii]=0;
	if(!isset($fisikstok['CPO'][$ii])) $fisikstok['CPO'][$ii]=0;
	if(!isset($fisikjual['CPO'][$ii])) $fisikjual['CPO'][$ii]=0;
	if(!isset($fisikawal['KER'][$ii])) $fisikawal['KER'][$ii]=0;
	if(!isset($fisikprod['KER'][$ii])) $fisikprod['KER'][$ii]=0;
	if(!isset($fisikstok['KER'][$ii])) $fisikstok['KER'][$ii]=0;
	if(!isset($fisikjual['KER'][$ii])) $fisikjual['KER'][$ii]=0;
    if($i>1){
        $fisikawal['CPO'][$ii]=$fisikakhir['CPO'][$jj];
        $fisikawal['KER'][$ii]=$fisikakhir['KER'][$jj];
    }else{ // bulan1, harga dan fisik udah ada
        
    }
    $fisikstok['CPO'][$ii]=$fisikawal['CPO'][$ii]+$fisikprod['CPO'][$ii];
    $fisikakhir['CPO'][$ii]=$fisikstok['CPO'][$ii]-$fisikjual['CPO'][$ii];
    $fisikstok['KER'][$ii]=$fisikawal['KER'][$ii]+$fisikprod['KER'][$ii];
    $fisikakhir['KER'][$ii]=$fisikstok['KER'][$ii]-$fisikjual['KER'][$ii];
}
if(!isset($fisikawal['CPO']['sd'])) $fisikawal['CPO']['sd']=0;
if(!isset($fisikprod['CPO']['sd'])) $fisikprod['CPO']['sd']=0;
if(!isset($fisikstok['CPO']['sd'])) $fisikstok['CPO']['sd']=0;
if(!isset($fisikjual['CPO']['sd'])) $fisikjual['CPO']['sd']=0;
if(!isset($fisikawal['KER']['sd'])) $fisikawal['KER']['sd']=0;
if(!isset($fisikprod['KER']['sd'])) $fisikprod['KER']['sd']=0;
if(!isset($fisikstok['KER']['sd'])) $fisikstok['KER']['sd']=0;
if(!isset($fisikjual['KER']['sd'])) $fisikjual['KER']['sd']=0;

//$fisikstok['CPO']['sd']=$fisikawal['CPO']['sd']+$fisikprod['CPO']['sd'];
$fisikstok['CPO']['sd']=$fisikprod['CPO']['sd'];
$fisikakhir['CPO']['sd']=$fisikakhir['CPO'][$bulan];
//$fisikstok['KER']['sd']=$fisikawal['KER']['sd']+$fisikprod['KER']['sd'];
$fisikstok['KER']['sd']=$fisikprod['KER']['sd'];
$fisikakhir['KER']['sd']=$fisikakhir['KER'][$bulan];

$subtotal=array();
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
    if($data['tipe']=='Header')
    {
        $totallagi=0;        
    }
    if($data['tipe']=='Detail'){
        if($data['nourut']=='213101'){ // biaya produksi CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $nilaiprod['CPO'][$ii]=$dzArr['212999'][$ii]*$persenjual['CPO'][$ii];
                $dzArr['213101'][$ii]=$nilaiprod['CPO'][$ii];
            }
            $nilaiprod['CPO']['sd']=$dzArr['212999']['sd']*$persenjual['CPO']['sd'];
            $dzArr['213101']['sd']=$nilaiprod['CPO']['sd'];                
        }
        if($data['nourut']=='213102'){ // biaya produksi KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $nilaiprod['KER'][$ii]=$dzArr['212999'][$ii]*$persenjual['KER'][$ii];
                $dzArr['213102'][$ii]=$nilaiprod['KER'][$ii];
            }
            $nilaiprod['KER']['sd']=$dzArr['212999']['sd']*$persenjual['KER']['sd'];
            $dzArr['213102']['sd']=$nilaiprod['KER']['sd'];               
        }
        if($data['nourut']=='213201'){ // persediaan awal CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $j=$i+1;
                if($j<10)$jj='0'.$j; else $jj=$j;
                if($i==1){
                    $nilaiawal['CPO'][$ii]=$fisikawal['CPO'][$ii]*$hargaawal['CPO'][$ii];
                }else{
                    
                }
                $nilaistok['CPO'][$ii]=$nilaiawal['CPO'][$ii]+$nilaiprod['CPO'][$ii];                
                @$hargastok['CPO'][$ii]=$nilaistok['CPO'][$ii]/$fisikstok['CPO'][$ii];                                                
                $nilaiakhir['CPO'][$ii]=$hargastok['CPO'][$ii]*$fisikakhir['CPO'][$ii];
                $nilaijual['CPO'][$ii]=$hargastok['CPO'][$ii]*$fisikjual['CPO'][$ii];
                $nilaiawal['CPO'][$jj]=$nilaiakhir['CPO'][$ii];
                $dzArr['213201'][$ii]=$nilaiawal['CPO'][$ii];                
                
                @$hargaprod['CPO'][$ii]=$nilaiprod['CPO'][$ii]/$fisikprod['CPO'][$ii];
                @$hargaawal['CPO'][$ii]=$nilaiawal['CPO'][$ii]/$fisikawal['CPO'][$ii];
            }
            $nilaiawal['CPO']['sd']=$fisikawal['CPO']['01']*$hargaawal['CPO']['01'];
            $nilaistok['CPO']['sd']=$nilaiawal['CPO']['sd']+$nilaiprod['CPO']['sd'];
            @$hargastok['CPO']['sd']=$nilaistok['CPO']['sd']/$fisikstok['CPO']['sd'];                
            $nilaiakhir['CPO']['sd']=$nilaiakhir['CPO'][$bulan];
            $nilaijual['CPO']['sd']=$hargastok['CPO']['sd']*$fisikjual['CPO']['sd'];            
            $dzArr['213201']['sd']=$nilaiawal['CPO']['sd'];
            
            @$hargaprod['CPO']['sd']=$nilaiprod['CPO']['sd']/$fisikprod['CPO']['sd'];
            $hargaawal['CPO']['sd']=$hargaawal['CPO']['01'];
            $fisikawal['CPO']['sd']=$fisikawal['CPO']['01'];
        }
        if($data['nourut']=='213202'){ // persediaan awal KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $j=$i+1;
                if($j<10)$jj='0'.$j; else $jj=$j;
                if($i==1){
                    $nilaiawal['KER'][$ii]=$fisikawal['KER'][$ii]*$hargaawal['KER'][$ii];
                }else{
                    
                }
                $nilaistok['KER'][$ii]=$nilaiawal['KER'][$ii]+$nilaiprod['KER'][$ii];                
                @$hargastok['KER'][$ii]=$nilaistok['KER'][$ii]/$fisikstok['KER'][$ii];                                                
                $nilaiakhir['KER'][$ii]=$hargastok['KER'][$ii]*$fisikakhir['KER'][$ii];
                $nilaijual['KER'][$ii]=$hargastok['KER'][$ii]*$fisikjual['KER'][$ii];
                $nilaiawal['KER'][$jj]=$nilaiakhir['KER'][$ii];
                $dzArr['213202'][$ii]=$nilaiawal['KER'][$ii];                
                
                @$hargaprod['KER'][$ii]=$nilaiprod['KER'][$ii]/$fisikprod['KER'][$ii];
                @$hargaawal['KER'][$ii]=$nilaiawal['KER'][$ii]/$fisikawal['KER'][$ii];
            }
            $nilaiawal['KER']['sd']=$fisikawal['KER']['01']*$hargaawal['KER']['01'];
            $nilaistok['KER']['sd']=$nilaiawal['KER']['sd']+$nilaiprod['KER']['sd'];
            @$hargastok['KER']['sd']=$nilaistok['KER']['sd']/$fisikstok['KER']['sd'];                
            $nilaiakhir['KER']['sd']=$nilaiakhir['KER'][$bulan];
            $nilaijual['KER']['sd']=$hargastok['KER']['sd']*$fisikjual['KER']['sd'];            
            $dzArr['213202']['sd']=$nilaiawal['KER']['sd'];                       
            
            @$hargaprod['KER']['sd']=$nilaiprod['KER']['sd']/$fisikprod['KER']['sd'];
            @$hargaawal['KER']['sd']=$hargaawal['KER']['01'];
            $fisikawal['KER']['sd']=$fisikawal['KER']['01'];
        }        

        if($data['nourut']=='213401'){ // persediaan akhir CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;                
                $dzArr['213401'][$ii]=$nilaiakhir['CPO'][$ii];                    
                
            }
            $dzArr['213401']['sd']=$nilaiakhir['CPO'][$bulan];             
        }
        
        if($data['nourut']=='213402'){ // persediaan akhir KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;                
                $dzArr['213402'][$ii]=$nilaiakhir['KER'][$ii];                    
                
            }
            $dzArr['213402']['sd']=$nilaiakhir['KER'][$bulan];             
        }
        
        if($data['nourut']=='213301'){ // barang siap dijual CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213301'][$ii]=$nilaistok['CPO'][$ii];
            }
            $dzArr['213301']['sd']=$nilaistok['CPO']['sd'];             
        }
        
        if($data['nourut']=='213402'){ // barang siap dijual KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213402'][$ii]=$nilaistok['KER'][$ii];
            }
            $dzArr['213402']['sd']=$nilaistok['KER']['sd'];             
        }
        
        if($data['nourut']=='213501'){ // penjualan CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213501'][$ii]=$nilaijual['CPO'][$ii];
            }
            $dzArr['213501']['sd']=$nilaijual['CPO']['sd'];             
        }
        
        if($data['nourut']=='213502'){ // penjualan KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213502'][$ii]=$nilaijual['KER'][$ii];
            }
            $dzArr['213502']['sd']=$nilaijual['KER']['sd'];             
        }
        
        // subtotal
		for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
			if(!isset($subtotal[$ii])) $subtotal[$ii]=0;
            $subtotal[$ii] += isset($data[$ii])? $data[$ii]: 0;
        }
		if(!isset($subtotal['sd'])) $subtotal['sd']=0;
        $subtotal['sd']+=isset($data['sd'])? $data['sd']: 0;
        
        $totallagi=0;                
    }
    if($data['tipe']=='Total'){
        if($data['nourut']=='212999'){ // BIAYA PRODUKSI SETELAH ELIMINASI
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
				if(!isset($dzArr['211999'][$ii])) $dzArr['211999'][$ii]=0;
				if(!isset($dzArr['212001'][$ii])) $dzArr['212001'][$ii]=0;
                $dzArr['212999'][$ii]=$dzArr['211999'][$ii]+$dzArr['212001'][$ii];
            }
			if(!isset($dzArr['211999']['sd'])) $dzArr['211999']['sd']=0;
			if(!isset($dzArr['212001']['sd'])) $dzArr['212001']['sd']=0;
            $dzArr['212999']['sd']=$dzArr['211999']['sd']+$dzArr['212001']['sd'];
        }
        if($totallagi==1){
            if(!empty($adaemaknya))foreach($adaemaknya as $ada){
                for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
					if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                    if($emaknya[$ada]==$data['nourut']) $dzArr[$data['nourut']][$ii]+=
						isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
                }
				if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
                if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']]['sd']+=
					isset($dzArr[$ada]['sd'])? $dzArr[$ada]['sd']: 0;
            }
        }else{
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
				$dzArr[$data['nourut']][$ii]+=isset($subtotal[$ii])? $subtotal[$ii]: 0;
                $subtotal[$ii]=0;
            }
			if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            $dzArr[$data['nourut']]['sd']+=isset($subtotal['sd'])? $subtotal['sd']: 0;
        }
        $subtotal['sd']=0;
        
        $totallagi=1;        
    }

}

//////////////////////////////////////////////////////////////////////////DETAIL

if($tipe=='Detail'){

    if($nourut=='213101'){ // biaya produksi CPO: fisik produksi + harga produksi
        $stream.="<table class=sortable border=0 cellspacing=0>";
        @$dataPER=($fisikprod['CPO'][$bulan]-$fisikprod['CPO'][$bulanlalu])/$fisikprod['CPO'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>PRODUKSI CPO (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($fisikprod['CPO'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($fisikprod['CPO']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($fisikprod['CPO'][$bulan]-$fisikprod['CPO'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        @$dataPER=($hargaprod['CPO'][$bulan]-$hargaprod['CPO'][$bulanlalu])/$hargaprod['CPO'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>HARGA CPO (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($hargaprod['CPO'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($hargaprod['CPO']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($hargaprod['CPO'][$bulan]-$hargaprod['CPO'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        $stream.="</table>";            
    }else
    if($nourut=='213102'){ // biaya produksi KER: fisik produksi + harga produksi
        $stream.="<table class=sortable border=0 cellspacing=0>";
        @$dataPER=($fisikprod['KER'][$bulan]-$fisikprod['KER'][$bulanlalu])/$fisikprod['KER'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>PRODUKSI KER (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($fisikprod['KER'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($fisikprod['KER']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($fisikprod['KER'][$bulan]-$fisikprod['KER'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        @$dataPER=($hargaprod['KER'][$bulan]-$hargaprod['KER'][$bulanlalu])/$hargaprod['KER'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>HARGA KER (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($hargaprod['KER'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($hargaprod['KER']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($hargaprod['KER'][$bulan]-$hargaprod['KER'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        $stream.="</table>";              
    }else
    if($nourut=='213201'){ // persediaan awal CPO
        $stream.="<table class=sortable border=0 cellspacing=0>";
        @$dataPER=($fisikawal['CPO'][$bulan]-$fisikawal['CPO'][$bulanlalu])/$fisikawal['CPO'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>PERSEDIAAN AWAL CPO (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($fisikawal['CPO'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($fisikawal['CPO']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($fisikawal['CPO'][$bulan]-$fisikawal['CPO'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        @$dataPER=($hargaawal['CPO'][$bulan]-$hargaawal['CPO'][$bulanlalu])/$hargaawal['CPO'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>HARGA AWAL CPO (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($hargaawal['CPO'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($hargaawal['CPO']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($hargaawal['CPO'][$bulan]-$hargaawal['CPO'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        $stream.="</table>";            
      
    }else
    if($nourut=='213202'){ // Kernel
        $stream.="<table class=sortable border=0 cellspacing=0>";
        @$dataPER=($fisikawal['KER'][$bulan]-$fisikawal['KER'][$bulanlalu])/$fisikawal['KER'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>PERSEDIAAN AWAL KER (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($fisikawal['KER'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($fisikawal['KER']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($fisikawal['KER'][$bulan]-$fisikawal['KER'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        @$dataPER=($hargaawal['KER'][$bulan]-$hargaawal['KER'][$bulanlalu])/$hargaawal['KER'][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>HARGA AWAL KER (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($hargaawal['KER'][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($hargaawal['KER']['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($hargaawal['KER'][$bulan]-$hargaawal['KER'][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        $stream.="</table>";       
    }
	else 
	if($nourut=='212999'){ // BIAYA PRODUKSI SETELAH ELIMINASI
	$stream.="<table class=sortable border=0 cellspacing=0>";
        @$dataPER=($dzArrFsk[$nourut][$bulan]-$dzArrFsk[$nourut][$bulanlalu])/$dzArrFsk[$nourut][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>TOTAL PRODUKSI TBS (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($dzArrFsk[$nourut][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($dzArrFsk[$nourut]['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($dzArrFsk[$nourut][$bulan]-$dzArrFsk[$nourut][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";              
        @$dataPER=($dzArrFskKrm[$nourut][$bulan]-$dzArrFskKrm[$nourut][$bulanlalu])/$dzArrFskKrm[$nourut][$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>TOTAL TBS KE EKSTERNAL (KG)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                $stream.="<td style='width:120px' align=right>".number_format($dzArrFskKrm[$nourut][$ii],2)."</td>";
            }            
            $stream.="<td style='width:120px' align=right>".number_format($dzArrFskKrm[$nourut]['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($dzArrFskKrm[$nourut][$bulan]-$dzArrFskKrm[$nourut][$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>"; 
		@$dataPER=(($dzArr[$nourut][$bulan]/$dzArrFsk[$nourut][$bulan])*$dzArrFskKrm[$nourut][$bulan])/(($dzArr[$nourut][$bulanlalu]/$dzArrFsk[$nourut][$bulanlalu])*$dzArrFskKrm[$nourut][$bulanlalu])*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'>HPP PENJUALAN TBS (RP)</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
				if($dzArrFsk[$nourut][$ii]*$dzArrFskKrm[$nourut][$ii]==0)
					$tmp = 0;
				else
					$tmp = $dzArr[$nourut][$ii]/$dzArrFsk[$nourut][$ii]*$dzArrFskKrm[$nourut][$ii];
                $stream.="<td style='width:120px' align=right>".number_format($tmp,2)."</td>";
				$dzArrsma[$nourut]['sd']+=$tmp;
            }
			if($dzArrFsk[$nourut][$bulan]*$dzArrFskKrm[$nourut][$bulan]==0)
				$tmp2 = 0;
			else
				$tmp2 = (($dzArr[$nourut][$bulan]/$dzArrFsk[$nourut][$bulan])*$dzArrFskKrm[$nourut][$bulan])-(($dzArr[$nourut][$bulanlalu]/$dzArrFsk[$nourut][$bulanlalu])*$dzArrFskKrm[$nourut][$bulanlalu]);
            $stream.="<td style='width:120px' align=right>".number_format($dzArrsma[$nourut]['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($tmp2,2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";		
        $stream.="</table>";
    }
    else{
        //report format
        $str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' and nourut = '".$nourut."'";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $dzArr[$bar->nourut]['nourut']=$bar->nourut;
            $dzArr[$bar->nourut]['tipe']=$bar->tipe;
            if($_SESSION['language']=='ID'){
                $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
            else{
                $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
            }
            $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
            $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
            
            // dari total
            $qwe=explode(",",$bar->noakundisplay);
            if(!empty($qwe))foreach($qwe as $rty){
                if((intval($rty)!=0)){
                    $emaknya[$rty]=$bar->nourut;
                    $adaemaknya[$rty]=$rty;
                }
            }           
            
            $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
            $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
                from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
                and '".$bar->noakunsampai."' and periode like'".$tahun."%' and kodeorg ".$where." order by periode";
            $res12=mysql_query($st12);
            while($ba12=mysql_fetch_object($res12))
            {
                $daftar[$ba12->noakun]=$ba12->noakun;
            }              

        }
		if(isset($daftar) and !is_null($daftar)) sort($daftar);

        $st12="select noakun, namaakun, namaakun1
            from ".$dbname.".keu_5akun where level=5";
        $res12=mysql_query($st12);
        while($ba12=mysql_fetch_object($res12))
        {
            if($_SESSION['language']=='ID'){
                $akun[$ba12->noakun]=$ba12->namaakun;}
            else{
                $akun[$ba12->noakun]=$ba12->namaakun1;
            }
        }      

        $stream="<table class=sortable border=0 cellspacing=0>";
        if(!empty($daftar))foreach($daftar as $akunnya){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td style='width:10px'></td>
                <td style='width:10px'></td>
                <td style='width:500px'>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= $bulanlalu; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$ii],2)."</td>";
                }            
                $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya]['sd'],2)."</td>
                    <td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
        }
        $stream.="</table>";            
    }

}

//////////////////////////////////////////////////////////////////////////HEADER

echo $stream;
*/
?>