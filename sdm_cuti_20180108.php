<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_5cuti.js'></script>
<?
OPEN_BOX('',$_SESSION['lang']['cuti']);
$optlokasitugas="";
if(trim($_SESSION['org']['tipeinduk'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and length(kodeorganisasi)=4 order by namaorganisasi";
	$res=mysql_query($str);
	while($bar=mysql_fetch_object($res))
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
}
else if(trim($_SESSION['org']['induk']!=''))//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{
     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
	$res=mysql_query($str);
	#echo mysql_error($conn);
	while($bar=mysql_fetch_object($res))
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
}
$optperiode='';
for($x=0;$x<3;$x++)
{
	$dt=date('Y')-$x;
	$optperiode.="<option value='".$dt."'>".$dt."</option>";
}

$opttipekaryawan = '';
$strTipe = "select * from ".$dbname.".sdm_5tipekaryawan where id in (0,1,2,3,7,8) order by id";
$resTipe = mysql_query($strTipe);
$opttipekaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($resTipe)){
	$opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";
}

echo"
     <fieldset><legend>".$_SESSION['lang']['navigasi']."</legend>
	   <table>
	      <tr>
		      <td>".$_SESSION['lang']['lokasitugas'].":</td><td><select id=lokasitugas>".$optlokasitugas."</select></td>
		      <td>".$_SESSION['lang']['tipekaryawan'].":</td><td><select id=tipekaryawan>".$opttipekaryawan."</select></td>
		      <td>".$_SESSION['lang']['periode'].":</td><td><select id=periode>".$optperiode."</select></td>
		      <td><button class=mybutton onclick=\"loadList(document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value,document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value)\">".$_SESSION['lang']['lihat']."</button></td>
			  <td><button class=mybutton onclick=prosesAwal()>".$_SESSION['lang']['proses']."</button></td>
		  </tr>	  
	   </table>
	 </fieldset>  
    ";


CLOSE_BOX();
OPEN_BOX('','');
$arr[0]="<div id=containerlist1 style='width:1000px;height:350px;overflow:scroll'>
      </div>";
$arr[1]="<div id=containerlist2 style='width:1000px;height:350px;overflow:scroll'>
      </div>";	  
$hfrm[0]=$_SESSION['lang']['header'];
$hfrm[1]=$_SESSION['lang']['detail'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$arr,100,900);	  
CLOSE_BOX();
echo close_body();
?>