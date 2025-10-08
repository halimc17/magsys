<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
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

//	$strtb="select a.kodemesin,a.ukur1,a.temp from ".$dbname.".pabrik_meggertest a where a.kodemesin like '".$kodemesin."%'
//			".$where."
//			order by a.kodeorg,a.kodemesin,a.tanggal,a.temp,a.keterangan limit 1";
//	//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
//	$restb=mysql_query($strtb);
//	$tebalsisa=array();
//	while($bartb=mysql_fetch_object($restb)){
//		$tebalsisa[$bartb->kodemesin]=$bartb->ukur1;
//	}
	//exit('Warning: '.$strtb.' '.$tebalsisa);

	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun,d.kw from ".$dbname.".pabrik_meggertest a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
		left join ".$dbname.".pabrik_machinery d on d.kodemesin=a.kodemesin and namasubmesin='Elektromotor'
		where a.kodeorg = '".$kdOrg."'
			and a.kodemesin like '".$kodemesin."%'
			and a.tanggal like '".$periode."%'
		ORDER BY a.kodeorg,a.kodemesin,a.tanggal,a.temp,a.keterangan
		";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Megger Test ".$kdOrg."<br>";
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
				<td colspan=3 align=center>Megger Test (M&#8486;)</td>
				<td align=center>Temp</td>
				<td colspan=4 align=center>".$_SESSION['lang']['kondisi']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>
			</tr>
			<tr class=rowcontent>
				<td width='4%' align=center>U-V</td>
				<td width='4%' align=center>U-W</td>
				<td width='4%' align=center>V-W</td>
				<td width='4%' align=center>(&#8451;)</td>
				<td width='5%' align=center>Critical</td>
				<td width='5%' align=center>Abnormal</td>
				<td width='5%' align=center>Good</td>
				<td width='5%' align=center>VeryGood</td>
			</tr>
		</thead>";
	$stream2.="<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			$tebalkecil=0;
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
						<td align=right>".$barz->kw."</td>
						<td align=center>".$barz->tanggal."</td>
						<td align=right>".number_format($barz->ukur1,0,'.',',')."</td>
						<td align=right>".number_format($barz->ukur2,0,'.',',')."</td>
						<td align=right>".number_format($barz->ukur3,0,'.',',')."</td>
						<td align=right>".number_format($barz->temp,2,'.',',')."</td>";
			if($ukurrata2<5){
				$stream2.="<td align=center>"."&radic;"."</td>
						<td></td>
						<td></td>
						<td></td>";
			}else if($ukurrata2<10){
				$stream2.="<td></td>
						<td align=center>"."&radic;"."</td>
						<td></td>
						<td></td>";
			}else if($ukurrata2<50){
				$stream2.="<td></td>
						<td></td>
						<td align=center>"."&radic;"."</td>
						<td></td>";
			}else{
				$stream2.="<td></td>
						<td></td>
						<td></td>
						<td align=center>"."&radic;"."</td>";
			}
			$stream2.="	<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Megger_Test_".$kdOrg."_".$periode."__".date("His");
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
