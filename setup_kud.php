<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zConfig.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/setup_kud.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

# Lokasi Tugas
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
} elseif($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
} else {
  $tmpOpt = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebunonly');
}

# Kebun Berdasarkan Lokasi Tugas
$sKebun = array(''=>'');
foreach($tmpOpt as $key=>$row) {
  $sKebun[$key] = $row;
}

# Form Cari Data Kebun
$searchEls = $_SESSION['lang']['kebun']." ";
$searchEls .= makeElement('sKebun','select','',
  array('onchange'=>"getAfdeling(this,'sAfdeling')",'style'=>'width:150px'),$sKebun)." ";
$searchEls .= $_SESSION['lang']['afdeling']." ";
$searchEls .= makeElement('sAfdeling','select','',array('style'=>'width:150px'),array())." ";
$searchEls .= makeElement('searchIt','button',$_SESSION['lang']['find'],array('onclick'=>'showData()'))." ";

# Render Search Element
echo "<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>";
echo "<legend><b>".$_SESSION['lang']['searchdata']."</b></legend>";
echo $searchEls;
echo "</fieldset>";


# Begin Select Option Value Form KUD
$optBlok=array();

$str="select supplierid, namasupplier from ".$dbname.".log_5supplier where kodekelompok = 'S004' order by namasupplier ASC";
$res=mysql_query($str);
$optSup='';
$optSup.="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
	$optSup.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}
# End Select Option Value Form KUD

#======= Begin Form KUD ============
echo"<div id='formKUD' style='display:none;margin-bottom:10px;clear:both'>
	<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>
	<legend><b>KUD</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['kodeblok']."</td>
			<td><select id='kodeblok'>".$optBlok."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namasupplier']."</td>
			<td><select id='supplierid'>".$optSup."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nosertifikat']."</td>
			<td><input class=myinputtext type='text' id='nosertifikat' maxlength='45'></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type='hidden' id='hiddenproses' value=''>
				<input type='hidden' id='hiddensupplierid' value=''>
				<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</fieldset>
	</div>";
#======= End Form KUD ============

#=======Table===============
# Display Table
echo "<div id='KUDTable' style='display:none;margin-bottom:10px;clear:both'>";
#echo masterTable($dbname,'setup_blok',"*",array(),array(),array(),array(),'setup_slave_blok_pdf');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>