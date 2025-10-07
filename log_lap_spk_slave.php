<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$koderekanan=checkPostGet('koderekanan','');
	$notransaksi=checkPostGet('notransaksi','');
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
	$where="";
	$whtax="";
	if($kodept!=''){
		$where.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		$whtax.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where.=" and a.kodeorg = '".$kodeunit."'";
		$whtax.=" and kodeorg = '".$kodeunit."'";
	}
	if($koderekanan!=''){
		$where.=" and a.koderekanan = '".$koderekanan."'";
	}
	if($notransaksi!=''){
		$where.=" and a.notransaksi like '".$notransaksi."%'";
		$whtax.=" and notransaksi like '".$notransaksi."%'";
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
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
					and detail='1' and induk like '".$kodept."%' order by namaorganisasi";
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

	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td width='3%' ".$bgclr.">No</td>
			<td width='4%' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td width='18%' ".$bgclr.">".$_SESSION['lang']['notransaksi']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td width='20%' ".$bgclr.">".$_SESSION['lang']['kontraktor']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['nilaikontrak']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['ppn']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['pph']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['dari']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['sampai']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['realisasi']."</td>
		</tr>
		</thead><tbody>";

	#ambil data SPK
	$str="select a.*,b.ppn,b.pph,d.namasupplier,c.jmlrealisasi from ".$dbname.".log_spkht a
			LEFT JOIN (select kodeorg,notransaksi,sum(if(left(noakun,1)='1',nilai,0)) as ppn,sum(if(left(noakun,1)='2',nilai,0)) as pph 
						from ".$dbname.".log_spk_tax
						where true ".$whtax." GROUP BY kodeorg,notransaksi) b on b.kodeorg=a.kodeorg and b.notransaksi=a.notransaksi
			left join (select notransaksi,sum(jumlahrealisasi) as jmlrealisasi from ".$dbname.".log_baspk GROUP BY notransaksi) c on c.notransaksi=a.notransaksi
			left join ".$dbname.".log_5supplier d on d.supplierid=a.koderekanan
			where true ".$where."
			ORDER BY a.kodeorg,a.tanggal desc";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$no=0;
	$gtnilaikontrak=0;
	$gtppnkontrak=0;
	$gtpphkontrak=0;
	$gtnilairealisasi=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td align='center'>".$bar->kodeorg."</td>
					<td align='left'>".$bar->notransaksi."</td>
					<td align='center'>".$bar->tanggal."</td>
					<td align='center'>".$bar->divisi."</td>
					<td align='left'>".$bar->namasupplier."</td>
					<td align='right'>".number_format($bar->nilaikontrak,0)."</td>
					<td align='right'>".number_format($bar->ppn,0)."</td>
					<td align='right'>".number_format($bar->pph,0)."</td>
					<td align='center'>".$bar->dari."</td>
					<td align='center'>".$bar->sampai."</td>
					<td align='right'>".number_format($bar->jmlrealisasi,0)."</td>
				</tr>";
		$gtnilaikontrak+=$bar->nilaikontrak;
		$gtppnkontrak+=$bar->ppn;
		$gtpphkontrak+=$bar->pph;
		$gtnilairealisasi+=$bar->jmlrealisasi;
	}
//	if($no>0){
		$stream.="<tr bgcolor='#00FFFF' class=rowcontent>
					<td colspan=6 bgcolor='#00FFFF' align='center'>Grand Total</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtnilaikontrak,0)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtppnkontrak,0)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtpphkontrak,0)."</td>
					<td bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtnilairealisasi,0)."</td>
				</tr>";
//	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Daftar SPK ';
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
