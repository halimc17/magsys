<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$carikodeorg=checkPostGet('carikodeorg','');
	$carinamainv=checkPostGet('carinamainv','');
	$carikaryawan=checkPostGet('carikaryawan','');
	$cariruangan=checkPostGet('cariruangan','');
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
		$where.=" and a.nik='".$carikaryawan."'";
	}
	if($cariruangan!=''){
		$where.=" and a.ruangan like '%".$cariruangan."%'";
	}
	$strz ="select a.*,c.namasupplier as supplier from ".$dbname.".log_invpanen a 
			left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
			where ".$where." 
			order by a.kodeorg,a.kodebarang,a.kodeinventaris";
	//exit('Warning: '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	$stream2="";
	if($_GET['type']=='excel'){
		$stream2.="<table class=sortable border=0 cellspacing=1>
					<tr>
						<td colspan=8 align=center><h2>"."INVENTARIS PERALATAN KEBUN"."</h2></td>
					</tr>
					<tr>
					</tr>
				</table>
				<table class=sortable border=1 cellspacing=1>";
	}else{
		$stream2.="<table class=sortable border=0 cellspacing=1>";
	}
	$stream2.="
		<thead>
			<tr class=rowcontent>
				<td rowspan=1 width='2%' align=center valign=center>No</td>
				<td rowspan=1 width='6%' align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td rowspan=1 width='20%' align=center>".$_SESSION['lang']['namabarang']."</td>
				<td rowspan=1 width='4%' align=center>".$_SESSION['lang']['satuan']."</td>
				<td rowspan=1 width='20%' align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td rowspan=1 width='20%' align=center>".$_SESSION['lang']['jumlah']."</td>
				<td rowspan=1 width='20%'  align=center>".$_SESSION['lang']['keterangan']."</td>";
	$stream2.="</tr>
		</thead>
		<tbody>";
	if($row==0){
		$stream2.="<tr class=rowcontent>";
		$stream2.="<td colspan=8>Tidak ada Data...!</td>";
		$stream2.="</tr>";
	}else{
		while($barz=mysql_fetch_object($resz)){
			$namakaryawan=$barz->namakaryawan;
			$no+=1;
            $stream2.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=left>".$barz->kodebarang."</td>
						<td align=left>".$barz->namainventaris."</td>
						<td align=left>".$barz->satuan."</td>
						<td align=left>".$barz->namakaryawan."</td>
						<td align=left>".$barz->jumlah."</td>
						<td align=left>".$barz->merkinventaris.' '.$barz->tipeinventaris.' '.$barz->ukuran.' '.$barz->warna."</td>
						<td align=left>".$barz->ketinventaris."</td>
					</tr>";
		}
	}
	$stream2.="</tbody></table>";
	if($_GET['type']!='excel'){
		echo $stream2;
	}else{
		$nop_="Inventaris_Karyawan_".$namakaryawan."_".date("His");
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
