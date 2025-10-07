<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/bgt_unit.js'></script>
<?
OPEN_BOX('',"<b>".'Budget Unit'."</b>");
//get org
$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sPT="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' and detail='1' order by namaorganisasi asc ";
    $optPT="";
	$qPT=mysql_query($sPT) or die(mysql_error($conn));
	while($rPT=mysql_fetch_assoc($qPT)){
		if($rPT['kodeorganisasi']==$_SESSION['org']['kodeorganisasi'])
			{$select="selected=selected";}
		else
			{$select="";}
		$optPT.="<option ".$select." value=".$rPT['kodeorganisasi'].">".$rPT['namaorganisasi']."</option>";
	}
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where detail='1' and induk='".$_SESSION['org']['kodeorganisasi']."') order by namaorganisasi";
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sVhc="select kodevhc from ".$dbname.".vhc_5master order by kodevhc";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optPT="<option value='".$_SESSION['org']['kodeorganisasi']."'>".$_SESSION['org']['namaorganisasi']."</option>";
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi not like '%HO' and detail='1' and induk='".$_SESSION['org']['kodeorganisasi']."' order by namaorganisasi asc ";	
	$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sVhc="select kodevhc from ".$dbname.".vhc_5master where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."') order by kodevhc";
}else{
	$optPT="<option value='".$_SESSION['org']['kodeorganisasi']."'>".$_SESSION['org']['namaorganisasi']."</option>";
	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";	
	$optUnit="";
	$sVhc="select kodevhc from ".$dbname.".vhc_5master where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."') order by kodevhc";
	$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and induk='".$_SESSION['empl']['lokasitugas']."' and tipe not like 'GUDANG%' order by namaorganisasi";
	$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
	while($rDivisi=mysql_fetch_assoc($qDivisi)){
		if($kdDivisi==$rDivisi['kodeorganisasi'])
			{$select="selected=selected";}
		else
			{$select="";}
		$optDivisi.="<option ".$select." value=".$rDivisi['kodeorganisasi'].">".$rDivisi['namaorganisasi']."</option>";
	}
}

$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
while($rUnit=mysql_fetch_assoc($qUnit)){
	if($rUnit['kodeorganisasi']==$_SESSION['empl']['lokasitugas'])
		{$select="selected=selected";}
	else
		{$select="";}
	$optUnit.="<option value=".$rUnit['kodeorganisasi'].">".$rUnit['namaorganisasi']."</option>";
}

$TahunDepan=date('Y')+1;
$optTahun="<option selected=selected value='".$TahunDepan."'>".$TahunDepan."</option>";
$sTahun="select distinct left(periode,4) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qTahun=mysql_query($sTahun) or die(mysql_error($conn));
while($rTahun=mysql_fetch_assoc($qTahun)){
	$optTahun.="<option value=".$rTahun['tahun'].">".$rTahun['tahun']."</option>";
}

$optBudget="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBudget="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget not like 'EXPL%' and kodebudget not like 'SALES%' order by kodebudget";
$qBudget=mysql_query($sBudget) or die(mysql_error($conn));
while($rBudget=mysql_fetch_assoc($qBudget)){
	$optBudget.="<option value=".$rBudget['kodebudget'].">[".$rBudget['kodebudget']."]-".$rBudget['nama']."</option>";
}

$optKegiatan="<option value=''>".$_SESSION['lang']['all']."</option>";
$sKegiatan="select kodekegiatan,namakegiatan from ".$dbname.".setup_kegiatan where status='1' and (kodekegiatan like '126%' or kodekegiatan like '128%' or kodekegiatan like '61%' or kodekegiatan like '62%' or kodekegiatan like '7%') order by kodekegiatan";
$qKegiatan=mysql_query($sKegiatan) or die(mysql_error($conn));
while($rKegiatan=mysql_fetch_assoc($qKegiatan)){
	$optKegiatan.="<option value=".$rKegiatan['kodekegiatan'].">[".$rKegiatan['kodekegiatan']."]-".$rKegiatan['namakegiatan']."</option>";
}

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
//$sBrg="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where inactive='0' and (kodebarang like '31%' or kodebarang like '35%' or kodebarang like '36%' or kodebarang like '37%' or kodebarang like '38%' or kodebarang like '905%' or kodebarang like '906%' or kodebarang like '909%') and kodebarang not like '384%' order by kodebarang";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$wBrg="";
}else{
	$wBrg=" and a.regional='".$_SESSION['empl']['regional']."' ";
}
$sBrg="select distinct a.kodebarang,b.namabarang,b.satuan from ".$dbname.".bgt_masterbarang a 
		left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
		where b.inactive='0' and a.hargasatuan>0 ".$wBrg." order by kodebarang";
$qBrg=mysql_query($sBrg) or die(mysql_error($conn));
while($rBrg=mysql_fetch_assoc($qBrg)){
	$optBrg.="<option value=".$rBrg['kodebarang'].">[".$rBrg['kodebarang']."]-".$rBrg['namabarang']."</option>";
}

$optVhc="<option value=''>".$_SESSION['lang']['all']."</option>";
$qVhc=mysql_query($sVhc) or die(mysql_error($conn));
while($rVhc=mysql_fetch_assoc($qVhc)){
	$optVhc.="<option value=".$rVhc['kodevhc'].">".$rVhc['kodevhc']."</option>";
}

$optThnTanam="<option value=''>".$_SESSION['lang']['all']."</option>";
$sThnTanam="select distinct tahuntanam from ".$dbname.".setup_blok order by tahuntanam";
$qThnTanam=mysql_query($sThnTanam) or die(mysql_error($conn));
while($rThnTanam=mysql_fetch_assoc($qThnTanam)){
	$optThnTanam.="<option value=".$rThnTanam['tahuntanam'].">".$rThnTanam['tahuntanam']."</option>";
}

echo "<fieldset style='width:730px;'>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td valign=top>
					<table>
						<tr>
							<td><label>".$_SESSION['lang']['pt']."</label></td>
							<td><select id='kdPT' name='kdPT' style='width:235px' onchange='getUnit()'>".$optPT."</select></td>
						</tr>
						<tr>
							<td><label>".$_SESSION['lang']['unit']."</label></td>
							<td><select id='kdUnit' name='kdUnit' style='width:235px' onchange='getDivisi()'>".$optUnit."</select></td>
						</tr>
						<tr>
							<td><label>".$_SESSION['lang']['divisi']."</label></td>
							<td><select id='kdDivisi' name='kdDivisi' style='width:235px' onchange='loadData()'>".$optDivisi."</select></td>
						</tr>
						<tr>
				            <td><label>".$_SESSION['lang']['tahuntanam']."</label></td>
							<td><select id='ThnTanam' name='ThnTanam' style='width:80px'>".$optThnTanam."</select></td>
						</tr>
						<tr>
				            <td><label>".$_SESSION['lang']['tahun']."</label></td>
							<td><select id='Tahun' name='Tahun' style='width:55px' onchange='loadData()'>".$optTahun."</select></td>
							<td><input type=hidden id=addedit name=addedit value='insert'></td>
						</tr>
					</table>
				</td>

				<td valign=top>
					<table>
						<tr>
				            <td><label>".$_SESSION['lang']['budget']."</label></td>
							<td><select id='kdBudget' name='kdBudget' style='width:300px'>".$optBudget."</select></td>
						</tr>
						<tr>
				            <td><label>".$_SESSION['lang']['kegiatan']."</label></td>
							<td><select id='kdKegiatan' name='kdKegiatan' style='width:300px'>".$optKegiatan."</select></td>
						</tr>
						<tr>
				            <td><label>".$_SESSION['lang']['namabarang']."</label></td>
							<td><select id='kdBarang' name='kdBarang' style='width:300px'>".$optBrg."</select></td>
						</tr>
						<tr>
				            <td><label>".$_SESSION['lang']['kodevhc']."</label></td>
							<td><select id='kdVhc' name='kdVhc' style='width:160px'>".$optVhc."</select></td>
						</tr>
						<tr>
							<td><input type=hidden id=idbgtunit></td>
						</tr>
					</table>
				</td>
			</tr>	  
		</table>
		<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='3%' align=center>".$_SESSION['lang']['tahun']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['divisi']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['tahuntanam']."</td>
					<td width='7%' align=center>".$_SESSION['lang']['budget']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['noakun']."</td>
					<td width='15%' align=center>".$_SESSION['lang']['kegiatan']."</td>
					<td width='4%' align=center>".$_SESSION['lang']['satuan']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['volume']."</td>
					<td width='4%' align=center>".$_SESSION['lang']['rotasi']."</td>
					<td width='4%' align=center>".$_SESSION['lang']['hk']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['jumlah']."</td>
					<td width='4%' align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center>".$_SESSION['lang']['kodevhc']."</td>
					<td align=center>".$_SESSION['lang']['total']."</td>
					<td width='3%' align=center>Jan</td>
					<td width='3%' align=center>Feb</td>
					<td width='3%' align=center>Mar</td>
					<td width='3%' align=center>Apr</td>
					<td width='3%' align=center>May</td>
					<td width='3%' align=center>Jau</td>
					<td width='3%' align=center>Jul</td>
					<td width='3%' align=center>Aug</td>
					<td width='3%' align=center>Sep</td>
					<td width='3%' align=center>Oct</td>
					<td width='3%' align=center>Nov</td>
					<td width='3%' align=center>Des</td>
					<td width='3%' align=center>Action</td>	   
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
