<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$_POST['proses']==''?$proses=$_GET['proses']:$proses=$_POST['proses'];
$_POST['periode0']==''?$periode=$_GET['periode0']:$periode=$_POST['periode0'];
$_POST['periode2']==''?$periode2=$_GET['periode2']:$periode2=$_POST['periode2'];
$_POST['unit0']==''?$unit=$_GET['unit0']:$unit=$_POST['unit0'];
$_POST['divisi0']==''?$divisi=$_GET['divisi0']:$divisi=$_POST['divisi0'];

if(isset($_GET['kdOrg'])){
    $kdOrg=$_GET['kdOrg'];
}else{
    $kdOrg = '';
}

if($proses=='preview'||$proses=='excel'){
    if($periode=='' and $periode2==''){
        exit("Error: All Date field required");
    }
	if($periode=='' and $periode2!=''){
		$periode=$periode2;
	}
	if($periode!='' and $periode2==''){
		$periode2=$periode;
	}
    $periode=$periode."-01";
    $periode2=$periode2."-".date("t",strtotime($periode2."-01"));
    if($periode>$periode2){
        exit("Error: All Date Wrong...!");
    }
    #ambil data jurnal
	/*
    $sQuery="select a.*,d.namaorganisasi from (SELECT a.nojurnal, a.kodekegiatan, a.kodeblok, a.jumlah
        FROM ".$dbname.".`keu_jurnaldt_vw` a
        WHERE a.`tanggal` LIKE '".$periode."%'
        AND a.kodekegiatan != ''
        AND length( a.kodeblok ) =10
        AND a.jumlah >0) a
		left join ".$dbname.".organisasi d on a.kodeblok=d.kodeorganisasi";  
	*/
	if($unit==''){
		$sQuery="select a.*,d.namaorganisasi from (SELECT a.nojurnal, a.kodekegiatan, a.kodeblok, a.jumlah, a.noakun
				FROM ".$dbname.".`keu_jurnaldt_vw` a
				WHERE a.`tanggal` BETWEEN '".$periode."' and '".$periode2."'
				AND length(a.kodeblok) = 10
				AND (a.noakun like '126%' OR a.noakun like '128%' OR  a.noakun like '6%')) a
				left join ".$dbname.".organisasi d on a.kodeblok=d.kodeorganisasi"; 
	}else{
		$sQuery="select a.*,d.namaorganisasi from (SELECT a.nojurnal, a.kodekegiatan, a.kodeblok, a.jumlah, a.noakun
				FROM ".$dbname.".`keu_jurnaldt_vw` a
				WHERE a.`tanggal` BETWEEN '".$periode."' and '".$periode2."'
				AND a.`kodeorg` = '".$unit."'
                AND a.`kodeblok` LIKE '".$divisi."%'
				AND length(a.kodeblok) = 10
				AND (a.noakun like '126%' OR a.noakun like '128%' OR  a.noakun like '6%')) a
				left join ".$dbname.".organisasi d on a.kodeblok=d.kodeorganisasi
				order by a.kodekegiatan,a.kodeblok,a.nojurnal"; 
	}
	//exit('Warning: '.$sQuery);
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
		/*
        $listKegiatan[$bQuery->kodekegiatan]=$bQuery->kodekegiatan;
        $listBlok[$bQuery->kodeblok]=$bQuery->kodeblok;
        $namaBlok[$bQuery->kodeblok]=$bQuery->namaorganisasi;
        $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['total']+=$bQuery->jumlah;
        if(substr($bQuery->nojurnal,13,4)=='/M0/'){ // HK Perawatan
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/M0/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,7)=='/INVK1/'){ // Material
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/INVK1/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/PNN'){ // Panen
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/PNN']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/KK'){ // Kas Keluar
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/BK'){ // Bank Keluar, digabung ke Kas Keluar
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/SPK'){ // SPK
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/SPK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/CT'){ // Catu Beras
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/CT']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/VHC'){ // Transit Kendaraan
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/VHC']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/M/'){ // Memorial
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/M/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,5)=='/IDC/'){ // IDC
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/IDC/']+=$bQuery->jumlah;
        }else{ // lainnya, harusnya nol
            $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['none']+=$bQuery->jumlah;
        } 
		*/
		$kodekegiatan=($bQuery->kodekegiatan=='' ? $bQuery->noakun.'01' : $bQuery->kodekegiatan);
        $listKegiatan[$kodekegiatan]=$kodekegiatan;
        $listBlok[$bQuery->kodeblok]=$bQuery->kodeblok;
        $namaBlok[$bQuery->kodeblok]=$bQuery->namaorganisasi;
        $dzArr[$kodekegiatan][$bQuery->kodeblok]['total']+=$bQuery->jumlah;
        if(substr($bQuery->nojurnal,13,4)=='/M0/'){ // HK Perawatan
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/M0/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,7)=='/INVK1/'){ // Material
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/INVK1/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/PNN'){ // Panen
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/PNN']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/KK'){ // Kas Keluar
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/BK'){ // Bank Keluar, digabung ke Kas Keluar
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/SPK'){ // SPK
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/SPK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/CT'){ // Catu Beras
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/CT']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/VHC'){ // Transit Kendaraan
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/VHC']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/M/'){ // Memorial
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/M/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,5)=='/IDC/'){ // IDC
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['/IDC/']+=$bQuery->jumlah;
        }else{ // lainnya, harusnya nol
            $dzArr[$kodekegiatan][$bQuery->kodeblok]['none']+=$bQuery->jumlah;
        } 
    }
    
    if(!empty($listKegiatan))sort($listKegiatan);
    if(!empty($listBlok))sort($listBlok);

    #ambil HK dan prestasi perawatan    
    $sQuery="SELECT b.kodekegiatan, b.kodeorg, a.jhk
        FROM ".$dbname.".`kebun_kehadiran` a
        LEFT JOIN ".$dbname.".`kebun_prestasi` b on a.notransaksi=b.notransaksi
        LEFT JOIN ".$dbname.".`kebun_aktifitas` c on b.notransaksi=c.notransaksi
        WHERE c.tanggal BETWEEN '".$periode."' and '".$periode2."'
        AND c.kodeorg = '".$unit."'
        AND b.kodeorg LIKE '".$divisi."%'
        AND c.jurnal =1
        AND b.nik = '-'
        ";  
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
        $dzArr[$bQuery->kodekegiatan][$bQuery->kodeorg]['hk/M0/']+=$bQuery->jhk;
    }    
    
    #ambil prestasi perawatan    
    $sQuery="SELECT b.kodekegiatan, b.kodeorg, b.hasilkerja
        FROM ".$dbname.".`kebun_prestasi` b
        LEFT JOIN ".$dbname.".`kebun_aktifitas` c on b.notransaksi=c.notransaksi
        WHERE c.tanggal BETWEEN '".$periode."' and '".$periode2."'
        AND c.kodeorg = '".$unit."'
        AND b.kodeorg LIKE '".$divisi."%'
        AND c.jurnal =1
        AND b.nik = '-'
        ";  
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
        $dzArr[$bQuery->kodekegiatan][$bQuery->kodeorg]['prestasi/M0/']+=$bQuery->hasilkerja;            
    }    
    
    #ambil hk dan prestasi panen    
    $sQuery="SELECT b.kodeorg, b.hasilkerja
        FROM ".$dbname.".`kebun_prestasi_vw` b
        WHERE b.tanggal BETWEEN '".$periode."' and '".$periode2."'
        AND b.unit LIKE '".$unit."%'
        AND b.kodeorg LIKE '".$divisi."%'
        AND b.jurnal =1
        ";  
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
        $dzArr['611010101'][$bQuery->kodeorg]['hk/PNN']+=1;            
        $dzArr['611010101'][$bQuery->kodeorg]['prestasi/PNN']+=$bQuery->hasilkerja;            
    }    
    
    #ambil prestasi spk    
    $sQuery="SELECT b.kodekegiatan, b.kodeblok, b.hasilkerjarealisasi
        FROM ".$dbname.".`log_baspk` b
        WHERE b.tanggal BETWEEN '".$periode."' and '".$periode2."'
        AND b.kodeblok LIKE '".$unit."%'
        AND b.kodeblok LIKE '".$divisi."%'
        AND b.statusjurnal =1
        ";  
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
        $dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['prestasi/SPK']+=$bQuery->hasilkerjarealisasi;            
    }    
    
    #ambil kegiatan
    $sQuery="select kodekegiatan, namakegiatan,satuan from ".$dbname.".setup_kegiatan
        where 1";  
    $rQuery=mysql_query($sQuery);
    while($bQuery=mysql_fetch_object($rQuery)){
        $dicKegiatan[$bQuery->kodekegiatan]=$bQuery->namakegiatan;
        $dicSatuanKegiatan[$bQuery->kodekegiatan]=$bQuery->satuan;
    }    

    $brd=0;
    if($proses!='excel'){
        // ga ngapa-ngapain
    }else{
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
        $tab.= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['costelement']."<br>Periode: ".$periode."";
    } 
    $tab.="
    <table width=100% cellspacing=1 border=".$brd." >
    <thead>
    <tr>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kodekegiatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kodeblok']."</td>
        <td ".$bgcoloraja." align=center colspan=3>".$_SESSION['lang']['perawatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['material']."</td>
        <td ".$bgcoloraja." align=center colspan=3>".$_SESSION['lang']['panen']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kasbank']."</td>
        <td ".$bgcoloraja." align=center colspan=2>".$_SESSION['lang']['spk']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['pembagiancatu']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kendaraan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['jurnal']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['alokasiidc']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['lain']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['jumlah']."</td>
    </tr>
    <tr>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['jhk']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['jhk']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
    </tr>

    </thead>
    <tbody>";
    
    if(!empty($listBlok))foreach($listBlok as $lBlok){
        if(!empty($listKegiatan))foreach($listKegiatan as $lKegiatan){
            if($dzArr[$lKegiatan][$lBlok]['total']!=0){
                $tab.="
                <tr class=rowcontent>
                <td align=center>".$lKegiatan."</td>
                <td>".$dicKegiatan[$lKegiatan]."</td>
                <td>".$dicSatuanKegiatan[$lKegiatan]."</td>
                <td align=center>".$namaBlok[$lBlok]."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['hk/M0/'],2)."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['prestasi/M0/'],2)."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/M0/'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/INVK1/'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['hk/PNN'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['prestasi/PNN'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/PNN'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/KK'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['prestasi/SPK'],2)."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/SPK'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/CT'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/VHC'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/M/'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['/IDC/'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['none'])."</td>
                <td align=right>".number_format($dzArr[$lKegiatan][$lBlok]['total'])."</td>
                </tr>";
            }
        }
    }
    
    $tab.="</tbody></table>";

}	
switch($proses)
{
    case'getAfdAll':
		$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where kodeorganisasi like '".$kdOrg."%' and length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN','STATION') order by namaorganisasi" ;
        $res=mysql_query($str);
        $optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
        while($bar=mysql_fetch_object($res)) 
        {
           $optDivisi.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
        }
        echo $optDivisi;
    break;
    
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="CostElement_".$periode;
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
          //  closedir($handle);
        }
    break;
    
    default:
    break;
}
?>
