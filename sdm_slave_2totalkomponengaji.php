<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');





$proses=checkPostGet('proses','');
$kdorg=checkPostGet('kdorg','');
$per1=checkPostGet('per1','');
$per2=checkPostGet('per2','');
$kom=checkPostGet('kom','');
$tipekar=checkPostGet('tipekar','');



$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$opttipekar=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$komponen="";
if($kom!='')
{
    $komponen=" and idkomponen='".$kom."' ";
}

$sorttk="";
if($tipekar!='')
{
    $sorttk=" and tipekaryawan='".$tipekar."'";
}
else
{
    $sorttk=" and tipekaryawan between '1' and '6' ";
}

#bentuk komponen
/*$noKom=$counterKar=$counterGaji="";
$iKom="select distinct idkomponen from ".$dbname.".sdm_gaji where kodeorg='".$kdorg."' "
        . " ".$komponen." ";
$nKom=mysql_query($iKom) or die (mysql_error($conn));
while($dKom=mysql_fetch_assoc($nKom))
{
    $noKom+=1;
    $idKomponen[$dKom['idkomponen']]=$dKom['idkomponen'];
}*/
  

$iKom="select distinct (a.idkomponen) as idkomponen,b.plus from ".$dbname.".sdm_gaji_vw a 
		left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id
		where true ".$komponen." order by b.plus desc,a.idkomponen asc ";
$nKom=mysql_query($iKom) or die (mysql_error($conn));
while($dKom=mysql_fetch_assoc($nKom))
{
    $noKom+=1;
    $idKomponen[$dKom['idkomponen']]=$dKom['idkomponen'];
    $pmKomponen[$dKom['idkomponen']]=$dKom['plus'];
}




     
$rangePer=month_inbetween($per1,$per2);
$colspanPer=count($rangePer);


$tglMasuk=$per1.'-01';
$tglKeluar=$per2.'-28';


//echo $tglMasuk;
#ambil list datakaryawannya #tolong buat validasi seperti proses gaji untuk filter karyawan aktif
$iKar="select distinct(karyawanid) as karyawanid,namakaryawan,nik,kodejabatan,tipekaryawan"
        . " from ".$dbname.".sdm_gaji_vw  where kodeorg='".$kdorg."' ".$sorttk." "
        . " and periodegaji between '".$per1."' and '".$per2."' ".$komponen." order by namakaryawan asc";


$nKar=  mysql_query($iKar) or die (mysql_error($conn));
while($dKar=  mysql_fetch_assoc($nKar))
{
    $counterKar+=1;
    $kar[$dKar['karyawanid']]=$dKar['karyawanid'];
    $nm[$dKar['karyawanid']]=$dKar['namakaryawan'];
    $nik[$dKar['karyawanid']]=$dKar['nik'];
    $jabatan[$dKar['karyawanid']]=$dKar['kodejabatan'];
    $tipekaryawan[$dKar['karyawanid']]=$dKar['tipekaryawan'];
}

#ambil gaji dan komponen
$iGaji="select * from ".$dbname.".sdm_gaji  where kodeorg='".$kdorg."' and"
        . " periodegaji between '".$per1."' and '".$per2."' ".$komponen."";
$nGaji=mysql_query($iGaji) or die (mysql_error($conn));
while($dGaji=mysql_fetch_assoc($nGaji))
{
    $counterGaji+=1;
    $gaji[$dGaji['karyawanid']][$dGaji['periodegaji']][$dGaji['idkomponen']]=$dGaji['jumlah'];
}





if($counterGaji<1 || $counterKar<1)
{
    exit("Data Kosong");
}


if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
       <td rowspan=3 bgcolor=#CCCCCC align=center>No</td>
       <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>
       <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>
       
           <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jabatan']."</td>
           <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tipekaryawan']."</td>    
       <td colspan=".$colspanPer*($noKom+4)." bgcolor=#CCCCCC align=center>".$_SESSION['lang']['periode']."</td>
      
    </tr>";
	// <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['subtotal']."</td>
$stream.="<tr class=rowheader>";
foreach($rangePer as $ar => $per)
{
    $stream.="<td colspan=".($noKom+4)." bgcolor=#CCCCCC align=center>".$per."</td>";
}
$stream.="</tr>";

$stream.="<tr class=rowheader>";
for($i=1;$i<=$colspanPer;$i++){
	$plus=0;
    foreach($idKomponen as $idKom){
		if($pmKomponen[$idKom]==$plus){
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['penambah']."</td>";
			$plus=1;
		}
		if($idKom==32){
	        $stream.="<td bgcolor=#CCCCCC align=center>".$optNmKomponen[$idKom]." Panen</td>";
	        $stream.="<td bgcolor=#CCCCCC align=center>".$optNmKomponen[$idKom]." Non Panen</td>";
		}else{
	        $stream.="<td bgcolor=#CCCCCC align=center>".$optNmKomponen[$idKom]."</td>";
		}
    }
	$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['pengurang']."</td>";
	$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."</td>";
}
$stream.="</tr>";
$stream.="</thead>";





$gtpenambah[$per]=0;
$gtpengurang[$per]=0;
foreach($kar as $idKar)
{
    $no+=1;
    $stream.="<tr class=rowcontent>
    <td>".$no."</td>
    <td align=right>".$nik[$idKar]."</td>    
    <td align=left>".$nm[$idKar]."</td>
    <td align=left>".$optnmjab[$jabatan[$idKar]]."</td>
    <td align=left>".$opttipekar[$tipekaryawan[$idKar]]."</td>";
    //$gaji[$dGaji['karyawanid']][$dGaji['periodegaji']][$dGaji['idkomponen']]
    foreach($rangePer as $ar => $per)
    {
        //$stream.="<td colspan=".$noKom."  align=right>".$isi."</td>";
        $granTotKom[$per]=0;
		$subpenambah[$per]=0;
		$subpengurang[$per]=0;
		$plus=0;
        foreach($idKomponen as $idKom)
        {
			if($pmKomponen[$idKom]==$plus){
	            $stream.="<td align=right>".number_format((float)$subpenambah[$per],2)."</td>";
				$plus=1;
			}
            setIt($gaji[$idKar][$per][$idKom],'');
            setIt($subTotKar[$idKar],'');
            setIt($subPerKom[$per][$idKom],'');
			if($idKom==32){
				if($jabatan[$idKar]==9 or $jabatan[$idKar]==10 or $jabatan[$idKar]==11 or $jabatan[$idKar]==14 or $jabatan[$idKar]==334){
		            $stream.="<td align=right>".number_format((float)$gaji[$idKar][$per][$idKom],2)."</td>";
		            $stream.="<td align=right>".number_format(0,2)."</td>";
					$subPerPremiPnn[$per][$idKom]+=$gaji[$idKar][$per][$idKom];
				}else{
		            $stream.="<td align=right>".number_format(0,2)."</td>";
		            $stream.="<td align=right>".number_format((float)$gaji[$idKar][$per][$idKom],2)."</td>";
					$subPerPremiNon[$per][$idKom]+=$gaji[$idKar][$per][$idKom];
				}
			}else{
	            $stream.="<td align=right>".number_format((float)$gaji[$idKar][$per][$idKom],2)."</td>";
			}
            $subTotKar[$idKar]+=$gaji[$idKar][$per][$idKom];
            $subPerKom[$per][$idKom]+=$gaji[$idKar][$per][$idKom];
			if($pmKomponen[$idKom]==1){
				$subpenambah[$per]+=$gaji[$idKar][$per][$idKom];
				$gtpenambah[$per]+=$gaji[$idKar][$per][$idKom];
			}else{
				$subpengurang[$per]+=$gaji[$idKar][$per][$idKom];
				$gtpengurang[$per]+=$gaji[$idKar][$per][$idKom];
			}
        }
        $stream.="<td align=right>".number_format((float)$subpengurang[$per],2)."</td>";
		$stream.="<td align=right>".number_format((float)($subpenambah[$per]-$subpengurang[$per]),2)."</td>";
    }
    $granTot="";
    $granTot+=$subTotKar[$idKar];
    //$stream.="<td align=right>".number_format($subTotKar[$idKar])."</td>";
    $stream.="</tr>";  
}
$stream.="<thead><tr class=rowcontent>";
$stream.="<td colspan=5 align=center>".$_SESSION['lang']['grnd_total']."</td>";
foreach($rangePer as $ar => $per){
	$plus=0;
    foreach($idKomponen as $idKom){
		if($pmKomponen[$idKom]==$plus){
            $stream.="<td align=right>".number_format((float)$gtpenambah[$per],2)."</td>";
			$plus=1;
		}
        setIt($subPerKom[$per][$idKom],'');
		if($idKom==32){
			$stream.="<td align=right>".number_format((float)$subPerPremiPnn[$per][$idKom],2)."</td>";
			$stream.="<td align=right>".number_format((float)$subPerPremiNon[$per][$idKom],2)."</td>";
		}else{
			$stream.="<td align=right>".number_format((float)$subPerKom[$per][$idKom],2)."</td>";
		}
    }
    $stream.="<td align=right>".number_format((float)$gtpengurang[$per],2)."</td>";
	$stream.="<td align=right>".number_format((float)($gtpenambah[$per]-$gtpengurang[$per]),2)."</td>";
}
//$stream.="<td>".number_format($granTot,2)."</td>";

$stream.="</tr></thead>";
$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="laporan_total_komponen_gaji_".$kdorg;
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
        break;	
}
?>