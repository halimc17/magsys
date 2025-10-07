<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param=$_POST;

//print_r($param);

$optnmCust=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

switch($param['proses']){
    case'insert':
        if($param['tanggal']==''){
            exit("error:Tanggal tidak boleh kosong");
        }
        if($param['nilaiinvoice']==''){
            exit("error:Nilai invoice tidak boleh kosong");
        }
	 if($param['noorder']==''){
            exit("error:No Kontrak tidak boleh kosong");
        }
	 if($param['debet']==''){
            exit("error:Debet tidak boleh kosong");
        }
	 if($param['kredit']==''){
            exit("error:Kredit tidak boleh kosong");
        }
        if($param['nilaippn']==''){
            $param['nilaippn']=0;
        }
        if($param['jatuhtempo']==''){
            $param['jatuhtempo']='0000-00-00';
        }
		$param['nilaiinvoice']=str_replace(",","",$param['nilaiinvoice']);
		$param['nilaippn']=str_replace(",","",$param['nilaippn']);
		$whrBrg="nokontrak='".$param['noorder']."'";
		$optBrg=makeOption($dbname,'pmn_kontrakjual','nokontrak,kodebarang',$whrBrg);
		$optKdpt=makeOption($dbname,'pmn_kontrakjual','nokontrak,kodept',$whrBrg);
                $param['rupiah8']==''?$param['rupiah8']=0:$param['rupiah8']=$param['rupiah8'];
                $param['rupiah7']==''?$param['rupiah7']=0:$param['rupiah7']=$param['rupiah7'];
                $param['rupiah6']==''?$param['rupiah6']=0:$param['rupiah6']=$param['rupiah6'];
		$param['rupiah5']==''?$param['rupiah5']=0:$param['rupiah5']=$param['rupiah5'];
		$param['rupiah4']==''?$param['rupiah4']=0:$param['rupiah4']=$param['rupiah4'];
		$param['rupiah3']==''?$param['rupiah3']=0:$param['rupiah3']=$param['rupiah3'];
		$param['rupiah2']==''?$param['rupiah2']=0:$param['rupiah2']=$param['rupiah2'];
		$param['rupiah1']==''?$param['rupiah1']=0:$param['rupiah1']=$param['rupiah1'];
                $param['kuantitas']=str_replace(",","",$param['kuantitas']);
		$param['kuantitas']==''?$param['kuantitas']=0:$param['kuantitas']=$param['kuantitas'];
		if($param['noinvoice']!=''){
			$sdel="delete from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
                        
                        
                        
			if(mysql_query($sdel)){
					$sinser="insert into ".$dbname.".keu_penagihanht 
							(noinvoice,kodeorg,kodept,tanggal,nokontrak,kodecustomer,nilaiinvoice,nilaippn,jatuhtempo,matauang,kurs,bayarke,debet,kredit,nofakturpajak,keterangan1,keterangan2,keterangan3,keterangan4,keterangan5,rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,kodebarang,keterangan6,rupiah6,keterangan7,rupiah7,keterangan8,rupiah8,ttd,jenis,kuantitas) values 
							 ('".$param['noinvoice']."','".$param['kodeorganisasi']."','".$optKdpt[$param['noorder']]."','".tanggalsystem($param['tanggal'])."','".$param['noorder']."','".$param['kodecustomer']."','".$param['nilaiinvoice']."','".$param['nilaippn']."','".tanggalsystem($param['jatuhtempo'])."','".$param['matauang']."','".$param['kurs']."','".$param['bayarke']."','".$param['debet']."','".$param['kredit']."','".$param['nofakturpajak']."','".$param['keterangan1']."','".$param['keterangan2']."','".$param['keterangan3']."','".$param['keterangan4']."','".$param['keterangan5']."','".$param['rupiah1']."','".$param['rupiah2']."','".$param['rupiah3']."','".$param['rupiah4']."','".$param['rupiah5']."','".$optBrg[$param['noorder']]."','".$param['keterangan6']."','".$param['rupiah6']."','".$param['keterangan7']."','".$param['rupiah7']."','".$param['keterangan8']."','".$param['rupiah8']."','".$param['ttd']."','".$param['jenis']."','".$param['kuantitas']."')";
					if(!mysql_query($sinser)){
						exit("error: code 11256\n ".  mysql_error($conn)."___".$sinser);
					}
			}else{
					exit("error: code 1125\n ".  mysql_error($conn)."___".$sdel);
			}
		}else{
			$sinser="insert into ".$dbname.".keu_penagihanht 
							(noinvoice,kodeorg,kodept,tanggal,nokontrak,kodecustomer,nilaiinvoice,nilaippn,jatuhtempo,matauang,kurs,bayarke,debet,kredit,nofakturpajak,keterangan1,keterangan2,keterangan3,keterangan4,keterangan5,rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,kodebarang,keterangan6,rupiah6,keterangan7,rupiah7,keterangan8,rupiah8,ttd,jenis,kuantitas) values 
							 ('".generateNoInvoice($param['noorder'],$param['tanggal'])."','".$param['kodeorganisasi']."','".$optKdpt[$param['noorder']]."','".tanggalsystem($param['tanggal'])."','".$param['noorder']."','".$param['kodecustomer']."','".$param['nilaiinvoice']."','".$param['nilaippn']."','".tanggalsystem($param['jatuhtempo'])."','".$param['matauang']."','".$param['kurs']."','".$param['bayarke']."','".$param['debet']."','".$param['kredit']."','".$param['nofakturpajak']."','".$param['keterangan1']."','".$param['keterangan2']."','".$param['keterangan3']."','".$param['keterangan4']."','".$param['keterangan5']."','".$param['rupiah1']."','".$param['rupiah2']."','".$param['rupiah3']."','".$param['rupiah4']."','".$param['rupiah5']."','".$optBrg[$param['noorder']]."','".$param['keterangan6']."','".$param['rupiah6']."','".$param['keterangan7']."','".$param['rupiah7']."','".$param['keterangan8']."','".$param['rupiah8']."','".$param['ttd']."','".$param['jenis']."','".$param['kuantitas']."')";
					if(!mysql_query($sinser)){
						exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
					}
		}
        
		break;	 
	case'getKursInvoice':
		if($param['noorder']==''){
			echo "Gagal. No Kontrak harus diisi.";
		}else{
			if($param['matauang']=='IDR'){
				echo '1';
			}else{
				$tanggal=tanggalsystem($param['tanggal']);
				$sKurs="select * from ".$dbname.".setup_matauangrate where kode='".$param['matauang']."' and daritanggal='".$tanggal."'";
				$qKurs=mysql_query($sKurs) or die(mysql_error($conn));
				if(mysql_num_rows($qKurs)<=0){
					echo 'Gagal. Tidak ada kurs pada tanggal '.$param['tanggal'];
				}else{
					$bKurs=mysql_fetch_assoc($qKurs);
					echo $bKurs['kurs'];
				}
			}
		}
	break;
        
        
        case'getNilai':
            if($param['matauang']!='IDR')
            {
                $ppn=$param['kurs']*$param['nilaippn'];
                $nilInv=$param['kurs']*$param['nilaiinvoice'];
            }
            else
            {
                $ppn=$param['nilaippn'];
                $nilInv=$param['nilaiinvoice'];
            }
            
            echo $ppn.'###'.$nilInv;
            //exit("Error:ASD");
          
        break;
        
	
    case'loadData':
	   // print_r($param);
		$where = '';
        if(!empty($param['noinvoice'])) {
            $where= $where." and noinvoice like '%".$param['noinvoice']."%'";
        }
		if(!empty($param['nokontrak'])) {
            $where= $where." and nokontrak like '%".$param['nokontrak']."%'";
        }
        if(!empty($param['tanggalCr'])) {            
            $tgrl=explode("-",$param['tanggalCr']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where= $where." and left(tanggal,10) = '".$ert."'";
        }
		if(!empty($param['kodebarang'])) {
            $where= $where." and kodebarang = '".$param['kodebarang']."'";
        }
		if(!empty($param['kodecustomer'])) {
            $where= $where." and kodecustomer = '".$param['kodecustomer']."'";
        }
		
		
        $sdel="";
        $limit=20;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
        $sql="select count(*) jmlhrow from ".$dbname.".keu_penagihanht where nokontrak!='' ".$where." order by tanggal desc";
		
        $query=mysql_query($sql) or die(mysql_error());
        while($jsl=mysql_fetch_object($query)){
            $jlhbrs= $jsl->jmlhrow;
        }
		
		$str="select * from ".$dbname.".keu_penagihanht where  nokontrak!=''  ".$where."  order by tanggal desc
              limit ".$offset.",".$limit." ";
        //print_r($str);			  
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
			$sPpn="select a.ppn  from ".$dbname.".pmn_kontrakjual a  where a.nokontrak='".$rstr['nokontrak']."'";
			$qPpn=mysql_query($sPpn);
			$rPpn=mysql_fetch_assoc($qPpn);
			
			//jika kawasan berikat dan include ppn maka tidak termasuk ppn
			// if($rPpn['statusberikat']==1 && $rPpn['ppn']==1){
				// $nilaiinv=$rstr['nilaiinvoice']*10/100;
			// }else{
				// $nilaiinv=$rstr['nilaiinvoice']+$rstr['nilaippn'];
			// }
			$nilaiinv=$rstr['nilaiinvoice']+$rstr['nilaippn'];
			
            $nilaiKlaimPengurang=$rstr['rupiah1']+$rstr['rupiah2']+$rstr['rupiah3']
                    +$rstr['rupiah4']+$rstr['rupiah5']+$rstr['rupiah6']+$rstr['rupiah7']-$rstr['rupiah8'];
          // $totalkurang=$nilaiinv-$nilaiKlaimPengurang; 
            $ppnKlaim=0;
            if($rPpn['ppn']==1){
           		$ppnKlaim=10/100*$nilaiKlaimPengurang;         	
            }
           $nilaiTot=$nilaiinv-$nilaiKlaimPengurang-$ppnKlaim;
           
           // echo $nilaiinv._.$nilaiKlaimPengurang._.$totalkurang;
            
            $nor+=1;//<td align=right>".number_format($rstr['nilaiinvoice'],0)."</td>
			$tab.="<tr class=rowcontent>
				<td id='noinvoice_".$nor."' align=center value='".$rstr['noinvoice']."'>".$rstr['noinvoice']."</td>
				<td id='kodeorg_".$nor."' align=center value='".$rstr['kodeorg']."'>".$rstr['kodeorg']."</td>
				<td id='tanggal_".$nor."' align=center value='".$rstr['tanggal']."'>".tanggalnormal(substr($rstr['tanggal'],0,10))."</td>
				<td id='noakun_".$nor."' align=center value='".$rstr['nokontrak']."'>".$rstr['nokontrak']."</td>
                                <td>".$optnmCust[$rstr['kodecustomer']]."</td>    
                                    
                                <td align=right>".number_format($nilaiTot,0)."</td>        
				
				";//<td align=left>".$rstr['keterangan']."</td>"
			if($rstr['posting']==0) {
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['noinvoice']."' onclick=\"fillField('".$rstr['noinvoice']."');\" ></td>";
				$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['noinvoice']."' onclick=\"delData('".$rstr['noinvoice']."');\" ></td>";
				//$tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_slave_print_pengihan',event);\" ></td>";
				$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['noinvoice']."' onclick=\"postingData('".$rstr['noinvoice']."');\" ></td>";
				
                                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_slave_print_pengihan_v2',event);\" ></td>";
				
                                 /*$tab.="<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting ".$rstr['noinvoice']."' onclick=\"popUpPosting('No Invoice Afiliasi','".$rstr['noinvoice']."','<div id=formaAfiliasi></div>',event);\" ></td>";*/
			} else {
                $tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."' onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_slave_print_pengihan_v2',event);\" ></td>";
				//$tab.="<td align=center colspan=2><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['noinvoice']."'  onclick=\"masterPDF('keu_penagihanht','".$rstr['noinvoice']."','','keu_slave_print_pengihan',event);\" ></td>";
				$tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted ".$rstr['noinvoice']."' onclick=\"popUpPosting('No Invoice Afiliasi','".$rstr['noinvoice']."','<div id=formaAfiliasi></div>',event);\"  ></td>";
                $tab.="<td></td><td></td>";
                
            }
			$tab.="</tr>"; 
        }
		$skeupenagih="select count(*) as rowd from ".$dbname.".keu_penagihanht where nokontrak!=''  ".$where;
		$qkeupenagih=mysql_query($skeupenagih) or die(mysql_error($conn));
		$rkeupenagih=mysql_fetch_assoc($qkeupenagih);
		$totrows=ceil($rkeupenagih['rowd']/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
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
	
    case'getData':
		$sdata="select distinct * from ".$dbname.".keu_penagihanht 
				where noinvoice='".$param['noinvoice']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		//noinvoice,kodeorg,tanggal,noorder,kodecustomer,nilaiinvoice,nilaippn,jatuhtempo,keterangan,bayarke,debet,kredit,nofakturpajak
		echo $rdata['noinvoice']."###".$rdata['kodeorg']."###".tanggalnormal(substr($rdata['tanggal'],0,10))."###".$rdata['nokontrak']."###".$rdata['kodecustomer']."###".number_format($rdata['nilaiinvoice'],0)."###".number_format($rdata['nilaippn'])."###".tanggalnormal(substr($rdata['jatuhtempo'],0,10))."###".$rdata['bayarke']."###".$rdata['debet']."###".$rdata['kredit']."###".$rdata['nofakturpajak']."###".$rdata['keterangan1']."###".$rdata['keterangan2']."###".$rdata['keterangan3']."###".$rdata['keterangan4']."###".$rdata['keterangan5']."###".$rdata['rupiah1']."###".$rdata['rupiah2']."###".$rdata['rupiah3']."###".$rdata['rupiah4']."###".$rdata['rupiah5']."###".$rdata['matauang']."###".$rdata['kurs']."###".$rdata['keterangan6']."###".$rdata['rupiah6']."###".$rdata['keterangan7']."###".$rdata['rupiah7']."###".$rdata['keterangan8']."###".$rdata['rupiah8']."###".$rdata['ttd']."###".$rdata['jenis']."###".number_format($rdata['kuantitas']);
		break;
	 
    case'getFormNosipb':
        $optSupplierCr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sSuplier="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer order by namacustomer asc";
        $qSupplier=mysql_query($sSuplier) or die(mysql_error($sSupplier));
        while($rSupplier=mysql_fetch_assoc($qSupplier)){
            $optSupplierCr.="<option value='".$rSupplier['kodecustomer']."'>".$rSupplier['namacustomer']."</option>";
        }
        $form="<fieldset style=float: left;>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['NoKontrak']."</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='return tanpa_kutip(event)' style='width:100px' /></td></tr>
			   <tr><td>".$_SESSION['lang']['nmcust']."</td><td><select id=custId style='width:100px'>".$optSupplierCr."</select></td></tr>
			   <tr><td colspan=2><button class=mybutton onclick=findNosipb()>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
		break;
		
	case'getFormAfiliasi':
		$form="<div style='padding-top:20px;'><fieldset style='float: left;'>
               <legend>".$_SESSION['lang']['input']." No Invoice Afiliasi</legend>
               No Invoice Afiliasi&nbsp;<input type=text class=myinputtext id=noafiliasi />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=inputAfiliasi('".$param['noinvoice']."')>".$_SESSION['lang']['save']."</button></fieldset></div>";
        echo $form;
		break;
	
    case'getnosibp':
        //txtfind
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab.="<td>".$_SESSION['lang']['kodecustomer']."</td>";
		$tab.="<td>".$_SESSION['lang']['namacust']."</td>
                    <td>sisa</td>
                    </tr>
                    </thead><tbody>";
		if($param['custId']!=''){
			$whr.=" and a.koderekanan='".$param['custId']."'";
		}
		if($param['txtfind']!=''){
			$whr.=" and a.nokontrak like '%".$param['txtfind']."%'";
		}
		$sdata="select distinct a.*,b.statusberikat from ".$dbname.".pmn_kontrakjual a 
			LEFT JOIN ".$dbname.".pmn_4customer b ON a.koderekanan = b.kodecustomer 
			where nokontrak!='' ".$whr."";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		while($rdata=  mysql_fetch_assoc($qdata)){
		
		
                    $nilaiQty=$rdata['kuantitaskontrak'];
					$nilaiSatuan=$rdata['hargasatuan'];
                   
			#cek kontrak dah terpenuhi atau belum rupiahnya
			/*$sCek="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt where nodok='".$rdata['nokontrak']."' and jumlah>0";
			$qCek=mysql_query($sCek) or die(mysql_error($conn));
			$rCek=mysql_fetch_assoc($qCek);
			//Get Nilai Invoice
			$sInv="select sum(nilaiinvoice) as nilaiinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$rdata['nokontrak']."' and posting=1";
			$qInv=mysql_query($sInv) or die(mysql_error($conn));
			$bInv=mysql_fetch_assoc($qInv);
			#cpo/pk terkirim
			$sTerkirim="select sum(beratbersih) as terkirim from ".$dbname.".pabrik_timbangan 
			            where nokontrak='".$rdata['nokontrak']."' and kodebarang='".$rdata['kodebarang']."'";
			$qTerkirim=mysql_query($sTerkirim) or die(mysql_error($conn));
			$rTerkirim=mysql_fetch_assoc($qTerkirim);
			
			#termin utk ambil persen pertama
			$whrPrsn="kode='".$rdata['kdtermin']."'";
			$optPersen=makeOption($dbname,'pmn_5terminbayar','kode,satu',$whrPrsn);
			#utk isi kode holding di kodeorganisasi
			$whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
			$optKdHo=makeOption($dbname,'organisasi','induk,kodeorganisasi',$whrorg);
			if(($rCek['jumlah']>=$bInv['nilaiinvoice'])&&($rCek['jumlah']!=0)){
				continue;
			}else{
				if(intval($bInv['nilaiinvoice'])==0){			
					$persen=$optPersen[$rdata['kdtermin']];
					$nilInvoice=($rdata['hargasatuan'])*($rdata['kuantitaskontrak'])*($persen/100);
				}else{
					$nilInvoice=(($rdata['hargasatuan'])*($rdata['kuantitaskontrak'])-$bInv['nilaiinvoice'])*(($rTerkirim['terkirim']/$rdata['kuantitaskontrak'])*100);
				}
				$whrCus="kodecustomer='".$rdata['koderekanan']."'";
				$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer',$whrCus);
				$brt="style=cursor:pointer; onclick=setData('".$rdata['nokontrak']."','".$rdata['koderekanan']."','".$optKdHo[$rdata['kodept']]."','".$rdata['matauang']."','".number_format($nilInvoice,0)."','".$rdata['rekening']."')";
				$tab.="<tr ".$brt." class=rowcontent><td>".$rdata['nokontrak']."</td>";
				$tab.="<td>".$rdata['koderekanan']."</td>";
				$tab.="<td>".$optnmcust[$rdata['koderekanan']]."".$rCek['jumlah']."</td></tr>";
			}*/
                    
                        $nilaiKontrak=$rdata['hargasatuan']*$rdata['kuantitaskontrak'];
						// if($rdata['ppn']==1 and $rdata['statusberikat']=='0') {
							// $nilaiKontrak = $nilaiKontrak / 1.1;
						// } else {
							// if($rdata['ppn']==1 and $rdata['statusberikat']=='1') {
								// $nilaiKontrak = $nilaiKontrak / 1.1;
							// }
						// }
						
						#termin pertama
                        $iCek=" select count(*) as jumlahinv,sum(nilaiinvoice + nilaippn) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas from ".$dbname.".keu_penagihanht where nokontrak='".$rdata['nokontrak']."' ";                   
                        $nCek=mysql_query($iCek) or die (mysql_error($conn));
                        $dCek=  mysql_fetch_assoc($nCek);
						$bnykInv=$dCek['jumlahinv'];
						$jumlahRupiah=$dCek['jumnlahnilai'];
						
						if($bnykInv==0){
							$sisaKuantitas=$nilaiQty;
						}else if($bnykInv==1){
							$sisaKuantitas=$nilaiQty;
							if($rdata['ppn']==1 and $rdata['statusberikat']=='1') {
								$nilaiKontrak = $nilaiKontrak / 1.11;
							}
						}else{
							$sisaKuantitas=($nilaiQty -($dCek['jumlahkuantitas']-$nilaiQty));
							if($rdata['ppn']==1 and $rdata['statusberikat']=='1') {
								$nilaiKontrak = $nilaiKontrak / 1.11;
							}
						}
						
						$sisaRupiah=$nilaiKontrak-$jumlahRupiah;
                       
						// $sisaKuantitas=$bnykInv;
						
						
					   
                            // if($dCek['jumlahkuantitas']<=0){
                                    // $sisaKuantitas=($jumlahRupiah)/($nilaiSatuan);
                            // }else{
                                    // $sisaKuantitas=$nilaiQty-$dCek['jumlahkuantitas'];
                            // }
                            
                            
                            
                        
                        if($bnykInv>0)
                        {
                            $masuk=1;
                            $nilInvoice=$sisaRupiah;
                        }
                        else
                        {
                            $optPersen=makeOption($dbname,'pmn_5terminbayar','kode,satu',$whrPrsn);
                            $persen=$optPersen[$rdata['kdtermin']];
                            $nilInvoice=($rdata['hargasatuan'])*($rdata['kuantitaskontrak'])*($persen/100);
                        }
						
                    //cek apakah sudah ada termin bayar atau belum
                        //buat if, jika sudah ada maka pake sisa termin
                 
                    
                    
                        
                        
                        #sisa termin
                        
                        $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
			$optKdHo=makeOption($dbname,'organisasi','induk,kodeorganisasi',$whrorg);
                        //exit("Error:$persen");
                        
                        $whrCus="kodecustomer='".$rdata['koderekanan']."'";
                        $optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer',$whrCus);
                        
                        
                        // if($rdata['ppn']==0)
                        // {
                            // $ppnnya=0;
                        // }
                        // else
                        // {
                            // $ppnnya=10/100*$nilInvoice;
                        // }
                        if($rdata['ppn']==1 and $rdata['statusberikat']=='0') {
							$nilInvoice = $nilInvoice / 1.11;
							$ppnnya = $nilInvoice * 0.11;
						} else {
							if($rdata['ppn']==1 and $rdata['statusberikat']=='1' and $bnykInv==0) {
								$nilInvoice = $nilInvoice / 1.11;
							}
							$ppnnya = 0;
						}
                        
                        
                        if($sisaRupiah!=0)
                        {
                            $brt="style=cursor:pointer; onclick=setData('".$rdata['nokontrak']."','".$rdata['koderekanan']."','".$optKdHo[$rdata['kodept']]."','".$rdata['matauang']."','".number_format($nilInvoice,0)."','".$rdata['rekening']."','".number_format($ppnnya,0)."','".number_format($sisaKuantitas,0)."')";
                        }
                        else
                        {
                            $brt="";
                        }
                        
                        $tab.="<tr ".$brt." title='kontrak sudah terpenuhi' class=rowcontent><td>".$rdata['nokontrak']."</td>";
                        $tab.="<td>".$rdata['koderekanan']."</td>";
                        $tab.="<td>".$optnmcust[$rdata['koderekanan']]."".$rCek['jumlah']."</td>";
                        $tab.="<td>".$sisaRupiah."</td>";
			
		}
		$tab.="</tbody></table>";
		echo $tab;
		break;
		
	case'inputNoAfiliasi':
            $sUpdate="update ".$dbname.".keu_penagihanht set noinvoice_afiliasi='".$param['noafiliasi']."' where noinvoice='".$param['noinvoice']."'";
		if(!mysql_query($sUpdate)){
            exit("error: gak berhasil".mysql_error($conn)."___".$sdel);
        }
	break;
	
    case'delData':
        $sdel="delete from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
        if(!mysql_query($sdel)){
            exit("error: gak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;
	
    case'postingData':
		$sdata="select distinct * from ".$dbname.".keu_penagihanht where noinvoice='".$param['noinvoice']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		$roc=mysql_num_rows($qdata);
		#=== Cek if posted ===
		$error0 = "";
		if($rdata['posting']==1) {
			$error0 .= $_SESSION['lang']['errisposted'];
		}
		if($error0!='') {
			echo "Data Error :\n".$error0;
			exit;
		}
		#====cek periode
		$tgl = str_replace("-","",$rdata['tanggal']);
		$sprd = "select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg = '".$rdata['kodeorg']."' and tutupbuku = '0' order by tanggalmulai ASC LIMIT 1";
		$qprd=mysql_query($sprd) or die(mysql_error($conn));
		$rprd=mysql_fetch_assoc($qprd);
		$tgl2 = str_replace("-","",$rprd['tanggalmulai']);
		if($tgl < $tgl2){
			exit("Error:Date beyond active period");
		}
		// if($_SESSION['org']['period']['start']>$tgl)
			// exit('Error:Date beyond active period');
		#=== Cek if data not exist ===
		$error1 = "";
		if($roc==0) {
			$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
		}
		if($error1!='') {
			echo "Data Error :\n".$error1;
			exit;
		}
		
		/**
		 * Parameter Jurnal
		 */
		// Get Parameter Claim
		$qClaim = selectQuery($dbname,'keu_5parameterjurnal','*',
			"kodeaplikasi='CLAIM' and jurnalid='SLE'");
		$resClaim = fetchData($qClaim);
		if(empty($resClaim)) exit("Warning: Parameter Jurnal CLAIM / SLE belum ada\nSilahkan hubungi IT");
		
		// Get Parameter Ppn
		$qPpn = selectQuery($dbname,'keu_5parameterjurnal','*',
			"kodeaplikasi='SPPN' and jurnalid='SLE'");
		$resPpn = fetchData($qPpn);
		if(empty($resPpn)) exit("Warning: Parameter Jurnal SPPN / SLE belum ada\nSilahkan hubungi IT");
		
		/**
		 * Pembentukan Jurnal
		 */
		// Get Last No Jurnal
		$yy=tanggalnormal(substr($rdata['tanggal'],0,10));
		$isyy=tanggalsystem($yy);
		$norjunal=$isyy."/".$rdata['kodeorg']."/PNJ/";
		$snojr="select max(substr(nojurnal,19,7)) as nourut from ".$dbname.".keu_jurnalht where nojurnal like '".$norjunal."%'";
		
		// Nomor Urut
		$qnojr=mysql_query($snojr) or die(mysql_error($conn));
		$rnojr=mysql_fetch_assoc($qnojr);
		$nourut=addZero((intval($rnojr['nourut'])+1), '3');
		$nojurnal=$norjunal.$nourut;
		
		// Potongan
		$nilaiKlaimPengurang=$rdata['rupiah1']+$rdata['rupiah2']+$rdata['rupiah3']
					+$rdata['rupiah4']+$rdata['rupiah5']+$rdata['rupiah6']+$rdata['rupiah7']-$rdata['rupiah8'];
		$ppnKlaim=0;
		if($rdata['nilaippn']>0) {
			$ppnKlaim=10/100*$nilaiKlaimPengurang;
		}
		$piutangKurang = $nilaiKlaimPengurang + $ppnKlaim;
		
		// Nilai
		$jumlahUM = $rdata['nilaiinvoice'];
		$jumlahPpn = $rdata['nilaippn'];
		$jumlahPiutang = $rdata['nilaiinvoice']+$rdata['nilaippn'];
		
		// Total
		$total = $jumlahPiutang + abs($piutangKurang);
		
		// Nama Customer
		$whrCus="kodecustomer='".$rdata['kodecustomer']."'";
		$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer',$whrCus);
		
		// Jurnal Header
		$dataH = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => 'PNJ',
			'tanggal' => $isyy,
			'tanggalentry' => date('Y-m-d'),
			'posting' => 1,
			'totaldebet' => $total,
			'totalkredit' => $total,
			'amountkoreksi' => 0,
			'noreferensi' => $rdata['noinvoice'],
			'autojurnal' => 1,
			'matauang' => 'IDR',
			'kurs' => 1,
			'revisi' => 0,
		);
		$colH = array();
		foreach($dataH as $key=>$row) {
			$colH[] = $key;
		}
		
		// Jurnal Detail
		$dataD = array();
		$colD = array('nojurnal','tanggal','nourut','noakun','keterangan',
			'jumlah','matauang','kurs','kodeorg','kodebarang','kodecustomer',
			'noreferensi','nodok','revisi','nik','kodesupplier');
		
		// Piutang Pihak Ketiga - Debet
		$dataD[] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $isyy,
			'nourut' => 1,
			'noakun' => $rdata['debet'],
			'keterangan' => 'PIUTANG '.$optnmcust[$rdata['kodecustomer']].
				' atas Invoice '.$rdata['noinvoice'].' u/ Kontrak '.$rdata['nokontrak'].
				' sebanyak '.$rdata['kuantitas'],
			'jumlah' => $jumlahPiutang,
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => $rdata['kodebarang'],
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => $rdata['nokontrak'],
			'revisi' => 0,
			'nik' => '',
			'kodesupplier' => ''
		);
		
		// Uang Muka Penjualan - Kredit
		$dataD[] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $isyy,
			'nourut' => 2,
			'noakun' => $rdata['kredit'],
			'keterangan' => 'PIUTANG '.$optnmcust[$rdata['kodecustomer']].
				' atas Invoice '.$rdata['noinvoice'].' u/ Kontrak '.$rdata['nokontrak'].
				' sebanyak '.$rdata['kuantitas'],
			'jumlah' => $jumlahUM*(-1),
			'matauang' => 'IDR',
			'kurs' => 1,
			'kodeorg' => $rdata['kodeorg'],
			'kodebarang' => $rdata['kodebarang'],
			'kodecustomer' => $rdata['kodecustomer'],
			'noreferensi' => $rdata['noinvoice'],
			'nodok' => $rdata['nokontrak'],
			'revisi' => 0,
			'nik' => '',
			'kodesupplier' => ''
		);
		
		$noUrut = 3;
		if($jumlahPpn>0) {
			// Hutang Ppn Penjualan - Kredit
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resPpn[0]['noakunkredit'],
				'keterangan' => 'PPn Keluaran dari '.$rdata['noinvoice'],
				'jumlah' => $jumlahPpn*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
			$noUrut++;
		}
		
		if($nilaiKlaimPengurang != 0) {
			// Claim 
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut,
				'noakun' => $resClaim[0]['noakundebet'],
				'keterangan' => 'Claim dari Invoice '.$rdata['noinvoice'],
				'jumlah' => $nilaiKlaimPengurang,
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
			
			// Pengurang / Penambah Piutang
			$dataD[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $isyy,
				'nourut' => $noUrut+1,
				'noakun' => $resClaim[0]['noakunkredit'],
				'keterangan' => 'Pengurang / Penambah Piutang Invoice '.$rdata['noinvoice'],
				'jumlah' => $piutangKurang*(-1),
				'matauang' => 'IDR',
				'kurs' => 1,
				'kodeorg' => $rdata['kodeorg'],
				'kodebarang' => '',
				'kodecustomer' => $rdata['kodecustomer'],
				'noreferensi' => $rdata['noinvoice'],
				'nodok' => $rdata['nokontrak'],
				'revisi' => 0,
				'nik' => '',
				'kodesupplier' => ''
			);
			
			if($ppnKlaim != 0) {
				// Ppn Klaim
				$dataD[] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $isyy,
					'nourut' => $noUrut+2,
					'noakun' => $resClaim[0]['sampaidebet'],
					'keterangan' => 'PPn Claim dari Invoice'.$rdata['noinvoice'],
					'jumlah' => $ppnKlaim,
					'matauang' => 'IDR',
					'kurs' => 1,
					'kodeorg' => $rdata['kodeorg'],
					'kodebarang' => '',
					'kodecustomer' => $rdata['kodecustomer'],
					'noreferensi' => $rdata['noinvoice'],
					'nodok' => $rdata['nokontrak'],
					'revisi' => 0,
					'nik' => '',
					'kodesupplier' => ''
				);
			}
		}
		
		// Query
		$sIns = insertQuery($dbname,'keu_jurnalht',$dataH,$colH);
		$sInsD = insertQuery($dbname,'keu_jurnaldt',$dataD, $colD);
		
		// Insert Header
		if(mysql_query($sIns)){
			if(!mysql_query($sInsD)){
				// Rollback
				$delH = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
				if(!mysql_query($delH)) {
					exit("Rollback Error: ".mysql_error($conn)."___".$delH);
				} else {
					exit("Insert Detail Error: ".mysql_error()."___".$sInsD);
				}
			}else{
				$supd="update ".$dbname.".keu_penagihanht set posting=1,jurnalstatus=1 where noinvoice='".$rdata['noinvoice']."'";
				if(!mysql_query($supd)){
					// Rollback
					$delH = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
					if(!mysql_query($delH)) {
						exit("Rollback Error: ".mysql_error($conn)."___".$delH);
					} else {
						exit("Update Penagihan Error: ".mysql_error($conn)."___".$supd);
					}
				}
			}
		} else {
			exit("Insert Header Error: ".mysql_error($conn)."___".$sinsert);
		}
		break;
}

function generateNoInvoice($nokontrak,$tgl){
	global $dbname;
	global $conn;
	#no invoice
	$tgldt=explode("-",$tgl);
	$bulan = $tgldt[1];
	$thn=date('Y');
	$sPt="select distinct kodebarang,kodept from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
	$qPt=mysql_query($sPt) or die(mysql_error($conn));
	$rPt=mysql_fetch_assoc($qPt);
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	if($rPt['kodept']=='API') {
		$invPT = 'AMP';
	} else {
		$invPT = $rPt['kodept'];
	}
	$ql="select `noinvoice` from ".$dbname.".`keu_penagihanht` where kodept = '".$invPT."' and left(noinvoice,3) = '".$invPT."' and left(tanggal,4) = '".$tgldt[2]."'
		and right(noinvoice,4) = '".$tgldt[2]."' order by noinvoice desc limit 1";
	
    $qr=mysql_query($ql) or die('error: '.mysql_error());
	$data = mysql_fetch_object($qr);
	$countNoInvoice = substr($data->noinvoice,4,3);
	//$countNoInvoice = mysql_num_rows($qr);
	$noInvoice = $rPt['kodept']."/".addZero($countNoInvoice+1,3)."/JKT/".$resultRomawi."/".$tgldt[2];
	return $noInvoice;
}