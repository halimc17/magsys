<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
$param = $_POST;

#periksa periode gudang
$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodegudang']."' and tutupbuku=0";
$qry = mysql_query($str) or die(mysql_error($conn));
$res = mysql_fetch_assoc($qry);

#ambil harga terakhir berdasarkan periode
$sHrg = "select * from ".$dbname.".log_5saldobulanan where kodegudang='".$param['kodegudang']."' and kodebarang = '".$param['kodebarang']."' and periode='".$res['periode']."'";
$qHrg = mysql_query($sHrg) or die(mysql_error($conn));
$rHrg = mysql_fetch_assoc($qHrg);

is_null($rHrg['hargarata'])?$rHrg['hargarata']=0:$rHrg['hargarata']=$rHrg['hargarata'];

echo $rHrg['hargarata'];