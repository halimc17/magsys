<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX(); 
?>
<?php
$str="select distinct periode from ".$dbname.".log_5saldobulanan order by periode desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$optPeriode.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
$qPabrik=mysql_query($sPabrik) or die(mysql_error());
while($rPabrik=mysql_fetch_assoc($qPabrik))
{
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['namaorganisasi']."</option>";
}
//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPabrik1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOpt=mysql_query($sOpt) or die(mysql_error());
while($rOpt=mysql_fetch_assoc($qOpt))
{
	$optPabrik1.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
$optBrg = "<option value=''>".$_SESSION['lang']['all']."</option>";
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000001', '40000002')";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg1.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$arr="##tanggalmulai##tanggalakhir##idPabrik##kdBrg";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2penjualan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<!-- Bootstrap Tabs Navigation -->
<ul class="nav nav-tabs" id="rekapkontrakTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="rekapkontrak-tab" data-bs-toggle="tab" data-bs-target="#rekapkontrak" type="button" role="tab" aria-controls="rekapkontrak" aria-selected="true">
            Rekap Kontrak, Invoice dan Faktur Penjualan
        </button>
    </li>
</ul>

<!-- Bootstrap Tabs Content -->
<div class="tab-content" id="rekapkontrakTabContent">
    <div class="tab-pane fade show active" id="rekapkontrak" role="tabpanel" aria-labelledby="rekapkontrak-tab">
        <div class="p-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo $_SESSION['lang']['find'];?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3" style="display:none;">
                                <label class="form-label"><?php echo $_SESSION['lang']['periode'];?></label>
                                <select id="periode" name="periode" class="form-select form-select-sm"><?php echo $optPeriode;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['tgldari'];?></label>
                                <input type="text" class="form-control form-control-sm" id="tanggalmulai" onmousemove="setCalendar(this.id)" onkeypress="return false;" maxlength="10" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['tglsmp'];?></label>
                                <input type="text" class="form-control form-control-sm" id="tanggalakhir" onmousemove="setCalendar(this.id)" onkeypress="return false;" maxlength="10" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['nm_perusahaan'];?></label>
                                <select id="idPabrik" name="idPabrik" class="form-select form-select-sm"><?php echo $optPabrik;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['komoditi'];?></label>
                                <select id="kdBrg" name="kdBrg" class="form-select form-select-sm"><?php echo $optBrg;?></select>
                            </div>
                            <div class="d-grid gap-2">
                                <button onclick="zPreview('pmn_slave_2rekapkontrak','<?php echo $arr;?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                                <button onclick="zExcel(event,'pmn_slave_2rekapkontrak.php','<?php echo $arr;?>')" class="btn btn-success btn-sm">Excel</button>
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
                    <div id="printContainer" style="overflow:auto;height:350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
CLOSE_BOX();
echo close_body();
?>