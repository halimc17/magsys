<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script type=text/javascript src='js/zTools.js'></script>
<script type=text/javascript src='js/stockOpneme.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX();

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%'
	and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$res=mysql_query($str);

$optGudang='';
while($bar=mysql_fetch_object($res)){
    $optGudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
echo"<fieldset>
	<legend><b>Adjustment Stock</b></legend>
	<table>
		<tr><td>".$_SESSION['lang']['kodeorg']."</td><td>
		<select id=kodegudang>
		".$optGudang."
		</select></td></tr>
		<tr>
			<td>".$_SESSION['lang']['materialname']."</td><td><span id=kodebarang></span><input type=text id=namadisabled size=50 class=myinputtext disabled>
			<img src=images/search.png class=dellicon title='".$_SESSION['lang']['find']."' onclick=\"searchBarang('".$_SESSION['lang']['findmaterial']."','<fieldset><legend>".$_SESSION['lang']['findmaterial']."</legend>Find<input type=text class=myinputtext id=namabrg><button class=mybutton onclick=findBarang()>Find</button></fieldset><div id=container style=\'overflow:auto;height:352px\'></div>',event);\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>
				<select id='jenisAdjust' onclick='changeJenis()'>
					<option value='in'>".$_SESSION['lang']['masuk']."</option>
					<option value='out'>".$_SESSION['lang']['keluar']."</option>
				</select>
			</td>
		</tr>
		<tr><td>".$_SESSION['lang']['jumlah']."</td><td><input type=text id=jumlah value=0 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" size=5><span id=sat></span></td></tr>
		<tr><td>".$_SESSION['lang']['hargasatuan']."(Rp)</td><td><input type=text id=harga class=myinputtextnumber value=0 onkeypress=\"return angka_doang(event);\" size=12><div id='divChkNol' style='display:none'><input type=checkbox id=chkNol onchange='checkChkNol();'>* Beri tanda checklist untuk untuk membuat harga satuan menjadi 0 (nol)</div></td></tr>
		<tr>
			<td>No Transaksi Referensi</td>
			<td><input type=text id=notransreferensi maxlength=25 size=25 class=myinputtext></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td><input type=text id=keterangan maxlength=80 size=80 class=myinputtext></td>
		</tr>
	</table>
    <button class=mybutton onclick=saveAdjustment()>".$_SESSION['lang']['save']."</button>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>