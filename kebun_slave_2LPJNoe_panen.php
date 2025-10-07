<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



$proses=checkPostGet('proses','');
if($proses=='excel'){
    $kodept=checkPostGet('kodept1','');
	$periode=checkPostGet('periode1','');
	$namakud=checkPostGet('namakud1','');
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
	  $whered.=" AND A.tanggal like '".$periode."%' ";
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
$gt_upah = 0;$gt_transport = 0;$gt_material = 0;$gt_all = 0;$gt_kirim = 0;
$qGroupkud = "SELECT distinct(supplierid), namasupplier 
              FROM ".$dbname.".v_subq_lpj_panen
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
			<td colspan=8><b>Laporan PertanggungJawaban Panen</b></td>
		</tr>";
	}
	$stream.="<tr class=rowheader>
				<td style='text-align:center;' rowspan='2' ><b>No</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Blok</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Nama Blok</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Ha</b></td>
				<td style='text-align:center;' rowspan='2' ><b>Kirim</b></td>
				<td style='text-align:center;' colspan='5' ><b>Biaya Panen</b></td>
			</tr>
			    <td style='text-align:center;'><b>Upah</b></td>
			    <td style='text-align:center;'><b>Transport</b></td>
			    <td style='text-align:center;'><b>Material</b></td>
			    <td style='text-align:center;'><b>Total</b></td>
			    <td style='text-align:center;'><b>Cost/Kg</b></td>
			<tr class=rowheader>
			</tr>
			</thead><tbody>";
			


	
	while($dtgroup=mysql_fetch_assoc($grouplist)){
	  $kd_supplier = $dtgroup['supplierid'];	 
	  $subt_upah = 0;$subt_transport = 0;$subt_material = 0;$subt_all = 0;$subt_kirim = 0;	 
	  $stream.="<tr class=rowcontent>
	              <td bgcolor='#FFFF99' style='text-align:left;' colspan ='10' ><b>".$dtgroup['namasupplier']."</b></td>
	            </tr>";
        $nourut=1;
       $w_supplier = " AND A.supplierid = '".$kd_supplier."' ";
	    /*
		 $qDataLpj = " SELECT A.kodeblok, A.luasareaproduktif luasblok, SUM(A.upah) upah, SUM(A.transport) transport, SUM(A.material) material, 
							 SUM(A.umum) umum, SUM(A.total) total, A.namasupplier, A.supplierid, SUM(B.kgwb) AS kirim, A.namablok   
					  FROM ".$dbname.".v_subq_lpj_panen2 A
                         LEFT JOIN ".$dbname.".kebun_spb_vw B ON (A.`kodeblok` = B.`blok` AND A.`tanggal` = B.`tanggal`)					  
					  ".$whered." ".$w_supplier."
					  GROUP BY A.kodeblok
					  ORDER BY A.namasupplier,A.kodeblok
					";
		*/
        $qDataLpj = " SELECT A.kodeblok, A.luasareaproduktif luasblok, SUM(A.upah) upah, SUM(A.transport) transport, SUM(A.material) material, 
							 SUM(A.umum) umum, SUM(A.total) total, A.namasupplier, A.supplierid,  A.namablok 
                             ,(SELECT SUM(B.kgwb) FROM owl.kebun_spb_vw B WHERE A.`kodeblok` = B.blok AND A.`tanggal` = B.`tanggal`) AS kirim							 
					  FROM ".$dbname.".v_subq_lpj_panen2 A
                      ".$whered." ".$w_supplier."
					  GROUP BY A.kodeblok
					  ORDER BY A.namasupplier,A.kodeblok
					";
	    //print_r($qDataLpj);exit;			
        $datalist=mysql_query($qDataLpj) or die(mysql_error($conn));
        while($dtdetil=mysql_fetch_assoc($datalist)){
		    if($dtdetil['kirim'] > 0){
			 $costha = $dtdetil['total']/$dtdetil['kirim'];
			}else{
			 $costha = 0;
			}
			$stream.="<tr class=rowcontent>
			            <td style='text-align:center;'>".$nourut++."</td>
						<td style='text-align:left;'>".$dtdetil['kodeblok']."</td>
						<td style='text-align:left;'>".$dtdetil['namablok']."</td>
						<td style='text-align:left;'>".$dtdetil['luasblok']."</td>
						<td style='text-align:left;'>".number_format($dtdetil['kirim'],2)."</td>
						<td style='text-align:left;'>".number_format($dtdetil['upah'],2)."</td>
						<td style='text-align:left;'>".number_format($dtdetil['transport'],2)."</td>
						<td style='text-align:left;'>".number_format($dtdetil['material'],2)."</td>
						<td style='text-align:left;'>".number_format($dtdetil['total'],2)."</td>
						<td style='text-align:left;'>".number_format($costha,2)."</td>
			          </tr>";
					  
			$subt_kirim = $subt_kirim + $dtdetil['kirim'];		  
			$subt_upah = $subt_upah + $dtdetil['upah'];		  
			$subt_transport = $subt_transport + $dtdetil['transport'];		  
			$subt_material = $subt_material + $dtdetil['material'];		  
			$subt_all = $subt_all + $dtdetil['total'];		  
        }
       	
            $gt_kirim = $gt_kirim + $subt_kirim;
            $gt_upah = $gt_upah + $subt_upah;
			$gt_transport = $gt_transport + $subt_transport;
			$gt_material = $gt_material + $subt_material;
			$gt_all = $gt_all + $subt_all;	

        $stream.="<tr class=rowcontent>
	               <td bgcolor='#e9de9a' style='text-align:left;' colspan ='4' ><b>Sub Total</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_kirim,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_upah,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_transport,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_material,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:right;' ><b>".number_format($subt_all,2)."</b></td>
	               <td bgcolor='#e9de9a' style='text-align:left;' ><b></b></td>
	            </tr>"; 			
	
	}
	$kd_supplier = '';
	
	$stream.="<tr class=rowcontent>
	               <td bgcolor='#eed66c' style='text-align:left;' colspan ='4' ><b>Grand Total</b></td>
	               <td bgcolor='#eed66c' style='text-align:right;' ><b>".number_format($gt_kirim,2)."</b></td>
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
            $nop_=$_SESSION['lang']['laporan']."_LPJ_KUD_panen_".$periode."-".date('YmdHis');
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