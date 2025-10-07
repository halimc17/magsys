<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$tanggalmulai=tanggalsystem(checkPostGet('tanggalmulai',''));
$tanggalakhir=tanggalsystem(checkPostGet('tanggalakhir',''));
$kdBrg=checkPostGet('kdBrg','');
$idPabrik=checkPostGet('idPabrik','');

if(($tanggalakhir-$tanggalmulai) < 0){
	echo " Gagal: Periksa kembali periode tanggal, Tanggal akhir lebih kecil dari tanggal mulai.";
}else{
switch($proses)
{
	case'preview':
		if($tanggalmulai=='')
		{
			echo"warning: Tanggal Dari Tidak Boleh Kosong";
			exit();
		}
		if($tanggalakhir=='')
		{
			echo"warning: Tanggal Sampai Tidak Boleh Kosong";
			exit();
		}
	
		echo" <table class=sortable cellspacing=1 border=0 style='width:1200px;'>
			<thead>
			<tr class=rowheader>
				<td align=center rowspan=2>No.</td>
			    <td align=center rowspan=2>".$_SESSION['lang']['NoKontrak']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['nmcust']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['komoditi']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['tglKontrak']."</td>
				<td align=center>".$_SESSION['lang']['kuantitas']."</td>   
				<td align=center>".$_SESSION['lang']['penerimaan']."</td>   
				<td align=center>".$_SESSION['lang']['selisih']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['hargasatuan']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total'].' '.$_SESSION['lang']['kontrak']."</td>
				<td align=center colspan=6>".$_SESSION['lang']['klaim'].' '.$_SESSION['lang']['kualitas'].' (Rp)'."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total'].' '.$_SESSION['lang']['klaim']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
				<td align=center colspan=4>".$_SESSION['lang']['invoice']."</td>
				<td align=center colspan=2>".$_SESSION['lang']['pembayaran']."</td>
				<td align=center colspan=2>Delivery</td>
				<td align=center colspan=2>".$_SESSION['lang']['realisasi']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['selisih'].' '.$_SESSION['lang']['delivery']."</td>
				<td align=center colspan=3>".$_SESSION['lang']['transporter']."</td>
			</tr>
			<tr>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>Susut</td>
				<td style='text-align:center;'>FFA</td>
				<td style='text-align:center;'>M&I</td>
				<td style='text-align:center;'>".$_SESSION['lang']['dobi']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['batu']."</td>
				<td style='text-align:center;'>Late Delivery</td>
				<td style='text-align:center;'>".$_SESSION['lang']['nilai']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['ppn']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['nilaiinvoice']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['invoice']."</td>
				<td style='text-align:center;'>Jumlah Bayar</td>
				<td style='text-align:center;'>".$_SESSION['lang']['tanggalbayar']."</td>
				<td style='text-align:center;'>Start</td>
				<td style='text-align:center;'>End</td>
				<td style='text-align:center;'>Start</td>
				<td style='text-align:center;'>End</td>
				<td style='text-align:center;'>".$_SESSION['lang']['jumlah']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['biaya']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['rpperkg']."</td>
			</tr>
			</thead><tbody>
			";
			
			$isidata=array();
			$sql="select a.nokontrak,a.tanggalkontrak,a.koderekanan,b.namacustomer,a.kodebarang,c.namabarang,a.kuantitaskontrak,a.kdtermin,d.qty,d.nodo
						,f.beratbersih,f.kgpembeli,(a.kuantitaskontrak-f.kgpembeli) as selisihkg,a.hargasatuan,(a.kuantitaskontrak*a.hargasatuan) as totalkontrak
						,e.rupiah1*if(e.nilaippn=0,1,1.1) as rupiah1,e.rupiah2*if(e.nilaippn=0,1,1.1) as rupiah2,e.rupiah3*if(e.nilaippn=0,1,1.1) as rupiah3
						,e.rupiah4*if(e.nilaippn=0,1,1.1) as rupiah4,e.rupiah5*if(e.nilaippn=0,1,1.1) as rupiah5,e.rupiah6*if(e.nilaippn=0,1,1.1) as rupiah6
						,(e.rupiah1+e.rupiah2+e.rupiah3+e.rupiah4+e.rupiah5+e.rupiah6)*if(e.nilaippn=0,1,1.1) as totalklaim
						,((a.kuantitaskontrak*a.hargasatuan)-((e.rupiah1+e.rupiah2+e.rupiah3+e.rupiah4+e.rupiah5+e.rupiah6)*if(e.nilaippn=0,1,1.1))) as total
						,e.noinvoice,e.nilaiinvoice,e.nilaippn,e.tanggal as tglinvoice,e.posting,g.jumlah,g.tanggal as tglbayar
						,a.tanggalkirim,a.sdtanggal,f.tglawal,f.tglakhir,d.jumlahrealisasippn,d.hasilkerjarealisasi
					from ".$dbname.".pmn_kontrakjual a
					LEFT JOIN ".$dbname.".pmn_4customer               b ON a.koderekanan=b.kodecustomer
					LEFT JOIN ".$dbname.".log_5masterbarang           c ON a.kodebarang=c.kodebarang
					LEFT JOIN ".$dbname.".keu_penagihanht             e ON a.nokontrak=e.nokontrak
					LEFT JOIN (select kodebarang,kodecustomer,nokontrak,millcode,sum(beratbersih) as beratbersih,sum(kgpembeli) as kgpembeli,min(tanggal) as tglawal,max(tanggal) as tglakhir from ".$dbname.".pabrik_timbangan where nokontrak<>'' GROUP BY nokontrak) f ON a.nokontrak=f.nokontrak
					LEFT JOIN (select x.keterangan1,x.nodok,y.tanggal,sum(x.jumlah*x.kurs) as jumlah,y.posting from ".$dbname.".keu_kasbankht y LEFT JOIN ".$dbname.".keu_kasbankdt x on x.notransaksi=y.notransaksi where x.keterangan1<>'' and y.posting='1' GROUP BY x.keterangan1)	g ON e.noinvoice = g.keterangan1
					LEFT JOIN (select p.nodo,p.tanggaldo,p.nokontrak,p.nokontrakinternal,p.qty,sum(r.jumlahrealisasippn) as jumlahrealisasippn
									,sum(r.hasilkerjarealisasi) as hasilkerjarealisasi
								from ".$dbname.".pmn_suratperintahpengiriman p LEFT JOIN
								(select s.notransaksi,s.keterangan,t.hasilkerjajumlah,if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0) as jmlppn
										,sum(v.jumlahrealisasi) as jumlahrealisasi,sum(v.hasilkerjarealisasi) as hasilkerjarealisasi,v.statusjurnal
										,(sum(v.jumlahrealisasi)+if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0)) as jumlahrealisasippn
								from ".$dbname.".log_spkht s
								LEFT JOIN (select * from ".$dbname.".log_spkdt where kodekegiatan='811010201') t on s.notransaksi=t.notransaksi
								LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun='1160100') u on s.notransaksi=u.notransaksi
								LEFT JOIN ".$dbname.".log_baspk v on s.notransaksi=v.notransaksi
								where v.statusjurnal=1 and s.keterangan<>'' and t.hasilkerjajumlah>0
								GROUP BY s.notransaksi) r on p.nodo=r.keterangan
								GROUP BY p.nodo) d ON a.nokontrak=d.nokontrak
					where a.tanggalkontrak between '".$tanggalmulai."' and '".$tanggalakhir."' and e.kodept = '".$idPabrik."'
				";
			if(!empty($kdBrg)) {
				$sql .= " and a.kodebarang = '".$kdBrg."'";
			}

			$sql .= " order by a.nokontrak,e.noinvoice";
			
			$query=mysql_query($sql) or die(mysql_error());
			$row=mysql_num_rows($query);
			if($row>0)
			{
				while($res=mysql_fetch_assoc($query))
				{
					$no+=1;
					$item = explode(";",$res['kdtermin']);
					$itemCount = count($item);
					$jumlahinvoice=$res['nilaiinvoice']+$res['nilaippn'];
					$selisihtgl=tanggalnormal($res['tglakhir'])-tanggalnormal($res['sdtanggal']);
					$biayaperkg=$res['nilaiinvoice']/$res['kuantitaskontrak'];
					$arr="nokontrak##".$res['nokontrak']."##nodo##".$res['nodo'];	  

					$sSub="select count(nokontrak) from ".$dbname.".keu_penagihanht where nokontrak = '".$res['nokontrak']."'";
					$qSub=mysql_query($sSub) or die(mysql_error());
					$cAry = mysql_fetch_array($qSub);
					$rows = $cAry[0];
					
					if($res['posting']==1){
						$hPosting = "Posted";
					}else{
						$hPosting = "Not Posted";
					}
					
					if($res['nilaippn']!=0){
						$hHargaSatuan = $res['hargasatuan']/1.1;
					}else{
						$hHargaSatuan = $res['hargasatuan'];
					}

					echo"<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$res['nokontrak']."</td>
							<td>".$res['namacustomer']."</td>
							<td>".singkatan($res['namabarang'])."</td>
							<td>".tanggalnormal($res['tanggalkontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['kuantitaskontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['kgpembeli'])."</td>
							<td style='text-align:right;'>".number_format($res['selisihkg'])."</td>
							<td style='text-align:right;'>".number_format($res['hargasatuan'],2)."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($res['totalkontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah1'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah2'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah3'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah4'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah5'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah6'])."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($res['totalklaim'],2)."</td>
							<td style='text-align:right;'>".number_format($res['total'])."</td>
							<td style='text-align:right;'>".number_format($res['nilaiinvoice'])."</td>
							<td style='text-align:right;'>".number_format($res['nilaippn'])."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($jumlahinvoice)."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglinvoice'])."</td>
							<td style='text-align:right;'>".number_format($res['jumlah'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglbayar'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tanggalkirim'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['sdtanggal'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglawal'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglakhir'])."</td>
							<td style='text-align:right;'>".$selisihtgl."</td>
							<td align=right style='cursor:pointer;' title='Click' onclick=\"zDetailBASPK(event,'pmn_slave_2rekappenjualandetail.php','".$arr."');\">".number_format($res['hasilkerjarealisasi'])."</td>
							<td align=right style='cursor:pointer;' title='Click' onclick=\"zDetailBASPK(event,'pmn_slave_2rekappenjualandetail.php','".$arr."');\">".number_format($res['jumlahrealisasippn'])."</td>
							<td style='text-align:right;'>";
					if($res['hasilkerjarealisasi']>0){
						echo number_format($res['jumlahrealisasippn']/$res['hasilkerjarealisasi'],2);
					}else{
						echo number_format(0);
					}
					echo	"</td>
							</tr>
						";
					
					$totalIncKg[]			= $res['nilaiinvoice']/$hHargaSatuan;
					$totalkuantitaskontrak  = $res['kuantitaskontrak'];
					$totalkgpembeli			= $res['kgpembeli'];
					$totalselisihkg			= $res['selisihkg'];
					$totalhargasatuan		= $res['hargasatuan'];
					$totalkontrak			= $res['totalkontrak'];
					$totalrupiah1[]			= $res['rupiah1'];
					$totalrupiah2[]			= $res['rupiah2'];
					$totalrupiah3[]			= $res['rupiah3'];
					$totalrupiah4[]			= $res['rupiah4'];
					$totalrupiah5[]			= $res['rupiah5'];
					$totalrupiah6[]			= $res['rupiah6'];
					$totalklaim[]			= $res['totalklaim'];
					$totaltotal				= $res['total'];
					$totalnilaiinvoice[]	= $res['nilaiinvoice'];
					$totalnilaippn[]		= $res['nilaippn'];
					$totalinvoice[]			= $res['nilaiinvoice']+$res['nilaippn'];
					$totalPayment[]			= $res['jumlah'];
					$totalselisihtgl		= $selisihtgl;
					$totalJmlTransport		= $res['hasilkerjarealisasi'];
					$totalTransport			= $res['jumlahrealisasippn'];
					
					$gtotalIncKg			+= $res['nilaiinvoice']/$hHargaSatuan;
					$gtotalkuantitaskontrak += 0;
					$gtotalkgpembeli		+= 0;
					$gtotalselisihKg		+= 0;
					$gtotalhargasatuan		+= 0;
					$gtotalkontrak			+= 0;
					$gtotalrupiah1			+= $res['rupiah1'];
					$gtotalrupiah2			+= $res['rupiah2'];
					$gtotalrupiah3			+= $res['rupiah3'];
					$gtotalrupiah4			+= $res['rupiah4'];
					$gtotalrupiah5			+= $res['rupiah5'];
					$gtotalrupiah6			+= $res['rupiah6'];
					$gtotalklaim			+= $res['totalklaim'];
					$gtotalhargasatuan		+= $res['hargasatuan'];
					$gtotaltotal			+= 0;
					$gtotalnilaiinvoice		+= $res['nilaiinvoice'];
					$gtotalnilaippn			+= $res['nilaippn'];
					$gtotalinvoice			+= ($res['nilaiinvoice']+$res['nilaippn']);
					$gtotalPayment			+= $res['jumlah'];
					$gtotalselisihtgl		+= 0;
					$gtotalJmlTransport		+= 0;
					$gtotalTransport		+= 0;
					
					if (count($totalIncKg) == $rows) {
						$gtotaltotal			+= $totaltotal;
						$gtotalJmlTransport		+= $totalJmlTransport;
						$gtotalTransport		+= $totalTransport;
						$gtotalkontrak			+= $totalkontrak;
						$gtotalselisihtgl		+= $selisihtgl;
						/*
						$sData = "select distinct(a.nokontrak) 
								from ".$dbname.".pmn_kontrakjualdt a
								where a.nokontrak_ref='".$res['nokontrak']."'";
						$qData =  mysql_query($sData) or die(mysql_error());
						if(mysql_num_rows($qData) != 0){
							while($rData=mysql_fetch_assoc($qData)){
								if($rData['posting']==1){
									$hPosting2 = "Posted";
								}else{
									$hPosting2 = "Not Posted";
								}
								
								if($rData['nilaippn']!=0){
									$hHargaSatuan2 = $rData['hargasatuan']/1.1;
								}else{
									$hHargaSatuan2 = $rData['hargasatuan'];
								}
								echo"<tr class=rowcontent style='color:green;'>
									<td></td>
									<td>".$rData['nokontrak']."</td>
									<td></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:center;'></td>
									<td></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:left;'></td>
									<td style='text-align:center;'></td>
									</tr>
								";
							}
						}
						*/
						echo"<tr class=rowcontent style='font-weight:bold;'>
								<td style='text-align:center' colspan=9>Sub Total</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah1))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah2))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah3))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah4))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah5))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah6))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalklaim))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalnilaiinvoice))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalnilaippn))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalinvoice))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalPayment))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
							</tr>";
							
						$totalIncKg   = array();
						$totalrupiah1 = array();
						$totalrupiah2 = array();
						$totalrupiah3 = array();
						$totalrupiah4 = array();
						$totalrupiah6 = array();
						$totalklaim   = array();
						$totalnilaiinvoice = array();
						$totalnilaippn = array();
						$totalinvoice = array();
						$totalPayment = array();
					}
				}
				echo"<tr class=rowcontent style='font-weight:bold;'>
						<td style='text-align:center' colspan=9>Grand Total</td>
						<td style='text-align:right'>".number_format($gtotalkontrak)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah1)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah2)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah3)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah4)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah5)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah6)."</td>
						<td style='text-align:right'>".number_format($gtotalklaim)."</td>
						<td style='text-align:right'>".number_format($gtotaltotal)."</td>
						<td style='text-align:right'>".number_format($gtotalnilaiinvoice)."</td>
						<td style='text-align:right'>".number_format($gtotalnilaippn)."</td>
						<td style='text-align:right'>".number_format($gtotalinvoice)."</td>
						<td style='text-align:right'></td>
						<td style='text-align:right'>".number_format($gtotalPayment)."</td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'>".number_format($gtotalselisihtgl)."</td>
						<td style='text-align:right'>".number_format($gtotalJmlTransport)."</td>
						<td style='text-align:right'>".number_format($gtotalTransport)."</td>
						<td style='text-align:right'>";
					if($gtotalJmlTransport>0){
						echo number_format($gtotalTransport/$gtotalJmlTransport,2);
					}else{
						echo number_format(0);
					}
				echo "</td>
					</tr>";
			}
			else
			{
					echo"<tr class=rowcontent align=center><td colspan=18>Not Found</td></tr>";
			}
			echo"</tbody></table>";
        break;
		
        case'getDetail':
        echo"<link rel=stylesheet type=text/css href=style/generic.css>";
        $nokontrak=$_GET['nokontrak'];
        $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
        $qHead=mysql_query($sHed) or die(mysql_error());
        $rHead=mysql_fetch_assoc($qHead);
        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
        $qBrg=mysql_query($sBrg) or die(mysql_error());
        $rBrg=mysql_fetch_assoc($qBrg);

        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
        $qCust=mysql_query($sCust) or die(mysql_error());
        $rCust=mysql_fetch_assoc($qCust);
        echo"<fieldset><legend>".$_SESSION['lang']['detailPengiriman']."</legend>
        <table cellspacing=1 border=0 class=myinputtext>
        <tr>
                <td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td>".$nokontrak."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['tglKontrak']."</td><td>:</td><td>".tanggalnormal($rHead['tanggalkontrak'])."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['komoditi']."</td><td>:</td><td>".$rBrg['namabarang']."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['Pembeli']."</td><td>:</td><td>".$rCust['namacustomer']."</td>
        </tr>
        </table><br />
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>".$_SESSION['lang']['nosipb']."</td>
        <td>".$_SESSION['lang']['beratBersih']."</td>
        <td>".$_SESSION['lang']['kodenopol']."</td>
        <td>".$_SESSION['lang']['sopir']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/	

        $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."'";
        $qDet=mysql_query($sDet) or die(mysql_error());
        $rCek=mysql_num_rows($qDet);
        if($rCek>0)
        {
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        echo"<tr class=rowcontent>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td align=right>".number_format($rDet['beratbersih'],2)."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        </tr>";
                }
        }
        else
        {
                echo"<tr><td colspan=7>Not Found</td></tr>";
        }
        echo"</tbody></table></fieldset>";

        break;
		
	case'excel':
			if($tanggalmulai=='')
			{
				echo"warning: Tanggal Dari Tidak Boleh Kosong";
				exit();
			}
			if($tanggalakhir=='')
			{
				echo"warning: Tanggal Sampai Tidak Boleh Kosong";
				exit();
			}

			$bgcoloraja="bgcolor=#DEDEDE align=center";
			$tab="<table><font size='5'><b>".$_SESSION['lang']['rekap'].' '.$_SESSION['lang']['penjualan']."</b></font></table>";
			$tab.="<table class=sortable cellspacing=1 border=1>
					<thead>
					<thead>
			<tr class=rowheader>
				<td align=center rowspan=2>No.</td>
			    <td align=center rowspan=2>".$_SESSION['lang']['NoKontrak']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['nmcust']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['komoditi']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['tglKontrak']."</td>
				<td align=center>".$_SESSION['lang']['kuantitas']."</td>   
				<td align=center>".$_SESSION['lang']['penerimaan']."</td>   
				<td align=center>".$_SESSION['lang']['selisih']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['hargasatuan']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total'].' '.$_SESSION['lang']['kontrak']."</td>
				<td align=center colspan=6>".$_SESSION['lang']['klaim'].' '.$_SESSION['lang']['kualitas'].' (Rp)'."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total'].' '.$_SESSION['lang']['klaim']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
				<td align=center colspan=4>".$_SESSION['lang']['invoice']."</td>
				<td align=center colspan=2>".$_SESSION['lang']['pembayaran']."</td>
				<td align=center colspan=2>Delivery</td>
				<td align=center colspan=2>".$_SESSION['lang']['realisasi']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['selisih'].' '.$_SESSION['lang']['delivery']."</td>
				<td align=center colspan=3>".$_SESSION['lang']['transporter']."</td>
			</tr>
			<tr>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>(Kg)</td>
				<td style='text-align:center;'>Susut</td>
				<td style='text-align:center;'>FFA</td>
				<td style='text-align:center;'>M&I</td>
				<td style='text-align:center;'>".$_SESSION['lang']['dobi']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['batu']."</td>
				<td style='text-align:center;'>Late Delivery</td>
				<td style='text-align:center;'>".$_SESSION['lang']['nilai']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['ppn']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['nilaiinvoice']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['invoice']."</td>
				<td style='text-align:center;'>Jumlah Bayar</td>
				<td style='text-align:center;'>".$_SESSION['lang']['tanggalbayar']."</td>
				<td style='text-align:center;'>Start</td>
				<td style='text-align:center;'>End</td>
				<td style='text-align:center;'>Start</td>
				<td style='text-align:center;'>End</td>
				<td style='text-align:center;'>".$_SESSION['lang']['jumlah']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['biaya']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['rpperkg']."</td>
			</tr>
			</thead><tbody>";
					
			$sql="select a.nokontrak,a.tanggalkontrak,a.koderekanan,b.namacustomer,a.kodebarang,c.namabarang,a.kuantitaskontrak,a.kdtermin,d.qty,d.nodo
						,f.beratbersih,f.kgpembeli,(a.kuantitaskontrak-f.kgpembeli) as selisihkg,a.hargasatuan,(a.kuantitaskontrak*a.hargasatuan) as totalkontrak
						,e.rupiah1*if(e.nilaippn=0,1,1.1) as rupiah1,e.rupiah2*if(e.nilaippn=0,1,1.1) as rupiah2,e.rupiah3*if(e.nilaippn=0,1,1.1) as rupiah3
						,e.rupiah4*if(e.nilaippn=0,1,1.1) as rupiah4,e.rupiah5*if(e.nilaippn=0,1,1.1) as rupiah5,e.rupiah6*if(e.nilaippn=0,1,1.1) as rupiah6
						,(e.rupiah1+e.rupiah2+e.rupiah3+e.rupiah4+e.rupiah5+e.rupiah6)*if(e.nilaippn=0,1,1.1) as totalklaim
						,((a.kuantitaskontrak*a.hargasatuan)-((e.rupiah1+e.rupiah2+e.rupiah3+e.rupiah4+e.rupiah5+e.rupiah6)*if(e.nilaippn=0,1,1.1))) as total
						,e.noinvoice,e.nilaiinvoice,e.nilaippn,e.tanggal as tglinvoice,e.posting,g.jumlah,g.tanggal as tglbayar
						,a.tanggalkirim,a.sdtanggal,f.tglawal,f.tglakhir,d.jumlahrealisasippn,d.hasilkerjarealisasi
					from ".$dbname.".pmn_kontrakjual a
					LEFT JOIN ".$dbname.".pmn_4customer               b ON a.koderekanan=b.kodecustomer
					LEFT JOIN ".$dbname.".log_5masterbarang           c ON a.kodebarang=c.kodebarang
					LEFT JOIN ".$dbname.".keu_penagihanht             e ON a.nokontrak=e.nokontrak
					LEFT JOIN (select kodebarang,kodecustomer,nokontrak,millcode,sum(beratbersih) as beratbersih,sum(kgpembeli) as kgpembeli,min(tanggal) as tglawal,max(tanggal) as tglakhir from ".$dbname.".pabrik_timbangan where nokontrak<>'' GROUP BY nokontrak) f ON a.nokontrak=f.nokontrak
					LEFT JOIN (select x.keterangan1,x.nodok,y.tanggal,sum(x.jumlah*x.kurs) as jumlah,y.posting from ".$dbname.".keu_kasbankht y LEFT JOIN ".$dbname.".keu_kasbankdt x on x.notransaksi=y.notransaksi where x.keterangan1<>'' and y.posting='1' GROUP BY x.keterangan1)	g ON e.noinvoice = g.keterangan1
					LEFT JOIN (select p.nodo,p.tanggaldo,p.nokontrak,p.nokontrakinternal,p.qty,sum(r.jumlahrealisasippn) as jumlahrealisasippn
									,sum(r.hasilkerjarealisasi) as hasilkerjarealisasi
								from ".$dbname.".pmn_suratperintahpengiriman p LEFT JOIN
								(select s.notransaksi,s.keterangan,t.hasilkerjajumlah,if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0) as jmlppn
										,sum(v.jumlahrealisasi) as jumlahrealisasi,sum(v.hasilkerjarealisasi) as hasilkerjarealisasi,v.statusjurnal
										,(sum(v.jumlahrealisasi)+if(u.nilai>0,(u.nilai*sum(v.hasilkerjarealisasi)/t.hasilkerjajumlah),0)) as jumlahrealisasippn
								from ".$dbname.".log_spkht s
								LEFT JOIN (select * from ".$dbname.".log_spkdt where kodekegiatan='811010201') t on s.notransaksi=t.notransaksi
								LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun='1160100') u on s.notransaksi=u.notransaksi
								LEFT JOIN ".$dbname.".log_baspk v on s.notransaksi=v.notransaksi
								where v.statusjurnal=1 and s.keterangan<>'' and t.hasilkerjajumlah>0
								GROUP BY s.notransaksi) r on p.nodo=r.keterangan
								GROUP BY p.nodo) d ON a.nokontrak=d.nokontrak
					where a.tanggalkontrak between '".$tanggalmulai."' and '".$tanggalakhir."' and e.kodept = '".$idPabrik."'
				";
			if(!empty($kdBrg)) {
				$sql .= " and a.kodebarang = '".$kdBrg."'";
			}

			$sql .= " order by a.nokontrak,e.noinvoice";
			
			$query=mysql_query($sql) or die(mysql_error());
			$row=mysql_num_rows($query);
			if($row>0){
				while($res=mysql_fetch_assoc($query))
				{
					$no+=1;
					$item = explode(";",$res['kdtermin']);
					$itemCount = count($item);
					$jumlahinvoice=$res['nilaiinvoice']+$res['nilaippn'];
					$selisihtgl=tanggalnormal($res['tglakhir'])-tanggalnormal($res['sdtanggal']);
					$biayaperkg=$res['nilaiinvoice']/$res['kuantitaskontrak'];
					
					$sSub="select count(nokontrak) from ".$dbname.".keu_penagihanht where nokontrak = '".$res['nokontrak']."'";
					$qSub=mysql_query($sSub) or die(mysql_error());
					$cAry = mysql_fetch_array($qSub);
					$rows = $cAry[0];
					
					if($res['posting']==1){
						$hPosting = "Posted";
					}else{
						$hPosting = "Not Posted";
					}
					
					if($res['nilaippn']!=0){
						$hHargaSatuan = $res['hargasatuan']/1.1;
					}else{
						$hHargaSatuan = $res['hargasatuan'];
					}
					
					$tab.="<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$res['nokontrak']."</td>
							<td>".$res['namacustomer']."</td>
							<td>".singkatan($res['namabarang'])."</td>
							<td>".tanggalnormal($res['tanggalkontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['kuantitaskontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['kgpembeli'])."</td>
							<td style='text-align:right;'>".number_format($res['selisihkg'])."</td>
							<td style='text-align:right;'>".number_format($res['hargasatuan'],2)."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($res['totalkontrak'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah1'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah2'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah3'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah4'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah5'])."</td>
							<td style='text-align:right;'>".number_format($res['rupiah6'])."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($res['totalklaim'],2)."</td>
							<td style='text-align:right;'>".number_format($res['total'])."</td>
							<td style='text-align:right;'>".number_format($res['nilaiinvoice'])."</td>
							<td style='text-align:right;'>".number_format($res['nilaippn'])."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($jumlahinvoice)."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglinvoice'])."</td>
							<td style='text-align:right;'>".number_format($res['jumlah'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglbayar'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tanggalkirim'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['sdtanggal'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglawal'])."</td>
							<td style='text-align:right;'>".tanggalnormal($res['tglakhir'])."</td>
							<td style='text-align:right;'>".$selisihtgl."</td>
							<td style='text-align:right;'>".number_format($res['hasilkerjarealisasi'])."</td>
							<td style='text-align:right;'>".number_format($res['jumlahrealisasippn'])."</td>
							<td style='text-align:right;'>";
					if($res['hasilkerjarealisasi']>0){
						$tab.=number_format($res['jumlahrealisasippn']/$res['hasilkerjarealisasi']);
					}else{
						$tab.=number_format(0);
					}
					$tab.=	"</td>
							</tr>
						";
						
					$totalIncKg[]			= $res['nilaiinvoice']/$hHargaSatuan;
					$totalkuantitaskontrak  = $res['kuantitaskontrak'];
					$totalkgpembeli			= $res['kgpembeli'];
					$totalselisihkg			= $res['selisihkg'];
					$totalhargasatuan		= $res['hargasatuan'];
					$totalkontrak			= $res['totalkontrak'];
					$totalrupiah1[]			= $res['rupiah1'];
					$totalrupiah2[]			= $res['rupiah2'];
					$totalrupiah3[]			= $res['rupiah3'];
					$totalrupiah4[]			= $res['rupiah4'];
					$totalrupiah5[]			= $res['rupiah5'];
					$totalrupiah6[]			= $res['rupiah6'];
					$totalklaim[]			= $res['totalklaim'];
					$totaltotal				= $res['total'];
					$totalnilaiinvoice[]	= $res['nilaiinvoice'];
					$totalnilaippn[]		= $res['nilaippn'];
					$totalinvoice[]			= $res['nilaiinvoice']+$res['nilaippn'];
					$totalPayment[]			= $res['jumlah'];
					$totalselisihtgl		= $selisihtgl;
					$totalJmlTransport		= $res['hasilkerjarealisasi'];
					$totalTransport			= $res['jumlahrealisasippn'];
					
					$gtotalIncKg			+= $res['nilaiinvoice']/$hHargaSatuan;
					$gtotalkuantitaskontrak += 0;
					$gtotalkgpembeli		+= 0;
					$gtotalselisihKg		+= 0;
					$gtotalhargasatuan		+= 0;
					$gtotalkontrak			+= 0;
					$gtotalrupiah1			+= $res['rupiah1'];
					$gtotalrupiah2			+= $res['rupiah2'];
					$gtotalrupiah3			+= $res['rupiah3'];
					$gtotalrupiah4			+= $res['rupiah4'];
					$gtotalrupiah5			+= $res['rupiah5'];
					$gtotalrupiah6			+= $res['rupiah6'];
					$gtotalklaim			+= $res['totalklaim'];
					$gtotalhargasatuan		+= $res['hargasatuan'];
					$gtotaltotal			+= 0;
					$gtotalnilaiinvoice		+= $res['nilaiinvoice'];
					$gtotalnilaippn			+= $res['nilaippn'];
					$gtotalinvoice			+= ($res['nilaiinvoice']+$res['nilaippn']);
					$gtotalPayment			+= $res['jumlah'];
					$gtotalselisihtgl		+= 0;
					$gtotalJmlTransport		+= 0;
					$gtotalTransport		+= 0;
					
					if (count($totalIncKg) == $rows) {
						$gtotaltotal			+= $totaltotal;
						$gtotalJmlTransport		+= $totalJmlTransport;
						$gtotalTransport		+= $totalTransport;
						$gtotalkontrak			+= $totalkontrak;
						$gtotalselisihtgl		+= $selisihtgl;
						/*
						$sData = "select distinct(a.nokontrak) 
								from ".$dbname.".pmn_kontrakjualdt a
								where a.nokontrak_ref='".$res['nokontrak']."'";
						$qData =  mysql_query($sData) or die(mysql_error());
						if(mysql_num_rows($qData) != 0){
							while($rData=mysql_fetch_assoc($qData)){
								if($rData['posting']==1){
									$hPosting2 = "Posted";
								}else{
									$hPosting2 = "Not Posted";
								}
								
								if($rData['nilaippn']!=0){
									$hHargaSatuan2 = $rData['hargasatuan']/1.1;
								}else{
									$hHargaSatuan2 = $rData['hargasatuan'];
								}
								$tab.="<tr class=rowcontent style='color:green;'>
									<td></td>
									<td>".$rData['nokontrak']."</td>
									<td></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:center;'></td>
									<td></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:right;'></td>
									<td style='text-align:left;'></td>
									<td style='text-align:center;'></td>
									</tr>
								";
							}
						}
						*/
						$tab.="<tr class=rowcontent style='font-weight:bold;'>
								<td style='text-align:center' colspan=9>Sub Total</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah1))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah2))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah3))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah4))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah5))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalrupiah6))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalklaim))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalnilaiinvoice))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalnilaippn))."</td>
								<td style='text-align:right'>".number_format(array_sum($totalinvoice))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'>".number_format(array_sum($totalPayment))."</td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
								<td style='text-align:right'></td>
							</tr>";
							
						$totalIncKg   = array();
						$totalrupiah1 = array();
						$totalrupiah2 = array();
						$totalrupiah3 = array();
						$totalrupiah4 = array();
						$totalrupiah5 = array();
						$totalrupiah6 = array();
						$totalklaim   = array();
						$totalnilaiinvoice = array();
						$totalnilaippn = array();
						$totalinvoice = array();
						$totalPayment = array();
					}
				}
				$tab.="<tr class=rowcontent style='font-weight:bold;'>
						<td style='text-align:center' colspan=9>Grand Total</td>
						<td style='text-align:right'>".number_format($gtotalkontrak)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah1)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah2)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah3)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah4)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah5)."</td>
						<td style='text-align:right'>".number_format($gtotalrupiah6)."</td>
						<td style='text-align:right'>".number_format($gtotalklaim)."</td>
						<td style='text-align:right'>".number_format($gtotaltotal)."</td>
						<td style='text-align:right'>".number_format($gtotalnilaiinvoice)."</td>
						<td style='text-align:right'>".number_format($gtotalnilaippn)."</td>
						<td style='text-align:right'>".number_format($gtotalinvoice)."</td>
						<td style='text-align:right'></td>
						<td style='text-align:right'>".number_format($gtotalPayment)."</td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'></td>
						<td style='text-align:right'>".number_format($gtotalselisihtgl)."</td>
						<td style='text-align:right'>".number_format($gtotalJmlTransport)."</td>
						<td style='text-align:right'>".number_format($gtotalTransport)."</td>
						<td style='text-align:right'>";
					if($gtotalJmlTransport>0){
						$tab.=number_format($gtotalTransport/$gtotalJmlTransport);
					}else{
						$tab.=number_format(0);
					}
				$tab.="</td>
					</tr>";
			}
			else
			{
					$tab.="<tr class=rowcontent align=center><td colspan=18>Not Found</td></tr>";
			}
				
			$tab.="</tbody></table>";
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
			$nop_="Rekap_Penjualan_".$dte;
			$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gztralala, $tab);
			gzclose($gztralala);
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";	
		break;
		
        default:
        break;
}
}

function singkatan($param) {
	$arr = explode(' ', $param);
	$singkatan = '';
	foreach($arr as $kata){
		$singkatan .= substr($kata, 0, 1);
	}
	return $singkatan;
}
?>