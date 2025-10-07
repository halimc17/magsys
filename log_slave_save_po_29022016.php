<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$supplier_id=	isset($_POST['supplier_id'])? $_POST['supplier_id']: '';
$proses=		isset($_POST['proses'])? $_POST['proses']: '';
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$tgl_po=		isset($_POST['tglpo'])? tanggalsystem($_POST['tglpo']): '';
$sub_total=		isset($_POST['subtot'])? $_POST['subtot']: '';
$disc=			isset($_POST['diskon'])? $_POST['diskon']: '';
$nilai_dis=		isset($_POST['nildiskon'])? $_POST['nildiskon']: '';
$pbbkb=			isset($_POST['pbbkb'])? $_POST['pbbkb']: '';
$npph=			isset($_POST['pph'])? $_POST['pph']: '';
$nppn=			isset($_POST['ppn'])? $_POST['ppn']: '';
$chkppn=		isset($_POST['chkppn'])? $_POST['chkppn']: '';
$tanggl_kirim=	isset($_POST['tgl_krm'])? tanggalsystemd($_POST['tgl_krm']): '';
$lokasi_krm=	isset($_POST['lok_kirim'])? $_POST['lok_kirim']: '';
$cr_pembayaran=	isset($_POST['cara_pembayarn'])? $_POST['cara_pembayarn']: '';
$nilai_po=		isset($_POST['grand_total'])? $_POST['grand_total']: '';
$purchaser=		isset($_POST['purchser_id'])? $_POST['purchser_id']: '';
$lokasi_kirim=	isset($_POST['lokasi_krm'])? $_POST['lokasi_krm']: '';
$persetujuan=	isset($_POST['id_user'])? $_POST['id_user']: '';
$comment=		isset($_POST['cm_hasil'])? $_POST['cm_hasil']: '';
$jmlh_realisasi=isset($_POST['jmlh_realisasi'])? $_POST['jmlh_realisasi']: '';
$jmlh_diminta=	isset($_POST['jmlh_diminta'])? $_POST['jmlh_diminta']: '';
$jnopp=			isset($_POST['jnopp'])? $_POST['jnopp']: '';
$jkdbrg=		isset($_POST['jkdbrg'])? $_POST['jkdbrg']: '';
$ketUraian=		isset($_POST['ketUraian'])? $_POST['ketUraian']: '';
$mtUang=		isset($_POST['mtUang'])? $_POST['mtUang']: '';
$Kurs=			isset($_POST['Kurs'])? $_POST['Kurs']: '';
$nmSupplier=	isset($_POST['nmSupplier'])? $_POST['nmSupplier']: '';
$ttd2=			isset($_POST['ttd2'])? $_POST['ttd2']: '';
$ongkirim=		isset($_POST['ongkirim'])? $_POST['ongkirim']: 0;
$stat=			isset($_POST['stat'])? $_POST['stat']: '';
$batal=			isset($_POST['batal'])? $_POST['batal']: '';

if($tanggl_kirim=='----:00' or $tanggl_kirim=='--:00') $tanggl_kirim = "00000000";

switch($proses)
{
	case 'cek_supplier':
		$sql="select * from ".$dbname.".log_5supplier where supplierid='".$supplier_id."'";
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_assoc($query);
		echo $res['rekening'].",";
		echo $res['npwp'];
		break;

	case 'insert':
		if(($supplier_id=='')||($nopo=='')||($disc=='')||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim=='')||($mtUang=='')) {
			exit("warning: Please complete the form");
		}
		
		//cek matauang dan kurs
		if($mtUang!='IDR')
		{
			$Kurs=floatval($Kurs);
			$sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' order by daritanggal desc";
			//exit("Error:".$sGetKurs."__".$Kurs);
			$qGetKurs=mysql_query($sGetKurs) or die(mysql_error());
			$rGetKurs=mysql_fetch_assoc($qGetKurs);
			if($Kurs=='0')
			{
			  exit("Error: Please provide curs corrensponding to currency, curs for ".$rGetKurs['kode']." :".$rGetKurs['kurs']);   
			}
		} else {
			$Kurs=1;
		}

		$awl=0;
		$i=1;
		foreach($_POST['kdbrg'] as $row =>$cntn) {
			$kdbrg=$cntn;
			$b=count($_POST['kdbrg']);
			$nopp=$_POST['nopp'][$row];
			$jmlh_pesan=$_POST['rjmlh_psn'][$row];
			$hrg_satuan=$_POST['rhrg_sat'][$row];
			$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
		   // $mat_uang=$_POST['rmat_uang'][$row];
			$satuan=$_POST['rsatuan_unit'][$row];
			$diskon=($hrg_sblmdiskon*$disc)/100;
			$hrg_diskon=$hrg_sblmdiskon-$diskon;

			$sqjmlh="select selisih,jlpesan,realisasi,purchaser from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
			//echo "warning:".$sqjmlh;exit();
			$qujmlh=mysql_query($sqjmlh) or die(mysql_error());
			$resjmlh=mysql_fetch_assoc($qujmlh);
			$jmlh_pesan=$resjmlh['jlpesan']+$jmlh_pesan;
			if(($jmlh_pesan=='')||($hrg_satuan==''))
			{
				echo "warning: Please complete the form";
				exit();
			}
			if($purchaser!=$resjmlh['purchaser'])
			{
				$purchaser=$resjmlh['purchaser'];
			}

			if($resjmlh['realisasi']<$jmlh_pesan)
			{
				echo "warning : \nTotal requested (".$jmlh_pesan.") to material code ".$kdbrg.".(".$jmlh_pesan.") =
				\nVolum of previous request (".$resjmlh['jlpesan'].")\nVolum on current request (".$_POST['rjmlh_psn'][$row].")
				\nLarger than approved (".$resjmlh['realisasi'].").";
				exit();
			}
		}
		$sKd="select kodeorg from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qKd=mysql_query($sKd) or die(mysql_error());
		$rKdorg=mysql_fetch_assoc($qKd);

		$sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_row($query);
		if(intval($lokasi_kirim)) {
			$field="`idFranco`";
		} else {
			$field="`lokasipengiriman`";
		}
		$thisDate=date('Y-m-d');
		if($nilai_dis=='')
		{
			$nilai_dis=0;
		}
		$Kurs=intval($Kurs);
		if($ongkirim=='') $ongkirim=0;
		
		$strx="update ".$dbname.".log_poht set `kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`pbbkb`='".$pbbkb."',`pph`='".$npph."',`chkppn`='".$chkppn."',`ppn`='".$nppn."',`nilaipo`='".$nilai_po."',`tanggalkirim`='".$tanggl_kirim."',
			  ".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',`purchaser`='".$purchaser."',`lokalpusat`='0',`matauang`='".$mtUang."',`kurs`='".$Kurs."',`persetujuan1`='".$persetujuan."',`hasilpersetujuan1`='1',
			  `tglp1`='".$thisDate."',`statuspo`='2',`persetujuan2`='".$ttd2."',`hasilpersetujuan2`='1',`tglp2`='".$thisDate."',tgledit='".$thisDate."',ongkosangkutan='".$ongkirim."'
			   where nopo='".$nopo."'";
		if(!mysql_query($strx)) {
			exit("Gagal,".(mysql_error($conn)));
		} else {
			foreach($_POST['kdbrg'] as $row =>$isi) {
				$kdbrg=$isi;
				$nopp=$_POST['nopp'][$row];
				$jmlh_pesan=$_POST['rjmlh_psn'][$row];
				$hrg_satuan=$_POST['rhrg_sat'][$row];
				$rongank=str_replace(',','',$_POST['rongank'][$row]);

				$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
				$satuan=$_POST['rsatuan_unit'][$row];
				$diskon=($hrg_sblmdiskon*$disc)/100;
				$hrg_diskon=$hrg_sblmdiskon-$diskon;
				$hrgSat=$hrg_diskon+($rongank/$jmlh_pesan);
				$spekBrg=$_POST['spekBrg'][$row];
				$sqjmlh="select selisih,jlpesan,realisasi from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
				$qujmlh=mysql_query($sqjmlh) or die(mysql_error());
				$resjmlh=mysql_fetch_assoc($qujmlh);
				if($rongank=='') $rongank=0;
				
				$sql="update ".$dbname.".log_podt set `jumlahpesan`='".$jmlh_pesan."',`harganormal`='".$hrg_diskon."',`nopp`='".$nopp."',
					  `hargasbldiskon`='".$hrg_sblmdiskon."',`satuan`='".$satuan."',`catatan`='".$spekBrg."',`hargasatuan`='".$hrgSat."',`ongkangkut`='".$rongank."'
					  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
				if(!mysql_query($sql)) {
					echo $sql."-----";
					echo "Gagal,".(mysql_error($conn));exit();
				}
				$supp="update ".$dbname.".log_prapoht set `nopo`='".$nopo."' where nopp='".$nopp."'";
				if(mysql_query($supp)) {
					echo"";
				} else {
					exit("Gagal,".(mysql_error($conn)));
				}
				
				$sdpp="update ".$dbname.".log_prapodt set `create_po`='1' where `nopp`='".$nopp."' and `kodebarang`='".$kdbrg."'";	
				if(mysql_query($sdpp)) {
					echo"";
				} else {
					echo "Gagal,".$sdpp."__".(mysql_error($conn));exit();
				}
			}
		}
		break;
	
	case 'update_data' :
		// <td>".$_SESSION['lang']['tgl_kirim']."</td>
		echo "<table cellspacing='1' border='0' class='sortable'>
			<thead>
				<tr class=rowheader>
					<td>No</td>
					<td>".$_SESSION['lang']['nopo']."</td>
					<td>".$_SESSION['lang']['namasupplier']."</td>
					<td>".$_SESSION['lang']['tgl_po']."</td>
					<td>".$_SESSION['lang']['syaratPem']."</td>
					<td>".$_SESSION['lang']['status']."</td>
					<td>action</td>
				</tr>
			</thead><tbody>";
		
		$txt_search='';
		$txt_tgl='';
		if(isset($_POST['txtSearch'])) {
			$txt_search=$_POST['txtSearch'];
			$txt_tgl = "";
			if(!empty($_POST['tglCari'])) {
				$txt_tgl=tanggalsystem($_POST['tglCari']);
				$txt_tgl_t=substr($txt_tgl,0,4);
				$txt_tgl_b=substr($txt_tgl,4,2);
				$txt_tgl_tg=substr($txt_tgl,6,2);
				$txt_tgl=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
			}
		}
		$where = "";
		if(!empty($txt_search)) {
			$where .= " and nopo LIKE  '%".$txt_search."%'";
		}
		if(!empty($txt_tgl)) {
			$where.=" and tanggal LIKE '".$txt_tgl."'";
		}
		
		$limit=20;
		$page=0;
		if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		if($_SESSION['empl']['kodejabatan']=='5') {
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' ".$where." order by tanggal desc ";
			$sql="select * from ".$dbname.".log_poht where lokalpusat='0' ".$where." order by tanggal desc limit ".$offset.",".$limit."";
		} else {
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."' ".$where." order by tanggal desc ";
			$sql="select * from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."' ".$where." order by tanggal desc limit ".$offset.",".$limit."";
		}
		$query2=mysql_query($sql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		$no=0;
		$query=mysql_query($sql) or die(mysql_error());
		while ($res = mysql_fetch_object($query)) {
			$no+=1;
			$sql2="select * from ".$dbname.".log_5supplier where supplierid='".$res->kodesupplier."'";
			$query2=mysql_query($sql2) or die(mysql_error());
			$res2=mysql_fetch_object($query2);

			$skry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res->purchaser."'";// echo $skry;
			$qkry=mysql_query($skry) or die(mysql_error());
			$rkry=mysql_fetch_assoc($qkry);
			
			$stdt="select * from ".$dbname.".log_transaksidt where nopo='".$res->nopo."'";
			$qtdt=mysql_query($stdt) or die(mysql_error());
			$numrowtdt = mysql_num_rows($qtdt);
			
			$skeu="select * from ".$dbname.".keu_tagihanht where nopo='".$res->nopo."'";
			$qkeu=mysql_query($skeu) or die(mysql_error());
			$numrowkeu = mysql_num_rows($qkeu);
			
			$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$res->syaratbayar."'";
			$qSyp=mysql_query($sSyp) or die(mysql_error($conn));
			$rSyp=mysql_fetch_object($qSyp);

			if($res->stat_release==0) {
				$stat_po=$_SESSION['lang']['un_release_po'];
			} elseif($res->stat_release==1) {
				$stat_po=$_SESSION['lang']['release_po'];
			} elseif($res->stat_release==2) {
				$stat_po="<a href=# onclick=getKoreksi('".$res->nopo."')>".$_SESSION['lang']['koreksi']."</a>";
			}
			// <td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".tanggalnormal($res->tanggalkirim)."</td>
			echo"
			<tr class=rowcontent>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".$no."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".$res->nopo."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".(isset($res2->namasupplier)? $res2->namasupplier: '')."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".tanggalnormal($res->tanggal)."</td>";
			echo "<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >";
			if(isset($rSyp->keterangan)) echo $rSyp->keterangan." (".$rSyp->jenis.")";
			echo "</td>";
			echo "<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".$stat_po."</td> ";

			if(($res->purchaser==$_SESSION['standard']['userid'])||($_SESSION['empl']['kodejabatan']=='5')) {
				if($res->stat_release!=1 && $numrowtdt==0 && $numrowkeu==0) {	
					echo"<td ".($res->stat_release==2?"bgcolor='orange'":"")."><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res->nopo."','".tanggalnormal($res->tanggal)."','".$res->kodesupplier."','".$res->subtotal."','".$res->diskonpersen."','".$res->pbbkb."','".$res->pph."','".$res->chkppn."','".$res->ppn."','".$res->nilaipo."','".(isset($res2->rekening)? $res2->rekening: '')."','".(isset($res2->npwp)? $res2->npwp: '')."','".$res->nilaidiskon."','".$stat."','".tanggalnormal($res->tanggalkirim)."','".$res->matauang."','".$res->kurs."','".$res->persetujuan1."','".$res->idFranco."','".$res->persetujuan2."','".$res->ongkosangkutan."');\">";
					echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"alasan_batal('".$res->nopo."','".$res->stat_release."');\" >
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
					</td></tr>";
				} else {
					echo"<td ".($res->stat_release==2?"bgcolor='orange'":"")."><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\"></td></tr>";
				}
			} else {
				echo"<td ".($res->stat_release==2?"bgcolor='orange'":"").">
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
					</td></tr>";
			}
		}
		echo "<tr><td colspan=9 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
			<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='' />"; 
		echo"</tbody> </table>";
		break;
	
	case 'edit_po':
		$tglSkrng=date("Y-m-d");
		if(($supplier_id=='')||($nopo=='')||($disc==''))
		{
				echo"warning:Please Complete The Form";
				exit();
		}
		//cek matauang dan kurs
		if($mtUang!='IDR')
		{
			$sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl_po."' order by daritanggal desc";
			//exit("Error:".$sGetKurs."__".$Kurs);
			$qGetKurs=mysql_query($sGetKurs) or die(mysql_error());
			$rGetKurs=mysql_fetch_assoc($qGetKurs);
			if($Kurs<$rGetKurs['kurs'])
			{
			   exit("Error: Please provide curs corrensponding to currency, curs for ".$rGetKurs['kode']." :".$rGetKurs['kurs']);   
			}
		}
		else
		{
			$Kurs=1;
		}


		foreach($_POST['kdbrg'] as $row =>$isi)
		{

				$kdbrg=$isi;
				$nopp=$_POST['nopp'][$row];
				$jmlh_pesan=$_POST['rjmlh_psn'][$row];
				$hrg_satuan=$_POST['rhrg_sat'][$row];
				$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
				
				$_POST['rmat_uang'][$row] = "IDR";
				$_POST['rongank'][$row] = 0;
				
				$diskon=($hrg_sblmdiskon*$disc)/100;
				$hrg_diskon=$hrg_sblmdiskon-$diskon;
				$mat_uang=$_POST['rmat_uang'][$row];
				$satuan=$_POST['rsatuan_unit'][$row];
				$spekBrg=$_POST['spekBrg'][$row];
				$rongank=str_replace(',','',$_POST['rongank'][$row]);
				$rongank==''?$rongank=0:$rongank=$rongank;
				$hrgSat=$hrg_diskon+$rongank;
				if(($jmlh_pesan=='')||($hrg_satuan=='')||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim==''))
				{
						echo "warning: Please complete the form";
						exit();
				}
		else
		{
		$scek="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rcek=mysql_fetch_assoc($qcek);
		if($rcek['stat_release']==1)
		{
				echo"warning : PO : ".$nopo." has been released";
				exit();
		}

				if(intval($lokasi_kirim))
				{
				$field="`idFranco`";
				}
				else
				{
				$field="`lokasipengiriman`";
				}

				$strx="update ".$dbname.".log_poht set 
				`kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',tgledit='".$tglSkrng."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`pbbkb`='".$pbbkb."',`pph`='".$npph."',`chkppn`='".$chkppn."',`ppn`='".$nppn."',`nilaipo`='".$nilai_po."',
				`tanggalkirim`='".$tanggl_kirim."',".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',matauang='".$mtUang."',kurs='".$Kurs."',
				 persetujuan1='".$persetujuan."',persetujuan2='".$ttd2."',ongkosangkutan='".$ongkirim."'
				 where nopo='".$nopo."'";
				//echo "warning:".$strx; exit();
				if(!mysql_query($strx))
				{
						//echo $sqp; 
						echo "Gagal,".(mysql_error($conn));exit();
				}
				else
				{

						foreach($_POST['kdbrg'] as $row =>$isi)
						{

								$kdbrg=$isi;
								$nopp=$_POST['nopp'][$row];
								$jmlh_pesan=$_POST['rjmlh_psn'][$row];
																$hrg_satuan=$_POST['rhrg_sat'][$row];
																$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
								$diskon=($hrg_sblmdiskon*$disc)/100;
								$hrg_diskon=$hrg_sblmdiskon-$diskon;
								$mat_uang=$_POST['rmat_uang'][$row];
								$satuan=$_POST['rsatuan_unit'][$row];
								$spekBrg=$_POST['spekBrg'][$row];
								$rongank=str_replace(',','',$_POST['rongank'][$row]);
								$hrgSat=$hrg_diskon+($rongank/$jmlh_pesan);
								$sql="update ".$dbname.".log_podt 
									  set `jumlahpesan`='".$jmlh_pesan."',`hargasatuan`='".$hrgSat."',`matauang`='".$mat_uang."',`hargasbldiskon`='".$hrg_sblmdiskon."',
									  `satuan`='".$satuan."',catatan='".$spekBrg."',harganormal='".$hrg_diskon."',`ongkangkut`='".$rongank."'
									  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
										//echo "warning:".$sql; exit();
										if(!mysql_query($sql))
										{
												//echo $sqp; 
												echo "Gagal,".(mysql_error($conn));exit();
										}	
										else
										{
										   // $sCek="select distinct create_po from ".$dbname.".log_prapodt where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
										   // $qCek=mysql_query($sCek) or die(mysql_error());
										   // $rCek=mysql_fetch_assoc($qCek);
										   // if($rCek['create_po']==''||$rCek['create_po']=='0')
										   // {
												$sUpdate="update ".$dbname.".log_prapodt set create_po=1 where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
												if(!mysql_query($sUpdate))
												{
												echo "Gagal,".(mysql_error($conn));exit();
												}
											//}
										}
						}
				}
		 }
		}
		break;
		
	case 'get_alasan_batal':
		$s_form="select * from ".$dbname.".log_poht where nopo='".$nopo."' ";
		$q_from=mysql_query($s_form) or die (mysql_error($conn));
		$r_form=mysql_fetch_assoc($q_from);
		echo "<div id=form_batal><fieldset><legend>".$nopo."</legend>
				<table cellspacing=1 border=0>
				<tr><td><textarea rows=5 cols=34 id='batal'></textarea></td>
				<td><button class=mybutton id=hapus onclick=delPo('".$nopo."','".$stat."','".$_POST['batal']."')>";echo $_SESSION['lang']['save'];
		echo"</button></td></tr></table></filedset></div>";
		break;

	case 'delete_all':
		$scek="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rPO=mysql_fetch_assoc($qcek);          
		if($rPO['stat_release']==1)
		{
				echo"warning : PO : ".$nopo." being on correction progress";
				exit();
		}
		else
		{
			$sCekGdng="select distinct nopo from ".$dbname.".log_transaksi_vw where nopo='".$nopo."'";
			$qCekGdng=mysql_query($sCekGdng) or die(mysql_error($conn));
			//exit("Error:".$sCekGdng);
			$rCekGdng=mysql_num_rows($qCekGdng);
			if($rCekGdng>0)
			{
				exit("Error: PO : ".$nopo." has been receipt in warehouse, could not be deleted");
			}

			$sListPP="select distinct nopp,kodebarang from ".$dbname.".log_podt where nopo='".$nopo."'";
			$qListPP=mysql_query($sListPP) or die(mysql_error());
			$row=mysql_num_rows($qListPP);
		   $rPO['terbayar']==''?$rPO['terbayar']=0:$rPO['terbayar'];
		   $rPO['tagihandp']==''?$rPO['tagihandp']=0:$rPO['tagihandp'];
		   $rPO['persetujuan1']==''?$rPO['persetujuan1']=0:$rPO['persetujuan1'];
		   $rPO['hasilpersetujuan1']==''?$rPO['hasilpersetujuan1']=0:$rPO['hasilpersetujuan1'];
		   $rPO['persetujuan2']==''?$rPO['persetujuan2']=0:$rPO['persetujuan2'];
		   $rPO['hasilpersetujuan2']==''?$rPO['hasilpersetujuan2']=0:$rPO['hasilpersetujuan2'];
		   $rPO['persetujuan3']==''?$rPO['persetujuan3']=0:$rPO['persetujuan3'];
		   $rPO['hasilpersetujuan3']==''?$rPO['hasilpersetujuan3']=0:$rPO['hasilpersetujuan3'];     
		   $rPO['stat_release']==''?$rPO['stat_release']=0:$rPO['stat_release'];
		   $rPO['tglrelease']==''?$rPO['tglrelease']='0000-00-00':$rPO['tglrelease'];
		   $rPO['tglp1']==''?$rPO['tglp1']='0000-00-00':$rPO['tglp1'];
		   $rPO['tglp2']==''?$rPO['tglp2']='0000-00-00':$rPO['tglp2'];
		   $rPO['tglp3']==''?$rPO['tglp3']='0000-00-00':$rPO['tglp3'];
		   $rPO['idFranco']==''?$rPO['idFranco']=0:$rPO['idFranco']=$rPO['idFranco'];
		   $x=0;
			while($rListPP=mysql_fetch_assoc($qListPP))
				{
				$x+=1;
				
					$sUpd="update ".$dbname.".log_prapodt set create_po=0 where kodebarang='".$rListPP['kodebarang']."' and nopp='".$rListPP['nopp']."'";
					mysql_query($sUpd);
					if($x==1){
						$sql_del_po="delete from ".$dbname.".log_poht_del where nopo = '".$nopo."'"; 
						mysql_query($sql_del_po);
						$str1="insert into ".$dbname.".log_poht_del(nopo,tanggal,tgledit,kodesupplier,subtotal,ongkosangkutan,diskonpersen,
								nilaidiskon,ppn,nilaipo,syaratbayar,uraian,tanggalkirim,lokasipengiriman,tanggalbayar,carapembayaran,
								terbayar,notransbyr,statuspo,matauang,kurs,invoicedp,idFranco,tagihandp,keterangan,pountuk,purchaser,
								persetujuan1,hasilpersetujuan1,persetujuan2,hasilpersetujuan2,persetujuan3,hasilpersetujuan3,
								kodeorg,lokalpusat,stat_release,useridreleasae,tglrelease,tglp1,tglp2,tglp3,catatanrelease,alasan_batal) 
								values ('".$rPO['nopo']."','".$rPO['tanggal']."','".$rPO['tgledit']."','".$rPO['kodesupplier']."',
								'".$rPO['subtotal']."','".$rPO['ongkosangkutan']."','".$rPO['diskonpersen']."','".$rPO['nilaidiskon']."',
								'".$rPO['ppn']."','".$rPO['nilaipo']."','".$rPO['syaratbayar']."','".$rPO['uraian']."','".$rPO['tanggalkirim']."',
								'".$rPO['lokasipengiriman']."','".$rPO['tanggalbayar']."','".$rPO['carapembayaran']."','".$rPO['terbayar']."',
								'".$rPO['notransbyr']."','".$rPO['statuspo']."','".$rPO['matauang']."','".$rPO['kurs']."','".$rPO['invoicedp']."',
								'".$rPO['idFranco']."','".$rPO['tagihandp']."','".$rPO['keterangan']."','".$rPO['pountuk']."','".$rPO['purchaser']."',
								'".$rPO['persetujuan1']."','".$rPO['hasilpersetujuan1']."','".$rPO['persetujuan2']."','".$rPO['hasilpersetujuan2']."',
								'".$rPO['persetujuan3']."','".$rPO['hasilpersetujuan3']."','".$rPO['kodeorg']."','".$rPO['lokalpusat']."',
								'".$rPO['stat_release']."','".$rPO['useridreleasae']."','".$rPO['tglrelease']."','".$rPO['tglp1']."',
								'".$rPO['tglp2']."','".$rPO['tglp3']."','".$rPO['catatanrelease']."','".$batal."')";
					}else
					{
						$str1="select 1=1";
					}
					   if(mysql_query($str1)){
						   
							$sPOdt="select * from ".$dbname.".log_podt where nopo = '".$nopo."'";
							$qPOdt=mysql_query($sPOdt) or die (mysql_error($conn));
							while($rPOdt=mysql_fetch_assoc($qPOdt)){
									$sql_dt_del="delete from ".$dbname.".log_podt_del where nopo = '".$nopo."'"; 
									mysql_query($sql_dt_del);
									$sql_dt="insert into ".$dbname.".log_podt_del(nopo,kodebarang,jumlahpesan,hargasatuan,ongkangkut,harganormal,
											  nopp,matauang,hargasbldiskon,satuan,catatan)
											  values ('".$nopo."','".$rPOdt['kodebarang']."','".$rPOdt['jumlahpesan']."',
											  '".$rPOdt['hargasatuan']."','".$rPOdt['ongkangkut']."','".$rPOdt['harganormal']."','".$rPOdt['nopp']."',
											  '".$rPOdt['matauang']."','".$rPOdt['hargasbldiskon']."','".$rPOdt['satuan']."','".$rPOdt['catatan']."')";
									if(mysql_query($sql_dt)){
										$sql_del="delete from ".$dbname.".log_poht where nopo = '".$nopo."'"; 
										if(mysql_query($sql_del)){
										}
										else{}
									}
									else{
									}
							}
					  }
					  else{
						  echo "Error".$str1;exit(mysql_error($conn));
						
					  }
					 $row--;
					
				}

		}

		break;

	case 'insert_forward_po' :
		if($persetujuan==$_SESSION['standard']['userid'])
		{
				echo "Warning:  Name cout not be the same as requester name";
		}
		else
		{		
				$tgl=date("Y-m-d");
				$sql="update ".$dbname.".log_poht set persetujuan1='".$persetujuan."',statuspo='2',tglp1='".$tgl."',hasilpersetujuan1='1' where nopo='".$nopo."'";
				//$sql="update ".$dbname.".log_poht set persetujuan1='".$persetujuan."',statuspo='1' where nopo='".$nopo."'";
				//echo "warning".$sql; exit();
				if(!mysql_query($sql))
				{
						echo "Gagal,".(mysql_error($conn));exit();
				}
		}
		break;
	case 'get_form_approval' :
		$sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."' and lokalpusat='0'";
		$query=mysql_query($sql) or die(mysql_error());
		$rCek=mysql_num_rows($query);
		if($rCek>0)
		{
			$rest=mysql_fetch_assoc($query);
			echo"<br />
			<div id=test style=display:block>
			<fieldset>
			<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
			<table cellspacing=1 border=0>
			<tr>
			<td colspan=3>
			Submission for the next verification :</td>
			</tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>:</td>
			<td valign=top>";

			$klq="select namakaryawan,karyawanid,bagian,lokasitugas from ".$dbname.".`datakaryawan` where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and karyawanid!='".$user_id."' and lokasitugas!='' and (kodejabatan<6 or kodejabatan=11) order by namakaryawan asc"; 
			//echo $klq;
			$qry=mysql_query($klq) or die(mysql_error());
			$optPur='';
			while($rst=mysql_fetch_object($qry))
			{
				$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
				$qBag=mysql_query($sBag) or die(mysql_error());
				$rBag=mysql_fetch_assoc($qBag);
				$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
			}

			echo"
				<select id=persetujuan_id name=persetujuan_id>
						$optPur;
				</select></td></tr>
				<tr>
				<td colspan=3 align=center>
				<button class=mybutton onclick=forward_po() title=\"Re-Submission\" >".$_SESSION['lang']['diajukan']."</button>
				<button class=mybutton onclick=cancel_po() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button>
				</td></tr></table><br />
				<input type=hidden name=proses id=proses  />
				</fieldset></div>

				<div id=close_po style=\"display:none;\">	
				<fieldset><legend><input type=text id=snopo name=snopo disabled value='".$nopo."' /></legend>
				<p align=center>Process this PO, Are you sure?</p><br />
				<button class=mybutton onclick=proses_release_po() title=\"Process!\" >".$_SESSION['lang']['approve']."</button>
				<button class=mybutton onclick=cancel_po() title=\"Close\">".$_SESSION['lang']['cancel']."</button>
				</fieldset></div>
				";
		} else {
			echo"warning: Data not recorded";
			exit();
		}
		break;
	
	case 'proses_release_po':
		$sql="update ".$dbname.".log_poht set statuspo='2',hasilpersetujuan1='1' where nopo='".$nopo."'";	
		mysql_query($sql) or die(mysql_error());
		break;
		
	case 'cek_pembuat_po':
		$user_id=$_SESSION['standard']['userid'];
		$skry="select purchaser from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qkry=mysql_query($skry) or die(mysql_error());
		$rkry=mysql_fetch_assoc($qkry);
		if($rkry['purchaser']!=$user_id)
		{
				echo "warning:Please See Your Username";
				exit();
		}
		break;
	
	case'getKurs':
		$sGet="select kurs from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl_po."'";
		$qGet=mysql_query($sGet) or die(mysql_error());
		$rGet=mysql_fetch_assoc($qGet);
		//echo "warning:".$rGet['kurs'];
		if($mtUang=='IDR')
		{
				$rGet['kurs']=1;
		}
		else
		{
				$rGet['kurs']=$rGet['kurs'];
		}
		echo $rGet['kurs'];
		break;
	
	case'getKoreksi':
		$sql="select  catatanrelease from ".$dbname.".log_poht where nopo='".$nopo."' and lokalpusat='0'";
		//echo $sql;
		$query=mysql_query($sql) or die(mysql_error());
		$rCek=mysql_num_rows($query);
		if($rCek>0)
		{
								$rest=mysql_fetch_assoc($query);
								echo"<br />
								<div id=test>
								<fieldset>
								<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
								<table class=sortable border=0 cellspacing=1 width=\"300\">
								<thead><tr class=rowheader><td align=center>".$_SESSION['lang']['koreksi']."</td></tr></thead>
								<tbody>
								<tr class=rowcontent><td align=justify>".$rest['catatanrelease']."</td></tr>
								<tr><td align=center><button class=mybutton onclick=doneKoreksi() title=\"Selesai Koreksi\" >".$_SESSION['lang']['done']."</button>
										<button class=mybutton onclick=cancel_po() title=\"close\">".$_SESSION['lang']['cancel']."</button></td></tr>
								</tbody>
								</table>

										</fieldset></div>
										";
		}
		else
		{
				echo"warning: Data not recorded";
				exit();
		}
		break;
	
	case'updateKoreksi':
		$sUpd="update ".$dbname.".log_poht set stat_release='0' where nopo='".$nopo."'";
		if(!mysql_query($sUpd))
		{
				echo $sUpd."Gagal,".(mysql_error($conn));
		}
		break;
	
	case'getNotifikasi':
		$Sorg="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qOrg=mysql_query($Sorg) or die(mysql_error());
		while($rOrg=mysql_fetch_assoc($qOrg))
		{
		if($_SESSION['empl']['kodejabatan']=='5')
		{
		$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)";
		}
		else
		{
		   $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."' and purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)"; 
//                   exit("error: ".$sList);
		}
		//echo $sList;
		//$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."'  and purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null) group by kodept";
		$qList=mysql_query($sList) or die(mysql_error());
		$rBaros=mysql_num_rows($qList);
			if($rBaros!=0)
			{
				$rList=mysql_fetch_assoc($qList);
				if($rList['jmlhJob']=='')
				{
				$rList['jmlhJob']=0;
				}
					if(isset($_POST['status']) and $_POST['status']==1)
					{
						echo"[".$rOrg['kodeorganisasi']." : ".$rList['jmlhJob']." ]";
					}
					else
					{
						echo"[".$rOrg['kodeorganisasi']." : <a href='#' onclick=\"cek_pp_pt('".$rOrg['kodeorganisasi']."')\">".$rList['jmlhJob']."</a> ]";
					}
			}
		}
		break;
	
	case'getSupplierNm':
			echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
				<div style=\"overflow:auto;height:295px;width:455px;\">
				<table cellpading=1 border=0 class=sortbale>
				<thead>
				<tr class=rowheader>
				<td>No.</td>
				<td>".$_SESSION['lang']['kodesupplier']."</td>
				<td>".$_SESSION['lang']['namasupplier']."</td>
				</tr><tbody>
				";
		 $sSupplier="select namasupplier,supplierid from ".$dbname.".log_5supplier 
					 where namasupplier like '%".$nmSupplier."%' and kodekelompok='S001' and status=1";
		 $qSupplier=mysql_query($sSupplier) or die(mysql_error($conn));
		 while($rSupplier=mysql_fetch_assoc($qSupplier))
		 {
			 $no+=1;
			 echo"<tr class=rowcontent onclick=setData('".$rSupplier['supplierid']."')>
				 <td>".$no."</td>
				 <td>".$rSupplier['supplierid']."</td>
				 <td>".$rSupplier['namasupplier']."</td>
			</tr>";
		 }
			echo"</tbody></table></div>";
		break;
	
	default:
		break;
}