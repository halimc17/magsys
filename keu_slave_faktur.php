<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php
$id = checkPostGet('id','');	
$pt = checkPostGet('pt','');	
$faktur = checkPostGet('faktur','');	
$cariPt = checkPostGet('cariPt','');	
$cariStatus = checkPostGet('cariStatus','');	
$method = checkPostGet('method','');

$arrst=array("0"=>"Tidak Aktif","1"=>"Aktif");

?>

<?php
switch($method)
{
	

    case 'insert':
		$countFak="select * from ".$dbname.".keu_fakturpajak where pt='".$pt."' and nofaktur='".$faktur."'";
		if(mysql_num_rows(mysql_query($countFak)) >= 1){
			echo " Gagal, No Faktur Bayar sudah pernah terdaftar sebelumnya.";
		}else{
			$i="insert into ".$dbname.".keu_fakturpajak (pt,nofaktur,updateby)
				values ('".$pt."','".$faktur."','".$_SESSION['standard']['userid']."')";
			if(mysql_query($i))
				echo"";
			else
				echo " Gagal,".addslashes(mysql_error($conn));
		}
            
    break;

    case 'update':
       // exit("Error:MASUK");
            $i="update ".$dbname.".keu_fakturpajak set pt='".$pt."',"
            . " updateby='".$_SESSION['standard']['userid']."',nofaktur='".$faktur."'
             where id='".$id."'";
        //exit("Error:$i");
            //exit("Error.$i");
            if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
    break;
	
		
    case'loadData':
	echo"
            <div id=container>
		<table class=sortable cellspacing=1 cellpadding=3 border=0>
                    <thead>
			 <tr class=rowheader>
			 	 <td align=center>".$_SESSION['lang']['nourut']."</td>
                                 <td align=center>".$_SESSION['lang']['pt']."</td>
				 <td align=center>No. Faktur</td>
				 <td align=center>".$_SESSION['lang']['status']."</td>
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".keu_fakturpajak where pt like '%".$cariPt."%' and status like '%".$cariStatus."%'";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$i="select * from ".$dbname.".keu_fakturpajak where pt like '%".$cariPt."%' and status like '%".$cariStatus."%' limit ".$offset.",".$limit."";
		
		$n=mysql_query($i) or die(mysql_error());
		$no=$maxdisplay;
		while($d=mysql_fetch_assoc($n))
		{
                    
                    $nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
                    $namaPerusahaan=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$namaPerusahaan[$d['pt']]." - ".$d['pt']."</td>";
                    echo "<td align=left>".$d['nofaktur']."</td>";
                    echo "<td align=left>".$arrst[$d['status']]."</td>";
                    echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
                    //echo "<td align=left>".$d['updatetime']."</td>";
					if($d['status']==0) {
						echo "<td align=center>
								<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$d['id']."','".$d['pt']."',"
								. "'".$d['nofaktur']."');\">
									<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['id']."');\">
								</td>";
					} else {
						echo "<td></td>";
					}
                    echo "</tr>";//
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
		$i="delete from ".$dbname.".keu_fakturpajak where id='".$id."'";
		//exit("Error.$str");
		if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
