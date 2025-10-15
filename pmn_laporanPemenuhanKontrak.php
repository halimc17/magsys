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

//$arr="##periode";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct substr(tanggalkontrak,1,4) as periode from ".$dbname.".pmn_kontrakjual order by substr(tanggalkontrak,1,4) desc";
$qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
while($rPeriode=mysql_fetch_assoc($qPeriode))
{
    $optPeriode.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}
$optPeriode2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode2="select distinct substr(tanggalkontrak,1,4) as periode from ".$dbname.".pmn_kontrakjual order by substr(tanggalkontrak,1,7) desc";
$qPeriode2=mysql_query($sPeriode2) or die(mysql_error($conn));
while($rPeriode2=mysql_fetch_assoc($qPeriode2))
{
    $optPeriode2.="<option value='".$rPeriode2['periode']."'>".$rPeriode2['periode']."</option>";
}

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optBrg2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
		$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
                $optBrg2.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'"; //echo $sOrg;
$qOrg=mysql_query($sOrg) or die(mysql_error());
while($rOrg=mysql_fetch_assoc($qOrg))
{
        $optPt.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$arr="##periode##kdBrg##pt";
$arr2="##thn##kdBrg2##pt2";
$arr3="##kdBrg3##tgl_dr##tgl_samp##pt3";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pmn_laporanPemenuhanKontrak.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo $_SESSION['lang']['laporanPemenuhanKontrak']?></h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['perusahaan']?></label>
                    <select id="pt" name="pt" class="form-select form-select-sm"><?php echo $optPt?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['periode']?></label>
                    <select id="periode" name="periode" class="form-select form-select-sm"><?php echo $optPeriode?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['komoditi']?></label>
                    <select id="kdBrg" name="kdBrg" class="form-select form-select-sm"><?php echo $optBrg?></select>
                </div>
                <div class="d-grid gap-2">
                    <button onclick="zPreview('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                    <button onclick="zPdf('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr?>','printContainer')" class="btn btn-danger btn-sm">PDF</button>
                    <button onclick="zExcel(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr?>')" class="btn btn-success btn-sm">Excel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Uncomplete Contract</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['perusahaan']?></label>
                    <select id="pt2" name="pt2" class="form-select form-select-sm"><?php echo $optPt?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['tahun']?></label>
                    <select id="thn" name="thn" class="form-select form-select-sm"><?php echo $optPeriode2?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['komoditi']?></label>
                    <select id="kdBrg2" name="kdBrg2" class="form-select form-select-sm"><?php echo $optBrg2?></select>
                </div>
                <div class="d-grid gap-2">
                    <button onclick="zPreview2('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr2?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                    <button onclick="zExcel2(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr2?>')" class="btn btn-success btn-sm">Excel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Range Delivery</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['perusahaan']?></label>
                    <select id="pt3" name="pt3" class="form-select form-select-sm"><?php echo $optPt?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['komoditi']?></label>
                    <select id="kdBrg3" name="kdBrg3" class="form-select form-select-sm"><?php echo $optBrg2?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['tgldari']?></label>
                    <input type="text" class="form-control form-control-sm" id="tgl_dr" onmousemove="setCalendar(this.id)" onkeypress="return false;" maxlength="10" />
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $_SESSION['lang']['tanggalsampai']?></label>
                    <input type="text" class="form-control form-control-sm" id="tgl_samp" onmousemove="setCalendar(this.id)" onkeypress="return false;" maxlength="10" />
                </div>
                <div class="d-grid gap-2">
                    <button onclick="zPreview3('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr3?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                    <button onclick="zExcel3(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr3?>')" class="btn btn-success btn-sm">Excel</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Print Area</h5>
    </div>
    <div class="card-body">
        <div id="printContainer" style="overflow:auto;height:350px;"></div>
    </div>
</div>
























<?php
/*#======Select Prep======
# Get Data
//$where = " length(kodeorganisasi)='4'";
$optOrg = makeOption($dbname,'vhc_5master','kodevhc,kodevhc','','0');
#======End Select Prep======
#=======Form============
$els = array();
# Fields
$els[] = array(
  makeElement('tahun','label',$_SESSION['lang']['tahun']),
  makeElement('tahun','textnum',date(Y),array('style'=>'width:150px','maxlength'=>'16',
    'onkeypress'=>'return tanpa_kutip(event)'))
);
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','select','',array('style'=>'width:150px'),$optOrg)
);
$els[] = array(
  makeElement('revisi','label',$_SESSION['lang']['revisi']),
  makeElement('revisi','textnum','0',array('style'=>'width:150px','maxlength'=>'80',
    'onkeypress'=>'return tanpa_kutip(event)'))
);

# Button
$param = '##tahun##kodeorg';
$container = 'printContainer';
$els['btn'] = array(
  makeElement('preview','btn','Preview',array('onclick'=>
    "zPreview('keu_slave_2laporanAnggaranKebun_print','".$param."','".$container."')")).
  makeElement('printPdf','btn','PDF',array('onclick'=>
    "zPdf('keu_slave_2laporanAnggaranKebun_print','".$param."','".$container."')")).
  makeElement('printExcel','btn','Excel',array('onclick'=>"excelBudKebun()"))
);

# Generate Field
echo "<div style='margin-bottom:30px'>";
echo genElTitle('Laporan Anggaran Traksi',$els);
echo "</div>";
echo "<fieldset style='clear:both'><legend><b>Print Area</b></legend>";
echo "<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'></div></fieldset>";
#=======End Form============*/

CLOSE_BOX();
echo close_body();
?>