<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$stasiun=checkPostGet('stasiun','');
$mesin=checkPostGet('mesin','');
########cara hitung tanggal kemarin###############
$tanggal=tanggalsystem(checkPostGet('tanggal',''));//merubah dari 10-10-2014 menjadi 20141010
$tgllama=tanggalsystem(checkPostGet('tgllama',''));//merubah dari 10-10-2014 menjadi 20141010
$tglKmrn=strtotime('-1 day',strtotime($tanggal));
$tglKmrn=date('Y-m-d', $tglKmrn);
$tebal1=checkPostGet('tebal1',0);
$tebal2=checkPostGet('tebal2',0);
$tebal3=checkPostGet('tebal3',0);
$tebal4=checkPostGet('tebal4',0);
$tebal5=checkPostGet('tebal5',0);
$tebal6=checkPostGet('tebal6',0);
$tipeservice=checkPostGet('tipeservice','');
$tipesrvlama=checkPostGet('tipesrvlama','');
$keterangan=checkPostGet('keterangan','');
$ketlama=checkPostGet('ketlama','');
$addedit=checkPostGet('addedit','');
switch($proses){
	case'loadData':
		/*
		$strtb="select left(a.tanggal,7) as periode from ".$dbname.".pabrik_thickness a where a.kodemesin like '".$mesin."%'
				order by a.kodemesin,a.tanggal desc limit 1";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$periodetb=date('Y-m');
		while($bartb=mysql_fetch_object($restb)){
			$periodetb=$bartb->periode;
		}
		$strtb="select a.* from ".$dbname.".pabrik_thickness a where a.kodemesin like '".$mesin."%' and a.tipeservice<>0 and a.tanggal<'".$periodetb."-01'
				order by a.kodemesin,a.tanggal,a.tipeservice";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$tebalsisa=0;
		while($bartb=mysql_fetch_object($restb)){
			$tebalsisa=$bartb->tebal1;
		}
		*/
		//$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_thickness a 
		//		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		//		where a.kodemesin like '".$mesin."%' and tanggal like '".$periodetb."%'
		//		order by a.kodemesin,a.tanggal,a.tipeservice";
		$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_thickness a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				where a.kodemesin like '".$mesin."%' 
				order by a.kodemesin,a.tanggal,a.tipeservice";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			if($bar->tipeservice!='0'){
				$jenisservice='Plat Baru';
				$tebalsisa=$bar->tebal1;
			}else{
				$jenisservice='';
			}
			if($bar->tebal1>0){
				$tebalkecil=$bar->tebal1;
			}
			if($bar->tebal2>0 and $tebalkecil>0){
				$tebalkecil=($bar->tebal2<$tebalkecil ?  $bar->tebal2 : $tebalkecil);
			}
			if($bar->tebal3>0 and $tebalkecil>0){
				$tebalkecil=($bar->tebal3<$tebalkecil ?  $bar->tebal3 : $tebalkecil);
			}
			if($bar->tebal4>0 and $tebalkecil>0){
				$tebalkecil=($bar->tebal4<$tebalkecil ?  $bar->tebal4 : $tebalkecil);
			}
			if($bar->tebal5>0 and $tebalkecil>0){
				$tebalkecil=($bar->tebal5<$tebalkecil ?  $bar->tebal5 : $tebalkecil);
			}
			if($bar->tebal6>0 and $tebalkecil>0){
				$tebalkecil=($bar->tebal6<$tebalkecil ?  $bar->tebal6 : $tebalkecil);
			}
			$tebalpersen=($tebalsisa==0 ? 0 : $tebalkecil/$tebalsisa*100);
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center width='3%'>".$bar->kodeorg."</td>
					<td ".$drcl." align=center width='5%'>".substr($bar->kodemesin,0,6)."</td>
					<td ".$drcl." align=center width='7%'>".$bar->kodemesin."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=center width='6%'>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=left width='5%'>".$jenisservice."</td>
					<td ".$drcl." align=right width='4%'>".number_format($tebalsisa,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal1,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal2,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal3,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal4,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal5,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tebal6,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($tebalpersen,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='7%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->tipeservice."','".round($bar->tebal1,2)."','".round($bar->tebal2,2)."','".round($bar->tebal3,2)."','".round($bar->tebal4,2)."','".round($bar->tebal5,2)."','".round($bar->tebal6,2)."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->tipeservice."','".$bar->keterangan."');\">&nbsp
						<img src=images/zoom.png class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','preview',event);\">&nbsp
						<img src=images/skyblue/excel.jpg class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','excel',event);\">
					</td>
				</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_thickness 
				where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and tipeservice='".$tipeservice."' and keterangan='".$keterangan."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		/*
		$stra="select * from ".$dbname.".pabrik_thickness where kodemesin='".$mesin."' and tanggal<'".$tanggal."' 
				order by kodemesin,tanggal desc,tipeservice desc limit 1";
		$resa=mysql_query($stra);
		$rowa=mysql_num_rows($resa);
		//exit('Warning: Tebal='.$tebal1.' '.$stra);
		while($bara=mysql_fetch_object($resa)){
			if($tipesrvlama=='0' and ($tebal1>$bara->tebal1 or $tebal2>$bara->tebal2 or $tebal3>$bara->tebal3 or $tebal4>$bara->tebal4 or $tebal5>$bara->tebal5 or $tebal6>$bara->tebal6)){
				exit('Warning: Tebal lebih besar dari sebelumnya...!');
			}
		}
		*/
		if($addedit=='update'){
			$strs="select * from ".$dbname.".pabrik_thickness
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and tipeservice='".$tipesrvlama."' and keterangan='".$ketlama."'";
			$ress=mysql_query($strs);
			while($bars=mysql_fetch_object($ress)){
				$kodeorglama=$bars->kodeorg;
				$kodemesinlama=$bars->kodemesin;
				$tanggallama=tanggalsystem(tanggalnormal($bars->tanggal));
				$tipeservicelama=$bars->tipeservice;
				$keteranganlama=$bars->keterangan;
			}
			if($kodeorglama==$kodeorg and $kodemesinlama==$mesin){
				//exit('Warning: masuk'.$kodeorglama.'=='.$kodeorg.' '.$kodemesinlama.'=='.$mesin.' '.$tanggallama.'=='.$tanggal.' '.$hmawallama.'=='.round($hmawal,2).' '.$hmakhirlama.'=='.round($hmakhir,2).' '.$teballama.'=='.round($tebal,2));
			}else{
				$strs="select * from ".$dbname.".pabrik_thickness
						where (kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and tipeservice<>'".$tipeservice."' and keterangan<>'".$keterangan."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".pabrik_thickness set kodeorg='".$kodeorg."',kodemesin='".$mesin."',tanggal='".$tanggal."',tebal1=".round($tebal1,2)."
					,tebal2=".round($tebal2,2).",tebal3=".round($tebal3,2).",tebal4=".round($tebal4,2).",tebal5=".round($tebal5,2).",tebal6=".round($tebal6,2)."
					,tipeservice='".$tipeservice."',keterangan='".$keterangan."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and tipeservice='".$tipesrvlama."' and keterangan='".$ketlama."'";
			//exit('Warning: '.$strx);
		}else{
			$strx="insert into ".$dbname.".pabrik_thickness
					(kodeorg,kodemesin,tanggal,tebal1,tebal2,tebal3,tebal4,tebal5,tebal6,tipeservice,keterangan,lastuser,lastdate)
					values('".$kodeorg."','".$mesin."','".$tanggal."',".round($tebal1,2).",".round($tebal2,2).",".round($tebal3,2).",".round($tebal4,2)."
							,".round($tebal5,2).",".round($tebal6,2).",'".$tipeservice."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
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
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk like '".$kodeorg."%' and length(kodeorganisasi)=10";
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
