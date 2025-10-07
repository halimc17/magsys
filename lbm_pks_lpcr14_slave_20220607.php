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

//ambil data budget bulan ini
$sBgtMill="select DISTINCT millcode,oerbunch,oerkernel from ".$dbname.".bgt_produksi_pks where millcode='".$unit."' and tahunbudget='".substr($periode,0,4)."'";
//exit("Warning: ".$sBgtMill);
$qBgtMill=mysql_query($sBgtMill) or die(mysql_error($conn));
while($rBgtMill=mysql_fetch_assoc($qBgtMill)){
	$stdoer=$rBgtMill['oerbunch'];
	$stdker=$rBgtMill['oerkernel'];
}

//ambil data bulan ini
$sMill="select * from ".$dbname.".pabrik_produksi where kodeorg='".$unit."' and tanggal like '".$periode."%'
		order by kodeorg,tanggal";
//exit("Warning: ".$sMill);
$qMill=mysql_query($sMill) or die(mysql_error($conn));
$row=mysql_num_rows($qMill);
if($row==0){
    exit("Warning: Data Kosong");
}
while($rMill=mysql_fetch_assoc($qMill)){
    $kodeorg[$rMill['kodeorg']]=$rMill['kodeorg'];
    $tbsdiolah[$rMill['kodeorg'].$rMill['tanggal']]=$rMill['tbsdiolah'];
    $sisahariini[$rMill['kodeorg'].$rMill['tanggal']]=$rMill['sisahariini'];
    $oer[$rMill['kodeorg'].$rMill['tanggal']]=$rMill['oer'];
    $exoer[$rMill['kodeorg'].$rMill['tanggal']]=($rMill['tbsdiolah']==0 ? 0 : $rMill['oer']/$rMill['tbsdiolah']*100);
    $oerpk[$rMill['kodeorg'].$rMill['tanggal']]=$rMill['oerpk'];
    $exoerpk[$rMill['kodeorg'].$rMill['tanggal']]=($rMill['tbsdiolah']==0 ? 0 : $rMill['oerpk']/$rMill['tbsdiolah']*100);
	if(substr($rMill['tanggal'],8,2)=='01'){
		$sisatbskemarin=$rMill['sisatbskemarin'];
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
	    <td align=center rowspan=2 ".$bg.">Tanggal</td>
	    <td align=center colspan=2 ".$bg.">TBS DIOLAH</td>
	    <td align=center colspan=3 ".$bg.">VERTICAL STERILIZER</td>
	    <td align=center rowspan=2 ".$bg.">Sisa</td>
	    <td align=center colspan=2 ".$bg.">CPO DIPEROLEH</td>
	    <td align=center colspan=2 ".$bg.">CPO EXTRAKSI</td>
	    <td align=center colspan=2 ".$bg.">KERNEL DIPEROLEH</td>
	    <td align=center colspan=2 ".$bg.">KERNEL EXTRAKSI</td>
    </tr>
    <tr>
	    <td align=center".$bg.">HI</td>
	    <td align=center".$bg.">s/d HI</td>
	    <td align=center".$bg.">DIOLAH</td>
	    <td align=center".$bg.">RESTAN</td>
	    <td align=center".$bg.">CAPST</td>
	    <td align=center".$bg.">HI</td>
	    <td align=center".$bg.">s/d HI</td>
	    <td align=center".$bg.">HI</td>
	    <td align=center".$bg.">s/d HI</td>
	    <td align=center".$bg.">HI</td>
	    <td align=center".$bg.">s/d HI</td>
	    <td align=center".$bg.">HI</td>
	    <td align=center".$bg.">s/d HI</td>
	</tr>";
	$tab.="</thead><tbody>";
	$tab.="<tr class=rowcontent>";
	for ($x=1;$x<=6;$x++){
		$tab.="<td align=center".$bg."></td>";
	}
	$tab.="<td align=right bgcolor='yellow' ".$bg.">".number_format($sisatbskemarin,0,'.',',')."</td>";
	for ($x=1;$x<=8;$x++){
		$tab.="<td align=center".$bg."></td>";
	}
	$tab.="</tr>";
	for ($x=1;$x<=$lastday;$x++){
		$xtgl=$periode.'-'.sprintf("%02d",$x);
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center".$bg.">".$xtgl."</td>";
		$sdhitbsdiolah+=$tbsdiolah[$unit.$xtgl];
		$sdhioer+=$oer[$unit.$xtgl];
		$sdhioerpk+=$oerpk[$unit.$xtgl];
		$tab.="<td align=right ".$bg.">".number_format($tbsdiolah[$unit.$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($sdhitbsdiolah,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($tbsdiolah[$unit.$xtgl]/20000,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($sisahariini[$unit.$xtgl]/20000,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format(20000,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($sisahariini[$unit.$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($oer[$unit.$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($sdhioer,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($exoer[$unit.$xtgl],2,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format(($sdhitbsdiolah==0 ? 0 : $sdhioer/$sdhitbsdiolah*100),2,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($oerpk[$unit.$xtgl],0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($sdhioerpk,0,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format($exoerpk[$unit.$xtgl],2,'.',',')."</td>";
		$tab.="<td align=right ".$bg.">".number_format(($sdhitbsdiolah==0 ? 0 : $sdhioerpk/$sdhitbsdiolah*100),2,'.',',')."</td>";
		$tab.="</tr>";
		$gttbsdiolah+=$tbsdiolah[$unit.$xtgl];
		$gtoer+=$oer[$unit.$xtgl];
		$gtoerpk+=$oerpk[$unit.$xtgl];
		$gtsisahariini=$sisahariini[$unit.$xtgl];
	}
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center rowspan=1 ".$bg.">Total</td>";
	$tab.="<td align=right ".$bg.">".number_format($gttbsdiolah,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($sdhitbsdiolah,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($gttbsdiolah/20000,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($gtsisahariini/20000,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(20000,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($gtsisahariini,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($gtoer,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($sdhioer,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(($gttbsdiolah==0 ? 0 : $gtoer/$gttbsdiolah*100),2,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(($sdhitbsdiolah==0 ? 0 : $sdhioer/$sdhitbsdiolah*100),2,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($gtoerpk,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($sdhioerpk,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(($gttbsdiolah==0 ? 0 : $gtoerpk/$gttbsdiolah*100),2,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(($sdhitbsdiolah==0 ? 0 : $sdhioerpk/$sdhitbsdiolah*100),2,'.',',')."</td>";
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center rowspan=1 ".$bg.">STD</td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg.">".number_format(20000,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format(0,0,'.',',')."</td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg.">".number_format($stdoer,2,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($stdoer,2,'.',',')."</td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg."></td>";
	$tab.="<td align=right ".$bg.">".number_format($stdker,2,'.',',')."</td>";
	$tab.="<td align=right ".$bg.">".number_format($stdker,2,'.',',')."</td>";
	$tab.="</tr>";
	$tab.="</tbody></table>";

switch($proses){
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
