<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');
require_once('lib/tanaman.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'showDetail':
		// Get Header
        $whereHead = "notransaksi='".
            $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
            "' and noakun='".$param['noakun']."' and tipetransaksi='".
            $param['tipetransaksi']."'";
        $qHead = selectQuery($dbname,'keu_kasbankht','*',$whereHead);
        $resHead = fetchData($qHead);
        if(empty($resHead)) {
            $defMU = 'IDR';
            $defKurs = 1;
        } else {
            $defMU = $resHead[0]['matauang'];
            $defKurs = $resHead[0]['kurs'];
        }
		
		$whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
		$queryAKB = selectQuery($dbname,'keu_5parameterjurnal',
			'jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',$whereAKB);
		$optAKB = fetchData($queryAKB);
		$tipe = "";
		foreach($optAKB as $row) {
			if($param['tipetransaksi']=='K') {
			if($param['noakun']>=$row['noakunkredit'] and $param['noakun']<=$row['sampaikredit']) {
				$tipe = $row['jurnalid'];
			}
			} else {
			if($param['noakun']>=$row['noakundebet'] and $param['noakun']<=$row['sampaidebet']) {
				$tipe = $row['jurnalid'];
			}
			}
		}
		
		# Cek Kelompok Jurnal
		$whereKel = "kodeorg='".$_SESSION['org']['kodeorganisasi'].
			"' and kodekelompok='".$tipe."'";
		$optKel = makeOption($dbname,'keu_5kelompokjurnal','kodekelompok,keterangan',$whereKel);
		if(empty($optKel)) {
			echo "Warning : Journal Group  ".$tipe." not assign for your unit/Company\n";
			echo "Please contact  IT Dept.";
			exit;
		}
		
		# Options
		if(!isset($_SESSION['org']['period']['start'])) {
            exit("Warning: Accounting Period for this unit is not set.\nPlease contact IT Dept.");
        }
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $whereKary = "kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") and tipekaryawan in ('0','1','2')";
        }else{
            $whereKary = "kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
        }
        $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan,nik',$whereKary,'4',true);
        
        $whereAsset = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting=0";
        $optAsset = makeOption($dbname,'project','kode,nama',$whereAsset,'2',true);
        $optMataUang = makeOption($dbname,'setup_matauang','kode,matauang');
        
        
        $wheresupaktif="status=1";
        $optSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',$wheresupaktif,'0',true);
        
        
        $optCustomer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',null,'0',true);
        if($_SESSION['language']=='EN'){
            // $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan,noakun',null,'6',true);
			$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan,noakun',null,'2',true);
        }else{
            // $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',null,'6',true);
			$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',null,'2',true);
        }
        
        $whereJam=" detail=1 and noakun <> '".$param['noakun']."' and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
        if($_SESSION['language']=='EN'){
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,'2',true);
        }else{
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,'2',true);
        }
        
        $optVhc = makeOption($dbname,'vhc_5master','kodevhc,kodeorg','','2',true);
        if($_SESSION['empl']['tipelokasitugas']=='KEBUN')
            $optOrgAl = makeOption($dbname,'setup_blok','kodeorg,kodeorg',"
                        kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and luasareaproduktif!=0",'',true);  
        else
            $optOrgAl = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)>6 and induk like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
        $optCashFlow = makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay',
            "tipe='Detail' and namalaporan='CASH FLOW DIRECT'",'2',true);
            $optHutangUnit = array('0'=>$_SESSION['lang']['no'],'1'=>$_SESSION['lang']['yes']);
        if($param['tipetransaksi']=='K') {
            $invTab = 'keu_tagihanht';
        } else {
            $invTab = 'keu_penagihanht';
        }
        $optInvoice = makeOption($dbname,$invTab,'noinvoice,noinvoice',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."'",'0',true);
		
		# Field Aktif
		$firstAkun = key($optAkun);
		$optField = makeOption($dbname,'keu_5akun','noakun,fieldaktif',
			"noakun='".$firstAkun."'");
		if(empty($firstAkun)) {
			$fieldAktif = '0000000';
		} else {
			$fieldAktif = $optField[$firstAkun];
		}
		
		# Get Data
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeorg='".$param['kodeorg'].
			"' and tipetransaksi='".$param['tipetransaksi'].
			"' and noakun2a='".$param['noakun']."'";
		$cols = "kode,keterangan1,noakun,noaruskas,matauang,kurs,keterangan2,jumlah,kodesegment,".
			"kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,kodevhc,orgalokasi,nodok,hutangunit1";
		$query = selectQuery($dbname,'keu_kasbankdt',$cols,$where);
		$data = fetchData($query);
		$dataShow = $data;
		
		// Masking Segment
		$arrSegment = array();
		foreach($data as $row) {
			$arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
		}
		if(!empty($arrSegment)) {
			$whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
			$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',
									 $whereSegment,'0',true);
		} else {
			$optSegment = array();
		}
		
		// Masking Akun
		$akunMask = "";
		foreach($data as $row) {
			if(!empty($akunMask)) $akunMask.=',';
			$akunMask .= "'".$row['noakun']."'";
		}
		if(empty($akunMask)) {
			$optAkunMask = array();
		} else {
			$whereMask = "noakun in (".$akunMask.")";
			if($_SESSION['language']=='EN'){
				$optAkunMask = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereMask,'2',true);
			}else{
				$optAkunMask = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereMask,'2',true);
			}
		}
		
		foreach($dataShow as $key=>$row) {
			$dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
			$dataShow[$key]['kode'] = $optKel[$row['kode']];
			$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['noaruskas'] = isset($optCashFlow[$row['noaruskas']])? $optCashFlow[$row['noaruskas']]: '';
			$dataShow[$key]['kodekegiatan'] = $optKegiatan[$row['kodekegiatan']];
			$dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
			$dataShow[$key]['kodecustomer'] = $optCustomer[$row['kodecustomer']];
			$dataShow[$key]['kodesupplier'] = $optSupplier[$row['kodesupplier']];
			$dataShow[$key]['kodevhc'] = $optVhc[$row['kodevhc']];
			$dataShow[$key]['matauang'] = $optMataUang[$row['matauang']];
			$dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
			$dataShow[$key]['orgalokasi'] = $optOrgAl[$row['orgalokasi']];
			$dataShow[$key]['hutangunit1'] = $optHutangUnit[$row['hutangunit1']];
		}
		
		# Form
		$theForm2 = new uForm('kasbankForm','Form Kas Bank',2);
		$theForm2->addEls('kode',$_SESSION['lang']['kode'],'','select','L',25,$optKel);
		$theForm2->addEls('keterangan1',$_SESSION['lang']['noinvoice'],'','text','L',25);
		$theForm2->_elements[1]->_attr['onclick'] = "searchNopo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']."','<div id=formPencariandata></div>',event)";
		$theForm2->addEls('noakun',$_SESSION['lang']['noakun'],'','selectsearch','L',25,$optAkun);
		$theForm2->_elements[2]->_attr['onchange'] = 'updFieldAktif()';
		$theForm2->addEls('noaruskas',$_SESSION['lang']['noaruskas'],'','select','L',25,$optCashFlow);
		$theForm2->addEls('matauang',$_SESSION['lang']['matauang'],$defMU,'select','L',25,$optMataUang);
		$theForm2->_elements[4]->_attr['onchange'] = "getKurs2()";
		#$theForm2->_elements[4]->_attr['disabled'] = 'disabled';  #permintaan pak rahmad per tanggal 03 june 2015 by email menambahkan dokumentasi, jamhari
		$theForm2->addEls('kurs',$_SESSION['lang']['kurs'],$defKurs,'textnum','L',10);//ind
        $theForm2->_elements[5]->_attr['readonly'] = true;
        $theForm2->addEls('keterangan2',$_SESSION['lang']['keterangan2'],'','text','L',40);
		$theForm2->addEls('jumlah',$_SESSION['lang']['jumlah'],'0','textnumw-','R',10);
		$theForm2->_elements[7]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
		$theForm2->addEls('kodesegment',$_SESSION['lang']['segment'],'','searchSegment','L',35);
		$theForm2->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','selectsearch','L',35,$optKegiatan);
		if(empty($fieldAktif[0])) {
			$theForm2->_elements[9]->_attr['disabled'] = 'disabled';
		}
			
		$theForm2->addEls('kodeasset',$_SESSION['lang']['aktivadalam'],'','select','L',35,$optAsset);
		if(empty($fieldAktif[1])) {
			$theForm2->_elements[10]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarang','L',10);
		if(empty($fieldAktif[2])) {
			$theForm2->_elements[11]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('nik',$_SESSION['lang']['nik'],'','select','L',35,$optKary);
		if(empty($fieldAktif[3])) {
			$theForm2->_elements[12]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('kodecustomer',$_SESSION['lang']['kodecustomer'],'','select','L',35,$optCustomer);
		if(empty($fieldAktif[4])) {
			$theForm2->_elements[13]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('kodesupplier',$_SESSION['lang']['kodesupplier'],'','select','L',35,$optSupplier);
		if(empty($fieldAktif[5])) {
			$theForm2->_elements[14]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('kodevhc',$_SESSION['lang']['kodevhc'],'','select','L',35,$optVhc);
		if(empty($fieldAktif[6])) {
			$theForm2->_elements[15]->_attr['disabled'] = 'disabled';
		}
		$theForm2->addEls('orgalokasi',$_SESSION['lang']['kodeblok'],'','select','L',35,$optOrgAl);
		$theForm2->addEls('nodok',$_SESSION['lang']['nodok'],'','textsearch','L',35);
		$theForm2->_elements[17]->_attr['onclick'] = 'searchDok(event)';
		$theForm2->addEls('hutangunit1',$_SESSION['lang']['hutangunit'],'','select','L',25,$optHutangUnit);
		
		# Table
		$theTable2 = new uTable('kasbankTable','Tabel Kas Bank',$cols,$data,$dataShow);
		
		# FormTable
		$formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,
			array('notransaksi','kodeorg','noakun2a','tipetransaksi'));
		$formTab2->_target = "keu_slave_kasbank_detail";
		$formTab2->_noClearField = '##keterangan1'; // dz: buat nambahin exception yang ga di-clear
		//$formTab2->_defValue = '##matauang=IDR##kurs=1##kodesegment=##kodebarang=##keterangan=';
        $formTab2->_defValue = '##matauang='.$defMU.'##kurs='.$defKurs.'##kodesegment=##kodebarang=##keterangan=##keterangan2=';
        $formTab2->_numberFormat = '##jumlah';
		$formTab2->_noEnable = '##kodesegment##kodebarang##matauang##kurs';
		$formTab2->_afterEditMode = "updFieldAktif";
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Tools</b></legend>";
		if($param['tipetransaksi']=='M') {
			echo makeElement('btnInvoice1','btn','Add from Invoice AR',array('onclick'=>"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']." AR','<div id=formPencariandata></div>',event)"));
			echo makeElement('btnMemo','btn','Add from Memorial',array('onclick'=>"searchMemo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
				echo makeElement('btnPerdin','btn','Add from Perdin',array('onclick'=>"searchPerdin('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
			}
		} else {
			echo makeElement('btnInvoice','btn','Add from Invoice AP',array('onclick'=>"searchNopo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']." AP','<div id=formPencariandata></div>',event)")).'&nbsp;';
			echo makeElement('btnMemo','btn','Add from Memorial',array('onclick'=>"searchMemo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
			//if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				echo makeElement('btnPerdin','btn','Add from Perdin',array('onclick'=>"searchPerdin('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
			//}
		}
        echo "</fieldset>";
		echo "<fieldset><legend><b>Detail</b></legend>";
		$formTab2->render();
		echo "</fieldset>";
		break;
	
    case 'add':
		cekVendorKasKecil(); // Cek Vendor Kas Kecil
		
		$cols = array(
			'kode','keterangan1','noakun','noaruskas','matauang','kurs','keterangan2',
			'jumlah','kodesegment','kodekegiatan','kodeasset','kodebarang','nik','kodecustomer',
			'kodesupplier','kodevhc','orgalokasi','nodok','hutangunit1','notransaksi','kodeorg','noakun2a','tipetransaksi'
		);
		$data = $param;
		unset($data['numRow']);
			
		//=====tambahan ginting
		#periksa apakah akun tanaman, dan jika akun tanaman maka harus ada kodeblok
			if($data['kurs']==0 || $data['kurs'] == ''){
				exit("[ Error ]: The value of the kurs rate there should be.");
			}
			
			$blk=str_replace(" ","",$data['orgalokasi']);
			$nik=str_replace(" ","",$data['nik']);        
			$sup=str_replace(" ","",$data['kodesupplier']);
			$vhc=str_replace(" ","",$data['kodevhc']);             
			if(cekAkun($data['noakun']) and $blk=='')
			{
				exit("[ Error ]: Plant Account must comply with Block Code.");
			}else if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
				exit("[ Error ]: Activity is obligatory.");
			}else  if(cekAkunPiutang($data['noakun']) and $nik=='')
			{
				exit("[ Error ]: Employee ID is Obligatory to this Account.");
			}else if(cekAkunHutang($data['noakun']) and $sup=='')
			{
				exit("[ Error ]: Supplier Code is obligatory to this Account.");
			}else if(cekAkunTrans($data['noakun']) and $vhc=='')
			{
				exit("[ Error ]: Vehicle Code is obligatory to this accout.");
			}
			//=====end tambahan ginting
			//
			//              
			# Additional Default Data
		$data['jumlah'] = str_replace(',','',$data['jumlah']);
		
		$query = insertQuery($dbname,'keu_kasbankdt',$data,$cols);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		
		unset($data['notransaksi']);unset($data['kodeorg']);
		unset($data['noakun2a']);unset($data['tipetransaksi']);
		
		$res = "";
		foreach($data as $cont) {
			$res .= "##".$cont;
		}
		
		$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
		echo $result;
		break;
	
    case 'edit':
		cekVendorKasKecil(); // Cek Vendor Kas Kecil
		$data = $param;
		if($data['kurs']==0 || $data['kurs'] == ''){
			exit("[ Error ]: The value of the kurs rate there should be.");
		}
		unset($data['notransaksi']);
		foreach($data as $key=>$cont) {
			if(substr($key,0,5)=='cond_') {
			unset($data[$key]);
			}
		}
		$data['jumlah'] = str_replace(',','',$data['jumlah']);
		
		$where = "notransaksi='".$param['notransaksi'].
			"' and noakun='".$param['cond_noakun'].
			"' and tipetransaksi='".$param['tipetransaksi'].
			"' and noakun2a='".$param['noakun2a'].
			"' and keterangan1='".$param['cond_keterangan1'].
			"' and keterangan2='".$param['cond_keterangan2'].
				"' and kodeorg='".$param['kodeorg']."'";
		$query = updateQuery($dbname,'keu_kasbankdt',$data,$where);
			if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		echo json_encode($param);
		break;
    case 'delete':
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeorg='".$param['kodeorg'].
			"' and noakun='".$param['noakun'].
			"' and noakun2a='".$param['noakun2a'].
			"' and tipetransaksi='".$param['tipetransaksi'].
			"' and keterangan1='".$param['keterangan1']."'
				 and keterangan2='".$param['keterangan2']."'";
		$query = "delete from `".$dbname."`.`keu_kasbankdt` where ".$where;
			if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		break;
    case 'updField':
		$optField = makeOption($dbname,'keu_5akun','noakun,fieldaktif',
			"noakun='".$param['noakun']."'");
		echo $optField[$param['noakun']];
		break;
	
    case'getForminvoice':
        $optSupplierCr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sSuplier="select distinct supplierid,namasupplier,substr(kodekelompok,1,1) as status from ".$dbname.".log_5supplier order by namasupplier asc";
        $qSupplier=mysql_query($sSuplier) or die(mysql_error($sSupplier));
        while($rSupplier=mysql_fetch_assoc($qSupplier))
        {
            $optSupplierCr.="<option value='".$rSupplier['supplierid']."'>".$rSupplier['namasupplier']." [".$rSupplier['status']."]</option>";
        }
        $form = "<fieldset style=float: left;><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['noinvoice']."</td>";
        $form.= "<td><input type=text class=myinputtext id=no_brg value=".date('Y')."></td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['namasupplier']."</td>";
        $form.= "<td><select id=supplierIdcr style=width:150px>".$optSupplierCr."</select></td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['nopo']."</td>";
        $form.= "<td>".makeElement('sNopo','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "<tr><td>No. Invoice Supplier</td>";
        $form.= "<td>".makeElement('sInvSupp','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $form.= "<td>".makeElement('sNilai','textnum','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td>";
        $form.= "<td>".makeElement('sYm','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findNoinvoice(0)>Find</button></fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;
	
	case'getFormInvoiceAR':
        $optSupplierCr="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sSuplier="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer  order by namacustomer asc";
        $qSupplier=mysql_query($sSuplier) or die(mysql_error($sSupplier));
        while($rSupplier=mysql_fetch_assoc($qSupplier))
        {
            $optSupplierCr.="<option value='".$rSupplier['kodecustomer']."'>".$rSupplier['namacustomer']."</option>";
        }
        $form = "<fieldset style=float: left;><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['noinvoice']." AR</td>";
        $form.= "<td><input type=text class=myinputtext id=no_brg value=".date('Y')."></td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['namacust']."</td>";
        $form.= "<td><select id=supplierIdcr style=width:150px>".$optSupplierCr."</select></td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
        $form.= "<td>".makeElement('sNopo','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $form.= "<td>".makeElement('sNilai','textnum','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "<tr><td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td>";
        $form.= "<td>".makeElement('sYm','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";
        
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findNoinvoice(1)>Find</button></fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;
	
	case'getFormMemo':

		$defPeriod = $_SESSION['org']['period']['tahun'].'-'.
			str_pad($_SESSION['org']['period']['bulan'],2,'0',STR_PAD_LEFT);
		
        $form = "<fieldset style=float: left;><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['nojurnal']."</td>";
        $form.= "<td><input type=text class=myinputtext id=sNojurnal value=''></td></tr>";
		
		$form.= "<tr><td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td>";
        $form.= "<td>".makeElement('sYm','textnumw-',$defPeriod,array('placeholder'=>'YYYY-mm'))."</td></tr>";
		
        $form.= "</table>";
		$form.= "<button class=mybutton onclick=findMemo()>Find</button>";
		$form.= "</fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;
	
	case'getFormPerdin':

		$defPeriod = $_SESSION['org']['period']['tahun'].'-'.
			str_pad($_SESSION['org']['period']['bulan'],2,'0',STR_PAD_LEFT);
		
        $form = "<fieldset style=float: left;><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
        $form.= "<td><input type=text class=myinputtext id=sNotransaksi value=''></td></tr>";
		
		$form.= "<tr><td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td>";
        $form.= "<td>".makeElement('sYm','textnumw-',$defPeriod,array('placeholder'=>'YYYY-mm'))."</td></tr>";
		
        $form.= "</table>";
		$form.= "<button class=mybutton onclick=findPerdin()>Find</button>";
		$form.= "</fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;
	
    case'getInvoice':
        $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
        $arrTipe=array(
			"p"=>$_SESSION['lang']['pesananpembelian'],
			"k"=>$_SESSION['lang']['kontrak'],
			"s"=>$_SESSION['lang']['suratjalan'],
			"n"=>$_SESSION['lang']['konosemen'],
			"b"=>'Biaya Kirim'
		);
        $dat="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $dat.="<div style=overflow:auto;width:800px;height:380px;>";
        $dat.= makeElement('btnAdd2Detail','btn','Tambahkan ke Detail',array('onclick'=>'add2detail()')).'<br><br>';
        $dat.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
        $dat.="<tr class='rowheader'><td>";
        $dat.= makeElement('btnAllInvoice','btn',$_SESSION['lang']['all'],array('onclick'=>'checkAll()'));
        $dat.="</td>";
        $dat.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $dat.="<td>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td>".$_SESSION['lang']['tipeinvoice']."</td>";
        $dat.="<td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        
        
        
        $dat.="<td>".$_SESSION['lang']['nilaippn']."</td>";
        $dat.="<td>Sudah dibayar</td>";
        $dat.="<td>".$_SESSION['lang']['sisa']."</td>";
        $dat.="<td>".$_SESSION['lang']['noakun']."</td>";
        $dat.="</tr></thead><tbody id='invTbody'>";
        
        $str="select distinct noinvoice from ".$dbname.".aging_sch_vw where (((dibayar<nilaipo)or(dibayar<nilaikontrak)or(dibayar<nilaiinvoice))or(dibayar is null or dibayar=0)) and noinvoice like '".$param['txtfind']."%'";
       // print_r($str);exit;
		$qstr=mysql_query($str) or die(mysql_error($conn));
        while($rstr=mysql_fetch_assoc($qstr))
        {
            $belumlunas[$rstr['noinvoice']]=$rstr['noinvoice'];
        }
        
		if(empty($param['idSupplier'])) {
			$kdsup=" ";
		} else {
			$kdsup=" and kodesupplier='".$param['idSupplier']."'  ";
		}
		
        $sPo="select distinct a.kodesupplier,noinvoice,a.nopo,tipeinvoice,nilaiinvoice,nilaippn,d.noakun,a.keterangan,a.posting,b.namakaryawan
            from ".$dbname.".keu_tagihanht a
			left join ".$dbname.".datakaryawan b on a.postingby=b.karyawanid ".
			//left join ".$dbname.".log_poht c on a.nopo=c.nopo
			"left join ".$dbname.".log_5klsupplier d on left(a.kodesupplier,4)=d.kode
            where noinvoice like '".$param['txtfind']."%' and kodeorg='".$_SESSION['org']['kodeorganisasi']."' ".$kdsup;
        if(!empty($param['sNopo'])) {
            $sPo.= " and a.nopo like '%".$param['sNopo']."%'";
        }
        if(!empty($param['sInvSupp'])) {
            $sPo.= " and a.noinvoicesupplier like '%".$param['sInvSupp']."%'";
        }
        if(!empty($param['sNilai'])) {
            $sPo.= " and a.nilaiinvoice=".$param['sNilai'];
        }
        if(!empty($param['sYm'])) {
            $sPo.= " and a.tanggal like '%".$param['sYm']."%'";
        }
        $sPo.=" order by a.tanggal asc";
		//print_r($sPo);
        $qPo=mysql_query($sPo) or die(mysql_error($conn));
        $key=$no=0;
        while($rPo=mysql_fetch_assoc($qPo)) {
            if(isset($belumlunas[$rPo['noinvoice']]) and $rPo['noinvoice']==$belumlunas[$rPo['noinvoice']]){
                $sJmlh="select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where keterangan1='".$rPo['noinvoice']."'";
               
                $qJmlh=mysql_query($sJmlh) or die(msyql_error($conn));
                $rJmlh=mysql_fetch_assoc($qJmlh);
                //$sCek="select distinct sum(nilaiinvoice+nilaippn) as jmlhinvoice from ".$dbname.".keu_tagihanht where  noinvoice='".$rPo['noinvoice']."'";
                $sCek="select distinct sum(nilaiinvoice) as jmlhinvoice from ".$dbname.".keu_tagihanht where  noinvoice='".$rPo['noinvoice']."'";
                // echo $sCek;
				$qCek=mysql_query($sCek) or die(mysql_error($conn));
                $rCek=mysql_fetch_assoc($qCek);
                
                $iDt="select sum(nilai) as nilai from ".$dbname.".keu_tagihandt where noakun in "
                        . "(select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='TX' and kodeparameter='PPNINV')"
                        . " and noinvoice='".$rPo['noinvoice']."'  ";
                $nDt=  mysql_query($iDt) or die (mysql_error($conn));
                $dDt=  mysql_fetch_assoc($nDt);
                    
               
                
                $no+=1;
                if(empty($rPo['kodesupplier'])) {
					$dat.="<tr class='rowcontent' title='PO still not valid, No Supplier'><td></td>"; 
					$dat.="<td style='background-color:red;'>".$rPo['noinvoice']."</td>";
				} elseif($rPo['posting']==0) {
                    //$dat.="<tr class='rowcontent' title='Document not complete:".$rPo['namakaryawan']."'><td></td>";
					$dat.="<tr class='rowcontent' title='Invoice not posted yet'><td></td>";
                    $dat.="<td style='background-color:red;'>".$rPo['noinvoice']."</td>"; 
                } elseif($rJmlh['jmlhKas']>=$rCek['jmlhinvoice']) {
				   
					//$dat.="<tr class='rowcontent' title='Already exist'><td></td>"; 
					//$dat.="<td style='background-color:red;'>".$rPo['noinvoice']."</td>";
				} else {
                                   
                                        $sisa=$rPo['nilaiinvoice']-$rJmlh['jmlhKas'];
					//$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['noinvoice']."','".$rPo['nilaiinvoice']."','".$rPo['noakun']."','".$rPo['keterangan']."','".$rPo['kodesupplier']."','".$rPo['nopo']."')\" style='pointer:cursor;'>";
					$dat.="<tr class='rowcontent'>";
					$dat.="<td>".makeElement('inv_'.$key,'checkbox','',array('class'=>'inv-chk','invNo'=>$rPo['noinvoice'],'sisa'=>$sisa))."</td>";
					$dat.="<td>".$rPo['noinvoice']."</td>";
					$key++;
                }
                
				if($rJmlh['jmlhKas']<$rCek['jmlhinvoice']) {
					$dat.="<td>".$rPo['nopo']."</td>";
					$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td>";
					$dat.="<td>".$arrTipe[$rPo['tipeinvoice']]."</td>";
					$dat.="<td align=right>".number_format($rPo['nilaiinvoice'],2)."</td>";
                                        
                                        $sisa=$rPo['nilaiinvoice']-$rJmlh['jmlhKas'];
                                        
					$dat.="<td>".$dDt['nilai']."</td>";
                                        $dat.="<td>".$rJmlh['jmlhKas']."</td>";
                                        $dat.="<td align=right>".number_format($sisa,2)."</td>";
					$dat.="<td>".$rPo['noakun']."</td></tr>";
				}
            }
        }// while
		setIt($rJmlh['jmlhKas'],0);
		setIt($rCek['jmlhinvoice'],0);
        $dat.="</tbody></table></div>#Status S atau K, refer To S=Supplier,K=Contractor</fieldset>";
        // echo $dat."__".$rJmlh['jmlhKas']."_____".$rCek['jmlhinvoice'];
		echo $dat;
        break;
	
	case'getInvoiceAR':
        $optNmsupp = makeOption($dbname, 'pmn_4customer','kodecustomer,namacustomer');
        
		$dat="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $dat.="<div style=overflow:auto;width:800px;height:432px;>";
        $dat.= makeElement('btnAdd2Detail','btn','Tambahkan ke Detail',array('onclick'=>'add2detailAR()')).'<br><br>';
        $dat.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
        $dat.="<tr class='rowheader'><td>";
        $dat.= makeElement('btnAllInvoice','btn',$_SESSION['lang']['all'],array('onclick'=>'checkAll()'));
        $dat.="</td>";
        $dat.="<td>".$_SESSION['lang']['noinvoice']." AR</td>";
        $dat.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
        $dat.="<td>".$_SESSION['lang']['namacust']."</td>";
		$dat.="<td>".$_SESSION['lang']['komoditi']."</td>";
        $dat.="<td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $dat.="<td>".$_SESSION['lang']['nilaippn']."</td>";
        $dat.="<td>Sudah dibayar</td>";
        $dat.="<td>".$_SESSION['lang']['sisa']."</td>";
        $dat.="</tr></thead><tbody id='invTbody'>";
        
        if(empty($param['idSupplier'])) {
			$kdsup=" ";
		} else {
			$kdsup=" and kodecustomer='".$param['idSupplier']."'  ";
		}
		
        $sPo="select distinct kodecustomer,noinvoice,nilaiinvoice,nilaippn,nokontrak,b.namabarang
            from ".$dbname.".keu_penagihanht a
			left join ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang
            where noinvoice like '%".$param['txtfind']."%' and posting=1 and a.matauang='".$param['matauang']."' and 
			kodept	='".$_SESSION['org']['kodeorganisasi']."' ".$kdsup;
        if(!empty($param['sNilai'])) {
            $sPo.= " and a.nilaiinvoice=".$param['sNilai'];
        }
        if(!empty($param['sYm'])) {
            $sPo.= " and a.tanggal like '%".$param['sYm']."%'";
        }
        $sPo.=" order by a.tanggal asc";
        $qPo=mysql_query($sPo) or die(mysql_error($conn));
        $key=$no=0;
        while($rPo=mysql_fetch_assoc($qPo)) {
			// Cek Track Pelunasan
			$sBayar = "select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where keterangan1='".$rPo['noinvoice']."'";
			$resBayar = fetchData($sBayar);
			$sisa = $rPo['nilaiinvoice'] - $resBayar[0]['jmlhKas'];
			
			if($sisa > 0){
                $no+=1;
				$dat.="<tr class='rowcontent'>";
				$dat.="<td>".makeElement('inv_'.$key,'checkbox','',array('class'=>'inv-chk','invNo'=>$rPo['noinvoice'],'sisa'=>$sisa))."</td>";
				$dat.="<td>".$rPo['noinvoice']."</td>";
				$dat.="<td>".$rPo['nokontrak']."</td>";
				$dat.="<td>".$optNmsupp[$rPo['kodecustomer']]."</td>";
				$dat.="<td>".$rPo['namabarang']."</td>";
				$dat.="<td align=right>".number_format($rPo['nilaiinvoice'],2)."</td>";
				$dat.="<td align=right>".number_format($rPo['nilaippn'],2)."</td>";
				$dat.="<td align=right>".number_format($resBayar[0]['jmlhKas'],2)."</td>";
				$dat.="<td align=right>".number_format($sisa,2)."</td>";
				$key++;
            }
        }// while
		setIt($rJmlh['jmlhKas'],0);
		setIt($rCek['jmlhinvoice'],0);
        $dat.="</tbody></table></div></fieldset>";
        // echo $dat."__".$rJmlh['jmlhKas']."_____".$rCek['jmlhinvoice'];
		echo $dat;
        break;
	
	case 'getMemo':
		$tgl = explode('-',$param['periode']);
		if(empty($param['periode'])) {
			exit("Warning: Periode harus diisi dengan format (YYYY-mm)");
		}
		if($param['hutangunit']==0){
			#jika hutang unit tidak di check maka masuk di query ini
			#jamhari 04-april-2015
			$qData = selectQuery($dbname,'keu_jurnalht','*',
							 "nojurnal like '%".$param['nojurnal']."%' and
							 tanggal like '%".$param['periode']."%' and
							 kodejurnal = 'M' and posting=0");
			$resData = fetchData($qData);	
		}else{
			if(!empty($param['nojurnal'])){
				$inQuery="select distinct nojurnal from ".$dbname.".keu_jurnaldt where noakun='".$param['noakunhutang']."' and kodeorg='".$param['pemilikhutang']."' and tanggal like  '%".$param['periode']."%' and nojurnal like '%".$param['nojurnal']."%'";
			}else{
				$inQuery="select distinct nojurnal from ".$dbname.".keu_jurnaldt where noakun='".$param['noakunhutang']."' and kodeorg='".$param['pemilikhutang']."' and tanggal like  '%".$param['periode']."%' and nojurnal like '%/M/%'";
			}
			$sData="select * from ".$dbname.".keu_jurnalht where nojurnal in (".$inQuery.") ";
			$qData=mysql_query($sData) or die(mysql_error($conn));
			while($rData=mysql_fetch_assoc($qData)){
				$resData[]=$rData;
			}

		}
		$dat = "<fieldset>";
		$dat .= "<legend>".$_SESSION['lang']['hasil']."</legend>";
		$dat .= "<div style='height:487px;overflow:auto'>";
		$dat .= "<table class=sortable cellpadding=2><thead><tr class=rowheader>";
		$dat .= "<td>".$_SESSION['lang']['nojurnal']."</td>";
		$dat .= "<td>".$_SESSION['lang']['tanggal']."</td>";
		$dat .= "<td>".$_SESSION['lang']['jumlah']."</td>";
		$dat .= "</tr></thead><tbody>";
		if(!empty($resData)){
			foreach($resData as $row) {
			if($row['totaldebet']==0){
				$addSum="sum(debet) as totaldebet";
				if($param['hutangunit']==1){
					$addDet=" and noakun='".$param['noakunhutang']."'";
					$addSum="sum(jumlah) as totaldebet";
				}
				$sRp="select ".$addSum." from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$row['nojurnal']."' ".$addDet."";
				$qRp=mysql_query($sRp) or die(mysql_error($conn));
				$rRp=mysql_fetch_assoc($qRp);
				if($rRp['totaldebet']<0){
					$rRp['totaldebet']=$rRp['totaldebet']*(-1);
				}
				$row['totaldebet']=$rRp['totaldebet'];
			}
			$dat .= "<tr class=rowcontent style='cursor:pointer'";
			$dat .= "onclick=\"getMemo('".$row['nojurnal']."')\">";
			$dat .= "<td>".$row['nojurnal']."</td>";
			$dat .= "<td>".$row['tanggal']."</td>";
			$dat .= "<td align=right>".number_format($row['totaldebet'],2)."</td>";
			$dat .= "</tr>";
			}	
		}else{
			$dat .= "<tr class=rowcontent>";
			$dat .= "<td colspan=3>".$_SESSION['lang']['dataempty']."</td>";
			$dat .= "</tr>";
		}
		
		$dat .= "</tbody></table></div></fieldset>";
		echo $dat;
		break;
	
	case 'getPerdin':
		$tgl = explode('-',$param['periode']);
		if(empty($param['periode'])) {
			exit("Warning: Periode harus diisi dengan format (YYYY-mm)");
		}
		if($param['hutangunit']==0){
			#jika hutang unit tidak di check maka masuk di query ini
			#jamhari 04-april-2015
			//$qData = selectQuery($dbname,'sdm_pjdinasht','*',
			//				 "notransaksi like '%".$param['notransaksi']."%' and
			//				 tanggalperjalanan like '%".$param['periode']."%' and statushrd='1' and dibayar='0'");
			/*
			$qData = "select a.*,b.namakaryawan from ".$dbname.".sdm_pjdinasht a 
						left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid 
						where a.notransaksi like '%".$param['notransaksi']."%' 
							and a.tanggalperjalanan like '%".$param['periode']."%' 
							and a.kodeorg='".$param['kodeorg']."' 
							and a.statushrd='1' and (a.dibayar='0' or a.statuspertanggungjawaban='1')";
			$resData = fetchData($qData);
			*/
			$qData = "select a.*,b.namakaryawan from ".$dbname.".sdm_pjdinasht a 
						left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid 
						where a.notransaksi like '%".$param['notransaksi']."%' 
							and a.tanggalperjalanan like '%".$param['periode']."%' 
							and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
							and a.statushrd='1' and (a.dibayar='0' or a.statuspertanggungjawaban='1')";
			$resData = fetchData($qData);
		}else{
			$qData = "select a.*,b.namakaryawan from ".$dbname.".sdm_pjdinasht a
						left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid 
						where a.notransaksi like '%".$param['notransaksi']."%' 
							and a.tanggalperjalanan like '%".$param['periode']."%' 
							and a.kodeorg='".$param['pemilikhutang']."' 
							and a.statushrd='1' and (a.dibayar='0' or a.statuspertanggungjawaban='1')";
			$resData = fetchData($qData);	
		}
		$dat = "<fieldset>";
		$dat .= "<legend>".$_SESSION['lang']['hasil']."</legend>";
		$dat .= "<div style='height:487px;overflow:auto'>";
		$dat .= "<table class=sortable cellpadding=2><thead><tr class=rowheader>";
		$dat .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
		$dat .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
		$dat .= "<td>".$_SESSION['lang']['tanggaldinas']."</td>";
		$dat .= "<td>".$_SESSION['lang']['tanggalkembali']."</td>";
		$dat .= "<td>".$_SESSION['lang']['tujuan']."</td>";
		$dat .= "<td>".$_SESSION['lang']['tugas']."</td>";
		$dat .= "<td align=right>".$_SESSION['lang']['jumlah']."</td>";
		$dat .= "</tr></thead><tbody>";
		if(!empty($resData)){
			foreach($resData as $row) {
				if($row['totalperdin']==0){
					if($row['statuspertanggungjawaban']=='1'){
						$sRp="select sum(jumlahhrd) as totalperdin from ".$dbname.".sdm_pjdinasdt where notransaksi='".$row['notransaksi']."' and (jumlahdibayar='' or jumlahdibayar='0')";
					}else{
						$sRp="select sum(uangmuka) as totalperdin from ".$dbname.".sdm_pjdinasht where notransaksi='".$row['notransaksi']."'";
					}
					$qRp=mysql_query($sRp) or die(mysql_error($conn));
					$rRp=mysql_fetch_assoc($qRp);
					if($rRp['totalperdin']<0){
						$rRp['totalperdin']=$rRp['totalperdin']*(-1);
					}
					$row['totalperdin']=$rRp['totalperdin'];
				}
				if($row['totalperdin']<>0){
					$dat .= "<tr class=rowcontent style='cursor:pointer'";
					$dat .= "onclick=\"getPerdin('".$row['notransaksi']."')\">";
					$dat .= "<td>".$row['notransaksi']."</td>";
					$dat .= "<td>".$row['namakaryawan']."</td>";
					$dat .= "<td>".$row['tanggalperjalanan']."</td>";
					$dat .= "<td>".$row['tanggalkembali']."</td>";
					if($row['tujuan2']==''){
						$dat .= "<td>".$row['tujuanlain']."</td>";
						$dat .= "<td>".$row['tugaslain']."</td>";
					}else{
						$dat .= "<td>".$row['tujuan2']."</td>";
						$dat .= "<td>".$row['tugas2']."</td>";
					}
					$dat .= "<td align=right>".number_format($row['totalperdin'],2)."</td>";
					$dat .= "</tr>";
				}
			}	
		}else{
			$dat .= "<tr class=rowcontent>";
			$dat .= "<td colspan=7>".$_SESSION['lang']['dataempty']."</td>";
			$dat .= "</tr>";
		}
		
		$dat .= "</tbody></table></div></fieldset>";
		echo $dat;
		break;
	
	case 'addFromInvoice'://indra
        $param = $_POST;

        $sisa = array();
        foreach($param['invoice'] as $key=>$row) {
            $sisa[$row] = $param['sisa'][$key];
        }
            
        $listInvoice = $_POST['invoice'];
        
        $invStr = '';
        foreach($listInvoice as $inv) {
            if(!empty($invStr)) {
                $invStr .= ',';
            }
            $invStr .= "'".$inv."'";
        }
        
        // Data Header
        if(!empty($invStr)) {
            $qHead = selectQuery($dbname,'keu_tagihanht','*',"noinvoice in (".$invStr.")");
            $resHead = fetchData($qHead);
			$qSupp = selectQuery($dbname,'log_5klsupplier','noakun',"kode = '".substr($resHead[0]['kodesupplier'],0,4)."'");
			$resSupp = fetchData($qSupp);
        } else {
            $resHead = array();
			$resSupp = array();
        }
        
        // Data Detail
        if(!empty($invStr)) {
            $qDet = selectQuery($dbname,'keu_tagihandt','*',"noinvoice in (".$invStr.")");
            $resDet = fetchData($qDet);
        } else {
            $resDet = array();
        }
        
        $data = array();
        $optHead = array();
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
        
        // From Header
		$noInv = "";
        foreach($resHead as $row) {
            $no+=0;
			// Jika Hutang Unit
			if($param['hutangunit']==1) {
				$sCek="select hutangunit  from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		        $qCek=mysql_query($sCek) or die(mysql_error($conn));
		        $rCek=mysql_fetch_assoc($qCek);
		        if($rCek['hutangunit']==0){
		        	exit('warning: Hutang Unit Belum di simpan');
		        }
				$currPT = $_SESSION['org']['kodeorganisasi'];
				// Get PT Pemilik Hutang
				$qPT = selectQuery($dbname,'organisasi','induk',
								   "kodeorganisasi='".$param['pemilikhutang']."'");
				$resPT = fetchData($qPT);
				if(empty($resPT)) exit("Warning: PT dari ".$param['pemilikhutang']." tidak ada\nSilahkan hubungi IT");
				
				// Tipe R/K
				if($resPT[0]['induk']==$currPT) {
					$tipe = 'intra';
				} else {
					$tipe = 'inter';
				}
				
				// Get No Akun Interco/Intraco
				$qCaco = selectQuery($dbname,'keu_5caco',"akunhutang",
									 "kodeorg='".$param['pemilikhutang']."' and
									 jenis='".$tipe."'");
				$resCaco = fetchData($qCaco);
				
				if(empty($resCaco))
					exit("Warning: Akun ".ucfirst($tipe)."co untuk Unit ".$param['pemilikhutang']." belum ada");
				else
					$akunHead = $resCaco[0]['akunhutang'];
			} else {
				$akunHead = $resSupp[0]['noakun'];
			}
			
			// Detail Journal from Header Kas bank
            $optHead[$row['noinvoice']] = $row;
            
            //exit("Error:$a");
           
            
            
            //'jumlah' => $row['nilaiinvoice'],
            
            $data[] = array(
                'notransaksi' => $param['notransaksi'],
                'noakun' => $akunHead,
                'tipetransaksi' => $param['tipetransaksi'],
                'tanggal' => $row['tanggal'],
                'jumlah' => $sisa[$row['noinvoice']],
                'noakun2a' => $param['noakun'],
                'kode' => $param['kode'],
                'keterangan1' => $row['noinvoice'],
                'keterangan2' => $row['noinvoice'].' '.$row['keterangan'].' ('.$row['noinvoicesupplier'].') ('.$row['nopo'].' '.$row['nofp'].')',
                'matauang' => $param['matauang'],
                'kurs' => $param['kurs'],
                'kurs2' => 1,
                'noaruskas' => '0',
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => $row['kodesupplier'],
                'kodevhc' => '',
                'orgalokasi' => '',
                'nodok' => $row['nopo'].' '.$row['nofp'],
                'hutangunit1' => $param['hutangunit'],
				'kodesegment' => $defSegment
            );
			$noInv = $row['noinvoice'];
        }
        
        // From Detail
        foreach($resDet as $row) {
            $tmpHead = $optHead[$row['noinvoice']];
			$qAkun = selectQuery($dbname,'keu_5akun','noakun',"UPPER(namaakun) like '%PPN%'");
			$resAkun = fetchData($qAkun);
			$listakun = array();
			foreach($resAkun as $dRow) {
				$listakun[] = $dRow['noakun'];
			}
			if(!in_array($row['noakun'],$listakun)) {
				$nilai = $row['nilai']*(-1);
			} else {
				$nilai = $row['nilai'];
			}
            $data[] = array(
                'notransaksi' => $param['notransaksi'],
                'noakun' => $row['noakun'],
                'tipetransaksi' => $param['tipetransaksi'],
                'tanggal' => $tmpHead['tanggal'],
                'jumlah' => intval($nilai),
                'noakun2a' => $param['noakun'],
                'kode' => $param['kode'],
                'keterangan1' => $noInv,
                'keterangan2' => '',
                'matauang' => $param['matauang'],
                'kurs' => $param['kurs'],
                'kurs2' => 1,
                'noaruskas' => '0',
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => $tmpHead['kodesupplier'],
                'kodevhc' => '',
                'orgalokasi' => '',
                'nodok' => $noInv,
                'hutangunit1' => $param['hutangunit'],
				'kodesegment' => $defSegment
            );
        }
        
        foreach($data as $row) {
            $query = insertQuery($dbname,'keu_kasbankdt',$row);
            if(!mysql_query($query)) {
                echo 'DB Error: '.mysql_error()."\n".$query;
            }
        }
        break;
	
	case 'addFromInvoiceAR':
		$param = $_POST;
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		$nilaiHeader = str_replace(',','',$param['jumlah']);
		
		// Parameter Piutang
		$qParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
			"kodeaplikasi='PIU' and jurnalid='PIU'");
		$resParam = fetchData($qParam);
		if(empty($resParam)) exit("Warning: Parameter Jurnal ".$kodeApp." belum ada\n".
			"Silahkan hubungi pihak IT");
		
		$data = array();
		foreach($param['invoice'] as $noInv) {
			// Get Header Penagihan
			$qHead = selectQuery($dbname,'keu_penagihanht','*',"noinvoice='".$noInv."'");
			$resHead = fetchData($qHead);
			
			// Potongan
			$resHead = $resHead[0];
			$rdata = $resHead;
			$nilaiKlaimPengurang=$rdata['rupiah1']+$rdata['rupiah2']+$rdata['rupiah3']
						+$rdata['rupiah4']+$rdata['rupiah5']+$rdata['rupiah6']+$rdata['rupiah7']-$rdata['rupiah8'];
			$ppnKlaim=0;
			if($rdata['nilaippn']>0) {
				$ppnKlaim=10/100*$nilaiKlaimPengurang;
			}
			$piutangKurang = $nilaiKlaimPengurang + $ppnKlaim;
			
			// Nilai
			$jumlahUM = $rdata['nilaiinvoice'];
			$jumlahPpn = $rdata['nilaippn'];
			$jumlahPiutang = ($rdata['nilaiinvoice']+$rdata['nilaippn']) - $piutangKurang;
			
			// Piutang Penjualan
			$data[] = array(
				'notransaksi' => $param['notransaksi'],
				'noakun' => $resParam[0]['noakunkredit'],
				'tipetransaksi' => $param['tipetransaksi'],
				'tanggal' => tanggalsystem($param['tanggal']),
				'jumlah' => $jumlahPiutang,
				'noakun2a' => $param['noakun'],
				'kode' => $param['kode'],
				'keterangan1' => $noInv,
				'keterangan2' => $resHead['nokontrak'].' - '.$resHead['kuantitas'].' kg',
				'matauang' => $param['matauang'],
				'kurs' => $param['kurs'],
				'kurs2' => 1,
				'noaruskas' => '0',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => $resHead['kodecustomer'],
				'kodesupplier' => '',
				'kodevhc' => '',
				'orgalokasi' => '',
				'nodok' => $resHead['nokontrak'],
				'hutangunit1' => $param['hutangunit'],
				'kodesegment' => $defSegment
			);
		}
		//print_r($data);exit('error');
		$qIns = insertQuery($dbname,'keu_kasbankdt',$data);
		if(!mysql_query($qIns)) {
			exit('Insert Error: '.mysql_error());
		}
		break;
	
	case 'addFromMemo':
        $param = $_POST;
       
        if($param['hutangunit']==1){
        	#cek apakah hutang unit sudah tersimpan di data header/belum
	        $sCek="select hutangunit from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' and hutangunit='".$param['hutangunit']."' 
	               and pemilikhutang='".$param['pemilikhutang']."'";
	        $qCek=mysql_query($sCek) or die(mysql_error($conn));
	        $rCek=mysql_num_rows($qCek);
	        if($rCek==0){
	        	exit('warning: Hutang unit belum tersimpan pada header!!');
	        }
	         #=============== Get Induk Pemilik Hutang
		     $whereNomilhut = "kodeorganisasi='".$param['pemilikhutang']."'";
		     $query = selectQuery($dbname,'organisasi','induk',$whereNomilhut);
		     $noKon = fetchData($query);
		     $indukpemilikhutang = $noKon[0]['induk'];
		    
		    #=============== Get Induk Pembayar Hutang
		    $whereNoyarhut = "kodeorganisasi='".$param['kodeorg']."'";
		    $query = selectQuery($dbname,'organisasi','induk',$whereNoyarhut);
		    $noKon = fetchData($query);
		    $indukpembayarhutang = $noKon[0]['induk'];
		    
		    if($indukpemilikhutang==$indukpembayarhutang)$jenisinduk='intra'; else $jenisinduk='inter';
	        $whereNocaco = "jenis='".$jenisinduk."' and kodeorg='".$param['pemilikhutang']."'";
			$query = selectQuery($dbname,'keu_5caco','akunpiutang',$whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];
        }	
		// Get Data
		if($param['tipetransaksi']=='M') {
			$whereJ = " and jumlah >= 0";
		} else {
			$whereJ = " and jumlah < 0";
		}
		$qData = selectQuery($dbname,'keu_jurnaldt',"*",
							 "nojurnal='".$param['nojurnal']."'".$whereJ);
		$resData = fetchData($qData);
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
        
		// Rearrange Data
		$data = array();
		$num=0;
        foreach($resData as $row) {
        	if($param['hutangunit']==1){
        		if($row['noakun']==$param['noakunhutang']){
        			$row['noakun']=$noakuncaco;
        		}else{
        			continue;
        		}
        	}
			$row['jumlah'] = $row['jumlah'] * (-1);
			$num++;
            $data[] = array(
                'notransaksi' => $param['notransaksi'],
                'noakun' => $row['noakun'],
                'tipetransaksi' => $param['tipetransaksi'],
                'tanggal' => tanggalsystem($param['tanggal']),
                'jumlah' => $row['jumlah'],
                'noakun2a' => $param['noakun'],
                'kode' => $param['kode'],
                'keterangan1' => $row['nodok'],
                'keterangan2' => $row['keterangan'],
                'matauang' => $param['matauang'],
                'kurs' => $param['kurs'],
                'kurs2' => 1,
                'noaruskas' => $row['noaruskas'],
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => $row['kodekegiatan'],
                'kodeasset' => $row['kodeasset'],
                'kodebarang' => $row['kodebarang'],
                'nik' => $row['nik'],
                'kodecustomer' => $row['kodecustomer'],
                'kodesupplier' => $row['kodesupplier'],
                'kodevhc' => $row['kodevhc'],
                'orgalokasi' => '',
                'nodok' => $param['nojurnal'],
                'hutangunit1' => $param['hutangunit'],
				'kodesegment' => $defSegment
            );
        }
        
        foreach($data as $row) {
            $query = insertQuery($dbname,'keu_kasbankdt',$row);
            if(!mysql_query($query)) {
                echo 'DB Error: '.mysql_error()."\n".$query;
            }
        }
        break;

	case 'addFromPerdin':
        $param = $_POST;
		$noperdin=$param['noperdin'];
        if($param['hutangunit']==1){
        	#cek apakah hutang unit sudah tersimpan di data header/belum
	        $sCek="select hutangunit from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' and hutangunit='".$param['hutangunit']."' 
	               and pemilikhutang='".$param['pemilikhutang']."'";
	        $qCek=mysql_query($sCek) or die(mysql_error($conn));
	        $rCek=mysql_num_rows($qCek);
	        if($rCek==0){
	        	exit('warning: Hutang unit belum tersimpan pada header!!');
	        }
	        #=============== Get Induk Pemilik Hutang
		    $whereNomilhut = "kodeorganisasi='".$param['pemilikhutang']."'";
		    $query = selectQuery($dbname,'organisasi','induk',$whereNomilhut);
		    $noKon = fetchData($query);
		    $indukpemilikhutang = $noKon[0]['induk'];
		    
		    #=============== Get Induk Pembayar Hutang
		    $whereNoyarhut = "kodeorganisasi='".$param['kodeorg']."'";
		    $query = selectQuery($dbname,'organisasi','induk',$whereNoyarhut);
		    $noKon = fetchData($query);
		    $indukpembayarhutang = $noKon[0]['induk'];
		    
		    if($indukpemilikhutang==$indukpembayarhutang)$jenisinduk='intra'; else $jenisinduk='inter';
	        $whereNocaco = "jenis='".$jenisinduk."' and kodeorg='".$param['pemilikhutang']."'";
			$query = selectQuery($dbname,'keu_5caco','akunpiutang,akunhutang',$whereNocaco);
			//exit('Warning '.$query);
			$noKon = fetchData($query);
			if($param['tipetransaksi']=='K'){
				$noakuncaco = $noKon[0]['akunhutang'];
			}else{
				$noakuncaco = $noKon[0]['akunpiutang'];
			}
        }
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
        
		// Get Data
		$qData = selectQuery($dbname,'sdm_pjdinasht',"*",
							 "notransaksi='".$param['noperdin']."'");
		$resData = fetchData($qData);

		// Rearrange Data
		$data = array();
		$num=0;
        foreach($resData as $bar) {

			//----- Start Ambil Data PJD
			$karyawanid=$bar['karyawanid'];
			$kodeorg=$bar['kodeorg'];
			$persetujuan=$bar['persetujuan'];
			$hrd=$bar['hrd'];
			$tujuan3=$bar['tujuan3'];
			$tujuan2=$bar['tujuan2'];
			$tujuan1=$bar['tujuan1'];
			$tanggalperjalanan=tanggalnormal($bar['tanggalperjalanan']);
			$tanggalkembali=tanggalnormal($bar['tanggalkembali']);
			$tanggalperjalananw=$bar['tanggalperjalanan'];
			$tanggalkembaliw=$bar['tanggalkembali'];
			$uangmuka=$bar['uangmuka'];
			$dibayar=$bar['dibayar'];
			$tglbayar=$bar['tglbayar'];
			$tugas1=$bar['tugas1'];
			$tugas2=$bar['tugas2'];
			$tugas3=$bar['tugas3'];
			$tujuanlain=$bar['tujuanlain'];
			$tugaslain=$bar['tugaslain'];
			$pesawat=$bar['pesawat'];
			$darat=$bar['darat'];
			$laut=$bar['laut'];
			$mess=$bar['mess'];
			$hotel=$bar['hotel'];	
			$statushrd=$bar['statushrd'];
			$xhrd=$bar['statushrd'];
			$xper=$bar['statuspersetujuan'];

			if($param['kodeorg']!=$kodeorg){
				//exit('Warning : Hutang Unit pada header form belum dicentang karena Unit Berbeda...!'.$noakuncaco);
				$kodeorg=$param['kodeorg'];
			}

			//ambil jabatan, karyawan perdin
			$hjabatan='';
			$bagian='';
			$hnama='';
			$hgolongan='';
			$strf="select a.bagian,b.namajabatan,a.namakaryawan,a.kodegolongan,a.karyawanid from ".$dbname.".datakaryawan a left join
				".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
				where karyawanid=".$karyawanid;	

			$resf=mysql_query($strf);
			while($barf=mysql_fetch_object($resf))
			{
				$hjabatan=$barf->namajabatan;
				$bagian=$barf->bagian;
				$hnama=$barf->namakaryawan;
				$hgolongan=$barf->kodegolongan;
				$hnik=$barf->karyawanid;
			}
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

			function getRangeTanggal($tglAwal,$tglAkhir){
				$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
				$jlhHari = $jlh / (3600*24);
				return $jlhHari + 1;
			}
			$jlhHari=getRangeTanggal($tanggalperjalanan,$tanggalkembali);
			$jmlharilokal=$jlhHari-$jmlhrlibur;
 	//exit('Warning:'.$jlhHari." - ".$jmlhrlibur." = ".$jmlharilokal."  ".$strlibur);

  if($tglbayar=='0000-00-00' or $tglbayar=='' or $tglbayar==NULL){
 			if($jlhHari == 1){
				$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
				where a.regional='".$reg."' and a.kodegolongan='".$hgolongan."' and a.jenis in (2,6,7) order by a.jenis";
			}else{
				$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
				where a.regional='".$reg."' and a.kodegolongan='".$hgolongan."' and a.jenis in (2,6,8,9,10,11) order by a.jenis";
			}
			$rUangMuka=mysql_query($sUangMuka);
			$jlhUangMuka=0;
			if($rUangMuka) {
				$nilaipjd=0;
				while($bUangMuka=mysql_fetch_object($rUangMuka)) {
        			if($param['hutangunit']==1){
       					$noakun=$noakuncaco;
       				}else{
						if(strstr(strtoupper(substr($_SESSION['empl']['lokasitugas'],0,4)),'HO')){
							$noakun='8221001';
						}else{
							//$noakun='7121000';
							$noakun='1180300';
						}
        			}
					if(($pesawat+$darat+$laut)==0 and $bUangMuka->jenis=='2'){
						continue;
					}
					if(($hotel==0 or substr($kodeorg,2,2)=='HO') and $bUangMuka->jenis=='8'){
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
					//if($xhrd==0 or $xper==0){
					if($jmlkali!=0){
        				if($param['tipetransaksi']=='M'){
							$nilaipjd=$nilaipjd * (-1);
						}else{
							$nilaipjd=$nilaipjd * (1);
						}
						$keterangan2='SPPD '.$param['noperdin'].' - '.$bUangMuka->namajenis.' '.$jmlkali.' '.$_SESSION['lang']['hari'].' x '.
									substr("       ".number_format(($bUangMuka->sekali+$bUangMuka->perhari+$bUangMuka->hariketiga),2,',','.'),-12);
						if($bUangMuka->jenis=='1' or $bUangMuka->jenis=='2' or $bUangMuka->jenis=='3' or $bUangMuka->jenis=='4' or $bUangMuka->jenis=='5' or 	$bUangMuka->jenis=='10'){
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
							}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or 	strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
								$aruskas='310223';
							}else{
								$aruskas='310213';
							}
						}
						$num++;
						$data[] = array(
						'notransaksi' => $param['notransaksi'],
						'noakun' => $noakun,
						'tipetransaksi' => $param['tipetransaksi'],
						'tanggal' => tanggalsystem($param['tanggal']),
						'jumlah' => $nilaipjd,
						'noakun2a' => $param['noakun'],
						'kode' => $param['kode'],
						'keterangan1' => $param['noperdin'],
						'keterangan2' => $keterangan2,
						'matauang' => $param['matauang'],
						'kurs' => $param['kurs'],
						'kurs2' => 1,
						'noaruskas' => $aruskas,
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $hnik,
						'kodecustomer' => '',
						'kodesupplier' => '',
						'kodevhc' => '',
						'orgalokasi' => '',
						'nodok' => '',
						'hutangunit1' => $param['hutangunit'],
						'kodesegment' => $defSegment
						);
					}
					//}
				}
			}
}else{
					$sreimburs="select a.*,b.keterangan as namajenis from ".$dbname.".sdm_pjdinasdt a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenisbiaya where notransaksi='".$param['noperdin']."'";
					$rreimburs=mysql_query($sreimburs);
					while($bardt=mysql_fetch_object($rreimburs)){ 
        				if($param['hutangunit']==1){
       						$noakun=$noakuncaco;
       					}else{
							if(strstr(strtoupper(substr($_SESSION['empl']['lokasitugas'],0,4)),'HO')){
								$noakun='8221001';
							}else{
								$noakun='7121000';
							}
        				}
        				if($param['tipetransaksi']=='M'){
							$nilaipjd=$bardt->jumlahhrd * (-1);
						}else{
							$nilaipjd=$bardt->jumlahhrd * (1);
						}
						$titik='';
						for ($i= 1; $i <= $num; $i++){
							$titik.='.';
						}
						$keterangan2='SPPD PJ '.$param['noperdin'].' - '.$bardt->namajenis.' '.$bardt->keterangan.$titik;
						if($bardt->jenisbiaya=='1' or $bardt->jenisbiaya=='2' or $bardt->jenisbiaya=='3' or $bardt->jenisbiaya=='4' or $bardt->jenisbiaya=='5' or $bardt->jenisbiaya=='10'){
							if(strtoupper($bagian)=='CCFI' or strtoupper($bagian)=='CFT' or strtoupper($bagian)=='COM' or strtoupper($bagian)=='DIR' or strtoupper($bagian)=='IT' or strtoupper($bagian)=='OACC' or strtoupper($bagian)=='OBCC' or strtoupper($bagian)=='OFA' or strtoupper($bagian)=='OMIS' or strtoupper($bagian)=='ROA' or strtoupper($bagian)=='URD'){
								$aruskas='310234';
							}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
								$aruskas='310224';
							}else{
								$aruskas='310214';
							}
						}else if($bardt->jenisbiaya=='8'){
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
							}else if(strtoupper($bagian)=='ADM' or strtoupper($bagian)=='CACM' or strtoupper($bagian)=='CAPR' or strtoupper($bagian)=='CARO' or 	strtoupper($bagian)=='CSLS' or strtoupper($bagian)=='EST' or strtoupper($bagian)=='ESTN' or strtoupper($bagian)=='ESTO' or strtoupper($bagian)=='KCO' or strtoupper($bagian)=='KCS' or strtoupper($bagian)=='MIL' or strtoupper($bagian)=='MILM' or strtoupper($bagian)=='MILP' or strtoupper($bagian)=='PNE' or strtoupper($bagian)=='RND' or strtoupper($bagian)=='RND1' or strtoupper($bagian)=='SPM' or strtoupper($bagian)=='SPM1' or strtoupper($bagian)=='TCW' or strtoupper($bagian)=='URD1'){
								$aruskas='310223';
							}else{
								$aruskas='310213';
							}
						}
						$num++;
						$data[] = array(
						'notransaksi' => $param['notransaksi'],
						'noakun' => $noakun,
						'tipetransaksi' => $param['tipetransaksi'],
						'tanggal' => tanggalsystem($param['tanggal']),
						'jumlah' => $nilaipjd,
						'noakun2a' => $param['noakun'],
						'kode' => $param['kode'],
						'keterangan1' => $param['noperdin'],
						'keterangan2' => $keterangan2,
						'matauang' => $param['matauang'],
						'kurs' => $param['kurs'],
						'kurs2' => 1,
						'noaruskas' => $aruskas,
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $hnik,
						'kodecustomer' => '',
						'kodesupplier' => '',
						'kodevhc' => '',
						'orgalokasi' => '',
						'nodok' => '',
						'hutangunit1' => $param['hutangunit'],
						'kodesegment' => $defSegment
						);
				}
}
			//----- End Ambil Data PJD
        }
        foreach($data as $row) {
            $query = insertQuery($dbname,'keu_kasbankdt',$row);
            if(!mysql_query($query)) {
                echo 'DB Error: '.mysql_error()."\n".$query;
            }
        }
        break;
	
    default:
	break;
}

function cekVendorKasKecil() {
	global $dbname;
	global $param;
	
	// Get Parameter Aplikasi
	$qParam = selectQuery($dbname,'setup_parameterappl',"nilai",
						  "kodeaplikasi='KB' and kodeparameter='VENCASH'");
	$resParam = fetchData($qParam);
	$jml = str_replace(',','',$param['jumlah']);
	if(!empty($resParam)) {
		if($param['kodesupplier']==$resParam[0]['nilai'] and $jml>20000000) {
			exit("Warning: Vendor Kas Kecil tidak boleh lebih dari 20,000,000");
		}
	}
}