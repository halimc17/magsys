<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
$arr0="##tanggal"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/pmn_2rekapdo.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['do'];

$sTgl="select distinct substr(tanggaldo,1,7) as periode from ".$dbname.".pmn_suratperintahpengiriman order by tanggaldo desc";
$qTgl=mysql_query($sTgl) or die(mysql_error());
$optPeriode="";
while($rTgl=mysql_fetch_assoc($qTgl))
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$sBar="select kodebarang,namabarang from ".$dbname.".log_5masterbarang 
      where kodebarang = '40000001' or kodebarang = '40000002' 
	  order by namabarang";
$qBar=mysql_query($sBar) or die(mysql_error());
$optBar="<option value=''>".$_SESSION['lang']['all']."</option>";;
while($rBar=mysql_fetch_assoc($qBar))
{
   $optBar.="<option value='".$rBar['kodebarang']."'>".$rBar['namabarang']."</option>";
}

$arr="##periode##komoditi";
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['komoditi']."</td>
          <td><select id=komoditi style=width:150px;>".$optBar."</select></td>
          </tr>";
echo"<tr height=\"20\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">
         <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2rekapdo.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>

<div id='printContainer' style='overflow:auto;height:250px;max-width:1220px;'>
</div>
</fieldset>";



CLOSE_BOX();
echo close_body();
?>