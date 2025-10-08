<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".$_SESSION['lang']['keeksternal']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
function add_new_data(){
                document.getElementById('headher').style.display="block";
				document.getElementById('listData').style.display="none";
				cancelData();
}
nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script language="javascript" src="js/kebun_timbangke_eksternal.js"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
<?php
	
	 $optTipePot=$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	/* */
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['nospb'].":<input type=text class='myinputtext' onkeypress='return tanpa_kutip(event)' id=nosbpCr />&nbsp;";
			echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove='setCalendar(this.id)' onkeypress='return false;' size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
	 </tr>
	 </table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
<?php OPEN_BOX()?>
<fieldset style="float:left;">
<legend><?php echo $_SESSION['lang']['list']?></legend>
<!--display data-->
<div id="contain">
<script>loadData(0);</script>
</div>
</fieldset>
<?php CLOSE_BOX()?>
</div>

<div id="headher" style="display:none">
<?php
OPEN_BOX();
//$optTipePot
$jmMsk=$jmKlr=$mntMsk=$mntKlr="";
for($i=0;$i<24;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   $jmMsk.="<option value=".$i." ".($i==$jmMasuk[0]?'selected':'').">".$i."</option>";
   $jmKlr.="<option value=".$i." ".($i==$jmKeluar[0]?'selected':'').">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   $mntMsk.="<option value=".$i." ".($i==$jmMasuk[1]?'selected':'').">".$i."</option>";
   $mntKlr.="<option value=".$i." ".($i==$jmKeluar[1]?'selected':'').">".$i."</option>";
   $i++;
}
?>
<fieldset style="float:left">
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<table cellspacing="1" border="0">
<tr><td><?php echo $_SESSION['lang']['tanggal']." Timbang"?></td>
<td>:</td>
<td><input type=text class=myinputtext id=tgl onmousemove='setCalendar(this.id)' onkeypress='return false;' onchange='getNosbp()'  size=10 maxlength=10 /></td>
</tr>
<tr> 	 
	<td style='valign:top'><?php echo $_SESSION['lang']['jammasuk']; ?></td>
	<td>:</td>
	<td><select id=jmMasuk><?php echo $jmMsk; ?></select> : <select id=mntMasuk><?php echo $mntMsk; ?></select>
</td></tr>
<tr> 	 
	<td style='valign:top'><?php echo $_SESSION['lang']['jamkeluar']; ?></td>
	<td>:</td>
	<td><select id=jmKeluar><?php echo $jmKlr; ?></select> : <select id=mntKeluar><?php echo $mntKlr; ?></select></td></tr>
<tr><td><?php echo $_SESSION['lang']['nospb']?></td>
<td>:</td><td>
<select id="spbId" name="spbId" style="width:150px;"  ><?php echo $optOrg;?></select></td>
</tr>

<tr><td><?php echo $_SESSION['lang']['nopol']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="kdKend" name="kdKend" style="width:150px;" />
</td>
</tr>

<tr><td><?php echo $_SESSION['lang']['supir']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="nmSupir" name="nmSupir" style="width:150px;" />
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['jjg']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="jmlhJjg" name="jmlhJjg" style="width:150px;" />
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['beratMasuk']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtMsk" name="brtMsk" onblur="getBersih()" style="width:150px;" />Kg
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['beratKeluar']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtKlr" name="brtKlr" onblur="getBersih()"  style="width:150px;" />Kg
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['beratBersih']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" disabled onkeypress="return angka_doang(event)" id="brtBrsh" name="brtBrsh" style="width:150px;" />Kg
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['jjg']?> Sortasi</td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="JjgSortasi" name="JjgSortasi" style="width:150px;" />
</td>
</tr>
<tr><td><?php echo $_SESSION['lang']['potongan']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber"  onkeypress="return angka_doang(event)" id="potKg" name="potKg" style="width:150px;" />Kg
</td>
</tr>

<tr>
<td colspan="3"><input type="hidden" id="notrans" value='' /><input type="hidden" id="proses" value='insert' />
    <div id="tombolHeader">
        <button class=mybutton id=dtlAbn onclick=saveData()><?php echo $_SESSION['lang']['save'] ?></button>
        <button class=mybutton id=cancelAbn onclick=cancelData()><?php echo $_SESSION['lang']['cancel']?></button>
    </div>
</td>
</tr>
</table>
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();
?>