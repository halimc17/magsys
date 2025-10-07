<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');

	//Pilih PT
	$optPT="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PT' and kodeorganisasi in (select DISTINCT induk from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E') order by namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by	namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodeorganisasi='".$_SESSION['empl']['induk']."' 
			and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
			and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optPT.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Unit
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E' and LENGTH(kodeorganisasi)=4 and tipe='KEBUN' and detail='1' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E' and LENGTH(kodeorganisasi)=4 and tipe='KEBUN' and detail='1' and induk='".$_SESSION['empl']['induk']."' order by namaorganisasi";
	}else{
		$optUnit="";
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
			and tipe='KEBUN' and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Karyawan
	//$qKary=mysql_query($sKary) or die(mysql_error($conn));
	//$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	//while($rKary=mysql_fetch_assoc($qKary)){
	//	$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." [".$rKary['nik']."]</option>";
	//}

	//Pilih Tahun
	$optTahun="";
	$sYear="select distinct left(periode,4) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qYear=mysql_query($sYear) or die (mysql_error($conn));
	while($dYear=mysql_fetch_assoc($qYear)){
		$optTahun.="<option value='".$dYear['tahun']."'>".$dYear['tahun']."</option>";
	}

	$arr="##kodept##kodeunit##tahun";
	$_POST['judul']==''?$judul=$_GET['judul']:$judul=$_POST['judul'];

	echo"
	<fieldset style=\"float: left;\">
		<legend><b>".$_POST['judul']."</b></legend>
		<table cellspacing=\"1\" border=\"0\" >
			<tr>
				<td><label>".$_SESSION['lang']['pt']."</label></td>
				<td><select id=\"kodept\" name=\"kodept\" style=\"width:200px;\" onchange=getUnit(this)>".$optPT."</select></td>
			</tr>
			<tr>
				<td><label>".$_SESSION['lang']['unit']."</label></td>
				<td><select id=\"kodeunit\" name=\"kodeunit\" style=\"width:200px\">".$optUnit."</select></td>
			</tr>
			<tr>
				<td><label>".$_SESSION['lang']['tahun']."</label></td>
				<td><select id=\"tahun\" name=\"tahun\" style=\"width:60px\">".$optTahun."</select></td>
			</tr>
			<tr height=\"8\">
				<td colspan=\"2\"><input type=hidden id=judul name=judul value='".$judul."'></td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<button onclick=\"zPreview('lbm_rkp_cekancak_slave','".$arr."','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
					<button onclick=\"zExcel(event,'lbm_rkp_cekancak_slave.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
					<button onclick=\"Clear1()\" class=\"mybutton\" id=\"btnBatal\" name=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>    
				</td>
			</tr>
		</table>
	</fieldset>";
?>
