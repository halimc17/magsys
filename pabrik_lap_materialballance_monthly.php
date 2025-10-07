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
	$kodeblok=checkPostGet('kebun0','');
	$periode=checkPostGet('periode0','');

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
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['periode']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td colspan=5 ".$bgclr.">Unripe</td>
			<td colspan=5 ".$bgclr.">Normal ripe</td>
			<td colspan=5 ".$bgclr.">Over ripe</td>
			<td colspan=5 ".$bgclr.">Total</td>
        </tr>
        <tr>
			<td width='4%' ".$bgclr.">Total Oil</td>
			<td width='4%' ".$bgclr.">Total Kernel</td>
			<td width='4%' ".$bgclr.">Brondolan</td>
			<td width='4%' ".$bgclr.">OER</td>
			<td width='4%' ".$bgclr.">KER</td>
			<td width='4%' ".$bgclr.">Total Oil</td>
			<td width='4%' ".$bgclr.">Total Kernel</td>
			<td width='4%' ".$bgclr.">Brondolan</td>
			<td width='4%' ".$bgclr.">OER</td>
			<td width='4%' ".$bgclr.">KER</td>
			<td width='4%' ".$bgclr.">Total Oil</td>
			<td width='4%' ".$bgclr.">Total Kernel</td>
			<td width='4%' ".$bgclr.">Brondolan</td>
			<td width='4%' ".$bgclr.">OER</td>
			<td width='4%' ".$bgclr.">KER</td>
			<td width='4%' ".$bgclr.">Total Oil</td>
			<td width='4%' ".$bgclr.">Total Kernel</td>
			<td width='4%' ".$bgclr.">Brondolan</td>
			<td width='4%' ".$bgclr.">OER</td>
			<td width='4%' ".$bgclr.">KER</td>
		</tr>
		</thead><tbody>";

	#ambil data Material Ballance
	$str="select a.*,if(a.kodeblok='TBSEXT','TBS Luar',b.namaorganisasi) as namablok from ".$dbname.".pabrik_materialballance  a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeblok
			where true ".$where." 
			ORDER BY a.kodeorg,left(a.tanggal,7),if(a.kodeblok='TBSEXT','ZZZZZZZZ',b.namaorganisasi)
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$rnum=mysql_num_rows($res);
	if($rnum==0){
		exit('Warning: Data not found...!');
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
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		if($no!=1 and substr($bar->tanggal,0,7)!=$xper){
			$stream.="<tr class=rowcontent>
				<td bgcolor='#DEDEDE' colspan=4 align='center'>Total ".$xper."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur/$sthasil_ur*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur/$sthasil_ur*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_nr/$sthasil_nr*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_nr/$sthasil_nr*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_or/$sthasil_or*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_or/$sthasil_or*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur+$stoil_nr+$stoil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur+$stkernel_nr+$stkernel_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_ur+$sthasil_nr+$sthasil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format(($stoil_ur+$stoil_nr+$stoil_or)/($sthasil_ur+$sthasil_nr+$sthasil_or)*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format(($stkernel_ur+$stkernel_nr+$stkernel_or)/($sthasil_ur+$sthasil_nr+$sthasil_or)*100,2)."</td>
			</tr>";
			$stoil_ur=0;
			$stkernel_ur=0;
			$sthasil_ur=0;
			$stoil_nr=0;
			$stkernel_nr=0;
			$sthasil_nr=0;
			$stoil_or=0;
			$stkernel_or=0;
			$sthasil_or=0;
		}
		$stream.="<tr class=rowcontent>
				<td align='center'>".$bar->kodeorg."</td>
				<td align='center'>".substr($bar->tanggal,0,7)."</td>
				<td align='center'>".substr($bar->kodeblok,0,6)."</td>
				<td align='center'>".$bar->namablok."</td>
				<td align='right'>".number_format($bar->oilinfiber_ur+$bar->oilinshell_ur,2)."</td>
				<td align='right'>".number_format($bar->kerneldry_ur,2)."</td>
				<td align='right'>".number_format($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->oilinfiber_ur+$bar->oilinshell_ur)/ ($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->kerneldry_ur)/ ($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100)*100,2)."</td>
				<td align='right'>".number_format($bar->oilinfiber_nr+$bar->oilinshell_nr,2)."</td>
				<td align='right'>".number_format($bar->kerneldry_nr,2)."</td>
				<td align='right'>".number_format($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->oilinfiber_nr+$bar->oilinshell_nr)/ ($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->kerneldry_nr)/ ($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100)*100,2)."</td>
				<td align='right'>".number_format($bar->oilinfiber_or+$bar->oilinshell_or,2)."</td>
				<td align='right'>".number_format($bar->kerneldry_or,2)."</td>
				<td align='right'>".number_format($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->oilinfiber_or+$bar->oilinshell_or)/ ($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100)*100,2)."</td>
				<td align='right'>".number_format(($bar->kerneldry_or)/ ($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100)*100,2)."</td>
				<td align='right'>".number_format($bar->oilinfiber_ur+$bar->oilinshell_ur+$bar->oilinfiber_nr+$bar->oilinshell_nr+ $bar->oilinfiber_or+$bar->oilinshell_or,2)."</td>
				<td align='right'>".number_format($bar->kerneldry_ur+$bar->kerneldry_nr+$bar->kerneldry_or,2)."</td>
				<td align='right'>".number_format(($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100)+ ($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100)+($bar->brondolan_or/ (($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100),2)."</td>
				<td align='right'>".number_format(($bar->oilinfiber_ur+$bar->oilinshell_ur+$bar->oilinfiber_nr+$bar->oilinshell_nr+ $bar->oilinfiber_or+$bar->oilinshell_or)/(($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100)+($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100)+($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100))*100,2)."</td>
				<td align='right'>".number_format(($bar->kerneldry_ur+$bar->kerneldry_nr+$bar->kerneldry_or)/ (($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100)+ ($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100)+($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100))*100,2)."</td>
				</tr>";
		$xper=substr($bar->tanggal,0,7);
		$stoil_ur+=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$stkernel_ur+=$bar->kerneldry_ur;
		$sthasil_ur+=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$stoil_nr+=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$stkernel_nr+=$bar->kerneldry_nr;
		$sthasil_nr+=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$stoil_or+=($bar->oilinfiber_or+$bar->oilinshell_or);
		$stkernel_or+=$bar->kerneldry_or;
		$sthasil_or+=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
		$gtoil_ur+=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$gtkernel_ur+=$bar->kerneldry_ur;
		$gthasil_ur+=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$gtoil_nr+=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$gtkernel_nr+=$bar->kerneldry_nr;
		$gthasil_nr+=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$gtoil_or+=($bar->oilinfiber_or+$bar->oilinshell_or);
		$gtkernel_or+=$bar->kerneldry_or;
		$gthasil_or+=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
	}
	$stream.="<tr class=rowcontent>
				<td bgcolor='#DEDEDE' colspan=4 align='center'>Total ".$xper."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_ur,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur/$sthasil_ur*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur/$sthasil_ur*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_nr,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_nr/$sthasil_nr*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_nr/$sthasil_nr*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_or/$sthasil_or*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_or/$sthasil_or*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stoil_ur+$stoil_nr+$stoil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkernel_ur+$stkernel_nr+$stkernel_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($sthasil_ur+$sthasil_nr+$sthasil_or,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format(($stoil_ur+$stoil_nr+$stoil_or)/($sthasil_ur+$sthasil_nr+$sthasil_or)*100,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format(($stkernel_ur+$stkernel_nr+$stkernel_or)/($sthasil_ur+$sthasil_nr+$sthasil_or)*100,2)."</td>
			</tr>";
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