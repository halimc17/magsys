<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	if($kdOrg=='')$kdOrg=$_GET['kdorg'];
	if($stasiun=='')$stasiun=$_GET['stasiun'];
	$kodemesin=$_GET['kodemesin'];
	$tanggal=$_GET['tanggal'];
	$periode=substr($tanggal,0,7);
	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_hm a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
		where a.kodeorg = '".$kdOrg."'
			and a.kodemesin = '".$kodemesin."'
			and a.tanggal like '".$periode."%'
		ORDER BY a.kodemesin,a.tanggal,a.tipeservice
		";
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);

	$rMesin=mysql_fetch_assoc($resz);
	$namamesin=$rMesin['namamesin'];
	
	$stream2="<h3>"."Laporan HM ".$kdOrg;
	$stream2.=$namamesin."</h3>";
	$stream2.=" Periode: ".$periode;
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>Service</td>
				<td align=center>HM Awal</td>
				<td align=center>HM Akhir</td>
				<td align=center>Jam</td>
				<td align=center>Keterangan</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=4>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			if($barz->tipeservice=='3'){
				$jenisservice='Major/General Overhaul/Sparepart3';
			}elseif($barz->tipeservice=='2'){
				$jenisservice='Intermediate/Top Overhaul/Sparepart2';
			}elseif($barz->tipeservice=='1'){
				$jenisservice='Pergantian Sparepart';
			}else{
				$jenisservice='';
			}
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$barz->tanggal."</td>
						<td align=left>".$jenisservice."</td>
						<td align=right>".$barz->hmawal."</td>
						<td align=right>".$barz->hmakhir."</td>
						<td align=right>".number_format($barz->jam,2,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Laporan_premi_".$kdOrg."_".$periode."__".date("His");
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
