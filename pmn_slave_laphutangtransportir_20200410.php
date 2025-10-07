<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$kdUnit=$_POST['kdUnit'];
	$kdCust=$_POST['kdCust'];
	$tgl_1=$_POST['tgl_1'];
	$tgl_2=$_POST['tgl_2'];
	$kdBrg=$_POST['kdBrg'];
	$nokontrak=$_POST['nokontrak'];
	if($kdUnit=='')$kdUnit=$_GET['kdUnit'];
	if($kdCust=='')$kdCust=$_GET['kdCust'];
	if($tgl_1=='')$tgl_1=$_GET['tgl_1'];
	if($tgl_2=='')$tgl_2=$_GET['tgl_2'];
	if($kdBrg=='')$kdBrg=$_GET['kdBrg'];
	if($nokontrak=='')$nokontrakext=$_GET['nokontrak'];
	if($proses=='')$proses=$_GET['proses'];

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdUnit."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdUnit;

	#Filter parameter where 
	$whr.="true";
	$stream="";
	$judul=$_SESSION['lang']['laporan']."_".$_SESSION['lang']['hutang']."_".$_SESSION['lang']['transporter'];
	if($kdUnit!=''){
		$whr.=" and f.kodeorg in (select kodeorganisasi from organisasi where induk like '%".$indukOrg."%')";
		$judul.="_".$kdUnit;
	}
	if($kdCust!=''){
		$whr.=" and b.koderekanan = '".$kdCust."'";
		$judul.="_".$kdCust;
	}
	if($tgl_1!='' and $tgl_2==''){
		$tgl_2=$tgl_1;
	}
	if($tgl_1=='' and $tgl_2!=''){
		$tgl_1=$tgl_2;
	}
	if($tgl_1!='' and $tgl_2!=''){
		$whr.=" and a.tanggaldo between '".tanggalsystem($tgl_1)."' and '".tanggalsystem($tgl_2)."'";
		$judul.="_".$tgl_1."_sd_".$tgl_2;
	}
	if($kdBrg!=''){
		$whr.=" and f.kodebarang = '".$kdBrg."'";
		$judul.="_".$kdBrg;
	}
	if($nokontrak!=''){
		$whr.=" and a.nokontrak like '%".$nokontrak."%'";
		$judul.="_".$nokontrakext;
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
			<td ".$bgclr.">No</td>
			<td ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
			<td ".$bgclr.">".'No.SPP'."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['kontrak']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['kode']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['transporter']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." BA"."</td>
			<td ".$bgclr.">".$_SESSION['lang']['harga']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['biaya']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['sisa']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['noberitaacara']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['noinvoice']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['nofp']." (Kg)"."</td>
			<td ".$bgclr.">".$_SESSION['lang']['terbayar']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggalbayar']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['notransaksi']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['nilaihutang']."</td>
		</tr></thead><tbody>";
	#ambil data 
	$str = "select a.nokontrak,a.nodo,a.tanggaldo,a.qty,c.hargarata,a.qty*c.hargarata as totalbiaya,b.koderekanan,b.namasupplier
				,b.hasilkerjarealisasi,b.hargarealisasi,b.jumlahrealisasi,b.notransaksi as nospk,b.tanggalba,d.noinvoice as noinv,d.nofp
				,if(e.posting='1',e.jumlah,0) as jmlbayar,if(e.posting='1',e.tanggal,'') as tglbayar,e.notransaksi as nobku
				,e.posting,f.kodebarang,f.tanggalkontrak
			from ".$dbname.".pmn_suratperintahpengiriman a
			LEFT JOIN 
				(select b.kodeorg,a.kodeblok,b.koderekanan,c.namasupplier,a.hasilkerjarealisasi
						,a.jumlahrealisasi/a.hasilkerjarealisasi as hargarealisasi,a.jumlahrealisasi,b.keterangan as nodo,a.notransaksi,a.tanggal as tanggalba
				from ".$dbname.".log_baspk a 
				LEFT JOIN ".$dbname.".log_spkht b on b.notransaksi=a.notransaksi
				LEFT JOIN ".$dbname.".log_5supplier c on c.supplierid=b.koderekanan
				where a.statusjurnal='1') b on b.nodo=a.nodo
			LEFT JOIN 
				(select b.kodeorg,b.keterangan as nospp,sum(a.jumlahrealisasi)/sum(a.hasilkerjarealisasi) as hargarata 
				from ".$dbname.".log_baspk a 
				LEFT JOIN ".$dbname.".log_spkht b on b.notransaksi=a.notransaksi
				where a.statusjurnal='1'
				GROUP BY b.keterangan) c on c.nospp=a.nodo
			LEFT JOIN 
				(select d.noinvoice,d.nopo,d.nofp from ".$dbname.".keu_tagihanht d where d.tipeinvoice='k') d on d.nopo=b.notransaksi
			LEFT JOIN 
				(select a.notransaksi,a.keterangan1,a.jumlah,b.posting,b.tanggal from ".$dbname.".keu_kasbankdt a 
				LEFT JOIN ".$dbname.".keu_kasbankht b on b.notransaksi=a.notransaksi
				where a.keterangan1<>'' and a.tipetransaksi='K') e on e.keterangan1=d.noinvoice
			LEFT JOIN 
				(select f.nokontrak,f.tanggalkontrak,f.kodebarang from ".$dbname.".pmn_kontrakjual f) f on f.nokontrak=a.nokontrak
			where ".$whr."
			ORDER BY a.tanggaldo,a.nodo,b.koderekanan
			";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$nokontrakextd="";
	$no=0;
	$jmlkontrak=0;
	$jmlba=0;
	$jmlbiaya=0;
	$sisa=0;
	$jmlbayar=0;
	$gtjmlkontrak=0;
	$gtjmlba=0;
	$gtjmlbiaya=0;
	$gtsisa=0;
	$gtjmlbayar=0;
	while($bar=mysql_fetch_object($res)){
		$stream.="<tr class=rowcontent>";
		if($nokontrakextd!=$bar->nokontrak){
			$no+=1;
			if($no!=1){
				$stream.="
					<td bgcolor='#DCDCDC' colspan=3 align='center'>Sub Total</td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlkontrak,0)."</td>
					<td bgcolor='#DCDCDC' colspan=2></td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlba,0)."</td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format(($jmlbiaya/$jmlba),2)."</td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlbiaya,2)."</td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($sisa,0)."</td>
					<td bgcolor='#DCDCDC' colspan=4></td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlbayar,2)."</td>
					<td bgcolor='#DCDCDC' colspan=2></td>
					<td bgcolor='#DCDCDC' align='right'>".@number_format(($jmlbiaya-$jmlbayar),2)."</td>";
				$stream.="</tr><tr class=rowcontent>";
				$gtsisa+=$sisa;
				$jmlkontrak=0;
				$jmlba=0;
				$jmlbiaya=0;
				$sisa=0;
				$jmlbayar=0;
			}
			$stream.="
				<td align='center'>".$no."</td>
				<td>".$bar->nokontrak."</td>
				<td>".$bar->nodo."</td>
				<td align='right'>".@number_format($bar->qty,0)."</td>";
			$jmlkontrak=$bar->qty;
			$sisa=$bar->qty;
			$gtjmlkontrak+=$bar->qty;
		}else{
			$stream.="<td colspan=4></td>";
		}
		$jmlhutang=($bar->jumlahrealisasi)+($bar->jumlahrealisasi*0.1)-($bar->jumlahrealisasi*0.02);
		$sisa=$sisa-$bar->hasilkerjarealisasi;
		$stream.="
				<td>".$bar->koderekanan."</td>
				<td>".$bar->namasupplier."</td>
				<td align='right'>".@number_format($bar->hasilkerjarealisasi,0)."</td>
				<td align='right'>".@number_format($bar->hargarealisasi,2)."</td>
				<td align='right'>".@number_format($jmlhutang,2)."</td>
				<td align='right'>".@number_format($sisa,0)."</td>
				<td>".$bar->nospk."</td>
				<td align='center'>".tanggalnormal($bar->tanggalba)."</td>
				<td>".$bar->noinv."</td>
				<td>".$bar->nofp."</td>
				<td align='right'>".@number_format($bar->jmlbayar,2)."</td>
				<td align='center'>".tanggalnormal($bar->tglbayar)."</td>
				<td align='right'>".@number_format($bar->nobku,2)."</td>
				<td align='right'>".@number_format($jmlhutang-$bar->jmlbayar,2)."</td>";
		$stream.="</tr>";
		$nokontrakextd=$bar->nokontrak;
		$jmlba+=$bar->hasilkerjarealisasi;
		$jmlbiaya+=$jmlhutang;
		$jmlbayar+=$bar->jmlbayar;

		$gtjmlba+=$bar->hasilkerjarealisasi;
		$gtjmlbiaya+=$jmlhutang;
		$gtjmlbayar+=$bar->jmlbayar;
	}
	if($nokontrakextd!=''){
		$stream.="<tr class=rowcontent>
			<td bgcolor='#DCDCDC' colspan=3 align='center'>Sub Total</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlkontrak,0)."</td>
			<td bgcolor='#DCDCDC' colspan=2></td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlba,0)."</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format(($jmlbiaya/$jmlba),2)."</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlbiaya,2)."</td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($sisa,0)."</td>
			<td bgcolor='#DCDCDC' colspan=4></td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format($jmlbayar,2)."</td>
			<td bgcolor='#DCDCDC' colspan=2></td>
			<td bgcolor='#DCDCDC' align='right'>".@number_format(($jmlbiaya-$jmlbayar),2)."</td>";
		$stream.="</tr>";
		$stream.="<thead class=rowheader>
			<td colspan=3 align='center'>Grand Total</td>
			<td align='right'>".@number_format($gtjmlkontrak,0)."</td>
			<td colspan=2></td>
			<td align='right'>".@number_format($gtjmlba,0)."</td>
			<td align='right'>".@number_format(($gtjmlbiaya/$gtjmlba),2)."</td>
			<td align='right'>".@number_format($gtjmlbiaya,2)."</td>
			<td align='right'>".@number_format($gtsisa,0)."</td>
			<td colspan=4></td>
			<td align='right'>".@number_format($gtjmlbayar,2)."</td>
			<td colspan=2></td>
			<td align='right'>".@number_format(($gtjmlbiaya-$gtjmlbayar),2)."</td>";
		$stream.="</thead>";
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
          echo $stream;
        break;
        case 'excel':
			$stream="<h2>".$judul."</h2>".$stream;
		    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $nop_=$judul.date("YmdHis");
            if(strlen($stream)>0){
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
				echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls.gz';
					  </script>";
            }
		break;
	}    
?>