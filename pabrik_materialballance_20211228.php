<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_materialballance.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('',"<b>".'Material Ballance'."</b>");
//get org
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res)){
	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' 
	and induk in (select kodeorganisasi from ".$dbname.".organisasi 
				where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."'))
	order by kodeorganisasi";
//exit('Warning: '.$i);
$optBlok.="<option value=''></option>";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optBlok.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
}
$optBlok.="<option value='TBSEXT'>TBS Luar</option>";

echo "<fieldset style='width:98%;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td><select id=kodeorg>".$optorg."</select></td>
				<td>".$_SESSION['lang']['blok']."</td>
				<td><select id=kodeblok>".$optBlok."</select></td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td><input type=text class=myinputtext id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"></td>
			</tr>
		</table>
		<table>
			<tr>
				<td valign=top>  
					<fieldset><legend>Buah Unripe</legend>
						<table>
							<thead><tr>
								<td align=center>DESCRIPTION</td>
								<td align=center>VALUE</td>
								<td align=center>SAT</td>
								<td align=center>%</td>
								<td align=center>% FFB</td>
							</tr></thead>
							<tr>
								<td>Berat TBS</td>
								<td><input type=text id=berattbs_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=berattbs_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=berattbs_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>TBS Rebus</td>
								<td><input type=text id=tbsrebus_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=tbsrebus_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=tbsrebus_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Evaporasi + Condensate</td>
								<td><input type=text id=condensate_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=condensate_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=condensate_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Luar</td>
								<td><input type=text id=brondolluar_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolluar_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolluar_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Dalam</td>
								<td><input type=text id=brondoldalam_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldalam_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldalam_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>ABN</td>
								<td><input type=text id=abn_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=abn_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=abn_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Calix Leaves & Dirt</td>
								<td><input type=text id=calix_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=calix_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=calix_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Janjang Kosong</td>
								<td><input type=text id=jangkos_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=jangkos_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=jangkos_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>TOTAL</b></td>
								<td><input type=text id=totaltbs_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaltbs_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=totaltbs_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Sampel Check Brondolan</td>
								<td><input type=text id=sampel_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=sampel_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sampel_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=5><b>Sub Sampel Brondolan</b></td>
							</tr>
							<tr>
								<td>Brondolan</td>
								<td><input type=text id=brondolan_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolan_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolan_ur5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>Evaporation</td>
								<td><input type=text id=evaporation_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=evaporation_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=evaporation_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total Brondolan Dry</td>
								<td><input type=text id=brondoldry_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldry_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldry_ur5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>* Mesocrap ( Fiber )</td>
								<td><input type=text id=fiber_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=fiber_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=fiber_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>* Nut</td>
								<td><input type=text id=nut_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=nut_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=nut_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Shell</td>
								<td><input type=text id=shell_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=shell_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=shell_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Kernel</td>
								<td><input type=text id=kernel_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kernel_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kernel_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Kernel Dry</td>
								<td><input type=text id=kerneldry_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kerneldry_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kerneldry_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossestbs_ur2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossestbs_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossestbs_ur5 value=0.00 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=sttotal_ur2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=sttotal_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sttotal_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Oil in Fiber</td>
								<td><input type=text id=oilinfiber_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinfiber_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinfiber_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Oil in Shell</td>
								<td><input type=text id=oilinshell_ur2 value=0 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinshell_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinshell_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Total OIL</b></td>
								<td><input type=text id=totaloil_ur2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaloil_ur4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=totaloil_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossesoil_ur2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossesoil_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossesoil_ur5 value=0.00 onchange=hitung_ur() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=gttotal_ur2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=gttotal_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=gttotal_ur5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td></td>
								<td><input type=text id=hasil_ur2 value=0.0000 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td></td>
								<td><input type=text id=hasil_ur4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=hasil_ur5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
						</table>
					</fieldset>
				</td>

				<td valign=top>  
					<fieldset><legend>Buah Normal Ripe</legend>
						<table>
							<thead><tr>
								<td align=center>DESCRIPTION</td>
								<td align=center>VALUE</td>
								<td align=center>SAT</td>
								<td align=center>%</td>
								<td align=center>% FFB</td>
							</tr></thead>
							<tr>
								<td>Berat TBS</td>
								<td><input type=text id=berattbs_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=berattbs_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=berattbs_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>TBS Rebus</td>
								<td><input type=text id=tbsrebus_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=tbsrebus_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=tbsrebus_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Evaporasi + Condensate</td>
								<td><input type=text id=condensate_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=condensate_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=condensate_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Luar</td>
								<td><input type=text id=brondolluar_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolluar_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolluar_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Dalam</td>
								<td><input type=text id=brondoldalam_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldalam_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldalam_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>ABN</td>
								<td><input type=text id=abn_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=abn_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=abn_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Calix Leaves & Dirt</td>
								<td><input type=text id=calix_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=calix_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=calix_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Janjang Kosong</td>
								<td><input type=text id=jangkos_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=jangkos_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=jangkos_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>TOTAL</b></td>
								<td><input type=text id=totaltbs_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaltbs_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=totaltbs_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Sampel Check Brondolan</td>
								<td><input type=text id=sampel_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=sampel_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sampel_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=5><b>Sub Sampel Brondolan</b></td>
							</tr>
							<tr>
								<td>Brondolan</td>
								<td><input type=text id=brondolan_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolan_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolan_nr5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>Evaporation</td>
								<td><input type=text id=evaporation_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=evaporation_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=evaporation_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total Brondolan Dry</td>
								<td><input type=text id=brondoldry_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldry_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldry_nr5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>* Mesocrap ( Fiber )</td>
								<td><input type=text id=fiber_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=fiber_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=fiber_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>* Nut</td>
								<td><input type=text id=nut_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=nut_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=nut_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Shell</td>
								<td><input type=text id=shell_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=shell_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=shell_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Kernel</td>
								<td><input type=text id=kernel_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kernel_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kernel_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Kernel Dry</td>
								<td><input type=text id=kerneldry_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kerneldry_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kerneldry_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossestbs_nr2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossestbs_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossestbs_nr5 value=0.00 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=sttotal_nr2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=sttotal_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sttotal_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Oil in Fiber</td>
								<td><input type=text id=oilinfiber_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinfiber_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinfiber_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Oil in Shell</td>
								<td><input type=text id=oilinshell_nr2 value=0 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinshell_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinshell_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Total OIL</b></td>
								<td><input type=text id=totaloil_nr2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaloil_nr4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=totaloil_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossesoil_nr2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossesoil_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossesoil_nr5 value=0.00 onchange=hitung_nr() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=gttotal_nr2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=gttotal_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=gttotal_nr5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td></td>
								<td><input type=text id=hasil_nr2 value=0.0000 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td></td>
								<td><input type=text id=hasil_nr4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=hasil_nr5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
						</table>
					</fieldset>
				</td>

				<td valign=top>  
					<fieldset><legend>Buah Over Ripe</legend>
						<table>
							<thead><tr>
								<td align=center>DESCRIPTION</td>
								<td align=center>VALUE</td>
								<td align=center>SAT</td>
								<td align=center>%</td>
								<td align=center>% FFB</td>
							</tr></thead>
							<tr>
								<td>Berat TBS</td>
								<td><input type=text id=berattbs_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=berattbs_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=berattbs_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>TBS Rebus</td>
								<td><input type=text id=tbsrebus_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=tbsrebus_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=tbsrebus_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Umum</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Evaporasi + Condensate</td>
								<td><input type=text id=condensate_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=condensate_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=condensate_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Luar</td>
								<td><input type=text id=brondolluar_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolluar_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolluar_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Brondolan Dalam</td>
								<td><input type=text id=brondoldalam_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldalam_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldalam_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>ABN</td>
								<td><input type=text id=abn_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=abn_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=abn_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Calix Leaves & Dirt</td>
								<td><input type=text id=calix_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=calix_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=calix_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Janjang Kosong</td>
								<td><input type=text id=jangkos_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=jangkos_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=jangkos_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>TOTAL</b></td>
								<td><input type=text id=totaltbs_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaltbs_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=totaltbs_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Sampel Check Brondolan</td>
								<td><input type=text id=sampel_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=sampel_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sampel_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Data Detail</b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=5><b>Sub Sampel Brondolan</b></td>
							</tr>
							<tr>
								<td>Brondolan</td>
								<td><input type=text id=brondolan_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondolan_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondolan_or5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>Evaporation</td>
								<td><input type=text id=evaporation_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=evaporation_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=evaporation_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total Brondolan Dry</td>
								<td><input type=text id=brondoldry_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=brondoldry_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=brondoldry_or5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
							<tr>
								<td>* Mesocrap ( Fiber )</td>
								<td><input type=text id=fiber_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=fiber_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=fiber_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>* Nut</td>
								<td><input type=text id=nut_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=nut_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=nut_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Shell</td>
								<td><input type=text id=shell_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=shell_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=shell_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>- Kernel</td>
								<td><input type=text id=kernel_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kernel_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kernel_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Kernel Dry</td>
								<td><input type=text id=kerneldry_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=kerneldry_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=kerneldry_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossestbs_or2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossestbs_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossestbs_or5 value=0.00 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=sttotal_or2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=sttotal_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=sttotal_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td> </td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td><b>EXTRACTION </b></td><td></td><td></td><td></td><td></td>
							</tr>
							<tr>
								<td>Oil in Fiber</td>
								<td><input type=text id=oilinfiber_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinfiber_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinfiber_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Oil in Shell</td>
								<td><input type=text id=oilinshell_or2 value=0 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=oilinshell_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=oilinshell_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td><b>Total OIL</b></td>
								<td><input type=text id=totaloil_or2 value=0 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td>gram</td>
								<td><input type=text id=totaloil_or4 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td><input type=text id=totaloil_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Absolut Losses</td>
								<td><input type=text id=lossesoil_or2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=lossesoil_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=lossesoil_or5 value=0.00 onchange=hitung_or() class=myinputtextnumber maxlength=8 size=8 onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td>Total</td>
								<td><input type=text id=gttotal_or2 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td></td>
								<td><input type=text id=gttotal_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=gttotal_or5 value=0.00 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
							</tr>
							<tr>
								<td></td>
								<td><input type=text id=hasil_or2 value=0.0000 class=myinputtextnumber maxlength=8 size=8 disabled onkeypress=\"return angka_doang(event);\"></td>
								<td></td>
								<td><input type=text id=hasil_or4 class=myinputtextnumber maxlength=8 size=8 disabled></td>
								<td><input type=text id=hasil_or5 class=myinputtextnumber maxlength=8 size=8 disabled></td>
							</tr>
						</table>
					</fieldset>
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
					<td rowspan=2 align=center>".$_SESSION['lang']['unit']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['divisi']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['blok']."</td>
					<td colspan=8 align=center>Unripe</td>
					<td colspan=8 align=center>Normal Ripe</td>
					<td colspan=8 align=center>Over Ripe</td>
					<td rowspan=2 align=center>Action</td>	   
				</tr>
				<tr class=rowheader> 
					<td align=center>".'Berat TBS'."</td>
					<td align=center>".'TBS Rebus'."</td>
					<td align=center>".'Cond'."</td>
					<td align=center>".'Brondol Luar'."</td>
					<td align=center>".'Brondol Dalam'."</td>
					<td align=center>".'ABN'."</td>
					<td align=center>".'Calix'."</td>
					<td align=center>".'Jangkos'."</td>
					<td align=center>".'Berat TBS'."</td>
					<td align=center>".'TBS Rebus'."</td>
					<td align=center>".'Cond'."</td>
					<td align=center>".'Brondol Luar'."</td>
					<td align=center>".'Brondol Dalam'."</td>
					<td align=center>".'ABN'."</td>
					<td align=center>".'Calix'."</td>
					<td align=center>".'Jangkos'."</td>
					<td align=center>".'Berat TBS'."</td>
					<td align=center>".'TBS Rebus'."</td>
					<td align=center>".'Cond'."</td>
					<td align=center>".'Brondol Luar'."</td>
					<td align=center>".'Brondol Dalam'."</td>
					<td align=center>".'ABN'."</td>
					<td align=center>".'Calix'."</td>
					<td align=center>".'Jangkos'."</td>
				</tr>
			</thead>
			<tbody id=container>";
echo"			<script>loadData(0)</script>";
echo"		</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
CLOSE_BOX();
close_body();
?>
