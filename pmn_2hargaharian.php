<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';


?>

<script type="text/javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2hargaharian.js'></script>

<script>
    function zExceldetail(ev,tujuan,passParam)
{
	judul='Report Excel';
        var passP = passParam.split('##');
	
    var param = "proses=exceldetail";
    for(i=0;i<passP.length;i++) {
       // var tmp = document.getElementById(passP[i]);
	   	a=i;
        param += "&"+passP[a]+"="+passP[i+1];
    }
	
	printFile(param,tujuan,judul,ev)	
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='250';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
</script>

<?php
$arr="##psrId##komoditi##periodePsr";
$arr2="##psrId2##komoditi2##periodePsr2";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpasar="<option value=''>".$_SESSION['lang']['all']."</option>";
$optBrg=$optGoldar=$optPeriode;
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pmn_hargapasar order by tanggal desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
/*$arrenum=getEnum($dbname,'pmn_hargapasar','pasar');
foreach($arrenum as $key=>$val)
{
	$optGoldar.="<option value='".$key."'>".$val."</option>";
        $optpasar.="<option value='".$key."'>".$val."</option>";
}*/


$iPasar="select distinct(pasar) as pasar  from ".$dbname.".pmn_hargapasar order by pasar asc ";
$nPasar=  mysql_query($iPasar) or die (mysql_error($conn));
while($dPasar=  mysql_fetch_assoc($nPasar))
{
    $optGoldar.="<option value='".$dPasar['pasar']."'>".$dPasar['pasar']."</option>";
    $optpasar.="<option value='".$dPasar['pasar']."'>".$dPasar['pasar']."</option>";
}

$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$sBrng="select distinct(kodeproduk) as kodeproduk from ".$dbname.".pmn_hargapasar";
$qBrng=mysql_query($sBrng) or die(mysql_error($conn));
while($rBarang=mysql_fetch_assoc($qBrng))
{
    $optBrg.="<option value='".$rBarang['kodeproduk']."'>".$optBarang[$rBarang['kodeproduk']]."</option>";
}

OPEN_BOX('',"<b>Daily Price</b><br>");
$frm[0].="<div>
<fieldset style='float: left;'>
<legend><b>".$_SESSION['lang']['hargapasar']."</b></legend>";
$frm[0].="<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['pasar']."</label></td><td><select id=psrId name=periode style='width:150px'>".$optGoldar."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['komoditi']."</label></td><td><select id=komoditi name=komoditi style='width:150px'>".$optBrg."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['periode']."</label></td><td><select id=periodePsr name=periodePsr style='width:150px'>".$optPeriode."</select></td></tr>
<tr height=20><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2><button onclick=\"zPreview('pmn_slave_2hargapasar','".$arr."','printContainer')\" class=mybutton name=preview id=preview>Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2hargapasar.php','".$arr."')\" class=mybutton name=preview id=preview>Excel</button>
        <button onclick=\"grafikProduksi(event)\" class=mybutton name=preview id=preview>Jpgraph</button></td></tr>

</table>
</fieldset>
</div>";

$frm[0].="<div style='margin-bottom: 30px;'>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>
		";
$frm[0].="</tbody></table></fieldset>";

//assseettt
$frm[1].="<div>
<fieldset style='float: left;'>
<legend><b>".$_SESSION['lang']['bandingHarga']."</b></legend>";
$frm[1].="<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['pasar']."</label></td><td><select id=psrId2 name=psrId2 style='width:150px'>".$optpasar."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['komoditi']."</label></td><td><select id=komoditi2 name=komoditi2 style='width:150px'>".$optBrg."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['periode']."</label></td><td><select id=periodePsr2 name=periodePsr2 style='width:150px'>".$optPeriode."</select></td></tr>
<tr height=20><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2>
<button onclick=\"zPreview('pmn_slave_2hargapasar_2','".$arr2."','printContainer2')\" class=mybutton name=preview id=preview>Preview</button>
        <button onclick=\"zExcel(event,'pmn_slave_2hargapasar_2.php','".$arr2."')\" class=mybutton name=preview id=preview>Excel</button>
		<button onclick=\"grafikProduksi2(event)\" class=mybutton name=preview id=preview>Jpgraph</button>
</td>
</tr>

</table>
</fieldset>
</div>";

$frm[1].="<div style='margin-bottom: 30px;'>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer2' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>
		";
$frm[1].="</tbody></table></fieldset>";



//========================
$hfrm[0]=$_SESSION['lang']['hargapasar'];
$hfrm[1]=$_SESSION['lang']['bandingHarga'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,220,930);
//===============================================	
?>

<?php
CLOSE_BOX();
echo close_body();
?>