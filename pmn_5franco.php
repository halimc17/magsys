<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src=js/pmn_5franco.js></script>
<?
$arr="##idFranco##nmFranco##almtFranco##jual##method";
include('master_mainMenu.php');
OPEN_BOX();

//Loco, Franco, FOB
$optJual="<option value='loco'>Loco</option>";
$optJual.="<option value='franco'>Franco</option>";		
$optJual.="<option value='fob'>FOB</option>";


echo"<fieldset>
     <legend>Master Tempat penyerahan Penjualan</legend>
	 <table>
	 <tr>
	   <td>Nama tempat</td>
	   <td><input type=text class=myinputtext id=nmFranco name=nmFranco onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" maxlength=100 /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['alamat']."</td>
	   <td><textarea id=almtFranco name=almtFranco></textarea></td>
	 </tr>
         <tr>
            <td>".$_SESSION['lang']['penjualan']."</td>
           
            <td><select id=jual style=\"width:125px;\">".$optJual."</select></td>
         </tr>
	  <tr>
	   <td>".$_SESSION['lang']['status']."</td>
	   <td><input type='checkbox' id=statFr name=statFr />".$_SESSION['lang']['tidakaktif']."</td>
	 </tr> 
	 </table>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco('pmn_slave_5franco','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();
$str="select * from ".$dbname.".setup_franco order by id_franco desc";
$res=mysql_query($str);
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>Nama Tempat</td>
	   <td>".$_SESSION['lang']['alamat']."</td>
	   <td>Franco Penjualan</td>
	   <td>".$_SESSION['lang']['status']."</td>
	   <td>Action</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";
//$no=0;	 
//while($bar=mysql_fetch_object($res))
//{
//  $no+=1;	
//  echo"<tr class=rowcontent>
//	  <td>No</td>
//	   <td>Nama Franco</td>
//	   <td>".$_SESSION['lang']['alamat']."</td>
//	   <td>Kontak Person</td>
//	   <td>".$_SESSION['lang']['telp']."</td>
//	   <td>".$_SESSION['lang']['status']."</td>
//	   <td>
//		      <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kelompok."','".$bar->kelompokbiaya."','".$bar->noakun."');\"> 
//			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKelompok('".$bar->kode."','".$bar->kelompok."');\">
//		  </td>
//	   
//	  </tr>";	
//}     
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>