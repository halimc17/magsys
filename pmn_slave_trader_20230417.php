<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//##thn##pilInp##karyawanId##idKomponen##jmlhDt##method##tpKary
$method=$_POST['method'];
$kdUnit=$_POST['kdUnit'];
$kdCust=$_POST['kdCust'];
$nokontrakext=$_POST['nokontrakext'];
$tanggalext=(tanggalsystem($_POST['tanggalext']));
$kdBrg=$_POST['kdBrg'];
$jmlExt=$_POST['jmlExt'];
$hrgExt=$_POST['hrgExt'];
$nilaiExt=$_POST['nilaiExt'];
$ppnExt=$_POST['ppnExt'];
$catatan=$_POST['catatan'];
$nokontrakpembanding=$_POST['nokontrakpembanding'];

$kdUnitCr=$_POST['kdUnitCr'];
$kdCustCr=$_POST['kdCustCr'];
$kdBrgCr=$_POST['kdBrgCr'];
$noKontrakCr=$_POST['noKontrakCr'];

switch($method){
	case'insert':
		if($kdUnit==''){
			echo "Error: Unit is obligatory";
			exit;
		}
		if($kdCust==''){
			echo "Error: silakan pilih Customer";
			exit;
		}
		if($nokontrakext==''){
			echo "Error: Contract is obligatory";
			exit;
		}
		if($kdBrg==''){
			echo "Error: silakan pilih Barang";
			exit;
		}
		if($nokontrakpembanding==''){
			echo "Error: silakan pilih Kontrak Pembanding";
			exit;
		}
		if(intval($nilaiExt)=='0'){
			echo "Error: Please fill amount(jumlah) ".$nilaiExt;
			exit;
		}
		$i="delete from ".$dbname.".pmn_traderht where nokontrakext='".$nokontrakext."'";
		if(mysql_query($i)){
			$n="insert into ".$dbname.".pmn_traderht values ('".$nokontrakext."','".$tanggalext."','".$kdUnit."','".$kdCust."','".$kdBrg."','".$jmlExt."','IDR','1','".$hrgExt."','".$nilaiExt."','".$ppnExt."','".$catatan."','".$nokontrakpembanding."')";
			if(!mysql_query($n)){
				echo"Gagal :".mysql_error($conn);
			}
		}else{
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
		break;
	case'loadData':
		$whrd="";
		if($kdUnitCr!=''){
			$whrd.=" and a.kodeorg='".$kdUnitCr."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$whrd.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe<>'HOLDING' and detail='1')";
			}else{
				if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
					$whrd.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
				}
			}
		}
		if($kdCustCr!=''){
			$whrd.=" and a.kodecustomer='".$kdCustCr."'";
		}
		if($kdBrgCr!=''){
			$whrd.=" and a.kodebarang='".$kdBrgCr."'";
		}
		if($noKontrakCr!=''){
			$whrd.=" and a.nokontrakext='".$noKontrakCr."'";
		}

		$limit=20;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_traderht a where true ".$whrd." ";
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		/*
		$str="select a.*,b.namabarang,c.namacustomer,if(isnull(d.jmlkirim),0,d.jmlkirim) as jmlkirim,if(a.qtykontrak-d.jmlkirim=0,1,0) as stsselisih from ".$dbname.".pmn_traderht a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang 
				left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer
				left join (select nokontrakext,sum(beratbersih) as jmlkirim from ".$dbname.".pmn_traderdt group by nokontrakext) d on d.nokontrakext=a.nokontrakext 
				where true ".$whrd."
				order by stsselisih,a.kodeorg,a.tanggalext desc,a.nokontrakext
				limit ".$offset.",".$limit." ";
		*/
		$str="select a.*,b.namabarang,c.namacustomer,if(isnull(d.jmlkirim),0,d.jmlkirim) as jmlkirim,if(a.qtykontrak-d.jmlkirim=0,1,0) as stsselisih from ".$dbname.".pmn_traderht a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang 
				left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer
				left join (select nokontrakext,sum(beratbersih) as jmlkirim from ".$dbname.".pmn_traderdt group by nokontrakext) d on d.nokontrakext=a.nokontrakext 
				where true ".$whrd."
				order by a.tanggalext desc,a.nokontrakext
				limit ".$offset.",".$limit." ";
		//exit('Warning : '.$str);
		$no=$maxdisplay;
		$res=mysql_query($str);
		$oow=mysql_num_rows($res);
		if($oow==0){
			echo"<tr class=rowcontent><td colspan=13>".$_SESSION['lang']['dataempty']."</td></tr>";
		}else{
			while($bar=mysql_fetch_assoc($res)){
				$no+=1;
				echo"<tr class=rowcontent>
						<td>".$no."</td>
						<td>".$bar['kodeorg']."</td>
						<td>".$bar['namacustomer']."</td>
						<td>".$bar['nokontrakext']."</td>
						<td>".$bar['tanggalext']."</td>
						<td>".$bar['namabarang']."</td>
						<td align=right>".number_format($bar['qtykontrak'],0)."</td>
						<td align=right>".number_format($bar['hargaext'],2)."</td>";
//				echo"	<td align=right>".number_format($bar['nilaikontrakext'],0)."</td>
//						<td align=right>".number_format($bar['nilaippnext'],0)."</td>
//						<td align=right>".number_format($bar['nilaikontrakext']+$bar['nilaippnext'],0)."</td>";
				echo"	<td>".$bar['nokontrakpembanding']."</td>
						<td align=right>".number_format($bar['jmlkirim'],0)."</td>
						<td>".$bar['catatan']."</td>";
				if($bar['jmlkirim']>0){
					//echo"<td></td>";
					echo"<td align=center>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['kodecustomer']."','".$bar['nokontrakext']."','".tanggalnormal($bar['tanggalext'])."','".$bar['kodebarang']."','".$bar['qtykontrak']."','".$bar['hargaext']."','".$bar['nilaikontrakext']."','".$bar['nilaippnext']."','".$bar['catatan']."','".$bar['nokontrakpembanding']."');\">
						</td>";
				}else{
					echo"<td align=center>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['kodecustomer']."','".$bar['nokontrakext']."','".tanggalnormal($bar['tanggalext'])."','".$bar['kodebarang']."','".$bar['qtykontrak']."','".$bar['hargaext']."','".$bar['nilaikontrakext']."','".$bar['nilaippnext']."','".$bar['catatan']."','".$bar['nokontrakpembanding']."');\">
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$bar['nokontrakext']."');\">
						</td>";
				}
                echo"</tr>";	
			}
		}
		echo"<tr class=rowheader>
				<td colspan=13 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".$jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>";
		break;
//	case'updateData':
//		$sdel="delete from ".$dbname.".pmn_traderht where nokontrakext='".$_POST['nokontrakext']."'";
//		if(mysql_query($sdel)){
//			$sIns="insert into ".$dbname.".pmn_traderht values ('".$nokontrakext."','".$tanggalext."','".$kdUnit."','".$kdCust."','".$kdBrg."','".$jmlExt."','IDR','1','".$hrgExt."','".$nilaiExt."','".$ppnExt."','".$catatan."')";
//			if(!mysql_query($sIns)){
//				echo"Gagal".mysql_error($conn);
//			}
//		}
//		break;
	case'updateData':
		$upd="update ".$dbname.".pmn_traderht set tanggalext='$tanggalext',kodeorg='$kdUnit',kodecustomer='$kdCust',kodebarang='$kdBrg',qtykontrak='$jmlExt'
		,matauang='IDR',kurs='1',hargaext='$hrgExt',nilaikontrakext='$nilaiExt',nilaippnext='$ppnExt',catatan='$catatan',nokontrakpembanding='$nokontrakpembanding' 
		where nokontrakext='".$nokontrakext."'";
		if(!mysql_query($upd)){
			echo"Gagal".$upd."____".mysql_error($conn);
		}
		break;
	case'delData':
		$sdel="delete from ".$dbname.".pmn_traderht where nokontrakext='".$nokontrakext."'";
		if(!mysql_query($sdel)){
			echo"Gagal".$sdel."____".mysql_error($conn);
		}
		break;

	case'getKontrak':
		//Pilih Kontrak
		$i="select nokontrak from ".$dbname.".pmn_kontrakjual where kodebarang='".$kdBrg."' order by tanggalkontrak desc";
		$optKontrak="<option value=''></option>";
		$n=mysql_query($i) or die (mysql_error($conn));
		while($d=mysql_fetch_assoc($n)){
			$optKontrak.="<option value='".$d['nokontrak']."'>".$d['nokontrak']."</option>";
		}
		echo $optKontrak;
		break;

	default:
}
?>
