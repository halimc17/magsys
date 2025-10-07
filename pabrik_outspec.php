<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_outspec.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Pengiriman Retur/Outspec'."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optpabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and length(kodeorganisasi)=4";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optpabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."'";
}else{
	$optpabrik="";
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
//exit('Warning: '.$str);
$caripabrik="<option value=''></option>";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
	$caripabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."</option>";
}

$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000018','40000019')";
$optbarang="<option value=''></option>";
$caribarang="<option value=''></option>";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$optbarang.="<option value='".$bar->kodebarang."'>[".$bar->kodebarang.'] '.$bar->namabarang."</option>";
	$caribarang.="<option value='".$bar->kodebarang."'>[".$bar->kodebarang.'] '.$bar->namabarang."</option>";
}

$str="select distinct left(tanggal,7) as periode from ".$dbname.".pabrik_outspec order by tanggal desc";
$cariperiode="<option value=''></option>";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$cariperiode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
//echo "<fieldset style='width:665px;'>
echo "<fieldset>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['pabrik']."</td>
							<td><select id=millcode style='width:265px'>".$optpabrik."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['notransaksi']."</td>
							<td><input type=text id=notransaksi class=myinputtext maxlength=7 size=10 disabled></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['namabarang']."</td>
							<td><select id=kodebarang style='width:265px' disabled>".$optbarang."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['nokendaraan']."</td>
							<td><input type=text id=nokendaraan class=myinputtext maxlength=12 size=12 disabled></td>
						</tr>
						<tr>
							<td>No BA</td>
							<td><input type=text id=noba class=myinputtext maxlength=30 size=30></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['alasanDtolak']."</td>
							<td><input type=text id=alasan class=myinputtext maxlength=255 size=50></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
					<table>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><input type=hidden id=addedit name=addedit value='update'></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text id=tanggal class=myinputtext maxlength=10 size=10 disabled></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['beratBersih']."</td>
							<td><input type=text id=beratbersih class=myinputtext maxlength=10 size=10 disabled></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['supir']."</td>
							<td><input type=text id=supir class=myinputtext maxlength=30 size=30 disabled></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['noTiket']."</td>
							<td><input type=text id=notiket class=myinputtext maxlength=7 size=10></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['ongkoskirim']."</td>
							<td><input type=text id=ongkoskirim value=0 class=myinputtextnumber maxlength=12 size=12 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<left>
			<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
		</left>
	</fieldset>";
echo "<fieldset>
		<legend>".$_SESSION['lang']['find'].":</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td><select id=caripabrik style='width:60px'>".$caripabrik."</select></td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td><select id=caribarang style='width:165px'>".$caribarang."</select></td>
				<td>".$_SESSION['lang']['periode']."</td>
				<td><select id=cariperiode style='width:75px'>".$cariperiode."</select></td>
				<td>".$_SESSION['lang']['noTiket']."</td>
				<td><input type=text id=carinotiket class=myinputtext maxlength=7 size=8></td>
				<td><button class=mybutton title='Preview' onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
				<td><button class=mybutton title='Print Excel' onclick=cariinventaris('excel',event)>".$_SESSION['lang']['excel']."</button></td>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='3%' align=center>".$_SESSION['lang']['pabrik']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['beratBersih']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['nokendaraan']."</td>
					<td align=center>".$_SESSION['lang']['supir']."</td>
					<td align=center>No. BA</td>
					<td width='5%' align=center>".$_SESSION['lang']['noTiket']."</td>
					<td align=center>".$_SESSION['lang']['alasanDtolak']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['ongkoskirim']."</td>
					<td width='4%' align=center>Action</td>	   
				</tr>
			</thead>
			<tbody id=container>";
echo"<script>loadData(0)</script>";
echo"	
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
CLOSE_BOX();
close_body();
?>
