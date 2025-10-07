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
$str="select distinct periode from ".$dbname.".log_5saldobulanan order by periode desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$optPeriode.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
$qPabrik=mysql_query($sPabrik) or die(mysql_error());
while($rPabrik=mysql_fetch_assoc($qPabrik))
{
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['namaorganisasi']."</option>";
}
//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";

$sCustomer="select distinct(a.koderekanan),b.namacustomer from ".$dbname.".pmn_kontrakjual a
			LEFT JOIN ".$dbname.".pmn_4customer b ON a.koderekanan=b.kodecustomer";
$qCustomer=mysql_query($sCustomer) or die(mysql_error());
$optCustomer = "<option value=''>".$_SESSION['lang']['all']."</option>";
while($rCustomer=mysql_fetch_assoc($qCustomer))
{
	$optCustomer.="<option value=".$rCustomer['koderekanan'].">".$rCustomer['namacustomer']."</option>";
}

$optPabrik1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOpt=mysql_query($sOpt) or die(mysql_error());
while($rOpt=mysql_fetch_assoc($qOpt))
{
	$optPabrik1.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
$optBrg = "<option value=''>".$_SESSION['lang']['all']."</option>";
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000001', '40000002')";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg1.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$arr="##tanggalmulai##tanggalakhir##idPabrik##kdBrg##idCustomer";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2penjualan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?      
$frm[0].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['find']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td style='display:none'><label>".$_SESSION['lang']['periode']."</label></td><td style='display:none;'><select id=\"periode\" name=\"periode\" style=\"width:150px\">".$optPeriode."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['tgldari']."</label></td><td><input type=text class=myinputtext id=tanggalmulai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>
<tr><td><label>".$_SESSION['lang']['tglsmp']."</label></td><td><input type=text class=myinputtext id=tanggalakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>
<tr><td><label>".$_SESSION['lang']['nm_perusahaan']."</label></td><td><select id=\"idPabrik\" name=\"idPabrik\" style=\"width:210px\">".$optPabrik."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['komoditi']."</label></td><td><select id=\"kdBrg\" name=\"kdBrg\" style=\"width:210px\">".$optBrg."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['nmcust']."</label></td><td><select id=\"idCustomer\" name=\"idCustomer\" style=\"width:210px\">".$optCustomer."</select></td></tr>
<tr><td colspan=\"2\"><button onclick=\"zPreview('pmn_slave_2rekappenjualan','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2rekappenjualan.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";

//========================
$hfrm[0]="Rekap Penjualan";
drawTab('FRM',$hfrm,$frm,200,900);
//===============================================

CLOSE_BOX();
echo close_body();
?>