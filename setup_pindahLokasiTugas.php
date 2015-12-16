<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/setup_gantiLokasiTugas.js></script>
<?
include('master_mainMenu.php');
$jabatan = array('IT','OMIS');
if(in_array($_SESSION['empl']['bagian'],$jabatan)){
    $str="select kodeorganisasi,namaorganisasi,alokasi from ".$dbname.".organisasi 
      where length(kodeorganisasi)=4 
	  order by namaorganisasi";
}else{
$str="select kodeorganisasi,namaorganisasi,alokasi from ".$dbname.".organisasi 
      where tipe='HOLDING' and length(kodeorganisasi)=4 
	  order by namaorganisasi";
}
$res=mysql_query($str);
   $opt="<option value='".$_SESSION['empl']['kodeorganisasi']."'>".$_SESSION['empl']['lokasitugas']."</option>";
while($bar=mysql_fetch_object($res))
{
	$opt.="<option value='".$bar->alokasi."'>".$bar->kodeorganisasi."</option>";
}
OPEN_BOX('',$_SESSION['lang']['pindahtugas']);
echo "<br><br>You are ON:<b>".$_SESSION['empl']['lokasitugas']."</b><br> ".$_SESSION['lang']['tujuan']."
      <select id=tjbaru>".$opt."</select><br>
	  <button class=mybutton onclick=gantiLokasitugas()>".$_SESSION['lang']['save']."</button>
	  ";
CLOSE_BOX();
echo close_body();
?>
