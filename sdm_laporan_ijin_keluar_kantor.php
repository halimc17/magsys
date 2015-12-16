<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_laporan_ijin_keluar_kantor.js'></script>
<script>
    tolak="<? echo $_SESSION['lang']['ditolak'];?>";
    </script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['izinkntor']).'</b>');

//Lokasi Tugas
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
	while($bar=mysql_fetch_object($res))
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
}

$strApp = "select distinct(a.karyawanid) as karyawanid, b.namakaryawan from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and (a.persetujuan1='".$_SESSION['standard']['userid']."' or hrd='".$_SESSION['standard']['userid']."')";
$qryApp = mysql_query($strApp);

$optKary='';
if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in(0,1,7,8) order by namakaryawan asc";
	$qKary=mysql_query($sKary) or die(mysql_error($sKary));
	while($rKary=mysql_fetch_assoc($qKary))
	{
		$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
	}
}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA'){
	$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optJenis=$optKary;
	$sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in(0,1,7,8) and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' order by namakaryawan asc";
	$qKary=mysql_query($sKary) or die(mysql_error($sKary));
	while($rKary=mysql_fetch_assoc($qKary))
	{
		$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
	}
}else{
	$optKary.="<option value='".$_SESSION['standard']['userid']."'>".$_SESSION['empl']['name']."</option>";
}
while($resApp=mysql_fetch_assoc($qryApp)){
	$optKary.="<option value='".$resApp['karyawanid']."'>".$resApp['namakaryawan']."</option>";
}
$optJenis='';
$optJenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$arragama=getEnum($dbname,'sdm_ijin','jenisijin');
foreach($arragama as $kei=>$fal)
{
	if($_SESSION['language']=='ID'){
		$optJenis.="<option value='".$kei."'>".$fal."</option>";
	}else{
		switch($fal){
			case 'TERLAMBAT':
				$fal='Late for work';
				break;
			case 'KELUAR':
				$fal='Out of Office';
				break;         
			case 'PULANGAWAL':
				$fal='Home early';
				break;     
			case 'IJINLAIN':
				$fal='Other purposes';
				break;   
			case 'CUTI':
				$fal='Leave';
				break;       
			case 'MELAHIRKAN':
				$fal='Maternity';
				break;           
			default:
				$fal='Wedding, Circumcision or Graduation';
				break;                              
		}
		$optJenis.="<option value='".$kei."'>".$fal."</option>";       
	}                    
}

$tglSkrg = date("d-m-Y");

echo"<fieldset><legend>".$_SESSION['lang']['navigasi']."</legend>
	   <table>
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
					<input type=text class=myinputtext id=periodeawal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly='true' value='".$tglSkrg."' /> s/d 
					<input type=text class=myinputtext id=periodeakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly='true' value='".$tglSkrg."' />
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['jeniscuti']."</td>
			  <td>:</td>
			  <td><select id=jnsCuti>".$optJenis."</select></td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['namakaryawan']."</td>
			  <td>:</td>
			  <td><select id=karyidCari>".$optKary."</select></td>
		  </tr>
		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"getCariDt()\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"detailExcel(event,'sdm_slave_laporan_ijin_meninggalkan_kantor.php')\">".$_SESSION['lang']['excel']."</button>
			  </td>
		  </tr>	  
	   </table>
	 </fieldset>  
    ";

	
// echo"
     // <img onclick=detailExcel(event,'sdm_slave_laporan_ijin_meninggalkan_kantor.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
     // &nbsp;".$_SESSION['lang']['namakaryawan'].": <select id=karyidCari style=width:150px onchange=getCariDt()>".$optKary."</select>&nbsp;
     // ".$_SESSION['lang']['jeniscuti'].": <select id=jnsCuti style=width:150px onchange=getCariDt()>".$optJenis."</select>&nbsp;
         // <button class=mybutton onclick=dtReset()>".$_SESSION['lang']['cancel']."</button>
         // 
CLOSE_BOX();
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['list']).'</b>');
echo "<div style='width:100%;height:600px;overflow:scroll;'>
		<table class=sortable cellspacing=1 border=0>
             <thead>
                    <tr>
                          <td align=center>No.</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>
                          <td align=center>".$_SESSION['lang']['nama']."</td>
                          <td align=center>".$_SESSION['lang']['keperluan']."</td>
                          <td align=center>".$_SESSION['lang']['jenisijin']."</td>  
                          <td align=center>".$_SESSION['lang']['persetujuan']."</td>    
                          <td align=center>".$_SESSION['lang']['approval_status']."</td>
                          <td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
                          <td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
                          <td align=center>".$_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil']."</td>
                          <td align=center>".$_SESSION['lang']['cuti']." ".$_SESSION['lang']['sisa']."</td>
                          <td align=center>".$_SESSION['lang']['atasan']."</td>
                          <td align=center>".$_SESSION['lang']['hrd']."</td> 
                          <td align=center>".$_SESSION['lang']['print']."</td>    
                        </tr>  
                 </thead>
                 <tbody id=container><script>loadData()</script>
                 </tbody>

           </table>
     </div>";
CLOSE_BOX();
close_body();
?>