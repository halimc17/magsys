<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');




//$proses=$_GET['proses'];

$proses = checkPostGet('proses','');
$unit = checkPostGet('unit','');
$barang = checkPostGet('barang','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');

$namaBarangCari= checkPostGet('namaBarangCari','');


if($tgl1=='--')
{
    $tgl1='';
}
if($tgl2=='--')
{
    $tgl2='';
}


//echo $tgl1;


//bgcolor=#CCCCCC border='1'

  $stream="<table cellspacing='1' class='sortable'>";
      $stream.="<thead><tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['notransaksi']."</td>   
            <td align=center>".$_SESSION['lang']['tanggal']."</td>
                
            <td align=center>".$_SESSION['lang']['gudang']."</td>
            <td align=center>".$_SESSION['lang']['alokasi']."</td>
            <td align=center>".$_SESSION['lang']['kendaraan']."</td> 
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>  
            
        </tr></thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr

$barangsort="";
if($barang!='')
{
    $barangsort="and kodebarang='".$barang."'";
}
      
$iList="SELECT * FROM ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$unit."' "
        . " and tanggal between '".$tgl1."' and '".$tgl2."' and tipetransaksi=5 ".$barangsort." ";
$nList=mysql_query($iList) or die (mysql_error($conn));	
$totaljumlah=0;
while($dList=mysql_fetch_assoc($nList))
{
    $no+=1;
    $stream.="<tr class=rowcontent>";
    $stream.="
        <td align=center>".$no."</td>
        <td align=left>".$dList['notransaksi']."</td>
        <td align=left>".tanggalnormal($dList['tanggal'])."</td>    
        
        <td align=right>".$dList['kodegudang']."</td>    
        <td align=right>".$dList['kodeblok']."</td>        
        <td align=left>".$dList['kodemesin']."</td>         
        <td align=right>".$dList['kodebarang']."</td>     
        <td align=left>".$nmBrg[$dList['kodebarang']]."</td>    
        <td align=right>".number_format($dList['jumlah'],2)."</td> 
        </tr>";			
    $totaljumlah+=$dList['jumlah'];
}
$stream.="<tr class=rowcontent>";
$stream.="<td colspan=8>".$_SESSION['lang']['total']."</td>";
$stream.="<td>".number_format($totaljumlah,2)."</td>";

$stream.="</tr>";
	
	$stream.="</tbody></table>";



  


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################
  
switch($proses)
{
    
    case'getListBarang':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>
  
                    <table id=listCariBarang >
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['kodebarang']."</td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>".$_SESSION['lang']['satuan']."</td>
                    </tr></thead>";
                        
                    if($namaBarangCari=='')
                    {
                       
                    }
                    else
                    {
                        
                        $i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
                       
                        $n=mysql_query($i) or die (mysql_error($conn));
                        while ($d=mysql_fetch_assoc($n))
                        {
                           
                            
                            $whBrg="kodebarang='".$d['kodebarang']."'";
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$dHarga['hargarata']."');\">
                                    <td>".$no."</td>
                                    <td>".$d['kodebarang']."</td>
                                    <td>".$nmBrg[$d['kodebarang']]."</td>
                                    <td>".$satBrg[$d['kodebarang']]."</td>
                                    
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
    break;
    
    
    
######HTML
	case 'preview':
            
            if($tgl1=='' || $tgl2=='' || $unit=='')
            {
                exit("Please Complate the form");
            }
            
		echo $stream;
    break;

######EXCEL	
    case 'excel':

            if($tgl1=='' || $tgl2=='' || $unit=='')
            {
                exit("Please Complate the form");
            }

            $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            $tglSkrg=date("Ymd");
            $nop_="LAPORAN_PEMAKAIAN_BARANG_".$tglSkrg;
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
            break;


	default:
	break;
}

?>