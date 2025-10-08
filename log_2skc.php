<?php //@Copy nangkoelframework
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
<script language=javascript src='js/kebun_3updkg.js'></script>

<?php

$frm[0]='';
$frm[1]='';

### Get Value Enum Suppllier
$optTipeSup="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrTipeSup=getEnum($dbname,'log_5klsupplier','tipe');
foreach($arrTipeSup as $kei=>$fal)
{
	$optTipeSup.="<option value='".$kei."'>".ucfirst(strtolower($fal))."</option>";
}

### Get List Kelompok
$str="select distinct(kelompok) from ".$dbname.".log_5klsupplier";
$res=mysql_query($str);
$optKelompok="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($res))
{
	$optKelompok.="<option value='".$bar->kelompok."'>".$bar->kelompok."</option>";
}

$buyer="<option value=''>".$_SESSION['lang']['all']."</option>";
$iBuy="select * from ".$dbname.".pmn_4customer order by namacustomer asc";
$nBuy=  mysql_query($iBuy) or die (mysql_error($conn));
while($dBuy=  mysql_fetch_assoc($nBuy))
{
    $buyer.="<option value='".$dBuy['kodecustomer']."'>".$dBuy['namacustomer']."</option>";
}  

#komoditi
$komoditi="<option value=''>".$_SESSION['lang']['all']."</option>";
$iBrg="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400' order by namabarang asc ";
$nBrg=  mysql_query($iBrg) or die (mysql_error($conn));
while($dBrg=  mysql_fetch_assoc($nBrg))
{
    $komoditi.="<option value='".$dBrg['kodebarang']."'>".$dBrg['namabarang']."</option>";
}  

include('master_mainMenu.php');


OPEN_BOX();
$arr="##namasupplier##tipe##kdkelompok";
$frm[0].="<br /><fieldset style=width:250px;float:left;>
	<legend>".$_SESSION['lang']['find']."</legend>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['namasupplier']."</td><td><input type=text class=myinputtext id=namasupplier onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45 placeholder='".$_SESSION['lang']['all']."'></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['Type']."</td><td><select id=tipe>".$optTipeSup."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodekelompok']."</td><td><select id=kdkelompok>".$optKelompok."</select></td>
		</tr>
		<tr>
			<td></td>
				<td><button onclick=\"zPreview('log_slave_2daftarsupplier','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
					<button onclick=\"zExcel(event,'log_slave_2daftarsupplier.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['excel']."</button>
				</td>
		</tr>
		</table>
	</fieldset>
	<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
	</div></fieldset>";


/*$frm[1]='';
$frm[2]='';

$kelSup.="<option value=''>".$_SESSION['lang']['all']."</option>";
$iSup="SELECT * FROM ".$dbname.".log_5klsupplier where tipe='SUPPLIER'";
$nSup = mysql_query($iSup) or die ("SQL ERR : ".mysql_error($conn));
while ($dSup=mysql_fetch_assoc($nSup))
{
        $kelSup.="<option value='".$dSup['kode']."'>".$dSup['kelompok']."</option>";
}			

$stSup.="<option value=''>".$_SESSION['lang']['all']."</option>";
$stSup.="<option value='0'>".$_SESSION['lang']['tidakaktif']."</option>";
$stSup.="<option value='1'>".$_SESSION['lang']['aktif']."</option>";


########################################################################################################

$kelKon.="<option value=''>".$_SESSION['lang']['all']."</option>";
$iKon="SELECT * FROM ".$dbname.".log_5klsupplier where tipe='KONTRAKTOR'";
$nKon = mysql_query($iKon) or die ("SQL ERR : ".mysql_error($conn));
while ($dKon=mysql_fetch_assoc($nKon))
{
        $kelKon.="<option value='".$dKon['kode']."'>".$dKon['kelompok']."</option>";
}			

$stKon.="<option value=''>".$_SESSION['lang']['all']."</option>";
$stKon.="<option value='0'>".$_SESSION['lang']['tidakaktif']."</option>";
$stKon.="<option value='1'>".$_SESSION['lang']['aktif']."</option>";




$buyer="<option value=''>".$_SESSION['lang']['all']."</option>";
$iBuy="select * from ".$dbname.".pmn_4customer order by namacustomer asc";
$nBuy=  mysql_query($iBuy) or die (mysql_error($conn));
while($dBuy=  mysql_fetch_assoc($nBuy))
{
    $buyer.="<option value='".$dBuy['kodecustomer']."'>".$dBuy['namacustomer']."</option>";
}  

#komoditi
$komoditi="<option value=''>".$_SESSION['lang']['all']."</option>";
$iBrg="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400' order by namabarang asc ";
$nBrg=  mysql_query($iBrg) or die (mysql_error($conn));
while($dBrg=  mysql_fetch_assoc($nBrg))
{
    $komoditi.="<option value='".$dBrg['kodebarang']."'>".$dBrg['namabarang']."</option>";
}  


include('master_mainMenu.php');


OPEN_BOX();
$arrsup="##kelsup##stsup";
$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>";


$frm[0].="<tr>
            <td>Kelompok</td>
            <td>:</td>
            <td><select id=kelsup style='width:200px;'>".$kelSup."</select></td>
	</tr>";

$frm[0].="<tr>
            <td>Status</td>
            <td>:</td>
            <td><select id=stsup style='width:200px;'>".$stSup."</select></td>
	</tr>";

	
$frm[0].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('log_slave_2skc','".$arrsup."','printContainerSup') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'log_slave_2skc.php','".$arrsup."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[0].="
<fieldset><legend><b>".$_SESSION['lang']['list']." Supplier</b></legend>
<div id='printContainerSup'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 



###########################
############################kontraktor
$arrkon="##kelkon##stkon";
$frm[1].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>";


$frm[1].="<tr>
            <td>Kelompok</td>
            <td>:</td>
            <td><select id=kelkon style='width:200px;'>".$kelKon."</select></td>
	</tr>";

$frm[1].="<tr>
            <td>Status</td>
            <td>:</td>
            <td><select id=stkon style='width:200px;'>".$stKon."</select></td>
	</tr>";

	
$frm[1].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('log_slave_2skckon','".$arrkon."','printContainerKon') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'log_slave_2skckon.php','".$arrkon."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[1].="
<fieldset><legend><b>".$_SESSION['lang']['list']." Kontraktor</b></legend>
<div id='printContainerKon'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 


*/




##############buyer
$arrbuy="##buy##brg";
$frm[1].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>";


$frm[1].="<tr>
            <td>Buyer</td>
            <td>:</td>
            <td><select id=buy style='width:200px;'>".$buyer."</select></td>
	</tr>";

$frm[1].="<tr>
            <td>Komoditi</td>
            <td>:</td>
            <td><select id=brg style='width:200px;'>".$komoditi."</select></td>
	</tr>";

	
$frm[1].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('log_slave_2skcbuy','".$arrbuy."','printContainerBuy') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'log_slave_2skcbuy.php','".$arrbuy."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[1].="
<fieldset><legend><b>".$_SESSION['lang']['list']." Buyer</b></legend>
<div id='printContainerBuy'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 





/*$hfrm[0]='Supplier';
$hfrm[1]='Kontraktor';
$hfrm[2]='Buyer';
 */

$hfrm[0]='Supplier, Kontraktor, dan Transportir';
$hfrm[1]='Buyer';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();


?>
