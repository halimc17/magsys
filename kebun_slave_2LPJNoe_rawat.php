<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
if($proses=='excel'){
    $kodept=checkPostGet('kodept0','');
	$periode=checkPostGet('periode0','');
	$namakud=checkPostGet('namakud0','');
}else{
	$kodept=checkPostGet('kodept','');
	$periode=checkPostGet('periode','');
	$namakud=checkPostGet('namakud','');
}

$where = " Where 1=1 ";
$whered = " Where 1=1 ";
if($kodept != ''){
  $where.=" AND kodept = '".$kodept."' ";
}
if($kodept != ''){
  $where.=" AND tanggal like '".$periode."%' ";
  $whered.=" AND tanggal like '".$periode."%' ";
}
if($namakud != ''){
  $where.=" AND supplierid = '".$namakud."' ";
}

$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
/*
$strList="select * from ".$dbname.".kebun_lpjkud 
		where periode like '%".$periode."%' 
		order by periode desc";
*/
$gt_upah = 0;$gt_transport = 0;$gt_material = 0;$gt_all = 0;	 
$qGroupkud = "SELECT distinct(supplierid), namasupplier 
              FROM ".$dbname.".v_subq_lpj_rawat
              ".$where."			  
              GROUP BY supplierid ORDER BY supplierid ";
//print_r($qGroupkud);exit;			  
$grouplist=mysql_query($qGroupkud) or die(mysql_error($conn));

if(mysql_num_rows($grouplist) <= 0){
	$stream=$_SESSION['lang']['datanotfound'];
}else{

	if($proses=='excel'){
		$border=1;
	}else{
		$border=0;
	}

	$stream="<table class=sortable cellspacing=1 border='".$border."'>
			<thead>";
	if($proses=='excel'){
		$stream.="<tr>
			<td colspan=8><b>Laporan PertanggungJawaban Rawat</b></td>
		</tr>";
	}
	$stream.="<tr class=rowheader>
				<td style='text-align:center;' rowspan='2' ><b>No</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Blok</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Nama Blok</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Ha</b></td>
				<td style='text-align:center;' colspan='5' ><b>Biaya Rawat</b></td>
			</tr>
			    <td style='text-align:center;'><b>Upah</b></td>
			    <td style='text-align:center;'><b>Transport</b></td>
			    <td style='text-align:center;'><b>Material</b></td>
			    <td style='text-align:center;'><b>Total</b></td>
			    <td style='text-align:center;'><b>Cost/Ha</b></td>
			<tr class=rowheader>
			</tr>
			</thead><tbody>";
			


	
	while($dtgroup=mysql_fetch_assoc($grouplist)){
	  $kd_supplier = $dtgroup['supplierid'];	
      $subt_upah = 0;$subt_transport = 0;$subt_material = 0;$subt_all = 0;	  
	  $stream.="<tr class=rowcontent>
	               <td bgcolor='#FFFF99' style='text-align:left;' colspan ='9' ><b>".$dtgroup['namasupplier']."</b></td>
	            </tr>";
        $nourut=1;
        $w_supplier = " AND supplierid = '".$kd_supplier."' ";
        $qDataLpj = " SELECT kodeblok, luasareaproduktif luasblok, SUM(upah) upah, SUM(transport) transport, SUM(material) material, 
							 SUM(umum) umum, SUM(total) total, namasupplier, supplierid, namablok  
					  FROM ".$dbname.".v_subq_lpj_rawat   
					  ".$whered." ".$w_supplier."
					  GROUP BY kodeblok
					  ORDER BY namasupplier,kodeblok
					";
        //print_r($qDataLpj);exit;
		$datalist=mysql_query($qDataLpj) or die(mysql_error($conn));
        while($dtdetil=mysql_fetch_assoc($datalist)){
			$costha = $dtdetil['total']/$dtdetil['luasblok'];
			$stream.="<tr class=rowcontent>
			            <td style='text-align:center;'>".$nourut++."</td>
						<td style='text-align:left;'>".$dtdetil['kodeblok']."</td>
						<td style='text-align:left;'>".$dtdetil['namablok']."</td>
						<td style='text-align:left;'>".$dtdetil['luasblok']."</td>
						<td style='text-align:right;'>".number_format($dtdetil['upah'],2)."</td>
						<td style='text-align:right;'>".number_format($dtdetil['transport'],2)."</td>
						<td style='text-align:right;'>".number_format($dtdetil['material'],2)."</td>
						<td style='text-align:right;'>".number_format($dtdetil['total'],2)."</td>
						<td style='text-align:right;'>".number_format($costha,2)."</td>
			          </tr>";
			$subt_upah = $subt_upah + $dtdetil['upah'];		  
			$subt_transport = $subt_transport + $dtdetil['transport'];		  
			$subt_material = $subt_material + $dtdetil['material'];		  
			$subt_all = $subt_all + $dtdetil['total'];

            		
        }
       	
        $gt_upah = $gt_upah + $subt_upah;
			$gt_transport = $gt_transport + $subt_transport;
			$gt_material = $gt_material + $subt_material;
			$gt_all = $gt_all + $subt_all;		
			
	   $stream.="<tr class=rowcontent>
	               <td bgcolor='#e9de9a' style='text-align:left;' colspan ='4' ><b>Sub Total</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_upah,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_transport,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_material,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_all,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:left;' ><b></b></td>
	            </tr>"; 
	}
	
	$stream.="<tr class=rowcontent>
	               <td bgcolor='#eed66c' style='text-align:left;' colspan ='4' ><b>Grand Total</b></td>
	               <td bgcolor='#eed66c' style='text-align:right;' ><b>".number_format($gt_upah,2)."</b></td>
	               <td bgcolor='#eed66c' style='text-align:right;' ><b>".number_format($gt_transport,2)."</b></td>
	               <td bgcolor='#eed66c' style='text-align:right;' ><b>".number_format($gt_material,2)."</b></td>
	               <td bgcolor='#eed66c' style='text-align:right;' ><b>".number_format($gt_all,2)."</b></td>
	               <td bgcolor='#eed66c' style='text-align:left;' ><b></b></td>
	            </tr>"; 
	/*
	while($barList=mysql_fetch_assoc($resList)){
		$stream.="<tr class=rowcontent>
			<td style='text-align:right;'>".$nourut++."</td>
				<td>".$barList['periode']."</td>
				<td>".$nmSup[$barList['namakud']]."</td>
				<td style='text-align:right;'>".number_format($barList['upah'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['material'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['transport'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['lainnya'],2)."</td>
				<td style='text-align:right;'>".number_format(($barList['upah']+$barList['material']+$barList['transport']+$barList['lainnya']),2)."</td>
			</tr>";
	}
	*/
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
            $nop_=$_SESSION['lang']['laporan']."_LPJ_KUD_rawat_".$periode."-".date('YmdHis');
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