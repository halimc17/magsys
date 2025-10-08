<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script type="text/javascript" src="js/sdm_5jenistraining.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<fieldset>
	<legend><?php echo $_SESSION['lang']['traininginternal'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kode'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="kode" class="myinputtext" size="10" maxlength="10" />
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['jeniskursus'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="jenistraining" class="myinputtext" size="50" maxlength="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpantraining()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=bataltraining()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['traininginternal'] ?></legend>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kode']?></td>
				<td><?php echo $_SESSION['lang']['jeniskursus']?></td>
				<td><?php echo $_SESSION['lang']['status'];?></td>
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
<?php
CLOSE_BOX();
echo close_body();
?>