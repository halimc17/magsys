<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX('',"<b>Training</b>");
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src="js/zTools.js"></script>
<script language="javascript" src="js/sdm_training.js"></script>
<script language=javascript1.2 src=js/generic.js></script>

<?php
	$tahun=date("Y");
	$sKary="select karyawanid,nik,namakaryawan,lokasitugas from ".$dbname.".datakaryawan 
			where tipekaryawan not in (4,5) and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$tahun."-01-01')
			order by namakaryawan";
	$qKary=mysql_query($sKary) or die(mysql_error());
	$optKary="<option value=''>Pilih Data</option>";
	while($rKary=mysql_fetch_assoc($qKary)){
		$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." - [".$rKary['nik']."] - [".$rKary['lokasitugas']."]</option>"; 
	}

	$sJenis="select kodetraining,jenistraining from ".$dbname.".sdm_5jenistraining where status='1' order by kodetraining";
	$qJenis=mysql_query($sJenis) or die(mysql_error());
	$optJenis="";
	while($rJenis=mysql_fetch_assoc($qJenis)){
		$optJenis.="<option value=".$rJenis['kodetraining'].">".$rJenis['jenistraining']."</option>"; 
	}

	$sList="select DISTINCT(penyelenggara) from ".$dbname.".sdm_karyawantraining order by penyelenggara";
	$qList=mysql_query($sList) or die(mysql_error());
	while($rList=mysql_fetch_assoc($qList)){
		$optList.="<option value=".$rList['penyelenggara'].">".$rList['penyelenggara']."</option>"; 
	}
	$arrPrm="##idkary##judultraining##jenistraining##tanggal1##tanggal2##penyelenggara##sertifikat##biayatraining";
	$arrPrm.="##berlakudari##berlakusampai##nmkary";
	//$arrPrm.="##berlakudari##berlakusampai##scansertifikat##nmkary";
?>

<div id="headher">
	<fieldset>
		<legend><?php echo $_SESSION['lang']['karyawan']?></legend>
		<table cellspacing="1" border="0">
			<tr>
				<td><?php echo $_SESSION['lang']['nik']?></td>
				<td>
					<select id="idkary" name="idkary" onchange="getKary()"><?php echo $optKary;?></select>
					<input type="hidden" id="nokary" name="nokary">
					<input type="hidden" id="nikary" name="nikary">
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['namakaryawan']?></td>
				<td>
					<input type="text" style="width:250px" id="nmkary" name="nmkary" class="myinputtext" readonly/>
				</td>
				<td><?php echo $_SESSION['lang']['lokasitugas']?></td>
				<td>
					<input type="text" style="width:125px" id="lkkary" name="lkkary" class="myinputtext" readonly/>
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['jabatan']?></td>
				<td>
					<input type="text" style="width:250px" id="jbkary" name="jbkary" class="myinputtext" readonly/>
				</td>
				<td><?php echo $_SESSION['lang']['masakerja']?></td>
				<td>
					<input type="text" style="width:125px" id="mkkary" name="mkkary" class="myinputtext" readonly/>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset>
		<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
		<table cellspacing="1" border="0">
			<tr>
				<td><?php echo ($_SESSION['language']=='EN'?'Title of Training':'Judul Training');?></td>
				<td>
					<input type="text" style="width:350px" id="judultraining" name="judultraining" class="myinputtext" />
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['jenis']?></td>
				<td>
					<select id="jenistraining" name="jenistraining""><?php echo $optJenis;?></select>
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['tanggal']?></td>
				<td>
					<input type=text id=tanggal1 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
					s/d 
					<input type=text id=tanggal2 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['penyelenggara']?></td>
				<td>
					<input type="text" style="width:170px" id="penyelenggara" name="penyelenggara" list="listpenyelenggara" class="myinputtext" />
					<datalist id="listpenyelenggara"><?php echo $optList;?></datalist>
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['biaya']?></td>
				<td>
					<input type="text" style="width:90px" id="biayatraining" name="biayatraining" class="myinputtextnumber" value=0 maxlength="12" onKeyPress="return angka_doang(event)"/>
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['sertifikat']?></td>
				<td>
					<select id="sertifikat" name="sertifikat" onchange="getBerlaku()">
						<option value="0"><?php echo ($_SESSION['language']=='EN'?'No Certificate':'Tidak Ada Sertifikat');?></option>
						<option value="1"><?php echo ($_SESSION['language']=='EN'?'Attendance Certificate':'Sertifikat Kehadiran');?></option>
						<option value="2"><?php echo ($_SESSION['language']=='EN'?'Competency Certificate':'Sertifikat Kompetensi');?></option>
					</select>
				</td>
			</tr>
			<table cellspacing='1' border='0' id='berlaku' style='display:none;'>
			<tr>
				<td><?php echo $_SESSION['lang']['tglberlaku']?></td>
				<td>
					<input type="text" id=berlakudari class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
					s/d 
					<input type="text" id=berlakusampai class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
				</td>
			<!--	<td>
					<form id='sertUpload' enctype='multipart/form-data' method='post' action='sdm_training_slave_savesertifikat.php'>	
						<input type=hidden name=MAX_FILE_SIZE value=1000000>
						<input type=file id=scansertifikat name=scansertifikat size=100>
						<input type=hidden name=idkaryx id=idkaryx value=idkary>
						<input type=hidden name=penyelenggarax id=penyelenggarax value=penyelenggara>
						<input type=hidden name=tanggal1x id=tanggal1x value=tanggal1>
					</form>
				</td>	-->
			</tr>
			</table>
			<tr>
				<td colspan="3" id="tmbLheader">
					<button class="mybutton" id="dtlAbn" onclick="saveData('<?php echo $arrPrm ?>')"><?php echo $_SESSION['lang']['save']?></button><button class="mybutton" id="cancelAbn" onclick="cancelSave()"><?php echo $_SESSION['lang']['cancel']?></button>
				</td>
			</tr>
		</table>
		<input type="hidden" id="proses" name="proses" value="insert"/>
	</fieldset>
</div>

<?php
	CLOSE_BOX();
?>

<div id="listData">
	<?php OPEN_BOX() ?>
	<fieldset>
		<legend><?php echo $_SESSION['lang']['list']?></legend>
		<table cellspacing="1" border="0">
			<thead>
				<tr class="rowheader">
					<td align=center>No.</td>
					<td>Judul Training</td>
					<td><?php echo $_SESSION['lang']['jenis'];?></td> 
					<td align=center><?php echo $_SESSION['lang']['tanggalmulai'];?></td>
					<td align=center><?php echo $_SESSION['lang']['tanggalselesai'];?></td>	
					<td><?php echo $_SESSION['lang']['penyelenggara'];?></td>	 
					<td align=center><?php echo $_SESSION['lang']['sertifikat'];?></td>
					<td align=center><?php echo $_SESSION['lang']['biaya'];?></td>
					<td align=center>Action</td>
					<td align=center>Scan</td>
				</tr>
			</thead>
			<tbody id="contain">
				<script></script>
			</tbody>
		</table>
	</fieldset>
	<?php CLOSE_BOX()?>
</div>

<?php 
	echo close_body();
?>
