<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX("","<b>".$_SESSION['lang']['daftarHutang']."/".$_SESSION['lang']['usiapiutang']."</b>");

//list akun
$str="select b.noakun, b.namaakun from  ".$dbname.".keu_5akun b 
      where detail=1 and (noakun like '113%' or noakun like '114%' or noakun like '211%' or noakun like '118%') order by b.noakun"; 
$res=mysql_query($str);
$optnoakun="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
        $optnoakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}
//list org
$str="select kodeorganisasi, namaorganisasi from  ".$dbname.".organisasi 
      where length(kodeorganisasi)=3 order by kodeorganisasi
"; 

$res=mysql_query($str);
$optorg="";
while($bar=mysql_fetch_object($res))
{
    $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

//list karyawan
$str="select a.nik, b.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".datakaryawan b on a.nik = b.karyawanid
      where a.kodeorg ='".$_SESSION['empl']['lokasitugas']."' and a.nik!='0'
      and a.nik != '' and a.noakun != '' group by a.nik order by b.namakaryawan
"; // hanya menampilkan nama yang ada di jurnal 
$res=mysql_query($str);
$optnamakaryawan="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
        $optnamakaryawan.="<option value='".$bar->nik."'>".$bar->namakaryawan."</option>";
}



echo"
<div class='card border-0 shadow-sm mb-3' style='width:50%;'>
    <div class='card-body'>
        <div class='row g-3'>
            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tanggalmulai']."</label>
                <input class='form-control form-control-sm' id='tanggalmulai' onmousemove='setCalendar(this.id)' maxlength='10' onkeypress='return false;' type='text'>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tanggalsampai']."</label>
                <input class='form-control form-control-sm' id='tanggalsampai' onmousemove='setCalendar(this.id)' maxlength='10' onkeypress='return false;' type='text'>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['noakun']."</label>
                <select id='noakun' class='form-select form-select-sm'>".$optnoakun."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['kodeorg']."</label>
                <select id='kodeorg' class='form-select form-select-sm'>".$optorg."</select>
            </div>

            <div class='col-md-12'>
                <button class='btn btn-primary btn-sm' onclick='getLaporanJurnalPiutangKaryawan()'>
                    <i class='bi bi-funnel-fill me-1'></i>".$_SESSION['lang']['proses']."
                </button>
            </div>
        </div>
    </div>
</div>
";
CLOSE_BOX();
OPEN_BOX('','<i class=\"bi bi-file-text-fill me-2\"></i>Result');
echo"
<div class='mb-3' id='printPanel' style='display:none;'>
    <button class='btn btn-success btn-sm' onclick='piutangKaryawanKeExcel(event,\"keu_laporanJurnalPiutangKaryawan_Excel.php\")'>
        <i class='bi bi-file-earmark-excel-fill me-1'></i>Export Excel
    </button>
</div>

<div class='table-responsive'>
    <table class='table table-sm table-bordered table-hover'>
        <thead class='table-primary text-white'>
            <tr>
                <th class='text-center' style='width:50px;'>".$_SESSION['lang']['nourut']."</th>
                <th class='text-center'>".$_SESSION['lang']['organisasi']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['noakun']."</th>
                <th class='text-center'>".$_SESSION['lang']['namaakun']."</th>
                <th class='text-center'>".$_SESSION['lang']['karyawan']."/".$_SESSION['lang']['supplier']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['saldoawal']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['debet']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['kredit']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['saldoakhir']."</th>
            </tr>
        </thead>
        <tbody id='container'>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>
";
CLOSE_BOX();
close_body();
?>