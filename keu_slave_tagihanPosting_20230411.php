<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$noinvoice=$_POST['noinvoice'];
$nopo=$_POST['nopo'];
$nofp=$_POST['nofp'];
if($noinvoice==''){
	exit('Warning : No. Invoice tidak boleh kosong!');
}
if($nopo==''){
	exit('Warning : No. PO tidak boleh kosong!');
}

$str="update ".$dbname.".keu_tagihanht set posting=1, postingby=".$_SESSION['standard']['userid']." 
     where noinvoice='".$noinvoice."'";
mysql_query($str);
if(mysql_affected_rows($conn)==0){
    echo "Error: None Updated ".$str;
}else{
	if($nofp!=''){
		$sPpn = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='TX' and kodeparameter='PPNINV'";
		$qPpn=mysql_query($sPpn) or die(mysql_error($conn));
		$akunPpn = '';
		while($dPpn=mysql_fetch_assoc($qPpn)){
			$akunPpn=$dPpn['nilai'];
		}
		//$sql1="select * from ".$dbname.".keu_jurnaldt where nodok='".$nopo."' and noakun='1160100'";
		$sql1="select * from ".$dbname.".keu_jurnaldt where nodok='".$nopo."' and noakun='".$akunPpn."'";
		$qry1=mysql_query($sql1) or die ("SQL ERR : ".mysql_error());
		$row1=mysql_num_rows($qry1);
		$strnofp=true;
		while($res1=mysql_fetch_assoc($qry1)){
			if(strstr(strtoupper($res1['keterangan']),$nofp)){
				$strnofp=false;
			}	
		}
		if($row1>0){
			//$sql2="select * from ".$dbname.".keu_jurnaldt where nodok='".$nopo."' and noakun='1160100' and keterangan like '".$nofp."%'";
			//$qry2=mysql_query($sql2) or die ("SQL ERR : ".mysql_error());
			//$row2=mysql_num_rows($qry2);
			//if($row2==0){
			if($strnofp){
				$str2="update ".$dbname.".keu_jurnaldt set nik='".$nofp."',keterangan=CONCAT(keterangan,' (".$nofp.")') 
				where nodok='".$nopo."' and noakun='".$akunPpn."' and right(keterangan,1)<>')'";
				mysql_query($str2);
				if(mysql_affected_rows($conn)==0){
					echo "Error: None Updated ".$str2;
				}
			}
		}else{
			echo "Warning: Belum ada jurnal pengakuan hutang!";
		}
	}
}
?>
