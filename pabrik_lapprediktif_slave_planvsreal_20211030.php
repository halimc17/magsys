<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses = checkPostGet('proses','');
	$pabrik = checkPostGet('pabrik2','');
	$station = checkPostGet('station2','');
	$machine = checkPostGet('mesin2','');
	$tahun = checkPostGet('tahun2','');
	$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	$arrPost=array("0"=>"Not Posted","1"=>"Posting");

	$stationTambah='';
	$where='';
	if($pabrik==''){
		exit('Warning : Pabrik tidak boleh kosong...!');
	}
	if($station!=''){
		$stationTambah.=" and b.statasiun='".$station."'";
		$where.=" and kodestasiun='".$station."'";
	}
	if($machine!=''){
		$stationTambah.=" and b.mesin='".$machine."'";
		$where.=" and kodemesin='".$machine."'";
	}
	if($tahun!=''){
		$stationTambah.=" and b.tanggal like '".$tahun."%'";
	}else{
		exit('Warning : Tahun tidak boleh kosong...!');
	}

	if($proses=='excel'){
		$border="border=1";
	}else{
		$border="border=0";
	}
	//bgcolor=#CCCCCC border='1'

	$stream="<table cellspacing='1' $border class='sortable'>";
	$stream.="<thead><tr class=rowheader>
	            <td rowspan=2 align=center>No</td>
		        <td rowspan=2 align=center>".$_SESSION['lang']['perawatan'].' '.$_SESSION['lang']['station'].' '.$nmOrg[$station]."</td>
			    <td rowspan=2 align=center>".$_SESSION['lang']['mesin']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['tipe']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jan']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['peb']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['mar']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['apr']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['mei']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jun']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jul']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['agt']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['sep']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['okt']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['nov']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['dec']."</td>";
	$stream.="</tr><tr class=rowheader>";
	for($i=1;$i<=12;$i++){
		$stream.="	<td align=center width='11px;'>1</td>
					<td align=center width='11px;'>2</td>
					<td align=center width='11px;'>3</td>
					<td align=center width='11px;'>4</td>
					<td align=center width='11px;'>5</td>";
	}
	$stream.="</tr></thead><tbody>";

	$sPlan="select b.statasiun as kodestasiun,b.mesin as kodemesin,a.nomor,b.tanggal,'P' as tipe 
			FROM ".$dbname.".pabrik_predictivedt_pekerjaan a 
			LEFT JOIN ".$dbname.".pabrik_predictiveht b on b.notransaksi=a.notransaksi 
			WHERE b.statasiun like '".$pabrik."%' ".$stationTambah." 
			ORDER BY b.statasiun,b.mesin,a.nomor,b.tanggal";
	$qPlan=mysql_query($sPlan) or die (mysql_error($conn));	
	while($dPlan=mysql_fetch_assoc($qPlan)){
		$tglnilai=$dPlan['tanggal'];
		$tglakhir=date('t', strtotime($tglnilai));
		$bulanke=substr($tglnilai,5,2);
		$mingguke=0;
		for($i=1;$i<=$tglakhir;$i++){
			if(date('N', strtotime(substr($tglnilai,0,8).'01'))==7){
				$mingguke=0;
			}else{
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
					$mingguke+=1;
				}
				if(date('Y-m-d', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==$tglnilai){
					if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))!=7){
						$mingguke+=1;
					}
					break;
				}
			}
		}
		$centangP[$dPlan['kodestasiun']][$dPlan['kodemesin']][$dPlan['nomor']][$bulanke][$mingguke]='x';
	}
	$sReal="select b.statasiun as kodestasiun,b.mesin as kodemesin,a.nomor,b.tanggal,'R' as tipe 
			FROM ".$dbname.".pabrik_rawatmesindt_pekerjaan a 
			LEFT JOIN ".$dbname.".pabrik_rawatmesinht b on b.notransaksi=a.notransaksi 
			WHERE b.statasiun like '".$pabrik."%' ".$stationTambah." 
			ORDER BY b.statasiun,b.mesin,a.nomor,b.tanggal";
	$qReal=mysql_query($sReal) or die (mysql_error($conn));	
	while($dReal=mysql_fetch_assoc($qReal)){
		$tglnilai=$dReal['tanggal'];
		$tglakhir=date('t', strtotime($tglnilai));
		$bulanke=substr($tglnilai,5,2);
		$mingguke=0;
		for($i=1;$i<=$tglakhir;$i++){
			if(date('N', strtotime(substr($tglnilai,0,8).'01'))==7){
				$mingguke=0;
			}else{
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
					$mingguke+=1;
				}
				if(date('Y-m-d', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==$tglnilai){
					if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))!=7){
						$mingguke+=1;
					}
					break;
				}
			}
		}
		$centangR[$dReal['kodestasiun']][$dReal['kodemesin']][$dReal['nomor']][$bulanke][$mingguke]='x';
	}

	$sList="select * FROM ".$dbname.".pabrik_5masterpreventive
			WHERE kodestasiun like '".$pabrik."%' ".$where." 
			ORDER BY kodestasiun,kodemesin,nomor";
	$qList=mysql_query($sList) or die (mysql_error($conn));	
	while($dList=mysql_fetch_assoc($qList)){
		$noList+=1;
		$stream.="<tr class=rowcontent>
		            <td align=center>".$noList."</td>
			        <td align=left>".$dList['rincian']."</td>
			        <td align=left>".$nmOrg[$dList['kodemesin']]."</td>
					<td align=center>Plan</td>";
		for($i=1;$i<=12;$i++){
			$bulanke=sprintf("%02d",$i);
			$stream.="<td align=center>".$centangP[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][1]."</td>
					<td align=center>".$centangP[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][2]."</td>
					<td align=center>".$centangP[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][3]."</td>
					<td align=center>".$centangP[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][4]."</td>
					<td align=center>".$centangP[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][5]."</td>";
		}
		$stream.="</tr>";
		$stream.="<tr class=rowcontent>
		            <td align=center></td>
			        <td align=left></td>
			        <td align=left></td>
					<td align=center>Real</td>";
		for($i=1;$i<=12;$i++){
			$bulanke=sprintf("%02d",$i);
			$stream.="<td align=center>".$centangR[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][1]."</td>
					<td align=center>".$centangR[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][2]."</td>
					<td align=center>".$centangR[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][3]."</td>
					<td align=center>".$centangR[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][4]."</td>
					<td align=center>".$centangR[$dList['kodestasiun']][$dList['kodemesin']][$dList['nomor']][$bulanke][5]."</td>";
		}
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";

	############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
	switch($proses){
		case 'preview':
			echo $stream;
			break;

		case 'excel':
			$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			$tglSkrg=date("Ymd");
			$nop_="LAPORAN_PREDICTIVE_PLAN_vs_REAL_".$tglSkrg;
			$judul="<h3>LAPORAN PREDICTIVE PLAN vs REAL";
			$judul.="<BR>".($station=='' ? $nmOrg[$station] : $nmOrg[$station])."";
			$judul.="<BR>Periode : ".$tahun."</h3>";
			if(strlen($stream)>0){
				$stream=$judul.$stream;
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
