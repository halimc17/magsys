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

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang in ('40000001', '40000002')";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
	$optBrg1.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$arr="##tanggalmulai##tanggalakhir##idPabrik##kdBrg";
$arr1="##kodeorg1##kodebarang1##tgl1_1##tgl2_1";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2penjualan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
$frm[0].="<div class=\"container-fluid\">
    <div class=\"card mb-4\">
        <div class=\"card-header bg-primary text-white\">
            <h5 class=\"mb-0\"><i class=\"bi bi-file-earmark-text me-2\"></i>".$_SESSION['lang']['laporanPenjualan']."</h5>
        </div>
        <div class=\"card-body\">
            <div class=\"row g-3\">
                <div class=\"col-md-6\" style='display:none'>
                    <label class=\"form-label\">".$_SESSION['lang']['periode']."</label>
                    <select id=\"periode\" name=\"periode\" class=\"form-select\">".$optPeriode."</select>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['tanggalmulai']."</label>
                    <input type=\"text\" class=\"form-control\" id=\"tanggalmulai\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" value='".date('d-m-Y')."'/>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['tanggalsampai']."</label>
                    <input type=\"text\" class=\"form-control\" id=\"tanggalakhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" value='".date('d-m-Y')."'/>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['nm_perusahaan']."</label>
                    <select id=\"idPabrik\" name=\"idPabrik\" class=\"form-select\">".$optPabrik."</select>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['komoditi']."</label>
                    <select id=\"kdBrg\" name=\"kdBrg\" class=\"form-select\">".$optBrg."</select>
                </div>
                <div class=\"col-12\">
                    <button onclick=\"zPreview('pmn_slave_2penjualan','".$arr."','printContainer')\" class=\"btn btn-primary btn-sm me-2\">
                        <i class=\"bi bi-eye me-1\"></i>Preview
                    </button>
                    <button onclick=\"zPdf('pmn_slave_2penjualan','".$arr."','printContainer')\" class=\"btn btn-danger btn-sm me-2\">
                        <i class=\"bi bi-file-pdf me-1\"></i>PDF
                    </button>
                    <button onclick=\"zExcel(event,'pmn_slave_2penjualan.php','".$arr."')\" class=\"btn btn-success btn-sm\">
                        <i class=\"bi bi-file-excel me-1\"></i>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class=\"card\">
        <div class=\"card-header bg-light\">
            <h6 class=\"mb-0\">Print Area</h6>
        </div>
        <div class=\"card-body\" style=\"overflow:auto;height:350px;\">
            <div id='printContainer'></div>
        </div>
    </div>
</div>";

$frm[1].="<div class=\"container-fluid\">
    <div class=\"card mb-4\">
        <div class=\"card-header bg-primary text-white\">
            <h5 class=\"mb-0\"><i class=\"bi bi-truck me-2\"></i>".$_SESSION['lang']['rPengiriman']." ".$_SESSION['lang']['harian']."</h5>
        </div>
        <div class=\"card-body\">
            <div class=\"row g-3\">
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['pabrik']."</label>
                    <select id=\"kodeorg1\" name=\"kodeorg1\" class=\"form-select\">".$optPabrik1."</select>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['komoditi']."</label>
                    <select id=\"kodebarang1\" name=\"kodebarang1\" class=\"form-select\">".$optBrg."</select>
                </div>
                <div class=\"col-md-12\">
                    <label class=\"form-label\">".$_SESSION['lang']['tanggal']."</label>
                    <div class=\"input-group\">
                        <input type=\"text\" class=\"form-control\" id=\"tgl1_1\" onchange=\"bersih_1()\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\">
                        <span class=\"input-group-text\">-</span>
                        <input type=\"text\" class=\"form-control\" id=\"tgl2_1\" onchange=\"bersih_1()\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\">
                    </div>
                </div>
                <div class=\"col-12\">
                    <button onclick=\"zPreview('pmn_slave_2penjualan_harian','".$arr1."','printContainer1')\" class=\"btn btn-primary btn-sm me-2\">
                        <i class=\"bi bi-eye me-1\"></i>Preview
                    </button>
                    <button onclick=\"zExcel(event,'pmn_slave_2penjualan_harian.php','".$arr1."')\" class=\"btn btn-success btn-sm\">
                        <i class=\"bi bi-file-excel me-1\"></i>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class=\"card\">
        <div class=\"card-header bg-light\">
            <h6 class=\"mb-0\">Print Area</h6>
        </div>
        <div class=\"card-body\" style=\"overflow:auto;height:350px;\">
            <div id='printContainer1'></div>
        </div>
    </div>
</div>";
//    <button onclick=\"zPdf('pmn_slave_2penjualan_harian','".$arr1."','printContainer1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>

//========================
$hfrm[0]=$_SESSION['lang']['laporanPenjualan'];
$hfrm[1]=$_SESSION['lang']['rPengiriman']." ".$_SESSION['lang']['harian'];
?>

<!-- Bootstrap 5 Nav Tabs -->
<ul class="nav nav-tabs" id="frmTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-0" data-bs-toggle="tab" data-bs-target="#content-0" type="button" role="tab" aria-controls="content-0" aria-selected="true">
            <i class="bi bi-file-earmark-text me-1"></i><?php echo $hfrm[0]; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-1" data-bs-toggle="tab" data-bs-target="#content-1" type="button" role="tab" aria-controls="content-1" aria-selected="false">
            <i class="bi bi-truck me-1"></i><?php echo $hfrm[1]; ?>
        </button>
    </li>
</ul>

<div class="tab-content" id="frmTabsContent">
    <div class="tab-pane fade show active" id="content-0" role="tabpanel" aria-labelledby="tab-0">
        <div class="p-3">
            <?php echo $frm[0]; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="content-1" role="tabpanel" aria-labelledby="tab-1">
        <div class="p-3">
            <?php echo $frm[1]; ?>
        </div>
    </div>
</div>

<?php
//===============================================

CLOSE_BOX();
echo close_body();
?>