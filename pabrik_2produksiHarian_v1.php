<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_produksi_v1.js'></script>
<?
include('master_mainMenu.php');
$str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'
      order by kodeorganisasi desc";
$res=mysql_query($str); 
$optpabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($res))
{
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."</option>";
}	 
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".pabrik_produksi order by tanggal desc ";
$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
$optper="";
while($rPeriode=  mysql_fetch_assoc($qPeriode))
{
    $optper.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}
OPEN_BOX('',"<b>".$_SESSION['lang']['rprodksiPabrik']." ".$_SESSION['lang']['harian']."</b>");
echo "<fieldset style='width:500px'>
      ".$_SESSION['lang']['kodeorganisasi'].":<select id=pabrik>".$optpabrik."</select>
      ".$_SESSION['lang']['periode']."<select id=periode>".$optper."</select>
	  <button class=mybutton onclick=getLaporanPrdPabrik()>".$_SESSION['lang']['ok']."</button>
	 ";

CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=container style='width:100%;height:500px overflow:scroll'>

     </div>"; 
CLOSE_BOX();
close_body();
?>