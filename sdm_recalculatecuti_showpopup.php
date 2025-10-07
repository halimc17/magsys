<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$carikodeorg=checkPostGet('carikodeorg','');
	$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
	$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
	$carikodebarang=checkPostGet('carikaryawanid','');
	$where="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where.="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where.="a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
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
	if($carikaryawanid!=''){
		$where.=" and a.karyawanid='".$carikaryawanid."'";
	}
	$strz="select a.*,b.nik,b.namakaryawan,c.sisa from ".$dbname.".sdm_phkcuti a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			left join ".$dbname.".sdm_cutiht c on c.karyawanid=a.karyawanid and c.kodeorg=a.kodeorg and c.periodecuti=left(a.tglkeluar,4)
			where ".$where." 
			order by a.kodeorg,a.tanggal,a.karyawanid";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$optNamaPT=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
		$optNamaBlok=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$namapt=$optNamaBlok[$optNamaPT[$kodeunit2]];
		$stream2.="<h2>".$namapt."<br>";
		$stream2.="DAFTAR KARYAWAN RESIGN/PHK</h2>";
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td style='width:050px' align=center>No</td>
				<td width='5%'  align=center>".$_SESSION['lang']['unit']."</td>
				<td width='7%'  align=center>".$_SESSION['lang']['nik']."</td>
				<td width='30%' align=center>".$_SESSION['lang']['nama']."</td>
				<td width='7%'  align=center>".$_SESSION['lang']['tanggal']."</td>
				<td width='7%'  align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
				<td width='7%'  align=center>".$_SESSION['lang']['jumlah']."</td>
				<td width='30%' align=center>".$_SESSION['lang']['keterangan']."</td>";
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
			$no+=1;
			$stream2.="	<td align=center>".$no."</td>
						<td align=center>".$barz->kodeorg."</td>
						<td align=center>".$barz->nik."</td>
						<td align=left>".$barz->namakaryawan."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>
						<td align=center>".tanggalnormal($barz->tglkeluar)."</td>
						<td align=right>".number_format($barz->sisa,0,'.',',')."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
			$xtgl=$barz->tanggal;
			$gtqtymasuk+=$barz->qtymasuk;
			$gtqtykeluar+=$barz->qtykeluar;
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Daftar_Karyawan_Resign_PHK_".date("YmdHis");
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
