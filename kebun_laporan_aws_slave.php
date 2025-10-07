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
$nmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

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
	$stream.="<td rowspan=3 align=center>".$_SESSION['lang']['waktu']."</td>";
}
$stream.="  <td align=center>Temp</td>
            <td align=center>Hi</td>
            <td align=center>Low</td>    
            <td align=center>Out</td>
            <td align=center>Dew</td>
            <td align=center>Wind</td> 
            <td align=center>Wind</td>    
            <td align=center>Wind</td>    
            <td align=center>Hi</td>    
            <td align=center>Hi</td> 
            <td align=center>Wind</td>    
            <td align=center>Heat</td> 
            <td align=center>THW</td> 
            <td align=center>THSW</td> 
            <td rowspan=2 align=center>Bar</td> 
            <td rowspan=2 align=center>Rain</td> 
            <td align=center>Rain</td> 
            <td align=center>Solar</td>
            <td align=center>Solar</td>
            <td align=center>Hi Solar</td>";
if($lapke==0){
  $stream.="<td align=center>Lama</td>";
}
$stream.="  <td align=center>UV</td>
            <td align=center>UV</td>
            <td align=center>Hi</td>
            <td align=center>Heat</td>
            <td align=center>Cool</td>
            <td align=center>In</td>
            <td align=center>In</td>
            <td align=center>In</td>
            <td align=center>In</td>
            <td align=center>In</td>
            <td align=center>In Air</td>
            <td rowspan=2 align=center>ET</td> 
            <td align=center>Wind</td> 
            <td align=center>Wind</td> 
            <td align=center>ISS</td> 
            <td align=center>Arc.</td></tr>";
$stream.="<tr class=rowheader>
            <td align=center>Out</td>
            <td align=center>Temp</td>
            <td align=center>Temp</td>
            <td align=center>Hum</td>
            <td align=center>Pt.</td>
            <td align=center>Speed</td>
            <td align=center>Dir</td>
            <td align=center>Run</td>
            <td align=center>Speed</td>
            <td align=center>Dir</td>
            <td align=center>Chill</td>
            <td align=center>Index</td>
            <td align=center>Index</td>
            <td align=center>Index</td>
            <td align=center>Rate</td>
            <td align=center>Rad.</td>
            <td align=center>Energy</td>
            <td align=center>Rad.</td>";
if($lapke==0){
  $stream.="<td align=center>Penyinaran</td>";
}
$stream.="  <td align=center>Index</td>
            <td align=center>Dose</td>
            <td align=center>UV</td>
            <td align=center>D-D</td>
            <td align=center>D-D</td>
            <td align=center>Temp</td>
            <td align=center>Hum</td>
            <td align=center>Dew</td>
            <td align=center>Heat</td>
            <td align=center>EMC</td>
            <td align=center>Density</td>
            <td align=center>Samp</td>
            <td align=center>Tx</td>
            <td align=center>Recept</td>
            <td align=center>Int.</td></tr>";
$stream.="<tr class=rowheader>
			<td align=center>(&deg;C)</td>
            <td align=center>(&deg;C)</td>
            <td align=center>(&deg;C)</td>    
            <td align=center>(%)</td>
            <td align=center>(&deg;C)</td>
            <td align=center>(km/jam)</td> 
            <td align=center>(&deg;)</td>    
            <td align=center>(km/jam)</td>    
            <td align=center>(km/jam)</td>    
            <td align=center>(&deg;)</td> 
            <td align=center>(&deg;C)</td>    
            <td align=center>(&deg;C)</td> 
            <td align=center>(&deg;C)</td> 
            <td align=center>(&deg;C)</td> 
            <td align=center>(mmHg)</td> 
            <td align=center>(mm)</td> 
            <td align=center>(mm/jam)</td> 
            <td align=center>(W/m2)</td>
            <td align=center>(W/m2)</td>
            <td align=center>(W/m2)</td>";
if($lapke==0){
  $stream.="<td align=center>(Jam)</td>";
}
$stream.="  <td align=center>(MEDs)</td>
            <td align=center>(MEDs)</td>
            <td align=center>(MEDs)</td>
            <td align=center>&nbsp;</td>
            <td align=center>&nbsp;</td>
            <td align=center>(&deg;C)</td>
            <td align=center>(%)</td>
            <td align=center>(&deg;C)</td>
            <td align=center>&nbsp;</td>
            <td align=center>&nbsp;</td>
            <td align=center>(kg/m3)</td>
            <td align=center>"."(mm)"."</td> 
            <td align=center>&nbsp;</td> 
            <td align=center>&nbsp;</td> 
            <td align=center>&nbsp;</td> 
            <td align=center>&nbsp;</td></tr>";
$stream.="</tr></thead><tbody>";

$sdata="SELECT kodeorg,tanggal,sum(temp_out) as temp_out,sum(hi_temp) as hi_temp,sum(low_temp) as low_temp
	,sum(out_hum) as out_hum,sum(dew_pt) as dew_pt,sum(wind_speed) as wind_speed,sum(wind_dir) as wind_dir
	,sum(wind_run) as wind_run,sum(hi_speed) as hi_speed,sum(hi_dir) as hi_dir,sum(wind_chill) as wind_chill
	,sum(heat_index) as heat_index,sum(thw_index) as thw_index,sum(thsw_index) as thsw_index,sum(bar) as bar
	,sum(rain) as rain,sum(rain_rate) as rain_rate,sum(solar_rad) as solar_rad,sum(solar_energy) as solar_energy
	,sum(hi_solar_rad) as hi_solar_rad,sum(uv_index) as uv_index,sum(uv_dose) as uv_dose,sum(hi_uv) as hi_uv
	,sum(heat_dd) as heat_dd,sum(cool_dd) as cool_dd,sum(in_temp) as in_temp,sum(in_hum) as in_hum
	,sum(in_dew) as in_dew,sum(in_heat) as in_heat,sum(in_emc) as in_emc,sum(in_air_density) as in_air_density
	,sum(et) as et,sum(wind_samp) as wind_samp,sum(wind_tx) as wind_tx,sum(iss_recept) as iss_recept
	,sum(arc_int) as arc_int
	FROM ".$dbname.".kebun_aws
	GROUP BY kodeorg,tanggal";

if($lapke==3){
	$where=" tanggal like '".$tahun."%'";
}else if($lapke==2){
	$where=" tanggal like '".$periode."%'";
}else{
	$where=" tanggal between '".$tgl1."' and '".$tgl2."'";
}

$iList="SELECT a.kodeorg,a.tanggal
	,sum(temp_out) as tt_temp_out,sum(hi_temp) as tt_hi_temp,sum(low_temp) as tt_low_temp
	,sum(out_hum) as tt_out_hum,sum(dew_pt) as tt_dew_pt,sum(wind_speed) as tt_wind_speed,sum(wind_dir) as tt_wind_dir
	,sum(wind_run) as tt_wind_run,sum(hi_speed) as tt_hi_speed,sum(hi_dir) as tt_hi_dir,sum(wind_chill) as tt_wind_chill
	,sum(heat_index) as tt_heat_index,sum(thw_index) as tt_thw_index,sum(thsw_index) as tt_thsw_index,sum(bar) as tt_bar
	,sum(rain) as tt_rain,sum(rain_rate) as tt_rain_rate,sum(solar_rad) as tt_solar_rad,sum(solar_energy) as tt_solar_energy
	,sum(hi_solar_rad) as tt_hi_solar_rad,sum(uv_index) as tt_uv_index,sum(uv_dose) as tt_uv_dose,sum(hi_uv) as tt_hi_uv
	,sum(heat_dd) as tt_heat_dd,sum(cool_dd) as tt_cool_dd,sum(in_temp) as tt_in_temp,sum(in_hum) as tt_in_hum
	,sum(in_dew) as tt_in_dew,sum(in_heat) as tt_in_heat,sum(in_emc) as tt_in_emc,sum(in_air_density) as tt_in_air_density
	,sum(et) as tt_et,sum(wind_samp) as tt_wind_samp,sum(wind_tx) as tt_wind_tx,sum(iss_recept) as tt_iss_recept
	,sum(arc_int) as tt_arc_int
	,sum(if(temp_out=0,0,1)) as hh_temp_out
	,sum(if(hi_temp=0,0,1)) as hh_hi_temp
	,sum(if(low_temp=0,0,1)) as hh_low_temp
	,sum(if(out_hum=0,0,1)) as hh_out_hum
	,sum(if(dew_pt=0,0,1)) as hh_dew_pt
	,sum(if(wind_speed=0,0,1)) as hh_wind_speed
	,sum(if(wind_dir=0,0,1)) as hh_wind_dir
	,sum(if(wind_run=0,0,1)) as hh_wind_run
	,sum(if(hi_speed=0,0,1)) as hh_hi_speed
	,sum(if(hi_dir=0,0,1)) as hh_hi_dir
	,sum(if(wind_chill=0,0,1)) as hh_wind_chill
	,sum(if(heat_index=0,0,1)) as hh_heat_index
	,sum(if(thw_index=0,0,1)) as hh_thw_index
	,sum(if(thsw_index=0,0,1)) as hh_thsw_index
	,sum(if(bar=0,0,1)) as hh_bar
	,sum(if(rain=0,0,1)) as hh_rain
	,sum(if(rain_rate=0,0,1)) as hh_rain_rate
	,sum(if(solar_rad=0,0,1)) as hh_solar_rad
	,sum(if(solar_energy=0,0,1)) as hh_solar_energy
	,sum(if(hi_solar_rad=0,0,1)) as hh_hi_solar_rad
	,sum(if(uv_index=0,0,1)) as hh_uv_index
	,sum(if(uv_dose=0,0,1)) as hh_uv_dose
	,sum(if(hi_uv=0,0,1)) as hh_hi_uv
	,sum(if(heat_dd=0,0,1)) as hh_heat_dd
	,sum(if(cool_dd=0,0,1)) as hh_cool_dd
	,sum(if(in_temp=0,0,1)) as hh_in_temp
	,sum(if(in_hum=0,0,1)) as hh_in_hum
	,sum(if(in_dew=0,0,1)) as hh_in_dew
	,sum(if(in_heat=0,0,1)) as hh_in_heat
	,sum(if(in_emc=0,0,1)) as hh_in_emc
	,sum(if(in_air_density=0,0,1)) as hh_in_air_density
	,sum(if(et=0,0,1)) as hh_et
	,sum(if(wind_samp=0,0,1)) as hh_wind_samp
	,sum(if(wind_tx=0,0,1)) as hh_wind_tx
	,sum(if(iss_recept=0,0,1)) as hh_iss_recept
	,sum(if(arc_int=0,0,1)) as hh_arc_int
	FROM (".$sdata.") a where kodeorg='".$kodeorg."' and ".$where;
$nList=mysql_query($iList) or die (mysql_error($conn));	
while($dList=mysql_fetch_row($nList)){
	for($x=2;$x<=37;$x++){
		$ttaws[$x-1]=$dList[$x];
	}
	for($x=38;$x<=73;$x++){
		$hhaws[$x-37]=$dList[$x];
	}
}

if($lapke==0){
	$iList="SELECT * FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal between '".$tgl1."' and '".$tgl2."'";
}else if($lapke==1){
	$iList="SELECT kodeorg,tanggal,sum(temp_out) as temp_out,sum(hi_temp) as hi_temp,sum(low_temp) as low_temp,sum(out_hum) as out_hum,sum(dew_pt) as dew_pt
			,sum(wind_speed) as wind_speed,sum(wind_dir) as wind_dir,sum(wind_run) as wind_run,sum(hi_speed) as hi_speed,sum(hi_dir) as hi_dir
			,sum(wind_chill) as wind_chill,sum(heat_index) as heat_index,sum(thw_index) as thw_index,sum(thsw_index) as thsw_index,sum(bar) as bar
			,sum(rain) as rain,sum(rain_rate) as rain_rate,sum(solar_rad) as solar_rad,sum(solar_energy) as solar_energy,sum(hi_solar_rad) as hi_solar_rad
			,sum(uv_index) as uv_index,sum(uv_dose) as uv_dose,sum(hi_uv) as hi_uv,sum(heat_dd) as heat_dd,sum(cool_dd) as cool_dd,sum(in_temp) as in_temp
			,sum(in_hum) as in_hum,sum(in_dew) as in_dew,sum(in_heat) as in_heat,sum(in_emc) as in_emc,sum(in_air_density) as in_air_density,sum(et) as et
			,sum(wind_samp) as wind_samp,sum(wind_tx) as wind_tx,sum(iss_recept) as iss_recept,sum(arc_int) as arc_int
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
			GROUP BY kodeorg,tanggal
			ORDER BY kodeorg,tanggal";
}else if($lapke==2){
	$iList="SELECT kodeorg,left(tanggal,7) as tanggal,sum(temp_out) as temp_out,sum(hi_temp) as hi_temp,sum(low_temp) as low_temp,sum(out_hum) as out_hum
			,sum(dew_pt) as dew_pt,sum(wind_speed) as wind_speed,sum(wind_dir) as wind_dir,sum(wind_run) as wind_run,sum(hi_speed) as hi_speed,sum(hi_dir) as hi_dir
			,sum(wind_chill) as wind_chill,sum(heat_index) as heat_index,sum(thw_index) as thw_index,sum(thsw_index) as thsw_index,sum(bar) as bar
			,sum(rain) as rain,sum(rain_rate) as rain_rate,sum(solar_rad) as solar_rad,sum(solar_energy) as solar_energy,sum(hi_solar_rad) as hi_solar_rad
			,sum(uv_index) as uv_index,sum(uv_dose) as uv_dose,sum(hi_uv) as hi_uv,sum(heat_dd) as heat_dd,sum(cool_dd) as cool_dd,sum(in_temp) as in_temp
			,sum(in_hum) as in_hum,sum(in_dew) as in_dew,sum(in_heat) as in_heat,sum(in_emc) as in_emc,sum(in_air_density) as in_air_density,sum(et) as et
			,sum(wind_samp) as wind_samp,sum(wind_tx) as wind_tx,sum(iss_recept) as iss_recept,sum(arc_int) as arc_int
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal like '".$periode."%'
			GROUP BY kodeorg,left(tanggal,7)
			ORDER BY kodeorg,left(tanggal,7)";
}else if($lapke==3){
	$iList="SELECT kodeorg,left(tanggal,4) as tanggal,sum(temp_out) as temp_out,sum(hi_temp) as hi_temp,sum(low_temp) as low_temp,sum(out_hum) as out_hum
			,sum(dew_pt) as dew_pt,sum(wind_speed) as wind_speed,sum(wind_dir) as wind_dir,sum(wind_run) as wind_run,sum(hi_speed) as hi_speed,sum(hi_dir) as hi_dir
			,sum(wind_chill) as wind_chill,sum(heat_index) as heat_index,sum(thw_index) as thw_index,sum(thsw_index) as thsw_index,sum(bar) as bar
			,sum(rain) as rain,sum(rain_rate) as rain_rate,sum(solar_rad) as solar_rad,sum(solar_energy) as solar_energy,sum(hi_solar_rad) as hi_solar_rad
			,sum(uv_index) as uv_index,sum(uv_dose) as uv_dose,sum(hi_uv) as hi_uv,sum(heat_dd) as heat_dd,sum(cool_dd) as cool_dd,sum(in_temp) as in_temp
			,sum(in_hum) as in_hum,sum(in_dew) as in_dew,sum(in_heat) as in_heat,sum(in_emc) as in_emc,sum(in_air_density) as in_air_density,sum(et) as et
			,sum(wind_samp) as wind_samp,sum(wind_tx) as wind_tx,sum(iss_recept) as iss_recept,sum(arc_int) as arc_int
			FROM ".$dbname.".kebun_aws where kodeorg='".$kodeorg."' and tanggal like '".$tahun."%'
			GROUP BY kodeorg,left(tanggal,4)
			ORDER BY kodeorg,left(tanggal,4)";
}
$nList=mysql_query($iList) or die (mysql_error($conn));	
$no=0;
while($dList=mysql_fetch_assoc($nList)){
	$no+=1;
	$stream.="<tr class=rowcontent>
				<td align='center'>".$no."</td>
				<td align='center'>".$dList['kodeorg']."</td>
				<td align='center'>".$dList['tanggal']."</td>";
	if($lapke==0){
		$stream.="<td align='center'>".substr($dList['waktu'],0,5)."</td>";
	}
	$stream.="	<td align='right'>".number_format($dList['temp_out'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_temp'],2,".","")."</td>
				<td align='right'>".number_format($dList['low_temp'],2,".","")."</td>
				<td align='right'>".number_format($dList['out_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['dew_pt'],2,".","")."</td>
				<td align='right'>".number_format($dList['wind_speed'],2,".","")."</td>
				<td align='left'>".$dList['wind_dir']."</td>
				<td align='right'>".number_format($dList['wind_run'],2,".","")."</td>
				<td align='right'>".number_format($dList['hi_speed'],2,".","")."</td>
				<td align='left'>".$dList['hi_dir']."</td>
				<td align='right'>".number_format($dList['wind_chill'],2,".","")."</td>
				<td align='right'>".number_format($dList['heat_index'],2,".","")."</td>
				<td align='right'>".number_format($dList['thw_index'],2,".","")."</td>
				<td align='right'>".number_format($dList['thsw_index'],2,".","")."</td>
				<td align='right'>".number_format($dList['bar'],2,".","")."</td>
				<td align='right'>".number_format($dList['rain'],2,".","")."</td>
				<td align='right'>".number_format($dList['rain_rate'],2,".","")."</td>
				<td align='right'>".number_format($dList['solar_rad'],2,".","")."</td>
				<td align='right'>".number_format($dList['solar_energy'],2,".","")."</td>
				<td align='right'>".number_format($dList['hi_solar_rad'],2,".","")."</td>";
	if($lapke==0){
		if($dList['solar_rad']<200){
			$lamapenyinaran=0;
		}elseif($dList['solar_rad']<700){
			$lamapenyinaran=1;
		}else{
			$lamapenyinaran=0;
		}
		$stream.="<td align='right'>".$lamapenyinaran."</td>";
	}
	$stream.="	<td align='right'>".number_format($dList['uv_index'],2,".","")."</td>
				<td align='right'>".number_format($dList['uv_dose'],2,".","")."</td>
				<td align='right'>".number_format($dList['hi_uv'],2,".","")."</td>
				<td align='right'>".number_format($dList['heat_dd'],2,".","")."</td>
				<td align='right'>".number_format($dList['cool_dd'],4,".","")."</td>
				<td align='right'>".number_format($dList['in_temp'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_hum'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_dew'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_heat'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_emc'],2,".","")."</td>
				<td align='right'>".number_format($dList['in_air_density'],4,".","")."</td>
				<td align='right'>".number_format($dList['et'],2,".","")."</td>
				<td align='right'>".number_format($dList['wind_samp'],2,".","")."</td>
				<td align='right'>".number_format($dList['wind_tx'],2,".","")."</td>
				<td align='right'>".number_format($dList['iss_recept'],2,".","")."</td>
				<td align='right'>".number_format($dList['arc_int'],2,".","")."</td>
			</tr>";
}
if($lapke>0){
	$stream.="<tr class=rowcontent><td colspan=3 align='center'>Total Satuan</td>";
	for($x=1;$x<=36;$x++){
		if($x==7 or $x==10){
			$stream.="<td></td>";
		}else if($x==25 or $x==31){
			$stream.="<td align='right'>".number_format($ttaws[$x],4,".","")."</td>";
		}else{
			$stream.="<td align='right'>".number_format($ttaws[$x],2,".","")."</td>";
		}
	}
	$stream.="</tr>";
	$stream.="<tr class=rowcontent><td colspan=3 align='center'>Total Hari</td>";
	for($x=1;$x<=36;$x++){
		if($x==7 or $x==10){
			$stream.="<td></td>";
		}else{
			$stream.="<td align='center'>".number_format($hhaws[$x],0,".","")."</td>";
		}
	}
	$stream.="</tr>";
	$stream.="<tr class=rowcontent><td colspan=3 align='center'>Rata-rata</td>";
	for($x=1;$x<=36;$x++){
		if($x==7 or $x==10){
			$stream.="<td></td>";
		}else if($x==25 or $x==31){
			$stream.="<td align='right'>".number_format(($hhaws[$x]==0 ? 0 : $ttaws[$x]/$hhaws[$x]),4,".","")."</td>";
		}else{
			$stream.="<td align='right'>".number_format(($hhaws[$x]==0 ? 0 : $ttaws[$x]/$hhaws[$x]),2,".","")."</td>";
		}
	}
	$stream.="</tr>";
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
