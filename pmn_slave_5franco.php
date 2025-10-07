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
$almtFranco=	checkPostGet('almtFranco','');
$nmFranco=	checkPostGet('nmFranco','');
$jual=checkPostGet('jual','');
$statFr=	checkPostGet('statFr','');
$arrX=array('franco'=>'Franco','loco'=>'Loco','fob'=>'FOB');

	switch($method)
	{
		case'insert':
		if($nmFranco=='')
		{
			echo"warning:Nama Franco tidak boleh kosong";
			exit();
		}
		$sCek="select franco_name from ".$dbname.".pmn_5franco where franco_name='".$nmFranco."'";
		$qCek=mysql_query($sCek) or die(mysql_error($conn));
		$rCek=mysql_num_rows($qCek);
		if($rCek>0)
		{
			echo"warning:Nama Franco sudah ada";
			exit();
		}
		else
		{
			if($almtFranco=='')
			{
				echo"warning:Alamat tidak boleh kosong";
				exit();
			}
			else
			{
				$sIns="insert into ".$dbname.".pmn_5franco (`franco_name`,`alamat`,`penjualan`,`status`,`updateby`) values ('".$nmFranco."','".$almtFranco."','".$jual."','".$statFr."','".$_SESSION['standard']['userid']."')";
				//exit("Error:$sIns");
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
		$str="select * from ".$dbname.".pmn_5franco order by id_franco desc";
		$res=mysql_query($str);
		while($bar=mysql_fetch_assoc($res))
		{
		$no+=1;	
		echo"<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$bar['franco_name']."</td>
		<td>".$bar['alamat']."</td>
		<td>".$arrX[$bar['penjualan']]."</td>
		<td>".$arr[$bar['status']]."</td>
		<td>
			  <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['id_franco']."');\"> 
			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$bar['id_franco']."');\">
		  </td>
		
		</tr>";	
		}     
		break;
		case'update':
		if($almtFranco=='')
		{
			echo"warning:Alamat tidak boleh kosong";
			exit();
		}
		else
		{
			$sUpd="update ".$dbname.".pmn_5franco set `franco_name`='".$nmFranco."',`alamat`='".$almtFranco."',`penjualan`='".$jual."',`status`='".$statFr."' where id_franco='".$idFranco."'";
			if(!mysql_query($sUpd))
			{
				echo"Gagal".mysql_error($conn);
			}
		}
		break;
		case'delData':
		$sDel="delete from ".$dbname.".pmn_5franco where id_franco='".$idFranco."'";
		if(!mysql_query($sDel))
		{
			echo"Gagal".mysql_error($conn);
		}
		break;
                
		case'getData':
		$sDt="select * from ".$dbname.".pmn_5franco where id_franco='".$idFranco."'";
		$qDt=mysql_query($sDt) or die(mysql_error($conn));
		$rDet=mysql_fetch_assoc($qDt);
		echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['penjualan']."###".$rDet['status'];
		break;
		default:
		break;
	}
?>