<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$arr="##kdOrg##periode##mesin";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK') and detail='1' order by namaorganisasi asc ";	
	$sPeriode="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '%M' order by periode desc";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
	$sPeriode="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '%M' order by periode desc";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}else{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
	$sPeriode="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '%M' and kodeorg='".$lksiTugas."' order by periode desc";
    //$optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\">";
}
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
//exit('Warning: '.$sPeriode);
while($rPeriode=mysql_fetch_assoc($qPeriode)){
	//$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	$optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}

$optMesin="<option value=''>".$_SESSION['lang']['all']."</option>";
$optMesin.="<option value='Bangunan_Kantor'>Bangunan_Kantor</option>";
$optMesin.="<option value='Bangunan_Pabrik'>Bangunan_Pabrik</option>";
$optMesin.="<option value='GuestHouse_Baru'>GuestHouse_Baru</option>";
$optMesin.="<option value='GuestHouse_Lama'>GuestHouse_Lama</option>";
$optMesin.="<option value='Power_House'>Power_House</option>";
$optMesin.="<option value='Rumah_Manager1'>Rumah_Manager1</option>";
$optMesin.="<option value='Rumah_Manager2'>Rumah_Manager2</option>";
$optMesin.="<option value='Rumah_Manager3'>Rumah_Manager3</option>";
$optMesin.="<option value='Timbangan'>Timbangan</option>";

$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg)){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function showpopup(kodemesin,tanggal,kdorg,type,ev){
   param='kodemesin='+kodemesin+'&tanggal='+tanggal+'&kdorg='+kdorg+'&type='+type;
   tujuan='pabrik_lap_earthtest_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Laporan Earth Test '+kdorg+' '+kodemesin+' '+tanggal,content,width,height,ev); 
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1(){
    document.getElementById('kdOrg').value='';
    document.getElementById('mesin').value='';
    //document.getElementById('periode').value='';
}
</script>
<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['laporan']." Earth Test";?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><?php echo $optOrg;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['lokasi'];?></label></td>
				<td><select id="mesin" name="mesin"><?php echo $optMesin;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['periode'];?></label></td>
				<td><select id="periode" name="periode" style="width:80px"><!--<option value=""></option>--><?php echo $optPeriode;?></select></td>
			</tr>
			<tr height="20"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
				<button onclick="zPreview('pabrik_lap_earthtest_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
				<button onclick="zExcel(event,'pabrik_lap_earthtest_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
				<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
			</td></tr>
		</table>
	</fieldset>
</div>

<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:330px;max-width:1235px'></div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>
