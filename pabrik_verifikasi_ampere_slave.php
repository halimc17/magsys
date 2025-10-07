<?//@Copy nangkoelframework
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
$ukur1=checkPostGet('ukur1',0);
$ukur2=checkPostGet('ukur2',0);
$ukur3=checkPostGet('ukur3',0);
$standard=checkPostGet('standard','');
$stdlama=checkPostGet('stdlama','');
$keterangan=checkPostGet('keterangan','');
$ketlama=checkPostGet('ketlama','');
$addedit=checkPostGet('addedit','');
switch($proses){
	case'loadData':
		/*
		$strtb="select left(a.tanggal,7) as periode from ".$dbname.".pabrik_verifikasi_ampere a where a.kodemesin like '".$mesin."%'
				order by a.kodemesin,a.tanggal desc limit 1";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$periodetb=date('Y-m');
		while($bartb=mysql_fetch_object($restb)){
			$periodetb=$bartb->periode;
		}
		$strtb="select a.* from ".$dbname.".pabrik_verifikasi_ampere a where a.kodemesin like '".$mesin."%' and a.standard<>0 and a.tanggal<'".$periodetb."-01'
				order by a.kodemesin,a.tanggal,a.standard";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$tebalsisa=0;
		while($bartb=mysql_fetch_object($restb)){
			$tebalsisa=$bartb->ukur1;
		}
		*/
		//$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_verifikasi_ampere a 
		//		left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
		//		where a.kodemesin like '".$mesin."%' and tanggal like '".$periodetb."%'
		//		order by a.kodemesin,a.tanggal,a.standard";
		$str="select a.*,b.namaorganisasi,d.kw from ".$dbname.".pabrik_verifikasi_ampere a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				left join ".$dbname.".pabrik_machinery d on d.kodemesin=a.kodemesin and namasubmesin='Elektromotor'
				order by a.kodeorg,a.kodemesin,a.tanggal desc,a.standard,a.keterangan";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			if($bar->standard!='0'){
				$jenisservice='Plat Baru';
				$tebalsisa=$bar->ukur1;
			}else{
				$jenisservice='';
			}
			$jmlukur=0;
			if($bar->ukur1>0){
				$tebalkecil=$bar->ukur1;
				$jmlukur+=1;
			}
			if($bar->ukur2>0){
				$jmlukur+=1;
			}
			if($bar->ukur3>0){
				$jmlukur+=1;
			}
			if($bar->ukur2>0 and $tebalkecil>0){
				$tebalkecil=($bar->ukur2<$tebalkecil ?  $bar->ukur2 : $tebalkecil);
			}
			if($bar->ukur3>0 and $tebalkecil>0){
				$tebalkecil=($bar->ukur3<$tebalkecil ?  $bar->ukur3 : $tebalkecil);
			}
			$tebalpersen=($tebalsisa==0 ? 0 : $tebalkecil/$tebalsisa*100);
			$ukurrata2=0;
			if($jmlukur>0){
				$ukurrata2=round(($bar->ukur1+$bar->ukur2+$bar->ukur3)/$jmlukur,2);
			}
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center width='3%'>".$bar->kodeorg."</td>
					<td ".$drcl." align=center width='5%'>".substr($bar->kodemesin,0,6)."</td>
					<td ".$drcl." align=center width='7%'>".$bar->kodemesin."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=right width='7%'>".$bar->kw."</td>
					<td ".$drcl." align=center width='6%'>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->standard,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur1,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur2,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur3,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($ukurrata2,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($ukurrata2-$bar->standard,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='7%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->standard."','".round($bar->ukur1,2)."','".round($bar->ukur2,2)."','".round($bar->ukur3,2)."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->standard."','".$bar->keterangan."');\">&nbsp
						<img src=images/zoom.png class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','preview',event);\">&nbsp
						<img src=images/skyblue/excel.jpg class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','excel',event);\">
					</td>
				</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_verifikasi_ampere 
				where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and standard='".$standard."' and keterangan='".$keterangan."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		/*
		$stra="select * from ".$dbname.".pabrik_verifikasi_ampere where kodemesin='".$mesin."' and tanggal<'".$tanggal."' 
				order by kodemesin,tanggal desc,standard desc limit 1";
		$resa=mysql_query($stra);
		$rowa=mysql_num_rows($resa);
		//exit('Warning: Tebal='.$ukur1.' '.$stra);
		while($bara=mysql_fetch_object($resa)){
			if($stdlama=='0' and ($ukur1>$bara->ukur1 or $ukur2>$bara->ukur2 or $ukur3>$bara->ukur3)){
				exit('Warning: Hasil Ukur lebih besar dari sebelumnya...!');
			}
		}
		*/
		if($addedit=='update'){
			$strs="select * from ".$dbname.".pabrik_verifikasi_ampere
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and standard='".$stdlama."' and keterangan='".$ketlama."'";
			$ress=mysql_query($strs);
			while($bars=mysql_fetch_object($ress)){
				$kodeorglama=$bars->kodeorg;
				$kodemesinlama=$bars->kodemesin;
				$tanggallama=tanggalsystem(tanggalnormal($bars->tanggal));
				$stdlama=$bars->standard;
				$keteranganlama=$bars->keterangan;
			}
			if($kodeorglama==$kodeorg and $kodemesinlama==$mesin){
				//exit('Warning: masuk'.$kodeorglama.'=='.$kodeorg.' '.$kodemesinlama.'=='.$mesin.' '.$tanggallama.'=='.$tanggal.' '.$hmawallama.'=='.round($hmawal,2).' '.$hmakhirlama.'=='.round($hmakhir,2).' '.$teballama.'=='.round($tebal,2));
			}else{
				$strs="select * from ".$dbname.".pabrik_verifikasi_ampere
						where (kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and standard<>'".$standard."' and keterangan<>'".$keterangan."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".pabrik_verifikasi_ampere set kodeorg='".$kodeorg."',kodemesin='".$mesin."',tanggal='".$tanggal."',ukur1=".round($ukur1,2)."
					,ukur2=".round($ukur2,2).",ukur3=".round($ukur3,2).",standard='".$standard."',keterangan='".$keterangan."'
					,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and standard='".$stdlama."' 
						and keterangan='".$ketlama."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_verifikasi_ampere
					(kodeorg,kodemesin,tanggal,ukur1,ukur2,ukur3,standard,keterangan,lastuser,lastdate)
					values('".$kodeorg."','".$mesin."','".$tanggal."',".round($ukur1,2).",".round($ukur2,2).",".round($ukur3,2)
					.",'".$standard."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
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
