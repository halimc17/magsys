<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//========================
  $gudang=$_POST['gudang'];
  $user  =$_SESSION['standard']['userid'];
  $period=$_POST['periode'];
  $awal  =$_POST['tanggalmulai'];
  $akhir =$_POST['tanggalsampai'];
  
  //==============
  $dtAdd=explode("-",$period);
  $bulan=$dtAdd[1];
  $x=str_replace("-","",$period);
  $x=str_replace("/","",$x);
  $x=mktime(0,0,0,intval(substr($x,4,2))+1,15,substr($x,0,4));
  $prefper=$period;
  $period=date('Y-m',$x);  
  

#periksa apakah sudah pernah tutup buku pada periode tersebut:
$str="select distinct(periode)  from `".$dbname."`.`log_5saldobulanan` where periode='".$period."' and kodegudang='".$gudang."'";
$res=mysql_query($str);
if(mysql_num_rows($res)>0){
    exit('Error: gudang '.$gudang.' sudah tutup buku pada periode tersebut ('.$prefper.'), mohon hubungi IT');
}



$nobkm=0;
##cek disini coi untuk transaksi BKM
$iBkm="select * from ".$dbname.".kebun_pakai_material_vw where jurnal=0 and tanggal like '%".substr($awal,0,7)."%' and"
        . " kodegudang='".$gudang."' ";
$nBkm=  mysql_query($iBkm) or die (mysql_error($conn));
while($dBkm=  mysql_fetch_assoc($nBkm))
{
    $nobkm++;
    $adabkm.=$dBkm['notransaksi']."____";
}
    

if($nobkm>0)
{
    exit("Error:Ada transaksi bkm yang memakai material belum terposting ".$adabkm." ");
}

#ambil PT:
$str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang,0,4)."'";
$res=mysql_query($str);
$pt='';
while($bar=mysql_fetch_object($res))
{
    $pt=$bar->induk;
}
if($pt=='')
{
    exit(' Error: Gudang belum memiliki PT');
}

//cel apakah sudah posting semua pada periode tersebut;
$str="select count(tanggal) as tgl from ".$dbname.".log_transaksi_vw
      where left(kodegudang,4)='".substr($gudang,0,4)."' and tanggal>='".$awal."' and tanggal<='".$akhir."'
      and (post=0 or statussaldo=0)";
$res=mysql_query($str);
$jlhNotPost=0;
while($bar=mysql_fetch_object($res))
{
	$jlhNotPost=$bar->tgl;
}

if($jlhNotPost>0)
{
    exit(" Error: ".$_SESSION['lang']['belumposting']." > 0");
} 
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
  #pengecekan apakah user sudah melakukan intergrity cek atau belum
  $sCek="select count(kodebarang) as jmlBrg from ".$dbname.".kebun_pakai_material_vw where 
         tanggal between '".$awal."' and '".$akhir."' and kodegudang like '".substr($gudang,0,4)."%'";
  $qCek=mysql_query($sCek) or die(mysql_error($conn));
  $rCek=mysql_fetch_assoc($qCek);

  $sCek2="select count(kodebarang) as jmlBrg from ".$dbname.".log_transaksi_vw where 
         tanggal between '".$awal."' and '".$akhir."' and kodegudang like '".substr($gudang,0,4)."%'
         and notransaksireferensi!='' and tipetransaksi=5";
  $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
  $rCek2=mysql_fetch_assoc($qCek2);
  if($rCek['jmlBrg']!=$rCek2['jmlBrg']){
    exit("warning: Silakan jalankan Proses pada menu Pengadaan>Proses>Intergrity Check BKM");
  }
}
/*
 #pengecekan apakah ada barang mutasi yang belum diterimakan
 #cek penerimaan barang mutasi tambahan jamhari 23062013
  $scekMut="select * from ".$dbname.".log_transaksiht where kodegudang like '".substr($gudang,0,4)."%'
            and tanggal between '".$awal."' and '".$akhir."' and tipetransaksi=7 
            and notransaksireferensi is null order by notransaksi asc";
  $qcekMut=mysql_query($scekMut) or die(mysql_error($conn));
  if(mysql_num_rows($qcekMut)>0)
  {
      echo "Masih ada notransaksi mutasi belum diterimakan:\n";
      while($rcekMut=  mysql_fetch_object($qcekMut)){
          echo $rcekMut->notransaksi.":".tanggalnormal($rcekMut->tanggal)."\n";
      }
     exit("warning");
  }
  $scekMut="select * from ".$dbname.".log_transaksiht where gudangx like '".substr($gudang,0,4)."%'
            and tanggal between '".$awal."' and '".$akhir."' and tipetransaksi=7 
            and notransaksireferensi is null order by notransaksi asc";
  $qcekMut=mysql_query($scekMut) or die(mysql_error($conn));
  if(mysql_num_rows($qcekMut)>0)
  {
      echo "Masih ada notransaksi mutasi belum diterimakan:\n";
      while($rcekMut=  mysql_fetch_object($qcekMut)){
          echo $rcekMut->notransaksi.":".tanggalnormal($rcekMut->tanggal)."___Gudang:".$rcekMut->notransaksi."\n";
      }
     exit("warning");
  }
  */
//=============================

/**************************************************************
 * [START] Rekalkulasi stock fisik dan harga ******************
 **************************************************************/
#ambil saldo awal
    $str="select a.kodebarang,a.saldoawalqty,a.saldoakhirqty,a.hargarata,a.nilaisaldoawal,b.namabarang,b.satuan,a.qtymasukxharga,a.qtykeluarxharga from ".$dbname.".log_5saldobulanan a 
              left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodegudang='".$gudang."' and a.periode='".$prefper."'";
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
    $str="select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargasatuan) as rpmasuk from ".$dbname.".log_transaksi_vw where kodegudang='".$gudang."' and tanggal>='".$awal."' and tanggal <='".$akhir."'
              and tipetransaksi<5 and statussaldo=1 group by kodebarang";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $masuk[$bar->kodebarang]=$bar->jumlah;
        $rpmasuk[$bar->kodebarang]+=$bar->rpmasuk;
    }    

  #ambil rupiah per barang per gudang menjadi tambahan rpmasuk    
    $sJrn="select kodebarang,jumlah from ".$dbname.".keu_jurnaldt where  nojurnal like '%EXP01%' and tanggal between '".$awal."' and '".$akhir."' and right(noreferensi,6)='".$gudang."' and kodebarang!=''";
    $qJrn=mysql_query($sJrn) or die(mysql_error($conn));
    while($rJrn=mysql_fetch_assoc($qJrn)){
      $rpmasuk[$rJrn['kodebarang']]+=$rJrn['jumlah'];  
    }

    #ambil data keluar
    $str="select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from ".$dbname.".log_transaksi_vw where kodegudang='".$gudang."' and tanggal>='".$awal."' and tanggal <='".$akhir."'
              and tipetransaksi>4 and statussaldo=1 group by kodebarang";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $keluar[$bar->kodebarang]=$bar->jumlah;
        $rpkeluar[$bar->kodebarang]+=$bar->rpkeluar;
    }    

  #hilangkan blank
    $fixdata=Array();
    if(!empty($Dt['hargarata'])){
        foreach($Dt['hargarata'] as $key=>$val){
               if(!isset( $masuk[$key])){
                   $masuk[$key]=0;
               }
               if(!isset( $keluar[$key])){
                   $keluar[$key]=0;
               }
               
              $seharusnya= $Dt['saldoawalqty'][$key]+$masuk[$key]-$keluar[$key];
              //if($seharusnya!=$Dt['saldoakhirqty'][$key]){
              if(($seharusnya!=$Dt['saldoakhirqty'][$key])||($rpmasuk[$key]!=$Dt['qtymasukxharga'][$key])||($rpkeluar[$key]!=$Dt['qtykeluarxharga'][$key])){
                  $fixdata['saldoawal'][$key]=$Dt['saldoawalqty'][$key];
                  $fixdata['saldoakhir'][$key]=$Dt['saldoakhirqty'][$key];
                  $fixdata['masuk'][$key]=$masuk[$key];
                  $fixdata['keluar'][$key]=$keluar[$key];              
                  $fixdata['seharusnya'][$key]=$seharusnya;
                  
                  $fixdatarp['masuk'][$key]=floatval($rpmasuk[$key])>0?$rpmasuk[$key]:0;
                  $fixdatarp['keluar'][$key]=floatval($rpkeluar[$key])>0?$rpkeluar[$key]:0;
                  $fixdatarp['saldoakhir'][$key]=round($Dt['nilaisaldoawal'][$key]+ $fixdatarp['masuk'][$key]- $fixdatarp['keluar'][$key],4);
                  $fixdatarp['hargarata'][$key]=floatval($fixdata['seharusnya'][$key])>0?$fixdatarp['saldoakhir'][$key]/$fixdata['seharusnya'][$key]:0;         
              }
        }

    if(count($fixdata)>0){
      foreach($fixdata['saldoawal'] as $key=>$val){
          #update log_5saldobulanan
          $str="update ".$dbname.".log_5saldobulanan set saldoakhirqty=".$fixdata['seharusnya'][$key].",qtymasuk=".$fixdata['masuk'][$key].",qtykeluar=".$fixdata['keluar'][$key].",
                       hargarata=".$fixdatarp['hargarata'][$key].", qtymasukxharga=".$fixdatarp['masuk'][$key].",qtykeluarxharga=".$fixdatarp['keluar'][$key].",
                       nilaisaldoakhir=".$fixdatarp['saldoakhir'][$key]." where kodebarang='".$key."' and kodegudang='".$gudang."'
                       and periode='".$prefper."'";
            if(!mysql_query($str)){
              echo mysql_error($conn);  
            }
           #update log_5masterbarangdt
            $str="update ".$dbname.".log_5masterbarangdt set saldoqty=".$fixdata['seharusnya'][$key]." where kodebarang='".$key."' and kodegudang='".$gudang."'";
            if(!mysql_query($str)){
              echo mysql_error($conn);  
            }
      }
    }
}
/**************************************************************
 * [END] Rekalkulasi stock fisik dan harga ********************
 **************************************************************/

/**************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 **************************************************************/
// Get Kelompok Barang yang ada Akun
$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '115%'");
$listKel = $listAkun = array();
foreach($optKel as $kode=>$akun) {
  $listKel[] =  $kode;
  $listAkun[$akun] =  $akun;
}

// Get Nilai Material, log_5saldobulanan
$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
  FROM ".$dbname.".log_5saldobulanan 
  WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".substr($gudang,0,4)."%' and periode='".$prefper."' GROUP BY left(kodebarang,3)";
  //echo $qSaldoMat."<p>";
  
$resSaldoMat = fetchData($qSaldoMat);
$optSaldoMat = array();
foreach($resSaldoMat as $row) {
  if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
    $optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
  } else {
        $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
    }
}
$periodeKuangan=$dtAdd[0].$dtAdd[1];
// Get Nilai Jurnal, keu_saldobulanan
$qSaldoJ = "SELECT awal".$bulan." as saldoawal,noakun
  FROM ".$dbname.".keu_saldobulanan
  WHERE kodeorg='".substr($gudang,0,4)."' and periode='".$periodeKuangan."'
    and noakun in ('".implode("','",$listAkun)."')";
	//echo $qSaldoJ."<p>";

$resSaldoJ = fetchData($qSaldoJ);
$optSaldoJ = array();
foreach($resSaldoJ as $row) {
  $optSaldoJ[$row['noakun']] = $row['saldoawal'];
}

// Get Transaksi Jurnal
$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
  FROM ".$dbname.".keu_jurnaldt_vw
  WHERE kodeorg='".substr($gudang,0,4)."' and tanggal>='".$awal."' and tanggal <='".$akhir."'
    and noakun in ('".implode("','",$listAkun)."')
  GROUP BY noakun";
  // echo $qTrans."<p>";
      // exit('warning');
$resTrans = fetchData($qTrans);
foreach($resTrans as $row) {
  if(!isset($optSaldoJ[$row['noakun']])) 
    $optSaldoJ[$row['noakun']] = 0;
  $optSaldoJ[$row['noakun']] += $row['saldotrans'];
}

// Cek All Akun
$notBal = "";
foreach($listAkun as $akun) {
  if(!isset($optSaldoMat[$akun])) $optSaldoMat[$akun] = 0;
  if(!isset($optSaldoJ[$akun])) $optSaldoJ[$akun] = 0;
  
  $selisih = abs( abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]) );
  if($selisih > 100) {
    $notBal .= $akun." = ".number_format($selisih)."___".abs($optSaldoMat[$akun])."____".abs($optSaldoJ[$akun])."\n";
  }
}

// Alert Jika ada yang belum balance
if(!empty($notBal)) {
  exit('warning: Silakan jalankan Proses pada menu Keuangan>Proses>Proses Akhir Bulan, Pilih Intergrity Check Gudang');
  //exit("Warning: Ada jurnal material yang belum balance dengan saldo material\n".$notBal);
}
/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/


//ambil saldo akhir bulan lalu termasuk rupiah
    $str="select kodebarang,saldoakhirqty,nilaisaldoakhir,hargarata 
            from ".$dbname.".log_5saldobulanan
            where kodeorg='".$pt."' and kodegudang='".$gudang."' and periode='".$prefper."'";

    $res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    
    
    
    
 //insert new line
  $str1="INSERT INTO `".$dbname."`.`log_5saldobulanan`
        (`kodeorg`,
        `kodebarang`,
        `saldoakhirqty`,
        `hargarata`,
        `lastuser`,
        `periode`,
        `nilaisaldoakhir`,
        `kodegudang`,
        `qtymasuk`,
        `qtykeluar`,
        `qtymasukxharga`,
        `qtykeluarxharga`,
        `saldoawalqty`,
        `hargaratasaldoawal`,
        `nilaisaldoawal`)
        VALUES
        (
            '".$pt."',
            '".$bar->kodebarang."',
            ".$bar->saldoakhirqty.",
            ".$bar->hargarata.",
            ".$user.",
            '".$period."',
            ".$bar->nilaisaldoakhir.",
            '".$gudang."',
            0,
            0,
            0,
            0,
            ".$bar->saldoakhirqty.",
            ".$bar->hargarata.",
            ".$bar->nilaisaldoakhir."
        )";
  if(!mysql_query($str1))
  {
      $err= addslashes(mysql_error($conn))."(".$str1.")"; 
      break;
  }
}

if($err=='')
{
  //next period is
  $nextPeriod=$period;
  $tg=mktime(0,0,0,substr($akhir,5,2),intval(substr($akhir,8,2)+1),intval(substr($prefper,0,4)));
  $nextAwal=date('Ymd',$tg);
  $tg=mktime(0,0,0,intval(substr($akhir,5,2))+1,date('t',$tg),intval(substr($prefper,0,4)));
  $nextAkhir=date('Ymd',$tg);  
 //update setup_periodeakuntansi
   $str="update ".$dbname.".setup_periodeakuntansi set tutupbuku=1
          where kodeorg='".$gudang."' and periode='".$prefper."'";
   if(mysql_query($str))
   {
    $str="INSERT INTO `".$dbname."`.`setup_periodeakuntansi`
            (`kodeorg`,
            `periode`,
            `tanggalmulai`,
            `tanggalsampai`,
            `tutupbuku`)
            VALUES
            ('".$gudang."',
                '".$nextPeriod."',
                ".$nextAwal.",
                ".$nextAkhir.",
                0
                )";
        if(mysql_query($str))
        {
            $str="delete from ".$dbname.".keu_setup_watu_tutup where periode='".$prefper."'. and kodeorg='".$gudang."'";
            mysql_query($str);
            $str="insert into ".$dbname.".keu_setup_watu_tutup(kodeorg,periode,username) values(
                  '".$gudang."','".$prefper."','".$_SESSION['standard']['username']."')";
            mysql_query($str);                        
        }
        else
        {
        $err= addslashes(mysql_error($conn))."(".$str.")";
        //buka kembali periodeakuntansi
           $str="update ".$dbname.".setup_periodeakuntansi set tutupbuku=0
          where kodeorg='".$gudang."' and periode='".$period."'";
        mysql_query($str);            
        //==========================================
        //delete jika sudah terdaftar pada saldo bulanan
        $str="delete from ".$dbname.".log_5saldobulanan where kodeorg='".$pt."' and kodegudang='".$gudang."'  and periode='".$period."'";
        mysql_query($str);   
        exit("Error: data ".$err);        
        }
   }
   else
   {
      $err= addslashes(mysql_error($conn))."(".$str.")";  
        //==========================================
        //delete jika sudah terdaftar pada saldo bulanan
        $str="delete from ".$dbname.".log_5saldobulanan where kodeorg='".$pt."' and kodegudang='".$gudang."'  and periode='".$period."'";
        mysql_query($str);   
        exit("Error: data ".$err);      
   }   
  
}
else
{
    //==========================================
    //delete jika sudah terdaftar pada saldo bulanan
    $str="delete from ".$dbname.".log_5saldobulanan where kodeorg='".$pt."' and kodegudang='".$gudang."'  and periode='".$period."'";
    mysql_query($str);   
    exit("Error: data ".$err);
}  
?>