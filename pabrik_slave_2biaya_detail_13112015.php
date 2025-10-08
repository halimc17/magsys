<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

?>
	<link rel=stylesheet type=text/css href=style/generic.css>	
<?php

$kodeorg=$_GET['kodeorg'];
$per=$_GET['per'];
$noakun=$_GET['noakun'];
$tipe=$_GET['tipe'];


if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}

$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$namaorganisasi=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

echo" Print Excel : <img style=cursor:pointer; "
. " onclick=\"parent.lihatDetail('".$noakun."','".$kodeorg."','".$per."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

            $stream="<table $border class=sortable cellspacing=1>
             <thead>
                    <tr>
                          <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                          <td align=center>".$_SESSION['lang']['noakun']."</td>
                          <td align=center>".$_SESSION['lang']['namaakun']."</td> 
                          <td align=center>".$_SESSION['lang']['keterangan']."</td> 
                          <td align=center>".$_SESSION['lang']['noreferensi']."</td>  
                          <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>  
                          <td align=center>".$_SESSION['lang']['namaorganisasi']."</td>     
                          <td align=center>".$_SESSION['lang']['debet']."</td>     
                          <td align=center>".$_SESSION['lang']['kredit']."</td>         
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
            
            
         
            
$no=0;
/*if(count($dat)<1)
{
        echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{*/

    $iList="select * from ".$dbname.".keu_jurnaldt_vw where kodeblok like '%".$kodeorg."%' and periode='".$per."'"
            . " and  noakun='".$noakun."' ";
   
    $nList=  mysql_query($iList) or die (mysql_error($conn));
    while($dList=mysql_fetch_assoc($nList))
    {
        $no+=1;
        $stream.="<tr class=rowcontent>
              <td align=center>".$no."</td>
              <td align=left>".tanggalnormal($dList['tanggal'])."</td>        
              <td align=right>".$dList['noakun']."</td>   
              <td align=left>".$namaakun[$dList['noakun']]."</td>     
              <td align=left>".$dList['keterangan']."</td>     
              <td align=left>".$dList['noreferensi']."</td>         
              <td align=left>".$dList['kodeblok']."</td> 
              <td align=left>".$namaorganisasi[$dList['kodeblok']]."</td>    
              <td align=right>".number_format($dList['debet'],2)."</td>       
              <td align=right>".number_format($dList['kredit'],2)."</td>        
             </tr>"; 
        $totdb+=$dList['debet'];
        $totkr+=$dList['kredit'];
    }
    $stream.="<tr class=rowcontent>
                <td colspan=8 align=right>".$_SESSION['lang']['total']."</td>
                <td align=right>".number_format($totdb,2)."</td>    
                <td align=right>".number_format($totkr,2)."</td>        
    ";
          
  
if($tipe=='excel')
{
    echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi".$kodeorg._.$noakun._.$per;
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