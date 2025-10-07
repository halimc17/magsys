<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdeOrg=checkPostGet('kdeOrg','');
$kdOrg=checkPostGet('kdOrg','');
$afdId=checkPostGet('afdId','');
$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$tgl_1=tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2=tanggalsystem(checkPostGet('tgl_2',''));
$periodeGaji=checkPostGet('periode','');
$periode=explode('-',checkPostGet('periode',''));
$kdUnit=checkPostGet('kdUnit','');
$idKry=checkPostGet('idKry','');
$tipeKary=checkPostGet('tipeKary','');
$sistemGaji=checkPostGet('sistemGaji','');

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
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}

$where = $wherez = $dmna = "";

//ambil query untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                        $where="  lokasitugas in ('".$kodeOrg."')";
                        if($afdId!='')
                        {			
                            $where="  subbagian='".$afdId."'";		
                        }

                }
                else
                {
                        if(strlen($kodeOrg)>4)
                        {			
                                $where="  subbagian='".$kodeOrg."'";		
                        }
                        else
                        {
                                $where="  lokasitugas='".$kodeOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where="  lokasitugas='".$kodeOrg."'";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        

$sGetKary="select a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,subbagian from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
           ".$where." ".$wherez." order by namakaryawan asc";    
  // echo $sGetKary; 
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
   // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nikkar[$kar['karyawanid']]=$kar['nik'];
    $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
}  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $dimanaPnjng=" substring(kodeorg,1,4)='".$kodeOrg."'";
    $dimanaPnjng2=" substring(kodeorg,1,4)='".$kodeOrg."'";
    $dimanaPnjng3=" substr(b.kodeorg,1,4)='".$kodeOrg."'";
    if($afdId!='')
    {
        $dimanaPnjng=" kodeorg like '".substr($afdId,0,4)."%'";
        $dimanaPnjng2=" substring(kodeorg,1,4)='".substr($afdId,0,4)."'";
        $dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($afdId,0,4)."'";
    }
}
else
{
    if(strlen($kodeOrg)>4)
    {
        $dimanaPnjng=" kodeorg='".$kodeOrg."'";
        $dimanaPnjng2=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
        $dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
    }
    else
    {
        $dimanaPnjng=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
        $dimanaPnjng2=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
        $dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
    }
}

switch($proses)
{
        case'preview':
        if(($tgl_1!='')&&($tgl_2!=''))
        {
                $tgl1=$tgl_1;
                $tgl2=$tgl_2;
        }

            $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Both period required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>40)
        {
                echo"warning: Invalid period range";
                exit();
        }
        $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        $qAbsen=mysql_query($sAbsen) or die(mysql_error());
        $jmAbsen=mysql_num_rows($qAbsen);
        $colSpan=intval($jmAbsen)+2;
        echo"<table cellspacing='1' border='0' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td>No</td>
        <td>".$_SESSION['lang']['nama']."</td>
        <td>".$_SESSION['lang']['nik']."</td>
        <td>".$_SESSION['lang']['jabatan']."</td>
        <td>".$_SESSION['lang']['subunit']."</td>";
        $klmpkAbsn=array();
        foreach($test as $ar => $isi)
        {
                $qwe=date('D', strtotime($isi));
                echo"<td width=5px align=center>";
                if($qwe=='Sun')echo"<font color=red>".substr($isi,8,2)."</font>"; else echo(substr($isi,8,2)); 
                echo"</td>";
        }
        while($rKet=mysql_fetch_assoc($qAbsen))
        {
                $klmpkAbsn[]=$rKet;
                echo"<td width=10px>".$rKet['kodeabsen']."</td>";
        }
        echo"
        <td>".$_SESSION['lang']['total']."</td></tr></thead>
        <tbody>";

        $resData[]=array();
        $hasilAbsn[]=array();
        //get karyawan

		

                        
                        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                         //exit("Error".$sPrestasi);
                        $rPrestasi=fetchData($sPrestasi);
                        foreach ($rPrestasi as $presBrs =>$resPres)
                        {
                                        setIt($notran[$resPres['nik']][$resPres['tanggal']],'');
                                        
                                        if($resPres['upahkerja']>0)
                                        {
                                        $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'H');
                                        }
                                        else
                                        {
                                            $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'HK0');
                                        }
                                        $notran[$resPres['nik']][$resPres['tanggal']].='BKM:'.$resPres['notransaksi'].'__';
                                        $resData[$resPres['nik']][]=$resPres['nik'];

                        }
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
                          //exit("Error".$sKehadiran);
                        $rkehadiran=fetchData($sKehadiran);
                        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        {	
                                if($resKhdrn['absensi']!='')
                                {
                                        setIt($notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']],'');
                                        
                                        if($resKhdrn['jhk']>0)
                                        {
                                            $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                                            'absensi'=>$resKhdrn['absensi']);
                                        }
                                        else
                                        {
                                            $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                                            'absensi'=>'HK0');
                                        }
                                        $notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']].='BKM:'.$resKhdrn['notransaksi'].'__';
                                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                }

                        }
                        
                        

// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
//echo $dzstr;
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
	setIt($notran[$dzbar->nikmandor][$dzbar->tanggal],'');
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil traksi                       
$dzstr="SELECT a.tanggal,idkaryawan, a.notransaksi FROM ".$dbname.".vhc_runhk a
        left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".substr($kodeOrg,0,4)."%'
        and ".$where."
    ";
 //exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
	setIt($notran[$dzbar->idkaryawan][$dzbar->tanggal],'');
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');    
    $notran[$dzbar->idkaryawan][$dzbar->tanggal].='TRAKSI:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}


$sAbsn="select absensi,tanggal,karyawanid,kodeorg,catu from ".$dbname.".sdm_absensidt 
			where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
// echo $sAbsn;			
		  //exit("Error".$sAbsn);
		$rAbsn=fetchData($sAbsn);
		foreach ($rAbsn as $absnBrs =>$resAbsn)
		{
			if(!is_null($resAbsn['absensi']))
			{
				setIt($notran[$resAbsn['karyawanid']][$resAbsn['tanggal']],'');
				$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
					'absensi'=>$resAbsn['absensi']);
				$notran[$resAbsn['karyawanid']][$resAbsn['tanggal']].='ABSENSI:'.$resAbsn['kodeorg'].'__';
				$resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
				$catuBerasStat[$resAbsn['karyawanid']][$resAbsn['tanggal']]=$resAbsn['catu'];
			}
		}


function kirimnama($nama) // buat ngirim nama lewat javascript. spasi diganti __
{
    $qwe=explode(' ',$nama);
	$balikin="";
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
	$balikin="";
    foreach($tumpuk as $tumpz){
        $balikin.=$tumpz.'__';
    }    

    return $balikin;
}

$brt=array();
$lmit=count($klmpkAbsn);
$a=0;
foreach($resData as $hslBrs => $hslAkhir)
{	
	if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
	{
		$no+=1;
		echo"<tr class=rowcontent><td>".$no."</td>";
		echo"
		<td>".$namakar[$hslAkhir[0]]."</td>
		<td>".$nikkar[$hslAkhir[0]]."</td>
		<td>".$nmJabatan[$hslAkhir[0]]."</td>
		<td>".$sbgnb[$hslAkhir[0]]."</td>
		";
		foreach($test as $barisTgl =>$isiTgl)
		{
			setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
			setIt($notran[$hslAkhir[0]][$isiTgl],'');
			if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']!='H')
			{
                            if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='HK0')
                            {
                                echo"<td bgcolor=pink title='Click for detail.' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";	
                            }
                            else
                            {
                                echo"<td title='Click for detail.' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)><font color=red>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</font></td>";
                            }
                            
                        }
			else
			{
				$bgdt="";
				setIt($catuBerasStat[$hslAkhir[0]][$isiTgl],0);
				setIt($totTgl[$isiTgl],0);
				if(count($catuBerasStat[$hslAkhir[0]][$isiTgl])!=0){
			        if($catuBerasStat[$hslAkhir[0]][$isiTgl]==0){
				        $bgdt="bgcolor=yellow";
					}
			    }
				echo"<td ".$bgdt." title='Click for detail' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
				$totTgl[$isiTgl]+=1;
			}
			setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
			$brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
        }
		
		foreach($klmpkAbsn as $brsKet =>$hslKet)
		{
			setIt($brt[$hslAkhir[0]][$hslKet['kodeabsen']],'');
			if($hslKet['kodeabsen']!='H')
			{
				echo"<td width=5px align=right><font color=red>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</font></td>";	
			}
			else
			{
				echo"<td width=5px  align=right>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
			}
			setIt($subtot[$hslAkhir[0]]['total'],0);
			$subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
		}	
		echo"<td width=5px  align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
		$subtot['total']=0;
		echo"</tr>";
    }	
}
        $coldt=count($klmpkAbsn);
        echo"<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']." ".$_SESSION['lang']['absensi']."</td>";
        foreach($test as $barisTgl =>$isiTgl)
        {
			setIt($totTgl[$isiTgl],0);
            echo "<td>".$totTgl[$isiTgl]."</td>";
        }
        echo"<td colspan=".($coldt+1).">&nbsp;</td></tr>";
        echo"</tbody></table>";
        break;
        case'pdf':


        $test = dates_inbetween($tgl1, $tgl2);

        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Both period required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>40)
        {
                echo"warning:Invalid period range ".$jmlHari;
                exit();
        }
        //ambil query untuk tanggal kehadiran

        $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        $qAbsen=mysql_query($sAbsen) or die(mysql_error());
        $jmAbsen=mysql_num_rows($qAbsen);
        $colSpan=intval($jmAbsen)+2;

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $period;
                                global $periode;
                                global $kdOrg;
                                global $kdeOrg;
                                global $tgl1;
                                global $tgl2;
                                global $where;
                                global $jmlHari;
                                global $test;
                                global $klmpkAbsn;
                                global $tipeKary;
                                global $sistemGaji;
                                global $dimanaPnjng;
                                global $afdId;
                                global $dimanaPnjng2;
                                global $dimanaPnjng3;


                                $jmlHari=$jmlHari*1.5;
                                $cols=247.5;
                            # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 20;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',10);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['rkpAbsen']." ".$sistemGaji,'',0,'L');
                                $this->Ln();
                                $this->Ln();

                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['rkpAbsen']),'',0,'C');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
                                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	

                                //$this->Cell($jmlHari/100*$width,$height-10,$_SESSION['lang']['tanggal'],1,0,'C',1);
                                //$this->GetX();
                                //$this->SetY($this->GetY());
                                //
                                //$this->SetX($this->GetX()+$cols);

                                foreach($test as $ar => $isi)
                                {
                                    $this->Cell(1.5/100*$width,$height,substr($isi,8,2),1,0,'C',1);	
                                    $akhirX=$this->GetX();
                                }	
                                $this->SetY($this->GetY());
                                $this->SetX($akhirX);
                                $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
                                $qAbsen=mysql_query($sAbsen) or die(mysql_error());
                                while($rAbsen=mysql_fetch_assoc($qAbsen))
                                {
                                        $klmpkAbsn[]=$rAbsen;
                                        $this->Cell(2/100*$width,$height,$rAbsen['kodeabsen'],1,0,'C',1);
                                }
                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['total'],1,1,'C',1);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
                $subtot=array();
                //ambil query untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                        $where="  lokasitugas in ('".$kodeOrg."')";
                        if($afdId!='')
                        {			
                            $where="  subbagian='".$afdId."'";		
                        }

                }
                else
                {
                        if(strlen($kdOrg)>4)
                        {			
                                $where="  subbagian='".$kdOrg."'";		
                        }
                        else
                        {
                                $where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where="  lokasitugas='".$kodeOrg."'";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        


        $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a 
                   left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
                   ".$where." ".$wherez." order by namakaryawan asc";
        //exit("Error:".$sGetKary);
        $rGetkary=fetchData($sGetKary);
        $namakar=Array();
        $nmJabatan=Array();
        foreach($rGetkary as $row => $kar)
        {
           // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
            $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
            $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
        }
        

                        
                        
                        
                        
                        
                        
                        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                        //exit("Error".$sPrestasi);
                        $rPrestasi=fetchData($sPrestasi);
                        foreach ($rPrestasi as $presBrs =>$resPres)
                        {
                            if($resPres['upahkerja']>0)
                            {
                                        $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'H');
                            }
                            else
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'HK0');
                            }
                                        $resData[$resPres['nik']][]=$resPres['nik'];

                        } 
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
                        //exit("Error".$sKehadiran);
                        $rkehadiran=fetchData($sKehadiran);
                        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        {	
                                if($resKhdrn['absensi']!='')
                                {
                                    
                                    if($resKhdrn['jhk']>0)
                                    {    
                                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          'absensi'=>$resKhdrn['absensi']);
                                    }
                                    else
                                    {
                                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          'absensi'=>'HK0');
                                    }
                                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                }

                        }

// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
 //exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil traksi                       
$dzstr="SELECT a.tanggal,idkaryawan FROM ".$dbname.".vhc_runhk a
        left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
        and ".$where."
    ";
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}

$sAbsn="select absensi,tanggal,karyawanid from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
                        //exit("Error".$sAbsn);
                        $rAbsn=fetchData($sAbsn);
                        foreach ($rAbsn as $absnBrs =>$resAbsn)
                        {
                                if(!is_null($resAbsn['absensi']))
                                {
                                        $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                                $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
                                }

                        }


        $brt=array();
        $lmit=count($klmpkAbsn);
        $a=0;
        foreach($resData as $hslBrs => $hslAkhir)
        {	
                        if(!empty($hslAkhir[0])  and !empty($namakar[$hslAkhir[0]]))
                        {
                                $no+=1;
                                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                                $pdf->Cell(10/100*$width,$height,strtoupper($namakar[$hslAkhir[0]]),1,0,'L',1);		
                                $pdf->Cell(10/100*$width,$height,strtoupper($nmJabatan[$hslAkhir[0]]),1,0,'L',1);	
                                foreach($test as $barisTgl =>$isiTgl)
                                {
                                    setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                    $pdf->Cell(1.5/100*$width,$height,$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],1,0,'C',1);	
                                    $akhirX=$pdf->GetX();
                                    setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
                                    $brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
                                }
                                $a=0;
                                for(;$a<$lmit;$a++)
                                {
                                    setIt($brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],0);
                                    $pdf->Cell(2/100*$width,$height,$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],1,0,'C',1);
                                    setIt($subtot[$hslAkhir[0]]['total'],0);
                                    $subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']];
                                }	
                                $pdf->Cell(5/100*$width,$height,$subtot[$hslAkhir[0]]['total'],1,1,'R',1);
                                $subtot[$hslAkhir[0]]['total']=0;
                        }	

        }


        $pdf->Output();

        break;
        case'excel':

        $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Both period required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>40)
        {
                echo"warning: Invalid period range";
                exit();
        }
        $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        $qAbsen=mysql_query($sAbsen) or die(mysql_error());
        $jmAbsen=mysql_num_rows($qAbsen);
        $colSpan=intval($jmAbsen)+2;
        $colatas=$jmlHari+$colSpan+3;
        $stream.="<table border='0'><tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['rkpAbsen'])." ".$sistemGaji."</td></tr>
        <tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2)."</td></tr><tr><td colspan='".$colatas."'>&nbsp;</td></tr></table>";
        $stream.="<table cellspacing='1' border='1' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nik']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bagian']."</td>
         <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['subunit']."</td>";
        $klmpkAbsn=array();
        foreach($test as $ar => $isi)
        {
                //exit("Error".$isi);
                $qwe=date('D', strtotime($isi));

                if($qwe=='Sun')
                {
                    $stream.="<td bgcolor=red align=center width=5px align=center><font color=white>".substr($isi,8,2)."</font></td>";
                }
                else
                {
                    $stream.="<td bgcolor=#DEDEDE align=center width=5px align=center>".substr($isi,8,2)."</td>";
                }

        }
        while($rKet=mysql_fetch_assoc($qAbsen))
        {
                $klmpkAbsn[]=$rKet;
                $stream.="<td bgcolor=#DEDEDE align=center width=10px>".$rKet['kodeabsen']."</td>";
        }
        $stream.="
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td></tr></thead>
        <tbody>";
        //ambil query untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                        $where="  lokasitugas in ('".$kodeOrg."')";
                        if($afdId!='')
                        {			
                            $where="  subbagian='".$afdId."'";		
                        }

                }
                else
                {
                        if(strlen($kdOrg)>4)
                        {			
                                $where="  subbagian='".$kdOrg."'";		
                        }
                        else
                        {
                                $where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where="  lokasitugas='".$kodeOrg."'";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        
        $resData[]=array();
        $hasilAbsn[]=array();
        $sGetKary="select a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,c.nama,subbagian from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
           left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
           where
           ".$where." ".$wherez."order by namakaryawan asc";  
         $namakar=Array();
        $nmJabatan=Array();
        $rGetkary=fetchData($sGetKary);
        foreach($rGetkary as $row => $kar)
    {

          $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
          $nikkar[$kar['karyawanid']]=$kar['nik'];
          $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
          $nmBagian[$kar['karyawanid']]=$kar['nama'];
           $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
    }  
                

                        
                        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                        //exit("Error".$sPrestasi);
                        $rPrestasi=fetchData($sPrestasi);
                        foreach ($rPrestasi as $presBrs =>$resPres)
                        {
                            if($resPres['upahkerja']>0)
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'H');
                            }
                            else
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'HK0');
                            }
                                        
                            $resData[$resPres['nik']][]=$resPres['nik'];

                        } 
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
                        //exit("Error".$sKehadiran);
                        $rkehadiran=fetchData($sKehadiran);
                        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        {	
                            
                                if($resKhdrn['absensi']!='')
                                {
                                    if($resKhdrn['jhk']>0)
                                    {
                                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          'absensi'=>$resKhdrn['absensi']);
                                    }
                                    else
                                    {
                                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          'absensi'=>'HK0');
                                    }
                                        
                                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                }

                        }
                        

// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
 //exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil traksi                       
$dzstr="SELECT a.tanggal,idkaryawan FROM ".$dbname.".vhc_runhk a
        left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
        and ".$where."
    ";
//exit("Error".$dzstr);
$dzres=mysql_query($dzstr);
while($dzbar=mysql_fetch_object($dzres))
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}        



$sAbsn="select absensi,tanggal,karyawanid,catu from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
                        //exit("Error".$sAbsn);
                        $rAbsn=fetchData($sAbsn);
                        foreach ($rAbsn as $absnBrs =>$resAbsn)
                        {
                                if(!is_null($resAbsn['absensi']))
                                {
                                        $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                                $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
				$catuBerasStat[$resAbsn['karyawanid']][$resAbsn['tanggal']]=$resAbsn['catu'];
                                }

                        }


        $brt=array();
        $lmit=count($klmpkAbsn);
        $a=0;
        foreach($resData as $hslBrs => $hslAkhir)
        {	
                        if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
                        {
                                $no+=1;
                                $stream.="<tr><td>".$no."</td>";
                                $stream.="
                                <td>".$namakar[$hslAkhir[0]]."</td>
                                <td>'".$nikkar[$hslAkhir[0]]."</td>
                                <td>".$nmJabatan[$hslAkhir[0]]."</td>
                                <td>".$nmBagian[$hslAkhir[0]]."</td>
                                <td>".$sbgnb[$hslAkhir[0]]."</td>
                                ";
                                foreach($test as $barisTgl =>$isiTgl)
                                {
									setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                    if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']!='H')
                                    {
                                        setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                        if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='HK0')
                                        {
                                            $stream.="<td bgcolor=pink>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                        }
                                        else
                                        {
                                            $stream.="<td><font color=red>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</font></td>";
                                        }
                                        
                                        
                                    }
                                    else
                                    {
				  $bgdt="";
				  setIt($catuBerasStat[$hslAkhir[0]][$isiTgl],0);
				  if(count($catuBerasStat[$hslAkhir[0]][$isiTgl])!=0){
			            if($catuBerasStat[$hslAkhir[0]][$isiTgl]==0){
				          $bgdt="bgcolor=yellow";
					}
			          }
					  setIt($totTgl[$isiTgl],0);
                                        $stream.="<td ".$bgdt.">".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                        $totTgl[$isiTgl]+=1;
                                    }
									setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
                                    $brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
                                }

                                foreach($klmpkAbsn as $brsKet =>$hslKet)
                                {
									setIt($brt[$hslAkhir[0]][$hslKet['kodeabsen']],'');
                                    if($hslKet['kodeabsen']!='H')
                                    {
                                        $stream.="<td width=5px  align=right><font color=red>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</font></td>";	
                                    }
                                    else
                                    {
                                        $stream.="<td width=5px  align=right>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
                                    }
									setIt($subtot[$hslAkhir[0]]['total'],0);
                                    $subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
                                }	
                                $stream.="<td width=5px  align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
                                $subtot['total']=0;
                                $stream.="</tr>";
                        }	
        }
         $coldt=count($klmpkAbsn);
        $stream.="<tr class=rowcontent><td colspan=6>".$_SESSION['lang']['total']."</td>";
        foreach($test as $barisTgl =>$isiTgl)
        {
			setIt($totTgl[$isiTgl],0);
            $stream.= "<td>".$totTgl[$isiTgl]."</td>";
        }
        $stream.="<td colspan=".($coldt+1).">&nbsp;</td></tr>";
        $stream.="</tbody></table>";




                        //echo "warning:".$strx;
                        //=================================================


                        $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                        if(!empty($period))
                        {
                                $art=$period;
                                $art=$art[1].$art[0];
                        }
                        if($periode!='')
                        {
                                $art=$periode;
                                $art=$art[1].$art[0];
                        }
                        if($kdeOrg!='')
                        {
                                $kodeOrg=$kdeOrg;
                        }
                        if($kdOrg!='')
                        {
                                $kodeOrg=$kdOrg;
                        }
                        $nop_="RekapAbsen".$art."__".$kodeOrg;
                        if(strlen($stream)>0)
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
                        if(!fwrite($handle,$stream))
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
        case'getTgl':
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal=$tgl[0]."-".$tgl[1];
                $dmna.=" and periode='".$tanggal."'";
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
                $dmna.=" and periode='".$tanggal."'";
        }
        if($sistemGaji!='')
        {
                $dmna.=" and jenisgaji='".substr($sistemGaji,0,1)."'";
        }
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['empl']['lokasitugas'];
        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' ".$dmna." ";
        //echo"warning".$sTgl;
        $qTgl=mysql_query($sTgl) or die(mysql_error());
        $rTgl=mysql_fetch_assoc($qTgl);
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getKry':
        $optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if(strlen($kdeOrg)>4)
        {
                $where=" lokasitugas='".substr($kdeOrg,0,4)."'";
        }
        else
        {
                $where=" lokasitugas='".$kdeOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
        }
        $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
        $qKry=mysql_query($sKry) or die(mysql_error());
        while($rKry=mysql_fetch_assoc($qKry))
        {
                $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
        }
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
        $qPeriode=mysql_query($sPeriode) or die(mysql_error());
        while($rPeriode=mysql_fetch_assoc($qPeriode))
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        //echo $optPeriode;
        echo $optKry."###".$optPeriode;
        break;
        case'getPeriode':
        if($periodeGaji!='')
        {
                $were=" kodeorg='".$kdUnit."' and periode='".$periodeGaji."' and jenisgaji='".$sistemGaji."'";
        }
        else
        {
                $were=" kodeorg='".$kdUnit."'";
        }
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where ".$were."";
        $qPeriode=mysql_query($sPeriode) or die(mysql_error());
        while($rPeriode=mysql_fetch_assoc($qPeriode))
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sSub="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdUnit."'  order by namaorganisasi asc";
        $qSub=mysql_query($sSub) or die(mysql_error($conn));
        while($rSub=  mysql_fetch_assoc($qSub))
        {
             $optAfd.="<option value='".$rSub['kodeorganisasi']."'>".$rSub['namaorganisasi']."</option>";
        }
        echo $optAfd."####".$optPeriode;
        break;
        case'getPeriodeGaji5':
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optPeriode2=$optPeriode;
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_POST['kdUnit']."' order by periode desc";
        //exit("error:".$sPeriode);
        $qPeriode=mysql_query($sPeriode) or die(mysql_error());
        while($rPeriode=mysql_fetch_assoc($qPeriode))
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        $sPeriode2="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_POST['kdUnit']."' order by periode asc";
        //exit("error:".$sPeriode);
        $qPeriode2=mysql_query($sPeriode2) or die(mysql_error());
        while($rPeriode2=mysql_fetch_assoc($qPeriode2))
        {
                $optPeriode2.="<option value=".$rPeriode2['periode'].">".substr(tanggalnormal($rPeriode2['periode']),1,7)."</option>";
        }
        echo $optPeriode2."####".$optPeriode;
        break;
        default:
        break;
}
?>
