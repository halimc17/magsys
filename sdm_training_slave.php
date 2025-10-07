<?php
	session_start();
	require_once('master_validation.php');
	require_once('config/connection.php');
	include_once('lib/nangkoelib.php');

	$proses=isset($_POST['proses'])? $_POST['proses']: '';
	$proses=($proses=='' ? $_GET['proses'] : $proses);
	$idkary=isset($_POST['idkary']) ? $_POST['idkary'] : '';
	$idkary=($idkary=='' ? $_GET['idkary'] : $idkary);
	$nmkary=isset($_POST['nmkary']) ? $_POST['nmkary'] : '';
	$nmkary=($nmkary=='' ? $_GET['nmkary'] : $nmkary);
	$nomor =isset($_POST['nomor']) ? $_POST['nomor'] : '';
	$nomor =($nomor=='' ? $_GET['nomor'] : $nomor);

	$judultraining=	isset($_POST['judultraining'])? $_POST['judultraining']: '';
	$jenistraining=	isset($_POST['jenistraining'])? $_POST['jenistraining']: '';
	$tanggal1=		isset($_POST['tanggal1'])? $_POST['tanggal1']: '';
	$tanggal2=		isset($_POST['tanggal2'])? $_POST['tanggal2']: '';
	$penyelenggara=	isset($_POST['penyelenggara'])? $_POST['penyelenggara']: '';
	$sertifikat=	isset($_POST['sertifikat'])? $_POST['sertifikat']: '';
	$biayatraining=	isset($_POST['biayatraining'])? $_POST['biayatraining']: '';

	$judultraining	=($judultraining=='' ? $_GET['judultraining'] : $judultraining);
	$jenistraining	=($jenistraining=='' ? $_GET['jenistraining'] : $jenistraining);
	$tanggal1		=($tanggal1=='' ? $_GET['tanggal1'] : $tanggal1);
	$tanggal2		=($tanggal2=='' ? $_GET['tanggal2'] : $tanggal2);
	$penyelenggara	=($penyelenggara=='' ? $_GET['penyelenggara'] : $penyelenggara);
	$sertifikat		=($sertifikat=='' ? $_GET['sertifikat'] : $sertifikat);
	$biayatraining	=($biayatraining=='' ? $_GET['biayatraining'] : $biayatraining);

	$tanggal1=tanggalsystem($tanggal1);
	$tanggal2=tanggalsystem($tanggal2);
	$biayatraining	=($biayatraining=='' ? 0 : $biayatraining);

	$berlakudari=	isset($_POST['berlakudari'])? $_POST['berlakudari']: '';
	$berlakusampai=	isset($_POST['berlakusampai'])? $_POST['berlakusampai']: '';
	$berlakudari=tanggalsystem($berlakudari);
	$berlakusampai=tanggalsystem($berlakusampai);
	$scansertifikat=isset($_POST['scansertifikat'])? $_POST['scansertifikat']: '';
	$scansertifikat=($scansertifikat=='' ? $_GET['scansertifikat'] : $scansertifikat);
	$path='scansertifikatkaryawan';

	switch($proses){
		case'getKary':
			$sKary="select a.karyawanid,a.nik,a.namakaryawan,a.lokasitugas,a.tanggallahir,a.tanggalmasuk,a.tanggalkeluar,b.namajabatan 
					from ".$dbname.".datakaryawan a	
					left join ".$dbname.".sdm_5jabatan b on b.kodejabatan=a.kodejabatan
					where a.karyawanid='".$idkary."'";
			$qKary=mysql_query($sKary) or die(mysql_error());
			while($rKary=mysql_fetch_assoc($qKary)){
				$nikary=$rKary['nik'];
				$nmkary=$rKary['namakaryawan'];
				$lkkary=$rKary['lokasitugas'];
				$tlkary=$rKary['tanggallahir'];
				$tmkary=$rKary['tanggalmasuk'];
				$tkkary=$rKary['tanggalkeluar'];
				$jbkary=$rKary['namajabatan'];
			}
			if($tkkary=='0000-00-00' or $tkkary==''){
				$tkkary=date("Y-m-d");
			}
			//Masa kerja
			$diff = abs(strtotime($tkkary) - strtotime($tmkary)); 
			$mkyears = floor($diff / (365*60*60*24)); 
			$mkmonths = floor(($diff - $mkyears * 365*60*60*24) / (30*60*60*24)); 
			$mkdays = floor(($diff - $mkyears * 365*60*60*24 - $mkmonths*30*60*60*24)/ (60*60*24)); 
			//$mkkary=$mkyears." Tahun, ".$mkmonths." Bulan, ".$mkdays." Hari";
			$mkkary=$mkyears." Tahun, ".$mkmonths." Bulan";
			echo $nikary."###".$nmkary."###".$jbkary."###".$lkkary."###".$mkkary;
			break;

		case'LoadData':
			if($idkary!=''){
			$limit=10;
			$page=0;
			if(isset($_POST['page'])){
				$page=$_POST['page'];
				if($page<0)
					$page=0;
			}
			$offset=$page*$limit;
		
			$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_karyawantraining a
				  left join ".$dbname.".sdm_5jenistraining f on f.kodetraining=a.jenistraining
				  where karyawanid='".$idkary."' 
				  order by tanggalselesai desc";
			$query2=mysql_query($ql2) or die(mysql_error());
			while($jsl=mysql_fetch_object($query2)){
				$jlhbrs= $jsl->jmlhrow;
			}
		
			$str="select a.*,f.jenistraining as kategori from ".$dbname.".sdm_karyawantraining a
				  left join ".$dbname.".sdm_5jenistraining f on f.kodetraining=a.jenistraining
				  where karyawanid='".$idkary."' 
				  order by tanggalselesai desc limit ".$offset.",".$limit."";
			//exit('Warning :'.$str);
			if($res=mysql_query($str)){
				$no=0;
				while($bar=mysql_fetch_object($res)){
					$no+=1;
					if($bar->sertifikat=='1'){
						$ketsertifikat=($_SESSION['language']=='EN'?'Attendance Certificate':'Sertifikat Kehadiran');
					}elseif($bar->sertifikat=='2'){
						$ketsertifikat=($_SESSION['language']=='EN'?'Competency Certificate':'Sertifikat Kompetensi');
					}else{
						$ketsertifikat=($_SESSION['language']=='EN'?'No Certificate':'Tidak Ada Sertifikat');
					}
					echo"<tr class=rowcontent id='tr_".$no."'>
							<td align=center>".$no."</td>
							<td>".$bar->judultraining."</td>
							<td>".$bar->kategori."</td>
							<td align=center>".$bar->tanggalmulai."</td>
							<td align=center>".$bar->tanggalselesai."</td>
							<td>".$bar->penyelenggara."</td>
							<td align=center>".$ketsertifikat."</td>
							<td align=right>".number_format($bar->biaya,0)."</td>
							<td>";
					$thbl=(date("Y")-1).'-12';
					if(substr($bar->tanggalselesai,0,7)>=$thbl){
						echo"	<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->nomor."');\">&nbsp
								<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$bar->nomor."');\">&nbsp
								<img src=images/pdf.jpg class=resicon title='Print' onclick=\"printPDF('".$bar->nomor."',event);\">";
					}
					echo "</td><td>";
					if($bar->sertifikat==2){
						echo	"<img class=resicon src='images/skyblue/plus.png' title='Edit Scan' 
								onclick=\"editscan('".$bar->nomor."',event);\">&nbsp";
					}
					if($bar->scansertifikat!=''){
						echo	"<img class=resicon src='images/skyblue/zoom.png' title='View Scan' 
								onclick=\"viewscan('".$bar->nomor."','".$bar->scansertifikat."',event);\">";
					}
					echo"	</td>
						</tr>";
				}
				if($jlhbrs>10){
					echo"<tr>
							<td colspan=8 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".$jlhbrs."<br />
								<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
								<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
							</td>
						</tr>";
				}
			}else{
				echo " Gagal,".(mysql_error($conn));
			}
			}
			break;

		case'insert':
			if(($idkary=='')||($judultraining=='')||($jenistraining=='')||($tanggal1=='')||($tanggal2=='')||($penyelenggara=='')||
				($sertifikat=='')){
				echo"warning: Lengkapi Form Inputan";
				exit();
			}
			$smax="select MAX(nomor) as nmax from ".$dbname.".sdm_karyawantraining";
			$qmax=mysql_query($smax) or die(mysql_error());
			while($rmax=mysql_fetch_assoc($qmax)){
				$nmax= $rmax['nmax']+1;
			}
			$sreset="ALTER TABLE ".$dbname.".sdm_karyawantraining AUTO_INCREMENT=".$nmax."";
			$qreset=mysql_query($sreset) or die(mysql_error());

			//$viewpath=isset($_POST['viewpath'])? $_POST['viewpath']: '';
			//$viewpath=($viewpath=='' ? $_GET['viewpath'] : $viewpath);

			//$fsize=filesize($_FILES['scansertifikat']['tmp_name']);
			//$fbase=basename($_FILES['scansertifikat']['name']);
			$str=explode(".",$scansertifikat);
			$ext=$str[count($str)-1];
			$namafile='';
			if($sertifikat==2){
				//$namafile=$path.'/'.$nmkary.'_'.$fbase.'.'.$ext;
			}
			//exit('Warning: '.$namafile);
			$sIns="insert into ".$dbname.".sdm_karyawantraining
			(karyawanid,jenistraining,tanggalmulai,tanggalselesai,judultraining,penyelenggara,sertifikat
			,biaya,berlakudari,berlakusampai,scansertifikat) values
			('".$idkary."','".$jenistraining."','".$tanggal1."','".$tanggal2."','".$judultraining."','".$penyelenggara."','".$sertifikat
			."','".$biayatraining."','".$berlakudari."','".$berlakusampai."','".$namafile."')";
			if(mysql_query($sIns))
				echo"";
			else
				echo "DB Error : ".mysql_error($conn);
			break;

		case'showData':
			$sql="select * from ".$dbname.".sdm_karyawantraining where nomor='".$nomor."'";
			$query=mysql_query($sql) or die(mysql_error());
			$res=mysql_fetch_assoc($query);
			echo $res['karyawanid']."###".$res['judultraining']."###".tanggalnormal($res['tanggalmulai'])."###".tanggalnormal($res['tanggalselesai'])."###".$res['jenistraining']."###".$res['penyelenggara']."###".$res['sertifikat']."###".$res['biaya']."###".$res['berlakudari']."###".$res['berlakusampai']."###".$res['scansertifikat'];
			break;

		case'update':
			//exit('Warning: '.$proses.' '.$idkary.' '.$penyelenggara.' '.$nomor);
			if(($idkary=='')||($judultraining=='')||($jenistraining=='')||($tanggal1=='')||($tanggal2=='')||($penyelenggara=='')||($sertifikat=='')){
				echo"warning: Lengkapi Form Inputan";
				exit();
			}
			$sUpd="update ".$dbname.".sdm_karyawantraining set judultraining='".$judultraining."', tanggalmulai='".$tanggal1."', tanggalselesai='".$tanggal2."'
					,jenistraining='".$jenistraining."', penyelenggara='".$penyelenggara."', sertifikat='".$sertifikat."',biaya='".$biayatraining."'  
					where nomor='".$nomor."'";
			//exit('Warning: '.$sUpd);
			if(mysql_query($sUpd))
				echo"";
			else
				echo "DB Error : ".mysql_error($conn);
			break;

		case'delData':
			$strk="select scansertifikat from ".$dbname.".sdm_karyawantraining where nomor=".$nomor."";
			$resk=mysql_query($strk);
			while($bark=mysql_fetch_object($resk)){
				$scansertifikatlama=$bark->scansertifikat;
				$sDel="delete from ".$dbname.".sdm_karyawantraining where nomor='".$nomor."'";
				if(mysql_query($sDel)){
					if($scansertifikatlama!=''){
						// Delete File Lama
						if(file_exists($scansertifikatlama)){
							unlink($scansertifikatlama);
						}
					}
				}else{
					echo "DB Error : ".mysql_error($conn);
				}
			}
			break;
	
		case'viewfile':
			$strk="select scansertifikat from ".$dbname.".sdm_karyawantraining where nomor=".$nomor."";
			$resk=mysql_query($strk);
			while($bark=mysql_fetch_object($resk)){
				$viewfile=$bark->scansertifikat;
				//$viewfile=isset($_POST['namafile'])? $_POST['namafile']: '';
				//$viewfile=($viewfile=='' ? $_GET['namafile'] : $viewfile);
				//exit('Warning: '.$viewfile);
				echo" <iframe name=frame id=frame frameborder=0 width=100% height=100% src='".$viewfile."'></iframe><br>";
			}
			break;

		case'pdf':
			echo 'Print Rekomendasi Program Pelatihan ';
			break;

		default:
			break;
	}
?>
