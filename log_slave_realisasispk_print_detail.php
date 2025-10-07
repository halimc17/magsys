<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_GET;


/** Report Prep **/
$cols = array();

#====================== Rencana =========================
$col1 = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
$cols = explode(',',$col1);
$cols[0] = 'subunit';
$where = "notransaksi='".$param['notransaksi']."'";
$query = selectQuery($dbname,'log_spkdt',$col1,$where);
$data = fetchData($query);
$align = explode(",","L,L,L,R,L,R");
$length = explode(",","20,20,10,20,10,20");
if(empty($data)) {
    echo "Data Kosong";
    exit;
}

# Options
$whereOrg = "kodeorganisasi in (";
$whereKeg = "kodekegiatan in (";
$whereKeg2 = "kegiatan in (";
foreach($data as $key=>$row) {
    if($key==0) {
        $whereOrg .= "'".$row['kodeblok']."'";
        $whereKeg .= "'".$row['kodekegiatan']."'";
		$whereKeg2 .= "'".$row['kodekegiatan']."'";
    } else {
        $whereOrg .= ",'".$row['kodeblok']."'";
        $whereKeg .= ",'".$row['kodekegiatan']."'";
		$whereKeg2 .= ",'".$row['kodekegiatan']."'";
    }
}
$whereOrg .= ",'".$param['kodeorg']."')";
$whereKeg .= ")";
$whereKeg2 .= ")";

$optKebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					   "kodeorganisasi='".$param['kodeorg']."'",'0',true);
if(empty($param['divisi'])) {
	$optOrg = makeOption($dbname,'project','kode,nama',"kodeorg='".
						 $param['kodeorg']."' and posting=0");
	$optKeg = makeOption($dbname,'project_dt','kegiatan,namakegiatan',
		$whereKeg2,'0',true);
} else {
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
		$whereOrg,'0',true);
	$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',
		$whereKeg,'0',true);
}
$optSupp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
    "supplierid='".$param['koderekanan']."'");

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
    $dataShow[$key]['kodeblok'] = $optOrg[$row['kodeblok']];
    $dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
}

#====================== Realisasi =========================
$col2 = "tanggal,kodeblok,kodekegiatan,hkrealisasi,hasilkerjarealisasi,jumlahrealisasi,ppn,pph,totalrealisasi";
$kol2 = "tanggal,kodeblok,kodekegiatan,hk,hasilkerjajumlah,jumlahrp,ppn,pph,total";
$cols2 = explode(',',$kol2);
$cols[1] = 'kegiatan';
$where2 = "a.notransaksi='".$param['notransaksi']."' and a.kodekegiatan in (";
$where3 = "noreferensi='".$param['notransaksi']."' and kodekegiatan in (";
foreach($data as $key=>$row) {
    if($key==0) {
        $where2 .= "'".$row['kodekegiatan']."'";
        $where3 .= "'".$row['kodekegiatan']."'";
    } else {
        $where2 .= ",'".$row['kodekegiatan']."'";
        $where3 .= ",'".$row['kodekegiatan']."'";
    }
}
$where2 .= ")";
$where3 .= ")";
//$query2 = selectQuery($dbname,'log_baspk',$col2,$where2);
//$query2 = "SELECT a.tanggal,a.kodeblok,a.kodekegiatan,a.hkrealisasi,a.hasilkerjarealisasi,a.jumlahrealisasi,if(isnull(b.jumlah),0,b.jumlah) as ppn
//		,if(isnull(c.jumlah),0,c.jumlah) as pph,a.jumlahrealisasi+if(isnull(b.jumlah),0,b.jumlah)+if(isnull(c.jumlah),0,c.jumlah) as totalrealisasi
//		FROM ".$dbname.".log_baspk a
//		left JOIN ".$dbname.".keu_jurnaldt b on b.noreferensi=a.notransaksi and b.kodekegiatan=a.kodekegiatan and b.tanggal=a.tanggal and left(b.noakun,3) ='116'
//		left JOIN ".$dbname.".keu_jurnaldt c on c.noreferensi=a.notransaksi and c.kodekegiatan=a.kodekegiatan and b.tanggal=a.tanggal and left(c.noakun,3) ='212'
//		where ".$where2."";
$query2 = "SELECT a.tanggal,a.kodeblok,a.kodekegiatan,a.hkrealisasi,a.hasilkerjarealisasi,a.jumlahrealisasi
		,if(isnull(b.ppn),0,b.ppn) as ppn
		,if(isnull(b.pph),0,b.pph) as pph
		,a.jumlahrealisasi+if(isnull(b.ppn),0,b.ppn)+if(isnull(b.pph),0,b.pph) as totalrealisasi
		FROM ".$dbname.".log_baspk a
		left JOIN (SELECT noreferensi,kodekegiatan,tanggal
						,sum(if(left(noakun,3)='116',jumlah,0)) as ppn 
						,sum(if(left(noakun,3)='212',jumlah,0)) as pph
					from ".$dbname.".keu_jurnaldt 
					where ".$where3." and left(noakun,3) in ('116','212')
					GROUP BY noreferensi,kodekegiatan,tanggal) b on b.noreferensi=a.notransaksi and b.kodekegiatan=a.kodekegiatan and b.tanggal=a.tanggal
		where ".$where2."";
//exit('Warning: '.$query2);
$data2 = fetchData($query2);
$align2 = explode(",","L,L,L,R,R,R,R,R,R");
//$length2 = explode(",","10,20,25,10,15,20");
$length2 = explode(",","7,18,25,6,7,10,9,8,10");

if(empty($data2)) {
    echo "Data Realisasi belum ada";
    exit;
}

# Options
$whereOrg2 = "kodeorganisasi in (";
foreach($data2 as $key=>$row) {
    if($key==0) {
        $whereOrg2 .= "'".$row['kodeblok']."'";
    } else {
        $whereOrg2 .= ",'".$row['kodeblok']."'";
    }
}
$whereOrg2 .= ",'".$param['kodeorg']."')";
$optOrg2 = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    $whereOrg2,'0',true);

# Data Show
$dataShow2 = $data2;
foreach($dataShow2 as $key=>$row) {
	if(isset($optOrg2[$row['kodeblok']])) {
		$dataShow2[$key]['kodeblok'] = $optOrg2[$row['kodeblok']];
	} else {
		$dataShow2[$key]['kodeblok'] = $optOrg[$row['kodeblok']];
	}
	$dataShow2[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
}

$title = $_SESSION['lang']['spk'];
$titleDetail = array('');

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('L','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".
            $param['notransaksi'],0,1,'L',1);
        $pdf->Cell($width,$height,$_SESSION['lang']['kodeorg']." : ".
            $optKebun[$param['kodeorg']],0,1,'L',1);
        $pdf->Cell($width,$height,$_SESSION['lang']['koderekanan']." : ".
            $optSupp[$param['koderekanan']],0,1,'L',1);
        $pdf->Ln();
        
        #====================== Rencana =========================
        # Header
        $pdf->Cell($width,$height,'Rencana',0,0,'L',1);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $i=0;
        foreach($cols as $column) 
		{
			if($column=='jumlahrp')
			{
				$optKurs = makeOption($dbname,"log_spkht","notransaksi,matauang","notransaksi='".$param['notransaksi']."'");
				$pdf->Cell($length[$i]/100*$width,$height,str_replace("(Rp)","",$_SESSION['lang'][$column])."(".$optKurs[$param['notransaksi']].")",1,0,'C',1);
			}
			else
			{
				$pdf->Cell($length[$i]/100*$width,$height,$_SESSION['lang'][$column],1,0,'C',1);
			}
            $i++;
        }
        $pdf->Ln();
        
        # Content
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        foreach($dataShow as $key=>$row) {    
            $i=0;
            foreach($row as $attr=>$cont) {
				if($attr=='jumlahrp') $cont = number_format($cont,2);
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
        $pdf->Ln();
        
        #====================== Realisasi =========================
        # Header
        $pdf->Cell($width,$height,'Realisasi',0,0,'L');
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $i=0;
        foreach($cols2 as $column) {
			//echo $column."<br>";
			if($column == 'jumlahrealisasi')
			{
				$optKurs = makeOption($dbname,"log_spkht","notransaksi,matauang","notransaksi='".$param['notransaksi']."'");
				$pdf->Cell($length2[$i]/100*$width,$height,str_replace("(Rp.)","",$_SESSION['lang'][$column])."(".$optKurs[$param['notransaksi']].")",1,0,'C',1);
			}
			else if($column == 'total')
			{
				$pdf->Cell($length2[$i]/100*$width,$height,$_SESSION['lang'][$column].' (Rp.)',1,0,'C',1);
			}
			else
			{
				$pdf->Cell($length2[$i]/100*$width,$height,$_SESSION['lang'][$column],1,0,'C',1);
			}
            $i++;
        }
        $pdf->Ln();
        
        # Content
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        foreach($dataShow2 as $key=>$row) {    
            $i=0;
            foreach($row as $attr=>$cont) {
				if($attr=='jumlahrealisasi' or $attr=='ppn' or $attr=='pph' or $attr=='totalrealisasi') $cont = number_format($cont,2);
                $pdf->Cell($length2[$i]/100*$width,$height,$cont,1,0,$align2[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
        $pdf->Ln();
        
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>