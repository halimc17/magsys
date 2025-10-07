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
<script language=javascript src='js/sdm_3tunjangan.js'></script>



<?
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji order by periode desc limit 10";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}	
 $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}


$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','2','3','4','5','6') ";
$nTipe=  mysql_query($iTipe) or die (mysql_errno($conn));
while($dTipe=   mysql_fetch_assoc($nTipe))
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}



$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iJenis="select * from ".$dbname.".sdm_ho_component where id in ('28','46','47') ";
$nJenis=  mysql_query($iJenis) or die (mysql_errno($conn));
while($dJenis=   mysql_fetch_assoc($nJenis))
{
    $optJenis.="<option value=".$dJenis['id'].">".$dJenis['name']."</option>";
}


$optGaji="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iGaji="select distinct(tahun) as tahun from ".$dbname.".sdm_5gajipokok where length(tahun)=4";
$nGaji=  mysql_query($iGaji) or die (mysql_errno($conn));
while($dGaji=   mysql_fetch_assoc($nGaji))
{
    $optGaji.="<option value=".$dGaji['tahun'].">".$dGaji['tahun']."</option>";
}

 $optstkawin="<option value=''>Seluruhnya</option>";
$arrsstk=getEnum($dbname,'datakaryawan','statusperkawinan');
foreach($arrsstk as $kei=>$fal)
{
        if($_SESSION['language']=='EN' && $fal=='Menikah')
            $fal='Married';
        if($_SESSION['language']=='EN' && $fal=='Janda')
               $fal='Widow';       
        if($_SESSION['language']=='EN' && $fal=='Duda')
               $fal='Widower';      
        if($_SESSION['language']=='EN' && $fal=='Lajang')
               $fal='Single';              
        $optstkawin.="<option value='".$kei."'>".$fal."</option>";
} 

?>



<?
include('master_mainMenu.php');
OPEN_BOX();
$arr="##unit##per##jenis##tipe##tahun##tgl##pengali##makan##kawin";	

echo "<fieldset><legend><b>Proses Tunjangan</b></legend>
<table>
    <tr>
        <td>Unit</td>
        <td>:</td>
        <td><select id=unit onchange=uang() style='width:125px;'>".$optOrg."</select></td>
    </tr>
    <tr>
        <td>Periode</td>
        <td>:</td>
        <td><select id=per style='width:125px;'>".$optper."</select></td>
    </tr>
    <tr>
        <td>Jenis</td>
        <td>:</td>
        <td><select id=jenis onchange=hide() style='width:125px;'>".$optJenis."</select></td>
    </tr>
    <tr>
        <td>Tipe Karyawan</td>
        <td>:</td>
        <td><select id=tipe style='width:125px;'>".$optTipe."</select></td>
    </tr>
    <tr>
        <td>Basis Gaji Tahunan</td>
        <td>:</td>
        <td><select id=tahun style='width:125px;'>".$optGaji."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tanggal']." Cut Off</td>
        <td>:</td>
        <td><input type=text class=myinputtext  id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:125px;\"/></td>
    </tr>
    <tr>
        <td>Pengali</td>
        <td>:</td>
        <td><input type=text id=pengali value=1 disabled size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 style=\"width:125px;\"></td>
    </tr>
    <tr>
        <td>Uang Makan</td>
        <td>:</td>
        <td><input type=text id=makan value=0 size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 style=\"width:125px;\"></td>
    </tr>
    
    <tr>
        <td>Status Kawin</td>
        <td>:</td>
        <td><select id=kawin style='width:125px;'>".$optstkawin."</select></td>
    </tr>
    


    ";



	
echo "	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3tunjangan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_3tunjangan.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



echo "
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

CLOSE_BOX();
echo close_body();




?>