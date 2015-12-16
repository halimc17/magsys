<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
        
        $cols = array(
	    'tahuntanam','jammulai','jamselesai','jamstagnasi','downstatus','keterangan','tekananawal','tekananakhir',
	    'nopengolahan','kodeorg'
	);
	$data = $param;
        $data['kodeorg'] = $data['station'];
	unset($data['numRow']);
        unset($data['station']);
        
        
        #ambil total stagnasi ht
        $iHt="select jamstagnasi from ".$dbname.".pabrik_pengolahan where nopengolahan='".$data['nopengolahan']."' ";
        $nHt=  mysql_query($iHt) or die (mysql_error($conn));
        $dHt=  mysql_fetch_assoc($nHt);
            
        #ambil total yang sudah tersimpan
        $iDt="select sum(jamstagnasi) as jamstagnasi  from ".$dbname.".pabrik_pengolahanmesin where nopengolahan='".$data['nopengolahan']."'"
                . " and downstatus='EDT'  ";
        
        $nDt=  mysql_query($iDt) or die (mysql_error($conn));
        $dDt=  mysql_fetch_assoc($nDt);
        
        if($dDt['jamstagnasi']=='')
        {
            $detail=0;
        }
        else
        {
            $detail=$dDt['jamstagnasi'];
        }
        
        
        if($data['downstatus']=='EDT')
        {
            $cek=$data['jamstagnasi'];
        }
        else
        {
            $cek=0;
        }
        
        $totalJam=$detail+$cek;
        
        
        if($totalJam>$dHt['jamstagnasi'])
        {
            exit("Error:Jam stagnasi detail melebihi header");
        }
        
	$query = insertQuery($dbname,'pabrik_pengolahanmesin',$data,$cols);
        //exit("Error:$query");
	if(!mysql_query($query)) {
	    echo "DB Error : ".mysql_error();
	    exit;
	}
	
        $data = $param;
	unset($data['nopengolahan']);
        unset($data['numRow']);
	$res = "";
	foreach($data as $cont) {
	    $res .= "##".$cont;
	}
        $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
	echo $result;
	break;
    case 'edit':
	$data = $param;
        $data['kodeorg'] = $data['station'];
        unset($data['station']);
	unset($data['nopengolahan']);
	foreach($data as $key=>$cont) {
	    if(substr($key,0,5)=='cond_') {
		unset($data[$key]);
	    }
	}
	$where = "nopengolahan='".$param['nopengolahan']."' and kodeorg='".
	    $param['cond_station']."' and tahuntanam='".$param['cond_tahuntanam']."'";
	$query = updateQuery($dbname,'pabrik_pengolahanmesin',$data,$where);
	if(!mysql_query($query)) {
	    echo "DB Error : ".mysql_error();
	    exit;
	}
	echo json_encode($param);
	break;
    case 'delete':
	$where = "nopengolahan='".$param['nopengolahan']."' and kodeorg='".
	    $param['station']."' and tahuntanam='".$param['tahuntanam']."'";
	$query = "delete from `".$dbname."`.`pabrik_pengolahanmesin` where ".$where;
	if(!mysql_query($query)) {
	    echo "DB Error : ".mysql_error();
	    exit;
	}
	break;
    default:
    break;
}
?>