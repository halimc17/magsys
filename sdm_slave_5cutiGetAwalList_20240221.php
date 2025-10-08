<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$lokasitugas=$_POST['lokasitugas'];
$periode=$_POST['periode'];
$mitmk=$periode."1231";
$tglAbis=date('Y-m-d');
$tglKeluar=$periode."-01-01";

if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$str1="select a.karyawanid,a.namakaryawan,a.tanggalmasuk,if(a.karyawanid=c.promosi,a.tanggalpengangkatan,a.tanggalmasuk) as tanggalhitung ,a.lokasitugas,a.tipekaryawan,a.nik,a.kodegolongan
	       ,COALESCE(ROUND(DATEDIFF('2018-12-26',a.tanggalmasuk)/365.25,3),0) as masakerja,c.promosi 
		   from ".$dbname.".datakaryawan a
		   LEFT JOIN (select DISTINCT(b.karyawanid) as promosi from ".$dbname.".sdm_riwayatjabatan b where b.ketipekaryawan in ('0','1','3')) c on a.karyawanid=c.promosi
	       where a.lokasitugas='".$lokasitugas."' 
		   and a.tanggalmasuk<>'0000-00-00' and (a.tanggalkeluar>='".$tglKeluar."' or a.tanggalkeluar='0000-00-00')
		   and a.tanggalmasuk<".$mitmk." and a.tipekaryawan in(0,1,2,3,6,9)";
}
else
{
	$str1="select a.karyawanid,a.namakaryawan,a.tanggalmasuk
		   ,if(a.karyawanid=c.promosi and year(a.tanggalpengangkatan)>2017 ,a.tanggalpengangkatan,a.tanggalmasuk) as tanggalhitung	
		   ,a.lokasitugas,a.tipekaryawan,a.nik,a.kodegolongan
	       ,COALESCE(ROUND(DATEDIFF('2018-12-26',a.tanggalmasuk)/365.25,3),0) as masakerja,c.promosi 
		   from ".$dbname.".datakaryawan a
		   LEFT JOIN (select DISTINCT(b.karyawanid) as promosi from ".$dbname.".sdm_riwayatjabatan b where b.ketipekaryawan in ('0','1','3')) c on a.karyawanid=c.promosi
	       where a.alokasi=1 and a.lokasitugas like '%HO'
		   and a.tanggalmasuk<>'0000-00-00' and (a.tanggalkeluar>='".$tglKeluar."' or a.tanggalkeluar='0000-00-00')
		   and a.tanggalmasuk<".$mitmk." and a.tipekaryawan in(0,1,2,3,6,7,8,9)";
}
//exit("Warning: ".$str1);
	$res1=mysql_query($str1); 
	$max=mysql_num_rows($res1);
	
	echo"<button class=mybutton onclick=simpanAwal(".$max.")>".$_SESSION['lang']['save']."</button>
	     <table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		    <td>".$_SESSION['lang']['nik']."</td>
		    <td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>".$_SESSION['lang']['tanggalmasuk']."</td>
			<td>".$_SESSION['lang']['tanggal']."</td>
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

        $thnhitung=substr($bar1->tanggalhitung,0,4);
        $tglhitung=substr($bar1->tanggalhitung,8,2);
		$blnhitung='01';
        $thnmasuk=substr($bar1->tanggalmasuk,0,4);
        $tglmasuk=substr($bar1->tanggalmasuk,8,2);
		$blnmasuk='01';
		if($thnhitung==$periode) $hakcuti=0;
    
		if ($bar1->kodegolongan!='KHT' and $bar1->kodegolongan!='NS' and $bar1->kodegolongan!='PHL' and $bar1->kodegolongan!='E' and $bar1->kodegolongan!='F' and $bar1->kodegolongan!='G'){
        if($periode-$thnhitung==5 or $periode-$thnhitung==9 or $periode-$thnhitung==12 or $periode-$thnhitung==15 or $periode-$thnhitung==18 or $periode-$thnhitung==21 or $periode-$thnhitung==24 or $periode-$thnhitung==27 or $periode-$thnhitung==30 or $periode-$thnhitung==33 or $periode-$thnhitung==36 or $periode-$thnhitung==39 or $periode-$thnhitung==42 or $periode-$thnhitung==45 or $periode-$thnhitung==48 or $periode-$thnhitung==51 or $periode-$thnhitung==54 or $periode-$thnhitung==57 or $periode-$thnhitung==60) $hakcuti=25;
		}

		if($thnhitung==$periode-1){
		   $blnhitung=substr($bar1->tanggalhitung,5,2);
           $hakcuti=12-$blnhitung;
		   if($tglhitung<=10){
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
	
		$tgl=substr(str_replace("-","",$bar1->tanggalhitung),4,4);		
		//$dari=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$periode);
		$dari  =mktime(0,0,0,$blnhitung,'01',$periode);
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
				   <td>".$bar1->tanggalhitung."</td>
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