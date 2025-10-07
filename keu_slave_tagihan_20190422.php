<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;
$optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
//cari nama orang
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
$res=mysql_query($str);
while($bar= mysql_fetch_object($res))
{
   $nama[$bar->karyawanid]=$bar->namakaryawan;
}    

switch($proses) {
    # Daftar Header
    case 'showHeadList':    
        $where = "a.kodeorg='".$_SESSION['org']['kodeorganisasi']."' and updateby='".$_SESSION['standard']['userid']."'";
        if($_SESSION['empl']['kodejabatan']==5)$where = "a.kodeorg like '%' and updateby like '%'";
        if(isset($param['where'])) {
                  $tmpW = str_replace('\\','',$param['where']);
            $arrWhere = json_decode($tmpW,true);
            if(!empty($arrWhere)) {
				foreach($arrWhere as $key=>$r1) {
					if($r1[0]=='namasupplier') {
						$where .= " and b.".$r1[0]." like '%".$r1[1]."%'";
					} else {
						$where .= " and a.".$r1[0]." like '%".$r1[1]."%'";
					}
				}
            } 
        }
        
        # Header
        $header = array(
            $_SESSION['lang']['noinvoice'],$_SESSION['lang']['noinvoice']." Supplier",$_SESSION['lang']['pt'],$_SESSION['lang']['tanggal'],'Last Update',
			$_SESSION['lang']['nopo'],$_SESSION['lang']['supplier'],$_SESSION['lang']['keterangan'],
			$_SESSION['lang']['subtotal'],'postingby'
        );
        
        # Content
        $cols = "a.noinvoice,a.noinvoicesupplier,a.kodeorg,a.tanggal,a.updateby,a.nopo,
			b.namasupplier,a.keterangan,a.nilaiinvoice,a.postingby,a.posting";
        $order="a.posting,a.tanggal desc";
		
		$queryRow = "select count(*) as rows";
		$query = " from ".$dbname.".keu_tagihanht a 
			left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid	
			where ".$where." order by ".$order;
		//print_r($query);
		$queryRow .= $query;
		if(!is_null($param['shows'])) {
			if(!is_null($param['page'])) {
			   $startFrom = ($param['page']-1) * $param['shows'];
			} else {
			   $startFrom = 0;
			}
			$query .= " limit ".$startFrom.",".$param['shows'];
		}
		$query = "select ".$cols.$query;
		$tmpTotal = fetchData($queryRow);
        $data = fetchData($query);
        $totalRow = $tmpTotal[0]['rows'];
		
		// Get Akun Ppn
		$qAkun = selectQuery($dbname,'setup_parameterappl','nilai',
			"kodeaplikasi='TX' and kodeparameter='PPNINV'");
		$resAkun = fetchData($qAkun);
		
		// List of Invoice
		$listInv = '';
		foreach($data as $key=>$row) {
			if(!empty($listInv)) $listInv.= ",";
			$listInv .= "'".$row['noinvoice']."'";
		}
		
		// Sum Akun Ppn (Detail Tagihan)
		if(empty($resAkun) or empty($listInv)) {
			$optDet = array();
		} else {
			$optDet = makeOption($dbname,'keu_tagihandt',"noinvoice,nilai",
								 "noinvoice in (".$listInv.") and noakun='".$resAkun[0]['nilai']."'");
		}
		
        foreach($data as $key=>$row) {
			// Add Ppn
			if(isset($optDet[$row['noinvoice']]))
				$row['nilaiinvoice'] += $optDet[$row['noinvoice']];
            if($row['posting']==1) {
				$data[$key]['switched']=true;
            }
            unset($data[$key]['posting']);            
            $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
            $data[$key]['nilaiinvoice'] = number_format($row['nilaiinvoice'],2);
            $data[$key]['updateby'] = $nama[$row['updateby']];
            $data[$key]['postingby'] = isset($nama[$row['postingby']])? $nama[$row['postingby']]: '-';
        }
    //    foreach($data as $c=>$key) {
    //        $sort_noaku[] = $key['tanggal'];
    //        $sort_tangg[] = $key['noinvoice'];
    //    }
    //    array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $data);
    
    //    array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $isidata);
    
        
        # Make Table
        $tHeader = new rTable('headTable','headTableBody',$header,$data);
        $tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
            
    
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['kodejabatan']==117 or $_SESSION['empl']['kodejabatan']==119){
                    $tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
            }
            else{//hanya HO dan region yang boleh menghapus
                $tHeader->addAction('','Delete','images/'.$_SESSION['theme']."/delete.png");
            }
            
        $tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
        $tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
        $tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
		$tHeader->_actions[3]->addAttr('event');
        $tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
		$tHeader->_switchException = array('detailPDF');
        if(isset($param['where'])) {
            $tHeader->setWhere($arrWhere);
        }
        
        # View
        $tHeader->renderTable();
        break;
    # Form Add Header
    case 'showAdd':
        // View
        echo formHeader('add',array());
        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Form Edit Header
    case 'showEdit':
        $query = selectQuery($dbname,'keu_tagihanht',"*","noinvoice='".$param['noinvoice']."'");
        $tmpData = fetchData($query);
        $data = $tmpData[0];
        $data['tanggal'] = tanggalnormal($data['tanggal']);
        $data['jatuhtempo'] = tanggalnormal($data['jatuhtempo']);
        echo formHeader('edit',$data);
        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Proses Add Header
    case 'add':
		$data = $_POST;
		if($data['tipeinvoice']=='po') {
			$optPO = makeOption($dbname,'log_poht','nopo,kodesupplier',"stat_release=1 and nopo='".$data['nopo']."'");
			
                            //jmlh po di dari po
                        $sCek2="select distinct  nilaipo as jmlhpo,ppn from ".$dbname.".log_poht where nopo='".$data['nopo']."' ";
                        $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                        $rCek2=mysql_fetch_assoc($qCek2);
            
		} else if($data['tipeinvoice']=='sj') {
			$optPO = makeOption($dbname,'log_suratjalanht','nosj,expeditor');
            $rCek2['jmlhpo']=0;
			$rCek2['ppn']=0;
		} else if($data['tipeinvoice']=='ns') {
			$optPO = makeOption($dbname,'log_konosemenht','nokonosemen,shipper');
            $rCek2['jmlhpo']=0;
			$rCek2['ppn']=0;
		} 
                else if($data['tipeinvoice']=='bykrm') {
			$optPO = makeOption($dbname,'log_biayakirim','nodok,kodetrp');
                        $rCek2['jmlhpo']=0;
			$rCek2['ppn']=0;
                        
                        $sCek2="select distinct jumlah as jmlhpo from ".$dbname.".log_biayakirim where nodok='".$data['nopo']."' ";           
                        $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                        $rCek2=mysql_fetch_assoc($qCek2);
                        
		}
                else {
                    
                    
                    
                    $sCek2="select distinct nilaikontrak as jmlhpo from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."' ";           
                    $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                    $rCek2=mysql_fetch_assoc($qCek2);
                    
                    #ppn
                    $iPn="select sum(nilai) as jumppn from ".$dbname.".log_spk_tax where noakun='1160100' and notransaksi='".$data['nopo']."' ";
                    $nPn=  mysql_query($iPn) or die (mysql_error($conn));
                    $dPn=  mysql_fetch_assoc($nPn);
                    #pph
                    $iPh="select sum(nilai) as jumpph from ".$dbname.".log_spk_tax where noakun!='1160100' and notransaksi='".$data['nopo']."' ";
                    $nPh=  mysql_query($iPh) or die (mysql_error($conn));
                    $dPh=  mysql_fetch_assoc($nPh);
                      $rCek2['jmlhpo']=$rCek2['jmlhpo']+$dPn['jumppn']-$dPh['jumpph'];
                    
                                $optPO = makeOption($dbname,'log_spkht','notransaksi,koderekanan');
		}
                //$a=$rCek2['jmlhpo'];
                //exit("Error:$a._.$b._.$c");
                
		#$optPO = makeOption($dbname,'log_poht','nopo,kodesupplier',"nopo='".$data['nopo']."'");
		
		// Error Trap
		$warning = "";
		if($data['noinvoice']=='') {$warning .= "Invoice number is obligatory\n";}
		if($data['tanggal']=='') {$warning .= "Date is obligatory\n";}
		if($warning!=''){echo "Warning :\n".$warning;exit;}
		
		$data['tipeinvoice'] = substr($data['tipeinvoice'],0,1);
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['nilaiinvoice'] = str_replace(',','',$data['nilaiinvoice']);
		$data['uangmuka'] = str_replace(',','',$data['uangmuka']);
		//$data['nilaippn'] = str_replace(',','',$data['nilaippn']);
		//$data['nilaippn'] = $rCek2['ppn'];
		$data['nilaippn'] = 0;
		if($data['jatuhtempo']!='') {
			$data['jatuhtempo'] = tanggalsystem($data['jatuhtempo']);
		} else {
			$data['jatuhtempo'] = '0000-00-00';
		}
		if(empty($optPO)) {
			exit('Error: Supplier ID is not defined: '.$data['nopo']);
		}
		$data['kodesupplier'] = isset($optPO[$data['nopo']])? $optPO[$data['nopo']]: '';
		$data['updateby'] = $_SESSION['standard']['userid'];
        
        //jmlh po di invoice
        $sCek="select distinct sum(nilaiinvoice) as jmlhinvoice from ".$dbname.".keu_tagihanht where nopo='".$data['nopo']."' "
                . " and tipeinvoice='".$data['tipeinvoice']."'";
        
        $qCek=mysql_query($sCek) or die(mysql_error($conn));
        $rCek=mysql_fetch_assoc($qCek);
		
		//jmlh ppn di invoice
        $sPpn="select distinct sum(a.nilai) as jmlhppn from ".$dbname.".keu_tagihandt a
			left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
			where b.nopo='".$data['nopo']."' and tipeinvoice='".$data['tipeinvoice']."'";
    
        $qPpn=mysql_query($sPpn) or die(mysql_error($conn));
        $rPpn=mysql_fetch_assoc($qPpn);
		$jmlInv = $rCek['jmlhinvoice'] + $rPpn['jmlhppn'];
                $a=$jmlInv;
                $b=$data['nilaiinvoice'];
                $c=$rCek2['jmlhpo'];
                //exit("Error:$a._.$b._.$c");
		 //print_r(jmlInv+'___'+$data['nilaiinvoice']+'____'+$rCek2['jmlhpo']);return false;		
         if(($jmlInv+$data['nilaiinvoice'])>$rCek2['jmlhpo'])
        {
            exit("Warning: Invouce amount greater than PO/Contract amount");
        }
        
		// Insert Header
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$query = insertQuery($dbname,'keu_tagihanht',$data,$cols);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
		} else {
			//if($rCek2['ppn']>0) { // Insert Ppn jika > 0
			//	// Get Akun Ppn
			//	$qAkun = selectQuery($dbname,'setup_parameterappl','nilai',
			//						 "kodeaplikasi='TX' and kodeparameter='PPNINV'");
			//	$resAkun = fetchData($qAkun);
			//	if(!empty($resAkun)) {
			//		$nilaiPpn = $rCek2['ppn'] * $data['nilaiinvoice'] / $rCek2['jmlhpo'];
			//		$dataD = array($data['noinvoice'],$resAkun[0]['nilai'],$nilaiPpn);
			//		$qIns = insertQuery($dbname,'keu_tagihandt',$dataD);
			//		if(!mysql_query($qIns)) {
			//			$qRB = deleteQuery($dbname,'keu_tagihanht',"noinvoice='".$data['noinvoice']."'");
			//			$detErr = "Insert Ppn Error: ".mysql_error();
			//			if(!mysql_query($qRB)) {
			//				exit("Rollback Error: ".mysql_error());
			//			} else {
			//				exit($detErr);
			//			}
			//		}
			//	}
			//}
		}
		break;
    # Proses Edit Header
    case 'edit':
		$data = $_POST;
		$where = "noinvoice='".$data['noinvoice']."'";
		unset($data['noinvoice']);
                if($data['tipeinvoice']=='po') {
                    $optPO = makeOption($dbname,'log_poht','nopo,kodesupplier',"stat_release=1 and nopo='".$data['nopo']."'");
                    //jmlh po di dari po
                    $sCek2="select distinct  nilaipo as jmlhpo,ppn from ".$dbname.".log_poht where nopo='".$data['nopo']."' ";
                    $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                    $rCek2=mysql_fetch_assoc($qCek2);
                } else if($data['tipeinvoice']=='sj') {
                    $optPO = makeOption($dbname,'log_suratjalanht','nosj,expeditor');
                    //jmlh po di dari po
                    //$sCek2="select distinct  biaya as jmlhpo from ".$dbname.".log_pengiriman_ht where nosj='".$data['nopo']."' ";
                    //$qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                    //$rCek2=mysql_fetch_assoc($qCek2);
                    $rCek2['jmlhpo']=0;
                } else {
                    $sCek2="select distinct nilaikontrak as jmlhpo from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."' ";
                    $qCek2=mysql_query($sCek2) or die(mysql_error($conn));
                    $rCek2=mysql_fetch_assoc($qCek2);
                    $optPO = makeOption($dbname,'log_spkht','notransaksi,koderekanan');
                    $rCek2['ppn']=0;
                }
                $data['nilaippn'] = $rCek2['ppn'];
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['jatuhtempo'] = tanggalsystem($data['jatuhtempo']);
		$data['tipeinvoice'] = substr($data['tipeinvoice'],0,1);
		$data['nilaiinvoice'] = str_replace(',','',$data['nilaiinvoice']);
		$data['uangmuka'] = str_replace(',','',$data['uangmuka']);
		$data['updateby'] = $_SESSION['standard']['userid'];
		$query = updateQuery($dbname,'keu_tagihanht',$data,$where);
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
		}
		break;
    case 'delete':
		$where = "noinvoice='".$param['noinvoice']."'";
		$query = "delete from `".$dbname."`.`keu_tagihanht` where ".$where;
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		break;
    case 'updpo':
		$pokontrak = $_POST['pokontrak'];
		if($pokontrak=='po') {
			$resPO = makeOption($dbname,'log_poht','nopo,nopo',"stat_release=1",'0',true);
		} if($pokontrak=='sj') {
			$resPO = makeOption($dbname,'log_pengiriman_ht','nosj,nosj','0',true);
		} else {
			$resPO = makeOption($dbname,'log_spkht','notransaksi,notransaksi',
			"kodeorg='".$_SESSION['empl']['lokasitugas']."'",'0',true);
		}
		
		echo json_encode($resPO);
		break;
    case 'updInvoice':
		# Check existing PO
		$query = selectQuery($dbname,'keu_tagihanht','nilaiinvoice',"nopo='".$_POST['nopo']."'");
		$res = fetchData($query);
		if(!empty($res)) {
			echo $res[0]['nilaiinvoice'];
		}
		break;
    case'getPo':
        $jenisInvoice = $_POST['jnsInvoice'];
		
        
       
        
		// Get Akun Ppn
		$qPpn = selectQuery($dbname,'setup_parameterappl','nilai',
			"kodeaplikasi='TX' and kodeparameter='PPNINV'");
		$resPpn = fetchData($qPpn);
		$akunPpn = '';
		if(!empty($resPpn)) $akunPpn = $resPpn[0]['nilai'];
		
        $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
        $dat="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $dat.="<div style=overflow:auto;width:100%;height:310px;>";
        $dat.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
        $dat.="<tr class='rowheader'><td>No.</td>";
        $rPo['ppn']=0;
		
		$where = '';
		switch($jenisInvoice) {
                    
                    
                        case'bykrm':
                            
                            //if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
                            //{
                                $sortHold=" and right(nodok,3)='".$_SESSION['empl']['kodeorganisasi']."'";
                            //}
                            
                            if($param['txtfind']!='')
                            {
                                    $where=" and nodok like '%".$param['txtfind']."%'";
                            } 
                            
                            $sPo="select * from ".$dbname.".log_biayakirim where 1=1 ".$where.""
                                    . " and posting=1 ".$sortHold."  order by updatetime desc ";
                            //$nBy=  mysql_query($iBy) or die (mysql_error($conn));
                            $dat.="<td>".$_SESSION['lang']['nopo']."</td>";
                            $dat.="<td>".$_SESSION['lang']['kodebarang']."</td>";
                            $dat.="<td>".$_SESSION['lang']['namabarang']."</td>";
                            $dat.="<td>Transportir</td>";
                            $dat.="<td>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>";
                            
                        break;
                    
			case 'po':
				if($param['txtfind']!='')
				{
					$where=" and nopo like '%".$param['txtfind']."%'";
				}
				//$where.=" and closed=0";
				$addlokal=" and lokalpusat=0 ";
				$addkdorg=" and kodeorg='".$_SESSION['org']['kodeorganisasi']."'";
				if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
				{
					$addlokal=" and lokalpusat=1 ";
					$addkdorg="";
				}
				//$sPo="select distinct nopo,(subtotal + ppn) as nilaipo,ppn,kodesupplier,stat_release,matauang,nilaidiskon from ".$dbname.".log_poht where 
				$sPo="select distinct nopo,(subtotal + ppn + pbbkb) as nilaipo,ppn,kodesupplier,stat_release,matauang,nilaidiskon from ".$dbname.".log_poht where 
                                      kodeorg='".$_SESSION['org']['kodeorganisasi']."' ".$where.$addlokal."  order by tanggal desc ";
									                      /* //and nopo not in (select distinct nopo from ".$dbname.".keu_tagihanht where tipeinvoice='p') ".$where." and kodeorg='".$_SESSION['org']['kodeorganisasi']."'order by tanggal desc "; */
				//print_r($sPo);
				// Table Header
				$dat.="<td> ".$_SESSION['lang']['nopo']."</td>";
				$dat.="<td>".$_SESSION['lang']['namasupplier']."</td>";
				$dat.="<td>".$_SESSION['lang']['matauang']."</td></tr></thead><tbody>"; 
				break;
                               
                                
			case 'sj':
				if($param['txtfind']!='')
				{
					$where="where nosj like '%".$param['txtfind']."%'";
				}
				$sPo="select distinct nosj as nopo,expeditor as kodesupplier from ".$dbname.". log_suratjalanht 
					   ".$where."  order by nosj desc";
				
				// Table Header
				$dat.="<td>".$_SESSION['lang']['nosj']."</td>";
				$dat.="<td>".$_SESSION['lang']['expeditor']."</td></tr></thead><tbody>";
				break;
			case 'ns':
				if($param['txtfind']!='')
				{
					$where="where nokonosemen like '%".$param['txtfind']."%'";
				}
				$sPo="select distinct nokonosemen as nopo,shipper as kodesupplier from ".$dbname.". log_konosemenht 
					   ".$where."  order by nokonosemen desc";
				
				// Table Header
				$dat.="<td>".$_SESSION['lang']['nokonosemen']."</td>";
				$dat.="<td>".$_SESSION['lang']['shipper']."</td></tr></thead><tbody>";
				break;
			default:
				if($param['txtfind']!='')
				{
					$where=" and notransaksi like '%".$param['txtfind']."%'";
				}
			   if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
			   {
				   $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier from ".$dbname.".log_spkht where kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')  ".$where."  order by tanggal desc";
			   }
			   else
			   {
				   $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier from ".$dbname.".log_spkht where  kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') ".$where."   order by tanggal desc";
			   }
                            $dat.="<td>".$_SESSION['lang']['kontrak']."</td>";
                            $dat.="<td>".$_SESSION['lang']['kontraktor']."</td></tr></thead><tbody>";
			   break;
        }
        
        $qPo=mysql_query($sPo) or die(mysql_error($conn));
		$no=0;
        while($rPo=mysql_fetch_assoc($qPo)){
            
                if($jenisInvoice=='bykrm') {
                   
                    
                    #cek sudah pernah ada inv apa belum
                    $sCek="select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby "
                    . "from ".$dbname.".keu_tagihanht where nopo='".$rPo['nodok']."' and tipeinvoice='b' order by noinvoice";
                    $qCek=  mysql_query($sCek) or die(mysql_error($conn));
                    $rCek=  mysql_fetch_assoc($qCek);
                    if($rCek['jmlhinvoice']!='')
                    {
                        $rPo['jumlah']=$rPo['jumlah']-$rCek['jmlhinvoice'];
                    }
                    $nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);
                        
                    if($rPo['jumlah']>0)
                    {
                        $whbrg="kodebarang='".$rPo['kodebarang']."'";
                        $no+=1;
                        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nodok']."','";
                                $dat.=isset($rPo['jumlah'])? $rPo['jumlah']: 0;
                                $dat.="','".$param['jnsInvoice']."','";
                                $dat.="','".$optNmsupp[$rPo['kodetrp']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                        $dat.="<td>".$rPo['nodok']."</td>";  
                        $dat.="<td>".$rPo['kodebarang']."</td>";
                        $dat.="<td>".$nmBrg[$rPo['kodebarang']]."</td>";
                        $dat.="<td>".$optNmsupp[$rPo['kodetrp']]."</td>";
                        $dat.="<td>".number_format($rPo['jumlah'])."</td></tr>";
                    }
                }
            
                
                
	        elseif($jenisInvoice=='po') {
                    if($rPo['nilaidiskon']==''){
                        $rPo['nilaidiskon']=0;
                    }
                    $nilPo=($rPo['nilaipo']-$rPo['nilaidiskon']);
                    $rPo['nilaipo']=$nilPo;
                $sCek="select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby "
                    . "from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='p' order by noinvoice";
				//print_r($sCek);	
				//print_r('<br/><br/>');	
                $qCek=  mysql_query($sCek) or die(mysql_error($conn));
                $rCek=  mysql_fetch_assoc($qCek);
                if($rCek['jmlhinvoice']!=''){
                    $rPo['nilaipo']=$rPo['nilaipo']-$rCek['jmlhinvoice'];
                    $rPo['ppn']=$rPo['ppn']-$rCek['jmlppn'];
                }
				
				// Get Kurs from Setup Mata Uang
				$qKurs = selectQuery($dbname,'setup_matauangrate','*',
									 "daritanggal<='".tanggalsystem($param['tanggal'])."' and
									 kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
				$kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
				
                if($rPo['nilaipo']>0) {
                    if($rPo['stat_release']==1) {
						$no+=1;
						$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
						$dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
						$dat.="','".$param['jnsInvoice']."','";
						$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
						$dat.="','".$optNmsupp[$rPo['kodesupplier']]."','".
							$rPo['matauang']."','".$kurs."')\" style='pointer:cursor;'>";
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$rPo['nopo']."</td>";
						$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td>";
						$dat.="<td>".$rPo['matauang']."</td></tr>";
					}
                }
                
            } elseif($jenisInvoice=='sj') {
                $no+=1;
                $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
                            $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
                            $dat.="','".$param['jnsInvoice']."','";
                            $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
                            $dat.="','".$optNmsupp[$rPo['kodesupplier']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                $dat.="<td>".$rPo['nopo']."</td>";
                $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td></tr>";
            } else {
				$notransaksi = $rPo['nopo'];
				
				// Get Tax
				$optTax = makeOption($dbname,'log_spk_tax','noakun,nilai',
					"notransaksi='".$notransaksi."' and kodeorg='".$rPo['kodeorg']."'");
				
				// Nilai Invoice ditambahkan dengan Ppn dan dikurangi PPh
				foreach($optTax as $noakun=>$nilai) {
					if($akunPpn==$noakun) {
						$rPo['nilaipo'] += $nilai;
					} else {
						$rPo['nilaipo'] -= $nilai;
					}
				}
				
				$no+=1;
                $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
                            $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
                            $dat.="','".$param['jnsInvoice']."','";
                            $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
                            $dat.="','".$optNmsupp[$rPo['kodesupplier']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                $dat.="<td>".$rPo['nopo']."</td>";
                $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td></tr>";
			}
        }
        $dat.="</tbody></table></div></fieldset>";
        echo $dat;
        break;
    default:
	break;
}

function formHeader($mode,$data) {
    global $dbname;
    
    # Default Value
    if(empty($data)) {
		$data['noinvoice'] = date('Ymdhis');
		$data['noinvoicesupplier'] = '';
		$data['nilaiinvoice'] = '0';
		$data['noakun'] = '';
		$data['tanggal'] = '';
		$data['tipeinvoice'] = 'po';
		$data['nopo'] = '';
		$data['jatuhtempo'] = '';
		$data['nofp'] = '';
		$data['keterangan'] = '';
		$data['uangmuka'] = '0';
		$data['nilaippn'] = '0';
		$data['kodeorg'] = '';
		$data['kurs'] = '1';
		$data['supplier'] = '';
		$data['matauang'] = 'IDR';
    } else {
		$data['nilaiinvoice'] = number_format($data['nilaiinvoice'],0);
		$data['uangmuka'] = number_format($data['uangmuka'],0);
		$data['nilaippn'] = number_format($data['nilaippn'],0);
		$data['supplier'] = '';
		$data['matauang'] = 'IDR';
		
		$tmpNopo = explode('/',$data['nopo']);
		if(count($tmpNopo)>2 and $tmpNopo[3]=='PO') { 					// Invoice PO
			$data['tipeinvoice']='po';
			
			// Cek PO
			$qPO = "select a.*,b.namasupplier from ".$dbname.".log_poht a
				left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
				where a.nopo='".$data['nopo']."'";
			$resPO = fetchData($qPO);
			if(!empty($resPO)) {
				$data['supplier'] = $resPO[0]['namasupplier'];
				$data['matauang'] = $resPO[0]['matauang'];
			}
		} elseif(substr($data['nopo'],0,2)=='SJ') { // Invoice Surat Jalan
			$data['tipeinvoice']='sj';
			
			// Cek Surat Jalan
			$qPO = "select a.*,b.namasupplier from ".$dbname.".log_suratjalanht a
				left join ".$dbname.".log_5supplier b on a.expeditor=b.supplierid
				where a.nosj='".$data['nopo']."'";
			$resPO = fetchData($qPO);
			if(!empty($resPO)) {
				$data['supplier'] = $resPO[0]['namasupplier'];
			}
		} elseif(substr($data['nopo'],0,2)=='KS') { // Invoice Konosemen
			$data['tipeinvoice']='ns';
			
			// Cek Konosemen
			$qPO = "select a.*,b.namasupplier from ".$dbname.".log_konosemenht a
				left join ".$dbname.".log_5supplier b on a.shipper=b.supplierid
				where a.nokonosemen='".$data['nopo']."'";
			$resPO = fetchData($qPO);
			if(!empty($resPO)) {
				$data['supplier'] = $resPO[0]['namasupplier'];
			}
		} else { 									// Else Invoice SPK
			$data['tipeinvoice']='kontrak';
			
			// Cek SPK
			$qPO = "select a.*,b.namasupplier from ".$dbname.".log_spkht a
				left join ".$dbname.".log_5supplier b on a.koderekanan=b.supplierid
				where a.notransaksi='".$data['nopo']."'";
			$resPO = fetchData($qPO);
			if(!empty($resPO)) {
				$data['supplier'] = $resPO[0]['namasupplier'];
			}
		}
		
		// Perbaiki Kurs Non IDR, jika kurs 1
		if($data['matauang']!='IDR' and $data['kurs']==1) {
			// Get from Setup Mata Uang
			$qKurs = selectQuery($dbname,'setup_matauangrate','*',
								 "daritanggal<='".tanggalsystem($data['tanggal'])."' and
								 kode='".$data['matauang']."'","daritanggal desc, jam desc",false,1,1);
			$resKurs = fetchData($qKurs);
			
			// Update hanya jika kurs ada
			if(!empty($resKurs)) {
				$dataUpd = array('kurs'=>$resKurs[0]['kurs']);
				$qUpd = updateQuery($dbname,'keu_tagihanht',$dataUpd,
									"noinvoice='".$data['noinvoice']."'");
				if(mysql_query($qUpd)) {
					$data['kurs'] = $resKurs[0]['kurs'];
				}
			}
		}
    }
    
    # Disabled Primary
    if($mode=='edit') {
	$disabled = 'disabled';
    } else {
	$disabled = '';
    }
    
   
    
    # Options
    $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
    $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"kasbank=1 and detail=1");
    if($data['tipeinvoice']=='po') {
		$optPO = makeOption($dbname,'log_poht','nopo,nopo',"stat_release=1",'0',true);
    } elseif($data['tipeinvoice']=='sj') {
		$optPO = makeOption($dbname,'log_suratjalanht','nosj,nosj',null,'0',true);
	} elseif($data['tipeinvoice']=='ns') {
		$optPO = makeOption($dbname,'log_konosemenht','nokonosemen,nokonosemen',null,'0',true);
    } else {
		$optPO = makeOption($dbname,'log_spkht','notransaksi,notransaksi',null,'0',true);
    }
    $optCgt = getEnum($dbname,'keu_kasbankht','cgttu');
    $optYn = array(0=>$_SESSION['lang']['belumposting'],1=>$_SESSION['lang']['posting']);
    
    $els = array();
    $els[] = array(
	makeElement('noinvoice','label',$_SESSION['lang']['noinvoice']),
	makeElement('noinvoice','text',$data['noinvoice'],
	    array('style'=>'width:150px','maxlength'=>'20','disabled'=>'disabled'))
    );
	$els[] = array(
	makeElement('noinvoicesupplier','label',$_SESSION['lang']['noinvoice']." Supplier"),
	makeElement('noinvoicesupplier','text',$data['noinvoicesupplier'],
	    array('style'=>'width:150px','maxlength'=>'25'))
    );
    $els[] = array(
	makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
	makeElement('kodeorg','select',$data['kodeorg'],
	    array('style'=>'width:150px'),$optOrg)
    );
    $els[] = array(
	makeElement('tanggal','label',$_SESSION['lang']['tanggalterima']),
	makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:150px',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
	makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
	makeElement('keterangan','text',$data['keterangan'],array('style'=>'width:150px'))
    );
	$els[] = array(
	makeElement('tipeinvoice','label',$_SESSION['lang']['jenis']),
	makeElement('tipeinvoice','select',$data['tipeinvoice'],
	    array('style'=>'width:150px',$disabled=>$disabled,'onchange'=>'updPO()'),
	    array('po'=>'PO',
			  'kontrak'=>$_SESSION['lang']['kontrak'],
			  'sj'=>$_SESSION['lang']['suratjalan'],
                          'bykrm'=>'Biaya Kirim',
			  // 'ns'=>$_SESSION['lang']['konosemen']
		))
    );
	$els[] = array(
	makeElement('nopo','label',$_SESSION['lang']['nopo']),
	makeElement('nopo','text',$data['nopo'],array('style'=>'width:150px;cursor:pointer',
        'readonly'=>'readonly',
        $disabled => $disabled,
        'placeholder' => 'Click to choose',
        'onclick'=>"searchNopo('".$_SESSION['lang']['find']." ',event,'".$_SESSION['lang']['find']."')"))
    );
       
	
	/** [START] Data dari PO */
	$els[] = array(
	makeElement('supplier','label',$_SESSION['lang']['supplier']),
	makeElement('supplier','text',$optNmsupp[$data['kodesupplier']],array('style'=>'width:150px','disabled'=>'disabled'))
    );
	$els[] = array(
	makeElement('matauang','label',$_SESSION['lang']['matauang']),
	makeElement('matauang','text',$data['matauang'],array('style'=>'width:150px','disabled'=>'disabled'))
    );
	$els[] = array(
	makeElement('kurs','label',$_SESSION['lang']['kurs']),
	makeElement('kurs','text',$data['kurs'],array('style'=>'width:150px','disabled'=>'disabled'))
    );
	/** [END] Data dari PO */
	
    $els[] = array(
	makeElement('jatuhtempo','label',$_SESSION['lang']['jatuhtempo']),
	makeElement('jatuhtempo','text',$data['jatuhtempo'],
	    array('style'=>'width:150px','readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
	makeElement('nofp','label',$_SESSION['lang']['nofp']),
	makeElement('nofp','text',$data['nofp'],
	    array('style'=>'width:150px','maxlength'=>'20'))
    );
    $els[] = array(
	makeElement('nilaiinvoice','label',$_SESSION['lang']['nilaiinvoice']),
	makeElement('nilaiinvoice','textnum',$data['nilaiinvoice'],
	    array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))
    );
    $els[] = array(
	makeElement('noakun','hidden',$_SESSION['lang']['noakun']),
	makeElement('noakun','hidden',$data['noakun'],
	    array('style'=>'width:150px'),$optAkun)
    );
    
    /*$els[] = array(
	makeElement('noakun','label',$_SESSION['lang']['noakun']),
	makeElement('noakun','select',$data['noakun'],
	    array('style'=>'width:150px'),$optAkun)
    );*/
    
    $els[] = array(
	makeElement('uangmuka','label',$_SESSION['lang']['uangmuka']),
	makeElement('uangmuka','textnum',$data['uangmuka'],
	    array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))
    );
//    $els[] = array(
//	makeElement('nilaippn','label',$_SESSION['lang']['nilaippn']),
//	makeElement('nilaippn','textnum',$data['nilaippn'],
//	    array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))
//    );
    
    if($mode=='add') {
	$els['btn'] = array(
	    makeElement('addHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"addDataTable()"))
	);
    } elseif($mode=='edit') {
	$els['btn'] = array(
	    makeElement('editHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"editDataTable()"))
	);
    }
    
    if($mode=='add') {
	return genElementMultiDim($_SESSION['lang']['addheader'],$els,3);
    } elseif($mode=='edit') {
	return genElementMultiDim($_SESSION['lang']['editheader'],$els,3);
    }
}
?>