<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/sdm_lemburverifikasi.js'></script>
<?
OPEN_BOX('',"<b>".$_SESSION['lang']['verifikasi'].' '.$_SESSION['lang']['lembur']."</b>");
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and length(kodeorganisasi)='4' order by namaorganisasi";
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and kodeorganisasi not like '%HO' and induk='".$_SESSION['empl']['induk']."' order by namaorganisasi";	
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
}else{
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";	
	$optUnit="";
}
//exit('Warning: '.$sUnit);
$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
while($rUnit=mysql_fetch_assoc($qUnit)){
	if($rUnit['kodeorganisasi']==$_SESSION['empl']['lokasitugas'])
		{$select="selected=selected";}
	else
		{$select="";}
	$optUnit.="<option ".$select." value=".$rUnit['kodeorganisasi'].">".$rUnit['namaorganisasi']."</option>";
}

$optperiode="";
$sTahun="select distinct left(periode,7) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qTahun=mysql_query($sTahun) or die(mysql_error($conn));
$no=0;
while($rTahun=mysql_fetch_assoc($qTahun)){
	$no+=1;
	if($no==1)
		{$select="selected=selected";}
	else
		{$select="";}
	$optperiode.="<option ".$select." value=".$rTahun['periode'].">".$rTahun['periode']."</option>";
}

echo "<fieldset style='width:720px;'>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td><label>".$_SESSION['lang']['unit']."</label></td>
				<td><select id='kdUnit' name='kdUnit' style='width:235px'>".$optUnit."</select></td>
			</tr>
			<tr>
	            <td><label>".$_SESSION['lang']['periode']."</label></td>
				<td><select id='periode' name='periode' style='width:80px' onchange='loadData()'>".$optperiode."</select></td>
			</tr>
		</table>
		<button class=mybutton onclick=loadData()>".$_SESSION['lang']['preview']."</button>
		<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='5%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='10%' align=center>".$_SESSION['lang']['nik']."</td>
					<td width='18%' align=center>".$_SESSION['lang']['namakaryawan']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['tipelembur']."</td>
					<td width='9%' align=center>".$_SESSION['lang']['jamaktual']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['penggantiantransport']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['uangmakan']."</td>
					<td width='18%' align=center>No. BA</td>
					<td width='9%' align=center>Action</td>	   
				</tr>
			</thead>
			<tbody id=container>";
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
	echo"<script>loadData()</script>";
}
echo"
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
CLOSE_BOX();
close_body();
?>
