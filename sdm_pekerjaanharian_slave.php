<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$tgl_cari=tanggalsystem(checkPostGet('tgl_cari',''));
$sts_cari=checkPostGet('sts_cari','');
$nomor=checkPostGet('nomor','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$pekerjaan=checkPostGet('pekerjaan','');
$target=checkPostGet('target','');
$aktual=checkPostGet('aktual','');
$correction=checkPostGet('correction','');
$rencanakerja=checkPostGet('rencanakerja','');
$catatan=checkPostGet('catatan','');
$atasan=checkPostGet('atasan','');
$stspekerjaan=checkPostGet('stspekerjaan','');
$addedit=checkPostGet('addedit','');

switch($proses){
	case'loadNewData':
		if($tgl_cari!=''){
			$where.=" and a.tanggal='".$tgl_cari."' ";
		}
		if($sts_cari!=''){
			$where.=" and a.stspekerjaan='".$sts_cari."' ";
		}
		echo"<table cellspacing='1' border='0' class='sortable'>
				<thead align=center>
					<tr class=rowheader>
						<td width='30px'>No.</td>
						<td width='65px'>NIK</td>
						<td width='215px'>". $_SESSION['lang']['namakaryawan'] ."</td>
						<td width='75px'>". $_SESSION['lang']['tanggal'] ."</td>
						<td width='260px'>". $_SESSION['lang']['pekerjaan'] ."</td>
						<td width='260px'>Target</td>
						<td width='260px'>". $_SESSION['lang']['aktual'] ."</td>
						<td width='45px'>Action</td>
					</tr>
				</thead><tbody>";
		$limit=20;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',1);
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$ql2="select count(DISTINCT a.karyawanid,a.tanggal) as jmlhrow from ".$dbname.".sdm_pekerjaanharian a 
				where a.karyawanid='".$_SESSION['standard']['userid']."' ".$where." 
				order by a.tanggal desc";
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
        $slvhc="select a.karyawanid,a.tanggal,a.pekerjaan,b.nik,b.namakaryawan,c.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
				left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				left join ".$dbname.".datakaryawan c on c.karyawanid=a.atasan
				where a.karyawanid='".$_SESSION['standard']['userid']."' ".$where." 
				group by a.karyawanid,a.tanggal 
				order by a.karyawanid,a.tanggal desc limit ".$offset.",".$limit."";
		//exit('Warning: '.$slvhc);
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$user_online=$_SESSION['standard']['userid'];
		$no=($page*$limit);
		while($rlvhc=mysql_fetch_assoc($qlvhc)){
			$no+=1;
			$bgclr=" title='Click untuk melihat detail..!' style=\"cursor: pointer\" onclick=\"showpopup('".$rlvhc['karyawanid']."','".$rlvhc['namakaryawan']."','".tanggalnormal($rlvhc['tanggal'])."','',event);\"";
			echo"
			<tr class=rowcontent valign=center>
				<td ".$bgclr." align=center>".$no."</td>
                <td ".$bgclr." align=center>".$rlvhc['nik']."</td>
                <td ".$bgclr.">".$rlvhc['namakaryawan']."</td>
                <td ".$bgclr." align=center>".$rlvhc['tanggal']."</td>
                <td ".$bgclr.">".$rlvhc['pekerjaan']."</td>
                <td ".$bgclr.">".$rlvhc['target']."</td>
                <td ".$bgclr.">".$rlvhc['aktual']."</td>
                <td align=center>
					<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editData('".$rlvhc['karyawanid']."','".tanggalnormal($rlvhc['tanggal'])."');\">&nbsp
					<!-- <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['karyawanid']."','".tanggalnormal($rlvhc['tanggal'])."');\">&nbsp -->
					<img src=images/excel.jpg class=resicon title='Detail Excel' onclick=\"showpopup('".$rlvhc['karyawanid']."','".$rlvhc['namakaryawan']."','".tanggalnormal($rlvhc['tanggal'])."','excel',event);\">
				</td>
			</tr>";
		}
		echo"
		<tr class=rowheader>
			<td colspan=7 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
		echo"</tbody></table>";
		break;

	case'loadDetailData':
		$str="select a.*,b.nik,b.namakaryawan,c.namakaryawan as namaatasan from ".$dbname.".sdm_pekerjaanharian a
				left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid
				left join ".$dbname.".datakaryawan c on c.karyawanid=a.atasan
				where a.karyawanid='".$_SESSION['standard']['userid']."' and a.tanggal='".$tanggal."' 
				order by a.karyawanid,a.tanggal,a.nomor";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->karyawanid."','".$bar->tanggal."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent valign='center'>
					<td ".$drcl." align=center>".$bar->tanggal."</td>
					<td ".$drcl.">".$bar->pekerjaan."</td>
					<td ".$drcl.">".$bar->target."</td>
					<td ".$drcl.">".$bar->aktual."</td>
					<td ".$drcl.">".$bar->correction."</td>
					<td ".$drcl.">".$bar->rencanakerja."</td>
					<td ".$drcl.">".$bar->catatan."</td>
					<td ".$drcl.">".$bar->namaatasan."</td>
					<td ".$drcl.">".$bar->stspekerjaan."</td>";
			//if($bar->posting!='1'){
			if($bar->posting!='1' and $bar->entrydate>=date('Y-m-d')){
				echo"<td align=center>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->nomor."','".tanggalnormal($bar->tanggal)."','".$bar->pekerjaan."','".$bar->target."','".$bar->aktual."','".$bar->correction."','".$bar->rencanakerja."','".$bar->catatan."','".$bar->atasan."','".$bar->stspekerjaan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"delDetail('".$bar->nomor."','".$bar->karyawanid."','".tanggalnormal($bar->tanggal)."');\">&nbsp
						<img src=images/skyblue/posting.png class=resicon title='Posting' onclick=\"postDetail('".$bar->nomor."','".$bar->karyawanid."','".tanggalnormal($bar->tanggal)."');\">
					</td>";
			}else{
				echo"<td align=center>
						<img src=images/skyblue/posted.png class=resicon title='Posting'>
					</td>";
			}
			echo"</tr>";
		}
		break;

	case'saveData':
		$stbl="select * from ".$dbname.".sdm_pekerjaanharian 
				where karyawanid='".$_SESSION['standard']['userid']."' and tanggal='".$tanggal."' and nomor='".$nomor."'";
		$qtbl=mysql_query($stbl);
		if(mysql_num_rows($qtbl) > 0){
		//if($addedit=='update'){
			$strx="update ".$dbname.".sdm_pekerjaanharian set
					pekerjaan='".$pekerjaan."',target='".$target."',aktual='".$aktual."',correction='".$correction."',rencanakerja='".$rencanakerja."'
					,catatan='".$catatan."',atasan='".$atasan."',stspekerjaan='".$stspekerjaan."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where karyawanid='".$_SESSION['standard']['userid']."' and tanggal='".$tanggal."' and nomor='".$nomor."'";
		}else{
			$strx="insert into ".$dbname.".sdm_pekerjaanharian
					(karyawanid,tanggal,pekerjaan,target,aktual,correction,rencanakerja,catatan,atasan,stspekerjaan,posting,lastuser,lastdate,entrydate)
					values('".$_SESSION['standard']['userid']."','".$tanggal."','".$pekerjaan."','".$target."','".$aktual."','".$correction."'
						,'".$rencanakerja."','".$catatan."','".$atasan."','".$stspekerjaan."','0','".$_SESSION['standard']['username']."',now(),curdate())";
		}
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
		break;

	case'delDetail':
		$strx="delete from ".$dbname.".sdm_pekerjaanharian where karyawanid='".$_SESSION['standard']['userid']."' and tanggal='".$tanggal."' and nomor='".$nomor."'";
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'postDetail':
		$strx="update ".$dbname.".sdm_pekerjaanharian set posting='1' where karyawanid='".$_SESSION['standard']['userid']."' and tanggal='".$tanggal."' and nomor='".$nomor."'";
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".sdm_pekerjaanharian where karyawanid='".$_SESSION['standard']['userid']."' and tanggal='".$tanggal."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	default:
		break;
	}
?>
