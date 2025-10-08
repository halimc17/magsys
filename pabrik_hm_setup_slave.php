<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$stasiun=checkPostGet('stasiun','');
$mesin=checkPostGet('mesin','');
$jamganti1=checkPostGet('jamganti1',0);
$jamganti2=checkPostGet('jamganti2',0);
$jamganti3=checkPostGet('jamganti3',0);
$hmakhir=checkPostGet('hmakhir',0);
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
switch($proses){
	case'loadData':
		$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_hm_setup a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				order by a.kodemesin";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center width='3%'>".substr($bar->kodemesin,0,4)."</td>
					<td ".$drcl." align=center width='5%'>".substr($bar->kodemesin,0,6)."</td>
					<td ".$drcl." align=center width='7%'>".$bar->kodemesin."</td>
					<td ".$drcl." align=left width='30%'>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=right width='7%'>".number_format($bar->jamganti1,2,'.',',')."</td>
					<td ".$drcl." align=right width='7%'>".number_format($bar->jamganti2,2,'.',',')."</td>
					<td ".$drcl." align=right width='7%'>".number_format($bar->jamganti3,2,'.',',')."</td>
					<td ".$drcl." align=right width='7%'>".number_format($bar->hmakhir,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='4%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodemesin."','".$bar->jamganti1."','".$bar->jamganti2."','".$bar->jamganti3."'
											,'".$bar->hmakhir."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodemesin."');\">
					</td>
				</tr>";	
		}
		break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_hm_setup where kodemesin='".$mesin."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			$strx="update ".$dbname.".pabrik_hm_setup set jamganti1='".$jamganti1."',jamganti2='".$jamganti2."',jamganti3='".$jamganti3."',hmakhir=".$hmakhir."
					,keterangan='".$keterangan."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodemesin='".$mesin."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_hm_setup (kodemesin,jamganti1,jamganti2,jamganti3,hmakhir,keterangan,lastuser,lastdate)
			values('".$mesin."','".$jamganti1."','".$jamganti2."','".$jamganti3."','".$hmakhir."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
		}
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getStasiun':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($kodeorg!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION' and induk='".$kodeorg."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}  
		}
		echo $optStation;
    break;

    case'getMesin':
        $optMesin.="";
		if($stasiun==''){
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk like '".$kodeorg."%'";
		}else{
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$stasiun."'";
		}
		$nMesin=mysql_query($iMesin) or die (mysql_error($conn));
		while($dMesin=mysql_fetch_assoc($nMesin)){
			if($mesin==$dMesin['kodeorganisasi'])
				{$select="selected=selected";}
			else
				{$select="";}
			$optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">[".$dMesin['kodeorganisasi']."] ".$dMesin['namaorganisasi']."</option>";
		}
        echo $optMesin;
    break;

	default:
    break;
}
?>
