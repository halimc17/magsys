<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
	$proses=$_POST['proses'];
}
else
{
	$proses=$_GET['proses'];
}
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optInduk=makeOption($dbname, 'organisasi','kodeorganisasi,induk');
$_POST['kdUnit']==''?$kdUnit=$_GET['kdUnit']:$kdUnit=$_POST['kdUnit'];
$_POST['periode']==''?$periode=$_GET['periode']:$periode=$_POST['periode'];
$_POST['afdId']==''?$afdId=$_GET['afdId']:$afdId=$_POST['afdId'];

$unitId=$_SESSION['lang']['all'];
if($periode=='')
{
    exit("Error: ".$_SESSION['lang']['periode']." required");
}
if($kdUnit!='')
{
    $unitId=$optNmOrg[$kdUnit];
}
else
{
    exit("Error:".$_SESSION['lang']['unit']." required");
}
$thn=explode("-",$periode);
$bln=intval($thn[1]);
$thnLalu=$thn[0];
if($thn[1]=='01'){
	$blnLalu='12';
	$thnLalu=$thn[0]-1;
}else{
	$blnLalu="0".$bln-1;
	$blnLalu=sprintf("%02d",$bln-1);
}
$whr="";
if($afdId!='')
{
    $whr=" and a.kodeorg like '".$afdId."%'";
}
$jmlHari= cal_days_in_month(CAL_GREGORIAN, $thn[1], $thn[0]);
$brdr=0;
$bgcoloraja='';
if($proses=='excel')
{
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $tab.="
    <table>
    <tr><td colspan=6 align=left><b>07. ".$_SESSION['lang']['pusingan']." ".$_SESSION['lang']['panen']."</b></td><td colspan=5 align=right><b>".$_SESSION['lang']['bulan']." : ".substr(tanggalnormal($periode),1,7)."</b></td></tr>
    <tr><td colspan=5 align=left>".$_SESSION['lang']['unit']." : ".$optNmOrg[$kdUnit]." </td></tr>";
    if($afdId!='')
    {
      $tab.="<tr><td colspan=5 align=left>".$_SESSION['lang']['afdeling']." : ".$optNmOrg[$afdId]." </td></tr>";  
    }
    
    $tab.="<tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
}

	$tab.="<table cellspacing=1 border=".$brdr." class=sortable>
	<thead class=rowheader>
	<tr>
        <td ".$bgcoloraja." rowspan=3>".$_SESSION['lang']['kodeorg']."</td>
        <td ".$bgcoloraja." rowspan=3>".$_SESSION['lang']['blok']."</td>
        <td ".$bgcoloraja." rowspan=3>".$_SESSION['lang']['luas']." (Ha)</td><td ".$bgcoloraja." colspan=8>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['panen']." (Ha)</td</tr>";
        $tab.="<tr><td ".$bgcoloraja." colspan=2> <= 7 ".$_SESSION['lang']['hari']."</td><td ".$bgcoloraja." colspan=2>8-14 ".$_SESSION['lang']['hari']."</td>";
        $tab.="<td ".$bgcoloraja." colspan=2>15-21 ".$_SESSION['lang']['hari']."</td>";
        $tab.=" <td ".$bgcoloraja." colspan=2> >=22 ".$_SESSION['lang']['hari']."</td></tr>";
        $tab.="<tr><td ".$bgcoloraja." align=center>".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." align=center>".$_SESSION['lang']['bulanlalu']."</td>
                   <td ".$bgcoloraja." align=center>".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." align=center>".$_SESSION['lang']['bulanlalu']."</td>
                   <td ".$bgcoloraja." align=center>".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." align=center>".$_SESSION['lang']['bulanlalu']."</td>
                   <td ".$bgcoloraja." align=center>".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." align=center>".$_SESSION['lang']['bulanlalu']."</td>";
        $tab.="</thead>
	<tbody>";
$sLuas="select a.kodeorg,c.namaorganisasi as blok,b.luasareaproduktif
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."01' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."07' then a.luaspanen else 0 end) as 'bi01' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."01' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."07' then a.luaspanen else 0 end) as 'bl01' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."08' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."14' then a.luaspanen else 0 end) as 'bi08' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."08' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."14' then a.luaspanen else 0 end) as 'bl08' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."15' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."21' then a.luaspanen else 0 end) as 'bi15' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."15' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."21' then a.luaspanen else 0 end) as 'bl15' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."22' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."31' then a.luaspanen else 0 end) as 'bi22' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."22' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."31' then a.luaspanen else 0 end) as 'bl22' 
	from ".$dbname.".kebun_prestasi a
	LEFT JOIN ".$dbname.".setup_blok b on a.kodeorg=b.kodeorg
	LEFT JOIN ".$dbname.".organisasi c on a.kodeorg=c.kodeorganisasi
	where (a.notransaksi like '".$thn[0].$blnLalu."%/PNN/%' or a.notransaksi like '".$thn[0].$thn[1]."%/PNN/%') 
			and a.kodeorg like '".$kdUnit."%'".$whr."
	group by a.kodeorg";
//exit('Warning: '.$sLuas);
$qLuas=mysql_query($sLuas) or die(mysql_error());
		while($rLuas=mysql_fetch_assoc($qLuas)){
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$rLuas['kodeorg']."</td>";
            $tab.="<td align=center>".$rLuas['blok']."</td>";
            $tab.="<td align=right>".number_format($rLuas['luasareaproduktif'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bi01'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bl01'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bi08'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bl08'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bi15'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bl15'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bi22'],2)."</td>";
            $tab.="<td align=right>".number_format($rLuas['bl22'],2)."</td>";
            $tab.="</tr>";
            $totLuas+=$rLuas['luasareaproduktif'];
            $totBi1+=$rLuas['bi01'];
            $totBl1+=$rLuas['bl01'];
            $totBi2+=$rLuas['bi08'];
            $totBl2+=$rLuas['bl08'];
            $totBi3+=$rLuas['bi15'];
            $totBl3+=$rLuas['bl15'];
            $totBi4+=$rLuas['bi22'];
            $totBl4+=$rLuas['bl22'];
		}
        $tab.="<tr class=rowcontent>";
        $tab.="<td ".$bgcoloraja." colspan=2 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBi1,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBl1,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBi2,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBl2,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBi3,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBl3,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBi4,2)."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($totBl4,2)."</td>";
        $tab.="</tr>";
        $tab.="</tbody></table>";
       
switch($proses)
{
	case'preview':
		echo $tab;
		break;
	case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="Pusingan_Panen_Per_Blok_".$dte;
		/*
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $tab);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
            </script>";	
		*/
		header("Cache-Control: must-revalidate");
		header("Pragma: must-revalidate");
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=".$nop_.".xls");
		echo $tab;
		break;
	case'pdf':
		class PDF extends FPDF {
			function Header() {
				global $periode;
		        global $kdUnit;
		        global $optNmOrg;  
				global $afdId;
				global $dbname;
				global $thn;
				global $thnLalu;
				global $blnLalu;

				$this->SetFont('Arial','B',8);
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['pusingan']." ".$_SESSION['lang']['panen']." Per Blok"),0,1,'L');
				$this->SetX(450);
				$this->Cell(100,$height,$_SESSION['lang']['bulan'].' : '.substr(tanggalnormal($periode),1,7),0,1,'R');
				$tinggiAkr=$this->GetY();
				$ksamping=$this->GetX();
				$this->SetY($tinggiAkr+20);
				$this->SetX($ksamping);
				$this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNmOrg[$kdUnit],0,1,'L');
				if($afdId!=''){
					$tinggiAkr=$this->GetY();
					$ksamping=$this->GetX();
					$this->SetY($tinggiAkr+20);
					$this->SetX($ksamping);
					$this->Cell($width,$height,$_SESSION['lang']['afdeling'].' : '.$optNmOrg[$afdId],0,1,'L');
				}
				$this->Cell(790,$height,' ',0,1,'R');
				$height = 15;
				$this->SetFillColor(220,220,220);
				$this->SetFont('Arial','B',7);
				$tinggiAkr=$this->GetY();
				$ksamping=$this->GetX();
				$this->SetY($tinggiAkr+20);
				$this->SetX($ksamping);

				$this->Cell(60,$height,' ',TLR,0,'C',1);
				$this->Cell(60,$height,' ',TLR,0,'C',1);
				$this->Cell(45,$height,' ',TLR,0,'C',1);
				$this->Cell(360,$height,$_SESSION['lang']['luas']." ".$_SESSION['lang']['panen']." (Ha)",TLR,1,'C',1);
                
				$this->Cell(60,$height,$_SESSION['lang']['kodeorg'],TLR,0,'C',1);
				$this->Cell(60,$height,$_SESSION['lang']['blok'],TLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['luas']." (Ha)",TLR,0,'C',1);
				$this->Cell(90,$height,"<= 7 ".$_SESSION['lang']['hari'],TLR,0,'C',1);
				$this->Cell(90,$height,"8-14 ".$_SESSION['lang']['hari'],TLR,0,'C',1);
				$this->Cell(90,$height,"15-21 ".$_SESSION['lang']['hari'],TLR,0,'C',1);
				$this->Cell(90,$height,">= 22 ".$_SESSION['lang']['hari'],TLR,1,'C',1);
                
				$this->Cell(60,$height," ",BLR,0,'C',1);
				$this->Cell(60,$height," ",BLR,0,'C',1);
				$this->Cell(45,$height," ",BLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bulanlalu'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bulanlalu'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bulanlalu'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
				$this->Cell(45,$height,$_SESSION['lang']['bulanlalu'],TBLR,1,'C',1);
			}

			function Footer(){
				$this->SetY(-30);
				$this->SetFont('Arial','I',8);
				$this->Cell(10,10,'Page '.$this->PageNo()." / {totalPages}",0,0,'L');
			}
		}
		//================================

		$pdf=new PDF('P','pt','A4');
		$pdf->AliasNbPages('{totalPages}');
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$height = 10;
		$tnggi=$jmlHari*$height;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','B',6);
          
$sLuas="select a.kodeorg,c.namaorganisasi as blok,b.luasareaproduktif
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."01' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."07' then a.luaspanen else 0 end) as 'bi01' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."01' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."07' then a.luaspanen else 0 end) as 'bl01' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."08' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."14' then a.luaspanen else 0 end) as 'bi08' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."08' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."14' then a.luaspanen else 0 end) as 'bl08' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."15' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."21' then a.luaspanen else 0 end) as 'bi15' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."15' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."21' then a.luaspanen else 0 end) as 'bl15' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$thn[1]."22' and left(a.notransaksi,8) <= '".$thn[0].$thn[1]."31' then a.luaspanen else 0 end) as 'bi22' 
	,SUM(case when left(a.notransaksi,8) >= '".$thn[0].$blnLalu."22' and left(a.notransaksi,8) <= '".$thn[0].$blnLalu."31' then a.luaspanen else 0 end) as 'bl22' 
	from ".$dbname.".kebun_prestasi a
	LEFT JOIN ".$dbname.".setup_blok b on a.kodeorg=b.kodeorg
	LEFT JOIN ".$dbname.".organisasi c on a.kodeorg=c.kodeorganisasi
	where (a.notransaksi like '".$thn[0].$blnLalu."%/PNN/%' or a.notransaksi like '".$thn[0].$thn[1]."%/PNN/%') 
			and a.kodeorg like '".$kdUnit."%'".$whr."
	group by a.kodeorg";
//exit('Warning: '.$sLuas);
$qLuas=mysql_query($sLuas) or die(mysql_error());
		while($rLuas=mysql_fetch_assoc($qLuas)){
			$pdf->Cell(60,$height,$rLuas['kodeorg'],TBLR,0,'C',1);
			$pdf->Cell(60,$height,$rLuas['blok'],TBLR,0,'C',1);
			$pdf->Cell(45,$height,number_format($rLuas['luasareaproduktif'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bi01'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bl01'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bi08'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bl08'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bi15'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bl15'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bi22'],2),TBLR,0,'R',1);
			$pdf->Cell(45,$height,number_format($rLuas['bl22'],2),TBLR,1,'R',1);
            $totLuas+=$rLuas['luasareaproduktif'];
            $totBi1+=$rLuas['bi01'];
            $totBl1+=$rLuas['bl01'];
            $totBi2+=$rLuas['bi08'];
            $totBl2+=$rLuas['bl08'];
            $totBi3+=$rLuas['bi15'];
            $totBl3+=$rLuas['bl15'];
            $totBi4+=$rLuas['bi22'];
            $totBl4+=$rLuas['bl22'];
		}
		$pdf->SetFillColor(220,220,220);
		$pdf->Cell(120,$height,$_SESSION['lang']['total'],TBLR,0,'C',1);
		$pdf->Cell(45,$height,number_format($totLuas,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bi1,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bl1,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bi2,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bl2,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bi3,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bl3,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bi4,2),TBLR,0,'R',1);
		$pdf->Cell(45,$height,number_format($bl4,2),TBLR,1,'R',1);

		$pdf->Output();
		break;
	
	default:
	break;
}
?>
