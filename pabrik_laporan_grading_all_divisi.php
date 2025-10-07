<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeorg= checkPostGet('pabrik1','');
$tgl1 = tanggalsystemn(checkPostGet('tgl11',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl21',''));

$where="";
if(!empty($kodeorg)){
	$where.=" and x.millcode='".$kodeorg."'";
}else{
	exit('Warning: Unit tidak boleh kosong...!');
}
if($tgl1=='--'){
    $tgl1='';
}
if($tgl2=='--'){
    $tgl2='';
}
if($tgl1=='' and  $tgl2!=''){
	$tgl1=$tgl2;
}
if($tgl1!='' and  $tgl2==''){
	$tgl2=$tgl1;
}
if($tgl1=='' and  $tgl2==''){
	exit('Warning: Tanggal tidak boleh kosong...!');
}else{
	$where.=" and (left(x.tanggal,10)>='".$tgl1."' and left(x.tanggal,10)<='".$tgl2."')";
}
if($proses=='excel'){
    $border="border=1";
}else{
    $border="border=0";
}
//bgcolor=#CCCCCC border='1'
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$stream="<table cellspacing='1' $border class='sortable'>";
$stream.="<thead><tr class=rowheader>
			<td rowspan=3 align=center width='10px'>No</td>
			<td rowspan=3 align=center>".$_SESSION['lang']['keterangan']."</td>
			<td colspan=4 align=center>Penerimaan TBS</td>
			<td colspan=9 align=center>Hasil Grading</td>
			<td colspan=3 align=center>Penalty TBS</td></tr>";
$stream.="<tr class=rowheader>
			<td align=center>TBS Diterima</td>
			<td align=center>TBS Diterima</td>
			<td align=center>TBS Diamati</td>
			<td align=center>Prosentase</td>
			<td align=center>Un Ripe</td>
			<td align=center>Over Ripe</td>
			<td align=center>Empty Bunch</td>
			<td align=center>Abnormal</td>
			<td align=center>Rotten Bunch</td>
			<td align=center>Ripe</td>
			<td align=center>Total</td>
			<td align=center>Long Stalk</td>
			<td align=center>Loose Fruit</td>
			<td align=center>Prosentase Penalty</td>
			<td align=center>Potongan Grading</td>
			<td align=center>Netto Setelah Grading</td></tr>";
$stream.="<tr class=rowheader>
			<td align=center>(Kg)</td>
			<td align=center>(Jjg)</td>
			<td align=center>(Jjg)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(%)</td>
			<td align=center>(Kg)</td>
			<td align=center>(Kg)</td></tr>";
$stream.="</thead><tbody>";
//select tanggal,SUM(beratbersih) as netto,SUM(jumlahtandan1) as jjg,SUM(beratbersih)/SUM(jumlahtandan1) as bjr
//,SUM(if(jjgsortasi>0,1,0)) as truck,SUM(if(jjgsortasi>0,beratbersih,0)) as nettogrding,sum(jjgsortasi) as jjggrading
//from vw_pabrik_timbangan_7ke7 
//$iList="SELECT * FROM ".$dbname.".vw_mill_grading where true ".$where." ORDER BY afdeling,tanggal";
$iList="select x.millcode,left(x.tanggal,10) as tanggal,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','XT_TBS',substr(x.nospb,9,6)) as afdeling
			,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','XTTBS',if(substr(x.nospb,9,6)='CKPE01','TP.G2',if(substr(x.nospb,9,6)='CKPE02','TP.G1'
			,if(substr(x.nospb,9,6)='CKPE03','TP.B1',if(substr(x.nospb,9,6)='CKPE04','TP.B2',substr(x.nospb,9,6)))))) as divisi
			,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0',v.namasupplier,if(substr(x.nospb,9,6)='CKPE01','TP.G2',if(substr(x.nospb,9,6)='CKPE02','TP.G1'
			,if(substr(x.nospb,9,6)='CKPE03','TP.B1',if(substr(x.nospb,9,6)='CKPE04','TP.B2',substr(x.nospb,9,6)))))) as namadivisi
			,sum(x.beratbersih) as beratbersih,sum(x.jumlahtandan1) as janjang,sum(if(x.jjgsortasi>0,1,0)) as truck
			,sum(if(x.kgpotsortasi>0,x.beratbersih,0)) as kgsortasi,sum(x.kgpotsortasi) as kgpotsortasi
			,sum(x.kgpotsortasi)/sum(if(x.kgpotsortasi>0,x.beratbersih,0))*100 as penaltygrading,sum(x.jjgsortasi) as jjgsortasi
			,sum(w.A) as A,sum(w.B) as B,sum(w.C) as C,sum(w.D) as D,sum(w.E) as E,sum(w.F) as F,sum(w.G) as G
 			,sum(w.A)/sum(x.jjgsortasi)*100 as pA
 			,sum(w.B)/sum(x.jjgsortasi)*100 as pB
			,sum(w.C)/sum(x.jjgsortasi)*100 as pC
			,sum(w.D)/sum(x.jjgsortasi)*100 as pD
			,sum(w.E)/sum(x.jjgsortasi)*100 as pE
			,sum(w.F)/sum(x.jjgsortasi)*100 as pF
			,sum(w.G)/sum(x.jjgsortasi)*100 as pG
			,sum(w.A)/sum(x.jjgsortasi)*100*(sum(w.pA)/sum(x.jjgsortasi)*100) as gA
 			,if((sum(w.B)/sum(x.jjgsortasi)*100)-5<=0,0,((sum(w.B)/sum(x.jjgsortasi)*100)-5)*(sum(w.pB)/sum(x.jjgsortasi)*100)) as gB
			,sum(w.C)/sum(x.jjgsortasi)*100*(sum(w.pC)/sum(x.jjgsortasi)*100) as gC
			,sum(w.D)/sum(x.jjgsortasi)*100 as gD
			,sum(w.E)/sum(x.jjgsortasi)*100*(sum(w.pE)/sum(x.jjgsortasi)*100) as gE
			,sum(w.F)/sum(x.jjgsortasi)*100*(sum(w.pF)/sum(x.jjgsortasi)*100) as gF
			,(12.5-(sum(w.G)/sum(x.jjgsortasi)*100))*(sum(w.pG)/sum(x.jjgsortasi)*100) as gG
			,(sum(w.A)/sum(x.jjgsortasi)*100*(sum(w.pA)/sum(x.jjgsortasi)*100))
			+(if((sum(w.B)/sum(x.jjgsortasi)*100)-5<=0,0,((sum(w.B)/sum(x.jjgsortasi)*100)-5)*(sum(w.pB)/sum(x.jjgsortasi)*100)))
			+(sum(w.C)/sum(x.jjgsortasi)*100*(sum(w.pC)/sum(x.jjgsortasi)*100))
			+(sum(w.E)/sum(x.jjgsortasi)*100*(sum(w.pE)/sum(x.jjgsortasi)*100))
			+(sum(w.F)/sum(x.jjgsortasi)*100*(sum(w.pF)/sum(x.jjgsortasi)*100))
			+((12.5-(sum(w.G)/sum(x.jjgsortasi)*100))*(sum(w.pG)/sum(x.jjgsortasi)*100)) as pengrading
 			,sum(w.A)/sum(x.jjgsortasi)*100*(sum(w.pA)/sum(x.jjgsortasi)*100)*sum(x.beratbersih)/100 as kgunripe
 			,if((sum(w.B)/sum(x.jjgsortasi)*100)-5<=0,0,((sum(w.B)/sum(x.jjgsortasi)*100)-5)*(sum(w.pB)/sum(x.jjgsortasi)*100))*sum(x.beratbersih)/100 as kgoverripe
			,sum(w.C)/sum(x.jjgsortasi)*100*(sum(w.pC)/sum(x.jjgsortasi)*100)*sum(x.beratbersih)/100 as kgemptybunch
			,sum(w.D)/sum(x.jjgsortasi)*100*sum(x.beratbersih)/100 as kgabnormal
			,sum(w.E)/sum(x.jjgsortasi)*100*(sum(w.pE)/sum(x.jjgsortasi)*100)*sum(x.beratbersih)/100 as kgrottenbunch
			,sum(w.F)/sum(x.jjgsortasi)*100*(sum(w.pF)/sum(x.jjgsortasi)*100)*sum(x.beratbersih)/100 as kglongstalk
			,(12.5-(sum(w.G)/sum(x.jjgsortasi)*100))*(sum(w.pG)/sum(x.jjgsortasi)*100)*sum(x.beratbersih)/100 as kgfruitlooses
			,((sum(w.A)/sum(x.jjgsortasi)*100*(sum(w.pA)/sum(x.jjgsortasi)*100))
			+(if((sum(w.B)/sum(x.jjgsortasi)*100)-5<=0,0,((sum(w.B)/sum(x.jjgsortasi)*100)-5)*(sum(w.pB)/sum(x.jjgsortasi)*100)))
			+(sum(w.C)/sum(x.jjgsortasi)*100*(sum(w.pC)/sum(x.jjgsortasi)*100))
			+(sum(w.E)/sum(x.jjgsortasi)*100*(sum(w.pE)/sum(x.jjgsortasi)*100))
			+(sum(w.F)/sum(x.jjgsortasi)*100*(sum(w.pF)/sum(x.jjgsortasi)*100))
			+((12.5-(sum(w.G)/sum(x.jjgsortasi)*100))*(sum(w.pG)/sum(x.jjgsortasi)*100)))*sum(x.beratbersih)/100 as kggrading
		from ".$dbname.".vw_pabrik_timbangan_7ke7 x
		LEFT JOIN ".$dbname.".vw_mill_sortasi_persen w on w.notiket=x.notransaksi
		LEFT JOIN ".$dbname.".log_5supplier v on v.kodetimbangan=x.kodecustomer
		LEFT JOIN ".$dbname.".organisasi u on u.kodeorganisasi=substr(x.nospb,9,6)
		where x.kodebarang='40000003' ".$where." 
		GROUP BY x.millcode,afdeling,namasupplier
		ORDER BY x.millcode,afdeling,namasupplier";
//exit('Warning: '.$iList);
$nList=mysql_query($iList) or die (mysql_error($conn));	
$no=0;
$stberatbersih=0;$stjanjang=0;
$stkgsortasi=0;
$stjjgsortasi=0;
$stA=0;$stkgunripe=0;
$stB=0;$stkgoverripe=0;
$stC=0;$stkgemptybunch=0;
$stD=0;$stkgabnormal=0;
$stE=0;$stkgrottenbunch=0;
$stripe=0;$stpripe=0;$sttotal=0;
$stF=0;$stkglongstalk=0;
$stG=0;$stkgfruitlooses=0;
$stppenalty=0;$stkgpenalty=0;
$stnettograding=0;

$ttberatbersih=0;$ttjanjang=0;
$ttkgsortasi=0;
$ttjjgsortasi=0;
$ttA=0;$ttkgunripe=0;
$ttB=0;$ttkgoverripe=0;
$ttC=0;$ttkgemptybunch=0;
$ttD=0;$ttkgabnormal=0;
$ttE=0;$ttkgrottenbunch=0;
$ttripe=0;$ttpripe=0;$tttotal=0;
$ttF=0;$ttkglongstalk=0;
$ttG=0;$ttkgfruitlooses=0;
$ttppenalty=0;$ttkgpenalty=0;
$ttnettograding=0;

$gtberatbersih=0;$gtjanjang=0;
$gtkgsortasi=0;
$gtjjgsortasi=0;
$gtA=0;$gtkgunripe=0;
$gtB=0;$gtkgoverripe=0;
$gtC=0;$gtkgemptybunch=0;
$gtD=0;$gtkgabnormal=0;
$gtE=0;$gtkgrottenbunch=0;
$gtripe=0;$gtpripe=0;$gttotal=0;
$gtF=0;$gtkglongstalk=0;
$gtG=0;$gtkgfruitlooses=0;
$gtppenalty=0;$gtkgpenalty=0;
$gtnettograding=0;
$namaunit='namaunit';
while($dList=mysql_fetch_assoc($nList)){
	$no+=1;
	//if($no==2){
	//	exit('Warning: '.$namaunit.' = '.substr($dList['afdeling'],0,4));
	//}
	if($no!=1 and $namaunit!=substr($dList['afdeling'],0,4)){
		$stpjjg=($stjanjang>0 ? $stjjgsortasi/$stjanjang*100 : 0);
		$stream.="<tr bgcolor='#FEDDFE'>
				<td colspan=2 align='center'>Sub Total ".($namaunit=='XT_T' ? 'TBS LUAR' : $namaunit)."</td>
				<td align='right' width='80px'>".number_format($stberatbersih,0)."</td>
				<td align='right' width='60px'>".number_format($stjanjang,0)."</td>
				<td align='right' width='60px'>".number_format($stjjgsortasi,0)."</td>
				<td align='right' width='60px'>".number_format($stpjjg,2)."</td>
				<td align='right' width='60px'>".number_format($stpunripe,2)."</td>
				<td align='right' width='60px'>".number_format($stpoverripe,2)."</td>
				<td align='right' width='60px'>".number_format($stpemptybunch,2)."</td>
				<td align='right' width='60px'>".number_format($stpabnormal,2)."</td>
				<td align='right' width='60px'>".number_format($stprottenbunch,2)."</td>
				<td align='right' width='60px'>".number_format($stpripe,2)."</td>
				<td align='right' width='60px'>".number_format($stptotal,2)."</td>
				<td align='right' width='60px'>".number_format($stplongstalk,2)."</td>
				<td align='right' width='60px'>".number_format($stpfruitoses,2)."</td>
				<td align='right' width='60px'>".number_format($stppenalty,2)."</td>
				<td align='right' width='60px'>".number_format($stkgpenalty,0)."</td>
				<td align='right' width='80px'>".number_format($stnettograding,0)."</td>
			</tr>";
		$stberatbersih=0;$stjanjang=0;
		$stkgsortasi=0;
		$stjjgsortasi=0;
		$stA=0;$stkgunripe=0;
		$stB=0;$stkgoverripe=0;
		$stC=0;$stkgemptybunch=0;
		$stD=0;$stkgabnormal=0;
		$stE=0;$stkgrottenbunch=0;
		$stripe=0;$stpripe=0;$sttotal=0;
		$stF=0;$stkglongstalk=0;
		$stG=0;$stkgfruitlooses=0;
		$stppenalty=0;$stkgpenalty=0;
		$stnettograding=0;
	}
	$pjjg=($dList['janjang']>0 ? $dList['jjgsortasi']/$dList['janjang']*100 : 0);
	$punripe=($dList['jjgsortasi']>0 ? $dList['A']/$dList['jjgsortasi']*100 : 0);
	$poverripe=($dList['jjgsortasi']>0 ? $dList['B']/$dList['jjgsortasi']*100 : 0);
	$pemptybunch=($dList['jjgsortasi']>0 ? $dList['C']/$dList['jjgsortasi']*100 : 0);
	$pabnormal=($dList['jjgsortasi']>0 ? $dList['D']/$dList['jjgsortasi']*100 : 0);
	$prottenbunch=($dList['jjgsortasi']>0 ? $dList['E']/$dList['jjgsortasi']*100 : 0);
	$ripe=$dList['jjgsortasi']-($dList['A']+$dList['B']+$dList['C']+$dList['D']+$dList['E']);
	$pripe=($dList['jjgsortasi']>0 ? $ripe/$dList['jjgsortasi']*100 : 0);
	$ptotal=$punripe+$poverripe+$pemptybunch+$pabnormal+$prottenbunch+$pripe;
	$plongstalk=($dList['jjgsortasi']>0 ? $dList['F']/$dList['jjgsortasi']*100 : 0);
	$pfruitoses=($dList['kgsortasi']>0 ? $dList['kgfruitlooses']/$dList['kgsortasi']*100 : 0);
	$kgpenalty=$dList['kgunripe']+$dList['kgoverripe']+$dList['kgemptybunch']+$dList['kgrottenbunch']+$dList['kglongstalk']+$dList['kgfruitlooses'];
	$ppenalty=($dList['beratbersih']>0 ? $kgpenalty/$dList['beratbersih']*100 : 0);
	$nettograding=$dList['beratbersih']-$kgpenalty;
	$stream.="<tr class=rowcontent>
				<td align='center'>".substr($dList['afdeling'],0,4)."</td>
				<td align='center'>".$dList['namadivisi']."</td>
				<td align='right' width='80px'>".number_format($dList['beratbersih'],0)."</td>
				<td align='right' width='60px'>".number_format($dList['janjang'],0)."</td>
				<td align='right' width='60px'>".number_format($dList['jjgsortasi'],0)."</td>
				<td align='right' width='60px'>".number_format($pjjg,2)."</td>
				<td align='right' width='60px'>".number_format($punripe,2)."</td>
				<td align='right' width='60px'>".number_format($poverripe,2)."</td>
				<td align='right' width='60px'>".number_format($pemptybunch,2)."</td>
				<td align='right' width='60px'>".number_format($pabnormal,2)."</td>
				<td align='right' width='60px'>".number_format($prottenbunch,2)."</td>
				<td align='right' width='60px'>".number_format($pripe,2)."</td>
				<td align='right' width='60px'>".number_format($ptotal,2)."</td>
				<td align='right' width='60px'>".number_format($plongstalk,2)."</td>
				<td align='right' width='60px'>".number_format($pfruitoses,2)."</td>
				<td align='right' width='60px'>".number_format($ppenalty,2)."</td>
				<td align='right' width='60px'>".number_format($kgpenalty,0)."</td>
				<td align='right' width='80px'>".number_format($nettograding,0)."</td>
			</tr>";
	$stberatbersih+=$dList['beratbersih'];
	$stjanjang+=$dList['janjang'];
	$stkgsortasi+=$dList['kgsortasi'];
	$stjjgsortasi+=$dList['jjgsortasi'];
	$stA+=$dList['A'];
	$stkgunripe+=$dList['kgunripe'];
	$stB+=$dList['B'];
	$stkgoverripe+=$dList['kgoverripe'];
	$stC+=$dList['C'];
	$stkgemptybunch+=$dList['kgemptybunch'];
	$stD+=$dList['D'];
	$stkgabnormal+=$dList['kgabnormal'];
	$stE+=$dList['E'];
	$stkgrottenbunch+=$dList['kgrottenbunch'];
	$stripe+=$ripe;
	$stF+=$dList['F'];
	$stkglongstalk+=$dList['kglongstalk'];
	$stG+=$dList['kgsortasi']*$dList['pG']/100;
	$stkgfruitlooses+=$dList['kgfruitlooses'];
	$stkgpenalty+=$kgpenalty;
	$stnettograding+=$dList['beratbersih']-$kgpenalty;

	$ttberatbersih+=$dList['beratbersih'];
	$ttjanjang+=$dList['janjang'];
	$ttkgsortasi+=$dList['kgsortasi'];
	$ttjjgsortasi+=$dList['jjgsortasi'];
	$ttA+=$dList['A'];
	$ttkgunripe+=$dList['kgunripe'];
	$ttB+=$dList['B'];
	$ttkgoverripe+=$dList['kgoverripe'];
	$ttC+=$dList['C'];
	$ttkgemptybunch+=$dList['kgemptybunch'];
	$ttD+=$dList['D'];
	$ttkgabnormal+=$dList['kgabnormal'];
	$ttE+=$dList['E'];
	$ttkgrottenbunch+=$dList['kgrottenbunch'];
	$ttripe+=$ripe;
	$ttF+=$dList['F'];
	$ttkglongstalk+=$dList['kglongstalk'];
	$ttG+=$dList['kgsortasi']*$dList['pG']/100;
	$ttkgfruitlooses+=$dList['kgfruitlooses'];
	$ttkgpenalty+=$kgpenalty;
	$ttnettograding+=$dList['beratbersih']-$kgpenalty;

	$gtberatbersih+=$dList['beratbersih'];
	$gtjanjang+=$dList['janjang'];
	$gtkgsortasi+=$dList['kgsortasi'];
	$gtjjgsortasi+=$dList['jjgsortasi'];
	$gtA+=$dList['A'];
	$gtkgunripe+=$dList['kgunripe'];
	$gtB+=$dList['B'];
	$gtkgoverripe+=$dList['kgoverripe'];
	$gtC+=$dList['C'];
	$gtkgemptybunch+=$dList['kgemptybunch'];
	$gtD+=$dList['D'];
	$gtkgabnormal+=$dList['kgabnormal'];
	$gtE+=$dList['E'];
	$gtkgrottenbunch+=$dList['kgrottenbunch'];
	$gtripe+=$ripe;
	$gtF+=$dList['F'];
	$gtkglongstalk+=$dList['kglongstalk'];
	$gtG+=$dList['kgsortasi']*$dList['pG']/100;
	$gtkgfruitlooses+=$dList['kgfruitlooses'];
	$gtkgpenalty+=$kgpenalty;
	$gtnettograding+=$dList['beratbersih']-$kgpenalty;
	$namaunit=substr($dList['afdeling'],0,4);
}
if($stberatbersih>0){
	$stream.="<tr bgcolor='#FEDDFE'>
				<td colspan=2 align='center'>Sub Total ".($namaunit=='XT_T' ? 'TBS LUAR' : $namaunit)."</td>
				<td align='right' width='80px'>".number_format($stberatbersih,0)."</td>
				<td align='right' width='60px'>".number_format($stjanjang,0)."</td>
				<td align='right' width='60px'>".number_format($stjjgsortasi,0)."</td>
				<td align='right' width='60px'>".number_format($stpjjg,2)."</td>
				<td align='right' width='60px'>".number_format($stpunripe,2)."</td>
				<td align='right' width='60px'>".number_format($stpoverripe,2)."</td>
				<td align='right' width='60px'>".number_format($stpemptybunch,2)."</td>
				<td align='right' width='60px'>".number_format($stpabnormal,2)."</td>
				<td align='right' width='60px'>".number_format($stprottenbunch,2)."</td>
				<td align='right' width='60px'>".number_format($stpripe,2)."</td>
				<td align='right' width='60px'>".number_format($stptotal,2)."</td>
				<td align='right' width='60px'>".number_format($stplongstalk,2)."</td>
				<td align='right' width='60px'>".number_format($stpfruitoses,2)."</td>
				<td align='right' width='60px'>".number_format($stppenalty,2)."</td>
				<td align='right' width='60px'>".number_format($stkgpenalty,0)."</td>
				<td align='right' width='80px'>".number_format($stnettograding,0)."</td>
			</tr>";
}
$gtpjjg=($gtjanjang>0 ? $gtjjgsortasi/$gtjanjang*100 : 0);
$gtpunripe=($gtjjgsortasi>0 ? $gtA/$gtjjgsortasi*100 : 0);
$gtpoverripe=($gtjjgsortasi>0 ? $gtB/$gtjjgsortasi*100 : 0);
$gtpemptybunch=($gtjjgsortasi>0 ? $gtC/$gtjjgsortasi*100 : 0);
$gtpabnormal=($gtjjgsortasi>0 ? $gtD/$gtjjgsortasi*100 : 0);
$gtprottenbunch=($gtjjgsortasi>0 ? $gtE/$gtjjgsortasi*100 : 0);
$gtripe=$gtjjgsortasi-($gtA+$gtB+$gtC+$gtD+$gtE);
$gtpripe=($gtjjgsortasi>0 ? $gtripe/$gtjjgsortasi*100 : 0);
$gtptotal=$gtpunripe+$gtpoverripe+$gtpemptybunch+$gtpabnormal+$gtprottenbunch+$gtpripe;
$gtplongstalk=($gtjjgsortasi>0 ? $gtF/$gtjjgsortasi*100 : 0);
$gtpfruitoses=($gtkgsortasi>0 ? $gtkgfruitlooses/$gtkgsortasi*100 : 0);
$gtppenalty=($gtberatbersih>0 ? $gtkgpenalty/$gtberatbersih*100 : 0);
$gtnettograding=$gtberatbersih-$gtkgpenalty;
if($gtberatbersih>0){
	$stream.="<tr bgcolor='#FEDEFE'>
			<td colspan=2 align='center'>Grant Total</td>
			<td align='right'>".number_format($gtberatbersih,0)."</td>
			<td align='right'>".number_format($gtjanjang,0)."</td>
			<td align='right'>".number_format($gtjjgsortasi,0)."</td>
			<td align='right'>".number_format($gtpjjg,2)."</td>
			<td align='right'>".number_format($gtpunripe,2)."</td>
			<td align='right'>".number_format($gtpoverripe,2)."</td>
			<td align='right'>".number_format($gtpemptybunch,2)."</td>
			<td align='right'>".number_format($gtpabnormal,2)."</td>
			<td align='right'>".number_format($gtprottenbunch,2)."</td>
			<td align='right'>".number_format($gtpripe,2)."</td>
			<td align='right'>".number_format($gtptotal,2)."</td>
			<td align='right'>".number_format($gtplongstalk,2)."</td>
			<td align='right'>".number_format($gtpfruitoses,2)."</td>
			<td align='right'>".number_format($gtppenalty,2)."</td>
			<td align='right'>".number_format($gtkgpenalty,0)."</td>
			<td align='right'>".number_format($gtnettograding,0)."</td>
		</tr>";
}
$stream.="</tbody></table>";

switch($proses){
	case 'preview':
		echo $stream;
    break;

	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
		$tglSkrg=date("Ymd");
		$judul="<h3>ANALISA MUTU TBS";
		$judul.="<BR>".($divisi=='' ? $nmOrg[$kodeorg] : $nmOrg[$divisi])."";
		$judul.="<BR>Periode : ".$periode."</h3>";
		$nop_="ANALISA_MUTU_TBS_".$divisi.'_'.$periode.'_'.$tglSkrg;
		if(strlen($stream)>0){
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
