<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$kdorg=$_POST['kdorg'];
$thn=$_POST['thn'];
if(($proses=='excel')or($proses=='pdf'))
{
    $thn=$_GET['thn'];
    $kdorg=$_GET['kdorg'];
}


if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{

    if(($kdorg=='')or($thn==''))
	{
		echo"Error: data tidak boleh kosong"; 
		exit;
    }

   
	
}



$pt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');

#karyawan
$iKar="select * from ".$dbname.".datakaryawan where lokasitugas='".$kdorg."' "
        . " and tanggalmasuk like '%2014-01%'  ";
$nKar=  mysql_query($iKar) or die (mysql_error($conn));
while($dKar=  mysql_fetch_assoc($nKar))
{
    $karyawan[$dKar['karyawanid']]=$dKar['karyawanid'];
    $namakaryawan[$dKar['karyawanid']]=$dKar['namakaryawan'];
    $nikkaryawan[$dKar['karyawanid']]=$dKar['nik'];
    $lokasitugas[$dKar['karyawanid']]=$dKar['lokasitugas'];
    $subbagian[$dKar['karyawanid']]=$dKar['subbagian'];
    $tglmasuk[$dKar['karyawanid']]=$dKar['tanggalmasuk'];
    $tglkeluar[$dKar['karyawanid']]=$dKar['tanggalkeluar'];
    $permasuk[$dKar['karyawanid']]=  substr($dKar['tanggalmasuk'],0,7);
    $perkeluar[$dKar['karyawanid']]=  substr($dKar['tanggalkeluar'],0,7);
  
    
}

#bentuk gapok
$iGaji="select jumlah,karyawanid from ".$dbname.".sdm_5gajipokok where tahun='".$thn."'"
        . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$kdorg."')  ";
$nGaji=  mysql_query($iGaji) or die (mysql_error($conn));
while($dGaji=  mysql_fetch_assoc($nGaji))
{
    $gaji[$dGaji['karyawanid']]=$dGaji['jumlah'];
}




#buat tipe org
$iTipe="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$kdorg."' ";
$nTipe=  mysql_query($iTipe) or die (mysql_error($conn));
$dTipe=  mysql_fetch_assoc($nTipe);
    $tipeorg=$dTipe['tipe'];

if($tipeorg!='PABRIK')
{
    $tipeorg='KEBUN';
}


#buat rumus bpjs
$iKerja="select bebanperusahaan from ".$dbname.".sdm_5bpjs where lokasibpjs='".$tipeorg."' and jenisbpjs='ketenagakerjaan' ";
$nKerja=  mysql_query($iKerja) or die (mysql_error($conn));
$dKerja=  mysql_fetch_assoc($nKerja);
    $bpjskerja=$dKerja['bebanperusahaan']/100;
    
$iSehat="select bebanperusahaan from ".$dbname.".sdm_5bpjs where lokasibpjs='".$tipeorg."' and jenisbpjs='kesehatan' ";
$nSehat=  mysql_query($iSehat) or die (mysql_error($conn));
$dSehat=  mysql_fetch_assoc($nSehat);
    $bpjssehat=$dSehat['bebanperusahaan']/100;   
    
 


/*echo"<pre>";
print_r($gaji);
echo"</pre>";*/


                if($proses=='excel')
                {
                    $stream="<table cellspacing='1' border='1' class='sortable'>";
                }
                else 
                {
                    $stream.="<table cellspacing='1' border='0' class='sortable'>";
                }
                $stream.="<thead class=rowheader>
                          <tr>
                            <td align=center rowspan=3>No</td>
                            <td align=center rowspan=3>Nama</td>
                            <td align=center rowspan=3>Nik</td>
                            <td align=center rowspan=3>lok</td>
                            <td align=center rowspan=3>sub bag</td>
                            <td align=center rowspan=3>gapok</td>
                            <td colspan=2  align=center>Tanggal</td>
                            <td align=center colspan=24>2014</td>
                          </tr>";
                
                $stream.="<tr>
                            <td rowspan=2>Masuk</td>
                            <td rowspan=2>Keluar</td>
                        ";
               // $stream.="<tr>";
                for($bulan=1;$bulan<=12;$bulan++)
                {
                    if(strlen($bulan)==1)
                    {
                        $bulan='0'.$bulan;
                        
                    }
                    else
                    {
                        $bulan=$bulan;
                    }
                    $nmbulan=  numToMonth($bulan, 'I', 'long');
                    $stream.="
                            <td align=center colspan=2>".$nmbulan."</td>
                         ";
                }
                $stream.="</tr>";
                $stream.="<tr>";
                for($bpjs=1;$bpjs<=12;$bpjs++)
                {
                    $stream.="
                            <td align=center>Ketenagakerjaan</td>
                            <td align=center>Kesehatan</td>";
                }
                $stream.="  
                          </tr>
                    </thead>
                <tbody>";




if(is_array($karyawan)){
	foreach($karyawan as $karyawanid)
	{

		if($tglkeluar[$karyawanid]=='0000-00-00')
		{
			$tglkeluar[$karyawanid]='';
			$tglkeluarv2=tanggalnormal($tglkeluar[$karyawanid]);      
		}
		
		if($tglkeluarv2=='--')
		{ 
			$tglkeluarv2='';      
		}
		else
		{
			$tglkeluarv2=tanggalnormal($tglkeluar[$karyawanid]);      
		}
		
		
		##kerja
		$rpbpjskerja=$bpjskerja*$gaji[$karyawanid];
		
		##sehat
		$rpbpjssehat=$bpjssehat*$gaji[$karyawanid];
		
		
		$no+=1;
		$stream.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$namakaryawan[$karyawanid]."</td> 
					<td>".$nikkaryawan[$karyawanid]."</td>    
					<td align=right>".$lokasitugas[$karyawanid]."</td> 
					<td align=right>".$subbagian[$karyawanid]."</td> 
					<td align=right>".$gaji[$karyawanid]."</td> 
					<td align=right>".tanggalnormal($tglmasuk[$karyawanid])."</td> 
					<td align=right>".$tglkeluarv2."</td>";
		for($i=1;$i<=12;$i++)
		{
			if(strlen($i)==1)
			{
				$i='0'.$i;
			}
			else
			{
				$i=$i;
			}
			
			$per='2014-'.$i;
			
			if($per<=$permasuk[$karyawanid])
			{
				$isikerja='';
				$isisehat='';
			}
			else if ($per>=$perkeluar[$karyawanid] and $tglkeluar[$karyawanid]!='')
			{
				$isikerja='';
				$isisehat='';
			}
			else
			{
				$isikerja=$rpbpjskerja;
				$isisehat=$rpbpjssehat;
			}
			
			$stream.="
					<td align=right>".$isikerja."</td>
					<td align=right>".$isisehat."</td>";
			
			
			$totkerja+=$isikerja;
		   
		}
	   
	}
}
else{
 echo "No data";
}
/*$stream.="<tr class=rowcontent>";
	$stream.="
            <thead>
            <tr>
                <td align=center colspan=6>Total</td>
                <td align=right>".number_format($gtotnetto,2)."</td>
                <td align=right>".number_format($gtotpersen,2)."</td>";    
        
        for($i=1;$i<=12;$i++)
        {
            $stream.="
                <td align=right>".$totkerja."</td>
                <td align=right>".$isisehat."</td>";
        }
*/              
                
        $stream.="        
            </tr>
	</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_BPJS_".$thn;
                
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
	
	
	
###############	
#panggil PDFnya
###############
	
	default:
	break;
}

?>