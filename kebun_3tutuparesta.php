<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".$_SESSION['lang']['tutup']." ".$_SESSION['lang']['arealstatement']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="application/javascript" src="js/kebun_3tutuparesta.js"></script>
<?php
$lksi=substr($_SESSION['empl']['lokasitugas'],0,4);
$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$lksi."'";
$qKbn=mysql_query($sKbn) or die(mysql_error());
$optKbn="";
while($rKbn=mysql_fetch_assoc($qKbn))
{
    $optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
}
$tahun=date("Y");

$arrPeriod = month_inbetween((date("Y")-1).'-01',(date("Y")+1).'-12');


$periode=(date("Y")-1).'-01';
$perakhir=date("Y-m-d");
//echo $perakhir;
//$perakhir=(date("Y")+1).'-01';

#bentuk daftar periode sampai periode terakhir
$start = new DateTime($periode.'-01');
$end = new DateTime($perakhir.'-01');
$inc = DateInterval::createFromDateString('first day of next month');
$end->modify('+1 day');
$p = new DatePeriod($start,$inc,$end);  
foreach ($p as $d)
{
    $optPerList.="<option value='".$d->format('Y-m')."'>".$d->format('Y-m')."</option>";
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
<td><?php echo $_SESSION['lang']['periode']?></td>
<td>:</td> 
<td>
    <select id="Tahun" name="Tahun" style="width:150px;"><?php echo $optPerList ?></select>
	
</td>
</tr>
<!--<?php echo makeElement('Tahun',"select","",array('style'=>'width:70px'),$arrPeriod);?>-->
<tr>
<td colspan="3" id="tmblHeader">
<button class=mybutton id='dtl_pem' onclick='saveData()'><?php echo $_SESSION['lang']['save']?></button><button class=mybutton id='cancel_gti' onclick='cancelSave()'>Reset</button>
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