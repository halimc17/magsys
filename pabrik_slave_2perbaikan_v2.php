<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');




//$proses=$_GET['proses'];

$proses = checkPostGet('proses','');
$pabrik = checkPostGet('pabrikv','');
$station = checkPostGet('stationv','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1v',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2v',''));

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");



if($tgl1v=='--')
{
    $tgl1v='';
}
if($tgl2v=='--')
{
    $tgl2v='';
}



if($proses=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}

//bgcolor=#CCCCCC border='1'

  $stream="<table cellspacing='1' $border class='sortable'>";
      /*$stream.="<thead><tr class=rowheader>
       
            <td align=center>No</td>
    
        </tr></thead>
      <tbody>";*/

      
      
      
if($station!='')
{
    $stationTambah="and statasiun='".$station."'";
}
      
      
$iSta="SELECT distinct(statasiun) as statasiun  FROM ".$dbname.".pabrik_rawatmesinht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." order by statasiun asc";
$nSta=  mysql_query($iSta) or die (mysql_error($conn));
while($dSta= mysql_fetch_assoc($nSta))
{
    $liststasiun[$dSta['statasiun']]=$dSta['statasiun'];
}

$iList="SELECT * FROM ".$dbname.".pabrik_rawatmesinht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." order by statasiun,mesin asc";
$nList=mysql_query($iList) or die (mysql_error($conn));	
while($dList=mysql_fetch_assoc($nList))
{
    $listmesin[$dList['statasiun']][$dList['mesin']][$dList['notransaksi']] = $dList;
}

$iBarang="select * from ".$dbname.".pabrik_rawatmesindt "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,kodebarang";
$nBarang=  mysql_query($iBarang) or die (mysql_error($conn));
while($dBarang=  mysql_fetch_assoc($nBarang))
{
    $listbarang[$dBarang['kodebarang']]=$dBarang['kodebarang'];
    $barang[$dBarang['notransaksi']][]=$dBarang['kodebarang'];
    $satuanbarang[$dBarang['notransaksi']][]=$dBarang['satuan'];
    $jumlahbarang[$dBarang['notransaksi']][]=$dBarang['jumlah'];
}

if(is_array($listmesin)){
	foreach ($listmesin as $stasiun=>$row)
	{
		foreach($row as $mesin=>$row2)
		{
			foreach($row2 as $notransaksi=>$list)
			{
				$listmesin[$stasiun][$mesin][$notransaksi]['barang'] = $barang[$notransaksi];
			}
		}
	}
}else{
	$listmesin='';
}

$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
    "pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");

$noList=0;

if(is_array($listmesin)){
	foreach ($listmesin as $stasiun=>$row)
	{
		
		$stream.="<thead><tr class=rowheader>";

		if(in_array($stasiun, $liststasiun)) 
		{
			
			$stream.="<td align=left colspan=6><b>Station : ".$stasiun." - ".$nmOrg[$stasiun]."</td>
				</tr>";
			$stream.="<td align=left colspan=6><b>Pabrik : ".$pabrik." - ".$nmOrg[$pabrik]."</td>
				</tr></thead>";
			/*
			$stream.="<td align=left>Station : ".$stasiun."</td>
					<td colspan=7></td></tr>";
			$stream.="<td align=left>Pabrik : ".$pabrik."</td>
					<td colspan=7></td></tr></thead>";
					  */
		   
			foreach($row as $mesin=>$row2)
			{
				 
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center colspan=6><b>".$mesin." - ".$nmOrg[$mesin]."</td>";
				$stream.="</tr>";
				
				//No.	Tanggal	Uraian Kerusakan / Kegiatan	Bagian yang diganti / rusak	

				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center></td>";
				$stream.="<td align=center><b>No</td>";
				$stream.="<td align=center><b>Tanggal</td>";
				$stream.="<td align=center><b>Uraian Kerusakan / Kegiatan</td>";
				$stream.="<td align=center><b>Bagian yang diganti / rusak</td>";
				$stream.="<td align=center><b>Status</td>";
				$stream.="</tr>";
				
				
				$no=0;
				foreach($row2 as $notransaksi=>$list)
				{
					$no+=1;
					$i=0;
					$rowspan = count($list['barang']);
					$stream.="<tr class=rowcontent>";
					$stream.="<td rowspan='".$rowspan."'></td>";
					$stream.="<td rowspan='".$rowspan."'>".$no."</td>";
					$stream.="<td rowspan='".$rowspan."'>".$list['tanggal']."</td>";
					$stream.="<td rowspan='".$rowspan."'>".$list['kegiatan']."</td>";
					
					//Uraian Kerusakan / Kegiatan
					//$stream.="<td rowspan='".$colspan."'>".$notransaksi."</td>";
					//$stream.="<td rowspan='".$colspan."'>".$list['kegiatan']."</td>";
					
					if(empty($list['barang']))
					{
						$stream.="<td rowspan='".$rowspan."'></td>";
						$stream.="<td rowspan='".$rowspan."'></td>";
					}
					else 
					{
						foreach ($list['barang'] as $brg) {
							if($i>0) 
							 {
								$stream.="<tr class=rowcontent>";

							}

							$stream.="<td>".$nmBrg[$brg]."</td>";
							$i++;
							if($i==1)
							{
								$stream.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
							}
						}
					//$nmBrg
					}
					$stream.="</tr>";
				}
				
				
			}
		}
	}
}
else{
  echo "No data found";
}


   		
	
$stream.="</tbody></table>";



  


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################
  
switch($proses)
{
    
    case'getStationv':
     
        $optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
        $iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$pabrik."' ";     
      
        $nStation=mysql_query($iStation) or die(mysql_error($conn));
        while($dStation=mysql_fetch_assoc($nStation))
        {
            $optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
        }  
        echo $optStation;
    break;
    
    
    
######HTML
	case 'preview':
            
            if($tgl1=='' || $tgl2=='' || $pabrik=='')
            {
                exit("Please Complate the form");
            }
            
            echo $stream;
    break;

######EXCEL	
	case 'excel':
            
                if($tgl1=='' || $tgl2=='' || $pabrik=='')
                {
                    exit("Please Complate the form");
                }
            
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_PERAWATAN_MESIN_V2_".$tglSkrg;
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
	break;
}

?>