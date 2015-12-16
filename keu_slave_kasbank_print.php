<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_POST;


/** Report Prep **/
$str="select periode, tanggalmulai, tanggalsampai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku = '0'";
$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $periodeaktif=$res['periode'];
    $periodemulai=$res['tanggalmulai'];
    $periodesampai=$res['tanggalsampai'];
}

$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal >= '".$periodemulai."' and tanggal <= '".$periodesampai."'";
$cols = 'notransaksi,tanggal,noakun,tipetransaksi,jumlah,posting,keterangan';

$colArr = explode(',',$cols);
$query = selectQuery($dbname,'keu_kasbankht',$cols,$where,'tanggal desc, notransaksi desc');
$data = fetchData($query);

//$title = "Kas Bank";
$title = "Cash/Bank Tansaction";
$align = explode(",","L,L,L,L,R,L,L");
$length = explode(",","25,10,15,20,10,10,10");

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->setAttr1($title,$align,$length,$colArr);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',9);
        foreach($data as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
	
        $pdf->Output();
        break;
    case 'excel':
		$tab = strtoupper($_SESSION['lang']['kasbank'])."<br>".
			strtoupper($_SESSION['lang']['tanggal'])." : ".$periodemulai." s/d ".$periodesampai.
			"<table border='1'>";
		$tab .= "<thead style=\"background-color:#EEE\">";
		$tab .= "<tr class=rowheader>";
		$tab .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
		$tab .= "<td>".$_SESSION['lang']['tanggal']."</td>";
		$tab .= "<td>".$_SESSION['lang']['noakun']."</td>";
		$tab .= "<td>".$_SESSION['lang']['tipetransaksi']."</td>";
		$tab .= "<td>".$_SESSION['lang']['jumlah']."</td>";
		$tab .= "<td>".$_SESSION['lang']['posting']."</td>";
		$tab .= "<td>".$_SESSION['lang']['keterangan']."</td>";
		$tab .= "</tr></thead><tbody>";
		foreach($data as $key=>$row) {    
            $tab .= "<tr>";
            foreach($row as $cont) {
                $tab .= "<td>".$cont."</td>";
            }
            $tab .= "</tr>";
        }
		$tab .= "</tbody></table>";
		
		header("Cache-control: must-revalidate");
		header("Pragma: must-revalidate");
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=KasBank_".$_SESSION['empl']['lokasitugas']."_".$periodeaktif.".xls");
		echo $tab;
		break;
    default:
    break;
}
?>