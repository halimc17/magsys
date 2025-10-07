<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_invpanen.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Inventaris Peralatan Kebun'."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and length(kodeorganisasi)=4";
	$sKry="select nik,namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar='0000-00-00' or tanggalkeluar>CURDATE()) and tipekaryawan not in ('4','5') order by namakaryawan";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe<>'HOLDING' and induk='".$_SESSION['empl']['induk']."'";
	$sKry="select nik,namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar='0000-00-00' or tanggalkeluar>CURDATE()) and lokasitugas not like '%HO' and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and induk='".$_SESSION['empl']['induk']."') and tipekaryawan not in ('4','5') order by namakaryawan";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$sKry="select nik,namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar='0000-00-00' or tanggalkeluar>CURDATE()) and lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan not in ('4','5') order by namakaryawan";
}
//exit('Warning: '.$str);
$res=mysql_query($str);
$cariorg="<option value=''></option>";
$optorg="";
while($bar=mysql_fetch_object($res)){
	$optorg.="<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
	$cariorg.="<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
}
$sBrg="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where inactive='0' and left(kodebarang,3)='361' order by namabarang";
$qBrg=mysql_query($sBrg);
$optbrg="<option value=''></option>";
while($rBrg=mysql_fetch_object($qBrg)){
	$optbrg.="<option value='".$rBrg->kodebarang."'>".$rBrg->namabarang."</option>";
}
$sSup="select supplierid,namasupplier from ".$dbname.".log_5supplier where left(supplierid,1)='S' order by namasupplier";
$qSup=mysql_query($sSup);
$optsupplier="<option value=''></option>";
while($rSup=mysql_fetch_object($qSup)){
	$optsupplier.="<option value='".$rSup->supplierid."'>".$rSup->namasupplier."</option>";
}
$qKry=mysql_query($sKry);
$optKry="<option value=''></option>";
while($rKry=mysql_fetch_object($qKry)){
	$optKry.="<option value='".$rKry->nik."'>".$rKry->namakaryawan." [".$rKry->nik."}</option>";
}
//echo "<fieldset style='width:665px;'>
echo "<fieldset>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg style='width:265px'>".$optorg."</select></td>
							<td><input type=hidden id=kodeorglama name=kodeorglama value=''></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kodebarang']."</td>
							<td><select id=kodebarang style='width:265px' onchange=getBarang()>".$optbrg."</select></td>
							<td><input type=hidden id=kodebrglama name=kodebrglama value=''></td>
						</tr>
						<tr>
							<td>Kode Inventaris</td>
							<td><input type=text id=kodeinv class=myinputtext maxlength=7 size=10></td>
							<td><input type=hidden id=kodeinvlama name=kodeinvlama value=''></td>
						</tr>
						<tr>
							<td>Nama Inventaris</td>
							<td><input type=text id=namainv class=myinputtext maxlength=150 size=50></td>
						</tr>
						<tr>
							<td>Merk Inventaris</td>
							<td><input type=text id=merkinv class=myinputtext maxlength=30 size=50></td>
						</tr>
						<tr>
							<td>Type Inventaris</td>
							<td><input type=text id=tipeinv class=myinputtext maxlength=30 size=50></td>
						</tr>
						<tr>
							<td>Keterangan Inventaris</td>
							<td><input type=text id=ketinv class=myinputtext maxlength=100 size=50></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
					<table>
						<tr>
							<td>ukuran</td>
							<td><input type=text id=ukuraninv class=myinputtext maxlength=20 size=30></td>
						</tr>
						<tr>
							<td>Warna</td>
							<td><input type=text id=warnainv class=myinputtext maxlength=20 size=30></td>
						</tr>
						<tr>
							<td>Bahan</td>
							<td><input type=text id=bahaninv class=myinputtext maxlength=20 size=30></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']." Perolehan</td>
							<td><input type=text class=myinputtext id=tglbeli size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"></td>
						</tr>
						<tr>
							<td>Harga Perolehan</td>
							<td><input type=text id=hrgbeli value=0 class=myinputtextnumber maxlength=18 size=15 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>No PO</td>
							<td><input type=text id=nopo class=myinputtext maxlength=25 size=30></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kodesupplier']."</td>
							<td><select id=kodesupplier style='width:170px'>".$optsupplier."</select></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['nik']."</td>
							<td><select id=nik style='width:265px' onchange=getKaryawan()>".$optKry."</select></td>
							<td><input type=hidden id=niklama name=niklama value=''></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['namakaryawan']."</td>
							<td><input type=text id=namakaryawan class=myinputtext maxlength=50 size=50></td>
							<td><input type=hidden id=namakaryawanlama name=namakaryawanlama value=''></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['jabatan']."</td>
							<td><input type=text id=divisi class=myinputtext maxlength=45 size=50></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['tanggal']." Diterima</td>
							<td><input type=text class=myinputtext id=tgldiuser size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"> Jumlah 
								<input type=text id=jumlahinv value=0 class=myinputtextnumber maxlength=11 size=11 onkeypress=\"return angka_doang(event);\">
								<input type=text id=satuaninv class=myinputtext maxlength=10 size=10 disabled>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kondisi']."</td>
							<td><select id=kondisi style='width:80px'>
									<option value='Baik'>Baik</option>
									<option value='Cukup'>Cukup</option>
									<option value='Rusak'>Rusak</option>
									<option value='Disposal'>Disposal</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['lokasi']."</td>
							<td><input type=text id=lokasi class=myinputtext maxlength=30 size=50></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['roomname']."</td>
							<td><input type=text id=ruangan class=myinputtext maxlength=30 size=50></td>
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
				<td>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td><select id=carikodeorg style='width:150px'>".$cariorg."</select></td>
				<td>Inventaris</td>
				<td><input type=text id=carinamainv class=myinputtext maxlength=150 size=25></td>
				<td>Karyawan</td>
				<td><select id=carikaryawan style='width:150px'>".$optKry."</select></td>
				<td>Ruangan</td>
				<td><input type=text id=cariruangan class=myinputtext maxlength=50 size=25></td>
				<td><button class=mybutton title='Preview' onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
				<td><button class=mybutton title='Print Excel' onclick=cariinventaris('excel',event)>".$_SESSION['lang']['excel']."</button></td>
				<td><button class=mybutton title='Print QR Code' onclick=cariinventaris('pdf',event)>QR PDF</button></td>
				<td><button class=mybutton title='Print QR Code' onclick=cariinventaris('qrxls',event)>QR ".$_SESSION['lang']['excel']."</button></td>
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
					<td width='5%' align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['kode']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>Merk</td>
					<td align=center>".$_SESSION['lang']['tipe']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>Ukuran</td>
					<td align=center>Warna</td>
					<td align=center>Bahan</td>
					<td align=center>Tgl Beli</td>
					<td align=center>Harga</td>
					<td align=center>No PO</td>
					<td align=center>Supplier</td>
					<td align=center>Karyawan</td>
					<td align=center>Tgl DiUser</td>
					<td align=center>Qty</td>
					<td align=center>Sat</td>
					<td align=center>".$_SESSION['lang']['kondisi']."</td>
					<td align=center>".$_SESSION['lang']['divisi']."</td>
					<td align=center>".$_SESSION['lang']['lokasi']."</td>
					<td align=center>Ruangan</td>
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
