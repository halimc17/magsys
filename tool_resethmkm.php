<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/tool_resethmkm.js'></script>
<?php
$arr="##kodevhc##kmhmakhir##method";
include('master_mainMenu.php');
OPEN_BOX();

$optVhc = makeOption($dbname,'vhc_5master','kodevhc,kodevhc');

// KM/HM Akhir
$optKmAkhir = makeOption($dbname,'vhc_kmhm_track','kodevhc,kmhmakhir');
setIt($optKmAkhir[key($optVhc)],0);

echo"<table><tr><td valign=top><fieldset style=width:350px;>
	<legend>Reset HM/KM</legend>
	<table>
	<tr>
		<td>".$_SESSION['lang']['kodevhc']."</td>
		<td>".makeElement('kodevhc','select',"",array('onchange'=>'getKmHmAkhir()'),$optVhc)."</td>
	</tr>
    <tr>
		<td>".$_SESSION['lang']['vhc_kmhm_akhir']."</td>
		<td>".makeElement('kmhmakhir','textnum',$optKmAkhir[key($optVhc)])."</td>
	</tr>
	</table>
	<button class=mybutton id=tmblDt onclick=resetDt()>".$_SESSION['lang']['proses']."</button>
	</fieldset><input type=hidden id=method value=getData />";
echo"</td></tr></table>";

CLOSE_BOX();

echo"<div id=listData style=display:none>";
OPEN_BOX();
echo"<fieldset style=height:550px;width:650px;><legend>".$_SESSION['lang']['list']."</legend>
    <div id=container style=overflow:auto;height:450px;width:650px;>";
echo"</div></fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>