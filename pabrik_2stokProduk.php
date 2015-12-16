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
if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   

}
else
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK'";   

}
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        
        
}	

$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iSup="SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang WHERE inactive='0' and kelompokbarang='400'"
        . " and kodebarang not in ('40000001','40000002','40000003') order by namabarang asc";
$nSup=mysql_query($iSup) or die (mysql_error($conn));
while ($dSup=mysql_fetch_assoc($nSup))
{
    $optBrg.="<option value=".$dSup['kodebarang'].">".$dSup['namabarang']."</option>";
}

$arr="##kdOrgRep##kdBrgRep##tgl1Rep##tgl2Rep";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<?
echo "<fieldset style='float:left;'><legend><b>Laporan Sisa Stok</b></legend>
	<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=kdOrgRep style=\"width:155px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=kdBrgRep style='width:155px;'>".$optBrg."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>
	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('pabrik_slave_2stokProduk','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2stokProduk.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1150'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();
?>