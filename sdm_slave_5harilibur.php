<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$tanggal=checkPostGet('tanggal','');
$keterangan=checkPostGet('keterangan','');
$catatan=checkPostGet('catatan','');

switch($method)
{
case'insert':
    if($kebun=="GLOBAL"){
		$wherRow = "tanggal='".tanggalsystem($tanggal)."'";
	}else{
		$wherRow = "tanggal='".tanggalsystem($tanggal)."' and (kebun='".$kebun."' or kebun='GLOBAL')";
	}
	
	$str="select * from ".$dbname.".sdm_5harilibur where ".$wherRow."";
	$res=mysql_query($str) or die(mysql_error($conn));
	$numRows=mysql_num_rows($res);
	
	if($numRows > 0){
		if($kebun=="GLOBAL"){
			echo"Gagal. Tidak bisa input hari libur secara global.";
		}else{
			echo"Gagal. Kebun dan Tanggal sudah pernah diinput";
		}
	}else{
		$sIns="insert into ".$dbname.".sdm_5harilibur (`tanggal`,`keterangan`,`catatan`,`updateby`,`kebun`) 
        values ('".tanggalsystem($tanggal)."','".$keterangan."','".$catatan."','".$_SESSION['standard']['userid']."','".$kebun."')";
		if(!mysql_query($sIns))
		{
			echo"Gagal".mysql_error($conn);
		}
	}
	break;

    case'loadData':
    $str="select * from ".$dbname.".sdm_5harilibur  order by tanggal desc";
    $res=mysql_query($str) or die(mysql_error($conn));
    while($bar=mysql_fetch_assoc($res))
    {
        $no+=1;
        echo"<tr class=rowcontent>
        <td>".$no."</td>
		<td>".$bar['kebun']."</td>
        <td align=right>".tanggalnormal($bar['tanggal'])."</td>
        <td>".$bar['keterangan']."</td>
        <td>".$bar['catatan']."</td>
        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletehk('".$bar['tanggal']."');\"></td>
        </tr>";	
    }     
    break;
    case'delete':
        $sIns="delete from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggal."'";
        //exit("Error".$sIns);
        if(!mysql_query($sIns))
        {
                echo"Gagal".mysql_error($conn);
        }
    break;
        default:
        break;
}
?>