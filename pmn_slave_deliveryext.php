<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kode=checkPostGet('kode','');
$kodeorg=checkPostGet('kodeorg','');
$nokontrakext=checkPostGet('kode','');
//$kodedetail=checkPostGet('kodedetail','');
$kodecustomer=checkPostGet('kodecustomer','');
$kodebarang=checkPostGet('kodebarang','');
$method=checkPostGet('method','');
$nokontrakextdet=checkPostGet('nokontrakextdet','');
$nokontrakdet=checkPostGet('nokontrakdet','');
//$nosipbdet=checkPostGet('nosipbdet','');
$tanggaldet=tanggalsystem(checkPostGet('tanggaldet',''));
$beratbersihdet=checkPostGet('beratbersihdet','');
$notransaksidet=checkPostGet('notransaksidet','');
//$keterangandet=checkPostGet('keterangandet','');
$kdUnitCr=checkPostGet('kdUnitCr','');
$kdCustCr=checkPostGet('kdCustCr','');
$kdBrgCr=checkPostGet('kdBrgCr','');
$noKontrakCr=checkPostGet('noKontrakCr','');
?>
<?php
switch($method){
	case'loadHead':
		$whrd="";
		if($kdUnitCr!=''){
			$whrd.=" and a.kodeorg='".$kdUnitCr."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$whrd.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe<>'HOLDING' and detail='1')";
			}else{
				if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
					$whrd.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
				}
			}
		}
		if($kdCustCr!=''){
			$whrd.=" and a.kodecustomer='".$kdCustCr."'";
		}
		if($kdBrgCr!=''){
			$whrd.=" and a.kodebarang='".$kdBrgCr."'";
		}
		if($noKontrakCr!=''){
			$whrd.=" and a.nokontrakext like '%".$noKontrakCr."%'";
		}
		echo"
			<table class=sortable cellspacing=1 border=0>
			<tr class=rowheader bgcolor='#275275' style='color:#9CCCC9;'>
				<td>No</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>".$_SESSION['lang']['vendor']."</td>
				<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td align=right>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['action']."</td>    
			</tr>";

		$limit=20;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_traderht a where true ".$whrd." ";
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		$str="select a.*,b.namabarang,c.namacustomer from ".$dbname.".pmn_traderht a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang 
				left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer where true ".$whrd."
				order by a.tanggalext desc,a.nokontrakext
				limit ".$offset.",".$limit." ";
        //exit('Warning : '.$str);
		$no=$maxdisplay;
		$res=mysql_query($str);
		$oow=mysql_num_rows($res);
		if($oow==0){
			echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
		}else{
			while($bar=mysql_fetch_assoc($res)){
				$no+=1;
				echo"<tr class=rowcontent>
						<td>".$no."</td>
						<td>".$bar['kodeorg']."</td>
						<td>".$bar['kodecustomer']."</td>
						<td>".$bar['nokontrakext']."</td>
						<td>".$bar['tanggalext']."</td>
						<td>".$bar['namabarang']."</td>
						<td align=right>".number_format($bar['qtykontrak'],0)."</td>
						<td align=center>
							<img src=images/application/application_view_detail.png class=resicon  title='Detail' onclick=\"loadData('".$bar['nokontrakext']."','".$bar['kodeorg']."','".$bar['kodecustomer']."','".$bar['kodebarang']."');\">
						</td>
                    </tr>";	
			}
		}
		echo"<tr class=rowheader>
				<td colspan=8 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".$jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>
		</table>";

	break;

	case'loadData':
		if ($nokontrakext==''){
			$nokontrakext=$kodedetail;
		}
		echo"
		<table class=sortable cellspacing=1 border=0>
			<thead>
				<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
					<td align=center>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['pengiriman'].' '.$_SESSION['lang']['eksternal']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>Tonage</td>
					<td align=center>Action</td>
				</tr>
			</thead>
			<tbody>";

		//Pilih Kontrak
		$i="select nokontrak from ".$dbname.".pmn_kontrakjual where kodebarang='".$kodebarang."' order by tanggalkontrak desc";
		$optKontrak="<option value=''></option>";
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$optKontrak.="<option value='".$d['nokontrak']."'>".$d['nokontrak']."</option>";
		}
		$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail='1' and tipe='PABRIK' order by kodeorganisasi";
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$optKontrak.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
		}

		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_traderdt where nokontrakext='".$nokontrakext."'";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		$ha="select * from ".$dbname.". pmn_traderdt where nokontrakext='".$nokontrakext."' order by tanggal";
		$hi=mysql_query($ha) or die(mysql_error());
		while($hu=mysql_fetch_assoc($hi)){
			$no+=1;
			/*
			echo "
			<tr class=rowcontent>
				<td>".$no."</td>
				<td>".$hu['nokontrakext']."</td>
				<td>".$hu['nokontrak']."</td>
				<td>".tanggalnormal($hu['tanggal'])."</td>
				<td align=right>".number_format($hu['beratbersih'],0)."</td>
				<td>
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldetail('".$hu['nokontrakext']."','".$kodeorg."','".$kodecustomer."','".$kodebarang."','".$hu['notransaksi']."');\" >
				</td>
			</tr>";
			*/
			echo "
			<tr class=rowcontent>
				<td>".$no."</td>
				<td><input type=text maxlength=30 id=nokontrakextdet".$no." value='".$hu['nokontrakext']."' disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:180px;\"></td>
				<td><input type=text maxlength=30 id=nokontrakdet".$no." value='".$hu['nokontrak']."' disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:180px;\"></td>
				<td><input type='text' class='myinputtext' id=tanggaldet".$no." value=".tanggalnormal($hu['tanggal'])." disabled onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:60px; /></td>
				<td><input type=text id=beratbersihdet".$no." value=".number_format($hu['beratbersih'],0)." disabled onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";
			if($hu['posting']=='0'){
			echo "
				<td>
					<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editdetail('".$hu['nokontrakext']."','".$kodeorg."','".$kodecustomer."','".$kodebarang."','".$hu['notransaksi']."','".$no."');\" >
					<img src=images/save.png class=resicon title='Save' onclick=\"simpanedit('".$hu['nokontrakext']."','".$kodeorg."','".$kodecustomer."','".$kodebarang."','".$hu['notransaksi']."','".$no."');\" >
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldetail('".$hu['nokontrakext']."','".$kodeorg."','".$kodecustomer."','".$kodebarang."','".$hu['notransaksi']."');\" >
					<img src=images/skyblue/posting.png class=resicon title='Posting' onclick=\"postingdetail('".$hu['nokontrakext']."','".$kodeorg."','".$kodecustomer."','".$kodebarang."','".$hu['notransaksi']."','".$no."');\" >
				</td>";
			}else{
			echo "
				<td>
					<img src=images/skyblue/posted.png class=resicon title='Posted'>
				</td>";
			}
			echo "
			</tr>";
			
		}
		
		echo"<tr class=rowcontent><td></td>
			<td><input type=text maxlength=30 id=nokontrakextdet value='".$nokontrakext."' disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:180px;\"></td>
			<td><select id=nokontrakdet style=width:180px;>".$optKontrak."</select></td>
			<td><input type='text' class='myinputtext' id='tanggaldet' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:60px; /></td>
			<td><input type=text id=beratbersihdet onkeypress=\"return angka_doang(event);\" class=myinputtext style=\"width:50px;\"></td>
			<td><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpandetail('".$nokontrakext."','".$kodeorg."','".$kodecustomer."','".$kodebarang."')></td>
		</tr>";	
		echo"</tbody></table>";
    break;

	case 'simpandetail':
		//Cari Kontrak
		$i="select nodo from ".$dbname.".pmn_suratperintahpengiriman where nokontrak='".$nokontrakdet."'";
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$nosipbdet=$d['nodo'];
		}
		//Cari $notransaksidet
		//$i="select right(notransaksi,2) as maxno from ".$dbname.".pmn_traderdt where nokontrakext='".$nokontrakextdet."' order by right(notransaksi,2) desc limit 1";
		$i="select right(notransaksi,2) as maxno from ".$dbname.".pmn_traderdt where notransaksi like '".substr($kodecustomer,0,4).substr($tanggaldet,0,6)."%' order by right(notransaksi,2) desc limit 1";
		//exit('Warning: '.$i);
		$n=mysql_query($i) or die (mysql_error($conn));
		$notransaksidet=substr($kodecustomer,0,4).substr($tanggaldet,0,6).'01';
		while($d=mysql_fetch_assoc($n)){
			$notransaksidet=substr($kodecustomer,0,4).substr($tanggaldet,0,6).sprintf("%02d",substr($d['maxno'],0,2)+1);
		}
		$str="insert into ".$dbname.".pmn_traderdt (nokontrakext,nokontrak,nosipb,tanggal,beratbersih,notransaksi,keterangan)
		values ('".$nokontrakextdet."','".$nokontrakdet."','".$nosipbdet."','".$tanggaldet."','".$beratbersihdet."','".$notransaksidet."','".$keterangandet."')";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'deldetail':
		//$str="delete from ".$dbname.".pmn_traderdt where nokontrakext='".$nokontrakextdet."' and nokontrak='".$nokontrakdet."' and tanggal='".$tanggaldet."'";
		$str="delete from ".$dbname.".pmn_traderdt where notransaksi='".$notransaksidet."'";
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'simpanedit':
		$str="update ".$dbname.".pmn_traderdt set nokontrak='".$nokontrakdet."',tanggal='".$tanggaldet."',beratbersih='".$beratbersihdet."' where notransaksi='".$notransaksidet."'";
		//exit('Warning : '.$str);
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'postingdetail':
		//Cari Kontrak
		$i="select a.*,b.tanggalext,b.kodecustomer,b.kodebarang,b.kodeorg,b.hargaext*a.beratbersih as jmlharga,c.induk as kodept,d.namabarang,d.satuan
			from ".$dbname.".pmn_traderdt a 
			left join ".$dbname.".pmn_traderht b on b.nokontrakext=a.nokontrakext
			left join ".$dbname.".organisasi c on c.kodeorganisasi=b.kodeorg
			left join ".$dbname.".log_5masterbarang d on d.kodebarang=b.kodebarang
			where a.notransaksi='".$notransaksidet."'";
		//exit('Warning: '.$i);
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$kdorg=$d['kodeorg'];
			//Cari KdOrg
			$smill="SELECT kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk in 
					(select kodept from ".$dbname.".pmn_kontrakjual where nokontrak='".$d['nokontrak']."')";
			$qmill=mysql_query($smill) or die (mysql_error($conn));
			$kodemill='';
			while($dmill=mysql_fetch_assoc($qmill)){
				$kodemill=$dmill['kodeorganisasi'];
			}
			//Lihat Periode Tutup Buku
			$sbuku="select * from ".$dbname.".setup_periodeakuntansi where periode='".substr($d['tanggal'],0,7)."' and kodeorg='".$kdorg."'";
			$qbuku=mysql_query($sbuku) or die (mysql_error($conn));
			$tutupbuku=0;
			while($dbuku=mysql_fetch_assoc($qbuku)){
				$tutupbuku=$dbuku['tutupbuku'];
			}
			if($tutupbuku==0){
				//$nosipbdet=$d['nodo'];
				$str="insert into ".$dbname.".pabrik_timbangan (notransaksi,tanggal,kodeorg,kodecustomer,kodebarang,nokontrak,intex,nosipb,username,millcode,beratbersih)
				values ('".$d['notransaksi']."','".$d['tanggal']."','".$kdorg."','".$kodecustomer."','".$d['kodebarang']."','".$d['nokontrak']."','0','".$d['nosipb']."','".$_SESSION['standard']['username']."','".$kdorg."','".$d['beratbersih']."')";
				if(mysql_query($str)){
					if($d['tanggalext']>='2023-01-01'){
						//Get Harga CPO
						// Get Counter Journal
						$kodeJurnal="PMB";
						$qCounter = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodekelompok='".$kodeJurnal."' and kodeorg='".$d['kodept']."'");
						$resCounter = fetchData($qCounter);
						if(empty($resCounter)){
							$strdelt="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$d['notransaksi']."'";
							if(!mysql_query($strdelt)){
								echo " Gagal, menghapus transaksi kirim ".addslashes(mysql_error($conn));
							}
							exit("Warning: Kelompok Jurnal ".$kodeJurnal." untuk PT.".$d['kodept']." belum ada".
								"\nSilahkan hubungi IT dengan melampirkan pesan error ini");
						}
						$counter = $resCounter[0]['nokounter'];
						$nojurnal=date('Ymd',strtotime($d['tanggal']))."/".$d['kodeorg']."/".$kodeJurnal."/".str_pad($counter+1,3,'0',STR_PAD_LEFT);
						//--- Create Jurnal header
						$strjrnh="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi)
						values ('".$nojurnal."','".$kodeJurnal."','".$d['tanggal']."','".$d['tanggal']."','1','".$d['jmlharga']."','".$d['jmlharga']."','0','".$d['nokontrakext']."','0','IDR','1','0')";
						if(!mysql_query($strjrnh)){
							$strdelt="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$d['notransaksi']."'";
							if(!mysql_query($strdelt)){
								echo " Gagal, menghapus transaksi kirim ".addslashes(mysql_error($conn));
							}
							exit(" Gagal, membentuk jurnal header".addslashes(mysql_error($conn)));
						}
						//--- Create Jurnal detail
						$qjurnalid = selectQuery($dbname,'keu_5parameterjurnal','jurnalid,noakundebet,noakunkredit',"jurnalid='".$kodeJurnal."'");
						$resjurnalid = fetchData($qjurnalid);
						$noakundebet=$resjurnalid[0]['noakundebet'];
						$noakunkredit=$resjurnalid[0]['noakunkredit'];
						if($d['kodebarang']=='40000001'){
							//$noakun="6420500";
							$noakun=$noakundebet;
						} else if($d['kodebarang']=='40000002'){
							$noakun="6430401";
						} else if($d['kodebarang']=='40000003'){
							$noakun="6430800";
						}
						$strjrnd1="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodebarang,kodecustomer,noreferensi,nodok,revisi,kodesegment) values ('".$nojurnal."','".$d['tanggal']."','1','".$noakun."','Pembelian ".$d['namabarang']." ".$d['beratbersih']." ".$d['satuan']."'
						,'".$d['jmlharga']."','IDR','1','".$kdorg."','".$d['kodebarang']."','".$d['kodecustomer']."','".$d['nokontrakext']."','".$d['nokontrakext']."','0','1')";
						if(!mysql_query($strjrnd1)){
							$strdelj="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
							if(!mysql_query($strdelj)){
								echo " Gagal, menghapus transaksi jurnal ".addslashes(mysql_error($conn));
							}
							$strdelt="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$d['notransaksi']."'";
							if(!mysql_query($strdelt)){
								echo " Gagal, menghapus transaksi kirim ".addslashes(mysql_error($conn));
							}
							exit(" Gagal, membentuk jurnal detail 1".addslashes(mysql_error($conn)));
						}
						$strjrnd2="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodebarang,kodecustomer,noreferensi,nodok,revisi,kodesegment) values ('".$nojurnal."','".$d['tanggal']."','2','".$noakunkredit."','Pembelian ".$d['namabarang']." ".$d['beratbersih']." "
						.$d['satuan']."','".($d['jmlharga']*-1)."','IDR','1','".$kdorg."','".$d['kodebarang']."','".$d['kodecustomer']."','"
						.$d['nokontrakext']."','".$d['nokontrakext']."','0','1')";
						if(!mysql_query($strjrnd2)){
							$strdelj="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
							if(!mysql_query($strdelj)){
								echo " Gagal, menghapus transaksi jurnal ".addslashes(mysql_error($conn));
							}
							$strdelt="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$d['notransaksi']."'";
							if(!mysql_query($strdelt)){
								echo " Gagal, menghapus transaksi kirim ".addslashes(mysql_error($conn));
							}
							exit(" Gagal, membentuk jurnal  detail 2".addslashes(mysql_error($conn)));
						}
						$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1),
								"kodeorg='".$d['kodept']."' and kodekelompok='".$kodeJurnal."'");
						if(!mysql_query($queryJ)) {
							echo "Update Counter Parameter Jurnal Error :".mysql_error()."\n";
						}
					}
					//update posting flag
					$strup="update ".$dbname.".pmn_traderdt set posting='1' where notransaksi='".$d['notransaksi']."'";
					if(!mysql_query($strup)){
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}else{
				exit('Warning : Periode Tanggal Sudah tutup Buku...!');
			}
		}
	break;

	default:
}
?>
