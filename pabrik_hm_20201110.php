<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_hm.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'HM/Jam Jalan'."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION'";
	$str3="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STENGINE'";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION' 
			and induk in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."')";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION' 
			and induk in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."')";
	$str3="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STENGINE' 
			and induk like concat((select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."' limit 1),'%')";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='STATION'";
	$str3="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='STENGINE'";
}
//exit('Warning: '.$str3);
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res))
{
	$optorg.="<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
}
$res2=mysql_query($str2);
$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar2=mysql_fetch_object($res2))
{
	$optStation.="<option value='".$bar2->kodeorganisasi."'>[".$bar2->kodeorganisasi."] ".$bar2->namaorganisasi."</option>";
}
$res3=mysql_query($str3);
$optMesin='';
while($bar3=mysql_fetch_object($res3))
{
	$optMesin.="<option value='".$bar3->kodeorganisasi."'>[".$bar3->kodeorganisasi."] ".$bar3->namaorganisasi."</option>";
}
echo "<fieldset style='width:850px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg onchange=getStasiun()>".$optorg."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['station']."</td>
							<td><select id=stasiun onchange=getMesin()>".$optStation."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['mesin']."</td>
							<td><select id=mesin onchange=loadData()>".$optMesin."</select></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext onchange=getData() id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<table>
									<tr>
										<td>".$_SESSION['lang']['jenis']."</td>
										<td><select id=tipeservice name=tipeservice>
												<option value='0'>Tidak Service</option>
												<option value='1'>Immediately/Top Overhaul</option>
												<option value='2'>Major/General Overhaul</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>HM Awal</td>
											<td><input type=text id=hmawal value=0 class=myinputtextnumber maxlength=8 size=12 disabled onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>HM Akhir</td>
										<td><input type=text id=hmakhir value=0 class=myinputtextnumber maxlength=8 size=12 onblur=hitungjam(1) onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Selisih Jam</td>
										<td><input type=text id=jam value=0 class=myinputtextnumber maxlength=8 size=12 onblur=hitungjam(2) onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Keterangan</td>
										<td><input type=text id=keterangan class=myinputtext maxlength=100 size=50></td>
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
					<td width='3%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['station']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['kode']."</td>
					<td align=center>".$_SESSION['lang']['mesin']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='13%' width='3%' align=center>".$_SESSION['lang']['service']."</td>
					<td width='5%' align=center>HM Awal</td>
					<td width='5%' align=center>HM Akhir</td>
					<td width='5%' align=center>Jam</td>
					<td width='5%' align=center>Inter/Top OH</td>
					<td width='5%' align=center>Major/Gen OH</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td width='4%' align=center>Action</td>	   
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
