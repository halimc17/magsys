<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';

?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/bgt_kapital.js"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
</script>
<?php
$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$sOrg2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
$qOrg2=mysql_query($sOrg2) or die(mysql_error());
while($rOrg2=mysql_fetch_assoc($qOrg2))
{
	$optBlok.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['namaorganisasi']."</option>";
}
$optJns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['language']=='EN'){
    $dd='namatipe1 as namatipe';
}else{
    $dd='namatipe as namatipe';
}
$sJns="select kodetipe,".$dd." from ".$dbname.".sdm_5tipeasset order by namatipe";
$qJns=mysql_query($sJns) or die(mysql_error($conn));
while($rJns=mysql_fetch_assoc($qJns))
{
    $optJns.="<option value='".$rJns['kodetipe']."'>".$rJns['namatipe']."</option>";
}
$optlokasi="<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$res=mysql_query($str);

while($bar=mysql_fetch_object($res))
{
    $optlokasi.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

OPEN_BOX('',"<b>".$_SESSION['lang']['anggaran']." ". $_SESSION['lang']['kapital']."</b>");
echo"<br /><br /><fieldset style='float:left;'><legend>".$_SESSION['lang']['entryForm']."</legend> <table border=0 cellpadding=1 cellspacing=1>";
echo"<tr><td>".$_SESSION['lang']['budgetyear']."</td><td><input type='text' class='myinputtextnumber' id='tahunbudget' style='width:150px;' maxlength='4' onkeypress='return angka_doang(event)' /></td></tr>";
echo"<tr><td>".$_SESSION['lang']['unit']."</td><td><select style='width:150px;' id='kodeorg' >".$optBlok."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['jnsKapital']."</td><td><select style='width:150px;' id='jeniskapital'>".$optJns."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['lokasi']."</td><td><select id=lokasi>".$optlokasi."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['keterangan']."</td><td><input type='text' maxlength='45' onkeypress='return tanpa_kutip(event)' id='keterangan' class='myinputtext'style='width:150px;' /></td></tr>";
echo"<tr><td>".$_SESSION['lang']['jumlah']."</td><td><input type='text' class='myinputtextnumber' id='jumlah' style='width:150px;'  onkeypress='return angka_doang(event)' onblur='kaliKan()' /></td></tr>";
echo"<tr><td>".$_SESSION['lang']['hargasatuan']."</td><td><input type='text' class='myinputtextnumber' id='harga' style='width:150px;' onkeypress='return angka_doang(event)' onblur='kaliKan()' /></td></tr>";
echo"<tr><td>".$_SESSION['lang']['total']."</td><td><input type='text' class='myinputtextnumber' id='totalrp' style='width:150px;' readonly/></td></tr>";
echo"<tr><td colspan='2'><button class=\"mybutton\"  id=\"saveData\" onclick='saveHeader()'>".$_SESSION['lang']['save']."</button></td></tr>";
echo"</table></fieldset>";

CLOSE_BOX();

echo"<div id='formIsian' style='display:block;'>";
OPEN_BOX();
$frm[0].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
			<div id=container> 
				<script>loadData()</script>
			</div>
		</fieldset>";
		
$frm[1].="<fieldset><legend>".$_SESSION['lang']['sebaran']."</legend>
    <div id='detailDataSebaran'>";
$frm[1].="</div></fieldset>";
$str="select distinct tahunbudget from ".$dbname.".bgt_kapital where kodeunit='".$_SESSION['empl']['lokasitugas']."'
      and tutup=0
      order by tahunbudget desc";
$optThnTtp="";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $optThnTtp.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}

$frm[2].="<fieldset><legend>".$_SESSION['lang']['tutup']."</legend>
    <div><table><tr><td>".$_SESSION['lang']['budgetyear']."</td><td><select id='thnBudgetTutup' style='width:150px'>".$optThnTtp."</select></td></tr>";
$frm[2].="<tr><td colspan=2 align=center><button class=\"mybutton\"  id=\"saveData\" onclick='closeBudget()'>".$_SESSION['lang']['tutup']."</button></td></tr></table>";
$frm[2].="</div></fieldset>";
//========================
$hfrm[0]=$_SESSION['lang']['list'];
$hfrm[1]=$_SESSION['lang']['sebaran'];
$hfrm[2]=$_SESSION['lang']['tutup'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,100,1000);
//===============================================	
?>


<?php
CLOSE_BOX();
echo"</div>";
echo close_body();
?>