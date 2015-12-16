<?//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('',"<b>Mill Cost</b><br /><br />");
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>

function lihatDetail(noakun,kodeorg,per,unit,tipe,ev){
   param='noakun='+noakun+'&kodeorg='+kodeorg+'&per='+per+'&unit='+unit+'&tipe='+tipe;
   tujuan='pabrik_slave_2biaya_detail.php'+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Detail Transaksi'+noakun,content,width,height,ev);     
}
    
    
    
</script>

<?
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi asc ";	
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$iPer="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12";
$nPer=mysql_query($iPer) or die(mysql_error($conn));
while($dPer=mysql_fetch_assoc($nPer))
{
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}


$arr="##kdorg##per";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:150px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td colspan=4>
                    <button onclick=zPreview('pabrik_slave_2biaya','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'pabrik_slave_2biaya.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();					
?>