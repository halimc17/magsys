<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/vhc_kegiatan.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['vhc_kegiatan']);

// Akun dari Kelompok Kegiatan
$optAkunKeg = makeOption($dbname,'setup_klpkegiatan',"noakun,noakun");
$whereAkun = "";
//foreach($optAkunKeg as $akun) {
//	if(!empty($whereAkun)) $whereAkun .= " OR ";
//	$whereAkun .= "noakun LIKE '".$akun."%'";
//}

if($_SESSION['language']=='EN'){
    $dd='namaakun1';
}else{
    $dd='namaakun';
}
$str="select noakun,".$dd." as namakegiatan from ".$dbname.".keu_5akun where detail=1 ";
if(!empty($whereAkun)) {
	$str .= " and (".$whereAkun.")";
}
//echo $str;
$str.="order by noakun";
$res=mysql_query($str);
$optakun="";
while($bar=mysql_fetch_object($res))
{
    $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." ".$bar->namakegiatan."</option>";
}

$strSat="select satuan from ".$dbname.".setup_satuan";
$resSat=mysql_query($strSat);
$optSatuan="";
while($barSat=mysql_fetch_object($resSat))
{
    $optSatuan.="<option value='".$barSat->satuan."'>".$barSat->satuan."</option>";
}

echo"<fieldset style='width:700px;'><table>
     <tr><td>".$_SESSION['lang']['kodekegiatan']."</td><td><input type=text id=kodekegiatan size=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
     <tr><td>".$_SESSION['lang']['namakegiatan']."</td><td><input type=text id=namakegiatan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
	 <tr><td>".$_SESSION['lang']['satuan']."</td><td><select id=satuan>".$optSatuan."</select></td></tr>
     <tr><td>".$_SESSION['lang']['noakun']."</td><td><select id=noakun>".$optakun."</select></td></tr>        
     </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanKegiatan()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelKegiatan()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
echo open_theme($_SESSION['lang']['daftarkegiatan']);
echo "<div id=container>";
	$str1="select * from ".$dbname.".vhc_kegiatan order by kodekegiatan";
	$res1=mysql_query($str1);
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader><td style='width:150px;'>".$_SESSION['lang']['kodekegiatan']."</td>
                     <td>".$_SESSION['lang']['namakegiatan']."</td>
                     <td>".$_SESSION['lang']['satuan']."</td>
                     <td>".$_SESSION['lang']['noakun']."</td>
                     <td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody>";
	while($bar1=mysql_fetch_object($res1))
	{
		echo"<tr class=rowcontent><td align=center>".$bar1->kodekegiatan."</td>
                    <td>".$bar1->namakegiatan."</td>
                    <td>".$bar1->satuan."</td>
                    <td>".$bar1->noakun."</td>    
                        <td style='text-align:center'><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodekegiatan."','".$bar1->namakegiatan."','".$bar1->satuan."','".$bar1->noakun."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>