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
	$carikodebarang=checkPostGet('carikodebarang','');
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
		$kodeunit2=$carikodeorg;
	}else{
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			exit('Warning: Unit tidak boleh kosong...!');
		}else{
			$kodeunit2=$_SESSION['empl']['lokasitugas'];
		}
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
	if($carikodebarang!=''){
		$where.=" and a.kodebarang='".$carikodebarang."'";
	}
	$strz="select a.*,if(a.kodemesin='GUDANGB3','Gudang B3',c.namaorganisasi) as namaorganisasi,d.namabarang from ".$dbname.".pabrik_limbahb3 a 
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodemesin
			left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
			where ".$where." 
			order by a.kodeorg,a.tanggal,a.kodemesin,a.kodebarang";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$optNamaPT=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
		$optNamaBlok=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$namapt=$optNamaBlok[$optNamaPT[$kodeunit2]];
		$stream2.="<h2>".$namapt."<br>";
		$stream2.="MONITORING LIMBAH B3</h2>";
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td style='width:050px' align=center>No</td>
				<td style='width:130px' align=center>".$_SESSION['lang']['tanggal']."</td>
				<td style='width:465px' align=center>".$_SESSION['lang']['mesin']."</td>
				<td style='width:465px' align=center>".$_SESSION['lang']['namabarang']."</td>
				<td style='width:090px' align=center>".$_SESSION['lang']['masuk']."</td>
				<td style='width:090px' align=center>".$_SESSION['lang']['keluar']."</td>
				<td style='width:465px' align=center>".$_SESSION['lang']['keterangan']."</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($rowz==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		$no=0;
		$xtgl='xxxx-xx-xx';
		$gtqtymasuk=0;
		$gtqtykeluar=0;
		while($barz=mysql_fetch_object($resz)){
            $stream2.="<tr class=rowcontent>";
			if($xtgl!=$barz->tanggal){
			$no+=1;
			$stream2.="	<td align=center>".$no."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>";
			}else{
			$stream2.="	<td align=center></td>
						<td align=center></td>";
			}
			$stream2.="	<td align=left>".$barz->namaorganisasi."</td>
						<td align=left>".$barz->namabarang."</td>
						<td align=right>".number_format($barz->qtymasuk,0,'.',',')."</td>
						<td align=right>".number_format($barz->qtykeluar,0,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
			$xtgl=$barz->tanggal;
			$gtqtymasuk+=$barz->qtymasuk;
			$gtqtykeluar+=$barz->qtykeluar;
		}
	}
	$stream2.="<tr class=rowcontent>
					<td bgcolor='#00ebeb' align=center colspan=3></td>
					<td bgcolor='#00ebeb' align=center>Total (Liter)</td>
					<td bgcolor='#00ebeb' align=right>".number_format($gtqtymasuk,0,'.',',')."</td>
					<td bgcolor='#00ebeb' align=right>".number_format($gtqtykeluar,0,'.',',')."</td>
					<td bgcolor='#00ebeb' align=left></td>
				</tr>";
	$stream2.="</tbody></table>";
	$stream2.="<table class=sortable border=0 cellspacing=1><tbody>
					<tr class=rowcontent></tr><tr class=rowcontent></tr><tr class=rowcontent></tr>
					<tr class=rowcontent>
						<td colspan=3 align=center>Dibuat,</td>
						<td colspan=1 align=center>Diperiksa,</td>
						<td colspan=3 align=center>Diketahui,</td>
					</tr>
					<tr class=rowcontent></tr><tr class=rowcontent></tr><tr class=rowcontent></tr>
					<tr class=rowcontent>
						<td colspan=3 style='width:130px' align=center>Oil Man</td>
						<td colspan=1 align=center>Maintenance Group Leader</td>
						<td colspan=3 align=center>Mill Assistant Maintenance</td>
					</tr>
				</tbody></table>";

	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Pabrik_Limbah_B3_".date("YmdHis");
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
