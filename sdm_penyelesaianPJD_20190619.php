<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_penyelesaianPJD.js></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['penyelesaianpjd']);
	
		
//=====================================
echo"<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=13>
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  <table class=sortable cellspacing=1 border=0>
      <thead>
	  <tr class=rowheader>
	  <td>No.</td>
	  <td>".$_SESSION['lang']['notransaksi']."</td>
	  <td>".$_SESSION['lang']['karyawan']."</td>
	  <td>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td>".$_SESSION['lang']['tujuan']."</td>
	  <td>".$_SESSION['lang']['uangmuka']."</td>
	  <td>".$_SESSION['lang']['dibayar']."</td>
	  <td>".$_SESSION['lang']['digunakan']."</td>	  
	  <td>".$_SESSION['lang']['dibayar']."</td>
	  <td>".$_SESSION['lang']['approval_status']."</td>	
	  <td>".$_SESSION['lang']['saldo']."</td>
                      <td>".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['ticket']."(PP)</td>
	  <td>".$_SESSION['lang']['action']."</td>
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
  	$notransaksi.=" and notransaksi like '%".$_POST['tex']."%' ";
  }
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg like '%HO'
		and statuspertanggungjawaban=1 and lunas=0
		".$notransaksi."
		order by jlhbrs desc";
}else{
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg not like '%HO'
		and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
		and statuspertanggungjawaban=1 and lunas=0
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
  
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
  $str="select * from ".$dbname.".sdm_pjdinasht 
        where 
		kodeorg like '%HO'
		and statuspertanggungjawaban=1 and lunas=0
		".$notransaksi."
		order by tanggalbuat desc,notransaksi desc limit ".$offset.",20";	
}else{
  $str="select * from ".$dbname.".sdm_pjdinasht 
        where
		kodeorg not like '%HO'
		and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')
		and statuspertanggungjawaban=1 and lunas=0
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
	  
   if($bar->statuspertanggungjawaban==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspertanggungjawaban==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  
   
   $str1="select sum(jumlah) as jumlah, sum(jumlahhrd) as jumlahhrd from ".$dbname.".sdm_pjdinasdt
         where notransaksi='".$bar->notransaksi."'";
   $res1=mysql_query($str1);

   $usage=0;
   $usagehrd=0;
   while($bar1=mysql_fetch_object($res1))
   {
   	 $usage=$bar1->jumlah;
	 $usagehrd=$bar1->jumlahhrd;
   }	 	 
	 //sisa adalah dp diterima kurang penggunaan
	 $vali=($bar->uangmuka+$usage)-($bar->dibayar+$usagehrd);
	//===============================================  

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
	  <td align=right>".number_format($bar->uangmuka,2,'.',',')."</td>
	  <td align=right>".number_format($bar->dibayar,2,'.',',')."</td>
	  <td align=right>".number_format($usage,2,'.',',')."</td>
	  <td align=right>".number_format($usagehrd,2,'.',',')."</td>
	  <td>".$stpersetujuan."</td>
	  <td align=right>".number_format($vali,2,'.',',')."</td>
                      <td align=right><input type=text class=myinputtextnumber size=14 onkeypress=\"return angka_doang(event);\" value=".($bar->notransaksi!=0?$bar->byticket:0)." id=t".$bar->notransaksi." title='Tidak termasuk tiket yang dibayar oleh  karyawan dari uang muka'></td>
	  <td align=center> 
                         <button class=mybutton onclick=savePenyelesaianPJD('".$bar->notransaksi."','".$vali."')>".$_SESSION['lang']['save']."</button>
                         <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\">    
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
  echo "</tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>";   
CLOSE_BOX();
echo close_body();
?>