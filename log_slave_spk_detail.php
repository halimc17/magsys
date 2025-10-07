<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'showDetail':
        # Options
        if($_SESSION['empl']['tipelokasitugas']=='KEBUN' or substr($param['kebun'],3,1)=='E')
        {
            $scek="select distinct tipe from ".$dbname.".organisasi where induk='".$param['divisi']."'";
			
            $qcek=mysql_query($scek) or die(mysql_error($conn));
            $rcek=mysql_fetch_assoc($qcek);
            $tpdt="BLOK";
            if($rcek['tipe']=='BIBITAN'){
                  $tpdt="BIBITAN";
            }
			/*
            $optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
                "tipe='".$tpdt."' 
                 and kodeorganisasi like '".$param['divisi']."%' 
                 and length(kodeorganisasi)>5 
                 and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where kodeorg like '".$param['divisi']."%' and luasareaproduktif>0)");
			*/
            $optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$param['divisi']."%'");
		}
        else
        {
		    $a = substr($param['divisi'],0,4);
			
            $optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
                #"tipe='BLOK' and kodeorganisasi like '".substr($param['divisi'],0,4)."%' and length(kodeorganisasi)>5");
                    "induk='".$param['divisi']."' or kodeorganisasi like '".substr($param['divisi'],0,4)."%'");
        }
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN' or substr($param['kebun'],3,1)=='E') {
			$optBlokStat = makeOption($dbname,'setup_blok','kodeorg,statusblok',
			"kodeorg='".key($optBlok)."'");
			$whereAct =" status = '1'";
			if(strlen(getFirstKey($optBlokStat))==10) {
				$whereAct .= " and kelompok='".trim(substr(getFirstContent($optBlokStat),0,3))."'";
			}
			$optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',$whereAct,'6');
		} else {
			$whereAct="status = '1'";
			$optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',$whereAct,'6');
		}
        
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama,kode',"kodeorg like '".$param['kebun']."%' and posting=0",4);
			$optAct = makeOption($dbname,'project_dt','kegiatan,namakegiatan,kegiatan',"kodeproject='".key($optBlok)."'",4);
		}
		
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp,rupiahpersatuan";
		$query = selectQuery($dbname,'log_spkdt',$cols,$where);
			$data = fetchData($query);
		$dataShow = $data;
		foreach($dataShow as $key=>$row) {
			$dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
			$dataShow[$key]['kodekegiatan'] = isset($optAct[$row['kodekegiatan']])? $optAct[$row['kodekegiatan']]: '';
		}
		
		$strSatuan = "select * from ".$dbname.".setup_kegiatan where kodekegiatan = '".getFirstKey($optAct)."'";
		$qrySatuan = mysql_query($strSatuan) or die(mysql_error($conn));
		$resSatuan = mysql_fetch_object($qrySatuan);
		
		if(isset($resSatuan->satuan)) {
			$satuan = $resSatuan->satuan;
		} else {
			$optMySatuan = makeOption($dbname,'project_dt','kegiatan,satuan',
									 "kegiatan = '".getFirstKey($optAct)."'");
			$satuan = isset($optMySatuan[getFirstKey($optAct)])? $optMySatuan[getFirstKey($optAct)]: '';
		}
		// $satuan = isset($resSatuan->satuan)? $resSatuan->satuan: $resSatuan->satuan;
		
		# Form
		$theForm1 = new uForm('detailForm','Form Detail',2);
		$theForm1->addEls('kodeblok',$_SESSION['lang']['subunit'],'','select','L',25,$optBlok);
		$theForm1->_elements[0]->_attr['onchange'] = "updKegiatan()";
		$theForm1->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','select','L',25,$optAct);
		$theForm1->_elements[1]->_attr['onchange'] = "updSatuan()";
		$theForm1->addEls('hk',$_SESSION['lang']['hk'],'1','textnum','R',10);
		$theForm1->addEls('hasilkerjajumlah',$_SESSION['lang']['volumekontrak'],'0','textnum','R',10);
		$theForm1->_elements[3]->_attr['onkeyup'] = "calrppersatuan()";
		$theForm1->addEls('satuan',$_SESSION['lang']['satuan'],$satuan,'text','L',25);
		$theForm1->_elements[4]->_attr['disabled'] = "true";
		$theForm1->addEls('jumlahrp',$_SESSION['lang']['rupiah'].' '.$_SESSION['lang']['total'],'0','textnum','R',10);
		$theForm1->_elements[5]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
		$theForm1->_elements[5]->_attr['onkeyup'] = 'calrppersatuan()';
		$theForm1->addEls('rupiahpersatuan',$_SESSION['lang']['rupiah'].' '.$_SESSION['lang']['per'].' '.$_SESSION['lang']['satuan'],'0','textnum','R',10);
		$theForm1->_elements[6]->_attr['disabled'] = 'true';
		
		# Table
		$theTable1 = new uTable('detailTable','Tabel Detail',$cols,$data,$dataShow);
				
		# FormTable
		$formTab1 = new uFormTable('ftDetail',$theForm1,$theTable1,null,array('notransaksi'));
		$formTab1->_target = "log_slave_spk_detail";
		$formTab1->_numberFormat = '##jumlah';
		$formTab1->_noEnable = '##satuan##rupiahpersatuan';
		$formTab1->_noClearField = '##satuan';
		//$formTab1->_defValue = '##satuan='.$satuan;
		$formTab1->_afterEditMode = "afterEditMode";
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		$formTab1->render();
			echo "</fieldset>";
		break;
    
	/** Add New Detail Data */
	case 'add':
	   
        $cols = array(
			'kodeblok','kodekegiatan','hk',
			'hasilkerjajumlah','satuan','jumlahrp','rupiahpersatuan','notransaksi'
		);
		$data = $param;
			unset($data['numRow']);
		$data['jumlahrp'] = str_replace(',','',$data['jumlahrp']);
		$data['rupiahpersatuan'] = str_replace(',','',$data['rupiahpersatuan']);
		
			$query = insertQuery($dbname,'log_spkdt',$data,$cols);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		
			unset($data['notransaksi']);
			$res = "";
		foreach($data as $cont) {
			$res .= "##".$cont;
		}
			$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
		echo $result;
		break;
    
	/** Edit existing Data Detail */
	case 'edit':
	    
		$data = $param;
			unset($data['notransaksi']);
		$data['jumlahrp'] = str_replace(',','',$data['jumlahrp']);
		$data['rupiahpersatuan'] = str_replace(',','',$data['rupiahpersatuan']);
		foreach($data as $key=>$cont) {
			if(substr($key,0,5)=='cond_') {
			unset($data[$key]);
			}
		}
		
		cekRealisasi($param);
		$where = "notransaksi='".$param['notransaksi']."'and kodekegiatan='".
			$param['cond_kodekegiatan']."' and kodeblok='".$param['cond_kodeblok']."'";
		$query = updateQuery($dbname,'log_spkdt',$data,$where);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		} else {
			$whereBA = "notransaksi='".$param['notransaksi']."'and kodekegiatan='".
				$param['cond_kodekegiatan']."' and blokspkdt='".$param['cond_kodeblok']."'";
			$dataBA = array(
				'kodekegiatan' => $param['kodekegiatan'],
				'blokspkdt' => $param['kodeblok'],
			);
			$queryBA = updateQuery($dbname,'log_baspk',$dataBA,$whereBA);
			if(!mysql_query($queryBA)) {
				echo "DB Error : ".mysql_error();
				exit;
			}
		}
		echo json_encode($param);
		break;
	
	/** Delete existing data detail */
    case 'delete':
                //================periksa realisasi
             $m =0;
            $strx="select sum(jumlahrealisasi) from ".$dbname.".log_baspk 
                  where notransaksi='".$param['notransaksi']."'";
            $resx=mysql_query($strx);
            while($barx=mysql_fetch_array($resx))
            {
              $m= $barx[0]; 
            }   
            //lihat postingan-=============================
            $n ='';
            $strx="select statusjurnal from ".$dbname.".log_baspk 
                  where notransaksi='".$param['notransaksi']."' and statusjurnal=0";
            $resx=mysql_query($strx);           
            if(mysql_num_rows($resx)>0)
                $n ='?';
            
            if($n=='' and $m==0)
            {     
        //=================================
                $where = "notransaksi='".$param['notransaksi']."' and kodekegiatan='".
                    $param['kodekegiatan']."'";
                $query = "delete from `".$dbname."`.`log_spkdt` where ".$where;
                if(!mysql_query($query)) {
                    echo "DB Error : ".mysql_error();
                    exit;
                }
                    }
            else
            {
                exit('Error:Realisasi sudah terisi');
            } 
	break;
    case 'updKegiatan':
		if(substr($param['kodeblok'],2,1)=='-') {
			$optAct = makeOption($dbname,'project_dt','kegiatan,namakegiatan,kegiatan',"kodeproject='".$param['kodeblok']."'",4);
		} else {
			$optBlokStat = makeOption($dbname,'setup_blok','kodeorg,statusblok,kodeorg',"kodeorg='".$param['kodeblok']."'");
			if(strlen(getFirstKey($optBlokStat))==10) {
				$whereAct = "kelompok='".trim(substr(getFirstContent($optBlokStat),0,3))."' and status = '1'";
			} else {
				$whereAct = "status = '1'";
			}
			$optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',$whereAct,'6');
		}
		echo json_encode($optAct);
		break;
	
	case 'updSatuan':
		$strSatuan = "select * from ".$dbname.".setup_kegiatan where kodekegiatan='".$param['kodekegiatan']."'";
		$qrySatuan = mysql_query($strSatuan) or die(mysql_error($conn));
		$resSatuan = mysql_fetch_object($qrySatuan);
		
		if(isset($resSatuan->satuan)) {
			$satuan = $resSatuan->satuan;
		} else {
			$optProject = makeOption($dbname,'project_dt','kegiatan,satuan',
									 "kegiatan = '".$param['kodekegiatan']."'");
			$satuan = isset($optProject[$param['kodekegiatan']])? $optProject[$param['kodekegiatan']]: '';
		}
		echo $satuan;
		break;
    default:
	break;
}

function cekRealisasi($param) {
	global $dbname;
	
	// Get Realisasi
	$where = "notransaksi='".$param['notransaksi']."'and kodekegiatan='".
			$param['cond_kodekegiatan']."' and kodeblok='".$param['cond_kodeblok']."'
			and posting = 1";
	$qCek = selectQuery($dbname,'log_baspk',"*",$where);
	$resCek = fetchData($qCek);
	if(!empty($resCek)) {
		exit("Warning: Sudah ada realisasi yang diposting. Data tidak bisa diubah");
	}
}