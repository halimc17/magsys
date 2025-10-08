<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$unit=checkPostGet('unit','');
$divisi=checkPostGet('divisi','');
$kodeorg=checkPostGet('kodeorg','');
$kodeorglama=checkPostGet('kodeorglama','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$tanggallama=tanggalsystem(checkPostGet('tanggallama',''));
$jenis=checkPostGet('jenis','');
$jenislama=checkPostGet('jenislama','');
$waktu=checkPostGet('waktu','');
$janjang=checkPostGet('janjang',0);
$supirlangsir=checkPostGet('supirlangsir','');
$keterangan=checkPostGet('keterangan','');
$catatan=checkPostGet('catatan','');
$addedit=checkPostGet('addedit','');
$carikodeorg=checkPostGet('carikodeorg','');
$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
$carijenis=checkPostGet('carijenis','');
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
		}else{
			$where.=" and a.tanggal>='".$_SESSION['org']['period']['start']."'";
		}
		if($carijenis!=''){
			$where.=" and a.jenis='".$carijenis."'";
		}
		$strb="select a.kodeorg,a.tanggal,a.jenis from ".$dbname.".kebun_adjpanen a 
				where ".$where." 
				order by a.tanggal,a.kodeorg,a.jenis";
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
		//$str="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".kebun_adjpanen a 
		//		left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
		//		left join ".$dbname.".datakaryawan d on d.nik=a.nik
		//		where ".$where." 
		//		order by a.kodeorg,a.kodebarang,a.kodeinventaris limit ".$offset.",".$limit."";
		$str="select a.*,b.namaorganisasi from ".$dbname.".kebun_adjpanen a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				where ".$where." 
				order by a.tanggal desc,a.kodeorg,a.jenis limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".substr($bar->kodeorg,0,4)."</td>
					<td ".$drcl." align=center>".substr($bar->kodeorg,0,6)."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl." align=center>".substr($bar->waktu,0,5)."</td>
					<td ".$drcl." align=left>".$bar->jenis."</td>
					<td ".$drcl." align=right>".number_format($bar->janjang,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->kg,0,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->supirlangsir."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td ".$drcl." align=left>".$bar->catatan."</td>
					<td align=center width='6%'>";
			if(tanggalsystem(tanggalnormal($bar->tanggal))>=$_SESSION['org']['period']['start']){
				echo"	<img src='images/skyblue/edit.png' class='resicon' title='Edit'	
						onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->jenis."','".substr($bar->waktu,0,5)."','".$bar->janjang."','".$bar->supirlangsir."','".$bar->keterangan."','')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->jenis."');\">&nbsp";
			}
			echo"		<img src=images/pdf.jpg class=resicon title='Print BA'
						onclick=\"preview_BAPDF('".$bar->kodeorg."','".$bar->tanggal."','".$bar->jenis."',event);\">
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
		$strx="delete from ".$dbname.".kebun_adjpanen 
				where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and jenis='".$jenis."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		$kgtbs=0;
		if($kodeorg!=''){
			//$strm="select bjr from ".$dbname.".kebun_5bjr where (kodeorg='".$kodeorg."' and tahunproduksi='".substr($tanggal,0,4)."')";
			$strm="select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$kodeorg."' order by tahunproduksi desc limit 1";
			$resm=mysql_query($strm);
			while($barm=mysql_fetch_object($resm)){
				$kgtbs=$barm->bjr*$janjang;
			}
		}
		if($addedit=='update'){
			if($kodeorglama!=$kodeorg and $tanggallama!=$tanggal and $jenislama!=$jenis){
				$strs="select * from ".$dbname.".kebun_adjpanen
						where (kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and jenis='".$jenis."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".kebun_adjpanen set kodeorg='".$kodeorg."',tanggal='".$tanggal."',jenis='".$jenis."',waktu='".$waktu."'
				,janjang='".$janjang."',kg='".$kgtbs."',supirlangsir='".$supirlangsir."',keterangan='".$keterangan."',catatan='".$catatan."'
				,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".$kodeorglama."' and tanggal='".$tanggallama."' and jenis='".$jenislama."'";
		}else{
			$strx="insert into ".$dbname.".kebun_adjpanen
				(tanggal,kodeorg,jenis,waktu,janjang,kg,supirlangsir,keterangan,catatan,lastuser,lastdate)
				values('".$tanggal."','".$kodeorg."','".$jenis."','".$waktu."','".$janjang."','".$kgtbs."','".$supirlangsir."','".$keterangan."','".$catatan."'
				,'".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getDivisi':
		$optDivisi="";
		if($unit!=''){
			$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='AFDELING' and induk='".$unit."' order by kodeorganisasi";
			$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
			$optDivisi="<option value=''></option>";
			while($rDivisi=mysql_fetch_assoc($qDivisi)){
				$optDivisi.="<option value=".$rDivisi['kodeorganisasi'].">[".$rDivisi['kodeorganisasi'].']-'.$rDivisi['namaorganisasi']."</option>";
			}
		}
		echo $optDivisi;
    break;

	case'getBlok':
		$optBlok="";
		if($unit!='' or $divisi!=''){
			$wheredivisi="";
			if($unit!=''){
				$wheredivisi.=" and left(kodeorganisasi,4)='".$unit."'";
			}
			if($divisi!=''){
				$wheredivisi.=" and induk='".$divisi."'";
			}
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='BLOK' ".$wheredivisi." order by kodeorganisasi";
			//exit('Warning: '.$sBlok);
			$qBlok=mysql_query($sBlok) or die(mysql_error($conn));
			$optBlok="<option value=''></option>";
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">[".$rBlok['kodeorganisasi'].']-'.$rBlok['namaorganisasi']."</option>";
			}
		}
		echo $optBlok;
    break;

	case'getCatatan':
		$optCatatan='';
		$whereidx="";
		if($kodeorg!=''){
			$whereidx.=" and kodeorg='".$kodeorg."'";
		}
		if($tanggal!=''){
			$whereidx.=" and tanggal='".$tanggal."'";
		}
		if($jenis!=''){
			$whereidx.=" and jenis='".$jenis."'";
		}
		$sData="select catatan from ".$dbname.".kebun_adjpanen where true ".$whereidx."";
		//exit('Warning: '.$sData);
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			$optCatatan=$rData['catatan'];
		}
		echo $optCatatan;
    break;

	default:
	break;
}
?>
