<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=$_GET['proses'];
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');
$thn=substr($per,0,4);

if($proses == 'excel'){
	$stream = "<table class=sortable cellspacing=1 border=1>";
}else{
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tahun']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodeblok']."</td>    
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['blok']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['bjr']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['janjang']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kgtbs']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['bjr']." Bulan Ini</td>
    </tr>";
$stream.="</thead>";

//$qBlok="select a.tahunproduksi,a.kodeorg,a.bjr from ".$dbname.".kebun_5bjr where a.tahunproduksi='".$thn."'";
$qBlok="SELECT a.blok,c.namaorganisasi,sum(a.jjg) as janjang,round(sum(a.kgwb),2) as tonage,round(sum(a.kgwb)/sum(a.jjg),2) as bjrset,d.bjr
		from ".$dbname.".kebun_spbdt a 
		LEFT JOIN ".$dbname.".kebun_spbht b on b.nospb=a.nospb
		LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.blok
		LEFT JOIN ".$dbname.".kebun_5bjr d on d.kodeorg=a.blok and d.tahunproduksi='".$thn."'
		where b.tanggal like '".$per."%' and a.blok like '".$unit."%'
		GROUP BY a.blok
		ORDER BY a.blok
		";

$vBlok=  mysql_query($qBlok) or die (mysql_error($conn));
while($dBlok=mysql_fetch_assoc($vBlok)){
    $countBlok+=1;
    $kodeblok[$dBlok['blok']]=$dBlok['blok'];
    $namablok[$dBlok['blok']]=$dBlok['namaorganisasi'];
    $janjang[$dBlok['blok']]=$dBlok['janjang'];
    $tonage[$dBlok['blok']]=$dBlok['tonage'];
    $bjrset[$dBlok['blok']]=($dBlok['janjang']==0 ? $dBlok['bjr'] : $dBlok['bjrset']);
    $bjr[$dBlok['blok']]=$dBlok['bjr'];
}

foreach ($kodeblok as $kdblok){
    $no+=1;
    $stream.="<tr class=rowcontent id=row".$no.">";
	$stream.="<td align=center>".$no."</td>";
    $stream.="<td align=center>".$thn."</td>";
	$stream.="<td id=kodeblok".$no.">".$kdblok."</td>";
	$stream.="<td>".$namablok[$kdblok]."</td>";
	$stream.="<td align=right>".number_format($bjr[$kdblok],2)."</td>";
	$stream.="<td align=right>".$janjang[$kdblok]."</td>";
	$stream.="<td align=right>".$tonage[$kdblok]."</td>";
	if($proses == 'excel'){
		$stream.="<td align=right>".$bjrset[$kdblok]."</td>";
	}else{
		$stream.="<td align=right><input type=text  id=bjrset".$no." value='".$bjrset[$kdblok]."' size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
	}
	$stream.="</tr>";           
}

if ($proses != 'excel'){
	//;saveAll(".$no.")
    $stream.="<button class=mybutton onclick=simpan(".$no.");>".$_SESSION['lang']['proses']."</button>";
	//saveAll
}

$stream.="</tbody></table>";
		
switch($proses){
    case'preview':
         echo $stream;
	break;
    
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="BJR_".$per."_".$tglSkrg;
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

	default:
}
?>
