<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$kdPT=$_POST['kdPT'];
	$kdKomoditi=$_POST['kdKomoditi'];
	$periode=$_POST['periode'];
	if($proses=='')$proses=$_GET['proses'];
	if($kdPT=='')$kdPT=$_GET['kdPT'];
	if($kdKomoditi=='')$kdKomoditi=$_GET['kdKomoditi'];
	if($periode=='')$periode=$_GET['periode'];

	if($proses=='getUnit'){
		$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and induk='".$kdPT."' order by namaorganisasi asc ";
		//exit('Warning: '.$sOrg);
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optOrg;
		exit;
	}

	$subjudul="";
	$whr="";
	if($kdPT!=''){
		$whr.=" and a.kodept='".$kdPT."'";
		$subjudul.=" ".$kdPT;
	}
	if($kdKomoditi!=''){
		$whr.=" and a.kodebarang='".$kdKomoditi."'";
		//$subjudul.=" ".$kdKomoditi;
	}
	if($periode!=''){
		$whr.=" and left(a.tanggalkontrak,7)<='".$periode."'";
		//$subjudul.=" ".$periode;
	}

	$stream="";
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	$stream.="<B>Outstanding Contract:</B>";

	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td width='3%' ".$bgclr.">No</td>
	        <td width='20%' ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
	        <td width='7%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
	        <td width='6%' ".$bgclr.">Terms</td>
	        <td width='14%' ".$bgclr.">".$_SESSION['lang']['tanggalkirim']."</td>
		    <td width='9%' ".$bgclr.">".$_SESSION['lang']['jumlah']."</td>
			<td width='9%' ".$bgclr.">".$_SESSION['lang']['kirim']."</td>
	        <td width='9%' ".$bgclr.">Outstanding</td>
		    <td width='9%' ".$bgclr.">".$_SESSION['lang']['harga']."/Kg (Excl) </td>
	        <td width='14%' ".$bgclr.">Outstanding ".$_SESSION['lang']['rupiah']."</td>";
	$stream.="</tr></thead><tbody>";

	# preview: nampilin data ================================================================================
	$str="SELECT a.nokontrak,a.tanggalkontrak,upper(d.penjualan) as terms,a.tanggalkirim,a.sdtanggal,a.kuantitaskontrak
			,if(isnull(b.beratbersih),0,b.beratbersih) as beratbersih,a.kuantitaskontrak-if(isnull(b.beratbersih),0,b.beratbersih) as outstanding
			,if(a.ppn=0,a.hargasatuan,a.hargasatuan/1.1) as hargaexcl,c.nokontrak as nokoninv
			from ".$dbname.".pmn_kontrakjual a
			LEFT JOIN (SELECT nokontrak,tanggal,sum(beratbersih) as beratbersih from ".$dbname.".pabrik_timbangan 
						where kodebarang in ('40000001','40000002','40000005') and nokontrak<>''
						GROUP BY nokontrak) b on b.nokontrak=a.nokontrak
			LEFT JOIN (select DISTINCT(nokontrak) as nokontrak from ".$dbname.".keu_penagihanht) c on c.nokontrak=a.nokontrak
			left JOIN ".$dbname.".pmn_5franco d on d.id_franco=a.franco
			where a.kodebarang in ('40000001','40000002','40000005') and tanggalkontrak>='2021-01-01'
				and a.kuantitaskontrak-if(isnull(b.beratbersih),0,b.beratbersih)>10000
				and !ISNULL(c.nokontrak) ".$whr."
			ORDER BY a.tanggalkontrak";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$no=0;
	$tjm_kontrak=0;
	$tjm_kirim=0;
	$tjm_outs=0;
	$trp_outs=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		//$stream.="<tr class=rowcontent title='Click untuk melihat detail.' style=\"cursor: pointer\" onclick=showpopup('".$kodeorg[$notransid]."','".$tanggal[$notransid]."','".$notrans[$notransid]."','',event)>
		$outrupiah=$bar->outstanding*$bar->hargaexcl;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td>".$bar->nokontrak."</td>
					<td align='center'>".$bar->tanggalkontrak."</td>
					<td>".$bar->terms."</td>
					<td align='center'>".$bar->tanggalkirim." - ".$bar->sdtanggal."</td>
					<td align='right'>".number_format($bar->kuantitaskontrak,0,'.',',')."</td>
					<td align='right'>".number_format($bar->beratbersih,0,'.',',')."</td>
					<td align='right'>".number_format($bar->outstanding,0,'.',',')."</td>
					<td align='right'>".number_format($bar->hargaexcl,2,'.',',')."</td>
					<td align='right'>".number_format($outrupiah,2,'.',',')."</td>
				</tr>";
		$tjm_kontrak+=$bar->kuantitaskontrak;
		$tjm_kirim+=$bar->beratbersih;
		$tjm_outs+=$bar->outstanding;
		$trp_outs+=$outrupiah;
	}
	# preview: nampilin sub total ================================================================================
	$thargaexcl=$trp_outs/$tjm_outs;
	$stream.="<tr class=rowcontent>
				<td bgcolor='#FEDEFE' colspan=5 align='center'>Total</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($tjm_kontrak,0,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($tjm_kirim,0,'.',',')."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($tjm_outs,0,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($thargaexcl,2,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($trp_outs,2,'.',',')."</td>
			</tr>";
	$stream.="</tbody></table>";

	# Start: nampilin Stok ================================================================================
	$stream.="<BR><B>Stock Availability:</B>";
	$sStock="select tanggal from ".$dbname.".pabrik_masukkeluartangki order by tanggal desc limit 1";
	$tglStock=date('Y-m-d');
	$qStock=mysql_query($sStock) or die(mysql_error());
	while($rStock=mysql_fetch_assoc($qStock)){
		$tglStock=$rStock['tanggal'];
	}
	$ST01=0;
	$ST02=0;
	$SLST=0;
	$BLK=0;
	$LAIN=0;
	$sStock="select * from ".$dbname.".pabrik_masukkeluartangki where tanggal='".$tglStock."' order by kodetangki";
	$qStock=mysql_query($sStock) or die(mysql_error());
	while($rStock=mysql_fetch_assoc($qStock)){
		if(substr($rStock['kodetangki'],0,4)=='ST01'){
			$ST01+=$rStock['kuantitas'];
		}else if(substr($rStock['kodetangki'],0,4)=='ST02'){
			$ST02+=$rStock['kuantitas'];
		}else if(substr($rStock['kodetangki'],0,4)=='SLST'){
			$SLST+=$rStock['kuantitas'];
		}else if(substr($rStock['kodetangki'],0,3)=='BLK'){
			$BLK+=$rStock['kernelquantity'];
		}else{
			$LAIN+=$rStock['kuantitas']+$rStock['kernelquantity'];
		}
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td width='3%' ".$bgclr.">No</td>
	        <td width='22%' ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
	        <td width='7%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
	        <td width='13%' ".$bgclr.">Storage 01</td>
	        <td width='13%' ".$bgclr.">Storage 02</td>
		    <td width='13%' ".$bgclr.">Luar Storage</td>
			<td width='14%' ".$bgclr.">CPO</td>
	        <td width='15%' ".$bgclr.">Kernel</td>";
	$stream.="</tr></thead><tbody>";
	$StockCPO=$ST01+$ST02+$SLST;
	$stream.="<tr class=rowcontent>
				<td align='center'>A</td>
				<td>Stock</td>
				<td align='center'>".$tglStock."</td>
				<td align='right'>".number_format($ST01,0,'.',',')."</td>
				<td align='right'>".number_format($ST02,0,'.',',')."</td>
				<td align='right'>".number_format($SLST,0,'.',',')."</td>
				<td align='right'>".number_format($StockCPO,0,'.',',')."</td>
				<td align='right'>".number_format($BLK,0,'.',',')."</td>
			</tr>";
	$stream.="</tbody></table>";
	# End: nampilin Stok ================================================================================
	# Start: nampilin External ================================================================================
	$stream.="<BR><B>Stock External:</B>";
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td width='3%' ".$bgclr.">No</td>
	        <td width='20%' ".$bgclr.">".$_SESSION['lang']['NoKontrak']." External</td>
	        <td width='7%' ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
	        <td width='6%' ".$bgclr.">Terms</td>
	        <td width='14%' ".$bgclr.">".$_SESSION['lang']['tanggalkirim']."</td>
		    <td width='9%' ".$bgclr.">".$_SESSION['lang']['jumlah']."</td>
			<td width='9%' ".$bgclr.">".$_SESSION['lang']['kirim']."</td>
	        <td width='9%' ".$bgclr.">Outstanding</td>
		    <td width='9%' ".$bgclr.">".$_SESSION['lang']['harga']."/Kg (Excl) </td>
	        <td width='14%' ".$bgclr.">Outstanding ".$_SESSION['lang']['rupiah']."</td>";
	$stream.="</tr></thead><tbody>";
	$str2="SELECT a.*,b.mintgl,b.maxtgl,if(isnull(b.beratkirim),0,b.beratkirim) as beratkirim,a.qtykontrak-if(isnull(b.beratkirim),0,b.beratkirim) as extoutstanding 
			FROM ".$dbname.".pmn_traderht a
			LEFT JOIN  (SELECT nokontrakext,sum(beratbersih) as beratkirim,left(min(tanggal),10) as mintgl,left(max(tanggal),10) as maxtgl 
						FROM ".$dbname.".pmn_traderdt WHERE posting=1
						GROUP BY nokontrakext) b on b.nokontrakext=a.nokontrakext
			WHERE a.qtykontrak-if(isnull(b.beratkirim),0,b.beratkirim)<>0
			ORDER BY a.tanggalext";
	//exit('Warning : '.$str2);
	$res2=mysql_query($str2);
	$no=0;
	$tjm_kontrak=0;
	$tjm_kirim=0;
	$tjm_outs=0;
	$trp_outs=0;
	while($bar2=mysql_fetch_object($res2)){
		$no+=1;
		//$stream.="<tr class=rowcontent title='Click untuk melihat detail.' style=\"cursor: pointer\" onclick=showpopup('".$kodeorg[$notransid]."','".$tanggal[$notransid]."','".$notrans[$notransid]."','',event)>
		$outrupiah=$bar2->extoutstanding*$bar2->hargaext;
		$stream.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td>".$bar2->nokontrakext."</td>
					<td align='center'>".$bar2->tanggalext."</td>
					<td></td>
					<td align='center'>".$bar2->mintgl." - ".$bar2->maxtgl."</td>
					<td align='right'>".number_format($bar2->qtykontrak,0,'.',',')."</td>
					<td align='right'>".number_format($bar2->beratkirim,0,'.',',')."</td>
					<td align='right'>".number_format($bar2->extoutstanding,0,'.',',')."</td>
					<td align='right'>".number_format($bar2->hargaext,2,'.',',')."</td>
					<td align='right'>".number_format($outrupiah,2,'.',',')."</td>
				</tr>";
		$tjm_kontrak+=$bar2->qtykontrak;
		$tjm_kirim+=$bar2->beratkirim;
		$tjm_outs+=$bar2->extoutstanding;
		$trp_outs+=$outrupiah;
	}
	# preview: nampilin sub total ================================================================================
	$thargaext=$trp_outs/$tjm_outs;
	$stream.="<tr class=rowcontent>
				<td bgcolor='#FEDEFE' colspan=5 align='center'>Total</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($tjm_kontrak,0,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($tjm_kirim,0,'.',',')."</td>
				<td bgcolor='#FEDEDE' align='right'>".number_format($tjm_outs,0,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($thargaext,2,'.',',')."</td>
				<td bgcolor='#FEDEFE' align='right'>".number_format($trp_outs,2,'.',',')."</td>
			</tr>";
	$stream.="</tbody></table>";
	# End: nampilin External ================================================================================

	switch($proses){
        case'preview':
          echo $stream;
			break;

		case 'excel':
            if(strlen($stream)>0){
				$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
				$judul="<h3>Laporan Outstanding Kontrak Penjualan".$subjudul;
				$judul.="<BR>Periode : ".$periode."</h3>";
				$nop_="Laporan_Outstanding_Kontrak_Penjualan_".date("His");
				$stream=$judul.$stream;
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
