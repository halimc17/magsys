<?php
	session_start();
	require_once('master_validation.php');
	require_once('config/connection.php');
	include_once('lib/nangkoelib.php');
	include('lib/zMysql.php');
	include('lib/zFunction.php');
	include_once('lib/zLib.php');
	$proses=$_POST['proses'];
	$page=$_POST['page'];
	if($page>0)$page=$page; else $page=0;
	$noinvoice=$_POST['noinvoice'];

	switch($proses){
    case'loadData':
		$where = 'a.posting=1';
		if($noinvoice!=''){
			$where.=" and a.noinvoice like '".$noinvoice."%'";
		}
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where.="";
		}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.=" and c.lokasitugas not like '%HO' and a.kodeorg='".$_SESSION['empl']['kodeorganisasi']."'";
		}else{
			$where.=" and c.lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
		}
		$ql2="select count(*) as jmlhrow from ".$dbname.".keu_tagihanht a 
			  left join ".$dbname.".log_5supplier b on b.supplierid=a.kodesupplier
			  left join ".$dbname.".datakaryawan c on c.karyawanid=a.postingby
			  left join ".$dbname.".datakaryawan d on d.karyawanid=a.updateby
			  where ".$where." 
			  order by a.noinvoice desc";
		//exit('Warning: '.$ql2);
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){ $jlhbrs= $jsl->jmlhrow; }

	    $limit=20;
		$offset=$page*$limit;
		$str="select a.*,b.namasupplier,c.namakaryawan as postingname,c.lokasitugas,d.namakaryawan as updatename from ".$dbname.".keu_tagihanht a 
			  left join ".$dbname.".log_5supplier b on b.supplierid=a.kodesupplier
			  left join ".$dbname.".datakaryawan c on c.karyawanid=a.postingby
			  left join ".$dbname.".datakaryawan d on d.karyawanid=a.updateby
			  where ".$where." 
			  order by a.noinvoice desc limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$no=0;
        while($bar=mysql_fetch_object($res)){
			$no+=1;
			echo"<tr class=rowcontent id='tr_".$no."'>
					<td>".$bar->noinvoice."</td>
					<td>".$bar->kodeorg."</td>
					<td>".tanggalnormal($bar->tanggal)."</td>
					<td>".$bar->nopo."</td>
					<td>".$bar->namasupplier."</td>
					<td>".$bar->keterangan."</td>
					<td align=right>".number_format($bar->nilaiinvoice)."</td>
					<td>".$bar->postingname."</td>";
			//if($bar->posting=='1')
				echo"<td align=center><img src=images/application/application_edit.png class=resicon title='Unposting' onclick=unposting('".($bar->noinvoice)."','".$page."');>";
			//else // belum posting / sudah dibayar
			//	echo"<td><img src=images/application/application_delete.png class=resicon  title='Unable to Unpost (Not Posted/Paid)'>";
		}
		echo"<tr>
				<td colspan=11 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>";  
		break;

	case'unposting':
        $sKasBank ="select a.keterangan1,b.posting from ".$dbname.".keu_kasbankdt a
					left join ".$dbname.".keu_kasbankht b on b.notransaksi=a.notransaksi
					where a.keterangan1='".$noinvoice."'";
		$qKasBank=mysql_query($sKasBank) or die(mysql_error($conn));
		$KBP=2;
		while($rKasBank=mysql_fetch_assoc($qKasBank)){
			$KBP=$rKasBank['posting'];
		}
		if($KBP<2){
			if($KBP==1){
				exit('Warning: Tidak bisa diunposting karena sudah ada pembayaran...!');
			}else{
				exit('Warning: Tidak bisa diunposting karena sudah dibuatkan BKU...!');
			}
		}else{
	        $sIns="update ".$dbname.".keu_tagihanht set posting='0', postingby='0' where noinvoice='".$noinvoice."'";
		    if(mysql_query($sIns)){ } else { echo "DB Error : ".$sIns."\n".addslashes(mysql_error($conn)); exit; }
		}
		break;    

	default:
		break;
	}
?>
