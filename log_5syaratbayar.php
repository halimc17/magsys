<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5syaratBayar.js'></script>



<?php

$optJenis='';
$arrJenis=getEnum($dbname,'log_5syaratbayar','jenis');
foreach($arrJenis as $kei=>$fal)
{
        $optJenis.="<option value='".$kei."'>".$fal."</option>";
}

OPEN_BOX();
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>Syarat Bayar</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['kode']." Bayar</td> 
                    <td>:</td>
                    <td><input type=text maxlength=5 id=kode nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jenis']."</td>
                    <td>:</td>
                    <td><select id=jenis style=\"width:75px;\">".$optJenis."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['keterangan']." Bayar</td> 
                    <td>:</td>
                    <td><input type=text  id=ket nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:300px;\"></td>
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