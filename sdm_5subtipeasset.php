<?php
require_once('master_validation.php');
require_once('lib/zMysql.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language="JavaScript1.2" src="js/zTools.js"></script>
<script language=javascript1.2 src=js/subtipeasset.js></script>
<?
include('master_mainMenu.php');

### BEGIN GET TYPE ASSET ###
$str="select kodetipe, namatipe from ".$dbname.".sdm_5tipeasset";
$res=mysql_query($str);
$optTypeAsset="";
while($bar=mysql_fetch_object($res))
{
    $namatipe[$bar->kodetipe]=$bar->namatipe;
    $optTypeAsset.="<option value='".$bar->kodetipe."'>".$bar->kodetipe." - ".$bar->namatipe."</option>";
}
### END GET TYPE ASSET ###

OPEN_BOX('',$_SESSION['lang']['subtipeasset']);

echo"<p /><fieldset style='width:600px;'><table>
	 <tr><td>".$_SESSION['lang']['tipeasset']."</td><td><select id=tipeasset>".$optTypeAsset."</select></td></tr>
     <tr><td>".$_SESSION['lang']['kodesubasset']."</td><td><input type=text id=kodesubasset size=2 maxlength=2 onkeypress='return angka_doang(event)' \" class=myinputtext></td></tr>
	 <tr><td>".$_SESSION['lang']['namasubasset']."</td><td><input type=text id=namasubasset size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
	 <tr><td>".$_SESSION['lang']['umurpenyusutan']."</td><td style='vertical-align:bottom'><input type=text id=umurpenyusutan size=4 maxlength=5 onkeypress='return angka_doang(event)' \" class=myinputtext>&nbsp;".$_SESSION['lang']['bulan']."</td></tr>
     </table>
	 <input type=hidden id=save value=simpan>
	 <button class=mybutton onclick=simpanSubTipeAset()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelSubTipeAsset()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset><p />";

echo open_theme($_SESSION['lang']['availvhc']);
echo "<div>";
	$str1="select * from ".$dbname.".sdm_5subtipeasset 
		   order by namasub";
	$res1=mysql_query($str1);
	echo"<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:800px;'>
	     <thead>
		 <tr class=rowheader>
		 <td>".$_SESSION['lang']['kode']."</td>
         <td>".$_SESSION['lang']['namasubasset']."</td>
         <td style='width:100px;'>".$_SESSION['lang']['umurpenyusutan']." (".$_SESSION['lang']['bulan'].")</td>
		 <td>".$_SESSION['lang']['tipeasset']."</td>
		 <td>".$_SESSION['lang']['action']."</td></tr>
		 </thead>
		 <tbody id=container>";
	while($bar1=mysql_fetch_object($res1))
	{
		echo"<tr class=rowcontent>
		     <td style='text-align:center'>".$bar1->kodesub."</td>
             <td>".$bar1->namasub."</td>
             <td>".$bar1->umurpenyusutan."</td>
			 <td>".$namatipe[$bar1->kodetipe]."</td>
			 <td style='text-align:center'>
				<img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"editSubTipeAset('".$bar1->kodesub."','".$bar1->namasub."','".$bar1->umurpenyusutan."','".$bar1->kodetipe."');\">
			</td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();

CLOSE_BOX();
echo close_body();
?>