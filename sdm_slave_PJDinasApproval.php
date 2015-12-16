<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once ('lib/nangkoelib.php');
$notransaksi=$_POST['notransaksi'];
$karyawanid=$_POST['karyawanid'];
$status=$_POST['status'];
$kolom=$_POST['kolom'];
$tanggal=date('Ymd');

$kolomstatus='status'.$kolom;
$kolomtanggal='tanggal'.$kolom;

$str="update ".$dbname.".sdm_pjdinasht set ".$kolomstatus."=".$status.", 
      ".$kolomtanggal."=".$tanggal." where notransaksi='".$notransaksi."'";	  
if(mysql_query($str))
{
    //ambil email notifikasi ke GA
    $str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='X2' limit 1";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
     $to=$bar->nilai;
    }
    
    if($status=='1' and $to!='')
    {
        $str="select a.tanggalperjalanan,a.kodeorg,a.tujuan1,a.tugas1,b.namakaryawan,b.bagian from ".$dbname.".sdm_pjdinasht a
              left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
              where a.notransaksi='".$notransaksi."'";
        
        $res=mysql_query($str);

        while($bar=mysql_fetch_object($res))
        {
            $nama=$bar->namakaryawan;
            $tanggal=tanggalnormal($bar->tanggalperjalanan);
            $tujuan=$bar->tujuan1;
            $bagian=$bar->bagian;
            $tugas=$bar->tugas1;
        }
        
        $subject="[Notifikasi] Perjalanan Dinas";
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Telah disetujui perjalanan dinas  A/n:".$nama." (".$bagian.")<br>
                   Tujuan:".$tujuan."<br>
                   Tugas :".$tugas."<br>
                   Tanggal:".$tanggal."
                   <br>
                   <br>
                   <br>
                   Regards,<br>
                   Owl-Plantation System.
                 </body>
                 </head>
               </html>
               ";
        $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;  
    }

}
else
{
	echo addslashes(mysql_error($conn));
}	  
?>
