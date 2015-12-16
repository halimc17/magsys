<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

   $tahunplafon         =checkPostGet('tahunplafon','');
   $periode		=checkPostGet('periode','');
   $jenisbiaya          =checkPostGet('jenisbiaya','');
   $karyawanid          =checkPostGet('karyawanid','');
   $method		=checkPostGet('method','');
   $ygberobat           =checkPostGet('ygberobat','');
   $rs			=checkPostGet('rs','');
   $diagnosa            =checkPostGet('diagnosa','');
   $klaim		=checkPostGet('klaim','');
   $notransaksi         =checkPostGet('notransaksi','');
   $hariistirahat       =checkPostGet('hariistirahat','');
   $tanggal		=checkPostGet('tanggal','');
   $keterangan          =checkPostGet('keterangan','');		   
   $byrs		=checkPostGet('byrs','');
   $byadmin		=checkPostGet('byadmin','');
   $bydr		=checkPostGet('bydr','');
   $byobat		=checkPostGet('byobat','');
   $total		=checkPostGet('total','');
   $bylab		=checkPostGet('bylab','');
   $bebanperusahaan	=checkPostGet('bebanperusahaan','');
   $bebankaryawan	=checkPostGet('bebankaryawan','');
   $bebanjamsostek	=checkPostGet('bebanjamsostek','');   
   $notransaksi		=checkPostGet('notransaksi','');
   $tanggalkwitansi		=checkPostGet('tanggalkwitansi','');
   $tanggalpengajuan		=checkPostGet('tanggalpengajuan','');
   // cari tipekar
   
    $query = "SELECT tipekaryawan, lokasitugas
        FROM ".$dbname.".`datakaryawan` a
        WHERE a.`karyawanid` = '".$karyawanid."'
        ";
    $qDetail=mysql_query($query) or die(mysql_error($conn));
    while($rDetail=mysql_fetch_assoc($qDetail))
    {
        $tipekaryawan=$rDetail['tipekaryawan'];
        $lokasitugas=$rDetail['lokasitugas'];
    }  
            
   if(!isset($_POST['tahunplafon'])){
   	$tahunplafon=date('Y');
   }
   #ngecekan plafon jika RWINP dan rwtinp
   // if($tahunplafon>2013){
       // if(($jenisbiaya=='RWINP')||($jenisbiaya=='RWJLN')){
           // $optNmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
           // $scek="select sum(jlhbayar) as totDibyr from ".$dbname.".sdm_pengobatanht 
                  // where karyawanid='".$karyawanid."' and tahunplafon='".$tahunplafon."'";
           // $qcek=mysql_query($scek) or die(mysql_error($conn));
           // $rcek=mysql_fetch_assoc($qcek);

           // $sgapok="select distinct sum(jumlah) as jmlhgapok from ".$dbname.".sdm_5gajipokok where
                    // karyawanid='".$karyawanid."' and tahun='".$tahunplafon."' and idkomponen in (1,2)";
           // $qgapok=mysql_query($sgapok) or die(mysql_error($conn));
           // $rgapok=mysql_fetch_assoc($qgapok);

           // $sprsn="select distinct rupiah from ".$dbname.".sdm_pengobatanplafond 
                   // where kodejenisbiaya='".$jenisbiaya."'";
           // $qprsn=mysql_query($sprsn) or die(mysql_error($conn));
           // $rprsn=mysql_fetch_assoc($qprsn);
           // $totPlafon=$rprsn['rupiah'];
           // if($rcek['totDibyr']>$totPlafon){
               // if(substr($lokasitugas,2,2)=='HO' or $tipekaryawan=='0'){
                   
               // }else{
               // exit("error: Plafon untuk ".$optNmKar[$karyawanid]." sudah melewati batas!!\n
                            // Plafon= ".number_format($totPlafon,2).", Reimbursement=".number_format($rcek['totDibyr'],2));
               // }
           // }
       // }
   // }
   $kodeorg=substr($_SESSION['empl']['lokasitugas'],0,4);
  if($method=='insert')
  {
  	$str="insert into ".$dbname.".sdm_pengobatanht (	
		  `notransaksi`, `kodeorg`, `karyawanid`,
		  `tahunplafon`, `tanggalkwitansi`, `tanggalpengajuan`, `kodebiaya`, `keterangan`,
		  `rs`, `updateby`, `jasars`,  `jasadr`,
		  `jasalab`, `byobat`, `bypendaftaran`,
		  `ygsakit`, `jlhbayar`, `tanggalbayar`,
		  `totalklaim`, `jlhhariistirahat`,
		  `klaimoleh`, `periode`, `tanggal`, `diagnosa`,
                                          `bebanperusahaan`, `bebankaryawan`, `bebanjamsostek`)
		  values(
		  '".$notransaksi."','".$kodeorg."',".$karyawanid.",
		   ".$tahunplafon.",".tanggalsystem($tanggalkwitansi).",".tanggalsystem($tanggalpengajuan).",'".$jenisbiaya."','".$keterangan."',
		    ".$rs.",".$_SESSION['standard']['userid'].",
			".$byrs.",".$bydr.",".$bylab.",".$byobat.",".$byadmin.",
			".$ygberobat.",0,'0000-00-00',
			".$total.",".$hariistirahat.",
			".$klaim.",'".$periode."',".tanggalsystem($tanggal).",
			".$diagnosa.",".$bebanperusahaan.",".$bebankaryawan.",".$bebanjamsostek."			
		  )";	  
//        exit("error: ".$str);
  }
  else if($method=='del')
  {
  	$str="delete from ".$dbname.".sdm_pengobatanht where notransaksi='".$notransaksi."'";
  }
  else if($method=='update')
  {
    $str="update ".$dbname.".sdm_pengobatanht set karyawanid='".$karyawanid."',tanggalkwitansi='".tanggalsystem($tanggalkwitansi)."',tanggalpengajuan='".tanggalsystem($tanggalpengajuan)."',kodebiaya='".$jenisbiaya."',
          keterangan='".$keterangan."',rs='".$rs."',updateby='".$_SESSION['standard']['userid']."',jasars='".$byrs."',
          jasadr='".$bydr."',jasalab='".$bylab."',byobat='".$byobat."',bypendaftaran='".$byadmin."',
          ygsakit='".$ygberobat."',totalklaim='".$total."',jlhhariistirahat='".$hariistirahat."',
          klaimoleh='".$klaim."',periode='".$periode."',tanggal='".tanggalsystem($tanggal)."',diagnosa='".$diagnosa."',
          bebanperusahaan='".$bebanperusahaan."',bebankaryawan='".$bebankaryawan."',bebanjamsostek='".$bebanjamsostek."'
          where notransaksi='".$notransaksi."'";
//    exit("error: ".$str);
    if(!mysql_query($str))
    {
        echo"Gagal".mysql_error($conn);
    }
  }
  else
  {
  	$str="select 1=1";
  }
	
  
	if(mysql_query($str))
	{
            if($_SESSION['empl']['lokasitugas']=='MJHO'){
                $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag, a.notransaksi as notransaksi,
                      a.karyawanid as karyawanid,a.kodebiaya as kodebiaya,a.keterangan as keterangan,
                      c.lokasitugas as lokasitugas,a.tahunplafon as thnplafon,a.periode as periode,
                      b.id as rs,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
                      a.bypendaftaran as byadmin,a.ygsakit as ygsakit,a.tanggal as tanggal,a.totalklaim as totalklaim,
                      a.jlhhariistirahat as istirahat,a.bebankaryawan as bebankaryawan,a.bebanjamsostek as bebanjamsostek,
                      a.bebanperusahaan as bebanperusahaan,a.diagnosa as diagnosa,a.klaimoleh as klaim
                      from ".$dbname.".sdm_pengobatanht a left join
                      ".$dbname.".sdm_5rs b on a.rs=b.id 
                      left join ".$dbname.".datakaryawan c
                      on a.karyawanid=c.karyawanid
                      left join ".$dbname.".sdm_5diagnosa d
                      on a.diagnosa=d.id
                      where a.periode='".$tahunplafon."'
                      and (c.tipekaryawan in ('0','7','8') or c.alokasi=1)
                      order by a.updatetime desc, a.tanggal desc";
//                and (c.tanggalkeluar = '0000-00-00' or c.tanggalkeluar > '".date("Y-m-d")."') 
            }
            else{
                $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag, a.notransaksi as notransaksi,
                      a.karyawanid as karyawanid,a.kodebiaya as kodebiaya,a.keterangan as keterangan,
                      c.lokasitugas as lokasitugas,a.tahunplafon as thnplafon,a.periode as periode,
                      b.id as rs,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
                      a.bypendaftaran as byadmin,a.ygsakit as ygsakit,a.tanggal as tanggal,a.totalklaim as totalklaim,
                      a.jlhhariistirahat as istirahat,a.bebankaryawan as bebankaryawan,a.bebanjamsostek as bebanjamsostek,
                      a.bebanperusahaan as bebanperusahaan,a.diagnosa as diagnosa,a.klaimoleh as klaim
                      from ".$dbname.".sdm_pengobatanht a left join
                      ".$dbname.".sdm_5rs b on a.rs=b.id 
                      left join ".$dbname.".datakaryawan c
                      on a.karyawanid=c.karyawanid
                      left join ".$dbname.".sdm_5diagnosa d
                      on a.diagnosa=d.id
                      where a.periode='".$tahunplafon."' 
                      and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
                      order by a.updatetime desc, a.tanggal desc";
//                and (c.tanggalkeluar = '0000-00-00' or c.tanggalkeluar > '".date("Y-m-d")."')
            }
                $stream='';
		$res=mysql_query($str)or die(mysql_error()); 
		  $no=0;
		  $regional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
		  $golonganKar = makeOption($dbname,'datakaryawan','karyawanid,kodegolongan');
		  while($bar=mysql_fetch_object($res))
		  {
				$sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$bar2->kodebiaya."' and kodegolongan='".			$golonganKar[$bar2->karyawanid]."' and regional = '".$regional[$bar2->lokasitugas]."'";
				$qPlaf=mysql_query($sPlaf);
				$rPlaf=mysql_fetch_assoc($qPlaf);
				if($rPlaf['satuan']==4){
					$vWhere = " and tahunplafon between '".(($bar->thnplafon)-2)."' and '".$bar->thnplafon."'";
				}else{
					$vWhere = " and tahunplafon='".$bar->thnplafon."'";
				} 

				$sPlaf2="select sum(jlhbayar) as jlhbayar, sum(bebanperusahaan) as bebanperusahaan, kodebiaya from ".$dbname.".sdm_pengobatanht
						  where karyawanid='".$bar->karyawanid."' and kodebiaya='".$bar->kodebiaya."' ".$vWhere." 
						  group by kodebiaya";
				$qPlaf2=mysql_query($sPlaf2);
				$rPlaf2=mysql_fetch_assoc($qPlaf2);
				
				$gaji="select * from ".$dbname.".sdm_5gajipokok where karyawanid = ".$bar->karyawanid."
                   and tahun like ".$bar->thnplafon."";
				$hasil=mysql_query($gaji) or die(mysql_error($conn));
				$row=mysql_fetch_assoc($hasil);
				$jumlahgaji=$row['jumlah'];
				
				if($bar->kodebiaya=='RWJLN'){
					$hasilPlaf=$jumlahgaji-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
				}else if($bar->kodebiaya=='RWINP'){
					$hasilPlaf=$rPlaf['rupiah'];
				}else if($rPlaf['satuan']==4){
					$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
				}else if($rPlaf['satuan']==3){
					$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
				}else{
					if(mysql_num_rows($qPlaf) <= 0){
						$hasilPlaf='0';
					}else{
						if($rPlaf2['jlhbayar'] >= $rPlaf['rupiah']){
							$hasilPlaf='0';
						}else{
							$hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
						}
					}
				}
				
			   $no+=1;
			   echo"<tr class=rowcontent>
			   <td>";
				$sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$bar->kodebiaya."' and kodegolongan='".			$golonganKar[$bar->karyawanid]."' and regional = '".$regional[$bar->lokasitugas]."'";
				$qPlaf=mysql_query($sPlaf);
				$rPlaf=mysql_fetch_assoc($qPlaf);
			   if($bar->posting==0)
			   {
                               $ket=rawurlencode($bar->keterangan);
                               echo"<img src=images/edit.png title='edit' class=resicon onclick=editPengobatan('".$bar->notransaksi."','".$bar->karyawanid."','".$bar->kodebiaya."','".$bar->lokasitugas."','".$bar->thnplafon."','".$bar->periode."','".$bar->rs."','".$bar->byrs."','".$bar->bydr."','".$bar->bylab."','".$bar->byobat."','".$bar->byadmin."','".$bar->ygsakit."','".$bar->diagnosa."','".tanggalnormal($bar->tanggal)."','".$bar->totalklaim."','".$bar->istirahat."','".$bar->bebankaryawan."','".$bar->bebanjamsostek."','".$bar->bebanperusahaan."','".$bar->klaim."','".$ket."','".tanggalnormal($bar->tanggalkwitansi)."','".tanggalnormal($bar->tanggalpengajuan)."','".number_format($hasilPlaf,2)."','".$rPlaf['satuan']."')>";
                               echo"&nbsp<img src=images/close.png title='delete' class=resicon onclick=deletePengobatan('".$bar->notransaksi."')>";
			   }
			     echo"&nbsp<img src=images/zoom.png title='View' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)>";
			   
			   echo"</td><td>".$no."</td>
				  <td>".$bar->notransaksi."</td>
				  <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
				  <td>".tanggalnormal($bar->tanggal)."</td>
				  <td>".$bar->namakaryawan."</td>
				  <td>".$bar->namars."[".$bar->kota."]"."</td>
				  <td>".$bar->kodebiaya."</td>
                                  <td align=right>".number_format($bar->byrs,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->byadmin,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->bylab,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->byobat,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->bydr,2,'.',',')."</td>
								  <td align=right>".number_format($bar->bebanperusahaan,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->bebankaryawan,2,'.',',')."</td>
                                  <td align=right>".number_format($bar->bebanjamsostek,2,'.',',')."</td>                                         
				  <td>".$bar->ketdiag."</td>
						<td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>
						<td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
				  <td>".$bar->keterangan."</td>
				</tr>";  	
		  }			  		
	}
	else
	{
		echo " Error: ".addslashes(mysql_error($conn));
	} 	
?>
