<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	echo open_body();
	include('master_mainMenu.php');
	OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['pemakaianBarang']).' BAKU KE BARANG JADI '."</b>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/log_brgjadi.js" /></script>

<?php
	$arr="##proses##notransaksi##kodegudang##tanggal##keterangan";
	$darr1="##proses##notransaksi##kodegudang##tanggal##kodebarang1##jumlah1##tipetransaksi1";
	$darr2="##proses##notransaksi##kodegudang##tanggal##kodebarang2##jumlah2##tipetransaksi2";
	$optGudang="";
	$sGudang="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe like 'GUDANG%'";
	$qGudang= mysql_query($sGudang) or die (mysql_error($conn));
	while($dGudang=mysql_fetch_assoc($qGudang)){
		$optGudang.="<option value='".$dGudang['kodeorganisasi']."'>[".$dGudang['kodeorganisasi'].'] - '.$dGudang['namaorganisasi']."</option>";
	}
	
	$speriodeakuntansi="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=0";
	$qperiodeakuntansi= mysql_query($speriodeakuntansi) or die (mysql_error($conn));
	$periodeakuntansi='';
	while($dperiodeakuntansi=mysql_fetch_assoc($qperiodeakuntansi)){
		$periodeakuntansi=$dperiodeakuntansi['periode'];
	}
	//exit('Warning: '.$speriodeakuntansi);
	$optBarang1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sBarang="select a.kodebarang,b.namabarang,a.saldoakhirqty,b.satuan from ".$dbname.".log_5saldobulanan a 
			LEFT JOIN ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
			where a.kodegudang like '".$_SESSION['empl']['lokasitugas']."%' and a.periode='".$periodeakuntansi."' and a.saldoakhirqty>0
			and (left(a.kodebarang,3) in ('311','312','373','384','385','386','387') or left(a.kodebarang,1)='4') 
			and b.satuan not in ('Lusin','Pack','Rit','Set','Transaksi','Unit') 
			order by b.namabarang";
	//exit('Warning: '.$sBarang);
	$qBarang= mysql_query($sBarang) or die (mysql_error($conn));
	while($dBarang=mysql_fetch_assoc($qBarang)){
	    $optBarang1.="<option value='".$dBarang['kodebarang']."'>".$dBarang['namabarang'].' ('.$dBarang['satuan'].")</option>";
	}
	
	$optBarang2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sBarang="select distinct(a.kodebarang),a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a where (left(a.kodebarang,3) in ('311','312','373','384','385','386','387') or left(a.kodebarang,1)='4') and satuan not in ('Lusin','Pack','Rit','Set','Transaksi','Unit') order by a.namabarang";
	$qBarang= mysql_query($sBarang) or die (mysql_error($conn));
	while($dBarang=mysql_fetch_assoc($qBarang)){
	    $optBarang2.="<option value='".$dBarang['kodebarang']."'>".$dBarang['namabarang'].' ('.$dBarang['satuan'].")</option>";
	}

	echo"<table>
			<tr valign=moiddle>
				<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
					<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
				<td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
					<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
				<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>";
					echo $_SESSION['lang']['notransaksi']." : <input type=text id=carinotransaksi size=25 maxlength=30 class=myinputtext>";
					echo ' '.$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
		            //echo ' '.$_SESSION['lang']['namabarang']." : <select style=\"width: 155px;\" name=caribarang id=caribarang>".$optBarang2."</select>";    
					echo "<input type=hidden id=caribarang value=''>";
					echo " <button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
				echo"</fieldset></td>
			</tr>
		</table> "; 

	CLOSE_BOX();

	OPEN_BOX();
	echo"<div id=listData>";
	echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
	echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
	echo"	<thead>";
	echo"		<tr align=center>";
	echo"			<td width=15%>".$_SESSION['lang']['notransaksi']."</td>";
	echo"			<td width=10%>".$_SESSION['lang']['unit']."</td>";
	echo"			<td width=10%>".$_SESSION['lang']['gudang']."</td>";
	echo"			<td width=10%>".$_SESSION['lang']['tanggal']."</td>";
	echo"			<td width=45%>".$_SESSION['lang']['keterangan']."</td>";
	echo"			<td colspan=4 width=10%>".$_SESSION['lang']['action']."</td>";
	echo"		</tr>";
	echo"	</thead>";
	echo"	<tbody id=continerlist>";
	echo"		<script>loadData(0)</script>";
	echo"	</tbody>";
	echo"	<tfoot id=footData></tfoot>";
	echo"</table></fieldset>";
	echo"</div><input type=hidden id=proses value=insert />";

	echo"<div id=formInput style=display:none;>
			<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
				<table style=width:100%;>
					<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td>
						<td><input type=text id=notransaksi class=myinputtext style=width:150px; readonly></td>
						<td>".$_SESSION['lang']['gudang']."</td>
						<td><select id=kodegudang style=width:330px;>".$optGudang."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px;  maxlength=10 /></td>
						<td>".$_SESSION['lang']['keterangan']."</td>
						<td><input type=text id=keterangan class=myinputtext style=width:330px;></td>
					</tr>
					<tr>
						<td colspan=2>
							<button class=mybutton onclick=saveData('log_brgjadi_slave','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
							<button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>";

	echo"<div id=formDetail style=display:none;>
			<fieldset style=float:left;><legend>".$_SESSION['lang']['Detail']."</legend>
				<table style=width:100%;>
					<tr>
						<td valign=top>
							<fieldset><legend>Bahan Baku</legend>
								<table>
									<tr>
										<td>".$_SESSION['lang']['namabarang']."</td>
										<td><select id=kodebarang1 style=width:330px;>".$optBarang1."</select></td>
									</tr>
									<tr>
										<td>".$_SESSION['lang']['jumlah']."</td>
										<td><input type=text id=jumlah1 class=myinputtext value=0 style=width:130px;></td>
										<td><input type=hidden id=tipetransaksi1 value=1></td>
									</tr>
									<tr>
										<td colspan=2>
											<button class=mybutton onclick=saveDetail1('log_brgjadi_slave','".$darr1."')>".$_SESSION['lang']['save']."</button>
											<button class=mybutton onclick=cancelDetail(1)>".$_SESSION['lang']['cancel']."</button>
										</td>
									</tr>
								</table>
								<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
									<thead><tr>
										<td align=center style=width:60px;>".$_SESSION['lang']['kode']."</td>
										<td align=center style=width:270px;>".$_SESSION['lang']['namabarang']."</td>
										<td align=center style=width:45px;>".$_SESSION['lang']['jumlah']."</td>
										<td align=center style=width:45px;>".$_SESSION['lang']['satuan']."</td>
										<td colspan=2 align=center style=width:50px;>".$_SESSION['lang']['action']."</td>
									</tr></thead>
									<tbody id=listDetail1>
										<script>loadDetail1()</script>
									</tbody>
								</table>
							</fieldset>
						</td>
						<td valign=top>
							<fieldset><legend>Bahan Jadi</legend>
								<table>
									<tr>
										<td>".$_SESSION['lang']['namabarang']."</td>
										<td><select id=kodebarang2 style=width:330px;>".$optBarang2."</select></td>
									</tr>
									<tr>
										<td>".$_SESSION['lang']['jumlah']."</td>
										<td><input type=text id=jumlah2 class=myinputtext value=0 style=width:130px;></td>
										<td><input type=hidden id=tipetransaksi2 value=2></td>
									</tr>
									<tr>
										<td colspan=2>
											<button class=mybutton onclick=saveDetail2('log_brgjadi_slave','".$darr2."')>".$_SESSION['lang']['save']."</button>
											<button class=mybutton onclick=cancelDetail(2)>".$_SESSION['lang']['cancel']."</button>
										</td>
									</tr>
								</table>
								<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
									<thead><tr>
										<td align=center style=width:60px;>".$_SESSION['lang']['kode']."</td>
										<td align=center style=width:270px;>".$_SESSION['lang']['namabarang']."</td>
										<td align=center style=width:45px;>".$_SESSION['lang']['jumlah']."</td>
										<td align=center style=width:45px;>".$_SESSION['lang']['satuan']."</td>
										<td colspan=2 align=center style=width:50px;>".$_SESSION['lang']['action']."</td>
									</tr></thead>
									<tbody id=listDetail2>
										<script>loadDetail2()</script>
									</tbody>
								</table>
							</fieldset>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>";

CLOSE_BOX();
echo close_body();
?>
