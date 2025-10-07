<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$tanggalawal=date('Y-m-d h:i:s',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-01 07:00:00'));
$tanggalmulai=date('Y-m-d h:i:s',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2).' 07:00:00'));
$tanggalakhir=date('Y-m-d h:i:s',strtotime('+1 days',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2).' 06:59:59')));
$idPabrik=checkPostGet('idPabrik','');

if($tanggal==''){
	echo "Warning: Tanggal tidak boleh kosong.";
}else{
switch($proses)
{
	case'preview':
		echo" <table class=sortable cellspacing=1 border=0 style='width:1200px;'>
			<thead>
				<tr class=rowheader>
					<td align=center rowspan=1 colspan=12>A.I. TBS DITERIMA [Kg]</td>
					<td align=center rowspan=2 colspan=12>A.II. PENERIMAAN TBS PER JAM</td>
				</tr>
				<tr>
					<td width='20%' align=center rowspan=2 colspan=2>Supplier</td>
					<td align=center colspan=8>A. GRADING ( % )</td>
					<td width='7%' align=center rowspan=2>Hari Ini</td>
					<td width='7%' align=center rowspan=2>s/d Hari Ini</td>
				</tr>
				<tr>
					<td width='5%' align=center>Un Ripe</td>
					<td width='5%' align=center>Over Ripe</td>
					<td width='5%' align=center>Empty Bunch</td>
					<td width='5%' align=center>Abnormal</td>
					<td width='5%' align=center>Rotten Bunch</td>
					<td width='5%' align=center>Ripe</td>
					<td width='5%' align=center>Long Stalk</td>
					<td width='5%' align=center>Loose Fruit</td>
					<td align=center colspan=2>PUKUL [WIB]</td>
					<td align=center colspan=2>JUMLAH [Kg]</td>
					<td align=center colspan=2>PUKUL [WIB]</td>
					<td align=center colspan=2>JUMLAH [Kg]</td>
					<td align=center colspan=2>PUKUL [WIB]</td>
					<td align=center colspan=2>JUMLAH [Kg]</td>
				</tr>
			</thead><tbody>
			";
		$isidata=array();

		$sql="select substr(a.nospb,9,6) as unit,d.namaorganisasi as namaunit,if(substr(a.nospb,9,6)<>'',e.namaorganisasi,c.namasupplier) as namadivisi,a.kodecustomer
				,if(substr(a.nospb,9,6)<>'',substr(a.nospb,9,6),c.namasupplier) as supplier,sum(a.beratbersih) as tonageall,b.tonageday,sum(a.jumlahtandan1) as janjangall
				,b.janjangday,b.a,b.b,b.c,b.d,b.e,b.f,b.g,b.h,b.apersen,b.bpersen,b.cpersen,b.dpersen,b.epersen,b.fpersen,b.gpersen,b.hpersen
				from ".$dbname.".pabrik_timbangan a
				LEFT JOIN 
				(select substr(z.nospb,9,6) as unit,z.kodecustomer,sum(z.beratbersih) as tonageday,sum(z.jumlahtandan1) as janjangday 
				,sum(z.A) as a,sum(z.B) as b,sum(z.C) as c,sum(z.D) as d,sum(z.E) as e,sum(z.jumlahtandan1)-sum(z.A)+sum(z.B)+sum(z.C)+sum(z.D)+sum(z.E) as f,sum(z.F) as g,sum(z.G) as h
				,sum(z.A)/sum(z.jumlahtandan1)*100 as apersen,sum(z.B)/sum(z.jumlahtandan1)*100 as bpersen,sum(z.C)/sum(z.jumlahtandan1)*100 as cpersen
				,sum(z.D)/sum(z.jumlahtandan1)*100 as dpersen,sum(z.E)/sum(z.jumlahtandan1)*100 as epersen,(1-((sum(z.A)+sum(z.B)+sum(z.C)+sum(z.D)+sum(z.E))/sum(z.jumlahtandan1)))*100 as fpersen
				,sum(z.F)/sum(z.jumlahtandan1)*100 as gpersen,sum(z.G)/sum(z.jumlahtandan1)*100 as hpersen
				from (select x.notransaksi,x.nospb,x.kodecustomer,x.beratbersih,x.millcode,x.tanggal,x.kodebarang,x.jumlahtandan1,w.A,w.B,w.C,w.D,w.E,w.F,w.G 
						from ".$dbname.".pabrik_timbangan x 
 						LEFT JOIN (select y.notiket 
									,SUM(case when y.kodefraksi = 'A' then y.jumlah else 0 end) as 'A' 
 									,SUM(case when y.kodefraksi = 'B' then y.jumlah else 0 end) as 'B' 
 									,SUM(case when y.kodefraksi = 'C' then y.jumlah else 0 end) as 'C'
 									,SUM(case when y.kodefraksi = 'D' then y.jumlah else 0 end) as 'D' 
 									,SUM(case when y.kodefraksi = 'E' then y.jumlah else 0 end) as 'E' 
 									,SUM(case when y.kodefraksi = 'F' then y.jumlah else 0 end) as 'F' 
 									,SUM(case when y.kodefraksi = 'G' then y.jumlah else 0 end) as 'G' 
 									from ".$dbname.".pabrik_sortasi y GROUP BY y.notiket) w on w.notiket=x.notransaksi
						where x.millcode='".$idPabrik."' and x.tanggal between '".$tanggalmulai."' and '".$tanggalakhir."' and x.kodebarang='40000003') z
				where z.millcode='".$idPabrik."' 
				and z.tanggal between '".$tanggalmulai."' and '".$tanggalakhir."'
				and z.kodebarang='40000003'
				GROUP BY substr(z.nospb,9,6),z.kodecustomer) b on b.unit=substr(a.nospb,9,6) and b.kodecustomer=a.kodecustomer
				LEFT JOIN ".$dbname.".log_5supplier c on c.kodetimbangan=a.kodecustomer
				LEFT JOIN ".$dbname.".organisasi d on d.kodeorganisasi=substr(a.nospb,9,4)
				LEFT JOIN ".$dbname.".organisasi e on e.kodeorganisasi=substr(a.nospb,9,6)
				where a.millcode='".$idPabrik."' 
				and a.tanggal between '".$tanggalawal."' and '".$tanggalakhir."'
				and a.kodebarang='40000003'
				GROUP BY substr(a.nospb,9,6),a.kodecustomer
				ORDER BY a.kodecustomer,substr(a.nospb,9,6)";
//exit($sql);
			$query=mysql_query($sql) or die(mysql_error());
			$row=mysql_num_rows($query);
			$namaunit='';
			$namadivisi='';
			$totalgradA=0;
			$totalgradB=0;
			$totalgradC=0;
			$totalgradD=0;
			$totalgradE=0;
			$totalgradF=0;
			$totalgradG=0;
			$totalgradH=0;
			$totalTonageday=0;
			$totalTonageall=0;
			$gtotalTonageday=0;
			$gtotalTonageall=0;
			if($row>0)
			{
				while($res=mysql_fetch_assoc($query))
				{
					$no+=1;
					if($res['namaunit']!=$namaunit){
						if($totalTonageday==0){
							echo"<tr class=rowcontent>
									<td colspan=12>".$res['namaunit']."</td>
								</tr>";
						}else{
							echo"<tr class=rowcontent style='font-weight:bold;'>
							<td colspan=2>Sub Total ".$namaunit."</td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td width='5%' style='text-align:right;'></td>
							<td style='text-align:right;'>".number_format($totalTonageday)."</td>
							<td style='text-align:right;'>".number_format($totalTonageall)."</td>
							</tr>";
							$totalgradA=0;
							$totalgradB=0;
							$totalgradC=0;
							$totalgradD=0;
							$totalgradE=0;
							$totalgradF=0;
							$totalgradG=0;
							$totalgradH=0;
							$totalTonageday=0;
							$totalTonageall=0;
						}
					}
					echo"<tr class=rowcontent>
							<td colspan=2>".$res['namadivisi']."</td>
							<td style='text-align:right;'>".number_format($res['apersen'])."</td>
							<td style='text-align:right;'>".number_format($res['bpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['cpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['dpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['epersen'])."</td>
							<td style='text-align:right;'>".number_format($res['fpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['gpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['hpersen'])."</td>
							<td style='text-align:right;'>".number_format($res['tonageday'])."</td>
							<td style='text-align:right;'>".number_format($res['tonageall'])."</td>
						</tr>";
					$namaunit=$res['namaunit'];
					$totalTonageday += $res['tonageday'];
					$gtotalTonageday += $res['tonageday'];
					$totalTonageall += $res['tonageall'];
					$gtotalTonageall += $res['tonageall'];
				}
				echo"<tr class=rowcontent style='font-weight:bold;'>
							<td colspan=2>Sub Total TBS LUAR</td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'>".number_format($totalTonageday)."</td>
							<td style='text-align:right;'>".number_format($totalTonageall)."</td>
					</tr>";
				echo"<tr class=rowcontent style='font-weight:bold;'>
							<td colspan=2>Total TBS Diterima</td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'></td>
							<td style='text-align:right;'>".number_format($gtotalTonageday)."</td>
							<td style='text-align:right;'>".number_format($gtotalTonageall)."</td>
					</tr>";
				$sql="select kodeorg,sum(if(tanggal='".$tanggal."',sisatbskemarin,0)) as tbssisa
							,sum(if(tanggal='".$tanggal."',tbsmasuk,0)) as tbsterima
							,sum(tbsmasuk) as tbsterimasd
							,sum(if(tanggal='".$tanggal."',sisatbskemarin+tbsmasuk,0)) as tbssedia 
							,sum(if(tanggal='".$tanggal."',tbsdiolah,0)) as tbsolah
							,sum(tbsdiolah) as tbsolahsd
							,sum(if(tanggal='".$tanggal."',sisatbskemarin+tbsmasuk,0))-sum(if(tanggal='".$tanggal."',tbsdiolah,0)) as tbssaldo
							,sum(if(tbsdiolah>0,1,0)) as hariolah
							,sum(if(tanggal='".$tanggal."',oer,0)) as oer
							,sum(oer) as oersd
							,sum(if(tanggal='".$tanggal."',ffa,0)) as ffa
							,sum(ffa) as ffasd
							,sum(if(tanggal='".$tanggal."',kadarair,0)) as kadarair
							,sum(kadarair) as kadarairsd
							,sum(if(tanggal='".$tanggal."',kadarkotoran,0)) as kadarkotoran
							,sum(kadarkotoran) as kadarkotoransd
							,sum(if(tanggal='".$tanggal."',oerpk,0)) as oerpk
							,sum(oerpk) as oerpksd
							,sum(if(tanggal='".$tanggal."',ffapk,0)) as ffapk
							,sum(ffapk) as ffapksd
							,sum(if(tanggal='".$tanggal."',kadarairpk,0)) as kadarairpk
							,sum(kadarairpk) as kadarairpksd
							,sum(if(tanggal='".$tanggal."',kadarkotoranpk,0)) as kadarkotoranpk
							,sum(kadarkotoranpk) as kadarkotoranpksd
						from ".$dbname.".pabrik_produksi
						where kodeorg='".$idPabrik."' 
						and tanggal between '".$tanggalawal."' and '".$tanggal."'
					";
				$query=mysql_query($sql) or die(mysql_error());
				echo"
					<tr class=rowcontent>
						<td colspan=12 style='text-align:center;'>B. TBS MASUK DAN DIOLAH</td>
					</tr>
					<tr class=rowcontent>
						<td colspan=10 style='text-align:center;'>Uraian</td>
						<td style='text-align:center;'>Hari Ini</td>
						<td style='text-align:center;'>s/d Hari Ini</td>
					</tr>
				";
			
				while($res=mysql_fetch_assoc($query)){
					$tbssisa=$res['tbssisa'];
					$tbsterima=$res['tbsterima'];
					$tbsterimasd=$res['tbsterimasd'];
					$tbssedia=$res['tbssedia'];
					$tbsolah=$res['tbsolah'];
					$tbsolahsd=$res['tbsolahsd'];
					$tbssaldo=$res['tbssaldo'];
					$hariolah=$res['hariolah'];
					$oer=$res['oer'];
					$oersd=$res['oersd'];
					$ffa=$res['ffa'];
					$ffasd=$res['ffasd'];
					$kadarair=$res['kadarair'];
					$kadarairsd=$res['kadarairsd'];
					$kadarkotoran=$res['kadarkotoran'];
					$kadarkotoransd=$res['kadarkotoransd'];
					$oerpk=$res['oerpk'];
					$oerpksd=$res['oerpksd'];
					$ffapk=$res['ffapk'];
					$ffapksd=$res['ffapksd'];
					$kadarairpk=$res['kadarairpk'];
					$kadarairpksd=$res['kadarairpksd'];
					$kadarkotoranpk=$res['kadarkotoranpk'];
					$kadarkotoranpksd=$res['kadarkotoranpksd'];
				}
				echo"
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>3</td>
						<td colspan=9>TBS sisa kemarin [Kg]</td>
						<td style='text-align:right;'>".number_format($tbssisa)."</td>
						<td style='background-color:DarkGrey;'></td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>4</td>
						<td colspan=9>TBS diterima hari ini [Kg]</td>
						<td style='text-align:right;'>".number_format($tbsterima)."</td>
						<td style='text-align:right;'>".number_format($tbsterimasd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>5</td>
						<td colspan=9>Total TBS [Kg]</td>
						<td style='text-align:right;'>".number_format($tbssedia)."</td>
						<td style='background-color:DarkGrey;'></td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>6</td>
						<td colspan=9>TBS diolah [Kg]</td>
						<td style='text-align:right;'>".number_format($tbsolah)."</td>
						<td style='text-align:right;'>".number_format($tbsolahsd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>7</td>
						<td colspan=9>Sisa TBS [Kg]</td>
						<td style='text-align:right;'>".number_format($tbssaldo)."</td>
						<td style='background-color:DarkGrey;'></td>
					</tr>";
				
				$sql="select kodeorg,tanggal,jammulai,jamselesai,jamdinasbruto from ".$dbname.".pabrik_pengolahan where kodeorg='".$idPabrik."' and tanggal between '".$tanggalawal."' and '".$tanggal."' ORDER BY jammulai";
				$query=mysql_query($sql) or die(mysql_error());
				$jammulai=0;
				$jamolah=0;
				$jamolahsd=0;
				while($res=mysql_fetch_assoc($query)){
					if($res['tanggal']==substr($tanggalmulai,0,10)){
						if($jammulai==""){
							$jammulai=$res['jammulai'];
						}
						$jamselesai=$res['jamselesai'];
						$jamolah +=$res['jamdinasbruto'];
					}
					$jamolahsd +=$res['jamdinasbruto'];
				}

				echo"
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>8</td>
						<td colspan=9>Jam pengolahan [WIB s/d WIB}</td>
						<td style='text-align:right;'>".substr($jammulai,0,5)."</td>
						<td style='text-align:right;'>".substr($jamselesai,0,5)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>9</td>
						<td colspan=9>Jumlah jam olah [Jam]</td>
						<td style='text-align:right;'>".$jamolah."</td>
						<td style='text-align:right;'>".$jamolahsd."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>10</td>
						<td colspan=9>Kapasitas Olah [Ton/Jam]</td>
						<td style='text-align:right;'>".number_format(($jamolah!=0 ? $tbsolah/$jamolah/1000 : 0),2)."</td>
						<td style='text-align:right;'>".number_format(($jamolahsd!=0 ? $tbsolahsd/$jamolahsd/1000 : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>11</td>
						<td colspan=9>Hari Olah (Hari)</td>
						<td style='text-align:right;'>".number_format($hariolah)."</td>
						<td style='background-color:DarkGrey;'></td>
					</tr>";

				echo"
					<tr class=rowcontent>
						<td colspan=12 style='text-align:center;'>C. HASIL DAN MUTU MKS</td>
					</tr>
					<tr class=rowcontent>
						<td colspan=9 style='text-align:center;'>Uraian</td>
						<td style='text-align:center;'>Std (%)</td>
						<td style='text-align:center;'>Hari Ini</td>
						<td style='text-align:center;'>s/d Hari Ini</td>
					</tr>";
				echo"
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>12</td>
						<td colspan=8>Hasil MKS [Kg]</td>
						<td style='text-align:center;'>xx</td>
						<td style='text-align:right;'>".number_format($oer)."</td>
						<td style='text-align:right;'>".number_format($oersd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>13</td>
						<td colspan=8>Ekstraksi [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format(($tbsolah!=0 ? $oer/$tbsolah*100 : 0),2)."</td>
						<td style='text-align:right;'>".number_format(($tbsolahsd!=0 ? $oersd/$tbsolahsd*100 : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>14</td>
						<td colspan=8>FFA [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($ffa,2)."</td>
						<td style='text-align:right;'>".number_format(($hariolah!=0 ? $ffasd/$hariolah : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>15</td>
						<td colspan=8>Kadar Air [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($kadarair,2)."</td>
						<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarairsd/$hariolah : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>15</td>
						<td colspan=8>Kadar Kotoran [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($kadarkotoran,3)."</td>
						<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarkotoransd/$hariolah : 0),3)."</td>
					</tr>";
				
				$sql="select sum(if(tanggal='".$tanggal."',dobi,0))/sum(if(tanggal='".$tanggal."' and dobi!=0,1,0)) as dobirt 
				,sum(dobi)/sum(if(dobi=0,0,1)) as dobirtsd
				from ".$dbname.".pabrik_masukkeluartangki where kodeorg='".$idPabrik."' and tanggal between '".$tanggalawal."' and '".$tanggal."'";
				$query=mysql_query($sql) or die(mysql_error());
				while($res=mysql_fetch_assoc($query)){
					$dobirt=$res['dobirt'];
					$dobirtsd=$res['dobirtsd'];
				}
				echo "<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>16</td>
						<td colspan=8>DOBI [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($dobirt,2)."</td>
						<td style='text-align:right;'>".number_format($dobirtsd,2)."</td>
					</tr>";
				echo"
					<tr class=rowcontent>
						<td colspan=12 style='text-align:center;'>D. HASIL DAN MUTU IKS</td>
					</tr>
					<tr class=rowcontent>
						<td colspan=9 style='text-align:center;'>Uraian</td>
						<td style='text-align:center;'>Std (%)</td>
						<td style='text-align:center;'>Hari Ini</td>
						<td style='text-align:center;'>s/d Hari Ini</td>
					</tr>";
				echo"
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>17</td>
						<td colspan=8>Hasil IKS [Kg]</td>
						<td style='text-align:center;'>xx</td>
						<td style='text-align:right;'>".number_format($oerpk)."</td>
						<td style='text-align:right;'>".number_format($oerpksd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>18</td>
						<td colspan=8>Ekstraksi [ % ]</td>
						<td style='text-align:center;'></td>
						<td style='text-align:right;'>".number_format(($tbsolah!=0 ? $oerpk/$tbsolah*100 : 0),2)."</td>
						<td style='text-align:right;'>".number_format(($tbsolahsd!=0 ? $oerpksd/$tbsolahsd*100 : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>19</td>
						<td colspan=8>Kadar Air [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($kadarairpk,2)."</td>
						<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarairpksd/$hariolah : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>20</td>
						<td colspan=8>Kadar Kotoran [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format($kadarkotoranpk,2)."</td>
						<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarkotoranpksd/$hariolah : 0),2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>21</td>
						<td colspan=8>Kernel Pecah [ % ]</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>22</td>
						<td colspan=8>Jumlah jam olah [Jam]</td>
						<td style='text-align:center;'>xx</td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>23</td>
						<td colspan=8>Kapasitas Olah [Kg/Jam]</td>
						<td style='text-align:center;'>xx</td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
						<td style='text-align:right;'>".number_format(0,2)."</td>
					</tr>";
				echo"
					<tr class=rowcontent>
						<td colspan=12 style='text-align:center;'>E. PENGIRIMAN MKS DAN IKS</td>
					</tr>
					<tr class=rowcontent>
						<td colspan=10 style='text-align:center;'>Uraian</td>
						<td style='text-align:center;'>Hari Ini</td>
						<td style='text-align:center;'>s/d Hari Ini</td>
					</tr>";

				$sqlkirim="select sum(if(tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalakhir."' and kodebarang='40000001',beratbersih,0)) as mkskirim
							,sum(if(kodebarang='40000001',beratbersih,0)) as mkskirimsd
							,sum(if(tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalakhir."' and kodebarang='40000002',beratbersih,0)) as ikskirim
							,sum(if(kodebarang='40000002',beratbersih,0)) as ikskirimsd
						from ".$dbname.".pabrik_timbangan 
						where millcode='".$idPabrik."'
						and intex='0'
						and kodebarang in ('40000001','40000002')
						and tanggal between '".$tanggalawal."' and '".$tanggalakhir."'
					";

				$qrykirim=mysql_query($sqlkirim) or die(mysql_error());

				while($reskirim=mysql_fetch_assoc($qrykirim)){
					$mkskirim=$reskirim['mkskirim'];
					$mkskirimsd=$reskirim['mkskirimsd'];
					$ikskirim=$reskirim['ikskirim'];
					$ikskirimsd=$reskirim['ikskirimsd'];
				}
			
				echo"
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>24</td>
						<td colspan=9>Pengiriman MKS [Kg]</td>
						<td style='text-align:right;'>".number_format($mkskirim)."</td>
						<td style='text-align:right;'>".number_format($mkskirimsd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>25</td>
						<td colspan=9>Pengiriman IKS [Kg]</td>
						<td style='text-align:right;'>".number_format($ikskirim)."</td>
						<td style='text-align:right;'>".number_format($ikskirimsd)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>26</td>
						<td colspan=9>Pengiriman IKS [Goni]</td>
						<td style='text-align:right;'>".number_format(0)."</td>
						<td style='text-align:right;'>".number_format(0)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>27</td>
						<td colspan=9>Penggonian IKS [Kg]</td>
						<td style='text-align:right;'>".number_format(0)."</td>
						<td style='text-align:right;'>".number_format(0)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>28</td>
						<td colspan=9>Penggonian IKS [Goni]</td>
						<td style='text-align:right;'>".number_format(0)."</td>
						<td style='text-align:right;'>".number_format(0)."</td>
					</tr>
					<tr class=rowcontent>
						<td width='1%' style='text-align:center;'>29</td>
						<td colspan=9>Pengiriman Sluge Oil [Kg]</td>
						<td style='text-align:right;'>".number_format(0)."</td>
						<td style='text-align:right;'>".number_format(0)."</td>
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
						,a.tanggalkirim,a.sdtanggal,f.tglawal,f.tglakhir,d.jumlahrealisasippn,d.hasilkerjarealisasi,datediff(f.tglakhir,a.sdtanggal) as selisihtgl
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
					//$selisihtgl=tanggalnormal($res['tglakhir'])-tanggalnormal($res['sdtanggal']);
					$selisihtgl=$res['selisihtgl'];
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