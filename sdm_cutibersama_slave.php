<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
	$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
	if($proses=='getUnit'){
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' order by namaorganisasi";
			}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' and kodeorganisasi not like '%HO' order by namaorganisasi";
			}else{
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
						and detail='1' order by namaorganisasi";
			}
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
				$optUnit.="<option value='HO'>HO ".$_SESSION['lang']['all']."</option>";
			}else{
				$optUnit="";
			}
			while($dUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;
			break;
	}
	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			//exit('Warning : PT tidak boleh kosong...!');
		}
	}
	$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
	$namapt=$optNm[$kodept];
	#Filter parameter where 
	$where="True";
	if($kodept!=''){
		//$where.=" and a.kodeorganisasi='".$kodept."'";
	}
	if($kodeunit!=''){
		$where.=" and left(a.subbagian,4) like '%".$kodeunit."%'";
	}
	if($tanggal1!='' and $tanggal2==''){
		$tanggal2=$tanggal1;
	}
	if($tanggal1=='' and $tanggal2!=''){
		$tanggal1=$tanggal2;
	}
	if($tanggal1!='' and $tanggal2!=''){
		//$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
	}else{
		exit('Warning : Tanggal tidak boleh kosong...!');
	}
	$where.=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>'".$tanggal2."')";

	$whday="True";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$whday.=" and kebun in ('GLOBAL','HOLDING')";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$whday.=" and kebun in ('GLOBAL','KANWIL')";
	}else{
		$whday.=" and kebun in ('GLOBAL','".$_SESSION['empl']['lokasitugas']."')";
	}

	switch($proses){
		case 'getUnit':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' order by namaorganisasi";
			}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 and detail='1' 
						and induk like '".$kodept."%' and kodeorganisasi not like '%HO' order by namaorganisasi";
			}else{
				$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
						and detail='1' order by namaorganisasi";
			}
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
				$optUnit.="<option value='HO'>HO ".$_SESSION['lang']['all']."</option>";
			}else{
				$optUnit="";
			}
			while($dUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;
			break;

		case 'prosescuber':
			$jumlah=checkPostGet('jumlah',0);
			#daftar karyawan ================================================================================
			$strdt="select a.* from ".$dbname.".datakaryawan a 
					where ".$where." and a.tipekaryawan not in (4,5,8)
					order by left(a.subbagian,4),a.karyawanid";
			//exit('Warning: '.$strdt);
			$resdt=mysql_query($strdt);
			$rowdt=mysql_num_rows($resdt);
			$per=date('Y',strtotime($tanggal1));
			$ket="CUTI BERSAMA dari HR";
			while($bardt=mysql_fetch_object($resdt)){
				$krywnId=$bardt->karyawanid;
				$kodeorg=substr($bardt->subbagian,0,4);
				$subbagian=$bardt->subbagian;
				if($kodeorg=='')
					$kodeorg=substr($bardt->subbagian,0,4);
				if($subbagian=='') 
					$subbagian=$kodeorg;

				//insert to cuti
				$str="insert into ".$dbname.".sdm_cutidt 
						(kodeorg,karyawanid,periodecuti,daritanggal,
						sampaitanggal,jumlahcuti,keterangan)
						values('".$kodeorg."',".$krywnId.",'".$per."','".$tanggal1."','".$tanggal2."',".$jumlah.",'".$ket."')";
				//exit('Warning: '.$str);
				if(mysql_query($str)){
					//ambil sum jumlah diambil dan update table cuti header
					$diambil=0;
					$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
							where upper(keterangan) like '%CUTI%'
							and karyawanid=".$krywnId."
							and periodecuti='".$per."'";
					//exit('Warning: '.$strx);
					$resx=mysql_query($strx);
					while($barx=mysql_fetch_object($resx)){
						$diambil=$barx->diambil;
					}
					if($diambil=='')
						$diambil=0;

					$strup="update ".$dbname.".sdm_cutiht set diambil=".$diambil.",sisa=(hakcuti-".$diambil.")	
							where karyawanid=".$krywnId."
							and periodecuti='".$per."'";
					//exit('Warning: '.$strup);
					mysql_query($strup);

					$strijin="insert into ".$dbname.".sdm_ijin 
							(karyawanid,tanggal,keperluan,keterangan,persetujuan1,stpersetujuan1,komenst1,lastupdate,waktupengajuan
							,darijam,sampaijam,jenisijin,hrd,stpersetujuanhrd,periodecuti,jumlahhari,komenst2)
							values(".$krywnId.",now(),'CUTI BERSAMA','CUTI BERSAMA',".$_SESSION['standard']['userid'].",'1','',now(),now()
							,'".$tanggal1."','".$tanggal2."','CUTI',".$_SESSION['standard']['userid'].",'1','".$per."',".$jumlah.",'')";
					//exit('Warning: '.$strijin);
					mysql_query($strijin);
					
					//------------- Start Insert Data Absensi
					$jnsabsensi='CB';
					$rangeTgl = rangeTanggal($tanggal1, $tanggal2);
					foreach($rangeTgl as $val){
						if(substr($kodeorg,2,2)=='HO'){
							$kebun='HOLDING';
						}else if(substr($kodeorg,2,2)=='RO'){
							$kebun='KANWIL';
						}else if(substr($kodeorg,3,1)=='E'){
							$kebun='ESTATE';
						}else if(substr($kodeorg,3,1)=='M'){
							$kebun='MILL';
						}else{
							$kebun=$kodeorg;
						}

						$strLibur="select kebun,tanggal from ".$dbname.".sdm_5harilibur where tanggal='".$val."' and (kebun='GLOBAL' or kebun='".$kebun."' or kebun='".$kodeorg."')";
						$resLibur=mysql_query($strLibur);
						$AdaLibur=mysql_num_rows($resLibur);
						if($AdaLibur>0){
							continue;
						}
						$strAbsen="select kodeorg,tanggal,karyawanid from ".$dbname.".sdm_absensidt where karyawanid = '".$krywnId."' and tanggal='".$val."'";
						$resAbsen=mysql_query($strAbsen);
						$Ada=0;
						while($barAbsen=mysql_fetch_object($resAbsen)){
							$Ada=1;
						}
						if($Ada==1){
							$strAbs = "update ".$dbname.".sdm_absensidt set absensi='".$jnsabsensi."' where karyawanid='".$krywnId."' and tanggal='".$val."'";
						}else{
							$strAbs = "insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,penjelasan,catu,penaltykehadiran,premi,insentif,fingerprint) values ('".$subbagian."','".$val."','".$krywnId."','','".$jnsabsensi."','00:00:00','00:00:00','".$ket."','0','0','0','0','0')";
						}
						mysql_query($strAbs);
					}
					//------------- End Insert Data Absensi
				}
			}
			exit;
			break;
	}

	#hitung jumlah cuti
	$jumlah=0;
	$tglcuti1=date('Y-m-d',strtotime($tanggal1));
	$tglcuti2=date('Y-m-d',strtotime($tanggal2));
	while($tglcuti1<=$tglcuti2){
		if($kodeunit<>''){
			$strada="select distinct periodecuti,keterangan,daritanggal from ".$dbname.".sdm_cutidt 
					where true and periodecuti=left('".$tglcuti1."',4) and keterangan like '%CUTI BERSAMA%' 
					and daritanggal>='".$tglcuti1."' and sampaitanggal<='".$tglcuti1."' and kodeorg='".$kodeunit."'
					order by daritanggal";
		}else{
			$strada="select distinct periodecuti,keterangan,daritanggal from ".$dbname.".sdm_cutidt 
					where true and periodecuti=left('".$tglcuti1."',4) and keterangan like '%CUTI BERSAMA%' 
					and daritanggal>='".$tglcuti1."' and sampaitanggal<='".$tglcuti1."'
					order by daritanggal";
		}
		//exit('Warning: '.$strada);
		$qryada=mysql_query($strada);
		$rowada=mysql_num_rows($qryada);
		if($rowada>0){
			exit('Sudah ada cuti bersama tanggal '.$tglcuti1);
		}
		$strday="select * from ".$dbname.".sdm_5harilibur 
				where ".$whday." and tanggal='".$tglcuti1."'
				order by tanggal";
		$qryday=mysql_query($strday);
		$rowday=mysql_num_rows($qryday);
		//exit('Warning: '.$strday.' row='.$rowday);
		if($rowday==0){
			$jumlah+=1;
		}
		$tglcuti1=date('Y-m-d',strtotime('+1 days',strtotime($tglcuti1)));
	}
	//exit('Warning: tgl='.$tglcuti1.' jml='.$jumlah);

	/*
	$param=array();
	$param['kodept']=$kodept;
	$param['kodeunit']=$kodeunit;
	$param['tanggal1']=$tanggal1;
	$param['tanggal2']=$tanggal2;
	$param['jumlah']=$jumlah;
	*/

	#ambil data Absensi
	$strz="select a.*,b.namaorganisasi,c.tipe
			from ".$dbname.".datakaryawan a 
			left join ".$dbname.".organisasi b on b.kodeorganisasi=left(a.subbagian,4)
			left join ".$dbname.".sdm_5tipekaryawan c on c.id=a.tipekaryawan
			where ".$where." and a.tipekaryawan not in (4,5,8)
			order by left(a.subbagian,4),a.karyawanid";
	//exit('Warning: '.$strz);
	#preview: nampilin header ================================================================================
	$resz=mysql_query($strz);
	$rowz=mysql_num_rows($resz);
	$stream="";
	if($proses=='excel'){
		$stream.="<table class=sortable border=1 cellspacing=1>";
	}else{
		if($rowz!=0){
			$stream.="<button class=mybutton onclick=prosescuber('".$kodept."','".$kodeunit."','".tanggalnormal($tanggal1)."','".tanggalnormal($tanggal2)."','".$jumlah."') id=btnproses>Process</button>";
		}
		$stream.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream.="
			<thead>
				<tr class=rowheader>
					<td align=center style='width:50px;'>No</td>
					<td align=center style='width:70px;'>".$_SESSION['lang']['unit']."</td>
					<td align=center style='width:120px;'>".$_SESSION['lang']['nik']."</td>
					<td align=center style='width:300px;'>".$_SESSION['lang']['namakaryawan']."</td>
					<td align=center style='width:100px;'>".$_SESSION['lang']['tipekaryawan']."</td>
					<td align=center style='width:80px;'>".$_SESSION['lang']['jumlah']."</td>
				</tr>
			</thead>
			<tbody>";
	if($rowz==0){
		$stream.="<tr class=rowcontent>";
		$stream.="	<td colspan=15>Tidak ada Data...!</td>";
		$stream.="</tr>";
	}else{
		$no=0;
		while($barz=mysql_fetch_object($resz)){
			$no+=1;
            $stream.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".substr($barz->subbagian,0,4)."</td>
						<td align=center>".$barz->nik."</td>
						<td align=left>".$barz->namakaryawan."</td>
						<td align=center>".$barz->tipe."</td>
						<td align=center>".$jumlah."</td>
					</tr>";
		}
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul=$_SESSION['lang']['laporan'].' Cuti Bersama';
            if(strlen($stream)>0){
				$stream='<h2>'.$namapt.'<BR>'.$judul.'</h2>'.$stream;
			    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				//	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			    //    gzwrite($gztralala, $stream);
				//    gzclose($gztralala);
				//	echo "<script language=javascript1.2>
				//			window.location='tempExcel/".$nop_.".xls.gz';
				//		  </script>";
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
            }
		break;

		default:
		break;
	}   
	
function prosescuber($param) {
	global $dbname;
	global $conn;
	exit('Warning '.$param['kodept']);
}

?>
