<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$diperiksa=checkPostGet('diperiksa','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			exit('Warning : PT tidak boleh kosong...!');
		}
	}
	$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
	$namapt=$optNm[$kodept];
	#Filter parameter where 
	$where="True";
	if($kodept!=''){
		$where.=" and left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where.=" and a.kodeorg like '".$kodeunit."%'";
	}
	if($diperiksa!=''){
		$where.=" and a.karyawanid = '".$diperiksa."'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
	}

	switch($proses){
		case 'getUnit':
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E' and LENGTH(kodeorganisasi)=4 
					and tipe='KEBUN' and detail='1' and induk like '".$kodept."%' order by namaorganisasi";
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			}else{
				$optUnit="";
			}
			while($dUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;
	}

	#ambil data Kontrol Ancak Panen
	$strz="select a.*,b.namaorganisasi,c.namakaryawan,e.bjr,round(if(d.luasareaproduktif=0,0,d.jumlahpokok/d.luasareaproduktif),0) as sph
			from ".$dbname.".kebun_cekancak a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			left join ".$dbname.".datakaryawan c on c.karyawanid=a.karyawanid
			left join ".$dbname.".setup_blok d on d.kodeorg=a.kodeorg
			left join ".$dbname.".kebun_5bjr e on e.kodeorg=a.kodeorg and e.tahunproduksi=year(a.tanggal)
			where ".$where." 
			order by a.tanggal,a.kodeorg,a.karyawanid";
	//exit('Warning: '.$strz);
	#preview: nampilin header ================================================================================
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream="";
	if($proses=='excel'){
		$stream.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream.="
			<thead>
				<tr class=rowheader>
					<td width='3%' align=center>No</td>
					<td width='3%' align=center>".$_SESSION['lang']['unit']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['divisi']."</td>
					<td width='8%' align=center>".$_SESSION['lang']['blok']."</td>
					<td width='3%' align=center>".$_SESSION['lang']['bjr']."</td>
					<td width='3%' align=center>SPH</td>
					<td align=center>".$_SESSION['lang']['diperiksa']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['tanggal']."</td>
					<td width='6%' align=center>".$_SESSION['lang']['pokok']." Sample</td>
					<td width='6%' align=center>Buah Tinggal</td>
					<td width='6%' align=center>".$_SESSION['lang']['brondolan']."</td>
					<td width='6%' align=center>Losses BT (Kg/Ha)</td>
					<td width='6%' align=center>Losses Brd (Kg/Ha)</td>
					<td width='6%' align=center>Losses Jml (Kg/Ha)</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
				</tr>
			</thead>
			<tbody>";
	if($rowz==0){
		$stream.="<tr class=rowcontent>";
		$stream.="	<td colspan=15>Tidak ada Data...!</td>";
		$stream.="</tr>";
	}else{
		$no=0;
		$gtpokok=0;
		$gtbrondolan=0;
		$gtjanjang=0;
		$gtlossesbt=0;
		$gtlossesbrd=0;
		$gtlossesjml=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
			$lossesbt=$barz->janjang/$barz->pokok*$barz->bjr*$barz->sph;
			$lossesbrd=$barz->brondolan/$barz->pokok*0.014*$barz->sph;
			if($lossesbt<=0){
				$nrmclrbt="";
			}else{
				$nrmclrbt="style='color:#FF0000;'";
			}
			if($lossesbrd<=0.5){
				$nrmclrbrd="";
			}else{
				$nrmclrbrd="style='color:#FF0000;'";
			}
			if(($lossesbt+$lossesbrd)<=0.5){
				$nrmclrjml="";
			}else{
				$nrmclrjml="style='color:#FF0000;'";
			}
            $stream.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".substr($barz->kodeorg,0,4)."</td>
						<td align=center>".substr($barz->kodeorg,0,6)."</td>
						<td align=left>".$barz->namaorganisasi."</td>
						<td align=right>".number_format($barz->bjr,2,'.',',')."</td>
						<td align=right>".number_format($barz->sph,0,'.',',')."</td>
						<td align=left>".$barz->namakaryawan."</td>
						<td align=center>".tanggalnormal($barz->tanggal)."</td>
						<td align=right>".number_format($barz->pokok,0,'.',',')."</td>
						<td align=right>".number_format($barz->janjang,0,'.',',')."</td>
						<td align=right>".number_format($barz->brondolan,0,'.',',')."</td>";
			if($proses=='excel'){
				$stream.="
						<td ".$nrmclrbt." align=right>".$lossesbt."</td>
						<td ".$nrmclrbrd." align=right>".$lossesbrd."</td>
						<td ".$nrmclrjml." align=right>".($lossesbt+$lossesbrd)."</td>";
			}else{
				$stream.="
						<td ".$nrmclrbt." align=right>".number_format($lossesbt,2,'.',',')."</td>
						<td ".$nrmclrbrd." align=right>".number_format($lossesbrd,2,'.',',')."</td>
						<td ".$nrmclrjml." align=right>".number_format($lossesbt+$lossesbrd,2,'.',',')."</td>";
			}
			$stream.="	<td align=left>".$barz->keterangan."</td>
					</tr>";
			$gtpokok+=$barz->pokok;
			$gtbrondolan+=$barz->brondolan;
			$gtjanjang+=$barz->janjang;
			$gtlossesbt+=$lossesbt;
			$gtlossesbrd+=$lossesbrd;
			$gtlossesjml+=$lossesbt+$lossesbrd;
		}
		$stream.="<tr class=rowcontent>
					<td align=center colspan=8>Total</td>
					<td align=right>".number_format($gtpokok,0,'.',',')."</td>
					<td align=right>".number_format($gtjanjang,0,'.',',')."</td>
					<td align=right>".number_format($gtbrondolan,0,'.',',')."</td>";
		if($proses=='excel'){
			$stream.="
					<td align=right>".$gtlossesbt."</td>
					<td align=right>".$gtlossesbrd."</td>
					<td align=right>".$gtlossesjml."</td>";
		}else{
			$stream.="
					<td align=right>".number_format($gtlossesbt,2,'.',',')."</td>
					<td align=right>".number_format($gtlossesbrd,2,'.',',')."</td>
					<td align=right>".number_format($gtlossesjml,2,'.',',')."</td>";
		}
		$stream.="	<td align=left></td>
				</tr>";
	}
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Kontrol Losses Ancak '.$_SESSION['lang']['panen'];
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.'<BR>'.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				//	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			    //    gzwrite($gztralala, $stream);
				//    gzclose($gztralala);
				//	echo "<script language=javascript1.2>
				//			window.location='tempExcel/".$nop_.".xls.gz';
				//		  </script>";
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
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
		break;

		default:
		break;
	}    
?>