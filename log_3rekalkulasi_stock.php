<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/log_3rekalkulasi_stock.js></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['transaksigudang']).'</b>');

//=================ambil unit;  
//if($_SESSION['empl']['tipelokasitugas']=='HOLDING') 
$str="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
      where tipe like 'GUDANG%' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'
	  order by kodeorganisasi"; 
//else
//$str="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
//      where tipe= 'GUDANG' and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%'
//	  order by namaorganisasi";

$res=mysql_query($str);
//$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$optunit="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
	$optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
/*
	 <tr>
	   <td>".$_SESSION['lang']['periode']."</td>
	   <td><select id=periode onchange=hideById('printPanel')>".$optper."</select></td>
	 </tr>

*/
echo"<fieldset>
     <legend>Stock Recalculation</legend>
	 <table cellspacing=1 border=0><tr>
	   <td>".$_SESSION['lang']['daftargudang']."</td>
	   <td>
	     <select id=unit style='width:150px;' onchange=ambilPeriode(this.options[this.selectedIndex].value)>".$optunit."</select></td>
	 </tr>
	 <tr>
	   <td colspan=2><button class=mybutton onclick=getTransaksiGudang()>".$_SESSION['lang']['proses']."</button></td>
	 </tr></table>
                      <fieldset style='width:350px;'>
                          Rekalkulasi stok akan melihat dan memperbaiki konsistensi nilai transaksi dan saldo akhir fisik barang.
                      </fieldset>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','Result:');
echo"<span id=printPanel> 
	 </span>    
	 <div style='width:100%;height:359px;overflow:scroll;'  id=container>
     </div>";
CLOSE_BOX();
close_body();
?>