<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeorg= checkPostGet('kebun0','');
$divisi	= checkPostGet('divisi0','');
$periode= checkPostGet('periode0','');

if($proses=='getSubUnit'){
	$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe='AFDELING' order by kodeorganisasi asc ";
	$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$optDivisi.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
	}
	echo $optDivisi;
	exit;
}
$where="";
if(!empty($kodeorg)){
	$where.=" and substr(x.nospb,9,4)='".$kodeorg."'";
}else{
	exit('Warning: Unit tidak boleh kosong...!');
}
if(!empty($divisi)){
	$where.=" and substr(x.nospb,9,6)='".$divisi."'";
}
if(!empty($periode)){
	$where.=" and x.tanggal like '".$periode."%'";
}else{
	exit('Warning: Periode tidak boleh kosong...!');
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
			<td rowspan=4 align=center width='45px'>".$_SESSION['lang']['tanggal']."</td>
			<td rowspan=2 colspan=3 align=center>Total TBS Diterima</td>
			<td rowspan=4 align=center width='45px'>Total Truck</td>
			<td rowspan=4 align=center width='45px'>Netto Grading</td>
			<td rowspan=4 align=center width='45px'>Jumlah Sample Grading</td>
			<td rowspan=1 colspan=4 align=center>Un Ripe</td>
			<td rowspan=1 colspan=4 align=center>Over Ripe</td>
			<td rowspan=1 colspan=4 align=center>Empty Bunch</td>
			<td rowspan=1 colspan=3 align=center>Abnormal</td>
			<td rowspan=1 colspan=4 align=center>Rotten Bunch</td>
			<td rowspan=1 colspan=2 align=center>Ripe</td>
			<td rowspan=1 colspan=4 align=center>Long Stalk</td>
			<td rowspan=1 colspan=4 align=center>Loose Fruit</td>
			<td rowspan=2 colspan=2 align=center>Penalty</td>
			<td rowspan=4 align=center width='45px'>Netto Setelah Grading</td>
			<td rowspan=4 align=center width='45px'>Grading (%)</td>
			<td rowspan=4 align=center width='45px'>Variance (Kg)</td></tr>";
$stream.="<tr class=rowheader>
			<td colspan=4 align=center>(Mentah)</td>
			<td colspan=4 align=center>(Lewat Matang)</td>
			<td colspan=4 align=center>(Tandan Kosong)</td>
			<td colspan=3 align=center>(Batu,Pasir dll)</td>
			<td colspan=4 align=center>(Rusak & Pecah)</td>
			<td colspan=2 align=center>(Matang)</td>
			<td colspan=4 align=center>(Tangkai Panjang)</td>
			<td colspan=4 align=center>(Brondolan)</td></tr>";
$stream.="<tr class=rowheader>
			<td rowspan=2 align=center>Netto (Kg)</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>BJR</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=1 align=center>%</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td rowspan=2 align=center>Jjg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>Kg</td>
			<td rowspan=2 align=center>%</td>
			<td colspan=2 align=center>Penalty</td>
			<td rowspan=2 align=center>%</td>
			<td rowspan=2 align=center>Kg</td></tr>";
$stream.="<tr class=rowheader>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td>
			<td rowspan=1 align=center>Penalty</td>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td>
			<td rowspan=1 align=center>%</td>
			<td rowspan=1 align=center>Kg</td></tr>";
$stream.="</tr></thead><tbody>";
//select tanggal,SUM(beratbersih) as netto,SUM(jumlahtandan1) as jjg,SUM(beratbersih)/SUM(jumlahtandan1) as bjr
//,SUM(if(jjgsortasi>0,1,0)) as truck,SUM(if(jjgsortasi>0,beratbersih,0)) as nettogrding,sum(jjgsortasi) as jjggrading
//from vw_pabrik_timbangan_7ke7 
//$iList="SELECT * FROM ".$dbname.".vw_mill_grading where true ".$where." ORDER BY afdeling,tanggal";
$iList="select x.millcode,left(x.tanggal,10) as tanggal,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','TBSEXT',substr(x.nospb,9,6)) as afdeling
	,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','XTTBS',if(substr(x.nospb,9,6)='CKPE01','TP.G2',if(substr(x.nospb,9,6)='CKPE02','TP.G1'
	,if(substr(x.nospb,9,6)='CKPE03','TP.B1',if(substr(x.nospb,9,6)='CKPE04','TP.B2',substr(x.nospb,9,6)))))) as divisi
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
where x.kodebarang='40000003' 
".$where." 
GROUP BY x.millcode,left(x.tanggal,10)
ORDER BY x.millcode,left(x.tanggal,10)";
//exit('Warning: '.$iList);
$nList=mysql_query($iList) or die (mysql_error($conn));	
$no=0;
$gtberatbersih=0;$gtjanjang=0;
$gttruck=0;
$gtkgsortasi=0;
$gtjjgsortasi=0;
$gtA=0;$gtpA=0;$gtgA=0;$gtkgunripe=0;
$gtB=0;$gtpB=0;$gtgB=0;$gtkgoverripe=0;
$gtC=0;$gtpC=0;$gtgC=0;$gtkgemptybunch=0;
$gtD=0;$gtpD=0;$gtgD=0;$gtkgabnormal=0;
$gtE=0;$gtpE=0;$gtgE=0;$gtkgrottenbunch=0;
$gtripe=0;$gtpripe=0;
$gtF=0;$gtpF=0;$gtgF=0;$gtkglongstalk=0;
$gtG=0;$gtpG=0;$gtgG=0;$gtkgfruitlooses=0;
$gtppenalty=0;$gtkgpenalty=0;
$gtnettograding=0;
$gtpgrading=0;
$gtvariance=0;
while($dList=mysql_fetch_assoc($nList)){
	$no+=1;
	//$tgl=intval(substr($dList['tanggal'],8,2));
	$tgl=date('j', strtotime($dList['tanggal']));
	//exit('Warning: '.$tgl);
	if($no<$tgl){
		$selisih=$tgl-$no;
		//exit('Warning: '.$no.' '.$tgl.' = '.$selisih);
		for($x = 0; $x < $selisih; $x++){
			$tglsisip=$periode.'-'.sprintf("%02d",$x+$no,0,2);
			$no+=1;
			$stream.="<tr class=rowcontent>
						<td align='center'>".$tglsisip."</td>";
			for($x = 1; $x <=40 ; $x++){
				$stream.="<td align='center'></td>";
			}
			$stream.="</tr>";
		}
	}
	$bjr=($dList['janjang']>0 ? $dList['beratbersih']/$dList['janjang'] : 0);
	$ripe=$dList['jjgsortasi']-($dList['A']+$dList['B']+$dList['C']+$dList['D']+$dList['E']);
	$pripe=($dList['jjgsortasi']>0 ? ($dList['jjgsortasi']-($dList['A']+$dList['B']+$dList['C']+$dList['D']+$dList['E']))/$dList['jjgsortasi'] : 0);
	$kgpenalty=$dList['kgunripe']+$dList['kgoverripe']+$dList['kgemptybunch']+$dList['kgrottenbunch']+$dList['kglongstalk']+$dList['kgfruitlooses'];
	$ppenalty=($dList['beratbersih']>0 ? $kgpenalty/$dList['beratbersih']*100 : 0);
	$pgrading=($dList['janjang']>0 ? $dList['jjgsortasi']/$dList['janjang']*100 : 0);
	$stream.="<tr class=rowcontent>
				<td align='center'>".$dList['tanggal']."</td>
				<td align='right'>".number_format($dList['beratbersih'],0)."</td>
				<td align='right'>".number_format($dList['janjang'],0)."</td>
				<td align='right'>".number_format($bjr,2)."</td>
				<td align='right'>".number_format($dList['truck'],0)."</td>
				<td align='right'>".number_format($dList['kgsortasi'],0)."</td>
				<td align='right'>".number_format($dList['jjgsortasi'],0)."</td>
				<td align='right'>".number_format($dList['A'],0)."</td>
				<td align='right'>".number_format($dList['pA'],2)."</td>
				<td align='right'>".number_format($dList['gA'],2)."</td>
				<td align='right'>".number_format($dList['kgunripe'],0)."</td>
				<td align='right'>".number_format($dList['B'],0)."</td>
				<td align='right'>".number_format($dList['pB'],2)."</td>
				<td align='right'>".number_format($dList['gB'],2)."</td>
				<td align='right'>".number_format($dList['kgoverripe'],0)."</td>
				<td align='right'>".number_format($dList['C'],0)."</td>
				<td align='right'>".number_format($dList['pC'],2)."</td>
				<td align='right'>".number_format($dList['gC'],2)."</td>
				<td align='right'>".number_format($dList['kgemptybunch'],0)."</td>
				<td align='right'>".number_format($dList['D'],0)."</td>
				<td align='right'>".number_format($dList['pD'],2)."</td>
				<td align='right'>".number_format(0,0)."</td>
				<td align='right'>".number_format($dList['E'],0)."</td>
				<td align='right'>".number_format($dList['pE'],2)."</td>
				<td align='right'>".number_format($dList['gE'],2)."</td>
				<td align='right'>".number_format($dList['kgrottenbunch'],0)."</td>
				<td align='right'>".number_format($ripe,0)."</td>
				<td align='right'>".number_format($pripe,2)."</td>
				<td align='right'>".number_format($dList['F'],0)."</td>
				<td align='right'>".number_format($dList['pF'],2)."</td>
				<td align='right'>".number_format($dList['gF'],2)."</td>
				<td align='right'>".number_format($dList['kglongstalk'],0)."</td>
				<td align='right'>".number_format($dList['kgsortasi']*$dList['pG']/100,0)."</td>
				<td align='right'>".number_format($dList['pG'],2)."</td>
				<td align='right'>".number_format($dList['gG'],2)."</td>
				<td align='right'>".number_format($dList['kgfruitlooses'],0)."</td>
				<td align='right'>".number_format($ppenalty,2)."</td>
				<td align='right'>".number_format($kgpenalty,0)."</td>
				<td align='right'>".number_format($dList['beratbersih']-$kgpenalty,0)."</td>
				<td align='right'>".number_format($pgrading,2)."</td>
				<td align='right'>".number_format(0,0)."</td>
			</tr>";
			$gtberatbersih+=$dList['beratbersih'];
			$gtjanjang+=$dList['janjang'];
			$gttruck+=$dList['truck'];
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
			$gtvariance+=0;
}
$tgl=date('t', strtotime($periode.'-01'));
if($no<$tgl){
	$selisih=$tgl-$no;
	//exit('Warning: '.$no.' '.$tgl.' = '.$selisih);
	for($x = 0; $x < $selisih; $x++){
		$no+=1;
		$tglsisip=$periode.'-'.sprintf("%02d",$no,0,2);
		$stream.="<tr class=rowcontent>
					<td align='center'>".$tglsisip."</td>";
		for($z = 1; $z <=40 ; $z++){
			$stream.="<td align='center'></td>";
		}
		$stream.="</tr>";
	}
}
$gtbjr=($gtjanjang>0 ? $gtberatbersih/$gtjanjang : 0);
$gtpripe=($gtjjgsortasi>0 ? $gtripe/$gtjjgsortasi*100 : 0);
$gtppenalty=($gtberatbersih>0 ? $gtkgpenalty/$gtberatbersih*100 : 0);
$gtpgrading=($gtjanjang>0 ? $gtjjgsortasi/$gtjanjang*100 : 0);
$stream.="<tr bgcolor='#FEDEFE'>
			<td align='center'>Total</td>
			<td align='right'>".number_format($gtberatbersih,0)."</td>
			<td align='right'>".number_format($gtjanjang,0)."</td>
			<td align='right'>".number_format($gtbjr,2)."</td>
			<td align='right'>".number_format($gttruck,0)."</td>
			<td align='right'>".number_format($gtkgsortasi,0)."</td>
			<td align='right'>".number_format($gtjjgsortasi,0)."</td>
			<td align='right'>".number_format($gtA,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtA/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtA/$gtjjgsortasi*100 : 0)*0.5,2)."</td>
			<td align='right'>".number_format($gtkgunripe,0)."</td>
			<td align='right'>".number_format($gtB,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtB/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format((($gtjjgsortasi>0 ? $gtB/$gtjjgsortasi*100 : 0)<5 ? 0 : (($gtjjgsortasi>0 ? $gtB/$gtjjgsortasi*100 : 0)-5)*0.25),2)."</td>
			<td align='right'>".number_format($gtkgoverripe,0)."</td>
			<td align='right'>".number_format($gtC,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtC/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtC/$gtjjgsortasi*100 : 0)*1,2)."</td>
			<td align='right'>".number_format($gtkgemptybunch,0)."</td>
			<td align='right'>".number_format($gtD,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtD/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format(0,0)."</td>
			<td align='right'>".number_format($gtE,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtE/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtE/$gtjjgsortasi*100 : 0)*1,2)."</td>
			<td align='right'>".number_format($gtkgrottenbunch,0)."</td>
			<td align='right'>".number_format($gtripe,0)."</td>
			<td align='right'>".number_format($gtpripe,2)."</td>
			<td align='right'>".number_format($gtF,0)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtF/$gtjjgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format(($gtjjgsortasi>0 ? $gtF/$gtjjgsortasi*100 : 0)*0.01,2)."</td>
			<td align='right'>".number_format($gtkglongstalk,0)."</td>
			<td align='right'>".number_format($gtG,0)."</td>
			<td align='right'>".number_format(($gtkgsortasi>0 ? $gtG/$gtkgsortasi*100 : 0),2)."</td>
			<td align='right'>".number_format((($gtkgsortasi>0 ? $gtG/$gtkgsortasi*100 : 0)>=12.5 or ($gtkgsortasi>0 ? $gtG/$gtkgsortasi*100 : 0)<=0 ? 0 : (12.5-($gtkgsortasi>0 ? $gtG/$gtkgsortasi*100 : 0))*0.3),2)."</td>
			<td align='right'>".number_format($gtkgfruitlooses,0)."</td>
			<td align='right'>".number_format($gtppenalty,2)."</td>
			<td align='right'>".number_format($gtkgpenalty,0)."</td>
			<td align='right'>".number_format($gtberatbersih-$gtkgpenalty,0)."</td>
			<td align='right'>".number_format($gtpgrading,2)."</td>
			<td align='right'>".number_format($gtvariance,0)."</td>
		</tr>";
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
