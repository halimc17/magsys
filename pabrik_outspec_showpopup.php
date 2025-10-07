<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$caripabrik=checkPostGet('caripabrik','');
	$caribarang=checkPostGet('caribarang','');
	$cariperiode=checkPostGet('cariperiode','');
	$carinotiket=checkPostGet('carinotiket','');
	$where="True";
	if($caripabrik!=''){
		$where.=" and a.millcode='".$caripabrik."'";
	}
	if($caribarang!=''){
		$where.=" and a.kodebarang='".$caribarang."'";
	}
	if($cariperiode!=''){
		$where.=" and left(a.tanggal,7)='".$cariperiode."'";
	}
	if($carinotiket!=''){
		$where.=" and a.notiket like '%".$carinotiket."%'";
	}
	$strz="select a.*,b.namabarang,c.tanggal as tglkirim,c.nokontrak as nokontrakkirim,c.nosipb as nosipbkirim,c.nokendaraan as nokendaraankirim
			,c.supir as supirkirim ,c.beratmasuk as beratmasukkirim,c.jammasuk as jammasukkirim,c.beratkeluar as beratkeluarkirim
			,c.jamkeluar as jamkeluarkirim,c.beratbersih as beratbersihkirim
			,e.namacustomer,f.namabarang as komoditi,g.namasupplier as pengangkut
			from ".$dbname.".pabrik_outspec a 
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			left join ".$dbname.".pabrik_timbangan c on c.notransaksi=a.notiket
			left join ".$dbname.".pmn_kontrakjual d on d.nokontrak=c.nokontrak
			left join ".$dbname.".pmn_4customer e on e.kodecustomer=d.koderekanan
			left join ".$dbname.".log_5masterbarang f on f.kodebarang=c.kodebarang
			left join ".$dbname.".log_5supplier g on g.kodetimbangan=c.kodecustomer
			where ".$where." 
			order by a.millcode,a.tanggal,a.notransaksi,a.kodebarang";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Data Outspec ".$kdOrg."</h3>";
	if($cariperiode!=''){
		$stream2.=" Periode: ".$cariperiode;
	}
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td width='3%' align=center>No</td>
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
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		$drcl='';
		$no=0;
		$gtongkoskirim=0;
		$gtberatbersih=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
			$stream2.="<tr class=rowcontent>
						<td ".$drcl." align=center>".$no."</td>
						<td ".$drcl." align=center>".$barz->millcode."</td>
						<td ".$drcl." align=center>".$barz->notransaksi."</td>
						<td ".$drcl." align=center>".substr($barz->tanggal,0,10)."</td>
						<td ".$drcl." align=left>".$barz->namabarang."</td>
						<td ".$drcl." align=right>".number_format($barz->beratbersih,0,'.',',')."</td>
						<td ".$drcl." align=left>".$barz->nokendaraan."</td>
						<td ".$drcl." align=left>".$barz->supir."</td>
						<td ".$drcl." align=left>".$barz->noba."</td>
						<td ".$drcl." align=center>".$barz->notiket."</td>
						<td ".$drcl." align=left>".$barz->alasan."</td>
						<td ".$drcl." align=right>".number_format($barz->ongkoskirim,0,'.',',')."</td>
					</tr>";
			$gtberatbersih+=$barz->beratbersih;
			$gtongkoskirim+=$barz->ongkoskirim;
		}
	}
	$stream2.="<tr class=rowcontent>
					<td ".$drcl." colspan=5 align=center>Total</td>
					<td ".$drcl." align=right>".number_format($gtberatbersih,0,'.',',')."</td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=left></td>
					<td ".$drcl." align=right>".number_format($gtongkoskirim,0,'.',',')."</td>
				</tr>";
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Data_Outspec_".$kdOrg."_".$periode."__".date("His");
        if(strlen($stream2)>0){
			//$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
            //gzwrite($gztralala, $stream2);
			//gzclose($gztralala);
			// echo "<script language=javascript1.2>
			//    window.location='tempExcel/".$nop_.".xls.gz';
			//    </script>";
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream2)){
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
	}   
?>
