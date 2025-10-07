<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$periode=checkPostGet('periode','');
$komoditi=checkPostGet('komoditi','');

if($komoditi==''){
	$judul='CPO & PK';
}else if($komoditi=='40000001'){
	$judul='CPO';
}else{
	$judul='PK';
}

if($proses=='excel'){
	$border=1;
	$stream="<table><tr>
			<td>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['do']." ".$judul."</td>
			</tr></table>";
}else{
	$border=0;
	$stream='';
}

$stream.="<table class=sortable cellspacing=1 border='".$border."'>
		<thead>
		<tr class=rowheader>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['NoKontrak']."</td>
			<td>".$_SESSION['lang']['nodo']."</td>
			<td>".$_SESSION['lang']['tanggal']." (Mulai pengiriman)</td>
			<td>".$_SESSION['lang']['kuantitas']."</td>
			<td>".$_SESSION['lang']['kualitas']." (FFA%)</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead><tbody>";
		

$strList="select a.*,c.namacustomer,d.namabarang,b.kuantitaskontrak,b.tanggalkirim,b.catatanlain, b.ffa, b.dobi, b.mdani, b.moist, b.dirt from ".$dbname.".pmn_suratperintahpengiriman a
		left join ".$dbname.".pmn_kontrakjual b
		on a.nokontrak = b.nokontrak
		left join ".$dbname.".pmn_4customer c
		on b.koderekanan = c.kodecustomer
		left join ".$dbname.".log_5masterbarang d
		on b.kodebarang = d.kodebarang 
		where b.kodebarang like '%".$komoditi."%' and month(a.tanggaldo) = '".substr($periode,5,2)."' and year(a.tanggaldo) = '".substr($periode,0,4)."' 
		order by a.tanggaldo desc";
$resList=mysql_query($strList) or die(mysql_error($conn));

$nourut=1;
while($barList=mysql_fetch_assoc($resList)){
	$stream.="<tr class=rowcontent>
		<td style='text-align:right; vertical-align:top'>".$nourut++."</td>
			<td style='vertical-align:top'>".$barList['nokontrak']."</td>
			<td style='vertical-align:top'>".$barList['nodo']."</td>
			<td style='vertical-align:top'>".tanggalnormal($barList['tanggalkirim'])."</td>
			<td style='vertical-align:top'>".number_format($barList['kuantitaskontrak'],2)."</td>
			<td style='vertical-align:top; text-align:right;'>".$barList['ffa']."</td>
			<td style='vertical-align:top'>".$barList['keterangan']."</td>
		</tr>";
}
$stream.="</tbody>";

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['suratperintahpengiriman']."_".$periode."-".date('YmdHis');
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