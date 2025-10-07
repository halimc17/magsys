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
	$periode=trim(substr($tanggal,0,strlen($tanggal)));
	if(strlen($tanggal)==7){
		$where=" and tanggal < '".$periode."-01' ";
	}elseif(strlen($tanggal)==4){
		$where=" and tanggal < '".$periode."-01-01' ";
	}

	$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodemesin."'";
	$qOrg=mysql_query($sOrg);
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$namamesin=$rOrg['namaorganisasi'];
	}

	$strtb="select a.kodemesin,a.ukur1,a.standard from ".$dbname.".pabrik_verifikasi_ampere a where a.kodemesin like '".$kodemesin."%'
			".$where."
			order by a.kodeorg,a.kodemesin,a.tanggal,a.standard,a.keterangan limit 1";
	//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
	$restb=mysql_query($strtb);
	$tebalsisa=array();
	while($bartb=mysql_fetch_object($restb)){
		$tebalsisa[$bartb->kodemesin]=$bartb->ukur1;
	}
	//exit('Warning: '.$strtb.' '.$tebalsisa);

	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun,d.ampere from ".$dbname.".pabrik_verifikasi_ampere a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
		left join ".$dbname.".pabrik_machinery d on d.kodemesin=a.kodemesin and namasubmesin='Elektromotor'
		where a.kodeorg = '".$kdOrg."'
			and a.kodemesin like '".$kodemesin."%'
			and a.tanggal like '".$periode."%'
		ORDER BY a.kodeorg,a.kodemesin,a.tanggal,a.standard,a.keterangan
		";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Verifikasi Ampere ".$kdOrg."<br>";
	$stream2.=$namamesin."</h3>";
	if($periode!=''){
		$stream2.=" Periode: ".$periode;
	}
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td rowspan=2 width='2%' align=center>No</td>
				<td rowspan=2 width='5%' align=center>".$_SESSION['lang']['station']."</td>
				<td rowspan=2 width='7%' align=center>".$_SESSION['lang']['kode']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['nmmesin']."</td>
				<td rowspan=2 width='7%' align=center>KW Electromotor</td>
				<td rowspan=2 width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td rowspan=2 width='4%' align=center>Ampere Meter</td>
				<td colspan=4 align=center>Tang Ampere</td>
				<td rowspan=2 width='4%' align=center>Selisih</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>
			</tr>
			<tr class=rowcontent>
				<td width='4%' align=center>R</td>
				<td width='4%' align=center>S</td>
				<td width='4%' align=center>T</td>
				<td width='4%' align=center>Rata Rata</td>
			</tr>
		</thead>";
	$stream2.="<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			if($barz->standard!='0'){
				$jenisservice='Plat Baru';
				$tebalsisa[$barz->kodemesin]=$barz->ukur1;
			}else{
				$jenisservice='';
			}
			$jmlukur=0;
			if($barz->ukur1>0){
				$tebalkecil=$barz->ukur1;
				$jmlukur+=1;
			}
			if($barz->ukur2>0){
				$jmlukur+=1;
			}
			if($barz->ukur3>0){
				$jmlukur+=1;
			}
			if($barz->ukur2>0 and $tebalkecil>0){
				$tebalkecil=($barz->ukur2<$tebalkecil ?  $barz->ukur2 : $tebalkecil);
			}
			if($barz->ukur3>0 and $tebalkecil>0){
				$tebalkecil=($barz->ukur3<$tebalkecil ?  $barz->ukur3 : $tebalkecil);
			}
			$tebalpersen=($tebalsisa[$barz->kodemesin]==0 ? 0 : $tebalkecil/$tebalsisa[$barz->kodemesin]*100);
			$ukurrata2=0;
			if($jmlukur>0){
				$ukurrata2=round(($barz->ukur1+$barz->ukur2+$barz->ukur3)/$jmlukur,2);
			}
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".substr($barz->kodemesin,0,6)."</td>
						<td align=center>".$barz->kodemesin."</td>
						<td align=left>".$barz->namamesin."</td>
						<td align=left>".$barz->ampere."</td>
						<td align=center>".$barz->tanggal."</td>
						<td align=right>".number_format($barz->standard,2,'.',',')."</td>
						<td align=right>".number_format($barz->ukur1,2,'.',',')."</td>
						<td align=right>".number_format($barz->ukur2,2,'.',',')."</td>
						<td align=right>".number_format($barz->ukur3,2,'.',',')."</td>
						<td align=right>".number_format($ukurrata2,2,'.',',')."</td>
						<td align=right>".number_format($ukurrata2-$barz->standard,2,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Verifikasi_Ampere_".$kdOrg."_".$periode."__".date("His");
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
