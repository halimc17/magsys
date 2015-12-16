<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$comId = checkPostGet('comId','');
$kdVhc = checkPostGet('kdVhc','');
$jnsVhc = checkPostGet('jnsVhc','');
$alokasi = checkPostGet('alokasi','');
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir',''));
$where2=' kelompokbarang=351';
$optBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',$where2);	

	switch($proses)
	{
		case'getJnsVhc':
		$optOrg=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
		$optJnsvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sjnsVhc="select distinct jenisvhc from ".$dbname.".vhc_runht where kodeorg='".substr($comId,0,4)."' group by jenisvhc"; //echo "warning:".$sjnsVhc;
		$qjnsVhc=mysql_query($sjnsVhc) or die(mysql_error());
		while($rjnsVhc=mysql_fetch_assoc($qjnsVhc))
		{
//			$sJhvc="select from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$rjnsVhc['jenisvhc']."'";
//			$qjhvc=mysql_query($sJhvc) or die(mysql_error());
//			$rjhvc=mysql_fetch_assoc($qjhvc);
		$optJnsvhc.="<option value='".$rjnsVhc['jenisvhc']."'>".$optOrg[$rjnsVhc['jenisvhc']]."</option>";
		}
		echo $optJnsvhc;
		break;
		
		case'getKdvhc':
		$optKvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$skdVhc="select kodevhc from ".$dbname.".vhc_runht where jenisvhc='".$jnsVhc."' group by kodevhc"; //echo "warning:".$skdVhc;
		$qkdVhc=mysql_query($skdVhc) or die(mysql_error());
		while($rkdVhc=mysql_fetch_assoc($qkdVhc))
		{
		$optKvhc.="<option value='".$rkdVhc['kodevhc']."'>".$rkdVhc['kodevhc']."</option>";
		}
		echo $optKvhc;
		break;
		
		case'get_result':
            if($comId=='')
            {
                echo"warning:Unit Tidak Boleh Kosong";
                exit();
            }
			 if($jnsVhc=='')
            {
                echo"warning:Jenis Kendaraan Tidak Boleh Kosong";
                exit();
            }
			 if($kdVhc=='')
            {
                echo"warning:Kode Kendaraan Tidak Boleh Kosong";
                exit();
            }
            if($tglAkhir==''||$tglAwal='')
            {
                echo"warning:Tanggal Tidak Boleh Kosong";
                exit();
            }
			
			$sql="select a.notransaksi,a.tanggal, a.tanggalkeluar, a.kodevhc, a.downtime, 
				a.kmmasuk, a.kmkeluar, a.kerusakan, a.terlambat, c.namajenisvhc from ".$dbname.".vhc_penggantianht a
				left join ".$dbname.".vhc_5master b
				on a.kodevhc = b.kodevhc 
				left join ".$dbname.".vhc_5jenisvhc c 
				on b.jenisvhc = c.jenisvhc 
				where a.tanggal between '".$tglAwal."' and '".$tglAkhir."' 
				and a.kodevhc = '".$kdVhc."' 
				order by a.notransaksi asc";

			echo"
				<table cellspacing=1 border=0 class=sortable>
				<thead>
					<tr class=rowheader>
						<td>No.</td>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['tanggalmasuk']." </td>
						<td align=center>".$_SESSION['lang']['tanggalkeluar']." </td>
						<td align=center>".$_SESSION['lang']['jenisvch']."</td>
						<td align=center>".$_SESSION['lang']['kodevhc']."</td>
						<td align=center>".$_SESSION['lang']['downtime']."(jam)"."</td>
						<td align=center>KM/HM Masuk</td>
						<td align=center>KM/HM Keluar</td>
						<td align=center>".$_SESSION['lang']['descDamage']."</td>
						<td align=center>Alasan Terlambat</td>
					</tr>
				</thead>
				<tbody>";
                
			$qRvhc=mysql_query($sql) or die(mysql_error());
                        $old='';
			while($res=mysql_fetch_assoc($qRvhc))
			{
				$no+=1;
                echo"<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$res['notransaksi']."</td>
					<td>".tanggalnormal($res['tanggal'])."</td>
					<td>".tanggalnormal($res['tanggalkeluar'])."</td>
					<td>".$res['namajenisvhc']."</td>
					<td>".$res['kodevhc']."</td>
					<td>".$res['downtime']."</td>
					<td>".$res['kmmasuk']."</td>
					<td>".$res['kmkeluar']."</td>
					<td>".$res['kerusakan']."</td>
					<td>".$res['terlambat']."</td>";
			}			
			echo"</tbody></table>";
		
		break;
		
		case'getResultKry':
		$sRvhc="select a.*,b.jenispekerjaan,b.jumlahrit,b.keterangan from ".$dbname.".vhc_runht 
		a inner join ".$dbname.".vhc_rundt b on a.notransaksi=b.notransaksi 
		inner join ".$dbname.".vhc_runhk c on b.notransaksi=c.notransaksi 
		where c.idkaryawan='".$kryId."' order by a.tanggal asc"; 
		//echo "warning:".$sRvhc;
		$qRvhc=mysql_query($sRvhc) or die(mysql_error());
		while($rRvhc=mysql_fetch_assoc($qRvhc))
		{
		$no+=1;
		echo"
		<tr class=rowcontent>
			<td>".$no."</td>
			<td align=center>".$rRvhc['notransaksi']."</td>
			<td align=center>".tanggalnormal($rRvhc['tanggal'])."</td>
			<td align=center>".$rRvhc['kmhmawal']."</td>
			<td align=center>".$rRvhc['kmhmakhir']."</td>
			<td align=center>".$rRvhc['jumlah']."</td>
			<td align=center>".$rRvhc['jenispekerjaan']."</td>
			<td align=center>".$rRvhc['keterangan']."</td>
			<td align=center>".$rRvhc['jumlahrit']."</td>
			<td align=center>".$rRvhc['jlhbbm']."</td>
		</tr>
		";
		}
		break;
		
		case'excel':
			if($comId=='')
            {
                echo"warning:Unit Tidak Boleh Kosong";
                exit();
            }
			 if($jnsVhc=='')
            {
                echo"warning:Jenis Kendaraan Tidak Boleh Kosong";
                exit();
            }
			 if($kdVhc=='')
            {
                echo"warning:Kode Kendaraan Tidak Boleh Kosong";
                exit();
            }
            if($tglAkhir==''||$tglAwal='')
            {
                echo"warning:Tanggal Tidak Boleh Kosong";
                exit();
            }
			
			$sVhc="select a.notransaksi,a.tanggal, a.tanggalkeluar, a.kodevhc, a.downtime, 
				a.kmmasuk, a.kmkeluar, a.kerusakan, a.terlambat, c.namajenisvhc from ".$dbname.".vhc_penggantianht a
				left join ".$dbname.".vhc_5master b
				on a.kodevhc = b.kodevhc 
				left join ".$dbname.".vhc_5jenisvhc c 
				on b.jenisvhc = c.jenisvhc 
				where a.tanggal between '".$tglAwal."' and '".$tglAkhir."' 
				and a.kodevhc = '".$kdVhc."' 
				order by a.notransaksi asc";
				
			$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".substr($comId,0,4)."'";
			$namapt='COMPANY NAME';
				
			$rVhc=mysql_query($sVhc);
			$res=mysql_query($str);
			while($bar=mysql_fetch_object($res)){
				$namapt=strtoupper($bar->namaorganisasi);
			}

			$stream="
			<table>
			<tr><td colspan=15 align=center>".$_SESSION['lang']['breakdown']."/".$_SESSION['lang']['unit']."</td></tr>";
			if($comId!='')
			{
				$stream.="
			<tr><td colspan=6>".$_SESSION['lang']['unit'].":".$namapt."</td></tr>";
			}
			
			$stream.="
			<tr><td colspan=6>".$_SESSION['lang']['periode'].":".$_GET['tglAwal']."-".$_GET['tglAkhir']."</td></tr>";
			
			$stream.="
			<tr><td colspan=6>&nbsp;</td></tr>
			</table>
			<table border=1 bgcolor=#DEDEDE >
			<tr>
				<td>No.</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['tanggalmasuk']." </td>
				<td align=center>".$_SESSION['lang']['tanggalkeluar']." </td>
				<td align=center>".$_SESSION['lang']['jenisvch']."</td>
				<td align=center>".$_SESSION['lang']['kodevhc']."</td>
				<td align=center>".$_SESSION['lang']['downtime']."(jam)"."</td>
				<td align=center>KM/HM Masuk</td>
				<td align=center>KM/HM Keluar</td>
				<td align=center>".$_SESSION['lang']['descDamage']."</td>
				<td align=center>Alasan Terlambat</td>
			</tr>
			</table>						
			";
			
			$stream.="<table border='1'>";
			$no=0;
			$arrPos=array("Sopir","Kondektur");
			while($bVhc=mysql_fetch_assoc($rVhc))
			{
                $no+=1;
                $stream.="
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$bVhc['notransaksi']."</td>
					<td>".tanggalnormal($bVhc['tanggal'])."</td>
					<td>".tanggalnormal($bVhc['tanggalkeluar'])."</td>
					<td>".$bVhc['namajenisvhc']."</td>
					<td>".$bVhc['kodevhc']."</td>
					<td>".$bVhc['downtime']."</td>
					<td>".$bVhc['kmmasuk']."</td>
					<td>".$bVhc['kmkeluar']."</td>
					<td>".$bVhc['kerusakan']."</td>
					<td>".$bVhc['terlambat']."</td>
				</tr>";
			}
		
			$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
			$nop_="BreakdownUnit__".$dte;
			$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gztralala, $stream);
			gzclose($gztralala);
			echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
				</script>";

        break;
		default:
		break;
	}

?>