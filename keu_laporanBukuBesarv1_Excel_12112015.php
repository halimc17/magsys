<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tanggal1=$_GET['tanggal1'];
$tanggal2=$_GET['tanggal2'];
$akundari=$_GET['akundari'];
$akunsampai=$_GET['akunsampai'];
$regional=$_GET['regional']; 



$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$tanggal1);
$periode=$qwe[2].$qwe[1];
$bulan=$qwe[1];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];


###tambahan indra
//bentuk tanggal 1 untuk veriv
$qwer=explode("-",$tanggal1);
$tglverivsatu=$qwer[2];

//bentuk tangal 1 diawal bulan untuk sum db-kr bentuk sawal
$tglsatu=$qwer[2]."-".$qwer[1]."-01";

//hitung tanggal kemarin
$tglx =  str_replace("-","",$tanggal1);
$tglkemarin = strtotime('-1 day',strtotime($tglx));
$tglkemarin = date('Y-m-d', $tglkemarin);
##tutup tambah indra

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$namapt=strtoupper($bar->namaorganisasi);
}
//ambil namagudang
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'";
$namagudang='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$namagudang=strtoupper($bar->namaorganisasi);
}

// exclude laba rugi tahun berjalan
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal
    where kodeaplikasi = 'CLM'
    ";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $clm=$bar->noakundebet;
}






if($regional=='' && $gudang=='')
{
    $wheregudang=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
else if($regional!='' && $gudang=='')
{
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
}
else
{
    $wheregudang=" and kodeorg='".$gudang."'";
}


//hitung total transaksi yang sudah ada
$iTran="select sum(debet)-sum(kredit) as transaksi,noakun from ".$dbname.".keu_jurnaldt_vw where "
        . " noakun != '".$clm."' and tanggal between '".$tglsatu."' and '".$tglkemarin."' "
        . " and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." "
        . " group by noakun";
$nTran=  mysql_query($iTran)or die (mysql_error($conn));
while($dTran=mysql_fetch_object($nTran))
{
    $totaltran[$dTran->noakun]+=$dTran->transaksi;
}


//ambil saldo awal

/*if($gudang==''){
    $str="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
    $wheregudang='';
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
	$wheregudang.="'".strtoupper($bar->kodeorganisasi)."',";
    }
    $wheregudang="and kodeorg in (".substr($wheregudang,0,-1).") ";
}else{
    $wheregudang="and kodeorg = '".$gudang."' ";
}*/





$str="select * from ".$dbname.".keu_saldobulanan where noakun != '".$clm."' and periode = '".$periode."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";

$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}
//        echo "<pre>";
//        print_r($saldoawal);
//        echo "</pre>";

// ambil data
$isidata=array();
$str="select *, (SELECT t3.nocek FROM ".$dbname.".keu_kasbankht t3 WHERE t3.notransaksi = t1.noreferensi) as nocekgiro  from ".$dbname.".keu_jurnaldt_vw t1 where t1.noakun != '".$clm."' and t1.tanggal >= '".$tanggal1."' and t1.tanggal <= '".$tanggal2."' and t1.noakun >= '".$akundari."' and t1.noakun <= '".$akunsampai."' ".$wheregudang." order by t1.noakun, t1.tanggal";
$res=mysql_query($str);
while($bar= mysql_fetch_object($res))
{
    $qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
    $isidata[$qwe][nojur]=$bar->nojurnal;
    $isidata[$qwe][tangg]=$bar->tanggal;
    $isidata[$qwe][noaku]=$bar->noakun;
    $isidata[$qwe][keter]=$bar->keterangan;
    $isidata[$qwe][debet]=$bar->debet;
    $isidata[$qwe][kredi]=$bar->kredit;
    $isidata[$qwe][kodeb]=$bar->kodeblok;
    if($bar->kodeblok=='')$org=$bar->kodeorg; else $org=substr($bar->kodeblok,0,6);
    $isidata[$qwe][organ]=$org;
    $isidata[$qwe][noref]=$bar->noreferensi;
    $isidata[$qwe][nik]=$bar->nik;
    $isidata[$qwe][kosup]=$bar->kodesupplier;
    $isidata[$qwe][nodok]=$bar->nodok;
    $isidata[$qwe][nocekgiro]=$bar->nocekgiro;
    $aqun[$bar->noakun]=$bar->noakun;
}
//        echo "<pre>";
//        print_r($isidata);
//        echo "</pre>";

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun
    where level = '5' and noakun!='".$clm."'";
 
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

// kamus nama supplier
$str="select supplierid, namasupplier from ".$dbname.".log_5supplier
    ";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namasupplier[$bar->supplierid]=$bar->namasupplier;
}

// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok
    ";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
} 

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    $sort_noaku[] = $key['noaku'];
    $sort_tangg[] = $key['tangg'];
    $sort_debet[] = $key['debet'];
    $sort_nojur[] = $key['nojur'];
}

// sort
if(!empty($isidata))array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($aqun))asort($aqun);

$stream=strtoupper($_SESSION['lang']['laporanbukubesar'])." : ".$namapt." ".$namagudang."<br>".
        strtoupper($_SESSION['lang']['tanggal'])." : ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."<br>".
        strtoupper($_SESSION['lang']['noakun'])." : ".$akundari." s/d ".$akunsampai."<br>
    <table border=1>
    <thead>
    <tr bgcolor='#dedede'>
        <td align=center>".$_SESSION['lang']['nomor']."</td>
        <td align=center>".$_SESSION['lang']['nojurnal']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['noakun']."</td>
        <td align=center>".$_SESSION['lang']['keterangan']."</td>
        <td align=center>".$_SESSION['lang']['debet']."</td>
        <td align=center>".$_SESSION['lang']['kredit']."</td>
        <td align=center>".$_SESSION['lang']['saldo']."</td>
        <td align=center>".$_SESSION['lang']['kodeorg']."</td>
        <td align=center>".$_SESSION['lang']['kodeblok']."</td>
        <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
        <td align=center>".$_SESSION['lang']['noreferensi']."</td>
        <td align=center>".$_SESSION['lang']['namasupplier']."</td>
            <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td align=center>".$_SESSION['lang']['nodok']."</td>
        <td align=center>No Cek/Giro</td>
    </tr>  
    </thead>
    <tbody id=container>";
 //tampil data
$no=0;
// tampilin daftar akun
if(!empty($aqun))foreach($aqun as $akyun){
    $subsalwal=$saldoawal[$akyun];
    $totaldebet=0;
    $totalkredit=0;
    $subsalak=$subsalwal;
    
    
    if($tglverivsatu!='01')
    {
        $salwal=$subsalwal+$totaltran[$akyun];
    }
    else
    {
        $salwal=$subsalwal;
    }
    //$salwal=$subsalwal;
    
    $grandsalwal+=$subsalwal;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=3></td>";
        $stream.="<td>".$akyun."</td>";
        $stream.="<td colspan=3>".$namaakun[$akyun]."</td>";
        $stream.="<td align=right>".number_format($salwal,2)."</td>";
        $stream.="<td colspan=8></td>";
    $stream.="</tr>";
// tampilin jurnal daftar akun    
    if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris[noaku]==$akyun){
            $no+=1;
            $stream.="<tr>";
            $stream.="<td>".$no."</td>";
            $stream.="<td>".substr($baris[nojur],14,8)."</td>";
            $stream.="<td>".$baris[tangg]."</td>";
            $stream.="<td>".$baris[noaku]."</td>";
            $stream.="<td>".$baris[keter]."</td>";

//            $stream.="<td align=right>".number_format($salwal)."</td>";
            $stream.="<td align=right>".number_format($baris[debet],2)."</td>";
            $totaldebet+=$baris[debet];
            $grandtotaldebet+=$baris[debet];
            $stream.="<td align=right>".number_format($baris[kredi],2)."</td>";
            $totalkredit+=$baris[kredi];
            $grandtotalkredit+=$baris[kredi];
            $salwal=$salwal+($baris[debet])-($baris[kredi]);
            $stream.="<td align=right>".number_format($salwal,2)."</td>";
            $stream.="<td>".$baris[organ]."</td>";
            $stream.="<td>".$baris[kodeb]."</td>";
            $stream.="<td>".$tahuntanam[$baris[kodeb]]."</td>";
            $stream.="<td>".$baris[noref]."</td>";
            $stream.="<td>".$namasupplier[$baris[kosup]]."</td>";
             $stream.="<td>".$nmKar[$baris[nik]]."</td>";
            $stream.="<td>".$baris[nodok]."</td>";
            $stream.="<td>".$baris[nocekgiro]."</td>";
            
            $stream.="</tr>";
            $subsalak=$salwal;
        }
    } 
// subtotal    
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=5>SubTotal</td>";
//        $stream.="<td align=right>".number_format($subsalwal)."</td>";
        $stream.="<td align=right>".number_format($totaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($totalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($subsalak,2)."</td>";
        $stream.="<td colspan=8></td>";
     $stream.="</tr>";
}

// total
    $grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=5>GrandTotal</td>";
//        $stream.="<td align=right>".number_format($grandsalwal)."</td>";
        $stream.="<td align=right>".number_format($grandtotaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($grandtotalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($grandsalak,2)."</td>";
        $stream.="<td colspan=8></td>";
     $stream.="</tr>";

$stream.="</tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>";



$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$qwe=date("YmdHms");
$nop_="Laporan_BukuBesar_".$pt.$gudang." ".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}    
?>