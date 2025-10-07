<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$lokasitugas=$_POST['lokasitugas'];
$periode=$_POST['periode'];
$mitmk=$periode."1231";
$tglAbis=date('Y-m-d');

if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan,nik,kodegolongan,
	      COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja from ".$dbname.".datakaryawan
	       where lokasitugas='".$lokasitugas."' 
		   and tanggalmasuk<>'0000-00-00' and (tanggalkeluar>'".date('Y-m-d')."' or tanggalkeluar='0000-00-00')
		   and tanggalmasuk<".$mitmk." and tipekaryawan in(0,1,2,3,6,9)";
}
else
{
    $str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan,nik,kodegolongan,
		  COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja from ".$dbname.".datakaryawan
	       where alokasi=1 and lokasitugas like '%HO'
		   and tanggalmasuk<>'0000-00-00' and (tanggalkeluar>'".date('Y-m-d')."' or tanggalkeluar='0000-00-00')
		   and tanggalmasuk<".$mitmk." and tipekaryawan in(0,1,2,3,6,7,8,9)";
}
//exit("error: ".$str1);
	$res1=mysql_query($str1); 
	$max=mysql_num_rows($res1);
	
	echo"<button class=mybutton onclick=simpanAwal(".$max.")>".$_SESSION['lang']['save']."</button>
	     <table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		    <td>".$_SESSION['lang']['nik']."</td>
		    <td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>".$_SESSION['lang']['tanggalmasuk']."</td>
			<td>Masa Kerja</td>
			<td>".$_SESSION['lang']['dari']."</td>
			<td>".$_SESSION['lang']['tanggalsampai']."</td>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>".$_SESSION['lang']['hakcuti']."</td>
			<td>".$_SESSION['lang']['kodeorganisasi']."</td>
			</tr>
		 </thead>
		 <tbody id=container>"; 
	$no=-1;	 
	while($bar1=mysql_fetch_object($res1))
	{
            //=================================
            //default
            $x=readTextFile('config/jumlahcuti.lst');
            if(intval($x)>0)
                $hakcuti=$x;
            else
                $hakcuti=12;  
            
        //=================================
        $no+=1;

        $thnmasuk=substr($bar1->tanggalmasuk,0,4);
        $tglmasuk=substr($bar1->tanggalmasuk,8,2);
		$blnmasuk='01';
		if($thnmasuk==$periode) $hakcuti=0;
    
		if ($bar1->kodegolongan!='KHT' and $bar1->kodegolongan!='NS' and $bar1->kodegolongan!='PHL' and $bar1->kodegolongan!='E' and $bar1->kodegolongan!='F' and $bar1->kodegolongan!='G'){
        if($periode-$thnmasuk==5 or $periode-$thnmasuk==9 or $periode-$thnmasuk==12 or $periode-$thnmasuk==15 or $periode-$thnmasuk==18 or $periode-$thnmasuk==21 or $periode-$thnmasuk==24 or $periode-$thnmasuk==27 or $periode-$thnmasuk==30 or $periode-$thnmasuk==33 or $periode-$thnmasuk==36 or $periode-$thnmasuk==39 or $periode-$thnmasuk==42 or $periode-$thnmasuk==45 or $periode-$thnmasuk==48 or $periode-$thnmasuk==51 or $periode-$thnmasuk==54 or $periode-$thnmasuk==57 or $periode-$thnmasuk==60) $hakcuti=25;
		}

		if($thnmasuk==$periode-1){
		   $blnmasuk=substr($bar1->tanggalmasuk,5,2);
           $hakcuti=12-$blnmasuk;
		   if($tglmasuk<=10){
              $hakcuti += 1;
		   }
		}

        #jika bukan orang HO maka dapat
		//echo "<BR>".$bar1->namakaryawan;
		//echo "<BR>".substr($bar1->lokasitugas,2,2);
		//echo "<BR>".$periode;
		//echo "<BR>".$thnmasuk;
		//echo "<BR>".$bar1->kodegolongan;
		//echo "<BR>".$hakcuti;
		if(substr($bar1->lokasitugas,2,2)!='HO' and $hakcuti!=25){
			if($periode-$thnmasuk<5){
				if($bar1->kodegolongan=='SR MGR')
					$hakcuti +=6;
				else if($bar1->kodegolongan=='MGR')
					$hakcuti +=4;
				else if($bar1->kodegolongan=='ASST MGR' or $bar1->kodegolongan=='SNM' or $bar1->kodegolongan=='I' or $bar1->kodegolongan=='II' or $bar1->kodegolongan=='III' or $bar1->kodegolongan=='IV')
					$hakcuti +=2;
				else if($bar1->kodegolongan=='NM' or $bar1->kodegolongan=='A' or $bar1->kodegolongan=='B' or $bar1->kodegolongan=='C' or $bar1->kodegolongan=='D')
					$hakcuti +=0;
			}elseif($periode-$thnmasuk<10){
				if($bar1->kodegolongan=='SR MGR')
					$hakcuti +=8;
				else if($bar1->kodegolongan=='MGR')
					$hakcuti +=6;
				else if($bar1->kodegolongan=='ASST MGR' or $bar1->kodegolongan=='SNM' or $bar1->kodegolongan=='I' or $bar1->kodegolongan=='II' or $bar1->kodegolongan=='III' or $bar1->kodegolongan=='IV')
					$hakcuti +=4;
				else if($bar1->kodegolongan=='NM' or $bar1->kodegolongan=='A' or $bar1->kodegolongan=='B' or $bar1->kodegolongan=='C' or $bar1->kodegolongan=='D')
					$hakcuti +=2;
			}elseif($periode-$thnmasuk>=10){
				if($bar1->kodegolongan=='SR MGR')
					$hakcuti +=10;
				else if($bar1->kodegolongan=='MGR')
					$hakcuti +=8;
				else if($bar1->kodegolongan=='ASST MGR' or $bar1->kodegolongan=='SNM' or $bar1->kodegolongan=='I' or $bar1->kodegolongan=='II' or $bar1->kodegolongan=='III' or $bar1->kodegolongan=='IV')
					$hakcuti +=6;
				else if($bar1->kodegolongan=='NM' or $bar1->kodegolongan=='A' or $bar1->kodegolongan=='B' or $bar1->kodegolongan=='C' or $bar1->kodegolongan=='D')
					$hakcuti +=4;
			}
		}
	
		$tgl=substr(str_replace("-","",$bar1->tanggalmasuk),4,4);		
		//$dari=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$periode);
		$dari  =mktime(0,0,0,$blnmasuk,'01',$periode);
		$dari=date('Ymd',$dari);
		//$sampai=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$periode+1);		
		$sampai  =mktime(0,0,0,'12','31',$periode);
		$sampai=date('Ymd',$sampai);
		#jika periode masuk masih belum 1tahun maka 0
                $d=str_replace("-","",$bar1->tanggalmasuk);
                if($d==$dari)
                    $hakcuti=0;

		echo"<tr class=rowcontent id=baris".$no.">
                                    <td hidden id=karyawanid".$no.">".$bar1->karyawanid."</td>
                                        <td>".$bar1->nik."</td>
				   <td id=nama".$no.">".$bar1->namakaryawan."</td>
				   <td>".$bar1->tanggalmasuk."</td>
				   <td>".$bar1->masakerja."</td>				   
				   <td id=dari".$no.">".$dari."</td>
				   <td id=sampai".$no.">".$sampai."</td>
				   <td id=periode".$no.">".$periode."</td>
				   <td id=hak".$no.">".$hakcuti."</td>
				   <td id=kodeorg".$no.">".substr($bar1->lokasitugas,0,4)."</td>
				   ";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
?>