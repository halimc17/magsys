<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = $_GET['proses'];
$kodevhc=checkPostGet('kodevhc','');
$kmhmakhir=checkPostGet('kmhmakhir','');

switch($proses) {
	case'getKm':
		$qKm = selectQuery($dbname,'vhc_kmhm_track','kmhmakhir',"kodevhc='".$kodevhc."'");
		$resKm = fetchData($qKm);
		if(empty($resKm))
			echo 0;
		else
			echo $resKm[0]['kmhmakhir'];
        break;
	case 'reset':
		$dataIns = array($kodevhc,$kmhmakhir);
		$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
		if(!mysql_query($qIns)) {
			$dataUpd = array('kmhmakhir'=>$kmhmakhir);
			$qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,
								"kodevhc='".$kodevhc."'");
			if(!mysql_query($qUpd)) {
				exit("Update KM/HM Error: ".mysql_error());
			}
		}
		break;
}