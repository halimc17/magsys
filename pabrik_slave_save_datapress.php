<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

	$kodeorg	  =checkPostGet('kodeorg','');
    $tanggal	  =tanggalsystem(checkPostGet('tanggal',''));

	$tekpressp1=checkPostGet('tekpressp1','');
	$tekpressp2=checkPostGet('tekpressp2','');
	$tekpressp3=checkPostGet('tekpressp3','');
	$tekpressp4=checkPostGet('tekpressp4','');
	$suhud1=checkPostGet('suhud1','');
	$suhud2=checkPostGet('suhud2','');
	$suhud3=checkPostGet('suhud3','');
	$suhud4=checkPostGet('suhud4','');
	$jampressp1=checkPostGet('jampressp1','');
	$jampressp2=checkPostGet('jampressp2','');
	$jampressp3=checkPostGet('jampressp3','');
	$jampressp4=checkPostGet('jampressp4','');

	$airkemarin=checkPostGet('airkemarin','');
	$airclarifier =checkPostGet('airclarifier','');
	$airboiler =checkPostGet('airboiler','');
	$airproduksi=checkPostGet('airproduksi','');
	$airpembersihan=checkPostGet('airpembersihan','');
	$airdomestik =checkPostGet('airdomestik','');
	$airsisa =checkPostGet('airsisa','');

	if(isset($_POST['del'])){
		$strx="delete from ".$dbname.".pabrik_datapress 
		       where kodeorg='".$kodeorg."' 
			   and tanggal='".$_POST['tanggal']."'";   
	}else{
		$strs="select * from ".$dbname.".pabrik_datapress 
		       where kodeorg='".$kodeorg."' 
			   and tanggal='".tanggalsystem($_POST['tanggal'])."'";
		$ress=mysql_query($strs);
		$rows=mysql_num_rows($ress);
		if($rows>0){
			$strx="update ".$dbname.".pabrik_datapress set kodeorg='".$kodeorg."',tanggal=".$tanggal."
					,tekpressp1=".$tekpressp1.",tekpressp2=".$tekpressp2.",tekpressp3=".$tekpressp3.",tekpressp4=".$tekpressp4."
					,suhud1=".$suhud1.",suhud2=".$suhud2.",suhud3=".$suhud3.",suhud4=".$suhud4."
					,jampressp1=".$jampressp1.",jampressp2=".$jampressp2.",jampressp3=".$jampressp3.",jampressp4=".$jampressp4."
					,airclarifier=".$airclarifier.",airboiler=".$airboiler.",airproduksi=".$airproduksi.",airpembersihan=".$airpembersihan.",airdomestik=".$airdomestik."
					,airsisa=".$airsisa.",karyawanid=".$_SESSION['standard']['userid']."
					where kodeorg='".$kodeorg."' 
					and tanggal='".tanggalsystem($_POST['tanggal'])."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_datapress
					(kodeorg,tanggal,tekpressp1,tekpressp2,tekpressp3,tekpressp4,suhud1,suhud2,suhud3,suhud4,jampressp1,jampressp2,jampressp3,jampressp4
					,airclarifier,airboiler,airproduksi,airpembersihan,airdomestik,airsisa,karyawanid)
					values('".$kodeorg."',".$tanggal.",".$tekpressp1.",".$tekpressp2.",".$tekpressp3.",".$tekpressp4.",
					".$suhud1.",".$suhud2.",".$suhud3.",".$suhud4.",".$jampressp1.",".$jampressp2.",".$jampressp3.",".$jampressp4.",
					".$airclarifier.",".$airboiler.",".$airproduksi.",".$airpembersihan.",".$airdomestik.",".$airsisa.",".$_SESSION['standard']['userid'].")";
		}
	}

	if(mysql_query($strx)){
		$str="select a.* from ".$dbname.".pabrik_datapress a where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
				order by a.tanggal desc limit 31";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$airkemarin=$bar->airsisa-$bar->airclarifier+$bar->airboiler+$bar->airproduksi+$bar->airpembersihan+$bar->airdomestik;
			$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=center>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp1,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp2,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp3,0,'.',',.')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp4,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->suhud1,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->suhud2,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->suhud3,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->suhud4,0,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp1,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp2,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp3,2,'.',',')."</td>
					<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp4,2,'.',',')."</td>

					<td ".$drcl." align=right width='6%'>".number_format($airkemarin,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airclarifier,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airboiler,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airproduksi,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airpembersihan,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airdomestik,0,'.',',')."</td>
					<td ".$drcl." align=right width='6%'>".number_format($bar->airsisa,0,'.',',')."</td>
		   
					<td>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->tekpressp1."','".$bar->tekpressp2."','".$bar->tekpressp3."'
						,'".$bar->tekpressp4."','".$bar->suhud1."','".$bar->suhud2."','".$bar->suhud3."','".$bar->suhud4."','".$bar->jampressp1."','".$bar->jampressp2."'
						,'".$bar->jampressp3."','".$bar->jampressp4."','".$airkemarin."','".$bar->airclarifier."','".$bar->airboiler."','".$bar->airproduksi."'
						,'".$bar->airpembersihan."','".$bar->airdomestik."','".$bar->airsisa."',)\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->tanggal."','".(isset($bar->kodebarang)? $bar->kodebarang:'')."');\">
					</td>
				</tr>";	
		}	  
	}else{
		echo " Gagal,".addslashes(mysql_error($conn));
	}
?>
