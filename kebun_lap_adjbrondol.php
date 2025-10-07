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
			//document.getElementById('kodept0').value='';
			document.getElementById('kodeunit0').value='';
			document.getElementById('kodedivisi0').value='';
			document.getElementById('kodeblok0').value='';
			document.getElementById('jenis0').value='';
			document.getElementById('tanggal10').value='';
			document.getElementById('tanggal20').value='';
			document.getElementById('printContainer0').innerHTML='';
		}
		if(num==1){
			//document.getElementById('kodept1').value='';
			document.getElementById('kodeunit1').value='';
			document.getElementById('jenis1').value='';
			document.getElementById('tahun1').value='';
			document.getElementById('printContainer1').innerHTML='';
		}
	}

	function getUnit(num){
		if(num==0){
			kodept=document.getElementById('kodept0').options[document.getElementById('kodept0').selectedIndex].value;
		}else{
			kodept=document.getElementById('kodept1').options[document.getElementById('kodept1').selectedIndex].value;
		}
		param='kodept0='+kodept+'&proses=getUnit';
		tujuan='kebun_lap_adjbrondol_detail.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else {
						if(num==0){
							document.getElementById('kodeunit0').innerHTML=con.responseText;
							if(kodept!=''){
								getDivisi(0);
							}
						}else{
							document.getElementById('kodeunit1').innerHTML=con.responseText;
						}
					}
				}else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getDivisi(num){
	    kodept=document.getElementById('kodept0').options[document.getElementById('kodept0').selectedIndex].value;
	    kodeunit=document.getElementById('kodeunit0').options[document.getElementById('kodeunit0').selectedIndex].value;
		param='kodept0='+kodept+'&kodeunit0='+kodeunit+'&proses=getDivisi';
		tujuan='kebun_lap_adjbrondol_detail.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						ar=con.responseText.split("###");
						document.getElementById('kodedivisi0').innerHTML=ar[0];
						document.getElementById('kodeblok0').innerHTML=ar[1];
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getBlok(num){
	    kodept=document.getElementById('kodept0').options[document.getElementById('kodept0').selectedIndex].value;
	    kodeunit=document.getElementById('kodeunit0').options[document.getElementById('kodeunit0').selectedIndex].value;
	    kodedivisi=document.getElementById('kodedivisi0').options[document.getElementById('kodedivisi0').selectedIndex].value;
		param='kodept0='+kodept+'&kodeunit0='+kodeunit+'&kodedivisi0='+kodedivisi+'&proses=getBlok';
	    tujuan='kebun_lap_adjbrondol_detail.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						if(kodeunit==''){
							document.getElementById('kodeunit0').value=kodedivisi.substring(0, 4);
						}
						document.getElementById('kodeblok0').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getSub(num){
	    kodept=document.getElementById('kodept0').options[document.getElementById('kodept0').selectedIndex].value;
	    kodeunit=document.getElementById('kodeunit0').options[document.getElementById('kodeunit0').selectedIndex].value;
	    kodedivisi=document.getElementById('kodedivisi0').options[document.getElementById('kodedivisi0').selectedIndex].value;
	    kodeblok=document.getElementById('kodeblok0').options[document.getElementById('kodeblok0').selectedIndex].value;
		param='kodept0='+kodept+'&kodeunit0='+kodeunit+'&kodedivisi0='+kodedivisi+'&kodeblok0='+kodeblok+'&proses=getSub';
	    tujuan='kebun_lap_adjbrondol_detail.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						if(kodedivisi==''){
							document.getElementById('kodedivisi0').value=kodeblok.substring(0, 6);
						}
						if(kodeunit==''){
							document.getElementById('kodeunit0').value=kodeblok.substring(0, 4);
						}
						if(kodept==''){
							document.getElementById('kodeunit0').value=con.responseText;
						}
					}
				}else{
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

	//Pilih PT
	$optPT="";
	//$optPT="<option value=''></option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PT' and kodeorganisasi in (select DISTINCT induk from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E') order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	$no=0;
	$dspPT="";
	while($d=mysql_fetch_assoc($n)){
		$no+=1;
		if($no==1){
			$dspPT=$d['kodeorganisasi'];
		}
		$optPT.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	##untuk pilihan Kebun
	$whrPT="";
	if(!empty($dspPT)){
		$whrPT=" and induk='".$dspPT."'";
	}
	$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
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
	$sKebun=$sKebun.$whrPT;
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
	$whrKebun="";
	if(!empty($dspPT)){
		$whrKebun=" and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$dspPT."')";
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

	$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
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

	//Pilih Tahun
	$optTahun="";
	$sYear="select distinct left(periode,4) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qYear=mysql_query($sYear) or die (mysql_error($conn));
	while($dYear=mysql_fetch_assoc($qYear)){
		$optTahun.="<option value='".$dYear['tahun']."'>".$dYear['tahun']."</option>";
	}

	$optJenis.="<option value=''></option>";
	$optJenis.="<option value='Afkir'>Afkir</option>";
	$optJenis.="<option value='Hilang'>Hilang</option>";

	OPEN_BOX();
	$arr0="##kodept0##kodeunit0##kodedivisi0##kodeblok0##jenis0##tanggal10##tanggal20";
	$arr1="##kodept1##kodeunit1##jenis1##tahun1";

	###form input 1
	################
	$frm[0].="<fieldset style='float:left;'><legend><b>Lap Adj Brondolan Detail</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>:</td>
					<td><select id=kodept0 name=kodept0 onchange=getUnit(0) style=\"width:240px;\" >".$optPT."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kodeunit0 name=kodeunit0 onchange=getDivisi(0) style=\"width:240px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=kodedivisi0 name=kodedivisi0 onchange=getBlok(0) style=\"width:240px;\" >".$optDivisi."</select></td>
				</tr>
				<tr>
					<td><label>".$_SESSION['lang']['blok']."</label></td>
					<td>:</td>
					<td><select id=kodeblok0 name=kodeblok0 onchange=getSub(0) style=\"width:240px;\" >".$optBlok."</select></td>
				</tr>
				<tr>
					<td><label>".$_SESSION['lang']['jenis']."</label></td>
					<td>:</td>
					<td>
						<input type='text' style='width:135px' id='jenis0' name='jenis0' list='listjenis' class='myinputtext' />
						<datalist id='listjenis'>".$optJenis."</datalist>
					</td>
				</tr>
				<tr> 
					<td>".$_SESSION['lang']['tanggal']."&nbsp</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tanggal10' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\"> 
						sd 
						<input type='text' class='myinputtext' id='tanggal20' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\">
					</td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_lap_adjbrondol_detail','".$arr0."','printContainer0') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_lap_adjbrondol_detail.php','".$arr0."') class=mybutton name=excel0 id=excel0>".$_SESSION['lang']['excel']."</button>
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
	$frm[1].="<fieldset style='float:left;'><legend><b>Lap Adj Brondolan Rekap</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>:</td>
					<td><select id=kodept1 name=kodept1 onchange=getUnit(1) style=\"width:240px;\" >".$optPT."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kodeunit1 name=kodeunit1 style=\"width:240px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>
					<td><select id=jenis1 style=\"width:240px;\" >".$optJenis."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=tahun1 style=\"width:70px;\" >".$optTahun."</select></td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_lap_adjbrondol_rekap','".$arr1."','printContainer1') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_lap_adjbrondol_rekap.php','".$arr1."') class=mybutton name=excel1 id=excel1>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(1) class=mybutton name=btnBatal1 id=btnBatal1>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	$hfrm[0]=$_SESSION['lang']['laporan'].' Adjustment Brondolan Detail';
	$hfrm[1]=$_SESSION['lang']['laporan'].' Adjustment Brondolan Rekap';
	drawTab('FRM',$hfrm,$frm,300,1155);	
	CLOSE_BOX();
	echo close_body();
?>
