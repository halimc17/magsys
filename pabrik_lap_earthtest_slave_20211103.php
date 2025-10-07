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
	$mesin=$_POST['mesin'];
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
	if($mesin=='')$mesin=$_GET['mesin'];
	if($proses=='')$proses=$_GET['proses'];
	$thnd=explode("-",$periode);

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdOrg;

	// determine begin end =========================================================================
	$lok=substr($kdOrg,0,4);
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

	#ambil data Earth Test
	if($mesin!=''){
		$whr=" and a.kodemesin='".$mesin."'";
	}else{
		$whr=" ";
	}
	$strtb="select kodemesin,a.ukur1,a.ukur2,a.ukur3,a.standard from ".$dbname.".pabrik_earthtest a 
			where a.kodeorg = '".$kdOrg."'
			".$whr." 
			and a.standard<>0 and tanggal<'".$periode."-01-01'
			order by a.kodemesin,a.tanggal,a.standard";
	$restb=mysql_query($strtb);
	while($bartb=mysql_fetch_object($restb)){
		$tebalsisa[$bartb->kodemesin]=$bartb->ukur1;
	}
	$str="select a.kodeorg,a.kodemesin,a.tanggal,avg(a.standard) as standard,avg(a.ukur1) as ukur1,avg(a.ukur2) as ukur2,avg(a.ukur3) as ukur3
		,b.namaorganisasi from ".$dbname.".pabrik_earthtest a 
		LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
		where a.kodeorg = '".$kdOrg."'
		".$whr." 
		and a.tanggal like '".$periode."%'
	GROUP BY a.kodeorg,a.kodemesin,left(a.tanggal,7)
	ORDER BY a.kodeorg,a.kodemesin,left(a.tanggal,7)
	";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$kodemesin=Array();
	while($bar=mysql_fetch_object($res)){
		$bulan=substr($bar->tanggal,5,2);
		$kodeorg[$bar->kodemesin]=$bar->kodeorg;
		$kodemesin[$bar->kodemesin]=$bar->kodemesin;
		if($bar->standard!=0){
			$tebalsisa[$bar->kodemesin]=$bar->ukur1;
		}
		$standard[$bar->kodemesin][$bulan]=$bar->standard;
		$ukur1[$bar->kodemesin][$bulan]=$bar->ukur1;
		$ukur2[$bar->kodemesin][$bulan]=$bar->ukur2;
		$ukur3[$bar->kodemesin][$bulan]=$bar->ukur3;
		$jmlukur=0;
		if($bar->ukur1>0){
			$jmlukur+=1;
		}
		if($bar->ukur2>0){
			$jmlukur+=1;
		}
		if($bar->ukur3>0){
			$jmlukur+=1;
		}
		$ukurrata2[$bar->kodemesin][$bulan]=0;
		if($jmlukur>0){
			$ukurrata2[$bar->kodemesin][$bulan]=round(($bar->ukur1+$bar->ukur2+$bar->ukur3)/$jmlukur,2);
		}
		$selisih[$bar->kodemesin][$bulan]=$ukurrata2[$bar->kodemesin][$bulan]-$standard[$bar->kodemesin][$bulan];
	}
	$stream="";
	$kolspan=6;
	$stkolspan=12*$kolspan;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$stream="<h2>Laporan Earth Test ".$kdOrg." Periode: ".$periode."</h2>";
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr." rowspan=2>No</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['unit']."</td>
        <td ".$bgclr." rowspan=2 width='300px'>".$_SESSION['lang']['lokasi']."</td>";

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
		$stream.=(($libur) ? "<font color=red>" : "").$isi."</font>"; 
		$stream.="</td>";
	}
	//$stream.="<td ".$bgclr."  colspan=3>"."Last Test"."</td></tr><tr>";
	$stream.="</tr><tr>";
	for ($x = 1; $x <= 12; $x++){
		// Cek Hari Libur
		//$qLibur = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$isi."' and keterangan='libur' and kebun in ('GLOBAL','".$kdOrg."')";
		//$resLibur = fetchData($qLibur);
		$libur = false;
		//if(!empty($resLibur)) $libur = true;

		//$qwe=date('D', strtotime($isi));
		//if($qwe=='Sun'){ 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."Std"."</font></td>"; 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."1"."</font></td>"; 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."2"."</font></td>"; 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."3"."</font></td>"; 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."Rata Rata"."</font></td>"; 
		$stream.="<td align=center width=5px rowspan='1' ".$bgclr.">".(($libur) ? "<font color=red>" : "")."Slsh"."</font></td>"; 
	}
    $stream.="</tr></thead><tbody>";

	# preview: nampilin data ================================================================================
	$namasts="";
	foreach($kodemesin as $mdid=>$mdval)
    {
		$no+=1;
		$stream.="<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$kodeorg[$mdid]."</td>
			<td width='300px' title='Click untuk cetak detail.' style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$periode."','".$kdOrg."','excel',event)>".$kodemesin[$mdid]."</td>";
			$no2=0;
		//foreach($tgltgl as $key=>$tangval){
		for ($tangval = 1; $tangval <= 12; $tangval++){
			$bln=$periode.'-'.sprintf("%02d",$tangval);
			$bulan=sprintf("%02d",$tangval);
			//$tanggalkemarin=date('Y-m-d', strtotime("-1 day", strtotime($tangval)));
			if($standard[$mdid][$tangval]!=0){
				//$tebalsisa[$mdid]=$tebal1[$mdid][$tangval];
			}else{
				//$tebalsisa[$mdid]=max($tebal1[$mdid][$tangval],$tebal2[$mdid][$tangval],$tebal3[$mdid][$tangval]);
			}
			$tebalkecil[$mdid][$tangval]=($tebal1[$mdid][$tangval]<$tebal2[$mdid][$tangval] ? $tebal1[$mdid][$tangval] : $tebal2[$mdid][$tangval]);
			$tebalkecil[$mdid][$tangval]=($tebal3[$mdid][$tangval]<$tebalkecil[$mdid][$tangval] ? $tebal3[$mdid][$tangval] : $tebalkecil[$mdid][$tangval]);
			//tebalpersen=($tebalsisa==0 ? 0 : $tebalkecil[$mdid][$tangval]/$tebalsisa[$mdid]*100);
			$stream.="<td title='Click untuk cetak detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($standard[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk cetak detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($ukur1[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($ukur2[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk cetak detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($ukur3[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($ukurrata2[$mdid][$bulan],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$mdid."','".$bln."','".$kdOrg."','',event)>".@number_format($selisih[$mdid][$bulan],2)."</td>";
		}
	}
	switch($proses){
        case'preview':
          echo $stream;
        break;

        case 'excel':
            $nop_="Laporan_Earth_Test_".$kdOrg."_".$periode."__".date("His");
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
