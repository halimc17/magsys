<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param=$_POST;
$optnmCust=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

switch($param['proses']){
	case'insert':
		if($param['kodeorganisasi']==''){
            exit("error: Komoditi tidak boleh kosong");
        }
		if($param['tanggal']==''){
            exit("error:Tanggal tidak boleh kosong");
        }
        if($param['jatuhtempo']==''){
            $param['jatuhtempo']='0000-00-00';
        }
		if($param['kodecustomer']==''){
            exit("error: Customer tidak boleh kosong");
        }
        if($param['nilaiinvoice']==''){
            //exit("error:Nilai invoice tidak boleh kosong");
        }
        if($param['nilaippn']==''){
            $param['nilaippn']=0;
        }
        if($param['nilaipph']==''){
            $param['nilaipph']=0;
        }
		if($param['debet']=='' or $param['debet']==false){
            exit("error:Debet tidak boleh kosong");
        }
		if($param['kredit']=='' or $param['kredit']==false){
            exit("error:Kredit tidak boleh kosong");
        }
        //exit('Warning: '.$pn.'  '.$param['nilaiinvoice']);
		$param['nilaiinvoice']=str_replace(",","",$param['nilaiinvoice']);
		$param['nilaippn']=str_replace(",","",$param['nilaippn']);
		$param['nilaipph']=str_replace(",","",$param['nilaipph']);
		$optKdpt=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$param['kodeorganisasi']."'");
        $param['kuantitas']=str_replace(",","",$param['kuantitas']);
		$param['kuantitas']==''?$param['kuantitas']=0:$param['kuantitas']=$param['kuantitas'];
		if($param['noinvoice']!=''){
			$sinser="update ".$dbname.".keu_penagihanht set kodeorg='".$param['kodeorganisasi']."',kodept='".$optKdpt[$param['kodeorganisasi']]."',bayarke='".$param['bayarke']."'
						,tanggal='".tanggalsystem($param['tanggal'])."',jatuhtempo='".tanggalsystem($param['jatuhtempo'])."'
						,kodecustomer='".$param['kodecustomer']."'
						,matauang='".$param['matauang']."',kurs='".$param['kurs']."'
						,ttd='".$param['ttd']."',jenis='".$param['jenis']."'
						,debet='".$param['debet']."',kredit='".$param['kredit']."'
						,keterangan='".$param['keterangan']."' where noinvoice='".$param['noinvoice']."'";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 11256\n ".  mysql_error($conn)."___".$sinser);
			}
			$noinvoice=$param['noinvoice'];
		}else{
			$noinvoice=generateNoInvoice($optKdpt[$param['kodeorganisasi']],$param['tanggal']);
			$sinser="insert into ".$dbname.".keu_penagihanht
			(noinvoice,kodeorg,kodept,bayarke,tanggal,jatuhtempo,kuantitas,nofakturpajak,kodecustomer,nokontrak,nilaiinvoice,stsppn,nilaippn,stspph,nilaipph
			,matauang,kurs,ttd,jenis,debet,kredit,keterangan,rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,rupiah6,rupiah7,rupiah8,tipeinvoice) values ('".$noinvoice."','".$param['kodeorganisasi']."','".$optKdpt[$param['kodeorganisasi']]."','".$param['bayarke']."','".tanggalsystem($param['tanggal'])."','".tanggalsystem($param['jatuhtempo'])."','0','','".$param['kodecustomer']."','".$param['nokontrak']."','0','0','0','','0','".$param['matauang']."','".$param['kurs']."','".$param['ttd']."','".$param['jenis']."','".$param['debet']."','".$param['kredit']."','".$param['keterangan']."','0','0','0','0','0','0','0','0','2')";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			}
			if($param['nokontrak']!=''){
				$sKontrak="select * from ".$dbname.".pmn_kontraklaindt where nokontrak='".$param['nokontrak']."' order by kodebarang";
				$qKontrak= mysql_query($sKontrak) or die (mysql_error($conn));
				while($rdKontrak=  mysql_fetch_assoc($qKontrak)){
					$str="insert into ".$dbname.".keu_penagihandt (noinvoice,nokontrak,kodebarang,nilaiinventory,hargasatuan,nilaitransaksi,lastuser,lastdate) values ('".$noinvoice."','".$param['nokontrak']."','".$rdKontrak['kodebarang']."','".$rdKontrak['jumlah']."','".$rdKontrak['hargasatuan']."','".$rdKontrak['jmlharga']."','".$_SESSION['standard']['username']."',now())";
					//exit('Warning: '.$str);
					if(!mysql_query($str)){
						echo " Gagal,".addslashes(mysql_error($conn));
					}
				}
			}
		}
		echo $noinvoice;
		break;

	case'getKursInvoice':
		if($param['matauang']=='IDR'){
			echo '1';
		}else{
			$tanggal=tanggalsystem($param['tanggal']);
			$sKurs="select * from ".$dbname.".setup_matauangrate where kode='".$param['matauang']."' and daritanggal='".$tanggal."'";
			$qKurs=mysql_query($sKurs) or die(mysql_error($conn));
			if(mysql_num_rows($qKurs)<=0){
				echo 'Gagal. Tidak ada kurs pada tanggal '.$param['tanggal'];
			}else{
				$bKurs=mysql_fetch_assoc($qKurs);
				echo $bKurs['kurs'];
			}
		}
		break;

	case'getKontrakData':
		$kodeorganisasi=$_SESSION['empl']['lokasitugas'];
		$kodecustomer='';
		$matauang='IDR';
		$kurs=1;
		if($param['nokontrak']!=''){
			$shkontrak="select * from ".$dbname.".pmn_kontraklainht where nokontrak='".$param['nokontrak']."'";
			$qhkontrak=mysql_query($shkontrak) or die(mysql_error($conn));
			while($rhkontrak=  mysql_fetch_assoc($qhkontrak)){
				$kodeorganisasi=$rhkontrak['kodeorg'];
				$kodecustomer=$rhkontrak['koderekanan'];
				$matauang=$rhkontrak['matauang'];
				$kurs=$rhkontrak['kurs'];
			}
		}
		echo $kodeorganisasi.'###'.$kodecustomer.'###'.$matauang.'###'.$kurs;
        break;

	case'getNilai':
		if($param['matauang']!='IDR'){
			$ppn=$param['kurs']*$param['nilaippn'];
			$nilInv=$param['kurs']*$param['nilaiinvoice'];
			$pph=$param['kurs']*$param['nilaipph'];
		}else{
			$ppn=$param['nilaippn'];
			$nilInv=$param['nilaiinvoice'];
			$pph=$param['nilaipph'];
		}
		echo $ppn.'###'.$nilInv.'###'.$pph;
		//exit("Error:ASD");
        break;

	case'loadData':
	   // print_r($param);
		$where = '';
        if(!empty($param['noinvoiceCr'])) {
            $where.=" and a.noinvoice like '%".$param['noinvoiceCr']."%'";
        }
        if(!empty($param['tanggalCr'])) {            
            $tgrl=explode("-",$param['tanggalCr']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where.=" and left(a.tanggal,10) = '".$ert."'";
        }
		if(!empty($param['kodebarangCr'])) {
            //$where.=" and a.kodebarang = '".$param['kodebarangCr']."'";
            $where.=" and a.noinvoice in (select distinct noinvoice from ".$dbname.".keu_penagihandt where kodebarang = '".$param['kodebarangCr']."')";
        }
		if(!empty($param['kodecustomerCr'])) {
            $where.=" and a.kodecustomer = '".$param['kodecustomerCr']."'";
        }
		
        $sdel="";
        $limit=20;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
        $sql="select count(*) jmlhrow from ".$dbname.".keu_penagihanht a where a.tipeinvoice='2' ".$where." order by a.tanggal desc";
        //$sql="select count(*) jmlhrow from ".$dbname.".keu_penagihanht where nokontrak!='' ".$where." order by tanggal desc";
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
		$str="select a.* from ".$dbname.".keu_penagihanht a
				where a.tipeinvoice='2' ".$where."  order by a.tanggal desc
				limit ".$offset.",".$limit." ";
        //exit('Warning: '.$str);
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
			$nilaigtinv=$rstr['nilaiinvoice']+$rstr['nilaippn']-$rstr['nilaipph']+$rstr['ongkoskirim'];
            $nor+=1;//<td align=right>".number_format($rstr['nilaiinvoice'],0)."</td>
			$tab.="<tr class=rowcontent>
					<td align=center>".$nor."</td>
					<td id='noinvoice_".$nor."' align=left value='".$rstr['noinvoice']."'>".$rstr['noinvoice']."</td>
					<td id='kodeorg_".$nor."' align=center value='".$rstr['kodeorg']."'>".$rstr['kodeorg']."</td>
					<td id='tanggal_".$nor."' align=center value='".$rstr['tanggal']."'>".tanggalnormal(substr($rstr['tanggal'],0,10))."</td>
					<td>".$optnmCust[$rstr['kodecustomer']]."</td>
					<td align=center>".tanggalnormal(substr($rstr['jatuhtempo'],0,10))."</td>
					<td align=right>".number_format($nilaigtinv,2)."</td>
					<td>".$rstr['keterangan']."</td>";
			if($rstr['posting']==0) {
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['noinvoice']."' onclick=\"fillField('".$rstr['noinvoice']."');\" ></td>";
				$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['noinvoice']."' onclick=\"delData('".$rstr['noinvoice']."');\" ></td>";
				$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['noinvoice']."' onclick=\"postingData('".$rstr['noinvoice']."');\" ></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_invoice_komoditi_print',event);\" ></td>";
			} else {
                $tab.="<td>&ensp;</td><td>&ensp;</td>";
				$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['noinvoice']."' onclick=\"popUpPosting('No Invoice Afiliasi','".$rstr['noinvoice']."','<div id=formaAfiliasi></div>',event);\"  ></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_invoice_komoditi_print',event);\" ></td>";
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
		$sdata="select distinct * from ".$dbname.".keu_penagihanht 
				where noinvoice='".$param['noinvoice']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		echo $rdata['noinvoice']."###".$rdata['kodeorg']."###".$rdata['bayarke']."###".tanggalnormal(substr($rdata['tanggal'],0,10))."###".tanggalnormal(substr($rdata['jatuhtempo'],0,10))."###".$rdata['kodecustomer']."###".$rdata['nokontrak']."###".$rdata['matauang']."###".$rdata['kurs']."###".$rdata['ttd']."###".$rdata['jenis']."###".$rdata['debet']."###".$rdata['kredit']."###".$rdata['keterangan'];
		//."###".$rdata['stsppn']."###".$rdata['nilaippn'].$rdata['stspph']."###".$rdata['nilaipph']."###".$rdata['ongkoskirim'];
		break;

	case'getFormAfiliasi':
		$form="<div style='padding-top:20px;'><fieldset style='float: left;'>
               <legend>".$_SESSION['lang']['input']." No Invoice Afiliasi</legend>
               No Invoice Afiliasi&nbsp;<input type=text class=myinputtext id=noafiliasi />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=inputAfiliasi('".$param['noinvoice']."')>".$_SESSION['lang']['save']."</button></fieldset></div>";
        echo $form;
		break;

	case'inputNoAfiliasi':
		$sUpdate="update ".$dbname.".keu_penagihanht set noinvoice_afiliasi='".$param['noafiliasi']."' where noinvoice='".$param['noinvoice']."'";
		if(!mysql_query($sUpdate)){
            exit("error: gak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;
	
    case'delData':
        $sdel="delete from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
        if(!mysql_query($sdel)){
            exit("error: gak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;
	
    case'postingData':
		$sdata="select distinct * from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		$roc=mysql_num_rows($qdata);
		#=== Cek if posted ===
		if($rdata['posting']==1) {
			exit('Warning: '.$_SESSION['lang']['errisposted']);
		}
		#====cek periode
		$tgl = str_replace("-","",$rdata['tanggal']);
		$sprd = "select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg = '".$rdata['kodeorg']."' and tutupbuku = '0' order by tanggalmulai ASC LIMIT 1";
		$qprd=mysql_query($sprd) or die(mysql_error($conn));
		$rprd=mysql_fetch_assoc($qprd);
		$tgl2 = str_replace("-","",$rprd['tanggalmulai']);
		if($tgl < $tgl2){
			exit("Error:Date beyond active period");
		}
		// if($_SESSION['org']['period']['start']>$tgl)
			// exit('Error:Date beyond active period');
		#=== Cek if data not exist ===
		if($roc==0) {
			exit('Warning: '.$_SESSION['lang']['errheadernotexist']);
		}
		/**
		 * Parameter Jurnal
		 */
		// Get Parameter Claim
		$qClaim = selectQuery($dbname,'keu_5parameterjurnal','*',
			"kodeaplikasi='CLAIM' and jurnalid='SLE'");
		$resClaim = fetchData($qClaim);
		if(empty($resClaim)) exit("Warning: Parameter Jurnal CLAIM / SLE belum ada\nSilahkan hubungi IT");
		
		// Get Parameter Ppn
		$qPpn = selectQuery($dbname,'keu_5parameterjurnal','*',
			"kodeaplikasi='SPPN' and jurnalid='SLE'");
		$resPpn = fetchData($qPpn);
		if(empty($resPpn)) exit("Warning: Parameter Jurnal SPPN / SLE belum ada\nSilahkan hubungi IT");
		
		/**
		 * Pembentukan Jurnal
		 */
		// Get Last No Jurnal
		$yy=tanggalnormal(substr($rdata['tanggal'],0,10));
		$isyy=tanggalsystem($yy);
		$norjunal=$isyy."/".$rdata['kodeorg']."/PNJ/";
		$snojr="select max(substr(nojurnal,19,7)) as nourut from ".$dbname.".keu_jurnalht where nojurnal like '".$norjunal."%'";
		
		// Nomor Urut Jurnal
		$qnojr=mysql_query($snojr) or die(mysql_error($conn));
		$rnojr=mysql_fetch_assoc($qnojr);
		$nourut=addZero((intval($rnojr['nourut'])+1), '3');
		$nojurnal=$norjunal.$nourut;
		
		// Potongan
		$nilaiKlaimPengurang=$rdata['rupiah1']+$rdata['rupiah2']+$rdata['rupiah3']
					+$rdata['rupiah4']+$rdata['rupiah5']+$rdata['rupiah6']+$rdata['rupiah7']-$rdata['rupiah8'];
		$ppnKlaim=0;
		if($rdata['nilaippn']>0) {
			$ppnKlaim=$rdata['nilaippn']/$rdata['nilaiinvoice']*$nilaiKlaimPengurang;
		}
		$pphKlaim=0;
		if($rdata['nilaipph']>0) {
			$pphKlaim=$rdata['nilaipph']/$rdata['nilaiinvoice']*$nilaiKlaimPengurang;
		}
		$piutangKurang = $nilaiKlaimPengurang + $ppnKlaim - $pphKlaim;
		
		// Nilai
		$jumlahUM = $rdata['nilaiinvoice'];
		$jumlahPpn = $rdata['nilaippn'];
		$jumlahPph = $rdata['nilaipph'];
		$jumlahPiutang = $rdata['nilaiinvoice']+$rdata['nilaippn']-$rdata['nilaipph']+$rdata['ongkoskirim'];
		
		// Total
		$total = $jumlahPiutang + abs($piutangKurang);
		
		// Nama Customer
		$whrCus="kodecustomer='".$rdata['kodecustomer']."'";
		$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer',$whrCus);
		
		// Nama Barang
		$whrBrg="kodebarang='".$rdata['kodebarang']."'";
		$optnmbrg=makeOption($dbname, 'log_5masterbarang','kodebarang,namabarang',$whrBrg);
		$optsatbrg=makeOption($dbname, 'log_5masterbarang','kodebarang,satuan',$whrBrg);
		
		// Jurnal Header
		$dataH = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => 'PNJ',
			'tanggal' => $isyy,
			'tanggalentry' => date('Y-m-d'),
			'posting' => 1,
			'totaldebet' => $total,
			'totalkredit' => $total,
			'amountkoreksi' => 0,
			'noreferensi' => $rdata['noinvoice'],
			'autojurnal' => 1,
			'matauang' => 'IDR',
			'kurs' => 1,
			'revisi' => 0,
		);
		$colH = array();
		foreach($dataH as $key=>$row) {
			$colH[] = $key;
		}
		
		// Jurnal Detail
		$dataD = array();
		$colD = array('nojurnal','tanggal','nourut','noakun','keterangan',
			'jumlah','matauang','kurs','kodeorg','kodebarang','kodecustomer',
			'noreferensi','nodok','revisi','nik','kodesupplier');
		
		// Piutang Pihak Ketiga - Debet
		$dataD[] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $isyy,
			'nourut' => 1,
			'noakun' => $rdata['debet'],
			'keterangan' => 'PIUTANG '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
				.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
			'jumlah' => $jumlahPiutang,
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => '',
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => $rdata['nokontrak'],
			'revisi' => 0,
			'nik' => '',
			'kodesupplier' => ''
		);
		
		// Uang Muka Penjualan - Kredit
		$dataD[] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $isyy,
			'nourut' => 2,
			'noakun' => $rdata['kredit'],
			'keterangan' => 'PENJUALAN '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
				.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
			'jumlah' => $jumlahUM*(-1),
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => '',
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => $rdata['nokontrak'],
			'revisi' => 0,
			'nik' => '',
			'kodesupplier' => ''
		);
		$noUrut=2;
		if($rdata['ongkoskirim']>0) {
			$noUrut = $noUrut+1;
			// Ongkos kirim - Kredit
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $rdata['kredit'],
				'keterangan' => 'Biaya Kirim '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
					.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
				'jumlah' => $rdata['ongkoskirim']*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
		}

		if($jumlahPpn>0) {
			$noUrut = $noUrut+1;
			// Hutang Ppn Penjualan - Kredit
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resPpn[0]['noakunkredit'],
				'keterangan' => 'PPN Keluaran '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
					.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
				'jumlah' => $jumlahPpn*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
		}

		if($jumlahPph>0) {
			$noUrut = $noUrut+1;
			// Pph Penjualan - Debet
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $rdata['stspph'],
				'keterangan' => 'PPh '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
					.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
				'jumlah' => $jumlahPph,
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
		}
		
		if($nilaiKlaimPengurang != 0) {
			// Claim 
			$noUrut = $noUrut+1;
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resClaim[0]['noakundebet'],
				'keterangan' => 'Claim '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
					.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
				'jumlah' => $nilaiKlaimPengurang,
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
			
			// Pengurang / Penambah Piutang
			$noUrut = $noUrut+1;
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resClaim[0]['noakunkredit'],
				'keterangan' => 'Pengurang/Penambah Piutang '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
					.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
				'jumlah' => $piutangKurang*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
			
			if($ppnKlaim != 0) {
				// Ppn Klaim
				$noUrut = $noUrut+1;
				$dataD[] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $isyy,
					'nourut' => $noUrut,
					'noakun' => $resClaim[0]['sampaidebet'],
					'keterangan' => 'PPN Keluaran Claim '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
						.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
					'jumlah' => $ppnKlaim,
					'matauang' => 'IDR',
					'kurs' => 1,
					'kodeorg' => $rdata['kodeorg'],
					'kodebarang' => '',
					'kodecustomer' => $rdata['kodecustomer'],
					'noreferensi' => $rdata['noinvoice'],
					'nodok' => $rdata['nokontrak'],
					'revisi' => 0,
					'nik' => '',
					'kodesupplier' => ''
				);
			}

			if($pphKlaim != 0) {
				// Pph Klaim
				$noUrut = $noUrut+1;
				$dataD[] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $isyy,
					'nourut' => $noUrut,
					'noakun' => $rdata['stspph'],
					'keterangan' => 'PPh Claim '.$optnmcust[$rdata['kodecustomer']].' atas Invoice '.$rdata['noinvoice']
						.($rdata['nokontrak']=='' ? '' : ' u/ Kontrak '.$rdata['nokontrak']).' '.$rdata['keterangan'],
					'jumlah' => $pphKlaim,
					'matauang' => 'IDR',
					'kurs' => 1,
					'kodeorg' => $rdata['kodeorg'],
					'kodebarang' => '',
					'kodecustomer' => $rdata['kodecustomer'],
					'noreferensi' => $rdata['noinvoice'],
					'nodok' => $rdata['nokontrak'],
					'revisi' => 0,
					'nik' => '',
					'kodesupplier' => ''
				);
			}
		}
		
		// Query
		$sIns = insertQuery($dbname,'keu_jurnalht',$dataH,$colH);
		$sInsD = insertQuery($dbname,'keu_jurnaldt',$dataD, $colD);
		// Insert Header
		if(mysql_query($sIns)){
			if(!mysql_query($sInsD)){
				// Rollback
				$delH = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
				if(!mysql_query($delH)) {
					exit("Rollback Error: ".mysql_error($conn)."___".$delH);
				} else {
					exit("Insert Detail Error: ".mysql_error()."___".$sInsD);
				}
			}else{
				$supd="update ".$dbname.".keu_penagihanht set posting=1,jurnalstatus=1 where noinvoice='".$rdata['noinvoice']."'";
				if(!mysql_query($supd)){
					// Rollback
					$delH = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
					if(!mysql_query($delH)) {
						exit("Rollback Error: ".mysql_error($conn)."___".$delH);
					} else {
						exit("Update Penagihan Error: ".mysql_error($conn)."___".$supd);
					}
				}
			}
		} else {
			exit("Insert Header Error: ".mysql_error($conn)."___".$sinsert);
		}
		break;

	case'loadDetail':
		//exit('warning :'.$param['noinvoice']);
		$where="";
		if($param['noinvoice']!=''){
			$where.="a.noinvoice='".$param['noinvoice']."'";
		}else{
			exit;
		}

		// Pilihan kodebarang
		$optBrg2="<option value=''></option>";
		$optBrg3="";
		$sBrg2="select a.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a
				where a.inactive='0' and (a.kodebarang like '4%' or a.kodebarang like '386%') order by a.kodebarang";
		$qBrg2=mysql_query($sBrg2) or die(mysql_error($conn));
		while($rBrg2=mysql_fetch_assoc($qBrg2)){
			$optBrg2.="<option value=".$rBrg2['kodebarang'].">".$rBrg2['namabarang']."</option>";
			$optBrg3.="<option value=".$rBrg2['kodebarang'].">".$rBrg2['namabarang']."</option>";
		}

		//Data Detail
		$str="select a.*,b.nokontrak,c.namabarang,c.satuan from ".$dbname.".keu_penagihandt a 
				left join ".$dbname.".keu_penagihanht b on b.noinvoice=a.noinvoice
				left join ".$dbname.".log_5masterbarang c on c.kodebarang=a.kodebarang
				where ".$where."
				order by a.noinvoice,a.kodebarang";
		//exit('Warning: '.$sBrg2.'    '.$str);
		$res=mysql_query($str);
		$no=0;
		$subtotaltransaksi=0;
		while($bar=mysql_fetch_assoc($res)){
			$no+=1;
			$subtotaltransaksi+=$bar['nilaitransaksi'];
			$optBrg3="<option value=".$bar['kodebarang'].">".$bar['namabarang']."</option>".$optBrg3;
			echo "
			<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td><select id=kodebarang".$no." value='".$bar['kodebarang']."' disabled onchange=getSatuan(".$no.") style=\"width:600px;\">".$optBrg3."</select></td>
				<td align=center><input type=text id=satuan".$no." value='".$bar['satuan']."' disabled class=myinputtext style=\"width:45px;\"></td>
				<td align=right><input type=text id=nilaiinventory".$no." value=".number_format($bar['nilaiinventory'],2)." disabled class=myinputtextnumber onblur=getNilaiTransaksi(".$no.") style=\"width:105px;\"></td>
				<td align=center><input type=text id=hargasatuan".$no." value=".number_format($bar['nilaitransaksi']/$bar['nilaiinventory'],2)." disabled class=myinputtextnumber onblur=getNilaiTransaksi(".$no.") style=\"width:105px;\"></td>
				<td align=right><input type=text id=nilaitransaksi".$no." value=".number_format($bar['nilaitransaksi'],2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
				<td align=center>
					<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editdetail('".$bar['noinvoice']."','".$no."');\">&nbsp&nbsp&nbsp
					<img src=images/save.png class=resicon title='Save' onclick=\"savedetail('".$bar['noinvoice']."','".$bar['kodebarang']."','".$no."');\">&nbsp&nbsp&nbsp
					<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldetail('".$bar['noinvoice']."','".$bar['kodebarang']."');\">
				</td>
			</tr>";
		}
		if($param['nokontrak']==''){
			echo"<tr class=rowcontent>
					<td align=center></td>
					<td><select id=kodebarang onchange=getSatuan('') style=\"width:600px;\">".$optBrg2."</select></td>
					<td align=center><input type=text id=satuan class=myinputtext disabled style=\"width:45px;\"></td>
					<td align=right><input type=text id=nilaiinventory onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur=getNilaiTransaksi('') style=\"width:105px;\"></td>
					<td align=right><input type=text id=hargasatuan onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur=getNilaiTransaksi('') style=\"width:105px;\"></td>
					<td align=right><input type=text id=nilaitransaksi value=0 class=myinputtextnumber disabled style=\"width:130px;\"></td>
					<td align=center><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpandetail('".$param['noinvoice']."','".$param['nokontrak']."')></td>
			</tr>";
		}
		$nilaippn=0;
		$nilaipph=0;
		$ongkoskirim=0;
		$catatan='';
		$sht="select * from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
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
			$catatan=$dht['catatan'];
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
					<td align=right><input type=text id=nilaiinvoice value=".number_format($subtotaltransaksi,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
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
					<td align=right><input type=text id=totalinvoice value=".number_format($gtotaltransaksi,2)." disabled class=myinputtextnumber style=\"width:130px;\"></td>
					<td></td>
				</tr>
			</table>";
		echo "<table><tr><td></td></tr>
				<tr><td colspan=4>
						<button class=mybutton onclick=saveFoot('".$param['noinvoice']."')>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=saveBaru('".$param['noinvoice']."')>".$_SESSION['lang']['new']."</button>
				</td></tr>";
		echo"</table>"; 
	break;

	case 'simpandetail':
		if($param['noinvoice']==''){
			exit('Warning: No.Invoice tidak boleh kosong');
		}
		if($param['kodebarang']==''){
			exit('Warning: Kode Barang tidak boleh kosong');
		}
		if($param['nilaiinventory']=='' or $param['nilaiinventory']=='0' or $param['nilaiinventory']==0){
			exit('Warning: Jumlah tidak boleh kosong');
		}
		//Cari $notransaksidet
		$i="select * from ".$dbname.".keu_penagihandt where noinvoice='".$param['noinvoice']."' and kodebarang='".$param['kodebarang']."'";
		//exit('Warning: '.$i);
		$n=mysql_query($i) or die (mysql_error($conn));
		$ada=mysql_num_rows($n);
		if($ada>0){
			exit('Warning: Barang sudah ada...!');
		}
		$nilaitransaksi=$param['nilaiinventory']*$param['hargasatuan'];
		$str="insert into ".$dbname.".keu_penagihandt (noinvoice,nokontrak,kodebarang,nilaiinventory,hargasatuan,nilaitransaksi,lastuser,lastdate) values ('".$param['noinvoice']."','".$param['nokontrak']."','".$param['kodebarang']."','".$param['nilaiinventory']."','".$param['hargasatuan']."','".$nilaitransaksi."','".$_SESSION['standard']['username']."',now())";
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'deldetail':
		$str="delete from ".$dbname.".keu_penagihandt where noinvoice='".$param['noinvoice']."' and kodebarang='".$param['kodebarang']."'";
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'savedetail':
		if($param['noinvoice']==''){
			exit('Warning: No.Invoice tidak boleh kosong');
		}
		if($param['kodebarang']==''){
			exit('Warning: Kode Barang tidak boleh kosong');
		}
		if($param['nilaiinventory']=='' or $param['nilaiinventory']=='0' or $param['nilaiinventory']==0){
			exit('Warning: Jumlah tidak boleh kosong');
		}
		$nilaitransaksi=$param['nilaiinventory']*$param['hargasatuan'];
		$str="update ".$dbname.".keu_penagihandt set kodebarang='".$param['kodebarang']."',nilaiinventory='".$param['nilaiinventory']."'
			,hargasatuan='".$param['hargasatuan']."',nilaitransaksi='".$nilaitransaksi."',lastuser='".$_SESSION['standard']['username']."',lastdate=now() 
			where noinvoice='".$param['noinvoice']."' and kodebarang='".$param['oldkodebarang']."'";
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
		if($param['noinvoice']==''){
			exit('Warning: No.Invoice tidak boleh kosong');
		}
		$str="update ".$dbname.".keu_penagihanht set 
				nilaiinvoice='".$param['nilaiinvoice']."'
				,stsppn='".$param['stsppn']."',nilaippn='".$param['nilaippn']."'
				,stspph='".$param['stspph']."',nilaipph='".$param['nilaipph']."'
				,ongkoskirim='".$param['ongkoskirim']."',catatan='".$param['catatan']."'
				where noinvoice='".$param['noinvoice']."'";
		//exit('Warning : '.$str);
		if(!mysql_query($str))
			echo " Gagal,".addslashes(mysql_error($conn));
	break;

}

function generateNoInvoice($invPT,$tgl){
	global $dbname;
	global $conn;
	#no invoice
	$tgldt=explode("-",$tgl);
	$bulan = $tgldt[1];
	$thn=date('Y');
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	$ql="select `noinvoice` from ".$dbname.".`keu_penagihanht` where kodept = '".$invPT."' and left(noinvoice,3) = '".$invPT."' and left(tanggal,4) = '".$tgldt[2]."'
		and right(noinvoice,4) = '".$tgldt[2]."' order by noinvoice desc limit 1";
    $qr=mysql_query($ql) or die('error: '.mysql_error());
	$data = mysql_fetch_object($qr);
	$countNoInvoice = substr($data->noinvoice,4,3);
	$noInvoice = $invPT."/".addZero($countNoInvoice+1,3)."/JKT/".$resultRomawi."/".$tgldt[2];
	return $noInvoice;
}
