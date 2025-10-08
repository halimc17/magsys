<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanjurnal']).'</b>');

//get existing period
$str="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc";

$res=mysql_query($str);
$optper='';
while($bar=mysql_fetch_object($res))
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

//$optgudang='';
/*if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
          where tipe='PT'
          order by namaorganisasi";
    $res=mysql_query($str);
    $optpt="";
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
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar=mysql_fetch_object($res))
    {
        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {   
    //=================ambil PT;
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
          where tipe='PT'
          order by namaorganisasi";
    $res=mysql_query($str);
    $optpt="";
    while($bar=mysql_fetch_object($res))
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL')  and induk!=''
        ";
    $res=mysql_query($str);
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar=mysql_fetch_object($res))
    {
        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
} else {
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang.="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";   
}  
*/

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{ 
    $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optgudang=$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";


    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
          where tipe='PT'
          order by namaorganisasi";
    $res=mysql_query($str);
    //$optpt="";
    while($bar=mysql_fetch_object($res))
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
    
    $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
    $iUnit="select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."' ";
    $nUnit=  mysql_query($iUnit) or die (mysql_error($conn));
    while($dUnit=  mysql_fetch_assoc($nUnit))
    {
        $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
    }
    $optgudang = $optUnit;
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    //$optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
} else {
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}
    

$optKel="<option value=''>".$_SESSION['lang']['all']."</option>";
$iKel="select distinct(kodekelompok) as kodekelompok,keterangan from ".$dbname.".keu_5kelompokjurnal";
$nKel=mysql_query($iKel) or die (mysql_error($conn));
while($dKel=  mysql_fetch_assoc($nKel))
{
    $optKel.="<option value='".$dKel['kodekelompok']."'>".$dKel['kodekelompok']." - ".$dKel['keterangan']."</option>";
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

echo"
<div class='row g-3' style='max-width: 50%;'>
    <div class='col-md-6'>
        <label for='pt' class='form-label'>".$_SESSION['lang']['pt']."</label>
        <select id='pt' class='form-select form-select-sm' onchange='getReg()'>".$optpt."</select>
    </div>

    <div class='col-md-6'>
        <label for='regional' class='form-label'>".$_SESSION['lang']['regional']."</label>
        <select id='regional' class='form-select form-select-sm' onchange='getUnit()'>".$optReg."</select>
    </div>

    <div class='col-md-6'>
        <label for='gudang' class='form-label'>".$_SESSION['lang']['unit']."</label>
        <select id='gudang' class='form-select form-select-sm'>".$optgudang."</select>
    </div>

    <div class='col-md-6'>
        <label class='form-label'>".$_SESSION['lang']['periode']."</label>
        <div class='d-flex gap-2 align-items-center'>
            <select id='periode' class='form-select form-select-sm' onchange=\"hideById('printPanel')\">".$optper."</select>
            <span class='mx-1'>s/d</span>
            <select id='periode1' class='form-select form-select-sm' onchange=\"hideById('printPanel')\">".$optper."</select>
        </div>
    </div>

    <div class='col-md-6'>
        <label for='revisi' class='form-label'>".$_SESSION['lang']['revisi']."</label>
        <select id='revisi' class='form-select form-select-sm' onchange=\"hideById('printPanel')\">".$optrev."</select>
    </div>

    <div class='col-md-6'>
        <label for='kdKel' class='form-label'>".$_SESSION['lang']['kodekelompok']."</label>
        <select id='kdKel' class='form-select form-select-sm'>".$optKel."</select>
    </div>

    <div class='col-md-6'>
        <label for='nojurnal' class='form-label'>".$_SESSION['lang']['nojurnal']."</label>
        <input type='text' id='nojurnal' class='form-control form-control-sm' onkeypress=\"return tanpa_kutip(event);\">
    </div>

    <div class='col-md-6'>
        <label for='ref' class='form-label'>".$_SESSION['lang']['noreferensi']."</label>
        <input type='text' id='ref' class='form-control form-control-sm' onkeypress=\"return tanpa_kutip(event);\">
    </div>

    <div class='col-md-12'>
        <label for='ket' class='form-label'>".$_SESSION['lang']['keterangan']."</label>
        <input type='text' id='ket' class='form-control form-control-sm' onkeypress=\"return tanpa_kutip(event);\">
    </div>

    <div class='col-12'>
        <hr class='my-3'>
        <div class='d-flex gap-2'>
            <button class='btn btn-primary btn-sm' onclick='getLaporanJurnal()'>
                <i class='bi bi-gear-fill me-1'></i>".$_SESSION['lang']['proses']."
            </button>
            <button class='btn btn-success btn-sm' onclick=\"fisikKeExcel(event,'keu_laporanJurnal_Excel.php')\">
                <i class='bi bi-file-excel-fill me-1'></i>".$_SESSION['lang']['excel']."
            </button>
            <button class='btn btn-danger btn-sm' onclick=\"fisikKePDF(event,'keu_laporanJurnal_pdf.php')\">
                <i class='bi bi-file-pdf-fill me-1'></i>".$_SESSION['lang']['pdf']."
            </button>
        </div>
    </div>
</div>
";
CLOSE_BOX();
OPEN_BOX('','<b>HASIL LAPORAN</b>');
echo"
<div id='printPanel' style='display:none;' class='mb-3'>
    <div class='btn-group btn-group-sm' role='group'>
        <button type='button' class='btn btn-success' onclick=\"fisikKeExcel(event,'keu_laporanJurnal_Excel.php')\" title='Export ke Excel'>
            <i class='bi bi-file-excel-fill me-1'></i>Excel
        </button>
        <button type='button' class='btn btn-danger' onclick=\"fisikKePDF(event,'keu_laporanJurnal_pdf.php')\" title='Export ke PDF'>
            <i class='bi bi-file-pdf-fill me-1'></i>PDF
        </button>
    </div>
</div>

<div class='table-responsive'>
    <table class='table table-striped table-hover table-sm table-bordered'>
        <thead class='table-dark sticky-top'>
            <tr>
                <th class='text-center' style='width:50px;'>".$_SESSION['lang']['nourut']."</th>
                <th class='text-center' style='width:250px;'>".$_SESSION['lang']['nojurnal']."</th>
                <th class='text-center' style='width:80px;'>".$_SESSION['lang']['tanggal']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['organisasi']."</th>
                <th class='text-center' style='width:80px;'>".$_SESSION['lang']['noakun']."</th>
                <th class='text-center' style='width:200px;'>".$_SESSION['lang']['namaakun']."</th>
                <th class='text-center' style='width:240px;'>".$_SESSION['lang']['keterangan']."</th>
                <th class='text-center' style='width:70px;'>Arus Kas</th>
                <th class='text-center' style='width:120px;'>".$_SESSION['lang']['debet']."</th>
                <th class='text-center' style='width:120px;'>".$_SESSION['lang']['kredit']."</th>
                <th class='text-center' style='width:200px;'>".$_SESSION['lang']['noreferensi']."</th>
                <th class='text-center' style='width:100px;'>".$_SESSION['lang']['kodeblok']."</th>
                <th class='text-center' style='width:80px;'>".$_SESSION['lang']['tahuntanam']."</th>
                <th class='text-center' style='width:50px;'>".$_SESSION['lang']['revisi']."</th>
            </tr>
        </thead>
        <tbody id='container'>
        </tbody>
    </table>
</div>
";
CLOSE_BOX();
close_body();
?>