<link rel=stylesheet type=text/css href='style/generic.css'>
<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tanggal = date('d-m-Y', time());
$hariini = date('Y-m-d', time());
$bulan = date('m', time());
$tahun = date('Y', time());

$updatetime=date('d M Y H:i:s', time());

//                $hariini = '2012-12-12';
//                $bulan = '12';
//                $tahun = '2012';

$dt = strtotime($hariini);
$kemarin = date('Y-m-d', $dt-86400);

$str="SELECT kodetimbangan, namasupplier FROM ".$dbname.".log_5supplier
    WHERE kodetimbangan is not null";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $kamuskodeorg[$bar->kodetimbangan]=$bar->namasupplier;
}

// Init
$total = array('hi'=>0,'maren'=>0,'bi'=>0,'sdbi'=>0);

// hari ini
$str="SELECT kodecustomer as kodeorg, sum(beratbersih) as beratbersih FROM ".$dbname.".pabrik_timbangan 
    WHERE substr(tanggal,1,10) like '".$hariini."%' and kodeorg = '' and kodebarang = '40000003'
    GROUP BY kodecustomer
    ORDER BY kodecustomer";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->kodeorg]=$bar->kodeorg;
    $arey[$bar->kodeorg]['hi']=$bar->beratbersih;
    $total['hi']+=$bar->beratbersih;
}

// kemarin
$str="SELECT kodecustomer as kodeorg, sum(beratbersih) as beratbersih FROM ".$dbname.".pabrik_timbangan 
    WHERE substr(tanggal,1,10) like '".$kemarin."%' and kodeorg = '' and kodebarang = '40000003'
    GROUP BY kodecustomer
    ORDER BY kodecustomer";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->kodeorg]=$bar->kodeorg;
    $arey[$bar->kodeorg]['maren']=$bar->beratbersih;
    $total['maren']+=$bar->beratbersih;
}

// bulan ini
$str="SELECT kodecustomer as kodeorg, sum(beratbersih) as beratbersih FROM ".$dbname.".pabrik_timbangan 
    WHERE substr(tanggal,1,10) between '".$tahun."-".$bulan."-01' and '".$hariini."' and kodeorg = '' and kodebarang = '40000003'
    GROUP BY kodecustomer
    ORDER BY kodecustomer";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->kodeorg]=$bar->kodeorg;
    $arey[$bar->kodeorg]['bi']=$bar->beratbersih;
    $total['bi']+=$bar->beratbersih;
}

// sd bulan ini
$str="SELECT kodecustomer as kodeorg, sum(beratbersih) as beratbersih FROM ".$dbname.".pabrik_timbangan 
    WHERE substr(tanggal,1,10) between '".$tahun."-01-01' and '".$hariini."' and kodeorg = '' and kodebarang = '40000003'
    GROUP BY kodecustomer
    ORDER BY kodecustomer";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->kodeorg]=$bar->kodeorg;
    $arey[$bar->kodeorg]['sdbi']=$bar->beratbersih;
    $total['sdbi']+=$bar->beratbersih;
}

// taksasi kebun
$str="SELECT afdeling as kodeorg, tanggal, sum(kg) as beratbersih FROM ".$dbname.".kebun_taksasi 
    WHERE tanggal between '".$tahun."-01-01' and '".$hariini."' and afdeling like '1%'
    GROUP BY afdeling, tanggal
    ORDER BY afdeling";
$res=mysql_query($str);
echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{ 
    $unit[$bar->kodeorg]=$bar->kodeorg;
    if(substr($bar->tanggal,0,10)==$hariini){
		setIt($areytak[$bar->kodeorg]['hi'],0);
		setIt($totaltak['hi'],0);
        $areytak[$bar->kodeorg]['hi']+=$bar->beratbersih;
        $totaltak['hi']+=$bar->beratbersih;        
    }
    if(substr($bar->tanggal,0,10)==$kemarin){
		setIt($areytak[$bar->kodeorg]['maren'],0);
		setIt($totaltak['maren'],0);
        $areytak[$bar->kodeorg]['maren']+=$bar->beratbersih;
        $totaltak['maren']+=$bar->beratbersih;        
    }
}

@$qwein=$total['hi']/1000;
@$qweintak=$totaltak['hi']/1000;
    $qwe="TBS Eksternal ".$tanggal." = ".number_format($qwein,2)." ton / Target = ".number_format($qweintak,2)." ton";

echo"<table class=sortable cellspacing=1 border=0 width=480px>
    <tr class=rowcontent>
    <td>".$qwe."</td>
    <td align=right width=1% nowrap>".$updatetime."</td>
    </tr>
    </table>";

echo"<table class=sortable cellspacing=1 border=0 width=480px>
    <thead>
    <tr class=rowtitle>
        <td align=center rowspan=2 style='width:120px;'>Unit</td>
        <td align=center colspan=2 style='width:120px;'>Hari Ini (T)</td>
        <td align=center colspan=2 style='width:120px;'>Kemarin (T)</td>
        <td align=center rowspan=2 style='width:60px;'>Bulan Ini (T)</td>
        <td align=center rowspan=2 style='width:60px;'>sd Bulan Ini (T)</td>
    </tr>  
    <tr class=rowtitle>
        <td align=center>Targ.</td>
        <td align=center>Real.</td>
        <td align=center>Targ.</td>
        <td align=center>Real.</td>
    </tr> 
    </thead>
    <tbody></tbody></table>";

echo"<marquee height=72 onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
    <table class=sortable cellspacing=1 border=0 width=480px>
    <tbody>";

if(!empty($unit))foreach($unit as $uun){
    echo"<tr class=rowcontent>";
    echo"<td style='width:120px;'>".$kamuskodeorg[$uun]."</td>";
    @$qweintak=$areytak[$uun]['hi']/1000;
    @$qwein=$arey[$uun]['hi']/1000;
    echo"<td align=right style='width:60px;'>".number_format($qweintak,2)."</td>";
    echo"<td align=right style='width:60px;'>".number_format($qwein,2)."</td>";
    @$qweintak=$areytak[$uun]['maren']/1000;
    @$qwein=$arey[$uun]['maren']/1000;
    echo"<td align=right style='width:60px;'>".number_format($qweintak,2)."</td>";
    echo"<td align=right style='width:60px;'>".number_format($qwein,2)."</td>";
    @$qwein=$arey[$uun]['bi']/1000;
    echo"<td align=right style='width:60px;'>".number_format($qwein,2)."</td>";
    @$qwein=$arey[$uun]['sdbi']/1000;
    echo"<td align=right style='width:60px;'>".number_format($qwein,2)."</td>";
    echo"</tr>";
}

echo"<tr class=rowtitle>";
echo"<td>Total</td>";
@$qweintak=$totaltak['hi']/1000;
@$qwein=$total['hi']/1000;
echo"<td align=right>".number_format($qweintak,2)."</td>";
echo"<td align=right>".number_format($qwein,2)."</td>";
@$qweintak=$totaltak['maren']/1000;
@$qwein=$total['maren']/1000;
echo"<td align=right>".number_format($qweintak,2)."</td>";
echo"<td align=right>".number_format($qwein,2)."</td>";
@$qwein=$total['bi']/1000;
echo"<td align=right>".number_format($qwein,2)."</td>";
@$qwein=$total['sdbi']/1000;
echo"<td align=right>".number_format($qwein,2)."</td>";
echo"</tr>";
echo"</tbody>
    </table>
    * sumber data: timbangan + target eksternal
    </marquee>";
?>