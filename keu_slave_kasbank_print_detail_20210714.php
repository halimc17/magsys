<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses = $_GET['proses'];
$param = $_GET;



$nmMt=  makeOption($dbname, 'setup_matauang', 'kode,matauang');

/** Report Prep **/
$cols = array();

#=============================== Header =======================================
$whereH = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$queryH = selectQuery($dbname,'keu_kasbankht','*',$whereH);
$resH = fetchData($queryH);

//echo "<pre>";
//print_r($resH);
//echo "</pre>";

# Get Nama Pembuat
$userId = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
    "karyawanid='".$resH[0]['userid']."'");
# Get Nama Akun Hutang
$namaakunhutang = makeOption($dbname,'keu_5akun','noakun,namaakun',
    "noakun='".$resH[0]['noakunhutang']."'");
#Get tipe Lokasi Tugas
$tipeLokasiTugas = makeOption($dbname,'organisasi','kodeorganisasi,tipe');

#=============================== Detail =======================================
# Data
$col1 = 'noakun,jumlah,noaruskas,matauang,kode,keterangan2';
$cols = array('nourut','noakun','namaakun','noaruskas','debet','kredit');
$colshtml = array('nourut','noakun','namaakun','noaruskas','debet','kredit','keterangan');
//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
$where = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun2a='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$query = selectQuery($dbname,'keu_kasbankdt',$col1,$where);
$res = fetchData($query);

# Data Empty
if(empty($res)) {
    echo 'Data Empty';
    exit;
}

# Options
$whereAkun = "noakun in (";
$whereAkun .= "'".$resH[0]['noakun']."'";
$whereAkun .= ",'".$resH[0]['noakunhutang']."'"; // tambahin kamus nama akun hutangunit
foreach($res as $key=>$row) {
    $whereAkun .= ",'".$row['noakun']."'";
}
$whereAkun .= ")";
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
/*
$optSupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');;
*/
$optHutangUnit = array('0'=>'Tidak','1'=>'Ya');

# Data Show
$data = array();

#================================ Prep Data ===================================
# Total
$totalDebet = 0;$totalKredit = 0;

# Dari Header
$i=1;
$data[$i] = array(
    'nomor'=>$i,
    'noakun'=>$resH[0]['noakun'],
    'namaakun'=>$optAkun[$resH[0]['noakun']],
    'noaruskas'=>$resH[0]['noaruskas'],
    'debet'=>0,
    'kredit'=>0,
    'keterangan2'=>$row['keterangan2'],
);

if($param['tipetransaksi']=='M') {
    $data[$i]['debet'] = $resH[0]['jumlah'];
    $totalDebet += $resH[0]['jumlah'];
	
} else {
    $data[$i]['kredit'] = $resH[0]['jumlah'];
    $totalKredit += $resH[0]['jumlah'];
	
}

if(substr($resH[0]['noakun'],0,5)=='11102')
{
	if($resH[0]['tipetransaksi']=='K'){
		$title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['keluar'].")");
	}else{
		$title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['masuk'].")");
	}
}
else
{
	if($resH[0]['tipetransaksi']=='K'){
		$title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
	}else{
		$title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
	}
}


$i++;

# Dari Detail
foreach($res as $row) {
    $data[$i] = array(
		'nomor'=>$i,
		'noakun'=>$row['noakun'],
		'namaakun'=>$optAkun[$row['noakun']],
		'noaruskas'=>$row['noaruskas'],
		'debet'=>0,
		'kredit'=>0,
        'keterangan2'=>$row['keterangan2'],
    );
//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
    if($param['tipetransaksi']=='M' and $row['jumlah']>0) {
	$data[$i]['kredit'] = $row['jumlah'];
	$totalKredit += $row['jumlah'];
    }
    else if($param['tipetransaksi']=='K' and $row['jumlah']<0){
	$data[$i]['kredit'] = $row['jumlah']*-1;
	$totalKredit += $row['jumlah']*-1;        
    }
    else if($param['tipetransaksi']=='M' and $row['jumlah']<0){
	$data[$i]['debet'] = $row['jumlah']*-1;
	$totalDebet += $row['jumlah']*-1;        
    }    
    else {
	$data[$i]['debet'] = $row['jumlah'];
	$totalDebet += $row['jumlah'];
    }
    $i++;
}

// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
if(!empty($data)) foreach($data as $c=>$key) {
    $sort_debet[] = $key['debet'];
    $sort_kredit[] = $key['kredit'];
}

// sort
if(!empty($data))array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

$align = explode(",","R,R,L,L,R,R,L,L");
$length = explode(",","7,12,35,10,18,18,10");
$titleDetail = 'Detail';

/** Output Format **/
switch($proses) {
    
    case 'pdf2':
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
                global $kodept;
				
                //ambil nama pt
                
                               
				$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' "; 
				$res1=mysql_query($str1);
				while($bar1=mysql_fetch_object($res1))
				{
                                        $kodept=$bar1->kodeorganisasi;
					$namapt=$bar1->namaorganisasi;
					$alamatpt=$bar1->alamat;
					$telp=$bar1->telepon;				 
				}

				if($kodept=='AMP'){
					$path='images/logo_amp.jpg';
				}else if($kodept=='CKS'){
					$path='images/logo_cks.jpg';
				}else if($kodept=='KAA'){
					$path='images/logo_kaa.jpg';
				}else if($kodept=='KAL'){
					$path='images/logo_kal.jpg';
				}else if($kodept=='MPA'){
					$path='images/logo_mpa.jpg';
				}else if($kodept=='MHS'){
					$path='images/logo_mhs.jpg';
				}else if($kodept=='MEA'){
					$path='images/logo_mea.jpg';
				}else if($kodept=='SMA'){
					$path='images/logo_sma.jpg';
				}else{
					$path='images/logo.jpg';
				}
				
				$this->Image($path,15,3,18);	
				$this->SetFont('Arial','B',10);
				$this->SetFillColor(255,255,255);	
				$this->SetY(5);
				$this->SetX(40);  
				$this->Cell(60,5,$namapt,0,1,'L');	 
				$this->SetX(40); 		
				$this->MultiCell(150,5,$alamatpt,0,'L');	
				$this->SetX(40); 			
				$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
				$this->Ln(10);
				$this->SetFont('Arial','B',15);
			   
				$this->Cell(190,5,'Bukti Pembayaran',0,1,'C');		
				$this->SetFont('Arial','',6); 
				$this->SetY(27);
				$this->SetX(163);
				//$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
				$this->Line(10,27,200,27);	
            }

            function Footer()
            {
            /*    $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');*/
            }
	    }
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		$heig=6;
		$size=10;

		$pdf->Ln(20);
		$iData="select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'"; //echo $str;exit();           
		$nData=  mysql_query($iData) or die (mysql_error($conn));
		$dData=  mysql_fetch_assoc($nData);
		
		$optSup=  makeOption($dbname, 'keu_kasbankdt', 'notransaksi,kodesupplier');
		$optNmSup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		
		if($optNmSup[$optSup[$dData['notransaksi']]]!='')
		{
			$isiSup=$optNmSup[$optSup[$dData['notransaksi']]];
		}
		else
		{
			
			$isiSup='n/a';
		}
        
		$pdf->SetFillColor(255,255,255);
		
		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(30,$heig,'Bayar Kepada',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,$isiSup,0,1,'L',1);
			
		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(30,$heig,'No. Transaksi',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,$dData['notransaksi'],0,0,'L',1); 
			
			
		$thnD=substr($dData['tanggal'],0,4);
		$blnD=substr($dData['tanggal'],5,2);
		$nmBln=numToMonth($blnD, 'I', 'long');
		$tglD=substr($dData['tanggal'],8,2);
		
		$dateNya=$tglD.' '.$nmBln.' '.$thnD;
		
		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(10,$heig,'Tgl',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,$dateNya,0,1,'L',1);     
			
		// $pdf->Cell($width,$height,$_SESSION['lang']['cgttu']." : ".$resH[0]['cgttu'],0,1,'L',1);
		// if($resH[0]['cgttu'] == 'Giro'){
			// $pdf->Cell($width,$height,"No. Giro : ".$resH[0]['nocek'],0,1,'L',1);
		// }else if($resH[0]['cgttu'] == 'Cheque'){
			// $pdf->Cell($width,$height,"No. Cek : ".$resH[0]['nocek'],0,1,'L',1);
		// }	
			
			
		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(30,$heig,'Keterangan',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,$dData['keterangan'],0,1,'L',1); 

		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(30,$heig,$_SESSION['lang']['cgttu'],0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,$dData['cgttu'],0,1,'L',1); 
		
		if($dData['cgttu'] == 'Giro'){
			$pdf->SetFont('Arial','B',$size);
			$pdf->Cell(30,$heig,'No. Giro',0,0,'L',1);
			$pdf->SetFont('Arial','',$size);
			$pdf->Cell(10,$heig,':',0,0,'C',1);
			$pdf->Cell(100,$heig,$dData['nocek'],0,1,'L',1); 
		}else if($dData['cgttu'] == 'Cheque'){
			$pdf->SetFont('Arial','B',$size);
			$pdf->Cell(30,$heig,'No. Cek',0,0,'L',1);
			$pdf->SetFont('Arial','',$size);
			$pdf->Cell(10,$heig,':',0,0,'C',1);
			$pdf->Cell(100,$heig,$dData['nocek'],0,1,'L',1); 
		}else if($dData['cgttu'] == 'Transfer'){
			$pdf->SetFont('Arial','B',$size);
			$pdf->Cell(30,$heig,'No. Transfer',0,0,'L',1);
			$pdf->SetFont('Arial','',$size);
			$pdf->Cell(10,$heig,':',0,0,'C',1);
			$pdf->Cell(100,$heig,$dData['nocek'],0,1,'L',1); 
		}
			
		// $pdf->SetFont('Arial','B',$size);
		// $pdf->Cell(30,$heig,'Tunai/Cek',0,0,'L',1);
		// $pdf->SetFont('Arial','',$size);
		// $pdf->Cell(10,$heig,':',0,0,'C',1);
		// $pdf->Cell(100,$heig,$dData['cgttu'].' / '.$dData['noakun'],0,1,'L',1);    
		
		$pdf->SetFont('Arial','B',$size);
		$pdf->Cell(30,$heig,'Nominal',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
		$pdf->Cell(10,$heig,':',0,0,'C',1);
		$pdf->Cell(100,$heig,number_format($dData['jumlah'],2).' '.$dData['matauang'],0,1,'L',1);       
			
		$pdf->SetFont('Arial','B',$size);
		
		$pdf->Cell(30,$heig,'Terbilang',0,0,'L',1);
		$pdf->SetFont('Arial','',$size);
			$pdf->Cell(10,$heig,':',0,0,'C',1);
			$pdf->MultiCell(100, $heig,terbilang($dData['jumlah'],3).' '.$nmMt[$resH[0]['matauang']],0, 'J',0);
			//$pdf->Cell(100,$heig,terbilang($dData['jumlah']),0,1,'L',1);     
	   
		$pdf->Ln(15);    
		$pdf->Cell(50,$heig*5,'',1,0,'L',1);    
		$pdf->Cell(50,$heig*5,'',1,1,'L',1);
		$pdf->Cell(50,$heig,'Kasir',1,0,'C',1);
		$pdf->Cell(50,$heig,'User',1,1,'C',1);
		$pdf->Ln(15);  
		
		$pdf->SetFont('Arial','I',8);
		$pdf->Cell(100,10,'Tgl Cetak : '.date('d-m-Y H:i:s'),0,0,'L');	
		$pdf->Cell(90,10,'Page '.$pdf->PageNo().'/'.$pdf->PageNo(),0,0,'R');      
		
		$pdf->Output();
        break;
    
    
    case 'pdf':
        
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
                global $kodept;
				
                //ambil nama pt
                
                               
				$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' "; 
				$res1=mysql_query($str1);
				while($bar1=mysql_fetch_object($res1))
				{
					$kodept=$bar1->kodeorganisasi;
					$namapt=$bar1->namaorganisasi;
					$alamatpt=$bar1->alamat;
					$telp=$bar1->telepon;				 
				}
				
				if($kodept=='AMP'){
					$path='images/logo_amp.jpg';
				}else if($kodept=='CKS'){
					$path='images/logo_cks.jpg';
				}else if($kodept=='KAA'){
					$path='images/logo_kaa.jpg';
				}else if($kodept=='KAL'){
					$path='images/logo_kal.jpg';
				}else if($kodept=='LKA'){
					$path='images/logo_lka.jpg';
				}else if($kodept=='MPA'){
					$path='images/logo_mpa.jpg';
				}else if($kodept=='MHS'){
					$path='images/logo_mhs.jpg';
				}else if($kodept=='MEA'){
					$path='images/logo_mea.jpg';
				}else if($kodept=='SMA'){
					$path='images/logo_sma.jpg';
				}else{
					$path='images/logo.jpg';
				}
				
				//$path='images/logo.jpg';
                                
				$this->Image($path,15,3,18);	
				$this->SetFont('Arial','B',10);
				$this->SetFillColor(255,255,255);	
				$this->SetY(5);
				$this->SetX(40);  
				$this->Cell(60,5,$namapt,0,1,'L');	 
				$this->SetX(40); 		
				$this->MultiCell(150,5,$alamatpt,0,'L');	
				$this->SetX(40); 			
				$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
				$this->Ln(10);
				
				$this->SetFont('Arial','',6); 
				$this->SetY(27);
				$this->SetX(163);
				//$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
				$this->Line(10,27,200,27);
                                $this->Ln();
            }

            function Footer()
            {
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $this->SetY(-20);
                $this->SetFont('Arial','I',7);
                $this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
		$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
			":".$rPeriode['periode']." at ".date('d-m-Y H:i:s');
		$this->Cell($width-1,$height,$str,'T',0,'R');
            }
        }
		
		$pdf=new PDF('P','mm','A4');
                $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                $height = 5;
               
		$pdf->AddPage();
	
		//$pdf->Ln();
                //HT
                $iht="select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' ";
                $nht=  mysql_query($iht) or die (mysql_error($conn));
                $dht=  mysql_fetch_assoc($nht);
                
                
                
                if(substr($dht['noakun'],0,5)=='11102')
                {
                        if($dht['tipetransaksi']=='K'){
                                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['keluar'].")");
                        }else{
                                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['masuk'].")");
                        }
                }
                else
                {
                        if($dht['tipetransaksi']=='K'){
                                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
                        }else{
                                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
                        }
                }
                
                
                $optSup=  makeOption($dbname, 'keu_kasbankdt', 'notransaksi,kodesupplier');
				$optNmSup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
				
				if($optNmSup[$optSup[$dht['notransaksi']]]!='')
				{
					$isiSup=$optNmSup[$optSup[$dht['notransaksi']]];
				}
				else
				{
					
					$isiSup='n/a';
				}
				
				
				
				$pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','B',9);
				
				/*
				 $pdf->Cell(30,$heig,'Bayar Kepada',0,0,'L',1);
				$pdf->SetFont('Arial','',$size);
				$pdf->Cell(10,$heig,':',0,0,'C',1);
				$pdf->Cell(100,$heig,$isiSup,0,1,'L',1);
				*/
				
                $pdf->Cell($width,$height,$title,0,1,'C',1);//ini nanti isi masuk / keluar , kas
				$pdf->Cell($width,$height,'Bayar Kepada'." : ".$isiSup,0,1,'L',1);
				$pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
                $pdf->Cell($width,$height,$_SESSION['lang']['cgttu']." : ".$dht['cgttu'],0,1,'L',1);
                if($dht['cgttu'] == 'Giro'){
                        $pdf->Cell($width,$height,"No. Giro : ".$dht['nocek'],0,1,'L',1);
                }else if($dht['cgttu'] == 'Cheque'){
                        $pdf->Cell($width,$height,"No. Cek : ".$dht['nocek'],0,1,'L',1);
                }     
                $pdf->Cell($width,$height,$_SESSION['lang']['matauang']." : ".$dht['matauang'],0,1,'L',1);
                $pdf->Cell($width,$height,$_SESSION['lang']['kurs']." : ".number_format($dht['kurs'],0),0,1,'L',1);

                # Header
                #$pdf->Cell($width,$height,$titleDetail,0,1,'L',1);
				# Keterangan
                $pdf->MultiCell($width,$height,$_SESSION['lang']['remark'].' : '.$dht['keterangan']);
               
	
        
		//$pdf->Ln(20);
                
                //No. No. Akun Nama Akun Kode Kas Debet Kredit
                $pdf->SetFillColor(220,220,220);
                $pdf->Cell(10,$height,$_SESSION['lang']['nourut'],1,0,'C',1);
                $pdf->Cell(20,$height,'No. Akun',1,0,'C',1);
                $pdf->Cell(80,$height,$_SESSION['lang']['namaakun'],1,0,'C',1);
                $pdf->Cell(20,$height,'Kode Kas',1,0,'C',1);
                $pdf->Cell(30,$height,$_SESSION['lang']['debet'],1,0,'C',1);
                $pdf->Cell(30,$height,$_SESSION['lang']['kredit'],1,1,'C',1);
                
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',9);
                //prepare data
               
                
                if($param['tipetransaksi']=='M') {
                    $datahdb= $dht['jumlah'];
                } else {
                    $datahkr=$dht['jumlah'];
                }
                
                
              
                
                    /*$no=1;
                    $pdf->Cell(10,$height,$no,TLR,0,'C',1);
                    $pdf->Cell(20,$height,$dht['noakun'],TLR,0,'R',1);
                    $pdf->Cell(80,$height,$optAkun[$dht['noakun']],TLR,0,'L',1);
                    $pdf->Cell(20,$height,$dht['noaruskas'],TLR,0,'C',1);
                    $pdf->Cell(30,$height,number_format($datahdb,2),TLR,0,'R',1);
                    $pdf->Cell(30,$height,number_format($datahkr,2),TLR,1,'R',1);
                    */
                    /*
                    ##########################################head
                    #########buat baris pertama dulu untuk pembanding
                    ############################################
					*/
                    $height=5;
                    $awalynmakun=$pdf->GetY();
                    $pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
                    $pdf->MultiCell(80, $height, $optAkun[$dht['noakun']], '0', 'L');
                    $akhirynmakun=$pdf->GetY();
                    $tinggiynmakun=$akhirynmakun-$awalynmakun;
                    $heightakun=$tinggiynmakun;
                    $pdf->SetY($akhirynmakun-$tinggiynmakun);
                    
                    $no+=1;
                   
                    $awalxlist=$pdf->GetX();
                    $awalylist=$pdf->GetY();
                    
                    
                    if($heightakun>$height)
                    {
                        $pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
                        $pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
                        $pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
                        $pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
                        $pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
                    }
                    
                    $pdf->Cell(10,$height,$no,TRL,0,'C',1);
                    
                    $pdf->Cell(20,$height,$dht['noakun'],TRL,0,'R',1);
                    $awalxlistnmakun=$pdf->GetX();
                    $pdf->MultiCell(80,$height,$optAkun[$dht['noakun']],TRL,'J');
                    $pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
                    $pdf->Cell(20,$height,$dht['noaruskas'],TRL,0,'C',1);
                    $pdf->Cell(30,$height,number_format($datahdb,2),TRL,0,'R',1);
                    $pdf->Cell(30,$height,number_format($datahkr,2),TRL,1,'R',1);
                    if($heightakun>$height)
                    {
                        $isi=$pdf->Ln();
                    }
                    /*
                    ############################################
                    ###tutup head##################################
                    ###########################################
                    */
                    
                    
                    
                    
                    
                    
                    /*$pdf->Cell(10,$height,'',BLR,0,'C',1);
                    $pdf->Cell(20,$height,'',BLR,0,'R',1);
                    $pdf->Cell(80,$height,$dht['keterangan'],BLR,0,'L',1);
                    $pdf->Cell(20,$height,'',BLR,0,'C',1);
                    $pdf->Cell(30,$height,'',BLR,0,'R',1);
                    $pdf->Cell(30,$height,'',BLR,1,'R',1);*/
                /*    
               ##########################################################
                ##############buat detail    
               #########################################################     
                 */   
                $idtd="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' "
                        . " order by jumlah asc";
                $ndtd=  mysql_query($idtd) or die (mysql_error($conn));
                //$i=0;
                while($ddtd=  mysql_fetch_assoc($ndtd))
                {
                    
                    
                    
                    if($param['tipetransaksi']=='M' and $ddtd['jumlah']>0) {
                        $datadkr=$ddtd['jumlah'];
                        $dataddb=0;
                    }
                    else if($param['tipetransaksi']=='K' and $ddtd['jumlah']<0){ 
                        $datadkr=$ddtd['jumlah']*-1;
                        $dataddb=0;
                    }
                    else if($param['tipetransaksi']=='M' and $ddtd['jumlah']<0){ 
                        $dataddb=$ddtd['jumlah']*-1;
                        $datadkr=0;
                    }    
                    else {
                        $dataddb=$ddtd['jumlah'];
                        $datadkr=0;
                    }
                    
                    
                    ##buat baris pertama dulu
                    $height=5;
                    $awalynmakun=$pdf->GetY();
                    $pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
                    $pdf->MultiCell(80, $height, $optAkun[$ddtd['noakun']], '0', 'L');
                    $akhirynmakun=$pdf->GetY();
                    $tinggiynmakun=$akhirynmakun-$awalynmakun;
                    $heightakun=$tinggiynmakun;
                    $pdf->SetY($akhirynmakun-$tinggiynmakun);
                    
                    $no+=1;
                    /*$pdf->Cell(10,$heightakun,$no,TRL,0,'C',1);
                    $pdf->Cell(20,$heightakun,$ddtd['noakun'],TRL,0,'R',1);
                    $awalxlistnmakun=$pdf->GetX();
                    $pdf->MultiCell(80,$height,$optAkun[$ddtd['noakun']],TRL,'J');
                    $pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
                    //$pdf->MultiCell(80,$height,$keterangan,'TLR','J');
                    
                    $pdf->Cell(20,$heightakun,$ddtd['noaruskas'],TRL,0,'C',1);
                    $pdf->Cell(30,$heightakun,number_format($dataddb,2),TRL,0,'R',1);
                    $pdf->Cell(30,$heightakun,number_format($datadkr,2),TRL,1,'R',1);
                     
                     */
                    
                    
                    
                    
                    
                    
                    $awalxlist=$pdf->GetX();
                    $awalylist=$pdf->GetY();
                    
                    
                    if($heightakun>$height)
                    {
                        $pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
                        $pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
                        $pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
                        $pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
                        $pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
                    }
                    
                    $pdf->Cell(10,$height,$no,TRL,0,'C',1);
                    
                    
                    
                    /*if($heightakun>$height)
                    {
                        $pdf->SetY($awalylist+$height);
                        $pdf->Cell(10,$height,'',RL,0,'C',1);
                    }
                    $pdf->SetXY($awalxlist+10, $awalylist);
                    */
                  
                    $pdf->Cell(20,$height,$ddtd['noakun'],TRL,0,'R',1);
                    $awalxlistnmakun=$pdf->GetX();
                    $pdf->MultiCell(80,$height,$optAkun[$ddtd['noakun']],TRL,'J');
                    $pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
                    //$pdf->MultiCell(80,$height,$keterangan,'TLR','J');
                    
                    $pdf->Cell(20,$height,$ddtd['noaruskas'],TRL,0,'C',1);
                    $pdf->Cell(30,$height,number_format($dataddb,2),TRL,0,'R',1);
                    $pdf->Cell(30,$height,number_format($datadkr,2),TRL,1,'R',1);
                   
                    if($heightakun>$height)
                    {
                        $isi=$pdf->Ln();
                    }
                    
                    $awalyket=$pdf->GetY();
                    $pdf->SetX(100000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
                    $pdf->MultiCell(80, $height, $ddtd['keterangan2'], '0', 'L');
                    $akhiryket=$pdf->GetY();
                    $tinggiyket=$akhiryket-$awalyket;
                    $heightket=$tinggiyket;
                    $pdf->SetY($akhiryket-$tinggiyket);
                    
                    
                    $pdf->Cell(10,$heightket,'',BRL,0,'C',1);
                    $pdf->Cell(20,$heightket,'',BRL,0,'R',1);
                    $awalxlistket=$pdf->GetX();
                    $pdf->MultiCell(80,$height,$ddtd['keterangan2'],BRL,'J');
                    $pdf->SetXY($awalxlistket+80, $awalyket);
                    $pdf->Cell(20,$heightket,'',BRL,0,'C',1);
                    $pdf->Cell(30,$heightket,'',BRL,0,'R',1);
                    $pdf->Cell(30,$heightket,'',BRL,1,'R',1);
                    
                    //$i++;
                    
                    
                    if($pdf->GetY() > 250) {
                    //$i=0;
                    $akhirY=$akhirY-20;
                    $akhirY=$pdf->GetY()-$akhirY;
                    $akhirY=$akhirY+35;
                    $pdf->AddPage();
                    }
                    
                    
                    $totdtdb+=$dataddb;
                    $totdtkr+=$datadkr;
                }
                
                $gtotdb=$datahdb+$totdtdb;
                $gtotkr=$datahkr+$totdtkr;
                
                $pdf->SetFont('Arial','B',9);
				$pdf->Cell(130,$height,'Total',1,0,'C',1);
                $pdf->Cell(30,$height,number_format($gtotdb,2),1,0,'R',1);
                $pdf->Cell(30,$height,number_format($gtotkr,2),1,1,'R',1);
                
                
                # Keterangan
                # $pdf->MultiCell($width,$height,$_SESSION['lang']['remark'].' : '.$dht['keterangan']);
				$pdf->MultiCell($width,$height,$_SESSION['lang']['terbilang'].' : '.terbilang($dht['jumlah'],3). ' '.$nmMt[$dht['matauang']],0);
                # Hutang Unit
                if($dht['hutangunit']==1){
                    $pdf->MultiCell($width,$height,'Unit payable Account '.$dht['pemilikhutang'].' : '.$namaakunhutang[$dht['noakunhutang']]);
                }
                $pdf->Ln();
                
                
                #######
                ##ttd###
                #######
               
                $pdf->SetFillColor(220,220,220);
                if($tipeLokasiTugas[$dht['kodeorg']]!='HOLDING')
                    {
                        $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
                                    $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
                                    $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['disetujui'],1,0,'C',1);
                                    $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diverifikasi'],1,0,'C',1);
                                    $pdf->Ln();
                                    $pdf->SetFillColor(255,255,255);
                                    for($i=0;$i<4;$i++) {
                                            $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                            $pdf->Ln();
                                    }
                                    if(isset($userId[$dht['userid']])) {
                                            $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
                                    } else {
                                            $pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
                                    }
                                    $pdf->Cell(25/100*$width,$height,'(ROA)','BLR',0,'C',1);
                                    $pdf->Cell(25/100*$width,$height,'(General Manager)','BLR',0,'C',1);
                                    $pdf->Cell(25/100*$width,$height,'(Acct & Tax)','BLR',0,'C',1);	
                    }
                    else
                    {
                                    if(($dht['tipetransaksi']=='K' and ($dht['jumlah']*$dht['kurs']) > 0 and ($dht['jumlah']*$dht['kurs']) <= 5000000) or
                           $dht['tipetransaksi']=='M'){
                                            $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['disetujui'],1,0,'C',1);
                                            $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diverifikasi'],1,0,'C',1);
                                            $pdf->Ln();
                                            $pdf->SetFillColor(255,255,255);
                                            for($i=0;$i<4;$i++) {
                                                    $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                                    $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                                    $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                                    $pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
                                                    $pdf->Ln();
                                            }
                                            if(isset($userId[$dht['userid']])) {
                                                           // $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
															$pdf->Cell(25/100*$width,$height+5,$userId[$dht['userid']],'BLR',0,'C',1);
                                            } else {
                                                            //$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
															$pdf->Cell(25/100*$width,$height+5,'','BLR',0,'C',1);
															
                                            }
											/*
											  $pdf->Cell(125,$height,$isiBerikat,RL,0,'L'); 
											  $pdf->vcell(125,15,25,$isiBerikat,87); 
											  function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
											*/
                                            $pdf->Cell(25/100*$width,$height+5,'(Budget & Cost Control)','BLR',0,'C',1);
                                            //$pdf->vcell2(28/100*$width,$height+5,57.5,'(Budget & Cost Control)',23,'BLR',0,'C');
											if($kodept=='MPA'){
												$pdf->Cell(25/100*$width,$height+5,'(Director)','BLR',0,'C',1);
											}else{
												$pdf->Cell(25/100*$width,$height+5,'(Finance)','BLR',0,'C',1);
											}
                                            $pdf->Cell(25/100*$width,$height+5,'(Acct & Tax)','BLR',0,'C',1);

                        }elseif(($dht['jumlah']*$dht['kurs']) > 5000000 && ($dht['jumlah']*$dht['kurs']) <= 100000000){
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['disetujui'].' (1)',1,0,'C',1);
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['disetujui'].' (2)',1,0,'C',1);
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diverifikasi'],1,0,'C',1);
                                            $pdf->Ln();
                                            $pdf->SetFillColor(255,255,255);
                                            for($i=0;$i<4;$i++) {
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Ln();
                                            }
                                            if(isset($userId[$dht['userid']])) {
                                                            //$pdf->Cell(20/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
															$pdf->Cell(20/100*$width,$height+5,$userId[$dht['userid']],'BLR',0,'C',1);
                                            } else {
                                                            //$pdf->Cell(20/100*$width,$height,'','BLR',0,'C',1);
															$pdf->Cell(20/100*$width,$height+5,'','BLR',0,'C',1);
                                            }
                                            $pdf->Cell(20/100*$width,$height+5,'(Budget & Cost Control)','BLR',0,'C',1);
											//$pdf->vcell2(20/100*$width,$height+5,48,'(Budget & Cost Control)',15,'BLR',0,'C');
                                            if($kodept=='MPA'){
												$pdf->Cell(20/100*$width,$height+5,'(Director)','BLR',0,'C',1);
	                                            $pdf->Cell(20/100*$width,$height+5,'(Finance Director)','BLR',0,'C',1);
											}else{
												$pdf->Cell(20/100*$width,$height+5,'(Finance)','BLR',0,'C',1);
	                                            $pdf->Cell(20/100*$width,$height+5,'(Director)','BLR',0,'C',1);
											}
                                            $pdf->Cell(20/100*$width,$height+5,'(Acct & Tax)','BLR',0,'C',1);
                        }else{
                                            $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
                                            $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
                                            $pdf->Cell(17/100*$width,$height,$_SESSION['lang']['disetujui'].' (1)',1,0,'C',1);
                                            $pdf->Cell(17/100*$width,$height,$_SESSION['lang']['disetujui'].' (2)',1,0,'C',1);
                                            $pdf->Cell(17/100*$width,$height,$_SESSION['lang']['disetujui'].' (3)',1,0,'C',1);
                                            $pdf->Cell(14/100*$width,$height,$_SESSION['lang']['diverifikasi'],1,0,'C',1);
                                            $pdf->Ln();
                                            $pdf->SetFillColor(255,255,255);
                                            for($i=0;$i<5;$i++) {
                                                            $pdf->Cell(15/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(17/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(17/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(17/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Cell(14/100*$width,$height,'','LR',0,'C',1);
                                                            $pdf->Ln();
                                            }
                                            if(isset($userId[$dht['userid']])) {
                                                            $pdf->Cell(15/100*$width,$height+5,$userId[$dht['userid']],'BLR',0,'C',1);
                                            } else {
                                                            $pdf->Cell(15/100*$width,$height+5,'','BLR',0,'C',1);
                                            }
                                            $pdf->Cell(20/100*$width,$height+5,'(Budget & Cost Control)','BLR',0,'C',1);
											//$pdf->vcell2(18/100*$width,$height+5,36.5,'(Budget & Cost Control)',15,'BLR',0,'C');
											if($kodept=='MPA'){
												$pdf->Cell(17/100*$width,$height+5,'(Director)','BLR',0,'C',1);
	                                            $pdf->Cell(17/100*$width,$height+5,'(Finance Director)','BLR',0,'C',1);
											}else{
												$pdf->Cell(17/100*$width,$height+5,'(Finance)','BLR',0,'C',1);
	                                            $pdf->Cell(17/100*$width,$height+5,'(Director)','BLR',0,'C',1);
											}	
                                            $pdf->Cell(17/100*$width,$height+5,'(President Dir/Com)','BLR',0,'C',1);
                                            $pdf->Cell(14/100*$width,$height+5,'(Acct & Tax)','BLR',0,'C',1);
                        }
                    }
                
                
                
                
		
	
        $pdf->Output();
        break;
        
        
        
    case 'excel':
        break;
    case'html':
        $tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$res[0]['kode']."/".$param['notransaksi']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['cgttu']."</td><td> :</td><td> ".$resH[0]['cgttu']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['terbilang']."</td><td> :</td><td> ".terbilang($resH[0]['jumlah'],2).
	    ' rupiah'."</td></tr>";
        if($resH[0]['hutangunit']==1){
            $tab.="<tr><td>".$_SESSION['lang']['hutangunit']."</td><td> :</td><td> ".'Unit payable Account '.$resH[0]['pemilikhutang'].' : '.$namaakunhutang[$resH[0]['noakunhutang']]."</td></tr>";            
        }
        $tab.="</tbody></table><br />";
       
            $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><thead><tr class=rowheader>";
            
            
            foreach($colshtml as $column) {
                $tab.="<td>".$_SESSION['lang'][$column]."</td>";
            }
            $tab.="</tr></thead><tbody class=rowcontent>";
            
            
          
            
        // nyusun ulang nomor setelah disort by debet. dz
            $nyomor=0;
            foreach($data as $key=>$row) {    
                $nyomor+=1;
                $tab.="<tr>";
                foreach($row as $key=>$cont) {
                    if($key=='nomor'){
                        $tab.="<td>".$nyomor."</td>";
                    }else{
                        if($key=='debet' or $key=='kredit') {
                            $tab.="<td>".number_format($cont,0)."</td>";
                        } else {
                            $tab.="<td>".$cont."</td>";
                        }                    
                    }
                }
                $tab.="</tr>";
            }
        $tab.="<tr><td colspan=4 align=center>Total</td><td align=right>".number_format($totalDebet,0)."</td>"
                . "<td align=right>".number_format($totalKredit,0)."</td>"
                . "<td></td></tr>";
             $tab.="</tbody></table> <br />";
       
        echo $tab;
        
    break;
    default:
    break;
}
?>