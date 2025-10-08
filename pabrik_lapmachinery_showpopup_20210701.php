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

	$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodemesin."'";
	$qOrg=mysql_query($sOrg);
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$namamesin=$rOrg['namaorganisasi'];
	}

	$strz="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_machinery a 
			LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
			where a.kodeorg='".$kdOrg."' and a.kodemesin='".$kodemesin."'
			ORDER BY a.kodemesin,a.kodesubmesin";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	$stream2.="";
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}

	$stream2.="<tbody><tr>
				<td rowspan=4><img src='images/logoagro.png' style='width:80px;height:80px;'></td>
				<th colspan=6 align=center valign=middle style='font-size:12;width:440px'><b>SPECIFICATION OF</b></th>
				<td rowspan=4><img src='images/logo_cks.jpg' style='width:80px;height:80px;'></td>
			</tr>
			<tr>
				<th colspan=6></th>
			</tr>
			<tr>
				<th colspan=6 align=center valign=middle><b>".$namamesin."</b></th>
			</tr>
			<tr>
				<th colspan=6></th>
			</tr>";

	$stream2.="<tr>
				<td style='width:80px'></td>
				<td style='width:20px'></td>
				<td style='width:20px'></td>
				<td style='width:2px'></td>
				<td style='width:80px'></td>
				<td style='width:80px'></td>
				<td style='width:80px'></td>
				<td style='width:80px'></td>
				</tr>";

	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			$stream2.="<tr class=rowcontent>";
			$stream2.="<td colspan=3><b>".strtoupper($barz->namasubmesin)."</b></td><td>:</td><td colspan=4>$barz->merk</td></tr>";
			if(strtoupper($barz->namasubmesin)!='AUXILARY'){
				$stream2.="<tr><td colspan=3>Type/Model</td><td>:</td><td colspan=2 align=left>$barz->model</td><td colspan=2></td></tr>";
			}
			if($barz->unit1!='' and !is_null($barz->unit1) and $barz->unit1!='0'){
				$stream2.="<tr><td colspan=3></td><td>:</td><td colspan=2>$barz->unit1</td><td colspan=2 align=left>".number_format($barz->stsunit1*100,0)."%</td></tr>";
			}
			if($barz->unit2!='' and !is_null($barz->unit2) and $barz->unit2!='0'){
				$stream2.="<tr><td colspan=3></td><td>:</td><td colspan=2>$barz->unit2</td><td colspan=2 align=left>".number_format($barz->stsunit2*100,0)."%</td></tr>";
			}
			if($barz->ratio!='' and !is_null($barz->ratio) and $barz->ratio!='0'){
				$stream2.="<tr><td colspan=3>Ratio</td><td>:</td><td colspan=2 align=left>$barz->ratio</td><td colspan=2></td></tr>";
			}
			if($barz->rpm!='' and !is_null($barz->rpm) and $barz->rpm!='0'){
				$stream2.="<tr><td colspan=3>Rpm</td><td>:</td><td colspan=2 align=left>$barz->rpm</td><td colspan=2></td></tr>";
			}
			if($barz->kw!='' and !is_null($barz->kw) and $barz->kw!='0'){
				$stream2.="<tr><td colspan=3>KW</td><td>:</td><td colspan=2 align=left>$barz->kw</td><td colspan=2></td></tr>";
			}
			if($barz->manufacturedyear!='' and !is_null($barz->manufacturedyear) and $barz->manufacturedyear!='0'){
				$stream2.="<tr><td colspan=3>Date</td><td>:</td><td colspan=2>$barz->manufacturedyear</td><td colspan=2></td></tr>";
			}
			if($barz->serialnumber!='' and !is_null($barz->serialnumber) and $barz->serialnumber!='0'){
				$stream2.="<tr><td colspan=3>Serial Number</td><td>:</td><td colspan=2>$barz->serialnumber</td><td colspan=2></td></tr>";
			}
			if($barz->ampere!='' and !is_null($barz->ampere) and $barz->ampere!='0'){
				$stream2.="<tr><td colspan=3>Ampere</td><td>:</td><td colspan=2>$barz->ampere</td><td colspan=2></td></tr>";
			}
			//if(strtoupper($barz->namasubmesin)!='AUXILARY'){
				$stream2.="<tr><td colspan=3>Status</td><td>:</td><td colspan=2 align=left>".number_format($barz->statusmesin*100,0)."%</td><td colspan=2></td></tr>";
			//}
			if(strtoupper($barz->namasubmesin)=='ELEKTROMOTOR' or strtoupper($barz->namasubmesin)=='GENERATOR' or strtoupper($barz->namasubmesin)=='DOOR'){
				$stream2.="<tr><td colspan=3>HM</td><td>:</td><td colspan=2>$barz->merkhm</td><td colspan=2></td></tr>";
			}
			$stream2.="<tr><td colspan=8></td></tr>";
			if($barz->sproket1!='' and !is_null($barz->sproket1) and $barz->sproket1!='0'){
				$stream2.="<tr><td colspan=8><b>SPROKET 1</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->sproket1</td><td colspan=2></td></tr>";
			}
			if($barz->sproket2!='' and !is_null($barz->sproket2) and $barz->sproket2!='0'){
				$stream2.="<tr><td colspan=8><b>SPROKET 2</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->sproket2</td><td colspan=2></td></tr>";
			}
			if($barz->sproket3!='' and !is_null($barz->sproket3) and $barz->sproket3!='0'){
				$stream2.="<tr><td colspan=8><b>SPROKET 3</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->sproket3</td><td colspan=2></td></tr>";
			}
			if($barz->chain1!='' and !is_null($barz->chain1) and $barz->chain1!='0'){
				$stream2.="<tr><td colspan=8><b>CHAIN 1</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->chain1</td><td colspan=2></td></tr>";
			}
			if($barz->chain2!='' and !is_null($barz->chain2) and $barz->chain2!='0'){
				$stream2.="<tr><td colspan=8><b>CHAIN 2</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->chain2</td><td colspan=2></td></tr>";
			}
			if($barz->pully1!='' and !is_null($barz->pully1) and $barz->pully1!='0'){
				$stream2.="<tr><td colspan=8><b>PULLY 1</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->pully1</td><td colspan=2></td></tr>";
			}
			if($barz->pully2!='' and !is_null($barz->pully2) and $barz->pully2!='0'){
				$stream2.="<tr><td colspan=8><b>PULLY 2</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->pully2</td><td colspan=2></td></tr>";
			}
			if($barz->vbelt!='' and !is_null($barz->vbelt) and $barz->vbelt!='0'){
				$stream2.="<tr><td colspan=8><b>V-BELT</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->vbelt</td><td colspan=2></td></tr>";
			}
			if($barz->coupling!='' and !is_null($barz->coupling) and $barz->coupling!='0'){
				$stream2.="<tr><td colspan=8><b>COUPLING</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->coupling</td><td colspan=2></td></tr>";
			}
			if($barz->bearing1!='' and !is_null($barz->bearing1) and $barz->bearing1!='0'){
				$stream2.="<tr><td colspan=8><b>BEARING 1</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->bearing1</td><td colspan=2></td></tr>";
			}
			if($barz->bearing2!='' and !is_null($barz->bearing2) and $barz->bearing2!='0'){
				$stream2.="<tr><td colspan=8><b>BEARING 2</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->bearing2</td><td colspan=2></td></tr>";
			}
			if($barz->bearing3!='' and !is_null($barz->bearing3) and $barz->bearing3!='0'){
				$stream2.="<tr><td colspan=8><b>BEARING 3</b></td></tr>";
				$stream2.="<tr><td colspan=3>Type</td><td>:</td><td colspan=2 align=left>$barz->bearing3</td><td colspan=2></td></tr>";
			}
		}
	}
	$stream2.="</tbody></table>";

	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Laporan_Data_Mesin_".$kdOrg."_".date("His");
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
