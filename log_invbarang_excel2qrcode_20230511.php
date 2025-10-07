<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include "phpqrcode/qrlib.php";
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$carikodeorg=checkPostGet('carikodeorg','');
	$carinamainv=checkPostGet('carinamainv','');
	$carikaryawan=checkPostGet('carikaryawan','');
	$cariruangan=checkPostGet('cariruangan','');
	$page=checkPostGet('page',0);
	$where="True";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where="True";
	}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where="a.kodeorg not like '%HO' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
	}else{
		$where="a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	if($carikodeorg!=''){
		$where.=" and a.kodeorg='".$carikodeorg."'";
	}
	if($carinamainv!=''){
		$where.=" and a.namainventaris like '%".$carinamainv."%'";
	}
	if($carikaryawan!=''){
		//$where.=" and a.nik='".$carikaryawan."'";
		$where.=" and a.namakaryawan like '%".$carikaryawan."%'";
	}else{
		if($_GET['type']=='excel'){
			//exit('Warning: Nama karyawan belum dipilh...!');
		}
	}
	if($cariruangan!=''){
		$where.=" and a.ruangan like '%".$cariruangan."%'";
	}
		$strb="select a.kodeinventaris from ".$dbname.".log_invbarang a 
				where ".$where." 
				order by a.kodeorg,a.kodebarang,a.kodeinventaris";
		//exit('Warning: '.$strb);
		$resb=mysql_query($strb);
		$jlhbrs=mysql_num_rows($resb);
		$limit=25;
		if((($page*$limit)+1)>$jlhbrs)
			$page=$page-1;
		if($page<0)
			$page=0;
		$offset=$page*$limit;
	//$strz ="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
	//		left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
	//		left join ".$dbname.".datakaryawan d on d.nik=a.nik
	//		where ".$where." 
	//		order by a.kodeorg,a.kodebarang,a.kodeinventaris";
	$strz ="select a.*,c.namasupplier as supplier from ".$dbname.".log_invbarang a 
			left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
			where ".$where." 
			order by a.kodeorg,a.kodebarang,a.kodeinventaris limit ".$offset.",".$limit."";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$no=0;
	$tempdir = "qrcode-img/";
	$quality="H"; // ini ada 4 pilihan yaitu L (Low), M(Medium), Q(Good), H(High)
	$ukuran=5; // 1 adalah yang terkecil, 10 paling besar
	$padding=1;
	if (!file_exists($tempdir))        //jika folder belum ada, maka buat
		mkdir($tempdir);
	$stream2="";
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=0 cellspacing=1 style='font-size:13;margin:64px;'>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$unit=$_SESSION['empl']['lokasitugas'];
	while($barz=mysql_fetch_object($resz)){
		$no+=1;
		if($no>25){
			break;
		}
		$kodeorg[$unit][$no]		=$barz->kodeorg;
		$kodebarang[$unit][$no]		=$barz->kodebarang;
		$kodeinventaris[$unit][$no]	=$barz->kodeinventaris;
		$namainventaris[$unit][$no]	=$barz->namainventaris;
		$merkinventaris[$unit][$no]	=$barz->merkinventaris;
		$tipeinventaris[$unit][$no]	=$barz->tipeinventaris;
		$ketinventaris[$unit][$no]	=$barz->ketinventaris;
		$nopo[$unit][$no]			=$barz->nopo;
		$tahun[$unit][$no]			=(substr($barz->tglperolehan,0,4)=='0000' ? '****' : substr($barz->tglperolehan,0,4));
		$nik[$unit][$no]			=$barz->nik;
		$karyawan[$unit][$no]		=$barz->namakaryawan;
		$tgluserterima[$unit][$no]	=$barz->tgluserterima;
		$lokasi[$unit][$no]			=$barz->lokasi;
		$qrbarang[$unit][$no]		=$barz->kodebarang."
".$barz->namainventaris."
".$barz->namakaryawan."
".$barz->tgluserterima."
".$barz->nopo;
		$namafile="qrcode-".$barz->kodeorg.$barz->kodebarang.$barz->kodeinventaris.".png";
		$path[$unit][$no]=$tempdir.$namafile;
		//QRCode::png($qrbarang[$unit][$no], $tempdir.$namafile, $quality, $ukuran, $padding);
		# ===== Baris 1 ====================
		$qrbrg=$qrbarang[$unit][$no];
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:9px;'><img src='https://chart.googleapis.com/chart?chs=63x63&cht=qr&chl=$qrbrg' style='width:63px;height:63px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:9px;'></td>";
		$stream2.="</tr>";
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:15px;'>".$kodeinventaris[$unit][$no]."</td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="</tr>";
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:15px;'><b>".$tahun[$unit][$no]."</b></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="</tr>";
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:15px;'>".$lokasi[$unit][$no]."</td>";
		$stream2.="<td align=center style='border:none;width:9px;height:15px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:15px;'></td>";
		$stream2.="</tr>";
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:9px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:9px;'></td>";
		$stream2.="</tr>";
		$stream2.="<tr>";
		$stream2.="<td align=center style='border:none;width:35px;height:10px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:10px;'></td>";
		$stream2.="<td align=center style='border:none;width:45px;height:10px;'></td>";
		$stream2.="<td align=center style='border:none;width:105px;height:10px;'></td>";
		$stream2.="<td align=center style='border:none;width:9px;height:10px;'></td>";
		$stream2.="<td align=center style='border:none;width:35px;height:10px;'></td>";
		$stream2.="</tr>";
		# ===== Selesai ====================
	}
	$stream2.="</table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Inventaris_Karyawan_QRCode"."_".date("His");
        if(strlen($stream2)>0){
			//$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
            //gzwrite($gztralala, $stream2);
			//gzclose($gztralala);
			// echo "<script language=javascript1.2>
			//    window.location='tempExcel/".$nop_.".xls.gz';
			//    </script>";
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream2)){
				echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
			}
			fclose($handle);
		}           
	}   
?>
