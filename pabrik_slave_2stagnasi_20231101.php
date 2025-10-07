<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$tgl_1 = checkPostGet('tgl1','');
$tgl_2 = checkPostGet('tgl2','');
$tanggl = checkPostGet('tglRe','');
$kdPabrik = checkPostGet('kdPabrik','');
$dwnStatus = checkPostGet('dwnStatus','');

if($tgl_1==''||$tgl_2==''){
    exit("Error:Date required");
}
if($kdPabrik==''){
    exit("Error: Mill code required");
}
if(strlen($tgl_1)!=10||strlen($tgl_2)!=10){
    exit("Error: Invalid date format");
}
$stad="";
if($dwnStatus!=''){
     if ($dwnStatus=='0')
    {
        $stad=" and downstatus=''";
    }
    else 
    {
        $stad=" and downstatus='".$dwnStatus."'";
    }
        

     
}

    
    
$tgl1=$tgl_1;
$tgl22=$tgl_2;
$optSupp=makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier');
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arrdwn=array("EDT"=>"EDT : Breakdown","SDT"=>"SDT : Stagnasi","CDT"=>"CDT : Commercial Downtime");
$tgl=explode("-",$tgl_1);
$tgl_1=$tgl[2]."-".$tgl[1]."-".$tgl[0];
$tgl2=explode("-",$tgl_2);
$tgl_2=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];


$dzArr=array();
$kmrn=strtotime ('-1 day',strtotime ($tgl_1));
$kmrn=date ('Y-m-d', $kmrn );

function dates_inbetween($date1, $date2)
{
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++)
	{
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}

	if(($tgl_1!='')&&($tgl_2!=''))
	{	
		$tgl1=tanggalsystem($tgl1);
		$tgl22=tanggalsystem($tgl22);
	}
	$test = dates_inbetween($tgl1, $tgl22);

        
$brdr=0;
$bgclr="";
if($proses=='excel')
{
    $bgclr=" bgcolor=#DEDEDE";
    $brdr=1;
}        
$tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
    $tab.="<tr>";
    $tab.="<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>";
    $tab.="<td align=center ".$bgclr.">Kode ".$_SESSION['lang']['station']."</td>";
    $tab.="<td align=center ".$bgclr.">Nama ".$_SESSION['lang']['station']."</td>";
    $tab.="<td align=center ".$bgclr.">Kode ".$_SESSION['lang']['mesin']."</td>";
    $tab.="<td align=center ".$bgclr.">".$_SESSION['lang']['nmmesin']."</td>";
    $tab.="<td align=center  ".$bgclr.">".$_SESSION['lang']['jumlahjamstagnasi']."</td>";
    $tab.="<td align=center  ".$bgclr.">".$_SESSION['lang']['downstatus']."</td>";
    
    $tab.="<td align=center  ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>";
    $tab.="</tr></thead><tbody>";        
        
$sData="select b.tahuntanam,b.downstatus,a.tanggal,b.kodeorg,b.jamstagnasi,keterangan 
		FROM ".$dbname.".`pabrik_pengolahanmesin` b 
		left join ".$dbname.".pabrik_pengolahan a on a.nopengolahan=b.nopengolahan where 
        b.kodeorg like '".$kdPabrik."%' and a.tanggal between '".$tgl_1."' and '".$tgl_2."'
        ".$stad." order by tanggal asc";

$qData=mysql_query($sData) or die(mysql_error($conn));


while($rData=  mysql_fetch_assoc($qData)){
 

            $tab.="<tr class=rowcontent>";
                    if($proses=='excel'){
                            $tab.="<td>".$rData['tanggal']."</td>";
                    }else{
                            $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                    }
                    $tab.="<td align=right>".$rData['kodeorg']."</td>";
                    $tab.="<td align=left>".$optNmOrg[$rData['kodeorg']]."</td>";
                    $tab.="<td align=right>".$rData['tahuntanam']."</td>";
                    $tab.="<td align=left>".$optNmOrg[$rData['tahuntanam']]."</td>";
                    $tab.="<td align=right>".$rData['jamstagnasi']."</td>";
                    $tab.="<td align=left>".$arrdwn[$rData['downstatus']]."</td>";
                    $tab.="<td align=left>".$rData['keterangan']."</td>";
                    $tab.="</tr>"; 
                    $totStag+=$rData['jamstagnasi'];
    
}
$tab.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']."</td>";
$tab.="<td align=right>".number_format($totStag,2)."</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
$tab.="</tbody></table>";



/*while($rData=  mysql_fetch_assoc($qData)){
  $dtTgl[$rData['tanggal']]=$rData['tanggal'];
  $dtKdOrg[$rData['kodeorg']]=$rData['kodeorg'];
  $dtJamStag[$rData['tanggal'].$rData['kodeorg']]=$rData['jamstagnasi'];
  $dtKet[$rData['tanggal'].$rData['kodeorg']]=$rData['keterangan'];
}

$brdr=0;
$bgclr="";
if($proses=='excel')
{
    $bgclr=" bgcolor=#DEDEDE";
    $brdr=1;
}
if($proses=='preview'||$proses=='excel')
{
$tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
$tab.="<tr>";
$tab.="<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>";
$tab.="<td align=center ".$bgclr.">".$_SESSION['lang']['nmmesin']."</td>";
$tab.="<td align=center  ".$bgclr.">".$_SESSION['lang']['jumlahjamstagnasi']."</td>";
$tab.="<td align=center  ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>";
$tab.="</tr></thead><tbody>";
$ared=$totStag=0;
$tgl=1;
foreach($dtTgl as $lstTgl =>$TglDt){
   foreach($dtKdOrg as $dtKodeorg=>$lstKodeorg){
	$tab.="<tr class=rowcontent>";
		if($proses=='excel'){
			$tab.="<td>".$TglDt."</td>";
		}else{
			$tab.="<td>".tanggalnormal($TglDt)."</td>";
		}
		$tab.="<td>".$optNm[$lstKodeorg]."</td>";
		$tab.="<td align=right>".$dtJamStag[$TglDt.$lstKodeorg]."</td>";
		$tab.="<td>".$dtKet[$TglDt.$lstKodeorg]."</td>";
		$tab.="</tr>"; 
		$totStag+=$dtJamStag[$TglDt.$lstKodeorg];
	}
}
$tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['total']."</td>";
$tab.="<td align=right>".number_format($totStag,2)."</td><td>&nbsp;</td></tr>";
$tab.="</tbody></table>";
}
 */
switch($proses)
{
	case'preview':
	echo $tab;
	break;
	
	case'excel':

			
                $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                $tglSkrg=date("Ymd");
                $nop_="stagnasi_report_".$kdPabrik;
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
        case'getKodeorg':
        $optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($tipeIntex==1)
	{
		//$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk ='PMO' order by namaorganisasi asc";
	}
	elseif($tipeIntex==0)
	{
		$sOrg="SELECT namasupplier,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE substring(kodekelompok,1,1)='S' and kodetimbangan!='NULL' order by namasupplier asc";//echo "warning:".$sOrg;
	}
	elseif($tipeIntex==2)
	{
		//$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk <>'PMO' order by namaorganisasi asc";
	}
	//echo "warning".$sOrg;exit();
        if($tipeIntex!=3)
        {
            $qOrg=mysql_query($sOrg) or die(mysql_error());
            while($rOrg=mysql_fetch_assoc($qOrg))
            {
                    if($tipeIntex!=0)
                    {
                            $optorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
                    }
                    else
                    {
                            $optorg.="<option value=".$rOrg['kodetimbangan'].">".$rOrg['namasupplier']."</option>";
                    }
            }
        }
	echo $optorg;
        break;
	default:
	break;
}
?>
