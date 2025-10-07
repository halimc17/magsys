<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>

<?php 
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTtm="<option value=''>".$_SESSION['lang']['all']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

// kebun
$sOrg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
    $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

// tahun
$sOrg="select distinct tahun from ".$dbname.".setup_blok_tahunan order by tahun asc limit 1";
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg))
{
    $tahunkecil=$rOrg['tahun'];
}

$tahun=date("Y");

$optTah="";
for ($i = $tahun; $i >= $tahunkecil+1; $i--) {
    if ($i==$tahun) $optTah.="<option value=".$i." selected>".$i."</option>"; else
    $optTah.="<option value=".$i.">".$i."</option>";
}
 
$arr0="##tahun0##kebun0##afdeling0##tahuntanam0##tipe0"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function getAfdeling(tab){
    if(tab==0){
        kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;        
        param='kebun0='+kebun0+'&proses=getAfdeling0';
    }

    tujuan='kebun_slave_2aresta.php';
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
                        document.getElementById('afdeling0').innerHTML=cor[0];                        
                        document.getElementById('tahuntanam0').innerHTML=cor[1];                        
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getTahuntanam(tab){
    if(tab==0){
        kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;        
        afdeling0=document.getElementById('afdeling0').options[document.getElementById('afdeling0').selectedIndex].value;        
        param='kebun0='+kebun0+'&afdeling0='+afdeling0+'&proses=getTahuntanam0';
    }

    tujuan='kebun_slave_2aresta.php';
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
                        document.getElementById('tahuntanam0').innerHTML=cor[0];                        
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

$title[0]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['arealstatement'];

$frm[0]="<fieldset style=\"float: left;\">
<legend><b>".$title[0]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['tahun']."</label></td>
    <td><select id=\"tahun0\" name=\"tahun0\" style=\"width:150px\">".$optTah."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td>
    <td><select id=\"kebun0\" name=\"kebun0\" style=\"width:150px\" onchange=getAfdeling(0)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td>
    <td><select id=\"afdeling0\" name=\"afdeling0\" style=\"width:150px\" onchange=getTahuntanam(0)>".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['tahuntanam']."</label></td>
    <td><select id=\"tahuntanam0\" name=\"tahuntanam0\" style=\"width:150px\">".$optTtm."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['tipe']."</label></td>
    <td><select id=\"tipe0\" name=\"tipe0\" >
			<option value='1'>Tipe 1</option>
			<option value='2'>Tipe 2</option>
			<option value='3'>Tipe 3</option>
		</select></td>
</tr>

<tr height=\"20\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">
        <button onclick=\"zPreview('kebun_slave_2aresta','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2aresta.php','".$arr0."')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
        <button style='display:none' onclick=\"zPdf('kebun_slave_2aresta','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer0' style='overflow:auto;height:350px;width:1220px'>
</div></fieldset>";

//========================
$hfrm[0]=$title[0];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1100);
//===============================================


CLOSE_BOX();
echo close_body();
?>