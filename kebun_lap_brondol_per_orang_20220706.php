<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses = checkPostGet('proses','');
	$kodeorg= checkPostGet('kebun0','');
	$divisi	= checkPostGet('divisi0','');
	$periode= checkPostGet('periode0','');

	if($proses=='getSubUnit'){
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe='AFDELING' order by kodeorganisasi asc ";
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optDivisi.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optDivisi;
		exit;
	}
	if($proses=='getKary'){
		$whr="True";
		if($kodeorg!=''){
			$whr.=" and subbagian like '".$kodeorg."%'";
		}
		if($divisi!=''){
			$whr.=" and subbagian like '".$divisi."%'";
		}
		$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where ".$whr." order by namakaryawan";
		//exit('Warning: '.$sKary);
		$qKary=mysql_query($sKary) or die(mysql_error($conn));
		while($dKary=mysql_fetch_assoc($qKary)){
			//$optKary.="<option value=".$dKary['karyawanid'].">[".$dKary['nik'].'] '.$dKary['namakaryawan']."</option>";
			$optKary.="<option value=".$dKary['karyawanid'].">".$dKary['namakaryawan'].' - ['.$dKary['nik']."]</option>";
		}
		echo $optKary;
		exit;
	}
	$where="";
	if(!empty($kodeorg)){
		$where.=" and b.kodeorg='".$kodeorg."'";
	}else{
		exit('Warning: Unit tidak boleh kosong...!');
	}
	if(!empty($divisi)){
		$where.=" and left(a.kodeorg,6)='".$divisi."'";
	}
	if($periode==''){
		exit('Warning: Tanggal tidak boleh kosong...!');
	}else{
		$where.=" and b.tanggal like '".$periode."%'";
	}
	$tgl_akhir = date('t', strtotime($periode.'-01'));
	if($proses=='excel'){
		$border="border=1";
	}else{
		$border="border=0";
	}
	$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

	$stream="<table cellspacing='1' $border class='sortable'>";
	$stream.="<thead>
				<tr class=rowheader>
					<td rowspan=2 align=center>No</td>
					<td rowspan=2 align=center>Divisi</td>
					<td rowspan=2 align=center>NIK</td>
					<td rowspan=2 align=center width='100px;'>Nama Karyawan</td>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td colspan=4 align=center>".$periode.'-'.sprintf("%02d",$x)."</td>";
	}
	$stream.="		<td colspan=4 align=center>Total</td>
				</tr>";
	$stream.="	<tr class=rowheader>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td align=center>Luas Panen</td>
					<td align=center>Hasil Jjg</td>
					<td align=center>Hasil Kg</td>
					<td align=center>Brondol Kg</td>";
	}
	$stream.="		<td align=center>Luas Panen</td>
					<td align=center>Hasil Jjg</td>
					<td align=center>Hasil Kg</td>
					<td align=center>Brondol Kg</td>";
	$stream.="</tr></thead><tbody>";
	$iList="select b.kodeorg as unit,a.kodeorg,a.nik as karyawanid,b.tanggal,c.nik,c.namakaryawan,sum(a.luaspanen) as luaspanen
			,sum(a.hasilkerja) as hasilkerja,sum(a.hasilkerjakg) as hasilkerjakg,sum(a.brondolan) as brondolan
			from ".$dbname.".kebun_prestasi a
			LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
			where b.tipetransaksi='PNN' and b.jurnal='1' ".$where."
			GROUP BY b.kodeorg,a.nik,b.tanggal
			ORDER BY b.kodeorg,a.nik,b.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row =mysql_num_rows($nList);
	if($row==0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=4>Data Not Found...!</td>";
		$stream.="</tr>";
	}else{
		while($barz=mysql_fetch_object($nList)){
			$unit=$barz->unit;
			$kid=$barz->karyawanid;
			$tgl=$barz->tanggal;
			$kdunit[$unit]=$barz->unit;
			$karyawanid[$unit.$kid]=$barz->karyawanid;
			$nik[$unit.$kid]=$barz->nik;
			$namakaryawan[$unit.$kid]=$barz->namakaryawan;
			$luaspanen[$unit.$kid.$tgl]=$barz->luaspanen;
			$hasilkerja[$unit.$kid.$tgl]=$barz->hasilkerja;
			$hasilkerjakg[$unit.$kid.$tgl]=$barz->hasilkerjakg;
			$brondolan[$unit.$kid.$tgl]=$barz->brondolan;
		}
	}
	asort($karyawanid);
	asort($kdunit);
	$ttluaspanen=Array();
	$tthasilkerja=Array();
	$tthasilkerjakg=Array();
	$ttbrondolan=Array();
	$gtluaspanen=Array();
	$gthasilkerja=Array();
	$gthasilkerjakg=Array();
	$gtbrondolan=Array();
	$gtttluaspanen=0;
	$gttthasilkerja=0;
	$gttthasilkerjakg=0;
	$gtttbrondolan=0;
	$no=0;
	foreach($karyawanid as $karyid=>$kid){
		$no+=1;
		foreach($kdunit as $kd_unit=>$unit){
			$stream.="<tr class=rowcontent>";
			$stream.="	<td align=center>".$no."</td>";
			$stream.="	<td align=left>".$unit."</td>";
			$stream.="	<td align=left>".$nik[$unit.$kid]."</td>";
			$stream.="	<td align=left>".$namakaryawan[$unit.$kid]."</td>";
			for ($x=1; $x<=$tgl_akhir; $x++){
				$tgl=$periode.'-'.sprintf("%02d",$x);
				$stream.="<td align=right>".number_format($luaspanen[$unit.$kid.$tgl],2)."</td>";
				$stream.="<td align=right>".number_format($hasilkerja[$unit.$kid.$tgl],0)."</td>";
				$stream.="<td align=right>".number_format($hasilkerjakg[$unit.$kid.$tgl],2)."</td>";
				$stream.="<td align=right>".number_format($brondolan[$unit.$kid.$tgl],2)."</td>";
				$ttluaspanen[$unit.$kid]+=$luaspanen[$unit.$kid.$tgl];
				$tthasilkerja[$unit.$kid]+=$hasilkerja[$unit.$kid.$tgl];
				$tthasilkerjakg[$unit.$kid]+=$hasilkerjakg[$unit.$kid.$tgl];
				$ttbrondolan[$unit.$kid]+=$brondolan[$unit.$kid.$tgl];
				$gtluaspanen[$unit.$tgl]+=$luaspanen[$unit.$kid.$tgl];
				$gthasilkerja[$unit.$tgl]+=$hasilkerja[$unit.$kid.$tgl];
				$gthasilkerjakg[$unit.$tgl]+=$hasilkerjakg[$unit.$kid.$tgl];
				$gtbrondolan[$unit.$tgl]+=$brondolan[$unit.$kid.$tgl];
				$gtttluaspanen+=$luaspanen[$unit.$kid.$tgl];
				$gttthasilkerja+=$hasilkerja[$unit.$kid.$tgl];
				$gttthasilkerjakg+=$hasilkerjakg[$unit.$kid.$tgl];
				$gtttbrondolan+=$brondolan[$unit.$kid.$tgl];
			}
			$stream.="<td align=right>".number_format($ttluaspanen[$unit.$kid],2)."</td>";
			$stream.="<td align=right>".number_format($tthasilkerja[$unit.$kid],0)."</td>";
			$stream.="<td align=right>".number_format($tthasilkerjakg[$unit.$kid],2)."</td>";
			$stream.="<td align=right>".number_format($ttbrondolan[$unit.$kid],2)."</td>";
			$stream.="</tr>";
		}
	}
	$stream.="<tr bgcolor='#FEDEFE'>";
	$stream.="	<td colspan=4 align=center>TOTAL</td>";
	for ($x=1; $x<=$tgl_akhir; $x++){
		$tgl=$periode.'-'.sprintf("%02d",$x);
		$stream.="<td align=right>".number_format($gtluaspanen[$unit.$tgl],2)."</td>";
		$stream.="<td align=right>".number_format($gthasilkerja[$unit.$tgl],0)."</td>";
		$stream.="<td align=right>".number_format($gthasilkerjakg[$unit.$tgl],2)."</td>";
		$stream.="<td align=right>".number_format($gtbrondolan[$unit.$tgl],2)."</td>";
	}
	$stream.="<td align=right>".number_format($gtttluaspanen,2)."</td>";
	$stream.="<td align=right>".number_format($gttthasilkerja,0)."</td>";
	$stream.="<td align=right>".number_format($gttthasilkerjakg,2)."</td>";
	$stream.="<td align=right>".number_format($gtttbrondolan,2)."</td>";
	$stream.="</tr>";
	$stream.="</tbody></table>";
	switch($proses){
		case 'preview':
			echo $stream;
			break;

		case 'excel':
			$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
			$tglSkrg=date("Ymd");
			$judul="<h3>PENALTY PEMANEN";
			$judul.="<BR>".($divisi=='' ? $nmOrg[$kodeorg] : $nmOrg[$divisi])."";
			$judul.="<BR>Periode : ".tanggalnormal($tgl1).' s/d '.tanggalnormal($tgl2)."</h3>";
			$nop_="PENALTY_PEMANEN_".$divisi.'_'.$periode.'_'.$tglSkrg;
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
