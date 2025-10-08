<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_batransportir.js'></script>
<?php
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'BA Transportir'."</b>");
//get org
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','PABRIK') and 
		  kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
}
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res))
{
	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
//Transporter
$str="select supplierid,namasupplier,kodetimbangan from ".$dbname.".log_5supplier where kodetimbangan<>'' order by namasupplier";
$res=mysql_query($str);
$opttrp="<option value=''>Pilih Data</option>";
while($bar=mysql_fetch_object($res))
{
	$opttrp.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}
$optSPP="<option value=''>Pilih Data</option>";

echo "<fieldset style='width:330px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>
					<table>
						<tr>
							<td>".$_SESSION['lang']['notransaksi']."</td>
							<td><input type=text id=notransaksi class=myinputtext size=30 disabled></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg>".$optorg."</select></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext id=tanggal name=tanggal value=".tanggalnormal(date('Y-m-d'))." readonly='readonly' style='width:150px;' /></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['transporter']."</td>
							<td><select id=trpcode onchange=getSPP()>".$opttrp."</select></td>
						</tr>
						<tr>
							<td>No. SPP</td>
							<td><select id=nospp onchange=getKG()>".$optSPP."</select></td>
						</tr>
						<tr>
							<td>Jumlah (Kg)</td>
							<td><input type=text id=jumlahkg value=0 class=myinputtextnumber maxlength=15 size=12 disabled></td>
						</tr>
						<tr>
							<td>Harga Per Kg</td>
							<td><input type=text id=hargakg value=0 onkeyup=getNo() class=myinputtextnumber maxlength=8 size=12></td>
						</tr>
						<tr>
							<td>Jumlah Harga</td>
							<td><input type=text id=jmlharga value=0 class=myinputtextnumber maxlength=15 size=12 disabled></td>
						</tr>
					</table>
				</td>
			</tr>	  
		</table>
		<center>
			<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
		</center>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width=5% align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
					<td align=center>".'No BA '.$_SESSION['lang']['transporter']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".'Nama Transportir'."</td>
					<td align=center>".'No Kontrak'."</td>
					<td align=center>".'No SPP'."</td>
					<td align=center>".'Jumlah'."</td>
					<td align=center>".'Satuan'."</td>
					<td align=center>".'Nilai'."</td>
					<td align=center>".'PPn'."</td>
					<td align=center>".'PPh'."</td>
					<td align=center>".'Total'."</td>
					<td width=7% align=center>Action</td>	   
				</tr>
			</thead>
			<tbody id=container>";
			echo"<script>loadData()</script>";
echo"	
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
CLOSE_BOX();
close_body();
?>