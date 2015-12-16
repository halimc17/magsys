<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script type="text/javascript" src="js/log_5subkelompokbarang.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
	//Get Kelompok Barang
	$optKlBarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sKlBarang="select kode,kelompok from ".$dbname.".log_5klbarang order by kelompok";
	$qKlBarang=mysql_query($sKlBarang) or die(mysql_error());
	while($rKlBarang=mysql_fetch_assoc($qKlBarang))
	{
		$optKlBarang.="<option value='".$rKlBarang['kode']."'>".$rKlBarang['kelompok']."</option>";
	}
	
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['subkelompokbarang'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kelompokbarang']?></td>
			<td>:</td>
			<td>
				<select id='kdKlBarang' onchange='getKodeSubKelompok()'>
					<?php echo $optKlBarang ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['kodesubkelompokbarang']?></td>
			<td>:</td>
			<td>
				<input type="text" id="kdSubKl" size="5" class="myinputtext" disabled='true' maxlength="5" />
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['namasubkelompokbarang'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="namaSubKl" class="myinputtext" maxlength="50" size='30'; />
			</td>
		</tr>
		<tr>
			<td colspan='2'></td>
			<td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=batal()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['subkelompokbarang'] ?></legend>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kelompokbarang']?></td>
				<td><?php echo $_SESSION['lang']['kodesubkelompokbarang'];?></td> 
				<td><?php echo $_SESSION['lang']['namasubkelompokbarang'];?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>