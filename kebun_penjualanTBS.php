<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/kebun_penjualanTBS.js'></script>



<?php

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iOrg="select * from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc ";
$nOrg=mysql_query($iOrg) or die (mysql_errno($conn));
while($dOrg=  mysql_fetch_assoc($nOrg))
{
    $optOrg.="<option value='".$dOrg['kodeorganisasi']."'>".$dOrg['namaorganisasi']."</option>";
}

$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iSup="select * from ".$dbname.".log_5supplier order by namasupplier asc";
$nSup=mysql_query($iSup) or die (mysql_errno($conn));
while($dSup=  mysql_fetch_assoc($nSup))
{
    $optSup.="<option value='".$dSup['supplierid']."'>".$dSup['namasupplier']."</option>";
}

$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPer="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$nPer=  mysql_query($iPer) or die (mysql_errno($conn));
while($dPer=mysql_fetch_assoc($nPer))
{
    $optPer.="<option value='".$dPer['periode']."'>".$dPer['periode']."</option>";
}



OPEN_BOX();
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>Penjualan TBS</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:175px;\">".$optOrg."</select></td>
                </tr>
                
                <tr>
                    <td>Pembeli</td>
                    <td>:</td>
                    <td><select id=sup style=\"width:175px;\">".$optSup."</select></td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:175px;\">".$optPer."</select></td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['totalkg']."</td>
                    <td>:</td>
                    <td><input type=text id=kg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=6 style=\"width:175px;\"> </td>
                </tr>
                    

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>";
        


CLOSE_BOX();
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>