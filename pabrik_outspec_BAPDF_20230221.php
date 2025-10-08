<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');

$millcode=$_GET['millcode'];
$notransaksi=$_GET['notransaksi'];
$kodebarang=$_GET['kodebarang'];

$str="select a.kodeorganisasi,a.namaorganisasi,a.alamat,a.wilayahkota,a.telepon,a.induk
	,b.namaorganisasi as namapt,b.alamat as alamatpt,b.wilayahkota as wilayahkotapt,b.telepon as teleponpt
	from ".$dbname.".organisasi a 
	left join ".$dbname.".organisasi b on b.kodeorganisasi=a.induk
	where a.kodeorganisasi='".$millcode."'";
//exit('Warning: '.$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$namaorganisasi=$bar->namaorganisasi;
	$alamat=$bar->alamat;
	$wilayahkota=$bar->wilayahkota;
	$telepon=$bar->telepon;
	$kodept=$bar->induk;
	$namapt=$bar->namapt;
	$alamatpt=$bar->alamatpt;
	$wilayahkotapt=$bar->wilayahkotapt;
	$teleponpt=$bar->teleponpt;
}

//create Header
class PDF extends FPDF{
	function Header(){
		global $millcode;
		global $notransaksi;
		global $kodebarang;
		global $namaorganisasi;
		global $alamat;
		global $wilayahkota;
		global $telepon;
		global $kodept;
		global $namapt;
		global $alamatpt;
		global $wilayahkotapt;
		global $teleponpt;

		$path='images/logo.jpg';
		$this->Image($path,12,0,0,22);
		$path2='images/logo_aaa.jpg';
		if($kodept=='CKS'){
			$path2='images/logo_cks.jpg';
			$this->Image($path2,172,2,0,30);
		}elseif($kodept=='MPA'){
			$path2='images/logo_mpa.jpg';
			$this->Image($path2,172,2,0,30);
		}
		$this->SetFont('Arial','B',12);
		$this->SetFillColor(255,255,255);
		$this->SetXY(20,5);
		$this->Cell(170,5,$namapt,0,1,'C');
		$this->SetFont('Arial','',15);
		$this->Cell(175,5,'',0,1,'C');
		$this->SetXY(42,15);
		$this->SetFont('Arial','',9); 
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
		$this->Cell(18,5,'Mill',0,0,'L');
		$this->Cell(145,5,': '.$alamat,0,1,'L');
		$this->SetY(30);
		$this->SetX(163);
		//$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,32,200,32);	   
	}

	function Footer(){
		$this->SetY(-15);
		$this->SetFont('Arial','I',6);
		$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		//$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}
}

$where="True";
if($millcode!=''){
	$where.=" and a.millcode='".$millcode."'";
}
if($notransaksi!=''){
	$where.=" and a.notransaksi='".$notransaksi."'";
}
if($kodebarang!=''){
	$where.=" and a.kodebarang='".$kodebarang."'";
}
$str="select a.*,b.namabarang,e.namacustomer,f.namabarang as komoditi,g.namasupplier as pengangkut
		from ".$dbname.".pabrik_outspec a 
		left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
		left join ".$dbname.".pmn_kontrakjual d on d.nokontrak=a.nokontrak
		left join ".$dbname.".pmn_4customer e on e.kodecustomer=d.koderekanan
		left join ".$dbname.".log_5masterbarang f on f.kodebarang=a.kodebarangkirim
		left join ".$dbname.".log_5supplier g on g.kodetimbangan=a.customerkirim and a.customerkirim<>''
		where ".$where." 
		order by a.millcode,a.tanggal,a.notransaksi,a.kodebarang";
//exit('Warning: '.$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	//$millcode=$bar->millcode;
	//$notransaksi=$bar->notransaksi;
	//$kodebarang=$bar->kodebarang;
	$namabarang=$bar->namabarang;
	$noba=$bar->noba;
	$notiket=$bar->notiket;
	$alasan=$bar->alasan;
	$tglkirim=substr($bar->tglkirim,0,10);
	$nokontrakkirim=$bar->nokontrak;
	$nosipbkirim=$bar->nosipb;
	$nokendaraankirim=$bar->nokendaraankirim;
	$supirkirim=$bar->supirkirim;
	$beratmasukkirim=$bar->beratmasukkirim;
	$jammasukkirim=$bar->jammasukkirim;
	$beratkeluarkirim=$bar->beratkeluarkirim;
	$jamkeluarkirim=$bar->jamkeluarkirim;
	$beratbersihkirim=$bar->beratbersihkirim;
	$namacustomer=$bar->namacustomer;
	$komoditi=$bar->komoditi;
	$pengangkut=$bar->pengangkut;
}

$tanggal=$tglkirim;
$namahari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum'at","Sabtu");
$namabulan = array("Januari","Pebruari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$hari=$namahari[date('N', $tanggal)];
$bln=substr($tanggal,5,2)+0;
$namatgl=terbilang(substr($tanggal,8,2),2);
$namabln=$namabulan[$bln-1];
$namathn=terbilang(substr($tanggal,0,4),2);

$textBA1 ='Pada hari ini '.$hari.', tanggal '.$namatgl.' bulan '.$namabln.' tahun '.$namathn.' ('.tanggalnormal($tanggal).'), telah dilakukan ';
$textBA1.='penimbangan unit '.$komoditi.' dangan rincian sebagai berikut : ';
$textBA2 ='Transaksi penimbangan unit '.$komoditi.' dengan No. Tiket diatas dinolkan karena '.$alasan;
$textBA3 ='Demikian Berita Acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.';

$pdf=new PDF('P','mm','A4');
	$pdf->SetFont('Arial','B',14);
	$pdf->AddPage();
	$pdf->SetY(40);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255); 
    $pdf->Cell(170,5,'BERITA ACARA KOREKSI TIMBANG '.strtoupper($komoditi),0,1,'C');
	$pdf->SetX(20);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(170,5,'Nomor : '.$noba,0,1,'C');	
	$pdf->Ln();
	$pdf->Ln();	
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA1,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(170,5,$namapt,0,1,'C');
	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(25,5,'No. Tiket/Seri',0,0,'L');
	$pdf->Cell(15,5," : ".$notiket,0,0,'L');	
	$pdf->Cell(82,5,'Tgl.Cetak',0,0,'R');	
	$pdf->Cell(48,5," : ".$tanggal,0,1,'L');	
	$pdf->SetX(20);
	$pdf->Cell(25,5,'Pembeli',0,0,'L');	
	$pdf->Cell(140,5," : ".$namacustomer,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['NoKontrak'],0,0,'L');	
	$pdf->Cell(140,5," : ".$nokontrakkirim,0,1,'L');
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'No. SPP',0,0,'L');	
	$pdf->Cell(140,5," : ".$nosipbkirim,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['komoditi'],0,0,'L');	
	$pdf->Cell(140,5," : ".$komoditi,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'Pengangkut',0,0,'L');	
	$pdf->Cell(140,5," : ".$pengangkut,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'No. Kend',0,0,'L');	
	$pdf->Cell(140,5," : ".$nokendaraankirim,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'Supir',0,0,'L');	
	$pdf->Cell(140,5," : ".$supirkirim,0,1,'L');	
	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'Timbang II',0,0,'L');	
	$pdf->Cell(3,5," : ",0,0,'L');
	$pdf->Cell(55,5,number_format($beratmasukkirim,0,'.',',').' Kg '.$jammasukkirim,0,1,'R');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'Timbang I',0,0,'L');	
	$pdf->Cell(3,5," : ",0,0,'L');
	$pdf->Cell(55,5,number_format($beratkeluarkirim,0,'.',',').' Kg '.$jamkeluarkirim,0,1,'R');	
	$pdf->SetX(20);	
	$pdf->Line(49, 145, 68, 145);	   
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'Berat Bersih',0,0,'L');	
	$pdf->Cell(3,5," : ",0,0,'L');
	$pdf->Cell(22,5,number_format($beratbersihkirim,0,'.',',').' Kg ',0,1,'R');	
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA2,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA3,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);
	if(strstr(strtoupper($millcode),'HO')){
		$pdf->Cell(170,5,ucwords(strtolower($wilayahkotapt)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}else{
		$pdf->Cell(170,5,ucwords(strtolower($wilayahkota)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}
	$pdf->Ln();
	//$pdf->Ln();
	if(strstr(strtoupper($kodeorg),'HO')){
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",'',0,'C');
		$pdf->Cell(80,5,$_SESSION['lang']['diperiksa'].",",'',0,'C');			
		$pdf->Cell(60,5,$_SESSION['lang']['diketahuioleh'].",",'',0,'C');
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();
		$pdf->SetX(20);  
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Cell(90,5,'(                           )','',0,'C');
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->Cell(40,5,'Admin','',0,'C');
		$pdf->Cell(90,5,'Heaf Of Operation','',0,'C');
		$pdf->Cell(40,5,'Direktur','',0,'C');
	}else{
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",'',0,'C');
		$pdf->Cell(90,5,$_SESSION['lang']['diperiksa'].",",'',0,'C');			
		$pdf->Cell(40,5,$_SESSION['lang']['diketahuioleh'].",",'',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->Cell(30,5,'(                       )','',0,'C');
		$pdf->Cell(35,5,'(                          )','',0,'C');
		$pdf->Cell(35,5,'(                          )','',0,'C');
		$pdf->Cell(35,5,'(                          )','',0,'C');
		$pdf->Cell(35,5,'(                          )','',0,'C');
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->Cell(30,5,'Lab. Admin','',0,'C');
		$pdf->Cell(35,5,'Lab. Mandor','',0,'C');
		$pdf->Cell(35,5,'Mill Manager','',0,'C');
		$pdf->Cell(35,5,'ROA Manager','',0,'C');
		$pdf->Cell(35,5,'General Manager','',0,'C');
		$pdf->Ln();
	}
	//footer================================
    $pdf->Ln();		
	$pdf->Output();
?>
