<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language="JavaScript1.2" src="js/zTools.js"></script>
<script language=javascript1.2 src="js/log_2pengeluaranBarangInventaris.js"></script>
<script language="javascript1.2" src="js/zReport.js"></script>
<?php
### BEGIN GET EXITING PERIODE ###
$str="select DISTINCT(DATE_FORMAT(tanggal,'%Y-%m')) AS periode from ".$dbname.".log_transaksi_vw
      order by tanggal desc";
$res=mysql_query($str);
$num_rows = mysql_num_rows($res);
$optperiode="";
if($num_rows >= 1){
	while($bar=mysql_fetch_object($res))
	{
		$optperiode.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
	}
}else{
}
### END GET EXITING PERIODE ###

$arr="##kodebarang##nopo##periode";

echo"<fieldset style='float: left;'>
		<legend><b>Laporan Penerimaan Barang Inventaris</b></legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td><input type=text size=10 maxlength=10 id=kodebarang placeholder='".$_SESSION['lang']['caribarang']."' class=myinputtext onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."',event);\">
				<button class=mybutton onclick=setAll()>".$_SESSION['lang']['all']."</button></td>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td><input type=text size=30 maxlength=30 id=nopo class=myinputtext></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td><select id=periode>".$optperiode."</select></td>
			</tr>
			<tr>
				<td></td>
				<td><button class=mybutton onclick=proses()>".$_SESSION['lang']['proses']."</button></td>
			</tr>
		</table>
	</fieldset>";
	
CLOSE_BOX();

OPEN_BOX('','Result:');
echo"<span id=printPanel style='display:none;'>
	<img onclick=\"zExcel(event,'log_slave_2pengeluaranBarangInventaris.php','".$arr."');\" src=images/excel.jpg class=resicon title='MS.Excel'> 
	</span>    
	<div id=container style='width:100%;height:359px;overflow:scroll; overflow-x:hidden'>
    </div>";
CLOSE_BOX();
echo close_body();
?>