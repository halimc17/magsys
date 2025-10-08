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
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi, kodeorganisasi from ".$dbname.".organisasi where  tipe='TRAKSI' order by namaorganisasi asc";
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
    $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$qThn=mysql_query($sThn) or die(mysql_error($conn));
while($rThn=mysql_fetch_assoc($qThn))
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$arr="##thnBudget##kdUnit";
$arr1="##thnBudget1##kdUnit1";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>

function Clear1()
{
    document.getElementById('thnBudget').value='';
    document.getElementById('kdUnit').value='';
    document.getElementById('printContainer').innerHTML='';
}
function Clear2()
{
    document.getElementById('thnBudget1').value='';
    document.getElementById('kdUnit1').value='';
    document.getElementById('printContainer1').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

$frm[0].="
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['byTraski']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td><select id='thnBudget' style=\"width:150px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td><select id='kdUnit'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr height=20><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2><button onclick=\"zPreview('bgt_slave_laporan_biaya_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>Preview</button>
<button onclick=\"zPdf('bgt_slave_laporan_biaya_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>PDF</button>
<button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_kendaraan.php','".$arr."')\" class=mybutton name=preview id=preview>Excel</button>
<button onclick=Clear1() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>

</table>
</fieldset>
";

$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>
";

$frm[1].="
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['byTraski']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td><select id='thnBudget1' style=\"width:150px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td><select id='kdUnit1'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr height=20><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2><button onclick=\"zPreview('bgt_slave_laporan_biaya_kendaraan1','".$arr1."','printContainer1')\" class=mybutton name=preview id=preview>Preview</button>
<button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_kendaraan1.php','".$arr1."')\" class=mybutton name=preview id=preview>Excel</button>
<button onclick=Clear2() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>

</table>
</fieldset>
";

$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>
";

//========================
$hfrm[0]=$_SESSION['lang']['byTraski'];
$hfrm[1]=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['byTraski'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1100);
//===============================================

CLOSE_BOX();
echo close_body();
?>