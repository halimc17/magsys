<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$method=$_POST['method'];

########cara hitung tanggal kemarin###############
        $tgl =  tanggalsystem($_POST['tanggal']);//merubah dari 10-10-2014 menjadi 20141010
        $newdate = strtotime('-1 day',strtotime($tgl));
        $newdate = date('Y-m-d', $newdate);
        
$notransaksi=$_POST['notransaksi'];
$kodeorg=$_POST['kodeorg'];
$tgl=tanggalsystem($_POST['tanggal']);

#tgl kmrn
$tglKmrn = strtotime('-1 day',strtotime($tgl));
$tglKmrn = date('Y-m-d', $tglKmrn);

$trpcode=$_POST['trpcode'];
$nospp=$_POST['nospp'];
$hargakg=$_POST['hargakg'];

$noKontrakCr=$_POST['noKontrakCr'];
$nosppCr=$_POST['nosppCr'];
$trpcodeCr=$_POST['trpcodeCr'];
$notransaksiCr=$_POST['notransaksiCr'];

switch($method)
{
    case'loadData':
		if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
			$kdorg="select kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','PABRIK') and 
					kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."')";
		}else{
			$kdorg="select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
		}
		$whrd="";
		if($noKontrakCr!=''){
			$whrd.=" and e.nokontrak='".$noKontrakCr."'";
		}
		if($nosppCr!=''){
			$whrd.=" and a.keterangan='".$nosppCr."'";
		}
		if($trpcodeCr!=''){
			$whrd.=" and a.koderekanan='".$trpcodeCr."'";
		}
		if($notransaksiCr!=''){
			$whrd.=" and a.notransaksi='".$notransaksiCr."'";
		}
		$limit=20;
		$page=0;
		$str="select count(a.notransaksi) as jlhbrs from ".$dbname.".log_spkht a
			LEFT JOIN ".$dbname.".log_spkdt b on b.notransaksi=a.notransaksi
			LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun like '116%') c on c.notransaksi=a.notransaksi
			LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun like '2%') j on j.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".log_5supplier d on d.supplierid=a.koderekanan
			LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on e.nodo=a.keterangan
			LEFT JOIN ".$dbname.".log_baspk f on f.notransaksi=a.notransaksi
			where b.kodekegiatan like '8110102%' and e.nokontrak<>'' and a.kodeorg in (".$kdorg.")".$whrd." 
					and year(a.tanggal)=year(now())
			ORDER BY f.statusjurnal,a.kodeorg,a.tanggal desc,a.koderekanan desc
			";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jlhbrs=$bar->jlhbrs;
		}
		if(isset($_POST['page'])){
	 		$page=$_POST['page'];
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$str="select a.kodeorg,a.notransaksi,a.tanggal,a.koderekanan,d.namasupplier,d.kodetimbangan,e.nokontrak,a.keterangan
			,b.kodekegiatan,f.hasilkerjarealisasi,b.satuan,f.jumlahrealisasi,c.nilai as nilaippn,j.nilai as nilaipph,if(f.statusjurnal=1
			,f.statusjurnal,0) as statusjurnal
			from ".$dbname.".log_spkht a
			LEFT JOIN ".$dbname.".log_spkdt b on b.notransaksi=a.notransaksi
			LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun like '116%') c on c.notransaksi=a.notransaksi
			LEFT JOIN (select * from ".$dbname.".log_spk_tax where noakun like '2%') j on j.notransaksi=a.notransaksi
			LEFT JOIN ".$dbname.".log_5supplier d on d.supplierid=a.koderekanan
			LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on e.nodo=a.keterangan
			LEFT JOIN ".$dbname.".log_baspk f on f.notransaksi=a.notransaksi
			where b.kodekegiatan like '8110102%' and e.nokontrak<>'' and a.kodeorg in (".$kdorg.")".$whrd."
					and year(a.tanggal)=year(now())
			ORDER BY f.statusjurnal,a.kodeorg,a.tanggal desc,a.koderekanan desc limit ".$offset.",".$limit."
			";
		$res=mysql_query($str);
		$no=$maxdisplay;
		while($bar=mysql_fetch_object($res)){
			$drcl="onclick=\"previewDetail('".$bar->kodeorg."','".$bar->notransaksi."','".
					$bar->keterangan."','".$bar->koderekanan."','".$bar->namasupplier."',event);\" style='cursor:pointer'";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=left>".$bar->notransaksi."</td>
					<td ".$drcl." align=center>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=left>".$bar->namasupplier."</td>
					<td ".$drcl." align=left>".$bar->nokontrak."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td ".$drcl." align=right>".number_format($bar->hasilkerjarealisasi,0,'.',',')."</td>
					<td ".$drcl." align=center>".$bar->satuan."</td>
					<td ".$drcl." align=right>".number_format($bar->jumlahrealisasi,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->nilaippn,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->nilaipph,0,'.',',')."</td>
					<td ".$drcl." align=right>".number_format($bar->jumlahrealisasi+$bar->nilaippn-$bar->nilaipph,0,'.',',')."</td>";
			if($bar->statusjurnal==0){
				echo"
					<td align=center>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."','".$bar->koderekanan."','".$bar->nokontrak."'
						,'".$bar->keterangan."','".$bar->hasilkerjarealisasi."','".$bar->jumlahrealisasi."','".$bar->nilaippn."','".$bar->nilaipph."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->notransaksi."');\">&nbsp
						<img src=images/skyblue/posting.png class=resicon title='Posting' onclick=\"postingData('".$bar->kodeorg."','".$bar->koderekanan."','".$bar->notransaksi."','".$bar->kodeorg."','0000000001','".$bar->kodekegiatan."','".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->hasilkerjarealisasi."','".$bar->jumlahrealisasi."');\">&nbsp
						<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewBA('".$bar->kodeorg."','".$bar->notransaksi."',event);\">
					</td>";
			}else{
				echo"
					<td align=center>
						<img src=images/skyblue/posted.png class=resicon title='Posted'>&nbsp
						<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewBA('".$bar->kodeorg."','".$bar->notransaksi."',event);\">
					</td>";
			}
			echo"</tr>";
		}
		echo"<tr><td colspan=13 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br>
					<button class=mybutton onclick=cariSPP(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariSPP(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>";	   
    break;
    
	case'getSPP':
		//exit("Warning: ").$_POST['keterangan'];
		$kodeCust='';
		$iCust="select kodetimbangan from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['kodetimbangan'];
		}
		//$iSPP="select distinct(nosipb) as nosipb from ".$dbname.".pabrik_timbangan where kodecustomer='".$kodeCust."' and intex='0' order by tanggal desc";	
		$iSPP="select m.*,n.tanggaldo from (select distinct(a.nosipb) as nosipb,if(b.statusjurnal=1,1,0) as stsjrn from ".$dbname.".pabrik_timbangan a 
								LEFT JOIN (select x.*,y.statusjurnal from ".$dbname.".log_spkht x
											LEFT JOIN ".$dbname.".log_baspk y on y.notransaksi=x.notransaksi
											where x.keterangan<>'' and x.koderekanan='".$trpcode."') b on b.keterangan=a.nosipb
								where a.kodecustomer='".$kodeCust."' and a.intex='0'
										and year(a.tanggal)>=year(now())-1
								) m
				LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman n on n.nodo=m.nosipb
				where m.stsjrn<>1
				ORDER BY n.tanggaldo desc
				";
        $nSPP=  mysql_query($iSPP) or die (mysql_error($conn));
		$optSPP="<option value=''>Pilih Data</option>";
        while($dSPP=  mysql_fetch_assoc($nSPP)){
			$optSPP.="<option value='".$dSPP['nosipb']."'>".$dSPP['nosipb']."</option>";
		}
		echo $optSPP;
		break;
    
	case'getNo':
		$kodeCust='';
		$iCust="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['namasupplier'];
		}
		$trp2=explode(",",$kodeCust);
		$trp=explode(" ",$trp2[0]);
		$singkatantrp='';
		for ($i = 0; $i < count($trp); $i++) {
			$singkatantrp.=substr($trp[$i],0,1);
		}
		$iSPP="select nokontrak from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$nospp."'";
        $nSPP=  mysql_query($iSPP) or die (mysql_error($conn));
        while($dSPP=  mysql_fetch_assoc($nSPP)){
			$nokontrak=$dSPP['nokontrak'];
		}
		$str2=explode("_",$nokontrak);
		$kmdt=explode("/",$str2[1]);
		$bln=intval(substr($tgl,4,2))-1;
		$thn=substr($tgl,0,4);
		$romawi='I##II##III##IV##V##VI##VII##VIII##IX##X##XI##XII';
		$rmw2=explode("##",$romawi);

		$iSPK="select max(left(notransaksi,3)) as maxno from ".$dbname.".log_spkht where notransaksi like '%/BAP_".$kmdt[0]."/".$_SESSION['org']['kodeorganisasi']."_".$singkatantrp."/".$rmw2[$bln]."/".$thn."'";
		//exit('Warning: '.$iSPK);
        $nSPK=  mysql_query($iSPK) or die (mysql_error($conn));
		$maxno='001';
        while($dSPK=  mysql_fetch_assoc($nSPK)){
			$maxno=$dSPK['maxno']+1;
		}
		$no=sprintf("%03d",substr($maxno,0,3));
		$notransaksi=$no."/BAP_".$kmdt[0]."/".$_SESSION['org']['kodeorganisasi']."_".$singkatantrp."/".$rmw2[$bln]."/".$thn;
		//exit('Warning: '.$notransaksi);
		echo $notransaksi;
		break;
    
	case'getKG':
		$kodeCust='';
		$iCust="select kodetimbangan from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['kodetimbangan'];
		}
		if($kodeCust!=''){
			$iSPP="select sum(beratbersih) as jumlahkg from ".$dbname.".pabrik_timbangan where nosipb='".$nospp."' and kodecustomer='".$kodeCust."' and intex='0'";
			//exit('Warning: '.$iSPP);
			$nSPP=  mysql_query($iSPP) or die (mysql_error($conn));
			$jumlahkg=0;
			while($dSPP=  mysql_fetch_assoc($nSPP)){
				$jumlahkg=$dSPP['jumlahkg'];
			}
			echo $jumlahkg;
		}
		break;
    
    case'getDetailBA':
		$vw=checkPostGet('vw','');
		$kodeCust='';
		$namaSupp='';
		$iCust="select kodetimbangan,namasupplier from ".$dbname.".log_5supplier where supplierid='".$trpcode."'";	
        $nCust=mysql_query($iCust) or die (mysql_error($conn));
        while($dCust=mysql_fetch_assoc($nCust)){
			$kodeCust=$dCust['kodetimbangan'];
			$namaSupp=$dCust['namasupplier'];
		}
		if($kodeCust!=''){
			$iSPP="select a.* from ".$dbname.".pabrik_timbangan a where a.nosipb='".$nospp."' and a.kodecustomer='".$kodeCust."' and intex='0' order by a.tanggal";
			$nSPP=  mysql_query($iSPP) or die (mysql_error($conn));
			if($vw!='excel'){
				//echo "<img src=images/excel.jpg class=resicon title='Excel' onclick=\"previewDetail('".$kodeorg."','".$notransaksi."','".$nospp."','".$trpcode."','".$namaSupp."',event,'excel');\"><BR>";
				echo "<img onclick=\"dataKeExcel('".$kodeorg."','".$notransaksi."','".$nospp."','".$trpcode."','".$namaSupp."',event,'excel')\" src=\"images/excel.jpg\" class=\"resicon\" title=\"MS.Excel\"><BR>";

			}
			$tab2.='No SPP/DO : '.$nospp.'<BR>';
			$tab2.='Transportir : '.$namaSupp.'<BR>';
			$tab2.="<table class=sortable border=0 cellspacing=1>
					<thead>
						<tr class=rowheader>
							<td>No</td>
							<td>No. Ticket</td>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>".$_SESSION['lang']['NoKontrak']."</td>
							<td>".$_SESSION['lang']['nosipb']."</td>
							<td>".$_SESSION['lang']['nokendaraan']."</td>
							<td>".$_SESSION['lang']['supir']."</td>
							<td>".$_SESSION['lang']['beratMasuk']."</td>
							<td>".$_SESSION['lang']['beratKeluar']."</td>
							<td>".$_SESSION['lang']['beratBersih']."</td>
						</tr>
					</thead>
					<tbody>";
			$no=0;
			while($dSPP=mysql_fetch_assoc($nSPP)){
				$masuk+=$dSPP['beratmasuk'];
				$keluar+=$dSPP['beratkeluar'];
				$netto+=$dSPP['beratbersih'];
				$no+=1;
				$tab2.="<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$dSPP['notransaksi']."</td>
							<td>".tanggalnormal($dSPP['tanggal'])."</td>
							<td>".$dSPP['nokontrak']."</td>
							<td>".$dSPP['nosipb']."</td>
							<td>".$dSPP['nokendaraan']."</td>
							<td>".$dSPP['supir']."</td>
							<td align=right>".number_format($dSPP['beratmasuk'],0,'.',',')."</td>
							<td align=right>".number_format($dSPP['beratkeluar'],0,'.',',')."</td>
							<td align=right>".number_format($dSPP['beratbersih'],0,'.',',')."</td>
						</tr>";
			}
			$tab2.="<tr class=rowcontent>
						<td bgcolor='#FEDEFE' colspan=7>Total</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($masuk,0,'.',',')."</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($keluar,0,'.',',')."</td>
						<td bgcolor='#FEDEFE' align=right>".number_format($netto,0,'.',',')."</td>
					</tr>";
			$tab2.="</tbody></table>";
			if($vw=='excel'){
				$judul='Laporan_Pengiriman';
				$tab2="<h2>".$judul."</h2>".$tab2;
				$tab2.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
				$nop_=$judul.date("YmdHis");
				if(strlen($tab2)>0){
					$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
					gzwrite($gztralala, $tab2);
					gzclose($gztralala);
					echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls.gz';
						</script>";
					//exit('Warning: 5. '.$nop_);
					
					/*
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
							}
						 }	
						closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$tab2)){
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
					*/
				}
			}else{
				echo $tab2;
			}
		}
    break;
}
?>
