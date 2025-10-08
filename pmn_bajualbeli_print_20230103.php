<?php
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
	$noKontrak=$_GET['column'];

	//create Header and Footer
	class PDF extends FPDF{
		function Header(){
			global $conn;
			global $dbname;
			global $userid;
			global $posting;
			global $noKontrak;
			global $bar;
			global $kodePt;
			global $tlgKontrk;
			global $kdCust;
			global $norekbayar;
			global $nama;
			global $alamatpt;
			global $wilKota;
			global $kodepos;
			global $arrStatPPn;
			global $norekbayar;

			$noKontrak=$_GET['column'];
			$str="select * from ".$dbname.".".$_GET['table']."  where nokontrak='".$noKontrak."' ";
			$res=mysql_query($str);
			$bar=mysql_fetch_assoc($res);
			$kodePt=$bar['kodeorg'];
			$tlgKontrk=tanggalnormal($bar['tanggalkontrak']);
			$kdCust=$bar['koderekanan'];
			$norekbayar=$bar['rekening'];
			//exit('Warning : pt='.$kodePt.' tgl='.$tlgKontrk.' cust='.$kdCust);

			$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodePt."'"; 
			$res1=mysql_query($str1);
			while($bar1=mysql_fetch_object($res1)){
				$nama=$bar1->namaorganisasi;
				$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				$telp=$bar1->telepon;
				$wilKota=$bar1->wilayahkota;			 
				$kodepos=$bar1->kodepos;			 
			}

			if($kodePt=='AMP'){
				$path='images/logo_amp.jpg';
			}else if($kodePt=='CKS'){
				$path='images/logo_cks.jpg';
			}else if($kodePt=='KAA'){
				$path='images/logo_kaa.jpg';
			}else if($kodePt=='KAL'){
				$path='images/logo_kal.jpg';
			}else if($kodePt=='LKA'){
				$path='images/logo_lka.jpg';
			}else if($kodePt=='MPA'){
				$path='images/logo_mpa.jpg';
			}else if($kodePt=='MHS'){
				$path='images/logo_mhs.jpg';
			}else if($kodePt=='MEA'){
				$path='images/logo_mea.jpg';
			}else if($kodePt=='SMA'){
				$path='images/logo_sma.jpg';
			}else{
				$path='images/logo.jpg';
			}
			$this->Image($path,15,5,0,30);	
			$this->SetFont('Arial','B',14);
			$this->SetFillColor(255,255,255);	
			$this->SetY(15);
			$this->SetX(45);
			$this->Cell(60,5,$nama,0,1,'L');	 
			$this->SetY(0);
		}

        function Footer(){
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
	$optKomo=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
	if(strstr(strtoupper($optKomo[$kdBrg]),'SEWA')){
		$pdf->Cell(180,5,strtoupper('BERITA ACARA SEWA PAKAI'),0,1,'C');
	}else{
		$pdf->Cell(180,5,strtoupper('BERITA ACARA JUAL BELI'),0,1,'C');
	}
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(180,5,"No : ".$noKontrak,0,1,'C');
	$pdf->Ln(10);				
	$arrStatPPn=array(0=>"Non",1=>"Include",2=>"Exclude");
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

	$whrmt="kode='".$bar['matauang']."'";
	$optMtSim=makeOption($dbname,'setup_matauang','kode,simbol',$whrmt);
	$optMtuang=makeOption($dbname,'setup_matauang','kode,matauang',$whrmt);

	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['komoditi'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$pdf->Cell(75,5,$_SESSION['lang']['namabarang'],'',0,'L');
	$pdf->Cell(13,5,$_SESSION['lang']['jumlah'],'',0,'R');
	$pdf->Cell(10,5,'Sat','',0,'L');
	$pdf->Cell(14,5,$_SESSION['lang']['harga'],'',0,'R');
	$pdf->Cell(24,5,$_SESSION['lang']['nilai'],'',1,'R');
	$pdf->SetFont('Arial','','9');
	$baris=13;
	$sBrg="select a.*,b.namabarang,b.satuan from ".$dbname.".pmn_kontraklaindt a 
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			where a.nokontrak='".$noKontrak."'";
	$qBrg=mysql_query($sBrg) or die(mysql_error());
	while($dBrg=mysql_fetch_assoc($qBrg)){
		$baris+=1;
		$pdf->Cell(44,5,'','',0,'L');
		$pdf->Cell(75,5,$dBrg['namabarang'],'',0,'L');
		$pdf->Cell(13,5,$dBrg['jumlah'],'',0,'R');
		$pdf->Cell(10,5,$dBrg['satuan'],'',0,'L');
		$pdf->Cell(14,5,number_format($dBrg['hargasatuan']),'',0,'R');
		$pdf->Cell(24,5,number_format($dBrg['jmlharga']),'',1,'R');
	}
	$baris+=1;
	$pdf->Ln(5);
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['ongkoskirim'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$baris+=2;
	if($bar['ongkoskirim']>0){
		$pdf->Cell(100,5,$optMtSim[$bar['matauang']]." ".number_format($bar['ongkoskirim'],0),'',1,'L');
		$pdf->Cell(44,5,'','',0,'L');
		$pdf->MultiCell(130,5,"(".ucfirst(terbilang($bar['ongkoskirim'],2))." ".$optMtuang[$bar['matauang']].")",0,'L',0);
	}else{
		$pdf->Cell(100,5,'-','',1,'L');
		$pdf->Cell(44,5,'','',0,'L');
		$pdf->Ln(5);
	}
	$baris+=1;
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
	$baris+=1;

	$baris+=1;
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['waktupenyerahan'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$kataSum = 'Pengiriman paling lambat diserahkan pada tanggal '.tanggalnormal($bar['tanggalkirim']);
	$tglbayar=$bar['tanggalkontrak'];
	if($bar['tanggalkirim']>$bar['tanggalkontrak']){
		$tglbayar=$bar['tanggalkirim'];
	}
	if($bar['sdtanggal']!='0000-00-00') {
		$kata[$asd].=" s.d ".tanggalnormal($bar['sdtanggal']);
		$tglbayar=$bar['sdtanggal'];
	}
	$pdf->MultiCell(130,5,$kataSum,0,'L',0);

	$baris+=1;
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['kualitas'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	if($bar['kualitas10']!=''){
		$pdf->Cell(25,5,$bar['kualitas10'],'',0,'L');
		$pdf->Cell(5,5,':','',0,'L');
		$pdf->Cell(12,5,$bar['kualitas11'].' %','',0,'R');
		if($bar['kualitas12']!='0'){
			$pdf->Cell(16,5,' - '.$bar['kualitas12'].' %','',0,'R');
		}
	}
	if($bar['kualitas20']!=''){
		$baris+=1;
		$pdf->Ln(5);
		$pdf->Cell(39,5,'','',0,'L');
		$pdf->Cell(5,5,'','',0,'L');
		$pdf->Cell(25,5,$bar['kualitas20'],'',0,'L');
		$pdf->Cell(5,5,':','',0,'L');
		$pdf->Cell(12,5,$bar['kualitas21'].' %','',0,'R');
		if($bar['kualitas22']!='0'){
			$pdf->Cell(16,5,' - '.$bar['kualitas22'].' %','',0,'R');
		}
	}
	if($bar['kualitas30']!=''){
		$baris+=1;
		$pdf->Ln(5);
		$pdf->Cell(39,5,'','',0,'L');
		$pdf->Cell(5,5,'','',0,'L');
		$pdf->Cell(25,5,$bar['kualitas30'],'',0,'L');
		$pdf->Cell(5,5,':','',0,'L');
		$pdf->Cell(12,5,$bar['kualitas31'].' %','',0,'R');
		if($bar['kualitas32']!='0'){
			$pdf->Cell(16,5,' - '.$bar['kualitas32'].' %','',0,'R');
		}
	}

	$sTrmn="select distinct * from ".$dbname.".pmn_5terminbayar where kode='".$bar['kdtermin']."'";
	$qTrmn=mysql_query($sTrmn) or die(mysql_error($conn));
	$rTrmn=mysql_fetch_assoc($qTrmn);
		
	$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$kodePt."' and noakun='".$norekbayar."'";
	$qTrmn2=mysql_query($sTrmn2) or die(mysql_error($conn));
	$rTrmn2=mysql_fetch_assoc($qTrmn2);

	$bulan=substr($tglbayar,5,2);
	$nmBulan=numToMonth($bulan,'I','long');
	$thn=substr($tglbayar,0,4);
	$tglnya=substr($tglbayar,8,2);
	$listTgl=$tglnya.' '.$nmBulan.' '.$thn;

	if($rTrmn['satu']==100){
		$ktTermin="".$rTrmn['satu']."% Setelah barang diterima atau selambat-lambatnya 4 (empat) hari setelah tanggal ".tanggalnormal($tglbayar)."\n";
	}else{
		if($kdCust=='SMR' or $kdCust=='BAP'){
			$ktTermin="".$rTrmn['satu']."% Setelah kontrak ditandatangani selambatnya tanggal ".$listTgl." \n".$rTrmn['dua']."% Selambatnya 7 (tujuh) hari setelah BA ditandatangani dan dokumen asli penagihan diterima \n";
		}else{
			$ktTermin="".$rTrmn['satu']."% Setelah kontrak ditandatangani selambatnya tanggal ".$listTgl." \n".$rTrmn['dua']."% Selambatnya 7 (tujuh) hari setelah BA ditandatangani \n";
		}		
	}

	$ktTermin2.="Pembayaran ditransfer ke :\n";
	$ktTermin2.="".$nmdt[0].".".ucwords(strtolower($nmdt[1]))."\n";
	$ktTermin2.=$rTrmn2['namabank']."\nRek : ".$rTrmn2['rekening'];
		
	$baris+=3;
	$pdf->Ln(5);
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['carapembayaran'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$pdf->MultiCell(150,5,$ktTermin,0,'L',0);
	$pdf->Cell(39,2,'','',1,'L');
	$pdf->Cell(39,5,'','',0,'L');
	$pdf->Cell(5,5,'','',0,'L');
	$baris+=4;
	$pdf->MultiCell(150,5,$ktTermin2,0,'L',0);

	$baris+=2;
	$nilKontrak=$bar['nilaikontrak'];
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,2,'','',1,'L');
	$pdf->Cell(39,5,$_SESSION['lang']['nilkontrak'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$pdf->Cell(100,5,$optMtSim[$bar['matauang']]." ".number_format($nilKontrak,0)." (".$arrStatPPn[$bar['stsppn']]." PPN".$angkaPpn.")",'',1,'L');
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,'','',0,'L');
	$pdf->Cell(5,5,'','',0,'L');
	$pdf->SetFont('Arial','','10');
	$nilKontrak=number_format($nilKontrak);
	$nilKontrak=str_replace(',','',$nilKontrak);
	$pdf->MultiCell(130,5,"(".ucfirst(terbilang($nilKontrak,2))." ".$optMtuang[$bar['matauang']].")",0,'L',0);

	$baris+=2;
	if($baris>=47){
		$pdf->Ln(50);
		$baris=2;
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
	}
	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(39,5,$_SESSION['lang']['catatanlain'],'',0,'L');
	$pdf->Cell(5,5,':','',0,'L');
	$pdf->SetFont('Arial','','10');
	$pdf->MultiCell(130,5,$toleransi.$bar['catatanlain'],0,'L',0);

	$tglTtd=explode("-",$tlgKontrk);
	$tglnya=$tglTtd[0];
	$blnnya=numToMonth($tglTtd[1],$lang='I',$format='long');
	$thnnya=$tglTtd[2];
	$tglbenernya=$tglnya.' '.$blnnya.' '.$thnnya;

	$baris+=11;
	if($baris>=47){
		$pdf->Ln(40);
		$baris=0;
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
		$pdf->Cell(5,5,'','',1,'L');
	}
	$pdf->Ln(5);
	$pdf->Cell(20,5,'','',1,'L');
	$pdf->Cell(39,5,ucwords(strtolower('Jakarta')).", ".$tglbenernya,'',1,'L');
	$pdf->Ln(5);
	$pdf->Cell(90,5,$_SESSION['lang']['penjual'].',','',0,'C');
	$pdf->Cell(90,5,$_SESSION['lang']['Pembeli'].',','',1,'C');
	$pdf->SetFont('Arial','B','10');
                
	$nmPt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	$nmPtS=explode(".",$nmPt[$kodePt]);
	setIt($nmPtS[1],'');
	$pdf->Cell(90,5,$nmPtS[0].".".ucwords(strtolower($nmPtS[1])),'',0,'C');
	$pdf->Cell(90,5,$nmdt2[0].".".$nmdt2[1],'',1,'C');
	
	$jabatanTtd=makeOption($dbname,'pmn_5ttd','nama,jabatan');
	$namaTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,penandatangan');
	$jabTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,jabatan');

	$pdf->Ln(20);
	$pdf->SetFont('Arial','BU','10');
	$pdf->Cell(90,5,ucwords(strtolower($bar['penandatangan'])),'',0,'C');
	$pdf->Cell(90,5,ucwords(strtolower($namaTtdBeli[$bar['koderekanan']])),'',1,'C');

	$pdf->SetFont('Arial','B','10');
	$pdf->Cell(90,5,$jabatanTtd[$bar['penandatangan']],'',0,'C');
	$pdf->Cell(90,5,ucwords(strtolower($jabTtdBeli[$bar['koderekanan']])),'',1,'C'); 
	$pdf->Output();
?>
