<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$method=$_POST['method'];
$pt=$_POST['pt'];
$noakun=$_POST['noakun'];
$bank=$_POST['bank'];	
$rek=$_POST['rek'];
?>

<?php

switch($method)
{
    case 'insert':
        $ha="insert into ".$dbname.".keu_5akunbank (`pemilik`,`noakun`,`namabank`,`rekening`,`updateby`)
        values ('".$pt."','".$noakun."','".$bank."','".$rek."','".$_SESSION['standard']['userid']."')";
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
                                <td align=center>".$_SESSION['lang']['pt']."</td>
                                <td align=center>".$_SESSION['lang']['noakun']."</td>
                                <td align=center>".$_SESSION['lang']['namabank']."</td>
                                <td align=center>".$_SESSION['lang']['norekeningbank']."</td>    
                                <td align=center>*</td>
                             </tr>
                    </thead>
                    <tbody>";
        $iList="select * from ".$dbname.".keu_5akunbank";
        $nList=mysql_query($iList) or die(mysql_error($conn));
        while($dList=mysql_fetch_assoc($nList))
        {
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>".$no."</td>";
            echo "<td align=left>".$dList['pemilik']."</td>";
            echo "<td align=left>".$dList['noakun']."</td>";
            echo "<td align=left>".$dList['namabank']."</td>";
            echo "<td align=left>".$dList['rekening']."</td>";
            echo "<td align=center>
                    <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                    onclick=\"del('".$dList['pemilik']."','".$dList['noakun']."','".$dList['namabank']."','".$dList['rekening']."');\">
                    </td>";
            echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
        }
    break;        
    
    case 'delete':
        $tab="delete from ".$dbname.".keu_5akunbank where pemilik='".$pt."' and noakun='".$noakun."' and namabank='".$bank."'"
            . " and rekening='".$rek."' ";
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