<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=checkPostGet('pt','');
if(isset($_GET['proses']))
{
    $proses=$_GET['proses'];
}
else
{   
    $proses=$_POST['proses'];
}

switch($proses)
{
  case'getDetailBASPK':
    $nokontrak=$_GET['nokontrak'];
    $nodo=$_GET['nodo'];
	$arrd="nokontrak##".$nokontrak."##nodo##".$nodo;	  

    echo"<link rel=stylesheet type=text/css href=style/generic.css>
        <script language=javascript1.2 src='js/generic.js'></script>
        <script language=javascript1.2 src='js/kebun_panen.js'></script>";
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
	//echo"<img onclick=zExcelDetailBASPK(event,'pmn_slave_2rekappenjualandetail.php','".$arrd."') src=images/excel.jpg class=resicon title='MS.Excel'> ";
    echo"<input type='hidden' id='nokontrak' value='".$nokontrak."' /><input type='hidden' id='nodo' value='".$nodo."' />
        <table class=sortable cellpadding=1 border=0>
        <thead>
        <tr class=rowheader>
        <td>No.</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['NoKontrak']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=right>".$_SESSION['lang']['jumlah']."</td>
        <td align=right>".$_SESSION['lang']['biaya']."</td>
        <td align=right>".$_SESSION['lang']['ppn']."</td>    
        <td align=right>".$_SESSION['lang']['total']."</td>
        <td align=right>".$_SESSION['lang']['rpperkg']."</td>
        </tr></thead><tbody>
        ";
    
    $sBASPK="select p.nodo,p.tanggaldo,p.nokontrak,p.nokontrakinternal,p.qty,r.jumlahrealisasippn as jumlahrealisasippn,r.hasilkerjarealisasi as hasilkerjarealisasi
			,r.notransaksi,r.jumlahrealisasi,r.jmlppn
			from ".$dbname.".pmn_suratperintahpengiriman p LEFT JOIN
				(select s.notransaksi,s.keterangan,t.hasilkerjajumlah,if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0) as jmlppn
						,sum(v.jumlahrealisasi) as jumlahrealisasi,sum(v.hasilkerjarealisasi) as hasilkerjarealisasi,v.statusjurnal
						,(sum(v.jumlahrealisasi)+if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0)) as jumlahrealisasippn
				from ".$dbname.".log_spkht s
				LEFT JOIN (select * from ".$dbname.".log_spkdt where kodekegiatan='811010201') t on s.notransaksi=t.notransaksi
				LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun='1160100') u on s.notransaksi=u.notransaksi
				LEFT JOIN ".$dbname.".log_baspk v on s.notransaksi=v.notransaksi
				where v.statusjurnal=1 and s.keterangan<>'' and t.hasilkerjajumlah>0
				GROUP BY s.notransaksi) r on p.nodo=r.keterangan
				where p.nokontrak='".$nokontrak."'
			";
	$qBASPK=mysql_query($sBASPK) or die(mysql_error());
    $no=0;
    if(mysql_num_rows($qBASPK)<1)
    {
            echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
		while($rBASPK=mysql_fetch_assoc($qBASPK))
        {
           $no+=1;
            echo"<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rBASPK['notransaksi']."</td>
                <td>".$rBASPK['nokontrak']."</td>
                <td>".$rBASPK['nodo']."</td>
                <td>".$rBASPK['tanggaldo']."</td>
                <td align=right>".number_format($rBASPK['hasilkerjarealisasi'],2)."</td>
                <td align=right>".number_format($rBASPK['jumlahrealisasi'],2)."</td>
                <td align=right>".number_format($rBASPK['jmlppn'],2)."</td>
                <td align=right>".number_format($rBASPK['jumlahrealisasippn'],2)."</td>
                <td align=right>";
				if($rBASPK['hasilkerjarealisasi']>0){
					echo number_format($rBASPK['jumlahrealisasippn']/$rBASPK['hasilkerjarealisasi'],2);
				}else{
					echo number_format(0,2);
				}
				"</td>
                </tr>";
                $tothasilkerjarealisasi	+=$rBASPK['hasilkerjarealisasi'];
                $totjumlahrealisasi		+=$rBASPK['jumlahrealisasi'];
                $totjmlppn				+=$rBASPK['jmlppn'];
                $totjumlahrealisasippn	+=$rBASPK['jumlahrealisasippn'];
        }

        echo"<tr class=rowcontent>
				<td colspan=5 align=center>Total</td>
				<td align=right>".number_format($tothasilkerjarealisasi,2)."</td>
				<td align=right>".number_format($totjumlahrealisasi,2)."</td>
				<td align=right>".number_format($totjmlppn,2)."</td>
				<td align=right>".number_format($totjumlahrealisasippn,2)."</td>
				<td align=right>";
				if($tothasilkerjarealisasi>0){
					echo number_format($totjumlahrealisasippn/$tothasilkerjarealisasi,2);
				}else{
					echo number_format(0,2);
				}
			"</td>
			</tr>";
        echo"</tbody></table></fieldset>";
    }
    break;
  case'excelDetailBASPK':
    $nokontrak=$_GET['nokontrak'];
    $nodo=$_GET['nodo'];
/*
	echo"<link rel=stylesheet type=text/css href=style/generic.css>
        <script language=javascript1.2 src='js/generic.js'></script>
        <script language=javascript1.2 src='js/kebun_panen.js'></script>";
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
	echo"<br /><img onclick=zExcelDetailBASPK(event,'pmn_slave_2rekappenjualandetail.php') src=images/excel.jpg class=resicon title='MS.Excel'> ";
    echo"<input type='hidden' id='nokontrak' value='".$nokontrak."' /><input type='hidden' id='nodo' value='".$nodo."' />
*/
	$tab="<table><font size='5'><b>".$_SESSION['lang']['rekap'].' '.$_SESSION['lang']['penjualan']."</b></font></table>";
	$tab.="
		<table class=sortable cellpadding=1 border=0>
        <thead>
        <thead>
        <tr class=rowheader>
        <td>No.</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['NoKontrak']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=right>".$_SESSION['lang']['jumlah']."</td>
        <td align=right>".$_SESSION['lang']['biaya']."</td>
        <td align=right>".$_SESSION['lang']['ppn']."</td>    
        <td align=right>".$_SESSION['lang']['total']."</td>
        <td align=right>".$_SESSION['lang']['rpperkg']."</td>
        </tr></thead><tbody>
        ";
    
    $sBASPK="select p.nodo,p.tanggaldo,p.nokontrak,p.nokontrakinternal,p.qty,r.jumlahrealisasippn as jumlahrealisasippn,r.hasilkerjarealisasi as hasilkerjarealisasi
			,r.notransaksi,r.jumlahrealisasi,r.jmlppn
			from ".$dbname.".pmn_suratperintahpengiriman p LEFT JOIN
				(select s.notransaksi,s.keterangan,t.hasilkerjajumlah,if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0) as jmlppn
						,sum(v.jumlahrealisasi) as jumlahrealisasi,sum(v.hasilkerjarealisasi) as hasilkerjarealisasi,v.statusjurnal
						,(sum(v.jumlahrealisasi)+if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0)) as jumlahrealisasippn
				from ".$dbname.".log_spkht s
				LEFT JOIN (select * from ".$dbname.".log_spkdt where kodekegiatan='811010201') t on s.notransaksi=t.notransaksi
				LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun='1160100') u on s.notransaksi=u.notransaksi
				LEFT JOIN ".$dbname.".log_baspk v on s.notransaksi=v.notransaksi
				where v.statusjurnal=1 and s.keterangan<>'' and t.hasilkerjajumlah>0
				GROUP BY s.notransaksi) r on p.nodo=r.keterangan
				where p.nokontrak='".$nokontrak."'
			";
	$qBASPK=mysql_query($sBASPK) or die(mysql_error());
    $no=0;
    if(mysql_num_rows($qBASPK)<1)
    {
           $tab.="<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
		while($rBASPK=mysql_fetch_assoc($qBASPK))
        {
           $no+=1;
           $tab.="<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rBASPK['notransaksi']."</td>
                <td>".$rBASPK['nokontrak']."</td>
                <td>".$rBASPK['nodo']."</td>
                <td>".$rBASPK['tanggaldo']."</td>
                <td align=right>".number_format($rBASPK['hasilkerjarealisasi'],2)."</td>
                <td align=right>".number_format($rBASPK['jumlahrealisasi'],2)."</td>
                <td align=right>".number_format($rBASPK['jmlppn'],2)."</td>
                <td align=right>".number_format($rBASPK['jumlahrealisasippn'],2)."</td>
                <td align=right>";
				if($rBASPK['hasilkerjarealisasi']>0){
					$tab.=number_format($rBASPK['jumlahrealisasippn']/$rBASPK['hasilkerjarealisasi'],2);
				}else{
					$tab.=number_format(0,2);
				}
				"</td>
                </tr>";
                $tothasilkerjarealisasi	+=$rBASPK['hasilkerjarealisasi'];
                $totjumlahrealisasi		+=$rBASPK['jumlahrealisasi'];
                $totjmlppn				+=$rBASPK['jmlppn'];
                $totjumlahrealisasippn	+=$rBASPK['jumlahrealisasippn'];
        }

        $tab.="<tr class=rowcontent>
				<td colspan=5 align=center>Total</td>
				<td align=right>".number_format($tothasilkerjarealisasi,2)."</td>
				<td align=right>".number_format($totjumlahrealisasi,2)."</td>
				<td align=right>".number_format($totjmlppn,2)."</td>
				<td align=right>".number_format($totjumlahrealisasippn,2)."</td>
				<td align=right>";
				if($tothasilkerjarealisasi>0){
					$tab.=number_format($totjumlahrealisasippn/$tothasilkerjarealisasi,2);
				}else{
					$tab.=number_format(0,2);
				}
			"</td>
			</tr>";
        $tab.="</tbody>";
    }
		/*
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="</table>Print Time:".date('d-m-Y H:i:s')."<br />By:".$_SESSION['empl']['name'];	
			
			$nop_="Detail_BA_Transporter_".date('d-m-Y H:i:s');
			if(strlen($tab)>0)
			{
			if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
			@unlink('tempExcel/'.$file);
			}
			}	
			closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab))
			{
			echo "<script language=javascript1.2>
			parent.window.alert('Can't convert to excel format');
			</script>";
			exit;
			}
			else
			{
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls';
			</script>";
			}
			//closedir($handle);
			}
		*/
			$tab.="</tbody></table>";
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
			$nop_="Detail_Penjualan_".$dte;
			$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gztralala, $tab);
			gzclose($gztralala);
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";	
    break;    
    default:
        break;
}

?>
