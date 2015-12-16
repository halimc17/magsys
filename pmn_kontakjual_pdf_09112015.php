<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');
require_once('lib/terbilang.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============
$noKontrak=$_GET['column'];
//create Header
class PDF extends FPDF{
        function Header(){
        global $conn;
        global $dbname;
		global $userid;
        global $posting;
        global $noKontrak;
        global $kodePt;
        global $kdBrg;
        global $tlgKontrk;
        global $kdCust;
        global $nmBrg;
        global $wilKota;
        global $nama;
		global $bar;
		global $arrStatPPn;

                        $noKontrak=$_GET['column'];
                        //$nospb=substr($noSpb,0,4);

                        $str="select * from ".$dbname.".".$_GET['table']."  where nokontrak='".$noKontrak."' ";
                        //echo $str;exit();
                        $res=mysql_query($str);
                        $bar=mysql_fetch_assoc($res);
                        $kodePt=$bar['kodept'];
                        $kdBrg=$bar['kodebarang'];
                        $tlgKontrk=tanggalnormal($bar['tanggalkontrak']);
                        $kdCust=$bar['koderekanan'];

                        //echo $posting; exit();	
                        //ambil nama pt
                           $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodePt."'"; 
                           $res1=mysql_query($str1);
                           while($bar1=mysql_fetch_object($res1))
                           {
                                 $nama=$bar1->namaorganisasi;
                                 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                                 $telp=$bar1->telepon;	
                                 $wilKota=$bar1->wilayahkota;			 
                           }    

                        $sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$kdBrg."'";
                        $qBrg=mysql_query($sBrg) or die(mysql_error());
                        $rBrg=mysql_fetch_assoc($qBrg);
                        $nmBrg=$rBrg['namabarang'];

             /*   $path='images/logo.jpg';
            $this->Image($path,15,5,40);	
                $this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);	
                $this->SetX(55);   
            $this->Cell(60,5,$nama,0,1,'L');	 
                $this->SetX(55); 		
            $this->Cell(60,5,$alamatpt,0,1,'L');	
                $this->SetX(55); 			
                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                $this->Line(10,30,200,30);
                $this->Ln(10);*/
                


        }


        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }

}
        $pdf=new PDF('P','mm','A4');
		$pdf->SetMargins(20,'',20);
        $pdf->AddPage();
		$pdf->Ln(45);
                $pdf->SetFont('Arial','BU','12');
                $pdf->Cell(180,5,strtoupper($_SESSION['lang']['kontrakJual']),0,1,'C');
                $pdf->SetFont('Arial','B','10');
                $pdf->Cell(180,5,"No : ".$noKontrak,0,1,'C');
                $pdf->Ln(10);				
        $arrStatPPn=array(0=>"Exclude",1=>"Include");
        $pdf->SetFont('Arial','B','10');
        
        $pdf->Cell(39,5,$_SESSION['lang']['penjual'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
        $pdf->SetFont('Arial','','10');
        $nmdt=explode(".",$nama);
		setIt($nmdt[1],'');
        $pdf->Cell(100,5,$nmdt[0].".".ucwords(strtolower($nmdt[1])),'',1,'L');
        
        $pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,'','',0,'L');
        $pdf->Cell(5,5,'','',0,'L');
        $pdf->SetFont('Arial','','10');
        $whrpt="kodeorg='".$kodePt."'";
        $almtPt=makeOption($dbname,'setup_org_npwp','kodeorg,alamatdomisili',$whrpt);
        $npwpPt=makeOption($dbname,'setup_org_npwp','kodeorg,npwp',$whrpt);
        $pdf->MultiCell(130,5,$almtPt[$kodePt],0,'L',0);
        
        $pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,$_SESSION['lang']['npwp']." ".$_SESSION['lang']['penjual'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
        $pdf->SetFont('Arial','','10');
        $pdf->Cell(100,5,$npwpPt[$kodePt],'',1,'L');	
        #data pembeli 
		$whrpemb="kodecustomer='".$kdCust."'";
		$optNm=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrpemb);
		$optNmAlmt=makeOption($dbname,'pmn_4customer','kodecustomer,alamat',$whrpemb);
		$optNpwp=makeOption($dbname,'pmn_4customer','kodecustomer,npwp',$whrpemb);
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,$_SESSION['lang']['Pembeli'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$nmdt2=explode(".",$optNm[$kdCust]);
		if(count($nmdt2)==0){
			$nmdt2=$optNm[$kdCust];
		}
        //$pdf->Cell(100,5,$nmdt2[0].".".ucwords(strtolower($nmdt2[1])),'',1,'L');
                $pdf->Cell(100,5,$nmdt2[0].".".$nmdt2[1],'',1,'L');
		$pdf->SetFont('Arial','B','10');
		$pdf->Cell(39,5,'','',0,'L');
        $pdf->Cell(5,5,'','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->MultiCell(130,5,$optNmAlmt[$kdCust],0,'L',0);
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,$_SESSION['lang']['npwp']." ".$_SESSION['lang']['Pembeli'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
        $pdf->Cell(100,5,$optNpwp[$kdCust],'',1,'L');
		$whrKomo="kodecustomer='".$kdCust."' and kodebarang='".$kdBrg."'";
		$optKomo=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$pdf->SetFont('Arial','B','10');
                
                
                
        $pdf->Cell(39,5,$_SESSION['lang']['komoditi'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(100,5,$optKomo[$kdBrg],'',1,'L');
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,$_SESSION['lang']['kuantitas'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(100,5,number_format($bar['kuantitaskontrak'],2)." ".$bar['satuan'],'',1,'L');
		$whrmt="kode='".$bar['matauang']."'";
		$optMtSim=makeOption($dbname,'setup_matauang','kode,simbol',$whrmt);
		$optMtuang=makeOption($dbname,'setup_matauang','kode,matauang',$whrmt);
		
		
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,$_SESSION['lang']['hargasatuan'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(100,5,$optMtSim[$bar['matauang']]." ".number_format($bar['hargasatuan'],2)." (".$arrStatPPn[$bar['ppn']]." PPn)",'',1,'L');
		$pdf->Cell(39,5,"",'',0,'L');
        $pdf->Cell(5,5,'','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->MultiCell(150,10,"(".ucfirst($bar['terbilang'])." ".$optMtuang[$bar['matauang']].")",0,'L',0);
		
		
		$whrfrn="id_franco='".$bar['franco']."'";
		$optFrnc=makeOption($dbname,'pmn_5franco','id_franco,franco_name',$whrfrn);
                $optFrncAlamat=makeOption($dbname,'pmn_5franco','id_franco,alamat',$whrfrn);
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,'Tempat '.$_SESSION['lang']['penyerahan'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
                
               
                $arrX=array('franco'=>'Franco','loco'=>'Loco','fob'=>'FOB');

                $iFranco=" select * from ".$dbname.".pmn_5franco where id_franco='".$bar['franco']."' ";
                $nFranco=  mysql_query($iFranco) or die (mysql_error($conn));
                $dFranco=  mysql_fetch_assoc($nFranco);

                $francoList=$arrX[$dFranco['penjualan']].' '.$dFranco['franco_name'].' '.$dFranco['alamat'];
                
                
		$pdf->Cell(100,5,$francoList,'',1,'L');
		$arrRom=array("0"=>"I","1"=>"II","2"=>"III","3"=>"IV");
		for($asd=3;$asd>=0;$asd--){
			if($asd!=0){
				if($bar['kuantitaskirim'.$asd]!=0){
					$kata[$asd]="Tahap ".$arrRom[$asd]." sebanyak ".number_format($bar['kuantitaskirim'.$asd],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim'.$asd])." s.d ".tanggalnormal($bar['sdtanggal'.$asd])."\n";
				}
			}else{
				if(isset($kata) and count($kata)!=0){
					$kata[$asd]="Tahap ".$arrRom[$asd]." sebanyak ".number_format($bar['kuantitaskirim'],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim'])." s.d ".tanggalnormal($bar['sdtanggal'])."\n";
				}else{
					$kata[$asd]="Pengiriman sebanyak ".number_format($bar['kuantitaskirim'],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim']);
					if($bar['sdtanggal']!='0000-00-00') {
						$kata[$asd].=" s.d ".tanggalnormal($bar['sdtanggal'])."";
					}
				}
			}
		}
		$pdf->SetFont('Arial','B','10');
		$pdf->Cell(39,5,$_SESSION['lang']['waktupenyerahan'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$kataSum = '';
		foreach($kata as $k) {
			$kataSum .= $k;
		}
		$pdf->MultiCell(130,5,$kataSum,0,'L',0);
		
                //$bar['hargasatuan']
                $ffaData=number_format($bar['ffa'],2).' ';
                $dobiData=number_format($bar['dobi'],2).' ';
                $mdaniData=number_format($bar['mdani'],2).' ';
                $moistData=number_format($bar['moist'],2).' ';
                $dirtData=number_format($bar['dirt'],2).' ';
                $gradingData=number_format($bar['grading'],2).' ';
                
                if($ffaData==0 and $dobiData==0 and $mdaniData==0 and $moistData==0 and $dirtData==0 and $gradingData==0)
                {
                    $pdf->SetFont('Arial','B','10');
                    $pdf->Cell(39,5,$_SESSION['lang']['kualitas'],'',0,'L');
                    $pdf->Cell(5,5,':','',1,'L');
                }
                else 
                {
                    $pdf->SetFont('Arial','B','10');
                    $pdf->Cell(39,5,$_SESSION['lang']['kualitas'],'',0,'L');
                    $pdf->Cell(5,5,':','',0,'L');
                }
                
                $pdf->SetFont('Arial','','10');
                
		/*if($bar['ffa']!=0){
			$ktKualitas.="FFA ".$bar['ffa']."% MAX; ";
		}
		if($bar['mdani']!=0){
			$ktKualitas.="M&I ".$bar['mdani']."% MAX; ";
		}
		if($bar['dobi']!=0){
			$ktKualitas.="DOBI ".$bar['mdani']."% MIN";
		}*/
                if($ffaData!=0)
                {
                    $pdf->Cell(15,5,'FFA','',0,'L');
                    $pdf->Cell(5,5,':','',0,'L');
                    $pdf->Cell(5,5,$ffaData.' % Max','',1,'L');
                }
                
                if($dobiData!=0)
                {
                $pdf->SetX(64);
                $pdf->Cell(15,5,'Dobi','',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
                $pdf->Cell(5,5,$dobiData.' Min','',1,'L');
                }
                
                if($mdaniData!=0)
                {
                $pdf->SetX(64);
                $pdf->Cell(15,5,'M & I','',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
                $pdf->Cell(5,5,$mdaniData.' % Max','',1,'L');
                }
                
                if($moistData!=0)
                {
                $pdf->SetX(64);
                $pdf->Cell(15,5,'Moisture','',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
                $pdf->Cell(5,5,$moistData.' % Max','',1,'L');
                }
                
                if($dirtData!=0)
                {
                $pdf->SetX(64);
                $pdf->Cell(15,5,'Impurities','',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
                $pdf->Cell(5,5,$dirtData.' % Max','',1,'L');
                } 
                
                if($gradingData!=0)
                {
                $pdf->SetX(64);
                $pdf->Cell(15,5,'Grading','',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
                $pdf->Cell(5,5,$gradingData.' %','',1,'L');
                } 
                
                
		$sTrmn="select distinct * from ".$dbname.".pmn_5terminbayar where kode='".$bar['kdtermin']."'";
		$qTrmn=mysql_query($sTrmn) or die(mysql_error($conn));
		$rTrmn=mysql_fetch_assoc($qTrmn);
		
		//$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$bar['kodept']."' and noakun='".$bar['rekening']."'";
		$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$bar['kodept']."'";
                $qTrmn2=mysql_query($sTrmn2) or die(mysql_error($conn));
		$rTrmn2=mysql_fetch_assoc($qTrmn2);
                
                
                
                $bulan=substr($bar['tglpembayarpertama'],5,2);
                $nmBulan=numToMonth($bulan,'I','long');
                
                $thn=substr($bar['tglpembayarpertama'],0,4);
                $tglnya=substr($bar['tglpembayarpertama'],8,2);
                
              //  echo $tglnya;
                $listTgl=$tglnya.' '.$nmBulan.' '.$thn;
                
                
                if($rTrmn['satu']==100)
                {
                    $ktTermin="".$rTrmn['satu']."% Setelah kontrak ditandatangani selambatnya tanggal ".$listTgl." \n \n";
		
                }
                else
                {
                    $ktTermin="".$rTrmn['satu']."% Setelah kontrak ditandatangani selambatnya tanggal ".$listTgl." \n".$rTrmn['dua']."% Selambatnya 7 (tujuh) hari setelah BA ditandatangani \n \n";
		
                }
		
                
                $ktTermin.="Pembayaran ditransfer ke :\n";
                $ktTermin.="".$nmdt[0].".".ucwords(strtolower($nmdt[1]))."\n";
		$ktTermin.=$rTrmn2['namabank']."\nRek : ".$rTrmn2['rekening'];
		
                $pdf->SetFont('Arial','B','10');
                $pdf->Cell(39,5,$_SESSION['lang']['carapembayaran'],'',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->MultiCell(150,5,$ktTermin,0,'L',0);
                
                
		$nilKontrak=$bar['hargasatuan']*$bar['kuantitaskontrak'];
		$pdf->SetFont('Arial','B','10');
                $pdf->Cell(39,5,$_SESSION['lang']['nilkontrak'],'',0,'L');
                $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(100,5,$optMtSim[$bar['matauang']]." ".number_format($nilKontrak,0)." (".$arrStatPPn[$bar['ppn']]." PPn)",'',1,'L');
		$pdf->SetFont('Arial','B','10');
        $pdf->Cell(39,5,'','',0,'L');
        $pdf->Cell(5,5,'','',0,'L');
		$pdf->SetFont('Arial','','10');
		$pdf->MultiCell(130,5,"(".ucfirst(terbilang($nilKontrak,2))." ".$optMtuang[$bar['matauang']].")",0,'L',0);
        $pdf->Ln(150);
		
		
		$toleransi=$bar['toleransi']."\n";
		$pdf->SetFont('Arial','B','10');
		$pdf->Cell(39,5,'','',1,'');
        $pdf->Cell(39,5,'','',1,'');
        $pdf->Cell(39,5,'','',1,'');				
        $pdf->Cell(39,5,$_SESSION['lang']['catatanlain'],'',0,'L');
        $pdf->Cell(5,5,':','',0,'L');
		$pdf->SetFont('Arial','','10');
		//$bar['catatanlain'] = str_replace('≤',chr(163),$bar['catatanlain']);
		$pdf->MultiCell(130,5,$toleransi.$bar['catatanlain'],0,'L',0);
		$pdf->Ln(5);
        $pdf->Cell(20,5,'',0,'L');
        
        
        
        
        $tglTtd=explode("-",$tlgKontrk);
        
        $tglnya=$tglTtd[0];
        $blnnya=numToMonth($tglTtd[1],$lang='I',$format='long');
        $thnnya=$tglTtd[2];
        
        $tglbenernya=$tglnya.' '.$blnnya.' '.$thnnya;
        
        
        $pdf->Cell(39,5,ucwords(strtolower('Jakarta')).", ".$tglbenernya,'',0,'L');
        $pdf->Ln();
		$pdf->Cell(20,5,'',0,'L');
        $pdf->Cell(12,5,$_SESSION['lang']['penjual'].',','',0,'L');
        $pdf->Cell(80,5,'','',0,'L');	
        $pdf->Cell(18,5,$_SESSION['lang']['Pembeli'].',','',1,'L');
        $pdf->SetFont('Arial','B','10');
		$pdf->Cell(20,5,'',0,'L');
                
                $nmPt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
                  $nmPtS=explode(".",$nmPt[$bar['kodept']]);
        setIt($nmPtS[1],'');
		$pdf->Cell(80,5,$nmPtS[0].".".ucwords(strtolower($nmPtS[1])),'',0,'C');
		//$pdf->Cell(80,5,$nmdt2[0].".".ucwords(strtolower($nmdt2[1])),'',1,'C');
                $pdf->Cell(80,5,$nmdt2[0].".".$nmdt2[1],'',1,'C');
                
                
                
                $jabatanTtd=makeOption($dbname,'pmn_5ttd','nama,jabatan');
                $namaTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,penandatangan');
                $jabTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,jabatan');
                
              
                
		$pdf->Ln(25);
		$pdf->SetFont('Arial','BU','10');
		$pdf->Cell(20,5,'',0,'L');
		$pdf->Cell(80,5,ucwords(strtolower($bar['penandatangan'])),'',0,'C');
	
		$pdf->Cell(80,5,ucwords(strtolower($namaTtdBeli[$bar['koderekanan']])),'',1,'C');
                $pdf->SetFont('Arial','B','10');
		
                
                $pdf->Cell(20,5,'',0,'L');
		$pdf->Cell(80,5,ucwords(strtolower($jabatanTtd[$bar['penandatangan']])),'',0,'C');
		
		$pdf->Cell(80,5,ucwords(strtolower($jabTtdBeli[$bar['koderekanan']])),'',1,'C'); 


        $pdf->Output();
?>
