<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script type="text/javascript" src="js/sdm_5bpjs.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
	//Get Enum lokasi bpjs
	$arrenum=getEnum($dbname,'sdm_5bpjs','lokasibpjs');
	foreach($arrenum as $key=>$val)
	{
		$optLokasi.="<option value='".$key."'>".$val."</option>";
	}
	
	//Get Enum jeni bpjs
	$arrenum=getEnum($dbname,'sdm_5bpjs','jenisbpjs');
	foreach($arrenum as $key=>$val)
	{
		$optJenis.="<option value='".$key."'>".$val."</option>";
	}
?>
<fieldset style='width:550px;float:left'>
	<legend><?php echo $_SESSION['lang']['list']." BPJS"; ?></legend>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td style='text-align:center;'><?php echo $_SESSION['lang']['nourut']?></td>
				<td style='text-align:center;'><?php echo $_SESSION['lang']['lokasitugas']?></td>
				<td style='text-align:center;'>Jenis BPJS</td> 
				<td style='text-align:center;'>Beban Karyawan(%)</td>
				<td style='text-align:center;'>Beban Perusahaan(%)</td>
				<td style='text-align:center;'>Max Gaji(Rp)</td>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</fieldset>
<fieldset id='editBPJS' style='width:300px;display:none'>
	<legend>Edit BPJS</legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['lokasitugas']?></td>
			<td>:</td>
			<td>
				<select id='lokasibpjs' name='lokasibpjs' disabled>
					<?php echo $optLokasi ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Jenis BPJS</td>
			<td>:</td>
			<td>
				<select id='jenisbpjs' name='jenisbpjs' disabled>
					<?php echo $optJenis ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Beban Karyawan(%)</td>
			<td>:</td>
			<td><input type="text" id="bebankaryawan" class="myinputtextnumber" onKeyPress="return angka_doang(event);" size="10" /></td>
		</tr>
		<tr>
			<td>Beban Perusahaan(%)</td>
			<td>:</td>
			<td><input type="text" id="bebanperusahaan" class="myinputtextnumber" onKeyPress="return angka_doang(event);" size="10" /></td>
		</tr>
		<tr>
			<td>Max Gaji(Rp)</td>
			<td>:</td>
			<td><input type="text" id="maxgaji" class="myinputtextnumber" onKeyPress="return angka_doang(event);" size="10" /></td>
		</tr>
		<tr>
			<td colspan="3">
			<input type="hidden" value="update" id="method"  />
			<button class=mybutton onclick=simpadendapanen()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=btldendapanen()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>

<?php 
	CLOSE_BOX();
	close_body();
?>