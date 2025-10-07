<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
$notransaksi=$_GET['notransaksi'];

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in 
		(select b.induk from ".$dbname.".sdm_pjdinasht a 
		left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		where notransaksi='".$notransaksi."')";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$namaptpemberitugas=$bar->namaorganisasi;
}
//=============

//create Header
class PDF extends FPDF
{
	function Header()
    {
            global $namapt;
            global $namaptpemberitugas;
            $path='images/logo.jpg';
            $this->Image($path,15,2,25);	
                $this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);	
                $this->SetY(5);   
				//$this->Cell(130,5,strtoupper($namapt),0,1,'C');	 
				$this->Cell(130,5,strtoupper($namaptpemberitugas),0,1,'C');	 
                $this->SetFont('Arial','',15);
            $this->Cell(190,5,'',0,1,'C');
                $this->SetFont('Arial','',6); 
                $this->SetY(30);
                $this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
                $this->Line(10,32,200,32);	   

    }

    function Footer()
    {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
    }

}

  $str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";	
  $res=mysql_query($str);
  $closure='Open';
  while($bar=mysql_fetch_object($res))
  {
	if($bar->statushrd=='2' and $bar->uangmuka=='0'){
		exit("Warning: Perjalanan Dinas ditolak !!!");
	}
                $jabatan='';
                $namakaryawan='';
                $bagian='';	
                $karyawanid='';
                $strc="select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan,a.nik,a.kodegolongan,c.nama as namabagian
						from ".$dbname.".datakaryawan a
						left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
						left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
                        where a.karyawanid=".$bar->karyawanid;
      $resc=mysql_query($strc);
          while($barc=mysql_fetch_object($resc))
          {
                $jabatan=$barc->namajabatan;
                $namakaryawan=$barc->namakaryawan;
                $bagian=$barc->bagian;
                $golongan=$barc->kodegolongan;
                $karyawanid=$barc->karyawanid;
                $karyawannik=$barc->nik;
                $namabagian=$barc->namabagian;
          }
$strw="select a.namaorganisasi from ".$dbname.".datakaryawan b left join ".$dbname.".organisasi a 
          on b.kodeorganisasi=a.kodeorganisasi where b.karyawanid=".$karyawanid;
$resw=mysql_query($strw);
while($barw=mysql_fetch_object($resw)){
    $namapt=$barw->namaorganisasi;
}
          //===============================	  

                $kodeorg=$bar->kodeorg;
                $persetujuan=$bar->persetujuan;
                $hrd=$bar->hrd; 
                $tujuan3=$bar->tujuan3;
                $tujuan2=$bar->tujuan2;	
                $tujuan1=$bar->tujuan1;
                $tanggalperjalanan=tanggalnormal($bar->tanggalperjalanan);
                $tanggalkembali=tanggalnormal($bar->tanggalkembali);
                $tanggalperjalananw=$bar->tanggalperjalanan;
                $tanggalkembaliw=$bar->tanggalkembali;
                $uangmuka=$bar->uangmuka;
                $dibayar=$bar->dibayar;
                $tugas1=$bar->tugas1;
                $tugas2=$bar->tugas2;
                $tugas3=$bar->tugas3;
                $tujuanlain=$bar->tujuanlain;
                $tugaslain=$bar->tugaslain;
                $pesawat=$bar->pesawat;
                $darat=$bar->darat;
                $laut=$bar->laut;
                $mess=$bar->mess;
                $hotel=$bar->hotel;	

                if($bar->lunas==1)
                  $closure='Closed';

                $statushrd=$bar->statushrd;
                if($statushrd==0)
                    $statushrd=$_SESSION['lang']['wait_approval'];
        else if($statushrd==1)
                    $statushrd=$_SESSION['lang']['disetujui'];
        else 
                    $statushrd=$_SESSION['lang']['ditolak'];

                $statuspersetujuan=$bar->statuspersetujuan;
                if($statuspersetujuan==0)
                    $perstatus=$_SESSION['lang']['wait_approval'];
        else if($statuspersetujuan==1)
                    $perstatus=$_SESSION['lang']['disetujui'];
        else 
                    $perstatus=$_SESSION['lang']['ditolak'];
        //ambil bagian,jabatan persetujuan
                $perjabatan='';
                $perbagian='';
                $pernama='';
        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=".$persetujuan;	   
        $resf=mysql_query($strf);
        while($barf=mysql_fetch_object($resf))
        {
                $perjabatan=$barf->namajabatan;
                $perbagian=$barf->bagian;
                $pernama=$barf->namakaryawan;
        }	 
//ambil jabatan, hrd

        $hjabatan='';
        $hbagian='';
        $hnama='';
        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=".$hrd;	
        $resf=mysql_query($strf);
        while($barf=mysql_fetch_object($resf))
        {
                $hjabatan=$barf->namajabatan;
                $hbagian=$barf->bagian;
                $hnama=$barf->namakaryawan;
        }

  }

        $pdf=new PDF('P','mm','A4');
        $pdf->SetFont('Arial','B',10);
        $pdf->AddPage();
        $pdf->SetY(40);
        $pdf->SetX(20);
        $pdf->SetFillColor(255,255,255); 
		$pdf->Cell(175,5,strtoupper($_SESSION['lang']['spdinas']),0,1,'C');
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',14);
		$pdf->Cell(175,5,'PERMINTAAN UANG KELUAR',0,1,'C');
        $pdf->SetFont('Arial','',10);
        //$pdf->Cell(175,5,'NO : '.$notransaksi,0,1,'C');	

        $pdf->Ln();	
        $pdf->SetX(20);			
        $pdf->Cell(30,5,$_SESSION['lang']['nm_perusahaan'],0,0,'L');	
		//$pdf->Cell(50,5," : ".strtoupper($namapt),0,1,'L');	 
		$pdf->Cell(50,5," : ".strtoupper($namaptpemberitugas),0,1,'L');	 
        $pdf->SetX(20);			
        $pdf->Cell(30,5,$_SESSION['lang']['departemen'],0,0,'L');	
		//$pdf->Cell(50,5," : ".strtoupper($_SESSION['empl']['bagian']),0,1,'L');	 
		$pdf->Cell(50,5," : ".$namabagian,0,1,'L');	 

		$pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['notransaksi'],0,0,'L');	
                $pdf->Cell(50,5," : ".$notransaksi,0,1,'L');	
		$pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['nik'],0,0,'L');	
                $pdf->Cell(50,5," : ".$karyawannik,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['namakaryawan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$namakaryawan,0,1,'L');	
        $pdf->SetX(20);	
        //$pdf->Cell(30,5,$_SESSION['lang']['bagian'],0,0,'L');	
        //        $pdf->Cell(50,5," : ".$bagian,0,1,'L');	
        //$pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['functionname'],0,0,'L');	
                $pdf->Cell(50,5," : ".$jabatan,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['tanggaldinas'],0,0,'L');	
                $pdf->Cell(50,5," : ".$tanggalperjalanan,0,1,'L');
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['tanggalkembali'],0,0,'L');	
                $pdf->Cell(50,5," : ".$tanggalkembali,0,1,'L');		
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['tujuan'],0,0,'L');
				if(!empty($tujuan2)){
					$pdf->Cell(50,5," : ".$tujuan2,0,1,'L');
				}
				if(!empty($tujuan3)){
					$pdf->SetX(50);
					$pdf->Cell(50,5," : ".$tujuan3,0,1,'L');
				}
				if(!empty($tujuanlain)){
					$pdf->SetX(50);
					$pdf->Cell(50,5," : ".$tujuanlain,0,1,'L');
				}

        $pdf->Ln();
        //$pdf->SetX(20);	
        $pdf->SetFont('Arial','B',10);		
        //$pdf->Cell(172,5,strtoupper($_SESSION['lang']['tujuandantugas']),0,1,'L');		
        $pdf->SetX(10);
        $pdf->Cell(7,5,($_SESSION['lang']['nourut']),1,0,'C');
                $pdf->Cell(23,5,($_SESSION['lang']['noakun']),1,0,'C');	
                $pdf->Cell(75,5,($_SESSION['lang']['uraian']),1,0,'C');	
                $pdf->Cell(37,5,($_SESSION['lang']['keterangan']),1,0,'C');	
                $pdf->Cell(18,5,($_SESSION['lang']['aruskas']),1,0,'C');	
                $pdf->Cell(30,5,($_SESSION['lang']['jumlah']),1,1,'C');
		if(strstr(strtoupper(substr($_SESSION['empl']['lokasitugas'],0,4)),'HO')){
			$noakun='8221001';
		}else{
			$noakun='1180300';
		}

/*
	//ambil jabatan, karyawan perdin
	$kjabatan='';
	$kbagian='';
	$knama='';
	$kgolongan='';
	$strf="select a.bagian,b.namajabatan,a.namakaryawan,a.kodegolongan from ".$dbname.".datakaryawan a left join
	       ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		   where karyawanid=".$karyawanid;	
	$resf=mysql_query($strf);
	while($barf=mysql_fetch_object($resf))
	{
		$kjabatan=$barf->namajabatan;
		$kbagian=$barf->bagian;
		$knama=$barf->namakaryawan;
		$kgolongan=$barf->kodegolongan;
	}
*/	
	//Get Lokasi Tugas
	$strLTgs="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$tujuan1."'";
	$resLTgs=mysql_query($strLTgs);
	while($barLTgs=mysql_fetch_object($resLTgs)){
		$LTgs=$barLTgs->namaorganisasi;
	}
	
	// PT Tujuan
	$qTujuan = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".$tujuan2."'");
	$resTujuan = fetchData($qTujuan);
	$ptTujuan = $resTujuan[0]['induk'];

	// Regional Tujuan
	$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan2."'");
	$resRegional = fetchData($qRegional);
	$reg = $resRegional[0]['regional'];
	if(empty($reg)){
		$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan3."'");
		$resRegional = fetchData($qRegional);
		$reg = $resRegional[0]['regional'];
		if(empty($reg)){
			$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"regional='".$tujuanlain."'");
			$resRegional = fetchData($qRegional);
			$reg = $resRegional[0]['regional'];
			if(empty($reg)){
				$reg='KALIMANTAN';
			}
		}
	}

	// Get Hari Libur
	$strlibur="select count(*) as jumlahlibur from ".$dbname.".sdm_5harilibur where kebun in ('HOLDING','GLOBAL') and keterangan='libur' and (tanggal>='".$tanggalperjalananw."' and tanggal<='".$tanggalkembaliw."')";
	$reslibur=mysql_query($strlibur);
	$jmlhrlibur=0;
	while($barlibur=mysql_fetch_object($reslibur))
	{ 
		$jmlhrlibur=$barlibur->jumlahlibur;
	}

	//Get Uang Muka
	function getRangeTanggal($tglAwal,$tglAkhir){
		$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
		$jlhHari = $jlh / (3600*24);
		return $jlhHari + 1;
	}
	$jlhHari=getRangeTanggal($tanggalperjalanan,$tanggalkembali);
    $jmlharilokal=$jlhHari-$jmlhrlibur;
	//exit('Warning:'.$jlhHari." - ".$jmlhrlibur." = ".$jmlharilokal."  ".$strlibur);

	if($jlhHari == 1){
		$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
		where a.regional='".$reg."' and a.kodegolongan='".$golongan."' and a.jenis in (2,6,7) order by a.jenis";
	}else{
		$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
		where a.regional='".$reg."' and a.kodegolongan='".$golongan."' and a.jenis in (2,6,8,9,10,11) order by a.jenis";
	}
	//exit('Warning:'.$sUangMuka);
	$rUangMuka=mysql_query($sUangMuka);
	$jlhUangMuka=0;
	if($rUangMuka) {
		$nilaipjd=0;
		$nu=1;
		while($bUangMuka=mysql_fetch_object($rUangMuka)) {
			if(($pesawat+$darat+$laut)==0 and $bUangMuka->jenis=='2'){
				continue;
			}
			if(($hotel==0 or substr($kodeorg,2,2)=='HO' or strstr(strtoupper($tujuan2.' '.$tujuan3.' '.$tujuanlain),'HO') or strstr(strtoupper($tujuan2.' '.$tujuan3.' '.$tujuanlain),'JAKARTA')) and $bUangMuka->jenis=='8'){
				continue;
			}
			if($bUangMuka->sekali!=0){
				$nilaipjd=$bUangMuka->sekali;
				$jmlkali=1;
			}
			if($bUangMuka->perhari!=0){
				if($bUangMuka->jenis==10){
				   $nilaipjd=$bUangMuka->perhari*$jmlharilokal;
				   $jmlkali=$jmlharilokal;
				}elseif($bUangMuka->jenis==8){
					$nilaipjd=$bUangMuka->perhari*($jlhHari-1);
					$jmlkali=$jlhHari-1;
				}else{
				   $nilaipjd=$bUangMuka->perhari*$jlhHari;
				   $jmlkali=$jlhHari;
				}
			}
			if($bUangMuka->hariketiga!=0){
				if($bUangMuka->jenis==10){
					$nilaipjd=$bUangMuka->hariketiga*($jmlharilokal - 2);
					$jmlkali=$jmlharilokal-2;
				}else{
					$nilaipjd=$bUangMuka->hariketiga*($jlhHari - 2);
					$jmlkali=$jlhHari-2;
				}
			}
			$jlhUangMuka+=$nilaipjd;
			if($xhrd==0 or $xper==0){
				if($jmlkali!=0){
					if($bUangMuka->jenis=='1' or $bUangMuka->jenis=='2' or $bUangMuka->jenis=='3' or $bUangMuka->jenis=='4' or $bUangMuka->jenis=='5' or $bUangMuka->jenis=='10'){
						//if(strstr('CCFI CFT COM DIR IT OACC OBCC OFA OMIS ROA URD',strtoupper($bagian))){
						if(strtoupper($bagian)=='CCFI' or strtoupper($bagian)=='CFT' or strtoupper($bagian)=='COM' or strtoupper($bagian)=='DIR' or strtoupper($bagian)=='IT' or strtoupper($bagian)=='OACC' or strtoupper($bagian)=='OBCC' or strtoupper($bagian)=='OFA' or strtoupper($bagian)=='OMIS' or strtoupper($bagian)=='ROA' or strtoupper($bagian)=='URD'){
							$aruskas='310234';
						}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
							$aruskas='310224';
						}else{
							$aruskas='310214';
						}
					}else if($bUangMuka->jenis=='8'){
						if(strtoupper($bagian)=='CCFI' or strtoupper($bagian)=='CFT' or strtoupper($bagian)=='COM' or strtoupper($bagian)=='DIR' or strtoupper($bagian)=='IT' or strtoupper($bagian)=='OACC' or strtoupper($bagian)=='OBCC' or strtoupper($bagian)=='OFA' or strtoupper($bagian)=='OMIS' or strtoupper($bagian)=='ROA' or strtoupper($bagian)=='URD'){
							$aruskas='310231';
						}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
							$aruskas='310221';
						}else{
							$aruskas='310211';
						}
					}else{
						if(strtoupper($bagian)=='CCFI' or strtoupper($bagian)=='CFT' or strtoupper($bagian)=='COM' or strtoupper($bagian)=='DIR' or strtoupper($bagian)=='IT' or strtoupper($bagian)=='OACC' or strtoupper($bagian)=='OBCC' or strtoupper($bagian)=='OFA' or strtoupper($bagian)=='OMIS' or strtoupper($bagian)=='ROA' or strtoupper($bagian)=='URD'){
							$aruskas='310233';
						}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
							$aruskas='310223';
						}else{
							$aruskas='310213';
						}
					}
					$pdf->SetFont('Arial','',10);
					$pdf->SetX(10);
					$pdf->Cell(7,5,$nu++,1,0,'L');
					$pdf->Cell(23,5,$noakun,1,0,'L');	
					$pdf->Cell(75,5,$bUangMuka->namajenis,1,0,'L');	
					$pdf->Cell(37,5,$jmlkali.' '.$_SESSION['lang']['hari'].' x '.
						substr("       ".number_format(($bUangMuka->sekali+$bUangMuka->perhari+$bUangMuka->hariketiga),2,',','.'),-12),1,0,'L');	
					$pdf->Cell(18,5,$aruskas,1,0,'R');	
					$pdf->Cell(30,5,number_format($nilaipjd,2,',','.'),1,1,'R');	
				}
			}
		}
	}

		$pdf->SetFont('Arial','B',10);
		$pdf->SetX(10);
		$pdf->Cell(160,5,'Total',1,0,'C');	
		$pdf->Cell(30,5,number_format($jlhUangMuka,2,',','.'),1,1,'R');	

//=============================
/*pertanggungjawaban
	$str="select a.*,b.keterangan as jns from ".$dbname.".sdm_pjdinasdt a
		left join ".$dbname.".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
        where a.notransaksi='".$notransaksi."' limit 1";
	$res=mysql_query($str);
	$PrtgJwbPJD=0;
	while($bar=mysql_fetch_object($res)){
		$PrtgJwbPJD=1;
	} 
	if($PrtgJwbPJD==1) {
		$pdf->Ln();
        $pdf->SetX(10);	
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(172,5,strtoupper($_SESSION['lang']['pertanggungjawabandinas']),0,1,'L');	
        $pdf->SetX(10);
                $pdf->Cell(7,5,($_SESSION['lang']['nourut']),1,0,'C');
                $pdf->Cell(45,5,($_SESSION['lang']['jenisbiaya']),1,0,'C');	
                $pdf->Cell(22,5,($_SESSION['lang']['tanggal']),1,0,'C');
                $pdf->Cell(56,5,($_SESSION['lang']['keterangan']),1,0,'C');
                $pdf->Cell(30,5,($_SESSION['lang']['jumlah']),1,0,'C');
                $pdf->Cell(30,5,($_SESSION['lang']['disetujui']),1,1,'C');
        $pdf->SetFont('Arial','',10);
		//========ambil detail
		$str="select a.*,b.keterangan as jns from ".$dbname.".sdm_pjdinasdt a
			left join ".$dbname.".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
			where a.notransaksi='".$notransaksi."'";
		$res=mysql_query($str);
		$no=0;
		$total=0;
		$total1=0;
		while($bar=mysql_fetch_object($res))
		{
			$no+=1;
			$pdf->SetX(10);	
                $pdf->Cell(7,5,$no,1,0,'L');
                $pdf->Cell(45,5,$bar->jns,1,0,'L');
                $pdf->Cell(22,5,tanggalnormal($bar->tanggal),1,0,'L');
                $pdf->Cell(56,5,$bar->keterangan,1,0,'L');
                $pdf->Cell(30,5,number_format($bar->jumlah,2,'.','.'),1,0,'R');
                $pdf->Cell(30,5,number_format($bar->jumlahhrd,2,'.','.'),1,1,'R');
			$total+=$bar->jumlah;
			$total1+=$bar->jumlahhrd;		
		}
        $balance=$dibayar-$total1;
        $pdf->SetFont('Arial','B',10);
        $pdf->SetX(10);
		$pdf->Cell(130,5,strtoupper($_SESSION['lang']['total']),1,0,'C');
        $pdf->Cell(30,5,number_format($total,2,'.','.'),1,0,'R');
        $pdf->Cell(30,5,number_format($total1,2,'.','.'),1,1,'R');
		//$pdf->Ln();
		//$pdf->SetX(30);
		//$pdf->Cell(50,5,$_SESSION['lang']['saldo'].": ".number_format($balance,2,'.','.')." *[".$closure."]",0,1,'L');
	}
*/
//==========================
//TTd
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->SetX(10);
        $baris=$pdf->GetY();
        $pdf->Line(10, $baris, 10, $baris+20);
        $pdf->Line(60, $baris, 60, $baris+20);
        $pdf->Line(110, $baris, 110, $baris+20);
        $pdf->Cell(50,5,($_SESSION['lang']['dibuat']),1,0,'C');
        $pdf->Cell(50,5,($_SESSION['lang']['disetujui']),1,1,'C');			
        $pdf->SetFont('Arial','',10);
        $pdf->SetX(10);
        //$pdf->Cell(50,5,$hjabatan,1,0,'C');
        //$pdf->Cell(50,5,$hbagian,1,1,'C');
        $pdf->Cell(50,5,'',0,0,'C');
        $pdf->Cell(50,5,'',0,1,'C');
        $pdf->Cell(50,5,'',0,0,'C');
        $pdf->Cell(50,5,'',0,1,'C');
        $pdf->Cell(50,5,'',0,0,'C');
        $pdf->Cell(50,5,'',0,1,'C');
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
			$pdf->Cell(50,5,'HR Service Adm',1,0,'C');
			$pdf->Cell(50,5,'HR Service Manager',1,1,'C');
		}else{
			$pdf->Cell(50,5,'HR & GA',1,0,'C');
			$pdf->Cell(50,5,'ROA',1,1,'C');
		}
//footer================================
    $pdf->Ln();
    $pdf->Output();
?>
