<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pabrik_perbaikan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>


<?



##untuk pilihan pabrik 	
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPabrik="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$nPabrik=mysql_query($iPabrik) or die(mysql_error($conn));
while($dPabrik=mysql_fetch_assoc($nPabrik))
{
    $optPabrik.="<option value=".$dPabrik['kodeorganisasi'].">".$dPabrik['namaorganisasi']."</option>";
}
$optStation.="<option value=''>".$_SESSION['lang']['all']."</option>";
//$optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' ";
//$nStation=mysql_query($iStation) or die(mysql_error($conn));
//while($dStation=mysql_fetch_assoc($nStation))
//{
//    $optStation.="<option value=".$dStation['kodeorganisasi'].">".$dStation['namaorganisasi']."</option>";
//}                       
			
?>


<?
include('master_mainMenu.php');
OPEN_BOX();
$arr="##unit##tgl1##tgl2";	

echo "<fieldset style='float:left;'><legend><b>Absen BKM vs Finger Print</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=unit style=\"width:155px;\" >".$optPabrik."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
            s/d
            <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
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
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();


?>