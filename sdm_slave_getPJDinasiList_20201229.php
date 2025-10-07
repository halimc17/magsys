<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
	$whereKary = " and bagian = 'HHRS' and lokasitugas like '%HO'";
} else {
	$whereKary = " and bagian = 'HRA' and lokasitugas not like '%HO' and kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
}

//ambil karyawan permanen
$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and
	  tipekaryawan in ('0','2','7','8','9') ".$whereKary." and
	  karyawanid <>".$_SESSION['standard']['userid']. " order by namakaryawan";

$res=mysql_query($str);
// $optKar="<option value=''></option>";
$optKar = "";
$optKar2="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
	$optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
	$optKar2.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}	

//limit/page
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
	$stpersetujuan.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select style='width:100px;'  onchange=ganti(this.options[this.selectedIndex].value,'persetujuan','".$bar->notransaksi."')>".$optKar."</select>";
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
	 $sthrd.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select style='width:100px;'  onchange=ganti(this.options[this.selectedIndex].value,'hrd','".$bar->notransaksi."')>".$optKar2."</select>";
  }

  $tujuan=$bar->tujuan1;
  if($bar->tujuan2!=''){
	$tujuan=$bar->tujuan2;
  }elseif($bar->tujuan3!=''){
	$tujuan=$bar->tujuan3;
  }elseif($bar->tujuanlain!=''){
	$tujuan=$bar->tujuanlain;
  }

	echo"<tr class=rowcontent>
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
  echo"<tr><td colspan=11 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
?>