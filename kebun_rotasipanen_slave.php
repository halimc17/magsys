<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses		=checkPostGet('proses','');
$kodeunit	=checkPostGet('kodeunit','');
$kodedivisi	=checkPostGet('kodedivisi','');
$periode	=checkPostGet('periode','');
switch($proses){
	case 'getDivisi':
		$sDiv ="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1'
				and kodeorganisasi like '".$kodeunit."%' order by kodeorganisasi";
		$qDiv =mysql_query($sDiv) or die(mysql_error($conn));
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rDiv=mysql_fetch_assoc($qDiv)){
			$optDivisi.="<option value=".$rDiv['kodeorganisasi'].">".$rDiv['namaorganisasi']."</option>";
		}
		echo $optDivisi;
		exit;
}
$optNamaPT=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$optNamaBlok=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$per=explode("-",$periode);
$perod=count($per)>2? $per[2]."-".$per[1]."-".$per[0]: '';
$dtPeriod=count($per)>2? $per[2]."-".$per[1]: '';
$tmpPeriod = explode('-',$periode);
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$tglAwal=$periode."-01";
$lastday = date('t',strtotime($tglAwal));
$tglAkhir=$periodeData."-".$lastday;
$laluAwal=(substr($periode,5,2)=='01' ? (substr($periode,0,4)-1).'-12-01' : (substr($periode,0,4)-7).'-'.sprintf('%02d',substr($periode,5,2)-1).'-01');
$laluAkhir=date('Y-m-t',strtotime($laluAwal));
if($kodeunit!=''){
	$where="kodeorg like '".$kodeunit."%'";
}else{
	exit("Error: Estate is obligatory");
}
if($kodedivisi!=''){
	$where="kodeorg like '".$kodedivisi."%'";
	$kodeunit=$kodedivisi;
}
if($periode!=''){
	$where.=" and tanggal like '".$periode."%'";
}else{
	exit("Error: Date is obligatory");
}

$dtKodeBlok=array();
//Setup Blok
$sBlok="select a.kodeorg,a.tahuntanam,a.luasareaproduktif,a.jumlahpokok from ".$dbname.".setup_blok a
		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.statusblok='TM' and a.luasareaproduktif>0 and a.kodeorg like '".$kodeunit."%' and b.detail='1' order by a.kodeorg";
//exit("Warning: ".$sBlok);
$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
while($rBlok=mysql_fetch_assoc($qBlok)){
	$dtKodeBlok[$rBlok['kodeorg']]=$rBlok['kodeorg'];
	$dtTahunTnm[$rBlok['kodeorg']]=$rBlok['tahuntanam'];
	$dtluasBlok[$rBlok['kodeorg']]=$rBlok['luasareaproduktif'];
	$dtJmlPokok[$rBlok['kodeorg']]=$rBlok['jumlahpokok'];
	$dtSPH[$rBlok['kodeorg']]=($rBlok['luasareaproduktif']==0 ? 0 : $rBlok['jumlahpokok']/$rBlok['luasareaproduktif']);
}

//panen
$sPanen="select distinct kodeorg,tanggal from ".$dbname.".kebun_prestasi_vw where ".$where." order by kodeorg,tanggal";
//exit("Warning: ".$sPanen);
$qPanen=mysql_query($sPanen) or die(mysql_error($conn));
while($rPanen=  mysql_fetch_assoc($qPanen)){
	$dtKodeBlok[$rPanen['kodeorg']]=$rPanen['kodeorg'];
	$dtDateKini[$rPanen['kodeorg'].$rPanen['tanggal']]=$rPanen['tanggal'];
}
//Borongan
$sBorong="select distinct kodeblok as kodeorg,tanggal from ".$dbname.".log_baspk where kodeblok like '".$kodeunit."%' and tanggal like '".$periode."%' 
and left(kodekegiatan,7) in (SELECT noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='PNN01')
order by kodeblok,tanggal";
//exit("Warning: ".$sBorong);
$qBorong=mysql_query($sBorong) or die(mysql_error($conn));
while($rBorong=  mysql_fetch_assoc($qBorong)){
	$dtKodeBlok[$rBorong['kodeorg']]=$rBorong['kodeorg'];
	$dtDateKini[$rBorong['kodeorg'].$rBorong['tanggal']]=$rBorong['tanggal'];
}

$sPanen2="select kodeorg,sum(luaspanen) as hapanen from ".$dbname.".kebun_prestasi_vw 
			where kodeorg like '".$kodeunit."%' and tanggal like '".$periode."%'  
			group by kodeorg
			order by kodeorg";
//exit("Warning: ".$sPanen2);
$qPanen2=mysql_query($sPanen2) or die(mysql_error($conn));
while($rPanen2=mysql_fetch_assoc($qPanen2)){
	$dtKodeBlok[$rPanen2['kodeorg']]=$rPanen2['kodeorg'];
	$dt_hapanen[$rPanen2['kodeorg']]=$rPanen2['hapanen'];
}
$sBorong2="select kodeblok as kodeorg,sum(hasilkerjarealisasi) as hapanen from ".$dbname.".log_baspk 
			where kodeblok like '".$kodeunit."%' and tanggal like '".$periode."%'  
			and left(kodekegiatan,7) in (SELECT noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='PNN01')
			group by kodeblok
			order by kodeblok";
//exit("Warning: ".$sBorong2);
$qBorong2=mysql_query($sBorong2) or die(mysql_error($conn));
while($rBorong2=mysql_fetch_assoc($qBorong2)){
	$dtKodeBlok[$rBorong2['kodeorg']]=$rBorong2['kodeorg'];
	$dt_hapanen[$rBorong2['kodeorg']]=$rBorong2['hapanen'];
}

$sPanen3="select x.* from (
			select distinct a.kodeorg,b.tanggal from ".$dbname.".kebun_prestasi a
			left join ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where c.detail='1' and a.notransaksi like '%/PNN/%' and a.kodeorg like '".$kodeunit."%' and b.tanggal<'".$tglAwal."'
			UNION
			select distinct a.kodeblok as kodeorg,a.tanggal from ".$dbname.".log_baspk a
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeblok
			where c.detail='1' and a.kodeblok like '".$kodeunit."%' and a.tanggal<'".$tglAwal."'
			and left(a.kodekegiatan,7) in (SELECT noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='PNN01')
			) x order by x.kodeorg,x.tanggal";
//exit("Warning: ".$sPanen3);
$qPanen3=mysql_query($sPanen3) or die(mysql_error($conn));
while($rPanen3=mysql_fetch_assoc($qPanen3)){
	$dtKodeBlok[$rPanen3['kodeorg']]=$rPanen3['kodeorg'];
	$dtDateLalu[$rPanen3['kodeorg']]=$rPanen3['tanggal'];
	//$tanggal1 = new DateTime($rPanen3['tanggal']);
	//$tanggal2 = new DateTime($tglAwal);
	//$dtDataAwal[$rPanen3['kodeorg']]=$tanggal2->diff($tanggal1)->days + 1;
	$tanggal1 = strtotime($rPanen3['tanggal']);
	$tanggal2 = strtotime($tglAwal);
	$dtDataAwal[$rPanen3['kodeorg']]=($tanggal2-$tanggal1)/60/60/24;
	//exit("Warning: ".$rPanen3['tanggal'].' sd '.$tglAwal.' = '.$dtDataAwal[$rPanen3['kodeorg']]);
}

$dcek=count($dtKodeBlok);
if($dcek==0){
	exit("Error:Data Kosong");
}
sort($dtKodeBlok);
$brd=0;
$bgcolordt="";
if($proses=='excel'){
	$bgcolordt="bgcolor=#DEDEDE";
	$brd=1;
}
		$tab="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable>";
		$tab.="<thead><tr>";
		$tab.="<td ".$bgcolordt." rowspan=2 align=center >No</td>";
		$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['divisi']."</td>";
		$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['blok']."</td>";
		$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['tahuntanam']."</td>";
		$tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['luas']."</td>";
        $tab.="<td ".$bgcolordt." rowspan=2 align=center >".$_SESSION['lang']['jumlahpokok']."</td>";
        $tab.="<td ".$bgcolordt." rowspan=2 align=center >SPH</td>";
		$tab.="<td ".$bgcolordt." rowspan=2  align=center >Seksi Panen</td>";
		$tab.="<td ".$bgcolordt." colspan=".$lastday."  align=center >".$periode."</td>";
		$tab.="<td ".$bgcolordt." rowspan=2  align=center >Rotasi</td>";
		$tab.="<td ".$bgcolordt." rowspan=2  align=center >Ha Panen</td>";
        $tab.="</tr><tr>";
		for ($x=1; $x<=$lastday; $x++) {
			$tgl=$periode.'-'.sprintf('%02d',$x);
			$sLibur="select distinct tanggal from ".$dbname.".sdm_5harilibur
					where kebun in ('GLOBAL','KEBUN','ESTATE','".substr($kodeunit,0,4)."','".$optNamaPT[$kodeunit]."') and tanggal='".$tgl."'
					order by tanggal limit 1";
			$qLibur=mysql_query($sLibur) or die(mysql_error($conn));
			$nLibur=mysql_num_rows($qLibur);
			if($nLibur>0){
				$tab.="<td ".$bgcolordt." align=center style='width:20px;color:red'>".$x."</td>";
			}else{
				$tab.="<td ".$bgcolordt." align=center>".$x."</td>";
			}
        }
        $tab.="</tr></thead><tbody>";
		$no=0;
		$stluasBlok=0;
		$stJmlPokok=0;
		$st_hapanen=0;
		$stjmlpanen=0;
		$gtluasBlok=0;
		$gtJmlPokok=0;
		$gt_hapanen=0;
		$gtjmlpanen=0;
		$xdiv='  ';
        foreach($dtKodeBlok as $blok){
			if($dtluasBlok[$blok]<=0){
				continue;
			}
			$no+=1;
			if($no!=1 and substr($blok,0,6)!=$xdiv){
	            $tab.="<tr class=rowcontent>";
		        $tab.="<td bgcolor=#ffcc99 colspan=4 align=center>Total ".$xdiv."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stluasBlok)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stJmlPokok)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stSPH)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=center></td>";
				for ($x=1; $x<=$lastday; $x++) {
					$tab.="<td bgcolor=#ffcc99 align=right></td>";
				}
				$tab.="<td bgcolor=#ffcc99 align=center>".number_format($rtrotasi)."</td>";
				$tab.="<td bgcolor=#ffcc99 align=right>".number_format($st_hapanen)."</td>";
				$tab.="</tr>";
				$no=1;
				$stluasBlok=0;
				$stJmlPokok=0;
				$st_hapanen=0;
				$stjmlpanen=0;
			}
			$xdiv=substr($blok,0,6);
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".substr($blok,0,6)."</td>";
            $tab.="<td>".$optNamaBlok[$blok]."</td>";
            $tab.="<td align=center>".$dtTahunTnm[$blok]."</td>";
			$tab.="<td align=right>".number_format($dtluasBlok[$blok])."</td>";
			$tab.="<td align=right>".number_format($dtJmlPokok[$blok])."</td>";
			$tab.="<td align=right>".number_format($dtSPH[$blok])."</td>";
            $tab.="<td align=center></td>";
			$nu=$dtDataAwal[$blok]+0;
			$ard=$dtDataAwal[$blok]+0;
			//exit('Warning: '.$blok.'-'.$optNamaBlok[$blok].'='.$dtDataAwal[$blok]);
			$anggapada=0;
			$ada=0;
			$rotasi=0;
			for ($x=1; $x<=$lastday; $x++) {
				$tgl=$periode.'-'.sprintf('%02d',$x);
				$tglesok=date('Y-m-d',strtotime('+1 days',strtotime($tgl)));
				$tgllalu=date('Y-m-d',strtotime('-1 days',strtotime($tgl)));
				$tglcoba=date('Y-m-d',strtotime('-1 days',strtotime($tglesok)));
                $ard+=1;
                $nu+=1;
				if($tgl>date('Y-m-d')){
					$tab.="<td align=center style='width:20px'></td>";
				}else{
					if($dtDateKini[$blok.$tgl]==$tgl){
						if($ada==0){
							$sLiburlalu="select distinct tanggal from ".$dbname.".sdm_5harilibur
									where kebun in ('GLOBAL','KEBUN','ESTATE','".substr($kodeunit,0,4)."','".$optNamaPT[$kodeunit]."') and tanggal='".$tgllalu."'
									order by tanggal limit 1";
							$qLiburlalu=mysql_query($sLiburlalu) or die(mysql_error($conn));
							$nLiburlalu=mysql_num_rows($qLiburlalu);
							if($nLiburlalu>0 and $anggapada==1){
								$tab.="<td align=center bgcolor=#FF9999 style='width:20px'>".number_format($nu)."</td>";
								//$ard=$ard+2;
							}else{
								$tab.="<td align=center bgcolor=#99ff99 style='width:20px'>".number_format($nu)."</td>";
								$ard=1;
							}
							$rotasi+=1;
						}else{
							$tab.="<td align=center bgcolor=#FF9999 style='width:20px'>".number_format($nu)."</td>";
						}
						$nu-=1;
						$ada=1;
						$anggapada=1;
					}else{
						$sLibur="select distinct tanggal from ".$dbname.".sdm_5harilibur
								where kebun in ('GLOBAL','KEBUN','ESTATE','".substr($kodeunit,0,4)."','".$optNamaPT[$kodeunit]."') and tanggal='".$tgl."'
								order by tanggal limit 1";
						$qLibur=mysql_query($sLibur) or die(mysql_error($conn));
						$nLibur=mysql_num_rows($qLibur);
						if($nLibur>0){
							if($dtDateKini[$blok.$tglesok]==$tglesok){
								$tab.="<td align=center style='width:20px'>".number_format($nu)."</td>";
								//$ard=$ard+2;
								if($ada==1){
									$rotasi-=1;
								}
							}else{
								$tab.="<td align=center style='width:20px'>".number_format($ard)."</td>";
							}
							if($ada==1){
								$nu-=1;
							}
						}else{
							$tab.="<td align=center style='width:20px'>".number_format($ard)."</td>";
							$nu=$ard;
							$anggapada=0;
						}
						$ada=0;
					}
				}
            }
			$hapanen=($rotasi==0 ? 0 : $dt_hapanen[$blok]/$rotasi);
			$tab.="<td align=center>".number_format($rotasi)."</td>";
			$tab.="<td align=right>".number_format($hapanen)."</td>";
            $tab.="</tr>";
			$stluasBlok+=$dtluasBlok[$blok];
			$stJmlPokok+=$dtJmlPokok[$blok];
			$strotasi+=$rotasi;
			$st_hapanen+=$hapanen;
			$stjmlpanen+=($rotasi==0 ? 0 : 1);
			$gtluasBlok+=$dtluasBlok[$blok];
			$gtJmlPokok+=$dtJmlPokok[$blok];
			$gtrotasi+=$rotasi;
			$gt_hapanen+=$hapanen;
			$gtjmlpanen+=($rotasi==0 ? 0 : 1);
			$stSPH=($stluasBlok==0 ? 0 : $stJmlPokok/$stluasBlok);
			$strtrotasi=($stjmlpanen==0 ? 0 : $strotasi/$stjmlpanen);
        }
        $tab.="<tr class=rowcontent><td bgcolor=#ffcc99 colspan=4 align=center>".$_SESSION['lang']['total'].' '.$xdiv."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stluasBlok)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stJmlPokok)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($stSPH)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=center></td>";
		for ($x=1; $x<=$lastday; $x++) {
			$tab.="<td bgcolor=#ffcc99  align=right></td>";
		}
		$tab.="<td bgcolor=#ffcc99 align=center>".number_format($strtrotasi)."</td>";
		$tab.="<td bgcolor=#ffcc99 align=right>".number_format($st_hapanen)."</td>";
		if($kodedivisi==''){
			$gtSPH=($gtluasBlok==0 ? 0 : $gtJmlPokok/$gtluasBlok);
			$gtrtrotasi=($gtjmlpanen==0 ? 0 : $gtrotasi/$gtjmlpanen);
	        $tab.="</tr><tr class=rowcontent><td bgcolor=#ffcc00 colspan=4 align=center>Grand Total</td>";
			$tab.="<td bgcolor=#ffcc00 align=right>".number_format($gtluasBlok)."</td>";
			$tab.="<td bgcolor=#ffcc00 align=right>".number_format($gtJmlPokok)."</td>";
			$tab.="<td bgcolor=#ffcc00 align=right>".number_format($gtSPH)."</td>";
			$tab.="<td bgcolor=#ffcc00 align=center></td>";
			for ($x=1; $x<=$lastday; $x++) {
				$tab.="<td bgcolor=#ffcc00  align=right></td>";
			}
			$tab.="<td bgcolor=#ffcc00 align=center>".number_format($gtrtrotasi)."</td>";
			$tab.="<td bgcolor=#ffcc00 align=right>".number_format($gt_hapanen)."</td>";
		}
		$tab.="</tr></tbody></table>";

	switch($proses){
		case'preview':
			echo $tab;
		break;

		case'excel':
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
			$nop_="laporan_Monitoring_Pusingan_Panen_".$dte;
			$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gztralala, $tab);
			gzclose($gztralala);
			echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
				</script>";	
		break;

		default:
		break;
	}
?>
