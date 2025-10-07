<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$param=$_POST;
if(count($param)==0){
	$param=$_GET;
}
if($_GET['proses']!=''){
	$param['proses']=$_GET['proses'];
}else{
	$param['proses']=$_POST['proses'];
}

	if($param['proses']!='getKodeAfd'){
		#cek2 variable kiriman
		if($param['kbnId']!=''){
			
		}
		if($param['afdId']!=''){
			$whr=" and nospb like '%".$param['afdId']."%'";
		}
		if(($param['tgl1']=='')||($param['tgl2']=='')){
			exit("warning: Tanggal tidak boleh kosong");
		}
		if(tanggalsystem($param['tgl1'])>tanggalsystem($param['tgl2'])){
			exit("warning: Tanggal yang dipilih salah");
		}
		$tgl1=explode("-",$param['tgl1']);
		$tanggal1=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
		$tgl2=explode("-",$param['tgl2']);
		$tanggal2=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
		$bgclr="class=rowheader";
	    $grs=0;
		if($param['proses']=='excel'){
			$bgclr="bgcolor=#dedede align=center";
			$grs=1;
		}
		 
		#persiapan di tampilkan
		$stream="<table cellpadding=1 cellspacing=1 border=".$grs." class=sortable>";
		$stream.="<thead><tr ".$bgclr.">";
		$stream.="<td>No.</td>";
		$stream.="<td>".$_SESSION['lang']['tanggal']."</td>";
		$stream.="<td>".$_SESSION['lang']['nospb']."</td>";
		//$stream.="<td>".$_SESSION['lang']['nmcust']."</td>";
		$stream.="<td>".$_SESSION['lang']['nopol']."</td>";
		$stream.="<td>".$_SESSION['lang']['supir']."</td>";
		$stream.="<td>".$_SESSION['lang']['beratMasuk']."</td>";
		$stream.="<td>".$_SESSION['lang']['beratKeluar']."</td>";
		$stream.="<td>".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td>".$_SESSION['lang']['jjg']." Sortasi</td>";
		$stream.="<td>".$_SESSION['lang']['potongan']."</td>";
		$stream.="<td>".$_SESSION['lang']['beratBersih']."</td>";
		$stream.="</tr></thead><tbody>";

		$sData="select notransaksi,left(tanggal,10) as tanggal,nospb,supir,jumlahtandan1,beratmasuk,beratkeluar,
				nokendaraan,beratbersih,jjgsortasi,kgpotsortasi 
				from ".$dbname.".pabrik_timbangan where millcode='EXTM' and kodeorg='".$param['kbnId']."' 
				and left(tanggal,10) between '".$tanggal1."' and '".$tanggal2."' and kodebarang='40000003' ".$whr." order by nospb,tanggal";
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			$dtSpb[$rData['nospb']]=$rData['nospb'];
			$arrAfd=explode("/",$rData['nospb']);
			$afdId[$arrAfd[1]]=$arrAfd[1];
			$dtTgl[$rData['tanggal']]=$rData['tanggal'];
			$whr="nospb='".$param['spbId']."'";
			$optCust=makeOption($dbname,'kebun_spbht','nospb,penerimatbs',$whr);
			$dtCust[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$optCust[$rData['nospb']];
			$dtCek[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['notransaksi'];
			$dtNopol[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['nokendaraan'];
			$dtSupir[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['supir'];
			$dtJjg[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['jumlahtandan1'];
			$dtBrtMsk[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['beratmasuk'];
			$dtBrtKlr[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['beratkeluar'];
			$dtBrtBrsh[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['beratbersih'];
			$dtJjgSor[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['jjgsortasi'];
			$dtBrtSor[$arrAfd[1].$rData['tanggal'].$rData['nospb']]=$rData['kgpotsortasi'];
			$jmlhDt[$arrAfd[1]]+=1;
		}
		if(is_array($afdId) && count($afdId)>0){
			foreach($afdId as $lstAfd){
			  if(is_array($dtTgl) && count($dtTgl)>0){
				foreach($dtTgl as $lstTgl){
				  if(is_array($dtSpb) && count($dtSpb)>0){
					foreach($dtSpb as $lstSpb){
						if($dtCek[$lstAfd.$lstTgl.$lstSpb]!=''){
							if($tempAfd!=$lstAfd){
								$no=1;
								$tempAfd=$lstAfd;
							}else{
								$no+=1;
							}
							$whrsp="supplierid='".$dtCust[$lstAfd.$lstTgl.$lstSpb]."'";
							$optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsp);
							$stream.="<tr class=rowcontent>";
							$stream.="<td>".$no."</td>";
							$stream.="<td>".tanggalnormal($lstTgl)."</td>";
							$stream.="<td>".$lstSpb."</td>";
							//$stream.="<td>".$optSupp[$dtCust[$lstAfd.$lstTgl.$lstSpb]]."</td>";
							$stream.="<td>".$dtNopol[$lstAfd.$lstTgl.$lstSpb]."</td>";
							$stream.="<td>".$dtSupir[$lstAfd.$lstTgl.$lstSpb]."</td>";
							$stream.="<td align=right>".number_format($dtBrtMsk[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$stream.="<td align=right>".number_format($dtBrtKlr[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$stream.="<td align=right>".number_format($dtJjg[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$stream.="<td align=right>".number_format($dtJjgSor[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$beratBersh[$lstAfd.$lstTgl.$lstSpb]=$dtBrtBrsh[$lstAfd.$lstTgl.$lstSpb]-$dtBrtSor[$lstAfd.$lstTgl.$lstSpb];
							$stream.="<td align=right>".number_format($dtBrtSor[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$stream.="<td align=right>".number_format($beratBersh[$lstAfd.$lstTgl.$lstSpb],0)."</td>";
							$stream.="</tr>";
							$totJjgKrm[$lstAfd]+=$dtJjg[$lstAfd.$lstTgl.$lstSpb];
							$totJjgSort[$lstAfd]+=$dtJjgSor[$lstAfd.$lstTgl.$lstSpb];
							$totBrtSort[$lstAfd]+=$dtBrtSor[$lstAfd.$lstTgl.$lstSpb];
							$totBrtBrsh[$lstAfd]+=$beratBersh[$lstAfd.$lstTgl.$lstSpb];
							if($no==$jmlhDt[$lstAfd]){
								$stream.="<tr  class=rowcontent>";
								$stream.="<td colspan=7  align=right><b>".$_SESSION['lang']['subtotal']." ".$lstAfd."</b></td>";
								$stream.="<td align=right>".number_format($totJjgKrm[$lstAfd],0)."</td>";
								$stream.="<td align=right>".number_format($totJjgSort[$lstAfd],0)."</td>";
								$stream.="<td align=right>".number_format($totBrtSort[$lstAfd],0)."</td>";
								$stream.="<td align=right>".number_format($totBrtBrsh[$lstAfd],0)."</td>";
								$stream.="</tr>";
								$grndJjkrm+=$totJjgKrm[$lstAfd];
								$grndJjSort+=$totJjgSort[$lstAfd];
								$grndBrtSort+=$totBrtSort[$lstAfd];
								$grndBrtBrsh+=$totBrtBrsh[$lstAfd];
							}
						}
					   }
					}
				}
			  }
			}	
		}
		$stream.="<tr  class=rowcontent>";
		$stream.="<td colspan=7 align=right><b>".$_SESSION['lang']['grnd_total']."</b></td>";
		$stream.="<td align=right>".number_format($grndJjkrm,0)."</td>";
		$stream.="<td align=right>".number_format($grndJjSort,0)."</td>";
		$stream.="<td align=right>".number_format($grndBrtSort,0)."</td>";
		$stream.="<td align=right>".number_format($grndBrtBrsh,0)."</td>";
		$stream.="</tr>";		
		$stream.="</tbody></table>";
	}
switch($param['proses']){
	 
        case 'preview':          
            echo $stream;
        break;
        case 'excel':          
                        $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
                        $qwe=date("YmdHms");
                        $nop_="timbanganEksternal_".$param['kbnId']."__".$qwe;
                        if(strlen($stream)>0)
                        {
                             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                             gzwrite($gztralala, $stream);
                             gzclose($gztralala);
                             echo "<script language=javascript1.2>
                                window.location='tempExcel/".$nop_.".xls.gz';
                                </script>"; 
                        }    
        break;
        case'getKodeAfd':
			$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
			$sAfd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kbnId']."' and tipe='AFDELING' order by namaorganisasi asc";
			//exit("error:".$sAfd);
			$qAfd=mysql_query($sAfd) or die(mysql_error($conn));
			while($rAfd=mysql_fetch_assoc($qAfd)){
				$optorg.="<option value='".$rAfd['kodeorganisasi']."'>".$rAfd['namaorganisasi']."</option>";
			}
        echo $optorg;
        break;
        default:
        break;
}
?>