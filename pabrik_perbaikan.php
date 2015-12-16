<?php
//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".$_SESSION['lang']['pemeliharaanMesin']."</b>");
//print_r($_SESSION['temp']);
?>


<script language=javascript1.2 src='js/pabrik_perbaikan.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>



<!--deklarasi untuk option-->
<?php


#
$optTuntas="<option value='Lanjut'>Lanjut</option>";
$optTuntas.="<option value='Selesai'>Selesai</option>";
$optTuntas.="<option value='Tunda'>Tunda</option>";

##untuk pilihan pabrik 	
$optPabrik='';
$iPabrik="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$nPabrik=mysql_query($iPabrik) or die(mysql_error($conn));
while($dPabrik=mysql_fetch_assoc($nPabrik))
{
    $optPabrik.="<option value=".$dPabrik['kodeorganisasi'].">".$dPabrik['namaorganisasi']."</option>";
}

$optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
        . " where induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('STATION','MAINTENANCE')";
$nStation=mysql_query($iStation) or die(mysql_error($conn));
while($dStation=mysql_fetch_assoc($nStation))
{
    $optStation.="<option value=".$dStation['kodeorganisasi'].">".$dStation['namaorganisasi']."</option>";
}

$optKaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iKar="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
                    and a.tipekaryawan not in ('0','7','8') and
			(b.namajabatan like '%mecha%' or b.namajabatan like '%process%' 
                        or b.namajabatan like '%maintenance%' or subbagian='".$_SESSION['empl']['lokasitugas']."10')
                        ";

$nKar=mysql_query($iKar) or die(mysql_error($conn));
while($dKar=mysql_fetch_assoc($nKar))
{
    $optKaryawan.="<option value=".$dKar['karyawanid'].">".$dKar['namakaryawan']." [".$dKar['nik']."]</option>";
}


#buat pilihan status pemohon
$optStPemohon="<option value='P'>Processing</option>";
$optStPemohon.="<option value='M'>Maintenance</option>";
$optStPemohon.="<option value='L'>Luar</option>";

#buat tipe perbaikan
//8. Type Perbaikan ( default value = Prev. Maintenance, Kalibrasi, Project, Pabrikasi )
$optPerbaikan="<option value='prev'>Preventive Maintenance</option>";
$optPerbaikan.="<option value='kalibrasi'>Kalibrasi</option>";
$optPerbaikan.="<option value='project'>Project</option>";
$optPerbaikan.="<option value='pabrikasi'>Pabrikasi</option>";
$optPerbaikan.="<option value='corrective'>Corrective Maintenance</option>";
$optPerbaikan.="<option value='service'>Service</option>";
#shift
$optShift='';
for($i=1;$i<=3;$i++)
{
    $optShift.="<option value='".$i."'>".$i."</option>";
}

#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}

$optKondisi="<option value='normal'>Normal</option>";
$optKondisi.="<option value='perbaikan'>Perlu Perbaikan</option>";
$optKondisi.="<option value='rusak'>Rusak</option>";

#default mesin
$optMesin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
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
			echo "No. Transaksi : <input type=text class=myinputtext id=schNodok />";
			echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=schTgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
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
            <td>".$_SESSION['lang']['nodok']."</td>
            <td>:</td>		
            <td><input type=text id=nodok size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>
	
	<tr>
            <td>".$_SESSION['lang']['tanggal']." Order</td>
            <td>:</td>
            <td><input type=text onchange=getNodok() class=myinputtext id=tglOrder onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
            <select id=jmOrder>".$jm."</select>:<select id=mnOrder>".$mnt."</select></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['namapemohon']."</td>
            <td>:</td>		
            <td><input type=text id=namaPemohon size=50  class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\"></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['statuspemohon']."</td>
            <td>:</td>		
            <td><select id=statusPemohon  style=\"width:150px;\">".$optStPemohon."</select></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>		
            <td><select id=pabrik disabled style=\"width:150px;\">'".$optPabrik."'</select></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['station']."</td>
            <td>:</td>		
            <td><select id=station onchange=getMesin() style=\"width:150px;\">'".$optStation."'</select></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['mesin']."</td>
            <td>:</td>		
            <td><select id=mesin style=\"width:150px;\">'".$optMesin."'</select></td>
	</tr>
        
        <tr>
            <td>".$_SESSION['lang']['shift']."</td>
            <td>:</td>		
            <td><select id=shift  style=\"width:150px;\">".$optShift."</select></td>
	</tr>
        
        <tr>
            <td>".$_SESSION['lang']['tipeperbaikan']."</td>
            <td>:</td>		
            <td><select id=tipePerbaikan  style=\"width:150px;\">".$optPerbaikan."</select></td>
	</tr>
        
        <tr>
            <td>".$_SESSION['lang']['uraiankerusakan']."</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=uraianKerusakan style=\"width:150px;\" rows=5></textarea></td>
        </tr>
        
        <tr>
            <td>Komentar Maintenance</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=komMain style=\"width:150px;\" rows=5></textarea></td>
        </tr>
       
        <tr>
            <td>Jam Mulai</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tglMulai name=tglMulai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
                <select id=jmMulai>".$jm."</select>:<select id=mnMulai>".$mnt."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['jamselesai']."</td>
            <td>:</td>
            <td><input onkeypress=\"return tanpa_kutip(event)\" type=text class=myinputtext id=tglSelesai name=tglSelesai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
                <select id=jmSelesai>".$jm."</select>:<select id=mnSelesai>".$mnt."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['jumlahjamperbaikan']."</td>
            <td>:</td>		
            <td><input type=text id=jumlahJamPerbaikan size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"></td>
	</tr>
        <tr>
            <td>".$_SESSION['lang']['statusketuntasan']."</td>
            <td>:</td>	
            <td><select id=statusKetuntasan style=\"width:150px;\">'".$optTuntas."'</select></td>
           </tr>
        

        
        <tr>
            <td>".$_SESSION['lang']['hasilkerjad']."</td>
            <td>:</td>
            <td><textarea id=hasilKerja style=\"width:150px;\" onkeypress=\"return tanpa_kutip(event);\" rows=5></textarea></td>
        </tr>
            
         <tr>
            <td>Komentar Proses</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=komPros style=\"width:150px;\" rows=5></textarea></td>
        </tr>

	
	<tr>
		<td colspan=2></td>
		<td>
                    <button id=savehead class=mybutton onclick=saveHeader()>".$_SESSION['lang']['save']."</button>
                    <button id=batal class=mybutton onclick=cancelHead()>".$_SESSION['lang']['cancel']."</button>
                    <button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
                </td>
                <input type=hidden id=method value='insert'>
		
	</tr>
	
</table>
</fieldset>";
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
$frm[0].="<table border=0 cellpadding=1 cellspacing=1>";
$frm[0].="<tr class=rowheader><thead>		
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['keterangan']."</td>
            <td align=center>*</td>
        </tr></thead>";
$frm[0].="<tr class=rowcontent>
			
			<td><input type=text  id=kodeBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
                            <img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td>
			<td><input type=text  id=namaBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
			<td><input type=text  id=satuanBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=jumlahBarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
                        <td><input type=text  id=keteranganBarang onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
			<input type=text hidden id=hargabarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
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
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>:</td>		
            <td><input type=text id=nomor size=2 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"></td>
	</tr>
        <tr>
            <td>Item Check</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=rincian style=\"width:500px;\" rows=5></textarea></td>
        </tr>
     
        <tr>
            <td>".$_SESSION['lang']['kondisi']."</td>
            <td>:</td>		
            <td><select  id=kondisi style=\"width:150px;\">'".$optKondisi."'</select></td>
	</tr>
        
        <tr>
            <td><button id=savehead class=mybutton onclick=savePekerjaan()>Simpan</button></td>
        </tr>";


$frm[1].="</table></fieldset>";
$frm[1].="<fieldset style=float:left style='display:block;'>";
$frm[1].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
$frm[1].="<div id=containListPekerjaan></div></fieldset>";	




$frm[2].="<fieldset style=float:left>";
$frm[2].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
$frm[2].="<table border=0 cellpadding=1 cellspacing=1>";
$frm[2].="
        <tr>
            <td>".$_SESSION['lang']['namakaryawan']."</td>
            <td>:</td>		
            <td><select id=karyawan style=\"width:150px;\">'".$optKaryawan."'</select></td>
	</tr>
        <tr>
            <td><button id=save class=mybutton onclick=saveKaryawan()>Simpan</button></td>
        </tr>";

$frm[2].="</table></fieldset>";
$frm[2].="<fieldset style=float:left style='display:block;'>";
$frm[2].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
$frm[2].="<div id=containListKaryawan></div></fieldset>";




$hfrm[0]=$_SESSION['lang']['daftarbarang'];
$hfrm[1]=$_SESSION['lang']['listPekerjaan'];
$hfrm[2]=$_SESSION['lang']['karyawan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,250,1150);		

//echo "<script>loadDetailBarang()</script>";

CLOSE_BOX();
echo close_body();			
?>
    
