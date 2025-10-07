<?php
//ind
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=$_POST['proses'];
$unit=$_POST['unit'];
$per=$_POST['per'];
$thn=substr($per,0,4);
$bjrset=$_POST['bjrset'];
$kodeblok=$_POST['kodeblok'];
if(substr($per,5,2)=='01'){
	$perlalu=(substr($per,0,4)-1)."-12";
}else{
	$perlalu=substr($per,0,4)."-".sprintf("%02d",substr($per,5,2)-1);
}
$tglakhir=date('Y-m-t',strtotime($per.'-01'));
//exit('Warning: '.$tglakhir);
switch($proses){
    case'simpan':
        $iSel="select * from ".$dbname.".kebun_5bjr where kodeorg like '".$unit."%' and tahunproduksi='".$thn."'";
        if(mysql_query($iSel)){
        }else{
			echo " Gagal,".addslashes(mysql_error($conn));
        }
    break;
    
    case'savedata':
        if($bjrset=='0' or $bjrset==''){
        }else{
			$iSel="select * from ".$dbname.".kebun_5bjr where kodeorg = '".$kodeblok."' and tahunproduksi='".$thn."'";
			$ress=mysql_query($iSel);
			$rows=mysql_num_rows($ress);
			//exit('Warning: '.$proses.' Unit='.$unit.' per='.$per.' thn='.$thn.' bjrset='.$bjrset.' blok='.$kodeblok.' row='.$rows);
			if($rows>0){
				$str="update ".$dbname.".kebun_5bjr set bjr='".$bjrset."' where kodeorg = '".$kodeblok."' and tahunproduksi='".$thn."'";
				//exit('Warning: '.$str);
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}else{
				$str="insert into ".$dbname.".kebun_5bjr (`kodeorg`,`bjr`,`tahunproduksi`)
				values ('".$kodeblok."','".$bjrset."','".$thn."')";
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}
        }
    break; 
                
    case'calcrestan':
		// ====== Calculate Restan Janjang ======
		$sRestan="select a.kodeorganisasi as kodeorg,a.namaorganisasi
						,if(isnull(f.jjglalu),0,f.jjglalu)+if(isnull(b.jjgpanen),0,b.jjgpanen)+if(isnull(d.jjgBorongan),0,d.jjgBorongan)
						+if(isnull(e.jjgkontanan),0,e.jjgkontanan)+if(isnull(d.jjgTemuan_NonBKM),0,d.jjgTemuan_NonBKM)
						-if(isnull(c.jjgkirim),0,c.jjgkirim)-if(isnull(d.jjgAfkir),0,d.jjgAfkir)-if(isnull(d.jjgHilang_TPH),0,d.jjgHilang_TPH) as jjgRestan
				from ".$dbname.".organisasi a
				LEFT JOIN (select kodeorg,sum(hasilkerja) as jjgpanen
						from ".$dbname.".kebun_prestasi_vw
						where kodeorg like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY kodeorg) b on b.kodeorg=a.kodeorganisasi
				LEFT JOIN (select blok,sum(jjg) as jjgkirim
						from ".$dbname.".kebun_spb_vw
						where blok like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY blok) c on c.blok=a.kodeorganisasi
				LEFT JOIN (select kodeorg
									,sum(if(jenis='Afkir',janjang,0)) as jjgAfkir
									,sum(if(jenis='Borongan',janjang,0)) as jjgBorongan
									,sum(if(jenis='Temuan_BKM',janjang,0)) as jjgTemuan_BKM
									,sum(if(jenis='Temuan_NonBKM',janjang,0)) as jjgTemuan_NonBKM
									,sum(if(jenis='Hilang_TPH',janjang,0)) as jjgHilang_TPH
									,sum(if(jenis='Hilang_Pokok',janjang,0)) as jjgHilang_Pokok
						from ".$dbname.".kebun_adjpanen
						where kodeorg like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY kodeorg) d on d.kodeorg=a.kodeorganisasi
				LEFT JOIN (select kodeblok,sum(jjgkontanan) as jjgkontanan 
						from ".$dbname.".log_baspk
						where jjgkontanan>0 and kodeblok like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY kodeblok) e on e.kodeblok=a.kodeorganisasi
				LEFT JOIN (select kodeorg,sum(jjgpanen-jjgkirim) as jjglalu 
						from ".$dbname.".kebun_restan
						where jjgpanen-jjgkirim<>0 and kodeorg like '".$unit."%' and tanggal like '".$perlalu."%'
						GROUP BY kodeorg) f on f.kodeorg=a.kodeorganisasi
				where a.kodeorganisasi like '".$unit."%' and a.tipe='BLOK' 
				and (if(isnull(f.jjglalu),0,f.jjglalu)+if(isnull(b.jjgpanen),0,b.jjgpanen)+if(isnull(d.jjgBorongan),0,d.jjgBorongan)
					+if(isnull(e.jjgkontanan),0,e.jjgkontanan)+if(isnull(d.jjgTemuan_NonBKM),0,d.jjgTemuan_NonBKM)
					-if(isnull(c.jjgkirim),0,c.jjgkirim)-if(isnull(d.jjgAfkir),0,d.jjgAfkir)-if(isnull(d.jjgHilang_TPH),0,d.jjgHilang_TPH))<>0
				ORDER BY a.kodeorganisasi
				";
		//exit('Warning: '.$sRestan);
		$qRestan=mysql_query($sRestan) or die(mysql_error($conn));
		while($rRestan=mysql_fetch_assoc($qRestan)){
			if($rRestan['jjgRestan']<0){
				//exit('Warning: Restan dibawah 0 (Minus)...! Input Adjustment Panen...! '.$rRestan['kodeorg'].'-'.$rRestan['namaorganisasi'].'='.$rRestan['jjgRestan']);
				//break;
			}
			$sAkhir="select * from ".$dbname.".kebun_restan where kodeorg='".$rRestan['kodeorg']."' and tanggal='".$tglakhir."'";
			$qAkhir=mysql_query($sAkhir);
			$rows=mysql_num_rows($qAkhir);
			if($rows>0){
				$str="update ".$dbname.".kebun_restan set jjgkirim=0,jjgpanen='".$rRestan['jjgRestan']."' where kodeorg='".$rRestan['kodeorg']."' and tanggal='".$tglakhir."'";
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}else{
				$str="insert into ".$dbname.".kebun_restan (`kodeorg`,`tanggal`,`jjgpanen`,`catatan`)
				values ('".$rRestan['kodeorg']."','".$tglakhir."','".$rRestan['jjgRestan']."','Restan Akhir')";
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}
		}

		// ====== Calculate Restan Brondol ======
		$sRestan="select a.kodeorganisasi as kodeorg,a.namaorganisasi
						,if(isnull(b.brdlalu),0,b.brdlalu)+if(isnull(c.brdpanen),0,c.brdpanen)+if(isnull(d.brdrawat),0,d.brdrawat)
						+if(isnull(e.brdborong),0,e.brdborong)
						-if(isnull(f.brdkirim),0,f.brdkirim)-if(isnull(g.brdafkir),0,g.brdafkir)-if(isnull(g.brdhilang),0,g.brdhilang) as brdrestan
				from ".$dbname.".organisasi a
				LEFT JOIN (select kodeorg,sum(kgsisa) as brdlalu 
						from ".$dbname.".kebun_sisabrondol
						where kgsisa<>0 and kodeorg like '".$unit."%' and tanggal like '".$perlalu."%'
						GROUP BY kodeorg) b on b.kodeorg=a.kodeorganisasi
				LEFT JOIN (select x.kodeorg,sum(x.brondolan) as brdpanen
						from ".$dbname.".kebun_prestasi x
						LEFT JOIN ".$dbname.".kebun_aktifitas y on y.notransaksi=x.notransaksi
						where y.tipetransaksi='PNN' and x.kodeorg like '".$unit."%' and y.tanggal like '".$per."%'
						GROUP BY x.kodeorg) c on c.kodeorg=a.kodeorganisasi
				LEFT JOIN (select v.kodeorg,sum(v.hasilkerja) as brdrawat
						from ".$dbname.".kebun_prestasi v
						LEFT JOIN ".$dbname.".kebun_aktifitas w on w.notransaksi=v.notransaksi
						where v.kodekegiatan='611010201' and v.kodeorg like '".$unit."%' and w.tanggal like '".$per."%'
						GROUP BY v.kodeorg) d on d.kodeorg=a.kodeorganisasi
				LEFT JOIN (select kodeblok as kodeorg,sum(hasilkerjarealisasi) as brdborong
						from ".$dbname.".log_baspk
						where kodekegiatan='611010201' and kodeblok like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY kodeblok) e on e.kodeorg=a.kodeorganisasi
				LEFT JOIN (select blok as kodeorg,sum(brondolan) as brdkirim
						from ".$dbname.".kebun_spb_vw
						where blok like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY blok) f on f.kodeorg=a.kodeorganisasi
				LEFT JOIN (select kodeorg,sum(if(jenis='Afkir',kg,0)) as brdafkir,sum(if(jenis='Hilang',kg,0)) as brdhilang
						from ".$dbname.".kebun_adjbrondol
						where kodeorg like '".$unit."%' and tanggal like '".$per."%'
						GROUP BY kodeorg) g on g.kodeorg=a.kodeorganisasi
				where a.kodeorganisasi like '".$unit."%' and a.tipe='BLOK' and
				(if(isnull(b.brdlalu),0,b.brdlalu)+if(isnull(c.brdpanen),0,c.brdpanen)+if(isnull(d.brdrawat),0,d.brdrawat)+if(isnull(e.brdborong),0,e.brdborong)
				-if(isnull(f.brdkirim),0,f.brdkirim)-if(isnull(g.brdafkir),0,g.brdafkir)-if(isnull(g.brdhilang),0,g.brdhilang))<>0
				ORDER BY a.kodeorganisasi
				";
		//exit('Warning: '.$sRestan);
		$qRestan=mysql_query($sRestan) or die(mysql_error($conn));
		while($rRestan=mysql_fetch_assoc($qRestan)){
			if($rRestan['brdrestan']>0){
				$sAkhir="select * from ".$dbname.".kebun_sisabrondol where kodeorg='".$rRestan['kodeorg']."' and tanggal='".$tglakhir."'";
				$qAkhir=mysql_query($sAkhir);
				$rows=mysql_num_rows($qAkhir);
				if($rows>0){
					$str="update ".$dbname.".kebun_sisabrondol set kesisa='".$rRestan['brdrestan']."'
					,lastuser='".$_SESSION['standard']['username']."',lastdate=now() where kodeorg='".$rRestan['kodeorg']."' and tanggal='".$tglakhir."'";
					if(mysql_query($str)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}else{
					$str="insert into ".$dbname.".kebun_sisabrondol (`kodeorg`,`tanggal`,`kgsisa`,`catatan`,`lastuser`,`lastdate`)
					values ('".$rRestan['kodeorg']."','".$tglakhir."','".$rRestan['brdrestan']."','Restan','".$_SESSION['standard']['username']."',now())";
					if(mysql_query($str)){
					}else{
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}
			}
		}
	break; 
                
    default:
    break; 
}

?>
