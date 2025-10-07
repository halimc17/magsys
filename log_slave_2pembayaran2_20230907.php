<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
        $proses=$_POST['proses'];
}
else
{
        $proses=$_GET['proses'];
}

$optSupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
$optNmOrg=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
$optKurs=makeOption($dbname, 'log_poht','nopo,kurs');
//$arr2="##tgl_cari##tgl_cari2##jenisId2##kdUnit2##cariNopo##suppId2";
$tgl_cari = 	empty($_POST['tgl_cari'])? (isset($_GET['tgl_cari'])? tanggalsystem($_GET['tgl_cari']): ''): tanggalsystem($_POST['tgl_cari']);
$tgl_cari2 = 	empty($_POST['tgl_cari2'])? (isset($_GET['tgl_cari2'])? tanggalsystem($_GET['tgl_cari2']): ''): tanggalsystem($_POST['tgl_cari2']);
$kdUnit = 		empty($_POST['kdUnit2'])? (isset($_GET['kdUnit2'])? $_GET['kdUnit2']: ''): $_POST['kdUnit2'];
$jenisId2 = 	(!isset($_POST['jenisId2']) or $_POST['jenisId2']=='')? (isset($_GET['jenisId2'])? $_GET['jenisId2']: ''): $_POST['jenisId2'];
$cariNopo = 	empty($_POST['cariNopo'])? (isset($_GET['cariNopo'])? $_GET['cariNopo']: ''): $_POST['cariNopo'];
$suppId2 = 		empty($_POST['suppId2'])? (isset($_GET['suppId2'])? $_GET['suppId2']: ''): $_POST['suppId2'];
$unitId=$_SESSION['lang']['all'];
$dktlmpk=$_SESSION['lang']['all'];
if($tgl_cari2!='')
{
    if($tgl_cari=='')
    {
        exit("Error: Date required");
    }

    $sSel="SELECT datediff('".$tgl_cari2."','".$tgl_cari."') as selisih";
    $qSel=mysql_query($sSel) or die(mysql_error($conn));
    $rSel=mysql_fetch_array($qSel);
    if($rSel['selisih']<0)
    {
        exit("Error: Date required");
    }
    $whre=" and a.tanggal between '".$tgl_cari."' and  '".$tgl_cari2."'";
    $whre2=" and tanggal between '".$tgl_cari."' and  '".$tgl_cari2."'";
//    $rhd=" and left(b.tanggal,4)='".substr($tgl_cari,0,4)."'";
    $rhd=" and c.tanggal between '".$tgl_cari."' and  '".$tgl_cari2."'";
}
else if($tgl_cari!='')
{
    $whre=" and a.tanggal='".$tgl_cari."'";
    $whre2=" and tanggal='".$tgl_cari."'";
}

if($kdUnit!='')
{
     $unitId=$optNmOrg[$kdUnit];
     $whre.=" and a.kodeorg='".$kdUnit."'";
     $whre2.=" and kodeorg='".$kdUnit."'";
}

if($jenisId2!='')
{
    $jenisId2=='0'?$dr="k":$dr="p";
    $whre.=" and a.tipeinvoice like '%".$dr."%'";
    $whre2.=" and tipeinvoice like '%".$dr."%'";
}

if($suppId2!='')
{
    $whre.=" and a.kodesupplier='".$suppId2."'";
    $whre2.=" and kodesupplier='".$suppId2."'";
}
if($cariNopo!='')
{
    $whre.=" and a.nopo like '%".$cariNopo."%'";
    $whre2.=" and nopo like '%".$cariNopo."%'";
}

if($proses=='preview'||$proses=='excel')
{
    if($jenisId2=='')
    {
        exit("Error: Transaction type required");
    }

    $sTagi="select sum(a.nilaiinvoice) as jumlah,sum(c.nilai) as jumlahppn,a.kodesupplier as kodesupplier
           from ".$dbname.".keu_tagihanht a 
           left join ".$dbname.".log_poht b on a.nopo=b.nopo
		   left join ".$dbname.".keu_tagihandt c on a.noinvoice=c.noinvoice
           where a.posting=1 and a.tanggal!=''  ".$whre." 
           group by a.kodesupplier";    
//    exit("Warning: ".$sTagi);
    $qTagi=mysql_query($sTagi) or die(mysql_error($conn));
    while($rTagi=  mysql_fetch_assoc($qTagi))
    {
        if($rTagi['kodesupplier']!=''){
        $dtTagih[$rTagi['kodesupplier']]=$rTagi['jumlah']+$rTagi['jumlahppn'];
        $dtSupp[$rTagi['kodesupplier']]=$rTagi['kodesupplier'];
        }
    }
   
    $sByr="select sum(a.jumlah) as jumlah,c.kodesupplier as kodesupplier
         from ".$dbname.". keu_kasbankdt a
         left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
         left join ".$dbname.".keu_tagihanht c on c.noinvoice=a.keterangan1
         where a.tipetransaksi='K' and b.posting=1 and a.keterangan1 in
         (select noinvoice from ".$dbname.". keu_tagihanht 
         where posting=1 and tanggal!=''  ".$whre2.") 
		 group by c.kodesupplier";
    
//     exit("Error:".$sByr);
$qByr=mysql_query($sByr) or die(mysql_error($conn));
while($rByr=mysql_fetch_assoc($qByr)){
    $penambah[$rByr['kodesupplier']]=$rByr['jumlah'];
    //$ntrKasBank[$rByr['keterangan1']]=$rByr['notransaksi'];
    //$tglKasBank[$rByr['keterangan1']]=$rByr['tanggal'];
}
$sByr2="select sum(a.jumlah) as jumlah,c.kodesupplier as kodesupplier
        from ".$dbname.". keu_kasbankdt a
        left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
        left join ".$dbname.".keu_tagihanht c on c.noinvoice=a.keterangan1    
        where a.tipetransaksi='M' and b.posting=1 and a.keterangan1 in
        (select noinvoice from ".$dbname.". keu_tagihanht 
        where posting=1 and tanggal!=''  ".$whre2.") 
		group by c.kodesupplier";
//exit("Error:".$sByr2);
$qByr2=mysql_query($sByr2) or die(mysql_error($conn));
while($rByr2=mysql_fetch_assoc($qByr2)){
    $pengurang[$rByr2['kodesupplier']]=$rByr2['jumlah'];
}
$cekdt=count($dtSupp);
if($cekdt==0)
{
    exit("Error: No data found");
}
$brdr=0;
$bgcoloraja='';
if($proses=='excel')
{
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $tab="
    <table>
    <tr><td colspan=15 align=left><b><font size=5>Riwayat Pembayaran</font></b></td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['pt']." : ".$unitId."</td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['periode']." : ".substr($tgl_cari,0,4)."</td></tr>
    </table>";
}

	$tab="<table cellspacing=1 border=".$brdr." class=sortable><thead class=rowheader>";
	$tab.="<tr>";
	$tab.="<td ".$bgcoloraja."align=center>No.</td>";
	$tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['namasupplier']."</td>";
	$tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['total']." ".$_SESSION['lang']['tagihan']."</td>";
	$tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['total']." ".$_SESSION['lang']['dibayar']."</td>";
	$tab.="<td ".$bgcoloraja." align=center>".$_SESSION['lang']['sisa']."</td></tr>";
	$tab.="</tr></thead><tbody>";
    if(!empty($dtSupp)) 
    {
		$aerta=0;
        foreach($dtSupp as $hutang)
        {
            $aerta+=1;
			if(!isset($penambah[$hutang])) $penambah[$hutang]=0;
			if(!isset($pengurang[$hutang])) $pengurang[$hutang]=0;
            $dibayarsmp[$hutang]=$penambah[$hutang]-$pengurang[$hutang];
            $sis[$hutang]=$dtTagih[$hutang]-$dibayarsmp[$hutang];
            $tab.="<tr class=rowcontent style='cursor:pointer;' onclick=detailpembayaran('".$tgl_cari."','".$tgl_cari2."','".$jenisId2."','".$kdUnit."','".$dtSupp[$hutang]."')>";
            $tab.="<td>".$aerta."</td>";
            $tab.="<td>".$optSupp[$dtSupp[$hutang]]."</td>";
            $tab.="<td align=right>".number_format($dtTagih[$hutang],0)."</td>";
            $tab.= "<td align=right>".number_format($dibayarsmp[$hutang],0)."</td>";
            
            $tab.="<td align=right>".number_format($sis[$hutang],0)."</td>";
            
//            $tothutang+=$dtTagih[$hutang];
//            $totdibayar+=$dibayarsmp[$hutang];
//            $totsisa+=$sis[$hutang];
//            
//            $tab.="<td align=right>".number_format($tothutang,0)."</td>";
//            $tab.= "<td align=right>".number_format($totdibayar,0)."</td>";
//            $tab.="<td align=right>".number_format($totsisa,0)."</td>";
            $tab.="</tr>";
             $tHut+=$dtTagih[$hutang];
             $tByr+=$dibayarsmp[$hutang];
             $tSis+=$sis[$hutang];
             
        }
       
       
        $tab.=" <thead><td colspan=2>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($tHut,0)."</td>";
        $tab.="<td align=right>".number_format($tByr,0)."</td>";
         $tab.="<td align=right>".number_format($tSis,0)."</td> </thead>";
       }
}
       
       $tab.="</tbody></table>";
switch($proses)
{
        case'getPt':
        //echo "warning:masuk";
        $optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sOrg="select distinct kodeorg  from ".$dbname.".log_po_vw where substr(tanggal,1,7)='".$periode."'";
        //exit("Error:".$sOrg);
        $qOrg=mysql_query($sOrg) or die(mysql_error());
        while($rOrg=mysql_fetch_assoc($qOrg))
        {
                $optorg.="<option value=".$rOrg['kodeorg'].">".$optNmOrg[$rOrg['kodeorg']]."</option>";
        }
        echo $optorg;
        break;
        case'getJenis':
        $optSu=$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
         $jenisId=='0'?$whr=" and tipeinvoice='K'":$whr=" and tipeinvoice='P'";
         if($kdUnit!='')
         {
             $whr.=" and kodeorg='".$kdUnit."'";
         }

        $sData="select distinct nopo from ".$dbname.".keu_tagihanht 
               where substr(tanggal,1,7)='".$periode."' ".$whr."";
        $qOrg=mysql_query($sData) or die(mysql_error($conn));
        while($rOrg=mysql_fetch_assoc($qOrg))
        {
                $optorg.="<option value=".$rOrg['nopo'].">".$rOrg['nopo']."</option>";
        }
        $sData="select distinct kodesupplier from ".$dbname.".keu_tagihanht
               where substr(tanggal,1,7)='".$periode."' ".$whr."";
        $qOrg=mysql_query($sData) or die(mysql_error($conn));
        while($rOrg=mysql_fetch_assoc($qOrg))
        {
                $optSu.="<option value='".$rOrg['kodesupplier']."'>".$optSupp[$rOrg['kodesupplier']]."</option>";
        }

        echo $optorg."###".$optSu;
        break;
        case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="riwayat_pembayaran_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";

        break;
        case'getTgl':
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['lang']['lokasitugas'];
        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' and periode='".$tanggal."' ";
        //echo"warning".$sTgl;
        $qTgl=mysql_query($sTgl) or die(mysql_error());
        $rTgl=mysql_fetch_assoc($qTgl);
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;

        case'pdf':
        $kdPt=$_GET['kdPt'];
        //$arr="##kdPt##kdSup##kdUnit##tglDr##tglSmp";
        $kdSup=$_GET['kdSup'];
        $kdUnit=$_GET['kdUnit'];
        $tglDari=tanggalsystem($_GET['tglDr']);
        $tanggalSampai=tanggalsystem($_GET['tanggalSampai']);	
        $lokBeli=$_GET['lokBeli'];
        //echo $tglDari."__".$tanggalSampai;exit();
        if(($tglDari=='')||($tanggalSampai==''))
        {
                echo"warning: Date required";
                exit();
        }
        else
        {
                if($kdPt!='')
                                {
                                        $where.=" and a.kodeorg='".$kdPt."'";
                                }
                                if($kdUnit!='')
                                {
                                        $where.=" and substring(b.nopp,16,4)='".$kdUnit."'";
                                }
                                if($kdSup!="")
                                {
                                        $where.=" and a.kodesupplier='".$kdSup."'";
                                }
                                if(($tglDr!='')||($tanggalSampai!=''))
                                {
                                        $where.=" and (a.tanggal between '".$tglDari."' and '".tanggalsystem($_GET['tanggalSampai'])."')";
                                }
                                if($lokBeli!='')
                                {
                                    $where.=" and lokalpusat='".$lokBeli."'";
                                }
        }

         class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $kdPt;
                                global $kdSup;
                                global $kdUnit;
                                global $tglDari;
                                global $tanggalSampai;
                                global $where;
                                global $isi;

                                $isi=array();
                                if($kdPt=="")
                                {
                                        $pt='MHO';
                                }
                                else
                                {
                                        $pt=$kdPt;
                                }
                # Alamat & No Telp
       /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                    "kodeorganisasi='".$kdPt."'");
                $orgData = fetchData($query);*/
                                $sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
                                $qAlamat=mysql_query($sAlmat) or die(mysql_error());
                                $rAlamat=mysql_fetch_assoc($qAlamat);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,70);	
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, $_SESSION['lang']['detPemb'],0,1,'C');	
                                $this->SetFont('Arial','',8);
                                $this->Cell($width,$height, "Periode : ".$_GET['tglDr']." s.d. ".$_GET['tanggalSampai'],0,1,'C');	
                                $this->Ln();$this->Ln();
                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);


                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                $this->Cell(15/100*$width,$height,$_SESSION['lang']['supplier'],1,0,'C',1);		
                                $this->Cell(12/100*$width,$height,$_SESSION['lang']['nopo'],1,0,'C',1);		
                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);			
                                $this->Cell(22/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);	
                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['matauang'],1,0,'C',1);		
                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);	
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggal']." PP",1,0,'C',1);	
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggal']." BAPB",1,1,'C',1);					

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
                $sData="select a.kodesupplier from ".$dbname.".log_poht a left join ".$dbname.".log_podt b on a.nopo=b.nopo where a.statuspo>1 ".$where." group by kodesupplier order by a.tanggal asc";
        $qData=mysql_query($sData) or die(mysql_error());
        while($rData=mysql_fetch_assoc($qData))
        {
                $isi[]=$rData;
        }
        $totalAll=array();
       if(!empty($isi)) 
       {
        foreach($isi as $test => $dt)
        {
                $no+=1;

                $i=0;$afdC=false;
                $sNm="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$dt['kodesupplier']."'";
                $qNm=mysql_query($sNm) or die(mysql_error());
                $rNm=mysql_fetch_assoc($qNm);
                if($afdC==false)
                {
                        $pdf->Cell(3/100*$width,$height,$no,'TLR',0,'C',1);
                        $pdf->Cell(15/100*$width,$height,$rNm['namasupplier'],'TLR',0,'C',1);	
                }

                $sList="select distinct a.tanggal,a.matauang,b.kodebarang,b.satuan,b.nopo,b.jumlahpesan,b.nopp,b.hargasatuan from ".$dbname.".log_poht a left join ".$dbname.".log_podt b on a.nopo=b.nopo where a.kodesupplier='".$dt['kodesupplier']."' and b.nopo!='NULL' and a.tanggal between '".$tglDari."' and '".$tanggalSampai."'";
                $qList=mysql_query($sList) or die(mysql_error());
                $grandTot=array();

                while($rList=mysql_fetch_assoc($qList))
                {		
                        $limit++;
                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rList['kodebarang']."'";
                        $qBrg=mysql_query($sBrg) or die(mysql_error());
                        $rBrg=mysql_fetch_assoc($qBrg);
                        if($rList['matauang']!='IDR')
                        {
                                $sKurs="select kurs from ".$dbname.".setup_matauangrate where kode='".$rList['matauang']."' and daritanggal='".$rList['tanggal']."'";
                                $qKurs=mysql_query($sKurs) or die(mysql_error());
                                $rKurs=mysql_fetch_assoc($qKurs);
                                if($rKurs!='')
                                {
                                        $hrg=$rKurs['kurs']*$rList['hargasatuan'];
                                        $totHrg=$rList['jumlahpesan']*$hrg;
                                }
                                else
                                {
                                        if($rList['matauang']=='USD')
                                        {
                                                $hrg=$rList['hargasatuan']*8850;
                                                $totHrg=$rList['jumlahpesan']*$hrg;
                                                $rList['matauang']="IDR";
                                        }
                                        elseif($rList['matauang']=='EUR') 
                                        {
                                                $hrg=$rList['hargasatuan']*12643;
                                                $totHrg=$rList['jumlahpesan']*$hrg;
                                                $rList['matauang']="IDR";
                                        }
                                        elseif(($rList['matauang']=='')||($rList['matauang']=='NULL'))
                                        {
                                                $totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                                        }
                                }
                        }
                        else
                        {
                                $totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                        }
                        //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                        $grandTot['total']+=$totHrg;
                        if($rList['nopp']!="")
                        {
                                $sTgl="select tanggal from ".$dbname.".log_prapoht where nopp='".$rList['nopp']."'";
                                $qTgl=mysql_query($sTgl) or die(mysql_error());
                                $rTgl=mysql_fetch_assoc($qTgl);

                                if(($rTgl['tanggal']!="")||($rTgl['tanggal']!="000-00-00"))
                                {
                                                $tglPP=tanggalnormal($rTgl['tanggal']);		
                                }
                                else
                                {
                                        $tglPP="";
                                }
                        }
                        else
                        {
                                $tglPP="";
                        }
                        if($rList['nopo']!="")
                        {
                                $sTgl2="select tanggal from ".$dbname.".log_transaksiht where nopo='".$rList['nopo']."' and tipetransaksi=1";
                                $qTgl2=mysql_query($sTgl2) or die(mysql_error());
                                $rTgl2=mysql_fetch_assoc($qTgl2);
                                if($rTgl2['tanggal']!="")
                                {
                                                $tglBapb=tanggalnormal($rTgl2['tanggal']);		
                                }
                                else
                                {
                                        $tglBapb="";
                                }
                        }
                        else
                        {
                                $tglBapb="";
                        }
                        if($afdC==true) {
                                $i=0;
                                $pdf->Cell(3/100*$width,$height,'','LR',$align[$i],1);
                                $pdf->Cell(15/100*$width,$height,'','LR',$align[$i],1);
                                //$pdf->Cell($length[$i]/100*$width,$height,'','LR',$align[$i],1);
                                $i++;
                        } else {
                                $afdC = true;
                        }	
                        $pdf->Cell(12/100*$width,$height,$rList['nopo'],1,0,'L',1);		
                        $pdf->Cell(6/100*$width,$height,tanggalnormal($rList['tanggal']),1,0,'C',1);			
                        $pdf->Cell(22/100*$width,$height,$rBrg['namabarang'],1,0,'L',1);	
                        $pdf->Cell(6/100*$width,$height,$rList['matauang'],1,0,'C',1);		
                        $pdf->Cell(6/100*$width,$height,$rList['jumlahpesan'],1,0,'R',1);
                        $pdf->Cell(6/100*$width,$height,$rList['satuan'],1,0,'C',1);
                        $pdf->Cell(10/100*$width,$height,number_format($totHrg,2),1,0,'R',1);	
                        $pdf->Cell(7/100*$width,$height,$tglPP,1,0,'C',1);	
                        $pdf->Cell(7/100*$width,$height,$tglBapb,1,1,'C',1);
                        //if($limit==46)				
//			{	
//				$limit=0;
//				$pdf->AddPage();
//			}

                }
                $totalAll['totalSemua']+=$grandTot['total'];
                $pdf->Cell(76/100*$width,$height,"Sub Total",1,0,'C',1);
                $pdf->Cell(10/100*$width,$height,number_format($grandTot['total'],2),1,0,'R',1);
                $pdf->Cell(14/100*$width,$height,'',1,1,'R',1);
        }
}
        $pdf->Cell(76/100*$width,$height,"Total",1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,number_format($totalAll['totalSemua'],2),1,0,'R',1);
        $pdf->Cell(14/100*$width,$height,'',1,1,'R',1);
        $pdf->Cell($width,$height,terbilang($totalAll['totalSemua'],2),1,1,'C',1);


        $pdf->Output();
        break;


        default:
        break;
}
?>