<?
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/fpdf.php');
	require_once('lib/zLib.php');
	require_once('lib/terbilang.php');

	$notransaksi=$_GET['notransaksi'];
	#Data header
	$sht="select a.*,b.namaorganisasi as namaorg,b.induk,b.wilayahkota,c.namaorganisasi as namapt from ".$dbname.".log_brgjadiht a
			left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
			left join ".$dbname.".organisasi c on c.kodeorganisasi=b.induk
			where a.notransaksi='".$notransaksi."'";
	$qht=mysql_query($sht) or die (mysql_error($conn));
	while($dht=mysql_fetch_assoc($qht)){
		$kodept=$dht['induk'];
		$namapt=$dht['namapt'];
		$kodeorg=$dht['kodeorg'];
		$namaorg=$dht['namaorg'];
		$wilayahkota=$dht['wilayahkota'];
		$kodegudang=$dht['kodegudang'];
		$tanggal=$dht['tanggal'];
		$keterangan=$dht['keterangan'];
	}
	$namahari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum'at","Sabtu");
	$namabulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
	$hari=$namahari[date('N', strtotime($tanggal))];
	$bln=substr($tanggal,5,2)+0;
	$namatgl=terbilang(substr($tanggal,8,2),2);
	$namabln=$namabulan[$bln-1];
	$namathn=terbilang(substr($tanggal,0,4),2);

	//create Header
	class PDF extends FPDF{
		function Header(){
			global $notransaksi;
			global $kodept;
			global $namapt;
			global $kodeorg;
			global $namaorg;
			global $kodegudang;
			global $tanggal;
			global $keterangan;

			$path='images/logo.jpg';
			$this->Image($path,12,0,0,30);
			$path2='images/logo_aaa.jpg';
			if($kodept=='AMP'){
				$path2='images/logo_amp.jpg';
				$this->Image($path2,252,2,0,30);
			}elseif($kodept=='CKS'){
				$path2='images/logo_cks.jpg';
				$this->Image($path2,252,2,0,30);
			}elseif($kodept=='MPA'){
				$path2='images/logo_mpa.jpg';
				$this->Image($path2,252,2,0,30);
			}elseif($kodept=='KAL'){
				$path2='images/logo_kal.jpg';
				$this->Image($path2,252,2,0,30);
			}elseif($kodept=='KAA'){
				$path2='images/logo_kaa.jpg';
				$this->Image($path2,252,2,0,30);
			}elseif($kodept=='LKA'){
				$path2='images/logo_lka.jpg';
				$this->Image($path2,252,2,0,30);
			}
			$this->SetFont('Arial','B',14);
			$this->SetFillColor(255,255,255);
			$this->SetXY(20,15);
			$this->Cell(250,5,$namapt,0,1,'C');
			$this->SetFont('Arial','',15);
			$this->Cell(175,5,'',0,1,'C');
			$this->SetXY(42,15);
			$this->SetFont('Arial','',9); 
			/*
			$this->Cell(18,5,'Head Office',0,0,'L');
			$this->Cell(5,5,': ',0,0,'L');
			$this->SetX(62);
			if(strlen($alamatpt)>80){
				$this->MultiCell(105,5,$alamatpt.', Telp. '.$teleponpt,0,'J');
			}else{
				$this->Cell(105,5,': '.$alamatpt,0,1,'L');
				$this->SetX(62);
				$this->Cell(145,5,'Telp. '.$teleponpt,0,1,'L');
			}
			$this->SetX(42);
			$this->Cell(18,5,'Estate',0,0,'L');
			$this->Cell(145,5,': '.$alamat,0,1,'L');
			*/
			$this->SetY(30);
			$this->SetX(163);
			//$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
			$this->Line(10,32,280,32);
		}

		function Footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','I',6);
			$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
			//$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
		}
	}

	$judul="PEMAKAIAN BAHAN BAKU KE BAHAN JADI";

	$pdf=new PDF('L','mm','A4');
	$pdf->SetFont('Arial','B',14);
	$pdf->AddPage();
	$pdf->SetY(40);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255); 
	$pdf->Cell(250,5,$judul,0,1,'C');
	$pdf->SetX(20);
	$pdf->SetFont('Arial','B',12);
	$pdf->Ln();	
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(40,5,'No Transaksi',0,0,'L');
	$pdf->Cell(130,5,': '.$notransaksi,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(40,5,'Unit',0,0,'L');
	$pdf->Cell(130,5,': '.$kodeorg.' - '.$namaorg,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(40,5,'Tanggal',0,0,'L');
	$pdf->Cell(130,5,': '.$tanggal,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(40,5,'Keterangan',0,0,'L');
	$pdf->Cell(130,5,': '.$keterangan,0,1,'L');
	$pdf->Ln();
	$pdf->SetX(20);
	//exit('Warning X='.$pdf->SetX().' Y='.$pdf->SetY().' XY='.$pdf->SetXY());
	$pdf->SetFont('Arial','',10);
    $pdf->Cell(7,5,'Bahan Baku',0,1,'L');
	$pdf->SetX(20);
    $pdf->Cell(7,5,'No',1,0,'C');
    $pdf->Cell(18,5,'Kode',1,0,'C');
    $pdf->Cell(70,5,'Nama Barang',1,0,'C');
    $pdf->Cell(15,5,'Satuan',1,0,'C');
    $pdf->Cell(15,5,'Jumlah',1,1,'C');
	$sdt="select a.*,b.namabarang,b.satuan from ".$dbname.".log_brgjadidt a
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			where a.notransaksi='".$notransaksi."' and tipetransaksi=1";
	$qdt=mysql_query($sdt) or die (mysql_error($conn));
	$no=0;
	while($ddt=mysql_fetch_assoc($qdt)){
		$no+=1;
		$pdf->SetX(20);	
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(07,5,$no,1,0,'C');
		$pdf->Cell(18,5,$ddt['kodebarang'],1,0,'L');
		$pdf->Cell(70,5,$ddt['namabarang'],1,0,'L');
		$pdf->Cell(15,5,$ddt['satuan'],1,0,'C');
		$pdf->Cell(15,5,$ddt['jumlah'],1,1,'R');
	}
	$pdf->SetY(75);
	$pdf->SetX(150);
	$pdf->SetFont('Arial','',10);
    $pdf->Cell(7,5,'Bahan Jadi',0,1,'L');
	$pdf->SetX(150);
    $pdf->Cell(7,5,'No',1,0,'C');
    $pdf->Cell(18,5,'Kode',1,0,'C');
    $pdf->Cell(70,5,'Nama Barang',1,0,'C');
    $pdf->Cell(15,5,'Satuan',1,0,'C');
    $pdf->Cell(15,5,'Jumlah',1,1,'C');
	$sdt="select a.*,b.namabarang,b.satuan from ".$dbname.".log_brgjadidt a
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			where a.notransaksi='".$notransaksi."' and tipetransaksi=2";
	$qdt=mysql_query($sdt) or die (mysql_error($conn));
	$no2=0;
	while($ddt=mysql_fetch_assoc($qdt)){
		$no2+=1;
		$pdf->SetX(150);	
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(07,5,$no2,1,0,'C');
		$pdf->Cell(18,5,$ddt['kodebarang'],1,0,'L');
		$pdf->Cell(70,5,$ddt['namabarang'],1,0,'L');
		$pdf->Cell(15,5,$ddt['satuan'],1,0,'C');
		$pdf->Cell(15,5,$ddt['jumlah'],1,1,'R');
	}
	for ($x = 1; $x <= $no; $x++) {
		$pdf->Ln();
	}
	$pdf->Ln();
	$pdf->SetX(20);
	if(strstr(strtoupper($kodeorg),'HO')){
		$pdf->Cell(170,5,ucwords(strtolower($wilayahkota)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}else{
		$pdf->Cell(170,5,ucwords(strtolower($wilayahkota)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}
	$pdf->Ln();
	//$pdf->Ln();
	// Kolom Tanda Tangabn
	if(strstr(strtoupper($kodeorg),'HO')){
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",1,0,'C');
		$pdf->Cell(80,5,$_SESSION['lang']['diketahuioleh'].",",1,0,'C');
        $baris=$pdf->GetY();
        $pdf->Line(20, $baris, 20, $baris+30);
        $pdf->Line(60, $baris, 60, $baris+30);
        $pdf->Line(100, $baris+5, 100, $baris+30);
        $pdf->Line(140, $baris, 140, $baris+30);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->Cell(40,5,'',1,0,'C');
		$pdf->Cell(40,5,'',1,0,'C');
		$pdf->Cell(40,5,'',1,0,'C');
	}else{
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",1,0,'C');
		$pdf->Cell(80,5,$_SESSION['lang']['diketahuioleh'].",",1,0,'C');
        $baris=$pdf->GetY();
        $pdf->Line(20, $baris, 20, $baris+30);
        $pdf->Line(60, $baris, 60, $baris+30);
        $pdf->Line(100, $baris+5, 100, $baris+30);
        $pdf->Line(140, $baris, 140, $baris+30);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->Cell(40,5,' ',1,0,'C');
		$pdf->Cell(40,5,' ',1,0,'C');
		$pdf->Cell(40,5,' ',1,0,'C');
	}
	$pdf->Ln();
	//footer================================
    $pdf->Ln();		
	$pdf->Output();
?>
