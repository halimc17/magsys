<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$kdsup=$_POST['kdsup'];
$kdorg=$_POST['kdorg'];
//exit("$kdsup");
$tgl1=$_POST['tgl1'];
$tgl2=$_POST['tgl2'];
if(($proses=='excel')or($proses=='pdf')){

        $kdsup=$_GET['kdsup'];
        $tgl1=$_GET['tgl1'];
        $tgl2=$_GET['tgl2'];
        $kdorg=$_GET['kdorg'];

}
$proses=checkPostGet('proses','');
$kdsup=checkPostGet('kdsup','');
$nokontrak=checkPostGet('nokontrak','');
$tgl1=  tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
$pt=checkPostGet('pt','');


$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$opttransport=  makeOption($dbname, 'pmn_5transportir', 'nopol,transportir');
$optnmtransport = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');



    if($tgl1=='--' or $tgl2=='--')
        {
                echo" Error: Tanggal tidak boleh kosong"; 
                exit();
    }

    else if($tgl1>$tgl2)
        {
        echo" Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
                exit();
    }

if($tgl1=='--'){
    $tgl1=date('Ymd');
}
if($tgl2=='--'){
    $tgl2=date('Ymd');
}


if($nokontrak!='')
{
    $nokontraksort="and nosipb like '%".$nokontrak."%'";#karena form diganti menjadi no DO maka dipake kolom nosipb, jika kembalo ke kontrak, ganti lagi menjadi nokontrak
}

if($kdsup!='')
{
    $kdsupsort="and nokendaraan in (select nopol from ".$dbname.".pmn_5transportir where transportir='".$kdsup."' )";
}

$add='';
if($pt!=''){
    $add=" and millcode in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
}

##ambil data SQL untuk global view 
//$str=" SELECT * FROM ".$dbname.".vhc_penggantianht where kodevhc='".$kdorgs."'";
//$str="select a.kodecustomer,a.beratbersih as netto,a.substr(tanggal,1,10) as tanggal from ".$dbname.".pabrik_timbangan join where tanggal between '".$tgl1."' and '".$tgl2."'";


              if($proses=='excel')$stream="<table cellspacing='1' border='1' class='sortable' bgcolor=#CCCCCC>";
                else $stream.="<table cellspacing='1' border='0' class='sortable'>";
                $stream.="<thead class=rowheader>
                 <tr>
                                        <td align=center>No</td>
                                        <td align=center>".$_SESSION['lang']['notransaksi']."</td>
                                        <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                        <td align=center>".$_SESSION['lang']['nopol']."</td>
                                        <td align=center>".$_SESSION['lang']['NoKontrak']."</td>
                                        <td align=center>".$_SESSION['lang']['nodo']."</td>  
                                        <td align=center>Transportir</td>      
                                        <td align=center>".$_SESSION['lang']['kodecustomer']."</td>    
                                        <td align=center>".$_SESSION['lang']['beratBersih']."<br />(Kg)</td>    
                                </tr></thead>
                <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
$str=" SELECT * FROM ".$dbname.".pabrik_timbangan "
        . " WHERE tanggal>='".$tgl1." 00:00:00' and tanggal<='".$tgl2." 23:59:59'  "
        . " ".$nokontraksort." ".$kdsupsort." ".$add." order by tanggal ";

$res=mysql_query($str) or die (mysql_error($conn));	
while($bar=mysql_fetch_assoc($res))
{
                    //$stream.="<tr bgcolor=#FFFFFF>";
    $stream.="<tr class=rowcontent>";
    $no+=1;
    $stream.="
    <td>".$no."</td>
    <td>".$bar['notransaksi']."</td>
    <td>".tanggalnormal($bar['tanggal'])."</td>
    <td>".$bar['nokendaraan']."</td>
    <td align=right>".$bar['nokontrak']."</td>
    <td align=right>".$bar['nosipb']."</td>
        <td align=right>".$optnmtransport[$opttransport[$bar['nokendaraan']]]."</td>



    <td align=right>".$optnamacostumer[$bar['kodecustomer']]."</td>    
    <td align=right>".number_format($bar['beratbersih'],2)." Kg</td>
    </tr>";		

    #untuk total
    $tonetto+=$bar['beratbersih'];

}

$stream.="
    <thead><tr>
            <td align=center colspan=8>Total</td>
            <td align=right>".number_format($tonetto,2)." Kg</td>     
    </tr>
</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
        case 'preview':
                echo $stream;
    break;

######EXCEL	
        case 'excel':
                $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
                $tglSkrg=date("Ymd");
                $nop_="LAPORAN_HARGA_TBS".$tglSkrg;
                if(strlen($stream)>0)
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
                        if(!fwrite($handle,$stream))
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



###############	
#panggil PDFnya
###############

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
                                                        global $kdorg;
                                                        global $kdAfd;
                                                        global $tgl1;
                                                        global $tgl2;
                                                        global $where;
                                                        global $nmOrg;
                                                        global $lok;
                                                        global $notrans;


                            //$cols=247.5;
                            $query = selectQuery($dbname,'organisasi','alamat,telepon',
                                "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                            $orgData = fetchData($query);

                            $width = $this->w - $this->lMargin - $this->rMargin;
                            $height = 20;
                            $path='images/logo.jpg';
                            //$this->Image($path,$this->lMargin,$this->tMargin,50);	
                                                        $this->Image($path,30,15,55);
                            $this->SetFont('Arial','B',9);
                            $this->SetFillColor(255,255,255);	
                            $this->SetX(90); 

                            $this->Cell($width-80,12,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                            $this->SetX(90); 		
                                        $this->SetFont('Arial','',9);
                                                        $height = 12;
                            $this->Cell($width-80,$height,$orgData[0]['alamat'],0,1,'L');	
                            $this->SetX(90); 			
                            $this->Cell($width-80,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                            $this->Ln();
                            $this->Line($this->lMargin,$this->tMargin+($height*4),
                            $this->lMargin+$width,$this->tMargin+($height*4));

                            $this->SetFont('Arial','B',12);
                            $this->Ln();
                            $height = 15;
                            $this->Cell($width,$height,"Laporan Harga TBS ".$kdorg,'',0,'C');
                            $this->Ln();
                            $this->SetFont('Arial','',10);
                            $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ". tanggalnormal($tgl1)." S/D ". tanggalnormal($tgl2),'',0,'C');
                                                                        //$this->Ln();
                            $this->Ln(30);
                            $this->SetFont('Arial','B',7);
                            $this->SetFillColor(220,220,220);
                            $this->Cell(3/100*$width,15,substr($_SESSION['lang']['nomor'],0,2),1,0,'C',1);		
                            $this->Cell(15/100*$width,15,'Supplier',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Tanggal',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'BJR',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Harga Satuan',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Netto',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Sortasi',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Berat Normal',1,0,'C',1);
                            $this->Cell(10/100*$width,15,'Total',1,1,'C',1);	



                                                                                        //$this->Ln();
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
                    $height = 15;
                            $pdf->AddPage();
                            $pdf->SetFillColor(255,255,255);
                            $pdf->SetFont('Arial','',7);

                $res=mysql_query($str);//tinggal tarik $res karna sudah di declarasi di atas
                $no=0;
                $ttl=0;
                while($bar=mysql_fetch_assoc($res))
                {	

                        $bjr=$bar['bjr'];
        //echo $bjr._;
        if($bjr>=3 && $bjr<5)
        {
                $a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='1' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
        }
        else if($bjr>=5 && $bjr<7)
        {
                $a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='2' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
                //echo $a;
        }
        else
        {
                $a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='3' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
                //echo $a;
        }
        $b=mysql_query($a);
        $c=mysql_fetch_assoc($b);
        //$harga=$c['harga'];

        $la="select disticnt kodetimbangan from ".$dbname.".log_5supplier";
        //echo $la;
        $li=mysql_query($la);
        $lu=mysql_fetch_assoc($li);
                $supz=$lu['kodetimbangan'];

        if($supz=='')
        {
                $sNm="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$bar['kodecustomer']."'  ";
        }
        else
        {
                $sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$bar['kodecustomer']."'  ";
        }	
        $qNm=mysql_query($sNm) or die(mysql_error());
        $rNm=mysql_fetch_assoc($qNm);
                $nm=$rNm['namasupplier'];

                                $beratnormal=$bar['netto']-$bar['kgpotsortasi'];//and supplierid='".$bar['kodecustomer']."'
                $total=$c['harga']*$beratnormal;//<td>".$optnamacostumer[$bar['kodecustomer']]."</td>



                                //echo $sNm;			
                        $no+=1;	
                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);	
                        $pdf->Cell(15/100*$width,$height,$nm,1,0,'L',1);		
                        $pdf->Cell(10/100*$width,$height,tanggalnormal($bar['tanggal']),1,0,'L',1);		
                        $pdf->Cell(10/100*$width,$height,number_format($bar['bjr'],2),1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,number_format($c['harga']),1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,$bar['netto'],1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,$bar['kgpotsortasi'],1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,$beratnormal,1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,number_format($total),1,1,'R',1);	

                $tonetto+=$bar['netto'];
                $tosortasi+=$bar['kgpotsortasi'];
                $toberatnormal+=$beratnormal;
                $tototal+=$total;
                                                                                        /*
                                <td>".$no."</td>
                                <td>".$nm."</td>
                                <td>".tanggalnormal($bar['tanggal'])."</td>
                                <td align=right>".number_format($bar['bjr'],2)."</td>
                                <td align=right>".number_format($c['harga'])."</td>
                                <td align=right>".number_format($bar['netto'],2)."</td>

                                <td align=right>".$bar['kgpotsortasi']."</td>
                                <td align=right>".$beratnormal."</td>
                                <td align=right>".number_format($total)."</td>


                                                                                        */


                /*
                $tonetto+=$bar['netto'];
                $tosortasi+=$bar['kgpotsortasi'];
                $toberatnormal+=$beratnormal;
                $tototal+=$total;

        }
        $stream.="
                                <thead><tr>
                                        <td align=center colspan=5>Total</td>
                                        <td align=right>".number_format($tonetto,2)."</td>
                                        <td align=right>".number_format($tosortasi,2)."</td>
                                        <td align=right>".number_format($toberatnormal,2)."</td>
                                        <td align=right>".number_format($tototal)."</td>

                */
                //$totnet+=$bar['netto'];

                }
                        $pdf->SetFillColor(220,220,220);
                        //$pdf->SetFont('arial','B',10);
                        $pdf->Cell(48/100*$width,$height,strtoupper('Total'),1,0,'C',1);

                        $pdf->Cell(10/100*$width,$height,number_format($tonetto,2),1,0,'R',1);	
                        $pdf->Cell(10/100*$width,$height,number_format($tosortasi,2),1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,number_format($toberatnormal,2),1,0,'R',1);
                        $pdf->Cell(10/100*$width,$height,number_format($tototal),1,1,'R',1);
//		

                $pdf->Output();

        break;



        default:
        break;
}

?>