<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdeOrg = checkPostGet('kdeOrg','');
$kdOrg = checkPostGet('kdOrg','');
$tgl1 = tanggalsystem(checkPostGet('tgl1',''));
$tgl2 = tanggalsystem(checkPostGet('tgl2',''));
$tgl_1 = tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2 = tanggalsystem(checkPostGet('tgl_2',''));
$periode = checkPostGet('period','');
$kdUnit = checkPostGet('kdUnit','');
$pilihan = checkPostGet('pilihan','');
$pilihan2 = checkPostGet('pilihan_2','');
$pilihan3 = checkPostGet('pilihan_3','');

$periodeGaji=$periode;
$periode=explode('-',$periode);
if(!$kdOrg)$kdOrg=$_SESSION['empl']['lokasitugas'];

$optBagian=makeOption($dbname,'sdm_5departemen','kode,nama');
$optTipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$optJabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

//get data karyawan nama,jabatan sm tipe
$optNmKar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optDtJbtnr=makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$optDtBag=makeOption($dbname, 'datakaryawan', 'karyawanid,bagian');
$optDtTipe=makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan');
$optDtSub=makeOption($dbname, 'datakaryawan', 'karyawanid,subbagian');
function dates_inbetween($date1, $date2)
{
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++)
        {
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}

        if(($tgl_1!='')&&($tgl_2!=''))
        {	
                $tgl1=$tgl_1;
                $tgl2=$tgl_2;
        }
        $test = dates_inbetween($tgl1, $tgl2);




// get namaorganisasi =========================================================================
        $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kdeOrg."' ";	
        $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
        while($rOrg=mysql_fetch_assoc($qOrg))
        {
                $nmOrg=$rOrg['namaorganisasi'];
        }
        if(!isset($nmOrg))$nmOrg=$kdOrg;
        //ambil where untuk data karyawan
        if($kdeOrg!='')
        {

                if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
                {
                        $where=" and lokasitugas = '".$kdeOrg."'";
                        $where2=" and substr(kodeorg,1,4)='".$kdeOrg."'";
                }
                else
                {
                        if(strlen($kdeOrg)>4)
                        {
                                $where=" and subbagian='".$kdeOrg."'";
                                $where2=" and kodeorg='".$kdeOrg."'";
                        }
                        else
                        {
                                $where=" and lokasitugas='".$kdeOrg."'";
                                $where2=" and substr(kodeorg,1,4)='".$kdeOrg."'";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where=" and lokasitugas='".$kodeOrg."'";
        }
// pilihan 2
$where3="";
if($pilihan2=='bulanan'){
        $where3 = ' and a.sistemgaji = \'Bulanan\' ';
} elseif($pilihan2=='harian'){
        $where3 = ' and a.sistemgaji = \'Harian\' ';
}

// pilihan 3
if($pilihan3=='semua')
    $where4 = '';
else
    $where4 = " and a.bagian = '".$pilihan3."' ";
$bgclr=" ";
$brdr=0;
if($proses=='excel')
{
    $bgclr=" bgcolor=#DEDEDE align=center";
    $brdr=1;
}
if(($proses=='excel')||($proses=='preview')||($proses=='pdf'))
{

        $sAbsensi="select distinct count(absensi) as jmlhhadir,absensi,karyawanid from ".$dbname.".sdm_absensidt_vw where absensi='H' and
                   tanggal between '".$tgl_1."' and '".$tgl_2."' and substring(kodeorg,1,4)='".substr($kdeOrg,0,4)."' group by karyawanid,absensi";
        //exit($sAbsensi);
        $qAbsensi=mysql_query($sAbsensi) or die(mysql_error());
        while($rAbsensi=mysql_fetch_assoc($qAbsensi))
        {
            $jmlhHadir[$rAbsensi['karyawanid']]=$rAbsensi['jmlhhadir'];
        }
        //kehadiran di perawatan
        $sKehadiran="select count(absensi) as jmlhhadir,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                     where tanggal between  '".$tgl_1."' and '".$tgl_2."' and substring(unit,1,4)='".substr($kdeOrg,0,4)."'";
        $qKehadiran=mysql_query($sKehadiran) or die(mysql_error($conn));
        while($rKehadiran=mysql_fetch_assoc($qKehadiran))
        {
			setIt($jmlhHadir[$rKehadiran['karyawanid']],0);
            $jmlhHadir[$rKehadiran['karyawanid']]+=$rKehadiran['jmlhhadir'];
        }
        //kehadiran di panen
        $sPrestasi="select count(a.nik) as jmlhhadir,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                    where b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='".substr($kdeOrg,0,4)."' and b.tanggal between '".$tgl_1."' and '".$tgl_2."'
                    group by a.nik";
        //exit("Error:".$sPrestasi);
        $qPrestasi=mysql_query($sPrestasi) or die(mysql_error($conn));
        while($rPrestasi=mysql_fetch_assoc($qPrestasi))
        {
            $jmlhHadir[$rPrestasi['nik']]+=$rPrestasi['jmlhhadir'];
        }
        //get jam lembur
        $sGetLembur="select jamaktual, jamlembur,tipelembur from ".$dbname.".sdm_5lembur where kodeorg = '".substr($kdeOrg,0,4)."'";
        //exit("Error".$sGetLembur);
        $rGetLembur=fetchData($sGetLembur);
        foreach($rGetLembur as $row => $kar)
        {
            $GetLembur[$kar['tipelembur']][$kar['jamaktual']]=$kar['jamlembur'];
        }  
        //semua data lembur
        $sLembur="select  uangkelebihanjam,a.karyawanid,jamaktual,tipelembur from ".$dbname.".sdm_lemburdt b
                  LEFT JOIN ".$dbname.".datakaryawan a on a.karyawanid = b.karyawanid
                  WHERE b.tanggal between  '".$tgl_1."' and '".$tgl_2."' ".$where2." ".$where3." ".$where4." order by namakaryawan asc ";
        $qLembur=mysql_query($sLembur) or die(mysql_error($conn));
		$dtKaryawan = array();
        while($rLembur=mysql_fetch_assoc($qLembur))
        {
			setIt($jlhJmLembur[$rLembur['karyawanid']],0);
			setIt($jlhJamLemburKali[$rLembur['karyawanid']],0);
			setIt($jlhUang[$rLembur['karyawanid']],0);
            $jlhJmLembur[$rLembur['karyawanid']]+=$GetLembur[$rLembur['tipelembur']][$rLembur['jamaktual']];//jumlah jam sblm perkalian
            $jlhJamLemburKali[$rLembur['karyawanid']]+=$rLembur['jamaktual'];
            $jlhUang[$rLembur['karyawanid']]+=$rLembur['uangkelebihanjam'];
            $dtKaryawan[$rLembur['karyawanid']]=$rLembur['karyawanid'];
        }
        
        $iGaji="select jumlah,karyawanid from ".$dbname.".sdm_5gajipokok where tahun='".substr($periodeGaji,0,4)."' "
                        . " and idkomponen=1";
        $nGaji=  mysql_query($iGaji) or die (mysql_error($conn));
        while($dGaji=  mysql_fetch_assoc($nGaji))
        {
            $gajiPokok[$dGaji['karyawanid']]=$dGaji['jumlah'];
        }
        
        
        
		
        $tab.="<table cellspacing='1' border='".$brdr."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr.">No.</td>
        <td ".$bgclr.">".$_SESSION['lang']['nama']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['subbagian']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['tipekaryawan']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['bagian']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['jabatan']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['total']." ".$_SESSION['lang']['absensi']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['totLembur']." Actual</td>
        <td ".$bgclr.">".$_SESSION['lang']['totLembur']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['jumlah']." (Rp)</td>";
        $tab.="<td ".$bgclr.">".$_SESSION['lang']['gaji']."</td><td  ".$bgclr.">".$_SESSION['lang']['persen']." ".$_SESSION['lang']['lembur']."</td>";
        $tab.="</tr><thead><tbody>";
        foreach($dtKaryawan as $dtKary)
        {
            $no++;
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$no."</td>";
            $tab.="<td>".$optNmKar[$dtKary]."</td>";
            $tab.="<td>".$optDtSub[$dtKary]."</td>";
            $tab.="<td>".$optTipe[$optDtTipe[$dtKary]]."</td>";
            $tab.="<td>".$optBagian[$optDtBag[$dtKary]]."</td>";
            $tab.="<td>".$optJabatan[$optDtJbtnr[$dtKary]]."</td>";
            $tab.="<td align=right>".$jmlhHadir[$dtKary]."</td>";
            $tab.="<td align=right>".$jlhJamLemburKali[$dtKary]."</td>";
            $tab.="<td align=right>".$jlhJmLembur[$dtKary]."</td>";
            $tab.="<td align=right>".number_format($jlhUang[$dtKary],0)."</td>";
            
            $tab.="<td align=right>".number_format($gajiPokok[$dtKary],2)."</td>";
            
            $persen=$jlhUang[$dtKary]/$gajiPokok[$dtKary]*100;
            $tab.="<td align=right>".number_format($persen,2)."</td>";            
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
}
switch($proses)
{
        case'preview':   
        if($periodeGaji=='')
        {
                echo"warning: Periode tidak boleh kosong";
                exit();
        }

        echo $tab;
        break;

        case'pdf':

//create Header
class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $period;
                                global $periode;
                                global $kdOrg;
                                global $kdeOrg;
                                global $tgl1;
                                global $tgl2;
                                global $where;
                                global $jmlHari;
                                global $test;
                                global $nmOrg;
                                global $pilihan;
                                global $pilihan2;

                                $jmlHari=$jmlHari*1.5;
                                $cols=247.5;
                            # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 20;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',10);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanLembur']."  Total/".$_SESSION['lang']['karyawan']." (option ".$pilihan.") ".$pilihan2,'',0,'L');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper('Overtime Recapitulation')." : ".$nmOrg,'',0,'C');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
                                $this->Ln();
                                $this->SetFont('Arial','B',7);
                                $this->SetFillColor(220,220,220);
                                $this->Cell(3/100*$width,$height,'No','TLR',0,'C',1);
                                $this->Cell(15/100*$width,$height,$_SESSION['lang']['nama'],'TLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['tipekaryawan'],'TLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['bagian'],'TLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],'TLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['total'],'TLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['totLembur'],'TLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['totLembur'],'TLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,'','TLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['gaji'],'TLR',0,'C',1);
                                $this->Cell(5/100*$width,$height,'%','TLR',1,'C',1);

                                $this->Cell(3/100*$width,$height," ",'BLR',0,'C',1);
                                $this->Cell(15/100*$width,$height," ",'BLR',0,'C',1);
                                $this->Cell(10/100*$width,$height," ",'BLR',0,'C',1);
                                $this->Cell(10/100*$width,$height," ",'BLR',0,'C',1);
                                $this->Cell(10/100*$width,$height," ",'BLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['absensi'],'BLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,'Actual','BLR',0,'C',1);	
                                $this->Cell(10/100*$width,$height,'','BLR',0,'C',1);
                                $this->Cell(10/100*$width,$height,"(Rupiah)",'BLR',0,'C',1);
                                
                                $this->Cell(10/100*$width,$height,'','LRB',0,'C',1);
                                $this->Cell(5/100*$width,$height,'','LRB',1,'C',1);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',6);
				$n3o=0;
                foreach($dtKaryawan as $dtKary)
                {
                    $n3o++;
                    $pdf->Cell(3/100*$width,$height,$n3o,1,0,'C',1);
                    $pdf->Cell(15/100*$width,$height,$optNmKar[$dtKary],1,0,'L',1);
                    $pdf->Cell(10/100*$width,$height,$optTipe[$optDtTipe[$dtKary]],1,0,'L',1);
                    $pdf->Cell(10/100*$width,$height,$optBagian[$optDtBag[$dtKary]],1,0,'L',1);
                    $pdf->Cell(10/100*$width,$height,$optJabatan[$optDtJbtnr[$dtKary]],1,0,'L',1);	
                    $pdf->Cell(10/100*$width,$height,$jmlhHadir[$dtKary],1,0,'R',1);	
                    $pdf->Cell(10/100*$width,$height,$jlhJamLemburKali[$dtKary],1,0,'R',1);	
                    $pdf->Cell(10/100*$width,$height,$jlhJmLembur[$dtKary],1,0,'R',1);
                    $pdf->Cell(10/100*$width,$height,number_format($jlhUang[$dtKary],0),1,0,'R',1);
                    $pdf->Cell(10/100*$width,$height,number_format($gajiPokok[$dtKary],2),1,0,'R',1);
                    $persen=$jlhUang[$dtKary]/$gajiPokok[$dtKary]*100;             
                    $pdf->Cell(5/100*$width,$height,number_format($persen,2),1,1,'R',1);
                    
                }

        $pdf->Output();
        break;

        case'excel':
       $wktu=date("Hms");
                $nop_="RekapLembur_total_per_orang_".$wktu."__".$kdeOrg;
                if(strlen( $tab)>0)
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
                if(!fwrite($handle, $tab))
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
        case'getTgl':
            $add='';

        if($periode!='')
        {
			$tgl=$periode; 
			$tanggal=count($tgl)>1? $tgl[0]."-".$tgl[1]: '';
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        if($pilihan2=='bulanan')
        {
            $add=" and jenisgaji='B'";

        }
        if($pilihan2=='harian')
        {
            $add=" and jenisgaji='H'";

        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where 
            kodeorg='".substr($kdUnit,0,4)."' and periode='".$tanggal."' ".$add."";
        //echo"warning".$sTgl;
        // exit("Error:".$sTgl);
        $qTgl=mysql_query($sTgl) or die(mysql_error());
        $rTgl=mysql_fetch_assoc($qTgl);
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getPeriode':
            //echo"warning:masuk";
            $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji  where kodeorg='".$kdOrg."'";
            $optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
            $qPeriode=mysql_query($sPeriode) or die(mysql_error());
            while($rPeriode=mysql_fetch_assoc($qPeriode))
            {
                $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
            }
            echo $optPeriode;
        break;
        default:
        break;
}

?>