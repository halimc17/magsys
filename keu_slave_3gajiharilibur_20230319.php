<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
#==========================================konfigurasi database
# M0	Perawatan Kebun
# M1	Biaya Panen

#============================================konfigurasi database

#==Komfigurasi komponen gaji
# 1	Gaji Pokok
# 2	Tunjangan Jabatan
# 14	Rapel
# 16	Premi Pengawasan
# 21	Klaim Pengobatan
# 26	Bonus
# 27	Tunjangan Fasilitas
# 28	THR
# 30	Tunjangan Profesi
# 31	Tunjangan Masa Kerja
# 32	Premi
# 33	Lembur
# 34	Penalti
#

$param = $_POST;
$tahunbulan = implode("",explode('-',$param['periode']));
#ambil periode akuntansi
$str="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
    where kodeorg='".$_SESSION['empl']['lokasitugas']."'
    and periode='".$param['periode']."'";
$tgmulai='';
$tgsampai='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tgsampai   = $bar->tanggalsampai;
    $tgmulai    = $bar->tanggalmulai;
}
if($tgmulai=='' || $tgsampai=='')
    exit("Error: Accounting period is not registered");

/**
 * Validasi apakah proses gaji telah ditutup
 */
$qGaji = selectQuery($dbname,'sdm_5periodegaji','sudahproses,jenisgaji',
					 "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
$resGaji = fetchData($qGaji);
$optGaji = array();
foreach($resGaji as $row) {
	$optGaji[$row['jenisgaji']] = $row['sudahproses'];
}

// 1. Validasi Empty
if(empty($optGaji)) exit('Warning: Periode Gaji '.$param['periode']." belum ada");
if(!isset($optGaji['H'])) exit('Warning: Periode Gaji Harian '.$param['periode']." belum ada");
if(!isset($optGaji['B'])) exit('Warning: Periode Gaji Bulanan '.$param['periode']." belum ada");

// 2. Validasi Proses Gaji
if($optGaji['H']==0) exit('Warning: Proses Gaji Harian '.$param['periode']." belum dilakukan");
if($optGaji['B']==0) exit('Warning: Proses Gaji Bulanan '.$param['periode']." belum dilakukan");

#---------------------------------------------------------------
#ambil potongan HK
#---------------------------------------------------------------
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
       and idkomponen in(37,41) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $resx=  mysql_query($str);
 $potx=Array();
 while($barx=mysql_fetch_object($resx))
 {
     $potx[$barx->karyawanid]=$barx->jumlah;
 }
#---------------------------------------------------------------
#ambil kontanan sudah dibayar
#---------------------------------------------------------------
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
       and idkomponen in(43) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $resx=  mysql_query($str);
 $potkon=Array();
 while($barx=mysql_fetch_object($resx))
 {
     $potkon[$barx->karyawanid]=$barx->jumlah;
 }
#---------------------------------------------------------------
#ambil semua gaji per karyawan
#---------------------------------------------------------------
#1. Ambil gaji total per karyawan yang plus pada unit bersangkutan
 $str="select sum(jumlah) as jumlah,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
       and plus=1 and periodegaji='".$param['periode']."' group by karyawanid";
 $res=  mysql_query($str);
 $gaji=Array();
 while($bar=mysql_fetch_object($res))
 {
	if(!isset($potx[$bar->karyawanid])) $potx[$bar->karyawanid]=0;
	if(!isset($potkon[$bar->karyawanid])) $potkon[$bar->karyawanid]=0;
    $gaji[$bar->karyawanid]=$bar->jumlah-$potx[$bar->karyawanid]-$potkon[$bar->karyawanid];//kurangi potongan hk
 }
 #2 Ambil subunit setiap karyawan
 $str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan 
       where lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
 $res=mysql_query($str);
 $subunit=Array();
 while($bar=mysql_fetch_object($res))
 {
     $subunit[$bar->karyawanid]=$bar->subbagian;
     $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
     
 }
 #3 ambil semua organisasi yang traksi atau workshop
 $str="select distinct kodeorganisasi,tipe from ".$dbname.".organisasi 
       where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
 $res=mysql_query($str);
 $tipe=Array();
 while($bar=mysql_fetch_object($res))
 {
     $tipe[$bar->kodeorganisasi]=$bar->tipe;
     
 } 

  #==========================================================================================  
  #ambil daftar karyawan yang masuk dalam perawatan dan panen
  $str="select karyawanid,(sum(umr)+sum(insentif)) as upah from ".$dbname.".kebun_kehadiran_vw
        where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal between '".$tgmulai."' 
        and '".$tgsampai."' group by karyawanid";
 
  $res=mysql_query($str);
  $gjPerawatan=Array();
  while($bar=mysql_fetch_object($res))
  {
      $gjPerawatan[$bar->karyawanid]=$bar->upah;
  }
  #===================panen
//  $str="select karyawanid,(sum(upahkerja)+sum(upahpremi)-sum(rupiahpenalty)) as upah from ".$dbname.".kebun_prestasi_vw
//        where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal between '".$tgmulai."' 
//        and '".$tgsampai."' group by karyawanid";
  $str="select tanggal,karyawanid,(sum(upahkerja)+sum(upahpremi)+sum(premibasis)-sum(upahpenalty)-sum(rupiahpenalty)) as upah from ".$dbname.".kebun_prestasi_vw
        where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal between '".$tgmulai."' 
        and '".$tgsampai."' group by tanggal,karyawanid";
 
  $res=mysql_query($str);
  $gjPanen=Array();
  while($bar=mysql_fetch_object($res))
  {
		// cari hari
		$day = date('D', strtotime($bar->tanggal));
		if($day=='Sun')$libur=true; else $libur=false;
		// kamus hari libur
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$bar->tanggal."'";
		$queorg=mysql_query($strorg) or die(mysql_error());
		while($roworg=mysql_fetch_assoc($queorg))
		{
//                $libur=true;
		if($roworg['keterangan']=='libur')$libur=true;
		if($roworg['keterangan']=='masuk')$libur=false;
		}        
            
            
        if($libur==false) {
			if(!isset($gjPanen[$bar->karyawanid])) $gjPanen[$bar->karyawanid]=0;
			$gjPanen[$bar->karyawanid]+=$bar->upah;
		}else{// kalo hari libur dianggap kontanan? (masuk ke pengurang)
			
		}
  }
  #=================================================================
  #hapus karyawan tidaklangsung
  $masukkotak=Array();
  $gaji1=$gaji;
  foreach($gaji as $karid=>$g){
	if(!isset($gjPanen[$karid])) $gjPanen[$karid]=0;
	if(!isset($gjPerawatan[$karid])) $gjPerawatan[$karid]=0;
	$gajiyangsudahdialokasi[$karid]=$gjPanen[$karid]+$gjPerawatan[$karid];
	if($gajiyangsudahdialokasi[$karid]!=0)
	{
		$masukkotak[$karid]=$g-$gajiyangsudahdialokasi[$karid];
	}
  }
  $zzz=$masukkotak;
  #bersihkan memory
  //unset($gaji);
  #=======================================================================================================  
    

 if(empty($masukkotak))
     exit('Error: Salaries has been allocated correctly');
 else {

     
       
             echo"Un Allocated Salaries:<br>
                  <button class=mybutton onclick=prosesGajiLangsung(1) id=btnproses>Process/Allocate</button>
                  <table class=sortable cellspacing=1 border=0>
                  <thead>
                    <tr class=rowheader>
                    <td>No</td>
                    <td>".$_SESSION['lang']['dari']."</td>
                    <td>".$_SESSION['lang']['sampai']."</td>
                    <td>".$_SESSION['lang']['namakaryawan']."</td>
                    <td>".$_SESSION['lang']['karyawanid']."</td>
                    <td>".$_SESSION['lang']['subbagian']."</td>
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td>".$_SESSION['lang']['blmAlokasi']."</td>
                    <td>".$_SESSION['lang']['gaji']."</td>
                    <td>Allocated</td>
                    </tr>
                  </thead>
                  <tbody>";
             $no=$ttl=0;
            foreach($masukkotak as $key =>$baris)
             { 
                $no+=1;
                 echo"<tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$tgmulai."</td>
                    <td>".$tgsampai."</td>    
                    <td>".$namakaryawan[$key]."</td>
                    <td>".$key."</td>    
                    <td>".$subunit[$key]."</td>
                    <td>".$tipe[$subunit[$key]]."</td>                        
                    <td align=right>".number_format($baris)."</td>
                    <td align=right>".number_format($gaji1[$key])."</td>
                    <td align=right>".number_format($gajiyangsudahdialokasi[$key])."</td>       
                    </tr>";
                 $ttl+=$baris;
             }
            echo"<tr class=rowcontent id='row".$no."'>
                    <td colspan=7>Total</td>
                    <td align=right>".number_format($ttl)."</td>
                    <td></td>
                    <td></td>
                    </tr>";
             echo"</tbody><tfoot></tfoot></table>";
                  $s=0;
                  foreach($zzz as $karid=>$val)
                  {
                      if($s==0)
                         $arrkarid="#".$karid."#";
                      else
                         $arrkarid.=",#".$karid."#"; 
                      $s++;
                  }
             echo "<input type=hidden id=karyawanid value=\"".$arrkarid."\">";
             echo "<input type=hidden id=jumlah value='".$ttl."'>";
             echo "<input type=hidden id=dari value='".$tgmulai."'>";
             echo "<input type=hidden id=sampai value='".$tgsampai."'>";
             

}
#----------------------------------------------------------------
?>