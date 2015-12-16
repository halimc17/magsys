<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$regional=checkPostGet('regional','');
$status=checkPostGet('status','');
$periode=checkPostGet('periode','');
$hargasatuan=checkPostGet('hargasatuan','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		// print_r($_SESSION['standard']);
		if($periode==''||$hargasatuan=='' || $hargasatuan=='0')
		{
			echo"warning : Semua field harus diisi.";
			exit();
		}
		// echo "warning : ".substr($periode,4,1);
		if(substr($periode,4,1) != '-' || !is_numeric(substr($periode,0,4)) || !is_numeric(substr($periode,5,2)) || substr($periode,5,2) > 12 || strlen(substr($periode,5,2)) != 2){
			echo"warning : Periksa kembali format periode.";
			exit();
		}
		$str="select * from ".$dbname.".kebun_5hargabibit where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
		$qry=mysql_query($str) or die(mysql_error());
		$numrows=mysql_num_rows($qry);
		
		if($numrows>0){
			echo "warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.";
			exit();
		}else{
			$strIns="insert into ".$dbname.".kebun_5hargabibit (regional,status,periode,hargasatuan,updateby) values ('".$regional."','".$status."','".$periode."','".$hargasatuan."','".$_SESSION['standard']['userid']."')";
			if(mysql_query($strIns)){
				loadlist();
			}else{
				echo"Gagal:Db Error".$strIns."__".mysql_error();
			}
		}
	break;
	
	case'edit':
		$strEdt="update ".$dbname.".kebun_5hargabibit set hargasatuan='".$hargasatuan."' where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
		if(mysql_query($strEdt)){
			loadlist();
		}else{
			echo"Gagal:Db Error".$strEdt."__".mysql_error();
		}		
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5hargabibit where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
		if(mysql_query($str)){
			loadlist();
		}else{
			echo"Gagal:Db Error".$str."__".mysql_error();
		}
	break;
	
	default:
	break;
}

function loadlist(){
	global $conn;
	global $dbname;
	global $pages;
	
	echo"<div id=container>
		<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
			   <td>".$_SESSION['lang']['regional']."</td>
			   <td>".$_SESSION['lang']['status']."</td>
			   <td>".$_SESSION['lang']['periode']."</td>
			   <td>".$_SESSION['lang']['hargasatuan']."</td>
			   <td colspan='2' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
	
	$limit=12;
	$page=0;
	if(isset($pages)){
		$page=$pages;
		if($page<0){
			$page=0;
		}
	}
	// print_r($pages);
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5hargabibit";
	$query2=mysql_query($ql2) or die(mysql_error());
	while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$strList="select * from ".$dbname.".kebun_5hargabibit order by periode desc limit ".$offset.",".$limit."";
	$qrtList=mysql_query($strList) or die(mysql_error($conn));
	$no=$maxdisplay;
	while($rowList=mysql_fetch_object($qrtList)){
		$no+=1;
		if($rowList->status == 'I'){
			$hStatus = "Inti";
		}else if($rowList->status == 'P'){
			$hStatus = "Plasma";
		}else{
			$hStatus = "Eksternal";
		}
		echo"<tr class='rowcontent'>
				<td>".$rowList->regional."</td>
				<td>".$hStatus."</td>
				<td>".$rowList->periode."</td>
				<td style='text-align:right;'>".number_format($rowList->hargasatuan,2)."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList->regional."','".$rowList->status."','".$rowList->periode."','".$rowList->hargasatuan."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList->regional."','".$rowList->status."','".$rowList->periode."')\"></td>
			</tr>";
	}
	echo"<tr class=rowheader><td colspan=6 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table></div>";
}
?>