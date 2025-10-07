<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

//$proses=$_GET['proses'];
$proses = checkPostGet('proses','');
$pabrik = checkPostGet('pabrik','');
$station = checkPostGet('station','');
$machine = checkPostGet('mesin','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));
$perbaikantipe = checkPostGet('tipeperbaikan','');
$stsketuntasan = checkPostGet('statusketuntasan','');

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");

if($tgl1=='--'){
    $tgl1='';
}
if($tgl2=='--'){
    $tgl2='';
}

if($proses=='excel'){
    $border="border=1";
}else{
    $border="border=0";
}

//bgcolor=#CCCCCC border='1'

  $stream="<table cellspacing='1' $border class='sortable'>";
      $stream.="<thead><tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['notransaksi']."</td>
            <td align=center>".$_SESSION['lang']['tanggal']."</td>
            <td align=center>Uraian Kerusakan</td>    
            <td align=center>".$_SESSION['lang']['pabrik']."</td>
            <td align=center>".$_SESSION['lang']['station']."</td>
            <td align=center>Kode ".$_SESSION['lang']['mesin']."</td>    
            <td align=center>Nama ".$_SESSION['lang']['mesin']."</td>    
            <td align=center>Jam Mulai</td> 
            <td align=center>Jam Selesai</td> 
            <td align=center>Jam Perbaikan</td> 
            <td align=center>".$_SESSION['lang']['kodebarang']."</td> 
            <td align=center>Barang Yang Diganti</td> 
            <td align=center>Satuan</td> 
            <td align=center>Jumlah</td>
            <td align=center>Harga</td>
            <td align=center>Jumlah Harga</td>
            <td align=center>Mekanik</td>
            <td align=center>Tipe Perbaikan</td>
            <td align=center>Status Ketuntasan</td>
            <td align=center>Hasil Kerja</td>";
    if($proses!='excel'){
        $stream.="  
                <td align=center>*</td>";
    }
    $stream.="        
        </tr></thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr

$stationTambah='';
if($station!=''){
    $stationTambah.=" and statasiun='".$station."'";
}
if($machine!=''){
    $stationTambah.=" and mesin='".$machine."'";
}
if($perbaikantipe!=''){
    $stationTambah.=" and tipeperbaikan='".$perbaikantipe."'";
}
if($stsketuntasan!=''){
    $stationTambah.=" and statusketuntasan='".$stsketuntasan."'";
}
      
$iList="SELECT *,round(TIMESTAMPDIFF(MINUTE,jammulai,jamselesai)/60,2) as jamjalan 
		FROM ".$dbname.".pabrik_rawatmesinht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." order by pabrik,tanggal,notransaksi,statasiun,mesin ";
$nList=mysql_query($iList) or die (mysql_error($conn));	
while($dList=mysql_fetch_assoc($nList))
{
    $notransaksi[$dList['notransaksi']]=$dList['notransaksi'];
    $kdorg[$dList['notransaksi']]=$dList['pabrik'];
    $tgl[$dList['notransaksi']]=$dList['tanggal'];
    $statasiun[$dList['notransaksi']]=$dList['statasiun'];
    $mesin[$dList['notransaksi']]=$dList['mesin'];
    $kegiatan[$dList['notransaksi']]=$dList['kegiatan'];
    $jammulai[$dList['notransaksi']]=$dList['jammulai'];
    $jamselesai[$dList['notransaksi']]=$dList['jamselesai'];
    $jamjalan[$dList['notransaksi']]=$dList['jamjalan'];
    $statusketuntasan[$dList['notransaksi']]=$dList['statusketuntasan'];
    $tipeperbaikan[$dList['notransaksi']]=$dList['tipeperbaikan'];
    $hasilkerja[$dList['notransaksi']]=$dList['hasilkerja'];
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
    $hargabarang[$dBarang['notransaksi']][]=$dBarang['harga'];
}

#karyawan
$iKar="select * from ".$dbname.".pabrik_rawatmesindt_karyawan "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,karyawanid";

$nKar=  mysql_query($iKar) or die (mysql_error($conn));
while($dKar=  mysql_fetch_assoc($nKar))
{
    $listkar[$dKar['karyawanid']]=$dKar['karyawanid'];
    $kar[$dKar['notransaksi']][]=$dKar['karyawanid'];
}

$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
    "pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");

$noList=0;
if(isset($notransaksi)){
	foreach ($notransaksi as $notran)
	{
		$rowspanbarang=count($barang[$notran]);
		$rowspankar=count($kar[$notran]);
		
		if($rowspanbarang>=$rowspankar)
		{
			$rowspan=$rowspanbarang;
		}
		else
		{
			$rowspan=$rowspankar;
		}
		$rowspan=$rowspan==0 ? 1 : $rowspan;
		
		$noList+=1;
		$stream.="<tr class=rowcontent>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$noList."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$notran."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$tgl[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$kegiatan[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$kdorg[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$statasiun[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$mesin[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$nmOrg[$mesin[$notran]]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$jammulai[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$jamselesai[$notran]."</td>";
			$stream.="<td align=right valign=top rowspan=".$rowspan.">".$jamjalan[$notran]."</td>";
	  
			if(empty($barang[$notran]) and empty($kar[$notran]))
			{
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				
				$stream.="<td valign=top  rowspan=".$rowspan.">".$arrTipePerbaikan[$tipeperbaikan[$notran]]."</td>";
				$stream.="<td valign=top  rowspan=".$rowspan.">".$statusketuntasan[$notran]."</td>";
				$stream.="<td valign=top  rowspan=".$rowspan.">".$hasilkerja[$notran]."</td>";
			   
				if($proses!='excel')
				{
					$stream.="<td valign=top rowspan=".$rowspan.">
								<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=detailBarang('".$notran."',event)>
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$notran."','','pabrik_slave_perbaikan_pdf',event)\">
							</td>";  
				}
			}
			else
			{
				for($i=0;$i<$rowspan;$i++) 
				{
					if($i>0)
					{
						$stream.="<tr class=rowcontent>";
					}
					$jmlharga=$jumlahbarang[$notran][$i]*$hargabarang[$notran][$i];
					$stream.="<td valign=top align=right>".$barang[$notran][$i]."</td>";
					$stream.="<td valign=top align=left>".$nmBrg[$barang[$notran][$i]]."</td>";
					$stream.="<td valign=top align=left>".$satuanbarang[$notran][$i]."</td>";
					$stream.="<td valign=top align=right>".$jumlahbarang[$notran][$i]."</td>";
					if(!empty($barang[$notran][$i])){
						$stream.="<td valign=top align=right>".number_format($hargabarang[$notran][$i],2)."</td>";
						$stream.="<td valign=top align=right>".number_format($jmlharga,2)."</td>";
					}else{
						$stream.="<td valign=top align=right></td>";
						$stream.="<td valign=top align=right></td>";
					}
					$stream.="<td valign=top align=left>".$nmKar[$kar[$notran][$i]]."</td>";
					
					if($i==0)
					{
						$stream.="<td valign=top  rowspan=".$rowspan.">".$arrTipePerbaikan[$tipeperbaikan[$notran]]."</td>";
						$stream.="<td valign=top  rowspan=".$rowspan.">".$statusketuntasan[$notran]."</td>";
						$stream.="<td valign=top  rowspan=".$rowspan.">".$hasilkerja[$notran]."</td>";
						if($proses!='excel')
						{
							$stream.="<td valign=top rowspan=".$rowspan.">
									<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=detailBarang('".$notran."',event)>
									<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$notran."','','pabrik_slave_perbaikan_pdf',event)\">
								</td>"; 
							
						}
					}
				}
			} 
		$stream.="</tr>";
	}
}

$stream.="</tbody></table>";

#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################
  
switch($proses)
{
    case'getListBarangLaporan':
        
        $isi="<table cellspacing='1' class='sortable'><thead class=rowheader>
                <thead><tr class=rowheader>
                    <td align=center>No</td>
                    <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                    <td align=center>".$_SESSION['lang']['namabarang']."</td>    
                    <td align=center>".$_SESSION['lang']['satuan']."</td>
                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td align=center>".$_SESSION['lang']['harga']."</td>
                    <td align=center>".$_SESSION['lang']['total']."</td>    
                </tr></thead>
              <tbody>";
        $iBrg="SELECT * FROM ".$dbname.".pabrik_rawatmesindt where notransaksi='".$_POST['nodok']."'";
        $nBrg=mysql_query($iBrg) or die (mysql_error($conn));	
        while($dBrg=mysql_fetch_assoc($nBrg))
        {
            $noBrg+=1;
            $isi.="<tr class=rowcontent>
                <td align=center>".$noBrg."</td>
                <td align=left>".$dBrg['kodebarang']."</td>    
                <td align=right>".$nmBrg[$dBrg['kodebarang']]."</td>
                <td align=left>".$stBrg[$dBrg['kodebarang']]."</td>   
                <td align=right>".number_format($dBrg['jumlah'],2)."</td>
                <td align=right>".number_format($dBrg['harga'],2)."</td>    
                <td align=right>".number_format($dBrg['jumlah']*$dBrg['harga'],2)."</td>"; 
        }
        echo $isi;
    break;
    
    case'getStation':
        $optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($pabrik!=''){
        $iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$pabrik."' ";     
      
        $nStation=mysql_query($iStation) or die(mysql_error($conn));
        while($dStation=mysql_fetch_assoc($nStation))
        {
            $optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
        }  
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
		$nop_="LAPORAN_PERAWATAN_MESIN_".$tglSkrg;
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
