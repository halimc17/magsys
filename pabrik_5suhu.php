<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_5suhu.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX();

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
$res=mysql_query($str);
if(mysql_num_rows($res)==0){
	$optOrg="<option value=''>-</option>";
}else{
	while($bar=mysql_fetch_object($res)){
		$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}
}

$sKdTangki="select kodetangki, keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by keterangan asc";
$qKdTangki=mysql_query($sKdTangki) or die(mysql_error($conn));
$numRows=mysql_num_rows($qKdTangki);
if(mysql_num_rows($qKdTangki)==0){
	$optKdTangki="<option value=''>-</option>";
}else{
	while($rKdTangki=mysql_fetch_assoc($qKdTangki)){
		$optKdTangki.="<option value='".$rKdTangki['kodetangki']."'>".$rKdTangki['kodetangki']." - ".$rKdTangki['keterangan']."</option>";
	}
}

?>
<fieldset style="width:300px;">
	<legend><?echo $_SESSION['lang']['suhu'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kodeorg'];?>
    </td>
    <td><select id="kodeorg"><?echo $optOrg;?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['kodetangki'];?></td>
    <td><select id="kodetangki"><? echo $optKdTangki; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['suhu'];?></td>
	<td><input type="text"  class=myinputtextnumber id="suhu" value=0 size=10 maxlength=10 onkeypress="return angka_doang(event)"></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['beratjenis'];?></td>
    <td><input type="text"  class=myinputtextnumber id="beratjenis" value=0 size=10 maxlength=10 onkeypress="return angka_doang(event)"></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['varian'];?></td>
	<td><input type="text"  class=myinputtextnumber id="varian" value=0 size=10 maxlength=10 onkeypress="return angka_doang(event)"></td>
  </tr>
  <input type=hidden value='insert' id=method>
</table>
<button class=mybutton onclick=simpan()><?echo $_SESSION['lang']['save'];?></button>
<button class=mybutton onclick=cancel()><?echo $_SESSION['lang']['cancel'];?></button>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset style='width:800px;background-color:#A9D4F4'>
<legend><b>".$_SESSION['lang']['list']."</b></legend>
<img src='images/pdf.jpg' title='PDF Format' style='width:20px;height:20px;cursor:pointer' onclick=\"printPDF(event)\">&nbsp;
<img src='images/printer.png' title='Print Page' style='width:20px;height:20px;cursor:pointer' onclick='javascript:print()'>
<div style='padding-top:5px;'>
<b id=caption></b>
      <table cellspacing=1 border=0 class=sortable>
      <thead>
	  <tr class=rowheader>
	  <td>".$_SESSION['lang']['nourut']."</td>
	  <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
	  <td align=center>".$_SESSION['lang']['kodetangki']."</td>
	  <td>".$_SESSION['lang']['suhu']."</td>
	  <td>".$_SESSION['lang']['beratjenis']."</td>
	  <td>".$_SESSION['lang']['varian']."</td>
	  <td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </thead>
	  <tbody id=container>
	  <script>loadData()</script>
	  </tbody>
	  <tfoot>
	  </tfoot>
	  </table>
</div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>