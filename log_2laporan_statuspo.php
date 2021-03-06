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
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

$optPt1="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStat1=$optPt1;
$optStat=$optPt1;
$optTer1=$optPt1;

//status po1
$arrDt1=array("0"=>"Head Office","1"=>"Local");
foreach($arrDt1 as $dtlst=>$dtklrm)
{
    $optStat1.="<option value='".$dtlst."'>".$dtklrm."</option>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt=$optPeriode;

//semua pt
$sPt="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$qPt=mysql_query($sPt) or die(mysql_error($conn));
while($rPt=mysql_fetch_assoc($qPt))
{
    $optPt.="<option value='".$rPt['kodeorganisasi']."'>".$rPt['namaorganisasi']."</option>";
    $optPt1.="<option value='".$rPt['kodeorganisasi']."'>".$rPt['namaorganisasi']."</option>";
}

//periode akuntansi
$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
while($rPeriode=mysql_fetch_assoc($qPeriode))
{
    $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}

//status po
$arrDt=array("0"=>"Head Office","1"=>"Local");
foreach($arrDt as $dtlst=>$dtklrm)
{
    $optStat.="<option value='".$dtlst."'>".$dtklrm."</option>";
}

//status terima po
$arrDt=array("0"=>"Belum Selesai","1"=>"Sudah Selesai","2"=>"Dapat Dikirim","3"=>"Diterima Gudang");
foreach($arrDt as $dtlst=>$dtklrm)
{
    $optTer1.="<option value='".$dtlst."'>".$dtklrm."</option>";
}

$arr="##periode##statId##ptId";
$arr1="##tgl1##tgl2##status1##pt1##terima1";

//$arrKry="##kdeOrg##period##idKry##tgl_1##tgl_2";
?>

<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function Clear1()
{
    document.getElementById('ptId').value='';
    document.getElementById('statId').value='';
    document.getElementById('periode').value='';
}
</script>
<link rel=stylesheet type='text/css' href='style/zTable.css'>
      
<?php

$title[0]='PO Status Report';
$title[1]='PO Status Detail';

$frm[0]="<div>
    <fieldset style=\"float: left;\">
    <legend><b>".$_SESSION['lang']['form']." ".$_SESSION['lang']['listpo']."</b></legend>
    <table cellspacing=\"1\" border=\"0\" >
    <tr>
        <td><label>".$_SESSION['lang']['pt']."</label></td>
        <td><select id=\"ptId\" name=\"ptId\" style=\"width:150px\">".$optPt."</select></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['periode']."</label></td>
        <td><select id=\"periode\" name=\"periode\" style=\"width:150px\">".$optPeriode."</select></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['status']."</label></td>
        <td><select id=\"statId\" name=\"statId\" style=\"width:150px\">".$optStat."</select></td>
    </tr>

    <tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
    <tr><td colspan=\"2\">
        <button onclick=\"zPreview('log_slave_2laporan_statuspo','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'log_slave_2laporan_statuspo.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
        <button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>
    </td></tr>
    </table>
    </fieldset>
    </div>

    <div style=\"margin-bottom: 30px;\">
    </div>
    <fieldset style='clear:both'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>

    </div></fieldset>
    ";

$frm[1]="<div>
    <fieldset style=\"float: left;\">
    <legend><b>".$_SESSION['lang']['form']." ".$_SESSION['lang']['listpo']."</b></legend>
    <table cellspacing=\"1\" border=\"0\" >
    <tr>
        <td><label>".$_SESSION['lang']['tanggal']."</label></td>
        <td><input type=\"text\" class=\"myinputtext\" id=\"tgl1\" name=\"tgl1\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:60px;\" />
        ".$_SESSION['lang']['sampai']."
        <input type=\"text\" class=\"myinputtext\" id=\"tgl2\" name=\"tgl2\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:60px;\" /></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['status']."</label></td>
        <td><select id=\"status1\" name=\"status1\" style=\"width:150px\">".$optStat1."</select></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['pt']."</label></td>
        <td><select id=\"pt1\" name=\"pt1\" style=\"width:150px\">".$optPt1."</select></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['status']." ".$_SESSION['lang']['diterima']."</label></td>
        <td><select id=\"terima1\" name=\"terima1\" style=\"width:150px\">".$optTer1."</select></td>
    </tr> 

    <tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
    <tr><td colspan=\"2\">
        <button onclick=\"zPreview('log_slave_2laporan_statuspo1','".$arr1."','printContainer1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'log_slave_2laporan_statuspo1.php','".$arr1."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
        <button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>
    </td></tr>
    </table>
    </fieldset>
    </div>

    <div style=\"margin-bottom: 30px;\">
    </div>
    <fieldset style='clear:both'><legend><b>Print Area</b></legend>
    <div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'>

    </div></fieldset>
    ";

//========================
$hfrm[0]=$title[0];
$hfrm[1]=$title[1];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1220);
//===============================================

CLOSE_BOX();
echo close_body();
?>