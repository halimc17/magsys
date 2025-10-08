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
$diperiksa=checkPostGet('diperiksa','');
$diperiksalama=checkPostGet('diperiksalama','');
$pokok=checkPostGet('pokok',0);
$brondolan=checkPostGet('brondolan',0);
$janjang=checkPostGet('janjang',0);
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
$carikodeorg=checkPostGet('carikodeorg','');
$caritanggal1=tanggalsystem(checkPostGet('caritanggal1',''));
$caritanggal2=tanggalsystem(checkPostGet('caritanggal2',''));
$carikary=checkPostGet('carikary','');
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
		if($carikary!=''){
			$where.=" and a.karyawanid='".$carikary."'";
		}
		$strb="select a.kodeorg,a.tanggal,a.karyawanid from ".$dbname.".kebun_cekancak a 
				where ".$where." 
				order by a.tanggal,a.kodeorg,a.karyawanid";
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
		$str="select a.*,b.namaorganisasi,c.namakaryawan
				from ".$dbname.".kebun_cekancak a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				left join ".$dbname.".datakaryawan c on c.karyawanid=a.karyawanid
				where ".$where." 
				order by a.tanggal desc,a.kodeorg,a.karyawanid limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$drcl="";
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$lossesbt=$bar->janjang/$bar->pokok*$bar->bjr*$bar->sph;
			$lossesbrd=$bar->brondolan/$bar->pokok*0.014*$bar->sph;
			if($lossesbrd<=0.5){
				$nrmclr="";
			}else{
				//$nrmclr="bgcolor='#FF0000'";
				$nrmclr="style='color:#FF0000;'";
			}
			//exit('Warning: '.$nrmclr);
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".substr($bar->kodeorg,0,4)."</td>
					<td ".$drcl." align=center>".substr($bar->kodeorg,0,6)."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=right>".number_format($bar->bjr,2,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->sph,0,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->namakaryawan."</td>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl." align=right>".number_format($bar->pokok,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->janjang,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->brondolan,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($lossesbt,2,'.',',')."</td>
					<td ".$drcl." ".$nrmclr." align=right>".number_format($lossesbrd,2,'.',',')."</td>
					<td ".$drcl." ".$nrmclr." align=right>".number_format($lossesbt+$lossesbrd,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='5%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->karyawanid."',".$bar->pokok.",".$bar->brondolan.",".$bar->janjang.",'".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->karyawanid."');\">&nbsp
						<!--<img src=images/pdf.jpg class=resicon title='Print PDF' onclick=\"preview_PDF('".$bar->kodeorg."','".$bar->tanggal."','".$bar->karyawanid."',event);\">-->
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
		$strx="delete from ".$dbname.".kebun_cekancak 
				where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$diperiksa."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		if($addedit=='update'){
			if($kodeorglama!=$kodeorg and $tanggallama!=$tanggal and $diperiksalama!=$diperiksa){
				$strs="select * from ".$dbname.".kebun_cekancak
						where (kodeorg='".$kodeorg."' and tanggal='".$tanggal."' and karyawanid='".$diperiksa."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".kebun_cekancak set kodeorg='".$kodeorg."',tanggal='".$tanggal."',karyawanid='".$diperiksa."',pokok='".$pokok."'
				,brondolan='".$brondolan."',janjang='".$janjang."',keterangan='".$keterangan."'
				,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where kodeorg='".$kodeorglama."' and tanggal='".$tanggallama."' and karyawanid='".$diperiksalama."'";
		}else{
			$sstblok="select a.kodeorg,e.bjr,round(if(a.luasareaproduktif=0,0,a.jumlahpokok/a.luasareaproduktif),0) as sph from ".$dbname.".setup_blok a
						left join ".$dbname.".kebun_5bjr e on e.kodeorg=a.kodeorg and e.tahunproduksi=year('".$tanggal."')
						where a.kodeorg='".$kodeorg."' order by a.kodeorg";
			//exit('Warning: '.$sstblok);
			$qstblok=mysql_query($sstblok) or die(mysql_error($conn));
			$bjr=0;
			$sph=0;
			while($rstblok=mysql_fetch_assoc($qstblok)){
				$bjr=$rstblok['bjr'];
				$sph=$rstblok['sph'];
			}
			$strx="insert into ".$dbname.".kebun_cekancak
				(kodeorg,bjr,sph,tanggal,karyawanid,pokok,brondolan,janjang,keterangan,lastuser,lastdate)
				values('".$kodeorg."',$bjr,$sph,'".$tanggal."','".$diperiksa."','".$pokok."','".$brondolan."','".$janjang."','".$keterangan."'
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

	case'getKary':
		$optKary="";
		if($unit!=''){
			$sKary="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan 
					where (tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and lokasitugas='".$unit."' 
					and kodejabatan in (4,283,330,331,332,333) order by kodeorganisasi";
			//exit('Warning: '.$sKary);
			$qKary=mysql_query($sKary) or die(mysql_error($conn));
			$optKary="<option value=''></option>";
			while($rKary=mysql_fetch_assoc($qKary)){
				$optKary.="<option value=".$rKary['karyawanid'].">[".$rKary['nik'].'] - '.$rKary['namakaryawan']."</option>";
			}
		}
		echo $optKary;
    break;

	default:
	break;
}
?>
