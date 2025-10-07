<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$periode=checkPostGet('periode','');
$periode2=checkPostGet('periode2','');
$lokasitugas=checkPostGet('lokasitugas','');
$namakaryawan=checkPostGet('namakaryawan','');
$tgl1=$periode.'-01';
$tgl2=$periode2.'-31';
if($tgl1>$tgl2){
	exit("Warning : Periode ".$tgl1." lebih besar dari Periode ".$tgl2);
}

$stream="<table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['uangmuka']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['kodebiaya']."</td>
			<td>".$_SESSION['lang']['jenisbiaya']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td align=center>Biaya Tiket</td>
			<td align=center style='text-align:center;'>".$_SESSION['lang']['total']."</td>
		</tr>
		</thead><tbody>";
/*
$strList="select c.namakaryawan,a.notransaksi,b.uangmuka,a.tanggal,a.jenisbiaya,d.keterangan,a.jumlah,b.bytiket,b.sisa from ".$dbname.".sdm_pjdinasdt a
		left join ".$dbname.".sdm_pjdinasht b
		on a.notransaksi=b.notransaksi
		left join ".$dbname.".datakaryawan c
		on b.karyawanid=c.karyawanid
		left join ".$dbname.".sdm_5jenisbiayapjdinas d
		on a.jenisbiaya=d.id
		where b.kodeorg like '%".$lokasitugas."%' and c.namakaryawan like '%".$namakaryawan."%' and month(b.tanggalperjalanan) = '".substr($periode,5,2)."' and year(b.tanggalperjalanan) = '".substr($periode,0,4)."'";

$strList="select c.namakaryawan,a.notransaksi,b.uangmuka,a.tanggal,a.jenisbiaya,d.keterangan,a.jumlah,b.bytiket,b.sisa from ".$dbname.".sdm_pjdinasdt a
		left join ".$dbname.".sdm_pjdinasht b on a.notransaksi=b.notransaksi
		left join ".$dbname.".datakaryawan c on b.karyawanid=c.karyawanid
		left join ".$dbname.".sdm_5jenisbiayapjdinas d on a.jenisbiaya=d.id
		where b.kodeorg like '%".$lokasitugas."%' and c.namakaryawan like '%".$namakaryawan."%' and b.tanggalperjalanan between '".$tgl1."' and '".$tgl2."'
		order by a.notransaksi,a.jenisbiaya";
*/
$strList="select x.* from (
			select c.nik,c.namakaryawan,a.notransaksi,a.uangmuka,a.tanggalperjalanan as tanggal
				,if(ISNULL(b.jenisbiaya),0,b.jenisbiaya) as jenisbiaya,'Uang Muka' as keterangan
				,0 as jumlah,a.bytiket,if(ISNULL(b.jumlahhrd),0,b.jumlahhrd) as sumjmlhrd
			from ".$dbname.".sdm_pjdinasht a
			left join (select DISTINCT(notransaksi) as notransaksi,0 as jenisbiaya,SUM(jumlahhrd) as jumlahhrd 
					   from ".$dbname.".sdm_pjdinasdt GROUP BY notransaksi) b on a.notransaksi=b.notransaksi
			left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
			where a.statushrd='1' and a.kodeorg like '%".$lokasitugas."%' and c.namakaryawan like '%".$namakaryawan."%' and a.tanggalperjalanan between '".$tgl1."' and '".$tgl2."'
			UNION
			select c.nik,c.namakaryawan,a.notransaksi,0 as uangmuka,a.tanggal,a.jenisbiaya,d.keterangan,a.jumlahhrd as jumlah,0 as bytiket,0 as sumjmlhrd
			from ".$dbname.".sdm_pjdinasdt a
			left join ".$dbname.".sdm_pjdinasht b on a.notransaksi=b.notransaksi
			left join ".$dbname.".datakaryawan c on b.karyawanid=c.karyawanid
			left join ".$dbname.".sdm_5jenisbiayapjdinas d on a.jenisbiaya=d.id
			where b.kodeorg like '%".$lokasitugas."%' and c.namakaryawan like '%".$namakaryawan."%' and b.tanggalperjalanan between '".$tgl1."' and '".$tgl2."'
			) x
		order by x.notransaksi,x.jenisbiaya";

//exit('Warning: '.$strList);
$resList=mysql_query($strList) or die(mysql_error($conn));

$oldnotransaksi="";
$nourut=1;
$sumbiaya=0;
while($barList=mysql_fetch_assoc($resList)){
	$newnotransaksi=$barList['notransaksi'];
	if(substr($newnotransaksi,2,2)=='HO'){
		$biaya=$barList['uangmuka']+$barList['sumjmlhrd']+$barList['bytiket'];
	}else{
		$biaya=$barList['sumjmlhrd']+$barList['bytiket'];
	}
	if($newnotransaksi==$oldnotransaksi){
		$stream.="<tr class=rowcontent>
			<td colspan='4'></td>
			<td align=center>".tanggalnormal($barList['tanggal'])."</td>
			<td align=center>".$barList['jenisbiaya']."</td>
			<td>".$barList['keterangan']."</td>
			<td style='text-align:right;'>".number_format($barList['jumlah'],2)."</td>
			<td colspan='2'></td>
		</tr>";
		// $oldnotransaksi=$newnotransaksi;
	}else{
		if($nourut!=1){
			$stream.="<tr class=rowcontent>
						<td colspan='10'>&nbsp;</td>
					</tr>";
		}
		$stream.="<tr class=rowcontent>
			<td style='text-align:center;'>".$nourut++."</td>
				<td>".$barList['namakaryawan']."</td>
				<td>".$barList['notransaksi']."</td>
				<td style='text-align:right;'>".number_format($barList['uangmuka'],2)."</td>
				<td align=center>".tanggalnormal($barList['tanggal'])."</td>
				<td align=center>".$barList['jenisbiaya']."</td>
				<td>".$barList['keterangan']."</td>
				<td style='text-align:right;'>".number_format($barList['jumlah'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['bytiket'],2)."</td>
				<td style='text-align:right;'>".number_format($biaya,2)."</td>
			</tr>";
		$oldnotransaksi=$newnotransaksi;
		//$sumSisa+=$barList['sisa'];
	}
	$sumUangmuka+=$barList['uangmuka'];
	$sumJumlah+=$barList['jumlah'];
	$sumTiket+=$barList['bytiket'];
	$sumbiaya+=$biaya;
}
$stream.="<tr class=rowcontent>
			<td colspan='10'>&nbsp;</td>
		</tr>
		<tr class=rowcontent>
		<td colspan='3' style='font-weight:bold; text-align:center;'>".$_SESSION['lang']['total']."</td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumUangmuka,2)."</td>
		<td colspan='3'></td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumJumlah,2)."</td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumTiket,2)."</td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumbiaya,2)."</td>
		</tr>";

$stream.="</tbody>
		</table>";

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['perjalanandinas']." ".$periode."-".date('YmdHis');
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