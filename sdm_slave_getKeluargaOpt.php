<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	
$karyawanid=$_POST['karyawanid'];
$thnplafon=$_POST['thnplafon'];
$lokasitugas=$_POST['lokasitugas'];
$jenisbiaya=$_POST['jenisbiaya'];

$blmbayar=$totPengobatan=0;
$regional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');

if($karyawanid=='')
{
 echo"<option value=''></option";
}
else
{
	$str="select nomor,nama,ROUND(DATEDIFF(NOW(),tanggallahir)/365,2) as umur,jeniskelamin,hubungankeluarga,tanggungan
		  from ".$dbname.".sdm_karyawankeluarga where 
		  karyawanid=".$karyawanid." and tanggungan=1";
		
	$res=mysql_query($str);
	$no=0;
	$optKel="<option value=0>Ybs/PIC</option>";
	while($bar=mysql_fetch_object($res)) {
		if(($bar->umur>23 and $bar->hubungankeluarga!='Pasangan') or ($bar->hubungankeluarga=='Anak' and ($bar->umur>21 or $bar->tanggungan==0))){	
			$optKel.="<option value='".$bar->nomor."' style='background-color:red;'>".$bar->nama."(".$bar->umur."Th)-".$bar->jeniskelamin."</option>";
		} else {
			$optKel.="<option value='".$bar->nomor."'>".$bar->nama."(".$bar->umur."Th)-".$bar->jeniskelamin."</option>";
		}
	}
        
        $s_kary="select * from ".$dbname.".datakaryawan where karyawanid = ".$karyawanid."";
        $q_kary=mysql_query($s_kary) or die(mysql_error($conn));
        $r_kary=mysql_fetch_assoc($q_kary);
        $tipekary=$r_kary['tipekaryawan'];
		$kodeGolongan=$r_kary['kodegolongan'];
        
        if($tipekary!=0){
            $gaji="select * from ".$dbname.".sdm_5gajipokok where karyawanid = ".$karyawanid."
                   and tahun like ".$thnplafon." and idkomponen=1";
            $hasil=mysql_query($gaji) or die(mysql_error($conn));
            $row=mysql_fetch_assoc($hasil);
            $jumlahgaji=$row['jumlah'];
        }
        else{
			$sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$jenisbiaya."' and kodegolongan='".$kodeGolongan."' and regional = '".$regional[$lokasitugas]."'";
			$qPlaf=mysql_query($sPlaf);
			$rPlaf=mysql_fetch_assoc($qPlaf);
            $jumlahgaji=$rPlaf['rupiah'];
		}
		// exit("error: ".$jumlahgaji);       
       
#jumlah pengobatan non staf yang sudah dibayar
   if($thnplafon>2013){
       if($jenisbiaya=='RWJLN'){
           
           $scek="select sum(jlhbayar) as totDibyr from ".$dbname.".sdm_pengobatanht 
                  where karyawanid='".$karyawanid."' and tahunplafon='".$thnplafon."' and kodebiaya='RWJLN' and posting=1";
           $qcek=mysql_query($scek) or die(mysql_error($conn));
           $rcek=mysql_fetch_assoc($qcek);
           
           $totPengobatan=$rcek['totDibyr'];
           if($totPengobatan==0){
               $totPengobatan=0;
           }
       }
       if($jenisbiaya=='RWINP'){
           
           $scek="select sum(jlhbayar) as totDibyr from ".$dbname.".sdm_pengobatanht 
                  where karyawanid='".$karyawanid."' and tahunplafon='".$thnplafon."' and kodebiaya='RWINP' and posting=1";
           $qcek=mysql_query($scek) or die(mysql_error($conn));
           $rcek=mysql_fetch_assoc($qcek);
           
           $totPengobatan=$rcek['totDibyr'];
           if($totPengobatan==0){
               $totPengobatan=0;
           } 
       }
   }
    
#jumlah pengobatan non staf yang sudah blm dibayar
   if($thnplafon>2013){
       if($jenisbiaya=='RWJLN'){
           
           $scek="select sum(totalklaim) as klaim from ".$dbname.".sdm_pengobatanht 
                  where karyawanid='".$karyawanid."' and tahunplafon='".$thnplafon."' and kodebiaya='RWJLN' and posting=0";
           $qcek=mysql_query($scek) or die(mysql_error($conn));
           $rcek=mysql_fetch_assoc($qcek);
           
           $blmbayar=$rcek['klaim'];
           if($blmbayar==0){
               $blmbayar=0;
           }
       }
       if($jenisbiaya=='RWINP'){
           
           $scek="select sum(totalklaim) as klaim from ".$dbname.".sdm_pengobatanht 
                  where karyawanid='".$karyawanid."' and tahunplafon='".$thnplafon."' and kodebiaya='RWINP' and posting=0";
           $qcek=mysql_query($scek) or die(mysql_error($conn));
           $rcek=mysql_fetch_assoc($qcek);
           
           $blmbayar=$rcek['klaim'];
           if($blmbayar==0){
               $blmbayar=0;
           }
       }
   }

#Jumlah Plafon
$sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$jenisbiaya."' and kodegolongan='".$kodeGolongan."' and regional = '".$regional[$lokasitugas]."'";
$qPlaf=mysql_query($sPlaf);
$rPlaf=mysql_fetch_assoc($qPlaf);

if($rPlaf['satuan']==4){
	$vWhere = " and tahunplafon between '".(($thnplafon)-2)."' and '".$thnplafon."'";
}else{
	$vWhere = " and tahunplafon='".$thnplafon."'";
} 

$sPlaf2="select sum(jlhbayar) as jlhbayar, sum(bebanperusahaan) as bebanperusahaan, kodebiaya from ".$dbname.".sdm_pengobatanht
	      where karyawanid='".$karyawanid."' and kodebiaya='".$jenisbiaya."' ".$vWhere." 
	      group by kodebiaya";
$qPlaf2=mysql_query($sPlaf2);
$rPlaf2=mysql_fetch_assoc($qPlaf2);

if($jenisbiaya=='RWJLN'){
	$hasilPlaf=$jumlahgaji-($rPlaf2['bebanperusahaan']);
}else if($jenisbiaya=='RWINP'){
	$hasilPlaf=$rPlaf['rupiah'];
}else if($rPlaf['satuan']==4){
	if(mysql_num_rows($qPlaf2) >= 1){
		$hasilPlaf='0';
	}else{
		if(mysql_num_rows($qPlaf) <= 0){
			$hasilPlaf='0';
		}else{
			$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']);
		}
	}
}else if($rPlaf['satuan']==3){
	if(mysql_num_rows($qPlaf2) >= 1){
		$hasilPlaf='0';
	}else{
		if(mysql_num_rows($qPlaf) <= 0){
			$hasilPlaf='0';
		}else{
			$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']);
		}
	}
}else{
	if(mysql_num_rows($qPlaf) <= 0){
		$hasilPlaf='0';
	}else{
		if($rPlaf2['jlhbayar'] >= $rPlaf['rupiah']){
			$hasilPlaf='0';
		}else{
			$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']);
		}
	}
}
if($rPlaf['satuan']==1){
	$satuanPlafon='/ per tahun';
}else if($rPlaf['satuan']==2){
	$satuanPlafon='/ per hari';
}else if($rPlaf['satuan']==3){
	$satuanPlafon='/ 1 tahun sekali';
}else if($rPlaf['satuan']==4){
	$satuanPlafon='/ 3 tahun sekali';
}else{
	$satuanPlafon='';
}
       
  // exit("error: ".$scek);
     echo $optKel."###".$jumlahgaji."###".$tipekary."###".$totPengobatan."###".$blmbayar."###".number_format($hasilPlaf,2)."###".$satuanPlafon;  
}
?>