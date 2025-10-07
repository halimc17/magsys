<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$arr="##karyawanid##tanggal1##tanggal2##pekerjaanx##atasanx##stskerja##stsposting";
if(strstr($_SESSION['empl']['bagian'],'HR') or strstr($_SESSION['empl']['bagian'],'IT') or strstr($_SESSION['empl']['bagian'],'MIS')){
	$whrKary="";
}else{
	$whrKary="and (a.karyawanid='".$_SESSION['empl']['userid']."' or a.atasan='".$_SESSION['empl']['userid']."')";
}
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sKary="select distinct a.karyawanid,b.nik,b.namakaryawan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			where (b.lokasitugas like '%HO' or b.subbagian like '%HO%') and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>CURDATE()) ".$whrKary." 
			order by b.namakaryawan";
	$sHead="select distinct a.atasan,b.nik,b.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.atasan
			where (b.lokasitugas like '%HO' or b.subbagian like '%HO%') order by b.namakaryawan";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sKary="select distinct a.karyawanid,b.nik,b.namakaryawan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			where (b.lokasitugas not like '%HO' and b.kodeorganisasi='".$_SESSION['empl']['induk']."') and (b.tanggalkeluar='0000-00-00' 
			or b.tanggalkeluar>CURDATE()) ".$whrKary." 
			order by b.namakaryawan";
	$sHead="select distinct a.atasan,b.nik,b.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.atasan
			where (b.lokasitugas not like '%HO' and b.kodeorganisasi='".$_SESSION['empl']['induk']."') order by b.namakaryawan";
}else{
	$sKary="select distinct a.karyawanid,b.nik,b.namakaryawan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			where (b.lokasitugas='".$_SESSION['empl']['lokasitugas']."') and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>CURDATE()) ".$whrKary." 
			order by b.namakaryawan";
	$sHead="select distinct a.atasan,b.nik,b.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.atasan
			where (b.lokasitugas not like '%HO' and b.kodeorganisasi='".$_SESSION['empl']['induk']."') order by b.namakaryawan";
}
$qKary=mysql_query($sKary) or die(mysql_error($conn));
if(mysql_num_rows($qKary)>1){
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
}else{
	$optKary="";
}
while($rKary=mysql_fetch_assoc($qKary)){
	$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']."</option>";
}
$qHead=mysql_query($sHead) or die(mysql_error($conn));
$optHead="<option value=''>".$_SESSION['lang']['all']."</option>";
while($rHead=mysql_fetch_assoc($qHead)){
	$optHead.="<option value=".$rHead['atasan'].">".$rHead['namaatasan']."</option>";
}
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>

function showpopup(karyawanid,namakaryawan,tanggal1,type,ev){
   param='karyawanid='+karyawanid+'&namakaryawan='+namakaryawan+'&tanggal1='+tanggal1+'&type='+type;
   tujuan='sdm_pekerjaanharian_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Pencapaian Dan Target Pekerjaan '+karyawanid+' '+namakaryawan+' '+tanggal1,content,width,height,ev); 
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1(){
    document.getElementById('tanggal1').value='';
    document.getElementById('tanggal2').value='';
    document.getElementById('pekerjaanx').value='';
    document.getElementById('atasanx').value='';
    document.getElementById('stskerja').value='';
    document.getElementById('stsposting').value='';
}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['laporan']." Pencapaian Pekerjaan ";?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['namakaryawan'];?></label></td>
				<td><select id="karyawanid" name="karyawanid"><?php echo $optKary;?></select></td>
			</tr>
			<tr>
	            <td><?php echo $_SESSION['lang']['tanggal'];?></td>
	            <td><input type='text' class='myinputtext' id='tanggal1' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10' >
		        s/d
			    <input type='text' class='myinputtext' id='tanggal2' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10' ></td>
			</tr>
			<tr>
	            <td><?php echo $_SESSION['lang']['pekerjaan'];?></td>
	            <td><input type='text' class='myinputtext' id='pekerjaanx' size='80' maxlength='255'></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['atasan'];?></label></td>
				<td><select id="atasanx" name="atasanx"><?php echo $optHead;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['status'];?></label></td>
				<td><select id="stskerja" name="stskerja">
						<option value=''><?php echo $_SESSION['lang']['all'];?></option>
						<option value='Selesai'>Selesai</option>
						<option value='Lanjut'>Lanjut</option>
						<option value='Tunda'>Tunda</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['posting'];?></label></td>
				<td><select id="stsposting" name="stsposting">
						<option value='1'>Posting</option>
						<option value='0'>Unposting</option>
						<option value=''><?php echo $_SESSION['lang']['all'];?></option>
					</select>
				</td>
			</tr>
			<tr height="5"><td colspan="2"></td></tr>
			<tr><td colspan="2">
				<button onclick="zPreview('sdm_pekerjaanharian_showpopup','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
				<button onclick="zExcel(event,'sdm_pekerjaanharian_showpopup.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
				<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>

<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:375px;max-width:1235px'></div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>
