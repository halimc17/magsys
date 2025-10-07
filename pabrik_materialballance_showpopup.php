<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$kodeorg	=checkPostGet('kodeorg','');
	$kodeblok	=checkPostGet('kodeblok','');
	$tanggal	=tanggalsystem(checkPostGet('tanggal',''));
	$type		=checkPostGet('type','');
	$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$where="True";
	if($kodeorg!=''){
		$where.=" and a.kodeorg='".$kodeorg."'";
	}
	if($kodeblok!=''){
		$where.=" and a.kodeblok='".$kodeblok."'";
	}
	if($tanggal!=''){
		$where.=" and a.tanggal='".$tanggal."'";
	}
	$strz="select a.*,b.namaorganisasi as namablok from ".$dbname.".pabrik_materialballance a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeblok
			where ".$where." 
			order by a.kodeorg,a.tanggal desc,a.kodeblok";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rdata=mysql_fetch_assoc($resz);
	if($type=='excel'){
		$brd=1;
	}else{
		$brd=0;
	}
	$stream2="
		<table>
			<tr>
				<td colspan=17 align=center>".$optNm[$_SESSION['empl']['induk']]."</td>
			</tr>
			<tr>
				<td colspan=17 align=center><h3>MATERIAL BALLANCE TBS</h3></td>
			</tr>
			<tr>
				<td colspan=17 align=center>".(substr($rdata['kodeblok'],0,6)=='TBSEXT' ? 'TBS Luar' : $optNm[substr($rdata['kodeblok'],0,6)])."</td>
			</tr>
			<tr>
				<td colspan=17 align=center>".$_SESSION['lang']['tanggal'].' : '.$rdata['tanggal']."</td>
			</tr>
		</table>
		<table>
			<tr>
				<td valign=top>
					<table class=sortable border=".$brd." cellspacing=1>
						<tr>
							<td colspan=5 align=center>Buah Unripe</td>
						</tr>
						<thead><tr>
							<td align=center>DESCRIPTION</td>
							<td align=center>VALUE</td>
							<td align=center>SAT</td>
							<td align=center>%</td>
							<td align=center>% FFB</td>
						</tr></thead>
						<tr>
							<td>Berat TBS</td>
							<td>".number_format($rdata['berattbs_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>TBS Rebus</td>
							<td>".number_format($rdata['tbsrebus_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['tbsrebus_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Evaporasi + Condensate</td>
							<td>".number_format($rdata['berattbs_ur']-$rdata['tbsrebus_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_ur']-$rdata['tbsrebus_ur'])/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Luar</td>
							<td>".number_format($rdata['brondolluar_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondolluar_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Dalam</td>
							<td>".number_format($rdata['brondoldalam_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondoldalam_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>ABN</td>
							<td>".number_format($rdata['abn_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['abn_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Calix Leaves & Dirt</td>
							<td>".number_format($rdata['calix_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['calix_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Janjang Kosong</td>
							<td>".number_format($rdata['jangkos_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['jangkos_ur']/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>TOTAL</b></td>
							<td>".number_format($rdata['berattbs_ur']-$rdata['tbsrebus_ur']+$rdata['brondolluar_ur']+$rdata['brondoldalam_ur']+$rdata['abn_ur']+$rdata['calix_ur']+$rdata['jangkos_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_ur']-$rdata['tbsrebus_ur']+$rdata['brondolluar_ur']+$rdata['brondoldalam_ur']+$rdata['abn_ur']+$rdata['calix_ur']+$rdata['jangkos_ur'])/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Sampel Check Brondolan</td>
							<td>".number_format($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td colspan=5><b>Sub Sampel Brondolan</b></td>
						</tr>
						<tr>
							<td>Brondolan</td>
							<td>".number_format($rdata['brondolan_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Evaporation</td>
							<td>".number_format($rdata['brondolan_ur']-$rdata['brondoldry_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondolan_ur']-$rdata['brondoldry_ur'])/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*(($rdata['brondolan_ur']-$rdata['brondoldry_ur'])/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total Brondolan Dry</td>
							<td>".number_format($rdata['brondoldry_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>* Mesocrap ( Fiber )</td>
							<td>".number_format($rdata['brondoldry_ur']-$rdata['nut_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondoldry_ur']-$rdata['nut_ur'])/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*(($rdata['brondoldry_ur']-$rdata['nut_ur'])/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>* Nut</td>
							<td>".number_format($rdata['nut_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['nut_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['nut_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Shell</td>
							<td>".number_format($rdata['shell_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['shell_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['shell_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Kernel</td>
							<td>".number_format($rdata['kernel_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kernel_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['kernel_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Kernel Dry</td>
							<td>".number_format($rdata['kerneldry_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kerneldry_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['kerneldry_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossestbs_ur'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['kerneldry_ur']/$rdata['brondolan_ur']*100)/100)-$rdata['lossestbs_ur'],2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Oil in Fiber</td>
							<td>".number_format($rdata['oilinfiber_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinfiber_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['oilinfiber_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Oil in Shell</td>
							<td>".number_format($rdata['oilinshell_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinshell_ur']/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*($rdata['oilinshell_ur']/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Total OIL</b></td>
							<td>".number_format($rdata['oilinfiber_ur']+$rdata['oilinshell_ur'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['oilinfiber_ur']+$rdata['oilinshell_ur'])/$rdata['brondolan_ur']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*(($rdata['oilinfiber_ur']+$rdata['oilinshell_ur'])/$rdata['brondolan_ur']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossesoil_ur'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*(($rdata['oilinfiber_ur']+$rdata['oilinshell_ur'])/$rdata['brondolan_ur']*100)/100)-$rdata['lossesoil_ur'],2,'.',',')."</td>
						</tr>
						<tr>
							<td></td>
							<td>".number_format($rdata['brondolan_ur']/(($rdata['brondolluar_ur']+$rdata['brondoldalam_ur'])/$rdata['berattbs_ur']*100)*100,4,'.',',')."</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</td>

				<td valign=top style='width:2px;'>_</td>

				<td valign=top>
					<table class=sortable border=".$brd." cellspacing=1>
						<tr>
							<td colspan=5 align=center>Buah Normal Ripe</td>
						</tr>
						<thead><tr>
							<td align=center>DESCRIPTION</td>
							<td align=center>VALUE</td>
							<td align=center>SAT</td>
							<td align=center>%</td>
							<td align=center>% FFB</td>
						</tr></thead>
						<tr>
							<td>Berat TBS</td>
							<td>".number_format($rdata['berattbs_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>TBS Rebus</td>
							<td>".number_format($rdata['tbsrebus_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['tbsrebus_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Evaporasi + Condensate</td>
							<td>".number_format($rdata['berattbs_nr']-$rdata['tbsrebus_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_nr']-$rdata['tbsrebus_nr'])/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Luar</td>
							<td>".number_format($rdata['brondolluar_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondolluar_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Dalam</td>
							<td>".number_format($rdata['brondoldalam_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondoldalam_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>ABN</td>
							<td>".number_format($rdata['abn_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['abn_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Calix Leaves & Dirt</td>
							<td>".number_format($rdata['calix_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['calix_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Janjang Kosong</td>
							<td>".number_format($rdata['jangkos_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['jangkos_nr']/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>TOTAL</b></td>
							<td>".number_format($rdata['berattbs_nr']-$rdata['tbsrebus_nr']+$rdata['brondolluar_nr']+$rdata['brondoldalam_nr']+$rdata['abn_nr']+$rdata['calix_nr']+$rdata['jangkos_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_nr']-$rdata['tbsrebus_nr']+$rdata['brondolluar_nr']+$rdata['brondoldalam_nr']+$rdata['abn_nr']+$rdata['calix_nr']+$rdata['jangkos_nr'])/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Sampel Check Brondolan</td>
							<td>".number_format($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td colspan=5><b>Sub Sampel Brondolan</b></td>
						</tr>
						<tr>
							<td>Brondolan</td>
							<td>".number_format($rdata['brondolan_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Evaporation</td>
							<td>".number_format($rdata['brondolan_nr']-$rdata['brondoldry_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondolan_nr']-$rdata['brondoldry_nr'])/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*(($rdata['brondolan_nr']-$rdata['brondoldry_nr'])/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total Brondolan Dry</td>
							<td>".number_format($rdata['brondoldry_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>* Mesocrap ( Fiber )</td>
							<td>".number_format($rdata['brondoldry_nr']-$rdata['nut_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondoldry_nr']-$rdata['nut_nr'])/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*(($rdata['brondoldry_nr']-$rdata['nut_nr'])/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>* Nut</td>
							<td>".number_format($rdata['nut_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['nut_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['nut_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Shell</td>
							<td>".number_format($rdata['shell_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['shell_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['shell_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Kernel</td>
							<td>".number_format($rdata['kernel_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kernel_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['kernel_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Kernel Dry</td>
							<td>".number_format($rdata['kerneldry_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kerneldry_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['kerneldry_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossestbs_nr'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['kerneldry_nr']/$rdata['brondolan_nr']*100)/100)-$rdata['lossestbs_nr'],2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Oil in Fiber</td>
							<td>".number_format($rdata['oilinfiber_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinfiber_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['oilinfiber_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Oil in Shell</td>
							<td>".number_format($rdata['oilinshell_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinshell_nr']/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*($rdata['oilinshell_nr']/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Total OIL</b></td>
							<td>".number_format($rdata['oilinfiber_nr']+$rdata['oilinshell_nr'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['oilinfiber_nr']+$rdata['oilinshell_nr'])/$rdata['brondolan_nr']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*(($rdata['oilinfiber_nr']+$rdata['oilinshell_nr'])/$rdata['brondolan_nr']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossesoil_nr'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*(($rdata['oilinfiber_nr']+$rdata['oilinshell_nr'])/$rdata['brondolan_nr']*100)/100)-$rdata['lossesoil_nr'],2,'.',',')."</td>
						</tr>
						<tr>
							<td></td>
							<td>".number_format($rdata['brondolan_nr']/(($rdata['brondolluar_nr']+$rdata['brondoldalam_nr'])/$rdata['berattbs_nr']*100)*100,4,'.',',')."</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</td>

				<td valign=top style='width:2px;'>_</td>

				<td valign=top>
					<table class=sortable border=".$brd." cellspacing=1>
						<tr>
							<td colspan=5 align=center>Buah Over Ripe</td>
						</tr>
						<thead><tr>
							<td align=center>DESCRIPTION</td>
							<td align=center>VALUE</td>
							<td align=center>SAT</td>
							<td align=center>%</td>
							<td align=center>% FFB</td>
						</tr></thead>
						<tr>
							<td>Berat TBS</td>
							<td>".number_format($rdata['berattbs_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>TBS Rebus</td>
							<td>".number_format($rdata['tbsrebus_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['tbsrebus_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Evaporasi + Condensate</td>
							<td>".number_format($rdata['berattbs_or']-$rdata['tbsrebus_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_or']-$rdata['tbsrebus_or'])/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Luar</td>
							<td>".number_format($rdata['brondolluar_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondolluar_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Brondolan Dalam</td>
							<td>".number_format($rdata['brondoldalam_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['brondoldalam_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>ABN</td>
							<td>".number_format($rdata['abn_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['abn_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Calix Leaves & Dirt</td>
							<td>".number_format($rdata['calix_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['calix_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Janjang Kosong</td>
							<td>".number_format($rdata['jangkos_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format($rdata['jangkos_or']/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>TOTAL</b></td>
							<td>".number_format($rdata['berattbs_or']-$rdata['tbsrebus_or']+$rdata['brondolluar_or']+$rdata['brondoldalam_or']+$rdata['abn_or']+$rdata['calix_or']+$rdata['jangkos_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['berattbs_or']-$rdata['tbsrebus_or']+$rdata['brondolluar_or']+$rdata['brondoldalam_or']+$rdata['abn_or']+$rdata['calix_or']+$rdata['jangkos_or'])/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Sampel Check Brondolan</td>
							<td>".number_format($rdata['brondolluar_or']+$rdata['brondoldalam_or'],0,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td>".number_format(($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td colspan=5><b>Sub Sampel Brondolan</b></td>
						</tr>
						<tr>
							<td>Brondolan</td>
							<td>".number_format($rdata['brondolan_or'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Evaporation</td>
							<td>".number_format($rdata['brondolan_or']-$rdata['brondoldry_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondolan_or']-$rdata['brondoldry_or'])/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*(($rdata['brondolan_or']-$rdata['brondoldry_or'])/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total Brondolan Dry</td>
							<td>".number_format($rdata['brondoldry_or'],4,'.',',')."</td>
							<td>gram</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>* Mesocrap ( Fiber )</td>
							<td>".number_format($rdata['brondoldry_or']-$rdata['nut_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['brondoldry_or']-$rdata['nut_or'])/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*(($rdata['brondoldry_or']-$rdata['nut_or'])/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>* Nut</td>
							<td>".number_format($rdata['nut_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['nut_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['nut_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Shell</td>
							<td>".number_format($rdata['shell_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['shell_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['shell_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>- Kernel</td>
							<td>".number_format($rdata['kernel_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kernel_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['kernel_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Kernel Dry</td>
							<td>".number_format($rdata['kerneldry_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['kerneldry_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['kerneldry_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossestbs_or'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['kerneldry_or']/$rdata['brondolan_or']*100)/100)-$rdata['lossestbs_or'],2,'.',',')."</td>
						</tr>
						<tr>
							<td> </td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>Oil in Fiber</td>
							<td>".number_format($rdata['oilinfiber_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinfiber_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['oilinfiber_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Oil in Shell</td>
							<td>".number_format($rdata['oilinshell_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format($rdata['oilinshell_or']/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*($rdata['oilinshell_or']/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td><b>Total OIL</b></td>
							<td>".number_format($rdata['oilinfiber_or']+$rdata['oilinshell_or'],4,'.',',')."</td>
							<td>gram</td>
							<td>".number_format(($rdata['oilinfiber_or']+$rdata['oilinshell_or'])/$rdata['brondolan_or']*100,2,'.',',')."</td>
							<td>".number_format((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*(($rdata['oilinfiber_or']+$rdata['oilinshell_or'])/$rdata['brondolan_or']*100)/100,2,'.',',')."</td>
						</tr>
						<tr>
							<td>Absolut Losses</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format($rdata['lossesoil_or'],2,'.',',')."</td>
						</tr>
						<tr>
							<td>Total</td>
							<td></td>
							<td></td>
							<td></td>
							<td>".number_format(((($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*(($rdata['oilinfiber_or']+$rdata['oilinshell_or'])/$rdata['brondolan_or']*100)/100)-$rdata['lossesoil_or'],2,'.',',')."</td>
						</tr>
						<tr>
							<td></td>
							<td>".number_format($rdata['brondolan_or']/(($rdata['brondolluar_or']+$rdata['brondoldalam_or'])/$rdata['berattbs_or']*100)*100,4,'.',',')."</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Material_Ballance_".$rdata['kodeorg'].'_'.$rdata['kodeblok'].'_'.$rdata['tanggal'].'_'.date("YmdHis");
        if(strlen($stream2)>0){
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream2)){
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
	}   
?>
