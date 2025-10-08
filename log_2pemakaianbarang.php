	<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2 src='js/log_2pemakaianbarang.js'></script>


<?php



##untuk pilihan pabrik 	
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
$nUnit=mysql_query($iUnit) or die(mysql_error($conn));
while($dUnit=mysql_fetch_assoc($nUnit))
{
    $optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
}                 
			
?>


<?php
include('master_mainMenu.php');
OPEN_BOX();
$arr="##unit##tgl1##tgl2##barang";	

echo "<fieldset style='float:left;'><legend><b>Rincian Pemakaian Barang</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=unit style=\"width:155px;\" >".$optUnit."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
            s/d
            <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['kodebarang']."</td>
            <td>:</td>
            <td><input type=text  id=barang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\">
                <input type=text  id=namaBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\">
            <img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td></td>
        </tr>

	<tr>
            <td colspan=100>&nbsp;</td>
	</tr>
	<tr>
            <td colspan=100>
            <button onclick=zPreview('log_slave_2pemakaianbarang','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
            <button onclick=zExcel(event,'log_slave_2pemakaianbarang.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
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