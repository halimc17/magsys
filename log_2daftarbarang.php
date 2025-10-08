<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX(); //1 O

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_2daftarbarang.js"></script>
<div id="action_list">
<?php
if($_SESSION['language']=='EN'){
    $zz='kelompok1 as kelompok';
}
else{
    $zz='kelompok';
}
//pengambilan kelompok barang dari table kelompok barang
$str="select kode,".$zz." from ".$dbname.".log_5klbarang order by kode asc";
$res=mysql_query($str);
$optkelompok="<option value=''></option>";
//create option search
$optsearch="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($res))
{
	$optkelompok.="<option value='".$bar->kode."'>".$bar->kelompok." [ ".$bar->kode." ] </option>";
        $optsearch.="<option value='".$bar->kode."'>".$bar->kelompok." [ ".$bar->kode." ] </option>";
}
//pengambilan kelompok gudang dari table organisasi
$strx="select * from ".$dbname.".organisasi where tipe='GUDANG' or tipe='GUDANGTEMP'";
$resx=mysql_query($strx);
//$optgudang="<option value=''></option>";
//create option search
$optsearchgdg="<option value=All>".$_SESSION['lang']['all']."</option>";
$optgudang='';
while($barx=mysql_fetch_object($resx))
{
    $optgudang.="<option value='".$barx->kodeorganisasi."'>".$barx->namaorganisasi." [ ".$barx->kodeorganisasi." ] </option>";
    $optsearchgdg.="<option value='".$barx->kodeorganisasi."'>".$barx->namaorganisasi." [ ".$barx->kodeorganisasi." ] </option>";
}

echo "<fieldset style='width:1100px;background-color:#A9D4F4'>
<legend><b>".$_SESSION['lang']['find']."</b></legend>
Text <input type=text id=txtcari class=myinputtext size=40 onkeypress=\"return tanpa_kutip(event);\" maxlength=30>
".$_SESSION['lang']['on']."   ".$_SESSION['lang']['kelompokbarang']."' <select id=kelbrg>".$optsearch."</select>
".$_SESSION['lang']['gudang']."' <select id=gdg>".$optgudang."</select>
<button class=mybutton onclick=cariBarang()>".$_SESSION['lang']['find']."</button>
</fieldset>";
?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_daftarbrg>
<?php OPEN_BOX();

?>
<fieldset>
<legend>
<?php 

echo $_SESSION['lang']['list'];?></legend>
<div style="overflow:scroll; height:420px;">
	 <table class="sortable" cellspacing="1" border="0">
	 <thead>
        <tr class=rowheader>
        <td>No.</td>
        <?php
            echo"
                <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodekelompok'])."</td>
              <td>".$_SESSION['lang']['materialcode']."</td>
              <td>".$_SESSION['lang']['materialname']."</td>
              <td>".$_SESSION['lang']['satuan']."</td>
              <td>".$_SESSION['lang']['produk']."</td>              
              <td>".$_SESSION['lang']['keterangan']."</td>
              <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['minstok'])."</td>
              <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['nokartubin'])."</td>
              <td>".$_SESSION['lang']['konversi']."</td>
              <td>".$_SESSION['lang']['tidakaktif']."</td>	  
              <td>".$_SESSION['lang']['tglmaxin']."</td>
              <td>".$_SESSION['lang']['tglmaxout']."</td>";
        ?>
        </tr>
	 </thead>
	 <tbody id="contain">
	<!--script>loadData()</script-->
	  </tbody>
	 <tfoot>
	 </tfoot>
	 </table></div>
</fieldset
><?php
CLOSE_BOX();
?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid']?>" />

<?php
echo close_body();
?>