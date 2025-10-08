<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$carikodeorg=checkPostGet('carikodeorg','');
	$carinamainv=checkPostGet('carinamainv','');
	$carikaryawan=checkPostGet('carikaryawan','');
	$cariruangan=checkPostGet('cariruangan','');
	$where="True";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where="a.kodeorg not like '%HO' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
	}else{
		$where="a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	if($carikodeorg!=''){
		$where.=" and a.kodeorg='".$carikodeorg."'";
	}
	if($carinamainv!=''){
		$where.=" and (a.namainventaris like '%".$carinamainv."%' or a.kodeinventaris like '%".$carinamainv."%')";
	}
	if($carikaryawan!=''){
		//$where.=" and a.nik='".$carikaryawan."'";
		$where.=" and a.namakaryawan like '%".$carikaryawan."%'";
	}else{
		if($_GET['type']=='excel'){
			exit('Warning: Nama karyawan belum dipilh...!');
		}
	}
	if($cariruangan!=''){
		$where.=" and a.ruangan like '%".$cariruangan."%'";
	}
	$strz ="select a.namakaryawan,a.lokasitugas,b.nama as departemen,d.namajabatan,c.namaorganisasi,c.wilayahkota from ".$dbname.".datakaryawan a 
			left join ".$dbname.".sdm_5departemen b on b.kode=a.bagian
			left join ".$dbname.".sdm_5jabatan d on d.kodejabatan=a.kodejabatan
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorganisasi
			where a.nik='".$carikaryawan."'";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	while($barz=mysql_fetch_object($resz)){
		$namakaryawan=$barz->namakaryawan;
		$lokasitugas=$barz->lokasitugas;
		$departemen=$barz->departemen;
		$namajabatan=$barz->namajabatan;
		$namaorganisasi=$barz->namaorganisasi;
		$wilayahkota=ucwords(strtolower($barz->wilayahkota));
	}
	//$strz ="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
	//		left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
	//		left join ".$dbname.".datakaryawan d on d.nik=a.nik
	//		where ".$where." 
	//		order by a.kodeorg,a.kodebarang,a.kodeinventaris";
	$strz ="select a.*,c.namasupplier as supplier from ".$dbname.".log_invbarang a 
			left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
			where ".$where." 
			order by a.kodeorg,a.kodebarang,a.kodeinventaris";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=0 cellspacing=1>
					<tr>
						<td colspan=12 align=center><h2>"."DAFTAR PEMINJAMAN INVENTARIS PERUSAHAAN"."</h2></td>
					</tr>
					<tr>
						<td colspan=12></td>
					</tr>
					<tr>
						<td colspan=2>PT</td>
						<td colspan=10>: ".$namaorganisasi."</td>
					</tr>
					<tr>
						<td colspan=2>Unit Kerja / Lokasi </td>
						<td colspan=8>: ".$lokasitugas."</td>
						<td colspan=1>User</td>
						<td colspan=1>: ".$namakaryawan."</td>
					</tr>
					<tr>
						<td colspan=2>Departemen</td>
						<td colspan=10>: ".$namajabatan."</td>
					</tr>
					<tr>
						<td colspan=12></td>
					</tr>
					<tr>
						<td colspan=12>Telah dilakukan inventarisasi Asset ".$namaorganisasi." (Medco Agro) sebagai sarana penunjang kerja dengan rincian berikut ini :</td>
					</tr>
					<tr>
						<td colspan=12></td>
					</tr>
				</table>";
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td rowspan=2 width='2%' align=center valign=center>No</td>
				<td rowspan=2 width='20%' align=center>".$_SESSION['lang']['namabarang']."</td>
				<td rowspan=2 width='20%' align=center>Merk / Type</td>
				<td rowspan=2 width='3%' align=center>Tahun</td>
				<td rowspan=2 width='5%' align=left>".$_SESSION['lang']['kodebarang']."</td>
				<td rowspan=2 width='5%' align=center>Kode Asset GA</td>
				<td colspan=3 width='9%' align=center>Kondisi</td>
				<td rowspan=2 width='10%' align=center>No PO</td>
				<td rowspan=2 width='6%' align=center>Tgl Terima</td>
				<td rowspan=2 width='20%'  align=center>".$_SESSION['lang']['keterangan']."</td>
			</tr>
			<tr class=rowcontent>
				<td bgcolor='lime' width='3%' align=center>Baik</td>
				<td bgcolor='yellow' width='3%' align=center>Layak</td>
				<td bgcolor='red' width='3%' align=center>Rusak</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			$namakaryawan=$barz->namakaryawan;
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=left>".$barz->namainventaris."</td>
						<td align=left>".$barz->merkinventaris.' '.$barz->tipeinventaris.' '.$barz->ukuran.' '.$barz->warna."</td>
						<td align=center>".(substr($barz->tglperolehan,0,4)=='0000' ? '****' : substr($barz->tglperolehan,0,4))."</td>
						<td align=center>".$barz->kodebarang."</td>
						<td align=center>".$barz->kodeinventaris."</td>";
			if($barz->kondisi=='Baik'){
				$stream2.=" <td bgcolor='lime' align=center>&radic;</td>
							<td align=center></td>
							<td align=center></td>";
			}else if($barz->kondisi=='Cukup' or $barz->kondisi=='Layak'){
				$stream2.=" <td align=center></td>
							<td bgcolor='yellow' align=center>&radic;</td>
							<td align=center></td>";
			}else if($barz->kondisi=='Rusak'){
				$stream2.=" <td align=center></td>
							<td align=center></td>
							<td bgcolor='red' align=center>&radic;</td>";
			}
			$stream2.=" <td align=left>".$barz->nopo."</td>
						<td align=center>".$barz->tgluserterima."</td>
						<td align=left>".$barz->ketinventaris."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
		$stream2.="<table class=sortable border=0 cellspacing=1>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td colspan=12>".$wilayahkota.', '.date("d F Y")."</td>
					</tr>
					<tr>
						<td></td>
					</tr>
				</table>
				<table class=sortable border=1 cellspacing=1>
					<tr>
						<td colspan=2 align=center>Dibuat Oleh</td>
						<td colspan=1 align=center>Disetujui Oleh</td>
						<td colspan=3 align=center>Diketahui Oleh</td>
					</tr>
					<tr>
						<td colspan=2 rowspan=4></td>
						<td colspan=1 rowspan=4></td>
						<td colspan=3 rowspan=4></td>
					</tr>
					<tr>
					</tr>
					<tr>
					</tr>
					<tr>
					</tr>
					<tr>
						<td colspan=2 align=center>GA Staff</td>
						<td colspan=1 align=center>".$departemen."</td>
						<td colspan=3 align=center>GA manager</td>
					</tr>
				</table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Inventaris_Karyawan_".$namakaryawan."_".date("His");
        if(strlen($stream2)>0){
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
