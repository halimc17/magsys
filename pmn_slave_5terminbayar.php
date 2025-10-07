<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$method=$_POST['method'];
$kode=$_POST['kode'];
$satu=$_POST['satu'];
$dua=$_POST['dua'];	
?>

<?php

switch($method)
{
    case 'insert':
        $ha="insert into ".$dbname.".pmn_5terminbayar (`kode`,`satu`,`dua`,`updateby`)
        values ('".$kode."','".$satu."','".$dua."','".$_SESSION['standard']['userid']."')";
        if(mysql_query($ha))
        {
        }
        else
        {
            echo " Gagal,".addslashes(mysql_error($conn));
        }	
    break;

    case 'update':
        $ha="update ".$dbname.".pmn_5terminbayar set satu='".$satu."',dua='".$dua."' where kode='".$kode."'";
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
                                <td align=center>".$_SESSION['lang']['kode']."</td>
                                <td align=center>Termin 1</td>    
                                <td align=center>Termin 2</td>   
                                <td align=center>*</td>
                             </tr>
                    </thead>
                    <tbody>";
        $iList="select * from ".$dbname.".pmn_5terminbayar ";
        $nList=mysql_query($iList) or die(mysql_error($conn));
        while($dList=mysql_fetch_assoc($nList))
        {
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>".$no."</td>";
            echo "<td align=left>".$dList['kode']."</td>";
            echo "<td align=left>".$dList['satu']."</td>";
            echo "<td align=left>".$dList['dua']."</td>";
            echo "<td align=center>
                    <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                    onclick=\"fillField('".$dList['kode']."','".$dList['satu']."','".$dList['dua']."');\">

                    <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                    onclick=\"del('".$dList['kode']."');\">

                    </td>";
            echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
        }
    break;        
    
    case 'delete':
        $tab="delete from ".$dbname.".pmn_5terminbayar where kode='".$kode."' ";
        //exit("Error:$tab");
        if(mysql_query($tab))
        {
        }
        else
        {
                echo " Gagal,".addslashes(mysql_error($conn));
        }			
    break;
	
	
	
	
default:
}
?>