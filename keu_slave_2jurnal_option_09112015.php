<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');



$pt=isset($_POST['pt'])?$_POST['pt']:'';
$regional=isset($_POST['regional'])?$_POST['regional']:'';
$proses=isset($_POST['proses'])?$_POST['proses']:'';
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch($proses)
{
	case'getReg':
            $optReg="<option value=''>".$_SESSION['lang']['all']."</option>";
            $iReg="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' ";
            $nReg=  mysql_query($iReg) or die (mysql_error($conn));
            while($dReg=  mysql_fetch_assoc($nReg))
            {
                $optReg.="<option value='".$dReg['regional']."'>".$dReg['regional']."</option>";
            }
            echo $optReg;
	break;
        
        case'getUnit':
            $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
            $iUnit="select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' "
                    . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') ";
            $nUnit=  mysql_query($iUnit) or die (mysql_error($conn));
            while($dUnit=  mysql_fetch_assoc($nUnit))
            {
                $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
            }
            echo $optUnit;
	break;
        
	
	default;
    
}
?>