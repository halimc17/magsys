<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	//Pilih PT
	$optPT="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PT' and kodeorganisasi in (select DISTINCT induk from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E') order by namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodejabatan in (4,6,10,11,283,330,331,332,333) order by namakaryawan";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by	namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodeorganisasi='".$_SESSION['empl']['induk']."' 
			and kodejabatan in (4,6,10,11,283,330,331,332,333) order by namakaryawan";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
		$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
			and kodejabatan in (4,6,10,11,283,330,331,332,333) order by namakaryawan";
	}
	//exit('Warning: '.$i);
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
	$qKary=mysql_query($sKary) or die(mysql_error($conn));
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	while($rKary=mysql_fetch_assoc($qKary)){
		$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." [".$rKary['nik']."]</option>";
	}

	$arr="##kodept##kodeunit##diperiksa##tanggal1##tanggal2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function getUnit(){
	    kodept=document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
		param='kodept='+kodept+'&proses=getUnit';
	    tujuan='kebun_lap_cekancak_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('kodeunit').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function Clear1(){
		//document.getElementById('kodept').value='';
		document.getElementById('kodeunit').value='';
		document.getElementById('diperiksa').value='';
		//document.getElementById('tahun').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo 'Kontrol Losses Ancak '.$_SESSION['lang']['panen'];?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['pt'];?></label></td>
				<td><select id="kodept" name="kodept" onchange=getUnit()><?php echo $optPT;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kodeunit" name="kodeunit"><?php echo $optUnit;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['namakaryawan'];?></label></td>
				<td><select id="diperiksa" name="diperiksa"><?php echo $optKary;?></select></td>
			</tr>
			<tr> 
				<td><?php echo $_SESSION['lang']['tanggal'];?>&nbsp</td>
				<td><input type='text' class='myinputtext' id='tanggal1' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\"> 
					s/d 
					<input type='text' class='myinputtext' id='tanggal2' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\">
				</td>
			</tr>
			<tr height="10"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('kebun_lap_cekancak_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'kebun_lap_cekancak_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
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
