<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_GET['pt'];
$unit=$_GET['gudang'];
$periode=$_GET['periode'];

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
if($bulanlalu=='00')$bulanlalu='12';
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

//title table
for ($i = $bulan; $i >= 1; $i--) {
    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
    $t=mktime(0,0,0,$i,15,$tahun);
    $kolom[$ii]=date('M-Y',$t);
}
$t=mktime(0,0,0,$bulan,15,$tahun);
$kolom['sd']='to '.date('M-Y',$t);

//involving units
if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" ='".$unit."'";

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
    $st12="select noakun,sum(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode order by periode";
    $res12=mysql_query($st12);
    while($ba12=mysql_fetch_object($res12)){
        $daftar[$ba12->noakun]=$ba12->noakun;
        $emaknya[$ba12->noakun]=$bar->nourut;
        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
        if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
        if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
        if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
        if($bulan>=$ba12->bulan){
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
    if(!empty($daftar))sort($daftar);
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
                $excepUrut[$bar->nourut]=$bar->nourut;//array untuk menghindari mensubtotal kembali di bawah
            break;
            
            case'213302':
                $excepUrut[$bar->nourut]=$bar->nourut;
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

$stream=$kodelaporan." ".$pt." ".$unit." ".$periode;
$stream.="<table class=sortable border=1 cellspacing=0>
          <thead>
          <tr class=rowheader><td align=center colspan=3 rowspan=2>Description</td>";
          for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=center rowspan=2>".$kolom[$ii]."</td>";    
           }
$stream.="<td align=center rowspan=2>".$kolom['sd']."</td><td align=center colspan=2>Increase/Decrease</td></tr>
           <tr class=rowheader><td align=center>Rupiah</td><td align=center>%</td></tr></thead><tbody>";
$st12="select noakun, namaakun, namaakun1 from ".$dbname.".keu_5akun where level=5";
$res12=mysql_query($st12);
while($ba12=mysql_fetch_object($res12)){
    if($_SESSION['language']=='ID'){
        $akun[$ba12->noakun]=$ba12->namaakun;}
    else{
        $akun[$ba12->noakun]=$ba12->namaakun1;
    }
}  
$subtotal['sd']=0;
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
    if($data['tipe']=='Header')
    {
        $totallagi=0;        
    }
    if($data['tipe']=='Detail'){
        // subtotal
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
            if(!isset($subtotal[$ii])) $subtotal[$ii]=0;
            $subtotal[$ii] += isset($data[$ii])? $data[$ii]: 0;
        }
        $subtotal['sd'] += isset($data['sd'])? $data['sd']: 0;
        $totallagi=0;
    }
    if($data['tipe']=='Total'){
        if($totallagi==1){
            // if(!empty($adaemaknya))foreach($adaemaknya as $ada){
            //     for ($i = 1; $i <= $bulan; $i++) {
            //         if($i<10)$ii='0'.$i; else $ii=$i;
            //         if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
            //         if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']][$ii]+=isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
            //     }
            //     if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            //     if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']]['sd']+=isset($dzArr[$ada]['sd'])? $dzArr[$ada]['sd']: 0;
            // }
        }else{
            if(!empty($excepUrut[$data['nourut']])){
                continue;
            }
            /*for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                $dzArr[$data['nourut']][$ii] += isset($subtotal[$ii])? $subtotal[$ii]: 0;
                $subtotal[$ii]=0;            
            } 
            if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            $dzArr[$data['nourut']]['sd'] += isset($subtotal['sd'])? $subtotal['sd']: 0;*/
        }
        $subtotal['sd']=0;
        
        $totallagi=1;        
    }

}


//ambil format mesinlaporan
if(!empty($dzArr))foreach($dzArr as $data){
    if($data['tipe']=='Header')
    {
        $stream.="<tr class=rowcontent>
            <td colspan=".($bulan+6)."><b>".$data['keterangan']."</b></td>
        </tr>"; 
    }
    else
    if($data['tipe']=='Total'){
        @$subtotalPER=($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu])/$dzArr[$data['nourut']][$bulanlalu]*100;
        $stream.="<tr class=rowcontent>
            <td></td>
            <td></td>
            <td><b>".$data['keterangan']."</b></td>
            </td>";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']][$ii],2)."</b></td>";                
            }
            $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']]['sd'],2)."</b>
                <td align=right><b>".number_format($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu],2)."</b></td>    
            <td align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        ";
    }
    else
    if($data['tipe']=='Detail'){
        @$dataPER=($data[$bulan]-$data[$bulanlalu])/$data[$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td></td>
            <td colspan=2>".$data['keterangan']."</td>
            ";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right>".number_format(isset($data[$ii])? $data[$ii]: 0,2)."</td>";
            }
            if(!isset($data['sd'])) $data['sd']=0;
            if(!isset($data[$bulan])) $data[$bulan]=0;
            if(!isset($data[$bulanlalu])) $data[$bulanlalu]=0;
            $stream.="<td align=right>".number_format($data['sd'],2)."</td>
                <td align=right>".number_format($data[$bulan]-$data[$bulanlalu],2)."</td>    
            <td align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
//        $stream.="<tr><td colspan=".($bulan+6)."><div style=\"display:none;\" id=".$data['nourut'].">";
        if(!empty($daftar))foreach($daftar as $akunnya){
            
            if($emaknya[$akunnya]==$data['nourut']){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td></td>
                <td></td>
                <td>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= 1; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya][$ii])? $dzArr2[$akunnya][$ii]: 0,2)."</td>";
                }
                if(!isset($dzArr2[$akunnya][$bulan])) $dzArr2[$akunnya][$bulan]=0;
                if(!isset($dzArr2[$akunnya][$bulanlalu])) $dzArr2[$akunnya][$bulanlalu]=0;
                $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya]['sd'])? $dzArr2[$akunnya]['sd']: 0,2)."</td><td align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
                
            }
            
        }
//        $stream.="</div></td></tr>";

//        $stream.="</table></div></td></tr>";
    }
}
$stream.= "</tbody></tfoot></tfoot></table>";   

$nop_="Laporan Keuangan-".$pt."-".$unit."-".$periode;
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
        }
    }   
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
}



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
//    if($ba12->catatanlain=='exclude')$nilai[$komoditi][$ba12->bulan]+=$ba12->nilai; else $nilai[$komoditi][$ba12->bulan]+=($ba12->nilai/1.1);
//    @$hargajual[$komoditi][$ba12->bulan]=$nilai[$komoditi][$ba12->bulan]/$fisik[$komoditi][$ba12->bulan];
    if($bulan>=$ba12->bulan){
        $fisikjual[$komoditi]['sd']+=$ba12->fisik;
//        if($ba12->catatanlain=='exclude')$nilai[$komoditi]['sd']+=$ba12->nilai; else $nilai[$komoditi]['sd']+=($ba12->nilai/1.1);
//        @$hargajual[$komoditi]['sd']=$nilai[$komoditi]['sd']/$fisik[$komoditi]['sd'];
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
    if(!empty($qwe))foreach($qwe as $rty){
        if((intval($rty)!=0)){
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
    $res12=mysql_query($st12);
    while($ba12=mysql_fetch_object($res12))
    {
        $daftar[$ba12->noakun]=$ba12->noakun;
        $emaknya[$ba12->noakun]=$bar->nourut;
		if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
		if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
		if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
		if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
        if($bulan>=$ba12->bulan){
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
        if(!empty($daftar))sort($daftar);
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
    
$stream=$kodelaporan." ".$pt." ".$unit." ".$periode;
$stream.="<table class=sortable border=1 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td align=center colspan=3 rowspan=2>Description</td>
            ";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=center rowspan=2>".$kolom[$ii]."</td>";    
            }
            $stream.="<td align=center rowspan=2>".$kolom['sd']."</td>
                <td align=center colspan=2>Increase/Decrease</td>    
        </tr>
        <tr class=rowheader>
            <td align=center>Rupiah</td>
            <td align=center>%</td>
        </tr>
    </thead><tbody>";

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

$noakunproduksicpo='213101';
$noakunproduksiker='213102';

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    $j=$i-1;
    if($j<10)$jj='0'.$j; else $jj=$j;
	
	$fisikawal['CPO'][$ii] = $fisikawal['KER'][$ii] = 0;
	$fisikprod['CPO'][$ii] = $fisikprod['KER'][$ii] = 0;
	$fisikstok['CPO'][$ii] = $fisikstok['KER'][$ii] = 0;
	$fisikjual['CPO'][$ii] = $fisikjual['KER'][$ii] = 0;
	
    if($i>1){
        $fisikawal['CPO'][$ii]=$fisikakhir['CPO'][$jj];
        $fisikawal['KER'][$ii]=$fisikakhir['KER'][$jj];
    }else{ // bulan1, harga dan fisik udah ada
        
    }   
    $fisikstok['CPO'][$ii] =$fisikawal['CPO'][$ii]+$fisikprod['CPO'][$ii];
    $fisikakhir['CPO'][$ii]=$fisikstok['CPO'][$ii]-$fisikjual['CPO'][$ii];
    $fisikstok['KER'][$ii] =$fisikawal['KER'][$ii]+$fisikprod['KER'][$ii];
    $fisikakhir['KER'][$ii]=$fisikstok['KER'][$ii]-$fisikjual['KER'][$ii];
}
//$fisikstok['CPO']['sd']=$fisikawal['CPO']['sd']+$fisikprod['CPO']['sd'];
$fisikstok['CPO']['sd']=$fisikprod['CPO']['sd'];
$fisikakhir['CPO']['sd']=$fisikakhir['CPO'][$bulan];
//$fisikstok['KER']['sd']=$fisikawal['KER']['sd']+$fisikprod['KER']['sd'];
$fisikstok['KER']['sd']=$fisikprod['KER']['sd'];
$fisikakhir['KER']['sd']=$fisikakhir['KER'][$bulan];

$subtotal['sd']=0;
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
                
				if(!isset($nilaistok['CPO']['sd'])) $nilaistok['CPO']['sd']=0;
				if(!isset($nilaiprod['CPO']['sd'])) $nilaiprod['CPO']['sd']=0;
				if(!isset($nilaijual['CPO']['sd'])) $nilaijual['CPO']['sd']=0;
                $nilaistok['CPO']['sd']+=$nilaistok['CPO'][$ii];
                $nilaiprod['CPO']['sd']+=$nilaiprod['CPO'][$ii];
                $nilaijual['CPO']['sd']+=$nilaijual['CPO'][$ii];
            }
            $nilaiawal['CPO']['sd']=$nilaiawal['CPO']['01'];            
            @$hargastok['CPO']['sd']=$nilaistok['CPO']['sd']/$fisikstok['CPO']['sd'];                
            $nilaiakhir['CPO']['sd']=$nilaiakhir['CPO'][$bulan];   
//            $nilaijual['CPO']['sd']=$nilaistok['CPO']['sd']-$nilaiakhir['CPO']['sd'];
            
            $dzArr['213201']['sd']=$nilaiawal['CPO']['sd'];
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
                
				if(!isset($nilaistok['KER']['sd'])) $nilaistok['KER']['sd']=0;
				if(!isset($nilaiprod['KER']['sd'])) $nilaiprod['KER']['sd']=0;
				if(!isset($nilaijual['KER']['sd'])) $nilaijual['KER']['sd']=0;
                $nilaistok['KER']['sd']+=$nilaistok['KER'][$ii];
                $nilaiprod['KER']['sd']+=$nilaiprod['KER'][$ii];
                $nilaijual['KER']['sd']+=$nilaijual['KER'][$ii];
            }
            $nilaiawal['KER']['sd']=$nilaiawal['KER']['01'];            
            @$hargastok['KER']['sd']=$nilaistok['KER']['sd']/$fisikstok['KER']['sd'];                
            $nilaiakhir['KER']['sd']=$nilaiakhir['KER'][$bulan];   
//            $nilaijual['KER']['sd']=$nilaistok['KER']['sd']-$nilaiakhir['KER']['sd'];
            
            $dzArr['213202']['sd']=$nilaiawal['KER']['sd'];                       
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
        
        if($data['nourut']=='213302'){ // barang siap dijual KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213302'][$ii]=$nilaistok['KER'][$ii];
            }
            $dzArr['213302']['sd']=$nilaistok['KER']['sd'];             
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
        $subtotal['sd'] += isset($data['sd'])? $data['sd']: 0;
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
        if($data['nourut']=='213509'){ // JUMLAH BEBAN POKOK PENJUALAN
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['213509'][$ii]=$dzArr['213501'][$ii]+$dzArr['213502'][$ii];
            }
            $dzArr['213509']['sd']=$dzArr['213501']['sd']+$dzArr['213502']['sd'];
        }
//        if($data['nourut']=='213999'){ // LABA(RUGI) KOTOR
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArr['213999'][$ii]=$dzArr['100009'][$ii]+$dzArr['213509'][$ii];
//            }
//            $dzArr['213999']['sd']=$dzArr['100009']['sd']+$dzArr['213509']['sd'];
//        }
        
        
        
        
        
        if($data['nourut']=='214999'){ // LABA(RUGI) USAHA
            $totallagi=0;
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArr['214999'][$ii]=$dzArr['213999'][$ii]+$dzArr['214009'][$ii];
            }
            $dzArr['214999']['sd']=$dzArr['213999']['sd']+$dzArr['214009']['sd'];
        }
        if($data['nourut']=='215999'){ // LABA (RUGI) SEBELUM PAJAK
            $totallagi=1;
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArr['215999'][$ii]=$dzArr['214999'][$ii]+$dzArr['215001'][$ii];
//            }
//            $dzArr['215999']['sd']=$dzArr['214999']['sd']+$dzArr['215001']['sd'];
        }
        if($data['nourut']=='216999'){ // LABA (RUGI) BERSIH
            $totallagi=1;
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArr['216999'][$ii]=$dzArr['215999'][$ii]+$dzArr['216001'][$ii];
//            }
//            $dzArr['216999']['sd']=$dzArr['215999']['sd']+$dzArr['216001']['sd'];
        }
        if($totallagi==1){
            if(!empty($adaemaknya))foreach($adaemaknya as $ada){
                for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
					if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                    if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']][$ii]+=isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
                }
				if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
                if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']]['sd']+=isset($dzArr[$ada]['sd'])? $dzArr[$ada]['sd']: 0;
            }
        }else{
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
				if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                $dzArr[$data['nourut']][$ii] += isset($subtotal[$ii])? $subtotal[$ii]: 0;
                $subtotal[$ii]=0;            
            } 
            if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            $dzArr[$data['nourut']]['sd'] += isset($subtotal['sd'])? $subtotal['sd']: 0;
        }
        $subtotal['sd']=0;
        
        $totallagi=1;        
    }

}

//echo "<pre>";
//print_r($dzArr);
//echo "</pre>";
//exit;

//ambil format mesinlaporan
if(!empty($dzArr))foreach($dzArr as $data){
    if($data['tipe']=='Header')
    {
        $stream.="<tr class=rowcontent>
            <td colspan=".($bulan+6)."><b>".$data['keterangan']."</b></td>
        </tr>"; 
    }
    else
    if($data['tipe']=='Total'){
        @$subtotalPER=($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu])/$dzArr[$data['nourut']][$bulanlalu]*100;
        $stream.="<tr class=rowcontent>
            <td></td>
            <td></td>
            <td><b>".$data['keterangan']."</b></td>
            </td>";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']][$ii],2)."</b></td>";                
            }
            $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']]['sd'],2)."</b>
                <td align=right><b>".number_format($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu],2)."</b></td>    
            <td align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        ";
		if($data['nourut']=='212999'){ // BIAYA PRODUKSI SETELAH ELIMINASI
				@$dataPER=($dzArrFsk[$data['nourut']][$bulan]-$dzArrFsk[$data['nourut']][$bulanlalu])/$dzArrFsk[$data['nourut']][$bulanlalu]*100;
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td>TOTAL PRODUKSI TBS (KG)</td>
					";
					for ($i = $bulan; $i >= 1; $i--) {
						if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
						$stream.="<td align=right>".number_format($dzArrFsk[$data['nourut']][$ii],2)."</td>";
					}            
					$stream.="<td align=right>".number_format($dzArrFsk[$data['nourut']]['sd'],2)."</td>
						<td align=right>".number_format($dzArrFsk[$data['nourut']][$bulan]-$dzArrFsk[$data['nourut']][$bulanlalu],2)."</td>    
					<td align=right>".number_format($dataPER,2)."</td>    
				</tr>";              
				@$dataPER=($dzArrFskKrm[$data['nourut']][$bulan]-$dzArrFskKrm[$data['nourut']][$bulanlalu])/$dzArrFskKrm[$data['nourut']][$bulanlalu]*100;
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td>TOTAL TBS KE EKSTERNAL (KG)</td>
					";
					for ($i = $bulan; $i >= 1; $i--) {
						if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
						$stream.="<td align=right>".number_format($dzArrFskKrm[$data['nourut']][$ii],2)."</td>";
					}            
					$stream.="<td align=right>".number_format($dzArrFskKrm[$data['nourut']]['sd'],2)."</td>
						<td align=right>".number_format($dzArrFskKrm[$data['nourut']][$bulan]-$dzArrFskKrm[$data['nourut']][$bulanlalu],2)."</td>    
					<td align=right>".number_format($dataPER,2)."</td>    
				</tr>"; 
				@$dataPER=(($dzArr[$data['nourut']][$bulan]/$dzArrFsk[$data['nourut']][$bulan])*$dzArrFskKrm[$data['nourut']][$bulan])/(($dzArr[$data['nourut']][$bulanlalu]/$dzArrFsk[$data['nourut']][$bulanlalu])*$dzArrFskKrm[$data['nourut']][$bulanlalu])*100;
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td>HPP PENJUALAN TBS (RP)</td>
					";
					for ($i = $bulan; $i >= 1; $i--) {
						if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
						$stream.="<td align=right>".number_format(($dzArr[$data['nourut']][$ii]/$dzArrFsk[$data['nourut']][$ii]*$dzArrFskKrm[$data['nourut']][$ii]),2)."</td>";
						$dzArrsma[$data['nourut']]['sd']+=($dzArr[$data['nourut']][$ii]/$dzArrFsk[$data['nourut']][$ii]*$dzArrFskKrm[$data['nourut']][$ii]);
					}            
					$stream.="<td align=right>".number_format($dzArrsma[$data['nourut']]['sd'],2)."</td>
						<td align=right>".number_format((($dzArr[$data['nourut']][$bulan]/$dzArrFsk[$data['nourut']][$bulan])*$dzArrFskKrm[$data['nourut']][$bulan])-(($dzArr[$data['nourut']][$bulanlalu]/$dzArrFsk[$data['nourut']][$bulanlalu])*$dzArrFskKrm[$data['nourut']][$bulanlalu]),2)."</td>    
					<td align=right>".number_format($dataPER,2)."</td>    
				</tr>";		
		}
    }
    else
    if($data['tipe']=='Detail'){
        @$dataPER=($data[$bulan]-$data[$bulanlalu])/$data[$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td></td>
            <td colspan=2>".$data['keterangan']."</td>
            ";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right>".number_format(isset($data[$ii])? $data[$ii]: 0,2)."</td>";
            }
			if(!isset($data['sd'])) $data['sd']=0;
			if(!isset($data[$bulan])) $data[$bulan]=0;
			if(!isset($data[$bulanlalu])) $data[$bulanlalu]=0;
            $stream.="<td align=right>".number_format($data['sd'],2)."</td>
                <td align=right>".number_format($data[$bulan]-$data[$bulanlalu],2)."</td>    
            <td align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
//        $stream.="<tr><td colspan=".($bulan+6)."><div style=\"display:none;\" id=".$data['nourut'].">";
        if(!empty($daftar))foreach($daftar as $akunnya){
            
            if($emaknya[$akunnya]==$data['nourut']){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td></td>
                <td></td>
                <td>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= 1; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya][$ii])? $dzArr2[$akunnya][$ii]: 0,2)."</td>";
                }
				if(!isset($dzArr2[$akunnya][$bulan])) $dzArr2[$akunnya][$bulan]=0;
				if(!isset($dzArr2[$akunnya][$bulanlalu])) $dzArr2[$akunnya][$bulanlalu]=0;
                $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya]['sd'])? $dzArr2[$akunnya]['sd']: 0,2)."</td><td align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
                
            }
            
        }
//        $stream.="</div></td></tr>";

//        $stream.="</table></div></td></tr>";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";       

$nop_="Laporan Keuangan-".$pt."-".$unit."-".$periode;
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
}*/
?>