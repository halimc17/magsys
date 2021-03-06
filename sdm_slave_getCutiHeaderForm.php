<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$kodeorg=$_POST['kodeorg'];
$periode=$_POST['periode'];
$tipekaryawan=$_POST['tipekaryawan'];

// ambil data jatah cuti
            $x=readTextFile('config/jumlahcuti.lst');
            if(intval($x)>0)
                $hakcuti=$x;
            else
                $hakcuti=12;  
            
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
    $str1="select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas as locTugas,b.tipekaryawan,b.nik,c.tipe,
	       COALESCE(ROUND(DATEDIFF('".$tglAbis."',b.tanggalmasuk)/365.25,3),0) as masakerja
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
		   left join ".$dbname.".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	       where b.lokasitugas='".$kodeorg."' and b.alokasi=0
		   and (a.periodecuti='".$periode."' or a.periodecuti='".($periode-1)."')
                   and b.tanggalkeluar='0000-00-00' and b.tipekaryawan in(0,1,2,3) and b.tipekaryawan like '%".$tipekaryawan."%'"; 
}
else{
    $str1="select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas  as locTugas,b.tipekaryawan,b.nik,c.tipe,
	       COALESCE(ROUND(DATEDIFF('".$tglAbis."',b.tanggalmasuk)/365.25,3),0) as masakerja
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
		   left join ".$dbname.".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	       where b.lokasitugas='".$kodeorg."' and b.alokasi=1
		   and (a.periodecuti='".$periode."' or a.periodecuti='".($periode-1)."')
                   and b.tanggalkeluar='0000-00-00' and b.tipekaryawan in(0,1,2,3) and b.tipekaryawan like '%".$tipekaryawan."%'"; 
}		  
	$res1=mysql_query($str1); 
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		    <td style='text-align:center;'>No</td>
			<td style='text-align:center;'>".$_SESSION['lang']['kodeorganisasi']."</td>		 
		    <td style='text-align:center;'>".$_SESSION['lang']['nik']."</td>
		    <td style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</td>
		    <td style='text-align:center;'>".$_SESSION['lang']['tipekaryawan']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['tanggalmasuk']."</td>
			<td style='text-align:center;'>Masa Kerja (Tahun-Bulan)</td>			
			<td style='text-align:center;'>".$_SESSION['lang']['periode']."</td>			
			<td style='text-align:center;'>".$_SESSION['lang']['dari']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['tanggalsampai']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['hakcuti']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['diambil']." (Hari)</td>
			<td style='text-align:center;'>".$_SESSION['lang']['sisa']."</td>
			</tr>
		 </thead>
		 <tbody id=container>"; 
	$no=0;	
	
	//Get RangeTanggal
	function getRangeTanggal($tglAwal,$tglAkhir){
		$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
		$jlhHari = $jlh / (3600*24);
		return $jlhHari + 1;
	}
	
	function adddate($vardate,$added){
		$data = explode("-", $vardate);
		$date = new DateTime();
		$date->setDate($data[0], $data[1], $data[2]);
		$date->modify("".$added."");
		$day= $date->format("Y-m-d");
		return $day;
	}
	
	while($bar1=mysql_fetch_object($res1))
	{
		$no+=1;
            #jika bukan orang HO maka dapat 
#            if($bar1->tipekaryawan==0 and substr($bar1->lokasitugas,2,2)!='HO')
#                    $hakcuti=12;
#            else if($bar1->tipekaryawan!=0 and substr($bar1->lokasitugas,2,2)!='HO')
#                    $hakcuti=12;
		$penambahTanggal = adddate($bar1->sampai,"+180 days");
		// echo getRangeTanggal(date('Y-m-d'),$bar1->sampai);
		// echo adddate(date('Y-m-d'), "-180 days");
		
		//Masa kerja
		$date1=$bar1->tanggalmasuk;
        $date2=date('Y-m-d');
        
        $diff = abs(strtotime($date2) - strtotime($date1)); 
                
        $years = floor($diff / (365*60*60*24)); 
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); 
        
        $lamaKerja=" ".$years." Tahun ".$months." Bulan";
		
		$sisacuti=$hakcuti-$bar1->diambil;
		if(getRangeTanggal(date('Y-m-d'),$penambahTanggal) > 0){
			echo"<tr class=rowcontent id=baris".$no.">
					   <td>".$no."</td>
					   <td id=kodeorg".$no.">".substr($bar1->locTugas,0,4)."</td>
					   <td id=karyawanid".$no." hidden>".$bar1->karyawanid."</td>
									<td >".$bar1->nik."</td>
					   <td class=firsttd id=nama".$no."  title='Click for detail' style='cursor:pointer'  onclick=showByUser('".$bar1->karyawanid."',event)>".$bar1->namakaryawan."</td>
					   <td>".$bar1->tipe."</td>
					   <td>".tanggalnormal($bar1->tanggalmasuk)."</td>
					   <td>".$lamaKerja."</td>
					   <td id=periode".$no.">".$bar1->periodecuti."</td>				   
					   <td id=dari".$no.">".tanggalnormal($bar1->dari)."</td>
					   <td id=sampai".$no.">".tanggalnormal($penambahTanggal)."</td>
					   <td id=hak".$no." align=right>".$hakcuti."</td>
					   <td id=diambil".$no." align=right>".$bar1->diambil."</td>
					   <td style='text-align:right;'><input type=text id=sisa".$no." class=myinputtextnumber size=4 conkeypress=\"return angka_doang(event);\" value='".$sisacuti."'>
					   <img src='images/save.png'  title='Save' class=resicon onclick=updateSisa('".$periode."','".$bar1->karyawanid."','".$bar1->kodeorg."','sisa".$no."')>
					   <img src='images/addplus.png'  title='".$_SESSION['lang']['tambah']."' class=resicon onclick=\"tambahData('".$bar1->periodecuti."','".$bar1->karyawanid."','".$bar1->kodeorg."','".$bar1->namakaryawan."','".$sisacuti."');\">
					   </td>
				</tr>	   
					   ";
		}
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
?>