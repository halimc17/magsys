<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
        $proses=$_POST['proses'];
}
else
{
        $proses=$_GET['proses'];
}
$param=$_POST;

$tgl_cari = 	empty($_POST['tgl_cari'])? (isset($_GET['tgl_cari'])? $_GET['tgl_cari']: ''): $_POST['tgl_cari'];
$tgl_cari2 = 	empty($_POST['tgl_cari2'])? (isset($_GET['tgl_cari2'])? $_GET['tgl_cari2']: ''): $_POST['tgl_cari2'];
$kdUnit = 		empty($_POST['kdUnit2'])? (isset($_GET['kdUnit2'])? $_GET['kdUnit2']: ''): $_POST['kdUnit2'];
$jenisId2 = 	(!isset($_POST['jenisId2']) or $_POST['jenisId2']=='')? (isset($_GET['jenisId2'])? $_GET['jenisId2']: ''): $_POST['jenisId2'];
$cariNopo = 	empty($_POST['cariNopo'])? (isset($_GET['cariNopo'])? $_GET['cariNopo']: ''): $_POST['cariNopo'];
$suppId2 = 		empty($_POST['suppId2'])? (isset($_GET['suppId2'])? $_GET['suppId2']: ''): $_POST['suppId2'];

//$arr2="##tgl_cari##tgl_cari2##jenisId2##kdUnit##suppId2";

$optSupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
$optNmOrg=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
$optKurs=makeOption($dbname, 'log_poht','nopo,kurs');
$unitId=$_SESSION['lang']['all'];
$dktlmpk=$_SESSION['lang']['all'];

    $jenisId2=='0'?$dr="k":$dr="p";
    $whre=" and tipeinvoice='".$dr."'";
    $rhd=" and c.tanggal between '".$tgl_cari."' and  '".$tgl_cari2."'";
    
    $sTagi="select distinct a.noinvoicesupplier,a.uangmuka as uangmuka,a.nopo as nopo,sum(a.nilaiinvoice) as jumlah,
            a.noinvoice as noinvoice,a.kodesupplier as kodesupplier,a.tanggal as tanggal,
            a.jatuhtempo as jatuhtempo,a.tipeinvoice as tipeinvoice,a.matauang as matauang,a.kurs as kurs,
            b.matauang as mt,b.kurs as krs,b.nilaipo as nilaipo, sum(c.nilai) as ppn
            from ".$dbname.".keu_tagihanht a
            left join ".$dbname.".log_poht b on a.nopo=b.nopo
			left join ".$dbname.".keu_tagihandt c on a.noinvoice=c.noinvoice
            where a.posting=1 and a.tanggal between '".$tgl_cari."' and  '".$tgl_cari2."' and a.kodeorg like '%".$kdUnit."%'
            and a.kodesupplier='".$suppId2."' and a.nopo like '%".$cariNopo."%' ".$whre."
            group by a.nopo,a.noinvoice";
    
   // echo $sTagi;
    
//    exit("Error:".$sTagi);
    $qTagi=mysql_query($sTagi) or die(mysql_error($conn));
    while($rTagi=  mysql_fetch_assoc($qTagi))
    {
        if($rTagi['kodesupplier']!=''){
//            if($rTagi['tipeinvoice']=="k"){
//               $optKurs[$rTagi['nopo']]=1;
//            }
        $dtNopo[$rTagi['noinvoice']]=$rTagi['nopo'];
        $dtNoInvSup[$rTagi['noinvoice']]=$rTagi['noinvoicesupplier'];
        $dtNotrans[$rTagi['noinvoice']]=$rTagi['noinvoice'];
        $dtInvoice[$rTagi['noinvoice']]=$rTagi['jumlah']+$rTagi['ppn'];
        $dtTagih[$rTagi['noinvoice']]=$rTagi['nilaipo'];
        $dtSupp[$rTagi['noinvoice']]=$rTagi['kodesupplier'];
        $dtJth[$rTagi['noinvoice']]=$rTagi['jatuhtempo'];
        $dtTglEn[$rTagi['noinvoice']]=$rTagi['tanggal'];
        $mtuang_po[$rTagi['noinvoice']]=$rTagi['mt'];
        $kurs_po[$rTagi['noinvoice']]=$rTagi['krs'];
        $mtuang_tghn[$rTagi['noinvoice']]=$rTagi['matauang'];
        $kurs_tghn[$rTagi['noinvoice']]=$rTagi['kurs'];
        $konversi[$rTagi['noinvoice']]=$rTagi['nilaipo']*$rTagi['krs'];
        $uangmuka[$rTagi['noinvoice']]=$rTagi['uangmuka'];
        }
    }
    
    
    //print_r($uangmuka);
    
  $sByr="select distinct sum(a.jumlah) as jumlah,b.tanggal,a.notransaksi,a.keterangan1,a.kodesupplier as kodesupplier
         from ".$dbname.". keu_kasbankdt a
         left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
         left join ".$dbname.".keu_tagihanht c on c.noinvoice=a.keterangan1
         where c.posting=1 and a.keterangan1!='' ".$rhd."
         and a.tipetransaksi='K' and b.posting=1
         group by a.keterangan1,a.tipetransaksi";
// exit("error: ".$sByr);
$qByr=mysql_query($sByr) or die(mysql_error($conn));
while($rByr=mysql_fetch_assoc($qByr)){
    $penambah[$rByr['keterangan1']]=$rByr['jumlah'];
    $ntrKasBank[$rByr['keterangan1']]=$rByr['notransaksi'];
    $tglKasBank[$rByr['keterangan1']]=$rByr['tanggal'];
}
$sByr2="select distinct sum(a.jumlah) as jumlah,a.keterangan1,a.kodesupplier as kodesupplier
        from ".$dbname.". keu_kasbankdt a
        left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
        left join ".$dbname.".keu_tagihanht c on c.noinvoice=a.keterangan1    
        where c.posting=1 and a.keterangan1!='' ".$rhd."
        and a.tipetransaksi='M' and b.posting=1 
        group by a.keterangan1,a.tipetransaksi";
//exit("error: ".$sByr2);
$qByr2=mysql_query($sByr2) or die(mysql_error($conn));
while($rByr2=mysql_fetch_assoc($qByr2)){
    $pengurang[$rByr2['keterangan1']]=$rByr2['jumlah'];
}

$brdr=0;
$bgcoloraja='';
if($proses=='excelgetDetail2')
{
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $tab= "
    <table>
    <tr><td colspan=15 align=left><b><font size=5>Riwayat Pembayaran</font></b></td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['namasupplier']." : ".$suppId2."</td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['periode']." : ".$tgl_cari." s/d ".$tgl_cari2."</td></tr>
    </table>";
}

        $tab= "<table cellspacing=1 border=".$brdr." class=sortable>
        <thead class=rowheader>
        <tr>
        <td ".$bgcoloraja." rowspan=2 align=center>No.</td>
        <td ".$bgcoloraja." rowspan=2 align=center>".$_SESSION['lang']['noinvoice']."</td>
        <td ".$bgcoloraja." rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
        <td ".$bgcoloraja." rowspan=2 align=center>".$_SESSION['lang']['jatuhtempo']."</td>
        <td ".$bgcoloraja." colspan=8 align=center>".$_SESSION['lang']['tagihan']."</td>
        <td ".$bgcoloraja." colspan=3 align=center>".$_SESSION['lang']['dibayar']."</td>
        <td ".$bgcoloraja." rowspan=2 align=center>".$_SESSION['lang']['sisa']."</td>
        <td ".$bgcoloraja." rowspan=2 align=center>Ost</td></tr>";

        $tab.= "<tr>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['kodesupplier']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['noinvoicesupplier']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['nopo']."/ ".$_SESSION['lang']['nospk']."</td>";        
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['tagihan']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['matauang']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['kurs']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>";        
        $tab.= "<td ".$bgcoloraja." align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['dibayar']."</td>";
        $tab.= "</tr></thead><tbody>";
        if(!empty($dtNotrans)) 
       {
		$aerta=0;
		$tothutang=$totdibayar=$totsisa=0;
        foreach($dtNotrans as $hutang)
        {
            $aerta+=1;
			if(!isset($penambah[$hutang])) $penambah[$hutang]=0;
			if(!isset($pengurang[$hutang])) $pengurang[$hutang]=0;
            $dibayarsmp[$hutang]=$penambah[$hutang]-$pengurang[$hutang];
            $sis[$hutang]=$dtInvoice[$hutang]-$dibayarsmp[$hutang];
            if($jenisId2==1){
               $tab.= "<tr class=rowcontent onclick=masterPDF('log_poht','".$dtNopo[$hutang]."','','log_slave_2pembayaran2cetakPO',event);>";
             
            }
            else{
               $tab.= "<tr class=rowcontent>";
            
            }
            
            
            #tagihan jatuh tempo
            $tglA=substr($dtJth[$hutang],0,4);
            $tglB=substr($dtJth[$hutang],5,2);
            $tglC=substr($dtJth[$hutang],8,2);
            $tgl2=$tglA.$tglB.$tglC;
            
            #pembayaran kasbank
            $tGl1=substr($tglKasBank[$hutang],0,4);
            $tGl2=substr($tglKasBank[$hutang],5,2);
            $tGl3=substr($tglKasBank[$hutang],8,2);
            $tgl1 =$tGl1.$tGl2.$tGl3;
            
            
           
            
            $starttime=strtotime($tgl1);//time();// tanggal sekarang
            $endtime=strtotime($tgl2);//tanggal pembuatan dokumen
            $timediffSecond = abs($endtime-$starttime);
            $base_year = min(date("Y", $tGl1), date("Y", $tglA));
            $diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
            $jmlHari=abs(date("j", $diff) - 1);
            
            
            
            $tab.= "<td>".$aerta."</td>";
            $tab.= "<td>".$hutang."</td>";
            $tab.= "<td>".$dtTglEn[$hutang]."</td>";
            $tab.= "<td>".$dtJth[$hutang]."</td>";
            $tab.= "<td>".$dtSupp[$hutang]."</td>";
            
            
            $tab.= "<td>".$optSupp[$dtSupp[$hutang]]."</td>";
            $tab.= "<td>".$dtNoInvSup[$hutang]."</td>";
            $tab.= "<td>".$dtNopo[$hutang]."</td>";
            $tab.= "<td align=right>".number_format($dtInvoice[$hutang],0)."</td>";
            $tab.= "<td align=right>".number_format($uangmuka[$hutang])."</td>";
            $tab.= "<td>".$mtuang_po[$hutang]."</td>";
            $tab.= "<td align=right>".number_format($kurs_po[$hutang],0)."</td>";
//            $tab.= "<td align=right>".number_format($konversi[$hutang],0)."</td>";
//            $tab.= "<td align=right>".number_format($dtInvoice[$hutang],0)."</td>";
            $tab.= "<td align=center>".(isset($ntrKasBank[$hutang])? $ntrKasBank[$hutang]: '')."</td>";
            $tab.= "<td>".(isset($tglKasBank[$hutang])? $tglKasBank[$hutang]: '')."</td>";
            $tab.= "<td align=right>".number_format($dibayarsmp[$hutang],0)."</td>";            
            $tab.= "<td align=right>".number_format($sis[$hutang],0)."</td>";
            $tab.= "<td align=right>".$jmlHari."</td>";
            $tab.= "</tr>";
            
			$tothutang+=$dtInvoice[$hutang];
            $totdibayar+=$dibayarsmp[$hutang];
            $totsisa+=$sis[$hutang];
        }
       } 
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=8 align=right>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($tothutang,0)."</td>";
//            $tab.="<td align=right>".number_format($totinvoice,0)."</td>";
            $tab.="<td align=right colspan=6>".number_format($totdibayar,0)."</td>";
            $tab.="<td align=right>".number_format($totsisa,0)."</td>";
            $tab.="<td align=right></td>";
            
            
            $tab.= "</tr>";
            
        $tab.= "</tbody></table>";
        if($proses=='preview')
        {
            $tab.= "<button class=mybutton onclick=kembali(1)>".$_SESSION['lang']['back']."</button>";
            $tab.= "<button class=mybutton onclick=zExcelDt(event,'log_slave_2pembayaran2detail.php','".$param['tgl_cari']."','".$param['tgl_cari2']."','".$param['jenisId2']."','".$param['kdUnit2']."','".$param['suppId2']."')>".$_SESSION['lang']['excel']."</button>";
    //        echo "<button onclick=zExcel(event,'log_slave_2pembayaran2detail.php',$arr2) class=mybutton name=preview id=preview>Excel</button>"; $arr2="##tgl_cari##tgl_cari2##jenisId2##kdUnit##suppId2";   
    //        echo $tab;
        }
        
switch($proses)
{
    case 'preview':
        echo $tab;
        break;
    case'excelgetDetail2':
     
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="detailRiwayatPembayaran";
        if(strlen($tab)>0)
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
            if(!fwrite($handle,$tab))
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
        break;

        default:
        break;
}
?>