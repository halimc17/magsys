<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses=checkPostGet('proses','');
$noTrans=checkPostGet('txtSearch','');
$notranssksi=checkPostGet('noTrans','');
$thisDate=date("Y-m-d");
$txtTgl=tanggalsystem(checkPostGet('txtTgl',''));
$statPost=checkPostGet('statPost','');
$userOnline = $_SESSION['standard']['userid'];

switch($proses)
{
	case'loadData':
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                    $wherelok="";
                }
                else
                {
                    $wherelok="where pabrik='".$_SESSION['empl']['lokasitugas']."' "; 
                }
               
                
                
		$ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_rawatmesinht ".$wherelok."   order by `notransaksi` desc";// echo $ql2;
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}

		$slvhc="select * from ".$dbname.".pabrik_rawatmesinht ".$wherelok."  order by `notransaksi` desc limit ".$offset.",".$limit."";
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$user_online=$_SESSION['standard']['userid'];
		while($rlvhc=mysql_fetch_assoc($qlvhc))
		{
		$no+=1;
		echo"
		<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$rlvhc['notransaksi']."</td>
		<td>".tanggalnormal($rlvhc['tanggal'])."</td>
		<td>".$rlvhc['shift']."</td>
		<td>".$rlvhc['statasiun']."</td>
		<td>".$rlvhc['mesin']."</td>
		<td>".tanggalnormald($rlvhc['jammulai'])."</td>
		<td>".tanggalnormald($rlvhc['jamselesai'])."</td>";
			if($rlvhc['statPost']=='0')
			{
				if($rlvhc['updateby']!=$userOnline)
				{
				echo"<td><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"postThis('".$rlvhc['notransaksi']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\"></td>";
				 } else {
					 echo"
				<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\"></td>";
				}
			}
			else
			{
			 echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\"></td>";
			}
		}	
		echo"
				 <tr><td colspan=9 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";
	break;
	case'postThis':
	$sCek="select statPost from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$notranssksi."'";
	$qCek=mysql_query($sCek) or die(mysql_error());
	$rCek=mysql_fetch_assoc($qCek);
	if($rCek['statPost']<1)
	{
		$sUpdate="update ".$dbname.".pabrik_rawatmesinht set statPost='1',postingby='".$_SESSION['standard']['userid']."',postingdate='".$thisDate."' where notransaksi='".$notranssksi."' ";
		if(!mysql_query($sUpdate)) {
	    echo "DB Error : ".mysql_error();
	    exit;
		}
	}
	else
	{
		echo"warning:No Transaksi ini telah terposting";
		exit();
	}
	break;
	case'cariTransaksi':
		$where="";
		if(!empty($noTrans)) {
			$where.="notransaksi='".$noTrans."'";
		}
		if(!empty($txtTgl)) {
			if(!empty($where)) $where .= " and ";
			$where.=" tanggal='".$txtTgl."'";
		}
		if($statPost!='') {
			if(!empty($where)) $where .= " and ";
			$where.=" statPost=".$statPost."";
		}
		if(!empty($where)) $where = " where ".$where;
                
                
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                    $wherelok="";
                }
                else
                {
                    $wherelok="and  pabrik='".$_SESSION['empl']['lokasitugas']."' "; 
                }
               
                
	$sql="select * from ".$dbname.".pabrik_rawatmesinht  ".$where."  ".$wherelok." ";
	//echo "warning".$sql;exit();
	$query=mysql_query($sql) or die(mysql_error());
	while($rlvhc=mysql_fetch_assoc($query))
		{
		$no+=1;
		echo"
		<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$rlvhc['notransaksi']."</td>
		<td>".tanggalnormal($rlvhc['tanggal'])."</td>
		<td>".$rlvhc['shift']."</td>
		<td>".$rlvhc['statasiun']."</td>
		<td>".$rlvhc['mesin']."</td>
		<td>".tanggalnormald($rlvhc['jammulai'])."</td>
		<td>".tanggalnormald($rlvhc['jamselesai'])."</td>";
			if($rlvhc['statPost']=='0')
			{
				if($rlvhc['updateby']!=$userOnline)
				{
				echo"<td><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"postThis('".$rlvhc['notransaksi']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event)\"></td>";
				 } else {
					 echo"
				<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event);\"></td>";
				}
			}
			else
			{
			 echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event);\"></td>";
			}
		}	


	break;
	default:
	break;
}
?>