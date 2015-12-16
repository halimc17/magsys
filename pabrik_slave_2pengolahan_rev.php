<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$kdpabrik=$_POST['kdpabrik'];
$tgl1=tanggalsystemn($_POST['tgl1']);
$tgl2=tanggalsystemn($_POST['tgl2']);
if(($proses=='excel')or($proses=='pdf'))
{
    $tgl1=tanggalsystemn($_GET['tgl1']);
    $tgl2=tanggalsystemn($_GET['tgl2']);
    $kdpabrik=$_GET['kdpabrik'];
}

$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'log_5supplier','supplierid,namasupplier');






if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{

    if(($tgl1=='')or($tgl2==''))
	{
		echo"Error: Tanggal tidak boleh kosong"; 
		exit;
    }

    else if($tgl1>$tgl2)
	{
        echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
		exit;
    }
	
}

$pt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$kdpt=$pt[$kdpabrik];






    if($proses=='excel')
    {
        $stream="<table cellspacing='1' border='1' class='sortable'>";
    }
    else 
    {
        $stream.="<table cellspacing='1' border='0' class='sortable'>";
    }
    $stream.="<thead class=rowheader>
            <tr>
              <td align=center>".$_SESSION['lang']['nourut']."</td>
              <td align=center>".$_SESSION['lang']['tanggal']."</td>
              <td align=center>".$_SESSION['lang']['downtime']."</td>
              <td align=center>".$_SESSION['lang']['jamoperasional']."</td>
              <td align=center>".$_SESSION['lang']['jumlahlori']."</td>
              <td align=center>".$_SESSION['lang']['tbsdiolah']."</td>
              <td align=center>".$_SESSION['lang']['cpokuantitas']."</td>
              <td align=center>".$_SESSION['lang']['kernel']."</td>";
    if($proses=='preview')
    {
    $stream.="
              <td align=center>".$_SESSION['lang']['detail']."</td> ";
    }
    $stream.="
            </tr>
        </thead>
    <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
##bentuk array
    
$cols = "a.tanggal,sum(jamstagnasi) as jamstagnasi,sum(jamdinasbruto) as jamdinasbruto,"
        . " sum(jumlahlori) as jumlahlori,"
        . " sum(a.tbsdiolah) as tbsdiolah,sum(oer) as cpo,sum(oerpk) as pk,nopengolahan,"
        . " sum(a.jamdinasbruto) as jamdinasbruto";
//$where = "a.kodeorg='".$kdpabrik."' and a.tanggal='2015-01-05' ";// and a.tanggal='2015-01-04'
	
$where = "a.kodeorg='".$kdpabrik."' and a.tanggal between '".$tgl1."' and '".$tgl2."' ";// and a.tanggal='2015-01-04'
// $query = selectQuery($dbname,'pabrik_pengolahan',$cols,$where)." group by tanggal";
$iList="select distinct ".$cols." from ".$dbname.".pabrik_pengolahan a left join ".$dbname.".pabrik_produksi b 
                 on (a.kodeorg=b.kodeorg and a.tanggal=b.tanggal) where ".$where." group by a.tanggal";



$nList=  mysql_query($iList) or die (mysql_error($conn));
while($dList=  mysql_fetch_assoc($nList))
{   
    $jamstagnasi[$dList['tanggal']]=$dList['jamstagnasi'];
    $jamoprasional[$dList['tanggal']]=$dList['jamdinasbruto'];
    $jumlahlori[$dList['tanggal']]=$dList['jumlahlori'];
    $tbsdiolah[$dList['tanggal']]=$dList['tbsdiolah'];
    $cpo[$dList['tanggal']]=$dList['cpo'];
    $pk[$dList['tanggal']]=$dList['pk'];
    $nopengolahan[$dList['tanggal']]=$dList['nopengolahan'];
    
}


$listtanggal = rangeTanggal($tgl1, $tgl2);




function fixjam ($jam,$menit)
{    
    if(strlen($menit)==1)
    {
        $menit=$menit*10;
    }
    else
    {
        $menit=$menit;
    }
    
    $menitnya=number_format($menit/100*60);
    
   
    if(strlen($menitnya==0))
    {
        $menitnya=$menitnya.'0';
    }
    
    
    return $jam.':'.$menitnya;
}







foreach ($listtanggal as $ar => $tgl)
{
    

    $formatjam=  explode(".", $jamoprasional[$tgl]);
    
    
    if(fixjam($formatjam[0],$formatjam[1])==':00' || fixjam($formatjam[0],$formatjam[1])=='0:00')
    {
        $isijam="";
    }
    else
    {
        $isijam=fixjam($formatjam[0],$formatjam[1]);
    }
    
    
    ##################stagnan
   
    
    $formatjamstagnan=  explode(".", $jamstagnasi[$tgl]);
    
    
    if(fixjam($formatjamstagnan[0],$formatjamstagnan[1])==':00' || fixjam($formatjamstagnan[0],$formatjamstagnan[1])=='0:00')
    {
        $isijamstag="";
    }
    else
    {
        $isijamstag=fixjam($formatjamstagnan[0],$formatjamstagnan[1]);
    }
    
    
    
    
    
    
    if($jumlahlori[$tgl]==0)
    {
        $lori="";
    }
    else
    {
        $lori=  number_format($jumlahlori[$tgl]);
    }
    
    if($tbsdiolah[$tgl]==0)
    {
        $tbs="";
    }
    else
    {
        $tbs=  $tbsdiolah[$tgl];
    }
    
    if($cpo[$tgl]==0)
    {
        $jumCpo="";
    }
    else
    {
        $jumCpo=  $cpo[$tgl];
    }
    
    if($pk[$tgl]==0)
    {
        $jumPk="";
    }
    else
    {
        $jumPk=  $pk[$tgl];
    }
    
    
    $no+=1;
    $stream.="<tr class=rowcontent>

            <td>".$no."</td>";
    
    $qwe=date('D', strtotime($tgl));
    
    

    
    if($qwe=='Sun')
    {
        $stream.="
            <td><font color=red>".tanggalnormal($tgl)."</font></td>";
    }
    else 
    {
        $stream.="
            <td>".tanggalnormal($tgl)."</td>";
    }
        
 
    
    
    $stream.="
            <td align=right>".$jamstagnasi[$tgl]."</td>
            <td align=right>".$jamoprasional[$tgl]."</td>
            <td align=right>".($lori!=''?number_format($lori):0)."</td>
            <td align=right>".($tbs!=''?number_format($tbs):0)."</td>
            <td align=right>".($jumCpo!=''?number_format($jumCpo):0)."</td>
            <td align=right>".($jumPk!=''?number_format($jumPk):0)."</td>";
    if($proses=='preview')
    {
    $stream.="            
            <td align=center><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=viewDetail('".$nopengolahan[$tgl]."','".$tgl."','".$kdpabrik."','".substr($tgl,0,4)."','".substr($tgl,5,2)."',event)></td>
			</td>
            ";
    }
    $stream.="                
        </tr>";
    
    
    
    $totStag+=$jamstagnasi[$tgl];
    $totOpr+=$jamoprasional[$tgl];
    $totLori+=$lori;
    $totTbs+=$tbs;
    $totCpo+=$jumCpo;
    $totPk+=$jumPk;
    
    
}
$stream.="                
        <thead><tr>";
    $stream.="
            <td align=right colspan=2>Total</td>
            <td align=right>".number_format($totStag,2)."</td>
            <td align=right>".number_format($totOpr,2)."</td>
            
            <td align=right>".number_format($totLori)."</td>
            <td align=right>".number_format($totTbs)."</td>
            <td align=right>".number_format($totCpo)."</td>"
            . "<td align=right>".number_format($totPk)."</td>";
     if($proses=='preview')
    {
    $stream.="            
            <td></td>
            ";
    }
    
    $stream.="                
        </tr></thead>"; 




/*
{
    if($dList['jamstagnasi']!=0)
    {
        $jamstagnasi=  number_format($dList['jamstagnasi'],2);
    }
    else
    {
        $jamstagnasi="";
    }
    
    if($dList['jamdinasbruto']!=0)
    {
        $jamoprasional=  number_format($dList['jamdinasbruto'],2);
    }
    else
    {
        $jamoprasional="";
    }
    
    if($dList['jumlahlori']!=0)
    {
        $jumlahlori=  number_format($dList['jumlahlori']);
    }
    else
    {
        $jumlahlori="";
    }
    
    
    if($dList['tbsdiolah']!=0)
    {
        $tbsdiolah=  number_format($dList['tbsdiolah']);
    }
    else
    {
        $tbsdiolah="";
    }
    
    
    
   
}*/
 





    $stream.="        

    </tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
    case 'preview':
            echo $stream;
    break;

######EXCEL	
    case 'excel':
            $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            $tglSkrg=date("Ymd");
            $nop_="LAPORAN_PENGOLAHAN_".$tglSkrg;
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
	
	
	
###############	
#panggil PDFnya
###############
	
	default:
	break;
}

?>