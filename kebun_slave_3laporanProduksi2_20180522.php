<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
	$proses=$_POST['proses'];
}
else
{
	$proses=$_GET['proses'];
}

//$arr2="##periodeId##unitId";
$periode = checkPostGet('periodeId','');
$unit = checkPostGet('unitId','');
$intiplasma = checkPostGet('intiplasma',''); 



$optThnTanam=makeOption($dbname, 'setup_blok', 'kodeorg,tahuntanam');
if($periode=='')    
{
    exit("Error:Periode tidak boleh kosong");
}
$where="";
if($unit!='')
{
    $where=" and kodeorg='".$unit."'";
}


if($intiplasma!='')
{
    $inplas=" and intiplasma='".$intiplasma."'";
}

$brd='0';
$bgdt='';
$tab='';
if($proses=='excel')
{
    $brd=1;
    $bgdt="bgcolor=#DEDEDE align=center";
    $tab.="<table cellspacing=1 cellpadding=1 border=0>";
    $tab.="<tr><td colspan=11 >".$_SESSION['lang']['rProdKebundetail']."</td></tr>";
    $tab.="<tr><td colspan=11>".$_SESSION['lang']['unit']." : ".$unit."</td></tr>";
    $tab.="<tr><td colspan=11>".$_SESSION['lang']['periode']." : ".$periode."</td></tr></table>";
}


$tab.="<table cellspacing=1 cellpadding=1 border='".$brd."' class=sortable>";
$tab.="<thead><tr class=rowheader>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['nomor']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['nospb']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['kodeblok']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['tahuntanam']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['nopol']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['noTiket']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['jjg']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['kgwb']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['luasareaproduktif']."</td>";
$tab.="<td ".$bgdt.">".$_SESSION['lang']['jumlahpokok']."</td>";
$tab.="</tr><tbody>";
$tglTemp='';
$sData="select distinct * from ".$dbname.".kebun_spb_vw where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,blok asc";
$qData=mysql_query($sData) or die(mysql_error($sData));
$rowDta=mysql_num_rows($qData);
if($rowDta>0)
{
    $totJjg=$totKgwb=$totLuasprd=$totJmlh=0;
    $afdC=false;$blankC=false;
    $tglTemp='';
	$dtNo=0;
    while($rData=mysql_fetch_assoc($qData))
    {
           
            $sDtBlok="select distinct tahuntanam,luasareaproduktif ,jumlahpokok from ".$dbname.".setup_blok where kodeorg='".$rData['blok']."'";
            $qDtBlok=mysql_query($sDtBlok) or die(mysql_error($conn));
            $rDtBlok=mysql_fetch_assoc($qDtBlok);
            $totJjg+=$rData['jjg'];
            $totKgwb+=$rData['kgwb'];
            $totLuasprd+=$rDtBlok['luasareaproduktif'];
            $totJmlh+=$rDtBlok['jumlahpokok'];
            if($rData['tanggal']!=$tglTemp)
            {
                $afdC=false;
                $tglTemp=$rData['tanggal'];
            }
            $dtNo++;
            $tab.="<tr class=rowcontent>";
            
//            if($afdC==false)
//            {
//               
//                $tab.="<td>".$dtNo."</td>";
//                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
//                $afdC=true;
//                $blankC=false;
//               
//            }
//            else 
//            {
//                if($blankC==false)
//                {
//                    $sRow="select  count(tanggal) as jumlahbaris from ".$dbname.".kebun_spb_vw where kodeorg='".$unit."' and tanggal='".$rData['tanggal']."'";
//                    //exit("Error:".$sRow);
//                    //echo $sRow;
//                    $qRow=mysql_query($sRow) or die(mysql_error($conn));
//                    $rRow=mysql_fetch_assoc($qRow);
//                    $baris=$rRow['jumlahbaris']-1;
//                    $tab.="<td colspan=2 rowspan='".$baris."'>&nbsp;</td>";
//                    $blankC=true;
//                }
//            }
            
           $tab.="<td>".$dtNo."</td>";
            if($proses=='excel')
            {
                $tab.="<td>".$rData['tanggal']."</td>";
            }
            else
            {
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
            }
            $tab.="<td>".$rData['nospb']."</td>";
            $tab.="<td>".$rData['blok']."</td>";
            $tab.="<td align=center>".$rDtBlok['tahuntanam']."</td>";
            $tab.="<td>".$rData['nokendaraan']."</td>";
            $tab.="<td>".$rData['notiket']."</td>";
            $tab.="<td align=right>".$rData['jjg']."</td>";
            $tab.="<td align=right>".number_format($rData['kgwb'],2)."</td>";
            $tab.="<td align=right>".$rDtBlok['luasareaproduktif']."</td>";
            $tab.="<td align=right>".number_format($rDtBlok['jumlahpokok'],0)."</td>";
            $tab.="</tr>";
//            if($rData['tanggal']!=$tglTemp)
//            {
//                

//                        
//            }
//            else
//            {
//               
//            }
           
    }
}
else
{
    $tab.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['dataempty']."</td>";
}
    $tab.="<tr class=rowcontent><td colspan=7>".$_SESSION['lang']['total']."</td>";
    $tab.="<td align=right>".number_format($totJjg,0)."</td>";
    $tab.="<td align=right>".number_format($totKgwb,2)."</td>";
    // $tab.="<td align=right>".number_format($totLuasprd,2)."</td>";
    $tab.="<td align=right></td>";
    // $tab.="<td align=right>".number_format($totJmlh,0)."</td>";
    $tab.="<td align=right></td>";
    $tab.="</tr>";
    $tab.="</tbody></table>";


switch($proses)
{
	case'preview':
	echo $tab;
	break;
	case'pdf':

	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $tipeIntex;
                global $periode;
                global $unit;
                global $where;
                global $intiplasma;
				
				
				$tglPeriode=explode("-",$periode);
				$tanggal=$tglPeriode[1]."-".$tglPeriode[0];
                # Alamat & No Telp
       /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                    "kodeorganisasi='".$kdPt."'");
                $orgData = fetchData($query);*/
				$sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
				$qAlamat=mysql_query($sAlmat) or die(mysql_error());
				$rAlamat=mysql_fetch_assoc($qAlamat);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
				$this->Ln();
                $this->SetFont('Arial','B',9);
                $this->Cell($width,$height, $_SESSION['lang']['rProdKebundetail'],0,1,'C');	
			 	$this->SetFont('Arial','',8);
			 	$this->Cell($width,$height, "Periode : ".$tanggal,0,1,'C');	
				$this->Ln();$this->Ln();
                $this->SetFont('Arial','B',5);	
                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);		
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['kodeblok'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['nopol'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['noTiket'],1,0,'C',1);
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['jjg'],1,0,'C',1);
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);
                                $this->Cell(13/100*$width,$height,$_SESSION['lang']['luasareaproduktif'],1,0,'L',1);
				$this->Cell(12/100*$width,$height,$_SESSION['lang']['jumlahpokok'],1,1,'C',1);	            
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
        $height = 9;
		$pdf->AddPage();
                $pdf->SetFont('Arial','',6);
		$pdf->SetFillColor(255,255,255);
		// select distinct * from ".$dbname.".kebun_spb_vw where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,blok asc
        $sData="select distinct * from ".$dbname.".kebun_spb_vw where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,blok asc";
        $qData=mysql_query($sData) or die(mysql_error($sData));
        $rowDta=mysql_num_rows($qData);
		$dtr=0;
        if($rowDta>0)
        {
			$totJjg=$totKgwb=$totLuasprd=$totJmlh=0;
            while($rData=mysql_fetch_assoc($qData))
            {	
                $dtr++;
                $sDtBlok="select distinct tahuntanam,luasareaproduktif ,jumlahpokok from ".$dbname.".setup_blok where kodeorg='".$rData['blok']."'";
                $qDtBlok=mysql_query($sDtBlok) or die(mysql_error($conn));
                $rDtBlok=mysql_fetch_assoc($qDtBlok);
                $pdf->Cell(3/100*$width,$height,$dtr,1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);		
                $pdf->Cell(15/100*$width,$height,$rData['nospb'],1,0,'L',1);		
                $pdf->Cell(10/100*$width,$height,$rData['blok'],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,$rDtBlok['tahuntanam'],1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,$rData['nokendaraan'],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,$rData['notiket'],1,0,'L',1);
                $pdf->Cell(7/100*$width,$height,$rData['jjg'],1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,number_format($rData['kgwb'],0),1,0,'R',1);
                $pdf->Cell(13/100*$width,$height,number_format($rDtBlok['luasareaproduktif'],0),1,0,'R',1);
                $pdf->Cell(12/100*$width,$height,number_format($rDtBlok['jumlahpokok'],0),1,1,'R',1);
                $totJjg+=$rData['jjg'];
                $totKgwb+=$rData['kgwb'];
                $totLuasprd+=$rDtBlok['luasareaproduktif'];
                $totJmlh+=$rDtBlok['jumlahpokok'];
            }
            $pdf->SetFont('Arial','',5);
            $pdf->Cell(60/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
            $pdf->Cell(7/100*$width,$height,number_format($totJjg,2),1,0,'C',1);
            $pdf->Cell(7/100*$width,$height,number_format($totKgwb,2),1,0,'R',1);
            $pdf->Cell(13/100*$width,$height,"",1,0,'R',1);
            $pdf->Cell(12/100*$width,$height,"",1,1,'R',1);
        }
        else
        {
            $pdf->Cell(99/100*$width,$height,$_SESSION['lang']['dataempty'],1,1,'L',1);
        }
			
    $pdf->Output();
	break;
	case'excel':
             $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $tglSkrg=date("Ymd");
            $nop_="LaporanProduksiDetail__".$unit."_".$periode;
            if(strlen($tab)>0)
            {
            if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
            }
            }	
            closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
            echo "<script language=javascript1.2>
            parent.window.alert('Can't convert to excel format');
            </script>";
            exit;
            }
            else
            {
            echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls';
            </script>";
            }
            fclose($handle);
            }
	break;
	default:
	break;
}
?>