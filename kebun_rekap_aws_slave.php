<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kebun0 = checkPostGet('kebun0','');
$kebun1 = checkPostGet('kebun1','');
$kebun2 = checkPostGet('kebun2','');
$periode= checkPostGet('periode','');
$kebun3 = checkPostGet('kebun3','');
$tahun  = checkPostGet('tahun','');
if(!empty($kebun0)){
	$lapke=0;
	$kodeorg=$kebun0;
	$tgl1 = tanggalsystemn(checkPostGet('tgl10',''));
	$tgl2 = tanggalsystemn(checkPostGet('tgl20',''));
}
if(!empty($kebun1)){
	$lapke=1;
	$kodeorg=$kebun1;
	$tgl1 = tanggalsystemn(checkPostGet('tgl11',''));
	$tgl2 = tanggalsystemn(checkPostGet('tgl21',''));
}
if(!empty($kebun2)){
	$lapke=2;
	$kodeorg=$kebun2;
}
if(!empty($kebun3)){
	$lapke=3;
	$kodeorg=$kebun3;
}
if($tgl1=='--'){
    $tgl1='';
}
if($tgl2=='--'){
    $tgl2='';
}
if($tgl1=='' and  $tgl2!=''){
	$tgl1=$tgl2;
}
if($tgl1!='' and  $tgl2==''){
	$tgl2=$tgl1;
}
if($tgl1=='' and  $tgl2==''){
	if($lapke==0 or $lapke==1){
		exit('Warning: Tanggal tidak boleh kosong...!');
	}
}
if($proses=='excel'){
    $border="border=1";
}else{
    $border="border=0";
}
///bgcolor=#CCCCCC border='1'
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$stream="<table cellspacing='1' $border class='sortable'>";
$stream.="<thead><tr class=rowheader>
            <td rowspan=3 align=center>No.</td>
            <td rowspan=3 align=center>".$_SESSION['lang']['unit']."</td>";
if($lapke==3){
	$stream.="<td rowspan=3 align=center>".$_SESSION['lang']['tahun']."</td>";
}else if($lapke==2){
	$stream.="<td rowspan=3 align=center>".$_SESSION['lang']['bulan']."</td>";
}else{
	$stream.="<td rowspan=3 align=center>".$_SESSION['lang']['tanggal']."</td>";
}
if($lapke==0){
$stream.="<td rowspan=1 align=center>Item</td>";
$stream.="  <td colspan=1 align=center>Suhu</td>
            <td colspan=1 align=center>Kelembaban</td>
            <td colspan=1 align=center>Kec Angin</td>    
            <td colspan=1 align=center>Arah angin</td>
            <td colspan=1 align=center>Tekanan Udara</td>
            <td colspan=1 align=center>Hujan</td> 
            <td colspan=1 align=center>Kec Hujan</td>    
            <td colspan=1 align=center>Radiasi Matahari</td>    
            <td colspan=1 align=center>Sinar UV</td>    
            <td colspan=1 align=center>Evapotranspirasi</td> 
		</tr>";
$stream.="<tr class=rowheader>
			<td rowspan=1 align=center>".$_SESSION['lang']['satuan']."</td>
			<td colspan=1 align=center>(&deg;C)</td>
            <td colspan=1 align=center>(%)</td>
            <td colspan=1 align=center>(km/jam)</td>    
            <td colspan=1 align=center>(&deg;)</td>
            <td colspan=1 align=center>(mmHg)</td>
            <td colspan=1 align=center>(mm)</td> 
            <td colspan=1 align=center>(mm/jam)</td>    
            <td colspan=1 align=center>(W/m2)</td>    
            <td colspan=1 align=center>(MEDs)</td>    
            <td colspan=1 align=center>(mm)</td> 
		</tr>";
$stream.="<tr class=rowheader>
			<td rowspan=1 align=center>Range</td>
            <td colspan=1 align=center>-40&deg;C - 65&deg;C</td>
            <td colspan=1 align=center>1 - 100</td>
            <td colspan=1 align=center>0 - 322</td>
            <td colspan=1 align=center>0 - 360</td>
            <td colspan=1 align=center>410 - 820</td>
            <td colspan=1 align=center>0 - 6553</td>
            <td colspan=1 align=center>0 - 2438</td>
            <td colspan=1 align=center>0 - 1800 </td>
            <td colspan=1 align=center>0 - 199</td>
            <td colspan=1 align=center>0 - 1999.9</td>
		</tr>";
}else{
$stream.="  <td colspan=3 align=center>Suhu (&deg;C)</td>
            <td colspan=3 align=center>Kelembaban (%)</td>
            <td colspan=3 align=center>Kec Angin (km/jam)</td>    
            <td colspan=1 align=center>Arah angin (&deg;)</td>
            <td colspan=3 align=center>Tekanan Udara (mmHg)</td>
            <td colspan=3 align=center>Hujan (mm)</td> 
            <td colspan=3 align=center>Kec Hujan (mm/jam)</td>    
            <td colspan=2 align=center>Radiasi Matahari (W/m2)</td>    
            <td colspan=2 align=center>Sinar UV (MEDs)</td>    
            <td colspan=2 align=center>Evapotranspirasi (mm)</td> 
		</tr>";
$stream.="<tr class=rowheader>
            <td colspan=3 align=center>-40&deg;C - 65&deg;C</td>
            <td colspan=3 align=center>1 - 100</td>
            <td colspan=3 align=center>0 - 322</td>
            <td colspan=1 align=center>0 - 360</td>
            <td colspan=3 align=center>410 - 820</td>
            <td colspan=3 align=center>0 - 6553</td>
            <td colspan=3 align=center>0 - 2438</td>
            <td colspan=2 align=center>0 - 1800 </td>
            <td colspan=2 align=center>0 - 199</td>
            <td colspan=2 align=center>0 - 1999.9</td>
		</tr>";
$stream.="<tr class=rowheader>
			<td align=center>Max</td>
            <td align=center>Min</td>
            <td align=center>Rata2</td>
			<td align=center>Max</td>
            <td align=center>Min</td>
            <td align=center>Rata2</td>
			<td align=center>Max</td>
            <td align=center>Min</td>
            <td align=center>Rata2</td>
            <td align=center>Mayoritas</td>
			<td align=center>Max</td>
            <td align=center>Min</td>
            <td align=center>Rata2</td>
			<td align=center>Durasi</td>
            <td align=center>Curah Hujan</td>
			<td align=center>Hari Hujan</td>
			<td align=center>Max</td>
            <td align=center>Min</td>
            <td align=center>Rata2</td>
			<td align=center>Durasi</td>
            <td align=center>Radiasi</td>
			<td align=center>Durasi</td>
            <td align=center>Sinar V</td>
			<td align=center>Durasi</td>
            <td align=center>ET</td>
		</tr>";
}
$stream.="</thead><tbody>";

if($lapke==3){
	$where=" tanggal like '".$tahun."%'";
}else if($lapke==2){
	$where=" tanggal like '".$periode."%'";
}else{
	$where=" tanggal between '".$tgl1."' and '".$tgl2."'";
}
$jam_rain=0;
$count_rain=0;
$jum_solar_rad=0;
$jam_solar_rad=0;
$jam_uv_dose=0;
$jam_et=0;

$iList="SELECT wind_dir,COUNT(wind_dir) as jml 
		FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and ".$where." 
		GROUP BY wind_dir
		ORDER BY jml desc limit 1";
$nList=mysql_query($iList) or die (mysql_error($conn));
$no=0;
while($dList=mysql_fetch_assoc($nList)){
	$mayoritas_wind_dir=$dList['wind_dir'];
}

if($lapke>0){
	$iList="SELECT kodeorg
			,max(temp_out) as max_temp_out,min(temp_out) as min_temp_out,avg(temp_out) as avg_temp_out
			,max(out_hum) as max_out_hum,min(out_hum) as min_out_hum,avg(out_hum) as avg_out_hum
			,max(wind_speed) as max_wind_speed,min(wind_speed) as min_wind_speed,avg(wind_speed) as avg_wind_speed
			,max(bar) as max_bar,min(bar) as min_bar,avg(bar) as avg_bar
			,sum(if(rain=0,0,1))/(count(waktu)/24) as jam_rain,sum(rain) as sum_rain,if(sum(rain)=0,0,1) as count_rain
			,max(rain_rate) as max_rain_rate,min(rain_rate) as min_rain_rate,avg(rain_rate) as avg_rain_rate
			,sum(if(solar_rad>0,1,0)) as jum_solar_rad,sum(if(solar_rad<200,0,if(solar_rad<700,1,0))) as jam_solar_rad
			,sum(solar_rad) as sum_solar_rad,sum(if(uv_dose=0,0,1))/(count(waktu)/24) as jam_uv_dose,sum(uv_dose) as uv_dose
			,sum(if(et=0,0,1))/(count(waktu)/24) as jam_et,sum(et) as et
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and ".$where." 
			GROUP BY kodeorg
			ORDER BY kodeorg";
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	while($dList=mysql_fetch_assoc($nList)){
		$max_temp_out=$dList['max_temp_out']	;$min_temp_out=$dList['min_temp_out']		;$avg_temp_out=$dList['avg_temp_out'];
		$max_out_hum=$dList['max_out_hum']		;$min_out_hum=$dList['min_out_hum']			;$avg_out_hum=$dList['avg_out_hum'];
		$max_wind_speed=$dList['max_wind_speed'];$min_wind_speed=$dList['min_wind_speed']	;$avg_wind_speed=$dList['avg_wind_speed'];
		$max_bar=$dList['max_bar']				;$min_bar=$dList['min_bar']					;$avg_bar=$dList['avg_bar'];
		$jam_rain=$dList['jam_rain']			;$sum_rain=$dList['sum_rain']				;$count_rain=$dList['count_rain'];
		$max_rain_rate=$dList['max_rain_rate']	;$min_rain_rate=$dList['min_rain_rate']		;$avg_rain_rate=$dList['avg_rain_rate'];
		$jum_solar_rad=$dList['jum_solar_rad']	;$jam_solar_rad=$dList['jam_solar_rad']		;$sum_solar_rad=$dList['sum_solar_rad'];
		$jam_uv_dose=$dList['jam_uv_dose']		;$uv_dose=$dList['uv_dose'];
		$jam_et=$dList['jam_et']				;$et=$dList['et'];
	}
}
if($lapke>1){
	$iList="SELECT kodeorg,tanggal
			,max(temp_out) as max_temp_out,min(temp_out) as min_temp_out,avg(temp_out) as avg_temp_out
			,max(out_hum) as max_out_hum,min(out_hum) as min_out_hum,avg(out_hum) as avg_out_hum
			,max(wind_speed) as max_wind_speed,min(wind_speed) as min_wind_speed,avg(wind_speed) as avg_wind_speed
			,(wind_dir) as wind_dir
			,max(bar) as max_bar,min(bar) as min_bar,avg(bar) as avg_bar
			,sum(if(rain=0,0,1))/(count(waktu)/24) as jam_rain,sum(rain) as sum_rain,if(sum(rain)=0,0,1) as count_rain
			,max(rain_rate) as max_rain_rate,min(rain_rate) as min_rain_rate,avg(rain_rate) as avg_rain_rate
			,sum(if(solar_rad>0,1,0)) as jum_solar_rad,sum(if(solar_rad<200,0,if(solar_rad<700,1,0))) as jam_solar_rad
			,sum(solar_rad) as sum_solar_rad,sum(if(uv_dose=0,0,1))/(count(waktu)/24) as jam_uv_dose,sum(uv_dose) as uv_dose
			,sum(if(et=0,0,1))/(count(waktu)/24) as jam_et,sum(et) as et
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and ".$where." 
			GROUP BY kodeorg,tanggal
			ORDER BY kodeorg,tanggal";
	//exit('Warning: '.$iList);
	$jam_rain=array();
	$count_rain=array();
	$jum_solar_rad=array();
	$jam_solar_rad=array();
	$jam_uv_dose=array();
	$jam_et=array();
	$nList=mysql_query($iList) or die (mysql_error($conn));	
	while($dList=mysql_fetch_assoc($nList)){
		/*
		//$max_temp_out=$dList['max_temp_out']	;$min_temp_out=$dList['min_temp_out']		;$avg_temp_out=$dList['avg_temp_out'];
		//$max_out_hum=$dList['max_out_hum']		;$min_out_hum=$dList['min_out_hum']			;$avg_out_hum=$dList['avg_out_hum'];
		//$max_wind_speed=$dList['max_wind_speed'];$min_wind_speed=$dList['min_wind_speed']	;$avg_wind_speed=$dList['avg_wind_speed'];
		//$max_bar=$dList['max_bar']				;$min_bar=$dList['min_bar']					;$avg_bar=$dList['avg_bar'];
		//$max_rain_rate=$dList['max_rain_rate']	;$min_rain_rate=$dList['min_rain_rate']		;$avg_rain_rate=$dList['avg_rain_rate'];
		//$sum_rain=$dList['sum_rain'];
		//$sum_solar_rad=$dList['sum_solar_rad'];
		//$sum_uv_dose=$dList['uv_dose'];
		//$sum_et=$dList['et'];
		$tanggal=explode('-',$dList['tanggal']);
		$thn=$tanggal[0];
		$bln=$tanggal[1];
		$tgl=$tanggal[2];
		$jam_rain[$thn][$bln][$tgl]+=$dList['jam_rain'];
		$count_rain[$thn][$bln][$tgl]+=$dList['count_rain'];
		$jam_solar_rad[$thn][$bln][$tgl]+=$dList['jam_solar_rad'];
		$jam_uv_dose[$thn][$bln][$tgl]+=$dList['jam_uv_dose'];
		$jam_et[$thn][$bln][$tgl]+=$dList['jam_et'];
		*/
		if($lapke==3){
			$tanggal=substr($dList['tanggal'],0,4);
		}elseif($lapke==2){
			$tanggal=substr($dList['tanggal'],0,7);
		}else{
			$tanggal=substr($dList['tanggal'],0,10);
		}
		$jam_rain[$tanggal]+=$dList['jam_rain'];
		$count_rain[$tanggal]+=$dList['count_rain'];
		$jum_solar_rad[$tanggal]+=$dList['jum_solar_rad'];
		$jam_solar_rad[$tanggal]+=$dList['jam_solar_rad'];
		$jam_uv_dose[$tanggal]+=$dList['jam_uv_dose'];
		$jam_et[$tanggal]+=$dList['jam_et'];
	}
}
//exit('Warning: '.$jam_rain['2020-11']);
if($lapke==0){
	$iList="SELECT * 
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
			ORDER BY kodeorg,tanggal,waktu";
}else if($lapke==1){
	$iList="SELECT kodeorg,tanggal
			,max(temp_out) as max_temp_out,min(temp_out) as min_temp_out,avg(temp_out) as avg_temp_out
			,max(out_hum) as max_out_hum,min(out_hum) as min_out_hum,avg(out_hum) as avg_out_hum
			,max(wind_speed) as max_wind_speed,min(wind_speed) as min_wind_speed,avg(wind_speed) as avg_wind_speed
			,(wind_dir) as wind_dir
			,max(bar) as max_bar,min(bar) as min_bar,avg(bar) as avg_bar
			,sum(if(rain=0,0,1))/(count(waktu)/24) as jam_rain,sum(rain) as sum_rain,if(sum(rain)=0,0,1) as count_rain
			,max(rain_rate) as max_rain_rate,min(rain_rate) as min_rain_rate,avg(rain_rate) as avg_rain_rate
			,sum(if(solar_rad>0,1,0)) as jum_solar_rad,sum(if(solar_rad<200,0,if(solar_rad<700,1,0))) as jam_solar_rad
			,sum(solar_rad) as sum_solar_rad,sum(if(uv_dose=0,0,1))/(count(waktu)/24) as jam_uv_dose,sum(uv_dose) as uv_dose
			,sum(if(et=0,0,1))/(count(waktu)/24) as jam_et,sum(et) as et
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
			GROUP BY kodeorg,tanggal
			ORDER BY kodeorg,tanggal";
}else if($lapke==2){
	$iList="SELECT kodeorg,left(tanggal,7) as tanggal
			,max(temp_out) as max_temp_out,min(temp_out) as min_temp_out,avg(temp_out) as avg_temp_out
			,max(out_hum) as max_out_hum,min(out_hum) as min_out_hum,avg(out_hum) as avg_out_hum
			,max(wind_speed) as max_wind_speed,min(wind_speed) as min_wind_speed,avg(wind_speed) as avg_wind_speed
			,(wind_dir) as wind_dir
			,max(bar) as max_bar,min(bar) as min_bar,avg(bar) as avg_bar
			,sum(if(rain=0,0,1))/(count(waktu)/24) as jam_rain,sum(rain) as sum_rain,if(sum(rain)=0,0,1) as count_rain
			,max(rain_rate) as max_rain_rate,min(rain_rate) as min_rain_rate,avg(rain_rate) as avg_rain_rate
			,sum(if(solar_rad>0,1,0)) as jum_solar_rad,sum(if(solar_rad<200,0,if(solar_rad<700,1,0))) as jam_solar_rad
			,sum(solar_rad) as sum_solar_rad,sum(if(uv_dose=0,0,1))/(count(waktu)/24) as jam_uv_dose,sum(uv_dose) as uv_dose
			,sum(if(et=0,0,1))/(count(waktu)/24) as jam_et,sum(et) as et
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal like '".$periode."%'
			GROUP BY kodeorg,left(tanggal,7)
			ORDER BY kodeorg,left(tanggal,7)";
}else if($lapke==3){
	$iList="SELECT kodeorg,left(tanggal,4) as tanggal
			,max(temp_out) as max_temp_out,min(temp_out) as min_temp_out,avg(temp_out) as avg_temp_out
			,max(out_hum) as max_out_hum,min(out_hum) as min_out_hum,avg(out_hum) as avg_out_hum
			,max(wind_speed) as max_wind_speed,min(wind_speed) as min_wind_speed,avg(wind_speed) as avg_wind_speed
			,(wind_dir) as wind_dir
			,max(bar) as max_bar,min(bar) as min_bar,avg(bar) as avg_bar
			,sum(if(rain=0,0,1))/(count(waktu)/24) as jam_rain,sum(rain) as sum_rain,if(sum(rain)=0,0,1) as count_rain
			,max(rain_rate) as max_rain_rate,min(rain_rate) as min_rain_rate,avg(rain_rate) as avg_rain_rate
			,sum(if(solar_rad>0,1,0)) as jum_solar_rad,sum(if(solar_rad<200,0,if(solar_rad<700,1,0))) as jam_solar_rad
			,sum(solar_rad) as sum_solar_rad,sum(if(uv_dose=0,0,1))/(count(waktu)/24) as jam_uv_dose,sum(uv_dose) as uv_dose
			,sum(if(et=0,0,1))/(count(waktu)/24) as jam_et,sum(et) as et
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal like '".$tahun."%'
			GROUP BY kodeorg,left(tanggal,4)
			ORDER BY kodeorg,left(tanggal,4)";
}
$nList=mysql_query($iList) or die (mysql_error($conn));
$no=0;
$waktu=array();
$jml_rain=array();
$durasi_rain=array();
$jml_solar_rad=array();
$durasi_solar_rad=array();
$jml_uv_dose=array();
$durasi_uv_dose=array();
$jml_et=array();
$durasi_et=array();
while($dList=mysql_fetch_assoc($nList)){
	$no+=1;
	$stream.="<tr class=rowcontent>
				<td align='center'>".$no."</td>
				<td align='center'>".$dList['kodeorg']."</td>
				<td align='center'>".$dList['tanggal']."</td>";
	if($lapke==0){
		$stream.="<td align='center'>".substr($dList['waktu'],0,5)."</td>";
	$stream.="	<td align='right'>".number_format($dList['temp_out'],2,".","")."</td>
				<td align='right'>".number_format($dList['out_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['wind_speed'],2,".","")."</td>
				<td align='left'>".$dList['wind_dir']."</td>
				<td align='right'>".number_format($dList['bar'],2,".","")."</td>
				<td align='right'>".number_format($dList['rain'],2,".","")."</td>
				<td align='right'>".number_format($dList['rain_rate'],2,".","")."</td>
				<td align='right'>".number_format($dList['solar_rad'],2,".","")."</td>
				<td align='right'>".number_format($dList['uv_dose'],2,".","")."</td>
				<td align='right'>".number_format($dList['et'],2,".","")."</td>
			</tr>";
			if($dList['waktu']!=''){
				$waktu[$dList['tanggal']]+=1;
			}
			$temp_out[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['temp_out'];
			$out_hum[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['out_hum'];
			$wind_speed[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['wind_speed'];
			$bar[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['bar'];
			$rain[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['rain'];
			if($dList['rain']>0){
				$hari_rain[$dList['tanggal']]=1;
				$jml_rain[$dList['tanggal']]+=1;
			}
			if($jml_rain[$dList['tanggal']]>0){
				$durasi_rain[$dList['tanggal']]=$jml_rain[$dList['tanggal']]/($waktu[$dList['tanggal']]/24);
			}
			$rain_rate[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['rain_rate'];
			$solar_rad[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['solar_rad'];
			if($dList['solar_rad']>0){
				$jml_solar_rad[$dList['tanggal']]+=1;
			}
			if($jml_solar_rad[$dList['tanggal']]>0){
				//$durasi_solar_rad[$dList['tanggal']]=$jml_solar_rad[$dList['tanggal']]/($waktu[$dList['tanggal']]/24);
			}
			if($dList['solar_rad']<200){
				$durasi_solar_rad[$dList['tanggal']]+=0;
			}elseif($dList['solar_rad']<700){
				$durasi_solar_rad[$dList['tanggal']]+=1;
			}else{
				$durasi_solar_rad[$dList['tanggal']]+=0;
			}
			$uv_dose[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['uv_dose'];
			if($dList['uv_dose']>0){
				$jml_uv_dose[$dList['tanggal']]+=1;
			}
			if($jml_uv_dose[$dList['tanggal']]>0){
				$durasi_uv_dose[$dList['tanggal']]=$jml_uv_dose[$dList['tanggal']]/($waktu[$dList['tanggal']]/24);
			}
			$et[$dList['kodeorg'].$dList['tanggal'].$dList['waktu']]=$dList['et'];
			if($dList['et']>0){
				$jml_et[$dList['tanggal']]+=1;
			}
			if($jml_et[$dList['tanggal']]>0){
				$durasi_et[$dList['tanggal']]=$jml_et[$dList['tanggal']]/($waktu[$dList['tanggal']]/24);
			}
	}else{
	$stream.="	<td align='right'>".number_format($dList['max_temp_out'],2,".","")."</td>
				<td align='right'>".number_format($dList['min_temp_out'],2,".","")."</td>
				<td align='right'>".number_format($dList['avg_temp_out'],2,".","")."</td>
				<td align='right'>".number_format($dList['max_out_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['min_out_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['avg_out_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['max_wind_speed'],2,".","")."</td>
				<td align='right'>".number_format($dList['min_wind_speed'],2,".","")."</td>
				<td align='right'>".number_format($dList['avg_wind_speed'],2,".","")."</td>
				<td align='left'>".$dList['wind_dir']."</td>
				<td align='right'>".number_format($dList['max_bar'],2,".","")."</td>
				<td align='right'>".number_format($dList['min_bar'],2,".","")."</td>
				<td align='right'>".number_format($dList['avg_bar'],2,".","")."</td>";
	if($lapke==1){
		$stream.="<td align='right'>".number_format($dList['jam_rain'],2,".","")."</td>
				<td align='right'>".number_format($dList['sum_rain'],2,".","")."</td>
				<td align='right'>".number_format($dList['count_rain'],0,".","")."</td>";
	}else{
		$stream.="<td align='right'>".number_format($jam_rain[$dList['tanggal']],2,".","")."</td>
				<td align='right'>".number_format($dList['sum_rain'],2,".","")."</td>
				<td align='right'>".number_format($count_rain[$dList['tanggal']],0,".","")."</td>";
	}
	$stream.="	<td align='right'>".number_format($dList['max_rain_rate'],2,".","")."</td>
				<td align='right'>".number_format($dList['min_rain_rate'],2,".","")."</td>
				<td align='right'>".number_format($dList['avg_rain_rate'],2,".","")."</td>";
	if($lapke==1){
		$stream.="<td align='right'>".number_format($dList['jam_solar_rad'],2,".","")."</td>
				<td align='right'>".number_format($dList['sum_solar_rad']/$dList['jum_solar_rad'],2,".","")."</td>
				<td align='right'>".number_format($dList['jam_uv_dose'],2,".","")."</td>
				<td align='right'>".number_format($dList['uv_dose'],2,".","")."</td>
				<td align='right'>".number_format($dList['jam_et'],2,".","")."</td>";
	}else{
		$stream.="<td align='right'>".number_format($jam_solar_rad[$dList['tanggal']],2,".","")."</td>
				<td align='right'>".number_format($dList['sum_solar_rad']/$jum_solar_rad[$dList['tanggal']],2,".","")."</td>
				<td align='right'>".number_format($jam_uv_dose[$dList['tanggal']],2,".","")."</td>
				<td align='right'>".number_format($dList['uv_dose'],2,".","")."</td>
				<td align='right'>".number_format($jam_et[$dList['tanggal']],2,".","")."</td>";
	}
	$stream.="	<td align='right'>".number_format($dList['et'],2,".","")."</td>
			</tr>";
			if($lapke==1){
				$ttjam_rain += $dList['jam_rain'];
				$ttcount_rain += $dList['count_rain'];
				$ttjum_solar_rad += $dList['jum_solar_rad'];
				$ttjam_solar_rad += $dList['jam_solar_rad'];
				$ttjam_uv_dose += $dList['jam_uv_dose'];
				$ttjam_et += $dList['jam_et'];
			}else{
				$ttjam_rain += $jam_rain[$dList['tanggal']];
				$ttcount_rain += $count_rain[$dList['tanggal']];
				$ttjum_solar_rad += $jum_solar_rad[$dList['tanggal']];
				$ttjam_solar_rad += $jam_solar_rad[$dList['tanggal']];
				$ttjam_uv_dose += $jam_uv_dose[$dList['tanggal']];
				$ttjam_et += $jam_et[$dList['tanggal']];
			}
	}
}
if($lapke==0){
	$stream.="<tr bgcolor='#DEFEDE'>
				<td></td>
				<td></td>
				<td></td>
				<td></td>";
	$stream.="<td align='right'>Max: ".number_format(max($temp_out),2,".","")."</td>";
	$stream.="<td align='right'>Max: ".number_format(max($out_hum),2,".","")."</td>";
	$stream.="<td align='right'>Max: ".number_format(max($wind_speed),2,".","")."</td>";
	$stream.="<td align='left'>Mayoritas:</td>";
	$stream.="<td align='right'>Max: ".number_format(max($bar),2,".","")."</td>";
	$stream.="<td align='right'>Durasi: ".number_format(array_sum($durasi_rain),2,".","")."</td>";
	$stream.="<td align='right'>Max: ".number_format(max($rain_rate),2,".","")."</td>";
	$stream.="<td align='right'>Durasi: ".number_format(array_sum($jml_solar_rad),2,".","")."</td>";
	$stream.="<td align='right'>Durasi: ".number_format(array_sum($durasi_uv_dose),2,".","")."</td>";
	$stream.="<td align='right'>Durasi: ".number_format(array_sum($durasi_et),2,".","")."</td>";
	$stream.="</tr>";
	$stream.="<tr bgcolor='#DEFEDE'>
				<td></td>
				<td></td>
				<td></td>
				<td></td>";
	$stream.="<td align='right'>Min: ".number_format(min($temp_out),2,".","")."</td>";
	$stream.="<td align='right'>Min: ".number_format(min($out_hum),2,".","")."</td>";
	$stream.="<td align='right'>Min: ".number_format(min($wind_speed),2,".","")."</td>";
	$stream.="<td align='left'>".$mayoritas_wind_dir."</td>";
	$stream.="<td align='right'>Min: ".number_format(min($bar),2,".","")."</td>";
	$stream.="<td align='right'>Curah Hujan: ".number_format(array_sum($rain),2,".","")."</td>";
	$stream.="<td align='right'>Min: ".number_format(min($rain_rate),2,".","")."</td>";
	$stream.="<td align='right'>Durasi Efektif: ".number_format(array_sum($durasi_solar_rad),2,".","")."</td>";
	$stream.="<td align='right'>Sinar UV: ".number_format(array_sum($uv_dose),2,".","")."</td>";
	$stream.="<td align='right'>Evapotranspirasi: ".number_format(array_sum($et),2,".","")."</td>";
	$stream.="</tr>";
	$stream.="<tr bgcolor='#DEFEDE'>
				<td></td>
				<td></td>
				<td></td>
				<td></td>";
	$stream.="<td align='right'>Rata2: ".number_format((array_sum($temp_out)/$no),2,".","")."</td>";
	$stream.="<td align='right'>Rata2: ".number_format((array_sum($out_hum)/$no),2,".","")."</td>";
	$stream.="<td align='right'>Rata2: ".number_format((array_sum($wind_speed)/$no),2,".","")."</td>";
	$stream.="<td></td>";
	$stream.="<td align='right'>Rata2: ".number_format((array_sum($bar)/$no),2,".","")."</td>";
	$stream.="<td align='right'>Hari Hujan: ".number_format(array_sum($hari_rain),0,".","")."</td>";
	$stream.="<td align='right'>Rata2: ".number_format((array_sum($rain_rate)/$no),2,".","")."</td>";
	$stream.="<td align='right'>Radiasi: ".number_format(array_sum($solar_rad)/array_sum($jml_solar_rad),2,".","")."</td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="</tr>";
}else{
	$stream.="<tr bgcolor='#DEFEDE'>
				<td colspan=3 align=center>Jumlah</td>
				<td align='right'>".number_format($max_temp_out,2,".","")."</td>
				<td align='right'>".number_format($min_temp_out,2,".","")."</td>
				<td align='right'>".number_format($avg_temp_out,2,".","")."</td>
				<td align='right'>".number_format($max_out_hum,2,".","")."</td>
				<td align='right'>".number_format($min_out_hum,2,".","")."</td>
				<td align='right'>".number_format($avg_out_hum,2,".","")."</td>
				<td align='right'>".number_format($max_wind_speed,2,".","")."</td>
				<td align='right'>".number_format($min_wind_speed,2,".","")."</td>
				<td align='right'>".number_format($avg_wind_speed,2,".","")."</td>
				<td align='left'>".$mayoritas_wind_dir."</td>
				<td align='right'>".number_format($max_bar,2,".","")."</td>
				<td align='right'>".number_format($min_bar,2,".","")."</td>
				<td align='right'>".number_format($avg_bar,2,".","")."</td>
				<td align='right'>".number_format($ttjam_rain,2,".","")."</td>
				<td align='right'>".number_format($sum_rain,2,".","")."</td>
				<td align='right'>".number_format($ttcount_rain,0,".","")."</td>
				<td align='right'>".number_format($max_rain_rate,2,".","")."</td>
				<td align='right'>".number_format($min_rain_rate,2,".","")."</td>
				<td align='right'>".number_format($avg_rain_rate,2,".","")."</td>
				<td align='right'>".number_format($ttjam_solar_rad,2,".","")."</td>
				<td align='right'>".number_format($sum_solar_rad/$ttjum_solar_rad,2,".","")."</td>
				<td align='right'>".number_format($ttjam_uv_dose,2,".","")."</td>
				<td align='right'>".number_format($uv_dose,2,".","")."</td>
				<td align='right'>".number_format($ttjam_et,2,".","")."</td>
				<td align='right'>".number_format($et,2,".","")."</td>
			</tr>";
}
$stream.="</tbody></table>";

switch($proses){
	case 'preview':
		echo $stream;
    break;

	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_AWS_".$tglSkrg;
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
	break;

	default:
	break;
}

?>
