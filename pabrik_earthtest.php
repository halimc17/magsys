<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_earthtest.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Earth Test'."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK'";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."'";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
}
//exit('Warning: '.$str3);
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res))
{
	$optorg.="<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
}
$optMesin='';
$optMesin.="<option value='Bangunan_Kantor'>Bangunan_Kantor</option>";
$optMesin.="<option value='Bangunan_Pabrik'>Bangunan_Pabrik</option>";
$optMesin.="<option value='GuestHouse_Baru'>GuestHouse_Baru</option>";
$optMesin.="<option value='GuestHouse_Lama'>GuestHouse_Lama</option>";
$optMesin.="<option value='Power_House'>Power_House</option>";
$optMesin.="<option value='Rumah_Manager1'>Rumah_Manager1</option>";
$optMesin.="<option value='Rumah_Manager2'>Rumah_Manager2</option>";
$optMesin.="<option value='Rumah_Manager3'>Rumah_Manager3</option>";
$optMesin.="<option value='Timbangan'>Timbangan</option>";

echo "<fieldset style='width:600px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg onchange=loadData()>".$optorg."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['lokasi']."</td>
							<td><select id=mesin>".$optMesin."</select></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"></td>
							<td><input type=hidden id=tgllama></td>
						</tr>
						<tr>
							<td>Keterangan</td>
							<td><input type=text id=keterangan class=myinputtext maxlength=100 size=54></td>
							<td><input type=hidden id=ketlama></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<table>
									<tr>
										<td>Standard</td>
										<td><input type=text id=standard value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
										<td><input type=hidden id=stdlama></td>
									</tr>
									<tr>
										<td>Ukur 1</td>
										<td><input type=text id=ukur1 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Ukur 2</td>
										<td><input type=text id=ukur2 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Ukur 3</td>
										<td><input type=text id=ukur3 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td><input type=hidden id=addedit name=addedit value='insert'></td>
									</tr>
								</table>
							</td>
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
					<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='20%' align=center>".$_SESSION['lang']['lokasi']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='5%' align=center>Standard</td>
					<td width='5%' align=center>Ukur 1</td>
					<td width='5%' align=center>Ukur 2</td>
					<td width='5%' align=center>Ukur 3</td>
					<td width='5%' align=center>Rata Rata</td>
					<td width='5%' align=center>Selisih</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td width='7%' align=center>Action</td>	   
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
