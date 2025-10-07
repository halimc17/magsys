<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_hm_setup.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'HM/Jam Jalan Mesin Setup'."</b>");
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
echo "<fieldset style='width:600px;'>
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
							<td><select id=stasiun >".$optStation."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['mesin']."</td>
							<td><select id=mesin>".$optMesin."</select></td>
						</tr>
						<tr>
							<td>Pergantian Sparepart 1</td>
							<td><input type=text id=jamganti1 value=0 class=myinputtextnumber maxlength=8 size=12 onblur=cekjam(1) onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Intermediate/Top Overhaul/Sparepart 2</td>
							<td><input type=text id=jamganti2 value=0 class=myinputtextnumber maxlength=8 size=12 onblur=cekjam(2) onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Major/General Overhaul/Sparepart 3</td>
							<td><input type=text id=jamganti3 value=0 class=myinputtextnumber maxlength=8 size=12 onblur=cekjam(3) onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>HM Akhir</td>
							<td><input type=text id=hmakhir value=0 class=myinputtextnumber maxlength=8 size=12 onkeypress=\"return angka_doang(event);\"></td>
						</tr>
						<tr>
							<td>Keterangan</td>
							<td><input type=text id=keterangan class=myinputtext maxlength=100 size=55></td>
						</tr>
						<tr>
							<td><input type=hidden id=addedit name=addedit value='insert'></td>
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
					<td width='30%' align=center>".$_SESSION['lang']['mesin']."</td>
					<td width='7%' align=center>Pergantian Sparepart 1</td>
					<td width='7%' align=center>Intermediate/ Top.Overhaul/ Sparepart 2</td>
					<td width='7%' align=center>Major/ Gen.Overhaul/ Sparepart 3</td>
					<td width='7%' align=center>HM Akhir</td>
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
