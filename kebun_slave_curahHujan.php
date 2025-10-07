<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$proses=	checkPostGet('proses','');
$kdORg=		checkPostGet('kdOrg','');
$daTpagi=	checkPostGet('daTpagi','');
$daTsore=	checkPostGet('daTsore','');
$daTmalam=	checkPostGet('daTmalam','');
$note=		checkPostGet('note','');
$daTtgl=	tanggalsystem(checkPostGet('daTtgl',''));
$lokasi=	$_SESSION['empl']['lokasitugas'];

switch($proses)
{
	case'LoadData':
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_curahhujan where `kodeorg` like  '".$lokasi."%' order by `tanggal` desc";// echo $ql2;
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		
		
		$str="select * from ".$dbname.".kebun_curahhujan where `kodeorg` like '".$lokasi."%' order by tanggal desc limit ".$offset.",".$limit."";
		if(mysql_query($str))
		{
            $res=mysql_query($str);
			$no=0;
			while($bar=mysql_fetch_object($res))
			{
			$spr="select namaorganisasi from  ".$dbname.".organisasi where  kodeorganisasi='".$bar->kodeorg."'";
			$rep=mysql_query($spr) or die(mysql_error($conn));
			$bas=mysql_fetch_object($rep);
			$no+=1;
			
			//echo $minute_selesai; exit();
			echo"<tr class=rowcontent id='tr_".$no."'>
			<td>".$no."</td>
			<td id='nmorg_".$no."'>".$bas->namaorganisasi."</td>
			<td id='kpsits_".$no."'>".tanggalnormal($bar->tanggal)."</td>
			<td id='strt_".$no."'>".$bar->pagi."</td>
			<td id='end_".$no."'>".$bar->sore."</td>
			<td id='mlm_".$no."'>".$bar->malam."</td>
			<td id='tglex_".$no."'>".$bar->catatan."</td>
			<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\"><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\"><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."',event);\"></td>
			</tr>";
			}	 	 
			echo"
			<tr><td colspan=7 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
			<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr>";     	
		}	
		else
		{
		echo " Gagal,".(mysql_error($conn));
		}	
		break;
	
	case'insert':
		if(($kdORg=='')||($daTpagi=='')||($daTsore=='')||($daTmalam=='')||($daTtgl=='')||($note==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}
		$tglCek=explode("-",$_POST['daTtgl']);
		$thnSkrng=date("Y");
		$blnSkrng=date("m");
		
		$sCek="select kodeorg,tanggal from ".$dbname.".kebun_curahhujan where kodeorg='".$kdORg."' and tanggal='".$daTtgl."'";
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_num_rows($qCek);
		if($rCek<1)
		{
			$sIns="insert into ".$dbname.".kebun_curahhujan (kodeorg, tanggal, pagi, sore, malam, catatan) values ('".$kdORg."','".$daTtgl."','".$daTpagi."','".$daTsore."','".$daTmalam."','".$note."')";
			if(mysql_query($sIns))
			echo"";
			else
			echo "DB Error : ".mysql_error($conn);
		}
		else
		{
			echo"warning:Data Already Entry";
			exit();
		}
		break;
	
	case'showData':
		$sql="select catatan,pagi,sore,malam from ".$dbname.".kebun_curahhujan where kodeorg='".$kdORg."' and tanggal='".$daTtgl."'";
		//echo"warning".$sql;
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_assoc($query);
		echo $res['catatan']."###".$res['pagi']."###".$res['sore']."###".$res['malam'];
		break;
		case'update':
		if(($kdORg=='')||($daTpagi=='')||($daTsore=='')||($daTtgl=='')||($note==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}
			$sUpd="update ".$dbname.".kebun_curahhujan set  pagi='".$daTpagi."', sore='".$daTsore."', malam='".$daTmalam."', catatan='".$note."' where  kodeorg='".$kdORg."' and tanggal='".$daTtgl."'";
			//echo "warning:".$sUpd;exit();
			if(mysql_query($sUpd))
			echo"";
			else
			echo "DB Error : ".mysql_error($conn);
		
		break;
	
	case'delData':
		$sDel="delete from ".$dbname.".kebun_curahhujan where  kodeorg='".$kdORg."' and tanggal='".$daTtgl."'";
		if(mysql_query($sDel))
		echo"";
		else
		echo "DB Error : ".mysql_error($conn);
		break;
	
	case'CekData':
		if(!preg_match("/e$/i",$lokasi)) {
			echo"warning:You Not In Estate";
			exit();
		}
		break;
	
	case'cariData':
		if(preg_match("/e$/i",$lokasi)) {
			$limit=10;
			$page=0;
			if(isset($_POST['page']))
			{
			$page=$_POST['page'];
			if($page<0)
			$page=0;
			}
			$offset=$page*$limit;
			if(($kdORg!='')&&($daTtgl!=''))
			{
				$where=" kodeorg='".$kdORg."' and tanggal='".$daTtgl."'";
			}
			elseif($kdORg!='')
			{
				$where=" kodeorg='".$kdORg."'";
			}
			elseif($daTtgl!='')
			{
				$where=" tanggal='".$daTtgl."' and kodeorg = '".$lokasi."'";
			}
			
			elseif(($kdORg=='')&&($daTtgl==''))
			{
				echo"warning:Please Insert Data";	
				exit();
			}
			$sCek="select * from ".$dbname.".kebun_curahhujan where ".$where."";
			//echo"warning:".$sCek; 
			$qCek=mysql_query($sCek) or die(mysql_error());
			$rCek=mysql_num_rows($qCek);
			if($rCek>0)
			{
				$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_curahhujan where ".$where." order by `tanggal` desc";// echo $ql2;
				$query2=mysql_query($ql2) or die(mysql_error());
				while($jsl=mysql_fetch_object($query2)){
				$jlhbrs= $jsl->jmlhrow;
				}
				
				
				$str="select * from ".$dbname.".kebun_curahhujan where ".$where." order by tanggal desc limit ".$offset.",".$limit."";
				//echo"warning:".$str; exit();
				if($res=mysql_query($str))
				{
					$no=0;
				while($bar=mysql_fetch_object($res))
				{
				$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$bar->kodeorg."'";
				$rep=mysql_query($spr) or die(mysql_error($conn));
				$bas=mysql_fetch_object($rep);
				$no+=1;
				
				//echo $minute_selesai; exit();
				echo"<tr class=rowcontent id='tr_".$no."'>
				<td>".$no."</td>
				<td id='nmorg_".$no."'>".$bas->namaorganisasi."</td>
				<td id='kpsits_".$no."'>".tanggalnormal($bar->tanggal)."</td>
				<td id='strt_".$no."'>".$bar->pagi."</td>
				<td id='end_".$no."'>".$bar->sore."</td>
				<td id='mlm_".$no."'>".$bar->malam."</td>
				<td id='tglex_".$no."'>".$bar->catatan."</td>
				<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\"><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\"><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."',event);\"></td>
				</tr>";
				}	 	 
				echo"
				<tr class=rowheader><td colspan=7 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";     	
				}	
				else
				{
				echo " Gagal,".(mysql_error($conn));
				}	
			}
			else
			{
				echo"<tr class=rowcontent><td colspan=8 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}
		}
		else
		{
			echo"warning:You Not In Estate";
			exit();
		}
	
		break;
		default:
		break;
	}

?>