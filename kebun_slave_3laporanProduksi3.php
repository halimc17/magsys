<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$periode = checkPostGet('periodetahun','');
$unit = checkPostGet('unittahun','');
$intiplasmatahun = checkPostGet('intiplasmatahun',''); 

if($intiplasmatahun!='')
{
    $inplas=" and intiplasma='".$intiplasmatahun."'";
}

#ambil  blok dan tahun tanam
$str="select kodeorg,tahuntanam,luasareaproduktif,intiplasma from ".$dbname.".setup_blok where kodeorg like '".$unit."%' ".$inplas." ";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $kodeblok[$bar->kodeorg]=$bar->kodeorg;
    $thntanam[$bar->kodeorg]=$bar->tahuntanam;
    $luas[$bar->kodeorg]=$bar->luasareaproduktif;
}
#ambil 
$str="select sum(totalkg) as kg, left(tanggal,7) as periode,blok from ".$dbname.".kebun_spb_vw where blok like '".$unit."%'
          and tanggal like '".$periode."%' group by blok,left(tanggal,7) order by left(tanggal,7),blok";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $produksi[$bar->blok][$bar->periode]=$bar->kg;
}
#ambil budget;
$str="select kodeblok,kg01,kg02,kg03,kg04,kg05,kg06,kg07,kg08,kg09,kg10,kg01,kg11,kg12 from ".$dbname.".bgt_produksi_kbn_kg_vw
          where tahunbudget=".$periode." and kodeunit='".$unit."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $budget[$bar->kodeblok][$periode."-01"]=$bar->kg01;
    $budget[$bar->kodeblok][$periode."-02"]=$bar->kg02;
    $budget[$bar->kodeblok][$periode."-03"]=$bar->kg03;
    $budget[$bar->kodeblok][$periode."-04"]=$bar->kg04;
    $budget[$bar->kodeblok][$periode."-05"]=$bar->kg05;
    $budget[$bar->kodeblok][$periode."-06"]=$bar->kg06;
    $budget[$bar->kodeblok][$periode."-07"]=$bar->kg07;
    $budget[$bar->kodeblok][$periode."-08"]=$bar->kg08;
    $budget[$bar->kodeblok][$periode."-09"]=$bar->kg09;
    $budget[$bar->kodeblok][$periode."-10"]=$bar->kg10;
    $budget[$bar->kodeblok][$periode."-11"]=$bar->kg11;
    $budget[$bar->kodeblok][$periode."-12"]=$bar->kg12;
}

if($proses=='excel')
{
    $border="border=1";
}


$stream="Estate Unit Production Trend :".$unit." Period:".$periode."
          <table class=sortable cellspacing=1 ".$border.">
           <thead>
            <tr class=rowheader>
               <td rowspan=2>No</td>
               <td rowspan=2>Blok</td>
               <td rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>
               <td rowspan=2>".$_SESSION['lang']['luas']."(Ha)</td>               
               <td colspan=4 align=center>Jan</td>
               <td colspan=4 align=center>Feb</td>
               <td colspan=4 align=center>Mar</td>
               <td colspan=4 align=center>Apr</td>
               <td colspan=4 align=center>Mei</td>
               <td colspan=4 align=center>Jun</td>
               <td colspan=4 align=center>Jul</td>
               <td colspan=4 align=center>Aug</td>
               <td colspan=4 align=center>Sep</td>
               <td colspan=4 align=center>Okt</td>
               <td colspan=4 align=center>Nop</td>
               <td colspan=4 align=center>Dec</td>
               <td colspan=4 align=center>Total</td>
            </tr>
            <tr class=rowheader>";
       
       for($i=1;$i<=13;$i++)
       {
           $stream.="       
              <td>Bgt(Kg)</td>
              <td>Real(Kg)</td>
              <td>Bgt(Kg/Ha)</td>
              <td>Real(Kg/Ha)</td>";  
       }

            
       
       $stream.="      
            </tr>
            </thead>
            <tbody>";
$no=1;
$gtluas=0;
foreach($kodeblok as $blk =>$val)
{
    $tbgts=0;
    $tps=0;
    @$tbvs=0;
    @$tpvs=0;
    $stream.="<tr class=rowcontent>
              <td>".$no."</td>
               <td>".$blk."</td>
               <td>".$thntanam[$blk]."</td>
               <td align=right>".$luas[$blk]."</td>                   
                ";
       for($x=1;$x<=12;$x++){
           $g=str_pad($x, 2, "0", STR_PAD_LEFT);
		   setIt($budget[$val][$periode."-".$g],0);
		   setIt($produksi[$val][$periode."-".$g],0);
		   setIt($tt1[$x],0);
		   setIt($tt2[$x],0);
           $stream.="<td align=right>".number_format($budget[$val][$periode."-".$g])."</td>
                     <td align=right>".number_format($produksi[$val][$periode."-".$g])."</td>
                     <td align=right>".@number_format($budget[$val][$periode."-".$g]/$luas[$val])."</td>
                     <td align=right>".@number_format($produksi[$val][$periode."-".$g]/$luas[$val])."</td>    
                       ";
           $tbgts+=$budget[$val][$periode."-".$g];
           $tps+=$produksi[$val][$periode."-".$g];
           @$tbvs+=$budget[$val][$periode."-".$g]/$luas[$val];
           @$tpvs+=$produksi[$val][$periode."-".$g]/$luas[$val];
           $tt1[$x]+=$budget[$val][$periode."-".$g];
           $tt2[$x]+=$produksi[$val][$periode."-".$g];
       }
       $stream.="<td align=right>".number_format($tbgts)."</td>
                <td align=right>".number_format($tps)."</td>
                <td align=right>".number_format($tbvs)."</td>
                <td align=right>".number_format($tpvs)."</td></tr>";
	$gtluas+=$luas[$val];    
	$no++;            
}
$gtbgt=$gtpr=0;
$stream.="<tr class=rowcontent>
        <td colspan=3>Total</td>
        <td align=right>".$gtluas."</td>";
      foreach($tt1 as $idx=>$val)
      {
          $stream.="<td align=right>".number_format($val)."</td>
                <td align=right>".number_format($tt2[$idx])."</td>
                <td align=right>".@number_format($val/$gtluas)."</td>                    
                <td align=right>".@number_format($tt2[$idx]/$gtluas)."</td>";       
          $gtbgt+=$val;
          $gtpr+=$tt2[$idx];
      }
           $stream.="<td align=right>".number_format($gtbgt)."</td>
                <td align=right>".number_format($gtpr)."</td>
                <td align=right>".@number_format($gtbgt/$gtluas)."</td>                    
                <td align=right>".@number_format($gtpr/$gtluas)."</td>";         
 $stream.="</tr>";
$stream.="</tbody><tfoot></tfoot></table>Pastikan SPB Sudah diinput keseluruhan/Make sure all FFB transport has been recorded";

switch ($proses){
        case 'preview':
                echo $stream;
            break;
         case 'excel':
            $nop_="Trend_Produksi_".$unit."_".$periode;
            if(strlen($stream)>0)
            {
                 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                 gzwrite($gztralala, $stream);
                 gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
            }
             break;
}
?>