<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script   language=javascript1.2 src='js/vhc_project.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX();
/*
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
$str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4
    order by induk, tipe, namaorganisasi";
}
else
{
    $str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'
    order by induk, tipe, namaorganisasi";
}
*/

 $optSub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

    /*$str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4 and kodeorganisasi  not like '%HO'
    order by induk, tipe, namaorganisasi";
    */
 
 $str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4 
    order by induk, tipe, namaorganisasi";
 
$res=mysql_query($str);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=mysql_fetch_object($res))
{
    $optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
if($_SESSION['language']=='EN'){
    $dd='namatipe1 as namatipe';
}else{

    $dd='namatipe';
}    
$str="select kodetipe, ".$dd." from ".$dbname.".sdm_5tipeasset
    order by kodetipe";
$res=mysql_query($str);
$optaset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=mysql_fetch_object($res))
{
    $optaset.="<option value='".$bar->kodetipe."'>".$bar->kodetipe." - ".$bar->namatipe."</option>";
}

$kamusjenis['AK']='Aktiva Dalam Konstruksi/Activa Under Construction';
$kamusjenis['PB']='Pabrikasi';

$optjenis="";
$arrjenis=getEnum($dbname,'project','tipe');
foreach($arrjenis as $kei=>$fal)
{
    if($fal=='PB')
    {
     #Pabrikasi  belum aktif  karena akunnya belum ada, pastikan akunnya sudah ada dan didaftar  pada parameter jurnal dengan kode
    #PAB       
    } 
    else{
          $optjenis.="<option value='".$kei."'>".$fal." ".$kamusjenis[$fal]."</option>";
    }
    
} 	

$optKel="";
$arrKel=getEnum($dbname,'project','kelompok');
foreach($arrKel as $kel)
{
   
	$optKel.="<option value='".$kel."'>".$kel."</option>";
  
} 

$optSatuan = makeOption($dbname,'setup_satuan','satuan,satuan');

echo"<fieldset style='width:500px;'>
    <legend>Project</legend>
    <table cellspacing=1 border=0>
    <tr><td align=right>
        ".$_SESSION['lang']['unit']."
    </td><td>
        <select id=unit style='width:200px;'>".$optunit."</select>
    </td></tr>
    <tr><td align=right>
        ".$_SESSION['lang']['aset']."
    </td><td>
        <select id=aset onchange=getSub() style='width:200px;'>".$optaset."</select>
    </td></tr>
    
    <tr><td align=right>
        Sub Asset
    </td><td>
        <select id=sub style='width:200px;'>".$optSub."</select>
    </td></tr>

    <tr><td align=right>
        ".$_SESSION['lang']['jenis']."
    </td><td>
        <select id=jenis style='width:200px;'>".$optjenis."</select>
    </td></tr>
    
    <tr><td align=right>
        ".$_SESSION['lang']['nama']."
    </td><td>
        <input type=text id=nama class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:200px;'>
    </td></tr>
    <tr><td align=right>
        ".$_SESSION['lang']['tanggalmulai']."
    </td><td>
        <input style='width:200px;' id=tanggalmulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
    </td></tr>
    <tr><td align=right>
        ".$_SESSION['lang']['tanggalsampai']."
    </td><td>
        <input style='width:200px;' id=tanggalselesai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
    </td></tr>
    
    </table>
    <input type=hidden value=insert id=method>
    <input type=hidden value='' id=kode>
    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>	 
    </fieldset>";

//$qwe=addZero('qwe',5);
//echo "qwe:".$qwe;

echo "<div id=dataDisimpan><fieldset style=width:800px;><legend>".$_SESSION['lang']['datatersimpan']."</legend><div style='height:350px;width:1220px;overflow:scroll;'>
      <table class=sortable border=0 cellspacing=1>
	  <thead>
	  <tr>
	  <td align=center>".$_SESSION['lang']['kode']."</td>
	  <td align=center>".$_SESSION['lang']['unit']."</td>
	  <td align=center>".$_SESSION['lang']['jenis']."</td>
	  <td align=center>".$_SESSION['lang']['nama']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsampai']."</td>
          <td align=center>".$_SESSION['lang']['updateby']."</td>
	  <td align=center>".$_SESSION['lang']['action']."</td>
          <td align=center>".$_SESSION['lang']['print']."</td>
	  </tr>
	  </thead>
	  <tbody id=container>";
echo"<script>loadData()</script>";

echo "</tbody>
    <tfoot>
    </tfoot>
    </table>
    </div></fieldset></div>";
echo"<div id=detailInput style=display:none>";
$frmdt="<fieldset style=width:800px;><legend>".$_SESSION['lang']['detail']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td>".$_SESSION['lang']['namakegiatan']."</td>";

$frmdt.="<td>".$_SESSION['lang']['satuan']."</td>";
$frmdt.="<td>".$_SESSION['lang']['volume']."</td>";
$frmdt.="<td>".$_SESSION['lang']['bobot']." %</td>";

$frmdt.="<td>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
$frmdt.="<tr class=rowcontent><td><input type=text id=kdProj class=myinputtext maxlength=20 onkeypress=\"return tanpa_kutip(event);\" style='width:200px;'  disabled></td>";
$frmdt.="<td><input type=text id=namaKeg class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'></td>";

$frmdt.="<td>".makeElement('satKeg','select',"",array(),$optSatuan)."</td>";
$frmdt.="<td><input type=text id=volKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";
$frmdt.="<td><input type=text id=bobotKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";


$frmdt.="<td><input style='width:100px;' id=tanggalMulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')."></td>";
$frmdt.="<td><input style='width:100px;' id=tanggalSampai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')."></td>";
$frmdt.="<td><img src='images/save.png' class='zImgBtn' style='cursor:pointer;' onclick=addDetail() /></td></tr></tbody></table> <button class=mybutton onclick=doneSlsi()>".$_SESSION['lang']['selesai']."</button></fieldset><input type=hidden id=kegId />";
$frmdt.="<div>";
$frmdt.="<fieldset style=width:800px;><legend>".$_SESSION['lang']['detail']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td>".$_SESSION['lang']['namakegiatan']."</td>";

$frmdt.="<td>".$_SESSION['lang']['satuan']."</td>";
$frmdt.="<td>".$_SESSION['lang']['volume']."</td>";
$frmdt.="<td>".$_SESSION['lang']['bobot']." %</td>";

$frmdt.="<td>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td>".$_SESSION['lang']['action']."</td></tr></thead><tbody id=printDat>";
$frmdt.="</tbody></table></fieldset>";
$frmdt.="</div>";
echo $frmdt;
echo"</div>";
CLOSE_BOX();
echo close_body();
?>
