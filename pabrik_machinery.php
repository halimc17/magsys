<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX('',"<b>Data ".$_SESSION['lang']['mesin']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
	nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
	nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
	nmTmblSave='<?php echo $_SESSION['lang']['save']?>';
	nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script language="javascript" src="js/pabrik_machinery.js"></script>
<input type="hidden" id="proses" name="proses" value="insert" />

<div id="action_list">
	<?php
		//$optPeriode="";
		//for($x=0;$x<=24;$x++){
		//	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
		//	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
		//}
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='STATION' ORDER BY `namaorganisasi`";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='STATION' and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
			ORDER BY `namaorganisasi`";
		}else{
			$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='STATION' and induk='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ORDER BY `namaorganisasi`";
		}
		$query=mysql_query($sql) or die(mysql_error());
		$optOrg="";
		while($res=mysql_fetch_assoc($query)){
			$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>"; 
		}
		echo"<table cellspacing=1 border=0>
				<tr valign=moiddle>
					<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
						<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
					<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
						<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
					<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
						echo $_SESSION['lang']['station']." : <select id=kdOrgCr><option value=''></option>".$optOrg."</select>&nbsp;";
						echo"<button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button>";
		echo"</fieldset></td>
				</tr>
			</table> "; 
	?>
</div>
<?php
	CLOSE_BOX();
?>

<div id="listData">
	<?php OPEN_BOX()?>
	<fieldset>
		<legend><?php echo $_SESSION['lang']['list']?></legend>
		<div id="contain">
			<script>loadData();</script>
		</div>
	</fieldset>
	<?php CLOSE_BOX()?>
</div>

<div id="headher" style="display:none">
	<?php
		OPEN_BOX();
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
	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg onchange=getStasiun()>".$optorg."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['station']."</td>
							<td><select id=stasiun onchange=getMesin() style='width:340px'>".$optStation."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['mesin']."</td>
							<td><select id=mesin onchange=loadDetailData() style='width:340px'>".$optMesin."</select></td>
						</tr>
						<tr>
							<td>Sub ".$_SESSION['lang']['mesin']."</td>
							<td><select id=submesin onchange=loadDetailData()>
									<option value='1'>Elektromotor</option>
									<option value='2'>Generator</option>
									<option value='3'>Door</option>
									<option value='4'>GearBox</option>
									<option value='5'>Unit</option>
									<option value='6'>Auxilary</option>
									<option value='7'>HeaterBank</option>
								</select>
								<input type=text id=stsmesin value=0 class=myinputtextnumber maxlength=5 size=3 onkeypress=\"return angka_doang(event);\"> %
							</td>
						</tr>
						<tr>
							<td>Unit 1</td>
							<td><input type=text id=unit1 class=myinputtext maxlength=30 size=12> 
								<input type=text id=stsunit1 value=0 class=myinputtextnumber maxlength=5 size=3 onkeypress=\"return angka_doang(event);\"> %
							</td>
						</tr>
						<tr>
							<td>Unit 2</td>
							<td><input type=text id=unit2 class=myinputtext maxlength=30 size=12> 
								<input type=text id=stsunit2 value=0 class=myinputtextnumber maxlength=5 size=3 onkeypress=\"return angka_doang(event);\"> %
							</td>
						</tr>
						<tr>
							<td>Merk</td>
							<td><input type=text id=merk class=myinputtext maxlength=30 size=50></td>
						</tr>
						<tr>
							<td>Model</td>
							<td><input type=text id=model class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td><input type=hidden id=addedit name=addedit value='insert'></td>
						</tr>
					</table>
				</td>
				<td valign=top>  
  					<table>
						<tr>
							<td>Tahun</td>
							<td><input type=text id=tahunbuat class=myinputtextnumber maxlength=4 size=4 onkeypress=\"return angka_doang(event);\">
								&nbsp Ratio
								<input type=text id=ratio class=myinputtextnumber maxlength=6 size=5 onkeypress=\"return angka_doang(event);\">
								&nbsp Rpm	
								<input type=text id=rpm class=myinputtextnumber maxlength=6 size=5 onkeypress=\"return angka_doang(event);\">
								&nbsp KW 
								<input type=text id=kw class=myinputtextnumber maxlength=6 size=5 onkeypress=\"return angka_doang(event);\">
							</td>
						</tr>
						<tr>
							<td>Ampere</td>
							<td><input type=text id=ampere class=myinputtext maxlength=30 size=30></td>
						</tr>
						<tr>
							<td>Serial Number</td>
							<td><input type=text id=sn class=myinputtext maxlength=30 size=30>
							</td>
						</tr>
						<tr>
							<td>Sproket 1</td>
							<td><input type=text id=sproket1 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Sproket 2</td>
							<td><input type=text id=sproket2 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Sproket 3</td>
							<td><input type=text id=sproket3 class=myinputtext maxlength=70 size=40>
								<input type=text id=stssproket value=0 class=myinputtextnumber maxlength=5 size=3 onkeypress=\"return angka_doang(event);\"> %
							</td>
						</tr>
						<tr>
							<td>Chain 1</td>
							<td><input type=text id=chain1 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Chain 2</td>
							<td><input type=text id=chain2 class=myinputtext maxlength=70 size=40>
								<input type=text id=stschain value=0 class=myinputtextnumber maxlength=5 size=3 onkeypress=\"return angka_doang(event);\"> %
							</td>
						</tr>
					</table>	
				</td>
				<td valign=top>  
  					<table>
						<tr>
							<td>Pully 1</td>
							<td><input type=text id=pully1 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Pully 2</td>
							<td><input type=text id=pully2 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Van Belt</td>
							<td><input type=text id=vbelt class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Coupling</td>
							<td><input type=text id=coupling class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Bearing 1</td>
							<td><input type=text id=bearing1 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Bearing 2</td>
							<td><input type=text id=bearing2 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Bearing 3</td>
							<td><input type=text id=bearing3 class=myinputtext maxlength=70 size=50></td>
						</tr>
						<tr>
							<td>Merk HM</td>
							<td><input type=text id=merkhm class=myinputtext maxlength=30 size=35></td>
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
		$bgclr="";
echo "<div style='overflow:auto; height:300px;'>
		<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead align=center>
				<tr class=rowheader>
			        <td ".$bgclr.">".$_SESSION['lang']['kode']."</td>
				    <td ".$bgclr." width='300px'>".$_SESSION['lang']['nmmesin']."</td>
					<td ".$bgclr." width='300px'>Sub ".$_SESSION['lang']['mesin']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['status']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['unit']."1</td>
					<td ".$bgclr.">".$_SESSION['lang']['status']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['unit']."2</td>
					<td ".$bgclr.">".$_SESSION['lang']['status']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['merk']."</td>
					<td ".$bgclr.">Type/Model</td>
					<td ".$bgclr.">Ratio</td>
					<td ".$bgclr.">Rpm</td>
					<td ".$bgclr.">KW</td>
					<td ".$bgclr.">Ampere</td>
					<td ".$bgclr.">".$_SESSION['lang']['tahun']."</td>
					<td ".$bgclr.">Serial Number</td>
					<td ".$bgclr." colspan=3>Sproket</td>
					<td ".$bgclr.">".$_SESSION['lang']['status']."</td>
					<td ".$bgclr." colspan=2>Chain</td>
					<td ".$bgclr.">".$_SESSION['lang']['status']."</td>
					<td ".$bgclr." colspan=2>Pully</td>
					<td ".$bgclr.">V-Belt</td>
					<td ".$bgclr.">Coupling</td>
					<td ".$bgclr." colspan=3>Bearing</td>
					<td ".$bgclr.">HM</td>
					<td ".$bgclr.">Action</td>
				</tr>
			</thead>
			<tbody id=contentDetail>";
echo"<script></script>";
echo"	
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>
</div>";
		CLOSE_BOX();
		close_body();
?>
