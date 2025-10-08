<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/keu_laporan.js"></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanbukubesar']).'</b>');






//get existing period
$str="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc";
	  
$res=mysql_query($str);
#$optper="<option value=''>".$_SESSION['lang']['sekarang']."</option>";
$optper="";
while($bar=mysql_fetch_object($res))
{
	$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}


 $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optReg=$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";

/*
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
 * 
 */
        //=================ambil PT;  

if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
              where tipe='PT'
                  order by namaorganisasi";
    $res=mysql_query($str);

        while($bar=mysql_fetch_object($res))
        {
                $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

        }
 
}  
 else 
{
   
    $optpt="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";  
 }
        
        
        
        
        
        
        
        
        
//echo"<pre>";
//print_r($_SESSION);
//echo"</pre>";
       /* $optgudang="";

        //=================ambil gudang;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='TRAKSI'
                        or tipe='HOLDING')  and induk!=''
                        ";
                $optgudang.="<option value=''>".$_SESSION['lang']['all']."</option>";
}
else
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where induk='".$_SESSION['empl']['kodeorganisasi']."' and length(kodeorganisasi)=4 and kodeorganisasi not like '%HO'";
//                $optgudang.="<option value=''>".$_SESSION['lang']['all']."</option>";
}
else
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'  and induk!=''";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
                $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

        }*/
        
        
/*        
}
else
{
        $optpt="";
        $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
         $optgudang.="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";
   
}
 * 
 */
        
        
        
        
        
        
        $str="select noakun,namaakun from ".$dbname.".keu_5akun
                        where level = '5'
                        order by noakun
                        ";
        $res=mysql_query($str);
//        $optakun="<option value=''>".$_SESSION['lang']['all']."</option>";
        $optakun="<option value=''></option>";
//        $optakun="";
        while($bar=mysql_fetch_object($res))
        {
                $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";

        }
$qwe="01-".date("m-Y");
echo"
<div class='card border-0 shadow-sm mb-3' style='width:50%;'>
    <div class='card-body'>
        <div class='row g-3'>
            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['pt']."</label>
                <select id='pt' class='form-select form-select-sm' onchange='getReg()'>".$optpt."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['regional']."</label>
                <select id='regional' class='form-select form-select-sm' onchange='getUnit()'>".$optReg."</select>
            </div>

            <div class='col-md-12'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['unit']."</label>
                <select id='gudang' class='form-select form-select-sm'>".$optgudang."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tanggalmulai']."</label>
                <input type='text' class='form-control form-control-sm' id='tgl1' name='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10' value='".$qwe."' />
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tanggalsampai']."</label>
                <input type='text' class='form-control form-control-sm' id='tgl2' name='tgl2' onchange='cekTanggal2(this.value);' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10' />
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['noakundari']."</label>
                <select id='akundari' class='form-select form-select-sm' onchange='ambilAkun2(this.options[this.selectedIndex].value)'>".$optakun."</select>
            </div>

            <div class='col-md-6'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['noakunsampai']."</label>
                <select id='akunsampai' class='form-select form-select-sm' onchange='hideById(\"printPanel\")'><option value=''></option></select>
            </div>

            <div class='col-md-12'>
                <button class='btn btn-primary btn-sm' onclick='getLaporanBukuBesarv1()'>
                    <i class='bi bi-funnel-fill me-1'></i>".$_SESSION['lang']['proses']."
                </button>
            </div>
        </div>
    </div>
</div>
";
$arr='';
CLOSE_BOX();
OPEN_BOX('','<i class=\"bi bi-file-text-fill me-2\"></i>Result');
echo"
<div class='mb-3' id='printPanel' style='display:none;'>
    <button class='btn btn-success btn-sm me-2' onclick='jurnalv1KeExcel(event,\"keu_laporanBukuBesarv1_Excel.php\")'>
        <i class='bi bi-file-earmark-excel-fill me-1'></i>Export Excel
    </button>
    <button class='btn btn-danger btn-sm' onclick='jurnalv1KePDF(event,\"keu_laporanBukuBesarv1_pdf.php\")'>
        <i class='bi bi-file-earmark-pdf-fill me-1'></i>Export PDF
    </button>
</div>

<div class='table-responsive'>
    <table class='table table-sm table-bordered table-hover'>
        <thead class='table-primary text-white'>
            <tr>
                <th class='text-center' style='width:30px;'>".$_SESSION['lang']['nourut']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['nojurnal']."</th>
                <th class='text-center' style='width:90px;'>".$_SESSION['lang']['tanggal']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['noakun']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['namakaryawan']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['nmcust']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['namasupplier']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['noreferensi']."</th>
                <th class='text-center' style='width:150px;'>".$_SESSION['lang']['nodok']."</th>
                <th class='text-center' style='width:120px;'>No Cek/Giro</th>
                <th class='text-center' style='width:200px;'>".$_SESSION['lang']['keterangan']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['debet']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['kredit']."</th>
                <th class='text-center' style='width:130px;'>".$_SESSION['lang']['saldo']."</th>
                <th class='text-center' style='width:80px;'>".$_SESSION['lang']['kodeorg']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['kodeblok']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['tahuntanam']."</th>
                <th class='text-center' style='width:120px;'>".$_SESSION['lang']['project']."</th>
                <th class='text-center' style='width:200px;'>".$_SESSION['lang']['project']."</th>
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