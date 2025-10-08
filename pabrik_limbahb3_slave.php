<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$kodeorglama=checkPostGet('kodeorglama','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$tanggallama=tanggalsystem(checkPostGet('tanggallama',''));
$kodemesin=checkPostGet('kodemesin','');
$kodemesinlama=checkPostGet('kodemesinlama','');
$kodebarang=checkPostGet('kodebarang','');
$kodebaranglama=checkPostGet('kodebaranglama','');
$qtymasuk=checkPostGet('qtymasuk',0);
$qtykeluar=checkPostGet('qtykeluar',0);
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
$carikodeorg=checkPostGet('carikodeorg','');
$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
$carikodebarang=checkPostGet('carikodebarang','');
switch($proses){
	case'loadData':
		$where="";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where.="True";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.="left(a.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
		}else{
			$where.="a.kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		if($carikodeorg!=''){
			$where.=" and a.kodeorg like '".$carikodeorg."%'";
		}
		if($caritanggal1!='' and $caritanggal2==''){
			$caritanggal2=$caritanggal1;
		}
		if($caritanggal1=='' and $caritanggal2!=''){
			$caritanggal1=$caritanggal2;
		}
		if($caritanggal1!='' and $caritanggal2!=''){
			$where.=" and a.tanggal>='".$caritanggal1."' and a.tanggal<='".$caritanggal2."'";
		}
		if($carikodebarang!=''){
			$where.=" and a.kodebarang='".$carikodebarang."'";
		}
		$strb="select a.kodeorg,a.tanggal,a.kodemesin,a.kodebarang from ".$dbname.".pabrik_limbahb3 a 
				where ".$where." 
				order by a.kodeorg,a.tanggal desc,a.kodemesin,a.kodebarang";
		//exit('Warning: '.$strb);
		$resb=mysql_query($strb);
		$jlhbrs=mysql_num_rows($resb);
		$limit=25;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',0);
			if((($page*$limit)+1)>$jlhbrs)
				$page=$page-1;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$str="select a.*,c.namaorganisasi,d.namabarang from ".$dbname.".pabrik_limbahb3 a 
				left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodemesin
				left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
				where ".$where." 
				order by a.kodeorg,a.tanggal desc,a.kodemesin,a.kodebarang limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".substr($bar->kodeorg,0,4)."</td>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=left>".$bar->namabarang."</td>
					<td ".$drcl." align=right>".number_format($bar->qtymasuk,2,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->qtykeluar,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='6%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->kodemesin."','".$bar->kodebarang."','".$bar->qtymasuk."','".$bar->qtykeluar."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->kodemesin."','".$bar->kodebarang."');\">
					</td>
				</tr>";	
		}
		echo"
		<tr class=rowheader>
			<td colspan=20 align=center>
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>&nbsp
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."&nbsp
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
	break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_limbahb3 
				where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and kodemesin='".$kodemesin."' and kodebarang='".$kodebarang."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			if($kodeorglama!=$kodeorg or $tanggallama!=$tanggal or $kodemesinlama!=$kodemesin or $kodebaranglama!=$kodebarang){
				$strs="select * from ".$dbname.".pabrik_limbahb3
						where (kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and kodemesin='".$kodemesin."' and kodebarang='".$kodebarang."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".pabrik_limbahb3 set kodeorg='".$kodeorg."',tanggal='".$tanggal."',kodemesin='".$kodemesin."',kodebarang='".$kodebarang."'
				,qtymasuk='".$qtymasuk."',qtykeluar='".$qtykeluar."',keterangan='".$keterangan."'
				,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".$kodeorglama."' and tanggal='".$tanggallama."' and kodemesin='".$kodemesinlama."' and kodebarang='".$kodebaranglama."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_limbahb3
				(kodeorg,tanggal,kodemesin,kodebarang,qtymasuk,qtykeluar,keterangan,lastuser,lastdate)
				values('".$kodeorg."','".$tanggal."','".$kodemesin."','".$kodebarang."','".$qtymasuk."','".$qtykeluar."','".$keterangan."'
				,'".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getMesin':
		$optMesin="";
		$sMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STENGINE' and kodeorganisasi like '".$kodeorg."%' order by kodeorganisasi";
		$qMesin=mysql_query($sMesin) or die(mysql_error($conn));
		while($rMesin=mysql_fetch_assoc($qMesin)){
			$optMesin.="<option value=".$rMesin['kodeorganisasi'].">[".$rMesin['kodeorganisasi'].'] - '.$rMesin['namaorganisasi']."</option>";
		}
		echo $optMesin;
    break;

	default:
	break;
}
?>
