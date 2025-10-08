<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

/*echo "<pre>";
print_r($_GET);
echo "</pre>";*/
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whKar);
$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik',$whKar);


//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $conn;
	global $dbname;
    global $userid;
	global $notransaksi;
	global $kodevhc;
	global $posting;
	
			$test=explode(',',$_GET['column']);
			$notransaksi=$test[0];
			$kodevhc=$test[1];
			$str="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' and kodevhc='".$kodevhc."'";
			//echo $str;exit();
			$res=mysql_query($str);
			$bar=mysql_fetch_object($res);
			$posting=$bar->posting;	
			
			//ambil nama pt
			   $str1="select * from ".$dbname.".organisasi where induk='MHO' and tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'"; 
			   $res1=mysql_query($str1);
			   while($bar1=mysql_fetch_object($res1))
			   {
			   	 $namapt=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   }    
	   $sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->updateby."'";
	   $query2=mysql_query($sql2) or die(mysql_error());
	   $res2=mysql_fetch_object($query2);
	   
	   $sql5="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->postingby."'";
	   $query5=mysql_query($sql5) or die(mysql_error());
	   $res5=mysql_fetch_object($query5);
	   
	   $sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
	   $query3=mysql_query($sql3) or die(mysql_error());
	   $res3=mysql_fetch_object($query3); 
	
		$path='images/logo.jpg';
	    $this->Image($path,15,3,20);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);
		$this->SetY(5);
		$this->SetX(40);   
	    $this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(40); 		
	    $this->MultiCell(150,5,$alamatpt,0,1,'L');	
		$this->SetX(40); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');
		$this->Ln();
		$this->SetFont('Arial','U',15);
		$this->SetY(40);
		$this->Cell(190,5,$_SESSION['lang']['laporanPenggunaanKomponen'],0,1,'C');		
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,27,200,27);	
		/*$this->SetY(45);
	    $this->SetFont('Arial','',9);		
		$this->Cell(10,4,"No.",0,0,'L'); 
		$this->Cell(20,4,": ".$bar->nopo,0,1,'L'); 
		$this->SetY(70);
		$this->SetX(145);
		$this->Cell(20,4,"Tanggal PO.",0,0,'L'); 
		$this->Cell(20,4,": ".tanggalnormal($bar->tanggal),0,1,'L'); */
		//$this->Cell(40,4,": ".$kodegudang,0,1,'L'); 
		$this->Ln();
		$this->SetFont('Arial','',9); 
		$this->Cell(35,4,$_SESSION['lang']['notransaksi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$bar->notransaksi,0,1,'L'); 				
		$this->Cell(35,4,$_SESSION['lang']['tanggalmasuk'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,tanggalnormal($bar->tanggal),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['tanggalkeluar'],0,0,'L');
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,tanggalnormal($bar->tanggalkeluar),0,1,'L'); 
		$this->Cell(35,4,$_SESSION['lang']['namaorganisasi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$res3->namaorganisasi." [".$bar->kodeorg."]",0,1,'L'); 		  
		$this->Cell(35,4,$_SESSION['lang']['kodevhc'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$bar->kodevhc,0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['noreferensi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$bar->noreferensi,0,1,'L');
		$this->Cell(35,4,$_SESSION['lang']['downtime'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$bar->downtime." Jam",0,1,'L'); 
                
                $this->Cell(35,4,'Km/Hm Masuk',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,number_format($bar->kmmasuk,2),0,1,'L'); 
                $this->Cell(35,4,'Km/Hm Keluar',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,number_format($bar->kmkeluar,2),0,1,'L'); 
                
		$this->Cell(35,4,$_SESSION['lang']['descDamage'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->MultiCell(150,4,$bar->kerusakan,0,1,'J'); 
                $this->Cell(35,4,'Alasan Terlambat',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->MultiCell(150,4,$bar->terlambat,0,1,'J'); 
		$this->Cell(35,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
		$this->Cell(40,4,$res2->namakaryawan,0,1,'L');  
		$this->Cell(35,4,$_SESSION['lang']['posted'],0,0,'L');
                $this->Cell(5,4,':',0,0,'L'); 
		setIt($res5->namakaryawan,'');
		$this->Cell(40,4,$res5->namakaryawan,0,1,'L');  
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
			
//ambil kelengkapan

	$pdf->Ln();
	if($posting<1)
	{
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,5,$_SESSION['lang']['belumposting'],0,0,'C');
		$pdf->Ln();
	}
        
	$str="select * from ".$dbname.".vhc_penggantiandt   where notransaksi='".$notransaksi."'"; //echo $str;exit();
	$re=mysql_query($str);
	$rCek=mysql_num_rows($re);
	if($rCek>0)
	{
	
            $pdf->SetFont('Arial','B',9);	
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'L',1);
            $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
            $pdf->Cell(65,5,$_SESSION['lang']['namabarang'],1,0,'C',1);		
            $pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
            $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
            $pdf->Cell(62,5,$_SESSION['lang']['keterangan'],1,1,'C',1);	
            /*$pdf->Cell(20,5,$_SESSION['lang']['kurs'],1,0,'C',1);
            $pdf->Cell(25,5,$_SESSION['lang']['hargasatuan'],1,0,'C',1);
            $pdf->Cell(25,5,'Total',1,1,'C',1);*/
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',9);


                $no=0;
                while($res=mysql_fetch_object($re))
                {
                        $no+=1;
                        $kodebarang=$res->kodebarang;
                        $sbrg="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."' ";
                        $qbrg=mysql_query($sbrg) or die(mysql_error());
                        $rbrg=mysql_fetch_assoc($qbrg);

                        $pdf->Cell(8,5,$no,1,0,'L',1);
                        $pdf->Cell(30,5,$kodebarang,1,0,'L',1);
                        $pdf->Cell(65,5,substr($rbrg['namabarang'],0,35),1,0,'L',1);	
                        $pdf->Cell(15,5,number_format($res->jumlah,2),1,0,'R',1);	
                        $pdf->Cell(15,5,$rbrg['satuan'],1,0,'C',1);	
                        $pdf->Cell(62,5,$res->keterangan,1,1,'L',1);

                }
	}
        
        
        $pdf->Ln();
        
        $iKar="select * from ".$dbname.".vhc_penggantiandt_karyawan   where notransaksi='".$notransaksi."'"; //echo $str;exit();
	$nKar=mysql_query($iKar) or die (mysql_error($conn));
	$wKar=mysql_num_rows($nKar);
	if($wKar>0)
	{
            $pdf->SetFont('Arial','B',9);	
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'L',1);
            $pdf->Cell(30,5,$_SESSION['lang']['nik'],1,0,'C',1);
            $pdf->Cell(65,5,$_SESSION['lang']['namakaryawan'],1,1,'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',9);
                $no=0;
                while($dKar=  mysql_fetch_assoc($nKar))
                {
                    $whKar="karyawanid='".$dKar['karyawanid']."'";
                    $no+=1;  
                    $pdf->Cell(8,5,$no,1,0,'L',1);
                    $pdf->Cell(30,5,$nikKar[$dKar['karyawanid']],1,0,'L',1);
                    $pdf->Cell(65,5,$nmKar[$dKar['karyawanid']],1,1,'L',1);

                }
	}
        
	
	$pdf->Output();
?>
