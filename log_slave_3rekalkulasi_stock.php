<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

#ambil periode aktif gudang:
$periode='';
$str="select max(periode) as periode from ".$dbname.".log_5saldobulanan where kodegudang='".$_POST['unit']."' limit 1";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $periode=$bar->periode;
}
if($periode==''){
    exit(" Error: Periode untuk gudang ".$_POST['unit']." belum terdaftar" );
}
else{
    #ambil tanggal aktif
    $mulai='';
    $sampai='';
    $str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['unit']."' and periode='".$periode."'";
    $res=mysql_query($str);
            while($bar=mysql_fetch_object($res)){
                    $mulai=$bar->tanggalmulai;
                    $sampai=$bar->tanggalsampai;
            }
     if($mulai=='' or sampai=='' ){
         exit(" Error: tanggal mulai dan tanggal sampai periode aktif belum ada");
     }
    #ambil saldo awal
    $str="select a.kodebarang,a.saldoawalqty,a.saldoakhirqty,a.hargarata,a.nilaisaldoawal,b.namabarang,b.satuan from ".$dbname.".log_5saldobulanan a 
              left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodegudang='".$_POST['unit']."' and a.periode='".$periode."'";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $Dt['saldoawalqty'][$bar->kodebarang]=$bar->saldoawalqty;
        $Dt['nilaisaldoawal'][$bar->kodebarang]=$bar->nilaisaldoawal;
        $Dt['saldoakhirqty'][$bar->kodebarang]=$bar->saldoakhirqty;
        $Dt['hargarata'][$bar->kodebarang]=$bar->hargarata;
        $Dt['namabarang'][$bar->kodebarang]=$bar->namabarang;
        $Dt['satuan'][$bar->kodebarang]=$bar->satuan; 
    }
 #ambil data masuk
    $str="select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargasatuan) as rpmasuk from ".$dbname.".log_transaksi_vw where kodegudang='".$_POST['unit']."' and tanggal>='".$mulai."' and tanggal <='".$sampai."'
              and tipetransaksi<5 and statussaldo=1 group by kodebarang";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $masuk[$bar->kodebarang]=$bar->jumlah;
        $rpmasuk[$bar->kodebarang]+=$bar->rpmasuk;
    }
#ambil rupiah per barang per gudang menjadi tambahan rpmasuk
//    $sJrn="select kodebarang,jumlah from ".$dbname.".keu_jurnaldt where  nojurnal like '%EXP01%' and tanggal between '".$mulai."' and '".$sampai."' and right(noreferensi,6)='".$_POST['unit']."' and kodebarang!=''";
    $sJrn="select kodebarang,jumlah from ".$dbname.".keu_jurnaldt where (nojurnal like '%EXP01%' or nojurnal like '%INVM1%') and tanggal between '".$mulai."' and '".$sampai."' and right(noreferensi,6)='".$_POST['unit']."' and kodebarang!='' and (keterangan like 'Biaya Kirim%' or keterangan like 'PBBKB%')";
    $qJrn=mysql_query($sJrn) or die(mysql_error($conn));
    while($rJrn=mysql_fetch_assoc($qJrn)){
      $rpmasuk[$rJrn['kodebarang']]+=$rJrn['jumlah'];  
    }
 #ambil data keluar
    $str="select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from ".$dbname.".log_transaksi_vw where kodegudang='".$_POST['unit']."' and tanggal>='".$mulai."' and tanggal <='".$sampai."'
              and tipetransaksi>4 and statussaldo=1 group by kodebarang";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $keluar[$bar->kodebarang]=$bar->jumlah;
        $rpkeluar[$bar->kodebarang]+=$bar->rpkeluar;
    }    
    
  #hilangkan blank
    $fixdata=Array();
    foreach($Dt['saldoawalqty'] as $key=>$val){
           if(!isset( $masuk[$key])){
               $masuk[$key]=0;
           }
           if(!isset( $keluar[$key])){
               $keluar[$key]=0;
           }
           
          $seharusnya= $Dt['saldoawalqty'][$key]+$masuk[$key]-$keluar[$key];
          if($seharusnya!=$Dt['saldoakhirqty'][$key]){
              $fixdata['saldoawal'][$key]=$Dt['saldoawalqty'][$key];
              $fixdata['saldoakhir'][$key]=$Dt['saldoakhirqty'][$key];
              $fixdata['masuk'][$key]=$masuk[$key];
              $fixdata['keluar'][$key]=$keluar[$key];              
              $fixdata['seharusnya'][$key]=$seharusnya;
              
              $fixdatarp['masuk'][$key]=$rpmasuk[$key]>0?$rpmasuk[$key]:0;
              $fixdatarp['keluar'][$key]=$rpkeluar[$key]>0?$rpkeluar[$key]:0;
              $fixdatarp['saldoakhir'][$key]=round($Dt['nilaisaldoawal'][$key]+ $fixdatarp['masuk'][$key]-$fixdatarp['keluar'][$key],4);  
              $fixdatarp['hargarata'][$key]=$fixdata['seharusnya'][$key]>0?$fixdatarp['saldoakhir'][$key]/$fixdata['seharusnya'][$key]:0;   
          }
}
$stream="<table class=sortable border=0 cellspacing=1 width=100%>
                   <thead>
                   <tr class=rowheader>
                   <td>".$_SESSION['lang']['nomor']."</td>
                   <td>".$_SESSION['lang']['kodebarang']."</td>
                   <td>".$_SESSION['lang']['namabarang']."</td>
                   <td>".$_SESSION['lang']['satuan']."</td>
                   <td>".$_SESSION['lang']['saldoawal']."</td>
                   <td>".$_SESSION['lang']['masuk']."</td>
                   <td>".$_SESSION['lang']['keluar']."</td>
                   <td>System</td>
                   <td>Fixed</td>
                   </tr></thead><tbody>";
$no=0;
if(count($fixdata)>0){
    #update log_5saldobulanan
    foreach($fixdata['saldoawal'] as $key=>$val){
        $no++;
        $str="update ".$dbname.".log_5saldobulanan set saldoakhirqty=".$fixdata['seharusnya'][$key].",qtymasuk=".$fixdata['masuk'][$key].",qtykeluar=".$fixdata['keluar'][$key].",
                   hargarata=".$fixdatarp['hargarata'][$key].", qtymasukxharga=".$fixdatarp['masuk'][$key].",qtykeluarxharga=".$fixdatarp['keluar'][$key].",
                   nilaisaldoakhir=".$fixdatarp['saldoakhir'][$key]." where kodebarang='".$key."' and kodegudang='".$_POST['unit']."'
                   and periode='".$periode."'";
        mysql_query($str);
        echo mysql_error($conn);
       #update log_5masterbarangdt
        $str="update ".$dbname.".log_5masterbarangdt set saldoqty=".$fixdata['seharusnya'][$key]." where kodebarang='".$key."' and kodegudang='".$_POST['unit']."'";
        mysql_query($str);
        echo mysql_error($conn);
        $stream.="<tr class=rowcontent>
                           <td>".$no."</td>
                           <td>".$key."</td>          
                           <td>". $Dt['namabarang'][$key]."</td>
                           <td>". $Dt['satuan'][$key]."</td>
                           <td align=right>". number_format($fixdata['saldoawal'][$key],2)."</td> 
                           <td align=right>". number_format($fixdata['masuk'][$key])."</td>
                           <td align=right>". number_format($fixdata['keluar'][$key])."</td>  
                           <td bgcolor=red align=right>". number_format($fixdata['saldoakhir'][$key])."</td>
                           <td align=right>". number_format($fixdata['seharusnya'][$key])."</td>
                           </tr>";    
    }
    $stream.="</tbody><tfoot></tfoot></table>Saldo sudah diperbaiki";
   echo $stream; 
}
else{
    echo" Data transaksi dan saldo tidak bermasalah";
}
}

/*
if($excel!='excel'){
	echo $tab;
}else{
$nop_="RekalkulasiStock_".$unit;
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
closedir($handle);
}	
}			
*/
?>