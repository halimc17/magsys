<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_stokProduk.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';




 //
if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   

}
else
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK'";   

}
$optOrg="";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        
        
}	

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$iSup="SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang WHERE inactive='0' and kelompokbarang='400'"
        . " and kodebarang not in ('40000001','40000002','40000003') order by namabarang asc";
$nSup=mysql_query($iSup) or die (mysql_error($conn));
while ($dSup=mysql_fetch_assoc($nSup))
{
    $optBrg.="<option value=".$dSup['kodebarang'].">".$dSup['namabarang']."</option>";
}
	
?>


<?php


OPEN_BOX('',"");// style='float:left;'
echo "<fieldset style='float:left;'><legend><b>Stok Product</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>".$_SESSION['lang']['pabrik']."</td>
                                    <td>:</td>
                                    <td><select id=kdOrg style=\"width:125px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['tanggal']."</td>
                                    <td>:</td>
                                    <td><input type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['namabarang']."</td>
                                    <td>:</td>
                                    <td><select id=kdBrg style=\"width:125px;\">".$optBrg."</select></td>
				</tr>
                                <tr>
                                    <td width=100>Estimasi Saldo Awal</td>
                                    <td>:</td>
                                    <td><input type=text id=sawal size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
				</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['produksi']."</td>
                                    <td>:</td>
                                    <td><input type=text id=prod size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
				</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['jmlhPakai']."</td>
                                    <td>:</td>
                                    <td><input type=text id=pakai size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
				</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['penjualan']."</td>
                                    <td>:</td>
                                    <td><input type=text id=jual size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
				</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['sisa']."</td>
                                    <td>:</td>
                                    <td><input type=text id=sisa size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
				</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['keterangan']."</td>
                                    <td>:</td>
                                    <td><input type=text id=ket size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>
				</tr>
				<tr>
                                    <td></td><td></td><br />
                                    <td><br /><button class=mybutton onclick=simpan()>Simpan</button>
                                    <button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
	</fieldset>";
	CLOSE_BOX();
	OPEN_BOX();

	echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']."</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>:</td>
				<td><select id='kdBrgSch' style='width:150px;'>".$optBrg."</select></td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' id='tglSch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
				<td><button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>&nbsp;<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
		<div id=container> 
			<script>loadData()</script>
		</div>
		</fieldset>";

	CLOSE_BOX();					
?>