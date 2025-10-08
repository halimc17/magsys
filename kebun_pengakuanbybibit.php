<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// Periode Sekarang
$currPeriod = $_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan'];

// Options
$optPeriod = makeOption($dbname,'setup_periodeakuntansi','periode,periode',
						"kodeorg = '".$_SESSION['empl']['lokasitugas']."' and
						periode >= '".$currPeriod."'");
$optKebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					   "kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'");
$optSumber = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					   "tipe='BIBITAN' and kodeorganisasi like '%MN%' and
					   LENGTH(kodeorganisasi) = 10");

/**
 * View
 */
echo open_body();
?>
<script src="js/kebun_pengakuanbybibit.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?php include('master_mainMenu.php');
OPEN_BOX();?>

<fieldset>
	<legend><b><?php echo $_SESSION['lang']['pengakuanbiayabibit']?></b></legend>
	<table>
		<tr>
			<td><?php echo $_SESSION['lang']['periode']?></td>
			<td><?php echo makeElement('periode','select',$currPeriod,
									   array('style'=>'width:100px'),$optPeriod)?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['kebun']?></td>
			<td><?php echo makeElement('kebun','select','',
									   array('style'=>'width:150px'),$optKebun)?></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['sumberbibit']?></td>
			<td><?php echo makeElement('sumber','select','',
									   array('style'=>'width:150px'),$optSumber)?></td>
		</tr>
		<tr>
			<td><?php echo makeElement('btnPreview','btn',$_SESSION['lang']['preview'],
									   array('onclick'=>'preview()'))?></td>
		</tr>
	</table>
</fieldset>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset>
	<legend><b><?php echo $_SESSION['lang']['preview']?></b></legend>
	<div id='previewCont'></div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>