<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pmn_pajak.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>




<?php
include('master_mainMenu.php');			
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT' ORDER BY kodeorganisasi";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
	$optCariPt.="<option value='".$data['kodeorganisasi']."'>".$data['namaorganisasi']."</option>";
}

/*$optFaktur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sFaktur="select * from ".$dbname.".keu_fakturpajak where status = '0' order by nofaktur limit 1";
$qFaktur = mysql_query($sFaktur) or die ("SQL ERR : ".mysql_error());
while ($dFaktur=mysql_fetch_assoc($qFaktur))
{
	$optFaktur.="<option value=".$dFaktur['nofaktur'].">".$dFaktur['nofaktur']."</option>";
}*/

// $optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ha="SELECT namasupplier,`supplierid`,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE status='1' and kodekelompok='S004' order by namasupplier asc";
$hi=mysql_query($ha) or die (mysql_error());
while ($hu=mysql_fetch_assoc($hi))
{
	$optsup.="<option value=".$hu['supplierid'].">".$hu['namasupplier']."</option>";
}

$optJenis="<option value=''></option>";
// $optJenis.="<option value='Jumlah Harga Jual'>Jumlah Harga Jual</option>";
// $optJenis.="<option value='Penggantian'>Penggantian</option>";
// $optJenis.="<option value='Uang Muka'>Uang Muka</option>";
// $optJenis.="<option value='Termin'>Termin</option>";		
?>


<?php
OPEN_BOX('',"<b>Faktur Pajak</b>");//optsup

echo"<br /><br /><fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>PT</td>
                                    <td>:</td>
                                    <td><select id=pt onchange=getFaktur() style=\"width:150px;\">".$optOrg."</select></td>
                                </tr>
                                <tr>
                                    <td>No. Faktur</td>
                                    <td>:</td>
                                    <td><select id=faktur  style=\"width:150px;\">".$optFaktur."</select></td>
                                </tr>
                                <tr>
                                    <td>No. Invoice</td>
                                    <td>:</td>
                                    <td><input type=text id=invoice name=nopajak onkeypress=return tanpa_kutip(event); class=myinputtext style=width:150px; /></td>
                                </tr>
                                <tr>
                                    <td style='display:none'>".$_SESSION['lang']['jenis']."</td>
                                    <td style='display:none'>:</td>		
                                    <td style='display:none'><select id=jenis  style=\"width:150px;\">".$optJenis."</select></td>
                                </tr>
                                
                                <tr>
                                    <td>Kurs Pajak</td>
                                    <td>:</td>
                                    <td><input type=text value=1 id=kurs name=kurs onkeypress=return tanpa_kutip(event); class=myinputtext style=width:150px; /></td>
                                </tr>
                                
				<tr>
                                    <td></td><td></td><br />
                                    <td><br /><button class=mybutton onclick=simpan()>Simpan</button>
                                    <button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
					
	/*echo"<fieldset style='float:left;'>
			<legend>Sort</legend> 
				<table border=0 cellpadding=1 cellspacing=1>
					<tr>
                                            <td>".$_SESSION['lang']['nodok']." Referensi</td>
                                            <td>:</td>
                                            <td><input type=text id=nopajaksch name=nopajaksch class=myinputtext style=width:150px; /></td>
                                        </tr>
					
				</table></fieldset>";*/
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<table>
			<tr>
				<td>".$_SESSION['lang']['perusahaan']."</td>
				<td><select id=cariPt>".$optCariPt."</select></td>
				<td><button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button></td>
			</tr>
		</table>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

<?php
/*
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo "<fieldset>";
echo "<legend><b>".$_SESSION['lang']['datatersimpan']."</b></legend>";
//echo "<div id=container>";
echo" <div id=container style='width:500px;height:400px;overflow:scroll'>";	
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
			 <td align=center>No</td>
                         <td align=center>".$_SESSION['lang']['pabrik']."</td>
			 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
			 <td align=center>".$_SESSION['lang']['tanggal']."</td>
			 <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
			 <td align=center>".$_SESSION['lang']['harga']."</td>
			 <td align=center>*</td></tr>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";
        
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
echo "</fieldset>";
CLOSE_BOX();
echo close_body();*/					
?>