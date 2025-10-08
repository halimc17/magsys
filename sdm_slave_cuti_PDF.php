<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
$karyawan=checkPostGet('karyawan','');
$method=checkPostGet('method','');

switch($method){
	case 'pdf':
	//create Header
	class PDF extends FPDF
	{
	function Header()
	{
        global $conn;
        global $dbname;
        global $kodeorg;
        global $periode;
        global $karyawan;

		$sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
                        $qInduk=mysql_query($sInduk) or die(mysql_error());
                        $rInduk=mysql_fetch_assoc($qInduk);

                  // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
                   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'"; 
                   $res1=mysql_query($str1);
                   while($bar1=mysql_fetch_object($res1))
                   {
                         $nama=$bar1->namaorganisasi;
                         $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                         $telp=$bar1->telepon;				 
                   }
		$optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$optNmKary=makeOption($dbname,'datakaryawan','nik,namakaryawan');
		$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
		$qOrg=mysql_query($sOrg) or die(mysql_error());
		$rOrg=mysql_fetch_assoc($qOrg);
		$path='images/logo.jpg';
                $this->Image($path,15,10,20);	
                $this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);	
                $this->SetX(40);   
                $this->Cell(60,5,$nama,0,1,'L');	 
                $this->SetX(40); 
                $this->MultiCell(150, 5, $alamatpt, 0);
				//$this->Cell(60,5,$alamatpt,0,1,'L');	
                $this->SetX(40); 			
                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                $this->Ln();
                $this->SetFont('Arial','B',8); 
                $this->Cell(20,5,'','',1,'L');
                // $this->Cell(20,5,$nama,'',1,'L');
        $this->SetFont('Arial','',8);
        //$this->Line(10,30,200,30);	
        $akhirY=$this->GetY()-5;
                $this->Line(10,$akhirY,200,$akhirY);	
                $akhirYline=$this->GetY();
                $this->SetY($akhirYline);
		$this->Cell(15,5,$_SESSION['lang']['unit'],'',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(175,5,$kodeorg." - ".$optNmOrg[$kodeorg],'',1,'L');	
		
		$this->Cell(15,5,$_SESSION['lang']['periode'],'',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(175,5,$periode,'',1,'L');
		
		$this->Cell(15,5,$_SESSION['lang']['karyawan'],'',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(175,5,$karyawan.' - '.$optNmKary[$karyawan],0,1,'L');		
		$this->Ln();
    }
	
	function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

	}

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->Ln();
$pdf->SetFont('Arial','U',10);
$pdf->SetY(55);
$pdf->Cell(190,5,strtoupper($_SESSION['lang']['list']." ".$_SESSION['lang']['cuti']),0,1,'C');	
$pdf->Ln();	
$pdf->SetFont('Arial','B',8);	
$pdf->SetFillColor(220,220,220);
$pdf->Cell(8,5,'No',1,0,'L',1);
$pdf->Cell(10,5,$_SESSION['lang']['unit'],1,0,'C',1);	
$pdf->Cell(15,5,'NIK',1,0,'C',1);	
$pdf->Cell(60,5,$_SESSION['lang']['namakaryawan'],1,0,'C',1);	
$pdf->Cell(17,5,$_SESSION['lang']['masuk'],1,0,'C',1);
$pdf->Cell(11,5,$_SESSION['lang']['periode'],1,0,'C',1);
$pdf->Cell(17,5,$_SESSION['lang']['dari'],1,0,'C',1);
$pdf->Cell(17,5,$_SESSION['lang']['sampai'],1,0,'C',1);
$pdf->Cell(12,5,$_SESSION['lang']['hakcuti'],1,0,'C',1);
$pdf->Cell(12,5,$_SESSION['lang']['diambil'],1,0,'C',1);
$pdf->Cell(12,5,$_SESSION['lang']['sisa'],1,1,'C',1);
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',8);
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where b.lokasitugas not like '%HO'
		   and a.kodeorg='".$kodeorg."' 
		   and a.periodecuti='".$periode."' 
		   and b.nik like '%".$karyawan."%'";
}else{
	$str="select a.*,b.namakaryawan,b.tanggalmasuk,b.nik
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where a.kodeorg='".$kodeorg."' 
		   and a.periodecuti='".$periode."' 
		   and b.nik like '%".$karyawan."%'";
}

$re=mysql_query($str);
$no=0;
while($res=mysql_fetch_assoc($re))
{
	$height = 5;
	//$test=$pdf->GetY();
	//$awalY=$pdf->GetY();
	//$pdf->SetX(1000);
	//$akhirYakun=$pdf->GetY();
	//$akhirY = $akhirYakun;
	//$height2=$akhirY-$awalY;
	//$pdf->SetY($awalY);
	$height2 = 5;
	$no+=1;
	// $pdf->SetY(100);
	$pdf->Cell(8,$height2,$no,1,0,'R',1);
	$pdf->Cell(10,$height2,$res['kodeorg'],1,0,'C',1);
	$pdf->Cell(15,$height2,$res['nik'],1,0,'C',1);	
	$pdf->Cell(60,$height2,$res['namakaryawan'],1,0,'L',1);
	$pdf->Cell(17,$height2,tanggalnormal($res['tanggalmasuk']),1,0,'C',1);
	$pdf->Cell(11,$height2,$res['periodecuti'],1,0,'C',1);
	$pdf->Cell(17,$height2,tanggalnormal($res['dari']),1,0,'C',1);
	$pdf->Cell(17,$height2,tanggalnormal($res['sampai']),1,0,'C',1);
	$pdf->Cell(12,$height2,number_format($res['hakcuti']),1,0,'R',1);
	$pdf->Cell(12,$height2,number_format($res['diambil']),1,0,'R',1);
	$pdf->Cell(12,$height2,number_format($res['sisa']),1,1,'R',1);
}
$pdf->Output();
	
	default:
	break;
}
?>