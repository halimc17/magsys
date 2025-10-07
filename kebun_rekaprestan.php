<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	//Pilih Unit
	$optUnit="<option value=''></option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and detail='1' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' 
			and tipe='KEBUN' and detail='1' order by namaorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
			and tipe='KEBUN' and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Divisi
	$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1' order by kodeorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where
			induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN' and detail='1') and tipe='AFDELING' and detail='1' order by kodeorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' 
			and tipe='AFDELING' and detail='1' order by kodeorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		//$optDivisi.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Blok
	$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where 
			induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN' and detail='1')
			and tipe='AFDELING' and detail='1') and tipe='BLOK' and detail='1' order by namaorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' 
			and tipe='BLOK' and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		//$optBlok.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Periode
	$sPeriode="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error());
	$optPeriode="";
	while($rPeriode=mysql_fetch_assoc($qPeriode)){
		$no+=1;
		//if($no==1){
		//	$optPeriode.="<option value='".substr($rPeriode['periode'],0,4)."'>".substr($rPeriode['periode'],0,4)."</option>";
		//}elseif(substr($rPeriode['periode'],5,2)=='12'){
		//	$optPeriode.="<option value='".substr($rPeriode['periode'],0,4)."'>".substr($rPeriode['periode'],0,4)."</option>";
		//}
		$optPeriode.="<option value='".$rPeriode['periode']."'>".substr($rPeriode['periode'],5,2)."-".substr($rPeriode['periode'],0,4)."</option>";
	}

	$arr="##kodeunit##kodedivisi##kodeblok##periode";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function getDivisi(){
	    kodeunit=document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
		param='kodeunit='+kodeunit+'&proses=getDivisi';
	    tujuan='kebun_rekaprestan_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//document.getElementById('kodedivisi').innerHTML=con.responseText;
						ar=con.responseText.split("###");
						document.getElementById('kodedivisi').innerHTML=ar[0];
						document.getElementById('kodeblok').innerHTML=ar[1];
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getBlok(){
	    kodeunit=document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
	    kodedivisi=document.getElementById('kodedivisi').options[document.getElementById('kodedivisi').selectedIndex].value;
		param='kodeunit='+kodeunit+'&kodedivisi='+kodedivisi+'&proses=getBlok';
	    tujuan='kebun_rekaprestan_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('kodeblok').innerHTML=con.responseText;
						//ar=con.responseText.split("###");
						//document.getElementById('kodeblok').innerHTML=ar[0];
						//document.getElementById('kodeblok').innerHTML=ar[1];
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function cekcek(apa){
		if(apa.checked)apa.value="1"; else apa.value="0";
	}

	function Clear1(){
		document.getElementById('kodeunit').value='';
		document.getElementById('kodedivisi').value='';
		document.getElementById('kodeblok').value='';
		//document.getElementById('periode').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['rekap']." ".$_SESSION['lang']['lapRestan'];?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kodeunit" name="kodeunit" onchange=getDivisi()><?php echo $optUnit;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['divisi'];?></label></td>
				<td><select id="kodedivisi" name="kodedivisi" onchange=getBlok()><?php echo $optDivisi;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['blok'];?></label></td>
				<td><select id="kodeblok" name="kodeblok"><?php echo $optBlok;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['periode'];?></label></td>
				<td><select id="periode" name="periode" style="width:135px"><?php echo $optPeriode;?></select></td>
			</tr>
			<tr height="20"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('kebun_rekaprestan_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'kebun_rekaprestan_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
					<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'>
	<legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:310px;max-width:1310px'></div>
</fieldset>

<?php
	CLOSE_BOX();
	echo close_body();
?>
