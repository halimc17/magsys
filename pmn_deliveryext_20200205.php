<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/pmn_deliveryext.js'></script>

<?php
$kdUnitCr=checkPostGet('kdUnitCr','');
$kdCustCr=checkPostGet('kdCustCr','');
$kdBrgCr=checkPostGet('kdBrgCr','');
$noKontrakCr=checkPostGet('noKontrakCr','');
//if($kdUnitCr!=""){
//	exit('Warning :'.$kdUnitCr);
//}
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
			$whrd.=" and a.nokontrakext like '%".$noKontrakCr."%'";
		}

//Pilih Unit
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and detail='1'";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe!='HOLDING' and detail='1'";
}else{
    $i="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$optUnit="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</option>";
}
$optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>".$optUnit;

//Pilih Customer
$i="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer where statusinteks = 'Eksternal'";
$optCust="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optCust.="<option value='".$d['kodecustomer']."'>".$d['namacustomer']."</option>";
}
$optCust2="<option value=''>".$_SESSION['lang']['all']."</option>".$optCust;
$optCust="<option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optCust;

//Pilih Barang
$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%' and inactive='0'";
$optBrg="";
$n=mysql_query($i) or die (mysql_error($conn));
while($d=mysql_fetch_assoc($n)){
	$optBrg.="<option value='".$d['kodebarang']."'>".$d['namabarang']."</option>";
}
$optBrg2="<option value=''>".$_SESSION['lang']['all']."</option>".$optBrg;
$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optBrg;

$arr="##kdUnit##kdCust##nokontrakext##tanggalext##kdBrg##jmlExt##hrgExt##nilaiExt##ppnExt##catatan##method";
OPEN_BOX();
echo"<fieldset style='float:left;'>
		<legend><font size=2.5><b>".$_SESSION['lang']['header']." ".$_SESSION['lang']['kontrak']." ".$_SESSION['lang']['eksternal']."</b></legend></font>
		<div style=float:left;><img class=delliconBig src=images/refresh.png title='".$_SESSION['lang']['list']."' onclick=displayList(0) ></div>
		<table border=0>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td><td><select id=kdUnitCr style=width:175px;>".$optUnit2."</select></td>
				<td>".$_SESSION['lang']['vendor']."</td><td><select id=kdCustCr style=width:175px;>".$optCust2."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['komoditi']."</td><td><select id=kdBrgCr style=width:175px;>".$optBrg2."</select></td>
				<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td><td><input type=text class=myinputtext  id=noKontrakCr name=noKontrakCr onkeypress=\"return tanpa_kutip(event);\" style=\"width:175px;\" /></td>
			</tr>
			<tr>
				<td colspan4><button onclick=cariBast(0) class=mybutton>".$_SESSION['lang']['find']."</button>  </td>
			</tr>
		</table>
		<div id=boxhead>
		<table class=sortable cellspacing=1 border=0>
			<tr class=rowheader>
				<td>No</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>".$_SESSION['lang']['vendor']."</td>
				<td>".$_SESSION['lang']['NoKontrak'].' '.$_SESSION['lang']['eksternal']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td align=right>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['action']."</td>    
			</tr>";

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
		$str="select a.*,b.namabarang,c.namacustomer from ".$dbname.".pmn_traderht a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang 
				left join ".$dbname.".pmn_4customer c on c.kodecustomer=a.kodecustomer where true ".$whrd."
				order by a.kodeorg,a.nokontrakext
				limit ".$offset.",".$limit." ";
        //exit('Warning : '.$str);
		$no=$maxdisplay;
		$res=mysql_query($str);
		$oow=mysql_num_rows($res);
		if($oow==0){
			echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
		}else{
			while($bar=mysql_fetch_assoc($res)){
				$no+=1;
				echo"<tr class=rowcontent>
						<td>".$no."</td>
						<td>".$bar['kodeorg']."</td>
						<td>".$bar['kodecustomer']."</td>
						<td>".$bar['nokontrakext']."</td>
						<td>".$bar['tanggalext']."</td>
						<td>".$bar['namabarang']."</td>
						<td align=right>".number_format($bar['qtykontrak'],0)."</td>
						<td align=center>
							<img src=images/application/application_view_detail.png class=resicon  title='Detail' onclick=\"loadData('".$bar['nokontrakext']."','".$bar['kodeorg']."','".$bar['kodecustomer']."','".$bar['kodebarang']."');\">
						</td>
                    </tr>";	
			}
		}
		echo"<tr class=rowheader>
				<td colspan=8 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".$jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>
	</table>
	</div>
	</fieldset>";

echo "<fieldset style='float:left;'>
		<legend><font size=2.5><b>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['kontrak']." ".$_SESSION['lang']['eksternal']."</b></legend></font>
		<input type=hidden id=kodedetail value=''>
		<div id=container> 
			
		</div>
	</fieldset>";//<script>loadData()</script>

CLOSE_BOX();
echo close_body();					
?>