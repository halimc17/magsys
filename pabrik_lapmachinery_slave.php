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

	$stream="";
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$stream="Laporan Data Mesin ".$kdOrg;
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td ".$bgclr.">No</td>
	        <td ".$bgclr.">".$_SESSION['lang']['kode']."</td>
		    <td ".$bgclr." width='300px'>".$_SESSION['lang']['nmmesin']."</td>
		    <td ".$bgclr." width='300px'>Sub ".$_SESSION['lang']['mesin']."</td>
	        <td ".$bgclr.">".$_SESSION['lang']['status']."</td>
	        <td ".$bgclr.">".$_SESSION['lang']['unit']."1</td>
	        <td ".$bgclr.">".$_SESSION['lang']['status']."</td>
	        <td ".$bgclr.">".$_SESSION['lang']['unit']."2</td>
	        <td ".$bgclr.">".$_SESSION['lang']['status']."</td>
	        <td ".$bgclr.">".$_SESSION['lang']['merk']."</td>
	        <td ".$bgclr.">Type/Model</td>
	        <td ".$bgclr.">Ratio</td>
	        <td ".$bgclr.">Rpm</td>
	        <td ".$bgclr.">KW</td>
	        <td ".$bgclr.">Ampere</td>
	        <td ".$bgclr.">".$_SESSION['lang']['tahun']."</td>
	        <td ".$bgclr.">Serial Number</td>
	        <td ".$bgclr." colspan=3>Sproket</td>
	        <td ".$bgclr.">".$_SESSION['lang']['status']."</td>
	        <td ".$bgclr." colspan=2>Chain</td>
	        <td ".$bgclr.">".$_SESSION['lang']['status']."</td>
			<td ".$bgclr." colspan=2>Pully</td>
	        <td ".$bgclr.">V-Belt</td>
	        <td ".$bgclr.">Coupling</td>
	        <td ".$bgclr." colspan=3>Bearing</td>
	        <td ".$bgclr.">HM</td>
		</tr></thead><tbody>";
	#ambil data Mesin
	if($stasiun!=''){
		$whr=" and a.kodemesin like '".$stasiun."%'";
	}else{
		$whr=" ";
	}
	$str="select a.*,b.namaorganisasi as namamesin,c.namaorganisasi as namastasiun from ".$dbname.".pabrik_machinery a 
	LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
	LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=left(a.kodemesin,6)
	where a.kodeorg = '".$kdOrg."'
		".$whr." 
	ORDER BY a.kodemesin";
	//exit('Warning : '.$str);
	$res=mysql_query($str) or die(mysql_error($conn));
	$namasts='';
	$kodemesin='';
	$nost=0;
	$noms=0;
	$romawi='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	while($bar=mysql_fetch_object($res)){
		if($bar->namastasiun!=$namasts){
			$nost+=1;
			$stream.="<tr class=rowcontent>
						<td align=center><b>".substr($romawi,$nost-1,1)."</b></td>
						<td align=left><b>".substr($bar->kodemesin,0,6)."</b></td>
						<td align=left style='width:600px;'><b>".$bar->namastasiun."</b></td>
						<td colspan=29></td>
					</tr>";
			$namasts=$bar->namastasiun;
		}
		if($bar->kodemesin!=$kodemesin){
			$noms+=1;
			$stream.="<tr class=rowcontent title='Click untuk melihat detail..!' align=left style=\"cursor: pointer\" onclick=showpopup('".$kdOrg."','".substr($bar->kodemesin,0,6)."','".$bar->kodemesin."','',event)>
						<td align=center>".$noms."</td>
						<td align=left>".$bar->kodemesin."</td>
						<td align=left style='width:600px;'>".$bar->namamesin."</td>";
			$kodemesin=$bar->kodemesin;
		}else{
			$stream.="<tr class=rowcontent title='Click untuk melihat detail excel..!' align=left style=\"cursor: pointer\" onclick=showpopup('".$kdOrg."','".substr($bar->kodemesin,0,6)."','".$bar->kodemesin."','excel',event)>
						<td></td>
						<td></td>
						<td></td>";
		}
		$stream.="<td>".$bar->namasubmesin."</td>";
		if($bar->statusmesin*100==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".@number_format($bar->statusmesin*100,0)."%</td>";
		}
		$stream.="<td>".$bar->unit1."</td>";
		if($bar->stsunit1*100==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".@number_format($bar->stsunit1*100,0)."%</td>";
		}
		$stream.="<td>".$bar->unit2."</td>";
		if($bar->stsunit2*100==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".@number_format($bar->stsunit2*100,0)."%</td>";
		}
		$stream.="<td>".$bar->merk."</td>";
		$stream.="<td>".$bar->model."</td>";
		$stream.="<td align=right>".$bar->ratio."</td>";
		$stream.="<td align=right>".$bar->rpm."</td>";
		$stream.="<td align=right>".$bar->kw."</td>";
		$stream.="<td align=right>".$bar->ampere."</td>";
		$stream.="<td align=right>".$bar->manufacturedyear."</td>";
		$stream.="<td>".$bar->serialnumber."</td>";
		$stream.="<td>".$bar->sproket1."</td>";
		$stream.="<td>".$bar->sproket2."</td>";
		$stream.="<td>".$bar->sproket3."</td>";
		if($bar->stssproket*100==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".@number_format($bar->stssproket*100,0)."%</td>";
		}
		$stream.="<td>".$bar->chain1."</td>";
		$stream.="<td>".$bar->chain2."</td>";
		if($bar->stschain*100==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".@number_format($bar->stschain*100,0)."%</td>";
		}
		$stream.="<td>".$bar->pully1."</td>";
		$stream.="<td>".$bar->pully2."</td>";
		$stream.="<td>".$bar->vbelt."</td>";
		$stream.="<td>".$bar->coupling."</td>";
		$stream.="<td>".$bar->bearing1."</td>";
		$stream.="<td>".$bar->bearing2."</td>";
		$stream.="<td>".$bar->bearing3."</td>";
		$stream.="<td>".$bar->merkhm."</td>";
		$stream.="</tr>";
	}
	$stream.="</tbody></table>";

//		$stream.="<td title='Detail' align=right style=\"cursor: pointer\" 
//		onclick=showpopup('".$kdOrg."','".$stasiun."','".$bar->kodemesin."','',event)>".number_format($bar->statusmesin*100,0)."%</td>";

	switch($proses)
	{
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
                if(!fwrite($handle,$stream))
                {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                }
                else
                {
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