<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$periode=$_POST['periode'];
	$kdOrg=$_POST['kdOrg'];
	$afdId=$_POST['afdId'];
	if($proses=='')$proses=$_GET['proses'];
	if($periode=='')$periode=$_GET['periode'];
	if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
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

	if($proses=='getSubUnit'){
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."' and tipe='AFDELING' and detail='1' order by namaorganisasi";
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

	#ambil data spatbs
	$whr.="";
	if($kdOrg!=''){
		$whr.=" and b.kodeorg = '".$kdOrg."' ";
	}
	if($afdId!=''){
		$whr.=" and a.blok like '".$afdId."%' ";
	}
	if($periode!=''){
		$whr.=" and b.tanggal like '".$periode."%' ";
	}else{
		exit('Warning: Periode harus dipilih...!!');
	}
	$str="select b.kodeorg,a.blok,c.namaorganisasi as namablok,left(b.tanggal,7) as periode,sum(a.kgwb) as kgwb,SUM(a.jjg) as jjg,sum(a.kgwb)/SUM(a.jjg) as bjr 
			from ".$dbname.".kebun_spbdt a
			LEFT JOIN ".$dbname.".kebun_spbht b on b.nospb=a.nospb
			LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.blok
			where b.posting=1 ".$whr."
			GROUP BY a.blok,left(b.tanggal,7)
			ORDER BY a.blok,left(b.tanggal,7)";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	if(mysql_num_rows($res) == 0){
		exit('Warning: Tidak ada data...!');
	}
	while($bar=mysql_fetch_object($res)){
		$kodeorg[$bar->blok]=$bar->kodeorg;
		$blok[$bar->blok]=$bar->blok;
		$namablok[$bar->blok]=$bar->namablok;
		$kgwb[$bar->blok][$bar->periode]=$bar->kgwb;
		$jjg[$bar->blok][$bar->periode]=$bar->jjg;
		$bjr[$bar->blok][$bar->periode]=$bar->bjr;
	}

	$kolspan=0;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
		$stream="Laporan_BJR_Pabrik_".$kdOrg."_".$afdId."_".$periode;
	}
	$stream="";
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr." rowspan=2>No</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['kodeblok']."</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['blok']."</td>";
	for ($bl = 1; $bl <= 12; $bl++){
		$perbl=substr($periode,0,4)."-".sprintf("%02d",$bl);
		if(strlen($periode)==7 and $perbl!=$periode){
			continue;
		}
		$stream.="<td width=10px colspan='3' ".$bgclr.">".$perbl."</td>";
	}
	if(strlen($periode)==7 and $perbl!=$periode){
	}else{
		$stream.="<td ".$bgclr." colspan='3'>".$_SESSION['lang']['total']."</td>";
	}
	$stream.="</tr><tr>";
	for ($bl = 1; $bl <= 12; $bl++){
		$perbl=substr($periode,0,4)."-".sprintf("%02d",$bl);
		if(strlen($periode)==7 and $perbl!=$periode){
			continue;
		}
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['kg']."</td>";
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['bjr']."</td>";
	}
	if(strlen($periode)==7 and $perbl!=$periode){
	}else{
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['kg']."</td>";
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td width=10px ".$bgclr.">".$_SESSION['lang']['bjr']."</td>";
	}
	$stream.="</tr></thead><tbody>";

	# preview: nampilin data ================================================================================
	$ttkgwb=0;
	$ttjjg=0;
	$stkgwb=array();
	$stjjg=array();
	$gtkgwb=array();
	$gtjjg=array();
	$sttkgwb=0;
	$sttjjg=0;
	$gttkgwb=0;
	$gttjjg=0;
	foreach($blok as $blokid=>$blokval){
		$no+=1;
		$stream.="<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$blok[$blokid]."</td>
		<td>".$namablok[$blokid]."</td>";
		for ($bl = 1; $bl <= 12; $bl++){
			$perbl=substr($periode,0,4)."-".sprintf("%02d",$bl);
			if(strlen($periode)==7 and $perbl!=$periode){
				continue;
			}
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$perbl."','',event)>".@number_format($kgwb[$blokid][$perbl],2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$perbl."','',event)>".@number_format($jjg[$blokid][$perbl],0)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$perbl."','',event)>".@number_format($bjr[$blokid][$perbl],2)."</td>";
			$ttkgwb+=$kgwb[$blokid][$perbl];
			$ttjjg+=$jjg[$blokid][$perbl];
			//$stkgwb[$perbl]+=$kgwb[$blokid][$perbl];
			//$stjjg[$perbl]+=$jjg[$blokid][$perbl];
			$gtkgwb[$perbl]+=$kgwb[$blokid][$perbl];
			$gtjjg[$perbl]+=$jjg[$blokid][$perbl];
		}
		if(strlen($periode)==7 and $perbl!=$periode){
		}else{
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$periode."','',event)>".@number_format($ttkgwb,2)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$periode."','',event)>".@number_format($ttjjg,0)."</td>";
			$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('".$blokid."','".$periode."','',event)>".@number_format($ttkgwb/$ttjjg,2)."</td>";
		}
		$stream.="</tr>";
		$ttkgwb=0;
		$ttjjg=0;
	}

	# preview: nampilin sub total ================================================================================
	//$stream.="<thead class=rowheader>
	//$stream.="</tr><tr bgcolor='#FEDEFE'>";
	$stream.="<tr bgcolor='#FEDEFE'>
			<td colspan=3 align='center'>Total</td>";
	for ($bl = 1; $bl <= 12; $bl++){
		$perbl=substr($periode,0,4)."-".sprintf("%02d",$bl);
		if(strlen($periode)==7 and $perbl!=$periode){
			continue;
		}
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$perbl."','',event)>".@number_format($gtkgwb[$perbl],2)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$perbl."','',event)>".@number_format($gtjjg[$perbl],0)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$perbl."','',event)>".@number_format($gtkgwb[$perbl]/$gtjjg[$perbl],2)."</td>";
		$gttkgwb+=$gtkgwb[$perbl];
		$gttjjg+=$gtjjg[$perbl];
		$gtkgwb[$perbl]=0;
		$gtjjg[$perbl]=0;
	}
	if(strlen($periode)==7 and $perbl!=$periode){
	}else{
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$periode."','',event)>".@number_format($gttkgwb,2)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$periode."','',event)>".@number_format($gttjjg,0)."</td>";
		$stream.="<td title='Click untuk melihat detail.' align=right style=\"cursor: pointer\" onclick=showpopup('','".$periode."','',event)>".@number_format($gttkgwb/$gttjjg,2)."</td>";
	}
	$stream.="</tr></tbody></table>";
	$gttkgwb=0;
	$gttjjg=0;

	switch($proses){
        case'preview':
          echo $stream;
			break;

		case 'excel':
            $nop_="Laporan_premi_".($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0){
                $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
				echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
				//$handle=fopen("tempExcel/".$nop_.".xls",'w');
				//if(!fwrite($handle,$stream)){
					//echo "<script language=javascript1.2>
					//parent.window.alert('Can't convert to excel format');
					//</script>";
					//exit;
				//}else{
					//echo "<script language=javascript1.2>
					//window.location='tempExcel/".$nop_.".xls';
					//</script>";
				//}
				//fclose($handle);
            }
			break;

		default:
			break;
	}    
?>
