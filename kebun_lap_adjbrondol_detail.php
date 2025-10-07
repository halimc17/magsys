<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept0','');
	$kodeunit=checkPostGet('kodeunit0','');
	$kodedivisi=checkPostGet('kodedivisi0','');
	$kodeblok=checkPostGet('kodeblok0','');
	$jenis=checkPostGet('jenis0','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal10',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal20',''));

	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			exit('Warning : PT tidak boleh kosong...!');
		}
	}

	switch($proses){
		case 'getUnit':
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kodept."' and detail='1'
					and kodeorganisasi like '%E' order by kodeorganisasi";
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$rUnit['kodeorganisasi'].">".$rUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;

		case 'getDivisi':
			$whrdiv="";
			if($kodept!=''){
				$whrdiv=" and left(kodeorganisasi,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
			}
			if($kodeunit!=''){
				$whrdiv=" and kodeorganisasi like '".$kodeunit."%'";
			}
			$sDiv ="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and detail='1' ".$whrdiv." order by kodeorganisasi";
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' ".$whrdiv." order by namaorganisasi";
			//exit('Warning: '.$sDiv.' // '.$sBlok);
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

	#Filter parameter where 
	$where="";
	if($kodept!='' and ($kodeunit=='' or $kodedivisi=='' or $kodeblok=='')){
		$where.=" and left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
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

	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr class=rowheader>
			<td style='width:40px;' ".$bgclr.">No</td>
			<td style='width:40px;' ".$bgclr.">".$_SESSION['lang']['unit']."</td>
			<td style='width:60px;' ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td style='width:200px;' ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td style='width:70px;' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td style='width:60px;' ".$bgclr.">".$_SESSION['lang']['waktu']."</td>
			<td style='width:50px;' ".$bgclr.">".$_SESSION['lang']['jenis']."</td>
			<td style='width:100px;' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
			<td style='width:480px;' ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead><tbody>";

	#ambil data Adjustmen Panen
	$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".kebun_adjbrondol a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where true ".$where." 
			ORDER BY a.kodeorg,a.tanggal,a.waktu,a.jenis
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$nu=0;
	$no=0;
	$kddiv='';
	$stkg=0;
	$gtkg=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
	  if($jenis!=''){
		if($no!=1 and substr($bar->kodeorg,0,6)!=$kddiv){
			$nu+=1;
			$stream.="<tr class=rowcontent>";
			$stream.="
				<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkg,2)."</td>
				<td bgcolor='#DEDEDE' align='right'></td>
				";
			$stream.="</tr>";
			$stkg=0;
		}
	  }
		$stream.="<tr class=rowcontent>";
		$stream.="
				<td align='center'>".$no."</td>
				<td align='center'>".substr($bar->kodeorg,0,4)."</td>
				<td align='center'>".substr($bar->kodeorg,0,6)."</td>
				<td align='left'>".$bar->namablok."</td>
				<td align='center'>".$bar->tanggal."</td>
				<td align='center'>".$bar->waktu."</td>
				<td align='left'>".$bar->jenis."</td>
				<td align='right'>".number_format($bar->kg,2)."</td>
				<td align='left'>".$bar->keterangan."</td>
				";
		$stream.="</tr>";
		$kddiv=substr($bar->kodeorg,0,6);
		$stkg+=$bar->kg;
		$gtkg+=$bar->kg;
	}
  if($jenis!=''){
	$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
	$stream.="	<td colspan=7 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkg,2)."</td>
				<td bgcolor='#DEDEDE' align='right'></td>
			";
	$stream.="</tr>";
	$nu+=1;
	if($nu>1){
		$stream.="<tr bgcolor='#CEDECE' class=rowcontent>";
		$stream.="	<td colspan=7 bgcolor='#CEDECE' align='center'>Grand Total</td>
					<td bgcolor='#CEDECE' align='right'>".number_format($gtkg,2)."</td>
					<td bgcolor='#CEDECE' align='right'></td>
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