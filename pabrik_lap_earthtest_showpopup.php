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
	$kodemesin=$_GET['kodemesin'];
	$tanggal=$_GET['tanggal'];
	$periode=trim(substr($tanggal,0,strlen($tanggal)));
	if(strlen($tanggal)==7){
		$where=" and tanggal < '".$periode."-01' ";
	}elseif(strlen($tanggal)==4){
		$where=" and tanggal < '".$periode."-01-01' ";
	}

	$strtb="select a.kodemesin,a.ukur1,a.standard from ".$dbname.".pabrik_earthtest a where a.kodemesin like '".$kodemesin."%'
			".$where."
			order by a.kodeorg,a.kodemesin,a.tanggal,a.standard,a.keterangan limit 1";
	$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
	$restb=mysql_query($strtb);
	$tebalsisa=array();
	while($bartb=mysql_fetch_object($restb)){
		$tebalsisa[$bartb->kodemesin]=$bartb->ukur1;
	}
	//exit('Warning: '.$strtb.' '.$tebalsisa);

	$strz="select a.*,b.namaorganisasi from ".$dbname.".pabrik_earthtest a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.kodeorg = '".$kdOrg."'
			and a.kodemesin='".$kodemesin."'
			and a.tanggal like '".$periode."%'
		ORDER BY a.kodeorg,a.kodemesin,a.tanggal,a.standard,a.keterangan
		";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Earth Test ".$kdOrg."<br>";
	$stream2.=$kodemesin."</h3>";
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
				<td width='3%' align=center>No</td>
				<td width='4%' align=center>".$_SESSION['lang']['unit']."</td>
				<td width='20%' align=left>".$_SESSION['lang']['lokasi']."</td>
				<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td width='5%' align=center>Standard</td>
				<td width='5%' align=center>Ukur 1</td>
				<td width='5%' align=center>Ukur 2</td>
				<td width='5%' align=center>Ukur 3</td>
				<td width='5%' align=center>Rata Rata</td>
				<td width='5%' align=center>Selisih</td>
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
						<td align=center>".$barz->kodeorg."</td>
						<td align=left>".$barz->kodemesin."</td>
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
		$nop_="Earth_Test_".$kdOrg."_".$periode."__".date("His");
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
