<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if(isTransactionPeriod()) {//check if transaction period is normal
    $tipetransaksi    =$_POST['tipetransaksi'];
    $tanggal	=$_POST['tanggal'];
    $kodebarang =$_POST['kodebarang'];
    $satuan		=$_POST['satuan'];
    $jumlah		=$_POST['jumlah'];
    $kodept		=$_POST['kodept'];
    $gudangx	=$_POST['gudangx'];
    $untukpt	=$_POST['untukpt'];
    $gudang		=$_POST['gudang'];
    $blok       =$_POST['kodeblok'];
	$segment    = !empty($_POST['kodesegment'])? $_POST['kodesegment']: colDefaultValue($dbname,'keu_5segment','kodesegment');
    $notransaksi=$_POST['notransaksi'];
    $user		=$_SESSION['standard']['userid'];
    $hargasatuan=$_POST['hargasatuan'];
    $nopo		=$_POST['nopo'];
    $supplier	=$_POST['supplier'];
    $kodekegiatan	=$_POST['kodekegiatan'];
    $kodemesin	=$_POST['kodemesin'];  
	
	if(!preg_match('/^[0-9]{8}$/',$kodebarang)) {
		exit("Warning: Kode Barang tidak standard");
	}
	
    //periksa apakah sudah pernah mempengaruhi saldo
    $statussaldo=0;
    $str= "select statussaldo from ".$dbname.".log_transaksidt 
        where notransaksi='".$notransaksi."'
            and kodebarang='".$kodebarang."'
            and kodeblok='".$blok."'";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)) {
        $statussaldo=isset($res->statussaldo)? $res->statussaldo: 0;
    }
	
    if($statussaldo>0 and $statussaldo!='') {
        //exit without error
        //dilewati karena sudah membentuk jurnal
        exit(0); 
    } // statussaldo=1
    else
    {	
        //==============periksa apakah sudah tutup buku:
        //unit sendiri
        $periode=$_SESSION['gudang'][$gudang]['tahun']."-".$_SESSION['gudang'][$gudang]['bulan'];
        $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudang, 0,4)."'";
        $res=mysql_query($str);
        $close=0;
        while($bar=mysql_fetch_object($res))
        {
            $close=$bar->tutupbuku;
        }
        if($close=='1')
        {
            //exit (" Error: Keuangan sudah tutup buku");
            exit (" Error: Accounting Period has been closed.");
        }
        //unit tujuan
        if($gudangx!='' and (substr($gudang, 0,4)!=substr($gudangx,0,4)))//jika mutasi dan gudang tujuan ada di unit berbeda
        {
            $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudangx, 0,4)."'";
            $res=mysql_query($str);
            $close=0;
            while($bar=mysql_fetch_object($res))
            {
                $close=$bar->tutupbuku;
            }
            if($close=='1' and $tipetransaksi!='3' )#khusus penerimaan mutasi dikecualikan boleh di jurnal walau pengirim sudah utup bk
            {
                exit (" Error: Receiver Accounting Period has been closed.");
            }           
        }      
        //periksa transaksi yang belum diposting di tanggal sebelumnya:
        $str="select * from ".$dbname.".log_transaksi_vw where kodebarang='".$kodebarang."' and kodegudang='".$gudang."' 
              and tanggal<'".tanggalsystem($tanggal)."' and statussaldo=0";
        $res=mysql_query($str);
        if(mysql_num_rows($res)>0)
        {
//          exit(" Error: Masih ada barang yang sama belum di posting pada tanggal yang lebih kecil.");
          exit(" Error: There is material has not been posted on previous date.");
        }
        #ambil nama barang
        $str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
        $res=mysql_query($str);
        $namabarang='';
        while($bar=mysql_fetch_object($res))
        {
            $namabarang=$bar->namabarang;
        }
        if($namabarang=='')
            $namabarang=$kodebarang;           
            
        if($tipetransaksi=='1')//penerimaan dari supplier=============================================================
        {
            //periksa harga satuan
			if(intval($hargasatuan)==0 or $nopo=='' or $supplier=='')
			{
                //exit(" Error: harga/no.po/supplier tidak ditemukan");
				exit(" Error: price/PO/supplier not found.");
			}
			//generate saldo updater
			//ambil saldo saat ini 
			$nilaitotal=$jumlah*$hargasatuan;
            $cursaldo=0;
            $nilaisaldo=0;
            $qtymasuk=0;
            $qtymasukxharga=0;
            $saldoakhirqty=0;
            $nilaisaldoakhir=0;
            $hargarata=0;
			$str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
				  and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            $res=mysql_query($str);
            if(mysql_numrows($res)<1)//jika belum ada penerimaan sebelumnya
            {
                $newhargarata=$hargasatuan;
                $newqtymasuk=$jumlah;
                $newqtymasukxharga=$nilaitotal;
                $newsaldoakhirqty=$jumlah;
                $newnilaisaldoakhir=$nilaitotal;
                $strupdate="insert into ".$dbname.".log_5saldobulanan (
					kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser,
					periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, 
					qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal)
					values('".$kodept."','".$kodebarang."',".$newqtymasuk.",".$newhargarata.",".$user.",
					'".$periode."',".$newqtymasukxharga.",'".$gudang."',".$newsaldoakhirqty.",0,".$newnilaisaldoakhir.",0,0,0,0)";   
            } else {
                //bentuk harga baru
                while($bar=mysql_fetch_object($res))
                {
                    $cursaldo=$bar->saldoakhirqty;
                    $nilaisaldo=$bar->nilaisaldoakhir;
                    $qtymasuk=$bar->qtymasuk;
                    $qtymasukxharga=$bar->qtymasukxharga;
                    $hargarata=$bar->hargarata; 
                }
                @$newhargarata=($nilaitotal+$nilaisaldo)/($jumlah+$cursaldo);
                $newqtymasuk=$qtymasuk+$jumlah;
                @$newqtymasukxharga=$qtymasukxharga+$nilaitotal;
                $newsaldoakhirqty=$jumlah+$cursaldo;
                $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
                if($newhargarata==0)
                {
                    //exit(" Error: Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
                    exit(" Error: Average price cannot be formed for ".$notransaksi." material code :".$kodebarang);
                }
                else
                {
                    $strupdate="update ".$dbname.".log_5saldobulanan set 
                                       saldoakhirqty=".$newsaldoakhirqty.", hargarata=".$newhargarata.",nilaisaldoakhir=".$newnilaisaldoakhir.",
                                       lastuser=".$user.",qtymasuk=".$newqtymasuk.",qtymasukxharga=".$newqtymasukxharga."
                                       where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";                                      
                }           
            }
            //prepare rollback penerimaan
			$strrollback="update ".$dbname.".log_5saldobulanan set 
				saldoakhirqty=".$cursaldo.", hargarata=".$hargarata.",nilaisaldoakhir=".$nilaisaldo.",
				lastuser=".$user.",qtymasuk=".$qtymasuk.",qtymasukxharga=".$qtymasukxharga."
				where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
			
            //prepare update masterbarangdt
            $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
				kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
				stockbataspesan, stockminimum, lastuser,kodegudang) values(
				'".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",".$newhargarata.",
				0,0,0,".$user.",'".$gudang."'
				)";
			
            $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                hargalastin=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
            //prepare jurnal
            
			#ambil noakun supplier
            $kodekl=substr($supplier,0,4);
            $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->noakun;
            }
			
			#ambil noakun barang
            $klbarang=substr($kodebarang,0,3);
            $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
            $res=mysql_query($str);
            $akunbarang='';
            while($bar=mysql_fetch_object($res))
            {
                $akunbarang=$bar->noakun;
            }   
            if(($akunbarang=='' or $akunspl=='') and ($klbarang<'400' or substr($kodebarang,0,1)=='9'))
            {    
                //exit("Error: Noakun  Noakun barang atau supplier  belum ada untuk transaksi ".$notransaksi); 
                exit("Error: Account no. for material or supplier not available yet for ".$notransaksi); 
            }
			// $nilaitotal=9000000;
			// Cek Nilai Ppn di PO
			$qPO = selectQuery($dbname,'log_poht',"*","nopo='".$nopo."'");
			$resPO = fetchData($qPO);
			if(empty($resPO)) exit("Warning: PO ".$nopo." tidak terdaftar");
			$nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'] * ($nilaitotal / ($resPO[0]['kurs'] * ($resPO[0]['subtotal']-$resPO[0]['nilaidiskon'])));
			// $nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'];
			// exit("warning : \nNilai Total : ".number_format($nilaitotal,2)."\nSubtotal : ".number_format($resPO[0]['subtotal'],2)."\nDiskon : ".number_format($resPO[0]['nilaidiskon'],2)."\nPPN : ".number_format($resPO[0]['ppn'],2)."\nPPN Result : ".number_format($nilaiPpn,2));
			
			
			#proses data
            $kodeJurnal = 'INVM1';
            #======================== Nomor Jurnal =============================
            # Get Journal Counter
//            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' ");
            $tmpKonter = fetchData($queryJ);
            $konter = addZero($tmpKonter[0]['nokounter']+1,3);

            # Transform No Jurnal dari No Transaksi
            $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
            #======================== /Nomor Jurnal ============================

            # Prep Header
			$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodeJurnal,
				'tanggal'=>tanggalsystem($tanggal),
				'tanggalentry'=>date('Ymd'),
				'posting'=>1,
				'totaldebet'=>$nilaitotal,
				'totalkredit'=>-1*$nilaitotal,
				'amountkoreksi'=>'0',
				'noreferensi'=>$notransaksi,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);

			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>tanggalsystem($tanggal),
				'nourut'=>$noUrut,
				'noakun'=>$akunbarang,
				'keterangan'=>'Pembelian barang '.$namabarang.' '.$jumlah." ".$satuan,
				'jumlah'=>$nilaitotal,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($gudang,0,4),
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>$kodebarang,
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$supplier,
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$nopo,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $segment
			);
			$noUrut++;
			
			// Debet PPn
			if($nilaiPpn>0) {
				$qPpn = selectQuery($dbname,'keu_5parameterjurnal',"noakundebet",
									"kodeaplikasi='INV' and jurnalid='PPN'");
				$resPpn = fetchData($qPpn);
				if(empty($resPpn)) exit("Warning: No Akun untuk PPn Penerimaan Barang belum ada");
				
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>tanggalsystem($tanggal),
					'nourut'=>$noUrut,
					'noakun'=>$resPpn[0]['noakundebet'],
					'keterangan'=>'PPn Pembelian barang '.$namabarang.' '.$jumlah." ".$satuan,
					'jumlah'=>$nilaiPpn,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($gudang,0,4),
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$supplier,
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nopo,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $segment
				);
				$noUrut++;
			}

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>tanggalsystem($tanggal),
				'nourut'=>$noUrut,
				'noakun'=>$akunspl,
				'keterangan'=>'Pembelian barang '.$namabarang.' '.$jumlah." ".$satuan,
				'jumlah'=>(-1*$nilaitotal) - $nilaiPpn,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($gudang,0,4),
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>$kodebarang,
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$supplier,
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$nopo,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $segment            
			);
			$noUrut++;
			
			#=========================================                
			$updflagststussaldo="update ".$dbname.". log_transaksidt set statussaldo=1,hargarata=".$newhargarata.",jumlahlalu=".$cursaldo."
			where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";        
			#==================================execute
			if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!='') {   
				$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				if(!mysql_query($insHead)) {
					$headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
				}
				if($headErr=='') {
					$detailErr = '';
					foreach($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname,'keu_jurnaldt',$row);
						if(!mysql_query($insDet)) {
							$detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
							break;
						}
					}
					if($detailErr=='') {
						# Header and Detail inserted
						#>>> Update Kode Jurnal
						$updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
							"kodeorg='".$kodept.
							"' and kodekelompok='".$kodeJurnal."'");
						if(!mysql_query($updJurnal)) {
							echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
							# Rollback if Update Failed
							$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
							if(!mysql_query($RBDet)) {
								echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
								exit;
							}
							exit;
						} else {#berhasil di jurnal
							#proses gudang
							$errGudang='';
							if(!mysql_query($strupdate))
							{
								 echo" Gagal update saldobulanan"; 
									# Rollback, Delete Header
									$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
									if(!mysql_query($RBDet)) {
										echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
										exit;
									}                             
							}
							else
							{
								 #update masterbarangdt
								if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
	                                //$errGudang=" Gagal update masterbarangdt"; 
									$errGudang=" Error update masterbarangdt"; 
								if(mysql_affected_rows()==0)
								{
									if(@!mysql_query($instmaster))
									{
										//$errGudang=" Gagal insert masterbarangdt"; 
									}
								}  
									  
								if($errGudang=='')
								 {
									if(!mysql_query($updflagststussaldo))
									{
	                                    //$errGudang=" Gagal update statussaldo pada masterbarangdt";
										$errGudang=" Error update statussaldo on masterbarangdt";
									}
								}
								if($errGudang!='')//check jika ada error(ini sudah di test)
								{
									#rollback gudang
										echo $errGudang;
										#rollback gudang
										if(!mysql_query($strrollback))
										{
										   echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
										}
										  # Rollback, Delete Header jurnal
											$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
											if(!mysql_query($RBDet)) {
												echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
												exit;
											}
									  exit;      
								}                             
							}  
						}
					} else {
						echo $detailErr;
						# Rollback, Delete Header
						$RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
						if(!mysql_query($RBDet)) {
							echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
							exit;
						}
					}
				} else {
					echo $headErr;
					exit;  
				}
				#============================================================================          
			} else {#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
				#proses gudang
				$errGudang='';
				if(!mysql_query($strupdate))
				{
						//echo" Gagal update saldobulanan";                           
						echo" Error update saldobulanan";                           
				}
				else
				{
                    #update masterbarangdt
					if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
						//$errGudang=" Gagal update masterbarangdt"; 
						$errGudang=" Error update masterbarangdt"; 
					if(mysql_affected_rows()==0)
					{
						if(@!mysql_query($instmaster))
						{
						 //   $errGudang=" Gagal insert masterbarangdt"; 
						}
					}
					
					if($errGudang=='') {
						if(!mysql_query($updflagststussaldo))
						{
							//$errGudang=" Gagal update statussaldo pada masterbarangdt";
							$errGudang=" Error update statussaldo on masterbarangdt";
						}
					}
					if($errGudang!='')//check jika ada error(ini sudah di test)
					{
						#rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
					}
				}
			}
		} // end of tipetransaksi = 1
		
        if($tipetransaksi=='6')//Pengembalian ke supplier=============================================================
        {
            //periksa harga satuan
			if(intval($hargasatuan)==0 or $nopo=='' or $supplier=='')
			{
				exit(" Error: price/PO/supplier not found");
			}
			//generate saldo updater
			//ambil saldo saat ini 
			$nilaitotal=$jumlah*$hargasatuan;
            $cursaldo=0;
            $nilaisaldo=0;
            $qtymasuk=0;
            $qtymasukxharga=0;
            $saldoakhirqty=0;
            $nilaisaldoakhir=0;
            $hargarata=0;
              $str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
                       and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            $res=mysql_query($str);
            if(mysql_numrows($res)<1)//jika belum ada penerimaan sebelumnya
            {
                $newhargarata=$hargasatuan;
                $newqtykeluar=$jumlah;
                $newqtykeluarxharga=$nilaitotal;
                $newsaldoakhirqty=$jumlah;
                $newnilaisaldoakhir=$nilaitotal;
            }
                //bentuk harga baru
                while($bar=mysql_fetch_object($res))
                {
                    $cursaldo=$bar->saldoakhirqty;
                    $nilaisaldo=$bar->nilaisaldoakhir;
                    $qtykeluar=$bar->qtykeluar;
                    $qtykeluarxharga=$bar->qtykeluarxharga;
                    $hargarata=$bar->hargarata; 
                }
                if(($cursaldo-$jumlah)<=0)$newhargarata=$hargasatuan; else{
                    @$newhargarata=($nilaisaldo-$nilaitotal)/($cursaldo-$jumlah);                    
                }
                $newqtykeluar=$qtykeluar+$jumlah;
                @$newqtykeluarxharga=$qtykeluarxharga+$nilaitotal;
                $newsaldoakhirqty=$cursaldo-$jumlah;
                $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
                if($newsaldoakhirqty<0)
                {
//                    exit(" Error: Saldo tidak mencukupi (retur:".$jumlah." saldo:".$cursaldo);
                    exit(" Error: Amount not sufficient (retur:".$jumlah." volume:".$cursaldo);
                }
                if($newhargarata==0)
                {
//                    exit(" Error: Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
                    exit(" Error: Average price can not be formed on ".$notransaksi." material code :".$kodebarang);
                }
                else
                {
                    $strupdate="update ".$dbname.".log_5saldobulanan set 
                                       saldoakhirqty=".$newsaldoakhirqty.", hargarata=".$newhargarata.",nilaisaldoakhir=".$newnilaisaldoakhir.",
                                       lastuser=".$user.",qtykeluar=".$newqtykeluar.",qtykeluarxharga=".$newqtykeluarxharga."
                                       where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";                                      
                }           
      
            //prepare rollback pengembalian
                $strrollback="update ".$dbname.".log_5saldobulanan set 
                    saldoakhirqty=".$cursaldo.", hargarata=".$hargarata.",nilaisaldoakhir=".$nilaisaldo.",
                    lastuser=".$user.",qtykeluar=".$qtykeluar.",qtykeluarxharga=".$qtykeluarxharga."
                    where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

            //prepare update masterbarangdt
            $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
                                kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
                                stockbataspesan, stockminimum, lastuser,kodegudang) values(
                                '".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",".$newhargarata.",
                                0,0,0,".$user.",'".$gudang."'
                                )";
            $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                                hargalastout=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
            //prepare jurnal
            
       #ambil noakun supplier
            $kodekl=substr($supplier,0,4);
            $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->noakun;
            }
        #ambil noakun barang
            $klbarang=substr($kodebarang,0,3);
            $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
            $res=mysql_query($str);
            $akunbarang='';
            while($bar=mysql_fetch_object($res))
            {
                $akunbarang=$bar->noakun;
            }   
            if(($akunbarang=='' or $akunspl=='') and ($klbarang<'400' or substr($kodebarang,0,1)=='9'))                
            {    
//                exit("Error: Noakun  Noakun barang atau supplier  belum ada untuk transaksi ".$notransaksi); 
                exit("Error: Account number for material or supplier not available yet on ".$notransaksi); 
            }
			
			// Cek Nilai Ppn di PO
			$qPO = selectQuery($dbname,'log_poht',"*","nopo='".$nopo."'");
			$resPO = fetchData($qPO);
			if(empty($resPO)) exit("Warning: PO ".$nopo." tidak terdaftar");
			$nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'] * ($nilaitotal / ($resPO[0]['kurs'] * ($resPO[0]['subtotal']-$resPO[0]['nilaidiskon'])));
			// $nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'];
			
			#proses data
            $kodeJurnal = 'INVK1';
            #======================== Nomor Jurnal =============================
            # Get Journal Counter
//            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' ");
            $tmpKonter = fetchData($queryJ);
            $konter = addZero($tmpKonter[0]['nokounter']+1,3);

            # Transform No Jurnal dari No Transaksi
            $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
            #======================== /Nomor Jurnal ============================

            # Prep Header
                $dataRes['header'] = array(
                    'nojurnal'=>$nojurnal,
                    'kodejurnal'=>$kodeJurnal,
                    'tanggal'=>tanggalsystem($tanggal),
                    'tanggalentry'=>date('Ymd'),
                    'posting'=>1,
                    'totaldebet'=>$nilaitotal,
                    'totalkredit'=>-1*$nilaitotal,
                    'amountkoreksi'=>'0',
                    'noreferensi'=>$notransaksi,
                    'autojurnal'=>'1',
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'revisi'=>'0'
                );

                # Data Detail
                $noUrut = 1;

                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>tanggalsystem($tanggal),
                    'nourut'=>$noUrut,
                    'noakun'=>$akunspl,
                    'keterangan'=>'ReturSupplier '.$namabarang.' '.$jumlah." ".$satuan,
                    'jumlah'=>$nilaitotal,
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>substr($gudang,0,4),
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>$kodebarang,
                    'nik'=>'',
                    'kodecustomer'=>'',
                    'kodesupplier'=>$supplier,
                    'noreferensi'=>$notransaksi,
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>$nopo,
                    'kodeblok'=>'',
                    'revisi'=>'0',
					'kodesegment' => $segment
                );
                $noUrut++;

                # Kredit
                $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>tanggalsystem($tanggal),
                    'nourut'=>$noUrut,
                    'noakun'=>$akunbarang,
                    'keterangan'=>'ReturSupplier '.$namabarang.' '.$jumlah." ".$satuan,
                    'jumlah'=>-1*$nilaitotal,
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>substr($gudang,0,4),
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>$kodebarang,
                    'nik'=>'',
                    'kodecustomer'=>'',
                    'kodesupplier'=>$supplier,
                    'noreferensi'=>$notransaksi,
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>$nopo,
                    'kodeblok'=>'',
                    'revisi'=>'0',
					'kodesegment' => $segment
                );
                $noUrut++;    
				
				// Kredit PPn
				if($nilaiPpn>0) {
					$qPpn = selectQuery($dbname,'keu_5parameterjurnal',"noakundebet",
										"kodeaplikasi='INV' and jurnalid='PPN'");
					$resPpn = fetchData($qPpn);
					if(empty($resPpn)) exit("Warning: No Akun untuk PPn Penerimaan Barang belum ada");
					
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>tanggalsystem($tanggal),
						'nourut'=>$noUrut,
						'noakun'=>$resPpn[0]['noakundebet'],
						'keterangan'=>'Retur PPn Pembelian barang '.$namabarang.' '.$jumlah." ".$satuan,
						'jumlah'=>$nilaiPpn * (-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>substr($gudang,0,4),
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$supplier,
						'noreferensi'=>$notransaksi,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nopo,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut++;
				}
				
#=========================================                
   $updflagststussaldo="update ".$dbname.". log_transaksidt set statussaldo=1,hargarata=".$newhargarata.",jumlahlalu=".$cursaldo."
   where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";        
 #==================================execute
   if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){        
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
//                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi'].
//                        "' and kodekelompok='".$kodeJurnal."'");
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$kodept.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#berhasil di jurnal
                      #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
                             echo" Gagal update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                                $errGudang=" Gagal update masterbarangdt"; 
                                $errGudang=" Error update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                 //   $errGudang=" Gagal insert masterbarangdt"; 
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                    $errGudang=" Error update statussaldo on masterbarangdt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                            exit;
                                        }
                                  exit;      
                            }                             
                        }  
                    }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                       // $errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
        }
      } // end of tipetransaksi=6     
      
else if($tipetransaksi=='2')
    {
     #ambil harga satuan dan saldo
        $hargarata=0;
        $saldoakhirqty=0;
        $nilaisaldoakhir=0;
        $qtymasukxharga=0;
        $qtymasuk=0;
        $str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
                       and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $oldhargarata=$bar->hargarata;
            $saldoakhirqty=$bar->saldoakhirqty;
            $nilaisaldoakhir=$bar->nilaisaldoakhir;
            $qtymasukxharga=$bar->qtymasukxharga;  
            $qtymasuk=$bar->qtymasuk;
        }
		#ambil trasaksi gudang
		$stp="select jumlah,hargasatuan as hargarata from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
		$rep=mysql_query($stp);
		while($bap=mysql_fetch_object($rep)){
		  $rpkembali=$bap->hargarata*$bap->jumlah;
		}
    
      $newsaldoakhirqty=$saldoakhirqty+$jumlah;
	  @$hargarata=($nilaisaldoakhir+$rpkembali)/$newsaldoakhirqty;
      $newhargarata=$hargarata;
      $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
      $newqtymasuk=$qtymasuk+$jumlah;
      $newqtymasukxharga=$newqtymasuk*$hargarata;
      
		
      if($hargarata==0) 
      {
//          exit(" Error: harga rata-rata belum ada");
          exit(" Error: Average price not available.");
      }
        
        $strupdate="update ".$dbname.".log_5saldobulanan set 
                    saldoakhirqty=".$newsaldoakhirqty.",hargarata=".$hargarata.", nilaisaldoakhir=".$newnilaisaldoakhir.",
                    lastuser=".$user.",qtymasuk=".$newqtymasuk.",qtymasukxharga=".$newqtymasukxharga."
                    where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

        //prepare rollback penerimaan
        $strrollback="update ".$dbname.".log_5saldobulanan set 
            saldoakhirqty=".$saldoakhirqty.",nilaisaldoakhir=".$nilaisaldoakhir.",
            lastuser=".$user.",qtymasuk=".$qtymasuk.",qtymasukxharga=".$qtymasukxharga."
            where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

        //prepare update masterbarangdt
        $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
                            kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
                            stockbataspesan, stockminimum, lastuser,kodegudang) values(
                            '".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",0,
                            ".$newhargarata.",0,0,".$user.",'".$gudang."'
                            )";
        $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                            hargalastout=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
           if($newhargarata==0)
            {
//                exit(" Error: belum ada harga pada saldo awal bulanan"); 
                exit(" Error: Price not found on the beginning of the month."); 
            }   
      
      
     #=======================================================
     #periksa apakah dari satu PT
     $pengguna=substr($_POST['untukunit'],0,4);
    
     $ptpengguna='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptpengguna=$bar->induk;
     }
      $str="select akunhutang,jenis from ".$dbname.".keu_5caco where 
           kodeorg='".$pengguna."'";
     $res=mysql_query($str);
     $intraco='';
     $interco='';
     while($bar=mysql_fetch_object($res)){
         if($bar->jenis=='intra')
            $intraco=$bar->akunhutang;
         else
            $interco=$bar->akunhutang; 
     }
     
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang,0,4)."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptpengguna)
     {
         #ambil akun interco
         $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='inter'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunpiutang;
            }
         $inter=$interco;   
        if($akunspl=='')
//           exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna); 
           exit("Error: Account for intraco or interco not available yet for ".$pengguna); 
     }
     else if($pengguna!=substr($gudang,0,4)){ #jika satu pt beda kebun
          #ambil akun intraco
         $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='intra'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunpiutang;
            } 
          $inter=$intraco;  
         if($akunspl=='')
//            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna);    
            exit("Error: Account for intraco or interco not available yet for ".$pengguna);    
     }
     
     
    #ambil akun pekerjaan atau kendaraan atau ab
     #periksa ke table setup blok
     $statustm='';
     $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res)){
         $statustm=$bar->statusblok;
     }
         $str="select noakun from ".$dbname.".setup_kegiatan where 
                kodekegiatan='".$kodekegiatan."'";
     $akunpekerjaan='';
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res)){
         $akunpekerjaan=$bar->noakun;
     }
     #jika akun kegiatan tidak ada maka exit
     if($akunpekerjaan=='')
//         exit("Error: Akun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
         exit("Error: Account not available yet for activity ".$kodekegiatan);
     
    #ambil noakun barang
    $klbarang=substr($kodebarang,0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $res=mysql_query($str);
    $akunbarang='';
    while($bar=mysql_fetch_object($res))
    {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='')
//        exit("Error: Noakun barang belum ada untuk transaksi".$notransaksi);
        exit("Error: Material account not available yet on ".$notransaksi);
    else{
          
        $updflagststussaldo="update ".$dbname.". log_transaksidt set statussaldo=1,jumlahlalu=".$saldoakhirqty.", hargarata=".$newhargarata."
                                        where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";        
        
        //penggunaan internal
        if($pengguna==substr($gudang,0,4)){
                    $kodeJurnal = 'INVM1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
//                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($rpkembali),
                            'totalkredit'=>(-1*$rpkembali),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="ReturGudang barang ".$namabarang." ".$jumlah." ".$satuan;
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=> $keterangan,
                            'jumlah'=>($rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>$kodekegiatan,
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++; 
#========================================= 
                                      
   if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){        
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptpengguna.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#berhasil di jurnal
                      #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
//                             echo" Gagal update saldobulanan"; 
                             echo" Error update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
                                $errGudang=" Gagal update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                   // $errGudang=" Gagal insert masterbarangdt".addslashes(mysql_error($conn))."\n";
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                    $errGudang=" Error update statussaldo on masterbarangdt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                            exit;
                                        }
                                  exit;      
                            }                             
                        }  
                    }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt "; 
                    $errGudang=" Error update masterbarangdt "; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                       // $errGudang=" Gagal insert masterbarangdt "; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
         }                
        }        
         else{
             #jika inter atau intraco 
                  #proses data sisi pemilik====================================================
                    $kodeJurnal = 'INVM1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
//                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                      $header1pemilik=$nojurnal;   //no header pemilik    
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($rpkembali),
                            'totalkredit'=>(-1*$rpkembali),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="ReturGudang barang ".$namabarang." ".$jumlah." ".$satuan;
                         $keterangan=substr($keterangan,0,150);
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>($rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$inter,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
   if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){       
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptGudang.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }              
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }  
#proses data sisi pengguna====================================================
                    $kodeJurnal = 'INVM1';
                    #======================== Nomor Jurnal =============================
                    #ambil tanggal terkecil periode pengguna
                    $stri="select tanggalmulai from ".$dbname.".setup_periodeakuntansi
                           where kodeorg='".$pengguna."' and tutupbuku=0";
                    $tanggalsana='';
                    $resi=mysql_query($stri);
                    while($bari=mysql_fetch_object($resi))
                    {
                        $tanggalsana=$bari->tanggalmulai;
                    }
                    if($tanggalsana=='' or substr($tanggalsana,0,7)==(substr(tanggalsystem($tanggal),0,4)."-".substr(tanggalsystem($tanggal),4,2)))#jika periode sama maka biarkan
                        $tanggalsana=tanggalsystem($tanggal);
                    else{//rollback header sisi pemilik
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                            if(!mysql_query($RBDet)) {
                                echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
//                                exit(" Error: periode pembukuan pengguna material tidak sama dengan gudang");
                                exit(" Error: Receivers accounting period not the same as warehouse.");
                            }else{
                                exit(" Error: Receivers accounting period not the same as warehouse.");
                            }  
                    }
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                    unset($dataRes['header']);//ganti header    
                    $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>$tanggalsana,
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($rpkembali),
                            'totalkredit'=>(-1*$rpkembali),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                        
                        );

                        # Data Detail
                         $keterangan="ReturGudang barang ".$namabarang." ".$jumlah." ".$satuan." ".substr($_POST['tanggal'],0,7);
                         $keterangan=substr($keterangan,0,150);
                        $noUrut = 1;
                        unset($dataRes['detail']);//ganti detail 
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunspl,
                            'keterangan'=>$keterangan,
                            'jumlah'=>($rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$rpkembali),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>$kodekegiatan,
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++; 
                    #===========EXECUTE
   if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){       
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptpengguna.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#berhasil di jurnal
                      #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
                             echo" Gagal update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
                                $errGudang=" Gagal update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                    //$errGudang=" Gagal insert masterbarangdt"; 
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                    $errGudang=" Error update statussaldo on masterbarangdt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                        //hapus juga sisi pemilik
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                                            if(!mysql_query($RBDet)) {
                                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                                exit;
                                            }
                                        }
                                  exit;      
                            }                             
                        }  
                    }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        //hapus juga sisi pemilik
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                            if(!mysql_query($RBDet)) {
                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                exit;
                            }
                    }
                }
            } else {
                echo $headErr;
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                        //$errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
         }  
        }//disini=====
    }        
    } // end of tipetransaksi = 2
else if($tipetransaksi=='3')//penerimaan mutasi
{
    #ambil harga satuan dan saldo
        $hargarata=0;
        $saldoakhirqty=0;
        $nilaisaldoakhir=0;
        $qtymasukxharga=0;
        $qtymasuk=0;
        $nilaitotal=$jumlah*$hargasatuan;
        $str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
                       and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
        $res=mysql_query($str);
        if(mysql_numrows($res)<1)//jika belum ada penerimaan sebelumnya
        {
            $newhargarata=$hargasatuan;
            $newqtymasuk=$jumlah;
            $newqtymasukxharga=$nilaitotal;
            $newsaldoakhirqty=$jumlah;
            $newnilaisaldoakhir=$nilaitotal;
            $strupdate="insert into ".$dbname.".log_5saldobulanan (
                                kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser,
                                periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, 
                                qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal)
                                values('".$kodept."','".$kodebarang."',".$newqtymasuk.",".$newhargarata.",".$user.",
                                '".$periode."',".$newqtymasukxharga.",'".$gudang."',".$newsaldoakhirqty.",0,".$newnilaisaldoakhir.",0,0,0,0)";   
        }
       else{
            while($bar=mysql_fetch_object($res))
            {
                $hargarata=$bar->hargarata;
                $saldoakhirqty=$bar->saldoakhirqty;
                $nilaisaldoakhir=$bar->nilaisaldoakhir;
                $qtymasukxharga=$bar->qtymasukxharga;  
                $qtymasuk=$bar->qtymasuk;
            }
                $newsaldoakhirqty=$saldoakhirqty+$jumlah;
                @$newhargarata     =($nilaitotal+$nilaisaldoakhir)/($newsaldoakhirqty);
                $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
                $newqtymasuk=$qtymasuk+$jumlah;
                $newqtymasukxharga=$newqtymasuk*$hargarata;//menggunakan harga rata-rata pada saat itu, bukan harga pada saat dikeluarkan 
            if($newhargarata==0 or $newhargarata=='')
                {
//                    exit(" Error: Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
                    exit(" Error: Average price cannot be formed on ".$notransaksi." material code :".$kodebarang);
                }
                else
                {                
                    $strupdate="update ".$dbname.".log_5saldobulanan set 
                                       saldoakhirqty=".$newsaldoakhirqty.", hargarata=".$newhargarata.",nilaisaldoakhir=".$newnilaisaldoakhir.",
                                       lastuser=".$user.",qtymasuk=".$newqtymasuk.",qtymasukxharga=".$newqtymasukxharga."
                                       where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";     
             
                } 
       }    
      
        //prepare rollback penerimaan
        $strrollback="update ".$dbname.".log_5saldobulanan set 
            saldoakhirqty=".$saldoakhirqty.",nilaisaldoakhir=".$nilaisaldoakhir.",
            lastuser=".$user.",qtymasuk=".$qtymasuk.",qtymasukxharga=".$qtymasukxharga."
            where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

        //prepare update masterbarangdt
        $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
                            kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
                            stockbataspesan, stockminimum, lastuser,kodegudang) values(
                            '".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",0,
                            ".$newhargarata.",0,0,".$user.",'".$gudang."'
                            )";
        $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                            hargalastin=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
      
      
      
     #=======================================================
     #periksa apakah dari satu PT
     $pengguna=substr($gudang,0,4);//ini sebenarnya pemilik
    
     $ptpengguna='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";//ini sebenarnya pemilik
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptpengguna=$bar->induk;
     }
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudangx,0,4)."'";//ini yang pengguna
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptpengguna)
     {
         #ambil akun interco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx,0,4)."' and jenis='inter'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            }  
        if($akunspl=='')
//           exit(" Error: Akun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4)); 
           exit(" Error: Account intraco or interco not available for ".substr($gudangx,0,4)); 
     }
     else if($pengguna!=substr($gudangx,0,4)){ #jika satu pt beda kebun
          #ambil akun intraco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx,0,4)."' and jenis='intra'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            } 
         if($akunspl=='')
//            exit(" Error: Akun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4));    
            exit(" Error: Account intraco / interco not available for ".substr($gudangx,0,4));    
     }
    #ambil noakun barang
    $klbarang=substr($kodebarang,0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $res=mysql_query($str);
    $akunbarang='';
    while($bar=mysql_fetch_object($res))
    {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='')
//        exit(" Error: Noakun barang belum ada untuk transaksi".$notransaksi);
        exit(" Error1: Account for material not available for ".$notransaksi);
    else{
                         
        $updflagststussaldo="update ".$dbname.". log_transaksidt set statussaldo=1,jumlahlalu=".$saldoakhirqty.", hargarata=".$newhargarata."
                                        where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";    
#proses data sisi pengguna====================================================
                    $kodeJurnal = 'INVM1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = tanggalsystem($tanggal)."/".$pengguna."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                    unset($dataRes['header']);//ganti header    
                    $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>$nilaitotal,
                            'totalkredit'=>(-1*$nilaitotal),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                        
                        );

                        # Data Detail
                         $keterangan="Terima Mutasi barang ".$namabarang." ".$jumlah." ".$satuan." ".substr($_POST['tanggal'],0,7);
                         $keterangan=substr($keterangan,0,150);
                        $noUrut = 1;
                        unset($dataRes['detail']);//ganti detail 
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>$nilaitotal,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunspl,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$nilaitotal),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++; 
                    #===========EXECUTE
                        
if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!='' and (substr($pengguna,0,4)!=substr($gudangx,0,4))){  #hanya barang stok yang dijurnal      dan mutasi keluar kebun
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn)).$insDet."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptpengguna.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
                            #proses gudang
                            $errGudang='';
                            if(!mysql_query($strupdate))
                            {
//                                    echo" Gagal update saldobulanan";                           
                                    echo" Error update saldobulanan";                           
                            }
                            else
                            {
                                    #update masterbarangdt
                                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                                    $errGudang=" Gagal update masterbarangdt"; 
                                    $errGudang=" Error update masterbarangdt"; 
                                if(mysql_affected_rows()==0)
                                {
                                    if(@!mysql_query($instmaster))
                                    {
                                        //$errGudang=" Gagal insert masterbarangdt"; 
                                    }
                                }  

                                if($errGudang=='')
                                    {
                                    if(!mysql_query($updflagststussaldo))
                                    {
//                                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                        $errGudang=" Error update statussaldo on masterbarangdt";
                                    }
                                }
                                if($errGudang!='')//check jika ada error(ini sudah di test)
                                {
                                    #rollback gudang
                                        echo $errGudang;
                                        #rollback gudang
                                        if(!mysql_query($strrollback))
                                        {
                                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                        }
                                        exit;      
                                }                             
                            }          
                        }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        //hapus juga sisi pemilik
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            if(!mysql_query($RBDet)) {
                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                exit;
                            }
                    }
                }
            } else {
                echo $headErr;
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                        //$errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo pada masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
         }  
    }
} // end of tipetransaksi = 3
else if($tipetransaksi=='7')//pengeluaran mutasi gudang
    {
     #ambil harga satuan dan saldo
        $hargarata=0;
        $saldoakhirqty=0;
        $nilaisaldoakhir=0;
        $qtykeluarxharga=0;
        $qtykeluar=0;
        $str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
                       and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $hargarata=$bar->hargarata;
            $saldoakhirqty=$bar->saldoakhirqty;
            $nilaisaldoakhir=$bar->nilaisaldoakhir;
            $qtykeluarxharga=$bar->qtykeluarxharga;  
            $qtykeluar=$bar->qtykeluar;
        }
      if($hargarata==0) 
      {
//          exit(" Error: harga rata-rata belum ada");
          exit(" Error: Average price not available");
      }
      
      $newsaldoakhirqty=$saldoakhirqty-$jumlah;
      $newhargarata=$hargarata;
      $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
      $newqtykeluar=$qtykeluar+$jumlah;
      $newqtykeluarxharga=$newqtykeluar*$newhargarata;
      if($newsaldoakhirqty<0)
      {
//          exit(" Error: Saldo tidak cukup");
          exit(" Error: Amount not sufficient");
      }
      
        $strupdate="update ".$dbname.".log_5saldobulanan set 
                    saldoakhirqty=".$newsaldoakhirqty.",nilaisaldoakhir=".$newnilaisaldoakhir.",
                    lastuser=".$user.",qtykeluar=".$newqtykeluar.",qtykeluarxharga=".$newqtykeluarxharga."
                    where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";                                      
        //prepare rollback penerimaan
        $strrollback="update ".$dbname.".log_5saldobulanan set 
            saldoakhirqty=".$saldoakhirqty.",nilaisaldoakhir=".$nilaisaldoakhir.",
            lastuser=".$user.",qtykeluar=".$qtykeluar.",qtykeluarxharga=".$qtykeluarxharga."
            where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

        //prepare update masterbarangdt
        $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
                            kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
                            stockbataspesan, stockminimum, lastuser,kodegudang) values(
                            '".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",0,
                            ".$newhargarata.",0,0,".$user.",'".$gudang."'
                            )";
        $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                            hargalastout=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
      
      
     #=======================================================
     #periksa apakah dari satu PT
     $pengguna=substr($gudangx,0,4);//gudang tujuan
    
     $ptpengguna='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptpengguna=$bar->induk;
     }
      $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where 
           kodeorg='".$pengguna."'";
     $res=mysql_query($str);
     $intraco='';
     $interco='';
     while($bar=mysql_fetch_object($res)){
         if($bar->jenis=='intra')
            $intraco=$bar->akunpiutang;
         else
            $interco=$bar->akunpiutang; 
     }
     
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang,0,4)."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptpengguna)
     {
         #ambil akun interco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='inter'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            }
         $inter=$interco;   
        if($akunspl=='')
//           exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna); 
           exit("Error: Account intraco or interco not available for ".$pengguna); 
     }
     else if($pengguna!=substr($gudang,0,4)){ #jika satu pt beda kebun
          #ambil akun intraco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='intra'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            } 
          $inter=$intraco;  
         if($akunspl=='')
//            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna);    
            exit("Error: Account intraco or interco not available for ".$pengguna);    
     }
        
    #ambil noakun barang
    $klbarang=substr($kodebarang,0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $res=mysql_query($str);
    $akunbarang='';
    while($bar=mysql_fetch_object($res))
    {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='')
//        exit("Error: Noakun barang belum ada untuk transaksi".$notransaksi);
        exit("Error2: Account for material not available for ".$notransaksi);
    else{
          
        $updflagststussaldo="update ".$dbname.". log_transaksidt set statussaldo=1,jumlahlalu=".$saldoakhirqty.",hargarata=".$newhargarata."
                                        where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";        
        
        //mutasi antar gudang internal tidak menggunakan jurnal
     if($pengguna==substr($gudang,0,4)){
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                        //$errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }                        
        }        
         else{
             #jika inter atau intraco 
                  #proses data sisi pemilik====================================================
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
//                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                      $header1pemilik=$nojurnal;   //no header pemilik    
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($jumlah*$hargarata),
                            'totalkredit'=>(-1*$jumlah*$hargarata),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="Mutasi barang ".$namabarang." ".$jumlah." ".$satuan;
                         $keterangan=substr($keterangan,0,150);
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$inter,
                            'keterangan'=>$keterangan,
                            'jumlah'=>($jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                          
 if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){    
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptGudang.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                   else{#berhasil di jurnal
                      #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
                             echo" Gagal update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                                $errGudang=" Gagal update masterbarangdt"; 
                                $errGudang=" Error update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                   // $errGudang=" Gagal insert masterbarangdt"; 
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                    $errGudang=" Error update statussaldo on masterbarangdt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                        //hapus juga sisi pemilik
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                                            if(!mysql_query($RBDet)) {
                                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                                exit;
                                            }
                                        }
                                  exit;      
                            }                             
                        }  
                    }  
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }  
        }//disini=====
    }        
    } // end of tipetransaksi = 7   
else if($tipetransaksi=='5')//pengeluaran
    {
     #ambil harga satuan dan saldo
        $hargarata=0;
        $saldoakhirqty=0;
        $nilaisaldoakhir=0;
        $qtykeluarxharga=0;
        $qtykeluar=0;
        $str="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."'
                       and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $hargarata=$bar->hargarata;
            $saldoakhirqty=$bar->saldoakhirqty;
            $nilaisaldoakhir=$bar->nilaisaldoakhir;
            $qtykeluarxharga=$bar->qtykeluarxharga;  
            $qtykeluar=$bar->qtykeluar;
        }
      if($hargarata==0) 
      {
          exit(" Error: harga rata-rata belum ada");
      }
      
      $newsaldoakhirqty=$saldoakhirqty-$jumlah;
      $newhargarata=$hargarata;
      $newnilaisaldoakhir=$newhargarata*$newsaldoakhirqty;
      $newqtykeluar=$qtykeluar+$jumlah;
      $newqtykeluarxharga=$newqtykeluar*$newhargarata;
      if($newsaldoakhirqty<0)
      {
//          exit(" Error: Saldo tidak cukup");
          exit(" Error: Amount not sufficient");
      }
      
        $strupdate="update ".$dbname.".log_5saldobulanan set 
                    saldoakhirqty=".$newsaldoakhirqty.",nilaisaldoakhir=".$newnilaisaldoakhir.",
                    lastuser=".$user.",qtykeluar=".$newqtykeluar.",qtykeluarxharga=".$newqtykeluarxharga."
                    where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";                                      
        //prepare rollback penerimaan
        $strrollback="update ".$dbname.".log_5saldobulanan set 
            saldoakhirqty=".$saldoakhirqty.",nilaisaldoakhir=".$nilaisaldoakhir.",
            lastuser=".$user.",qtykeluar=".$qtykeluar.",qtykeluarxharga=".$qtykeluarxharga."
            where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 

        //prepare update masterbarangdt
        $instmaster=" insert into ".$dbname.".log_5masterbarangdt(
                            kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, 
                            stockbataspesan, stockminimum, lastuser,kodegudang) values(
                            '".$kodept."','".$kodebarang."',".$newsaldoakhirqty.",0,
                            ".$newhargarata.",0,0,".$user.",'".$gudang."'
                            )";
        $updmaster="update ".$dbname.".log_5masterbarangdt set saldoqty=".$newsaldoakhirqty.",
                            hargalastout=".$newhargarata." where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'"; 
            
      
      
     #=======================================================
     #periksa apakah dari satu PT
     $pengguna=substr($_POST['untukunit'],0,4);
    
     $ptpengguna='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptpengguna=$bar->induk;
     }
      $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where 
           kodeorg='".$pengguna."'";
     $res=mysql_query($str);
     $intraco='';
     $interco='';
     while($bar=mysql_fetch_object($res)){
         if($bar->jenis=='intra')
            $intraco=$bar->akunpiutang;
         else
            $interco=$bar->akunpiutang; 
     }
     
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang,0,4)."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptpengguna)
     {
         #ambil akun interco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='inter'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            }
         $inter=$interco;   
        if($akunspl=='')
//           exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna); 
           exit("Error: Account intraco or interco not available for ".$pengguna); 
     }
     else if($pengguna!=substr($gudang,0,4)){ #jika satu pt beda kebun
          #ambil akun intraco
         $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='intra'";
            $res=mysql_query($str);
            $akunspl='';
            while($bar=mysql_fetch_object($res))
            {
                $akunspl=$bar->akunhutang;
            } 
          $inter=$intraco;  
         if($akunspl=='')
//            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna);    
            exit("Error: Account intraco or interco not available for ".$pengguna);    
     }
     
     
    #ambil akun pekerjaan atau kendaraan atau ab
     #periksa ke table setup blok
     $statustm='';
     $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res)){
         $statustm=$bar->statusblok;
     }
         $str="select noakun from ".$dbname.".setup_kegiatan where 
                kodekegiatan='".$kodekegiatan."'";
     $akunpekerjaan='';
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res)){
         $akunpekerjaan=$bar->noakun;
     }
     #untuk project aktiva dalam konstruksi maka akun diambil dari kolom kodekegiatan
     $kodeasset='';
     if(substr($blok,0,2)=='AK' or substr($blok,0,2)=='PB'){
            $akunpekerjaan=substr($kodekegiatan,0,7);
            $kodeasset=$blok;
//            $sblk="select distinct kodeorg from ".$dbname.".project where kode='".$blok."'";
//            $qblk=mysql_query($sblk) or die(mysql_error($conn));
//            $rblk=mysql_fetch_assoc($qblk);#menambahkan untuk mengisi kodeblok
            $blok="";#pemindahan kodeblok ke kode asset
     }
     #jika akun kegiatan tidak ada maka exit
     if($akunpekerjaan=='')
//         exit("Error: Akun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
         exit("Error: Account not available for activity ".$kodekegiatan);
     
    #ambil noakun barang
    $klbarang=substr($kodebarang,0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $res=mysql_query($str);
    $akunbarang='';
    while($bar=mysql_fetch_object($res))
    {
        $akunbarang=$bar->noakun;
    }   
//if(($akunbarang=='' or $akunspl=='') and ($klbarang<'400' or substr($kodebarang,0,1)=='9'))
if(($akunbarang=='') and ($klbarang<'400' or substr($kodebarang,0,1)=='9'))
//        exit("Error: Noakun barang belum ada untuk transaksi".$notransaksi);
        exit("Error3: Account for material not available for ".$notransaksi);
    else{
          
     if(substr($kodeasset,0,2)=='AK' or substr($kodeasset,0,2)=='PB'){
        $updflagststussaldo="update ".$dbname.".log_transaksidt set statussaldo=1,jumlahlalu=".$saldoakhirqty.",hargarata=".$newhargarata."
                                        where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$kodeasset."'";           
     }else{
        $updflagststussaldo="update ".$dbname.".log_transaksidt set statussaldo=1,jumlahlalu=".$saldoakhirqty.",hargarata=".$newhargarata."
                                        where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";  
     }
        
        //penggunaan internal$ptGudang$ptpengguna
        if($pengguna==substr($gudang,0,4)){
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
//                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($jumlah*$hargarata),
                            'totalkredit'=>(-1*$jumlah*$hargarata),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="Pemakaian barang ".$namabarang." ".$jumlah." ".$satuan;
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=> $keterangan,
                            'jumlah'=>($jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>$kodekegiatan,
                            'kodeasset'=>$kodeasset,
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++; 
#=========================================                                
 if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){    
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptpengguna.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#berhasil di jurnal
                      #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
//                             echo" Gagal update saldobulanan"; 
                             echo" Error update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
                                $errGudang=" Gagal update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                    //$errGudang=" Gagal insert masterbarangdt"; 
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada logtransaksidt";
                                    $errGudang=" Error update statussaldo on log_transaksidt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                            exit;
                                        }
                                  exit;      
                            }                             
                        }  
                    }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                        //$errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
         }                
        }        
         else{                 
             #jika inter atau intraco 
                  #proses data sisi pemilik====================================================
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
//                    # Get Journal Counter
//                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",tanggalsystem($tanggal))."/".substr($gudang,0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                      $header1pemilik=$nojurnal;   //no header pemilik    
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($jumlah*$hargarata),
                            'totalkredit'=>(-1*$jumlah*$hargarata),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="Pemakaian barang ".$namabarang." ".$jumlah." ".$satuan;
                         $keterangan=substr($keterangan,0,150);
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$inter,
                            'keterangan'=>$keterangan,
                            'jumlah'=>($jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($tanggal),
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($gudang,0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' => $segment
                        );                      
 if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){       
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if(empty($headErr)) {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptGudang.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }              
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
                }
            } else {
                echo $headErr;
                exit;  
            }
         #============================================================================          
     }  
        //                    echo "warning: header1pemilik_".$header1pemilik."\n".$updJurnal;
        //                    exit;
        //exit('trap!');     
        #proses data sisi pengguna====================================================
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    #ambil tanggal terkecil periode pengguna
                    $stri="select tanggalmulai from ".$dbname.".setup_periodeakuntansi
                           where kodeorg='".$pengguna."' and tutupbuku=0";
                    $tanggalsana='';
                    $resi=mysql_query($stri);
                    while($bari=mysql_fetch_object($resi))
                    {
                        $tanggalsana=$bari->tanggalmulai;
                    }
                    if($tanggalsana=='' or substr($tanggalsana,0,7)==(substr(tanggalsystem($tanggal),0,4)."-".substr(tanggalsystem($tanggal),4,2)))#jika periode sama maka biarkan
                        $tanggalsana=tanggalsystem($tanggal);
                    else{//rollback header sisi pemilik
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                            if(!mysql_query($RBDet)) {
                                echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
//                                exit(" Error: periode pembukuan pengguna material tidak sama dengan gudang");
                                exit(" Error: Receivers accounting period not the same as warehouse");
                            }else{
                                exit(" Error: Receivers accounting period not the same as warehouse");
                            }  
                    }
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                    unset($dataRes['header']);//ganti header    
                    $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>$tanggalsana,
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>($jumlah*$hargarata),
                            'totalkredit'=>(-1*$jumlah*$hargarata),
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$notransaksi,
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                        
                        );

                        # Data Detail
                         $keterangan="Pemakaian barang ".$namabarang." ".$jumlah." ".$satuan." ".substr($_POST['tanggal'],0,7);
                         $keterangan=substr($keterangan,0,150);
                        $noUrut = 1;
                        unset($dataRes['detail']);//ganti detail 
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=>$keterangan,
                            'jumlah'=>($jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>$kodekegiatan,
                            'kodeasset'=>$kodeasset,
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunspl,
                            'keterangan'=>$keterangan,
                            'jumlah'=>(-1*$jumlah*$hargarata),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$kodebarang,
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$notransaksi,
                            'noaruskas'=>'',
                            'kodevhc'=>$kodemesin,
                            'nodok'=>'',
                            'kodeblok'=>$blok,
							'revisi'=>'0',
							'kodesegment' => $segment
                        );
                        $noUrut++; 
                    #===========EXECUTE                      
 if((substr($kodebarang,0,3)<'400' or substr($kodebarang,0,1)=='9') and trim($akunbarang)!=''){      
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            if(!mysql_query($insHead)) {
                $headErr .= "Insert Header Error : ".addslashes(mysql_error($conn))."\n";
            }
            if($headErr=='') {
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    if(!mysql_query($insDet)) {
                        $detailErr .= "Insert Detail Error : ".addslashes(mysql_error($conn))."\n";
                        break;
                    }
                }
                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>> Update Kode Jurnal
//                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
//                        "kodeorg='".$_SESSION['org']['kodeorganisasi'].
//                        "' and kodekelompok='".$kodeJurnal."'");
                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                        "kodeorg='".$ptpengguna.
                        "' and kodekelompok='".$kodeJurnal."'");
                    if(!mysql_query($updJurnal)) {
                        echo "Update Kode Jurnal Error : ".addslashes(mysql_error($conn))."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        if(!mysql_query($RBDet)) {
                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                            exit;
                        }
                        exit;
                    }
                    else{#berhasil di jurnal
//                    echo "warning: \nheader1pemilik_".$header1pemilik."\nnojurnal_".$nojurnal."\n".$insHead;
//                    exit; 

                        #proses gudang
                        $errGudang='';
                        if(!mysql_query($strupdate))
                        {
//                             echo" Gagal update saldobulanan"; 
                             echo" Error update saldobulanan"; 
                                # Rollback, Delete Header
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                if(!mysql_query($RBDet)) {
                                    echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                    exit;
                                }                             
                        }
                        else
                        {
                             #update masterbarangdt
                            if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                                $errGudang=" Gagal update masterbarangdt"; 
                                $errGudang=" Error update masterbarangdt"; 
                            if(mysql_affected_rows()==0)
                            {
                                if(@!mysql_query($instmaster))
                                {
                                    //$errGudang=" Gagal insert masterbarangdt"; 
                                }
                            }  
                                  
                            if($errGudang=='')
                             {
                                if(!mysql_query($updflagststussaldo))
                                {
//                                    $errGudang=" Gagal update statussaldo pada masterbarangdt";
                                    $errGudang=" Error update statussaldo on masterbarangdt";
                                }
                            }
                            if($errGudang!='')//check jika ada error(ini sudah di test)
                            {
                                #rollback gudang
                                    echo $errGudang;
                                    #rollback gudang
                                    if(!mysql_query($strrollback))
                                    {
                                       echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                                    }
                                      # Rollback, Delete Header jurnal
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        if(!mysql_query($RBDet)) {
                                            echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                                        //hapus juga sisi pemilik
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                                            if(!mysql_query($RBDet)) {
                                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                                exit;
                                            }
                                        }
                                  exit;      
                            }                             
                        }  
                    }
               
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        //hapus juga sisi pemilik
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                            if(!mysql_query($RBDet)) {
                                echo "Rollback Delete Header pemilik Error : ".addslashes(mysql_error($conn))."\n";
                                exit;
                            }
                    }
                }
            } else {
                echo $headErr;
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$header1pemilik."'");
                    if(!mysql_query($RBDet)) {
                        echo "Rollback Delete Header Error : ".addslashes(mysql_error($conn))."\n";
                        exit;
                    }
            }
         #============================================================================          
     }
     else{#jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
            #proses gudang
            $errGudang='';
            if(!mysql_query($strupdate))
            {
//                    echo" Gagal update saldobulanan";                           
                    echo" Error update saldobulanan";                           
            }
            else
            {
                    #update masterbarangdt
                if(!mysql_query($updmaster))// error catch disatukan dengan if dibawahnya
//                    $errGudang=" Gagal update masterbarangdt"; 
                    $errGudang=" Error update masterbarangdt"; 
                if(mysql_affected_rows()==0)
                {
                    if(@!mysql_query($instmaster))
                    {
                        //$errGudang=" Gagal insert masterbarangdt"; 
                    }
                }  

                if($errGudang=='')
                    {
                    if(!mysql_query($updflagststussaldo))
                    {
//                        $errGudang=" Gagal update statussaldo pada masterbarangdt";
                        $errGudang=" Error update statussaldo on masterbarangdt";
                    }
                }
                if($errGudang!='')//check jika ada error(ini sudah di test)
                {
                    #rollback gudang
                        echo $errGudang;
                        #rollback gudang
                        if(!mysql_query($strrollback))
                        {
                            echo "Rollback saldobulanan Error : ".addslashes(mysql_error($conn))."\n"; 
                        }
                        exit;      
                }                             
            }          
         }  
        }//disini=====
    }        
    }    
  } // end of statussaldo=0   
} //  end of if(isTransactionPeriod()) line: 7
else
{
    echo " Error: Transaction Period missing";
}
?>