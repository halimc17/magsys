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

	$strtb="select a.hmakhir,a.tipeservice from ".$dbname.".pabrik_hm a where a.kodemesin like '".$kodemesin."%' and a.tipeservice<>0 and tanggal<'".$periode."-01'
			order by a.kodemesin,a.tanggal,a.tipeservice";
	//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
	$restb=mysql_query($strtb);
	$hmsisa1=0;
	$hmsisa2=0;
	$hmsisa3=0;
	while($bartb=mysql_fetch_object($restb)){
		if($bartb->tipeservice==3){
			$hmsisa3=$bartb->hmakhir;
		}elseif($bartb->tipeservice==2){
			$hmsisa2=$bartb->hmakhir;
		}else{
			$hmsisa1=$bartb->hmakhir;
		}
	}

	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_hm a 
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
	
	$stream2="<h3>"."Laporan HM ".$kdOrg."<br>";
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
				<td width='5%' align=center>HM Awal</td>
				<td width='5%' align=center>HM Akhir</td>
				<td width='5%' align=center>Jam</td>
				<td width='5%' align=center>Pergantian Sparepart1</td>
				<td width='5%' align=center>Pergantian Sparepart2/ Intermed/ Top.OH</td>
				<td width='5%' align=center>Pergantian Sparepart3/ Major/ Gen.OH</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=4>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		$awalmesin='';
		while($barz=mysql_fetch_object($resz)){
			if($awalmesin!=$barz->kodemesin){
				$awalmesin=$barz->kodemesin;
				$strjam="select a.jamganti1 from ".$dbname.".pabrik_hm_setup a where a.kodemesin like '".$barz->kodemesin."%'";
				$resjam=mysql_query($strjam);
				$jamganti=10000;
				$jamjalan1=$barz->hmawal-$hmsisa1;
				$jamjalan2=$barz->hmawal-$hmsisa2;
				$jamjalan3=$barz->hmawal-$hmsisa3;
				while($barjam=mysql_fetch_object($resjam)){
					$jamganti=$barjam->jamganti1;
				}
			}
			$jamjalan1+=$barz->jam;
			$jamjalan2+=$barz->jam;
			$jamjalan3+=$barz->jam;
			if($barz->tipeservice=='3'){
				$jenisservice='Pergantian Sparepart3/ Major/ General Overhaul';
				//$jamjalan1=0;
				//$jamjalan2=0;
				$jamjalan3=0;
				$jamganti=$barz->jamganti;
			}elseif($barz->tipeservice=='2'){
				$jenisservice='Pergantian Sparepart2/ Intermediate/ Top Overhaul';
				//$jamjalan1=0;
				$jamjalan2=0;
				$jamganti=$barz->jamganti;
			}elseif($barz->tipeservice=='1'){
				$jenisservice='Pergantian Sparepart1';
				$jamjalan1=0;
				$jamganti=$barz->jamganti;
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
						<td align=right>".number_format($jamjalan1,2,'.',',')."</td>
						<td align=right>".number_format($jamjalan2,2,'.',',')."</td>
						<td align=right>".number_format($jamjalan3,2,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Laporan_HM_".$kdOrg."_".$periode."__".date("His");
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
