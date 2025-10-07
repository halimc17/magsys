<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/keu_tagihan.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
#=== Prep Control & Search
$ctl = array();

# Control
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
$jenisSearch = array(
	'noinvoice' => $_SESSION['lang']['noinvoice'],
	'noinvoicesupplier' => $_SESSION['lang']['noinvoice']." Supplier",
	'namasupplier' => $_SESSION['lang']['supplier'],
	'nopo' => $_SESSION['lang']['nopo'],
);
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    makeElement('sJenis','select','noinvoice',array(),$jenisSearch).
    makeElement('sNoTrans','text','').
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans()")).
    "</fieldset>";


#=== Table Aktivitas
# Header
$header = array(
   $_SESSION['lang']['noinvoice'],$_SESSION['lang']['noinvoice']." Supplier",
   $_SESSION['lang']['pt'],$_SESSION['lang']['tanggalterima'],'Last Update',
   $_SESSION['lang']['nopo'],$_SESSION['lang']['supplier'],
   $_SESSION['lang']['keterangan'],$_SESSION['lang']['subtotal'],'postingby'
);

//cari nama orang
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
$res=mysql_query($str);
while($bar= mysql_fetch_object($res))
{
   $nama[$bar->karyawanid]=$bar->namakaryawan;
}    

# Content
$cols = "a.noinvoice,a.noinvoicesupplier,a.kodeorg,a.tanggal,a.updateby,a.nopo,
	b.namasupplier,a.keterangan,a.nilaiinvoice,a.postingby,a.posting";
$order="a.tanggal desc";
$where = "a.kodeorg='".$_SESSION['org']['kodeorganisasi']."' and updateby='".$_SESSION['standard']['userid']."'";
if($_SESSION['empl']['kodejabatan']==5)$where = "a.kodeorg like '%' and updateby like '%'";

$queryRow = "select count(*) as rows";
$query = " from ".$dbname.".keu_tagihanht a 
	left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
	where ".$where." order by ".$order." limit 0,10";
$queryRow .= $query;
$query = "select ".$cols.$query;

$tmpTotal = fetchData($queryRow);
$data = fetchData($query);
$totalRow = $tmpTotal[0]['rows'];

// Get Akun Ppn
$qAkun = selectQuery($dbname,'setup_parameterappl','nilai',
	"kodeaplikasi='TX' and kodeparameter='PPNINV'");
$resAkun = fetchData($qAkun);

// List of Invoice
$listInv = '';
foreach($data as $key=>$row) {
	if(!empty($listInv)) 
	  $listInv.= ",";  
	$listInv .= "'".$row['noinvoice']."'";
}

// Sum Akun Ppn (Detail Tagihan)
if(empty($resAkun) and empty($listInv)) {
	$optDet = array();
} else {
    if($listInv!=''){
	$optDet = makeOption($dbname,'keu_tagihandt',"noinvoice,nilai",
						 "noinvoice in (".$listInv.") and noakun='".$resAkun[0]['nilai']."'");
	}
}
foreach($data as $key=>$row) {
	// Add Ppn
	if(isset($optDet[$row['noinvoice']]))
		$row['nilaiinvoice'] += $optDet[$row['noinvoice']];
	
	if($row['posting']==1) {
		$data[$key]['switched']=true;
	}
	unset($data[$key]['posting']);            
	$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
	$data[$key]['nilaiinvoice'] = number_format($row['nilaiinvoice'],2);
	$data[$key]['updateby'] = $nama[$row['updateby']];
	$data[$key]['postingby'] = isset($nama[$row['postingby']])? $nama[$row['postingby']]: '-';
}

# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data);
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['kodejabatan']==117 or $_SESSION['empl']['kodejabatan']==119){
	$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
} else {//hanya HO dan region yang boleh menghapus
	$tHeader->addAction('','Delete','images/'.$_SESSION['theme']."/delete.png");
}

$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
$tHeader->_actions[3]->addAttr('event');
$tHeader->pageSetting(1,$totalRow,10);
$tHeader->_switchException = array('detailPDF');

#=== Display View
# Title & Control
OPEN_BOX();
echo "<div align='center'><h3>Invoice</h3></div>";
echo "<div><table align='center'><tr>";
foreach($ctl as $el) {
    echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
}
echo "</tr></table></div>";
CLOSE_BOX();

# List
OPEN_BOX();
echo "<div id='workField'>";
$tHeader->renderTable();
echo "</div>";
CLOSE_BOX();
echo close_body();
?>