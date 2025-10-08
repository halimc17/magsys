<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$arr="##kdOrg##stasiun";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK') and detail='1' order by namaorganisasi asc ";	
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getStasiun()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getStasiun()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}else{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
    //$optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\">";
}
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optStasiun="<option value=''>".$_SESSION['lang']['all']."</option>";
$sStasiun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='STATION' and detail='1' order by namaorganisasi asc";
$qStasiun=mysql_query($sStasiun) or die(mysql_error($conn));
while($rStasiun=mysql_fetch_assoc($qStasiun))
{
	$optStasiun.="<option value=".$rStasiun['kodeorganisasi'].">".$rStasiun['namaorganisasi']."</option>";
}
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function getStasiun(){
    afd=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param='kdOrg='+afd+'&proses=getStasiun';
    tujuan='pabrik_lapmachinery_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('stasiun').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  	
}

function showpopup(kdorg,stasiun,kodemesin,type,ev){
   param='kdorg='+kdorg+'&stasiun='+stasiun+'&kodemesin='+kodemesin+'&type='+type;
   tujuan='pabrik_lapmachinery_showpopup.php'+"?"+param;
   width='800';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Data Machine '+kdorg+' '+stasiun+' '+kodemesin,content,width,height,ev); 
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1()
{
    document.getElementById('kdOrg').value='';
    document.getElementById('stasiun').value='';
    //document.getElementById('periode').value='';
}
</script>

<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['laporan']." Data Machinery ";?></b></legend>
<table cellspacing="1" border="0" >
<tr>
	<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
	<td><?php echo $optOrg;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['station'];?></label></td>
	<td><select id="stasiun" name="stasiun"><?php echo $optStasiun;?>
	</select></td>
</tr>
<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">
	<button onclick="zPreview('pabrik_lapmachinery_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<button onclick="zExcel(event,'pabrik_lapmachinery_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
</td></tr>
</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:375px;max-width:1235px'></div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>
