<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script>
	function batalLaporan(num){
		if(num==0){
			document.getElementById('divisi0').value='';
			document.getElementById('tgl10').value='';
			document.getElementById('tgl20').value='';
			document.getElementById('pemanen0').value='';
			document.getElementById('printContainer0').innerHTML='';
		}
		if(num==1){
			document.getElementById('divisi1').value='';
			document.getElementById('pemanen1').value='';
			document.getElementById('printContainer1').innerHTML='';
		}
	}

	function getSub(num){
		if(num==0){
		    kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;
		}else{
		    kebun0=document.getElementById('kebun1').options[document.getElementById('kebun1').selectedIndex].value;
		}
		param='kebun0='+kebun0+'&proses=getSubUnit';
		tujuan='kebun_lap_brondol_per_orang.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else {
						//alert(con.responseText);
						if(num==0){
							document.getElementById('divisi0').innerHTML=con.responseText;
						}else{
							document.getElementById('divisi1').innerHTML=con.responseText;
						}
						getKary(num);
					}
				}else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getKary(num){
		if(num==0){
			kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;
		    divisi0=document.getElementById('divisi0').options[document.getElementById('divisi0').selectedIndex].value;
		}else{
			kebun0=document.getElementById('kebun1').options[document.getElementById('kebun1').selectedIndex].value;
		    divisi0=document.getElementById('divisi1').options[document.getElementById('divisi1').selectedIndex].value;
		}
		param='kebun0='+kebun0+'&divisi0='+divisi0+'&proses=getKary';
		tujuan='kebun_lap_brondol_per_orang.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else {
						//alert(con.responseText);
						if(num==0){
							document.getElementById('pemanen0').innerHTML=con.responseText;
						}else{
							document.getElementById('pemanen1').innerHTML=con.responseText;
						}
					}
				}else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}
</script>
<?
	include('master_mainMenu.php');
	$frm[0]='';
	$frm[1]='';

	##untuk pilihan Kebun
	$optKebun="";
	//$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and detail='1'";
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='KEBUN' and kodeorganisasi like '%E'";
	}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='KEBUN' and kodeorganisasi like '%E' 
				and induk='".$_SESSION['empl']['induk']."'";
	}else{
		$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='KEBUN' and kodeorganisasi like '%E' 
				and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	}
	//exit('Warning: '.$sKebun);
	$qKebun=mysql_query($sKebun) or die(mysql_error($conn));
	$no=0;
	$dspKebun="";
	while($dKebun=mysql_fetch_assoc($qKebun)){
		$no+=1;
		if($no==1){
			$dspKebun=$dKebun['kodeorganisasi'];
		}
		$optKebun.="<option value=".$dKebun['kodeorganisasi'].">".$dKebun['namaorganisasi']."</option>";
	}

	##untuk pilihan Divisi
	$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
	//$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1'";
	if(!empty($dspKebun)){
		$whrKebun=" and induk='".$dspKebun."'";
	}
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='AFDELING' and left(kodeorganisasi,4) like '%E'"
				.$whrKebun;
	}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='AFDELING' and left(kodeorganisasi,4) like '%E' 
				and left(kodeorganisasi,4) in (SELECT kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')"
				.$whrKebun;
	}else{
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='AFDELING' and left(kodeorganisasi,4) like '%E' 
				and induk='".$_SESSION['empl']['lokasitugas']."'"
				.$whrKebun;
	}
	//exit('Warning: '.$sDivisi);
	$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
	while($dDivisi=mysql_fetch_assoc($qDivisi)){
		$optDivisi.="<option value=".$dDivisi['kodeorganisasi'].">".$dDivisi['namaorganisasi']."</option>";
	}

	##untuk pilihan Periode
	$optPeriode="";
	$sPeriode="select distinct(left(periode,7)) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
	$no=0;
	while($dPeriode=mysql_fetch_assoc($qPeriode)){
		$no+=1;
		if($no==1){
			//$optPeriode.="<option value='".substr($dPeriode['periode'],0,4)."'>".substr($dPeriode['periode'],0,4)."</option>";
		}else if(substr($dPeriode['periode'],5,2)=='12'){
			//$optPeriode.="<option value='".substr($dPeriode['periode'],0,4)."'>".substr($dPeriode['periode'],0,4)."</option>";
		}
		$optPeriode.="<option value=".$dPeriode['periode'].">".$dPeriode['periode']."</option>";
	}

	##untuk pilihan Pemanen
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	if(!empty($dspKebun)){
		$whrKebun=" and lokasitugas='".$dspKebun."'";
	}
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where left(subbagian,4) like '%E'"
				.$whrKebun
				." order by namakaryawan";
	}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where left(subbagian,4) like '%E'
				and left(lokasitugas,4) in (SELECT kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')"
				.$whrKebun
				." order by namakaryawan";
	}else{
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where left(subbagian,4) like '%E'
				and lokasitugas='".$_SESSION['empl']['lokasitugas']."'"
				.$whrKebun
				." order by namakaryawan";
	}
	//exit('Warning: '.$sKary);
	$qKary=mysql_query($sKary) or die(mysql_error($conn));
	while($dKary=mysql_fetch_assoc($qKary)){
		//$optKary.="<option value=".$dKary['karyawanid'].">[".$dKary['nik'].'] '.$dKary['namakaryawan']."</option>";
		$optKary.="<option value=".$dKary['karyawanid'].">".$dKary['namakaryawan'].' - ['.$dKary['nik']."]</option>";
	}

	OPEN_BOX();
	$arr0="##kebun0##divisi0##periode0";
	$arr1="##kebun1##divisi1##periode1";

	###form input 1
	################
	$frm[0].="<fieldset style='float:left;'><legend><b>Form Brondol per Tanggal</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun0 onchange=getSub(0) style=\"width:240px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi0 onchange=getKary(0) style=\"width:240px;\" >".$optDivisi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode0 style=\"width:70px;\" >".$optPeriode."</select></td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_lap_brondol_per_orang','".$arr0."','printContainer0') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_lap_brondol_per_orang.php','".$arr0."') class=mybutton name=excel0 id=excel0>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(0) class=mybutton name=btnBatal0 id=btnBatal0>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer0' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 2
	################
	$frm[1].="<fieldset style='float:left;'><legend><b>Form Brondol Restan</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun1 onchange=getSub(1) style=\"width:240px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi1 onchange=getKary(1) style=\"width:240px;\" >".$optDivisi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode1 style=\"width:70px;\" >".$optPeriode."</select></td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_lap_brondol_per_blok','".$arr1."','printContainer1') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_lap_brondol_per_blok.php','".$arr1."') class=mybutton name=excel1 id=excel1>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(1) class=mybutton name=btnBatal1 id=btnBatal1>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	$hfrm[0]=$_SESSION['lang']['laporan'].' Brondol Pemanen Per Bulan';
	$hfrm[1]=$_SESSION['lang']['laporan'].' Brondol Restan Per Blok';
	drawTab('FRM',$hfrm,$frm,300,1225);	
	CLOSE_BOX();
	echo close_body();
?>
