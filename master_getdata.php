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

    case'getJam':
		$num	   = checkPostGet('num','');
		$tglMulai  = checkPostGet('tglMulai','');
		$jmMulai   = checkPostGet('jmMulai','');
		$mnMulai   = checkPostGet('mnMulai','');
		$tglSelesai= checkPostGet('tglSelesai','');
		$jmSelesai = checkPostGet('jmSelesai','');
		$mnSelesai = checkPostGet('mnSelesai','');
		if($tglMulai==''){
			exit('Warning: Tanggal Mulai tidak boleh kosong...!');
		}
		if($tglSelesai=='' and $num==2){
			exit('Warning: Tanggal Selesai tidak boleh kosong...!');
		}
		$waktu_awal =strtotime($tglMulai.' '.$jmMulai.':'.$mnMulai.':00');
		$waktu_akhir=strtotime($tglSelesai.' '.$jmSelesai.':'.$mnSelesai.':00');
		$jamperbaikan=round(($waktu_akhir-$waktu_awal)/3600,2);
		if($tglMulai!='' and $tglSelesai!=''){
			if($waktu_awal>$waktu_akhir){
				echo '';
				exit('Warning: Tanggal Selesai lebih kecil...!'.$jamperbaikan);
			}else{
				echo $jamperbaikan;
			}
		}
    break;

    case'getPrev':
		$station= checkPostGet('station','');
		$mesin	= checkPostGet('mesin','');
		$nomor	= checkPostGet('nomor','');
		$tipePerbaikan	= checkPostGet('tipePerbaikan','');
		$rincian='';
		if($tipePerbaikan=='prev'){
		//$sPrev="select rincian from ".$dbname.".pabrik_5masterpreventive where kodestasiun='".$station."' and kodemesin='".$mesin."' and nomor='".$nomor."'";     
			$sPrev="select rincian from ".$dbname.".pabrik_5masterpreventive where kodestasiun='".$station."' and nomor='".$nomor."'";     
			//exit('Warning: '.$sPrev);
			$qPrev=mysql_query($sPrev) or die(mysql_error($conn));
			while($dPrev=mysql_fetch_assoc($qPrev)){
				$rincian=$dPrev['rincian'];
			}
		}  
        echo $rincian;
    break;

	default:
	break;
}
?>
