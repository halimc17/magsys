<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script language=javascript>


	function batal()
	{
		document.getElementById('kdsup').value='';
		document.getElementById('tgl2').value='';	
		document.getElementById('tgl1').value='';
		document.getElementById('printContainer').innerHTML='';	
	}


</script>

<?php



$optsup="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="SELECT namasupplier,`supplierid` FROM ".$dbname.".log_5supplier WHERE kodekelompok='S004' order by namasupplier asc";
//echo $sql;
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optsup.="<option value=".$data['supplierid'].">".$data['namasupplier']."</option>";
}
                        
$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ha="SELECT namasupplier,`supplierid` FROM ".$dbname.".log_5supplier WHERE status='1' and left(kodekelompok,3)='T00' "
        . " order by namasupplier asc";
$hi=mysql_query($ha) or die (mysql_error());
while ($hu=mysql_fetch_assoc($hi))
{
	$optsup.="<option value=".$hu['supplierid'].">".$hu['namasupplier']."</option>";
}                    
	
$optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ha="SELECT * FROM ".$dbname.".organisasi WHERE length(kodeorganisasi)=3 "
        . " ";
$hi=mysql_query($ha) or die (mysql_error());
while ($hu=mysql_fetch_assoc($hi))
{
	$optPt.="<option value=".$hu['kodeorganisasi'].">".$hu['namaorganisasi']."</option>";
} 

?>


<?php
include('master_mainMenu.php');
OPEN_BOX();
$arr="##kdsup##pt##nokontrak##tgl1##tgl2";

echo "<div class='row'>
    <div class='col-md-4'>
        <div class='card'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>Laporan Pengiriman Per Transportir</h5>
            </div>
            <div class='card-body'>
                <div class='mb-3'>
                    <label class='form-label'>Suplier</label>
                    <select id='kdsup' class='form-select form-select-sm'>".$optsup."</select>
                </div>
                <div class='mb-3'>
                    <label class='form-label'>PT</label>
                    <select id='pt' class='form-select form-select-sm'>".$optPt."</select>
                </div>
                <div class='mb-3'>
                    <label class='form-label'>".$_SESSION['lang']['nodo']."</label>
                    <input type='text' maxlength='50' id='nokontrak' class='form-control form-control-sm' onkeypress='return_tanpa_kutip(event);'>
                </div>
                <div class='mb-3'>
                    <label class='form-label'>".$_SESSION['lang']['tanggal']."</label>
                    <div class='input-group input-group-sm'>
                        <input type='text' class='form-control form-control-sm' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10'>
                        <span class='input-group-text'>s/d</span>
                        <input type='text' class='form-control form-control-sm' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10'>
                    </div>
                </div>
                <div class='d-grid gap-2'>
                    <button onclick=\"zPreview('pmn_slave_2transportir','".$arr."','printContainer')\" class='btn btn-primary btn-sm'>".$_SESSION['lang']['preview']."</button>
                    <button onclick=\"zExcel(event,'pmn_slave_2transportir.php','".$arr."')\" class='btn btn-success btn-sm'>".$_SESSION['lang']['excel']."</button>
                    <button onclick='batal()' class='btn btn-secondary btn-sm'>".$_SESSION['lang']['cancel']."</button>
                </div>
            </div>
        </div>
    </div>
</div>";

echo "<div class='card mt-3'>
    <div class='card-header'>
        <h5 class='card-title mb-0'>".$_SESSION['lang']['printArea']."</h5>
    </div>
    <div class='card-body'>
        <div id='printContainer' style='overflow:auto;height:350px;'></div>
    </div>
</div>";

CLOSE_BOX();
echo close_body();




?>