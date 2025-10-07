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
	$kodeblok='';
	$periode=checkPostGet('periode3','');

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
	$optNamaPT=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$optNamaOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
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

	#ambil data Produksi Pabrik
	$sMill="select kodeorg,left(tanggal,7) as periode,sum(tbsdiolah) as tbsolah,sum(oer) as cpo,sum(oerpk) as kernel
			,sum(oer)/sum(tbsdiolah)*100 as oer,sum(oerpk)/sum(tbsdiolah)*100 as ker
			from ".$dbname.".pabrik_produksi 
			where kodeorg='".$kodeorg."' and tanggal like '".$periode."%'
			GROUP BY kodeorg,left(tanggal,7)
			ORDER BY kodeorg,left(tanggal,7)";
	//exit('Warning: '.$sMill);
	$qMill=mysql_query($sMill) or die(mysql_error($conn));
	while($dMill=mysql_fetch_assoc($qMill)){
		//$xbln=substr($bar->periode,5,2);
		$xbln=date('n', strtotime($dMill['periode']."-01"));
		$actual_oer[$xbln]=$dMill['oer'];
		$actual_ker[$xbln]=$dMill['ker'];
	}

	#ambil data Timbangan ==========================
	$sMill="select a.millcode as kodeorg,left(a.tanggal,7) as periode,sum(a.beratbersih) as tbsterima
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'',a.beratbersih,0)) as tbsinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE',a.beratbersih,0)) as tbsplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='',a.beratbersih,0)) as tbsluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'',a.beratbersih,0))/sum(a.beratbersih)*100 as seninti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE',a.beratbersih,0))/sum(a.beratbersih)*100 as senplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='',a.beratbersih,0))/sum(a.beratbersih)*100 as senluar
			from ".$dbname.".vw_pabrik_timbangan_7ke7 a
			where a.kodebarang='40000003' and a.millcode='".$kodeorg."' and a.tanggal like '".$periode."%'
			GROUP BY a.millcode,left(a.tanggal,7)
			ORDER BY a.millcode,left(a.tanggal,7)
			";
	//exit('Warning: '.$sMill);
	$qMill=mysql_query($sMill) or die(mysql_error($conn));
	while($dMill=mysql_fetch_assoc($qMill)){
		$xbln=date('n', strtotime($dMill['periode']."-01"));
		$tbsterima[$xbln]=$dMill['tbsterima'];
		$tbsluar[$xbln]=$dMill['tbsluar'];
		$senluar[$xbln]=$dMill['senluar'];
	}

	#ambil data Timbangan ==========================
	$sMill="select a.millcode as kodeorg,left(a.tanggal,7) as periode
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='B'),a.jjgsortasi,0)) as jjgsortasiinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='B'),a.jjgsortasi,0)) as jjgsortasiplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='B'),a.jjgsortasi,0)) as jjgsortasiluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='B' or y.kodefraksi='C' or y.kodefraksi='D' or y.kodefraksi='E'),y.jumlah,0)) as jjgunripeinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='B' or y.kodefraksi='C' or y.kodefraksi='D' or y.kodefraksi='E'),y.jumlah,0)) as jjgunripeplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='B' or y.kodefraksi='C' or y.kodefraksi='D' or y.kodefraksi='E'),y.jumlah,0)) as jjgunripeluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='A'),y.jumlah,0)) as jjgoverinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='A'),y.jumlah,0)) as jjgoverplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='A'),y.jumlah,0)) as jjgoverluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='B'),y.jumlah,0)) as Bjjgunripeinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='B'),y.jumlah,0)) as Bjjgunripeplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='B'),y.jumlah,0)) as Bjjgunripeluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='C'),y.jumlah,0)) as Cjjgunripeinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='C'),y.jumlah,0)) as Cjjgunripeplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='C'),y.jumlah,0)) as Cjjgunripeluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='D'),y.jumlah,0)) as Djjgunripeinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='D'),y.jumlah,0)) as Djjgunripeplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='D'),y.jumlah,0)) as Djjgunripeluar
			,sum(if(trim(substr(a.kodeorg,3,2))<>'PE' and trim(substr(a.kodeorg,3,2))<>'' and (y.kodefraksi='E'),y.jumlah,0)) as Ejjgunripeinti
			,sum(if(trim(substr(a.kodeorg,3,2))='PE' and (y.kodefraksi='E'),y.jumlah,0)) as Ejjgunripeplasma
			,sum(if(trim(substr(a.kodeorg,3,2))='' and (y.kodefraksi='E'),y.jumlah,0)) as Ejjgunripeluar
			from ".$dbname.".pabrik_sortasi y
			LEFT JOIN ".$dbname.".vw_pabrik_timbangan_7ke7 a on a.notransaksi=y.notiket
			where a.kodebarang='40000003' and a.millcode='".$kodeorg."' and a.tanggal like '".$periode."%'
			GROUP BY a.millcode,left(a.tanggal,7)
			ORDER BY a.millcode,left(a.tanggal,7)
			";
	//exit('Warning: '.$sMill);
	$qMill=mysql_query($sMill) or die(mysql_error($conn));
	while($dMill=mysql_fetch_assoc($qMill)){
		$xbln=date('n', strtotime($dMill['periode']."-01"));

		//$gradinti_ur[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['jjgunripeinti']/$dMill['jjgsortasiinti']*100);
		$Bgradinti_ur[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['Bjjgunripeinti']/$dMill['jjgsortasiinti']*100);
		$Cgradinti_ur[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['Cjjgunripeinti']/$dMill['jjgsortasiinti']*100);
		$Dgradinti_ur[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['Djjgunripeinti']/$dMill['jjgsortasiinti']*100);
		$Egradinti_ur[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['Ejjgunripeinti']/$dMill['jjgsortasiinti']*100);
		$gradinti_ur[$xbln]=round($Bgradinti_ur[$xbln],2)+round($Cgradinti_ur[$xbln],2)+round($Dgradinti_ur[$xbln],2)+round($Egradinti_ur[$xbln],2);
		$gradinti_or[$xbln]=($dMill['jjgsortasiinti']==0 ? 0 : $dMill['jjgoverinti']/$dMill['jjgsortasiinti']*100);
		$gradinti_nr[$xbln]=100-($gradinti_ur[$xbln]+$gradinti_or[$xbln]);

		//$gradplasma_ur[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['jjgunripeplasma']/$dMill['jjgsortasiplasma']*100);
		$Bgradplasma_ur[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['Bjjgunripeplasma']/$dMill['jjgsortasiplasma']*100);
		$Cgradplasma_ur[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['Cjjgunripeplasma']/$dMill['jjgsortasiplasma']*100);
		$Dgradplasma_ur[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['Djjgunripeplasma']/$dMill['jjgsortasiplasma']*100);
		$Egradplasma_ur[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['Ejjgunripeplasma']/$dMill['jjgsortasiplasma']*100);
		$gradplasma_ur[$xbln]=round($Bgradplasma_ur[$xbln],2)+round($Cgradplasma_ur[$xbln],2)+round($Dgradplasma_ur[$xbln],2)+round($Egradplasma_ur[$xbln],2);
		$gradplasma_or[$xbln]=($dMill['jjgsortasiplasma']==0 ? 0 : $dMill['jjgoverplasma']/$dMill['jjgsortasiplasma']*100);
		$gradplasma_nr[$xbln]=100-($gradplasma_ur[$xbln]+$gradplasma_or[$xbln]);

		//$gradluar_ur[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['jjgunripeluar']/$dMill['jjgsortasiluar']*100);
		$Bgradluar_ur[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['Bjjgunripeluar']/$dMill['jjgsortasiluar']*100);
		$Cgradluar_ur[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['Cjjgunripeluar']/$dMill['jjgsortasiluar']*100);
		$Dgradluar_ur[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['Djjgunripeluar']/$dMill['jjgsortasiluar']*100);
		$Egradluar_ur[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['Ejjgunripeluar']/$dMill['jjgsortasiluar']*100);
		$gradluar_ur[$xbln]=round($Bgradluar_ur[$xbln],2)+round($Cgradluar_ur[$xbln],2)+round($Dgradluar_ur[$xbln],2)+round($Egradluar_ur[$xbln],2);
		$gradluar_or[$xbln]=($dMill['jjgsortasiluar']==0 ? 0 : $dMill['jjgoverluar']/$dMill['jjgsortasiluar']*100);
		$gradluar_nr[$xbln]=100-($gradluar_ur[$xbln]+$gradluar_or[$xbln]);
	}

	#ambil data SPAT ==========================
	$sMill="select '".$kodeorg."' as kodeorg,left(a.tanggal,7) as periode
			,round(sum(if(b.intiplasma='I',a.kgwb,0)),0) as tbsterima_inti
			,round(sum(if(b.intiplasma='P',a.kgwb,0)),0) as tbsterima_plasma
			from ".$dbname.".kebun_spb_vw a
			LEFT JOIN ".$dbname.".setup_blok b on b.kodeorg=a.blok
			where a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$optInduk[$kodeorg]."') and a.tanggal like '".$periode."%'
			GROUP BY left(a.tanggal,7)
			ORDER BY left(a.tanggal,7)
			";
	//exit('Warning: '.$sMill);
	$qMill=mysql_query($sMill) or die(mysql_error($conn));
	while($dMill=mysql_fetch_assoc($qMill)){
		$xbln=date('n', strtotime($dMill['periode']."-01"));
		$tbsinti[$xbln]=$dMill['tbsterima_inti'];
		$tbsplasma[$xbln]=$dMill['tbsterima_plasma'];
		$seninti[$xbln]=$dMill['tbsterima_inti']/$tbsterima[$xbln]*100;
		$senplasma[$xbln]=$dMill['tbsterima_plasma']/$tbsterima[$xbln]*100;
	}

	#ambil data Material Ballance
	$str="select a.*,if(isnull(b.intiplasma),'L',b.intiplasma) as intiplasma
			from ".$dbname.".pabrik_materialballance a
			left join ".$dbname.".setup_blok b on b.kodeorg=a.kodeblok
			where true ".$where." 
			ORDER BY a.kodeorg,left(a.kodeblok,4) desc,left(a.kodeblok,6),a.kodeblok,left(a.tanggal,7)
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	//$rnum=mysql_num_rows($res);
	//if($rnum==0){
	//	exit('Warning: Data not found...!');
	//}
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

		$gtoil_ur[$xbln][$xinti]+=($bar->oilinfiber_ur+$bar->oilinshell_ur);
		$gtkernel_ur[$xbln][$xinti]+=$bar->kerneldry_ur;
		$gthasil_ur[$xbln][$xinti]+=($bar->brondolan_ur/(($bar->brondolluar_ur+$bar->brondoldalam_ur)/$bar->berattbs_ur*100)*100);

		$gtoil_nr[$xbln][$xinti]+=($bar->oilinfiber_nr+$bar->oilinshell_nr);
		$gtkernel_nr[$xbln][$xinti]+=$bar->kerneldry_nr;
		$gthasil_nr[$xbln][$xinti]+=($bar->brondolan_nr/(($bar->brondolluar_nr+$bar->brondoldalam_nr)/$bar->berattbs_nr*100)*100);

		$gtoil_or[$xbln][$xinti]+=($bar->oilinfiber_or+$bar->oilinshell_or);
		$gtkernel_or[$xbln][$xinti]+=$bar->kerneldry_or;
		$gthasil_or[$xbln][$xinti]+=($bar->brondolan_or/(($bar->brondolluar_or+$bar->brondoldalam_or)/$bar->berattbs_or*100)*100);

		$gtoil_tt[$xbln][$xinti]=($gtoil_ur[$xbln][$xinti]+$gtoil_nr[$xbln][$xinti]+$gtoil_or[$xbln][$xinti]);
		$gtkernel_tt[$xbln][$xinti]=($gtkernel_ur[$xbln][$xinti]+$gtkernel_nr[$xbln][$xinti]+$gtkernel_or[$xbln][$xinti]);
		$gthasil_tt[$xbln][$xinti]=($gthasil_ur[$xbln][$xinti]+$gthasil_nr[$xbln][$xinti]+$gthasil_or[$xbln][$xinti]);
	}

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
			<td colspan=14 ".$bgclr.">OER</td>
			<td ".$bgclr.">&nbsp</td>
			<td colspan=14 ".$bgclr.">KER</td>
        </tr>
        <tr>
			<td ".$bgclr.">".$_SESSION['lang']['periode']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tbsterima']." (%)</td>
			<td ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td ".$bgclr.">VAR</td>
			<td ".$bgclr.">Unripe</td>
			<td ".$bgclr.">Normal ripe</td>
			<td ".$bgclr.">Over ripe</td>
			<td ".$bgclr.">TOTAL</td>
			<td ".$bgclr.">&nbsp</td>
			<td ".$bgclr.">".$_SESSION['lang']['deskripsi']."</td>
			<td ".$bgclr.">INTI</td>
			<td ".$bgclr.">PLASMA</td>
			<td ".$bgclr.">LUAR</td>
			<td ".$bgclr.">TOTAL</td>
			<td ".$bgclr.">&nbsp</td>
			<td ".$bgclr.">".$_SESSION['lang']['periode']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tbsterima']." (%)</td>
			<td ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td ".$bgclr.">VAR</td>
			<td ".$bgclr.">Unripe</td>
			<td ".$bgclr.">Normal ripe</td>
			<td ".$bgclr.">Over ripe</td>
			<td ".$bgclr.">TOTAL</td>
			<td ".$bgclr.">&nbsp</td>
			<td ".$bgclr.">".$_SESSION['lang']['deskripsi']."</td>
			<td ".$bgclr.">INTI</td>
			<td ".$bgclr.">PLASMA</td>
			<td ".$bgclr.">LUAR</td>
			<td ".$bgclr.">TOTAL</td>
        </tr>
		</thead><tbody>";

	for ($xbln=1; $xbln<=12; $xbln++){
		$xper=$periode.($xbln<=9 ? '-0'.$xbln : '-'.$xbln);
		//----- OER -----
		$gtintioer_ur[$xbln]=($gthasil_ur[$xbln]['I']==0 ? 0 : ($gtoil_ur[$xbln]['I']/$gthasil_ur[$xbln]['I']*100)-$xoerlosses_ur);
		$gtintioer_nr[$xbln]=($gthasil_nr[$xbln]['I']==0 ? 0 : ($gtoil_nr[$xbln]['I']/$gthasil_nr[$xbln]['I']*100)-$xoerlosses_nr);
		$gtintioer_or[$xbln]=($gthasil_or[$xbln]['I']==0 ? 0 : ($gtoil_or[$xbln]['I']/$gthasil_or[$xbln]['I']*100)-$xoerlosses_or);
		$gtintioer_tt[$xbln]=($gthasil_tt[$xbln]['I']==0 ? 0 : ($gtoil_tt[$xbln]['I']/$gthasil_tt[$xbln]['I']*100)-$xoerlosses_tt);
		$mbintioer_ur[$xbln]=$gradinti_ur[$xbln]*$gtintioer_ur[$xbln]/100;
		$mbintioer_nr[$xbln]=$gradinti_nr[$xbln]*$gtintioer_nr[$xbln]/100;
		$mbintioer_or[$xbln]=$gradinti_or[$xbln]*$gtintioer_or[$xbln]/100;
		$mbintioer_tt[$xbln]=$mbintioer_ur[$xbln]+$mbintioer_nr[$xbln]+$mbintioer_or[$xbln];

		$gtplasmaoer_ur[$xbln]=($gthasil_ur[$xbln]['P']==0 ? 0 : ($gtoil_ur[$xbln]['P']/$gthasil_ur[$xbln]['P']*100)-$xoerlosses_ur);
		$gtplasmaoer_nr[$xbln]=($gthasil_nr[$xbln]['P']==0 ? 0 : ($gtoil_nr[$xbln]['P']/$gthasil_nr[$xbln]['P']*100)-$xoerlosses_nr);
		$gtplasmaoer_or[$xbln]=($gthasil_or[$xbln]['P']==0 ? 0 : ($gtoil_or[$xbln]['P']/$gthasil_or[$xbln]['P']*100)-$xoerlosses_or);
		$gtplasmaoer_tt[$xbln]=($gthasil_tt[$xbln]['P']==0 ? 0 : ($gtoil_tt[$xbln]['P']/$gthasil_tt[$xbln]['P']*100)-$xoerlosses_tt);
		$mbplasmaoer_ur[$xbln]=$gradplasma_ur[$xbln]*$gtplasmaoer_ur[$xbln]/100;
		$mbplasmaoer_nr[$xbln]=$gradplasma_nr[$xbln]*$gtplasmaoer_nr[$xbln]/100;
		$mbplasmaoer_or[$xbln]=$gradplasma_or[$xbln]*$gtplasmaoer_or[$xbln]/100;
		$mbplasmaoer_tt[$xbln]=$mbplasmaoer_ur[$xbln]+$mbplasmaoer_nr[$xbln]+$mbplasmaoer_or[$xbln];

		$gtluaroer_ur[$xbln]=($gthasil_ur[$xbln]['L']==0 ? 0 : ($gtoil_ur[$xbln]['L']/$gthasil_ur[$xbln]['L']*100)-$xoerlosses_ur);
		$gtluaroer_nr[$xbln]=($gthasil_nr[$xbln]['L']==0 ? 0 : ($gtoil_nr[$xbln]['L']/$gthasil_nr[$xbln]['L']*100)-$xoerlosses_nr);
		$gtluaroer_or[$xbln]=($gthasil_or[$xbln]['L']==0 ? 0 : ($gtoil_or[$xbln]['L']/$gthasil_or[$xbln]['L']*100)-$xoerlosses_or);
		$gtluaroer_tt[$xbln]=($gthasil_tt[$xbln]['L']==0 ? 0 : ($gtoil_tt[$xbln]['L']/$gthasil_tt[$xbln]['L']*100)-$xoerlosses_tt);
		$mbluaroer_ur[$xbln]=$gradluar_ur[$xbln]*$gtluaroer_ur[$xbln]/100;
		$mbluaroer_nr[$xbln]=$gradluar_nr[$xbln]*$gtluaroer_nr[$xbln]/100;
		$mbluaroer_or[$xbln]=$gradluar_or[$xbln]*$gtluaroer_or[$xbln]/100;
		$mbluaroer_tt[$xbln]=$mbluaroer_ur[$xbln]+$mbluaroer_nr[$xbln]+$mbluaroer_or[$xbln];

		$ptintioer_tt[$xbln]=$mbintioer_tt[$xbln]*$seninti[$xbln]/100;
		$ptplasmaoer_tt[$xbln]=$mbplasmaoer_tt[$xbln]*$senplasma[$xbln]/100;
		$ptluaroer_tt[$xbln]=$mbluaroer_tt[$xbln]*$senluar[$xbln]/100;
		$pttotaloer_tt[$xbln]=$ptintioer_tt[$xbln]+$ptplasmaoer_tt[$xbln]+$ptluaroer_tt[$xbln];

		//----- KER -----
		$gtintiker_ur[$xbln]=($gthasil_ur[$xbln]['I']==0 ? 0 : ($gtkernel_ur[$xbln]['I']/$gthasil_ur[$xbln]['I']*100)-$xkerlosses_ur);
		$gtintiker_nr[$xbln]=($gthasil_nr[$xbln]['I']==0 ? 0 : ($gtkernel_nr[$xbln]['I']/$gthasil_nr[$xbln]['I']*100)-$xkerlosses_nr);
		$gtintiker_or[$xbln]=($gthasil_or[$xbln]['I']==0 ? 0 : ($gtkernel_or[$xbln]['I']/$gthasil_or[$xbln]['I']*100)-$xkerlosses_or);
		$gtintiker_tt[$xbln]=($gthasil_tt[$xbln]['I']==0 ? 0 : ($gtkernel_tt[$xbln]['I']/$gthasil_tt[$xbln]['I']*100)-$xkerlosses_tt);
		$mbintiker_ur[$xbln]=$gradinti_ur[$xbln]*$gtintiker_ur[$xbln]/100;
		$mbintiker_nr[$xbln]=$gradinti_nr[$xbln]*$gtintiker_nr[$xbln]/100;
		$mbintiker_or[$xbln]=$gradinti_or[$xbln]*$gtintiker_or[$xbln]/100;
		$mbintiker_tt[$xbln]=$mbintiker_ur[$xbln]+$mbintiker_nr[$xbln]+$mbintiker_or[$xbln];

		$gtplasmaker_ur[$xbln]=($gthasil_ur[$xbln]['P']==0 ? 0 : ($gtkernel_ur[$xbln]['P']/$gthasil_ur[$xbln]['P']*100)-$xkerlosses_ur);
		$gtplasmaker_nr[$xbln]=($gthasil_nr[$xbln]['P']==0 ? 0 : ($gtkernel_nr[$xbln]['P']/$gthasil_nr[$xbln]['P']*100)-$xkerlosses_nr);
		$gtplasmaker_or[$xbln]=($gthasil_or[$xbln]['P']==0 ? 0 : ($gtkernel_or[$xbln]['P']/$gthasil_or[$xbln]['P']*100)-$xkerlosses_or);
		$gtplasmaker_tt[$xbln]=($gthasil_tt[$xbln]['P']==0 ? 0 : ($gtkernel_tt[$xbln]['P']/$gthasil_tt[$xbln]['P']*100)-$xkerlosses_tt);
		$mbplasmaker_ur[$xbln]=$gradplasma_ur[$xbln]*$gtplasmaker_ur[$xbln]/100;
		$mbplasmaker_nr[$xbln]=$gradplasma_nr[$xbln]*$gtplasmaker_nr[$xbln]/100;
		$mbplasmaker_or[$xbln]=$gradplasma_or[$xbln]*$gtplasmaker_or[$xbln]/100;
		$mbplasmaker_tt[$xbln]=$mbplasmaker_ur[$xbln]+$mbplasmaker_nr[$xbln]+$mbplasmaker_or[$xbln];

		$gtluarker_ur[$xbln]=($gthasil_ur[$xbln]['L']==0 ? 0 : ($gtkernel_ur[$xbln]['L']/$gthasil_ur[$xbln]['L']*100)-$xkerlosses_ur);
		$gtluarker_nr[$xbln]=($gthasil_nr[$xbln]['L']==0 ? 0 : ($gtkernel_nr[$xbln]['L']/$gthasil_nr[$xbln]['L']*100)-$xkerlosses_nr);
		$gtluarker_or[$xbln]=($gthasil_or[$xbln]['L']==0 ? 0 : ($gtkernel_or[$xbln]['L']/$gthasil_or[$xbln]['L']*100)-$xkerlosses_or);
		$gtluarker_tt[$xbln]=($gthasil_tt[$xbln]['L']==0 ? 0 : ($gtkernel_tt[$xbln]['L']/$gthasil_tt[$xbln]['L']*100)-$xkerlosses_tt);
		$mbluarker_ur[$xbln]=$gradluar_ur[$xbln]*$gtluarker_ur[$xbln]/100;
		$mbluarker_nr[$xbln]=$gradluar_nr[$xbln]*$gtluarker_nr[$xbln]/100;
		$mbluarker_or[$xbln]=$gradluar_or[$xbln]*$gtluarker_or[$xbln]/100;
		$mbluarker_tt[$xbln]=$mbluarker_ur[$xbln]+$mbluarker_nr[$xbln]+$mbluarker_or[$xbln];

		$ptintiker_tt[$xbln]=$mbintiker_tt[$xbln]*$seninti[$xbln]/100;
		$ptplasmaker_tt[$xbln]=$mbplasmaker_tt[$xbln]*$senplasma[$xbln]/100;
		$ptluarker_tt[$xbln]=$mbluarker_tt[$xbln]*$senluar[$xbln]/100;
		$pttotalker_tt[$xbln]=$ptintiker_tt[$xbln]+$ptplasmaker_tt[$xbln]+$ptluarker_tt[$xbln];

		$stream.="<tr class=rowcontent>
					<td rowspan=9 align='center'>".$xper."</td>
					<td rowspan=3 align='right'>".number_format($seninti[$xbln],2)." %</td>
					<td rowspan=3 align='center'>INTI</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtintioer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtintioer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtintioer_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>OER MATERIAL BALANCE</td>
					<td align='right'>".number_format($mbintioer_tt[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaoer_tt[$xbln],2)."</td>
					<td align='right'>".number_format($mbluaroer_tt[$xbln],2)."</td>
					<td align='right'></td>
					<td rowspan=9 align='right'></td>
					<td rowspan=9 align='center'>".$xper."</td>
					<td rowspan=3 align='right'>".number_format($seninti[$xbln],2)." %</td>
					<td rowspan=3 align='center'>INTI</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtintiker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtintiker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtintiker_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>KER MATERIAL BALANCE</td>
					<td align='right'>".number_format($mbintiker_tt[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaker_tt[$xbln],2)."</td>
					<td align='right'>".number_format($mbluarker_tt[$xbln],2)."</td>
					<td align='right'></td>
		</tr>
				<tr class=rowcontent>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradinti_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradinti_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradinti_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>POTENSI OER on TBS TERIMA</td>
					<td align='right'>".number_format($ptintioer_tt[$xbln],2)."</td>
					<td align='right'>".number_format($ptplasmaoer_tt[$xbln],2)."</td>
					<td align='right'>".number_format($ptluaroer_tt[$xbln],2)."</td>
					<td align='right'>".number_format($pttotaloer_tt[$xbln],2)."</td>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradinti_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradinti_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradinti_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>POTENSI KER on TBS TERIMA</td>
					<td align='right'>".number_format($ptintiker_tt[$xbln],2)."</td>
					<td align='right'>".number_format($ptplasmaker_tt[$xbln],2)."</td>
					<td align='right'>".number_format($ptluarker_tt[$xbln],2)."</td>
					<td align='right'>".number_format($pttotalker_tt[$xbln],2)."</td>
				</tr>
				<tr class=rowcontent>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbintioer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbintioer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbintioer_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbintioer_tt[$xbln],2)."</td>
					<td align='right'></td>
					<td align='left'>REALISASI OER</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>".number_format($actual_oer[$xbln],2)."</td>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbintiker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbintiker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbintiker_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbintiker_tt[$xbln],2)."</td>
					<td align='right'></td>
					<td align='left'>REALISASI KER</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>".number_format($actual_ker[$xbln],2)."</td>
				</tr>";

		$stream.="<tr class=rowcontent>
					<td rowspan=3 align='right'>".number_format($senplasma[$xbln],2)." %</td>
					<td rowspan=3 align='center'>PLASMA</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtplasmaoer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtplasmaoer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtplasmaoer_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>VARIAN POTENSI - REAL</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>".number_format($actual_oer[$xbln]-$pttotaloer_tt[$xbln],2)."</td>
					<td rowspan=3 align='right'>".number_format($senplasma[$xbln],2)." %</td>
					<td rowspan=3 align='center'>PLASMA</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtplasmaker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtplasmaker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtplasmaker_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left'>VARIAN POTENSI - REAL</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>".number_format($actual_ker[$xbln]-$pttotalker_tt[$xbln],2)."</td>
				</tr>
				<tr class=rowcontent>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradplasma_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradplasma_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradplasma_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left' colspan=5 rowspan=5></td>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradplasma_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradplasma_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradplasma_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='left' colspan=5 rowspan=5></td>
				</tr>
				<tr class=rowcontent>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbplasmaoer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaoer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaoer_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaoer_tt[$xbln],2)."</td>
					<td align='right'></td>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbplasmaker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaker_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbplasmaker_tt[$xbln],2)."</td>
					<td align='right'></td>
				</tr>";

		$stream.="<tr class=rowcontent>
					<td rowspan=3 align='right'>".number_format($senluar[$xbln],2)." %</td>
					<td rowspan=3 align='center'>LUAR</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtluaroer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtluaroer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtluaroer_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td rowspan=3 align='right'>".number_format($senluar[$xbln],2)." %</td>
					<td rowspan=3 align='center'>LUAR</td>
					<td align='center'>MB</td>
					<td align='right'>".number_format($gtluarker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gtluarker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gtluarker_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
				</tr>
				<tr class=rowcontent>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradluar_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradluar_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradluar_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='center'>GRAD</td>
					<td align='right'>".number_format($gradluar_ur[$xbln],2)."</td>
					<td align='right'>".number_format($gradluar_nr[$xbln],2)."</td>
					<td align='right'>".number_format($gradluar_or[$xbln],2)."</td>
					<td align='right'></td>
					<td align='right'></td>
				</tr>
				<tr class=rowcontent>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbluaroer_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbluaroer_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbluaroer_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbluaroer_tt[$xbln],2)."</td>
					<td align='right'></td>
					<td align='center'>POTENSI</td>
					<td align='right'>".number_format($mbluarker_ur[$xbln],2)."</td>
					<td align='right'>".number_format($mbluarker_nr[$xbln],2)."</td>
					<td align='right'>".number_format($mbluarker_or[$xbln],2)."</td>
					<td align='right'>".number_format($mbluarker_tt[$xbln],2)."</td>
					<td align='right'></td>
				</tr>";
	}


	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Material Ballance Potensi';
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
