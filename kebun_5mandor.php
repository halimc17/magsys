<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/kebun_5mandor.js'></script>
<?php

include('master_mainMenu.php');
$optmandor='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
$str="select t1.karyawanid, t1.namakaryawan from ".$dbname.".datakaryawan t1
    where t1.lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0 and not exists (select t2.karyawanid from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.karyawanid)
    order by t1.namakaryawan";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $optmandor.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
}

// $optmandor='<option value=\'\'></option>';
$optkaryawan='<option value=\'\'></option>';
 
OPEN_BOX();
echo"<fieldset>
     <legend>".$_SESSION['lang']['mandor']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['mandor']."</td>
	   <td>: <select onchange=\"pilihmandor();\" id=mandor style='width:200px'>".$optmandor."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['karyawan']."</td>
	   <td>
                : <select id=karyawan style='width:200px'>".$optkaryawan."</select>
        </td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['urutan']."</td>
		<td>
			: <input type=text class=myinputtext onkeypress=\"return angka_doang(event);\" id=urut size=3 maxlength=3 class=myinputtextnumber>
		</td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>
			: <select id=status disabled>
				<option value='1'>Aktif</option>
				<option value='0'>Tidak Aktif</option>
			</select>
		</td>
	 </tr>
	 <tr>
		<td></td>
		<td>
			 <input type='hidden' id='procces' value='tambahkaryawan'>
			 <button class=mybutton onclick=tambahkaryawan()>".$_SESSION['lang']['save']."</button>
			 <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	 <tr>
	   <td></td>
	   <td><div id=anggota style='display:none'></td>
	 </tr>
	 </table>
     </fieldset>";
CLOSE_BOX();

OPEN_BOX();
//$str="select * from ".$dbname.".kebun_5mandor";
//$res=mysql_query($str);
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 cellpadding=3 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['mandor']."</td>
	   <td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>tampilmandor()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>