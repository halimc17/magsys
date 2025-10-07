<?//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?





$optOrg="";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$vOrg = "";
}
else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $vOrg = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
}

else{
	$vOrg = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$vOrg." order by namaorganisasi asc ";	
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optPer="";
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$nPer=mysql_query($iPer) or die(mysql_error($conn));
while($dPer=mysql_fetch_assoc($nPer))
{
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}

$optTp="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTp="select * from ".$dbname.".sdm_ho_component order by name asc";
$nTp=mysql_query($iTp) or die(mysql_error($conn));
while($dTp=mysql_fetch_assoc($nTp))
{
	$optTp.="<option value=".$dTp['id'].">".$dTp['name']."</option>";
}

$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id between 1 and 6 ";
$nTipe=  mysql_query($iTipe) or die (mysql_error($conn));
while($dTipe=   mysql_fetch_assoc($nTipe))
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}

$optJab="<option value=''>".$_SESSION['lang']['all']."</option>";
$iJab="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$nJab=  mysql_query($iJab) or die (mysql_error($conn));
while($dJab=   mysql_fetch_assoc($nJab))
{
    $optJab.="<option value=".$dJab['kodejabatan'].">".$dJab['namajabatan']."</option>";
}

$frm[0]='';
$frm[1]='';

OPEN_BOX('',"<b>Rekap Gaji</b><br /><br />");

$arr="##kdorg##per1##per2##kom##tipekar";	
$frm[0].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per1 style=\"width:150px;\">".$optPer."</select> S/D 
                        <select id=per2 style=\"width:150px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['komponenpayroll']."</td>
                    <td>:</td>
                    <td><select id=kom style=\"width:150px;\">".$optTp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekar style=\"width:150px;\">".$optTipe."</select></td>
                </tr>

                <tr>
                    <td colspan=4>
                    <button onclick=zPreview('sdm_slave_2totalkomponengaji','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2totalkomponengaji.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>

$arrv="##kdorgv##per1v##per2v##komv##tipekarv##jabv";
$frm[1].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorgv style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per1v style=\"width:150px;\">".$optPer."</select> S/D 
                        <select id=per2v style=\"width:150px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['komponenpayroll']."</td>
                    <td>:</td>
                    <td><select id=komv style=\"width:150px;\">".$optTp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekarv style=\"width:150px;\">".$optTipe."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jabatan']."</td>
                    <td>:</td>
                    <td><select id=jabv style=\"width:150px;\">".$optJab."</select></td>
                </tr>

                <tr>
                    <td colspan=4>
                    <button onclick=zPreview('sdm_slave_2totalkomponengajiv','".$arrv."','printContainerv') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2totalkomponengajiv.php','".$arrv."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainerv' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";


$hfrm[0]='Detail Perkaryawan';
$hfrm[1]='Rekap Jabatan';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();








?>