<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
   
$noakun=$_GET['noakun'];
$periode=$_GET['periode'];
$periode1=$_GET['periode1'];
$lmperiode=$_GET['lmperiode'];
$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$revisi=$_GET['revisi'];
$nmSup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
	
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namapt=strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'";
$namagudang='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil mutasi-----------------------
if($gudang=='' and $pt=='')
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan 
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.noakun='".$noakun."' and a.revisi <= '".$revisi."'";
}
else if($gudang=='' and $pt!='')
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.kodeorg in(select kodeorganisasi 
        from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)
        and a.noakun='".$noakun."' and a.revisi <= '".$revisi."'";
}
else
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.kodeorg ='".$gudang."'
        and a.noakun='".$noakun."' and a.revisi <= '".$revisi."'";   
}   

//echo $str;
//=================================================
echo"<fieldset><legend>Print Excel</legend>
     <img onclick=\"parent.detailKeExcel(event,'keu_slave_getBBDetail.php?type=excel&noakun=".$noakun."&periode=".$periode."&periode1=".$periode1."&lmperiode=".$lmperiode."&pt=".$pt."&gudang=".$gudang."&revisi=".$revisi."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     </fieldset>";
if(isset($_GET['type']) and $_GET['type']=='excel')$border=1; else $border=0;
$stream="<table class=sortable border=".$border." cellspacing=1>
    <thead>
    <tr class=rowcontent>
        <td>No</td>
        <td>No.Transaksi</td>
        <td>Tanggal</td>
        <td>No.Akun</td>
        <td>Keterangan</td>
        <td>Debet</td>
        <td>Kredit</td>
        <td>Karyawan</td>
        <td>Supplier</td>
        <td>Mesin</td>
        <td>Blok</td>
    </tr>
    </thead>
    <tbody>";
$res=mysql_query($str);
$no=0;
$tdebet=0;
$tkredit=0;
while($bar= mysql_fetch_object($res))
{
    $no+=1;
    $debet=0;
    $kredit=0;
    if($bar->jumlah>0)
         $debet= $bar->jumlah;
    else
         $kredit= $bar->jumlah*-1;

    $noref=$bar->noreferensi;
    if(trim($noref)=='')$noref=$bar->nojurnal;
if(isset($_GET['type']) and $_GET['type']=='excel')$tampiltanggal=$bar->tanggal; else $tampiltanggal=tanggalnormal($bar->tanggal);
    $penerima=$bar->namakaryawan;
    if(substr($bar->noreferensi,11,2)=='-G'){ // kalo transaksi gudang
        $penerima=$bar->namapenerima;
        if(substr($bar->namapenerima,0,3)=='000')$penerima=$bar->namakaryawan;
    }
    $stream.="<tr class=rowcontent>
           <td>".$no."</td>
           <td>".$noref."</td>               
           <td>".$tampiltanggal."</td>    
           <td>".$noakun."</td>    
           <td>".$bar->keterangan."</td>
           <td align=right>".number_format($debet,2)."</td>
           <td align=right>".number_format($kredit,2)."</td>  
           <td>".$penerima."</td>
                <td>".$nmSup[$bar->kodesupplier]."</td>
           <td>".$bar->kodevhc."</td>
           <td>".$bar->kodeblok."</td>  
        </tr>";
    $tdebet+=$debet;
    $tkredit+=$kredit;    
} 
$stream.="<tr class=rowcontent>
    <td colspan=5>TOTAL</td>
    <td align=right>".number_format($tdebet,2)."</td>
    <td align=right>".number_format($tkredit,2)."</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>";  
$stream.="</tbody><tfoot></tfoot></table>";
if(isset($_GET['type']) and $_GET['type']=='excel')
{
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="Detail_jurnal_".$_GET['gudang']."_".$_GET['periode'];
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