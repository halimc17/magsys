<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$proses=checkPostGet('proses','');
	$type=checkPostGet('type','');
	if($type=='' and $proses!=''){
		$type=$proses;
	}
	$karyawanid=checkPostGet('karyawanid','');
	$namakaryawan=checkPostGet('namakaryawan','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	$pekerjaanx=checkPostGet('pekerjaanx','');
	$atasanx=checkPostGet('atasanx','');
	$stskerja=checkPostGet('stskerja','');
	$stsposting=checkPostGet('stsposting','');
	$periode=substr($tanggal,0,7);
	$whr="";
	$periodesd="";
	if($karyawanid!=''){
		$whr.=" and a.karyawanid='".$karyawanid."'";
		$sKary="select a.karyawanid,a.nik,a.namakaryawan,b.nama as namabagian,c.namajabatan from ".$dbname.".datakaryawan a
				left join ".$dbname.".sdm_5departemen b on b.kode=a.bagian
				left join ".$dbname.".sdm_5jabatan c on c.kodejabatan=a.kodejabatan
				where a.karyawanid='".$karyawanid."'";
		$qKary=mysql_query($sKary);
		while($rKary=mysql_fetch_object($qKary)){
			$nik=$rKary->nik;
			$namakaryawan=$rKary->namakaryawan;
			$namabagian=$rKary->namabagian;
			$namajabatan=$rKary->namajabatan;
		}
	}
	if($tanggal1!='' and $tanggal2=='' ){
		$whr.=" and a.tanggal='".$tanggal1."'";
		$periodesd="<br>Periode : ".tanggalnormal($tanggal1);
	}
	if($tanggal1=='' and $tanggal2!='' ){
		$whr.=" and a.tanggal='".$tanggal1."'";
		$periodesd="<br>Periode : ".tanggalnormal($tanggal2);
	}
	if($tanggal1!='' and $tanggal2!='' ){
		$whr.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$periodesd="<br>Periode : ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal1);
	}
	if($pekerjaanx!=''){
		$whr.=" and a.pekerjaan like '%".$pekerjaanx."%'";
	}
	if($atasanx!=''){
		$whr.=" and a.atasan = '".$atasanx."'";
	}
	if($stskerja!=''){
		$whr.=" and a.stspekerjaan = '".$stskerja."'";
	}
	if($stsposting!=''){
		if($stsposting=='1'){
			$whr.=" and a.posting = '1'";
		}else{
			$whr.=" and a.posting <> '1'";
		}
	}
	$strz="select a.*,b.nik,b.namakaryawan,c.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
				left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				left join ".$dbname.".datakaryawan c on c.karyawanid=a.atasan
				where true ".$whr."
				order by a.karyawanid,a.tanggal,a.nomor";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	if($proses!='preview'){
		$stream2="<center><h3>"."PENCAPAIAN DAN TARGET PEKERJAAN";
		$stream2.=$periodesd."</h3></center>";
		if($karyawanid!=''){
			$stream2.="<table>
							<tr>
								<td colspan=2>Nama</td>
								<td>: ".$namakaryawan."</td>
							</tr>
							<tr>
								<td colspan=2>NIK</td>
								<td>: ".$nik."</td>
							</tr>
							<tr>
								<td colspan=2>Departemen</td>
								<td>: ".$namabagian."</td>
							</tr>
							<tr>
								<td colspan=2>Jabatan</td>
								<td>: ".$namajabatan."</td>
							</tr>
						</table>";
		}
	}
	if($type=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent align=center>
				<td width='2%' align=center>No</td>
				<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['pekerjaan']."</td>
				<td ".$bgclr.">Target</td>
				<td ".$bgclr.">".$_SESSION['lang']['aktual']."</td>
				<td ".$bgclr.">Correction Action</td>
				<td ".$bgclr.">Rencana Kerja</td>
				<td ".$bgclr.">".$_SESSION['lang']['catatan']."</td>
				<td ".$bgclr.">".$_SESSION['lang']['atasan']."</td>
				<td ".$bgclr." width='50px'>".$_SESSION['lang']['status']."</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($rowz==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=4>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		$no=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$barz->tanggal."</td>
						<td>".$barz->pekerjaan."</td>
						<td>".$barz->target."</td>
						<td>".$barz->aktual."</td>
						<td>".$barz->correction."</td>
						<td>".$barz->rencanakerja."</td>
						<td>".$barz->catatan."</td>
						<td>".$barz->namaatasan."</td>
						<td>".$barz->stspekerjaan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($type!='excel'){
		echo $stream2;
	}else{
		$nop_="Pencapaian_Pekerjaan_".$kdOrg."_".$periode."__".date("His");
        if(strlen($stream2)>0){
			if($karyawanid!=''){
				$stream2.="<br><table border=1>
							<tr>
								<td colspan=2 align=center>Dibuat</td>
							</tr>
							<tr>
								<td colspan=2 rowspan=3></td>
							</tr>
							<tr>
							</tr>
							<tr>
							</tr>
							<tr>
								<td colspan=2 align=center>".$namakaryawan."</td>
							</tr>
						</table>";
			}
			//$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
            //gzwrite($gztralala, $stream2);
			//gzclose($gztralala);
			// echo "<script language=javascript1.2>
			//    window.location='tempExcel/".$nop_.".xls.gz';
			//    </script>";
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream2)){
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
	}   
?>
