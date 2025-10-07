<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param=$_POST;
$optnmBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

switch($param['proses']){
	case'loadData':
		$where = '';
        if(!empty($param['carinotransaksi'])) {
            $where= $where." and a.notransaksi like '%".$param['carinotransaksi']."%'";
        }
        if(!empty($param['caritanggal'])) {
            $tgrl=explode("-",$param['caritanggal']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where= $where." and a.tanggal = '".$ert."'";
        }
		if(!empty($param['caribarang'])) {
            $where= $where." and b.kodebarang = '".$param['caribarang']."'";
        }
		
        $limit=20;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        //$offset=$page*$limit;
        $sql="select count(*) jmlhrow from ".$dbname.".log_brgjadiht a 
				where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$where." order by a.tanggal desc,a.notransaksi";
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
        if($page>=$totrows) {
            $page=$totrows-1;
        }
        $offset=$page*$limit;
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		$str="select a.* from ".$dbname.".log_brgjadiht a
				where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$where." order by a.tanggal desc,a.notransaksi
				limit ".$offset.",".$limit." ";
        //exit('Warning: '.$page.' > '.$totrows);
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
            $nor+=1;
			$tab.="<tr class=rowcontent>
					<td id='notransaksi".$nor."' value='".$rstr['notransaksi']."'>".$rstr['notransaksi']."</td>
					<td id='kodeorg_".$nor."' align=center value='".$rstr['kodeorg']."'>".$rstr['kodeorg']."</td>
					<td id='kodegudang_".$nor."' align=center value='".$rstr['kodegudang']."'>".$rstr['kodegudang']."</td>
					<td id='tanggal_".$nor."' align=center value='".$rstr['tanggal']."'>".tanggalnormal(substr($rstr['tanggal'],0,10))."</td>
					<td>".$rstr['keterangan']."</td>";
			if($rstr['posting']==0) {
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['notransaksi']."' onclick=\"fillField('".$rstr['notransaksi']."');\" ></td>";
				$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['notransaksi']."' onclick=\"delData('".$rstr['notransaksi']."');\" ></td>";
				$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['notransaksi']."' onclick=\"postingData('".$rstr['notransaksi']."');\" ></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['notransaksi']."' onclick=\"preview_PDF('".$rstr['notransaksi']."',event);\"></td>";
			} else {
                $tab.="<td>&ensp;</td><td>&ensp;</td>";
				$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['notransaksi']."'></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['notransaksi']."' onclick=\"preview_PDF('".$rstr['notransaksi']."',event);\"></td>";
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

	case'insert':
		//if($param['notransaksi']==''){
        //    exit("Warning: No Transaksi tidak boleh kosong");
        //}
		if($param['kodegudang']==''){
            exit("Warning: Gudang tidak boleh kosong");
        }
		if($param['tanggal']==''){
            exit("Warning: Tanggal tidak boleh kosong");
        }
		$sData="select a.* from ".$dbname.".log_brgjadiht a where a.notransaksi='".$param['notransaksi']."'";
        $qData=mysql_query($sData) or die(mysql_error($conn));
		$rnum=mysql_num_rows($qData);
		while($rData=mysql_fetch_assoc($qData)){
			if(substr(tanggalnormal($rData['tanggal']),3,2)!=substr($param['tanggal'],3,2)){
				exit("Warning: Bulan pada Tanggal tidak sama dengan No Transaksi...!");
			}
		}
		if($rnum>0){
			$notransaksi=$param['notransaksi'];
			$sinser="update ".$dbname.".log_brgjadiht set tanggal='".tanggalsystem($param['tanggal'])."',keterangan='".$param['keterangan']."'
					,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where notransaksi='".$notransaksi."'";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 11256\n ".  mysql_error($conn)."___".$sinser);
			}
		}else{
			$notransaksi=generateNoTransaksi($param['kodegudang'],$param['tanggal']);
			$sinser="insert into ".$dbname.".log_brgjadiht
			(notransaksi,kodeorg,kodegudang,tanggal,keterangan,posting,lastuser,lastdate) values ('".$notransaksi."','".$_SESSION['empl']['lokasitugas']."','".$param['kodegudang']."','".tanggalsystem($param['tanggal'])."','".$param['keterangan']."',0,'".$_SESSION['standard']['username']."',now())";
			//exit('Warning: '.$sinser);
			if(!mysql_query($sinser)){
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			}
		}
		echo $notransaksi;
		break;

    case'getData':
		$sdata="select distinct * from ".$dbname.".log_brgjadiht 
				where notransaksi='".$param['notransaksi']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		echo $rdata['notransaksi']."###".$rdata['kodegudang']."###".tanggalnormal(substr($rdata['tanggal'],0,10))."###".$rdata['keterangan'];
		break;

    case'delData':
        $sdel="delete from ".$dbname.".log_brgjadiht where notransaksi='".$param['notransaksi']."'";
        if(!mysql_query($sdel)){
            exit("error: Tidak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;

	case'loadDetail':
		$sDetail="select a.* from ".$dbname.".log_brgjadidt a
				  left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				  where a.notransaksi='".$param['notransaksi']."' and a.tipetransaksi='".$param['tipetransaksi']."'
				  order by a.notransaksi,a.kodebarang";
        //exit('Warning: '.$sDetail);
        $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
		$dtab='';$dnor=0;
        while($rDetail=  mysql_fetch_assoc($qDetail)) {
            $dnor+=1;
			$dtab.="<tr class=rowcontent>
					<td id='kodebarang_".$dnor."' align=center value='".$rDetail['kodebarang']."'>".$rDetail['kodebarang']."</td>
					<td id='namabarang_".$dnor."' align=center value='".$rDetail['namabarang']."'>".$rDetail['namabarang']."</td>
					<td id='jumlah_".$dnor."' align=center value='".$rDetail['jumlah']."'>".$rDetail['jumlah']."</td>";
			$dtab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rDetail['namabarang']."' onclick=\"dfillField('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rDetail['namabarang']."' onclick=\"delDetail('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="</tr>";
        }
		echo $dtab;
		break;

	case'loadDetail1':
		$sDetail="select a.*,b.namabarang,b.satuan from ".$dbname.".log_brgjadidt a
				  left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				  where a.notransaksi='".$param['notransaksi']."' and a.tipetransaksi='".$param['tipetransaksi']."'
				  order by a.notransaksi,a.kodebarang";
        //exit('Warning: '.$sDetail);
        $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
		$dtab='';$dnor=0;
        while($rDetail=  mysql_fetch_assoc($qDetail)) {
            $dnor+=1;
			$dtab.="<tr class=rowcontent>
					<td id='kodebarang_".$dnor."' align=center value='".$rDetail['kodebarang']."'>".$rDetail['kodebarang']."</td>
					<td id='namabarang_".$dnor."' align=left   value='".$rDetail['namabarang']."'>".$rDetail['namabarang']."</td>
					<td id='jumlah_".$dnor."' align=right value='".$rDetail['jumlah']."'>".$rDetail['jumlah']."</td>
					<td id='satuan_".$dnor."' align=center value='".$rDetail['satuan']."'>".$rDetail['satuan']."</td>";
			$dtab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rDetail['namabarang']."' onclick=\"dfillField('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rDetail['namabarang']."' onclick=\"delDetail('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="</tr>";
        }
		echo $dtab;
		break;

	case'loadDetail2':
		$sDetail="select a.*,b.namabarang,b.satuan from ".$dbname.".log_brgjadidt a
				  left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				  where a.notransaksi='".$param['notransaksi']."' and a.tipetransaksi='".$param['tipetransaksi']."'
				  order by a.notransaksi,a.kodebarang";
        //exit('Warning: '.$sDetail);
        $qDetail=mysql_query($sDetail) or die(mysql_error($conn));
		$dtab='';$dnor=0;
        while($rDetail=  mysql_fetch_assoc($qDetail)) {
            $dnor+=1;
			$dtab.="<tr class=rowcontent>
					<td id='kodebarang_".$dnor."' align=center value='".$rDetail['kodebarang']."'>".$rDetail['kodebarang']."</td>
					<td id='namabarang_".$dnor."' align=left   value='".$rDetail['namabarang']."'>".$rDetail['namabarang']."</td>
					<td id='jumlah_".$dnor."' align=right value='".$rDetail['jumlah']."'>".$rDetail['jumlah']."</td>
					<td id='satuan_".$dnor."' align=center value='".$rDetail['satuan']."'>".$rDetail['satuan']."</td>";
			$dtab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rDetail['namabarang']."' onclick=\"dfillField('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rDetail['namabarang']."' onclick=\"delDetail('".$rDetail['notransaksi']."','".$rDetail['kodebarang']."','".$rDetail['tipetransaksi']."','".$rDetail['jumlah']."');\" ></td>";
			$dtab.="</tr>";
        }
		echo $dtab;
		break;
/*
	case'saveDetail':
		if($param['kodebarang']==''){
            exit("Warning: Barang tidak boleh kosong");
        }
		if($param['jumlah']==''){
            exit("Warning: Jumlah tidak boleh kosong");
        }
		$sdData="select a.* from ".$dbname.".log_brgjadidt a where a.notransaksi='".$param['notransaksi']."' and a.kodebarang='".$param['kodebarang']."' and a.tipetransaksi='".$param['tipetransaksi']."'";
        $qdData=mysql_query($sdData) or die(mysql_error($conn));
		$rdnum=mysql_num_rows($qdData);
		if($rdnum>0){
			$sdinser="update ".$dbname.".log_brgjadidt set jumlah='".$param['jumlah']."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarang']."' and tipetransaksi='".$param['tipetransaksi']."'";
		}else{
			$sdinser="insert into ".$dbname.".log_brgjadiht (notransaksi,kodebarang,tipetransaksi,jumlah,lastuser,lastdate) values
					('".$param['notransaksi']."','".$_SESSION['empl']['kodebarang']."','".$param['tipetransaksi']."','".$param['jumlah']."'
					,'".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$sdinser);
		if(!mysql_query($sdinser)){
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdinser);
		}
		break;
*/
	case'saveDetail1':
		if($param['kodebarang1']==''){
            exit("Warning: Barang1 tidak boleh kosong");
        }
		if($param['jumlah1']=='' or $param['jumlah1']=='0' or $param['jumlah1']==0){
            exit("Warning: Jumlah tidak boleh kosong");
        }
		$sSaldo="select saldoakhirqty from ".$dbname.".log_5saldobulanan where kodegudang='".$param['kodegudang']."' and kodebarang='".$param['kodebarang1']."' and periode='".substr($param['tanggal'],6,4)."-".substr($param['tanggal'],3,2)."'";
		//exit('Warning: '.$sSaldo);
        $qSaldo=mysql_query($sSaldo) or die(mysql_error($conn));
		$saldo=0;
		while($dSaldo=mysql_fetch_assoc($qSaldo)){
			$saldo=$dSaldo['saldoakhirqty'];
		}
		if($saldo<$param['jumlah1']){
			exit("Warning: Saldo tidak cukup...!");
		}
		$sdData="select a.* from ".$dbname.".log_brgjadidt a where a.notransaksi='".$param['notransaksi']."' and a.kodebarang='".$param['kodebarang1']."' and a.tipetransaksi=1";
        $qdData=mysql_query($sdData) or die(mysql_error($conn));
		$rdnum=mysql_num_rows($qdData);
		if($rdnum>0){
			$sdinser="update ".$dbname.".log_brgjadidt set jumlah='".$param['jumlah1']."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarang1']."' and tipetransaksi=1";
		}else{
			$sdinser="insert into ".$dbname.".log_brgjadidt (notransaksi,kodebarang,tipetransaksi,jumlah,lastuser,lastdate) values
					('".$param['notransaksi']."','".$param['kodebarang1']."',1,'".$param['jumlah1']."'
					,'".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$sdinser);
		if(!mysql_query($sdinser)){
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdinser);
		}
		break;

	case'saveDetail2':
		if($param['kodebarang2']==''){
            exit("Warning: Barang2 tidak boleh kosong");
        }
		if($param['jumlah2']=='' or $param['jumlah2']=='0' or $param['jumlah2']==0){
            exit("Warning: Jumlah tidak boleh kosong");
        }
		$sdData="select a.* from ".$dbname.".log_brgjadidt a where a.notransaksi='".$param['notransaksi']."' and a.kodebarang='".$param['kodebarang2']."' and a.tipetransaksi=2";
        $qdData=mysql_query($sdData) or die(mysql_error($conn));
		$rdnum=mysql_num_rows($qdData);
		if($rdnum>0){
			$sdinser="update ".$dbname.".log_brgjadidt set jumlah='".$param['jumlah2']."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarang2']."' and tipetransaksi=2";
		}else{
			$sdData="select a.* from ".$dbname.".log_brgjadidt a where a.notransaksi='".$param['notransaksi']."' and a.tipetransaksi=2";
			$qdData=mysql_query($sdData) or die(mysql_error($conn));
			$rdnum=mysql_num_rows($qdData);
			if($rdnum>0){
				exit("Warning: Hanya bisa 1 barang untuk bahan jadi...!");
			}
			$sdinser="insert into ".$dbname.".log_brgjadidt (notransaksi,kodebarang,tipetransaksi,jumlah,lastuser,lastdate) values
					('".$param['notransaksi']."','".$param['kodebarang2']."',2,'".$param['jumlah2']."'
					,'".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$sdinser);
		if(!mysql_query($sdinser)){
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdinser);
		}
		break;

    case'delDetail':
        $sdel="delete from ".$dbname.".log_brgjadidt where notransaksi='".$param['notransaksi']."' and tipetransaksi='".$param['tipetransaksi']."' and kodebarang='".$param['kodebarang']."'";
        if(!mysql_query($sdel)){
            exit("error: Tidak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;

    case'postingData':
		#Data header
		$sht="select a.*,b.induk from ".$dbname.".log_brgjadiht a
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				where a.notransaksi='".$param['notransaksi']."'";
		$qht=mysql_query($sht) or die (mysql_error($conn));
		while($dht=mysql_fetch_assoc($qht)){
			$kodept=$dht['induk'];
			$kodeorg=$dht['kodeorg'];
			$kodegudang=$dht['kodegudang'];
			$tanggal=$dht['tanggal'];
		}
		#periksa periode gudang
		$sCek="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$kodegudang."' and tutupbuku=0 and periode='".substr($tanggal,0,7)."'";
		$qCek=mysql_query($sCek);
		if(mysql_num_rows($qCek)<1){
			exit('Warning: Tanggal tidak sesuai dengan Periode Akuntansi berjalan...!');
		}
		#periksa saldo
		$sCek="SELECT a.*,b.kodegudang,b.tanggal,c.saldoakhirqty,c.hargarata,d.namabarang FROM ".$dbname.".log_brgjadidt a 
				left join ".$dbname.".log_brgjadiht b on b.notransaksi=a.notransaksi
				left join ".$dbname.".log_5saldobulanan c on c.kodegudang=b.kodegudang and c.periode=left(b.tanggal,7) and c.kodebarang=a.kodebarang
				left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
				where a.tipetransaksi=1 and a.notransaksi='".$param['notransaksi']."'";
				//where a.tipetransaksi=1 and a.notransaksi='".$param['notransaksi']."' and a.jumlah>c.saldoakhirqty";
		$qCek=mysql_query($sCek);
		$rcnum=mysql_num_rows($qCek);
		if($rcnum<1){
			exit('Warning: Tidak ada data detail yang diinput...!');
		}
		$brgsld='';
		$totalharga=0;
		while($dCek=mysql_fetch_assoc($qCek)){
			$totalharga+=$dCek['jumlah']*$dCek['hargarata'];
			if($dCek['jumlah']>$dCek['saldoakhirqty']){
				$brgsld.='
	'.$dCek['namabarang'].' = '.$dCek['saldoakhirqty'];
			}
		}
		if($brgsld!=''){
			exit("Warning: Saldo barang tidak cukup...!".$brgsld);
		}
		// Pengeluaran Barang GI
		// Generate No Transaksi
		$snogi="select max(left(notransaksi,11)) as maxnum from ".$dbname.".log_transaksiht 
				where right(notransaksi,9)='GI-".$kodegudang."' and left(notransaksi,6)='".str_replace('-','',substr($tanggal,0,7))."' 
				and substr(notransaksi,7,1)<>'M'";
		$qnogi=fetchData($snogi);
		if(empty($qnogi[0]['maxnum'])) {
			$nogi=str_replace('-','',substr($tanggal,0,7))."00001-GI-".$kodegudang;
		} else {
			$nogi=($qnogi[0]['maxnum']+1)."-GI-".$kodegudang;
		}
		// Insert Header Pengeluaran Barang GI
		$shtgi="insert into ".$dbname.".log_transaksiht
				(tipetransaksi,notransaksi,tanggal,kodept,keterangan,statusjurnal,kodegudang,user,post,postedby,notransaksireferensi,lastupdate) values
				(8,'".$nogi."','".$tanggal."','".$kodept."','Bahan Baku ke Bahan Jadi',1,'".$kodegudang."','".$_SESSION['standard']['userid']."',1
				,'".$_SESSION['standard']['userid']."','".$param['notransaksi']."',now())";
		//exit('Warning: '.$shtgi);
		if(!mysql_query($shtgi)){
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$shtgi);
		}
		// Get Journal Counter
		$kdjurnal='INVM4';
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kdjurnal."'");
		$tmpKonter = fetchData($queryJ);
		if(empty($tmpKonter)) exit("Warning: Kelompok Jurnal ".$kdjurnal." untuk PT ".$kodept." belum ada.\n Silahkan hubungi IT dengan melampirkan pesan ini");
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);
		// No Jurnal
		$nojurnal = str_replace('-','',$tanggal)."/".$kodeorg."/".$kdjurnal."/".$konter;
		// Insert Jurnal Header
		$shtjr="insert into ".$dbname.".keu_jurnalht
				(nojurnal,kodejurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi) values
				('".$nojurnal."','".$kdjurnal."','".$tanggal."',CURDATE(),1,'".$totalharga."','".$totalharga."','0','".$param['notransaksi']."',1,'IDR','1',0)";
		//exit('Warning: '.$shtjr);
		if(!mysql_query($shtjr)){
			//Rollback
	        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
	        if(!mysql_query($sdelgi)){
				exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
			}
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$shtjr);
		}
		$sdinser="update ".$dbname.".log_brgjadidt set jumlah='".$param['jumlah2']."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarang2']."' and tipetransaksi=2";
		// Update No Kounter Jurnal
		$skljr="update ".$dbname.".keu_5kelompokjurnal set nokounter=".$konter." where kodeorg='".$kodept."' and kodekelompok='".$kdjurnal."'";
		//exit('Warning: '.$skljr);
		if(!mysql_query($skljr)){
			//Rollback GI
	        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
	        if(!mysql_query($sdelgi)){
				exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
			}
			//Rollback Jurnal HT
	        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
	        if(!mysql_query($sdeljr)){
				exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
			}
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$skljr);
		}
		// Get Data Detail
		$sdata="SELECT a.*,b.kodegudang,b.tanggal,c.saldoakhirqty,c.hargarata,c.nilaisaldoakhir,c.qtykeluar,c.qtykeluarxharga
				,d.namabarang,d.satuan,d.kelompokbarang,e.noakun 
				FROM ".$dbname.".log_brgjadidt a 
				left join ".$dbname.".log_brgjadiht b on b.notransaksi=a.notransaksi
				left join ".$dbname.".log_5saldobulanan c on c.kodegudang=b.kodegudang and c.periode=left(b.tanggal,7) and c.kodebarang=a.kodebarang
				left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
				left join ".$dbname.".log_5klbarang e on e.kode=d.kelompokbarang
				where a.tipetransaksi=1 and a.notransaksi='".$param['notransaksi']."'";
		//exit('Warning: '.$sdata);
		$qdata=mysql_query($sdata) or die (mysql_error($conn));
		$nourut=0;
		$totalharga=0;
		while($rdata=mysql_fetch_assoc($qdata)){
			$nourut+=1;
			$totalharga+=$rdata['jumlah']*$rdata['hargarata'];
			$jmlharga=$rdata['jumlah']*$rdata['hargarata'];
			$saldoakhirqty=$rdata['saldoakhirqty'];
			$hargarata=$rdata['hargarata'];
			$nilaisaldoakhir=$rdata['nilaisaldoakhir'];
			$qtykeluar=$rdata['qtykeluar'];
			$qtykeluarxharga=$rdata['qtykeluarxharga'];
			$qtyakhirgi=$rdata['saldoakhirqty']-$rdata['jumlah'];
			// Insert Detail Pengeluaran Barang GI
			$sdtgi="insert into ".$dbname.".log_transaksidt
					(notransaksi,kodebarang,satuan,jumlah,jumlahlalu,hargasatuan,waktutransaksi,updateby,statussaldo,hargarata) values
					('".$nogi."','".$rdata['kodebarang']."','".$rdata['satuan']."','".$rdata['jumlah']."','".$rdata['saldoakhirqty']."'
					,'".$rdata['hargarata']."',now(),'".$_SESSION['standard']['userid']."',1,'".$rdata['hargarata']."')";
			//exit('Warning: '.$sdtgi.' Totalharga='.$totalharga);
			if(!mysql_query($sdtgi)){
				//Rollback GI
		        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
		        if(!mysql_query($sdelgi)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdtgi);
			}
			// Insert Detail Jurnal
			$keterangan="Bahan baku ke bahan jadi ".$rdata['namabarang']." ".$rdata['jumlah']." ".$rdata['satuan'];
			$sdtjr="insert into ".$dbname.".keu_jurnaldt
					(nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,noaruskas,kodevhc,nodok,kodeblok,revisi) values
					('".$nojurnal."','".$tanggal."',".$nourut.",'".$rdata['noakun']."','".$keterangan."','".($jmlharga*-1)."','IDR','1','".$kodeorg."','',''
					,'".$rdata['kodebarang']."','','','','".$param['notransaksi']."','','','".$nogi."','',0)";
			//exit('Warning: '.$sdtjr.' harga='.$hargasatuan);
			if(!mysql_query($sdtjr)){
				//Rollback GI
		        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
		        if(!mysql_query($sdelgi)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdtjr);
			}
			// Update Saldo bulanan barang
			$ssdbl="update ".$dbname.".log_5saldobulanan set qtykeluar=qtykeluar+".$rdata['jumlah'].", qtykeluarxharga=qtykeluarxharga+".$jmlharga."
					, saldoakhirqty=saldoakhirqty-".$rdata['jumlah'].", nilaisaldoakhir=nilaisaldoakhir-".$jmlharga."
					, hargarata=(nilaisaldoakhir/saldoakhirqty)
					where kodegudang='".$kodegudang."' and periode='".substr($tanggal,0,7)."' and kodebarang='".$rdata['kodebarang']."'";
			//exit('Warning: '.$ssdbl.' harga='.$jmlharga);
			if(!mysql_query($ssdbl)){
				//Rollback GI
		        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
		        if(!mysql_query($sdelgi)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$ssdbl);
			}
			// Update Master barang DT
			$smsdt="update ".$dbname.".log_5masterbarangdt set saldoqty='".$qtyakhirgi."', hargalastout='".$hargarata."' 
					where kodegudang='".$kodegudang."' and kodebarang='".$rdata['kodebarang']."'";
			//exit('Warning: '.$smsdt.' harga='.$hargarata);
			if(!mysql_query($smsdt)){
				/*
				//Rollback Saldo bulanan
				$ssdbl="update ".$dbname.".log_5saldobulanan set qtykeluar=".$qtykeluar.", qtykeluarharga=".$qtykeluarharga."
						, saldoakhirqty=".$saldoakhirqty.", nilaisaldoakhir=".$nilaisaldoakhir." 
						where kodegudang='".$kodegudang."' and periode='".substr($tanggal,0,7)."' and kodebarang='".$rdata['kodebarang']."'";
		        if(!mysql_query($ssdbl)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$ssdbl);
				}
				*/
				//Rollback GI
		        $sdelgi="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."'";
		        if(!mysql_query($sdelgi)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgi);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$smsdt);
			}
		}

		// Penerimaan Barang GR
		// Generate No Transaksi
		$snogr="select max(left(notransaksi,11)) as maxnum from ".$dbname.".log_transaksiht 
				where right(notransaksi,9)='GR-".$kodegudang."' and left(notransaksi,6)='".str_replace('-','',substr($tanggal,0,7))."' 
				and substr(notransaksi,7,1)<>'M'";
		$qnogr=fetchData($snogr);
		if(empty($qnogr[0]['maxnum'])) {
			$nogr=str_replace('-','',substr($tanggal,0,7))."00001-GR-".$kodegudang;
		} else {
			$nogr=($qnogr[0]['maxnum']+1)."-GR-".$kodegudang;
		}
		// Insert Penerimaan Barang GR
		$shtgr="insert into ".$dbname.".log_transaksiht
				(tipetransaksi,notransaksi,tanggal,kodept,keterangan,statusjurnal,kodegudang,user,post,postedby,notransaksireferensi,lastupdate) values
				(4,'".$nogr."','".$tanggal."','".$kodept."','Bahan Baku ke Bahan Jadi',1,'".$kodegudang."','".$_SESSION['standard']['userid']."',1
				,'".$_SESSION['standard']['userid']."','".$param['notransaksi']."',now())";
		//exit('Warning: '.$shtgr);
		if(!mysql_query($shtgr)){
			//Rollback
	        $sdelgr="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."' or notransaksi='".$nogr."'";
	        if(!mysql_query($sdelgr)){
				exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgr);
			}
			exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$shtgr);
		}
		$sdata="SELECT a.*,b.kodegudang,b.tanggal,c.saldoakhirqty,c.hargarata,c.nilaisaldoakhir,c.qtymasuk,c.qtymasukxharga
				,d.namabarang,d.satuan,d.kelompokbarang,e.noakun 
				FROM ".$dbname.".log_brgjadidt a 
				left join ".$dbname.".log_brgjadiht b on b.notransaksi=a.notransaksi
				left join ".$dbname.".log_5saldobulanan c on c.kodegudang=b.kodegudang and c.periode=left(b.tanggal,7) and c.kodebarang=a.kodebarang
				left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
				left join ".$dbname.".log_5klbarang e on e.kode=d.kelompokbarang
				where a.tipetransaksi=2 and a.notransaksi='".$param['notransaksi']."'";
		//exit('Warning: '.$sdata);
		$qdata=mysql_query($sdata) or die (mysql_error($conn));
		while($rdata=mysql_fetch_assoc($qdata)){
			$nourut+=1;
			$hargasatuan=$totalharga/$rdata['jumlah'];
			$qtyakhirgr=$rdata['saldoakhirqty']+$rdata['jumlah'];
			// Insert Detail Penerimaan Barang GR
			$sdtgr="insert into ".$dbname.".log_transaksidt
					(notransaksi,kodebarang,satuan,jumlah,jumlahlalu,hargasatuan,waktutransaksi,updateby,statussaldo,hargarata) values
					('".$nogr."','".$rdata['kodebarang']."','".$rdata['satuan']."','".$rdata['jumlah']."','".$rdata['saldoakhirqty']."'
					,'".$hargasatuan."',now(),'".$_SESSION['standard']['userid']."',1,'".$hargasatuan."')";
			//exit('Warning: '.$sdtgr.' Harga='.$hargasatuan);
			if(!mysql_query($sdtgr)){
				//Rollback GR dan GI
		        $sdelgr="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."' or notransaksi='".$nogr."'";
		        if(!mysql_query($sdelgr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdtgr);
			}
			// Insert Detail Jurnal
			$keterangan="Bahan baku ke bahan jadi ".$rdata['namabarang']." ".$rdata['jumlah']." ".$rdata['satuan'];
			$sdtjr="insert into ".$dbname.".keu_jurnaldt
					(nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,noaruskas,kodevhc,nodok,kodeblok,revisi) values
					('".$nojurnal."','".$tanggal."',".$nourut.",'".$rdata['noakun']."','".$keterangan."','".$totalharga."','IDR','1','".$kodeorg."','',''
					,'".$rdata['kodebarang']."','','','','".$param['notransaksi']."','','','".$nogr."','',0)";
			//exit('Warning: '.$sdtjr.' harga='.$totalharga);
			if(!mysql_query($sdtjr)){
				//Rollback GR dan GI
		        $sdelgr="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."' or notransaksi='".$nogr."'";
		        if(!mysql_query($sdelgr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgr);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$sdtjr);
			}
			// Update/insert Saldo bulanan barang
			$ssdbl="select * from ".$dbname.".log_5saldobulanan 
					where kodegudang='".$kodegudang."' and periode='".substr($tanggal,0,7)."' and kodebarang='".$rdata['kodebarang']."'";
			//exit('Warning: '.$ssdbl.' harga='.$hargasatuan);
			$qsdbl=mysql_query($ssdbl);
			$rsnum=mysql_num_rows($qsdbl);
			if($rsnum>0){
				$ssdbl="update ".$dbname.".log_5saldobulanan set qtymasuk=qtymasuk+".$rdata['jumlah'].", qtymasukxharga=qtymasukxharga+".$totalharga."
						, saldoakhirqty=saldoakhirqty+".$rdata['jumlah'].", nilaisaldoakhir=nilaisaldoakhir+".$totalharga."
						, hargarata=(nilaisaldoakhir/saldoakhirqty)
						where kodegudang='".$kodegudang."' and periode='".substr($tanggal,0,7)."' and kodebarang='".$rdata['kodebarang']."'";
			}else{
				$ssdbl="insert into ".$dbname.".log_5saldobulanan 
						(kodeorg,kodebarang,saldoakhirqty,hargarata,lastuser,lastupdate,periode,nilaisaldoakhir,kodegudang,qtymasuk,qtykeluar,qtymasukxharga,qtykeluarxharga,saldoawalqty,hargaratasaldoawal,nilaisaldoawal) values
						('".$kodept."','".$rdata['kodebarang']."',".$rdata['jumlah'].",'".$hargasatuan."','".$_SESSION['standard']['userid']."',now(),'".substr($tanggal,0,7)."','".$totalharga."','".$kodegudang."',".$rdata['jumlah'].",'0','".$totalharga."','0','0','0','0')";
			}
			//exit('Warning: '.$ssdbl.' harga='.$hargasatuan);
			if(!mysql_query($ssdbl)){
				//Rollback GR dan GI
		        $sdelgr="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."' or notransaksi='".$nogr."'";
		        if(!mysql_query($sdelgr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgr);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$ssdbl);
			}
			$smsdt="select * from ".$dbname.".log_5masterbarangdt where kodegudang='".$kodegudang."' and kodebarang='".$rdata['kodebarang']."'";
			//exit('Warning: '.$smsdt.' harga='.$hargasatuan);
			$qmsdt=mysql_query($smsdt);
			$rmnum=mysql_num_rows($qmsdt);
			if($rmnum>0){
				// Update Master barang DT
				$smsdt="update ".$dbname.".log_5masterbarangdt set saldoqty='".$qtyakhirgr."', hargalastin='".$hargasatuan."' 
						where kodegudang='".$kodegudang."' and kodebarang='".$rdata['kodebarang']."'";
			}else{
				$smsdt="insert into ".$dbname.".log_5masterbarangdt 
						(kodeorg,kodebarang,saldoqty,hargalastin,hargalastout,lastuser,lastupdate,kodegudang) values
						('".$kodept."','".$rdata['kodebarang']."','".$rdata['jumlah']."','".$hargasatuan."','0','".$_SESSION['standard']['userid']."',now(),'".$kodegudang."')";
			}
			//exit('Warning: '.$smsdt.' harga='.$hargasatuan);
			if(!mysql_query($smsdt)){
				/*
				//Rollback Saldo bulanan
				$ssdbl="update ".$dbname.".log_5saldobulanan set qtykeluar=".$qtykeluar.", qtykeluarharga=".$qtykeluarharga."
						, saldoakhirqty=".$saldoakhirqty.", nilaisaldoakhir=".$nilaisaldoakhir." 
						where kodegudang='".$kodegudang."' and periode='".substr($tanggal,0,7)."' and kodebarang='".$rdata['kodebarang']."'";
		        if(!mysql_query($ssdbl)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$ssdbl);
				}
				*/
				//Rollback GR dan GI
		        $sdelgr="delete from ".$dbname.".log_transaksiht where notransaksi='".$nogi."' or notransaksi='".$nogr."'";
		        if(!mysql_query($sdelgr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdelgr);
				}
				//Rollback Jurnal HT
		        $sdeljr="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			    if(!mysql_query($sdeljr)){
					exit("error: Tidak berhasil".mysql_error($conn)."___".$sdeljr);
				}
				exit("error: data tidak bisa disimpan ".  mysql_error($conn)."___".$smsdt);
			}
		}
		// Update Posting Data
		$spdata="update ".$dbname.".log_brgjadiht set posting=1,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
				where notransaksi='".$param['notransaksi']."'";
		//exit('Warning: '.$smsdt.' harga='.$hargasatuan);
		if(!mysql_query($spdata)){
			exit("error: Posting Tidak berhasil".mysql_error($conn)."___".$spdata);
		}
		//exit("Warning: Posting Berhasil...!");
		break;
}

function generateNoTransaksi($kdGudang,$tgl){
	global $dbname;
	global $conn;
	#no invoice
	$tgldt=explode("-",$tgl);
	$bulan = $tgldt[1];
	$tahun = $tgldt[2];
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	$ql="select notransaksi from ".$dbname.".log_brgjadiht where kodegudang = '".$kdGudang."' and left(tanggal,4) = '".$tahun."'
		and substr(tanggal,6,2) = '".$bulan."' order by notransaksi desc limit 1";
	//exit('Warning: '.$ql);
    $qr=mysql_query($ql) or die('error: '.mysql_error());
	$data = mysql_fetch_object($qr);
	$countNoTransaksi = substr($data->notransaksi,0,4);
	//exit('Warning: '.$countNoTransaksi);
	if($countNoTransaksi==''){
		$countNoTransaksi = 0;
	}
	$noTransaksi = addZero($countNoTransaksi+1,4)."/BJ/".$kdGudang."/".$bulan."/".$tahun;
	return $noTransaksi;
}
