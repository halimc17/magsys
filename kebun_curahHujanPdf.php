<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/fpdf.php');

$proses = $_GET['proses'];
$kdOrg=$_GET['kdOrg'];
$tgl=explode('-',$_GET['daTtgl']);
$tngl=$tgl[2]."-".$tgl[1];

$param = $_POST;
//$where=" kodeorg='".$kdOrg."' and tanggal like '%".$tngl."%'";


/** Report Prep **/
$cols = 'nourut,tanggal,pagi,sore,malam,note';
$colArr = explode(',',$cols);

//$query = selectQuery($dbname,'kebun_curahhujan','kodeorg, tanggal, pagi, sore, catatan',$where);
//$data = fetchData($query);

$title = $_SESSION['lang']['curahHujan'];
$align = explode(",","L,L,R,R,L");
$length = explode(",","10,15,20,20,35");

/** Output Format **/
switch($proses) {
    case 'pdf':
        class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $kdOrg;
				global $tngl;
				global $tgl;
                
                # Bulan
               // $optBulan = 
                
                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,45);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(80);   
                $this->Cell($width-80,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(80); 		
                $this->Cell($width-80,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(80); 			
                $this->Cell($width-80,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
                $this->SetFont('Arial','',8);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$_SESSION['empl']['lokasitugas'],'',0,'L');
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,
                   $_SESSION['standard']['username'],0,0,'L');
                $this->Ln();
				$query2 = selectQuery($dbname,'organisasi','namaorganisasi',
				"kodeorganisasi='".$kdOrg."'");
				$orgData2 = fetchData($query2);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kebun'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$orgData2[0]['namaorganisasi'],'',0,'L');
              	$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');		
                
                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,$title,0,1,'C');	
                $this->Ln();	
                $this->SetFont('Arial','B',9);	
                $this->SetFillColor(220,220,220);
			   // $this->Cell(10/100*$width,$height,'No',1,0,'C',1);
				
				$this->Cell(5/100*$width,$height,$_SESSION['lang']['nourut'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['pagi'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['sore'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['malam'],1,0,'C',1);
				$this->Cell(35/100*$width,$height,$_SESSION['lang']['catatan'],1,1,'C',1);
			   
                // foreach($colArr as $key=>$head) {
                    // $this->Cell($length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                // }
                // $this->Ln();
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',9);

		$sTgl="select tanggal from ".$dbname.".kebun_curahhujan where tanggal  like '%".$tngl."%' order by tanggal asc";
		$qTgl=mysql_query($sTgl) or die(mysql_error());
		$rTgl=mysql_fetch_assoc($qTgl);
	
		$sTgl2="select tanggal from ".$dbname.".kebun_curahhujan where tanggal  like '%".$tngl."%' order by tanggal desc";
		$qTgl2=mysql_query($sTgl2) or die(mysql_error());
		$rTgl2=mysql_fetch_assoc($qTgl2);
	
		/*$sql="select * from ".$dbname.".kebun_curahhujan where kodeorg='".$kdOrg."' or  (tanggal between ".$rTgl['tanggal']." and ".$rTgl2['tanggal'].") ";
		//echo "warning".$sql;exit();
		$query=mysql_query($sql) or die(mysql_error());
		while($res=mysql_fetch_assoc($query))
		{*/
		//$no+=1;
			$ts=mktime(0,0,0,$tgl[1],1,$tgl[2]);
			$jmlhHari=intval(date("t",$ts));
			//$tglDb=tanggalnormal($res['tanggal']);
			//echo"warning:".$jmlhHari;exit();
			$i=0;
			for($a=1;$a<=$jmlhHari;$a++)
			{
					$i+=1;
					if(strlen($a)<2)
					{
						$a="0".$a;
					}
					$tglProg=$a."-".$tgl[1]."-".$tgl[2];
			
					$sql="select * from ".$dbname.".kebun_curahhujan where kodeorg='".$kdOrg."' and tanggal='".tanggalsystem($tglProg)."'  "; 
					//echo "warning:".$sql."__".$tglProg;exit();
					$query=mysql_query($sql) or die(mysql_error());
					$res=mysql_fetch_assoc($query);
					
						$pdf->Cell(5/100*$width,$height,$i,1,0,'L',1);
						$pdf->Cell(15/100*$width,$height,$tglProg,1,0,'L',1);
						$pdf->Cell(15/100*$width,$height,$res['pagi'],1,0,'R',1);
						$pdf->Cell(15/100*$width,$height,$res['sore'],1,0,'R',1);
						$pdf->Cell(15/100*$width,$height,$res['malam'],1,0,'R',1);
						$pdf->Cell(35/100*$width,$height,$res['catatan'],1,1,'L',1);	
					
				}
				$pdf->Cell((20/100*$width)-5,$height,$_SESSION['lang']['ketCurahHUjan'],'',0,'L');
				
		//}
	/*	foreach($data as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
				$pdf->Cell(10/100*$width,$height,$i,1,0,0,1);
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }*/
	
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>