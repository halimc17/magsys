<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodeorg=checkPostGet('kodeorg0','');
	if($kodeorg==''){
		$kodeorg=checkPostGet('kodeorg1','');
	}
	if($kodeorg==''){
		$kodeorg=checkPostGet('kodeorg2','');
	}
	if($kodeorg==''){
		$kodeorg=checkPostGet('kodeorg3','');
	}
	$kodeblok=checkPostGet('kebun1','');
	$periode=checkPostGet('periode1','');

	switch($proses){
		case 'getKebun':
			$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
			$sKebun="select distinct left(a.kodeblok,4) as kodeunit,if(a.kodeblok='TBSEXT','TBS LUAR',b.namaorganisasi) as namaunit 
					from ".$dbname.".pabrik_materialballance a
					left join ".$dbname.".organisasi b on b.kodeorganisasi=left(a.kodeblok,4)
					where a.kodeorg='".$kodeorg."'
					ORDER BY if(a.kodeblok='TBSEXT','ZZZZ',a.kodeblok)";
			$qKebun=mysql_query($sKebun) or die(mysql_error($conn));
			while($dKebun=mysql_fetch_assoc($qKebun)){
				$optKebun.="<option value=".$dKebun['kodeunit'].">".$dKebun['namaunit']."</option>";
			}
			echo $optKebun;
			exit();
	}

	if($proses=='preview' or $proses=='excel'){
		if($kodeorg==''){
			exit('Warning : Pabrik tidak boleh kosong...!');
		}
	}
	#Filter parameter where 
	$where="";
	if($kodeorg!=''){
		$where.=" and a.kodeorg = '".$kodeorg."'";
	}
	if($kodeblok!=''){
		$where.=" and a.kodeblok like '".$kodeblok."%'";
	}
	if($periode!=''){
		$where.=" and a.tanggal like '".$periode."%'";
	}
	$optInduk=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$kodept=$optInduk[$kodeorg];
	$namapt=$optNm[$kodept];
	//exit('Warning: '.$kodept);
	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	$bulan=array(1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec');
	$stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td colspan=12 ".$bgclr.">OER</td>
			<td colspan=12 ".$bgclr.">KER</td>
        </tr>
        <tr>";
		for ($xbln=1; $xbln<=12; $xbln++){
			$stream.="<td width='4%' ".$bgclr.">".$bulan[$xbln]."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			$stream.="<td width='4%' ".$bgclr.">".$bulan[$xbln]."</td>";
		}
	$stream.="</tr>
		</thead><tbody>";
	#ambil data
	$sDiv="select DISTINCT left(kodeorg,6) as divisi from ".$dbname.".setup_blok where statusblok='TM'
			and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk in 
									(select induk from ".$dbname.".organisasi where kodeorganisasi='SKDM'))
			ORDER BY left(kodeorg,6) desc";
	$qDiv=mysql_query($sDiv) or die(mysql_error($conn));
	while($dDiv=mysql_fetch_assoc($qDiv)){
		$unit[substr($dDiv['divisi'],0,4)]=substr($dDiv['divisi'],0,4);
		$divisi[$dDiv['divisi']]=$dDiv['divisi'];
	}
	$unit['TBSE']='TBSE';
	$divisi['TBSEXT']='TBSEXT';
	$str="select a.* 
			from ".$dbname.".pabrik_materialballance  a
			where true ".$where." 
			ORDER BY a.kodeorg,left(a.tanggal,7),a.kodeblok
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$xbln=date('n', strtotime($bar->tanggal));
		$xorg=substr($bar->kodeblok,0,4);
		$xdiv=substr($bar->kodeblok,0,6);
		$xkodeblok[$bar->kodeblok]=$bar->kodeblok;

		$ttoil_ur[$xbln][$xdiv]		=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$ttoil_ur[$xdiv.$xbln]		=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$ttkernel_ur[$xbln][$xdiv]	=$bar->kerneldry_ur;
		$ttkernel_ur[$xdiv.$xbln]	=$bar->kerneldry_ur;
		$tthasil_ur[$xdiv.$xbln]	=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$oer_ur[$xdiv.$xbln]		=$ttoil_ur[$xdiv.$xbln]/$tthasil_ur[$xdiv.$xbln]*100;
		$ker_ur[$xdiv.$xbln]		=$ttkernel_ur[$xdiv.$xbln]/$tthasil_ur[$xdiv.$xbln]*100;

		$ttoil_nr[$xdiv.$xbln]		=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$ttkernel_nr[$xdiv.$xbln]	=$bar->kerneldry_nr;
		$tthasil_nr[$xdiv.$xbln]	=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$oer_nr[$xdiv.$xbln]		=$ttoil_nr[$xdiv.$xbln]/$tthasil_nr[$xdiv.$xbln]*100;
		$ker_nr[$xdiv.$xbln]		=$ttkernel_nr[$xdiv.$xbln]/$tthasil_nr[$xdiv.$xbln]*100;

		$ttoil_or[$xdiv.$xbln]		=($bar->oilinfiber_or+$bar->oilinshell_or);
		$ttkernel_or[$xdiv.$xbln]	=$bar->kerneldry_or;
		$tthasil_or[$xdiv.$xbln]	=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
		$oer_or[$xdiv.$xbln]		=$ttoil_or[$xdiv.$xbln]/$tthasil_or[$xdiv.$xbln]*100;
		$ker_or[$xdiv.$xbln]		=$ttkernel_or[$xdiv.$xbln]/$tthasil_or[$xdiv.$xbln]*100;

		$ttoil_tt[$xdiv.$xbln]		=($ttoil_ur[$xdiv.$xbln]+$ttoil_nr[$xdiv.$xbln]+$ttoil_or[$xdiv.$xbln]);
		$ttkernel_tt[$xdiv.$xbln]	=($ttkernel_ur[$xdiv.$xbln]+$ttkernel_nr[$xdiv.$xbln]+$ttkernel_or[$xdiv.$xbln]);
		$tthasil_tt[$xdiv.$xbln]	=($tthasil_ur[$xdiv.$xbln]+$tthasil_nr[$xdiv.$xbln]+$tthasil_or[$xdiv.$xbln]);
		$oer_tt[$xdiv.$xbln]		=$ttoil_tt[$xdiv.$xbln]/$tthasil_tt[$xdiv.$xbln]*100;
		$ker_tt[$xdiv.$xbln]		=$ttkernel_tt[$xdiv.$xbln]/$tthasil_tt[$xdiv.$xbln]*100;
	}
	$no=0;
	$xper='xxxx-xx';
	$stoil_ur=0;
	$stkernel_ur=0;
	$sthasil_ur=0;
	$stoil_nr=0;
	$stkernel_nr=0;
	$sthasil_nr=0;
	$stoil_or=0;
	$stkernel_or=0;
	$sthasil_or=0;
	$gtoil_ur=0;
	$gtkernel_ur=0;
	$gthasil_ur=0;
	$gtoil_nr=0;
	$gtkernel_nr=0;
	$gthasil_nr=0;
	$gtoil_or=0;
	$gtkernel_or=0;
	$gthasil_or=0;
	foreach($divisi as $xdiv){
		$no+=1;
		$stream.="<tr class=rowcontent>
				<td align='center'>".$kodeorg."</td>
				<td align='center'>".$xdiv."</td>";
		foreach($kodeblok as $xblok){
			if(substr($xblok,0,6)!=$xdiv){
			$stream.="<td align='left'></td>";
		        for ($xbln=1; $xbln<=12; $xbln++){
					$stream.="<td align='right'></td>";
				}
			    for ($xbln=1; $xbln<=12; $xbln++){
					$stream.="<td align='right'></td>";
				}
				break;
			}
			$stream.="<td align='left'>".($xblok=='TBSEXT' ? 'TBS Luar' : $xblok)."</td>";
	        for ($xbln=1; $xbln<=12; $xbln++){
				$fontcolor0=($oer_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
				$stream.="<td ".$fontcolor0." align='right'>".number_format($oer_tt[$xdiv.$xbln],2)."</td>";
			}
		    for ($xbln=1; $xbln<=12; $xbln++){
				$fontcolor0=($ker_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
				$stream.="<td ".$fontcolor0." align='right'>".number_format($ker_tt[$xdiv.$xbln],2)."</td>";
			}
			$stream.="</tr>";
			if(substr($xblok,0,6)!=$xdiv){
				break;
			}
		}
	}
	$fontcolor0="";
	$stream.="<tr class=rowcontent>
				<td colspan=3 align='left'>Total</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		//$fontcolor0=($oer_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
		$stream.="<td ".$fontcolor0." align='right'>".number_format(array_sum($ttoil_ur[$xbln]),2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		//$fontcolor0=($ker_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
		$stream.="<td ".$fontcolor0." align='right'>".number_format(array_sum($ttkernel_ur[$xbln]),2)."</td>";
	}
	$stream.="</tr>";
	foreach($unit as $xorg){
		$stream.="<tr class=rowcontent>
					<td colspan=3 align='left'>Total ".($xorg=='TBSE' ? 'LUAR' : $xorg)."</td>";
        for ($xbln=1; $xbln<=12; $xbln++){
			//$fontcolor0=($oer_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
			$stream.="<td ".$fontcolor0." align='right'>".number_format(array_sum($ttoil_ur[$xbln][$xorg]),2)."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			//$fontcolor0=($ker_tt[$xdiv.$xbln]==0 ? " style='color:#e0ecfc' " : "");
			$stream.="<td ".$fontcolor0." align='right'>".number_format(array_sum($ttkernel_ur[$xbln][$xorg]),2)."</td>";
		}
		$stream.="</tr>";
	}
	/*
	$stream.="<tr class=rowcontent>
				<td bgcolor='#FEDEDE' colspan=4 align='center'>Grand Total</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_ur,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_ur,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gthasil_ur,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_ur/$gthasil_ur*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_ur/$gthasil_ur*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_nr,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_nr,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gthasil_nr,2)."</td>
				<td bgcolor='#FEDEDE' ign='right'>".number_format($gtoil_nr/$gthasil_nr*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_nr/$gthasil_nr*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gthasil_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_or/$gthasil_or*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_or/$gthasil_or*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtoil_ur+$gtoil_nr+$gtoil_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gtkernel_ur+$gtkernel_nr+$gtkernel_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($gthasil_ur+$gthasil_nr+$gthasil_or,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format(($gtoil_ur+$gtoil_nr+$gtoil_or)/($gthasil_ur+$gthasil_nr+$gthasil_or)*100,2)."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format(($gtkernel_ur+$gtkernel_nr+$gtkernel_or)/($gthasil_ur+$gthasil_nr+$gthasil_or)*100,2)."</td>
			</tr>";
	*/
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Material Ballance Rekap Monthly';
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.'<br>'.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
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
