<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
//	
echo open_body();
?>

<script language=javascript1.2 src='js/keu_5akunbankv2.js'></script>


<?php
include('master_mainMenu.php');

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where "
        . " namaorganisasi like '%PT%' and length(kodeorganisasi)=3 ORDER BY kodeorganisasi";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}

$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT * FROM ".$dbname.".keu_5akun where "
        . " namaakun like '%bank%' and length(noakun)=7";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
	$optAkun.="<option value=".$data['noakun'].">".$data['namaakun']."</option>";
}





OPEN_BOX('',"<b>Akun Bank</b>");//optsup

echo"<br /><br /><fieldset style='float:left;'>
    <legend>".$_SESSION['lang']['entryForm']."</legend> 
            <table border=0 cellpadding=1 cellspacing=1>
                    <tr>
                        <td>".$_SESSION['lang']['pt']."</td>
                        <td>:</td>
                        <td><select id=pt style=\"width:125px;\" >".$optOrg."</select></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['noakun']."</td>
                        <td>:</td>
                        <td><select id=noakun style=\"width:125px;\" >".$optAkun."</select></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['namabank']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=bank  onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\"></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['norekeningbank']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=rek   onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\"></td>
                    </tr>
                    <tr>
                        <td></td><td></td><br />
                        <td><br /><button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=hapus()>Batal</button></td>
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