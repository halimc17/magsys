<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'gantikegiatan':
        $kodekegiatan = $param['kodekegiatan'];

        // samain dari case 'showDetail':
		$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in('2','3','4','6')";
		$whereKary .= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		$whereKaryBukanPemanen .= " and kodejabatan != '45'"; // selain pemanen
		
		// kegiatan tunas
		$kegiatantunas=false;
		//$str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
		//    where namakegiatan like '%tunas%' or namakegiatan like '%kastrasi%'"; // ambil kegiatan tunas dan kastrasi (126100101, 126100201, 126100301, 621050101, 621090901)
		$str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
			where namakegiatan like '%tunas pokok%' and status = '1'"; // ambil kegiatan tunas pokok saja (126100101, 621050101)
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)) {
			if($kodekegiatan==$bar->kodekegiatan)$kegiatantunas=true;
		}
		
		// cek regional, KALO KALTIM BOLEH SEMUA
		$str="select * from ".$dbname.".bgt_regional_assignment 
			where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'
			";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$regional=$bar->regional;
		}
		if($regional=='KALTIM')$kegiatantunas=true;
		
		if(!$kegiatantunas) {
			// kalo tunas, pemanen boleh ada
			// selain tunas, pemanen ga boleh
			$whereKary.=$whereKaryBukanPemanen;    
		}
		$query = selectQuery($dbname,'datakaryawan','karyawanid,namakaryawan,subbagian',$whereKary,'namakaryawan');
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $optKbn.="<option value=".$bar->karyawanid.">".$bar->namakaryawan." - ".$bar->subbagian."</option>";
        } 
		echo $optKbn;    
		break;
	
    case 'cegatKegiatan': // by dz March 11, 2014
        // jika ada perubahan, ganti juga di log_slave_realisasispk_detail
        $kegiatan = $param['kodekegiatan'];
        $kodeorg = $param['kodeorg'];
        $hasilkerja = $param['hasilkerja'];
        $notransaksi = $param['notransaksi'];
        
        // cek hasil kerja ga boleh 0
        if($hasilkerja==0){
            echo "error: ".$_SESSION['lang']['hasilkerjad']." = 0.";
            exit();
        }
        
        // ambil kode parameter kegiatan
        $where = "nilai = '".$kegiatan."'";
        $cols = "kodeparameter";
		$query = selectQuery($dbname,'setup_parameterappl',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $kodeparameter=$bar->kodeparameter;
        }
        $luasareanonproduktif=0;
        $jumlahpokok=0;
        $luasareaproduktif=0;
        
        // kalo kegiatan tanam, cek. kalo luas blok = luas kerangka tidak bisa.
        $where = "kodeorg = '".$kodeorg."'";
        $cols = "luasareanonproduktif,jumlahpokok,luasareaproduktif";
        $query = selectQuery($dbname,'setup_blok',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $luasareanonproduktif=$bar->luasareanonproduktif;
            $jumlahpokok=$bar->jumlahpokok;
            $luasareaproduktif=$bar->luasareaproduktif;
        }
        @$sph=($jumlahpokok+$hasilkerja)/$luasareaproduktif;
        $maxtanam=$luasareanonproduktif*150;

        // ambil periode
        $where = "notransaksi = '".$notransaksi."'";
        $cols = "tanggal";
		$query = selectQuery($dbname,'kebun_rkb_aktifitas',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $tanggal=$bar->tanggal;
        }        
        
        // kalo kegiatan sisip, cek. kalo sisa rencanasisip-udahsisip<=0 tidak bisa.
        // ambil rencana sisip s/d pada tahun berjalan
        $where = "blok = '".$kodeorg."' and periode <= '".substr($tanggal,0,7)."' and substr(periode,1,4) = '".substr($tanggal,0,4)."' and posting ='1'";
        $cols = "sum(rencanasisip) as rencanasisip";
        $query = selectQuery($dbname,'kebun_rencanasisip',$cols,$where);
        $res=mysql_query($query);
		$rencanasisip=0;
        while($bar=mysql_fetch_object($res))
        {
            $rencanasisip+=$bar->rencanasisip;
        }
        
        $where = "notransaksi = '".$notransaksi."'";
        $cols = "tanggal";
		$query = selectQuery($dbname,'kebun_rkb_aktifitas',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $tanggal=$bar->tanggal;
        }
        
        // ambil jumlah sisip
		$sudahsisip=0;
		
        // BKM
        $query="select kodeorg,sum(hasilkerja)as telahsisip from ".$dbname.".kebun_perawatan_vw 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeorg = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";        
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $sudahsisip+=$bar->telahsisip;
        }
        // PERAWATAN
        $query="select kodeblok,sum(hasilkerjarealisasi)as telahsisip from ".$dbname.".log_baspk 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeblok = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";        
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $sudahsisip+=$bar->telahsisip;
        }
        
        $sisasisip=$rencanasisip-($sudahsisip+$hasilkerja);       
        
        if(substr($kodeparameter,0,5)=='TANAM'){
            if($hasilkerja>$maxtanam) {
                echo "error: Tidak bisa tanam baru, luas yang belum ditanam: ".number_format($luasareanonproduktif,2)." Ha, pokok bisa ditanam: ".number_format($maxtanam).". Jumlah ditanam: ".number_format($hasilkerja).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='COMPL'){
            if($sph>150) {
                echo "error: SPH setelah transaksi lebih dari 150: ".number_format($sph,2).".";
                exit();
            }
        } 
        if(substr($kodeparameter,0,5)=='SISIP'){
            if($sisasisip < 0) {
                echo "error: Harap diinput data pokok mati dan rencana sisipan, \nrencana sisip: ".$rencanasisip.", \nsudah sisip: ".$sudahsisip." + ".$hasilkerja.", \nsisa rencana sisip: ".$sisasisip.".";
                exit();
            } elseif($sisasisip > 0) {
				echo "Message: Rencana sisip tersisa: ".$sisasisip;
			} elseif($rencanasisip >0 and $sisasisip == 0) {
				echo "Message: Rencana Sisip sudah selesai dilakukan. Silahkan buat BA Penyisipan";
			}
        }
		break;
	
    case 'cekSisip': // by dz March 13, 2012
        $kegiatan = $param['kodekegiatan'];
        $where = "nilai = '".$kegiatan."' and kodeparameter like 'SISIP%' and kodeaplikasi = 'TN'"; // kalo kodeparameter SISIP
        $cols = "kodeaplikasi";
		$query = selectQuery($dbname,'setup_parameterappl',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $kodeaplikasi=$bar->kodeaplikasi;
        }
        echo $kodeaplikasi;        
		break;
	
    case 'saveSisip': // by dz March 15, 2012
		$notrans = $param['notrans'];
        $kodeorg = $param['kodeorg'];
        $jumlah = $param['jumlah'];
        $penyebab = $param['penyebab'];
        $where = "notransaksi = '".$notrans."'";
        $cols = "tanggal";
		$query = selectQuery($dbname,'kebun_rkb_aktifitas',$cols,$where);
        $res=mysql_query($query);
        while($bar=mysql_fetch_object($res))
        {
            $tanggal=$bar->tanggal;
        }
        $qwe="INSERT INTO `".$dbname."`.`kebun_sisip` (`notransaksi` ,`tanggal` ,`kodeorg` ,`jumlah` ,`penyebab`)
        VALUES ('".$notrans."', '".$tanggal."', '".$kodeorg."', '".$jumlah."', '".$penyebab." ')";
        if(!mysql_query($qwe)) {
            echo "Error:".addslashes(mysql_error($conn).$str);
        }
		break;
	
    case 'showDetail':
		#== Prep Tab
		$headFrame = array(
			$_SESSION['lang']['prestasi'],
			$_SESSION['lang']['absensi'],
			$_SESSION['lang']['material']
		);
		$contentFrame = array();
		
		$blokStatus = $_SESSION['tmp']['actStat'];
		
		# Options
		//$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan<>0";
		$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in('1','2','3','4','6')";
		$whereKary .= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		$whereKaryBukanPemanen = " and kodejabatan != '45'"; // selain pemanen
		$whereKeg = "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and status='1' and ";
		switch($blokStatus) {
			case 'lc':
			$whereKeg = "kelompok in ('TB','TBM') and status='1'";
			break;
			case 'bibit':
			$whereKeg = "(kelompok='BBT' or kelompok='PN' or kelompok='MN') and status='1'";
			break;
			case 'tbm':
			$whereKeg = "(kelompok='TBM') and status='1'";
			break;
			case 'tm':
			$whereKeg = "kelompok='TM' and status='1'";
			break;
			default:
			$whereKeg = "kelompok='TM' and status='1'";
			break;
		}
        
        if($blokStatus=='bibit'){
           $whereOrg = " tipe='BIBITAN' and length(kodeorganisasi)>6 and left(kodeorganisasi,4)='".$param['afdeling']."'";
		   $whereDiv = " tipe='BIBITAN'";
		}
        else{
			if($blokStatus=='lc'){
                $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['afdeling']."' and luasareanonproduktif>0 and detail=1) and tipe='BLOK' and left(kodeorganisasi,4)='".$param['afdeling']."'";
			}else{
                $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['afdeling']."' and luasareaproduktif>0 and detail=1) and tipe='BLOK' and left(kodeorganisasi,4)='".$param['afdeling']."'";
			}
			$whereDiv = " tipe='AFDELING'";
            //$whereOrg = "(tipe='BLOK') and left(kodeorganisasi,4)='".$param['afdeling']."'";
            
        }
		
		// cek kegiatan, samain dengan case 'gantikegiatan':
		$str="select kodekegiatan from ".$dbname.".kebun_rkb_prestasi
			where notransaksi LIKE '".$param['notransaksi']."'
			";
		$res=mysql_query($str);
		$kodekegiatan='';
		while($bar=mysql_fetch_object($res))
		{
			$kodekegiatan=$bar->kodekegiatan;
		}        
        
		$kegiatantunas=false;        
		// kegiatan tunas
		$str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
			where kodekegiatan like '621%' and namakegiatan like '%tunas pokok%' and status = '1'"; // ambil kegiatan tunas 621 aja
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			if($kodekegiatan==$bar->kodekegiatan)$kegiatantunas=true;
		}

		// cek regional, KALO KALTIM BOLEH SEMUA
		$str="select * from ".$dbname.".bgt_regional_assignment 
			where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'
			";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$regional=$bar->regional;
		}
		if($regional=='KALTIM')$kegiatantunas=true;
		
		if(!$kegiatantunas) { 
		// kalo tunas, pemanen boleh ada    
		// selain tunas, pemanen ga boleh
			$whereKary.=$whereKaryBukanPemanen;    
		}
		//bisst
		$optKary = makeOption($dbname,'datakaryawan','karyawanid,nik,subbagian,namakaryawan',$whereKary,'6');
		if($_SESSION['language']=='EN'){
			$qKeg = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan',$whereKeg,"namakegiatan1");
		} else {
			$qKeg = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan',$whereKeg,"namakegiatan");
		}
		$resKeg = fetchData($qKeg);
		$optKeg = array();$satuan = array();
		foreach($resKeg as $row) {
			if($_SESSION['language']=='EN'){
				$optKeg[$row['kodekegiatan']] = $row['namakegiatan1']." (".$row['satuan'].") - ".$row['kodekegiatan'];
			} else {
				$optKeg[$row['kodekegiatan']] = $row['namakegiatan']." (".$row['satuan'].") - ".$row['kodekegiatan'];
			}
			$satuan[$row['kodekegiatan']] = $row['satuan'];
		}
		$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
		$optAbs = makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan','kodeabsen="H"');
		#$optOrg = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebun');
		$optBin = array('1'=>$_SESSION['lang']['yes'],'0'=>$_SESSION['lang']['no']);
		
		$lokasi=$_SESSION['empl']['lokasitugas'];
		$optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$sDiv="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereDiv." and induk='".$lokasi."'"; 
		$rDiv=mysql_query($sDiv);
		while($bDiv=mysql_fetch_object($rDiv))
		{
			$optDivisi .= "<option value=".$bDiv->kodeorganisasi.">".$bDiv->namaorganisasi."</option>";
		}
		
		#================ Prestasi Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "kodekegiatan,kodeorg,kodesegment,hasilkerja,jumlahhk,upahkerja,upahpremi";
		$query = selectQuery($dbname,'kebun_rkb_prestasi',$cols,$where);
//exit('Warning : 5= '.$query);
		$data = fetchData($query);
		$dataShow = $data;
		
		// Masking Segment
		$arrSegment = array();
		foreach($data as $row) {
			$arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
		}
		if(!empty($arrSegment)) {
			$whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
			$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment);
		} else {
			$optSegment = array();
		}
		$optSegment[''] = '';
		
		foreach($dataShow as $key=>$row) {
			#$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
			$dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
			$dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
			$dataShow[$key]['satuan'] = $satuan[$row['kodekegiatan']];
			#$dataShow[$key]['pekerjaanpremi'] = $optBin[$row['pekerjaanpremi']];
		}
		
			# Form
			$theForm2 = new uForm('prestasiForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['prestasi'],2);
			$theForm2->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','select','L',35,$optKeg);
			if($regional!='KALTIM')$theForm2->_elements[0]->_attr['onchange'] = 'gantikegiatan()';
			if(!empty($data)){
				$theForm2->addEls('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',25,$optOrg);
				$theForm2->_elements[1]->_attr['onchange'] = 'changeOrg()';
				$theForm2->_elements[1]->_attr['title'] = 'Please choose block';
				$theForm2->_elements[1]->_attr['disabled'] = 'disabled';
			}else{
				$theForm2->addEls('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',25,$optOrg);
				$theForm2->_elements[1]->_attr['onchange'] = 'changeOrg()';
				$theForm2->_elements[1]->_attr['title'] = 'Please choose block';
			}
			$theForm2->addEls('kodesegment',$_SESSION['lang']['kodesegment'],'','searchSegment','L',25,$optOrg);
			$theForm2->addEls('hasilkerja',$_SESSION['lang']['hasilkerjajumlah'],'0','textnumwsatuan','R',10);
			$theForm2->addEls('jumlahhk',$_SESSION['lang']['jumlahhk'],'0','textnum','R',10);
			$theForm2->_elements[4]->_attr['onfocus'] =
				"document.getElementById('tmpValHk').value = this.value";
			$theForm2->_elements[4]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Hk')";
			$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],'0','textnum','R',10);
			$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
			$theForm2->addEls('umr',$_SESSION['lang']['umr'],'0','textnum','R',10);
			$theForm2->_elements[6]->_attr['disabled'] = 'disabled';
			$theForm2->_elements[6]->_attr['onfocus'] =
				"document.getElementById('tmpValUmr').value = this.value";
			$theForm2->_elements[6]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Umr')";
			$theForm2->addEls('upahpremi',$_SESSION['lang']['upahpremi'],'0','textnum','R',10);
			$theForm2->_elements[7]->_attr['disabled'] = 'disabled';
			$theForm2->_elements[7]->_attr['onfocus'] =
				"document.getElementById('tmpValIns').value = this.value";
			$theForm2->_elements[7]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Ins')";
			
			
			# Table
			$theTable2 = new uTable('prestasiTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['prestasi'],$cols,$data,$dataShow);
			
			# FormTable
			$formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,array('notransaksi'));
			$formTab2->_target = "kebun_rkb_slave_prestasi";
			if(!empty($data)) {
				$formTab2->_noEnable = '##upahkerja##umr##upahpremi##kodesegment##kodeorg';
				$formTab2->_noaction = false;
				$formTab2->_afterEditMode = 'gantikegiatan';
				$theBlok = $data[0]['kodeorg'];
			} else {
				$theBlok = "";
				$contentFrame[0] ="<div id='divDivisi' style='display:block'>Divisi : <select id=divisi onchange=getDivisi('ftPrestasi_kodeorg',this)>".$optDivisi."</select></div>";
			}
			
			$contentFrame[0] .= $formTab2->prep();
		
		#================ Absensi Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "nourut,nik,absensi,jhk,umr,insentif,denda";
		$query = selectQuery($dbname,'kebun_kehadiran',$cols,$where);
		$data = fetchData($query);
		$dataShow = $data;
		
		foreach($dataShow as $key=>$row) {
			$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['absensi'] = $optAbs[$row['absensi']];
			$dataShow[$key]['umr'] = number_format($row['umr'],0);
		}
		
		#=============================== Get UMR ==============================
		$firstKary = getFirstKey($optKary);
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".date('Y')." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		#=============================== Get UMR ==============================
		
		# Form
		$theForm1 = new uForm('absensiForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['absensi'],2);
		$theForm1->addEls('nourut',$_SESSION['lang']['nourut'],'0','textnum','R',3);
		$theForm1->_elements[0]->_attr['disabled'] = 'disabled';
		$theForm1->addEls('nik',$_SESSION['lang']['nik'],'','select','L',25,$optKary);
		$theForm1->_elements[1]->_attr['onchange'] = 'updateUMR(this)';
		$theForm1->addEls('absensi',$_SESSION['lang']['absensi'],'H','select','L',25,$optAbs);
		$theForm1->addEls('jhk',$_SESSION['lang']['jhk'],'0','textnum','R',10);
		$theForm1->_elements[3]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Hk');updateUMR2()";
		$theForm1->addEls('umr',$_SESSION['lang']['umrhari'],0,'textnum','R',10);
		#$theForm1->_elements[4]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Umr')";
		$theForm1->_elements[4]->_attr['onkeyup'] = "totalVal();";
		$theForm1->addEls('insentif',$_SESSION['lang']['insentif'],'0','textnum','R',10);
		#$theForm1->_elements[5]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Ins')";
		$theForm1->_elements[5]->_attr['onkeyup'] = "totalVal();";

		$theForm1->addEls('denda',$_SESSION['lang']['denda'],'0','textnum','R',10);
		$theForm1->_elements[6]->_attr['onkeyup'] = "totalVal();";
		
		# Table
		$theTable1 = new uTable('absensiTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['absensi'],$cols,$data,$dataShow);
		
		# FormTable
		$formTab1 = new uFormTable('ftAbsensi',$theForm1,$theTable1,null,array('notransaksi'));
		$formTab1->_target = "kebun_slave_operasional_absensi";
		$formTab1->_noEnable = '##nourut';
		//$formTab1->_defValue = '##umr='.($Umr[0]['nilai']/25).'##jhk=1';
		
		$contentFrame[1] ="<input type=checkbox id=filternik onclick=filterKaryawan('nik',this) title='Filter Employee'>Filter Employee</checkbox>";
		
			$contentFrame[1] .= $formTab1->prep();
		
		#================ Material Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "kodeorg,kwantitasha,kodegudang,kodebarang,kwantitas";
		$query = selectQuery($dbname,'kebun_rkb_pakaimaterial',$cols,$where);
		$data = fetchData($query);
		
		if(!empty($data)) {
			$whereBarang = "";
			$i=0;
			foreach($data as $row) {
			if($i==0) {
				$whereBarang .= "kodebarang='".$row['kodebarang']."'";
			} else {
				$whereBarang .= " or kodebarang='".$row['kodebarang']."'";
			}
			$i++;
			}
			$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang);
		} else {
			$optBarang = array();
		}
					  $optGudang=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi'," kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='GUDANGTEMP'");
		
		$dataShow = $data;
		foreach($dataShow as $key=>$row) {
			$dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
			$dataShow[$key]['kwantitasha'] = number_format($row['kwantitasha'],2);
			$dataShow[$key]['kodegudang'] = $optGudang[$row['kodegudang']];
			$dataShow[$key]['kodebarang'] = $optBarang[$row['kodebarang']];
			$dataShow[$key]['kwantitas'] = number_format($row['kwantitas'],2);
		}
		
		# Form
		$theForm3 = new uForm('materialForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['pakaimaterial'],2);
		$theForm3->addEls('kodeorg',$_SESSION['lang']['kodeorg'],$theBlok,'select','L',40,$optOrg);
		$theForm3->_elements[0]->_attr['disabled'] = 'disabled';
		$theForm3->addEls('kwantitasha',$_SESSION['lang']['kwantitasha'],'0','textnum','R',10);        
		$theForm3->addEls('kodegudang',$_SESSION['lang']['pilihgudang'],'','select','L',40,$optGudang);
		$theForm3->_elements[2]->_attr['onchange'] = 'changeGudang()';
		$theForm3->_elements[2]->_attr['disabled'] = 'disabled';
		$theForm3->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarangGudang','L',20,null,null,null,null,'kodegudang','saldoMaterial');
		$theForm3->addEls('kwantitas',$_SESSION['lang']['kwantitas'],'0','textnum','R',10);
		//$theForm3->_elements[4]->_attr['onkeyup'] = 'cekSaldo()';
	
		
		# Table
		$theTable3 = new uTable('materialTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['pakaimaterial'],$cols,$data,$dataShow);
		
		# FormTable
		$formTab3 = new uFormTable('ftMaterial',$theForm3,$theTable3,null,array('notransaksi'));
		$formTab3->_target = "kebun_rkb_slave_material";
		$formTab3->_noClearField = '##kodebarang';
		$formTab3->_noEnable = '##kodebarang##kodeorg##kodegudang##kwantitas';
		// $formTab3->_numberFormat = '##kwantitas';
		
		$contentFrame[2] = $formTab3->prep();
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		drawTab('FRM',$headFrame,$contentFrame,150,'100%');
		echo "<input type='hidden' id='saldoMaterial' value=0>";
		echo "<input type='hidden' id='satuan' value='".json_encode($satuan)."'>";
		echo "<input type='hidden' id='firstSatuan' value='".reset($satuan)."'>";
		echo "</fieldset>";
		break;
	
    case 'updateUMR':
		$firstKary = $param['nik'];
		$jhk = $param['jhk'];
		$tanggal = $param['tanggal'];
		
		// Ambil Gaji Pokok
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		
		// Standard UMR
		$stdUmr = $Umr[0]['nilai']/25;
		
		// Upah yang didapat
		@$zUmr=$jhk*$Umr[0]['nilai']/25;
		
		// Cek UMR di Panen
		$qPanen = selectQuery($dbname,'kebun_prestasi_vw','sum(upahkerja) as upah',
							  "karyawanid = '".$firstKary."' and tanggal = '".tanggalsystem($param['tanggal'])."'");
		$resPanen = fetchData($qPanen);
		$upahPanen = $resPanen[0]['upah'];
		
		// Sisa Upah setelah panen
		$sisaUpah = $stdUmr - $upahPanen;
		
		// Jika UMR
		if($zUmr > $sisaUpah) {
			exit("Warning: Karyawan tersebut sudah bekerja di panen.\n".
				"Sisa HK yang dapat digunakan adalah ".number_format($sisaUpah / $stdUmr,2));
		} else {
			echo $zUmr;
		}
		break;
	
    case 'gatKarywanAFD':
        if($param['tipe']=='afdeling')
        {
            $subbagian=substr($param['kodeorg'],0,6);
            $str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where subbagian='".$subbagian."'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6') order by namakaryawan";
        }
        else
        {    
            $subbagian=substr($param['kodeorg'],0,4);
            $str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where lokasitugas='".$subbagian."'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6') order by namakaryawan";
        }   
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            echo"<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
        }
		break;  
	
	case 'getDivisi':
		$blokStatus = $_SESSION['tmp']['actStat'];
		if($blokStatus=='bibit'){
           $whereOrg = " tipe='BIBITAN' and length(kodeorganisasi)>6 and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
		}
        else{
			if($blokStatus=='lc'){
			$whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['chKdOrg']."' and luasareanonproduktif>0)
						and detail=1 and tipe='BLOK' and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
            }else{
			$whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['chKdOrg']."' and luasareaproduktif>0)
						and detail=1 and tipe='BLOK' and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
			}
        }
        
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereOrg." and induk like '".$param['divisi']."%' order by namaorganisasi";
        
		echo"<select id='kodeorg' onchange='changeOrg()'>";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            echo"<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
        }
		echo"</select>";
		break;

	case 'updateGudang':
		$blokStatus = $_SESSION['tmp']['actStat'];
		if(substr($param['material'],4,1)=='8'){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where kodeorganisasi like '".substr($param['material'],0,4)."9".substr($param['material'],5,1)."%' AND tipe like '%GUDANG%'";
		}else{
			if($blokStatus=='bibit'){
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where kodeorganisasi like '".substr($param['material'],0,5)."%' AND tipe like '%GUDANG%'";
			}else{
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where kodeorganisasi like '".substr($param['material'],0,4)."2".substr($param['material'],5,1)."%' AND tipe like '%GUDANG%'";
			}
		}
		//exit('Warning: '.$str.' - '.$param['material']);
		// echo"<select id='kodegudang' onchange='changeGudang()'>";
        $res=mysql_query($str);
        $bar=mysql_fetch_assoc($res);
        echo $bar['kodeorganisasi'];
        // echo"</select>";
		break; 
		
	case 'savePrestasi':
		if($param['jumlahhk'] < $param['totalAbsHk']){
			echo number_format($param['totalAbsHk'],2);
			exit('Warning : Jumlah HK Prestasi Harus lebih besar atau sama dengan HK Kehadiran = '.$param['totalAbsHk']);
		}else{
			$sUpd = "update ".$dbname.".kebun_rkb_prestasi set hasilkerja = '".$param['hasilkerja']."', jumlahhk = '".$param['jumlahhk']."' where notransaksi = '".$param['notransaksi']."'";
			mysql_query($sUpd);
			echo number_format($param['jumlahhk'],2);
		}
	break;
	
    default:
		break;
}
?>