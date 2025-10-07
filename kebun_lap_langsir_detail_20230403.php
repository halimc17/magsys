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
	$where2="";
	if($kodept!='' and ($kodeunit=='' or $kodedivisi=='' or $kodeblok=='')){
		$where.=" and left(a.kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		$where2.=" and left(a.blok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where.=" and a.kodeblok like '".$kodeunit."%'";
		$where2.=" and a.blok like '".$kodeunit."%'";
	}
	if($kodedivisi!=''){
		$where.=" and a.kodeblok like '".$kodedivisi."%'";
		$where2.=" and a.blok like '".$kodedivisi."%'";
	}
	if($kodeblok!=''){
		$where.=" and a.kodeblok = '".$kodeblok."'";
		$where2.=" and a.blok = '".$kodeblok."'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$where2.=" and b.tanggal>='".$tanggal1."' and b.tanggal<='".$tanggal2."'";
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
			<td style='width:60px;' ".$bgclr.">".$_SESSION['lang']['divisi']."</td>
			<td style='width:200px;' ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td style='width:75px;' ".$bgclr.">".$_SESSION['lang']['intiplasma']."</td>
			<td style='width:75px;' ".$bgclr.">".$_SESSION['lang']['tahuntanam']."</td>
			<td style='width:75px;' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td style='width:100px;' ".$bgclr.">".$_SESSION['lang']['kg']." Langsir</td>
			<td style='width:100px;' ".$bgclr.">".$_SESSION['lang']['kgwb']."</td>
			<td style='width:75px;' ".$bgclr.">".$_SESSION['lang']['varian']." (%)</td>
		</tr>
		</thead><tbody>";

	#ambil data langsir BASPK
	$str="select a.kodeblok,a.tanggal,a.hasilkerjarealisasi,c.namaorganisasi as namablok,d.intiplasma,d.tahuntanam from ".$dbname.".log_baspk a
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeblok
			left join ".$dbname.".setup_blok d on d.kodeorg=a.kodeblok
			where a.kodekegiatan='611020201' and a.statusjurnal='1' ".$where." 
			ORDER BY a.kodeblok,a.tanggal";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	while($bar=mysql_fetch_assoc($res)){
		$xtgl=$bar['tanggal'];
		$xblok=$bar['kodeblok'];
		$tgldata[$xtgl]=$bar['tanggal'];
		$blokdata[$xblok]=$bar['kodeblok'];
		$namablok[$xblok]=$bar['namablok'];
		$intiplasma[$xblok]=($bar['intiplasma'] == 'P' ? 'Plasma' : 'Inti');
		$tahuntanam[$xblok]=$bar['tahuntanam'];
		$tanggalkg[$xblok][$xtgl]=$bar['tanggal'];
		$kglangsir[$xblok][$xtgl]+=$bar['hasilkerjarealisasi'];
	}

	#ambil data SPATBS
	$str="select a.blok as kodeblok,b.tanggal,a.kgwb,c.namaorganisasi as namablok,d.intiplasma,d.tahuntanam from ".$dbname.".kebun_spbdt a
			left join ".$dbname.".kebun_spbht b on b.nospb=a.nospb
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.blok
			left join ".$dbname.".setup_blok d on d.kodeorg=a.blok
			where b.posting='1' ".$where2." 
			ORDER BY a.blok,b.tanggal";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	while($bar=mysql_fetch_assoc($res)){
		$xtgl=$bar['tanggal'];
		$xblok=$bar['kodeblok'];
		$tgldata[$xtgl]=$bar['tanggal'];
		$blokdata[$xblok]=$bar['kodeblok'];
		$namablok[$xblok]=$bar['namablok'];
		$intiplasma[$xblok]=($bar['intiplasma'] == 'P' ? 'Plasma' : 'Inti');
		$tahuntanam[$xblok]=$bar['tahuntanam'];
		$tanggalkg[$xblok][$xtgl]=$bar['tanggal'];
		$kgwb[$xblok][$xtgl]+=$bar['kgwb'];
	}
	$nu=0;
	$no=0;
	$kddiv='';
	$stkglangsir=0;
	$stkgwb=0;
	$gtkglangsir=0;
	$gtkgwb=0;
	asort($blokdata);
	foreach($blokdata as $kdblok=>$blok){
		if($no!=0 and substr($blok,0,6)!=$kddiv){
			$nu+=1;
			$stvarian=($stkgwb==0 ? 0 : $stkglangsir/$stkgwb);
			$stream.="<tr class=rowcontent>";
			$stream.="
				<td colspan=6 bgcolor='#DEDEDE' align='center'>Sub Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkglangsir,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkgwb,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stvarian,2)."</td>
				";
			$stream.="</tr>";
			$stkglangsir=0;
			$stkgwb=0;
		}
		asort($tanggalkg[$blok]);
		foreach($tanggalkg[$blok] as $tglblok=>$tgl){
			$no+=1;
			$varian=($kgwb[$blok][$tgl]==0 ? 0 : $kglangsir[$blok][$tgl]/$kgwb[$blok][$tgl]);
			$stream.="<tr class=rowcontent>";
			$stream.="
						<td align='center'>".$no."</td>
						<td align='center'>".substr($blok,0,6)."</td>
						<td align='left'>".$namablok[$blok]."</td>
						<td align='center'>".$intiplasma[$blok]."</td>
						<td align='center'>".$tahuntanam[$blok]."</td>
						<td align='center'>".$tgl."</td>
						<td align='right'>".number_format($kglangsir[$blok][$tgl],2)."</td>
						<td align='right'>".number_format($kgwb[$blok][$tgl],2)."</td>
						<td align='right'>".number_format($varian,2)."</td>
						";
			$stream.="</tr>";
			$kddiv=substr($blok,0,6);
			$stkglangsir+=$kglangsir[$blok][$tgl];
			$stkgwb+=$kgwb[$blok][$tgl];
			$gtkglangsir+=$kglangsir[$blok][$tgl];
			$gtkgwb+=$kgwb[$blok][$tgl];
		}
	}
	if($no>0){
		$stvarian=($stkgwb==0 ? 0 : $stkglangsir/$stkgwb);
		$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
		$stream.="	<td bgcolor='#DEDEDE' align='center' colspan=6>Total ".$kddiv."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stkglangsir,2)."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stkgwb,2)."</td>
					<td bgcolor='#DEDEDE' align='right'>".number_format($stvarian,2)."</td>
				";
		$stream.="</tr>";
	}
	if($nu>1){
		$gtvarian=($gtkgwb==0 ? 0 : $gtkglangsir/$gtkgwb);
		$stream.="<tr bgcolor='#CEDECE' class=rowcontent>";
		$stream.="	<td bgcolor='#CEDECE' align='center' colspan=6>Grand Total</td>
					<td bgcolor='#CEDECE' align='right'>".number_format($gtkglangsir,2)."</td>
					<td bgcolor='#CEDECE' align='right'>".number_format($gtkgwb,2)."</td>
					<td bgcolor='#CEDECE' align='right'>".number_format($gtvarian,2)."</td>
				";
		$stream.="</tr>";
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