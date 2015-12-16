<?php
//@Copy nangkoelframework
//--IND--
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>Service Kendaraan</b>");//".$_SESSION['lang']['pemeliharaanMesin']."
//print_r($_SESSION['temp']);
?>


<script language=javascript1.2 src='js/vhc_service.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>



<!--deklarasi untuk option-->
<?php


$svhc="select kodevhc,jenisvhc,tahunperolehan from ".$dbname.".vhc_5master  order by kodevhc"; //echo $svhc;
$qvhc=mysql_query($svhc) or die(mysql_error());
$optVhc="";
while($rvhc=mysql_fetch_assoc($qvhc))
{
    $optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']."[".$rvhc['tahunperolehan']."]</option>";
}
$svhc2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='WORKSHOP' 
                and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc"; //echo $svhc;
$qvhc2=mysql_query($svhc2) or die(mysql_error());
$optOrg="";
while($rvhc2=mysql_fetch_assoc($qvhc2))
{
    $optOrg.="<option value='".$rvhc2['kodeorganisasi']."'>".$rvhc2['namaorganisasi']."</option>";
}


$optOrgTr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'  
//                 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc"; //echo $svhc;
$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'  
                 order by namaorganisasi asc"; //echo $svhc;
$qvhc23=mysql_query($svhc23) or die(mysql_error());
while($rvhc23=mysql_fetch_assoc($qvhc23))
{
    $optOrgTr.="<option value='".$rvhc23['kodeorganisasi']."'>".$rvhc23['namaorganisasi']."</option>";
}


$optKaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iKar="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and
			b.namajabatan like '%Mechanic%'";
$nKar=mysql_query($iKar) or die(mysql_error($conn));
while($dKar=mysql_fetch_assoc($nKar))
{
    $optKaryawan.="<option value=".$dKar['karyawanid'].">".$dKar['namakaryawan']." [".$dKar['nik']."]</option>";
}

	
?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo "No. Transaksi : <input type=text class=myinputtext id=schTran />";
			echo $_SESSION['lang']['tanggalmasuk'].":<input type=text class=myinputtext id=schTgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo "No. Referensi : <input type=text class=myinputtext id=schRef />";
                        echo"<button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>

<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php echo"
<div id=listData style=display:block>";//buka list data
OPEN_BOX();
	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=contain  style=display:block> 
                    <script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

//<td><select id=pabrik onchange=get_isi(this.options[this.selectedIndex].value,this.options[this.selectedIndex].text) style=\"width:150px;\">'".$optOrg."'</select></td>

echo "<div id=headher style=display:none>";//buka diff
OPEN_BOX();//<td><select id=kdorg disabled style=\"width:150px;\"><option  value='".$kdor."'>".$nmor."</option></select></td>
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0>
        <tr>
            <td>".$_SESSION['lang']['workshop']."</td>
            <td>:</td>
            <td><select id=codeOrg name=codeOrg style=width:150px;  onchange=getNotrans(0)><option value=></option> $optOrg;</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['notransaksi']."</td>
            <td>:</td>
            <td><input type=text disabled id=trans_no name=trans_no class=myinputtext style=width:150px; /></td>
        </tr>
        <tr>
            <td> ".$_SESSION['lang']['kodetraksi']."</td>
            <td>:</td>
            <td><select id=kdTraksi name=kdTraksi style=width:150px; onchange=getKdVhc(0,0) > $optOrgTr;</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['kodevhc']."</td>
            <td>:</td>
            <td><select id=vhc_code onchange=getKm() name=vhc_code style=width:150px;>$optVhc</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['nodok']." Referensi</td>
            <td>:</td>
            <td><input type=text id=nodok name=nodok class=myinputtext style=width:150px; /></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggalmasuk']."</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tgl_ganti name=tgl_ganti onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=width:150px; /></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggalkeluar']."</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tgl_keluar name=tgl_keluar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=width:150px; /></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['downtime']."</td>
            <td>:</td>
            <td><input type=text class=myinputtextnumber id=dwnTime name=dwnTime onkeypress=return angka_doang(event);  value=0  maxlength=10 style=width:150px; />".$_SESSION['lang']['jmlhJam']."</td>
        </tr>
        
        <tr>
            <td>KM/HM Masuk</td>
            <td>:</td>
            <td><input type=text disabled class=myinputtextnumber value=0 id=kmmasuk name=kmawal onkeypress=return angka_doang(event);  value=0  maxlength=10 style=width:150px; /></td>
        </tr>
        <tr>
            <td>KM/HM Keluar</td>
            <td>:</td>
            <td><input type=text class=myinputtextnumber id=kmkeluar name=kmakhir onkeypress=return angka_doang(event);  value=0  maxlength=10 style=width:150px; /></td>
        </tr>
        
        <tr>
            <td> ".$_SESSION['lang']['descDamage']."</td>
            <td>:</td>
            <td><textarea name=textarea id=descDmg cols=45 rows=5 onkeypress=return tanpa_kutip(event);></textarea></td>
        </tr>        

        <tr>
            <td>Alasan Terlambat</td>
            <td>:</td>
            <td><textarea name=textarea id=terlambat style=width:150px; rows=3 onkeypress=return tanpa_kutip(event);></textarea></td>
        </tr>
	<tr>
		<td colspan=2></td>
		<td>
                    <button id=savehead class=mybutton onclick=\"saveHeader()\">".$_SESSION['lang']['save']."</button>
                    <button id=batal class=mybutton onclick=cancelHead()>".$_SESSION['lang']['cancel']."</button>
                   
                </td>
                <input type=hidden id=proses value='insert'>
		
	</tr>
	
</table>
</fieldset>";// <button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
CLOSE_BOX();
echo"</div>";
?>



<?php
echo"<div id=detailEntry style=display:none>";
OPEN_BOX();

$frm[0]='';
$frm[1]='';
$frm[2]='';

$frm[0].="<fieldset style=float:left>";
$frm[0].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
$frm[0].="<table border=0 cellpadding=1 cellspacing=1 class=sortable>";
$frm[0].="<tr class=rowheader>		
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['keterangan']."</td>
            <td align=center>*</td>
        </tr>";
$frm[0].="<tr class=rowcontent>
			
			<td><input type=text  id=kodeBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
                            <img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td>
			<td><input type=text  id=namaBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
			<td><input type=text  id=satuanBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=jumlahBarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
                        <td><input type=text  id=keteranganBarang onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td>
                            <img src=images/save.png class=resicon  title='Save Material' onclick=saveBarang()>
			</td>
     	</tr>";
$frm[0].="</table></fieldset>";
$frm[0].="<fieldset style=float:left style='display:block;'>";
$frm[0].="<legend><b>".$_SESSION['lang']['list']."</b></legend>"; 
$frm[0].="<div id=containListBarang></div></fieldset>";	






$frm[1].="<fieldset style=float:left>";
$frm[1].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
$frm[1].="<table border=0 cellpadding=1 cellspacing=1>";
$frm[1].="
        <tr>
            <td>".$_SESSION['lang']['namakaryawan']."</td>
            <td>:</td>		
            <td><select id=karyawan style=\"width:150px;\">'".$optKaryawan."'</select></td>
	</tr>
        <tr>
            <td><button id=save class=mybutton onclick=saveKaryawan()>Simpan</button></td>
        </tr>";

$frm[1].="</table></fieldset>";
$frm[1].="<fieldset style=float:left style='display:block;'>";
$frm[1].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
$frm[1].="<div id=containListKaryawan></div></fieldset>";




$hfrm[0]=$_SESSION['lang']['daftarbarang'];
$hfrm[1]=$_SESSION['lang']['karyawan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,250,1150);		

//echo "<script>loadDetailBarang()</script>";

CLOSE_BOX();
echo "</div>";
echo close_body();
?>
    
