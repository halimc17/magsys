<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
include_once('lib/zJournal.php');

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];

//cek apakah ada data
/*$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
          and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%'";
//exit(" Error".$str);
$res=mysql_query($str);
if(mysql_num_rows($res)>0){
    exit(" Warning: Proses Gaji sudah dilakukan");    
}else{
    //$str="insert into ".$dbname.".keu_jurnalht(nojurnal,kodejurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi) values(
    //    '".$tahunbulan."28/".$_SESSION['empl']['lokasitugas']."/KBNB0/0000','KBNB0','".$param['periode']."-28','".date('Y-m-d')."','1','0','0','0','ALK_GAJI','1','IDR','1','0')";
    //mysql_query($str);                              
}*/

//ambil akun laba tahun berjalan;
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'";
$rel=mysql_query($stl);
$akunCLM='';
while($bal=mysql_fetch_object($rel))
{
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'";
$rel=mysql_query($stl);
$akunCLY='';
while($bal=mysql_fetch_object($rel))
{
    $akunCLY=$bal->noakundebet;
}
//ambil batas bawah akun laba/rugi
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'";
$rel=mysql_query($stl);
$akunRAT='';
while($bal=mysql_fetch_object($rel))
{
    $akunRAT=$bal->noakundebet;
}
if($akunCLM=='' or $akunCLY=='' or $akunRAT=='')
{
    if($_SESSION['language']=='EN'){
        exit(' Error: Annual income account data, account  retained earnings and account limits profits / losses not yet listed on the parameters of the journal');
    }else{
       exit(' Error: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
    }
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
//echo $str."____";
$res=mysql_query($str);
$currstart='';
$currend='';
while($bar=mysql_fetch_object($res))
{
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend=='')
{
    exit('Error: Accounting period is not normal to '.$_SESSION['empl']['lokasitugas']);
}
else
{
    #periksa kas
    $str="select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo " There are Cash/Bank transaction that has not been posted:\n";
        $no=0;
        while($bar=mysql_fetch_object($res))
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Error');
    }

    #periksa bapp
    $str="select notransaksi,tanggal,jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$_SESSION['empl']['lokasitugas']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo "There are Contract Realization transaction that has not been posted:\n";
        $no=0;
        while($bar=mysql_fetch_object($res))
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."\n"; 
        }
        exit('Error');
    }
    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo "There is still yet balanced Journal:\n";
        $no=0;
        while($bar=mysql_fetch_object($res))
        {
           $no+=1;
            echo $no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."\n"; 
        }
        exit('Error');
    }    
    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and kodegudang like '".$_SESSION['empl']['lokasitugas']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $res=mysql_query($str);
    $stm='';
    if(mysql_num_rows($res)>0){
        while($bar=mysql_fetch_object($res))
        {
             $stm.="Gudang:".$bar->kodegudang."->No.>".$bar->notransaksi."->".$bar->tanggal."<br>";
         }
       echo "Error: Warehouse transaction that has not been posted\r<br>".$stm; 
       exit();
    }
    #periksa Tutup Gudang
    $str="select kodeorg,periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
			and kodeorg <> '".$_SESSION['empl']['lokasitugas']."'
            and periode = '".$param['periode']."'";
    $res=mysql_query($str);
    $stm='';
    if(mysql_num_rows($res)>0){
        while($bar=mysql_fetch_object($res))
        {
             $stm.="Gudang:".$bar->kodeorg."->Periode:".$bar->periode."";
         }
       echo "Error: Warehouse transaction that has not been Closed\r".$stm; 
       exit();
    }
	/*
	#cek penerimaan barang mutasi tambahan jamhari 23062013
    $scekMut="select * from ".$dbname.".log_transaksiht where kodegudang like '".$_SESSION['empl']['lokasitugas']."%'
              and tanggal between '".$currstart."' and '".$currend."' and tipetransaksi=7 
              and (notransaksireferensi is null or notransaksireferensi='') order by notransaksi asc";
    //exit("error:".$scekMut);
    $qcekMut=mysql_query($scekMut) or die(mysql_error($conn));
    if(mysql_num_rows($qcekMut)>0)
    {
        echo "Error: Still no receipt of goods mutation that has not been done:\n";
        while($rcekMut=  mysql_fetch_object($qcekMut)){
            echo $rcekMut->notransaksi.":".tanggalnormal($rcekMut->tanggal)."\n";
        }
       exit("error");
    }
   #Periksa BKM
    $str="select notransaksi,tanggal from ".$dbname.".kebun_aktifitas where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and jurnal=0";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo " There still estate transaction that has not been posted:\n";
        $no=0;
        while($bar=mysql_fetch_object($res))
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n";
        }
        exit('Error');
    }
	*/
   #Periksa TRAKSI
    $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo " There still Vehicle Runn transaction that has not been posted:\n";
        $no=0;
        while($bar=mysql_fetch_object($res))
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n";
        }
        exit('Error');
    }    
}   

#PERIKSA akun transit yang belum nol=============================
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$_SESSION['empl']['lokasitugas']."' AND noakun like '4%'";
$res=mysql_query($str);
$transit=0;
if(mysql_num_rows($res)>0){
        while($bar=mysql_fetch_object($res))
        {
            $transit=$bar->saldo;
        }
}
if($transit>100 && $transit!='')#lebih dari  10 rupiah
{
    exit(" Error: Transit account has not been allocated correctly, remains:".$transit);
}
#---------------------------------------==================================

if(substr($_SESSION['empl']['lokasitugas'],2,2)!='HO' and substr($_SESSION['empl']['lokasitugas'],2,2)!='LO'){
    #PERIKSA apakah sudah ada gaji=============================
    //$str="select jumlah FROM ".$dbname.".sdm_gaji where  periodegaji ='".$param['periode']."' 
    //          and kodeorg='".$_SESSION['empl']['lokasitugas']."' and substr(kodeorg,3,2)!='HO'";
    //$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
    //          and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%' and substr(nojurnal,12,2)!='HO'";
    $str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%'";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0){

    }else{
        exit(" Error: Proses Gaji has not been processed. ");    
    }
    #---------------------------------------==================================
}

/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $listTiket="";
    /*$qJual = "SELECT notransaksi,a.nokontrak
            FROM ".$dbname.".pabrik_timbangan a
            INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
            WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
                "'and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";*/
    $qJual = "SELECT notransaksi,a.nokontrak
            FROM ".$dbname.".pabrik_timbangan a
            INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
            WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
                "'and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '".substr($currstart,0,4)."%') 
                  and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and (tipe='PABRIK' or tipe='HOLDING'))";
        $resJual = fetchData($qJual);
        if(!empty($resJual)) {
            $listTiket = '';
            foreach($resJual as $row) {
                $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
                        where notransaksi='".$row['notransaksi']."'";
                        //exit("error:".$scek2);
                $qcek2=mysql_query($scek2) or die(mysql_error($conn));
                $rcek2=mysql_num_rows($qcek2);
                if($rcek2==0){
                    $listTiket .= "- ".$row['notransaksi']."\n";    
                }
            }
            if($listTiket!=''){
                exit("Warning: Ada Timbangan Pabrik ke Eksternal yang belum diakui\n".$listTiket);    
            }
            
        }
}

/**************************************************************
 * [END] Cek Pengakuan Penjualan ******************************
 **************************************************************/


/**************************************************************
 * [START] Buat HPP CPO,PK,TBS ********************************
 **************************************************************/
$tglPeriode="tanggal between '".$currstart."' and '".$currend."' ";
$tglPeriode2="left(tanggal,10) between '".$currstart."' and '".$currend."' ";
$ptUnit=$_SESSION['org']['kodeorganisasi'];
#cek apakah memiliki pabrik
#jika memiliki pabrik,cek apakah sudah akrtif mengolah jika iya,masuk ke dalam proses hpp. jika tidak, tidak masuk ke proses hpp
$sCekPabrikA="select count(kodeorganisasi) as jmlhPabrik from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."' ";

$qCekPabrikA=mysql_query($sCekPabrikA) or die(mysql_error($conn));
$rCekPabrikA=mysql_fetch_assoc($qCekPabrikA);

if($rCekPabrikA!=0){
$sCekPabrik="select count(kodeorg) as jmlhPabrik from ".$dbname.".pabrik_produksi 
             where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."')  and  ".$tglPeriode."";
$qCekPabrik=mysql_query($sCekPabrik) or die(mysql_error($conn));
$rCekPabrik=mysql_fetch_assoc($qCekPabrik);
if(intval($rCekPabrik['jmlhPabrik'])!=0){//start pengecekan ada pabrik atau tidak
$ptUnit = $_SESSION['org']['kodeorganisasi'];
$unit = $_SESSION['empl']['lokasitugas'];
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
    // Kode Barang
    $barang = array(
        'cpo' => '40000001',
        'pk' => '40000002',
        'tbs' => '40000003',
    );
    
    // Cek Saldo Awal HPP
    $qHpp = selectQuery($dbname,'keu_4hpp',"*",
                        "kodeorg = '".$unit."' and periode = '".$param['periode']."'");
    $resHpp = fetchData($qHpp);
    $optHpp = array();
    foreach($resHpp as $row) {
        $optHpp[$row['kodebarang']] = array(
            'qty' => $row['qtyawal'],
            'rp' => $row['rpawal']
        );
    }
    
    // Init Saldo Awal
    $tbsAwal = (empty($resHpp))? 0: $optHpp[$barang['tbs']]['qty'];
    $tbsRpAwal = (empty($resHpp))? 0: $optHpp[$barang['tbs']]['rp'];
    
    $cpoAwal = (empty($resHpp))? 0: $optHpp[$barang['cpo']]['qty'];
    $cpoRpAwal = (empty($resHpp))? 0: $optHpp[$barang['cpo']]['rp'];
    
    $pkAwal = (empty($resHpp))? 0: $optHpp[$barang['pk']]['qty'];
    $pkRpAwal = (empty($resHpp))? 0: $optHpp[$barang['pk']]['rp'];
    
    
    
    // Price / KG
    @$priceTbsAwal = empty($tbsAwal)? 0: $tbsRpAwal / $tbsAwal;
    @$tbsAwalAlkCpo=$tbsRpAwal*0.9;
    @$tbsAwalAlkKer=$tbsRpAwal*0.1;
    
    /**
     * TBS - Penerimaan
     */
    // Fisik
    // $qTbsIn = "select intex,sum(beratbersih-kgpotsortasi) as tbs from ".$dbname.".pabrik_timbangan 
    //        where kodebarang='40000003' and left(tanggal,10) between '".$currstart."' and '".$currend."'
    //        and millcode in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."')";
    // $resTbsIn = fetchData($qTbsIn);
    // //$tbsIn = array();
    // foreach($resTbsIn as $row) {
    //     $tbsIn = $row['tbs'];
    // }
    
    
    $qTbsIn = "select sum(tbsmasuk) as tbsolah from ".$dbname.".pabrik_produksi 
               where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."') 
               and  ".$tglPeriode."";
    $resTbsIn = fetchData($qTbsIn);
    $tbsIn = empty($resTbsIn[0]['tbsolah'])? 0: $resTbsIn[0]['tbsolah'];

    
    // Rupiah Internal
    $qTbsRpPanen = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where ".$tglPeriode." and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi
              where induk='".$ptUnit."') and noakun like '61%' 
              and noakun not in ('6219999','6430800','6430500', '6430600', '6430700') and nojurnal not like '%HPP%' ";
    $resTbsRp = fetchData($qTbsRpPanen);
    $rpTbsInt = empty($resTbsRp[0]['jumlah'])? 0: $resTbsRp[0]['jumlah'];
    
    #biaya deplesi
    $sDeplesi="select sum(jumlah) as deplesi from ".$dbname.".keu_jurnaldt 
                where ".$tglPeriode." and  noakun='7150201' and kodeorg='".$unit."'";
    $qDeplesi=mysql_query($sDeplesi) or die(mysql_error($conn));
    $rDeplesi=mysql_fetch_assoc($qDeplesi);
    $rpTbsInt=$rpTbsInt+$rDeplesi['deplesi'];
    

    #rupiah biaya jika ada jurnal memorial dari HO
    $qTbsRpRawat = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
                where ".$tglPeriode." and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."') 
                and noakun like '62%' and noakun not in ('6219999','6430800','6430500', '6430600', '6430700')  and nojurnal not like '%HPP%'";
    //echo $qTbsRpRawat;
    $resTbsRp2 = fetchData($qTbsRpRawat);
    $rpTbsInt2 = empty($resTbsRp2[0]['jumlah'])? 0: $resTbsRp2[0]['jumlah'];

    #rupiah biaya tbs beli ke plasma
    $qTbsRpRawat = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
                where ".$tglPeriode." and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."') 
                and noakun in ('6410400','6410100') and noakun not in ('6219999','6430800','6430500', '6430600', '6430700')  and nojurnal not like '%HPP%'";
     
    $resTbsRp3 = fetchData($qTbsRpRawat);
    $rpTbsInt3 = empty($resTbsRp3[0]['jumlah'])? 0: $resTbsRp3[0]['jumlah'];
       


    #overhead 
    #total unit kebun
    /*$sUnitKbn="select distinct kodeorg from ".$dbname.".keu_jurnaldt 
              where ".$tglPeriode." and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe not in ('HOLDING','KANWIL') and tipe='KEBUN' and induk='".$ptUnit."') order by kodeorg asc ";*/
    $sUnitKbn="select distinct unit  from ".$dbname.".kebun_prestasi_vw 
              where ".$tglPeriode."  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$ptUnit."')   order by unit asc";
    $qUnitKbn=mysql_query($sUnitKbn) or die(mysql_error($conn));
    $rowUnitKbn=mysql_num_rows($qUnitKbn);

    #total unit pabrik
    $sUnitPbrk="select distinct kodeorg from ".$dbname.".pabrik_pengolahan 
              where ".$tglPeriode." and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."') order by kodeorg asc ";
    $qUnitPbrk=mysql_query($sUnitPbrk) or die(mysql_error($conn));
    $rowUnitPbrk=mysql_num_rows($qUnitPbrk);
    $rowUnit=$rowUnitKbn+$rowUnitPbrk;


    #over head ro dan ho
    $sOverHead="select sum(jumlah) as overhead from ".$dbname.".keu_jurnaldt 
                where ".$tglPeriode." and noakun like '7%' and noakun!='7150201' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$ptUnit."')  and noakun!='7199999'";
    $qOverHead=mysql_query($sOverHead) or die(mysql_error($conn));
    $rOverHead=mysql_fetch_assoc($qOverHead);
    @$byOverHead=$rOverHead['overhead']/$rowUnit;
    
    #overh head kebun
    $sOverHeadKbn="select sum(jumlah) as overhead from ".$dbname.".keu_jurnaldt 
                where ".$tglPeriode." and noakun like '7%' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$ptUnit."') and noakun!='7199999'";
    $qOverHeadKbn=mysql_query($sOverHeadKbn) or die(mysql_error($conn));
    $rOverHeadKbn=mysql_fetch_assoc($qOverHeadKbn);

    $byOvHeadKbn=($byOverHead*$rowUnitKbn)+$rOverHeadKbn['overhead'];#rupiah untuk jurnal alokasi overhead
    $rpTbsInt=$rpTbsInt+$rpTbsInt2+$rpTbsInt3;
    

    // Rupiah Eksternal
    $qTbsEks = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where  ".$tglPeriode."  and kodeorg = '".$unit."' and noakun in ('6430800','6410300')";
    $resTbsRpEks = fetchData($qTbsEks);
    $rpTbsEks = empty($resTbsRpEks[0]['jumlah'])? 0: $resTbsRpEks[0]['jumlah'];
    
    // Rupiah Afiliasi
    $qTbsAf = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where  ".$tglPeriode."  and kodeorg = '".$unit."'
              and noakun in ('6430500', '6430600', '6430700','6410200')";
    $resTbsRpAf = fetchData($qTbsAf);
    $rpTbsAf = empty($resTbsRpAf[0]['jumlah'])? 0: $resTbsRpAf[0]['jumlah'];
    
    // Price Mutasi
    $sumTbsIn = $tbsIn;
    @$priceTbsMutasi = empty($sumTbsIn)? 0: ($rpTbsInt+$rpTbsEks+$rpTbsAf+$byOvHeadKbn) / $sumTbsIn;
    #total biaya tbs
    $totByTbs=$rpTbsInt + $rpTbsEks + $rpTbsAf+$byOvHeadKbn;
    //exit("warning:".$totByTbs."___".$rpTbsInt."___".$rpTbsEks."___".$rpTbsAf."___".$byOvHeadKbn);
    /**
     * TBS - Pengeluaran
     */
    // Fisik
    $qTbsOut = "select sum(tbsdiolah) as tbsolah from ".$dbname.".pabrik_produksi 
               where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."') 
               and  ".$tglPeriode."";
    $resTbsOut = fetchData($qTbsOut);
    $tbsOut = empty($resTbsOut[0]['tbsolah'])? 0: $resTbsOut[0]['tbsolah'];
    
    // Rupiah
    $sisaTbsOut = $tbsOut - $tbsAwal; // Cari TBS yang memakai rupiah rata2 mutasi
    $qtyTbs1 = ($sisaTbsOut > 0)?$tbsAwal : $tbsOut; // Qty TBS yang memakai harga awal
    $rpTbsOut = $qtyTbs1 * $priceTbsAwal; // Rupiah dari TBS yang memakai harga awal
    //exit("error:".$tbsOut."___".$tbsAwal."___".$qtyTbs1);
    if($sisaTbsOut > 0) {
        $rpTbsOut += $sisaTbsOut * $priceTbsMutasi;
    }
    
    
    /**
     * TBS - Saldo Akhir
     */
    $tbsQtyAkhir = $tbsAwal + $tbsIn - $tbsOut;
    $tbsRpAkhir  = $tbsRpAwal+($rpTbsInt + $rpTbsEks + $rpTbsAf+$byOvHeadKbn) - $rpTbsOut;
    
    // Price
    @$cpoPriceAwal = empty($cpoAwal)? 0: $cpoRpAwal / $cpoAwal;
    @$pkPriceAwal = empty($pkAwal)? 0: $pkRpAwal / $pkAwal;
    
    /**
     * CPO & PK - Penerimaan
     */
    // Fisik
    $qCpoOlah = "SELECT sum(oer) as cpoqty, sum(oerpk) as pkqty
        FROM ".$dbname.".pabrik_produksi WHERE ".$tglPeriode."
        AND kodeorg in (select kodeorganisasi from ".$dbname.".organisasi
        where tipe='PABRIK' and induk='".$ptUnit."')";
    $resCpoOlah = fetchData($qCpoOlah);
    $cpoIn = empty($resCpoOlah[0]['cpoqty'])? 0: $resCpoOlah[0]['cpoqty'];
    $pkIn = empty($resCpoOlah[0]['pkqty'])? 0: $resCpoOlah[0]['pkqty'];
    
    // // Rupiah Pengolahan
     $cpoRpOlah = (($totByTbs * 0.9)+$tbsAwalAlkCpo)-($tbsRpAkhir*0.9);//perubahan total biaya di tambah rupiah awal di kurang tbs di olah
     $pkRpOlah = (($totByTbs * 0.1)+$tbsAwalAlkKer)-($tbsRpAkhir*0.1);
    
    // Rupiah Biaya Pabrik
    $qCpoRp = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where ".$tglPeriode." and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi
              where tipe='PABRIK' and induk='".$ptUnit."') and noakun like '63%'";
    
    $resCpoRp = fetchData($qCpoRp);
    $cpoRpInt = empty($resCpoRp[0]['jumlah'])? 0: $resCpoRp[0]['jumlah'] * 0.9;
    $pkRpInt = empty($resCpoRp[0]['jumlah'])? 0: $resCpoRp[0]['jumlah'] * 0.1;
    
    // Rupiah Eksternal
    $qCpoEks = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where ".$tglPeriode." and kodeorg = '".$unit."' and noakun like '6420500'";
    $resCpoRpEks = fetchData($qCpoEks);
    $cpoRpEks = empty($resCpoRpEks[0]['jumlah'])? 0: $resCpoRpEks[0]['jumlah'] * 0.9;
    $pkRpEks = empty($resCpoRpEks[0]['jumlah'])? 0: $resCpoRpEks[0]['jumlah'] * 0.1;
    
    // Rupiah Afiliasi
    $qCpoAf = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
              where ".$tglPeriode." and kodeorg = '".$unit."'
              and noakun in ('6420100', '6420200', '6420300','6420400')";
    $resCpoRpAf = fetchData($qCpoAf);
    $cpoRpAf = empty($resCpoRpAf[0]['jumlah'])? 0: $resCpoRpAf[0]['jumlah'] * 0.9;
    $pkRpAf = empty($resCpoRpAf[0]['jumlah'])? 0: $resCpoRpAf[0]['jumlah'] * 0.1;
    
    //over head pabrik
    $sOverHeadMll="select sum(jumlah) as overhead from ".$dbname.".keu_jurnaldt 
                where ".$tglPeriode." and noakun like '7%' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."') and noakun!='7199999'";
    $qOverHeadMll=mysql_query($sOverHeadMll) or die(mysql_error($conn));
    $rOverHeadMll=mysql_fetch_assoc($qOverHeadMll);

    $byOvHeadMll=($byOverHead*$rowUnitPbrk)+$rOverHeadMll['overhead'];

    $cpoOverhead=$byOvHeadMll*0.9;#rupiah untuk jurnal alokasi overhead untuk cpo
    $kerOverhead=$byOvHeadMll*0.1;#rupiah untuk jurnal alokasi overhead untuk ker


    // Price
    @$cpoPriceMutasi = empty($cpoIn)? 0: ($cpoRpOlah + $cpoRpInt + $cpoRpEks + $cpoRpAf+$cpoOverhead) / $cpoIn;
    @$pkPriceMutasi = empty($pkIn)? 0: ($pkRpOlah + $pkRpInt + $pkRpEks + $pkRpAf+$kerOverhead)/ $pkIn;
    //exit("error:".$cpoRpOlah."+".$cpoRpInt."+".$cpoRpEks."+".$cpoRpAf."+".$cpoOverhead."___".$cpoIn);
    
    /**
     * CPO & PK - Pengeluaran
     */
    // Fisik
    $qCpoOut = "select kodebarang,sum(beratbersih) as kg from ".$dbname.".pabrik_timbangan 
               where millcode in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."') 
               and ".$tglPeriode2." and kodebarang in ('40000001','40000002') and char_length(notransaksi)<11 group by kodebarang";
    $resCpoOut = fetchData($qCpoOut);
    $optCpoOut = array();
    foreach($resCpoOut as $row) {
        $optCpoOut[$row['kodebarang']] = $row['kg'];
    }
    $cpoOut = isset($optCpoOut['40000001'])? $optCpoOut['40000001']: 0;
    $pkOut = isset($optCpoOut['40000002'])? $optCpoOut['40000002']: 0;
    
    // Rupiah
    $sisaCpoOut = $cpoOut - $cpoAwal; // Cari CPO & PK yang memakai rupiah rata2 mutasi
    $sisaPkOut = $pkOut - $pkAwal;
    $qtyCpo1 = ($sisaCpoOut > 0)? $cpoAwal: $cpoOut; // Qty CPO & PK yang memakai harga awal
    $qtyPk1 = ($sisaPkOut > 0)? $pkAwal: $pkOut;
    
    $rpCpoOut = $qtyCpo1 * $cpoPriceAwal; // Rupiah dari CPO & PK yang memakai harga awal
    $rpPkOut = $qtyPk1 * $pkPriceAwal;
    
    if($sisaCpoOut > 0) $rpCpoOut += $sisaCpoOut * $cpoPriceMutasi;
    if($sisaPkOut > 0) $rpPkOut += $sisaPkOut * $pkPriceMutasi;
    //exit("error jam:".$sisaCpoOut."___".$cpoPriceMutasi."___".$rpCpoOut."___".$qtyCpo1."__".$cpoOut);
    /**
     * CPO & PK - Saldo Akhir
     */
    $cpoQtyAkhir = $cpoAwal + $cpoIn - $cpoOut;
    $cpoRpAkhir = $cpoRpAwal+($cpoRpOlah + $cpoRpInt + $cpoRpEks + $cpoRpAf+$cpoOverhead) - $rpCpoOut;
    $pkQtyAkhir = $pkAwal + $pkIn - $pkOut;
    $pkRpAkhir = $pkRpAwal+($pkRpOlah + $pkRpInt + $pkRpEks + $pkRpAf+$kerOverhead) - $rpPkOut;
   
    /***************************************************************************
     ** Jurnal *****************************************************************
     ***************************************************************************/
    // Init Param
    $zJ = new zJournal();
    $lastDay = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
    $nojurnal = $tahunbulan.$lastDay.'/'.$unit.'/HPP/001';
    $kodeJurnal = 'HPP';
    $tanggalJurnal = $param['periode'].'-'.$lastDay;
    $noUrut = 1;
    $noRef = $kodeJurnal.'/'.$unit.'/'.$tahunbulan;
    
    // Default Segment
    $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
    
    // Delete Jurnal
    $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal = '".$nojurnal."'");
    if(!mysql_query($qDel)) exit("Delete Error: ".mysql_error());
    
    // Prepare Data Header
    $dataResHPP['header'] = array(
        'nojurnal'=>$nojurnal, 'kodejurnal'=>$kodeJurnal,
		'tanggal'=>$tanggalJurnal, 'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>'0', 'totalkredit'=>'0',
		'amountkoreksi'=>'0',
		'noreferensi'=>$noRef,
		'autojurnal'=>'1',
		'matauang'=>'IDR', 'kurs'=>'1',
		'revisi'=>'0'
    );
    
    // Prepare Data Detail
    $dataResHPP['detail'] = array();
    
    /***************************************************************************
     ** Jurnal TBS *************************************************************
     ***************************************************************************/

    if($rpTbsInt!='') {
        // TBS Internal - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150208',
            'keterangan'=>'Produksi TBS Internal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsInt,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $dataResHPP['header']['totaldebet'] += $rpTbsInt;
        $dataResHPP['header']['totalkredit'] += $rpTbsInt;
        $noUrut++;
        
        // TBS Internal - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6219999',
            'keterangan'=>'Produksi TBS Internal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsInt * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($rpTbsEks !='') {
        // TBS Eksternal - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150208',
            'keterangan'=>'Pembelian TBS Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsEks,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpTbsEks;
        $dataResHPP['header']['totalkredit'] += $rpTbsEks;
        // TBS Eksternal - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6430999',
            'keterangan'=>'Pembelian TBS Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsEks * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($rpTbsAf!='') {
        // TBS Afiliasi - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150208',
            'keterangan'=>'Pembelian TBS Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsAf,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpTbsAf;
        $dataResHPP['header']['totalkredit'] += $rpTbsAf;
        // TBS Afiliasi - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6430999',
            'keterangan'=>'Pembelian TBS Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsAf * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    if($byOvHeadKbn!=''){
        // Overhead Kebun - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150208',
            'keterangan'=>'Biaya Overhead alokasi TBS '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$byOvHeadKbn,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $byOvHeadKbn;
        $dataResHPP['header']['totalkredit'] += $byOvHeadKbn;
        
        // Overhead Kebun - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'7199999',
            'keterangan'=>'Biaya Overhead alokasi TBS  '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$byOvHeadKbn * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($rpTbsOut !='') {
        // TBS Diolah - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410500',
            'keterangan'=>'Harga Pokok Penjualan TBS '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsOut,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpTbsOut;
        $dataResHPP['header']['totalkredit'] += $rpTbsOut;
        
        // TBS Diolah - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150208',
            'keterangan'=>'Harga Pokok Penjualan TBS '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpTbsOut * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['tbs'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    /***************************************************************************
     ** Jurnal CPO *************************************************************
     ***************************************************************************/
    if($cpoRpOlah !='') {
        // CPO Olah - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Penerimaan TBS (CPO Olah) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpOlah,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $cpoRpOlah;
        $dataResHPP['header']['totalkredit'] += $cpoRpOlah;
        
        // CPO Olah - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410500',
            'keterangan'=>'Penerimaan TBS (CPO Olah) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpOlah * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($cpoRpInt !='') {
        // Biaya Pabrik - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Biaya Pabrik porsi CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpInt,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $cpoRpInt;
        $dataResHPP['header']['totalkredit'] += $cpoRpInt;
        
        // Biaya Pabrik - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6330100',
            'keterangan'=>'Biaya Pabrik porsi CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpInt * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($cpoRpEks!='') {
        // CPO Eksternal - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Pembelian CPO Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpEks,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $cpoRpEks;
        $dataResHPP['header']['totalkredit'] += $cpoRpEks;
        
        // CPO Eksternal - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6431999',
            'keterangan'=>'Pembelian CPO Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpEks * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($cpoRpAf!='') {
        // CPO Afiliasi - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Pembelian CPO Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpAf,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $cpoRpAf;
        $dataResHPP['header']['totalkredit'] += $cpoRpAf;
        
        // CPO Afiliasi - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6431999',
            'keterangan'=>'Pembelian CPO Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoRpAf * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    if($cpoOverhead!=''){
        // Overhead CPO - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Biaya Overhead alokasi CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoOverhead,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $cpoOverhead;
        $dataResHPP['header']['totalkredit'] += $cpoOverhead;
        
        // Overhead CPO - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'7199999',
            'keterangan'=>'Biaya Overhead alokasi CPO  '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$cpoOverhead * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;

    }
    
    if($rpCpoOut !='') {
        // CPO Dikirim - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410600',
            'keterangan'=>'Harga Pokok Penjualan CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpCpoOut,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpCpoOut;
        $dataResHPP['header']['totalkredit'] += $rpCpoOut;
        
        // CPO Dikirim - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Harga Pokok Penjualan CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpCpoOut * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    
    /***************************************************************************
     ** Jurnal PK (Kernel) *****************************************************
     ***************************************************************************/
    if($pkRpOlah !='') {
        // PK Olah - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Penerimaan TBS (PK Olah) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpOlah,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $pkRpOlah;
        $dataResHPP['header']['totalkredit'] += $pkRpOlah;
        
        // PK Olah - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410500',
            'keterangan'=>'Penerimaan TBS (PK Olah) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpOlah * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($pkRpInt !='') {
        // Biaya Pabrik - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Biaya Pabrik porsi PK '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpInt,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $pkRpInt;
        $dataResHPP['header']['totalkredit'] += $pkRpInt;
        
        // Biaya Pabrik - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6330100',
            'keterangan'=>'Biaya Pabrik porsi PK '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpInt * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($pkRpEks!='') {
        // PK Eksternal - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Pembelian PK Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpEks,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $pkRpEks;
        $dataResHPP['header']['totalkredit'] += $pkRpEks;
        
        // PK Eksternal - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6432999',
            'keterangan'=>'Pembelian PK Eksternal '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpEks * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    if($pkRpAf!='') {
        // PK Afiliasi - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Pembelian PK Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpAf,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $pkRpAf;
        $dataResHPP['header']['totalkredit'] += $pkRpAf;
        
        // PK Afiliasi - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6432999',
            'keterangan'=>'Pembelian PK Afiliasi '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$pkRpAf * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    if($kerOverhead!=''){
        // Overhead KER - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Biaya Overhead alokasi KER '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$kerOverhead,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $kerOverhead;
        $dataResHPP['header']['totalkredit'] += $kerOverhead;
        
        // Overhead KER - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'7199999',
            'keterangan'=>'Biaya Overhead alokasi KER  '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$kerOverhead * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;

    }


    if($rpPkOut !='') {
        // PK Dikirim - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410700',
            'keterangan'=>'Harga Pokok Penjualan PK '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpPkOut,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpPkOut;
        $dataResHPP['header']['totalkredit'] += $rpPkOut;
        
        // PK Dikirim - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Harga Pokok Penjualan PK '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpPkOut * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    #Lakukan Jurnal
    $zJ->doJournal($ptUnit,$kodeJurnal,$dataResHPP,1,"",false);
    
    // Insert ke Saldo Awal HPP
    $nxtBulan = ($bulan<12)? $bulan+1: 1;
    $nxtTahun = ($bulan<12)? $tahun: $tahun+1;
    $nxtPeriod = $nxtTahun.'-'.str_pad($nxtBulan,2,'0',STR_PAD_LEFT);
    
    $dataHpp = array();
    // TBS
    $dataHpp[] = array(
        'kodeorg' => $unit,
        'periode' => $nxtPeriod,
        'kodebarang' => $barang['tbs'],
        'qtyawal' => $tbsQtyAkhir,
        'rpawal' => $tbsRpAkhir,
    );
    
    // CPO
    $dataHpp[] = array(
        'kodeorg' => $unit,
        'periode' => $nxtPeriod,
        'kodebarang' => $barang['cpo'],
        'qtyawal' => $cpoQtyAkhir,
        'rpawal' => $cpoRpAkhir,
    );
    
    // PK
    $dataHpp[] = array(
        'kodeorg' => $unit,
        'periode' => $nxtPeriod,
        'kodebarang' => $barang['pk'],
        'qtyawal' => $pkQtyAkhir,
        'rpawal' => $pkRpAkhir,
    );
    
    // Delete Saldo Awal HPP
    $qDelHPP = deleteQuery($dbname,'keu_4hpp',"kodeorg='".$unit."' and periode='".$nxtPeriod."'");
    if(!mysql_query($qDelHPP)) {
        echo "Delete HPP Error: ".mysql_error()."\n";
    }
    
    // Insert Saldo Awal HPP
    $qInsHPP = insertQuery($dbname,'keu_4hpp',$dataHpp);
    if(!mysql_query($qInsHPP)) {
        echo "Insert HPP Error: ".mysql_error()."\n";
        $zJ->rbJournal($nojurnal);
    }
}
/**************************************************************
 * [END] Buat HPP CPO,PK,TBS **********************************
 **************************************************************/
}//end pengecekan pabrik sudah mengolah atau belum
}//end pengecekan ada pabrik atau tidak

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

switch($proses) {
    case 'tutupBuku':
        #==================== Prep Periode ====================================
        # Prep Tahun Bulan untuk periode selanjutnya
        if($tmpPeriod[1]==12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0]+1;
        } else {
            $bulanLanjut = $tmpPeriod[1]+1;
            $tahunLanjut = $tmpPeriod[0];
        }
        
        # Prep Hari untuk periode selanjutnya
        $jmlHari = cal_days_in_month(CAL_GREGORIAN,$bulanLanjut,$tahunLanjut);
        $tglAwal = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-01';
        $tglAkhir = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-'.addZero($jmlHari,2);
        #==================== /Prep Periode ===================================
        
        #==================== Prep Jurnal =====================================
        #=== Extract Data ====
        # Get PT
        $pt = getPT($dbname,$param['kodeorg']);
        if($pt==false) {
            $pt = getHolding($dbname,$param['kodeorg']);
        }
        
        # Tanggal dan Kode Jurnal
        $tgl = $tmpPeriod[0].$tmpPeriod[1].
            cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
        $kodejurnal = 'CLSM';
        
        
        #==================== Journal Counter ==================
        $nojurnal = $tgl."/".$param['kodeorg'].
            "/".$kodejurnal."/999";
        #==================== Journal Counter ==================
        
        # Cek apakah tahun sudah ditutup
        $qCek = selectQuery($dbname,'keu_jurnalht','*',
            "nojurnal='".$nojurnal."'");
//        echo "error:".$qCek;
//        exit;
        $resCek = fetchData($qCek);
        if(!empty($resCek)) {
            $sPeriode="select periode from ".$dbname.".setup_periodeakuntansi 
                       where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc limit 1";
            $qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
            $rPeriode=mysql_fetch_assoc($qPeriode);
            if($rPeriode['periode']==$param['periode']){
                $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
                if(!mysql_query($sDel)){
                    exit("warning :".$sDel." ".mysql_error($conn));
                }
            }else{
                echo ' Error : This period has been closed(Before).';
                exit;    
            }
        }
        
         $query = "select count(*) as x from ".$dbname.".keu_jurnaldt_vw where 
                   tanggal between '".$currstart."' and '".$currend."' and substr(nojurnal,10,4)='".$param['kodeorg']."'";
//         exit("error: ".$query);
        $res=mysql_query($query);
        
       if(mysql_num_rows($res)==0) {
            echo 'Warning : No data found for this unit';
            exit;
        }
        
        # Get Sum dari Jurnal
        $query = selectQuery($dbname,'keu_jurnaldt_vw','substr(nojurnal,10,4) as kodeorg,sum(jumlah) as jumlah',
            "substr(nojurnal,10,4)='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."'
             and noakun>='".$akunRAT."'").
            "group by substr(nojurnal,10,4)";
        $data = fetchData($query);

        
        # Get Akun
        #+++++++++++++++++++++++++
        //tambahan ginting
        $noakun=$akunCLM;//akun laba tahun berjalan
        #++++++++++++++++++++++++++
        if($data[0]['jumlah']>0) {
            # Rugi
            $debetH=$data[0]['jumlah'];
            $kreditH=0;
        } else {
            # Laba
            $debetH=0;
            $kreditH=$data[0]['jumlah'];            
        }
        
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodejurnal,
            'tanggal'=>$tgl,
            'tanggalentry'=>date('Ymd'),
            'posting'=>'0',
            'totaldebet'=>$debetH,
            'totalkredit'=>$kreditH,
            'amountkoreksi'=>'0',
            'noreferensi'=>'TUTUP/'.$param['kodeorg'].'/'.$tahunbulan,
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );
        
        # Data Detail
        $noUrut = 1;
        
        # Debet
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$noakun,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>$data[0]['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$param['kodeorg'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment'=>$defSegment
        );
        $noUrut++;
 /*    kredit tidak perlu untuk laba rugi tahun berjalan   
        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$akunKredit,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>-1*$data[0]['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$pt['kode'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>''
            
        );
  *        $noUrut++; 
  * 
  */

       #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
        $headErr = '';
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        if(!mysql_query($insHead)) {
            $headErr .= 'Insert Header Error : '.mysql_error()."\n".$insHead."___".$query;
        }
        
        if($headErr=='') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                if(!mysql_query($insDet)) {
                    $detailErr .= "Insert Detail Error : ".mysql_error()."\n";
                    break;
                }
                else
                {

                }    
            }
            
            if($detailErr=='') {
                    #==================== /Prep Jurnal ====================================
                    createSaldoAwal($param['periode'],$tahunLanjut.'-'.addZero($bulanLanjut,2),$param['kodeorg']);
                    #========================== Proses Insert dan Update ==========================
 
                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>1),
                    "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                if(!mysql_query($queryUpd)) {
                    echo 'Error Update : '.mysql_error();
                    exit;
                } else {
                    # Insert periode baru
                    $dataIns = array(
                        'kodeorg'=>$param['kodeorg'],
                        'periode'=>$tahunLanjut.'-'.addZero($bulanLanjut,2),
                        'tanggalmulai'=>$tglAwal,
                        'tanggalsampai'=>$tglAkhir,
                        'tutupbuku'=>0
                    );
                    $queryIns = insertQuery($dbname,'setup_periodeakuntansi',$dataIns);
                    echo '1';
                    if(!mysql_query($queryIns)) {
                        # Rollback
                        echo 'Error Insert : '.mysql_error();
                        $queryRB = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>0),
                            "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                        if(!mysql_query($queryRB)) {
                            echo 'Error Rollback Update : '.mysql_error();
                            exit;
                        }
                    }
                    else{
                            //update history tutup buku
                            $str="delete from ".$dbname.".keu_setup_watu_tutup where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'";
                            mysql_query($str);
                            $str="insert into ".$dbname.".keu_setup_watu_tutup(kodeorg,periode,username) values(
                                  '".$param['kodeorg']."','".$param['periode']."','".$_SESSION['standard']['username']."')";
                            mysql_query($str);                              
                        }                    
                }
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                if(!mysql_query($RBDet)) {
                    echo "Rollback Delete Header Error : ".mysql_error();
                    exit;
                }
            }
        } else {
            echo $headErr;
            exit;
        }
        
 #email notifikasi bjr       
        break;
    default:
}

function createSaldoAwal($dariperiode,$keperiode,$kodeorg)
{
    global $conn;
    global $dbname;
    global $akunRAT;
    global $akunCLM;
    global $akunCLY;
    $sawal=Array();
    $mtdebet=Array();
    $mtkredit=Array();
    $salak=Array();
    #ambil saldoawal bulan berjalan
    $str="select awal".substr($dariperiode,5,2).",noakun from ".$dbname.".keu_saldobulanan
          where periode='".str_replace("-", "", $dariperiode)."' and kodeorg='".$kodeorg."'";
    $res=mysql_query($str);
    while($bar=mysql_fetch_array($res))
    {
        $sawal[$bar[1]]=$bar[0];
        $mtdebet[$bar[1]]=0;
        $mtkredit[$bar[1]]=0;
        $salak[$bar[1]]=$bar[0];
    }
    #ambil transaksi transaksi bln berjalan
    $str="select debet,kredit,noakun from ".$dbname.".keu_jurnalsum_vw 
          where periode='".$dariperiode."' and kodeorg='".$kodeorg."'";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
    #ambil semu nomor akun
    $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
    $res=mysql_query($str);
    $temp='';
    while($bar=mysql_fetch_object($res))
    {
        #create string update current
       if($sawal[$bar->noakun]!=''){  
         #jika sudah ada di database maka update
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           
           $temp="update ".$dbname.".keu_saldobulanan 
                set debet".substr($dariperiode,5,2)."=".$mtdebet[$bar->noakun].",
                kredit".substr($dariperiode,5,2)."=".$mtkredit[$bar->noakun]."
                where periode='".str_replace("-", "", $dariperiode)."'
                and kodeorg='".$kodeorg."' and noakun='".$bar->noakun."';";
           if(!mysql_query($temp))
           {
               exit("Error update mutasi bulanan ".mysql_error($conn));
           }   
        }
        else
        {
           #jika belum ada maka insert
         if($sawal[$bar->noakun]!='' or $mtdebet[$bar->noakun]!='' or  $mtkredit[$bar->noakun]!=''){
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                  awal".substr($dariperiode,5,2).",debet".substr($dariperiode,5,2).",
                  kredit".substr($dariperiode,5,2).")values('". 
                   $kodeorg."','".str_replace("-", "", $dariperiode)."','".$bar->noakun."',0,".
                   $mtdebet[$bar->noakun].",".$mtkredit[$bar->noakun].");";
           if(!mysql_query($temp))
           {
               exit("Error insert mutasi bulanan ".mysql_error($conn));
           }  
         }
        }   
    } 
    #delete saldo awal bulan selanjutnya;
    $str="delete from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $keperiode)."'
          and kodeorg='".$kodeorg."';";
    if(mysql_query($str))
    {
        $saldoditahan=0;
        foreach($salak as $key=>$val){
            if($salak[$key]!=''){
              
                $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                      awal".substr($keperiode,5,2).")values('". 
                       $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";
               if(substr($keperiode,5,2)!='01')#jika bukan awal tahun
               {      
                   if(!mysql_query($temp))
                   {
                       exit("Error insert saldo awal ".mysql_error($conn).":".$temp);
                   }  
               }
               else #jika bulan 12
               {                     
                   if($key<$akunRAT){#jika awal tahun maka hanya akan membawa aktiva saja ke bulan selanjutnya
                #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                   #deteksi jika saldo ditahan
                   #sudah mengakomodasi tutup akhir tahun    
                    if($key==$akunCLY)
                        $saldoditahan+=$salak[$key];
                    else{                    
                            if($key==$akunCLM){
                                $saldoditahan+=$salak[$key];#tampung laba tahun berjalan ke laba ditahan
                                $salak[$key]=0;
                            }
                            $temp1="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                                  awal".substr($keperiode,5,2).")values('". 
                                   $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";

                       #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       

                           if(!mysql_query($temp1))
                           {
                               exit("Error insert saldo awal ".mysql_error($conn));
                           } 
                    }                   
                  }
               }
            }   
        }
      //masukkan saldo laba ditahan
     if(substr($keperiode,5,2)=='01'){//hanya pada bulan 12                           
        $temp2="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
          awal".substr($keperiode,5,2).")values
           ('".$kodeorg."','".str_replace("-", "", $keperiode)."','".$akunCLY."',".$saldoditahan.")";
       if(!mysql_query($temp2))
       {
           exit("Error insert laba ditahan pada saldo awal ".mysql_error($conn));
       }  
     }
    }   
}   
?>