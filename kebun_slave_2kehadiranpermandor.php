<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=$_GET['proses'];
$lokasi=$_SESSION['empl']['lokasitugas'];
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');
$mandor=checkPostGet('mandor','');
$tanggal=checkPostGet('tanggal','');
$tanggal2=checkPostGet('tanggal2','');

$tanggal=tanggalsystem($tanggal); $tanggal=substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2);
$tanggal2=tanggalsystem($tanggal2); $tanggal2=substr($tanggal2,0,4).'-'.substr($tanggal2,4,2).'-'.substr($tanggal2,6,2);

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kebun==''){
        echo"Error: Kebun tidak boleh kosong."; exit;
    }
    if(($tanggal=='--' || $tanggal2=='--')){
        echo"Error: Periode tanggal harus diisi."; exit;
    }
}

$stream="";
if ($proses=='excel' or $proses=='preview')
{
    $border=0;
    if($proses=='excel')$border=1;

    if(substr($tanggal,0,7)!=substr($tanggal2,0,7)){
        exit("error: Hanya bisa menampilkan laporan dalam periode yang sama");
    }
    
	$sMan="select a.nikmandor, b.namakaryawan from ".$dbname.".kebun_aktifitas a
			left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid
			where a.kodeorg = '".$kebun."' and b.subbagian like '%".$divisi."%' and a.nikmandor != '' and a.tanggal between '".$tanggal."' and '".$tanggal2."'
			group by a.nikmandor
			order by b.namakaryawan";
			// exit("error: ".$sMan);
	$qMan=mysql_query($sMan) or die(mysql_error($conn));
	while($rMan=mysql_fetch_assoc($qMan))
	{
		$namamandor[$rMan['nikmandor']]=$rMan['namakaryawan'];
	}
	
	$str="select a.*, b.*, c.namakaryawan from ".$dbname.".kebun_kehadiran a
        left join ".$dbname.".kebun_aktifitas b on a.notransaksi = b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid
        where  b.kodeorg like '".$kebun."%' and c.subbagian like '".$divisi."%' and b.nikmandor like '".$mandor."%' and b.nikmandor != '' and b.tanggal between '".$tanggal."' and '".$tanggal2."'       
        ";
   // exit("error: ".$str);
	$res=mysql_query($str);
	$stream.="<table cellspacing='1' border='".$border."' class='sortable'>
	<thead>
	<tr class=rowheader>
        <td>".$_SESSION['lang']['nomor']."</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>    
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>".$_SESSION['lang']['namakaryawan']."</td>
		<td>".$_SESSION['lang']['jhk']."</td>
		<td>".$_SESSION['lang']['umr']."</td>
		<td>".$_SESSION['lang']['insentif']."</td>            
	</tr></thead>
	<tbody>";
        $no=$jhk=$umr=$insentif=0;
        while($bar=mysql_fetch_object($res))
        {
            $dzda[$bar->nikmandor]=$bar->nikmandor;
            
			setIt($dzdata[$bar->nikmandor]['jhk'],0);
			setIt($dzdata[$bar->nikmandor]['umr'],0);
			setIt($dzdata[$bar->nikmandor]['insentif'],0);
            $dzdata[$bar->nikmandor]['mandor']=$bar->nikmandor;
            $dzdata[$bar->nikmandor]['jhk']+=$bar->jhk;
            $dzdata[$bar->nikmandor]['umr']+=$bar->umr;
            $dzdata[$bar->nikmandor]['insentif']+=$bar->insentif;
            
            $niknotransaksi=$bar->nik.$bar->notransaksi;
            
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['notransaksi']=$bar->notransaksi;
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['tanggal']=$bar->tanggal;
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['namakaryawan']=$bar->namakaryawan;
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['jhk']=$bar->jhk;
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['umr']=$bar->umr;
            $dzdatadetail[$bar->nikmandor][$niknotransaksi]['insentif']=$bar->insentif;
            
            $jhk+=$bar->jhk;
            $umr+=$bar->umr;
            $insentif+=$bar->insentif;
        }   
		
		// print_r($dzda);
        
        if(!empty($dzda))foreach($dzda as $datanya){
            $stream.="
            <tr class=rowcontent>
            <td colspan=4>".$namamandor[$dzdata[$datanya]['mandor']]."</td>
            <td align=right>".number_format($dzdata[$datanya]['jhk'],2)."</td>
            <td align=right>".number_format($dzdata[$datanya]['umr'])."</td>
            <td align=right>".number_format($dzdata[$datanya]['insentif'])."</td>
            </tr>";

            $no=0;
            if(!empty($dzdatadetail[$datanya]))foreach($dzdatadetail[$datanya] as $datadetailnya){
                $no+=1;
                $stream.="<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$datadetailnya['notransaksi']."</td>    
                <td>".tanggalnormal($datadetailnya['tanggal'])."</td>
                <td>".$datadetailnya['namakaryawan']."</td>
                <td align=right>".number_format($datadetailnya['jhk'],2)."</td>
                <td align=right>".number_format($datadetailnya['umr'])."</td>
                <td align=right>".number_format($datadetailnya['insentif'])."</td>     
                </tr>";                
            }
        }        
            
	$stream.="<tr class=rowcontent>
	<td colspan=4>Total</td>
	<td align=right>".number_format($jhk,2)."</td>
	<td align=right>".number_format($umr)."</td>
	<td align=right>".number_format($insentif)."</td>
        </tbody></table>";
}  
switch($proses)
{
    case'preview':
        echo $stream;    
    break;
    case 'excel':
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHms");
        $nop_="KehadiranperMandor".$kebun.$mandor."-".$tanggal."_".date('YmdHis');
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $stream);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";            
    break;    
}

?>