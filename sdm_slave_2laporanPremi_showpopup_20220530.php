<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$kdUnit=$_POST['kdUnit'];
	$periode=$_POST['periode'];
	$idkary=$_POST['idkary'];
	$tipe=$_POST['tipe'];

	if($kdUnit=='')$kdUnit=$_GET['kdUnit'];
	if($periode=='')$periode=$_GET['periode'];
	if($idkary=='')$idkary=$_GET['idkary'];
	if($tipe=='')$tipe=$_GET['tipe'];

	$per=explode('-',$periode);
	$periodem=$per[0].$per[1];
	$whr='';
	if($idkary!=''){
		$whr.=" and b.karyawanid='".$idkary."'";
	}
	
	if($tipe=='Panen'){
        $strz="SELECT a.notransaksi as transaksi,CONCAT(left(a.notransaksi,4),'-',substr(a.notransaksi,5,2),'-',right(a.notransaksi,2)) as tanggal
					 ,a.nik as karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,a.hasilkerja,a.hasilkerjakg,a.norma
					 ,if(a.upahpremi-(g.premibrondolan*a.brondolan)>0,g.premiliburcapaibasis,0) as premicapaibasis
					 ,if(a.upahpremi-(g.premibrondolan*a.brondolan)>0,a.upahpremi-(g.premibrondolan*a.brondolan)-g.premiliburcapaibasis,0) as premioverbasis
					 ,a.upahpremi-(g.premibrondolan*a.brondolan) as premibasis,a.brondolan,g.premibrondolan*a.brondolan as brondolanpremi
					 ,a.upahpenalty,a.rupiahpenalty
					 ,0 as premirawat,0 as premitraksi,0 as dendatraksi,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas
					 ,0 as premiandor
				FROM ".$dbname.".kebun_prestasi a
				LEFT JOIN ".$dbname.".datakaryawan b on a.nik = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				LEFT JOIN ".$dbname.".organisasi e on e.kodeorganisasi=left(a.kodeorg,4)
				LEFT JOIN ".$dbname.".setup_blok f on f.kodeorg=a.kodeorg
				LEFT JOIN ".$dbname.".kebun_5basispanen2 g on g.afdeling=e.induk and g.topografi=f.topografi and  g.kelaspohon=f.kelaspohon
						  and g.jenispremi=if(a.upahkerja='0','LIBUR','KERJA')
				WHERE a.notransaksi like '".$periodem."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr." 
					  and a.upahpremi+a.upahpenalty+a.rupiahpenalty+a.brondolan<>0
				ORDER BY a.notransaksi";
	}elseif($tipe=='Rawat'){
		$strz="SELECT a.notransaksi as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty
					 ,a.insentif as premirawat
					 ,0 as premitraksi,0 as dendatraksi,0 as premiabsensi,0 as dendaabsensi,0 as insentif,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".kebun_kehadiran_vw a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.insentif<>0
				ORDER BY a.notransaksi";
	}elseif($tipe=='Traksi'){
		$strz="SELECT a.notransaksi as transaksi,a.tanggal,a.idkaryawan,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat
					 ,a.premi as premitraksi,a.penalty as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".vhc_runhk a
				LEFT JOIN ".$dbname.".datakaryawan b on a.idkaryawan = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premi+a.penalty<>0
				ORDER BY a.tanggal,a.notransaksi";
	}elseif($tipe=='Absensi'){
		$strz="SELECT CONCAT('Absensi ',if(isnull(a.penjelasan),'',a.penjelasan)) as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan
					 ,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,a.premi as premiabsensi,a.insentif,a.penaltykehadiran as dendaabsensi
					 ,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".sdm_absensidt a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premi+a.insentif+a.penaltykehadiran<>0
				ORDER BY a.tanggal";
	}elseif($tipe=='Pengawas'){
		$strz="SELECT 'Pengawas' as transaksi,CONCAT(a.periodegaji,'-01') as tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan
					 ,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premiandor
					 ,a.jumlah as premipengawas
				FROM ".$dbname.".sdm_gaji a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.idkomponen = '16' and a.periodegaji='".$periode."' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.jumlah<>0
				ORDER BY a.periodegaji";
	}elseif($tipe=='Mandor'){
		$strz="SELECT 'Mandor' as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas
					 ,a.premiinput as premiandor
				FROM ".$dbname.".kebun_premikemandoran a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.posting='1' and a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premiinput<>0
				ORDER BY a.tanggal";
	}else{
        $strz="SELECT a.notransaksi as transaksi,CONCAT(left(a.notransaksi,4),'-',substr(a.notransaksi,5,2),'-',right(a.notransaksi,2)) as tanggal
					 ,a.nik as karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,a.hasilkerja,a.hasilkerjakg,a.norma
					 ,if(a.upahpremi-(g.premibrondolan*a.brondolan)>0,g.premiliburcapaibasis,0) as premicapaibasis
					 ,if(a.upahpremi-(g.premibrondolan*a.brondolan)>0,a.upahpremi-(g.premibrondolan*a.brondolan)-g.premiliburcapaibasis,0) as premioverbasis
					 ,a.upahpremi-(g.premibrondolan*a.brondolan) as premibasis,a.brondolan,g.premibrondolan*a.brondolan as brondolanpremi
					 ,a.upahpenalty,a.rupiahpenalty
					 ,0 as premirawat,0 as premitraksi,0 as dendatraksi,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas
					 ,0 as premiandor
				FROM ".$dbname.".kebun_prestasi a
				LEFT JOIN ".$dbname.".datakaryawan b on a.nik = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				LEFT JOIN ".$dbname.".organisasi e on e.kodeorganisasi=left(a.kodeorg,4)
				LEFT JOIN ".$dbname.".setup_blok f on f.kodeorg=a.kodeorg
				LEFT JOIN ".$dbname.".kebun_5basispanen2 g on g.afdeling=e.induk and g.topografi=f.topografi and  g.kelaspohon=f.kelaspohon
						  and g.jenispremi=if(a.upahkerja='0','LIBUR','KERJA')
				WHERE a.notransaksi like '".$periodem."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.upahpremi+a.upahpenalty+a.rupiahpenalty+a.brondolan<>0
				UNION
				SELECT a.notransaksi as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty
					 ,a.insentif as premirawat
					 ,0 as premitraksi,0 as dendatraksi,0 as premiabsensi,0 as dendaabsensi,0 as insentif,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".kebun_kehadiran_vw a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.insentif<>0
				UNION
				SELECT a.notransaksi as transaksi,a.tanggal,a.idkaryawan,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat
					 ,a.premi as premitraksi,a.penalty as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".vhc_runhk a
				LEFT JOIN ".$dbname.".datakaryawan b on a.idkaryawan = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premi+a.penalty<>0
				UNION
				SELECT CONCAT('Absensi ',if(isnull(a.penjelasan),'',a.penjelasan)) as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan
					 ,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,a.premi as premiabsensi,a.insentif,a.penaltykehadiran as dendaabsensi
					 ,0 as premipengawas,0 as premiandor
				FROM ".$dbname.".sdm_absensidt a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premi+a.insentif+a.penaltykehadiran<>0
				UNION
				SELECT 'Pengawas' as transaksi,CONCAT(a.periodegaji,'-01') as tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan
					 ,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premiandor
					 ,a.jumlah as premipengawas
				FROM ".$dbname.".sdm_gaji a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.idkomponen = '16' and a.periodegaji='".$periode."' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.jumlah<>0
				UNION
				SELECT 'Mandor' as transaksi,a.tanggal,a.karyawanid,b.nik,b.namakaryawan,c.namajabatan,d.tipe as tipekaryawan,b.subbagian
					 ,0 as hasilkerja,0 as hasilkerjakg,0 as norma,0 as premicapaibasis,0 as premioverbasis,0 as premibasis,0 as brondolan
					 ,0 as brondolanpremi,0 as upahpenalty,0 as rupiahpenalty,0 as premirawat,0 as premitraksi,0 as dendatraksi
					 ,0 as premiabsensi,0 as insentif,0 as dendaabsensi,0 as premipengawas
					 ,a.premiinput as premiandor
				FROM ".$dbname.".kebun_premikemandoran a
				LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid 
				LEFT JOIN ".$dbname.".sdm_5jabatan c on b.kodejabatan= c.kodejabatan
				LEFT JOIN ".$dbname.".sdm_5tipekaryawan d on b.tipekaryawan = d.id 
				WHERE a.posting='1' and a.tanggal like '".$periode."%' and b.lokasitugas like '".substr($kdUnit,0,4)."%'".$whr."
					  and a.premiinput<>0
				";
	}
	//exit('Warning : '.$kdUnit.' - '.$periode.' - '.$idkary.' - '.$tipe.' - '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	if($_GET['type']!='excel'){
		$stream="<table class=sortable border=0 cellspacing=1>";
	}else{
		$stream="<table class=sortable border=1 cellspacing=1>";
	}
	$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=left>Transaksi</td>
          <td align=center>Tanggal</td>
          <td align=center>NIK</td>
          <td align=left>Nama Karyawan</td>
          <td align=left>Jabatan</td>
          <td align=left>Level</td>
          <td align=left>Sub Bagian</td>
          <td align=center>Hasil Jjg</td>
          <td align=center>Hasil Kg</td>
          <td align=center>Brondol Kg</td>
          <td align=center>Capai Basis</td>
          <td align=center>Over Basis</td>
          <td align=center>Premi Brondol</td>
          <td align=center>Denda Panen</td>
          <td align=center>Penalty</td>
          <td align=center>Premi Rawat</td>
          <td align=center>Premi Traksi</td>
          <td align=center>Denda Traksi</td>
          <td align=center>Premi Absen</td>
          <td align=center>Insentif Absen</td>
          <td align=center>Denda Absen</td>
          <td align=center>Premi Pengawas</td>
          <td align=center>Premi Mandor</td>
          <td align=center>Total</td>
        </tr>
      </thead>
      <tbody>";
        if($row==0){
            $stream.="<tr class=rowcontent>";
            $stream.="<td colspan=4>Data not found...!</td>";
            $stream.="</tr>";
        }else{
			$jumlah=0;
			while($barz=mysql_fetch_object($resz)){
				$no+=1;
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=left>".$barz->transaksi."</td>";
                $stream.="<td align=center>".$barz->tanggal."</td>";
                $stream.="<td align=center>".$barz->nik."</td>";
                $stream.="<td align=left>".$barz->namakaryawan."</td>";
                $stream.="<td align=left>".$barz->namajabatan."</td>";
                $stream.="<td align=left>".$barz->tipekaryawan."</td>";
                $stream.="<td align=left>".$barz->subbagian."</td>";
	            $stream.="<td align=right>".number_format($barz->hasilkerja,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->hasilkerjakg,2)."</td>";
	            $stream.="<td align=right>".number_format($barz->brondolan,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premicapaibasis,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premioverbasis,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->brondolanpremi,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->upahpenalty,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->rupiahpenalty,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premirawat,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premitraksi,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->dendatraksi,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premiabsensi,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->insentif,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->dendaabsensi,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premipengawas,0)."</td>";
	            $stream.="<td align=right>".number_format($barz->premimandor,0)."</td>";
				$gthk+=$barz->hasilkerja;
				$gtkg+=$barz->hasilkerjakg;
				$gtbd+=$barz->brondolan;
				$gtcb+=$barz->premicapaibasis;
				$gtob+=$barz->premioverbasis;
				$gtbp+=$barz->brondolanpremi;
				$gtup+=$barz->upahpenalty;
				$gtrp+=$barz->rupiahpenalty;
				$gtpr+=$barz->premirawat;
				$gtpt+=$barz->premitraksi;
				$gtdt+=$barz->dendatraksi;
				$gtpa+=$barz->premiabsensi;
				$gtia+=$barz->insentif;
				$gtda+=$barz->dendaabsensi;
				$gtpp+=$barz->premipengawas;
				$gtpm+=$barz->premimandor;
				$ttjm=$barz->premicapaibasis+$barz->premioverbasis+$barz->brondolanpremi-$barz->upahpenalty-$barz->rupiahpenalty+$barz->premirawat
					  +$barz->premitraksi-$barz->dendatraksi+$barz->premiabsensi+$barz->insentif-$barz->dendaabsensi+$barz->premipengawas+$barz->premimandor;
				$gtjm+=$ttjm;
	            $stream.="<td align=right>".number_format($ttjm,0)."</td>";
                $stream.="</tr>";
			}
			$stream.="</tbody><thead><tr>";
			$stream.="<td align=center colspan=7>Total</td>";
			$stream.="<td align=right>".number_format($gthk,0)."</td>";
			$stream.="<td align=right>".number_format($gtkg,2)."</td>";
			$stream.="<td align=right>".number_format($gtbd,0)."</td>";
			$stream.="<td align=right>".number_format($gtcb,0)."</td>";
			$stream.="<td align=right>".number_format($gtob,0)."</td>";
			$stream.="<td align=right>".number_format($gtbp,0)."</td>";
			$stream.="<td align=right>".number_format($gtup,0)."</td>";
			$stream.="<td align=right>".number_format($gtrp,0)."</td>";
			$stream.="<td align=right>".number_format($gtpr,0)."</td>";
			$stream.="<td align=right>".number_format($gtpt,0)."</td>";
			$stream.="<td align=right>".number_format($gtdt,0)."</td>";
			$stream.="<td align=right>".number_format($gtpa,0)."</td>";
			$stream.="<td align=right>".number_format($gtia,0)."</td>";
			$stream.="<td align=right>".number_format($gtda,0)."</td>";
			$stream.="<td align=right>".number_format($gtpp,0)."</td>";
			$stream.="<td align=right>".number_format($gtpm,0)."</td>";
			$stream.="<td align=right>".number_format($gtjm,0)."</td>";
			$stream.="</tr></thead></tbody>";
		}
   $stream.="</tbody></table>";
   echo $stream;
?>
