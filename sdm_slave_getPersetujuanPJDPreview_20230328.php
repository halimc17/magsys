<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi=$_GET['notransaksi'];

$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";	
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{

	$jabatan='';
	$namakaryawan='';
	$bagian='';	
	$karyawanid='';
	$strc="select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan, a.kodeorganisasi, a.tipekaryawan 
		from ".$dbname.".datakaryawan a left join  ".$dbname.".sdm_5jabatan b
		on a.kodejabatan=b.kodejabatan
		where a.karyawanid=".$bar->karyawanid;
	$resc=mysql_query($strc);
	while($barc=mysql_fetch_object($resc))
	{
	  	$jabatan=$barc->namajabatan;
		$namakaryawan=$barc->namakaryawan;
		$bagian=$barc->bagian;
		$karyawanid=$barc->karyawanid;
		$kKodeorganisasi=$barc->kodeorganisasi;
		$kTipeKaryawan=$barc->tipekaryawan;
	}
	//===============================
	
	$kodeorg=$bar->kodeorg;
	$persetujuan=$bar->persetujuan;
	$hrd=$bar->hrd; 
	$tujuan3=$bar->tujuan3;
	$tujuan2=$bar->tujuan2;	
	$tujuan1=$bar->tujuan1;
	$tanggalperjalanan=tanggalnormal($bar->tanggalperjalanan);
	$tanggalkembali=tanggalnormal($bar->tanggalkembali);
	$uangmuka=$bar->uangmuka;
	$tugas1=$bar->tugas1;
	$tugas2=$bar->tugas2;
	$tugas3=$bar->tugas3;
	$tujuanlain=$bar->tujuanlain;
	$tugaslain=$bar->tugaslain;
	$pesawat=$bar->pesawat;
	$darat=$bar->darat;
	$laut=$bar->laut;
	$mess=$bar->mess;
	$hotel=$bar->hotel;	
	$statushrd=$bar->statushrd;
	$xhrd=$bar->statushrd;
	$xper=$bar->statuspersetujuan;
	if($statushrd==0)
		$statushrd=$_SESSION['lang']['wait_approval'];
	else if($statushrd==1)
		$statushrd=$_SESSION['lang']['disetujui'];
	else 
		$statushrd=$_SESSION['lang']['ditolak'];
	
	$statuspersetujuan=$bar->statuspersetujuan;
	if($statuspersetujuan==0)
		$perstatus=$_SESSION['lang']['wait_approval'];
	else if($statuspersetujuan==1)
		$perstatus=$_SESSION['lang']['disetujui'];
	else 
		$perstatus=$_SESSION['lang']['ditolak'];
	
	//ambil bagian,jabatan persetujuan
		$perjabatan='';
		$perbagian='';
		$pernama='';
	$strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
	       ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		   where karyawanid=".$persetujuan;	   
	$resf=mysql_query($strf);
	while($barf=mysql_fetch_object($resf))
	{
		$perjabatan=$barf->namajabatan;
		$perbagian=$barf->bagian;
		$pernama=$barf->namakaryawan;
	}	 
	
	//ambil jabatan, karyawan perdin
	$hjabatan='';
	$hbagian='';
	$hnama='';
	$hgolongan='';
	$strf="select a.bagian,b.namajabatan,a.namakaryawan,a.kodegolongan from ".$dbname.".datakaryawan a left join
	       ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		   where karyawanid=".$karyawanid;	
	$resf=mysql_query($strf);
	while($barf=mysql_fetch_object($resf))
	{
		$hjabatan=$barf->namajabatan;
		$hbagian=$barf->bagian;
		$hnama=$barf->namakaryawan;
		$hgolongan=$barf->kodegolongan;
	}
	
	//Get Lokasi Tugas
	$strLTgs="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$tujuan1."'";
	$resLTgs=mysql_query($strLTgs);
	while($barLTgs=mysql_fetch_object($resLTgs)){
		$LTgs=$barLTgs->namaorganisasi;
	}
	
	// PT Tujuan
	$qTujuan = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".$tujuan2."'");
	$resTujuan = fetchData($qTujuan);
	$ptTujuan = $resTujuan[0]['induk'];
	
	// Regional Tujuan
	$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan2."'");
	$resRegional = fetchData($qRegional);
	$reg = $resRegional[0]['regional'];
	if(empty($reg)){
		$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan3."'");
		$resRegional = fetchData($qRegional);
		$reg = $resRegional[0]['regional'];
		if(empty($reg)){
			$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"regional='".$tujuanlain."'");
			$resRegional = fetchData($qRegional);
			$reg = $resRegional[0]['regional'];
			if(empty($reg)){
				$reg='KALIMANTAN';
			}
		}
	}

	// Get Hari Libur
	$strlibur="select count(*) as jumlahlibur from ".$dbname.".sdm_5harilibur where kebun in ('HOLDING','GLOBAL') and keterangan='libur' and (tanggal>='".$bar->tanggalperjalanan."' and tanggal<='".$bar->tanggalkembali."')";
	$reslibur=mysql_query($strlibur);
	$jmlhrlibur=0;
	while($barlibur=mysql_fetch_object($reslibur))
	{ 
		$jmlhrlibur=$barlibur->jumlahlibur;
	}

	//Get Uang Muka
	function getRangeTanggal($tglAwal,$tglAkhir){
		$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
		$jlhHari = $jlh / (3600*24);
		return $jlhHari + 1;
	}
	$jlhHari=getRangeTanggal($bar->tanggalperjalanan,$bar->tanggalkembali);
    $jmlharilokal=$jlhHari-$jmlhrlibur;
	//exit('Warning:'.$jlhHari." - ".$jmlhrlibur." = ".$jmlharilokal."  ".$strlibur);
/*
	$sUangMuka="select sum(sekali) as satu, sum(perhari) as dua, sum(hariketiga) as tiga
		from ".$dbname.".sdm_5uangmukapjd where regional='".$reg."' and
		kodegolongan='".$hgolongan."' and jenis in (2,6,9,10,11)";
	$rUangMuka=mysql_query($sUangMuka);
	// exit("error : ".$sUangMuka);
	if($rUangMuka) {
		// Uang Non Uang Saku
		while($bUangMuka=mysql_fetch_object($rUangMuka)) {
			if($jlhHari > 2){
				$jlhUangMuka = (($bUangMuka->satu)+(($bUangMuka->dua)*$jlhHari)) + (($bUangMuka->tiga)*($jlhHari-2));
			}else{
				$jlhUangMuka = (($bUangMuka->satu)+(($bUangMuka->dua)*$jlhHari));
			}
		}
		
		// Uang Saku
		$jenisUS = ($jlhHari > 1)? 11: 7;
		$qUangSaku = selectQuery($dbname,'sdm_5uangmukapjd','sekali,perhari,hariketiga',
								 "regional='".$reg."' and kodegolongan='".$hgolongan."' and jenis = ".$jenisUS);
		$resUangSaku = fetchData($qUangSaku);
		if(!empty($resUangSaku)) {
			$rpUS = $resUangSaku[0];
			$jlhUangMuka += $rpUS['sekali'] + ($rpUS['perhari'] * $jlhHari);
			if($jlhHari > 2){
				$jlhUangMuka += $rpUS['perhari'] * ($jlhHari - 2);
			}
		}
*/
//	}
  }
  
 
  echo"<div style=\"height:300px;width:600;overflow:scroll;\">";
   echo $_SESSION['lang']['perjalanandinas'].": 
     <img src=images/pdf.jpg class=resicon  title='PUK ".$_SESSION['lang']['pdf']."' onclick=\"previewPUKPJDPDF('".$notransaksi."',event);\">
     <table class=standard cellspacing=1>
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['nama']."</td>
		<td>".$namakaryawan."</td>
	 </tr>
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['kodeorg']."</td>
		<td>".$kodeorg."</td>
	 </tr>	 
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['tanggaldinas']."</td>
		<td>".$tanggalperjalanan.". &nbsp 
		    ".$_SESSION['lang']['tanggalkembali']." 
			".$tanggalkembali."
		</td>
	 </tr>	
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['pemberitugas']."</td>
		<td>".$LTgs."</td>
	 </tr>
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['transportasi']."/".$_SESSION['lang']['akomodasi']."</td>
		<td>
		     <input type=checkbox id=pesawat disabled ".($pesawat==1?'checked':'')."> ".$_SESSION['lang']['pesawatudara']."
			 <input type=checkbox id=darat disabled ".($darat==1?'checked':'')."> ".$_SESSION['lang']['transportasidarat']."
			 <input type=checkbox id=laut disabled ".($laut==1?'checked':'')."> ".$_SESSION['lang']['transportasiair']."
			 <input type=checkbox id=mess disabled ".($mess==1?'checked':'')."> ".$_SESSION['lang']['mess']."
			 <input type=checkbox id=hotel disabled ".($hotel==1?'checked':'')."> ".$_SESSION['lang']['hotel']."
        </td>
	 </tr>
	 </table>
	 <table>";

	if($jlhHari == 1){
		$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
		where a.regional='".$reg."' and a.kodegolongan='".$hgolongan."' and a.jenis in (2,6,7) order by a.jenis";
	}else{
		$sUangMuka="select a.*,b.id,b.keterangan as namajenis from ".$dbname.".sdm_5uangmukapjd a left join ".$dbname.".sdm_5jenisbiayapjdinas b on b.id=a.jenis 
		where a.regional='".$reg."' and a.kodegolongan='".$hgolongan."' and a.jenis in (2,6,8,9,10,11) order by a.jenis";
	}
	$rUangMuka=mysql_query($sUangMuka);
	$jlhUangMuka=0;
	if($rUangMuka) {
		$nilaipjd=0;
		while($bUangMuka=mysql_fetch_object($rUangMuka)) {
			if(($pesawat+$darat+$laut)==0 and $bUangMuka->jenis=='2'){
				continue;
			}
			if(($hotel==0 or substr($kodeorg,2,2)=='HO') and $bUangMuka->jenis=='8'){
				continue;
			}
			if($bUangMuka->sekali!=0){
				$nilaipjd=$bUangMuka->sekali;
				$jmlkali=1;
			}
			if($bUangMuka->perhari!=0){
				if($bUangMuka->jenis==10){
				   $nilaipjd=$bUangMuka->perhari*$jmlharilokal;
				   $jmlkali=$jmlharilokal;
				}elseif($bUangMuka->jenis==8){
					$nilaipjd=$bUangMuka->perhari*($jlhHari-1);
					$jmlkali=$jlhHari-1;
				}else{
					$nilaipjd=$bUangMuka->perhari*$jlhHari;
					$jmlkali=$jlhHari;
				}
			}
			if($bUangMuka->hariketiga!=0){
				if($bUangMuka->jenis==10){
					$nilaipjd=$bUangMuka->hariketiga*($jmlharilokal - 2);
					$jmlkali=$jmlharilokal-2;
				}else{
					$nilaipjd=$bUangMuka->hariketiga*($jlhHari - 2);
					$jmlkali=$jlhHari-2;
				}
			}
			$jlhUangMuka+=$nilaipjd;
			if($xhrd==0 or $xper==0){
			if($jmlkali!=0){
				echo "
				<tr class=rowcontent>
					<td>".$bUangMuka->namajenis."</td>
					<td align='Right'>".$jmlkali."</td>
					<td align='Right'>".($bUangMuka->sekali+$bUangMuka->perhari+$bUangMuka->hariketiga)."</td>
					<td align='Right'>".number_format($nilaipjd,2,',','.')."</td>
					<td></td>
				</tr>";
			}
			}
		}
	}

    echo "
	 <tr class=rowcontent>
	   <td>
	      ".$_SESSION['lang']['uangmuka']."
	   </td><td></td><td></td>
	   <td align='Right'>
	    <input type=hidden id=nitransaksipjd value='".$notransaksi."'>";
	if($xhrd==0 or $xper==0)
	  {	 
		echo "<span id=oldval style='display:block;'>".number_format($uangmuka,2,'.',',')."</span>";
		if($uangmuka==0){
			echo "<input type=text class=myinputtextnumber id=newvalpjd onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=17 disabled value='".$jlhUangMuka."'>";
		}else{
			//echo "<input type=text class=myinputtextnumber id=newvalpjd onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=17 value='".$uangmuka."'>";
			echo "<input type=text class=myinputtextnumber id=newvalpjd onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=17 disabled value='".$jlhUangMuka."'>";
		}
		echo "<td><button class=mybutton onclick=saveUpdateValPJD()>".$_SESSION['lang']['ganti']."</button></td>";
	  }else{
		echo "<span id=oldval style='display:block;'>".number_format($uangmuka,2,'.',',')."</span>";
	  }
	echo"   
	   </td>
	 </tr> 	 	 
	 </table>
	 <table class=standard  cellspacing=1>
	   <tr class=rowcontent>
	     <td>
		     ".$_SESSION['lang']['tujuan']."1
		 </td>
	     <td>
		   ".$tujuan2.":
		   ".$tugas2."
		  </td> 
		</tr>
		<tr class=rowcontent> 
	     <td>
		    ".$_SESSION['lang']['tujuan']."2
		 </td>
	     <td>
		   ".$tujuan3.":
		   ".$tugas3."		 
		  </td>		 		 		 
	   </tr>
	   
	   <tr class=rowcontent>
	     <td>
		     ".$_SESSION['lang']['tujuan']."3
		 </td>
	     <td>
		   ".$tujuanlain.":
		   ".$tugaslain."		 
		 </td>
		</tr>
	 </table>";
        
        echo"<br>";
        
        echo"
            <table>
        
        <tr class=rowcontent><thead>
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['jenis']."</td>
                    <td  align=center>".$_SESSION['lang']['keterangan']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']."</td>
                    <td align=center>Rp. Pengajuan</td>
                    <td align=center>Rp. HRD</td>
                </tr></thead>";
        
        $arrJenis=  makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
        
        $iBy="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ";    
        $no=0;
        $nBy=  mysql_query($iBy)or die (mysql_error($conn));
        while($dBy=  mysql_fetch_assoc($nBy))
        {
            $no+=1;
            echo"
                <tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$arrJenis[$dBy['jenisbiaya']]."</td>
                    <td>".$dBy['keterangan']."</td>
                    <td>".tanggalnormal($dBy['tanggal'])."</td>
                    <td align=right>".number_format($dBy['jumlah'])."</td>
                    <td align=right>".number_format($dBy['jumlahhrd'])."</td>
                </tr>";
        }
        
        
        echo"        
            </table></div>
        ";
	
?>
