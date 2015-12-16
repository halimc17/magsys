<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$tanggaldari=checkPostGet('tanggaldari','');
$tanggalsampai=checkPostGet('tanggalsampai','');
$komoditi=checkPostGet('komoditi','');
$penjual=checkPostGet('penjual','');



if($proses=='excel'){
	$border=1;
	$aksi="";
}else{
	$border=0;
	$aksi="<td style='text-align:center;' rowspan=2>".$_SESSION['lang']['action']."</td>";
}

$stream="<table class=sortable cellspacing=1 border='".$border."'>
		<thead>
		<tr class=rowheader>
                    <td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['NoKontrak']."</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['Pembeli']."</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['komoditi']."</td>    
                    <td align=center rowspan=2>".$_SESSION['lang']['nodo']."</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['tanggalsurat']."</td>
                    <td rowspan=2 align=center>Nomor Work Order<br>Transportir</td>    
                    <td align=center rowspan=2>".$_SESSION['lang']['kualitas']."</td>
                    <td align=center colspan=2>".$_SESSION['lang']['kuantitas']."</td>    
                    <td align=center rowspan=2>".$_SESSION['lang']['noberitaacara']."</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['keterangan']."</td>
                    
                    ".$aksi."
                    
                    </tr>
                <tr>
                    <td>FFA (%)</td>
                    <td>M & I (%)</td>
                </tr>
                    
		
		</thead><tbody>";
		

if($komoditi!='')
{
    $sortkomoditi=" and b.kodebarang='".$komoditi."' ";
}


if($penjual!='')
{
    $sortpenjual=" and c.kodecustomer='".$penjual."' ";
}




$strList="select a.*,c.kodecustomer,c.namacustomer,b.kodebarang,d.namabarang,b.kuantitaskontrak,b.ffa,b.mdani from ".$dbname.".pmn_suratperintahpengiriman a
		left join ".$dbname.".pmn_kontrakjual b
		on a.nokontrak = b.nokontrak
		left join ".$dbname.".pmn_4customer c
		on b.koderekanan = c.kodecustomer
		left join ".$dbname.".log_5masterbarang d
		on b.kodebarang = d.kodebarang 
		where a.nodo like '%".$param['nodo']."%' and a.tanggaldo between '".tanggalsystem($tanggaldari)."'
                and '".tanggalsystem($tanggalsampai)."' ".$sortkomoditi." ".$sortpenjual."
		order by a.tanggaldo desc";


$resList=mysql_query($strList) or die(mysql_error($conn));

$nourut=1;
while($barList=mysql_fetch_assoc($resList)){
	$stream.="<tr class=rowcontent>
			<td style='text-align:right;'>".$nourut++."</td>
                                <td>".$barList['nokontrak']."</td>
                                <td>".$barList['namacustomer']."</td>
                                <td>".$barList['namabarang']."</td>    
				<td>".$barList['nodo']."</td>
				<td>".tanggalnormal($barList['tanggaldo'])."</td>
				<td></td>
                                <td align=right>".number_format($barList['kuantitaskontrak'])."</td>
                                <td align=right>".number_format($barList['ffa'],2)."</td>
                                <td align=right>".number_format($barList['mdani'],2)."</td>
                                <td></td>
                                <td>".$barList['keterangan']."</td>";
				
				if($proses=='preview'){
					$stream.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$barList['nodo']."' onclick=\"masterPDF('pmn_2suratperintahpengiriman','".$barList['nodo']."','','pmn_slave_print_pdf_suratperintahpengiriman',event);\" ></td>";
				}
	$stream.="</tr>";
}

$stream.="</tbody>";

switch($proses)
{
    case'preview':
		if($tanggaldari=='' || $tanggalsampai==''){
			echo 'Gagal, Periksa kembali periode tanggal.';
		}else{
			if(tanggalsystem($tanggalsampai) < tanggalsystem($tanggaldari)){
				echo 'Gagal, Periksa kembali periode tanggal.';
			}else{
				echo $stream;
			}
		}
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['suratperintahpengiriman']."_periode_".$tanggaldari."-".$tanggalsampai;
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