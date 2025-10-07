<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$millcode= checkPostGet('pabrik0','');
$kodeorg= checkPostGet('kebun0','');
$divisi	= checkPostGet('divisi0','');
$periode= checkPostGet('periode0','');

if($proses=='getSubUnit'){
	$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($kodeorg=='CKS'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk in ('DUKE','TEBE') and tipe='AFDELING' order by kodeorganisasi";
	}else{
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe='AFDELING' order by kodeorganisasi";
	}
	$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
	while($rOrg=mysql_fetch_assoc($qOrg)){
		$optDivisi.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
	}
	echo $optDivisi;
	exit;
}
$kolomselisih=1;
$where="";
if(!empty($millcode)){
	$where.=" and x.millcode='".$millcode."'";
}else{
	exit('Warning: Pabrik tidak boleh kosong...!');
}
if(!empty($kodeorg)){
	if($kodeorg=='CKS'){
		$where.=" and substr(x.nospb,9,4) in ('DUKE','TEBE')";
	}else{
		$where.=" and substr(x.nospb,9,4)='".$kodeorg."'";
	}
	if(substr($kodeorg,2,2)=='PE'){
		$kolomselisih=0;
	}
}
if(!empty($divisi)){
	$where.=" and substr(x.nospb,9,6)='".$divisi."'";
	if(substr($divisi,2,2)=='PE'){
		$kolomselisih=0;
	}
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
			<td rowspan=4 align=center width='45px'>".$_SESSION['lang']['noTiket']."</td>
			<td rowspan=4 align=center width='30px'>".$_SESSION['lang']['divisi']."</td>
			<td rowspan=4 align=center width='45px'>".$_SESSION['lang']['tanggal']."</td>
			<td rowspan=2 colspan=3 align=center>Total TBS Diterima</td>
			<td rowspan=4 align=center width='40px'>Jumlah Sample Grading</td>";
if($kolomselisih==1){
	$stream.="<td rowspan=4 align=center width='40px'>Selisih Janjang</td>";
}
$stream.="	<td rowspan=1 colspan=4 align=center>Un Ripe</td>
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
			</tr>";
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
$iList="SELECT x.notransaksi,x.millcode,left(x.tanggal,10) as tanggal
		,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','TBSEXT',substr(x.nospb,9,6)) as afdeling
		,if(substr(x.nospb,9,6)='' or substr(x.nospb,9,6)='0','XTTBS',if(substr(x.nospb,9,6)='CKPE01','G2',if(substr(x.nospb,9,6)='CKPE02','G1'
		,if(substr(x.nospb,9,6)='CKPE03','B1',if(substr(x.nospb,9,6)='CKPE04','B2',substr(x.nospb,9,6)))))) as divisi 
		,x.beratbersih,x.jumlahtandan1 as janjang,x.jjgsortasli as jjgsortasi
		,w.A,(w.A/x.jjgsortasli)*100 as pA,(w.A/x.jjgsortasli)*100*w.pA as gA,(w.A/x.jjgsortasli)*100*w.pA*x.beratbersih/100 as kgunripe
		,w.B,(w.B/x.jjgsortasli)*100 as pB,(w.B/x.jjgsortasli)*100*w.pB as gB,(w.B/x.jjgsortasli)*100*w.pB*x.beratbersih/100 as kgoverripe
		,w.C,(w.C/x.jjgsortasli)*100 as pC,(w.C/x.jjgsortasli)*100*w.pC as gC,(w.C/x.jjgsortasli)*100*w.pC*x.beratbersih/100 as kgemptybunch
		,w.D,(w.D/x.jjgsortasli)*100 as pD,(w.D/x.jjgsortasli)*100*w.pD as gD,(w.D/x.jjgsortasli)*100*w.pD*x.beratbersih/100 as kgabnormal
		,w.E,(w.E/x.jjgsortasli)*100 as pE,(w.E/x.jjgsortasli)*100*w.pE as gE,(w.E/x.jjgsortasli)*100*w.pE*x.beratbersih/100 as kgrottenbunch
		,w.F,(w.F/x.jjgsortasli)*100 as pF,(w.F/x.jjgsortasli)*100*w.pF as gF,(w.F/x.jjgsortasli)*100*w.pF*x.beratbersih/100 as kglongstalk
		,w.G*x.beratbersih/100 as G ,w.G as pG,(12.5-w.G)*w.pg as gG,(12.5-w.G)*w.pg*x.beratbersih/100 as kgfruitlooses
		from ".$dbname.".vw_pabrik_timbangan_7ke7 x
		LEFT JOIN ".$dbname.".vw_mill_sortasli_persen w on w.notiket=x.notransaksi
		where x.kodebarang='40000003' and (w.A+w.B+w.C+w.D+w.E+w.F+w.G)>0
		".$where." 
		ORDER BY x.millcode,left(x.tanggal,10),afdeling";
//exit('Warning: '.$iList);
$nList=mysql_query($iList) or die (mysql_error($conn));	
$no=0;
$gtberatbersih=0;$gtjanjang=0;
$gttruck=0;
$gtkgsortasi=0;
$gtjjgsortasi=0;
$gtjjgselisih=0;
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
	/*
	if($no<$tgl){
		$selisih=$tgl-$no;
		//exit('Warning: '.$no.' '.$tgl.' = '.$selisih);
		for($x = 0; $x < $selisih; $x++){
			$tglsisip=$periode.'-'.sprintf("%02d",$no,0,2);
			$no+=1;
			$stream.="<tr class=rowcontent>
						<td align='center'>".$tglsisip."</td>";
			for($z = 1; $z <=40 ; $z++){
				$stream.="<td align='center'></td>";
			}
			$stream.="</tr>";
		}
	}
	*/
	$bjr=($dList['janjang']>0 ? $dList['beratbersih']/$dList['janjang'] : 0);
	$ripe=$dList['jjgsortasi']-($dList['A']+$dList['B']+$dList['C']+$dList['D']+$dList['E']);
	$pripe=($dList['jjgsortasi']>0 ? ($dList['jjgsortasi']-($dList['A']+$dList['B']+$dList['C']+$dList['D']+$dList['E']))/$dList['jjgsortasi']*100 : 0);
	$kgpenalty=$dList['kgunripe']+$dList['kgoverripe']+$dList['kgemptybunch']+$dList['kgrottenbunch']+$dList['kglongstalk']+$dList['kgfruitlooses'];
	$ppenalty=($dList['beratbersih']>0 ? $kgpenalty/$dList['beratbersih']*100 : 0);
	$pgrading=($dList['janjang']>0 ? $dList['jjgsortasi']/$dList['janjang']*100 : 0);
	$jjgselisih=($dList['jjgsortasi']==100 ? 0 : $dList['jjgsortasi']-$dList['janjang']);
	$stream.="<tr class=rowcontent>
				<td align='center'>".$dList['notransaksi']."</td>
				<td align='center'>".$dList['divisi']."</td>
				<td align='center'>".$dList['tanggal']."</td>
				<td align='right'>".number_format($dList['beratbersih'],0)."</td>
				<td align='right'>".number_format($dList['janjang'],0)."</td>
				<td align='right'>".number_format($bjr,2)."</td>
				<td align='right'>".number_format($dList['jjgsortasi'],0)."</td>";
	if($kolomselisih==1){
		$stream.="<td align='right'>".number_format($jjgselisih,0)."</td>";
	}
	$stream.="	<td align='right'>".number_format($dList['A'],0)."</td>
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
				<td align='right'>".number_format($dList['G'],0)."</td>
				<td align='right'>".number_format($dList['pG'],2)."</td>
				<td align='right'>".number_format($dList['gG'],2)."</td>
				<td align='right'>".number_format($dList['kgfruitlooses'],0)."</td>
				<td align='right'>".number_format($ppenalty,2)."</td>
				<td align='right'>".number_format($kgpenalty,0)."</td>
				<td align='right'>".number_format($dList['beratbersih']-$kgpenalty,0)."</td>
				<td align='right'>".number_format($pgrading,2)."</td>
			</tr>";
			$gtberatbersih+=$dList['beratbersih'];
			$gtjanjang+=$dList['janjang'];
			//$gttruck+=$dList['truck'];
			//$gtkgsortasi+=$dList['kgsortasi'];
			$gtjjgsortasi+=$dList['jjgsortasi'];
			$gtjjgselisih+=$jjgselisih;
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
			$gtG+=$dList['G'];
			$gtkgfruitlooses+=$dList['kgfruitlooses'];
			$gtkgpenalty+=$kgpenalty;
			$gtnettograding+=$dList['beratbersih']-$kgpenalty;
			$gtvariance+=0;
}
$tgl=date('t', strtotime($periode.'-01'));
/*
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
*/
$gtbjr=($gtjanjang>0 ? $gtberatbersih/$gtjanjang : 0);
$gtpripe=($gtjjgsortasi>0 ? $gtripe/$gtjjgsortasi*100 : 0);
$gtppenalty=($gtberatbersih>0 ? $gtkgpenalty/$gtberatbersih*100 : 0);
$gtpgrading=($gtjanjang>0 ? $gtjjgsortasi/$gtjanjang*100 : 0);
$stream.="<tr bgcolor='#FEDEFE'>
			<td colspan=3 align='center'>Total</td>
			<td align='right'>".number_format($gtberatbersih,0)."</td>
			<td align='right'>".number_format($gtjanjang,0)."</td>
			<td align='right'>".number_format($gtbjr,2)."</td>
			<td align='right'>".number_format($gtjjgsortasi,0)."</td>";
if($kolomselisih==1){
	$stream.="<td align='right'>".number_format($gtjjgselisih,0)."</td>";
}
$stream.="	<td align='right'>".number_format($gtA,0)."</td>
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
			<td align='right'>".number_format(($gtberatbersih>0 ? $gtG/$gtberatbersih*100 : 0),2)."</td>
			<td align='right'>".number_format(($gtberatbersih>0 ? $gtkgfruitlooses/$gtberatbersih*100 : 0),2)."</td>
			<td align='right'>".number_format($gtkgfruitlooses,0)."</td>
			<td align='right'>".number_format($gtppenalty,2)."</td>
			<td align='right'>".number_format($gtkgpenalty,0)."</td>
			<td align='right'>".number_format($gtberatbersih-$gtkgpenalty,0)."</td>
			<td align='right'>".number_format($gtpgrading,2)."</td>
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
		$nop_="ANALISA_MUTU_TBS_".$millcode.'_'.$divisi.'_'.$periode.'_'.$tglSkrg;
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
