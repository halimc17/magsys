<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/kebun_cekancak.js'></script>
<?php
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Kontrol Losses Ancak Panen'."</b>");
//Pilih org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='KEBUN' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="";
	$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='KEBUN' and induk='".$_SESSION['empl']['induk']."' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="and left(kodeorganisasi,4) in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe in ('KEBUN','AFDELING','BLOK')
				and induk like '".$_SESSION['empl']['induk']."%')";
	$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and kodeorganisasi='".$_SESSION['empl']['induk']."' 
			and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
}else{
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$optUnit="";
	$whereOrg="and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
	$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
			and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
}
//exit('Warning: '.$str);
$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
while($rUnit=mysql_fetch_assoc($qUnit)){
	$optUnit.="<option value=".$rUnit['kodeorganisasi'].">[".$rUnit['kodeorganisasi'].'] - '.$rUnit['namaorganisasi']."</option>";
}

$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='AFDELING' ".$whereOrg." order by kodeorganisasi";
$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
$optDivisi="<option value=''></option>";
while($rDivisi=mysql_fetch_assoc($qDivisi)){
	$optDivisi.="<option value=".$rDivisi['kodeorganisasi'].">[".$rDivisi['kodeorganisasi'].'] - '.$rDivisi['namaorganisasi']."</option>";
}

$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='BLOK' ".$whereOrg." order by kodeorganisasi";
$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
$optBlok="<option value=''></option>";
while($rBlok=mysql_fetch_assoc($qBlok)){
	$optBlok.="<option value=".$rBlok['kodeorganisasi'].">[".$rBlok['kodeorganisasi'].'] - '.$rBlok['namaorganisasi']."</option>";
}
/*
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe in ('KEBUN','AFDELING','BLOK') ".$whereOrg." order by kodeorganisasi";
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
$cariorg="<option value=''></option>";
while($rOrg=mysql_fetch_assoc($qOrg)){
	$cariorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi'].' - '.$rOrg['namaorganisasi']."</option>";
}
*/
$cariorg=$optDivisi;

//exit('Warning: '.$sKary);
$qKary=mysql_query($sKary) or die(mysql_error($conn));
$optKary="<option value=''></option>";
while($rKary=mysql_fetch_assoc($qKary)){
	$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." [".$rKary['nik']."]</option>";
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
							<td><select id=unit style='width:300px' onchange=getDivisi()>".$optUnit."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['divisi']."</td>
							<td><select id=divisi style='width:300px' onchange=getBlok()>".$optDivisi."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['blok']."</td>
							<td><select id=kodeorg style='width:300px'>".$optBlok."</select></td>
							<td><input type=hidden id=kodeorglama name=kodeorglama></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['diperiksa']."</td>
							<td><select id=diperiksa style='width:300px'>".$optKary."</select></td>
							<td><input type=hidden id=diperiksalama name=diperiksalama></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext id=tanggal size=9 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
							<td><input type=hidden id=tanggallama name=tanggallama></td>
						</tr>
						<tr>
							<td>Jumlah Pokok Sample</td>
							<td><input type=text id=pokok value=0 class=myinputtextnumber maxlength=7 size=9 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Buah Tinggal</td>
							<td><input type=text id=janjang value=0 class=myinputtextnumber maxlength=7 size=9 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Brondolan Tinggal</td>
							<td><input type=text id=brondolan value=0 class=myinputtextnumber maxlength=7 size=9 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Keterangan</td>
							<td><input type=text id=keterangan class=myinputtext maxlength=50 size=60></td>
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
				<td>".$_SESSION['lang']['divisi']." </td>
				<td><select id=carikodeorg style='width:225px'>".$cariorg."</select></td>
				<td> ".$_SESSION['lang']['tanggal']." </td>
				<td><input type=text class=myinputtext id=caritanggal1 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> sd </td>
				<td><input type=text class=myinputtext id=caritanggal2 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> ".$_SESSION['lang']['diperiksa']." </td>
				<td><select id=carikary style='width:120px'>".$optKary."</select></td>
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
					<td width='3%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['divisi']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['blok']."</td>
					<td width='3%' align=center>".$_SESSION['lang']['bjr']."</td>
					<td width='3%' align=center>SPH</td>
					<td align=center>".$_SESSION['lang']['diperiksa']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['pokok']." Sample</td>
					<td width='6%' align=center>Buah Tinggal</td>
					<td width='6%' align=center>".$_SESSION['lang']['brondolan']."</td>
					<td width='6%' align=center>Losses BT (Kg/Ha)</td>
					<td width='6%' align=center>Losses Brd (Kg/Ha)</td>
					<td width='6%' align=center>Losses Jml (Kg/Ha)</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
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
