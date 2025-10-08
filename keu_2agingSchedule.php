<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_2agingSchedule.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['usiahutang']).'</b>');

	
//=================ambil PT;  


if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT'  order by namaorganisasi";

$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Pusat</option>";
$optStatus.="<option value='1'>Lokal</option>";

}//and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'
else
{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'  order by namaorganisasi";
  
$optStatus.="<option value='1'>Lokal</option>";
}

$res=mysql_query($str);

while($bar=mysql_fetch_object($res))
{
	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}


$optsupkontran="<option value=''>".$_SESSION['lang']['all']."</option>";
$optsupkontran.="<option value='S'>Supllier</option>";
$optsupkontran.="<option value='K'>".$_SESSION['lang']['kontraktor']."</option>";
$optsupkontran.="<option value='T'>Transportir</option>";
//=================ambil gudang;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
		where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
		or tipe='HOLDING')  and induk!=''";
//$str="select distinct a.kodeorg,b.namaorganisasi from ".$dbname.".setup_periodeakuntansi a
//      left join ".$dbname.".organisasi b
//	  on a.kodeorg=b.kodeorganisasi
//     where b.tipe='KEBUN'
//	  order by namaorganisasi";
$res=mysql_query($str);
$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
$optper="<option value=''>".$_SESSION['lang']['all']."</option>";

while($bar=mysql_fetch_object($res))
{
#	$optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}

echo"
<div class='card border-0 shadow-sm mb-3' style='max-width:600px;'>
    <div class='card-body'>
        <div class='row g-3'>
            <div class='col-md-12'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['pt']."</label>
                <select id='pt' class='form-select form-select-sm'>".$optpt."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['po']."</label>
                <select id='statuspo' class='form-select form-select-sm'>".$optStatus."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['supplier']." / ".$_SESSION['lang']['kontraktor']." / Transportir</label>
                <select id='supkontran' class='form-select form-select-sm'>".$optsupkontran."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tanggal']."</label>
                <input type='text' value='".$tanggalpivot=date('d-m-Y')."' class='form-control form-control-sm' id='tanggalpivot' name='tanggalpivot' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10' />
            </div>

            <div class='col-md-12'>
                <button class='btn btn-primary btn-sm' onclick='getUsiaHutang()'>
                    <i class='bi bi-funnel-fill me-1'></i>".$_SESSION['lang']['proses']."
                </button>
                <select id='gudang' hidden class='form-select form-select-sm' onchange='hideById(\"printPanel\")'>".$optgudang."</select>
            </div>
        </div>
    </div>
</div>
";
CLOSE_BOX();
/* ".$_SESSION['lang']['pt']." : "."<select id=pt style='width:200px;' >".$optpt."</select>
	 PO Lokal/Pusat : "."<select id=statuspo>".$optStatus."</select><select id=gudang hidden style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select>
<input type=\"text\" value=\"".$tanggalpivot=date('d-m-Y')."\" class=\"myinputtext\" id=\"tanggalpivot\" name=\"tanggalpivot\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:100px;\" />
	 <button class=mybutton onclick=getUsiaHutang()>".$_SESSION['lang']['proses']."</button>*/
OPEN_BOX('','<i class=\"bi bi-file-text-fill me-2\"></i>Result');
echo"
<div class='mb-3' id='printPanel' style='display:none;'>
    <button class='btn btn-success btn-sm me-2' onclick='fisikKeExcel(event,\"keu_laporanUsiaHutang_Excel.php\")'>
        <i class='bi bi-file-earmark-excel-fill me-1'></i>Export Excel
    </button>
    <button class='btn btn-danger btn-sm' onclick='fisikKePDF(event,\"keu_laporanUsiaHutang_pdf.php\")'>
        <i class='bi bi-file-earmark-pdf-fill me-1'></i>Export PDF
    </button>
</div>

<div class='table-responsive'>
    <table class='table table-sm table-bordered table-hover'>
        <thead class='table-primary text-white'>
            <tr>
                <th rowspan='2' class='text-center align-middle' style='width:50px;'>".$_SESSION['lang']['nourut']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:90px;'>".$_SESSION['lang']['tanggal']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:200px;'>".$_SESSION['lang']['noinvoice']."<br>".$_SESSION['lang']['namasupplier']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:90px;'>Jatuh Tempo</th>
                <th rowspan='2' class='text-center align-middle' style='width:100px;'>".$_SESSION['lang']['nopokontrak']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:120px;'>".$_SESSION['lang']['nilaipokontrak']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:120px;'>".$_SESSION['lang']['nilaiinvoice']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:120px;'>".$_SESSION['lang']['belumjatuhtempo']."</th>
                <th colspan='4' class='text-center' style='width:400px;'>".$_SESSION['lang']['sudahjatuhtempo']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:120px;'>".$_SESSION['lang']['dibayar']."</th>
                <th rowspan='2' class='text-center align-middle' style='width:80px;'>".$_SESSION['lang']['jmlh_hari_outstanding']."</th>
            </tr>
            <tr>
                <th class='text-center' style='width:100px;'>1-30 ".$_SESSION['lang']['hari']."</th>
                <th class='text-center' style='width:100px;'>31-60 ".$_SESSION['lang']['hari']."</th>
                <th class='text-center' style='width:100px;'>61-90 ".$_SESSION['lang']['hari']."</th>
                <th class='text-center' style='width:100px;'>over 90 ".$_SESSION['lang']['hari']."</th>
            </tr>
        </thead>
        <tbody id='container'>
            <script>getUsiaHutang()</script>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>
";
	
CLOSE_BOX();

close_body();
?>