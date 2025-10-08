<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $arr="##kdOrg##periode##afdId";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') and detail='1' order by namaorganisasi asc ";	
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getSub()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $arr="##kdOrg##periode##afdId";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."' and tipe in ('KEBUN') and detail='1' order by kodeorganisasi asc";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getSub()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}
else
{
    $arr="##kdOrg##periode##afdId";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe in ('KEBUN') and detail='1' order by kodeorganisasi asc";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
    //$optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\">";
}
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
while($rPeriode=mysql_fetch_assoc($qPeriode))
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}

$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$sAfd="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING' order by namaorganisasi asc";
$qAfd=mysql_query($sAfd) or die(mysql_error($conn));
while($rAfd=mysql_fetch_assoc($qAfd))
{
	$optAfd.="<option value=".$rAfd['kodeorganisasi'].">".$rAfd['namaorganisasi']."</option>";
}

$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function getSub()
{
    afd=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param='kdOrg='+afd+'&proses=getSubUnit';
    tujuan='sdm_slave_2laporanPremiMandorPanen.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
                  if(con.readyState==4)
                  {
                            if (con.status == 200) {
                                            busy_off();
                                            if (!isSaveResponse(con.responseText)) {
                                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                                            }
                                            else {
//                                                    alert(con.responseText);
                                                    document.getElementById('afdId').innerHTML=con.responseText;

                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  }	
     }  	
}

function showpopup(mandorid,karyawanid,tanggal,ev)
{
   param='mandorid='+mandorid+'&karyawanid='+karyawanid+'&tanggal='+tanggal;
   tujuan='sdm_slave_2laporanPremiMandorPanen_showpopup.php'+"?"+param;
   width='1100';
   height='550';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('No Transaksi Premi Panen '+mandorid+' '+karyawanid+' '+tanggal,content,width,height,ev); 
	
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1()
{
    document.getElementById('kdOrg').value='';
    document.getElementById('afdId').value='';
    //document.getElementById('periode').value='';
}
</script>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['laporanPremi']." ".$_SESSION['lang']['panen'];?></b></legend>
<table cellspacing="1" border="0" >
<tr>
	<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
	<td><?php echo $optOrg;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['subunit'];?></label></td>
	<td><select id="afdId" name="afdId" style="width:150px"><?php echo $optAfd;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['periode'];?></label></td>
	<td><select id="periode" name="periode" style="width:150px">
		<!--<option value=""></option>--><?php echo $optPeriode;?>
	</select></td>
</tr>

<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">
	<button onclick="zPreview('sdm_slave_2laporanPremiMandorPanen','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<button onclick="zExcel(event,'sdm_slave_2laporanPremiMandorPanen.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
</td></tr>
</table>
</fieldset>
</div>


<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:330px;max-width:1100px'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>