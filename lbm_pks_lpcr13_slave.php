<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$_POST['unit']==''?$unit=$_GET['unit']:$unit=$_POST['unit'];
$_POST['periode']==''?$periode=$_GET['periode']:$periode=$_POST['periode'];
$_POST['judul']==''?$judul=$_GET['judul']:$judul=$_POST['judul'];
if($unit==''||$periode==''){
    exit("Warning: Field Tidak Boleh Kosong");
}
$qwe=explode('-',$periode); $tahun=$qwe[0]; $bulan=$qwe[1];
$tgl_awal=$periode."-01";
$lastday =date('t',strtotime($tgl_awal));
$tgl_akhir=$periode."-".$lastday;
$tanggalkemarin=date('Y-m-d',strtotime('-1 days',$tgl_awal));
$thn_awal=substr($periode,0,4)."-01-01 00:00:00";
$thn_akhir=$tanggalkemarin." 23:59:59";
$optNm=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
$optpt=makeOption($dbname, 'organisasi','kodeorganisasi,induk');
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

//ambil data sd bulan ini
/*
$sMill="select millcode,SUM(beratbersih) as netto
		from ".$dbname.".vw_pabrik_timbangan_7ke7 where kodebarang='40000003' 
		and millcode='".$unit."' and tanggal>='".$thn_awal."' and tanggal<='".$tgl_akhir."'
		";
//exit("Warning: ".$sMill);
$qMill=mysql_query($sMill) or die(mysql_error($conn));
$row=mysql_num_rows($qMill);
if($row==0){
    exit("Warning: Data Kosong");
}
while($rMill=mysql_fetch_assoc($qMill)){
    $netto_sdTBS=$rMill['netto'];
}
*/
//ambil data bulan ini
$sMill="select left(tanggal,10) as tanggal,if(kodeorg='','ZZZZ',kodeorg) as kodeorg,substr(nospb,9,6) as divisi,SUM(beratbersih) as netto
		from ".$dbname.".vw_pabrik_timbangan_7ke7 where kodebarang='40000003' 
		and millcode='".$unit."' and tanggal like '".$periode."%'
		GROUP BY left(tanggal,10),kodeorg,substr(nospb,9,6)
		order by left(tanggal,10),kodeorg,substr(nospb,9,6)";
//exit("Warning: ".$sMill);
$qMill=mysql_query($sMill) or die(mysql_error($conn));
$row=mysql_num_rows($qMill);
if($row==0){
    exit("Warning: Data Kosong");
}
while($rMill=mysql_fetch_assoc($qMill)){
    $kodeorg[$rMill['kodeorg']]=$rMill['kodeorg'];
    $divisi[$rMill['divisi']]=$rMill['divisi'];
    $netto_Div[$rMill['tanggal'].$rMill['divisi']]=$rMill['netto'];
    $netto_Org[$rMill['tanggal'].$rMill['kodeorg']]+=$rMill['netto'];
    $ttnetto_Div[$rMill['divisi']]+=$rMill['netto'];
    $ttnetto_Org[$rMill['kodeorg']]+=$rMill['netto'];
	$netto_TBS[$rMill['tanggal']]+=$rMill['netto'];
	$ttnetto_TBS+=$rMill['netto'];
	if($rMill['divisi']==''){
	    $netto_Luar[$rMill['tanggal']]+=$rMill['netto'];
	    $ttnetto_Luar+=$rMill['netto'];
	}else{
	    $netto_PT[$rMill['tanggal']]+=$rMill['netto'];
	    $ttnetto_PT+=$rMill['netto'];
	}
	if(substr($rMill['divisi'],2,2)=='PE'){
	    $ttnetto_Plasma+=$rMill['netto'];
	}
}

if($proses=='excel'){
	//$bg="bgcolor=#DEDEDE";
	$bg="";
	$brdr=1;
	$tab.="<table border=0>
			<tr>
				<td colspan=6 align=left><font size=3>".$judul."</font></td>
			</tr> 
			<tr> 
				<td colspan=6 align=left>".$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun."</td>
			</tr> 
			<tr>
				<td colspan=6 align=left>".$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")</td>
			</tr>   
		</table>";
}else{ 
    $bg="";
    $brdr=0;
}
    $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
	    <td align=center rowspan=1 ".$bg.">Tanggal</td>";
	$namaunit='';
	foreach($divisi as $xdiv){
		if($namaunit!=substr($xdiv,0,4) and $namaunit!=''){
			$tab.="<td align=center rowspan=1 ".$bg.">Total ".$namaunit."</td>";
		}
		$namaunit=substr($xdiv,0,4);
		if(substr($xdiv,0,6)=='CKPE01'){
			$xdiv='G2';
		}else if(substr($xdiv,0,6)=='CKPE02'){
			$xdiv='G1';
		}else if(substr($xdiv,0,6)=='CKPE03'){
			$xdiv='B1';
		}else if(substr($xdiv,0,6)=='CKPE04'){
			$xdiv='B2';
		}
		if(substr($xdiv,0,4)!=''){
			$tab.="<td align=center rowspan=1 ".$bg.">".$xdiv."</td>";
		}
	}
	if($namaunit!=''){
		$tab.="<td align=center rowspan=1 ".$bg.">Total ".$namaunit."</td>";
	}
	$tab.="<td align=center rowspan=1 ".$bg.">Total INTI</td>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total ".$optpt[$unit]."</td>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total Luar</td>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total TBS</td>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total TBS SD</td>";
	$tab.="</tr>";
	$tab.="</thead><tbody>";
	for ($x=1;$x<=$lastday;$x++){
		$xtgl=$periode.'-'.sprintf("%02d",$x);
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center rowspan=1 ".$bg.">".$xtgl."</td>";
		$namaunit='';
		foreach($divisi as $xdiv){
			if($namaunit!=substr($xdiv,0,4) and $namaunit!=''){
				$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_Org[$xtgl.$namaunit],0,'.',',')."</td>";
			}
			$namaunit=substr($xdiv,0,4);
			if(substr($xdiv,0,4)!=''){
				$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_Div[$xtgl.$xdiv],0,'.',',')."</td>";
			}
		}
		if($namaunit!=''){
			$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_Org[$xtgl.$namaunit],0,'.',',')."</td>";
		}
		$netto_sdTBS+=$netto_TBS[$xtgl];
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format(($netto_PT[$xtgl]-$netto_Plasma[$xtgl]),0,'.',',')."</td>";
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_PT[$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_Luar[$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_TBS[$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_sdTBS,0,'.',',')."</td>";
		$tab.="</tr>";
	}
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total</td>";
	$namaunit='';
	foreach($divisi as $xdiv){
		if($namaunit!=substr($xdiv,0,4) and $namaunit!=''){
			$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_Org[$namaunit],0,'.',',')."</td>";
		}
		$namaunit=substr($xdiv,0,4);
		if(substr($xdiv,0,4)!=''){
			$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_Div[$xdiv],0,'.',',')."</td>";
		}
	}
	if($namaunit!=''){
		$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_Org[$namaunit],0,'.',',')."</td>";
	}
	$tab.="<td align=right rowspan=1 ".$bg.">".number_format(($ttnetto_PT-$ttnetto_Plasma),0,'.',',')."</td>";
	$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_PT,0,'.',',')."</td>";
	$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_Luar,0,'.',',')."</td>";
	$tab.="<td align=right rowspan=1 ".$bg.">".number_format($ttnetto_TBS,0,'.',',')."</td>";
	$tab.="<td align=right rowspan=1 ".$bg.">".number_format($netto_sdTBS,0,'.',',')."</td>";
	$tab.="</tr>";
	$tab.="</tbody></table>";

switch($proses)
{
    case'preview':
    echo $tab;
    break;

    case'excel':
    $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_=$judul."_".$unit."_".$periode;
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

    default:
    break;
}
	
?>
