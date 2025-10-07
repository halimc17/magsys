<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language="javascript" src="js/kebun_5premibasis.js"></script>
<?php
OPEN_BOX();
$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
$arr="##afd##jenispremi##kelaspohon##basis##premilebih##premilibur##".
	"premiliburcapaibasis##topografi##premitopografi##premibrondolan##jenisbasis##premilebih2##premilebih3##method";

$sAfd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$qAfd=mysql_query($sAfd) or die(mysql_error());
$optAfd='';
while($rAfd=mysql_fetch_assoc($qAfd))
{
        $optAfd.="<option value=".$rAfd['kodeorganisasi'].">".$rAfd['namaorganisasi']."</option>";	
}

$aTopo="select distinct(topografi) from ".$dbname.".setup_blok";
$bTopo=mysql_query($aTopo) or die(mysql_error());
$optTopo='';
while($cTopo=mysql_fetch_assoc($bTopo))
{
        $optTopo.="<option value=".$cTopo['topografi'].">".$optTopografi[$cTopo['topografi']]."</option>";	
}

$optJenis = array(
	'KERJA' => 'Hari Kerja',
	'LIBUR' => 'Hari Libur'
);

$optJenisbasis = array(
	'KG'  => 'Kilogram',
	'JJG' => 'Janjang'
);

$sKls="select * from ".$dbname.".kebun_5kelaspohon";
$qKls=mysql_query($sKls) or die(mysql_error($conn));
$optKelas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bKls=mysql_fetch_assoc($qKls))
{
	$optKelas.="<option value=".$bKls['kelas'].">".$bKls['nama']."</option>";	
}


// // $optKelas="<option value=''>".$_SESSION['lang']['pilihdata']"</option>";
// $optKelas = makeOption($dbname,'kebun_5kelaspohon','kelas,nama');

echo"<fieldset>
     <legend style='font-weight:bold'>".$_SESSION['lang']['premisiapbasis']."</legend>
         <table>
            <tr>
                <td>".$_SESSION['lang']['pt']."</td>
                <td><select id='afd' name='afd' style='width:150px;'>".$optAfd."</select></td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['jenispremi']."</td>
                <td>".makeElement('jenispremi','select',"",array(),$optJenis)."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['kelaspohon']."</td>
                <td><select id='kelaspohon' onchange='getBasis()'>".$optKelas."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['basisjjg']."</td>
                <td>".makeElement('basis','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
                <td>".makeElement('jenisbasis','select',"",array(),$optJenisbasis)."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['premilebihbasis']."(Rp/Sat) ke - 1 - </td>
				<td>".makeElement('premilebih','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
                <td><center> ke - 2 - </center></td>
				<td>".makeElement('premilebih2','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
                <td> ke - 3 - </td>
				<td>".makeElement('premilebih3','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['harilibur']." (Rp/Hr)</td>
				<td>".makeElement('premilibur','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['premi']." Capai Basis (Rp/Hr)</td>
				<td>".makeElement('premiliburcapaibasis','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['topografi']."</td>
                <td><select id='topografi' name='topografi' style='width:150px;'>".$optTopo."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['absensi']." (Rp/Hr)</td>
				<td>".makeElement('premitopografi','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['premi']." Brondolan (Rp/Kg)</td>
				<td>".makeElement('premibrondolan','textnum',0,array('style'=>'width:150px','maxlength'=>10))."</td>
            </tr>
         </table>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=simpan('kebun_slave_5premibasis','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=hiddenz name=hiddenz />";
CLOSE_BOX();


OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					





/*OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['afdeling']."</td>
	   <td>".$_SESSION['lang']['bjr']."</td>
	   <td>".$_SESSION['lang']['basisjjg']."</td>
	   <td>".$_SESSION['lang']['premibasis']."</td>
	   <td>".$_SESSION['lang']['premilebihbasis']."(/JJG)</td>
           <td>".$_SESSION['lang']['action']."</td>
	  </tr>
     </thead>
     <tbody id=container>";
echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();*/

echo close_body();
?>
