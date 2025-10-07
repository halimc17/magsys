<link rel=stylesheet type=text/css href='style/generic.css'>
<?
require_once('config/connection.php');

$tanggal = date('d-m-Y', time());
$hariini = date('Y-m-d', time());
$bulan = date('m', time());
$tahun = date('Y', time());

$updatetime=date('d M Y H:i:s', time());

//                $hariini = '2014-01-20';
//                $bulan = '01';
//                $tahun = '2014';

$dt = strtotime($hariini);
$kemarin = date('Y-m-d', $dt-172800);
$kemarin2 = date('d-m-Y', $dt-172800);

$str="SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi
    WHERE tipe in ('KEBUN','AFDELING')";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $kamuskodeorg[$bar->kodeorganisasi]=$bar->namaorganisasi;
}

$arey = $totalsub = $total = array();

// produksi kebun yang ada pabrik
$str="SELECT kodeorg, substr(tanggal,1,10) as tanggal, substr(nospb,9,6) as afdeling, sum(beratbersih) as beratbersih, sum(jumlahtandan1) as jumlahtandan1 FROM ".$dbname.".pabrik_timbangan 
    WHERE substr(tanggal,1,10) = '".$kemarin."' and kodeorg != '' and kodebarang = '40000003'
    and kodeorg in ('SOGE', 'SENE', 'SKSE', 'SKNE', 'MRKE', 'WKNE')
    GROUP BY substr(nospb,9,6), substr(tanggal,1,10)
    ORDER BY substr(nospb,9,6)";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->afdeling]=$bar->afdeling;
    $kebun[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$kemarin){
        $arey[$bar->afdeling]['kgreamaren']+=$bar->beratbersih;
        $totalsub[$bar->kodeorg]['kgreamaren']+=$bar->beratbersih;        
        $total['kgreamaren']+=$bar->beratbersih;        
        
        $arey[$bar->afdeling]['jjreamaren']+=$bar->jumlahtandan1;
        $totalsub[$bar->kodeorg]['jjreamaren']+=$bar->jumlahtandan1;        
        $total['jjreamaren']+=$bar->jumlahtandan1;        
    }
}

// produksi kebun yang ga ada pabrik
$str="SELECT substr(blok,1,4) as kodeorg, substr(tanggal,1,10) as tanggal, substr(blok,1,6) as afdeling, sum(kgwb) as beratbersih, sum(jjg) as jumlahtandan1 FROM ".$dbname.".kebun_spb_vw 
    WHERE substr(tanggal,1,10) = '".$kemarin."'
        and substr(blok,1,4) in ('SBNE', 'SBME', 'SMTE', 'STLE', 'SMLE')
    GROUP BY substr(blok,1,6), substr(tanggal,1,10)
    ORDER BY substr(blok,1,6)";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->afdeling]=$bar->afdeling;
    $kebun[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$kemarin){
        $arey[$bar->afdeling]['kgreamaren']+=$bar->beratbersih;
        $totalsub[$bar->kodeorg]['kgreamaren']+=$bar->beratbersih;        
        $total['kgreamaren']+=$bar->beratbersih;        
        
        $arey[$bar->afdeling]['jjreamaren']+=$bar->jumlahtandan1;
        $totalsub[$bar->kodeorg]['jjreamaren']+=$bar->jumlahtandan1;        
        $total['jjreamaren']+=$bar->jumlahtandan1;        
    }
}

// taksasi kebun
$str="SELECT substr(afdeling,1,4) as kodeorg, tanggal, afdeling, sum(jjgmasak*bjr) as beratbersih, sum(hkdigunakan) as hk, sum(jjgmasak) as jjg FROM ".$dbname.".kebun_taksasi 
    WHERE tanggal between '".$kemarin."' and '".$hariini."' and afdeling not like '1%'
    GROUP BY afdeling, tanggal
    ORDER BY afdeling";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->afdeling]=$bar->afdeling;
    $kebun[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$hariini){
		if(!isset($arey[$bar->afdeling]['hktak'])) $arey[$bar->afdeling]['hktak']=0;
		if(!isset($totalsub[$bar->kodeorg]['hktak'])) $totalsub[$bar->kodeorg]['hktak']=0;
		if(!isset($total['hktak'])) $total['hktak']=0;
        $arey[$bar->afdeling]['hktak']+=$bar->hk;
        $totalsub[$bar->kodeorg]['hktak']+=$bar->hk;        
        $total['hktak']+=$bar->hk;        
        
		if(!isset($arey[$bar->afdeling]['jjtak'])) $arey[$bar->afdeling]['jjtak']=0;
		if(!isset($totalsub[$bar->kodeorg]['jjtak'])) $totalsub[$bar->kodeorg]['jjtak']=0;
		if(!isset($total['jjtak'])) $total['jjtak']=0;
        $arey[$bar->afdeling]['jjtak']+=$bar->jjg;
        $totalsub[$bar->kodeorg]['jjtak']+=$bar->jjg;        
        $total['jjtak']+=$bar->jjg;        
    }
    if(substr($bar->tanggal,0,10)==$kemarin){
		if(!isset($arey[$bar->afdeling]['hktakmaren'])) $arey[$bar->afdeling]['hktakmaren']=0;
		if(!isset($totalsub[$bar->kodeorg]['hktakmaren'])) $totalsub[$bar->kodeorg]['hktakmaren']=0;
		if(!isset($total['hktakmaren'])) $total['hktakmaren']=0;
        $arey[$bar->afdeling]['hktakmaren']+=$bar->hk;
        $totalsub[$bar->kodeorg]['hktakmaren']+=$bar->hk;
        $total['hktakmaren']+=$bar->hk;
        
		if(!isset($arey[$bar->afdeling]['jjtakmaren'])) $arey[$bar->afdeling]['jjtakmaren']=0;
		if(!isset($totalsub[$bar->kodeorg]['jjtakmaren'])) $totalsub[$bar->kodeorg]['jjtakmaren']=0;
		if(!isset($total['jjtakmaren'])) $total['jjtakmaren']=0;
        $arey[$bar->afdeling]['jjtakmaren']+=$bar->jjg;
        $totalsub[$bar->kodeorg]['jjtakmaren']+=$bar->jjg;        
        $total['jjtakmaren']+=$bar->jjg;        
    }    
}

// panen kebun
$str="SELECT unit as kodeorg, tanggal, substr(kodeorg,1,6) as afdeling, sum(hasilkerjakg) as beratbersih, count(*) as hk, sum(hasilkerja) as jjg FROM ".$dbname.".kebun_prestasi_vw
    WHERE tanggal = '".$hariini."'
    GROUP BY afdeling, tanggal
    ORDER BY afdeling";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->afdeling]=$bar->afdeling;
    $kebun[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$hariini){
		if(!isset($arey[$bar->afdeling]['hkrea'])) $arey[$bar->afdeling]['hkrea']=0;
		if(!isset($totalsub[$bar->kodeorg]['hkrea'])) $totalsub[$bar->kodeorg]['hkrea']=0;
		if(!isset($total['hkrea'])) $total['hkrea']=0;
        $arey[$bar->afdeling]['hkrea']+=$bar->hk;
        $totalsub[$bar->kodeorg]['hkrea']+=$bar->hk;        
        $total['hkrea']+=$bar->hk;        
        
		if(!isset($arey[$bar->afdeling]['kgpan'])) $arey[$bar->afdeling]['kgpan']=0;
		if(!isset($totalsub[$bar->kodeorg]['kgpan'])) $totalsub[$bar->kodeorg]['kgpan']=0;
		if(!isset($total['kgpan'])) $total['kgpan']=0;
        $arey[$bar->afdeling]['kgpan']+=$bar->beratbersih;
        $totalsub[$bar->kodeorg]['kgpan']+=$bar->beratbersih;        
        $total['kgpan']+=$bar->beratbersih;        
        
		if(!isset($arey[$bar->afdeling]['jjpan'])) $arey[$bar->afdeling]['jjpan']=0;
		if(!isset($totalsub[$bar->kodeorg]['jjpan'])) $totalsub[$bar->kodeorg]['jjpan']=0;
		if(!isset($total['jjpan'])) $total['jjpan']=0;
        $arey[$bar->afdeling]['jjpan']+=$bar->jjg;
        $totalsub[$bar->kodeorg]['jjpan']+=$bar->jjg;        
        $total['jjpan']+=$bar->jjg;        
    }
}

// panen kebun
$str="SELECT unit as kodeorg, tanggal, substr(kodeorg,1,6) as afdeling, sum(hasilkerjakg) as beratbersih, count(*) as hk, sum(hasilkerja) as jjg  FROM ".$dbname.".kebun_prestasi_vw
    WHERE tanggal = '".$kemarin."'
    GROUP BY afdeling, tanggal
    ORDER BY afdeling";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->afdeling]=$bar->afdeling;
    $kebun[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$kemarin){
		if(!isset($arey[$bar->afdeling]['hkreamaren'])) $arey[$bar->afdeling]['hkreamaren']=0;
		if(!isset($totalsub[$bar->kodeorg]['hkreamaren'])) $totalsub[$bar->kodeorg]['hkreamaren']=0;
		if(!isset($total['hkreamaren'])) $total['hkreamaren']=0;
        $arey[$bar->afdeling]['hkreamaren']+=$bar->hk;
        $totalsub[$bar->kodeorg]['hkreamaren']+=$bar->hk;        
        $total['hkreamaren']+=$bar->hk;        
        
		if(!isset($arey[$bar->afdeling]['kgpanmaren'])) $arey[$bar->afdeling]['kgpanmaren']=0;
		if(!isset($totalsub[$bar->kodeorg]['kgpanmaren'])) $totalsub[$bar->kodeorg]['kgpanmaren']=0;
		if(!isset($total['kgpanmaren'])) $total['kgpanmaren']=0;
        $arey[$bar->afdeling]['kgpanmaren']+=$bar->beratbersih;
        $totalsub[$bar->kodeorg]['kgpanmaren']+=$bar->beratbersih;        
        $total['kgpanmaren']+=$bar->beratbersih;
		
        if(!isset($arey[$bar->afdeling]['jjpanmaren'])) $arey[$bar->afdeling]['jjpanmaren']=0;
		if(!isset($totalsub[$bar->kodeorg]['jjpanmaren'])) $totalsub[$bar->kodeorg]['jjpanmaren']=0;
		if(!isset($total['jjpanmaren'])) $total['jjpanmaren']=0;
        $arey[$bar->afdeling]['jjpanmaren']+=$bar->jjg;
        $totalsub[$bar->kodeorg]['jjpanmaren']+=$bar->jjg;        
        $total['jjpanmaren']+=$bar->jjg;        
    }
}

//@$qwein=$total['kgrea']/1000;
//@$qweintak=$total['kgtak']/1000;
//    $qwe="Taksasi Panen ".$tanggal." = ".number_format($qweintak,2)." ton / Realisasi = ".number_format($qwein,2)." ton";
    $qwe="Taksasi Panen ".$tanggal."";

echo"<table class=sortable cellspacing=1 border=0 width=480px>
    <tr class=rowcontent>
    <td>".$qwe."</td>
    <td align=right width=1% nowrap>".$updatetime."</td>
    </tr>
    </table>";

echo"<table class=sortable cellspacing=1 border=0 width=480px>
    <thead>
    <tr class=rowtitle>
        <td align=center rowspan=2 style='width:60px;'>Unit</td>
        <td align=center colspan=2>Taksasi HI</td>
        <td align=center colspan=5>H-2 (".$kemarin2.")</td>
    </tr>  
    <tr class=rowtitle>
        <td align=center style='width:60px;'>JJG</td>
        <td align=center style='width:60px;'>HK</td>
        <td align=center style='width:60px;'>JJG Taks.</td>
        <td align=center style='width:60px;'>JJG Real</td>
        <td align=center style='width:60px;'>BJR Akt. PKS</td>
        <td align=center style='width:60px;'>BJR Kebun</td>
        <td align=center style='width:60px;'>Selisih BJR</td>
        <!--<td align=center>Restan</td>-->
    </tr>  
    </thead>
    <tbody></tbody></table>";

echo"<marquee height=85 onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
    <table class=sortable cellspacing=1 border=0 width=480px>
    <tbody>";

if(!empty($kebun))foreach($kebun as $buu){
    echo"<tr class=rowtitle>";
    echo"<td style='width:60px;'>".$buu."</td>";
    @$qwein=$totalsub[$buu]['jjtak'];
    echo"<td align=right style='width:60px;'>".number_format($qwein)."</td>";
    @$qwein=$totalsub[$buu]['hktak'];
    echo"<td align=right style='width:60px;'>".number_format($qwein)."</td>";
    
    @$qwein=$totalsub[$buu]['jjtakmaren'];
    echo"<td align=right style='width:60px;'>".number_format($qwein)."</td>";
    @$qwein=$totalsub[$buu]['jjreamaren'];
    echo"<td align=right style='width:60px;'>".number_format($qwein)."</td>";

    @$qwein2=$totalsub[$buu]['kgreamaren']/$totalsub[$buu]['jjreamaren'];
    echo"<td align=right style='width:60px;'>".number_format($qwein2,2)."</td>";
    @$qwein1=$totalsub[$buu]['kgpanmaren']/$totalsub[$buu]['jjpanmaren'];
    echo"<td align=right style='width:60px;'>".number_format($qwein1,2)."</td>";
    @$qwein=(@$qwein2-@$qwein1);
    echo"<td align=right style='width:60px;'>".number_format($qwein,2)."</td>";
//    @$qwein=$totalsub[$buu]['kgres']/1000;
//    echo"<!--<td align=right style='width:60px;'>".number_format($qwein,2)."</td>-->";
    echo"</tr>";
    if(!empty($unit))foreach($unit as $uun){
        if(substr($uun,0,4)==$buu){
        echo"<tr class=rowcontent>";
        echo"<td>&nbsp; &nbsp;".$uun."</td>";
        @$qwein=$arey[$uun]['jjtak'];
        echo"<td align=right>".number_format($qwein)."</td>";
        @$qwein=$arey[$uun]['hktak'];
        echo"<td align=right>".number_format($qwein)."</td>";
        
        @$qwein=$arey[$uun]['jjtakmaren'];
        echo"<td align=right>".number_format($qwein)."</td>";
        @$qwein=$arey[$uun]['jjreamaren'];
        echo"<td align=right>".number_format($qwein)."</td>";

        @$qwein2=$arey[$uun]['kgreamaren']/$arey[$uun]['jjreamaren'];
        echo"<td align=right>".number_format($qwein2,2)."</td>";
        @$qwein1=$arey[$uun]['kgpanmaren']/$arey[$uun]['jjpanmaren'];
        echo"<td align=right>".number_format($qwein1,2)."</td>";
        @$qwein=(@$qwein2-@$qwein1);
        echo"<td align=right>".number_format($qwein,2)."</td>";
//        @$qwein=$arey[$uun]['kgres']/1000;
//        echo"<!--<td align=right>".number_format($qwein,2)."</td>-->";
        echo"</tr>";
        }
    }
}

echo"<tr class=rowtitle>";
echo"<td>Total</td>";
@$qwein=$total['jjtak'];
echo"<td align=right>".number_format($qwein)."</td>";
@$qwein=$total['hktak'];
echo"<td align=right>".number_format($qwein)."</td>";

@$qwein=$total['jjtakmaren'];
echo"<td align=right>".number_format($qwein)."</td>";
@$qwein=$total['jjreamaren'];
echo"<td align=right>".number_format($qwein)."</td>";

@$qwein2=$total['kgreamaren']/$total['jjreamaren'];
echo"<td align=right>".number_format($qwein2,2)."</td>";
@$qwein1=$total['kgpanmaren']/$total['jjpanmaren'];
echo"<td align=right>".number_format($qwein1,2)."</td>";
@$qwein=(@$qwein2-@$qwein1);
echo"<td align=right>".number_format($qwein,2)."</td>";
//@$qwein=$total['kgres']/1000;
//echo"<!--<td align=right>".number_format($qwein,2)."</td>-->";
echo"</tr>";
echo"</tbody>
    </table>
    * sumber data: taksasi + panen + timbangan
    </marquee>";
?>