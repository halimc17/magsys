<?php 
// file creator: dhyaz aug 3, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodept=$_POST['kodept'];
$kodeunit=$_POST['kodeunit'];

//kamus nama unit
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
    where tipe in('KEBUN','PABRIK','GUDANG','GUDANGTEMP','TRAKSI','KANWIL') or (tipe='HOLDING' and length(kodeorganisasi)=4)
    order by kodeorganisasi";
$res=mysql_query($str);
$kamus=array();
while($bar=mysql_fetch_object($res))
{
    $kamus[$bar->kodeorganisasi]=$bar->namaorganisasi;
}

//ambil anak-anak
$str="select kodeorganisasi from ".$dbname.".organisasi
    where induk = '".$kodeunit."' and tipe like 'gudang%'
    order by kodeorganisasi";
$res=mysql_query($str);
$anak=array();
while($bar=mysql_fetch_object($res))
{
    $anak[$bar->kodeorganisasi]=$bar->kodeorganisasi;
}

//ambil unit holding
$jumlahunit=0;
$str="select kodeorganisasi from ".$dbname.".organisasi 
    where induk='".$kodept."' and kodeorganisasi like '".$kodeunit."%' and tipe = 'HOLDING'
    order by tipe desc";
$res=mysql_query($str);
$unit=array();
while($bar=mysql_fetch_object($res))
{
    $unit[$bar->kodeorganisasi]=$bar->kodeorganisasi;
    $jumlahunit+=1;
}
$str="select kodeorganisasi from ".$dbname.".organisasi 
    where induk='".$kodept."' and kodeorganisasi like '".$kodeunit."%' and tipe != 'HOLDING'
    order by tipe desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $unit[$bar->kodeorganisasi]=$bar->kodeorganisasi;
    $jumlahunit+=1;
}

// ambil data
$arr=Array();
$str1="select * from ".$dbname.".keu_setup_watu_tutup order by periode desc, kodeorg";
$res1=mysql_query($str1);
while($bar1=mysql_fetch_object($res1))
{
    $arr[$bar1->periode][$bar1->kodeorg]['username']=$bar1->username;
    $arr[$bar1->periode][$bar1->kodeorg]['waktu']=$bar1->waktu;
}

$no=1;
$str="select * from ".$dbname.".setup_periodeakuntansi order by periode desc, kodeorg";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    //if(isset($arr[$baris->periode][$baris->kodeorg])) {
		$periode[$baris->periode]=$baris->periode;    
		$tutup[$baris->periode][$baris->kodeorg]=$baris->tutupbuku;
		$waktu[$baris->periode][$baris->kodeorg]=$arr[$baris->periode][$baris->kodeorg]['waktu'];
		$pelaku[$baris->periode][$baris->kodeorg]=$arr[$baris->periode][$baris->kodeorg]['username'];
	//}
}

// kasbank total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".keu_kasbankht group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $kasbank[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// kasbank total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".keu_kasbankht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $kasbankp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// bkm total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".kebun_aktifitas group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $bkm[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// bkm total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".kebun_aktifitas where jurnal = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $bkmp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// traksi running total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_runht group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $traksi[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// traksi running total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_runht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $traksip[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// traksi service total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_penggantianht group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
	if(!isset($traksi[$baris->periode][$baris->kodeorg])) $traksi[$baris->periode][$baris->kodeorg]=0;
    $traksi[$baris->periode][$baris->kodeorg]+=$baris->jumlah;    
}
// traksi service total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_penggantianht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
	if(!isset($traksip[$baris->periode][$baris->kodeorg])) $traksip[$baris->periode][$baris->kodeorg]=0;
    $traksip[$baris->periode][$baris->kodeorg]+=$baris->jumlah;    
}


// bapp total
$str="select substr(kodeblok,1,4) as kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_baspk group by substr(kodeblok,1,4), substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $bapp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// bapp total post
$str="select substr(kodeblok,1,4) as kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_baspk where statusjurnal = 1 group by substr(kodeblok,1,4), substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $bappp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}


// gudang total
$str="select kodegudang, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_transaksiht group by kodegudang, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $gudang[$baris->periode][$baris->kodegudang]=$baris->jumlah;    
}
// gudang total post
$str="select kodegudang, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_transaksiht where post = 1 group by kodegudang, substr(tanggal,1,7) ";
$res=mysql_query($str);
while($baris= mysql_fetch_object($res))
{
    $gudangp[$baris->periode][$baris->kodegudang]=$baris->jumlah;    
}

//echo "<pre>";
//print_r($waktu);
//echo "</pre>";

echo"<table class=sortable cellspacing=1 border=0 width=100%>
    <thead>
    <tr>
        <td align=center>".$_SESSION['lang']['periode']."</td>
        <td align=center>".$_SESSION['lang']['kodeorg']."</td>
        <td align=center>".$_SESSION['lang']['status']."</td>
        <td align=center>".$_SESSION['lang']['waktu']."</td>
        <td align=center>".$_SESSION['lang']['nama']."</td>  
        <td align=center>".$_SESSION['lang']['kasbank']." (posted)</td>  
        <td align=center>".$_SESSION['lang']['traksi']." (posted)</td>  
        <td align=center>BAPP (posted)</td>  
        <td align=center>BKM (posted)</td>";  
if(!empty($anak))foreach($anak as $data){
    echo"<td align=center colspan=2 title=\"".$kamus[$data]."\">".$data."</td>";
}
    echo"</tr>  
    </thead>
    <tbody>";

if(!empty($periode))foreach($periode as $per){
    $tamper=true;
    if(!empty($unit))foreach($unit as $uni){
        if($tamper){
            $tampil=$per;
        }else{
            $tampil='';
        }
        $tamtut='';
        $warna="<tr class=rowcontent>";
        if(isset($tutup[$per][$uni]) and $tutup[$per][$uni]=='1'){ $tamtut='closed'; }
        if(!isset($tutup[$per][$uni]) or $tutup[$per][$uni]=='0'){ $tamtut='__active'; $warna="<tr bgcolor=lightgreen>"; }
        echo $warna;
        if($tamper)echo"<td align=center rowspan=".$jumlahunit.">".$tampil."</td>";
        echo"<td>".$uni."</td>";
        echo"<td>".$tamtut."</td>";
        echo"<td>".(isset($waktu[$per][$uni])? $waktu[$per][$uni]: '')."</td>";
        echo"<td>".(isset($pelaku[$per][$uni])? $pelaku[$per][$uni]: '')."</td>";
        @$persen=$kasbankp[$per][$uni]*100/$kasbank[$per][$uni];
        echo"<td align=right nowrap>".(isset($kasbank[$per][$uni])? $kasbank[$per][$uni]: 0)." (".number_format($persen)."%)</td>";
        @$persen=$traksip[$per][$uni]*100/$traksi[$per][$uni];
        echo"<td align=right nowrap>".(isset($traksi[$per][$uni])? $traksi[$per][$uni]: 0)." (".number_format($persen)."%)</td>";
        @$persen=$bappp[$per][$uni]*100/$bapp[$per][$uni];
        echo"<td align=right nowrap>".(isset($bapp[$per][$uni])? $bapp[$per][$uni]: 0)." (".number_format($persen)."%)</td>";
        @$persen=$bkmp[$per][$uni]*100/$bkm[$per][$uni];
        echo"<td align=right nowrap>".(isset($bkm[$per][$uni])? $bkm[$per][$uni]: 0)." (".number_format($persen)."%)</td>";
if(!empty($anak))foreach($anak as $data){
//    echo"<td align=center>".$data."</td>";
        $tamtud='';
        if($tutup[$per][$data]=='1')$tamtud='closed';
        if($tutup[$per][$data]=='0')$tamtud='__active';
        echo"<td>".$tamtud."</td>";
        @$persen=$gudangp[$per][$data]*100/$gudang[$per][$data];
        echo"<td align=right nowrap>".$gudang[$per][$data]." (".number_format($persen)."%)</td>";
        
}
        echo"</tr>";
        $tamper=false;
    }        
}
    echo"</tbody>
    <tfoot>
    </tfoot>		 
    </table>
";
    
?>
