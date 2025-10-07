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
	function getKebun(num){
	    param='proses=getKebun';
		if(num==0){
			kodeorg0=document.getElementById('kodeorg0').options[document.getElementById('kodeorg0').selectedIndex].value;
			param+='&kodeorg0='+kodeorg0;
		}else if(num==1){
			kodeorg1=document.getElementById('kodeorg1').options[document.getElementById('kodeorg1').selectedIndex].value;
			param+='&kodeorg1='+kodeorg1;
		}else if(num==2){
			kodeorg2=document.getElementById('kodeorg2').options[document.getElementById('kodeorg2').selectedIndex].value;
			param+='&kodeorg2='+kodeorg2;
		}else if(num==3){
			kodeorg3=document.getElementById('kodeorg3').options[document.getElementById('kodeorg3').selectedIndex].value;
			param+='&kodeorg3='+kodeorg3;
		}
		tujuan='pabrik_lap_materialballance_monthly.php';
	    post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						if(num==0){
							document.getElementById('kebun0').innerHTML=con.responseText;
						}else if(num==1){
							document.getElementById('kebun1').innerHTML=con.responseText;
						}else if(num==2){
							document.getElementById('kebun2').innerHTML=con.responseText;
						}else if(num==3){
							document.getElementById('kebun3').innerHTML=con.responseText;
						}
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function batalLaporan(num){
		if(num==0){
			document.getElementById('kebun0').value='';
		}
		if(num==1){
			document.getElementById('kebun1').value='';
		}
		if(num==2){
			document.getElementById('kebun2').value='';
		}
		if(num==3){
			document.getElementById('kebun3').value='';
		}
	}
</script>
<?
	include('master_mainMenu.php');
	$frm[0]='';
	$frm[1]='';
	$frm[2]='';
	$frm[3]='';

	$getKebun0="onchange='getKebun(0)'";
	$getKebun1="onchange='getKebun(1)'";
	$getKebun2="onchange='getKebun(2)'";
	$getKebun3="onchange='getKebun(3)'";

	##untuk pilihan Pabrik
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK') and detail='1' order by namaorganisasi asc ";	
		$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
		$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}else{
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe in ('PABRIK') and detail='1' order by kodeorganisasi asc";
		$optOrg="";
		$getKebun0="";
		$getKebun1="";
		$getKebun2="";
		$getKebun3="";
	}
	$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
	while($dOrg=mysql_fetch_assoc($qOrg)){
		$optOrg.="<option value=".$dOrg['kodeorganisasi'].">".$dOrg['namaorganisasi']."</option>";
	}
	
	$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sKebun="select distinct left(a.kodeblok,4) as kodeunit,if(a.kodeblok='TBSEXT','TBS LUAR',b.namaorganisasi) as namaunit 
			from ".$dbname.".pabrik_materialballance a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=left(a.kodeblok,4)
			where a.kodeorg='".$_SESSION['empl']['lokasitugas']."'
			ORDER BY if(a.kodeblok='TBSEXT','ZZZZ',a.kodeblok)";
	$qKebun=mysql_query($sKebun) or die(mysql_error($conn));
	while($dKebun=mysql_fetch_assoc($qKebun)){
		$optKebun.="<option value=".$dKebun['kodeunit'].">".$dKebun['namaunit']."</option>";
	}

	##untuk pilihan Periode
	$optPeriode="";
	$sPeriode="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
	$no=0;
	while($dPeriode=mysql_fetch_assoc($qPeriode)){
		$no+=1;
		if($no==1){
			$optPeriode.="<option value='".substr($dPeriode['periode'],0,4)."'>".substr($dPeriode['periode'],0,4)."</option>";
		}else if(substr($dPeriode['periode'],5,2)=='12'){
			$optPeriode.="<option value='".substr($dPeriode['periode'],0,4)."'>".substr($dPeriode['periode'],0,4)."</option>";
		}
		$optPeriode.="<option value='".$dPeriode['periode']."'>".$dPeriode['periode']."</option>";
	}

	##untuk pilihan tahun
	$optTahun="";
	$sTahun="select distinct left(periode,4) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
	$qTahun=mysql_query($sTahun) or die(mysql_error($conn));
	while($dTahun=mysql_fetch_assoc($qTahun)){
		$optTahun.="<option value='".$dTahun['tahun']."'>".$dTahun['tahun']."</option>";
	}

	OPEN_BOX();
	$arr0="##kodeorg0##kebun0##periode0";
	$arr1="##kodeorg1##kebun1##periode1";
	$arr2="##kodeorg2##kebun2##periode2";
	$arr3="##kodeorg3##kebun3##periode3";

	###form input 1
	################
	$frm[0].="<fieldset style='float:left;'><legend><b>Rekap Monthly</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorg0 ".$getKebun0." style=\"width:155px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun0 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode0 style=\"width:155px;\" >".$optPeriode."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lap_materialballance_monthly','".$arr0."','printContainer0') class=mybutton name=preview0 id=preview0>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lap_materialballance_monthly.php','".$arr0."') class=mybutton name=excel0 id=excel0>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(0) class=mybutton name=btnBatal0 id=btnBatal0>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer0' style='overflow:auto;height:470px;max-width:1150px'; ></div>
		</fieldset>";

	###form input 2
	################
	$frm[1].="<fieldset style='float:left;'><legend><b>Summary</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorg1 ".$getKebun1." style=\"width:155px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun1 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode1 style=\"width:155px;\" >".$optTahun."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lap_materialballance_summary','".$arr1."','printContainer1') class=mybutton name=preview1 id=preview1>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lap_materialballance_summary.php','".$arr1."') class=mybutton name=excel1 id=excel1>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(1) class=mybutton name=btnBatal1 id=btnBatal1>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer1' style='overflow:auto;height:470px;max-width:1150px'; ></div>
		</fieldset>";

	###form input 3
	################
	$frm[2].="<fieldset style='float:left;'><legend><b>Sum With Losses</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorg2 ".$getKebun2." style=\"width:155px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun2 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode2 style=\"width:155px;\" >".$optTahun."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lap_materialballance_sumlosses','".$arr2."','printContainer2') class=mybutton name=preview2 id=preview2>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lap_materialballance_sumlosses.php','".$arr2."') class=mybutton name=excel2 id=excel2>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(2) class=mybutton name=btnBatal2 id=btnBatal2>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[2].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer2' style='overflow:auto;height:470px;max-width:1150px'; ></div>
		</fieldset>";

	###form input 4
	################
	$frm[3].="<fieldset style='float:left;'><legend><b>MB Potensi</b></legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=kodeorg3 ".$getKebun3." style=\"width:155px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kebun']."</td>
					<td>:</td>
					<td><select id=kebun3 style=\"width:155px;\" >".$optKebun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td><select id=periode3 style=\"width:155px;\" >".$optPeriode."</select></td>
				</tr>	
				<tr>
					<td colspan=100>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=100>
						<button onclick=zPreview('pabrik_lap_materialballance_mbpotensi','".$arr3."','printContainer3') class=mybutton name=preview3 id=preview3>".$_SESSION['lang']['preview']."</button>
						<button onclick=zExcel(event,'pabrik_lap_materialballance_mbpotensi.php','".$arr3."') class=mybutton name=excel3 id=excel3>".$_SESSION['lang']['excel']."</button>
						<button onclick=batalLaporan(3) class=mybutton name=btnBatal3 id=btnBatal3>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
	$frm[3].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
			<div id='printContainer3' style='overflow:auto;height:470px;max-width:1150px'; ></div>
		</fieldset>";

	$hfrm[0]='Rekap Monthly';
	$hfrm[1]='Summary';
	$hfrm[2]='Sum With Losses';
	$hfrm[3]='MB Potensi';
	drawTab('FRM',$hfrm,$frm,490,1155);	
	CLOSE_BOX();
	echo close_body();
?>
