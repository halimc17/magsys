<!--ind-->

<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
//	
echo open_body();
?>

<script language=javascript1.2 src='js/pmn_5terminbayar.js'></script>


<?php
include('master_mainMenu.php');
OPEN_BOX('',"<b>Termin Pembayaran</b>");//optsup

echo"<br /><br /><fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>".$_SESSION['lang']['kode']."</td>
                                    <td>:</td>
                                    <td><input type=text class=myinputtext id=kode size=26 maxlength=10 onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\"></td>
				</tr>
				<tr>
                                    <td>Termin 1</td>
                                    <td>:</td>
                                    <td><input type=text id=satu size=10 class=myinputtextnumber maxlength=3 onkeypress=\"return angka_doang(event);\"  style=\"width:100px;\">%</td>
				</tr>
				<tr>
                                    <td>Termin 2</td>
                                    <td>:</td>
                                    <td><input type=text id=dua size=10 class=myinputtextnumber maxlength=2 onkeypress=\"return angka_doang(event);\"  style=\"width:100px;\">%</td>
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