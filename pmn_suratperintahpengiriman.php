<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['suratperintahpengiriman'])."</b>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/pmn_suratperintahpengiriman.js" /></script>



<?php
echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['NoKontrak'].":<input type=text id=txtsearchkontrak size=25 maxlength=50 class=myinputtext>";			
                        echo $_SESSION['lang']['nodo'].":<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
/*echo"<img src=\"images/pdf.jpg\" onclick=\"masterPDF('log_prapoht','','','log_print_pdf_pp',event)\" width=\"20\" height=\"20\" />
<img onclick=\"javascript:print()\" style=\"width: 20px; height: 20px; cursor: pointer;\" title=\"Print Page\" src=\"images/printer.png\">";*/
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr><td>".$_SESSION['lang']['nodo']."</td>";
echo"<td>".$_SESSION['lang']['tanggalsurat']."</td>";
echo"<td>".$_SESSION['lang']['NoKontrak']."</td>";
echo"<td>".$_SESSION['lang']['Pembeli']."</td>";
echo"<td>".$_SESSION['lang']['komoditi']."</td>";
echo"<td>".$_SESSION['lang']['kuantitas']."</td>";
echo"<td>".$_SESSION['lang']['dibuatoleh']."</td>";
echo"<td colspan=4 style='text-align:center;'>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
$skeupenagih="select count(*) as rowd from ".$dbname.".keu_penagihanht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$qkeupenagih=mysql_query($skeupenagih) or die(mysql_error($conn));
$rkeupenagih=mysql_num_rows($qkeupenagih);
$totrows=ceil($rkeupenagih/10);
if($totrows==0){
    $totrows=1;
}
$isiRow='';
for($er=1;$er<=$totrows;$er++){
    $isiRow.="<option value='".$er."'>".$er."</option>";
}
echo"<tfoot id=footData>";
//<tr>";
//if($totrows==1){
//   echo"<td colspan=\"10\" align=\"center\">
//    <img src=\"images/skyblue/first.png\">&nbsp;
//    <img src=\"images/skyblue/prev.png\">&nbsp;
//    <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."
//    </select>&nbsp;
//    <img src=\"images/skyblue/next.png\">&nbsp;<img src=\"images/skyblue/last.png\"></td>";
//}else{
//echo"<td colspan=\"10\" align=\"center\">
//    <img src=\"images/skyblue/first.png\" onclick='loadPage(0)' style=curosr:pointer >&nbsp;
//    <img src=\"images/skyblue/prev.png\">&nbsp;
//    <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."
//    </select>&nbsp;
//    <img src=\"images/skyblue/next.png\">&nbsp;<img src=\"images/skyblue/last.png\" onclick=loadPage(".$totrows.") style=curosr:pointer></td>";
//
//}
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

#byr ke
$whereJam=" kasbank=1 and detail=1 and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
$sakun="select distinct noakun,namaakun from ".$dbname.".keu_5akun 
        where  ".$whereJam." order by namaakun asc";
$qakun=mysql_query($sakun) or die(mysql_error($conn));
$optAkun='';
while($rakun=  mysql_fetch_assoc($qakun)){
    $optAkun.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
}


$optKepada="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iKepada=" select * from ".$dbname.".pmn_5kepada order by kepada asc ";
$nKepada=  mysql_query($iKepada) or die (mysql_error($conn));
while($dKepada=  mysql_fetch_assoc($nKepada))
{
    $optKepada.="<option value='".$dKepada['id']."'>".$dKepada['kepada']." - ".$dKepada['alamat']."</option>";
}

$optTtd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTtd=" select * from ".$dbname.".pmn_5ttd order by nama asc ";
$nTtd=  mysql_query($iTtd) or die (mysql_error($conn));
while($dTtd=  mysql_fetch_assoc($nTtd))
{
    $optTtd.="<option value='".$dTtd['nama']."'>".$dTtd['nama']." - ".$dTtd['jabatan']."</option>";
}


$iX="select * from  ".$dbname.".pmn_5franco ";
$nX=  mysql_query($iX) or die (mysql_error($conn));
while($dX=  mysql_fetch_assoc($nX))
{
    $optSerah.="<option value='".$dX['id_franco']."'>".$dX['franco_name']."</option>";
}


#kodepelanggan
$optCust='';
$sakun="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer
        order by namacustomer asc";
$qakun=mysql_query($sakun) or die(mysql_error($conn));
while($rakun=  mysql_fetch_assoc($qakun)){
    $optCust.="<option value='".$rakun['kodecustomer']."'>".$rakun['kodecustomer']."-".$rakun['namacustomer']."</option>";
}

$arr="##nokontrak##nokontrakInternal##nodo##kodecustomer##kepada##tanggalsurat##waktupenyerahan##tempatpenyerahan##dibuat##lain##jabatan##ttd##qty";
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table style=width:100%;>";
echo"<tr><td>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['eksternal']."</td><td><input type=text id=nokontrak class=myinputtext style=width:150px; readonly onclick=\"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['eksternal']."','Eksternal','<div id=formPencariandata></div>',event)\" /></td>";
echo"<td>".$_SESSION['lang']['nodo']."</td><td><input type=text id=nodo class=myinputtext style=width:150px;  readonly></td></tr>";

echo"<tr><td>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['internal']."</td><td><input type=text id=nokontrakInternal disabled=disabled class=myinputtext style=width:150px; readonly onclick=\"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['internal']."','Internal','<div id=formPencariandata></div>',event)\" /></td>";
echo"<td>".$_SESSION['lang']['kepada']."</td>"
    . "<td><select id=kepada style=width:300px>".$optKepada."</select></td>"
    . "</tr>";//

echo"<tr><td>".$_SESSION['lang']['Pembeli']."</td><td><select id=kodecustomer style=width:150px disabled=true>".$optCust."</select></td>";
echo"<td>".$_SESSION['lang']['tanggalsurat']."</td><td><input type=text class=myinputtext id=tanggalsurat readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 /></td></tr>";


echo"<tr><td>".$_SESSION['lang']['waktupenyerahan']."</td><td><input type=text id=waktupenyerahan class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' /></td>";
echo"<td>".$_SESSION['lang']['tandatangan']."</td><td style='vertical-align:top;'><select id=ttd style=width:150px>".$optTtd."</select></td>";
echo"<td hidden>".$_SESSION['lang']['dibuat']."</td><td style='vertical-align:top;' hidden><input type=text id=dibuat class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)'  /></td></tr>";

echo"<tr><td>".$_SESSION['lang']['tempatpenyerahan']."</td><td style='vertical-align:top;'><select id=tempatpenyerahan style=width:150px>".$optSerah."</select></td>";
echo"<td style='vertical-align:top;' hidden>".$_SESSION['lang']['jabatan']."</td><td  hidden style='vertical-align:top;'><input type=text id=jabatan class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)'/>";
echo"<td style='vertical-align:top;'>QTY</td><td style='vertical-align:top;'><input type=text id=qty class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('qty',0)\" onkeypress='return tanpa_kutip(event)'/></tr>";

echo"<tr><td style='vertical-align:top;'>Lain-lain</td><td><textarea id='lain' style=width:145px; onkeypress='return tanpa_kutip(event);' maxlength=40></textarea></td>";
echo"<tr><td colspan='3'></td></tr>";


echo"<tr><td></td><td colspan=3>
		 <input type=hidden id=proses value='insert'  />
		 <input type=hidden id=kdOrg value=''  />
		 <button class=mybutton onclick=saveData('pmn_slave_suratperintahpengiriman','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
         <button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button></td></tr>";


echo"</table></fieldset>"; 
echo"<fieldset style=float:left;><legend>Note</legend>* Field Pembeli, Waktu Penyerahan dan Kepada otomatis muncul jika No Kontrak sudah diinput<br>* Ketika pembuatan Form Baru No DO (Delivery Order) otomatis terbentuk jika melakukan aksi simpan</fieldset></div>";


CLOSE_BOX();
echo close_body(); ?>
