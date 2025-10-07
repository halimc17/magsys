<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	//OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['ba']).' PER '.strtoupper($_SESSION['lang']['jualbeli'])."</b>"); //1 O
	OPEN_BOX("","<b>".strtoupper('Berita Acara').' '.strtoupper('Jual Beli')."</b>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/pmn_bajualbeli.js" /></script>

<?php
	$arr="##proses##nokontrak##tanggalkontrak##kodeorg##koderekanan##matauang##kurs";
	$arr.="##franco##tanggalkirim##sdtanggal##kualitas10##kualitas11##kualitas12##kualitas20##kualitas21##kualitas22##kualitas30##kualitas31##kualitas32";
	$arr.="##kdtermin##rekening##penandatangan";

	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$sPT="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
	}else{
		$sPT="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
	}
	//$optPT="<option value=''></option>";
	$optPT="";
	$qPT= mysql_query($sPT) or die (mysql_error($conn));
	while($dPT=mysql_fetch_assoc($qPT)){
		if($dPT['kodeorganisasi']==$_SESSION['empl']['kodeorganisasi']){
			$optPT.="<option value='".$dPT['kodeorganisasi']."' selected>".$dPT['namaorganisasi']."</option>";
		}else{
			$optPT.="<option value='".$dPT['kodeorganisasi']."'>".$dPT['namaorganisasi']."</option>";
		}
	}
	
	$optKomoditi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sKomoditi="select distinct(a.kodebarang),b.namabarang from ".$dbname.".pmn_kontraklaindt a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang order by a.kodebarang";
	//$sKomoditi="select a.kodebarang,a.namabarang from ".$dbname.".log_5masterbarang a where a.inactive='0' and a.kodebarang like '4%' order by a.namabarang";
	$qKomoditi= mysql_query($sKomoditi) or die (mysql_error($conn));
	while($dKomoditi=  mysql_fetch_assoc($qKomoditi)){
	    $optKomoditi.="<option value='".$dKomoditi['kodebarang']."'>".$dKomoditi['namabarang']."</option>";
	}

	$optCustc="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sCust="select distinct(a.koderekanan),b.namacustomer from ".$dbname.".pmn_kontraklainht a left join ".$dbname.".pmn_4customer b on a.koderekanan=b.kodecustomer order by b.namacustomer";
	$qCust= mysql_query($sCust) or die (mysql_error($conn));
	while($dCust=mysql_fetch_assoc($qCust)){
	    $optCustc.="<option value='".$dCust['kodecustomer']."'>".$dCust['namacustomer']."</option>";
	}

	echo"<table>
			<tr valign=moiddle>
				<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
					<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
				<td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
					<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
				<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
					echo $_SESSION['lang']['NoKontrak'].":<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
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
					<td width='5%'>".$_SESSION['lang']['kodeorg']."</td>
					<td width='20%'>".$_SESSION['lang']['NoKontrak']."</td>";
	echo"			<td width='7%'>".$_SESSION['lang']['tanggal']."</td>";
	echo"			<td width='38%'>".$_SESSION['lang']['nmcust']."</td>";
	echo"			<td width='12%'>".$_SESSION['lang']['nilkontrak']."</td>";
	echo"			<td width='7%'>".$_SESSION['lang']['tanggalkirim']."</td>";
	echo"			<td width='8%' colspan=4>".$_SESSION['lang']['action']."</td>";
	echo"	</tr></thead><tbody id=continerlist>";
	echo"<script>loadData(0)</script>";
	echo"</tbody>";

	echo"<tfoot id=footData>";
	echo"</tfoot></table></fieldset>";
	echo"</div><input type=hidden id=proses value=insert />";

	// Ambil PPN
	$optPPn ="<option value='0'>Non PPn</option>";
	$optPPn.="<option value='1'>Incl PPn</option>";
	$optPPn.="<option value='2'>Excl PPn</option>";
	// Ambil Akun Pph
	$sPajak="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in 
			(select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi like 'SPPH%' and jurnalid='SLE' and aktif=1)";
	$qPajak= mysql_query($sPajak) or die (mysql_error($conn));
    $optPph.="<option value=''>Non Pph</option>";
	while($dPajak=mysql_fetch_assoc($qPajak)){
	    $optPph.="<option value='".$dPajak['noakun']."'>".$dPajak['noakun'].' - '.$dPajak['namaakun']."</option>";
	}
	#byr ke
	$optCust=$optAkun=$optFranco=$optTtdjual="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sakun="select distinct pemilik,noakun,namabank,rekening from ".$dbname.".keu_5akunbank order by pemilik,noakun,namabank,rekening asc";
	$qakun=mysql_query($sakun) or die(mysql_error($conn));
	while($rakun=mysql_fetch_assoc($qakun)){
		$optAkun.="<option value='".$rakun['noakun']."'>".$rakun['pemilik']." - ".$rakun['noakun']." - ".$rakun['namabank']."</option>";
	}
	#kodepelanggan
	$sakun="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer order by namacustomer asc";
	$qakun=mysql_query($sakun) or die(mysql_error($conn));
	while($rakun=mysql_fetch_assoc($qakun)){
		$optCust.="<option value='".$rakun['kodecustomer']."'>".$rakun['kodecustomer']."-".$rakun['namacustomer']."</option>";
	}
	#franco
	$sakun="select * from ".$dbname.".pmn_5franco order by franco_name asc";
	$qakun=mysql_query($sakun) or die(mysql_error($conn));
	while($rakun=mysql_fetch_assoc($qakun)){
		$optFranco.="<option value='".$rakun['id_franco']."'>".strtoupper($rakun['penjualan'])." ".$rakun['franco_name']." ".$rakun['alamat']."</option>";
	}
	#termin
	$optTermin ="<option value='100'>100%</option>";
	#Mata Uang
	$iMt=" select * from ".$dbname.".setup_matauang order by matauang asc ";
	$nMt=mysql_query($iMt) or die (mysql_error($conn));
	while($dMt=mysql_fetch_assoc($nMt)){
		if($dMt['kode']=='IDR'){
			$optMtuang.="<option value='".$dMt['kode']."' selected>".$dMt['matauang']."</option>";
		}else{
			$optMtuang.="<option value='".$dMt['kode']."'>".$dMt['matauang']."</option>";
		}
	}
	#PenandaTangan
	//$optTtdjual="";
	$iTtd="select * from ".$dbname.".pmn_5ttd";
	$nTtd=mysql_query($iTtd) or die(mysql_error($conn));
	while($dTtd=mysql_fetch_assoc($nTtd)){
		$optTtdjual.="<option value='".$dTtd['nama']."'>".$dTtd['nama']."</option>";
	}

	echo"<div id=formInput style=display:none;>";
	echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
		<table style=width:100%;>";
	echo"<tr><td>".$_SESSION['lang']['NoKontrak']."</td><td><input type=text id=nokontrak class=myinputtext style=width:185px;  readonly></td>";
	echo"<td>".$_SESSION['lang']['perusahaan']."</td><td><select id=kodeorg style=width:190px>".$optPT."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['tanggal']."</td><td><input type=text class=myinputtext id=tanggalkontrak onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:185px;  maxlength=10 /></td>";
	echo"<td>".$_SESSION['lang']['nmcust']."</td><td><select id=koderekanan style=width:190px >".$optCust."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['tempatpenyerahan']."</td><td><select id=franco style=width:190px>".$optFranco."</select></td>";
	echo"<td>".$_SESSION['lang']['waktupenyerahan']."</td><td><input type=text class=myinputtext id=tanggalkirim onmousemove=setCalendar(this.id) onkeypress=return false; style=width:185px; maxlength=10 /></td></tr>";
	echo"<tr><td></td><td></td>";
	echo"<td>s/d</td><td><input type=text class=myinputtext id=sdtanggal onmousemove=setCalendar(this.id) onkeypress=return false; style=width:185px; maxlength=10 /></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['carapembayaran']."</td><td><select id=kdtermin style=width:190px>".$optTermin."</select></td>";
	echo"<td>".$_SESSION['lang']['bayarke']."</td><td><select id=rekening style=width:190px; >".$optAkun."</select></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['matauang']."</td><td><select id=matauang onchange=getKursInvoice() style=width:190px; >".$optMtuang."</select></td><td>".$_SESSION['lang']['kurs']."</td><td><input type=text id=kurs class=myinputtextnumber value=1 style=width:185px; onkeypress='return angka_doang(event)' /></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['kualitas']."</td>
				<td><input type=text id=kualitas10 class=myinputtext style=width:185px;></td>
				<td><input type=text id=kualitas11 class=myinputtextnumber style=width:105px;></td>
				<td><input type=text id=kualitas12 class=myinputtextnumber style=width:185px;></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['kualitas']."</td>
				<td><input type=text id=kualitas20 class=myinputtext style=width:185px;></td>
				<td><input type=text id=kualitas21 class=myinputtextnumber style=width:105px;></td>
				<td><input type=text id=kualitas22 class=myinputtextnumber style=width:185px;></td></tr>";
	echo"<tr><td>".$_SESSION['lang']['kualitas']."</td>
				<td><input type=text id=kualitas30 class=myinputtext style=width:185px;></td>
				<td><input type=text id=kualitas31 class=myinputtextnumber style=width:105px;></td>
				<td><input type=text id=kualitas32 class=myinputtextnumber style=width:185px;></td></tr>";
	echo"<tr><td>Penanda Tangan</td><td><select id=penandatangan style=width:190px; >".$optTtdjual."</select></td></tr>";
	echo "<table><tr><td></td></tr>";
	echo"<tr><td colspan=4><button class=mybutton onclick=saveData('pmn_bajualbeli_slave','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
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
