<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tahunbulan = implode("",explode('-',$param['periode']));

#1. ambil periode akuntansi
$str="select tanggalmulai,tanggalsampai,periode from ".$dbname.".setup_periodeakuntansi where 
      kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=0";
$tgmulai='';
$tgsampai='';
$periode='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tgsampai   = $bar->tanggalsampai;
    $tgmulai    = $bar->tanggalmulai;
    $periode      =$bar->periode;
}
if($tgmulai=='' || $tgsampai=='')
    exit("Error: Accounting period is not registered");

#ambil kamus traksi
$str="select kodetraksi,kodevhc from ".$dbname.".vhc_5master";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $kamus[$bar->kodevhc]=$bar->kodetraksi;
}

#pastikan semua kegiatan ada noakun pada saat entry
#antisipasi penggantian kegiatan traksi
$str="select distinct a.notransaksi,a.jenispekerjaan from ".$dbname.".vhc_rundt a
    left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
    where b.tanggal like '".$periode."%'
    and a.jenispekerjaan not in (SELECT kodekegiatan FROM ".$dbname.".vhc_kegiatan)";
$resf=mysql_query($str);
//echo mysql_error($conn);
if(mysql_num_rows($resf)>0)
{
    echo "Error : There are Vehicle activity that do not have Acount Number, Please contact administrator\n";
	while($barf=mysql_fetch_object($resf)){
		print_r($barf);
	}
	exit();
}

#2. ambil biaya workshop
#kode parameter WS1, ambil semua noakun biaya bengkel
$str="select noakundebet,sampaidebet from ".$dbname.".keu_5parameterjurnal where jurnalid='WS1'";
$res=mysql_query($str);
$dariakun='';
$sampaiakun='';
while($bar=  mysql_fetch_object($res))
{
    $dariakun=$bar->noakundebet;
    $sampaiakun=$bar->sampaidebet;
}
if($dariakun=='' or $sampaiakun=='')
    exit('Eror: Journalid for WS1 not found');

$str="select sum(debet-kredit) as jumlah from ".$dbname.".keu_jurnaldt_vw  
	  where noakun >='".$dariakun."' and noakun<='".$sampaiakun."' 
	  and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' 
	  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."'
	  and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS') or noreferensi is NULL)";

$res=  mysql_query($str);
$bybengkel=0;
while($bar=mysql_fetch_object($res))
{
	$bybengkel=$bar->jumlah;
}

#3 periksa apakah sudah posting semua
$str="select * from ".$dbname.".msvhc_by_operator where posting=0 
	  and kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
	  where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%')
	  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' limit 1";
$res=mysql_query($str);
$str1="select * from ".$dbname.".vhc_runht where posting=0
	   and kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
	   where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%')
	   and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' limit 1";
$res1=mysql_query($str1);

if(mysql_num_rows($res)>0 or mysql_num_rows($res1)>0){
	$t="Service:\n";
	while($bart=mysql_fetch_object($res)){
	   $t.=$bart->notransaksi."\n";
	}
	$t.="Pekerjaan:\n";
	while($bart=mysql_fetch_object($res1)){
	   $t.=$bart->notransaksi."\n";
	}	 
	exit("Error: there are transactions that have not posted:\n".$t);
}
#4 ambil semua kendaraan yang diservice pada periode berjalan
$str="select sum(downtime) as dt,kodevhc from ".$dbname.".msvhc_by_operator 
	  where notransaksi like '".$_SESSION['empl']['lokasitugas']."%' 
	  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and posting=1
	  group by kodevhc";
$res=mysql_query($str);
$kend=Array();
$byrinci=Array();
$totaljamservice=0;
while($bar=mysql_fetch_object($res))
{
   $totaljamservice+=$bar->dt;
   $kend[$bar->kodevhc]=$bar->dt; 
}
foreach($kend as $key=>$val){
		@$byrinci[$key]=($val/$totaljamservice)*$bybengkel;
}

#=======================================================================================================  
#4.5 ambilnoakun biaya kendaraan
$akunkdari='';
$akunksampai='';
$strh="select distinct noakundebet,sampaidebet  from ".$dbname.".keu_5parameterjurnal where  jurnalid='LPVHC'";
$resh=mysql_query($strh);
while($barh=mysql_fetch_object($resh))
{
	$akunkdari=$barh->noakundebet;
	$akunksampai=$barh->sampaidebet;
}
if($akunkdari=='' or $akunksampai=='')
{
	exit("Error: Journal parameter for LPVHC not found");
}
#5 ambil biaya perkendaraan dari data yang sudah dijurnal jurnal 
$str="select sum(debet-kredit) as jlh,kodevhc from ".$dbname.".keu_jurnaldt_vw where
	  kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
	  where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%') 
	  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' 
	  and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%'
	  and (noakun between '".$akunkdari."' and '".$akunksampai."')   
	  and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS') or noreferensi is NULL) 
	  group by kodevhc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)) {
		if(!isset($biayattlkend[$bar->kodevhc])) $biayattlkend[$bar->kodevhc]=0;
		$biayattlkend[$bar->kodevhc]+=$bar->jlh; 	
}

#mengambil biaya service di bengkel unit lain
#ambil akun biaya bengkel dialokasi:
$str="select noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='WS2'";
$noakunAlkBengkel='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)) {
		$noakunAlkBengkel=$bar->noakunkredit; 	
}

if($noakunAlkBengkel!=''){
$str="select sum(jumlah*-1) as service,kodevhc from ".$dbname.".keu_jurnaldt_vw where
	  kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
	  where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%') 
	  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' 
	  and kodeorg not like '%".$_SESSION['empl']['lokasitugas']."%'
	  and noakun= '".$noakunAlkBengkel."'  
	  and (noreferensi not in('ALK_KERJA_AB') or noreferensi is NULL) 
	  group by kodevhc";  
	$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)) {
			if(!isset($biayattlkend[$bar->kodevhc])) $biayattlkend[$bar->kodevhc]=0;
			$biayattlkend[$bar->kodevhc]+=$bar->service; 	
		}	  
 }
 else
 {
 }
 
#6 ambil semua jamkerja kendaraan per unit
$str="select sum(a.jumlah) as jlhjam,kodevhc from ".$dbname.".vhc_rundt a
	left join ".$dbname.".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join ".$dbname.".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' and alokasibiaya!='' 
	and jenispekerjaan!=''  and   
	kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
	where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%')
	group by kodevhc";   
$res=mysql_query($str);
// echo mysql_error($conn);
$biayaperjam=Array();
while($bar=mysql_fetch_object($res)){
    if(!isset($byrinci[$bar->kodevhc])){
		$byrinci[$bar->kodevhc]=0;
	}
	#biaya kendaraan ditambah biaya bengkel yang masuk ke masing-masing kendaraan
 	 	@$biayaperjam[$bar->kodevhc]=($biayattlkend[$bar->kodevhc]+$byrinci[$bar->kodevhc])/$bar->jlhjam;
}
  
echo"<button  onclick=prosesAlokasi(1) id=btnproses>Process</button>
	 <font ><br>Note: If it does not work please reprocess, the old data will be deleted.</font>
	 <table class=sortable cellspacing=1 border=0>
	 <thead>
	   <tr class=rowheader>
	   <td>No</td>
	   <td>Period</td>
	   <td>KodeVhc</td>
	   <td>Organization</td>
	   <td>Price/Hour</td>
	   <td>Type</td>
	   </tr>
	 </thead>
	 <tbody>";

$no=0;
foreach($byrinci as $key =>$val) { 
	$no+=1;
	echo"<tr class=rowcontent id='row".$no."'>
	   <td>".$no."</td>
	   <td id='periode".$no."'>".$_POST['periode']."</td>
	   <td id='kodevhc".$no."'>".$key."</td>
		<td>".$kamus[$key]."</td>
	   <td id='jumlah".$no."' align=right>".number_format($val,2,'.','')."</td>    
	   <td id='jenis".$no."'>BYWS</td>
	   </tr>";
}

foreach ($biayaperjam as $key=>$jlh)
{ 
	$no+=1;
	echo"<tr class=rowcontent id='row".$no."'>
	   <td>".$no."</td>
	   <td id='periode".$no."'>".$_POST['periode']."</td>
	   <td id='kodevhc".$no."'>".$key."</td>
	   <td>".$kamus[$key]."</td>    
	   <td id='jumlah".$no."' align=right>".number_format($jlh,2,'.','')."</td>    
	   <td id='jenis".$no."'>ALKJAM</td>
	   </tr>";
}
echo"</tbody><tfoot></tfoot></table>";