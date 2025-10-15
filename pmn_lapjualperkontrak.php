<?php
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
	$sPeriode="select distinct(left(tanggal,7)) as periode from ".$dbname.".pabrik_timbangan order by tanggal desc";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe!='HOLDING' and detail='1'";
	$sPeriode="select distinct(left(tanggal,7)) as periode from ".$dbname.".pabrik_timbangan order by tanggal desc";
}else{
    $i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	//$sPeriode="select distinct(left(tanggal,7)) as periode from ".$dbname.".pabrik_timbangan where kodeorg='".$lksiTugas."' order by tanggal desc";
	$sPeriode="select distinct(left(tanggal,7)) as periode from ".$dbname.".pabrik_timbangan order by tanggal desc";
}
$optUnit="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
}
$optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;

//Pilih Customer
$i="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer where statusinteks = 'Eksternal'";
$optCust="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optCust.="<option value='".$d['kodecustomer']."'>".$d['namacustomer']."</option>";
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

$sPeriode="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
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

$arr="##kdUnit##kdCust##nokontrak##periode##kdBrg";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>

function showpopup(mandorid,karyawanid,tanggal,kdorg,afdid,pengawas,ev)
{
   param='mandorid='+mandorid+'&karyawanid='+karyawanid+'&tanggal='+tanggal+'&kdorg='+kdorg+'&afdid='+afdid+'&pengawas='+pengawas;
   tujuan='sdm_slave_laporantrader.php'+"?"+param;
   width='1170';
   height='470';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('No Transaksi Premi Panen '+afdid+' '+mandorid+' '+karyawanid+' '+tanggal,content,width,height,ev); 
	
}

function Clear1()
{
    document.getElementById('kdUnit').value='';
    document.getElementById('kdCust').value='';
    document.getElementById('kdBrg').value='';
    document.getElementById('nokontrak').value='';
}
</script>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo $_SESSION['lang']['laporan']." ".$_SESSION['lang']['penjualan']." Per ".$_SESSION['lang']['NoKontrak'];?></h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['unit'];?></label>
                    <select id="kdUnit" class="form-select form-select-sm"><?php echo $optUnit;?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['vendor'];?></label>
                    <select id="kdCust" name="kdCust" class="form-select form-select-sm"><?php echo $optCust;?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['periode'];?></label>
                    <select id="periode" name="periode" class="form-select form-select-sm"><?php echo $optPeriode;?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['kodebarang'];?></label>
                    <select id="kdBrg" name="kdBrg" class="form-select form-select-sm"><?php echo $optBrg;?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['NoKontrak'];?></label>
                    <input type="text" class="form-control form-control-sm" id="nokontrak" name="nokontrak" />
                </div>
                <div class="d-grid gap-2">
                    <button onclick="zPreview('pmn_slave_lapjualperkontrak','<?php echo $arr;?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                    <button onclick="zExcel(event,'pmn_slave_lapjualperkontrak.php','<?php echo $arr;?>')" class="btn btn-success btn-sm">Excel</button>
                    <button onclick="Clear1()" class="btn btn-secondary btn-sm"><?php echo $_SESSION['lang']['cancel'];?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Print Area</h5>
    </div>
    <div class="card-body">
        <div id="printContainer" style="overflow:auto;height:330px;"></div>
    </div>
</div>

<?php
CLOSE_BOX();
echo close_body();
?>