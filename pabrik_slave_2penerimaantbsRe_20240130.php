<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$tipeIntex = checkPostGet('tipeIntexRe','');
$unit = checkPostGet('unitRe','');
$tgl_1 = checkPostGet('tglRe','');
$tanggl = checkPostGet('tglRe','');
$kdPabrik = checkPostGet('kdPabrikRe','');
$optSupp=makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$XA=  tanggalsystem($tanggl);
$thnA=substr($XA,0,4);
$blnA=substr($XA,4,2);
$tglA=substr($XA,6,2);
if($proses!='getKodeorg'){
//for($x=$tglA-1;$x>=0;$x--){
//    $tm=mktime(0,0,0,$blnA,$tglA-$x,$thnA);
//    $TGL[]=date('Y-m-d',$tm);
//    if($x==$tglA-1)
//      $listTGL="'".date('Y-m-d',$tm)."'";
//    else
//      $listTGL.=",'".date('Y-m-d',$tm)."'";  
//}
for($x=7;$x>=0;$x--){
    $tm=mktime(0,0,0,$blnA,$tglA-$x,$thnA);
    $TGL[]=date('Y-m-d',$tm);
    if($x==7)
      $listTGL="'".date('Y-m-d',$tm)."'";
    else
      $listTGL.=",'".date('Y-m-d',$tm)."'";  
}
//ambil  pt pabrik
$str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kdPabrik,0,4)."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $ptPks=$bar->induk;
}


#ambil  kebun internal;
$str="select kodeorganisasi from ".$dbname.".organisasi where induk='".substr($ptPks,0,3)."' and tipe='KEBUN' and kodeorganisasi not like '%PE'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $kebunInternal[]=$bar->kodeorganisasi;
}

#ambil kebun afiliasi
$str="select kodeorganisasi from ".$dbname.".organisasi where induk!='".substr($ptPks,0,3)."' and tipe='KEBUN'and kodeorganisasi not like '%PE'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $kebunAffiliasi[]=$bar->kodeorganisasi;
}
#ambil kebun Plasma
//$kebunPlasma=Array('CKPE','MEPE');
$str="select kodeorganisasi from ".$dbname.".organisasi where induk='".substr($ptPks,0,3)."' and tipe='KEBUN' and kodeorganisasi like '%PE'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $kebunPlasma[]=$bar->kodeorganisasi;
}
#ambil curtommer
$str="select distinct kodecustomer from ".$dbname.".pabrik_timbangan where (kodeorg is null or kodeorg='') 
           and left(tanggal,10) in (".$listTGL.") and kodebarang='40000003'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $costomer[]=$bar->kodecustomer;
}


#ambil tbs internal
//$str1="select kodeorg,left(tanggal,10) as tanggal,substr(nospb,9,6) as afd, sum(beratbersih) as netto
//          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and length(kodeorg)=4 and
//          left(tanggal,10) in (".$listTGL.") and kodebarang='40000003' group by  left(tanggal,10),substr(nospb,9,6)
//          order by kodeorg, substr(nospb,9,6),left(tanggal,10) ";
$str1="select kodeorg,left(tanggal,10) as tanggal,substr(nospb,9,6) as afd, sum(beratbersih) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and length(kodeorg)=4 and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10),substr(nospb,9,6)
          order by kodeorg, substr(nospb,9,6),left(tanggal,10) ";
$resInternal=mysql_query($str1);
while($bar=mysql_fetch_object($resInternal))
{
    $tbsInt[$bar->kodeorg][$bar->afd][$bar->tanggal]=$bar->netto;
}
#ambil tbs internal 2 (totalnya)
$str1="select kodeorg,left(tanggal,10) as tanggal,substr(nospb,9,6) as afd, sum(beratbersih) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and length(kodeorg)=4 and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10),substr(nospb,9,6)
          order by kodeorg, substr(nospb,9,6),left(tanggal,10) ";
$resInternal=mysql_query($str1);
while($bar=mysql_fetch_object($resInternal))
{
	setIt($tottbsInt[$bar->kodeorg][$bar->afd],0);
    $tottbsInt[$bar->kodeorg][$bar->afd]+=$bar->netto;
}


#ambil tbs external
//$str2="select kodecustomer,left(tanggal,10) as tanggal, sum(beratbersih) as netto
//          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and (kodeorg is null or kodeorg='') and
//          left(tanggal,10) in (".$listTGL.") and kodebarang='40000003' group by  left(tanggal,10),kodecustomer
//          order by left(tanggal,10),kodecustomer";
$str2 ="select kodecustomer,trpcode,left(tanggal,10) as tanggal, sum(beratbersih) as netto
		from ".$dbname.".pabrik_timbangan 
		where millcode like '%".$kdPabrik."%' and (kodeorg is null or kodeorg='') 
		and left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' 
		group by left(tanggal,10),kodecustomer,trpcode
		order by left(tanggal,10),kodecustomer,trpcode";
$resExternal=mysql_query($str2);
while($bar=mysql_fetch_object($resExternal))
{
    $tbsExt[$bar->kodecustomer.$bar->trpcode][$bar->tanggal]=$bar->netto;
}
#ambil tbs external 2 (totalnya)
$str2 ="select kodecustomer,trpcode,left(tanggal,10) as tanggal, sum(beratbersih) as netto
		from ".$dbname.".pabrik_timbangan 
		where millcode like '%".$kdPabrik."%' and (kodeorg is null or kodeorg='') 
		and left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' 
		group by left(tanggal,10),kodecustomer,trpcode
		order by left(tanggal,10),kodecustomer,trpcode";
$resExternal=mysql_query($str2);
while($bar=mysql_fetch_object($resExternal))
{
	setIt($tottbsExt[$bar->kodecustomer.$bar->trpcode],0);
    $tottbsExt[$bar->kodecustomer.$bar->trpcode]+=$bar->netto;
}
#==================================create table
if($proses=='preview')
     $border=0;
else
    $border=1;
if($_SESSION['language']=='EN'){
//$stream="FFB Recieve from ".tanggalnormal($TGL[0])." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
//                 Netto not include deduction";    
$stream="FFB Recieve month ".$blnA." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
                 Netto not include deduction";    
}else{
//        $stream="Penerimaan TBS  dari Tanggal ".tanggalnormal($TGL[0])." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
//                 Berat yang tampil adalah berat bersih (belum dikurangi potongan sortasi)";
        $stream="Penerimaan TBS  Bulan ".$blnA." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
                 Berat yang tampil adalah berat bersih (belum dikurangi potongan sortasi)";
}
$stream.=" <table class=sortable cellspacing=1 border=".$border.">
                    <thead>
                     <tr class=rowheader><td colspan=3 align=center>".$_SESSION['lang']['tanggal']."</td>";
                    if(!empty($TGL))foreach($TGL as $key=>$tg)
                    {
                        $stream.="<td width=50px align=center>".substr($tg,8,2)."</td>";
                    }
$stream.="<td>".$_SESSION['lang']['total']."</td></tr>
                    </thead>
                  <tbody>";
#inti=========================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>A.Internal</td><td colspan=10 bgcolor=#dedede></td></tr>";
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td  colspan=2 bgcolor=#dedede>A.1. Inti</td><td colspan=9 bgcolor=#dedede></td></tr>";
$ttinti=0;
if(!empty($kebunInternal))foreach($kebunInternal as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        $no=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
                    $stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                        $bgwarna="";
						setIt($art[$tang],0);
						setIt($ttang[$tang],0);
                        $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                           left(blok,6)='".$afd."' and tanggal='".$tang."' 
                           and substr(nospb,9,6)<>left(blok,6)";
                        $qcek=mysql_query($scek) or die(mysql_error($conn));
                        $rcek=mysql_num_rows($qcek);
                        if($rcek==1){
                            $bgwarna="bgcolor=yellow";
                        }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tinti[$keei],0);
                $tinti[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttinti+=$tkebun[$kodekebun];
      }     
}
$ttinternal=0;
if(!empty($tinti))
{
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Inti</td>";        
        if(!empty($tinti))foreach($tinti as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
//        $ttinti+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttinti)."</td></tr>";
    $ttinternal+=$ttinti;
}
#afiliasi====================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td  colspan=2 bgcolor=#dedede>A.2. Afiliasi</td><td colspan=9 bgcolor=#dedede></td></tr>";
if(!empty($kebunAffiliasi))foreach($kebunAffiliasi as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        $no=$ttafiliasi=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
                    $stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                        $bgwarna="";
						setIt($art[$tang],0);
						setIt($ttang[$tang],0);
                        $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                           left(blok,6)='".$afd."' and tanggal='".$tang."' 
                           and substr(nospb,9,6)<>left(blok,6)";
                        $qcek=mysql_query($scek) or die(mysql_error($conn));
                        $rcek=mysql_num_rows($qcek);
                        if($rcek==1){
                            $bgwarna="bgcolor=yellow";
                        }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tafiliasi[$keei],0);
                $tafiliasi[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttafiliasi+=$tkebun[$kodekebun];
      }     
}
if(!empty($tafiliasi))
{
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Afiliasi</td>";
        if(!empty($tafiliasi))foreach($tafiliasi as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
//        $ttafiliasi+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttafiliasi)."</td></tr>";
    $ttinternal+=$ttafiliasi;
}
#Plasma====================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td  colspan=2 bgcolor=#dedede>A.3. Plasma</td><td colspan=9 bgcolor=#dedede></td></tr>";
if(!empty($kebunPlasma))foreach($kebunPlasma as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        $no=$ttplasma=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
					$stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                    $bgwarna="";
					setIt($art[$tang],0);
					setIt($ttang[$tang],0);
                    $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                       left(blok,6)='".$afd."' and tanggal='".$tang."' 
                       and substr(nospb,9,6)<>left(blok,6)";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    $rcek=mysql_num_rows($qcek);
                    if($rcek==1){
                        $bgwarna="bgcolor=yellow title='Ada Buah Dari Afdeling Lain'";
                    }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tplasma[$keei],0);
                $tplasma[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttplasma+=$tkebun[$kodekebun];
      }     
}
if(!empty($tplasma))
{
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Plasma</td>";
        if(!empty($tplasma))foreach($tplasma as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
        //$ttplasma+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttplasma)."</td></tr>";
    $ttinternal+=$ttplasma;
}
#total internal
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Total Inti + Plasma (A)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg])."</td>";
		setIt($tinternal[$tg],0);
        $tinternal[$tg]+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
//        $ttinternal+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttinternal)."</td></tr>";
	setIt($gtt,0);
    $gtt+=$ttinternal;
    
    
#External========================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>B.External</td><td colspan=10 bgcolor=#dedede></td></tr>";
$no=$ttExt=0;
if(!empty($tbsExt))
{
if(!empty($tbsExt))foreach($tbsExt as $suppid=>$art){
        $no+=1;
        $stream.="<tr class=rowcontent>";
        $stream.="<td></td><td>".$no."</td><td>".$optSupp[substr($suppid,0,10)]." ".substr($suppid,10,30)."</td>";
        $tt=0;
        if(!empty($TGL))foreach($TGL as $kei=>$tang){
			setIt($art[$tang],0);
			setIt($tExt[$tang],0);
            $stream.="<td align=right>".number_format($art[$tang])."</td>";
            $tt+=$art[$tang];
            $tExt[$tang]+=$art[$tang];
        }
        $stream.="<td align=right>".number_format($tottbsExt[$suppid])."</td></tr>";
        $ttExt+=$tottbsExt[$suppid];
    }    
}
#total External

    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Total External (B)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tExt[$tg])."</td>";
//        $ttExt+=$tExt[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttExt)."</td></tr>";
    $gtt+=$ttExt;
#Grand Total
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Grand Total (A+B)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tExt[$tg]+$tinternal[$tg])."</td>";
//        $gtt+=$tExt[$tg]+$tinternal[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($gtt)."</td></tr>";
    
$stream.="</tbody><tfoot></tfoot></table>";
}               
switch($proses)
{
        case 'preview':          
            echo $stream;
        break;
        case 'excel':          
                        $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
                        $qwe=date("YmdHms");
                        $nop_="Laporan_penerimaan_TBS_Tanggal ".tanggalnormal($TGL[0])." sd Tanggal ".tanggalnormal($TGL[count($TGL)-1])."PKS".$kdPabrik.'_'.$qwe;
                        if(strlen($stream)>0)
                        {
                             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                             gzwrite($gztralala, $stream);
                             gzclose($gztralala);
                             echo "<script language=javascript1.2>
                                window.location='tempExcel/".$nop_.".xls.gz';
                                </script>"; 
                        }    
        break;
        case 'pdf':          
        //belum dibuat
          
	 class PDF extends FPDF
        {
            function Header() {
            global $conn;
            global $dbname;
            global $align;
            global $length;
            global $colArr;
            global $title;
            global $tipeIntex;
            global $periode;
            global $unit;
            global $kdPabrik;
            global $tgl_2;
            global $tgl_1;
            global $tglPeriode;
            global $TGL;
            global $optSupp;
            global $optNm;
			global $blnA;
				
				
                $tglPeriode=explode("-",$periode);
                $tanggal=count($tglPeriode)>1? $tglPeriode[1]."-".$tglPeriode[0]: '';
                # Alamat & No Telp
                /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                "kodeorganisasi='".$kdPt."'");
                $orgData = fetchData($query);*/
                $sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
                $qAlamat=mysql_query($sAlmat) or die(mysql_error());
                $rAlamat=mysql_fetch_assoc($qAlamat);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,43);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(90);   
                $this->Cell($width-90,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(90); 		
                $this->Cell($width-90,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(90); 			
                $this->Cell($width-90,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, $_SESSION['lang']['rePenerimaanTbs'],0,1,'C');	
                $this->SetFont('Arial','',8);
                $this->Ln(5);
                if($_SESSION['language']=='EN'){
//                    $this->Cell($width,$height,"FFB receive from ".tanggalnormal($TGL[0])." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (not include grading deduction)",0,1,'C');
                    $this->Cell($width,$height,"FFB receive month ".$blnA." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (not include grading deduction)",0,1,'C');
                }else{
//                    $this->Cell($width,$height,"Penerimaan TBS  dari Tanggal ".tanggalnormal($TGL[0])." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (belum dikurangi potongan sortasi)",0,1,'C');
                    $this->Cell($width,$height,"Penerimaan TBS  Bulan ".$blnA." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (belum dikurangi potongan sortasi)",0,1,'C');
                }
                $this->Ln(10);
                         
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		//$pdf->SetFillColor(255,255,255); 
                $pdf->SetFillColor(220,220,220);
		$pdf->SetFont('Arial','',7);
        $totPdf=0;
        $nor=0;
        $no=0;
 
               $coldt=count($TGL)+1;
               $coldt=9*$coldt;
                $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                if(!empty($TGL))foreach($TGL as $key=>$tg)
                {
                   $pdf->Cell(9/100*$width,$height,substr($tg,8,2),1,0,'C',1);
                }
                $pdf->Cell(9/100*$width,$height,$_SESSION['lang']['total'],1,1,'C',1);
                $pdf->Cell(20/100*$width,$height,"A.Internal",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                #inti=========================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.1. Inti",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunInternal))foreach($kebunInternal as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    
                                    
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                                        setIt($ttang[$tang],0);
										setIt($art[$tang],0);
										$pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
										$ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                 $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tinti[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttinti+=$tkebun[$kodekebun];
                            
                      }     
                }
               
                if(!empty($tinti))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Inti",1,0,'L',1);
                    
                        if(!empty($tinti))foreach($tinti as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                        $ttinti+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttinti),1,1,'R',1);
//    $ttinternal+=$ttinti;
                }
                
                
                #afiliasi====================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.2. Afiliasi",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunAffiliasi))foreach($kebunAffiliasi as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;           
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
										setIt($ttang[$tang],0);
										setIt($art[$tang],0);
                                        $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                                        $ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tafiliasi[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttafiliasi+=$tkebun[$kodekebun];
                            
                      }     
                }
                
                if(!empty($tafiliasi))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Afiliasi",1,0,'L',1);
                    
                        if(!empty($tafiliasi))foreach($tafiliasi as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                         $ttafiliasi+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttafiliasi),1,1,'R',1);
//    $ttinternal+=$ttafiliasi;
                }
                
                #Plasma====================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.3. Plasma",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunPlasma))foreach($kebunPlasma as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;           
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
										setIt($ttang[$tang],0);
										setIt($art[$tang],0);
                                        $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                                        $ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tplasma[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttplasma+=$tkebun[$kodekebun];
                            
                      }     
                }
                
                if(!empty($tplasma))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Afiliasi",1,0,'L',1);
                    
                        if(!empty($tplasma))foreach($tplasma as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                         $ttplasma+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttplasma),1,1,'R',1);
                }
            #total internal
            $pdf->Cell(20/100*$width,$height,"Total Internal (A)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg]),1,0,'R',1);
//                $tinternal[$tg]+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
//                $ttinternal+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
            }
            $pdf->Cell(9/100*$width,$height,number_format($ttinternal),1,1,'R',1);
//    $gtt+=$ttinternal;
            
            #External========================================================================================
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>B.External</td><td colspan=10 bgcolor=#dedede></td></tr>";
            $no=0;
                $pdf->SetFillColor(220,220,220); 
                $pdf->Cell(20/100*$width,$height,"B.External",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($tbsExt))
                {
                  $pdf->SetFillColor(255,255,255);    
                if(!empty($tbsExt))foreach($tbsExt as $suppid=>$art){
                        $no+=1;
                        
                        $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                        $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                        $pdf->Cell(13/100*$width,$height,$optSupp[$suppid],1,0,'L',1);
                        $tt=0;
                        if(!empty($TGL))foreach($TGL as $kei=>$tang){
							setIt($art[$tang],0);
                            $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                        }
                         $pdf->Cell(9/100*$width,$height,number_format($tottbsExt[$suppid]),1,1,'R',1);
//                         $ttExt+=$tottbsExt[$suppid];
                    }    
                }
                $pdf->SetFillColor(220,220,220); 
#total External
    $pdf->Cell(20/100*$width,$height,"Total External (B)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tExt[$tg]),1,0,'R',1);
//                 $ttExt+=$tExt[$tg];
            }
     $pdf->Cell(9/100*$width,$height,number_format($ttExt),1,1,'R',1);
//    $gtt+=$ttExt;
#Grand Total
    $pdf->Cell(20/100*$width,$height,"Grand Total (A+B)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tExt[$tg]+$tinternal[$tg]),1,0,'R',1);
//                  $gtt+=$tExt[$tg]+$tinternal[$tg];
            }
     $pdf->Cell(9/100*$width,$height,number_format($gtt),1,1,'R',1);
    $pdf->Output();
            
        break;    
        case'getKodeorg':
        $optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
        
        $kdPabrik=$_POST['kdPabrik']; 
        $optPt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
        $ptnya=$optPt[$kdPabrik]; 
        if($tipeIntex==1)
        {
                //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk ='".$ptnya."' order by namaorganisasi asc";
        }
        else if($tipeIntex==0)
        {
                $sOrg ="SELECT namasupplier,kodetimbangan FROM ".$dbname.".log_5supplier WHERE (kodetimbangan!='' and kodetimbangan!='NULL') and status='1' 
						order by namasupplier asc";//echo "warning:".$sOrg;
        }
        else if($tipeIntex==2)
        {
                //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk <>'".$ptnya."' order by namaorganisasi asc";
        }
        // echo "warning:__".$sOrg."___".$tipeIntex;exit();
        if($tipeIntex!=3)
        {
            $qOrg=mysql_query($sOrg) or die(mysql_error());
            while($rOrg=mysql_fetch_assoc($qOrg))
            {
                    if($tipeIntex!=0)
                    {
                            $optorg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['namaorganisasi']."</option>";
                    }
                    else
                    {
                            $optorg.="<option value='".$rOrg['kodetimbangan']."'>".$rOrg['namasupplier']."</option>";
                    }
            }
        }
        echo $optorg;
        break;
        default:
        break;
}
?>