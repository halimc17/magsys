<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=$_REQUEST['proses'];
$kodebarang=$_REQUEST['kodebarang'];
$nopo=$_REQUEST['nopo'];
$periode=$_REQUEST['periode'];
$unit=$_REQUEST['unit'];

### begin get nama barang ###
$str2="select namabarang,kodebarang from ".$dbname.".log_5masterbarang";
$res2=mysql_query($str2);
while($bar2=mysql_fetch_object($res2))
{
        $namabarang[$bar2->kodebarang]=$bar2->namabarang;
}
### end get nama barang ###

### begin get tanggal po ###
$str3="select tanggal,nopo from ".$dbname.". log_poht";
$res3=mysql_query($str3);
while($bar3=mysql_fetch_object($res3))
{
        $tanggalpo[$bar3->nopo]=$bar3->tanggal;
}
### end get tanggal po ###

### begin get tanggal transaksi po ###
$str4="select waktutransaksi,notransaksi,kodebarang from ".$dbname.". log_transaksidt";
$res4=mysql_query($str4);
while($bar4=mysql_fetch_object($res4))
{
        $tanggaltransaksi[$bar4->notransaksi][$bar4->kodebarang]=$bar4->waktutransaksi;
}
### end get tanggal transaksi po ###

### BEGIN GET DATA FILTER ###
$str="select * from ".$dbname.".log_transaksi_vw where
		tipetransaksi=1 and
		kodebarang like '%".$kodebarang."%' and
		left(kodebarang,1) = '9' and
		nopo like '%".$nopo."%' and
		tanggal like '%".$periode."%' and
		kodegudang like '%".$unit."%'
	order by tanggal desc";
	
	// print_r($str);
$res=mysql_query($str);
$num_rows=mysql_num_rows($res);
$stream="<table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead class=rowheader>
		<tr>
		<td style='text-align:center;'>No</td>
		<td style='text-align:center;'>".$_SESSION['lang']['kodebarang']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['namabarang']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['satuan']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['nopo']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['tanggalpo']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['tanggalpenerimaan']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['harga']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['jumlah']."</td>
		<td style='text-align:center;'>".$_SESSION['lang']['nilai']."</td>
		</tr>
		</thead>";
$nourut=0;
while($bar=mysql_fetch_object($res))
{
	$nourut+=1;
	$stream.="<tbody><tr class=rowcontent>
		<td style='text-align:right;'>".$nourut."</td>
		<td>".$bar->kodebarang."</td>
		<td>".$namabarang[$bar->kodebarang]."</td>
		<td style='text-align:center;'>".$bar->satuan."</td>
		<td>".$bar->nopo."</td>
		<td>".tanggalnormal($tanggalpo[$bar->nopo])."</td>
		<td>".tanggalnormal($tanggaltransaksi[$bar->notransaksi][$bar->kodebarang])."</td>
		<td style='text-align:right'>".number_format($bar->hargasatuan)."</td>
		<td style='text-align:right'>".$bar->jumlah."</td>
		<td style='text-align:right'>".number_format($bar->hargasatuan*$bar->jumlah)."</td>
		</tr></tbody>
		<tfoot>
		</tfoot>
		";
}

if($num_rows<1){
	echo "<p />Data not Found";
}else{
	switch($proses)
	{
		case 'showdata':
			echo $stream;
		break;
		
		case 'excel':
			$nop_="Laporan Penerimaan Barang Inventaris";
			if(strlen($stream)>0)
			{
				$gzdowmload = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
				gzwrite($gzdowmload, $stream);
				gzclose($gzdowmload);
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
				</script>";
			}
		break;

		default:
			
		break;
	}
}
### END GET DATA FILTER ###

?>