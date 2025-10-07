<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses = checkPostGet('proses','');
	$kodeorg= checkPostGet('kebun1','');
	$divisi	= checkPostGet('divisi1','');
	$periode= checkPostGet('periode1','');

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
	$where2="";
	if(!empty($kodeorg)){
		$where.=" and b.kodeorg='".$kodeorg."'";
		$where2.=" and b.kodeorg='".$kodeorg."'";
	}else{
		exit('Warning: Unit tidak boleh kosong...!');
	}
	if(!empty($divisi)){
		$where.=" and left(a.kodeorg,6)='".$divisi."'";
		$where2.=" and left(a.blok,6)='".$divisi."'";
	}
	if($periode==''){
		exit('Warning: Tanggal tidak boleh kosong...!');
	}else{
		$where.=" and b.tanggal like '".$periode."%'";
		$where2.=" and b.tanggal like '".$periode."%'";
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
					<td rowspan=2 align=center width='100px;'>Nama Blok</td>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td colspan=3 align=center>".$periode.'-'.sprintf("%02d",$x)."</td>";
	}
	$stream.="		<td colspan=3 align=center>Total</td>
				</tr>";
	$stream.="	<tr class=rowheader>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td align=center>Brondol Kebun</td>
					<td align=center>Brondol Kirim</td>
					<td align=center>Brondol Restan</td>";
	}
	$stream.="		<td align=center>Brondol Kebun</td>
					<td align=center>Brondol Kirim</td>
					<td align=center>Brondol Restan</td>";
	$stream.="</tr></thead><tbody>";
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$where.=" and b.jurnal='1' ";
	}
	$iList="select b.kodeorg as unit,a.kodeorg,b.tanggal,c.namaorganisasi,sum(a.luaspanen) as luaspanen
			,sum(a.hasilkerja) as hasilkerja,sum(a.hasilkerjakg) as hasilkerjakg,sum(a.brondolan) as brondolan
			from ".$dbname.".kebun_prestasi a
			LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where b.tipetransaksi='PNN' ".$where."
			GROUP BY b.kodeorg,a.kodeorg,b.tanggal
			ORDER BY b.kodeorg,a.kodeorg,b.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row =mysql_num_rows($nList);
	while($barz=mysql_fetch_object($nList)){
		$unit=$barz->unit;
		$blok=$barz->kodeorg;
		$tgl=$barz->tanggal;
		$kdunit[$unit]=$barz->unit;
		$kodeblok[$unit.$blok]=$barz->kodeorg;
		$namablok[$unit.$blok]=$barz->namaorganisasi;
		$luaspanen[$unit.$blok.$tgl]=$barz->luaspanen;
		$hasilkerja[$unit.$blok.$tgl]=$barz->hasilkerja;
		$hasilkerjakg[$unit.$blok.$tgl]=$barz->hasilkerjakg;
		$brondolan[$unit.$blok.$tgl]=$barz->brondolan;
	}
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$where2.=" and b.posting='1' ";
	}
	$iList="select b.kodeorg as unit,a.blok,c.namaorganisasi,b.tanggal,sum(a.jjg) as jjgkirim,sum(a.kgwb) as kgkirim,sum(a.brondolan) as brondolkirim
			from ".$dbname.".kebun_spbdt a
			LEFT JOIN ".$dbname.".kebun_spbht b on b.nospb=a.nospb
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.blok
			where true ".$where2."
			GROUP BY b.kodeorg,a.blok,b.tanggal
			ORDER BY b.kodeorg,a.blok,b.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$nrow =mysql_num_rows($nList);
	if($nrow==0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=4>Data Not Found...!</td>";
		$stream.="</tr>";
	}else{
		while($barz=mysql_fetch_object($nList)){
			$unit=$barz->unit;
			$blok=$barz->blok;
			$tgl=$barz->tanggal;
			$kdunit[$unit]=$barz->unit;
			$kodeblok[$unit.$blok]=$barz->blok;
			$namablok[$unit.$blok]=$barz->namaorganisasi;
			$jjgkirim[$unit.$blok.$tgl]=$barz->jjgkirim;
			$kgkirim[$unit.$blok.$tgl]=$barz->kgkirim;
			$brondolkirim[$unit.$blok.$tgl]=$barz->brondolkirim;
		}
		asort($kodeblok);
		asort($kdunit);
		$ttbrondolan=Array();
		$ttbrondolkirim=Array();
		$ttbrondolrestan=Array();
		$gtbrondolan=Array();
		$gtbrondolkirim=Array();
		$gtbrondolrestan=Array();
		$gtttbrondolan=0;
		$gtttbrondolkirim=0;
		$gtttbrondolrestan=0;
		$no=0;
		foreach($kodeblok as $kdblok=>$blok){
			$no+=1;
			$restanlalu=0;
			foreach($kdunit as $kd_unit=>$unit){
				$stream.="<tr class=rowcontent>";
				$stream.="	<td align=center>".$no."</td>";
				$stream.="	<td align=left>".$unit."</td>";
				$stream.="	<td align=left>".$namablok[$unit.$blok]."</td>";
				for ($x=1; $x<=$tgl_akhir; $x++){
					$tgl=$periode.'-'.sprintf("%02d",$x);
					$brondolrestan[$unit.$blok.$tgl]=$restanlalu+$brondolan[$unit.$blok.$tgl]-$brondolkirim[$unit.$blok.$tgl];
					$stream.="<td align=right>".number_format($brondolan[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolkirim[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolrestan[$unit.$blok.$tgl],0)."</td>";
					$restanlalu=($brondolrestan[$unit.$blok.$tgl]<0 ? 0 : $brondolrestan[$unit.$blok.$tgl]);
					$ttbrondolan[$unit.$blok]+=$brondolan[$unit.$blok.$tgl];
					$ttbrondolkirim[$unit.$blok]+=$brondolkirim[$unit.$blok.$tgl];
					$ttbrondolrestan[$unit.$blok]+=$brondolrestan[$unit.$blok.$tgl];
					$gtbrondolan[$unit.$tgl]+=$brondolan[$unit.$blok.$tgl];
					$gtbrondolkirim[$unit.$tgl]+=$brondolkirim[$unit.$blok.$tgl];
					$gtbrondolrestan[$unit.$tgl]+=$brondolrestan[$unit.$blok.$tgl];
					$gtttbrondolan+=$brondolan[$unit.$blok.$tgl];
					$gtttbrondolkirim+=$brondolkirim[$unit.$blok.$tgl];
				}
				$stream.="<td align=right>".number_format($ttbrondolan[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolkirim[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($restanlalu,0)."</td>";
				$stream.="</tr>";
				$gtttbrondolrestan+=$restanlalu;
			}
		}
		$stream.="<tr bgcolor='#FEDEFE'>";
		$stream.="	<td colspan=3 align=center>TOTAL</td>";
		for ($x=1; $x<=$tgl_akhir; $x++){
			$tgl=$periode.'-'.sprintf("%02d",$x);
			$stream.="<td align=right>".number_format($gtbrondolan[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolkirim[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolrestan[$unit.$tgl],0)."</td>";
		}
		$stream.="<td align=right>".number_format($gtttbrondolan,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolkirim,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolrestan,0)."</td>";
		$stream.="</tr>";
	}
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
