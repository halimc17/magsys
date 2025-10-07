<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');

$kodeorg=$_GET['kodeorg'];
$tanggal=$_GET['tanggal'];
$jenis	=$_GET['jenis'];

$str="select a.kodeorganisasi,a.namaorganisasi,a.alamat,a.wilayahkota,a.telepon,a.induk
	,b.namaorganisasi as namapt,b.alamat as alamatpt,b.wilayahkota as wilayahkotapt,b.telepon as teleponpt
	from ".$dbname.".organisasi a 
	left join ".$dbname.".organisasi b on b.kodeorganisasi=a.induk
	where a.kodeorganisasi='".substr($kodeorg,0,4)."'";
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
		$this->Cell(18,5,'Estate',0,0,'L');
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

$where="";
if($kodeorg!=''){
	$where.=" and left(a.kodeorg,6)='".substr($kodeorg,0,6)."'";
}
if($tanggal!=''){
	$where.=" and a.tanggal='".$tanggal."'";
}
if($jenis!=''){
	//$where.=" and a.jenis='".$jenis."'";
}
if($jenis=='Afkir'){
	$sebab="terdapat TBS Afkir";
	$judul="BERITA ACARA TBS AFKIR";
	$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".kebun_adjpanen a 
		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.jenis='Afkir' ".$where." 
		order by a.tanggal,a.waktu,a.kodeorg,a.jenis";
}else if($jenis=='Borongan'){
	$sebab="terdapat TBS Borongan";
	$judul="BERITA ACARA TBS BORONGAN";
	$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".kebun_adjpanen a 
		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.jenis='Borongan' ".$where." 
		order by a.tanggal,a.waktu,a.kodeorg,a.jenis";
}else{
	$sebab="terjadi pencurian TBS";
	$judul="BERITA ACARA PENCURIAN TBS";
	$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".kebun_adjpanen a 
		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.jenis<>'Afkir' and a.jenis<>'Borongan' ".$where." 
		order by a.tanggal,a.waktu,a.kodeorg,a.jenis";
}
//exit('Warning: '.$str);
$res=mysql_query($str);
$no=0;
while($bar=mysql_fetch_object($res)){
	$no+=1;
	$kodeblok[$no]=$bar->kodeorg;
	$namablok[$no]=$bar->namablok;
	//$tanggal[$no]=$bar->tanggal;
	$waktu[$no]=substr($bar->waktu,0,5);
	$jenis2[$no]=$bar->jenis;
	$janjang[$no]=$bar->janjang;
	$kg[$no]=$bar->kg;
	$supirlangsir[$no]=$bar->supirlangsir;
	$keterangan[$no]=$bar->keterangan;
	$catatan.=$bar->catatan."
	";
}

$namahari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum'at","Sabtu");
$namabulan = array("Januari","Pebruari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$hari=$namahari[date('N', $tanggal)];
$bln=substr($tanggal,5,2)+0;
$namatgl=terbilang(substr($tanggal,8,2),2);
$namabln=$namabulan[$bln-1];
$namathn=terbilang(substr($tanggal,0,4),2);

$textBA1 ='Pada hari ini '.$hari.', tanggal '.$namatgl.' bulan '.$namabln.' tahun '.$namathn.' ('.tanggalnormal($tanggal).'), diperkirakan ';
$textBA1.='pada pukul '.$waktu[1].' telah '.$sebab.' dengan keterangan sebagai berikut : ';
$textBA2 ='Catatan tambahan lainnya :';
$textBA3 ='Demikian Berita Acara ini dibuat dengan sebenarnya untuk diketahui bersama dan sebagai kelengkapan administrasi kebun.';

$pdf=new PDF('P','mm','A4');
	$pdf->SetFont('Arial','B',14);
	$pdf->AddPage();
	$pdf->SetY(40);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255); 
	$pdf->Cell(170,5,$judul,0,1,'C');
	$pdf->SetX(20);
	$pdf->SetFont('Arial','B',12);
	$pdf->Ln();	
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA1,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',10);
    $pdf->Cell(7,5,'No',1,0,'C');
    $pdf->Cell(17,5,'Divisi',1,0,'C');
    $pdf->Cell(25,5,'Blok',1,0,'C');
    $pdf->Cell(15,5,'Janjang',1,0,'C');
    $pdf->Cell(22,5,'Tonage (Kg)',1,0,'C');
    $pdf->Cell(30,5,'Driver Langsir',1,0,'C');
    $pdf->Cell(55,5,'Keterangan',1,1,'C');
	for ($x = 1; $x <= $no; $x++) {
		$pdf->SetX(20);	
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(07,5,$x,1,0,'C');
		$pdf->Cell(17,5,substr($kodeblok[$x],0,6),1,0,'C');	
		$pdf->Cell(25,5,$namablok[$x],1,0,'L');	
		$pdf->Cell(15,5,number_format($janjang[$x],0,'.',','),1,0,'R');	
		$pdf->Cell(22,5,number_format($kg[$x],0,'.',','),1,0,'R');	
		$pdf->Cell(30,5,$supirlangsir[$x],1,0,'L');	
		$pdf->Cell(55,5,$jenis2[$x],1,1,'L');
		$pdf->SetX(20);	
		$pdf->Cell(07,5,'',1,0,'C');
		$pdf->Cell(17,5,'',1,0,'C');	
		$pdf->Cell(25,5,'',1,0,'L');	
		$pdf->Cell(15,5,'',1,0,'R');	
		$pdf->Cell(22,5,'',1,0,'R');	
		$pdf->Cell(30,5,'',1,0,'L');	
		$pdf->Cell(55,5,$keterangan[$x],1,1,'L');
	}
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA2,0,'J');
//	for ($x = 1; $x <= $no; $x++) {
		$pdf->SetX(20);
//		$pdf->Cell(5,5,$x,0,0,'L');
		$pdf->MultiCell(150,5,$catatan,0,'J');
//	}
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
		$pdf->Cell(40,5,'Field Assitent',1,0,'C');
		$pdf->Cell(40,5,'Head Assitent',1,0,'C');
		$pdf->Cell(40,5,'Chief Security',1,0,'C');
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
		$pdf->Cell(40,5,'Field Assitent',1,0,'C');
		$pdf->Cell(40,5,'Head Assitent',1,0,'C');
		$pdf->Cell(40,5,'Chief Security',1,0,'C');
	}
	$pdf->Ln();
	//footer================================
    $pdf->Ln();		
	$pdf->Output();
?>
