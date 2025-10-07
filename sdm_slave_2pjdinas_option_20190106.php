<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=isset($_POST['kodeorg'])?$_POST['kodeorg']:'';
$proses=isset($_POST['proses'])?$_POST['proses']:'';

switch($proses)
{
	case'getReg':
            
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
			{
				$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";
				$iReg="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' ";
			}
			else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
			{
				$iReg="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' and regional<>'JAKARTA' ";	
			}
			else
			{
				$iReg="select distinct(regional) as regional from ".$dbname.".regional_pt 
					where pt='".$pt."' and regional='".$_SESSION['empl']['regional']."' ";	
			}
            $nReg=  mysql_query($iReg) or die (mysql_error($conn));
            while($dReg=  mysql_fetch_assoc($nReg))
            {
                $optReg.="<option value='".$dReg['regional']."'>".$dReg['regional']."</option>";
            }
            echo $optReg;
	break;
        
        case'getUnit':
            $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
            $iUnit="select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' 
					and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') ";
            $nUnit=  mysql_query($iUnit) or die (mysql_error($conn));
            while($dUnit=  mysql_fetch_assoc($nUnit))
            {
                $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
            }
            echo $optUnit;
	break;

        case'getTujuan':
            $optOrg="<option value=''>".''."</option>";
            $iUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'".$kodeorg."' and induk in
				    (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') ";
            $nUnit=  mysql_query($iUnit) or die (mysql_error($conn));
            while($dUnit=  mysql_fetch_assoc($nUnit))
            {
                $optOrg.="<option value='".$dUnit['kodeorganisasi']."'>".$dUnit['namaorganisasi']."</option>";
            }
            echo $optOrg;
	break;
	
	default;
}
?>