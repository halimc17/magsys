<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX();
	if (isset($_POST['submit'])){
		//Script Upload File..
		if(!strstr(strtoupper($_FILES['filename']['name']),'.CSV')){
			exit('Warning : File bukan CSV...! '.$_FILES['filename']['name']);
		}
		if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
			echo "<b>"."File ".$_FILES['filename']['name']." Berhasil di Upload...!"."</b><br>";
			//echo "<h2>Menampilkan Hasil Upload:</h2>";
			//readfile($_FILES['filename']['tmp_name']);
		}
		$stsupload=0;
		//Import uploaded file to Database, Letakan dibawah sini..
		$handle = fopen($_FILES['filename']['tmp_name'], "r"); 
		//Membuka file dan membacanya
		$no=0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$no+=1;
			if($no==1){
				if(count($data)!=38){
					echo '<br><h2>Import Gagal, Jumlah kolom tidak sama...!</h2>';
					exit;
				}
				continue;
			}
			$tgl[$data[0].$data[1]]=substr($data[0],6,4)."-".substr($data[0],3,2)."-".substr($data[0],0,2).' '.$data[1];
			$tanggal=substr($data[0],6,4)."-".substr($data[0],3,2)."-".substr($data[0],0,2);
			//$import="INSERT into ".$dbname.".kebun_aws (kodeorg,tanggal,waktu,user_update,last_update) 
			//		values('".$_SESSION['empl']['lokasitugas']."','$tanggal','$data[1]',".$_SESSION['standard']['userid'].",now())"; 
			$str="select * from ".$dbname.".kebun_aws where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$tanggal."' 
				  and waktu='".$data[1]."'";
			$qstr=mysql_query($str) or die(mysql_error($conn));
			$rnum=mysql_num_rows($qstr);
			if($rnum==0){
				$stsupload=1;
				$import="INSERT into ".$dbname.".kebun_aws values('".$_SESSION['empl']['lokasitugas']."','".$tanggal."'";
				for ($i = 1; $i <= 37; $i++){
					$import.=",'".$data[$i]."'";
				}
				$import.=",'".$_SESSION['standard']['userid']."',now())";
				//echo "data array sesuaikan dengan jumlah kolom pada CSV anda mulai dari '0' bukan '1'";
				//echo $import;
				//echo "<br>";
				mysql_query($import) or die(mysql_error($conn)); //Melakukan Import
			}else{
				while($rstr=mysql_fetch_assoc($qstr)){
					echo "<br>Sudah ada data ".$rstr['kodeorg'].' '.$rstr['tanggal'].' '.$rstr['waktu'];
					$import="UPDATE ".$dbname.".kebun_aws set 
							temp_out='".$data[2]."'
							,hi_temp='".$data[3]."'
							,low_temp='".$data[4]."'
							,out_hum='".$data[5]."'
							,dew_pt='".$data[6]."'
							,wind_speed='".$data[7]."'
							,wind_dir='".$data[8]."'
							,wind_run='".$data[9]."'
							,hi_speed='".$data[10]."'
							,hi_dir='".$data[11]."'
							,wind_chill='".$data[12]."'
							,heat_index='".$data[13]."'
							,thw_index='".$data[14]."'
							,thsw_index='".$data[15]."'
							,bar='".$data[16]."'
							,rain='".$data[17]."'
							,rain_rate='".$data[18]."'
							,solar_rad='".$data[19]."'
							,solar_energy='".$data[20]."'
							,hi_solar_rad='".$data[21]."'
							,uv_index='".$data[22]."'
							,uv_dose='".$data[23]."'
							,hi_uv='".$data[24]."'
							,heat_dd='".$data[25]."'
							,cool_dd='".$data[26]."'
							,in_temp='".$data[27]."'
							,in_hum='".$data[28]."'
							,in_dew='".$data[29]."'
							,in_heat='".$data[30]."'
							,in_emc='".$data[31]."'
							,in_air_density='".$data[32]."'
							,et='".$data[33]."'
							,wind_samp='".$data[34]."'
							,wind_tx='".$data[35]."'
							,iss_recept='".$data[36]."'
							,arc_int='".$data[37]."'
							,user_update='".$_SESSION['standard']['userid']."'
							,last_update=now() 
							where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$tanggal."' and waktu='".$data[1]."'";
					mysql_query($import) or die(mysql_error($conn)); //Melakukan Import
					if($stsupload!=1){
						$stsupload=2;
					}
				}
			}
		}
		fclose($handle); //Menutup CSV file
		if($stsupload==1){
			echo "<h2>"."<strong>Import data sukses...!</strong>"."</h2>";
		}elseif($stsupload==2){
			echo "<h2>"."<strong>Import data sukses, Duplicate data overwrite...!</strong>"."</h2>";
		}else{
			echo "<h2>"."<strong>Tidak ada data...!</strong>"."</h2>";
		}
		if($stsupload>0){
			echo "<br>".min($tgl).' s/d '.max($tgl);
			// --- Start Calculate curahhujan ke tanggal pasi sore malam
			/*
			$srain="select kodeorg,if(waktu<'06:00',tanggal-interval 1 day,tanggal) as tglhitung
					,SUM(if(waktu>='06:00' and waktu<'12:00',rain,0)) as mm_pagi 
					,SUM(if(waktu>='12:00' and waktu<'18:00',rain,0)) as mm_sore 
					,SUM(if(waktu>='18:00' or waktu<'06:00',rain,0)) as mm_malam 
					,SUM(rain) as mmhari
					from ".$dbname.".kebun_aws 
					where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
						  and tanggal BETWEEN '".substr(min($tgl),0,10)." 06:00' and '".substr(max($tgl),0,10)." 05:59'+interval 1 day
					GROUP BY kodeorg,tglhitung
					ORDER BY kodeorg,tglhitung";
			*/
			$srain="select kodeorg,tanggal as tglhitung
					,SUM(if(waktu>='00:00' and waktu<'12:00',rain,0)) as mm_pagi 
					,SUM(if(waktu>='12:00' and waktu<'18:00',rain,0)) as mm_sore 
					,SUM(if(waktu>='18:00' and waktu<='23:59',rain,0)) as mm_malam 
					,SUM(rain) as mmhari
					from ".$dbname.".kebun_aws 
					where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal BETWEEN '".substr(min($tgl),0,10)."' and '".substr(max($tgl),0,10)."'
					GROUP BY kodeorg,tglhitung
					ORDER BY kodeorg,tglhitung";
			//echo '<br>'.$srain;
			$qrain=mysql_query($srain) or die(mysql_error($conn));
			while($rrain=mysql_fetch_assoc($qrain)){
				if($rrain['kodeorg']=='TEBE'){
					$kodeorg=$rrain['kodeorg'].'02';
				}else{
					$kodeorg=$rrain['kodeorg'].'01';
				}
				$shujan="select kodeorg,tanggal	from ".$dbname.".kebun_curahhujan 
					where kodeorg='".$kodeorg."' and tanggal='".$rrain['tglhitung']."'";
				$qhujan=mysql_query($shujan) or die(mysql_error($conn));
				$numhujan=mysql_num_rows($qhujan);
				if($numhujan==0){
					$curah="INSERT into ".$dbname.".kebun_curahhujan values('".$kodeorg."','".$rrain['tglhitung']
							."','".$rrain['mm_pagi']."','".$rrain['mm_sore']."','".$rrain['mm_malam']."','"
							.($rrain['mm_pagi']+$rrain['mm_sore']+$rrain['mm_malam'])."')";
				}else{
					$curah="UPDATE ".$dbname.".kebun_curahhujan set pagi='".$rrain['mm_pagi']."',sore='".$rrain['mm_sore']."'
							,malam='".$rrain['mm_malam']."',catatan='".($rrain['mm_pagi']+$rrain['mm_sore']+$rrain['mm_malam'])."' 
							where kodeorg='".$kodeorg."' and tanggal='".$rrain['tglhitung']."'";
				}
				mysql_query($curah) or die(mysql_error($conn)); //Melakukan Insert Curah Hujan
			}
			// --- End Calculate curahhujan ke tanggal pasi sore malam
			
		}
	}else { //Jika belum menekan tombol submit, form dibawah akan muncul..
		//<!-- Form Untuk Upload File CSV-->
		echo "<fieldset><legend>Form</legend>
				<span id=sample><b>AWS Data Uploader. This form must be preceded by a header on the first line</b>
					<a href=tool_slave_getExample.php?form=AWS target=frame>Click here for example</a>
				</span>
				<br><br>(File type support only CSV).<br />
				<form enctype='multipart/form-data' action='' method='post'>File : 
					<input type='file' name='filename' size='100'>
					<input type='submit' name='submit' value='Upload'>
				</form>
			</fieldset>";
	}
	CLOSE_BOX();
	echo close_body();
?>
