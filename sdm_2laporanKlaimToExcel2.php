<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$kodeorg=$_GET['kodeorg'];
if($periode=='')$periode=date('Y');    

    $str2="select a.karyawanid, sum(jlhbayar) as klaim,d.namakaryawan,d.lokasitugas,
         COALESCE(ROUND(DATEDIFF('".date('Y-m-d')."',d.tanggallahir)/365.25,1),0) as umur
         from ".$dbname.".sdm_pengobatanht a 
	  left join ".$dbname.".datakaryawan d
	  on a.karyawanid=d.karyawanid 
              where a.periode like '".$periode."%'
              and d.lokasitugas like '".$kodeorg."%'
        group by a.karyawanid order by klaim desc
    ";
$stream="Laporan Ranking Biaya/Karyawan ".$periode." ".$kodeorg."
<table border=1>
<thead>
<tr>
    <td bgcolor=#dedede>Rank</td>
    <td bgcolor=#dedede>".$_SESSION['lang']['namakaryawan']."</td>
    <td bgcolor=#dedede>".$_SESSION['lang']['umur']." (yrs)</td>    
    <td bgcolor=#dedede>".$_SESSION['lang']['lokasitugas']."</td>
    <td bgcolor=#dedede>".$_SESSION['lang']['jumlah']."</td>
</tr>
</thead>
<tbody>";  
$res2=mysql_query($str2);    
$no=0;
while($bar2=mysql_fetch_object($res2))
{
    $no+=1;
    $stream.="<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar2->namakaryawan."</td>
            <td>".$bar2->umur."</td>    
            <td>".$bar2->lokasitugas."</td>
            <td align=right>".number_format($bar2->klaim)."</td>
    </tr>";	  	

}   
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="LaporanRankingBiayaperKaryawan-".$periode.$kodeorg;
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
        parent.window.alert('Cant convert to excel format');
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
