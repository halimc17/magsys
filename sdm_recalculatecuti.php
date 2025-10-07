<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_recalculatecuti.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Re Calculate Cuti Untuk Karyawan Resign/PHK'."</b>");
//Pilih org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	//$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='HOLDING' and length(kodeorganisasi)=4 order by kodeorganisasi";
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and length(kodeorganisasi)=4 order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	//$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
	//		where lokasitugas like '%HO' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
	$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
			where tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and length(kodeorganisasi)=4 and induk='".$_SESSION['empl']['induk']."' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
			where lokasitugas not like '%HO' and kodeorganisasi='".$_SESSION['empl']['induk']."' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
}else{
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$optUnit="";
	$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
			where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tanggalkeluar='0000-00-00' and tipekaryawan not in ('4','5','8') order by namakaryawan";
}
//exit('Warning: '.$str);
$optUnit="<option value=''></option>";
$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
while($rUnit=mysql_fetch_assoc($qUnit)){
	$optUnit.="<option value=".$rUnit['kodeorganisasi'].">[".$rUnit['kodeorganisasi'].'] - '.$rUnit['namaorganisasi']."</option>";
}
$cariorg=$optUnit;

$optKary="<option value=''></option>";
$carikry="<option value=''></option>";
$qKary=mysql_query($sKary);
while($rKary=mysql_fetch_object($qKary)){
	$optKary.="<option value='".$rKary->karyawanid."'>[".$rKary->nik.'] '.$rKary->namakaryawan."</option>";
	$carikry.="<option value='".$rKary->karyawanid."'>[".$rKary->nik.'] '.$rKary->namakaryawan."</option>";
}

//echo "<fieldset style='width:665px;'>
echo "<fieldset>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['unit']."</td>
							<td><select id=kodeorg style='width:275px' onchange=getKary() disabled>".$optUnit."</select></td>
							<td><input type=hidden id=kodeorglama name=kodeorglama></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['nama']."</td>
							<td><select id=karyawanid style='width:275px' onchange=getUnit()>".$optKary."</select></td>
							<td><input type=hidden id=karyawanidlama name=karyawanidlama></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']." Surat</td>
							<td><input type=text class=myinputtext id=tanggal size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']." Resign/PHK</td>
							<td><input type=text class=myinputtext id=tglkeluar size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
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
				<td> ".$_SESSION['lang']['nama']." </td>
				<td><select id=carikaryawanid style='width:265px'>".$carikry."</select></td>
				<td><button class=mybutton title='Preview' onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
				<td><button class=mybutton title='Print' onclick=carikaryawan('excel',event)>".$_SESSION['lang']['excel']."</button></td>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='5%'  align=center>".$_SESSION['lang']['unit']."</td>
					<td width='7%'  align=center>".$_SESSION['lang']['nik']."</td>
					<td width='30%' align=center>".$_SESSION['lang']['nama']."</td>
					<td width='7%'  align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='7%'  align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
					<td width='7%'  align=center>".$_SESSION['lang']['jumlah']."</td>
					<td width='30%' align=center>".$_SESSION['lang']['keterangan']."</td>
					<td width='7%'  align=center>Action</td>	   
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
