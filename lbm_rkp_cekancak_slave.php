<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$tahun=checkPostGet('tahun','');
	if($proses=='preview' or $proses=='excel'){
		if($kodept==''){
			exit('Warning : PT tidak boleh kosong...!');
		}
		if($tahun==''){
			exit('Warning : Tahun tidak boleh kosong...!');
		}
	}
	$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
	$namapt=$optNm[$kodept];
	#Filter parameter where 
	$where="";
	if($kodept!=''){
		$where.=" and left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		$whspb.=" and substr(a.nospb,9,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where.=" and a.kodeorg like '".$kodeunit."%'";
		$whspb.=" and a.blok like '".$kodeunit."%'";
	}
	if($tahun!=''){
		$where.=" and a.tanggal like '".$tahun."%'";
		$whspb.=" and a.nospb like '%".$tahun."'";
	}

	switch($proses){
		case 'getUnit':
			$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where substr(kodeorganisasi,4,1)='E' and LENGTH(kodeorganisasi)=4 
					and tipe='KEBUN' and detail='1' and induk like '".$kodept."%' order by namaorganisasi";
			$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			}else{
				$optUnit="";
			}
			while($dUnit=mysql_fetch_assoc($qUnit)){
				$optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['namaorganisasi']."</option>";
			}
			echo $optUnit;
			exit;
	}

	#ambil data Kontrol Panen
	$str="select a.*,e.bjr,round(if(d.luasareaproduktif=0,0,d.jumlahpokok/d.luasareaproduktif),0) as sph
			from ".$dbname.".kebun_cekancak a 
			left join ".$dbname.".setup_blok d on d.kodeorg=a.kodeorg
			left join ".$dbname.".kebun_5bjr e on e.kodeorg=a.kodeorg and e.tahunproduksi=year(a.tanggal)
			where True ".$where." 
			order by a.tanggal,a.kodeorg";
	//exit('Warning: '.$str);
	$xunit=array();
	$perbl=date('m');
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$bln=substr($bar->tanggal,5,2);
		$perbl=$bln;
		$div=substr($bar->kodeorg,0,6);
		$xunit[substr($bar->kodeorg,0,4)]=substr($bar->kodeorg,0,4);
		$pkk[$div][$bln]+=$bar->pokok;
		$bt[$div][$bln]+=$bar->janjang;
		$brd[$div][$bln]+=$bar->brondolan;
		$lossesbt[$div][$bln]+=$bar->janjang/$bar->pokok*$bar->bjr*$bar->sph;
		$lossesbrd[$div][$bln]+=$bar->brondolan/$bar->pokok*0.014*$bar->sph;
		$lossesjml[$div][$bln]+=($bar->janjang/$bar->pokok*$bar->bjr*$bar->sph)+($bar->brondolan/$bar->pokok*0.014*$bar->sph);
	}

	//Ambil unit untuk kriteria Where 
	asort($xunit);
	$whr="";
	$whafd="";
	$x=0;
	foreach($xunit as $xun=>$vun){
		$x+=1;
		if($x==1){
			$whr.=" and (a.kodeorganisasi like '".$vun."0%' or a.kodeorganisasi like '".$vun."1%'";
			$whafd.=" and (a.blok like '".$vun."0%' or a.blok like '".$vun."1%'";
		}else{
			$whr.=" or a.kodeorganisasi like '".$vun."0%' or a.kodeorganisasi like '".$vun."1%'";
			$whafd.=" or a.blok like '".$vun."0%' or a.blok like '".$vun."1%'";
		}
	}
	if($whr!=""){
		$whr.=")";
		$whafd.=")";
	}else{
		$whr=" and a.induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		$whafd=" and left(a.blok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}

	#ambil data SPATBS Panen
	$str="select a.* from ".$dbname.".kebun_spbdt a 
			where True ".$whspb." ".$whafd." 
			order by a.nospb,a.blok";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$bln=substr($bar->nospb,15,2);
		$div=substr($bar->blok,0,6);
		$xwil=substr($bar->blok,0,4);
		$bijjg[$div][$bln]+=$bar->jjg;
		$bikgwb[$div][$bln]+=$bar->kgwb;
		$sdjjg[$div]+=$bar->jjg;
		$sdkgwb[$div]+=$bar->kgwb;
		$ttbijjg[$wil][$bln]+=$bar->jjg;
		$ttbikgwb[$wil][$bln]+=$bar->kgwb;
		$ttsdjjg[$wil]+=$bar->jjg;
		$ttsdkgwb[$wil]+=$bar->kgwb;
		$gtbijjg[$bln]+=$bar->jjg;
		$gtbikgwb[$bln]+=$bar->kgwb;
		$gtsdjjg+=$bar->jjg;
		$gtsdkgwb+=$bar->kgwb;
	}

	#preview: nampilin header ================================================================================
	$bgclr=" align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr=" bgcolor='#DEDEDE' align='center' ";
	}
	//$namabulan = array("","Januari","Pebruari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
	$namabulan = array("","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
				<thead class=rowheader>
					<tr>
						<td rowspan=3 width='70px'".$bgclr.">".$_SESSION['lang']['divisi']."</td>
						<td rowspan=3 width='70px'".$bgclr.">".$_SESSION['lang']['luas']." (Ha)</td>
						<td rowspan=3 width='70px'".$bgclr.">".$_SESSION['lang']['jmlpokok']."</td>";
	for ($x = 1; $x <= 12; $x++){
		$stream.="	<td colspan=3 width='150px'".$bgclr.">".$namabulan[$x]."</td>";
	}
	$stream.="	<td colspan=3 width='150px'".$bgclr.">Total</td>
				<td colspan=4 width='100px'".$bgclr.">".$_SESSION['lang']['produksi']."</td>";
	$stream.="</tr><tr>";
	for ($x = 1; $x <= 12; $x++){
		$stream.="		<td colspan=3".$bgclr.">Kg/Ha</td>";
	}
	$stream.="			<td colspan=3".$bgclr.">Kg/Ha</td>";
	$stream.="			<td colspan=2".$bgclr.">Kg</td>";
	$stream.="</tr><tr>";
	for ($x = 1; $x <= 12; $x++){
		$stream.="		<td width='50px'".$bgclr.">BT</td>";
		$stream.="		<td width='50px'".$bgclr.">Brd</td>";
		$stream.="		<td width='50px'".$bgclr.">Jml</td>";
	}
	$stream.="			<td width='50px'".$bgclr.">BT</td>";
	$stream.="			<td width='50px'".$bgclr.">Brd</td>";
	$stream.="			<td width='50px'".$bgclr.">Jml</td>";
	$stream.="			<td width='50px'".$bgclr.">BI</td>";
	$stream.="			<td width='50px'".$bgclr.">sd BI</td>";
	$stream.="		</tr>
				</thead><tbody>";

	#ambil data Divisi
	$str  ="select a.kodeorganisasi,a.namaorganisasi,b.luasdivisi,b.jmlpokok from ".$dbname.".organisasi a 
			left join (select left(kodeorg,6) as divisi,SUM(luasareaproduktif) as luasdivisi,SUM(jumlahpokok) as jmlpokok 
						from ".$dbname.".setup_blok GROUP BY left(kodeorg,6)) b on b.divisi=a.kodeorganisasi
			where a.tipe='AFDELING' ".$whr." ORDER BY kodeorganisasi";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$ttluasdiv=array();
	$ttjmlpkk=array();
	$gtluasdiv=0;
	$gtjmlpkk=0;
	$stlbt=array();
	$stlbrd=array();
	$stljml=array();
	$ttlbt=array();
	$ttlbrd=array();
	$ttljml=array();
	$gtlbt=array();
	$gtlbrd=array();
	$gtljml=array();
	$subdiv="";
	$no=0;
	$nu=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		if($no>1 and $subdiv!=substr($bar->kodeorganisasi,0,4)){
			$stream.="<tr>";
			$stream.="	<td bgcolor='pink' width='50px'>".$subdiv."</td>";
			$stream.="	<td bgcolor='pink' width='50px' align='right'>".number_format($ttluasdiv[$subdiv],2)."</td>";
			$stream.="	<td bgcolor='pink' width='50px' align='right'>".number_format($ttjmlpkk[$subdiv],0)."</td>";
			$ttluasdiv[$subdiv]=0;
			$ttjmlpkk[$subdiv]=0;
			for ($x = 1; $x <= 12; $x++){
				$bln=sprintf("%02d",$x);
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttlbt[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttlbt[$bln],2)."</td>";
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttlbrd[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttlbrd[$bln],2)."</td>";
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttljml[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttljml[$bln],2)."</td>";
				$ttstbt+=$ttlbt[$bln];
				$ttstbrd+=$ttlbrd[$bln];
				$ttstjml+=$ttljml[$bln];
				$ttlbt[$bln]=0;
				$ttlbrd[$bln]=0;
				$ttljml[$bln]=0;
			}
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstbt>0 ? "" : " style='color:pink' ").">".number_format($ttstbt,2)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstbrd>0 ? "" : " style='color:pink' ").">".number_format($ttstbrd,2)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstjml>0 ? "" : " style='color:pink' ").">".number_format($ttstjml,2)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttbikgwb[$subdiv][$perbl]>0 ? "" : " style='color:pink' ").">".number_format($ttbikgwb[$subdiv][$perbl],0)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttsdkgwb[$subdiv]>0 ? "" : " style='color:pink' ").">".number_format($ttsdkgwb[$subdiv],0)."</td>";
			$ttstbt=0;
			$ttstbrd=0;
			$ttstjml=0;
			$stream.="</tr>";
			$nu+=1;
		}
		$subdiv=substr($bar->kodeorganisasi,0,4);
		$div=$bar->kodeorganisasi;
		$stream.="<tr class=rowcontent>";
		$stream.="	<td width='50px'>".$div."</td>";
		$stream.="	<td width='50px' align='right'>".number_format($bar->luasdivisi,2)."</td>";
		$stream.="	<td width='50px' align='right'>".number_format($bar->jmlpokok,0)."</td>";
		$ttluasdiv[$subdiv]+=$bar->luasdivisi;
		$ttjmlpkk[$subdiv]+=$bar->jmlpokok;
		$gtluasdiv+=$bar->luasdivisi;
		$gtjmlpkk+=$bar->jmlpokok;
		for ($x = 1; $x <= 12; $x++){
			$bln=sprintf("%02d",$x);
			$stream.="	<td width='50px' align='right'.".($lossesbt[$div][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($lossesbt[$div][$bln],2)."</td>";
			$stream.="	<td width='50px' align='right'.".($lossesbrd[$div][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($lossesbrd[$div][$bln],2)."</td>";
			$stream.="	<td width='50px' align='right'.".($lossesjml[$div][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($lossesjml[$div][$bln],2)."</td>";
			$stlbt[$div]+=$lossesbt[$div][$bln];
			$stlbrd[$div]+=$lossesbrd[$div][$bln];
			$stljml[$div]+=$lossesjml[$div][$bln];
			$ttlbt[$bln]+=$lossesbt[$div][$bln];
			$ttlbrd[$bln]+=$lossesbrd[$div][$bln];
			$ttljml[$bln]+=$lossesjml[$div][$bln];
			$gtlbt[$bln]+=$lossesbt[$div][$bln];
			$gtlbrd[$bln]+=$lossesbrd[$div][$bln];
			$gtljml[$bln]+=$lossesjml[$div][$bln];
		}
		$stream.="		<td width='50px' align='right'.".($stlbt[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stlbt[$div],2)."</td>";
		$stream.="		<td width='50px' align='right'.".($stlbrd[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stlbrd[$div],2)."</td>";
		$stream.="		<td width='50px' align='right'.".($stljml[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stljml[$div],2)."</td>";
		$stream.="		<td width='50px' align='right'.".($bikgwb[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($bikgwb[$div][$perbl],0)."</td>";
		$stream.="		<td width='50px' align='right'.".($sdkgwb[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($sdkgwb[$div],0)."</td>";
		$stlbt[$div]=0;
		$stlbrd[$div]=0;
		$stljml[$div]=0;
		$stream.="</tr>";
	}
	// Sub Total
	if($nu>0){
		$stream.="<tr>";
		$stream.="	<td bgcolor='pink' width='50px'>".$subdiv."</td>";
		$stream.="	<td bgcolor='pink' width='50px' align='right'>".number_format($ttluasdiv[$subdiv],2)."</td>";
		$stream.="	<td bgcolor='pink' width='50px' align='right'>".number_format($ttjmlpkk[$subdiv],0)."</td>";
		$ttluasdiv[$subdiv]=0;
		$ttjmlpkk[$subdiv]=0;
		for ($x = 1; $x <= 12; $x++){
			$bln=sprintf("%02d",$x);
			$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttlbt[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttlbt[$bln],2)."</td>";
			$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttlbrd[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttlbrd[$bln],2)."</td>";
			$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttljml[$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttljml[$bln],2)."</td>";
			$ttstbt+=$ttlbt[$bln];
			$ttstbrd+=$ttlbrd[$bln];
			$ttstjml+=$ttljml[$bln];
			$ttlbt[$bln]=0;
			$ttlbrd[$bln]=0;
			$ttljml[$bln]=0;
		}
		$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstbt>0 ? "" : " style='color:pink' ").">".number_format($ttstbt,2)."</td>";
		$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstbrd>0 ? "" : " style='color:pink' ").">".number_format($ttstbrd,2)."</td>";
		$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstjml>0 ? "" : " style='color:pink' ").">".number_format($ttstjml,2)."</td>";
		$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttbikgwb[$subdiv][$perbl]>0 ? "" : " style='color:pink' ").">".number_format($ttbikgwb[$subdiv][$perbl],0)."</td>";
		$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttsdkgwb[$subdiv]>0 ? "" : " style='color:pink' ").">".number_format($ttsdkgwb[$subdiv],0)."</td>";
		$ttstbt=0;
		$ttstbrd=0;
		$ttstjml=0;
		$stream.="</tr>";
	}
	// Grand Total
	$stream.="<tr>";
	$stream.="	<td bgcolor='cyan' width='50px'>TOTAL</td>";
	$stream.="	<td bgcolor='cyan' width='50px' align='right'>".number_format($gtluasdiv,2)."</td>";
	$stream.="	<td bgcolor='cyan' width='50px' align='right'>".number_format($gtjmlpkk,0)."</td>";
	for ($x = 1; $x <= 12; $x++){
		$bln=sprintf("%02d",$x);
		$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtlbt[$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtlbt[$bln],2)."</td>";
		$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtlbrd[$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtlbrd[$bln],2)."</td>";
		$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtljml[$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtljml[$bln],2)."</td>";
		$gtstbt+=$gtlbt[$bln];
		$gtstbrd+=$gtlbrd[$bln];
		$gtstjml+=$gtljml[$bln];
		$gtlbt[$bln]=0;
		$gtlbrd[$bln]=0;
		$gtljml[$bln]=0;
	}
	$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstbt>0 ? "" : " style='color:cyan' ").">".number_format($gtstbt,2)."</td>";
	$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstbrd>0 ? "" : " style='color:cyan' ").">".number_format($gtstbrd,2)."</td>";
	$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstjml>0 ? "" : " style='color:cyan' ").">".number_format($gtstjml,2)."</td>";
	$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtbikgwb[$perbl]>0 ? "" : " style='color:pink' ").">".number_format($gtbikgwb[$perbl],0)."</td>";
	$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtsdkgwb>0 ? "" : " style='color:pink' ").">".number_format($gtsdkgwb,0)."</td>";
	$gtstbt=0;
	$gtstbrd=0;
	$gtstjml=0;
	$stream.="</tr>";
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Rekap Kontrol '.$_SESSION['lang']['panen'];
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
?>