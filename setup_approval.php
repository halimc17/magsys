<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/approval.js'></script>
<?php
include('master_mainMenu.php');

$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

//OPEN_BOX('',$_SESSION['lang']['input'].' '.$_SESSION['lang']['persetujuan']);
OPEN_BOX();
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi";
$res=mysql_query($str);
$optOrg='';
while($bar=mysql_fetch_object($res)){
    $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
$str="select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','1','7','8') order by namakaryawan";
$res=mysql_query($str);
$optkar='';
while($bar=mysql_fetch_object($res)){
    $optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
}
$optapp="";
for($i=1;$i<=5;$i++)
{
     $optapp.="<option value='PP".$i."'>PP".$i."</option>";
}

//<td>".$_SESSION['lang']['kode']."</td><td><input type=text id=app size=3 maxlength=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
echo"<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['input']." ".$_SESSION['lang']['persetujuan']."</b></legend>
	<table>
     <tr><td>".$_SESSION['lang']['kodeorg']."</td><td>
     <select id=kodeorg>".$optOrg."</select>    
     </td></tr>
	 <tr><td>".$_SESSION['lang']['kode']."</td><td><select id=app>".$optapp."</select></td></tr> 
        <tr><td>".$_SESSION['lang']['namakaryawan']."</td><td><select id=karyawanid>".$optkar."</select></td></tr> 
     </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanDep()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelDep()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";

echo"<fieldset style='width:500px;'><legend><b>".$_SESSION['lang']['keterangan']."</b></legend>
    <table>
        <tr>
            <td valign=top>Kode Organisasi</td>
            <td valign=top>:</td>
            <td valign=top>Unit kerja dimana PP tersebut dibuat</td>
        </tr>
        <tr>
            <td valign=top>Kode Aplikasi</td>
            <td valign=top>:</td>
            <td valign=top>Kode untuk level persetujuan, isi hanya (PP1-PP5)</td>
        </tr>
        <tr>
            <td valign=top>".$_SESSION['lang']['namakaryawan']."</td>
            <td valign=top>:</td>
            <td valign=top>Nama karyawan yang dapat menyetujui PP</td>
        </tr>
   
        <tr>
            <td valign=top>Contoh</td>
            <td valign=top>:</td>
            <td valign=top>Kode : DUKE, Kode App : PP2, Karyawan : Indra <br> Berarti : Indra hanya dapat menyetujui PP jika Indra berada di level ke-2 dan untuk unit DUKE </td>
        </tr>
     </table></fieldset><br>";


echo open_theme($_SESSION['lang']['list']);

	$str1="select a.*,b.namakaryawan from ".$dbname.".setup_approval a
               left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               order by kodeunit";
	$res1=mysql_query($str1);
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader><td style='width:150px;'>".$_SESSION['lang']['kodeorg']."</td><td>".$_SESSION['lang']['namaorganisasi']."</td><td>".$_SESSION['lang']['kode']."</td><td>".$_SESSION['lang']['namakaryawan']."</td><td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>";
	while($bar1=mysql_fetch_object($res1))
	{
		echo"<tr class=rowcontent><td align=left>".$bar1->kodeunit."</td><td align=left>".$nmOrg[$bar1->kodeunit]."</td><td>".$bar1->applikasi."</td><td>".$bar1->namakaryawan."</td>
                    <td>
                   <img src=images/skyblue/delete.png class=resicon  caption='Edit' onclick=\"dellField('".$bar1->kodeunit."','".$bar1->applikasi."','".$bar1->karyawanid."');\">     
                   </td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>