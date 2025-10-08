<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zFunction.php');
include_once('lib/zLib.php');

$method = checkPostGet('method','');
$kode = checkPostGet('kode','');

$unit =checkPostGet('unit','');
$aset =checkPostGet('aset','');
$jenis =checkPostGet('jenis','');
$nama =checkPostGet('nama','');
$tanggalmulai=tanggalsystem(checkPostGet('tanggalmulai',''));
$tanggalselesai=tanggalsystem(checkPostGet('tanggalselesai',''));
$kelompok =checkPostGet('kelompok','');
$nilai =checkPostGet('nilai','');
$optLokasi=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$kegiatan =checkPostGet('kegiatan','');
$nmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNmKegBrg=makeOption($dbname, 'project_dt', 'kegiatan,namakegiatan');

$kodeproject=checkPostGet('kodeproject','');
$kodekegiatan=checkPostGet('kodekegiatan','');
$kodeBarangForm=checkPostGet('kodeBarangForm','');//buat insert
$kodebarang=checkPostGet('kodebarang','');//buat delete
$jumlahBarangForm=checkPostGet('jumlahBarangForm','');

$namaBarangCari=checkPostGet('namaBarangCari','');

$satKeg=checkPostGet('satKeg','');
        $volKeg=checkPostGet('volKeg','');
        $bobotKeg=checkPostGet('bobotKeg','');


$sub=checkPostGet('sub','');

$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

switch($method)
{
    
    case'getSub':
   
        $optSub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $iSub="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$aset."' ";
        $nSub=  mysql_query($iSub) or die (mysql_error($conn));
        while($dSub=  mysql_fetch_assoc($nSub))
        {
            if($_POST['sub']==$dSub['kodesub'])
            {
                $select="selected=selected";
            }
            else
            {
                $select="";
            }
            $optSub.="<option ".$select." value='".$dSub['kodesub']."'>".$dSub['namasub']."</option>";
        }
        
     

    echo $optSub;
    
   
    
    break;
    
    
    case 'update':	
    $str="update ".$dbname.".project set nama='".$nama."',
          tanggalmulai='".$tanggalmulai."',tanggalselesai='".$tanggalselesai."',
          updateby='".$_SESSION['standard']['userid']."',subtipe='".$sub."'
          where kode='".$kode."'";
    if(!mysql_query($str))
    { echo " Gagal,".addslashes(mysql_error($conn));}
    break;
    
    case 'insert':
		// String Kode
		$kode = $jenis.'-'.$aset.$sub;
		
        // cari nomor terakhir
        $str="select kode from ".$dbname.".project
			where kode like '".$kode."%'
            order by substring(kode, -5) desc
            limit 1";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $belakangnya=intval(substr($bar->kode,-5));
        }
        $belakangnya+=1;
        
        $belakangnya=addZero($belakangnya,10-strlen($aset.$sub));
        $kode=$jenis."-".$aset.$sub.$belakangnya;
        $str="insert into ".$dbname.".project (kode, nama, tipe, kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe)
              values('".$kode."','".$nama."','".$jenis."','".$unit."','".$tanggalmulai."','".$tanggalselesai."',".$_SESSION['standard']['userid'].",'".$sub."')";
        if(mysql_query($str))
        {
            
        }
        else
        {
            echo " Gagal,".addslashes(mysql_error($conn));
        }	
    break;
    
    case 'delete':
		$sGudang = "select * from ".$dbname.".log_transaksi_vw where kodeblok = '".$kode."'";
		$cGudang = mysql_num_rows(mysql_query($sGudang));
		
		$sKasBank = "select * from ".$dbname.".keu_kasbankdt where kodeasset = '".$kode."'";
		$cKasBank = mysql_num_rows(mysql_query($sKasBank));
		
		$sBaSpk = "select * from ".$dbname.".log_baspk where kodeblok = '".$kode."'";
		$cBaSpk = mysql_num_rows(mysql_query($sBaSpk));
		
		$sJurnal = "select * from ".$dbname.".keu_jurnaldt_vw where kodeasset = '".$kode."'";
		$cJurnal = mysql_num_rows(mysql_query($sJurnal));
		
		if($cGudang > 0 || $cKasBank > 0 || $cBaSpk> 0 || $cJurnal > 0){
			exit("Gagal: Item ini tidak dapat dihapus, sudah ada transaksi.");
		}
		
        $str="delete from ".$dbname.".project where kode='".$kode."'";
        if(mysql_query($str))
        {

        }
        else
        {
            echo " Gagal,".addslashes(mysql_error($conn));
        }
    break;
    
    case'loadData':
        //$str1="select * from ".$dbname.".project where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by substring(kode, -7) desc";
      $postJabatan = getPostingJabatan('traksi');
		if($_SESSION['empl']['subbagian']=='')
		{
			if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
				$whereCont = "TRUE";
			}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
				$whereCont = "kodeorg in (select kodeorganisasi from ".$dbname.".organisasi c where c.induk='".$_SESSION['empl']['kodeorganisasi']."' and kodeorganisasi not like '%HO')";
			}else{
				$whereCont = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
			}
		}
		else
		{
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
				$whereCont = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
			}else{
				//$whereCont = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
				$whereCont = "a.updateby='".$_SESSION['standard']['userid']."'";
			}
		}
     $str1="select a.*,b.namakaryawan from ".$dbname.".project a 
            left join ".$dbname.".datakaryawan b on a.updateby=b.karyawanid 
			where ".$whereCont."
			order by a.posting,a.kodeorg,substring(a.kode, -10) desc";
	//echo $str1;		
    if($res1=mysql_query($str1))
    {
        $rowd=mysql_num_rows($res1);
        if($rowd==0)
        {
            echo"<tr class=rowcontent><td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
        }
        else
        {
            $no=0;
            while($bar1=mysql_fetch_object($res1))
            {
                
                $kdAst=substr($bar1->kode,3,2);
                $iSubAst="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$kdAst."' ";
                $nSubAst=  mysql_query($iSubAst) or die (mysql_error($conn));
                $dSubAst=  mysql_fetch_assoc($nSubAst);
                
                
             
                $qwe=substr($bar1->kode,3,3);
                $asd=substr($qwe,-1);
                if($asd=='0')$aset=substr($qwe,0,2);
                else $aset=$qwe;

                $no+=1;
                echo"<tr class=rowcontent>
                    <td>".$bar1->kode."</td>
                    <td>".$bar1->kodeorg."</td>
                    <td>".$bar1->tipe."</td>
                    <td>".$bar1->nama."</td>
                    <td>".tanggalnormal($bar1->tanggalmulai)."</td>
                    <td>".tanggalnormal($bar1->tanggalselesai)."</td>
                    <td>".$bar1->namakaryawan."</td>
                    <td>";
                
                    if($bar1->posting==0 and $bar1->updateby==$_SESSION['standard']['userid']){
                        echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$aset."','".$bar1->tipe."','".$bar1->nama."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalselesai)."','update','".$bar1->kode."','".$bar1->subtipe."');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapus('".$bar1->kode."');\">
                        <img src=images/nxbtn.png class=resicon  title='Detail' onclick=\"detailForm('".$bar1->kodeorg."','".$aset."','".$bar1->tipe."','".$bar1->nama."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalselesai)."','detail','".$bar1->kode."','".$bar1->subtipe."','".$dSubAst['namasub']."');\">
						<img src=images/skyblue/posting.png class=resicon  title='posting data' onclick=\"postIni('".$bar1->kode."','".$bar1->kodeorg."');\">";
                    }elseif($bar1->posting==0 and in_array($_SESSION['empl']['kodejabatan'],$postJabatan)){
                        echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$aset."','".$bar1->tipe."','".$bar1->nama."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalselesai)."','update','".$bar1->kode."','".$bar1->subtipe."');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapus('".$bar1->kode."');\">
                        <img src=images/nxbtn.png class=resicon  title='Detail' onclick=\"detailForm('".$bar1->kodeorg."','".$aset."','".$bar1->tipe."','".$bar1->nama."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalselesai)."','detail','".$bar1->kode."','".$bar1->subtipe."','".$dSubAst['namasub']."');\">
                        <img src=images/skyblue/posting.png class=resicon  title='posting data' onclick=\"postIni('".$bar1->kode."','".$bar1->kodeorg."');\">";
                    }else{
                        if($bar1->posting==1){
                            echo"<img src=images/skyblue/posted.png class=resicon>";
                        }else{    
                            echo"<img src=images/skyblue/posting.png>";
                        }                       
						//echo"<img onclick=\"masterPDF('project','".$bar1->kode.",".$bar1->updateby."','','vhc_slave_project_pdf',event);\" title=\"Print\" class=\"resicon\" src=\"images/pdf.jpg\">";
                    }
                    echo"</td>
                        <td>
                            <img onclick=\"masterPDF('project','".$bar1->kode.",".$bar1->updateby."','','vhc_slave_project_pdf',event);\" title=\"Print\" class=\"resicon\" src=\"images/pdf.jpg\">
                   	 <img onclick=excelMaterial(event,'".$bar1->kode."') src=images/excel.jpg class=resicon title='MS.Excel Material'>
                            <img onclick=timeFrame(event,'".$bar1->kode."') src=images/excel.jpg class=resicon title='MS.Excel Time Frame Project'>
			</td></tr>";
            }
        }
    }
    break;
    
    case'detail':   
    $sDet="select distinct * from ".$dbname.".project_dt  where kodeproject='".$kode."'";
    $qDet=mysql_query($sDet) or die(mysql_error($conn));
    $row=mysql_num_rows($qDet);
	$frmdt="";
    if($row==0)
    {
        $frmdt.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['dataempty']."</td></tr>";
    }
    else
    {
        while($rDet=  mysql_fetch_assoc($qDet))
        {
        $frmdt.="<tr class=rowcontent><td>".$rDet['kodeproject']."</td>";
        $frmdt.="<td>".$rDet['namakegiatan']."</td>";
        $frmdt.="<td>".$rDet['satuan']."</td>";
        $frmdt.="<td align=right>".$rDet['volume']."</td>";
        $frmdt.="<td align=right>".$rDet['bobot']."</td>";
        $frmdt.="<td>".tanggalnormal($rDet['tanggalmulai'])."</td>";
        $frmdt.="<td>".tanggalnormal($rDet['tanggalselesai'])."</td>";
        $frmdt.="<td>
                <img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$rDet['kegiatan']."','".$rDet['kodeproject']."','".$_SESSION['lang']['find']."',event)>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDet('".tanggalnormal($rDet['tanggalmulai'])."','".tanggalnormal($rDet['tanggalselesai'])."','updatedet','".$rDet['kodeproject']."','".$rDet['kegiatan']."','".$rDet['namakegiatan']."','".$rDet['satuan']."','".$rDet['volume']."','".$rDet['bobot']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapusData('".$rDet['kegiatan']."');\">
                </td></tr>";
        }
    }
    echo $frmdt;
    
    break;
    case'insertDetail':
        $tglMul=tanggalsystem(checkPostGet('tglMul',''));
        $tglakh=tanggalsystem(checkPostGet('tglSmp',''));
        
        


        $sCek="SELECT datediff('".$tglakh."', '".$tglMul."') as selisih";
        $hasil = mysql_query($sCek);
        $data = mysql_fetch_array($hasil);
        if($data['selisih']<0)
        {
            exit("Error:Tanggal Selesai Lebih Besar dari Tanggal Mulai");
        }
    $sInser="insert into ".$dbname.".project_dt (kodeproject, namakegiatan, tanggalmulai, tanggalselesai,satuan,volume,bobot) 
             values ('".$kode."','".$_POST['nmKeg']."','".tanggalsystem($_POST['tglMul'])."','".tanggalsystem($_POST['tglSmp'])."','".$satKeg."','".$volKeg."','".$bobotKeg."')";
    if(!mysql_query($sInser))
    {
        die(mysql_error($conn));
    }
    break;
    
    case'updatedet':
         $tglMul=tanggalsystem($_POST['tglMul']);
         $tglakh=tanggalsystem($_POST['tglSmp']);

        $sCek="SELECT datediff('".$tglakh."', '".$tglMul."') as selisih";
        $hasil = mysql_query($sCek);
        $data = mysql_fetch_array($hasil);
        if($data['selisih']<0)
        {
            exit("Error:Tanggal Selesai Lebih Kecil dari Tanggal Mulai");
        }
    $sUpdate="update ".$dbname.".project_dt set namakegiatan='".$_POST['nmKeg']."',
              tanggalmulai='".tanggalsystem($_POST['tglMul'])."', tanggalselesai='".tanggalsystem($_POST['tglSmp'])."',
              satuan='".$satKeg."',volume='".$volKeg."',bobot='".$bobotKeg."' where kegiatan='".$_POST['index']."'";
    if(!mysql_query($sUpdate))
    {
        die(mysql_error($conn));
    }
    break;
    
    case'hpsDetail':
    $sdel="delete from ".$dbname.".project_dt where kegiatan='".$_POST['index']."'";
    if(!mysql_query($sdel))
    {
        die(mysql_error($conn));
    }
    break;
    
    case 'postingData':
		// Parse Kode
		$detailKode = explode('-',$kode);
		$tipe = substr($detailKode[1],0,2);
		$kodeorg = $_SESSION['org']['kodeorganisasi'];
		
		// Get Parameter Jurnal
		$qParam = selectQuery($dbname,'keu_5parameterjurnal',"noakundebet, noakunkredit",
							  "kodeaplikasi='PRJ' and jurnalid='PRJ".$tipe."'");
		$resParam = fetchData($qParam);
		
		// Get Header Project
		$qH = selectQuery($dbname,'project',"*","kode='".$kode."'");
		$resH = fetchData($qH);
		$subtipe = $resH[0]['subtipe'];
		
		if(!empty($resParam)) {
			if($tipe!='TM') {
                        //if($tipe!='PR' and $tipe!='TM') {    
				// Get Nilai Project
				$qNilai = "SELECT SUM(jumlah) as jumlah FROM ".$dbname.".keu_jurnaldt
					WHERE kodeasset='".$kode."'";
				$resNilai = fetchData($qNilai);
				
				if(empty($resNilai)) {
					exit("Warning: Project belum direalisasi. Data tidak dapat di posting");
				} elseif($resNilai[0]['jumlah']==0) {
					exit("Warning: Nilai Project tidak ada. Data tidak dapat di posting");
				}
				$nilai = $resNilai[0]['jumlah'];
				
				// Default Segment
				$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
				
				/**
				 * Pendaftaran Asset
				 */
				// Get Jumlah Bulan
				$qSubTipe = selectQuery($dbname,'sdm_5subtipeasset',"umurpenyusutan",
										"kodetipe='".$tipe."' and kodesub='".$subtipe."'");
				$resSubTipe = fetchData($qSubTipe);
				
				if(empty($resSubTipe)) {
					exit("Warning: Sub Tipe ".$subtipe." dari tipe asset ".$tipe.
						 " belum terdaftar\nSilahkan hubungi IT");
				}
				
				// Kode Asset
				$kodeAsset = $kodeorg."-".$tipe.$subtipe;
				$qAsset = selectQuery($dbname,'sdm_daftarasset',"kodeasset",
									  "kodeasset like '".$kodeAsset."%' order by kodeasset desc limit 1" );
				$resAsset = fetchData($qAsset);
				if(empty($resAsset)) {
					$counterAsset = 1;
				} else {
					$counterAsset = substr($resAsset[0]['kodeasset'],8,6)+1;
				}
				$kodeAsset .= str_pad($counterAsset,6,'0',STR_PAD_LEFT);
				
				// Data
				$dataAsset = array(
					'kodeorg' => $unit,
					'kodeasset' => $kodeAsset,
					'tipeasset' => $tipe,
					'tahunperolehan' => date('Y'),
					'namasset' => $resH[0]['nama'],
					'hargaperolehan' => $nilai,
					'jlhblnpenyusutan' => $resSubTipe[0]['umurpenyusutan'],
					'keterangan' => $resH[0]['nama'],
					'awalpenyusutan' => date('Y-m'),
					'user' => $_SESSION['standard']['userid'],
					'bulanan' => $nilai/$resSubTipe[0]['umurpenyusutan'],
					'penambah' => 0,
					'pengurang' => 0,
					'posisiasset' => $unit,
					'kodeproject' => $kode
				);
				$cols = array();
				foreach($dataAsset as $key=>$row) {
					$cols[] = $key;
				}
				$qIns = insertQuery($dbname,'sdm_daftarasset',$dataAsset,$cols);
				
				if(!mysql_query($qIns)) {
					exit("Insert Asset Error: ".$mysql_error($conn));
				}
				
				/**
				 * Jurnal
				 */
				# Get Journal Counter
				$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
					"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and ".
					"kodekelompok='PRJ".$tipe."'");
                                
				$tmpKonter = fetchData($queryJ);
				if(empty($tmpKonter)) {
                                    
					// Jika Kelompok Jurnal belum ada, insert
					$dataKel = array(
						'kodeorg' => $_SESSION['org']['kodeorganisasi'],
						'kodekelompok' => "PRJ".$tipe,
						'keterangan' => "Project ".$tipe,
						'nokounter' => 0
					);
					$qKel = insertQuery($dbname,'keu_5kelompokjurnal',$dataKel);
					if(!mysql_query($qKel)) {
						exit("DB Error: ".mysql_error());
					}
					
					$konter = '001';
				} else {
                                    
                                    //exit("Error:MASUK");
                                    
					$konter = addZero($tmpKonter[0]['nokounter']+1,3);
				}
				
				$tanggal = date('Ymd');
				
				# Prep No Jurnal
				$nojurnal = $tanggal."/".$unit."/PRJ".
					$tipe."/".$konter;
				
				# Prep Header
				$dataRes['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>'PRJ'.$tipe,
					'tanggal'=>$tanggal,
					'tanggalentry'=>$tanggal,
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$kode,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				
				$dataRes['detail'] = array();
				$dataRes['detail'][0] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>1,
					'noakun'=>$resParam[0]['noakundebet'],
					'keterangan'=>"Project ".$kode,
					'jumlah'=>$nilai,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>$kode,
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$kode,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$kode,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				$dataRes['detail'][1] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>2,
					'noakun'=>$resParam[0]['noakunkredit'],
					'keterangan'=>"Project ".$kode,
					'jumlah'=>(-1)*$nilai,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>$kode,
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$kode,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$kode,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				
				$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				if(!mysql_query($queryH)) {
					$errorDB .= "Header :".$queryH."\n";
				}
				
				foreach($dataRes['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					
					if(!mysql_query($queryD)) {
						$errorDB .= "Detail ".$key." :".$queryD."\n";
					}
				}
			} else {
				exit("Warning: Parameter Jurnal 'DEP".$tipe."' belum ada\n".
					 "Silahkan hubungi pihak IT");
			}
		}
                else
                {
                    exit("Warning: Parameter Jurnal 'PRJ".$tipe."' belum ada\n".
					 "Silahkan hubungi pihak IT");
                }
                
                
                
                
                
		//exit("Error:$errorDB");
		if(empty($errorDB)) {	
			$sCari="select distinct updateby from ".$dbname.".project where kode='".$_POST['kode']."'";
			$qCari=mysql_query($sCari) or die(mysql_error($conn));
			$rCari=mysql_fetch_assoc($qCari);
			/*
			if($optLokasi[$rCari['updateby']]!=$_SESSION['empl']['lokasitugas'] and !in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
				$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback jurnal
				$str="delete from ".$dbname.".sdm_daftarasset where kodeasset='".$kodeAsset."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback asset			
				exit("Error:Anda Tidak Memiliki Autorisasi");
			}
			*/
			$sPost="update ".$dbname.".project set updateby='".$_SESSION['standard']['userid']."',posting='1' where kode='".$_POST['kode']."'";
			if(!mysql_query($sPost)){ 
				$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback jurnal
				$str="delete from ".$dbname.".sdm_daftarasset where kodeasset='".$kodeAsset."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback asset				
				die(mysql_error($conn));
			}
                        
			$nokounterbaru=$tmpKonter[0]['nokounter']+1;
			$iUpdate="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounterbaru."' "
					. " where kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='PRJ".$tipe."' ";               
			if(!mysql_query($iUpdate)){
				$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback jurnal
				$str="delete from ".$dbname.".sdm_daftarasset where kodeasset='".$kodeAsset."'";
				mysql_query($str) or die(" Error".mysql_error($conn)); #rollback asset	
				die(" Gagal update counter".mysql_error($conn));
			}
		}
		else
		{
			//hapus jurnal dan hapus daftar 
			$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			mysql_query($str) or die(" Error".mysql_error($conn)); #rollback jurnal
			$str="delete from ".$dbname.".sdm_daftarasset where kodeasset='".$kodeAsset."'";
			mysql_query($str) or die(" Error".mysql_error($conn)); #rollback asset			
		}
		break;
    
    case'timeFrame':	
        $iHead="select * from ".$dbname.".project where kode='".$kode."'";
        $nHead=mysql_query($iHead) or die (mysql_error($conn));
        $dHead=mysql_fetch_assoc($nHead);

        $tgl1=
        $stream="<table border=0>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['unit']."</td>
                            <td><u>".$optNmOrg[$dHead['kodeorg']]."</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['project']."</td>
                            <td><u>".$dHead['nama']."</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['mulai']."</td>
                            <td><u>".tanggalnormal($dHead['tanggalmulai'])."</u></td>
                            <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['selesai']."</td>
                            <td><u>".tanggalnormal($dHead['tanggalselesai'])."</u></td>
                    </tr>
                </table>";//NO	Kodebarang	Namabarang	Satuan	JLH RAB	DIPAKAI	SELISIH	
	$arrTgl=rangeTanggal($dHead['tanggalmulai'],$dHead['tanggalselesai']);
//	print_r($arrTgl);
	$stream.="<br /><table class=sortable border=1 cellspacing=1>
                         <thead>
                            <tr>
                                <td align=center bgcolor=#CCCCCC>Tahapan</td>";
                                if(!empty($arrTgl))foreach($arrTgl as $lstTgl=>$tgl)
                                {
                                    $stream.="<td align=center bgcolor=#CCCCCC>".tanggalnormal($tgl)."</td>";
                                }
	$stream.="</tr>";
	$iTahap="select * from ".$dbname.".project_dt where kodeproject='".$kode."' ";
	//echo $iTahap;
	$nTahap=mysql_query($iTahap) or die (mysql_error($conn));
	while($dTahap=mysql_fetch_assoc($nTahap))
	{
		//$i+=1;
		//$listKdProject[$dTahap['kodeproject']]=$dTahap['kodeproject'];
		$tahapan[$dTahap['namakegiatan']]=$dTahap['namakegiatan'];
		$tglMulai[$dTahap['namakegiatan']]=$dTahap['tanggalmulai'];
		$tglSelesai[$dTahap['namakegiatan']]=$dTahap['tanggalselesai'];
	}
	echo $i;
	//$tglMulai[$dTahap['namakegiatan'].$dTahap['tanggalmulai']]
	//$arrTgl=rangeTanggal($dHead['tanggalmulai'],$dHead['tanggalselesai']);

	if(!empty($tahapan))foreach($tahapan as $listTahapan)
	{
            $arrTglData=rangeTanggal($tglMulai[$listTahapan],$tglSelesai[$listTahapan]);
            $listTersimpan=false;
            $dert=false;
            $stream.="<tr>
                        <td>".$tahapan[$listTahapan]."</td>";
            $isi="";
            if(!empty($arrTgl))foreach($arrTgl as $listTgl)
            {
                if($dert==false)
                {
                    if($tglSelesai[$listTahapan]==$listTgl)
                    {	
                        $isi="bgcolor=blue";//$isi="bgcolor=red";
                        $listTersimpan=false;
                        //$tglSelesai[$listTahapan]="";
                        $dert=true;
                    }
                    else
                    {
                        if($listTersimpan==false)
                        {
                            if($tglMulai[$listTahapan]==$listTgl)
                            {
                                    $isi="bgcolor=blue";
                                    $listTersimpan=true;
                            }

                        }

                    }
                }
                else
                {
                        $isi="";
                        $dert=false;
                }
                //$isi="";//exit("Error:HAHA");
                $stream.="<td ".$isi."></td>";	//".$tglSelesai[$listTahapan]."			
            }	
	}
	//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="Laporan_Progres_Project".$dHead['kode'];
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }  
	
	break;
        
		case'excelMaterial':
		
		$iHead="select * from ".$dbname.".project where kode='".$kode."'";
		$nHead=mysql_query($iHead) or die (mysql_error($conn));
		$dHead=mysql_fetch_assoc($nHead);
			
		$stream="<table border=0>
					<tr>
						<td></td>
						<td>".$_SESSION['lang']['unit']."</td>
						<td><u>".$optNmOrg[$dHead['kodeorg']]."</u></td>
					</tr>
					<tr>
						<td ></td >
						<td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['project']."</td>
						<td><u>".$dHead['nama']."</u></td>
					</tr>
					<tr>
						<td></td>
						<td>".$_SESSION['lang']['namakelompok']." ".$_SESSION['lang']['project']."</td>
						<td><u>".$dHead['tipe']."</u></td>
					</tr>
					<tr>
						<td></td>
						<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['mulai']."</td>
						<td><u>".tanggalnormal($dHead['tanggalmulai'])."</u></td>
						<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['selesai']."</td>
						<td><u>".tanggalnormal($dHead['tanggalselesai'])."</u></td>
					</tr>
				</table>";//NO	Kodebarang	Namabarang	Satuan	JLH RAB	DIPAKAI	SELISIH
	
		$stream.="<br /><table class=sortable border=1 cellspacing=1>
					 <thead>
						<tr>
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['nourut']."</td> 
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['kodebarang']."</td> 
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['namabarang']."</td> 
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['satuan']."</td> 
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['penggunaan']." ".$_SESSION['lang']['project']."</td> 
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['jumlahkeluargudang']."</td>
							<td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['selisih']."</td> 
						</tr>";
						
		$iPro="select * from ".$dbname.".project_material where kodeproject='".$kode."' ";
		$nPro=mysql_query($iPro) or die (mysql_error($conn));
		while($dPro=mysql_fetch_assoc($nPro))
		{
			$listKdBrg[$dPro['kodebarang']]=$dPro['kodebarang'];
			$listJumlahRab[$dPro['kodebarang']]=$dPro['jumlah'];
		}
		$iGud="select * from ".$dbname.".log_transaksi_vw where kodeblok='".$kode."' and post='1' ";
		$nGud=mysql_query($iGud) or die (mysql_error($conn));
		while($dGud=mysql_fetch_assoc($nGud))
		{
			$listKdBrg[$dGud['kodebarang']]=$dGud['kodebarang'];
			$listJumlahPakai[$dGud['kodebarang']]=$dGud['jumlah'];
		}	
		if(!empty($listKdBrg))foreach($listKdBrg as $kdBarang)
		{
			$no+=1;
			setIt($listJumlahRab[$kdBarang],0);
			setIt($listJumlahPakai[$kdBarang],0);
			$selisih[$kdBarang]=$listJumlahRab[$kdBarang]-$listJumlahPakai[$kdBarang];
			$stream.="<tr>
						<td>".$no."</td>
						<td>".$kdBarang."</td>
						<td>".$nmBrg[$kdBarang]."</td>
						<td>".$satBrg[$kdBarang]."</td>
						<td>".$listJumlahRab[$kdBarang]."</td>
						<td>".$listJumlahPakai[$kdBarang]."</td>
						<td>".$selisih[$kdBarang]."</td>
					</tr>";	
		}
		$nop_="Laporan_Material_".$dHead['kode'];
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}
	break;        
        
	case'saveFormBarang':
		$i="INSERT INTO ".$dbname.".`project_material` (`kodeproject`, `kodekegiatan`, `kodebarang`, `jumlah`, `updateby`) 
		 	values('".$kodeproject."','".$kodekegiatan."','".$kodeBarangForm."','".$jumlahBarangForm."','".$_SESSION['standard']['userid']."')";
		if(mysql_query($i))
        {
        }
        else
        {
            echo " Gagal,".addslashes(mysql_error($conn));
        }
	break;	
		
		
	case 'deleteMaterial':
	//exit("Error:hahaha");
		$i="DELETE FROM ".$dbname.".`project_material` WHERE `kodeproject` = '".$kodeproject."' AND `kodekegiatan` = '".$kegiatan."' AND `kodebarang`= '".$kodebarang."'";
		//exit("Error.$i");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;	

	case'getListBarang':
	//exit("Error:MASUK");
		echo"
			<fieldset>
			<legend>".$_SESSION['lang']['form']." Utama</legend>
				<fieldset  style='float:left;' >
					<legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
						<table cellspacing=1 border=0 class=data>
						
							<tr>
								<td colspan=2>".$_SESSION['lang']['namabarang']."</td>
								
								<td colspan=5>: 
									<input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
									<button class=mybutton onclick=cariListBarang('".$kegiatan."','".$kodeproject."')>cari</button>
								<td>
							<tr>
							</table>
							
							<table id=listCariBarang >
							<thead>
							<tr class=rowheader>
								<td>No</td>
								<td>".$_SESSION['lang']['kodebarang']."</td>
								<td>".$_SESSION['lang']['namabarang']."</td>
								<td>".$_SESSION['lang']['satuan']."</td>
							</tr></thead>";
							
						if($namaBarangCari=='')
						{
						}
						else
						{
						$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
						//echo $i;
						$n=mysql_query($i) or die (mysql_error($conn));
						while ($d=mysql_fetch_assoc($n))
						{
						$no+=1;
						echo"
							<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."');\">
								<td>".$no."</td>
								<td>".$d['kodebarang']."</td>
								<td>".$nmBrg[$d['kodebarang']]."</td>
								<td>".$satBrg[$d['kodebarang']]."</td>
							</tr>";
						}
						}
						echo"</table>
					</fieldset>
					
					
					<fieldset>
					<legend>".$_SESSION['lang']['form']."</legend>
						<table cellspacing=1 border=0>
							<tr>
								<td>".$_SESSION['lang']['project']."</td>
								<td>:</td>
								<td><input type=text id=kodeproject disabled value='".$kodeproject."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['kodekegiatan']."</td>
								<td>:</td>
								<td><input type=text id=kodekegiatan disabled value='".$kegiatan."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['kodebarang']."</td>
								<td>:</td>
								<td>
									<input type=text id=kodeBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['namabarang']."</td>
								<td>:</td>
								<td><input type=text id=namaBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['satuan']."</td>
								<td>:</td>
								<td><input type=text id=satuanBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['jumlah']."</td>
								<td>:</td>
								<td><input type=text id=jumlahBarangForm class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
							</tr>
							
							<tr>
								<td>
									<button class=mybutton onclick=saveFormBarang('".$kegiatan."','".$kodeproject."','".$_SESSION['lang']['find']."',event)>Simpan</button>
									<button class=mybutton onclick=cancelFormBarang('".$kegiatan."','".$kodeproject."','".$_SESSION['lang']['find']."',event)>Hapus</button>
									<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['selesai']."</button>
								</td>
							</tr>
						</table>
					</fieldset>	
				</fieldset>
				
		<fieldset>
		<legend>".$_SESSION['lang']['datatersimpan']."</legend>
		<table cellspacing=1 border=0 class=data>
		<thead>
			<tr class=rowheader>
				<td>No</td>
				<td>".$_SESSION['lang']['project']."</td>
				<td>".$_SESSION['lang']['namakegiatan']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['dibuat']."</td>
				<td>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		</tbody>";
		
		$i="select * from ".$dbname.".project_material where kodekegiatan='".$kegiatan."'";
		//echo $i;
		$n=mysql_query($i) or die (mysql_error($conn));
		$noData=0;
		while ($d=mysql_fetch_assoc($n))
		{
			$noData+=1;
		echo"
			<tr class=rowcontent>
				<td>".$noData."</td>
				<td>".$d['kodeproject']."</td>
				<td>".$optNmKegBrg[$d['kodekegiatan']]."</td>
				<td>".$d['kodebarang']."</td>
				<td>".$nmBrg[$d['kodebarang']]."</td>
				<td align=right>".$d['jumlah']."</td>
				<td>".$satBrg[$d['kodebarang']]."</td>
				<td>".$nmKar[$d['updateby']]."</td>
				
				<td>
					<img src=images/application/application_delete.png class=resicon  caption='Delete' 
					onclick=\"delMaterial('".$d['kodeproject']."','".$d['kodekegiatan']."','".$d['kodebarang']."');\">
				</td>
			</tr>";
		}
		echo "</table></fieldset>";
	
	break;        
    default:
    break;					
}