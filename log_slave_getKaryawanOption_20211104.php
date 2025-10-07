<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
//=============================================

if(isTransactionPeriod())//check if transaction period is normal
{
    $today = date("Y-m-d"); 
//  echo " Error:".$_POST['induk'];
    $unit=$_POST['unit'];
    $subunit=$_POST['subunit'];
    $penerima=$_POST['penerima'];
    if($penerima=='')$penerima='0';
    
        if($unit==''){
            echo "<option value=''></option>";
            exit;
        }

//    $str="select karyawanid, namakaryawan, nik from ".$dbname.".datakaryawan 
//        where (tanggalkeluar > '".$today."' or tanggalkeluar = '0000-00-00') and lokasitugas like '".$unit."%' and subbagian = '".$subunit."'
//        order by namakaryawan";        
	if(substr($unit,2,2)!='HO' and substr($unit,2,2)!='RO'){
	    $str="select karyawanid, namakaryawan, subbagian from ".$dbname.".datakaryawan 
	        where (tanggalkeluar > '".$today."' or tanggalkeluar = '0000-00-00') and lokasitugas in 
				(select kodeorganisasi from ".$dbname.".organisasi 
						where induk in (select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($unit,0,4)."')
				and (kodeorganisasi like '".$unit."%' or tipe='KANWIL'))
		    order by namakaryawan";
	}else{
	    $str="select karyawanid, namakaryawan, subbagian from ".$dbname.".datakaryawan 
	        where (tanggalkeluar > '".$today."' or tanggalkeluar = '0000-00-00') and lokasitugas like '".$unit."%'
		    order by namakaryawan";
	}
    //exit('Warning: '.$str);
    if($penerima!='0'){
    $str="select karyawanid, namakaryawan, nik from ".$dbname.".datakaryawan 
        where karyawanid = '".$penerima."'
        order by namakaryawan";                
    }
    
//    echo "warning:".$str." ".$penerima;

    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $tampilan=$bar->namakaryawan." [".$bar->subbagian."]";
        $optsloc.="<option value='".$bar->karyawanid."'>".$tampilan."</option>";
    }    
    $optsloc.="<option value='masyarakat'>".$_SESSION['lang']['masyarakat']."</option>";
    
    echo $optsloc;
}
else
{
    echo " Error: Transaction Period missing";
}
?>