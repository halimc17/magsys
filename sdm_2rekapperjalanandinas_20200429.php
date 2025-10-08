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
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/sdm_2rekapperjalanandinas.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['perjalanandinas'];

$sTgl="select distinct substr(tanggalperjalanan,1,7) as periode from ".$dbname.".sdm_pjdinasht where tanggalperjalanan != '0000-00-00' order by tanggalperjalanan desc";
$qTgl=mysql_query($sTgl) or die(mysql_error());
$no=0;
$optPeriode="";
while($rTgl=mysql_fetch_assoc($qTgl))
{
	$no+=1;
	if($no==1){
		$optPeriode.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
	}else
	if(substr($rTgl['periode'],5,2)=='12'){
		$optPeriode.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
	}
    $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$sLoc="select kodeorganisasi,namaorganisasi,alokasi from ".$dbname.".organisasi 
      where length(kodeorganisasi)=4 
	  order by namaorganisasi";
$qLoc=mysql_query($sLoc) or die(mysql_error());
$optLoc="<option value=''>".$_SESSION['lang']['all']."</option>";;
while($rLoc=mysql_fetch_assoc($qLoc))
{
   $optLoc.="<option value='".$rLoc['kodeorganisasi']."'>".$rLoc['kodeorganisasi']."-".$rLoc['namaorganisasi']."</option>";
}

$arr="##periode##lokasitugas##namakaryawan";
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['lokasitugas']."</td>
          <td><select id=lokasitugas style=width:150px;>".$optLoc."</select></td>
          </tr>";
echo"<tr><td>".$_SESSION['lang']['namakaryawan']."</td>
          <td><input type=text id=namakaryawan class=myinputtext style=width:150px;></td>
          </tr>";
echo"<tr height=\"20\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">
         <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'sdm_slave_2rekapperjalanandinas.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
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