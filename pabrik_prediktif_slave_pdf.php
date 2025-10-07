<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

	$tmp=explode(',',$_GET['column']);
	$notran=$tmp[0];
	//exit("Error:$notran");
	
//create Header
class PDF extends FPDF
{
	
	function Header()
	{
	}
	
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
			
	$pdf->Ln();
        
        $iHt="select * from ".$dbname.".pabrik_predictiveht where notransaksi='".$notran."' ";
        $nHt=  mysql_query($iHt) or die(mysql_error($conn));
        $dHt=  mysql_fetch_assoc($nHt);
        
        $path='images/logo.jpg';
	    $pdf->Image($path,15,3,15);	
	
        $pdf->SetFont('Arial','',8); 
        $pdf->SetXY(170,2);
        $pdf->Cell(35,5,'Form No :',0,1,'L');
        $pdf->SetX(170);
        $pdf->Cell(35,5,$_SESSION['lang']['notransaksi'],1,1,'C');
        $pdf->SetX(170);
        $pdf->Cell(35,5,$dHt['notransaksi'],1,1,'C');
        
        
        
	$pdf->SetFont('Arial','B',12);
        $pdf->SetY(10);
	$pdf->Cell(190,5,'JOB PREDICTIVE MAINTENANCE',0,1,'C');	
	$pdf->SetFont('Arial','',6);
	$pdf->Ln();	
	
        $pdf->SetFont('Arial','',9); 
        $pdf->Cell(20,5,'Tanggal',1,0,'L');
        $pdf->Cell(60,5,tanggalnormal($dHt['tanggal']),1,0,'L');
        //$pdf->Cell(15,5,'Jam',1,0,'L');
        //$pdf->Cell(20,5,substr($dHt['jam'],0,5),1,0,'L');
        //$pdf->Cell(25,5,'Pemohon',1,0,'L');
        //$pdf->Cell(50,5,$dHt['namapemohon'],1,0,'L');
        //$pdf->Cell(15,5,$dHt['statuspemohon'],1,1,'L');
        $pdf->Cell(13,5,'',1,0,'L');
        $pdf->Cell(23,5,'',1,0,'L');
        $pdf->Cell(14,5,'',1,0,'L');
        $pdf->Cell(65,5,'',1,1,'L');
        
        //$pdf->SetFillColor(0, 0, 100);
 
        //$pdf->SetFillColor(100, 95, 0, 0);
		$awalYMesin = $pdf->GetY();
		$pdf->SetY($awalYMesin);
		$pdf->SetX(1000);
		$pdf->MultiCell(60,5,$nmOrg[$dHt['mesin']],0,'L');
		$akhirYMesin=$pdf->GetY();
		$heightMesin=$akhirYMesin-$awalYMesin;
		$pdf->SetY($awalYMesin);
		
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(20,$heightMesin,'Mesin / Alat',1,0,'L','1');
        $pdf->Cell(60,$heightMesin,'',1,0,'L');
        $pdf->Cell(13,$heightMesin,'Kode',1,0,'L');
        $pdf->Cell(23,$heightMesin,$dHt['mesin'],1,0,'L');
        $pdf->Cell(14,$heightMesin,'Stasiun',1,0,'L');
        $pdf->Cell(65,$heightMesin,$nmOrg[$dHt['statasiun']],1,1,'L');
		
		$pdf->SetY($awalYMesin);
		$pdf->SetX(30);
		$pdf->MultiCell(60,5,$nmOrg[$dHt['mesin']],0,'L');
        
        $pdf->SetFont('Arial','U',9); 
        $pdf->Cell(195,5,'Uraian Kerusakan / Prev, Maint. / Kalibrasi / Projek : ','LR',1,'L');
        $pdf->SetFont('Arial','',9); 
        $pdf->MultiCell(195, 5,$dHt['kegiatan'],'LBR', 'J');
        
        $pdf->SetFont('Arial','',9); 
        $pdf->Cell(20,5,'S t a r t',1,0,'L','1');
        $pdf->Cell(30,5,tanggalnormal(substr($dHt['jammulai'],0,10)).' '.substr($dHt['jammulai'],11,2).':'.substr($dHt['jammulai'],14,2),1,0,'L');
        $pdf->Cell(20,5,'S e l e s a i',1,0,'L','1');
        $pdf->Cell(30,5,tanggalnormal(substr($dHt['jamselesai'],0,10)).' '.substr($dHt['jamselesai'],11,2).':'.substr($dHt['jamselesai'],14,2),1,0,'L');
        $pdf->Cell(25,5,'Lama Perb.',1,0,'L');
        $pdf->Cell(20,5,$dHt['jumlahjamperbaikan'],1,0,'L');
        $pdf->Cell(20,5,'Ketuntasan',1,0,'L');
        $pdf->Cell(30,5,$dHt['statusketuntasan'],1,1,'L');
 
        $pdf->Cell(30,5,'Pelaksana - M / E :',TL,0,'L');
        
        
        
        $iKarJum="select count(*) as jumlah from ".$dbname.".pabrik_predictivedt_karyawan where notransaksi='".$notran."' ";
        $nKarJum=  mysql_query($iKarJum) or die (mysql_error($conn));
        $dKarJum=mysql_fetch_assoc($nKarJum);
            $jumKar=$dKarJum['jumlah'];
        
        $iKar="select * from ".$dbname.".pabrik_predictivedt_karyawan where notransaksi='".$notran."' ";
        $nKar=  mysql_query($iKar) or die (mysql_error($conn));
        while($dKar=mysql_fetch_assoc($nKar))
        {
            $noKar+=1;
            if($noKar==$jumKar)
            {
                $separator='.';
            }
            else
            {
                $separator=',';
            }
            
            $whKar="karyawanid='".$dListKaryawan['karyawanid']."'";
            $tempKar.=$nmKar[$dKar['karyawanid']].$separator.' ';
            
        }
        
        $pdf->Cell(30,5,$tempKar,0,0,'L');
        $pdf->SetX(205);
        $pdf->Cell(1,5,'',L,1,'L');
        
        $akhirYnamaKar=$pdf->GetY();
      
        
        $pdf->SetFont('Arial','B',9);	
		$pdf->SetFillColor(220,220,220);
        $pdf->Cell(9,15,'No',1,0,'C',1);
		$pdf->Cell(60,15,'Item Check',1,0,'C',1);	
		$pdf->Cell(51,5,'K o n d i s i',1,1,'C',1);
        $pdf->SetX(79);
		$currenX=$pdf->GetX();
        $pdf->Cell(17,10,'Normal',1,0,'C',1);
        $pdf->Cell(17,5,'Perlu',TRL,0,'C',1);
        $pdf->Cell(17,10,'Rusak',1,1,'C',1);
        $akhirY=$pdf->GetY();
        $pdf->SetXY(96,$akhirY-5);	
		$pdf->Cell(17,5,'Diperbaiki',BRL,1,'C',1);
		$pdf->SetY($akhirYnamaKar);
		$pdf->SetX($currenX+51);
		$pdf->Cell(59,15,'Spare Part  Yang  Diganti',1,0,'C',1);
		$pdf->Cell(16,15,'Jumlah',1,1,'C',1);
        
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
		
		$iPekerjaan="select * from ".$dbname.".pabrik_predictivedt_pekerjaan where notransaksi='".$notran."' "
                . " order by nomor asc ";
        $nPekerjaan=  mysql_query($iPekerjaan) or die (mysql_error($conn));
		$arrPekerjaan = array();
		while($dPekerjaan=  mysql_fetch_assoc($nPekerjaan))
        {
			$noPkj+=1;
			$arrPekerjaan[$noPkj]['item'] = $dPekerjaan['rincian'];
			$arrPekerjaan[$noPkj]['kondisi'] = $dPekerjaan['kondisi'];
		}
		
		$iBarang="select * from ".$dbname.".pabrik_predictivedt where notransaksi='".$notran."' ";
        $nBarang=  mysql_query($iBarang) or die (mysql_error($conn));
        while($dBarang=  mysql_fetch_assoc($nBarang))
        {
			$noBarang+=1;
			$arrPekerjaan[$noBarang]['sparepart'] = $dBarang['kodebarang'].' - '.$nmBrg[$dBarang['kodebarang']];
			$arrPekerjaan[$noBarang]['jumlah'] = $dBarang['jumlah'];
		}
		
		
		$test=$pdf->GetY();
		for($noPekerjaan+=1;$noPekerjaan<=15;$noPekerjaan++){
			
			$height=5;
			$awalY=$pdf->GetY();
			$pdf->SetY($awalY);
			$pdf->SetX(1000);
			$pdf->MultiCell(60, $height, $arrPekerjaan[$noPekerjaan]['item'], '0', 'L');
			$akhirYPekerjaan=$pdf->GetY();
			
			$pdf->SetY($awalY);
			$pdf->SetX(1000);
			$pdf->MultiCell(59, $height, $arrPekerjaan[$noPekerjaan]['sparepart'], '0', 'L');
			$akhirYBarang=$pdf->GetY();
			
			$akhirY = max($akhirYPekerjaan,$akhirYBarang);
			$height2=$akhirY-$awalY;
			$pdf->SetY($awalY);
			
			if($akhirY == $akhirYPekerjaan){
				$multiHeightPekerjaan = $height;
			}else{
				$multiHeightPekerjaan = $height2;
			}
			
			if($akhirY == $akhirYBarang){
				$multiHeightBarang = $height;
			}else{
				$multiHeightBarang = $height2;
			}
			
			if(isset($arrPekerjaan[$noPekerjaan])){
				$currentX=$pdf->GetX();
				$pdf->Cell(9,$height2,'',1,0,'C',1);
				$pdf->Cell(60,$height2,'',1,0,'C',1);
				$pdf->Cell(17,$height2,'',1,0,'C',1);
				$pdf->Cell(17,$height2,'',1,0,'C',1);
				$pdf->Cell(17,$height2,'',1,0,'C',1);
				$pdf->Cell(59,$height2,'',1,0,'C',1);
				$pdf->Cell(16,$height2,'',1,1,'C',1);
				
				$pdf->SetY($awalY);
				$pdf->SetX($currentX);
				$pdf->MultiCell(9,$height,$noPekerjaan,0,'C');
				$pdf->SetY($awalY);
				$pdf->SetX($currentX+9);
				$currentX=$pdf->GetX();
				$pdf->MultiCell(60,$height,$arrPekerjaan[$noPekerjaan]['item'],0,'J');
				$pdf->SetY($awalY);
				$pdf->SetX($currentX+60);
				$currentX=$pdf->GetX();
				if($arrPekerjaan[$noPekerjaan]['kondisi']=='normal')
				{
					$pdf->Cell(17,$height2,'V',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
				}
				else if($arrPekerjaan[$noPekerjaan]['kondisi']=='perbaikan')
				{
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'V',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
				}
				else if($arrPekerjaan[$noPekerjaan]['kondisi']=='rusak')
				{
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'V',0,0,'C');
				}
				else
				{
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
					$pdf->Cell(17,$height2,'',0,0,'C');
				}
				$pdf->SetY($awalY);
				$pdf->SetX($currentX+51);
				$currentX=$pdf->GetX();
				$pdf->MultiCell(59,$height,$arrPekerjaan[$noPekerjaan]['sparepart'],0,'L');
				$pdf->SetY($awalY);
				$pdf->SetX($currentX+59);
				$currentX=$pdf->GetX();
				$pdf->MultiCell(16,$height,$arrPekerjaan[$noPekerjaan]['jumlah'],0,'C');
				$pdf->SetY($awalY);
				$pdf->Ln($height2);
			}else{
				$pdf->Cell(9,5,$noPekerjaan,1,0,'C',1);
				$pdf->Cell(60,5,'',1,0,'L',1);
				if($arrPekerjaan[$noPekerjaan]['kondisi']=='normal')
				{
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
				}
				else if($arrPekerjaan[$noPekerjaan]['kondisi']=='perbaikan')
				{
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
				}
				else if($arrPekerjaan[$noPekerjaan]['kondisi']=='rusak')
				{
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
				}
				else
				{
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
					$pdf->Cell(17,5,'',1,0,'C',1);
				}
				$pdf->Cell(59,5,'',1,0,'L',1);
				$pdf->Cell(16,5,'',1,1,'C',1);
			}
		}

		$akhirBarang=$pdf->GetY();
		if($akhirYJob>$akhirBarang)
        {
            $akhirDipakai=$akhirYJob;
        }
        else
        {
            $akhirDipakai=$akhirBarang;
        }
        
        $pdf->setY($akhirDipakai);
		
		$pdf->SetFont('Arial','BU',9); 
        $pdf->Cell(120,5,'Catatan Pekerjaan :','TRL',0,'L',1);
        $pdf->Cell(75,5,'Komentar / Saran - Mill Head Maintenance','TRL',1,'L',1);
		
		$awalYx=$pdf->GetY();
		$height=5;
		$pdf->SetY($awalYx);
		$pdf->SetX(1000);
		$pdf->MultiCell(120, $height, $dHt['hasilkerja'],0,'J');
		$akhirYHK=$pdf->GetY();
		
		$pdf->SetY($awalYx);
		$pdf->SetX(1000);
		$pdf->MultiCell(75, $height, $dHt['komentarmainten'],0,'J');
		$akhirYComment=$pdf->GetY();
		
		$akhirYx = max($akhirYHK,$akhirYComment);
		$height2=$akhirYx-$awalYx;
		
		$pdf->SetY($awalYx);
		$pdf->SetX(10);
		$pdf->Cell(120,$height2,'','LRB',0,'L',1);
		$pdf->Cell(75,$height2,'','LRB',0,'L',1);
		$pdf->Ln();
		$AkhirYA = $pdf->GetY();
		
        $pdf->SetFont('Arial','',9);
		
		$pdf->SetY($awalYx);
		$pdf->SetX(10);		
        $pdf->MultiCell(120,$height,$dHt['hasilkerja'],0,'J');
		$pdf->SetY($awalYx);
		$pdf->SetX(130);
        $pdf->MultiCell(75,$height,$dHt['komentarmainten'],0,'J');
        
		$pdf->SetY($AkhirYA);
		$ybaru=$pdf->GetY();
        //echo $akhirKometar;
        $pdf->SetFont('Arial','',9); 
        //$pdf->SetXY(90, $ybaru);
        $pdf->SetFont('Arial','BU',9); 
        $pdf->Cell(80,5,'Komentar / Saran - Mill Manager :',TRL,0,'L');
        $pdf->SetFont('Arial','',9); 
        $pdf->Cell(40,5,'M / E Maintenance',1,0,'C');
        $pdf->Cell(37.5,5,'Mill Head. Maintenance',1,0,'C');
        $pdf->Cell(37.5,5,'Mill Manager',1,0,'C');
        
        $yakhirJudul=$pdf->GetY()+5;
        $pdf->SetXY(10, $yakhirJudul);
        $pdf->Cell(80,25,'','BRL', 'T');
		$pdf->SetX(10);
        $pdf->MultiCell(80,5,$dHt['komentarproses'],0,'J');
        $pdf->SetXY(90, $yakhirJudul);
        $pdf->MultiCell(40,20,'','RB','T');
        $pdf->SetXY(130, $yakhirJudul);
        $pdf->MultiCell(37.5,20,'','RB', 'T');
        $pdf->SetXY(167.5, $yakhirJudul);
        $pdf->MultiCell(37.5,20,'','RB', 'T');
        
        $pdf->SetX(90);
        $pdf->Cell(40,5,'','RB',0,'C');
        $pdf->Cell(37.5,5,'','RB',0,'C');
        $pdf->Cell(37.5,5,'','RB',0,'C');
       
	$pdf->Output();
?>
