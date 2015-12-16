<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$tmp=explode(',',$_GET['column']);
$notran=$tmp[0];
$jabatanTtd=makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');

$qKepada = selectQuery($dbname, 'pmn_5kepada', 'id,kepada,alamat');
$resKepada = fetchData($qKepada);
$optKepada = array();
foreach($resKepada as $row) {
	$optKepada[$row['id']] = array(
		'kepada' => $row['kepada'],
		'alamat' => $row['alamat']
	);
}

$str="select a.*,b.kuantitaskontrak,b.satuan,c.namabarang,d.namacustomer,d.alamat as alamatpelanggan,d.kota,d.telepon,f.namaorganisasi,f.alamat,f.wilayahkota from ".$dbname.".pmn_suratperintahpengiriman a
	left join ".$dbname.".pmn_kontrakjual b
	on a.nokontrak = b.nokontrak 
	left join ".$dbname.".log_5masterbarang c
	on b.kodebarang = c.kodebarang 
	left join ".$dbname.".pmn_4customer d
	on b.koderekanan = d.kodecustomer 
	left join ".$dbname.".organisasi f
	on b.kodept = f.kodeorganisasi
	where a.nodo='".$notran."'";



$qry=mysql_query($str) or die(mysql_error($conn));
$data=mysql_fetch_assoc($qry);
	
class PDF extends FPDF
{
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',10);
	}

}
	
	$pdf=new PDF('P','mm','A4');
	$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
	$height = 5;
	$pdf->AddPage();
	
	$pdf->Ln(45);
	$pdf->SetFont('Arial','UB',12);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(200,5,strtoupper($_SESSION['lang']['suratperintahpengiriman']),0,1,'C');
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(200,5,$_SESSION['lang']['nourut']." : ".$notran,0,1,'C');
	$pdf->ln();
	$pdf->ln();
	
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);	
	$pdf->MultiCell(20,$height,$_SESSION['lang']['kepada'],0,'L');
		
	$pdf->SetX(30);
	$pdf->SetFont('Arial','B',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,$optKepada[$data['kepada']]['kepada'],0,'J');
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	
	$tmpAlamat = explode(',',$optKepada[$data['kepada']]['alamat'],2);
	$pdf->Cell(150,$height,$tmpAlamat[0],0,1,'L');
	$pdf->SetX(30);
	$tmpAlamat2 = explode(' - ',$tmpAlamat[1]);
	if(count($tmpAlamat2)>1) {
		$pdf->Cell(150,$height,trim($tmpAlamat2[0]),0,1,'L');
		$pdf->SetX(30);
		$pdf->Cell(150,$height,trim($tmpAlamat2[1]),0,1,'L');
	} else {
		$pdf->Cell(150,$height,trim($tmpAlamat[1]),0,1,'L');
	}
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,'Mohon dikirim kepada',0,'J');
	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,'',0,'J');
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['Pembeli'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$data['namacustomer'],0,'J');
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,'',0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,'',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(100,$height,$data['alamatpelanggan'],0,'J');
	if($data['telepon'] == '' || $data['telepon'] == '0'){
		
	}else{
		$pdf->SetX(30);
		$pdf->SetFont('Arial','',9);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(50,$height,'',0,'J');	
		$pdf->SetX(65);
		$pdf->Cell(2,$height,'',0,'J');
		$pdf->SetX(68);
		$pdf->MultiCell(98,$height,$data['telepon'],0,'J');
	}
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['komoditi'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$data['namabarang'],0,'J');
        
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,'Kuantitas',0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,number_format($data['qty']).' Kg',0,'J');
        
        
        
        $franco=  makeOption($dbname, 'pmn_5franco', 'id_franco,franco_name');
        $arrX=array('franco'=>'Franco','loco'=>'Loco','fob'=>'FOB');
        
        $iFranco=" select * from ".$dbname.".pmn_5franco where id_franco='".$data['tempatpenyerahan']."' ";
        $nFranco=  mysql_query($iFranco) or die (mysql_error($conn));
        $dFranco=  mysql_fetch_assoc($nFranco);
        
        $francoList=$arrX[$dFranco['penjualan']].' '.$dFranco['franco_name'].' '.$dFranco['alamat'];
	//$pdf->MultiCell(98,$height,number_format($data['kuantitaskontrak']).' '.$data['satuan'],0,'J');
	
        
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['tempatpenyerahan'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$francoList,0,'J');
	
	if (strpos($data['waktupenyerahan'],'00-00-0000') !== false) {
		$hWaktuPenyerahan = "Mulai Tanggal ".substr($data['waktupenyerahan'],0,10);
	}else{
		$hWaktuPenyerahan = "Mulai Tanggal ".$data['waktupenyerahan'];
	}
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['waktupenyerahan'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$hWaktuPenyerahan,0,'J');
	
	/*$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['kualitas'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$data['kualitas'],0,'J');*/
        
        $iData=" select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$data['nokontrak']."' ";
        $nData=  mysql_query($iData) or die (mysql_error($conn));
        $dData=  mysql_fetch_assoc($nData);
            $ffaData=number_format($dData['ffa'],2).' ';
            $dobiData=number_format($dData['dobi'],2).' ';
            $mdaniData=number_format($dData['mdani'],2).' ';
            $moistData=number_format($dData['moist'],2).' ';
            $dirtData=number_format($dData['dirt'],2).' ';
        
	
            
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['kualitas'],0,'J');
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$emptyQual = true;
	if($ffaData!=0)
	{
		$pdf->SetX(65);
		$pdf->SetX(68);
        $pdf->Cell(15,$height,'FFA',0,0,'J');
        $pdf->Cell(5,$height,':',0,0,'J');
        $pdf->Cell(20,$height,$ffaData.' % Max',0,1,'J');
		$emptyQual = false;
    }
        
	if($dobiData!=0)
	{
		$pdf->SetX(133);
		$pdf->SetX(68);
        $pdf->Cell(15,$height,'DOBI',0,0,'J');
        $pdf->Cell(5,$height,':',0,0,'J');
        $pdf->Cell(20,$height,$dobiData.' Min',0,1,'J');
		$emptyQual = false;
    }
        
	if($mdaniData!=0)
	{
        $pdf->SetX(133);
		$pdf->SetX(68);
        $pdf->Cell(15,$height,'M & I',0,0,'J');
        $pdf->Cell(5,$height,':',0,0,'J');
        $pdf->Cell(20,$height,$mdaniData.' % Max',0,1,'J');
		$emptyQual = false;
    }
        
	if($moistData!=0)
	{
        $pdf->SetX(133);
		$pdf->SetX(68);
        $pdf->Cell(15,$height,'Moister',0,0,'J');
        $pdf->Cell(5,$height,':',0,0,'J');
        $pdf->Cell(20,$height,$moistData.' % Max',0,1,'J');
		$emptyQual = false;
    }
        
	if($dirtData!=0)
	{
        $pdf->SetX(133);
		$pdf->SetX(68);
        $pdf->Cell(15,$height,'Derth',0,0,'J');
        $pdf->Cell(5,$height,':',0,0,'J');
        $pdf->Cell(20,$height,$dirtData.' % Max',0,1,'J');
		$emptyQual = false;
    }
	if($emptyQual) {$pdf->Ln();}
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['NoKontrak'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$data['nokontrak'],0,'J');
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['carapembayaran'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,'('.$_SESSION['lang']['sesuaikontrak'].')',0,'J');
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(50,$height,$_SESSION['lang']['others'],0,'J');	
	$pdf->SetX(65);
	$pdf->Cell(2,$height,':',0,'J');
	$pdf->SetX(68);
	$pdf->MultiCell(98,$height,$data['keterangan'],0,'J');
	
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,'Demikian surat ini, atas perhatiannya kami ucapkan terima kasih.',0,'J');
	
	$pdf->Ln();
	$pdf->Ln();
	
	$bulan = substr($data['tanggaldo'],5,2);
	$tanggalSurat = substr($data['tanggaldo'],8,2)." ".numToMonth($bulan,'I','long')." ".substr($data['tanggaldo'],0,4);
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,'Jakarta, '.$tanggalSurat,0,'J');
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,'Hormat Kami,',0,'J');
	
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->SetX(30);
	$pdf->SetFont('Arial','U',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,$data['ttd'],0,'J');
	$pdf->SetX(30);
	$pdf->SetFont('Arial','B',9);
	$pdf->SetFillColor(255,255,255);
	$pdf->MultiCell(150,$height,$jabatanTtd[$data['ttd']],0,'J');
	$pdf->Ln();
	$pdf->Output();
?>
