<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

	$pt=$_GET['pt'];
	$gudang=$_GET['gudang'];
	$periode=$_GET['periode'];
	$periodeKmrn=periodelalu($periode);
    $stream='';
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='ALL';
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
//$stream.= $str."____";
$res=mysql_query($str);
$currstart='';
$currend='';
while($bar=mysql_fetch_object($res)){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periodeKmrn."' ".$whr."";
//$stream.= $str."____";
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
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='CASH FLOW DIRECT' order by nourut";
$qstr=mysql_query($str) or die(mysql_error($conn));
while($rstr=mysql_fetch_assoc($qstr)){
	$dtArus[]=$rstr;
}
$rpdt=array();
$str1="select sum(debet) as debet,sum(kredit) as kredit,noaruskas,noakun from ".$dbname.".keu_jurnaldt_vw  where ".$tgl." ".$whr." and nojurnal not like '%/M/%'  group by noaruskas";//noakun,
$qstr1=mysql_query($str1) or die(mysql_error($conn));
while($rstr1=mysql_fetch_assoc($qstr1)){
	$rpdbt[$rstr1['noaruskas']]+=$rstr1['debet'];
	$rpkrt[$rstr1['noaruskas']]+=$rstr1['kredit'];
	$dbtperakun[$rstr1['noaruskas']]+=$rstr1['debet'];
	$krtperakun[$rstr1['noaruskas']]+=$rstr1['kredit'];
	$lstAkun[$rstr1['noaruskas']]=$rstr1['noakun'];
}
$str2="select sum(jumlah) as jumlah,noaruskas,noakun from ".$dbname.".keu_jurnaldt  where ".$tgl2." ".$whr." and  nojurnal not like '%/M/%' group by noaruskas";
//echo $str2;
$qstr1=mysql_query($str2) or die(mysql_error($conn));
while($rstr1=mysql_fetch_assoc($qstr1)){
	$stAwal[$rstr1['noaruskas']]+=$rstr1['jumlah'];
	$stAwalAkn[$rstr1['noaruskas']]+=$rstr1['jumlah'];
	$lstAkun[$rstr1['noaruskas']]=$rstr1['noakun'];
}
$sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7";
$qAkun=mysql_query($sAkun) or die(mysql_error($conn));
while($rAkun=mysql_fetch_assoc($qAkun)){
	$nmAkun[$rAkun['noakun']]=$rAkun['namaakun'];
}

$stream.=$_SESSION['lang']['aruskas']."<br>";
$stream.=$_SESSION['lang']['periode'].":".$periode."<br>";
$stream.=$_SESSION['lang']['pt'].":".$namapt."<br>
<table border=1>
<tr>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nomor']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
</tr>";
foreach($dtArus as $lstArus){
 	if($lstArus['tipe']=='Header'){
		$stream.="<tr>
			  <td colspan=6>".$lstArus['keterangandisplay']."</td>
			";
		$stream.="</tr>"; 		
 	}
 	if($lstArus['tipe']=='Detail'){
		$stream.="<tr class=rowcontent>
			  <td>".$lstArus['nourut']."</td>
			  <td>".$lstArus['keterangandisplay']."</td>
			  <td align=right>".number_format($stAwal[$lstArus['nourut']],2,'.',',')."</td>
			  <td align=right>".number_format($rpdbt[$lstArus['nourut']],2,'.',',')."</td>
			  <td align=right>".number_format($rpkrt[$lstArus['nourut']],2,'.',',')."</td>";
		$endbalance=$stAwal[$lstArus['nourut']]+$rpdbt[$lstArus['nourut']]-$rpkrt[$lstArus['nourut']];
		$stream.="<td align=right>".number_format($endbalance,2,'.',',')."</td>
			</tr>";
			if(!empty($lstAkun[$lstArus['nourut']])){
				// $stream.="<tr class=rowcontent>";
				// $stream.="<td colspan=6>";
				// $stream.="<table border=1 width=100%>";
				// $stream.="<tr>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
				// <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
				// </tr>";
				// foreach ($lstAkun[$lstArus['nourut']] as $key=>$akund) {
				// 	$stream.="<tr><td>".$akund."</td>
				// 	<td>".$nmAkun[$akund]."</td>
				// 	<td align=right>".number_format($stAwalAkn[$lstArus['nourut']][$akund],2,'.',',')."</td>
				// 	<td align=right>".number_format($dbtperakun[$lstArus['nourut']][$akund],2,'.',',')."</td>
				// 	<td align=right>".number_format($krtperakun[$lstArus['nourut']][$akund],2,'.',',')."</td>";
				// 	$endbalance=$stAwalAkn[$lstArus['nourut']][$akund]+$dbtperakun[$lstArus['nourut']][$akund]-$krtperakun[$lstArus['nourut']][$akund];
				// 	$stream.="<td align=right>".number_format($endbalance,2,'.',',')."</td></tr>";
				// }
				// $stream.="</table>";
				// $stream.="</td></tr>";

			}
	}
		
 }
 	$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
 	// echo $stream;
 	// exit();
$nop_="ArusKasLangsung";
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




// if($pt=='') { // pilihan: seluruhnya
// 		$str="select * from ".$dbname.".keu_5mesinlaporandt
// 		where namalaporan='CASH FLOW DIRECT'
// 		order by nourut 
// 		";
// 		$str1="select * from ".$dbname.".keu_jurnaldt 
// 		where substr(tanggal,1,7)<='".$periode."' 
// 		";
// 		$str2="select * from ".$dbname.".keu_jurnaldt 
// 		where noakun<='1110299' and 
// 		substr(tanggal,1,4)<'".substr($periode,0,4)."'  
// 		"; 
// } else
// {
// 	if($gudang=='')
// 	{
// 		$str="select a.*,c.induk from ".$dbname.".keu_5mesinlaporandt a
// 		left join ".$dbname.".organisasi c
// 		on substr(a.kodeorg,1,4)=c.kodeorganisasi
// 		where a.namalaporan='CASH FLOW DIRECT'
// 		order by a.nourut 
// 		";
// 		if($pt!=''){
// 		$str1="select a.*, b.induk from ".$dbname.".keu_jurnaldt a 
// 			left join ".$dbname.".organisasi b
// 			on a.kodeorg=b.kodeorganisasi
// 		where substr(a.tanggal,1,7)<='".$periode."' 
// 		and b.induk = '".$pt."'  
// 			";
// 		}else
// 		{
// 		$str1="select a.*, b.induk from ".$dbname.".keu_jurnaldt a 
// 			left join ".$dbname.".organisasi b
// 			on a.kodeorg=b.kodeorganisasi
// 		where substr(a.tanggal,1,7)<='".$periode."' 
// 			";
// 		}
// 		$str2="select * from ".$dbname.".keu_jurnaldt where substr(kodeorg,4,1)!=' ' and noakun<='1110299' and substr(tanggal,1,4)<'".substr($periode,0,4)."'  
// 		";
// 	}
// 	else
// 	{
// 		$str="select a.*,c.induk from ".$dbname.".keu_5mesinlaporandt a
// 		left join ".$dbname.".organisasi c
// 		on substr(a.kodeorg,1,4)=c.kodeorganisasi
// 		where a.namalaporan='CASH FLOW DIRECT'
// 		order by a.nourut 
// 		";
// 		$str1="select * from ".$dbname.".keu_jurnaldt 
// 		where substr(kodeorg,1,4) = '".$gudang."' and substr(tanggal,1,7)<='".$periode."' and substr(kodeorg,4,1)!=' '  
// 		";
// 		$str2="select * from ".$dbname.".keu_jurnaldt where substr(kodeorg,4,1)!=' ' and noakun<='1110299' and substr(tanggal,1,4)<'".substr($periode,0,4)."'  
// 		";
// 	}	
// }
// //=================================================
// $begbal = 0;
// /*
// if($periode=='')
// {
// 	$sawalQTY	='';
// 	$masukQTY	='';
// 	$keluarQTY	='';
// 	$kuantitas	=0;
// 	$res		=mysql_query($str);
// 	$no		=0;
// 	if(mysql_num_rows($res)<1)
// 	{
// 		$stream.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
// 	}
// 	else
// 	{
// 		$stream.=$_SESSION['lang']['aruskas'].":<br>
// 		<table border=1>
// 				    <tr>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
// 					</tr>";
// 		while($bar=mysql_fetch_object($res))
// 		{
// 			$no+=1;
// 			$periode=date('d-m-Y H:i:s');
// 			$kodebarang=$bar->kodebarang;
// 			$namabarang=$bar->namabarang; 
// 			$kuantitas =$bar->kuan;
// 			$nojurnal	=$bar->nojurnal;
// 			$tanggal    =$bar->tanggal;
// 			$noakun		=$bar->noakun;
// 			$namaakun	=$bar->namaakun;
// 			$keterangan =$bar->keterangan;
// 			$jumlah 	=$bar->jumlah;
// 		if ($jumlah >=0 ){
// 			$debet	= $jumlah;
// 			$kredit	= 0;
// 		}
// 		else{
// 			$debet	= 0;
// 			$kredit	= -$jumlah;
// 		}
// 			$stream.="<tr>
// 				  <td>".$no."</td>
// 				  <td>".$nojurnal."</td>
// 				  <td>".$tanggal."</td>
// 				  <td>".$noakun."</td>
// 				  <td>".$namaakun."</td>
// 				  <td>".$keterangan."</td>
// 				   <td align=right class=firsttd>".number_format($debet,2,'.','')."</td>
// 				   <td align=right class=firsttd>".number_format($kredit,2,'.','')."</td>
// 				</tr>";
// 		}
// 	  $stream.="</table>";	
// 	}
// }
// else
// */
// 	{
// $begbal = 0;
// 		$salakqty	=0;
// 		$masukqty	=0;
// 		$keluarqty	=0;
// 		$sawalQTY	=0;
// 		$t1balance = $t2balance = $t3balance = $t4balance = $t5balance = $t6balance = $t7balance = $t8balance = 0;
// 		$t1ebalance = $t2ebalance = $t3ebalance = $t4ebalance = $t5ebalance = $t6ebalance = $t7ebalance = $t8ebalance = $t9ebalance = 0;

// 	//
// 	$res=mysql_query($str);
// 	//$res1=mysql_query($str1);
// 	$res2=mysql_query($str2);
// 	$begbal = 0;
// 	while($bar=mysql_fetch_object($res2))
// 	{
// 		$begbal		+=$bar->debet;
// 		$begbal		-=$bar->kredit;
// 	}
// #print_r($begbal);
// #exit;
// 	$no = $counter = 0;
// 	$stawal = $stdebet = $stkredit = $stakhir = $sawal = 0;
// 	$tawal = $tdebet = $tkredit = $takhir = 0;
// 	$noakun1 = $namaakun1 = ' ';
// 	if(mysql_num_rows($res)<1)
// 	{
// 		$stream.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
// 	}
// 	else
// 	{
// 		$stream.=$_SESSION['lang']['aruskas']." DIRECT: ".$namapt."<br>
// 		<table border=1>
// 				    <tr>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nomor']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
// 					  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
// 					</tr>";
// 	while($bar=mysql_fetch_object($res))
// 	{
// 		$no+=1;
// 		$tanggal    		=$bar->tanggal;
// 		$noakun				=$bar->noakun;
// 		$nourut				=$bar->nourut;
// 		$nojurnal			=$bar->nojurnal;
// 		$namaakun			=$bar->namaakun;
// 		$noakundari			=$bar->noakundari;
// 		$noakunsampai		=$bar->noakunsampai;
// 		$tipe				=$bar->tipe;
// 		$keterangandisplay 	=$bar->keterangandisplay;
// 		$variableoutput 	=$bar->variableoutput;
// 		if ($periode ==$bar->periode)
// 		{
// 			$stdebet	+=$bar->debet;
// 			$stkredit	+=$bar->kredit;
// 		}
// 		else
// 		{
// 			$stawal 	+= $bar->debet - $bar->kredit;	
// 		}
// 		$stakhir		=$stawal + $stdebet - $stkredit;	
// 		if ($tipe=='Header'){
// 		$stream.="<tr>
// 			  <td colspan=4>".$keterangandisplay."</td>
// 			</tr>"; 		
// 		}	
// 		if ($tipe=='Detail'){
// /*
// 		$stream.="<tr>
// 			  <td>".' '."</td>
// 			  <td>".' -- '.$keterangandisplay."</td>
// 			   <td align=right class=firsttd>".number_format($balance,2,'.','')."</td>
// 			   <td align=right class=firsttd>".number_format($endbalance,2,'.','')."</td>
// 			</tr>"; 		

// */
// 		$res1=mysql_query($str1);
// 		$balance = $endbalance = 0; 
// 		while($bar1=mysql_fetch_object($res1))
// 		{
// 			$noakun1		=$bar1->noaruskas;
// 			$jumlah1		=$bar1->jumlah;
// 			if ($noakun1==$nourut)
// 			{
// 				$balance += $jumlah1;
// 				$endbalance += $jumlah1;
// 			}
// 		}
// 		if ($nourut==51000){
// 			$balance = $begbal;			
// 			$endbalance = $begbal;
// 		} 
// 		if ($nourut==52000){
// #			$balance = $t2balance + $begbal;
// 			$balance = $xbalance + $begbal;
// #			$endbalance = $t2ebalance + $begbal;
// 			$endbalance = $xbalance + $begbal;
// 		}

// 		$stream.="<tr class=rowcontent style='cursor:pointer;'>
// 			  <td>".$nourut."</td>
// 			  <td>".$keterangandisplay."</td>
// 			  <td align=right>".number_format($balance,2,'.',',')."</td>
// 			  <td align=right>".number_format($endbalance,2,'.',',')."</td>
// 			</tr>";
// 		$xbalance +=$balance;
// 		$t1balance +=$balance;
// 		$t2balance +=$balance;
// 		$t3balance +=$balance;
// 		$t4balance +=$balance;
// 		$t5balance +=$balance;
// 		$t6balance +=$balance;
// 		$t7balance +=$balance;
// 		$t8balance +=$balance;
// 		$t9balance +=$balance;
// 		$t1ebalance += $endbalance;
// 		$t2ebalance += $endbalance;
// 		$t3ebalance += $endbalance;
// 		$t4ebalance += $endbalance;
// 		$t5ebalance += $endbalance;
// 		$t6ebalance += $endbalance;
// 		$t7ebalance += $endbalance;
// 		$t8ebalance += $endbalance;
// 		$t9ebalance += $endbalance;
// 		$balance = $endbalance = 0;
// 		}	
// 		if ($tipe=='Total'){
// /*
// 		$stream.="<tr>
// 			  <td>".' '."</td>
// 			  <td>".' --- '.$keterangandisplay."</td>
// 			   <td align=right class=firsttd>".number_format($balance,2,'.','')."</td>
// 			   <td align=right class=firsttd>".number_format($endbalance,2,'.','')."</td>
// 			</tr>"; 		

// */
// 		$stream.="<tr>
// 			  <td>&nbsp;</td>
// 			  <td>".$keterangandisplay."</td>
// 			";
// 			if ($variableoutput == '1'){
// 			$stream.="
// 			  <td align=right>".number_format($t1balance,2,'.',',')."</td>
// 			  <td align=right>".number_format($t1ebalance,2,'.',',')."</td>
// 			";
// 			$t1balance = $t1ebalance = 0;
// 			}
// 			if ($variableoutput == '2'){
// 			$stream.="
// 			  <td align=right>".number_format($t2balance,2,'.',',')."</td>
// 			  <td align=right>".number_format($t2ebalance,2,'.',',')."</td>
// 			";
// 				$t1balance = $t1ebalance = 0;
// #				$t2balance = $t2ebalance = 0;
// 			}
// 			if ($variableoutput == '9'){
// 			$stream.="
// 			  <td align=right>".number_format($t9balance,2,'.',',')."</td>
// 			  <td align=right>".number_format($t9ebalance,2,'.',',')."</td>
// 			";
// 				$t1balance = $t1ebalance = $t2balance = $t2ebalance = $t3balance = $t3ebalance = 0;
// 				$t4balance = $t4ebalance = $t5balance = $t5ebalance = $t6balance = $t6ebalance = 0;
// 				$t7balance = $t7ebalance = $t8balance = $t8ebalance = $t9balance = $t9ebalance = 0;
// 			}
// 		$stream.="</tr>"; 		
// 		}	
// //if($no==50){		
// //$stream.=($stream." ".$str1);
// //exit;}
// 	}
// 	$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
//   }
// }	

// $nop_="ArusKasLangsung";
// if(strlen($stream)>0)
// {
// if ($handle = opendir('tempExcel')) {
//     while (false !== ($file = readdir($handle))) {
//         if ($file != "." && $file != "..") {
//             @unlink('tempExcel/'.$file);
//         }
//     }	
//    closedir($handle);
// }
//  $handle=fopen("tempExcel/".$nop_.".xls",'w');
//  if(!fwrite($handle,$stream))
//  {
//   $stream.= "<script language=javascript1.2>
//         parent.window.alert('Can't convert to excel format');
//         </script>";
//    exit;
//  }
//  else
//  {
//   $stream.= "<script language=javascript1.2>
//         window.location='tempExcel/".$nop_.".xls';
//         </script>";
//  }
// fclose($handle);
// }
?>