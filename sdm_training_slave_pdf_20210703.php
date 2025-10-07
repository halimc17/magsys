<?
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/fpdf.php');
	require_once('lib/zLib.php');

	$notransaksi=$_GET['notransaksi'];
	$notransaksi=($notransaksi=='' ? $_POST['notransaksi'] : $notransaksi) ;
	if($notransaksi==''){
		exit('Warning : Tidak ada No Transaksi...');
	}

	//============= Data Pelatihan
	$str="	select a.*,b.nik,b.namakaryawan,d.namaorganisasi,e.namajabatan,f.jenistraining as kategori from ".$dbname.".sdm_karyawantraining a 
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			left join ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(b.subbagian,4))
			left join ".$dbname.".organisasi d on d.kodeorganisasi=c.induk
			left join ".$dbname.".sdm_5jabatan e on e.kodejabatan=b.kodejabatan
			left join ".$dbname.".sdm_5jenistraining f on f.kodetraining=a.jenistraining
			where a.nomor='".$notransaksi."'";
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$namaorganisasi=$bar->namaorganisasi;
		$karyawanid=$bar->karyawanid;
		$nik=$bar->nik;
		$namakaryawan=$bar->namakaryawan;
		$namajabatan=$bar->namajabatan;
		$jenistraining=$bar->jenistraining;
		$kategori=$bar->kategori;
		$judultraining=$bar->judultraining;
		$tanggalmulai=$bar->tanggalmulai;
		$tanggalselesai=$bar->tanggalselesai;
		$penyelenggara=$bar->penyelenggara;
		$sertifikat=$bar->sertifikat;
		$biaya=$bar->biaya;
	}

	class PDF extends FPDF{
		function Header(){
			global $conn;
			global $dbname;
			global $namaorganisasi;
			global $notransaksi;
			global $karyawanid;
			global $tanggalmulai;
			$path='images/logo.jpg';
			$this->Image($path,12,2,0,26);
			$this->SetFont('Arial','B',10);
			$this->SetFillColor(255,255,255);	
			$this->SetXY(40,22);
			$this->Cell(160,6,$namaorganisasi,0,1,'R');	 
			$this->SetFont('Arial','',15);
			$this->Cell(190,5,'',0,1,'C');
			$this->SetFont('Arial','',6); 
			$this->SetY(28);
			$this->SetX(120);
		    //$this->Cell(80,5,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'R');		
			$this->Line(10,28,200,28);	   
		}

		function Footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','I',8);
			//$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
		    $this->Cell(30,5,'Print Time : '.date('d-m-Y H:i:s'),0,1,'L');		
		}
	}

	$pdf=new PDF('P','mm','A4');
	$pdf->SetFont('Arial','B',14);
	$pdf->AddPage();
	$pdf->SetY(35);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255);
    $pdf->Cell(170,7,strtoupper('Rekomendasi Program Pelatihan'),0,1,'C');
	$pdf->Line(58,42,152,42);
	$pdf->Ln();
	$pdf->SetX(10);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(100,7,'A. Data Karyawan',0,1,'L');
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(35,6,$_SESSION['lang']['nik'],0,0,'L');
	$pdf->Cell(50,6," : ".$nik,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,$_SESSION['lang']['namakaryawan'],0,0,'L');
	$pdf->Cell(50,6," : ".$namakaryawan,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,$_SESSION['lang']['functionname'],0,0,'L');
	$pdf->Cell(50,6," : ".$namajabatan,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,'Kategori Training',0,0,'L');
	$pdf->Cell(50,6," : ".$kategori,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,'Nama Training',0,0,'L');
	$pdf->Cell(50,6," : ".$judultraining,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,'Waktu Training',0,0,'L');
	$pdf->Cell(50,6," : ".tanggalnormal($tanggalmulai)." s/d ".tanggalnormal($tanggalselesai),0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,'Vendor Training',0,0,'L');
	$pdf->Cell(50,6," : ".$penyelenggara,0,1,'L');
	$pdf->SetX(20);
	$pdf->Cell(35,6,'Biaya Training',0,0,'L');
	$pdf->Cell(50,6," : ".number_format($biaya,2),0,1,'L');

	$pdf->Cell(35,2,'',0,1,'L');//pengganti $pdf->Ln();
	$pdf->SetX(10);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(100,7,'B. Training Track',0,1,'L');
	$pdf->SetX(10);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(6,5,'No',1,0,'C');
	$pdf->Cell(21,5,'Jenis Training',1,0,'C');
	$pdf->Cell(65,5,'Nama Training',1,0,'C');
	$pdf->Cell(34,5,'Waktu Training',1,0,'C');
	$pdf->Cell(42,5,'Vendor Training',1,0,'C');
	$pdf->Cell(22,5,'Biaya Training',1,1,'C');
	$sstr="	select a.*,b.nik,b.namakaryawan,d.namaorganisasi,e.namajabatan,f.jenistraining as kategori from ".$dbname.".sdm_karyawantraining a 
			left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
			left join ".$dbname.".organisasi c on c.kodeorganisasi=if(b.subbagian='',b.lokasitugas,left(b.subbagian,4))
			left join ".$dbname.".organisasi d on d.kodeorganisasi=c.induk
			left join ".$dbname.".sdm_5jabatan e on e.kodejabatan=b.kodejabatan
			left join ".$dbname.".sdm_5jenistraining f on f.kodetraining=a.jenistraining
			where a.karyawanid='".$karyawanid."' and a.nomor<>'".$notransaksi."' and a.tanggalselesai<'".$tanggalmulai."'
			order by a.tanggalselesai desc";
	$qres=mysql_query($sstr);
	$no=0;
	$tbiaya=0;
	while($dbar=mysql_fetch_object($qres)){
		$no+=1;
		$pdf->SetX(10);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(6,5,$no,1,0,'C');
		$pdf->Cell(21,5,$dbar->kategori,1,0,'L');
		$pdf->Cell(65,5,$dbar->judultraining,1,0,'L');
		//$pdf->Cell(34,5,tanggalnormal($dbar->tanggalmulai)." s/d ".tanggalnormal($dbar->tanggalselesai),1,0,'L');
		$pdf->Cell(17,5,tanggalnormal($dbar->tanggalmulai),1,0,'C');
		$pdf->Cell(17,5,tanggalnormal($dbar->tanggalselesai),1,0,'C');
		$pdf->Cell(42,5,$dbar->penyelenggara,1,0,'L');
		$pdf->Cell(22,5,number_format($dbar->biaya,2),1,1,'R');
		$tbiaya+=$dbar->biaya;
	}
	$pdf->SetX(10);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(168,5,strtoupper('Total Biaya Training'),1,0,'C');
	$pdf->Cell(22,5,number_format($tbiaya,2),1,1,'R');

	$pdf->Ln();
	$pdf->SetX(10);
	$getY=$pdf->GetY();
	if($getY>=244){
		$tx=(279-$getY)/5;
		for ($x = 1; $x <= $tx; $x++) {
			$pdf->Cell(45,5,'',0,1,'C');
		}
	}
	$getX=$pdf->GetX();
	$getY=$pdf->GetY();
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(45,5,'Mengusulkan, Dept.Head/Manager',1,'C');
	$pdf->SetXY($getX+45,$getY);
	$pdf->MultiCell(45,5,'Mengetahui,   Div.Head/GM',1,'C');
	$pdf->SetXY($getX+90,$getY);
	$pdf->Cell(100,5,'Menyetujui',1,1,'C');
	$pdf->SetXY($getX+90,$getY+5);
	$pdf->Cell(50,5,'Direktur Terkait',1,0,'C');
	$pdf->SetXY($getX+140,$getY+5);
	$pdf->Cell(50,5,'Direktur HR & GS',1,1,'C');
	$pdf->Cell(45,20,'',1,0,'C');
	$pdf->Cell(45,20,'',1,0,'C');
	$pdf->Cell(50,20,'',1,0,'C');
	$pdf->Cell(50,20,'',1,1,'C');
	$pdf->Cell(45,5,'',1,0,'C');
	$pdf->Cell(45,5,'',1,0,'C');
	$pdf->Cell(50,5,'',1,0,'C');
	$pdf->Cell(50,5,'',1,1,'C');
	//if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
	//	$pdf->Cell(60,5,$_SESSION['lang']['direktur'],'',0,'C');
	//}else{
	//	$pdf->Cell(60,5,'GM','',0,'C');
	//}
	//footer================================
	$pdf->Ln();		
	$pdf->Output();
?>
