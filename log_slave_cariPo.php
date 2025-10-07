<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
if(isTransactionPeriod())//check if transaction period is normal
{
   $nopo=$_POST['nopo'];
   echo"<table cellspacing=1 border=0 class=data>
        <thead>
		<tr class=rowheader><td>No</td>
		    <td>".$_SESSION['lang']['nopo']."</td>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>".$_SESSION['lang']['purchaser']."</td>
		</tr>
		</thead>
		</tbody>";
  $str="select * from ".$dbname.".log_poht where nopo like '%".$nopo."%'
		and kodeorg = '".$_SESSION['org']['kodeorganisasi']."'
        order by tanggal desc,nopo desc";
  $res=mysql_query($str);
  $no=0;
  while($bar=mysql_fetch_object($res))
  {
   //ambil userid purchaser
   $purchaser='';
   if(!empty($bar->karyawanid))
   {
	   $str="select namauser from ".$dbname.".user where karyawanid=".$bar->karyawanid;
	   $resv=mysql_query($str);
	   while($barv=mysql_fetch_object($resv))
	   {
	   	$purchaser=$barv->namauser;
	   }
   }
  	$no+=1;
	echo"
		<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickPo('".$bar->nopo."')><td>".$no."</td>
		    <td>".$bar->nopo."</td>
			<td>".tanggalnormal($bar->tanggal)."</td>
			<td>".$purchaser."</td>
		</tr>
	";
	
	
  }	 	
					
	echo"</tbody>
	     <tfoot>
		 </tfoot>
		 </table>";		
}
else
{
	echo " Error: Transaction Period missing";
}
?>