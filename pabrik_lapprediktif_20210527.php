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


$frm[0]='';
$frm[1]='';


##untuk pilihan pabrik 	
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPabrik="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' ";
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
$optMesin.="<option value=''>".$_SESSION['lang']['all']."</option>";
			
?>


<?
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';


OPEN_BOX();
$arr="##pabrik##station##mesin##tgl1##tgl2";	
$arrv="##pabrikv##stationv##mesinv##tgl1v##tgl2v";	

$frm[0].="<fieldset style='float:left;'><legend><b>Form I</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrik onchange=getStation() style=\"width:155px;\" >".$optPabrik."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['station']."</td>
            <td>:</td>
            <td><select id=station onchange=getMachine() style=\"width:155px;\">".$optStation."</select></td>
        </tr>
		<tr>
            <td>".$_SESSION['lang']['mesin']."</td>
            <td>:</td>
            <td><select id=mesin style=\"width:155px;\">".$optMesin."</select></td>
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
		<button onclick=zPreview('pabrik_lapprediktif_slave_v1','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_lapprediktif_slave_v1.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>

		<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
</div></fieldset>";




###form input II
################


$frm[1].="<fieldset style='float:left;'><legend><b>Form II</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrikv onchange=getStationv() style=\"width:155px;\" >".$optPabrik."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['station']."</td>
            <td>:</td>
            <td><select id=stationv onchange=getMachinev() style=\"width:155px;\">".$optStation."</select></td>
        </tr>
		<tr>
            <td>".$_SESSION['lang']['mesin']."</td>
            <td>:</td>
            <td><select id=mesinv style=\"width:155px;\">".$optMesin."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1v' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
            s/d
            <input type='text' class='myinputtext' id='tgl2v' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('pabrik_lapprediktif_slave_v2','".$arrv."','printContainerv') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_lapprediktif_slave_v2.php','".$arrv."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>

		<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainerv' style='overflow:auto;height:350px;max-width:1220px'; >
</div></fieldset>";


$hfrm[0]=$_SESSION['lang']['laporan'].' Predictive I';
$hfrm[1]=$_SESSION['lang']['laporan'].' Predictive II';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();
?>
