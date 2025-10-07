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
		if($param['kodebarang']==''){
            exit("error: Komoditi tidak boleh kosong");
        }
		if($param['kodecustomer']==''){
            exit("error: Customer tidak boleh kosong");
        }
        if($param['nilaiinvoice']==''){
            exit("error:Nilai invoice tidak boleh kosong");
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
		$param['nilaiinvoice']=str_replace(",","",$param['nilaiinvoice']);
		$param['nilaippn']=str_replace(",","",$param['nilaippn']);
		$param['nilaipph']=str_replace(",","",$param['nilaipph']);
		$whrBrg="kodebarang='".$param['kodebarang']."'";
		$optBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrBrg);
		$optKdpt=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$param['kodeorganisasi']."'");
        $param['kuantitas']=str_replace(",","",$param['kuantitas']);
		$param['kuantitas']==''?$param['kuantitas']=0:$param['kuantitas']=$param['kuantitas'];
		if($param['noinvoice']!=''){
			$sinser="update ".$dbname.".keu_penagihanht set kodeorg='".$param['kodeorganisasi']."',kodept='".$optKdpt[$param['kodeorganisasi']]."',bayarke='".$param['bayarke']."'
						,tanggal='".tanggalsystem($param['tanggal'])."',jatuhtempo='".tanggalsystem($param['jatuhtempo'])."'
						,kodebarang='".$param['kodebarang']."',kuantitas='".$param['kuantitas']."',nofakturpajak='".$param['nofakturpajak']."'
						,kodecustomer='".$param['kodecustomer']."',nilaiinvoice='".$param['nilaiinvoice']."'
						,stsppn='".$param['stsppn']."',nilaippn='".$param['nilaippn']."'
						,stspph='".$param['stspph']."',nilaipph='".$param['nilaipph']."'
						,matauang='".$param['matauang']."',kurs='".$param['kurs']."'
						,ttd='".$param['ttd']."',jenis='".$param['jenis']."'
						,debet='".$param['debet']."',kredit='".$param['kredit']."'
						,keterangan='".$param['keterangan']."' where noinvoice='".$param['noinvoice']."'";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 11256\n ".  mysql_error($conn)."___".$sinser);
			}
		}else{
			$sinser="insert into ".$dbname.".keu_penagihanht
			(noinvoice,kodeorg,kodept,bayarke,tanggal,jatuhtempo,kodebarang,kuantitas,nofakturpajak,kodecustomer,nilaiinvoice,stsppn,nilaippn,stspph,nilaipph
			,matauang,kurs,ttd,jenis,debet,kredit,keterangan,rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,rupiah6,rupiah7,rupiah8,tipeinvoice,nokontrak) values ('".generateNoInvoice($optKdpt[$param['kodeorganisasi']],$param['tanggal'])."','".$param['kodeorganisasi']."','".$optKdpt[$param['kodeorganisasi']]."','".$param['bayarke']."','".tanggalsystem($param['tanggal'])."','".tanggalsystem($param['jatuhtempo'])."','".$param['kodebarang']."','".$param['kuantitas']."','".$param['nofakturpajak']."','".$param['kodecustomer']."','".$param['nilaiinvoice']."','".$param['stsppn']."','".$param['nilaippn']."','".$param['stspph']."','".$param['nilaipph']."','".$param['matauang']."','".$param['kurs']."','".$param['ttd']."','".$param['jenis']."','".$param['debet']."','".$param['kredit']."','".$param['keterangan']."','0','0','0','0','0','0','0','0','1','')";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			}
		}
		break;	 

	case'getKursInvoice':
		if($param['kodebarang']==''){
			echo "Gagal. Komoditi harus diisi.";
		}else{
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
		}
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
            $where= $where." and a.noinvoice like '%".$param['noinvoiceCr']."%'";
        }
        if(!empty($param['tanggalCr'])) {            
            $tgrl=explode("-",$param['tanggalCr']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where= $where." and left(a.tanggal,10) = '".$ert."'";
        }
		if(!empty($param['kodebarangCr'])) {
            $where= $where." and a.kodebarang = '".$param['kodebarangCr']."'";
        }
		if(!empty($param['kodecustomerCr'])) {
            $where= $where." and a.kodecustomer = '".$param['kodecustomerCr']."'";
        }
		
        $sdel="";
        $limit=20;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
        $sql="select count(*) jmlhrow from ".$dbname.".keu_penagihanht a where a.nokontrak='' and a.tipeinvoice='1' ".$where." order by a.tanggal desc";
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
		$str="select a.*,b.namabarang from ".$dbname.".keu_penagihanht a
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				where a.nokontrak='' and tipeinvoice='1' ".$where."  order by a.tanggal desc
				limit ".$offset.",".$limit." ";
        //exit('Warning: '.$str);
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
			$nilaiinv=$rstr['nilaiinvoice']+$rstr['nilaippn'];
            $nilaiKlaimPengurang=$rstr['rupiah1']+$rstr['rupiah2']+$rstr['rupiah3']
                    +$rstr['rupiah4']+$rstr['rupiah5']+$rstr['rupiah6']+$rstr['rupiah7']-$rstr['rupiah8'];
            $ppnKlaim=0;
            if($rstr['nilaippn']>0){
           		$ppnKlaim=10/100*$nilaiKlaimPengurang;         	
            }
			$nilaiTot=$nilaiinv-$nilaiKlaimPengurang-$ppnKlaim;
			$harga=($rstr['kuantitas']==0 ? 0 : $nilaiTot/$rstr['kuantitas']);
            $nor+=1;//<td align=right>".number_format($rstr['nilaiinvoice'],0)."</td>
			$tab.="<tr class=rowcontent>
					<td id='noinvoice_".$nor."' align=center value='".$rstr['noinvoice']."'>".$rstr['noinvoice']."</td>
					<td id='kodeorg_".$nor."' align=center value='".$rstr['kodeorg']."'>".$rstr['kodeorg']."</td>
					<td id='tanggal_".$nor."' align=center value='".$rstr['tanggal']."'>".tanggalnormal(substr($rstr['tanggal'],0,10))."</td>
					<td>".$optnmCust[$rstr['kodecustomer']]."</td>
					<td>".$rstr['namabarang']."</td>
					<td align=right>".$rstr['kuantitas']."</td>
					<td align=right>".number_format($harga,2)."</td>
					<td align=right>".number_format($nilaiTot,2)."</td>";
			if($rstr['posting']==0) {
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['noinvoice']."' onclick=\"fillField('".$rstr['noinvoice']."');\" ></td>";
				$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['noinvoice']."' onclick=\"delData('".$rstr['noinvoice']."');\" ></td>";
				$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['noinvoice']."' onclick=\"postingData('".$rstr['noinvoice']."');\" ></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_invoice_nonkontrak_print',event);\" ></td>";
			} else {
                $tab.="<td>&ensp;</td><td>&ensp;</td>";
				$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['noinvoice']."' onclick=\"popUpPosting('No Invoice Afiliasi','".$rstr['noinvoice']."','<div id=formaAfiliasi></div>',event);\"  ></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_invoice_nonkontrak_print',event);\" ></td>";
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
		echo $rdata['noinvoice']."###".$rdata['kodeorg']."###".$rdata['bayarke']."###".tanggalnormal(substr($rdata['tanggal'],0,10))."###".tanggalnormal(substr($rdata['jatuhtempo'],0,10))."###".$rdata['kodebarang']."###".$rdata['kuantitas']."###".$rdata['nofakturpajak']."###".$rdata['kodecustomer']."###".number_format($rdata['nilaiinvoice'],2)."###".$rdata['stsppn']."###".number_format($rdata['nilaippn'],2)."###".$rdata['stspph']."###".number_format($rdata['nilaipph'],2)."###".$rdata['matauang']."###".$rdata['kurs']."###".$rdata['ttd']."###".$rdata['jenis']."###".$rdata['debet']."###".$rdata['kredit']."###".$rdata['keterangan'];
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
		$error0 = "";
		if($rdata['posting']==1) {
			$error0 .= $_SESSION['lang']['errisposted'];
		}
		if($error0!='') {
			echo "Data Error :\n".$error0;
			exit;
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
		$error1 = "";
		if($roc==0) {
			$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
		}
		if($error1!='') {
			echo "Data Error :\n".$error1;
			exit;
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
		
		// Nomor Urut
		$qnojr=mysql_query($snojr) or die(mysql_error($conn));
		$rnojr=mysql_fetch_assoc($qnojr);
		$nourut=addZero((intval($rnojr['nourut'])+1), '3');
		$nojurnal=$norjunal.$nourut;
		
		// Potongan
		$nilaiKlaimPengurang=$rdata['rupiah1']+$rdata['rupiah2']+$rdata['rupiah3']
					+$rdata['rupiah4']+$rdata['rupiah5']+$rdata['rupiah6']+$rdata['rupiah7']-$rdata['rupiah8'];
		$ppnKlaim=0;
		if($rdata['nilaippn']>0) {
			$ppnKlaim=10/100*$nilaiKlaimPengurang;
		}
		$pphKlaim=0;
		if($rdata['nilaipph']>0) {
			$pphKlaim=0.25/100*$nilaiKlaimPengurang;
		}
		$piutangKurang = $nilaiKlaimPengurang + $ppnKlaim - $pphKlaim;
		
		// Nilai
		$jumlahUM = $rdata['nilaiinvoice'];
		$jumlahPpn = $rdata['nilaippn'];
		$jumlahPph = $rdata['nilaipph'];
		$jumlahPiutang = $rdata['nilaiinvoice']+$rdata['nilaippn']-$rdata['nilaipph'];
		
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
			'keterangan' => ''.$optnmcust[$rdata['kodecustomer']].
				' Inv: '.$rdata['noinvoice'].' '.$rdata['kuantitas'].' '.$optsatbrg[$rdata['kodebarang']].' '.$optnmbrg[$rdata['kodebarang']].' '.$rdata['keterangan'],
			'jumlah' => $jumlahPiutang,
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => $rdata['kodebarang'],
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => '',
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
			'keterangan' => 'PIUTANG '.$optnmcust[$rdata['kodecustomer']].
				' atas Invoice '.$rdata['noinvoice'].' u/ Kontrak '.$rdata['nokontrak'].
				' sebanyak '.$rdata['kuantitas'].' '.$optsatbrg[$rdata['kodebarang']].' '.$optnmbrg[$rdata['kodebarang']].' '.$rdata['keterangan'],
			'jumlah' => $jumlahUM*(-1),
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => $rdata['kodebarang'],
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => '',
			'revisi' => 0,
			'nik' => '',
			'kodesupplier' => ''
		);
		
		$noUrut = 3;
		if($jumlahPpn>0) {
			// Hutang Ppn Penjualan - Kredit
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resPpn[0]['noakunkredit'],
				'keterangan' => 'PPn Keluaran '.$optnmcust[$rdata['kodecustomer']].
					' atas Invoice '.$rdata['noinvoice'].' u/ Kontrak '.$rdata['nokontrak'].
					' sebanyak '.$rdata['kuantitas'].' '.$optsatbrg[$rdata['kodebarang']].' '.$optnmbrg[$rdata['kodebarang']].' '.$rdata['keterangan'],
				'jumlah' => $jumlahPpn*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => '',
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
		}
		
		$noUrut = $noUrut+1;
		if($jumlahPph>0) {
			// Pph Penjualan - Debet
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $rdata['stspph'],
				'keterangan' => 'PPh '.$optnmcust[$rdata['kodecustomer']].
					' atas Invoice '.$rdata['noinvoice'].' u/ Kontrak '.$rdata['nokontrak'].
					' sebanyak '.$rdata['kuantitas'].' '.$optsatbrg[$rdata['kodebarang']].' '.$optnmbrg[$rdata['kodebarang']].' '.$rdata['keterangan'],
				'jumlah' => $jumlahPph,
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => '',
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
				'keterangan' => 'Claim dari Invoice '.$rdata['noinvoice'],
				'jumlah' => $nilaiKlaimPengurang,
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => '',
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
				'keterangan' => 'Pengurang / Penambah Piutang Invoice '.$rdata['noinvoice'],
				'jumlah' => $piutangKurang*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => '',
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
					'keterangan' => 'PPn Claim dari Invoice'.$rdata['noinvoice'],
					'jumlah' => $ppnKlaim,
					'matauang' => 'IDR',
					'kurs' => 1,
					'kodeorg' => $rdata['kodeorg'],
					'kodebarang' => '',
					'kodecustomer' => $rdata['kodecustomer'],
					'noreferensi' => $rdata['noinvoice'],
					'nodok' => '',
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
					'keterangan' => 'PPh Claim dari Invoice'.$rdata['noinvoice'],
					'jumlah' => $pphKlaim,
					'matauang' => 'IDR',
					'kurs' => 1,
					'kodeorg' => $rdata['kodeorg'],
					'kodebarang' => '',
					'kodecustomer' => $rdata['kodecustomer'],
					'noreferensi' => $rdata['noinvoice'],
					'nodok' => '',
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
