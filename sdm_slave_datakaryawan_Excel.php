<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');

$ptsearch = checkPostGet('ptsearch','');
$regional = checkPostGet('regional','');
$txtsearch = checkPostGet('txtsearch','');
$orgsearch = checkPostGet('orgsearch','');
$subunit = checkPostGet('subunit','');
$kodegolongan = checkPostGet('kodegolongan','');
$tipesearch = checkPostGet('tipesearch','');
$statussearch = checkPostGet('statussearch','');
$schjk = checkPostGet('schjk','');
$levelpendidikan = checkPostGet('levelpendidikan','');
$tanggalmasuk1 = tanggalsystem(checkPostGet('tanggalmasuk1',''));
$tanggalmasuk2 = tanggalsystem(checkPostGet('tanggalmasuk2',''));
$tanggalkeluar1 = tanggalsystem(checkPostGet('tanggalkeluar1',''));
$tanggalkeluar2 = tanggalsystem(checkPostGet('tanggalkeluar2',''));
$umur1 = checkPostGet('umur1','');
$umur2 = checkPostGet('umur2','');

$where='';
if($txtsearch!='')
	$where= " and a.namakaryawan like '%".$txtsearch."%'";
if($orgsearch!=''){
	$where .=" and (a.lokasitugas='".$orgsearch."' or left(a.subbagian,4)='".$orgsearch."')";    
}else{
	if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
		if($ptsearch!=''){
			//$where.=" and a.kodeorganisasi='".$ptsearch."'";
			$where.=" and (a.kodeorganisasi='".$ptsearch."' or left(a.subbagian,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptsearch."'))";
		}
		if($regional!=''){
			if($regional=='JAKARTA'){
				$where.=" and left(a.subbagian,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
			}else{
				$where.=" and a.lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
			}
		}
    }else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
        $where .=" and a.lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where "
                . " induk='".$_SESSION['empl']['kodeorganisasi']."')";    
    }else{
        $where .=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."'";    
    }
}
if($subunit!=''){
	$where .=" and a.subbagian='".$subunit."'"; 
}
if($kodegolongan!=''){
	$where .=" and a.kodegolongan like '".$kodegolongan."%'"; 
}
if($tipesearch!=''){
	if($tipesearch==100){
		$where.=" and a.tipekaryawan!=4 ";
	}else{
		$where .=" and a.tipekaryawan='".$tipesearch."'"; 
	}
}
if($schjk!=''){
	$where.=" and a.jeniskelamin='".$schjk."'";
}
if($levelpendidikan!=''){
	$where .=" and a.levelpendidikan='".$levelpendidikan."'"; 
}
$hariini = date("Y-m-d");
if($tanggalmasuk1!='' and $tanggalmasuk2==''){
	//$tanggalmasuk2=$tanggalmasuk1;
	$hariini = $tanggalmasuk1;
}
if($tanggalmasuk1=='' and $tanggalmasuk2!=''){
	//$tanggalmasuk1=$tanggalmasuk2;
	$hariini = $tanggalmasuk2;
}
if($tanggalmasuk1!='' and $tanggalmasuk2!=''){
	$where.=" and (a.tanggalmasuk>='".$tanggalmasuk1."' and a.tanggalmasuk<='".$tanggalmasuk2."')";
}else{
	//if($statussearch=='0000-00-00'){
	//	$where .=" and a.tanggalmasuk<='".$hariini."'";
	//}
}
if($tanggalkeluar1!='' and $tanggalkeluar2==''){
	//$tanggalkeluar2=$tanggalkeluar1;
	$hariini = $tanggalkeluar1;
}
if($tanggalkeluar1=='' and $tanggalkeluar2!=''){
	//$tanggalkeluar1=$tanggalkeluar2;
	$hariini = $tanggalkeluar2;
}
if($tanggalkeluar1!='' and $tanggalkeluar2!=''){
	$where.=" and (a.tanggalkeluar>='".$tanggalkeluar1."' and a.tanggalkeluar<='".$tanggalkeluar2."')";
}
if($umur1!='' and $umur2==''){
	$umur2=$umur1;
}
if($umur1=='' and $umur2!=''){
	$umur1=$umur2;
}
if($umur1!='' and $umur2!=''){
	$where.=" and ((year(CURDATE())-year(a.tanggallahir))>='".$umur1."' and (year(CURDATE())-year(a.tanggallahir))<='".$umur2."')";
}
//$hariini = date("Y-m-d");
if($statussearch=='*'){
	//$where .=" and (a.tanggalkeluar!='0000-00-00')";
	$where .=" and (a.tanggalkeluar!='0000-00-00' and a.tanggalkeluar<'".$hariini."')"; // tidak aktif
}else if($statussearch=='0000-00-00'){
	//$where .=" and (a.tanggalkeluar='0000-00-00')";
	$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
}else{
	if($tanggalmasuk1!='' and $tanggalmasuk2==''){
		$where .=" and a.tanggalmasuk<='".$hariini."'";
	}
	if($tanggalmasuk1=='' and $tanggalmasuk2!=''){
		$where .=" and a.tanggalmasuk<='".$hariini."'";
	}
	if($tanggalkeluar1!='' and $tanggalkeluar2==''){
		$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
	}
	if($tanggalkeluar1=='' and $tanggalkeluar2!=''){
		$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
	}
}
	 
//make sure user can only access allowed data   
$listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
$list=str_replace("|","','",$listOrg);
$list="'".$list."'";

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan ".$where."
		order by a.namakaryawan";
	$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment
		from ".$dbname.".sdm_karyawankeluarga b
		left join ".$dbname.".datakaryawan a on b.karyawanid=a.karyawanid
		left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
		where 1=1 ".$where." order by a.namakaryawan ASC";
}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') ".$where."
		order by a.namakaryawan";
	$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment 
		from ".$dbname.".sdm_karyawankeluarga b
		left join ".$dbname.".datakaryawan a on b.karyawanid=a.karyawanid
		left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
		where a.tipekaryawan!=0 and a.lokasitugas in(".$list.") ".$where." order by a.namakaryawan ASC"; 
}else{
	//a.tipekaryawan!=0 orang yang tidak di pusat tidak dapat melihat data orang permanent
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') and lokasitugas in(".$list.") ".$where." 
		order by a.namakaryawan";
	$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment 
		from ".$dbname.".sdm_karyawankeluarga b
		left join ".$dbname.".datakaryawan a on b.karyawanid=a.karyawanid
		left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
		where a.tipekaryawan!=0 and a.lokasitugas in(".$list.") ".$where." order by a.namakaryawan ASC"; 
}
//exit('Warning: '.$strd);
//=====================
$stream='';

 
   $stream.="
       Daftar karyawan:
	   <table border=1>
	   <tr>
	     <td align=center>No.</td>
 		 <td align=center>".$_SESSION['lang']['nokaryawan']."</td>		 
		 <td align=center>".$_SESSION['lang']['nik']."</td>
		 <td align=center>".$_SESSION['lang']['nama']."</td>
		 <td align=center>".$_SESSION['lang']['functionname']."</td>
		 <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
		 <td align=center>".$_SESSION['lang']['lokasitugas']."</td>
		 <td align=center>".$_SESSION['lang']['pt']."</td>
		 <td align=center>".$_SESSION['lang']['noktp']."</td>
		 <td align=center>".$_SESSION['lang']['pendidikan']."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statusperkawinan'])."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['jumlahanak'])."</td>
		 <td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalpengangkatan']."</td>
		 <td align=center>".$_SESSION['lang']['masakerja']." (".$_SESSION['lang']['tahun'].")</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</td>
		 <td align=center>".$_SESSION['lang']['tempatlahir']."</td>
		 <td align=center>".$_SESSION['lang']['tanggallahir']."</td>
		 <td align=center>".$_SESSION['lang']['umur']." (".$_SESSION['lang']['tahun'].")</td>
		 <td align=center>".$_SESSION['lang']['warganegara']."</td>
		 <td align=center>".$_SESSION['lang']['jeniskelamin']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalmenikah']."</td>
		 <td align=center>".$_SESSION['lang']['agama']."</td>
		 <td align=center>".$_SESSION['lang']['golongandarah']."</td>
		 <td align=center>".$_SESSION['lang']['alamataktif']."</td>
		 <td align=center>".$_SESSION['lang']['provinsi']."</td>
		 <td align=center>".$_SESSION['lang']['kota']."</td>
		 <td align=center>".$_SESSION['lang']['kodepos']."</td>
		 <td align=center>".$_SESSION['lang']['noteleponrumah']."</td>
		 <td align=center>".$_SESSION['lang']['nohp']."</td>
		 <td align=center>".$_SESSION['lang']['norekeningbank']."</td>
		 <td align=center>".$_SESSION['lang']['namabank']."</td>
		 <td align=center>".$_SESSION['lang']['sistemgaji']."</td>
		 <td align=center>".$_SESSION['lang']['nopaspor']."</td>
		 <td align=center>".$_SESSION['lang']['notelepondarurat']."</td>
   		 <td align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
		 <td align=center>".$_SESSION['lang']['jumlahtanggungan']."</td>
		 <td align=center>".$_SESSION['lang']['npwp']."</td>
		 <td align=center>".$_SESSION['lang']['lokasipenerimaan']."</td>
		 <td align=center>".$_SESSION['lang']['bagian']."</td>
		 <td align=center>".$_SESSION['lang']['subbagian']."</td>
         <td align=center>".$_SESSION['lang']['jms']."</td>    
         <td align=center>".$_SESSION['lang']['bpjs'].' '.$_SESSION['lang']['kesehatan']."</td>    
		 <td align=center>".$_SESSION['lang']['email']."</td>
		 <td align=center>".'Kependudukan'."</td>
	     </tr>";
   
function getAge($tgldate,$tgldata){
	$tdate=strtotime($tgldate);
	$dob=strtotime($tgldata);
	$age = 0;
	//while( $tdate > $dob = strtotime('+1 year', $dob)){
	//	++$age;
	//}
	$bllalu=date('Y-m-d',strtotime('-1 month',$dob));
	$thn=(date('Y',$tdate)-date('Y',$dob))-(date('m',$tdate)<date('m',$dob) || (date('m',$tdate)==date('m',$dob) && date('d',$tdate)<date('d',$dob)) ? 1 : 0);
	//$bln=(substr($tgldate,5,5)<substr($tgldata,5,5) ? 12 : 0)+(date('m',$tdate)-date('m',$dob))-(date('d',$tdate)<date('d',$dob) ? 1 : 0);
	$bln=(date('m',$tdate)<date('m',$dob) || (date('m',$tdate)==date('m',$dob) && date('d',$tdate)<date('d',$dob)) ? 12 : 0)+(date('m',$tdate)-date('m',$dob))-(date('d',$tdate)<date('d',$dob) ? 1 : 0);
	$tgl=(date('d',$tdate)<date('d',$dob) ? date('t',strtotime($bllalu)) : 0)+date('d',$tdate)-date('d',$dob);
	$age=$thn.' Th, '.$bln.' Bl, '.$tgl.' Hr';
	return $age;
}
$res=mysql_query($str) or die(mysql_error($conn));
$numrows=mysql_numrows($res);
if($numrows<1)
{
	$stream.="<tr><td>NOT FOUND</td></tr>";
}
else
{
	$no=0;
	while($bar=mysql_fetch_object($res))
	{
		$no+=1;
//                $masakerja=$tahunini-substr($bar->tanggalmasuk,0,4);
//                $usia=$tahunini-substr($bar->tanggallahir,0,4)+1;
                $masakerja=getAge($hariini,$bar->tanggalmasuk);
                $usia=getAge($hariini,$bar->tanggallahir);
		$sqlkota="SELECT wilayahkota FROM ".$dbname.".organisasi where kodeorganisasi='".$bar->lokasitugas."'";
		$qrykota=mysql_query($sqlkota) or die ("SQL ERR : ".mysql_error());
		$lokal='Non Lokal';
		while ($data=mysql_fetch_assoc($qrykota)){
			$lokal=(strtoupper($data['wilayahkota'])==strtoupper($bar->kota) ? 'Lokal' : 'Non Lokal');
		}
		$stream.="<tr>
		     <td>".$no."</td>
			 <td>'".$bar->karyawanid."</td>
			 <td>".$bar->nik."</td>
			 <td>".$bar->namakaryawan."</td>
			 <td>".$bar->namajabatan."</td>
			 <td>".$bar->namagolongan."</td>
			 <td>".$bar->lokasitugas."</td>
			 <td>".$bar->kodeorganisasi."</td>
			 <td>".($bar->noktp=="" ? "" : "'").$bar->noktp."</td>
			 <td>".$bar->kelompok."</td>
			 <td>".$bar->statuspajak."</td>
			 <td>".$bar->statusperkawinan."</td>
			 <td align=right >".$bar->jumlahanak."</td>
			 <td>".$bar->tanggalmasuk."</td>
			 <td>".$bar->tanggalpengangkatan."</td>
			 <td>".$masakerja."</td>
			 <td>".$bar->tipe."</td>
			 <td>".$bar->tempatlahir."</td>
			 <td>".$bar->tanggallahir."</td>
			 <td>".$usia."</td>
			 <td>".$bar->warganegara."</td>
			 <td>".$bar->jeniskelamin."</td>
			 <td>".$bar->tanggalmenikah."</td>
			 <td>".$bar->agama."</td>
			 <td>".$bar->golongandarah."</td>
			 <td>".$bar->alamataktif."</td>
			 <td>".$bar->provinsi."</td>
			 <td>".$bar->kota."</td>
			 <td>".$bar->kodepos."</td>
			 <td>".$bar->noteleponrumah."</td>
			 <td>".$bar->nohp."</td>
			 <td>".$bar->norekeningbank."</td>
			 <td>".$bar->namabank."</td>
			 <td>".$bar->sistemgaji."</td>
			 <td>".$bar->nopaspor."</td>
			 <td>".$bar->notelepondarurat."</td>
			 <td>".$bar->tanggalkeluar."</td>
			 <td>".$bar->jumlahtanggungan."</td>
			 <td>".$bar->npwp."</td>
			 <td>".$bar->lokasipenerimaan."</td>
			 <td>".$bar->bagian."</td>
			 <td>".$bar->subbagian."</td>
             <td>".($bar->jms=="" ? "" : "'").$bar->jms."</td>
             <td>".($bar->bpjs=="" ? "" : "'").$bar->bpjs."</td>
			 <td>".$bar->email."</td>	 
			 <td>".$lokal."</td>
		  </tr>";			 		  
	}
	$stream.="</table>";
	
//============================keluarga
$stream.= "KELUARGA";
   $stream.="<table border=1>
	   <tr>
	     <td align=center>No.</td>
 		 <td align=center>".$_SESSION['lang']['nokaryawan']."</td>		 
		 <td align=center>".$_SESSION['lang']['nama']."</td>
		 <td align=center>".$_SESSION['lang']['anggotakeluarga']."</td>
		 <td align=center>".$_SESSION['lang']['jeniskelamin']."</td>
		 <td align=center>".$_SESSION['lang']['hubungan']."</td>
	 	 <td align=center>".$_SESSION['lang']['tempatlahir']."</td>
		 <td align=center>".$_SESSION['lang']['tanggallahir']."</td>		 		 
		 <td align=center>".$_SESSION['lang']['pekerjaan']."</td> 
		 <td align=center>".$_SESSION['lang']['statusperkawinan']."</td>	 
		 <td align=center>".$_SESSION['lang']['pendidikan']."</td>		 
		 <td align=center>".$_SESSION['lang']['email']."</td>
		 <td align=center>".$_SESSION['lang']['telp']."</td>	 
		 <td align=center>".$_SESSION['lang']['tanggungan']."</td>
		 <td align=center>".$_SESSION['lang']['emplasment']."</td>
	     </tr>";
$res=mysql_query($strd);
	$no=0;
	while($bar=mysql_fetch_object($res))
	{
		$no+=1;
   $stream.="<table border=1>
	   <tr>
	     <td>".$no."</td>
 		 <td>'".$bar->karyawanid."</td>		 
		 <td>".$bar->namakaryawan."</td>
		 <td>".$bar->nama."</td>
		 <td>".$bar->jeniskelamin."</td>
		 <td>".$bar->hubungankeluarga."</td>
	 	 <td>".$bar->tempatlahir."</td>
		 <td>".tanggalnormal($bar->tanggallahir)."</td>		 		 
		 <td>".$bar->pekerjaan."</td> 
		 <td>".$bar->status."</td>	 
		 <td>".$bar->kelompok."</td>		 
		 <td>".$bar->email."</td>
		 <td>".$bar->telp."</td>	 
		 <td>".$bar->statusx."</td>
		 <td>".$bar->emplasment."</td>
	     </tr>";		
	}
$stream.="</table>";
}
$wktu=date("Hms");
$nop_="DT_Employee_".$wktu."__".date('Y');
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
} 
?>
