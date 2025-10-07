<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_5lembur.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['tipelembur']);

$tipelembur='';
$tipelembur="<option value=0>".$_SESSION['lang']['haribiasa']."</option>
            <option value=1>".$_SESSION['lang']['hariminggu']."</option>
			<option value=2>".$_SESSION['lang']['harilibur']."</option>
			<option value=3>".$_SESSION['lang']['hariraya']."</option>
			";




if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
    $listOrg=" kodeorg in (select kodeorganisasi from ".$dbname.".organisasi  where length(kodeorganisasi)=4) ";

    
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'"
        . "and kodeorganisasi not like '%HO%' ";
    $listOrg=" kodeorg in (select kodeorganisasi from ".$dbname.".organisasi  where induk='".$_SESSION['org']['kodeorganisasi']."' "
            . " and kodeorganisasi not like '%HO%') ";
}
else
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'"
        . "and kodeorganisasi not like '%HO%' ";
    $listOrg=" kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ";
} 
$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
while ($data=mysql_fetch_assoc($qry))
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']."</option>";
        
}



echo"<fieldset style='width:500px;'><table>
     <tr><td>".$_SESSION['lang']['kodeorg']."</td><td><select id=kodeorg>".$optOrg."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['tipelembur']."</td><td><select id=tipelembur>".$tipelembur."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['jamaktual']."</td><td><input type=text id=jamaktual size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 value=0 onblur=change_number(this)></td></tr>
     <tr><td>".$_SESSION['lang']['jamlembur']."</td><td><input type=text id=jamlembur size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 value=0 onblur=change_number(this)></td></tr>
	 </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJ()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
echo open_theme($_SESSION['lang']['availfunct']);
echo "<div>";
	$str1="select *,
	     case tipelembur when '0' then '".$_SESSION['lang']['haribiasa']."'
		 when '1' then '".$_SESSION['lang']['hariminggu']."'
		 when '2' then '".$_SESSION['lang']['harilibur']."'
		 when '3' then '".$_SESSION['lang']['hariraya']."'
		 end as ketgroup 
                 from ".$dbname.".sdm_5lembur where ".$listOrg."
		 order by kodeorg,tipelembur,jamaktual";
	$res1=mysql_query($str1);//where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'

	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
                    <td>".$_SESSION['lang']['nourut']."</td>
		    <td style='width:150px;'>".$_SESSION['lang']['kodeorg']."</td>
			<td>".$_SESSION['lang']['tipelembur']."</td>
			<td>".$_SESSION['lang']['jamaktual']."</td>
			<td>".$_SESSION['lang']['jamlembur']."</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>"; 
	while($bar1=mysql_fetch_object($res1))
	{
            $no+=1;
		echo"<tr class=rowcontent>
                        <td>".$no."</td>
		           <td align=center>".$bar1->kodeorg."</td>
				   <td>".$bar1->ketgroup."</td>
				   <td align=center>".$bar1->jamaktual."</td>
				   <td align=center>".$bar1->jamlembur."</td>
				   <td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->tipelembur."','".$bar1->jamaktual."','".$bar1->jamlembur."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>