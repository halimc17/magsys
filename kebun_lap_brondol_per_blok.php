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
	$where3="";
	$where4="";
	$where5="";
	if(!empty($kodeorg)){
		$where.=" and b.kodeorg='".$kodeorg."'";
		$where2.=" and b.kodeorg='".$kodeorg."'";
		$where3.=" and left(a.kodeblok,4)='".$kodeorg."'";
		$where4.=" and left(a.kodeorg,4)='".$kodeorg."'";
		$where5.=" and left(a.kodeorg,4)='".$kodeorg."'";
	}else{
		exit('Warning: Unit tidak boleh kosong...!');
	}
	if(!empty($divisi)){
		$where.=" and left(a.kodeorg,6)='".$divisi."'";
		$where2.=" and left(a.blok,6)='".$divisi."'";
		$where3.=" and left(a.kodeblok,6)='".$divisi."'";
		$where4.=" and left(a.kodeorg,6)='".$divisi."'";
		$where5.=" and left(a.kodeorg,6)='".$divisi."'";
	}
	if($periode==''){
		exit('Warning: Tanggal tidak boleh kosong...!');
	}else{
		$where.=" and b.tanggal like '".$periode."%'";
		$where2.=" and b.tanggal like '".$periode."%'";
		$where3.=" and a.tanggal like '".$periode."%'";
		if(substr($periode,5,2)=='01'){
			$pertahun=substr($periode,0,4)-1;
		}else{
			$pertahun=substr($periode,0,4);
		}
		$perlalu=$pertahun.'-'.(substr($periode,5,2)=='01' ? '12' : sprintf("%02d",substr($periode,5,2)-1));
		$tgllalu=date('Y-m-t',strtotime($perlalu.'-01'));
		$where4.=" and a.tanggal='".$tgllalu."'";
		$where5.=" and a.tanggal like '".$periode."%'";
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
					<td rowspan=2 align=center width='100px;'>Nama Blok</td>
					<td rowspan=2 align=center>Saldo Awal</td>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td colspan=7 align=center>".$periode.'-'.sprintf("%02d",$x)."</td>";
	}
	$stream.="		<td colspan=7 align=center>Total</td>
				</tr>";
	$stream.="	<tr class=rowheader>";
	for ($x=1; $x<=$tgl_akhir; $x++) {
		$stream.="	<td align=center>Brondol Panen</td>
					<td align=center>Brondol Rawat</td>
					<td align=center>Brondol Borong</td>
					<td align=center>Brondol Kirim</td>
					<td align=center>Brondol Afkir</td>
					<td align=center>Brondol Hilang</td>
					<td align=center>Brondol Restan</td>";
	}
	$stream.="		<td align=center>Brondol Panen</td>
					<td align=center>Brondol Rawat</td>
					<td align=center>Brondol Borong</td>
					<td align=center>Brondol Kirim</td>
					<td align=center>Brondol Afkir</td>
					<td align=center>Brondol Hilang</td>
					<td align=center>Brondol Restan</td>";
	$stream.="</tr></thead><tbody>";
	//========== Sisa Brondolan ================
	//if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
	//	$where.=" and b.jurnal='1' ";
	//}
	$iList="select left(a.kodeorg,4) as unit,a.kodeorg,a.tanggal,c.namaorganisasi,a.kgsisa from ".$dbname.".kebun_sisabrondol a
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where True ".$where4."
			ORDER BY a.kodeorg,a.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row0=mysql_num_rows($nList);
	while($barz=mysql_fetch_object($nList)){
		$unit=$barz->unit;
		$blok=$barz->kodeorg;
		$tgl=$barz->tanggal;
		$kdunit[$unit]=$barz->unit;
		$kodeblok[$unit.$blok]=$barz->kodeorg;
		$namablok[$unit.$blok]=$barz->namaorganisasi;
		$kgsisa[$unit.$blok]=$barz->kgsisa;
	}
	//========== Brondolan Panen ================
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
	$row1=mysql_num_rows($nList);
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
	//========== Brondolan Rawat ================
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$where.=" and b.jurnal='1' ";
	}
	$iList="SELECT b.kodeorg as unit,a.kodeorg,b.tanggal,c.namaorganisasi,sum(a.hasilkerja) as brondolrawat from ".$dbname.".kebun_prestasi a
			LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where a.kodekegiatan = '611010201' ".$where."
			GROUP BY b.kodeorg,a.kodeorg,b.tanggal
			ORDER BY b.kodeorg,a.kodeorg,b.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row2=mysql_num_rows($nList);
	while($barz=mysql_fetch_object($nList)){
		$unit=$barz->unit;
		$blok=$barz->kodeorg;
		$tgl=$barz->tanggal;
		$kdunit[$unit]=$barz->unit;
		$kodeblok[$unit.$blok]=$barz->kodeorg;
		$namablok[$unit.$blok]=$barz->namaorganisasi;
		$brondolrawat[$unit.$blok.$tgl]=$barz->brondolrawat;
	}
	//========== Brondolan Borong ================
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$where3.=" and a.statusjurnal='1' ";
	}
	$iList="SELECT left(a.kodeblok,4) as unit,a.kodeblok,a.tanggal,c.namaorganisasi,sum(a.hasilkerjarealisasi) as brondolborong 
			from ".$dbname.".log_baspk a
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeblok
			where a.kodekegiatan = '611010201' ".$where3."
			GROUP BY a.kodeblok,a.tanggal
			ORDER BY a.kodeblok,a.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row3=mysql_num_rows($nList);
	while($barz=mysql_fetch_object($nList)){
		$unit=$barz->unit;
		$blok=$barz->kodeblok;
		$tgl=$barz->tanggal;
		$kdunit[$unit]=$barz->unit;
		$kodeblok[$unit.$blok]=$barz->kodeblok;
		$namablok[$unit.$blok]=$barz->namaorganisasi;
		$brondolborong[$unit.$blok.$tgl]=$barz->brondolborong;
	}
	//========== Brondolan Afkir/Hilang ================
	//if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
	//	$where.=" and b.jurnal='1' ";
	//}
	$iList="select left(a.kodeorg,4) as unit,a.kodeorg,a.tanggal,c.namaorganisasi,sum(if(a.jenis='Afkir',a.kg,0)) as kgafkir
			,sum(if(a.jenis='Hilang',a.kg,0)) as kghilang 
			from ".$dbname.".kebun_adjbrondol a
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where True ".$where5."
			GROUP BY a.kodeorg,a.tanggal
			ORDER BY a.kodeorg,a.tanggal
			";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row4=mysql_num_rows($nList);
	while($barz=mysql_fetch_object($nList)){
		$unit=$barz->unit;
		$blok=$barz->kodeorg;
		$tgl=$barz->tanggal;
		$kdunit[$unit]=$barz->unit;
		$kodeblok[$unit.$blok]=$barz->kodeorg;
		$namablok[$unit.$blok]=$barz->namaorganisasi;
		$brondolafkir[$unit.$blok.$tgl]=$barz->kgafkir;
		$brondolhilang[$unit.$blok.$tgl]=$barz->kghilang;
	}
	//========== Brondolan Kirim ================
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
	if(($row0+$row1+$row2+$row3+$row4+$nrow)==0){
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
		$ttbrondolrawat=Array();
		$ttbrondolborong=Array();
		$ttbrondolkirim=Array();
		$ttbrondolafkir=Array();
		$ttbrondolhilang=Array();
		$ttbrondolrestan=Array();

		$gtbrondolan=Array();
		$gtbrondolrawat=Array();
		$gtbrondolborong=Array();
		$gtbrondolkirim=Array();
		$gtbrondolafkir=Array();
		$gtbrondolhilang=Array();
		$gtbrondolrestan=Array();
		$gtawal=0;
		$gtttbrondolan=0;
		$gtttbrondolrawat=0;
		$gtttbrondolborong=0;
		$gtttbrondolkirim=0;
		$gtttbrondolafkir=0;
		$gtttbrondolhilang=0;
		$gtttbrondolrestan=0;
		$no=0;
		foreach($kodeblok as $kdblok=>$blok){
			$no+=1;
			$restanlalu=0;
			foreach($kdunit as $kd_unit=>$unit){
				$stream.="<tr class=rowcontent>";
				$stream.="	<td align=center>".$no."</td>";
				//$stream.="	<td align=left>".$unit."</td>";
				$stream.="	<td align=left>".substr($blok,0,6)."</td>";
				$stream.="	<td align=left>".$namablok[$unit.$blok]."</td>";
				$stream.="  <td align=right>".number_format($kgsisa[$unit.$blok],0)."</td>";
				$restanlalu=$kgsisa[$unit.$blok];
				$gtawal+=$kgsisa[$unit.$blok];
				for ($x=1; $x<=$tgl_akhir; $x++){
					$tgl=$periode.'-'.sprintf("%02d",$x);
					$brondolrestan[$unit.$blok.$tgl]=$restanlalu+$brondolan[$unit.$blok.$tgl]+$brondolrawat[$unit.$blok.$tgl]+$brondolborong[$unit.$blok.$tgl]-$brondolkirim[$unit.$blok.$tgl]-$brondolafkir[$unit.$blok.$tgl]-$brondolhilang[$unit.$blok.$tgl];
					$stream.="<td align=right>".number_format($brondolan[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolrawat[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolborong[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolkirim[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolafkir[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolhilang[$unit.$blok.$tgl],0)."</td>";
					$stream.="<td align=right>".number_format($brondolrestan[$unit.$blok.$tgl],0)."</td>";
					//$restanlalu=($brondolrestan[$unit.$blok.$tgl]<0 ? 0 : $brondolrestan[$unit.$blok.$tgl]);
					$restanlalu=$brondolrestan[$unit.$blok.$tgl];
					$ttbrondolan[$unit.$blok]+=$brondolan[$unit.$blok.$tgl];
					$ttbrondolrawat[$unit.$blok]+=$brondolrawat[$unit.$blok.$tgl];
					$ttbrondolborong[$unit.$blok]+=$brondolborong[$unit.$blok.$tgl];
					$ttbrondolkirim[$unit.$blok]+=$brondolkirim[$unit.$blok.$tgl];
					$ttbrondolafkir[$unit.$blok]+=$brondolafkir[$unit.$blok.$tgl];
					$ttbrondolhilang[$unit.$blok]+=$brondolhilang[$unit.$blok.$tgl];
					$ttbrondolrestan[$unit.$blok]+=$brondolrestan[$unit.$blok.$tgl];
					$gtbrondolan[$unit.$tgl]+=$brondolan[$unit.$blok.$tgl];
					$gtbrondolrawat[$unit.$tgl]+=$brondolrawat[$unit.$blok.$tgl];
					$gtbrondolborong[$unit.$tgl]+=$brondolborong[$unit.$blok.$tgl];
					$gtbrondolkirim[$unit.$tgl]+=$brondolkirim[$unit.$blok.$tgl];
					$gtbrondolafkir[$unit.$tgl]+=$brondolafkir[$unit.$blok.$tgl];
					$gtbrondolhilang[$unit.$tgl]+=$brondolhilang[$unit.$blok.$tgl];
					$gtbrondolrestan[$unit.$tgl]+=$brondolrestan[$unit.$blok.$tgl];
					$gtttbrondolan+=$brondolan[$unit.$blok.$tgl];
					$gtttbrondolrawat+=$brondolrawat[$unit.$blok.$tgl];
					$gtttbrondolborong+=$brondolborong[$unit.$blok.$tgl];
					$gtttbrondolkirim+=$brondolkirim[$unit.$blok.$tgl];
					$gtttbrondolafkir+=$brondolafkir[$unit.$blok.$tgl];
					$gtttbrondolhilang+=$brondolhilang[$unit.$blok.$tgl];
				}
				$stream.="<td align=right>".number_format($ttbrondolan[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolrawat[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolborong[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolkirim[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolafkir[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($ttbrondolhilang[$unit.$blok],0)."</td>";
				$stream.="<td align=right>".number_format($restanlalu,0)."</td>";
				$stream.="</tr>";
				$gtttbrondolrestan+=$restanlalu;
			}
		}
		$stream.="<tr bgcolor='#FEDEFE'>";
		$stream.="	<td colspan=3 align=center>TOTAL</td>";
		$stream.="  <td align=right>".number_format($gtawal,0)."</td>";
		for ($x=1; $x<=$tgl_akhir; $x++){
			$tgl=$periode.'-'.sprintf("%02d",$x);
			$stream.="<td align=right>".number_format($gtbrondolan[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolrawat[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolborong[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolkirim[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolafkir[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolhilang[$unit.$tgl],0)."</td>";
			$stream.="<td align=right>".number_format($gtbrondolrestan[$unit.$tgl],0)."</td>";
		}
		$stream.="<td align=right>".number_format($gtttbrondolan,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolrawat,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolborong,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolkirim,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolafkir,0)."</td>";
		$stream.="<td align=right>".number_format($gtttbrondolhilang,0)."</td>";
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
