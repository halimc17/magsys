<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$lksiTgs=$_SESSION['empl']['lokasitugas'];
	$kdOrg=$_POST['kdOrg'];
	$stasiun=$_POST['stasiun'];
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
	if($stasiun=='')$stasiun=$_GET['stasiun'];
	if($proses=='')$proses=$_GET['proses'];

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

	if($stasiun!=''){
		$whr=" and a.kodemesin like '".$stasiun."%'";
	}else{
		$whr=" ";
	}

	$strtb="select a.kodemesin,a.tanggal,a.hmakhir,a.tipeservice from ".$dbname.".pabrik_hm a where a.tipeservice<>0 ".$whr." 
			order by a.kodemesin,a.tanggal,a.tipeservice";
	$restb=mysql_query($strtb);
	$hmtgl1=array();
	$hmtgl2=array();
	$hmtgl3=array();
	$hmjalan1=array();
	$hmjalan2=array();
	$hmjalan3=array();
	$hmservice1=array();
	$hmservice2=array();
	$hmservice3=array();
	while($bartb=mysql_fetch_object($restb)){
		if($bartb->tipeservice==3){
			$hmtgl3[$bartb->kodemesin]=$bartb->tanggal;
			$hmservice3[$bartb->kodemesin]=$bartb->tipeservice;
			$hmjalan3[$bartb->kodemesin]=$bartb->hmakhir;
		}elseif($bartb->tipeservice==2){
			$hmtgl2[$bartb->kodemesin]=$bartb->tanggal;
			$hmservice2[$bartb->kodemesin]=$bartb->tipeservice;
			$hmjalan2[$bartb->kodemesin]=$bartb->hmakhir;
		}else{
			$hmtgl1[$bartb->kodemesin]=$bartb->tanggal;
			$hmservice1[$bartb->kodemesin]=$bartb->tipeservice;
			$hmjalan1[$bartb->kodemesin]=$bartb->hmakhir;
		}
	}

	$stream="";
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$stream=$_SESSION['lang']['laporan']." HM Service ".$_SESSION['lang']['mesin']." ".$kdOrg;
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
	        <td ".$bgclr." width='50px'>".$_SESSION['lang']['kode']."</td>
		    <td ".$bgclr." width='225px'>".$_SESSION['lang']['station']."</td>
	        <td ".$bgclr." width='80px'>".$_SESSION['lang']['kode']."</td>
		    <td ".$bgclr." width='330px'>".$_SESSION['lang']['nmmesin']."</td>
			<td ".$bgclr." width='70px' align=center>HM Service</td>
			<td ".$bgclr." width='70px' align=center>HM Akhir</td>
			<td ".$bgclr." width='70px' align=center>HM Sisa</td>
	        <td ".$bgclr." width='300px'>".$_SESSION['lang']['keterangan']." Service</td>
		</tr></thead><tbody>";
	#ambil data Mesin
	$str="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_hm_setup a 
	LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
	LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
	where a.jamganti1+a.jamganti2+a.jamganti3>0 ".$whr." 
	ORDER BY a.kodemesin";
	//exit('Warning : '.$str);
	$res=mysql_query($str) or die(mysql_error($conn));
	$namasts='';
	$kodemesin='';
	$nost=0;
	$noms=0;
	$romawi='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	while($bar=mysql_fetch_object($res)){
		$str2="select a.* from ".$dbname.".pabrik_hm a 
				where a.kodemesin='".$bar->kodemesin."' and a.tanggal>='".$hmtgl1[$bar->kodemesin]."'
				ORDER BY a.kodemesin,a.tanggal desc limit 1";
		$res2=mysql_query($str2) or die(mysql_error($conn));
		while($bar2=mysql_fetch_object($res2)){
			$hmakhir[$bar2->kodemesin]=$bar2->hmakhir;
		}
		if($bar->jamganti1!=0 and $hmakhir[$bar->kodemesin]>=($bar->jamganti1*0.8)){
			$stream.="<tr clas=rowcontent title='Click untuk melihat detail..!' align=left style=\"cursor: pointer\" onclick=showpopup('".substr($bar->kodemesin,0,4)."','".substr($bar->kodemesin,0,6)."','".$bar->kodemesin."','',event)>";
			$stream.="<td align=center>".substr($bar->kodemesin,0,6)."</td>";
			$stream.="<td>".$bar->namastasiun."</td>";
			$stream.="<td align=center>".$bar->kodemesin."</td>";
			$stream.="<td>".$bar->namamesin."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti1,0)."</td>";
			$stream.="<td align=right>".number_format($hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti1-$hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td>"."Pergantian Sparepart 1"."</td>";
			$stream.="</tr>";
		}
		if($bar->jamganti2!=0 and $hmakhir[$bar->kodemesin]>=$bar->jamganti2*0.85){
			$stream.="<tr clas=rowcontent title='Click untuk melihat detail..!' align=left style=\"cursor: pointer\" onclick=showpopup('".substr($bar->kodemesin,0,4)."','".substr($bar->kodemesin,0,6)."','".$bar->kodemesin."','',event)>";
			$stream.="<td align=center>".substr($bar->kodemesin,0,6)."</td>";
			$stream.="<td>".$bar->namastasiun."</td>";
			$stream.="<td align=center>".$bar->kodemesin."</td>";
			$stream.="<td>".$bar->namamesin."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti2,0)."</td>";
			$stream.="<td align=right>".number_format($hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti2-$hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td>"."Pergantian Sparepart 2 / Intermediate / Top Overhaul"."</td>";
			$stream.="</tr>";
		}
		if($bar->jamganti3!=0 and $hmakhir[$bar->kodemesin]>=$bar->jamganti3*0.85){
			$stream.="<tr clas=rowcontent title='Click untuk melihat detail..!' align=left style=\"cursor: pointer\" onclick=showpopup('".substr($bar->kodemesin,0,4)."','".substr($bar->kodemesin,0,6)."','".$bar->kodemesin."','',event)>";
			$stream.="<td align=center>".substr($bar->kodemesin,0,6)."</td>";
			$stream.="<td>".$bar->namastasiun."</td>";
			$stream.="<td align=center>".$bar->kodemesin."</td>";
			$stream.="<td>".$bar->namamesin."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti3,0)."</td>";
			$stream.="<td align=right>".number_format($hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td align=right>".number_format($bar->jamganti3-$hmakhir[$bar->kodemesin],0)."</td>";
			$stream.="<td>"."Pergantian Sparepart 3 / Major / General Overhaul"."</td>";
			$stream.="</tr>";
		}
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
			echo $stream;
		break;

        case 'excel':
            $nop_="Laporan_HM_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0){
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
