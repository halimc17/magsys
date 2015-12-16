<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kodept=checkPostGet('kodept','');
$masakerja=checkPostGet('masakerja','');
$jenis=checkPostGet('jenis','');
$banyaknya=checkPostGet('banyaknya','');
$old_kodept=checkPostGet('old_kodept','');
$old_masakerja=checkPostGet('old_masakerja','');
$old_jenis=checkPostGet('old_jenis','');
$old_banyaknya=checkPostGet('old_banyaknya','');
$carikodept=checkPostGet('carikodept','');
$carijenis=checkPostGet('carijenis','');

switch($method)
{
    case'insert':
    if($kodept==''){
        exit("error: ".$_SESSION['lang']['kodept']." tidak boleh kosong");
    }
    if($masakerja==''){
        exit("error: ".$_SESSION['lang']['masakerja']." tidak boleh kosong");
    }
    if($jenis==''){
        exit("error: Pilih ".$_SESSION['lang']['jenis']."");
    }
    if($banyaknya==''){
        exit("error: ".$_SESSION['lang']['jmlhBrg']." tidak boleh kosong");
    }
    
    $scek="select * from ".$dbname.".sdm_5pesangon 
           where kodept='".$kodept."' and masakerja='".$masakerja."' and jenis = '".$jenis."' and banyaknya = '".$banyaknya."'";
    $qcek=mysql_query($scek) or die(mysql_error($conn));
    $rcek=mysql_num_rows($qcek);
    if($rcek!=0){
       exit("error: Data sudah pernah diinput.");
    }
    
    $sIns="insert into ".$dbname.".sdm_5pesangon (kodept,masakerja,jenis,banyaknya) 
           values ('".$kodept."','".$masakerja."','".$jenis."','".$banyaknya."')";
    if(!mysql_query($sIns)){
        echo"Gagal".mysql_error($conn);
    }
    break;
    
    case'loadData':
    $whr=" and kodept like '".$carikodept."%' and jenis like '".$carijenis."%'";
    if($carikodept==''){
        $str="select * from ".$dbname.".sdm_5pesangon where jenis like '".$carijenis."%' 
              order by jenis, masakerja asc";        
    }
    else if($carijenis==''){
        $str="select * from ".$dbname.".sdm_5pesangon where kodept like '".$carikodept."%' 
              order by jenis, masakerja asc";
    }
    else{
        $str="select * from ".$dbname.".sdm_5pesangon where 1=1 ".$whr." order by jenis, masakerja asc";
    }
    $no=0;
    $res=mysql_query($str)or die(mysql_error());
    $oow=mysql_num_rows($res);
    if($oow==0){
        echo"<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['dataempty']."</td></tr>";
    }
    else{
        while($bar=mysql_fetch_assoc($res))
        {
            $no+=1;	
            echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar['kodept']."</td>
            <td>".$bar['masakerja']."</td>
            <td>".$bar['jenis']."</td>
            <td>".$bar['banyaknya']."</td>
            <td>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodept']."','".$bar['masakerja']."','".$bar['jenis']."','".$bar['banyaknya']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['kodept']."','".$bar['masakerja']."','".$bar['jenis']."','".$bar['banyaknya']."');\">             
            </td>
            </tr>";	
        }  
    }
    break;

    case'update':
    if($kodept==''){
        exit("error: ".$_SESSION['lang']['kodept']." tidak boleh kosong");
    }
    if($masakerja==''){
        exit("error: ".$_SESSION['lang']['masakerja']." tidak boleh kosong");
    }
    if($jenis==''){
        exit("error: Pilih ".$_SESSION['lang']['jenis']."");
    }
    if($banyaknya==''){
        exit("error: ".$_SESSION['lang']['jmlhBrg']." tidak boleh kosong");
    }

    $scek="select * from ".$dbname.".sdm_5pesangon where kodept='".$kodept."' and masakerja='".$masakerja."'
           and jenis='".$jenis."' and banyaknya='".$banyaknya."'";
    $qcek=mysql_query($scek) or die(mysql_error($conn));
    $rcek=mysql_num_rows($qcek);
    if($rcek!=0){
       exit("error: Data sudah ada.");
    }
    else{
        $sUpd="update ".$dbname.".sdm_5pesangon set kodept='".$kodept."',masakerja='".$masakerja."',
               jenis='".$jenis."',banyaknya='".$banyaknya."'
               where kodept='".$old_kodept."' and masakerja='".$old_masakerja."' 
               and jenis = '".$old_jenis."' and banyaknya='".$old_banyaknya."' ";
        if(!mysql_query($sUpd))
        {
            echo"Gagal".mysql_error($conn);
        }
    }
    break;

    case 'deletedata':
    $sDel="delete from ".$dbname.".sdm_5pesangon 
           where kodept='".$kodept."' and masakerja='".$masakerja."' and jenis='".$jenis."' 
           and banyaknya = '".$banyaknya."'";
    if(mysql_query($sDel))
        echo"";
    else
        echo "DB Error : ".mysql_error($conn);                        
    break;

    default:
    break;
}
?>