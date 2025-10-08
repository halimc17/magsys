<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
$arr0="##tanggal"; 

$title[0]=$_SESSION['lang']['laporan']." LPJ RAWAT KUD ";
$title[1]=$_SESSION['lang']['laporan']." LPJ PANEN KUD ";

$optPT = "<option value=''>".$_SESSION['lang']['all']."</option>";
$qPT = "select * from ".$dbname.".organisasi where tipe = 'PT' order by kodeorganisasi ";
$nPT=  mysql_query($qPT) or die (mysql_errno($conn));
while($dPt=mysql_fetch_assoc($nPT))
{
   $optPT.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPer="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$nPer=  mysql_query($iPer) or die (mysql_errno($conn));
while($dPer=mysql_fetch_assoc($nPer))
{
    $optPeriode.="<option value='".$dPer['periode']."'>".$dPer['periode']."</option>";
}

$optKud = "<option value=''>".$_SESSION['lang']['all']."</option>";
$sKud = "select * from ".$dbname.".log_5supplier where kodekelompok = 'S004'";
$qKud = mysql_query($sKud) or die(mysql_errno($conn));
while($rKud = mysql_fetch_assoc($qKud)){
	$optKud .= "<option value='".$rKud['supplierid']."'>".$rKud['namasupplier']."</option>";
}

$arr0="##kodept0##periode0##namakud0";
$arr1="##kodept1##periode1##namakud1";

?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<!-- <script type="text/javascript" src="js/kebun_2LPJNoe.js"></script> -->
<script>
function preview(tab){
	 
    if(tab==0){
        kodept=document.getElementById('kodept0').options[document.getElementById('kodept0').selectedIndex].value;        
		periode=document.getElementById('periode0').options[document.getElementById('periode0').selectedIndex].value; 
		namakud=document.getElementById('namakud0').options[document.getElementById('namakud0').selectedIndex].value; 
        param='kodept='+kodept+'&periode='+periode+'&namakud='+namakud+'&proses=preview';
		tujuan='kebun_slave_2LPJNoe_rawat.php';
    }
    if(tab==1){
        kodept=document.getElementById('kodept1').options[document.getElementById('kodept1').selectedIndex].value;        
		periode=document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value; 
		namakud=document.getElementById('namakud1').options[document.getElementById('namakud1').selectedIndex].value; 
        param='kodept='+kodept+'&periode='+periode+'&namakud='+namakud+'&proses=preview';
		tujuan='kebun_slave_2LPJNoe_panen.php';
    }
    

   
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    cor=con.responseText.split("####");
                    if(tab==0){
                        document.getElementById('printContainer0').innerHTML=con.responseText;                   
                    }
                    if(tab==1){
                        document.getElementById('printContainer1').innerHTML=con.responseText;                  
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
/*
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";

echo"<tr><td>PT</td>";
echo"<td><select id=kodept style=width:150px;>".$optPT."</select></td>";
echo"</tr>";

echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";

echo"<tr><td>".$_SESSION['lang']['namakud']."</td>
          <td><select id=namakud style=\"width:150px;\">".$optKud."</select></td>
          </tr>";
		  
echo"<tr height=\"1\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">
         <button onclick=\"preview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button> 
         <button onclick=\"zExcel(event,'kebun_slave_2LPJNoe.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>

<div id='printContainer' style='overflow:auto;height:250px;max-width:1220px;'>
</div>
</fieldset>";
*/
$frm[0]="<fieldset style=\"float: left;\">
		 <legend><b>".$title[0]."</b></legend>
		 <table cellspacing=\"1\" border=\"0\" >
		   <tr>
		     <td>PT</td>
		     <td><select id=kodept0 style=width:150px;>".$optPT."</select></td>
           </tr>
           <tr>
		     <td>".$_SESSION['lang']['periode']."</td>
			 <td><select id=periode0 style=width:150px;>".$optPeriode."</select></td>
           </tr>
		   <tr>
		     <td>".$_SESSION['lang']['namakud']."</td>
			 <td><select id=namakud0 style=\"width:150px;\">".$optKud."</select></td>
		   </tr>
		   <tr height=\"1\">
		     <td colspan=\"2\">&nbsp;</td>
           </tr>
		   <tr>
			<td colspan=\"2\">
				  <button onclick=\"preview(0)\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
                  <button onclick=\"zExcel(event,'kebun_slave_2LPJNoe_rawat.php','".$arr0."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
			</td>    
		   </tr>    
		</table>
		</fieldset>

		<div style=\"margin-bottom: 30px;\">
		</div>
		<fieldset style='clear:both'><legend><b>Print Area</b></legend>

		<div id='printContainer0' style='overflow:auto;height:250px;max-width:1220px;'>
		</div>
		</fieldset>
";

$frm[1]="<fieldset style=\"float: left;\">
		 <legend><b>".$title[1]."</b></legend>
		 <table cellspacing=\"1\" border=\"0\" >
		   <tr>
		     <td>PT</td>
		     <td><select id=kodept1 style=width:150px;>".$optPT."</select></td>
           </tr>
           <tr>
		     <td>".$_SESSION['lang']['periode']."</td>
			 <td><select id=periode1 style=width:150px;>".$optPeriode."</select></td>
           </tr>
		   <tr>
		     <td>".$_SESSION['lang']['namakud']."</td>
			 <td><select id=namakud1 style=\"width:150px;\">".$optKud."</select></td>
		   </tr>
		   <tr height=\"1\">
		     <td colspan=\"2\">&nbsp;</td>
           </tr>
		   <tr>
			<td colspan=\"2\">
				 <button onclick=\"preview(1)\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
                  <button onclick=\"zExcel(event,'kebun_slave_2LPJNoe_panen.php','".$arr1."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
			</td>    
		   </tr>    
		</table>
		</fieldset>

		<div style=\"margin-bottom: 30px;\">
		</div>
		<fieldset style='clear:both'><legend><b>Print Area</b></legend>

		<div id='printContainer1' style='overflow:auto;height:250px;max-width:1220px;'>
		</div>
		</fieldset>
";

$hfrm[0]=$title[0];
$hfrm[1]=$title[1];
drawTab('FRM',$hfrm,$frm,200,1100);

CLOSE_BOX();
echo close_body();
?>