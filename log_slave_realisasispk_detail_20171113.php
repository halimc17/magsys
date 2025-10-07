<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'showDetail':
		# Options
		#khusus jika project
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB'){
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama',"kodeorg='".$param['kebun']."' and posting=0");
			$optPrj = array();
			foreach($optBlok as $key=>$row) {
				$optPrj[] = $key;
			}
			$str="select kegiatan,namakegiatan from ".$dbname.".project_dt
				where kodeproject in ('".implode("','",$optPrj)."')";
			$res=mysql_query($str);
			while($bar=mysql_fetch_object($res)){
				$optAct[$bar->kegiatan]=$bar->namakegiatan;
			}               
		} else {
			//$str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
			//	  ON a.kodeorg = b.kodeorganisasi 
			//	  WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($param['divisi'],0,4)."%' 
			//	  and length(b.kodeorganisasi)>6";
			//$res_blok=mysql_query($str_blok);
			//while($bar=mysql_fetch_object($res_blok)) {
			//   $optBlok[$bar->kodeorg]=$bar->namaorg;
			//}
			$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
								  "kodeorganisasi like '".substr($param['divisi'],0,4)."%'");
            $optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
        }    
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."' and kodeblok like '".substr($param['divisi'],0,4)."%'";
		$cols = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
		$query = selectQuery($dbname,'log_spkdt',$cols,$where);
		$data = fetchData($query);
		$dataShow = array();
		foreach($data as $key=>$row) {
			$dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
			$dataShow[$key]['kodekegiatan'] = $optAct[$row['kodekegiatan']];
			$dataShow[$key]['hk'] = $row['hk'];
			$dataShow[$key]['hasilkerjajumlah'] = $row['hasilkerjajumlah'];
			$dataShow[$key]['satuan'] = $row['satuan'];
			$dataShow[$key]['jumlahrp'] = $row['jumlahrp'];
		}
		
		#== Grid
		$headName = array(
			$_SESSION['lang']['subunit'],
			$_SESSION['lang']['kodekegiatan'],
			$_SESSION['lang']['hk'],
			$_SESSION['lang']['hasilkerjajumlah'],
			$_SESSION['lang']['satuan'],
			$_SESSION['lang']['jumlahrp'],
		);
		
		# Grid Header
		$grid = "<table class='sortable'><thead><tr class='rowheader'>";
		foreach($headName as $head) {
			$grid .= "<td>".$head."</td>";
		}
		$grid .= "</tr></thead>";

		# Grid Content
		$grid .= "<tbody>";
		if(empty($data)) {
			$grid .= "<tr class='rowcontent'><td colspan='10'>Data Empty</td></tr>";
		} else {
			foreach($dataShow as $key=>$row) {
				$grid .= "<tr class='rowcontent' onclick=\"manageDetail(".$key.")\" style='cursor:pointer'>";
				foreach($row as $head=>$cont) {
					$grid .= "<td id='".$head."_".$key."' ";
					if(isset($data[$key][$head])) {
						$grid .= "value='".$data[$key][$head]."' ";
					} else {
						$grid .= "value='' ";
					}
					if($head=='kodeblok' or $head=='kodekegiatan') {
							$grid .= "align='left'";
					} else {
							$grid .= "align='right'";
					}
					if($head=='jumlahrp') {
						$grid .= ">".number_format($cont)."</td>";
					} else {
						$grid .= ">".$cont."</td>";
					}
                }
                $grid .= "</tr>";
                $grid .= "<tr><td colspan='6'><div id='detail_".$key."'></div></td></tr>";
            }
        }
        $grid .= "</tbody>";
        $grid .= "</table>";
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		echo $grid;
		echo "</fieldset>";
		break;
    case 'manageDetail':
		# Get Data
		$cols = 'a.*, b.revisihasilkerja, b.revisihk, b.revisijumlah';
		$where = "a.notransaksi='".$param['notransaksi']
			. "' and a.kodekegiatan='".$param['kodekegiatan']
			. "' and a.blokspkdt='".$param['kodeblok']."'";
		//$query = selectQuery($dbname,'log_baspk',$cols,$where);
		$query = "SELECT ".$cols." FROM ".$dbname.".log_baspk a LEFT JOIN ".
			$dbname.".log_baspk_rev b ON
			a.notransaksi = b.notransaksi AND
			a.kodeblok = b.kodeblok AND
			a.kodekegiatan = b.kodekegiatan AND
			a.tanggal = b.tanggal AND
			a.blokspkdt = b.blokspkdt AND
			a.kodesegment = b.kodesegment WHERE ".$where;
        
        $resDetail = fetchData($query);
		foreach($resDetail as $key=>$row) {
			$resDetail[$key]['jumlahrealisasi'] = number_format($row['jumlahrealisasi']);
		}
        # Options
        if($_SESSION['empl']['tipelokasitugas']!='KEBUN') {
            $str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".organisasi b 
				WHERE b.kodeorganisasi like '".$param['kodeblok']."%'";
            $res_blok=mysql_query($str_blok);
            while($bar=mysql_fetch_object($res_blok)) {
                $optBlok[$bar->kodeorg]=$bar->namaorg;
            }  
        } else {
			$str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
				ON a.kodeorg = b.kodeorganisasi 
				WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($param['divisi'],0,4)."%' 
				and length(b.kodeorganisasi)>6";
            $res_blok=mysql_query($str_blok);
            while($bar=mysql_fetch_object($res_blok)) {
                $optBlok[$bar->kodeorg]=$bar->namaorg;
            }
            
			
        }
		#khusus jika project
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB') {
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama',
								  "kodeorg='".$param['kebun']."' and kode='".
								  $param['kodeblok']."' and posting=0");
		}
		
		// Init Segment
		$optSegment = array();
		$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		// Option Segment
		$listSegment = '';
		foreach($resDetail as $row) {
			if(!empty($listSegment)) $listSegment .= ',';
			$listSegment .= "'".$row['kodesegment']."'";
		}
		if(!empty($listSegment)) {
			$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',
									 "kodesegment in (".$listSegment.")");
		}
		
		# Setting Table
		$header = array(
			$_SESSION['lang']['subunit'],
			$_SESSION['lang']['segment'],
			$_SESSION['lang']['tanggal'],
			$_SESSION['lang']['matauang'],
			$_SESSION['lang']['hkrealisasi'],
			$_SESSION['lang']['hasilkerjarealisasi'],
			$_SESSION['lang']['jumlahrealisasi'],
            $_SESSION['lang']['jjgkontanan'],
			$_SESSION['lang']['action']
		);
		
		# Table
		$table = "";
		$table .= "<table class='sortable' style='margin-bottom:15px'>";
		$table .= "<thead><tr class='rowheader'>";
		foreach($header as $head) {
			$table .= "<td>".$head."</td>";
		}
		$table .= "</tr></thead>";
		$table .= "<tbody id='detailBody_".$param['numRow']."'>";
		$i=0;
		foreach($resDetail as $row) {
			# Exist Row
			$tanggal = tanggalnormal($row['tanggal']);
			$table .= "<tr id='tr_".$param['numRow'].'_'.$i."' class='rowcontent'>";
			$table .= "<td>".makeElement('blokalokasi_'.$param['numRow'].'_'.$i,'select',$row['kodeblok'],array('disabled'=>'disabled'),$optBlok)."</td>";
			$table .= "<td>".makeElement('kodesegment_'.$param['numRow'].'_'.$i,'select',$row['kodesegment'],array('disabled'=>'disabled'),$optSegment)."</td>";
			$table .= "<td>".makeElement('tanggal_'.$param['numRow'].'_'.$i,'text',$tanggal,array('disabled'=>'disabled'))."</td>";
			$table .= "<td>".makeElement('matauang_'.$param['numRow'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled'))."</td>";
			if($row['statusjurnal']==0) {
				$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',$row['hkrealisasi'])."</td>";
				$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
					'textnum',$row['hasilkerjarealisasi'],array('onkeyup'=>"calJumlah(".$param['numRow'].",".$i.")"))."</td>";
				$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['jumlahrealisasi'],array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))."</td>";
				$table .= "<td>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
					$row['jjgkontanan'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)'))."</td>";
				$table .= "<td><img id='btn_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				$table .= "src='images/".$_SESSION['theme']."/save.png' ";
				$table .= "onclick='saveData(".$param['numRow'].",".$i.")'>&nbsp;";
				$table .= "<img id='btnDel_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				$table .= "src='images/".$_SESSION['theme']."/delete.png' ";
				$table .= "onclick='deleteData(".$param['numRow'].",".$i.")'>&nbsp;";
				$table .= "<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				$table .= "src='images/".$_SESSION['theme']."/posting.png' ";
				$table .= "onclick=\"postingData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">&nbsp;";
				$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' style='display:none'";
				$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
				$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."',event)\">";
			} else {
				if($row['revisijumlah']>0) $row['jumlahrealisasi'] = $row['revisijumlah'];
				if($row['revisihk']>0) $row['hkrealisasi'] = $row['revisihk'];
				if($row['revisihasilkerja']>0) $row['hasilkerjarealisasi'] = $row['revisihasilkerja'];
				$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['hkrealisasi'],array('disabled'=>'disabled'))."</td>";
				$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
					'textnum',$row['hasilkerjarealisasi'],array('disabled'=>'disabled'))."</td>";
				$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['jumlahrealisasi'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)'))."</td>";
				$table .= "<td>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
					$row['jjgkontanan'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)'))."</td>";
				$table .= "<td>&nbsp;&nbsp;<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				$table .= "src='images/".$_SESSION['theme']."/posted.png'>&nbsp;";
				if($row['revisijumlah']>0) {
					$table .= "<a style='cursor:pointer' title='HK = ".$row['revisihk'].";Hasil = ".
						$row['revisihasilkerja'].";Jumlah = ".number_format($row['revisijumlah'])."'>*Rev</a>";
				} else {
					$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
					$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
					$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."',event)\">";
				}
			}
			$table .= "</td>";
			$table .= "</tr>";
			$i++;
		}
		
		// Opt Segment for New Row
		$blok = key($optBlok);
		$query = "select distinct a.kodesegment,a.namasegment from ".$dbname.".keu_5segment a
			left join ".$dbname.".keu_5proporsisegment b on a.kodesegment=b.kodesegment
			where b.kodeblok='".$blok."' or a.kodesegment = '".$defaultSegment."'";
		$res = fetchData($query);
		$optSegment = array();
		foreach($res as $row) {
			$optSegment[$row['kodesegment']] = $row['namasegment'];
		}
		
		# New Row
		$table .= "<tr id='tr_".$param['numRow'].'_'.$i."' class='rowcontent'>";
		$table .= "<td>".makeElement('blokalokasi_'.$param['numRow'].'_'.$i,'select','',
									 array('onchange'=>"getSegment(".$param['numRow'].",".$i.")"),$optBlok)."</td>";
		$table .= "<td>".makeElement('kodesegment_'.$param['numRow'].'_'.$i,'select','',array(),$optSegment)."</td>";
		$table .= "<td>".makeElement('tanggal_'.$param['numRow'].'_'.$i,'date')."</td>";
		$table .= "<td>".makeElement('matauang_'.$param['numRow'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled'))."</td>";
		$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',0)."</td>";
		$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
			'textnum',0,array('onkeyup'=>"calJumlah(".$param['numRow'].",".$i.")"))."</td>";
		$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',0,
			array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))."</td>";
        $table .= "<td>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
			0,array('disabled'=>'disabled',
			'onchange'=>'this.value = _formatted(this)'))."</td>";
		$table .= "<td><img id='btn_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/plus.png' ";
		$table .= "onclick=\"addData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">&nbsp;";
		$table .= "<img id='btnDel_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/delete.png' style='display:none'";
		$table .= "onclick='deleteData(".$param['numRow'].",".$i.")'>&nbsp;";
		$table .= "<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/posting.png' ";
		$table .= "onclick=\"postingData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\" style='display:none'>&nbsp;";
		$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' style='display:none'";
		$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
		$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">";
		$table .= "</td></tr></tbody>";
		$table .= "</table>";
		$i++;
		
		echo $table;
		break;
    case 'add':
		$data = $param;
		unset($data['numRow1']);
		unset($data['divisi']);
		unset($data['matauang']);
		unset($data['blokalokasi']);
		unset($data['numRow2']);
		unset($data['kebun']);
		$data['kodeblok'] = $param['blokalokasi'];
		$data['posting'] = '0';
		$data['statusjurnal'] = '0';
		$data['blokspkdt'] = $param['kodeblok'];
        
		$data['jumlahrealisasi'] = str_replace(',','',$data['jumlahrealisasi']);
                $data['jjgkontanan'] = str_replace(',','',$data['jjgkontanan']);
		$dtCol=array('notransaksi', 'kodeblok', 'kodekegiatan','kodesegment', 'tanggal', 'hasilkerjarealisasi', 'hkrealisasi', 'jumlahrealisasi', 'jjgkontanan', 'posting', 'statusjurnal', 'blokspkdt');
		# Options
		$str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
			ON a.kodeorg = b.kodeorganisasi 
			WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($param['divisi'],0,4)."%' 
			and length(b.kodeorganisasi)>6";
		$res_blok=mysql_query($str_blok);
		$optBlok = array();
		while($bar=mysql_fetch_object($res_blok)) {
            $optBlok[$bar->kodeorg]=$bar->namaorg;
        }  
		#khusus jika project
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama',
								  "kodeorg='".$param['kebun']."' and kode='".
								  $param['kodeblok']."' and posting=0");
		}
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB'){
		//	$optBlok = makeOption($dbname,'project','kode,nama',"kode='".$param['divisi']."' and posting=0");
		//}    		
		# Empty Data
		foreach($data as $cont) {
			if($cont=='') exit('Warning : Data tidak boleh ada yang kosong');
		}
        
		//cek tanam: april 4, 2014
		//dicopy dari file: kebun_slave_operasional_detail: cegatKegiatan
        $kegiatan = $param['kodekegiatan'];
        $kodeorg = $param['blokalokasi'];
        $hasilkerja = $param['hasilkerjarealisasi'];
        $qwe=explode('-',$param['tanggal']);        
        $tanggal = $qwe[2].'-'.$qwe[1].'-'.$qwe[0];
        
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
		$kodeparameter='';
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
        
        // ambil jumlah sisip
        // BKM
        $query="select kodeorg,sum(hasilkerja)as telahsisip from ".$dbname.".kebun_perawatan_vw 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeorg = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";        
        $res=mysql_query($query);
		$sudahsisip=0;
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
            if($hasilkerja>$maxtanam){
                echo "error: Tidak bisa tanam baru, luas yang belum ditanam: ".number_format($luasareanonproduktif,2)." Ha, pokok bisa ditanam: ".number_format($maxtanam).". Jumlah ditanam: ".number_format($hasilkerja).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='COMPL'){
            if($sph>150){
                echo "error: SPH setelah transaksi lebih dari 150: ".number_format($sph,2).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='SISIP'){
            if($sisasisip<0){
                echo "error: Harap diinput data pokok mati dan rencana sisipan, rencana sisip: ".$rencanasisip.", sudah sisip: ".$sudahsisip." + ".$hasilkerja.", sisa rencana sisip: ".$sisasisip.".";
                exit();
            }
        }                
                //
		
		# Convert Tanggal
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		
		$strKurs="select * from ".$dbname.".setup_matauangrate where daritanggal=".$data['tanggal']."";
		$qryKurs=mysql_query($strKurs) or die(mysql_error($conn));
        $numRows=mysql_num_rows($qryKurs);
		
		if($param['matauang']!='IDR' && $numRows<=0){
			echo "Gagal : Data kurs untuk mata uang ".$param['matauang']." pada tanggal ".tanggalnormal($data['tanggal'])." masih belum ada";
		}else{
			$query = insertQuery($dbname,'log_baspk',$data,$dtCol);
			if(!mysql_query($query)) {
				echo "DB Error : ".mysql_error();
				exit;
			} else {
				// Init Segment
				$optSegment = array();
				$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
				
				// Opt Segment for New Row
				$blok = key($optBlok);
				$query = "select distinct a.kodesegment,a.namasegment from ".$dbname.".keu_5segment a
					left join ".$dbname.".keu_5proporsisegment b on a.kodesegment=b.kodesegment
					where b.kodeblok='".$blok."' or a.kodesegment = '".$defaultSegment."'";
				$res = fetchData($query);
				$optSegment = array();
				foreach($res as $row) {
					$optSegment[$row['kodesegment']] = $row['namasegment'];
				}
				
				# Prepare New
				$i = $param['numRow2']+1;
				$row = "<td>".makeElement('blokalokasi_'.$param['numRow1'].'_'.$i,'select','',array(),$optBlok)."</td>";
				$row .= "<td>".makeElement('kodesegment_'.$param['numRow1'].'_'.$i,'select','',array(),$optSegment)."</td>";
				$row .= "<td>".makeElement('tanggal_'.$param['numRow1'].'_'.$i,'date')."</td>";
				$row .= "<td>".makeElement('matauang_'.$param['numRow1'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled'))."</td>";
				$row .= "<td>".makeElement('hkrealisasi_'.$param['numRow1'].'_'.$i,'textnum',0)."</td>";
				$row .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow1'].'_'.$i,
				'textnum',0,array('onkeyup'=>"calJumlah(".$param['numRow1'].",".$i.")"))."</td>";
				$row .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow1'].'_'.$i,'textnum',0,
				array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))."</td>";
				$row .= "<td>".makeElement('jjgkontanan_'.$param['numRow1'].'_'.$i,'textnum',0,
				array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)','disabled'=>'disabled'))."</td>";
				$row .= "<td><img id='btn_".$param['numRow1']."_".$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/plus.png' ";
				$row .= "onclick=\"addData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\">&nbsp;";
				$row .= "<img id='btnDel_".$param['numRow1'].'_'.$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/delete.png' style='display:none'";
				$row .= "onclick='deleteData(".$param['numRow1'].",".$i.")'>&nbsp;";
				$row .= "<img id='btnPost_".$param['numRow1'].'_'.$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/posting.png' ";
				$row .= "onclick=\"postingData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\" style='display:none'>&nbsp;";
				$row .= "<img id='btnRev_".$param['numRow1'].'_'.$i."' class='zImgBtn' style='display:none'";
				$row .= "src='images/".$_SESSION['theme']."/zoom.png' ";
				$row .= "onclick=\"revisiData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\">";
				$row .= "</td>";
				
				echo $row;
			}
		}
		break;
    case 'edit':
		$data = $param;
		unset($data['notransaksi']);
		unset($data['kodeblok']);
		unset($data['blokalokasi']);
		unset($data['kodekegiatan']);
		unset($data['kodesegment']);
		unset($data['tanggal']);
		unset($data['matauang']);
		unset($data['numRow1']);
		unset($data['numRow2']);
		$data['jumlahrealisasi'] = str_replace(',','',$data['jumlahrealisasi']);
        $data['jjgkontanan'] = str_replace(',','',$data['jjgkontanan']);
		
		# Empty Data
		foreach($data as $cont) {
			if($cont=='') {
			echo 'Warning : Data tidak boleh ada yang kosong';
			exit;
			}
		}
		
		# Convert Tanggal
		$param['tanggal'] = tanggalsystem($param['tanggal']);
		
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeblok='".$param['blokalokasi'].
			"' and kodekegiatan='".$param['kodekegiatan'].
			"' and tanggal='".$param['tanggal'].
			"' and blokspkdt='".$param['kodeblok'].
			"' and kodesegment='".$param['kodesegment']."'";
		$query = updateQuery($dbname,'log_baspk',$data,$where);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		break;
    case 'delete':
		# Convert Tanggal
		$param['tanggal'] = tanggalsystem($param['tanggal']);
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeblok='".$param['blokalokasi'].
			"' and kodekegiatan='".$param['kodekegiatan'].
			"' and tanggal='".$param['tanggal'].
			"' and blokspkdt='".$param['kodeblok'].
			"' and kodesegment='".$param['kodesegment']."'";
		$query = "delete from `".$dbname."`.`log_baspk` where ".$where;
		
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		break;
    default:
	break;
}
?>