<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$kdOrg=$_POST['kdorg'];
	$kodestasiun=$_POST['kodestasiun'];
	$namastasiun=$_POST['namastasiun'];
	$periode=$_POST['periode'];
	$jenis=$_POST['jenis'];
	if($kdOrg=='')$kdOrg=$_GET['kdorg'];
	if($kodestasiun=='')$kodestasiun=$_GET['kodestasiun'];
	if($namastasiun=='')$namastasiun=$_GET['namastasiun'];
	if($periode=='')$periode=$_GET['periode'];
	if($jenis=='')$jenis=$_GET['jenis'];
	$where="";
	if($kdOrg!=''){
		$where.=" and a.kodeorg='".$kdOrg."'";
	}
	if($kodestasiun!=''){
		$where.=" and a.kodestasiun='".$kodestasiun."'";
	}
	if($periode!=''){
		$where.=" and a.tanggal like '".$periode."%'";
	}
	if($jenis!=''){
		$where.=" and a.jenis='".$jenis."'";
	}
	$strz="select a.*,b.namaorganisasi as namastasiun from ".$dbname.".pabrik_preventifpanel a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodestasiun
			where true ".$where."
			order by a.kodeorg,a.kodestasiun,a.tanggal,a.jenis";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	
	$stream2="<h3>"."Preventive Panel ".$kdOrg."<br>";
	$stream2.=$namastasiun."</h3>";
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
				<td width='6%' align=center>".$_SESSION['lang']['kode']."</td>
				<td width='30%' align=left>".$_SESSION['lang']['station']."</td>
				<td width='4%' align=center>".$_SESSION['lang']['tipe']."</td>
				<td width='8%' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
			</tr>
		</thead>";
	$stream2.="<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$barz->kodeorg."</td>
						<td align=center>".$barz->kodestasiun."</td>
						<td align=left>".$barz->namastasiun."</td>
						<td align=center>".$barz->jenis."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Preventive_Panel_".$kdOrg."_".$periode."__".date("His");
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
