<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/pmn_hargapasar.js'></script>
<?php
$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##status##ffa##mni##proses";
include('master_mainMenu.php');
OPEN_BOX();
$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optKodeSat=$optKode=$optPasar=$optBrg;
$sBrng="select distinct kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='400' order by namabarang asc";
$qBrng=mysql_query($sBrng) or die(mysql_error($conn));
while($rBarang=mysql_fetch_assoc($qBrng))
{
    $optBrg.="<option value='".$rBarang['kodebarang']."'>".$rBarang['namabarang']."</option>";
}
$sData="select distinct kode  from ".$dbname.".setup_matauangrate order by kode asc";
$qData=mysql_query($sData) or die(mysql_error($conn));
$optKode.="<option value='IDR'>IDR</option>";
while($rData=mysql_fetch_assoc($qData))
{
    $optKode.="<option value='".$rData['kode']."'>".$rData['kode']."</option>";
}
/*$arrenum=getEnum($dbname,'pmn_hargapasar','pasar');
foreach($arrenum as $key=>$val)
{
	$optGoldar.="<option value='".$key."'>".$val."</option>";
} */

$iPasar="select * from ".$dbname.".pmn_5pasar order by pasar asc ";

$nPasar=  mysql_query($iPasar) or die (mysql_errn($conn));
while($dPasar=  mysql_fetch_assoc($nPasar))
{
    $optPasar.="<option value='".$dPasar['pasar']."'>".$dPasar['pasar']."</option> ";
}

$arrSatuan=array("KG","TON");
foreach($arrSatuan as $der)
{
    $optKodeSat.="<option value='".$der."'>".$der."</option>";
}
$optStatus="<option value='Best Bidder'>Best Bidder</option>";
$optStatus.="<option value='Price Idea'>Price Idea</option>";
$optStatus.="<option value='Traded'>Traded</option>";

echo"<div class='card' style='max-width:400px;'>
     <div class='card-header'><strong>".$_SESSION['lang']['hargapasar']."</strong></div>
     <div class='card-body'>
	 <table class='table table-sm table-borderless'>
	 <tr>
	   <td style='width:140px;'>".$_SESSION['lang']['tanggal']."</td>
	   <td><input type=text class='form-control form-control-sm' id=tglHarga onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['komoditi']."</td>
	   <td><select id=kdBarang class='form-select form-select-sm'>".$optBrg."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['satuan']."</td>
	   <td><select id=satuan class='form-select form-select-sm'>".$optKodeSat."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['pasar']."</td>
	   <td><select id=idPasar class='form-select form-select-sm'>".$optPasar."</select></td>
	 </tr>
	  <tr>
	   <td>".$_SESSION['lang']['matauang']."</td>
	   <td><select id=idMatauang class='form-select form-select-sm'>".$optKode."</select></td>
	 </tr>
          <tr>
	   <td>".$_SESSION['lang']['harga']."</td>
	   <td><input type=text class='form-control form-control-sm' id=hrgPasar onkeypress=\"return angka_doang(event);\" /> </td>
	 </tr>

         <tr>
	   <td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['harga']."</td>
	   <td><select id=status class='form-select form-select-sm'>".$optStatus."</select></td>
	 </tr>
         <tr>
	   <td>FFA</td>
	   <td><input type=text class='form-control form-control-sm' id=ffa onkeypress=\"return angka_doang(event);\" /> </td>
	 </tr>

        <tr>
	   <td>M & I</td>
	   <td><input type=text class='form-control form-control-sm' id=mni onkeypress=\"return angka_doang(event);\" /> </td>
	 </tr>

	 </table>
	 <input type=hidden value=insert id=proses>
	 <div class='mt-2'>
	 <button class='btn btn-primary btn-sm me-1' onclick=saveFranco('pmn_slave_hargapasar','".$arr."')><i class='bi bi-save'></i> ".$_SESSION['lang']['save']."</button>
	 <button class='btn btn-secondary btn-sm' onclick=cancelIsi()><i class='bi bi-x-circle'></i> ".$_SESSION['lang']['cancel']."</button>
	 </div>
     </div>
     </div><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();

echo"<div class='card'>
     <div class='card-header'><strong>".$_SESSION['lang']['list']."</strong></div>
     <div class='card-body'>";
echo"<div class='row g-2 mb-3 align-items-center'>
     <div class='col-auto'><label class='col-form-label col-form-label-sm'>".$_SESSION['lang']['tanggal'].":</label></div>
     <div class='col-auto'><input type=text class='form-control form-control-sm' id=tglCri onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 style='width:120px;' /></div>
     <div class='col-auto'><label class='col-form-label col-form-label-sm'>".$_SESSION['lang']['komoditi'].":</label></div>
     <div class='col-auto'><select id=kdBrgCari class='form-select form-select-sm' style='width:180px;'>".$optBrg."</select></div>
     <div class='col-auto'><label class='col-form-label col-form-label-sm'>".$_SESSION['lang']['pasar'].":</label></div>
     <div class='col-auto'><select id=idPsrCari class='form-select form-select-sm' style='width:150px;'>".$optPasar."</select></div>
     <div class='col-auto'><button class='btn btn-primary btn-sm' onclick=cariTransaksi()><i class='bi bi-search'></i> ".$_SESSION['lang']['find']."</button></div>
     </div>";
echo"<div class='table-responsive'>
    <table class='table table-striped table-hover table-sm'>
     <thead class='table-dark'>
	  <tr>
	   <th>No</th>
	   <th>".$_SESSION['lang']['tanggal']."</th>
	   <th>".$_SESSION['lang']['komoditi']."</th>
	   <th>".$_SESSION['lang']['satuan']."</th>
	   <th>".$_SESSION['lang']['pasar']."</th>
	   <th>".$_SESSION['lang']['matauang']."</th>
           <th>".$_SESSION['lang']['harga']."</th>
           <th>".$_SESSION['lang']['status']." ".$_SESSION['lang']['harga']."</th>
               <th>FFA</th>
               <th>M & I</th>
	   <th>Action</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";

echo"</tbody>
     </table>
     </div>
     </div>
     </div>";
CLOSE_BOX();
echo close_body();
?>