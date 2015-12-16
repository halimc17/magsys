<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_faktur.js'></script>



<?php

$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPt="select * from ".$dbname.".organisasi where tipe='PT' ";
$nPt=mysql_query($iPt) or die (mysql_error($conn));
while($dPt=  mysql_fetch_assoc($nPt))
{
    $optPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
    $optCariPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}

//$optSt="<option value='0'>Tidak Aktif</optio>";
/* <tr>
                    <td>".$_SESSION['lang']['status']."</td>
                    <td>:</td>
                    <td><select id=jenis style=\"width:75px;\">".$optJenis."</select></td>
                </tr>*/
OPEN_BOX();
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>Faktur</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr hidden>
                    <td hidden>".$_SESSION['lang']['id']." Bayar</td> 
                    <td hidden>:</td>
                    <td hidden><input type=text maxlength=5 id=id nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=pt style=\"width:200px;\">".$optPt."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nofaktur']." Bayar</td> 
                    <td>:</td>
                    <td><input type=faktur  id=faktur nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:300px;\"></td>
                </tr>
               
                

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
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
		<div>
			<table>
				<tr>
					<td>".$_SESSION['lang']['perusahaan']."</td>
					<td><select id=cariPt>".$optCariPt."</select></td>
					<td>".$_SESSION['lang']['status']."</td>
					<td><select id=cariStatus >
							<option value=''>".$_SESSION['lang']['all']."</option>
							<option value='1'>Aktif</option>
							<option value='0'>Tidak Aktif</option>
						</select>
					</td>
					<td><button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button></td>
				</tr>
			</table>
		</div>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>