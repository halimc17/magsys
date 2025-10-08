<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/kebun_2pemeliharaan.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php

$kodekegiatan=checkPostGet('kodekegiatan','');
$kodeorg=checkPostGet('kodeorg','');
$bulan=checkPostGet('bulan','');
$type=checkPostGet('type','');

    // kamus kegiatan
    $str="select kodekegiatan, namakegiatan, satuan
        from ".$dbname.".setup_kegiatan where kodekegiatan='".$kodekegiatan."'
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $namaKeg=$bar->namakegiatan;
        $satuKeg=$bar->satuan;
    }

    // kamus barang
    $str="select kodebarang, namabarang, satuan
        from ".$dbname.".log_5masterbarang
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $namabarang[$bar->kodebarang]=$bar->namabarang;
        $satuanbarang[$bar->kodebarang]=$bar->satuan;
    }
    

//=================================================
//echo"<fieldset><legend>Print Excel</legend>
//     <img onclick=\"detailExcel(event,'kebun_slave_2pemeliharaan1_detail.php?type=excel&tanggal=".$kodekegiatan."&kodeorg=".$kodeorg."&bulan=".$bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
//     </fieldset>"; 
    

    
if($type!='excel')$stream="<fieldset><legend>BKM</legend><table class=sortable border=0 cellspacing=1>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>".$_SESSION['lang']['notransaksi']."</td>
          <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
          <td align=center>".$_SESSION['lang']['kodeblok']."</td>
          <td align=center>".$_SESSION['lang']['tanggal']."</td>
          <td align=center>".$_SESSION['lang']['jhk']."</td>
          <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
          <td align=center>".$_SESSION['lang']['satuan']."</td>
          <td align=center>Output</td>
          <td align=center>".$_SESSION['lang']['namabarang']."</td>
          <td align=center>".$_SESSION['lang']['jumlah']."</td>
          <td align=center>".$_SESSION['lang']['satuan']."</td>
          ";
        $stream.="</tr>
      </thead>
      <tbody>";
//    $str="select a.notransaksi, a.hasilkerja, a.jumlahhk, a.tanggal, a.jumlahhk, a.hasilkerja
//        from ".$dbname.".kebun_perawatan_vw a
//        where a.kodekegiatan = '".$kodekegiatan."' and a.kodeorg = '".$kodeorg."' and a.tanggal like '".$bulan."%'";   
    $str="select a.notransaksi, a.hasilkerja, a.jumlahhk, a.tanggal, a.jumlahhk, a.hasilkerja,
        b.kodebarang, b.kwantitas
        from ".$dbname.".kebun_perawatan_vw a
        left join ".$dbname.".kebun_pakaimaterial b on a.notransaksi=b.notransaksi
        where a.kodekegiatan = '".$kodekegiatan."' and a.kodeorg = '".$kodeorg."' and a.tanggal like '".$bulan."%'";   
//    echo $str;
    $notem='';
    $res=mysql_query($str); 
    while($bar= mysql_fetch_object($res))
    {
        $qwebar='';
        if($bar->kwantitas)$qwebar=number_format($bar->kwantitas,3);
        if($notem!=$bar->notransaksi){
            $notem=$bar->notransaksi;
            @$oput=$bar->hasilkerja/$bar->jumlahhk;
			setIt($namabarang[$bar->kodebarang],'');
			setIt($satuanbarang[$bar->kodebarang],'');
            $stream.="<tr class=rowcontent>
                <td>".$bar->notransaksi."</td>    
                <td>".$namaKeg."</td>     
                <td>".$kodeorg."</td>    
                <td>".tanggalnormal($bar->tanggal)."</td>    
                <td align=right>".number_format($bar->jumlahhk,2)."</td>    
                <td align=right>".number_format($bar->hasilkerja,2)."</td>    
                <td>".$satuKeg."</td>    
                <td align=right>".number_format($oput,2)." ".$satuKeg."/HK</td> 
                <td>".$namabarang[$bar->kodebarang]."</td>    
                <td align=right>".$qwebar."</td>    
                <td>".$satuanbarang[$bar->kodebarang]."</td>";
			
			setIt($jumlahhk,0);
			setIt($hasilkerja,0);
			setIt($bar->jumlahhk,0);
			setIt($bar->hasilkerja,0);
            $jumlahhk+=$bar->jumlahhk;
            $hasilkerja+=$bar->hasilkerja;
        }else{
            $stream.="<tr class=rowcontent>
                <td></td>    
                <td></td>    
                <td></td>    
                <td></td>    
                <td align=right></td>    
                <td align=right></td>    
                <td></td>    
                <td align=right></td>
                <td>".$namabarang[$bar->kodebarang]."</td>    
                <td align=right>".$qwebar."</td>    
                <td>".$satuanbarang[$bar->kodebarang]."</td>";             
        }
        $stream.="</tr>";        
    } 
    
    @$oput=$hasilkerja/$jumlahhk;
    $stream.="<tr class=rowcontent>
            <td colspan=4 align=center>Total</td>    
            <td align=right>".number_format($jumlahhk,2)."</td>    
            <td align=right>".number_format($hasilkerja,2)."</td>    
            <td>".$satuKeg."</td>    
            <td align=right>".number_format($oput,2)." ".$satuKeg."/HK</td>    
            <td colspan=3></td>    
        </tr>";
    
   $stream.="</tbody></table></fieldset>";
   
   
   
   
   
   
##dari BA   
$stream.="<br><br><fieldset><legend>BA SPK</legend>";   
if($type!='excel')$stream.="<table class=sortable border=0 cellspacing=1>"; else
$stream.="<br><br><table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>".$_SESSION['lang']['notransaksi']."</td>
          <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
          <td align=center>".$_SESSION['lang']['kodeblok']."</td>
          <td align=center>".$_SESSION['lang']['tanggal']."</td>
          <td align=center>".$_SESSION['lang']['jhk']."</td>
          <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
          <td align=center>".$_SESSION['lang']['satuan']."</td>
          <td align=center>Output</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
    ##dari Ba spk
    $iBa="select * from ".$dbname.".log_baspk  where "
            . " kodekegiatan = '".$kodekegiatan."' "
            . " and kodeblok = '".$kodeorg."' "
            . " and tanggal like '".$bulan."%' ";
    $nBa=  mysql_query($iBa) or die (mysql_error($conn));
    while($dBa=  mysql_fetch_assoc($nBa))
    {
     $stream.="<tr class=rowcontent>
        <td>".$dBa['notransaksi']."</td> 
        <td>".$namaKeg."</td>  
        <td>".$dBa['kodeblok']."</td>     
        <td>".tanggalnormal($dBa['tanggal'])."</td> 
        <td align=right>".number_format($dBa['hkrealisasi'],2)."</td> 
        <td align=right>".number_format($dBa['hasilkerjarealisasi'],2)."</td> 
        <td>".$satuKeg."</td> 
            <td align=right>".number_format($dBa['hasilkerjarealisasi']/$dBa['hkrealisasi'],2)."  ".$satuKeg."/HK</td>   
        ";
        $totHk+=$dBa['hkrealisasi'];
        $totHsl+=$dBa['hasilkerjarealisasi'];
        $totOut+=$dBa['hasilkerjarealisasi']/$dBa['hkrealisasi'];
    }
    $stream.="<tr class=rowcontent>
                <td colspan=4 align=center>Total</td>
                <td  align=right>".number_format($totHk,2)."</td>
                <td  align=right>".number_format($totHsl,2)."</td>
                <td>".$satuKeg."</td>
                <td  align=right>".number_format($totOut,2)." ".$satuKeg."/HK</td>
    ";
    $stream.="</tbody></table>";
    $stream.="</fieldset>";
    
    
$stream.="<br><br><fieldset><legend>Grand Total</legend>";   
if($type!='excel')$stream.="<table class=sortable border=0 cellspacing=1>"; else
$stream.="<br><br><table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
          <td align=center>".$_SESSION['lang']['jhk']."</td>
          <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
          <td align=center>".$_SESSION['lang']['satuan']."</td>
          <td align=center>Output</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
$stream.="<tr class=rowcontent>
        <td>".$namaKeg."</td>
        <td  align=right>".number_format($totHk+$jumlahhk,2)."</td>
        <td  align=right>".number_format($totHsl+$hasilkerja,2)."</td>
        <td>".$satuKeg."</td>
        <td  align=right>".number_format(($totHsl+$hasilkerja)/($totHk+$jumlahhk),2)." ".$satuKeg."/HK</td>
    ";
   
   
   if($type=='excel')
   {
$nop_="Detail_pemeliharaan1_".$kodekegiatan."_".$kodeorg."_".$bulan;
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
   }
   else
   {
       echo $stream;
   }    
       
?>