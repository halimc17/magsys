<?//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script type="text/javascript" src="js/log_2riwayatPP.js" /></script>
<script language="javascript" src="js/zMaster.js"></script>




<?
$arrPil=array("1"=>$_SESSION['lang']['proses'].' '.$_SESSION['lang']['persetujuan'].' '.$_SESSION['lang']['prmntaanPembelian'],
              "2"=>$_SESSION['lang']['proses'].' '.$_SESSION['lang']['purchasing'],
              "3"=>$_SESSION['lang']['jmlh_brg_sdh_po'],
              "4"=>$_SESSION['lang']['jmlh_brg_blm_po'],
    "5"=>$_SESSION['lang']['ditolak']);
$optPil='';
foreach($arrPil as $id =>$isi)
{
    $optPil.="<option value=".$id.">".$isi."</option>";
}
$optLokal="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrPo=array("0"=>"Pusat","1"=>"Lokal");
foreach($arrPo as $brsLokal =>$isiLokal)
{
    $optLokal.="<option value=".$brsLokal.">".$isiLokal."</option>";
}
//$optper="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_prapoht order by tanggal desc";
$qTgl=mysql_query($sTgl) or die(mysql_error());
$optper="";
while($rTgl=mysql_fetch_assoc($qTgl))
{
   if(substr($rTgl['periode'],5,2)=='12')
   {
         $optper.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
   }
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$optPersetujuan='';
$arrPersetujuan=array();
for($i=1;$i<6;$i++){
	$sPersetujuan="select distinct(a.persetujuan".$i.") as persetujuan, b.namakaryawan as namakaryawan from ".$dbname.".log_prapoht a 
				left join ".$dbname.".datakaryawan b 
				on a.persetujuan".$i." = b.karyawanid 
				where a.persetujuan".$i." != NULL or a.persetujuan".$i." != '' 
				order by b.namakaryawan asc";
	$qPersetujuan=mysql_query($sPersetujuan) or die(mysql_error());
	while($rPersetujuan=mysql_fetch_assoc($qPersetujuan))
	{
		$arrPersetujuan[$rPersetujuan['persetujuan']]=array("nik"=>$rPersetujuan['persetujuan'],"nama"=>$rPersetujuan['namakaryawan']);
	}
}

foreach($arrPersetujuan as $value) {
  $optPersetujuan.="<option value='".$value['nik']."'>".$value['nama']."</option>";
}

$arr="##nopp##tgl##per##lok##stat##sup##nama##psj";	// style='float:left;'

OPEN_BOX('',"<b>".$_SESSION['lang']['list']." PP</b><br><br>");

echo"<fieldset style='float:left;'>
        <legend><b>Form</b></legend>
            <table cellpadding=1 cellspacing=1 border=0>
                <tr>
                    <td>".$_SESSION['lang']['nopp']."</td>
                    <td>:</td>
                    <td><input type='text' id='nopp' name='nopp' onkeypress='return tanpa_kutip(event)' style='width:150px' class=myinputtext /></td>
                    
                    <td>".$_SESSION['lang']['tanggal']." PP </td>
                    <td>:</td>
                    <td><input type=text class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;   maxlength=10 style=width:150px /></td>
                    
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per name=per style='width:150px;'>".$optper."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['lokasiBeli']."</td>
                    <td>:</td>
                    <td><select id=lok name=lok style='width:150px;'>".$optLokal."</select></td>
                    
                    <td>".$_SESSION['lang']['status']." PP</td>
                    <td>:</td>
                    <td><select id=stat name=stat style='width:150px;'><option value=''>".$_SESSION['lang']['all']."</option>".$optPil."</select></td>
                    
                     <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type='text' id='sup' name='supplier' onkeypress='return tanpa_kutip(event)' style='width:150px' class=myinputtext /></td>               
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namabarang']."</td>
                    <td>:</td>
                    <td><input type='text' id='nama' name='namabarang' onkeypress='return tanpa_kutip(event)' style='width:150px' class=myinputtext /></td> 
					<td>".$_SESSION['lang']['persetujuan']."</td>
                    <td>:</td>
                    <td><select id=psj name=psj style='width:150px;'><option value=''>".$_SESSION['lang']['all']."</option>".$optPersetujuan."</select></td>     
                </tr>";


                echo"<tr>
                    <td colspan=4>
                        <button onclick=zPreview('log_slave_2riwayat_baru','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                        <button onclick=zExcel(event,'log_slave_2riwayat_baru.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                       

</td>
                </tr>
            </table>
</fieldset>";// <button onclick=zPdf('log_slave_2riwayat','".$arr."','printContainer')  class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
                          


CLOSE_BOX(); 

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();		