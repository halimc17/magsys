<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##idFranco##nmFranco##almtFranco##cntcPerson##hdnPhn##statFr##method";
$method=	checkPostGet('method','');
$idFranco=	checkPostGet('idFranco','');
$nmFranco=	checkPostGet('nmFranco','');
$almtFranco=checkPostGet('almtFranco','');
$cntcPerson=checkPostGet('cntcPerson','');
$hdnPhn=	checkPostGet('hdnPhn','');
$statFr=	checkPostGet('statFr','');


	switch($method)
	{
		case'insert':
		if($nmFranco=='')
		{
			echo"warning:Nama Franco tidak boleh kosong";
			exit();
		}
		$sCek="select franco_name from ".$dbname.".setup_franco where franco_name='".$nmFranco."'";
		$qCek=mysql_query($sCek) or die(mysql_error($conn));
		$rCek=mysql_num_rows($qCek);
		if($rCek>0)
		{
			echo"warning:Nama Franco sudah ada";
			exit();
		}
		else
		{
			if(($almtFranco=='')||($cntcPerson==''))
			{
				echo"warning:Alamat dan Contat Person tidak boleh kosong";
				exit();
			}
			else
			{
				$sIns="insert into ".$dbname.".setup_franco (`franco_name`,`alamat`,`contact`,`handphone`,`status`,`updateby`) values ('".$nmFranco."','".$almtFranco."','".$cntcPerson."','".$hdnPhn."','".$statFr."','".$_SESSION['standard']['userid']."')";
				if(!mysql_query($sIns))
				{
					echo"Gagal".mysql_error($conn);
				}
			}
		}
		break;
		case'loadData':
		$no=0;	 
		$arr=array("Aktif","Tidak Aktif");
		$str="select * from ".$dbname.".setup_franco order by id_franco desc";
		$res=mysql_query($str);
		while($bar=mysql_fetch_assoc($res))
		{
		$no+=1;	
		echo"<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$bar['franco_name']."</td>
		<td>".$bar['alamat']."</td>
		<td>".$bar['contact']."</td>
		<td>".$bar['handphone']."</td>
		<td>".$arr[$bar['status']]."</td>
		<td>
			  <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['id_franco']."');\"> 
			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$bar['id_franco']."');\">
		  </td>
		
		</tr>";	
		}     
		break;
		case'update':
		if(($almtFranco=='')||($cntcPerson==''))
		{
			echo"warning:Alamat dan Contat Person tidak boleh kosong";
			exit();
		}
		else
		{
			$sUpd="update ".$dbname.".setup_franco set `alamat`='".$almtFranco."',`contact`='".$cntcPerson."',`handphone`='".$hdnPhn."',`status`='".$statFr."' where id_franco='".$idFranco."'";
			if(!mysql_query($sUpd))
			{
				echo"Gagal".mysql_error($conn);
			}
		}
		break;
		case'delData':
		$sDel="delete from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
		if(!mysql_query($sDel))
		{
			echo"Gagal".mysql_error($conn);
		}
		break;
		case'getData':
		$sDt="select * from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
		$qDt=mysql_query($sDt) or die(mysql_error($conn));
		$rDet=mysql_fetch_assoc($qDt);
		echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['contact']."###".$rDet['handphone']."###".$rDet['status'];
		break;
		default:
		break;
	}
?>