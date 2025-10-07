<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=$_POST['proses'];
	$kdPT=$_POST['kdPT'];
	$kdOrg=$_POST['kdOrg'];
	//$periode=$_POST['periode'];
	$tanggal1=tanggalsystem($_POST['tanggal1']);
	$tanggal2=tanggalsystem($_POST['tanggal2']);
	$jenistransaksi=$_POST['jenistransaksi'];
	$stsposting=$_POST['stsposting'];
	if($proses=='')$proses=$_GET['proses'];
	if($kdPT=='')$kdPT=$_GET['kdPT'];
	if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
	//if($periode=='')$periode=$_GET['periode'];
	if($tanggal1=='')$tanggal1=tanggalsystem($_GET['tanggal1']);
	if($tanggal2=='')$tanggal2=tanggalsystem($_GET['tanggal2']);
	if($jenistransaksi=='')$jenistransaksi=$_GET['jenistransaksi'];
	if($stsposting=='')$stsposting=$_GET['stsposting'];

	if($proses=='getUnit'){
		$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and induk='".$kdPT."' order by namaorganisasi asc ";
		//exit('Warning: '.$sOrg);
		$qOrg=mysql_query($sOrg) or die(mysql_error($conn));
		while($rOrg=mysql_fetch_assoc($qOrg)){
			$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
		}
		echo $optOrg;
		exit;
	}

	// get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";	
    $qOrg=mysql_query($sOrg) or die(mysql_error($conn));
    while($rOrg=mysql_fetch_assoc($qOrg)){
		$nmOrg=$rOrg['namaorganisasi'];
        $indukOrg=$rOrg['induk'];
	}
	if(!$nmOrg)$nmOrg=$kdOrg;

	$whr="";
	$whr1="";
	$whr2="";
	$whr3="";
	if($kdOrg!=''){
		$whr.=" and a.kodeorg = '".$kdOrg."' ";
		$whr1.=" and left(a.kodegudang,4) = '".$kdOrg."' ";
		$whr2.=" and left(a.kodeblok,4) = '".$kdOrg."' ";
		$whr3.=" and a.kodeorg = '".$kdOrg."' ";
	}
	if($tanggal1!='' and $tanggal2=='' ){
		$whr.=" and a.tanggal='".$tanggal1."'";
		$whr1.=" and a.tanggal='".$tanggal1."'";
		$whr2.=" and a.tanggal='".$tanggal1."'";
		$whr3.=" and a.tanggal='".$tanggal1."'";
	}
	if($tanggal1=='' and $tanggal2!='' ){
		$whr.=" and a.tanggal='".$tanggal2."'";
		$whr1.=" and a.tanggal='".$tanggal2."'";
		$whr2.=" and a.tanggal='".$tanggal2."'";
		$whr3.=" and a.tanggal='".$tanggal2."'";
	}
	if($tanggal1!='' and $tanggal2!='' ){
		$whr.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$whr1.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$whr2.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
		$whr3.=" and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'";
	}
	if($stsposting!=''){
		$whr.=" and a.posting = '".$stsposting."' ";
		$whr1.=" and a.post = '".$stsposting."' ";
		$whr2.=" and a.statusjurnal = '".$stsposting."' ";
		$whr3.=" and a.jurnal = '".$stsposting."' ";
	}

	$jenistr=array();
	$notrans=array();
	$tanggal=array();
	$kodeorg=array();
	$namaorg=array();
	$tipe_tr=array();
	#ambil data kasbank
	if($jenistransaksi=='' or $jenistransaksi=='kasbank'){
		$str="select 'KASBANK' as jenistr,a.notransaksi,a.tanggal,a.kodeorg,b.namaorganisasi,if(substr(notransaksi,15,2)='BK','Bank Keluar',if(substr(notransaksi,15,2)='BM','Bank Masuk',if(substr(notransaksi,15,2)='KK','Kas Keluar',if(substr(notransaksi,15,2)='KM','Kas Masuk','Lain-lain')))) as tipe_tr
				from ".$dbname.".keu_kasbankht a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				where true ".$whr."
				ORDER BY a.kodeorg,substr(a.notransaksi,15,2),a.notransaksi";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	#ambil data gudang
	if($jenistransaksi=='' or $jenistransaksi=='gudang'){
		$str="select c.namaorganisasi as jenistr,a.notransaksi,a.tanggal,left(a.kodegudang,4) as kodeorg,b.namaorganisasi,if(tipetransaksi='1','Penerimaan Barang',if(tipetransaksi='2','Retur Gudang',if(tipetransaksi='3','Penerimaan Mutasi',if(tipetransaksi='5','Pengeluaran Barang',if(tipetransaksi='6','Retur Supplier',if(tipetransaksi='7','Mutasi Barang','Koreksi')))))) as tipe_tr
				from ".$dbname.".log_transaksiht a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=left(a.kodegudang,4)
				LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodegudang
				where true ".$whr1."
				ORDER BY a.kodegudang,tipetransaksi,a.notransaksi";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	#ambil data baspk
	if($jenistransaksi=='' or $jenistransaksi=='baspk'){
		//$str="select 'BASPK' as jenistr,a.notransaksi,a.tanggal,left(a.kodeblok,4) as kodeorg,b.namaorganisasi,c.namaorganisasi as tipe_tr
		$str="select 'BASPK' as jenistr,a.notransaksi,a.tanggal,left(a.kodeblok,4) as kodeorg,b.namaorganisasi,a.kodekegiatan as tipe_tr
				from ".$dbname.".log_baspk a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=left(a.kodeblok,4)
				LEFT JOIN ".$dbname.".organisasi c on c.kodeorganisasi=a.kodeblok
				where true ".$whr2."
				ORDER BY a.kodeblok,a.notransaksi";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	#ambil data bkm
	if($jenistransaksi=='' or $jenistransaksi=='bkm'){
		$str="select if(tipetransaksi='TB','BKM Pembukaan Lahan',if(tipetransaksi='BBT','BKM Pembibitan',if(tipetransaksi='TBM','BKM Pemelharaan TBM',if(tipetransaksi='TM','BKM Pemelharaan TM',if(tipetransaksi='PNN','BKM Panen','BKM Lain-lain'))))) as jenistr,a.notransaksi,a.tanggal,a.kodeorg,b.namaorganisasi,d.namakaryawan as tipe_tr
				from ".$dbname.".kebun_aktifitas a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				LEFT JOIN ".$dbname.".datakaryawan d on d.karyawanid=if(a.tipetransaksi='PNN',a.nikasisten,a.keranimuat)
				where true ".$whr3."
				ORDER BY a.kodeorg,d.namakaryawan,a.notransaksi";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	#ambil data spatbs
	if($jenistransaksi=='' or $jenistransaksi=='spatbs'){
		$str="select 'SPATBS' as jenistr,a.nospb as notransaksi,a.tanggal,a.kodeorg,b.namaorganisasi,a.penerimatbs as tipe_tr
				from ".$dbname.".kebun_spbht a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				where true ".$whr."
				ORDER BY a.kodeorg,a.penerimatbs,a.nospb";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	#ambil data traksi
	if($jenistransaksi=='' or $jenistransaksi=='traksi'){
		$str="select 'TRAKSI' as jenistr,a.notransaksi,a.tanggal,a.kodeorg,b.namaorganisasi,a.kodevhc as tipe_tr
				from ".$dbname.".vhc_runht a
				LEFT JOIN ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
				where true ".$whr."
				ORDER BY a.kodeorg,a.kodevhc,a.notransaksi";
		//exit('Warning : '.$str);
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			$jenistr[$bar->notransaksi]=$bar->jenistr;
			$notrans[$bar->notransaksi]=$bar->notransaksi;
			$tanggal[$bar->notransaksi]=$bar->tanggal;
			$kodeorg[$bar->notransaksi]=$bar->kodeorg;
			$namaorg[$bar->notransaksi]=$bar->namaorganisasi;
			$tipe_tr[$bar->notransaksi]=$bar->tipe_tr;
		}
	}

	$kolspan=0;
	$brd=0;
	$bgclr="align='center'";
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
		$stream="Laporan_Transaksi_Belum_Posting".$kdOrg."_".$tanggal1."_".$tanggal2;
	}
	$stream="";
	#preview: nampilin header ================================================================================
    $stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr.">No</td>
        <td ".$bgclr.">".$_SESSION['lang']['jenis'].' '.$_SESSION['lang']['transaksi']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['unit']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['unitkerja']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['notransaksi']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['tanggal']."</td>
        <td ".$bgclr.">".$_SESSION['lang']['keterangan']."</td>";
	$stream.="</tr></thead><tbody>";

	# preview: nampilin data ================================================================================
	foreach($notrans as $notransid=>$notransval){
		$no+=1;
		//$stream.="<tr class=rowcontent title='Click untuk melihat detail.' style=\"cursor: pointer\" onclick=showpopup('".$kodeorg[$notransid]."','".$tanggal[$notransid]."','".$notrans[$notransid]."','',event)>
		$stream.="<tr class=rowcontent>
		<td align='center'>".$no."</td>
		<td>".$jenistr[$notransid]."</td>
		<td>".$kodeorg[$notransid]."</td>
		<td>".$namaorg[$notransid]."</td>
		<td>".$notrans[$notransid]."</td>
		<td align='center'>".$tanggal[$notransid]."</td>
		<td>".$tipe_tr[$notransid]."</td>
		</tr>";
	}

	# preview: nampilin sub total ================================================================================
	$stream.="</tbody></table>";

	switch($proses){
        case'preview':
          echo $stream;
			break;

		case 'excel':
            $nop_="Laporan_premi_".($pengawas=='M' ? $_SESSION['lang']['mandorpanen'] : $_SESSION['lang']['keranimuat'])."_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0){
                $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
				echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
				//$handle=fopen("tempExcel/".$nop_.".xls",'w');
				//if(!fwrite($handle,$stream)){
					//echo "<script language=javascript1.2>
					//parent.window.alert('Can't convert to excel format');
					//</script>";
					//exit;
				//}else{
					//echo "<script language=javascript1.2>
					//window.location='tempExcel/".$nop_.".xls';
					//</script>";
				//}
				//fclose($handle);
            }
			break;

		default:
			break;
	}    
?>
