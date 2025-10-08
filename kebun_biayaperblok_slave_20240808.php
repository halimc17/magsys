<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');

	$proses		=checkPostGet('proses','');
	$kodeunit	=checkPostGet('kodeunit','');
	$kodedivisi	=checkPostGet('kodedivisi','');
	$periode	=checkPostGet('periode','');
	switch($proses){
		case 'getDivisi':
			$sDiv ="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1'
					and kodeorganisasi like '".$kodeunit."%' order by kodeorganisasi";
			$qDiv =mysql_query($sDiv) or die(mysql_error($conn));
			$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rDiv=mysql_fetch_assoc($qDiv)){
				$optDivisi.="<option value=".$rDiv['kodeorganisasi'].">".$rDiv['namaorganisasi']."</option>";
			}
			echo $optDivisi;
			exit;
	}
	if($kodeunit!=''){
		$where="kodeorg like '".$kodeunit."%'";
		$kodeunit2=$kodeunit;
	}else{
		exit("Error: Estate is obligatory");
	}
	if($kodedivisi!=''){
		$where="kodeorg like '".$kodedivisi."%'";
		$kodeunit=$kodedivisi;
	}
	if($periode!=''){
		$where.=" and tanggal like '".$periode."%'";
	}else{
		exit("Error: Date is obligatory");
	}
	$optNamaPT=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$optNamaBlok=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$namapt=$optNamaBlok[$optNamaPT[$kodeunit2]];
	$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
	$tglAwal=$periode."-01";
	$lastday = date('t',strtotime($tglAwal));
	$tglAkhir=$periodeData."-".$lastday;
	$laluAwal=(substr($periode,5,2)=='01' ? (substr($periode,0,4)-1).'-12-01' : (substr($periode,0,4)-7).'-'.sprintf('%02d',substr($periode,5,2)-1).'-01');
	$laluAkhir=date('Y-m-t',strtotime($laluAwal));
	$dtKodeBlok=array();
	//Jurnal
	$sBlok="select tanggal,noakun,kodeblok,kodebarang,kodevhc,jumlah from ".$dbname.".keu_jurnaldt
			where LENGTH(kodeblok)=10 and (left(noakun,1) in ('6','7') or left(noakun,3) in ('126','128')) 
			and kodeblok like '".$kodeunit."%' and tanggal like '".$periode."%' 
			and nojurnal like '%/".$kodeunit2."/%'
			order by kodeblok,tanggal";
	//exit("Warning: ".$sBlok);
	$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
	while($rBlok=mysql_fetch_assoc($qBlok)){
		$xbln=date('n', strtotime($rBlok['tanggal']));
		$xblok=$rBlok['kodeblok'];
		$dtKodeBlok[$rBlok['kodeblok']]=$rBlok['kodeblok'];
		if((substr($rBlok['noakun'],0,1)=='6' or substr($rBlok['noakun'],0,1)=='1') and $rBlok['kodebarang']=='' and $rBlok['kodevhc']==''){
			$upah[$xblok][$xbln]+=$rBlok['jumlah'];
		}else if((substr($rBlok['noakun'],0,1)=='6' or substr($rBlok['noakun'],0,1)=='1') and $rBlok['kodebarang']!=''){
			$material[$xblok][$xbln]+=$rBlok['jumlah'];
		}else if((substr($rBlok['noakun'],0,1)=='6' or substr($rBlok['noakun'],0,1)=='1') and $rBlok['kodevhc']!=''){
			$transport[$xblok][$xbln]+=$rBlok['jumlah'];
		}else{
			$umum[$xblok][$xbln]+=$rBlok['jumlah'];
		}
	}

	$dcek=count($dtKodeBlok);
	if($dcek==0){
		exit("Warning: Data Kosong");
	}
	sort($dtKodeBlok);
	$brd=0;
	$bgcolordt="";
	if($proses=='excel'){
		$bgcolordt="bgcolor=#DEDEDE";
		$brd=1;
	}
	$tab="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable>";
	$tab.="<thead><tr>";
	$tab.="<td ".$bgcolordt." rowspan=2 align=center >No</td>";
	$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['divisi']."</td>";
	$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['blok']."</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tab.="<td ".$bgcolordt." colspan=5 align=center>".$arrBln[$xbln]."</td>";
	}
	$tab.="<td ".$bgcolordt." colspan=5 align=center>Total</td>";
	$tab.="</tr><tr>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tab.="<td ".$bgcolordt." align=center>".$_SESSION['lang']['upah']."</td>";
		$tab.="<td ".$bgcolordt." align=center>Material</td>";
		$tab.="<td ".$bgcolordt." align=center>Transport</td>";
		$tab.="<td ".$bgcolordt." align=center>".$_SESSION['lang']['umum']."</td>";
		$tab.="<td ".$bgcolordt." align=center>Total</td>";
	}
	$tab.="<td ".$bgcolordt." align=center>".$_SESSION['lang']['upah']."</td>";
	$tab.="<td ".$bgcolordt." align=center>Material</td>";
	$tab.="<td ".$bgcolordt." align=center>Transport</td>";
	$tab.="<td ".$bgcolordt." align=center>".$_SESSION['lang']['umum']."</td>";
	$tab.="<td ".$bgcolordt." align=center>Total</td>";
	$tab.="</tr></thead><tbody>";
	$no=0;
	$nodiv=0;
	$stluasBlok=0;
	$stupah=Array();
	$stmaterial=Array();
	$sttransport=Array();
	$stumum=Array();
	$sttotal=Array();
	$gtluasBlok=0;
	$gtupah=Array();
	$gtmaterial=Array();
	$gttransport=Array();
	$gtumum=Array();
	$gttotal=Array();
	$xdiv='xx';
	foreach($dtKodeBlok as $xblok){
		$no+=1;
		if($no!=1 and substr($xblok,0,6)!=$xdiv){
			$nodiv+=1;
            $tab.="<tr class=rowcontent>";
	        $tab.="<td bgcolor=#ffcc99 colspan=3 align=center>Total ".$xdiv."</td>";
			for ($xbln=1; $xbln<=12; $xbln++){
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stupah[$xbln],2)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stmaterial[$xbln],2)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttransport[$xbln],2)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stumum[$xbln],2)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttotal[$xbln],2)."</td>";
				$stupah[$xbln]=0;
				$stmaterial[$xbln]=0;
				$sttransport[$xbln]=0;
				$stumum[$xbln]=0;
				$sttotal[$xbln]=0;
			}
			$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttupah,2)."</td>";
			$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttmaterial,2)."</td>";
			$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttttransport,2)."</td>";
			$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttumum,2)."</td>";
			$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttttotal,2)."</td>";
			$tab.="</tr>";
			$no=1;
			$stttupah=0;
			$stttmaterial=0;
			$sttttransport=0;
			$stttumum=0;
			$sttttotal=0;
		}
		$xdiv=substr($xblok,0,6);
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".$xdiv."</td>";
		$tab.="<td>".$optNamaBlok[$xblok]."</td>";
		for ($xbln=1; $xbln<=12; $xbln++){
			$tab.="<td align=right>".number_format($upah[$xblok][$xbln],2)."</td>";
			$tab.="<td align=right>".number_format($material[$xblok][$xbln],2)."</td>";
			$tab.="<td align=right>".number_format($transport[$xblok][$xbln],2)."</td>";
			$tab.="<td align=right>".number_format($umum[$xblok][$xbln],2)."</td>";
			$tab.="<td align=right>".number_format($upah[$xblok][$xbln]+$material[$xblok][$xbln]+$transport[$xblok][$xbln]+$umum[$xblok][$xbln],2)."</td>";
			$ttupah[$xblok]+=$upah[$xblok][$xbln];
			$ttmaterial[$xblok]+=$material[$xblok][$xbln];
			$tttransport[$xblok]+=$transport[$xblok][$xbln];
			$ttumum[$xblok]+=$umum[$xblok][$xbln];
			$tttotal[$xblok]+=$upah[$xblok][$xbln]+$material[$xblok][$xbln]+$transport[$xblok][$xbln]+$umum[$xblok][$xbln];
			$stupah[$xbln]+=$upah[$xblok][$xbln];
			$stmaterial[$xbln]+=$material[$xblok][$xbln];
			$sttransport[$xbln]+=$transport[$xblok][$xbln];
			$stumum[$xbln]+=$umum[$xblok][$xbln];
			$sttotal[$xbln]+=$upah[$xblok][$xbln]+$material[$xblok][$xbln]+$transport[$xblok][$xbln]+$umum[$xblok][$xbln];
			$gtupah[$xbln]+=$upah[$xblok][$xbln];
			$gtmaterial[$xbln]+=$material[$xblok][$xbln];
			$gttransport[$xbln]+=$transport[$xblok][$xbln];
			$gtumum[$xbln]+=$umum[$xblok][$xbln];
			$gttotal[$xbln]+=$upah[$xblok][$xbln]+$material[$xblok][$xbln]+$transport[$xblok][$xbln]+$umum[$xblok][$xbln];
		}
		$tab.="<td align=right>".number_format($ttupah[$xblok],2)."</td>";
		$tab.="<td align=right>".number_format($ttmaterial[$xblok],2)."</td>";
		$tab.="<td align=right>".number_format($tttransport[$xblok],2)."</td>";
		$tab.="<td align=right>".number_format($ttumum[$xblok],2)."</td>";
		$tab.="<td align=right>".number_format($tttotal[$xblok],2)."</td>";
		$tab.="</tr>";
		$stttupah+=$ttupah[$xblok];
		$stttmaterial+=$ttmaterial[$xblok];
		$sttttransport+=$tttransport[$xblok];
		$stttumum+=$ttumum[$xblok];
		$sttttotal+=$tttotal[$xblok];
		$gtttupah+=$ttupah[$xblok];
		$gtttmaterial+=$ttmaterial[$xblok];
		$gttttransport+=$tttransport[$xblok];
		$gtttumum+=$ttumum[$xblok];
		$gttttotal+=$tttotal[$xblok];
	}
	$tab.="<tr class=rowcontent>";
	$tab.="<td bgcolor=#ffcc99 colspan=3 align=center>Total ".$xdiv."</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stupah[$xbln],2)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stmaterial[$xbln],2)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttransport[$xbln],2)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stumum[$xbln],2)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttotal[$xbln],2)."</td>";
	}
	$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttupah,2)."</td>";
	$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttmaterial,2)."</td>";
	$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttttransport,2)."</td>";
	$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stttumum,2)."</td>";
	$tab.="<td bgcolor=#ffcc99 align=right>".number_format($sttttotal,2)."</td>";
	$tab.="</tr>";
	if($nodiv>0){
		$tab.="<tr class=rowcontent>";
		$tab.="<td bgcolor=#ffff99 colspan=3 align=center>GRAND TOTAL</td>";
		for ($xbln=1; $xbln<=12; $xbln++){
			$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtupah[$xbln],2)."</td>";
			$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtmaterial[$xbln],2)."</td>";
			$tab.="<td bgcolor=#ffff99 align=right>".number_format($gttransport[$xbln],2)."</td>";
			$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtumum[$xbln],2)."</td>";
			$tab.="<td bgcolor=#ffff99 align=right>".number_format($gttotal[$xbln],2)."</td>";
		}
		$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtttupah,2)."</td>";
		$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtttmaterial,2)."</td>";
		$tab.="<td bgcolor=#ffff99 align=right>".number_format($gttttransport,2)."</td>";
		$tab.="<td bgcolor=#ffff99 align=right>".number_format($gtttumum,2)."</td>";
		$tab.="<td bgcolor=#ffff99 align=right>".number_format($gttttotal,2)."</td>";
		$tab.="</tr>";
	}
	$tab.="</tbody></table>";

	switch($proses){
		case'preview':
			echo $tab;
		break;

		case'excel':
			$judul='Laporan '.$_SESSION['lang']['biaya'].' Per '.$_SESSION['lang']['blok'];
            if(strlen($tab)>0){
				$tab='<h2>'.$namapt.'<br>'.strtoupper($judul).'<br>Periode : '.$periode.'</h2>'.$tab;
	            $nop_=$judul.'_'.date("YmdHis");
				$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				//$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
				//gzwrite($gztralala, $tab);
				//gzclose($gztralala);
				//echo "<script language=javascript1.2>
				//	window.location='tempExcel/".$nop_.".xls.gz';
				//	</script>";
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
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
