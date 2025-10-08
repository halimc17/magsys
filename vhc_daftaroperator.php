<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['daftaroperator']).'</b>'); //1 O
?>

<script type="text/javascript" src="js/vhc_daftaroperator.js" /></script>
<div id="action_list">
<?php

$optOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sOrg="select distinct kodetraksi from ".$dbname.".vhc_5master order by kodetraksi asc";
$qOrg=fetchData($sOrg);
$optPt="";
foreach($qOrg as $brsOrg)
{
    $optPt.="<option value=".$brsOrg['kodetraksi'].">".$optOrg[$brsOrg['kodetraksi']]."</option>";
}
$optJns="<option value=>".$_SESSION['lang']['all']."</option>";
$sJvhc="select distinct jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc order by namajenisvhc asc";
$qJvhc=mysql_query($sJvhc) or die(mysql_error($sJvhc));
while($rJvhc=mysql_fetch_assoc($qJvhc))
{
    $optJns.="<option value=".$rJvhc['jenisvhc'].">".$rJvhc['namajenisvhc']."</option>";
}

$optper="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".vhc_penggantianht order by tanggal desc";
$qTgl=mysql_query($sTgl) or die(mysql_error());
while($rTgl=mysql_fetch_assoc($qTgl))
{
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

echo"<table>
     <tr valign=moiddle>
		 <td><fieldset><legend>".$_SESSION['lang']['pilihdata']."</legend>"; 
			echo $_SESSION['lang']['unit'].":<select id=company_id name=company_id style=width:200px><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optPt."</select>&nbsp;"; 
			echo $_SESSION['lang']['jenisvch'].":<select id=jnsVhc name=jnsVhc>".$optJns."</select>&nbsp;";
			echo"<button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 
?>
</div>
<?php 
CLOSE_BOX();
OPEN_BOX();

?>
<div id="cari_barang" name="cari_barang">
<div id="hasil_cari" name="hasil_cari">
    <fieldset>
		<legend><?php echo $_SESSION['lang']['result']?></legend>
		<div id="contain">
		</div>
    </fieldset>
    </div>
</div>
<?php
CLOSE_BOX();
?>
<?php
echo close_body();
?>