<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$millcode=checkPostGet('millcode','');
$notransaksi=checkPostGet('notransaksi','');
$tanggal=checkPostGet('tanggal','');
$kodebarang=checkPostGet('kodebarang','');
$beratbersih=checkPostGet('beratbersih','');
$nokendaraan=checkPostGet('nokendaraan','');
$supir=checkPostGet('supir','');
$noba=checkPostGet('noba','');
$notiket=checkPostGet('notiket','');
$alasan=checkPostGet('alasan','');
$ongkoskirim=checkPostGet('ongkoskirim','');
$addedit=checkPostGet('addedit','');
$caripabrik=checkPostGet('caripabrik','');
$caribarang=checkPostGet('caribarang','');
$cariperiode=checkPostGet('cariperiode','');
$carinotiket=checkPostGet('carinotiket','');
switch($proses){
	case'loadData':
		$where="True";
		if($millcode!=''){
			$where.=" and a.millcode='".$millcode."'";
		}
		if($caripabrik!=''){
			$where.=" and a.millcode='".$caripabrik."'";
		}
		if($caribarang!=''){
			$where.=" and a.kodebarang='".$caribarang."'";
		}
		if($cariperiode!=''){
			$where.=" and left(a.tanggal,7)='".$cariperiode."'";
		}
		if($carinotiket!=''){
			$where.=" and a.notiket like '%".$carinotiket."%'";
		}
		$strb="select a.notransaksi from ".$dbname.".pabrik_outspec a 
				where ".$where." 
				order by a.millcode,a.tanggal desc,a.notransaksi,a.kodebarang";
		//exit('Warning: '.$strb);
		$resb=mysql_query($strb);
		$jlhbrs=mysql_num_rows($resb);
		$limit=15;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',0);
			if((($page*$limit)+1)>$jlhbrs)
				$page=$page-1;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$str="select a.*,b.namabarang,e.namacustomer,f.namabarang as komoditi,g.namasupplier as pengangkut
				from ".$dbname.".pabrik_outspec a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				left join ".$dbname.".pmn_kontrakjual d on d.nokontrak=a.nokontrak
				left join ".$dbname.".pmn_4customer e on e.kodecustomer=d.koderekanan
				left join ".$dbname.".log_5masterbarang f on f.kodebarang=a.kodebarangkirim
				left join ".$dbname.".log_5supplier g on g.kodetimbangan=a.customerkirim and a.customerkirim<>''
				where ".$where." 
				order by a.millcode,a.tanggal desc,a.notransaksi,a.kodebarang limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->millcode."</td>
					<td ".$drcl." align=center>".$bar->notransaksi."</td>
					<td ".$drcl." align=center>".substr($bar->tanggal,0,10)."</td>
					<td ".$drcl." align=left>".$bar->namabarang."</td>
					<td ".$drcl." align=right>".number_format($bar->beratbersih,0,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->nokendaraan."</td>
					<td ".$drcl." align=left>".$bar->supir."</td>
					<td ".$drcl." align=left>".$bar->noba."</td>
					<td ".$drcl." align=center>".$bar->notiket."</td>
					<td ".$drcl." align=left>".$bar->alasan."</td>
					<td ".$drcl." align=right>".number_format($bar->ongkoskirim,0,'.',',')."</td>
					<td align=center width='6%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->millcode."','".$bar->notransaksi."','".substr($bar->tanggal,0,10)."','".$bar->kodebarang."','".$bar->beratbersih."','".$bar->nokendaraan."','".$bar->supir."','".$bar->noba."','".$bar->notiket."','".$bar->alasan."','".$bar->ongkoskirim."')\">&nbsp
						<img src=images/pdf.jpg class=resicon title='Print BA' onclick=\"preview_BAPDF('".$bar->millcode."','".$bar->notransaksi."','".$bar->kodebarang."','".$bar->namabarang."','preview',event);\">
					</td>
				</tr>";	
		}
		echo"
		<tr class=rowheader>
			<td colspan=20 align=center>
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>&nbsp
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."&nbsp
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
	break;

	case'delData':
		//$strx="delete from ".$dbname.".log_invbarang 
		//		where kodeorg='".$kodeorg."' and kodebarang='".$kodebarang."' and kodeinventaris='".$kodeinv."'";
		//if(!mysql_query($strx)){
		//	echo " Gagal, ".addslashes(mysql_error($conn));
		//}
	break;

	case'saveData':
		if($addedit=='update'){
			$nokontrak='';
			$nodo='';
			$nosipb='';
			$tglkirim='';
			$komoditi='';
			$customerkirim='';
			$nokendaraankirim='';
			$supirkirim='';
			$jammasukkirim='';
			$beratmasukkirim=0;
			$jamkeluarkirim='';
			$beratkeluarkirim=0;
			$beratbersihkirim=0;
			if($notiket!=''){
				$strm ="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$notiket."'";
				$resm=mysql_query($strm);
				while($barm=mysql_fetch_object($resm)){
					$nokontrak=$barm->nokontrak;
					$nodo=$barm->nodo;
					$nosipb=$barm->nosipb;
					$tglkirim=$barm->tanggal;
					$kodebarangkirim=$barm->kodebarang;
					$customerkirim=$barm->kodecustomer;
					$nokendaraankirim=$barm->nokendaraan;
					$supirkirim=$barm->supir;
					$jammasukkirim=$barm->tanggal.' '.$bar->jammasuk;
					$beratmasukkirim=$barm->beratmasuk;
					$jamkeluarkirim=$barm->tanggal.' '.$bar->jamkeluar;
					$beratkeluarkirim=$barm->beratkeluar;
					$beratbersihkirim=$barm->beratbersih;
				}
			}
			//$strx="update ".$dbname.".pabrik_outspec set millcode='".$millcode."',notransaksi='".$notransaksi."',tanggal='".$tanggal."'
			//		,kodebarang='".$kodebarang."',beratbersih='".$beratbersih."',nokendaraan='".$nokendaraan."',supir='".$supir."',noba='".$noba."'
			//		,notiket='".$notiket."',alasan='".$alasan."',ongkoskirim='".$ongkoskirim."'
			//		,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
			//		where millcode='".$millcode."' and notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
			$strx="update ".$dbname.".pabrik_outspec set noba='".$noba."',notiket='".$notiket."',alasan='".$alasan."',ongkoskirim='".$ongkoskirim."'";
			if($beratbersihkirim>0){
				$strx.=",nokontrak='".$nokontrak."',nodo='".$nodo."',nosipb='".$nosipb."'
					,tglkirim='".$tglkirim."',kodebarangkirim='".$kodebarangkirim."',customerkirim='".$customerkirim."',nokendaraankirim='".$nokendaraankirim."'
					,supirkirim='".$supirkirim."',jammasukkirim='".$jammasukkirim."',beratmasukkirim='".$beratmasukkirim."'
					,jamkeluarkirim='".$jamkeluarkirim."',beratkeluarkirim='".$beratkeluarkirim."',beratbersihkirim='".$beratbersihkirim."'";
			}
			$strx.=",lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where millcode='".$millcode."' and notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
		}else{
			//$strx="insert into ".$dbname.".pabrik_outspec
			//		(millcode,notransaksi,tanggal,kodebarang,beratbersih,nokendaraan,supir,noba,notiket,alasan,ongkoskirim,lastuser,lastdate) values
			//		('".$millcode."','".$notransaksi."','".$tanggal."','".$kodebarang."','".$beratbersih."','".$nokendaraan."','".$supir."','".$noba."'
			//		,'".$notiket."','".$alasan."','".$ongkoskirim."','".$_SESSION['standard']['username']."',now())";
			$strx="select * from ".$dbname.".pabrik_outspec where millcode='".$millcode."' and notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	default:
	break;
}
?>
