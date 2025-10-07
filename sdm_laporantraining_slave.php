<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$kodedept=checkPostGet('kodedept','');
	$idkary=checkPostGet('idkary','');
	$kodejenis=checkPostGet('kodejenis','');
	$tgl_1=checkPostGet('tgl_1','');
	$tgl_2=checkPostGet('tgl_2','');

	#Filter parameter where 
	$where="";
	$whr1="";
	$whr2="";
	$whr3="";
	$namapt='';
	if($kodept!=''){
		$where.=" and c.induk='".$kodept."'";
		$whr1.=" and c.induk='".$kodept."'";
		$whr2.=" and c.induk='".$kodept."'";
		$whr3.=" and c.induk='".$kodept."'";
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$namapt=$d['namaorganisasi'].'<br>';
		}
	}
	if($kodeunit!=''){
		$where.=" and c.kodeorganisasi='".$kodeunit."'";
		$whr2.=" and c.kodeorganisasi='".$kodeunit."'";
		$whr3.=" and c.kodeorganisasi='".$kodeunit."'";
	}
	if($kodedept!=''){
		$where.=" and b.bagian='".$kodedept."'";
		$whr3.=" and b.bagian='".$kodedept."'";
	}
	if($idkary!=''){
		$where=" and a.karyawanid='".$idkary."'";
	}
	if($kodejenis!=''){
		$where.=" and a.jenistraining='".$kodejenis."'";
	}
	if ($tgl_1<>"" AND $tgl_2==""){
		$tgl_1=tanggalsystem($tgl_1);
		$where.=" AND a.tanggalmulai='".$tgl_1."'";
	}
	if ($tgl_1=="" AND $tgl_2<>""){
		$tgl_2=tanggalsystem($tgl_2);
		$where.=" AND a.tanggalselesai = '".$tgl_2."'";
	}
	if ($tgl_1<>"" AND $tgl_2<>""){
		$tgl_1=tanggalsystem($tgl_1);
		$tgl_2=tanggalsystem($tgl_2);
		$where.=" AND a.tanggalmulai >= '".$tgl_1."' AND a.tanggalselesai <= '".$tgl_2."'";
	}

	switch($proses){
		case 'getUnit':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sUnit="select DISTINCT(if(b.subbagian='',b.lokasitugas,left(subbagian,4))) as kodeorganisasi,c.namaorganisasi 
						from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where true ".$whr1." 
						order by c.namaorganisasi";
				//$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kodept."' order by namaorganisasi";
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
						where true ".$whr1." 
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where true ".$whr1." 
						order by b.namakaryawan";
			}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sUnit="select DISTINCT(if(b.subbagian='',b.lokasitugas,left(subbagian,4))) as kodeorganisasi,c.namaorganisasi 
						from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where c.tipe<>'HOLDING' ".$whr1." 
						order by c.namaorganisasi";
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
						where c.tipe<>'HOLDING' ".$whr1." 
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where c.tipe<>'HOLDING' ".$whr1." 
						order by b.namakaryawan";
			}else{
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
						order by b.namakaryawan";
			}
			//exit('Warning: '.$sKary);
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$rUnit['kodeorganisasi'].">".$rUnit['namaorganisasi']."</option>";
			}

			$qDept=mysql_query($sDept) or die(mysql_error($conn));
			$optDept="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rDept=mysql_fetch_assoc($qDept)){
				$optDept.="<option value=".$rDept['kode'].">".$rDept['nama']."</option>";
			}

			$qKary=mysql_query($sKary) or die(mysql_error($conn));
			$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKary=mysql_fetch_assoc($qKary)){
				$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." - [".$rKary['nik']."]</option>";
			}

			echo $optUnit."###".$optDept."###".$optKary;
			break;

		case 'getDept':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
						where true ".$whr2." 
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where true ".$whr2." 
						order by b.namakaryawan";
			}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
						where c.tipe<>'HOLDING' ".$whr2." 
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where c.tipe<>'HOLDING' ".$whr2." 
						order by b.namakaryawan";
			}else{
				$sDept="select DISTINCT(b.bagian) as kode,d.nama from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
						order by d.nama";
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
						order by b.namakaryawan";
			}
			//exit('Warning: '.$sDept);
			$qDept=mysql_query($sDept) or die(mysql_error($conn));
			$optDept="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rDept=mysql_fetch_assoc($qDept)){
				$optDept.="<option value=".$rDept['kode'].">".$rDept['nama']."</option>";
			}

			$qKary=mysql_query($sKary) or die(mysql_error($conn));
			$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKary=mysql_fetch_assoc($qKary)){
				$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." - [".$rKary['nik']."]</option>";
			}

			echo $optDept."###".$optKary;
			break;

		case 'getKary':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where true ".$whr3." 
						order by b.namakaryawan";
			}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
						where c.tipe<>'HOLDING' ".$whr3." 
						order by b.namakaryawan";
			}else{
				$sKary="select DISTINCT(a.karyawanid) as karyawanid,b.namakaryawan,b.nik,b.lokasitugas from ".$dbname.".sdm_karyawantraining a 
						LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
						where if(b.subbagian='',b.lokasitugas,left(subbagian,4))='".$_SESSION['empl']['lokasitugas']."'
						order by b.namakaryawan";
			}
			//exit('Warning: '.$sKary);
			$qKary=mysql_query($sKary) or die(mysql_error($conn));
			$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKary=mysql_fetch_assoc($qKary)){
				$optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']." - [".$rKary['nik']."]</option>";
			}

			echo $optKary;
			break;

		default:
			break;
	}

	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td ".$bgclr.">No</td>
			<td ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['bagian']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['nik']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['namakaryawan']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jabatan']."</td>
			<td ".$bgclr.">Judul Training</td>
			<td ".$bgclr.">".$_SESSION['lang']['jenis']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggalmulai']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggalselesai']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['penyelenggara']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['sertifikat']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['biaya']."</td>
		</tr></thead><tbody>";
	#ambil data 
	$str = "select a.*,if(b.subbagian='',b.lokasitugas,left(subbagian,4)) as unit,b.nik,b.namakaryawan,d.nama as namabagian,e.namajabatan,f.jenistraining as kategori
			from ".$dbname.".sdm_karyawantraining a 
			LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(subbagian,4))
			LEFT JOIN ".$dbname.".sdm_5departemen d on d.kode=b.bagian
			LEFT JOIN ".$dbname.".sdm_5jabatan e on e.kodejabatan=b.kodejabatan
			LEFT JOIN ".$dbname.".sdm_5jenistraining f on f.kodetraining=a.jenistraining
			where true ".$where." 
			order by if(b.subbagian='',b.lokasitugas,left(subbagian,4)),d.nama,b.namakaryawan,a.tanggalmulai
			";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$unit="";
	$no=0;
	$ttbiaya=0;
	$gtbiaya=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		$stream.="<tr class=rowcontent>";
		/*
		if($unit!=$bar->unit){
			if($no!=1){
				$stream.="
					<td bgcolor='#DCDCDC' colspan=12 align='center'>Sub Total</td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($ttbiaya,0)."</td>
				$stream.="</tr><tr class=rowcontent>";
				$ttbiaya=0;
			}
			$stream.="
				<td align='center'>".$no."</td>
				<td>".$bar->nokontrak."</td>
				<td>".$bar->nodo."</td>
				<td align='right'>".@number_format($bar->qty,0)."</td>";
			$jmlkontrak=$bar->qty;
			$sisa=$bar->qty;
			$gtjmlkontrak+=$bar->qty;
		}else{
			$stream.="<td colspan=4></td>";
		}
		*/
		$stream.="
				<td align='center'>".$no."</td>
				<td align='center'>".$bar->unit."</td>
				<td>".$bar->namabagian."</td>
				<td align='center'>".$bar->nik."</td>
				<td>".$bar->namakaryawan."</td>
				<td>".$bar->namajabatan."</td>
				<td>".$bar->judultraining."</td>
				<td>".$bar->kategori."</td>
				<td align='center'>".$bar->tanggalmulai."</td>
				<td align='center'>".$bar->tanggalselesai."</td>
				<td>".$bar->penyelenggara."</td>
				<td align='center'>".($bar->sertifikat=='0' ? 'Tidak' : 'Ya')."</td>
				<td align='right'>".@number_format($bar->biaya,0)."</td>";
		$stream.="</tr>";
		$unit=$bar->unit;
		$ttbiaya+=$bar->biaya;
		$gtbiaya+=$bar->biaya;
	}
	if($unit!=''){
		/*
		$stream.="<tr class=rowcontent>
			<td bgcolor='#DCDCDC' colspan=12 align='center'>Sub Total</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($ttbiaya,2)."</td>";
		$stream.="</tr>";
		*/
		$stream.="<thead class=rowheader>
			<td bgcolor='#DCDCDC' colspan=12 align='center'>Grand Total</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($gtbiaya,0)."</td>";
		$stream.="</thead>";
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
          echo $stream;
        break;
        case 'excel':
			$judul='Rekap Training';
			$stream='<h2>'.$namapt.$judul.'</h2>'.$stream;
		    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $nop_=$judul.'_'.date("YmdHis");
            if(strlen($stream)>0){
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
				echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls.gz';
					  </script>";
            }
		break;
	}    
?>