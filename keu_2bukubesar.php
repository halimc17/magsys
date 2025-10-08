<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body(); 
?>
<script language=javascript1.2 src="js/keu_laporan.js"></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['neracasaldo']).'</b>');

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

//get revisi available
//$str="select distinct revisi from ".$dbname.".keu_jurnalht
//      order by revisi";	  
//$res=mysql_query($str);
//#$optper="<option value=''>".$_SESSION['lang']['sekarang']."</option>";
//$optrev="";
//while($bar=mysql_fetch_object($res))
//{
    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";    
    $optrev.="<option value='5'>5</option>";     
//}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi";
    $res=mysql_query($str);
    $optpt="";
    $optpt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=mysql_fetch_object($res))
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
        or tipe='HOLDING')  and induk!=''
        ";
    $res=mysql_query($str);
    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=mysql_fetch_object($res))
    {
//        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else
if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi";
    $res=mysql_query($str);
    $optpt="";
    $optpt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=mysql_fetch_object($res))
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL')  and induk!=''
        ";
    $res=mysql_query($str);
    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=mysql_fetch_object($res))
    {
//        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else    
{
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}
/*".$_SESSION['lang']['pt']." : "."<select id=pt style='width:200px;'  onchange=ambilAnakBB(this.options[this.selectedIndex].value)>".$optpt."</select>
    <select id=gudang style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select>*/
echo"
<div class='card border-0 shadow-sm mb-3' style='width:50%;'>
    <div class='card-body'>
        <div class='row g-3'>
            <div class='col-md-12'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['pt']."</label>
                <div class='row g-2'>
                    <div class='col-md-4'>
                        <select id='pt' class='form-select form-select-sm' onchange='getReg()'>
                            ".$optpt."
                        </select>
                    </div>
                    <div class='col-md-4'>
                        <select id='regional' class='form-select form-select-sm' onchange='getUnit()'>
                            ".$optReg."
                        </select>
                    </div>
                    <div class='col-md-4'>
                        <select id='gudang' class='form-select form-select-sm'>
                            ".$optgudang."
                        </select>
                    </div>
                </div>
            </div>

            <div class='col-md-4'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['periode']."</label>
                <select id='periode' class='form-select form-select-sm' onchange='hideById(\"printPanel\")'>
                    ".$optper."
                </select>
            </div>

            <div class='col-md-4'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['tglcutisampai']." ".$_SESSION['lang']['periode']."</label>
                <select id='periode1' class='form-select form-select-sm' onchange='hideById(\"printPanel\")'>
                    ".$optper."
                </select>
            </div>

            <div class='col-md-4'>
                <label class='form-label fw-semibold'>".$_SESSION['lang']['revisi']."</label>
                <select id='revisi' class='form-select form-select-sm' onchange='hideById(\"printPanel\")'>
                    ".$optrev."
                </select>
            </div>

            <div class='col-md-12'>
                <button class='btn btn-primary btn-sm' onclick='getLaporanBukuBesar()'>
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
    <button class='btn btn-success btn-sm me-2' onclick='fisikKeExcel(event,\"keu_laporanBukuBesar_Excel.php\")'>
        <i class='bi bi-file-earmark-excel-fill me-1'></i>Export Excel
    </button>
    <button class='btn btn-danger btn-sm' onclick='fisikKePDF(event,\"keu_laporanBukuBesar_pdf.php\")'>
        <i class='bi bi-file-earmark-pdf-fill me-1'></i>Export PDF
    </button>
</div>

<div class='table-responsive'>
    <table class='table table-sm table-bordered table-hover'>
        <thead class='table-primary text-white'>
            <tr>
                <th class='text-center' style='width:50px;'>".$_SESSION['lang']['nomor']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['noakun']."</th>
                <th class='text-center'>".$_SESSION['lang']['namaakun']."</th>
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