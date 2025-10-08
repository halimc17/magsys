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
<script type="text/javascript" src="js/pmn_2suratperintahpengiriman.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['suratperintahpengiriman'];

$sTgl="select distinct substr(tanggalsisa,1,7) as periode from ".$dbname.".sdm_pjdinasht order by tanggalsisa desc";
$qTgl=mysql_query($sTgl) or die(mysql_error());
$optPeriode="";
while($rTgl=mysql_fetch_assoc($qTgl))
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$sLoc="select kodeorganisasi,namaorganisasi,alokasi from ".$dbname.".organisasi 
      where length(kodeorganisasi)=4 
	  order by namaorganisasi";
$qLoc=mysql_query($sLoc) or die(mysql_error());
$optLoc="<option value=''>".$_SESSION['lang']['all']."</option>";
while($rLoc=mysql_fetch_assoc($qLoc))
{
   $optLoc.="<option value='".$rLoc['kodeorganisasi']."'>".$rLoc['kodeorganisasi']."-".$rLoc['namaorganisasi']."</option>";
}


$sBar="select kodebarang,namabarang from ".$dbname.".log_5masterbarang 
      where kelompokbarang = '400' and inactive=0  
	  order by namabarang asc";
$qBar=mysql_query($sBar) or die(mysql_error());
$optBar="<option value=''>".$_SESSION['lang']['all']."</option>";;
while($rBar=mysql_fetch_assoc($qBar))
{
   $optBar.="<option value='".$rBar['kodebarang']."'>".$rBar['namabarang']."</option>";
}

$optPel="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPel="select * from ".$dbname.".pmn_4customer order by namacustomer";
$nPel=  mysql_query($iPel) or die (mysql_error($conn));
while($dPel=  mysql_fetch_assoc($nPel))
{
    $optPel.="<option value='".$dPel['kodecustomer']."'>".$dPel['namacustomer']."</option>";
}

$arr="##tanggaldari##tanggalsampai##komoditi##penjual";
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['tgldari']."</td>";
echo"<td><input type=text class=myinputtext id=tanggaldari readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 /></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['tanggalsampai']."</td>
          <td><input type=text class=myinputtext id=tanggalsampai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 /></td>
          </tr>";

echo"<tr><td>".$_SESSION['lang']['komoditi']."</td>
          <td><select id=komoditi style=width:150px;>".$optBar."</select></td>
          </tr>";

echo"<tr><td>".$_SESSION['lang']['penjual']."</td>
          <td><select id=penjual style=width:150px;>".$optPel."</select></td>
          </tr>";



echo"<tr height=\"20\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">

<button onclick=zPreview('pmn_slave_2suratperintahpengiriman','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		

      <button onclick=\"zExcel(event,'pmn_slave_2suratperintahpengiriman.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
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


//   <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    
CLOSE_BOX();
echo close_body();
?>