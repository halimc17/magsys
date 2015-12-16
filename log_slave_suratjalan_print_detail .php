<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

// Class PDF Custom Surat Jalan
class sjPdf extends zPdfMaster {
	public $dataH;
	public $dataD;
	public $franco;
}

$proses = $_GET['proses'];
$param = $_GET;

/** Report Prep **/
$where = "nosj='".$param['nosj']."'";
$cols = 'kodept,kodebarang,jenis,jumlah,satuanpo,nopo,nopp';

$colArr = explode(',',$cols);
$query = selectQuery($dbname,'log_suratjalandt',$cols,$where,'nosj desc');
$data = fetchData($query);
$resData = $data;
$barang = '';
foreach($data as $row) {
	if(!empty($barang)) {$barang .= ',';}
	$barang .= "'".$row['kodebarang']."'";
}

// Header
$queryH = selectQuery($dbname,'log_suratjalanht','*',$where);
$dataH = fetchData($queryH);
$dataH = $dataH[0];
$tmpTgl = explode('-',$dataH['tanggal']);
$tglStr = date('d F Y',mktime(0,0,0,$tmpTgl[1],$tmpTgl[2],$tmpTgl[0]));

// Get Kota
$qOrg = selectQuery($dbname,'organisasi','namaorganisasi,wilayahkota',"kodeorganisasi='".$dataH['kodept']."'");
$resOrg = fetchData($qOrg);
$kota = ucfirst(strtolower($resOrg[0]['wilayahkota']));
$nmOrg = $resOrg[0]['namaorganisasi'];

// Option
$optBarang = array();
$optPartnum = array();
if(!empty($barang)) {
	$qBarang = selectQuery($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang in (".$barang.")");
	$resBarang = fetchData($qBarang);
	foreach($resBarang as $row) {
		$optBarang[$row['kodebarang']] = $row['namabarang'];
	}
}

// Franco
$qFranco = selectQuery($dbname,'setup_franco','*',"id_franco='".$dataH['franco']."'");
$resFranco = fetchData($qFranco);
$franco = $resFranco[0];

$align = explode(",","L,L,R,L,L,L");
$length = explode(",","5,8,34,8,7,19,19");

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new sjPdf('P','pt','A4');
        $pdf->_kopOnly = true;
		$pdf->_kodeOrg = $dataH['kodept'];
		$pdf->dataH = $dataH;
		$pdf->dataD = $data;
		$pdf->franco = $franco;
		$pdf->_logoOrg = $dataH['kodept'];
		$pdf->_orgName = $nmOrg;
		$pdf->_noThead = true;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		
		// Title
		$pdf->SetFont('Arial','BU',15);
		$pdf->Cell($width,15,'Surat Jalan',0,1,'C');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell($width,10,'No. '.$param['nosj'],0,1,'C');
		$pdf->Ln();
		
		// Kop
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(10/100*$width,$height,'TO:',0,0,'L');
		$pdf->Cell(50/100*$width,$height,$franco['franco_name'],0,0,'L');
		$pdf->Cell(40/100*$width,$height,$kota.', '.$tglStr,0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'L');
		$pdf->Cell(50/100*$width,$height,$franco['alamat'],0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'L');
		$pdf->Cell(50/100*$width,$height,'Phone : '.$franco['handphone'],0,0,'L');
		$pdf->MultiCell(40/100*$width,$height,'UP : '.$franco['contact'].' ('.$franco['handphone'].')',0,'J');
		$pdf->Ln();
		
		// Narasi
		$pdf->Cell($width,$height,'Terkirim barang-barang milik '.$pdf->_orgName.' yang terdiri dari:',0,0,'L');
		$pdf->Ln();
        
        $pdf->SetFillColor(200,200,200);
		$pdf->SetFont('Arial','B',8);
		
		// Table Header
		$pdf->Cell(5/100*$width,$height,'NO',1,0,'C',1);
		$pdf->Cell(42/100*$width,$height,'BARANG',1,0,'C',1);
		$pdf->Cell(8/100*$width,$height,'QTY',1,0,'C',1);
		$pdf->Cell(7/100*$width,$height,'UNIT',1,0,'C',1);
		$pdf->Cell(19/100*$width,$height,'PO NO',1,0,'C',1);
		$pdf->Cell(19/100*$width,$height,'PP NO',1,0,'C',1);
		$pdf->Ln();
		
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',8);
		$i=0;
		$beginY = $pdf->GetY();
		$notPlY = $pdf->GetY();
		foreach($resData as $row) {
			if($pdf->GetY()>760) {
				$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin,$endY);
				$currLen = 0;
				for($j=0;$j<8;$j++) {
					$currLen += $length[$j]/100*$width;
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
							   $pdf->lMargin+$currLen,$endY);
				}
				$pdf->AddPage();
				$beginY = $pdf->GetY();
				$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin+$width,$beginY);
			}
			
			$endY = $currY = $pdf->GetY();
			$pdf->Cell(5/100*$width,$height,$i+1,0,0,'R');
			$pdf->Cell(8/100*$width,$height,$row['kodebarang'],0,0,'L');
			if(isset($optBarang[$row['kodebarang']])) {
				$pdf->MultiCell(34/100*$width,$height,$optBarang[$row['kodebarang']],0,'J');
				$endY = $pdf->GetY();
				$pdf->SetY($currY);
				$pdf->SetX($pdf->GetX()+47/100*$width);
			} else {
				$pdf->Cell(34/100*$width,$height,'',0,0,'L');
			}
			$pdf->Cell(8/100*$width,$height,$row['jumlah'],0,0,'R');
			$pdf->Cell(7/100*$width,$height,$row['satuanpo'],0,0,'L');
			$pdf->Cell(19/100*$width,$height,$row['nopo'],0,0,'L');
			$pdf->Cell(19/100*$width,$height,$row['nopp'],0,0,'L');
			if(!isset($optBarang[$row['kodebarang']])) {
				$pdf->Ln();
				$endY = $pdf->GetY();
			}
			if($endY>$pdf->GetY()) {
				$pdf->SetY($endY);
			}
			if(substr($row['kodebarang'],0,2)!='PL') {
				$notPlY = $pdf->GetY();
			}
			$pdf->Line($pdf->lMargin,$endY,$pdf->lMargin+$width,$endY);
			$i++;
        }
		
		if(!empty($resData)) {
			$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin,$endY);
			$currLen = 0;
			for($i=0;$i<7;$i++) {
				$currLen += $length[$i]/100*$width;
				if($i==1) {
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
						$pdf->lMargin+$currLen,$notPlY);
				} else {
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
						$pdf->lMargin+$currLen,$endY);
				}
			}
		}
		
		// Space untuk penandatangan
		if($pdf->GetY()>620) {
			$pdf->AddPage();
		}
		$pdf->Ln($height*2);
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'C');
		$pdf->Cell(20/100*$width,$height,'CHECKED BY',0,0,'L');
		$pdf->Cell(70/100*$width,$height,': '.$dataH['checkedby'],0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'C');
		$pdf->Cell(20/100*$width,$height,'DRIVER',0,0,'L');
		$pdf->Cell(70/100*$width,$height,': '.$dataH['driver'].' Ph.'.$dataH['hpdriver'],0,0,'L');
		$pdf->Ln($height*2);
		
		// Narasi penutup
		$pdf->Cell($width,$height,'Barang-barang tersebut akan dikirim ke perkebunan milik '.$pdf->_orgName,0,1,'L');
		$pdf->Cell($width,$height,'yang berada di ',0,0,'L');
		$pdf->Ln($height*2);
		$pdf->Cell($width,$height,'Demikian Surat Pengantar Barang ini di buat untuk dipergunakan dengan semestinya',0,0,'L');
		$pdf->Ln($height*2);
		
		$pdf->Cell(25/100*$width,$height,'Pengirim',0,0,'C');
		$pdf->Cell(25/100*$width,$height,'Mengetahui',0,0,'C');
		$pdf->Cell(25/100*$width,$height,'Angkutan',0,0,'C');
		$pdf->Cell(25/100*$width,$height,'Penerima',0,0,'C');
		$pdf->Ln($height*4);
		
		$pdf->Cell(25/100*$width,$height,$pdf->_orgName,0,0,'C');
		$pdf->Cell(25/100*$width,$height,'',0,0,'C');
		$pdf->Cell(25/100*$width,$height,$dataH['jeniskend'].' : '.$dataH['nopol'],0,0,'C');
		$pdf->Cell(25/100*$width,$height,$dataH['penerima'],0,0,'C');
                
                $pdf->Ln($height*2);
                $pdf->Cell($width,$height,$_SESSION['lang']['fyiGudang2'],0,0,'L');
                
                
		
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>