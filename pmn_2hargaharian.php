<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';


?>

<script type="text/javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2hargaharian.js'></script>

<script>
    function zExceldetail(ev,tujuan,passParam)
{
	judul='Report Excel';
        var passP = passParam.split('##');
	
    var param = "proses=exceldetail";
    for(i=0;i<passP.length;i++) {
       // var tmp = document.getElementById(passP[i]);
	   	a=i;
        param += "&"+passP[a]+"="+passP[i+1];
    }
	
	printFile(param,tujuan,judul,ev)	
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='250';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
</script>

<?php
$arr="##psrId##komoditi##periodePsr";
$arr2="##psrId2##komoditi2##periodePsr2";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpasar="<option value=''>".$_SESSION['lang']['all']."</option>";
$optBrg=$optGoldar=$optPeriode;
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pmn_hargapasar order by tanggal desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
/*$arrenum=getEnum($dbname,'pmn_hargapasar','pasar');
foreach($arrenum as $key=>$val)
{
	$optGoldar.="<option value='".$key."'>".$val."</option>";
        $optpasar.="<option value='".$key."'>".$val."</option>";
}*/


$iPasar="select distinct(pasar) as pasar  from ".$dbname.".pmn_hargapasar order by pasar asc ";
$nPasar=  mysql_query($iPasar) or die (mysql_error($conn));
while($dPasar=  mysql_fetch_assoc($nPasar))
{
    $optGoldar.="<option value='".$dPasar['pasar']."'>".$dPasar['pasar']."</option>";
    $optpasar.="<option value='".$dPasar['pasar']."'>".$dPasar['pasar']."</option>";
}

$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$sBrng="select distinct(kodeproduk) as kodeproduk from ".$dbname.".pmn_hargapasar";
$qBrng=mysql_query($sBrng) or die(mysql_error($conn));
while($rBarang=mysql_fetch_assoc($qBrng))
{
    $optBrg.="<option value='".$rBarang['kodeproduk']."'>".$optBarang[$rBarang['kodeproduk']]."</option>";
}

OPEN_BOX('',"<b>Daily Price</b><br>");
?>
<!-- Bootstrap Tabs Navigation -->
<ul class="nav nav-tabs" id="hargaharianTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="hargapasar-tab" data-bs-toggle="tab" data-bs-target="#hargapasar" type="button" role="tab" aria-controls="hargapasar" aria-selected="true">
            <?php echo $_SESSION['lang']['hargapasar'];?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bandingharga-tab" data-bs-toggle="tab" data-bs-target="#bandingharga" type="button" role="tab" aria-controls="bandingharga" aria-selected="false">
            <?php echo $_SESSION['lang']['bandingHarga'];?>
        </button>
    </li>
</ul>

<!-- Bootstrap Tabs Content -->
<div class="tab-content" id="hargaharianTabContent">
    <!-- Tab 1: Harga Pasar -->
    <div class="tab-pane fade show active" id="hargapasar" role="tabpanel" aria-labelledby="hargapasar-tab">
        <div class="p-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo $_SESSION['lang']['hargapasar'];?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['pasar'];?></label>
                                <select id="psrId" name="periode" class="form-select form-select-sm"><?php echo $optGoldar;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['komoditi'];?></label>
                                <select id="komoditi" name="komoditi" class="form-select form-select-sm"><?php echo $optBrg;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['periode'];?></label>
                                <select id="periodePsr" name="periodePsr" class="form-select form-select-sm"><?php echo $optPeriode;?></select>
                            </div>
                            <div class="d-grid gap-2">
                                <button onclick="zPreview('pmn_slave_2hargapasar','<?php echo $arr;?>','printContainer')" class="btn btn-primary btn-sm">Preview</button>
                                <button onclick="zExcel(event,'pmn_slave_2hargapasar.php','<?php echo $arr;?>')" class="btn btn-success btn-sm">Excel</button>
                                <button onclick="grafikProduksi(event)" class="btn btn-info btn-sm">Jpgraph</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Print Area</h5>
                </div>
                <div class="card-body">
                    <div id="printContainer" style="overflow:auto;height:350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Banding Harga -->
    <div class="tab-pane fade" id="bandingharga" role="tabpanel" aria-labelledby="bandingharga-tab">
        <div class="p-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo $_SESSION['lang']['bandingHarga'];?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['pasar'];?></label>
                                <select id="psrId2" name="psrId2" class="form-select form-select-sm"><?php echo $optpasar;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['komoditi'];?></label>
                                <select id="komoditi2" name="komoditi2" class="form-select form-select-sm"><?php echo $optBrg;?></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $_SESSION['lang']['periode'];?></label>
                                <select id="periodePsr2" name="periodePsr2" class="form-select form-select-sm"><?php echo $optPeriode;?></select>
                            </div>
                            <div class="d-grid gap-2">
                                <button onclick="zPreview('pmn_slave_2hargapasar_2','<?php echo $arr2;?>','printContainer2')" class="btn btn-primary btn-sm">Preview</button>
                                <button onclick="zExcel(event,'pmn_slave_2hargapasar_2.php','<?php echo $arr2;?>')" class="btn btn-success btn-sm">Excel</button>
                                <button onclick="grafikProduksi2(event)" class="btn btn-info btn-sm">Jpgraph</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Print Area</h5>
                </div>
                <div class="card-body">
                    <div id="printContainer2" style="overflow:auto;height:350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php	
?>

<?php
CLOSE_BOX();
echo close_body();
?>