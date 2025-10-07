<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pabrik_preventifpanel.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?
include('master_mainMenu.php');
//get org
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' ORDER BY kodeorganisasi";
	$resk=mysql_query($str);
	$kodeorg='';
	while($bark=mysql_fetch_object($resk)){
		$kodeorg=$bark->kodeorganisasi;
		break;
	}
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and induk='".$kodeorg."'";
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 
			and induk in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe='PABRIK' and induk='".$_SESSION['empl']['induk']."')";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
	$str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='STATION'";
}
//exit('Warning: '.$str3);
$res=mysql_query($str);
$optorg='';
$nu=0;
while($bar=mysql_fetch_object($res)){
	$nu+=1;
	if($nu==1){
		$sele=" selected=selected ";
	}else{
		$sele="";
	}
	$optorg.="<option ".$sele." value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."] ".$bar->namaorganisasi."</option>";
}
$res2=mysql_query($str2);
$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
//$optStation="";
while($bar2=mysql_fetch_object($res2)){
	$optStation.="<option value='".$bar2->kodeorganisasi."'>[".$bar2->kodeorganisasi."] ".$bar2->namaorganisasi."</option>";
}

OPEN_BOX();
$frm[0]='';
$frm[1]='';

$frm[0].="<fieldset style='float:left;'><legend><b>Form ".$_SESSION['lang']['realisasi']."</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorg onchange=getStasiun() style=\"width:300px;\" >".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td><select id=stasiun onchange=loadData() style=\"width:300px;\">".$optStation."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td><input type=text id=keterangan class=myinputtext maxlength=100 style=\"width:300px;\"></td>
				</tr>
				<tr>
					<td><input type=hidden id=tgllama name=tgllama></td>
					<td><input type=hidden id=jenis name=jenis value='Real'>&nbsp;</td>
					<td><input type=hidden id=addedit name=addedit value='insert'></td>
				</tr>
			</table>
			<center>
				<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
			</center>
		</fieldset>";

$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['realisasi']."</b></legend>
			<table class=sortable cellspacing=1 border=0 width=100%>
				<thead>
					<tr class=rowheader>
						<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
						<td width='6%' align=center>".$_SESSION['lang']['kode']."</td>
						<td width='30%' align=center>".$_SESSION['lang']['station']."</td>
						<td width='4%' align=center>".$_SESSION['lang']['jenis']."</td>
						<td width='7%' align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['keterangan']."</td>
						<td width='7%' align=center>Action</td>	   
					</tr>
				</thead>
				<tbody id=container>
					<script>loadData()</script>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</fieldset>";

###form input II
################
$frm[1].="<fieldset style='float:left;'><legend><b>Form ".$_SESSION['lang']['rencana']."</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorgP onchange=getStasiunP() style=\"width:300px;\" >".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td><select id=stasiunP onchange=loadDataP() style=\"width:300px;\">".$optStation."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tanggalP' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td><input type=text id=keteranganP class=myinputtext maxlength=100 style=\"width:300px;\"></td>
				</tr>
				<tr>
					<td><input type=hidden id=tgllamaP name=tgllamaP></td>
					<td><input type=hidden id=jenisP name=jenisP value='Plan'>&nbsp;</td>
					<td><input type=hidden id=addeditP name=addeditP value='insert'></td>
				</tr>
			</table>
			<center>
				<button class=mybutton onclick=simpanDataP()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=bersihkanFormP()>".$_SESSION['lang']['cancel']."</button>
			</center>
		</fieldset>";

$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['rencana']."</b></legend>
			<table class=sortable cellspacing=1 border=0 width=100%>
				<thead>
					<tr class=rowheader>
						<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
						<td width='6%' align=center>".$_SESSION['lang']['kode']."</td>
						<td width='30%' align=center>".$_SESSION['lang']['station']."</td>
						<td width='4%' align=center>".$_SESSION['lang']['jenis']."</td>
						<td width='7%' align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['keterangan']."</td>
						<td width='7%' align=center>Action</td>	   
					</tr>
				</thead>
				<tbody id=containerP>
					<script></script>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</fieldset>";

$hfrm[0]=$_SESSION['lang']['realisasi'];
$hfrm[1]=$_SESSION['lang']['rencana'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();
?>
