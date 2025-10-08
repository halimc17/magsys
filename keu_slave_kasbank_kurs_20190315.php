<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$matauang=isset($_POST['matauang'])?$_POST['matauang']:'';
$proses=isset($_POST['proses'])?$_POST['proses']:'';
$tanggal=isset($_POST['tanggal'])?tanggalsystem($_POST['tanggal']):'';
$tipetransaksi=isset($_POST['tipetransaksi'])?$_POST['tipetransaksi']:'';

switch($proses)
{
	case'getKurs':
		if($matauang!='IDR')
        {
           $iKurs="select kurs from ".$dbname.".setup_matauangrate where kode='".$matauang."' and daritanggal='".$tanggal."' ";
           $nKurs=mysql_query($iKurs) or die (mysql_error($conn));
           $dKurs=mysql_fetch_assoc($nKurs);
           $kurs=$dKurs['kurs'];
        }
        else 
        {
           $kurs=1;
        }
        echo $kurs;
		break;
	case'getAkun':
		if($tipetransaksi=='K'){
			$wheredx = " (noakun like '211%' or noakun like '212%' or noakun like '213%') and length(noakun)=7";
		}else{
			//$wheredx = " (noakun like '11301%' or noakun like '114%' or noakun like '122%') and length(noakun)=7";
			$wheredx = " (noakun like '211%' or noakun like '212%' or noakun like '213%' or noakun like '11301%' or noakun like '114%' or noakun like '122%') and length(noakun)=7";
		}
		
		$iReg="select noakun,namaakun from ".$dbname.".keu_5akun where ".$wheredx."";
        $nReg=mysql_query($iReg) or die (mysql_error($conn));
		$optNoakunHutang="<option value=''></option>";
        while($dReg=mysql_fetch_assoc($nReg))
        {
			$optNoakunHutang.="<option value='".$dReg['noakun']."'>".$dReg['noakun']." - ".$dReg['namaakun']."</option>";
        }
		
		//$optNoakunHutang = makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredx,'2');
		//$optNoakunHutang['']=''; ksort($optNoakunHutang);
        echo $optNoakunHutang;
		break;
	
	default:
		break;
}
?>