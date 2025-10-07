<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun3','');
$afdeling = checkPostGet('afdeling3','');
$periode = checkPostGet('periode3','');

if($proses=='preview'||$proses=='excel'){
    if($periode==''||$kebun==''){
        exit("Error: All field required");
    }
    
    $brd=0;
	$bgcoloraja="";
    if($proses=='excel'){
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
//    #ambil data timbangan
//    $sPabrik="select nospb,kodeorg, (jumlahtandan1+jumlahtandan2+jumlahtandan3) as jjgpabrik,beratbersih,
//        notransaksi,left(tanggal,10) as tanggal, substr(nospb,9,6) as afdeling from ".$dbname.".pabrik_timbangan 
//        where left(tanggal,10)!='' and kodeorg = '".$kebun."' and nospb!='' and substr(nospb,9,6) like '".$afdeling."%'
//        and left(tanggal,10) like '".$periode."%' order by substr(nospb,9,6)";  
//    $respabrik=mysql_query($sPabrik);
//    while($bar0=mysql_fetch_object($respabrik)){
//        $kunci2=$kebun.$bar0->tanggal;
//        
//        $dzArr[$kunci2]['p_kg']+=$bar0->beratbersih;
//    }
    


    #ambil afdeling
//    $sPabrik="select kodeorganisasi from ".$dbname.".organisasi
//        where kodeorganisasi like '".$kebun."%' and kodeorganisasi like '".$afdeling."%' and tipe = 'AFDELING'";  
//    $respabrik=mysql_query($sPabrik);
//    while($bar0=mysql_fetch_object($respabrik)){
//        $listafd[$bar0->kodeorganisasi]=$bar0->kodeorganisasi;
//    }
    
    #ambil data taksasi
    $sTaksasi="select a.afdeling, a.tanggal, a.blok, b.namaorganisasi as namablok, a.seksi, a.hasisa, a.haesok, a.jmlhpokok, a.persenbuahmatang, a.jjgmasak, a.jjgoutput, a.hkdigunakan, a.bjr, (a.bjr*a.jjgmasak) as kg from ".$dbname.".kebun_taksasi a
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.blok
		        where a.afdeling like '".$kebun."%' and a.afdeling like '%".$afdeling."%' and a.tanggal like '".$periode."%' 
				order by a.tanggal, a.blok";
    $restaksasi=mysql_query($sTaksasi);
    while($bar1=mysql_fetch_object($restaksasi)){        
        $kunci=$bar1->tanggal.$bar1->blok;
        
        @$kurangdarienam=($bar1->haesok+$bar1->hasisa)/$bar1->hkdigunakan;
        if($kurangdarienam <= 6){
            $bisapanen=$bar1->hkdigunakan;
        }else{
            $bisapanen=ceil(($bar1->haesok+$bar1->hasisa)/6);
        }
		setIt($dzArr[$kunci]['hkpanen'],0);
		setIt($dzArr[$kunci]['counter'],0);
		setIt($dzArr[$kunci]['blok'],'');
		setIt($dzArr[$kunci]['seksi'],'');
		setIt($dzArr[$kunci]['hasisa'],0);
		setIt($dzArr[$kunci]['haesok'],0);
		setIt($dzArr[$kunci]['jmlhpokok'],0);
		setIt($dzArr[$kunci]['pbm'],0);
		setIt($dzArr[$kunci]['jjgmasak'],0);
		setIt($dzArr[$kunci]['hkdigunakan'],0);
		setIt($dzArr[$kunci]['kg'],0);
		setIt($dzArr[$kunci]['tanggal'],'');
        $dzArr[$kunci]['hkpanen']+=$bisapanen;    
        $dzArr[$kunci]['counter']+=1; // jumlahdata
        $dzArr[$kunci]['afdeling']=$bar1->afdeling;
        $dzArr[$kunci]['blok'].=$bar1->namablok;
        $dzArr[$kunci]['tanggal'].=$bar1->tanggal;
        $dzArr[$kunci]['seksi'].=$bar1->seksi;
        $dzArr[$kunci]['hasisa']+=$bar1->hasisa;
        $dzArr[$kunci]['haesok']+=$bar1->haesok;
        $dzArr[$kunci]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$kunci]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$kunci]['persenbuahmatang']=$dzArr[$kunci]['pbm']/$dzArr[$kunci]['counter'];
        $dzArr[$kunci]['jjgmasak']+=$bar1->jjgmasak;
//        $dzArr[$kunci]['jjgoutput']+=$bar1->jjgoutput;
        $dzArr[$kunci]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$kunci]['kg']+=$bar1->kg;
        @$dzArr[$kunci]['bjr']=$dzArr[$kunci]['kg']/$dzArr[$kunci]['jjgmasak'];
    }
    
//    echo "<pre>";
//    print_r($dzArr);
//    echo "</pre>";
    
    #ambil data taksasi total
//    $sTaksasi="select afdeling, tanggal, blok, seksi, hasisa, haesok, jmlhpokok, persenbuahmatang, jjgmasak, jjgoutput, hkdigunakan, bjr, (bjr*jjgmasak) as kg from ".$dbname.".kebun_taksasi 
//        where afdeling like '".$kebun."%' and tanggal like '".$periode."%'
//        order by tanggal, blok";    
//    $restaksasi=mysql_query($sTaksasi);
//    while($bar1=mysql_fetch_object($restaksasi)){        
//        $kunci2=$kebun.$bar1->tanggal.$bar1->blok;
//        
//        $dzArr[$kunci2]['hkdigunakan']+=$bar1->hkdigunakan;
//        $dzArr[$kunci2]['hasisa']+=$bar1->hasisa;
//        $dzArr[$kunci2]['haesok']+=$bar1->haesok;
//        $dzArr[$kunci2]['jmlhpokok']+=$bar1->jmlhpokok;
//        $dzArr[$kunci2]['jjgmasak']+=$bar1->jjgmasak;
//        $dzArr[$kunci2]['kg']+=$bar1->kg;
//    }        
	$tab="";
    if($proses=='excel'){
        $tab.= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." ".$_SESSION['lang']['harian']."<br>Kebun: ".$kebun." ".$afdeling." ".$periode." ";
    }
    $tab.="
    <table width=100% cellspacing=1 border=".$brd." >
    <thead>
    <tr>
        <td ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['section']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['blok']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['hasisa']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['haesok']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['jumlahha']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['jmlhpokok']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['persenbuahmatang']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['jjgmasak']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['jjgoutput']."</td>
        <td ".$bgcoloraja.">HK Output</td>
        <td ".$bgcoloraja.">HK Dipekerjakan</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['bjr']."</td>
        <td ".$bgcoloraja.">".$_SESSION['lang']['taksasi']." (kg)</td>";
        $tab.="
    </tr>
    </thead>
    <tbody>";
    
    // With timestamp, this gets last day of April 2010 'Y-m-t'
//    $tanggalterakhir = date('t', strtotime($periode.'-01'));
    
//    $kgsdhi=0;
//    $p_kgsdhi=0;
//    $varian_kgsdhi=0;
//    for ($i = 1; $i <= $tanggalterakhir; $i++) {
//        if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
//        $kunci2=$kebun.$periode.'-'.$ii;
//        $jumlahha2=$dzArr[$kunci2]['hasisa']+$dzArr[$kunci2]['haesok'];
        
        foreach($dzArr as $datanya){
        $tab.="<tr class=rowcontent>";
			setIt($datanya['kg'],0);
			setIt($datanya['hasisa'],0);
			setIt($datanya['haesok'],0);
			setIt($datanya['jmlhpokok'],0);
			setIt($datanya['jjgmasak'],0);
			setIt($datanya['hkdigunakan'],0);
			setIt($datanya['hkpanen'],0);
			setIt($datanya['bjr'],0);
			setIt($datanya['kg'],0);
			if($proses!='excel')$tab.="<td align=right>".tanggalnormal($datanya['tanggal'])."</td>";
            else $tab.="<td align=right>".$datanya['tanggal']."</td>";
            $jumlahha=$datanya['hasisa']+$datanya['haesok'];
            @$pbm=$datanya['jjgmasak']*100/$datanya['jmlhpokok'];
            @$jjgoutput=$datanya['jjgmasak']/$datanya['hkdigunakan'];
            $tab.="<td>".$datanya['seksi']."</td>
            <td>".$datanya['blok']."</td>
            <td align=right>".number_format($datanya['hasisa'],2)."</td>
            <td align=right>".number_format($datanya['haesok'],2)."</td>
            <td align=right>".number_format($jumlahha,2)."</td>
            <td align=right>".number_format($datanya['jmlhpokok'])."</td>
            <td align=right>".number_format($pbm,2)."</td>
            <td align=right>".number_format($datanya['jjgmasak'])."</td>
            <td align=right>".number_format($jjgoutput)."</td>
            <td align=right>".number_format($datanya['hkdigunakan'])."</td>
            <td align=right>".number_format($datanya['hkpanen'])."</td>
            <td align=right>".number_format($datanya['bjr'],2)."</td>
            <td align=right>".number_format($datanya['kg'])."</td>";            
        $tab.="</tr>";                        
                    
        }
        
//        $kgsdhi+=$dzArr[$kunci2]['kg'];
//        $p_kgsdhi+=$dzArr[$kunci2]['p_kg'];
//        $varian_kg=$dzArr[$kunci2]['p_kg']-$dzArr[$kunci2]['kg'];
//        $varian_kgsdhi+=$varian_kg;
//        @$varian_ps=100-($dzArr[$kunci2]['p_kg']-$dzArr[$kunci2]['kg'])/$dzArr[$kunci2]['p_kg']*100;
//        @$varian_pssdhi=100-($p_kgsdhi-$kgsdhi)/$p_kgsdhi*100;
//        if($dzArr[$kunci2]['kg']==0)$varian_ps=0;
//        
//        $tab.="<tr class=rowcontent>
//        <td align=right>".$i."</td>";
//        if(!empty($listafd))foreach($listafd as $laf){
//            $kunci=$laf.$periode.'-'.$ii;
//            $jumlahha=$dzArr[$kunci]['hasisa']+$dzArr[$kunci]['haesok'];
//            @$pbm=$dzArr[$kunci]['jjgmasak']*100/$dzArr[$kunci]['jmlhpokok'];
//            @$jjgoutput=$dzArr[$kunci]['jjgmasak']/$dzArr[$kunci]['hkdigunakan'];
//            $tab.="<td>".$dzArr[$kunci]['seksi']."</td>
//            <td>".$dzArr[$kunci]['blok']."</td>
//            <td align=right>".number_format($dzArr[$kunci]['hasisa'],2)."</td>
//            <td align=right>".number_format($dzArr[$kunci]['haesok'],2)."</td>
//            <td align=right>".number_format($jumlahha,2)."</td>
//            <td align=right>".number_format($dzArr[$kunci]['jmlhpokok'])."</td>
//            <td align=right>".number_format($pbm,2)."</td>
//            <td align=right>".number_format($dzArr[$kunci]['jjgmasak'])."</td>
//            <td align=right>".number_format($jjgoutput)."</td>
//            <td align=right>".number_format($dzArr[$kunci]['hkdigunakan'])."</td>
//            <td align=right>".number_format($dzArr[$kunci]['hkpanen'])."</td>
//            <td align=right>".number_format($dzArr[$kunci]['bjr'],2)."</td>
//            <td align=right>".number_format($dzArr[$kunci]['kg'])."</td>";            
//        }
//        @$pbm2=$dzArr[$kunci2]['jjgmasak']*100/$dzArr[$kunci2]['jmlhpokok'];
//        $tab.="
//        <td align=right>".number_format($dzArr[$kunci2]['hkdigunakan'])."</td>
//        <td align=right>".number_format($jumlahha2,2)."</td>
//        <td align=right>".number_format($pbm2,2)."</td>
//        <td align=right>".number_format($dzArr[$kunci2]['kg'])."</td>
//        <td align=right>".number_format($kgsdhi)."</td>
//
//        </tr>";                        
        
//    }
    
    $tab.="</tbody></table></td></tr></tbody><table>";

}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="taksasi_pertgl_perblok".$kebun."_".$afdeling."_".$periode;
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
            fclose($handle);
        }
    break;
    
    default:
    break;
}
?>