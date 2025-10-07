<?php
	function dates_inbetween($date1, $date2){
		$day = 60*60*24;
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
		$dates_array = array();
		$dates_array[] = date('Y-m-d',$date1);
		for($x = 1; $x < $days_diff; $x++){
			$dates_array[] = date('Y-m-d',($date1+($day*$x)));
		}
		$dates_array[] = date('Y-m-d',$date2);
		return $dates_array;
	}

	function dates_inbetween2($date1, $date2){ //function Baru
		$date1 = date('Y-m-d',strtotime($date1));
		$date2 = date('Y-m-d',strtotime($date2));
		$dates_array = array();
		$dates_array[] = $date1;
		$tgl = $date1;
		while($tgl<$date2){
			$dates_array[]=date('Y-m-d',strtotime('+1 days',strtotime(substr($tgl,0,10))));
			$tgl=date('Y-m-d',strtotime('+1 days',strtotime(substr($tgl,0,10))));
		}
		return $dates_array;
	}
		
	$tgltgl = dates_inbetween2('2023-10-01','2023-10-31');
		$tgl1=strtotime('2023-10-01');
		$tgl2=strtotime('2023-10-31');
		$bagi=60*60*24;
		$selisih=$tgl2-$tgl1;
		$diff = round(($tgl2 - $tgl1)/$bagi)+1;

		$angka1=1698576800;
		$angka2=1698586800;
		$angka3=1698596800;
		$angka4=1698606800;
		$angka5=1698616800;
		$angka6=1698626800;
		$angka7=1698636800;
		$angka8=1698646800;
		$angka9=1698656800;

		echo "<br>angka1 = ".date('Y-m-d',$angka1);
		echo "<br>angka2 = ".date('Y-m-d',$angka2);
		echo "<br>angka3 = ".date('Y-m-d',$angka3);
		echo "<br>angka4 = ".date('Y-m-d',$angka4);
		echo "<br>angka5 = ".date('Y-m-d',$angka5);
		echo "<br>angka6 = ".date('Y-m-d',$angka6);
		echo "<br>angka7 = ".date('Y-m-d',$angka7);
		echo "<br>angka8 = ".date('Y-m-d',$angka8);
		echo "<br>angka9 = ".date('Y-m-d',$angka9);
		echo "<br>";

		echo "<br>date1 = ".$tgl1;
		echo "<br>date2 = ".$tgl2;
		echo "<br>day = ".$bagi;
		echo "<br>selisih = ".$selisih;
		echo "<br>diff = ".$diff;
		echo "<br>tgl01 = ".($tgl1+($bagi*1));
		echo "<br>tgl01 = ".date('Y-m-d',($tgl1+($bagi*1)));
		echo "<br>tgl28 = ".($tgl1+($bagi*28));
		echo "<br>tgl28 = ".date('Y-m-d',($tgl1+($bagi*28)));
		echo "<br>tgl29 = ".($tgl1+($bagi*29));
		echo "<br>tgl29 = ".date('Y-m-d',($tgl1+($bagi*29)));

	foreach($tgltgl as $ar => $isi)
    {
		$qwe=date('D', strtotime($isi));
		echo "<br>".strtotime($isi).' '.$isi ;
	}
	echo "<br>Selesai...!";
	
?>
