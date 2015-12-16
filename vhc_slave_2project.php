<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
	$proses=$_POST['proses'];
}
else
{
	$proses=$_GET['proses'];
}

$optSupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
$optNmOrg=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');

$kdOrg = checkPostGet('kdOrg','');
$thnId = checkPostGet('thnId','');
$kdProj = checkPostGet('kdProj','');

///$unitId=$_SESSION['lang']['all'];
$dktlmpk=$_SESSION['lang']['all'];

if($proses=='preview'||$proses=='excel')
{


$brdr=0;
$bgcoloraja=$tab='';
if($proses=='excel')
{
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $tab.="
    <table>
    <tr><td colspan=7 align=left><b><font size=5>Laporan Project</font></b></td></tr>
    <tr><td colspan=7 align=left>".$_SESSION['lang']['unit']." : ".$optNmOrg[$kdOrg]."</td></tr>
    <tr><td colspan=7 align=left>".$_SESSION['lang']['tahun']." : ".$thnId."</td></tr>
    </table>";
}

	$tab.="<table cellspacing=1 border=".$brdr." class=sortable>
	<thead class=rowheader>
	<tr>
        <td ".$bgcoloraja." align=center>No.</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['kodeorg']."</td>
        <td ".$bgcoloraja." align=center>Project Code</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nama']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggalmulai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggalselesai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['biaya']."</td></tr>";
        $tab.="</tr></thead><tbody>";
        $sData="select distinct * from ".$dbname.".project
        where substr(tanggalmulai,1,4)='".$thnId."' and kodeorg='".$kdOrg."'";
        $qData=mysql_query($sData) or die(mysql_error($conn));
		$nor=0;
        while($rData=  mysql_fetch_assoc($qData))
        {
           $nor+=1;
           $sBiaya="select distinct sum(jumlah) as biaya from ".$dbname.".keu_jurnaldt
                   where kodeasset='".$rData['kode']."'";
           $qBiaya=mysql_query($sBiaya) or die(mysql_error($conn));
           $rBiaya=mysql_fetch_assoc($qBiaya);
           $tab.="<tr class=rowcontent style='cursor:pointer;' onclick=getDetail('".$rData['kode']."')><td align=center>".$nor."</td>";
           $tab.="<td>".$rData['kodeorg']."</td>";
           $tab.="<td>".$rData['kode']."</td>";
           $tab.="<td>".$rData['nama']."</td>";
           $tab.="<td>".$rData['tanggalmulai']."</td>";
            $tab.="<td>".$rData['tanggalselesai']."</td>";
           $tab.="<td  align=right>".number_format($rBiaya['biaya'],2)."</td>";
           $tbiaya+=$rBiaya['biaya'];
        }
        $tab.="<tr class=rowcontent><td  colspan=6 align=center><b>".$_SESSION['lang']['total']."</b></td>";
        $tab.="<td align=right><b>".number_format($tbiaya,2)."</b></td></tr>";
        $tab.="</tbody></table>";
        
}
        
switch($proses)
{
    
    
    
    
	case'getPt':
	//echo "warning:masuk";
	$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select distinct kodeorg  from ".$dbname.".log_po_vw where substr(tanggal,1,7)='".$periode."'";
        //exit("Error:".$sOrg);
	$qOrg=mysql_query($sOrg) or die(mysql_error());
	while($rOrg=mysql_fetch_assoc($qOrg))
	{
		$optorg.="<option value=".$rOrg['kodeorg'].">".$optNmOrg[$rOrg['kodeorg']]."</option>";
	}
	echo $optorg;
	break;
        
	case'preview':
	echo $tab;
	break;
    
    case'excel':

        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="Laporan_Project_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";
			
	break;
       
        case'getDetail':
            $shead="select distinct nama from ".$dbname.".project
                   where kode='".$kdProj."'";
            $qhead=mysql_query($shead) or die(mysql_error($conn));
            $rhead=mysql_fetch_assoc($qhead);
            $tab.="<table cellpadding=1 cellspacing=1 border=0><tr><td>Kode Project</td><td>:</td>";
            $tab.="<td>".$kdProj."</td></tr>";
            $tab.="<tr><td>Nama Project</td><td>:</td><td>".$rhead['nama']."</td></tr></table>";
            $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead><tr>";
            $tab.="<button class=mybutton onclick=fisikKeExcel(event,'vhc_slave_2project.php','".$kdProj."')>".$_SESSION['lang']['excel']."</button>";
            $tab.="<td align=center>No.</td>";
            $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['nojurnal']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['noakun']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['keterangan']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['noreferensi']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['debet']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['kredit']."</td></tr></thead><tbody>";
            
            $sDetail="select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun
                     from ".$dbname.".keu_jurnaldt_vw where kodeasset='".$kdProj."' and noakun like '12813%'";
            $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
            $row=mysql_num_rows($qDetail);
            
            if($row!=0)
            {
                while($rDetail=  mysql_fetch_assoc($qDetail))
                {
                    $nor+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$nor."</td>";
                    $tab.="<td align=left>".$rDetail['tanggal']."</td>";
                    $tab.="<td align=left>".$rDetail['nojurnal']."</td>";
                    $tab.="<td align=left>".$rDetail['noakun']."</td>";
                    $tab.="<td align=left>".$rDetail['keterangan']."</td>";
                    $tab.="<td align=left>".$rDetail['noreferensi']."</td>";
                    $tab.="<td align=right>".number_format($rDetail['debet'],2)."</td>";
                    $tab.="<td align=right>".number_format($rDetail['kredit'],2)."</td></tr>";  
                    $tdb+=$rDetail['debet'];
                    $tkr+=$rDetail['kredit'];
                }
                $tab.="<tr class=rowcontent>";
                $tab.="<td colspan=6 align=center><b>".$_SESSION['lang']['total']."</b></td>";
                $tab.="<td align=right><b>".number_format($tdb,2)."</td>";
                $tab.="<td align=right><b>".number_format($tkr,2)."</td>";
                $tab.="</tr>";
            }
            else
            {
                $tab.="<tr class=rowcontent><td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
            }
            
            /*$sDetail="select distinct keterangan,noreferensi,nojurnal,tanggal
                     from ".$dbname.".keu_jurnaldt where kodeasset='".$kdProj."'";
            //exit("Error:".$sDetail);
            $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
            $row=mysql_num_rows($qDetail);
            if($row!=0)
            {
                while($rDetail=  mysql_fetch_assoc($qDetail))
                {
                    $nor+=1;
                    $sMin="select distinct sum(jumlah) as debet from ".$dbname.".keu_jurnaldt
                          where nojurnal='".$rDetail['nojurnal']."' and jumlah<0";
                    $qMin=mysql_query($sMin) or die(mysql_error($conn));
                    $rMin=mysql_fetch_assoc($qMin);

                    $sPlus="select distinct sum(jumlah) as kredit from ".$dbname.".keu_jurnaldt
                          where nojurnal='".$rDetail['nojurnal']."' and jumlah>0";
                    $qPlus=mysql_query($sPlus) or die(mysql_error($conn));
                    $rPlus=mysql_fetch_assoc($qPlus);

                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$nor."</td>";
                    $tab.="<td>".$rDetail['tanggal']."</td>";
                    $tab.="<td>".$rDetail['nojurnal']."</td>";
                    $tab.="<td>".$rDetail['keterangan']."</td>";
                    $tab.="<td>".$rDetail['noreferensi']."</td>";
                    $tab.="<td>".$rMin['debet']."</td>";
                    $tab.="<td>".$rPlus['kredit']."</td></tr>";
                }
            }
            else
            {
                $tab.="<tr class=rowcontent><td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
            }*/
            $tab.="<tr><td colspan=7><button class=mybutton onclick=kembaliAja()>Back</button></td></tr>";
            $tab.="</tbody></table>";
           
            echo $tab;
        break;
        
        
        
        case'getdetailexcel':
            $shead="select distinct nama from ".$dbname.".project
                   where kode='".$kdProj."'";
            $qhead=mysql_query($shead) or die(mysql_error($conn));
            $rhead=mysql_fetch_assoc($qhead);
            $tab.="<table cellpadding=1 cellspacing=1 border=1><tr><td>Kode Project</td><td>:</td>";
            $tab.="<td>".$kdProj."</td></tr>";
            $tab.="<tr><td>Nama Project</td><td>:</td><td>".$rhead['nama']."</td></tr></table>";
            $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead><tr>";
            $tab.="<td align=center>No.</td>";
            $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['nojurnal']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['noakun']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['keterangan']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['noreferensi']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['debet']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['kredit']."</td></tr></thead><tbody>";
            
            $sDetail="select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun
                     from ".$dbname.".keu_jurnaldt_vw where kodeasset='".$kdProj."'  and noakun like '12813%'";
            $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
            $row=mysql_num_rows($qDetail);
            
            if($row!=0)
            {
                while($rDetail=  mysql_fetch_assoc($qDetail))
                {
                    $nor+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$nor."</td>";
                    $tab.="<td align=left>".$rDetail['tanggal']."</td>";
                    $tab.="<td align=left>".$rDetail['nojurnal']."</td>";
                    $tab.="<td align=left>".$rDetail['noakun']."</td>";
                    $tab.="<td align=left>".$rDetail['keterangan']."</td>";
                    $tab.="<td align=left>".$rDetail['noreferensi']."</td>";
                    $tab.="<td align=right>".number_format($rDetail['debet'],2)."</td>";
                    $tab.="<td align=right>".number_format($rDetail['kredit'],2)."</td></tr>";  
                    $tdb+=$rDetail['debet'];
                    $tkr+=$rDetail['kredit'];
                }
                $tab.="<tr class=rowcontent>";
                $tab.="<td colspan=6 align=center><b>".$_SESSION['lang']['total']."</b></td>";
                $tab.="<td align=right><b>".number_format($tdb,2)."</td>";
                $tab.="<td align=right><b>".number_format($tkr,2)."</td>";
                $tab.="</tr>";
            }
            else
            {
                $tab.="<tr class=rowcontent><td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
            }
            $tab.="</tbody></table>";
           
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
            $nop_="detail_transaksi".$kdProj;
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
                fclose($handle);
            }
            
        break;
	
	default:
	break;
}
?>