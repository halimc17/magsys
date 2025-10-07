<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$kodecustomer=checkPostGet('kodecustomer','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	$tipereal=checkPostGet('tipereal','');
	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			exit('Warning : PT tidak boleh kosong...!');
		}
	}
	$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
	$namapt=$optNm[$kodept];
	#Filter parameter where 
	$where="";
	if($kodept!=''){
		$where.=" and a.kodept='".$kodept."'";
	}
	if($kodeunit!=''){
		$where=" and a.kodeorg='".$kodeunit."'";
	}
	if($kodecustomer!=''){
		$where.=" and a.kodecustomer='".$kodecustomer."'";
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
	if($tipereal=='Sudah'){
		$where.=" and (a.nilaiinvoice+nilaippn-rupiah1-rupiah2-rupiah3-rupiah4-rupiah5-rupiah6-rupiah7+rupiah8)-if(isnull(b.jmlbayar),0,b.jmlbayar)<=0";
	}else if($tipereal=='Belum'){
		$where.=" and (a.nilaiinvoice+nilaippn-rupiah1-rupiah2-rupiah3-rupiah4-rupiah5-rupiah6-rupiah7+rupiah8)-if(isnull(b.jmlbayar),0,b.jmlbayar)>0";
	}

	switch($proses){
		case 'getUnit':
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
					and detail='1' and induk='".$kodept."' order by namaorganisasi";
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
			<td rowspan=2 width='2%' ".$bgclr.">No</td>
			<td rowspan=2 width='4%' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td rowspan=2 width='10%' ".$bgclr.">".$_SESSION['lang']['nmcust']."</td>
			<td rowspan=2 width='5%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td rowspan=2 width='7%' ".$bgclr.">".$_SESSION['lang']['noinvoice']."</td>
			<td rowspan=2 width='7%' ".$bgclr.">".$_SESSION['lang']['nilaiinvoice']."</td>
			<td rowspan=2 width='5%' ".$bgclr.">".$_SESSION['lang']['noakun']."</td>
			<td rowspan=2 width='5%' ".$bgclr.">".$_SESSION['lang']['jatuhtempo']."</td>
			<td rowspan=2 width='7%' ".$bgclr.">".$_SESSION['lang']['sebelum'].' '.$_SESSION['lang']['jatuhtempo']."</td>
			<td colspan=4 width='24%' ".$bgclr.">".$_SESSION['lang']['sesudah'].' '.$_SESSION['lang']['jatuhtempo']."</td>
			<td rowspan=2 width='7%' ".$bgclr.">".$_SESSION['lang']['dibayar']."</td>
			<td rowspan=2 width='5%' ".$bgclr.">".$_SESSION['lang']['tanggalbayar']."</td>
			<td rowspan=2 width='7%' ".$bgclr.">".$_SESSION['lang']['selisih']."</td>
			<td rowspan=2 width='5%' ".$bgclr.">".$_SESSION['lang']['noakun']."</td>
		</tr>
        <tr>
			<td width='6%' ".$bgclr.">1-30 ".$_SESSION['lang']['hari']."</td>
			<td width='6%' ".$bgclr.">31-60 ".$_SESSION['lang']['hari']."</td>
			<td width='6%' ".$bgclr.">61-90 ".$_SESSION['lang']['hari']."</td>
			<td width='6%' ".$bgclr.">Over 90 ".$_SESSION['lang']['hari']."</td>
		</tr>
		</thead><tbody>";

	#ambil data jurnal PJ ============================================
	$str_2="select a.kodept,a.kodeorg,a.noinvoice,a.nokontrak,a.kodecustomer,c.namacustomer,a.debet,a.tanggal,a.kuantitas,a.nilaiinvoice,a.nilaippn
			,if(a.jatuhtempo='0000-00-00',a.tanggal + INTERVAL 7 DAY,a.jatuhtempo) as jatuhtempo
			,(rupiah1+rupiah2+rupiah3+rupiah4+rupiah5+rupiah6+rupiah7-rupiah8) as nilaiklaim,b.jmlbayar,b.tglbayar
			,(a.nilaiinvoice+nilaippn-rupiah1-rupiah2-rupiah3-rupiah4-rupiah5-rupiah6-rupiah7+rupiah8)-if(isnull(b.jmlbayar),0,b.jmlbayar) as selisih,b.noakun
			from ".$dbname.".keu_penagihanht a
			LEFT JOIN (select x.kodeorg,x.keterangan1,x.noakun,y.tanggal as tglbayar,sum(x.jumlah) as jmlbayar,y.posting 
						from ".$dbname.".keu_kasbankdt x
						LEFT JOIN ".$dbname.".keu_kasbankht y on y.notransaksi=x.notransaksi
						where y.posting='1' and keterangan1<>'' and x.keterangan1<>'0' and (left(x.noakun,3)='113' or left(x.noakun,3)='114')
						GROUP BY x.keterangan1
						ORDER BY x.keterangan1,y.tanggal desc) b on b.keterangan1=a.noinvoice
			left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer
			where a.posting='1' ".$where."
			ORDER BY a.kodept,a.kodeorg,a.kodecustomer,a.tanggal
			";
	//exit('Warning: '.$str_2);
	$res_2=mysql_query($str_2) or die (mysql_error($conn));
	#preview: nampilin detail ================================================================================
	$no=0;
	$gtnilai=0;
	$gtbelum=0;
	$gt01_30=0;
	$gt31_60=0;
	$gt61_90=0;
	$gt91_xx=0;
	$gtbayar=0;
	$gtsaldo=0;
	while($bar_2=mysql_fetch_object($res_2)){
		$no+=1;
		if($bar_2->tglbayar=='' or is_null($bar_2->tglbayar)){
			$tglhitung=date('Y-m-d');
		}else{
			$tglhitung=$bar_2->tglbayar;
		}
		$jarak = (strtotime($tglhitung)-strtotime($bar_2->jatuhtempo))/60/60/24;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td align='center'>".$bar_2->kodeorg."</td>
					<td align='left'>".$bar_2->namacustomer."</td>
					<td align='center'>".$bar_2->tanggal."</td>
					<td align='center'>".$bar_2->noinvoice."</td>
					<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim,2)."</td>
					<td align='left'>".$bar_2->debet."</td>
					<td align='left'>".$bar_2->jatuhtempo."</td>";
		if($tglhitung<=$bar_2->jatuhtempo){
			$stream.="<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn,2)."</td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
		}else if($jarak<=30){
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn,2)."</td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
		}else if($jarak<=60){
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn,2)."</td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
		}else if($jarak<=90){
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn,2)."</td>";
			$stream.="<td align='right'></td>";
		}else{
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'>".number_format($bar_2->nilaiinvoice+$bar_2->nilaippn,2)."</td>";
		}
		$stream.="	<td align='right'>".number_format($bar_2->jmlbayar,2)."</td>
					<td align='center'>".$bar_2->tglbayar."</td>
					<td align='right'>".number_format($bar_2->selisih,2)."</td>
					<td align='center'>".$bar_2->noakun."</td>
				</tr>";
		$gtnilai+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gtbelum+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gt01_30+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gt31_60+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gt61_90+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gt91_xx+=($bar_2->nilaiinvoice+$bar_2->nilaippn-$bar_2->nilaiklaim);
		$gtbayar+=$bar_2->jmlbayar;
		$gtsaldo+=($bar_2->selisih<0 ? 0 : $bar_2->selisih);
	}
	if($gtnilai!=0){
		$stream.="<tr bgcolor='#00FFFF' class=rowcontent>
					<td colspan=5 bgcolor='#00FFFF' align='center'>Grand Total</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtnilai,2)."</td>
					<td colspan=1 bgcolor='#00FFFF' align='center'></td>
					<td colspan=1 bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtbelum,2)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gt01_30,2)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gt31_60,2)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gt61_90,2)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gt91_xx,2)."</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtbayar,2)."</td>
					<td colspan=1 bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtsaldo,2)."</td>
					<td colspan=1 bgcolor='#00FFFF' align='center'></td>
				</tr>";
	}
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
