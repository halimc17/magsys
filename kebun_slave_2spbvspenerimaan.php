<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$traksiId = checkPostGet('traksiId','');
$periode = checkPostGet('periode','');
$afdId = checkPostGet('afdId','');

if($proses=='preview'||$proses=='excel'){

    if($traksiId!='')
    {
        $whr=" and  b.kodeorg='".$traksiId."'";
        $whrpab=" and kodeorg='".$traksiId."'";
    }
    if($afdId!='')
    {
        $whr=" and a.nospb like '%".$afdId."%'";
        $whrpab=" and nospb like '%".$afdId."%'";
    }

    if($periode=='')
    {
         exit("Error:Field Tidak Boleh Kosong");
    }
    $brd=0;
    if($proses=='excel')
    {
        $brd=1;
         $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
#ambil spb kebun
$str="SELECT a.nospb,sum(a.jjg) as jjg,b.tanggal,substr(a.nospb,9,6) as afdeling FROM ".$dbname.".kebun_spbdt a
           left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb where b.tanggal like '".$periode."%' ".$whr." group by a.nospb
           order by tanggal,nospb";    
$reskebun=mysql_query($str);
#ambil  spb timbangan
$sPabrik="select nospb,(jumlahtandan1+jumlahtandan2+jumlahtandan3) as jjgpabrik,beratbersih,
                notransaksi,left(tanggal,10) as tanggal from ".$dbname.".pabrik_timbangan where 
          left(tanggal,10)!='' ".$whrpab." and nospb!='' 
          and left(tanggal,10) like '".$periode."%' order by tanggal,nospb";
$respabrik=mysql_query($sPabrik);
$nospb=$afd=$tglkebun=$jjgkebun=$nospbkebun=array();

#ambil semua no spb di pks maupun kebun
while($bar=mysql_fetch_object($reskebun)){
	setIt($nospb[$bar->nospb],'');
	setIt($afd[$bar->nospb],'');
	setIt($tglkebun[$bar->nospb],'');
	setIt($jjgkebun[$bar->nospb],0);
	setIt($nospbkebun[$bar->nospb],'');
    $nospb[$bar->nospb].=$bar->nospb.' ';
    $afd[$bar->nospb].=$bar->afdeling.' ';
    $tglkebun[$bar->nospb].=$bar->tanggal.' ';
    $jjgkebun[$bar->nospb]+=$bar->jjg.' ';
    $nospbkebun[$bar->nospb].=$bar->nospb.' ';
}
while($bar1=mysql_fetch_object($respabrik)){
	setIt($nospb[$bar1->nospb],'');
	setIt($tglpabrik[$bar1->nospb],'');
	setIt($tiket[$bar1->nospb],'');
	setIt($beratbersih[$bar1->nospb],0);
	setIt($jjgpabrik[$bar1->nospb],'');
	setIt($nosppabrik[$bar1->nospb],'');
    $nospb[$bar1->nospb].=$bar1->nospb.' ';
    $tglpabrik[$bar1->nospb].=$bar1->tanggal.' ';
    $tiket[$bar1->nospb].=$bar1->notransaksi.' ';   
    $beratbersih[$bar1->nospb]+=$bar1->beratbersih.' ';  
    $jjgpabrik[$bar1->nospb]+=$bar1->jjgpabrik.' ';    
    $nosppabrik[$bar1->nospb].=$bar1->nospb.' ';   
}
$bgcoloraja="";
$tab="
<table cellspacing=1 border=".$brd." >
<thead>
<tr><td align=center colspan=5>".$_SESSION['lang']['kebun']."</td>
<td align=center colspan=5>".$_SESSION['lang']['pabrik']."</td></tr>
            <tr class=rowheader>
            <td ".$bgcoloraja.">No</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['kodeorg']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['nospb']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['tglNospb']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['jjg']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['nospb']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['notransaksi']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['berat']."</td>
            <td ".$bgcoloraja.">".$_SESSION['lang']['jjg']."</td>
            </tr>
</thead><tbody>";
if(isset($nospb)){
    $no=0;
    foreach($nospb as $spb=>$val){
        $no++;
		if(!isset($nospbkebun[$spb])){
            $colorkebun='red';
        } else { 
			$colorkebun='#D1E3BA';
		}
        if(!isset($nosppabrik[$spb])){
            $colorpabrik='red';
        } else { 
			$colorpabrik='#CEDCDE';
		}
		
		setIt($afd[$spb],'');
		setIt($nospbkebun[$spb],'');
		setIt($tglkebun[$spb],'');
		setIt($jjgkebun[$spb],0);
		setIt($tglpabrik[$spb],'');
		setIt($nosppabrik[$spb],'');
		setIt($tiket[$spb],'');
		setIt($beratbersih[$spb],0);
		setIt($jjgpabrik[$spb],0);
		
		setIt($totaljjgkebun,0);
		setIt($totalberatbersih,0);
		setIt($totaljjgpabrik,0);
		$tab.="<tr class=rowcontent>
			<td>".$no."</td>
			<td bgcolor=".$colorkebun.">".$afd[$spb]."</td>
			<td bgcolor=".$colorkebun.">".$nospbkebun[$spb]."</td>
			<td bgcolor=".$colorkebun.">".tanggalnormal($tglkebun[$spb])."</td>
			<td bgcolor=".$colorkebun." align=right>".number_format($jjgkebun[$spb])."</td> 
			<td bgcolor=".$colorpabrik.">".$tglpabrik[$spb]."</td>
			<td bgcolor=".$colorpabrik.">".$nosppabrik[$spb]."</td>
			<td bgcolor=".$colorpabrik.">".$tiket[$spb]."</td>
			<td align=right bgcolor=".$colorpabrik.">".number_format($beratbersih[$spb])."</td>
			<td align=right bgcolor=".$colorpabrik.">".number_format($jjgpabrik[$spb])."</td>
			</tr>";        
		$totaljjgkebun+=$jjgkebun[$spb];
		$totalberatbersih+=$beratbersih[$spb];
		$totaljjgpabrik+=$jjgpabrik[$spb];     
    }
          $tab.="<tr class=rowcontent>
            <td></td>
            <td bgcolor=".$colorkebun."></td>
            <td bgcolor=".$colorkebun."></td>
            <td bgcolor=".$colorkebun.">Total</td>
            <td bgcolor=".$colorkebun." align=right>".number_format($totaljjgkebun)."</td> 
            <td bgcolor=".$colorpabrik."></td>
            <td bgcolor=".$colorpabrik."></td>
            <td bgcolor=".$colorpabrik.">Total</td>
            <td align=right bgcolor=".$colorpabrik.">".number_format($totalberatbersih)."</td>
            <td align=right bgcolor=".$colorpabrik.">".number_format($totaljjgpabrik)."</td>
            </tr>";                
    
}
 $tab.="</tbody></table></td></tr></tbody><table>";

}	
switch($proses)
{
        case'preview':
        echo $tab;
        break;

        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="spbvstimbangan__".$traksiId."__".$periode;
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

        case'getPrd':
            //$traksiId
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPrd="select distinct left(tanggal,7) as periode from ".$dbname.".kebun_spbht 
               where kodeorg = '".$traksiId."' order by left(tanggal,7) desc";
        $qPrd=mysql_query($sPrd) or die(mysql_error($conn));
        while($rPrd=  mysql_fetch_assoc($qPrd)){
            $optPeriode.="<option value=".$rPrd['periode'].">".$rPrd['periode']."</option>";
        }
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sPrd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$traksiId."' and tipe='afdeling' order by namaorganisasi asc";
        $qPrd=mysql_query($sPrd) or die(mysql_error($conn));
        while($rPrd=  mysql_fetch_assoc($qPrd)){
            $optAfd.="<option value=".$rPrd['kodeorganisasi'].">".$rPrd['namaorganisasi']."</option>";
        }
        echo $optPeriode."####".$optAfd;
        break;
        default:
        break;
}
?>