<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=$_GET['proses'];
$unit		= checkPostGet('unit','');
$tglAkhir	= checkPostGet('tglAkhir','');
$tglAwal	= checkPostGet('tglAwal','');
$whr="kodekelompok='K001'";
$optNamkont=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',$whr );

if($proses=='excel'){
	$border = 1;
}else{
	$border = 0;
}

$stream="
       <table class=sortable cellspacing=1 border=".$border.">
             <thead>
                    <tr class=rowheader>
                       <td>No.</td>
                       <td>".$_SESSION['lang']['nospk']."</td>
                       <td>".$_SESSION['lang']['blok']." on ".$_SESSION['lang']['kontrak']."</td>
                       <td>".$_SESSION['lang']['kontraktor']."</td>
                       <td>".$_SESSION['lang']['kegiatan']." on ".$_SESSION['lang']['kontrak']."</td>
                       <td>".$_SESSION['lang']['namakegiatan']." on ".$_SESSION['lang']['kontrak']."</td>                       
                       <td>".$_SESSION['lang']['jhk']." on ".$_SESSION['lang']['kontrak']."</td>
                       <td>".$_SESSION['lang']['hasilkerjad']." on ".$_SESSION['lang']['kontrak']."</td>
                       <td>".$_SESSION['lang']['satuan']."</td>
                       <td>".$_SESSION['lang']['jumlah']." on ".$_SESSION['lang']['kontrak']."</td>
                       <td>".$_SESSION['lang']['kegiatan']." on ".$_SESSION['lang']['realisasi']."</td>
                       <td>".$_SESSION['lang']['tanggal']."</td>
                       <td>".$_SESSION['lang']['hasilkerjad']." on ".$_SESSION['lang']['realisasi']."</td>
                       <td>".$_SESSION['lang']['jhk']." on ".$_SESSION['lang']['realisasi']."</td>
                       <td>".$_SESSION['lang']['jumlah']." on ".$_SESSION['lang']['realisasi']."</td>
                       <td>".$_SESSION['lang']['blok']." on ".$_SESSION['lang']['realisasi']."</td>

                     </tr>  
                 </thead>
                 <tbody>";
$str="SELECT a.notransaksi, a.kodeblok as blokspk ,a.kodekegiatan as kegspk, a.hk as hkspk, a.hasilkerjajumlah as hasilspk, a.satuan, a.jumlahrp as rpspk,
       b.kodekegiatan as kegrealisasi, b.tanggal, b.hasilkerjarealisasi as hasilrealisasi, b.hkrealisasi, b.jumlahrealisasi as rprealisasi,
       b.kodeblok as blokrealisasi,c.namakegiatan
 FROM ".$dbname.".log_spkdt a left join ".$dbname.".log_baspk b on a.notransaksi=b.notransaksi and a.kodekegiatan=b.kodekegiatan
 left join ".$dbname.".setup_kegiatan c on a.kodekegiatan=c.kodekegiatan
 where a.kodeblok like '".$unit."%' and tanggal between '".tanggalsystem($tglAwal)."' and '".tanggalsystem($tglAkhir)."'    
order by a.notransaksi,a.kodeblok,b.tanggal";
//echo $str;
$res=mysql_query($str);
$no=0;
$oldspk='';
 $kgr=0;
 $hsr=0;
 $hkr=0;
 $rpr=0;
 
 $totalOnKontrak = 0;
 $totalOnRealisasi = 0;
while($bar=mysql_fetch_object($res))
{
    $no+=1;
         $kgr+=$bar->kegrealisasi;
         $hsr+=$bar->hasilrealisasi;
         $hkr+=$bar->hkrealisasi;
         $rpr+=$bar->rprealisasi;
$sKontak="select distinct koderekanan from ".$dbname.".log_spkht where notransaksi='".$bar->notransaksi."'";
$qKontrak=mysql_query($sKontak) or die(mysql_error($conn));
$rKontrak=mysql_fetch_assoc($qKontrak);
    $newspk=$bar->notransaksi.$bar->blokspk.$bar->kegspk;
    if($oldspk==$newspk){
    $stream.="<tr class=rowcontent>
                       <td>".$no."</td>
                       <td>".$bar->notransaksi."</td>
                       <td>".$bar->blokspk."</td>
                       <td>".$optNamkont[$rKontrak['koderekanan']]."</td>
                       <td>".$bar->kegspk."</td>
                       <td>-</td>
                       <td>-</td>
                       <td>-</td>
                       <td>-</td>
                       <td>-</td>
                       <td align=right>".$bar->kegrealisasi."</td>
                       <td>".tanggalnormal($bar->tanggal)."</td>
                       <td align=right>".number_format($bar->hasilrealisasi,2)."</td>
                       <td align=right>".number_format($bar->hkrealisasi,2)."</td>
                       <td align=right>".number_format($bar->rprealisasi,2)."</td>
                       <td>".$bar->blokrealisasi."</td>
                     </tr>";
					$totalOnRealisasi += $bar->rprealisasi;
    }
    else{    

        $stream.="<tr class=rowcontent>
                       <td>".$no."</td>
                       <td>".$bar->notransaksi."</td>
                       <td>".$bar->blokspk."</td>
                       <td>".$optNamkont[$rKontrak['koderekanan']]."</td>
                       <td>".$bar->kegspk."</td>
                       <td>".$bar->namakegiatan."</td>                           
                       <td align=right>".number_format($bar->hkspk,2)."</td>
                       <td align=right>".number_format($bar->hasilspk,2)."</td>
                       <td>".$bar->satuan."</td>
                       <td align=right>".number_format($bar->rpspk,2)."</td>
                       <td align=right>".$bar->kegrealisasi."</td>
                       <td>".tanggalnormal($bar->tanggal)."</td>
                       <td align=right>".number_format($bar->hasilrealisasi,2)."</td>
                       <td align=right>".number_format($bar->hkrealisasi,2)."</td>
                       <td align=right>".number_format($bar->rprealisasi,2)."</td>
                       <td>".$bar->blokrealisasi."</td>
                     </tr>";
					 $totalOnKontrak += $bar->rpspk;
					 $totalOnRealisasi += $bar->rprealisasi;
    }
    $oldspk=$newspk;

}
$stream.="<tr class=rowcontent>
			<td colspan=9 style='text-align:center; font-weight:bold'>".$_SESSION['lang']['total']."</td>
			<td style='text-align:right; font-weight:bold''>".number_format($totalOnKontrak,2)."</td>
			<td colspan=4></td>
			<td style='text-align:right; font-weight:bold''>".number_format($totalOnRealisasi,2)."<td>
		</tr>";
$stream.="</tbody>
                 <tfoot>
                 </tfoot>		 
           </table>";

switch ($proses){
    case 'html':
        echo  $stream;
        break;
   case 'excel':
        $nop_="RealisasiSPK_".$unit."_".tanggalsystem($tglAwal)."_".tanggalsystem($tglAkhir);
        $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $stream);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";	
        break;

        default:
        break;
}
?>