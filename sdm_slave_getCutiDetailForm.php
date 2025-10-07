<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');

$periode=$_POST['periode'];
$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];
$namakaryawan=$_POST['namakaryawan'];
$outstanding=$_POST['outstanding'];

echo "<fieldset>
		<label style='font-weight:bold'>Total Outstanding : ".$outstanding."</label> 
	</fieldset>";

echo"<fieldset><legend>".$_SESSION['lang']['form']."</legend>
    <table>
		<tr>
			<td>
				<input type=hidden class=myinputtext id=kodeorgJ  value='".$kodeorg."'>
				<input type=hidden class=myinputtext id=karyawanidJ value='".$karyawanid."'>
				<input type=hidden class=myinputtext id=periodeJ value='".$periode."'>
				".$_SESSION['lang']['namakaryawan']."
			</td>
			<td>
				:
			</td>
			<td>
				<input type=text class=myinputtext id=namakaryawan disabled value='".$namakaryawan."' size=25>
			</td>
		</tr>
		<tr>
			<td>
				".$_SESSION['lang']['tangalcuti']."
			</td>
			<td>
				:
			</td>
			<td>
				<input type=text class=myinputtext id=dariJ onmouseover=setCalendar(this) onkeypress=\"return false;\" size=15>
			</td>
		</tr>
		<tr>
			<td>
				".$_SESSION['lang']['tglcutisampai']."
			</td>
			<td>
				:
			</td>
			<td>
				<input type=text class=myinputtext id=sampaiJ onmouseover=setCalendar(this) onkeypress=\"return false;\" size=15>
			</td>
		</tr>
		<tr>
			<td>
				".$_SESSION['lang']['diambil']." (Hari)
			</td>
			<td>
				:
			</td>
			<td>
				<input type=text class=myinputtextnumber id=diambilJ  size=10 onkeypress=\"return angka_doang(event);\"  size=3 maxlength=2>
			</td>
		</tr>
		<tr>
			<td>
				".$_SESSION['lang']['keterangan']." (Hari)
			</td>
			<td>
				:
			</td>
			<td>
				<input type=text class=myinputtext id=keteranganJ onkeypress=\"return tanpa_kutip(event);\" size=35 maxlength=45>
			</td>
		</tr>
		<tr>
			<td colspan=2>
			</td>
			<td>
				<input type='hidden' id='outstanding' value='".$outstanding."'>
				<button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
			</td>
		</tr>
	</table>
	 </fieldset>
	<fieldset>
	<legend>".$_SESSION['lang']['cuti']."->[".$namakaryawan."] ".$_SESSION['lang']['periode'].":".$periode."</legend>
	<div style='width:750px;height:220px;overflow:scroll;' id=containerlist3>
	<table class=sortable cellspacing=1 border=0>
	<thead>
	<tr class=rowheader>
	   <td>
	      No
	   </td>
	   <td>".$_SESSION['lang']['tangalcuti']."</td>
	   <td>".$_SESSION['lang']['tglcutisampai']."</td>
	   <td>".$_SESSION['lang']['diambil']."</td>
	   <td>".$_SESSION['lang']['keterangan']."</td>
	   <td></td>
	</tr>
	</thead>
	<tbody>
	";
	//$str="select * from ".$dbname.".sdm_cutidt where karyawanid=".$karyawanid."
	//      and periodecuti='".$periode."' and kodeorg='".$kodeorg."'";
	$str="select * from ".$dbname.".sdm_cutidt where karyawanid=".$karyawanid."
	      and periodecuti='".$periode."'";
	$res=mysql_query($str);
	$no=0;
	$ttl=0;
	while($bar=mysql_fetch_object($res))	  
	{
		$no+=1;
		echo"<tr class=rowcontent id=barisJ".$no.">
	   <td>".$no."</td>
	   <td>".tanggalnormal($bar->daritanggal)."</td>
	   <td>".tanggalnormal($bar->sampaitanggal)."</td>
	   <td align=right>".$bar->jumlahcuti."</td>
	   <td>".$bar->keterangan."</td>
	   <td>
	   <img src='images/application/application_delete.png'  title='".$_SESSION['lang']['delete']."' class=resicon onclick=\"hapusData('".$periode."','".$karyawanid."','".$kodeorg."','".$bar->daritanggal."','".$bar->sampaitanggal."','barisJ".$no."',".$bar->jumlahcuti.");\">
	   </td>
	   </tr>";
	   $ttl+=$bar->jumlahcuti;
	}
		echo"<tr class=rowcontent>
	   <td></td>
	   <td>TOTAL</td>
	   <td></td>
	   <td align=right id=cellttl>".$ttl."</td>
	   <td></td>
	   <td></td>
	   </tr>";	
echo"</tbody>
     <tfoot>
	 </tfoot>
     </div>
	</fieldset> 
	"; 
?>