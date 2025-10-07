<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$periode=$_POST['periode'];
	$lksiTgs=$_SESSION['empl']['lokasitugas'];
	$kdOrg=$_POST['kdOrg'];
	$stasiun=$_POST['stasiun'];
	if($periode=='')$periode=$_GET['periode'];
	if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
	if($kdOrg==''||$kdOrg=='false'){
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			exit('Warning: Unit harus dipilih!');
		}else{
			if(substr($_SESSION['empl']['lokasitugas'],3,1)=='M'){
				$kdOrg=$_SESSION['empl']['lokasitugas'];
			}else{
				exit('Warning: Unit bukan Pabrik!');
			}
		}
	}
	if($stasiun=='')$afdId=$_GET['afdId'];
	if($proses=='')$proses=$_GET['proses'];
	$thnd=explode("-",$periode);

	if($proses=='getStasiun'){
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."' and tipe='STATION' and detail='1' order by namaorganisasi";
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		$optStasiun="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optStasiun.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optStasiun;
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
	$sDatez = "select DISTINCT(periode),tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode = '".$periode."'";
	$qDatez=mysql_query($sDatez) or die(mysql_error($conn));
	while($rDatez=mysql_fetch_assoc($qDatez)){
		$tanggalMulai=$rDatez['tanggalmulai'];
		$tanggalSampai=$rDatez['tanggalsampai'];
	}

	function dates_inbetween($date1, $date2){
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
	$tgltgl = dates_inbetween($tanggalMulai, $tanggalSampai);

	#ambil data Thickness
	if($stasiun!=''){
		$whr=" and a.kodemesin like '".$stasiun."%'";
	}else{
		$whr=" ";
	}
	$str="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_thickness a 
	LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
	LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
	where a.kodeorg = '".$kdOrg."'
		".$whr." 
		and a.tanggal like '".$periode."%'
	ORDER BY a.kodemesin,a.tanggal,a.tipeservice
	";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$kodemesin=Array();

	while($bar=mysql_fetch_object($res)){
		$strtb="select a.tebal1,a.tebal2,a.tebal3,a.tipeservice from ".$dbname.".pabrik_thickness a where a.kodemesin like '".$bar->kodemesin."%' and a.tipeservice<>0 and tanggal<'".$periode."-01'
				order by a.kodemesin,a.tanggal,a.tipeservice";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$tebalsisa[$bar->kodemesin]=0;
		while($bartb=mysql_fetch_object($restb)){
			$tebalsisa[$bar->kodemesin]=$bartb->tebal1;
		}
		$kodemesin[$bar->kodemesin]=$bar->kodemesin;
		$namamesin[$bar->kodemesin]=$bar->namamesin;
		$namastasiun[$bar->kodemesin]=$bar->namastasiun;
		$tanggal[$bar->kodemesin]=$bar->tanggal;
		$tebal1[$bar->kodemesin][$bar->tanggal]=$bar->tebal1;
		$tebal2[$bar->kodemesin][$bar->tanggal]=$bar->tebal2;
		$tebal3[$bar->kodemesin][$bar->tanggal]=$bar->tebal3;
		$tipeservice[$bar->kodemesin][$bar->tanggal]=$bar->tipeservice;
	}
	//exit('Warning: '.$tanggalSampai.'  -  '.substr($tanggalSampai,9,2));
	$stream="";
	$kolspan=4;
	$stkolspan=(substr($tanggalSampai,8,2)+1)*$kolspan;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$stream="Laporan Thickness ".$kdOrg." Periode: ".$periode;
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr." rowspan=2>No</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['kode']."</td>
        <td ".$bgclr." rowspan=2 width='300px'>".$_SESSION['lang']['nmmesin']."</td>";

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
	$stream.="<td ".$bgclr."  colspan=2>".$_SESSION['lang']['jumlah']."</td></tr><tr>";
	foreach($tgltgl as $ar => $isi)
    {
		// Cek Hari Libur
		$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
		$resLibur = fetchData($qLibur);
		$libur = false;
		if(!empty($resLibur)) $libur = true;

		$qwe=date('D', strtotime($isi));
		//if($qwe=='Sun'){ 
		if($libur){
			$stream.="<td width=5px rowspan='1' ".$bgclr."><font color=red>"."Ukur 1"."</font></td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr."><font color=red>"."Ukur 2"."</font></td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr."><font color=red>"."Ukur 3"."</font></td>"; 
			$stream.="<td width=5px colspan='1' ".$bgclr."><font color=red>"."(%)"."</font></td>";
		}else{
			$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Ukur 1"."</td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Ukur 2"."</td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Ukur 3"."</td>"; 
			$stream.="<td width=5px colspan='1' ".$bgclr.">"."(%)"."</td>"; 
		}
	}
	$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Thickness"."</td>"; 
	$stream.="<td width=5px colspan='1' ".$bgclr.">"."(%)"."</td>"; 
    $stream.="</thead><tbody>";

	# preview: nampilin data ================================================================================
	$namasts="";
	foreach($kodemesin as $mdid=>$mdval)
    {
		$no+=1;
		if($namastasiun[$mdid]!=$namasts){
			$stream.="<tr class=rowcontent>
				<td>".$no."</td>
				<td>".substr($kodemesin[$mdid],0,6)."</td>
				<td width='300px'>".$namastasiun[$mdid]."</td>
				<td colspan=".$stkolspan."></td>";
			$namasts=$namastasiun[$mdid];
		}
		$stream.="<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$kodemesin[$mdid]."</td>
			<td width='300px'>".$namamesin[$mdid]."</td>";
			$no2=0;
		$tebalakhir=$tebalsisa[$mdid];
		foreach($tgltgl as $key=>$tangval){
			$tanggalkemarin=date('Y-m-d', strtotime("-1 day", strtotime($tangval)));
			if($tipeservice[$mdid][$tangval]!=0){
				$tebalsisa[$mdid]=$tebal1[$mdid][$tangval];
			}else{
				//$tebalsisa[$mdid]=max($tebal1[$mdid][$tangval],$tebal2[$mdid][$tangval],$tebal3[$mdid][$tangval]);
			}
			$tebalkecil[$mdid][$tangval]=($tebal1[$mdid][$tangval]<$tebal2[$mdid][$tangval] ? $tebal1[$mdid][$tangval] : $tebal2[$mdid][$tangval]);
			$tebalkecil[$mdid][$tangval]=($tebal3[$mdid][$tangval]<$tebalkecil[$mdid][$tangval] ? $tebal3[$mdid][$tangval] : $tebalkecil[$mdid][$tangval]);
			if($tebalkecil[$mdid][$tangval]!=0){
				$tebalakhir=($tebalakhir<$tebalkecil[$mdid][$tangval] ? $tebalakhir : $tebalkecil[$mdid][$tangval]);
			}
			$tebalpersen=($tebalsisa==0 ? 0 : $tebalkecil[$mdid][$tangval]/$tebalsisa[$mdid]*100);
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebal1[$mdid][$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebal2[$mdid][$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebal3[$mdid][$tangval],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebalpersen,2)."</td>";
		}
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebalakhir,2)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$tangval."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebalakhir/$tebalsisa[$mdid]*100,2)."</td>";
	}

	switch($proses){
        case'preview':
          echo $stream;
        break;

        case 'excel':
            $nop_="Laporan_HM_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0)
            {
                //$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                //gzwrite($gztralala, $stream);
                //gzclose($gztralala);
                // echo "<script language=javascript1.2>
                //    window.location='tempExcel/".$nop_.".xls.gz';
                //    </script>";
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
