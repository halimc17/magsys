<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('kdOrg','');
$tahun=checkPostGet('tahun','');

$optIP = array('I'=>'Inti','P'=>'Plasma');

if(!isset($_POST['proses'])){
    echo "Generating XLS...";
}

if(($proses=='getData')or($proses=='excel')){
    $tahunkecil='0000';
	$bulankecil='00';
    
    // ambil data awal tahun, kalo ada data tahun ini, timpa yang setup_blok
    $sCek="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdOrg."%' and tahun like '".$tahun."%' and length(tahun)=6 and statusblok in ('TB', 'TBM','TBM-01','TBM-02','TBM-03','TM') and (luasareaproduktif+luasareanonproduktif) > 0";
		// kodeorg like '".$kdOrg."%' and tahun like '".$tahun."%' and length(tahun)=6 and statusblok in ('TB', 'TBM', 'TM') and (luasareaproduktif+luasareanonproduktif) > 0
		// echo $sCek;
    $qCek=mysql_query($sCek) or die(mysql_error());
	$ip = array();
    while($rCek=mysql_fetch_assoc($qCek))
    {
        if($tahunkecil=='0000')$tahunkecil=substr($rCek['tahun'],0,4);
		if($bulankecil=='00')$bulankecil=substr($rCek['tahun'],4,2);
        $listtahun[$rCek['tahun']]=$rCek['tahun'];
        $listkodeorg[$rCek['kodeorg']]=$rCek['kodeorg'];
        $tahuntanam[$rCek['kodeorg']]=$rCek['tahuntanam'];    
        $bibit[$rCek['kodeorg']]=$rCek['jenisbibit'];
		$ip[$rCek['kodeorg']]=$rCek['intiplasma'];
        
        $luasareaproduktif[$rCek['kodeorg']][$rCek['tahun']]=$rCek['luasareaproduktif'];    
//        $luasareanonproduktif[$rCek['kodeorg']][$rCek['tahun']]=$rCek['luasareanonproduktif'];    
        $jumlahpokok[$rCek['kodeorg']][$rCek['tahun']]=$rCek['jumlahpokok'];    
        $statusblok[$rCek['kodeorg']][$rCek['tahun']]=$rCek['statusblok'];
		$tTanam[$rCek['tahuntanam']]=$rCek['tahuntanam'];
    }
    
    // ambil data tanam
    $sCek="select substr(tanggal,1,7) as tahunan, kodeorg, sum(hasilkerja) as tambah from ".$dbname.".kebun_perawatan_vw where kodeorg like '".$kdOrg."%' 
        and tanggal >= '".$tahunkecil."-".$bulankecil."-01'
        and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        group by substr(tanggal,1,7), kodeorg order by kodeorg";
//    $streamline.= $sCek; exit();
    $qCek=mysql_query($sCek) or die(mysql_error());
    while($rCek=mysql_fetch_assoc($qCek))
    {
        $tambah[$rCek['kodeorg']][$rCek['tahunan']]=$rCek['tambah'];    
    }

    // ambil data mutasi kurang
    $sCek="select substr(periode,1,7) as tahunan, blok, sum(pokokmati) as kurang from ".$dbname.".kebun_rencanasisip where blok like '".$kdOrg."%'
        and periode >= '".$tahunkecil."-".$bulankecil."'
        and posting = '1'
        group by substr(periode,1,7), blok order by blok";
//    $streamline.= $sCek; exit();
    $qCek=mysql_query($sCek) or die(mysql_error());
    while($rCek=mysql_fetch_assoc($qCek))
    {
        $kurang[$rCek['blok']][$rCek['tahunan']]=$rCek['kurang'];    
    }
    
//    $jumlahtahun=count($listtahun);

    if($proses=='excel'){
        $bgcolor=" bgcolor=#DEDEDE";
        $border="1";
    }else{
        $bgcolor="";
        $border="0";        
    }
    
    $streamline="<table cellspacing=1 border=".$border.">
    <thead>
    <tr>
    <td align=center rowspan=2".$bgcolor.">".$_SESSION['lang']['kodeorg']."</td>
    <td align=center rowspan=2".$bgcolor.">".$_SESSION['lang']['tahuntanam']."</td>
    <td align=center rowspan=2".$bgcolor.">".$_SESSION['lang']['jenisbibit']."</td>
	<td align=center rowspan=2".$bgcolor.">".$_SESSION['lang']['intiplasma']."</td>";
    if(!empty($listtahun))foreach($listtahun as $tafun){
        $streamline.="<td align=center colspan=6".$bgcolor.">".$tafun."</td>";        
    }
    $streamline.="</tr><tr>";
    if(!empty($listtahun))foreach($listtahun as $tafun){
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['luasareaproduktif']."</td>";        
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['posisiawaltahun']."</td>";        
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['Mutasi1']."</td>";        
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['jumlahpokok']."</td>";        
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['kerapatan']."</td>";        
        $streamline.="<td align=center".$bgcolor.">".$_SESSION['lang']['statusblok']."</td>";        
    }

    $streamline.="</tr></thead>

    <tbody>";
    if(!empty($tTanam))arsort($tTanam);
	
	$jumlahCols = (count($listtahun)*6)+4;
	
	if(!empty($tTanam))foreach($tTanam as $tT){
		if($tT==0){
			$subheader="BIBITAN";
		}else{
			if(($tahun-$tT)==1){
				$subheader="TBM 1";
			}else if(($tahun-$tT)==2){
				$subheader="TBM 2";
			}else if(($tahun-$tT)==3){
				$subheader="TBM 3";
			}else{
				$subheader="TM";
			}
		}
		$streamline.="<tr class=rowcontent>
				<td colspan=".$jumlahCols."><b>".$subheader."</b></td>
				</tr>";
		
		$countData=0;
		if(!empty($listkodeorg)){
			foreach($listkodeorg as $daftar){
				if($tahuntanam[$daftar]==$tT){
					$streamline.="<tr class=rowcontent>
								<td align=center>".$daftar."</td>
								<td align=center>".$tahuntanam[$daftar]."</td>
								<td align=left>".$bibit[$daftar]."</td>
								<td align=left>".$optIP[$ip[$daftar]]."</td>";
				
					if(!empty($listtahun))foreach($listtahun as $tafun){
						setIt($tambah[$daftar][$tafun],0);
						setIt($kurang[$daftar][$tafun],0);
						setIt($jumlahpokok[$daftar][$tafun],0);
						setIt($mutasi[$daftar][$tafun],0);
						setIt($luasareaproduktif[$daftar][$tafun],0);
						$mutasi[$daftar][$tafun]=$tambah[$daftar][$tafun]-$kurang[$daftar][$tafun];
						$jumlahpokok1[$daftar][$tafun]=$jumlahpokok[$daftar][$tafun]+$mutasi[$daftar][$tafun];
						@$rapat[$daftar][$tafun]=$jumlahpokok1[$daftar][$tafun]/$luasareaproduktif[$daftar][$tafun];
						$streamline.="<td align=right>".number_format($luasareaproduktif[$daftar][$tafun],2)."</td>
						<td align=right>".number_format($jumlahpokok[$daftar][$tafun])."</td>
						<td align=right title=\"Details\" style=\"cursor: pointer\" onclick=\"viewData('".$daftar."###".$tafun."','".$_SESSION['lang']['detail']." ".$daftar."','<div id=container></div>',event)\";>".number_format($mutasi[$daftar][$tafun])."</td>
						<td align=right>".number_format($jumlahpokok1[$daftar][$tafun])."</td>
						<td align=right>".number_format(@$rapat[$daftar][$tafun])."</td>
						<td align=center>".$statusblok[$daftar][$tafun]."</td>";  
						
						// $subTotalPosisiAwalTahun+=$jumlahpokok[$daftar][$tafun];
						// $subTotalMutasi+=$mutasi[$daftar][$tafun];
						// $subTotalJumlahPokok+=$jumlahpokok1[$daftar][$tafun];
						// $subTotalKerapatan+=@$rapat[$daftar][$tafun];
					}
					//Get Sub Total
					setIt($subTotal[$tT]['luasareaproduktif'],0);
					setIt($subTotal[$tT]['jumlahpokok'],0);
					setIt($subTotal[$tT]['mutasi'],0);
					setIt($subTotal[$tT]['jumlahpokok1'],0);
					setIt($subTotal[$tT]['rapat'],0);
					$subTotal[$tT]['luasareaproduktif']+=$luasareaproduktif[$daftar][$tafun];
					$subTotal[$tT]['jumlahpokok']+=$jumlahpokok[$daftar][$tafun];
					$subTotal[$tT]['mutasi']+=$mutasi[$daftar][$tafun];
					$subTotal[$tT]['jumlahpokok1']+=$jumlahpokok1[$daftar][$tafun];
					$subTotal[$tT]['rapat']+=@$rapat[$daftar][$tafun];
					
					//Get Grand Total
					setIt($grandTotal['luasareaproduktif'],0);
					setIt($grandTotal['jumlahpokok'],0);
					setIt($grandTotal['mutasi'],0);
					setIt($grandTotal['jumlahpokok1'],0);
					setIt($grandTotal['rapat'],0);
					$grandTotal['luasareaproduktif']+=$luasareaproduktif[$daftar][$tafun];
					$grandTotal['jumlahpokok']+=$jumlahpokok[$daftar][$tafun];
					$grandTotal['mutasi']+=$mutasi[$daftar][$tafun];
					$grandTotal['jumlahpokok1']+=$jumlahpokok1[$daftar][$tafun];
					$grandTotal['rapat']+=@$rapat[$daftar][$tafun];
					
					$streamline.="</tr>";
					$countData+=$countData;
				}
			}
			
			//Print Sub Total
			$streamline.="<tr class=rowcontent>
							<td colspan='4' style='text-align:center'><b>".$_SESSION['lang']['subtotal']." ".$subheader."</b></td>";
			
			if(!empty($listtahun))foreach($listtahun as $tafun){
				$streamline.="<td style='text-align:right'><b>".number_format($subTotal[$tT]['luasareaproduktif'],2)."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($subTotal[$tT]['jumlahpokok'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($subTotal[$tT]['mutasi'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($subTotal[$tT]['jumlahpokok1'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($subTotal[$tT]['rapat'])."</b></td>";
				$streamline.="<td style='text-align:right'><b></b></td>";
			}
			$streamline.="</tr>"; 
			// $grandTotalPlanted+=$subTotalPlanted;
			// $grandTotalPosisiAwalTahun+=$subTotalPosisiAwalTahun;
			// $grandTotalMutasi+=$subTotalMutasi;
			// $grandTotalJumlahPokok+=$subTotalJumlahPokok;
			// $grandTotalKerapatan+=$subTotalKerapatan;
		}else{
			$streamline.="<tr class=rowcontent>";
				$streamline.="<td colspan=4 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";
			$streamline.="</tr>";    
		}
	}
	
	if(!empty($listkodeorg)){
		$streamline.="<tr class=rowcontent><td colspan=".$jumlahCols.">&nbsp;</td></tr>
					<tr class=rowcontent>
								<td colspan='4' style='text-align:center'><b>Grand Total</b></td>";
				
			if(!empty($listtahun))foreach($listtahun as $tafun){
				$streamline.="<td style='text-align:right'><b>".number_format($grandTotal['luasareaproduktif'],2)."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($grandTotal['jumlahpokok'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($grandTotal['mutasi'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($grandTotal['jumlahpokok1'])."</b></td>";
				$streamline.="<td style='text-align:right'><b>".number_format($grandTotal['rapat'])."</b></td>";
				$streamline.="<td style='text-align:right'><b></b></td>";
			}
	}else{
		$streamline.="<tr class=rowcontent>";
		$streamline.="<td colspan=4 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";
		$streamline.="</tr>";
	}
	
	
	// if(!empty($listkodeorg))foreach($listkodeorg as $daftar){
        // $streamline.="<tr class=rowcontent>
        // <td align=center>".$daftar."</td>
        // <td align=center>".$tahuntanam[$daftar]."</td>
        // <td align=left>".$bibit[$daftar]."</td>
		// <td align=left>".$optIP[$ip[$daftar]]."</td>";
		
        // if(!empty($listtahun))foreach($listtahun as $tafun){
			// setIt($tambah[$daftar][$tafun],0);
			// setIt($kurang[$daftar][$tafun],0);
			// setIt($jumlahpokok[$daftar][$tafun],0);
			// setIt($mutasi[$daftar][$tafun],0);
			// setIt($luasareaproduktif[$daftar][$tafun],0);
			// $mutasi[$daftar][$tafun]=$tambah[$daftar][$tafun]-$kurang[$daftar][$tafun];
            // $jumlahpokok1[$daftar][$tafun]=$jumlahpokok[$daftar][$tafun]+$mutasi[$daftar][$tafun];
            // @$rapat[$daftar][$tafun]=$jumlahpokok1[$daftar][$tafun]/$luasareaproduktif[$daftar][$tafun];
            // $streamline.="<td align=right>".number_format($luasareaproduktif[$daftar][$tafun],2)."</td>
            // <td align=right>".number_format($jumlahpokok[$daftar][$tafun])."</td>
            // <td align=right title=\"Details\" style=\"cursor: pointer\" onclick=\"viewData('".$daftar."###".$tafun."','".$_SESSION['lang']['detail']." ".$daftar."','<div id=container></div>',event)\";>".number_format($mutasi[$daftar][$tafun])."</td>
            // <td align=right>".number_format($jumlahpokok1[$daftar][$tafun])."</td>
            // <td align=right>".number_format(@$rapat[$daftar][$tafun])."</td>
            // <td align=center>".$statusblok[$daftar][$tafun]."</td>";            
        // }
        // $streamline.="</tr>";    
    // }else{
        // $streamline.="<tr class=rowcontent>";
            // $streamline.="<td colspan=4 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";
        // $streamline.="</tr>";    
    // }
    $streamline.="</tbody>
    <table>";    
}

switch($proses)
{
//load data
case'getData':
    echo"<fieldset>
    <legend>".$_SESSION['lang']['list']." ".$kdOrg." ".$tahun."</legend>";

    echo $streamline;
    
    echo"</fieldset>";
break;

case'excel':
    $streamline.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
    $nop_="HistorisAresta_".$kdOrg."_".$tahun;
    if(strlen($streamline)>0)
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
        if(!fwrite($handle,$streamline))
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
       // closedir($handle);
    }    
break;
    
case'ShowData':
    function baliktanggal($lama){
        $qwe=explode('-',$lama);
        $balikin=$qwe[2]."-".$qwe[1]."-".$qwe[0];
        return $balikin;
    }
    echo"<link rel=stylesheet type=text/css href=\"style/zTable.css\">";
    echo"<fieldset><legend>".$_SESSION['lang']['Mutasi1']."+ ".$tahun."</legend><table cellspacing=1 border=0>
    <thead>
    <tr>
    <td align=center>".$_SESSION['lang']['notransaksi']."</td>
    <td align=center>".$_SESSION['lang']['tanggal']."</td>
    <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
    <td align=center>".$_SESSION['lang']['jumlah']."</td>
    </tr>
    </thead>
    <tbody>";
    
    // kamus kode kegiatan
    $sCek="select * from ".$dbname.".setup_kegiatan
        where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')";
    $qCek=mysql_query($sCek) or die(mysql_error());
    while($rCek=mysql_fetch_assoc($qCek))
    {
        $kamuskegiatan[$rCek['kodekegiatan']]=$rCek['namakegiatan'];
    }
                    
    // ambil data mutasi tambah
    $sCek="select * from ".$dbname.".kebun_perawatan_vw 
        where kodeorg like '".$kdOrg."%' and tanggal like '".$tahun."%'
        and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        order by tanggal, notransaksi";
//    echo $sCek;
    $qCek=mysql_query($sCek) or die(mysql_error());
    while($rCek=mysql_fetch_assoc($qCek))
    {
        echo"<tr class=rowcontent>";
        echo"<td>".$rCek['notransaksi']."</td>";
        echo"<td>".baliktanggal($rCek['tanggal'])."</td>";
        echo"<td>".$kamuskegiatan[$rCek['kodekegiatan']]."</td>";
        echo"<td align=right>".number_format($rCek['hasilkerja'])."</td>";
        echo"</tr>";
        $totaltambah+=$rCek['hasilkerja'];    
    }
    echo"<tr class=rowcontent>";
    echo"<td colspan=3>".$_SESSION['lang']['total']."+</td>";
    echo"<td align=right>".number_format($totaltambah)."</td>";
    echo"</tr>";
                    
    echo"</tbody></table></fieldset>";    
    
    echo"<fieldset><legend>".$_SESSION['lang']['Mutasi1']."- ".$tahun."</legend><table cellspacing=1 border=0>
    <thead>
    <tr>
    <td align=center>".$_SESSION['lang']['periode']."</td>
    <td align=center>".$_SESSION['lang']['rencanasisip']."</td>
    <td align=center>".$_SESSION['lang']['keterangan']."</td>
    <td align=center>".$_SESSION['lang']['pokokmati']."</td>
    </tr>
    </thead>
    <tbody>";
    
    // ambil data mutasi kurang
    $sCek="select * from ".$dbname.".kebun_rencanasisip
        where blok like '".$kdOrg."%' and periode like '".$tahun."%'
        and posting = '1'
        order by periode";
//    echo $sCek;
    $qCek=mysql_query($sCek) or die(mysql_error());
    while($rCek=mysql_fetch_assoc($qCek))
    {
        echo"<tr class=rowcontent>";
        echo"<td>".$rCek['periode']."</td>";
        echo"<td align=right>".number_format($rCek['rencanasisip'])."</td>";
        echo"<td>".$rCek['keterangan']."</td>";
        echo"<td align=right>".number_format($rCek['pokokmati'])."</td>";
        echo"</tr>";
        $totalkurang+=$rCek['pokokmati'];    
    }
    echo"<tr class=rowcontent>";
    echo"<td colspan=3>".$_SESSION['lang']['total']."-</td>";
    echo"<td align=right>".number_format($totalkurang)."</td>";
    echo"</tr>";
                    
    echo"</tbody></table></fieldset>";        
break;

default:
break;
        }
?>