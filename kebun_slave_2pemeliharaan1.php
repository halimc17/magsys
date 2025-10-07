<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=$_GET['proses'];
$kdOrg1=checkPostGet('kdOrg1','');
$kdAfd1=checkPostGet('kdAfd1','');
$tahun1=checkPostGet('tahun1','');
$kegiatan1=checkPostGet('kegiatan1','');
if(($proses=='excel')or($proses=='pdf')){
	$kdOrg1=$_GET['kdOrg1'];
	$kdAfd1=$_GET['kdAfd1'];
	$tahun1=$_GET['tahun1'];
        $kegiatan1=$_GET['kegiatan1'];
}
if($kdAfd1=='')
    $kdAfd1=$kdOrg1;

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kdOrg1==''){
            //echo"Error: Estate code and afdeling code required."; exit;
    }
    if($tahun1==''){
            echo"Error: year is reqired."; exit;
    }
	
}


if ($proses=='excel' or $proses=='preview') 
{
    
     if($kdOrg1=='')
    {
        exit("Warning:Kebun masih kosong");
    }
    
    
    // kamus kegiatan
if($_SESSION['language']=='EN'){
    $zz='namakegiatan1 as namakegiatan';
}else{
    $zz='namakegiatan';
}    
    $str="select kodekegiatan, ".$zz.", satuan
        from ".$dbname.".setup_kegiatan
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $kamusKeg[$bar->kodekegiatan]['nama']=$bar->namakegiatan;
        $kamusKeg[$bar->kodekegiatan]['satu']=$bar->satuan;
    }
    
    // kamus blok
    $str="select a.kodeorg, a.luasareaproduktif, a.tahuntanam, b.namaorganisasi
        from ".$dbname.".setup_blok a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $kamusOrg[$bar->kodeorg]['luas']=$bar->luasareaproduktif;
        $kamusOrg[$bar->kodeorg]['tata']=$bar->tahuntanam;
        $kamusOrg[$bar->kodeorg]['namaorg']=$bar->namaorganisasi;
    }
    
    
    // ambil data kegiatan/blok
    if($kdOrg1==''){
    $str="select kodekegiatan, kodeorg, hasilkerja, jumlahhk, tanggal 
        from ".$dbname.".kebun_perawatan_vw
        where kodeorg like '".$kdAfd1."%' and tanggal like '".$tahun1."%' and kodekegiatan like '%".$kegiatan1."%' 
        and jurnal=1
        ";
    }
    else{
        $where='';
        if($kdOrg1 != $_SESSION['empl']['lokasitugas']){                
            $where=" and jurnal=1";
        }
        $str="select kodekegiatan, kodeorg, hasilkerja, jumlahhk, tanggal 
        from ".$dbname.".kebun_perawatan_vw
        where kodeorg like '".$kdAfd1."%' and tanggal like '".$tahun1."%' and kodekegiatan like '%".$kegiatan1."%' 
        ".$where." ";
    }
//    exit("error: ".$str);
    $res=mysql_query($str) or die(mysql_error($conn));
    while($bar=mysql_fetch_object($res))
    {
        $dzKeg[$bar->kodekegiatan]=$bar->kodekegiatan;
        $dzOrg[$bar->kodeorg]=$bar->kodeorg;

        $bulan=substr($bar->tanggal,5,2);
		setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja'],0);
		setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk'],0);
        $dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja']+=$bar->hasilkerja;
        $dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk']+=$bar->jumlahhk;
        
        // cari jumlah baris untuk tiap kegiatan
        if(!isset($barisKeg[$bar->kodekegiatan][$bar->kodeorg])){
			setIt($barizKeg[$bar->kodekegiatan],0);
            $barisKeg[$bar->kodekegiatan][$bar->kodeorg]=$bar->kodekegiatan.$bar->kodeorg;            
            $barizKeg[$bar->kodekegiatan]+=1;
        }
    }
    
    
    if($kdAfd1!='')
    {
        $sortOrg=" and kodeblok like '".$kdAfd1."%' ";
    }
    else
    {
        $sortOrg=" and kodeblok like '".$kdOrg1."%' ";   
    }
   
    if($kegiatan1!='')
    {
        $sortKeg=" and kodekegiatan='".$kegiatan1."'";
    }
    else
    {
        
        $sortKeg=" and kodekegiatan in "
                . " (select kodekegiatan from ".$dbname.".setup_kegiatan where "
                . " kelompok in ('BBT','TM','TB','TBM','PNN')) ";
    }
    
    $iBa="select * from ".$dbname.".log_baspk where "
            . " tanggal like '".$tahun1."%' "
            . " and statusjurnal=1 ".$sortOrg." ".$sortKeg." ";
   // echo $iBa;
    $nBa=  mysql_query($iBa) or die (mysql_error($conn));
    while($dBa=  mysql_fetch_assoc($nBa))
    {
        $dzKeg[$dBa['kodekegiatan']]=$dBa['kodekegiatan'];
        $dzOrg[$dBa['kodeblok']]=$dBa['kodeblok'];
        $bulan=substr($dBa['tanggal'],5,2);
        
        
       setIt($dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['hasilkerja'], 0);
       setIt($dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['jumlahhk'], 0);
        
        $dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['hasilkerja']+=$dBa['hasilkerjarealisasi'];
        $dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['jumlahhk']+=$dBa['hkrealisasi'];
        
        if(!isset($barisKeg[$dBa['kodekegiatan']][$dBa['kodeblok']])){
            $barisKeg[$dBa['kodekegiatan']][$dBa['kodeblok']]=$dBa['kodekegiatan'].$dBa['kodeblok'];            
            $barizKeg[$dBa['kodekegiatan']]+=1;
        }
    }

    
    echo"<pre>";
    //print_r($barizKeg);
    echo"</pre>";
    
    
    
    if(!empty($dzKeg))asort($dzKeg);
    if(!empty($dzOrg))asort($dzOrg);
    $jumlahKeg = count($dzKeg);
    $jumlahOrg = count($dzOrg);

//    echo $str;
//    echo "<pre>";
//    print_r($dzArr);
//    echo "</pre>";

    $border=0;
    if($proses=='excel'){
		$border=1;
		$stream="<table>
					<tr>
						<td colspan=16 style='text-align:left; font-weight:bold'>".$_SESSION['lang']['laporanRotasiPemeliharaan']."</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>";
	}else{
		$border=0;
		$stream="";
	}

    $stream.="<table cellspacing='1' border='".$border."' class='sortable'>
    <thead>
    <tr class=rowheader>
        <td rowspan=2 align=center>".$_SESSION['lang']['kodekegiatan']."</td>
        <td rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</td>
        <td rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>
        <td colspan=3 align=center>".$_SESSION['lang']['blok']."</td>";   
    for ($i = 1; $i <= 12; $i++) {
        $stream.="<td colspan=3 align=center>".numToMonth($i)."</td>";   
    }    
        $stream.="<td colspan=3 align=center>".$_SESSION['lang']['semester']." I</td>
        <td colspan=3 align=center>".$_SESSION['lang']['semester']." II</td>
        <td colspan=3 align=center>".$_SESSION['lang']['total']."</td>
    </tr>
    <tr class=rowheader>
        <td align=center>".$_SESSION['lang']['kode']."</td>    
        <td align=center>".$_SESSION['lang']['luas']."</td>
        <td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
    // tiap bulan
    for ($i = 1; $i <= 12; $i++) {
        $stream.="<td align=center>".$_SESSION['lang']['jhk']."</td>
        <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
        <td align=center>Output (Hasil/JHK)</td>";   
    }    
        $stream.="<td align=center>".$_SESSION['lang']['jhk']."</td>
        <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
        <td align=center>Output (Hasil/JHK)</td>
        <td align=center>".$_SESSION['lang']['jhk']."</td>
        <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
        <td align=center>Output (Hasil/JHK)</td>
        <td align=center>".$_SESSION['lang']['jhk']."</td>
        <td align=center>".$_SESSION['lang']['hasilkerjad']."</td>
        <td align=center>Output (Hasil/JHK)</td>
    </tr></thead>
    <tbody>";
    // tiap kegiatan    
    if(!empty($dzKeg))foreach($dzKeg as $rKeg){
        $bariskegiatan=true;
        $stream.="<tr class=rowcontent>
            <td rowspan=".$barizKeg[$rKeg].">".$rKeg."</td>
            <td rowspan=".$barizKeg[$rKeg].">".$kamusKeg[$rKeg]['nama']."</td>
            <td rowspan=".$barizKeg[$rKeg].">".$kamusKeg[$rKeg]['satu']."</td>";
    // tiap blok    
    if(!empty($dzOrg))foreach($dzOrg as $rOrg){
        
        $adadata=false;
        for ($i = 1; $i <= 12; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;    
            
            if(!empty($dzArr[$rKeg][$rOrg][$ii]['hasilkerja']))$adadata=true;
            if(!empty($dzArr[$rKeg][$rOrg][$ii]['jumlahhk']))$adadata=true;
        }
        
        if($adadata){
            if(!$bariskegiatan)$stream.="<tr class=rowcontent>";
                //$stream.="<td>".$rOrg."</td>";        
                $stream.="<td align=right>".$kamusOrg[$rOrg]['namaorg']."</td>";        
                $stream.="<td align=right>".$kamusOrg[$rOrg]['luas']."</td>";        
                $stream.="<td align=right>".$kamusOrg[$rOrg]['tata']."</td>";     

                $jumlahhk1=0;
                $jumlahhk2=0;
                $hasilkerja1=0;
                $hasilkerja2=0;
            for ($i = 1; $i <= 12; $i++) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
				setIt($dzArr[$rKeg][$rOrg][$ii]['jumlahhk'],0);
				setIt($dzArr[$rKeg][$rOrg][$ii]['hasilkerja'],0);
                $haka=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                $hasi=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                
                
            
                
                $oput=0;
                @$oput=$hasi/$haka;
//                if($oput==0)$oput=''; else $oput=number_format($oput,2);
                
                /*if(($haka==0)&&($hasi==0)){
                    $haka='';
                    $hasi='';
                    $oput='';
                    $bisadiklik='';                    
                }else{
                    $haka=number_format($haka,2);
                    $hasi=number_format($hasi,2);
                    $oput=number_format($oput,2);
                    
                    $bisadiklik=" style='cursor:pointer;' onclick=\"viewDetail1('".$rKeg."','".$rOrg."','".$tahun1."-".$ii."',event);\" title=\"Click untuk melihat detail\" ";
                }*/
                
                if($haka==0)
                {
                    $haka='';
                }
                else
                {
                    $haka=number_format($haka,2);
                }
                
                if($hasi==0)
                {
                    $hasi='';
                }
                else
                {
                    $hasi=number_format($hasi,2);
                }
                
                
                if($oput==0)
                {
                    $oput='';
                }
                 else
                {
                    $oput=number_format($oput,2);
                }
                
                $bisadiklik=" style='cursor:pointer;' onclick=\"viewDetail1('".$rKeg."','".$rOrg."','".$tahun1."-".$ii."',event);\" title=\"Click untuk melihat detail\" ";
              

                $stream.="<td align=right ".$bisadiklik.">".$haka."</td>
                <td align=right ".$bisadiklik.">".$hasi."</td>
                <td align=right ".$bisadiklik.">".$oput."</td>";
				
				setIt($dzArr[$rKeg][$rOrg][$ii]['jumlahhk'],0);
				setIt($dzArr[$rKeg][$rOrg][$ii]['hasilkerja'],0);
                if($i<8){ // semester 1
                     $jumlahhk1+=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                     $hasilkerja1+=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                }else{ // semester 2
                     $jumlahhk2+=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                     $hasilkerja2+=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                }           
            }
            // semester 1
            $oput=0;
            $haka=0;
            $hasi=0;

            $haka=$jumlahhk1;
            $hasi=$hasilkerja1;
            @$oput=$hasi/$haka;
            if(($haka==0)&&($hasi==0)){
                $haka='';
                $hasi='';
                $oput='';
            }else{
                $haka=number_format($haka,2);
                $hasi=number_format($hasi,2);                
                $oput=number_format($oput,2);
            }

            $stream.="<td align=right>".$haka."</td>
            <td align=right>".$hasi."</td>
            <td align=right>".$oput."</td>";

            // semester 2
            $oput=0;
            $haka=0;
            $hasi=0;

            $haka=$jumlahhk2;
            $hasi=$hasilkerja2;
            @$oput=$hasi/$haka;
            if(($haka==0)&&($hasi==0)){
                $haka='';
                $hasi='';
                $oput='';
            }else{
                $haka=number_format($haka,2);
                $hasi=number_format($hasi,2);                
                $oput=number_format($oput,2);
            }

            $stream.="<td align=right>".$haka."</td>
            <td align=right>".$hasi."</td>
            <td align=right>".$oput."</td>";

            // total
            $oput=0;
            $haka=0;
            $hasi=0;

            $haka=$jumlahhk1+$jumlahhk2;
            $hasi=$hasilkerja1+$hasilkerja2;
            @$oput=$hasi/$haka;
            
            if(($haka==0)&&($hasi==0)){
                $haka='';
                $hasi='';
                $oput='';
            }else{
                $haka=number_format($haka,2);
                $hasi=number_format($hasi,2);                
                $oput=number_format($oput,2);
            }            

            $stream.="<td align=right>".$haka."</td>
            <td align=right>".$hasi."</td>
            <td align=right>".$oput."</td>";

            $stream.="</tr>";
            $bariskegiatan=false;            
        } // end of adadata

    } //  end of tiap blok
    } //  end of tiap kegiatan    
    $stream.="</tbody></table>";
     
}  
switch($proses)
{
    case'preview':
        echo $stream;    
    break;
    case 'excel':
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHms");
        $nop_="Rotasi_Perawatan_".$kdAfd1."_".$tahun1."_".$kegiatan1."_".date('YmdHis');
        $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
        gzwrite($gztralala, $stream);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";            
    break;    
}

?>