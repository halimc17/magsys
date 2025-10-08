<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5hargabibit.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX();

$optRegional='';
$sReg="select * from ".$dbname.".bgt_regional order by regional asc";
$rReg=mysql_query($sReg);
while($bReg=mysql_fetch_object($rReg)){
	$optRegional.="<option value='".$bReg->regional."'>".$bReg->regional."</option>";	
}

$arrStatus = getEnum($dbname,'kebun_5hargabibit','status');
foreach($arrStatus as $key=>$row) {
	if($row=='I'){
		$optStatus.="<option value='".$row."'>Inti</option>";
	}else if($row=='P'){
		$optStatus.="<option value='".$row."'>Plasma</option>";
	}else{
		$optStatus.="<option value='".$row."'>Eksternal</option>";
	}
}

$optPeriod = array();
for($i=date('Y')-1; $i<=date('Y')+1; $i++) {
	for($j=1; $j<=12; $j++) {
		$tmpPeriod = $i.'-'.str_pad($j,2,'0',STR_PAD_LEFT);
		$optPeriod[$tmpPeriod] = $tmpPeriod;
	}
}
$currPeriod = $_SESSION['org']['period']['tahun'].'-'.$_SESSION['org']['period']['bulan'];

echo"<fieldset style=width:350px;float:left;>
     <legend>".$_SESSION['lang']['hargabibit'].' Setup'."</legend>
	 <input type=hidden id='currPeriod' value='".$currPeriod."'>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['regional']."</td>
	   <td><select id=regional >".$optRegional."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['status']."</td>
	   <td><select id=status >".$optStatus."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['periode']."</td>
	   <td>".makeElement('periode','select',$currPeriod,array('style'=>'width:95px'),$optPeriod)."</td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['hargasatuan']."</td>
	   <td><input type='text' id='hargasatuan' class='myinputtextnumber' style='width:95px' onkeypress='return angka_doang(event)' value='0' /></td>
	 </tr>
	 <tr>
		<td></td>
		<td>
			<input type=hidden value=insert id=proses>
			<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	</table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>