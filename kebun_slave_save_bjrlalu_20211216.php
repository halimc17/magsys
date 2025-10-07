<?php
//ind
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=$_POST['proses'];
$unit=$_POST['unit'];
$per=$_POST['per'];
$thn=substr($per,0,4);
$bjrset=$_POST['bjrset'];
$kodeblok=$_POST['kodeblok'];
switch($proses){
    case'simpan':
        $iSel="select * from ".$dbname.".kebun_5bjr where kodeorg like '".$unit."%' and tahunproduksi='".$thn."'";
        if(mysql_query($iSel)){
        }else{
			echo " Gagal,".addslashes(mysql_error($conn));
        }
    break;
    
    case'savedata':
        if($bjrset=='0' or $bjrset==''){
        }else{
			$iSel="select * from ".$dbname.".kebun_5bjr where kodeorg = '".$kodeblok."' and tahunproduksi='".$thn."'";
			$ress=mysql_query($iSel);
			$rows=mysql_num_rows($ress);
			//exit('Warning: '.$proses.' Unit='.$unit.' per='.$per.' thn='.$thn.' bjrset='.$bjrset.' blok='.$kodeblok.' row='.$rows);
			if($rows>0){
				$str="update ".$dbname.".kebun_5bjr set bjr='".$bjrset."' where kodeorg = '".$kodeblok."' and tahunproduksi='".$thn."'";
				//exit('Warning: '.$str);
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}else{
				$str="insert into ".$dbname.".kebun_5bjr (`kodeorg`,`bjr`,`tahunproduksi`)
				values ('".$kodeblok."','".$bjrset."','".$thn."')";
				if(mysql_query($str)){
				}else{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
			}
        }
    break; 
                
    default:
}

?>