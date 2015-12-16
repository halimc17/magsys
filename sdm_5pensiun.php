<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5pensiun.js'></script>
<script>

</script>
<?
$arr="##kodept##masakerja##jenis##banyaknya##old_kodept##old_masakerja##old_jenis##old_banyaknya##method";

$sPT="select * from ".$dbname.".organisasi where tipe='PT'";
$qPT=mysql_query($sPT) or die(mysql_error($conn));
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPT2="<option value=''>".$_SESSION['lang']['all']."</option>";
while($rPT=mysql_fetch_assoc($qPT))
{
    $optPT.="<option value=".$rPT['kodeorganisasi'].">".$rPT['namaorganisasi']."</option>";
	$optPT2.="<option value=".$rPT['kodeorganisasi'].">".$rPT['namaorganisasi']."</option>";
}

$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optJenis2="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrJenis=getEnum($dbname,'sdm_5pesangon','jenis');
foreach($arrJenis as $jenis=>$jns)
{
    $optJenis.="<option value='".$jenis."'>".$jns."</option>";
	$optJenis2.="<option value='".$jenis."'>".$jns."</option>";
}


include('master_mainMenu.php');
OPEN_BOX();

echo"<fieldset style='width:380px;float:left;'>
     <legend><b>".$_SESSION['lang']['pensiun']."</b></legend>
	 <table>
           <tr>
	   <td>".$_SESSION['lang']['kodept']."<input type='hidden' id=old_kodept name=old_kodept /></td>
	    <td><select id=kodept style=width:150px;>".$optPT."</select></td>
	 </tr>
         <tr>
	   <td>".$_SESSION['lang']['masakerja']."<input type='hidden' id=old_masakerja name=old_masakerja /></td>
	   <td><input type=text class=myinputtextnumber id=masakerja style=width:150px; onkeypress='return angka_doang(event)' /></td>
	 </tr>	
         <tr>
	   <td>".$_SESSION['lang']['jenis']."<input type='hidden' id=old_jenis name=old_jenis /></td>
	   <td><select id=jenis style=width:150px;>".$optJenis."</select></td>
	 </tr>	
	 <tr>
	   <td>".$_SESSION['lang']['jmlhBrg']."<input type='hidden' id=old_banyaknya name=old_banyaknya /></td>
	   <td><input type=text class=myinputtextnumber id=banyaknya style=width:150px; onkeypress='return angka_doang(event)' /></td>
	 </tr>	 
	 </table>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=savePensiun('sdm_slave_5pensiun','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset>";
 
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset  style=width:750px;><legend>".$_SESSION['lang']['list']."</legend>
     <table>
        <tr>
            <td>".$_SESSION['lang']['kodept']." </td>
            <td><select id=carikodept style='width:150px;' onchange='loadData()'>".$optPT2."</select></td>
            <td>".$_SESSION['lang']['jenis']." </td>
            <td><select id=carijenis style='width:100px;' onchange='loadData()'>".$optJenis2."</select></td>
        </tr>	
     </table>
     <table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
           <td>No.</td>
           <td>".$_SESSION['lang']['kodept']."</td>
           <td>".$_SESSION['lang']['masakerja']."</td>
	   <td>".$_SESSION['lang']['jenis']."</td>
           <td>".$_SESSION['lang']['jmlhBrg']."</td>
           <td>".$_SESSION['lang']['action']."</td>    
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";

echo"</tbody>
     <tfoot>
     
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>