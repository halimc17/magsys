<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pjdinas.js'></script>
<?
OPEN_BOX('',$_SESSION['lang']['perjalanandinas']);

if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
	$whereKary = " and tipekaryawan in ('0','2','7','8','9') and lokasitugas like '%HO'";
} else {
	$whereKary = " and lokasitugas not like '%HO' and kodeorganisasi in (select induk FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."')";
}
//ambil karyawan permanen
$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') ".$whereKary." 
	  and (bagian='HRA' or bagian='HHRS')
	  and karyawanid in (select karyawanid FROM ".$dbname.".user where status='1') 
	  and karyawanid <>".$_SESSION['standard']['userid']. " order by namakaryawan";
$res=mysql_query($str);
// $optKar="<option value=''></option>";
$optKar = "";
$optKar2="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
	$optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
	$optKar2.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}	  	

$str="select namaorganisasi from ".$dbname.".organisasi
      where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	$namalokasitugas=$bar->namaorganisasi;
}

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
      where length(kodeorganisasi)=4 and kodeorganisasi<>'".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
$res=mysql_query($str);
echo mysql_error($conn);
$optPemberiTgs="<option value='".$_SESSION['empl']['lokasitugas']."'></option>";
$lokasitugas="<option value='".$_SESSION['empl']['lokasitugas']."'>".$namalokasitugas."</option>";
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
   while($bar=mysql_fetch_object($res))
   {
	   if(substr($bar->kodeorganisasi,2,2)=='HO'){
		   if($bar->kodeorganisasi=='AMHO' or $bar->kodeorganisasi=='CKHO' or $bar->kodeorganisasi=='MPHO' or $bar->kodeorganisasi=='KAHO'){
			   $lokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
		   }
	   }
	   if($bar->kodeorganisasi==$_SESSION['empl']['lokasitugas']){
		   $optPemberiTgs.="<option value='".$bar->kodeorganisasi."' selected>".$bar->namaorganisasi."</option>";
	   }
   }
}
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
	$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and kodeorganisasi<>'".$_SESSION['empl']['lokasitugas']."' 
 		  and induk in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') order by namaorganisasi";
}else{
	$str="select a.kodeorganisasi,a.namaorganisasi,b.regional from ".$dbname.".organisasi a left join ".$dbname.".bgt_regional_assignment b on a.kodeorganisasi=b.kodeunit where length(a.kodeorganisasi)=4 and a.kodeorganisasi<>'".$_SESSION['empl']['lokasitugas']."' and b.regional not in (select d.regional from ".$dbname.".organisasi c left join ".$dbname.".bgt_regional_assignment d on c.kodeorganisasi=d.kodeunit where c.kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') order by a.namaorganisasi";
}
$res=mysql_query($str);
echo mysql_error($conn);
$optOrg="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
	$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$bagianHRD = array('HHRD','HRA');
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or !in_array($_SESSION['empl']['bagian'],$bagianHRD)) {
	$where = "karyawanid=".$_SESSION['standard']['userid'];
} else {
	$where = "lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
}
$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where);

//$lokasitugas = $_SESSION['empl']['lokasitugas'];

$frm[0]="
     <fieldset>
	  <legend>".$_SESSION['lang']['form']."</legend>
     <table>
	 <tr>
	   <input type=hidden value='insert' id=method>
	   <input type=hidden value='' id=notransaksi>
	    <td>".$_SESSION['lang']['nama']."</td>
		<td>".makeElement('karyawanid','select',$_SESSION['standard']['userid'],
						  array(),$optKary)."</td>
	 </tr>
	 <tr>
	    <td>".$_SESSION['lang']['kodeorg']."</td>
		<td><select id='kodeorg' onchange=getTujuan()>".$lokasitugas."'</select></td>
	 </tr>	 
	 <tr>
	    <td>".$_SESSION['lang']['tanggaldinas']."</td>
		<td><input type=text id=tanggalperjalanan class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
		    ".$_SESSION['lang']['tanggalkembali']." 
			<input type=text id=tanggalkembali class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=10>
		</td>
	 </tr>	
	 <tr>
	    <td>".$_SESSION['lang']['transportasi']."/".$_SESSION['lang']['akomodasi']."</td>
		<td>
		     <input type=checkbox id=pesawat> ".$_SESSION['lang']['pesawatudara']."
			 <input type=checkbox id=darat> ".$_SESSION['lang']['transportasidarat']."
			 <input type=checkbox id=laut> ".$_SESSION['lang']['transportasiair']."
			 <input type=checkbox id=mess> ".$_SESSION['lang']['mess']."
			 <input type=checkbox id=hotel> ".$_SESSION['lang']['hotel']."
        </td>
	 </tr>	
	 <tr>
	   <td style='display:none;'>
	      ".$_SESSION['lang']['uangmuka']."
	   </td>
	   <td style='display:none;'>
	     <input type=text class=myinputtextnumber onblur=change_number(this) id=uangmuka value='0' onkeypress=\"return angka_doang(event);\" size=15 maxlength=15>
	   </td>
	 </tr>
	  <tr>
	   <td>
	      ".$_SESSION['lang']['pemberitugas']."
	   </td>
	   <td>
	     <select id='tujuan1' style='width:150px' disabled='true'>".$optPemberiTgs."</select>
		 <input type=hidden id=tugas1 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254>
	   </td>
	 </tr>
	 </table>
	 <table>
	   <tr> 
	     <td>
		    ".$_SESSION['lang']['tujuan']."1
		 </td>
	     <td>
		    <select id='tujuan2' style='width:150px'>".$optOrg."</select>
			".$_SESSION['lang']['tugas']."
			<input type=text id=tugas2 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254>
		 </td>		 		 		 
	   </tr>
		</tr>
		<tr>	   
	   <tr>
	     <td>
		     ".$_SESSION['lang']['tujuan']."2
		 </td>
	     <td>
		   <select id='tujuan3' style='width:150px'>".$optOrg."</select>
		   ".$_SESSION['lang']['tugas']."
		   <input type=text id=tugas3 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254>
		 </td>
		</tr>
		<tr>		 
	     <td>
		    ".$_SESSION['lang']['tujuan']."3
		 </td>
	     <td>
		    <input type=text style='width:150px' id=tujuanlain class=myinputtext onkeypress=\"return tanpa_kutip(event)\" maxlength=45>
		    ".$_SESSION['lang']['tugas']."
			<input type=text id=tugaslain class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254>
		 </td>		 		 		 
	   </tr>
	 </table>
	   <fieldset>
	      <legend>
		    ".$_SESSION['lang']['approve']."
		  </legend>
		  <table>
		   <tr>
		     <td style='display:none;'>".$_SESSION['lang']['atasan']."</td>
			 <td style='display:none;'>
			    <select id=persetujuan>".$optKar."</select>
			 </td>
		     <td>".$_SESSION['lang']['hrd']."</td>
			 <td>
			    <select id=hrd>".$optKar."</select>
			 </td>			 
		   </tr>
		  </table>
	   </fieldset>	 
	 <center>
	   <button class=mybutton onclick=simpanPJD()>".$_SESSION['lang']['save']."</button>
	   <button class=mybutton onclick=clearForm()>".$_SESSION['lang']['new']."</button>
	 </center>
	 </fieldset>";

$frm[1]="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset><legend></legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=13>
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset>
	  <table class=sortable cellspacing=1 border=0>
      <thead>
	  <tr class=rowheader>
	  <td>No.</td>
	  <td>".$_SESSION['lang']['notransaksi']."</td>
	  <td>".$_SESSION['lang']['karyawan']."</td>
	  <td>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td>".$_SESSION['lang']['tujuan']."</td>
	  <td>".$_SESSION['lang']['approval_status']."</td>
	  <td>".$_SESSION['lang']['hrd']."</td>
	  <td></td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
$notransaksi="";
  if(isset($_POST['tex']))
  {
  	$notransaksi.=$_POST['tex'];
  }
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where notransaksi like '%".$notransaksi."%'
		and karyawanid=".$_SESSION['standard']['userid']."
		order by jlhbrs desc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$jlhbrs=$bar->jlhbrs;
}		
//==================
		 
  if(isset($_POST['page']))
     {
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	 }
	 
  
  $offset=$page*$limit;  

  $str="select * from ".$dbname.".sdm_pjdinasht 
        where notransaksi like '%".$notransaksi."%'
        and karyawanid=".$_SESSION['standard']['userid']."
		order by tanggalbuat desc limit ".$offset.",20";	
//		order by notransaksi desc limit ".$offset.",20";	 
  $res=mysql_query($str);
  $no=$page*$limit;
  while($bar=mysql_fetch_object($res))
  {
  	$no+=1;

	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;

	  $resx=mysql_query($strx);
	  while($barx=mysql_fetch_object($resx))
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  if($bar->statushrd==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
		 &nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
         ";
	  }
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else {
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	
	$stpersetujuan.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select  style='width:100px;' onchange=ganti(this.options[this.selectedIndex].value,'persetujuan','".$bar->notransaksi."')>".$optKar."</select>";
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
	 $sthrd.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select   style='width:100px;' onchange=ganti(this.options[this.selectedIndex].value,'hrd','".$bar->notransaksi."')>".$optKar2."</select>";
  }

  $tujuan=$bar->tujuan1;
  if($bar->tujuan2!=''){
	$tujuan=$bar->tujuan2;
  }elseif($bar->tujuan3!=''){
	$tujuan=$bar->tujuan3;
  }elseif($bar->tujuanlain!=''){
	$tujuan=$bar->tujuanlain;
  }

	$frm[1].="<tr class=rowcontent>
	  <td>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$tujuan."</td>
	  <td>".$stpersetujuan."</td>
	  <td>".$sthrd."</td>	
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
       ".$add."
	  </td>
	  </tr>";
  }
  $frm[1].="<tr><td colspan=11 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";	   
$frm[1].="</tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
 	 
drawTab('FRM',$hfrm,$frm,100,900);
CLOSE_BOX();
echo close_body('');
?>