<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$method=$_POST['method'];
$kdorg=$_POST['kdorg'];
$harga=$_POST['harga'];
$thntnm=$_POST['thntnm'];
$tgl=tanggalsystem($_POST['tgl']);
$kodesupplier=$_POST['kodesupplier'];

$periodesort=$_POST['periodesort'];
$suppsort=$_POST['suppsort'];
$kdorgsort=$_POST['kdorgsort'];
//exit("Error:$sInsert");	
$namasupp=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');
$namaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
?>

<?php

switch($method)
{
	case 'insert':
		$ha="insert into ".$dbname.".pabrik_5hargatbs (`kodeorg`,`kodesupplier`,`tanggal`,`tahuntanam`,`harga`,`updateby`)
		values ('".$kdorg."','".$kodesupplier."','".$tgl."','".$thntnm."','".$harga."','".$_SESSION['standard']['userid']."')";
		if(mysql_query($ha))
		{
		}
		else
		{
			echo " Gagal,".addslashes(mysql_error($conn));
		}	
	break;
        
        case 'update':
		$ha="update ".$dbname.".pabrik_5hargatbs set harga='".$harga."' where kodeorg='".$kdorg."' and kodesupplier='".$kodesupplier."' "
                . " and tanggal='".$tgl."' and tahuntanam='".$thntnm."'";
		if(mysql_query($ha))
		{
		}
		else
		{
			echo " Gagal,".addslashes(mysql_error($conn));
		}	
	break;
        

case'loadData':
		echo"<div id=container>
                        <table class=sortable cellspacing=1 border=0>
                         <thead>
                                     <tr class=rowheader>
                                        <td align=center>No</td>
                                        <td align=center>".$_SESSION['lang']['pabrik']."</td>
                                        <td align=center>".$_SESSION['lang']['kodesupplier']."</td>    
                                        <td align=center>".$_SESSION['lang']['namasupplier']."</td>
                                        <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                        <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
                                        <td align=center>".$_SESSION['lang']['harga']."</td>
                                        <td align=center>*</td></tr>
                                     </tr>
                            </thead>
                            <tbody>";
		
		$tmbh='';
                if($periodesort!='')
                {
                    $tmbh="and tanggal like '%".$periodesort."%' ";
                }
		$tmbh2='';
		
                if($suppsort!='')
                {
                    $tmbh2=" and kodesupplier='".$suppsort."' ";
                }
		$tmbh3='';
                if($kdorgsort!='')
                {
                    $tmbh3=" where kodeorg like '".$kdorgsort."' ";
                }
                
                $limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$iList="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh."  limit ".$offset.",".$limit."";
		//$str="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
		$nList=mysql_query($iList) or die(mysql_error());
		$no=$maxdisplay;
		while($dList=mysql_fetch_assoc($nList))
		{
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$dList['kodeorg']."</td>";
                    echo "<td align=left>".$dList['kodesupplier']."</td>";
                    echo "<td align=left>".$namasupp[$dList['kodesupplier']]."</td>";
                    echo "<td align=left>".tanggalnormal($dList['tanggal'])."</td>";
                    echo "<td align=right>".$dList['tahuntanam']."</td>";
                    echo "<td align=right>".number_format($dList['harga'])."</td>";
                    
                    
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$dList['kodeorg']."','".$dList['kodesupplier']."',"
                            . "'".tanggalnormal($dList['tanggal'])."','".$dList['tahuntanam']."','".$dList['harga']."');\">
                            
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kodeorg']."','".$dList['kodesupplier']."',"
                            . "'".tanggalnormal($dList['tanggal'])."','".$dList['tahuntanam']."');\">

                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
		}
                echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
                
		

		//$str="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
		

	case 'delete':
		
		$tab="delete from ".$dbname.".pabrik_5hargatbs where kodesupplier='".$kodesupplier."' and tanggal='".$tgl."' and kodeorg='".$kdorg."' and tahuntanam='".$thntnm."' ";
		//exit("Error:$tab");
		if(mysql_query($tab))
		{
		}
		else
		{
			echo " Gagal,".addslashes(mysql_error($conn));
		}			
	break;
	
	case 'getperiodesort':
	//exit("Error:MASUK");
		$optpersort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$aper = "SELECT distinct substr(tanggal,1,7) as tanggal FROM ".$dbname.".pabrik_5hargatbs where substr(tanggal,1,7) order by tanggal desc";
		//exit ("Error:$asup");
		$bper=mysql_query($aper) or die(mysql_error($conn));
		while($cper=mysql_fetch_assoc($bper))
		{
			$optpersort.="<option value='".$cper['tanggal']."'>".$cper['tanggal']."</option>";
		}
		echo $optpersort;
	break;
	
	case 'getsuppsort':
			//exit("Error:xx");
		$optsupsort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$asup = "SELECT distinct kodesupplier FROM ".$dbname.".pabrik_5hargatbs ";
		//exit ("Error:$asup");
		$bsup=mysql_query($asup) or die(mysql_error($conn));
		while($csup=mysql_fetch_assoc($bsup))
		{
			$optsupsort.="<option value='".$csup['kodesupplier']."'>".$namasupp[$csup['kodesupplier']]."</option>";
		}
		echo $optsupsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	case 'getorgsort':
			//exit("Error:xx");
		$optorgsort="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$aorg = "SELECT distinct kodeorg FROM ".$dbname.".pabrik_5hargatbs ";
		//exit ("Error:$aorg");
		$borg=mysql_query($aorg) or die(mysql_error($conn));
		while($corg=mysql_fetch_assoc($borg))
		{
			$optorgsort.="<option value='".$corg['kodeorg']."'>".$namaorg[$corg['kodeorg']]."</option>";
		}
		echo $optorgsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	
default:
}
?>