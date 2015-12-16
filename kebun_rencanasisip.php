<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript1.2 src='js/kebun_rencanasisip.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['rencanasisip']);

$sKebun="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi 
    where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."' and tipe='KEBUN' order by kodeorganisasi asc";
$qKebun=mysql_query($sKebun) or die(mysql_error());
$optKebun='';
while($rKebun=mysql_fetch_assoc($qKebun))
{
    $kamusKebun[$rKebun['kodeorganisasi']]=$rKebun['namaorganisasi'];
    $optKebun.="<option value='".$rKebun['kodeorganisasi']."'>".$rKebun['namaorganisasi']."</option>";
}

$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sBlok="select kodeorg, statusblok, tahuntanam from ".$dbname.".setup_blok 
    where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and luasareaproduktif>0 order by kodeorg asc";
$qBlok=mysql_query($sBlok) or die(mysql_error());
while($rBlok=mysql_fetch_assoc($qBlok))
{
    $optBlok.="<option value='".$rBlok['kodeorg']."'>".$rBlok['kodeorg']." - ".$rBlok['statusblok']." - ".$rBlok['tahuntanam']."</option>";
}

$sAlsRncaSisip="select kodealasanrencanasisip, deskripsi from ".$dbname.".kebun_5alasanrencanasisip order by deskripsi asc";
$qAlsRncaSisip=mysql_query($sAlsRncaSisip) or die(mysql_error());
$optKeterangan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rAlsRncaSisip=mysql_fetch_assoc($qAlsRncaSisip))
{
	$optKeterangan.="<option value='".$rAlsRncaSisip['kodealasanrencanasisip']."'>".$rAlsRncaSisip['deskripsi']."</option>";
}

$tahun=date("Y");
$optPeriode="";
for ($i = 1; $i <= 12; $i++) {
    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
    $optPeriode.="<option value='".$tahun."-".$ii."'>".$tahun."-".$ii."</option>";
}

$optPeriode2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPeriode="select distinct periode from ".$dbname.".kebun_rencanasisip order by periode desc";
$qPeriode=mysql_query($sPeriode) or die(mysql_error());
while($rPeriode=mysql_fetch_assoc($qPeriode))
{
    //if($rPeriode['periode']==($tahun.'-01'))$pilih=' selected'; else $pilih='';
    $optPeriode2.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}

echo"<fieldset style='width:500px;'>
    <table>
    <tr>
        <td>".$_SESSION['lang']['kebun']."</td>
        <td><select id=kebun>".$optKebun."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['blok']."</td>
        <td><select id=blok onchange=gantiblok();>".$optBlok."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td><select id=periode>".$optPeriode."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['pokok']."</td>
        <td><input type=text class=myinputtextnumber id=pokok onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['sph']."</td>
        <td><input type=text class=myinputtextnumber id=sph onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['pokokmati']."</td>
        <td><input type=text class=myinputtextnumber id=pokokmati onkeypress=\"return angka_doang(event);\" size=10 maxlength=10></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['rencanasisip']."</td>
        <td><input type=text class=myinputtextnumber id=rencanasisip onkeypress=\"return angka_doang(event);\" size=10 maxlength=10></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['alasanrencanasisip']."</td>
        <td><select id=keterangan>".$optKeterangan."</select></td>
    </tr>
    </table>
    <input type=hidden id=method value='insert'>
    <input type=hidden id=matrixid value=''>    
    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
    </fieldset>";

echo open_theme($_SESSION['lang']['list']);
echo "<div id=container>";
echo "<table><tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td><select id=periode2 onchange=pilihperiode()>".$optPeriode2."</select></td>
    </tr></table>";

$where = "";
if(substr($_SESSION['empl']['lokasitugas'],2,2) != "HO") {
	$where = " WHERE t1.blok like '".$_SESSION['empl']['lokasitugas']."%' ";
}
$str1="select t1.*, t2.deskripsi from ".$dbname.".kebun_rencanasisip t1 
	left join ".$dbname.".kebun_5alasanrencanasisip t2
	on t1.keterangan=t2.kodealasanrencanasisip ".$where."
	order by t1.periode desc, t1.blok";
$res1=mysql_query($str1);
echo"<table class=sortable cellspacing=1 border=0 style='width:800px;'>
     <thead>
     <tr class=rowheader>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>".$_SESSION['lang']['blok']."</td>
        <td>".$_SESSION['lang']['pokok']."</td>
        <td>".$_SESSION['lang']['sph']."</td>
        <td>".$_SESSION['lang']['pokokmati']."</td>
        <td>".$_SESSION['lang']['rencanasisip']."</td>
        <td>".$_SESSION['lang']['alasanrencanasisip']."</td>
        <td width=100>".$_SESSION['lang']['action']."</td>
     </tr></thead>
     <tbody>";
$no=0;
while($bar1=mysql_fetch_object($res1))
{ 
    $no+=1;
    echo"<tr class=rowcontent>
        <td>".$bar1->periode."</td>
        <td>".$bar1->blok."</td>
        <td align=right>".number_format($bar1->pokok)."</td>
        <td align=right>".number_format($bar1->sph,2)."</td>
        <td align=right>".number_format($bar1->pokokmati)."</td>
        <td align=right>".number_format($bar1->rencanasisip)."</td>
        <td>".$bar1->deskripsi."</td>
        <td align=center>";
            if($bar1->posting=='0'){ // belum posting
                echo"<img src=images/application/application_edit.png class=resicon  caption='Edit' 
                onclick=\"fillField('".$bar1->periode."','".$bar1->blok."','".$bar1->pokok."','".$bar1->sph."','".$bar1->pokokmati."','".$bar1->rencanasisip."','".$bar1->keterangan."');\">
                <img src=images/application/application_delete.png class=resicon  caption='Edit' onclick=\"hapus('".$bar1->periode."','".$bar1->blok."');\">";
                echo"&nbsp;<img src=images/skyblue/posting.png class=resicon caption='Posting' onclick=\"posting('".$bar1->periode."','".$bar1->blok."',event);\">";                    
            }else{ // sudah postng
                echo"&nbsp;<img src=images/skyblue/posted.png>";                    
            }
        echo"</td>
    </tr>";
}	 
echo"</tbody>
    <tfoot>
    </tfoot>
    </table>";
echo "</div>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>