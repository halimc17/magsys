<?php
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
$suhu=checkPostGet('suhu','');
$suhulama=checkPostGet('suhulama','');
$keterangan=checkPostGet('keterangan','');
$ketlama=checkPostGet('ketlama','');
$addedit=checkPostGet('addedit','');
switch($proses){
	case'loadData':
		$str="select a.*,b.namaorganisasi,d.kw from ".$dbname.".pabrik_meggertest a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				left join ".$dbname.".pabrik_machinery d on d.kodemesin=a.kodemesin and namasubmesin='Elektromotor'
				order by a.kodeorg,a.kodemesin,a.tanggal desc,a.temp,a.keterangan";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			$tebalkecil=0;
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
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur1,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur2,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->ukur3,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->temp,2,'.',',')."</td>";
			if($tebalkecil<5){
				echo "<td ".$drcl." align=center width='5%'>"."&radic;"."</td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
			}else if($tebalkecil<10){
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'>"."&radic;"."</td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
			}else if($tebalkecil<50){
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'>"."&radic;"."</td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
			}else{
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'></td>";
				echo "<td ".$drcl." align=center width='5%'>"."&radic;"."</td>";
			}
			echo "	<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='7%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->temp."','".round($bar->ukur1,2)."','".round($bar->ukur2,2)."','".round($bar->ukur3,2)."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->temp."','".$bar->keterangan."');\">&nbsp
						<img src=images/zoom.png class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','preview',event);\">&nbsp
						<img src=images/skyblue/excel.jpg class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."','excel',event);\">
					</td>
				</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_meggertest 
				where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and temp='".$suhu."' and keterangan='".$keterangan."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			$strs="select * from ".$dbname.".pabrik_meggertest
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and temp='".$suhulama."' and keterangan='".$ketlama."'";
			$ress=mysql_query($strs);
			while($bars=mysql_fetch_object($ress)){
				$kodeorglama=$bars->kodeorg;
				$kodemesinlama=$bars->kodemesin;
				$tanggallama=tanggalsystem(tanggalnormal($bars->tanggal));
				$suhulama=$bars->temp;
				$keteranganlama=$bars->keterangan;
			}
			if($kodeorglama==$kodeorg and $kodemesinlama==$mesin){
				//exit('Warning: masuk'.$kodeorglama.'=='.$kodeorg.' '.$kodemesinlama.'=='.$mesin.' '.$tanggallama.'=='.$tanggal.' '.$hmawallama.'=='.round($hmawal,2).' '.$hmakhirlama.'=='.round($hmakhir,2).' '.$teballama.'=='.round($tebal,2));
			}else{
				$strs="select * from ".$dbname.".pabrik_meggertest
						where (kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and temp<>'".$suhu."' and keterangan<>'".$keterangan."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".pabrik_meggertest set kodeorg='".$kodeorg."',kodemesin='".$mesin."',tanggal='".$tanggal."',ukur1=".round($ukur1,2)."
					,ukur2=".round($ukur2,2).",ukur3=".round($ukur3,2).",temp='".$suhu."',keterangan='".$keterangan."'
					,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tgllama."' and temp='".$suhulama."' 
						and keterangan='".$ketlama."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_meggertest
					(kodeorg,kodemesin,tanggal,ukur1,ukur2,ukur3,temp,keterangan,lastuser,lastdate)
					values('".$kodeorg."','".$mesin."','".$tanggal."',".round($ukur1,2).",".round($ukur2,2).",".round($ukur3,2)
					.",'".$suhu."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
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
