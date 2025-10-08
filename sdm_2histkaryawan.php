<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$optJabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',null,'0',true);
$optJabatan[''] = $_SESSION['lang']['all'];

$fReport = new formReport('histkaryawan','sdm_slave_2histkaryawan',$_SESSION['lang']['histkaryawan']);
$fReport->addPrime('karyawan',$_SESSION['lang']['namakaryawan'],'','text','L',20);
$fReport->addPrime('jabatan',$_SESSION['lang']['jabatan'],'','select','L',20,$optJabatan);
$fReport->addPrime('periode',$_SESSION['lang']['tanggal'],date('d-m-Y'),'period','L',15);
$fReport->_detailHeight = 60;

/** View **/
echo open_body();
?>
<script src="js/formReport.js"></script>
<script src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?php
include('master_mainMenu.php');

OPEN_BOX();
$fReport->render();
CLOSE_BOX();

echo close_body();
?>