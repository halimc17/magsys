<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];

$kdorg=$_POST['kdorg'];
$per=$_POST['per'];
if($proses=='excel')
{
    $kdorg=$_GET['kdorg'];
    $per=$_GET['per'];
}



$akunsort=" and (left(noakun,1)='7' or left(noakun,2)='63')";
$namaakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}


// echo"<pre>";
// print_r($_SESSION['empl']);
// echo"</pre>";


$stream.="<thead>
    <tr class=rowheader>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kotoran']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['noakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namaakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jumlah']."</td> 
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['beban']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."</td>    
       ";
$stream.="</tr>";
$stream.="</thead>";




$iOrga="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdorg."' ";

$nOrga=  mysql_query($iOrga) or die (mysql_error($conn));
while($dOrga=  mysql_fetch_assoc($nOrga))
{
    $org[$dOrga['kodeorganisasi']]=$dOrga['kodeorganisasi'];
    $nmorg[$dOrga['kodeorganisasi']]=$dOrga['namaorganisasi'];
    
}


$iOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdorg."' ";
//echo $iOrg;
$nOrg=  mysql_query($iOrg) or die (mysql_error($conn));
while($dOrg=  mysql_fetch_assoc($nOrg))
{
    $org[$dOrg['kodeorganisasi']]=$dOrg['kodeorganisasi'];
    $nmorg[$dOrg['kodeorganisasi']]=$dOrg['namaorganisasi'];
    
}

$org['']='';




##akun digit 5
$iAkunHead="select substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok "
        . " from ".$dbname.".keu_jurnaldt_vw where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." "
        . " group by substr(noakun,1,5),substr(kodeblok,1,6) order by noakun";

//echo $iAkunHead;
$nAkunHead=  mysql_query($iAkunHead) or die (mysql_error($conn));
while($dAkunHead=  mysql_fetch_assoc($nAkunHead))
{
    $akunhead[$dAkunHead['noakunhead']]=$dAkunHead['noakunhead'];
    $listakunhead[$dAkunHead['kodeblok']][$dAkunHead['noakunhead']]=$dAkunHead['noakunhead'];
}


/*
$iAkun="select distinct(noakun) as noakun from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' order by noakun";
  */

//SUBSTR(pub_name,4,5)
$iAkun="select distinct(noakun) as noakun,substr(kodeblok,1,6) as kodeblok from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." order by noakun,substr(kodeblok,1,6)";
$nAkun=  mysql_query($iAkun) or die (mysql_error($conn));
while($dAkun=  mysql_fetch_assoc($nAkun))
{
    $akun[$dAkun['noakun']]=$dAkun['noakun'];
    //$akun[$dAkun['kodeblok']][$dAkun['noakun']]=$dAkun['noakun'];
}

##ambil biaya yang tidak dibebankan di HO
$iData="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok,kodeorg from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." "
        . "  and nojurnal not in (select nojurnal from ".$dbname.".keu_jurnaldt_vw where noakun='1210102') "
        . "  group by substr(kodeblok,1,6),noakun  order by noakun";
//echo $iData;
$nData=  mysql_query($iData) or die (mysql_error($conn));
while($dData=  mysql_fetch_assoc($nData))
{
    $akundata[$dData['kodeblok']][$dData['noakunhead']][$dData['noakun']]=$dData['noakun'];
    $jumlah[$dData['kodeblok']][$dData['noakunhead']][$dData['noakun']][1]=$dData['jumlah']; 
}


#ambil beban yang dibebankan ke HO
$iHo="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok,kodeorg from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." "
        . "  and nojurnal in (select nojurnal from ".$dbname.".keu_jurnaldt_vw where noakun='1210102') "
        . "  group by substr(kodeblok,1,6),noakun  order by noakun";
$nHo=  mysql_query($iHo) or die (mysql_error($conn));
while($dHo=  mysql_fetch_assoc($nHo))
{
    $akundata[$dHo['kodeblok']][$dHo['noakunhead']][$dHo['noakun']]=$dHo['noakun'];
    $jumlah[$dHo['kodeblok']][$dHo['noakunhead']][$dHo['noakun']][2]=$dHo['jumlah']; 
}




/*echo"<pre>";
print_r($jumlah);
echo"</pre>";*/
//exit();



//blok~akun 5~akun 7

//  bgcolor=#66FF00
$grandTotal[1]=0;
$grandTotal[2]=0;
foreach ($org as $kdorg)
{
    $subTotal[$kdorg][1]=0;
    $subTotal[$kdorg][2]=0;
    if($kdorg=='')
    {
        $nmorg[$kdorg]='Lain-Lain';
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=6><b>".$kdorg." - ".$nmorg[$kdorg]."</b></td>";
    $stream.="</tr>";	
    if(is_array($akunhead)){
		foreach($akunhead as $akunjudul)
		{
			if($listakunhead[$kdorg][$akunjudul]!='')
			{
				$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=6 bgcolor=#00FFFF>".$listakunhead[$kdorg][$akunjudul]." - ".$namaakn[$listakunhead[$kdorg][$akunjudul]]."</td>"; 
				$stream.="</tr>";
			
				foreach ($akun as $noakun)
				{
					if($akundata[$kdorg][$akunjudul][$noakun]!='')
					{
						//setIt($jumlah[$kdorg][$akunjudul][$noakun][1],0);
						//setIt($jumlah[$kdorg][$akunjudul][$noakun][2],0);
						$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=lihatDetail('".$noakun."','".$kdorg."','".$per."','html',event)>";
						$stream.="<td></td>";
						$stream.="<td>".$akundata[$kdorg][$akunjudul][$noakun]."</td>";
						$stream.="<td>".$namaakun[$akundata[$kdorg][$akunjudul][$noakun]]."</td>";
						$stream.="<td align=right>".number_format($jumlah[$kdorg][$akunjudul][$noakun][1],2)."</td>";
						$stream.="<td align=right>".number_format($jumlah[$kdorg][$akunjudul][$noakun][2],2)."</td>";
						$stream.="<td align=right>".number_format($jumlah[$kdorg][$akunjudul][$noakun][1]+$jumlah[$kdorg][$akunjudul][$noakun][2],2)."</td>"; 
						$stream.="</tr>";
					}
					$total[$kdorg][$akunjudul][1]+=$jumlah[$kdorg][$akunjudul][$noakun][1];
					$total[$kdorg][$akunjudul][2]+=$jumlah[$kdorg][$akunjudul][$noakun][2];
				}
				$stream.="<tr class=rowcontent>";#00FFFF
				$stream.="<td bgcolor=#00CCFF colspan=3 align=right>Total</td>";
				$stream.="<td bgcolor=#00CCFF align=right>".number_format($total[$kdorg][$akunjudul][1],2)."</td>"; 
				$stream.="<td bgcolor=#00CCFF align=right>".number_format($total[$kdorg][$akunjudul][2],2)."</td>"; 
				$stream.="<td bgcolor=#00CCFF align=right>".number_format($total[$kdorg][$akunjudul][1]+$total[$kdorg][$akunjudul][2],2)."</td>";
				$stream.="</tr>";
			}
			$subTotal[$kdorg][1]+=$total[$kdorg][$akunjudul][1];
			$subTotal[$kdorg][2]+=$total[$kdorg][$akunjudul][2];
			
		}
	}
    $grandTotal[1]+=$subTotal[$kdorg][1];
    $grandTotal[2]+=$subTotal[$kdorg][2];
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=3 align=right  bgcolor=#0099FF>Sub Total</td>";
    $stream.="<td align=right bgcolor=#0099FF>".number_format($subTotal[$kdorg][1],2)."</td>"; 
    $stream.="<td align=right bgcolor=#0099FF>".number_format($subTotal[$kdorg][2],2)."</td>"; 
    $stream.="<td align=right bgcolor=#0099FF>".number_format($subTotal[$kdorg][1]+$subTotal[$kdorg][2],2)."</td>"; 
    $stream.="</tr>";       
    
}
$stream.="<thead><tr class=rowheader>";
$stream.="<td colspan=3 align=right>Grand Total</td>";
$stream.="<td align=right>".number_format($grandTotal[1],2)."</td>"; 
$stream.="<td align=right>".number_format($grandTotal[2],2)."</td>"; 
$stream.="<td align=right>".number_format($grandTotal[1]+$grandTotal[2],2)."</td>"; 
$stream.="</tr></thead>";   
//$stream.="<tr class=rowcontent>
//$stream.="

$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="Mill_Cost".$kdorg."_".$per1."_sd_".per2;
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
}
?>