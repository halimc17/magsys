<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src="js/zTools.js"></script>
<script language="javascript" src="js/sdm_training.js"></script>
<script language=javascript1.2 src=js/generic.js></script>
<?php
$nomor =$_GET['nomor'];
$str="select * from ".$dbname.".sdm_karyawantraining where nomor=".$nomor."";
$berlakudari='';
$berlakusampai='';
$scansertifikat='';

$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$berlakudari	=tanggalnormal($bar->berlakudari);
	$berlakusampai	=tanggalnormal($bar->berlakusampai);
	$scansertifikat	=$bar->scansertifikat;
}

echo"<fieldset style='width:600px;'><legend>Upload Sertifikat</legend>
		<form id='scanupload' name='scanupload' method='post' enctype='multipart/form-data'>
			<table cellapacing=1 border=0>
			<!--	<tr> 
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>
						<input type=text id=tanggal1s class=myinputtext this.onkeypress=\"return false;\" onmouseover=this.setCalendar(this) size=10 value='".$berlakudari."'>
						s/d 
						<input type=text id=tanggal2s class=myinputtext this.onkeypress=\"return false;\" onmouseover=this.setCalendar(this) size=10 value='".$berlakusampai."'>
					</td>
				</tr>	-->
				<tr>
	   				<td>File</td>
	   				<td>
						<input type=hidden name=MAX_FILE_SIZE value=4700000>
						<input type=file id=photo name=photo size=100>
						<input type=hidden name=nomorx id=nomorx value='".$nomor."'>
					</td>						
				</tr>
			</table>
		</form>
		<center>1 File(s) Max 1000 Kb.<br>
			<button onclick=parent.savescan()>Save</button>
		</center>
	</fieldset>";
?>
