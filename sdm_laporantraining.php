<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	//Pilih PT
	$optPt="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select DISTINCT(b.kodeorganisasi) as kodeorganisasi,c.namaorganisasi from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=b.kodeorganisasi
			where c.detail='1' order by c.namaorganisasi";
		$optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optPt.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Unit
	$optUnit="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select DISTINCT(if(b.subbagian='',b.lokasitugas,left(subbagian,4))) as kodeorganisasi,c.namaorganisasi from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
			order by c.namaorganisasi";
		$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe!='HOLDING' 
			order by namaorganisasi";
		$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
	}else{
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	}
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
	}

	//Pilih Bagian
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=b.kodeorganisasi
			LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
			order by d.nama";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
			LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
			where c.induk='".$_SESSION['empl']['kodeorganisasi']."' and c.tipe!='HOLDING'
			order by d.nama";
	}else{
		$i="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
			where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
			order by d.nama";
	}
	$optDept="<option value=''>".$_SESSION['lang']['all']."</option>";
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optDept.="<option value='".$d['kode']."'>".$d['nama']."</option>";
	}

	//Pilih Karyawan
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$i="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			order by b.namakaryawan";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$i="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
			where c.induk='".$_SESSION['empl']['kodeorganisasi']."' and c.tipe!='HOLDING'
			order by b.namakaryawan";
	}else{
		$i="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
			where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
			order by b.namakaryawan";
	}
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	$n=mysql_query($i) or die (mysql_error($conn));
	while($d=mysql_fetch_assoc($n)){
		$optKary.="<option value='".$d['karyawanid']."'>".$d['namakaryawan']." - [".$d['nik']."]</option>";
	}

	//Pilih Kategori Training
	$sJenis="select kodetraining,jenistraining from ".$dbname.".sdm_5jenistraining where status='1' order by kodetraining";
	$qJenis=mysql_query($sJenis) or die(mysql_error());
	$optJenis ="<option value=''>".$_SESSION['lang']['all']."</option>";
	while($rJenis=mysql_fetch_assoc($qJenis)){
		$optJenis.="<option value=".$rJenis['kodetraining'].">".$rJenis['jenistraining']."</option>"; 
	}
	$arr="##kodept##kodeunit##kodedept##idkary##kodejenis##tgl_1##tgl_2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function getUnit(){
	    kodept=document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
		param='kodept='+kodept+'&proses=getUnit';
	    tujuan='sdm_laporantraining_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//document.getElementById('kodeunit').innerHTML=con.responseText;
						ar=con.responseText.split("###");
						document.getElementById('kodeunit').innerHTML=ar[0];
						document.getElementById('kodedept').innerHTML=ar[1];
						document.getElementById('idkary').innerHTML=ar[2];
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getDept(){
	    kodept=document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
	    kodeunit=document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
		param='kodept='+kodept+'&kodeunit='+kodeunit+'&proses=getDept';
	    tujuan='sdm_laporantraining_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//document.getElementById('kodeunit').innerHTML=con.responseText;
						ar=con.responseText.split("###");
						document.getElementById('kodedept').innerHTML=ar[0];
						document.getElementById('idkary').innerHTML=ar[1];
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getKary(){
	    kodept=document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
	    kodeunit=document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
	    kodedept=document.getElementById('kodedept').options[document.getElementById('kodedept').selectedIndex].value;
		param='kodept='+kodept+'&kodeunit='+kodeunit+'&kodedept='+kodedept+'&proses=getKary';
	    tujuan='sdm_laporantraining_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('idkary').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function showpopup(mandorid,karyawanid,tanggal,kdorg,afdid,pengawas,ev){
		param='mandorid='+mandorid+'&karyawanid='+karyawanid+'&tanggal='+tanggal+'&kdorg='+kdorg+'&afdid='+afdid+'&pengawas='+pengawas;
		tujuan='sdm_laporantraining_slave.php'+"?"+param;
		width='1170';
		height='470';
		content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
		showDialog1('No Transaksi Premi Panen '+afdid+' '+mandorid+' '+karyawanid+' '+tanggal,content,width,height,ev); 
	}

	function cekcek(apa){
		if(apa.checked)apa.value="1"; else apa.value="0";
	}

	function Clear1(){
		document.getElementById('kodept').value='';
		document.getElementById('kodeunit').value='';
		document.getElementById('kodedept').value='';
		document.getElementById('idkary').value='';
		document.getElementById('kodejenis').value='';
		document.getElementById('tgl_1').value='';
		document.getElementById('tgl_2').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['laporan']." ".$_SESSION['lang']['rekap']." Training";?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['pt'];?></label></td>
				<td><select id="kodept" name="kodept" onchange=getUnit()><?php echo $optPt;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kodeunit" name="kodeunit" onchange=getDept()><?php echo $optUnit;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['bagian'];?></label></td>
				<td><select id="kodedept" name="kodedept" onchange=getKary()><?php echo $optDept;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['karyawan'];?></label></td>
				<td><select id="idkary" name="idkary" style="width:250px"><?php echo $optKary;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['jenis'];?></label></td>
				<td><select id="kodejenis" name="kodejenis" style="width:250px"><?php echo $optJenis;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['tanggal'];?></label></td>
				<td><input type="text" class="myinputtext" id="tgl_1" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10" 		onblur="clear1()" /> s.d. 
					<input type="text" class="myinputtext" id="tgl_2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10"  onblur="clear1()" />
				</td>
			</tr>
			<tr height="20"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('sdm_laporantraining_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'sdm_laporantraining_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
					<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'>
	<legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:310px;max-width:1310px'></div>
</fieldset>

<?php
	CLOSE_BOX();
	echo close_body();
?>
