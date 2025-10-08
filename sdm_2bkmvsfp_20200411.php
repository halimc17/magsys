<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/sdm_bkmvsfp.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
##untuk pilihan Unit
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
}elseif(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
	$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['induk']."'";
}else{
	$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
}
$nUnit=mysql_query($iUnit) or die(mysql_error($conn));
while($dUnit=mysql_fetch_assoc($nUnit))
{
    $optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
}
##untuk pilihan Divisi
$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
$iDivisi="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe not like 'GUDANG%' and induk='".$_SESSION['empl']['lokasitugas']."'";
$nDivisi=mysql_query($iDivisi) or die(mysql_error($conn));
while($dDivisi=mysql_fetch_assoc($nDivisi))
{
    $optDivisi.="<option value=".$dDivisi['kodeorganisasi'].">".$dDivisi['namaorganisasi']."</option>";
}
?>

<?php
include('master_mainMenu.php');
OPEN_BOX();
$arr="##unit##divisi##tgl1##tgl2";	
echo "
<fieldset style='float:left;'><legend><b>Absen BKM vs Finger Print</b></legend>
	<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=unit onchange=getDivisi() style=\"width:155px;\" >".$optUnit."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['divisi']."</td>
            <td>:</td>
            <td><select id=divisi style=\"width:155px;\" >".$optDivisi."</select></td>
        </tr>
		<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
            s/d <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
			</td>
		</tr>	
		<tr>
			<td colspan=100>&nbsp;</td>
		</tr>
		<tr>
			<td colspan=100>
				<button onclick=zPreview('sdm_slave_2bkmvsfp','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
				<button onclick=zExcel(event,'sdm_slave_2bkmvsfp.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
				<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
	<div id='printContainer' style='overflow:auto;height:350px;max-width:1320px'; ></div>
</fieldset>";

CLOSE_BOX();
echo close_body();

?>