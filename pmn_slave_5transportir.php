<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$tran=$_POST['tran'];
$driv=$_POST['driv'];
$nopol=$_POST['nopol'];
$method=$_POST['method'];
?>

<?php
switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".pmn_5transportir (transportir,nopol,supir,updateby)
            values ('".$tran."','".$nopol."','".$driv."','".$_SESSION['standard']['userid']."')";
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
                           <td align=center>".$_SESSION['lang']['transport']."</td>
                           <td align=center>".$_SESSION['lang']['nopol']."</td>
                           <td align=center>".$_SESSION['lang']['supir']."</td>
                           <td align=center>".$_SESSION['lang']['updateby']."</td>
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_5transportir";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$i="select * from ".$dbname.".pmn_5transportir  limit ".$offset.",".$limit."";
		
		$n=mysql_query($i) or die(mysql_error());
		$no=$maxdisplay;
		while($d=mysql_fetch_assoc($n))
		{
                    $whKar="karyawanid='".$d['updateby']."'";
                    $whSup="supplierid='".$d['transportir']."'";
                    
                    $nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
                     $nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whSup);
                  
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$nmSup[$d['transportir']]."</td>";
                    echo "<td align=left>".$d['nopol']."</td>";
                    echo "<td align=left>".$d['supir']."</td>";
                    echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
                    //echo "<td align=left>".$d['updatetime']."</td>";
                    echo "<td align=center>
                        
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['transportir']."','".$d['nopol']."');\">
                        
                            
                            </td>";//<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$d['kode']."','".$d['jenis']."',"
                           // . "'".$d['keterangan']."');\">
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
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
	//exit("Error:hahaha");nopol
		$i="delete from ".$dbname.".pmn_5transportir where transportir='".$tran."' and nopol='".$nopol."'";
		
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
