<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();

	$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
	$arr="##kdOrg##afdId##periode##pengawas";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') and detail='1' order by namaorganisasi asc ";	
		$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
	    $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."' and tipe in ('KEBUN') and detail='1' order by kodeorganisasi asc";
		$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
		$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}else{
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe in ('KEBUN') and detail='1' order by kodeorganisasi asc";
		$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
	    //$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optOrg="";
	}
	$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
	}

	$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sAfd="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING' order by namaorganisasi asc";
	$qAfd=mysql_query($sAfd) or die(mysql_error($conn));
	while($rAfd=mysql_fetch_assoc($qAfd)){
		$optAfd.="<option value=".$rAfd['kodeorganisasi'].">".$rAfd['namaorganisasi']."</option>";
	}

	$qPeriode=mysql_query($sPeriode) or die(mysql_error());
	while($rPeriode=mysql_fetch_assoc($qPeriode)){
		$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	}

	$optSpv="<option value=''>".$_SESSION['lang']['all']."</option>";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	function getSub(){
		afd=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
		periode=document.getElementById('periode').value;
	    param='proses=getSubUnit'+'&kdOrg='+afd+'&periode='+periode;
		tujuan='sdm_2laporanPremiPerSupervisi_slave.php';
	    post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//alert(con.responseText);
						document.getElementById('afdId').innerHTML=con.responseText;
						getSpv();
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}  	
	}

	function getSpv(){
		org=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
		afd=document.getElementById('afdId').options[document.getElementById('afdId').selectedIndex].value;
	    param='proses=getSpv'+'&kdOrg='+org+'&afdId='+afd+'&periode='+periode;
		tujuan='sdm_2laporanPremiPerSupervisi_slave.php';
	    post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//alert(con.responseText);
						document.getElementById('pengawas').innerHTML=con.responseText;
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
		tujuan='sdm_2laporanPremiPerSupervisi_showpopup.php'+"?"+param;
		width='1200';
		height='470';
  		content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
		showDialog1('No Transaksi Premi Panen '+afdid+' '+mandorid+' '+karyawanid+' '+tanggal,content,width,height,ev); 
	}

	function cekcek(apa){
		if(apa.checked)apa.value="1"; else apa.value="0";
	}

	function Clear1(){
		document.getElementById('kdOrg').value='';
		document.getElementById('afdId').value='';
		//document.getElementById('periode').value='';
		document.getElementById('pengawas').value='';
	}
</script>
<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['laporanPremi']." Per ".$_SESSION['lang']['supervisi'];?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kdOrg" name="kdOrg" style="width:150px" onchange='getSub()'><?php echo $optOrg;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['subunit'];?></label></td>
 				<td><select id="afdId" name="afdId" style="width:150px" onchange='getSpv()'><?php echo $optAfd;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['periode'];?></label></td>
				<td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriode;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['supervisi'];?></label></td>
				<td><select id="pengawas" name="pengawas" style="width:150px"><?php echo $optSpv;?></select></td>
			</tr>
			<tr height="20"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('sdm_2laporanPremiPerSupervisi_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'sdm_2laporanPremiPerSupervisi_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
					<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'>
	<legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:330px;max-width:1200px'>
	</div>
</fieldset>

<?php
	CLOSE_BOX();
	echo close_body();
?>
