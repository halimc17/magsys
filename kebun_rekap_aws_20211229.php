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
			document.getElementById('tgl10').value='';
			document.getElementById('tgl20').value='';
		}
		if(num==1){
			document.getElementById('tgl10').value='';
			document.getElementById('tgl21').value='';
		}
	}
</script>
<?php
	include('master_mainMenu.php');
	$frm[0]='';
	$frm[1]='';
	$frm[2]='';
	$frm[3]='';

	##untuk pilihan Kebun
	$optKebun="";
	$sKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and detail='1'";
	$qKebun=mysql_query($sKebun) or die(mysql_error($conn));
	while($dKebun=mysql_fetch_assoc($qKebun)){
		$optKebun.="<option value=".$dKebun['kodeorganisasi'].">".$dKebun['namaorganisasi']."</option>";
	}

	##untuk pilihan Periode
	$optPeriode="";
	$sPeriode="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
	while($dPeriode=mysql_fetch_assoc($qPeriode)){
		$optPeriode.="<option value=".$dPeriode['periode'].">".$dPeriode['periode']."</option>";
	}

	##untuk pilihan Tahun
	$optTahun="";
	$sTahun="select distinct(left(periode,4)) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qTahun=mysql_query($sTahun) or die(mysql_error($conn));
	while($dTahun=mysql_fetch_assoc($qTahun)){
		$optTahun.="<option value=".$dTahun['tahun'].">".$dTahun['tahun']."</option>";
	}

	OPEN_BOX();
	$arr0="##kebun0##tgl10##tgl20";
	$arr1="##kebun1##tgl11##tgl21";	
	$arr2="##kebun2##periode";	
	$arr3="##kebun3##tahun";	

	###form input 1
	################
	$frm[0].="<fieldset style='float:left;'><legend><b>Form Detail</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun0 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tgl10' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
						s/d
						<input type='text' class='myinputtext' id='tgl20' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
					</td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_rekap_aws_slave','".$arr0."','printContainer0') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_rekap_aws_slave.php','".$arr0."') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(0) class=mybutton name=btnBatal0 id=btnBatal0>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer0' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 2
	################
	$frm[1].="<fieldset style='float:left;'><legend><b>Form Harian</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun1 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tgl11' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
						s/d
						<input type='text' class='myinputtext' id='tgl21' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'>
					</td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_rekap_aws_slave','".$arr1."','printContainer1') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_rekap_aws_slave.php','".$arr1."') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(1) class=mybutton name=btnBatal1 id=btnBatal1>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 3
	################
	$frm[2].="<fieldset style='float:left;'><legend><b>Form Bulanan</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun2 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tahun']."</td>
					<td>:</td>
					<td><select id=periode style=\"width:155px;\" >".$optTahun."</select></td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_rekap_aws_slave','".$arr2."','printContainer2') class=mybutton name=preview2 id=preview2>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_rekap_aws_slave.php','".$arr2."') class=mybutton name=preview2 id=preview2>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(2) class=mybutton name=btnBatal2 id=btnBatal2>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[2].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer2' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	###form input 4
	################
	$frm[3].="<fieldset style='float:left;'><legend><b>Form Tahunan</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun3 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tahun']."</td>
					<td>:</td>
					<td><select id=tahun style=\"width:155px;\" >"."<option value=''>".$_SESSION['lang']['all']."</option>".$optTahun."</select></td>
				</tr>
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('kebun_rekap_aws_slave','".$arr3."','printContainer3') class=mybutton name=preview3 id=preview3>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'kebun_rekap_aws_slave.php','".$arr3."') class=mybutton name=preview3 id=preview3>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(3) class=mybutton name=btnBatal3 id=btnBatal3>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[3].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer3' style='overflow:auto;height:350px;max-width:1220px'; ></div>
		</fieldset>";

	$hfrm[0]=$_SESSION['lang']['laporan'].' Detail';
	$hfrm[1]=$_SESSION['lang']['laporan'].' Harian';
	$hfrm[2]=$_SESSION['lang']['laporan'].' Bulanan';
	$hfrm[3]=$_SESSION['lang']['laporan'].' Tahunan';
	drawTab('FRM',$hfrm,$frm,300,1225);	
	CLOSE_BOX();
	echo close_body();
?>
