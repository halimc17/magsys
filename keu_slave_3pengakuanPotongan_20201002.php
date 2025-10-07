<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
#kamus akun
if($_SESSION['language']=='EN'){
    $zz='namaakun1 as namaakun';
}else{
    $zz='namaakun';
}
$str="select noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun)=7 order by namaakun";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $arrAkun[$bar->noakun]=$bar->namaakun;
}
#kamus komponen
$sAkun="select  id,name from ".$dbname.".sdm_ho_component where plus=0 order by name";
$qAkun=mysql_query($sAkun) or die(mysql_error($conn));
while($rAkun=mysql_fetch_assoc($qAkun))
{
    $namakomponen[$rAkun['id']]=$rAkun['name'];
}

#ambil  noakun debet dan kredit dari setup keu_5pegakuanpotongan
$str="select * from ".$dbname.".keu_5pengakuanpotongan order by idkomponen";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $debet[$bar->idkomponen]=$bar->noakundebet;
    $kredit[$bar->idkomponen]=$bar->noakunkredit;
    $kelompok[$bar->idkomponen]=substr($bar->noakunkredit,0,3);#ini juga tambahan untuk blok
	
    if($bar->noakundebet=='' or $bar->noakundebet=='')
    {
        exit(' Error: Setup account number debet/kredit for component '.$bar->idkomponen.' not defined');
    }
}
#==========bahan dasar pengambilan blok TBM,TM,PNN
#perawatan:
$str="select distinct karyawanid,kodeorg,left(kodekegiatan,3) as kelompok from ".$dbname.".kebun_kehadiran_vw
      where kodekegiatan like '126%' or kodekegiatan like '128%' or kodekegiatan like '621%'
      and tanggal like '".$_POST['periode']."%' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $blok[$bar->karyawanid][$bar->kelompok]=$bar->kodeorg;
}
$str="select distinct karyawanid,kodeorg from ".$dbname.".kebun_prestasi_vw
      where tanggal like '".$_POST['periode']."%' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $blok[$bar->karyawanid]['611']=$bar->kodeorg;
}

#===================end pengambilan blok

$tanggal=  str_replace("-", "",$_POST['periode'])."28";
                 
$str=" select a.idkomponen,a.karyawanid,a.jumlah,b.namakaryawan from ".$dbname.".sdm_gaji a
           left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
           and a.periodegaji='".$_POST['periode']."' and a.idkomponen in (select idkomponen from ".$dbname.".keu_5pengakuanpotongan)";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
	if(!isset($total[$bar->idkomponen])) $total[$bar->idkomponen]=0;
    $total[$bar->idkomponen]+=$bar->jumlah;
    $nama[$bar->karyawanid]=$bar->namakaryawan;
    $rinci[$bar->idkomponen][$bar->karyawanid]=$bar->jumlah;
}
#penambahan gapok untuk perhitungan jamsostek porsi perusahaan
$strGapok=" select idkomponen,karyawanid,jumlah from ".$dbname.".sdm_gaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
           and periodegaji='".$_POST['periode']."' and idkomponen=1";
$resGapok=mysql_query($strGapok);
while($bar=mysql_fetch_object($resGapok)){
	$dtGapok[$bar->karyawanid]=$bar->jumlah;
}
#pengambilan persen porsi perusahaan
#jamhari 10-april-2015 penambahan jurnal porsi perusahaan
$persenJamsostek=array();
$loksi='PABRIK';
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$loksi='KEBUN';
}
$sPersn="select jenisbpjs,bebanperusahaan from ".$dbname.".sdm_5bpjs where lokasibpjs='".$loksi."'";
$qPersn=mysql_query($sPersn) or die(mysql_error($conn));
while($rPersn=mysql_fetch_assoc($qPersn)){
	if($rPersn['jenisbpjs']=='kesehatan'){
		$persenJamsostek[44]=$rPersn['bebanperusahaan'];		
		$awlJrn[44]='002';
		$ketPersn[44]=strtoupper($rPersn['jenisbpjs']);
	}else{
		$persenJamsostek[3]=$rPersn['bebanperusahaan'];
		$awlJrn[3]='001';
		$ketPersn[3]=strtoupper($rPersn['jenisbpjs']);
	}
	
}

$jamPrhsn=0;

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

if(empty($total))
    exit('Error: No salary data found');
elseif(isset($_POST['method']) and $_POST['method']=='post'){
	#periksa periode akuntansi
	$str="select * from ".$dbname.".setup_periodeakuntansi where 
		 kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=0 and periode='".$_POST['periode']."'";
    $res=mysql_query($str);
    if(mysql_num_rows($res)<1)
    {
        exit("Error: Accounting has closed transaction of  ".$_POST['periode']);
    }   
	foreach($total as $komponen =>$ttl)
	{
		#bersihkan detail
		$dataRes['detail']='';
		$dataRes['header']='';
		$totalJamsostek=0;
		$nojurnal2="";//untuk jurnal jamsostek porsi PT
		#buat nourut
		$noUrut=0;                
		$noUrut++;
		
		#setup nojurnal
		$nojurnal=$tanggal."/".$_SESSION['empl']['lokasitugas']."/POT/".$komponen;
		
		#======================== /Nomor Jurnal ============================
		# Prep Header
		$dataRes['header'][]  = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>'POT',
			'tanggal'=>$tanggal,
			'tanggalentry'=>date('Ymd'),
			'posting'=>1,
			'totaldebet'=>$ttl,
			'totalkredit'=>-1*$ttl,
			'amountkoreksi'=>'0',
			'noreferensi'=>'ALK_POT:'.$komponen,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		# Data Detail
		# Debet
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggal,
			'nourut'=>$noUrut,
			'noakun'=>$debet[$komponen],
			'keterangan'=> $namakomponen[$komponen],
			'jumlah'=>$ttl,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$_SESSION['empl']['lokasitugas'],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>'ALK_POT:'.$komponen,
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>'',
			'revisi'=>'0',
		   'kodesegment'=>$defSegment
		);
		
		foreach($rinci[$komponen] as $karid =>$jlhperorang) {
			$noUrut++;
		#tambahan untuk kode blok------------	
			if(isset($blok[$karid][$kelompok[$komponen]])){
			    $kodeblok=$blok[$karid][$kelompok[$komponen]];
			}else{
			  $kodeblok=''; 
			}	
		#========end tambahan kode blok	
			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>$noUrut,
				'noakun'=>$kredit[$komponen],
				'keterangan'=> $namakomponen[$komponen].": ".$nama[$karid],
				'jumlah'=>-1*$jlhperorang,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>$karid,
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'ALK_POT:'.$komponen,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>$kodeblok,
				'revisi'=>'0',
			   'kodesegment'=>$defSegment
			);

			#jika potongan jamsoste ditambahkan perhitungan jamsostek perusahan per kary
			if(($komponen==3)||($komponen==44)){
				@$jamPrhsn=$dtGapok[$karid]*$persenJamsostek[$komponen]/100;
				$totalJamsostek+=$jamPrhsn;
			}
		}
		if(($komponen==3)||($komponen==44)){
			$noUrut2=0;
			$noUrut2++;
			if($komponen==44){
				if(substr($_SESSION['empl']['lokasitugas'],3,1)=='M'){
					$sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMKM'";
				}else{
					$sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMKP'";
				}
			}else{
				if(substr($_SESSION['empl']['lokasitugas'],3,1)=='M'){
					$sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMJM'";
				}else{
					$sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMJP'";
				}
			}
			$qJrnid=mysql_query($sJrnid) or die(mysql_error($conn));
			$rJrnid=mysql_fetch_assoc($qJrnid);
					$nojurnal2=$tanggal."/".$_SESSION['empl']['lokasitugas']."/".$rJrnid['jurnalid']."/".$awlJrn[$komponen];
		
					#======================== /Nomor Jurnal ============================
					# Prep Header
					$dataRes['header'][] = array(
						'nojurnal'=>$nojurnal2,
						'kodejurnal'=>$rJrnid['jurnalid'],
						'tanggal'=>$tanggal,
						'tanggalentry'=>date('Ymd'),
						'posting'=>1,
						'totaldebet'=>$totalJamsostek,
						'totalkredit'=>-1*$totalJamsostek,
						'amountkoreksi'=>'0',
						'noreferensi'=>'ALK_POT:'.$komponen,
						'autojurnal'=>'1',
						'matauang'=>'IDR',
						'kurs'=>'1',
						'revisi'=>'0'
					); 

					# Data Detail
					# Debet
					if(substr($_SESSION['empl']['lokasitugas'],3,1)=='E'){
						if($komponen==44){
							$bpjs="bpjs";
						}else{
							$bpjs="jms";
						}
						$strBPJS="select sum(f.upah) as totalupah from (select a.notransaksi,a.tipetransaksi,a.tanggal,a.kodeorg as divisi,if(a.tipetransaksi='PNN',b.nik,c.nik) as nik ,b.kodeorg as blok,if(a.tipetransaksi='PNN','611010101',b.kodekegiatan) as kodekegiatan ,if(a.tipetransaksi='PNN',b.upahkerja+b.upahpremi,c.umr+c.insentif) as upah from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi where a.tanggal like '".$_POST['periode']."%' and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."') f where f.nik in (select j.karyawanid from ".$dbname.".datakaryawan j where j.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and j.".$bpjs."<>'')";
						$resBPJS=mysql_query($strBPJS);
						$totalupah=0;
						while($barBPJS=mysql_fetch_object($resBPJS)){
							$totalupah=$barBPJS->totalupah;
						}
						if($totalupah>0){
							$strBPJS="select f.kodekegiatan,f.blok,sum(f.upah) as upah from (select a.notransaksi,a.tipetransaksi,a.tanggal,a.kodeorg as divisi,if(a.tipetransaksi='PNN',b.nik,c.nik) as nik ,b.kodeorg as blok,if(a.tipetransaksi='PNN','611010101',b.kodekegiatan) as kodekegiatan ,if(a.tipetransaksi='PNN',b.upahkerja+b.upahpremi,c.umr+c.insentif) as upah from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi where a.tanggal like '".$_POST['periode']."%' and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."') f where f.nik in (select j.karyawanid from ".$dbname.".datakaryawan j where j.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and j.".$bpjs."<>'') GROUP BY f.kodekegiatan,f.blok";
							$resBPJS=mysql_query($strBPJS);
							while($barBPJS=mysql_fetch_object($resBPJS)){
								$dataRes['detail'][] = array(
								'nojurnal'=>$nojurnal2,
								'tanggal'=>$tanggal,
								'nourut'=>$noUrut2,
								'noakun'=>substr($barBPJS->kodekegiatan,0,7),
								'keterangan'=> "BPJS ".$ketPersn[$komponen]." : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
								'jumlah'=>($barBPJS->upah/$totalupah)*$totalJamsostek,
								'matauang'=>'IDR',
								'kurs'=>'1',
								'kodeorg'=>$_SESSION['empl']['lokasitugas'],
								'kodekegiatan'=>$barBPJS->kodekegiatan,
								'kodeasset'=>'',
								'kodebarang'=>'',
								'nik'=>'',
								'kodecustomer'=>'',
								'kodesupplier'=>'',
								'noreferensi'=>'ALK_POT:'.$komponen,
								'noaruskas'=>'',
								'kodevhc'=>'',
								'nodok'=>'',
								'kodeblok'=>$barBPJS->blok,
								'revisi'=>'0',
								'kodesegment'=>$defSegment
								);
								$noUrut2++;
							}
						}else{
							$dataRes['detail'][] = array(
							'nojurnal'=>$nojurnal2,
							'tanggal'=>$tanggal,
							'nourut'=>$noUrut2,
							'noakun'=>$rJrnid['noakundebet'],
							'keterangan'=> "BPJS ".$ketPersn[$komponen]." : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
							'jumlah'=>$totalJamsostek,
							'matauang'=>'IDR',
							'kurs'=>'1',
							'kodeorg'=>$_SESSION['empl']['lokasitugas'],
							'kodekegiatan'=>'',
							'kodeasset'=>'',
							'kodebarang'=>'',
							'nik'=>'',
							'kodecustomer'=>'',
							'kodesupplier'=>'',
							'noreferensi'=>'ALK_POT:'.$komponen,
							'noaruskas'=>'',
							'kodevhc'=>'',
							'nodok'=>'',
							'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment'=>$defSegment
							);
						}
					}else{
						$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal2,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut2,
						'noakun'=>$rJrnid['noakundebet'],
						'keterangan'=> "BPJS ".$ketPersn[$komponen]." : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
						'jumlah'=>$totalJamsostek,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>'ALK_POT:'.$komponen,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment'=>$defSegment
						);
					}
			$noUrut2++;
					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal2,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut2,
						'noakun'=>$rJrnid['noakunkredit'],
						'keterangan'=> "BPJS ".$ketPersn[$komponen]."  : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
						'jumlah'=>-1*$totalJamsostek,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>'ALK_POT:'.$komponen,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
					   'kodesegment'=>$defSegment
					);
		}    
		
		#hapus dulu yang lama
		$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
		mysql_query($RBDet);
		if($nojurnal2!=''){
			$RBDet2 = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal2."'");
			mysql_query($RBDet2);      
		}
		#=====================execute
		foreach($dataRes['header'] as $row) {
			$insHead = insertQuery($dbname,'keu_jurnalht',$row);
			if(!mysql_query($insHead)) {
				$headErr .= 'Insert Header komponen:'.$komponen.' Error : '.mysql_error()."\n";
			}
		}
	
		if(empty($headErr)) {
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname,'keu_jurnaldt',$row);
				if(!mysql_query($insDet)) {
					$detailErr .= "Insert Detail Komponen:".$komponen." Error : ".mysql_error()."\n";
					break;
				}
			}
	
			if($detailErr=='') {
				#do nothing
			} else {
				echo $detailErr;
				# Rollback, Delete Header
				if($nojurnal2!=''){
					$RBDet2 = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal2."'");
					mysql_query($RBDet2);      
				} 
				$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
				if(!mysql_query($RBDet)) {
					echo "Rollback Delete Header Error : ".mysql_error();
					exit;
				}
			}
		} else {
			echo $headErr;
			exit;
		}                   
		#====================end excute   
    }//end for total          
} else {
             echo"<button class=mybutton onclick=prosesPotongan('".$_POST['periode']."') id=btnproses>Process</button>
                  <table class=sortable cellspacing=1 border=0>
                  <thead>
                    <tr class=rowheader>
                    <td>No</td>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>".$_SESSION['lang']['noakun']."</td>
                    <td>".$_SESSION['lang']['namaakun']."</td>                    
                    <td>".$_SESSION['lang']['keterangan']."</td>
                    <td>".$_SESSION['lang']['debet']."</td>
                    <td>".$_SESSION['lang']['kredit']."</td>
                    </tr>
                  </thead>
                  <tbody>";

            foreach($total as $komponen =>$ttl)
             {
              $no=0;                
                $no++;
                echo"<tr class=rowcontent>
                          <td>".$no."</td>
                          <td>".$_POST['periode']."</td> 
                          <td>".$debet[$komponen]."</td>
                          <td>".$arrAkun[$debet[$komponen]]."</td> 
                          <td>".$namakomponen[$komponen]."</td>
                          <td align=right>".number_format($ttl)."</td> 
                          <td align=right>0</td>     
                          </tr>";
                #loop per orangnya:
                    foreach($rinci[$komponen] as $karid =>$jlhperorang){
                        $no++;
                        echo"<tr class=rowcontent>
                                 <td>".$no."</td>
                                 <td>".$_POST['periode']."</td> 
                                 <td>".$kredit[$komponen]."</td>
                                 <td>".$arrAkun[$kredit[$komponen]]."</td> 
                                 <td>".$namakomponen[$komponen].": ".$nama[$karid]."</td>
                                 <td align=right>0</td>                                      
                                 <td align=right>".number_format($jlhperorang)."</td>     
                                 </tr>";                    
                    }
               
             }
             echo"</tbody><tfoot></tfoot></table>";
}
#----------------------------------------------------------------
?>