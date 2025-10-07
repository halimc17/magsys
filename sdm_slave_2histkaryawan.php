<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} else {
    $mode = 'preview';
}
if($mode=='pdf') {
    $param = $_GET;
    unset($param['mode']);
    unset($param['level']);
} else {
    $param = $_POST;
}

// Kode Organisasi
if(!isset($param['kodeorg'])) $param['kodeorg']=$_SESSION['empl']['lokasitugas'];

# Validasi Periode
$periode1 = $param['periode_from'];
$periode2 = $param['periode_until'];
#1. Empty
if($periode1=='' or $periode2=='') {
    echo 'Warning : Transaction period required';
    exit;
}
#2. Range Terbalik
if(tanggalsystem($periode1)>tanggalsystem($periode2)) {
    $tmp = $periode1;
    $periode1 = $periode2;
    $periode2 = $tmp;
}

// Get Data
$qData = "SELECT
	a.updatetime,c.namakaryawan as updatedby,b.nik,b.namakaryawan,a.data
	FROM ".$dbname.".hist_datakaryawan a LEFT JOIN
	".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid LEFT JOIN 
	".$dbname.".datakaryawan c on a.updateby=c.karyawanid
	where a.updatetime between '".tanggalsystem($periode1)."000000' and '".
	tanggalsystem($periode2)."235959' and b.namakaryawan like '%".$param['karyawan']."%'";
if(!empty($param['jabatan'])) $qData .= " and a.kodejabatan = '".$param['jabatan']."'";
$data = fetchData($qData);

$optJabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',null,'0',true);

// Rearrange Data
foreach($data as $key=>$row) {
	$data[$key]['data'] = json_decode($row['data'],1);
}

$dataShow = $data;
$dataExcel = $data;

# Report Gen
$theCols = array(
	$_SESSION['lang']['tanggalupdate'],
	$_SESSION['lang']['updateby'],
	'NIK',
	$_SESSION['lang']['namakaryawan'],
	$_SESSION['lang']['data'],
);
$align = explode(",","C,C,C,C,C");

switch($mode) {
    case 'pdf':
        /** Report Prep **/
		$title = $_SESSION['lang']['histkaryawan'];
        $length = explode(",","17,20,10,20,33");
        
        $pdf = new zPdfMaster('P','pt','A4');
        $pdf->setAttr1($title,$align,$length,$theCols);
		$pdf->_finReport = true;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
		
		# Content
		$pdf->SetFont('Arial','',9);
        foreach($dataShow as $key=>$row) {
			$i=0;
            foreach($row as $head=>$cont) {
				if($head!='data') {
					$pdf->Cell($length[$i]/100*$width,$height*count($row['data']),$cont,'LBR',0,$align[$i],1);
				} else {
					$tmpX = $pdf->GetX();
					foreach($row['data'] as $k=>$r) {
						$pdf->SetX($tmpX);
						$pdf->Cell(13/100*$width,$height,$k,'LBR',0,'L');
						$pdf->Cell(20/100*$width,$height,$r['old'].' => '.$r['new'],'LBR',0,'L');
						$pdf->Ln();
					}
				}
                $i++;
            }
        }
        $pdf->Output();
        break;
	
    default:
		# Redefine Align
		$alignPrev = array();
		foreach($align as $key=>$row) {
			switch($row) {
			case 'L':
				$alignPrev[$key] = 'left';
				break;
			case 'R':
				$alignPrev[$key] = 'right';
				break;
			case 'C':
				$alignPrev[$key] = 'center';
				break;
			default:
			}
		}
		
		/** Mode Header **/
        if($mode=='excel') {
            $tab = strtoupper($_SESSION['lang']['histkaryawan'])."<br>".
				strtoupper($_SESSION['lang']['tanggal'])." : ".$periode1." s/d ".$periode2.
			"<table border='1'>";
            $tab .= "<thead style=\"background-color:#222222\"><tr class='rowheader'>";
        } else {
            $tab = "<table id='periksabuah' class='sortable'>";
            $tab .= "<thead><tr class='rowheader'>";
        }
	
		/** Generate Table **/
        foreach($theCols as $key=>$head) {
			if($key==4) {
				$tab .= "<td style='text-align:center' colspan=2>".$head."</td>";
			} else {
				$tab .= "<td style='text-align:center'>".$head."</td>";
			}
        }
        $tab .= "</tr></thead>";
        $tab .= "<tbody>";
        
		# Content
		foreach($data as $key=>$row) {
            $tab .= "<tr class='rowcontent'>";
			$i=0;
			$tab .= "<td rowspan='".count($row['data'])."' align='".$alignPrev[0]."'>".$row['updatetime']."</td>";
			$tab .= "<td rowspan='".count($row['data'])."' align='".$alignPrev[1]."'>".$row['updatedby']."</td>";
			$tab .= "<td rowspan='".count($row['data'])."' align='".$alignPrev[1]."'>".$row['nik']."</td>";
			$tab .= "<td rowspan='".count($row['data'])."' align='".$alignPrev[2]."'>".$row['namakaryawan']."</td>";
			$i=0;
			foreach($row['data'] as $k=>$r) {
				if($i>0) $tab .= "<tr class=rowcontent>";
				$tab .= "<td align='left'>".$k."</td>";
				$tab .= "<td align='left'>".$r['old']." => ".$r['new']."</td></tr>";
				$i++;
			}
        }
        
	    /** Output Type **/
        if($mode=='excel') {
            $stream = $tab;
            $nop_="HistKaryawan_".tanggalsystem($periode1)."_".tanggalsystem($periode2);
            if(strlen($stream)>0) {
                # Delete if exist
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            @unlink('tempExcel/'.$file);
                        }
                    }	
                    closedir($handle);
                }
                
                # Write to File
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
                    echo "Error : Tidak bisa menulis ke format excel";
                    exit;
                } else {
                    echo $nop_;
                }
                fclose($handle);
            }
        } else {
            echo $tab;
        }
        break;
}