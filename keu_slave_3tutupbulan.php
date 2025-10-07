<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];
 
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
	/*
    #cek penerimaan barang mutasi tambahan jamhari 23062013
    $scekMut="select * from ".$dbname.".log_transaksiht where kodegudang like '".$_SESSION['empl']['lokasitugas']."%'
              and tanggal between '".$currstart."' and '".$currend."' and tipetransaksi=7 
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
    $scekMut="select * from ".$dbname.".log_transaksiht where gudangx like '".$_SESSION['empl']['lokasitugas']."%'
              and tanggal between '".$currstart."' and '".$currend."' and tipetransaksi=7 
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
   #Periksa TRAKSI
    $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=mysql_query($str);
    if(mysql_num_rows($res)>0)
    {
        echo " There still Vehicle Run transaction that has not been posted:\n";
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
          and kodeorg='".$_SESSION['empl']['lokasitugas']."' AND noakun like '4%'";//exit('error'.$str);
$res=mysql_query($str);
$transit=0;
if(mysql_num_rows($res)>0){
        while($bar=mysql_fetch_object($res))
        {
            $transit=abs($bar->saldo);
        }
}
if($transit>100 && $transit!='')#lebih dari  10 rupiah
{
    exit(" Error: Transit account has not been allocated correctly, remains:".$transit);
}
#---------------------------------------==================================

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
	WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
		$param['kodeorg']."%' and periode like '".$param['periode']."%'
	GROUP BY left(kodebarang,3)";
$resSaldoMat = fetchData($qSaldoMat);
$optSaldoMat = array();
foreach($resSaldoMat as $row) {
	if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
		$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
	} else {
        $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
    }
}

// Get Nilai Jurnal, keu_saldobulanan
$qSaldoJ = "SELECT awal".$bulan." as saldoawal,noakun
	FROM ".$dbname.".keu_saldobulanan
	WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
		and noakun in ('".implode("','",$listAkun)."')";
$resSaldoJ = fetchData($qSaldoJ);
$optSaldoJ = array();
foreach($resSaldoJ as $row) {
	$optSaldoJ[$row['noakun']] = $row['saldoawal'];
}

// Get Transaksi Jurnal
$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
	FROM ".$dbname.".keu_jurnaldt_vw
	WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
		and noakun in ('".implode("','",$listAkun)."')
	GROUP BY noakun";
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
		$notBal .= $akun." = ".number_format($selisih)."\n";
	}
}

// Alert Jika ada yang belum balance
if(!empty($notBal)) {
	exit("Warning: Ada jurnal material yang belum balance dengan saldo material\n".$notBal);
}
/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/

if(substr($_SESSION['empl']['lokasitugas'],2,2)!='HO'){
    #PERIKSA apakah sudah ada gaji=============================
    //$str="select jumlah FROM ".$dbname.".sdm_gaji where  periodegaji ='".$param['periode']."' 
    //          and kodeorg='".$_SESSION['empl']['lokasitugas']."' and substr(kodeorg,3,2)!='HO'";
    //$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
    //          and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%' and substr(nojurnal,12,2)!='HO'";
	if(substr($_SESSION['empl']['lokasitugas'],3,1)=='E'){
		$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and (nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%' or nojurnal like '%".$_SESSION['empl']['lokasitugas']."/M0%')
			  order by tanggal desc limit 1";
	}else{
		if($_SESSION['empl']['lokasitugas']=='KACB'){
			$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/SAPI%'
			  order by tanggal desc limit 1";
		}else{
			$str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%'
			  order by tanggal desc limit 1";
		}
	}
	$res=mysql_query($str);
	if(mysql_num_rows($res)>0){

	}else{
        exit(" Error: Proses Gaji has not been processed. ");    
    }
    #---------------------------------------==================================
}

// CEK SPB INPUT/POSTING diambil dari KEBUN_SLAVE_PANEN_DETAIL
// cek spb vs tiket
$spbbelumdiinput='';
$query = "SELECT a.nospb, b.tanggal
    FROM ".$dbname.".`pabrik_timbangan` a
    LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
    WHERE a.`tanggal` LIKE '".$param['periode']."%' and a.`kodeorg` = '".$_SESSION['empl']['lokasitugas']."'
        AND b.`tanggal` is NULL";
$qDetail=mysql_query($query) or die(mysql_error($conn));
while($rDetail=mysql_fetch_assoc($qDetail))
{
    $spbbelumdiinput.=$rDetail['nospb'].', ';
}        
if($spbbelumdiinput!=''){
    $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
    echo "WARNING: Ada SPB bulan lalu yang belum diinput: ".$spbbelumdiinput;
    exit;
}

$spbbelumdiposting='';
$query = "SELECT nospb, tanggal
    FROM ".$dbname.".`kebun_spb_vw`
    WHERE `tanggal` LIKE '".$param['periode']."%' and `blok` like '".$_SESSION['empl']['lokasitugas']."%'
        and posting = 0
        ";
$qDetail=mysql_query($query) or die(mysql_error($conn));
while($rDetail=mysql_fetch_assoc($qDetail))
{
    $spbbelumdiposting.=$rDetail['nospb'].', ';
}        
if($spbbelumdiposting!=''){
    $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
    echo "WARNING: Ada SPB bulan lalu yang belum diposting: ".$spbbelumdiposting;
    exit;
}
//============================================================================== END OF CEK SPB

/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
$listTiket="";
$qJual = "SELECT notransaksi,a.nokontrak
        FROM ".$dbname.".pabrik_timbangan a
        INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
        WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
            "'and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";
//exit("error:".$qJual);
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
            $headErr .= 'Insert Header Error : '.mysql_error()."\n";
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
				
				/**
				 * Cek Nilai Akumulasi Penyusutan (Dari Daftar Asset) dengan Nilai Akumulasi Penyusutan pada Saldo Bulanan
				 */
				// Ambil Nilai Akumulasi Penyusutan
				if($_SESSION['language']=='EN'){
					$zz="b.namatipe1 as namatipe";
				} else {
					$zz="b.namatipe";
				}
				
				$rinci = array();//indra
				$str="select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,".$zz." 
					  from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
					  on a.tipeasset=b.kodetipe    
					  where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
		and jlhblnpenyusutan-(((".substr($param['periode'],0,4)."*12)+".substr($param['periode'],5,2).")-((left(awalpenyusutan,4)*12)+RIGHT(awalpenyusutan,2)))>0
					  and status=1  and a.awalpenyusutan <= '".$param['periode']."' and persendecline=0";
				$res=  mysql_query($str);
				$arrAsset = array();
				while($bar=mysql_fetch_object($res))
				{
					$x=mktime(0,0,0,  intval(substr($bar->awalpenyusutan,5,2)+($bar->jlhblnpenyusutan)),15,substr($bar->awalpenyusutan,0,4));
					$maxperiod=date('Y-m',$x);
					if($param['periode']<$maxperiod) {
					   if(!isset($arrAsset[$bar->tipeasset]['nilai'])) $arrAsset[$bar->tipeasset]['nilai']=0;
					   $arrAsset[$bar->tipeasset]['nilai']+=$bar->bulanan;
					}
					
					$arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
					$arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
				}
				
				//Ambil double declining
				$str="select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.persendecline,a.hargaperolehan,".$zz." 
					 from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
					 on a.tipeasset=b.kodetipe    
					 where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
		and jlhblnpenyusutan-(((".substr($param['periode'],0,4)."*12)+".substr($param['periode'],5,2).")-((left(awalpenyusutan,4)*12)+RIGHT(awalpenyusutan,2)))>0
					 and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.persendecline>'0'";
				$res=  mysql_query($str);
			   
				while($bar=mysql_fetch_object($res)){
					$thnawal=substr($bar->awalpenyusutan,0,4);
					$blnawal=substr($bar->awalpenyusutan,5,2);
					$total=($thnawal*12)+$blnawal;
				
					$thnNow=substr($param['periode'],0,4);
					$blnNow=substr($param['periode'],5,2);
					
					$totalBulanAwal = 12-$blnawal+1;
					$totalTahun = $thnNow-$thnawal-1;
					
					$totalNow=($thnNow*12)+$blnNow+1;
					$selisih=$totalNow-$total;
					$out=0;
					$akumNow = $sekarang = 0;
					
					// Depresiasi s/d akhir tahun
					$before = $sekarang = $bar->hargaperolehan;
					if($totalTahun>-1) {
						$akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
					}
					$sekarang -= $akumNow;
					
					// Depresiasi per Tahun
					if($totalTahun>0) {
						for($i=0;$i<$totalTahun;$i++) {
							$before = $sekarang;
							$akumNow += $sekarang*$bar->persendecline/100;
							$sekarang -= $sekarang*$bar->persendecline/100;
						}
					}
					
					// Depresiasi per Bulan
					$out = $sekarang*($bar->persendecline/100)/12;
					if($bar->jlhblnpenyusutan<$selisih) {
						if($totalTahun>-1) {
							$out = $sekarang - ($bulanNow*$sekarang);
						} else {
							$out = $sekarang - (($bulanNow-$bulanawal+1)*$sekarang);
						}
					}
					
					if(isset($arrAsset[$bar->tipeasset]['nilai'])) {
						$arrAsset[$bar->tipeasset]['nilai']+=$out;
					} else {
						$arrAsset[$bar->tipeasset]['nilai']=$out;
					}
					$arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
					$arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
				}
				
				$poolAsset = array();
				foreach($arrAsset as $row) {
					$poolAsset[$row['kode']] = $row['nilai'];
					//print_r('poolasset = '.$poolAsset.' nilai = '.$nilai);
				}
				//print_r('poolasset = '.$poolAsset.' nilai = '.$nilai);
				// Get List Akun dari Parameter Jurnal = 'DEP'
				$optDep = makeOption($dbname,'keu_5parameterjurnal',"jurnalid,noakunkredit",
									  "kodeaplikasi='DEP'");
				
				// Get Jurnal
				foreach($poolAsset as $kode=>$nilai) {
					// No Jurnal
					$konter ='001';
					$tanggal=$param['periode']."-28";
					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-","",$tanggal)."/".substr($param['kodeorg'],0,4)."/".$kode."/".$konter;
					
					$qJurnal = selectQuery($dbname,'keu_jurnaldt',"jumlah",
										   "nojurnal='".$nojurnal."' and noakun='".$optDep[$kode]."'");
					$resJurnal = fetchData($qJurnal);
					//exit('Warning: nojurnal='.$qJurnal.' dan nilai='.$resJurnal[0]['jumlah']+round($nilai,2));
					if(empty($resJurnal)) {
						exit("Warning: Depresiasi ".$kode." belum terjurnal dengan benar.");
					} else {
						if($resJurnal[0]['jumlah']+round($nilai,2)>0.01) {
							exit("Warning: Depresiasi ".$kode." belum terjurnal dengan benar");
						}
					}
				}
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

        // ambil BJR bulan lalu
        
        // ambil BJR bulan ini
        
        // bila BJRBI<BJRBL imel ke manager n gm ybs
        
        // bila BJRBI>=BJRBL nothing
        
        // bila BJRBL belum ada nothing
        
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
		setIt($sawal[$bar->noakun],0);
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
         if(isset($sawal[$bar->noakun]) and ($sawal[$bar->noakun]!='' or $mtdebet[$bar->noakun]!='' or  $mtkredit[$bar->noakun]!='')){
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
}  ?>