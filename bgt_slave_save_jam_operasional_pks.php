<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$oldtahunbudget=checkPostGet('oldtahunbudget','');
$oldkodeorg=checkPostGet('oldkodeorg','');
$tahunbudget=checkPostGet('tahunbudget','');
$kodeorg=checkPostGet('kodeorg','');
$jamo=checkPostGet('jamo','');
$jamo01=checkPostGet('jamo01','');
$jamo02=checkPostGet('jamo02','');
$jamo03=checkPostGet('jamo03','');
$jamo04=checkPostGet('jamo04','');
$jamo05=checkPostGet('jamo05','');
$jamo06=checkPostGet('jamo06','');
$jamo07=checkPostGet('jamo07','');
$jamo08=checkPostGet('jamo08','');
$jamo09=checkPostGet('jamo09','');
$jamo10=checkPostGet('jamo10','');
$jamo11=checkPostGet('jamo11','');
$jamo12=checkPostGet('jamo12','');
$jamb=checkPostGet('jamb','');
$jamb01=checkPostGet('jamb01','');
$jamb02=checkPostGet('jamb02','');
$jamb03=checkPostGet('jamb03','');
$jamb04=checkPostGet('jamb04','');
$jamb05=checkPostGet('jamb05','');
$jamb06=checkPostGet('jamb06','');
$jamb07=checkPostGet('jamb07','');
$jamb08=checkPostGet('jamb08','');
$jamb09=checkPostGet('jamb09','');
$jamb10=checkPostGet('jamb10','');
$jamb11=checkPostGet('jamb11','');
$jamb12=checkPostGet('jamb12','');
//$arrEnum=getEnum($dbname,'bgt_jam_operasioal_pks','jamolah,breakdown');
$method=checkPostGet('method','');

		


switch($method)
{
/*case 'update':	
	$str="update ".$dbname.".bgt_jam_operasioal_pks set jamolah='".$jamo."',breakdown='".$jamb."'
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'";
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));}
	break;
case 'insert':
    $str="select * from ".$dbname.".bgt_jam_operasioal_pks 
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'
            limit 0,1";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $sudahada="1";
		$pesan=$bar->tahunbudget."-".$bar->millcode."-".$bar->jamolah."-".$bar->breakdown;
    }
    if($sudahada=="1"){
        echo " Gagal, data sudah ada: ".$pesan; exit;
    }

    $str="insert into ".$dbname.".bgt_jam_operasioal_pks (`tahunbudget`,`millcode`,`jamolah`,`breakdown`)
		values ('".$thnbudget."','".$kdpks."','".$jamo."','".$jamb."')";
		
	//	exit ("Error:$str");
		
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));}	
	break;
case 'delete':
	$str="delete from ".$dbname.".bgt_jam_operasioal_pks 
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'";
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));}
	break;
default:
   break;					
*/

	case 'insert':
		$oldtahunbudget==''?$oldtahunbudget=$_POST['tahunbudget']:$oldtahunbudget=$_POST['oldtahunbudget'];
		$oldkodeorg==''?$oldkodeorg=$_POST['kodeorg']:$oldkodeorg=$_POST['oldkodeorg'];
		
		if(strlen($tahunbudget)<4){
			exit("Error:tahun budget belum sesuai");
		}	
		$sRicek="select * from ".$dbname.".bgt_jam_operasioal_pks where tahunbudget='".$oldtahunbudget."' and millcode='".$oldkodeorg."' ";
		//exit("Error:$sRicek");
		
		$qRicek=mysql_query($sRicek) or die(mysql_error($conn));
		$rRicek=mysql_num_rows($qRicek);
		
		if($rRicek>0){
			$sDel="delete from ".$dbname.".bgt_jam_operasioal_pks where tahunbudget='".$oldtahunbudget."' and millcode='".$oldkodeorg."'  ";	    
			if(mysql_query($sDel)){
				echo"";
			}else{
				echo " Gagal,".addslashes(mysql_error($conn));
			}	
		}
		$sDel2="insert into ".$dbname.".bgt_jam_operasioal_pks values ('".$tahunbudget."','".$kodeorg."','".$jamo."','".$jamb."'
		,'".$jamo01."','".$jamo02."','".$jamo03."','".$jamo04."','".$jamo05."','".$jamo06."'
		,'".$jamo07."','".$jamo08."','".$jamo09."','".$jamo10."','".$jamo11."','".$jamo12."'
		,'".$jamb01."','".$jamb02."','".$jamb03."','".$jamb04."','".$jamb05."','".$jamb06."'
		,'".$jamb07."','".$jamb08."','".$jamb09."','".$jamb10."','".$jamb11."','".$jamb12."')";
		if(mysql_query($sDel2))
			echo"";
		else
			echo " Gagal,".addslashes(mysql_error($conn));
	break;



	case'loadData':
		$str1="select * from ".$dbname.".bgt_jam_operasioal_pks where millcode='".
			$_SESSION['empl']['lokasitugas']."' order by tahunbudget desc";
		$no=0;
		$res1=mysql_query($str1);
			
		while($bar1=mysql_fetch_object($res1))
		{
			$no+=1;
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=right>".$bar1->tahunbudget."</td>
			<td align=left>".$bar1->millcode."</td>
			<td align=right>".$bar1->jamolah."</td>
			<td align=right>".$bar1->breakdown."</td>	
			<td align=right>".$bar1->jamolah01."</td>
			<td align=right>".$bar1->jamolah02."</td>
			<td align=right>".$bar1->jamolah03."</td>
			<td align=right>".$bar1->jamolah04."</td>
			<td align=right>".$bar1->jamolah05."</td>
			<td align=right>".$bar1->jamolah06."</td>
			<td align=right>".$bar1->jamolah07."</td>
			<td align=right>".$bar1->jamolah08."</td>
			<td align=right>".$bar1->jamolah09."</td>
			<td align=right>".$bar1->jamolah10."</td>
			<td align=right>".$bar1->jamolah11."</td>
			<td align=right>".$bar1->jamolah12."</td>
			<td align=right>".$bar1->breakdown01."</td>	
			<td align=right>".$bar1->breakdown02."</td>	
			<td align=right>".$bar1->breakdown03."</td>	
			<td align=right>".$bar1->breakdown04."</td>	
			<td align=right>".$bar1->breakdown05."</td>	
			<td align=right>".$bar1->breakdown06."</td>	
			<td align=right>".$bar1->breakdown07."</td>	
			<td align=right>".$bar1->breakdown08."</td>	
			<td align=right>".$bar1->breakdown09."</td>	
			<td align=right>".$bar1->breakdown10."</td>	
			<td align=right>".$bar1->breakdown11."</td>	
			<td align=right>".$bar1->breakdown12."</td>	
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->tahunbudget."','".$bar1->millcode."'
			,'".$bar1->jamolah."'
			,'".$bar1->jamolah01."'
			,'".$bar1->jamolah02."'
			,'".$bar1->jamolah03."'
			,'".$bar1->jamolah04."'
			,'".$bar1->jamolah05."'
			,'".$bar1->jamolah06."'
			,'".$bar1->jamolah07."'
			,'".$bar1->jamolah08."'
			,'".$bar1->jamolah09."'
			,'".$bar1->jamolah10."'
			,'".$bar1->jamolah11."'
			,'".$bar1->jamolah12."'
			,'".$bar1->breakdown."'
			,'".$bar1->breakdown01."'
			,'".$bar1->breakdown02."'
			,'".$bar1->breakdown03."'
			,'".$bar1->breakdown04."'
			,'".$bar1->breakdown05."'
			,'".$bar1->breakdown06."'
			,'".$bar1->breakdown07."'
			,'".$bar1->breakdown08."'
			,'".$bar1->breakdown09."'
			,'".$bar1->breakdown10."'
			,'".$bar1->breakdown11."'
			,'".$bar1->breakdown12."'
			);\"></td></tr>";
		}
	break;
}


?>





