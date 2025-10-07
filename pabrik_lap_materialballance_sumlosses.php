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
	$kodeblok=checkPostGet('kebun2','');
	$periode=checkPostGet('periode2','');

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
	#ambil data Divisi
	$sDiv="select DISTINCT left(kodeorg,6) as divisi from ".$dbname.".setup_blok where statusblok='TM'
			and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk in 
									(select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'))
			ORDER BY left(kodeorg,4) desc,left(kodeorg,6)";
	$qDiv=mysql_query($sDiv) or die(mysql_error($conn));
	while($dDiv=mysql_fetch_assoc($qDiv)){
		$unit[substr($dDiv['divisi'],0,4)]=substr($dDiv['divisi'],0,4);
		$divisi[$dDiv['divisi']]=$dDiv['divisi'];
	}
	$unit['TBSE']='TBSE';
	$divisi['TBSEXT']='TBSEXT';
	#ambil data Produksi Pabrik
	$sMill="select kodeorg,left(tanggal,7) as periode,sum(tbsdiolah) as tbsolah,sum(oer) as cpo,sum(oerpk) as kernel
			,sum(oer)/sum(tbsdiolah)*100 as oer,sum(oerpk)/sum(tbsdiolah)*100 as ker
			from ".$dbname.".pabrik_produksi 
			where kodeorg='".$kodeorg."' and tanggal like '".$periode."%'
			GROUP BY kodeorg,left(tanggal,7)
			ORDER BY kodeorg,left(tanggal,7)";
	$qMill=mysql_query($sMill) or die(mysql_error($conn));
	while($dMill=mysql_fetch_assoc($qMill)){
		//$xbln=substr($bar->periode,5,2);
		$xbln=date('n', strtotime($dMill['periode']."-01"));
		$actual_oer[$xbln]=$dMill['oer'];
		$actual_ker[$xbln]=$dMill['ker'];
	}
	#ambil data Material Ballance
	$str="select a.*,b.intiplasma
			from ".$dbname.".pabrik_materialballance a
			left join ".$dbname.".setup_blok b on b.kodeorg=a.kodeblok
			where true ".$where." 
			ORDER BY a.kodeorg,left(a.kodeblok,4) desc,left(a.kodeblok,6),a.kodeblok,left(a.tanggal,7)
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$rnum=mysql_num_rows($res);
	if($rnum==0){
		exit('Warning: Data not found...!');
	}
	while($bar=mysql_fetch_object($res)){
		$xbln=date('n', strtotime($bar->tanggal));
		$xorg=substr($bar->kodeblok,0,4);
		$xdiv=substr($bar->kodeblok,0,6);
		$xblok=$bar->kodeblok;
		$xkodeblok[$xdiv][$bar->kodeblok]=$bar->kodeblok;
		$xinti=$bar->intiplasma;

		$xoerlosses_ur=$bar->lossesoil_ur;
		$xkerlosses_ur=$bar->lossestbs_ur;
		$xoerlosses_nr=$bar->lossesoil_nr;
		$xkerlosses_nr=$bar->lossestbs_nr;
		$xoerlosses_or=$bar->lossesoil_or;
		$xkerlosses_or=$bar->lossestbs_or;
		$xoerlosses_tt=round(($bar->lossesoil_ur+$bar->lossesoil_nr+$bar->lossesoil_or)/3,2);
		$xkerlosses_tt=round(($bar->lossestbs_ur+$bar->lossestbs_nr+$bar->lossestbs_or)/3,2);

		$ttoil_ur[$xbln][$xblok]	=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$ttkernel_ur[$xbln][$xblok]	=$bar->kerneldry_ur;
		$tthasil_ur[$xbln][$xblok]	=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$ttoer_ur[$xbln][$xblok]	=$ttoil_ur[$xbln][$xblok]/$tthasil_ur[$xbln][$xblok]*100;
		$ttker_ur[$xbln][$xblok]	=$ttkernel_ur[$xbln][$xblok]/$tthasil_ur[$xbln][$xblok]*100;

		$ttoil_nr[$xbln][$xblok]	=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$ttkernel_nr[$xbln][$xblok]	=$bar->kerneldry_nr;
		$tthasil_nr[$xbln][$xblok]	=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$ttoer_nr[$xbln][$xblok]	=$ttoil_nr[$xbln][$xblok]/$tthasil_nr[$xbln][$xblok]*100;
		$ttker_nr[$xbln][$xblok]	=$ttkernel_nr[$xbln][$xblok]/$tthasil_nr[$xbln][$xblok]*100;

		$ttoil_or[$xbln][$xblok]	=($bar->oilinfiber_or+$bar->oilinshell_or);
		$ttkernel_or[$xbln][$xblok]	=$bar->kerneldry_or;
		$tthasil_or[$xbln][$xblok]	=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
		$ttoer_or[$xbln][$xblok]	=$ttoil_or[$xbln][$xblok]/$tthasil_or[$xbln][$xblok]*100;
		$ttker_or[$xbln][$xblok]	=$ttkernel_or[$xbln][$xblok]/$tthasil_or[$xbln][$xblok]*100;

		$ttoil_tt[$xbln][$xblok]	=($ttoil_ur[$xbln][$xblok]+$ttoil_nr[$xbln][$xblok]+$ttoil_or[$xbln][$xblok]);
		$ttkernel_tt[$xbln][$xblok]	=($ttkernel_ur[$xbln][$xblok]+$ttkernel_nr[$xbln][$xblok]+$ttkernel_or[$xbln][$xblok]);
		$tthasil_tt[$xbln][$xblok]	=($tthasil_ur[$xbln][$xblok]+$tthasil_nr[$xbln][$xblok]+$tthasil_or[$xbln][$xblok]);
		$ttoer_tt[$xbln][$xblok]	=$ttoil_tt[$xbln][$xblok]/$tthasil_tt[$xbln][$xblok]*100;
		$ttker_tt[$xbln][$xblok]	=$ttkernel_tt[$xbln][$xblok]/$tthasil_tt[$xbln][$xblok]*100;

		$gtoil_ur[$xbln][$xorg][$xblok]		=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$gtkernel_ur[$xbln][$xorg][$xblok]	=$bar->kerneldry_ur;
		$gthasil_ur[$xbln][$xorg][$xblok]	=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$gtoer_ur[$xbln][$xorg][$xblok]		=$ttoil_ur[$xbln][$xblok]/$tthasil_ur[$xbln][$xblok]*100;
		$gtker_ur[$xbln][$xorg][$xblok]		=$ttkernel_ur[$xbln][$xblok]/$tthasil_ur[$xbln][$xblok]*100;

		$gtoil_nr[$xbln][$xorg][$xblok]		=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$gtkernel_nr[$xbln][$xorg][$xblok]	=$bar->kerneldry_nr;
		$gthasil_nr[$xbln][$xorg][$xblok]	=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$gtoer_nr[$xbln][$xorg][$xblok]		=$ttoil_nr[$xbln][$xblok]/$tthasil_nr[$xbln][$xblok]*100;
		$gtker_nr[$xbln][$xorg][$xblok]		=$ttkernel_nr[$xbln][$xblok]/$tthasil_nr[$xbln][$xblok]*100;

		$gtoil_or[$xbln][$xorg][$xblok]		=($bar->oilinfiber_or+$bar->oilinshell_or);
		$gtkernel_or[$xbln][$xorg][$xblok]	=$bar->kerneldry_or;
		$gthasil_or[$xbln][$xorg][$xblok]	=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
		$gtoer_or[$xbln][$xorg][$xblok]		=$ttoil_or[$xbln][$xblok]/$tthasil_or[$xbln][$xblok]*100;
		$gtker_or[$xbln][$xorg][$xblok]		=$ttkernel_or[$xbln][$xblok]/$tthasil_or[$xbln][$xblok]*100;

		$gtoil_tt[$xbln][$xorg][$xblok]		=($ttoil_ur[$xbln][$xblok]+$ttoil_nr[$xbln][$xblok]+$ttoil_or[$xbln][$xblok]);
		$gtkernel_tt[$xbln][$xorg][$xblok]	=($ttkernel_ur[$xbln][$xblok]+$ttkernel_nr[$xbln][$xblok]+$ttkernel_or[$xbln][$xblok]);
		$gthasil_tt[$xbln][$xorg][$xblok]	=($tthasil_ur[$xbln][$xblok]+$tthasil_nr[$xbln][$xblok]+$tthasil_or[$xbln][$xblok]);
		$gtoer_tt[$xbln][$xorg][$xblok]		=$gtoil_tt[$xbln][$xorg][$xblok]/$gthasil_tt[$xbln][$xorg][$xblok]*100;
		$gtker_tt[$xbln][$xorg][$xblok]		=$gtkernel_tt[$xbln][$xorg][$xblok]/$gthasil_tt[$xbln][$xorg][$xblok]*100;
	
		$stoil_ur[$xbln][$xinti][$xblok]	=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$stkernel_ur[$xbln][$xinti][$xblok]	=$bar->kerneldry_ur;
		$sthasil_ur[$xbln][$xinti][$xblok]	=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);
		$stoer_ur[$xbln][$xinti][$xblok]	=$stoil_ur[$xbln][$xinti][$xblok]/$sthasil_ur[$xbln][$xinti][$xblok]*100;
		$stker_ur[$xbln][$xinti][$xblok]	=$stkernel_ur[$xbln][$xinti][$xblok]/$sthasil_ur[$xbln][$xinti][$xblok]*100;

		$stoil_nr[$xbln][$xinti][$xblok]	=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$stkernel_nr[$xbln][$xinti][$xblok]	=$bar->kerneldry_nr;
		$sthasil_nr[$xbln][$xinti][$xblok]	=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);
		$stoer_nr[$xbln][$xinti][$xblok]	=$stoil_nr[$xbln][$xinti][$xblok]/$sthasil_nr[$xbln][$xinti][$xblok]*100;
		$stker_nr[$xbln][$xinti][$xblok]	=$stkernel_nr[$xbln][$xinti][$xblok]/$sthasil_nr[$xbln][$xinti][$xblok]*100;

		$stoil_or[$xbln][$xinti][$xblok]	=($bar->oilinfiber_or+$bar->oilinshell_or);
		$stkernel_or[$xbln][$xinti][$xblok]	=$bar->kerneldry_or;
		$sthasil_or[$xbln][$xinti][$xblok]	=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);
		$stoer_or[$xbln][$xinti][$xblok]	=$stoil_or[$xbln][$xinti][$xblok]/$sthasil_or[$xbln][$xinti][$xblok]*100;
		$stker_or[$xbln][$xinti][$xblok]	=$stkernel_or[$xbln][$xinti][$xblok]/$sthasil_or[$xbln][$xinti][$xblok]*100;

		$stoil_tt[$xbln][$xinti][$xblok]	=($ttoil_ur[$xbln][$xblok]+$ttoil_nr[$xbln][$xblok]+$ttoil_or[$xbln][$xblok]);
		$stkernel_tt[$xbln][$xinti][$xblok]	=($ttkernel_ur[$xbln][$xblok]+$ttkernel_nr[$xbln][$xblok]+$ttkernel_or[$xbln][$xblok]);
		$sthasil_tt[$xbln][$xinti][$xblok]	=($tthasil_ur[$xbln][$xblok]+$tthasil_nr[$xbln][$xblok]+$tthasil_or[$xbln][$xblok]);
		$stoer_tt[$xbln][$xinti][$xblok]	=$stoil_tt[$xbln][$xinti][$xblok]/$sthasil_tt[$xbln][$xinti][$xblok]*100;
		$stker_tt[$xbln][$xinti][$xblok]	=$stkernel_tt[$xbln][$xinti][$xblok]/$sthasil_tt[$xbln][$xinti][$xblok]*100;

	}
	//============== MB TOTAL===============
	$stream.="<b>TOTAL MATERIAL BALLANCE</b>
		<table cellspacing='1' border='".$brd."' class='sortable'>
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
	$no=0;
	foreach($divisi as $xdiv){
		$no+=1;
		$ada=0;
		$xorg=substr($xdiv,0,4);
		//foreach($xkodeblok[$xdiv] as $xblok){
		foreach($xkodeblok as $xkddiv => $xkdblok){
			if($xdiv==$xkddiv){
				foreach($xkdblok as $xblok){
					//exit('Warning: '.$xblok);
					$ada=1;
					$stream.="<tr class=rowcontent>
								<td align='center'>".$kodeorg."</td>
								<td align='center'>".$xdiv."</td>";
					$stream.="<td align='left'>".($xblok=='TBSEXT' ? 'TBS Luar' : $optNm[$xblok])."</td>";
			        for ($xbln=1; $xbln<=12; $xbln++){
						$ttoer_tt0=($ttoer_tt[$xbln][$xblok]==0 ? 0 : $ttoer_tt[$xbln][$xblok]-$xoerlosses_tt);
						$fontcolor0=($ttoer_tt0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttoer_tt0,2)."</td>";
						$ttbln_oil_tt[$xbln]+=$ttoil_tt[$xbln][$xblok];
						$ttbln_hasil_tt[$xbln]+=$tthasil_tt[$xbln][$xblok];
						$ttunit_oil_tt[$xorg][$xbln]+=$ttoil_tt[$xbln][$xblok];
						$ttunit_hasil_tt[$xorg][$xbln]+=$tthasil_tt[$xbln][$xblok];
						$stinti_oil_tt[$xbln]+=$stoil_tt[$xbln]['I'][$xblok];
						$stinti_hasil_tt[$xbln]+=$sthasil_tt[$xbln]['I'][$xblok];
					}
				    for ($xbln=1; $xbln<=12; $xbln++){
						$ttker_tt0=($ttker_tt[$xbln][$xblok]==0 ? 0 : $ttker_tt[$xbln][$xblok]-$xkerlosses_tt);
						$fontcolor0=($ttker_tt0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttker_tt0,2)."</td>";
						$ttbln_kernel_tt[$xbln]+=$ttkernel_tt[$xbln][$xblok];
						$ttunit_kernel_tt[$xorg][$xbln]+=$ttkernel_tt[$xbln][$xblok];
						$stinti_kernel_tt[$xbln]+=$stkernel_tt[$xbln]['I'][$xblok];
					}
					$stream.="</tr>";
				}
			}
		}
		if($ada==0){
			$stream.="<tr class=rowcontent>
					<td align='center'>".$kodeorg."</td>
					<td align='center'>".$xdiv."</td>";
			$stream.="<td align='left'></td>";
	        for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
		    for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
			$stream.="</tr>";
		}
	}
	// Warna Background rowcontent = #e0ecfc
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_oer=($ttbln_hasil_tt[$xbln]==0 ? 0 : ($ttbln_oil_tt[$xbln]/$ttbln_hasil_tt[$xbln]*100)-$xoerlosses_tt);
		$fontcolor0=($tt_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_ker=($ttbln_hasil_tt[$xbln]==0 ? 0 : ($ttbln_kernel_tt[$xbln]/$ttbln_hasil_tt[$xbln]*100)-$xkerlosses_tt);
		$fontcolor0=($tt_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_ker,2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL INTI</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_oer=($stinti_hasil_tt[$xbln]==0 ? 0 : ($stinti_oil_tt[$xbln]/$stinti_hasil_tt[$xbln]*100)-$xoerlosses_tt);
		$fontcolor0=($stinti_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_ker=($stinti_hasil_tt[$xbln]==0 ? 0 : ($stinti_kernel_tt[$xbln]/$stinti_hasil_tt[$xbln]*100)-$xkerlosses_tt);
		$fontcolor0=($stinti_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_ker,2)."</td>";
	}
	$stream.="</tr>";
	$wrn = array("bb", "cc", "dd", "ee", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff"); 
	$no=0;
	foreach($unit as $xorg){
		$no+=1;
		$ttbgcolor="#ff".$wrn[$no]."99";
		$stream.="<tr class=rowcontent>
					<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
					<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL ".($xorg=='TBSE' ? 'LUAR' : $xorg)."</td>";
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_oer=($ttunit_hasil_tt[$xorg][$xbln]==0 ? 0 : ($ttunit_oil_tt[$xorg][$xbln]/$ttunit_hasil_tt[$xorg][$xbln]*100)-$xoerlosses_tt);
			$fontcolor0=($ttunit_oer==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_oer,2)."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_ker=($ttunit_hasil_tt[$xorg][$xbln]==0 ? 0 : ($ttunit_kernel_tt[$xorg][$xbln]/$ttunit_hasil_tt[$xorg][$xbln]*100)-$xkerlosses_tt);
			$fontcolor0=($ttunit_ker==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_ker,2)."</td>";
		}
		$stream.="</tr>";
	}
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>ACTUAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_oer[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_oer[$xbln],2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_ker[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_ker[$xbln],2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>VARIAN</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_oer=(array_sum($ttoil_tt[$xbln])/array_sum($tthasil_tt[$xbln])*100)-($actual_oer[$xbln]);
		$tt_oer=($ttbln_hasil_tt[$xbln]==0 ? 0 : ($ttbln_oil_tt[$xbln]/$ttbln_hasil_tt[$xbln]*100)-$xoerlosses_tt);
		$varian_oer=($tt_oer)-($actual_oer[$xbln]);
		$fontcolor0=($varian_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_ker=(array_sum($ttkernel_tt[$xbln])/array_sum($tthasil_tt[$xbln])*100)-($actual_ker[$xbln]);
		$tt_ker=($ttbln_hasil_tt[$xbln]==0 ? 0 : ($ttbln_kernel_tt[$xbln]/$ttbln_hasil_tt[$xbln]*100)-$xkerlosses_tt);
		$varian_ker=($tt_ker)-($actual_ker[$xbln]);
		$fontcolor0=($varian_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_ker,2)."</td>";
	}
	$stream.="</tr>";
	$stream.="</tbody></table>";

	//============== MB UNRIPE===============
	$stream.="<br><b>MATERIAL BALLANCE UNRIPE</b>
		<table cellspacing='1' border='".$brd."' class='sortable'>
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
	$no=0;
	foreach($divisi as $xdiv){
		$no+=1;
		$ada=0;
		$xorg=substr($xdiv,0,4);
		//foreach($xkodeblok[$xdiv] as $xblok){
		foreach($xkodeblok as $xkddiv => $xkdblok){
			if($xdiv==$xkddiv){
				foreach($xkdblok as $xblok){
					//exit('Warning: '.$xblok);
					$ada=1;
					$stream.="<tr class=rowcontent>
								<td align='center'>".$kodeorg."</td>
								<td align='center'>".$xdiv."</td>";
					$stream.="<td align='left'>".($xblok=='TBSEXT' ? 'TBS Luar' : $optNm[$xblok])."</td>";
			        for ($xbln=1; $xbln<=12; $xbln++){
						$ttoer_ur0=($ttoer_ur[$xbln][$xblok]==0 ? 0 : $ttoer_ur[$xbln][$xblok]-$xoerlosses_ur);
						$fontcolor0=($ttoer_ur0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttoer_ur0,2)."</td>";
						$ttbln_oil_ur[$xbln]+=$ttoil_ur[$xbln][$xblok];
						$ttbln_hasil_ur[$xbln]+=$tthasil_ur[$xbln][$xblok];
						$ttunit_oil_ur[$xorg][$xbln]+=$ttoil_ur[$xbln][$xblok];
						$ttunit_hasil_ur[$xorg][$xbln]+=$tthasil_ur[$xbln][$xblok];
						$stinti_oil_ur[$xbln]+=$stoil_ur[$xbln]['I'][$xblok];
						$stinti_hasil_ur[$xbln]+=$sthasil_ur[$xbln]['I'][$xblok];
					}
				    for ($xbln=1; $xbln<=12; $xbln++){
						$ttker_ur0=($ttker_ur[$xbln][$xblok]==0 ? 0 : $ttker_ur[$xbln][$xblok]-$xkerlosses_ur);
						$fontcolor0=($ttker_ur0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttker_ur0,2)."</td>";
						$ttbln_kernel_ur[$xbln]+=$ttkernel_ur[$xbln][$xblok];
						$ttunit_kernel_ur[$xorg][$xbln]+=$ttkernel_ur[$xbln][$xblok];
						$stinti_kernel_ur[$xbln]+=$stkernel_ur[$xbln]['I'][$xblok];
					}
					$stream.="</tr>";
				}
			}
		}
		if($ada==0){
			$stream.="<tr class=rowcontent>
					<td align='center'>".$kodeorg."</td>
					<td align='center'>".$xdiv."</td>";
			$stream.="<td align='left'></td>";
	        for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
		    for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
			$stream.="</tr>";
		}
	}
	// Warna Background rowcontent = #e0ecfc
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_oer=($ttbln_hasil_ur[$xbln]==0 ? 0 : ($ttbln_oil_ur[$xbln]/$ttbln_hasil_ur[$xbln]*100)-$xoerlosses_ur);
		$fontcolor0=($tt_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_ker=($ttbln_hasil_ur[$xbln]==0 ? 0 : ($ttbln_kernel_ur[$xbln]/$ttbln_hasil_ur[$xbln]*100)-$xkerlosses_ur);
		$fontcolor0=($tt_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_ker,2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL INTI</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_oer=($stinti_hasil_ur[$xbln]==0 ? 0 : ($stinti_oil_ur[$xbln]/$stinti_hasil_ur[$xbln]*100)-$xoerlosses_ur);
		$fontcolor0=($stinti_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_ker=($stinti_hasil_ur[$xbln]==0 ? 0 : ($stinti_kernel_ur[$xbln]/$stinti_hasil_ur[$xbln]*100)-$xkerlosses_ur);
		$fontcolor0=($stinti_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_ker,2)."</td>";
	}
	$stream.="</tr>";
	$wrn = array("bb", "cc", "dd", "ee", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff"); 
	$no=0;
	foreach($unit as $xorg){
		$no+=1;
		$ttbgcolor="#ff".$wrn[$no]."99";
		$stream.="<tr class=rowcontent>
					<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
					<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL ".($xorg=='TBSE' ? 'LUAR' : $xorg)."</td>";
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_oer=($ttunit_hasil_ur[$xorg][$xbln]==0 ? 0 : ($ttunit_oil_ur[$xorg][$xbln]/$ttunit_hasil_ur[$xorg][$xbln]*100)-$xoerlosses_ur);
			$fontcolor0=($ttunit_oer==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_oer,2)."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_ker=($ttunit_hasil_ur[$xorg][$xbln]==0 ? 0 : ($ttunit_kernel_ur[$xorg][$xbln]/$ttunit_hasil_ur[$xorg][$xbln]*100)-$xkerlosses_ur);
			$fontcolor0=($ttunit_ker==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_ker,2)."</td>";
		}
		$stream.="</tr>";
	}
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>ACTUAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_oer[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_oer[$xbln],2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_ker[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_ker[$xbln],2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>VARIAN</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_oer=(array_sum($ttoil_ur[$xbln])/array_sum($tthasil_ur[$xbln])*100)-($actual_oer[$xbln]);
		$tt_oer=($ttbln_hasil_ur[$xbln]==0 ? 0 : ($ttbln_oil_ur[$xbln]/$ttbln_hasil_ur[$xbln]*100)-$xoerlosses_ur);
		$varian_oer=($tt_oer)-($actual_oer[$xbln]);
		$fontcolor0=($varian_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_ker=(array_sum($ttkernel_ur[$xbln])/array_sum($tthasil_ur[$xbln])*100)-($actual_ker[$xbln]);
		$tt_ker=($ttbln_hasil_ur[$xbln]==0 ? 0 : ($ttbln_kernel_ur[$xbln]/$ttbln_hasil_ur[$xbln]*100)-$xkerlosses_ur);
		$varian_ker=($tt_ker)-($actual_ker[$xbln]);
		$fontcolor0=($varian_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_ker,2)."</td>";
	}
	$stream.="</tr>";
	$stream.="</tbody></table>";

	//============== MB NORMAL RIPE===============
	$stream.="<br><b>MATERIAL BALLANCE NORMAL RIPE</b>
		<table cellspacing='1' border='".$brd."' class='sortable'>
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
	$no=0;
	foreach($divisi as $xdiv){
		$no+=1;
		$ada=0;
		$xorg=substr($xdiv,0,4);
		//foreach($xkodeblok[$xdiv] as $xblok){
		foreach($xkodeblok as $xkddiv => $xkdblok){
			if($xdiv==$xkddiv){
				foreach($xkdblok as $xblok){
					//exit('Warning: '.$xblok);
					$ada=1;
					$stream.="<tr class=rowcontent>
								<td align='center'>".$kodeorg."</td>
								<td align='center'>".$xdiv."</td>";
					$stream.="<td align='left'>".($xblok=='TBSEXT' ? 'TBS Luar' : $optNm[$xblok])."</td>";
			        for ($xbln=1; $xbln<=12; $xbln++){
						$ttoer_nr0=($ttoer_nr[$xbln][$xblok]==0 ? 0 : $ttoer_nr[$xbln][$xblok]-$xoerlosses_nr);
						$fontcolor0=($ttoer_nr0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttoer_nr0,2)."</td>";
						$ttbln_oil_nr[$xbln]+=$ttoil_nr[$xbln][$xblok];
						$ttbln_hasil_nr[$xbln]+=$tthasil_nr[$xbln][$xblok];
						$ttunit_oil_nr[$xorg][$xbln]+=$ttoil_nr[$xbln][$xblok];
						$ttunit_hasil_nr[$xorg][$xbln]+=$tthasil_nr[$xbln][$xblok];
						$stinti_oil_nr[$xbln]+=$stoil_nr[$xbln]['I'][$xblok];
						$stinti_hasil_nr[$xbln]+=$sthasil_nr[$xbln]['I'][$xblok];
					}
				    for ($xbln=1; $xbln<=12; $xbln++){
						$ttker_nr0=($ttker_nr[$xbln][$xblok]==0 ? 0 : $ttker_nr[$xbln][$xblok]-$xkerlosses_nr);
						$fontcolor0=($ttker_nr0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttker_nr0,2)."</td>";
						$ttbln_kernel_nr[$xbln]+=$ttkernel_nr[$xbln][$xblok];
						$ttunit_kernel_nr[$xorg][$xbln]+=$ttkernel_nr[$xbln][$xblok];
						$stinti_kernel_nr[$xbln]+=$stkernel_nr[$xbln]['I'][$xblok];
					}
					$stream.="</tr>";
				}
			}
		}
		if($ada==0){
			$stream.="<tr class=rowcontent>
					<td align='center'>".$kodeorg."</td>
					<td align='center'>".$xdiv."</td>";
			$stream.="<td align='left'></td>";
	        for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
		    for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
			$stream.="</tr>";
		}
	}
	// Warna Background rowcontent = #e0ecfc
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_oer=($ttbln_hasil_nr[$xbln]==0 ? 0 : ($ttbln_oil_nr[$xbln]/$ttbln_hasil_nr[$xbln]*100)-$xoerlosses_nr);
		$fontcolor0=($tt_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_ker=($ttbln_hasil_nr[$xbln]==0 ? 0 : ($ttbln_kernel_nr[$xbln]/$ttbln_hasil_nr[$xbln]*100)-$xkerlosses_nr);
		$fontcolor0=($tt_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_ker,2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL INTI</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_oer=($stinti_hasil_nr[$xbln]==0 ? 0 : ($stinti_oil_nr[$xbln]/$stinti_hasil_nr[$xbln]*100)-$xoerlosses_nr);
		$fontcolor0=($stinti_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_ker=($stinti_hasil_nr[$xbln]==0 ? 0 : ($stinti_kernel_nr[$xbln]/$stinti_hasil_nr[$xbln]*100)-$xkerlosses_nr);
		$fontcolor0=($stinti_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_ker,2)."</td>";
	}
	$stream.="</tr>";
	$wrn = array("bb", "cc", "dd", "ee", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff"); 
	$no=0;
	foreach($unit as $xorg){
		$no+=1;
		$ttbgcolor="#ff".$wrn[$no]."99";
		$stream.="<tr class=rowcontent>
					<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
					<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL ".($xorg=='TBSE' ? 'LUAR' : $xorg)."</td>";
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_oer=($ttunit_hasil_nr[$xorg][$xbln]==0 ? 0 : ($ttunit_oil_nr[$xorg][$xbln]/$ttunit_hasil_nr[$xorg][$xbln]*100)-$xoerlosses_nr);
			$fontcolor0=($ttunit_oer==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_oer,2)."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_ker=($ttunit_hasil_nr[$xorg][$xbln]==0 ? 0 : ($ttunit_kernel_nr[$xorg][$xbln]/$ttunit_hasil_nr[$xorg][$xbln]*100)-$xkerlosses_nr);
			$fontcolor0=($ttunit_ker==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_ker,2)."</td>";
		}
		$stream.="</tr>";
	}
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>ACTUAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_oer[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_oer[$xbln],2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_ker[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_ker[$xbln],2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>VARIAN</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_oer=(array_sum($ttoil_nr[$xbln])/array_sum($tthasil_nr[$xbln])*100)-($actual_oer[$xbln]);
		$tt_oer=($ttbln_hasil_nr[$xbln]==0 ? 0 : ($ttbln_oil_nr[$xbln]/$ttbln_hasil_nr[$xbln]*100)-$xoerlosses_nr);
		$varian_oer=($tt_oer)-($actual_oer[$xbln]);
		$fontcolor0=($varian_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_ker=(array_sum($ttkernel_nr[$xbln])/array_sum($tthasil_nr[$xbln])*100)-($actual_ker[$xbln]);
		$tt_ker=($ttbln_hasil_nr[$xbln]==0 ? 0 : ($ttbln_kernel_nr[$xbln]/$ttbln_hasil_nr[$xbln]*100)-$xkerlosses_nr);
		$varian_ker=($tt_ker)-($actual_ker[$xbln]);
		$fontcolor0=($varian_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_ker,2)."</td>";
	}
	$stream.="</tr>";
	$stream.="</tbody></table>";

	//============== MB OVER RIPE===============
	$stream.="<br><b>MATERIAL BALLANCE OVER RIPE</b>
		<table cellspacing='1' border='".$brd."' class='sortable'>
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
	$no=0;
	foreach($divisi as $xdiv){
		$no+=1;
		$ada=0;
		$xorg=substr($xdiv,0,4);
		//foreach($xkodeblok[$xdiv] as $xblok){
		foreach($xkodeblok as $xkddiv => $xkdblok){
			if($xdiv==$xkddiv){
				foreach($xkdblok as $xblok){
					//exit('Warning: '.$xblok);
					$ada=1;
					$stream.="<tr class=rowcontent>
								<td align='center'>".$kodeorg."</td>
								<td align='center'>".$xdiv."</td>";
					$stream.="<td align='left'>".($xblok=='TBSEXT' ? 'TBS Luar' : $optNm[$xblok])."</td>";
			        for ($xbln=1; $xbln<=12; $xbln++){
						$ttoer_or0=($ttoer_or[$xbln][$xblok]==0 ? 0 : $ttoer_or[$xbln][$xblok]-$xoerlosses_or);
						$fontcolor0=($ttoer_or0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttoer_or0,2)."</td>";
						$ttbln_oil_or[$xbln]+=$ttoil_or[$xbln][$xblok];
						$ttbln_hasil_or[$xbln]+=$tthasil_or[$xbln][$xblok];
						$ttunit_oil_or[$xorg][$xbln]+=$ttoil_or[$xbln][$xblok];
						$ttunit_hasil_or[$xorg][$xbln]+=$tthasil_or[$xbln][$xblok];
						$stinti_oil_or[$xbln]+=$stoil_or[$xbln]['I'][$xblok];
						$stinti_hasil_or[$xbln]+=$sthasil_or[$xbln]['I'][$xblok];
					}
				    for ($xbln=1; $xbln<=12; $xbln++){
						$ttker_or0=($ttker_or[$xbln][$xblok]==0 ? 0 : $ttker_or[$xbln][$xblok]-$xkerlosses_or);
						$fontcolor0=($ttker_or0==0 ? " style='color:#e0ecfc' " : "");
						$stream.="<td ".$fontcolor0." align='right'>".number_format($ttker_or0,2)."</td>";
						$ttbln_kernel_or[$xbln]+=$ttkernel_or[$xbln][$xblok];
						$ttunit_kernel_or[$xorg][$xbln]+=$ttkernel_or[$xbln][$xblok];
						$stinti_kernel_or[$xbln]+=$stkernel_or[$xbln]['I'][$xblok];
					}
					$stream.="</tr>";
				}
			}
		}
		if($ada==0){
			$stream.="<tr class=rowcontent>
					<td align='center'>".$kodeorg."</td>
					<td align='center'>".$xdiv."</td>";
			$stream.="<td align='left'></td>";
	        for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
		    for ($xbln=1; $xbln<=12; $xbln++){
				$stream.="<td align='right'></td>";
			}
			$stream.="</tr>";
		}
	}
	// Warna Background rowcontent = #e0ecfc
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_oer=($ttbln_hasil_or[$xbln]==0 ? 0 : ($ttbln_oil_or[$xbln]/$ttbln_hasil_or[$xbln]*100)-$xoerlosses_or);
		$fontcolor0=($tt_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$tt_ker=($ttbln_hasil_or[$xbln]==0 ? 0 : ($ttbln_kernel_or[$xbln]/$ttbln_hasil_or[$xbln]*100)-$xkerlosses_or);
		$fontcolor0=($tt_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($tt_ker,2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL INTI</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_oer=($stinti_hasil_or[$xbln]==0 ? 0 : ($stinti_oil_or[$xbln]/$stinti_hasil_or[$xbln]*100)-$xoerlosses_or);
		$fontcolor0=($stinti_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$stinti_ker=($stinti_hasil_or[$xbln]==0 ? 0 : ($stinti_kernel_or[$xbln]/$stinti_hasil_or[$xbln]*100)-$xkerlosses_or);
		$fontcolor0=($stinti_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($stinti_ker,2)."</td>";
	}
	$stream.="</tr>";
	$wrn = array("bb", "cc", "dd", "ee", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff", "ff"); 
	$no=0;
	foreach($unit as $xorg){
		$no+=1;
		$ttbgcolor="#ff".$wrn[$no]."99";
		$stream.="<tr class=rowcontent>
					<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
					<td bgcolor=".$ttbgcolor." colspan=2 align='center'>TOTAL ".($xorg=='TBSE' ? 'LUAR' : $xorg)."</td>";
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_oer=($ttunit_hasil_or[$xorg][$xbln]==0 ? 0 : ($ttunit_oil_or[$xorg][$xbln]/$ttunit_hasil_or[$xorg][$xbln]*100)-$xoerlosses_or);
			$fontcolor0=($ttunit_oer==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_oer,2)."</td>";
		}
        for ($xbln=1; $xbln<=12; $xbln++){
			$ttunit_ker=($ttunit_hasil_or[$xorg][$xbln]==0 ? 0 : ($ttunit_kernel_or[$xorg][$xbln]/$ttunit_hasil_or[$xorg][$xbln]*100)-$xkerlosses_or);
			$fontcolor0=($ttunit_ker==0 ? " style='color:$ttbgcolor' " : "");
			$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($ttunit_ker,2)."</td>";
		}
		$stream.="</tr>";
	}
	$ttbgcolor="#ffbb99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>ACTUAL</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_oer[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_oer[$xbln],2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		$fontcolor0=($actual_ker[$xbln]==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($actual_ker[$xbln],2)."</td>";
	}
	$stream.="</tr>";
	$ttbgcolor="#ffaa99";
	$stream.="<tr class=rowcontent>
				<td bgcolor=".$ttbgcolor." align='center'>".$kodeorg."</td>
				<td bgcolor=".$ttbgcolor." colspan=2 align='center'>VARIAN</td>";
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_oer=(array_sum($ttoil_or[$xbln])/array_sum($tthasil_or[$xbln])*100)-($actual_oer[$xbln]);
		$tt_oer=($ttbln_hasil_or[$xbln]==0 ? 0 : ($ttbln_oil_or[$xbln]/$ttbln_hasil_or[$xbln]*100)-$xoerlosses_or);
		$varian_oer=($tt_oer)-($actual_oer[$xbln]);
		$fontcolor0=($varian_oer==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_oer,2)."</td>";
	}
	for ($xbln=1; $xbln<=12; $xbln++){
		//$varian_ker=(array_sum($ttkernel_or[$xbln])/array_sum($tthasil_or[$xbln])*100)-($actual_ker[$xbln]);
		$tt_ker=($ttbln_hasil_or[$xbln]==0 ? 0 : ($ttbln_kernel_or[$xbln]/$ttbln_hasil_or[$xbln]*100)-$xkerlosses_or);
		$varian_ker=($tt_ker)-($actual_ker[$xbln]);
		$fontcolor0=($varian_ker==0 ? " style='color:$ttbgcolor' " : "");
		$stream.="<td bgcolor=".$ttbgcolor." ".$fontcolor0." align='right'>".number_format($varian_ker,2)."</td>";
	}
	$stream.="</tr>";
	$stream.="</tbody></table>";

	// End Of Content
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
