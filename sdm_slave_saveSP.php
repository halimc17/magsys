<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$jenissp		=checkPostGet('jenissp','');
$karyawanid		=checkPostGet('karyawanid','');
$masaberlaku	=checkPostGet('masaberlaku','');
$tanggalsp		=tanggalsystem(checkPostGet('tanggalsp',''));
$paragraf1		=checkPostGet('paragraf1','');
$paragraf3		=checkPostGet('paragraf3','');
$paragraf4		=checkPostGet('paragraf4','');
$pelanggaran	=checkPostGet('pelanggaran','');
$penandatangan	=checkPostGet('penandatangan','');
$jabatan		=checkPostGet('jabatan','');
$tembusan1		=checkPostGet('tembusan1','');
$tembusan2		=checkPostGet('tembusan2','');
$tembusan3		=checkPostGet('tembusan3','');
$tembusan4		=checkPostGet('tembusan4','');
$method			=checkPostGet('method','');
$kodeorg		=substr($_SESSION['empl']['lokasitugas'],0,4);
$verifikasi                 =checkPostGet('verifikasi','');
$dibuat                     =checkPostGet('dibuat','');
$jabatan1                 =checkPostGet('jabatan1','');
$jabatan2                 =checkPostGet('jabatan2','');
        
$t=mktime(0,0,0,substr($tanggalsp,4,2)+$masaberlaku,substr($tanggalsp,6,2),substr($tanggalsp,0,4));
$sampai=date('Ymd',$t);

if($method=='selectsp'){
	if($jenissp=='BAPP'){
		echo readTextFile('config/sp_format/sp_pelanggaran_BAPP.lst')."###".readTextFile('config/sp_format/sp_paragraf2_BAPP.lst')."###".readTextFile('config/sp_format/sp_paragraf3_BAPP.lst')."###".readTextFile('config/sp_format/sp_paragraf4_BAPP.lst')."###".$_SESSION['lang']['yangmemeriksa']."###".$_SESSION['lang']['mengetahui']."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='ST1'){
		echo readTextFile('config/sp_format/sp_pelanggaran_ST1.lst')."###".readTextFile('config/sp_format/sp_paragraf2_ST1.lst')."###".readTextFile('config/sp_format/sp_paragraf3_ST1.lst')."###".''."###".$_SESSION['lang']['disetujui']."###".''."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='SP1'){
		echo readTextFile('config/sp_format/sp_pelanggaran_ST1.lst')."###".readTextFile('config/sp_format/sp_paragraf2_SP1.lst')."###".readTextFile('config/sp_format/sp_paragraf3_SP1.lst')."###".''."###".$_SESSION['lang']['disetujui']."###".$_SESSION['lang']['diketahuioleh']."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='SP2'){
		echo readTextFile('config/sp_format/sp_pelanggaran_ST1.lst')."###".readTextFile('config/sp_format/sp_paragraf2_SP2.lst')."###".readTextFile('config/sp_format/sp_paragraf3_SP2.lst')."###".''."###".$_SESSION['lang']['disetujui']."###".$_SESSION['lang']['diketahuioleh']."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='SP3'){
		echo readTextFile('config/sp_format/sp_pelanggaran_ST1.lst')."###".readTextFile('config/sp_format/sp_paragraf2_SP3.lst')."###".readTextFile('config/sp_format/sp_paragraf3_SP3.lst')."###".''."###".$_SESSION['lang']['disetujui']."###".$_SESSION['lang']['diketahuioleh']."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='SKR'){
		echo ''."###".readTextFile('config/sp_format/sp_paragraf2_SKR.lst')."###".readTextFile('config/sp_format/sp_paragraf3_SKR.lst')."###".readTextFile('config/sp_format/sp_paragraf4_SKR.lst')."###".$_SESSION['lang']['disetujui']."###".''."###".''."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}else if($jenissp=='PHK'){
		echo ''."###".''."###".readTextFile('config/sp_format/sp_paragraf3_PHK.lst')."###".readTextFile('config/sp_format/sp_paragraf4_PHK.lst')."###".$_SESSION['lang']['disetujui']."###".''."###".''."###".$_SESSION['lang']['membaca']."###".$_SESSION['lang']['menimbang']."###".$_SESSION['lang']['mengingat']."###".$_SESSION['lang']['menetapkan'];
	}else{
		echo ''."###".''."###".''."###".''."###".$_SESSION['lang']['disetujui']."###".$_SESSION['lang']['diketahuioleh']."###".$_SESSION['lang']['dibuat']."###".$_SESSION['lang']['pelanggaran']."###".'Paragraf 2'."###".'Paragraf 3'."###".'Paragraf 4';
	}
	
}else{

if($method=='insert')
{

if($jenissp=='BAPP'){
		$js='BAPK';
	}else if($jenissp=='ST1'){
		$js='ST';
	}else if($jenissp=='SP1'){
		$js='SP.I';
	}else if($jenissp=='SP2'){
		$js='SP.II';
	}else if($jenissp=='SP3'){
		$js='SP.III';
	}else if($jenissp=='SKR'){
		$js='SS';
	}else if($jenissp=='PHK'){
		$js='SK.PHK';
	}else{
		$js='SP';
	}
	$bulan = substr($tanggalsp,4,2);
	$tahun = substr($tanggalsp,0,4);
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	$ql="select `nomor` from ".$dbname.".`sdm_suratperingatan` where year(tanggal)='".$tahun."'";
	$qr=mysql_query($ql) or die(mysql_error());
	$ql2="select `nomor` from ".$dbname.".`sdm_suratperingatan` where jenissp='".$jenissp."' and year(tanggal)='".$tahun."'";
	$qr2=mysql_query($ql2) or die(mysql_error());
	
	$countNo = mysql_num_rows($qr2);
	$countNoGlobal = mysql_num_rows($qr);
	
	$noSP = addZero($countNoGlobal+1,3)."/".$js."-".addZero($countNo+1,3)."/".$resultRomawi."/".$tahun;


//get number
$potSK=substr($_SESSION['empl']['lokasitugas'],0,4).strtoupper($jenissp).substr($tanggalsp,0,4);
$str="select nomor from ".$dbname.".sdm_suratperingatan
      where  nomor like '".$potSK."%'
          order by nomor desc limit 1";	  
$notrx=0;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
        $notrx=substr($bar->nomor,10,5);
}
$notrx=intval($notrx);
$notrx=$notrx+1;
$notrx=str_pad($notrx, 4, "0", STR_PAD_LEFT);
$notrx=$potSK.$notrx;

        $str="insert into ".$dbname.".sdm_suratperingatan (
                      `nomor`,`jenissp`,`karyawanid`,
                          `pelanggaran`,`tanggal`,`masaberlaku`,
                          `sampai`,`tembusan1`,`tembusan2`,
                          `tembusan4`,`tembusan3`,
                          `kodeorg`, `penandatangan`,`jabatan`,
                          `updateby`,`paragraf1`,`paragraf3`,
                          `paragraf4`,`verifikasi`,`dibuat`,`jabatanverifikasi`,`jabatandibuat`
                  ) values(
                   '".$noSP."','".$jenissp."',".$karyawanid.",
                   '".$pelanggaran."',".$tanggalsp.",".$masaberlaku.",
                   ".$sampai.",'".$tembusan1."','".$tembusan2."',
                   '".$tembusan4."','".$tembusan3."','".$kodeorg."',
                   '".$penandatangan."','".$jabatan."',".$_SESSION['standard']['userid'].",
                   '".$paragraf1."','".$paragraf3."','".$paragraf4."','".$verifikasi."','".$dibuat."','".$jabatan1."','".$jabatan2."'
                  )";  
}
else if($method=='delete')
{
  $nosp=$_POST['nosp'];
        $str="delete from ".$dbname.".sdm_suratperingatan
              where karyawanid=".$karyawanid." and nomor='".$nosp."'"; 
}
else if($method=='update')
{
  $nosp=$_POST['nosp'];
        $str="update ".$dbname.".sdm_suratperingatan set
                          `jenissp`='".$jenissp."',
                          `pelanggaran`='".$pelanggaran."',
                          `tanggal`=".$tanggalsp.",
                          `masaberlaku`=".$masaberlaku.",
                          `sampai`=".$sampai.",
                          `tembusan1`='".$tembusan1."',
                          `tembusan2`='".$tembusan2."',
                          `tembusan4`='".$tembusan4."',
                          `tembusan3`='".$tembusan3."',
                          `kodeorg`='".$kodeorg."', 
                          `penandatangan`='".$penandatangan."',
                          `jabatan`='".$jabatan."',
                          `updateby`=".$_SESSION['standard']['userid'].",
                          `paragraf1`='".$paragraf1."',
                          `paragraf3`='".$paragraf3."',
                          `paragraf4`='".$paragraf4."',
                          `verifikasi`='".$verifikasi."',
                          `dibuat`='".$dibuat."',
                          `jabatanverifikasi`='".$jabatan1."',
                          `jabatandibuat`='".$jabatan2."'
                where karyawanid=".$karyawanid." and nomor='".$nosp."'"; 	  	
}

if(mysql_query($str))
{}
else
   echo " Gagal:".addslashes(mysql_error($conn));

}

?>