<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['penagihan']).' PER '.strtoupper($_SESSION['lang']['komoditi'])."</b>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/keu_invoice_komoditi.js" /></script>

<?php
	$arr="##proses##noinvoice##kodeorganisasi##bayarke##tanggal##jatuhtempo";
	$arr.="##kodecustomer##nokontrak##matauang##kurs##ttd##jenis##debet##kredit##keterangan";

	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and length(kodeorganisasi)=4 and detail='1'";
	//}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	//	$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KANWIL'";
	}else{
		$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	}
	//$optUnit="<option value=''></option>";
	$qUnit= mysql_query($sUnit) or die (mysql_error($conn));
	while($dUnit=mysql_fetch_assoc($qUnit)){
		if($dUnit['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
			$optUnit.="<option value='".$dUnit['kodeorganisasi']."' selected>".$dUnit['namaorganisasi']."</option>";
		}else{
			$optUnit.="<option value='".$dUnit['kodeorganisasi']."'>".$dUnit['namaorganisasi']."</option>";
		}
	}
	
	$optKomoditi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sKomoditi="select distinct(a.kodebarang),b.namabarang from ".$dbname.".keu_penagihandt a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang order by a.kodebarang";
	//$sKomoditi="select a.kodebarang,a.namabarang from ".$dbname.".log_5masterbarang a where a.inactive='0' and a.kodebarang like '4%' order by a.namabarang";
	$qKomoditi= mysql_query($sKomoditi) or die (mysql_error($conn));
	while($dKomoditi=  mysql_fetch_assoc($qKomoditi)){
	    $optKomoditi.="<option value='".$dKomoditi['kodebarang']."'>".$dKomoditi['namabarang']."</option>";
	}

	$optCustc="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sCust="select distinct(a.kodecustomer),b.namacustomer from ".$dbname.".keu_penagihanht a left join ".$dbname.".pmn_4customer b on a.kodecustomer=b.kodecustomer where a.tipeinvoice<>0 order by b.namacustomer";
	$qCust= mysql_query($sCust) or die (mysql_error($conn));
	while($dCust=  mysql_fetch_assoc($qCust)){
	    $optCustc.="<option value='".$dCust['kodecustomer']."'>".$dCust['namacustomer']."</option>";
	}

	$optPPn ="<option value='0'>Non PPn</option>";
	$optPPn.="<option value='1'>Incl PPn</option>";
	$optPPn.="<option value='2'>Excl PPn</option>";

	// Ambil Akun Pph
	$sPajak="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in 
			(select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi like 'SPPH%' and jurnalid='SLE' and aktif=1)";
	$qPajak= mysql_query($sPajak) or die (mysql_error($conn));
    $optPph.="<option value=''>Non Pph</option>";
	while($dPajak=  mysql_fetch_assoc($qPajak)){
	    $optPph.="<option value='".$dPajak['noakun']."'>".$dPajak['noakun'].' - '.$dPajak['namaakun']."</option>";
	}

	#nokontrak
	$optNoKontrak="<option value=''></option>";
	$skontrak="select distinct a.nokontrak from ".$dbname.".pmn_kontraklainht a 
				left join ".$dbname.".keu_penagihanht b on b.nokontrak=a.nokontrak
				where b.noinvoice='' or isnull(b.noinvoice)
				order by a.nokontrak asc";
	$qkontrak=mysql_query($skontrak) or die(mysql_error($conn));
	while($rkontrak=  mysql_fetch_assoc($qkontrak)){
		$optNoKontrak.="<option value='".$rkontrak['nokontrak']."'>".$rkontrak['nokontrak']."</option>";
	}

	echo"<table>
			<tr valign=moiddle>
				<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
					<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
				<td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
					<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
				<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
					echo $_SESSION['lang']['noinvoice'].":<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
					echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /><BR>";
		            echo $_SESSION['lang']['komoditi'].":<select style=\"width: 155px;\" name=ptKomoditi id=ptKomoditi >".$optKomoditi."</select>";    
		            echo $_SESSION['lang']['nmcust'].":<select style=\"width: 290px;\" name=ptCust id=ptCust >".$optCustc."</select>";
					echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
				echo"</fieldset></td>
			</tr>
		</table> "; 

	CLOSE_BOX();

	OPEN_BOX();
	echo"<div id=listData>";
	echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
	echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
	echo"	<thead>";
	echo"		<tr align=center>
					<td width='3%'>No</td>
					<td width='12%'>".$_SESSION['lang']['noinvoice']."</td>";
	echo"			<td width='4%'>".$_SESSION['lang']['unit']."</td>";
	echo"			<td width='6%'>".$_SESSION['lang']['tanggal']."</td>";
	echo"			<td width='25%'>".$_SESSION['lang']['nmcust']."</td>";
	echo"			<td width='6%'>".$_SESSION['lang']['jatuhtempo']."</td>";
	echo"			<td width='10%'>".$_SESSION['lang']['nilaiinvoice']."</td>";
	echo"			<td width='25%'>".$_SESSION['lang']['keterangan']."</td>";
	echo"			<td width='9%' colspan=4>".$_SESSION['lang']['action']."</td>";
	echo"	</tr></thead><tbody id=continerlist>";
	echo"<script>loadData(0)</script>";
	echo"</tbody>";
	$skeupenagih="select count(*) as rowd from ".$dbname.".keu_penagihanht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	$qkeupenagih=mysql_query($skeupenagih) or die(mysql_error($conn));
	$rkeupenagih=mysql_num_rows($qkeupenagih);
	$totrows=ceil($rkeupenagih/10);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$isiRow.="<option value='".$er."'>".$er."</option>";
	}
	echo"<tfoot id=footData>";
	echo"</tfoot></table></fieldset>";
	echo"</div><input type=hidden id=proses value=insert />";
	#byr ke
	$optCust=$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sakun="select distinct noakun,namaakun from ".$dbname.".keu_5akun 
			where  noakun like '11102%' and char_length(noakun)>6 order by namaakun asc";
	$qakun=mysql_query($sakun) or die(mysql_error($conn));
	while($rakun=  mysql_fetch_assoc($qakun)){
		$optAkun.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
	}
	#kodepelanggan
	$sakun="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer  order by namacustomer asc";
	$qakun=mysql_query($sakun) or die(mysql_error($conn));
	while($rakun=  mysql_fetch_assoc($qakun)){
		$optCust.="<option value='".$rakun['kodecustomer']."'>".$rakun['kodecustomer']."-".$rakun['namacustomer']."</option>";
	}

	$iMt=" select * from ".$dbname.".setup_matauang order by matauang asc ";
	$nMt=mysql_query($iMt) or die (mysql_error($conn));
	while($dMt=  mysql_fetch_assoc($nMt)){
	    $optMtuang.="<option value='".$dMt['kode']."'>".$dMt['matauang']."</option>";
	}

	#akuun debet
	$sakundbt="select distinct noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1130100','1130205','1140600')  and char_length(noakun)=7
				order by namaakun asc";
	$qakun=mysql_query($sakundbt) or die(mysql_error($conn));
	$optDebet='';
	while($rakun=  mysql_fetch_assoc($qakun)){
		$optDebet.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
	}
	$sakundbt="select distinct noakun,namaakun from ".$dbname.".keu_5akun where (noakun like '511%' or noakun='8110102' or noakun='9110107') and detail=1
			    order by namaakun asc";
	$qakun=mysql_query($sakundbt) or die(mysql_error($conn));
	$optKredit='';
	while($rakun=  mysql_fetch_assoc($qakun)){
		$optKredit.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
	}

	//$optTtdjual="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optTtdjual="";
	$iTtd="select * from ".$dbname.".pmn_5ttd";
	$nTtd=mysql_query($iTtd) or die(mysql_error($conn));
	while($dTtd=mysql_fetch_assoc($nTtd)){
		$optTtdjual.="<option value='".$dTtd['nama']."'>".$dTtd['nama']."</option>";
	}

	$optJenis="<option value='Jumlah Harga Jual'>Jumlah Harga Jual</option>";
	$optJenis.="<option value='Penggantian'>Penggantian</option>";
	$optJenis.="<option value='Uang Muka'>Uang Muka</option>";
	$optJenis.="<option value='Termin'>Termin</option>";

	echo"<div id=formInput style=display:none;>";
	echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
		<table style=width:100%;>";
	echo"<tr><td>".$_SESSION['lang']['noinvoice']."</td><td><input type=text id=noinvoice class=myinputtext style=width:185px;  readonly></td>";
	echo"<td>".$_SESSION['lang']['NoKontrak']."</td><td><select id=nokontrak onchange=getKontrakData() style=width:190px;>".$optNoKontrak."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['tanggal']."</td><td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:185px;  maxlength=10 /></td>";
	echo"<td>".$_SESSION['lang']['jatuhtempo']."</td><td><input type=text class=myinputtext id=jatuhtempo onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:185px;  maxlength=10 /></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['unitkerja']."</td><td><select id=kodeorganisasi style=width:190px>".$optUnit."</select></td>";
	echo"<td>".$_SESSION['lang']['nmcust']."</td><td><select id=kodecustomer style=width:190px >".$optCust."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['bayarke']."</td><td><select id=bayarke  style=width:190px; >".$optAkun."</select></td>";
	echo"<td>Penanda Tangan</td><td><select id=ttd  style=width:190px; >".$optTtdjual."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['matauang']."</td><td><select id=matauang onchange=getKursInvoice() style=width:190px; >".$optMtuang."</select></td><td>".$_SESSION['lang']['kurs']."</td><td><input type=text id=kurs class=myinputtextnumber value=1 style=width:185px; onkeypress='return angka_doang(event)' /></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['debet']."</td><td><select id=debet style=width:190px;>".$optDebet."</select></td>";
	echo"<td>".$_SESSION['lang']['kredit']."</td><td><select id=kredit style=width:190px;>".$optKredit."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['keterangan']."</td><td colspan=3><input type=text id=keterangan class=myinputtext style=width:475px;></td>
		<td hidden><select id=jenis  style=width:155px; >".$optJenis."</select></td></tr>";
	echo "<table><tr><td></td></tr>";
	echo"<tr><td colspan=4><button class=mybutton onclick=saveData('keu_invoice_komoditi_slave','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
         <button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button></td></tr>";
	echo"</table>"; 
	echo"</fieldset></div>";
CLOSE_BOX();

OPEN_BOX();
echo"<div id=formDetail style=display:none;>";
echo "<fieldset><legend>".$_SESSION['lang']['detail']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td width='3%' align=center>No</td>
					<td width='52%' align=center>".$_SESSION['lang']['namabarang']."</td>
					<td width='5%' align=center>".$_SESSION['lang']['satuan']."</td>
					<td width='10%' align=center>".$_SESSION['lang']['jumlah']."</td>
					<td width='10%' align=center>".$_SESSION['lang']['hargasatuan']."</td>
					<td width='12%' align=center>".$_SESSION['lang']['totalharga']."</td>
					<td width='8%' align=center>Action</td>	   
				</tr>
			</thead>
			<tbody id=container>";
				echo"<script></script>";
echo"	
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>
	</div>";
CLOSE_BOX();
echo close_body();
?>
