<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');
$nodok=$_GET['notransaksi'];

//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $conn;
	global $dbname;
	global $nodok;
    global $userid;
	global $posted;
	global $tanggal;
	global $gudangx;
	global $kodegudang;
	global $referensi;
	
		$pt='';
		$namapt='';
		$alamatpt='';
		$telp='';
		$kodegudang='';
		$status=0;
		$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$kodept=$bar->kodept;
			$kodegudang=$bar->kodegudang;
			$userid=$bar->user;
			$posted=$bar->postedby;
			$status=$bar->post;
			$tanggal=$bar->tanggal;
			$gudangx=$bar->gudangx;
			$referensi=$bar->notransaksireferensi;
	
			if($status==0)
			 $status='Not Confirm';
			else
			 $status='Confirmed'; 
			//ambil nama pt
			   
			   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
			   $res1=mysql_query($str1);
			   while($bar1=mysql_fetch_object($res1))
			   {
			   	 $namapt=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   } 
		}	
		$path='images/logo.jpg';
	    $this->Image($path,15,5,18);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);
		$this->SetY(5);
		$this->SetX(40);   
	    $this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(40); 		
	    $this->MultiCell(150,5,$alamatpt,0,'L');	
		$this->SetX(40); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->SetFont('Arial','',15);
		$this->SetY(30);		
	    $this->Cell(190,5,strtoupper($_SESSION['lang']['terimamutasi']),0,1,'C');
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,27,200,27);	
		$this->Ln();
	    $this->SetFont('Arial','',9);		
		$this->Cell(30,4,$_SESSION['lang']['sloc'],0,0,'L'); 
		$this->Cell(40,4,": ".$kodegudang,0,1,'L'); 
		$this->Cell(30,4,$_SESSION['lang']['docnum'],0,0,'L'); 
		$this->Cell(40,4,": ".$nodok,0,1,'L'); 				
		$this->Cell(30,4,$_SESSION['lang']['docstatus'],0,0,'L'); 
		$this->Cell(40,4,": ".$status,0,1,'L'); 		  
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}

/*
    print"<pre>";
	print_r($_SESSION);
	print"</pre>";
*/
	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
			
//ambil kelengkapan
//ambil supplier

     $hari=hari($tanggal,$_SESSION['language']);
	 $tanggal=tanggalnormal($tanggal);
	 $resc=str_replace("#DATE_REPARAM#",$hari.", ".$tanggal,$_SESSION['lang']['preterimamutasi']);
	 $resc=str_replace("#SLOC_PARAM#",$kodegudang,$resc);
	 $resc=str_replace("#SOURCE_PARAM#",$gudangx,$resc);
    $pdf->Ln();
    $pdf->Ln();	
    $pdf->MultiCell(170,5,$resc,0,'L');	
		$pdf->Cell(30,4,$_SESSION['lang']['noreferensi'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$referensi,0,1,'L'); 		
//    $pdf->Ln();
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
    $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
    $pdf->Cell(100,5,$_SESSION['lang']['namabarang'],1,0,'C',1);	
    $pdf->Cell(20,5,$_SESSION['lang']['satuan'],1,0,'C',1);		
    $pdf->Cell(20,5,$_SESSION['lang']['kuantitas'],1,1,'C',1);	
	$pdf->SetFillColor(255,255,255);
	    $pdf->SetFont('Arial','',9);
		
		$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$_GET['notransaksi']."'";
		$res=mysql_query($str);
		$no=0;
		while($bar=mysql_fetch_object($res))
		{
			$no+=1;
			
			$kodebarang=$bar->kodebarang;
			$satuan=$bar->satuan;
			$jumlah=$bar->jumlah;
		   $namabarang='';
		   $strv="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";	
		   $resv=mysql_query($strv);
		   while($barv=mysql_fetch_object($resv))
		   {
		   	$namabarang=$barv->namabarang;
		   }
			    $pdf->Cell(8,5,$no,1,0,'L',1);
			    $pdf->Cell(30,5,$kodebarang,1,0,'L',1);
			    $pdf->Cell(100,5,printSpecialChar($namabarang),1,0,'L',1);	
			    $pdf->Cell(20,5,$satuan,1,0,'L',1);	
				$pdf->Cell(20,5,number_format($jumlah,2,'.',','),1,1,'R',1);		
			    	   
		}
//footer================================
        $pdf->Ln();
//get user;
		$optUser=makeOption($dbname, 'user', 'karyawanid,namauser');
		$namakaryawan=namakaryawan($dbname,$conn,$userid);		
		$pdf->Cell(20,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$namakaryawan." / ".$optUser[$userid],0,1,'L'); 
//get posted by
       if($posted!='')
	      $posted2=namakaryawan($dbname,$conn,$posted);		
	   else
	      $posted2='';
	   	$pdf->Cell(20,4,$_SESSION['lang']['posted'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$posted2." / ".$optUser[$posted],0,1,'L');		
	$pdf->Output();
?>
