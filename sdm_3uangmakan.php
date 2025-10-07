<?//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();


?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_3uangmakan.js'></script>

<?

$frm[0]='';
$frm[1]='';

$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji order by periode desc limit 10";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}			


if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'"
        . "and kodeorganisasi not like '%HO%' ";
}
else
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'"
        . "and kodeorganisasi not like '%HO%' ";
} 
 $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}


$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','3') ";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','2','3') ";
$nTipe=  mysql_query($iTipe) or die (mysql_errno($conn));
while($dTipe=   mysql_fetch_assoc($nTipe))
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}

include('master_mainMenu.php');

$frm[0]='';
$frm[1]='';

OPEN_BOX();
$arr="##per##unit##tipe##rupiah";

$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>Periode</td>
		<td>:</td>
		<td><select id=per style='width:125px;'>".$optper."</select></td>
	</tr>";

$frm[0].="  <tr>
            <td>Unit</td>
            <td>:</td>
            <td><select id=unit onchange=uang() style='width:125px;'>".$optOrg."</select></td>
	</tr>";

$frm[0].="  <tr>
            <td>Tipe Karyawan</td>
            <td>:</td>
            <td><select id=tipe style='width:125px;'>".$optTipe."</select></td>
	</tr>";	
	
$frm[0].="  <tr>
            <td>Rupiah/Kehadiran</td>
            <td>:</td>
            <td><input type=text disabled id=rupiah size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=6 style=\"width:125px;\"></td>
	</tr>";



	
$frm[0].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3uangmakan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[0].="
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 


#############f1
#############
##################


$arrtjabsen="##pertjabsen##unittjabsen##tipetjabsen";
$frm[1].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>Periode</td>
		<td>:</td>
		<td><select id=pertjabsen style='width:125px;'>".$optper."</select></td>
	</tr>";

$frm[1].="  <tr>
            <td>Unit</td>
            <td>:</td>
            <td><select id=unittjabsen  style='width:125px;'>".$optOrg."</select></td>
	</tr>";

$frm[1].="  <tr>
            <td>Tipe Karyawan</td>
            <td>:</td>
            <td><select id=tipetjabsen style='width:125px;'>".$optTipe."</select></td>
	</tr>";
	


	
$frm[1].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3tjabsen','".$arrtjabsen."','printContainertjabsen') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainertjabsen'>
</div></fieldset>";


###########################
####################################################
##f2

$arrtpremitetap="##perpremitetap##unitpremitetap##tipepremitetap";

$frm[2].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>Periode</td>
		<td>:</td>
		<td><select id=perpremitetap style='width:125px;'>".$optper."</select></td>
	</tr>";

$frm[2].="  <tr>
            <td>Unit</td>
            <td>:</td>
            <td><select id=unitpremitetap  style='width:125px;'>".$optOrg."</select></td>
	</tr>";

$frm[2].="  <tr>
            <td>Tipe Karyawan</td>
            <td>:</td>
            <td><select id=tipepremitetap style='width:125px;'>".$optTipe."</select></td>
	</tr>";	
	


	
$frm[2].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3premitetap','".$arrtpremitetap."','printContainerpremitetap') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
		
		<button onclick=batalpremitetap() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[2].="
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerpremitetap'>
</div></fieldset>";


$hfrm[0]='Uang Makan';
$hfrm[1]='Tunjangan Absensi';
$hfrm[2]='Premi Tetap';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();


?>
