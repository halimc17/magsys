<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$kodeakun=checkPostGet('noakun','');
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
	$where2="";
	if($kodept!=''){
		$where.=" and substr(a.nojurnal,10,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		$where2.=" and substr(a.nojurnal,10,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where=" and substr(a.nojurnal,10,4) = '".$kodeunit."'";
		$where2=" and substr(a.nojurnal,10,4) = '".$kodeunit."'";
	}
	if($kodeakun!=''){
		$where.=" and a.noakun = '".$kodeakun."'";
		$where2.=" and a.noakun = '".$kodeakun."'";
	}else{
		$where.=" and a.noakun in ('1180300','1180400')";
//		$where2.=" and a.noakun in ('1180300','1180400')";
		$where2.=" and a.noakun like '118%'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$where2.=" and a.tanggal>='".$tanggal1."'";
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

	#ambil data jurnal UM ============================================
	$str_1="select a.*,b.namakaryawan from ".$dbname.".keu_jurnaldt a
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.nik
			where a.jumlah>0 ".$where."
			ORDER BY a.tanggal,a.noreferensi desc";
	//exit('Warning: '.$str_1);
	$res_1=mysql_query($str_1);
	while($bar_1=mysql_fetch_object($res_1)){
		$unit_1[$bar_1->noreferensi]=substr($bar_1->nojurnal,9,4);
		$nojurnal_1[$bar_1->noreferensi]=$bar_1->nojurnal;
		$tanggal_1[$bar_1->noreferensi]=$bar_1->tanggal;
		$noakun_1[$bar_1->noreferensi]=$bar_1->noakun;
		$keterangan_1[$bar_1->noreferensi]=$bar_1->keterangan;
		$debet_1[$bar_1->noreferensi]+=$bar_1->jumlah;
		$noreferensi_1[$bar_1->noreferensi]=$bar_1->noreferensi;
		$namakaryawan_1[$bar_1->noreferensi]=$bar_1->namakaryawan;
	}

	#ambil data jurnal PJ ============================================
	$str_2="select a.* from ".$dbname.".keu_jurnaldt a
			where a.jumlah<=0 ".$where2."
			ORDER BY a.tanggal,a.noreferensi desc";
	//exit('Warning: '.$str_2);
	$res_2=mysql_query($str_2);
	while($bar_2=mysql_fetch_object($res_2)){
		$unit_2[$bar_2->nodok]=substr($bar_2->nojurnal,9,4);
		$nojurnal_2[$bar_2->nodok]=$bar_2->nojurnal;
		$tanggal_2[$bar_2->nodok]=$bar_2->tanggal;
		$noakun_2[$bar_2->nodok]=$bar_2->noakun;
		$keterangan_2[$bar_2->nodok]=$bar_2->keterangan;
		$kredit_2[$bar_2->nodok]+=$bar_2->jumlah;
		$noreferensi_2[$bar_2->nodok]=$bar_2->noreferensi;
		$namakaryawan_2[$bar_2->nodok]=$bar_2->namakaryawan;
		$nodok_2[$bar_2->nodok]=$bar_2->nodok;
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
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['nojurnal']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['noakun']."</td>
			<td width='14%' ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>
			<td width='7%' ".$bgclr.">".$_SESSION['lang']['debet']."</td>
			<td width='7%' ".$bgclr.">".$_SESSION['lang']['noreferensi']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['namakaryawan']."</td>
			<td width='4%' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['nojurnal']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['noakun']."</td>
			<td width='7%' ".$bgclr.">".$_SESSION['lang']['kredit']."</td>
			<td width='7%' ".$bgclr.">".$_SESSION['lang']['noreferensi']."</td>
			<td width='7%' ".$bgclr.">".$_SESSION['lang']['selisih']."</td>
		</tr>
		</thead><tbody>";

	#preview: nampilin detail ================================================================================
	$no=0;
	$gtdebet=0;
	$gtkredit=0;
	foreach($noreferensi_1 as $nodokref=>$noref){
		if($tipereal=='Sudah'){
			if($noreferensi_2[$noref]=='' or is_null($noreferensi_2[$noref])){
				continue;
			}
		}else if($tipereal=='Belum'){
			if($noreferensi_2[$noref]!=''){
				continue;
			}
		}
		$no+=1;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td align='center'>".$unit_1[$noref]."</td>
					<td align='left'>".$nojurnal_1[$noref]."</td>
					<td align='center'>".$tanggal_1[$noref]."</td>
					<td align='center'>".$noakun_1[$noref]."</td>
					<td align='left'>".$keterangan_1[$noref]."</td>
					<td align='right'>".number_format($debet_1[$noref],2)."</td>
					<td align='left'>".$noreferensi_1[$noref]."</td>
					<td align='left'>".$namakaryawan_1[$noref]."</td>
					<td align='center'>".$unit_2[$noref]."</td>
					<td align='left'>".$nojurnal_2[$noref]."</td>
					<td align='center'>".$tanggal_2[$noref]."</td>
					<td align='center'>".$noakun_2[$noref]."</td>
					<td align='right'>".number_format($kredit_2[$noref],2)."</td>
					<td align='left'>".$noreferensi_2[$noref]."</td>
					<td align='right'>".number_format($debet_1[$noref]+$kredit_2[$noref],2)."</td>
				</tr>";
		$gtdebet+=$debet_1[$noref];
		$gtkredit+=$kredit_2[$noref];
	}
	if($gtdebet+$gtkredit!=0){
		$stream.="<tr bgcolor='#00FFFF' class=rowcontent>
					<td colspan=6 bgcolor='#00FFFF' align='center'>Grand Total</td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtdebet,2)."</td>
					<td colspan=6 bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtkredit,2)."</td>
					<td colspan=1 bgcolor='#00FFFF' align='center'></td>
					<td bgcolor='#00FFFF' align='right'>".number_format($gtdebet+$gtkredit,2)."</td>
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
