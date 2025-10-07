<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses = $_GET['proses'];
$param = $_GET;


/** Report Prep **/
$cols = array();

#=============================== Header =======================================
$where = "noinvoice='".$param['noinvoice']."'";
$queryH = selectQuery($dbname,'keu_tagihanht','*',$where);
$resH = fetchData($queryH);
$dataH = $resH[0];

#=============================== Detail =======================================
# Data
$query = selectQuery($dbname,'keu_tagihandt','*',$where);
$res = fetchData($query);

# Options
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun like '116%' and detail=1");
$optPt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					"kodeorganisasi='".$dataH['kodeorg']."'");
$tab = ($dataH['tipeinvoice']=='p')? 'log_poht': 'log_spkht';
$cond = ($dataH['tipeinvoice']=='p')? 'nopo': 'notransaksi';

if($tab=='log_poht')
{
    $qSupp = "select b.namasupplier".(($dataH['tipeinvoice']=='p')? ',a.matauang':'')."
	from ".$dbname.".".$tab." a
	left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
	where ".$cond."='".$dataH['nopo']."'";    
}
else
{
    $qSupp = "select b.namasupplier".(($dataH['tipeinvoice']=='p')? ',a.matauang':'')."
	from ".$dbname.".".$tab." a
	left join ".$dbname.".log_5supplier b on a.koderekanan=b.supplierid
	where ".$cond."='".$dataH['nopo']."'";
}    
if($dataH['tipeinvoice']=='t'){
    $qSupp="select b.namacustomer as namasupplier,a.matauang from ".$dbname.".pmn_traderht a
			left join ".$dbname.".pmn_4customer b on a.kodecustomer=b.kodecustomer
			where nokontrakext='".$dataH['nopo']."'";    
}
//exit("Error:$qSupp");

$resSupp = fetchData($qSupp);
if($resSupp[0]['namasupplier']=='' || !isset($resSupp[0]['namasupplier'])){
	if($dataH['tipeinvoice']=='t'){
		$str="select b.namacustomer as namasupplier from ".$dbname.".keu_tagihanht a 
			  left join ".$dbname.".pmn_4customer b on a.kodecustomer=b.kodecustomer 
			  where a.noinvoice='".$param['noinvoice']."'";
	}else{
		$str="select b.namasupplier from ".$dbname.".keu_tagihanht a left join ".$dbname.".log_5supplier b
              on a.kodesupplier=b.supplierid where a.noinvoice='".$param['noinvoice']."'";
	}
	$res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
		$resSupp[0]['namasupplier']=$bar->namasupplier;
	}
}
#================================ Prep Data ===================================
$title = "INVOICE";

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->_noThead=true;
		$pdf->_title = $title;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
		
		switch($dataH['tipeinvoice']) {
			case 'p':
				$tipe = 'PO';
				break;
			case 'k':
				$tipe = 'SPK';
				break;
			case 's':
				$tipe = 'SJ';
				break;
			case 'b':
				$tipe = 'BK';
				break;
			case 't':
				$tipe = 'Kontrak Ext.';
				break;
		}
		
		$pdf->Ln();
		// Header
		$startY = $pdf->GetY();
                
                $pdf->Cell(85,$height,$_SESSION['lang']['noinvoice'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,$dataH['noinvoice'],0,1,'L');
                
                $pdf->Cell(85,$height,$_SESSION['lang']['pt'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,$optPt[$dataH['kodeorg']],0,1,'L');
                
                $pdf->Cell(85,$height,$_SESSION['lang']['tanggal'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,tanggalnormal($dataH['tanggal']),0,1,'L');
                
                
                $pdf->Cell(85,$height,$_SESSION['lang']['keterangan'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->MultiCell(170,$height,$dataH['keterangan'],0,'L');
                
                $pdf->Cell(85,$height,$_SESSION['lang']['jatuhtempo'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,tanggalnormal($dataH['jatuhtempo']),0,1,'L');
                
                
		
                $pdf->Cell(85,$height,$_SESSION['lang']['nofp'],0,0,'L');
                $pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,$dataH['nofp'],0,1,'L');
                
                $pdf->Cell(85,$height,$_SESSION['lang']['nilaiinvoice'],0,0,'L');
                $pdf->Cell(10,$height,':',0,0,'L');
		if($dataH['nilaiinvoice']==0 or $dataH['nilaiinvoice']==''){
                $pdf->Cell(25,$height,number_format($dataH['uangmuka'],2),0,1,'L');
		}else{
                $pdf->Cell(25,$height,number_format($dataH['nilaiinvoice'],2),0,1,'L');
		}                
                
                //sisi kanan
                
                $pdf->SetXY(290,$startY);
                $awalx=$pdf->GetX();
                $setpanjang=275;
                $pdf->Cell($setpanjang,$height,$tipe,1,1,'L');
                
		
                $pdf->SetX(290);   
                
                $pdf->Cell(85,$height,'No. '.$tipe,0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,$dataH['nopo'],0,1,'L');
                
                $pdf->SetX(290); 
                $pdf->Cell(85,$height,$_SESSION['lang']['noinvoice'].' Supplier',0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,$dataH['noinvoicesupplier'],0,1,'L');
                
                $pdf->SetX(290);    
                $pdf->Cell(85,$height,$_SESSION['lang']['supplier'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                //$pdf->Cell(25,$height,$resSupp[0]['namasupplier'],0,1,'L');
                $pdf->MultiCell(190,$height,$resSupp[0]['namasupplier'],0,'L');
                
                $pdf->SetX(290);
                $pdf->Cell(85,$height,$_SESSION['lang']['matauang'],0,0,'L');
		$pdf->Cell(10,$height,':',0,0,'L');
                $pdf->Cell(25,$height,(isset($resSupp[0]['matauang'])? $resSupp[0]['matauang']: 'IDR'),0,1,'L');
                
                $pdf->SetX(290);    
                $pdf->Cell(85,$height,$_SESSION['lang']['kurs'],'B',0,'L');
		$pdf->Cell(10,$height,':','B',0,'L');
                $pdf->Cell(180,$height,$dataH['kurs'],'B',1,'L');
                
		$endY = $pdf->GetY();
		
		//$pdf->Rect($pdf->lMargin+49.5/100*$width,$startY-1,50.5/100*$width,$endY-$startY-7);
		//$pdf->Line($pdf->lMargin+49.5/100*$width,$startY+11,$pdf->lMargin+$width,$startY+11);
		
                $pdf->Line($awalx, $startY, $awalx, $endY);
                $pdf->Line($awalx+$setpanjang, $startY, $awalx+$setpanjang, $endY);
                
		$pdf->Ln($height);
		
	    $pdf->Output();
        break;
    default:
    break;
}
?>