<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

	$pt=$_GET['pt'];
	$unit=$_GET['gudang'];
	$periode=$_GET['periode'];
 //ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$namapt=strtoupper($bar->namaorganisasi);
}
#=========================================
$kodelaporan='INCOME STATEMENT';

//$periodesaldo=str_replace("-", "", $periode);
//$tahunini=substr($periodesaldo,0,4);
//
//#sekarang
//$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
//$periodCUR=date('Ym',$t);#periode saldoakhir bulan berjalan
//$kolomCUR="awal".date('m',$t);
//
//#captionsekarang============================
//$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
//$captionCUR=date('M-Y',$t);

#ambil format mesinlaporan==========
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut";
$res=mysql_query($str);

#query+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if($unit=='')
    $where=" kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else 
    $where=" kodeorg='".$unit."'";

$stream=$_SESSION['lang']['laporanrugilabaperiodik']."<br>".$pt."-".$unit."-".$periode."<br>
    <table class=sortable border=1 cellspacing=1>
          <thead>
           <tr class=rowheader>
            <td colspan=3>".$_SESSION['lang']['keterangan']."</td>
            <td align=center>".numToMonth(1, 'E')."</td>
            <td align=center>".numToMonth(2, 'E')."</td>
            <td align=center>".numToMonth(3, 'E')."</td>
            <td align=center>".numToMonth(4, 'E')."</td>
            <td align=center>".numToMonth(5, 'E')."</td>
            <td align=center>".numToMonth(6, 'E')."</td>
            <td align=center>".numToMonth(7, 'E')."</td>
            <td align=center>".numToMonth(8, 'E')."</td>
            <td align=center>".numToMonth(9, 'E')."</td>
            <td align=center>".numToMonth(10, 'E')."</td>
            <td align=center>".numToMonth(11, 'E')."</td>
            <td align=center>".numToMonth(12, 'E')."</td>
            <td align=center>YTD</td>    
            </tr>
         </thead><tbody>";
$tnow2[]=0;
$ttill2=0;
$tnow3[]=0;
$ttill3=0;

while($bar=mysql_fetch_object($res))
{
    if($bar->tipe=='Header')
      {
        if($_SESSION['language']=='ID'){
            $stream.="<tr class=rowcontent><td colspan=16><b>".$bar->keterangandisplay."</b></td></tr>"; }
        else{
            $stream.="<tr class=rowcontent><td colspan=16><b>".$bar->keterangandisplay1."</b></td></tr>";
        }
      }
    else
    {
//        #ambil saldo akhir periode barjalan sebagai akumulasi
//        $st12="select sum(".$kolomCUR.") as akumilasi
//               from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
//               and '".$bar->noakunsampai."' and  periode like '".$periode."%' and ".$where;
//        $res12=mysql_query($st12);
////echo $st12.'<br>';        
//        $akumulasi=0;
//        while($ba12=mysql_fetch_object($res12))
//        {
//            $akumulasi=$ba12->akumilasi;
//        }
        #mutasi bulan berjalan
        $akum=0;
        for ($i = 1; $i <= 12; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $st13="select sum(debet".$ii.") - sum(kredit".$ii.") as sekarang
                       from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
                       and '".$bar->noakunsampai."' and  periode like '".$periode."%' and ".$where;
                $res13=mysql_query($st13);
                $jlhsekarang[$ii]=0;
                while($ba13=mysql_fetch_object($res13))
                {
                    $jlhsekarang[$ii]=$ba13->sekarang;
                    $akum+=$ba13->sekarang;
                }
				if(!isset($tnow201[$ii])) $tnow201[$ii]=0;
				if(!isset($tnow301[$ii])) $tnow301[$ii]=0;
                $tnow201[$ii]+=$jlhsekarang[$ii];
                $tnow301[$ii]+=$jlhsekarang[$ii];
        }        
                $ttill2+=$akum;
                $ttill3+=$akum;
        if($bar->tipe=='Total'){
                if($bar->noakundari=='' or $bar->noakunsampai=='')
                {
                    if($bar->variableoutput=='2')
                    {
                                                $akum=$ttill2; 
                                                $ttill2=0;
                        for ($i = 1; $i <= 12; $i++) {
                            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                                                $jlhsekarang[$ii]=$tnow201[$ii];
                                                $tnow201[$ii]=0;
                        }                        
                    }
                    if($bar->variableoutput=='3')
                    {
                                                $akum=$ttill3; 
                                                $ttill3=0;
                        for ($i = 1; $i <= 12; $i++) {
                            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                                                $jlhsekarang[$ii]=$tnow301[$ii];
                                                $tnow301[$ii]=0;
                        }                        
                    }                                        
                }     
            $stream.="<tr class=rowcontent>
                        <td><td>
                        <td></td>
                        <td colspan=13><hr></td></tr>
                    <tr class=rowcontent>
                        <td></td>";
            if($_SESSION['language']=='ID'){
                $stream.="<td colspan=2><b>".$bar->keterangandisplay."</b></td>";}
            else{
                $stream.="<td colspan=2><b>".$bar->keterangandisplay1."</b></td>";
            }
                    for ($i = 1; $i <= 12; $i++) {
                        if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                                            $stream.="<td align=right><b>".number_format($jlhsekarang[$ii],2)."</b></td>";
                    }    
                        $stream.="<td align=right><b>".number_format($akum,2)."</b></td>    
                     </tr>
                     <tr class=rowcontent><td colspan=16>.</td></tr>
                     "; 
        }
        else
        {
            $stream.="
                    <tr class=rowcontent>
                    <td style='width:30px'></td><td style='width:30px'></td>";
            if($_SESSION['language']=='ID'){
                $stream.="<td>".$bar->keterangandisplay."</td>";}
            else{
                $stream.="<td>".$bar->keterangandisplay1."</td>";
            }
                    for ($i = 1; $i <= 12; $i++) {
                        if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                                            $stream.="<td align=right>".number_format($jlhsekarang[$ii],2)."</td>";
                    }    
                        $stream.="<td align=right>".number_format($akum,2)."</td>    
                     </tr>";             
        }   
    }   
}
$stream.= "</tbody></tfoot></tfoot></table>";
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$nop_="LaporanLabaRugiPeriodik-".$pt."-"."-".$unit."-".$periode;
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
?>