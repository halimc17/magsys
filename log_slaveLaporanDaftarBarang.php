<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$kelbrg=$_POST['kelbrg'];
$gdg=$_POST['gdg'];
$txtfind=$_POST['txtcari'];

OPEN_BOX();
echo "<img src='images/excel.jpg' class=resicon title='Cetak ke MS.Excel' onclick=\"fisikKeExcel(event,'log_laporanDaftarBarang_Excel.php')\">";
echo"<div style='overflow:scroll; height:420px;'><table class=sortable cellspacing=1 border=0>
<thead>
	<tr class=rowheader>
		<td>No.</td>
		<td align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodekelompok'])."</td>
		<td>".$_SESSION['lang']['materialcode']."</td>
		<td>".$_SESSION['lang']['materialname']."</td>
		<td>".$_SESSION['lang']['satuan']."</td>
		<td align=center>".str_replace(" ","<br>",$_SESSION['lang']['minstok'])."</td>
		<td align=center>".str_replace(" ","<br>",$_SESSION['lang']['nokartubin'])."</td>
		<td>".$_SESSION['lang']['konversi']."</td>	  
		<td>".$_SESSION['lang']['tglmaxin']."</td>
		<td>".$_SESSION['lang']['tglmaxout']."</td>
	</tr>  
</thead><tbody>";

$str="select a.kelompokbarang,a.kodebarang,a.namabarang,a.satuan,a.konversi,a.inactive,
	b.nokartubin,b.minstok from ".$dbname.".log_5masterbarang a
left join ".$dbname.".log_5kartubin b on a.kodebarang=b.kodebarang and b.kodegudang='".$gdg."'
where (a.namabarang like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%')
	and a.kelompokbarang like '%".$kelbrg."%'
order by namabarang";

$strin="select min(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(1,3) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' group by namabarang";

$strout="select max(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(5,7) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' 
group by namabarang";

$resin=mysql_query($strin);
$in = array();
while($barin=mysql_fetch_object($resin)){
	$in[$barin->kodebarang]=tanggalnormal($barin->tgl);
}
$resout=mysql_query($strout);
$out = array();
while($barout=mysql_fetch_object($resout)){
	$out[$barout->kodebarang]=tanggalnormal($barout->tgl);
}
echo mysql_error($conn);
$res=mysql_query($str);

$no=0;
if(mysql_query($str)) {
	while($bar=mysql_fetch_object($res)) {
		$stru="select * from ".$dbname.".log_5photobarang where kodebarang='".$bar->kodebarang."'";
		if(mysql_num_rows(mysql_query($stru))>0) {
			$adx="<img src=images/zoom.png class=resicon height=16px title='View detail'  onclick=viewDetailbarang('".$bar->kodebarang."',event)> <img src=images/tool.png class=resicon height=16px title='Edit Detail'  onclick=editDetailbarang('".$bar->kodebarang."',event)>";
		} else {
			$adx="<img src=images/tool.png class=resicon height=16px title='Edit Detail' onclick=editDetailbarang('".$bar->kodebarang."',event)>";
		}
		
		$no+=1;
		echo"<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$bar->kelompokbarang."</td>
			<td>".$bar->kodebarang."</td>
			<td>".$bar->namabarang."</td>
			<td>".$bar->satuan."</td>
			<td align=right>".$bar->minstok."</td>
			<td>".$bar->nokartubin."</td>
			<td>".$bar->konversi."</td>
			<td>".(isset($in[$bar->kodebarang])? $in[$bar->kodebarang]: '')."</td>
			<td>".(isset($out[$bar->kodebarang])? $out[$bar->kodebarang]: '')."</td>    
		</tr>";
	}
}
echo"</tbody>
	</table>
</div>";
CLOSE_BOX();