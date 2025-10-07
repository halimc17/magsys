<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



$matauang=isset($_POST['matauang'])?$_POST['matauang']:'';
$proses=isset($_POST['proses'])?$_POST['proses']:'';
$tanggal=isset($_POST['tanggal'])?tanggalsystem($_POST['tanggal']):'';

switch($proses)
{
	case'getKurs':
            
            
            if($matauang!='IDR')
            {
                $iKurs="select kurs from ".$dbname.".setup_matauangrate where kode='".$matauang."' and daritanggal='".$tanggal."' ";
                $nKurs=mysql_query($iKurs) or die (mysql_error($conn));
                $dKurs=mysql_fetch_assoc($nKurs);
                        $kurs=$dKurs['kurs'];
            }
            else 
            {
                $kurs=1;
            }
            echo $kurs;
            
	break;
	
	default;
    
}
?>