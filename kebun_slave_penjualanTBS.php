<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');
$sup=checkPostGet('sup','');
$kg=checkPostGet('kg','');
$method=checkPostGet('method','');

$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

?>

<?php
switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".kebun_penjualantbs (kodeorg,kodesupplier,periode,totalkg,updateby)
            values ('".$unit."','".$sup."','".$per."','".$kg."','".$_SESSION['standard']['userid']."')";
           // exit("Error.$i");
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;

    case 'update':
  
            $i="update ".$dbname.".kebun_penjualantbs set totalkg='".$kg."'
             where kodeorg='".$unit."' and kodesupplier='".$sup."' and periode='".$per."'";
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;
	
		
    case'loadData':
	echo"
	<div id=container>
		<table class=sortable cellspacing=1 border=0>
	     <thead>
			 <tr class=rowheader>
			 	 <td align=center>".$_SESSION['lang']['nourut']."</td>
                                     <td align=center>".$_SESSION['lang']['unit']."</td>
				 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
				 <td align=center>".$_SESSION['lang']['periode']."</td>
				 <td align=center>".$_SESSION['lang']['totalkg']."</td>
				 <td align=center>".$_SESSION['lang']['action']."</td>
			 </tr>
		</thead>
		<tbody>";
		
		
		
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_penjualantbs ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$i="select * from ".$dbname.".kebun_penjualantbs order by periode desc  limit ".$offset.",".$limit."";
		
		$n=mysql_query($i) or die(mysql_error());
		$no=$maxdisplay;
		while($d=mysql_fetch_assoc($n))
		{
	
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$d['kodeorg']."</td>";
                    echo "<td align=left>".$nmSup[$d['kodesupplier']]."</td>";
                    echo "<td align=left>".$d['periode']."</td>";
                    echo "<td align=right>".number_format($d['totalkg'],2)."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$d['kodeorg']."','".$d['kodesupplier']."',"
                            . "'".$d['periode']."','".$d['totalkg']."');\">
                              <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kodeorg']."','".$d['kodesupplier']."',"
                            . "'".$d['periode']."');\">   

                                
                            </td>";
                    echo "</tr>";
                    
                }
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".kebun_penjualantbs where kodeorg='".$unit."' and kodesupplier='".$sup."' and periode='".$per."'";
		//exit("Error.$i");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
