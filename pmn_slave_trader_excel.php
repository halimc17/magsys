<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method=$_POST['method'];
$kdUnitCr=$_POST['kdUnitCr'];
$kdCustCr=$_POST['kdCustCr'];
$kdBrgCr=$_POST['kdBrgCr'];
$noKontrakCr=$_POST['noKontrakCr'];
if($method=='')$method=$_GET['method'];
if($kdUnitCr=='')$kdUnitCr=$_GET['kdUnitCr'];
if($kdCustCr=='')$kdCustCr=$_GET['kdCustCr'];
if($kdBrgCr=='')$kdBrgCr=$_GET['kdBrgCr'];
if($noKontrakCr=='')$noKontrakCr=$_GET['noKontrakCr'];
switch ($method){
	case 'dataDetail':
		$whrd="";
		if($kdUnitCr!=''){
			$whrd.=" and a.kodeorg='".$kdUnitCr."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$whrd.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe<>'HOLDING' and detail='1')";
			}else{
				if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
					$whrd.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
				}
			}
		}
		if($kdCustCr!=''){
			$whrd.=" and a.kodecustomer='".$kdCustCr."'";
		}
		if($kdBrgCr!=''){
			$whrd.=" and a.kodebarang='".$kdBrgCr."'";
		}
		if($noKontrakCr!=''){
			$whrd.=" and a.nokontrakext='".$noKontrakCr."'";
		}
		$stream.="<h2>KONTRAK EXTERNAL</h2>
		<table border=1>
			<thead><tr class=rowheader>
				<td bgcolor=#DEDEDE>No</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['unit']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['vendor']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['tanggal']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['namabarang']."</td>
				<td bgcolor=#DEDEDE align=right>".$_SESSION['lang']['jumlah']."</td>
				<td bgcolor=#DEDEDE align=right>".$_SESSION['lang']['harga']."</td>
				<td bgcolor=#DEDEDE align=right>".$_SESSION['lang']['nilai']."</td>
				<td bgcolor=#DEDEDE align=right>".$_SESSION['lang']['ppn']."</td>
				<td bgcolor=#DEDEDE align=right>".$_SESSION['lang']['total']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['induk']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['catatan']."</td>
			</tr></thead>
			<body>";
		$str2="select a.*,b.namabarang,c.namacustomer from ".$dbname.".pmn_traderht a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang 
				left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer where true ".$whrd."
				order by a.kodeorg,a.nokontrakext";
        //exit('Warning : '.$str2);
		$res2=mysql_query($str2);
		while($bar2=mysql_fetch_assoc($res2)){
			$no+=1;
			$stream.="<tr>
						<td>".$no."</td>
						<td>".$bar2['kodeorg']."</td>
						<td>".$bar2['kodecustomer']."</td>
						<td>".$bar2['nokontrakext']."</td>
						<td>".$bar2['tanggalext']."</td>
						<td>".$bar2['namabarang']."</td>
						<td align=right>".number_format($bar2['qtykontrak'],0)."</td>
						<td align=right>".number_format($bar2['hargaext'],2)."</td>
						<td align=right>".number_format($bar2['nilaikontrakext'],2)."</td>
						<td align=right>".number_format($bar2['nilaippnext'],2)."</td>
						<td align=right>".number_format($bar2['nilaikontrakext']+$bar['nilaippnext'],2)."</td>
						<td>".$bar2['nokontrakpembanding']."</td>
						<td>".$bar2['catatan']."</td>
					</tr>";
		}
		$stream.="</tbody>";
		//=================================================
		$stream.="</table>Print Time : ".date('Y-m-d H:i:s')."<br>By : ".$_SESSION['empl']['name'];
		$time=date("Hms");
		$nop_="Kontrak_External_".$time;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
					 gzwrite($gztralala, $stream);
					 gzclose($gztralala);
		echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
 		break;
	default :
		break;
}
?>
