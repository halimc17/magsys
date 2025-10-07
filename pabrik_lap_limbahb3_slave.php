<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	require_once('lib/fpdf.php');

	$proses=checkPostGet('proses','');
	$kodeorg=checkPostGet('kdOrg','');
	$periode=checkPostGet('periode','');

	if($proses=='preview' or $proses=='excel'){
		if($kodeorg==''){
			exit('Warning : Pabrik tidak boleh kosong...!');
		}
	}
	#Filter parameter where 
	$where="True";
	if($kodeorg!=''){
		$where.=" and a.kodeorg = '".$kodeorg."'";
	}
	if($periode!=''){
		$where.=" and a.tanggal like '".$periode."%'";
	}
	$optInduk=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$kodept=$optInduk[$kodeorg];
	$namapt=$optNm[$kodept];
	if(strlen($periode)==4){
		$tglawal=$periode.'-01-01';
		$tglakhir=$periode.'-12-31';
	}else{
		$tglawal=$periode.'-01';
		$tglakhir=date('Y-m-t',strtotime($tglawal));
	}
	//exit('Warning: '.strlen($periode));
	#preview: nampilin header ================================================================================
	$bgclr="align='center'";
	$brd=0;
	if($proses=='excel'){
		$brd=1;
		$bgclr="bgcolor='#DEDEDE' align='center'";
		$stream.="<table cellspacing='1' border='".$brd."' class='sortable'>
			<tr>
				<td rowspan=4 colspan=2><img src='images/logo.jpg' style='width:80px;height:80px;'></td>
				<td rowspan=4 colspan=9 align='center' style='vertical-align:center'><h2>".$namapt."</h2></td>
				<td>Doc.</td>
				<td>:</td>
				<td colspan=2 align='left'>MHS-10-FR/03</td>
			</tr>
				<td>rev.</td>
				<td>:</td>
				<td colspan=2 align='left'>00</td>
			<tr>
				<td>Issued Date</td>
				<td>:</td>
				<td colspan=2 align='left'>".$tglawal."</td>
			</tr>
			<tr>
				<td>Page</td>
				<td>:</td>
				<td colspan=2 align='left'>1 of 1</td>
			</tr>
				<td rowspan=2 colspan=15 align='center' style='vertical-align:center'><h3>LEMBAR DATA MASUK DAN KELUARNYA LIMBAH BAHAN BERBAHAYA DAN BERACUN (B3)</h3></td>
			</tr>
			<tr>
		";
	}else{
		$stream.="<table cellspacing='1' border='".$brd."' class='sortable'>";
	}
    $stream.="
        <thead class=rowheader>
        <tr>
			<td colspan=7 ".$bgclr.">MASUKNYA LIMBAH B3 KE TEMPAT PENYIMPANAN</td>
			<td ".$bgclr.">&nbsp</td>
			<td colspan=4 ".$bgclr.">KELUARNYA LIMBAH B3 DARI TEMPAT PENYIMPANAN/td>
			<td ".$bgclr.">&nbsp</td>
			<td colspan=2 ".$bgclr.">SISA</td>
        </tr>
        <tr>
			<td style='width:040px' ".$bgclr.">No</td>
			<td style='width:465px' ".$bgclr.">Jenis Limbah B3 Masuk</td>
			<td style='width:145px' ".$bgclr.">Tanggal Masuk Limbah B3</td>
			<td style='width:455px' ".$bgclr.">Sumber Limbah B3</td>
			<td style='width:175px' ".$bgclr.">Jumlah Limbah Oli  Masuk (ton/kg)</td>
			<td style='width:175px' ".$bgclr.">Jumlah Limbah Filter Masuk (pcs)</td>
			<td style='width:175px' ".$bgclr.">Maksimal Waktu Penyimpanan (hr)</td>
			<td style='width:010px' ".$bgclr.">&nbsp</td>
			<td style='width:145px' ".$bgclr.">Tanggal Keluar Limbah B3</td>
			<td style='width:175px' ".$bgclr.">Jumlah Limbah Oli Keluar (ton/kg)</td>
			<td style='width:175px' ".$bgclr.">Jumlah Limbah Filter Keluar (pcs)</td>
			<td style='width:450px' ".$bgclr.">Bukti No. Dokumen</td>
			<td style='width:010px' ".$bgclr.">&nbsp</td>
			<td style='width:135px' ".$bgclr.">Sisa Oli di Temp. Penyimpanan (ton/kg)</td>
			<td style='width:135px' ".$bgclr.">Sisa Filter di Temp. Penyimpanan (pcs)</td>
        </tr>
        <tr>
			<td ".$bgclr.">(A)</td>
			<td ".$bgclr.">(B)</td>
			<td ".$bgclr.">(C)</td>
			<td ".$bgclr.">(D)</td>
			<td ".$bgclr.">(E)</td>
			<td ".$bgclr.">(F)</td>
			<td ".$bgclr.">(G)</td>
			<td ".$bgclr.">&nbsp</td>
			<td ".$bgclr.">(H)</td>
			<td ".$bgclr.">(I)</td>
			<td ".$bgclr.">(J)</td>
			<td ".$bgclr.">(K)</td>
			<td ".$bgclr.">&nbsp</td>
			<td ".$bgclr.">(L)</td>
			<td ".$bgclr.">(M)</td>
        </tr>
		</thead><tbody>";

	#ambil data Summary Limbah B3 
	$str="select a.kodeorg
			,sum(if((left(a.kodebarang,2)='35' or left(a.kodebarang,2)='33') and a.tanggal<'".$tglawal."',a.qtymasuk-a.qtykeluar,0)) as lalu_oli
			,sum(if((left(a.kodebarang,2)='34') and a.tanggal<'".$tglawal."',a.qtymasuk-a.qtykeluar,0)) as lalu_filter
			,sum(if((left(a.kodebarang,2)='35' or left(a.kodebarang,2)='33') and a.tanggal like '".$periode."%',a.qtymasuk,0)) as masuk_oli
			,sum(if((left(a.kodebarang,2)='34') and a.tanggal like '".$periode."%',a.qtymasuk,0)) as masuk_filter
			,sum(if((left(a.kodebarang,2)='35' or left(a.kodebarang,2)='33') and a.tanggal like '".$periode."%',a.qtykeluar,0)) as keluar_oli
			,sum(if((left(a.kodebarang,2)='34') and a.tanggal like '".$periode."%',a.qtykeluar,0)) as keluar_filter
			from ".$dbname.".pabrik_limbahb3 a
			where a.kodeorg='".$kodeorg."' and a.tanggal <= '".$tglakhir."'
			";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$rnum=mysql_num_rows($res);
	if($rnum==0){
		exit('Warning: Data not found...!');
	}
	while($bar=mysql_fetch_object($res)){
		$masuk_oli=$bar->masuk_oli;
		$masuk_filter=$bar->masuk_filter;
		$keluar_oli=$bar->keluar_oli;
		$keluar_filter=$bar->keluar_filter;
		$saldo_oli=$bar->lalu_oli+$bar->masuk_oli-$bar->keluar_oli;
		$saldo_filter=$bar->lalu_filter+$bar->masuk_filter-$bar->keluar_filter;
	}

	#ambil data Summary Limbah B3 
	$str="select a.*,if(a.kodemesin='GUDANGB3','Gudang B3',c.namaorganisasi) as namaorganisasi,d.namabarang from ".$dbname.".pabrik_limbahb3 a 
			left join ".$dbname.".organisasi c on c.kodeorganisasi=a.kodemesin
			left join ".$dbname.".log_5masterbarang d on d.kodebarang=a.kodebarang
			where ".$where." 
			order by a.kodeorg,a.tanggal,a.kodemesin,a.kodebarang";
	//exit('Warning: '.$str);
	$res=mysql_query($str);
	$no=0;
	while($bar=mysql_fetch_object($res)){
		$no+=1;
		$stream.="<tr class=rowcontent>
				<td align='center'>".$no."</td>
				<td align='left'>".$bar->namabarang."</td>
				<td align='center'>".tanggalnormal($bar->tanggal)."</td>
				<td align='left'>".$bar->namaorganisasi."</td>";
		if(substr($bar->kodebarang,0,2)=='34'){
			$stream.="<td align='right'>".number_format(0,2)."</td>";
			$stream.="<td align='right'>".number_format($bar->qtymasuk,0)."</td>";
		}else{
			$stream.="<td align='right'>".number_format($bar->qtymasuk,2)."</td>";
			$stream.="<td align='right'>".number_format(0,0)."</td>";
		}
		$stream.="<td align='right'></td>
				<td align='right'></td>";
		$stream.="<td align='center'>".tanggalnormal($bar->tanggal)."</td>";
		if(substr($bar->kodebarang,0,2)=='34'){
			$stream.="<td align='right'>".number_format(0,2)."</td>";
			$stream.="<td align='right'>".number_format($bar->qtykeluar,0)."</td>";
		}else{
			$stream.="<td align='right'>".number_format($bar->qtykeluar,2)."</td>";
			$stream.="<td align='right'>".number_format(0,0)."</td>";
		}
		$stream.="<td align='left'>".$bar->keterangan."</td>
				<td align='right'></td>";
		if($no==1){
			$stream.="<td align='right'>".number_format($saldo_oli,2)."</td>";
			$stream.="<td align='right'>".number_format($saldo_filter,0)."</td>";
		}else{
			$stream.="<td align='right'></td>";
			$stream.="<td align='right'></td>";
		}
	}
		$stream.="<tr class=rowcontent>
				<td align='center'></td>
				<td align='center'>Total</td>
				<td align='center'></td>
				<td align='left'></td>
				<td align='right'>".number_format($masuk_oli,2)."</td>
				<td align='right'>".number_format($masuk_filter,0)."</td>
				<td align='right'></td>
				<td align='right'></td>
				<td align='right'></td>
				<td align='right'>".number_format($keluar_oli,2)."</td>
				<td align='right'>".number_format($keluar_filter,0)."</td>
				<td align='right'></td>
				<td align='right'></td>
				<td align='right'>".number_format($saldo_oli,2)."</td>
				<td align='right'>".number_format($saldo_filter,0)."</td>
				</tr>";
	if($proses=='excel'){
		$stream.="
		</tr>
			<td colspan=15 style='font-size:20;border:none;'></td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>Keterangan :</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(A) : Nomor urut dari LB3 yang masuk</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(B) : Jenis  LB3 yang masuk seperti : oli bekas, majun, accu bekas, dll.</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(C) : Tanggal masuk LB3 dari departemen/bagian terkait  ke tempat penyimpanan sementara</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(D) : Asal dari LB3 yang masuk seperti : departemen/bagian</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(E) : Jumlah Oli dari LB3 yang masuk ke TPS (ton atau kg)</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(F) : Jumlah Filter dari LB3 yang masuk ke TPS (ton atau kg)</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(G) : Maksimal waktu penyimpanan LB3 di TPS (Hari)</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(H) : Tanggal keluar LB3 dari TPS ke pihak ketiga</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(I) : Jumlah Oli dari LB3 yang keluar ke pihak ketiga (ton atau kg)</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(J) : Jumlah Filter dari LB3 yang keluar ke pihak ketiga (ton atau kg)</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(K) : Bukti nomor dokumen pengeluaran limbah</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(L) : Sisa Oli LB3 yang masih ada di tempat penyimpanan sementara</td>
		</tr>
		</tr>
			<td colspan=15 style='font-size:20;border:none;'>(M) : Sisa Filter LB3 yang masih ada di tempat penyimpanan sementara</td>
		</tr>
		";
	}
	$stream.="</tbody></table>";
	switch($proses){
        case'preview':
			echo $stream;
        break;
        case 'excel':
			$judul='Limbah_B3';
            if(strlen($stream)>0){
				//$stream='<h2>'.$namapt.'<br>'.$judul.'</h2>'.$stream;
			    //$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	            $nop_=$judul.'_'.date("YmdHis");
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					 }	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
            }
		break;

		default:
		break;
	}    
?>
