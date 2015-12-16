<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$regional=$_POST['regional'];
$rupiah=$_POST['rupiah'];
$method=$_POST['method'];
?>

<?php
switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".sdm_5uangmakan (regional,rupiah,updateby)
            values ('".$regional."','".$rupiah."','".$_SESSION['standard']['userid']."')";
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;

    case 'update':
            $i="update ".$dbname.".sdm_5uangmakan set rupiah='".$rupiah."',"
            . " updateby='".$_SESSION['standard']['userid']."' where regional='".$regional."'";
            //exit("Error.$i");
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
                                 <td align=center>".$_SESSION['lang']['regional']."</td>
				 <td align=center>".$_SESSION['lang']['rupiah']."/Hari</td>
				 <td align=center>".$_SESSION['lang']['updateby']."</td>
                                 <td align=center>".$_SESSION['lang']['action']."</td>
			 </tr>
		</thead>
		<tbody>";
		
                    
		$i="select * from ".$dbname.".sdm_5uangmakan  ";
		
		$n=mysql_query($i) or die(mysql_error());
		$no=$maxdisplay;
		while($d=mysql_fetch_assoc($n))
		{   
                    
                    $nmReg=makeOption($dbname,'bgt_regional','regional,nama');
                    $whKar="karyawanid='".$d['updateby']."' ";
                    $nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$nmReg[$d['regional']]."</td>";
                    echo "<td align=right>".number_format($d['rupiah'])."</td>";
                    echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
                    //echo "<td align=left>".$d['updatetime']."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$d['regional']."','".$d['rupiah']."');\">
                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
		} 
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".kebun_5dendapengawas where kode='".$regional."'";
		//exit("Error.$str");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
