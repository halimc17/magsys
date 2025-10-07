<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$kdUnit=$_POST['kdUnit'];
	$kdCust=$_POST['kdCust'];
	$periode=$_POST['periode'];
	$kdBrg=$_POST['kdBrg'];
	$nokontrakext=$_POST['nokontrakext'];
	if($kdUnit=='')$kdUnit=$_GET['kdUnit'];
	if($kdCust=='')$kdCust=$_GET['kdCust'];
	if($periode=='')$periode=$_GET['periode'];
	if($kdBrg=='')$kdBrg=$_GET['kdBrg'];
	if($nokontrakext=='')$nokontrakext=$_GET['nokontrakext'];
	if($proses=='')$proses=$_GET['proses'];

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdUnit."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdUnit;

	#Filter parameter where 
	$whr.="b.posting='1'";
	$stream="";
	$judul="Laporan_Kontrak_Eksternal";
	if($kdUnit!=''){
		$whr.=" and a.kodeorg = '".$kdUnit."'";
		$judul.="_".$kdUnit;
	}
	if($kdCust!=''){
		$whr.=" and a.kodecustomer = '".$kdCust."'";
		$judul.="_".$kdCust;
	}
	if($periode!=''){
		$whr.=" and a.tanggalext like '".$periode."%'";
		$judul.="_".$periode;
	}
	if($kdBrg!=''){
		$whr.=" and a.kodebarang = '".$kdBrg."'";
		$judul.="_".$kdBrg;
	}
	if($nokontrakext!=''){
		$whr.=" and a.nokontrakext like '%".$nokontrakext."%'";
		$judul.="_".$nokontrakext;
	}
	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
	}
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
			<td ".$bgclr." rowspan=2>No</td>
			<td ".$bgclr." colspan=4>".$_SESSION['lang']['kontrak']." ".$_SESSION['lang']['induk']."</td>
			<td ".$bgclr." colspan=4>".$_SESSION['lang']['kontrak']." ".$_SESSION['lang']['eksternal']."</td>
			<td ".$bgclr." colspan=4>".$_SESSION['lang']['kontrak']." ".$_SESSION['lang']['pengiriman']." ".$_SESSION['lang']['eksternal']."</td>
		</tr>
        <tr>
			<td ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." (Kg)"."</td>
			<td ".$bgclr.">".$_SESSION['lang']['harga']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." (Kg)"."</td>
			<td ".$bgclr.">".$_SESSION['lang']['harga']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['NoKontrak']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
			<td ".$bgclr.">".$_SESSION['lang']['jumlah']." (Kg)"."</td>
			<td ".$bgclr.">".$_SESSION['lang']['harga']."</td>
		</tr></thead><tbody>";
	#ambil data kontrak external
	$str = "select a.*,b.nokontrak,b.nosipb,b.tanggal,b.beratbersih,if(d.ppn='0',d.hargasatuan,d.hargasatuan/1.1) as hargakirim
			,c.tanggalkontrak, c.kuantitaskontrak,if(c.ppn='0',c.hargasatuan,c.hargasatuan/1.1) as hargasatuan 
			from ".$dbname.".pmn_traderht a
			LEFT JOIN ".$dbname.".pmn_traderdt b on b.nokontrakext=a.nokontrakext
			LEFT JOIN ".$dbname.".pmn_kontrakjual c on c.nokontrak=a.nokontrakpembanding
			LEFT JOIN ".$dbname.".pmn_kontrakjual d on d.nokontrak=b.nokontrak
			where ".$whr."
			";
	//exit('Warning : '.$str);
	$res=mysql_query($str);
	$nokontrakextd="";
	$no=0;
	$gttlkuantitaskontrak=0;
	$gttlqtykontrak=0;
	$gttlberatbersih=0;
	while($bar=mysql_fetch_object($res)){
		$stream.="<tr class=rowcontent>";
		if($nokontrakextd!=$bar->nokontrakext){
			$no+=1;
			$stream.="
				<td align='center'>".$no."</td>
				<td>".$bar->nokontrakpembanding."</td>
				<td align='center'>".tanggalnormal($bar->tanggalkontrak)."</td>
				<td align='right'>".@number_format($bar->kuantitaskontrak,0)."</td>
				<td align='right'>".@number_format($bar->hargasatuan,2)."</td>
				<td>".$bar->nokontrakext."</td>
				<td align='center'>".tanggalnormal($bar->tanggalext)."</td>
				<td align='right'>".@number_format($bar->qtykontrak,0)."</td>
				<td align='right'>".@number_format($bar->hargaext,2)."</td>";
				$gttlkuantitaskontrak+=$bar->kuantitaskontrak;
				$gttlqtykontrak+=$bar->qtykontrak;
		}else{
			$stream.="<td colspan=9></td>";
		}
		$stream.="
			<td>".$bar->nokontrak."</td>
			<td align='center'>".tanggalnormal($bar->tanggal)."</td>
			<td align='right'>".@number_format($bar->beratbersih,0)."</td>
			<td align='right'>".@number_format($bar->hargakirim,2)."</td>";
		$stream.="</tr>";

		$gttlberatbersih+=$bar->beratbersih;
		$nokontrakextd=$bar->nokontrakext;
	}
	if($kdBrg!='' or $nokontrakext!=''){
		$stream.="
		<tr class=rowcontent>
			<td bgcolor='#FEDEFE' colspan=3 align='center'>Total</td>
			<td bgcolor='#FEDEFE' align='right'>".@number_format($gttlkuantitaskontrak,0)."</td>
			<td bgcolor='#FEDEFE' colspan=3></td>
			<td bgcolor='#FEDEFE' align='right'>".@number_format($gttlqtykontrak,0)."</td>
			<td bgcolor='#FEDEFE' colspan=3></td>
			<td bgcolor='#FEDEFE' align='right'>".@number_format($gttlberatbersih,0)."</td>
			<td bgcolor='#FEDEFE'></td>
		</tr>";
	}
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
          echo $stream;
        break;
        case 'excel':
			$stream="<h2>".$judul."</h2>".$stream;
		    $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $nop_="Laporan_Kontrak_Eksternal_".date("YmdHis");
            if(strlen($stream)>0){
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
				echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls.gz';
					  </script>";
            }
		break;
	}    
?>