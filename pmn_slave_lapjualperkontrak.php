<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$kdUnit=$_POST['kdUnit'];
	$kdCust=$_POST['kdCust'];
	$periode=$_POST['periode'];
	$kdBrg=$_POST['kdBrg'];
	$nokontrak=$_POST['nokontrak'];
	if($kdUnit=='')$kdUnit=$_GET['kdUnit'];
	if($kdCust=='')$kdCust=$_GET['kdCust'];
	if($periode=='')$periode=$_GET['periode'];
	if($kdBrg=='')$kdBrg=$_GET['kdBrg'];
	if($nokontrak=='')$nokontrak=$_GET['nokontrak'];
	if($proses=='')$proses=$_GET['proses'];

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdUnit."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdUnit;

	#Filter parameter where 
	$whr1.="true";
	$whr2.="true";
	$stream="";
	$judul="Laporan Penjualan Per Kontrak";
	if($kdUnit!=''){
		$whr1.=" and millcode = '".$kdUnit."'";
		//$judul.="_".$kdUnit;
	}
	if($kdCust!=''){
		$whr2.=" and b.koderekanan = '".$kdCust."'";
		//$judul.="_".$kdCust;
	}
	if($periode!=''){
		$whr1.=" and tanggal like '".$periode."%'";
		//$judul.="_".$periode;
	}
	if($kdBrg!=''){
		$whr1.=" and kodebarang = '".$kdBrg."'";
		//$judul.="_".$kdBrg;
	}
	if($nokontrak!=''){
		$whr1.=" and nokontrak like '%".$nokontrak."%'";
		//$judul.="_".$nokontrak;
	}
	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td width=2% ".$bgclr.">No</td>
			<td width=4% ".$bgclr.">".$_SESSION['lang']['periode']."</td>
			<td width=30% ".$bgclr.">".$_SESSION['lang']['Pembeli']."</td>
			<td width=8% ".$bgclr.">".$_SESSION['lang']['jumlah']." (Kg)"."</td>
			<td width=5% ".$bgclr.">".$_SESSION['lang']['rpperkg']."</td>
			<td width=13% ".$bgclr.">".$_SESSION['lang']['jumlahrp']."</td>
			<td width=5% ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width=13% ".$bgclr.">".$_SESSION['lang']['noinvoice']." (Kg)"."</td>
			<td width=20% ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
		</tr></thead><tbody>";
	#ambil data kontrak external
	$str = "select '".$periode."' as periode,b.koderekanan,c.namacustomer,sum(a.beratbersih) as qtykg
					,round(if(b.ppn=1,b.hargasatuan/1.1,b.hargasatuan),2) as hargakg
					,round(sum(a.beratbersih)*if(b.ppn=1,b.hargasatuan/1.1,b.hargasatuan),2) as jmlharga
					,max(a.tanggal) as tglkirim,d.noinvoice,a.nokontrak 
			from (select * from ".$dbname.".pabrik_timbangan where ".$whr1." and nokontrak<>'') a
			LEFT JOIN ".$dbname.".pmn_kontrakjual b on b.nokontrak=a.nokontrak
			LEFT JOIN ".$dbname.".pmn_4customer c on c.kodecustomer=b.koderekanan
			LEFT JOIN (select nokontrak,noinvoice,min(tanggal) as tglinv from ".$dbname.".keu_penagihanht GROUP BY nokontrak) d on d.nokontrak=a.nokontrak
			where ".$whr2."
			GROUP BY a.nokontrak
			";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$no=0;
	$gt_qtykg=0;
	$gt_jmlharga=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td align='center'>".$bar->periode."</td>
					<td>".$bar->namacustomer."</td>
					<td align='right'>".@number_format($bar->qtykg,0)."</td>
					<td align='right'>".@number_format($bar->hargakg,2)."</td>
					<td align='right'>".@number_format($bar->jmlharga,2)."</td>
					<td align='center'>".tanggalnormal($bar->tglkirim)."</td>
					<td>".$bar->noinvoice."</td>
					<td>".$bar->nokontrak."</td>";
		$stream.="</tr>";
		$gt_qtykg+=$bar->qtykg;
		$gt_jmlharga+=$bar->jmlharga;
	}
	$stream.="<tr class=rowcontent>
				<td bgcolor='#FEDEFE' colspan=3 align='center'>Total</td>
				<td bgcolor='#FEDEFE' align='right'>".@number_format($gt_qtykg,0)."</td>
				<td bgcolor='#FEDEFE' align='right'>".@number_format($gt_jmlharga/$gt_qtykg,2)."</td>
				<td bgcolor='#FEDEFE' align='right'>".@number_format($gt_jmlharga,2)."</td>
				<td bgcolor='#FEDEFE' colspan=3></td>
			</tr>";
	$stream.="</tbody></table>";

	switch($proses){
        case 'preview':
          echo $stream;
        break;
        case 'excel':
            if(strlen($stream)>0){
				$stream="<h2>".$judul."</h2>".$stream;
				$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
				$nop_="Laporan_Penjualan_Per_Kontrak_".date("YmdHis");
				/*
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
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
				if(!fwrite($handle,$stream)){
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
		break;
	}    
?>