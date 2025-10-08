<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$_POST['kdUnit1']==''?$kodeOrg=$_GET['kdUnit1']:$kodeOrg=$_POST['kdUnit1'];
$_POST['thnBudget1']==''?$thnBudget=$_GET['thnBudget1']:$thnBudget=$_POST['thnBudget1'];

if($thnBudget=='')
{
    exit("Error:Tahun Budget Tidak Boleh Kosong");
}	
if($kodeOrg=='')
{
    exit("Error:Kode Traksi Tidak Boleh Kosong");
}

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

//get org
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeOrg."' ";	
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
    $nmOrg=$rOrg['namaorganisasi'];
}
if(!$nmOrg)$nmOrg=$kodeOrg;
	
//get nama karyawan
$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namakar[$bar->karyawanid]=$bar->namakaryawan;
}
		
if($_GET['proses']=='excel')
{
$bg=" bgcolor=#DEDEDE";
$brdr=1;
$tab.="<table>
     <tr><td colspan=4 align=left>".$optNm[$kdTraksi]."</td></tr>   
     <tr><td colspan=4>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['byTraski']." ".$_SESSION['lang']['budgetyear'].": ".$thnBudget."</td></tr>   
     </table>";
}
else
{
    $bg="";
    $brdr=0;
}

$sdata="SELECT * FROM ".$dbname.".bgt_budget
    WHERE tipebudget = 'TRK' and tahunbudget = '".$thnBudget."' and kodeorg = '".$kodeOrg."' ";
$qdata=mysql_query($sdata) or die(mysql_error($conn));

while($rdata=mysql_fetch_assoc($qdata))
{
    $listvhc[$rdata['kodevhc']]=$rdata['kodevhc'];
    if(substr($rdata['kodebudget'],0,3)=='M-3'){ // suku cadang, minyak dan pelumas
        if(substr($rdata['kodebarang'],0,2)=='35'){ // minyak dan pelumas
            $dzdata[$rdata['kodevhc']]['bbmo']+=$rdata['rupiah'];
        }else{ // suku cadang
            $dzdata[$rdata['kodevhc']]['suku']+=$rdata['rupiah'];
        }
    }else if(substr($rdata['kodebudget'],0,3)=='SDM'){ // gaji dan premi
        if($rdata['kodebudget']=='SDM-PRE'){ // premi
            $dzdata[$rdata['kodevhc']]['prem']+=$rdata['rupiah'];
        }else{ // gaji
            $dzdata[$rdata['kodevhc']]['gaji']+=$rdata['rupiah'];
        }
    }else if($rdata['kodebudget']=='TRANSIT'){ // transit dan biaya umum
        if($rdata['noakun']=='4110206'){ // pajak dan asuransi
            $dzdata[$rdata['kodevhc']]['asur']+=$rdata['rupiah'];
        }else{ // umum
            $dzdata[$rdata['kodevhc']]['umum']+=$rdata['rupiah'];
        }
    }else if($rdata['kodebudget']=='SERVICE'){ // servis
        $dzdata[$rdata['kodevhc']]['serv']+=$rdata['rupiah'];
    }else{
        $dzdata[$rdata['kodevhc']]['lain']+=$rdata['rupiah']; // untuk trap kalau2 ada yang tidak tertangkap sama yang di atas...
    }
}

if(!empty($listvhc))sort($listvhc);

$tab.="<table cellspacing=1 cellpadding=1 border=".$brdr." class=sortable><thead>";
$tab.="<tr class=rowheader>";
$tab.="<td align=center ".$bg.">No.</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['kodevhc']."</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['gaji']."</td>";            
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['insentif']."</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['bbmoli']."</td>";
$tab.="<td align=center ".$bg.">Spareparts</td>";
$tab.="<td align=center ".$bg.">Service</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['pajak']."/".$_SESSION['lang']['asuransi']."</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['biayaumum']."</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['lain']."</td>";
$tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td></tr></thead><tbody>";

$no=0;

if(!empty($listvhc))foreach($listvhc as $vhc){
    $no+=1;
    $tab.="<tr class=rowcontent>";
    $tab.="<td>".$no."</td>";
    $tab.="<td>".$vhc."</td>";
    $tab.="<td align=right>".number_format($dzdata[$vhc]['gaji'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['prem'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['bbmo'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['suku'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['serv'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['asur'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['umum'])."</td>";                
    $tab.="<td align=right>".number_format($dzdata[$vhc]['lain'])."</td>";                
    $subtotal=$dzdata[$vhc]['gaji']+$dzdata[$vhc]['prem']+$dzdata[$vhc]['bbmo']+$dzdata[$vhc]['suku']+$dzdata[$vhc]['serv']+$dzdata[$vhc]['asur']+$dzdata[$vhc]['umum']+$dzdata[$vhc]['lain'];
    $tab.="<td align=right>".number_format($subtotal)."</td>";                
    $tab.="</tr>";    
    $total+=$subtotal;
}else{
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=center colspan=11>".$_SESSION['lang']['dataempty']."</td>";
    $tab.="</tr>";    
}
		
$tab.="</tbody><thead><tr class=rowheader  bgcolor=#DEDEDE>";
$tab.="<td align=center colspan=10>".$_SESSION['lang']['total']."</td>";
$tab.="<td align=right>".number_format($total)."</td>";
$tab.="</tr>";
$tab.="</thead></table>";

switch($proses)
{
    case'preview':
        echo $tab;
    break;
    case'excel':
    $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="RekaplaporanBiayaKendaran_".$dte;
    if(strlen($tab)>0)
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
     if(!fwrite($handle,$tab))
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
   // closedir($handle);
    }
    break;



default;
    break;
	
	
}    
?>			
			
           