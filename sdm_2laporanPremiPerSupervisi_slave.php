<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	##premi##upah##hk##premitetap##dendahk##dendarp

	$proses=$_POST['proses'];
	$lksiTgs=$_SESSION['empl']['lokasitugas'];
	$kdOrg=$_POST['kdOrg'];
	$afdId=$_POST['afdId'];
	$periode=$_POST['periode'];
	$pengawas=$_POST['pengawas'];
	if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
	//if($kdOrg=='')$kdOrg=$_SESSION['empl']['lokasitugas'];
	if($kdOrg==''||$kdOrg=='false'){
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			exit('Warning: Unit harus dipilih!');
		}else{
			if(substr($_SESSION['empl']['lokasitugas'],3,1)=='E'){
				$kdOrg=$_SESSION['empl']['lokasitugas'];
			}else{
				exit('Warning: Unit bukan Estate!');
			}
		}
	}
	if($afdId=='')$afdId=$_GET['afdId'];
	if($periode=='')$periode=$_GET['periode'];
	if($pengawas=='')$pengawas=$_GET['pengawas'];
	if($proses=='')$proses=$_GET['proses'];
	$lastdate=date('t',strtotime($periode.'-01'));

	if($proses=='getSubUnit'){
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."' and tipe='AFDELING' order by namaorganisasi asc ";
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optAfd.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optAfd;
		exit;
	}
	if($proses=='getSpv'){
		$optSpv="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where="";
		if($afdId!=''){
			$where.=" and b.subbagian='".$afdId."'";
		}
		if(substr($periode,5,2)=='01'){
			$thperiode=substr($periode,0,4)-1;
		}else{
			$thperiode=substr($periode,0,4);
		}
		//$sOrg="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$kdOrg."' ".$where." order by namakaryawan asc";
		$sOrg="select DISTINCT a.karyawanid,b.nik,b.namakaryawan,b.kodeorganisasi,b.lokasitugas,b.subbagian 
				from (select DISTINCT nikmandor as karyawanid from ".$dbname.".kebun_aktifitas
					  where nikmandor<>'' and tanggal like '".$thperiode."%' and kodeorg='".$kdOrg."'
					  UNION
					  select DISTINCT nikmandor1 as karyawanid from ".$dbname.".kebun_aktifitas
					  where nikmandor1<>'' and tanggal like '".$thperiode."%' and kodeorg='".$kdOrg."'
					 ) a
				LEFT JOIN ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				where b.lokasitugas='".$kdOrg."' ".$where." order by b.namakaryawan asc";
		//exit('Warning: '.$sOrg);
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optSpv.="<option value=".$rOrg['karyawanid'].">".$rOrg['namakaryawan'].'-['.$rOrg['nik']."]</option>";
		}
		echo $optSpv;
		exit;
	}

	if($kdOrg!=''){
		$whr.=" and b.kodeorg='".$kdOrg."'";
	}else{
		exit('Warning: Unit tidak boleh kosong');
	}
	if($periode!=''){
		$whr.=" and b.tanggal like '".$periode."%'";
	}else{
		exit('Warning: Periode tidak boleh kosong');
	}
	if($pengawas!=''){
		$whr.=" and b.nikmandor='".$pengawas."'";
	//}else{
	//	exit('Warning: Supervisi tidak boleh kosong');
	}
			// ================== Start Denda Grading Pabrik ==================
			$sgrading="select a.notransaksi,a.nospb,b.jumlah,c.tanggal,c.nikmandor,c.mentah from ".$dbname.".pabrik_timbangan a
						LEFT JOIN ".$dbname.".pabrik_sortasi b on b.notiket=a.notransaksi and kodefraksi='B'
						LEFT JOIN (select x.nospb,z.tanggal,x.blok,z.nikmandor,sum(z.mentah) as mentah from ".$dbname.".kebun_spbdt x
									LEFT JOIN ".$dbname.".kebun_spbht y on y.nospb=x.nospb
									LEFT JOIN (SELECT w.tanggal,w.nikmandor,v.kodeorg,SUM(v.penalti1) as mentah from ".$dbname.".kebun_prestasi v 
												LEFT JOIN ".$dbname.".kebun_aktifitas w on w.notransaksi=v.notransaksi
												where w.tipetransaksi='PNN' and v.kodeorg like '".$kdOrg."%' and v.kodeorg like '".$afdId."%' 
														and w.tanggal like '".$periode."%' and w.nikmandor<>''
												GROUP BY w.tanggal,w.nikmandor,v.kodeorg) z on z.kodeorg=x.blok and z.tanggal=y.tanggal
									where x.blok like '".$kdOrg."%' and x.blok like '".$afdId."%' and y.tanggal like '".$periode."%' and z.nikmandor<>''
									GROUP BY x.nospb,y.tanggal,z.nikmandor) c on c.nospb=a.nospb
						where a.nospb like '%".$kdOrg."%' and a.tanggal like '".$periode."%' and a.jjgsortasi>0 and b.jumlah>3
						ORDER BY a.tanggal";
			//exit('Warning : '.$sgrading);
			$qgrading=mysql_query($sgrading);
			while($rgrading=mysql_fetch_object($qgrading)){
				if($rgrading->jumlah<=3){
					$grading[$rgrading->nikmandor][$rgrading->tanggal]=0;
				}else if($rgrading->jumlah<=6){
					$grading[$rgrading->nikmandor][$rgrading->tanggal]=0.05;
				}else if($rgrading->jumlah<=10){
					$grading[$rgrading->nikmandor][$rgrading->tanggal]=0.075;
				}else{
					$grading[$rgrading->nikmandor][$rgrading->tanggal]=0.10;
				}
			}

	#ambil data premi
	$str="select b.nikmandor,b.tanggal,a.kodeorg,c.namaorganisasi,1 as jmlorg,(a.hasilkerja) as jjg,(a.hasilkerjakg) as kg,a.brondolan
			,(a.upahpremi) as premi,(a.upahpenalty+a.rupiahpenalty) as denda,(a.upahpremi-(a.upahpenalty+a.rupiahpenalty)) as totalpremi
			from ".$dbname.".kebun_prestasi a
			LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeorg
			where b.tipetransaksi='PNN' ".$whr."
			ORDER BY b.nikmandor,b.tanggal,a.kodeorg";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$nikmandor  =Array();
	$tanggal=Array();
	$xblok='';
	while($bar=mysql_fetch_object($res)){
		$nikmandor[$bar->nikmandor]=$bar->nikmandor;
		$tanggal[$bar->tanggal]=$bar->tanggal;
		if($xblok!=$bar->namaorganisasi){
			$xblok=$bar->namaorganisasi;
			$blok[$bar->nikmandor][$bar->tanggal].=$bar->namaorganisasi.', ';
		}
		$jmlorg[$bar->nikmandor][$bar->tanggal]+=$bar->jmlorg;
		$jjg[$bar->nikmandor][$bar->tanggal]+=$bar->jjg;
		$kg[$bar->nikmandor][$bar->tanggal]+=$bar->kg;
		$brd[$bar->nikmandor][$bar->tanggal]+=$bar->brondolan;
		$premi[$bar->nikmandor][$bar->tanggal]+=$bar->premi;
		$denda[$bar->nikmandor][$bar->tanggal]+=$bar->denda;
		$totalpremi[$bar->nikmandor][$bar->tanggal]+=$bar->totalpremi;
	}
	$bdr=0;
	if($proses=='excel'){
		$bdr=1;
		// get namaorganisasi =========================================================================
		$sOrg="select a.kodeorganisasi,a.namaorganisasi,a.induk,a.wilayahkota,b.namaorganisasi as namapt from ".$dbname.".organisasi a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.induk
				where a.kodeorganisasi ='".$kdOrg."' ";	
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$nmOrg=$rOrg['namaorganisasi'];
			$indukOrg=$rOrg['induk'];
			$namapt=$rOrg['namapt'];
			$kota=$rOrg['wilayahkota'];
		}
		$stream="<h3>".$namapt."<br>".$nmOrg."</h3>";
		$stream.="<b>Perhitungan Premi Mandor Panen</b>"; 
	}
	$gtjmlorg=0;
	$gtjjg=0;
	$gtkg=0;
	$gtbrd=0;
	$gtpremi=0;
	$gtdenda=0;
	$gttotalpremi=0;
	$gtratarata=0;
	$gtpremihk=0;
	$gtpremihl=0;
	$gtpremisbl=0;
	$gtdendagrd=0;
	$gtpremissd=0;
	$no=0;
	foreach($nikmandor as $mdnik=>$mdval){
		$no+=1;
		// get karyawan =========================================================================
		$sOrg="select * from ".$dbname.".datakaryawan where karyawanid='".$mdnik."'";	
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$namakaryawan=$rOrg['namakaryawan'];
			$subbagian=$rOrg['subbagian'];
		}
		$stream.="<table cellspacing='1' class='sortable'>
					<tr>
						<td>Divisi</td><td>: ".$subbagian."</td>
					</tr>
					<tr>
						<td>Nama Mandor</td><td>: ".$namakaryawan."</td>
					</tr>
					<tr>
						<td>Bulan</td><td>: ".$periode."</td>
					</tr>
				</table>";
		#preview: nampilin header ================================================================================
		$stream.="<table cellspacing='1' border='".$bdr."' class='sortable'>
					<thead class=rowheader>
						<tr>
							<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
							<td rowspan=2 align=center>".$_SESSION['lang']['blok']."</td>
							<td rowspan=2 align=center>Tenaga Kerja (Prs)</td>
							<td colspan=2 align=center>Hari Kerja</td>
							<td colspan=2 align=center>Hari Libur</td>
							<td rowspan=2 align=center>Premi (Capai Basis, Over Basis, Brondolan)</td>
							<td rowspan=2 align=center>Potongan (Tidak Capai Basis, Penalty)</td>
							<td rowspan=2 align=center>Total Premi (Rp)</td>
							<td rowspan=2 align=center>Rata-rata (Rp)</td>
							<td rowspan=2 align=center>Premi Hari Kerja (Rp)</td>
							<td rowspan=2 align=center>Premi Hari Libur (Rp)</td>
							<td rowspan=2 align=center>Potongan Grading</td>
							<td rowspan=2 align=center>Total Pendapatan / Premi (Rp)</td>
						</tr>
						<tr>
							<td align=center>Janjang Panen (JJg)</td>
							<td align=center>LF (Kg)</td>
							<td align=center>Janjang Panen (JJg)</td>
							<td align=center>LF (Kg)</td>
						</tr>
					</thead>
					<tbody>";
		$stjmlorg=0;
		$stjjg=0;
		$stkg=0;
		$stbrd=0;
		$stpremi=0;
		$stdenda=0;
		$sttotalpremi=0;
		$stratarata=0;
		$stpremihk=0;
		$stpremihl=0;
		$stpremisbl=0;
		$stdendagrd=0;
		$stpremissd=0;
		$dendagrading=0;
		for ($x = 1; $x <= $lastdate; $x++) {
			$tgl=$periode."-".sprintf("%02d",$x);
			// Cek Hari Libur
			$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$tgl."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
			$resLibur = fetchData($qLibur);
			$libur = 'KERJA';
			if(!empty($resLibur)) $libur = 'LIBUR';
			$stream.="<tr class=rowcontent>
						<td align=center style='width:70px'>".$tgl."</td>
						<td>".substr($blok[$mdnik][$tgl],0,strlen($blok[$mdnik][$tgl])-2)."</td>";
			if($jmlorg[$mdnik][$tgl]>0){
				$stream.="<td align=right style='width:45px'>".number_format($jmlorg[$mdnik][$tgl],0)."</td>";
				if($libur=='LIBUR'){
					$stream.="<td align=right style='width:50px'></td>
							<td align=right style='width:50px'></td>
							<td align=right style='width:50px'>".number_format($jjg[$mdnik][$tgl],0)."</td>
							<td align=right style='width:50px'>".number_format($brd[$mdnik][$tgl],0)."</td>";
				}else{
					$stream.="<td align=right style='width:50px'>".number_format($jjg[$mdnik][$tgl],0)."</td>
							<td align=right style='width:50px'>".number_format($brd[$mdnik][$tgl],0)."</td>
							<td align=right style='width:50px'></td>
							<td align=right style='width:50px'></td>";
				}
				$stream.="	<td align=right style='width:80px'>".number_format($premi[$mdnik][$tgl],0)."</td>
							<td align=right style='width:80px'>".number_format($denda[$mdnik][$tgl],0)."</td>
							<td align=right style='width:80px'>".number_format($totalpremi[$mdnik][$tgl],0)."</td>";
				$ratarata=$totalpremi[$mdnik][$tgl]/$jmlorg[$mdnik][$tgl];
				$stream.="<td align=right style='width:55px'>".number_format($ratarata,0)."</td>";
				if($libur=='LIBUR'){
					$premisbldenda=$ratarata*1.5/2;
					$stream.="<td align=right style='width:80px'></td>";
					$stream.="<td align=right style='width:80px'>".number_format($premisbldenda,0)."</td>";
					$stjjghl+=$jjg[$mdnik][$tgl];
					$stkghl+=$kg[$mdnik][$tgl];
					$stbrdhl+=$brd[$mdnik][$tgl];
					$stpremihl+=$premisbldenda;
					$gtjjghl+=$jjg[$mdnik][$tgl];
					$gtkghl+=$kg[$mdnik][$tgl];
					$gtbrdhl+=$brd[$mdnik][$tgl];
					$gtpremihl+=$premisbldenda;
				}else{
					$premisbldenda=$ratarata*1.5;
					$stream.="<td align=right style='width:80px'>".number_format($premisbldenda,0)."</td>";
					$stream.="<td align=right style='width:80px'></td>";
					$stjjghk+=$jjg[$mdnik][$tgl];
					$stkghk+=$kg[$mdnik][$tgl];
					$stbrdhk+=$brd[$mdnik][$tgl];
					$stpremihk+=$premisbldenda;
					$gtjjghk+=$jjg[$mdnik][$tgl];
					$gtkghk+=$kg[$mdnik][$tgl];
					$gtbrdhk+=$brd[$mdnik][$tgl];
					$gtpremihk+=$premisbldenda;
				}
				$dendagrading=abs($grading[$mdnik][$tgl]*$premisbldenda);
				$stream.="<td align=right style='width:80px'>".number_format($dendagrading,0)."</td>";
				$stream.="<td align=right style='width:80px'>".number_format($premisbldenda-$dendagrading,0)."</td>";
			}else{
				$ratarata=0;
				$premisbldenda=0;
				$dendagrading=0;
				$stream.="<td align=right style='width:45px'></td>";
				$stream.="<td align=right style='width:50px'></td>";
				$stream.="<td align=right style='width:50px'></td>";
				$stream.="<td align=right style='width:50px'></td>";
				$stream.="<td align=right style='width:50px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
				$stream.="<td align=right style='width:80px'></td>";
			}
			$stream.="</tr>";
			$stjmlorg+=$jmlorg[$mdnik][$tgl];
			$stpremi+=$premi[$mdnik][$tgl];
			$stdenda+=$denda[$mdnik][$tgl];
			$sttotalpremi+=$totalpremi[$mdnik][$tgl];
			$stratarata+=$ratarata;
			$stpremisbl+=$premisbldenda;
			$stdendagrd+=$dendagrading;
			$stpremissd+=$premisbldenda-$dendagrading;

			$gtjmlorg+=$jmlorg[$mdnik][$tgl];
			$gtpremi+=$premi[$mdnik][$tgl];
			$gtdenda+=$denda[$mdnik][$tgl];
			$gttotalpremi+=$totalpremi[$mdnik][$tgl];
			$gtratarata+=$ratarata;
			$gtpremisbl+=$premisbldenda;
			$gtdendagrd+=$dendagrading;
			$gtpremissd+=$premisbldenda-$dendagrading;
		}
		$stream.="<tr bgcolor='#FFE4E1'>";
		$stream.="<td align=center colspan=2>Total</td>";
		$stream.="<td align=right style='width:45px'>".number_format($stjmlorg,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($stjjghk,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($stbrdhk,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($stjjghl,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($stbrdhl,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stpremi,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stdenda,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($sttotalpremi,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stratarata,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stpremihk,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stpremihl,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stdendagrd,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($stpremissd,0)."</td>";
		$stream.="</tr>";
	}
	if($no>1){
		$stream.="<tr bgcolor='#FFDAB9'>";
		$stream.="<td align=center colspan=2>Grand Total</td>";
		$stream.="<td align=right style='width:45px'>".number_format($gtjmlorg,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($gtjjghk,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($gtbrdhk,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($gtjjghl,0)."</td>";
		$stream.="<td align=right style='width:50px'>".number_format($gtbrdhl,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtpremi,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtdenda,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gttotalpremi,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtratarata,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtpremihk,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtpremihl,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtdendagrd,0)."</td>";
		$stream.="<td align=right style='width:80px'>".number_format($gtpremissd,0)."</td>";
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";
	switch($proses)
	{
        case'preview':
          echo $stream;
        break;
        case 'excel':
            $judul="Laporan_premi_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0){
				$stream.="<br><table>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td colspan=4>".ucfirst(strtolower($kota)).", ".date('j F Y')."</td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Dibuat</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Diketahui</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Diperiksa</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Disetujui</td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
								<td style='border-style:solid;border-width: 0px 1px 0px 1px;'></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td style='border-style:solid;border-width: 1px 1px 1px 1px;'></td>
								<td style='border-style:solid;border-width: 1px 1px 1px 1px;'></td>
								<td style='border-style:solid;border-width: 1px 1px 1px 1px;'></td>
								<td style='border-style:solid;border-width: 1px 1px 1px 1px;'></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Kerani Divisi</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Field Assistant</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Estate Data Adm.</td>
								<td align=center style='border-style:solid;border-width: 1px 1px 1px 1px;'>Estate Manager</td>
							</tr>
						</table>";
			    //$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
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
	}    

?>