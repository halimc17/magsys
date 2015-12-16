<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
#
//FILE INI SUDAH TIDAK FULL TERPAKAI, GANYA PENGAMBILAN GUDANG SAJA YANG TERPAKAI
#
empty($_POST['proses'])? $proses=isset($_GET['proses'])? $_GET['proses']: '' : $proses=$_POST['proses'];
empty($_POST['unitDt'])? $unitDt=isset($_GET['unitDt'])? $_GET['unitDt']: '' : $unitDt=$_POST['unitDt'];
empty($_POST['gudang'])? $gudang=isset($_GET['gudang'])? $_GET['gudang']: '' : $gudang=$_POST['gudang'];
empty($_POST['periode'])? $periode=isset($_GET['periode'])? $_GET['periode']: '' : $periode=$_POST['periode'];
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmSat=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$where='';
if($proses!='getGudang') {
    if($unitDt=='') {
       exit("Error:Unit Tidak Boleh Kosong");
    }
    if($gudang!='') {
       $where.="and kodegudang like '".$gudang."%'";
    } else {
       exit("Error:Gudang Tidak Boleh Kosong");
    }
    if($periode!='') {
       $where.=" and periode='".$periode."'";
    }
    if($proses=='excel') {
        $tab=" <table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
                <tr>
					<td  bgcolor=#DEDEDE  align=center>No.</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['unit']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['sloc']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['periode']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['namabarang']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['satuan']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['saldoawal']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['masuk']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['keluar']."</td>
					<td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['saldo']."</td>
                </tr>
            </thead><tbody>";
    }
	$sData="select distinct * from ".$dbname.".log_5saldobulanan a 
			where kodegudang!='' ".$where." and (qtymasuk !='0' or qtykeluar !='0' or saldoakhirqty !='0')";
        //echo $sData;
	//print_r($sData);exit;		
	$qData=mysql_query($sData) or die(mysql_error($conn));
	while($rData=mysql_fetch_assoc($qData))
	{
		$dtPeriode[$rData['periode']]=$rData['periode'];
		$lstKdBrg[$rData['kodebarang']]=$rData['kodebarang'];
		$dtKdBarang[$rData['periode']][$rData['kodebarang']]=$rData['kodebarang'];
		$dtAwal[$rData['periode'].$rData['kodebarang']]=$rData['saldoawalqty'];
		$dtMasuk[$rData['periode'].$rData['kodebarang']]=$rData['qtymasuk'];
		$dtKeluar[$rData['periode'].$rData['kodebarang']]=$rData['qtykeluar'];
		$dtAkhir[$rData['periode'].$rData['kodebarang']]=$rData['saldoakhirqty'];
	}
	
	$chekDt=count($dtPeriode);
	if($chekDt==0) {
		exit("Error:Data Kosong");
	}
	
	$no=0;$tab.='';
    foreach($dtPeriode as $dtIsi) {
        foreach($lstKdBrg as $dtBrg) {
            if(!empty($dtKdBarang[$dtIsi][$dtBrg])) {
				$no+=1;
				$tglSkrg=date('Y-m-d H:i:s');
				$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarang2(event,'".$unitDt."','".$dtIsi."','".$gudang."','".$dtKdBarang[$dtIsi][$dtBrg]."','".$optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]]."','".$optNmSat[$dtKdBarang[$dtIsi][$dtBrg]]."');\">";
				$tab.="<td>".$no."</td>";
				$tab.="<td>".$unitDt."</td>";
				$tab.="<td>".$gudang."</td>";
				$tab.="<td>".$dtIsi."</td>";
				$tab.="<td>".$dtKdBarang[$dtIsi][$dtBrg]."</td>";
				$tab.="<td>".$optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]]."</td>";
				$tab.="<td>".$optNmSat[$dtKdBarang[$dtIsi][$dtBrg]]."</td>";
				$tab.="<td align=right class=firsttd>".number_format($dtAwal[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2)."</td>";//saldo awal
				$tab.="<td align=right>".$dtMasuk[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]]."</td>";//saldo masuk
				$tab.="<td align=right  class=firsttd>".$dtKeluar[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]]."</td>";//saldo keluar
				$tab.="<td align=right  class=firsttd>".number_format($dtAkhir[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2)."</td>";//saldo akhir  
            }
        }
    }
}
switch($proses)
{
    case'getGudang':
		$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
				kodeorganisasi like '".$unitDt."%' and tipe like 'GUDANG%' order by namaorganisasi asc";
		$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
		while($rUnit=mysql_fetch_assoc($qUnit))
		{
			$optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['namaorganisasi']."</option>";
		}
		echo $optUnit;
		break;
    case'preview':
        echo $tab;
		break;
	case'excel':
		$tab.="</tbody></table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
		$dte=date("Hms");
		$nop_="lapPersediaanFisikUnit_".$dte;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $tab);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
		break;
	
	case'pdf':
		//=================================================
		class PDF extends FPDF {
			function Header() {
				global $namapt;
				global $pt;
				$this->SetFont('Arial','B',8); 
				$this->Cell(20,5,$namapt,'',1,'L');
				$this->SetFont('Arial','B',12);
				$this->Cell(190,5,strtoupper($_SESSION['lang']['laporanstok']),0,1,'C');
				$this->SetFont('Arial','',8);
				$this->Cell(140,5,' ','',0,'R');
				$this->Cell(15,5,$_SESSION['lang']['tanggal'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,date('d-m-Y H:i'),0,1,'L');
				$this->Cell(140,5,' ','',0,'R');
				$this->Cell(15,5,$_SESSION['lang']['page'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,$this->PageNo(),'',1,'L');
				$this->Cell(140,5,' ','',0,'R');
				$this->Cell(15,5,'User','',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');
				$this->Ln();
				$this->SetFont('Arial','',6);
				$this->Cell(5,5,'No.',1,0,'C');
				$this->Cell(15,5,$_SESSION['lang']['unit'],1,0,'C');
				$this->Cell(20,5,$_SESSION['lang']['sloc'],1,0,'C');
				$this->Cell(17,5,$_SESSION['lang']['periode'],1,0,'C');				
				$this->Cell(18,5,$_SESSION['lang']['kodebarang'],1,0,'C');	
				$this->Cell(45,5,substr($_SESSION['lang']['namabarang'],0,30),1,0,'C');
				$this->Cell(8,5,$_SESSION['lang']['satuan'],1,0,'C');
				$this->Cell(20,5,$_SESSION['lang']['saldoawal'],1,0,'C');		
				$this->Cell(15,5,$_SESSION['lang']['masuk'],1,0,'C');
				$this->Cell(15,5,$_SESSION['lang']['keluar'],1,0,'C');
				$this->Cell(15,5,$_SESSION['lang']['saldo'],1,1,'C');
			}
		}
		//================================
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		$nor=0;
		foreach($dtPeriode as $dtIsi) {
			foreach($lstKdBrg as $dtBrg) {
				if(!empty($dtKdBarang[$dtIsi][$dtBrg])) {
					$nor+=1;
					$pdf->Cell(5,5,$nor,1,0,'C');
					$pdf->Cell(15,5,$unitDt,1,0,'C');
					$pdf->Cell(20,5,$gudang,1,0,'C');
					$pdf->Cell(17,5,$dtIsi,1,0,'C');				
					$pdf->Cell(18,5,$dtKdBarang[$dtIsi][$dtBrg],1,0,'L');	
					$pdf->Cell(45,5,$optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]],1,0,'L');
					$pdf->Cell(8,5,$optNmSat[$dtKdBarang[$dtIsi][$dtBrg]],1,0,'L');
					$pdf->Cell(20,5,number_format($dtAwal[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2,'.',','),1,0,'R');		
					$pdf->Cell(15,5,number_format($dtMasuk[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2,'.',','),1,0,'R');
					$pdf->Cell(15,5,number_format($dtKeluar[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2,'.',','),1,0,'R');
					$pdf->Cell(15,5,number_format($dtAkhir[$dtIsi.$dtKdBarang[$dtIsi][$dtBrg]],2,'.',','),1,1,'R');
				}
			}
		}
		$pdf->Output();
		break;
	case'detailData':
		$pt=$_GET['unitDt'];
		$gudang=$_GET['gudang'];
		$periode=$_GET['periode'];
		$kodebarang=$_GET['kodebarang'];
		$namabarang=$_GET['namabarang'];
		$satuan=$_GET['satuan'];	
		//======================================

		//ambil namapt
		$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
		$namapt='COMPANY NAME';
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$namapt=strtoupper($bar->namaorganisasi);
		}
		//==========================get periode
		$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi
			  where kodeorg='".$gudang."' and periode='".$periode."'";
			$awal='';
			$akhir='';	  
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$awal=$bar->tanggalmulai;
			$akhir=$bar->tanggalsampai;
		}	  
		$sPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
		$qPt=mysql_query($sPt) or die(mysql_error($conn));
		$rPt=mysql_fetch_assoc($qPt);
		//ambil saldo awal===============================
		if($gudang=='') {
			/*$str="select  sum(saldoakhirqty) as sawal,
					sum(nilaisaldoakhir) as sawalrp from 
					".$dbname.".log_5saldobulanan
					where kodebarang='".$kodebarang."'
					and periode='".$periode."'";			
			//=========================================
			//ambil transaksi detail
			$strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,
				  b.tipetransaksi 
				  from ".$dbname.".log_transaksidt a
				  left join ".$dbname.".log_transaksiht b
				  on a.notransaksi=b.notransaksi
				  where kodebarang='".$kodebarang."'
				  and kodept='".$rPt['induk']."'
				  and b.tanggal>='".$awal."'
				  and b.tanggal<='".$akhir."'
				  and b.post=1
				  order by tanggal,waktutransaksi";
					 */
			$str="select  sum(saldoawalqty) as sawal,
			sum(nilaisaldoawal) as sawalrp from 
			".$dbname.".log_5saldobulanan
			where kodebarang='".$kodebarang."'
			and periode='".$periode."'";			
			//=========================================
			//ambil transaksi detail
			$strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,
				b.tipetransaksi 
				from ".$dbname.".log_transaksidt a
				left join ".$dbname.".log_transaksiht b
				on a.notransaksi=b.notransaksi
				where kodebarang='".$kodebarang."'
				and kodept='".$rPt['induk']."'
				and b.tanggal>='".$awal."'
				and b.tanggal<='".$akhir."'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
		} else {
			/*$str="select  sum(saldoakhirqty) as sawal,
					sum(nilaisaldoakhir) as sawalrp from 
					".$dbname.".log_5saldobulanan
					where kodebarang='".$kodebarang."'
					and periode='".$periode."'
					and kodegudang='".$gudang."'";		
			//=========================================
			//ambil transaksi detail
			$strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,
				  b.tipetransaksi
				  from ".$dbname.".log_transaksidt a
				  left join ".$dbname.".log_transaksiht b
				  on a.notransaksi=b.notransaksi
				  where kodebarang='".$kodebarang."'
				  and kodept='".$rPt['induk']."'
				  and kodegudang='".$gudang."'
				  and b.tanggal>='".$awal."'
				  and b.tanggal<='".$akhir."'
				  and b.post=1
				  order by tanggal,waktutransaksi";
					*/
			$str="select  sum(saldoawalqty) as sawal,
			sum(nilaisaldoawal) as sawalrp from 
			".$dbname.".log_5saldobulanan
			where kodebarang='".$kodebarang."'
			and periode='".$periode."'
			and kodegudang='".$gudang."'";
			//=========================================
			//ambil transaksi detail
			$strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,b.notransaksireferensi,
				b.tipetransaksi, b.notransaksi, b.nopo 
				from ".$dbname.".log_transaksidt a
				left join ".$dbname.".log_transaksiht b
				on a.notransaksi=b.notransaksi
				where kodebarang='".$kodebarang."'
				and kodept='".$rPt['induk']."'
				and kodegudang='".$gudang."'
				and b.tanggal>='".$awal."'
				and b.tanggal<='".$akhir."'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
		}
		$sawal=0;
		$sawalrp=0;	
		$hargasawal=0;
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$sawal=$bar->sawal;
			$sawalrp=$bar->sawalrp;
		}
		if($sawal>0)
			$hargasawal=$sawalrp/$sawal;

		//=================================================
		class PDF extends FPDF {
			function Header() {
				global $namapt;
				global $pt;
				global $gudang;
				global $periode;
				global $kodebarang;
				global $namabarang;
				global $satuan;
				$this->SetFont('Arial','B',8); 
				$this->Cell(20,5,$namapt,'',1,'L');
				$this->SetFont('Arial','B',12);
				$this->Cell(190,5,strtoupper($_SESSION['lang']['detailtransaksibarang']),0,1,'C');
				$this->SetFont('Arial','',8);
				
				$this->Cell(35,5,$_SESSION['lang']['unit']."",'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(100,5,$pt,'',0,'L');		
				$this->Cell(15,5,$_SESSION['lang']['tanggal'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,date('d-m-Y H:i'),0,1,'L');
				$this->Cell(35,5,$_SESSION['lang']['namabarang'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(100,5,'['.$kodebarang.']'.$namabarang.'('.$satuan.')','',0,'L');		
				$this->Cell(15,5,$_SESSION['lang']['page'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,$this->PageNo(),'',1,'L');
				$this->Cell(35,5,$_SESSION['lang']['periode'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(100,5,$periode,'',0,'L');		
				$this->Cell(15,5,'User','',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');
				
				$this->SetFont('Arial','',6);
				$this->Cell(5,5,'No.',1,0,'C');		
				$this->Cell(25,5,$_SESSION['lang']['sloc'],1,0,'C');	
				$this->Cell(16,5,$_SESSION['lang']['tanggal'],1,0,'C');
				$this->Cell(10,5,$_SESSION['lang']['tipe'],1,0,'C');						
				$this->Cell(30,5,$_SESSION['lang']['nopo'],1,0,'C');						
				$this->Cell(30,5,$_SESSION['lang']['notransaksi'],1,0,'C');						
				$this->Cell(20,5,$_SESSION['lang']['saldoawal'],1,0,'C');
				$this->Cell(20,5,$_SESSION['lang']['masuk'],1,0,'C');		
				$this->Cell(20,5,$_SESSION['lang']['keluar'],1,0,'C');
				$this->Cell(20,5,$_SESSION['lang']['saldo'],1,1,'C');
			}
		}
		//================================
		$kamustipe[0]='Koreksi';
		$kamustipe[1]='Penerimaan';
		$kamustipe[2]='Pengembalian Pengeluaran';
		$kamustipe[3]='Penerimaan Mutasi';
		$kamustipe[4]='';
		$kamustipe[5]='Pengeluaran';
		$kamustipe[6]='Pengembalian Penerimaan';
		$kamustipe[7]='Pengeluaran Mutasi';		
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		//print_r($strx);exit;
		$resx=mysql_query($strx);
		$no=0;
		$saldo=$sawal;
		$masuk=0;
		$keluar=0;
		while($barx=mysql_fetch_object($resx)) {
			$no+=1;
			if($barx->tipetransaksi<5) {
				$saldo=$saldo+$barx->jumlah;
				$masuk=$barx->jumlah;
				$keluar=0;
			} else {
				$saldo=$saldo-$barx->jumlah;
				$keluar=$barx->jumlah;
				$masuk=0;
			}
			
			if($barx->tipetransaksi == 1){
			  $v_no_po = $barx->nopo;
			  $v_no_trans = $barx->notransaksi;
			}else{
			  $v_no_po = ' ';
			  $v_no_trans = $barx->notransaksi;
			}
			/*DATA*/
			$pdf->Cell(5,5,$no,0,0,'C');		
			$pdf->Cell(25,5,$barx->kodegudang,0,0,'C');	
			$pdf->Cell(16,5,tanggalnormal($barx->tanggal),0,0,'C');
			$pdf->Cell(10,5,$kamustipe[$barx->tipetransaksi],0,0,'C');						
			$pdf->Cell(30,5,$v_no_po,0,0,'C');						
			$pdf->Cell(30,5,$v_no_trans,0,0,'C');						
			$pdf->Cell(20,5,number_format($sawal,2,'.',','),0,0,'R');
			$pdf->Cell(20,5,number_format($masuk,2,'.',','),0,0,'R');		
			$pdf->Cell(20,5,number_format($keluar,2,'.',','),0,0,'R');
			$pdf->Cell(20,5,number_format($saldo,2,'.',','),0,1,'R');
                        $pdf->SetX(108);
                        $pdf->Cell(10,10,'BKM : '.$barx->notransaksireferensi,0,1,'C');
			$sawal=$saldo;		
		}
		$pdf->Output();	
		break;
    default:
		break;
}