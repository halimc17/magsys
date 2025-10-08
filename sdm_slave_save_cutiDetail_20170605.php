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

if(!strstr(strtoupper($keterangan),'CUTI')){
   $keterangan='CUTI '.$keterangan;
}

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
	//if(getRangeTanggal($dari,$sampai) != $diambil){
	//	exit("Gagal : Periksa kembali tanggal dari/sampai cuti.");
	//}
	if(getRangeTanggal($dari,$sampai) < $diambil){
		exit("Gagal : Periksa kembali tanggal dari/sampai cuti.");
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
		
		$str3="select * from ".$dbname.".sdm_ijin
		       where karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'
			   and substr(darijam,1,10)='".$_POST['dariJ']."'
			   and substr(sampaijam,1,10)='".$_POST['sampaiJ']."'
			   and persetujuan1=hrd";
		$res3=mysql_query($str3);
        	$inputhrd=0;
        	while($bar3=mysql_fetch_object($res3)){
           	   $inputhrd=$bar3->hrd;
        	}
        	if($inputhrd==0){
		   $str2="update ".$dbname.".sdm_ijin set stpersetujuanhrd=0,komenst2='' 
		       where karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'
			   and substr(darijam,1,10)='".$_POST['dariJ']."'
			   and substr(sampaijam,1,10)='".$_POST['sampaiJ']."'
			   and stpersetujuanhrd='1'";
		}else{
		   $str2="delete from ".$dbname.".sdm_ijin
		       where karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'
			   and substr(darijam,1,10)='".$_POST['dariJ']."'
			   and substr(sampaijam,1,10)='".$_POST['sampaiJ']."'
			   and stpersetujuanhrd='1'";
	    	}
		mysql_query($str2);
	
		$str="delete from ".$dbname.".sdm_cutidt
		       where karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'
			   and daritanggal='".$_POST['dariJ']."'
			   and sampaitanggal='".$_POST['sampaiJ']."'";

		break;	   
	case 'insert':
		foreach($rangeTgl as $val){
			$strAbs = "insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,penjelasan,catu,penaltykehadiran,premi,insentif,fingerprint) values ('".$optSubBagian[$karyawanid]."','".$val."','".$karyawanid."','','C','00:00:00','00:00:00','".$keterangan."','0','0','0','0','0')";
			mysql_query($strAbs);
		}
	
		$str2="insert into ".$dbname.".sdm_ijin 
		      values('".$karyawanid."',".$dari.",'".$keterangan."','".$keterangan."','".$_SESSION['standard']['userid']."','1','Permintaan Disetujui',".tanggalsystem(date('d-m-Y,')).",".$dari.",".$dari.",".$sampai.",'CUTI','".$_SESSION['standard']['userid']."','1',
			  '".$periode."',".$diambil.",'Permintaan Disetujui')";
		mysql_query($str2);

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
        /*
		$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
		       where kodeorg='".$kodeorg."'
			   and karyawanid=".$karyawanid."
			   and periodecuti='".$periode."'";
		*/
		$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
		       where upper(keterangan) like '%CUTI%'
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
