
<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_pembersihantangki.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';





$optTangki="";   
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
    $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}	

$optBrg="";
$optBrgSch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optBrgSch.="<option value='40000001'>CRUDE PALM OIL (CPO)</option>";
$optBrgSch.="<option value='40000002'>PALM KERNEL (PK)</option>";




#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}
	
?>


<?php


OPEN_BOX('',"<b>BA Pengurangan Stok</b>");// style='float:left;'
$frm[0].="<fieldset style='float:left;'><fieldset  style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>".$_SESSION['lang']['pabrik']."</td>
                                    <td>:</td>
                                    <td><select id=pabrik onchange=getTangki() style=\"width:125px;\" >".$optOrg."</select></td>
				</tr>
                                <tr>
                                    <td>".$_SESSION['lang']['tangki']."</td>
                                    <td>:</td>
                                    <td><select id=tangki onchange=getBarang() style=\"width:125px;\" >".$optTangki."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['namabarang']."</td>
                                    <td>:</td>
                                    <td><select id=barang style=\"width:125px;\">".$optBrg."</select></td>
				</tr>
                                <tr>
                                    <td>".$_SESSION['lang']['tanggal']."</td>
                                    <td>:</td>
                                    <td><input type=text class=myinputtext id=tgl name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:125px;/>
                                        <select id=jm>".$jm."</select>:<select id=mn>".$mnt."</select></td>
                                </tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['jumlah']."</td>
                                    <td>:</td>
                                    <td><input type=text id=jumlah size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
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
					<input type=hidden id=method value='insert'>";
    $frm[0].="<fieldset style='float:left;'>
                <legend>Sort</legend> 
                        <table border=0 cellpadding=1 cellspacing=1>
                            <tr>
                                <td>".$_SESSION['lang']['namabarang']."</td>
                                <td>:</td>
                                <td><select id='brgSch' style='width:150px;'>".$optBrgSch."</select></td>
                            </tr>
                            <tr>
                                <td>".$_SESSION['lang']['tanggal']."</td>
                                <td>:</td>
                                <td><input type='text' class='myinputtext' id='tglSch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
                            </tr>
                            <tr> <td></td><td></td><br>
                                <td><button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>
                                     <button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
                            </tr>
                        </table></fieldset></fieldset>";




					


//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
$frm[0].="<fieldset  style='float:left;'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";




$arr="##pabrikRep##brgRep##tgl1Rep##tgl2Rep";
$frm[1].="<fieldset style='float:left;'><legend><b>Laporan BA Pembersihan Tangki</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrikRep style=\"width:155px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=brgRep style='width:155px;'>".$optBrgSch."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('pabrik_slave_2pembersihantangki','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2pembersihantangki.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batalRep() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:300px;max-width:1150'; >
</div></fieldset>";


$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['laporan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();




?>

<?php
/*
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo "<fieldset>";
echo "<legend><b>".$_SESSION['lang']['datatersimpan']."</b></legend>";
//echo "<div id=container>";
echo" <div id=container style='width:500px;height:400px;overflow:scroll'>";	
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
			 <td align=center>No</td>
                         <td align=center>".$_SESSION['lang']['pabrik']."</td>
			 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
			 <td align=center>".$_SESSION['lang']['tanggal']."</td>
			 <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
			 <td align=center>".$_SESSION['lang']['harga']."</td>
			 <td align=center>*</td></tr>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";
        
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
echo "</fieldset>";
CLOSE_BOX();
echo close_body();*/					
?>