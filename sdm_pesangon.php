<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_pesangon.js'></script>
<script>

</script>
<?php
$sKary="select * from ".$dbname.".datakaryawan
        where tipekaryawan not in(0,7,8) and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."')";
$qKary=mysql_query($sKary) or die(mysql_error($conn));
$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rKary=mysql_fetch_assoc($qKary))
{
    $optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']."</option>";	
}

$jenis=getEnum($dbname,'sdm_5pesangon','jenis');
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";                
foreach($jenis as $jns=>$jns2)
{
        $optjenis.="<option value='".$jns."'>".$jns2."</option>";
}

include('master_mainMenu.php');
OPEN_BOX();

echo"<fieldset style='width:600px;'>
     <legend><b>".$_SESSION['lang']['pesangon']."</b></legend>
	 <table>
         <tr>
	   <td>".$_SESSION['lang']['nosurat']."</td>
	   <td><input type=text class=myinputtextnumber id=nosurat style=width:200px;></td>
	 </tr>
         <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
         </tr>
         <tr>
	   <td>".$_SESSION['lang']['namakaryawan']."</td>
	    <td><select id=karyawanid style=width:200px; onchange=getkodeunit(this.options[this.selectedIndex].value);>".$optKary."</select></td>
	 </tr>
         <tr>
	   <td>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['unitkerja']."</td>
	   <td><input type=text disabled class=myinputtextnumber id=kodeunit size=10 maxlength=10>
               <input type=hidden  class=myinputtextnumber id=tglmasuk></td>
	 </tr>
          <tr>
            <td>".$_SESSION['lang']['tglberhenti']."</td>
            <td><input type=text class=myinputtext id=tglberhenti onmousemove=setCalendar(this.id) onkeypress=return false; onchange=getmasakerja(); size=10 maxlength=10 /></td>
         </tr>
         <tr>
	   <td>".$_SESSION['lang']['masakerja']."</td>
	   <td><input type=text disabled class=myinputtextnumber id=masakerjatahun style=width:30px; onkeypress='return angka_doang(event)' onblur=calculatePesangon(this);> ".$_SESSION['lang']['tahun']."
               <input type=text disabled class=myinputtextnumber id=masakerjabulan style=width:30px; onkeypress='return angka_doang(event)' /> ".$_SESSION['lang']['bulan']."
               <input type=text disabled class=myinputtextnumber id=masakerjahari style=width:30px; onkeypress='return angka_doang(event)' /> ".$_SESSION['lang']['hari']."</td>
	 </tr>	
         <tr>
	   <td>".$_SESSION['lang']['gajipokok']."</td>
	   <td><input type=text class=myinputtextnumber id=gajipokok  value=0 style=width:200px; onkeypress='return angka_doang(event)' onchange='calculatePesangon()' onblur=\"change_number(this);\"></td>
	 </tr>
         <tr>
	   <td>".$_SESSION['lang']['tjjabatan']."</td>
	   <td><input type=text class=myinputtextnumber id=tunjanganjabatan value=0 style=width:200px; onkeypress='return angka_doang(event)' onchange='calculatePesangon()' onblur=\"change_number(this);\"></td>
	 </tr>
         <tr>
	   <td>".$_SESSION['lang']['jenis']." SK</td>
	    <td><select id=jenissk style=width:200px; onchange=getdetail('','','','','','','','','','',this.options[this.selectedIndex].value,'','','','','','','','');>".$optjenis."</select></td>
	 </tr>
	 </table>
     </fieldset>";
 
echo "<br /><fieldset style='width:600px;'>
        <legend><b>".$_SESSION['lang']['detail']."</b></legend>
            <div id=detailTable style=display:block;>
            </div>
    </fieldset>";
echo "<br /><fieldset style='width:1000px;'>
        <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <div id=isi><script>loadData()</script></div>
    </fieldset>";
CLOSE_BOX();
echo close_body();
?>