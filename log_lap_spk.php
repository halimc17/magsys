<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	//Pilih PT
	$optPT="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select DISTINCT induk from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E' and LENGTH(kodeorganisasi)=4) and detail='1' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['induk']."' and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optPT.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Unit
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' and induk='".$_SESSION['empl']['induk']."' order by namaorganisasi";
	}else{
		$optUnit="";
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
			and detail='1' order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Rekanan Kontraktor
	$optRekanan="<option value=''>".$_SESSION['lang']['all']."</option>";
	$i="select supplierid,namasupplier from ".$dbname.".log_5supplier where left(supplierid,1) in ('K','T') order by namasupplier";
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optRekanan.="<option value='".$d['supplierid']."'>".$d['namasupplier']."</option>";
	}

	$arr="##kodept##kodeunit##koderekanan##notransaksi##tanggal1##tanggal2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function getUnit(){
	    kodept=document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
		param='kodept='+kodept+'&proses=getUnit';
	    tujuan='log_lap_spk_slave.php';
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
		//document.getElementById('kodeunit').value='';
		document.getElementById('notransaksi').value='';
		document.getElementById('tanggal1').value='';
		document.getElementById('tanggal2').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo 'Daftar SPK';?></b></legend>
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
				<td><label><?php echo $_SESSION['lang']['kontraktor'];?></label></td>
				<td><select id="koderekanan" name="koderekanan"><?php echo $optRekanan;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['notransaksi'];?></label></td>
				<td><input type=text id="notransaksi" name="notransaksi" class=myinputtext maxlength=30 size=35></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['tanggal'];?></label></td>
				<td><input type=text class=myinputtext id=tanggal1 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\">
				s/d	<input type=text class=myinputtext id=tanggal2 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
			</tr>
			<tr height="10"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('log_lap_spk_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'log_lap_spk_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
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
