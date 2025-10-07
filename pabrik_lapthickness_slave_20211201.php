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
	$strtb="select kodemesin,a.tebal1,a.tebal2,a.tebal3,a.tipeservice from ".$dbname.".pabrik_thickness a 
			where a.kodeorg = '".$kdOrg."'
			".$whr." 
			and a.tipeservice<>0 and tanggal<'".$periode."-01-01'
			order by a.kodemesin,a.tanggal,a.tipeservice";
	$restb=mysql_query($strtb);
	while($bartb=mysql_fetch_object($restb)){
		$tebalsisa[$bartb->kodemesin]=$bartb->tebal1;
	}
	$str="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_thickness a 
	LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
	LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
	where a.kodeorg = '".$kdOrg."'
		".$whr." 
		and a.tanggal like '".$periode."%'
	ORDER BY a.kodemesin,a.tanggal,a.tipeservice,a.keterangan
	";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$kodemesin=Array();
	while($bar=mysql_fetch_object($res)){
		$bulan=substr($bar->tanggal,5,2);
		//exit('Warning: '.$bulan.' '.$bar->tanggal);
		$kodemesin[$bar->kodemesin]=$bar->kodemesin;
		$namamesin[$bar->kodemesin]=$bar->namamesin;
		$namastasiun[$bar->kodemesin]=$bar->namastasiun;
		if($bar->tipeservice!=0){
			$tebalsisa[$bar->kodemesin]=$bar->tebal1;
		}
		$max_tebal[$bar->kodemesin][$bulan]=$bar->tebal1;
		$max_tebal[$bar->kodemesin][$bulan]=($bar->tebal2>$max_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal2 : $max_tebal[$bar->kodemesin][$bulan]);
		$max_tebal[$bar->kodemesin][$bulan]=($bar->tebal3>$max_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal3 : $max_tebal[$bar->kodemesin][$bulan]);
		$max_tebal[$bar->kodemesin][$bulan]=($bar->tebal4>$max_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal4 : $max_tebal[$bar->kodemesin][$bulan]);
		$max_tebal[$bar->kodemesin][$bulan]=($bar->tebal5>$max_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal5 : $max_tebal[$bar->kodemesin][$bulan]);
		$max_tebal[$bar->kodemesin][$bulan]=($bar->tebal6>$max_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal6 : $max_tebal[$bar->kodemesin][$bulan]);
		if(is_null($min_tebal[$bar->kodemesin][$bulan])){
			if($bar->tebal1>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal1;
			}elseif($bar->tebal2>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal2;
			}elseif($bar->tebal3>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal3;
			}elseif($bar->tebal4>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal4;
			}elseif($bar->tebal5>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal5;
			}elseif($bar->tebal6>0){
				$min_tebal[$bar->kodemesin][$bulan]=$bar->tebal6;
			}else{
				$min_tebal[$bar->kodemesin][$bulan]=0;
			}
		}
		if($bar->tebal1>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal1<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal1 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		if($bar->tebal2>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal2<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal2 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		if($bar->tebal3>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal3<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal3 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		if($bar->tebal4>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal4<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal4 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		if($bar->tebal5>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal5<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal5 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		if($bar->tebal6>0 and $min_tebal[$bar->kodemesin][$bulan]>0){
			$min_tebal[$bar->kodemesin][$bulan]=($bar->tebal6<$min_tebal[$bar->kodemesin][$bulan] ?  $bar->tebal6 : $min_tebal[$bar->kodemesin][$bulan]);
		}
		//exit('Warning: '.);
		if(is_null($min_akhir[$bar->kodemesin])){
			if($bar->tebal1>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal1;
			}elseif($bar->tebal2>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal2;
			}elseif($bar->tebal3>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal3;
			}elseif($bar->tebal4>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal4;
			}elseif($bar->tebal5>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal5;
			}elseif($bar->tebal6>0){
				$min_akhir[$bar->kodemesin]=$bar->tebal6;
			}else{
				$min_akhir[$bar->kodemesin]=0;
			}
		}
		if($bar->tebal1>0 and $min_akhir[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal1<$min_akhir[$bar->kodemesin] ?  $bar->tebal1 : $min_akhir[$bar->kodemesin]);
		}
		if($bar->tebal2>0 and $min_akhir[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal2<$min_akhir[$bar->kodemesin] ?  $bar->tebal2 : $min_akhir[$bar->kodemesin]);
		}
		if($bar->tebal3>0 and $min_tebal[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal3<$min_akhir[$bar->kodemesin] ?  $bar->tebal3 : $min_akhir[$bar->kodemesin]);
		}
		if($bar->tebal4>0 and $min_tebal[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal4<$min_akhir[$bar->kodemesin] ?  $bar->tebal4 : $min_akhir[$bar->kodemesin]);
		}
		if($bar->tebal5>0 and $min_tebal[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal5<$min_akhir[$bar->kodemesin] ?  $bar->tebal5 : $min_akhir[$bar->kodemesin]);
		}
		if($bar->tebal6>0 and $min_tebal[$bar->kodemesin]>0){
			$min_akhir[$bar->kodemesin]=($bar->tebal6<$min_akhir[$bar->kodemesin] ?  $bar->tebal6 : $min_akhir[$bar->kodemesin]);
		}
	}
	//exit('Warning: '.$tebalsisa['SKDM020204'].'  -  '.$min_akhir['SKDM020204']);
	$stream="";
	$kolspan=2;
	//$stkolspan=(substr($tanggalSampai,8,2)+1)*$kolspan;
	$stkolspan=13*$kolspan+1;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$stream="<h2>Laporan Thickness ".$kdOrg." Periode: ".$periode."</h2>";
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

	for ($x = 1; $x <= 12; $x++){
		// Cek Hari Libur
		//$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
		//$resLibur = fetchData($qLibur);
		$libur = false;
		//if(!empty($resLibur)) $libur = true;
		$stream.="<td width=5px  ".$bgclr."  colspan=".$kolspan.">";
		//$qwe=date('D', strtotime($isi));
		//if($qwe=='Sun')
		$isi=date('F',strtotime($periode.'-'.sprintf("%02d",$x).'-'.sprintf("%02d",$x)));
		if($libur)
			$stream.="<font color=red>".$isi."</font>"; 
		else 
			$stream.=$isi; 
		$stream.="</td>";
	}
	//$stream.="<td ".$bgclr."  colspan=2>".$_SESSION['lang']['jumlah']."</td></tr><tr>";
	$stream.="<td ".$bgclr."  colspan=3>"."Last Test"."</td></tr><tr>";
	for ($x = 1; $x <= 12; $x++){
		// Cek Hari Libur
		//$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
		//$resLibur = fetchData($qLibur);
		$libur = false;
		//if(!empty($resLibur)) $libur = true;

		//$qwe=date('D', strtotime($isi));
		//if($qwe=='Sun'){ 
		if($libur){
			$stream.="<td width=5px rowspan='1' ".$bgclr."><font color=red>"."Max"."</font></td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr."><font color=red>"."Min"."</font></td>"; 
		}else{
			$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Max"."</td>"; 
			$stream.="<td width=5px rowspan='1' ".$bgclr.">"."Min"."</td>"; 
		}
	}
	$stream.="<td width=5px rowspan='1' ".$bgclr.">"."New"."</td>"; 
	$stream.="<td width=5px colspan='1' ".$bgclr.">"."Min"."</td>"; 
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
			<td width='300px' title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$periode."','".$kdOrg."','".$stasiun."','excel',event)>".$namamesin[$mdid]."</td>";
			$no2=0;
		//foreach($tgltgl as $key=>$tangval){
		for ($tangval = 1; $tangval <= 12; $tangval++){
			$bln=$periode.'-'.sprintf("%02d",$tangval);
			$bulan=sprintf("%02d",$tangval);
			//$tanggalkemarin=date('Y-m-d', strtotime("-1 day", strtotime($tangval)));
			if($tipeservice[$mdid][$tangval]!=0){
				//$tebalsisa[$mdid]=$tebal1[$mdid][$tangval];
			}else{
				//$tebalsisa[$mdid]=max($tebal1[$mdid][$tangval],$tebal2[$mdid][$tangval],$tebal3[$mdid][$tangval]);
			}
			$tebalkecil[$mdid][$tangval]=($tebal1[$mdid][$tangval]<$tebal2[$mdid][$tangval] ? $tebal1[$mdid][$tangval] : $tebal2[$mdid][$tangval]);
			$tebalkecil[$mdid][$tangval]=($tebal3[$mdid][$tangval]<$tebalkecil[$mdid][$tangval] ? $tebal3[$mdid][$tangval] : $tebalkecil[$mdid][$tangval]);
			//tebalpersen=($tebalsisa==0 ? 0 : $tebalkecil[$mdid][$tangval]/$tebalsisa[$mdid]*100);
			$stream.="<td title='Click untuk cetak detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','".$stasiun."','excel',event)>".@number_format($max_tebal[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','".$stasiun."','',event)>".@number_format($min_tebal[$mdid][$bulan],2)."</td>";
		}
		$stream.="<td title='Click untuk cetak detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$periode."','".$kdOrg."','".$stasiun."','excel',event)>".@number_format(($tebalsisa[$mdid]),2)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$periode."','".$kdOrg."','".$stasiun."','',event)>".@number_format(($min_akhir[$mdid]),2)."</td>";
		if($tebalsisa[$mdid]==0 or is_null($tebalsisa[$mdid])){
			$tebalpersen=0;
		}else{
			$tebalpersen=$min_akhir[$mdid]/$tebalsisa[$mdid]*100;
		}
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$periode."','".$kdOrg."','".$stasiun."','',event)>".@number_format($tebalpersen,2)."</td>";
	}
	if($proses!='excel'){
		$stream.="<tr class=rowcontent>
				<td colspan=3 align=center title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=showpopup('','".$periode."','".$kdOrg."','','excel',event)>SELURUHNYA</td>";
		for ($tangval = 1; $tangval <= 12; $tangval++){
			$bln=$periode.'-'.sprintf("%02d",$tangval);
			$bulan=sprintf("%02d",$tangval);
			$stream.="<td title='Click untuk cetak detail.' align=center style=\"cursor: pointer\" 	onclick=showpopup('".$stasiun."','".$bln."','".$kdOrg."','','excel',event)><img src=images/skyblue/excel.jpg></td>";
			$stream.="<td title='Click untuk melihat detail.' align=center style=\"cursor: pointer\" onclick=showpopup('".$stasiun."','".$bln."','".$kdOrg."','','',event)><img src=images/zoom.png></td>";
		}
		$stream.="<td title='Click untuk melihat detail.' align=center style=\"cursor: pointer\" onclick=showpopup('".$stasiun."','".$periode."','".$kdOrg."','','excel',event)><img src=images/skyblue/excel.jpg></td>";
		$stream.="<td title='Click untuk melihat detail.' align=center style=\"cursor: pointer\" onclick=showpopup('','".$periode."','".$kdOrg."','','',event)><img src=images/zoom.png></td>";
		$stream.="<td title='Click untuk melihat detail.' align=center style=\"cursor: pointer\" onclick=showpopup('".$stasiun."','".$periode."','".$kdOrg."','','',event)>&nbsp;</td>";
	}
	switch($proses){
        case'preview':
          echo $stream;
        break;

        case 'excel':
            $nop_="Laporan_Thickness_".$kdOrg."_".$periode."__".date("His");
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
