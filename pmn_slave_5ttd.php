<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$nama=$_POST['nama'];   
$jabatan=$_POST['jabatan'];
$method=$_POST['method'];
?>

<?php
switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".pmn_5ttd (nama,jabatan,updateby)
            values ('".$nama."','".$jabatan."','".$_SESSION['standard']['userid']."')";
        //exit("Error:$i");
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
                                 <td align=center>".$_SESSION['lang']['nama']."</td>
                                 <td align=center>".$_SESSION['lang']['jabatan']."</td>
                                     <td align=center>*</td>
			 </tr>
		</thead>
		<tbody>";
		
		
		$i="select * from ".$dbname.".pmn_5ttd order by nama asc"; 
		$n=mysql_query($i) or die(mysql_error($conn));
		while($d=mysql_fetch_assoc($n))
		{
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$d['nama']."</td>";
                    echo "<td align=left>".$d['jabatan']."</td>";
                    echo "<td align=center>
                          <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['nama']."');\">
                            </td>";
                    echo "</tr>";//
		}
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".pmn_5ttd where nama='".$nama."'";
		//exit("Error.$str");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
