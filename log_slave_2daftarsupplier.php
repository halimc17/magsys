<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=checkPostGet('proses','');
$namasupplier=checkPostGet('namasupplier','');
$tipe=checkPostGet('tipe','');
$kdkelompok=checkPostGet('kdkelompok','');

if($proses=='excel'){
	$border=1;
	$stream="<table><tr>
			<td>".$_SESSION['lang']['daftarsupplier']."</td>
			</tr></table>";
}else{
	$border=0;
	$stream='';
}

$stream.="<table cellspacing=1 border='".$border."'>
		<thead>
		<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['tipe']."</td>
			<td align=center>".$_SESSION['lang']['kodekelompok']."</td>
			<td align=center>Id.".$_SESSION['lang']['supplier']."</td>
			<td align=center>".$_SESSION['lang']['namasupplier']."</td>
			<td align=center>".$_SESSION['lang']['alamat']."</td>
			<td align=center>".$_SESSION['lang']['cperson']."</td>
			<td align=center>".$_SESSION['lang']['kota']."</td>
			<td align=center>".$_SESSION['lang']['telp']."</td>		 
			<td align=center>".$_SESSION['lang']['fax']."</td>		 
			<td align=center>".$_SESSION['lang']['email']."</td>		 
			<td align=center>".$_SESSION['lang']['npwp']."</td>	 
			<td align=center>".$_SESSION['lang']['plafon']."</td>
		</tr>
		</thead><tbody>";
		

$strList="select a.*,b.kelompok,b.tipe from ".$dbname.".log_5supplier a
		left join ".$dbname.".log_5klsupplier b
		on a.kodekelompok = b.kode
		where namasupplier like '%".$namasupplier."%' 
		and b.tipe like '%".$tipe."%' 
		and b.kelompok like '%".$kdkelompok."%' 
		order by namasupplier asc";
$resList=mysql_query($strList) or die(mysql_error($conn));

if(mysql_num_rows($resList)==0){
	$stream = $_SESSION['lang']['datanotfound'];
}else{
	while($barList=mysql_fetch_assoc($resList)){
		$stream.="<tr class=rowcontent>
				<td style='vertical-align:top'>".$barList['tipe']."</td>
				<td style='vertical-align:top'>".$barList['kodekelompok']."<br>".$barList['kelompok']."</td>
				<td style='vertical-align:top'>".$barList['supplierid']."</td>
				<td style='vertical-align:top'>".$barList['namasupplier']."</td>
				<td style='vertical-align:top'>".$barList['alamat']."</td>
				<td style='vertical-align:top'>".$barList['kontakperson']."</td>
				<td style='vertical-align:top'>".$barList['kota']."</td>
				<td style='vertical-align:top'>".$barList['telepon']."</td>
				<td style='vertical-align:top'>".$barList['fax']."</td>
				<td style='vertical-align:top'>".$barList['email']."</td>
				<td style='vertical-align:top'>".$barList['npwp']."</td>
				<td style='vertical-align:top'>".$barList['plafon']."</td>
			</tr>";
	}
	$stream.="</tbody>";
}

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_="Supplier_List_".$periode."-".date('YmdHis');
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        break;

    default:
        break;
}

?>