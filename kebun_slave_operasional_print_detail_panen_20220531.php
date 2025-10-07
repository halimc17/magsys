<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$tipe=$_GET['tipe'];
$param = $_GET;

$notran=$param['notransaksi'];

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select a.*,d.namaorganisasi from (select ".$col1." from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 		a.notransaksi='".$param['notransaksi']."') a
		left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");



//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg=fetchData($sOrg);
foreach($rDataOrg as $brOrg =>$rNamaOrg)
{
    $rNmOrg[$rNamaOrg['kodeorganisasi']]=$rNamaOrg['namaorganisasi'];
}
switch($tipe) {
    case "LC":
        $title = "Land Clearing";
        break;
    case "BBT":
	$title = $_SESSION['lang']['pembibitan'];
	break;
    case "TBM":
	$title = "UPKEEP-".$_SESSION['lang']['tbm'];
	break;
    case "TM":
	$title = "UPKEEP-".$_SESSION['lang']['tm'];
	break;
	case "PNN":
	$title = $_SESSION['lang']['panen'];
	break;
    default:
	echo "Error : Attribut not defined";
	exit;
	break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=0;
$totUpahPremibasis=$totUpahDenda=$totLuas=$totSisa=0;

/** Output Format **/
switch($proses) {
    case 'pdf':
        
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        $pdf->Ln();
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['nik'],1,0,'C',1);
        $pdf->Cell(13/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);
        $pdf->Cell(5/100*$width,$height,$_SESSION['lang']['jjg'],1,0,'C',1);
        $pdf->Cell(6/100*$width,$height,$_SESSION['lang']['luas'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['upahkerja'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['upahpenalty'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['premibasis'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['upahpremi'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['rupiahpenalty'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['total'],1,1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
        $qData=mysql_query($query) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData))
        {
            $pdf->Cell(10/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);
            $pdf->Cell(15/100*$width,$height,$RnamaKary[$rData['nik']],1,0,'L',1);
            $pdf->Cell(13/100*$width,$height,$rData['namaorganisasi'],1,0,'C',1);
            $pdf->Cell(5/100*$width,$height,$rData['hasilkerja'],1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['luaspanen'],2),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahkerja'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahpenalty'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['premibasis'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahpremi'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['rupiahpenalty'],0),1,0,'R',1);
            $sisa=$rData['upahkerja']-$rData['upahpenalty']+$rData['premibasis']+$rData['upahpremi']-$rData['rupiahpenalty'];
            $pdf->Cell(8/100*$width,$height,number_format($sisa,0),1,1,'R',1);
            $totJanjang+=$rData['hasilkerja'];
            $totUpahKerja+=$rData['upahkerja'];
            $totUpahKerjapenalty+=$rData['upahpenalty'];
            $totUpahPremi+=$rData['upahpremi'];
            $totUpahPremibasis+=$rData['premibasis'];
            $totUpahDenda+=$rData['rupiahpenalty'];
            $totLuas+=$rData['luaspanen'];
            $totSisa+=$sisa;
        }
        $pdf->Cell(38/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
        $pdf->Cell(5/100*$width,$height,number_format($totJanjang,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totLuas,2),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahKerja,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahKerjapenalty,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahPremibasis,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahPremi,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahDenda,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totSisa,0),1,1,'R',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',8);
        $sAsis="select distinct nikmandor,nikmandor1,nikasisten,keranimuat,tanggal,kodeorg from ".$dbname.".kebun_aktifitas where notransaksi='".$param['notransaksi']."'";
        $qAsis=mysql_query($sAsis) or die(mysql_error($conn));
        $rAsis=mysql_fetch_assoc($qAsis);
		setIt($RnamaKary[$rAsis['nikasisten']],'');
		setIt($RnamaKary[$rAsis['nikmandor1']],'');
		setIt($RnamaKary[$rAsis['nikmandor']],'');
        $pdf->ln(10);
        $pdf->Cell(85/100*$width,$height,$rAsis['kodeorg'].",".tanggalnormal($rAsis['tanggal']),0,1,'R',0);
        $pdf->ln(35);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['dbuat_oleh'],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['diperiksa'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['disetujui'],0,1,'C',0);
        $pdf->ln(65);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikasisten']],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$RnamaKary[$rAsis['nikmandor']],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikmandor1']],0,1,'C',0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['kerani'],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['mandor'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['nikmandor1'],0,1,'C',0);
        $pdf->Output();
        break;
        
        
        
        
        
        
        
        
        case'html':
            
           
        $tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=65% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
        $tab.="</tbody></table>";
        $tab.="<br />".$titleDetail[0]."<br />";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['jjg']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahpenalty']."</td>";
        
        $tab.="<td  align=center>".$_SESSION['lang']['brondolan']."</td>";
        $tab.="<td  align=center>Premi Brondolan</td>";
        
        $tab.="<td  align=center>Premi Kehadiran</td>";
        
        $tab.="<td align=center>".$_SESSION['lang']['premibasis']."</td>";
        $tab.="<td align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
        
        
        $isiQuery="a.notransaksi as anotransaksi,a.nik,a.kodekegiatan,a.kodeorg as kodeblok,a.tahuntanam,a.hasilkerja,a.hasilkerjakg,
            a.jumlahhk,a.norma,a.outputminimal,a.upahkerja,a.upahpenalty,a.upahpremi,a.premibasis,a.umr,a.statusblok,
            a.pekerjaanpremi,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,
            a.penalti9,a.penalti10,a.rupiahpenalty,a.luaspanen,a.kodesegment,a.brondolan,a.jjgpenalty,b.*";
        $queryhtml="select a.*,d.namaorganisasi from (select ".$isiQuery." from ".$dbname.".kebun_prestasi a 
					left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."') a
					left join ".$dbname.".organisasi d on a.kodeblok=d.kodeorganisasi";
        $qData=mysql_query($queryhtml) or die(mysql_error($conn));
        while($rData=mysql_fetch_assoc($qData)){
            
              
                $iLibur="select count(*) as libur from ".$dbname.".sdm_5harilibur where tanggal='".$rData['tanggal']."' and "
                        . " keterangan='libur' and kebun in ('GLOBAL','".$rData['kodeorg']."') ";
              
                $nLibur=  mysql_query($iLibur) or die (mysql_error($conn));
                $dLibur=  mysql_fetch_assoc($nLibur);
                    $libur=$dLibur['libur'];
                    
                    $day = date('D', strtotime($rData['tanggal']));
                    //if($day=='Sun')$cekminggu=1; 
                    if($day=='Sun')$cekminggu=0;
                    else $cekminggu=0;
                    $ceklibur=$libur+$cekminggu;
                    
                  
                    
                    if($ceklibur>0)
                    {
                        $jenisPremi='LIBUR';
                    }
                    else
                    {
                        $jenisPremi='KERJA';
                    }
                 
                    
                $iTopo=" select * from ".$dbname.".setup_blok where kodeorg='".$rData['kodeblok']."' ";
                $nTopo=  mysql_query($iTopo) or die (mysql_error($conn));
                $dTopo=  mysql_fetch_assoc($nTopo);
                  
                    
                
                $iBasis="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$_SESSION['org']['kodeorganisasi']."'"
                        . " and jenispremi='".$jenisPremi."' and topografi='".$dTopo['topografi']."'"
                        . " and kelaspohon='".$dTopo['kelaspohon']."' ";
                
             
                
                $nBasis=  mysql_query($iBasis) or die (mysql_error($conn));
                $dBasis=  mysql_fetch_assoc($nBasis);
                
                //echo $iBasis;
                
                
            
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td>".$RnamaKary[$rData['nik']]."</td>";
                $tab.="<td>".$rData['namaorganisasi']."</td>";
                $tab.="<td align=right>".$rData['hasilkerja']."</td>";
                $tab.="<td align=right>".number_format($rData['luaspanen'],2)."</td>";
                $tab.="<td align=right>".number_format($rData['upahkerja'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['upahpenalty'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['brondolan'],0)."</td>";
                
                $premibrondolan=$dBasis['premibrondolan']*$rData['brondolan'];
                
                $tab.="<td align=right>".number_format($premibrondolan,0)."</td>";
                
                if($ceklibur>0)
                {
                    
                    if($rData['hasilkerja']>=$dBasis['basis'])//cek apakah capai basis / tidak
                    {
                        $premiHadir=$dBasis['premiliburcapaibasis'];
                    }
                    else
                    {
                        $premiHadir=$dBasis['premilibur'];
                    }
                    
                    
                }
                else
                {
                    $premiHadir=$dBasis['premitopografi'];
                }
                
                
                $tab.="<td align=right>".number_format($premiHadir,0)."</td>";
                
                
                $premiBasis=$rData['upahpremi']-$premiHadir-$premibrondolan;
                
                $tab.="<td align=right>".number_format($premiBasis)."</td>";
                
                $tab.="<td align=right>".number_format($rData['upahpremi'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['rupiahpenalty'],0)."</td>";
                $sisa=$rData['upahkerja']-$rData['upahpenalty']+$rData['premibasis']+$rData['upahpremi']-$rData['rupiahpenalty'];
                $tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                $totJanjang+=$rData['hasilkerja'];
                $totUpahKerja+=$rData['upahkerja'];
                $totUpahKerjapenalty+=$rData['upahpenalty'];
                $totUpahPremi+=$rData['upahpremi'];
                //$totUpahPremibasis+=$rData['premibasis'];
                $totUpahDenda+=$rData['rupiahpenalty'];
                $totLuas+=$rData['luaspanen'];
                $totSisa+=$sisa;
                $totBrondolan+=$rData['brondolan'];
                $totPremiBrondolan+=$premibrondolan;
                
                $totPremiKehadiran+=$premiHadir;
                
                $totUpahPremibasis+=$premiBasis;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerjapenalty,0)."</td>";
        
        $tab.="<td align=right>".number_format($totBrondolan)."</td>";
        $tab.="<td align=right>".number_format($totPremiBrondolan)."</td>";
        $tab.="<td align=right>".number_format($totPremiKehadiran)."</td>";
        
        
        $tab.="<td align=right>".number_format($totUpahPremibasis,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right>".number_format($totSisa,0)."</td>";
        $tab.="</tr></tbody></table>";
        
//        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
//        $tab.="<tr class=rowheader>";
//        $tab.="<td>".$_SESSION['lang']['disetujui']."</td>";
//        $tab.="<td>".$_SESSION['lang']['diperiksa']."</td>";
//        $tab.="<td>".$_SESSION['lang']['dbuat_oleh']."</td></tr></thead><tbody>";
//        $tab.="<tr class=rowcontent>";
//        $tab.="<td>".$RnamaKary[$rAsis['nikasisten']]."</td>";
//        $tab.="<td>".$RnamaKary[$rAsis['nikmandor1']]."</td>";
//        $tab.="<td>".$RnamaKary[$rAsis['nikmandor']]."</td></tr></tbody></table>";
 
        echo $tab;
        break;
    case 'excel':
        
        //$tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table border=1 cellpadding=1 cellspacing=1 class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td bgcolor=#CCCCCC>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td bgcolor=#CCCCCC>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
        $tab.="</tbody></table>";
        $tab.="<br />".$titleDetail[0]."<br />";
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jjg']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['upahpenalty']."</td>";
        
        $tab.="<td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['brondolan']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>Premi Brondolan</td>";
        
        $tab.="<td bgcolor=#CCCCCC align=center>Premi Kehadiran</td>";
        
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['premibasis']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
        
        
        $isiQuery="a.notransaksi as anotransaksi,a.nik,a.kodekegiatan,a.kodeorg as kodeblok,a.tahuntanam,a.hasilkerja,a.hasilkerjakg,
            a.jumlahhk,a.norma,a.outputminimal,a.upahkerja,a.upahpenalty,a.upahpremi,a.premibasis,a.umr,a.statusblok,
            a.pekerjaanpremi,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,
            a.penalti9,a.penalti10,a.rupiahpenalty,a.luaspanen,a.kodesegment,a.brondolan,a.jjgpenalty,b.*";
        $queryhtml="select a.*,d.namaorganisasi from (select ".$isiQuery." from ".$dbname.".kebun_prestasi a 
					left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."') a
					left join ".$dbname.".organisasi d on a.kodeblok=d.kodeorganisasi";
        
        $qData=mysql_query($queryhtml) or die(mysql_error($conn));
        while($rData=mysql_fetch_assoc($qData)){
            
                $iLibur="select count(*) as libur from ".$dbname.".sdm_5harilibur where tanggal='".$rData['tanggal']."' and "
                        . " keterangan='libur' and kebun in ('GLOBAL','".$rData['kodeorg']."') ";
                              
                $nLibur=  mysql_query($iLibur) or die (mysql_error($conn));
                $dLibur=  mysql_fetch_assoc($nLibur);
                    $libur=$dLibur['libur'];
                    
                    $day = date('D', strtotime($rData['tanggal']));
                    if($day=='Sun')$cekminggu=1; else $cekminggu=0;
                    $ceklibur=$libur+$cekminggu;
                    
                    if($ceklibur>0)
                    {
                        $jenisPremi='LIBUR';
                    }
                    else
                    {
                        $jenisPremi='KERJA';
                    }
                    
                $iTopo=" select * from ".$dbname.".setup_blok where kodeorg='".$rData['kodeblok']."' ";
               
                $nTopo=  mysql_query($iTopo) or die (mysql_error($conn));
                $dTopo=  mysql_fetch_assoc($nTopo);
                  
                $iBasis="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$_SESSION['org']['kodeorganisasi']."'"
                        . " and jenispremi='".$jenisPremi."' and topografi='".$dTopo['topografi']."'"
                        . " and kelaspohon='".$dTopo['kelaspohon']."' ";
                $nBasis=  mysql_query($iBasis) or die (mysql_error($conn));
                $dBasis=  mysql_fetch_assoc($nBasis);
                
                //echo $iBasis;
                
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td>".$RnamaKary[$rData['nik']]."</td>";
                $tab.="<td>".$rData['namaorganisasi']."</td>";
                $tab.="<td align=right>".$rData['hasilkerja']."</td>";
                $tab.="<td align=right>".number_format($rData['luaspanen'],2)."</td>";
                $tab.="<td align=right>".number_format($rData['upahkerja'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['upahpenalty'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['brondolan'],0)."</td>";
                
                $premibrondolan=$dBasis['premibrondolan']*$rData['brondolan'];
                
                $tab.="<td align=right>".number_format($premibrondolan,0)."</td>";
                
                if($ceklibur>0)
                {
                    
                    if($rData['hasilkerja']>=$dBasis['basis'])//cek apakah capai basis / tidak
                    {
                        $premiHadir=$dBasis['premiliburcapaibasis'];
                    }
                    else
                    {
                        $premiHadir=$dBasis['premilibur'];
                    }
                    
                    
                }
                else
                {
                    $premiHadir=$dBasis['premitopografi'];
                }
                
                
                $tab.="<td align=right>".number_format($premiHadir,0)."</td>";
                
                
                $premiBasis=$rData['upahpremi']-$premiHadir-$premibrondolan;
                
                $tab.="<td align=right>".number_format($premiBasis)."</td>";
                
                $tab.="<td align=right>".number_format($rData['upahpremi'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['rupiahpenalty'],0)."</td>";
                $sisa=$rData['upahkerja']-$rData['upahpenalty']+$rData['premibasis']+$rData['upahpremi']-$rData['rupiahpenalty'];
                $tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                $totJanjang+=$rData['hasilkerja'];
                $totUpahKerja+=$rData['upahkerja'];
                $totUpahKerjapenalty+=$rData['upahpenalty'];
                $totUpahPremi+=$rData['upahpremi'];
                //$totUpahPremibasis+=$rData['premibasis'];
                $totUpahDenda+=$rData['rupiahpenalty'];
                $totLuas+=$rData['luaspanen'];
                $totSisa+=$sisa;
                $totBrondolan+=$rData['brondolan'];
                $totPremiBrondolan+=$premibrondolan;
                
                $totPremiKehadiran+=$premiHadir;
                
                $totUpahPremibasis+=$premiBasis;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3><b>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right><b>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right><b>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right><b>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right><b>".number_format($totUpahKerjapenalty,0)."</td>";
        
        $tab.="<td align=right><b>".number_format($totBrondolan)."</td>";
        $tab.="<td align=right><b>".number_format($totPremiBrondolan)."</td>";
        $tab.="<td align=right><b>".number_format($totPremiKehadiran)."</td>";
        
        
        $tab.="<td align=right><b>".number_format($totUpahPremibasis,0)."</td>";
        $tab.="<td align=right><b>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right><b>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right><b>".number_format($totSisa,0)."</td>";
        $tab.="</tr></tbody></table>";
        
        
        
     
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			//$nop_="PNN:".$param['notransaksi'];
			$nop_="Laporan_PNN";
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