<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kdUnit=checkPostGet('kdUnit','');
$tanggal1=tanggalsystem(checkPostGet('tanggal1',''));
$tanggal2=tanggalsystem(checkPostGet('tanggal2',''));
$kodeorg=checkPostGet('kodeorg','');
$tanggal=checkPostGet('tanggal','');
$karyawanid=checkPostGet('karyawanid','');
switch($proses){
	case'loadData':
		$where="";
		if($kdUnit!=''){
			$where.=" and left(a.kodeorg,4)='".$kdUnit."'";
		}
		if($tanggal1!='' and $tanggal2==''){
			$tanggal2=$tanggal1;
		}
		if($tanggal1=='' and $tanggal2!=''){
			$tanggal1=$tanggal2;
		}
		if($tanggal1!='' and $tanggal2!=''){
			$tanggal1=substr($tanggal1,0,4)."-".substr($tanggal1,4,2)."-".substr($tanggal1,6,2);
			$tanggal2=substr($tanggal2,0,4)."-".substr($tanggal2,4,2)."-".substr($tanggal2,6,2);
			$where.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		}

		// Pilihan jam lembur
		$optjam2="";
		$sjam="select distinct jamaktual from ".$dbname.".sdm_5lembur order by jamaktual";
		$qjam=mysql_query($sjam) or die(mysql_error($conn));
		while($rjam=mysql_fetch_assoc($qjam)){
			$optjam2.="<option value=".$rjam['jamaktual'].">".$rjam['jamaktual']."</option>";
		}

		//Data Detail
		$str="select a.*,b.nik,b.namakaryawan from ".$dbname.".sdm_lemburdt a 
				left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				left join ".$dbname.".sdm_5lembur c on c.kodeorg=left(a.kodeorg,4) and c.tipelembur=a.tipelembur and c.jamaktual=a.jamaktual
				where posting=0 ".$where."
				order by a.kodeorg,a.tanggal,a.karyawanid";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			$arrsstk=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya']);
			$id=$bar->kodeorg.$bar->tanggal.$bar->karyawanid;
			$optjam3=$optjam2."<option selected value=".$bar->jamaktual.">".$bar->jamaktual."</option>";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl." align=center>".$bar->nik."</td>
					<td ".$drcl." align=left>".$bar->namakaryawan."</td>
					<td ".$drcl." align=left>".$arrsstk[$bar->tipelembur]."</td>
					<td ".$drcl." align=center><select id=jamaktual".$id." name=jamaktual".$id." onchange=\"SaveCell('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."','jamaktual','".$bar->jamaktual."');\">".$optjam3."</select></td>
					<td ".$drcl." align=right>".number_format($bar->uangkelebihanjam,2,'.',',')."</td>
					<td ".$drcl." align=right><input type=text id=uangtransport".$id." value=".number_format($bar->uangtransport,2)." onchange=\"SaveCell('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."','uangtransport','".$bar->uangtransport."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>
					<td ".$drcl." align=right><input type=text id=uangmakan".$id." value=".number_format($bar->uangmakan,2)." onchange=\"SaveCell('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."','uangmakan','".$bar->uangmakan."');\"  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>
					<td ".$drcl." align=left><input type=text id=noba".$id." value=".$bar->noba." onchange=\"SaveCell('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."','noba','".$bar->noba."');\" class=myinputtext style=\"width:195px;\"></td>
					<td align=center>
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."');\">&nbsp;&nbsp;";
			if($bar->posting==0){
				echo "	<img src='images/".$_SESSION['theme']."/posting.png' class=resicon title='Posting' onclick=\"postingdata('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."');\">";
			}else{
				echo "	<img src='images/".$_SESSION['theme']."/posted.png' class=resicon title='Posted'>";
			}
			echo"	</td>
				</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".sdm_lemburdt where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$karyawanid."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'SimpanCell':
		$cellname=checkPostGet('cellname','');
		$cellvalue=checkPostGet('cellvalue','');
		if($cellname=='jamaktual'){
			$sData="select c.jamlembur,d.jumlah from ".$dbname.".sdm_lemburdt a 
					left join ".$dbname.".sdm_5lembur c on c.kodeorg=left(a.kodeorg,4) and c.tipelembur=a.tipelembur and c.jamaktual='".$cellvalue."'
					left join ".$dbname.".sdm_5gajipokok d on d.karyawanid=a.karyawanid and d.tahun=left(a.tanggal,4) and idkomponen=1
					where a.posting=0 and a.kodeorg='".$kodeorg."' and a.tanggal='".$tanggal."' and a.karyawanid='".$karyawanid."'";
			$qData=mysql_query($sData) or die(mysql_error($conn));
			$uangkelebihanjam=0;
			while($rData=mysql_fetch_assoc($qData)){
				$uangkelebihanjam=intval($rData['jamlembur']*$rData['jumlah']/173);
			}
			$strx="update ".$dbname.".sdm_lemburdt set ".$cellname."='".$cellvalue."',uangkelebihanjam=".$uangkelebihanjam." where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$karyawanid."'";
		}else{
			$strx="update ".$dbname.".sdm_lemburdt set ".$cellname."='".$cellvalue."' where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$karyawanid."'";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'postingData':
		$strx="update ".$dbname.".sdm_lemburdt set posting=1, postby='".$_SESSION['standard']['username']."' where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$karyawanid."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	default:
	break;
}
?>
