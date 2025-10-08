<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=checkPostGet('notransaksi','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$jenisby=checkPostGet('jenisby','');
$jumlahhrd=checkPostGet('jumlahhrd',''); 
$method=checkPostGet('method','');
$jumlah=checkPostGet('jumlah',''); 


if($jumlahhrd=='')
  $jumlahhrd=0;


if($method=='update')
{
	$str="update ".$dbname.".sdm_pjdinasdt
	       set jumlahhrd=".$jumlahhrd."
	      where jenisbiaya=".$jenisby." and notransaksi='".$notransaksi."'
		  and tanggal=".$tanggal." and jumlah='".$jumlah."'"; 
	//echo "Error:".$str;	  
	if(mysql_query($str))
		{}
	else
   		{
		 echo " Gagal:".addslashes(mysql_error($conn));	 
		 exit(0);
		}
}
if($method=='finish')
{
  $strhrd="select sum(jumlah) as jumlah,sum(jumlahhrd) as jumlahhrd from ".$dbname.".sdm_pjdinasdt where  notransaksi='".$notransaksi."'"; 
  $reshrd=mysql_query($strhrd);
  $jmlahhrd=0;
  while($barhrd=mysql_fetch_object($reshrd))
  {
  	$jumlah=$bar->jumlah;
  	$jumlahhrd=$barhrd->jumlahhrd;
  }

  if($jumlah>0 and $jumlahhrd==0){
   	 exit("Warning: Nilai belum terisi...!");
  }else{
	$str="update ".$dbname.".sdm_pjdinasht
	       set statuspertanggungjawaban=1
	      where  notransaksi='".$notransaksi."'"; 
	if(mysql_query($str))
		{}
	else
   		{
   			echo " Gagal:".addslashes(mysql_error($conn));	 
		 exit(0);
		}
  }
}

$str="select a.*,b.keterangan as jns,b.id as bid from ".$dbname.".sdm_pjdinasdt a
      left join ".$dbname.".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
	  where a.notransaksi='".$notransaksi."'";
$res=mysql_query($str);
$no=0;
$total=0;
while($bar=mysql_fetch_object($res))
{
	$no+=1;
	echo"<tr class=rowcontent>
	     	<td>".$no."</td>
		    <td>".$bar->jns."</td>
                        <td>".tanggalnormal($bar->tanggal)."</td>
			<td>".$bar->keterangan."</td>
			<td align=right>".number_format($bar->jumlah,2,'.','.')."</td>
			<td align=right>
			<img src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('jumlahhrd".$bar->bid.$no."').value='".$bar->jumlah."'\">
			<input type=text id='jumlahhrd".$bar->bid.$no."' class=myinputtextnumber size=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this) value='".number_format($bar->jumlahhrd,2,'.',',')."'>
			<img src='images/save.png' title='Save' class=resicon onclick=saveApprvPJD('".$bar->bid."','".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."','".$bar->jumlah."','".$no."')></td>
			</tr>";
	$total+=$bar->jumlah;		
}
	echo"<tr class=rowcontent>
	     	<td colspan=4 align=center>TOTAL</td>
			<td align=right>".number_format($total,2,'.','.')."</td>
		    <td></td>
			</tr>";

?>