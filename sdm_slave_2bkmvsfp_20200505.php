<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

//$proses=$_GET['proses'];

$proses = checkPostGet('proses','');
$unit = checkPostGet('unit','');
$divisi = checkPostGet('divisi','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");

if($tgl1=='--')
{
    $tgl1='';
}
if($tgl2=='--')
{
    $tgl2='';
}

$golkar=makeOption($dbname,'datakaryawan','karyawanid','kodegolongan');
$namagol=makeOption($dbname,'sdm_5golongan','kodegolongan','namagolongan');
$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

$sGetKary="select sum(c.jumlah) as jumlah,a.kodegolongan,a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,a.tipekaryawan,d.tipe,a.subbagian,a.lokasitugas
           from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
           left join ".$dbname.".sdm_5gajipokok c on a.karyawanid=c.karyawanid
           left join ".$dbname.".sdm_5tipekaryawan d on a.tipekaryawan=d.id
           where (a.tanggalkeluar>'".$tgl1."' or a.tanggalkeluar='0000-00-00') and a.lokasitugas='$unit' and a.subbagian='$divisi' and
           a.tipekaryawan in ('1','2','3','4')  group by a.karyawanid order by a.subbagian,a.tipekaryawan,a.kodejabatan,a.namakaryawan asc";  
 
 //echo $sGetKary;
 //$sGetKar="";
 //echo $sGetKary; exit;
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
	$resData[$kar['karyawanid']][]=$kar['karyawanid'];
	//$karyawanid[$kar['karyawanid']]=$kar['karyawanid'];
	$jumlahUmr[$kar['karyawanid']]=$kar['jumlah'];
	$namakar[$kar['karyawanid']]=$kar['namakaryawan'];
	$nikkar[$kar['karyawanid']]=$kar['nik'];
	$nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
	$subbagian[$kar['karyawanid']]=$kar['subbagian'];
	$tipekaryawan[$kar['karyawanid']]=$kar['tipekaryawan'];
	$tipekary[$kar['karyawanid']]=$kar['tipe'];
	$golongankar[$kar['karyawanid']]=$kar['kodegolongan'];
}  
       
        $test = rangeTanggal($tgl1, $tgl2);

	$jmlHari=count($test);
        $colspanTgl=$jmlHari*2;
	//cek max hari inputan
	if($jmlHari>32)
	{
		echo"warning:Range tanggal tidak valid";
		exit();
	}
        
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=mysql_query($sAbsen) or die(mysql_error());
	$jmAbsen=mysql_num_rows($qAbsen);
	$colSpan=intval($jmAbsen)+2;
        
       if($proses=='excel')
       {
           $border="border=1";
       }
       else
       {
           $border="border=0";
       }
        
       
      $ind="<table cellspacing='1' $border class='sortable'>";
      $ind.="<thead><tr class=rowheader>
              <td rowspan=3>No</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['namakaryawan']."</td>
                <td rowspan=3  align=center>".$_SESSION['lang']['nik']."</td>
                <td rowspan=3  align=center>".$_SESSION['lang']['subbagian']."</td>
                <td rowspan=3  align=center>".$_SESSION['lang']['jabatan']."</td>
                <td rowspan=3  align=center>".$_SESSION['lang']['tipe']."</td>
                <td colspan=".$colspanTgl."  align=center>".$_SESSION['lang']['tanggal']."</td>
              </tr>";
             $ind.="<tr>";
             foreach($test as $ar => $isi)
            {
                $qwe=date('D', strtotime($isi));
                $ind.="<td colspan=2 width=5px align=center>";
                if($qwe=='Sun')
                {
                    $ind.="<font color=red  align=center>".$isi."</font>"; 
                }
                else 
                {
                    $ind.= $isi;
                }
                $ind.="</td>";
            }
            $ind.="</tr> <tr>"; 
            for($z=1;$z<=$jmlHari;$z++)
            {  
                $ind.="
                <td  align=center>BKM</td>
                <td  align=center>FP</td>
                ";
            }  
    $ind.="</tr></thead><tbody>";
       
       
	/*$klmpkAbsn=array();
	foreach($test as $ar => $isi)
	{
            $qwe=date('D', strtotime($isi));
	}
	while($rKet=mysql_fetch_assoc($qAbsen))
	{
            $klmpkAbsn[]=$rKet;
	}
	$ind.="
	</tr></thead>
	<tbody>";*/
	
	$resData[]=array();
	$hasilAbsn[]=array();
	$umrList[]=array();

           
        $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi,umr from ".$dbname.".kebun_kehadiran_vw 
            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg like '%".$unit."%'";
        //echo $sKehadiran;
        $rkehadiran=fetchData($sKehadiran);
        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
        {	
                if($resKhdrn['absensi']!='')
                {
                        $umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['umr']);
                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('absensi'=>$resKhdrn['absensi']);
                     //   $notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']].='BKM:'.$resKhdrn['notransaksi'].'__';
                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                }

        }

       

        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where b.notransaksi like '%PNN%' and b.kodeorg like '%".$unit."%' and b.tanggal between '".$tgl1."' and '".$tgl2."'";
         //exit("Error".$sPrestasi);
        $rPrestasi=fetchData($sPrestasi);
        foreach ($rPrestasi as $presBrs =>$resPres)
        {
                        //$umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['upahkerja']);
                        $umrList[$resPres['nik']][$resPres['tanggal']][]=array('umr'=>$resPres['upahkerja']);
                        $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array('absensi'=>'H');
                     //   $notran[$resPres['nik']][$resPres['tanggal']].='BKM:'.$resPres['notransaksi'].'__';
                        $resData[$resPres['nik']][]=$resPres['nik'];

        } 
       

        // ambil pengawas                        
        $dzstr="SELECT tanggal,nikmandor,a.notransaksi,b.upahpremi FROM ".$dbname.".kebun_aktifitas a
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
            union select tanggal,nikmandor1,a.notransaksi,b.upahpremi FROM ".$dbname.".kebun_aktifitas a 
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";

        //exit("Error".$dzstr);   upahpremi
        $dzres=mysql_query($dzstr) or die(mysql_error($conn)." ".$dzstr);
        while($dzbar=mysql_fetch_object($dzres))
        {
            $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
            $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
            $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
        }

        // ambil administrasi                       
        $dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
            union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";
        //exit("Error".$dzstr);
        $dzres=mysql_query($dzstr);
        while($dzbar=mysql_fetch_object($dzres))
        {
            $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
            $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
            $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
        }
        
        
        
        
         // ambil administrasi                       
        $dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
            union select tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";
        //exit("Error".$dzstr);
        $dzres=mysql_query($dzstr);
        while($dzbar=mysql_fetch_object($dzres))
        {
            $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
            $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
            $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
        }
        
        
        
        $sAbsn="select a.karyawanid,tanggal,a.absensi,kodeorg,catu,insentif
                  from ".$dbname.".sdm_absensidt a left join 
                 ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                  where  (b.tanggalkeluar>='".$tgl1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                  and a.tanggal>='".$tgl1."' and a.tanggal<='".$tgl2."' and 
                  b.lokasitugas='".$unit."' and a.fingerprint='1' order by tanggal"; 
                $rAbsn=fetchData($sAbsn);
        foreach ($rAbsn as $absnBrs =>$resAbsn)
        {
                #jika yang sks dkk dibayar 
                
                   //$umrList[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('umr'=>'ind');//kalo jam tidak berpengaruh
                    $umrList[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('umr'=>$resAbsn['insentif']);
                    $hasilAbsnFp[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('absensi'=>$resAbsn['absensi']);
                   //$notran[$resAbsn['karyawanid']][$resAbsn['tanggal']].='ABSENSI:'.$resAbsn['kodeorg'].'__';
                    $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
               

        }
       

function kirimnama($nama) // buat ngirim nama lewat javascript. spasi diganti __
{
    $qwe=explode(' ',$nama);
    foreach($qwe as $kyu){
        $balikin.=$kyu.'__';
    }    
    return $balikin;
}

function removeduplicate($notransaksi) // buat ngilangin nomor transaksi yang dobel
{
    $notransaksi=substr($notransaksi,0,-2);    
    $qwe=explode('__',$notransaksi);
    foreach($qwe as $kyu){
        $tumpuk[$kyu]=$kyu;
    }    
    foreach($tumpuk as $tumpz){
        $balikin.=$tumpz.'__';
    }    

    return $balikin;
}


	$lmit=count($klmpkAbsn);
        
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			
            if($hslAkhir[0]!='' and $namakar[$hslAkhir[0]]!='')
            {
                $no+=1;
                $ind.="<tr class=rowcontent id=row".$no."><td>".$no."</td>";
                $ind.="
                <td>".$namakar[$hslAkhir[0]]."</td>
                <td>".$nikkar[$hslAkhir[0]]."</td>
                <td>".$subbagian[$hslAkhir[0]]."</td>
                <td>".$nmJabatan[$hslAkhir[0]]."</td>
                <td>".$tipekary[$hslAkhir[0]]."</td>
                ";
                foreach($test as $barisTgl =>$isiTgl)
                {
					if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']==$hasilAbsnFp[$hslAkhir[0]][$isiTgl][0]['absensi']){
						$ind.="<td align=center>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
						$ind.="<td align=center>".$hasilAbsnFp[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
					}else{
						$ind.="<td align=center bgcolor='cyan'>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
						$ind.="<td align=center bgcolor='cyan'>".$hasilAbsnFp[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
					}
                }
                    
                $ind.="</tr>";
            }	
	}
	$ind.="</tbody></table>";
	
        
        
switch($proses)
{
######PREVIEW
	case 'preview':
		echo $ind;
		break;
        
	######EXCEL	
	case 'excel':
		//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_absen_BKM_vs_absen_FP".$unit."_".$tgl1._.tgl2;
		if(strlen($ind)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$ind))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
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