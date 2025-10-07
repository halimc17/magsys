<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
include "phpqrcode/qrlib.php";
//=============
$kodeorg=$_GET['kodeorg'];
$kodebrg=$_GET['kodebarang'];
$kodeinv=$_GET['kodeinv'];
$namainv=$_GET['namainv'];
$nik=$_GET['nik'];
$ruangan=$_GET['ruangan'];
$type=$_GET['type'];
//create Header
class PDF extends FPDF
{
}
if($type=='1'){
	//$str="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
	//	left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
	//	left join ".$dbname.".datakaryawan d on d.nik=a.nik
	//	where a.kodeorg='".$kodeorg."' and a.kodebarang='".$kodebrg."' and a.kodeinventaris='".$kodeinv."'";
	$str="select a.*,c.namasupplier as supplier from ".$dbname.".log_invbarang a 
		left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
		where a.kodeorg='".$kodeorg."' and a.kodebarang='".$kodebrg."' and a.kodeinventaris='".$kodeinv."'";
}else{
	$where="True";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where="a.kodeorg not like '%HO' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
	}else{
		$where="a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	if($kodeorg!=''){
		$where.=" and a.kodeorg='".$kodeorg."'";
	}
	if($kodebrg!=''){
		$where.=" and a.kodebarang='".$kodebrg."'";
	}
	if($kodeinv!=''){
		$where.=" and a.kodeinventaris='".$kodeinv."'";
	}
	if($namainv!=''){
		$where.=" and a.namainventaris like '%".$namainv."%'";
	}
	if($nik!=''){
		$where.=" and a.nik='".$nik."'";
	}
	if($ruangan!=''){
		$where.=" and a.ruangan like '%".$ruangan."%'";
	}
	//$str="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
	//	left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
	//	left join ".$dbname.".datakaryawan d on d.nik=a.nik
	//	where ".$where." 
	//	order by a.kodeorg,a.kodebarang,a.kodeinventaris";
	$str="select a.*,c.namasupplier as supplier from ".$dbname.".log_invbarang a 
		left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
		where ".$where." 
		order by a.kodeorg,a.kodebarang,a.kodeinventaris";
}
//exit('Warning:'.$str);
$res=mysql_query($str);
$nrows=mysql_num_rows($res);
if($nrows==0){
	exit('Warning: No Data Found...!');
}else if($nrows>25){
	exit('Warning: Data hanya dibatasi 25 QR Code, Mohon diisikan filter pencarian...! ');
}
		$pdf=new PDF('P','mm',array(42,68));
$no=0;
while($bar=mysql_fetch_object($res)){
	$no+=1;
	$kodeorg=$bar->kodeorg;
	$kodebarang=$bar->kodebarang;
	$kodeinventaris=$bar->kodeinventaris;
	$namainventaris=$bar->namainventaris;
	$merkinventaris=$bar->merkinventaris;
	$tipeinventaris=$bar->tipeinventaris;
	$ketinventaris=$bar->ketinventaris;
	$nopo=$bar->nopo;
	$tahun=(substr($bar->tglperolehan,0,4)=='0000' ? '****' : substr($bar->tglperolehan,0,4));
	$nik=$bar->nik;
	$karyawan=$bar->namakaryawan;
	$tgluserterima=$bar->tgluserterima;
	$lokasi=$bar->lokasi;
	$qrbarang=$kodebarang."
".$namainventaris."
".$karyawan."
".$tgluserterima."
".$nopo;
	//$path="https://chart.googleapis.com/chart?chs=125x125&cht=qr&chl=$qrbarang";
$tempdir = "qrcode-img/";
if (!file_exists($tempdir))        //jika folder belum ada, maka buat
	mkdir($tempdir);
$namafile="qrcode-".$kodeorg.$kodebarang.$kodeinventaris.".png";
$quality="H"; // ini ada 4 pilihan yaitu L (Low), M(Medium), Q(Good), H(High)
$ukuran=5; // 1 adalah yang terkecil, 10 paling besar
$padding=1;
QRCode::png($qrbarang, $tempdir.$namafile, $quality, $ukuran, $padding);
$path=$tempdir.$namafile;
        $pdf->SetFont('Arial','B',18);
        $pdf->AddPage();
        $pdf->SetY(5);
        $pdf->SetX(5);
        $pdf->SetFillColor(255,255,255); 
		$pdf->Image($path,10,5,25);
        $pdf->SetY(33);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(24,2,$kodeinventaris,0,1,'C');
        $pdf->SetY(39);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(24,2,$tahun,0,1,'C');	
        $pdf->SetY(45);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(24,2,$lokasi,0,0,'C');	
}
        $pdf->Output();
?>
