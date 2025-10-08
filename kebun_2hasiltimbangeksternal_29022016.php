<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
}else{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$arr="##kbnId##afdId##tgl1##tgl2";

?>
<script language=javascript src=js/zTools.js></script>
<script>
function getKode()
{
	tipeIntex=document.getElementById('kbnId').options[document.getElementById('kbnId').selectedIndex].value;
	param='kbnId='+tipeIntex+'&proses=getKodeAfd';
	tujuan="kebun_slave_2hasiltimbanganeksternal.php";
    post_response_text(tujuan, param, respon);
	 function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                  	document.getElementById('afdId').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<script language=javascript src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['keeksternal']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td><select id="kbnId" name="kbnId" onchange="getKode()" style="width:169px"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['afdeling']?></label></td><td><select id="afdId" name="afdId"  style="width:169px"><?php echo $optAfd?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td><input type="text" class="myinputtext" id="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" /> s.d <input type="text" class="myinputtext" id="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" />
</td></tr>
<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" align=center>
<button onclick="zPreview('kebun_slave_2hasiltimbanganeksternal','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
<button onclick="zExcel(event,'kebun_slave_2hasiltimbanganeksternal.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>       

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>