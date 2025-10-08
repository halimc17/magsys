<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$_POST['unit']==''?$unit=$_GET['unit']:$unit=$_POST['unit'];
$_POST['periode']==''?$periode=$_GET['periode']:$periode=$_POST['periode'];
$_POST['judul']==''?$judul=$_GET['judul']:$judul=$_POST['judul'];
$_POST['afdId']==''?$afdId=$_GET['afdId']:$afdId=$_POST['afdId'];

$qwe=explode('-',$periode); $tahun=$qwe[0]; $bulan=$qwe[1];

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if($unit==''||$periode=='')
{
    exit("Error:Field required");
}

$optBulan['01']=$_SESSION['lang']['jan'];
$optBulan['02']=$_SESSION['lang']['peb'];
$optBulan['03']=$_SESSION['lang']['mar'];
$optBulan['04']=$_SESSION['lang']['apr'];
$optBulan['05']=$_SESSION['lang']['mei'];
$optBulan['06']=$_SESSION['lang']['jun'];
$optBulan['07']=$_SESSION['lang']['jul'];
$optBulan['08']=$_SESSION['lang']['agt'];
$optBulan['09']=$_SESSION['lang']['sep'];
$optBulan['10']=$_SESSION['lang']['okt'];
$optBulan['11']=$_SESSION['lang']['nov'];
$optBulan['12']=$_SESSION['lang']['dec'];

// building array: dzArr (main data) =========================================================================
// as seen on sdm_slave_2prasarana.php
$dzArr=array();

// kg budget setahun
$aresta="SELECT sum(kgsetahun) as setahun FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget ='".$tahun."'";
if($afdId!='')
{
   $aresta="SELECT sum(kgsetahun) as setahun FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget ='".$tahun."'";  
}
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kgbgth=$res['setahun'];
}   

// kg budget bulan ini
$aresta="SELECT sum(kg".$bulan.") as bi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget = '".$tahun."'";
if($afdId!='')
{
    $aresta="SELECT sum(kg".$bulan.") as bi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget = '".$tahun."'";
}
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kgbgbi=$res['bi'];
}   

$addstr="(";
for($W=1;$W<=intval($bulan);$W++)
{
    if($W<10)$jack="kg0".$W;
    else $jack="kg".$W;
    if($W<intval($bulan))$addstr.=$jack."+";
    else $addstr.=$jack;
}
$addstr.=")";

// kg budget sampai dengan bulan ini
$aresta="SELECT sum(".$addstr.") as sdbi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget = '".$tahun."'";
if($afdId!='')
{
    $aresta="SELECT sum(".$addstr.") as sdbi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget = '".$tahun."'";
}
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kgbgsd=$res['sdbi'];
}   

// kg real bulan ini
$aresta="SELECT sum(beratbersih) as bi FROM ".$dbname.".pabrik_timbangan
    WHERE kodeorg like '".$unit."%' and tanggal like '".$periode."%'";
if($afdId!='')
{
    $aresta="SELECT sum(beratbersih) as bi FROM ".$dbname.".pabrik_timbangan
    WHERE nospb like '%".$afdId."%' and tanggal like '".$periode."%'";
}
//echo $aresta.'<br>';
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kgrebi=$res['bi'];
}   

// kg real sampain dengan bulan ini
$aresta="SELECT sum(beratbersih) as sdbi FROM ".$dbname.".pabrik_timbangan
    WHERE kodeorg like '".$unit."%' and (substr(tanggal,1,10) between '".$tahun."-01-01 00:00:00' and LAST_DAY('".$periode."-15'))";
if($afdId!='')
{
 $aresta="SELECT sum(beratbersih) as sdbi FROM ".$dbname.".pabrik_timbangan
    WHERE nospb like '%".$afdId."%' and (substr(tanggal,1,10) between '".$tahun."-01-01 00:00:00' and LAST_DAY('".$periode."-15'))";   
}
//echo $aresta.'<br>';
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kgresd=$res['sdbi'];
}   

// cari kegiatan panen
$aresta="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE length(noakun)=7 and noakun like '611%'
    ORDER BY noakun";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kegpanen[$res['noakun']]['noakun']=$res['noakun'];
    if($_SESSION['language']=='EN'){
        $kegpanen[$res['noakun']]['namaakun']=$res['namaakun1'];
    }else{
        $kegpanen[$res['noakun']]['namaakun']=$res['namaakun'];
    }
}   

// cari kegiatan pemeliharaan
$aresta="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE length(noakun)=7 and noakun like '621%'
    ORDER BY noakun";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kegpemel[$res['noakun']]['noakun']=$res['noakun'];
        if($_SESSION['language']=='EN'){
            $kegpemel[$res['noakun']]['namaakun']=$res['namaakun1'];
        }else{
           $kegpemel[$res['noakun']]['namaakun']=$res['namaakun'];
        }
    
}   

// cari kegiatan tidak langsung
$aresta="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE length(noakun)=7 and noakun like '71%'
    ORDER BY noakun";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $kegtidak[$res['noakun']]['noakun']=$res['noakun'];
            if($_SESSION['language']=='EN'){
                $kegtidak[$res['noakun']]['namaakun']=$res['namaakun1'];
            }else{
                $kegtidak[$res['noakun']]['namaakun']=$res['namaakun'];
            }
}   

// cari anggaran setahun
$str="SELECT noakun, setahun FROM ".$dbname.".bgt_summary_biaya_vw 
    WHERE tahunbudget = '".$tahun."' and unit = '".$unit."'";

$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $dzArr[$res['noakun']]['111']=$res['setahun'];
    @$dzArr[$res['noakun']]['112']=$res['setahun']/$kgbgth;
}

// cari anggaran bulan ini
$str="SELECT noakun, rp".$bulan." as bi FROM ".$dbname.".bgt_summary_biaya_vw 
    WHERE tahunbudget = '".$tahun."' and unit = '".$unit."'";
$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $dzArr[$res['noakun']]['121']=$res['bi'];
    @$dzArr[$res['noakun']]['122']=$res['bi']/$kgbgbi;
}

$addstr2="(";
for($W=1;$W<=intval($bulan);$W++)
{
    if($W<10)$jack="rp0".$W;
    else $jack="rp".$W;
    if($W<intval($bulan))$addstr2.=$jack."+";
    else $addstr2.=$jack;
}
$addstr2.=")";

// cari anggaran sampai dengan bulan ini
$str="SELECT noakun, ".$addstr2." as jumlah FROM ".$dbname.".bgt_summary_biaya_vw 
    WHERE tahunbudget = '".$tahun."' and unit = '".$unit."'";
$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $dzArr[$res['noakun']]['131']=$res['jumlah'];
    @$dzArr[$res['noakun']]['132']=$res['jumlah']/$kgbgsd;
}

// cari realisasi bulan ini
$str="SELECT noakun, sum(jumlah) as jumlah FROM ".$dbname.".keu_jurnaldt 
    WHERE tanggal like '".$periode."%' and nojurnal like '%".$unit."%'
    GROUP BY noakun";
if($afdId!='')
{
  $str="SELECT noakun, sum(jumlah) as jumlah FROM ".$dbname.".keu_jurnaldt 
    WHERE tanggal like '".$periode."%' and kodeblok like '%".$afdId."%'
    GROUP BY noakun";  
}
$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $dzArr[$res['noakun']]['211']=$res['jumlah'];
    @$dzArr[$res['noakun']]['212']=$res['jumlah']/$kgrebi;
}

// cari realisasi sampai dengan bulan ini
$str="SELECT noakun, sum(jumlah) as jumlah FROM ".$dbname.".keu_jurnaldt 
    WHERE (substr(tanggal,1,10) between '".$tahun."-01-01' and LAST_DAY('".$periode."-15')) and nojurnal like '%".$unit."%'
    GROUP BY noakun";
if($afdId!='')
{
    $str="SELECT noakun, sum(jumlah) as jumlah FROM ".$dbname.".keu_jurnaldt 
    WHERE (substr(tanggal,1,10) between '".$tahun."-01-01' and LAST_DAY('".$periode."-15')) and kodeblok like '%".$afdId."%'
    GROUP BY noakun";
}

$query=mysql_query($str) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $dzArr[$res['noakun']]['221']=$res['jumlah'];
    @$dzArr[$res['noakun']]['222']=$res['jumlah']/$kgresd;
}

if(!empty($kegpanen))foreach($kegpanen as $keg){
    @$dzArr[$keg['noakun']]['311']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['111'];
    @$dzArr[$keg['noakun']]['312']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['131'];
}
if(!empty($kegpemel))foreach($kegpemel as $keg){
    @$dzArr[$keg['noakun']]['311']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['111'];
    @$dzArr[$keg['noakun']]['312']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['131'];
}
if(!empty($kegtidak))foreach($kegtidak as $keg){
    @$dzArr[$keg['noakun']]['311']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['111'];
    @$dzArr[$keg['noakun']]['312']=100*$dzArr[$keg['noakun']]['221']/$dzArr[$keg['noakun']]['131'];
}





#######
##pta##
#######

if($afdId=='')
{$sortUnit=$unit;}
 else 
{$sortUnit=$afdId;}




//$kgbgth
//$kgbgbi
##setahun 
$iptarpthn="select sum(rupiah) as rupiah,sum(volume) as volume,noakun from ".$dbname.".pta_dt_blok_vw where "
        . " tahun='".$tahun."' and kodeorg like '%".$sortUnit."%'  group by noakun";
$nptarpthn=  mysql_query($iptarpthn) or die (mysql_error($conn));
while($dptarpthn=  mysql_fetch_assoc($nptarpthn))
{
    
    $dzArr[$dptarpthn['noakun']][811]=$dptarpthn['rupiah'];
    @$dzArr[$dptarpthn['noakun']][812]=$dptarpthn['rupiah']/$kgbgth;
}


##bulan ini 
$iptarpbln="select sum(rupiah) as rupiah,sum(volume) as volume,noakun from ".$dbname.".pta_dt_blok_vw where "
        . " bulan='".$periode."' and kodeorg like '%".$sortUnit."%' group by noakun";
$nptarpbln=  mysql_query($iptarpbln) or die (mysql_error($conn));
while($dptarpbln=  mysql_fetch_assoc($nptarpbln))
{

    $dzArr[$dptarpbln['noakun']][821]=$dptarpbln['rupiah'];
    @$dzArr[$dptarpbln['noakun']][822]=$dptarpbln['rupiah']/$kgbgbi; 
}


##sd bulan ini 
$iptarpsdbln="select sum(rupiah) as rupiah,sum(volume) as volume,noakun from ".$dbname.".pta_dt_blok_vw where "
        . " (tanggal between '".$tahun."-01-01' and LAST_DAY('".$periode."-15')) and kodeorg like '%".$sortUnit."%'"
        . "  group by noakun";
$nptarpsdbln=  mysql_query($iptarpsdbln) or die (mysql_error($conn));
while($dptarpsdbln=  mysql_fetch_assoc($nptarpsdbln))
{
    $dzArr[$dptarpsdbln['noakun']][831]=$dptarpsdbln['rupiah'];
    @$dzArr[$dptarpsdbln['noakun']][832]=$dptarpsdbln['rupiah']/$kgbgsd;
}




//echo '<pre>';
//print_r($dzArr);
//echo '</pre>';

function numberformat($qwe,$asd)
{
    if($qwe==0)$zxc='0'; 
    else{
        $zxc=number_format($qwe,$asd);
    }
    return $zxc;
}        

if($proses=='excel')
{
$bg=" bgcolor=#DEDEDE";
$brdr=1;
$tab.="<table border=0>
     <tr>
        <td colspan=8 align=left><font size=3>20. ".strtoupper($_SESSION['lang']['biaya'])." ".strtoupper($_SESSION['lang']['produksi'])."</font></td>
        <td colspan=6 align=right>".$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun."</td>
     </tr> 
     <tr><td colspan=14 align=left>".$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")</td></tr>  ";
if($afdId!='')
{
    $tab.="<tr><td colspan=14 align=left>".$_SESSION['lang']['afdeling']." : ".$optNm[$afdId]." (".$afdId.")</td></tr>  ";
}
$tab.="</table>";
}
else
{ 
    $bg="";
    $brdr=0;
}
if($proses!='excel')$tab.=$judul;
    $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <td align=right colspan=2 ".$bg.">".strtoupper($_SESSION['lang']['produksi'])." (kg):</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgth,2)."</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgbi,2)."</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgsd,2)."</td>
        
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgth,2)."</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgbi,2)."</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgbgsd,2)."</td>

    <td align=right colspan=2 ".$bg.">".numberformat($kgrebi,2)."</td>
    <td align=right colspan=2 ".$bg.">".numberformat($kgresd,2)."</td>
    <td align=left colspan=2".$bg."></td>
    </tr>
    <tr>
    <td align=center rowspan=3 ".$bg.">No.</td>
    <td align=center rowspan=3 ".$bg.">".$_SESSION['lang']['pekerjaan']."</td>
    <td align=center colspan=6 ".$bg.">".$_SESSION['lang']['anggaran']."</td>
        <td align=center colspan=6 ".$bg.">PTA</td>
    <td align=center colspan=4 ".$bg.">".$_SESSION['lang']['realisasi']."</td>
    <td align=center rowspan=2 colspan=2 ".$bg.">% ".$_SESSION['lang']['pencapaian']."</td>
    </tr>
    <tr>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>

    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>

    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    <tr>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
        
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>

    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    </thead>
    <tbody>
";
        
//echo '<pre>';
//print_r($dzArr);
//echo '</pre>';

$dummy='';
if(empty($dzArr)){
    $tab.="<tr class=rowcontent><td colspan=14>Data Empty.</td></tr>";
}else{
// PANEN DAN PENGUMPUL =========================================================================
    if(!empty($kegpanen)){
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right>A.</td>";
        $tab.= "<td>".strtoupper($_SESSION['lang']['panen'])."</td><td colspan=18>&nbsp;</td>";
        $tab.= "</tr>";
        $totalpanen=Array();
        $no=1;
        foreach($kegpanen as $keg){
            $tab.= "<tr class=rowcontent>";
            $tab.= "<td align=right>".$no."</td>";
            $tab.= "<td>".$keg['namaakun']."</td>";
            $totalpanen['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpanen['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpanen['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpanen['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpanen['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpanen['132']+=$dzArr[$keg['noakun']]['132'];
            
            $totalpanen['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpanen['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpanen['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpanen['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpanen['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpanen['312']+=$dzArr[$keg['noakun']]['312'];
            
            $totalpanen['811']+=$dzArr[$keg['noakun']]['811'];
            $totalpanen['812']+=$dzArr[$keg['noakun']]['812'];
            $totalpanen['821']+=$dzArr[$keg['noakun']]['821'];
            $totalpanen['822']+=$dzArr[$keg['noakun']]['822'];
            $totalpanen['831']+=$dzArr[$keg['noakun']]['831'];
            $totalpanen['832']+=$dzArr[$keg['noakun']]['832'];
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['111']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['112'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['121']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['122'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['131']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['132'],2)."</td>";
            
            #indra pta
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['811']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['812'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['821']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['822'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['831']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['832'],2)."</td>";
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['211']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['212'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['221']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['222'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['311'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['312'],2)."</td>";
            $tab.= "</tr>";
            $no+=1;
        }
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td colspan=2 align=center>".strtoupper($_SESSION['lang']['biaya']." ".$_SESSION['lang']['panen'])."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['111']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['112'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['121']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['122'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['131']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['132'],2)."</td>";
        

        $tab.= "<td align=right>".numberformat($totalpanen['811']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['812'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['821']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['822'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['831']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['832'],2)."</td>";
        
        $tab.= "<td align=right>".numberformat($totalpanen['211']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['212'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['221']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpanen['222'],2)."</td>";
        @$panen311=100*$totalpanen['221']/$totalpanen['111'];
        @$panen312=100*$totalpanen['221']/$totalpanen['131'];
        $tab.= "<td align=right>".numberformat($panen311,2)."</td>";
        $tab.= "<td align=right>".numberformat($panen312,2)."</td>";
        $tab.= "</tr>";
    }    
// PEMELIHARAAN TM =========================================================================
    if(!empty($kegpanen)){
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right>B.</td>";
        $tab.= "<td>".strtoupper($_SESSION['lang']['pemeltanaman'])." ".strtoupper($_SESSION['lang']['TM'])." </td><td colspan=18>&nbsp;</td>";
        $tab.= "</tr>";
        $totalpemel=Array();
        $no=1;
        foreach($kegpemel as $keg){
            $tab.= "<tr class=rowcontent>";
            $tab.= "<td align=right>".$no."</td>";
            $tab.= "<td>".$keg['namaakun']."</td>";
            $totalpemel['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpemel['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpemel['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpemel['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpemel['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpemel['132']+=$dzArr[$keg['noakun']]['132'];
            $totalpemel['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpemel['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpemel['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpemel['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpemel['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpemel['312']+=$dzArr[$keg['noakun']]['312'];
            
            $totalpemel['811']+=$dzArr[$keg['noakun']]['811'];
            $totalpemel['812']+=$dzArr[$keg['noakun']]['812'];
            $totalpemel['821']+=$dzArr[$keg['noakun']]['821'];
            $totalpemel['822']+=$dzArr[$keg['noakun']]['822'];
            $totalpemel['831']+=$dzArr[$keg['noakun']]['831'];
            $totalpemel['832']+=$dzArr[$keg['noakun']]['832'];
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['111']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['112'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['121']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['122'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['131']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['132'],2)."</td>";
            
            
            #indra pta
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['811']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['812'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['821']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['822'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['831']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['832'],2)."</td>";
            
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['211']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['212'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['221']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['222'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['311'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['312'],2)."</td>";
            $tab.= "</tr>";
            $no+=1;
        }
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td colspan=2 align=center>".strtoupper($_SESSION['lang']['biaya'])." ".strtoupper($_SESSION['lang']['pemeltanaman'])." ".strtoupper($_SESSION['lang']['TM'])." </td>";
        $tab.= "<td align=right>".numberformat($totalpemel['111']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['112'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['121']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['122'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['131']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['132'],2)."</td>";
        
            $tab.= "<td align=right>".numberformat($totalpemel['811']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totalpemel['812'],2)."</td>";
            $tab.= "<td align=right>".numberformat($totalpemel['821']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totalpemel['822'],2)."</td>";
            $tab.= "<td align=right>".numberformat($totalpemel['831']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totalpemel['832'],2)."</td>";
        
        $tab.= "<td align=right>".numberformat($totalpemel['211']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['212'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['221']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalpemel['222'],2)."</td>";
        @$pemel311=100*$totalpemel['221']/$totalpemel['111'];
        @$pemel312=100*$totalpemel['221']/$totalpemel['131'];
        $tab.= "<td align=right>".numberformat($pemel311,2)."</td>";
        $tab.= "<td align=right>".numberformat($pemel312,2)."</td>";
        $tab.= "</tr>";        
    }    
// BIAYA TIDAK LANGSUNG (OVER HEAD) =========================================================================
    if(!empty($kegpanen)){
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right>C.</td>";
        $tab.= "<td>BIAYA TIDAK LANGSUNG (OVER HEAD)</td><td colspan=18>&nbsp;</td>";
        $tab.= "</tr>";
        $totaltidak=Array();
        $no=1;
        foreach($kegtidak as $keg){
            $tab.= "<tr class=rowcontent>";
            $tab.= "<td align=right>".$no."</td>";
            $tab.= "<td>".$keg['namaakun']."</td>";
            $totaltidak['111']+=$dzArr[$keg['noakun']]['111'];
            $totaltidak['112']+=$dzArr[$keg['noakun']]['112'];
            $totaltidak['121']+=$dzArr[$keg['noakun']]['121'];
            $totaltidak['122']+=$dzArr[$keg['noakun']]['122'];
            $totaltidak['131']+=$dzArr[$keg['noakun']]['131'];
            $totaltidak['132']+=$dzArr[$keg['noakun']]['132'];
            $totaltidak['211']+=$dzArr[$keg['noakun']]['211'];
            $totaltidak['212']+=$dzArr[$keg['noakun']]['212'];
            $totaltidak['221']+=$dzArr[$keg['noakun']]['221'];
            $totaltidak['222']+=$dzArr[$keg['noakun']]['222'];
            $totaltidak['311']+=$dzArr[$keg['noakun']]['311'];
            $totaltidak['312']+=$dzArr[$keg['noakun']]['312'];
            
            $totaltidak['811']+=$dzArr[$keg['noakun']]['811'];
            $totaltidak['812']+=$dzArr[$keg['noakun']]['812'];
            $totaltidak['821']+=$dzArr[$keg['noakun']]['821'];
            $totaltidak['822']+=$dzArr[$keg['noakun']]['822'];
            $totaltidak['831']+=$dzArr[$keg['noakun']]['831'];
            $totaltidak['832']+=$dzArr[$keg['noakun']]['832'];
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['111']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['112'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['121']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['122'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['131']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['132'],2)."</td>";
            
            #indra pta
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['811']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['812'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['821']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['822'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['831']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['832'],2)."</td>";
            
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['211']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['212'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['221']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['222'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['311'],2)."</td>";
            $tab.= "<td align=right>".numberformat($dzArr[$keg['noakun']]['312'],2)."</td>";
            $tab.= "</tr>";
            $no+=1;
        }
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td colspan=2 align=center>BIAYA TIDAK LANGSUNG (OVER HEAD)</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['111']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['112'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['121']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['122'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['131']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['132'],2)."</td>";
        
            $tab.= "<td align=right>".numberformat($totaltidak['811']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totaltidak['812'],2)."</td>";
            $tab.= "<td align=right>".numberformat($totaltidak['821']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totaltidak['822'],2)."</td>";
            $tab.= "<td align=right>".numberformat($totaltidak['831']/1000,0)."</td>";
            $tab.= "<td align=right>".numberformat($totaltidak['832'],2)."</td>";
        
        
        $tab.= "<td align=right>".numberformat($totaltidak['211']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['212'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['221']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totaltidak['222'],2)."</td>";
        @$tidak311=100*$totaltidak['221']/$totaltidak['111'];
        @$tidak312=100*$totaltidak['221']/$totaltidak['131'];
        $tab.= "<td align=right>".numberformat($tidak311,2)."</td>";
        $tab.= "<td align=right>".numberformat($tidak312,2)."</td>";
        $tab.= "</tr>";        
    }    
    $totalbiaya['111']=$totalpanen['111']+$totalpemel['111']+$totaltidak['111'];
    $totalbiaya['112']=$totalpanen['112']+$totalpemel['112']+$totaltidak['112'];
    $totalbiaya['121']=$totalpanen['121']+$totalpemel['121']+$totaltidak['121'];
    $totalbiaya['122']=$totalpanen['122']+$totalpemel['122']+$totaltidak['122'];
    $totalbiaya['131']=$totalpanen['131']+$totalpemel['131']+$totaltidak['131'];
    $totalbiaya['132']=$totalpanen['132']+$totalpemel['132']+$totaltidak['132'];
    $totalbiaya['211']=$totalpanen['211']+$totalpemel['211']+$totaltidak['211'];
    $totalbiaya['212']=$totalpanen['212']+$totalpemel['212']+$totaltidak['212'];
    $totalbiaya['221']=$totalpanen['221']+$totalpemel['221']+$totaltidak['221'];
    $totalbiaya['222']=$totalpanen['222']+$totalpemel['222']+$totaltidak['222'];
    $totalbiaya['311']=$totalpanen['311']+$totalpemel['311']+$totaltidak['311'];
    $totalbiaya['312']=$totalpanen['312']+$totalpemel['312']+$totaltidak['312'];
    
    $totalbiaya['811']=$totalpanen['811']+$totalpemel['811']+$totaltidak['811'];
    $totalbiaya['812']=$totalpanen['812']+$totalpemel['812']+$totaltidak['812'];
    $totalbiaya['821']=$totalpanen['821']+$totalpemel['821']+$totaltidak['821'];
    $totalbiaya['822']=$totalpanen['822']+$totalpemel['822']+$totaltidak['822'];
    $totalbiaya['831']=$totalpanen['831']+$totalpemel['831']+$totaltidak['831'];
    $totalbiaya['832']=$totalpanen['832']+$totalpemel['832']+$totaltidak['832'];
    
    
    $tab.= "<tr class=rowcontent>";
    $tab.= "<td colspan=2 align=center>TOTAL ".strtoupper($_SESSION['lang']['biaya'])." ".strtoupper($_SESSION['lang']['produksi'])."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['111']/1000,0)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['112'],2)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['121']/1000,0)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['122'],2)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['131']/1000,0)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['132'],2)."</td>";
    
    
        $tab.= "<td align=right>".numberformat($totalbiaya['811']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalbiaya['812'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalbiaya['821']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalbiaya['822'],2)."</td>";
        $tab.= "<td align=right>".numberformat($totalbiaya['831']/1000,0)."</td>";
        $tab.= "<td align=right>".numberformat($totalbiaya['832'],2)."</td>";
    
    
    $tab.= "<td align=right>".numberformat($totalbiaya['211']/1000,0)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['212'],2)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['221']/1000,0)."</td>";
    $tab.= "<td align=right>".numberformat($totalbiaya['222'],2)."</td>";
    @$biaya311=100*$totalbiaya['221']/$totalbiaya['111'];
    @$biaya312=100*$totalbiaya['221']/$totalbiaya['131'];
    $tab.= "<td align=right>".numberformat($biaya311,2)."</td>";
    $tab.= "<td align=right>".numberformat($biaya312,2)."</td>";
    $tab.= "</tr>";                
}// end of else data empty

$tab.="</tbody></table>";
			
switch($proses)
{
    case'preview':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }
    echo $tab;
    break;

    case'excel':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }

    $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="lbm_biayaroduksi_".$unit.$periode;
    if(strlen($tab)>0)
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
        if(!fwrite($handle,$tab))
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

    case'pdf':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }

            $cols=247.5;
            $wkiri=24;
            $wlain=6;

    class PDF extends FPDF {
    function Header() {
        global $periode;
        global $unit;
        global $optNm;
        global $optBulan;
        global $tahun;
        global $bulan;
        global $dbname;
        global $luas;
        global $wkiri, $wlain,$afdId;
        global $kgbgth, $kgbgbi, $kgbgsd, $kgrebi, $kgresd;
            $width = $this->w - $this->lMargin - $this->rMargin;
  
        $height = 20;
        $this->SetFillColor(220,220,220);
        $this->SetFont('Arial','B',12);

        $this->Cell($width/2,$height,'20. '.strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['produksi']).' (RP./KG)',NULL,0,'L',1);
        $this->Cell($width/2,$height,$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun,NULL,0,'R',1);
        $this->Ln();
        $this->Cell($width,$height,$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")",NULL,0,'L',1);
        if($afdId!='')
        {
            $this->Ln();
        $this->Cell($width,$height,$_SESSION['lang']['afdeling']." : ".$optNm[$afdId]." (".$afdId.")",NULL,0,'L',1);
        }
        $this->Ln();
        $this->Ln();

        $height = 15;
        $this->SetFont('Arial','B',7);
        $this->Cell(3/100*$width+$wkiri/100*$width,$height,'Produksi (kg):',0,0,'R',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgth,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgbi,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgsd,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgrebi,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgresd,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',0,0,'L',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wlain*6/100*$width,$height,$_SESSION['lang']['anggaran'],1,0,'C',1);	
        $this->Cell($wlain*4/100*$width,$height,$_SESSION['lang']['realisasi'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',TRL,0,'C',1);	
        $this->Ln(); 
        $this->Cell(3/100*$width,$height,'No.',RL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,$_SESSION['lang']['pekerjaan'],RL,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['setahun'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['pencapaian'],BRL,0,'C',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,$_SESSION['lang']['setahun'],1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Ln();
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(10,10,'Page '.$this->PageNo()." / {totalPages}",0,0,'L');
    }
}
    //================================

    $pdf=new PDF('L','pt','A4');
	$pdf->AliasNbPages('{totalPages}');
    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 15;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',7);
    
    $no=1;
// pdf array content =========================================================================
//    if(!empty($dzArr))foreach($dzArr as $keg){
//        $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
//        $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[111]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[112],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[121]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[122],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[131]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[132],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[211]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[212],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[221]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[222],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[311],2),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[312],2),1,0,'R',1);	
//        $no+=1;
//        $pdf->Ln();
//    }else echo 'Data Empty.';
   
if(empty($dzArr)){
    echo "Data Empty.";
}else{
// PANEN DAN PENGUMPUL =========================================================================
    if(!empty($kegpanen)){
        $pdf->Cell(3/100*$width,$height,'A.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,strtoupper($_SESSION['lang']['panen']),1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpanen=Array();
        $no=1;
        foreach($kegpanen as $keg){
            $totalpanen['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpanen['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpanen['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpanen['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpanen['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpanen['132']+=$dzArr[$keg['noakun']]['132'];
            $totalpanen['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpanen['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpanen['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpanen['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpanen['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpanen['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['panen']),1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['222'],2),1,0,'R',1);	
        @$panen311=100*$totalpanen['221']/$totalpanen['111'];
        @$panen312=100*$totalpanen['221']/$totalpanen['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($panen311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($panen312,2),1,0,'R',1);	
        $pdf->Ln();
    }    
// PEMELIHARAAN TM =========================================================================
    if(!empty($kegpanen)){
        $pdf->Cell(3/100*$width,$height,'B.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,strtoupper($_SESSION['lang']['pemeltanaman'].' '.$_SESSION['lang']['TM']),1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpemel=Array();
        $no=1;
        foreach($kegpemel as $keg){
            $totalpemel['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpemel['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpemel['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpemel['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpemel['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpemel['132']+=$dzArr[$keg['noakun']]['132'];
            $totalpemel['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpemel['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpemel['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpemel['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpemel['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpemel['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['pemeltanaman'].' '.$_SESSION['lang']['TM']),1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['222'],2),1,0,'R',1);	
        @$pemel311=100*$totalpemel['221']/$totalpemel['111'];
        @$pemel312=100*$totalpemel['221']/$totalpemel['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($pemel311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($pemel312,2),1,0,'R',1);	
        $pdf->Ln();
    } 
// BIAYA TIDAK LANGSUNG (OVER HEAD) =========================================================================
    if(!empty($kegtidak)){
        $pdf->Cell(3/100*$width,$height,'C.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,'BIAYA TIDAK LANGSUNG (OVER HEAD)',1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpemel=Array();
        $no=1;
        foreach($kegtidak as $keg){
            $totaltidak['111']+=$dzArr[$keg['noakun']]['111'];
            $totaltidak['112']+=$dzArr[$keg['noakun']]['112'];
            $totaltidak['121']+=$dzArr[$keg['noakun']]['121'];
            $totaltidak['122']+=$dzArr[$keg['noakun']]['122'];
            $totaltidak['131']+=$dzArr[$keg['noakun']]['131'];
            $totaltidak['132']+=$dzArr[$keg['noakun']]['132'];
            $totaltidak['211']+=$dzArr[$keg['noakun']]['211'];
            $totaltidak['212']+=$dzArr[$keg['noakun']]['212'];
            $totaltidak['221']+=$dzArr[$keg['noakun']]['221'];
            $totaltidak['222']+=$dzArr[$keg['noakun']]['222'];
            $totaltidak['311']+=$dzArr[$keg['noakun']]['311'];
            $totaltidak['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,'BIAYA TIDAK LANGSUNG (OVER HEAD)',1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['222'],2),1,0,'R',1);	
        @$tidak311=100*$totaltidak['221']/$totaltidak['111'];
        @$tidak312=100*$totaltidak['221']/$totaltidak['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($tidak311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($tidak312,2),1,0,'R',1);	
        $pdf->Ln();
    } 
    $totalbiaya['111']=$totalpanen['111']+$totalpemel['111']+$totaltidak['111'];
    $totalbiaya['112']=$totalpanen['112']+$totalpemel['112']+$totaltidak['112'];
    $totalbiaya['121']=$totalpanen['121']+$totalpemel['121']+$totaltidak['121'];
    $totalbiaya['122']=$totalpanen['122']+$totalpemel['122']+$totaltidak['122'];
    $totalbiaya['131']=$totalpanen['131']+$totalpemel['131']+$totaltidak['131'];
    $totalbiaya['132']=$totalpanen['132']+$totalpemel['132']+$totaltidak['132'];
    $totalbiaya['211']=$totalpanen['211']+$totalpemel['211']+$totaltidak['211'];
    $totalbiaya['212']=$totalpanen['212']+$totalpemel['212']+$totaltidak['212'];
    $totalbiaya['221']=$totalpanen['221']+$totalpemel['221']+$totaltidak['221'];
    $totalbiaya['222']=$totalpanen['222']+$totalpemel['222']+$totaltidak['222'];
    $totalbiaya['311']=$totalpanen['311']+$totalpemel['311']+$totaltidak['311'];
    $totalbiaya['312']=$totalpanen['312']+$totalpemel['312']+$totaltidak['312'];
    $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,'TOTAL '.strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['produksi']),1,0,'C',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['111']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['112'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['121']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['122'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['131']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['132'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['211']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['212'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['221']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['222'],2),1,0,'R',1);	
    @$biaya311=100*$totalbiaya['221']/$totalbiaya['111'];
    @$biaya312=100*$totalbiaya['221']/$totalbiaya['131'];
    $pdf->Cell($wlain/100*$width,$height,numberformat($biaya311,2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($biaya312,2),1,0,'R',1);	
    $pdf->Ln();
}// end of else data empty    
    $pdf->Output();	 
    break;

    default:
    break;
}
	
?>
