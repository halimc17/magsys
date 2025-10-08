<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/pmn_trader.js'></script>
<?php

//Pilih Unit
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and detail='1'";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe!='HOLDING' and detail='1'";
}else{
    $i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$optUnit="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
}
$optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;

//Pilih Customer
$i="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer where statusinteks = 'Eksternal'";
$optCust="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optCust.="<option value='".$d['kodecustomer']."'>".$d['namacustomer']."</option>";
}
$optCust2="<option value=''>".$_SESSION['lang']['all']."</option>".$optCust;
$optCust="<option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optCust;

//Pilih Barang
$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%' and inactive='0'";
$optBrg="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optBrg.="<option value='".$d['kodebarang']."'>".$d['namabarang']."</option>";
}
$optBrg2="<option value=''>".$_SESSION['lang']['all']."</option>".$optBrg;
$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optBrg;

//Pilih Kontrak
//$i="select nokontrak from ".$dbname.".pmn_kontrakjual where kodebarang='".$kdBrg."' order by tanggalkontrak desc";
$i="select nokontrak from ".$dbname.".pmn_kontrakjual order by tanggalkontrak desc";
$optKontrak="<option value=''></option>";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optKontrak.="<option value='".$d['nokontrak']."'>".$d['nokontrak']."</option>";
}

$arr="##kdUnit##kdCust##nokontrakext##tanggalext##kdBrg##jmlExt##hrgExt##nilaiExt##ppnExt##catatan##nokontrakpembanding##method";
include('master_mainMenu.php');
OPEN_BOX();

echo"<fieldset style='width:450px;float:left;'>
	<legend><b>".$_SESSION['lang']['kontrak'].' '.$_SESSION['lang']['eksternal']."</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td><select id=kdUnit>".$optUnit."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['vendor']."</td>
			<td><select id=kdCust>".$optCust."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
			<td><input type='text' class='myinputtext' id='nokontrakext' style='width:180px;' onkeypress='return tanpa_kutip(event);' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td><input type='text' class='myinputtext' id='tanggalext' onmousemove='setCalendar(this.id)' onkeypress='return false;' value=".tanggalnormal(date('Y-m-d'))." size='10' maxlength='10' style='width:75px;' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['komoditi']."</td>
			<td><select id=kdBrg onchange=getKontrak()>".$optBrg."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td><input type=text class=myinputtextnumber id=jmlExt name=jmlExt value=0 onchange=getNilai() onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength='18' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['harga']."</td>
			<td><input type=text class=myinputtextnumber id=hrgExt name=hrgExt value=0 onchange=getNilai() onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength='18' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nilai']."</td>
			<td><input type=text class=myinputtextnumber id=nilaiExt name=nilaiExt value=0 disabled style=\"width:100px;\" maxlength='18' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nilaippn']."</td>
			<td><input type=text class=myinputtextnumber id=ppnExt name=ppnExt value=0 onchange=getNilai() onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength='18' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['total']."</td>
			<td><input type=text class=myinputtextnumber id=totalExt name=totalExt value=0 disabled style=\"width:100px;\" maxlength='18' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['induk']."</td>
			<td><select id=nokontrakpembanding name=nokontrakpembanding>".$optKontrak."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['catatan']."</td>
			<td><input type='text' class='myinputtext' id='catatan' style='width:300px;' onkeypress='return tanpa_kutip(event);' /></td>
		</tr>
	</table>
	<input type=hidden value=insert id=method>
	<button class=mybutton onclick=saveFranco('pmn_slave_trader','".$arr."')>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
	</fieldset>";

echo"<fieldset  style=width:560px;><legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['data']."</legend>
	<div style=float:left;><img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."' onclick=displatList() ></div>
	<table border=0>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td><td><select id=kdUnitCr style=width:190px;>".$optUnit2."</select></td>
			<td>".$_SESSION['lang']['vendor']."</td><td><select id=kdCustCr style=width:190px;>".$optCust2."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['komoditi']."</td><td><select id=kdBrgCr style=width:190px;>".$optBrg2."</select></td>
			<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td><td><input type=text class=myinputtext  id=noKontrakCr name=noKontrakCr onkeypress=\"return tanpa_kutip(event);\" style=\"width:190px;\" /></td>
		</tr>
		<tr>
			<td colspan4><button onclick=loadData() class=mybutton>".$_SESSION['lang']['find']."</button>  </td>
		</tr>
	</table>
	</fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<fieldset  style=width:1035px;><legend>".$_SESSION['lang']['list']."</legend>
	<img onclick=\"dataKeExcel(event)\" src=\"images/excel.jpg\" class=\"resicon\" title=\"MS.Excel\">
	<table class=sortable cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td>No</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>".$_SESSION['lang']['vendor']."</td>
				<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td align=right>".$_SESSION['lang']['jumlah']."</td>
				<td align=right>".$_SESSION['lang']['harga']."</td>";
//echo"			<td align=right>".$_SESSION['lang']['nilai']."</td>
//				<td align=right>".$_SESSION['lang']['ppn']."</td>
//				<td align=right>".$_SESSION['lang']['total']."</td>";
echo"			<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['induk']."</td>
				<td align=right>".$_SESSION['lang']['kirim']."</td>
				<td>".$_SESSION['lang']['catatan']."</td>
				<td>".$_SESSION['lang']['action']."</td>    
			</tr>
		</thead>
		<tbody id=container>";
			echo"<script>loadData()</script>";
			echo"</tbody>
		<tfoot>
			
		</tfoot>
	</table>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>
