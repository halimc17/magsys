<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/sdm_2rekapabsen.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
   
if($kdOrg=='')$kdOrg=$_GET['kdorg'];
if($afdId=='')$afdId=$_GET['afdid'];
if($afdId=='')$afdId=$_GET['kdorg'];
$mandorid=$_GET['mandorid'];
$karyawanid=$_GET['karyawanid'];
$tanggal=$_GET['tanggal'];
$pengawas=$_GET['pengawas'];
/*
$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'"; 
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
}

$strz="select notransaksi, tanggal, karyawanid,(upahpremi-rupiahpenalty) as upahpremi from ".$dbname.".kebun_prestasi_vw
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by notransaksi";
*/
if($pengawas=='M'){
	$strz="select b.tipetransaksi,b.tanggal,b.kodeorg as kodeunit,a.kodeorg as kodeblok,a.notransaksi
			,b.nikmandor  as mandorid	 ,d.nik as mandornik    ,d.namakaryawan as namamandor,d.subbagian as subbagianmandor,g.namajabatan as jabatanmandor
			,b.keranimuat as keranimuatid,e.nik as keranimuatnik,e.namakaryawan as namakeranimuat,e.subbagian as subbagiankeranimuat,h.namajabatan as jabatankeranimuat
			,a.nik		  as karyawanid  ,c.nik as karyawannik  ,c.namakaryawan,c.subbagian as subbagiankaryawan,f.namajabatan as jabatankaryawan
			,a.hasilkerja as hasilkerja,a.hasilkerjakg as hasilkerjakg,a.norma as basis,a.luaspanen as luaspanen,i.namaorganisasi as namablok
			,a.brondolan as brondolan
			,a.upahkerja as upahkerja
			,a.upahpremi as upahpremi
			,a.upahpenalty as upahpenalty
			,a.rupiahpenalty as rupiahpenalty
			,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,a.penalti9,a.penalti10
		from ".$dbname.".kebun_prestasi a 
		LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
		LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
		LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=b.nikmandor
		LEFT JOIN ".$dbname.".datakaryawan e on e.karyawanid=b.keranimuat
		LEFT JOIN ".$dbname.".sdm_5jabatan f on f.kodejabatan=c.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan g on g.kodejabatan=d.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan h on h.kodejabatan=e.kodejabatan
		LEFT JOIN ".$dbname.".organisasi i on i.kodeorganisasi=a.kodeorg
		where b.tipetransaksi='PNN'
			and b.kodeorg like '".$kdOrg."%'
			and a.kodeorg like '".$afdId."%' 
			and b.tanggal like '".$tanggal."%'
			and b.jurnal='1'
			and b.nikmandor like '".$mandorid."%'
			and b.nikmandor <>''
			and a.nik like '".$karyawanid."%'
		ORDER BY b.nikmandor,a.nik,b.tanggal";
}else{
	$strz="select b.tipetransaksi,b.tanggal,b.kodeorg as kodeunit,a.kodeorg as kodeblok,a.notransaksi
			,b.keranimuat as mandorid	 ,e.nik as mandornik    ,e.namakaryawan as namamandor,e.subbagian as subbagianmandor,h.namajabatan as jabatanmandor
			,b.keranimuat as keranimuatid,e.nik as keranimuatnik,e.namakaryawan as namakeranimuat,e.subbagian as subbagiankeranimuat,h.namajabatan as jabatankeranimuat
			,a.nik		  as karyawanid  ,c.nik as karyawannik  ,c.namakaryawan,c.subbagian as subbagiankaryawan,f.namajabatan as jabatankaryawan
			,a.hasilkerja as hasilkerja,a.hasilkerjakg as hasilkerjakg,a.norma as basis,a.luaspanen as luaspanen,i.namaorganisasi as namablok
			,a.brondolan as brondolan
			,a.upahkerja as upahkerja
			,a.upahpremi as upahpremi
			,a.upahpenalty as upahpenalty
			,a.rupiahpenalty as rupiahpenalty
			,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,a.penalti9,a.penalti10
		from ".$dbname.".kebun_prestasi a 
		LEFT JOIN ".$dbname.".kebun_aktifitas b on b.notransaksi=a.notransaksi
		LEFT JOIN ".$dbname.".datakaryawan c on c.karyawanid=a.nik
		LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=b.nikmandor
		LEFT JOIN ".$dbname.".datakaryawan e on e.karyawanid=b.keranimuat
		LEFT JOIN ".$dbname.".sdm_5jabatan f on f.kodejabatan=c.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan g on g.kodejabatan=d.kodejabatan
		LEFT JOIN ".$dbname.".sdm_5jabatan h on h.kodejabatan=e.kodejabatan
		LEFT JOIN ".$dbname.".organisasi i on i.kodeorganisasi=a.kodeorg
		where b.tipetransaksi='PNN'
			and b.kodeorg like '".$kdOrg."%'
			and a.kodeorg like '".$afdId."%' 
			and b.tanggal like '".$tanggal."%'
			and b.jurnal='1'
			and b.keranimuat like '".$mandorid."%'
			and b.keranimuat <>''
			and a.nik like '".$karyawanid."%'
		ORDER BY b.keranimuat,a.nik,b.tanggal";
}
$resz=mysql_query($strz);
$row =mysql_num_rows($resz);
//exit('Warning : '.$row);
//while($barz=mysql_fetch_object($resz))
//{
//   $notran['BKM:'.$barz->notransaksi].='BKM:'.$barz->notransaksi;
//    $premi['BKM:'.$barz->notransaksi]=$barz->upahpremi;
//}
//echo $strz.'<br>';

/*
//ambil data di perawatan
$strx="select notransaksi,karyawanid,tanggal,(insentif) as upahpremi from ".$dbname.".kebun_kehadiran_vw
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by notransaksi";   
$resx=mysql_query($strx);
while($barx=mysql_fetch_object($resx))
{
    $notran['BKM:'.$barx->notransaksi]='BKM:'.$barx->notransaksi;
    $premi['BKM:'.$barx->notransaksi]=$barx->upahpremi;
}
//echo $strx.'<br>';

//ambil data kemandoran
$stry="select karyawanid,tanggal,(premiinput) as upahpremi from ".$dbname.".kebun_premikemandoran 
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by tanggal";   
$resy=mysql_query($stry);
while($bary=mysql_fetch_object($resy))
{
    $notran['PREMI KEMANDORAN:'.$bary->tanggal]='PREMI KEMANDORAN:'.$bary->tanggal;
    $premi['PREMI KEMANDORAN:'.$bary->tanggal]=$bary->upahpremi;
}

//premi traksi
$strv="select notransaksi,idkaryawan as karyawanid,tanggal,(premi-penalty) as upahpremi from ".$dbname.".vhc_runhk 
     where tanggal like '".$tanggal."%' and idkaryawan = '".$karyawanid."'
     order by notransaksi";  
$resv=mysql_query($strv);
while($barv=mysql_fetch_object($resv))
{
    $notran['TRAKSI:'.$barv->notransaksi]='TRAKSI:'.$barv->notransaksi;
    $premi['TRAKSI:'.$barv->notransaksi]=$barv->upahpremi;
}
*/
//echo $strv;
//
//echo "<pre>";
//print_r($notran);
//print_r($premi);
//echo "</pre>";

//=================================================
//echo"<fieldset><legend>Print Excel</legend>
//     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahandetail.php?type=excel&tanggal=".$tanggal."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
//     </fieldset>"; 
if($_GET['type']!='excel')$stream="<table class=sortable border=0 cellspacing=1>"; //else
//$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td>No</td>
          <td>Karyawan</td>
          <td>No. Transaksi</td>
          <td>Tanggal</td>
          <td>Blok</td>
          <td>Basis</td>
          <td>Hasil Jjg</td>
          <td>Hasil Kg</td>
          <td>Brondol Kg</td>
          <td>Luas Panen</td>
          <td>Upah Kerja</td>
          <td>Premi</td>
          <td>Denda Upah</td>
          <td>Buah Mentah</td>
          <td>Tangkai Panjang</td>
          <td>Over Pruning</td>
          <td>Buah Tinggal</td>
          <td>Brondolan Tinggal</td>
          <td>Pelepah Tidak Disusun</td>
          <td>Pelepah Sengkleh</td>
          <td>Buah diperam</td>
          <td>Buah Matahari</td>
          <td>Buah Tidak Disusun</td>
          <td>Penalty Rp</td>";
//		  if($_GET['type']!='excel')$stream.="<td>Browse</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
        if($row==0){
            $stream.="<tr class=rowcontent>";
            $stream.="<td colspan=4>Absence</td>";
            $stream.="</tr>";
        }else{
			/*
            foreach($notran as $kyu){
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=left>".$namakaryawan[$karyawanid]."</td>";
                $stream.="<td align=left>".$kyu."</td>";
                $stream.="<td align=center>".$tanggal."</td>";
                $stream.="<td align=right>".number_format($premi[$kyu])."</td>";
                $stream.="</tr>";
            }
			*/
			while($barz=mysql_fetch_object($resz)){
				$no+=1;
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=center>".$no."</td>";
                $stream.="<td align=left>".$barz->namakaryawan."</td>";
                $stream.="<td align=left>".$barz->notransaksi."</td>";
                $stream.="<td align=left>".$barz->tanggal."</td>";
                $stream.="<td align=left>".$barz->namablok."</td>";
                $stream.="<td align=right>".$barz->basis."</td>";
                $stream.="<td align=right>".$barz->hasilkerja."</td>";
                $stream.="<td align=right>".$barz->hasilkerjakg."</td>";
                $stream.="<td align=right>".$barz->brondolan."</td>";
                $stream.="<td align=right>".$barz->luaspanen."</td>";
                $stream.="<td align=right>".number_format($barz->upahkerja,2)."</td>";
                $stream.="<td align=right>".number_format($barz->upahpremi,2)."</td>";
                $stream.="<td align=right>".number_format($barz->upahpenalty,2)."</td>";
                $stream.="<td align=right>".$barz->penalti1."</td>";
                $stream.="<td align=right>".$barz->penalti2."</td>";
                $stream.="<td align=right>".$barz->penalti3."</td>";
                $stream.="<td align=right>".$barz->penalti4."</td>";
                $stream.="<td align=right>".$barz->penalti5."</td>";
                $stream.="<td align=right>".$barz->penalti6."</td>";
                $stream.="<td align=right>".$barz->penalti7."</td>";
                $stream.="<td align=right>".$barz->penalti8."</td>";
                $stream.="<td align=right>".$barz->penalti9."</td>";
                $stream.="<td align=right>".$barz->penalti10."</td>";
                $stream.="<td align=right>".number_format($barz->rupiahpenalty,2)."</td>";
                $stream.="</tr>";
			}
		}
//    $str="select * from ".$dbname.".pabrik_pengolahan
//              where tanggal = '".$tanggal."'";   
//    $res=mysql_query($str);
//    $no=0;
//    $tdebet=0;
//    $tkredit=0;
//    while($bar= mysql_fetch_object($res))
//    {
//        $no+=1;
//        $debet=0;
//        $kredit=0;
//        if($bar->jumlah>0)
//             $debet= $bar->jumlah;
//        else
//             $kredit= $bar->jumlah*-1;
//    
//    $stream.="<tr class=rowcontent>
//           <td align=right>".$no."</td>
//           <td>".tanggalnormal($bar->tanggal)."</td>    
//           <td align=right>".$bar->nopengolahan."</td>               
//           <td align=right>".$bar->shift."</td>               
//           <td align=right>".substr($bar->jammulai,0,5)."</td>               
//           <td align=right>".substr($bar->jamselesai,0,5)."</td>               
//           <td align=right>".$bar->jamdinasbruto."</td>               
//           <td align=right>".$bar->jamstagnasi."</td>               
//           <td align=right>".number_format($bar->jumlahlori)."</td>               
//           <td align=right>".number_format($bar->tbsdiolah)."</td>";
//   		  if($_GET['type']!='excel')$stream.="
//           <td><img onclick=\"parent.browsemesin(".$bar->nopengolahan.",'".$tanggal."','".$kodeorg."','".$periode_tahun."','".$periode_bulan."',event);\" title=\"Mesin\" class=\"resicon\" src=\"images/icons/joystick.png\">
//		       <img onclick=\"parent.browsebarang(".$bar->nopengolahan.",'".$tanggal."','".$kodeorg."','".$periode_tahun."','".$periode_bulan."',event);\" title=\"Barang\" class=\"resicon\" src=\"images/icons/box.png\"></td>";               
//         $stream.="</tr>";
//    } 
   $stream.="</tbody></table>";
//   if($_GET['type']=='excel')
//   {
//$nop_="Detail_pengolahan_".$kodeorg."_".$tanggal;
//        if(strlen($stream)>0)
//        {
//        if ($handle = opendir('tempExcel')) {
//            while (false !== ($file = readdir($handle))) {
//                if ($file != "." && $file != "..") {
//                    @unlink('tempExcel/'.$file);
//                }
//            }	
//           closedir($handle);
//        }
//         $handle=fopen("tempExcel/".$nop_.".xls",'w');
//         if(!fwrite($handle,$stream))
//         {
//          echo "<script language=javascript1.2>
//                parent.window.alert('Can't convert to excel format');
//                </script>";
//           exit;
//         }
//         else
//         {
//          echo "<script language=javascript1.2>
//                window.location='tempExcel/".$nop_.".xls';
//                </script>";
//         }
//        fclose($handle);
//        }       
//   }
//   else
   {
       echo $stream;
   }    
       
?>