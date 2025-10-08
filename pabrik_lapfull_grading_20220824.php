<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script>
	function batalLaporan(num){
		if(num==0){
			document.getElementById('divisi0').value='';
			document.getElementById('printContainer0').innerHTML='';
		}
		if(num==1){
			document.getElementById('divisi1').value='';
			document.getElementById('printContainer1').innerHTML='';
		}
		if(num==2){
			document.getElementById('printContainer2').innerHTML='';
		}
	}

	function getdSub(){
	    kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;
		param='kebun0='+kebun0+'&proses=getSubUnit';
		tujuan='pabrik_lapfull_grading_per_hari.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else {
						//alert(con.responseText);
						document.getElementById('divisi0').innerHTML=con.responseText;
					}
				}else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getSub(){
	    kebun1=document.getElementById('kebun1').options[document.getElementById('kebun1').selectedIndex].value;
		param='kebun1='+kebun1+'&proses=getSubUnit';
		tujuan='pabrik_lapfull_grading_per_divisi.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else {
						//alert(con.responseText);
						document.getElementById('divisi1').innerHTML=con.responseText;
					}
				}else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

</script>
<?php
	include('master_mainMenu.php');

	##untuk pilihan Pabrik
	$optPabrik="";
	//$sPabrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and detail='1'";
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sPabrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PABRIK' and kodeorganisasi like '%M'";
	}else{
		$sPabrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PABRIK' and kodeorganisasi like '%M' 
				and kodeorganisasi in (SELECT kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
	}
	//exit('Warning: '.$sPabrik);
	$qPabrik=mysql_query($sPabrik) or die(mysql_error($conn));
	$no=0;
	$dspPabrik="";
	while($dPabrik=mysql_fetch_assoc($qPabrik)){
		$no+=1;
		if($no==1){
			$dspPabrik=$dPabrik['kodeorganisasi'];
		}
		$optPabrik.="<option value=".$dPabrik['kodeorganisasi'].">".$dPabrik['namaorganisasi']."</option>";
	}

	##untuk pilihan Kebun
	$optKebun0="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optKebun="";
	//$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and detail='1'";
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='KEBUN' and kodeorganisasi like '%E'";
	}else{
		$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='KEBUN' and kodeorganisasi like '%E' 
				and kodeorganisasi in (SELECT kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
	}
	//exit('Warning: '.$sKebun);
	$qKebun=mysql_query($sKebun) or die(mysql_error($conn));
	$no=0;
	$dspKebun="";
	while($dKebun=mysql_fetch_assoc($qKebun)){
		$no+=1;
		if($no==1){
			$dspKebun=$dKebun['kodeorganisasi'];
		}
		$optKebun0.="<option value=".$dKebun['kodeorganisasi'].">".$dKebun['namaorganisasi']."</option>";
		$optKebun.="<option value=".$dKebun['kodeorganisasi'].">".$dKebun['namaorganisasi']."</option>";
	}

	##untuk pilihan Divisi
	$optDivisi0="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
	//$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1'";
	if(!empty($dspKebun)){
		$whrKebun=" and induk='".$dspKebun."'";
	}
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='AFDELING' and left(kodeorganisasi,4) like '%E'"
				.$whrKebun;
	}else{
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='AFDELING' and left(kodeorganisasi,4) like '%E' 
				and left(kodeorganisasi,4) in (SELECT kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')"
				.$whrKebun;
	}
	//exit('Warning: '.$sDivisi);
	$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
	while($dDivisi=mysql_fetch_assoc($qDivisi)){
		$optDivisi.="<option value=".$dDivisi['kodeorganisasi'].">".$dDivisi['namaorganisasi']."</option>";
	}

	##untuk pilihan Periode
	$optPeriode="";
	$sPeriode="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
	while($dPeriode=mysql_fetch_assoc($qPeriode)){
		$optPeriode.="<option value=".$dPeriode['periode'].">".$dPeriode['periode']."</option>";
	}

	OPEN_BOX();
	$frm[0]='';
	$frm[1]='';
	$frm[2]='';
	$arr0="##pabrik0##kebun0##divisi0##periode0";
	$arr1="##pabrik1##kebun1##divisi1##periode1";
	$arr2="##pabrik2##tgl12##tgl22";	

	###form input 0
	################
	$frm[0].="<fieldset style='float:left;'><legend><b>Form Harian Detail</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=pabrik0 style=\"width:155px;\" >".$optPabrik."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun0 onchange=getdSub() style=\"width:240px;\" >".$optKebun0."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi0 style=\"width:240px;\" >".$optDivisi0."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode0 style=\"width:70px;\" >".$optPeriode."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lapfull_grading_per_hari','".$arr0."','printContainer0') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lapfull_grading_per_hari.php','".$arr0."') class=mybutton name=excel0 id=excel0>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(0) class=mybutton name=btnBatal0 id=btnBatal0>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer0' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 1
	################
	$frm[1].="<fieldset style='float:left;'><legend><b>Form Harian Divisi</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=pabrik1 style=\"width:155px;\" >".$optPabrik."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun1 onchange=getSub() style=\"width:240px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi1 style=\"width:240px;\" >".$optDivisi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode1 style=\"width:70px;\" >".$optPeriode."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lapfull_grading_per_divisi','".$arr1."','printContainer1') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lapfull_grading_per_divisi.php','".$arr1."') class=mybutton name=excel1 id=excel1>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(1) class=mybutton name=btnBatal1 id=btnBatal1>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 2
	################
	$frm[2].="<fieldset style='float:left;'><legend><b>Form Divisi</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=pabrik2 style=\"width:155px;\" >".$optPabrik."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tgl12' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
						s/d
						<input type='text' class='myinputtext' id='tgl22' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
					</td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lapfull_grading_all_divisi','".$arr2."','printContainer2') class=mybutton name=preview2 id=preview2>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lapfull_grading_all_divisi.php','".$arr2."') class=mybutton name=excel2 id=excel2>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(2) class=mybutton name=btnBatal2 id=btnBatal2>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[2].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer2' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	$hfrm[0]=$_SESSION['lang']['laporan'].' Grading Actual Harian Detail';
	$hfrm[1]=$_SESSION['lang']['laporan'].' Grading Actual Harian Divisi';
	$hfrm[2]=$_SESSION['lang']['laporan'].' Grading Actual All Divisi';
	drawTab('FRM',$hfrm,$frm,300,1225);	
	CLOSE_BOX();
	echo close_body();
?>
