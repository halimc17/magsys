<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_meggertest.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Megger Test'."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION'";
	$str3="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STENGINE'";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."'";
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
echo "<fieldset style='width:665px;'>
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
										<td>Ukur U-V</td>
										<td><input type=text id=ukur1 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Ukur U-W</td>
										<td><input type=text id=ukur2 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Ukur V-W</td>
										<td><input type=text id=ukur3 value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
									</tr>
									<tr>
										<td>Temp Elektromotor</td>
										<td><input type=text id=suhu value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
										<td><input type=hidden id=suhulama></td>
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
					<td rowspan=2 width='3%' align=center>".$_SESSION['lang']['unit']."</td>
					<td rowspan=2 width='5%' align=center>".$_SESSION['lang']['station']."</td>
					<td rowspan=2 width='7%' align=center>".$_SESSION['lang']['kode']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['mesin']."</td>
					<td rowspan=2 width='7%' align=center>KW Electromotor</td>
					<td rowspan=2 width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td colspan=3 align=center>Megger Test (M&#8486;)</td>
					<td align=center>Temp</td>
					<td colspan=4 align=center>".$_SESSION['lang']['kondisi']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>
					<td rowspan=2 width='7%' align=center>Action</td>	   
				</tr>
				<tr class=rowheader>
					<td width='4%' align=center>U-V</td>
					<td width='4%' align=center>U-W</td>
					<td width='4%' align=center>V-W</td>
					<td width='4%' align=center>(&#8451;)</td>
					<td width='5%' align=center>Critical</td>
					<td width='5%' align=center>Abnormal</td>
					<td width='5%' align=center>Good</td>
					<td width='5%' align=center>VeryGood</td>
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
