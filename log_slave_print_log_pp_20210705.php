<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
#echo "<pre>";
#print_r($_SESSION);
#exit;
# Get Data
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

#====================== Prepare Data
$ql="select a.kodeorg,a.nopp,a.tanggal,a.dibuat from ".$dbname.".`log_prapoht` a where a.nopp='".$column."'"; //echo $ql;
$pq=mysql_query($ql) or die(mysql_error());
$hsl=mysql_fetch_assoc($pq);
$kdr=$hsl['kodeorg'];
$unit=substr($column,15,4);

$sNmKry="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$hsl['dibuat']."'";
$qNmKry=mysql_query($sNmKry) or die(mysql_error());
$rNmKry=mysql_fetch_assoc($qNmKry);
$dibuat=$rNmKry['namakaryawan'];

$sNmkntr="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdr."'";
$qNmkntr=mysql_query($sNmkntr) or die(mysql_error());
$rNmkntr=mysql_fetch_assoc($qNmkntr);
$nmKntr=$rNmkntr['namaorganisasi'];
$tgl=tanggalnormal($hsl['tanggal']);

$whrper="kodeorg like '".$unit."%' and tutupbuku='0' ORDER BY kodeorg, periode DESC LIMIT 1";
$sqlper="select periode from ".$dbname.".`setup_periodeakuntansi` where ".$whrper;
$qryper=mysql_query($sqlper) or die(mysql_error());
$assper=mysql_fetch_assoc($qryper);
$persto=$assper['periode'];

$query="select a.*,b.*,c.namabarang,c.satuan,d.spesifikasi from ".$dbname.".".$table." a inner join ".$dbname.".`log_prapodt` b on a.nopp=b.nopp inner join ".$dbname.".`log_5masterbarang` c on b.kodebarang=c.kodebarang  left join ".$dbname.".`log_5photobarang` d on c.kodebarang=d.kodebarang where a.nopp='".$column."'"; //echo $query; exit();
$result = fetchData($query);

#====================== Prepare Header PDF
class masterpdf extends FPDF {
    function Header() {
        global $table;
        global $header;
                global $column;
                global $dbname;
                global $tgl;
                 global $nmKntr;
                global $dibuat;
                global $unit;
                global $nmOrg;
                global $column;
        # Panjang, Lebar
        $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $a=$this->Image('images/logo.jpg',$this->lMargin,10,0,75,'jpg','');
                
                $this->SetFont('Arial','B',10);
               
                $this->Ln(-15);
                 $this->SetX(100);
                $this->Cell(40/100*$width,$height,$nmKntr,'',1,'L');
                $this->SetFont('Arial','',10);
                $this->SetX(100);
                 $this->Cell(40/100*$width,$height,$nmOrg[$unit],'',1,'L');
                 
                 $this->SetFont('Arial','B',12);
                 $this->Cell(100/100*$width,$height,'PERMINTAAN PEMBELIAN','',1,'C');
                 $this->SetFont('Arial','',10);
                 $this->Cell(100/100*$width,$height,$column,'',1,'C');
                 $this->Cell(100/100*$width,$height,'Tanggal. '.$tgl,'',1,'C');
                
                
                /*$this->Cell(120,$height,$a,' ',0,'L');
        $this->SetFont('Arial','B',10);
                $this->Cell(40/100*$width,$height,$nmKntr,'',0,'L');
                $this->Cell(40/100*$width,$height,'TO :','',1,'L');
                $this->Cell(120,$height,' ','',0,'L');
                //$this->Cell(22/100*$width,$height,' ','',0,'L');
                $this->SetFont('Arial','B',10);
                $this->Cell(12/100*$width,$height,$_SESSION['lang']['unit'],'',0,'L');
                $this->Cell(2/100*$width,$height,':','',0,'L');
                $this->Cell(1/100*$width,$height,substr($column,15,4),'',0,'L');		
                $this->Cell(25/100*$width,$height,' ','',0,'L');
                $this->SetFont('Arial','B',10);
                $this->Cell(12/100*$width,$height,'PURCHASING DEPARTEMENT','',0,'L');
                $this->Cell(2/100*$width,$height,'','',0,'L');
                $this->Cell(1/100*$width,$height,'','',1,'L');

                //$this->Cell(40/100*$width,$height,strtoupper($_SESSION['org']['namaorganisasi']),'',0,'L');
                $this->Cell(120,$height,' ','',0,'L');
                $this->SetFont('Arial','B',10);
                $this->Cell(12/100*$width,$height,'PP NO','',0,'L');
                $this->Cell(2/100*$width,$height,':','',0,'L');
                $this->Cell(1/100*$width,$height,$column,'',0,'L');		
                $this->Cell(25/100*$width,$height,' ','',0,'L');
                $this->SetFont('Arial','B',10);
                $this->Cell(14/100*$width,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(2/100*$width,$height,':','',0,'L');
                $this->Cell(1/100*$width,$height,$tgl,'',1,'L');*/


        $this->Ln(20);
        

    }
}

#====================== Prepare PDF Setting
$pdf = new masterpdf('P','pt','A4');
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$height = 12;

$pdf->SetFont('Arial','B',8);
$pdf->AddPage();
    
        $awalXjudulno=$pdf->GetX();
        $awalYjudulatas=$pdf->GetY();
        $pdf->Cell(18,1.5*$height,'No.','TBLR',0,'C');
        
        $awalXkdbrg=$pdf->GetX();
        //$pdf->Cell(55,1.5*$height,$_SESSION['lang']['kodebarang'],'TBLR',0,'L');
        $pdf->Cell(37,1.5*$height,'Kode','TBLR',0,'C');
        
        $awalXnmbrgjudul=$pdf->GetX();
        $pdf->Cell(165,1.5*$height,$_SESSION['lang']['namabarang'],'TBLR',0,'C');
        
        $awalXjum=$pdf->GetX();
        $pdf->Cell(35,1.5*$height,$_SESSION['lang']['jumlah'],'TBLR',0,'L');
        
        $awalXsat=$pdf->GetX();
        $pdf->Cell(30,1.5*$height,$_SESSION['lang']['satuan'],'TBLR',0,'C');
        
        $awalXreq=$pdf->GetX();
        $pdf->Cell(40,1.5*$height,'Required','TBLR',0,'C');
        
        $awalXsto=$pdf->GetX();
        $pdf->Cell(35,1.5*$height,$_SESSION['lang']['stock'],'TBLR',0,'C');

		$awalXket=$pdf->GetX();
        $pdf->Cell(190,1.5*$height,$_SESSION['lang']['keterangan'],'TBLR',0,'C');
        $akhirXket=$pdf->GetX();
        
        $pdf->Ln();
        $awalYbanget=$pdf->GetY();
        
        //echo $awalYbanget.__;
        
        $no=0;
        
        foreach($result as $data) {
			
			$kdbar=$data['kodebarang'];
			$whrsto="LEFT(kodegudang,4)='".$unit."' and periode='".$persto."' and kodebarang='".$kdbar."' GROUP BY kodebarang";
			$sqlsto="select sum(saldoakhirqty) as stockakhir from ".$dbname.".`log_5saldobulanan` where ".$whrsto;
			$qrysto=mysql_query($sqlsto) or die(mysql_error());
			$jmlsto=0;
			if(!empty($qrysto)){
				while($rowsto=mysql_fetch_assoc($qrysto))
				{
					$jmlsto=$rowsto['stockakhir'];
				}          
			}

            $pdf->SetFont('Arial','',7);
            
                $awalXno=$pdf->GetX();
                $no++;
                $pdf->SetY($awalYbanget);
                
                $pdf->Cell(18,$height,$no,0,0,'C');
                $pdf->Cell(37,$height,$data['kodebarang'],0,0,'L');
                
                $awalYnmbrg=$pdf->GetY();
                $awalXnmbrg=$pdf->GetX()+165;
                $pdf->MultiCell(165, $height, printSpecialChar($data['namabarang']), '0', 'L');
                $akhirYnmbrg=$pdf->GetY();
                
                $pdf->SetXY($awalXnmbrg,$awalYnmbrg);
                
                //$pdf->Cell(80,$height,$data['spesifikasi'],'TBLR',0,'L');
                $pdf->Cell(35,$height,number_format($data['jumlah'],2),0,0,'C');
                $pdf->Cell(30,$height,trim($data['satuan']),'0',0,'C');
                $pdf->Cell(40,$height,tanggalnormal($data['tgl_sdt']),'0',0,'L');
                $pdf->Cell(35,$height,number_format($jmlsto,2),0,0,'C');
                //$pdf->SetFont('Arial','',6.5);
                //$height=12;
                $akhirXket=$pdf->GetX()+190;
                $pdf->MultiCell(190, $height, $data['keterangan'], '0', 'L');
                
                $pdf->SetFont('Arial','I',7);
                if($data['keteranganubah']!='') 
                {
					
                                        $pdf->SetX(390);
                                        $pdf->SetFillColor(240,240,240);
                                        $pdf->MultiCell(190, $height, "- Barang diatas diubah dengan catatan: ".$data['keteranganubah'], '0', 'L');
					//$pdf->Cell(545,$height,"Barang diatas diubah oleh Purchasing dengan catatan: ".$data['keteranganubah'],1,1,'L',1);
                }
                $whKartolak="karyawanid='".$data['ditolakoleh']."'";
                $nmKartolak=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whKartolak);
                if($data['status']=='3') 
                {
					
                                        $pdf->SetX(390);
                                        $pdf->SetFillColor(240,240,240);
                                        $pdf->MultiCell(190, $height, '- Barang telah ditolak oleh : '.$nmKartolak[$data['ditolakoleh']], '0', 'L');
					//$pdf->Cell(545,$height,"Barang diatas diubah oleh Purchasing dengan catatan: ".$data['keteranganubah'],1,1,'L',1);
                }
                
                $akhirYket=$pdf->GetY();
                
                if($akhirYnmbrg>=$akhirYket)
                {
                    $akhirYbanget=$akhirYnmbrg;
                }
                else
                {
                    $akhirYbanget=$akhirYket;
                }
                
                $pdf->Line($awalXno, $akhirYbanget, $akhirXket, $akhirYbanget);
                
                $awalYbanget=$akhirYbanget;
                
                
				
				
        }
        $akhirYloop=$akhirYbanget;
        
        $pdf->Line($awalXjudulno, $awalYjudulatas, $awalXjudulno, $akhirYloop);
        $pdf->Line($awalXkdbrg, $awalYjudulatas, $awalXkdbrg, $akhirYloop);
        $pdf->Line($awalXnmbrgjudul, $awalYjudulatas, $awalXnmbrgjudul, $akhirYloop);
        $pdf->Line($awalXjum, $awalYjudulatas, $awalXjum, $akhirYloop);
        $pdf->Line($awalXsat, $awalYjudulatas, $awalXsat, $akhirYloop);
        
        $pdf->Line($awalXreq, $awalYjudulatas, $awalXreq, $akhirYloop);
        $pdf->Line($awalXsto, $awalYjudulatas, $awalXsto, $akhirYloop);
        $pdf->Line($awalXket, $awalYjudulatas, $awalXket, $akhirYloop);
        $pdf->Line($akhirXket, $awalYjudulatas, $akhirXket, $akhirYloop);
        
        
        
		$pdf->__currentY=$pdf->SetY($akhirYloop);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(120,$height,$_SESSION['lang']['dbuat_oleh'].':'.$dibuat,'',0,'L');
        $pdf->Ln();
		$pdf->Ln();
        $pdf->Cell(120,$height,$_SESSION['lang']['approval_status'].':','',0,'L');
        $pdf->Ln();
        $ko=0;

                $pdf->Cell(20,1.5*$height,'No.','TBLR',0,'C');
                $pdf->Cell(120,1.5*$height,$_SESSION['lang']['nama'].' / '.$_SESSION['lang']['kodejabatan'],'TBLR',0,'C');
                $pdf->Cell(70,1.5*$height,$_SESSION['lang']['lokasitugas'],'TBLR',0,'C');
                $pdf->Cell(100,1.5*$height,$_SESSION['lang']['keputusan'],'TBLR',0,'C');
                $pdf->Cell(240,1.5*$height,$_SESSION['lang']['note'],'TBLR',0,'C');
                $pdf->Ln();	
                $sCek="select nopp from ".$dbname.".log_prapodt where nopp='".$column."'";
                //echo $sCek;exit();
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_num_rows($qCek);
                if($rCek>0)
                {
                        $qp="select * from ".$dbname.".`log_prapoht` where `nopp`='".$column."'"; //echo $qp;
                        $qyr=fetchData($qp);
                        foreach($qyr as $hsl)
                        {


                                for($i=1;$i<6;$i++)
                                {
                                        if($hsl['hasilpersetujuan'.$i]==1)
                                        {
                                                $b['status']=$_SESSION['lang']['disetujui'];
                                        }
                                        elseif($hsl['hasilpersetujuan'.$i]==3)
                                        {
                                                $b['status']=$_SESSION['lang']['ditolak'];
                                        }
                                        elseif($hsl['hasilpersetujuan'.$i]==''||$hsl['hasilpersetujuan'.$i]==0)
                                        {
                                                $b['status']=$_SESSION['lang']['wait_approve'];
                                        }
                                        if($hsl['persetujuan'.$i]!=0000000000)
                                        {
                                                $sql="select * from ".$dbname.".`datakaryawan` where `karyawanid`='".$hsl['persetujuan'.$i]."'"; //echo $sql;//exit();
                                                $keterangan=$hsl['komentar'.$i];
                                                $tanggal=tanggalnormal($hsl['tglp'.$i]);
                                                $query=mysql_query($sql) or die(mysql_error());
                                                $res3=mysql_fetch_object($query);

                                                $sql2="select * from ".$dbname.".`sdm_5jabatan` where kodejabatan='".$res3->kodejabatan."'";
                                                $query2=mysql_query($sql2) or die(mysql_error());
                                                $res2=mysql_fetch_object($query2);
												
												$height=12;
												##ini untuk akalin biar dinamis, jadi kita taro keterangan di atas dahulu agar
												## mendapatkan panjang heightnya, biar rapih
												$awalY2=$pdf->GetY();
												$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
												$pdf->MultiCell(240, $height, $keterangan, '0', 'L');
												$akhirY2=$pdf->GetY();
												$tinggiKet2=$akhirY2-$awalY2;
												$height2=$tinggiKet2;
												$pdf->SetY($akhirY2-$tinggiKet2);
												### tutupnya disini
												
                                                $pdf->SetFont('Arial','',7);
                                                $pdf->Cell(20,$height2,$i,'TLR',0,'C');
                                                $pdf->Cell(120,$height2,$res3->namakaryawan." (".$tanggal.") ",'TLR',0,'L');
                                                $pdf->Cell(70,$height2,$res3->lokasitugas,'TLR',0,'C');
                                                $pdf->Cell(100,$height2,$b['status'],'TLR',0,'L');
                                                $pdf->MultiCell(240,$height,$keterangan,'TLR','J');
                                                
                                                
                                                $pdf->Cell(20,1.5*$height,'','BLR',0,'C');
                                                $pdf->Cell(120,1.5*$height,$res2->namajabatan,'BLR',0,'L');
                                                $pdf->Cell(70,1.5*$height,'','BLR',0,'C');
                                                $pdf->Cell(100,1.5*$height,'','BLR',0,'C');
                                                $pdf->Cell(240,1.5*$height,'','BLR',1,'L');
                                               	
                                        }
                                        else
                                        {
                                                break;
                                        }



                                }
                        }
        }
        else
        {
                        $pdf->SetFont('Arial','',7);
                        $pdf->Cell(520,1.5*$height,"Not Found",'TBLR',0,'C');
        }
        $pdf->Cell(15,$height,'Page '.$pdf->PageNo(),'',1,'L');

# Print Out
$pdf->Output();

?>