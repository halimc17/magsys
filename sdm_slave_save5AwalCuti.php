<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
$karyawanid=$_POST['karyawanid'];
$lokasitugas=$_POST['lokasitugas'];
$periode=$_POST['periode'];
$dari=$_POST['dari'];
$sampai=$_POST['sampai'];
$hak=0+$_POST['hak'];

#ambil sisa periode lalu
$periodelalu=$periode-1;
$str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$karyawanid." and periodecuti='".$periodelalu."'";
$sisalalu=0;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $sisalalu=$bar->sisa; // -10
}
//if($sisalalu<0)#jika saldolalu minus maka dibawa
//   $hak=$hak+$sisalalu;// 5

//ambil sum jumlah diambil dan update table header
    $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
           where karyawanid=".$karyawanid."
               and periodecuti='".$periode."'";

    $diambil=0;
    $resx=mysql_query($strx);
    while($barx=mysql_fetch_object($resx))
    {
            $diambil=0+$barx->diambil; // 5
    }
$sisa=0+$hak-$diambil;
/*
$str="update ".$dbname.".sdm_cutiht 
      set dari=".$dari.",
	  sampai=".$sampai.",
	  hakcuti=".$hak.",
	  diambil=".$diambil.",
	  sisa=".$sisa."
     where 
      kodeorg='".$lokasitugas."'
	  and karyawanid=".$karyawanid."
	  and periodecuti='".$periode."'";
*/
$str="update ".$dbname.".sdm_cutiht 
      set dari=".$dari.",
	  sampai=".$sampai.",
	  hakcuti=".$hak.",
	  diambil=".$diambil.",
	  sisa=hakcuti-".$diambil."
     where
	  karyawanid=".$karyawanid."
	  and periodecuti='".$periode."'";
mysql_query($str);
if(mysql_affected_rows($conn)<1)
{	  
$str="insert into ".$dbname.".sdm_cutiht(kodeorg,`karyawanid`,
      `periodecuti`,`dari`,`sampai`,`hakcuti`,`sisa`,`diambil`,`keterangan`)
	  values(
	  '".$lokasitugas."',".$karyawanid.",'".$periode."',
	  ".$dari.",".$sampai.",".$hak.",".$sisa.",".$diambil.",'".$_SESSION['standard']['username']."'
	  )";
  if(mysql_query($str))
  {
  	
  }
  else
  {
  	echo addslashes(mysql_error($conn));
  }
}

		 
?>