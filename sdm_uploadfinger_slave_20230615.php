<?
	require_once('master_validation.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('config/connection.php');

	//if($_FILES['filex']['name']!='1_attlog.dat'){
	if(substr(strtolower($_FILES['filex']['name']),-4)!=".dat"){
		echo ('<br>Warning: File tidak support...!');
		exit;
	}
	$tanggal1=$_POST['tanggal1'];
	$tanggal2=$_POST['tanggal2'];
	$tgl1=substr($tanggal1,6,4)."-".substr($tanggal1,3,2)."-".substr($tanggal1,0,2);
	$tgl2=substr($tanggal2,6,4)."-".substr($tanggal2,3,2)."-".substr($tanggal2,0,2);
	//@$fp=fopen("1_attlog.dat",'r');
	//@$text=fread($fp,filesize("1_attlog.dat"));
	@$fp=fopen($_FILES['filex']['tmp_name'],'r');
	@$text=fread($fp,filesize($_FILES['filex']['tmp_name']));
	@fclose($fp);
	$tg_i=array();
	$ni_i=array();
	//echo '<br>'.$_FILES['filex']['tmp_name'];
	$data = explode(chr(13), $text);
	for ($x = 0; $x <= count($data); $x++) {
		$isi=explode("	", $data[$x]);
		//echo '<br>'.substr($isi[1],0,10).' <= '.$tgl2;
		//if(substr($isi[1],0,10)>='".$tgl1."' && substr($isi[1],0,10)<='".$tgl2."'){
		if(substr($isi[1],0,10)>='".$tgl1."'){
			//echo "<br>".($x+1).'.';
			//for ($y = 0; $y <= count($isi); $y++) {
			//	echo ' '.$isi[$y].'     ';
			//}
			$tgl=substr($isi[1],0,10);
			$nip=trim($isi[0]);
			$tg_i[$tgl]=substr($isi[1],0,10);
			$ni_i[$nip]=trim($isi[0]);
			$tanggal[$tgl][$nip]=substr($isi[1],0,10);
			$nik[$tgl][$nip]=trim($isi[0]);
			if($jamdatang[$tgl][$nip]==''){
				$jamdatang[$tgl][$nip]=substr($isi[1],11,8);
			}
			$jampulang[$tgl][$nip]=substr($isi[1],11,8);
			//echo '<br>'.$x.'. '.$nik[$tgl][$nip].' '.$tanggal[$tgl][$nip].' '.substr($isi[1],11,8).' '.$jampulang[$tgl][$nip].' ';
		}
	}
	asort($tg_i);
	asort($ni_i);
	foreach($tg_i as $tg_ => $tgi){
		$periode=substr($tgi,0,7);
		$tglload=substr($tgi,0,7).'-01';
		foreach($ni_i as $id_ => $idi){
			if($jamdatang[$tgi][$idi]!=''){
				$lbl=$nik[$tgi][$idi].' '.$tanggal[$tgi][$idi].' '.$jamdatang[$tgi][$idi].' '.$jampulang[$tgi][$idi].'        ';
				//echo '<br>'.$lbl;
				$skary="select karyawanid,nik,namakaryawan,kodeorganisasi,lokasitugas,subbagian from ".$dbname.".datakaryawan 
						where nik='".$idi."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$tglload."') limit 1";
				//exit('Warning: '.$skary);
				$qkary= mysql_query($skary) or die (mysql_error($conn));
				$nkary=mysql_num_rows($qkary);
				if($nkary>0){
					while($rkary=mysql_fetch_assoc($qkary)){
						$kodeorg=($rkary['subbagian']=='' ? $rkary['lokasitugas'] : $rkary['subbagian']);
						$sabsht="select * from ".$dbname.".sdm_absensiht where tanggal='".$tgi."' and kodeorg='".$kodeorg."'";
						$qabsht=mysql_query($sabsht) or die (mysql_error($conn));
						if(mysql_num_rows($qabsht)==0){
							$strh="insert into ".$dbname.".sdm_absensiht (tanggal,kodeorg,periode,posting) values ('".$tgi."','".$kodeorg."','".$periode."',0)";
							if(!mysql_query($strh)){
								echo '<br> gatot';
								echo " Gagal, ".$strh.' '.addslashes(mysql_error($conn));
								exit;
							}
						}
						$sabsdt="select * from ".$dbname.".sdm_absensidt 
								where tanggal='".$tgi."' and kodeorg='".$kodeorg."' and karyawanid='".$rkary['karyawanid']."'";
						$qabsdt=mysql_query($sabsdt) or die (mysql_error($conn));
						if(mysql_num_rows($qabsdt)==0){
							$strd="insert into ".$dbname.".sdm_absensidt 
							(kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,catu,penaltykehadiran,premi,insentif,fingerprint) values 
							('".$kodeorg."','".$tgi."','".$rkary['karyawanid']."','1','H','".$jamdatang[$tgi][$idi]."','".$jampulang[$tgi][$idi]."'
							,'0','0','0','0','1')";
							if(!mysql_query($strd)){
								echo " Gagal, ".$strd.' '.addslashes(mysql_error($conn));
								exit;
							}
							//echo "<script>loadlabel(".$idi.")</script>";
						}
					}
				}
			}
		}
	}
	//echo '<br>';
	echo '<br>Selesai...!';
?>
