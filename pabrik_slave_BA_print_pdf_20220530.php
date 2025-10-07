<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');

$kodeorg=$_GET['kodeorg'];
$notransaksi=$_GET['notransaksi'];

$str="select a.kodeorganisasi,a.namaorganisasi,a.alamat,a.wilayahkota,a.telepon from ".$dbname.".organisasi a where a.kodeorganisasi in 
	(select b.induk from ".$dbname.".log_spkht c left join ".$dbname.".organisasi b on c.kodeorg=b.kodeorganisasi where c.kodeorg='".$kodeorg."' and c.notransaksi='".$notransaksi."')";
//exit('Warning: '.$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$namapt=$bar->namaorganisasi;
	$kodept=$bar->kodeorganisasi;
	$alamatpt=$bar->alamat;
	$teleponpt=$bar->telepon;
	//$_SESSION['empl']['ptpdf']=$bar->namaorganisasi;
}

$str2="select kodeorganisasi,namaorganisasi,alamat,wilayahkota from ".$dbname.".organisasi  where kodeorganisasi='".$kodeorg."'";	
$res2=mysql_query($str2);
while($bar2=mysql_fetch_object($res2)){
	$namaorg=$bar2->namaorganisasi;
	$alamatorg=$bar2->alamat;
}
//exit('Warning: '.$alamatpt);

//create Header
class PDF extends FPDF{
	function Header(){
		global $kodept;
		global $namapt;
		global $alamatpt;
		global $teleponpt;
		global $namaorg;
		global $alamatorg;

		$path='images/logo.jpg';
		$this->Image($path,12,2,0,30);
		if($kodept=='CKS'){
			$path2='images/logo_cks.jpg';
			$this->Image($path2,172,2,0,30);
		}elseif($kodept=='MPA'){
			$path2='images/logo_mpa.jpg';
			$this->Image($path2,172,2,0,30);
		}
		$this->SetFont('Arial','B',12);
		$this->SetFillColor(255,255,255);
		$this->SetXY(42,5);
		//$this->SetXY(23,22);
		//$this->Cell(177,5,$_SESSION['empl']['ptpdf'],0,1,'R');	 
		$this->Cell(150,5,$namapt,0,1,'L');
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
		$this->Cell(145,5,': '.$alamatorg,0,1,'L');
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

$str="select a.notransaksi,b.tanggal,b.kodeorg,j.namaorganisasi,j.wilayahkota,f.namacustomer,c.kodetimbangan,c.namasupplier,a.hasilkerjarealisasi
			,a.jumlahrealisasi*h.nilai/b.nilaikontrak as ppn,a.jumlahrealisasi*i.nilai/b.nilaikontrak as pph,a.jumlahrealisasi
			,b.keterangan as nospp,d.nokontrak,g.franco_name,k.millcode,l.namaorganisasi as unit,l.wilayahkota as kotawilayah
		from ".$dbname.".log_baspk a 
		LEFT JOIN ".$dbname.".log_spkht b on b.notransaksi=a.notransaksi
		LEFT JOIN ".$dbname.".log_5supplier c on c.supplierid=b.koderekanan
		LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman d on d.nodo=b.keterangan
		LEFT JOIN ".$dbname.".pmn_kontrakjual e on e.nokontrak=d.nokontrak
		LEFT JOIN ".$dbname.".pmn_4customer f on f.kodecustomer=e.koderekanan
		LEFT JOIN ".$dbname.".pmn_5franco g on g.id_franco=d.tempatpenyerahan
		LEFT JOIN ".$dbname.".log_spk_tax h on h.kodeorg=b.kodeorg and h.notransaksi=a.notransaksi and h.noakun like '1%'
		LEFT JOIN ".$dbname.".log_spk_tax i on i.kodeorg=b.kodeorg and i.notransaksi=a.notransaksi and i.noakun like '2%'
		LEFT JOIN ".$dbname.".organisasi j on j.kodeorganisasi=b.kodeorg
		LEFT JOIN (select DISTINCT nosipb,kodecustomer,millcode from ".$dbname.".pabrik_timbangan) k on k.nosipb=b.keterangan and k.kodecustomer=c.kodetimbangan
		LEFT JOIN ".$dbname.".organisasi l on l.kodeorganisasi=k.millcode
		where b.kodeorg='".$kodeorg."' and a.notransaksi='".$notransaksi."'
		";	
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$notransaksi=$bar->notransaksi;
	$tanggal=$bar->tanggal;
	$kodeorg=$bar->kodeorg;
	$namaorg=$bar->namaorganisasi;
	$wilayah=$bar->wilayahkota;
	$namacustomer=$bar->namacustomer;
	$namasupplier=$bar->namasupplier;
	$hasilkerjarealisasi=$bar->hasilkerjarealisasi;
	$ppn=$bar->ppn;
	$pph=$bar->pph;
	$jumlahrealisasi=$bar->jumlahrealisasi;
	$nospp=$bar->nospp;
	$nokontrak=$bar->nokontrak;
	$franco_name=$bar->franco_name;
	$unit=$bar->unit;
	$kota=$bar->kotawilayah;
}

$str2=explode("_",$nokontrak);
$kmdt=explode("/",$str2[1]);

$arrtrp = explode(",",$namasupplier);
$arrtrp[0]=ucwords(strtolower($arrtrp[0])).",";
$arrtrp[1]=trim($arrtrp[1]);
$arrtrp[2]=trim($arrtrp[2]);
$namasupplier=implode(" ",$arrtrp);


	if($kmdt[0]=='CPO'){
		$komoditi='CPO';
	}elseif($kmdt[0]=='KER'){
		$komoditi='Kernel';
	}elseif($kmdt[0]=='CKG'){
		$komoditi='Cangkang';
	}else{
		$komoditi='';
	}

$namahari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum'at","Sabtu");
$namabulan = array("Januari","Pebruari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$hari=$namahari[date('N', $tanggal)];
$bln=substr($tanggal,5,2)+0;
$namatgl=terbilang(substr($tanggal,8,2),2);
$namabln=$namabulan[$bln-1];
$namathn=terbilang(substr($tanggal,0,4),2);

$textBA1 ='Pada hari ini '.$hari.', tanggal '.$namatgl.' bulan '.$namabln.' tahun '.$namathn.' ('.tanggalnormal($tanggal).'), telah selesai dilakukan ';
$textBA1.='pengiriman '.$komoditi.' dari '.$namapt.' Unit '.$unit.' '.ucwords(strtolower($kota)).' kepada :';
$textBA2 ='Demikian Berita Acara Transport ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.';

$pdf=new PDF('P','mm','A4');
	$pdf->SetFont('Arial','B',14);
	$pdf->AddPage();
	$pdf->SetY(40);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255); 
    $pdf->Cell(170,5,'BERITA ACARA TRANSPORT '.strtoupper($komoditi),0,1,'C');
	$pdf->SetX(20);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(170,5,'Nomor : '.$notransaksi,0,1,'C');	

	$pdf->Ln();
	$pdf->Ln();	
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA1,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['Pembeli'],0,0,'L');	
	$pdf->Cell(140,5," : ".$namacustomer,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['transporter'],0,0,'L');	
	$pdf->Cell(140,5," : ".$namasupplier,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,'No. SPP',0,0,'L');	
	$pdf->Cell(140,5," : ".$nospp,0,1,'L');	
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['NoKontrak'],0,0,'L');	
	$pdf->Cell(140,5," : ".$nokontrak,0,1,'L');
	$pdf->SetX(20);	
	$pdf->Cell(25,5,$_SESSION['lang']['tujuan'],0,0,'L');	
	$pdf->Cell(140,5," : ".$franco_name,0,1,'L');	
	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->Cell(170,5,'Dengan jumlah pengiriman '.$komoditi.' yang dilaksanakan menurut perincian sebagai berikut :',0,1,'L');

	$pdf->Ln();
	$pdf->SetX(20);
    $pdf->SetTextColor(0, 0, 0); 
    $pdf->SetFillColor(36, 140, 129); 
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(60,7,ucwords(strtoupper($_SESSION['lang']['beratBersih'])),1,0,'C');
	$pdf->Cell(50,7,ucwords(strtoupper($_SESSION['lang']['hargasatuan'])),1,0,'C');	
	$pdf->Cell(60,7,ucwords(strtoupper($_SESSION['lang']['total'])),1,1,'C');	
	$pdf->SetX(20);	
	$pdf->Cell(60,7,'(Kg)',1,0,'C');
	$pdf->Cell(50,7,'(Rp)',1,0,'C');	
	$pdf->Cell(60,7,'(Rp)',1,1,'C');
	$pdf->SetX(20);	
    $pdf->SetTextColor(0, 0, 0); 
	$pdf->SetFillColor(0, 0, 0); 
	$pdf->Cell(60,7,number_format($hasilkerjarealisasi,0,'.',','),1,0,'R');
	$pdf->Cell(50,7,number_format($jumlahrealisasi/$hasilkerjarealisasi,2,'.',','),1,0,'R');	
	$pdf->Cell(60,7,number_format($jumlahrealisasi,2,'.',','),1,1,'R');
	$pdf->SetX(20);	
	$pdf->Cell(60,7,'PPN 10%',1,0,'L');
	$pdf->Cell(50,7,'',1,0,'L');	
	$pdf->Cell(60,7,number_format($ppn,2,'.',','),1,1,'R');
	$pdf->SetX(20);	
	$pdf->Cell(60,7,'PPh 23 - 2%',1,0,'L');
	$pdf->Cell(50,7,'',1,0,'L');	
	$pdf->Cell(60,7,'('.number_format($pph,2,'.',',').')',1,1,'R');
	$pdf->SetX(20);	
	$pdf->Cell(60,7,'GRAND TOTAL',1,0,'L');
	$pdf->Cell(50,7,'',1,0,'L');	
	$pdf->Cell(60,7,number_format($jumlahrealisasi+$ppn-$pph,2,'.',','),1,1,'R');

	$pdf->Ln();
	$pdf->SetX(20);	
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(170,5,$textBA2,0,'J');
	$pdf->Ln();
	$pdf->SetX(20);	
	if(strstr(strtoupper($kodeorg),'HO')){
		$pdf->Cell(160,5,ucwords(strtolower($wilayah)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}else{
		$pdf->Cell(160,5,ucwords(strtolower($kota)).', '.substr($tanggal,8,2).' '.$namabln.' '.substr($tanggal,0,4),0,1,'L');
	}
	$pdf->Ln();
	//$pdf->Ln();
	if(strstr(strtoupper($kodeorg),'HO')){
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",'',0,'C');
		$pdf->Cell(80,5,$_SESSION['lang']['diperiksa'].",",'',0,'C');			
		$pdf->Cell(60,5,$_SESSION['lang']['transporter'].",",'',0,'C');
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();
		$pdf->SetX(20);  
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Cell(80,5,'(                           )','',0,'C');
		$pdf->Cell(60,5,'(                           )','',0,'C');
		$pdf->Ln();
		$pdf->SetX(20);  
		$pdf->Cell(40,5,'Admin','',0,'C');
		$pdf->Cell(80,5,'Heaf Of Operation','',0,'C');
		$pdf->Cell(60,5,$namasupplier,'',0,'C');
	}else{
		$pdf->SetX(20);
		$pdf->Cell(40,5,$_SESSION['lang']['dibuat'].",",'',0,'C');
		$pdf->Cell(80,5,$_SESSION['lang']['diperiksa'].",",'',0,'C');			
		$pdf->Cell(60,5,$_SESSION['lang']['transporter'].",",'',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(20);  
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Cell(60,5,'(                           )','',0,'C');
		$pdf->Ln();
		$pdf->SetX(20);  
		$pdf->Cell(40,5,'Admin','',0,'C');
		$pdf->Cell(40,5,'Mill Manager','',0,'C');
		$pdf->Cell(40,5,'ROA Manager','',0,'C');
		$pdf->Cell(60,5,$namasupplier,'',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(80);
		$pdf->Cell(40,5,$_SESSION['lang']['disetujui'].",",'',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetX(80);  
		$pdf->Cell(40,5,'(                           )','',0,'C');
		$pdf->Ln();
		$pdf->SetX(80);  
		$pdf->Cell(40,5,'General Manager','',0,'C');
	}
	//footer================================
    $pdf->Ln();		
	$pdf->Output();
?>
