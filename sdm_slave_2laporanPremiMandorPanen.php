<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	##premi##upah##hk##premitetap##dendahk##dendarp

	$proses=$_POST['proses'];
	$periode=$_POST['periode'];
	$lksiTgs=$_SESSION['empl']['lokasitugas'];
	$kdOrg=$_POST['kdOrg'];
	$afdId=$_POST['afdId'];
	$pengawas=$_POST['pengawas'];
	if($periode=='')$periode=$_GET['periode'];
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
	if($pengawas=='')$pengawas=$_GET['pengawas'];
	if($proses=='')$proses=$_GET['proses'];
	$thnd=explode("-",$periode);

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

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdOrg;

	// determine begin end =========================================================================
	$lok=substr($kdOrg,0,4); //$_SESSION['empl']['lokasitugas'];
	$sDatez = "select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where periode = '".$periode."' and kodeorg= '".$lok."'";
	$qDatez=mysql_query($sDatez) or die(mysql_error($conn));
	while($rDatez=mysql_fetch_assoc($qDatez)){
		$tanggalMulai=$rDatez['tanggalmulai'];
		$tanggalSampai=$rDatez['tanggalsampai'];
	}

	function dates_inbetwee($date1, $date2){
		$day = 60*60*24;
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
		$dates_array = array();
		$dates_array[] = date('Y-m-d',$date1);
		for($x = 1; $x < $days_diff; $x++){
			$dates_array[] = date('Y-m-d',($date1+($day*$x)));
		}
		$dates_array[] = date('Y-m-d',$date2);
		return $dates_array;
	}
	//$tgltgl = dates_inbetwee($tanggalMulai, $tanggalSampai);
	$tgltgl = rangeTanggal($tanggalMulai, $tanggalSampai);

	#ambil data premi
	if($afdId!=''){
		$whr=" and a.kodeorg like '".$afdId."%'";
	}else{
		$whr=" and a.kodeorg like '".$kdOrg."%' ";
	}
	if($pengawas=='M'){
		$str="select b.tipetransaksi,b.tanggal,b.kodeorg as kodeunit,a.kodeorg as kodeblok
			,b.nikmandor  as mandorid	 ,d.nik as mandornik    ,d.namakaryawan as namamandor,d.subbagian as subbagianmandor,g.namajabatan as jabatanmandor
			,b.keranimuat as keranimuatid,e.nik as keranimuatnik,e.namakaryawan as namakeranimuat,e.subbagian as subbagiankeranimuat,h.namajabatan as jabatankeranimuat
			,a.nik		  as karyawanid  ,c.nik as karyawannik  ,c.namakaryawan,c.subbagian as subbagiankaryawan,f.namajabatan as jabatankaryawan
			,sum(a.hasilkerja) as hasilkerja,sum(a.hasilkerjakg) as hasilkerjakg,sum(a.norma) as basis,sum(a.luaspanen) as luaspanen
			,sum(a.brondolan) as brondolan
			,sum(a.upahkerja) as upahkerja
			,sum(a.upahpremi) as upahpremi
			,sum(a.upahpenalty) as upahpenalty
			,sum(a.rupiahpenalty) as rupiahpenalty
		from ".$dbname.".kebun_prestasi a 
		LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
		LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
		LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=b.nikmandor
		LEFT JOIN ".$dbname.".datakaryawan e on e.karyawanid=b.keranimuat
		LEFT JOIN ".$dbname.".sdm_5jabatan f on f.kodejabatan=c.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan g on g.kodejabatan=d.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan h on h.kodejabatan=e.kodejabatan
		where b.tipetransaksi='PNN'
			and b.kodeorg = '".$kdOrg."'
			".$whr." 
			and b.tanggal like '".$periode."%'
			and b.jurnal='1'
			and b.nikmandor<>''
		GROUP BY b.nikmandor,a.nik,b.tanggal
		ORDER BY d.subbagian,b.nikmandor,a.nik,b.tanggal
		";
	}else{
		$str="select b.tipetransaksi,b.tanggal,b.kodeorg as kodeunit,a.kodeorg as kodeblok
			,b.keranimuat as mandorid	 ,e.nik as mandornik    ,e.namakaryawan as namamandor,e.subbagian as subbagianmandor,h.namajabatan as jabatanmandor
			,b.keranimuat as keranimuatid,e.nik as keranimuatnik,e.namakaryawan as namakeranimuat,e.subbagian as subbagiankeranimuat,h.namajabatan as jabatankeranimuat
			,a.nik		  as karyawanid  ,c.nik as karyawannik  ,c.namakaryawan,c.subbagian as subbagiankaryawan,f.namajabatan as jabatankaryawan
			,sum(a.hasilkerja) as hasilkerja,sum(a.hasilkerjakg) as hasilkerjakg,sum(a.norma) as basis,sum(a.luaspanen) as luaspanen
			,sum(a.brondolan) as brondolan
			,sum(a.upahkerja) as upahkerja
			,sum(a.upahpremi) as upahpremi
			,sum(a.upahpenalty) as upahpenalty
			,sum(a.rupiahpenalty) as rupiahpenalty
		from ".$dbname.".kebun_prestasi a 
		LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
		LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
		LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=b.nikmandor
		LEFT JOIN ".$dbname.".datakaryawan e on e.karyawanid=b.keranimuat
		LEFT JOIN ".$dbname.".sdm_5jabatan f on f.kodejabatan=c.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan g on g.kodejabatan=d.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan h on h.kodejabatan=e.kodejabatan
		where b.tipetransaksi='PNN'
			and b.kodeorg = '".$kdOrg."'
			".$whr." 
			and b.tanggal like '".$periode."%'
			and b.jurnal='1'
			and b.keranimuat<>''
		GROUP BY b.keranimuat,a.nik,b.tanggal
		ORDER BY e.subbagian,b.keranimuat,a.nik,b.tanggal
		";
	}
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$mandornik  =Array();
	$karyawannik=Array();
	$upahpremi  =Array();
	while($bar=mysql_fetch_object($res)){
		// Cek Hari Libur
		$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$bar->tanggal."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
		$resLibur = fetchData($qLibur);
		$libur = 'KERJA';
		if(!empty($resLibur)) $libur = 'LIBUR';
					
		$qbrondol = "select DISTINCT premibrondolan from ".$dbname.".kebun_5basispanen2 where afdeling='".$indukOrg."' and jenispremi='".$libur."'";
		$rbrondol=mysql_query($qbrondol) or die(mysql_error($conn));
		$rpbrondol=1;
		while($dbrondol=mysql_fetch_object($rbrondol)){
			$rpbrondol=$dbrondol->premibrondolan;
		}

		$mandornik[$bar->mandorid]=$bar->mandornik;
		$namamandor[$bar->mandorid]=$bar->namamandor;
		$jabatanmandor[$bar->mandorid]=$bar->jabatanmandor;
		$subbagianmandor[$bar->mandorid]=$bar->subbagianmandor;

		$karyawannik[$bar->mandorid][$bar->karyawanid]=$bar->karyawannik;
		$namakaryawan[$bar->mandorid][$bar->karyawanid]=$bar->namakaryawan;
		$jabatankaryawan[$bar->mandorid][$bar->karyawanid]=$bar->jabatankaryawan;
		$subbagiankaryawan[$bar->mandorid][$bar->karyawanid]=$bar->subbagiankaryawan;

		$upahkerja[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahkerja;
		$premibasis[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahpremi-($bar->brondolan*$rpbrondol);
		$premibrondol[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->brondolan*$rpbrondol;
		$upahpremi[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahpremi;
		$upahpenalty[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahpenalty;
		$rupiahpenalty[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->rupiahpenalty;
		$denda[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahpenalty+$bar->rupiahpenalty;
		$total[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$bar->upahkerja+$bar->upahpremi-($bar->upahpenalty+$bar->rupiahpenalty);
		if($libur == 'LIBUR'){
			$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=($bar->upahpremi-($bar->upahpenalty+$bar->rupiahpenalty))/2;
			//$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=($bar->upahpremi-($bar->rupiahpenalty))/2;
		}else{
			$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=($bar->upahpremi-($bar->upahpenalty+$bar->rupiahpenalty));
			//$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=($bar->upahpremi-($bar->rupiahpenalty));
		}
		if($pengawas=='M'){
			$premibersih[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]*1.5;
		}else{
			$premibersih[$bar->mandorid][$bar->karyawanid][$bar->tanggal]=$premihitung[$bar->mandorid][$bar->karyawanid][$bar->tanggal]*1.25;
		}
		if($total[$bar->mandorid][$bar->karyawanid][$bar->tanggal] != 0){
			$jmlpemanen[$bar->mandorid][$bar->tanggal]+=1;
		}
		//if($jmlpemanen[$bar->mandorid][$bar->tanggal] == 0){
		//	$jmlpemanen[$bar->mandorid][$bar->tanggal]=1;
		//}
	}
	// ================== Start Denda Supervisi ==================
	$grading=Array();
	$dendatph=Array();
	if($periode>='2022-09'){
		if($pengawas=='M'){
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
		}else{
			// ================== Start Denda TPH banyak buah/brondolan tinggal ==================
			$sgrading="select a.notransaksi,b.tanggal,b.keranimuat,a.kodeorg,avg(c.denda) as denda from ".$dbname.".kebun_prestasi a 
						LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
						LEFT JOIN (select z.kodeorg,y.tanggal,x.denda from ".$dbname.".kebun_kehadiran x
									LEFT JOIN ".$dbname.".kebun_aktifitas y on y.notransaksi=x.notransaksi
									LEFT JOIN ".$dbname.".kebun_prestasi z on z.notransaksi=x.notransaksi
									where x.denda>0 and z.kodekegiatan='611020103'
									and z.kodeorg like '".$kdOrg."%' and y.tanggal like '".$periode."%') c on c.kodeorg=a.kodeorg and c.tanggal=b.tanggal
						where b.tipetransaksi='PNN' and c.denda>0
						and a.kodeorg like '".$kdOrg."%' and b.tanggal like '".$periode."%'
						GROUP BY b.tanggal,b.keranimuat";
			//exit('Warning : '.$sgrading);
			$qgrading=mysql_query($sgrading);
			while($rgrading=mysql_fetch_object($qgrading)){
				$dendatph[$rgrading->keranimuat][$rgrading->tanggal]=$rgrading->denda;
			}
		}
	}
	// ================== End Denda Supervisi ==================
	$kolspan=10;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	$stream="Laporan_Premi_".($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."_".$kdOrg."_".$periode; 
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr." rowspan=3>No</td>
        <td ".$bgclr." rowspan=3>".($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['nik']."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['jabatan']."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['subunit']."</td>
        <td ".$bgclr." rowspan=3>No</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['pemanen']."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['nik']."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['jabatan']."</td>
        <td ".$bgclr." rowspan=3>".$_SESSION['lang']['subunit']."</td>";
        foreach($tgltgl as $ar => $isi)
        {
			// Cek Hari Libur
			$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
			$resLibur = fetchData($qLibur);
			$libur = false;
			if(!empty($resLibur)) $libur = true;
				
            $qwe=date('D', strtotime($isi));
            $stream.="<td width=5px  ".$bgclr."  colspan=".$kolspan.">";
            //if($qwe=='Sun')
            if($libur)
				$stream.="<font color=red>".substr($isi,8,2)."</font>"; 
            else 
				$stream.=(substr($isi,8,2)); 
            $stream.="</td>";
        }
        $stream.="<td ".$bgclr."  colspan=".$kolspan.">".$_SESSION['lang']['jumlah']."</td></tr><tr>";
        foreach($tgltgl as $ar => $isi)
        {
			// Cek Hari Libur
			$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
			$resLibur = fetchData($qLibur);
			$libur = false;
			if(!empty($resLibur)) $libur = true;

            $qwe=date('D', strtotime($isi));
            ;
            //if($qwe=='Sun'){ 
			if($libur){
				$stream.="<td width=5px rowspan='2' ".$bgclr."><font color=red>".$_SESSION['lang']['upah']."</font></td>"; 
                $stream.="<td width=5px colspan='3' ".$bgclr."><font color=red>".$_SESSION['lang']['premi']."</font></td>";
                $stream.="<td width=5px colspan='3' ".$bgclr."><font color=red>".$_SESSION['lang']['denda']."</font></td>"; 
                $stream.="<td width=5px rowspan='2' ".$bgclr."><font color=red>".$_SESSION['lang']['total']."</font></td>";
                $stream.="<td width=5px rowspan='2' ".$bgclr."><font color=red>".$_SESSION['lang']['premi'].' Sebelum Denda'."</font></td>";
                $stream.="<td width=5px rowspan='2' ".$bgclr."><font color=red>".$_SESSION['lang']['premi'].' '.($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."</font></td>";
			}else{
                $stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['upah']."</td>"; 
                $stream.="<td width=5px colspan='3' ".$bgclr.">".$_SESSION['lang']['premi']."</td>"; 
                $stream.="<td width=5px colspan='3' ".$bgclr.">".$_SESSION['lang']['denda']."</td>"; 
                $stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['total']."</td>";
                $stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['premi'].' Sebelum Denda'."</td>";
                $stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['premi'].' '.($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."</td>";
			}
		}
		$stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['upah']."</td>"; 
		$stream.="<td width=5px colspan='3' ".$bgclr.">".$_SESSION['lang']['premi']."</td>"; 
		$stream.="<td width=5px colspan='3' ".$bgclr.">".$_SESSION['lang']['denda']."</td>"; 
		$stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['total']."</td>";
		$stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['premi'].' Sebelum Denda'."</td>";
		$stream.="<td width=5px rowspan='2' ".$bgclr.">".$_SESSION['lang']['premi'].' '.($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."</td></tr><tr>";
        foreach($tgltgl as $ar => $isi)
        {
			// Cek Hari Libur
			$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
			$resLibur = fetchData($qLibur);
			$libur = false;
			if(!empty($resLibur)) $libur = true;

            $qwe=date('D', strtotime($isi));
            ;
            //if($qwe=='Sun'){ 
			if($libur){
				$stream.="<td width=5px  ".$bgclr."><font color=red>".'basis'."</font></td>";
                $stream.="<td width=5px  ".$bgclr."><font color=red>".$_SESSION['lang']['brondol']."</font></td>";
                $stream.="<td width=5px  ".$bgclr."><font color=red>".$_SESSION['lang']['total']."</font></td>";
                $stream.="<td width=5px  ".$bgclr."><font color=red>".$_SESSION['lang']['upah']."</font></td>"; 
                $stream.="<td width=5px  ".$bgclr."><font color=red>".$_SESSION['lang']['penalti']."</font></td>";
                $stream.="<td width=5px  ".$bgclr."><font color=red>".$_SESSION['lang']['total']."</font></td>";
			}else{
				$stream.="<td width=5px  ".$bgclr.">".'basis'."</td>";
				$stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['brondol']."</td>";
				$stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['total']."</td>";
                $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['upah']."</td>"; 
                $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['penalti']."</td>";
                $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['total']."</td>";
			}
        }
		$stream.="<td width=5px  ".$bgclr.">".'basis'."</td>";
        $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['brondol']."</td>";
        $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['total']."</td>";
        $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['upah']."</td>"; 
        $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['penalti']."</td>";
        $stream.="<td width=5px  ".$bgclr.">".$_SESSION['lang']['total']."</td></tr>";
        
        $stream.="</thead>
        <tbody>";
        # preview: nampilin data ================================================================================
        $stupahkerja[$tangval]=Array();
        $stpremibasis[$tangval]=Array();
        $stpremibrondol[$tangval]=Array();
        $stupahpremi[$tangval]=Array();
        $stupahpenalty[$tangval]=Array();
        $strupiahpenalty[$tangval]=Array();
        $stdenda[$tangval]=Array();
        $stpremihitung[$tangval]=Array();
        $stpremibersih[$tangval]=Array();
        $sttotal[$tangval]=Array();

        $gtupahkerja[$tangval]=Array();
        $gtpremibasis[$tangval]=Array();
        $gtpremibrondol[$tangval]=Array();
        $gtupahpremi[$tangval]=Array();
        $gtupahpenalty[$tangval]=Array();
        $gtrupiahpenalty[$tangval]=Array();
        $gtdenda[$tangval]=Array();
        $gtpremihitung[$tangval]=Array();
        $gtpremibersih[$tangval]=Array();
        $gttotal[$tangval]=Array();

        foreach($karyawannik as $mdid=>$mdval)
        {
			$no+=1;
			$stream.="<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$namamandor[$mdid]."</td>
			<td>".$mandornik[$mdid]."</td>
			<td>".$jabatanmandor[$mdid]."</td>
			<td>".$subbagianmandor[$mdid]."</td>";
			$no2=0;
			foreach($mdval as $kpid=>$kpval)
			{
				$no2+=1;
				if($no2!=1){
					$stream.="<td class=rowcontent colspan='5'></td>";
				}
				$stream.="
				<td>".$no2."</td>
				<td>".$namakaryawan[$mdid][$kpid]."</td>
				<td>".$karyawannik[$mdid][$kpid]."</td>
				<td>".$jabatankaryawan[$mdid][$kpid]."</td>
				<td>".$subbagiankaryawan[$mdid][$kpid]."</td>";
				foreach($tgltgl as $key=>$tangval)
				{
					//if($jmlpemanen[$mdid][$tangval] == 0){
					//	$jmlpemanen[$mdid][$tangval]=1;
					//}

					// Cek Hari Libur
					$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$tangval."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
					$resLibur = fetchData($qLibur);
					$libur = false;
					if(!empty($resLibur)) $libur = true;
					if($libur and $periode>='2022-08'){
						if($jmlpemanen[$mdid][$tangval] <= 10){
							$jmlpemanen[$mdid][$tangval]=10;
						}
					}else{
						if($jmlpemanen[$mdid][$tangval] == 0){
							$jmlpemanen[$mdid][$tangval]=1;
						}
					}

					$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($upahkerja[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($premibasis[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($premibrondol[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($upahpremi[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($upahpenalty[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($rupiahpenalty[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($denda[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($total[$mdid][$kpid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval],2)."</td>";
                    $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format(($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval])-($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval]*$grading[$mdid][$tangval])+($dendatph[$mdid][$tangval]/$jmlpemanen[$mdid][$tangval]),2)."</td>";
                   
                    $ttupahkerja+=$upahkerja[$mdid][$kpid][$tangval];
                    $ttpremibasis+=$premibasis[$mdid][$kpid][$tangval];
                    $ttpremibrondol+=$premibrondol[$mdid][$kpid][$tangval];
                    $ttupahpremi+=$upahpremi[$mdid][$kpid][$tangval];
                    $ttupahpenalty+=$upahpenalty[$mdid][$kpid][$tangval];
                    $ttrupiahpenalty+=$rupiahpenalty[$mdid][$kpid][$tangval];
                    $ttdenda+=$denda[$mdid][$kpid][$tangval];
                    $ttpremihitung+=$premihitung[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $ttpremibersih+=$premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $ttgrpremibersih+=($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval])-($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval]*$grading[$mdid][$tangval])+($dendatph[$mdid][$tangval]/$jmlpemanen[$mdid][$tangval]);
                    $tttotal+=$total[$mdid][$kpid][$tangval];
                    
                    $stupahkerja[$tangval]+=$upahkerja[$mdid][$kpid][$tangval];
                    $stpremibasis[$tangval]+=$premibasis[$mdid][$kpid][$tangval];
                    $stpremibrondol[$tangval]+=$premibrondol[$mdid][$kpid][$tangval];
                    $stupahpremi[$tangval]+=$upahpremi[$mdid][$kpid][$tangval];
                    $stupahpenalty[$tangval]+=$upahpenalty[$mdid][$kpid][$tangval];
                    $strupiahpenalty[$tangval]+=$rupiahpenalty[$mdid][$kpid][$tangval];
                    $stdenda[$tangval]+=$denda[$mdid][$kpid][$tangval];
                    $stpremihitung[$tangval]+=$premihitung[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $stpremibersih[$tangval]+=$premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $stgrpremibersih[$tangval]+=($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval])-($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval]*$grading[$mdid][$tangval])+($dendatph[$mdid][$tangval]/$jmlpemanen[$mdid][$tangval]);
                    $sttotal[$tangval]+=$total[$mdid][$kpid][$tangval];

                    $gtupahkerja[$tangval]+=$upahkerja[$mdid][$kpid][$tangval];
                    $gtpremibasis[$tangval]+=$premibasis[$mdid][$kpid][$tangval];
                    $gtpremibrondol[$tangval]+=$premibrondol[$mdid][$kpid][$tangval];
                    $gtupahpremi[$tangval]+=$upahpremi[$mdid][$kpid][$tangval];
                    $gtupahpenalty[$tangval]+=$upahpenalty[$mdid][$kpid][$tangval];
                    $gtrupiahpenalty[$tangval]+=$rupiahpenalty[$mdid][$kpid][$tangval];
                    $gtdenda[$tangval]+=$denda[$mdid][$kpid][$tangval];
                    $gtpremihitung[$tangval]+=$premihitung[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $gtpremibersih[$tangval]+=$premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval];
                    $gtgrpremibersih[$tangval]+=($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval])-($premibersih[$mdid][$kpid][$tangval]/$jmlpemanen[$mdid][$tangval]*$grading[$mdid][$tangval])+($dendatph[$mdid][$tangval]/$jmlpemanen[$mdid][$tangval]);
                    $gttotal[$tangval]+=$total[$mdid][$kpid][$tangval];
				}
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttupahkerja,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttpremibasis,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttpremibrondol,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttupahpremi,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttupahpenalty,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttrupiahpenalty,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttdenda,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($tttotal,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttpremibersih,2)."</td>";
				$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$kpid."','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgrpremibersih,2)."</td>";
				$stream.="</tr><tr class=rowcontent>";

				$ttupahkerja=0;
				$ttpremibasis=0;
				$ttpremibrondol=0;
				$ttupahpremi=0;
				$ttupahpenalty=0;
				$ttrupiahpenalty=0;
				$ttdenda=0;
				$ttpremihitung=0;
				$ttpremibersih=0;
				$ttgrpremibersih=0;
				$tttotal=0;
			}  
			# preview: nampilin sub total ================================================================================
			//$stream.="<thead class=rowheader>
			//$stream.="</tr><tr bgcolor='#FEDEFE'>";
			$stream.="
			<td bgcolor='#FEDEFE'>".$no."</td>
			<td bgcolor='#FEDEFE'>".$namamandor[$mdid]."</td>
			<td bgcolor='#FEDEFE'>".$mandornik[$mdid]."</td>
			<td bgcolor='#FEDEFE'>".$jabatanmandor[$mdid]."</td>
			<td bgcolor='#FEDEFE'>".$subbagianmandor[$mdid]."</td>
			<td bgcolor='#FEDEFE' colspan=5 align='center'>Total</td>";
			foreach($tgltgl as $ar => $tangval)
			{
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stupahkerja[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stpremibasis[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stpremibrondol[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stupahpremi[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stupahpenalty[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($strupiahpenalty[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stdenda[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($sttotal[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stpremibersih[$tangval],2)."</td>";
				$stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($stgrpremibersih[$tangval],2)."</td>";
                
				$ttstupahkerja+=$stupahkerja[$tangval];
				$ttstpremibasis+=$stpremibasis[$tangval];
				$ttstpremibrondol+=$stpremibrondol[$tangval];
				$ttstupahpremi+=$stupahpremi[$tangval];
				$ttstupahpenalty+=$stupahpenalty[$tangval];
				$ttstrupiahpenalty+=$strupiahpenalty[$tangval];
				$ttstdenda+=$stdenda[$tangval];
				$ttstpremihitung+=$stpremihitung[$tangval];
				$ttstpremibersih+=$stpremibersih[$tangval];
				$ttstgrpremibersih+=$stgrpremibersih[$tangval];
				$ttsttotal+=$sttotal[$tangval];

				$stupahkerja[$tangval]=0;
				$stpremibasis[$tangval]=0;
				$stpremibrondol[$tangval]=0;
				$stupahpremi[$tangval]=0;
				$stupahpenalty[$tangval]=0;
				$strupiahpenalty[$tangval]=0;
				$stdenda[$tangval]=0;
				$stpremihitung[$tangval]=0;
				$stpremibersih[$tangval]=0;
				$stgrpremibersih[$tangval]=0;
				$sttotal[$tangval]=0;
			}
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstupahkerja,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstpremibasis,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstpremibrondol,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstupahpremi,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstupahpenalty,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstrupiahpenalty,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstdenda,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttsttotal,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstpremibersih,2)."</td>";
            $stream.="<td bgcolor='#FEDEFE' title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttstgrpremibersih,2)."</td>";
			//$stream.="</tr>";
			//$stream.="</thead>";
      
			$ttstupahkerja=0;
            $ttstpremibasis=0;
            $ttstpremibrondol=0;
            $ttstupahpremi=0;
            $ttstupahpenalty=0;
			$ttstrupiahpenalty=0;
            $ttstdenda=0;
            $ttstpremihitung=0;
            $ttstpremibersih=0;
            $ttstgrpremibersih=0;
            $ttsttotal=0;
		}

        # preview: nampilin Grand total ================================================================================
        $stream.="<thead class=rowheader>
        <tr>
        <td colspan=10 align='center'>Grand Total</td>";
        foreach($tgltgl as $ar => $tangval)
        {
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtupahkerja[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtpremibasis[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtpremibrondol[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtupahpremi[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtupahpenalty[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtrupiahpenalty[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtdenda[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gttotal[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtpremibersih[$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$tangval."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($gtgrpremibersih[$tangval],2)."</td>";
                
			$ttgtupahkerja+=$gtupahkerja[$tangval];
			$ttgtpremibasis+=$gtpremibasis[$tangval];
			$ttgtpremibrondol+=$gtpremibrondol[$tangval];
			$ttgtupahpremi+=$gtupahpremi[$tangval];
			$ttgtupahpenalty+=$gtupahpenalty[$tangval];
			$ttgtrupiahpenalty+=$gtrupiahpenalty[$tangval];
			$ttgtdenda+=$gtdenda[$tangval];
			$ttgtpremihitung+=$gtpremihitung[$tangval];
			$ttgtpremibersih+=$gtpremibersih[$tangval];
			$ttgtgrpremibersih+=$gtgrpremibersih[$tangval];
			$ttgttotal+=$gttotal[$tangval];
        }
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtupahkerja,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtpremibasis,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtpremibrondol,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtupahpremi,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtupahpenalty,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtrupiahpenalty,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtdenda,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgttotal,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtpremibersih,2)."</td>";
        $stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('','','".$periode."','".$kdOrg."','".$afdId."','".$pengawas."',event)>".@number_format($ttgtgrpremibersih,2)."</td>";
		$stream.="</tr></tbody></table>";
        
		$ttgtupahkerja=0;
        $ttgtpremibasis=0;
        $ttgtpremibrondol=0;
        $ttgtupahpremi=0;
        $ttgtupahpenalty=0;
		$ttgtrupiahpenalty=0;
        $ttgtdenda=0;
        $ttgtpremibersih=0;
        $ttgtgrpremibersih=0;
        $ttgttotal=0;

	switch($proses)
	{
        case'preview':
          echo $stream;
        break;
        case 'excel':
            $nop_="Laporan_premi_".($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0)
            {
                $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
//                $handle=fopen("tempExcel/".$nop_.".xls",'w');
//                if(!fwrite($handle,$stream))
//                {
//                    echo "<script language=javascript1.2>
//                    parent.window.alert('Can't convert to excel format');
//                    </script>";
//                    exit;
//                }
//                else
//                {
//                    echo "<script language=javascript1.2>
//                    window.location='tempExcel/".$nop_.".xls';
//                    </script>";
//                }
//                fclose($handle);
            }           
            break;
	}    
?>