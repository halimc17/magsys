<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanPekerjaan']).'</b>'); //1 O
?>
<!--<script type="text/javascript" src="js/log_2keluarmasukbrg.js" /></script>
-->
<script type="text/javascript" src="js/vhc_laporanKerjaKendaraan.js" /></script>
<div id="action_list">
<?php

$optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sOrg="select distinct kodetraksi from ".$dbname.".vhc_5master order by kodetraksi asc";
$qOrg=fetchData($sOrg);
foreach($qOrg as $brsOrg)
{
    $optPt.="<option value=".$brsOrg['kodetraksi'].">".$optOrg[$brsOrg['kodetraksi']]."</option>";
}

/*$sOrg="select distinct substr(alokasibiaya,1,4) as lokasi from ".$dbname.".vhc_rundt 
    where alokasibiaya not like 'AK-%' order by substr(alokasibiaya,1,4) asc";
$qOrg=fetchData($sOrg);
$optLokasi="";
foreach($qOrg as $brsOrg)
{
    if(trim($brsOrg['lokasi'])!='')$optLokasi.="<option value=".$brsOrg['lokasi'].">".$optOrg[$brsOrg['lokasi']]."</option>";
}*/




$iAlokasi="select distinct(alokasibiaya) as alokasibiaya from ".$dbname.".vhc_rundt where alokasibiaya not like 'AK-%' ";
$nAlokasi=  mysql_query($iAlokasi) or die (mysql_error($conn));
while($dAlokasi=  mysql_fetch_assoc($nAlokasi))
{
    $optLokasi.="<option value='".$dAlokasi['alokasibiaya']."'>".$dAlokasi['alokasibiaya']."</option>";    
}

$optAkun="<option value=''>".$_SESSION['lang']['all']."</option>";
$nmAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$sOrg1="select noakun,namakegiatan from ".$dbname.".vhc_kegiatan order by noakun";
$qOrg1=fetchData($sOrg1);
foreach($qOrg1 as $brsOrg1)
{
    $optAkun.="<option value=".$brsOrg1['noakun'].">".$brsOrg1['noakun']." - ".$brsOrg1['namakegiatan']."</option>";
}




echo"<fieldset><table><legend>".$_SESSION['lang']['pilihdata']."</legend> ";
    echo"
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=company_id name=company_id onChange=get_jnsVhc() style=width:200px>".$optPt."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['jenisvch']."</td>
            <td>:</td>
            <td><select id=jnsVhc name=jnsVhc onchange=\"getKdVhc()\" style=width:100px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['kodevhc']."</td>
            <td>:</td>
            <td><select id=kdVhc name=kdVhc style=width:100px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['alokasi']."</td>
            <td>:</td>
            <td><select id=alokasi name=alokasi style=width:100px><option  value=''>".$_SESSION['lang']['all']."</option>".$optLokasi."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:100px;\" /> S/D
            <input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:100px;\" /></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['noakun']." & ".$_SESSION['lang']['pekerjaan']." </td>
            <td>:</td>
            <td><select id=akun name=akun style=width:100px>".$optAkun."</select></td>
        </tr>
        <tr>
		<td colspan=2></td>
        <td><button class=mybutton onclick=save_pil()>".$_SESSION['lang']['find']."</button>
                             <button class=mybutton onclick=ganti_pil()>".$_SESSION['lang']['ganti']."</button></td>";
         echo"</tr>";
                        
echo"
     
         </table></fieldset> "; 
?>
</div>
<?php 
CLOSE_BOX();
OPEN_BOX();

?>
<div id="cari_barang" name="cari_barang">
   <div id="hasil_cari" name="hasil_cari">
    <fieldset>
    <legend><?php echo $_SESSION['lang']['result']?></legend>
     <img onclick=dataKeExcel(event,'vhc_slave_laporanKerjaKendaraan.php') src=images/excel.jpg class=resicon title='MS.Excel'> 

<div id="contain">


     </div>
    </fieldset>
    </div>
</div>
<?php
CLOSE_BOX();
?>
<?php
echo close_body();
?>