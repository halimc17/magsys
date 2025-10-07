<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses','');
$pabrik = checkPostGet('pabrik','');
$station = checkPostGet('station','');

switch($proses){
	case'getStation':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($pabrik!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pabrik."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}  
		}
		echo $optStation;
    break;

    case'getMesin':
        $optMesin.="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($station!=''){
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$station."'";
			$nMesin=mysql_query($iMesin) or die (mysql_error($conn));
			while($dMesin=mysql_fetch_assoc($nMesin)){
				if($mesin==$dMesin['kodeorganisasi'])
					{$select="selected=selected";}
				else
					{$select="";}
				$optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">[".$dMesin['kodeorganisasi']."] ".$dMesin['namaorganisasi']."</option>";
			}
			$optMesin.="<option value=''>Others</option>";
		}
        echo $optMesin;
    break;

	default:
	break;
}
?>
