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
<?

### begin get nama unit ###
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by induk,kodeorganisasi";
$nUnit=mysql_query($iUnit) or die(mysql_error($conn));
while($dUnit=mysql_fetch_assoc($nUnit))
{
    $optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
}
### end get nama unit ###

### BEGIN GET EXITING PERIODE ###
//$str="select DISTINCT(DATE_FORMAT(tanggal,'%Y-%m')) AS periode from ".$dbname.".log_transaksi_vw order by tanggal desc";
$str="select DISTINCT(periode) AS periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=mysql_query($str);
$num_rows = mysql_num_rows($res);
$optperiode="<option value=''>".$_SESSION['lang']['all']."</option>";
if($num_rows >= 1){
	while($bar=mysql_fetch_object($res))
	{
		$no+=1;
		if($no==1){
			$optperiode.="<option value='".substr($bar->periode,0,4)."'>".substr($bar->periode,0,4)."</option>";
		}else
		if(substr($bar->periode,5,2)=='12')
		{
			$optperiode.="<option value='".substr($bar->periode,0,4)."'>".substr($bar->periode,0,4)."</option>";
		}
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
				<td>".$_SESSION['lang']['unit']."</td>
				<td><select id=unit>".$optUnit."</select></td>
			</tr>
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