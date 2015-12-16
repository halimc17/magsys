<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>History ".$_SESSION['lang']['arealstatement']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="application/javascript" src="js/kebun_2historisaresta.js"></script>
<?php
$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qKbn=mysql_query($sKbn) or die(mysql_error());
$optKbn="";
while($rKbn=mysql_fetch_assoc($qKbn))
{
    $optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
}

$optTahun = array();
for($i=date('Y');$i>date('Y')-10;$i--) {
	$optTahun[$i] = $i;
}
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="entryForm">
<fieldset>
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['kebun']?></td>
<td>:</td>
<td><select id="idKbn" name="idKbn" style="width:150px;"><?php echo $optKbn ?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['tahun']?></td>
<td>:</td>
<td><?php echo makeElement('tahun','select',date('Y'),array(),$optTahun)?></td>
</tr>
<tr>
<td colspan="3" id="tmblHeader">
<button class=mybutton id='dtl_pem' onclick='previewData();'><?php echo $_SESSION['lang']['preview']?></button>
<button class=mybutton id='dtl_xls' onclick='detexcel(event);'><?php echo $_SESSION['lang']['excel']?></button>
</td>
</tr>
</table>
</fieldset>

</div>

<?php
CLOSE_BOX();

?>
<div id="result" style="display:none;">
<?php OPEN_BOX(); ?>
<div id="list_ganti" >



</div>
<?php CLOSE_BOX();?>
</div>
<?php 

echo close_body();
?>