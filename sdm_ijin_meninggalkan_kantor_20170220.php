<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".$_SESSION['lang']['izinkntor']."/".$_SESSION['lang']['cuti']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script>
 jdl_ats_0='<?php echo $_SESSION['lang']['find']?>';
// alert(jdl_ats_0);
 jdl_ats_1='<?php echo $_SESSION['lang']['findBrg']?>';
 content_0='<fieldset><legend><?php echo $_SESSION['lang']['findnoBrg']?></legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div>';

nmSaveHeader='';
nmCancelHeader='';
nmDetialDone='<?php echo $_SESSION['lang']['done']?>';
nmDetailCancel='<?php echo $_SESSION['lang']['cancel']?>';

</script>
<script type="application/javascript" src="js/sdm_ijin_meninggalkan_kantor.js"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="headher">
<?php
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
$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$qOrg=mysql_query($sOrg) or die(mysql_error());
while($rOrg=mysql_fetch_assoc($qOrg))
{
        $optKary.="<option value=".$rOrg['karyawanid'].">".$rOrg['namakaryawan']."</option>";
}

//Pengambilan karyawan HRD
$optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sHRD="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and bagian in ('HRD','HHRD','HRA') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$qHRD=mysql_query($sHRD) or die(mysql_error());
while($rHRD=mysql_fetch_assoc($qHRD))
{
        $optHRD.="<option value=".$rHRD['karyawanid'].">".$rHRD['namakaryawan']."</option>";
}

$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $arragama=getEnum($dbname,'sdm_ijin','jenisijin');
                foreach($arragama as $kei=>$fal)
                {
                    if($_SESSION['language']=='ID'){
                        $optJenis.="<option value='".$kei."'>".$fal."</option>";
                    }else{
                        switch($fal){
                            case 'TERLAMBAT':
                                $fal='Late for work';
                                break;
                            case 'KELUAR':
                                $fal='Out of Office';
                                break;         
                            case 'PULANGAWAL':
                                $fal='Home early';
                                break;     
                            case 'IJINLAIN':
                                $fal='Other purposes';
                                break;   
                            case 'CUTI':
                                $fal='Leave';
                                break;       
                            case 'MELAHIRKAN':
                                $fal='Maternity';
                                break;           
                            default:
                                $fal='Wedding, Circumcision or Graduation';
                                break;                              
                        }
                        $optJenis.="<option value='".$kei."'>".$fal."</option>";       
                    }
                }  

//ambil cuti ybs
// Ambil tanggal masuk ybs

$stc="select right(tanggalmasuk,5) as tanggalmasuk from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid'];
$rec=mysql_query($stc);
$tglmasup='';
$hrini=date('md');#default
while($bac=mysql_fetch_object($rec))
{
    $tglmasup=str_replace("-","",$bac->tanggalmasuk);#replace with data karyawan
}
if($tglmasup>$hrini){
    $tahunplafon=(date('Y')-1);
}
else
{
    $tahunplafon=date('Y');
}   
#penguncian agar cuti yang sudah hangus tidak dapat diambil
$optPeriodec="<option value=".$tahunplafon.">".$tahunplafon."</option>";
$optPeriodec.="<option value=".($tahunplafon+1).">".($tahunplafon+1)."</option>"; 

$strf="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." 
       and periodecuti=".$tahunplafon;
$res=mysql_query($strf);

$sisa='';
while($barf=mysql_fetch_object($res))
{
    $sisa=$barf->sisa;
}
if($sisa=='')
    $sisa=0;

?>
<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">

<tr>
<td><?php echo $_SESSION['lang']['tanggal']?></td>
<td>:</td>
<td><input type='text' class='myinputtext' id='tglIzin' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style="width:150px;" /></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['jenisijin']?></td>
<td>:</td>
<td><select id="jnsIjin" name="jnsIjin" style="width:150px"><?php echo $optJenis;?></select></td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['pengabdian']." ".$_SESSION['lang']['tahun'];?></td>
<td>:</td>
<td><select id="periodec"  style="width:150px" onchange="loadSisaCuti(this.options[this.selectedIndex].value,<?php echo $_SESSION['standard']['userid']?>)"><?php echo $optPeriodec;?></select></td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['dari']."  ".$_SESSION['lang']['tanggal']."&".$_SESSION['lang']['jam']?></td>
<td>:</td>
<td><input type='text' class='myinputtext' id='tglAwal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style="width:150px;" /><select id="jam1"><?php echo $jm;?></select>:<select id="mnt1"><?php echo $mnt;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['tanggal']."&".$_SESSION['lang']['jam']?></td>
<td>:</td>
<td><input type='text' class='myinputtext' id='tglEnd' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style="width:150px;" /><select id="jam2"><?php echo $jm;?></select>:<select id="mnt2"><?php echo $mnt;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil'];?></td>
<td>:</td>
<td>
<input type="text" class="myinputtext" id="jumlahhk" name="keperluan" onkeypress="return angka_doang(event);" maxlength="5" value="0"/><?php echo $_SESSION['lang']['hari']; ?> -
(<?php echo $_SESSION['lang']['sisa']; ?>:<span id="sis"><?php echo $sisa." ".$_SESSION['lang']['hari']; ?></span>)</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['keperluan']?></td>
<td>:</td>
<td>
<input type="text" class="myinputtext" id="keperluan" name="keperluan" onkeypress="return tanpa_kutip(event);" maxlength="30" style="width:150px;" /></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['keterangan']?></td>
<td>:</td>
<td>
<textarea id='ket'  onkeypress="return tanpa_kutip(event);"></textarea>
</td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['atasan']?></td>
<td>:</td>
<td>
    <select id="atasan" style="width:150px"><?php echo $optKary; ?></select>
</td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['hrd']?></td>
<td>:</td>
<td>
    <select id="hrd" style="width:150px"><?php echo $optHRD; ?></select>
</td>
</tr>

<tr>
<td colspan="3" id="tmblHeader">
    <button class=mybutton id=dtlForm onclick=saveForm()><?php echo $_SESSION['lang']['save']?></button>
    <button class=mybutton id=cancelForm onclick=cancelForm()><?php echo $_SESSION['lang']['cancel']?></button>
</td>
</tr>
</table><input type="hidden" id="atsSblm" name="atsSblm" />
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<div id="list_ganti">
<?php OPEN_BOX()?>
    <div id="action_list">

</div>
<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['list']?></legend>

<table cellspacing="1" border="0" class="sortable">
<thead>
<tr class="rowheader">
<td>No.</td>
<td><?php echo $_SESSION['lang']['tanggal']?></td>
<td><?php echo $_SESSION['lang']['keperluan']?></td>
<td><?php echo $_SESSION['lang']['jenisijin']?></td>
<td><?php echo $_SESSION['lang']['persetujuan']?></td>
<td><?php echo $_SESSION['lang']['approval_status']?></td>
<td><?php echo $_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']?></td>
<td><?php echo $_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']?></td>
<td>Action</td>
</tr>
</thead>
<tbody id="contain">
<?php
        $arrNmkary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan=array(0=>$_SESSION['lang']['diajukan'],1=>$_SESSION['lang']['disetujui'],2=>$_SESSION['lang']['ditolak']);

$userOnline=$_SESSION['standard']['userid'];
        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;

        $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'  order by `tanggal` desc";// echo $ql2;
        $query2=mysql_query($ql2) or die(mysql_error());
        while($jsl=mysql_fetch_object($query2)){
        $jlhbrs= $jsl->jmlhrow;
        }


$slvhc="select * from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'   order by `tanggal` desc limit ".$offset.",".$limit." ";
$qlvhc=mysql_query($slvhc) or die(mysql_error());
$user_online=$_SESSION['standard']['userid'];
while($rlvhc=mysql_fetch_assoc($qlvhc))
{
$no+=1;

?>
<tr class="rowcontent">
<td><?php echo $no?></td>
<td><?php echo tanggalnormal($rlvhc['tanggal'])?></td>
<td><?php echo $rlvhc['keperluan']?></td>
<td><?php echo $rlvhc['jenisijin']?></td>
<td><?php echo $arrNmkary[$rlvhc['persetujuan1']]?></td>
<td><?php echo $arrKeputusan[$rlvhc['stpersetujuan1']]?></td>
<td><?php echo tanggalnormald($rlvhc['darijam']); ?></td>
<td><?php echo tanggalnormald($rlvhc['sampaijam']);?></td>

<?php 
if($rlvhc['stpersetujuan1']==0 and $rlvhc['stpersetujuanrd']==0)
{
echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['keperluan']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['jenisijin']."','".$rlvhc['persetujuan1']."','".$rlvhc['stpersetujuan1']."','".$rlvhc['darijam']."','".$rlvhc['sampaijam']."','".$rlvhc['hrd']."','".$rlvhc['jumlahhari']."','".$rlvhc['periodecuti']."');\">
    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".tanggalnormal($rlvhc['tanggal'])."');\" ></td>";
    //<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_ijin','".$rlvhc['tanggal'].",".$rlvhc['karyawanid']."','','sdm_slave_ijin_meninggalkan_kantor',event)\">";
}
else
{
    echo "<td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>";
   // echo"<td> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_ijin','".$rlvhc['tanggal'].",".$rlvhc['karyawanid']."','','sdm_slave_ijin_meninggalkan_kantor',event)\"></td>";
}
?>
</tr>

<?php 
}
echo"
        <tr class=rowheader><td colspan=9 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr>";
?>

</tbody>
</table>
</fieldset>
<?php CLOSE_BOX()?>
</div>

<?php 
echo close_body();
?>