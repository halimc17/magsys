<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
  $notransaksi='';
  if(isset($_POST['tex']))
  {
  	$notransaksi.=" and notransaksi like '%".$_POST['tex']."%' ";
  } 
if(strstr(strtoupper(substr($_SESSION['empl']['lokasitugas'],0,4)),'HO')){
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg like '%HO'
		and statuspersetujuan=1 and statushrd=1
		".$notransaksi."
		order by jlhbrs desc";
}else{
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg not like '%HO'
		and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
		and statuspersetujuan=1 and statushrd=1
		".$notransaksi."
		order by jlhbrs desc";
}
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
  
if(strstr(strtoupper(substr($_SESSION['empl']['lokasitugas'],0,4)),'HO')){
  $str="select * from ".$dbname.".sdm_pjdinasht 
        where
        kodeorg like '%HO'
		and statuspersetujuan=1 and statushrd=1
		".$notransaksi."
		order by tanggalbuat desc,notransaksi desc limit ".$offset.",20";	
}else{
  $str="select * from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg not like '%HO'
		and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
		and statuspersetujuan=1 and statushrd=1
		".$notransaksi."
		order by tanggalbuat desc,notransaksi desc limit ".$offset.",20";	
}
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
	  if($bar->statuspertanggungjawaban==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_edit.png class=resicon  title='FollowUp' onclick=\"editPPJD('".$bar->notransaksi."');\">
         ";
	  }
   if($bar->statuspertanggungjawaban==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspertanggungjawaban==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  
   
   $str1="select sum(jumlah) as jumlah,sum(jumlahhrd) as jumlahhrd from ".$dbname.".sdm_pjdinasdt
         where notransaksi='".$bar->notransaksi."'";
   $res1=mysql_query($str1);
   //exit('Warning: '.$str1);
   $usage=0;
   $usagehrd=0;
   while($bar1=mysql_fetch_object($res1))
   {
   	 $usage=$bar1->jumlah;
   	 $usagehrd=$bar1->jumlahhrd;
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
	  <td align=right>".number_format($usage,2,'.',',')."</td>
	  <td align=right>".number_format($usagehrd,2,'.',',')."</td>
	  <td>".$stpersetujuan."</td>
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\">".$add;
	  if($usagehrd<>0){
		 echo "<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPUKPJDPJPDF('".$bar->notransaksi."',event);\">";
	  }
	  echo "</td>
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