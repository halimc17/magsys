<?php
include_once('lib/fpdf.php');
include_once('lib/zLib.php');

class zPdfMaster extends FPDF {
    public $_align;
    public $_length;
    public $_colArr;
    public $_title;
    public $_noThead;
	public $_finReport = false;
    
    function zPdfMaster($ori,$unit,$format) {
        parent::FPDF($ori,$unit,$format);
        $this->_noThead = false;
    }
    
    function Header() {
        global $conn;
        global $dbname;
		global $bulan;
		global $tahun;
        
        # Alamat & No Telp
        $query = selectQuery($dbname,'organisasi','alamat,telepon',
            "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
        $orgData = fetchData($query);
        
        $sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'
            and tutupbuku = 0";
        $qPeriode=mysql_query($sPeriode) or die(mysql_error());
        $rPeriode=mysql_fetch_assoc($qPeriode);
        
        
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = 12;
		
		if($this->_finReport) {
			$this->SetFont('Arial','B',8);
			$this->Cell($width,$height,$_SESSION['org']['namaorganisasi'],0,1,'R');
			$this->SetFont('Arial','U',12);
			$this->Cell($width,$height,strtoupper($this->_title),0,1,'C');
			
			$this->Ln();
			//$this->Line($this->lMargin,$this->GetY(),$this->w - $this->rMargin,$this->GetY());
		} else {
				$kodept=$_SESSION['org']['kodeorganisasi'];
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
			$this->Image($path,$this->lMargin,$this->tMargin,0,45);	
			$this->SetFont('Arial','B',9);
			$this->SetFillColor(255,255,255);	
			$this->SetX(100);   
			$this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
			$this->SetX(100); 		
			$this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
			$this->SetX(100); 			
			$this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
			$this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
			$this->Ln($height*1.5);
			$this->SetFont('Arial','',12);
			$this->Cell($width,$height,strtoupper($this->_title),0,1,'C');
		}
        $this->SetFont('Arial','I',6);
		//$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
		//	":".$rPeriode['periode']." at ".date('d-m-Y H:i:s');
		//$this->Cell($width,$height,$str,'',1,'R');
        //$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
        //$this->Cell(5,$height,':','',0,'L');
        //$this->Cell(45/100*$width,$height,$_SESSION['empl']['lokasitugas'],'',0,'L');
        //$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
        //$this->Cell(5,$height,':','',0,'L');
        //$this->Cell(15/100*$width,$height,
        //    //numToMonth($bulan,'I','long')." ".
        //    //$tahun,0,0,'L');
        // $rPeriode['periode'],0,0,'L');
        //$this->Ln();
        //
        //$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
        //$this->Cell(5,$height,':','',0,'L');
        //$this->Cell(45/100*$width,$height,$_SESSION['standard']['username'],'',0,'L');
        //$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
        //$this->Cell(5,$height,':','',0,'L');
        //$this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');		
        //$this->Ln();
        
		$this->SetFont('Arial','',8);
        if($this->_noThead==false) {
            $this->Ln();
            $this->SetFont('Arial','B',9);	
            $this->SetFillColor(220,220,220);
            foreach($this->_colArr as $key=>$head) {
                if(isset($_SESSION['lang'][$head])) {
					$this->Cell($this->_length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
				} else {
					$this->Cell($this->_length[$key]/100*$width,$height,ucfirst($head),1,0,'C',1);
				}
            }
            $this->Ln();
        }
    }
    
    function Footer()
    {
		global $dbname;
		
        $sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'
            and tutupbuku = 0";
        $qPeriode=mysql_query($sPeriode) or die(mysql_error());
        $rPeriode=mysql_fetch_assoc($qPeriode);
        
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = 12;
		
		$this->SetY(-20);
        $this->SetFont('Arial','I',7);
		
        $this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
		$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
			":".$rPeriode['periode']." at ".date('d-m-Y H:i:s');
		$this->Cell($width-1,$height,$str,'T',0,'R');
    }
    function setAttr1($cTitle,$cAlign,$cLength,$cColArr) {
        $this->_align = $cAlign;
        $this->_length = $cLength;
        $this->_colArr = $cColArr;
        $this->_title = $cTitle;
    }
}
?>