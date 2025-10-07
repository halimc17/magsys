<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

	$notransaksi=checkPostGet('notransaksi','');
	$kodeorg	=checkPostGet('kodeorg','');
    $tanggal	=tanggalsystem(checkPostGet('tanggal',''));
	$trpcode	=checkPostGet('trpcode','');
	$nospp		=checkPostGet('nospp','');
	$jumlahkg	=checkPostGet('jumlahkg','');
	$hargakg	=checkPostGet('hargakg','');
	$jmlharga	=checkPostGet('jmlharga','');

	if(isset($_POST['del'])){
		$strx1="delete from ".$dbname.".log_spkht where notransaksi='".$notransaksi."'";
		if(mysql_query($strx1)){
			$strx2="delete from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."'";
			if(mysql_query($strx2)){
				$strx3="delete from ".$dbname.".log_baspk where notransaksi='".$notransaksi."'";
				if(mysql_query($strx3)){
					//echo"<script>loadData()</script>";
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}else{
				echo " Gagal,".addslashes(mysql_error($conn));
			}
		}else{
			echo " Gagal,".addslashes(mysql_error($conn));
		}
	}else{
		$kodeCust='';
		$iCust="select kodetimbangan from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['kodetimbangan'];
		}
		if($kodeCust!=''){
			$str="select nokontrak,tanggal from ".$dbname.".pabrik_timbangan where nosipb='".$nospp."' and kodecustomer='".$kodeCust."'";
			$res=mysql_query($str) or die (mysql_error($conn));
			while($bar=mysql_fetch_object($res)){
				$tgl[]=$bar->tanggal;
				$nokontrak=$bar->nokontrak;
			}
			$tglmin=min($tgl);
			$tglmax=max($tgl);

			if(strstr($nokontrak,'CPO')){
				$kodekegiatan='811010201';
			}elseif(strstr($nokontrak,'KER')){
				$kodekegiatan='811010202';
			}elseif(strstr($nokontrak,'CKG')){
				$kodekegiatan='811010203';
			}else{
				$kodekegiatan='811010204';
			}
			$strs="select * from ".$dbname.".log_spkht 
				where keterangan='".$nospp."' 
				and koderekanan='".$trpcode."'";
			$ress=mysql_query($strs);
			$rows=mysql_num_rows($ress);
			while($bars=mysql_fetch_object($ress)){
				$notransaksi=$bars->notransaksi;
				$posting=$bars->posting;
			}
			if($rows>0){
				if($posting=='0'){
					$strx1="update ".$dbname.".log_spkht set kodeorg='".$kodeorg."',notransaksi='".$notransaksi."',tanggal='".$tanggal."',divisi='".$kodeorg
							."',koderekanan='".$trpcode."',posting='0',nilaikontrak='".$jmlharga."',ppnnilaikontrak='0',keterangan='".$nospp."',dari='".$tglmin
							."',sampai='".$tglmax."',matauang='IDR' where keterangan='".$nospp."' and koderekanan='".$trpcode."'";
					if(mysql_query($strx1)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
					$strx2="update ".$dbname.".log_spk_tax set nilai='".($jmlharga*0.10)."' where notransaksi='".$notransaksi."' and noakun='1160100';";
					if(mysql_query($strx2)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
					$strx3="update ".$dbname.".log_spk_tax set nilai='".($jmlharga*0.02)."' where notransaksi='".$notransaksi."' and noakun='2120200';";
					if(mysql_query($strx3)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
					$strx4="update ".$dbname.".log_spkdt set notransaksi='".$notransaksi."',kodeblok='".$kodeorg."',kodekegiatan='".$kodekegiatan."',hk='0'
							,hasilkerjajumlah='".$jumlahkg."',satuan='kg',jumlahrp='".$jmlharga."',rupiahpersatuan='".$hargakg."' 
							where notransaksi='".$notransaksi."'";
					if(mysql_query($strx4)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
					$strx5="update ".$dbname.".log_baspk set notransaksi='".$notransaksi."',kodeblok='".$kodeorg."',kodekegiatan='".$kodekegiatan."'
							,tanggal='".$tanggal."',hasilkerjarealisasi='".$jumlahkg."',hkrealisasi='0',jumlahrealisasi='".$jmlharga."',jjgkontanan='0'
							,posting='0',statusjurnal='0',blokspkdt='".$kodeorg."',kodesegment='0000000001' where notransaksi='".$notransaksi."'";
					if(mysql_query($strx5)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}
			}else{
				$strx1="insert into ".$dbname.".log_spkht
					(kodeorg,notransaksi,tanggal,divisi,koderekanan,posting,nilaikontrak,ppnnilaikontrak,keterangan,dari,sampai,matauang)
					values('".$kodeorg."','".$notransaksi."','".$tanggal."','".$kodeorg."','".$trpcode."','0','".$jmlharga."','0','".$nospp."'
							,'".$tglmin."','".$tglmax."','IDR')";
				if(mysql_query($strx1)){
					$strx2="insert into ".$dbname.".log_spk_tax values('".$kodeorg."','".$notransaksi."','1160100','".($jmlharga*0.10)."')";
					if(mysql_query($strx2)){
						$strx3="insert into ".$dbname.".log_spk_tax values('".$kodeorg."','".$notransaksi."','2120200','".($jmlharga*0.02)."')";
						if(mysql_query($strx3)){
							$strx4="insert into ".$dbname.".log_spkdt
									(notransaksi,kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp,rupiahpersatuan)
									values('".$notransaksi."','".$kodeorg."','".$kodekegiatan."','0','".$jumlahkg."','kg','".$jmlharga."','".$hargakg."')";
							if(mysql_query($strx4)){
								$strx5="insert into ".$dbname.".log_baspk (notransaksi,kodeblok,kodekegiatan,tanggal,hasilkerjarealisasi,hkrealisasi
								,jumlahrealisasi,jjgkontanan,posting,statusjurnal,blokspkdt,kodesegment)
								values('".$notransaksi."','".$kodeorg."','".$kodekegiatan."','".$tanggal."','".$jumlahkg."','0'
								,'".$jmlharga."','0','0','0','".$kodeorg."','0000000001')";
								if(mysql_query($strx5)){
									//echo"<script>loadData()</script>";
								}else{
									echo " Gagal,".addslashes(mysql_error($conn));
								}
							}else{
								echo " Gagal,".addslashes(mysql_error($conn));
							}
						}else{
							echo " Gagal,".addslashes(mysql_error($conn));
						}
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}
		}
	}
?>
