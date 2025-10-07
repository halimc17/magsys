<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodeunit=checkPostGet('kodeunit','');
	$kodecust=checkPostGet('kodecust','');
	$kodebarang=checkPostGet('kodebarang','');
	$noinvoice=checkPostGet('noinvoice','');
	$nokontrak=checkPostGet('nokontrak','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	if($proses=='preview' or $proses=='excel'){
		if($kodeunit==''){
			//exit('Warning : Unit tidak boleh kosong...!');
		}
	}

	#Filter parameter where 
	$where="";
	if($kodeunit!=''){
		$where.=" and b.kodeorg='".$kodeunit."'";
	}
	if($kodecust!=''){
		$where.=" and b.kodecustomer='".$kodecust."'";
	}
	if($kodebarang!=''){
		$where.=" and a.kodebarang = '".$kodebarang."'";
	}
	if($noinvoice!=''){
		$where.=" and a.noinvoice like '%".$noinvoice."%'";
	}
	if($nokontrak!=''){
		$where.=" and a.nokontrak like '%".$nokontrak."%'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$where.=" and b.tanggal>='".$tanggal1."' and b.tanggal<='".$tanggal2."'";
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
			<td width='3%' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td width='12%' ".$bgclr.">".$_SESSION['lang']['noinvoice']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width='25%' ".$bgclr.">".$_SESSION['lang']['nmcust']."</td>
			<td width='25%' ".$bgclr.">".$_SESSION['lang']['namabarang']."</td>
			<td width='3%' ".$bgclr.">".'Sat'."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['jumlah']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['harga']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['nilai']."</td>
		</tr>
		</thead><tbody>";

	#ambil data
	$str="select a.*,b.kodeorg,b.kodept,b.kodeorg,b.tanggal,c.namacustomer,d.namabarang,d.satuan from ".$dbname.".keu_penagihandt a
			left join ".$dbname.".keu_penagihanht b on b.noinvoice=a.noinvoice
			left join ".$dbname.".pmn_4customer c on c.kodecustomer=b.kodecustomer
			left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
			where true ".$where." 
			ORDER BY b.kodeorg,b.tanggal,a.noinvoice,a.nokontrak,b.kodecustomer,a.kodebarang
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$no=0;
	$nu=0;
	$kdunit='';
	$stjml=0;
	$stnilai=0;
	$gtjml=0;
	$gtnilai=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		if($kodebarang!=''){
			if($no!=1 and substr($bar->kodeorg,0,4)!=$kdunit){
				$nu+=1;
				$stharga=($stjml==0 ? 0 : $stnilai/$stjml);
				$stream.="<tr class=rowcontent>";
				$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kdunit."</td>
							<td bgcolor='#DEDEDE' align='right'>".number_format($stjml,2)."</td>
							<td bgcolor='#DEDEDE' align='right'>".number_format($stharga,2)."</td>
							<td bgcolor='#DEDEDE' align='right'>".number_format($stnilai,2)."</td>
						";
				$stream.="</tr>";
				$stjml=0;
				$stnilai=0;
			}
		}
		$stream.="<tr class=rowcontent>";
		$stream.="
				<td align='center'>".substr($bar->kodeorg,0,4)."</td>
				<td align='left'>".$bar->noinvoice."</td>
				<td align='left'>".$bar->nokontrak."</td>
				<td align='center'>".$bar->tanggal."</td>
				<td align='left'>".$bar->namacustomer."</td>
				<td align='left'>".$bar->namabarang."</td>
				<td align='left'>".$bar->satuan."</td>
				<td align='right'>".number_format($bar->nilaiinventory,2)."</td>
				<td align='right'>".number_format($bar->hargasatuan,0)."</td>
				<td align='right'>".number_format($bar->nilaitransaksi,2)."</td>
				";
		$stream.="</tr>";
		$kdunit=substr($bar->kodeorg,0,6);
		$stjml+=$bar->nilaiinventory;
		$stnilai+=$bar->nilaitransaksi;
		$gtjml+=$bar->nilaiinventory;
		$gtnilai+=$bar->nilaitransaksi;
	}
	if($kodebarang!=''){
		$stharga=($stjml==0 ? 0 : $stnilai/$stjml);
		$stream.="<tr class=rowcontent>";
		$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kdunit."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stjml,2)."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stharga,2)."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stnilai,2)."</td>
				";
		$stream.="</tr>";
	}
	if($no>0){
		$gtharga=($gtjml==0 ? 0 : $gtnilai/$gtjml);
		$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
		if($kodebarang!=''){
			$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Grand Total</td>
						<td bgcolor='#DEDEDE' align='right'>".number_format($gtjml,2)."</td>
						<td bgcolor='#DEDEDE' align='right'>".number_format($gtharga,2)."</td>
						<td bgcolor='#DEDEDE' align='right'>".number_format($gtnilai,2)."</td>
					";
		}else{
			$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Grand Total</td>
						<td bgcolor='#DEDEDE' align='right'></td>
						<td bgcolor='#DEDEDE' align='right'></td>
						<td bgcolor='#DEDEDE' align='right'>".number_format($gtnilai,2)."</td>
					";
		}
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Adjustment '.$_SESSION['lang']['panen'];
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				//	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			    //    gzwrite($gztralala, $stream);
				//    gzclose($gztralala);
				//	echo "<script language=javascript1.2>
				//			window.location='tempExcel/".$nop_.".xls.gz';
				//		  </script>";
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

		default:
		break;
	}    
?>
