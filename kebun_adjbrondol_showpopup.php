<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$carikodeorg=checkPostGet('carikodeorg','');
	$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
	$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
	$carijenis=checkPostGet('carijenis','');
	$where="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where.="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where.="left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
	}else{
		$where.="a.kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
	}
	if($carikodeorg!=''){
		$where.=" and a.kodeorg='".$carikodeorg."'";
	}
	if($caritanggal1!='' and $caritanggal2==''){
		$caritanggal2=$caritanggal1;
	}
	if($caritanggal1=='' and $caritanggal2!=''){
		$caritanggal1=$caritanggal2;
	}
	if($caritanggal1!='' and $caritanggal2!=''){
		$where.=" and a.tanggal>='".$caritanggal1."' and a.tanggal<='".$caritanggal2."'";
	}
	if($carijenis!=''){
		$where.=" and a.jenis='".$carijenis."'";
	}
	$strz="select a.*,b.namaorganisasi from ".$dbname.".kebun_adjbrondol a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where ".$where." 
			order by a.kodeorg,a.tanggal,a.waktu,a.jenis";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$stream2.="<h2>ADJUSTMENT BRONDOLAN</h2>";
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td width='3%' align=center>No</td>
				<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['divisi']."</td>
				<td width='15%' align=center>".$_SESSION['lang']['blok']."</td>
				<td width='7%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td width='5%' align=center>".$_SESSION['lang']['waktu']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['jenis']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['kg']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
	</tr>
		</thead>
		<tbody>";
	if($rowz==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		$no=0;
		$gtkg=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".substr($barz->kodeorg,0,4)."</td>
						<td align=center>".substr($barz->kodeorg,0,6)."</td>
						<td align=left>".$barz->kodeorg."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>
						<td align=center>".substr($barz->waktu,0,5)."</td>
						<td align=left>".$barz->jenis."</td>
						<td align=right>".number_format($barz->kg,0,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
			$gtkg+=$barz->kg;
		}
	}
	if($carijenis!=''){
		$stream2.="<tr class=rowcontent>
					<td align=center colspan=7>Total</td>
					<td align=right>".number_format($gtkg,0,'.',',')."</td>
					<td align=left></td>
				</tr>";
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Adjustment_Brondolan_".date("YmdHis");
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
