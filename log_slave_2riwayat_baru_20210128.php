<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

//status PP masih harus dikaji ulang

$nmBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

$stPp=array("0"=>"Proses Persetujuan","1"=>"sudah selesai di sisi user","2"=>"PP sudah bisa di PO","3"=>"Ditolak");
$stPo=array("0"=>"Belum Selesai","1"=>"sudah selesai dan diajukan","2"=>"sudah dapat di kirim(persetujuan selesai)",
    "3"=>"barang sudah ada yang masuk gudang");

$proses=$_GET['proses'];
$nopp=$_POST['nopp'];
$tgl=tanggalsystem($_POST['tgl']);
$per=$_POST['per'];
$lok=$_POST['lok'];
$stat=$_POST['stat'];
$sup=$_POST['sup'];
$nama=$_POST['nama'];
$psj=$_POST['psj'];



if($proses=='excel' || $proses=='pdf')
{
    $nopp=$_GET['nopp'];
    $tgl=tanggalsystem($_GET['tgl']);
    $per=$_GET['per'];
    $lok=$_GET['lok'];
    $stat=$_GET['stat'];
    $sup=$_GET['sup'];
    $nama=$_GET['nama'];
    $psj=$_GET['psj'];
}


if ($proses == 'excel') 
    {
        $stream = "<table class=sortable cellspacing=1 border=1>";
    } 
    else 
    {
        $stream = "<table class=sortable cellspacing=1>";
    }

    $stream.="<thead class=rowheader>
        <tr class=rowheader>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nourut']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nopp']."</td> 
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['tanggal']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['kodebarang']."</td>    
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['namabarang']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['jumlah']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['satuan']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['persetujuan']."</td>
            <td bgcolor=#CCCCCC  align=center>Ostd</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['chat']."</td>   
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['purchaser']."</td>     
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nopo']."</td>     
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['tgl_po']."</td> 
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['namasupplier']."</td>
            <td bgcolor=#CCCCCC  align=center>"."Qty PO"."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['satuan']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['status']." PO</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['rapbNo']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['tanggal']."</td>
            <td bgcolor=#CCCCCC  align=center>"."Qty BAPB"."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['satuan']."</td>
            <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['print']."</td>
                
        </tr></thead>";

    //lokasitugas lock
    //print_r($_SESSION['empl']);
      
    if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
    {
        $where.=" and a.kodeorg='".$_SESSION['empl']['kodeorganisasi']."'";
    }
    
    //nopp
    if($nopp!='')
    {
        $where.=" and a.nopp like '%".$nopp."%' ";
    }
    
    //tanggal pp
    if($tgl!='')
    {
        $where.="and a.tanggal='".$tgl."' ";
    }
    
    //lokasi pembelian
    if($lok!='')
    {
        $where.="and a.lokalpusat='".$lok."'";
    }
    
    //nama supplier
    if($sup!='')
    {
        $where.="and b.namasupplier like '%".$sup."%' ";
    }
    
    //
    
    //stat pp
    if($stat!='')
    {
        if($stat=='1')
        {
            $where.=" and a.close='1' and a.status!='3'";       
        }
        if($stat=='2')
        {
            $where.=" and a.close='2' and a.purchaser='0000000000' and a.create_po=''";       
        }
        if($stat=='3')
        {
            $where.="and  a.create_po!='' and b.nopo is not null";
        }
        if($stat=='4')
        {
            $where.="and (a.create_po='' or a.create_po='0') and a.purchaser!='0000000000' and a.close='2' ";
        }
        if($stat=='5')
        {
            $where.="and a.status='3' and (a.close='2' or a.close='1') ";
        }
    }
    
    if($nama!='')
    {
        $where.=" and a.kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where "
                . " namabarang like '%".$nama."%')";
                //. " namabarang like '%".$nama."%' and inactive='0')";
    }
    
    
       
     if($psj!='')
     { 
         $where.="and (a.persetujuan1='".$psj."' || a.persetujuan2='".$psj."' || a.persetujuan3='".$psj."' || "
                 . " a.persetujuan4='".$psj."' || a.persetujuan5='".$psj."')";
     }
      

    //select * from log_prapodt a left join log_podt b on a.nopp=b.nopp left join log_transaksi_vw c on a.nopp=c.nopp where a.nopp='002/10/2014/PP/SKDM' and a.kodebarang='37401051'   
    
    $iList="select a.kodeorg,a.nopp,a.tanggal as tanggalpp,a.purchaser,a.kodebarang,a.jumlah as jumlahpp,a.kodevhc,a.status,"
            . " a.close,a.create_po,a.lokalpusat,b.nopo as nopo,b.tanggal as tanggalpo,"
            . " b.jumlahpesan as jumlahpo,b.satuan as satuanpo,b.kodesupplier,b.kodesupplier,b.namasupplier,"
            . " b.statuspo,c.notransaksi,c.tanggal as tanggalba,c.jumlah as jumlahba,c.satuan as satuanba,"
            . " a.persetujuan1,a.persetujuan2,a.persetujuan3,a.persetujuan4,a.persetujuan5,d.namakaryawan as namapurchaser "
            . " from ".$dbname.".log_prapo_vw a left join ".$dbname.".log_po_vw b on a.nopp=b.nopp and a.kodebarang=b.kodebarang"
            . " left join ".$dbname.".log_transaksi_vw c on b.nopo=c.nopo and b.nopp=c.nopp and b.kodebarang=c.kodebarang "
            . " left join ".$dbname.".datakaryawan d on d.karyawanid=a.purchaser "
            . " where  a.tanggal like '%".$per."%' ".$where." order by a.nopp desc,a.tanggal desc ";    

    //echo $iList;
    
    $nList=mysql_query($iList) or die (mysql_error($conn));
    while($dList=mysql_fetch_assoc($nList))
    {
        //buat tanggal
        if(!is_null($dList['tanggalpo'])||$dList['tanggalpo']!='')
        {
            $tglA=substr($dList['tanggalpo'],0,4);//po
            $tglB=substr($dList['tanggalpo'],5,2);
            $tglC=substr($dList['tanggalpo'],8,2);
            $tgl2=$tglA.$tglB.$tglC;
            $tGl1=substr($dList['tanggalpp'],0,4);
            $tGl2=substr($dList['tanggalpp'],5,2);
            $tGl3=substr($dList['tanggalpp'],8,2);
            $tgl2=$tglA.$tglB.$tglC;
            $tgl1 =$tGl1.$tGl2.$tGl3;
            $stat=1;
            $nopo=$dList['nopo'];
        }
        else
        {
            $tGl1=substr($dList['tanggalpp'],0,4);
            $tGl2=substr($dList['tanggalpp'],5,2);
            $tGl3=substr($dList['tanggalpp'],8,2);
            $tgl1 =$tGl1.$tGl2.$tGl3;
            $Tgl2 = date('Y-m-d');			
            //$tglA=substr($dList,0,4);
            //$tglB=substr($dList,5,2);
            //$tglC=substr($dList,8,2);
			$tglA=0;
            $tglB=0;
            $tglC=0;
            $tgl2=$tglA.$tglB.$tglC;
            $stat=0;	
            $nopo="Blm PO";				
        }
        $starttime=strtotime($tgl1);//time();// tanggal sekarang
        $endtime=strtotime($tgl2);//tanggal pembuatan dokumen
        $timediffSecond = abs($endtime-$starttime);
        $base_year = min($tGl1, $tglA);
        $diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
        $jmlHari=date("j", $diff) - 1;
        //tutup tanggal
        
        //periksa chat==================================
        $strChat="select * from ".$dbname.".log_pp_chat where "
                . " kodebarang='".$dList['kodebarang']."' and nopp='".$dList['nopp']."'";
        $resChat=mysql_query($strChat);
        if(mysql_num_rows($resChat)>0)
        {
                $ingChat="<img src='images/chat1.png' onclick=\"loadPPChat('".$dList['nopp']."','".$dList['kodebarang']."',event);\" class=resicon>";
        } else {
                $ingChat="<img src='images/chat0.png'  onclick=\"loadPPChat('".$dList['nopp']."','".$dList['kodebarang']."',event);\" class=resicon>";
        }
        
        //status pp
        $no+=1;
		if(!isset($stPo[$dList['statuspo']])) $stPo[$dList['statuspo']] = "Belum PO";
        $stream.="<tr class=rowcontent>";
            $stream.="<td>".$no."</td>";
            $stream.="<td>".$dList['nopp']."</td>";
            $stream.="<td>".tanggalnormal($dList['tanggalpp'])."</td>";
            $stream.="<td>".$dList['kodebarang']."</td>";
            $stream.="<td width='450px'>".$nmBrg[$dList['kodebarang']]."</td>";
            $stream.="<td align=right>".number_format($dList['jumlahpp'])."</td>";
            $stream.="<td>".$satBrg[$dList['kodebarang']]."</td>";
            
            //$stream.="<td>".$stPp[$dList['close']]."</td>";
            
           
            
            if(($dList['close']==2 ||$dList['close']==1) && $dList['status']==3)
            {
                $stream.="<td>Ditolak</td>";
            }
            else
            {
                $stream.="<td>".$stPp[$dList['close']]."</td>";
            }
            
            
            $stream.="<td>".$jmlHari."</td>";
            $stream.="<td>".$ingChat."</td>";
            $stream.="<td>".$dList['namapurchaser']."</td>";
            $stream.="<td>".$dList['nopo']."</td>";
            $stream.="<td>".$dList['tanggalpo']."</td>";
            $stream.="<td>".$dList['namasupplier']."</td>";
            $stream.="<td align=right>".number_format($dList['jumlahpo'])."</td>";
            $stream.="<td>".$dList['satuanpo']."</td>";
            $stream.="<td>".$stPo[$dList['statuspo']]."</td>";
            $stream.="<td>".$dList['notransaksi']."</td>";
            $stream.="<td>".$dList['tanggalba']."</td>";
            $stream.="<td align=right>".number_format($dList['jumlahba'])."</td>";
            $stream.="<td>".$dList['satuanba']."</td>";
            $stream.="<td align=center>
                        <img onclick=\"previewDetail('".$dList['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','".$dList['nopp']."','','log_slave_print_log_pp',event);\"></td>";
        $stream.="</tr>";    
    }

$stream.="</table>";	  
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
		$nop_="Laporan_riwayat_PP_".$kdOrg;
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
                    
   
default;


}    
?>