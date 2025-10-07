<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zGrid.php');
require_once('lib/formTable.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$tmpNoJ = explode('/',$data['nojurnal']);
$org = $tmpNoJ[1];

switch($proses) {
    case 'show':
	$ids = $_POST;
	
	# Options
	$whereAsset = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting=0";
	$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
    $whereJam=" detail=1 and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
	$optCashFlow = makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay',
	    "tipe='Detail' and namalaporan='CASH FLOW DIRECT'",'2',true);
	$optMatauang = makeOption($dbname,'setup_matauang','kode,matauang');
	#dialihkan ke aktiva dalam konstruksi
	#$optAsset = makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whereAsset,'2',true);
	$optAsset = makeOption($dbname,'project','kode,nama',$whereAsset,'2',true);
	$optSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier','status=1','0',true);
	$optCustomer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',null,'0',true);
	$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKary,'0',true);
	if($_SESSION['language']=='EN'){
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,'2',true);
	}else{
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,'2',true);
	}
	$optVhc = makeOption($dbname,'vhc_5master','kodevhc,kodeorg','','2',true);
	if($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and detail=1 and (tipe = 'BLOK' or tipe = 'BIBITAN')",'',true);
	} else if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
		$optBlok = makeOption($dbname,'setup_blok','kodeorg,statusblok','','2',true);   
	} else if($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
	} else if($_SESSION['empl']['tipelokasitugas']=='TRAKSI') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
	} else {
        $optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)>6 and induk like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
    }
	
	# Kegiatan
	if($_SESSION['language']=='EN'){
		$optKlpKeg = makeOption($dbname,'setup_klpkegiatan','kodeklp,namakelompok1',null,'0',true);
		$qKegiatan = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1 as namakegiatan,kelompok').' order by noakun';
	}else{
		$optKlpKeg = makeOption($dbname,'setup_klpkegiatan','kodeklp,namakelompok',null,'0',true);
		$qKegiatan = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,kelompok').' order by noakun';
	}
	$tmpKeg = fetchData($qKegiatan);
	$optKegiatan = array(''=>'');
	foreach($tmpKeg as $row) {
	    $optKegiatan[$row['kodekegiatan']] = $row['kodekegiatan']."-".$row['namakegiatan']." (".$optKlpKeg[$row['kelompok']].")";
	}
	#$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',null,'0',true);
	
	$tmpKlp = makeOption($dbname,'setup_klpkegiatan','noakun,namakelompok');
	
	// Validasi Kurs
	$kurs = 1;
	if($data['matauang']!='IDR') {
		$qKurs = selectQuery($dbname,'setup_matauangrate','kurs',
							 "kode='".$ids['matauang']."' and daritanggal='".
							 tanggalsystem($ids['tanggal'])."'");
		$resKurs = fetchData($qKurs);
		if(empty($resKurs)) exit("Warning: Kurs ".$ids['matauang']." di tanggal ".
								 $ids['tanggal']." belum ada");
		else
			$kurs = $resKurs[0]['kurs'];
	}
	
	# Get Data
	$cols = array('nourut','noakun','keterangan','jumlah','matauang','kurs','noaruskas',
	    'kodekegiatan','kodesegment','kodeasset','kodebarang','nik','kodecustomer',
	    'kodesupplier','kodevhc','nodok','kodeblok');
	$where = "nojurnal='".$ids['nojurnal']."'";
	$query = selectQuery($dbname,'keu_jurnaldt',$cols,$where,"nojurnal desc");
	$data = fetchData($query);
	
	# Masking Nama Barang
	$arrSegment = array();
	if(!empty($data)) {
	    $whereBarang = "";
		$i=0;
	    foreach($data as $row) {
			$arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
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
	
	// Masking Segment
	if(!empty($arrSegment)) {
		$whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
		$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment);
	} else {
		$optSegment = array();
	}
	
	# Replace Code with Name
	$dataShow = $data;
	foreach($dataShow as $key=>$row) {
		setIt($optSegment[$row['kodesegment']],'');
	    $dataShow[$key]['nik'] = $optKary[$row['nik']];
	    $dataShow[$key]['noaruskas'] = $optCashFlow[$row['noaruskas']];
	    $dataShow[$key]['kodekegiatan'] = $optKegiatan[$row['kodekegiatan']];
	    $dataShow[$key]['kodecustomer'] = $optCustomer[$row['kodecustomer']];
	    $dataShow[$key]['kodesupplier'] = $optSupplier[$row['kodesupplier']];
	    $dataShow[$key]['kodevhc'] = $optVhc[$row['kodevhc']];
	    $dataShow[$key]['matauang'] = $optMatauang[$row['matauang']];
	    $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
	    if($row['kodebarang']!='' and $row['kodebarang']!='0') {
			$dataShow[$key]['kodebarang'] = $optBarang[$row['kodebarang']];
	    }
		$dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
	    $dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
		$dataShow[$key]['kurs'] = number_format($row['kurs'],2);
	}
	
	# Form
	$theForm = new uForm('jurnalForm','Form Jurnal Detail',2);
	$theForm->addEls('nourut',$_SESSION['lang']['nourut'],'0','textnum','R',3);
	$theForm->_elements[0]->_attr['disabled'] = 'disabled';
	$theForm->addEls('noakun',$_SESSION['lang']['noakun'],'','select','L',25,$optAkun);
	$theForm->addEls('keterangan',$_SESSION['lang']['keterangan'],'','text','L',25);
	$theForm->addEls('jumlah',$_SESSION['lang']['jumlah'],'0','dk','R',15);
	$theForm->_elements[3]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
	$theForm->addEls('matauang',$_SESSION['lang']['matauang'],$ids['matauang'],'select','L',25,$optMatauang);
	$theForm->_elements[4]->_attr['disabled'] = 'disabled';
	$theForm->addEls('kurs',$_SESSION['lang']['kurs'],$kurs,'textnum','R',10);
	$theForm->_elements[5]->_attr['disabled'] = 'disabled';
	$theForm->addEls('noaruskas',$_SESSION['lang']['noaruskas'],'','select','L',25,$optCashFlow);
	$theForm->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','select','L',25,$optKegiatan);
	$theForm->addEls('kodesegment',$_SESSION['lang']['segment'],'','searchSegment','L',25);
	$theForm->addEls('kodeasset',$_SESSION['lang']['aktivadalam'],'','select','L',35,$optAsset);
	$theForm->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarang','L',10);
	$theForm->addEls('nik',$_SESSION['lang']['nik'],'','select','L',35,$optKary);
	$theForm->addEls('kodecustomer',$_SESSION['lang']['kodecustomer'],'','select','L',35,$optCustomer);
	$theForm->addEls('kodesupplier',$_SESSION['lang']['kodesupplier'],'','select','L',35,$optSupplier);
	$theForm->addEls('kodevhc',$_SESSION['lang']['kodevhc'],'','select','L',35,$optVhc);
	$theForm->addEls('nodok',$_SESSION['lang']['nodok'],'','text','L',30);
	$theForm->addEls('kodeblok',$_SESSION['lang']['kodeblok'],'','select','L',30,$optBlok);
	
	
	# Table
	$theTable = new uTable('jurnalTable','Tabel Jurnal Detail',"",$data,$dataShow);
	
	# FormTable
	$formTab = new uFormTable('ftJurnalDt',$theForm,$theTable,null,
	    array('nojurnal','kodejurnal','tanggal','matauang'));
	$formTab->_target = "keu_slave_jurnal_manage_detail";
	$formTab->_defValue = '##matauang='.$ids['matauang'].'##kurs='.$kurs.'##kodesegment=##kodebarang=##keterangan=';
	$formTab->_numberFormat = '##jumlah';
	$formTab->_noEnable = '##kodesegment##kodebarang##matauang##kurs';
	$formTab->_afterCrud = "loadHeader";
	$formTab->render();
	break;
    default:
	break;
}
?>