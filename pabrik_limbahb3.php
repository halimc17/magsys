<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_limbahb3.js'></script>
<?php
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Pabrik Limbah B3'."</b>");
//Pilih org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and length(kodeorganisasi)=4 order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="and left(kodeorganisasi,4) in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe in ('PABRIK','STATION','STENGINE')
				and induk like '".$_SESSION['empl']['induk']."%')";
}else{
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$optUnit="";
	$whereOrg="and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
//exit('Warning: '.$str);
$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
while($rUnit=mysql_fetch_assoc($qUnit)){
	$optUnit.="<option value=".$rUnit['kodeorganisasi'].">[".$rUnit['kodeorganisasi'].'] - '.$rUnit['namaorganisasi']."</option>";
}
$cariorg=$optUnit;

$sMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STENGINE' ".$whereOrg." order by kodeorganisasi";
$qMesin=mysql_query($sMesin) or die(mysql_error($conn));
$optMesin="<option value=''></option>";
$optMesin.="<option value='GUDANGB3'>[GUDANGB3] - Gudang B3</option>";
while($rMesin=mysql_fetch_assoc($qMesin)){
	$optMesin.="<option value=".$rMesin['kodeorganisasi'].">[".$rMesin['kodeorganisasi'].'] - '.$rMesin['namaorganisasi']."</option>";
}

$sBarang="select kodebarang,namabarang from ".$dbname.".log_5masterbarang 
			where kodebarang like '3460804%' or kodebarang like '3470105%' or kodebarang like '3470106%' or kodebarang like '352%'";
$optbarang="<option value=''></option>";
$qBarang=mysql_query($sBarang);
while($rBarang=mysql_fetch_object($qBarang)){
	$optbarang.="<option value='".$rBarang->kodebarang."'>[".$rBarang->kodebarang.'] '.$rBarang->namabarang."</option>";
}
$caribrg=$optbarang;

//echo "<fieldset style='width:665px;'>
echo "<fieldset>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['unit']."</td>
							<td><select id=kodeorg style='width:265px' onchange=getMesin()>".$optUnit."</select></td>
							<td><input type=hidden id=kodeorglama name=kodeorglama></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext id=tanggal size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
							<td><input type=hidden id=tanggallama name=tanggallama></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['mesin']."</td>
							<td><select id=kodemesin style='width:265px'>".$optMesin."</select></td>
							<td><input type=hidden id=kodemesinlama name=kodemesinlama></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['namabarang']."</td>
							<td><select id=kodebarang style='width:265px'>".$optbarang."</select></td>
							<td><input type=hidden id=kodebaranglama name=kodebaranglama></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
					<table>
						<tr>
							<td>Jumlah Masuk</td>
							<td><input type=text id=qtymasuk value=0 class=myinputtextnumber maxlength=7 size=9 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Jumlah Keluar</td>
							<td><input type=text id=qtykeluar value=0 class=myinputtextnumber maxlength=7 size=9 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Keterangan</td>
							<td><input type=text id=keterangan class=myinputtext maxlength=50 size=80></td>
						</tr>
						<tr>
							<td><input type=hidden id=addedit name=addedit value='insert'></td>
							<td><input type=hidden id=page name=page value=0></td>
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
				<td>".$_SESSION['lang']['unit']." </td>
				<td><select id=carikodeorg style='width:265px'>".$cariorg."</select></td>
				<td> ".$_SESSION['lang']['tanggal']." </td>
				<td><input type=text class=myinputtext id=caritanggal1 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> sd </td>
				<td><input type=text class=myinputtext id=caritanggal2 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> ".$_SESSION['lang']['namabarang']." </td>
				<td><select id=carikodebarang style='width:265px'>".$caribrg."</select></td>
				<td><button class=mybutton title='Preview' onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
				<td><button class=mybutton title='Print' onclick=cariinventaris('excel',event)>".$_SESSION['lang']['excel']."</button></td>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='25%' align=center>".$_SESSION['lang']['mesin']."</td>
					<td width='25%' align=center>".$_SESSION['lang']['namabarang']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['masuk']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['keluar']."</td>
					<td width='25%' align=center>".$_SESSION['lang']['keterangan']."</td>
					<td width='5%' align=center>Action</td>	   
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
