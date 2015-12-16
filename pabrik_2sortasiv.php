	<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script language=javascript>


	function batal()
	{
		document.getElementById('kdorg').value='';
		document.getElementById('sup').value='';
		document.getElementById('tgl2').value='';	
		document.getElementById('tgl1').value='';
		document.getElementById('printContainer').innerHTML='';	
	}


</script>

<?


// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' ORDER BY kodeorganisasi";
// $qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
// while ($data=mysql_fetch_assoc($qry))
// {
    // $optOrg.="<option value='".$data['namaorganisasi']."'>";
    // //$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        
        
// }                        



$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodetimbangan,namasupplier FROM ".$dbname.".log_5supplier where left(kodetimbangan,1)='5' ORDER BY namasupplier";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
    $optSup.="<option value=".$data['kodetimbangan'].">".$data['namasupplier']."</option>";
   
}
                        
                        
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' ORDER BY kodeorganisasi";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
    $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}                       
			
?>


<?
include('master_mainMenu.php');
OPEN_BOX();




// <td><input type=text class='myinputtext' placeholder='Pilih Pabik' name=location list=urls id=kdorg><datalist id=urls>".$optOrg."</datalist></td>
//$arr="##tgl1##tgl2";	
$arr="##kdorg##sup##tgl1##tgl2";
echo "
<fieldset style='float:left;'><legend><b>Grading TBS External</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=kdorg style=\"width:155px;\" >".$optOrg."</select></td>
        </tr>
        
        <tr>
            <td>".$_SESSION['lang']['supplier']."</td>
            <td>:</td>
            <td><select id=sup style=\"width:155px;\" >".$optSup."</select></td>
        </tr>
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('pabrik_slave_2sortasiv','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2sortasiv.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();




?>