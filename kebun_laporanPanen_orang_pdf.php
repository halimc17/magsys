<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/fpdf.php');
    require_once('lib/nangkoelib.php');

    // ambil yang dilempar javascript
    $pt=$_GET['pt'];
    $unit=$_GET['unit'];
    $intiplasma=$_GET['intiplasma'];
    $tgl1=$_GET['tgl1'];
    $tgl2=$_GET['tgl2'];
	
	// olah tanggal
    $tanggal1=explode('-',$tgl1);
    $tanggal2=explode('-',$tgl2);
    $date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
    $tanggalterakhir=date('t', strtotime($date1));

	$tahuntahuntahun=substr($tgl1,0,4);
	$bulanbulanbulan=substr($tgl1,5,2); 
    
// ambil bjr sesuaikan dengan algoritma LBM (lbm_slave_produksi_perblok.php)        
$sProd="select distinct * from ".$dbname.".kebun_spb_bulanan_vw 
        where blok like '".$unit."%' and periode = '".$tahuntahuntahun."-".$bulanbulanbulan."'
        ";
//echo $sProd;
$qProd=mysql_query($sProd) or die(mysql_error($conn));
while($rProd=  mysql_fetch_assoc($qProd))
{
    $blok[$rProd['blok']]=$rProd['blok'];
    $kgwb[$rProd['blok']]=$rProd['nettotimbangan'];
}        
$sJjg="select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from ".$dbname.".kebun_prestasi_vw 
       where kodeorg like '".$unit."%' and left(tanggal,7) = '".$tahuntahuntahun."-".$bulanbulanbulan."' and jurnal=1 group  by kodeorg
       ";
//echo $sJjg;
$qJjg=mysql_query($sJjg) or die(mysql_error($conn));
while($rJjg=  mysql_fetch_assoc($qJjg))
{
    $blok[$rJjg['kodeorg']]=$rJjg['kodeorg'];
    $jjg[$rJjg['kodeorg']]=$rJjg['jjg'];
}
if(!empty($blok))foreach($blok as $blk){
    @$bjrlalu[$blk]=$kgwb[$blk]/$jjg[$blk];
}    
    
    // urutin tanggal
    $tanggal=Array();
    if($tanggal2[1]>$tanggal1[1]){ // beda bulan
        for ($i = $tanggal1[0]; $i <= $tanggalterakhir; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
        for ($i = 1; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal2[2].'-'.$tanggal2[1].'-'.$ii]=$tanggal2[2].'-'.$tanggal2[1].'-'.$ii;
        }
    }else{ // sama bulan
        for ($i = $tanggal1[0]; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
    }
        
    // kamus karyawan --- ga dibatesin, batesin untuk optimize (kalo dah yakin)
    $sdakar="select karyawanid, namakaryawan, tipekaryawan, subbagian from ".$dbname.".datakaryawan";
    $qdakar=mysql_query($sdakar) or die(mysql_error($conn));
    while($rdakar=  mysql_fetch_assoc($qdakar))
    {
        $dakar[$rdakar['karyawanid']]['karyawanid']=$rdakar['karyawanid'];
        $dakar[$rdakar['karyawanid']]['namakaryawan']=$rdakar['namakaryawan'];
        $dakar[$rdakar['karyawanid']]['tipekaryawan']=$rdakar['tipekaryawan'];
        $dakar[$rdakar['karyawanid']]['subbagian']=$rdakar['subbagian'];
    }

    $stikar="select id, tipe from ".$dbname.".sdm_5tipekaryawan";
    $qtikar=mysql_query($stikar) or die(mysql_error($conn));
    while($rtikar=  mysql_fetch_assoc($qtikar))
    {
        $tikar[$rtikar['id']]=$rtikar['tipe'];
    }

    if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty, a.karyawanid  
              from ".$dbname.".kebun_prestasi_vw a
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where c.induk = '".$pt."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%' 
              and a.jurnal=1
              group by a.tanggal,a.karyawanid";
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty, a.karyawanid  
              from ".$dbname.".kebun_prestasi_vw a
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where unit = '".$unit."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%'  
              ".$where."
              group by a.tanggal, a.karyawanid";
    }	

    // isi array
    $jumlahhari=count($tanggal);
    $res=mysql_query($str);
    $dzArr=array();
    if(mysql_num_rows($res)<1){
        $jukol=($jumlahhari*2)+5;
        echo $_SESSION['lang']['tidakditemukan'];
        exit;
    }else{
        while($bar=mysql_fetch_object($res)){
            $dzArr[$bar->karyawanid][$bar->tanggal]=$bar->tanggal;
            $dzArr[$bar->karyawanid]['karyawanid']=$bar->karyawanid;
//            $dzArr[$bar->karyawanid]['tahuntanam']=$bar->tahuntanam;
            $dzArr[$bar->karyawanid][$bar->tanggal.'j']=$bar->jjg;
            $dzArr[$bar->karyawanid][$bar->tanggal.'k']=$bar->berat;
        }	
    } 
    if(!empty($dzArr)) { // list isi data on kodeorg
        foreach($dzArr as $c=>$key) { // list tanggal
            $sort_kodeorg[] = $key['karyawanid'];
//            $sort_tahuntanam[] = $key['tahuntanam'];
        }
        array_multisort($sort_kodeorg, SORT_ASC, $dzArr); // urut kodeorg, terus tahun tanam
    }    
    

    class PDF extends FPDF{
        function Header() {
            global $conn;
            global $dbname;
            global $pt;
            global $unit;
            global $tgl1;
            global $tgl2;
            global $tanggal;

            $sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
            $qAlamat=mysql_query($sAlmat) or die(mysql_error());
            $rAlamat=mysql_fetch_assoc($qAlamat);

            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 11;
            $path='images/logo.jpg';
            $this->Image($path,$this->lMargin,$this->tMargin,0,55);
            $this->SetFont('Arial','B',9);
            $this->SetFillColor(255,255,255);	
            $this->SetX(100);   
            $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
            $this->SetX(100); 		
            $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
            $this->SetX(100); 			
            $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
            $this->Line($this->lMargin,$this->tMargin+($height*4),
            $this->lMargin+$width,$this->tMargin+($height*4));
            $this->Ln();	
            $this->Ln();	
            $this->SetFont('Arial','B',11);
            $this->Cell($width,$height, $_SESSION['lang']['laporanpanen']." per ".$_SESSION['lang']['tanggal'],0,1,'C');	
            $this->Cell($width,$height, $_SESSION['lang']['periode'].":".$tgl1." S/d ".$tgl2 ." ".$_SESSION['lang']['unit'].":" .(!empty($gudang)?$gudang:$_SESSION['lang']['all']),0,1,'C');	
            $this->SetFont('Arial','',8);

            $this->Ln();
            $this->SetFont('Arial','B',7);	
            $this->SetFillColor(220,220,220);

            $this->Cell(2/100*$width,$height,'','TRL',0,'C',1);
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['karyawan'].':','TRL',0,'C',1);
            foreach($tanggal as $tang){
                $ting=explode('-',$tang);
                $qwe=date('D', strtotime($tang));
                if($qwe=='Sun'){
                    $this->SetTextColor(255,0,0);
                }               
                $this->Cell(2.84/100*$width,$height,$ting[2],1,0,'C',1);      
                $this->SetTextColor(0,0,0);
            }
            $this->Cell(4/100*$width,$height,'Total',1,0,'C',1);
            $this->Ln();
            $this->Cell(2/100*$width,$height,'No','RL',0,'C',1);
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['nama'],'RL',0,'C',1);
            foreach($tanggal as $tang){
                $ting=explode('-',$tang);
                $qwe=date('D', strtotime($tang));
                if($qwe=='Sun'){
                    $this->SetTextColor(255,0,0);
                }               
                $this->Cell(2.84/100*$width,$height,'jjg','TRL',0,'C',1);      
                $this->SetTextColor(0,0,0);
            }
            $this->Cell(4/100*$width,$height,'jjg','TRL',0,'C',1);
            $this->Ln();
            $this->Cell(2/100*$width,$height,'','BRL',0,'C',1);
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['tipe'],'BRL',0,'C',1);
            foreach($tanggal as $tang){
                $ting=explode('-',$tang);
                $qwe=date('D', strtotime($tang));
                if($qwe=='Sun'){
                    $this->SetTextColor(255,0,0);
                }               
                $this->Cell(2.84/100*$width,$height,'kg','BRL',0,'C',1);      
                $this->SetTextColor(0,0,0);
            }
            $this->Cell(4/100*$width,$height,'kg','BRL',0,'C',1);
            $this->Ln();
        }
                
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
    }
    $pdf=new PDF('L','pt','A4');
    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 11;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',6);

    // content
    $no=0;
    foreach($dzArr as $arey){ // list isi data on kodeorg
        $no+=1;
        $pdf->Cell(2/100*$width,$height,$no,'TRL',0,'R',1);
        $pdf->Cell(6/100*$width,$height,$dakar[$arey['karyawanid']]['namakaryawan'],'TRL',0,'L',1);
        $totalj=0;
        foreach($tanggal as $tang){ // list tanggal
            $qwe=date('D', strtotime($tang));
            if($qwe=='Sun'){
                $pdf->SetTextColor(255,0,0);
            }else{
                $pdf->SetTextColor(0,0,0);
            }
			setIt($arey[$tang.'j'],0);
			setIt($arey[$tang.'k'],0);
			setIt($arey[$tang.'h'],0);
			setIt($arey[$tang.'b'],0);
			setIt($arey[$tang.'t'],0);
			setIt($bjrlalu[$arey[$tang.'b']],0);
			setIt($total[$tang.'j'],0);
			setIt($total[$tang.'k'],0);
			setIt($total[$tang.'h'],0);
			setIt($totalj,0);
			setIt($totalk,0);
			setIt($totalh,0);
            $pdf->Cell(2.84/100*$width,$height,number_format($arey[$tang.'j']),'TRL',0,'R',1);      
            $total[$tang.'j']+=$arey[$tang.'j']; // tambahin total bawah
            
            $totalj+=$arey[$tang.'j']; // tambahin total kanan
        }
		$pdf->SetTextColor(0,0,0);
        $pdf->Cell(4/100*$width,$height,number_format($totalj),'TRL',0,'R',1);      
        $pdf->Ln();
        $pdf->Cell(2/100*$width,$height,'','BRL',0,'C',1);
        $pdf->Cell(6/100*$width,$height,$dakar[$arey['karyawanid']]['subbagian'].' - '.$tikar[$dakar[$arey['karyawanid']]['tipekaryawan']],'BRL',0,'L',1);
        $totalk=0;
        foreach($tanggal as $tang){ // list tanggal
            $qwe=date('D', strtotime($tang));
            if($qwe=='Sun'){
                $pdf->SetTextColor(255,0,0);
            }else{
                $pdf->SetTextColor(0,0,0);
            }
            $pdf->Cell(2.84/100*$width,$height,number_format($arey[$tang.'k']),'BRL',0,'R',1);      
            $total[$tang.'k']+=$arey[$tang.'k']; // tambahin total bawah
            
            $totalk+=$arey[$tang.'k']; // tambahin total kanan
        }
		$pdf->SetTextColor(0,0,0);
        $pdf->Cell(4/100*$width,$height,number_format($totalk),'BRL',0,'R',1);      
        $pdf->Ln();
    }
    
    // tampilin total
    $pdf->Cell(8/100*$width,$height,'','TRL',0,'C',1);
    $totalj=0;
    foreach($tanggal as $tang){ // list tanggal
        $pdf->Cell(2.84/100*$width,$height,number_format($total[$tang.'j']),'TRL',0,'R',1);      
        $totalj+=$total[$tang.'j']; // tambahin total kanan
    }
    $pdf->Cell(4/100*$width,$height,number_format($totalj),'TRL',0,'R',1);      
    $pdf->Ln();
    // tampilin total
    $pdf->Cell(8/100*$width,$height,'Total','BRL',0,'C',1);
    $totalk=0;
    foreach($tanggal as $tang){ // list tanggal
        $pdf->Cell(2.84/100*$width,$height,number_format($total[$tang.'k']),'BRL',0,'R',1);      
        $totalk+=$total[$tang.'k']; // tambahin total kanan
    }
    $pdf->Cell(4/100*$width,$height,number_format($totalk),'BRL',0,'R',1);      
    $pdf->Ln();              
                
//	if($gudang=='')
//	{
//	
//		$str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
//                    sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty  from ".$dbname.".kebun_prestasi_vw a
//		left join ".$dbname.".organisasi c
//		on substr(a.kodeorg,1,4)=c.kodeorganisasi
//		where c.induk = '".$pt."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." group by a.tanggal,a.kodeorg";
//	}
//	else
//	{
//		$str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
//                    sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty  from ".$dbname.".kebun_prestasi_vw a
//		where unit = '".$gudang."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." group by a.tanggal, a.kodeorg";
//	}  
//
//$res=mysql_query($str);
//while($bar=mysql_fetch_object($res))
//	{
//        $periode=date('Y-m-d H:i:s');
//        $notransaksi=$bar->notransaksi;
//        $tanggal=$bar->tanggal; 
//        $kodeorg 	=$bar->kodeorg;
//                $no+=1;
//                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
//                $pdf->Cell(10/100*$width,$height,tanggalnormal($tanggal),1,0,'L',1);
//                $pdf->Cell(12/100*$width,$height,$kodeorg,1,0,'C',1);
//                $pdf->Cell(8/100*$width,$height,$bar->tahuntanam,1,0,'R',1);	
//                $pdf->Cell(12/100*$width,$height,number_format($bar->jjg,2),1,0,'R',1);	
//                $pdf->Cell(12/100*$width,$height,number_format($bar->berat,2),1,0,'R',1);
//                $pdf->Cell(12/100*$width,$height,number_format($bar->upah,2),1,0,'R',1);	
//                $pdf->Cell(12/100*$width,$height,number_format($bar->premi,2),1,0,'R',1);	
//                $pdf->Cell(12/100*$width,$height,number_format($bar->penalty,2),1,1,'R',1);	
//                
//                $totberat+=$bar->berat;
//                $totUpah+=$bar->upah;
//                $totJjg+=$bar->jjg;
//                $totPremi+=$bar->premi;
//                $totPenalty+=$bar->penalty;
//		
//        }	
//            $pdf->Cell(33/100*$width,$height,"Total",1,0,'R',1);
//            $pdf->Cell(12/100*$width,$height,number_format($totJjg,2),1,0,'R',1);
//            $pdf->Cell(12/100*$width,$height,number_format($totberat,2),1,0,'R',1);
//            $pdf->Cell(12/100*$width,$height,number_format($totUpah,2),1,0,'R',1);
//            $pdf->Cell(12/100*$width,$height,number_format($totPremi,2),1,0,'R',1);
//            $pdf->Cell(12/100*$width,$height,number_format($totPenalty,2),1,1,'R',1);
            
    $pdf->Output();

?>