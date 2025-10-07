<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param=$_POST;
$kdid=substr($param['gudangId'],0,4);
$whrt="kodeorganisasi='".substr($param['gudangId'],0,4)."'";
$optInduk=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', $whrt);

switch($param['proses']){
    case'list':
		$q = "select a.nosj,a.kodebarang,a.notransaksireferensi,a.jenis
              from ".$dbname.".log_suratjalandt a
              left join ".$dbname.".log_suratjalanht b on a.nosj=b.nosj
              where a.notransaksireferensi='' and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.kodept='".$optInduk[$kdid]."'" 
             . " group by nosj,jenis order by nosj desc";
		$res = fetchData($q);
        $str ="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $exist = array();
        foreach($res as $row) {
                if(!in_array($row['nosj'],$exist)) {
                        $str .= "<option value='".$row['nosj']."'>".$row['nosj']." [".$row['jenis']."]</option>";
                        $exist[$row['nosj']] = $row['nosj'];
                } 
        }
        echo $str;
    break;
    case'crLst':
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['nosj']."</td>";
        $tab.="<td>".$_SESSION['lang']['tipe']."</td></tr></thead><tbody>";
        
        if($param['txtcrNosj']!=''){
            $add="and nosj like '%".$param['txtcrNosj']."%'";
        }
        $q = "select distinct a.nosj,a.kodebarang,a.notransaksireferensi,a.jenis
              from ".$dbname.".log_suratjalandt a
              left join ".$dbname.".log_suratjalanht b on a.nosj=b.nosj
              where a.notransaksireferensi='' and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.kodept='".$optInduk[$kdid]."'"
              . " and a.nosj like '%".$param['txtcrNosj']."%'" 
             . "group by nosj,jenis order by nosj desc";
                
        $sqDt=  mysql_query($q) or die(mysql_error($conn));
        while($rDt=  mysql_fetch_assoc($sqDt)){
			$no+=1;
			$addTr="onclick=setNosj('".$rDt['nosj']."','".$rDt['jenis']."') title='click ".$rDt['nosj']."' style=cursor:pointer";
			$tab.="<tr class=rowcontent ".$addTr.">";
			$tab.="<td>".$no."</td>";
			$tab.="<td>".$rDt['nosj']."</td>";
			$tab.="<td>".$rDt['jenis']."</td>";
			$tab.="</tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
}
