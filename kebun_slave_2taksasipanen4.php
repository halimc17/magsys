<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun4','');
$afdeling = checkPostGet('afdeling4','');
$periode = checkPostGet('periode4','');

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

    # ambil janjang taksasi
    $query = "SELECT left(blok,6) as afdeling, sum(jjgmasak) as jjgmasak, tanggal
        FROM ".$dbname.".`kebun_taksasi`
        WHERE `tanggal` like '".$periode."%' AND `blok` like '".$kebun."%' AND `blok` like '".$afdeling."%' GROUP BY left(blok,6), tanggal ORDER BY left(blok,6)
        ";
    $qDetail=mysql_query($query) or die(mysql_error($conn));
    while($rDetail=mysql_fetch_assoc($qDetail))
    {

        $janjangtaksasi[$rDetail['afdeling']][$rDetail['tanggal']]=$rDetail['jjgmasak'];
        $listafdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
    }

    # ambil janjang panen
    $query = "SELECT left(kodeorg,6) as afdeling, sum(hasilkerja) as janjangpanen, tanggal
        FROM ".$dbname.".`kebun_prestasi_vw`
        WHERE `tanggal` like '".$periode."%' AND `kodeorg` like '".$kebun."%' AND `kodeorg` like '".$afdeling."%' GROUP BY left(kodeorg,6), tanggal ORDER BY left(kodeorg,6)
        ";        
    $qDetail=mysql_query($query) or die(mysql_error($conn));
    while($rDetail=mysql_fetch_assoc($qDetail))
    {
        $janjangpanen[$rDetail['afdeling']][$rDetail['tanggal']]=$rDetail['janjangpanen'];
        $listafdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
    }                          
    
    // With timestamp, this gets last day of April 2010 'Y-m-t'
    $tanggalterakhir = date('t', strtotime($periode.'-01'));
        
//    echo "<pre>";
//    print_r($janjangtaksasi);
//    echo "</pre>";
	$tab="";
    if($proses!='excel'){

    }else{
        $tab.= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." vs ".$_SESSION['lang']['realisasi']."<br>Kebun: ".$kebun." ".$afdeling." ".$periode." ";
    }
    $tab.="
    <table width=100% cellspacing=1 border=".$brd." >
    <thead>
    <tr>
        <td ".$bgcoloraja." rowspan=2>".$_SESSION['lang']['afdeling']."</td>";
    for ($i = 1; $i <= $tanggalterakhir; $i++) {
        if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
        if($proses!='excel')$tab.="<td ".$bgcoloraja." colspan=3 align=center>".tanggalnormal($periode."-".$ii)."</td>";
        else $tab.="<td ".$bgcoloraja." colspan=3 align=center>".$periode."-".$ii."</td>";
    }
    $tab.="</tr><tr>";
    for ($i = 1; $i <= $tanggalterakhir; $i++) {
        if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
        $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['taksasi']." (JJG)</td>";
        $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['panen']." (JJG)</td>";
        $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['selisih']." (%)</td>";
    }
    $tab.="</tr>    
    </thead>
    <tbody>";
    
    foreach($listafdeling as $laf){
        $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$laf."</td>";
            for ($i = 1; $i <= $tanggalterakhir; $i++) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
				setIt($janjangtaksasi[$laf][$periode.'-'.$ii],0);
				setIt($janjangpanen[$laf][$periode.'-'.$ii],0);
                $tab.="<td>".number_format($janjangtaksasi[$laf][$periode.'-'.$ii])."</td>";
                $tab.="<td>".number_format($janjangpanen[$laf][$periode.'-'.$ii])."</td>";
                @$selisih=(abs($janjangtaksasi[$laf][$periode.'-'.$ii]-$janjangpanen[$laf][$periode.'-'.$ii]))/$janjangtaksasi[$laf][$periode.'-'.$ii]*100;
                if($selisih>10)$warna=" bgcolor=red"; else $warna="";
                $tab.="<td align=right".$warna.">".number_format($selisih,2)."</td>";
            }            
        $tab.="</tr>";                        
        
    }
            
//        foreach($dzArr as $datanya){
//        $tab.="<tr class=rowcontent>";
//            if($proses!='excel')$tab.="<td align=right>".tanggalnormal($datanya['tanggal'])."</td>";
//            else $tab.="<td align=right>".$datanya['tanggal']."</td>";
//            $jumlahha=$datanya['hasisa']+$datanya['haesok'];
//            @$pbm=$datanya['jjgmasak']*100/$datanya['jmlhpokok'];
//            @$jjgoutput=$datanya['jjgmasak']/$datanya['hkdigunakan'];
//            $tab.="<td>".$datanya['seksi']."</td>
//            <td>".$datanya['blok']."</td>
//            <td align=right>".number_format($datanya['hasisa'],2)."</td>
//            <td align=right>".number_format($datanya['haesok'],2)."</td>
//            <td align=right>".number_format($jumlahha,2)."</td>
//            <td align=right>".number_format($datanya['jmlhpokok'])."</td>
//            <td align=right>".number_format($pbm,2)."</td>
//            <td align=right>".number_format($datanya['jjgmasak'])."</td>
//            <td align=right>".number_format($jjgoutput)."</td>
//            <td align=right>".number_format($datanya['hkdigunakan'])."</td>
//            <td align=right>".number_format($datanya['hkpanen'])."</td>
//            <td align=right>".number_format($datanya['bjr'],2)."</td>
//            <td align=right>".number_format($datanya['kg'])."</td>";            
//        $tab.="</tr>";                        
//                    
//        }
    
    $tab.="</tbody></table></td></tr></tbody><table>";

}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="taksasi_vs_real".$kebun."_".$afdeling."_".$periode;
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