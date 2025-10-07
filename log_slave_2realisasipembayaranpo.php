<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//require_once('lib/fpdf.php');
//require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$pt = checkPostGet('pt','');
$periode = checkPostGet('periode','');
$periode1 = checkPostGet('periode1','');
$supplier = checkPostGet('supplier','');
$kelompok = checkPostGet('kelompok','');
$namasupplier = checkPostGet('namasupplier','');

//
if(($periode=='')||($periode1==''))
{
    exit("Error: ".$_SESSION['lang']['silakanisi']." : ".$_SESSION['lang']['periode']);
}

// po
$str="select * from ".$dbname.".log_po_vw
    where nopo!='' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' 
        and nopo like '%".$pt."%' 
        and kodesupplier like '%".$supplier."%' 
        and kodebarang like '".$kelompok."%'
        and namasupplier like '%".$namasupplier."%' 
    order by tanggal,nopo,kodebarang";

$que=mysql_query($str) or die(mysql_error($conn));
while($row=mysql_fetch_assoc($que))
{
    $key=strtoupper($row['nopo']);
    $adatagihan[$key]=0;
    $datapo[$key][$row['kodebarang']]['nopo']=$key;    
    $datapo[$key][$row['kodebarang']]['kodebarang']=$row['kodebarang'];    
    $datapo[$key][$row['kodebarang']]['namabarang']=$row['namabarang'];    
    if($proses=='excel'){
        $datapo[$key][$row['kodebarang']]['tanggal']=$row['tanggal'];            
    }else{
        $datapo[$key][$row['kodebarang']]['tanggal']=tanggalnormal($row['tanggal']);    
    }
    $datapo[$key][$row['kodebarang']]['pesan']=number_format($row['jumlahpesan']);    
    $datapo[$key][$row['kodebarang']]['matauang']=$row['matauang'];    
    $datapo[$key][$row['kodebarang']]['kurs']=number_format($row['kurs']);    
    $datapo[$key][$row['kodebarang']]['harga']=number_format($row['jumlahpesan']*$row['hargasatuan']);    
    $datapo[$key][$row['kodebarang']]['namasupplier']=$row['namasupplier'];    
} 

// bapb
$str="select * from ".$dbname.".log_transaksi_vw
    where nopo!='' and nopo like '%".$pt."%' and idsupplier like '%".$supplier."%' and kodebarang like '".$kelompok."%'";
$que=mysql_query($str) or die(mysql_error($conn));
while($row=mysql_fetch_assoc($que))
{    
    $key=strtoupper($row['nopo']);
	setIt($databa[$key][$row['kodebarang']]['notransaksi'],'');
	setIt($databa[$key][$row['kodebarang']]['tanggal'],'');
	setIt($databa[$key][$row['kodebarang']]['jumlah'],'');
	setIt($databa[$key][$row['kodebarang']]['hartot'],'');
    $databa[$key][$row['kodebarang']]['notransaksi'].=$row['notransaksi'].'<br>';    
    if($proses=='excel'){
        $databa[$key][$row['kodebarang']]['tanggal'].=$row['tanggal'].'<br>';    
    }else{
        $databa[$key][$row['kodebarang']]['tanggal'].=tanggalnormal($row['tanggal']).'<br>';    
    }
    if($row['tipetransaksi']==6){ // balikin barang
        $databa[$key][$row['kodebarang']]['jumlah'].='-'.number_format($row['jumlah']).'<br>';    
        $databa[$key][$row['kodebarang']]['hartot'].='-'.number_format($row['hartot']).'<br>';            
    }else{
        $databa[$key][$row['kodebarang']]['jumlah'].=number_format($row['jumlah']).'<br>';    
        $databa[$key][$row['kodebarang']]['hartot'].=number_format($row['hartot']).'<br>';            
    }
}

// tagihan
$str="select * from ".$dbname.".keu_tagihanht
    where nopo!='' and ((nopo like '%".substr($periode,0,4)."%/".$pt."%') or (nopo like '%".substr($periode1,0,4)."%/".$pt."%')) and kodesupplier like '%".$supplier."%'";
$que=mysql_query($str) or die(mysql_error($conn));
while($row=mysql_fetch_assoc($que))
{
    $key=strtoupper($row['nopo']);
    $adatagihan[$key]=1;
    $datata[$key][$row['noinvoice']]['noinvoice']=$row['noinvoice'];    
    if($proses=='excel'){
        $datata[$key][$row['noinvoice']]['tanggal']=$row['tanggal'];    
        $datata[$key][$row['noinvoice']]['jatuhtempo']=$row['jatuhtempo'];    
    }else{
        $datata[$key][$row['noinvoice']]['tanggal']=tanggalnormal($row['tanggal']);    
        $datata[$key][$row['noinvoice']]['jatuhtempo']=tanggalnormal($row['jatuhtempo']);    
    }
    $datata[$key][$row['noinvoice']]['nilaiinvoice']=number_format(($row['nilaiinvoice']+$row['nilaippn'])*$row['kurs']);    
}

// kasbank
$str="select a.nodok, a.keterangan1, a.notransaksi, a.tipetransaksi, sum(a.jumlah) as jumlah, b.tanggal from ".$dbname.".keu_kasbankdt a
    left join ".$dbname.".keu_kasbankht b on a.notransaksi = b.notransaksi
    where ((a.nodok like '%".substr($periode,0,4)."%/".$pt."%') or (a.nodok like '%".substr($periode1,0,4)."%/".$pt."%'))
    group by a.nodok, a.keterangan1, a.notransaksi, a.tipetransaksi, b.tanggal";
$que=mysql_query($str) or die(mysql_error($conn));
while($row=mysql_fetch_assoc($que))
{
    $key=strtoupper($row['nodok']);
	setIt($dataks[$key][$row['keterangan1']]['notransaksi'],'');
	setIt($dataks[$key][$row['keterangan1']]['keterangan1'],'');
	setIt($dataks[$key][$row['keterangan1']]['jumlah'],'');
	setIt($dataks[$key][$row['keterangan1']]['tanggal'],'');
    $dataks[$key][$row['keterangan1']]['notransaksi'].=$row['notransaksi'].'<br>';    
    $dataks[$key][$row['keterangan1']]['keterangan1'].=$row['keterangan1'].'<br>';    
    if($row['tipetransaksi']=='K'){
        $dataks[$key][$row['keterangan1']]['jumlah'].=number_format($row['jumlah']).'<br>'; 
    }else{
        $dataks[$key][$row['keterangan1']]['jumlah'].='-'.number_format($row['jumlah']).'<br>'; 
    }
    if($proses=='excel'){
        $dataks[$key][$row['keterangan1']]['tanggal'].=$row['tanggal'].'<br>';    
    }else{
        $dataks[$key][$row['keterangan1']]['tanggal'].=tanggalnormal($row['tanggal']).'<br>';    
    }
}

//echo "<pre>";
//print_r($adatagihan);
//echo "</pre>";
//exit;

$brsdt= isset($datapo)? count($datapo): 0;
$brdr=0;
$bgcoloraja='';
//
if($proses=='excel')
{
      $bgcoloraja="bgcolor=#DEDEDE ";
      $brdr=1;
}

/*
INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`, `TH`) VALUES ('invoice', 'Tagihan', 'global', NULL, 'Tagihan', 'Invoice', 'Invoice');
INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`, `TH`) VALUES ('po', 'PO', 'global', NULL, 'PO', 'PO', 'PO');
*/

$tab="<table cellspacing=1 cellpadding=1 border=".$brdr." class=sortable>
<thead class=rowheader>";
$tab.="<tr>";
$tab.="<td ".$bgcoloraja." colspan=9 align=center>".$_SESSION['lang']['po']."</td>";
$tab.="<td ".$bgcoloraja." colspan=4 align=center>".$_SESSION['lang']['bapb']."</td>";
$tab.="<td ".$bgcoloraja." colspan=4 align=center>".$_SESSION['lang']['tagihan']."</td>";
$tab.="<td ".$bgcoloraja." colspan=3 align=center>".$_SESSION['lang']['pembayaran']."</td>";
$tab.="</tr>";
$tab.="<tr>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['nopo']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['kodebarang']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['namabarang']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['jmlhPesan']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['matauang']." PO</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['kurs']." </td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['harga']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['namasupplier']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['notransaksi']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['jumlah']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['harga']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['noinvoice']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['jatuhtempo']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilaiinvoice']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['notransaksi']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['jumlah']."</td>";
$tab.="</tr></thead><tbody>";
        
        #==================
     #   po $datapo[$key][$row['kodebarang']]['kodebarang']
     #   bapp $databa[$row['nopo']][$row['kodebarang']]['notransaksi']
     #   tagihan $datata[$key][$row['noinvoice']]['tanggal']
     #   kas $dataks[$key][$row['notransaksi']]['jumlah']
// $tab="";
// print_r($datata);exit();
if(!empty($datapo))foreach($datapo as $nPO =>$vv){
    foreach($vv as $kBarang =>$yy){
		setIt($datapo[$nPO][$kBarang]['namasupplier'],'');
		setIt($databa[$nPO][$kBarang]['notransaksi'],'');
		setIt($databa[$nPO][$kBarang]['tanggal'],'');
		setIt($databa[$nPO][$kBarang]['jumlah'],'');
		setIt($databa[$nPO][$kBarang]['hartot'],'');
        $tab.=""; 
        $tab.="<tr class=rowcontent>";
        $tab.="<td>".$datapo[$nPO][$kBarang]['nopo']."</td>";
        $tab.="<td align=center>".$datapo[$nPO][$kBarang]['kodebarang']."</td>";
        $tab.="<td>".$datapo[$nPO][$kBarang]['namabarang']."</td>";
        $tab.="<td>".$datapo[$nPO][$kBarang]['tanggal']."</td>";
        $tab.="<td align=right>".$datapo[$nPO][$kBarang]['pesan']."</td>";
        $tab.="<td align=center>".$datapo[$nPO][$kBarang]['matauang']."</td>";
        $tab.="<td align=right>".$datapo[$nPO][$kBarang]['kurs']."</td>";
        $tab.="<td align=right>".$datapo[$nPO][$kBarang]['harga']."</td>";
        $tab.="<td>".$datapo[$nPO][$kBarang]['namasupplier']."</td>";
        $tab.="<td>".$databa[$nPO][$kBarang]['notransaksi']."</td>";
        $tab.="<td align=center>".$databa[$nPO][$kBarang]['tanggal']."</td>";
        $tab.="<td align=right>".$databa[$nPO][$kBarang]['jumlah']."</td>";
        $tab.="<td align=right>".$databa[$nPO][$kBarang]['hartot']."</td>";            
        $tatag=false;
        if($adatagihan[$nPO]==1){ // ada invoice
            foreach($datata[$nPO] as $nInv =>$zz){ // tagihan
				setIt($dataks[$nPO][$nInv]['notransaksi'],'');
				setIt($dataks[$nPO][$nInv]['tanggal'],'');
				setIt($dataks[$nPO][$nInv]['jumlah'],'');
                if($tatag==false){
					$tab.="<td>".$datata[$nPO][$nInv]['noinvoice']."</td>";
                    $tab.="<td align=center>".$datata[$nPO][$nInv]['tanggal']."</td>";
                    $tab.="<td align=center>".$datata[$nPO][$nInv]['jatuhtempo']."</td>";
                    $tab.="<td align=right>".$datata[$nPO][$nInv]['nilaiinvoice']."</td>";                
                    $tab.="<td>".$dataks[$nPO][$nInv]['notransaksi']."</td>";
                    $tab.="<td align=center>".$dataks[$nPO][$nInv]['tanggal']."</td>";
                    $tab.="<td align=right>".$dataks[$nPO][$nInv]['jumlah']."</td>";
                    $tatag=true;
    //                $kakas=false;
    //                foreach ($dataks[$nPO] as $nKas => $xx){ // kasbank
    //                    $tab.="<td>".$dataks[$nPO][$nInv]['notransaksi']."</td>";
    //                    $tab.="<td>".$dataks[$nPO][$nInv]['jumlah']."</td>";
    //                } // foreach dataks
                }else{
                    $tab.="</tr><tr class=rowcontent>";
                    $tab.="<td>".$datapo[$nPO][$kBarang]['nopo']."</td>";
                    $tab.="<td align=center>".$datapo[$nPO][$kBarang]['kodebarang']."</td>";
                    $tab.="<td>".$datapo[$nPO][$kBarang]['namabarang']."</td>";
                    $tab.="<td align=center>".$datapo[$nPO][$kBarang]['tanggal']."</td>";
                    $tab.="<td align=right>".$datapo[$nPO][$kBarang]['pesan']."</td>";
                    $tab.="<td align=center>".$datapo[$nPO][$kBarang]['matauang']."</td>";
                    $tab.="<td align=right>".$datapo[$nPO][$kBarang]['kurs']."</td>";
                    $tab.="<td align=right>".$datapo[$nPO][$kBarang]['harga']."</td>";
                    $tab.="<td>".$datapo[$nPO][$kBarang]['namasupplier']."</td>";
                    $tab.="<td>".$databa[$nPO][$kBarang]['notransaksi']."</td>";
                    $tab.="<td align=center>".$databa[$nPO][$kBarang]['tanggal']."</td>";
                    $tab.="<td align=right>".$databa[$nPO][$kBarang]['jumlah']."</td>";
                    $tab.="<td align=right>".$databa[$nPO][$kBarang]['hartot']."</td>";            
                    $tab.="<td>".$datata[$nPO][$nInv]['noinvoice']."</td>";
                    $tab.="<td align=center>".$datata[$nPO][$nInv]['tanggal']."</td>";
                    $tab.="<td align=center>".$datata[$nPO][$nInv]['jatuhtempo']."</td>";
                    $tab.="<td align=right>".$datata[$nPO][$nInv]['nilaiinvoice']."</td>";                
                    $tab.="<td>".$dataks[$nPO][$nInv]['notransaksi']."</td>";
                    $tab.="<td align=center>".$dataks[$nPO][$nInv]['tanggal']."</td>";
                    $tab.="<td align=right>".$dataks[$nPO][$nInv]['jumlah']."</td>";
                }                            

            } // foreach datata
        }else{ // end if ada invoice, ga ada invoice
            $tab.="<td></td>";                
            $tab.="<td></td>";                
            $tab.="<td></td>";                
            $tab.="<td></td>";                
            $tab.="<td></td>";                
            $tab.="<td></td>";                
            $tab.="<td></td>";                
        } // end of invoice            
        $tab.="</tr>";
    } // foreach barang
} // foreach po
        
$tab.="</tbody></table>";
       
switch($proses)
{
	case'preview':
	echo $tab;
	break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="realisasipembayaranpo_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";	
	break;
	
	default:
	break;
}
      
?>