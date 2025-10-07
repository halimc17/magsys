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
	$periode=checkPostGet('periode','');
	if($periode!=''){
		if(substr($periode,5,2)=='01'){
			$perlalu=(substr($periode,0,4)-1)."-12";
		}else{
			$perlalu=substr($periode,0,4)."-".sprintf("%02d",substr($periode,5,2)-1);
		}
	//}else{
	//	exit('Warning : Periode tidak boleh kosong...!');
	}

	#Filter parameter where 
	$whererll="";
	$wherepnn="";
	$wherekrm="";
	$whererst="";
	if($kodeunit!=''){
		$whererll.=" and a.kodeorg like '".$kodeunit."%'";
		$wherepnn.=" and a.kodeorg like '".$kodeunit."%'";
		$wherekrm.=" and a.blok like '".$kodeunit."%'";
		$whererst.=" and a.kodeorg like '".$kodeunit."%'";
	}
	if($kodedivisi!=''){
		$whererll.=" and a.kodeorg like '".$kodedivisi."%'";
		$wherepnn.=" and a.kodeorg like '".$kodedivisi."%'";
		$wherekrm.=" and a.blok like '".$kodedivisi."%'";
		$whererst.=" and a.kodeorg like '".$kodedivisi."%'";
	}
	if($kodeblok!=''){
		$whererll.=" and a.kodeorg = '".$kodeblok."'";
		$wherepnn.=" and a.kodeorg = '".$kodeblok."'";
		$wherekrm.=" and a.blok = '".$kodeblok."'";
		$whererst.=" and a.kodeorg = '".$kodeblok."'";
	}
	if($periode!=''){
		$whererll.=" and a.tanggal like '".$perlalu."%'";
		$wherepnn.=" and a.tanggal like '".$periode."%'";
		$wherekrm.=" and a.tanggal like '".$periode."%'";
		$whererst.=" and a.tanggal like '".$periode."%'";
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
			break;

		case 'getBlok':
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and detail='1' 
					and kodeorganisasi like '".$kodeunit."%' and kodeorganisasi like '".$kodedivisi."%' order by namaorganisasi";
			$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
			$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
			}
			echo $optBlok;
			break;

		default:
			break;
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
			<td rowspan=2 ".$bgclr.">No</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['periode']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['kodeblok']."</td>
			<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['blok']."</td>
			<td colspan=2 ".$bgclr.">".$_SESSION['lang']['lalu']."</td>
			<td colspan=2 ".$bgclr.">".$_SESSION['lang']['panen']."</td>
			<td colspan=2 ".$bgclr.">".$_SESSION['lang']['kirim']."</td>
			<td colspan=2 ".$bgclr.">Afkir</td>
			<td colspan=2 ".$bgclr.">Restan</td>
		</tr>
        <tr>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jjg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jjg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jjg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jjg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['jjg']."</td>
			<td width='8%' ".$bgclr.">".$_SESSION['lang']['kg']."</td>
		</tr></thead><tbody>";

	#ambil data panen
	$str="select a.kodeorg as blok,b.namaorganisasi as namablok,sum(a.hasilkerja) as jjgpanen,sum(a.hasilkerjakg) as kgpanen 
			from ".$dbname.".kebun_prestasi_vw a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where true ".$wherepnn." 
			GROUP BY a.kodeorg
			ORDER BY a.kodeorg
			";
	//echo 'Warning: '.$str;
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$blok[$bar->blok]=$bar->blok;
		$namablok[$bar->blok]=$bar->namablok;
		$jjgpanen[$bar->blok]=$bar->jjgpanen;
		$kgpanen[$bar->blok]=$bar->kgpanen;
		$bjrpanen[$bar->blok]=$bar->kgpanen/$bar->jjgpanen;
		$bjrset=$bjrpanen[$bar->blok];
	}
	#ambil data spatbs
	$str="select a.blok,b.namaorganisasi as namablok,sum(a.jjg) as jjgkirim,sum(a.kgbjr) as kgkirim from ".$dbname.".kebun_spb_vw a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.blok
			where true ".$wherekrm." 
			GROUP BY a.blok
			ORDER BY a.blok
			";
	//echo 'Warning: '.$str;
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$blok[$bar->blok]=$bar->blok;
		$namablok[$bar->blok]=$bar->namablok;
		$jjgkirim[$bar->blok]=$bar->jjgkirim;
		if($jjgpanen[$bar->blok]>0){
			$bjrkirim[$bar->blok]=$bjrpanen[$bar->blok];
		}else{
			$bjrkirim[$bar->blok]=$bar->kgkirim/$bar->jjgkirim;
		}
		$bjrset=$bjrkirim[$bar->blok];
		$kgkirim[$bar->blok]=$bar->jjgkirim*$bjrkirim[$bar->blok];
	}
	#ambil data restan
	$str="select a.kodeorg as blok,b.namaorganisasi as namablok,sum(a.jjgkirim) as jjgafkir from ".$dbname.".kebun_restan a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where true ".$whererst." 
			GROUP BY a.kodeorg
			ORDER BY a.kodeorg
			";
	//echo 'Warning: '.$bjrset;
	//exit();
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$blok[$bar->blok]=$bar->blok;
		$namablok[$bar->blok]=$bar->namablok;
		$jjgafkir[$bar->blok]=$bar->jjgafkir;
		if($jjgpanen[$bar->blok]>0){
			$bjrafkir[$bar->blok]=$bjrpanen[$bar->blok];
			$bjrset=$bjrafkir[$bar->blok];
		}elseif($jjgkirim[$bar->blok]>0){
			$bjrafkir[$bar->blok]=$bjrkirim[$bar->blok];
			$bjrset=$bjrafkir[$bar->blok];
		}else{
			$bjrafkir[$bar->blok]=$bjrset;
		}
		$kgafkir[$bar->blok]=$bar->jjgafkir*$bjrafkir[$bar->blok];
	}
	#ambil data restan lalu
	$str="select a.kodeorg as blok,b.namaorganisasi as namablok,sum(a.jjgpanen) as jjgsisalalu,sum(a.jjgkirim) as jjgafkirlalu 
			from ".$dbname.".kebun_restan a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			where true ".$whererll." and a.jjgpanen-a.jjgkirim<>0
			GROUP BY a.kodeorg
			ORDER BY a.kodeorg
			";
	//echo 'Warning: '.$str;
	//exit();
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$blok[$bar->blok]=$bar->blok;
		$namablok[$bar->blok]=$bar->namablok;
		$jjglalu[$bar->blok]=$bar->jjgsisalalu-$bar->jjgafkirlalu;
		if($jjgpanen[$bar->blok]>0){
			$bjrlalu[$bar->blok]=$bjrpanen[$bar->blok];
			$bjrset=$bjrafkir[$bar->blok];
		}elseif($jjgkirim[$bar->blok]>0){
			$bjrlalu[$bar->blok]=$bjrkirim[$bar->blok];
			$bjrset=$bjrafkir[$bar->blok];
		}else{
			$bjrlalu[$bar->blok]=$bjrset;
		}
		$kglalu[$bar->blok]=$jjglalu[$bar->blok]*$bjrlalu[$bar->blok];
	}
	$no=0;
	$kddiv='';
	$stjjglalu=0;
	$stkglalu=0;
	$stjjgpanen=0;
	$stkgpanen=0;
	$stjjgkirim=0;
	$stkgkirim=0;
	$stjjgafkir=0;
	$stkgafkir=0;
	$stjjgrestan=0;
	$stkgrestan=0;
	$gtjjglalu=0;
	$gtkglalu=0;
	$gtjjgpanen=0;
	$gtkgpanen=0;
	$gtjjgkirim=0;
	$gtkgkirim=0;
	$gtjjgafkir=0;
	$gtkgafkir=0;
	$gtjjgrestan=0;
	$gtkgrestan=0;
	sort($blok);
	foreach($blok as $kdblok){
		$no+=1;
		if($no!=1 and substr($kdblok,0,6)!=$kddiv){
			$stream.="<tr class=rowcontent>";
			$stream.="
				<td colspan=4 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjglalu,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkglalu,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgpanen,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkgpanen,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgkirim,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkgkirim,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgafkir,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkgafkir,2)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgrestan,0)."</td>
				<td bgcolor='#DEDEDE' align='right'>".number_format($stkgrestan,2)."</td>
				";
			$stream.="</tr>";
			$no=1;
			$stjjglalu=0;
			$stkglalu=0;
			$stjjgpanen=0;
			$stkgpanen=0;
			$stjjgkirim=0;
			$stkgkirim=0;
			$stjjgafkir=0;
			$stkgafkir=0;
			$stjjgrestan=0;
			$stkgrestan=0;
		}
		$jjgrestan[$kdblok]=$jjglalu[$kdblok]+$jjgpanen[$kdblok]-$jjgkirim[$kdblok]-$jjgafkir[$kdblok];
		if($jjgpanen[$kdblok]>0){
			$bjrrestan[$kdblok]=$bjrpanen[$kdblok];
			$bjrset=$bjrrestan[$kdblok];
		}elseif($jjgkirim[$kdblok]>0){
			$bjrrestan[$kdblok]=$bjrkirim[$kdblok];
			$bjrset=$bjrrestan[$kdblok];
		}elseif($jjgafkir[$kdblok]>0){
			$bjrrestan[$kdblok]=$bjrafkir[$kdblok];
			$bjrset=$bjrrestan[$kdblok];
		}elseif($jjglalu[$kdblok]>0){
			$bjrrestan[$kdblok]=$bjrlalu[$kdblok];
			$bjrset=$bjrrestan[$kdblok];
		}else{
			$bjrrestan[$kdblok]=$bjrset;
		}
		$kgrestan[$kdblok] =$jjgrestan[$kdblok]*$bjrrestan[$kdblok];
		$stream.="<tr class=rowcontent>";
		$stream.="
				<td align='center'>".$no."</td>
				<td align='center'>".$periode."</td>
				<td align='left'>".$kdblok."</td>
				<td align='left'>".$namablok[$kdblok]."</td>
				<td align='right'>".number_format($jjglalu[$kdblok],0)."</td>
				<td align='right'>".number_format($kglalu[$kdblok],2)."</td>
				<td align='right'>".number_format($jjgpanen[$kdblok],0)."</td>
				<td align='right'>".number_format($kgpanen[$kdblok],2)."</td>
				<td align='right'>".number_format($jjgkirim[$kdblok],0)."</td>
				<td align='right'>".number_format($kgkirim[$kdblok],2)."</td>
				<td align='right'>".number_format($jjgafkir[$kdblok],0)."</td>
				<td align='right'>".number_format($kgafkir[$kdblok],2)."</td>
				<td align='right'>".number_format($jjgrestan[$kdblok],0)."</td>
				<td align='right'>".number_format($kgrestan[$kdblok],2)."</td>
				";
		$stream.="</tr>";
		$kddiv=substr($kdblok,0,6);
		$stjjglalu+=$jjglalu[$kdblok];
		$stkglalu+=$kglalu[$kdblok];
		$stjjgpanen+=$jjgpanen[$kdblok];
		$stkgpanen+=$kgpanen[$kdblok];
		$stjjgkirim+=$jjgkirim[$kdblok];
		$stkgkirim+=$kgkirim[$kdblok];
		$stjjgafkir+=$jjgafkir[$kdblok];
		$stkgafkir+=$kgafkir[$kdblok];
		$stjjgrestan+=$jjgrestan[$kdblok];
		$stkgrestan+=$kgrestan[$kdblok];
		$gtjjglalu+=$jjglalu[$kdblok];
		$gtkglalu+=$kglalu[$kdblok];
		$gtjjgpanen+=$jjgpanen[$kdblok];
		$gtkgpanen+=$kgpanen[$kdblok];
		$gtjjgkirim+=$jjgkirim[$kdblok];
		$gtkgkirim+=$kgkirim[$kdblok];
		$gtjjgafkir+=$jjgafkir[$kdblok];
		$gtkgafkir+=$kgafkir[$kdblok];
		$gtjjgrestan+=$jjgrestan[$kdblok];
		$gtkgrestan+=$kgrestan[$kdblok];
	}
	if($no>1){
		$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
		$stream.="
			<td colspan=4 bgcolor='#DEDEDE' align='center'>Total ".$kddiv."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stjjglalu,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stkglalu,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgpanen,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stkgpanen,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgkirim,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stkgkirim,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgafkir,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stkgafkir,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stjjgrestan,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($stkgrestan,2)."</td>
			";
		$stream.="</tr>";
		$stream.="<tr bgcolor='#DEDEDE' class=rowcontent>";
		$stream.="
			<td colspan=4 bgcolor='#DEDEDE' align='center'>Grand Total</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjglalu,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtkglalu,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjgpanen,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtkgpanen,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjgkirim,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtkgkirim,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjgafkir,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtkgafkir,2)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtjjgrestan,0)."</td>
			<td bgcolor='#DEDEDE' align='right'>".number_format($gtkgrestan,2)."</td>
			";
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			if($periode==''){
				exit('Warning : Periode tidak boleh kosong...!');
			}
			echo $stream;
        break;
        case 'excel':
			if($periode==''){
				exit('Warning : Periode tidak boleh kosong...!');
			}
			$judul=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['lapRestan'];
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