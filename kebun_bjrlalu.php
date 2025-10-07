<?//BJR
//-----
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_bjrlalu.js'></script>

<?
$lksi=substr($_SESSION['empl']['lokasitugas'],0,4);
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '%E' and detail='1'";
}elseif(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
	$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '%E' and detail='1' and induk='".$_SESSION['empl']['induk']."'";
}else{
	$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$lksi."'";
}
$qKbn=mysql_query($sKbn) or die(mysql_error());
$optOrg="";
while($rKbn=mysql_fetch_assoc($qKbn))
{
    $optOrg.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
}

//$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji where sudahproses='0' and kodeorg='".$lksi."' order by periode limit 1";
$optper="";
$sql="SELECT distinct periode FROM ".$dbname.".setup_periodeakuntansi where tutupbuku='0' and kodeorg='".$lksi."' order by periode limit 1";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry)){
	$optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}

include('master_mainMenu.php');
OPEN_BOX();
$arr="##unit##per";	

echo "<fieldset><legend><b>Proses BJR</b></legend>
	<table>
		<tr>
			<td>Unit</td>
			<td>:</td>
	        <td><select id=unit>".$optOrg."</select></td>
	    </tr>
		<tr>
			<td>Periode</td>
	        <td>:</td>
		    <td><select id=per>".$optper."</select></td>
	    </tr>";
echo "	<tr>
			<td colspan=100>&nbsp;</td>
		</tr>
		<tr>
			<td colspan=100>
				<button onclick=zPreview('kebun_slave_bjrlalu','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
				<button onclick=zExcel(event,'kebun_slave_bjrlalu.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
				<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

echo "<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
		<div id='printContainer'></div>
	</fieldset>";// style='overflow:auto;height:350px;max-width:1220px';
CLOSE_BOX();
echo close_body();
?>
