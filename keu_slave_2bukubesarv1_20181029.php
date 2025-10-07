<?php
// file creator: dhyaz aug 3, 2011
// updated: dz may 22, 2012

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$gudang=$_POST['gudang'];
$tanggal1=$_POST['tanggal1'];
$tanggal2=$_POST['tanggal2'];
$akundari=$_POST['akundari'];
$akunsampai=$_POST['akunsampai'];
$regional=$_POST['regional'];


$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

//check, one-two
if($tanggal1==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
if($tanggal2==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
if($akundari==''){
    echo "WARNING: silakan memilih akun."; exit;
}
if($akunsampai==''){
    echo "WARNING: silakan memilih akun."; exit;
}

//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$tanggal1);
$periode=$qwe[2].$qwe[1];
$bulan=$qwe[1];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];

// Init Grand Total
$grandtotaldebet=$grandtotalkredit=0;




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




if($regional=='' && $gudang=='')
{
   $wheregudang =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    //$where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."') "; 
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}
else
{
    $wheregudang =" and kodeorg ='".$gudang."'";
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




// ambil saldo awal
$str="select * from ".$dbname.".keu_saldobulanan where noakun != '".$clm."' and periode = '".$periode."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	setIt($saldoawal[$bar->noakun],0);
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}
//        echo "<pre>";
//        print_r($saldoawal);
//        echo "</pre>";

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun
    where level = '5' and noakun!='".$clm."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok
    ";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   

// ambil data
$isidata=array();
$str="select *, IF(t1.kodesupplier = '', '', (SELECT t2.namasupplier FROM ".$dbname.".log_5supplier t2 WHERE t2.supplierid = t1.kodesupplier)) as namasupplier, (SELECT t3.nocek FROM ".$dbname.".keu_kasbankht t3 WHERE t3.notransaksi = t1.noreferensi) as nocekgiro from ".$dbname.".keu_jurnaldt_vw t1 where t1.noakun != '".$clm."' and t1.tanggal >= '".$tanggal1."' and t1.tanggal <= '".$tanggal2."' and t1.noakun >= '".$akundari."' and t1.noakun <= '".$akunsampai."' ".$wheregudang." order by t1.noakun, t1.tanggal";
            //echo $str;
$res=mysql_query($str);
while($bar= mysql_fetch_object($res))
{
	$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
    $qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
    $isidata[$qwe]['nojur']=$bar->nojurnal;
    $isidata[$qwe]['tangg']=$bar->tanggal;
    $isidata[$qwe]['noaku']=$bar->noakun;
    $isidata[$qwe]['keter']=$bar->keterangan;
    $isidata[$qwe]['namacustomer']=$optNmCust[$bar->kodecustomer];
    $isidata[$qwe]['namasupplier']=$bar->namasupplier;
    $isidata[$qwe]['nodok']=$bar->nodok;
    $isidata[$qwe]['noreferensi']=$bar->noreferensi;
    $isidata[$qwe]['nocekgiro']=$bar->nocekgiro;
    $isidata[$qwe]['debet']=$bar->debet;
    $isidata[$qwe]['kredi']=$bar->kredit;
    $isidata[$qwe]['kodeb']=$bar->kodeblok;
    $isidata[$qwe]['nik']=$bar->nik;
    if($bar->kodeblok=='')$org=$bar->kodeorg; else $org=substr($bar->kodeblok,0,6);
    $isidata[$qwe]['organ']=$org;
    $aqun[$bar->noakun]=$bar->noakun;
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

$no=0;
$grandsalwal=0;
// tampilin daftar akun

if(!empty($aqun))foreach($aqun as $akyun){
    $subsalwal=isset($saldoawal[$akyun])? $saldoawal[$akyun]: 0;
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
    
    $grandsalwal+=$subsalwal;
    echo"<tr class=rowcontent>";
        echo"<td width=210px align=right colspan=3></td>";
        echo"<td width=80px>".$akyun."</td>";
		echo"<td width=980px colspan=6>&nbsp;</td>";
        echo"<td width=500px colspan=3>".$namaakun[$akyun]."</td>";
        echo"<td width=150px align=right>".number_format($salwal,2)."</td>";
        echo"<td width=160px colspan=2></td>";
        echo"<td></td>";
    echo"</tr>";
	// tampilin jurnal daftar akun
	if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris['noaku']==$akyun){
            $no+=1;
			setIt($nmKar[$baris['nik']],'');
            echo"<tr class=rowcontent>";
            echo"<td width=30px>".$no."</td>";
            echo"<td width=80px>".substr($baris['nojur'],14,8)."</td>";
            echo"<td width=100px>".tanggalnormal($baris['tangg'])."</td>";
            echo"<td width=80px>".$baris['noaku']."</td>";
            echo"<td width=150px>".$nmKar[$baris['nik']]."</td>";
			echo"<td width=160px>".$baris['namacustomer']."</td>";
			echo"<td width=160px>".$baris['namasupplier']."</td>";
			echo"<td width=160px>".$baris['noreferensi']."</td>";
            echo"<td width=160px>".$baris['nodok']."</td>";
            echo"<td width=160px>".$baris['nocekgiro']."</td>";
            echo"<td width=200px>".$baris['keter']."</td>";
			echo"<td align=right width=150px>".number_format($baris['debet'],2)."</td>";
            $totaldebet+=$baris['debet'];
            $grandtotaldebet+=$baris['debet'];
            echo"<td align=right width=150px>".number_format($baris['kredi'],2)."</td>";
            $totalkredit+=$baris['kredi'];
            $grandtotalkredit+=$baris['kredi'];
            $salwal=$salwal+($baris['debet'])-($baris['kredi']);
            echo"<td align=right width=150px>".number_format($salwal,2)."</td>";
            echo"<td width=80px>".$baris['organ']."</td>";
            echo"<td width=80px>".$baris['kodeb']."</td>";
            echo"<td width=80px>".(isset($tahuntanam[$baris['kodeb']])? $tahuntanam[$baris['kodeb']]: '')."</td>";
            echo"</tr>";
            $subsalak=$salwal;
        }
    } 
// subtotal    
    echo"<tr class=rowtitle>";
        echo"<td align=right colspan=11>SubTotal</td>";
//        echo"<td align=right style='width:100px;'>".number_format($subsalwal)."</td>";
        echo"<td align=right>".number_format($totaldebet,2)."</td>";
        echo"<td align=right>".number_format($totalkredit,2)."</td>";
        echo"<td align=right>".number_format($subsalak,2)."</td>";
        echo"<td colspan=3></td>";
     echo"</tr>";
}

// total
    $grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
    echo"<tr class=rowtitle>";
        echo"<td align=right colspan=11>GrandTotal</td>";
//        echo"<td align=right style='width:100px;'>".number_format($grandsalwal)."</td>";
        echo"<td align=right>".number_format($grandtotaldebet,2)."</td>";
        echo"<td align=right>".number_format($grandtotalkredit,2)."</td>";
        echo"<td align=right>".number_format($grandsalak,2)."</td>";
        echo"<td colspan=3></td>";
     echo"</tr>";