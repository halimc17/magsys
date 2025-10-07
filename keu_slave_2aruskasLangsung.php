<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

	$pt=$_POST['pt'];
	$gudang=$_POST['gudang'];
	$periode=$_POST['periode'];
	$periodeKmrn=periodelalu($periode);
	$nourut=$_POST['nourut'];
#print_r($_POST);
#exit;	
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$namapt=strtoupper($bar->namaorganisasi);
}
$whr=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
if($gudang!=''){
	$whr=" and kodeorg='".$gudang."'";
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periode."' ".$whr."";
//echo $str."____";
$res=mysql_query($str);
$currstart='';
$currend='';
while($bar=mysql_fetch_object($res)){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periodeKmrn."' ".$whr."";
//echo $str."____";
$res=mysql_query($str);
$paststart='';
$pastend='';
while($bar=mysql_fetch_object($res)){
    $paststart=$bar->tanggalmulai;
    $pastend=$bar->tanggalsampai;
}
$tgl="tanggal between '".$currstart."' and '".$currend."'";
if(substr($periode,5,2)=='01'){
	$thn=intval(substr($periode,0,4)-1);
}else{
	$thn=intval(substr($periode,0,4));
}
$tgl2="tanggal>='".$thn."-01-01' and tanggal<='".$pastend."'";
$dtArus=array();
if($nourut==''){
		$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='CASH FLOW DIRECT' order by nourut";
		$qstr=mysql_query($str) or die(mysql_error($conn));
		while($rstr=mysql_fetch_assoc($qstr)){
			$dtArus[]=$rstr;
		}
		$rpdt=array();
		$str1="select sum(debet) as debet,sum(kredit) as kredit,noaruskas from ".$dbname.".keu_jurnaldt_vw  where ".$tgl." ".$whr."  and nojurnal not like '%/M/%' group by noaruskas";
		$qstr1=mysql_query($str1) or die(mysql_error($conn));
		while($rstr1=mysql_fetch_assoc($qstr1)){
			$rpdbt[$rstr1['noaruskas']]+=$rstr1['debet'];
			$rpkrt[$rstr1['noaruskas']]+=$rstr1['kredit'];
		}
		$str2="select sum(jumlah) as jumlah,noaruskas from ".$dbname.".keu_jurnaldt  where ".$tgl2." ".$whr."  and nojurnal not like '%/M/%' group by noaruskas";
		$qstr1=mysql_query($str2) or die(mysql_error($conn));
		while($rstr1=mysql_fetch_assoc($qstr1)){
			$stAwal[$rstr1['noaruskas']]+=$rstr1['jumlah'];
		}
		 foreach($dtArus as $lstArus){
				 	if($lstArus['tipe']=='Header'){
						echo"<tr>
							  <td colspan=6>".$lstArus['keterangandisplay']."</td>
							";
						echo"</tr>"; 		
				 	}
				 	$dert="";
				 	if($lstArus['tipe']=='Detail'){
				 		if(($rpdbt[$lstArus['nourut']]!=0)||($rpkrt[$lstArus['nourut']]!=0)){
				 				$dert="onclick=\"getDetailData('".$lstArus['nourut']."',1)\" style='cursor:pointer' title=\"Detail Data ".$lstArus['keterangandisplay']."\"";
				 		}
				 		$endbalance=($stAwal[$lstArus['nourut']]+$rpdbt[$lstArus['nourut']])-$rpkrt[$lstArus['nourut']];
							echo"<tr class=rowcontent ".$dert." >
								  <td>".$lstArus['nourut']."</td>
								  <td>".$lstArus['keterangandisplay']."</td>
								  <td align=right>".number_format($stAwal[$lstArus['nourut']],2,'.',',')."</td>
								  <td align=right>".number_format($rpdbt[$lstArus['nourut']],2,'.',',')."</td>
								  <td align=right>".number_format($rpkrt[$lstArus['nourut']],2,'.',',')."</td>
								  <td align=right>".number_format($endbalance,2,'.',',')."</td>
								</tr>";
						if(($rpdbt[$lstArus['nourut']]!=0)||($rpkrt[$lstArus['nourut']]!=0)){
							echo"<tr><td colspan='6' valign=top><div  id='".$lstArus['nourut']."' style='display:none'></div></td></tr>";
						}
					}
		 }
 }else{

 	$sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7";
	$qAkun=mysql_query($sAkun) or die(mysql_error($conn));
	while($rAkun=mysql_fetch_assoc($qAkun)){
		$nmAkun[$rAkun['noakun']]=$rAkun['namaakun'];
	}
	$str1="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnaldt_vw  where ".$tgl." ".$whr." and noaruskas='".$nourut."' and nojurnal not like '%/M/%' group by noakun";
	$qstr1=mysql_query($str1) or die(mysql_error($conn));
	while($rstr1=mysql_fetch_assoc($qstr1)){
		$rpdbt[$rstr1['noakun']]+=$rstr1['debet'];
		$rpkrt[$rstr1['noakun']]+=$rstr1['kredit'];
		$lstAkun[$rstr1['noakun']]=$rstr1['noakun'];
	}
	$str2="select sum(jumlah) as jumlah,noakun from ".$dbname.".keu_jurnaldt  where ".$tgl2." ".$whr."  and noaruskas='".$nourut."' and nojurnal not like '%/M/%' group by noakun";
	$qstr1=mysql_query($str2) or die(mysql_error($conn));
	while($rstr1=mysql_fetch_assoc($qstr1)){
		$stAwal[$rstr1['noakun']]+=$rstr1['jumlah'];
		$lstAkun[$rstr1['noakun']]=$rstr1['noakun'];
	}
	echo"<table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>";
	echo"<tr>
		<td align=center>".$_SESSION['lang']['noakun']."</td>
		<td align=center>".$_SESSION['lang']['namaakun']."</td>
		<td align=center>".$_SESSION['lang']['saldoawal']."</td>
		<td align=center>".$_SESSION['lang']['debet']."</td>
			<td align=center>".$_SESSION['lang']['kredit']."</td>
			<td align=center>".$_SESSION['lang']['saldoakhir']."</td>
			</tr></thead><tbody>  ";
	foreach($lstAkun as $dtNoakun){
		echo"<tr class=rowcontent ".$dert.">
			  <td>".$dtNoakun."</td>
			  <td>".$nmAkun[$dtNoakun]."</td>
			  <td align=right>".number_format($stAwal[$dtNoakun],2,'.',',')."</td>
			  <td align=right>".number_format($rpdbt[$dtNoakun],2,'.',',')."</td>
			  <td align=right>".number_format($rpkrt[$dtNoakun],2,'.',',')."</td>";
			  //
		$endbalance=($stAwal[$dtNoakun]+$rpdbt[$dtNoakun])-$rpkrt[$dtNoakun];
		echo"<td align=right>".number_format($endbalance,2,'.',',')."</td>
			</tr>";
	}
	echo"</tbody></table>";
}

?>