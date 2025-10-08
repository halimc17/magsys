<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;

//echo "warning: <pre>".print_r($param)."</pre>";
//exit;

switch($proses) {
    # Daftar Header
    case 'showHeadList':
        $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
        if(isset($param['where'])) {
            $arrWhere = json_decode(str_replace('\\','',$param['where']),true);
            if(!empty($arrWhere)) {
                foreach($arrWhere as $key=>$r1) {
                    if($r1[0]=='tanggal')
                    {
                        $tanggal1 = $r1[1];
                    } 
                    elseif($r1[0]=='tanggal2') 
                    {
                        $tanggal2 = $r1[1];
                    }
                    else
                    {
                        $where .= " and ".$r1[0]." like '%".$r1[1]."%'";
                    }
                }
				if(!empty($tanggal1) and !empty($tanggal2)) {
					$where.=" and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
				} elseif(!empty($tanggal1)) {
					$where.=" and tanggal >= '".$tanggal1."'";
				} elseif(!empty($tanggal2)) {
					$where.=" and tanggal <= '".$tanggal2."'";
				}
            }
        }
		
        # Header & Align
        $header = array(
			$_SESSION['lang']['notransaksi'],$_SESSION['lang']['unitkerja'],
			$_SESSION['lang']['tanggal'],$_SESSION['lang']['noakun'],
			$_SESSION['lang']['tipe'],$_SESSION['lang']['matauang'],
			$_SESSION['lang']['jumlah'],'Balance',$_SESSION['lang']['remark']
		);
		$align = explode(',','C,L,C,L,C,C,R,C');

        # Content
        $cols = "notransaksi,kodeorg,tanggal,noakun,tipetransaksi,matauang,jumlah,'balan',keterangan,posting";
        $query = selectQuery($dbname,'keu_kasbankht',$cols,$where,
            "tanggal desc, notransaksi desc",false,$param['shows'],$param['page']);
        $data = fetchData($query);
        $totalRow = getTotalRow($dbname,'keu_kasbankht',$where);
        $whereAkun="";$whereOrg="";$i=0;
        foreach($data as $key=>$row) {
            if($row['posting']==1) {
                $data[$key]['switched']=true;
            }
            $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
            unset($data[$key]['posting']);

            # Build Condition
            if($i==0) {
              $whereAkun.="noakun='".$row['noakun']."'";
              $whereOrg.="kodeorganisasi='".$row['kodeorg']."'";
            } else {
              $whereAkun.=" or noakun='".$row['noakun']."'";
              $whereOrg.=" or kodeorganisasi='".$row['kodeorg']."'";
            }
            $i++;
        }


        # Posting --> Jabatan
        $postJabatan = getPostingJabatan('keuangan');

        # Options
        if($_SESSION['language']=='EN'){
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereAkun);
        }else{
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
        }
        
        $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);

        # Mask Data Show
        $dataShow = $data;
        foreach($dataShow as $key=>$row) {
            $dataShow[$key]['jumlah'] = number_format($row['jumlah'],2);
            $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
            $dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
            #=====================tambahan ginting sebagai pembalance
            $str="select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt 
                  where notransaksi='".$data[$key]['notransaksi']."' 
                  and kodeorg='".$data[$key]['kodeorg']."' 
                  and tipetransaksi='".$data[$key]['tipetransaksi']."'
                  and noakun2a='".$data[$key]['noakun']."'";
            $res=mysql_query($str);
            $bar=mysql_fetch_object($res);
            $balan=0;
            $balan=$bar->jumlah;
            $balan=$balan-$row['jumlah'];
            #==================================
            $dataShow[$key]['balan'] = number_format($balan,2);
        }

        # Make Table
        $tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
        $tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
        $tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
        $tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
        $tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
        $tHeader->addAction('detailPDF','Print Voucher','images/'.$_SESSION['theme']."/pdf.jpg");
        if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan) and $_SESSION['empl']['tipelokasitugas']!='HOLDING') {
            $tHeader->_actions[2]->_name='';
        }
        $tHeader->_actions[3]->addAttr('event');
        $tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
        $tHeader->addAction('tampilDetail','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
        $tHeader->_actions[4]->addAttr('event');
        
        $tHeader->addAction('detailPDF2','Print Bukti Pembayaran','images/'.$_SESSION['theme']."/pdf.jpg");
		$tHeader->_actions[5]->addAttr('event');    
		
		$tHeader->_switchException = array('detailPDF','detailPDF2','tampilDetail');
        if(isset($param['where'])) {
            $tHeader->setWhere($arrWhere);
        }
        $tHeader->setAlign($align);

        # View
        $tHeader->renderTable();
        break;
    # Form Add Header
    case 'showAdd':
        // View
        echo formHeader('add',array());
        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Form Edit Header
    case 'showEdit':
        $query = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".
            $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
            "' and noakun='".$param['noakun']."' and tipetransaksi='".
            $param['tipetransaksi']."'");
        $tmpData = fetchData($query);
        $data = $tmpData[0];
        $data['tanggal'] = tanggalnormal($data['tanggal']);

        echo formHeader('edit',$data);

        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Proses Add Header
    case 'add':
        $data = $_POST;
		if($data['kurs'] == 0 || $data['kurs'] == ''){
			exit("Error: Nilai kurs harus diisi (tidak boleh kosong).");
		}
        if(($data['hutangunit']==1)and($data['pemilikhutang']=='' or $data['noakunhutang']==''))
        {
            exit("Error: Please complete the form.");
        }
        else if($data['hutangunit']=='')
        {
            $data['hutangunit']=0;
        }
		
        // Error Trap
        $warning = "";
        //if($data['notransaksi']=='') {$warning .= "Transaction number is obligatory\n";}
        if($data['tanggal']=='') {$warning .= "Date is obligatory\n";}
        if($warning!=''){echo "Warning :\n".$warning;exit;}

		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        $sekarang=  tanggalsystem($data['tanggal']);
        if($sekarang<$_SESSION['org']['period']['start']){
        echo "Validation Error : Date out or range";
        break;                        
        }
		#======================================================
		// Get Tipe Transaksi (Bank / Kas)
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
		
		// Get Last Transaction
		$noTrans = tanggalsystem($data['tanggal'])."/".$data['kodeorg']."/".$tipe."/";
		$qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',
							  "notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
		$resTrans = fetchData($qTrans);
		if(empty($resTrans)) {
			$data['notransaksi'] = $noTrans."00001";
		} else {
			$tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
			$tmpTrans++;
			$data['notransaksi'] = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
		}
		
        //cek notransaksi pada kasbankht
        $str="select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
        $res=mysql_query($str);
        if(mysql_num_rows($res)>0)
        {
            exit("Error: Dokumen dengan nomor yang sama sudah ada\nSilahkan buat no.baru");
        }   

        $data['tanggal'] = tanggalsystem($data['tanggal']);
        $data['jumlah'] = str_replace(',','',$data['jumlah']);
        $data['userid'] = $_SESSION['standard']['userid'];
        $cols = array('notransaksi','noakun','tanggal','matauang','kurs',
            'tipetransaksi','jumlah','cgttu','keterangan','yn','kodeorg','nocek','hutangunit','pemilikhutang','noakunhutang','userid');
        $query = insertQuery($dbname,'keu_kasbankht',$data,$cols);
        if(!mysql_query($query)) {
            echo "DB Error : ".mysql_error();
        } else {
			echo $data['notransaksi'];
		}
        break;
    # Proses Edit Header
    case 'edit':        
		$data = $_POST;
        if($data['kurs'] == 0 || $data['kurs'] == ''){
			exit("Error: Nilai kurs harus diisi (tidak boleh kosong).");
		}
		if(($data['hutangunit']==1)and($data['pemilikhutang']=='' or $data['noakunhutang']==''))
        {
            exit("Error: Silakan melengkapi data hutang.");
        }
        else if($data['hutangunit']=='')
        {
            $data['hutangunit']=0;
        }
		
        // Error Trap
        $warning = "";
        //if($data['notransaksi']=='') {$warning .= "Transaction number is obligatory\n";}
        if($data['tanggal']=='') {$warning .= "Date is obligatory\n";}
        if($warning!=''){echo "Warning :\n".$warning;exit;}

		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        $sekarang=  tanggalsystem($data['tanggal']);
        if($sekarang<$_SESSION['org']['period']['start']){
        echo "Validation Error : Date out or range";
        break;                        
        }
		#======================================================

		if($data['hutangunit']=='') $data['hutangunit']=0;
        $where = "notransaksi='".$data['notransaksi']."' and kodeorg='".
		$data['kodeorg']."' and noakun='".$data['oldNoakun']."' and tipetransaksi='".
		$data['tipetransaksi']."'";
        $wheredt = "notransaksi='".$data['notransaksi']."' and kodeorg='".
        $data['kodeorg']."'";
        $datadt['noakun2a'] = $param['noakun'];
		$datadt['matauang'] = $param['matauang'];
		$datadt['kurs'] = $param['kurs'];
        unset($data['notransaksi']);
        unset($data['kodeorg']);
        unset($data['oldNoakun']);
        unset($data['tipetransaksi']);
        $data['tanggal'] = tanggalsystem($data['tanggal']);
        $data['jumlah'] = str_replace(',','',$data['jumlah']);
        $query = updateQuery($dbname,'keu_kasbankht',$data,$where);
        $querydt = updateQuery($dbname,'keu_kasbankdt',$datadt,$wheredt);
        if(!mysql_query($query)) {
            echo "DB Error ht : ".mysql_error();
        }else{
            if(!mysql_query($querydt)) {
                echo "DB Error dt : ".mysql_error();
            }else{
                echo 'Done.';
            }
        }
        // tadinya ga pake else echo Done, tapi kalo ga pake update-annya ga kesimpen. koq bisa ya?
        // tambahan querydt untuk ngupdate noakun2a kasbankdt
        break;
    case 'delete':
        $where = "notransaksi='".$param['notransaksi']."' and kodeorg='".
            $param['kodeorg']."' and noakun='".$param['noakun']."' and tipetransaksi='".
            $param['tipetransaksi']."'";
        $query = "delete from `".$dbname."`.`keu_kasbankht` where ".$where;
        if(!mysql_query($query)) {
            echo "DB Error : ".mysql_error();
            exit;
        }
        break;
	case 'getUangMuka':
		// Get Transaksi Keluar
              /*
		$where = "a.noakun='".$param['noakun']."' and a.notransaksi != '".
			$param['notransaksi']."' and a.tipetransaksi='K' and b.posting=1";
		if(!empty($param['nik'])) $where .= " and a.nik='".$param['nik']."'";
		$query1 = "SELECT a.*,b.posting from ".$dbname.".keu_kasbankdt a
			LEFT JOIN ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
			WHERE ".$where;
              */
		$where = "a.noakun='".$param['noakun']."' and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.notransaksi != '".
			$param['notransaksi']."' and a.tipetransaksi='K' and b.posting=1";
		$whereUM="d.noakun='".$param['noakun']."' and d.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		if(!empty($param['nik'])){
			$where .= " and a.nik='".$param['nik']."'";
			$whereUM .= " and d.nik='".$param['nik']."'";
              }
		$query1 = "SELECT a.*,b.posting from ".$dbname.".keu_kasbankdt a
			LEFT JOIN ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
			WHERE ".$where;
		$query1 ="SELECT c.* FROM (".$query1.") c WHERE c.notransaksi not in (select d.nodok from ".$dbname.".keu_kasbankdt d where ".$whereUM.")";

		$res1 = fetchData($query1);
		
		// Get Transaksi yang sudah dipertanggungjawabkan
		$where2 = "nodok is not null and nodok <> '' and tipetransaksi='K'";
		$res2 = makeOption($dbname,'keu_kasbankdt','notransaksi,notransaksi',$where2);
		
		// Filter
		$res3 = array();
		$listKary = array();
		foreach($res1 as $row) {
			$listKary[$row['nik']] = $row['nik'];
			if(!in_array($row['notransaksi'],$res2)) {
				$res3[] = $row;
			}
		}
		
		if(!empty($listKary)) {
			$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
								  "karyawanid in ('".implode("','",$listKary)."')");
		} else {
			$optKary = array();
		}
		
		$res = "<div style='max-height:300px;max-width:300px;overflow:auto'><table><thead>";
		$res .= "<tr class=rowheader>";
		$res .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
		$res .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
		$res .= "<td>".$_SESSION['lang']['jumlah']."</td>";
		$res .= "</tr>";
		$res .= "</thead><tbody>";
		if(empty($res3)) {
			$res .= "<tr class=rowcontent><td colspan=3>Data Kosong</td></tr>";
		} else {
			foreach($res3 as $row) {
				$res .= "<tr class=rowcontent onclick='setNodok(\"".$row['notransaksi']."\",\"".
					$row['nik']."\",\"".number_format($row['jumlah'],2)."\")'>";
				$res .= "<td>".$row['notransaksi']."</td>";
				$res .= "<td>".$optKary[$row['nik']]."</td>";
				$res .= "<td align=right>".number_format($row['jumlah'],2)."</td>";
				$res .= "</tr>";
			}
		}
		$res .= "</tbody></table></div>";
		
		echo $res;
		break;
    default:
        break;
}

function formHeader($mode,$data) {
    global $dbname;

    # Default Value
    if(empty($data)) {
        $data['notransaksi'] = '';
        $data['kodeorg'] = $_SESSION['empl']['lokasitugas'];
        $data['noakun'] = '';
        $data['tanggal'] = '';
        $data['tipetransaksi'] = '';
        $data['jumlah'] = '0';
        $data['matauang'] = 'IDR';
        $data['kurs'] = '1';
        $data['cgttu'] = '';
        $data['keterangan'] = '';
        $data['yn'] = '0';
        $data['oldNoakun'] = '';
        $data['hutangunit'] = 0;
        $data['pemilikhutang'] = '';
        $data['nocek'] = '';
        $data['noakunhutang'] = '';
    } else {
        $data['jumlah'] = number_format($data['jumlah'],2);
    }

    # Disabled Primary
    if($mode=='edit') {
        $disabled = 'disabled';
    } else {
        $disabled = '';
    }

    # Options
    $whereJam=" kasbank=1 and detail=1 and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
    $optMataUang = makeOption($dbname,'setup_matauang','kode,matauang');
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
        "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
            if($_SESSION['language']=='EN'){
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam);
        }else{
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam);
        }

    $optTipe = array('M'=>$_SESSION['lang']['masuk'],'K'=>$_SESSION['lang']['keluar']);
    $optCgt = getEnum($dbname,'keu_kasbankht','cgttu');
    $optYn = array(0=>$_SESSION['lang']['belumposting'],1=>$_SESSION['lang']['posting']);
    $wheredz = " kodeorganisasi != '".$_SESSION['empl']['lokasitugas']."' and length(kodeorganisasi)=4";
    //if($optTipe=='M'){
	if($data['tipetransaksi']=='K'){
		$wheredx = " (noakun like '211%' or noakun like '212%' or noakun like '213%') and length(noakun)=7";
    }else{
		$wheredx = " (noakun like '11301%' or noakun like '114%' or noakun like '122%') and length(noakun)=7";
	}
	$optPemilikHutang = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',$wheredz);
    $optNoakunHutang = makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredx,'2');
    $optPemilikHutang['']=''; ksort($optPemilikHutang);
    $optNoakunHutang['']=''; ksort($optNoakunHutang);
    $optHutangUnit = array('0'=>$_SESSION['lang']['ya'],'1'=>$_SESSION['lang']['tidak']);

//    echo "Warning: <pre>".print_r($optNoakunHutang).'</pre>';
//    exit;

    $els = array();
    $els[] = array(
        makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
        makeElement('notransaksi','text',$data['notransaksi'],
            array('style'=>'width:150px','maxlength'=>'25','disabled'=>'disabled'))
    );
    $els[] = array(
        makeElement('kodeorg','label',$_SESSION['lang']['unitkerja']),
        makeElement('kodeorg','select',$data['kodeorg'],
            array('style'=>'width:150px',$disabled=>$disabled),$optOrg)
    );
      $els[] = array(
        makeElement('noakun2a','label',$_SESSION['lang']['noakun']),
        makeElement('noakun2a','select',$data['noakun'],
            array('style'=>'width:150px'),$optAkun)
    );

//    $els[] = array(
//	makeElement('noakun2a','label',$_SESSION['lang']['noakun']),
//	makeElement('noakun2a','select',$data['noakun'],
//	    array('style'=>'width:150px',$disabled=>$disabled),$optAkun)
//    );
//    
    $els[] = array(
        makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
        makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:150px',
        'readonly'=>'readonly','onchange'=>"getKurs()",'onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
        makeElement('matauang','label',$_SESSION['lang']['matauang']),
        makeElement('matauang','select',$data['matauang'],
            array('style'=>'width:150px','onchange'=>"getKurs()"),$optMataUang) #permintaan pak rahmad per tanggal 03 june 2015 by email membuang $disabled=>$disabled setelah getKurs,jamhari
    );
    $els[] = array(
        makeElement('kurs','label',$_SESSION['lang']['kurs']),
        makeElement('kurs','textnum',$data['kurs'],
					array('style'=>'width:150px','disabled'=>'disabled','onkeyup'=>'cekKurs()'))
    );
    
    $els[] = array(
        makeElement('tipetransaksi','label',$_SESSION['lang']['tipetransaksi']),
        makeElement('tipetransaksi','select',$data['tipetransaksi'],
            array('style'=>'width:150px',$disabled=>$disabled,'onchange'=>"getAkun()"),$optTipe)
    );
	$els[] = array(
        makeElement('nocek','label','No. Bukti Bayar'),
        makeElement('nocek','text',$data['nocek'],array('style'=>'width:150px'))
    );
    $els[] = array(
        makeElement('oldNoakun','hid',$data['noakun'] ));
    $els[] = array(
        makeElement('jumlah','label',$_SESSION['lang']['jumlah']),
        makeElement('jumlah','textnum',$data['jumlah'],
            array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))
    );
    $els[] = array(
        makeElement('cgttu','label',$_SESSION['lang']['cgttu']),
        makeElement('cgttu','select',$data['cgttu'],array('style'=>'width:150px'),$optCgt)
    );
    $els[] = array(
        makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
        makeElement('keterangan','text',$data['keterangan'],array('style'=>'width:150px','maxlength'=>'85'))
    );
    $els[] = array(
        makeElement('yn','label',$_SESSION['lang']['yn']),
        makeElement('yn','select',$data['yn'],
            array('style'=>'width:150px','disabled'=>'disabled'),$optYn)
    );
    if($data['hutangunit']==0)
        $dis='disabled'; else $dis='';
//    $els[] = array(
//	makeElement('hutangunit','label',$_SESSION['lang']['hutangunit']),
//        makeElement('hutangunit','checkbox',$data['hutangunit'],
//                array('onclick'=>"pilihhutang()")),
//        makeElement('pemilikhutang','select',$data['pemilikhutang'],
//	    array('style'=>'width:100px',$dis=>$dis),$optPemilikHutang),
//        makeElement('noakunhutang','select',$data['noakunhutang'],
//	    array('style'=>'width:100px',$dis=>$dis),$optNoakunHutang),
//    );
    $els[] = array(
        "<label id='lhutangunit'>".$_SESSION['lang']['piutangunit']."</label>",
        makeElement('hutangunit','checkbox',$data['hutangunit'],
                array('onclick'=>"pilihhutang()"))
    );
    $els[] = array(
        "<label id='lpemilikhutang'>".$_SESSION['lang']['pemilikpiutang']."</label>",
        makeElement('pemilikhutang','select',$data['pemilikhutang'],
            array('style'=>'width:150px',$dis=>$dis),$optPemilikHutang)
    );
    
    
    $els[] = array(
        "<label id='lnoakunhutang'>".$_SESSION['lang']['noakunpiutang']."</label>",
        makeElement('noakunhutang','select',$data['noakunhutang'],
            array('style'=>'width:150px',$dis=>$dis),$optNoakunHutang)
    );
    
    if($mode=='add') {
        $els['btn'] = array(
            makeElement('addHead','btn',$_SESSION['lang']['save'],
                array('onclick'=>"addDataTable()"))//,'disabled'=>'disabled'
        );
    } elseif($mode=='edit') {
        $els['btn'] = array(
            makeElement('editHead','btn',$_SESSION['lang']['save'],
                array('onclick'=>"editDataTable()"))
        );
    }

    if($mode=='add') {
        return genElementMultiDim($_SESSION['lang']['addheader'],$els,2);
    } elseif($mode=='edit') {
        return genElementMultiDim($_SESSION['lang']['editheader'],$els,2);
    }
}
?>