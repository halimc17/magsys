<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$buy=$_POST['buy'];
$brg=$_POST['brg'];
if(($proses=='excel')or($proses=='pdf'))
{
  
    $buy=$_GET['buy'];
    $brg=$_GET['brg'];
}


if($buy!='')
{
    $buysch="and kodecustomer='".$buy."'";
}


if($brg!='')
{
    $brgsch="and kodecustomer in (select kodecustomer from ".$dbname.".pmn_4komoditi where kodebarang='".$brg."')";
}


if($proses=='excel')
{
    $stream="<table cellspacing='1' border='1' class='sortable' bgcolor=#CCCCCC>";
} else
{
    $stream.="<table cellspacing='1' border='0' class='sortable'>";
}
    $stream.="<thead class=rowheader>
                <tr>
                    <td align=center>No</td>
                    <td align=center>".$_SESSION['lang']['komoditi']."</td>
                    <td align=center>".$_SESSION['lang']['kodecustomer']."</td>
                    <td align=center>".$_SESSION['lang']['nmcust']."</td>
                    <td align=center>".$_SESSION['lang']['alamat']."</td>
                    <td align=center>".$_SESSION['lang']['kota']."</td>
                    <td align=center>".$_SESSION['lang']['telepon']."</td>
                    <td align=center>".$_SESSION['lang']['npwp']."</td>
                    <td align=center>".$_SESSION['lang']['alamat']." ".$_SESSION['lang']['npwp']."</td>
                    <td align=center>".$_SESSION['lang']['penandatangan']."</td>
                    <td align=center>".$_SESSION['lang']['jabatan']."</td>    
                    <td align=center>".$_SESSION['lang']['kntprson']." (".$_SESSION['lang']['email'].")</td>    
                    <td align=center>".$_SESSION['lang']['eksternal']."/".$_SESSION['lang']['internal']."</td>    
                    <td align=center>".$_SESSION['lang']['plafon']."</td>    
                    <td align=center>".$_SESSION['lang']['nilaihutang']."</td>    
                    <td align=center>".$_SESSION['lang']['toleransipenyusutan']."</td>        
                    <td align=center>".$_SESSION['lang']['berikat']."</td>        
                    <td align=center>".$_SESSION['lang']['statusberikat']."</td>               
                </tr>
            </thead>
  <tbody>";

    


    
$srt="select * from ".$dbname.".pmn_4customer where 1=1  ".$buysch." ".$brgsch." order by namacustomer asc ";  //echo $srt;
                if($rep=mysql_query($srt))
                  {
                        $no=0;
                        while($bar=mysql_fetch_object($rep))
                        {
                        //get kelompok cust
                        $sql="select * from ".$dbname.".pmn_4klcustomer where `kode`='".$bar->klcustomer."'";
                        $query=mysql_query($sql) or die(mysql_error($conn));
                        $res=mysql_fetch_object($query);
						
						//get Komoditi
						$sKo="select t1.*,t2.namabarang from ".$dbname.".pmn_4komoditi t1
							left join ".$dbname.".log_5masterbarang t2
							on t1.kodebarang = t2.kodebarang
							where `kodecustomer`='".$bar->kodecustomer."'";
						$qKo=mysql_query($sKo) or die(mysql_error($conn));
						$hasilKomoditi="";
						$hasilKomoditi2="";
						while($rKo=mysql_fetch_object($qKo)){
							$hasilKomoditi.=",".$rKo->kodebarang;
							$hasilKomoditi2.=",<br>".$rKo->namabarang;
						}
						
						//get Kontak Person
						$sPer="select * from ".$dbname.".pmn_4customercontact
							where `kodecustomer`='".$bar->kodecustomer."'";
						$qPer=mysql_query($sPer) or die(mysql_error($conn));
						$hasilPerson="";
						while($rPer=mysql_fetch_object($qPer)){
							$hasilPerson.=",<br>".$rPer->nama." (".$rPer->email.")";
						}
						
						//get akun
                        $spr="select * from  ".$dbname.".keu_5akun where `noakun`='".$bar->akun."'";
                        $rej=mysql_query($spr) or die(mysql_error($conn));
                        $bas=mysql_fetch_object($rej);
                        $no++;
						$bar->alamat = clearInvalidChar($bar->alamat);
						$bar->telepon = clearInvalidChar($bar->telepon);
						$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
                        $stream.="<tr class=rowcontent>
                                  <td style='vertical-align:top;'>".$no."</td>
                                  <td style='vertical-align:top;'>".substr($hasilKomoditi2,5)."</td>
                                  <td style='vertical-align:top;'>".$bar->kodecustomer."</td>
                                  <td style='vertical-align:top;'>".$bar->namacustomer."</td>
                                  <td style='vertical-align:top;'>".$bar->alamat."</td>
                                  <td style='vertical-align:top;'>".$bar->kota."</td>
                                  <td style='vertical-align:top;'>".$bar->telepon."</td>
                                  <td style='vertical-align:top;'>".$bar->npwp."</td>
                                  <td style='vertical-align:top;'>".$bar->alamatnpwp."</td>
                                  <td style='vertical-align:top;'>".$bar->penandatangan."</td>
                                  <td style='vertical-align:top;'>".$bar->jabatan."</td>
                                  <td style='vertical-align:top;'>".substr($hasilPerson,5)."</td>
                                  <td style='vertical-align:top;'>".$bar->statusinteks."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->plafon."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->nilaihutang."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->toleransipenyusutan."</td>
                                  <td style='vertical-align:top; text-align:center;'>".(($bar->statusberikat=='1') ? 'Y' : '')."</td>
                                  <td style='vertical-align:top;'>".$bar->keteranganberikat."</td>
                                  </tr>";
                        }
                  }
                  else
                 {
                        echo " Gagal,".(mysql_error($conn));
                 }

	$stream.="
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
		$nop_="LAPORAN_DATA_SUPPLIER_".$tglSkrg;
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
			closedir($handle);
		}           
		break;
	
	default:
	break;
}

?>