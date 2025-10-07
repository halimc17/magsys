<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

//+++++++++++++++++++++++++++++++++++++++++++++
//list employee
$kodeorganisasi=$_POST['kodeorganisasi'];
if($kodeorganisasi==''){
   $kodeorganisasi=$_SESSION['empl']['lokasitugas']; 
}
$str="select karyawanid,namakaryawan,subbagian,tanggalkeluar,b.tipe from ".$dbname.".datakaryawan a
          left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id
          where lokasitugas='".$kodeorganisasi."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") order by namakaryawan";
$res=mysql_query($str);
$opt="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
    if($bar->tanggalkeluar!='0000-00-00' and $bar->tanggalkeluar!='')
        $add=" Keluar: ".$bar->tanggalkeluar;
    else
        $add='';
    $opt.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->subbagian."]-".$bar->tipe."-".$add."</option>";
}
echo $opt;
?>