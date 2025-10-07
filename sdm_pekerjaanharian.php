<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX('',"<b>Pencapaian Dan Target Pekerjaan</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
	nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
	nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
	nmTmblSave='<?php echo $_SESSION['lang']['save']?>';
	nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script language="javascript" src="js/sdm_pekerjaanharian.js"></script>
<input type="hidden" id="proses" name="proses" value="insert" />

<div id="action_list">
	<?php
		$optSts="<option value=''></option>";
		$optSts.="<option value='Selesai'>Selesai</option>";
		$optSts.="<option value='Lanjut'>Lanjut</option>";
		$optSts.="<option value='Tunda'>Tunda</option>";
		echo"<table cellspacing=1 border=0>
				<tr valign=moiddle>
					<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
						<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
					<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
						<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
					<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false; size=10 maxlength=10 />&nbsp;&nbsp;";
		echo $_SESSION['lang']['status']." : <select id=sts_cari>".$optSts."</select>&nbsp;";
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
	$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan 
		where (lokasitugas like '%HO' or subbagian like '%HO%') and (kodegolongan in ('DIR','KOM') or kodegolongan like '%MGR%' ) 
		and (tanggalkeluar='0000-00-00' or tanggalkeluar>CURDATE()) order by namakaryawan";
	$res=mysql_query($str);
	$optAtasan="<option value=''></option>";
	while($bar=mysql_fetch_object($res)){
		//$optAtasan.="<option value='".$bar->karyawanid."'>[".$bar->nik."] ".$bar->namakaryawan."</option>";
		$optAtasan.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
	}
	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td><input type=hidden id=nomor name=nomor></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td><input type=text class=myinputtext onchange=loadDetailData() id=tanggal size=10 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['pekerjaan']."</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=pekerjaan style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>Target</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=target style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['aktual']."</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=aktual style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>Correction Action</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=correction style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>Rencana Kerja</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=rencanakerja style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['catatan']."</td>
				<td><textarea onkeypress=\"return tanpa_kutip(event)\" id=catatan style=\"width:1100px;\" rows=1></textarea></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['atasan']."</td>
				<td><select id=atasan>".$optAtasan."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['status'].' '.$_SESSION['lang']['pekerjaan']."</td>
				<td><select id=stspekerjaan>".$optSts."</select></td>
			</tr>
			<tr>
				<td><input type=hidden id=addedit name=addedit value='insert'></td>
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
				    <td ".$bgclr." width='75px'>".$_SESSION['lang']['tanggal']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['pekerjaan']."</td>
					<td ".$bgclr.">Target</td>
					<td ".$bgclr.">".$_SESSION['lang']['aktual']."</td>
					<td ".$bgclr.">Correction Action</td>
					<td ".$bgclr.">Rencana Kerja</td>
					<td ".$bgclr.">".$_SESSION['lang']['catatan']."</td>
					<td ".$bgclr.">".$_SESSION['lang']['atasan']."</td>
					<td ".$bgclr." width='50px'>".$_SESSION['lang']['status']."</td>
					<td ".$bgclr." width='60px'>Action</td>
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
