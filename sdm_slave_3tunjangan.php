<?php
//ind
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=$_GET['proses'];


$unit=checkPostGet('unit','');
$per=checkPostGet('per','');
$jenis=checkPostGet('jenis','');
$tipe=checkPostGet('tipe','');
$tahun=checkPostGet('tahun','');
$tgl=  tanggalsystemn(checkPostGet('tgl',''));
$pengali=checkPostGet('pengali','');
$makan=checkPostGet('makan','');
$kawin=checkPostGet('kawin','');
$optTk=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$optJab=makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');


if($jenis!=46)
{
    $disabled="disabled";
}
else 
{
    $disabled="";
}

/*$periode=$_POST['periode'];
$karyawanid=$_POST['karyawanid'];
$premi=$_POST['premi'];*/


if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}


if($kawin!='')
{
    $stKawin="and statusperkawinan='".$kawin."' ";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
        <td bgcolor=#CCCCCC hidden align=center>Jenis</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>
        <td bgcolor=#CCCCCC hidden  align=center>".$_SESSION['lang']['karyawanid']."</td>    
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>    
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tipekaryawan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['lokasitugas']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['subbagian']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['bagian']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tmk']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['masakerja']."<br>(Bulan)</td> 
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['basic']."</td>
        <td bgcolor=#CCCCCC align=center hidden>rupiah x basis</td>  
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['pengali']."</td>
        <td bgcolor=#CCCCCC align=center>Tunjangan</td>
    </tr>";
$stream.="</thead>";

#bentuk list karyawan
$iKar="select karyawanid,namakaryawan,nik,tipekaryawan,kodejabatan,lokasitugas,subbagian,bagian,tanggalmasuk,"
        . " COALESCE(ROUND(DATEDIFF('".$tgl."',tanggalmasuk)/365.25,3),0) as masakerja"
        . " from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."')"
        . " and tipekaryawan='".$tipe."' ".$stKawin." ";
//exit('Warning :'.$iKar);
$nKar=  mysql_query($iKar) or die (mysql_error($conn));
while($dKar=  mysql_fetch_assoc($nKar))
{
    $counterKar+=1;
    $idKar[$dKar['karyawanid']]=$dKar['karyawanid'];
    $nama[$dKar['karyawanid']]=$dKar['namakaryawan'];
    $tk[$dKar['karyawanid']]=$dKar['tipekaryawan'];
    $nik[$dKar['karyawanid']]=$dKar['nik'];
    $lokasi[$dKar['karyawanid']]=$dKar['lokasitugas'];
    $subBag[$dKar['karyawanid']]=$dKar['subbagian'];
    $jab[$dKar['karyawanid']]=$dKar['kodejabatan'];
    $bag[$dKar['karyawanid']]=$dKar['bagian'];
    $tglMasuk[$dKar['karyawanid']]=$dKar['tanggalmasuk'];
    $masa[$dKar['karyawanid']]=number_format($dKar['masakerja']*12,1);
    //$dKar['masakerja'];
    //number_format($dKar['masakerja'],2);
}


/*if($jenis=='28')
{
    if($_SESSION['empl']['regional']=='KALTENG')
    {
        $komGaji="and idkomponen in ('1','48')";
    }
    else if($_SESSION['empl']['regional']=='PAPUA')
    {
        $komGaji="and idkomponen in ('1')";
    }
    $uangmakan=$makan*25;
}
else
{
     $komGaji="and idkomponen in ('1')";
}*/




$uangmakan=$makan*25;

if($jenis=='28')
{
    $komGaji="and idkomponen in ('1','56','57')";
}
else if ($jenis=='46')
{
    $komGaji="and idkomponen in ('1')";
}
else 
{
    $komGaji="and idkomponen in ('1')";
}



/*
thr : gp+umakan, tunj lainnya (118) + tunj. absensi (sec)
prod : gp+uangmakan + premi tetap + tunj lainnya (118)
pendidikan : gp
 */

#bentuk gapok
/*$iGaji="select sum(jumlah) as jumlah,karyawanid from ".$dbname.".sdm_5gajipokok where tahun='".$tahun."' ".$komGaji.""
        . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."'  "
        . " and tipekaryawan='".$tipe."')  group by karyawanid";*/

$iGaji="select * from ".$dbname.".sdm_5gajipokok where tahun='".$tahun."' ".$komGaji.""
        . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."'  "
        . " and tipekaryawan='".$tipe."')";


$nGaji=  mysql_query($iGaji) or die (mysql_error($conn));
while($dGaji=  mysql_fetch_assoc($nGaji))
{
    $counterGaji+=1;
    if($dGaji['idkomponen']=='56')
    {
        $dGaji['jumlah']=$dGaji['jumlah']*25;
    }
    $gaji[$dGaji['karyawanid']]+=$dGaji['jumlah'];
}





if($counterGaji<1 || $counterKar<1)
{
    exit("Data Kosong");
}

foreach ($idKar as $kar)
{
    $no+=1;
    $stream.="<tr class=rowcontent id=row".$no.">";
        $stream.="<td hidden id=jenissave".$no.">".$jenis."</td>";
        $stream.="<td>".$no."</td>";
        $stream.="<td hidden id=karyawanidsave".$no.">".$kar."</td>";
        $stream.="<td>".$nama[$kar]."</td>";
        $stream.="<td>".$nik[$kar]."</td>";
        $stream.="<td>".$optTk[$tk[$kar]]."</td>";
        $stream.="<td id=kdorgsave".$no.">".$lokasi[$kar]."</td>";
        $stream.="<td>".$subBag[$kar]."</td>";
        $stream.="<td>".$bag[$kar]."</td>";
        $stream.="<td>".$optJab[$jab[$kar]]."</td>";
        $stream.="<td>".tanggalnormal($tglMasuk[$kar])."</td>";
        $stream.="<td>".$masa[$kar]."</td>";
        //$stream.="<td>".$gaji[$kar]."</td>";
        if($jenis=='47')
        {
            $rupiah=$gaji[$kar];
        }
        else
        {
             $rupiah=$gaji[$kar]+($uangmakan);
        }
        
        $stream.="<td>".$rupiah."</td>";
        
        /*if($jenis=='28')
        {
            if($masa[$kar]<3)
            {
                $basis=0;
            }
            else if($masa[$kar]>=3 && $masa[$kar]<12)
            {
                $basis=$masa[$kar]/10;
            }
            else if($masa[$kar]>=12)
            {
                $basis=1;
            }
        }
        else if($jenis=='46')
        {
            if($masa[$kar]<12)
            {
                $basis=0;
            }
            else
            {
                $basis=1;
            }
        }
        
        //
        
        else if($jenis=='47')
        {
            if($masa[$kar]<3)
            {
                $basis=0;
            }
            else
            {
                $basis=1;
            }
        }*/
        
        if($masa[$kar]<3)
        {
            $basis=0;
        }
        else if($masa[$kar]>=3 && $masa[$kar]<12)
        {
            $basis=$masa[$kar]/10;
        }
        else if($masa[$kar]>=12)
        {
            $basis=1;
        }
        
        //$tanpaPengali=$rupiah*$basis;
        
        if($basis<1)
        {
            $tanpaPengali=$rupiah/12*$basis*10;
        }
        else
        {
            $tanpaPengali=$rupiah*$basis*1;
        }
      
        $stream.="<td id=tanpapengali".$no." hidden>".$tanpaPengali."</td>";
        
        $stream.="<td><input type=text onkeyup=getPerhitungan(".$no.") $disabled  id=pengalibawah".$no." value='".$pengali."' size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 style=\"width:125px;\"></td>";

         $stream.="<td><input type=text  id=jumlahsave".$no." value='".$tanpaPengali*$pengali."' size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:125px;\"></td>";
 
        //$stream.="<td id=jumlahsave".$no.">".$tanpaPengali*$pengali."</td>";
    $stream.="</tr>";           
    
}
if ($proses != 'excel') 
{//;saveAll(".$no.")
    $stream.="<button class=mybutton onclick=del(".$no.");>".$_SESSION['lang']['proses']."</button>";
}//saveAll

$stream.="</tbody></table>";
		
switch($proses)
{
    
     case'uang':
        
        $optReg=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
        $reg=$optReg[$unit];
        
        $iUang="select * from ".$dbname.".sdm_5uangmakan where regional='".$reg."' ";
        $nUang=  mysql_query($iUang) or die (mysql_error($conn));
        $dUang=  mysql_fetch_assoc($nUang);
            echo $dUang['rupiah'];
    break;
    
    
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_tunjangan_".$jenis._.$tglSkrg;
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
                
	default:
}



?>