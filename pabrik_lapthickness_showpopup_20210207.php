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

	$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodemesin."'";
	$qOrg=mysql_query($sOrg);
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$namamesin=$rOrg['namaorganisasi'];
	}

	$strtb="select a.hmakhir,a.tipeservice from ".$dbname.".pabrik_thickness a where a.kodemesin like '".$kodemesin."%' and a.tipeservice<>0 and tanggal<'".$periode."-01'
			order by a.kodemesin,a.tanggal,a.tipeservice";
	//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
	$restb=mysql_query($strtb);
	$tebalsisa=0;
	while($bartb=mysql_fetch_object($restb)){
		$tebalsisa=$bartb->tebal1;
	}

	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_thickness a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
		where a.kodeorg = '".$kdOrg."'
			and a.kodemesin = '".$kodemesin."'
			and a.tanggal like '".$periode."%'
		ORDER BY a.kodemesin,a.tanggal,a.tipeservice
		";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Pengukuran Ketebalan Plat (Thickness) ".$kdOrg."<br>";
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
				<td width='2%' align=center>No</td>
				<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['service']."</td>
				<td width='5%' align=center>Ukur 1</td>
				<td width='5%' align=center>Ukur 2</td>
				<td width='5%' align=center>Ukur 3</td>
				<td width='5%' align=center>(%)</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			if($barz->tipeservice!='0'){
				$jenisservice='Ganti Plat';
				$tebalsisa=$barz->tebal1;
			}else{
				$jenisservice='';
			}
			$tebalkecil=($barz->tebal1<$barz->tebal2 ?  $barz->tebal1 : $barz->tebal2);
			$tebalkecil=($barz->tebal3<$tebalkecil ?  $barz->tebal3 : $tebalkecil);
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$barz->tanggal."</td>
						<td align=left>".$jenisservice."</td>
						<td align=right>".number_format($barz->tebal1,2,'.',',')."</td>
						<td align=right>".number_format($barz->tebal2,2,'.',',')."</td>
						<td align=right>".number_format($barz->tebal3,2,'.',',')."</td>
						<td align=right>".number_format(($tebalkecil/$tebalsisa*100),2,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Thickness_".$kdOrg."_".$periode."__".date("His");
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
