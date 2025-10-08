<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('config/connection.php');

$str="select * from ".$dbname.".setup_posting order by kodeaplikasi";
$res=mysql_query($str);
$postJabatansdm	=array();
$whereJabatansdm='0';
while($row=mysql_fetch_assoc($res)) {
   if($row['kodeaplikasi']=='sdm'){
	  $whereJabatansdm='1';
      $postJabatansdm[$row['jabatan']] = $row['jabatan'];
   }
}

$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
  if(isset($_POST['tex']))
  {
  	$notransaksi=" and notransaksi like '%".$_POST['tex']."%' ";
  }
  else
  $notransaksi='';
if($whereJabatansdm=='1'){
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where kodeorg like '%HO' order by jlhbrs desc";
	}else{
		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where kodeorg not like '%HO' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') order by jlhbrs desc";
	}
}else{
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where kodeorg like '%HO' and persetujuan=".$_SESSION['standard']['userid']." or hrd=".$_SESSION['standard']['userid']."  order by jlhbrs desc";
	}else{
		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where kodeorg not like '%HO' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') and persetujuan=".$_SESSION['standard']['userid']." or hrd=".$_SESSION['standard']['userid']."  order by jlhbrs desc";
	}
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

if($whereJabatansdm=='1'){
	//$str="select * from ".$dbname.".sdm_pjdinasht order by tanggalbuat desc  limit ".$offset.",20";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str="select * from ".$dbname.".sdm_pjdinasht where kodeorg like '%HO' order by tanggalbuat desc limit ".$offset.",20";
	}else{
		$str="select * from ".$dbname.".sdm_pjdinasht where kodeorg not like '%HO' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') order by tanggalbuat desc limit ".$offset.",20";
	}
}else{
    //$str="select * from ".$dbname.".sdm_pjdinasht where persetujuan=".$_SESSION['standard']['userid']." or hrd=".$_SESSION['standard']['userid']." order by tanggalbuat desc  limit ".$offset.",20";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str="select * from ".$dbname.".sdm_pjdinasht where kodeorg like '%HO' and persetujuan=".$_SESSION['standard']['userid']." or hrd=".$_SESSION['standard']['userid']." order by tanggalbuat desc limit ".$offset.",20";
	}else{
		$str="select * from ".$dbname.".sdm_pjdinasht where kodeorg not like '%HO' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') and persetujuan=".$_SESSION['standard']['userid']." or hrd=".$_SESSION['standard']['userid']." order by tanggalbuat desc  limit ".$offset.",20";
	}
}
  $res=mysql_query($str);
  $no=$page*$limit;
  while($bar=mysql_fetch_object($res))
  {
  	$no+=1;

	  if($bar->persetujuan==$_SESSION['standard']['userid'])
	  {
	  	$per='persetujuan';
	  }
	  else
	  {
	  	$per='hrd';
	  }
	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;

	  $resx=mysql_query($strx);
	  while($barx=mysql_fetch_object($resx))
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  if($bar->statuspersetujuan==0 && $per=='persetujuan')
	  {
	  	$add.="&nbsp <img src=images/onebit_34.png class=resicon  title='".$_SESSION['lang']['disetujui']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',1,'".$per."','".$bar->tujuan2."');\">
		       &nbsp <img src=images/onebit_33.png class=resicon  title='".$_SESSION['lang']['ditolak']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',2,'".$per."','".$bar->tujuan2."');\">
         ";
	  }
	  if($bar->statushrd==0 && $per=='hrd')
	  {
		if(in_array($_SESSION['empl']['kodejabatan'],$postJabatansdm)){
	  	$add.="&nbsp <img src=images/onebit_34.png class=resicon  title='".$_SESSION['lang']['disetujui']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',1,'".$per."','".$bar->tujuan2."');\">
		       &nbsp <img src=images/onebit_33.png class=resicon  title='".$_SESSION['lang']['ditolak']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',2,'".$per."','".$bar->tujuan2."');\">
         ";
		}else{
	  	$add.="&nbsp <img src=images/onebit_34.png class=resicon title='".$_SESSION['lang']['disetujui']."'>
		       &nbsp <img src=images/onebit_33.png class=resicon title='".$_SESSION['lang']['ditolak']."'>
         ";
		}
	  }	  
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else
     $sthrd=$_SESSION['lang']['wait_approve'];


  $tujuan=$bar->tujuan1;
  if($bar->tujuan2!=''){
	$tujuan=$bar->tujuan2;
  }elseif($bar->tujuan3!=''){
	$tujuan=$bar->tujuan3;
  }elseif($bar->tujuanlain!=''){
	$tujuan=$bar->tujuanlain;
  }

  $pukpdf='';
  //if($bar->statushrd=='1'){
	$pukpdf="&nbsp &nbsp <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPUKPJDPDF('".$bar->notransaksi."',event);\">";
  //}
	echo"<tr class=rowcontent>
	  <td>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$tujuan."</td>
	  <td>".$stpersetujuan."</td>
	  <td>".$sthrd."</td>	
	  <td align=center>
	     <img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
       ".$add."
	  &nbsp &nbsp
	   <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJDPDF('".$bar->notransaksi."',event);\"> 
	  ".$pukpdf."</td>
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