<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodeorg=checkPostGet('kdOrg','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));

	if($proses=='preview' or $proses=='excel'){
		if($kodeorg==''){
			exit('Warning : Pabrik tidak boleh kosong...!');
		}
	}
	#Filter parameter where 
	$where="True";
	if($kodeorg!=''){
		$where.=" and a.millcode = '".$kodeorg."'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$tanggal1=substr($tanggal1,0,4)."-".substr($tanggal1,4,2)."-".substr($tanggal1,6,2);
		$tanggal2=substr($tanggal2,0,4)."-".substr($tanggal2,4,2)."-".substr($tanggal2,6,2);
		$where.=" and a.tanggal>='".$tanggal1.' 00:00:00'."' and a.tanggal<='".$tanggal2.' 23:59:59'."'";
	}
	$optInduk=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$kodept=$optInduk[$kodeorg];
	$namapt=$optNm[$kodept];
	//exit('Warning: '.$where);
	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	$stream.="<table cellspacing='1' border='".$brd."' class='sortable'>";
    $stream.="
        <thead class=rowheader>
			<tr class=rowcontent>
				<td width='2%' align=center>No</td>
				<td width='3%' align=center>".$_SESSION['lang']['pabrik']."</td>
				<td width='7%' align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['beratBersih']."</td>
				<td width='8%' align=center>".$_SESSION['lang']['nokendaraan']."</td>
				<td align=center>".$_SESSION['lang']['supir']."</td>
				<td align=center>No. BA</td>
				<td width='5%' align=center>".$_SESSION['lang']['noTiket']."</td>
				<td align=center>".$_SESSION['lang']['alasanDtolak']."</td>
				<td width='7%' align=center>".$_SESSION['lang']['ongkoskirim']."</td>
	        </tr>
		</thead><tbody>";

	#ambil data Outspec
	$str="select a.*,b.namabarang,e.namacustomer,f.namabarang as komoditi,g.namasupplier as pengangkut
			from ".$dbname.".pabrik_outspec a 
			left join ".$dbname.".pmn_kontrakjual d on d.nokontrak=a.nokontrak
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			left join ".$dbname.".pmn_4customer e on e.kodecustomer=d.koderekanan
			left join ".$dbname.".log_5masterbarang f on f.kodebarang=a.kodebarangkirim
			left join ".$dbname.".log_5supplier g on g.kodetimbangan=a.customerkirim
			where ".$where." 
			order by a.millcode,a.tanggal,a.notransaksi,a.kodebarang";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$rnum=mysql_num_rows($res);
	if($rnum==0){
		exit('Warning: Data not found...!');
	}
	$no=0;
	$gtongkoskirim=0;
	$gtberatbersih=0;
	while($bar=mysql_fetch_object($res)){
		$drcl="title='Click untuk cetak detail.' style='cursor: pointer' onclick=\"preview_BAPDF('".$bar->millcode."','".$bar->notransaksi."','".$bar->kodebarang."','".$bar->namabarang."','preview',event);\"";
		$no+=1;
		$stream.="<tr class=rowcontent>
						<td ".$drcl." align=center>".$no."</td>
						<td ".$drcl." align=center>".$bar->millcode."</td>
						<td ".$drcl." align=center>".$bar->notransaksi."</td>
						<td ".$drcl." align=center>".substr($bar->tanggal,0,10)."</td>
						<td ".$drcl." align=left>".$bar->namabarang."</td>
						<td ".$drcl." align=right>".number_format($bar->beratbersih,0,'.',',')."</td>
						<td ".$drcl." align=left>".$bar->nokendaraan."</td>
						<td ".$drcl." align=left>".$bar->supir."</td>
						<td ".$drcl." align=left>".$bar->noba."</td>
						<td ".$drcl." align=center>".$bar->notiket."</td>
						<td ".$drcl." align=left>".$bar->alasan."</td>
						<td ".$drcl." align=right>".number_format($bar->ongkoskirim,0,'.',',')."</td>
					</tr>";
		$gtberatbersih+=$bar->beratbersih;
		$gtongkoskirim+=$bar->ongkoskirim;
	}
	$stream.="<tr class=rowcontent>
					<td ".$drcl." colspan=5 align=center>Total</td>
					<td ".$drcl." align=right>".number_format($gtberatbersih,0,'.',',')."</td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=right>".number_format($gtongkoskirim,0,'.',',')."</td>
				</tr>";
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Data Retur Dan Outspec';
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.'<br>'.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
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
