<?php
//ind
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=$_POST['proses'];
$jenissave=$_POST['jenissave'];
$per=$_POST['per'];
$karyawanidsave=$_POST['karyawanidsave'];
$jumlahsave=$_POST['jumlahsave'];
$kdorgsave=$_POST['kdorgsave'];


$unit=$_POST['unit'];
$jenis=$_POST['jenis'];

	

switch($proses)
{
    
    case'del':
        
        #delete dlo semua 
        $iDel="delete from ".$dbname.".sdm_gaji where kodeorg='".$unit."' "
            . " and periodegaji='".$per."' and idkomponen='".$jenis."' ";
        //exit("Error:$iDel");
        if(mysql_query($iDel))
        {
        }
        else
        {
                echo " Gagal,".addslashes(mysql_error($conn));
        }
    break;
    
    
    case'savedata':
        
        
        
        if($jumlahsave=='0' or $jumlahsave=='')
        {
        }
        else
        {
            $str="insert into ".$dbname.".sdm_gaji (`kodeorg`,`periodegaji`,`karyawanid`,`idkomponen`,`jumlah`,`pengali`)
            values ('".$kdorgsave."','".$per."','".$karyawanidsave."','".$jenissave."','".$jumlahsave."','1')";

            if(mysql_query($str))
            {
            }
            else
            {
                    echo " Gagal,".addslashes(mysql_error($conn));
            }
        }
    break; 
    default:
}

?>