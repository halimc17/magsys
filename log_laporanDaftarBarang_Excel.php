<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kelbrg=$_GET['kelbrg'];
$gdg=$_GET['gdg'];
$txtfind=$_GET['txtcari'];
$stream="";

$str="select * from ".$dbname.".log_5masterbarang where (namabarang like '%".$txtfind."%' or 
        kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' 
        order by kodebarang";
        
$strin="select min(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(1,3) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' group by kodebarang order by kodebarang";

$strout="select max(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(5,7) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' 
group by kodebarang order by kodebarang";

$in = $out = array();
$resin=mysql_query($strin);
while($barin=mysql_fetch_object($resin)){
	$in[$barin->kodebarang]=tanggalnormal($barin->tgl);
}
$resout=mysql_query($strout);
while($barout=mysql_fetch_object($resout)){
   $out[$barout->kodebarang]=tanggalnormal($barout->tgl);
}
$no=0;
$res=mysql_query($str);    
    $stream.="
    <table border=1>
    <thead>
    <tr><td bgcolor=#DEDEDE colspan=16>".$_SESSION['lang']['laporanstok']."</td></tr>
        <tr>
          <td bgcolor=#DEDEDE align=center>No.</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodekelompok']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['materialcode']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['materialname']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['minstok']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nokartubin']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['konversi']."</td>	  
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tglmaxin']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tglmaxout']."</td>
        </tr></thead><tbody>";
	// $stream="";
    while($bar=mysql_fetch_object($res))
    {
        $no+=1;
        
        $stream.="<tr>
                      <td>".$no."</td>
                      <td>".$bar->kelompokbarang."</td>
                      <td>".$bar->kodebarang."</td>
                      <td>".$bar->namabarang."</td>
                      <td>".$bar->satuan."</td>
                      <td align=right>".$bar->minstok."</td>
                      <td>".$bar->nokartubin."</td>
                      <td>".$bar->konversi."</td>
                      <td>".(isset($in[$bar->kodebarang])? $in[$bar->kodebarang]: '')."</td>
                      <td>".(isset($out[$bar->kodebarang])? $out[$bar->kodebarang]: '')."</td>
                      </tr>";
    }
$stream.="</tbody></table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
$nop_="Daftar Barang";
/*if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls.gz",'w');
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
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
 }
closedir($handle);
}*/
if(strlen($stream)>0){
	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
	gzwrite($gztralala, $stream);
	gzclose($gztralala);
	echo "<script language=javascript1.2>
	window.location='tempExcel/".$nop_.".xls.gz';
	</script>";
}
?>
