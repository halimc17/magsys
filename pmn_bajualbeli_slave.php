<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param=$_POST;
$optnmCust=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

switch($param['proses']){

	case'insert':
		if($param['kodeorg']==''){
            exit("error: Perusahaan tidak boleh kosong");
        }
		if($param['tanggalkontrak']==''){
            exit("error:Tanggal tidak boleh kosong");
        }
		if($param['koderekanan']==''){
            exit("error: Customer tidak boleh kosong");
        }
		$param['nilaikontrak']=str_replace(",","",$param['nilaikontrak']);
		$param['nilaippn']=str_replace(",","",$param['nilaippn']);
		$param['nilaipph']=str_replace(",","",$param['nilaipph']);
		if($param['nokontrak']!=''){
			$nokontrak=$param['nokontrak'];
			$sinser="update ".$dbname.".pmn_kontraklainht set kodeorg='".$param['kodeorg']."',tanggalkontrak='".tanggalsystem($param['tanggalkontrak'])."'
						,koderekanan='".$param['koderekanan']."',franco='".$param['franco']."',tanggalkirim='".tanggalsystem($param['tanggalkirim'])."'
						,sdtanggal='".tanggalsystem($param['sdtanggal'])."',kdtermin='".$param['kdtermin']."',rekening='".$param['rekening']."'
						,matauang='".$param['matauang']."',kurs='".$param['kurs']."',penandatangan='".$param['penandatangan']."'
						,kualitas10='".$param['kualitas10']."',kualitas11='".$param['kualitas11']."',kualitas12='".$param['kualitas12']."'
						,kualitas20='".$param['kualitas20']."',kualitas21='".$param['kualitas21']."',kualitas22='".$param['kualitas22']."'
						,kualitas30='".$param['kualitas30']."',kualitas31='".$param['kualitas31']."',kualitas32='".$param['kualitas32']."'
						,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
						where nokontrak='".$param['nokontrak']."'";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 11256\n ".  mysql_error($conn)."___".$sinser);
			}
		}else{
			$nokontrak=generateNoKontrak($param['kodeorg'],$param['tanggalkontrak'],$param['koderekanan']);
			$sinser="insert into ".$dbname.".pmn_kontraklainht
			(nokontrak,kodeorg,tanggalkontrak,koderekanan,franco,tanggalkirim,sdtanggal,kdtermin,rekening,matauang,kurs,kualitas10,kualitas11,kualitas12,kualitas20,kualitas21,kualitas22,kualitas30,kualitas31,kualitas32,penandatangan,lastuser,lastdate) values ('".$nokontrak."','".$param['kodeorg']."','".tanggalsystem($param['tanggalkontrak'])."','".$param['koderekanan']."','".$param['franco']."','".tanggalsystem($param['tanggalkirim'])."','".tanggalsystem($param['sdtanggal'])."','".$param['kdtermin']."','".$param['rekening']."','".$param['matauang']."','".$param['kurs']."','".$param['kualitas10']."','".$param['kualitas11']."','".$param['kualitas12']."','".$param['kualitas20']."','".$param['kualitas21']."','".$param['kualitas22']."','".$param['kualitas30']."','".$param['kualitas31']."','".$param['kualitas32']."','".$param['penandatangan']."','".$_SESSION['standard']['username']."',now())";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			}
		}
		echo $nokontrak;
		break;

	case'getKursInvoice':
		if($param['matauang']=='IDR'){
			echo '1';
		}else{
			$tanggalkontrak=tanggalsystem($param['tanggalkontrak']);
			$sKurs="select * from ".$dbname.".setup_matauangrate where kode='".$param['matauang']."' and daritanggal='".$tanggalkontrak."'";
			$qKurs=mysql_query($sKurs) or die(mysql_error($conn));
			if(mysql_num_rows($qKurs)<=0){
				echo 'Gagal. Tidak ada kurs pada tanggal '.$param['tanggalkontrak'];
			}else{
				$bKurs=mysql_fetch_assoc($qKurs);
				echo $bKurs['kurs'];
			}
		}
		break;

	case'getNilai':
		if($param['matauang']!='IDR'){
			$ppn=$param['kurs']*$param['nilaippn'];
			$nilKontrak=$param['kurs']*$param['nilaikontrak'];
			$pph=$param['kurs']*$param['nilaipph'];
		}else{
			$ppn=$param['nilaippn'];
			$nilKontrak=$param['nilaikontrak'];
			$pph=$param['nilaipph'];
		}
		echo $ppn.'###'.$nilKontrak.'###'.$pph;
		//exit("Error:ASD");
        break;

	case'loadData':
		$where = '';
        if(!empty($param['nokontrakCr'])) {
            $where.=" and a.nokontrak like '%".$param['nokontrakCr']."%'";
        }
        if(!empty($param['tanggalCr'])) {            
            $tgrl=explode("-",$param['tanggalCr']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where.=" and left(a.tanggalkontrak,10) = '".$ert."'";
        }
		if(!empty($param['kodebarangCr'])) {
            //$where.=" and a.kodebarang = '".$param['kodebarangCr']."'";
            $where.=" and a.nokontrak in (select distinct nokontrak from ".$dbname.".pmn_kontraklaindt where kodebarang = '".$param['kodebarangCr']."')";
        }
		if(!empty($param['kodecustomerCr'])) {
            $where.=" and a.koderekanan = '".$param['kodecustomerCr']."'";
        }
		
        $sdel="";
        $limit=20;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
        $sql="select count(a.nokontrak) jmlhrow from ".$dbname.".pmn_kontraklainht a 
				where True ".$where." order by a.tanggalkontrak desc";
        //exit('Warning: '.$sql);
        $query=mysql_query($sql) or die(mysql_error($conn));
		$jlhbrs=0;
		while($jsl=mysql_fetch_object($query)){
            $jlhbrs=$jsl->jmlhrow;
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		$str="select a.*,b.nokontrak as no_kontrak from ".$dbname.".pmn_kontraklainht a
				left join (select distinct nokontrak from ".$dbname.".keu_penagihanht where nokontrak<>'') b on b.nokontrak=a.nokontrak
				where True ".$where."  order by a.tanggalkontrak desc
				limit ".$offset.",".$limit." ";
        //exit('Warning: '.$str);
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
			$nilaigtinv=$rstr['nilaikontrak']+$rstr['nilaippn']-$rstr['nilaipph']+$rstr['ongkoskirim'];
            $nor+=1;//<td align=right>".number_format($rstr['nilaikontrak_'],0)."</td>
			$tab.="<tr class=rowcontent>
					<td align=center>".$nor."</td>
					<td id='kodeorg_".$nor."' align=center value='".$rstr['kodeorg']."'>".$rstr['kodeorg']."</td>
					<td id='nokontrak_".$nor."' align=left value='".$rstr['nokontrak']."'>".$rstr['nokontrak']."</td>
					<td id='tanggalkontrak_".$nor."' align=center value='".$rstr['tanggalkontrak']."'>".tanggalnormal(substr($rstr['tanggalkontrak'],0,10))."</td>
					<td id='koderekanan_".$nor."' value='".$rstr['koderekanan']."'>".$optnmCust[$rstr['koderekanan']]."</td>
					<td id='nilaikontrak_".$nor."' align=right>".number_format($nilaigtinv,2)."</td>
					<td id='tanggalkirim_".$nor."' align=center value='".$rstr['tanggalkirim']."'>".tanggalnormal(substr($rstr['tanggalkirim'],0,10))."</td>";
			if($rstr['nokontrak']!=$rstr['no_kontrak']) {
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['nokontrak']."' onclick=\"fillField('".$rstr['nokontrak']."');\" ></td>";
				$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['nokontrak']."' onclick=\"delData('".$rstr['nokontrak']."');\" ></td>";
				//$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['nokontrak']."' onclick=\"postingData('".$rstr['nokontrak']."');\" ></td>";
				//$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['nokontrak']."'></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['nokontrak']."' onclick=\"masterPDF('pmn_kontraklainht','".$rstr['nokontrak']."','','pmn_bajualbeli_print',event);\" ></td>";
			} else {
                $tab.="<td>&nbsp;</td><td>&nbsp;</td>";
				//$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['nokontrak']."' onclick=\"popUpPosting('No Invoice Afiliasi','".$rstr['nokontrak']."','<div id=formaAfiliasi></div>',event);\"  ></td>";
				//$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['nokontrak']."'></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['nokontrak']."' onclick=\"masterPDF('pmn_kontraklainht','".$rstr['nokontrak']."','','pmn_bajualbeli_print',event);\" ></td>";
            }
			$tab.="</tr>"; 
        }
		$footd="</tr>
            <tr><td colspan=10 align=center>
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
		echo $tab."####".$footd;
		break;

    case'getData':
		$sdata="select distinct * from ".$dbname.".pmn_kontraklainht 
				where nokontrak='".$param['nokontrak']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		echo $rdata['nokontrak']."###".$rdata['kodeorg']."###".tanggalnormal(substr($rdata['tanggalkontrak'],0,10))."###".$rdata['koderekanan']."###".$rdata['franco']."###".tanggalnormal(substr($rdata['tanggalkirim'],0,10))."###".tanggalnormal(substr($rdata['sdtanggal'],0,10))."###".$rdata['kdtermin']."###".$rdata['rekening']."###".$rdata['matauang']."###".$rdata['kurs']."###".$rdata['kualitas10']."###".$rdata['kualitas11']."###".$rdata['kualitas12']."###".$rdata['kualitas20']."###".$rdata['kualitas21']."###".$rdata['kualitas22']."###".$rdata['kualitas30']."###".$rdata['kualitas31']."###".$rdata['kualitas32']."###".$rdata['penandatangan'];
		break;

	case'getFormAfiliasi':
		$form="<div style='padding-top:20px;'><fieldset style='float: left;'>
               <legend>".$_SESSION['lang']['input']." No Kontrak Induk</legend>
               No Kontrak Induk&nbsp;<input type=text class=myinputtext id=noafiliasi />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=inputAfiliasi('".$param['nokontrak']."')>".$_SESSION['lang']['save']."</button></fieldset></div>";
        echo $form;
		break;

	case'inputNoAfiliasi':
		$sUpdate="update ".$dbname.".pmn_kontraklainht set nokontrak_ref='".$param['noafiliasi']."' where nokontrak='".$param['nokontrak']."'";
		if(!mysql_query($sUpdate)){
            exit("error: tidak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;

    case'delData':
        $sdel="delete from ".$dbname.".pmn_kontraklainht where nokontrak='".$param['nokontrak']."'";
        if(!mysql_query($sdel)){
            exit("error: tidak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;

	case'loadDetail':
		$where="";
		if($param['nokontrak']!=''){
			$where.="a.nokontrak='".$param['nokontrak']."'";
		}else{
			exit;
		}

		// Pilihan kodebarang
		$optBrg2="<option value=''></option>";
		$optBrg3="";
		$sBrg2="select a.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a
				where a.inactive='0' and (a.kodebarang like '4%' or a.kodebarang like '386%') order by a.namabarang";
		$qBrg2=mysql_query($sBrg2) or die(mysql_error($conn));
		while($rBrg2=mysql_fetch_assoc($qBrg2)){
			$optBrg2.="<option value=".$rBrg2['kodebarang'].">".$rBrg2['namabarang']."</option>";
			$optBrg3.="<option value=".$rBrg2['kodebarang'].">".$rBrg2['namabarang']."</option>";
		}

		//Data Detail
		$str="select a.*,b.tanggalkontrak,c.namabarang,c.satuan from ".$dbname.".pmn_kontraklaindt a 
				left join ".$dbname.".pmn_kontraklainht b on b.nokontrak=a.nokontrak
				left join ".$dbname.".log_5masterbarang c on c.kodebarang=a.kodebarang
				where ".$where."
				order by a.nokontrak,a.kodebarang";
		//exit('Warning: '.$sBrg2.'    '.$str);
		$res=mysql_query($str);
		$no=0;
		$subtotaltransaksi=0;
		while($bar=mysql_fetch_assoc($res)){
			$no+=1;
			$subtotaltransaksi+=$bar['jmlharga'];
			$optBrg3="<option value=".$bar['kodebarang'].">".$bar['namabarang']."</option>".$optBrg3;
			echo "
			<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td><select id=kodebarang".$no." value='".$bar['kodebarang']."' disabled onchange=getSatuan(".$no.") style=\"width:600px;\">".$optBrg3."</select></td>
				<td align=center><input type=text id=satuan".$no." value='".$bar['satuan']."' disabled class=myinputtext style=\"width:45px;\"></td>
				<td align=right><input type=text id=nilaiinventory".$no." value=".number_format($bar['jumlah'],2)." disabled class=myinputtextnumber onblur=getNilaiTransaksi(".$no.") style=\"width:105px;\"></td>
				<td align=center><input type=text id=hargasatuan".$no." value=".number_format($bar['jmlharga']/$bar['jumlah'],4)." disabled class=myinputtextnumber onblur=getNilaiTransaksi(".$no.") style=\"width:105px;\"></td>
				<td align=right><input type=text id=nilaitransaksi".$no." value=".number_format($bar['jmlharga'],2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
				<td align=center>
					<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editdetail('".$bar['nokontrak']."','".$no."');\">&nbsp&nbsp&nbsp
					<img src=images/save.png class=resicon title='Save' onclick=\"savedetail('".$bar['nokontrak']."','".$bar['kodebarang']."','".$no."');\">&nbsp&nbsp&nbsp
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldetail('".$bar['nokontrak']."','".$bar['kodebarang']."');\">
				</td>
			</tr>";
		}
		if($param['nokontrak']!=''){
			echo"<tr class=rowcontent>
					<td align=center></td>
					<td><select id=kodebarang onchange=getSatuan('') style=\"width:600px;\">".$optBrg2."</select></td>
					<td align=center><input type=text id=satuan class=myinputtext disabled style=\"width:45px;\"></td>
					<td align=right><input type=text id=nilaiinventory onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur=getNilaiTransaksi('') style=\"width:105px;\"></td>
					<td align=right><input type=text id=hargasatuan onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur=getNilaiTransaksi('') style=\"width:105px;\"></td>
					<td align=right><input type=text id=nilaitransaksi value=0 class=myinputtextnumber disabled style=\"width:130px;\"></td>
					<td align=center><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpandetail('".$param['nokontrak']."')></td>
			</tr>";
		}
		$nilaippn=0;
		$nilaipph=0;
		$ongkoskirim=0;
		$catatan='';
		$sht="select * from ".$dbname.".pmn_kontraklainht where nokontrak='".$param['nokontrak']."'";
		$qht= mysql_query($sht) or die (mysql_error($conn));
		while($dht=mysql_fetch_assoc($qht)){
			$stsppn=$dht['stsppn'];
			$stspph=$dht['stspph'];
			//$nilaippn=$dht['nilaippn'];
			//$nilaipph=$dht['nilaipph'];
			if($stsppn=='2'){//Excl PPN
				$nilaippn=($subtotaltransaksi*0.11);
			}else if($stsppn=='1'){//Incl PPN
				$nilaippn=($subtotaltransaksi*0.11);
			}else{//Non PPN
				$nilaippn=0;
			}

			if($stspph=='1160200'){//PPh Pasal 22
				$nilaipph=($subtotaltransaksi*0.0025);
			}else if($stspph=='1160400'){//PPh Pasal 23
				$nilaipph=($subtotaltransaksi*0.02);
			}else if($stspph=='1160500'){//PPh Pasal 4 Ayat 2
				$nilaipph=($subtotaltransaksi*0.05);
			}else{//Non PPh
				$nilaipph=0;
			}
			$ongkoskirim=$dht['ongkoskirim'];
			$catatan=$dht['catatanlain'];
			//$subtotaltransaksi=$dht['nilaiinvoice'];
		}
		$gtotaltransaksi=$subtotaltransaksi+$nilaippn-$nilaipph+$ongkoskirim;

		// Ambil Akun PPN
		$optPPn ="<option value='0' ".($stsppn==0 ? "selected" : "").">Non PPN</option>";
		$optPPn.="<option value='1' ".($stsppn==1 ? "selected" : "").">Incl PPN</option>";
		$optPPn.="<option value='2' ".($stsppn==2 ? "selected" : "").">Excl PPN</option>";

		// Ambil Akun PPh
		$sPajak="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in 
				(select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi like 'SPPH%' and jurnalid='SLE' and aktif=1)";
		$qPajak= mysql_query($sPajak) or die (mysql_error($conn));
		$optPph.="<option value=''>Non PPh</option>";
		while($dPajak=  mysql_fetch_assoc($qPajak)){
			if($stspph==$dPajak['noakun']){
				$optPph.="<option value='".$dPajak['noakun']."' selected>".$dPajak['namaakun']."</option>";
			}else{
				$optPph.="<option value='".$dPajak['noakun']."'>".$dPajak['namaakun']."</option>";
			}
		}
		echo "<table>
				<tr><td colspan=7></td></tr><tr><td colspan=7></td></tr><tr><td colspan=7></td></tr><tr><td colspan=7></td></tr>
				<tr><td colspan=7>Catatan :</td></tr>
				<tr class=rowcontent>
					<td colspan=4 rowspan=5><textarea id=catatan onkeypress='return tanpa_kutip(event);' rows=5 cols=107>".$catatan."</textarea></td>
					<td align=right>Sub Total :</td>
					<td align=right><input type=text id=nilaikontrak value=".number_format($subtotaltransaksi,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
					<td></td>
				</tr>
				<tr class=rowcontent>
					<td align=right>PPN :</td>
					<td align=right><input type=text id=nilaippn value=".number_format($nilaippn,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
					<td><select id=stsppn style=width:105px onchange=getPajak()>".$optPPn."</select></td>
				</tr>
				<tr class=rowcontent>
					<td align=right>PPh :</td>
					<td align=right><input type=text id=nilaipph value=".number_format($nilaipph,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
					<td><select id=stspph style=width:105px onchange=getPajak()>".$optPph."</select></td>
				</tr>
				<tr class=rowcontent>
					<td align=right>Biaya Kirim :</td>
					<td align=right><input type=text id=ongkoskirim value=".number_format($ongkoskirim,2)." onblur=getPajak() class=myinputtextnumber style=\"width:130px;\"></td>
					<td></td>
				</tr>
				<tr class=rowcontent>
					<td align=right>Grand Total :</td>
					<td align=right><input type=text id=totalkontrak value=".number_format($gtotaltransaksi,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
					<td></td>
				</tr>
			</table>";
		echo "<table><tr><td></td></tr>
				<tr><td colspan=4>
						<button class=mybutton onclick=saveFoot('".$param['nokontrak']."')>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=saveBaru('".$param['nokontrak']."')>".$_SESSION['lang']['new']."</button>
				</td></tr>";
		echo"</table>"; 
	break;

	case 'simpandetail':
		if($param['nokontrak']==''){
			exit('Warning: No.Kontrak tidak boleh kosong');
		}
		if($param['kodebarang']==''){
			exit('Warning: Kode Barang tidak boleh kosong');
		}
		if($param['nilaiinventory']=='' or $param['nilaiinventory']=='0' or $param['nilaiinventory']==0){
			exit('Warning: Jumlah tidak boleh kosong');
		}
		//Cari $notransaksidet
		$i="select * from ".$dbname.".pmn_kontraklaindt where nokontrak='".$param['nokontrak']."' and kodebarang='".$param['kodebarang']."'";
		//exit('Warning: '.$i);
		$n=mysql_query($i) or die (mysql_error($conn));
		$ada=mysql_num_rows($n);
		if($ada>0){
			exit('Warning: Barang sudah ada...!');
		}
		$nilaitransaksi=$param['nilaiinventory']*$param['hargasatuan'];
		$str="insert into ".$dbname.".pmn_kontraklaindt (nokontrak,kodebarang,jumlah,hargasatuan,jmlharga,lastuser,lastdate) values ('".$param['nokontrak']."','".$param['kodebarang']."','".$param['nilaiinventory']."','".$param['hargasatuan']."','".$nilaitransaksi."','".$_SESSION['standard']['username']."',now())";
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'deldetail':
		$str="delete from ".$dbname.".pmn_kontraklaindt where nokontrak='".$param['nokontrak']."' and kodebarang='".$param['kodebarang']."'";
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'savedetail':
		if($param['nokontrak']==''){
			exit('Warning: No.Kontrak tidak boleh kosong');
		}
		if($param['kodebarang']==''){
			exit('Warning: Kode Barang tidak boleh kosong');
		}
		if($param['nilaiinventory']=='' or $param['nilaiinventory']=='0' or $param['nilaiinventory']==0){
			exit('Warning: Jumlah tidak boleh kosong');
		}
		$nilaitransaksi=$param['nilaiinventory']*$param['hargasatuan'];
		$str="update ".$dbname.".pmn_kontraklaindt set kodebarang='".$param['kodebarang']."',jumlah='".$param['nilaiinventory']."'
			,hargasatuan='".$param['hargasatuan']."',jmlharga='".$nilaitransaksi."',lastuser='".$_SESSION['standard']['username']."',lastdate=now() 
			where nokontrak='".$param['nokontrak']."' and kodebarang='".$param['oldkodebarang']."'";
		//exit('Warning : '.$str);
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case'getSatuan':
		if($param['kodebarang']!=''){
			$sSatuan="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$param['kodebarang']."'";
			$qSatuan=mysql_query($sSatuan) or die(mysql_error($conn));
			$Satuan='';
			while($rSatuan=mysql_fetch_assoc($qSatuan)){
				$Satuan=$rSatuan['satuan'];
			}
			echo $Satuan;
		}
    break;

	case 'saveFoot':
		if($param['nokontrak']==''){
			exit('Warning: No.Kontrak tidak boleh kosong');
		}
		$str="update ".$dbname.".pmn_kontraklainht set 
				nilaikontrak='".$param['nilaikontrak']."'
				,stsppn='".$param['stsppn']."',nilaippn='".$param['nilaippn']."'
				,stspph='".$param['stspph']."',nilaipph='".$param['nilaipph']."'
				,ongkoskirim='".$param['ongkoskirim']."',catatanlain='".$param['catatan']."'
				where nokontrak='".$param['nokontrak']."'";
		//exit('Warning : '.$str);
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'genNo':
		echo 'Ok';
	break;

}

function generateNoKontrak($invPT,$tgl,$invCust){
	global $dbname;
	global $conn;
	#no invoice
	$tgldt=explode("-",$tgl);
	$bulan = $tgldt[1];
	$thn=date('Y');
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	$ql="select `nokontrak` from ".$dbname.".`pmn_kontraklainht` where kodeorg='".$invPT."' and substr(nokontrak,5,3)='".$invPT."' and left(tanggalkontrak,4)= '".$tgldt[2]."' and right(nokontrak,4)='".$tgldt[2]."' and substr(tanggalkontrak,6,2)='".$bulan."' order by nokontrak desc limit 1";
    $qr=mysql_query($ql) or die('error: '.mysql_error());
	$data = mysql_fetch_object($qr);
	$countNoKontrak = substr($data->nokontrak,0,3);
	$noKontrak = addZero($countNoKontrak+1,3).'/'.$invPT.'/BJB_'.$invCust."/".$resultRomawi."/".$tgldt[2];
	return $noKontrak;
}
