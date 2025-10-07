<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses = checkPostGet('proses','');
	$kodeorg= checkPostGet('kebun0','');
	$divisi	= checkPostGet('divisi0','');
	$nik	= checkPostGet('pemanen0','');
	$tgl1 = tanggalsystemn(checkPostGet('tgl10',''));
	$tgl2 = tanggalsystemn(checkPostGet('tgl20',''));

	if($proses=='getSubUnit'){
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe='AFDELING' order by kodeorganisasi asc ";
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optDivisi.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optDivisi;
		exit;
	}
	if($proses=='getKary'){
		$whr="True";
		if($kodeorg!=''){
			$whr.=" and subbagian like '".$kodeorg."%'";
		}
		if($divisi!=''){
			$whr.=" and subbagian like '".$divisi."%'";
		}
		$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where ".$whr." order by namakaryawan";
		//exit('Warning: '.$sKary);
		$qKary=mysql_query($sKary) or die(mysql_error($conn));
		while($dKary=mysql_fetch_assoc($qKary)){
			//$optKary.="<option value=".$dKary['karyawanid'].">[".$dKary['nik'].'] '.$dKary['namakaryawan']."</option>";
			$optKary.="<option value=".$dKary['karyawanid'].">".$dKary['namakaryawan'].' - ['.$dKary['nik']."]</option>";
		}
		echo $optKary;
		exit;
	}
	$where="";
	if(!empty($kodeorg)){
		$where.=" and b.kodeorg='".$kodeorg."'";
	}else{
		exit('Warning: Unit tidak boleh kosong...!');
	}
	if(!empty($divisi)){
		$where.=" and left(a.kodeorg,6)='".$divisi."'";
	}
	if($tgl1=='--'){
		$tgl1='';
	}
	if($tgl2=='--'){
		$tgl2='';
	}
	if($tgl1=='' and  $tgl2!=''){
		$tgl1=$tgl2;
	}
	if($tgl1!='' and  $tgl2==''){
		$tgl2=$tgl1;
	}
	if($tgl1=='' and  $tgl2==''){
		exit('Warning: Tanggal tidak boleh kosong...!');
	}else{
		$where.=" and (b.tanggal>='".$tgl1."' and b.tanggal<='".$tgl2."')";
	}
	if(!empty($nik)){
		$where.=" and a.nik='".$nik."'";
	}

	if($proses=='excel'){
		$border="border=1";
	}else{
		$border="border=0";
	}
	$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

	$stream="<table cellspacing='1' $border class='sortable'>";
	$stream.="<thead>
				<tr class=rowheader>
					<td rowspan=2 align=center>No</td>
					<td rowspan=2 align=center width='100px;'>Karyawan</td>
					<td rowspan=2 align=center width='100px;'>No. Transaksi</td>
					<td rowspan=2 align=center>Tanggal</td>
					<td rowspan=2 align=center>Blok</td>
					<td rowspan=2 align=center>Basis</td>
					<td rowspan=2 align=center>Hasil Jjg</td>
					<td rowspan=2 align=center>Hasil Kg</td>
					<td rowspan=2 align=center>Brondol Kg</td>
					<td rowspan=2 align=center>Luas Panen</td>
					<td rowspan=2 align=center>Upah Kerja</td>
					<td rowspan=2 align=center>Premi</td>
					<td rowspan=2 align=center>Denda Upah</td>
					<td colspan=2 align=center>Buah Mentah</td>
					<td colspan=2 align=center>Tangkai Panjang</td>
					<td colspan=2 align=center>Over Pruning</td>
					<td colspan=2 align=center>Buah Tinggal</td>
					<td colspan=2 align=center>Brondolan Tinggal</td>
					<td colspan=2 align=center>Pelepah Tidak Disusun</td>
					<td colspan=2 align=center>Pelepah Sengkleh</td>
					<td colspan=2 align=center>Buah diperam</td>
					<td colspan=2 align=center>Buah Matahari</td>
					<td colspan=2 align=center>Buah Tidak Disusun</td>
					<td rowspan=2 align=center>Penalty Rp</td>";
	$stream.="	</tr><tr class=rowheader>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>
					<td align=center>Sat</td>
					<td align=center>Rp</td>";
	$stream.="</tr></thead><tbody>";
	$iList="select b.tipetransaksi,b.tanggal,b.kodeorg as kodeunit,a.kodeorg as kodeblok,a.notransaksi
				,b.keranimuat as mandorid	 ,e.nik as mandornik    ,e.namakaryawan as namamandor,e.subbagian as subbagianmandor,h.namajabatan as jabatanmandor
				,b.keranimuat as keranimuatid,e.nik as keranimuatnik,e.namakaryawan as namakeranimuat,e.subbagian as subbagiankeranimuat,h.namajabatan as 	jabatankeranimuat
				,a.nik		  as karyawanid  ,c.nik as karyawannik  ,c.namakaryawan,c.subbagian as subbagiankaryawan,f.namajabatan as jabatankaryawan
				,a.hasilkerja as hasilkerja,a.hasilkerjakg as hasilkerjakg,a.norma as basis,a.luaspanen as luaspanen,i.namaorganisasi as namablok
				,a.brondolan as brondolan
				,a.upahkerja as upahkerja
				,a.upahpremi as upahpremi
				,a.upahpenalty as upahpenalty
				,a.rupiahpenalty as rupiahpenalty
				,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,a.penalti9,a.penalti10
				,j.denda01,j.denda02,j.denda03,j.denda04,j.denda05,j.denda06,j.denda07,j.denda08,j.denda09,j.denda10
			from ".$dbname.".kebun_prestasi a 
			LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
			LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=b.nikmandor
			LEFT JOIN ".$dbname.".datakaryawan e on e.karyawanid=b.keranimuat
			LEFT JOIN ".$dbname.".sdm_5jabatan f on f.kodejabatan=c.kodejabatan
			LEFT JOIN ".$dbname.".sdm_5jabatan g on g.kodejabatan=d.kodejabatan
			LEFT JOIN ".$dbname.".sdm_5jabatan h on h.kodejabatan=e.kodejabatan
			LEFT JOIN ".$dbname.".organisasi i on i.kodeorganisasi=a.kodeorg
			LEFT JOIN (select kodeorg,jenisdenda
							,sum(if(kodedenda='A',denda,0)) as denda01
							,sum(if(kodedenda='TP',denda,0)) as denda02
							,sum(if(kodedenda='S',denda,0)) as denda03
							,sum(if(kodedenda='M2',denda,0)) as denda04
							,sum(if(kodedenda='GL',denda,0)) as denda05
							,sum(if(kodedenda='PB',denda,0)) as denda06
							,sum(if(kodedenda='PS',denda,0)) as denda07
							,sum(if(kodedenda='M1',denda,0)) as denda08
							,sum(if(kodedenda='M3',denda,0)) as denda09
							,sum(if(kodedenda='BT',denda,0)) as denda10
						from ".$dbname.".kebun_5dendapanen
						GROUP BY kodeorg
						ORDER BY kodeorg) j on j.kodeorg=b.kodeorg
			where b.tipetransaksi='PNN' and b.jurnal='1' ".$where."
			ORDER BY b.tanggal,a.nik";
	//exit('Warning: '.$iList);
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	$row =mysql_num_rows($nList);
	if($row==0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=4>Data Not Found...!</td>";
		$stream.="</tr>";
	}else{
		$no=0;
		$hasilkerjajjg=0;
		$hasilkerjakg=0;
		$brondolan=0;
		$luaspanen=0;
		$upahkerja=0;
		$upahpremi=0;
		$upahpenalty=0;
		$penalti1=0;$dendarp01=0;
		$penalti2=0;$dendarp02=0;
		$penalti3=0;$dendarp03=0;
		$penalti4=0;$dendarp04=0;
		$penalti5=0;$dendarp05=0;
		$penalti6=0;$dendarp06=0;
		$penalti7=0;$dendarp07=0;
		$penalti8=0;$dendarp08=0;
		$penalti9=0;$dendarp09=0;
		$penalti10=0;$dendarp10=0;
		$rupiahpenalty=0;
		while($barz=mysql_fetch_object($nList)){
			$no+=1;
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=left width='100px;'>".$barz->namakaryawan."</td>";
			$stream.="<td align=left width='100px;'>".$barz->notransaksi."</td>";
			$stream.="<td align=left>".$barz->tanggal."</td>";
			$stream.="<td align=left>".$barz->namablok."</td>";
			$stream.="<td align=right>".$barz->basis."</td>";
			$stream.="<td align=right>".$barz->hasilkerja."</td>";
			$stream.="<td align=right>".number_format($barz->hasilkerjakg,2,".","")."</td>";
			$stream.="<td align=right>".$barz->brondolan."</td>";
			$stream.="<td align=right>".number_format($barz->luaspanen,2,".","")."</td>";
			$stream.="<td align=right>".number_format($barz->upahkerja,2)."</td>";
			$stream.="<td align=right>".number_format($barz->upahpremi,2)."</td>";
			$stream.="<td align=right>".number_format($barz->upahpenalty,2)."</td>";
			$stream.="<td align=right>".$barz->penalti1."</td>";
			$stream.="<td align=right>".number_format($barz->penalti1*$barz->denda01,2)."</td>";
			$stream.="<td align=right>".$barz->penalti2."</td>";
			$stream.="<td align=right>".number_format($barz->penalti2*$barz->denda02,2)."</td>";
			$stream.="<td align=right>".$barz->penalti3."</td>";
			$stream.="<td align=right>".number_format($barz->penalti3*$barz->denda03,2)."</td>";
			$stream.="<td align=right>".$barz->penalti4."</td>";
			$stream.="<td align=right>".number_format($barz->penalti4*$barz->denda04,2)."</td>";
			$stream.="<td align=right>".$barz->penalti5."</td>";
			$stream.="<td align=right>".number_format($barz->penalti5*$barz->denda05,2)."</td>";
			$stream.="<td align=right>".$barz->penalti6."</td>";
			$stream.="<td align=right>".number_format($barz->penalti6*$barz->denda06,2)."</td>";
			$stream.="<td align=right>".$barz->penalti7."</td>";
			$stream.="<td align=right>".number_format($barz->penalti7*$barz->denda07,2)."</td>";
			$stream.="<td align=right>".$barz->penalti8."</td>";
			$stream.="<td align=right>".number_format($barz->penalti8*$barz->denda08,2)."</td>";
			$stream.="<td align=right>".$barz->penalti9."</td>";
			$stream.="<td align=right>".number_format($barz->penalti9*$barz->denda09,2)."</td>";
			$stream.="<td align=right>".$barz->penalti10."</td>";
			$stream.="<td align=right>".number_format($barz->penalti10*$barz->denda10,2)."</td>";
			$stream.="<td align=right>".number_format($barz->rupiahpenalty,2)."</td>";
			$stream.="</tr>";
			$hasilkerjajjg+=$barz->hasilkerja;
			$hasilkerjakg+=$barz->hasilkerjakg;
			$brondolan+=$barz->brondolan;
			$luaspanen+=$barz->luaspanen;
			$upahkerja+=$barz->upahkerja;
			$upahpremi+=$barz->upahpremi;
			$upahpenalty+=$barz->upahpenalty;
			$penalti1+=$barz->penalti1;$dendarp01+=$barz->penalti1*$barz->denda01;
			$penalti2+=$barz->penalti2;$dendarp02+=$barz->penalti2*$barz->denda02;
			$penalti3+=$barz->penalti3;$dendarp03+=$barz->penalti3*$barz->denda03;
			$penalti4+=$barz->penalti4;$dendarp04+=$barz->penalti4*$barz->denda04;
			$penalti5+=$barz->penalti5;$dendarp05+=$barz->penalti5*$barz->denda05;
			$penalti6+=$barz->penalti6;$dendarp06+=$barz->penalti6*$barz->denda06;
			$penalti7+=$barz->penalti7;$dendarp07+=$barz->penalti7*$barz->denda07;
			$penalti8+=$barz->penalti8;$dendarp08+=$barz->penalti8*$barz->denda08;
			$penalti9+=$barz->penalti9;$dendarp09+=$barz->penalti9*$barz->denda09;
			$penalti10+=$barz->penalti10;$dendarp10+=$barz->penalti10*$barz->denda10;
			$rupiahpenalty+=$barz->rupiahpenalty;
		}
		$stream.="<tr bgcolor='#FEDEFE'>";
		$stream.="<td colspan=6 align=center>Total</td>";
		$stream.="<td align=right>".$hasilkerjajjg."</td>";
		$stream.="<td align=right>".number_format($hasilkerjakg,2,".","")."</td>";
		$stream.="<td align=right>".$brondolan."</td>";
		$stream.="<td align=right>".number_format($luaspanen,2,".","")."</td>";
		$stream.="<td align=right>".number_format($upahkerja,2)."</td>";
		$stream.="<td align=right>".number_format($upahpremi,2)."</td>";
		$stream.="<td align=right>".number_format($upahpenalty,2)."</td>";
		$stream.="<td align=right>".$penalti1."</td>";
		$stream.="<td align=right>".number_format($dendarp01,2)."</td>";
		$stream.="<td align=right>".$penalti2."</td>";
		$stream.="<td align=right>".number_format($dendarp02,2)."</td>";
		$stream.="<td align=right>".$penalti3."</td>";
		$stream.="<td align=right>".number_format($dendarp03,2)."</td>";
		$stream.="<td align=right>".$penalti4."</td>";
		$stream.="<td align=right>".number_format($dendarp04,2)."</td>";
		$stream.="<td align=right>".$penalti5."</td>";
		$stream.="<td align=right>".number_format($dendarp05,2)."</td>";
		$stream.="<td align=right>".$penalti6."</td>";
		$stream.="<td align=right>".number_format($dendarp06,2)."</td>";
		$stream.="<td align=right>".$penalti7."</td>";
		$stream.="<td align=right>".number_format($dendarp07,2)."</td>";
		$stream.="<td align=right>".$penalti8."</td>";
		$stream.="<td align=right>".number_format($dendarp08,2)."</td>";
		$stream.="<td align=right>".$penalti9."</td>";
		$stream.="<td align=right>".number_format($dendarp09,2)."</td>";
		$stream.="<td align=right>".$penalti10."</td>";
		$stream.="<td align=right>".number_format($dendarp10,2)."</td>";
		$stream.="<td align=right>".number_format($rupiahpenalty,2)."</td>";
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";
	switch($proses){
		case 'preview':
			echo $stream;
			break;

		case 'excel':
			$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
			$tglSkrg=date("Ymd");
			$judul="<h3>PENALTY PEMANEN";
			$judul.="<BR>".($divisi=='' ? $nmOrg[$kodeorg] : $nmOrg[$divisi])."";
			$judul.="<BR>Periode : ".tanggalnormal($tgl1).' s/d '.tanggalnormal($tgl2)."</h3>";
			$nop_="PENALTY_PEMANEN_".$divisi.'_'.$periode.'_'.$tglSkrg;
			if(strlen($stream)>0){
				$stream=$judul.$stream;
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
?>
