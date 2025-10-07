<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$tanggalkemarin=date('Y-m-d',strtotime('-1 days',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2))));
$tanggalawal=date('Y-m-d h:i:s',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-01 07:00:00'));
$tanggalmulai=date('Y-m-d h:i:s',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2).' 07:00:00'));
$tanggalakhir=date('Y-m-d h:i:s',strtotime('+1 days',strtotime(substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2).' 06:59:59')));
$tglbulanlalu=date('Y-m-d',strtotime('-1 days',strtotime(substr($tanggalawal,0,10))));
$idPabrik=checkPostGet('idPabrik','');

$indukPabrik="";
$sqlinduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$idPabrik."'";
$qryinduk=mysql_query($sqlinduk) or die(mysql_error());
while($resinduk=mysql_fetch_assoc($qryinduk)){
	$indukPabrik=$resinduk['induk'];
}
$optinduk = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$indukPabrik."'");
$namainduk=$optinduk[$indukPabrik];

if($tanggal==''){
	echo "Warning: Tanggal LHP tidak boleh kosong.";
}else{
	if($proses=='preview' or $proses=='excel'){
		if($proses=='excel'){
			$tab[0]="<table class=sortable cellspacing=1 border=1>";
		}else{
			$tab[0]="<table class=sortable cellspacing=1 border=0 style='width:1300px;'>";
		}
		$tab[0].="
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=1 colspan=12 style='font-weight:bold;'>A.I. TBS DITERIMA [Kg]</td>
						<td align=center rowspan=2 colspan=12 style='font-weight:bold;'>A.II. PENERIMAAN TBS PER JAM</td>
					</tr>
					<tr>
						<td width='26%' align=center rowspan=2 colspan=2>Supplier</td>
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
						<td width='7%' align=center colspan=2>JUMLAH [Kg]</td>
						<td width='7%' align=center colspan=2>PUKUL [WIB]</td>
						<td width='7%' align=center colspan=2>JUMLAH [Kg]</td>
						<td align=center colspan=2>PUKUL [WIB]</td>
						<td align=center colspan=2>JUMLAH [Kg]</td>
					</tr>
				</thead>
				<tbody>";
		$sql="select if(substr(a.nospb,9,6)='' or substr(a.nospb,9,6)='0','zzzzzz',substr(a.nospb,9,6)) as unit
				,d.namaorganisasi as namaunit,if(substr(a.nospb,9,6)<>'' and substr(a.nospb,9,6)<>'0',e.namaorganisasi,c.namasupplier) as namadivisi,a.kodecustomer
				,if(substr(a.nospb,9,6)<>'' and substr(a.nospb,9,6)<>'0',substr(a.nospb,9,6),c.namasupplier) as supplier,sum(a.beratbersih) as tonageall,b.tonageday,sum(a.jumlahtandan1) as janjangall,sum(a.jjgsortasi) as jjgsortasi
				,b.janjangday,b.a,b.b,b.c,b.d,b.e,b.f,b.g,b.h,b.apersen,b.bpersen,b.cpersen,b.dpersen,b.epersen,b.fpersen,b.gpersen,b.hpersen
				from ".$dbname.".pabrik_timbangan a
				LEFT JOIN 
				(select substr(z.nospb,9,6) as unit,z.kodecustomer,sum(z.beratbersih) as tonageday,sum(z.jumlahtandan1) as janjangday 
				,sum(z.A) as a,sum(z.B) as b,sum(z.C) as c,sum(z.D) as d,sum(z.E) as e,if(z.kodeorg='',sum(z.jumlahtandan1),100)-(sum(z.A)+sum(z.B)+sum(z.C)+sum(z.D)+sum(z.E)) as f,sum(z.F) as g,sum(z.G) as h
				,sum(z.A)/sum(z.jjgsortasi)*100 as apersen,sum(z.B)/sum(z.jjgsortasi)*100 as bpersen,sum(z.C)/sum(z.jjgsortasi)*100 as cpersen
				,sum(z.D)/sum(z.jjgsortasi)*100 as dpersen,sum(z.E)/sum(z.jjgsortasi)*100 as epersen,(1-((sum(z.A)+sum(z.B)+sum(z.C)+sum(z.D)+sum(z.E))/sum(z.jjgsortasi)))*100 as fpersen
				,sum(z.F)/sum(z.jjgsortasi)*100 as gpersen,sum(z.G)/sum(z.jjgsortasi)*100 as hpersen
				from (select x.notransaksi,x.nospb,x.kodeorg,x.kodecustomer,x.beratbersih,x.millcode,x.tanggal,x.kodebarang,x.jumlahtandan1,x.jjgsortasi,w.A,w.B,w.C,w.D,w.E,w.F,w.G 
						from ".$dbname.".pabrik_timbangan x 
 						LEFT JOIN (select y.notiket 
									,SUM(case when y.kodefraksi = 'B' then y.jumlah else 0 end) as 'A' 
 									,SUM(case when y.kodefraksi = 'A' then y.jumlah else 0 end) as 'B' 
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
				GROUP BY if(substr(a.nospb,9,6)<>'' and substr(a.nospb,9,6)<>'0',substr(a.nospb,9,6),''),a.kodecustomer
				ORDER BY unit,kodecustomer";
		//exit('Warning: '.$sql);
		$query=mysql_query($sql) or die(mysql_error());
		$row=mysql_num_rows($query);
		$kodeunit='';
		$namaunit='';
		$namadivisi='';
		$totalgradA=0;
		$totalgradB=0;
		$totalgradC=0;
		$totalgradD=0;
		$totalgradE=0;
		$totalgradF=0;
		$totalsample=0;
		$totalgradG=0;
		$totalgradH=0;
		$totaljanjangday=0;
		$gtotaljanjangday=0;
		$totalTonageday=0;
		$totalTonageall=0;
		$gtotalTonageday=0;
		$gtotalTonageall=0;
		$gtotalsample=0;

		$intitotalgradA=0;
		$intitotalgradB=0;
		$intitotalgradC=0;
		$intitotalgradD=0;
		$intitotalgradE=0;
		$intitotalgradF=0;
		$intitotalsample=0;
		$intitotalgradG=0;
		$intitotalgradH=0;
		$intitotaljanjangday=0;
		$intitotalTonageday=0;
		$intitotalTonageall=0;
		$pttotalgradA=0;
		$pttotalgradB=0;
		$pttotalgradC=0;
		$pttotalgradD=0;
		$pttotalgradE=0;
		$pttotalgradF=0;
		$pttotalsample=0;
		$pttotalgradG=0;
		$pttotalgradH=0;
		$pttotaljanjangday=0;
		$pttotalTonageday=0;
		$pttotalTonageall=0;

		$no=0;
		$jmlluar=0;
		if($row>0){
			while($res=mysql_fetch_assoc($query)){
				$no+=1;
				if($res['namaunit']!=$namaunit){
					//if($totalTonageday==0){
					//	$tab[$no].="
					//				<td colspan=12>".$res['namaunit']."</td>
					//			";
					//	$no+=1;
					//}else{
					if($totalTonageall>0){
						$tab[$no].="
									<td colspan=2 style='font-weight:bold;'>Sub Total ".$namaunit."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradA/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradB/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradC/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradD/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradE/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format((1-(($totalgradA+$totalgradB+$totalgradC+$totalgradD+$totalgradE)/$totalsample))*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradG/$totalsample*100,2))."</td>
									<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradH/$totalsample*100,2))."</td>
									<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageday)."</td>
									<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageall)."</td>
								";
						$totalgradA=0;
						$totalgradB=0;
						$totalgradC=0;
						$totalgradD=0;
						$totalgradE=0;
						$totalgradF=0;
						$totalsample=0;
						$totalgradG=0;
						$totalgradH=0;
						$totaljanjangday=0;
						$totalTonageday=0;
						$totalTonageall=0;
						$no+=1;
					}
				}
				if($res['unit']=='zzzzzz' and $jmlluar==0){
					$jmlluar=1;
					$tab[$no].="
								<td colspan=2 style='font-weight:bold;'>Sub Total INTI</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradA/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradB/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradC/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradD/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradE/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format((1-(($intitotalgradA+$intitotalgradB+$intitotalgradC+$intitotalgradD+$intitotalgradE)/$intitotalsample))*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradG/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradH/$intitotalsample*100,2))."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($intitotalTonageday)."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($intitotalTonageall)."</td>
							";
					$intitotalgradA=0;
					$intitotalgradB=0;
					$intitotalgradC=0;
					$intitotalgradD=0;
					$intitotalgradE=0;
					$intitotalgradF=0;
					$intitotalsample=0;
					$intitotalgradG=0;
					$intitotalgradH=0;
					$intitotaljanjangday=0;
					$intitotalTonageday=0;
					$intitotalTonageall=0;
					$no+=1;
					$tab[$no].="
								<td colspan=2 style='font-weight:bold;'>Sub Total ".$indukPabrik."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradA/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradB/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradC/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradD/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradE/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format((1-(($pttotalgradA+$pttotalgradB+$pttotalgradC+$pttotalgradD+$pttotalgradE)/$pttotalsample))*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradG/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradH/$pttotalsample*100,2))."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($pttotalTonageday)."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($pttotalTonageall)."</td>
							";
					$pttotalgradA=0;
					$pttotalgradB=0;
					$pttotalgradC=0;
					$pttotalgradD=0;
					$pttotalgradE=0;
					$pttotalgradF=0;
					$pttotalsample=0;
					$pttotalgradG=0;
					$pttotalgradH=0;
					$pttotaljanjangday=0;
					$pttotalTonageday=0;
					$pttotalTonageall=0;
					$no+=1;
				}
				if($res['namaunit']!=$namaunit){
					if($res['unit']=='zzzzzz'){
						$tab[$no].="<td colspan=12>TBS LUAR</td>";
					}else{
						$tab[$no].="<td colspan=12>".$res['namaunit']."</td>";
					}
					$no+=1;
				}
				$tab[$no].="
							<td colspan=2>".$res['namadivisi']."</td>
							<td style='text-align:right;'>".number_format($res['apersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['bpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['cpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['dpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['epersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['fpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['gpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['hpersen'],2)."</td>
							<td style='text-align:right;'>".number_format($res['tonageday'])."</td>
							<td style='text-align:right;'>".number_format($res['tonageall'])."</td>
						";
				$kodeunit=$res['unit'];
				$namaunit=$res['namaunit'];
				$totalgradA += $res['apersen'];
				$totalgradB += $res['bpersen'];
				$totalgradC += $res['cpersen'];
				$totalgradD += $res['dpersen'];
				$totalgradE += $res['epersen'];
				$totalgradF += $res['fpersen'];
				$totalsample += $res['apersen']+$res['bpersen']+$res['cpersen']+$res['dpersen']+$res['epersen']+$res['fpersen'];
				$totalgradG += $res['gpersen'];
				$totalgradH += $res['hpersen'];
				$gtotalgradA += $res['apersen'];
				$gtotalgradB += $res['bpersen'];
				$gtotalgradC += $res['cpersen'];
				$gtotalgradD += $res['dpersen'];
				$gtotalgradE += $res['epersen'];
				$gtotalgradF += $res['fpersen'];
				$gtotalsample += $res['apersen']+$res['bpersen']+$res['cpersen']+$res['dpersen']+$res['epersen']+$res['fpersen'];
				$gtotalgradG += $res['gpersen'];
				$gtotalgradH += $res['hpersen'];
				$totaljanjangday += $res['janjangday'];
				$gtotaljanjangday += $res['janjangday'];
				$totalTonageday += $res['tonageday'];
				$gtotalTonageday += $res['tonageday'];
				$totalTonageall += $res['tonageall'];
				$gtotalTonageall += $res['tonageall'];
				if($res['unit']!='zzzzzz'){
					if(substr($res['unit'],2,2)!='PE'){
						$intitotalgradA += $res['apersen'];
						$intitotalgradB += $res['bpersen'];
						$intitotalgradC += $res['cpersen'];
						$intitotalgradD += $res['dpersen'];
						$intitotalgradE += $res['epersen'];
						$intitotalgradF += $res['fpersen'];
						$intitotalsample += $res['apersen']+$res['bpersen']+$res['cpersen']+$res['dpersen']+$res['epersen']+$res['fpersen'];
						$intitotalgradG += $res['gpersen'];
						$intitotalgradH += $res['hpersen'];
						$intitotaljanjangday += $res['janjangday'];
						$intitotalTonageday += $res['tonageday'];
						$intitotalTonageall += $res['tonageall'];
					}
					$pttotalgradA += $res['apersen'];
					$pttotalgradB += $res['bpersen'];
					$pttotalgradC += $res['cpersen'];
					$pttotalgradD += $res['dpersen'];
					$pttotalgradE += $res['epersen'];
					$pttotalgradF += $res['fpersen'];
					$pttotalsample += $res['apersen']+$res['bpersen']+$res['cpersen']+$res['dpersen']+$res['epersen']+$res['fpersen'];
					$pttotalgradG += $res['gpersen'];
					$pttotalgradH += $res['hpersen'];
					$pttotaljanjangday += $res['janjangday'];
					$pttotalTonageday += $res['tonageday'];
					$pttotalTonageall += $res['tonageall'];
				}
			}
			$no+=1;
			if($totalTonageall>0 and $kodeunit!='zzzzzz'){
				$tab[$no].="
							<td colspan=2 style='font-weight:bold;'>Sub Total ".$namaunit."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradA/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradB/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradC/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradD/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradE/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format((1-(($totalgradA+$totalgradB+$totalgradC+$totalgradD+$totalgradE)/$totalsample))*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradG/$totalsample*100,2))."</td>
							<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradH/$totalsample*100,2))."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageday)."</td>
							<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageall)."</td>
						";
				$totalgradA=0;
				$totalgradB=0;
				$totalgradC=0;
				$totalgradD=0;
				$totalgradE=0;
				$totalgradF=0;
				$totalsample=0;
				$totalgradG=0;
				$totalgradH=0;
				$totaljanjangday=0;
				$totalTonageday=0;
				$totalTonageall=0;
				$no+=1;
				$tab[$no].="
								<td colspan=2 style='font-weight:bold;'>Sub Total INTI</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradA/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradB/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradC/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradD/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradE/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format((1-(($intitotalgradA+$intitotalgradB+$intitotalgradC+$intitotalgradD+$intitotalgradE)/$intitotalsample))*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradG/$intitotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($intitotalsample==0 ? number_format(0,2) : number_format($intitotalgradH/$intitotalsample*100,2))."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($intitotalTonageday)."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($intitotalTonageall)."</td>
						";
					$intitotalgradA=0;
					$intitotalgradB=0;
					$intitotalgradC=0;
					$intitotalgradD=0;
					$intitotalgradE=0;
					$intitotalgradF=0;
					$intitotalsample=0;
					$intitotalgradG=0;
					$intitotalgradH=0;
					$intitotaljanjangday=0;
					$intitotalTonageday=0;
					$intitotalTonageall=0;
					$no+=1;
					$tab[$no].="
								<td colspan=2 style='font-weight:bold;'>Sub Total ".$indukPabrik."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradA/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradB/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradC/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradD/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradE/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format((1-(($pttotalgradA+$pttotalgradB+$pttotalgradC+$pttotalgradD+$pttotalgradE)/$pttotalsample))*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradG/$pttotalsample*100,2))."</td>
								<td width='5%' style='text-align:right;font-weight:bold;'>".($pttotalsample==0 ? number_format(0,2) : number_format($pttotalgradH/$pttotalsample*100,2))."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($pttotalTonageday)."</td>
								<td style='text-align:right;font-weight:bold;'>".number_format($pttotalTonageall)."</td>
							";
					$pttotalgradA=0;
					$pttotalgradB=0;
					$pttotalgradC=0;
					$pttotalgradD=0;
					$pttotalgradE=0;
					$pttotalgradF=0;
					$pttotalsample=0;
					$pttotalgradG=0;
					$pttotalgradH=0;
					$pttotaljanjangday=0;
					$pttotalTonageday=0;
					$pttotalTonageall=0;
					$no+=1;
			}
			if($totalTonageall>0 and $kodeunit=='zzzzzz'){
				$tab[$no].="
						<td colspan=2 style='font-weight:bold;'>Sub Total TBS LUAR</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradA/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradB/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradC/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradD/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradE/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format((1-(($totalgradA+$totalgradB+$totalgradC+$totalgradD+$totalgradE)/$totalsample))*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradG/$totalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($totalsample==0 ? number_format(0,2) : number_format($totalgradH/$totalsample*100,2))."</td>
						<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageday)."</td>
						<td style='text-align:right;font-weight:bold;'>".number_format($totalTonageall)."</td>";
				$no+=1;
			}
			$tab[$no].="
						<td colspan=2 style='font-weight:bold;'>Total TBS Diterima</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradA/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradB/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradC/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradD/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradE/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format((1-(($gtotalgradA+$gtotalgradB+$gtotalgradC+$gtotalgradD+$gtotalgradE)/$gtotalsample))*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradG/$gtotalsample*100,2))."</td>
						<td width='5%' style='text-align:right;font-weight:bold;'>".($gtotalsample==0 ? number_format(0,2) : number_format($gtotalgradH/$gtotalsample*100,2))."</td>
						<td style='text-align:right;font-weight:bold;'>".number_format($gtotalTonageday)."</td>
						<td style='text-align:right;font-weight:bold;'>".number_format($gtotalTonageall)."</td>";
		}
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
					,sum(if(tanggal='".$tanggal."',dobicpo,0)) as dobicpo
					,sum(dobicpo) as dobicposd
					,sum(if(tanggal='".$tanggal."',kernelpecah,0)) as kernelpecah
					,sum(kernelpecah) as kernelpecahsd
					,sum(if(tanggal='".$tanggal."',kerneljamolah,0)) as kerneljamolah
					,sum(kerneljamolah) as kerneljamolahsd
					,sum(if(tanggal='".$tanggal."',kernelkapasitasolah,0)) as kernelkapasitasolah
					,sum(kernelkapasitasolah) as kernelkapasitasolahsd
					,sum(if(tanggal='".$tanggal."',limbah,0)) as limbah
					,sum(limbah) as limbahsd
					,sum(if(tanggal='".$tanggal."',jampompa,0)) as jampompa
					,sum(jampompa) as jampompasd
					,sum(if(tanggal='".$tanggal."',landaplikasi,0)) as landaplikasi
					,sum(landaplikasi) as landaplikasisd
				from ".$dbname.".pabrik_produksi
				where kodeorg='".$idPabrik."' 
				and tanggal between '".substr($tanggalawal,0,10)."' and '".$tanggal."'
				";
		//exit('Warning'.$sql);
		$query=mysql_query($sql) or die(mysql_error());
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
			$dobicpo=$res['dobicpo'];
			$dobicposd=$res['dobicposd'];
			$kernelpecah=$res['kernelpecah'];
			$kernelpecahsd=$res['kernelpecahsd'];
			$kerneljamolah=$res['kerneljamolah'];
			$kerneljamolahsd=$res['kerneljamolahsd'];
			$kernelkapasitasolah=$res['kernelkapasitasolah'];
			$kernelkapasitasolahsd=$res['kernelkapasitasolahsd'];
			$limbah=$res['limbah'];
			$limbahsd=$res['limbahsd'];
			$jampompa=$res['jampompa'];
			$jampompasd=$res['jampompasd'];
			$landaplikasi=$res['landaplikasi'];
			$landaplikasisd=$res['landaplikasisd'];
		}
		$no+=1;
		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>B. TBS MASUK DAN DIOLAH</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=10 style='text-align:center;'>Uraian</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>3</td>
			<td colspan=9>TBS sisa kemarin [Kg]</td>
			<td style='text-align:right;'>".number_format($tbssisa)."</td>
			<td style='background-color:DarkGrey;'></td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>4</td>
			<td colspan=9>TBS diterima hari ini [Kg]</td>
			<td style='text-align:right;'>".number_format($tbsterima)."</td>
			<td style='text-align:right;'>".number_format($tbsterimasd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>5</td>
			<td colspan=9>Total TBS [Kg]</td>
			<td style='text-align:right;'>".number_format($tbssedia)."</td>
			<td style='background-color:DarkGrey;'></td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>6</td>
			<td colspan=9>TBS diolah [Kg]</td>
			<td style='text-align:right;'>".number_format($tbsolah)."</td>
			<td style='text-align:right;'>".number_format($tbsolahsd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>7</td>
			<td colspan=9>Sisa TBS [Kg]</td>
			<td style='text-align:right;'>".number_format($tbssaldo)."</td>
			<td style='background-color:DarkGrey;'></td>";

		$sql="select a.nopengolahan,a.kodeorg,a.tanggal,a.jammulai,a.jamselesai,a.jamdinasbruto,a.jamstagnasi,if(ISNULL(b.jamsdt),0,b.jamsdt) as jamsdt 
			from ".$dbname.".pabrik_pengolahan a 
			LEFT JOIN (select nopengolahan,sum(jamstagnasi) as jamsdt from ".$dbname.".pabrik_pengolahanmesin 
						where kodeorg like '".$idPabrik."%' and downstatus='SDT' GROUP BY nopengolahan) b on b.nopengolahan=a.nopengolahan
		where kodeorg='".$idPabrik."' and tanggal between '".substr($tanggalawal,0,10)."' and '".$tanggal."' ORDER BY jammulai";
		//exit('Warning: '.$sql);
		$query=mysql_query($sql) or die(mysql_error());
		$jammulai=0;
		$jamolah=0;
		$jamsdt=0;
		$jamolahsd=0;
		$jamstagnasihi=0;
		$jamstagnasisd=0;
		$jamsdtsd=0;
		while($res=mysql_fetch_assoc($query)){
			if($res['tanggal']==substr($tanggalmulai,0,10)){
				if($jammulai==""){
					$jammulai=$res['jammulai'];
				}
				$jamselesai=$res['jamselesai'];
				$jamolah +=$res['jamdinasbruto'];
				$jamstagnasihi+=$res['jamstagnasi'];
				$jamsdt +=$res['jamsdt'];
			}
			$jamolahsd +=$res['jamdinasbruto'];
			$jamstagnasisd+=$res['jamstagnasi'];
			$jamsdtsd +=$res['jamsdt'];
		}
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>8</td>
			<td colspan=9>Jam pengolahan [WIB s/d WIB}</td>
			<td style='text-align:right;'>".substr($jammulai,0,5)."</td>
			<td style='text-align:right;'>".substr($jamselesai,0,5)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>9</td>
			<td colspan=9>Jumlah jam olah [Jam]</td>
			<td style='text-align:right;'>".$jamolah."</td>
			<td style='text-align:right;'>".$jamolahsd."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>10</td>
			<td colspan=9>Kapasitas Olah [Ton/Jam]</td>
			<td style='text-align:right;'>".number_format(($jamolah!=0 ? $tbsolah/$jamolah/1000 : 0),2)."</td>
			<td style='text-align:right;'>".number_format(($jamolahsd!=0 ? $tbsolahsd/$jamolahsd/1000 : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>11</td>
			<td colspan=9>Hari Olah (Hari)</td>
			<td style='text-align:right;'>".number_format($hariolah)."</td>
			<td style='background-color:DarkGrey;'></td>";
		$no+=1;

		$sqlstd="select oerbunch,oerkernel from ".$dbname.".bgt_produksi_pks where tahunbudget='".substr($tanggal,0,4)."' limit 1";
		$qrystd=mysql_query($sqlstd) or die(mysql_error());
		while($resstd=mysql_fetch_assoc($qrystd)){
			$stdoer=$resstd['oerbunch'];
			$stdker=$resstd['oerkernel'];
		}

		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>C. HASIL DAN MUTU MKS</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=9 style='text-align:center;'>Uraian</td>
			<td style='text-align:center;'>Std (%)</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>12</td>
			<td colspan=8>Hasil MKS [Kg]</td>
			<td style='text-align:center;'>xx</td>
			<td style='text-align:right;'>".number_format($oer)."</td>
			<td style='text-align:right;'>".number_format($oersd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>13</td>
			<td colspan=8>Ekstraksi [ % ]</td>
			<td style='text-align:right;'>".number_format($stdoer,2)."</td>
			<td style='text-align:right;'>".number_format(($tbsolah!=0 ? $oer/$tbsolah*100 : 0),2)."</td>
			<td style='text-align:right;'>".number_format(($tbsolahsd!=0 ? $oersd/$tbsolahsd*100 : 0),2)."</td>";
		$no+=1;
		$rataffa=($hariolah!=0 ? $ffasd/$hariolah : 0);
		$tab[$no].="
			<td width='1%' style='text-align:center;'>14</td>
			<td colspan=8>FFA [ % ]</td>
			<td style='text-align:right;'>4.00</td>
			<td style='text-align:right;".($ffa>4 ? 'color:red;' : '')."'>".number_format($ffa,2)."</td>
			<td style='text-align:right;".($rataffa>4 ? 'color:red;' : '')."'>".number_format($rataffa,2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>15</td>
			<td colspan=8>Kadar Air [ % ]</td>
			<td style='text-align:right;'>0.25</td>
			<td style='text-align:right;'>".number_format($kadarair,2)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarairsd/$hariolah : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>16</td>
			<td colspan=8>Kadar Kotoran [ % ]</td>
			<td style='text-align:right;'>0.025</td>
			<td style='text-align:right;'>".number_format($kadarkotoran,3)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarkotoransd/$hariolah : 0),3)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>17</td>
			<td colspan=8>DOBI [ % ]</td>
			<td style='text-align:right;'>2.00</td>
			<td style='text-align:right;'>".number_format($dobicpo,2)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $dobicposd/$hariolah : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>D. HASIL DAN MUTU IKS</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=9 style='text-align:center;'>Uraian</td>
			<td style='text-align:center;'>Std (%)</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>18</td>
			<td colspan=8>Hasil IKS [Kg]</td>
			<td style='text-align:center;'>xx</td>
			<td style='text-align:right;'>".number_format($oerpk)."</td>
			<td style='text-align:right;'>".number_format($oerpksd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>19</td>
			<td colspan=8>Ekstraksi [ % ]</td>
			<td style='text-align:right;'>".number_format($stdker,2)."</td>
			<td style='text-align:right;'>".number_format(($tbsolah!=0 ? $oerpk/$tbsolah*100 : 0),2)."</td>
			<td style='text-align:right;'>".number_format(($tbsolahsd!=0 ? $oerpksd/$tbsolahsd*100 : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>20</td>
			<td colspan=8>Kadar Air [ % ]</td>
			<td style='text-align:right;'>7.50</td>
			<td style='text-align:right;'>".number_format($kadarairpk,2)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarairpksd/$hariolah : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>21</td>
			<td colspan=8>Kadar Kotoran [ % ]</td>
			<td style='text-align:right;'>7.50</td>
			<td style='text-align:right;'>".number_format($kadarkotoranpk,2)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kadarkotoranpksd/$hariolah : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>22</td>
			<td colspan=8>Kernel Pecah [ % ]</td>
			<td style='text-align:right;'>20.00</td>
			<td style='text-align:right;'>".number_format($kernelpecah,2)."</td>
			<td style='text-align:right;'>".number_format(($hariolah!=0 ? $kernelpecahsd/$hariolah : 0),2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>23</td>
			<td colspan=8>Jumlah jam olah [Jam]</td>
			<td style='text-align:center;'>xx</td>
			<td style='text-align:right;'>".number_format($kerneljamolah,2)."</td>
			<td style='text-align:right;'>".number_format($kerneljamolahsd,2)."</td>";
		$no+=1;
		$kernelkapasitasolah  =($kerneljamolah==0 ? 0 : $oerpk/$kerneljamolah);
		$kernelkapasitasolahsd=($kerneljamolahsd==0 ? 0 : $oerpksd/$kerneljamolahsd);
		$tab[$no].="
			<td width='1%' style='text-align:center;'>24</td>
			<td colspan=8>Kapasitas Olah [Kg/Jam]</td>
			<td style='text-align:center;'>xx</td>
			<td style='text-align:right;'>".number_format($kernelkapasitasolah,0)."</td>
			<td style='text-align:right;'>".number_format($kernelkapasitasolahsd,0)."</td>";

		$sqlkirim="select sum(if(tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalakhir."' and kodebarang='40000001' and nokontrak not like '%SLU%',beratbersih,0)) as mkskirim
						,sum(if(kodebarang='40000001' and nokontrak not like '%SLU%',beratbersih,0)) as mkskirimsd
						,sum(if(tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalakhir."' and kodebarang='40000001' and nokontrak like '%SLU%',beratbersih,0)) as slukirim
						,sum(if(kodebarang='40000001' and nokontrak like '%SLU%',beratbersih,0)) as slukirimsd
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
			$slukirim=$reskirim['slukirim'];
			$slukirimsd=$reskirim['slukirimsd'];
		}

		$no+=1;
		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>E. PENGIRIMAN MKS DAN IKS</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=10 style='text-align:center;'>Uraian</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>25</td>
			<td colspan=9>Pengiriman MKS [Kg]</td>
			<td style='text-align:right;'>".number_format($mkskirim)."</td>
			<td style='text-align:right;'>".number_format($mkskirimsd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>26</td>
			<td colspan=9>Pengiriman IKS [Kg]</td>
			<td style='text-align:right;'>".number_format($ikskirim)."</td>
			<td style='text-align:right;'>".number_format($ikskirimsd)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>27</td>
			<td colspan=9>Pengiriman IKS [Goni]</td>
			<td style='text-align:right;'>".number_format(0)."</td>
			<td style='text-align:right;'>".number_format(0)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>28</td>
			<td colspan=9>Penggonian IKS [Kg]</td>
			<td style='text-align:right;'>".number_format(0)."</td>
			<td style='text-align:right;'>".number_format(0)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>29</td>
			<td colspan=9>Penggonian IKS [Goni]</td>
			<td style='text-align:right;'>".number_format(0)."</td>
			<td style='text-align:right;'>".number_format(0)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>30</td>
			<td colspan=9>Pengiriman Sluge Oil [Kg]</td>
			<td style='text-align:right;'>".number_format($slukirim)."</td>
			<td style='text-align:right;'>".number_format($slukirimsd)."</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>F. LAND APLIKASI</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=10 style='text-align:center;'>Uraian</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>31</td>
			<td colspan=9>Produksi Limbah ( Ltr )</td>
			<td style='text-align:right;'>".number_format($limbah,2)."</td>
			<td style='text-align:right;'>".number_format($limbahsd,2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>32</td>
			<td colspan=9>Jam Jalan Pompa Land Aplikasi (Jam)</td>
			<td style='text-align:right;'>".number_format($jampompa,2)."</td>
			<td style='text-align:right;'>".number_format($jampompasd,2)."</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>33</td>
			<td colspan=9>Land Aplikasi ( M3 )</td>
			<td style='text-align:right;'>".number_format($landaplikasi,2)."</td>
			<td style='text-align:right;'>".number_format($landaplikasisd,2)."</td>";
		$no+=1;
		$tab[$no].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>G. PENGIRIMAN MKS,IKS DAN CKG PER KONTRAKTOR</td>";
		$no+=1;
		$tab[$no].="
			<td width='1%' style='text-align:center;'>34</td>
			<td colspan=2>Kontraktor</td>
			<td colspan=3>No Kontrak</td>
			<td colspan=3>No SPP</td>
			<td style='text-align:center;'>Qty SPP</td>
			<td style='text-align:center;'>Hari Ini</td>
			<td style='text-align:center;'>s/d Hari Ini</td>";

		$sqlspp="select a.nokontrak,a.nosipb as nospp,a.kodecustomer,e.namasupplier,b.tanggaldo as tglspp,f.jmldo,b.qty,a.netto,a.nettohi,c.koderekanan,d.namacustomer 
				from (select z.nokontrak,z.nosipb,z.kodecustomer,sum(z.beratbersih) as netto
							,sum(case when z.tanggal between '".$tanggalmulai."' and '".$tanggalakhir."' then z.beratbersih else 0 end) as nettohi
						from ".$dbname.".pabrik_timbangan z
						where z.nokontrak<>'' and z.nokontrak<>'NULL' and z.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$indukPabrik."')
						GROUP BY z.nosipb,z.nokontrak,z.kodecustomer) a
				LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman b on b.nokontrak=a.nokontrak and b.nodo=a.nosipb
				LEFT JOIN ".$dbname.".pmn_kontrakjual c on c.nokontrak=a.nokontrak
				LEFT JOIN ".$dbname.".pmn_4customer d on d.kodecustomer=c.koderekanan
				LEFT JOIN ".$dbname.".log_5supplier e on e.kodetimbangan=a.kodecustomer
				LEFT JOIN (select x.nokontrak,x.nosipb,sum(x.beratbersih) as jmldo from ".$dbname.".pabrik_timbangan x
							where x.nokontrak<>'' and x.nokontrak<>'NULL' and x.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$indukPabrik."')
							GROUP BY x.nosipb,x.nokontrak) f on f.nokontrak=a.nokontrak and f.nosipb=a.nosipb
				where b.qty<>'' and (f.jmldo<b.qty or b.tanggaldo like '".substr($tanggalmulai,0,7)."%') and b.tanggaldo like '".substr($tanggalmulai,0,4)."%'
				ORDER BY b.tanggaldo,a.kodecustomer";
		//exit('Warning: '.$sqlspp);
		$qryspp=mysql_query($sqlspp) or die(mysql_error());
		$nu=33;
		while($resspp=mysql_fetch_assoc($qryspp)){
			$nu+=1;
			$no+=1;
			$tab[$no].="
				<td width='1%' style='text-align:center;'>".($resspp['jmldo']<$resspp['qty'] ? '' : 'v')."</td>
				<td colspan=2>".$resspp['namasupplier']."</td>
				<td colspan=3>".$resspp['nokontrak']."</td>
				<td colspan=3>".$resspp['nospp']."</td>
				<td style='text-align:right;'>".number_format($resspp['qty'],0)."</td>
				<td style='text-align:right;'>".number_format($resspp['nettohi'],0)."</td>
				<td style='text-align:right;'>".number_format($resspp['netto'],0)."</td>";
		}

		$tonage[]=array();
		$sqltbs="select concat(left(tanggal,4),substr(tanggal,6,2),substr(tanggal,9,2),substr(tanggal,12,2)) as waktu,sum(beratbersih) as tonage
				from ".$dbname.".pabrik_timbangan 
				where millcode='".$idPabrik."'
				and kodebarang='40000003'
				and tanggal between '".$tanggalmulai."' and '".$tanggalakhir."'
				GROUP BY left(tanggal,13)
				ORDER BY tanggal";
		$qrytbs=mysql_query($sqltbs) or die(mysql_error());
		//exit('Warning: '.$sqltbs);
		while($restbs=mysql_fetch_assoc($qrytbs)){
			$tonage[$restbs['waktu']]=$restbs['tonage'];
			$totaltonage+=$restbs['tonage'];
		}
		$no2=0;
		while($no2<12){
			$no2+=1;
			$waktumulai=substr($tanggalmulai,0,4).substr($tanggalmulai,5,2).substr($tanggalmulai,8,2).sprintf("%02d",$no2);
			if($no2+12==24){
				$waktumulai2=substr($tanggalakhir,0,4).substr($tanggalakhir,5,2).substr($tanggalakhir,8,2).sprintf("%02d",0);
			}else{
				$waktumulai2=substr($tanggalmulai,0,4).substr($tanggalmulai,5,2).substr($tanggalmulai,8,2).sprintf("%02d",$no2+12);
			}
			$waktuakhir=substr($tanggalakhir,0,4).substr($tanggalakhir,5,2).substr($tanggalakhir,8,2).sprintf("%02d",$no2);
			$tab2[$no2].="<td colspan=2 style='font-weight:normal;text-align:center;'>".($no2).':00'."</td>
						<td colspan=2 style='font-weight:normal;text-align:right;'>".number_format($tonage[$waktumulai])."</td>
						<td colspan=2 style='font-weight:normal;text-align:center;;'>".($no2+12).':00'."</td>
						<td colspan=2 style='font-weight:normal;text-align:right;'>".number_format($tonage[$waktumulai2])."</td>
						<td colspan=2 style='font-weight:normal;text-align:center;;'>".($no2).':00'."</td>
						<td colspan=2 style='font-weight:normal;text-align:right;'>".number_format($tonage[$waktuakhir])."</td>";
		}
		$no2+=1;
		$tab2[$no2].="
			<td colspan=10 style='font-weight:bold;'>TOTAL PENERIMAAN TBS [Kg]</td>
			<td colspan=2 style='text-align:right;font-weight:bold;'>".number_format($totaltonage)."</td>";

		$sqltbs="select tanggal,sum(kuantitas) as mkslalu,sum(kernelquantity) as ikslalu from ".$dbname.".pabrik_masukkeluartangki
				where kodeorg='".$idPabrik."' and tanggal='".$tanggalkemarin."' and kodetangki not like 'SL%'";
		//exit('Warning: '.$sqltbs);
		$qrytbs=mysql_query($sqltbs) or die(mysql_error());
		while($restbs=mysql_fetch_assoc($qrytbs)){
			$mkslalu=$restbs['mkslalu'];
			$ikslalu=$restbs['ikslalu'];
		}
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>K. STOCK AWAL MKS DAN IKS</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=10 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>Pagi Ini</td>";
		$no2+=1;
		$tab2[$no2].="
			<td style='text-align:center;'>35</td>
			<td colspan=9>Stock MKS di PKS [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($mkslalu)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td style='text-align:center;'>36</td>
			<td colspan=9>Stock IKS di PKS [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($ikslalu)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>L. STOCK AKHIR MKS DAN IKS [HARI INI]</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=8 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>Goni</td>
			<td colspan=2 style='text-align:center;'>Kg</td>";
		$no2+=1;
		$tab2[$no2].="
			<td style='text-align:center;'>37</td>
			<td colspan=7>Stock MKS di PKS [Kg]</td>
			<td colspan=2 style='background-color:DarkGrey;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($mkslalu+$oer-$mkskirim)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>38</td>
			<td colspan=11>Stock IKS di PKS [Kg]</td>";

		$sqlcuci="select a.* from ".$dbname.".pabrik_pembersihantangki a where a.kodeorg='".$idPabrik."' 
				and a.tanggal between '".$tanggalmulai."' and '".$tanggalakhir."' and a.kodebarang='40000001' and a.kodetangki like 'ST%'
				ORDER BY a.kodetangki";
		//exit('Warning: '.$sqlcuci);
		$qrycuci=mysql_query($sqlcuci) or die(mysql_error());
		$cucitangki1=0;
		$cucitangki2=0;
		while($rescuci=mysql_fetch_assoc($qrycuci)){
			if($rescuci['kodetangki']=='ST01'){
				$cucitangki1=$rescuci['jumlah'];
			}else{
				$cucitangki2=$rescuci['jumlah'];
			}
		}

		$sqliks="select a.*,b.keterangan as namatangki from ".$dbname.".pabrik_masukkeluartangki a
				left join ".$dbname.".pabrik_5tangki b on b.kodeorg=a.kodeorg and b.kodetangki=a.kodetangki
				where a.kodeorg='".$idPabrik."' and a.tanggal = '".$tanggal."'
				ORDER BY a.kodetangki";
		//exit('Warning: '.$sqliks);
		$qryiks=mysql_query($sqliks) or die(mysql_error());
		$kernelbagi=0;
		$cpobagi=0;
		$mikobagi=0;
		while($resiks=mysql_fetch_assoc($qryiks)){
			if($resiks['kernelquantity']!='0'){
				$no2+=1;
				$tab2[$no2].="
					<td colspan=1 style='text-align:center;'></td>
					<td colspan=7>".$resiks['namatangki']."</td>
					<td colspan=2></td>
					<td colspan=2 style='text-align:right;'>".number_format($resiks['kernelquantity'])."</td>";
					$kernelbagi+=1;
					$kernelkdair+=$resiks['kernelkdair'];
					$kernelkdkot+=$resiks['kernelkdkot'];
					$kernelffa+=$resiks['kernelffa'];
			}else{
				if(substr($resiks['kodetangki'],0,2)=='ST'){
					$cpobagi+=1;
					$cpokdair[$cpobagi]+=$resiks['cpokdair'];
					$cpokdkot[$cpobagi]+=$resiks['cpokdkot'];
					$cpoffa[$cpobagi]+=$resiks['cpoffa'];
					$dobi[$cpobagi]+=$resiks['dobi'];
					$kuantitas[$cpobagi]+=$resiks['kuantitas'];
					$storage[$cpobagi]=$resiks['namatangki'];
				}else{
					$mikobagi+=1;
					$mikokdair[$mikobagi]+=$resiks['cpokdair'];
					$mikokdkot[$mikobagi]+=$resiks['cpokdkot'];
					$mikoffa[$mikobagi]+=$resiks['cpoffa'];
					$mikodobi[$mikobagi]+=$resiks['dobi'];
					$mikokuantitas[$mikobagi]+=$resiks['kuantitas'];
					$mikostorage[$mikobagi]=$resiks['namatangki'];
				}
			}
		}

		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'></td>
			<td colspan=7>Stock IKS di PKS [Kg]</td>
			<td colspan=2 style='background-color:DarkGrey;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($ikslalu+$oerpk-$ikskirim)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>39</td>
			<td colspan=7>Kadar Air IKS di PKS</td>
			<td colspan=2 style='text-align:center;'>%</td>
			<td colspan=2 style='text-align:right;'>".number_format(($kernelbagi>0 ? $kernelkdair/$kernelbagi : 0),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>40</td>
			<td colspan=7>Kadar Kotoran IKS di PKS</td>
			<td colspan=2 style='text-align:center;'>%</td>
			<td colspan=2 style='text-align:right;'>".number_format(($kernelbagi>0 ? $kernelkdkot/$kernelbagi : 0),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>M. MUTU STOCK MKS [HARI INI]</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=6 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>".str_replace(' ','_',$storage[1])."</td>
			<td colspan=2 style='text-align:center;'>".str_replace(' ','_',$storage[2])."</td>
			<td colspan=2 style='text-align:center;'>Rata-rata</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>41</td>
			<td colspan=5>FFA [ % ]</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpoffa[1],2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpoffa[2],2)."</td>
			<td colspan=2 style='text-align:right;'>"
			.number_format(($kuantitas[1]+$kuantitas[2])==0 ? 0 : (($kuantitas[1]*$cpoffa[1])+($kuantitas[2]*$cpoffa[2]))/($kuantitas[1]+$kuantitas[2]),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>42</td>
			<td colspan=5>Kadar Air [ % ]</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpokdair[1],2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpokdair[2],2)."</td>
			<td colspan=2 style='text-align:right;'>"
			.number_format(($kuantitas[1]+$kuantitas[2])==0 ? 0 : (($kuantitas[1]*$cpokdair[1])+($kuantitas[2]*$cpokdair[2]))/($kuantitas[1]+$kuantitas[2]),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>43</td>
			<td colspan=5>Kadar Kotoran [ % ]</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpokdkot[1],3)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($cpokdkot[2],3)."</td>
			<td colspan=2 style='text-align:right;'>"
			.number_format(($kuantitas[1]+$kuantitas[2])==0 ? 0 : (($kuantitas[1]*$cpokdkot[1])+($kuantitas[2]*$cpokdkot[2]))/($kuantitas[1]+$kuantitas[2]),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>44</td>
			<td colspan=5>DOBI [ % ]</td>
			<td colspan=2 style='text-align:right;'>".number_format($dobi[1],2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($dobi[2],2)."</td>
			<td colspan=2 style='text-align:right;'>"
			.number_format(($kuantitas[1]+$kuantitas[2])==0 ? 0 : (($kuantitas[1]*$dobi[1])+($kuantitas[2]*$dobi[2]))/($kuantitas[1]+$kuantitas[2]),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>45</td>
			<td colspan=5>Pembersihan Tangki [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($cucitangki1,2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($cucitangki2,2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format(($cucitangki1+$cucitangki2),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>46</td>
			<td colspan=5>Stock MKS di Storage [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($kuantitas[1],2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($kuantitas[2],2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format(($kuantitas[1]+$kuantitas[2]),2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>N. DATA PRESS</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=4 style='text-align:center;'>Tek. Press</td>
			<td colspan=4 style='text-align:center;'>Suhu Digester</td>
			<td colspan=4 style='text-align:center;'>Jam Press</td>";

		$sqlair="select a.* from ".$dbname.".pabrik_datapress a
				where a.kodeorg='".$idPabrik."' and a.tanggal = '".$tanggal."'";
		$qryair=mysql_query($sqlair) or die(mysql_error());
		while($resair=mysql_fetch_assoc($qryair)){
			$tekpressp1=$resair['tekpressp1'];
			$tekpressp2=$resair['tekpressp2'];
			$tekpressp3=$resair['tekpressp3'];
			$tekpressp4=$resair['tekpressp4'];
			$suhud1=$resair['suhud1'];
			$suhud2=$resair['suhud2'];
			$suhud3=$resair['suhud3'];
			$suhud4=$resair['suhud4'];
			$jampressp1=$resair['jampressp1'];
			$jampressp2=$resair['jampressp2'];
			$jampressp3=$resair['jampressp3'];
			$jampressp4=$resair['jampressp4'];
			$airkemarin=$resair['airsisa']-$resair['airclarifier']+$resair['airboiler']+$resair['airproduksi']+$resair['airpembersihan']+$resair['airdomestik'];
			$airclarifier=$resair['airclarifier'];
			$airboiler=$resair['airboiler'];
			$airproduksi=$resair['airproduksi'];
			$airpembersihan=$resair['airpembersihan'];
			$airdomestik=$resair['airdomestik'];
			$airsisa=$resair['airsisa'];
		}

		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>P1</td>
			<td colspan=3 style='text-align:right;'>".number_format($tekpressp1,0)."</td>
			<td colspan=1 style='text-align:center;'>D1</td>
			<td colspan=3 style='text-align:right;'>".number_format($suhud1,0)."</td>
			<td colspan=1 style='text-align:center;'>P1</td>
			<td colspan=3 style='text-align:right;'>".number_format($jampressp1,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>P2</td>
			<td colspan=3 style='text-align:right;'>".number_format($tekpressp2,0)."</td>
			<td colspan=1 style='text-align:center;'>D2</td>
			<td colspan=3 style='text-align:right;'>".number_format($suhud2,0)."</td>
			<td colspan=1 style='text-align:center;'>P2</td>
			<td colspan=3 style='text-align:right;'>".number_format($jampressp2,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>P3</td>
			<td colspan=3 style='text-align:right;'>".number_format($tekpressp3,0)."</td>
			<td colspan=1 style='text-align:center;'>D3</td>
			<td colspan=3 style='text-align:right;'>".number_format($suhud3,0)."</td>
			<td colspan=1 style='text-align:center;'>P3</td>
			<td colspan=3 style='text-align:right;'>".number_format($jampressp3,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>P4</td>
			<td colspan=3 style='text-align:right;'>".number_format($tekpressp4,0)."</td>
			<td colspan=1 style='text-align:center;'>D4</td>
			<td colspan=3 style='text-align:right;'>".number_format($suhud4,0)."</td>
			<td colspan=1 style='text-align:center;'>P4</td>
			<td colspan=3 style='text-align:right;'>".number_format($jampressp4,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=9 style='text-align:center;'>Total</td>
			<td colspan=3 style='text-align:right;'>".number_format($jampressp1+$jampressp2+$jampressp3+$jampressp4,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>47</td>
			<td colspan=7>Air Sisa kemarin (Bak Basin)</td>
			<td colspan=2 style='text-align:right;'>".number_format($airkemarin,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>48</td>
			<td colspan=7>Air Clarifier Tank</td>
			<td colspan=2 style='text-align:right;'>".number_format($airclarifier,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>49</td>
			<td colspan=7>Air Boiler</td>
			<td colspan=2 style='text-align:right;'>".number_format($airboiler,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>50</td>
			<td colspan=7>Air Produksi</td>
			<td colspan=2 style='text-align:right;'>".number_format($airproduksi,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>51</td>
			<td colspan=7>Air Domestik Camp</td>
			<td colspan=2 style='text-align:right;'>".number_format($airdomestik,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>52</td>
			<td colspan=7>Air Pembersihan</td>
			<td colspan=2 style='text-align:right;'>".number_format($airpembersihan,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>53</td>
			<td colspan=7>Air Sisa (Bak Basin)</td>
			<td colspan=2 style='text-align:right;'>".number_format($airsisa,2)."</td>
			<td colspan=2 style='text-align:center;'>M3</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>54</td>
			<td colspan=7>PKS Break Down [hari ini s/d hari ini] - [Jam]</td>
			<td colspan=2 style='text-align:right;'>".number_format($jamstagnasihi,2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($jamstagnasisd,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>55</td>
			<td colspan=7>Prosentase PKS Break Down s/d hari ini [ % ]</td>
			<td colspan=2 style='text-align:right;'>".($jamolahsd==0 ? number_format(0,2) : number_format($jamstagnasisd/$jamolahsd*100,2))."</td>
			<td colspan=2 style='background-color:DarkGrey;'></td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>56</td>
			<td colspan=7>PKS Stagnasi (hari ini s/d hari ini)</td>
			<td colspan=2 style='text-align:right;'>".number_format($jamsdt,2)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($jamsdtsd,2)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>O. ANGKUTAN PRODUKSI [HARI INI]</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=6 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>MKS</td>
			<td colspan=2 style='text-align:center;'>Cangkang</td>
			<td colspan=2 style='text-align:center;'>IKS</td>";

		$sqlkirim="select a.*,if(b.koderekanan='' or ISNULL(b.koderekanan),substr(a.nosipb,9,3),b.koderekanan) as koderekanan 
					from ".$dbname.".pabrik_timbangan a
					left join ".$dbname.".pmn_kontrakjual b on b.nokontrak=a.nokontrak
					where a.millcode='".$idPabrik."'
					and a.intex='0'
					and a.kodebarang in ('40000001','40000002','40000005')
					and a.tanggal between '".$tanggalmulai."' and '".$tanggalakhir."'
					order by a.tanggal";
		$qrykirim=mysql_query($sqlkirim) or die(mysql_error());
		while($reskirim=mysql_fetch_assoc($qrykirim)){
			$jmlrekanan[$reskirim['kodebarang']][$reskirim['koderekanan']]=$reskirim['koderekanan'];
			$jmlcustomer[$reskirim['kodebarang']][$reskirim['kodecustomer']]=$reskirim['kodecustomer'];
			$jmlrate[$reskirim['kodebarang']]+=1;
			$jmlkirim[$reskirim['kodebarang']]+=$reskirim['beratbersih'];
			if(empty($jamawal[$reskirim['kodebarang']])){
				$jamawal[$reskirim['kodebarang']]=$reskirim['tanggal'];
			}
			$jamakhir[$reskirim['kodebarang']]=$reskirim['tanggal'];
		}

		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>57</td>
			<td colspan=5>Jumlah Kontaktor</td>
			<td colspan=2 style='text-align:right;'>".count($jmlcustomer['40000001'])."</td>
			<td colspan=2 style='text-align:right;'>".count($jmlcustomer['40000005'])."</td>
			<td colspan=2 style='text-align:right;'>".count($jmlcustomer['40000002'])."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>58</td>
			<td colspan=5>Jumlah Rate [Rate]</td>
			<td colspan=2 style='text-align:right;'>".$jmlrate['40000001']."</td>
			<td colspan=2 style='text-align:right;'>".$jmlrate['40000005']."</td>
			<td colspan=2 style='text-align:right;'>".$jmlrate['40000002']."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>59</td>
			<td colspan=5>Jumlah Pengiriman [Kg]</td>
			<td colspan=2 style='text-align:right;'>".$jmlkirim['40000001']."</td>
			<td colspan=2 style='text-align:right;'>".$jmlkirim['40000005']."</td>
			<td colspan=2 style='text-align:right;'>".$jmlkirim['40000002']."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>60</td>
			<td colspan=5>Jam Awal [WIB]</td>
			<td colspan=2 style='text-align:right;'>".substr($jamawal['40000001'],11,8)."</td>
			<td colspan=2 style='text-align:right;'>".substr($jamawal['40000005'],11,8)."</td>
			<td colspan=2 style='text-align:right;'>".substr($jamawal['40000002'],11,8)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>61</td>
			<td colspan=5>Jam Akhir [WIB]</td>
			<td colspan=2 style='text-align:right;'>".substr($jamakhir['40000001'],11,8)."</td>
			<td colspan=2 style='text-align:right;'>".substr($jamakhir['40000005'],11,8)."</td>
			<td colspan=2 style='text-align:right;'>".substr($jamakhir['40000002'],11,8)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>P. JANJANG KOSONG DAN CANGKANG</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=8 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>Hari Ini</td>
			<td colspan=2 style='text-align:center;'>s/d Hari Ini</td>";
		/*
		$sqllain="select a.*,b.namabarang from ".$dbname.".pabrik_stokbarang a
				left join log_5masterbarang b on b.kodebarang=a.kodebarang
				where a.kodeorg='".$idPabrik."' and a.tanggal like '".substr($tanggal,0,7)."%'
				ORDER BY a.kodebarang";
		*/
		$sqllain="select a.kodeorg,a.kodebarang,b.namabarang
				,sum(if(a.tanggal='".$tanggal."',a.produksi,0)) as produksihi
				,sum(if(a.tanggal='".$tanggal."',a.pemakaian,0)) as pemakaianhi
				,sum(if(a.tanggal='".$tanggal."',a.penjualan,0)) as penjualanhi
				,sum(if(a.tanggal='".$tanggal."',a.produksi,0))-sum(if(a.tanggal='".$tanggal."',a.pemakaian,0))-sum(if(a.tanggal='".$tanggal."',a.penjualan,0)) as sisahi
				,sum(if(a.tanggal='".$tglbulanlalu."',a.sisa,0)) as saldoawalsd
				,sum(a.produksi)-sum(if(a.tanggal='".$tglbulanlalu."',a.produksi,0)) as produksisd
				,sum(a.pemakaian)-sum(if(a.tanggal='".$tglbulanlalu."',a.pemakaian,0)) as pemakaiansd
				,sum(a.penjualan)-sum(if(a.tanggal='".$tglbulanlalu."',a.penjualan,0)) as penjualansd
				,sum(if(a.tanggal='".$tanggal."',a.sisa,0)) as sisasd
				from ".$dbname.".pabrik_stokbarang a
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				where a.kodeorg='".$idPabrik."' and a.tanggal BETWEEN '".$tglbulanlalu."' and '".$tanggal."'
				GROUP BY a.kodeorg,a.kodebarang
				ORDER BY a.kodebarang";

		$qrylain=mysql_query($sqllain) or die(mysql_error());
		$jksproduksihi=0;
		$jkspemakaianhi=0;
		$jkspenjualanhi=0;
		$jkssisahi=0;
		$jkssaldoawalsd=0;
		$jksproduksisd=0;
		$jkspemakaiansd=0;
		$jkspenjualansd=0;
		$jkssisasd=0;

		$ckgproduksihi=0;
		$ckgpemakaianhi=0;
		$ckgpenjualanhi=0;
		$ckgsisahi=0;
		$ckgsaldoawalsd=0;
		$ckgproduksisd=0;
		$ckgpemakaiansd=0;
		$ckgpenjualansd=0;
		$ckgsisasd=0;

		$fbrproduksihi=0;
		$fbrpemakaianhi=0;
		$fbrpenjualanhi=0;
		$fbrsisahi=0;
		$fbrsaldoawalsd=0;
		$fbrproduksisd=0;
		$fbrpemakaiansd=0;
		$fbrpenjualansd=0;
		$fbrsisasd=0;

		$ln2produksihi=0;
		$ln2pemakaianhi=0;
		$ln2penjualanhi=0;
		$ln2sisahi=0;
		$ln2saldoawalsd=0;
		$ln2produksisd=0;
		$ln2pemakaiansd=0;
		$ln2penjualansd=0;
		$ln2sisasd=0;

		while($reslain=mysql_fetch_assoc($qrylain)){
			if($reslain['kodebarang']=='40000004'){
				$jksproduksihi=$reslain['produksihi'];
				$jkspemakaianhi=$reslain['pemakaianhi'];
				$jkspenjualanhi=$reslain['penjualanhi'];
				$jkssisahi=$reslain['sisahi'];
				$jkssaldoawalsd=$reslain['saldoawalsd'];
				$jksproduksisd=$reslain['produksisd'];
				$jkspemakaiansd=$reslain['pemakaiansd'];
				$jkspenjualansd=$reslain['penjualansd'];
				$jkssisasd=$reslain['sisasd'];
			}elseif($reslain['kodebarang']=='40000005'){
				$ckgproduksihi=$reslain['produksihi'];
				$ckgpemakaianhi=$reslain['pemakaianhi'];
				$ckgpenjualanhi=$reslain['penjualanhi'];
				$ckgsisahi=$reslain['sisahi'];
				$ckgsaldoawalsd=$reslain['saldoawalsd'];
				$ckgproduksisd=$reslain['produksisd'];
				$ckgpemakaiansd=$reslain['pemakaiansd'];
				$ckgpenjualansd=$reslain['penjualansd'];
				$ckgsisasd=$reslain['sisasd'];
			}elseif($reslain['kodebarang']=='40000016'){
				$fbrproduksihi=$reslain['produksihi'];
				$fbrpemakaianhi=$reslain['pemakaianhi'];
				$fbrpenjualanhi=$reslain['penjualanhi'];
				$fbrsisahi=$reslain['sisahi'];
				$fbrsaldoawalsd=$reslain['saldoawalsd'];
				$fbrproduksisd=$reslain['produksisd'];
				$fbrpemakaiansd=$reslain['pemakaiansd'];
				$fbrpenjualansd=$reslain['penjualansd'];
				$fbrsisasd=$reslain['sisasd'];
			}else{
				$ln2produksihi+=$reslain['produksihi'];
				$ln2pemakaianhi+=$reslain['pemakaianhi'];
				$ln2penjualanhi+=$reslain['penjualanhi'];
				$ln2sisahi+=$reslain['sisahi'];
				$ln2saldoawalsd+=$reslain['saldoawalsd'];
				$ln2produksisd+=$reslain['produksisd'];
				$ln2pemakaiansd+=$reslain['pemakaiansd'];
				$ln2penjualansd+=$reslain['penjualansd'];
				$ln2sisasd+=$reslain['sisasd'];
			}
		}

		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>62</td>
			<td colspan=7>Sisa Janjang kosong bulan lalu [Kg]</td>
			<td colspan=2  style='background-color:DarkGrey;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($jkssaldoawalsd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>63</td>
			<td colspan=7>Janjang Kosong Diproduksi [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($jksproduksihi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($jksproduksisd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>64</td>
			<td colspan=7>Janjang Kosong Diangkut [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($jkspemakaianhi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($jkspemakaiansd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>65</td>
			<td colspan=7>Sisa Janjang Kosong [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($jkssisahi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($jkssisasd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>66</td>
			<td colspan=7>Stock Cangkang Bulan Lalu [Kg]</td>
			<td colspan=2 style='background-color:DarkGrey;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgsaldoawalsd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>67</td>
			<td colspan=7>Produksi Cangkang [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgproduksihi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgproduksisd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>68</td>
			<td colspan=7>Penerimaan Cangkang [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format(0,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format(0,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>69</td>
			<td colspan=7>Pemakaian Cangkang [Kg]</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgpemakaianhi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgpemakaiansd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>70</td>
			<td colspan=7>Pengiriman  Cangkang ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgpenjualanhi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgpenjualansd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>71</td>
			<td colspan=7>Stock Cangkang ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgsisahi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($ckgsisasd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>72</td>
			<td colspan=7>Stock Fibre Bulan Lalu ( Kg )</td>
			<td colspan=2  style='background-color:DarkGrey;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrsaldoawalsd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>73</td>
			<td colspan=7>Produksi Fibre ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrproduksihi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrproduksisd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>74</td>
			<td colspan=7>Pemakaian Fibre ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrpemakaianhi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrpemakaiansd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>75</td>
			<td colspan=7>Penerimaan Fibre ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format(0,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format(0,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>76</td>
			<td colspan=7>Pengiriman Fibre (Kg)</td>
			<td colspan=2 style='text-align:right;'>".number_format($ln2penjualanhi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($ln2penjualansd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>77</td>
			<td colspan=7>Stock Fibre ( Kg )</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrsisahi,0)."</td>
			<td colspan=2 style='text-align:right;'>".number_format($fbrsisasd,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=12 style='text-align:center;font-weight:bold;'>Q. STOCK PENAMPUNGAN CPO</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=8 style='text-align:center;'>Uraian</td>
			<td colspan=2 style='text-align:center;'>Hari Ini</td>
			<td colspan=2 style='text-align:center;'>s/d Hari Ini</td>";
		$stmikokuantitas=0;
		for($z = 1; $z <= $mikobagi; $z++) {
			$no2+=1;
			$tab2[$no2].="
				<td colspan=1 style='text-align:center;'>".(77+$z)."</td>
				<td colspan=7>".$mikostorage[$z]."</td>
				<td colspan=2 style='text-align:right;'></td>
				<td colspan=2 style='text-align:right;'>".number_format($mikokuantitas[$z],0)."</td>";
			$stmikokuantitas+=$mikokuantitas[$z];
		}
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>".(77+$z+1)."</td>
			<td colspan=7>Total Stock penampungan CPO</td>
			<td colspan=2 style='text-align:right;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($stmikokuantitas,0)."</td>";
		$no2+=1;
		$tab2[$no2].="
			<td colspan=1 style='text-align:center;'>".(77+$z+2)."</td>
			<td colspan=7>Total Stock penampungan CPO + Storage</td>
			<td colspan=2 style='text-align:right;'></td>
			<td colspan=2 style='text-align:right;'>".number_format($stmikokuantitas+($mkslalu+$oer-$mkskirim),0)."</td>";
	}
switch($proses)
{
	case'preview':
		if(count($tab2)>count($tab)){
			$selisihlength=count($tab2)-count($tab);
			for($z = 0; $z <= $selisihlength; $z++) {
				$no+=1;
				$tab[$no].="<td colspan=12></td>";
			}		
		}
		echo $tab[0];
		$arrlength = count($tab);
		for($x = 1; $x < $arrlength; $x++) {
			echo "<tr class=rowcontent>";
			echo $tab[$x].$tab2[$x];
			echo "</tr>";
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
        $sDet="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$idPabrik."'";
        $qDet=mysql_query($sDet) or die(mysql_error());
        while($rDet=mysql_fetch_assoc($qDet)){
             $namaorganisasi=$rDet['namaorganisasi'];
        }
		$bgcoloraja="bgcolor=#DEDEDE align=center";
		if(count($tab2)>count($tab)){
			$selisihlength=count($tab2)-count($tab);
			for($z = 0; $z <= $selisihlength; $z++) {
				$no+=1;
				$tab[$no].="<td colspan=12></td>";
			}		
		}
		$isi="<table><font size='4'><b>LAPORAN HARIAN PRODUKSI</b></font>";
		$isi.="<br><b>".$idPabrik." - ".$namaorganisasi."</b>";
		$isi.="<br><b>"."Periode : ".substr($tanggalawal,0,10)." s/d ".substr($tanggalmulai,0,10)."</b></table>";
		$isi.=$tab[0];
		$arrlength = count($tab);
		for($x = 1; $x < $arrlength; $x++) {
			$isi.="<tr class=rowcontent>";
			$isi.=$tab[$x].$tab2[$x];
			$isi.="</tr>";
		}		
		$isi.="</tbody></table>";
		$isi.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		$dte=date("Hms");
		$nop_="LHP_".substr($tanggalmulai,0,10)."_".$dte;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $isi);
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