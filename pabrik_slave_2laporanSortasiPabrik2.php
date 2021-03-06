<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdPbrk = checkPostGet('kdPbrk','');
$statBuah = checkPostGet('statBuah','');
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir',''));
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$suppId = checkPostGet('suppId','');
$kdOrg = checkPostGet('kdOrg','');
$intextId = checkPostGet('intextId','');
$BuahStat = checkPostGet('BuahStat','');

// kondisi mendapatkan data
$ukuran['S']=$_SESSION['lang']['kecil'];
$ukuran['M']=$_SESSION['lang']['sedang'];
$ukuran['L']=$_SESSION['lang']['besar'];

// Init Sub Total
setIt($subTotal['beratmasuk'],0);
setIt($subTotal['beratkeluar'],0);
setIt($subTotal['beratbersih'],0);
setIt($subTotal['jjgSortasitot'],0);
setIt($subTotal['prsnBrondolan'],0);
setIt($subTotal['jmlhTndn'],0);
setIt($subTotal['kgpotsortasi'],0);

// Init Total
$totkgpwajib=$totkgbmentah=$totkgbmengkal=$totkgjjgbhmentah=
$totkgbrndlnpersen=$totkgtpanjang=$totkgbnginap=$totkgbbasah=$totkgsampah=0;

if(($proses=='preview')or($proses=='excel')){
            if($suppId!='')
            {
                 $str="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$suppId."'";
                $res=mysql_query($str);
                while($bar=mysql_fetch_object($res))
                {
                    $namaspl=$_SESSION['lang']['namasupplier'].":".$bar->namasupplier;
                }
            }
            else if($kdOrg!='')
            {
                 $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
                $res=mysql_query($str);
                while($bar=mysql_fetch_object($res))
                {
                    $namaspl=$_SESSION['lang']['unit'].":".$bar->namaorganisasi;
                }
            }
            else
            {
                $namaspl=$_SESSION['lang']['dari'].":".$_SESSION['lang']['all'];
            }
    
        if(($tglAkhir=='')||($tglAwal==''))
        {
                echo"warning:Date required";
                exit();
        }
        $thn=substr($tglAwal,0,4);
        $bln=substr($tglAwal,4,2);
        $dte=substr($tglAwal,6,2);
        $tglAwal1=$thn."-".$bln."-".$dte;
        $thn2=substr($tglAkhir,0,4);
        $bln2=substr($tglAkhir,4,2);
        $dte2=substr($tglAkhir,6,2);
        $tglAkhir1=$thn2."-".$bln2."-".$dte2;

//        $stream.="<div style=overflow:auto; height:650px;>";
        $stream.="Mill FFB Grading Report ".$kdPbrk."  ".$namaspl." period :".$tglAwal."-".$tglAkhir."";

//        $colspand=count($kodeFraksi);bgcolor=#DEDEDE 
        if($proses=='preview'){
            $stream.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
            $bgcolor="";
        }else{
            $stream.="<table cellpadding=0 cellspacing=0 border=1 class=sortable width=100%>";
            $bgcolor=" bgcolor=#DEDEDE";
        }
        $stream.="<thead><tr class=rowheader>";
        $stream.="<td rowspan=3".$bgcolor.">No.</td>";
        $stream.="<td rowspan=3".$bgcolor.">".$_SESSION['lang']['nospb']."</td>";
        $stream.="<td rowspan=3".$bgcolor.">".$_SESSION['lang']['noTiket']."</td>";
        $stream.="<td rowspan=3".$bgcolor.">".$_SESSION['lang']['tanggal']."</td>";
        $stream.="<td rowspan=3".$bgcolor.">".str_replace(" ","<br>",$_SESSION['lang']['nopol'])."</td>";
        $stream.="<td align=center  colspan=3 valign=middle".$bgcolor.">".$_SESSION['lang']['hslTimbangan']."</td>";
        $stream.="<td rowspan=3".$bgcolor.">".str_replace(" ","<br>",$_SESSION['lang']['jmlhTandan'])."</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']."</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['sortasi']."(JJG)</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['sortasi']."</td>";
        
        $stream.="<td align=center valign=middle colspan=18".$bgcolor.">".$_SESSION['lang']['hslSortasi']."</td>";
        $stream.="<td align=center rowspan=3 valign=middle".$bgcolor.">kriteria buah</td>";
        $stream.="<td align=center rowspan=3 valign=middle".$bgcolor.">".$_SESSION['lang']['potongan']."(Kg)</td></tr>";
        $stream.="<tr>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratMasuk']."</td>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratkosong']."</td>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratBersih']."</td>";
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('pwajib', 'Potongan Wajib', 'sortasi', NULL, 'Potongan Wajib', 'Compulsory Deduction');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bmentah', 'Buah Mentah', 'sortasi', NULL, 'Buah Mentah', 'Raw Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bmengkal', 'Buah Mengkal', 'sortasi', NULL, 'Buah Mengkal', 'Half Fresh Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('jjgbhmentah', 'Janjang Buah Mentah', 'sortasi', NULL, 'Janjang Buah Mentah', 'Raw Fruit Bunch');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('brndlnpersen', 'Brondolan', 'sortasi', NULL, 'Brondolan', 'Brondolan');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('tpanjang', 'Tangkai Panjang', 'sortasi', NULL, 'Tangkai Panjang', 'Long Stalk');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bnginap', 'Buah Menginap', 'sortasi', NULL, 'Buah Menginap', 'Over Night Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bbasah', 'Buah Basah', 'sortasi', NULL, 'Buah Basah', 'Wet Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('sampah', 'Sampah', 'sortasi', NULL, 'Sampah', 'Trash');
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['pwajib']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['bmentah']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['bmengkal']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['jjgbhmentah']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['brndlnpersen']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['tpanjang']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['bnginap']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['bbasah']."</td>";
                     $stream.="<td align=center colspan=2".$bgcolor.">".$_SESSION['lang']['sampah']."</td>";
        $stream.="</tr><tr>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            <td".$bgcolor.">KG</td><td".$bgcolor.">%</td>
            </tr>";
        $stream.="</thead>";
        if(($kdPbrk!='')&&($statBuah!='5'))
            {
                    if($statBuah==0)
                    {
                        if($suppId!='')
                        {
                            $add=" and kodecustomer='".$suppId."'";
                        }
                    }
                    elseif($statBuah>0)
                    {
                        if($kdOrg!='')
                        {
                            $add=" and kodeorg='".$kdOrg."'";
                        }
                    }
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."' and intex='".$statBuah."'  ".$add."";
            }
            else if(($kdPbrk!='')&&($statBuah=='5'))
            {
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."'";
            }
            else if(($kdPbrk=='')&&($statBuah!='5'))
            {
                    if($statBuah=='0')
                    {
                        if($suppId!='')
                        {
                            $add=" and kodecustomer='".$suppId."'";
                        }
                    }
                    elseif($statBuah>1)
                    {
                        if($kdOrg!='')
                        {
                            $add=" and kodeorg='".$kdOrg."'";
                        }
                    }
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and intex='".$statBuah."'   ".$add."";
            }
            else if(($kdPbrk=='')&&($statBuah=='5'))
            {
                    $where= "substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."'";
            }
            
            
                 $str="select * from ".$dbname.".pabrik_sortasi where notiket in
                     (select notransaksi
            from ".$dbname.".pabrik_timbangan where ".$where." and kodebarang='40000003')";
                $res=mysql_query($str);
                while($bar=mysql_fetch_object($res))
                {
                    $sortazi[$bar->notiket][$bar->kodefraksi]=$bar->jumlah;
                }
            
        $sql="select *
            from ".$dbname.".pabrik_timbangan where ".$where." and kodebarang='40000003' group by notransaksi  order by `tanggal` asc ";
//        $stream.= "warning".$sql;exit();
//        $stream.= $sql;
        $query=mysql_query($sql) or die(mysql_error());
        $row=mysql_num_rows($query);
        if($row>0)
        {
                while($res=mysql_fetch_assoc($query))
                {
                        $jmlhTndn=$res['jumlahtandan1']+$res['jumlahtandan2']+$res['jumlahtandan3'];
                        if(($jmlhTndn!=0)||($res['jjgsortasi']!=0))
                        {
                            @$jBrt=$res['beratbersih']/$res['jjgsortasi'];
                            @$jBrt2=$res['beratbersih']/$jmlhTndn;
                        }

                        else
                        {
                            $jBrt=0;
                            $jBrt2=0;
                        }
                            $subTotal['beratmasuk']+=$res['beratmasuk'];
                            $subTotal['beratkeluar']+=$res['beratkeluar'];
                            $subTotal['beratbersih']+=$res['beratbersih'];
                            $subTotal['jjgSortasitot']+=$res['jjgsortasi'];
                            $subTotal['prsnBrondolan']+=$res['persenBrondolan'];
                            $subTotal['jmlhTndn']+=$jmlhTndn;
                            //$subTotal['jBrt']+=$jBrt;
                            $subTotal['kgpotsortasi']+=$res['kgpotsortasi'];
                        $no+=1;

                        @$kgpwajib=$sortazi[$res['notransaksi']]['pwajib']*$res['beratbersih']/100;
                        @$kgbmentah=$sortazi[$res['notransaksi']]['bmentah']*$res['beratbersih']/100;
                        @$kgbmengkal=$sortazi[$res['notransaksi']]['bmengkal']*$res['beratbersih']/100;
                        @$kgjjgbhmentah=$sortazi[$res['notransaksi']]['jjgbhmentah']*$res['beratbersih']/100;
                        @$kgbrndlnpersen=$sortazi[$res['notransaksi']]['brndlnpersen']*$res['beratbersih']/100;
                        @$kgtpanjang=$sortazi[$res['notransaksi']]['tpanjang']*$res['beratbersih']/100;
                        @$kgbnginap=$sortazi[$res['notransaksi']]['bnginap']*$res['beratbersih']/100;
                        @$kgbbasah=$sortazi[$res['notransaksi']]['bbasah']*$res['beratbersih']/100;
                        @$kgsampah=$sortazi[$res['notransaksi']]['sampah']*$res['beratbersih']/100;
                        
                        $totkgpwajib+=$kgpwajib;
                        $totkgbmentah+=$kgbmentah;
                        $totkgbmengkal+=$kgbmengkal;
                        $totkgjjgbhmentah+=$kgjjgbhmentah;
                        $totkgbrndlnpersen+=$kgbrndlnpersen;
                        $totkgtpanjang+=$kgtpanjang;
                        $totkgbnginap+=$kgbnginap;
                        $totkgbbasah+=$kgbbasah;
                        $totkgsampah+=$kgsampah;
                        
                        setIt($sortazi[$res['notransaksi']]['jjgbhmentah'],0);
						setIt($sortazi[$res['notransaksi']]['brndlnpersen'],0);
						setIt($sortazi[$res['notransaksi']]['tpanjang'],0);
						setIt($ukuran[$res['kriteriabuah']],'');
                            $stream.="<tr class=rowcontent>
                                    <td>".$no."</td>
                                    <td>".$res['nospb']."</td>
                                    <td>".$res['notransaksi']."</td>
                                    <td>".tanggalnormal($res['tanggal'])."</td>				 
                                    <td>".$res['nokendaraan']."</td>			 		
                                    <td align=right>".number_format($res['beratmasuk'],2)."</td>
                                    <td align=right>".number_format($res['beratkeluar'],2)."</td>
                                    <td align=right>".number_format($res['beratbersih'],2)."</td>
                                    <td align=right>".number_format($jmlhTndn,0)."</td><!--
                                    <td align=right>".number_format($jBrt,2)."</td>
                                    <td align=right>".number_format($res['jjgsortasi'],0)."</td>
                                    <td align=right>".number_format($jBrt2,2)."</td>-->
                                    <td align=right>".number_format($kgpwajib,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['pwajib'],2)."</td>
                                    <td align=right>".number_format($kgbmentah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bmentah'],2)."</td>
                                    <td align=right>".number_format($kgbmengkal,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bmengkal'],2)."</td>
                                    <td align=right>".number_format($kgjjgbhmentah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['jjgbhmentah'],2)."</td>
                                    <td align=right>".number_format($kgbrndlnpersen,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['brndlnpersen'],2)."</td>
                                    <td align=right>".number_format($kgtpanjang,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['tpanjang'],2)."</td>
                                    <td align=right>".number_format($kgbnginap,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bnginap'],2)."</td>
                                    <td align=right>".number_format($kgbbasah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bbasah'],2)."</td>
                                    <td align=right>".number_format($kgsampah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['sampah'],2)."</td>
                                        "; 
        
                                    $stream.="<td align=center>".$ukuran[$res['kriteriabuah']]."</td>";
                                    $stream.="<td align=right>".number_format($res['kgpotsortasi'],2)."</td>";
                            $stream.="	
                            </tr>
                            ";


                }
                 $stream.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']."</td>
                    <td align=right>".number_format($subTotal['beratmasuk'],2)."</td>
                    <td align=right>".number_format($subTotal['beratkeluar'],2)."</td>
                    <td align=right>".number_format($subTotal['beratbersih'],2)."</td>
                    <td align=right>".number_format($subTotal['jmlhTndn'])."</td>
                        ";
                 
                $stream.="<td align=right>".number_format($totkgpwajib,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbmentah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbmengkal,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgjjgbhmentah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbrndlnpersen,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgtpanjang,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbnginap,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbbasah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgsampah,2)."</td>";       
                $stream.="<td align=right></td>";  

                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($subTotal['kgpotsortasi'],2)."</td>";  
                $stream.="</tr>";
//                $subTotal['beratmasuk']=0;
//                $subTotal['beratkeluar']=0;
//                $subTotal['beratbersih']=0;
//                $subTotal['jmlhTndn']=0;
//                //$subTotal['jBrt']=0;
//                $subTotal['jjgSortasitot']=0;
//                $subTotal['prsnBrondolan']=0;
//                $subTotal['kgpotsortasi']=0;
        }
        else
        {
                $pnjng=10+$rNm;
                $stream.="<tr class=rowcontent><td colspan=".$pnjng." align=center>Not Found</td></tr>";
        }
        $stream.="</tbody></table>";       
}

     
            
switch($proses)
{
        case'preview':
echo $stream;
        break;
//        case'pdf':
//
//        $kdPbrk=$_GET['kdPbrk'];
//        $statBuah=$_GET['statBuah'];
//        $tglAkhir=tanggalsystem($_GET['tglAkhir']);
//        $tglAwal=tanggalsystem($_GET['tglAwal']);
//        $thn=substr($tglAwal,0,4);
//        $bln=substr($tglAwal,4,2);
//        $dte=substr($tglAwal,6,2);
//        $tglAwal1=$thn."-".$bln."-".$dte;
//        $thn2=substr($tglAkhir,0,4);
//        $bln2=substr($tglAkhir,4,2);
//        $dte2=substr($tglAkhir,6,2);
//        $tglAkhir1=$thn2."-".$bln2."-".$dte2;
//
//        if(($tglAkhir=='')||($tglAwal==''))
//        {
//                echo"warning:Tanggal Harus Di isi";
//                exit();
//        }
//         class PDF extends FPDF
//        {
//            function Header() {
//                global $conn;
//                global $dbname;
//                global $align;
//                global $length;
//                global $colArr;
//                global $title;
//                global $kdPbrk;
//                global $statBuah;
//                global $tglAkhir;
//                global $tglAwal;
//                global $tglAwal1;
//                global $tglAkhir1;
//                global $suppId;
//                global $statBuah;
//                global $kdOrg;
//                global $namaspl;
//                global $jmlhFraksi;
//                global $kodeFraksi;
//                global $listFraksi2;
//
//
//                            # Alamat & No Telp
//                $query = selectQuery($dbname,'organisasi','alamat,telepon',
//                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
//                $orgData = fetchData($query);
//
//                $width = $this->w - $this->lMargin - $this->rMargin;
//                $height = 20;
//                $path='images/logo.jpg';
//                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
//                $this->SetFont('Arial','B',9);
//                $this->SetFillColor(255,255,255);	
//                $this->SetX(100);   
//                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
//                $this->SetX(100); 		
//                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
//                $this->SetX(100); 			
//                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
//                $this->Line($this->lMargin,$this->tMargin+($height*4),
//                $this->lMargin+$width,$this->tMargin+($height*4));
//                $this->Ln();
//
//                $this->SetFont('Arial','B',6);
//                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanSortasi'],'',0,'L');
//                                $this->Ln();
//                                $this->Ln();
//                                $dari="";
//                                if($statBuah==0)
//                                {
//                                        $sql="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$suppId."'";
//                                        $query=mysql_query($sql) or die(mysql_error($conn));
//                                        $res=mysql_fetch_assoc($query);
//                                        $dari=" : ".$res['namasupplier'];
//                                }
//                                elseif($statBuah!=0||$statBuah!=5)
//                                {
//                                        $sql="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
//                                        //echo $sql;exit();
//                                        $query=mysql_query($sql) or die(mysql_error($conn));
//                                        $res=mysql_fetch_assoc($query);
//                                        $dari=" : ".$res['namaorganisasi'];
//                                        //$where="kodeorg='".$kdOrg."'";
//                                }
//                                else
//                                {
//                                        $dari=$_SESSION['lang']['all'];
//                                }
//                                $this->Cell($width,$height,strtoupper("Rekapitulasi Penerimaan / Penimbangan TBS").$dari,'',0,'C');
//                                $this->Ln();
//                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ". tanggalnormal($tglAwal)." s.d. ".tanggalnormal($tglAkhir),'',0,'C');
//                                $this->Ln();
//                                $this->Ln();
//                $this->SetFont('Arial','B',5);
//                $this->SetFillColor(220,220,220);
//                                $this->Cell(2/100*$width,$height,'No',1,0,'C',1);
//                                $this->Cell(3/100*$width,$height,$_SESSION['lang']['noTiket'],1,0,'C',1);
//                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);
//                                $this->Cell(3/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
//                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['nopol'],1,0,'C',1);	
//                                //$this->Cell(15/100*$width,$height-10,$_SESSION['lang']['hslTimbangan'],1,0,'C',1);
//                                //$this->SetY($this->GetY());
////				$akhirX=$this->GetX();
////				$this->SetX($akhirX+162);
//                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['beratMasuk'],1,0,'C',1);	
//                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['beratkosong'],1,0,'C',1);
//                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);		
//                                //$this->SetY($this->GetY());
//                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
//                                $this->Cell(3/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);
//                                $lbr=3;
//                                $this->SetFont('Arial','B',5);
//                                $this->SetFillColor(220,220,220);
//                                if($_SESSION['language']=='EN'){
//                                    $sFr="select kode,keterangan1 as keterangan,type from ".$dbname.".pabrik_5fraksi order by kode asc";
//                                }else{
//                                    $sFr="select * from ".$dbname.".pabrik_5fraksi order by kode asc";
//                                }
//                                $qFr=mysql_query($sFr) or die(mysql_error());
//                                $row=mysql_num_rows($qFr);
//                                $br=0;
//                                while($rFr=mysql_fetch_assoc($qFr))
//                                {
//                                        $ar=substr($rFr['keterangan'],0,1);
//                                        if(is_numeric($ar))
//                                        {
//                                                if(($ar=='0')||($ar=='5'))
//                                                {
//                                                        $rFr['keterangan']=substr($rFr['keterangan'],0,2);
//                                                }
//                                        }
//                                        else
//                                        {
//                                                if(substr($rFr['keterangan'],0,1)=='B')
//                                                {
//                                                        $as=substr($rFr['keterangan'],0,1);
//                                                        $as2=substr($rFr['keterangan'],5,6)	;
//                                                        $rFr['keterangan']=$as.".".$as2;
//                                                }
//                                                elseif(substr($rFr['keterangan'],0,1)=='T')
//                                                {
//                                                        $as=substr($rFr['keterangan'],0,1);
//                                                        $as2=substr($rFr['keterangan'],7,8);
//                                                        $rFr['keterangan']=$as.".".$as2;
//                                                }
//                                                elseif(substr($rFr['keterangan'],0,1)=='J')
//                                                {
//                                                        $as=substr($rFr['keterangan'],0,1);
//                                                        $as2=substr($rFr['keterangan'],7,9);
//                                                        $rFr['keterangan']=$as.".".$as2;
//                                                }
//                                                elseif((substr($rFr['keterangan'],0,1)=='L')||(substr($rFr['keterangan'],0,1)=='l'))
//                                                {
//                                                        $as="+";
//                                                        $as2=substr($rFr['keterangan'],6,9);
//                                                        $rFr['keterangan']=$as.$as2;
//                                                }
//
//                                        }
//                                        if($rFr['kode']=='L')
//                                        {
//                                            $lbr='4';
//                                        }
//                                        if($rFr['kode']=='A'||$rFr['kode']=='B')
//                                        {
//                                            $this->Cell(6/100*$width,10,$rFr['keterangan'],1,$r,'C',1);	
//                                        }
//                                        else
//                                        {
//                                            $this->Cell($lbr/100*$width,$height,$rFr['keterangan'],1,$r,'C',1);	
//                                        }
//
//                                }
//                                $this->SetFont('Arial','B',5);
//                                $this->SetFillColor(220,220,220);
//                                $this->Cell(4/100*$width,$height,"JJG". $_SESSION['lang']['sortasi'],1,0,'C',1);
//                                $this->Cell(4/100*$width,$height,"% ".$_SESSION['lang']['brondolan'],1,1,'C',1);
//                                $gy=$this->GetY();
//                                $gx=$this->GetX();
//                                $this->SetY($gy-10);
//                                $this->SetX($gx+361.5);
//                                $this->Cell(3/100*$width,10,"KG",1,0,'C',1);
//                                $this->Cell(3/100*$width,10,"%",1,0,'C',1);
//                                $this->Cell(3/100*$width,10,"KG",1,0,'C',1);
//                                $this->Cell(3/100*$width,10,"%",1,1,'C',1);
//
//                                //$this->Ln();
//            }
//
//            function Footer()
//            {
//                $this->SetY(-15);
//                $this->SetFont('Arial','I',7);
//                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
//            }
//        }
//        $pdf=new PDF('L','pt','Legal');
//        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
//        $height = 12;
//                $pdf->AddPage();
//                $pdf->SetFillColor(255,255,255);
//                $pdf->SetFont('Arial','',5);
//
//                if(($kdPbrk!='')&&($statBuah!='5'))
//            {
//                    if($statBuah==0)
//                    {
//                        if($suppId!='')
//                        {
//                            $add=" and kodecustomer='".$suppId."'";
//                        }
//                    }
//                    elseif($statBuah>0)
//                    {
//                        if($kdOrg!='')
//                        {
//                            $add=" and kodeorg='".$kdOrg."'";
//                        }
//                    }
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."' and intex='".$statBuah."'  ".$add."";
//            }
//            else if(($kdPbrk!='')&&($statBuah=='5'))
//            {
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."'";
//            }
//            else if(($kdPbrk=='')&&($statBuah!='5'))
//            {
//                    if($statBuah=='0')
//                    {
//                        if($suppId!='')
//                        {
//                            $add=" and kodecustomer='".$suppId."'";
//                        }
//                    }
//                    elseif($statBuah>1)
//                    {
//                        if($kdOrg!='')
//                        {
//                            $add=" and kodeorg='".$kdOrg."'";
//                        }
//                    }
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and intex='".$statBuah."'   ".$add."";
//            }
//            else if(($kdPbrk=='')&&($statBuah=='5'))
//            {
//                    $where= "substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."'";
//            }
//            $sMax="select notiket,kodefraksi,jumlah from ".$dbname.".pabrik_sortasi_vw where jumlah!=0 and ".$where." order by kodefraksi asc";
//            //exit("error".$sMax);
//            $qMax=fetchData($sMax);
//            foreach($qMax as $brsMax => $rMax)
//            {
//                $jmlhFraksi[$rMax['notiket']][$rMax['kodefraksi']]=$rMax['jumlah'];
//            }
//                $i=0;
//                $subTotal=array();
//                $sql="select notransaksi,tanggal,nokendaraan,beratmasuk,beratkeluar,beratbersih,nospb,`jumlahtandan1` , `jumlahtandan2` , `jumlahtandan3`,a.jjgsortasi,a.persenBrondolan,a.kgpotsortasi
//            from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pabrik_sortasi b on a.notransaksi=b.notiket where ".$where." and b.jumlah!=0 and kodebarang='40000003' group by notransaksi,notiket  order by `tanggal` asc ";
//                $qDet=mysql_query($sql) or die(mysql_error());
//                while($res=mysql_fetch_assoc($qDet))
//                {                   
//                            $jmlhTndn=$res['jumlahtandan1']+$res['jumlahtandan2']+$res['jumlahtandan3'];
//                            if($jmlhTndn!=0)
//                            {
//                                $jBrt=$res['beratbersih']/$jmlhTndn;
//                            }
//                            else
//                            {
//                                $jBrt=0;
//                            }
//                            $subTotal['beratmasuk']+=$res['beratmasuk'];
//                            $subTotal['beratkeluar']+=$res['beratkeluar'];
//                            $subTotal['beratbersih']+=$res['beratbersih'];
//                            $subTotal['jjgSortasitot']+=$res['jjgsortasi'];
//                            $subTotal['prsnBrondolan']+=$res['persenBrondolan'];
//                            $subTotal['jmlhTndn']+=$jmlhTndn;
//                            $subTotal['jBrt']+=$jBrt;
//                            //$subTotal['beratmasuk']+=$res['beratmasuk'];
//                            $no+=1;
//                            $i++;
//                            $pdf->Cell(2/100*$width,$height,$no,1,0,'C',1);
//                            $pdf->Cell(3/100*$width,$height,$res['notransaksi'],1,0,'C',1);	
//                            $pdf->Cell(7/100*$width,$height,$res['nospb'],1,0,'L',1);	
//                            $pdf->Cell(3/100*$width,$height,tanggalnormal($res['tanggal']),1,0,'C',1);		
//                            $pdf->Cell(4/100*$width,$height,$res['nokendaraan'],1,0,'L',1);		
//                            $pdf->Cell(4/100*$width,$height,number_format($res['beratmasuk'],2),1,0,'R',1);		
//                            $pdf->Cell(4/100*$width,$height,number_format($res['beratkeluar'],2),1,0,'R',1);
//                            $pdf->Cell(4/100*$width,$height,number_format($res['beratbersih'],2),1,0,'R',1);	
//                            $pdf->Cell(4/100*$width,$height,number_format($jmlhTndn,2),1,0,'R',1);	
//                            $pdf->Cell(3/100*$width,$height,number_format($jBrt,2),1,0,'R',1);	
//                            $j=1;
//                            $lbr=3;
//    //			while($rFraksi=mysql_fetch_assoc($qFraksi2))
//                          foreach($kodeFraksi as $brsKdFraksi =>$listFraksi)
//                            {
//                              if($listFraksi['kode']=='L')
//                              {
//                                $lbr='4';
//                              }
//                              if($listFraksi['kode']=='D'||$listFraksi['kode']=='F')
//                              {
//                                  $jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']];
//                              }   
//                              else
//                              {
//                                $jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]*$jBrt;
//                              }
//                              if($listFraksi['kode']=='A'||$listFraksi['kode']=='B')
//                              {
//                                  @$prsenDt[$res['notransaksi']][$listFraksi['kode']]=($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]/$res['beratbersih'])*100;
//                                  $pdf->Cell(3/100*$width,$height,number_format($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']],2),1,0,'R',1);				
//                                  $pdf->Cell(3/100*$width,$height,number_format($prsenDt[$res['notransaksi']][$listFraksi['kode']],2),1,0,'R',1);				
//                              }
//                              else
//                              {
//                                  $pdf->Cell($lbr/100*$width,$height,number_format($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']],2),1,0,'R',1);				
//                              }
//
//                              $subTotal['fraksi'.$j]+=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']];
//                              $j++;
//                            }
//
//                             $pdf->Cell(4/100*$width,$height,number_format($res['jjgsortasi'],2),1,0,'R',1);
//                             $pdf->Cell(4/100*$width,$height,number_format($res['persenBrondolan'],2),1,0,'R',1);
//
//                        $pdf->Ln();
//                        if($i==20)
//                        {
//
//                                $pdf->AddPage();
//
//                         }
//                }
//                $pdf->Cell(19/100*$width,$height,"Sub Total",1,0,'R',1);
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['beratmasuk'],2),1,0,'R',1);		
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['beratkeluar'],2),1,0,'R',1);
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['beratbersih'],2),1,0,'R',1);	
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['jmlhTndn'],2),1,0,'R',1);	
//                $pdf->Cell(3/100*$width,$height,number_format($subTotal['jBrt'],2),1,0,'R',1);	
//                for($k=1;$k<$j;$k++)
//                {
//                     if($k==1||$k==2)
//                     {
//                      $pdf->Cell(3/100*$width,$height,number_format($subTotal['fraksi'.$k],2),1,0,'R',1);	  
//                      $pdf->Cell(3/100*$width,$height,"",1,0,'R',1);	  
//                     }
//                     elseif($k==12||$k==13)
//                     {
//                         $pdf->Cell(4/100*$width,$height,number_format($subTotal['fraksi'.$k],2),1,0,'R',1);	  
//                     }
//                     else
//                     {
//                          $pdf->Cell(3/100*$width,$height,number_format($subTotal['fraksi'.$k],2),1,0,'R',1);	
//                     }
//                }
//
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['jjgSortasitot'],2),1,0,'R',1);
//                $pdf->Cell(4/100*$width,$height,number_format($subTotal['prsnBrondolan'],2),1,0,'R',1);
//
//        $pdf->Output();
//        break;
        case'excel':

//        $kdPbrk=$_GET['kdPbrk'];
//        $statBuah=$_GET['statBuah'];
//        $tglAkhir=tanggalsystem($_GET['tglAkhir']);
//        $tglAwal=tanggalsystem($_GET['tglAwal']);
//         $thn=substr($tglAwal,0,4);
//        $bln=substr($tglAwal,4,2);
//        $dte=substr($tglAwal,6,2);
//        $tglAwal1=$thn."-".$bln."-".$dte;
//        $thn2=substr($tglAkhir,0,4);
//        $bln2=substr($tglAkhir,4,2);
//        $dte2=substr($tglAkhir,6,2);
//        $tglAkhir1=$thn2."-".$bln2."-".$dte2;
//        $suppId=$_GET['suppId'];
//        $kdOrg=$_GET['kdOrg'];
//            $str="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$suppId."'";
//            $res=mysql_query($str);
//            while($bar=mysql_fetch_object($res))
//            {
//                $namaspl=$bar->namasupplier;
//            }
//            $statBuah=='0'?'':$namaspl=$kdOrg;
//
//        if(($tglAkhir=='')||($tglAwal==''))
//        {
//                echo"warning:Tanggal Harus Di isi";
//                exit();
//        }
//        if(($kdPbrk!='')&&($statBuah!='5'))
//                {
//                        if($statBuah==0)
//                        {
//                            if($suppId!='')
//                            {
//                                $add=" and kodecustomer='".$suppId."'";
//                            }
//                        }
//                        elseif($statBuah!=0)
//                        {
//                           if($kdOrg!='')
//                           {
//                               $add=" and kodeorg='".$kdOrg."'";
//                           }
//                        }
//                        $where="substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."' and intex='".$statBuah."' ".$add."";
//                        //$where="tanggal between '".$tglAwal."' and '".$tglAkhir."' and millcode='".$kdPbrk."' and intex='".$statBuah."'";
//                }
//                elseif(($kdPbrk!='')&&($statBuah=='5'))
//                {
//                        $where="substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."'";
//                }
//                elseif(($kdPbrk=='')&&($statBuah!='5'))
//                {
//                        if($statBuah==0)
//                        {
//                            if($suppId!='')
//                            {
//                                $add=" and kodecustomer='".$suppId."'";
//                            }
//                        }
//                        elseif($statBuah!=0)
//                        {
//                            if($kdOrg!='')
//                            {
//                                $add=" and kodeorg='".$kdOrg."'";
//                            }
//                        }
//                                $where="substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and intex='".$statBuah."'   ".$add."";
//                        //$where="tanggal between '".$tglAwal."' and '".$tglAkhir."' and intex='".$statBuah."'";
//                }
//                elseif(($kdPbrk=='')&&($statBuah==5))
//                {
//                        $where="substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."'";
//                }
//                    $sMax="select notiket,kodefraksi,jumlah from ".$dbname.".pabrik_sortasi_vw where jumlah!=0 and ".$where." order by kodefraksi asc";
//            //exit("error".$sMax);
//            $qMax=fetchData($sMax);
//            foreach($qMax as $brsMax => $rMax)
//            {
//                $jmlhFraksi[$rMax['notiket']][$rMax['kodefraksi']]=$rMax['jumlah'];
//            }
//                $sFr="select * from ".$dbname.".pabrik_5fraksi order by kode asc";
//                $qFr=mysql_query($sFr) or die(mysql_error());
//                $rNm=mysql_num_rows($qFr);
//
//                $colspand=count($kodeFraksi);	
//                        $stream.="
//                        <table>
//                        <tr><td colspan=13 align=center>Laporan Sortasi PMKS ".$kdPbrk."  ".$namaspl." periode :".$_GET['tglAwal']."-".$_GET['tglAkhir']."</td></tr>
//                        <tr><td colspan=3></td><td></td></tr>
//                        </table>";
//                        $stream.="<table cellpadding=1 cellspacing=1 border=1 class=sortable width=100%>";
//                    $stream.="<thead><tr class=rowheader>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>No.</td>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>".$_SESSION['lang']['nospb']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>".$_SESSION['lang']['noTiket']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>".$_SESSION['lang']['tanggal']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>".str_replace(" ","<br>",$_SESSION['lang']['nopol'])."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center  colspan=3 valign=middle>".$_SESSION['lang']['hslTimbangan']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE rowspan=3>".str_replace(" ","<br>",$_SESSION['lang']['jmlhTandan'])."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center rowspan=3 valign=middle>JJG ".$_SESSION['lang']['sortasi']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['sortasi']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center  valign=middle colspan=".($colspand+2).">".$_SESSION['lang']['hslSortasi']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center rowspan=3 valign=middle>% ".$_SESSION['lang']['brondolan']."</td>";
//                    $stream.="<td  bgcolor=#DEDEDE align=center rowspan=3 valign=middle>".$_SESSION['lang']['potongan']."(Kg)</td></tr>";
//                    $stream.="<tr>
//                         <td  bgcolor=#DEDEDE align=center rowspan=2  valign=middle>".$_SESSION['lang']['beratMasuk']."</td>
//                         <td  bgcolor=#DEDEDE align=center rowspan=2  valign=middle>".$_SESSION['lang']['beratkosong']."</td>
//                         <td  bgcolor=#DEDEDE align=center rowspan=2  valign=middle>".$_SESSION['lang']['beratBersih']."</td>";
//                   foreach ($kodeFraksi as $barisFraksi => $rFr)
//                    {
//                            $ar=substr($nmKeterangan[$rFr['kode']],0,1);
//                            if(is_numeric($ar))
//                            {
//                                    if(($ar=='0')||($ar=='5'))
//                                    {
//                                            $nmKeterangan[$rFr['kode']]=substr($nmKeterangan[$rFr['kode']],0,2);
//                                    }
//                            }
//                            if(($rFr['kode']=='A') ||($rFr['kode']=='B'))
//                            {
//                                 $stream.="<td  bgcolor=#DEDEDE align=center colspan=2>".$nmKeterangan[$rFr['kode']]."</td>";
//                            }
//
//                            else
//                            {
//                                 $stream.="<td  bgcolor=#DEDEDE align=center rowspan=2 >".$nmKeterangan[$rFr['kode']]."</td>";
//                            }
//                    }
//                    $stream.="</tr><tr><td bgcolor=#DEDEDE >KG</td><td bgcolor=#DEDEDE >%</td><td bgcolor=#DEDEDE >KG</td><td bgcolor=#DEDEDE >%</td></tr>";
//                    $stream.="</thead><tbody>";
//
//
//        if(($kdPbrk!='')&&($statBuah!='5'))
//            {
//                    if($statBuah==0)
//                    {
//                        if($suppId!='')
//                        {
//                            $add=" and kodecustomer='".$suppId."'";
//                        }
//                    }
//                    elseif($statBuah>0)
//                    {
//                        if($kdOrg!='')
//                        {
//                            $add=" and kodeorg='".$kdOrg."'";
//                        }
//                    }
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."' and intex='".$statBuah."'  ".$add."";
//            }
//            else if(($kdPbrk!='')&&($statBuah=='5'))
//            {
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."'";
//            }
//            else if(($kdPbrk=='')&&($statBuah!='5'))
//            {
//                    if($statBuah=='0')
//                    {
//                        if($suppId!='')
//                        {
//                            $add=" and kodecustomer='".$suppId."'";
//                        }
//                    }
//                    elseif($statBuah>1)
//                    {
//                        if($kdOrg!='')
//                        {
//                            $add=" and kodeorg='".$kdOrg."'";
//                        }
//                    }
//                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and intex='".$statBuah."'   ".$add."";
//            }
//            else if(($kdPbrk=='')&&($statBuah=='5'))
//            {
//                    $where= "substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."'";
//            }
//            $sMax="select notiket,kodefraksi,jumlah from ".$dbname.".pabrik_sortasi_vw where jumlah!=0 and ".$where." order by kodefraksi asc";
//            //exit("error".$sMax);
//            $qMax=fetchData($sMax);
//            foreach($qMax as $brsMax => $rMax)
//            {
//                $jmlhFraksi[$rMax['notiket']][$rMax['kodefraksi']]=$rMax['jumlah'];
//            }
//        $sql="select notransaksi,tanggal,nokendaraan,beratmasuk,beratkeluar,beratbersih,nospb,`jumlahtandan1` , `jumlahtandan2` , `jumlahtandan3`,a.jjgsortasi,a.persenBrondolan,a.kgpotsortasi
//            from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pabrik_sortasi b on a.notransaksi=b.notiket where ".$where." and b.jumlah!=0 and kodebarang='40000003' group by notransaksi,notiket  order by `tanggal` asc ";
//        //echo "warning".$sql;exit();
//        //echo $sql;
//        $query=mysql_query($sql) or die(mysql_error());
//        $row=mysql_num_rows($query);
//        if($row>0)
//        {
//                while($res=mysql_fetch_assoc($query))
//                {
//                        $jmlhTndn=$res['jumlahtandan1']+$res['jumlahtandan2']+$res['jumlahtandan3'];
//                        if(($jmlhTndn!=0)||($res['jjgsortasi']!=0))
//                        {
//                            @$jBrt=$res['beratbersih']/$res['jjgsortasi'];
//                            @$jBrt2=$res['beratbersih']/$jmlhTndn;
//                        }
//
//                        else
//                        {
//                            $jBrt=0;
//                            $jBrt2=0;
//                        }
//                            $subTotal['beratmasuk']+=$res['beratmasuk'];
//                            $subTotal['beratkeluar']+=$res['beratkeluar'];
//                            $subTotal['beratbersih']+=$res['beratbersih'];
//                            $subTotal['jjgSortasitot']+=$res['jjgsortasi'];
//                            $subTotal['prsnBrondolan']+=$res['persenBrondolan'];
//                            $subTotal['jmlhTndn']+=$jmlhTndn;
//                            //$subTotal['jBrt']+=$jBrt;
//                            $subTotal['kgpotsortasi']+=$res['kgpotsortasi'];
//                        $no+=1;
//
//                            $stream.="<tr class=rowcontent>
//                                    <td>".$no."</td>
//                                    <td>".$res['nospb']."</td>
//                                    <td>".$res['notransaksi']."</td>
//                                    <td>".tanggalnormal($res['tanggal'])."</td>				 
//                                    <td>".$res['nokendaraan']."</td>			 		
//                                    <td align=right>".number_format($res['beratmasuk'],2)."</td>
//                                    <td align=right>".number_format($res['beratkeluar'],2)."</td>
//                                    <td align=right>".number_format($res['beratbersih'],2)."</td>
//                                    <td align=right>".number_format($jmlhTndn,0)."</td>
//                                    <td align=right>".number_format($jBrt,2)."</td>
//                                    <td align=right>".number_format($res['jjgsortasi'],0)."</td>
//                                    <td align=right>".number_format($jBrt2,2)."</td>";
//                                    foreach($kodeFraksi as $brsKdFraksi =>$listFraksi)
//                                    {
//                                         if($listFraksi['kode']=='D'||$listFraksi['kode']=='F')
//                                          {
//                                              $jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']];
//                                          }   
//                                          else if($listFraksi['kode']=='A'||$listFraksi['kode']=='B')
//                                          {
//                                            $jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]*$jBrt2;
//                                            @$persendt[$res['notransaksi']][$listFraksi['kode']]=($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']]/$res['beratbersih'])*100;
//                                          }
//                                        if(($listFraksi['kode']=='A')||($listFraksi['kode']=='B'))
//                                        {
//                                            $stream.="<td width=60 align=right>".number_format($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']],2)."</td>";
//                                            $stream.="<td width=60 align=right>".number_format($persendt[$res['notransaksi']][$listFraksi['kode']],2)."</td>";
//                                        }
//                                        else
//                                        {
//                                            $stream.="<td width=60 align=right>".number_format($jmlhFraksi[$res['notransaksi']][$listFraksi['kode']],2)."</td>";
//                                        }
//                                        $subTotal[$listFraksi['kode']]+=$jmlhFraksi[$res['notransaksi']][$listFraksi['kode']];
//                                        $j++;
//
//                                    }
//
//                                    $stream.="<td align=right>".number_format($res['persenBrondolan'],2)."</td>";
//                                    $stream.="<td align=right>".number_format($res['kgpotsortasi'],2)."</td>";
//                            $stream.="	
//                            </tr>
//                            ";
//
//
//                }
//                 $stream.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']."</td>
//                    <td align=right>".number_format($subTotal['beratmasuk'],2)."</td>
//                    <td align=right>".number_format($subTotal['beratkeluar'],2)."</td>
//                    <td align=right>".number_format($subTotal['beratbersih'],2)."</td>
//                    <td align=right>".number_format($subTotal['jmlhTndn'],2)."</td>
//                    <td align=right>&nbsp;</td>
//                    <td align=right>".number_format($subTotal['jjgSortasitot'],2)."</td>
//                    <td align=right>&nbsp;</td>
//                        ";
//
//                $sFraksi="select kode from ".$dbname.".pabrik_5fraksi order by kode asc";
//                $qFraksi=mysql_query($sFraksi) or die(mysql_error());
//                while($rFraksi=mysql_fetch_assoc($qFraksi))
//                {    
//                    if($rFraksi['kode']=='A'||$rFraksi['kode']=='B')
//                    {
//                         $stream.="<td align=right>".number_format($subTotal[$rFraksi['kode']],2)."</td>";	
//                         $stream.="<td align=right>&nbsp;</td>";	
//                    }
//                    else
//                    {
//                         $stream.="<td align=right>".number_format($subTotal[$rFraksi['kode']],2)."</td>";	
//                    }
//                      $subTotal[$rFraksi['kode']]=0;
//                }
//
//                $stream.="<td align=right>".number_format($subTotal['prsnBrondolan'],2)."</td>";  
//                $stream.="<td align=right>".number_format($subTotal['kgpotsortasi'],2)."</td>";  
//                $stream.="</tr>";
//                $subTotal['beratmasuk']=0;
//                $subTotal['beratkeluar']=0;
//                $subTotal['beratbersih']=0;
//                $subTotal['jmlhTndn']=0;
//                //$subTotal['jBrt']=0;
//                $subTotal['jjgSortasitot']=0;
//                $subTotal['prsnBrondolan']=0;
//                $subTotal['kgpotsortasi']=0;
//        }
//        else
//        {
//                $pnjng=10+$rNm;
//                $stream.="<tr class=rowcontent><td colspan=".$pnjng." align=center>Not Found</td></tr>";
//        }
//        $stream.="</tbody></table>";


                        //echo "warning:".$strx;
                        //=================================================
                $stream.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
                        $tglSkrg=date("Ymd");
                        $nop_="Laporan Sortasi 2_".$tglSkrg;
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
//        case'getDetail':
//        echo"<link rel=stylesheet type=text/css href=style/generic.css>";
//        $nokontrak=$_GET['nokontrak'];
//        $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
//        $qHead=mysql_query($sHed) or die(mysql_error());
//        $rHead=mysql_fetch_assoc($qHead);
//        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
//        $qBrg=mysql_query($sBrg) or die(mysql_error());
//        $rBrg=mysql_fetch_assoc($qBrg);
//
//        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
//        $qCust=mysql_query($sCust) or die(mysql_error());
//        $rCust=mysql_fetch_assoc($qCust);
//        echo"<fieldset><legend>".$_SESSION['lang']['detailPengiriman']."</legend>
//        <table cellspacing=1 border=0 class=myinputtext>
//        <tr>
//                <td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td>".$nokontrak."</td>
//        </tr>
//        <tr>
//                <td>".$_SESSION['lang']['tglKontrak']."</td><td>:</td><td>".tanggalnormal($rHead['tanggalkontrak'])."</td>
//        </tr>
//        <tr>
//                <td>".$_SESSION['lang']['komoditi']."</td><td>:</td><td>".$rBrg['namabarang']."</td>
//        </tr>
//        <tr>
//                <td>".$_SESSION['lang']['Pembeli']."</td><td>:</td><td>".$rCust['namacustomer']."</td>
//        </tr>
//        </table><br />
//        <table cellspacing=1 border=0 class=sortable><thead>
//        <tr class=data>
//        <td>".$_SESSION['lang']['notransaksi']."</td>
//        <td>".$_SESSION['lang']['tanggal']."</td>
//        <td>".$_SESSION['lang']['nodo']."</td>
//        <td>".$_SESSION['lang']['nosipb']."</td>
//        <td>".$_SESSION['lang']['beratBersih']."</td>
//        <td>".$_SESSION['lang']['kodenopol']."</td>
//        <td>".$_SESSION['lang']['sopir']."</td>
//        </tr></thead><tbody>
//        ";
///*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
//*/	
//
//        $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."'";
//        $qDet=mysql_query($sDet) or die(mysql_error());
//        $rCek=mysql_num_rows($qDet);
//        if($rCek>0)
//        {
//                while($rDet=mysql_fetch_assoc($qDet))
//                {
//                        echo"<tr class=rowcontent>
//                        <td>".$rDet['notransaksi']."</td>
//                        <td>".tanggalnormal($rDet['tanggal'])."</td>
//                        <td>".$rDet['nodo']."</td>
//                        <td>".$rDet['nosipb']."</td>
//                        <td align=right>".number_format($rDet['beratbersih'],2)."</td>
//                        <td>".$rDet['nokendaraan']."</td>
//                        <td>".ucfirst($rDet['supir'])."</td>
//                        </tr>";
//                }
//        }
//        else
//        {
//                echo"<tr><td colspan=7>Not Found</td></tr>";
//        }
//        echo"</tbody></table></fieldset>";
//
//        break;
        case'getkbn':
               // $optkdOrg2="<option value=''></option value=''>".$_SESSION['lang']['all']."</option>";
            if($kdPbrk=='')
            {
                exit("Error: Mill code required");
            }

                if($BuahStat==0)
                {
                        $optkdOrg2.="<option value=''>".$_SESSION['lang']['all']."</option>";
                        $sOrg="SELECT namasupplier,supplierid,kodetimbangan FROM ".$dbname.".log_5supplier WHERE substring(kodekelompok,1,1)='S' and kodetimbangan is not null";//echo "warning:".$sOrg;exit();
                        $qOrg=mysql_query($sOrg) or die(mysql_error());
                        while($rOrg=mysql_fetch_assoc($qOrg))
                        {
                                $optkdOrg2.="<option value=".$rOrg['kodetimbangan']."".($rOrg['kodetimbangan']==$idCust?'selected':'').">".$rOrg['namasupplier']."</option>";
                        }
                        //echo"warning:test";
                        echo $optkdOrg2."###".$BuahStat;exit();
                }
                elseif($BuahStat==5)
                {
                    $optkdOrg2.="<option value=''>".$_SESSION['lang']['all']."</option>";
                    echo $optkdOrg2."###".$BuahStat;exit();
                }
                elseif($BuahStat==1)
                {
                    $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and kodeorganisasi in(select distinct kodeorg from ".$dbname.".pabrik_timbangan where intex='".$BuahStat."' and millcode='".$kdPbrk."')";//echo "warning:".$sOrg;
                        //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK')";//echo "warning:".$sOrg;
                }
                elseif($BuahStat==2)
                {
                    $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and kodeorganisasi in(select distinct kodeorg from ".$dbname.".pabrik_timbangan where intex='".$BuahStat."'  and millcode='".$kdPbrk."')";//echo "warning:".$sOrg;
                        //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK')"; //echo "warning:".$sOrg;
                }
                $optkdOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
                $qOrg=mysql_query($sOrg) or die(mysql_error());
                while($rOrg=mysql_fetch_assoc($qOrg))
                {
                        $optkdOrg.="<option value=".$rOrg['kodeorganisasi']."".($rOrg['kodeorganisasi']==$kdKbn?'selected':'').">".$rOrg['namaorganisasi']."</option>";
                }

                echo $optkdOrg."###".$BuahStat;
                break;

        break;
}

?>