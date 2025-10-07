<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/a.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<!--<link rel=stylesheet type=text/css href="style/zTable.css">-->
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src=js/zTools.js></script>

<?php
$arr="##nopo";	
OPEN_BOX('',"<b>Laporan Minimal Stok</b>"); //1 O

echo"<div><fieldset style=float:left><legend>Reminder</legend>";
echo"<table table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>".$_SESSION['lang']['kodebarang']."</td>
			<td>".$_SESSION['lang']['namabarang']."</td>
			<td>".$_SESSION['lang']['satuan']."</td>
			<td>".$_SESSION['lang']['saldo']."</td>
			<td>".$_SESSION['lang']['min']." Stock</td>
			<td>".$_SESSION['lang']['nopp']." Stock</td>
		</tr>
        </thead><tbody>";

#reminder  stok minimum
$str="select kodebarang,namabarang,satuan,minstok from ".$dbname.".log_5masterbarang where minstok>0 order by kodebarang";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $barang[$bar->kodebarang]=$bar->kodebarang;
    $namabarang[$bar->kodebarang]=$bar->namabarang;
    $satuan[$bar->kodebarang]=$bar->satuan;
    $minstok[$bar->kodebarang]=$bar->minstok;
}
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')&&($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
    $whrg=" and left(kodegudang,4)='".$_SESSION['empl']['lokasitugas']."'";    
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    //$whrg=" and left(kodegudang,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";    
    $whrg=" and left(kodegudang,4) in (select a.kodeunit from ".$dbname.".bgt_regional_assignment a LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeunit where b.detail=1 and a.regional='".$_SESSION['empl']['regional']."')";    
}else if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $whrg="  and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi where detail=1 and tipe like 'GUDANG%')";    
}

#ambil saldo per PT
$str="select sum(a.saldoqty) as saldo, a.kodebarang,a.kodeorg,b.minstok,c.nopp from ".$dbname.".log_5masterbarangdt a
		left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
		left join (select kodeorg,kodebarang,nopp from ".$dbname.".log_prapo_vw where realisasi=0 and year(tanggal)=year(curdate()) and `status`<>3 ORDER BY kodeorg,kodebarang,nopp) c on c.kodeorg=a.kodeorg and c.kodebarang=a.kodebarang
		where b.minstok>0 ".$whrg." 
		group by a.kodeorg,a.kodebarang
		having (saldo < minstok or saldo=minstok)";
$res=mysql_query($str);
if(mysql_num_rows($res)>0){
	$no=0;      
    while($bar=mysql_fetch_object($res)){
       $no+=1;
       echo "<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$bar->kodeorg."</td>
                <td>".$bar->kodebarang."</td>
                <td>".$namabarang[$bar->kodebarang]."</td>
                <td>".$satuan[$bar->kodebarang]."</td>
                <td align=right>".number_format($bar->saldo,0)."</td>
                <td align=right>".number_format($bar->minstok,0)."</td>
                <td>".$bar->nopp."</td>
            </tr>";
     } 
}
echo"</tbody></table></fieldset></div>"; 
CLOSE_BOX();
/*PEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer'>
</div></fieldset>";//style='overflow:auto;height:350px;max-width:1500px';
CLOSE_BOX();
echo close_body();*/					
?>
