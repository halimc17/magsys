<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept','');
	$kodeunit=checkPostGet('kodeunit','');
	$jenis=checkPostGet('jenis','');
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
		$where.=" and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	}
	if($kodeunit!=''){
		$where.=" and kodeorg like '".$kodeunit."%'";
	}
	if($jenis!=''){
		$where.=" and jenis = '".$jenis."'";
	}
	if($tahun!=''){
		$where.=" and tanggal like '".$tahun."%'";
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

	#ambil data Adjustmen Panen
	$str  ="select left(kodeorg,6) as divisi,jenis,left(tanggal,7) as periode,SUM(janjang) as jjg,SUM(kg) as kg,count(tanggal) as BA
			,count(DISTINCT tanggal) as noBA
			from ".$dbname.".kebun_adjpanen
			where True ".$where."
			GROUP BY left(kodeorg,6),jenis,left(tanggal,7)
			ORDER BY left(kodeorg,6),jenis,left(tanggal,7)";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res)){
		$jns=$bar->jenis;
		$bln=substr($bar->periode,5,2);
		$div=$bar->divisi;
		$xunit[substr($bar->divisi,0,4)]=substr($bar->divisi,0,4);
		$xjenis[$jns]=$bar->jenis;
		$jjg[$div][$jns][$bln]=$bar->jjg;
		$kg[$div][$jns][$bln]=$bar->kg;
		$BA[$div][$jns][$bln]=$bar->BA;
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

	if(empty($xjenis)){
		$stream.="<table cellspacing='1' border='0' class='sortable'>
					<tbody>
						<tr class=rowcontent>
							<td ".$bgclr.">Data tidak ada...! </td>";
		//exit('Warning: Data tidak ada...!');
	}else{
	$stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
				<thead class=rowheader>
					<tr>
						<td rowspan=3 width='70px'".$bgclr.">".$_SESSION['lang']['divisi']."</td>";
	asort($xjenis);
	foreach($xjenis as $xjns=>$jns){
		$stream.="		<td colspan=39 width='100px'".$bgclr.">".$jns."</td>";
	}
	$stream.="</tr><tr>";
	foreach($xjenis as $xjns=>$jns){
		for ($x = 1; $x <= 12; $x++){
			$stream.="	<td colspan=3 width='100px'".$bgclr.">".$namabulan[$x]."</td>";
		}
		$stream.="	<td colspan=3 width='100px'".$bgclr.">Total</td>";
	}
	$stream.="</tr><tr>";
	foreach($xjenis as $xjns=>$vjns){
		for ($x = 1; $x <= 12; $x++){
			$stream.="		<td width='50px'".$bgclr.">Jjg</td>";
			$stream.="		<td width='50px'".$bgclr.">Kg</td>";
			$stream.="		<td width='50px'".$bgclr.">Rec</td>";
		}
		$stream.="			<td width='50px'".$bgclr.">Jjg</td>";
		$stream.="			<td width='50px'".$bgclr.">Kg</td>";
		$stream.="			<td width='50px'".$bgclr.">Rec</td>";
	}
	$stream.="		</tr>
				</thead><tbody>";
	//Ambil unit untuk kriteria Where 
	$whr="tipe='AFDELING' and (";
	asort($xunit);
	$x=0;
	foreach($xunit as $xun=>$vun){
		$x+=1;
		if($x==1){
			$whr.="kodeorganisasi like '".$vun."0%' or kodeorganisasi like '".$vun."1%'";
		}else{
			$whr.=" or kodeorganisasi like '".$vun."0%' or kodeorganisasi like '".$vun."1%'";
		}
	}
	$whr.=")";
	#ambil data Adjustmen Panen
	$str  ="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whr." ORDER BY kodeorganisasi";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$ttjjg=array();
	$ttkg=array();
	$ttBA=array();
	$gtjjg=array();
	$gtkg=array();
	$gtBA=array();
	$subdiv="";
	$no=0;
	$nu=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		if($no>1 and $subdiv!=substr($bar->kodeorganisasi,0,4)){
			$stream.="<tr>";
			$stream.="	<td bgcolor='pink' width='50px'>".$subdiv."</td>";
			foreach($xjenis as $xjns=>$jns){
				for ($x = 1; $x <= 12; $x++){
					$bln=sprintf("%02d",$x);
					$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttjjg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttjjg[$jns][$bln])."</td>";
					$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttkg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttkg[$jns][$bln])."</td>";
					$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttBA[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttBA[$jns][$bln])."</td>";
					$ttstjjg+=$ttjjg[$jns][$bln];
					$ttstkg+=$ttkg[$jns][$bln];
					$ttstBA+=$ttBA[$jns][$bln];
					$ttjjg[$jns][$bln]=0;
					$ttkg[$jns][$bln]=0;
					$ttBA[$jns][$bln]=0;
				}
				$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstjjg>0 ? "" : " style='color:pink' ").">".number_format($ttstjjg)."</td>";
				$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstkg>0 ? "" : " style='color:pink' ").">".number_format($ttstkg)."</td>";
				$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstBA>0 ? "" : " style='color:pink' ").">".number_format($ttstBA)."</td>";
				$ttstjjg=0;
				$ttstkg=0;
				$ttstBA=0;
			}
			$stream.="</tr>";
			$nu+=1;
		}
		$subdiv=substr($bar->kodeorganisasi,0,4);
		$div=$bar->kodeorganisasi;
		$stream.="<tr class=rowcontent>";
		$stream.="	<td width='50px'>".$div."</td>";
		foreach($xjenis as $xjns=>$jns){
			for ($x = 1; $x <= 12; $x++){
				$bln=sprintf("%02d",$x);
				$stream.="	<td width='50px' align='right'.".($jjg[$div][$jns][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($jjg[$div][$jns][$bln])."</td>";
				$stream.="	<td width='50px' align='right'.".($kg[$div][$jns][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($kg[$div][$jns][$bln])."</td>";
				$stream.="	<td width='50px' align='right'.".($BA[$div][$jns][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($BA[$div][$jns][$bln])."</td>";
				$stjjg[$div]+=$jjg[$div][$jns][$bln];
				$stkg[$div]+=$kg[$div][$jns][$bln];
				$stBA[$div]+=$BA[$div][$jns][$bln];
				$ttjjg[$jns][$bln]+=$jjg[$div][$jns][$bln];
				$ttkg[$jns][$bln]+=$kg[$div][$jns][$bln];
				$ttBA[$jns][$bln]+=$BA[$div][$jns][$bln];
				$gtjjg[$jns][$bln]+=$jjg[$div][$jns][$bln];
				$gtkg[$jns][$bln]+=$kg[$div][$jns][$bln];
				$gtBA[$jns][$bln]+=$BA[$div][$jns][$bln];
			}
			$stream.="		<td width='50px' align='right'.".($stjjg[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stjjg[$div])."</td>";
			$stream.="		<td width='50px' align='right'.".($stkg[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stkg[$div])."</td>";
			$stream.="		<td width='50px' align='right'.".($stBA[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stBA[$div])."</td>";
			$stjjg[$div]=0;
			$stkg[$div]=0;
			$stBA[$div]=0;
		}
		$stream.="</tr>";
	}
	// Sub Total
	if($nu>0){
		$stream.="<tr>";
		$stream.="	<td bgcolor='pink' width='50px'>".$subdiv."</td>";
		foreach($xjenis as $xjns=>$jns){
			for ($x = 1; $x <= 12; $x++){
				$bln=sprintf("%02d",$x);
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttjjg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttjjg[$jns][$bln])."</td>";
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttkg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttkg[$jns][$bln])."</td>";
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttBA[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttBA[$jns][$bln])."</td>";
				$ttstjjg+=$ttjjg[$jns][$bln];
				$ttstkg+=$ttkg[$jns][$bln];
				$ttstBA+=$ttBA[$jns][$bln];
				$ttjjg[$jns][$bln]=0;
				$ttkg[$jns][$bln]=0;
				$ttBA[$jns][$bln]=0;
			}
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstjjg>0 ? "" : " style='color:pink' ").">".number_format($ttstjjg)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstkg>0 ? "" : " style='color:pink' ").">".number_format($ttstkg)."</td>";
			$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstBA>0 ? "" : " style='color:pink' ").">".number_format($ttstBA)."</td>";
			$ttstjjg=0;
			$ttstkg=0;
			$ttstBA=0;
		}
		$stream.="</tr>";
	}
	// Grand Total
	$stream.="<tr>";
	$stream.="	<td bgcolor='cyan' width='50px'>TOTAL</td>";
	foreach($xjenis as $xjns=>$jns){
		for ($x = 1; $x <= 12; $x++){
			$bln=sprintf("%02d",$x);
			$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtjjg[$jns][$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtjjg[$jns][$bln])."</td>";
			$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtkg[$jns][$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtkg[$jns][$bln])."</td>";
			$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtBA[$jns][$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtBA[$jns][$bln])."</td>";
			$gtstjjg+=$gtjjg[$jns][$bln];
			$gtstkg+=$gtkg[$jns][$bln];
			$gtstBA+=$gtBA[$jns][$bln];
			$gtjjg[$jns][$bln]=0;
			$gtkg[$jns][$bln]=0;
			$gtBA[$jns][$bln]=0;
		}
		$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstjjg>0 ? "" : " style='color:cyan' ").">".number_format($gtstjjg)."</td>";
		$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstkg>0 ? "" : " style='color:cyan' ").">".number_format($gtstkg)."</td>";
		$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstBA>0 ? "" : " style='color:cyan' ").">".number_format($gtstBA)."</td>";
		$gtstjjg=0;
		$gtstkg=0;
		$gtstBA=0;
	}
	}
	$stream.="</tr>";
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Rekap Adjustment '.$_SESSION['lang']['panen'];
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