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
/*
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
       and idkomponen in(37,41) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $resx=  mysql_query($str);
 $potx=Array();
 while($barx=mysql_fetch_object($resx))
 {
	$potx[$barx->karyawanid]=$barx->jumlah;
 }
*/

#---------------------------------------------------------------
#ambil kontanan sudah dibayar
#---------------------------------------------------------------
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
       and idkomponen in(34) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
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
 $penalty=Array();
 while($bar=mysql_fetch_object($res))
 {
	if(!isset($potx[$bar->karyawanid])) $potx[$bar->karyawanid]=0;
	if(!isset($potkon[$bar->karyawanid])) $potkon[$bar->karyawanid]=0;
    $gaji[$bar->karyawanid]=$bar->jumlah-$potx[$bar->karyawanid]-$potkon[$bar->karyawanid];//kurangi potongan hk
    $gajibrt[$bar->karyawanid]=$bar->jumlah;//kurangi potongan hk
    $penalty[$bar->karyawanid]=$potx[$bar->karyawanid]+$potkon[$bar->karyawanid];
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
  $str="select karyawanid,sum(umr+insentif-denda) as upah from ".$dbname.".kebun_kehadiran_vw
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
/*
  $str="select tanggal,karyawanid,(sum(upahkerja)+sum(upahpremi)+sum(premibasis)-sum(upahpenalty)-sum(rupiahpenalty)) as upah from ".$dbname.".kebun_prestasi_vw
        where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal between '".$tgmulai."' and '".$tgsampai."' 
		and tanggal not in (select tanggal from ".$dbname.".sdm_5harilibur where tanggal between '".$tgmulai."' and '".$tgsampai."' 
		and keterangan='libur'
		and kebun in ('GLOBAL','".$_SESSION['empl']['lokasitugas']."'))
		group by tanggal,karyawanid";
*/
  $str="select karyawanid,(sum(upahkerja)+sum(upahpremi)+sum(premibasis)-sum(upahpenalty)-sum(rupiahpenalty)) as upah from ".$dbname.".kebun_prestasi_vw
        where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal between '".$tgmulai."' and '".$tgsampai."' 
		and tanggal not in (select tanggal from ".$dbname.".sdm_5harilibur where tanggal between '".$tgmulai."' and '".$tgsampai."' 
		and keterangan='libur'
		and kebun in ('GLOBAL','".$_SESSION['empl']['lokasitugas']."'))
		group by karyawanid";
 
  $res=mysql_query($str);
  $gjPanen=Array();
  while($bar=mysql_fetch_object($res))
  {
		/*
		// cari hari
		$day = date('D', strtotime($bar->tanggal));
		if($day=='Sun')$libur=true; else $libur=false;
		// kamus hari libur
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$bar->tanggal."' and kebun in ('GLOBAL','".$_SESSION['empl']['lokasitugas']."')";
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
		*/
		$gjPanen[$bar->karyawanid]+=$bar->upah;
  }
  #=================================================================
  #hapus karyawan tidaklangsung
  $masukkotak=Array();
  $gaji1=$gaji;
  foreach($gaji1 as $karid=>$g){
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
                    <td rowspan=2 align=center>No</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['dari']."</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['sampai']."</td>
                    <td rowspan=2 rowspan=2 align=center>".$_SESSION['lang']['namakaryawan']."</td>
                    <td rowspan=2 rowspan=2 align=center>".$_SESSION['lang']['karyawanid']."</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['subbagian']."</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['tipe']."</td>
                    <td colspan=3 align=center>Allocated</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['gaji']."</td>
                    <td rowspan=2 align=center>Penalty</td>
                    <td rowspan=2 align=center>".$_SESSION['lang']['blmAlokasi']."</td>
                    </tr>
                    <tr class=rowheader>
	                    <td align=center>".$_SESSION['lang']['perawatan']."</td>
	                    <td align=center>".$_SESSION['lang']['panen']."</td>
	                    <td align=center>".$_SESSION['lang']['total']."</td>
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
                    <td align=right>".number_format($gjPerawatan[$key])."</td>       
                    <td align=right>".number_format($gjPanen[$key])."</td>       
                    <td align=right>".number_format($gajiyangsudahdialokasi[$key])."</td>       
                    <td align=right>".number_format($gajibrt[$key])."</td>
                    <td align=right>".number_format($penalty[$key])."</td>
                    <td align=right>".number_format($baris)."</td>
                    </tr>";
                 $ttl+=$baris;
                 $gj1+=$gajibrt[$key];
                 $pnt+=$penalty[$key];
                 $alk+=$gajiyangsudahdialokasi[$key];
                 $apnn+=$gjPanen[$key];
                 $arwt+=$gjPerawatan[$key];
             }
            echo"<tr class=rowcontent id='row".$no."'>
                    <td colspan=7>Total</td>
                    <td align=right>".number_format($arwt)."</td>
                    <td align=right>".number_format($apnn)."</td>
                    <td align=right>".number_format($alk)."</td>
                    <td align=right>".number_format($gj1)."</td>
                    <td align=right>".number_format($pnt)."</td>
                    <td align=right>".number_format($ttl)."</td>
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