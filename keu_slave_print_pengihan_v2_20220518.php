<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/terbilang.php');
 
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$tglInv = "";


	
$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$nmPt=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//=============

//create Header
class PDF extends FPDF {
	function Header() {
		global $conn;
		global $dbname;
		global $userid;
		global $column;
		global $optnmakun;
		global $optnmcust;
		global $bar;
		global $nmPt;
		global $tglInv;
		
		$test=explode(',',$_GET['column']);
		$notransaksi=$test[0];
		$kodevhc = count($test)>1? $test[1]: '';
		$str="select * from ".$dbname.".".$_GET['table']."  where noinvoice='".$column."'";
                
		$res=mysql_query($str);
		$bar=mysql_fetch_object($res);
		$posting=$bar->posting;	
        $pt=$bar->kodept;
		$tglInv = $bar->tanggal;
		
		//ambil nama pt
		/*$str1="select * from ".$dbname.".organisasi where induk='MHO' and tipe='PT'"; 
		$res1=mysql_query($str1);
		while($bar1=mysql_fetch_object($res1)) {
			$namapt=$bar1->namaorganisasi;
			$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
			$telp=$bar1->telepon;				 
		}*/
		//$sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->updateby."'";
		//$query2=mysql_query($sql2) or die(mysql_error());
		//$res2=mysql_fetch_object($query2);
		
		//$sql5="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->postingby."'";
		//$query5=mysql_query($sql5) or die(mysql_error());
		//$res5=mysql_fetch_object($query5);
		
		//$sqlJnsVhc="select namajenisvhc from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$bar->jenisvhc ."'";
		//$qJnsVhc=mysql_query($sqlJnsVhc) or die(mysql_error());
		//$rJnsVhc=mysql_fetch_assoc($qJnsVhc);
		
		//$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->jenisbbm."'";
		//$qBrg=mysql_query($sBrg) or die(mysql_error());
		//$rBrg=mysql_fetch_assoc($qBrg);
		
				if($pt=='AMP'){
					$path='images/logo_amp.jpg';
				}else if($pt=='CKS'){
					$path='images/logo_cks.jpg';
				}else if($pt=='KAA'){
					$path='images/logo_kaa.jpg';
				}else if($pt=='KAL'){
					$path='images/logo_kal.jpg';
				}else if($pt=='LKA'){
					$path='images/logo_lka.jpg';
				}else if($pt=='MPA'){
					$path='images/logo_mpa.jpg';
				}else if($pt=='MHS'){
					$path='images/logo_mhs.jpg';
				}else if($pt=='MEA'){
					$path='images/logo_mea.jpg';
				}else if($pt=='SMA'){
					$path='images/logo_sma.jpg';
				}else{
					$path='images/logo.jpg';
				}

                $this->Image($path,15,5,20);
                
                $path2='images/Quality_ISO_9001.jpg';
                $this->Image($path2,160,5,40);
                
                $this->Ln(20);
                $this->SetFont('Arial','B',8);
                $this->Cell(60,5,$nmPt[$bar->kodept],0,1,'L');
	
                //logo_sma.png
                
                
		/*$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetX(55);   
                 $this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(55); 		
                $this->Cell(60,5,$alamatpt,0,1,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                */
                
		$this->Ln();
		$this->SetFont('Arial','B',12);
		$this->SetY(35);
		$this->Cell(190,5,'INVOICE',0,1,'C');	
                 	 
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
       // $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		//$this->Line(10,27,200,27);	
		$this->Ln(20);
	}
	
	function Footer() {
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    //$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}
}

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();


$height='5';

$iCust="select * from ".$dbname.".pmn_4customer where kodecustomer='".$bar->kodecustomer."' ";
$nCust=  mysql_query($iCust) or die (mysql_error($conn));
$dCust=mysql_fetch_assoc($nCust);

$pdf->SetFont('Arial','',8); 
$pdf->Ln();
$awalYKop=$pdf->GetY();
$pdf->Cell(80,$height,$dCust['namacustomer'],TLR,1,'L'); 

//echo $akhirYKop;
$pdf->MultiCell(80, $height,$dCust['alamat'], RL, 'L');
$pdf->Cell(80,$height,$dCust['telepon'],LR,1,'L'); 
$pdf->Cell(80,$height,'Attn. Finance Dept.',BLR,1,'L'); 

$akhirYKop=$pdf->GetY();

$selisihYKop=$akhirYKop-$awalYKop;

$heightKopKanan=$selisihYKop/3;

$pdf->SetXY(115, $awalYKop);
$pdf->Cell(30,$heightKopKanan,'No. Invoice',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,$bar->noinvoice,0,1,'L');  

$pdf->SetX(115);
$pdf->Cell(30,$heightKopKanan,'Tanggal',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,tanggalnormal($bar->tanggal),0,1,'L');    

$pdf->SetX(115);
$pdf->Cell(30,$heightKopKanan,'No. Kontrak',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,$bar->nokontrak,0,1,'L'); 

/*$pdf->SetX(115);
$pdf->Cell(30,$heightKopKanan,'No. Ba',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,'',0,1,'L'); 
*/
/*
$pdf->SetX(115);
$pdf->Cell(30,$height,'Penjualan',0,0,'L'); 
$pdf->Cell(5,$height,':',0,0,'L');
$pdf->Cell(30,$height,'INTI',0,1,'L'); 
*/
$pdf->Ln(20);

$pdf->Cell(15,$height,'No',1,0,'C'); 
$pdf->Cell(125,$height,'Deskripsi',1,0,'C'); 
$pdf->Cell(50,$height,'Jumlah (Rp)',1,1,'C'); 



$iKontrak=" select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$bar->nokontrak."' ";
$nKontrak=  mysql_query($iKontrak) or die (mysql_error($conn));
$dKontrak=  mysql_fetch_assoc($nKontrak);

$namaKomoditi= makeOption($dbname,'log_5masterbarang', 'kodebarang,namabarang','kelompokbarang=400');
$simbolMataUang=makeOption($dbname,'setup_matauang', 'kode,simbol');

$iTermin="select * from ".$dbname.".pmn_5terminbayar where kode='".$dKontrak['kdtermin']."' ";
$nTermin=  mysql_query($iTermin) or die (mysql_error($conn));
$dTermin=  mysql_fetch_assoc($nTermin);
    $terminSatu=$dTermin['satu'];

$sPem="select * from ".$dbname.".keu_penagihanht where tanggal<'".$tglInv."' and nokontrak='".$bar->nokontrak."'";
if(mysql_num_rows(mysql_query($sPem))>0){
	$termin=$dTermin['dua'];
}else{
	$termin=$dTermin['satu'];
}



if($dKontrak['ppn']==0)
{  
    $rpPpn="";
    $dKontrak['hargasatuan']=$dKontrak['hargasatuan'];
}
else
{ 
	if($dKontrak['tanggalkontrak']<='2022-03-31'){
	    $dKontrak['hargasatuan']=$dKontrak['hargasatuan']/1.10;
	}else{
	    $dKontrak['hargasatuan']=$dKontrak['hargasatuan']/1.11;
	}
}




if($dCust['statusberikat']==0)
{
    
    $rpPpn=$bar->nilaippn;
	if($dKontrak['tanggalkontrak']<='2022-03-31'){
	    $isiPpn="     PPN 10%";
	}else{
	    $isiPpn="     PPN 11%";
	}
}
else
{
	if($dKontrak['tanggalkontrak']<='2022-03-31'){
	    $isiPpn="     PPN 10%";
	}else{
	    $isiPpn="     PPN 11%";
	}
    $rpPpn="0";
    $isiBerikat="     ".$dCust['keteranganberikat'];
}

$totalKontrak=$dKontrak['hargasatuan']*$dKontrak['kuantitaskontrak'];
 
$persenpembayaran=$totalKontrak>0?$bar->nilaiinvoice/$totalKontrak*100:0;

 

/*$iBnyk="select count(*) as jumlah from ".$dbname.".keu_penagihanht where nokontrak='".$bar->nokontrak."' ";
$nBnyk=  mysql_query($iBnyk) or die (mysql_error($conn));
$dBnyk=  mysql_fetch_assoc($nBnyk);

    $bnykInv=$dBnyk['jumlah'];
    
    
 

    if($bnykInv>1)
    {
        $keterangan="Pelunasan Delivery";
    }
    else
    {
        $keterangan="Pembayaran";
    }*/
 //echo $bnykInv._.$keterangan;



//echo $totalKontrak;

$a=$termin;
$b=$dKontrak['kuantitaskontrak'];


$c=round($dKontrak['hargasatuan'],2);


$totalHarga=$bar->nilaiinvoice;


$iBnyk="select nilaiinvoice,kuantitas from ".$dbname.".keu_penagihanht where nokontrak='".$bar->nokontrak."' and noinvoice='".$bar->noinvoice."' ";
$nBnyk=  mysql_query($iBnyk) or die (mysql_error($conn));
$dBnyk=  mysql_fetch_assoc($nBnyk);
    $totalInv=$dBnyk['nilaiinvoice'];
    
//    $cekPersen;

//$termin=$dKontrak['kdtermin'];

$keterangan="Pembayaran (".$termin."%)";


$isideskripsi="     ".$keterangan."   untuk ".ucwords(strtolower($namaKomoditi[$bar->kodebarang]))."  ";
$isideskripsi.=" ".number_format($dBnyk['kuantitas'],2)." KG ";
$isideskripsi2="     @ ".$simbolMataUang[$dKontrak['matauang']]." ".number_format($dKontrak['hargasatuan'],2)." ";





$pdf->Cell(15,$height,'1',LR,0,'C'); 
$pdf->Cell(125,$height,$isideskripsi,LR,0,'L'); 
$pdf->Cell(40,$height,  number_format($totalHarga,0),L,0,'R'); 
$pdf->Cell(10,$height,'',R,1,'R');

$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,$isideskripsi2,RL,0,'L'); 
$pdf->Cell(40,$height,'',L,0,'C'); 
$pdf->Cell(10,$height,'',R,1,'R');


$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,$isiPpn,RL,0,'L'); 
$pdf->Cell(40,$height,  number_format($rpPpn),L,0,'R'); 
$pdf->Cell(10,$height,'',R,1,'R');


$pdf->Cell(15,$height,'',RL,0,'C'); 
//$pdf->Cell(125,$height,$isiBerikat,RL,0,'L'); 
$pdf->vcell(125,15,25,$isiBerikat,87); 
$pdf->Cell(40,$height,'',L,0,'L'); 
$pdf->Cell(10,$height,'',R,1,'R');


$optFaktur=  makeOption($dbname, 'pmn_faktur', 'noinvoice,nofaktur');

$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,'',RL,0,'L'); 
$pdf->Cell(40,$height,'',L,0,'R'); 
$pdf->Cell(10,$height,'',R,1,'R');

$subTot=$rpPpn+$totalHarga;


    $pdf->Cell(15,$height,'',RL,0,'C'); 
    $pdf->Cell(125,$height,'',RL,0,'L'); 
    $pdf->Cell(40,$height,'',L,0,'R'); 
    $pdf->Cell(10,$height,'',R,1,'R');





$pdf->SetFont('Arial','B',8); 


$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,'Sub Total :     ',LR,0,'R'); 

if($bar->matauang=='IDR')
{
    $pdf->Cell(40,$height,number_format($subTot,0),T,0,'R'); 
}
else
{
    $pdf->Cell(40,$height,number_format($subTot,2),T,0,'R');    
}
$pdf->Cell(10,$height,'',RT,1,'R');



    $pdf->Cell(15,$height,'',RL,0,'C'); 
    $pdf->Cell(125,$height,'',RL,0,'L'); 
    $pdf->Cell(40,$height,'',L,0,'R'); 
    $pdf->Cell(10,$height,'',R,1,'R');





//$pdf->Cell(15,$height,'',RL,0,'C'); 
 



$totalKalimKurangV=$bar->rupiah1+$bar->rupiah2+$bar->rupiah3+$bar->rupiah4+$bar->rupiah5+$bar->rupiah6+$bar->rupiah7;
$totalKlaimTambah=$bar->rupiah8;

$totalKlaim=$totalKalimKurangV-$totalKlaimTambah;

$absKlaim=abs($totalKlaim);

if($absKlaim!=0)
{
$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(4.5,$height,'',L,0,'L'); 
$pdf->SetFont('Arial','BU',9);
$pdf->Cell(120.5,$height,'Klaim',R,0,'L'); 
$pdf->SetFont('Arial','',8); 
$pdf->Cell(40,$height,'',L,0,'R'); 
$pdf->Cell(10,$height,'',R,1,'R');
}

for($i=1;$i<=7;$i++)
{
    $rupiah="rupiah$i";
    $keterangan="keterangan$i";  
   
        if($bar->$rupiah!=0)
        {
            if($i<=6)
            {
				if($i==6){
				    $pdf->Cell(15,$height,'',RL,0,'C'); 
					$pdf->Cell(125,$height,'     Klaim '.$bar->$keterangan,RL,0,'L'); 
					$pdf->Cell(40,$height,"(".number_format($bar->$rupiah).")",L,0,'R'); 
					$pdf->Cell(10,$height,'',R,1,'R');

				}else{
					$pdf->Cell(15,$height,'',RL,0,'C'); 
					$pdf->Cell(125,$height,'     Klaim mutu '.$bar->$keterangan.' di atas standar',RL,0,'L'); 
					$pdf->Cell(40,$height,"(".number_format($bar->$rupiah).")",L,0,'R'); 
					$pdf->Cell(10,$height,'',R,1,'R');
				}
            }
            else
            {
                $pdf->Cell(15,$height,'',RL,0,'C'); 
                $pdf->Cell(125,$height,'     Klaim kesusutan antara timbangan pabrik dengan sounding kapal',RL,0,'L'); 
                $pdf->Cell(40,$height,"(".number_format($bar->$rupiah).")",L,0,'R'); 
                $pdf->Cell(10,$height,'',R,1,'R');
            }
    }
    $totalKlaimPengurang+=$bar->$rupiah;
}

//echo $totalKlaim;
if($absKlaim!=0)
{
 if($bar->rupiah8!=0)
 {
	$pdf->Cell(15,$height,'',RL,0,'C'); 
	$pdf->Cell(125,$height,'     Klaim kelebihan timbangan',RL,0,'L'); 
	$pdf->Cell(40,$height,number_format($bar->rupiah8),L,0,'R'); 
	$pdf->Cell(10,$height,'',R,1,'R');
  }
}
$totalKlaim=$totalKlaimPengurang-$bar->rupiah8;
$angkappn='(11%)';
if($dKontrak['ppn']!=0){
	if($dKontrak['tanggalkontrak']<='2022-03-31'){
		$ppnKlaim=10/100*$totalKlaim;
		$angkappn='(10%)';
	}else{
		$ppnKlaim=11/100*$totalKlaim;
		$angkappn='(11%)';
	}
}
if($absKlaim!=0)
{
$txtTotal = ($totalKlaim<0)? number_format($totalKlaim*(-1)): "(".number_format($totalKlaim).")";
$pdf->SetFont('Arial','B',8); 
$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,'                    Total Klaim :     ',LR,0,'L'); 
$pdf->Cell(40,$height,  $txtTotal,T,0,'R'); 
$pdf->Cell(10,$height,'',RT,1,'R');
}

if($absKlaim!=0)
{
$txtPpn = ($ppnKlaim<0)? number_format($ppnKlaim*(-1)): "(".number_format($ppnKlaim).")";
$pdf->SetFont('Arial','B',8); 
$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,'                    PPN Klaim '.$angkappn.' :     ',LR,0,'L'); 
$pdf->Cell(40,$height,  $txtPpn,B,0,'R'); 
$pdf->Cell(10,$height,'',RB,1,'R');
}

$subTotKlaim=$totalKlaim+$ppnKlaim;

if($absKlaim!=0)
{
$txtSub = ($subTotKlaim<0)? number_format($subTotKlaim*(-1)): "(".number_format($subTotKlaim).")";
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(15,$height,'',RL,0,'C'); 
$pdf->Cell(125,$height,'Sub Total :     ',LR,0,'R'); 
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(40,$height,$txtSub,T,0,'R'); 
$pdf->Cell(10,$height,'',RT,1,'R');
}

$pdf->Cell(15,2*$height,'',RL,0,'C'); 
$pdf->Cell(125,2*$height,'',RL,0,'L'); 
$pdf->Cell(40,2*$height,'',LB,0,'R'); 
$pdf->Cell(10,2*$height,'',RB,1,'R');

$pdf->Cell(15,0.2*$height,'',RL,0,'C'); 
$pdf->Cell(125,0.2*$height,'',RL,0,'L'); 
$pdf->Cell(40,0.2*$height,'',LB,0,'R'); 
$pdf->Cell(10,0.2*$height,'',RB,1,'R');

$grandTotal=$subTot-$subTotKlaim;

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(15,1.5*$height,'',RLB,0,'C'); 
$pdf->Cell(125,1.5*$height,'Total :     ',LRB,0,'R'); 
$pdf->Cell(40,1.5*$height,number_format($grandTotal),'BL',0,'R'); 
$pdf->Cell(10,1.5*$height,'',RB,1,'R');

$pdf->Ln();
$pdf->SetFont('Arial','',8); 

$akhirYTerbilang=$pdf->GetY();

$pdf->Cell(30,$height,'Dalam huruf :',0,0,'L'); 




if($bar->matauang=='IDR')
{
$pdf->MultiCell(100, $height,  terbilang(round($grandTotal,0),'').' Rupiah',0,'J');
}
else
{
    $pdf->MultiCell(100, $height,  terbilang(round($grandTotal,2),'').' ',0,'J');
}
$pdf->SetFont('Arial','B',8); 
$pdf->SetXY(150, $akhirYTerbilang);
$pdf->Cell(30,$height,'Hormat Kami,',0,1,'L'); 

$pdf->SetX(150);
$pdf->Cell(50,2*$height,$nmPt[$bar->kodept],0,1,'C'); 

//$pdf->SetFont('Arial','',8); 

$iAkunBank=" select * from ".$dbname.".keu_5akunbank where pemilik='".$bar->kodept."' and noakun = '".$dKontrak['rekening']."' ";

$nAkunBank=  mysql_query($iAkunBank) or die (mysql_error($conn));
$dAkunBank=  mysql_fetch_assoc($nAkunBank);


$pdf->Cell(45,$height,'Mohon Ditransfer ke Acc.',0,0,'L'); 


$nmPtS=explode(".",$nmPt[$bar->kodept]);

$nmPtBawah=$nmPtS[0]." ".ucwords(strtolower($nmPtS[1]));



$pdf->Cell(90,$height,$nmPtBawah.' Acc: '.$dAkunBank['rekening'],1,1,'C'); 

$pdf->SetFont('Arial','',8); 
$pdf->Cell(45,1.5*$height,'Pada :',0,0,'L'); 
$pdf->Cell(90,1.5*$height,$dAkunBank['namabank'],1,1,'C'); 

$jbTtd=  makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');

$pdf->Ln();
$pdf->Ln();
$pdf->SetX(150);
$pdf->Cell(50,$height,$bar->ttd,0,1,'C'); 
$pdf->SetX(150);
$pdf->SetFont('Arial','B',8); 
$pdf->Cell(50,$height,$jbTtd[$bar->ttd],T,1,'C'); 

//ambil kelengkapan
/*$sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
$query3=mysql_query($sql3) or die(mysql_error());
$res3=mysql_fetch_object($query3); 

$pdf->Cell(30,4,$_SESSION['lang']['noinvoice'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->noinvoice,0,1,'L'); 				
$pdf->Cell(30,4,$_SESSION['lang']['jatuhtempo'],0,0,'L'); 
$pdf->Cell(40,4,": ".tanggalnormal($bar->jatuhtempo),0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['kodeorganisasi'],0,0,'L'); 
$pdf->Cell(40,4,": ".$res3->namaorganisasi." [".$bar->kodeorg."]",0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['nofaktur'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->nofakturpajak,0,1,'L'); 		  
$pdf->Cell(30,4,$_SESSION['lang']['tanggal'],0,0,'L'); 
$pdf->Cell(40,4,": ".tanggalnormal($bar->tanggal),0,1,'L'); 

$pdf->Cell(30,4,$_SESSION['lang']['bayarke'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->bayarke,0,1,'L');
$pdf->Cell(30,4,$_SESSION['lang']['kodecustomer'],0,0,'L'); 
$pdf->Cell(40,4,": ".$optnmcust[$bar->kodecustomer],0,1,'L');
$pdf->Cell(30,4,$_SESSION['lang']['uangmuka'],0,0,'L'); 
$pdf->Cell(40,4,": ".number_format($bar->uangmuka,2),0,1,'L');  
$pdf->Cell(30,4,$_SESSION['lang']['noorder'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->noorder,0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['nilaippn'],0,0,'L'); 
$pdf->Cell(40,4,": ".number_format($bar->nilaippn,2),0,1,'L');  
$pdf->Cell(30,4,$_SESSION['lang']['nilaiinvoice'],0,0,'L'); 
$pdf->Cell(40,4,": ".number_format($bar->nilaiinvoice,2),0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['debet'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->debet."-".$optnmakun[$bar->debet],0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['kredit'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->kredit."-".$optnmakun[$bar->kredit],0,1,'L'); 
$pdf->Cell(30,4,$_SESSION['lang']['keterangan'],0,0,'L'); 
$pdf->Cell(40,4,": ".$bar->keterangan,0,1,'L'); */

$pdf->Output();
?>