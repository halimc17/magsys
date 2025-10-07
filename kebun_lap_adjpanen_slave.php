<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodeunit=checkPostGet('kodeunit','');
	$kodedivisi=checkPostGet('kodedivisi','');
	$kodeblok=checkPostGet('kodeblok','');
	$jenis=checkPostGet('jenis','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	if($proses=='preview' or $proses=='excel'){
		if($kodeunit==''){
			exit('Warning : Unit tidak boleh kosong...!');
		}
	}

	#Filter parameter where 
	$where="";
	if($kodeunit!=''){
		$where.=" and a.kodeorg like '".$kodeunit."%'";
	}
	if($kodedivisi!=''){
		$where.=" and a.kodeorg like '".$kodedivisi."%'";
	}
	if($kodeblok!=''){
		$where.=" and a.kodeorg = '".$kodeblok."'";
	}
	if($jenis!=''){
		$where.=" and a.jenis like '%".$jenis."%'";
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
		case 'getDivisi':
			$sDiv ="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1'
					and kodeorganisasi like '".$kodeunit."%' order by kodeorganisasi";
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' 
					and kodeorganisasi like '".$kodeunit."%' order by namaorganisasi";
			$qDiv =mysql_query($sDiv) or die(mysql_error($conn));
			$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rDiv=mysql_fetch_assoc($qDiv)){
				$optDivisi.="<option value=".$rDiv['kodeorganisasi'].">".$rDiv['namaorganisasi']."</option>";
			}

			$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
			$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
			}

			echo $optDivisi."###".$optBlok;
			exit;

		case 'getBlok':
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' 
					and kodeorganisasi like '".$kodeunit."%' and kodeorganisasi like '".$kodedivisi."%' order by namaorganisasi";
			$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
			$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
			}
			echo $optBlok;
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
			<td width='3%' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td width='6%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td width='3%' ".$bgclr.">".$_SESSION['lang']['waktu']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jenis']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['janjang']."</td>
			<td width='5%' ".$bgclr.">".$_SESSION['lang']['kgtbs']."</td>
			<td ".$bgclr.">Driver Langsir</td>
			<td ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['catatan']."</td>
		</tr>
		</thead><tbody>";

	#ambil data Adjustmen Panen
	$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".kebun_adjpanen a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where true ".$where." 
			ORDER BY a.kodeorg,a.tanggal,a.waktu,a.jenis
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$nu=0;
	$no=0;
	$kddiv='';
	$stjjg=0;
	$stkg=0;
	$gtjjg=0;
	$gtkg=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
	  if($jenis!=''){
		if($no!=1 and substr($bar->kodeorg,0,6)!=$kddiv){
			$nu+=1;
			$stream.="<tr class=rowcontent>";
			$stream.="
				<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjg,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkg,2)."</td>
				<td bgcolor='#DEDEDE' align='right'></td>
				<td bgcolor='#DEDEDE' align='right'></td>
				<td bgcolor='#DEDEDE' align='right'></td>
				";
			$stream.="</tr>";
			$stjjg=0;
			$stkg=0;
		}
	  }
		$stream.="<tr class=rowcontent>";
		$stream.="
				<td align='center'>".$no."</td>
				<td align='center'>".substr($bar->kodeorg,0,4)."</td>
				<td align='center'>".substr($bar->kodeorg,0,6)."</td>
				<td align='center'>".$bar->namablok."</td>
				<td align='center'>".$bar->tanggal."</td>
				<td align='center'>".$bar->waktu."</td>
				<td align='left'>".$bar->jenis."</td>
				<td align='right'>".number_format($bar->janjang,0)."</td>
				<td align='right'>".number_format($bar->kg,2)."</td>
				<td align='left'>".$bar->supirlangsir."</td>
				<td align='left'>".$bar->keterangan."</td>
				<td align='left'>".$bar->catatan."</td>
				";
		$stream.="</tr>";
		$kddiv=substr($bar->kodeorg,0,6);
		$stjjg+=$bar->janjang;
		$stkg+=$bar->kg;
		$gtjjg+=$bar->janjang;
		$gtkg+=$bar->kg;
	}
  if($jenis!=''){
	$nu+=1;
	$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
	$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjg,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkg,2)."</td>
				<td bgcolor='#DEDEDE' align='right'></td>
				<td bgcolor='#DEDEDE' align='right'></td>
				<td bgcolor='#DEDEDE' align='right'></td>
			";
	$stream.="</tr>";
	if($nu>1){
		$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
		$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Grand Total</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjg,0)."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($gtkg,2)."</td>
					<td bgcolor='#DEDEDE' align='right'></td>
					<td bgcolor='#DEDEDE' align='right'></td>
					<td bgcolor='#DEDEDE' align='right'></td>
				";
		$stream.="</tr>";
	}
  }
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Adjustment '.$_SESSION['lang']['panen'];
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.$judul.'</h2>'.$stream;
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