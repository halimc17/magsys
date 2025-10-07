<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>


<script language=javascript1.2 src=js/bgt_jam_operasional_pks.js></script>


<?php
include('master_mainMenu.php');
OPEN_BOX();
//'',$_SESSION['lang']['input'].' '.$_SESSION['lang']['']
?>


<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY kodeorganisasi";
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
			{
			$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
			}
$optws="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
?>

<?php
echo"<fieldset style='width:920px;'><legend><b>".$_SESSION['lang']['jamoperasional']."</b></legend>
		<table>
			<tr>
				<td width=100>".$_SESSION['lang']['budgetyear']."</td><td width=10>:</td>
				<td><input type=text id=tahunbudget size=10 onkeypress=\"return angka(event,'0123456789');validatefn(event);\" class=myinputtext maxlength=4 style=\"width:100px;\"></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td><td>:</td>
				<td><select id=kodeorg name=kodeorg style=\"width:100px;\">".$optOrg."</select></td>
				<td align='center' style='width:50px'>Jan</td>
				<td align='center' style='width:50px'>Feb</td>
				<td align='center' style='width:50px'>Mar</td>
				<td align='center' style='width:50px'>Apr</td>
				<td align='center' style='width:50px'>May</td>
				<td align='center' style='width:50px'>Jun</td>
				<td align='center' style='width:50px'>Jul</td>
				<td align='center' style='width:50px'>Aug</td>
				<td align='center' style='width:50px'>Sep</td>
				<td align='center' style='width:50px'>Okt</td>
				<td align='center' style='width:50px'>Nov</td>
				<td align='center' style='width:50px'>Des</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['totJamThn']."</td><td>:</td>
				<td><input type=text class=myinputtextnumber id=jamo name=jmo disabled onkeypress=\"return angka_doang(event);\" style=\"width:100px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo01 name=jamo01 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo02 name=jamo02 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo03 name=jamo03 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo04 name=jamo04 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo05 name=jamo05 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo06 name=jamo06 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo07 name=jamo07 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo08 name=jamo08 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo09 name=jamo09 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo10 name=jamo10 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo11 name=jamo11 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamo12 name=jamo12 onchange=calcJam(1) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['totbreak']."</td><td>:</td>
				<td><input type=text class=myinputtextnumber id=jamb name=jamb disabled onkeypress=\"return angka_doang(event);\" style=\"width:100px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb01 name=jamb01 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb02 name=jamb02 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb03 name=jamb03 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb04 name=jamb04 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb05 name=jamb05 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb06 name=jamb06 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb07 name=jamb07 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb08 name=jamb08 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb09 name=jamb09 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb10 name=jamb10 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb11 name=jamb11 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
				<td><input type=text class=myinputtextnumber id=jamb12 name=jamb12 onchange=calcJam(0) onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"  /></td>
			</tr>
		</table> 
		<table>
			<tr>
				<td style='width:50px;'>
					<input type=hidden id=method value='insert'>
					<input type=hidden id=oldtahunbudget value='insert'>
					<input type=hidden id=oldkodeorg value='insert'>
				</td>
				<td>
					<button class=mybutton onclick=simpanpks()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=batalpks()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
	</fieldset>";
	 
echo open_theme($_SESSION['lang']['datatersimpan']);


		 echo "<div id=container>";
	
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		     <td rowspan=2 style='width:5px'>".substr($_SESSION['lang']['nomor'],0,2)."</td>
			 <td rowspan=2 style='width:85px;'>".$_SESSION['lang']['budgetyear']."</td>
			 <td rowspan=2 style='width:50px'>".$_SESSION['lang']['unit']."</td>
			 <td rowspan=2 style='width:105px'>".$_SESSION['lang']['totJamThn']."</td>
			 <td rowspan=2 style='width:105px'>".$_SESSION['lang']['totbreak']."</td>
			 <td colspan=12 style='width:105px'>".$_SESSION['lang']['totJamThn']."</td>
			 <td colspan=12 style='width:105px'>".$_SESSION['lang']['totbreak']."</td>
			 <td rowspan=2 style='width:50px;'>".$_SESSION['lang']['edit']."</td>
		 </tr>
		 <tr class=rowheader>
				<td align='center' style='width:50px'>Jan</td>
				<td align='center' style='width:50px'>Feb</td>
				<td align='center' style='width:50px'>Mar</td>
				<td align='center' style='width:50px'>Apr</td>
				<td align='center' style='width:50px'>May</td>
				<td align='center' style='width:50px'>Jun</td>
				<td align='center' style='width:50px'>Jul</td>
				<td align='center' style='width:50px'>Aug</td>
				<td align='center' style='width:50px'>Sep</td>
				<td align='center' style='width:50px'>Okt</td>
				<td align='center' style='width:50px'>Nov</td>
				<td align='center' style='width:50px'>Des</td>
				<td align='center' style='width:50px'>Jan</td>
				<td align='center' style='width:50px'>Feb</td>
				<td align='center' style='width:50px'>Mar</td>
				<td align='center' style='width:50px'>Apr</td>
				<td align='center' style='width:50px'>May</td>
				<td align='center' style='width:50px'>Jun</td>
				<td align='center' style='width:50px'>Jul</td>
				<td align='center' style='width:50px'>Aug</td>
				<td align='center' style='width:50px'>Sep</td>
				<td align='center' style='width:50px'>Okt</td>
				<td align='center' style='width:50px'>Nov</td>
				<td align='center' style='width:50px'>Des</td>
		 </tr>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";
        
	
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";


/*	$str1="select * from ".$dbname.".bgt_jam_operasioal_pks order by tahunbudget ";
	$res1=mysql_query($str1);
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
		     <td style='width:5px'>No</td>
			 <td style='width:75px;'>Tahun Budget</td>
			 <td style='width:75px'>Kode PKS</td>
			 <td style='width:75px'>Total Jam</td>
			 <td style='width:75px'>Total Breakdown</td>
			 <td style='width:30px;'>Aksi</td>
		 </tr>		 
		 </thead>
		 <tbody id=container>";
	while($bar1=mysql_fetch_object($res1))
	{
		$no+=1;	
		echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=right>".$bar1->tahunbudget."</td>
			<td align=center>".$bar1->millcode."</td>
			<td align=right>".$bar1->jamolah."</td>
			<td align=right>".$bar1->breakdown."</td>			
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->tahunbudget."','".$bar1->millcode."','".$bar1->jamolah."','".$bar1->breakdown."');\"></td>
		</tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";*/

echo close_theme();
CLOSE_BOX();
echo close_body();
?>