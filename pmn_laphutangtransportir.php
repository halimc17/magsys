<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

//Pilih Unit
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and detail='1'";
	$sPeriode="select distinct(left(tanggalext,7)) as periode from ".$dbname.".pmn_traderht order by tanggalext desc";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe!='HOLDING' and detail='1'";
	$sPeriode="select distinct(left(tanggalext,7)) as periode from ".$dbname.".pmn_traderht order by tanggalext desc";
}else{
    $i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$sPeriode="select distinct(left(tanggalext,7)) as periode from ".$dbname.".pmn_traderht where kodeorg='".$lksiTugas."' order by tanggalext desc";
}
$optUnit="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
}
$optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;

//Pilih Customer
$i="select supplierid,namasupplier from ".$dbname.".log_5supplier where kodekelompok not like 'S%'";
$optCust="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optCust.="<option value='".$d['supplierid']."'>".$d['namasupplier']."</option>";
}
$optCust2="<option value=''>".$_SESSION['lang']['all']."</option>".$optCust;
$optCust="<option value=''>".$_SESSION['lang']['all']."</option>".$optCust;

//Pilih Barang
$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%' and inactive='0'";
$optBrg="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optBrg.="<option value='".$d['kodebarang']."'>".$d['namabarang']."</option>";
}
$optBrg2="<option value=''>".$_SESSION['lang']['all']."</option>".$optBrg;
$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>".$optBrg;

//Pilih Kontrak
//$i="select nokontrak from ".$dbname.".pmn_kontrakjual where kodebarang='".$kdBrg."' order by tanggalkontrak desc";
$i="select nokontrak from ".$dbname.".pmn_kontrakjual order by tanggalkontrak desc";
$optKontrak="<option value=''>".$_SESSION['lang']['all']."</option>";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optKontrak.="<option value='".$d['nokontrak']."'>".$d['nokontrak']."</option>";
}

//$sPeriode="select distinct(left(tanggalext,7)) as periode from ".$dbname.".pmn_traderht order by tanggalext desc";
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
$optPeriode="";
while($rPeriode=mysql_fetch_assoc($qPeriode)){
	$no+=1;
	if($no==1){
         $optPeriode.="<option value='".substr($rPeriode['periode'],0,4)."'>".substr($rPeriode['periode'],0,4)."</option>";
	}else
   if(substr($rPeriode['periode'],5,2)=='12')
   {
         $optPeriode.="<option value='".substr($rPeriode['periode'],0,4)."'>".substr($rPeriode['periode'],0,4)."</option>";
   }
   $optPeriode.="<option value='".$rPeriode['periode']."'>".substr($rPeriode['periode'],5,2)."-".substr($rPeriode['periode'],0,4)."</option>";
}

$arr="##kdUnit##kdCust##nokontrak##tgl_1##tgl_2##kdBrg";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function getSub()
{
    afd=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param='kdUnit='+afd+'&proses=getSubUnit';
    tujuan='pmn_slave_laphutangtransportir.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
                  if(con.readyState==4)
                  {
                            if (con.status == 200) {
                                            busy_off();
                                            if (!isSaveResponse(con.responseText)) {
                                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                                            }
                                            else {
//                                                    alert(con.responseText);
                                                    document.getElementById('afdId').innerHTML=con.responseText;

                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  }	
     }  	
}

function showpopup(mandorid,karyawanid,tanggal,kdorg,afdid,pengawas,ev)
{
   param='mandorid='+mandorid+'&karyawanid='+karyawanid+'&tanggal='+tanggal+'&kdorg='+kdorg+'&afdid='+afdid+'&pengawas='+pengawas;
   tujuan='pmn_slave_laphutangtransportir.php'+"?"+param;
   width='1170';
   height='470';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('No Transaksi Premi Panen '+afdid+' '+mandorid+' '+karyawanid+' '+tanggal,content,width,height,ev); 
	
}

function cekcek(apa){
    if(apa.checked)apa.value="1"; else apa.value="0";
}

function Clear1()
{
    document.getElementById('tgl_1').value='';
    document.getElementById('tgl_2').value='';
    document.getElementById('kdUnit').value='';
    document.getElementById('kdCust').value='';
    document.getElementById('kdBrg').value='';
    document.getElementById('nokontrak').value='';
 }
</script>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['laporan']." ".$_SESSION['lang']['hutang']." ".$_SESSION['lang']['transporter'];?></b></legend>
<table cellspacing="1" border="0" >
<tr>
	<td><label><?php echo $_SESSION['lang']['tanggal'];?></label></td>
	<td><input type="text" class="myinputtext" id="tgl_1" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10" onblur="cleart()" /> s.d. <input type="text" class="myinputtext" id="tgl_2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10"  onblur="cleart()" />
	</td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
	<td><select id=kdUnit><?php echo $optUnit;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['transporter'];?></label></td>
	<td><select id="kdCust" name="kdCust" style="width:150px"><?php echo $optCust;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['komoditi'];?></label></td>
	<td><select id="kdBrg" name="kdBrg" style="width:150px"><?php echo $optBrg;?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['NoKontrak'];?></label></td>
	<td><input type=text class=myinputtext  id=nokontrak name=nokontrak style="width:200px" /></td>
</tr>

<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">
	<button onclick="zPreview('pmn_slave_laphutangtransportir','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<button onclick="zExcel(event,'pmn_slave_laphutangtransportir.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
</td></tr>
</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:330px;max-width:1100px'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>