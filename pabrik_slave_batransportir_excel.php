<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method=$_POST['method'];
$kodeorg=$_POST['kodeorg'];
$notransaksi=$_POST['notransaksi'];
$nospp=$_POST['nospp'];
$trpcode=$_POST['trpcode'];
if($method=='')$method=$_GET['method'];
if($kodeorg=='')$kodeorg=$_GET['kodeorg'];
if($notransaksi=='')$notransaksi=$_GET['notransaksi'];
if($nospp=='')$nospp=$_GET['nospp'];
if($trpcode=='')$trpcode=$_GET['trpcode'];
$vw=checkPostGet('vw','');
switch ($method){
	case 'dataDetail':
		$kodeCust='';
		$namaSupp='';
		$iCust="select kodetimbangan,namasupplier from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['kodetimbangan'];
			$namaSupp=$dCust['namasupplier'];
		}
		if($kodeCust!=''){
			$iSPP="select a.* from ".$dbname.".pabrik_timbangan a where a.nosipb='".$nospp."' and a.kodecustomer='".$kodeCust."' and intex='0' order by a.tanggal";
			$nSPP=  mysql_query($iSPP) or die (mysql_error($conn));
			$tab2.="<h2>LAPORAN PENGIRIMAN</h2>";
			$tab2.='No SPP/DO : '.$nospp.'<BR>';
			$tab2.='Transportir : '.$namaSupp;
			$tab2.="<table class=sortable border=1 cellspacing=1>
					<thead>
						<tr class=rowheader>
							<td>No</td>
							<td>No. Ticket</td>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>".$_SESSION['lang']['NoKontrak']."</td>
							<td>".$_SESSION['lang']['nosipb']."</td>
							<td>".$_SESSION['lang']['nokendaraan']."</td>
							<td>".$_SESSION['lang']['supir']."</td>
							<td>".$_SESSION['lang']['beratMasuk']."</td>
							<td>".$_SESSION['lang']['beratKeluar']."</td>
							<td>".$_SESSION['lang']['beratBersih']."</td>
						</tr>
					</thead>
					<tbody>";
			$no=0;
			while($dSPP=mysql_fetch_assoc($nSPP)){
				$masuk+=$dSPP['beratmasuk'];
				$keluar+=$dSPP['beratkeluar'];
				$netto+=$dSPP['beratbersih'];
				$no+=1;
				$tab2.="<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$dSPP['notransaksi']."</td>
							<td>".tanggalnormal($dSPP['tanggal'])."</td>
							<td>".$dSPP['nokontrak']."</td>
							<td>".$dSPP['nosipb']."</td>
							<td>".$dSPP['nokendaraan']."</td>
							<td>".$dSPP['supir']."</td>
							<td align=right>".number_format($dSPP['beratmasuk'],0,'.',',')."</td>
							<td align=right>".number_format($dSPP['beratkeluar'],0,'.',',')."</td>
							<td align=right>".number_format($dSPP['beratbersih'],0,'.',',')."</td>
						</tr>";
			}
			$tab2.="<tr class=rowcontent>
						<td bgcolor='#FEDEFE' colspan=7>Total</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($masuk,0,'.',',')."</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($keluar,0,'.',',')."</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($netto,0,'.',',')."</td>
					</tr>";
			$tab2.="</tbody></table>";

		}
		//=================================================
		$tab2.="</table>Print Time : ".date('Y-m-d H:i:s')."<br>By : ".$_SESSION['empl']['name'];
		$time=date("YmdHis");
		$nop_="Laporan_Pengiriman_".$time;
		/*
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
					 gzwrite($gztralala, $tab2);
					 gzclose($gztralala);
		echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
		*/

		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
			}	
			closedir($handle);
		}
		$handle=fopen("tempExcel/".$nop_.".xls",'w');
		if(!fwrite($handle,$tab2)){
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

 		break;
	default :
		break;
}
?>
