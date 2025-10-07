<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

//=============

//create Header
class PDF extends FPDF {
	function Header() {
        global $conn;
        global $dbname;
		global $userid;
        global $posted;
        global $tanggal;
        global $norek_sup;
        global $cpsn;
        global $npwp_sup;
        global $nm_kary;
        global $nm_pt;
        global $nmSupplier;
        global $almtSupplier;
        global $tlpSupplier;
        global $faxSupplier;
        global $nopo;
        global $tglPo;
        global $kdBank;
        global $an;
        global $arrlp;
        global $lokalpusat;
        global $nmlokalpusat;
            global $optNmkry;
            global $kotasup;
                
                $arrlp=array("0"=>" ","1"=>" LOKAL");
		$optNmkry=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

		$str="select kodeorg,kodesupplier,purchaser,nopo,tanggal,lokalpusat from ".$dbname.".log_poht  where nopo='".$_GET['column']."'";
		//echo $str;exit();
		$res=mysql_query($str);
		$bar=mysql_fetch_object($res);

		//ambil nama pt
		if($bar->kodeorg=='')
		{
			   $bar->kodeorg=$_SESSION['org']['kodeorganisasi']; 
		}
		$str1="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
		$res1=mysql_query($str1);
		while($bar1=mysql_fetch_object($res1))
		{
			$namapt=$bar1->namaorganisasi;
			$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
			$telp=$bar1->telepon;				 
		} 
		$sNpwp="select npwp,alamatnpwp,alamatdomisili from ".$dbname.".setup_org_npwp where kodeorg='".$bar->kodeorg."'";
		$qNpwp=mysql_query($sNpwp) or die(mysql_error());
		$rNpwp=mysql_fetch_assoc($qNpwp);
		$alamatpt = $rNpwp['alamatdomisili'];
		
		$sql="select * from ".$dbname.".log_5supplier where supplierid='".$bar->kodesupplier."'"; //echo $sql;
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_object($query);

		$sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->purchaser."'";
		$query2=mysql_query($sql2) or die(mysql_error());
		$res2=mysql_fetch_object($query2);

		$sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
		$query3=mysql_query($sql3) or die(mysql_error());
		$res3=mysql_fetch_object($query3); 

		$norek_sup=$res->rekening;
		$kdBank=$res->bank;
		$npwp_sup=$res->npwp;
                $kotasup=$res->kota;
               
		$an=$res->an;   
		$nm_kary=$res2->namakaryawan;
		$nm_pt=$res3->namaorganisasi;
		//data PO
		$nopo=$bar->nopo;
                $lokalpusat=$bar->lokalpusat;
                $nmlokalpusat=$arrlp[$lokalpusat];
		$tglPo=tanggalnormal($bar->tanggal);
		//data supplier
		$nmSupplier=$res->namasupplier;
		$almtSupplier=$res->alamat;
		$tlpSupplier=$res->telepon;
		$faxSupplier=$res->fax;
                $cpsn=$res->kontakperson;

        $this->SetMargins(15,10,0);
		//$path='images/logo.jpg';
		$kodept=$bar->kodeorg;
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

		$this->Image($path,15,5,0,30);	
		$this->SetFont('Arial','B',9);
		$this->SetFillColor(255,255,255);	
		$this->SetX(55);   
		$this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(55); 		
		$this->MultiCell(120,5,$alamatpt,0,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->SetFont('Arial','B',7);
		$this->SetX(55); 			
		$this->Cell(60,5,"NPWP: ".$rNpwp['npwp'],0,1,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,$_SESSION['lang']['alamat']." NPWP: ".$rNpwp['alamatnpwp'],0,1,'L');
		$currY = $this->GetY();
		$this->Line(15,$currY,205,$currY);	
		$this->SetFont('Arial','',6); 	
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');
    }
	
    function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}
}

$pdf=new PDF('P','mm','A4');
$height=4;
$pdf->AddPage();
$pdf->SetFont('Arial','B',8);	
if($_SESSION['language']=='EN'){
	$pdf->Cell(30,4,"TO :",0,0,'L');
}else{
	$pdf->Cell(30,4,"KEPADA YTH :",0,0,'L'); 
}
$pdf->Ln();


$noPp=  makeOption($dbname, 'log_podt', 'nopo,nopp');


$pdf->Cell(35,4,$_SESSION['lang']['nm_perusahaan'],0,0,'L'); 
$pdf->Cell(40,4,": ".$nmSupplier,0,1,'L'); 				
$pdf->Cell(35,4,$_SESSION['lang']['alamat'],0,0,'L'); 
$pdf->Cell(40,4,": ".ucfirst($almtSupplier.' '.$kotasup),0,1,'L'); 		  
$pdf->Cell(35,4,$_SESSION['lang']['telp'],0,0,'L'); 
$pdf->Cell(40,4,": ".$tlpSupplier,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['fax'],0,0,'L'); 
$pdf->Cell(40,4,": ".$faxSupplier,0,1,'L'); 
//$pdf->Cell(35,4,$_SESSION['lang']['namabank'],0,0,'L'); 
//$pdf->Cell(40,4,": ".ucfirst(strtolower($kdBank))." ".$kdBank,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['cperson'],0,0,'L'); 
$pdf->Cell(40,4,": ".$cpsn,0,1,'L'); 
//$pdf->Cell(35,4,$_SESSION['lang']['npwp'],0,0,'L'); 
//$pdf->Cell(40,4,": ".$npwp_sup,0,1,'L'); 
$pdf->SetFont('Arial','BU',12);
$ar=$pdf->GetY();
$pdf->SetY($ar+5);
$pdf->Cell(190,5,strtoupper("Purchase Order")."".$nmlokalpusat,0,1,'C');
$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,4,$nopo,0,1,'C'); 
$pdf->SetY($ar+22);
$pdf->SetFont('Arial','',8);	

$pdf->Cell(10,4,"No. PP",0,0,'L'); 
$pdf->Cell(20,4,": ".$noPp[$nopo],0,0,'L');    

if($lokalpusat=='0')
{
    $pdf->SetX(93); 
}
else
{
    $pdf->SetX(90); 
}




$pdf->SetX(163);
$pdf->Cell(20,4,"Tanggal PO.",0,0,'L'); 
$pdf->Cell(20,4,": ".$tglPo,0,0,'L'); 
$pdf->SetY($ar+27);
$pdf->SetFont('Arial','B',8);	
$pdf->SetFillColor(220,220,220);
$pdf->Cell(8,5,'No',1,0,'L',1);
$pdf->Cell(15,5,$_SESSION['lang']['kodeabs'],1,0,'C',1);	
$pdf->Cell(84,5,$_SESSION['lang']['namabarang'],1,0,'C',1);
		
$pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
$pdf->Cell(14,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
//$pdf->Cell(15,5,$_SESSION['lang']['kurs'],1,0,'C',1);
$pdf->Cell(29,5,$_SESSION['lang']['hargasatuan'],1,0,'C',1);
$pdf->Cell(26,5,'Total',1,1,'C',1);

$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',8);

$str="select a.*,b.kodesupplier,b.subtotal,b.diskonpersen,b.tanggal,b.nilaidiskon,b.ppn,b.nilaipo,b.tanggalkirim,b.lokasipengiriman,b.uraian,b.matauang from ".$dbname.".log_podt a inner join ".$dbname.".log_poht b on a.nopo=b.nopo  where a.nopo='".$_GET['column']."'";
//echo $str;exit();
$re=mysql_query($str);
$no=0;$i=0;
while($bar=mysql_fetch_object($re))
{
    $no+=1;

	$kodebarang=$bar->kodebarang;
	$jumlah=$bar->jumlahpesan;
	$harga_sat=$bar->hargasbldiskon;
	$total=$jumlah*$harga_sat;
	$unit=substr($bar->nopp,15,4);
	$namabarang='';

	$strv="select b.spesifikasi from  ".$dbname.".log_5photobarang b  where b.kodebarang='".$bar->kodebarang."'"; //echo $strv;exit();	
	$resv=mysql_query($strv);
	$barv=mysql_fetch_object($resv);

	if(!empty($barv->spesifikasi)) {
		$spek=$barv->spesifikasi;					
	} else {
		$spek="";
	}
	$nopp=substr($bar->nopp,0,3);
	$sSat="select satuan,namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
	$qSat=mysql_query($sSat) or die(mysql_error());
	$rSat=mysql_fetch_assoc($qSat);
	$satuan=$rSat['satuan'];
	$namabarang=$rSat['namabarang'];
    $i++;

    if($no!=1) {
        $pdf->SetY($akhirY);
    }
	$posisiY=$pdf->GetY();
	$pdf->Cell(8,4,$no,0,0,'L',0);
	$pdf->SetX($pdf->GetX());
	$pdf->Cell(15,5,$bar->kodebarang,0,0,'C',0);
        
        if($spek=='' && $bar->catatan=='')
        {
            $pdf->MultiCell(84,5,$namabarang,0,'J',0);
        }
        else if ($spek!='' && $bar->catatan=='')
        {
            $pdf->MultiCell(84,5,$namabarang."\n".$spek,0,'J',0);
        }
        else if ($spek=='' && $bar->catatan!='')
        {
            $pdf->MultiCell(84,5,$namabarang."\n".$bar->catatan,0,'J',0);
        }
        else
        {
            $pdf->MultiCell(84,5,$namabarang."\n".$spek."\n".$bar->catatan,0,'J',0);
        }
        $akhirY=$pdf->GetY();

	$pdf->SetY($posisiY);
	$pdf->SetX($pdf->GetX()+106);

	
	$pdf->Cell(15,5,number_format($jumlah,2,'.',','),0,0,'R',0);
    $pdf->Cell(14,5,$bar->satuan,0,0,'C',0);
	if($bar->matauang=='IDR'){
		$pdf->Cell(29,5,$bar->matauang." ".number_format($harga_sat,2,'.',','),0,0,'R',0);
		$pdf->Cell(26,5,number_format($total,2,'.',','),0,1,'R',0);
	} else {
		$pdf->Cell(29,5,$bar->matauang." ".number_format($harga_sat,2,'.',','),0,0,'R',0);
		$pdf->Cell(26,5,number_format($total,2,'.',','),0,1,'R',0);
    }
	if($pdf->GetY() > 250) {
		$i=0;
		$akhirY=$akhirY-20;
		$akhirY=$pdf->GetY()-$akhirY;
		$akhirY=$akhirY+35;
		$pdf->AddPage();
    }
}
$akhirSubtot=$pdf->GetY();
$pdf->SetY($akhirY);
$slopoht="select * from ".$dbname.".log_poht where nopo='".$_GET['column']."'";
$qlopoht=mysql_query($slopoht) or die(mysql_error());
$rlopoht=mysql_fetch_object($qlopoht);
$sb_tot=$rlopoht->subtotal;
$nil_diskon=$rlopoht->nilaidiskon;
$npbbkb=$rlopoht->pbbkb;
$npph=$rlopoht->pph;
$nppn=$rlopoht->ppn;
$stat_release=$rlopoht->stat_release ;
$user_release=$rlopoht->useridreleasae;
$gr_total=(($sb_tot-$nil_diskon)+$npbbkb)+$nppn-$npph;

$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$rlopoht->syaratbayar."'";
$qSyp=mysql_query($sSyp) or die(mysql_error($conn));
$rSyp=mysql_fetch_object($qSyp);
//$rlopoht->uraian
$pdf->Cell(133,5,$_SESSION['lang']['keterangan'].' :','T',1,'L',1);
$akhirYKet=$pdf->GetY();

$pdf->SetY($akhirYKet-4.5);
$pdf->SetX(32);
$pdf->MultiCell(120,4,$rlopoht->uraian,0,1,'J',0);
$yakhiruraian=$pdf->GetY();
$pdf->SetY($akhirY);
$pdf->SetX($pdf->GetX()+131);
$pdf->Cell(32,5,$_SESSION['lang']['subtotal'],'T',0,'L',1);	
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,number_format($rlopoht->subtotal,2,'.',','),'T',1,'R',1);
} else {
	$pdf->Cell(26,5,number_format($rlopoht->subtotal,2,'.',','),'T',1,'R',1);
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+131);
$pdf->Cell(32,5,'Diskon ('.$rlopoht->diskonpersen.'%)',0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,number_format($rlopoht->nilaidiskon,2,'.',','),0,1,'R',1);
} else{
    $pdf->Cell(26,5,number_format($rlopoht->nilaidiskon,2,'.',','),0,1,'R',1);
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+131);
$pdf->Cell(32,5,'PBBKB',0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,number_format($rlopoht->pbbkb,2,'.',','),0,1,'R',1);
} else {
	$pdf->Cell(26,5,number_format($rlopoht->pbbkb,2,'.',','),0,1,'R',1);
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+131);
$pdf->Cell(32,5,'PPn',0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,number_format($rlopoht->ppn,2,'.',','),0,1,'R',1);
} else {
	$pdf->Cell(26,5,number_format($rlopoht->ppn,2,'.',','),0,1,'R',1);
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+131);
$pdf->Cell(32,5,'PPh',0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,number_format($rlopoht->pph,2,'.',','),0,1,'R',1);
} else {
	$pdf->Cell(26,5,number_format($rlopoht->pph,2,'.',','),0,1,'R',1);
}
$pdf->SetFont('Arial','B',8);
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+131);

$pdf->Cell(32,5,$_SESSION['lang']['grnd_total'],0,0,'L',1);
if($rlopoht->matauang=='IDR'){
	$pdf->Cell(26,5,$rlopoht->matauang." ".number_format($gr_total,2,'.',','),0,1,'R',1);
} else {
	$pdf->Cell(26,5,$rlopoht->matauang." ".number_format($gr_total,2,'.',','),0,1,'R',1);
}
$yakhirgtot=$pdf->GetY();
/*if(strlen($rlopoht->uraian)>350)
{
	$tmbhBrs=65;
	$tmbhBrs2=115;
} else {
	$tmbhBrs=0;
	$tmbhBrs2=95;
}*/

if($yakhiruraian>$yakhirgtot)
{
	$yawalbawah=$yakhiruraian;
}
else
{
	$yawalbawah=$yakhirgtot;
}

//$pdf->SetY($akhirY+$tmbhBrs);
$pdf->SetY($yawalbawah);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->Cell(35,4,$_SESSION['lang']['syaratPem'],0,0,'L'); 
$pdf->Cell(2,4,":",0,0,'L'); 
if(isset($rSyp->keterangan)) $pdf->Cell(40,4,$rSyp->keterangan." (".$rSyp->jenis.")",0,0,'L'); 
$pdf->Ln();
// $pdf->Cell(35,4,$_SESSION['lang']['tgl_kirim'],0,0,'L'); 
// $pdf->Cell(40,4,": ".tanggalnormald($rlopoht->tanggalkirim),0,1,'L'); 		
if((is_null($rlopoht->idFranco))||($rlopoht->idFranco=='')||($rlopoht->idFranco==0)) {
	$pdf->Cell(35,4,$_SESSION['lang']['almt_kirim'],0,0,'L'); 
	$pdf->Cell(2,4,":",0,0,'L'); 
	$pdf->MultiCell(100,4,": ".$rlopoht->lokasipengiriman,0,'L'); 		
} else {
	$sFr="select * from ".$dbname.".setup_franco where id_franco='".$rlopoht->idFranco."'";
	$qFr=mysql_query($sFr) or die(mysql_error());
	$rFr=mysql_fetch_assoc($qFr);
	$pdf->Cell(35,4,$_SESSION['lang']['almt_kirim'],0,0,'L'); 
	$pdf->Cell(2,4,":",0,0,'L'); 
	$pdf->MultiCell(100,4,$rFr['alamat'],0,'L'); 		
		
}
$pdf->Ln();
$pdf->Ln();
// $pdf->SetY($akhirY+$tmbhBrs2);
// //$pdf->SetY($pdf->GetY()+900);
// $pdf->SetFont('Arial','B',12);
// $pdf->Ln();
// //$pdf->Cell(120,$height,$_SESSION['lang']['approval_status'].':','',0,'L');
// // $pdf->Cell(120,$height,$_SESSION['lang']['tandatangan'].':','',0,'L');
// $pdf->SetY($akhirY+53);
$pdf->SetFont('Arial','B',8);
$ko=0;
$qp="select * from ".$dbname.".`log_poht` where `nopo`='".$column."'"; //echo $qp;
$qyr=fetchData($qp);
$qPo=mysql_query($qp) or die(mysql_error($conn));
$rPo=mysql_fetch_assoc($qPo);
$pdf->Cell(65,4,'',0,0,'C');
$pdf->Cell(40,4,$_SESSION['lang']['dbuat_oleh'],'TBLR',0,'C');
if($rlopoht->matauang=='IDR' and $gr_total>=500000000){
	$pdf->Cell(40,4,$_SESSION['lang']['menyetujui'],'TBLR',0,'C');
	$pdf->Cell(40,4,$_SESSION['lang']['mengetahui'],'TBLR',0,'C');
} else {
	$pdf->Cell(40,4,$_SESSION['lang']['mengetahui'],'TBLR',0,'C');
	$pdf->Cell(40,4,$_SESSION['lang']['menyetujui'],'TBLR',0,'C');
}

$pdf->Ln();
$pdf->Cell(65,4,'','',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');

$pdf->Ln();
$pdf->Cell(65,4,'','',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');

$pdf->Ln();
$pdf->Cell(65,4,'','',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');

$pdf->Ln();
$pdf->Cell(65,4,'','',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');
$pdf->Cell(40,4,'','LR',0,'C');

$pdf->Ln();
$pdf->Cell(65,4,'',0,0,'C');
$pdf->Cell(40,4,strtoupper($nm_kary),'TBLR',0,'C');
$pdf->Cell(40,4,isset($optNmkry[$rPo['persetujuan1']])? strtoupper($optNmkry[$rPo['persetujuan1']]): '','TBLR',0,'C');
$pdf->Cell(40,4,isset($optNmkry[$rPo['persetujuan2']])? strtoupper($optNmkry[$rPo['persetujuan2']]): '','TBLR',0,'C');
//$pdf->Cell(20,4,$_SESSION['lang']['tanggal'],'TBLR',0,'C');
//$pdf->Cell(30,4,$_SESSION['lang']['status'],'TBLR',0,'C');
//$pdf->Cell(30,4,$_SESSION['lang']['keputusan'],'TBLR',0,'C');
//$pdf->Cell(50,4,$_SESSION['lang']['note'],'TBLR',0,'C');
$pdf->Ln(15);	

$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,4,strtoupper($_SESSION['lang']['fyiGudang2']),0,0,'C',0);

// foreach($qyr as $hsl)
// {
	// for($i=1;$i<4;$i++)
	// {
		// if($hsl['persetujuan'.$i]!='')
		// {
			// $sql="select * from ".$dbname.".`datakaryawan` where `karyawanid`='".$hsl['persetujuan'.$i]."'"; //echo $sql;//exit();
			// $keterangan = isset($hsl['komentar'.$i])? $hsl['komentar'.$i]: '';
		// } else {
			// break;
		// }
		// if($hsl['hasilpersetujuan'.$i]==1)
		// {
			// $b['status']=$_SESSION['lang']['disetujui'];
		// }
		// elseif($hsl['hasilpersetujuan'.$i]==3)
		// {
			// $b['status']=$_SESSION['lang']['ditolak'];
		// }
		// elseif($hsl['hasilpersetujuan'.$i]==''||$hsl['hasilpersetujuan'.$i]==0)
		// {
			// $b['status']=$_SESSION['lang']['wait_approve'];
		// }

		// $query=mysql_query($sql) or die(mysql_error());
		// $res3=mysql_fetch_object($query);

		// $sql2="select * from ".$dbname.".`sdm_5jabatan` where kodejabatan='".$res3->kodejabatan."'";
		// $query2=mysql_query($sql2) or die(mysql_error());
		// $res2=mysql_fetch_object($query2);
		// if($hsl['tglp'.$i]!='0000-00-00')
		// { $tgl=tanggalnormal($hsl['tglp'.$i]);}
		// else
		// {$tgl='';}
		// $pdf->SetFont('Arial','',7);
		// $pdf->Cell(8,4,$i,'TBLR',0,'C');
		// $pdf->Cell(40,4,$res3->namakaryawan,'TBLR',0,'L');
		// $pdf->Cell(35,4,$res2->namajabatan,'TBLR',0,'L');
		// $pdf->Cell(20,4,$res3->lokasitugas,'TBLR',0,'L');
		// //$pdf->Cell(20,4,$tgl,'TBLR',0,'L');
		// //$pdf->Cell(30,4,$b['status'],'TBLR',0,'L');
		// /*$pdf->Cell(50,4,$keterangan,'TBLR',0,'L');*/
		// $pdf->Ln();	
	// }
// }
$pdf->Output();