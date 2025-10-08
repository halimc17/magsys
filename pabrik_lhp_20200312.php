<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<?php
$sPabrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and detail<>'0'";
$qPabrik=mysql_query($sPabrik) or die(mysql_error());
//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
//$optPabrik="<option value=''>''</option>";
while($rPabrik=mysql_fetch_assoc($qPabrik))
{
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['namaorganisasi']."</option>";
}

$arr="##tanggal##idPabrik";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pabrik_lhp.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
$frm[0].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['find']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['pabrik']."</label></td><td><select id=\"idPabrik\" name=\"idPabrik\" style=\"width:210px\">".$optPabrik."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggal']."</label></td><td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>
<tr><td colspan=\"2\"><button onclick=\"zPreview('pabrik_slave_lhp','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pabrik_slave_lhp.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1250px'>
</div></fieldset>";

//========================
$hfrm[0]="Laporan Harian Produksi";
drawTab('FRM',$hfrm,$frm,200,900);
//===============================================

CLOSE_BOX();
echo close_body();
?>