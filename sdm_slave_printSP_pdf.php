<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zMysql.php');
$nosp=$_GET['nosp'];

//=============
$str="select a.*,b.keterangan,c.*,d.namajabatan,e.wilayahkota as wilKaryawan from ".$dbname.".sdm_suratperingatan a 
	left join ".$dbname.".sdm_5jenissp b 
	on a.jenissp = b.kode 
	left join ".$dbname.".datakaryawan c
	on a.karyawanid = c.karyawanid 
	left join ".$dbname.".sdm_5jabatan d
	on c.kodejabatan = d.kodejabatan 
	left join ".$dbname.".organisasi e
	on c.kodeorganisasi = e.kodeorganisasi
	where nomor='".$nosp."'";	
$resHead=mysql_query($str);
$tmpBar=mysql_fetch_object($resHead);

//create Header
class PDF extends FPDF
{

        function Header()
        {
                global $conn;
                global $dbname;
				global $tmpBar;
				
				$strx="select b.namaorganisasi from ".$dbname.".datakaryawan a 
				left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi
				where a.karyawanid=".$tmpBar->karyawanid;
				$resOrg = fetchData($strx);
                 $this->SetFillColor(255,255,255); 
            $this->SetMargins(15,10,0);
                $path='images/logo.jpg';
            $this->Image($path,15,5,30);
				$this->SetFont('Arial','',6); 	
                $this->SetX(163);
				$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');
				$this->Ln();
        }



        // function Footer()
        // {
                // global $conn;
                // global $dbname;
            // $str1="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='KHO'";
               // $res1=mysql_query($str1);
               // while($bar1=mysql_fetch_object($res1))
               // {
                     // $namapt=$bar1->namaorganisasi;
                     // $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                     // $telp=$bar1->telepon;				 
               // }            
            // $this->SetY(-15);
            // $this->SetFont('Arial','I',8);
            // $this->Cell(160,5,$alamatpt.", Cc:".$telp,0,1,'L');
            // //$this->Cell(10,5,'Page '.$this->PageNo(),0,0,'C');
        // }

}

$resHead1=mysql_query($str);
  while($bar=mysql_fetch_object($resHead1))
  {

        //===================smbil nama karyawan
          $namakaryawan='';
          $strx="select a.namakaryawan,b.namajabatan,tipekaryawan,a.alamataktif from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
          where karyawanid=".$bar->karyawanid;

          $resx=mysql_query($strx);
          //echo mysql_error($conn);
          while($barx=mysql_fetch_object($resx))
          {
                $namakaryawan=$barx->namakaryawan;
                $jabatanybs=$barx->namajabatan;
                $tipex=$barx->tipekaryawan;
				$alamataktif=$barx->alamataktif;
          }

          $tanggal=tanggalnormal($bar->tanggal);
          $sampai=tanggalnormal($bar->sampai);
          $tipesp=$bar->jenissp;
          //====================ambil tipe untuk hal
          $ketHal='';
          $str="select keterangan from ".$dbname.".sdm_5jenissp where kode='".$tipesp."'";
          $rekx=mysql_query($str);
          while($barkx=mysql_fetch_object($rekx))
          {
                $ketHal=trim($barkx->keterangan);
          }
          //===============================

          $paragraf1=$bar->paragraf1;
          $pelanggaran=$bar->pelanggaran;
          $paragraf3=$bar->paragraf3;
          $paragraf4=$bar->paragraf4;
          $karyawanid=$bar->karyawanid;

          $penandatangan=$bar->penandatangan;
          $jabatan=$bar->jabatan;
          $tembusan1=$bar->tembusan1;
          $tembusan2=$bar->tembusan2;
          $tembusan3=$bar->tembusan3;
          $tembusan4=$bar->tembusan4;
          $verifikasi=$bar->verifikasi;
          $dibuat=$bar->dibuat;
          $jabatandibuat=$bar->jabatandibuat;
          $jabatanverifikasi=$bar->jabatanverifikasi;
  }
  
				// $this->SetFont('Arial','UB',16);
                // $this->SetFillColor(255,255,255);	
                // $this->SetX(50);   
                // $this->Cell(60,15,strtoupper($tmpBar->keterangan),0,1,'L');
        $pdf=new PDF('P','mm','A4');
        $pdf->SetFont('Arial','B',14);
        $pdf->AddPage();
        $pdf->SetY(40);
        $pdf->SetFillColor(255,255,255); 
        if($tmpBar->jenissp=='PHK'){
			$pdf->SetX(20);
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(170,5,$_SESSION['lang']['suratkeputusan'],0,0,'C');
			$pdf->Ln();
			$pdf->SetX(20);
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,5,'No. '.strtoupper($tmpBar->nomor),0,0,'C');
			$pdf->Ln();
			$pdf->Ln();
			$pdf->SetX(20);
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,5,$_SESSION['lang']['tentang'],0,0,'C');
			$pdf->Ln();
			$pdf->Ln();
			$pdf->SetX(20);
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,5,strtoupper($tmpBar->keterangan),0,0,'C');
			$pdf->Ln();
			$pdf->SetX(20);
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,5,"(".strtoupper($tmpBar->jenissp).")",0,0,'C');
			
		}else{
			$pdf->SetX(20);
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(170,5,strtoupper($tmpBar->keterangan),0,0,'C');
			$pdf->Ln();
			$pdf->SetX(20);
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,5,$tmpBar->nomor,0,0,'C');
		}
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		if($tipesp=='BAPP'){
		
		}else if($tipesp=='PHK'){
		
		}else if($tipesp=='SKR'){
		
		}else{
			// echo $tmpBar->jenissp;
			$pdf->SetX(20);
			$pdf->Cell(20,5,$_SESSION['lang']['kepada'],0,0,'L');
			$pdf->Ln();
			// $pdf->Cell(5,5,':',0,0,'L');	
			// $pdf->Cell(100,5,$namakaryawan,0,1,'L');
			$pdf->SetX(20);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(10,5,'Sdr. '.$namakaryawan,0,0,'L');
			$pdf->Ln();
			$pdf->SetX(20);	
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(10,5,'Di  -',0,1,'L');
			$pdf->SetX(28);
			$pdf->SetFont('Arial','U',10);
			$pdf->Cell(105,5,'TEMPAT',0,0,'L');
			$pdf->SetFont('Arial','',10);			
			$pdf->Ln();
			$pdf->Ln();
		}
	
	//Content Letter
	if($tipesp == 'BAPP'){
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$pelanggaran,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf1,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->Cell(20,5,'',0,0,'L');
        $pdf->Cell(20,5,$_SESSION['lang']['nama'],0,0,'L');
        $pdf->Cell(2,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(110,5,$namakaryawan,0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->Cell(20,5,'',0,0,'L');
		$pdf->Cell(20,5,$_SESSION['lang']['jabatan'],0,0,'L');
        $pdf->Cell(2,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(110,5,$jabatan,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf3,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf4,0,'J');
	}else if($tipesp == 'SKR'){
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,$_SESSION['lang']['menimbang'],0,0,'L');
        $pdf->Cell(10,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->Cell(10,5,'1.',0,0,'L');   
        $pdf->MultiCell(110,5,'Perlu adanya tindakan disiplin bagi pegawai yang telah melakukan pelanggaran terhadap peraturan perusahaan yang berlaku.',0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,'',0,0,'L');
        $pdf->Cell(10,5,'',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->Cell(10,5,'2.',0,0,'L');   
        $pdf->MultiCell(110,5,'Perlu adanya peringatan keras bagi pegawai yang telah melakukan pelanggaran serius terhadap peraturan perusahaan yang berlaku.',0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,$_SESSION['lang']['memperhatikan'],0,0,'L');
        $pdf->Cell(10,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(125,5,$pelanggaran,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf1,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->Cell(20,5,'',0,0,'L');
        $pdf->Cell(20,5,$_SESSION['lang']['nama'],0,0,'L');
        $pdf->Cell(2,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(110,5,$namakaryawan,0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->Cell(20,5,'',0,0,'L');
		$pdf->Cell(20,5,$_SESSION['lang']['jabatan'],0,0,'L');
        $pdf->Cell(2,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(110,5,$jabatan,0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->Cell(20,5,'',0,0,'L');
		$pdf->Cell(20,5,$_SESSION['lang']['alamat'],0,0,'L');
        $pdf->Cell(2,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(110,5,$alamataktif,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf3,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf4,0,'J');
	}else if($tipesp == 'PHK'){
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,5,$_SESSION['lang']['membaca'],0,0,'L');
        $pdf->Cell(4,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(140,5,$pelanggaran,0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,5,$_SESSION['lang']['menimbang'],0,0,'L');
        $pdf->Cell(4,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(140,5,$paragraf1,0,'J');
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,5,$_SESSION['lang']['mengingat'],0,0,'L');
        $pdf->Cell(4,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(140,5,$paragraf3,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(169,5,strtoupper($_SESSION['lang']['memutuskan']),0,0,'C');
        
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,5,$_SESSION['lang']['menetapkan'],0,0,'L');
        $pdf->Cell(4,5,':',0,0,'L');   
        $pdf->SetFont('Arial','',10);
		$pdf->MultiCell(140,5,$paragraf4,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,'Demikian Surat Keputusan ini dikeluarkan agar yang berkepentingan mengetahui dan menjadi maklum.',0,'J');
	}else{
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$pelanggaran,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf1,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf3,0,'J');
		
		$pdf->Ln();
		
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',10);
        $pdf->MultiCell(170,5,$paragraf4,0,'J');		
	}
	
	$pdf->Ln();	
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->SetX(20);			
	$pdf->MultiCell(170,5,$tmpBar->wilKaryawan.', '.tanggalnormal($tmpBar->tanggal),0,'J');
	$pdf->Ln();
	
	//signature
	if($tmpBar->jenissp=='BAPP'){
		$pdf->SetX(20);
        $pdf->Cell(40,5,$_SESSION['lang']['yangmemeriksa'],0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');
        $pdf->Cell(40,5,$_SESSION['lang']['yangdiperiksa'],0,0,'C'); 
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,$_SESSION['lang']['mengetahui'],0,1,'C');        
        $pdf->Ln();
        $pdf->Ln();			
        $pdf->Ln();	
        $pdf->SetX(20);
        $pdf->Cell(40,5,"".$penandatangan." ",'B',0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$namakaryawan." ",'B',0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$penandatangan." ",'B',1,'C');        
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',10);	
        $pdf->Cell(40,5,"".$jabatan." ",0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$jabatanybs." ",0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$jabatanverifikasi." ",0,1,'C');
	}else if($tmpBar->jenissp=='PHK'){
		$pdf->SetX(20);
        $pdf->Cell(40,5,$_SESSION['lang']['disetujui'],0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');
        $pdf->Ln();
        $pdf->Ln();			
        $pdf->Ln();	
        $pdf->SetX(20);
        $pdf->Cell(40,5,"".$penandatangan." ",'B',0,'C');
		$pdf->Ln();        
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',10);	
        $pdf->Cell(40,5,"".$jabatan." ",0,0,'C');
	}else if($tmpBar->jenissp=='SKR'){
		$pdf->SetX(20);
        $pdf->Cell(40,5,$_SESSION['lang']['disetujui'],0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');
        $pdf->Ln();
        $pdf->Ln();			
        $pdf->Ln();	
        $pdf->SetX(20);
        $pdf->Cell(40,5,"".$penandatangan." ",'B',0,'C');
		$pdf->Ln();        
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',10);	
        $pdf->Cell(40,5,"".$jabatan." ",0,0,'C');
	}else if($tmpBar->jenissp=='ST1'){
		$pdf->SetX(20);
        $pdf->Cell(40,5,$_SESSION['lang']['disetujui'],0,0,'C');
        $pdf->Cell(70,5,'',0,0,'C');
		$pdf->Cell(40,5,$_SESSION['lang']['pegawaiyangbersangkutan'],0,1,'C'); 
		
               
        $pdf->Ln();
        $pdf->Ln();			
        $pdf->Ln();	
        $pdf->SetX(20);
        $pdf->Cell(40,5,"".$penandatangan." ",'B',0,'C');
        $pdf->Cell(70,5,'',0,0,'C');
		$pdf->Cell(40,5,"".$namakaryawan." ",'B',1,'C'); 
		       
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',10);	
        $pdf->Cell(40,5,"".$jabatan." ",0,0,'C');
        $pdf->Cell(70,5,'',0,0,'C');     
		$pdf->Cell(40,5,"".$jabatanybs." ",0,1,'C');
	}else{
		$pdf->SetX(20);
        $pdf->Cell(40,5,$_SESSION['lang']['disetujui'],0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');
        $pdf->Cell(40,5,$_SESSION['lang']['diketahuioleh'],0,0,'C'); 
        $pdf->Cell(15,5,'',0,0,'C');        
		if($dibuat==''){
			$pdf->Cell(40,5,$_SESSION['lang']['pegawaiyangbersangkutan'],0,1,'C'); 
		}else{
			$pdf->Cell(40,5,$_SESSION['lang']['dibuat'],0,1,'C'); 
		}
               
        $pdf->Ln();
        $pdf->Ln();			
        $pdf->Ln();	
        $pdf->SetX(20);
        $pdf->Cell(40,5,"".$penandatangan." ",'B',0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$verifikasi." ",'B',0,'C');
        $pdf->Cell(15,5,'',0,0,'C');  
		if($dibuat==''){
			$pdf->Cell(40,5,"".$namakaryawan." ",'B',1,'C'); 
		}else{
			$pdf->Cell(40,5,"".$dibuat." ",'B',1,'C'); 
		}
               
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',10);	
        $pdf->Cell(40,5,"".$jabatan." ",0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');        
        $pdf->Cell(40,5,"".$jabatanverifikasi." ",0,0,'C');
        $pdf->Cell(15,5,'',0,0,'C');      
		if($dibuat==''){
			$pdf->Cell(40,5,"".$jabatanybs." ",0,1,'C');
		}else{
			$pdf->Cell(40,5,"".$jabatandibuat." ",0,1,'C');
		}        
	}
	
	// if($tipex=='0'){
// //=========penandatangan
			
        // $pdf->SetX(20);
        // $pdf->Cell(40,5,'KPP GROUP',0,1,'L');
        // $pdf->Ln();
        // $pdf->Ln();			
        // $pdf->Ln();	
        // $pdf->SetX(20);
        // $pdf->Cell(40,5,"".$penandatangan." ",'B',1,'L');
        // $pdf->SetX(20);
        // $pdf->SetFont('Arial','',10);	
        // $pdf->Cell(40,5,"".$jabatan." ",0,1,'L');	
    // }else{
                
    // }
	//=====================tembusan	
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetX(20);			
	$pdf->Cell(40,5,'Cc:',0,1,'L');
	if($tembusan1!=''){
		$pdf->SetX(25);
		$pdf->Cell(40,5,'1. '.$tembusan1,0,1,'L');
	}
	if($tembusan2!=''){
		$pdf->SetX(25);			
		$pdf->Cell(40,5,'2. '.$tembusan2,0,1,'L');
	}
	if($tembusan3!=''){
		$pdf->SetX(25);			
		$pdf->Cell(40,5,'3 .'.$tembusan3,0,1,'L');	
	}
	if($tembusan4!=''){
		$pdf->SetX(25);			
		$pdf->Cell(40,5,'4 .'.$tembusan4,0,1,'L');
	}
				

//footer================================
    $pdf->Ln();		
        $pdf->Output();

?>
