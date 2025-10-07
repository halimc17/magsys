<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

//$arr="##kdPT##kdOrg##periode##jenistransaksi##stsposting";
$arr="##kdPT##kdOrg##tanggal1##tanggal2##jenistransaksi##stsposting";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sPT="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' and detail='1' order by namaorganisasi asc ";
    $optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
	$qPT=mysql_query($sPT) or die(mysql_error($conn));
	while($rPT=mysql_fetch_assoc($qPT)){
		$optPT.="<option value=".$rPT['kodeorganisasi'].">".$rPT['namaorganisasi']."</option>";
	}
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and length(kodeorganisasi)=40 order by namaorganisasi asc ";
	$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optPT="<option value='".$_SESSION['org']['kodeorganisasi']."'>".$_SESSION['org']['namaorganisasi']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi not like '%HO' and detail='1' and induk='".$_SESSION['org']['kodeorganisasi']."' order by namaorganisasi asc ";	
	$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode";
}else{
	$optPT="<option value='".$_SESSION['org']['kodeorganisasi']."'>".$_SESSION['org']['namaorganisasi']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";	
	$optOrg="";
	$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode";
}

$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
while($rOrg=mysql_fetch_assoc($qOrg)){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['all']."</option>";
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
while($rPeriode=mysql_fetch_assoc($qPeriode)){
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	$thbl=$rPeriode['periode'];
}
$thbltg1=tanggalnormal(date('Y-m-01', strtotime($thbl)));
$thbltg2=tanggalnormal(date('Y-m-t', strtotime($thbl)));
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function getSub(){
    kdPT=document.getElementById('kdPT').options[document.getElementById('kdPT').selectedIndex].value;
    param='kdPT='+kdPT+'&proses=getUnit';
    tujuan='kebun_lapposting_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					document.getElementById('kdOrg').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  	
}

function showpopup(kdOrg,tanggal,notransaksi,type,ev){
   param='notransaksi='+notransaksi+'&type='+type;
   tujuan='kebun_lapposting_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('No Transaksi '+kdOrg+' '+tanggal+' '+notransaksi,content,width,height,ev);
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1(){
    document.getElementById('kdOrg').value='';
    //document.getElementById('tan').value='';
    document.getElementById('jenistransaksi').value='';
    document.getElementById('stsposting').value='0';
}
</script>

<div>
	<fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['transaksi']." ".$_SESSION['lang']['posting'];?></b></legend>
		<table cellspacing="1" border="0" >
			<tr>
				<td><label><?php echo $_SESSION['lang']['pt'];?></label></td>
				<td><select id='kdPT' name='kdPT' style='width:220px' onchange='getSub()'><?php echo $optPT;?></select></td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
				<td><select id="kdOrg" name="kdOrg" style="width:220px"><?php echo $optOrg;?></select></td>
			</tr>
			<tr>
	            <td><label><?php echo $_SESSION['lang']['tanggal'];?></label></td>
	            <td><input type='text' class='myinputtext' id='tanggal1' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'
					value='<?php echo $thbltg1;?>'>
					s/d
					<input type='text' class='myinputtext' id='tanggal2' onmousemove='setCalendar(this.id)' onkeypress='return false;' size='8' maxlength='10'
					value='<?php echo $thbltg2;?>'>
				</td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['transaksi'];?></label></td>
				<td><select id="jenistransaksi" name="jenistransaksi" style="width:155px">
						<option value="" ><?php echo $_SESSION['lang']['all'];?></option>
						<option value="kasbank" ><?php echo $_SESSION['lang']['kasbank'];?></option>
						<option value="gudang" ><?php echo $_SESSION['lang']['gudang'];?></option>
						<option value="baspk" ><?php echo $_SESSION['lang']['realisasispk'];?></option>
						<option value="bkm">BKM</option>
						<option value="spatbs">SPATBS</option>
						<option value="traksi" ><?php echo $_SESSION['lang']['traksi'];?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label><?php echo $_SESSION['lang']['posting'];?></label></td>
				<td><select id="stsposting" name="stsposting" style="width:155px">
						<option value="0"><?php echo $_SESSION['lang']['belumposting'];?></option>
						<option value="1"><?php echo $_SESSION['lang']['posting'];?></option>
						<option value="" ><?php echo $_SESSION['lang']['all'];?></option>
					</select>
				</td>
			</tr>
			<tr height="20"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
					<button onclick="zPreview('kebun_lapposting_slave','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zExcel(event,'kebun_lapposting_slave.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
					<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div style="margin-bottom: 30px;"></div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:330px;max-width:13100px'></div>
</fieldset>
<?php
CLOSE_BOX();
echo close_body();
?>
