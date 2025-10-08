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

echo"<fieldset>
     <legend>".$_SESSION['lang']['laporanjurnal']."</legend>
         
        <table>
            <tr>
                <td>".$_SESSION['lang']['pt']."</td>
                <td>:</td>
                <td><select id=pt style='width:200px;'  onchange=getReg()>".$optpt."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['regional']."</td>
                <td>:</td>
                <td><select id=regional style='width:150px;' onchange=getUnit()>".$optReg."</select> </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=gudang style='width:150px;'>".$optgudang."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>:</td>
                <td><select id=periode onchange=hideById('printPanel')>".$optper."</select> s/d <select id=periode1 onchange=hideById('printPanel')>".$optper."</select>     </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['revisi']."</td>
                <td>:</td>
                <td><select id=revisi onchange=hideById('printPanel')>".$optrev."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['kodekelompok']."</td>
                <td>:</td>
                <td><select id=kdKel>".$optKel."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nojurnal']."</td>
                <td>:</td>
                <td><input type=text id=nojurnal size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['noreferensi']."</td>
                <td>:</td>
                <td><input type=text id=ref size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                    <td>".$_SESSION['lang']['keterangan']."</td>
                <td>:</td>
                <td><input type=text id=ket size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            
        </table>

             
       <br>

             

	 <br><button class=mybutton onclick=getLaporanJurnal()>".$_SESSION['lang']['proses']."</button>
	 <button class=mybutton onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php')>".$_SESSION['lang']['excel']."</button>
	 <button class=mybutton onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php')>".$_SESSION['lang']['pdf']."</button>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','Result:');
echo"<span id=printPanel style='display:none;'>
     <img onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>
	 <div style='width:1480px;display:fixed;'>
       <table class=sortable cellspacing=1 border=0 width=1480px>
	     <thead>
		    <tr>
                        <td align=center style='width:50px;'>".$_SESSION['lang']['nourut']."</td>
                        <td align=center style='width:250px;'>".$_SESSION['lang']['nojurnal']."</td>
                        <td align=center style='width:80px;'>".$_SESSION['lang']['tanggal']."</td>
                        <td align=center style='width:64px;'>".$_SESSION['lang']['organisasi']."</td>
                        <td align=center style='width:60px;'>".$_SESSION['lang']['noakun']."</td>
                        <td align=center style='width:200px;'>".$_SESSION['lang']['namaakun']."</td>
                        <td align=center  style='width:240px;'>".$_SESSION['lang']['keterangan']."</td>
                        <td align=center  style='width:70px;'>Arus Kas</td>
                        <td align=center  style='width:100px;'>".$_SESSION['lang']['debet']."</td>
                        <td align=center style='width:100px;'>".$_SESSION['lang']['kredit']."</td>
                        <td align=center style='width:200px;'>".$_SESSION['lang']['noreferensi']."</td>    
                        <td align=center style='width:80px;'>".$_SESSION['lang']['kodeblok']."</td>
                        <td align=center style='width:60px;'>".$_SESSION['lang']['tahuntanam']."</td>
                        <td align=center style='width:30px;'>".$_SESSION['lang']['revisi']."</td>
		   </tr>  
		 </thead>
		 <tbody>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div>         

	 <div style='width:1500px;height:359px;overflow:scroll;'>
       <table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>
		  <tr>
		 </tr>  
		 </thead>
		 <tbody id=container>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div>";
CLOSE_BOX();
close_body();
?>