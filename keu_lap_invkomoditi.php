<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	//Pilih Unit
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and length(kodeorganisasi)=4 order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe<>'HOLDING' and induk='".$_SESSION['empl']['kodeorganisasi']."' 
			and length(kodeorganisasi)=4 order by namaorganisasi";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'  order by namaorganisasi";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih pelanggan
	$optCust="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sCust="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer order by namacustomer asc";
	$qCust=mysql_query($sCust) or die(mysql_error($conn));
	while($rCust=mysql_fetch_assoc($qCust)){
		$optCust.="<option value='".$rCust['kodecustomer']."'>".$rCust['kodecustomer']."-".$rCust['namacustomer']."</option>";
	}

	//Pilih kodebarang
	$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sBrg="select distinct a.kodebarang,b.namabarang from ".$dbname.".keu_penagihandt a 
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			order by b.namabarang asc";
	$qBrg=mysql_query($sBrg) or die(mysql_error($conn));
	while($rBrg=mysql_fetch_assoc($qBrg)){
		$optBrg.="<option value='".$rBrg['kodebarang']."'>".$rBrg['kodebarang']."-".$rBrg['namabarang']."</option>";
	}

	//Pilih Invoice
	$optInvoice="<option value=''></option>";
	$sInvoice="select distinct noinvoice from ".$dbname.".keu_penagihanht where tipeinvoice>0 order by noinvoice asc";
	$qInvoice=mysql_query($sInvoice) or die(mysql_error($conn));
	while($rInvoice=mysql_fetch_assoc($qInvoice)){
		$optInvoice.="<option value='".$rInvoice['noinvoice']."'>".$rInvoice['noinvoice']."</option>";
	}

	//Pilih Kontrak
	$optKontrak="<option value=''></option>";
	$sKontrak="select distinct nokontrak from ".$dbname.".keu_penagihanht where tipeinvoice>0 and nokontrak<>'' order by noinvoice asc";
	$qKontrak=mysql_query($sKontrak) or die(mysql_error($conn));
	while($rKontrak=mysql_fetch_assoc($qKontrak)){
		$optKontrak.="<option value='".$rKontrak['nokontrak']."'>".$rKontrak['nokontrak']."</option>";
	}

	$arr="##kodeunit##kodecust##kodebarang##noinvoice##nokontrak##tanggal1##tanggal2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function Clear1(){
		document.getElementById('kodeunit').value='';
		document.getElementById('kodecust').value='';
		document.getElementById('noinvoice').value='';
		document.getElementById('nokontrak').value='';
		document.getElementById('tanggal1').value='';
		document.getElementById('tanggal2').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo strtoupper($_SESSION['lang']['laporan']).' '.strtoupper($_SESSION['lang']['penagihan']).' PER '.strtoupper($_SESSION['lang']['komoditi']);?></b></legend>

		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kodeunit" name="kodeunit"><?php echo $optUnit;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['nmcust'];?></label></td>
				<td><select id="kodecust" name="kodecust"><?php echo $optCust;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['namabarang'];?></label></td>
				<td><select id="kodebarang" name="kodebarang"><?php echo $optBrg;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['noinvoice'];?></label></td>
				<td>
					<input type="text" style="width:157px" id="noinvoice" name="noinvoice" list="listinvoice" class="myinputtext" />
					<datalist id="listinvoice"><?php echo $optInvoice;?></datalist>
				</td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['NoKontrak'];?></label></td>
				<td>
					<input type="text" style="width:157px" id="nokontrak" name="nokontrak" list="listkontrak" class="myinputtext" />
					<datalist id="listkontrak"><?php echo $optKontrak;?></datalist>
				</td>
			</tr>
			<tr> 
				<td><?php echo $_SESSION['lang']['tanggal'];?>&nbsp</td>
				<td><input type='text' class='myinputtext' id='tanggal1' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\"> 
					sd 
					<input type='text' class='myinputtext' id='tanggal2' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\">
				</td>
			</tr>

			<tr height="10"><td colspan="2"></td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('keu_lap_invkomoditi_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'keu_lap_invkomoditi_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
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
