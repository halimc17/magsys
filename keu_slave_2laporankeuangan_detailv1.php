<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];
$nourut=$_POST['nourut'];
$tipe=$_POST['tipe'];
$kodelaporan='LK - LABA RUGI V1';
$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];
//involving units
if($unit==''){
	$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}else{
	$where=" ='".$unit."'";
} 
		$st12="select noakun, namaakun, namaakun1
            from ".$dbname.".keu_5akun where level=5";
        $res12=mysql_query($st12);
        while($ba12=mysql_fetch_object($res12))
        {
            if($_SESSION['language']=='ID'){
                $akun[$ba12->noakun]=$ba12->namaakun;}
            else{
                $akun[$ba12->noakun]=$ba12->namaakun1;
            }
        }
		
if($bulan=='01' or $bulan=='1'){
  $bulanlalu=12;
 }else{ 
  $bulanlalu=$bulan-1;
} 
if($bulanlalu<10)$bulanlalu='0'.$bulanlalu; // bulan lalu dia digit
$periodelalu=$tahun.'-'.$bulanlalu; // periode lalu
if($bulan==1)$periodelalu=$tahunlalu.'-12';

$desemberlalu=$tahunlalu.'-12'; // periode desember tahun lalu

//ambil namapt
$namaptw=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
if($pt==''){
	$optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
	$namapt=$namaptw[$optPt[$unit]];
}else{
	$namapt=$namaptw[$pt];
}

//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' and nourut='".$nourut."'
    order by nourut";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    foreach($qwe as $rty){
        //if((number_format($rty)!=0)){
        if($rty!=0){
            $emaknya[$rty]=$bar->nourut;
            $adaemaknya[$rty]=$rty;
        }
    }
	if($bar->nourut==200004){#biaya tidak langsung kebun
        $dtNoakun=array();
        $sDeplesi="select sum(jumlah) as deplesi,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                where left(tanggal,4)='".$tahun."'  and  noakun='7150201' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."') group by left(tanggal,7)";
        $qDeplesi=mysql_query($sDeplesi) or die(mysql_error($conn));
        while($rDeplesi=mysql_fetch_assoc($qDeplesi)){
            $byDeplesi[$rDeplesi['bln']]=$rDeplesi['deplesi'];
            $dzArr2[$rDeplesi['noakun']][$rDeplesi['bln']]=$rDeplesi['deplesi'];
            $dzArr2[$rDeplesi['noakun']]['sd']+=$rDeplesi['deplesi'];
            $daftar[$rDeplesi['noakun']]=$rDeplesi['noakun'];
        }

        $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
        $qUnitKbn=mysql_query($sUnitKbn) or die(mysql_error($conn));
        while($rowUnitKbn=mysql_fetch_assoc($qUnitKbn)){
            $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
        }

        #total unit pabrik
        $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                  where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
        $qUnitPbrk=mysql_query($sUnitPbrk) or die(mysql_error($conn));
        while($rowUnitPbrk=mysql_fetch_assoc($qUnitPbrk)){
            $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
        }
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
            $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
        }
        
        $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                    where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL','TRAKSI') and induk='".$pt."')  and noakun!='7199999' 
                    group by noakun,left(tanggal,7)";
        $qOverHead2=mysql_query($sOverHead2) or die(mysql_error($conn));
        while($rOverHead2=mysql_fetch_assoc($qOverHead2)){
            $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
            $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
        }
        foreach($dtNoakun as $lstNoakun){
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                $dzArr2[$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                $dzArr2[$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                $daftar[$lstNoakun]=$lstNoakun;
            }
        }
		if($unit==''){
			$where="";
			$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
		}else{
			$cekorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			if($cekorg[$unit]!='KEBUN'){
				$where="";
			}
		}
	}elseif($bar->nourut==300003){#biaya tidak langsung pabrik
        $dtNoakun=array();
        $byOverHead3=array();
        $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
        $qUnitKbn=mysql_query($sUnitKbn) or die(mysql_error($conn));
        while($rowUnitKbn=mysql_fetch_assoc($qUnitKbn)){
            $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
        }

        #total unit pabrik
        $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                  where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
        $qUnitPbrk=mysql_query($sUnitPbrk) or die(mysql_error($conn));
        while($rowUnitPbrk=mysql_fetch_assoc($qUnitPbrk)){
            $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
        }
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
            $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
        }
         
        $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                    where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL','TRAKSI') and induk='".$pt."')  and noakun!='7199999' 
                    group by noakun,left(tanggal,7)";
        $qOverHead2=mysql_query($sOverHead2) or die(mysql_error($conn));
        while($rOverHead2=mysql_fetch_assoc($qOverHead2)){
            $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
            $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
        }
        foreach($dtNoakun as $lstNoakun){
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                $dzArr2[$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                $dzArr2[$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                $daftar[$lstNoakun]=$lstNoakun;
            }
        }
		if($unit==''){
			$where="";
			$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
		}else{
			$cekorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			if($cekorg[$unit]!='PABRIK'){
				$where="";
			}
		}
	}elseif($bar->nourut==200005){#depresiasi kebun 
            $dtNoakun=array();   
            $byOverHead=array();
            $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
            $qUnitKbn=mysql_query($sUnitKbn) or die(mysql_error($conn));
            while($rowUnitKbn=mysql_fetch_assoc($qUnitKbn)){
                $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
            }

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
            $qUnitPbrk=mysql_query($sUnitPbrk) or die(mysql_error($conn));
            while($rowUnitPbrk=mysql_fetch_assoc($qUnitPbrk)){
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
            }

            $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."')  
                        group by noakun,left(tanggal,7)";
            $qOverHead2=mysql_query($sOverHead2) or die(mysql_error($conn));
            while($rOverHead2=mysql_fetch_assoc($qOverHead2)){
                $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                    $dzArr2[$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                    $dzArr2[$lstNoakun]['sd']+=($byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii]);
                    $daftar[$lstNoakun]=$lstNoakun;
                }
            }
            if($unit==''){
                $where="";
                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in  ('KEBUN'))";
            }else{
                $whr="kodeorganisasi='".$unit."'";
                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                if(($optTipe[$unit]!='KEBUN')||($optTipe[$unit]!='TRAKSI')){
                    $where="";
                }else{
                    $where=" ='".$unit."'";
                }
            }
    }elseif($bar->nourut==300004){#depresiasi pabrik
            $dtNoakun=array();   
            $byOverHead3=array();
            $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
            $qUnitKbn=mysql_query($sUnitKbn) or die(mysql_error($conn));
            while($rowUnitKbn=mysql_fetch_assoc($qUnitKbn)){
                $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
            }

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
            $qUnitPbrk=mysql_query($sUnitPbrk) or die(mysql_error($conn));
            while($rowUnitPbrk=mysql_fetch_assoc($qUnitPbrk)){
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
            }
             
            $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."')  
                        group by noakun,left(tanggal,7)";
            $qOverHead2=mysql_query($sOverHead2) or die(mysql_error($conn));
            while($rOverHead2=mysql_fetch_assoc($qOverHead2)){
                $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                    $dzArr2[$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                    $dzArr2[$lstNoakun]['sd']+=($byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii]);
                    $daftar[$lstNoakun]=$lstNoakun;
                }
            }
            if($unit==''){
                $where="";
                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in  ('PABRIK'))";
            }else{
                $whr="kodeorganisasi='".$unit."'";
                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                if(($optTipe[$unit]!='PABRIK')||($optTipe[$unit]!='TRAKSI')){
                    $where="";
                }else{
                    $where=" ='".$unit."'";
                }
            }
    }else{
		if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		else $where=" ='".$unit."'";
	}
    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and periode like'".$tahun."%' and kodeorg ".$where."  order by noakun,periode";
	  
//    if($bar->nourut=='211001') {echo $bulan.' '.$st12; exit;}
    $res12=mysql_query($st12);
    while($ba12=mysql_fetch_object($res12)){
		if($bar->nourut<100004){
			$ba12->jumlah=abs($ba12->jumlah);
			$ba12->awal=abs($ba12->awal);
		}
		$daftar[$ba12->noakun]=$ba12->noakun;
		if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
		if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=($ba12->jumlah+$ba12->awal);
        $dzArr2[$ba12->noakun][$ba12->bulan]+=($ba12->jumlah+$ba12->awal);
        if($bulan>=$ba12->bulan){
			if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
			if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
}
        array_multisort($daftar,SORT_ASC);
		$stream="<table class=sortable border=0 cellspacing=0>";
        if(!empty($daftar))foreach($daftar as $akunnya){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td style='width:10px'></td>
                <td style='width:10px'></td>
                <td style='width:500px'>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= $bulanlalu; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$ii],2)."</td>";
                }            
                $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya]['sd'],2)."</td>
                    <td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
        }
        $stream.="</table>";   
	echo $stream;
?>