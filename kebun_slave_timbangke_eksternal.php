<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;


switch($param['proses']) {
	case'getNosbp':
	$tglpr=explode("-",$param['tgl']);
	$optDtSpb="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if($param['nospb']!=''){
		$optDtSpb.="<option value='".$param['nospb']."' selected>".$param['nospb']."</option>";
	}
	
	$sDtSpb="select nospb from ".$dbname.".kebun_spbht where left(tanggal,7)='".$tglpr[2]."-".$tglpr[1]."' and posting=0 and kodeorg='".$_SESSION['empl']['lokasitugas']."' and tujuan=3";
		$qDtSpb=mysql_query($sDtSpb) or die(mysql_error($conn));
		while($rDtSpb=mysql_fetch_assoc($qDtSpb)){
			$sCek="select * from ".$dbname.".pabrik_timbangan where nospb='".$rDtSpb['nospb']."'";
			$qCek=mysql_query($sCek) or die(mysql_error($conn));
			$rCek=mysql_num_rows($qCek);
			if($rCek==0){
				$optDtSpb.="<option value='".$rDtSpb['nospb']."'>".$rDtSpb['nospb']."</option>";
			}
		}
		echo $optDtSpb;
	break;
	case'insert':
		$whr="nospb='".$param['spbId']."'";
		$optCust=makeOption($dbname,'kebun_spbht','nospb,penerimatbs',$whr);
			$scek="select max(notransaksi) as notransaksi from ".$dbname.".pabrik_timbangan where char_length(notransaksi)>7 and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
			$qcek=mysql_query($scek) or die(mysql_error($conn));
			$rcek=mysql_fetch_assoc($qcek);
			if($rcek['notransaksi']==''){
				$rcek['notransaksi']=0;
			}else{
				$rcek['notransaksi']=substr($rcek['notransaksi'],-6,6);
			}
			$notrans=$_SESSION['empl']['lokasitugas'].addZero((intval($rcek['notransaksi'])+1),6);
			if(($param['tgl']=='')||($param['kdKend']=='')||($param['nmSupir']=='')||($param['jmlhJjg']=='')||($param['brtMsk']=='')||($param['brtKlr']=='')||($param['spbId']=='')){
				exit("error: Seluruh field tidak boleh kosong");
			}
			
			$sins="insert into ".$dbname.".pabrik_timbangan (notransaksi,tanggal,kodeorg,kodecustomer,jumlahtandan1,kodebarang,jammasuk,beratmasuk,jamkeluar,beratkeluar,nokendaraan,supir,nospb,timbangonoff,intex,millcode,beratbersih,jjgsortasi,kgpotsortasi,username) values ";
			$sins.="('".$notrans."','".tanggalsystem($param['tgl'])."','".$_SESSION['empl']['lokasitugas']."','".$optCust[$param['spbId']]."','".$param['jmlhJjg']."','40000003','".$param['jamMasuk']."','".$param['brtMsk']."','".$param['jamKeluar']."','".$param['brtKlr']."','".$param['kdKend']."','".$param['nmSupir']."','".$param['spbId']."','1','1','EXTM','".$param['brtBrsh']."','".$param['JjgSortasi']."','".$param['potKg']."','".$_SESSION['standard']['username']."')";

		
		if(!mysql_query($sins)){
			exit("error: ".mysql_error($conn)."__".$sins);
		}
	break;
	case'update':
		if(($param['tgl']=='')||($param['kdKend']=='')||($param['nmSupir']=='')||($param['jmlhJjg']=='')||($param['brtMsk']=='')||($param['brtKlr']=='')||($param['spbId']=='')){
				exit("error: Seluruh field tidak boleh kosong");
			}
		$sins="update ".$dbname.".pabrik_timbangan set tanggal='".tanggalsystem($param['tgl'])."',kodeorg='".$_SESSION['empl']['lokasitugas']."',kodecustomer='".$optCust[$param['spbId']]."',jumlahtandan1='".$param['jmlhJjg']."',jammasuk='".$param['jamMasuk']."',beratmasuk='".$param['brtMsk']."',jamkeluar='".$param['jamKeluar']."',beratkeluar='".$param['brtKlr']."',nokendaraan='".$param['kdKend']."',supir='".$param['nmSupir']."',nospb='".$param['spbId']."',beratbersih='".$param['brtBrsh']."',jjgsortasi='".$param['JjgSortasi']."',kgpotsortasi='".$param['potKg']."',username='".$_SESSION['standard']['username']."' where notransaksi='".$param['notransaksi']."'";
		if(!mysql_query($sins)){
			exit("error: ".mysql_error($conn)."__".$sins);
		}	
	break;
    case'loadNewData':
		echo"<table cellspacing='1' border='0' class='sortable'>
			 <thead>
			 <tr class=rowheader>
			 <td>No.</td>
			 <td>".$_SESSION['lang']['noTiket']."</td>
			 <td>".$_SESSION['lang']['tanggal']."</td>
			 <td>".$_SESSION['lang']['nospb']."</td>
			 <td>".$_SESSION['lang']['nopol']."</td>
			 <td>".$_SESSION['lang']['supir']."</td>
			 <td>".$_SESSION['lang']['jjg']."</td>
			 <td>".$_SESSION['lang']['beratMasuk']."</td>
			 <td>".$_SESSION['lang']['beratKeluar']."</td>
			 <td>".$_SESSION['lang']['beratBersih']."</td>
			 <td>".$_SESSION['lang']['jjg']." Sortasi</td>
			 <td>".$_SESSION['lang']['potongan']."</td>
			 <td>Action</td>
			 </tr>
			 </thead><tbody>";
		$whrCr="";
		if($param['nosbpCr']!=''){
			$whrCr.=" and nospb like '%".$param['nosbpCr']."%'";
		}
		if($param['tgl_cari']!=''){
			$whrCr.=" and tanggal like '%".tanggalsystemn($param['tgl_cari'])."%'";
		}
		$limit=20;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		$ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan 
			  where kodeorg='".$_SESSION['empl']['lokasitugas']."' and char_length(notransaksi)>7  ".$whrCr." order by `tanggal` desc";
		$slvhc="select * from ".$dbname.".pabrik_timbangan 
				where kodeorg='".$_SESSION['empl']['lokasitugas']."' and char_length(notransaksi)>7 ".$whrCr."
				order by `tanggal` desc limit ".$offset.",".$limit."";
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$user_online=$_SESSION['standard']['userid'];
		$no=0;
		while($rData=mysql_fetch_assoc($qlvhc)){
			$no+=1;
			echo"
			<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$rData['notransaksi']."</td>
			<td>".tanggalnormal(substr($rData['tanggal'],0,10))."</td>
			<td>".$rData['nospb']."</td>
			<td>".$rData['nokendaraan']."</td>
			<td>".$rData['supir']."</td>
			<td align='right'>".$rData['jumlahtandan1']."</td>
			<td align='right'>".number_format($rData['beratmasuk'],0)."</td>
			<td align='right'>".number_format($rData['beratkeluar'],0)."</td>
			<td align='right'>".number_format($rData['beratbersih'],0)."</td>
			<td align='right'>".$rData['jjgsortasi']."</td>
			<td align='right'>".$rData['kgpotsortasi']."</td>
			<td>";
			$whr="nospb='".$rData['nospb']."'";
			$optStat=makeOption($dbname,'kebun_spbht','nospb,posting',$whr);
				if(($_SESSION['standard']['username']==$rData['username'])||($optStat[$rData['nospb']]=='0')){
					echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rData['notransaksi']."','".$rData['jammasuk']."','".$rData['jamkeluar']."','".$rData['nokendaraan']."','".$rData['supir']."','".$rData['jumlahtandan1']."','".$rData['beratmasuk']."','".$rData['beratkeluar']."','".$rData['beratbersih']."','".$rData['jjgsortasi']."','".$rData['kgpotsortasi']."','".$rData['nospb']."','".tanggalnormal(substr($rData['tanggal'],0,10))."');\">&nbsp";
					echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteData('".$rData['notransaksi']."');\">&nbsp";
					echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','".$rData['notransaksi']."','','pabrik_timbanganPdf',event)\">";
				} else {
					echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','".$rData['notransaksi']."','','pabrik_timbanganPdf',event)\">";
				}
			 
            echo"</td></tr>";
        }
		echo"</tbody><tfoot>
		<tr><td colspan=13 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tfoot></table>";
        break;
    case'deleteData':
		$sDel="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$param['notransaksi']."'";
		if(!mysql_query($sDel)){
			echo "DB Error : ".mysql_error($conn);
		}
		break;
    default:
    break;
}