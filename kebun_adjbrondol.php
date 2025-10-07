<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/kebun_adjbrondol.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Adjustment Brondolan Afkir/Hilang/Temuan'."</b>");
//Pilih org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='KEBUN' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='KEBUN' and induk='".$_SESSION['empl']['induk']."' order by kodeorganisasi";
	$optUnit="<option value=''></option>";
	$whereOrg="and left(kodeorganisasi,4) in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe in ('KEBUN','AFDELING','BLOK')
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

$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe in ('KEBUN','AFDELING','BLOK') ".$whereOrg." order by kodeorganisasi";
$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
$cariorg="<option value=''></option>";
while($rOrg=mysql_fetch_assoc($qOrg)){
	$cariorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi'].' - '.$rOrg['namaorganisasi']."</option>";
}

$optJenis="<option value=''></option>";
$optJenis.="<option value='Afkir'>Afkir</option>";
$optJenis.="<option value='Hilang'>Hilang</option>";

//$jam=date('H:i');
//echo "<fieldset style='width:665px;'>
echo "<fieldset>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['unit']."</td>
							<td><select id=unit style='width:265px' onchange=getDivisi()>".$optUnit."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['divisi']."</td>
							<td><select id=divisi style='width:265px' onchange=getBlok()>".$optDivisi."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['blok']."</td>
							<td><select id=kodeorg style='width:265px'>".$optBlok."</select></td>
							<td><input type=hidden id=kodeorglama name=kodeorglama></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext id=tanggal size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
							<td><input type=hidden id=tanggallama name=tanggallama></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['waktu']."</td>
							<td><input type=time class=myinputtext id=waktu size=10></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['jenis']."</td>
							<td><select id=jenis style='width:120px'>".$optJenis."</select></td>
							<td><input type=hidden id=jenislama name=jenislama></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kg']."</td>
							<td><input type=text id=kg value=0 class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"></td>
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
				<td>".$_SESSION['lang']['kodeorganisasi']." </td>
				<td><select id=carikodeorg style='width:225px'>".$cariorg."</select></td>
				<td> ".$_SESSION['lang']['tanggal']." </td>
				<td><input type=text class=myinputtext id=caritanggal1 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> sd </td>
				<td><input type=text class=myinputtext id=caritanggal2 size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
				<td> Jenis </td>
				<td><select id=carijenis style='width:120px'>".$optJenis."</select></td>
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
					<td width='6%' align=center>".$_SESSION['lang']['divisi']."</td>
					<td width='15%' align=center>".$_SESSION['lang']['blok']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['waktu']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['jenis']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['kg']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td width='6%' align=center>Action</td>	   
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
