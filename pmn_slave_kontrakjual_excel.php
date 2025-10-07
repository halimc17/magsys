<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$nokontrak = checkPostGet('nokontrak','');


$str="select * from ".$dbname.".pmn_kontrakjual  where nokontrak='".$nokontrak."' ";
//echo $str;exit();
$res=mysql_query($str);
$bar=mysql_fetch_assoc($res);
$kodePt=$bar['kodept'];
$kdBrg=$bar['kodebarang'];
$tlgKontrk=tanggalnormal($bar['tanggalkontrak']);
$kdCust=$bar['koderekanan'];

//echo $posting; exit();	
//ambil nama pt
   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodePt."'"; 
   $res1=mysql_query($str1);
   while($bar1=mysql_fetch_object($res1))
   {
         $nama=$bar1->namaorganisasi;
         $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
         $telp=$bar1->telepon;	
         $wilKota=$bar1->wilayahkota;			 
   }    

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$kdBrg."'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
$rBrg=mysql_fetch_assoc($qBrg);
$nmBrg=$rBrg['namabarang'];

$nmdt=explode(".",$nama);

$whrpt="kodeorg='".$kodePt."'";
$almtPt=makeOption($dbname,'setup_org_npwp','kodeorg,alamatdomisili',$whrpt);
$npwpPt=makeOption($dbname,'setup_org_npwp','kodeorg,npwp',$whrpt);


$whrpemb="kodecustomer='".$kdCust."'";
$optNm=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrpemb);
$optNmAlmt=makeOption($dbname,'pmn_4customer','kodecustomer,alamat',$whrpemb);
$optNpwp=makeOption($dbname,'pmn_4customer','kodecustomer,npwp',$whrpemb);

$nmdt2=explode(".",$optNm[$kdCust]);
		if(count($nmdt2)==0){
			$nmdt2=$optNm[$kdCust];
		}
                
$whrKomo="kodecustomer='".$kdCust."' and kodebarang='".$kdBrg."'";
$optKomo=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');    


$whrmt="kode='".$bar['matauang']."'";
$optMtSim=makeOption($dbname,'setup_matauang','kode,simbol',$whrmt);
$optMtuang=makeOption($dbname,'setup_matauang','kode,matauang',$whrmt);
$arrStatPPn=array(0=>"Exclude",1=>"Include");


$whrfrn="id_franco='".$bar['franco']."'";
$optFrnc=makeOption($dbname,'pmn_5franco','id_franco,franco_name',$whrfrn);
$optFrncAlamat=makeOption($dbname,'pmn_5franco','id_franco,alamat',$whrfrn);
$arrX=array('franco'=>'Franco','loco'=>'Loco','fob'=>'FOB');

$iFranco=" select * from ".$dbname.".pmn_5franco where id_franco='".$bar['franco']."' ";
$nFranco=  mysql_query($iFranco) or die (mysql_error($conn));
$dFranco=  mysql_fetch_assoc($nFranco);
$francoList=$arrX[$dFranco['penjualan']].' '.$dFranco['franco_name'].' '.$dFranco['alamat'];

$arrRom=array("0"=>"I","1"=>"II","2"=>"III","3"=>"IV");
for($asd=3;$asd>=0;$asd--){
    if($asd!=0){
        if($bar['kuantitaskirim'.$asd]!=0){
            $kata[$asd]="Tahap ".$arrRom[$asd]." sebanyak ".number_format($bar['kuantitaskirim'.$asd],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim'.$asd])." s.d ".tanggalnormal($bar['sdtanggal'.$asd])."\n";
        }
    }else{
        if(count($kata)!=0){
            $kata[$asd]="Tahap ".$arrRom[$asd]." sebanyak ".number_format($bar['kuantitaskirim'],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim'])." s.d ".tanggalnormal($bar['sdtanggal'])."\n";
        }else{
            $kata[$asd]="Pengiriman sebanyak ".number_format($bar['kuantitaskirim'],0)." ".$bar['satuan']." diserahkan pada tanggal ".tanggalnormal($bar['tanggalkirim'])." s.d ".tanggalnormal($bar['sdtanggal'])."";
        }
    }
}


$ffaData=number_format($bar['ffa'],2).' ';
$dobiData=number_format($bar['dobi'],2).' ';
$mdaniData=number_format($bar['mdani'],2).' ';
$moistData=number_format($bar['moist'],2).' ';
$dirtData=number_format($bar['dirt'],2).' ';

$sTrmn="select distinct * from ".$dbname.".pmn_5terminbayar where kode='".$bar['kdtermin']."'";
$qTrmn=mysql_query($sTrmn) or die(mysql_error($conn));
$rTrmn=mysql_fetch_assoc($qTrmn);

//$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$bar['kodept']."' and noakun='".$bar['rekening']."'";
$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$bar['kodept']."'";
$qTrmn2=mysql_query($sTrmn2) or die(mysql_error($conn));
$rTrmn2=mysql_fetch_assoc($qTrmn2);



$bulan=substr($bar['tglpembayarpertama'],5,2);
$nmBulan=numToMonth($bulan,'I','long');

$thn=substr($bar['tglpembayarpertama'],0,4);
$tglnya=substr($bar['tglpembayarpertama'],8,2);

//  echo $tglnya;
$listTgl=$tglnya.' '.$nmBulan.' '.$thn;

$ktTermin="".$rTrmn['satu']."% Setelah kontrak ditandatangani selambatnya tanggal ".$listTgl." <br>".$rTrmn['dua']."% Selambatnya 7 (tujuh) hari setelah BA ditandatangani <br><br>";
$ktTermin.="Pembayaran ditransfer ke :<br>";
$ktTermin.="".$nmdt[0].".".ucwords(strtolower($nmdt[1]))."<br>";
$ktTermin.=$rTrmn2['namabank']."<br>Rek : ".$rTrmn2['rekening'];
$nilKontrak=$bar['hargasatuan']*$bar['kuantitaskontrak'];

if($bar['tanggalkontrak']<='2022-03-31'){
    $angkaPpn=" ";
}else{
	$angkaPpn=" 11%";
}

$tglTtd=explode("-",$tlgKontrk);
        
        $tglnya=$tglTtd[0];
        $blnnya=numToMonth($tglTtd[1],$lang='I',$format='long');
        $thnnya=$tglTtd[2];
        
        $tglbenernya=$tglnya.' '.$blnnya.' '.$thnnya;


$nmPt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
                  $nmPtS=explode(".",$nmPt[$bar['kodept']]);        
        
  $jabatanTtd=makeOption($dbname,'pmn_5ttd','nama,jabatan');
                $namaTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,penandatangan');
                $jabTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,jabatan');       
        
switch($proses)
{

    case'excel':
        $stream="<table cellspacing='1' border='0'>
        <tr>
            <td  colspan=7 align=center><u><b>KONTRAK JUAL BELI</b></u></td>
        </tr>    
        <tr>   
            <td  colspan=7 align=center><u><b>".$nokontrak."</b></u></td>    
        </tr>
        <tr>
            <td colspan=2><b>Penjual</b></td>
            <td>:</td>
            <td colspan=5>".$nmdt[0]." ".ucwords(strtolower($nmdt[1]))."</td>
        </tr>
        <tr>
            <td  colspan=3></td>
            <td  colspan=5>".$almtPt[$kodePt]."</td>
        </tr>



        <tr>
            <td colspan=2><b>NPWP Penjual</b></td>
            <td>:</td>
            <td  colspan=5 align=left>".$npwpPt[$kodePt]."</td>
        </tr>

        <tr>
            <td colspan=2><b>Pembeli</b></td>
            <td>:</td>
            <td  colspan=5>".$nmdt2[0]." ".ucwords(strtolower($nmdt2[1]))."</td>
        </tr>
        <tr>
            <td  colspan=3></td>
            <td  colspan=5>".$optNmAlmt[$kdCust]."</td>
        </tr>

        <tr>
            <td colspan=2><b>NPWP Pembeli</b></td>
            <td>:</td>
            <td  colspan=5>".$optNpwp[$kdCust]."</td>
        </tr>

        <tr>
            <td colspan=2><b>Komoditi</b></td>
            <td>:</td>
            <td  colspan=5>".$optKomo[$kdBrg]."</td>
        </tr>
		<tr>
            <td colspan=2><b>Kuantitas</b></td>
            <td>:</td>
            <td  colspan=5>".number_format($bar['kuantitaskontrak'],0)." KG</td>
        </tr>
        <tr>
            <td colspan=2><b>Harga Satuan</b></td>
            <td>:</td>
            <td  colspan=5>".$optMtSim[$bar['matauang']]." ".number_format($bar['hargasatuan'],2)." (".$arrStatPPn[$bar['ppn']]." PPn".$angkaPpn.")</td>
        </tr>
        <tr>
            <td colspan=3></td>
            <td  colspan=5>(".ucfirst($bar['terbilang'])." ".$optMtuang[$bar['matauang']].")</td>
        </tr>




        <tr>
            <td colspan=2><b>Tempat penyerahan</b></td>
            <td>:</td>
            <td  colspan=5>".$francoList."</td>
        </tr>
        <tr>
            <td colspan=2><b>Waktu Penyerahan</b></td>
            <td>:</td>
            <td colspan=5>".$kata[0].$kata[1].$kata[2].$kata[3]."</td>
        </tr>";
        
        $stream.="
            <tr>
            <td colspan=2><b>Kualitas</b></td>
            <td>:</td>";
        
        if($ffaData!=0)
        {
            $stream.="<td>FFA</td>";
            $stream.="<td>:</td>";
            $stream.="<td>".$ffaData." % Max</td>";
            $stream.="</tr>";
        }       
        if($dobiData!=0)
        {  
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Dobi</td>";
            $stream.="<td>:</td>";
            $stream.="<td>".$dobiData." Min</td>";
            $stream.="</tr>";
        }                
        if($mdaniData!=0)
        {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>M & I</td>";
            $stream.="<td>:</td>";
            $stream.="<td>".$mdaniData." % Max</td>";
            $stream.="</tr>";
        }       
        if($moistData!=0)
        {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Moisture</td>";
            $stream.="<td>:</td>";
            $stream.="<td>".$moistData." % Max</td>";
            $stream.="</tr>";
        }      
        if($dirtData!=0)
        {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Impurities</td>";
            $stream.="<td>:</td>";
            $stream.="<td>".$dirtData." % Max</td>";
            $stream.="</tr>";
        }
        
        $stream.="
        
        
        <tr>
            <td colspan=2><b>Cara Pembayaran</b></td>
            <td>:</td>
            <td colspan=5>".$ktTermin."</td>
        </tr>   
        <tr>
            <td colspan=2><b>Nilai Kontrak</b></td>
            <td>:</td>
            <td colspan=5>".$optMtSim[$bar['matauang']]." ".number_format($nilKontrak,0)." (".$arrStatPPn[$bar['ppn']]." PPn".$angkaPpn.")</td>
        </tr>
        <tr>
            <td  colspan=3></td>
            <td  colspan=5>(".ucfirst(terbilang($nilKontrak,2))." ".$optMtuang[$bar['matauang']].")</td>
        </tr>";
        
        
        for($i=1;$i<=20;$i++)
        {
            $stream.="<tr><td></td></tr>";
        }
        
        
        $stream.="
        <tr>
            <td colspan=2><b>Catatan Lain</b></td>
            <td>:</td>
            <td colspan=5>".$bar['toleransi']."</td>
        </tr>
         <tr>
            <td  colspan=3></td>
            <td  colspan=5>".$bar['catatanlain']."</td>
        </tr>
        
        <tr>
            <td colspan=2><b></b></td>
            <td></td>
            <td colspan=5></td>
        </tr>
         <tr>
            <td colspan=2><b></b></td>
            <td></td>
            <td colspan=5></td>
        </tr>
        
        <tr>
            <td colspan=2></td>
            <td colspan=4 align=left>".ucwords(strtolower('Jakarta')).", ".$tglbenernya."</td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=left>Penjual,</td>
            <td align=right>Pembeli,</td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center><b>".$nmPtS[0].".".ucwords(strtolower($nmPtS[1]))."</td>
            <td colspan=2 align=center><b>".$nmdt2[0].".".ucwords(strtolower($nmdt2[1]))."</td>
        </tr>";

        
        for($i=1;$i<=5;$i++)
        {
            $stream.="<tr><td></td></tr>";
        }
        
        

        $stream.="<tr>
            <td colspan=2></td>
            <td colspan=2 align=left></td>
            <td colspan=2 align=left></td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center><b><u>".ucwords(strtolower($bar['penandatangan']))."</u></td>
            <td colspan=2 align=center><b><u>".ucwords(strtolower($namaTtdBeli[$bar['koderekanan']]))."</u></td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center><b>".ucwords(strtolower($jabatanTtd[$bar['penandatangan']]))."</td>
            <td colspan=2 align=center><b>".ucwords(strtolower($jabTtdBeli[$bar['koderekanan']]))."</td>
        </tr>


       


        </table>";
/* <tr>
            <td colspan=2><b></b></td>
            <td>:</td>
            <td colspan=5></td>
        </tr>*/




    //exit("Error:$nokontrak");
       // $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="KontrakJual";
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        } 
                
 
 
/*if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
}*/
 
        break;
		default:
		break;
	}

?>