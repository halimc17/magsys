<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zMysql.php');
echo open_body();
?>
<script language='JavaScript1.2' src='js/supplier.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<u><b><font face=Verdana size=4 color=#000080>'.$_SESSION['lang']['data'].' '.$_SESSION['lang']['supplier'].' </font></b></u>');

### Get Value Enum Suppllier
$optTipeSup='';
$arrTipeSup=getEnum($dbname,'log_5klsupplier','tipe');
foreach($arrTipeSup as $kei=>$fal)
{
	$optTipeSup.="<option value='".$kei."'>".ucfirst(strtolower($fal))."</option>";
}

### Get Value Enum Status Internal/Eksternal
$optStatusIntExt='';
$arrStatusIntExt=getEnum($dbname,'log_5supplier','statusintext');
foreach($arrStatusIntExt as $kei=>$fal)
{
	$optStatusIntExt.="<option value='".$kei."'>".ucfirst(strtolower($fal))."</option>";
}

 echo"<fieldset>
      <legend>Input ".$_SESSION['lang']['supplier']."/".$_SESSION['lang']['kontraktor']."</legend>
	  <table>
	  <tr>
	     <td>".$_SESSION['lang']['Type']."</td><td><select id=tipe onchange=\"getKelompokSupplier(this.options[this.selectedIndex].value)\"><option value=''></option>".$optTipeSup."</select></td>
	     <td>".$_SESSION['lang']['telp']."</td><td><input type=text class=myinputtext id=telp onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>
	  <tr>
	      <td>".$_SESSION['lang']['kodekelompok']."</td><td><select id=kdkelompok onchange=\"getSupplierNumber(this.options[this.selectedIndex].value,this.options[this.selectedIndex].text);\"><option value=''></option></select></td>
          <td>".$_SESSION['lang']['fax']."</td><td><input type=text class=myinputtext id=fax onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>	  
 	  </tr>
	  <tr>
	      <td>Id.".$_SESSION['lang']['supplier']."/".$_SESSION['lang']['kontraktor']."</td><td><input type=text class=myinputtext disabled id=idsupplier></td>
	      <td>".$_SESSION['lang']['email']."</td><td><input type=text class=myinputtext id=email onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=45></td>
	  </tr>
	   <tr>
		  <td>".$_SESSION['lang']['namasupplier']."</td><td><input type=text class=myinputtext id=namasupplier onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45><select style='display:none' id=statusintext>".$optStatusIntExt."</select></td>
	      <td>".$_SESSION['lang']['npwp']."</td><td><input type=text class=myinputtext id=npwp onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>
	  <tr>
	      <td>".$_SESSION['lang']['alamat']."</td><td><input type=text class=myinputtext id=alamat onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=100></td>
	      <td>".$_SESSION['lang']['cperson']."</td><td><input type=text class=myinputtext id=cperson onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>
	  <tr>
	      <td>".$_SESSION['lang']['kota']."</td><td><input type=text class=myinputtext id=kota onkeypress=\"return tanpa_kutip(event);\" size=20 maxlength=30></td>
	      <td>".$_SESSION['lang']['plafon']."</td><td><input type=text  onblur=\"change_number(this);\"class=myinputtextnumber id=plafon onkeypress=\"return angka_doang(event);\" size=15 maxlength=15 value=0></td>
	  </tr>
	  </table>
	  <input type=hidden id=method value=insert>
	<button class=mybutton onclick=saveSupplier()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancelSupplier()>".$_SESSION['lang']['cancel']."</button>	  
	  </fieldset>";
?>
<?php
CLOSE_BOX();
OPEN_BOX('',$_SESSION['lang']['plafon'].': <span id=captiontipe></span> '.$_SESSION['lang']['namakelompok'].':<span id=captionkelompok></span>');
	     
	echo"<div style='width=100%; height:250px;overflow:scroll'>
	     <table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=header>
	         <td align=center>".$_SESSION['lang']['kodekelompok']."</td>
		 <td align=center>Id.".$_SESSION['lang']['supplier']."</td>
		 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
		 <td align=center>".$_SESSION['lang']['alamat']."</td>
		 <td align=center>".$_SESSION['lang']['cperson']."</td>
		 <td align=center>".$_SESSION['lang']['kota']."</td>
		 <td align=center>".$_SESSION['lang']['telp']."</td>		 
		 <td align=center>".$_SESSION['lang']['fax']."</td>		 
		 <td align=center>".$_SESSION['lang']['email']."</td>		 
		 <td align=center>".$_SESSION['lang']['npwp']."</td>	 
		 <td align=center>".$_SESSION['lang']['plafon']."</td>
                 <td  align=center colspan=2>".$_SESSION['lang']['action']."</td>
		 </tr>
		 <tbody id=container>
		 </tbody>
		 <tfoot></tfoot>
		 </table>
		 </div>
		 ";

CLOSE_BOX();
echo close_body();
?>