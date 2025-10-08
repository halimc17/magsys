<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src=js/zMaster.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
#======Select Prep======
# Get Data
$optApp = getEnum($dbname,'setup_posting','kodeaplikasi');
$optJab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

#======End Select Prep======
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodeaplikasi','label',$_SESSION['lang']['kodeaplikasi']),
  makeElement('kodeaplikasi','select','',array('style'=>'width:150px'),$optApp)
);
$els[] = array(
  makeElement('jabatan','label',$_SESSION['lang']['jabatan']),
  makeElement('jabatan','select','',array('style'=>'width:150px'),$optJab)
);

# Fields
$fieldStr = '##kodeaplikasi##jabatan';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$opt = array('jabatan' => $optJab);
$btnConfig = array(
	'freeze' => 'kodeaplikasi',
	'empty' => true,
	'opt' => json_encode($opt)
);
$els['btn'] = array(
  genFormBtnConfig($fieldStr,
    'setup_posting',"##kodeaplikasi##jabatan",$btnConfig)
);
//$els['btn'] = array(
//  genFormBtn($fieldStr,
//    'setup_posting',"##kodeaplikasi",null,'kodeaplikasi',true)
//);

# Generate Field
echo genElTitle('Setup Posting',$els);
echo "</div>";
#=======End Form============

#=======Table===============
$configTable = array(
	'opt' => $opt,
	'column' => '*'
);
# Display Table
echo "<div style='clear:both;float:left'>";
echo masterTableConfig($dbname,'setup_posting',$configTable);
#echo masterTable($dbname,'keu_5akun',"*",array(),array(),array(),array(),'keu_slave_5daftarperkiraan_pdf');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>