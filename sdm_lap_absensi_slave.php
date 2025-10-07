<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$karyawanid=checkPostGet('karyawanid','');
	$tipekaryawan=checkPostGet('tipekaryawan','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			//exit('Warning : PT tidak boleh kosong...!');
		}
	}
	$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
	$namapt=$optNm[$kodept];
	#Filter parameter where 
	$where="True";
	if($kodept!=''){
		$where.=" and c.kodeorganisasi='".$kodept."'";
	}
	if($kodeunit!=''){
		$where.=" and c.lokasitugas='".$kodeunit."'";
	}
	if($karyawanid!=''){
		$where.=" and a.karyawanid = '".$karyawanid."'";
	}
	if($tipekaryawan!=''){
		$where.=" and c.tipekaryawan = '".$tipekaryawan."'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
	}

	switch($proses){
		case 'getUnit':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' order by namaorganisasi";
			}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' and kodeorganisasi not like '%HO' order by namaorganisasi";
			}else{
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
						and detail='1' order by namaorganisasi";
			}
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			}else{
				$optUnit="";
			}
			while($dUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;
			break;

		case 'getKary':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
						where kodeorganisasi like '".$kodept."%' and lokasitugas like '".$kodeunit."%' and tipekaryawan like '".$tipekaryawan."%' 
						order by namakaryawan";
				//$sKary="select distinct a.karyawanid,b.namakaryawan,b.nik from ".$dbname.".sdm_absensidt a
				//		left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				//		where b.kodeorganisasi like '".$kodept."%' and b.lokasitugas like '".$kodeunit."%' and b.tipekaryawan like '".$tipekaryawan."%' 
				//		order by namakaryawan";
			}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
						where kodeorganisasi like '".$kodept."%' and lokasitugas like '".$kodeunit."%' and lokasitugas not like '%HO' 
						and tipekaryawan like '".$tipekaryawan."%' order by namakaryawan";
				//$sKary="select distinct a.karyawanid,b.namakaryawan,b.nik from ".$dbname.".sdm_absensidt a
				//		left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				//		where b.kodeorganisasi like '".$kodept."%' and b.lokasitugas like '".$kodeunit."%' and b.lokasitugas not like '%HO' 
				//		and b.tipekaryawan like '".$tipekaryawan."%' order by namakaryawan";
			}else{
				$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
						where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan like '".$tipekaryawan."%' order by namakaryawan";
				//$sKary="select distinct a.karyawanid,b.namakaryawan,b.nik from ".$dbname.".sdm_absensidt a
				//		left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				//		where b.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and b.tipekaryawan like '".$tipekaryawan."%' order by namakaryawan";
			}
			//exit('Warning: '.$sKary);
			$qKary=mysql_query($sKary) or die(mysql_error($conn));
			$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKary=mysql_fetch_assoc($qKary)){
				$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." [".$rKary['nik']."]</option>";
			}
			echo $optKary;
			exit;
			break;
	}

	#ambil data Absensi
	$strz="select a.*,b.namaorganisasi,c.nik,c.namakaryawan,d.keterangan as ket_absensi
			from ".$dbname.".sdm_absensidt a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			left join ".$dbname.".datakaryawan c on c.karyawanid=a.karyawanid
			left join ".$dbname.".sdm_5absensi d on d.kodeabsen=a.absensi
			where ".$where." 
			order by a.tanggal,a.kodeorg,a.karyawanid";
	//exit('Warning: '.$strz);
	#preview: nampilin header ================================================================================
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream="";
	if($proses=='excel'){
		$stream.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream.="
			<thead>
				<tr class=rowheader>
					<td align=center style='width:50px;'>No</td>
					<td align=center style='width:140px;'>".$_SESSION['lang']['nik']."</td>
					<td align=center style='width:300px;'>".$_SESSION['lang']['namakaryawan']."</td>
					<td align=center style='width:115px;'>".$_SESSION['lang']['tanggal']."</td>
					<td align=center style='width:60px;'>Shift</td>
					<td align=center style='width:105px;'>".$_SESSION['lang']['absensi']."</td>
					<td align=center style='width:115px;'>".$_SESSION['lang']['jammasuk']."</td>
					<td align=center style='width:115px;'>".$_SESSION['lang']['jamPlg']."</td>
					<td align=center style='width:300px;'>".$_SESSION['lang']['keterangan']."</td>
				</tr>
			</thead>
			<tbody>";
	if($rowz==0){
		$stream.="<tr class=rowcontent>";
		$stream.="	<td colspan=15>Tidak ada Data...!</td>";
		$stream.="</tr>";
	}else{
		$no=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
            $stream.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$barz->nik."</td>
						<td align=left>".$barz->namakaryawan."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>
						<td align=center>".($barz->shift=='' ? 1 : $barz->shift)."</td>
						<td align=center>".$barz->ket_absensi."</td>
						<td align=center>".$barz->jam."</td>
						<td align=center>".$barz->jamPlg."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul=$_SESSION['lang']['laporan'].' Absensi';
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.'<BR>'.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				//	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			    //    gzwrite($gztralala, $stream);
				//    gzclose($gztralala);
				//	echo "<script language=javascript1.2>
				//			window.location='tempExcel/".$nop_.".xls.gz';
				//		  </script>";
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
            }
		break;

		default:
		break;
	}    
?>
