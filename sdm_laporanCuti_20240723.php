<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/sdm_5cuti.js></script>
<?
OPEN_BOX('',$_SESSION['lang']['cuti']);

$optlokasitugas="";
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and length(kodeorganisasi)=4 order by namaorganisasi";
}
else if(trim($_SESSION['empl']['tipelokasitugas']=='KANWIL'))//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and length(kodeorganisasi)=4 and induk='".trim($_SESSION['empl']['kodeorganisasi'])."' order by namaorganisasi";
}
else
{
     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
}
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res))
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
$optperiode='';
for($x=-1;$x<3;$x++)
{
	$dt=date('Y')-$x;
	$optperiode.="<option value='".$dt."'>".$dt."</option>";
}

echo"<fieldset><legend>".$_SESSION['lang']['navigasi']."</legend>
	   <table>
	      <tr>
		      <td>".$_SESSION['lang']['lokasitugas']."</td>
			  <td>:</td>
			  <td><select id=lokasitugas onchange=\"loadkaryawan();\">".$optlokasitugas."</select></td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
				<select id=periode>".$optperiode."</select>
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['namakaryawan']."</td>
			  <td>:</td>
			  <td><select id=karyawan>";
			  ?> <script>loadkaryawan();</script> <? echo "</select></td>
		  </tr>
		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"loadLaporan()\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"cutiToExcel(document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value,document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value,document.getElementById('karyawan').options[document.getElementById('karyawan').selectedIndex].value,event)\">".$_SESSION['lang']['excel']."</button>
			  </td>
		  </tr>	  
	   </table>
	 </fieldset>  
    ";
CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=containerlist1 style='height:350px;overflow:scroll'>
      </div>"; 
CLOSE_BOX();
echo close_body();
?>