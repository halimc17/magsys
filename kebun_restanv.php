<?php
//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".strtoupper("Restan")."</b>");
//print_r($_SESSION['temp']);
?>


<script language=javascript1.2 src='js/kebun_restanv.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>



<!--deklarasi untuk option-->
<?php

$optpersch=$optdivsch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optdiv=$optsms =$optthn=$optstblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' ";
$res=mysql_query($str) or die (mysql_error($str));
while($bar=mysql_fetch_assoc($res))
{
	$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optdivsch.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$res=mysql_query($str) or die (mysql_error($str));
while($bar=mysql_fetch_assoc($res))
{
	$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
       
}

/*
$str="select distinct tahun from ".$dbname.".kebun_rencanapanen where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ";
$res=mysql_query($str) or die (mysql_error($str));
while($bar=mysql_fetch_assoc($res))
{
	$optthn.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
	$optthnsch.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
       
}
*/


?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=persch style=\"width:150px;\">'".$optpersch."'</select></td>
        <tr>
            <td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td colspan=4><select id=divisisch  style=\"width:150px;\">'".$optdivsch."'</select></td>
		</tr>
		<tr><td colspan=2>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
        </td></tr>
	
	";
        echo "</table>";
		
	
echo"</fieldset></td>";

echo"</td>
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>



<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 

echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();//Divisi	Semester	Bulan	Tahun	Jumlah Jjg	Jumlah Kg	BJR	Aksi

	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
               
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:40%>
                <thead>
                    <tr class=rowheader>
						<td  align=center>".$_SESSION['lang']['nourut']."</td>
						<td  align=center>".$_SESSION['lang']['periode']."</td>
                        <td  align=center>".$_SESSION['lang']['divisi']."</td>
						<td  align=center>".$_SESSION['lang']['jjg']."</td>
                        <td  align=center>".$_SESSION['lang']['action']."</td>    
                    </tr>  
                </thead>
         
		 <tbody id=contain> 
                    <script>loaddata(0)</script>
                 </tbody>
                <tfoot id=footData>
                 </tfoot>
                 </table>
                
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php




echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
		
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=per style=\"width:150px;\">'".$optper."'</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['divisi']."</td>
		<td>:</td>
		<td colspan=20><select id=divisi  style=\"width:150px;\">'".$optdiv."'</select></td>
	</tr>
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();
echo "<div id=detailinput></div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
    
