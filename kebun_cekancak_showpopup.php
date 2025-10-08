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
	$carikary=checkPostGet('carikary','');
	$namapt="";
	$where="";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where.="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where.="left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
	}else{
		$where.="a.kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
	}
	if($carikodeorg!=''){
		$where.=" and a.kodeorg like'".$carikodeorg."%'";
		$optPT=makeOption($dbname, 'organisasi','kodeorganisasi,induk');
		$kodept=$optPT[substr($carikodeorg,0,4)];
		$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
		$namapt=$optNm[$kodept];
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
	if($carikary!=''){
		$where.=" and a.karyawanid='".$carikary."'";
	}
	$strz="select a.*,b.namaorganisasi,c.namakaryawan 
			from ".$dbname.".kebun_cekancak a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			left join ".$dbname.".datakaryawan c on c.karyawanid=a.karyawanid
			where ".$where." 
			order by a.tanggal desc,a.kodeorg,a.karyawanid";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
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
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
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
            $stream2.="<tr class=rowcontent>
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
						<td align=right>".number_format($barz->brondolan,0,'.',',')."</td>
						<td ".$nrmclrbt."  align=right>".$lossesbt."</td>
						<td ".$nrmclrbrd." align=right>".$lossesbrd."</td>
						<td ".$nrmclrjml." align=right>".($lossesbt+$lossesbrd)."</td>
						<td align=left>".$barz->keterangan."</td>
					</tr>";
			$gtpokok+=$barz->pokok;
			$gtbrondolan+=$barz->brondolan;
			$gtjanjang+=$barz->janjang;
			$gtlossesbt+=$lossesbt;
			$gtlossesbrd+=$lossesbrd;
			$gtlossesjml+=$lossesbt+$lossesbrd;
		}
		$stream2.="<tr class=rowcontent>
					<td align=center colspan=8>Total</td>
					<td align=right>".number_format($gtpokok,0,'.',',')."</td>
					<td align=right>".number_format($gtbrondolan,0,'.',',')."</td>
					<td align=right>".number_format($gtjanjang,0,'.',',')."</td>
					<td align=right>".$gtlossesbt."</td>
					<td align=right>".$gtlossesbrd."</td>
					<td align=right>".$gtlossesjml."</td>
					<td align=left></td>
				</tr>";
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
        if(strlen($stream2)>0){
			$judul="Kontrol Ancak Panen";
			$stream2='<h2>'.$namapt.'<BR>'.$judul.'</h2>'.$stream2;
		    $stream2.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $nop_=$judul.'_'.date("YmdHis");
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
