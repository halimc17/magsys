<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$kodeorg	=$_POST['kodeorgJ'];
$karyawanid	=$_POST['karyawanidJ'];
$periode	=$_POST['periodeJ'];
$dari		=tanggalsystem($_POST['dariJ']);
$sampai		=tanggalsystem($_POST['sampaiJ']);
$diambil	=$_POST['diambilJ'];
$keterangan	=$_POST['keteranganJ'];
$method     =$_POST['method'];

$optSubBagian = makeOption($dbname,'datakaryawan','karyawanid,subbagian');

//periksa apakah ada yang tidak benar
//==============================================
function getRangeTanggal($tglAwal,$tglAkhir){
	$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
	$jlhHari = $jlh / (3600*24);
	return $jlhHari + 1;
}

$rangeTgl = rangeTanggal($dari, $sampai);

if($method=='insert')
{
	if(getRangeTanggal($dari,$sampai) != $diambil){
		exit("Gagal : Perisaka kembali tanggal dari/sampai cuti.");
	}
	
	$strAbsen = "select * from ".$dbname.".sdm_absensidt where karyawanid = '".$karyawanid."' and tanggal between '".$dari."' and '".$sampai."'";
	if(mysql_num_rows(mysql_query($strAbsen)) > 0){
		exit("Gagal : Untuk range tanggal awal s/d akhir cuti sudah ada absen.");
	}
	
$strc="select * from ".$dbname.".sdm_cutidt
       where karyawanid = '".$karyawanid."' and ((daritanggal>=".$dari." and daritanggal<=".$sampai.")
	   or (sampaitanggal>=".$dari." and sampaitanggal<=".$sampai.")
	   or (daritanggal<=".$dari." and sampaitanggal>=".$sampai."))";
	if(mysql_num_rows(mysql_query($strc))>0)
	{
		echo " Error ".$_SESSION['lang']['irisan'];
		exit(0);
	}	
	else if($sampai<$dari)
	{
		echo " Error < >";
		exit(0);
	} 
}
  
//===============================================

	if($diambil==''){
		$diambil=0;
	}
	
	switch($method)
	{
	case 'delete':	
		$rangeTglDel = rangeTanggal($_POST['dariJ'], $_POST['sampaiJ']);
		
		foreach($rangeTglDel as $val){
			$strDelAbs = "delete from ".$dbname.".sdm_absensidt where kodeorg='".$optSubBagian[$karyawanid]."' and karyawanid='".$karyawanid."' and tanggal='".$val."' and absensi='C'";
			mysql_query($strDelAbs);
		}
		
		$str="delete from ".$dbname.".sdm_cutidt
		       where kodeorg='".$kodeorg."'
			   and karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'
			   and daritanggal='".$_POST['dariJ']."'";
		break;	   
	case 'insert':
		foreach($rangeTgl as $val){
			$strAbs = "insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,penjelasan,catu,penaltykehadiran,premi,insentif,fingerprint) values ('".$optSubBagian[$karyawanid]."','".$val."','".$karyawanid."','','C','00:00:00','00:00:00','".$keterangan."','0','0','0','0','0')";
			mysql_query($strAbs);
		}
	
		$str="insert into ".$dbname.".sdm_cutidt 
		      (kodeorg,karyawanid,periodecuti,daritanggal,
			  sampaitanggal,jumlahcuti,keterangan
			  )
		      values('".$kodeorg."',".$karyawanid.",
			  '".$periode."','".$dari."','".$sampai."',
			  ".$diambil.",'".$keterangan."'
			  )";
		break;
	default:
	   break;					
	}
	if(mysql_query($str))
		{
		//ambil sum jumlah diambil dan update table header
		$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
		       where kodeorg='".$kodeorg."'
			   and karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'";
			   
		$diambil=0;
		$resx=mysql_query($strx);
		while($barx=mysql_fetch_object($resx))
		{
			$diambil=$barx->diambil;
		}
                if($diambil=='')
                    $diambil=0;
		$strup="update ".$dbname.".sdm_cutiht set diambil=".$diambil.",sisa=(hakcuti-".$diambil.")	
		       where kodeorg='".$kodeorg."'
			   and karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'";

		mysql_query($strup);
		}
	else
		{echo " Gagal,".addslashes(mysql_error($conn));}

?>
