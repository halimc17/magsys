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
//for($x=0;$x<=3;$x++)
//{
//	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
//	$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
//}

//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOpt=mysql_query($sOpt) or die(mysql_error());
while($rOpt=mysql_fetch_assoc($qOpt))
{
	$optPabrik.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}
//$optProduk="<option value=''>".$_SESSION['lang']['all']."</option>";
$optProduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPrd="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%'";
$qPrd=mysql_query($sPrd) or die(mysql_error());
while($rPrd=mysql_fetch_assoc($qPrd))
{
	$optProduk.="<option value=".$rPrd['kodebarang'].">".$rPrd['namabarang']."</option>";
}
$sGp="select DISTINCT substr(tanggal,1,7) as periode  from ".$dbname.".pabrik_produksi order by tanggal desc";
$qGp=mysql_query($sGp) or die(mysql_error());
$optPeriode="";
while($rGp=mysql_fetch_assoc($qGp))
{
   $thn=explode("-", $rGp['periode']);
   if($thn[1]=='12')
   {
     $optPeriode.="<option value='".substr($rGp['periode'],0,4)."'>".substr($rGp['periode'],0,4)."</option>";
   }
    $optPeriode.="<option value='".$rGp['periode']."'>".substr($rGp['periode'],5,2)."-".substr($rGp['periode'],0,4)."</option>";
}

$arr="##kdPbrik##kdTangki##tgl_1##tgl_2";
$arr1="##kodeorg1##tanggal1";


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/pabrik_4persediaan.js"></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
$frm[0]="<div class=\"container-fluid\">
    <div class=\"card mb-4\">
        <div class=\"card-header bg-primary text-white\">
            <h5 class=\"mb-0\"><i class=\"bi bi-box-seam me-2\"></i>".$_SESSION['lang']['laporanstok']."</h5>
        </div>
        <div class=\"card-body\">
            <div class=\"row g-3\">
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['unit']."</label>
                    <select id=\"kdPbrik\" name=\"kdPbrik\" class=\"form-select\" onchange=\"getTangki()\">".$optPabrik."</select>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['kodetangki']."</label>
                    <select id=\"kdTangki\" name=\"kdTangki\" class=\"form-select\"><option value=\"\">".$_SESSION['lang']['all']."</option></select>
                </div>
                <div class=\"col-md-12\">
                    <label class=\"form-label\">".$_SESSION['lang']['tanggal']."</label>
                    <div class=\"input-group\">
                        <input type=\"text\" class=\"form-control\" id=\"tgl_1\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\" onblur=\"cleart()\" />
                        <span class=\"input-group-text\">s.d.</span>
                        <input type=\"text\" class=\"form-control\" id=\"tgl_2\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\" onblur=\"cleart()\" />
                    </div>
                </div>
                <div class=\"col-12\">
                    <button onclick=\"zPreview('pabrik_slave_4persediaan','".$arr."','printContainer')\" class=\"btn btn-primary btn-sm me-2\">
                        <i class=\"bi bi-eye me-1\"></i>Preview
                    </button>
                    <button onclick=\"zPdf('pabrik_slave_4persediaan','".$arr."','printContainer')\" class=\"btn btn-danger btn-sm me-2\">
                        <i class=\"bi bi-file-pdf me-1\"></i>PDF
                    </button>
                    <button onclick=\"zExcel(event,'pabrik_slave_4persediaan.php','".$arr."')\" class=\"btn btn-success btn-sm\">
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


$frm[1]="<div class=\"container-fluid\">
    <div class=\"card mb-4\">
        <div class=\"card-header bg-primary text-white\">
            <h5 class=\"mb-0\"><i class=\"bi bi-truck me-2\"></i>".$_SESSION['lang']['laporanstok']." vs ".$_SESSION['lang']['pengiriman']."</h5>
        </div>
        <div class=\"card-body\">
            <div class=\"row g-3\">
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['unit']."</label>
                    <select id=\"kodeorg1\" name=\"kodeorg1\" class=\"form-select\">".$optPabrik."</select>
                </div>
                <div class=\"col-md-6\">
                    <label class=\"form-label\">".$_SESSION['lang']['tanggal']."</label>
                    <input type=\"text\" class=\"form-control\" id=\"tanggal1\" name=\"tanggal1\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" />
                </div>
                <div class=\"col-12\">
                    <button onclick=\"zPreview('pabrik_slave_4persediaan_kirim','".$arr1."','printContainer1')\" class=\"btn btn-primary btn-sm\">
                        <i class=\"bi bi-eye me-1\"></i>Preview
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
//<tr><td><label>".$_SESSION['lang']['produk']."</label></td><td><select id=produk1 name=produk1 style=width:150px>".$optProduk."</select></td></tr>
//    <button onclick=zPdf('pabrik_slave_4persediaan_kirim','".$arr1."','printContainer1') class=mybutton name=preview id=preview>PDF</button>
//    <button onclick=zExcel(event,'pabrik_slave_4persediaan_kirim.php','".$arr1."') class=mybutton name=preview id=preview>Excel</button></td></tr>

//========================
$hfrm[0]=$_SESSION['lang']['laporanstok'];
$hfrm[1]=$_SESSION['lang']['laporanstok']." vs ".$_SESSION['lang']['pengiriman'];
?>

<!-- Bootstrap 5 Nav Tabs -->
<ul class="nav nav-tabs" id="frmTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-0" data-bs-toggle="tab" data-bs-target="#content-0" type="button" role="tab" aria-controls="content-0" aria-selected="true">
            <i class="bi bi-box-seam me-1"></i><?php echo $hfrm[0]; ?>
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