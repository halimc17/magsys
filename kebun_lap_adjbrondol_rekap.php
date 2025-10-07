<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodept=checkPostGet('kodept1','');
	$kodeunit=checkPostGet('kodeunit1','');
	$jenis=checkPostGet('jenis1','');
	$tahun=checkPostGet('tahun1','');
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

	#ambil data Adjustmen Panen
	$str  ="select left(kodeorg,6) as divisi,jenis,left(tanggal,7) as periode,SUM(kg) as kg,count(tanggal) as BA
			,count(DISTINCT tanggal) as noBA
			from ".$dbname.".kebun_adjbrondol
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
	//$namabulan = array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
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
		$stream.="		<td colspan=26 width='100px'".$bgclr.">".$jns."</td>";
	}
	$stream.="</tr><tr>";
	foreach($xjenis as $xjns=>$jns){
		for ($x = 1; $x <= 12; $x++){
			$stream.="	<td colspan=2 width='100px'".$bgclr.">".$namabulan[$x]."</td>";
		}
		$stream.="	<td colspan=2 width='100px'".$bgclr.">Total</td>";
	}
	$stream.="</tr><tr>";
	foreach($xjenis as $xjns=>$vjns){
		for ($x = 1; $x <= 12; $x++){
			$stream.="		<td width='50px'".$bgclr.">Kg</td>";
			$stream.="		<td width='50px'".$bgclr.">Rec</td>";
		}
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
	$ttkg=array();
	$ttBA=array();
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
					$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttkg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttkg[$jns][$bln])."</td>";
					$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttBA[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttBA[$jns][$bln])."</td>";
					$ttstkg+=$ttkg[$jns][$bln];
					$ttstBA+=$ttBA[$jns][$bln];
					$ttkg[$jns][$bln]=0;
					$ttBA[$jns][$bln]=0;
				}
				$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstkg>0 ? "" : " style='color:pink' ").">".number_format($ttstkg)."</td>";
				$stream.="		<td bgcolor='pink' width='50px' align='right'.".($ttstBA>0 ? "" : " style='color:pink' ").">".number_format($ttstBA)."</td>";
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
				$stream.="	<td width='50px' align='right'.".($kg[$div][$jns][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($kg[$div][$jns][$bln])."</td>";
				$stream.="	<td width='50px' align='right'.".($BA[$div][$jns][$bln]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($BA[$div][$jns][$bln])."</td>";
				$stkg[$div]+=$kg[$div][$jns][$bln];
				$stBA[$div]+=$BA[$div][$jns][$bln];
				$ttkg[$jns][$bln]+=$kg[$div][$jns][$bln];
				$ttBA[$jns][$bln]+=$BA[$div][$jns][$bln];
				$gtkg[$jns][$bln]+=$kg[$div][$jns][$bln];
				$gtBA[$jns][$bln]+=$BA[$div][$jns][$bln];
			}
			$stream.="		<td width='50px' align='right'.".($stkg[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stkg[$div])."</td>";
			$stream.="		<td width='50px' align='right'.".($stBA[$div]>0 ? "" : " style='color:#e0ecfc' ").">".number_format($stBA[$div])."</td>";
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
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttkg[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttkg[$jns][$bln])."</td>";
				$stream.="	<td bgcolor='pink' width='50px' align='right'.".($ttBA[$jns][$bln]>0 ? "" : " style='color:pink' ").">".number_format($ttBA[$jns][$bln])."</td>";
				$ttstkg+=$ttkg[$jns][$bln];
				$ttstBA+=$ttBA[$jns][$bln];
				$ttkg[$jns][$bln]=0;
				$ttBA[$jns][$bln]=0;
			}
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
			$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtkg[$jns][$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtkg[$jns][$bln])."</td>";
			$stream.="	<td bgcolor='cyan' width='50px' align='right'.".($gtBA[$jns][$bln]>0 ? "" : " style='color:cyan' ").">".number_format($gtBA[$jns][$bln])."</td>";
			$gtstkg+=$gtkg[$jns][$bln];
			$gtstBA+=$gtBA[$jns][$bln];
			$gtkg[$jns][$bln]=0;
			$gtBA[$jns][$bln]=0;
		}
		$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstkg>0 ? "" : " style='color:cyan' ").">".number_format($gtstkg)."</td>";
		$stream.="		<td bgcolor='cyan' width='50px' align='right'.".($gtstBA>0 ? "" : " style='color:cyan' ").">".number_format($gtstBA)."</td>";
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