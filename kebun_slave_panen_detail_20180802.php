<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

$str="select * from ".$dbname.".bgt_regional_assignment 
	where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'
	";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$regional=$bar->regional;
}

switch($proses) {
    case 'showDetail':
		#== Prep Tab
		$headFrame = array(
			$_SESSION['lang']['prestasi'],
			$_SESSION['lang']['absensi'],
			$_SESSION['lang']['material']
		);
		$contentFrame = array();
		
		// Tanggal
		$tmpTgl = explode('-',$param['tanggal']);
		$tahun = $tmpTgl[2];
		
		# Options
		$tanggalx=substr($param['notransaksi'],0,4).'-'.substr($param['notransaksi'],4,2).'-'.substr($param['notransaksi'],6,2);
		#============== KHT, KHL dan Kontrak ======================
		$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and ".
			"tipekaryawan in (2,3,4,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".$tanggalx."')";
		#============== KHT, KHL dan Kontrak ======================
		$whereKeg = "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and ";
		$whereKeg .= "kelompok='PNN'";
		
		$optKary = makeOption($dbname,'datakaryawan','karyawanid,nik,subbagian,namakaryawan',$whereKary,'6');
		$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whereKeg);
		#$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
		$optOrg = getOrgBelow($dbname,$param['afdeling'],false,'blok');
		$optThTanam= makeOption($dbname,'setup_blok','kodeorg,tahuntanam',
			"kodeorg='".key($optOrg)."'");
		$optBin = array('1'=>'Ya','0'=>'Tidak');
		$thTanam = $optThTanam[key($optOrg)];
		
		// Validasi Empty
		if(empty($optKary)) {
			exit("Warning: Data Karyawan KHT dan KHL tidak ada.".
				 "\nTransaksi panen tidak dapat dilanjutkan");
		}
		
		#=============================== Get UMR ==============================
		$firstKary = getFirstKey($optKary);
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$tahun." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		$umrHarian = $Umr[0]['nilai']/25;
		#=============================== Get UMR ==============================
		
		#================ Prestasi =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "nik,kodeorg,kodesegment,tahuntanam,norma,outputminimal,hasilkerja,hasilkerjakg,upahkerja,luaspanen,brondolan,upahpremi,".
			"upahpenalty,penalti1,penalti2,penalti3,penalti4,penalti5,penalti6,penalti7,penalti8,penalti9,penalti10,rupiahpenalty,jjgpenalty";
		$query = selectQuery($dbname,'kebun_prestasi',$cols,$where);
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
			$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
			$dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
			#$dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
			#$dataShow[$key]['pekerjaanpremi'] = $optBin[$row['pekerjaanpremi']];
		}
		
			// cari hari
			$day = date('D', strtotime($tanggalx));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalx."'";
			$queorg=mysql_query($strorg) or die(mysql_error());
			while($roworg=mysql_fetch_assoc($queorg))
			{
	//            $libur=true;
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}        
			
		# Form
		$theForm2 = new uForm('prestasiForm','Form Prestasi',3);       
		$theForm2->addEls('nik',$_SESSION['lang']['nik'],'','select','L',25,$optKary);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[0]->_attr['onchange'] = "updUpah()";
			else $theForm2->_elements[0]->_attr['onchange'] = "updUpah2()";
		}
		$theForm2->addEls('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',25,$optOrg);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam();";  
			else $theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam2();";
		} else $theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam2();";  
		$theForm2->addEls('kodesegment',$_SESSION['lang']['kodesegment'],'','searchSegment','L',25);
		$theForm2->addEls('tahuntanam',$_SESSION['lang']['tahuntanam'],$thTanam,'textnum','R',6);
		$theForm2->_elements[3]->_attr['disabled'] = 'disabled';
	//        $theForm2->addEls('bjr',$_SESSION['lang']['bjr'],'','textnum','R',6);
	//	$theForm2->_elements[3]->_attr['disabled'] = 'disabled';
	//        $theForm2->_elements[3]->_attr['onchange'] = "updBjr();";         
		$theForm2->addEls('norma',$_SESSION['lang']['basisjjg'],'0','textnum','R',10);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[4]->_attr['disabled'] = 'disabled';
		}else $theForm2->_elements[4]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[4]->_attr['title'] = 'Basis diambil dari tabel berdasarkan BJR';
		$theForm2->addEls('outputminimal',$_SESSION['lang']['outputminimal'],'0','textnum','R',10);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
		}else $theForm2->_elements[5]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[5]->_attr['title'] = 'Output minimal';
		$theForm2->addEls('hasilkerja',$_SESSION['lang']['hasilkerja'],'0','textnum','R',10);
		$theForm2->_elements[6]->_attr['onkeyup'] = "countPremi();";
		//if($libur==false){
		//	if($regional!='KALTIM')$theForm2->_elements[6]->_attr['onblur'] = "updBjr();";       
		//	if($regional!='KALTIM')$theForm2->_elements[6]->_attr['onkeyup'] = "disablesimpan(this);";       
		//	else $theForm2->_elements[6]->_attr['onblur'] = "updBjr2();";    
		//}else $theForm2->_elements[6]->_attr['onblur'] = "updBjr3();"; 
		$theForm2->addEls('hasilkerjakg',$_SESSION['lang']['hasilkerjakg'],'0','textnum','R',10);
		$theForm2->_elements[7]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[7]->_attr['title'] = 'Hasil Kerja (JJG) * BJR bulan lalu';
		if($libur==false){
			$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],$Umr[0]['nilai']/25,'textnum','R',10);
			if($regional!='KALTIM')$theForm2->_elements[8]->_attr['disabled'] = 'disabled';
		} else {
			$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],'0','textnum','R',10);
			$theForm2->_elements[8]->_attr['disabled'] = 'disabled';
		}
		$theForm2->_elements[8]->_attr['title'] = 'Upah harian';
		$theForm2->addEls('luaspanen',$_SESSION['lang']['luaspanen'],'0','textnum','R',10);
		$theForm2->_elements[9]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('brondolan',$_SESSION['lang']['brondolan'],'0','textnum','R',10);
		$theForm2->_elements[10]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('upahpremi',$_SESSION['lang']['premilebihbasis'],'0','textnum','R',10);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[11]->_attr['disabled'] = 'disabled';
		}else $theForm2->_elements[11]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[11]->_attr['title'] = 'Hasil Kerja > Basis * Premi Lebih Basis';
		
		//$theForm2->addEls('premibasis','','0','hidden','R',10);
		//if($regional!='KALTIM')$theForm2->_elements[12]->_attr['disabled'] = 'disabled';
		//$theForm2->_elements[12]->_attr['title'] = 'Premi Basis';
		
		$theForm2->addEls('upahpenalty',$_SESSION['lang']['upahpenalty'],'0','textnum','R',10);
		if($libur==false){
			if($regional!='KALTIM')$theForm2->_elements[12]->_attr['disabled'] = 'disabled';
		}else $theForm2->_elements[12]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[12]->_attr['title'] = 'Denda upah harian';
		$theForm2->addEls('penalti1',$_SESSION['lang']['penalti1'],'0','textnum','R',10);
		$theForm2->_elements[13]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti2',$_SESSION['lang']['penalti2'],'0','textnum','R',10);
		$theForm2->_elements[14]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti3',$_SESSION['lang']['penalti3'],'0','textnum','R',10);
		$theForm2->_elements[15]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti4',$_SESSION['lang']['penalti4'],'0','textnum','R',10);
		$theForm2->_elements[16]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti5',$_SESSION['lang']['penalti5'],'0','textnum','R',10);
		$theForm2->_elements[17]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti6',$_SESSION['lang']['penalti6'],'0','textnum','R',10);
		$theForm2->_elements[18]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti7',$_SESSION['lang']['penalti7'],'0','textnum','R',10);
		$theForm2->_elements[19]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti8',$_SESSION['lang']['penalti8'],'0','textnum','R',10);
		$theForm2->_elements[20]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti9',$_SESSION['lang']['penalti9'],'0','textnum','R',10);
		$theForm2->_elements[21]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('penalti10',$_SESSION['lang']['penalti10'],'0','textnum','R',10);
		$theForm2->_elements[22]->_attr['onkeyup'] = "countPremi()";
		$theForm2->addEls('rupiahpenalty',$_SESSION['lang']['rupiahpenalty'],'0','textnum','R',10);
		$theForm2->_elements[23]->_attr['disabled'] = 'disabled';
		$theForm2->_elements[23]->_attr['title'] = 'Rupiah Penalty';
		$theForm2->addEls('jjgpenalty',$_SESSION['lang']['jjgpenalty'],'0','textnum','R',10);
		$theForm2->_elements[24]->_attr['disabled'] = 'disabled';
		
		# Table
		$theTable2 = new uTable('prestasiTable','Tabel Prestasi',$cols,$data,$dataShow);
		
		# FormTable
		$formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,array('notransaksi','tanggal'));
		$formTab2->_target = "kebun_slave_panen_detail";
		$formTab2->_noClearField = '##kodeorg##tahuntanam';
		if($libur==false){
			if($regional!='KALTIM')$formTab2->_noEnable = '##tahuntanam##norma##outputminimal##hasilkerjakg##upahkerja##upahpenalty##upahpremi##premibasis##rupiahpenalty##jjgpenalty##kodesegment';
			else $formTab2->_noEnable = '##tahuntanam##rupiahpenalty##jjgpenalty##kodesegment';
			$formTab2->_defValue = '##upahkerja='.$Umr[0]['nilai']/25;
		}else $formTab2->_noEnable = '##tahuntanam##outputminimal##hasilkerjakg##upahkerja##upahpenalty##premibasis##rupiahpenalty##jjgpenalty##kodesegment';
		$formTab2->_defValue = '##upahkerja=0##kodesegment=';
		$formTab2->_afterCrud = "showDetail";
		$formTab2->_numberFormat = '##upahkerja##upahpremi';
		
		// List Karyawan
		$listKary = array();
		foreach($data as $row) {
			$listKary[$row['nik']] = $row['nik'];
		}
		
		// Cek Transaksi Tanggal sama
		$qPres = selectQuery($dbname,'kebun_prestasi_vw','COUNT(karyawanid) AS jumlah,karyawanid',
							 "karyawanid in ('".implode("','",$listKary)."') and
							 tanggal='".tanggalsystem($param['tanggal'])."'").' group by karyawanid';
		$resPres = fetchData($qPres);
		
		// Jumlah Transaksi Panen per Karyawan
		$karyTrans = array();
		foreach($resPres as $row) {
			if($row['jumlah']>1)
				$karyTrans[] = $row['karyawanid'];
		}
		
		#== Display View
		# Draw Tab
		echo "<input id=listKary type='hidden' value='".json_encode($karyTrans)."'>";
		echo "<fieldset><legend><b>Detail</b></legend>";
		echo "<param id='denda' value='{}'>";
		   # echo "<button class=mybutton id=filternik onclick=filterKaryawan(val='null') title='Tampilkan Semua Karyawan'>Show All</button>";
		$formTab2->render();
		echo "</fieldset>";
		break;
	
    case 'add':
		// Cek absensi perawatan
		cekPrestasi($param);
		
//        if($tanggal<'20140201'){ // sebelum tanggal 1 FEB 2014
//
//        }else{
            // cek yang bisa panen berdasarkan taksasi
            $luastaksasi=0;
            $hktaksasi=0;
            $query = "SELECT *
                FROM ".$dbname.".`kebun_taksasi` a
                WHERE a.`tanggal` = '".substr($param['notransaksi'],0,8)."' and a.`blok` = '".$param['kodeorg']."'
                ";
            $qDetail=mysql_query($query) or die(mysql_error($conn));
            while($rDetail=mysql_fetch_assoc($qDetail))
            {
                $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//                $hktaksasi=$rDetail['hkdigunakan'];
                $jjgmasak=$rDetail['jjgmasak'];
                $jjgoutput=$rDetail['hkdigunakan'];
            }
            
            @$hktaksasi=ceil($jjgmasak/$jjgoutput);

            $yangbisapanen=0;
            @$luasperhk=ceil($luastaksasi/$hktaksasi);
            if($luasperhk<=6){
                $yangbisapanen=$hktaksasi;            
            }else{
                $yangbisapanen=$luasperhk;
            }

            // cek hk panen 
            $hkpanen=0;
            $query = "SELECT count(*) as hkpanen
                FROM ".$dbname.".`kebun_prestasi_vw`
                WHERE `tanggal` = '".substr($param['notransaksi'],0,8)."' and `kodeorg` like '".$param['kodeorg']."'
                ";

            $qDetail=mysql_query($query) or die(mysql_error($conn));
            while($rDetail=mysql_fetch_assoc($qDetail))
            {
                $hkpanen=$rDetail['hkpanen'];
            }          
            
        // cari hari
        $day = date('D', strtotime(substr($param['notransaksi'],0,8)));
        if($day=='Sun')$libur=true; else $libur=false;
        // kamus hari libur
        $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".substr($param['notransaksi'],0,8)."'";
        $queorg=mysql_query($strorg) or die(mysql_error());
        while($roworg=mysql_fetch_assoc($queorg))
        {
//            $libur=true;
            if($roworg['keterangan']=='libur')$libur=true;
            if($roworg['keterangan']=='masuk')$libur=false;
        }        

            //if($libur==false){
            //    if($regional!='KALTIM')if($hkpanen>=$yangbisapanen){
            //        echo "error: HK panen tidak boleh melebihi HK taksasi.\n
            //            HK Taksasi: ".$yangbisapanen.", HK Panen: ".$hkpanen;
            //        exit;
            //    }            
            //}
//        }
//            echo "error:".$yangbisapanen."__".$hkpanen;
//            exit();
        
		$cols = array(
			'nik','kodeorg','kodesegment','tahuntanam','norma','outputminimal','hasilkerja','hasilkerjakg',
			'upahkerja','luaspanen','brondolan','upahpremi','upahpenalty',
			'penalti1','penalti2','penalti3','penalti4','penalti5','penalti6','penalti7','penalti8','penalti9','penalti10',
			'rupiahpenalty','jjgpenalty','notransaksi','kodekegiatan','statusblok','pekerjaanpremi'
		);
		$data = $param;
		
		unset($data['numRow']);
		# Additional Default Data
		$data['kodekegiatan'] = '0';
		$data['statusblok'] = 0;$data['pekerjaanpremi'] = 0;
        if($data['luaspanen']==0){
            $warning="Luas Panen(Ha)";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
        # periksa luas panen hari ini apakah sudah melebihi setup blok
        // cari luas blok
        $query = "SELECT luasareaproduktif
            FROM ".$dbname.".`setup_blok`
            WHERE `kodeorg` = '".$param['kodeorg']."'
            ";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luasbloknya=$rDetail['luasareaproduktif'];
        }          

        // cari tanggal
        $query = "SELECT distinct tanggal
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `notransaksi` = '".$param['notransaksi']."'
            ";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
		$tanggalnya = '';
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $tanggalnya=$rDetail['tanggal'];
        }
    if($tanggalnya==''){
        $tanggalnya= tanggalsystemn($param['tanggal']);
    }
        // cari luas panen yang sudah diinput ditambah inputan
        $query = "SELECT sum(luaspanen) as luaspanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."' and karyawanid!='".$param['nik']."'";
        //echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luaspanennya=$rDetail['luaspanen'];
        }
        $luaspanennya+=$data['luaspanen'];

        if($luaspanennya>$luasbloknya){
            $warning="Luas Panen ".$luaspanennya." melebihi Luas Blok ".$luasbloknya." (Ha)";
            echo "error: ".$warning.".";
            exit();               
        }
        unset($data['tanggal']);
        $query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
        if(!mysql_query($query)) {
            echo "DB Error : ".mysql_error();
            exit;
        }
        unset($data['notransaksi']);unset($data['kodekegiatan']);
        unset($data['statusblok']);
        unset($data['pekerjaanpremi']);

        $res = "";
        foreach($data as $cont) {
            $res .= "##".$cont;
        }

        $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
        echo $result;
        
        if($libur==false){
            if($regional!='KALTIM'){
                // ambil premi basis
                //$query = "SELECT afdeling, basis, premibasis, premilebihbasis
                //    FROM ".$dbname.".`kebun_5basispanen2`
                //    WHERE afdeling LIKE '".substr($data['kodeorg'],0,6)."' limit 1
                //    ";
                //$res = fetchData($query);
                //if(!empty($res)) {
                //    $premibasis=$res[0]['premibasis'];            
                //}

                // cek janjang taksasi
                $jjgmasak=0;
                $query = "SELECT *
                    FROM ".$dbname.".`kebun_taksasi` a
                    WHERE a.`tanggal` = '".$tanggalnya."' and a.`blok` = '".$param['kodeorg']."'
                    ";
                $qDetail=mysql_query($query) or die(mysql_error($conn));
                while($rDetail=mysql_fetch_assoc($qDetail))
                {
                    $jjgmasak=$rDetail['jjgmasak'];
                }

                // cek janjang panen
                $hasilkerja=0;
                $query = "SELECT sum(hasilkerja) as hasilkerja
                    FROM ".$dbname.".`kebun_prestasi_vw`
                    WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."'
                    ";
                $qDetail=mysql_query($query) or die(mysql_error($conn));
                while($rDetail=mysql_fetch_assoc($qDetail))
                {
                    $hasilkerja=$rDetail['hasilkerja'];
                }          

                $jjgmasak=$jjgmasak*1.1;
        //        echo "error:".$jjgmasak.",".$hasilkerja;

                // kalo janjang panen>(janjang masak taksasi x 1.1), set premibasis=53000 where premibasis>53000 and notransaksi=notransaksi
                //if($hasilkerja>$jjgmasak){
                //    $query="UPDATE `".$dbname."`.`kebun_prestasi` SET `premibasis` = '".$premibasis."' 
                //        WHERE `notransaksi` = '".$param['notransaksi']."' and `kodeorg` ='".$param['kodeorg']."' AND `premibasis` > '".$premibasis."'";
                //    if(!mysql_query($query)) {
                //        echo "DB Error : ".mysql_error();
                //        exit;
                //    }
                //}
            }
			//proporsiUpah($param);
        }
			proporsiUpah($param);
        break;
	
    case 'edit':
		// Cek absensi perawatan
		cekPrestasi($param);
		
		$data = $param;
        
        // cek inputan luas
        if($data['luaspanen']==0){
            $warning="Luas Panen(Ha)";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
        
        # periksa luas panen hari ini apakah sudah melebihi setup blok
        // cari luas blok
        $query = "SELECT luasareaproduktif
            FROM ".$dbname.".`setup_blok`
            WHERE `kodeorg` = '".$param['kodeorg']."'
            ";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luasbloknya=$rDetail['luasareaproduktif'];
        }          

        // cari tanggal
        $query = "SELECT distinct tanggal
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `notransaksi` = '".$param['notransaksi']."'
            ";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $tanggalnya=$rDetail['tanggal'];
        }

        // cari luas panen yang sudah diinput ditambah inputan
        $query = "SELECT sum(luaspanen) as luaspanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."' and karyawanid!='".$param['nik']."'";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luaspanennya=$rDetail['luaspanen'];
        }
        $luaspanennya+=$data['luaspanen'];

        if($luaspanennya>$luasbloknya){
            $warning="Luas Panen ".$luaspanennya." melebihi Luas Blok ".$luasbloknya." (Ha)";
            echo "error: ".$warning.".";
            exit();               
        }else{

        }        
        
		unset($data['notransaksi']);
		foreach($data as $key=>$cont) {
			if(substr($key,0,5)=='cond_') {
			unset($data[$key]);
			}
		}
    $data['outputminimal']=str_replace(",","",$data['outputminimal']);
    $data['hasilkerja']=str_replace(",","",$data['hasilkerja']);
    $data['hasilkerjakg']=str_replace(",","",$data['hasilkerjakg']);
    $data['upahkerja']=str_replace(",","",$data['upahkerja']);
    $data['luaspanen']=str_replace(",","",$data['luaspanen']);
    $data['brondolan']=str_replace(",","",$data['brondolan']);
    $data['upahpremi'] =str_replace(",","",$data['upahpremi']);
    $data['upahpenalty']=str_replace(",","",$data['upahpenalty']);
    $data['penalti1']=str_replace(",","",$data['penalti1']);
    $data['penalti2']=str_replace(",","",$data['penalti2']);
    $data['penalti3']=str_replace(",","",$data['penalti3']);
    $data['penalti4'] =str_replace(",","",$data['penalti4']);
    $data['penalti5'] =str_replace(",","",$data['penalti5']);
    $data['penalti6'] =str_replace(",","",$data['penalti6']);
    $data['penalti7'] =str_replace(",","",$data['penalti7']);
    $data['penalti8'] =str_replace(",","",$data['penalti8']);
    $data['penalti9'] =str_replace(",","",$data['penalti9']);
    $data['penalti10'] =str_replace(",","",$data['penalti10']);
    $data['rupiahpenalty'] =str_replace(",","",$data['rupiahpenalty']);
    $data['jjgpenalty']=str_replace(",","",$data['jjgpenalty']);
	
		unset($data['tanggal']);
		$where = "notransaksi='".$param['notransaksi']."' and nik='".$param['cond_nik'].
			"' and kodeorg='".$param['cond_kodeorg']."' and kodesegment='".$param['cond_kodesegment']."'";
		$query = updateQuery($dbname,'kebun_prestasi',$data,$where);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
        
        // cari hari
        $day = date('D', strtotime($tanggalnya));
        if($day=='Sun')$libur=true; else $libur=false;
        // kamus hari libur
        $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalnya."'";
        $queorg=mysql_query($strorg) or die(mysql_error());
        while($roworg=mysql_fetch_assoc($queorg))
        {
//            $libur=true;
            if($roworg['keterangan']=='libur')$libur=true;
            if($roworg['keterangan']=='masuk')$libur=false;
        }        
        
		echo json_encode($param);
        if($libur==false){
            if($regional!='KALTIM'){
                // ambil premi basis
                //$query = "SELECT afdeling, basis, premibasis, premilebihbasis
                //    FROM ".$dbname.".`kebun_5basispanen2`
                //    WHERE afdeling LIKE '".substr($data['kodeorg'],0,6)."' limit 1
                //    ";
                //$res = fetchData($query);
                //if(!empty($res)) {
                //    $premibasis=$res[0]['premibasis'];            
                //}

                // cek janjang taksasi
                $jjgmasak=0;
                $query = "SELECT *
                    FROM ".$dbname.".`kebun_taksasi` a
                    WHERE a.`tanggal` = '".$tanggalnya."' and a.`blok` = '".$param['kodeorg']."'
                    ";
                $qDetail=mysql_query($query) or die(mysql_error($conn));
                while($rDetail=mysql_fetch_assoc($qDetail))
                {
                    $jjgmasak=$rDetail['jjgmasak'];
                }

                // cek janjang panen
                $hasilkerja=0;
                $query = "SELECT sum(hasilkerja) as hasilkerja
                    FROM ".$dbname.".`kebun_prestasi_vw`
                    WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."'
                    ";
                $qDetail=mysql_query($query) or die(mysql_error($conn));
                while($rDetail=mysql_fetch_assoc($qDetail))
                {
                    $hasilkerja=$rDetail['hasilkerja'];
                }          

                $jjgmasak=$jjgmasak*1.1;
        //        echo "error:".$jjgmasak.",".$hasilkerja;

                // janjang output di taksasi itu udah dikali 1.1
                // kalo janjang panen>(janjang masak taksasi x 1.1), set premibasis=53000 where premibasis>53000 and notransaksi=notransaksi
                //if($hasilkerja>$jjgmasak){
                //    $query="UPDATE `".$dbname."`.`kebun_prestasi` SET `premibasis` = '".$premibasis."' 
                //        WHERE `notransaksi` = '".$param['notransaksi']."' and `kodeorg` ='".$param['kodeorg']."' AND `premibasis` > '".$premibasis."'";
                //    if(!mysql_query($query)) {
                //        echo "DB Error : ".mysql_error();
                //        exit;
                //    }
                //}                    
            }
			//proporsiUpah($param);
        }
			proporsiUpah($param);
		break;
	
    case 'delete':
		$where = "notransaksi='".$param['notransaksi']."' and nik='".$param['nik'].
			"' and kodeorg='".$param['kodeorg']."' and kodesegment='".$param['kodesegment']."'";
		$query = "delete from `".$dbname."`.`kebun_prestasi` where ".$where;
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		proporsiUpah($param);
		break;
	
    case 'updTahunTanam':
		$query = selectQuery($dbname,'setup_blok','kodeorg,tahuntanam',
			"kodeorg='".$param['kodeorg']."'");
		$res = fetchData($query);
		if(!empty($res)) {
			echo $res[0]['tahuntanam'];
		} else {
			echo '0';
		}
		break;
	
    case 'updBjr':
        
        // KALO ADA UPDATE DI SINI, UPDATE JUGA YANG ADA DI KEBUN_SLAVE_TAKSASI: getSPH
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $bulanbulanbulan=substr($param['notransaksi'],4,2); 
		$firstKary = $param['nik'];
        $tanggal=$param['tanggal'];
        $tanggal=tanggalsystem($tanggal);
        
        $hari=date('l', strtotime($tanggal)); 
        
        if($bulanbulanbulan=='01'){
            $bulanbulanbulan='12';
            $tahuntahuntahun-=1;
        }else{
            $bulanbulanbulan-=1;
            if(strlen($bulanbulanbulan)==1)$bulanbulanbulan='0'.$bulanbulanbulan;
        }
        
        $janjangjanjangjanjang=$param['hasilkerja'];
        $luaspanen=$param['luaspanen'];
        $afdelingafdelingafdeling=substr($param['kodeorg'],0,6);  
        
//        // cek spb vs tiket
//        $spbbelumdiinput='';
//        $query = "SELECT a.nospb, b.tanggal
//            FROM ".$dbname.".`pabrik_timbangan` a
//            LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
//            WHERE a.`tanggal` LIKE '".$tahuntahuntahun."-".$bulanbulanbulan."%' and a.`kodeorg` = '".substr($param['kodeorg'],0,4)."'
//                AND b.`tanggal` is NULL";
//        $qDetail=mysql_query($query) or die(mysql_error($conn));
//        while($rDetail=mysql_fetch_assoc($qDetail))
//        {
//            $spbbelumdiinput.=$rDetail['nospb'].', ';
//        }        
//        if($spbbelumdiinput!=''){
//            $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
//            echo "WARNING: Ada SPB bulan lalu yang belum diinput: ".$spbbelumdiinput;
//            exit;
//        }
//
//        $spbbelumdiposting='';
//        $query = "SELECT nospb, tanggal
//            FROM ".$dbname.".`kebun_spb_vw`
//            WHERE `tanggal` LIKE '".$tahuntahuntahun."-".$bulanbulanbulan."%' and `blok` like '".substr($param['kodeorg'],0,4)."%'
//                and posting = 0
//                ";
//        $qDetail=mysql_query($query) or die(mysql_error($conn));
//        while($rDetail=mysql_fetch_assoc($qDetail))
//        {
//            $spbbelumdiposting.=$rDetail['nospb'].', ';
//        }        
//        if($spbbelumdiposting!=''){
//            $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
//            echo "WARNING: Ada SPB bulan lalu yang belum diposting: ".$spbbelumdiposting;
//            exit;
//        }        
        
        // ambil bjr budget
        $query = "SELECT a.kodeblok, a.thntnm, b.bjr
            FROM ".$dbname.".`bgt_blok` a
            LEFT JOIN ".$dbname.".bgt_bjr b ON a.tahunbudget = b.tahunbudget
                AND substr( a.kodeblok, 1, 4 ) = b.kodeorg
                AND a.thntnm = b.thntanam
            WHERE a.`tahunbudget` =".$tahuntahuntahun."
                AND a.`kodeblok` LIKE '".$param['kodeorg']."'";
		$res = fetchData($query);
		if(!empty($res)) {
			$bjr=$res[0]['bjr'];
		}
        
// ambil bjr sesuaikan dengan algoritma LBM (lbm_slave_produksi_perblok.php)        
//$sProd="select distinct * from ".$dbname.".kebun_spb_bulanan_vw 
//        where blok like '".$param['kodeorg']."' and periode = '".$tahuntahuntahun."-".$bulanbulanbulan."'
//        ";
//$qProd=mysql_query($sProd) or die(mysql_error($conn));
//while($rProd=  mysql_fetch_assoc($qProd))
//{
//    $dtKgBi=$rProd['nettotimbangan'];
//}        
//$sJjg="select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from ".$dbname.".kebun_prestasi_vw 
//       where kodeorg like '".$param['kodeorg']."' and left(tanggal,7) = '".$tahuntahuntahun."-".$bulanbulanbulan."'
//       ";
//$qJjg=mysql_query($sJjg) or die(mysql_error($conn));
//while($rJjg=  mysql_fetch_assoc($qJjg))
//{
//    $jjgpanen=$rJjg['jjg'];
//}
//@$bjr=$dtKgBi/$jjgpanen;        

        // cek bjr via SETUP
        $query = "SELECT *
            FROM ".$dbname.".`kebun_5bjr` a
            WHERE a.`tahunproduksi` = '".substr($param['notransaksi'],0,4)."' and a.`kodeorg` = '".$param['kodeorg']."'
            ";
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $bjr=$rDetail['bjr'];
        }            
        
        $basis=0;
        // ambil basis yang paling kecil
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr asc limit 1
            ";
		$res = fetchData($query);
		if(!empty($res)) {
				$bjrpalingkecil=$res[0]['bjr'];
		}
        // ambil basis yang paling besar
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr desc limit 1
            ";
		$res = fetchData($query);
		if(!empty($res)) {
				$bjrpalingbesar=$res[0]['bjr'];          
		}
        
        $bjr2=$bjr;
        if($bjr<$bjrpalingkecil)$bjr2=$bjrpalingkecil;
        if($bjr>$bjrpalingbesar)$bjr2=$bjrpalingbesar;
        
        // ambil basis berdasarkan bjr + afdeling
        $query = "SELECT afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' and bjr = ".round($bjr2,2)."
            ";
		$res = fetchData($query);
		if(!empty($res)) {
				$basis=$res[0]['basis'];
				$premibasis=$res[0]['premibasis'];            
				$premilebihbasis=$res[0]['premilebihbasis'];            
		}
        
        // kalo hari jumat basisnya 5/7
        if($hari=='Friday'){
            @$basis=5/7*$basis;
        }
        $basis=round($basis);
        
        // itung premi lebih basis
        $lebihbasis=$janjangjanjangjanjang-$basis;
        if($lebihbasis>0){
            $premilebihbasis=$lebihbasis*$premilebihbasis;            
        }else{
            $premilebihbasis=0;
        }
        
        //update upah penalty
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".substr($param['notransaksi'],0,4)." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);        
        $hasilkerja=$param['hasilkerja'];
        // cek yang bisa panen berdasarkan taksasi
        $query = "SELECT *
            FROM ".$dbname.".`kebun_taksasi` a
            WHERE a.`tanggal` = '".substr($param['notransaksi'],0,8)."' and a.`blok` = '".$param['kodeorg']."'
            ";
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        $luastaksasi=0;
        $hktaksasi=0;
        $jjgmasak=0;
        $akp=0;
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//            $hktaksasi=$rDetail['hkdigunakan'];
            $jjgmasak=$rDetail['jjgmasak'];
            $jjgoutput=$rDetail['jjgoutput'];
            
            $akp=$rDetail['persenbuahmatang'];
        }
        
		$sorg="select kodeorg, jumlahpokok as pokokthnini, luasareaproduktif as hathnini from ".$dbname.".setup_blok where kodeorg ='".$param['kodeorg']."'";
		$qorg=mysql_query($sorg) or die(mysql_error($conn));
		while($rorg=mysql_fetch_assoc($qorg)){
			$pokok=$rorg['pokokthnini'];
			$luas=$rorg['hathnini'];
		}
		@$sph=round($pokok/$luas);        
		
        @$hktaksasi=ceil($jjgmasak/$jjgoutput);
        
		$yangbisapanen=0;
        @$luasperhk=ceil($luastaksasi/$hktaksasi);
        if($luasperhk<=6){
            $yangbisapanen=$hktaksasi;            
        }else{
            $yangbisapanen=$luasperhk;
        }       
        
        $upahharian=round($Umr[0]['nilai']/25);
        
        @$capaibasis=$hasilkerja/$basis;        
        if($tanggal<'20140201'){ // sebelum tanggal 1 FEB 2014
            @$batasproporsi=round(0.8*$basis);
            if(($capaibasis>=(0.8))or($luaspanen>=6)){ // luas lebih 6 ha lon dibuang
                $upahpenalty=0;
            }else{
                @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
                $upahpenalty=$upahharian-$upahpenalty;
            }            
        }else{ //setelah tanggal 1 FEB 2014
            if($luasperhk <= 6){
    //            if(($capaibasis>=(0.8))or($luaspanen>=6)){ // luas lebih 6 ha lon dibuang
                @$batasproporsi=round(0.8*$basis);
                if($capaibasis>=(0.8)){ // luas lebih 6 ha lon dibuang
                    $upahpenalty=0;
                }else{
                    @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
                    $upahpenalty=$upahharian-$upahpenalty;
                }
            }else{
                @$batasproporsi=round($sph*6*$akp/100);
                if($hasilkerja>=($batasproporsi)){ // luas lebih 6 ha dibuang
                    $upahpenalty=0;
                }else{
                    @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
//        echo "error: uh:".$upahharian." up".$upahpenalty." hk".$hasilkerja." bp".$batasproporsi." b".$basis; exit;
                    $upahpenalty=$upahharian-$upahpenalty;
                }
            }     
            
        }
        
        if($upahpenalty<0)$upahpenalty=0;
        
//        echo "error: ".$batasproporsi; exit;
  
        // itung premi basis (kalo 2x basis, dapet 2x... dst)
        @$kalibasis=floor($janjangjanjangjanjang/$basis);        
        $premibasis=$premibasis*$kalibasis;            

        $hasilkerjakg=round($bjr*$janjangjanjangjanjang,2);
        $hasilhasilhasil=$hasilkerjakg.'##'.$basis.'##'.$premibasis.'##'.$premilebihbasis.'##'.$upahpenalty.'##'.$upahharian.'##'.$batasproporsi;
        echo $hasilhasilhasil;
		break;
        
    case 'updBjr2': // if($regional=='KALTIM')
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $hasilhasilhasil=$param['hasilkerja'];
		$query = selectQuery($dbname,'kebun_5bjr','kodeorg,bjr',
			"kodeorg='".$param['kodeorg']."' and tahunproduksi = '".$tahuntahuntahun."'");
		$res = fetchData($query);
		if(!empty($res)) {
				$hasilhasil=$hasilhasilhasil*$res[0]['bjr'];
			echo $hasilhasil;
		} else {
			echo '0';
		}
		break;
	
    case 'updBjr3': // khusus hari libur
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $hasilhasilhasil=$param['hasilkerja'];
        $afdelingafdelingafdeling=substr($param['kodeorg'],0,6);  
        
		$query = selectQuery($dbname,'kebun_5bjr','kodeorg,bjr',
			"kodeorg='".$param['kodeorg']."' and tahunproduksi = '".$tahuntahuntahun."'");
		$res = fetchData($query);
		if(!empty($res)) {
				$bjr2=$res[0]['bjr'];
				$hasil3=$hasilhasilhasil*$bjr2;
		} else {
				$bjr2=0;
			$hasil3=0;
		}
        
        // ambil basis berdasarkan bjr + afdeling
        $query = "SELECT afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' and bjr = ".round($bjr2,2)."
            ";
		$res = fetchData($query);
		if(!empty($res)) {
				$basis=$res[0]['basis'];
				$premibasis=$res[0]['premibasis'];            
				$premilebihbasis=$res[0]['premilebihbasis'];            
		}
        $hasil33=$hasilhasilhasil*$premilebihbasis;
        
        // itung premi basis (kalo 2x basis, dapet 2x... dst)
        @$kalibasis=floor($hasilhasilhasil/$basis);        
        $premibasis=$premibasis*$kalibasis;                    
        
        echo $hasil3.'##'.$hasil33.'##'.$basis.'##'.$premibasis;
		break;
	
    case 'updUpah':
		$firstKary = $param['nik'];
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
        $luaspanen=$param['luaspanen'];
        $hasilkerja=$param['hasilkerja'];
        $basis=$param['basis'];
		
		// Get Region
		$qRegion = selectQuery($dbname,'bgt_regional_assignment','regional',
							   "kodeunit");
		$resRegion = fetchData($qRegion);
        
        // cek yang bisa panen berdasarkan taksasi
        $query = "SELECT *
            FROM ".$dbname.".`kebun_taksasi` a
            WHERE a.`tanggal` = '".tanggalsystem($param['tanggal'])."' and a.`blok` = '".$param['kodeorg']."'
            ";
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//            $hktaksasi=$rDetail['hk'];
            $jjgmasak=$rDetail['jjgmasak'];
            $jjgoutput=$rDetail['jjgoutput'];
            
            $akp=$rDetail['persenbuahmatang'];
        }
        
		$sorg="select kodeorg, jumlahpokok as pokokthnini, luasareaproduktif as hathnini from ".$dbname.".setup_blok where kodeorg ='".$param['kodeorg']."'";
		$qorg=mysql_query($sorg) or die(mysql_error($conn));
		while($rorg=mysql_fetch_assoc($qorg)){
			$pokok=$rorg['pokokthnini'];
			$luas=$rorg['hathnini'];
		}
		@$sph=round($pokok/$luas);          
    
        @$hktaksasi=ceil($jjgmasak/$jjgoutput);
        
        @$luasperhk=ceil($luastaksasi/$hktaksasi);
        if($luasperhk<=6){
            $yangbisapanen=$hktaksasi;            
        }else{
            $yangbisapanen=$luasperhk;
        }        
        
        @$capaibasis= ($basis - $hasilkerja)/$basis;
		$upahpenalty=0;
		if(!empty($resRegional) and $resRegional[0]['regional']=='PAPUA' and $capaibasis < 1)
			$upahpenalty = $upahharian * $capaibasis;
		
        //if(tanggalsystem($param['tanggal'])<'20140201'){
        //    @$batasproporsi=round(0.8*$basis);
        //    if(($capaibasis>=(0.8))or($luaspanen>=6)){ // luas lebih 6 ha lon dibuang
        //        $upahpenalty=0;
        //    }else{
        //        @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
        //        $upahpenalty=$upahharian-$upahpenalty;
        //    }
        //    
        //}else{
        //    if($luasperhk <= 6){
        //        if($capaibasis>=(0.8)){ // luas lebih 6 ha lon dibuang
        //            $upahpenalty=0;
        //        }else{
        //            @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
        //            $upahpenalty=$upahharian-$upahpenalty;
        //        }
        //    }else{
        //        @$batasproporsi=$sph*6*$akp/100;
        //        if($hasilkerja>=($batasproporsi)){ // luas lebih 6 ha dibuang
        //            $upahpenalty=0;
        //        }else{
        //            @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
        //            $upahpenalty=$upahharian-$upahpenalty;
        //        }
        //    }        
        //    
        //}
        
		echo round($upahharian).'##'.round($upahpenalty);
		break;
        
    case 'updUpah2': // if($regional=='KALTIM')
		$firstKary = $param['nik'];
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		echo $Umr[0]['nilai']/25;
		break;
		
	case 'countPremi':
		// Get Region
		$qRegion = selectQuery($dbname,'bgt_regional_assignment','regional',
							   "kodeunit = '".substr($param['blok'],0,4)."'");
		$resRegion = fetchData($qRegion);
		
		// Cek Hari Libur
		$qLibur = selectQuery($dbname,'sdm_5harilibur',"*","tanggal='".
							  tanggalsystem($param['tanggal'])."' and ".
							  "keterangan='libur' and kebun in ('GLOBAL','".
							  substr($param['blok'],0,4)."')");
		$resLibur = fetchData($qLibur);
		
		$libur = false;
		if(!empty($resLibur)) $libur = true;
		
		// Get Areal Statement (Setup Blok)
		$qBlok = selectQuery($dbname,'setup_blok','*',"kodeorg='".$param['blok']."'");
		$resBlok = fetchData($qBlok);
		if(empty($resBlok)) exit("Warning: Areal Statement blok ".$param['blok']." belum ada");
		$dataBlok = $resBlok[0];
                
                ##hasil kg
                $iBjr="select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$param['blok']."' order by tahunproduksi DESC limit 1 ";
                $nBjr=  mysql_query($iBjr) or die (mysql_error($conn));
                $dBjr=mysql_fetch_assoc($nBjr);
                    $hasilkg=  number_format($param['hasilkerja']*$dBjr['bjr'],2);
                    $hasilkerjakg=  str_replace(",", "", $hasilkg);
		if($hasilkerjakg-$param['brondolan']<0) exit("Warning: Hasil Kerja (KG) lebih kecil dari Brondolan...!");

	  	//exit("Warning: ".$param['hasilkerja']." Kg, ".$hasilkerjakg." Kgx");

		// Get Basis Panen dan Ketentuan Premi

 	    $topografi=$dataBlok['topografi'];
		$kelaspohon='0';
        if($dBjr['bjr']<=0){
		   exit("Warning: BJR belum disetup pada Blok ".$param['blok']." di KEBUN->Setup->Tabel BJR");
        }elseif($dBjr['bjr']<7){
		   $kelaspohon='1';
        }elseif($dBjr['bjr']<=10){
		   $kelaspohon='2';
        }elseif($dBjr['bjr']>10){
		   $kelaspohon='3';
        }
	
		$jenisPremi = ($libur) ? 'LIBUR': 'KERJA';
		$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
			and jenispremi='".$jenisPremi."'
			and kelaspohon='".$kelaspohon."'
			and topografi='".$dataBlok['topografi']."'";
		$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
		$resBasis = fetchData($qBasis);
		if(empty($resBasis)) exit("Warning: Basis Panen belum ada untuk\nPT ".
								  $_SESSION['org']['kodeorganisasi']."\nJenis Premi ".$jenisPremi.
								  "\nKelas Pohon ".$kelaspohon."\nTopografi ".
								  $dataBlok['topografi']);
		$rumusPremi = $resBasis[0];

		// Get Rate Premi Lebih Basis Hari Kerja, jika hari libur
		// digunakan untuk perhitungan denda
		if($libur) {
			$whereBasis1 = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
				and jenispremi='KERJA'
				and kelaspohon='".$kelaspohon."'
				and topografi='".$dataBlok['topografi']."'";
			$qBasis1 = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis1);
			$resBasis1 = fetchData($qBasis1);
			if(empty($resBasis1)) exit("Warning: Basis Panen belum ada untuk\nPT ".
									  $_SESSION['org']['kodeorganisasi']."\nJenis Premi KERJA".
									  "\nKelas Pohon ".$kelaspohon."\nTopografi ".
									  $dataBlok['topografi']);
		}

		/*
		$jenisPremi = ($libur) ? 'LIBUR': 'KERJA';
		$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
			and jenispremi='".$jenisPremi."'
			and kelaspohon='".$dataBlok['kelaspohon']."'
			and topografi='".$dataBlok['topografi']."'";
		$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
		$resBasis = fetchData($qBasis);
		if(empty($resBasis)) exit("Warning: Basis Panen belum ada untuk\nPT ".
								  $_SESSION['org']['kodeorganisasi']."\nJenis Premi ".$jenisPremi.
								  "\nKelas Pohon ".$dataBlok['kelaspohon']."\nTopografi ".
								  $dataBlok['topografi']);
		$rumusPremi = $resBasis[0];

		// Get Rate Premi Lebih Basis Hari Kerja, jika hari libur
		// digunakan untuk perhitungan denda
		if($libur) {
			$whereBasis1 = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
				and jenispremi='KERJA'
				and kelaspohon='".$dataBlok['kelaspohon']."'
				and topografi='".$dataBlok['topografi']."'";
			$qBasis1 = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis1);
			$resBasis1 = fetchData($qBasis1);
			if(empty($resBasis1)) exit("Warning: Basis Panen belum ada untuk\nPT ".
									  $_SESSION['org']['kodeorganisasi']."\nJenis Premi KERJA".
									  "\nKelas Pohon ".$dataBlok['kelaspohon']."\nTopografi ".
									  $dataBlok['topografi']);
		}
		*/
		// Get Denda
		$qDenda = selectQuery($dbname,'kebun_5dendapanen',"*",
							  "kodeorg='".substr($param['blok'],0,4)."'");
		$resDenda = fetchData($qDenda);
		$optDenda = array();
		foreach($resDenda as $row) {
			$optDenda[$row['kodedenda']] = array(
				'jenis' => $row['jenisdenda'],
				'nilai' => $row['denda']
			);
		}
		
		/**
		 * [START] Perhitungan Denda & Premi
		 */
		// Init
		$premi = 0;
		$premilebih = 0;
		$denda = array(
			'jjg' => 0,
			'rp' => 0
		);
		
		// 1. Denda Panen
		if(is_array($param['penalti'])){
			foreach($param['penalti'] as $kode=>$val) {
				if(isset($optDenda[$kode])) {
					if($optDenda[$kode]['jenis']=='JANJANG') {
						$denda['jjg'] += $val * $optDenda[$kode]['nilai'];
					} elseif($optDenda[$kode]['jenis']=='RUPIAH') {
						$denda['rp'] += $val * $optDenda[$kode]['nilai'];
					}
				}
			}
		}
		if(isset($resBasis1)) {
			$denda['rp'] += $denda['jjg'] * $resBasis1[0]['premilebihbasis'];
		} else {
			$denda['rp'] += $denda['jjg'] * $rumusPremi['premilebihbasis'];
		}
		
		// 2. Premi Kehadiran
		//if(!$libur) {
			$premi += $rumusPremi['premitopografi'];
		//}
		
		// 3. Premi Over Basis
	       $hasiltbs=0;
		   $bataslebih=0;
		   $kgbrondolan=0;
  		   if($rumusPremi['jenisbasis']=='JJG'){
		      $hasiltbs = $param['hasilkerja'];
			  $bataslebih=30;
		      $kgbrondolan=0;
		   }else{
		      $hasiltbs = $hasilkerjakg;
			  $bataslebih=300;
		      $kgbrondolan=$param['brondolan'];
           }

  	       //exit("Warning: ".$hasiltbs." ".$rumusPremi['jenisbasis']." ".$bataslebih." ".$rumusPremi['jenisbasis']." ");

           $overbasis = $hasiltbs - $rumusPremi['basis'];
  		   //Capai Basis
		   if($overbasis>=0){
			  $premi += $rumusPremi['premiliburcapaibasis'];
			  $premilebih += $rumusPremi['premiliburcapaibasis'];
           }
		   if($libur) {
	  	      $premi += ($hasiltbs-$kgbrondolan) * $rumusPremi['premilebihbasis'];
		   }else {
              //Over Basis 1-300 Kg
			  if($overbasis-$kgbrondolan <=0){
			     $premi += 0 * $rumusPremi['premilebihbasis'];
				 $premilebih += 0 * $rumusPremi['premilebihbasis'];
			  }elseif($overbasis-$kgbrondolan >=$bataslebih){
			     $premi += $bataslebih * $rumusPremi['premilebihbasis'];
				 $premilebih += $bataslebih * $rumusPremi['premilebihbasis'];
			  }else{
			     $premi += ($overbasis-$kgbrondolan) * $rumusPremi['premilebihbasis'];
				 $premilebih += ($overbasis-$kgbrondolan) * $rumusPremi['premilebihbasis'];
			  }
              //Over Basis 301-600 Kg
			  if($overbasis-$kgbrondolan-$bataslebih <=0){
			     $premi += 0 * $rumusPremi['premilebihbasis2'];
				 $premilebih += 0 * $rumusPremi['premilebihbasis2'];
			  }elseif($overbasis-$kgbrondolan-$bataslebih >=$bataslebih){
			     $premi += $bataslebih * $rumusPremi['premilebihbasis2'];
				 $premilebih += $bataslebih * $rumusPremi['premilebihbasis2'];
			  }elseif($overbasis-$kgbrondolan-$bataslebih <$bataslebih){
			     $premi += ($overbasis-$kgbrondolan-$bataslebih) * $rumusPremi['premilebihbasis2'];
				 $premilebih += ($overbasis-$kgbrondolan-$bataslebih) * $rumusPremi['premilebihbasis2'];
			  }
              //Over Basis 601-Lebih Kg
			  if($overbasis-$kgbrondolan-($bataslebih*2) <=0){
			     $premi += 0 * $rumusPremi['premilebihbasis3'];
				 $premilebih += 0 * $rumusPremi['premilebihbasis3'];
			  }elseif($overbasis-$kgbrondolan-($bataslebih*2) >0){
			     $premi += ($overbasis-$kgbrondolan-($bataslebih*2)) * $rumusPremi['premilebihbasis3'];
				 $premilebih += ($overbasis-$kgbrondolan-($bataslebih*2)) * $rumusPremi['premilebihbasis3'];
			  }

		   }

		// 4. Premi Brondolan
		$premi += $param['brondolan'] * $rumusPremi['premibrondolan'];
		
		// 5. Premi Hari Libur
		if($libur) {
			//if($overJjg >= 0) {
			//	//$premi += $rumusPremi['premiliburcapaibasis'];
			//	$premi += $rumusPremi['premilibur'];
			//} else {
				$premi += $rumusPremi['premilibur'];
			//}
		}
		
		/**
		 * [END] Perhitungan Denda & Premi
		 */
		
		$firstKary = $param['nik'];
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
		$basis = $rumusPremi['basis'];
 		//$capaibasis= ($basis - $param['hasilkerja'])/$basis;
		   $batasluaspanen=0;
  		   if($topografi=='B1'){
 		      $batasluaspanen=4;
		   }else{
 		      $batasluaspanen=5;
           }
 		$capaibasis= ($basis - $hasiltbs)/$basis;
		$upahpenalty=0;
		if(!($libur) and !empty($resRegion) and ($capaibasis < 1 and $capaibasis > 0) and $param['luaspanen']<$batasluaspanen ){
			$upahpenalty = round($upahharian * $capaibasis,2);
		}
  	    //exit("Warning: ".$topografi." Ha dan ".$param['luaspanen']." Ha ");

		$res = array(
			'dendajjg' => $denda['jjg'],
			'dendarp' => $denda['rp'],
			'premi' => $premi,
			'premilebih' => $premilebih,
			'basis' => $rumusPremi['basis'],
			'hari' => $jenisPremi,
			'upahpenalty' => $upahpenalty,
                        'hasilkerjakg' => $hasilkerjakg
		);//indra
		echo json_encode($res);
		break;
	
    default:
		break;
}

function cekPrestasi($param) {
	global $dbname;
	/*
	// Cek Panen hanya di 1 blok
	$qPnn = selectQuery($dbname,'kebun_prestasi_vw','karyawanid',
						"karyawanid='".$param['nik']."' and tanggal='".
						tanggalsystem($param['tanggal'])."'");
	$resPnn = fetchData($qPnn);
	if(!empty($resPnn)) exit("Warning: Pemanen hanya dapat terdaftar di 1 kali dalam hari yang sama");
	*/
	// Cek Perawatan
	// Jika sudah ada di perawatan tidak bisa input panen
	// Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_prestasi_vw','karyawanid',
						"karyawanid='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."' and notransaksi<>'".$param['notransaksi']."'");
	$resAbs = fetchData($qAbs);
	if(!empty($resAbs)) {
		exit("Warning: Karyawan sudah terdaftar di kemandoran panen lain");
	}

	$qAbs = selectQuery($dbname,'kebun_kehadiran_vw','karyawanid',
						"karyawanid='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."'");
	$resAbs = fetchData($qAbs);
	if(!empty($resAbs)) {
		exit("Warning: Karyawan sudah terdaftar di kegiatan perawatan");
	}
}


function proporsiUpah_Lama($param) {
	global $dbname;
	global $conn;
	
	// Get Tahun
	$tmpTgl = explode('-',$param['tanggal']);
	$tahun = $tmpTgl[2];
	
	// Get UMR
	$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
		"karyawanid=".$param['nik']." and tahun=".$tahun." and idkomponen in (1,31)");
	$Umr = fetchData($qUMR);
	$upahharian=round($Umr[0]['nilai']/25);
	
	// Get Data Panen
	$qPres = selectQuery($dbname,'kebun_prestasi_vw','*',
						 "karyawanid='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."'");
	$resPres = fetchData($qPres);
	
	// Proses hanya jika masih ada data
	if(!empty($resPres)) {
		// Upah Per Blok
		$upahPerBlok = $upahharian / count($resPres);
		
		// Update Data Upah
		$dataUpd = array('upahkerja' => $upahPerBlok);
		
		// Iterasi per transaksi
		foreach($resPres as $row) {
			$qUpd = updateQuery($dbname,'kebun_prestasi',$dataUpd,
				"nik='".$row['karyawanid']."' and
				notransaksi='".$row['notransaksi']."' and
				kodeorg='".$row['kodeorg']."' and
				kodesegment='".$row['kodesegment']."'");
			if(!mysql_query($qUpd)) {
				exit("Proporsi Error: ".mysql_error($conn));
			}
		}
	}
}


function proporsiUpah($param) {
	global $dbname;
	global $conn;
	
	// Get Region
	$qRegion = selectQuery($dbname,'bgt_regional_assignment','regional',
						   "kodeunit = '".substr($param['blok'],0,4)."'");
	$resRegion = fetchData($qRegion);
	// Cek Hari Libur
	$qLibur = selectQuery($dbname,'sdm_5harilibur',"*","tanggal='".
						  tanggalsystem($param['tanggal'])."' and ".
						  "keterangan='libur' and kebun in ('GLOBAL','".
						  substr($param['blok'],0,4)."')");
	$resLibur = fetchData($qLibur);
	$libur = false;
	if(!empty($resLibur)) $libur = true;
	//if($libur) exit;
	
	// Get Tahun
	$tmpTgl = explode('-',$param['tanggal']);
	$tahun = $tmpTgl[2];
	
	// Get UMR
	$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
		"karyawanid=".$param['nik']." and tahun=".$tahun." and idkomponen in (1,31)");
	$Umr = fetchData($qUMR);
	$upahharian=round($Umr[0]['nilai']/25);

	// Get Sum Data Panen Total Hasil Kerja
    $where2 = "a.notransaksi='".$param['notransaksi']."' and a.nik='".$param['nik']."'";
    $where  = "notransaksi='".$param['notransaksi']."' and nik='".$param['nik'].
	          "' and kodeorg='".$param['kodeorg']."' and kodesegment='".$param['kodesegment']."'";
    $qsPres = "SELECT sum(a.hasilkerja) as totaltbs,sum(a.hasilkerjakg) as totaltbskg,sum(a.hasilkerjakg-a.brondolan) as totaltbsnet,sum(a.luaspanen) as totalluaspanen FROM `".$dbname."`.`kebun_prestasi` a WHERE ".$where2;
    $qDetail=mysql_query($qsPres) or die(mysql_error($conn));
	if(empty($qDetail)) exit;
    while($rDetail=mysql_fetch_assoc($qDetail))
    {
      $totaltbs=$rDetail['totaltbs'];
      $totaltbskg=$rDetail['totaltbskg'];
      $totaltbsnet=$rDetail['totaltbsnet'];
      $totalluaspanen=$rDetail['totalluaspanen'];
    }          

	// Get Sum Data Panen Proporsi Basis
	$qsPres = "SELECT a.*,b.topografi FROM `".$dbname."`.`kebun_prestasi` a LEFT JOIN `".$dbname."`.`setup_blok` b on a.kodeorg=b.kodeorg WHERE ".$where2;
    $qDetail=mysql_query($qsPres) or die(mysql_error($conn));
    $totalbasispro=0;
  	$batasluaspanen=0;
	$jenisbasis='KG';
  	$totalpremikehadiran=0;
  	$totalpremilibur=0;
  	$totalpremicapaibasis=0;
  	$totalbrondolan=0;
  	$totalpremibrondolan=0;
	while($rDetail=mysql_fetch_assoc($qDetail))
    {
	   $bjr=$rDetail['hasilkerjakg']/$rDetail['hasilkerja'];

       //Ambil Tabel Premi
 	    $topografi=$rDetail['topografi'];
		$kelaspohon='0';
        if($bjr<=0){
		   exit("Warning: BJR belum disetup pada Blok di KEBUN->Setup->Tabel BJR");
        }elseif($bjr<7){
		   $kelaspohon='1';
        }elseif($bjr<=10){
		   $kelaspohon='2';
        }elseif($bjr>10){
		   $kelaspohon='3';
        }
		$jenisPremi = ($libur) ? 'LIBUR': 'KERJA';
		$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
			and jenispremi='".$jenisPremi."'
			and kelaspohon='".$kelaspohon."'
			and topografi='".$topografi."'";
		$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
		$resBasis = fetchData($qBasis);
		if(empty($resBasis)) exit("Warning: Basis Panen belum ada untuk\nPT ".
								  $_SESSION['org']['kodeorganisasi']."\nJenis Premi ".$jenisPremi.
								  "\nKelas Pohon ".$kelaspohon."\nTopografi ".$topografi);
		$rumusPremi = $resBasis[0];
		$jenisbasis=$rumusPremi['jenisbasis'];
	    if($jenisbasis=='JJG'){
	       $totalbasispro +=$rumusPremi['basis']*($rDetail['hasilkerja']/$totaltbs);
           if($topografi=='B1'){
              $batasluaspanen +=4*($rDetail['hasilkerja']/$totaltbs);
           }else{
 	          $batasluaspanen +=5*($rDetail['hasilkerja']/$totaltbs);
           }
   		  $totalpremikehadiran += $rumusPremi['premitopografi']*($rDetail['hasilkerja']/$totaltbs);
 		  $totalpremilibur += $rumusPremi['premilibur']*($rDetail['hasilkerja']/$totaltbs);
		  $totalpremicapaibasis += $rumusPremi['premiliburcapaibasis']*($rDetail['hasilkerja']/$totaltbs);
	    }else{
	      $totalbasispro +=$rumusPremi['basis']*(($rDetail['hasilkerjakg']-$rDetail['brondolan'])/$totaltbsnet);
          if($topografi=='B1'){
             $batasluaspanen +=4*($rDetail['hasilkerjakg']/$totaltbskg);
          }else{
 	         $batasluaspanen +=5*($rDetail['hasilkerjakg']/$totaltbskg);
          }
   		  $totalpremikehadiran += $rumusPremi['premitopografi']*($rDetail['hasilkerjakg']/$totaltbskg);
 		  $totalpremilibur += $rumusPremi['premilibur']*($rDetail['hasilkerjakg']/$totaltbskg);
		  $totalpremicapaibasis += $rumusPremi['premiliburcapaibasis']*($rDetail['hasilkerjakg']/$totaltbskg);
		}
		$totalbrondolan += $rDetail['brondolan'];
		$totalpremibrondolan += $rumusPremi['premibrondolan']*$rDetail['brondolan'];
	}

	// Get Data Panen
    $qPres = "SELECT a.*,b.topografi FROM `".$dbname."`.`kebun_prestasi` a LEFT JOIN `".$dbname."`.`setup_blok` b on a.kodeorg=b.kodeorg WHERE ".$where2;
    $resPres=mysql_query($qPres) or die(mysql_error($conn));
	// Iterasi per transaksi
    while($rowPres=mysql_fetch_assoc($resPres))
    {
       $notransaksi=$rowPres['notransaksi'];
       $nik        =$rowPres['nik'];
       $kodeorg    =$rowPres['kodeorg'];
       $kodesegment=$rowPres['kodesegment'];
	   $bjr        =$rowPres['hasilkerjakg']/$rowPres['hasilkerja'];
	   $brondolan  =$rowPres['brondolan'];
	   $premicapaibasis=0;
       $hasiltbs=0;
	   $bataslebih=0;
	   $kgbrondolan=0;

	   //Ambil Tabel Premi
 	    $topografi=$rowPres['topografi'];
		$kelaspohon='0';
        if($bjr<=0){
		   exit("Warning: BJR belum disetup pada Blok di KEBUN->Setup->Tabel BJR");
        }elseif($bjr<7){
		   $kelaspohon='1';
        }elseif($bjr<=10){
		   $kelaspohon='2';
        }elseif($bjr>10){
		   $kelaspohon='3';
        }
		$jenisPremi = ($libur) ? 'LIBUR': 'KERJA';
		$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
			and jenispremi='".$jenisPremi."'
			and kelaspohon='".$kelaspohon."'
			and topografi='".$topografi."'";
		$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
		$resBasis = fetchData($qBasis);
		if(empty($resBasis)) exit("Warning: Basis Panen belum ada untuk\nPT ".
								  $_SESSION['org']['kodeorganisasi']."\nJenis Premi ".$jenisPremi.
								  "\nKelas Pohon ".$kelaspohon."\nTopografi ".$topografi);
		$rumusPremi = $resBasis[0];
		$jenisbasis=$rumusPremi['jenisbasis'];

	   if($jenisbasis=='JJG'){
	      $hasiltbs   =$rowPres['hasilkerja'];
	      $kgbrondolan=0;
		  $totalhasiltbs=$totaltbs;
		  $totalhasiltbsnet=$totaltbs;
		  $bataslebih=30*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet;
	   }else{
	      $hasiltbs   =$rowPres['hasilkerjakg'];
	      $kgbrondolan=$brondolan;
          $totalhasiltbs=$totaltbskg;
		  $totalhasiltbsnet=$totaltbsnet;
		  $bataslebih=300*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet;
	   }
       $upahkerja  =round($upahharian*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet,2);
	   $premikehadiran=round($totalpremikehadiran*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet,2);
	   $premilibur=round($totalpremilibur*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet,2);
	   if($totalhasiltbs>=$totalbasispro){
  		 $premicapaibasis=round($totalpremicapaibasis*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet,2);
	   }
	   //$premibrondolan=round($totalpremibrondolan*($brondolan/$totalbrondolan),2);
	   $premibrondolan=round($brondolan*$rumusPremi['premibrondolan'],2);

		// 3. Premi Over Basis
		   $premi=0;
           $overbasis = ($totalhasiltbsnet-$totalbasispro)*(($hasiltbs-$kgbrondolan)/$totalhasiltbsnet);
		   if($libur){
			  $upahkerja=0;
	  	      $premi=($hasiltbs-$kgbrondolan)*$rumusPremi['premilebihbasis'];
		   }else {
              //Over Basis 1-300 Kg
			  if($overbasis<=0){
			     $premi += 0 * $rumusPremi['premilebihbasis'];
			  }elseif($overbasis>=$bataslebih){
			     $premi += $bataslebih * $rumusPremi['premilebihbasis'];
			  }else{
			     $premi += ($overbasis) * $rumusPremi['premilebihbasis'];
			  }
              //Over Basis 301-600 Kg
			  if($overbasis-$bataslebih <=0){
			     $premi += 0 * $rumusPremi['premilebihbasis2'];
			  }elseif($overbasis-$bataslebih >=$bataslebih){
			     $premi += $bataslebih * $rumusPremi['premilebihbasis2'];
			  }elseif($overbasis-$bataslebih <$bataslebih){
			     $premi += ($overbasis-$bataslebih) * $rumusPremi['premilebihbasis2'];
			  }
              //Over Basis 601-Lebih Kg
			  if($overbasis-($bataslebih*2) <=0){
			     $premi += 0 * $rumusPremi['premilebihbasis3'];
			  }elseif($overbasis-($bataslebih*2) >0){
			     $premi += ($overbasis-($bataslebih*2)) * $rumusPremi['premilebihbasis3'];
			  }
		   }

	   $upahpremi=round($premikehadiran+$premilibur+$premicapaibasis+$premibrondolan+$premi,2);

       //Denda
 	   $capaibasis= ($totalbasispro - $totalhasiltbs)/$totalbasispro;
	   $totalupahpenalty=0;
	   if(!($libur) and ($capaibasis < 1 and $capaibasis > 0) and $totalluaspanen<$batasluaspanen ){
		  $totalupahpenalty = round($upahharian * $capaibasis,2);
	   }
	   $upahpenalty=round($totalupahpenalty*($hasiltbs-$kgbrondolan)/$totalhasiltbsnet,2);
	   $where  = "notransaksi='".$notransaksi."' and nik='".$nik.
	             "' and kodeorg='".$kodeorg."' and kodesegment='".$kodesegment."'";

	   // Update Data Upah
       //$qUpd="UPDATE `".$dbname."`.`kebun_prestasi` SET `upahkerja` = '".$upahkerja."',`upahpenalty` = '".$upahpenalty."',`upahpremi` = '".$upahpremi."',`premibasis` = //'".$premi."' WHERE ".$where;
       $qUpd="UPDATE `".$dbname."`.`kebun_prestasi` SET `upahkerja` = '".$upahkerja."',`upahpenalty` = '".$upahpenalty."',`upahpremi` = '".$upahpremi."' WHERE ".$where;
	   if(!mysql_query($qUpd)) {
	  	  exit("Proporsi Error: ".mysql_error($conn));
	   }
    }          
}
