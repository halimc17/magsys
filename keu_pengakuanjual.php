<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript" src="js/keu_pengakuanjual.js" /></script>
<?php 
	OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['pengakuanjual'])."</b>"); 
	$sListPabrik = "select kodeorganisasi as millcode, namaorganisasi from ".$dbname.".organisasi where 
					tipe='PABRIK'";
	$rListPabrik = fetchData($sListPabrik);
	$optListPabrik[''] = $_SESSION['lang']['all'];
	foreach($rListPabrik as $key=>$row) {
		$optListPabrik[$row['millcode']] = $row['millcode']." - ".$row['namaorganisasi'];
	}
	$optPt[''] = $_SESSION['lang']['all'];
	$optListBrg[''] = $_SESSION['lang']['all'];
	$sBrg="select distinct(a.kodebarang) as millcode, b.namabarang as namaorganisasi 
					from ".$dbname.".pabrik_timbangan a
					left join ".$dbname.".log_5masterbarang b
					on a.kodebarang = b.kodebarang";
	$rBrgPabrik = fetchData($sBrg);
	foreach($rBrgPabrik as $key=>$row) {
		$optListBrg[$row['millcode']] = $row['millcode']." - ".$row['namaorganisasi'];
	}
?>
<fieldset style='margin-top:10px'>
	<legend style='font-weight:bold'>Form</legend>
	<table>
		<tr>
			<td><?php echo $_SESSION['lang']['tanggal']?></td>
			<td><?php echo makeElement('tanggal','period',date('d-m-Y'))?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['NoKontrak']?></td>
			<td><?php echo makeElement('nokontrak','text')?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['pabrik']?></td>
			<td><?php echo makeElement('pabrik','select','',array('onchange'=>'getPtkntrk()'),$optListPabrik) ?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['komoditi']?></td>
			<td><?php echo makeElement('komoditi','select','',array(),$optListBrg) ?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['pt']?></td>
			<td><?php echo makeElement('kdpt','select','',array(),$optPt) ?></td>
		</tr>
		<tr>
			<td colspan=2>
			<?php echo makeElement('btnList','btn',
				$_SESSION['lang']['list'],array('onclick'=>"list()"))?>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
CLOSE_BOX();
OPEN_BOX();?>
<fieldset>
	<legend style='font-weight:bold'><?php echo $_SESSION['lang']['list']?></legend>
	<div id='containerList'></div>
</fieldset>
<?php CLOSE_BOX();
echo close_body();
?>