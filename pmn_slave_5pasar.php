<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

    

$pasar=$_POST['pasar'];
$method=$_POST['method'];
?>

<?php
switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".pmn_5pasar (pasar,updateby)
            values ('".$pasar."','".$_SESSION['standard']['userid']."')";
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
                                 <td align=center>".$_SESSION['lang']['pasar']."</td>
                                     <td align=center>*</td>
			 </tr>
		</thead>
		<tbody>";
		
		
		$i="select * from ".$dbname.".pmn_5pasar order by pasar asc"; 
		$n=mysql_query($i) or die(mysql_error($conn));
		while($d=mysql_fetch_assoc($n))
		{
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$d['pasar']."</td>";
                    echo "<td align=center>
                          <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['pasar']."');\">
                            </td>";
                    echo "</tr>";//
		}
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".pmn_5pasar where pasar='".$pasar."'";
		//exit("Error.$str");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
