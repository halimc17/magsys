<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$fReport = new formReport('kasharian','keu_slave_2kasHarian',$_SESSION['lang']['kasharian']);
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
						 "length(kodeorganisasi)=4");
    $fReport->addPrime('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',20,$optOrg);
}  
// update Oct 17, 2011 begin
// $as=Array('0'=>'Seluruhnya');
$as=Array();
// update Oct 17, 2011 end
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"kasbank=1");
$optAkun=$as+$optAkun;

$fReport->addPrime('noakun',$_SESSION['lang']['noakun'],'','select','L',20,$optAkun);
$fReport->addPrime('periode',$_SESSION['lang']['periode'],date('d-m-Y'),'period','L',15);
$fReport->_detailHeight = 60;

/** View **/
echo open_body();
?>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language="JavaScript1.2" src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');

OPEN_BOX();
$fReport->render();
CLOSE_BOX();

echo close_body();
?>