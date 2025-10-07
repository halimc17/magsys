<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<?php
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$optOrg="<option value=\"\">".$_SESSION['lang']['pilihdata']."</option>";
if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') order by namaorganisasi asc ";	
}
else
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
}
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct periode from ".$dbname.".kebun_rencanasisip order by periode";
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
while($rPeriode=mysql_fetch_assoc($qPeriode))
{
     $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}
 

$arr="##kdOrg##kdAfd##periode";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/kebun_2rencanasisip.js'></script>

<link rel=stylesheet type='text/css' href='style/zTable.css'>
<?php    

$title[0]=$_SESSION['lang']['laprencanasisip'];
echo "<fieldset style=\"float: left;\">
<legend><b>".$title[0]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['kebun']."</label></td><td><select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange=\"getAfd()\">".$optOrg."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['afdeling']."</label></td><td><select id=\"kdAfd\" name=\"kdAfd\" style=\"width:150px\"><option value=\"\"></option></select></td></tr>
<tr><td><label>".$_SESSION['lang']['periode']."</label></td><td><select id=\"periode\" name=\"periode\" style=\"width:62px\">".$optPeriode."</select><td></tr>
<tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
<tr><td colspan=\"2\">
    <button onclick=\"zPreviewd('kebun_slave_2rencanasisip','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'kebun_slave_2rencanasisip.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    <button onclick=\"pdf(event,'kebun_slave_2rencanasisip.php')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['pdf']."</button>
</table>
</fieldset>";
?>
<fieldset style='clear:both;'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto; height:350px; max-width:1220px;'></div>
    <div id='printContainer1' style='overflow:auto; height:350px; max-width:1220px;'>
</div></fieldset>
<?php   
CLOSE_BOX();
echo close_body();
?>