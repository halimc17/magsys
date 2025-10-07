<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses = $_POST['proses'];
	$pabrik = $_POST['kdOrg'];
	$stasiun = $_POST['stasiun'];
	$tahun = $_POST['periode'];

	if($proses=='')$proses=$_GET['proses'];
	if($pabrik=='')$pabrik=$_GET['kdOrg'];
	if($pabrik==''||$pabrik=='false'){
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			exit('Warning: Unit harus dipilih!');
		}else{
			if(substr($_SESSION['empl']['lokasitugas'],3,1)=='M'){
				$pabrik=$_SESSION['empl']['lokasitugas'];
			}else{
				exit('Warning: Unit bukan Pabrik!');
			}
		}
	}
	if($stasiun=='')$stasiun=$_GET['stasiun'];
	if($tahun=='')$tahun=$_GET['periode'];
	//$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	//$arrPost=array("0"=>"Not Posted","1"=>"Posting");

	if($proses=='getStasiun'){
		$sStasiun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pabrik."' and detail='1' order by kodeorganisasi";
		$qStasiun=mysql_query($sStasiun) or die(mysql_error($conn));
		$optStasiun="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rStasiun=mysql_fetch_assoc($qStasiun)){
			$optStasiun.="<option value=".$rStasiun['kodeorganisasi'].">[".$rStasiun['kodeorganisasi'].']-'.$rStasiun['namaorganisasi']."</option>";
		}
		echo $optStasiun;
		exit;
	}

	$where='';
	if($pabrik!=''){
		$where.=" and a.kodeorg='".$pabrik."'";
	}else{
		exit('Warning : Unit Pabrik tidak boleh kosong...!');
	}
	if($stasiun!=''){
		$where.=" and a.kodestasiun='".$stasiun."'";
	}
	if($tahun!=''){
		$where.=" and a.tanggal like '".$tahun."%'";
	}else{
		exit('Warning : Tahun tidak boleh kosong...!');
	}

	if($proses=='excel'){
		$border="border=1";
	}else{
		$border="border=0";
	}
	//bgcolor=#CCCCCC border='1'

	$stream="<table cellspacing='1' $border class='sortable'>";
	$stream.="<thead><tr class=rowheader>
	            <td rowspan=2 align=center>No</td>
			    <td rowspan=2 align=center>".$_SESSION['lang']['kode']."</td>
			    <td rowspan=2 align=center>".$_SESSION['lang']['station']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['tipe']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jan']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['peb']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['mar']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['apr']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['mei']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jun']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['jul']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['agt']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['sep']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['okt']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['nov']."</td>
	            <td colspan=5 align=center>".$_SESSION['lang']['dec']."</td>";
	$stream.="</tr><tr class=rowheader>";
	for($i=1;$i<=12;$i++){
		$stream.="	<td align=center width='11px;'>1</td>
					<td align=center width='11px;'>2</td>
					<td align=center width='11px;'>3</td>
					<td align=center width='11px;'>4</td>
					<td align=center width='11px;'>5</td>";
	}
	$stream.="</tr></thead><tbody>";
	//Data Plan
	$sData="select a.kodeorg,a.kodestasiun,b.namaorganisasi as namastasiun,a.jenis,a.tanggal
			FROM ".$dbname.".pabrik_preventifpanel a 
			LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodestasiun 
			WHERE jenis='Plan' ".$where." 
			ORDER BY a.kodeorg,a.kodestasiun,a.jenis,a.tanggal";
	//exit('Warning: '.$sData);
	$qData=mysql_query($sData) or die (mysql_error($conn));	
	while($dData=mysql_fetch_assoc($qData)){
		$tglnilai=$dData['tanggal'];
		$tglakhir=date('t', strtotime($tglnilai));
		$bulanke=substr($tglnilai,5,2);
		$mingguke=1;
		for($i=1;$i<=$tglakhir;$i++){
			if(date('Y-m-d', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==$tglnilai){
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
					$mingguke+=1;
	            }
    			break;
		    }else{
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
		        	if($i!=1){
						$mingguke+=1;
		            }
				}
			}
		}
		$mingguke=($mingguke==6 ? 5 : $mingguke);
		$kodestasiun[$dData['kodestasiun']]=$dData['kodestasiun'];
		$namastasiun[$dData['kodestasiun']]=$dData['namastasiun'];
		$centang[$dData['kodestasiun']][$dData['jenis']][$bulanke][$mingguke]='&radic;';
	}

	//Data Real
	$sData="select a.kodeorg,a.kodestasiun,b.namaorganisasi as namastasiun,a.jenis,a.tanggal
			FROM ".$dbname.".pabrik_preventifpanel a 
			LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodestasiun 
			WHERE jenis='Real' ".$where." 
			ORDER BY a.kodeorg,a.kodestasiun,a.jenis,a.tanggal";
	//exit('Warning: '.$sData);
	$qData=mysql_query($sData) or die (mysql_error($conn));	
	while($dData=mysql_fetch_assoc($qData)){
		$tglnilai=$dData['tanggal'];
		$tglakhir=date('t', strtotime($tglnilai));
		$bulanke=substr($tglnilai,5,2);
		$mingguke=1;
		for($i=1;$i<=$tglakhir;$i++){
			if(date('Y-m-d', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==$tglnilai){
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
					$mingguke+=1;
	            }
    			break;
		    }else{
				if(date('N', strtotime(substr($tglnilai,0,8).sprintf("%02d",$i)))==7){
		        	if($i!=1){
						$mingguke+=1;
		            }
				}
			}
		}
		$mingguke=($mingguke==6 ? 5 : $mingguke);
		$kodestasiun[$dData['kodestasiun']]=$dData['kodestasiun'];
		$namastasiun[$dData['kodestasiun']]=$dData['namastasiun'];
		$centang[$dData['kodestasiun']][$dData['jenis']][$bulanke][$mingguke]='&radic;';
	}
	$no=0;
	foreach($kodestasiun as $kdstasiun=>$kdst){
		$no+=1;
		$jenis='Plan';
		$stream.="<tr class=rowcontent>
		            <td rowspan=2 align=center>".$no."</td>";
		$stream.="
				<td rowspan=2 align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$tahun."','".$pabrik."','".$namastasiun[$kdstasiun]."','','excel',event)\">".$kdstasiun."</td>
				<td rowspan=2 align=left title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$tahun."','".$pabrik."','".$namastasiun[$kdstasiun]."','','',event)\">".$namastasiun[$kdstasiun]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$tahun."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$jenis."</td>";
		for($i=1;$i<=12;$i++){
			$blnke=sprintf("%02d",$i);
			$bln=$tahun.'-'.$blnke;
			$stream.="
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][1]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][2]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][3]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][4]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][5]."</td>";
		}
		$stream.="</tr>";
		$jenis='Real';
		$stream.="<tr class=rowcontent>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$tahun."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$jenis."</td>";
		for($i=1;$i<=12;$i++){
			$blnke=sprintf("%02d",$i);
			$bln=$tahun.'-'.$blnke;
			$stream.="
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][1]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][2]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][3]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][4]."</td>
				<td align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=\"showpopup('".$kdstasiun."','".$bln."','".$pabrik."','".$namastasiun[$kdstasiun]."','".$jenis."','',event)\">".$centang[$kdstasiun][$jenis][$blnke][5]."</td>";
		}
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";

	############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
	switch($proses){
		case 'preview':
			echo $stream;
			break;

		case 'excel':
			$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			$tglSkrg=date("Ymd");
			$nop_="LAPORAN_PREDICTIVE_PLAN_vs_REAL_".$tglSkrg;
			$judul="<h3>LAPORAN PREDICTIVE PLAN vs REAL";
			$judul.="<BR>".($stasiun=='' ? $nmOrg[$stasiun] : $nmOrg[$stasiun])."";
			$judul.="<BR>Periode : ".$tahun."</h3>";
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
