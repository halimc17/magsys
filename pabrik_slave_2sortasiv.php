<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



//$tgl1=tanggalsystemn($_POST['tgl1']);

/*catatan nanti buat jelasin selisihnya
 * jika dalam 1 hari ada 10 tiket, dan yang di sortasi hanya 5 / kurang dari jumlah tiketnya
 * pembaginya karena kg pembagi dibagi ke netto total tiket, bukan netto tiket yang di sortasi saja
 * -
 * jika ada lebih dari 1 tiket dan brondolannya yang tiket pertama 10%, tiket ke 2 >12.5, maka
 * di sini saya ambil rata2nya 10+12.5/2 sehingga menjadi 11.25 yang artinya akan menjadi terkena penalty
*/
$proses=checkPostGet('proses','');
$kdorg=checkPostGet('kdorg','');
$sup=checkPostGet('sup','');

$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');
$optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk');





if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{
    
    if($kdorg=='')
    {
        echo"Warning: Unit tidak boleh kosong"; 
        exit;
    }
    if($sup=='')
    {
        echo"Warning: Supplier tidak boleh kosong"; 
        exit;
    }
    
    if(($tgl1=='')or($tgl2==''))
    {
        echo"Warning: Tanggal tidak boleh kosong"; 
        exit;
    }

    else if($tgl1>$tgl2)
    {
        echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
        exit;
    }
	
}









##############################
############PREPARE###########
#############DATA#############
##############################


#bentuk range tanggal
$rangetanggal = rangeTanggal($tgl1, $tgl2);

#bentuk netto , sumber pabrik_timbangan
/*$iTim="select tanggal,sum(beratbersih) as netto,sum(jjg) as jjg,sum(beratbersih)/sum(jjg) as bjr,count(notiket) as truk "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' group by tanggal ";*/
$iTim="select tanggal,sum(beratbersih) as netto,sum(jjg) as jjg,sum(beratbersih)/sum(jjg) as bjr "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' group by tanggal ";
$nTim=  mysql_query($iTim) or die (mysql_error($conn));
while($dTim=  mysql_fetch_assoc($nTim))
{
    $netto[$dTim['tanggal']]=  $dTim['netto'];
    $jjg[$dTim['tanggal']]=$dTim['jjg'];
    $bjr[$dTim['tanggal']]=$dTim['bjr'];
    
}

##bentuk jumlah yang disortasi //perbedaan dengan query pertama adalah where kgpotsortasi>0
##untuk mengsortir nomor tiket yang disortasi. 
##karena dari beberapa tiket persupplier belum tentu semua di sortasi
$iSortim="select tanggal,count(notiket) as truk,sum(beratbersih) as netto "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."'"
        . " and kgpotsortasi>0 group by tanggal ";

$nSortim=  mysql_query($iSortim) or die (mysql_error($conn));
while($dSortim=  mysql_fetch_assoc($nSortim))
{
    $truk[$dSortim['tanggal']]=$dSortim['truk'];
    $sample[$dSortim['tanggal']]=$dSortim['truk']*100;
    $nettosor[$dSortim['tanggal']]=$dSortim['netto'];
}




#bentuk sortasinya , sumber : pabrik_sortasi_vw
$iSor="select tanggal,kodefraksi,sum(jumlah) as jumlah from ".$dbname.".pabrik_sortasi_vw "
        . " where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' "
        . " group by tanggal,kodefraksi ";
$nSor=  mysql_query($iSor) or (mysql_error($conn));
while($dSor=  mysql_fetch_assoc($nSor))
{
    $jumsor[$dSor['tanggal']][$dSor['kodefraksi']]=$dSor['jumlah'];
}



##buat koefisien
##jika SMA = 12.5, CKS=7

if($optPt[$kdorg]=='CKS')
{
    $koef=7;
}
else if($optPt[$kdorg]=='SMA')
{
    $koef=12.5;
}






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
            <td align=center rowspan=4>tanggal</td>
            <td align=center colspan=3 rowspan=2>Total TBS Diterima</td>
            <td align=center rowspan=2>Total<br>Truk di</td>
            <td align=center rowspan=4>Netto<br>Grading</td>
            <td align=center rowspan=4>Jumlah<br>Sample<br>Grading</td>
            <td align=center colspan=4 rowspan=2>Unripe</td>
            <td align=center colspan=4 rowspan=2>Over Ripe</td>
            <td align=center colspan=4 rowspan=2>Empty Bunch</td>
            <td align=center colspan=2 rowspan=2>Abnormal</td>
            <td align=center colspan=4 rowspan=2>Rotten Bunch</td>
            <td align=center colspan=2 rowspan=2>Ripe</td>
            <td align=center colspan=4 rowspan=2>Long<br />
              Stalk</td>
            <td align=center colspan=4 rowspan=2>Brondolan<br />
              (Loose Fruit)</td>
            <td align=center colspan=2 rowspan=2>Pinalty</td>
            <td align=center rowspan=4>Netto<br />
              Setelah<br />
              Grading</td>
            <td align=center rowspan=4>Grading<br>%</td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td align=center>Netto</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>Bjr</td>
            <td align=center rowspan=2>Grading</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>Jjg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Kg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>Kg</td>
        </tr>
        <tr>
            <td align=center>(Kg)</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
        </tr>
    </thead>
<tbody>";


foreach($rangetanggal as $listtanggal => $tgl)
{
        $stream.="<tr class=rowcontent>";
        

        
        $stream.="<td align=center>".tanggalnormal($tgl)."</td>";   
        $stream.="<td align=right>".number_format(@$netto[$tgl])."</td>";  
        $stream.="<td align=right>".number_format(@$jjg[$tgl])."</td>";  
        $stream.="<td align=right>".number_format(@$bjr[$tgl],2)."</td>";  
        $stream.="<td align=right>".number_format(@$truk[$tgl])."</td>"; 
        $stream.="<td align=right>".number_format(@$nettosor[$tgl])."</td>";  //
        $stream.="<td align=right>".number_format(@$sample[$tgl])."</td>";
    
    //b=unripe
    @$persenb=$jumsor[$tgl]['B']/$sample[$tgl]*100;
    @$persenpenb=round($persenb*50/100,2);
    @$kgpenb=round($persenpenb/100*$netto[$tgl]);
        $stream.="<td align=right>".@$jumsor[$tgl]['B']."</td>";
        $stream.="<td align=right>".number_format($persenb,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenb,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenb)."</td>";
    
    //a=over ripe
    @$persena=$jumsor[$tgl]['A']/$sample[$tgl]*100;
    @$persenpena=($persena-5)*25/100;
    if($persenpena<0)
    {@$persenpena=0;}
    else 
    {@$persenpena=round(($persena-5)*25/100,2);}
    @$kgpena=round($persenpena/100*$netto[$tgl]);
        $stream.="<td align=right>".@$jumsor[$tgl]['A']."</td>";
        $stream.="<td align=right>".number_format($persena,2)."</td>";
        $stream.="<td align=right>".number_format($persenpena,2)."</td>";
        $stream.="<td align=right>".number_format($kgpena)."</td>";
    
    //c=Empty Bunch
    @$persenc=round(@$persenpenc=$jumsor[$tgl]['C']/$sample[$tgl]*100,2);
    @$kgpenc=round($persenc/100*$netto[$tgl]);
        $stream.="<td align=right>".@$jumsor[$tgl]['C']."</td>";
        $stream.="<td align=right>".number_format($persenc,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenc,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenc)."</td>";
    
    //D=Abnormal
    @$persend=round($jumsor[$tgl]['D']/$sample[$tgl]*100,2);
        $stream.="<td align=right>".@$jumsor[$tgl]['D']."</td>";
        $stream.="<td align=right>".number_format($persend,2)."</td>";
    
    //E=Rotten Bunch
    @$persene=round(@$persenpene=$jumsor[$tgl]['E']/$sample[$tgl]*100);
    @$kgpene=round($persene/100*$netto[$tgl]);
        $stream.="<td align=right>".@$jumsor[$tgl]['E']."</td>";
        $stream.="<td align=right>".number_format($persene,2)."</td>";
        $stream.="<td align=right>".number_format($persenpene,2)."</td>";
        $stream.="<td align=right>".number_format($kgpene)."</td>";
    
    //ripe
    @$ripe=round($sample[$tgl]-($jumsor[$tgl]['B']+$jumsor[$tgl]['A']+
    $jumsor[$tgl]['C']+$jumsor[$tgl]['D']+$jumsor[$tgl]['E']));
    @$persenripe=round($ripe/$sample[$tgl]*100,2);
        $stream.="<td align=right>".number_format($ripe)."</td>";
        $stream.="<td align=right>".number_format($persenripe,2)."</td>";
    
    //F=RoLong Stalk
    @$persenf=round($jumsor[$tgl]['F']/$sample[$tgl]*100,2);
    @$persenpenf=round($persenf/100,2);
    @$kgpenf=round($persenpenf/100*$netto[$tgl]);
        $stream.="<td align=right>".@$jumsor[$tgl]['F']."</td>";
        $stream.="<td align=right>".number_format($persenf,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenf,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenf)."</td>";
    
    //G=Brondolan
    /*@$perseng=round($jumsor[$tgl]['G']/$nettosor[$tgl]*100,2);
    @$persenpeng=round((12.5-$perseng)*0.3,2);
    @$kgpeng=round($persenpeng/100*$netto[$tgl]);
        
        $stream.="<td align=right>".@$jumsor[$tgl]['G']."</td>";
        $stream.="<td align=right>".number_format($perseng,2)."</td>";
        $stream.="<td align=right>".number_format($persenpeng,2)."</td>";
        $stream.="<td align=right>".number_format($kgpeng)."</td>";     */
        
        
        $jumsor[$tgl]['G']=$jumsor[$tgl]['G']/$truk[$tgl];
        if($jumsor[$tgl]['G']>$koef)
        {
            $jumsor[$tgl]['G']=$koef;
        }
        
        @$persenpeng=round((12.5-$jumsor[$tgl]['G'])*0.3,2);
        @$kgpeng=round($persenpeng/100*$netto[$tgl]);
        @$kgbrdol=$nettosor[$tgl]*$jumsor[$tgl]['G']/100;
        $stream.="<td align=right>".@$kgbrdol."</td>";
        $stream.="<td align=right>".@$jumsor[$tgl]['G']."</td>";
        $stream.="<td align=right>".number_format($persenpeng,2)."</td>";
        $stream.="<td align=right>".number_format($kgpeng)."</td>";
    
    @$kgpen=$kgpena+$kgpenb+$kgpenc+$kgpend+$kgpene+$kgpenf+$kgpeng;
    @$perpen=round($kgpen/$netto[$tgl]*100,2);
        $stream.="<td align=right>".round($perpen,2)."</td>";
        $stream.="<td align=right>".number_format($kgpen)."</td>";
      
    //netto setelah grading
    @$beratnormal=$netto[$tgl]-$kgpen;
    @$persengrad=round($sample[$tgl]/$jjg[$tgl]*100,2);
        $stream.="<td align=right>".number_format($beratnormal)."</td>";
        $stream.="<td align=right>".number_format($persengrad,2)."</td>";
    
        $stream.="</tr>";
        
    #buat total
        $tnetto+=$netto[$tgl];
        $tjjg+=$jjg[$tgl];
        $ttruk+=$truk[$tgl];
        $tnettosor+=$nettosor[$tgl];
        $tsample+=$sample[$tgl];
        
        //b unripe
        $tjumsorb+=$jumsor[$tgl]['B'];
        $tkgpenb+=$kgpenb;
        
        //a
        $tjumsora+=$jumsor[$tgl]['A'];
        $tkgpena+=$kgpena;
        
        //c=Empty Bunch
        $tjumsorc+=$jumsor[$tgl]['C'];
        $tkgpenc+=$kgpenc;
        
        //D=Abnormal
        $tjumsord+=$jumsor[$tgl]['D'];
        
        //E=Rotten Bunch
        $tjumsore+=$jumsor[$tgl]['E'];
        $tkgpene+=$kgpene;
        
        //ripe
        $tripe+=$ripe;
        
        //F=RoLong Stalk
        $tjumsorf+=$jumsor[$tgl]['F'];
        $tkgpenf+=$kgpenf;
        
        //g brondolan
        $tkgbrdol+=$kgbrdol;
        $tkgpeng+=$kgpeng;
        
        //total kg pen
        $tkgpen+=$kgpen;
        
 
}//tutup foreach   


$tbjr=$tnetto/$tjjg;

//b unripe
$tpersenb=$tjumsorb/$tsample*100;
$tpersenpenb=$tkgpenb/$tnetto*100;

//a=over ripe
$tpersena=$tjumsora/$tsample*100;
$tpersenpena=$tkgpena/$tnetto*100;

//c=Empty Bunch
$tpersenc=$tjumsorc/$tsample*100;
$tpersenpenc=$tkgpenc/$tnetto*100;

//d abnormal
$tpersend=round($tjumsord/$tsample);

//E=Rotten Bunch
$tpersene=$tjumsore/$tsample*100;
$tpersenpene=$tkgpene/$tnetto*100;

///ripe
$tpersenripe=round($tripe/$tsample*100);

//F=RoLong Stalk
$tpersenf=$tjumsorf/$tsamplf*100;
$tpersenpenf=$tkgpenf/$tnetto*100;

//G brondolan
$tperseng=$tkgbrdol/$tnetto*100;
$tpersenpeng=$tkgpeng/$tnetto*100;

//persen pen
$tperpen=round($tkgpen/$tnetto*100,2);


       //netto setelah grading
$tberatnormal=$tnetto-$tkgpen;
$tpersengrad=round($tsample/$tjjg*100,2);


$stream.="<tr class=rowcontent>";
    $stream.="<td align=right>Total</td>";
    $stream.="<td align=right>".number_format($tnetto)."</td>";
    $stream.="<td align=right>".number_format($tjjg)."</td>";
    $stream.="<td align=right>".number_format($tbjr,2)."</td>";
    $stream.="<td align=right>".number_format($ttruk)."</td>";    
    $stream.="<td align=right>".number_format($tnettosor)."</td>";      
    $stream.="<td align=right>".number_format($tsample)."</td>"; 
    
    $stream.="<td align=right>".number_format($tjumsorb)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenb,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenb,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenb)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsora)."</td>"; 
    $stream.="<td align=right>".number_format($tpersena,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpena,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpena)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsorc)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenc,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenc,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenc)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsord)."</td>";
    $stream.="<td align=right>".number_format($tpersend,2)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsore)."</td>"; 
    $stream.="<td align=right>".number_format($tpersene,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpene,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpene)."</td>";
    
    $stream.="<td align=right>".number_format($tripe)."</td>";
    $stream.="<td align=right>".number_format($tpersenripe,2)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsorf)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenf,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenf,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenf)."</td>";
    
    $stream.="<td align=right>".number_format($tkgbrdol)."</td>"; 
    $stream.="<td align=right>".number_format($tperseng,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpeng,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpeng)."</td>";
    
    
    $stream.="<td align=right>".number_format($tperpen,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpen)."</td>";
    $stream.="<td align=right>".number_format($tberatnormal)."</td>";
    $stream.="<td align=right>".number_format($tpersengrad,2)."</td>";
$stream.="</tr>";
              
                
                
                
                
                
                
                
                
                
                
                
                
                
                
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
		$nop_="LAPORAN_sortasi_".$tglSkrg;
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