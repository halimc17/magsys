<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

    $_POST['method']!=''?$method=$_POST['method']:$method=$_GET['method'];
    $nomor= !empty($_POST['no_permintaan'])? $_POST['no_permintaan']: isset($_GET['no_permintaan'])? $_GET['no_permintaan']: '';
    $no_prmntan= !empty($_POST['ckno_permintaan'])? $_POST['ckno_permintaan']: isset($_GET['ckno_permintaan'])? $_GET['ckno_permintaan']: '';
    $tgl=			isset($_POST['tgl'])? tanggalsystem($_POST['tgl']): '';
    $supplier_id=	isset($_POST['id_supplier'])? $_POST['id_supplier']: '';
    $id_user=		isset($_POST['user_id'])? $_POST['user_id']: '';
    $kd_brg=		isset($_POST['kdbrg'])? $_POST['kdbrg']: '';
    $mtUang=		isset($_POST['mtUang'])? $_POST['mtUang']: '';
    $kurs=			isset($_POST['kurs'])? $_POST['kurs']: '';
    $noUrut=		isset($_POST['noUrut'])? $_POST['noUrut']: '';
    $optBarang=		makeOption($dbname,'log_5masterbarang', 'kodebarang,namabarang');
    $arrNmBrg=		makeOption($dbname,'log_5masterbarang', 'kodebarang,namabarang');
 
    $optSat=		makeOption($dbname,'log_5masterbarang', 'kodebarang,satuan');
    $optNmkry=		makeOption($dbname,'datakaryawan', 'karyawanid,namakaryawan');
    $optNmSup=		makeOption($dbname,'log_5supplier', 'supplierid,namasupplier');
    $kdNopp=		isset($_POST['kdNopp'])? $_POST['kdNopp']: '';
    $nilDiskon=		isset($_POST['nilDiskon'])? $_POST['nilDiskon']: '';
    $diskonPersen=	isset($_POST['diskonPersen'])? $_POST['diskonPersen']: '';
    $nilPPn=		isset($_POST['nilPPn'])? $_POST['nilPPn']: '';
    $nilaiPermintaan=	isset($_POST['nilaiPermintaan'])? $_POST['nilaiPermintaan']: '';
    $subTotal=			isset($_POST['subTotal'])? $_POST['subTotal']: '';
    $termPay=			isset($_POST['termPay'])? $_POST['termPay']: '';
    $idFranco=			isset($_POST['idFranco'])? $_POST['idFranco']: '';
    $stockId=			isset($_POST['stockId'])? $_POST['stockId']: '';
    $ketUraian=			isset($_POST['ketUraian'])? $_POST['ketUraian']: '';
    $nmSupplier=		isset($_POST['nmSupplier'])? $_POST['nmSupplier']: '';
 
    switch ($method)
    {
       case'getSupplierNm':
           
           if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
           {
               $sortkode="and kodekelompok in ('S001','S002','S004')";
           }
           else
           {
               $sortkode="and kodekelompok in ('S004','S001')";
           }
           
                 $sSupplier="select namasupplier,supplierid from ".$dbname.".log_5supplier "
               . " where namasupplier like '%".$nmSupplier."%' ".$sortkode." and status=1 ";
                 //exit("Error:".$sSupplier);
                 $qSupplier=mysql_query($sSupplier) or die(mysql_error($conn));
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['kodesupplier']."</td>
                        <td>".$_SESSION['lang']['namasupplier']."</td>
                        </tr><tbody>
                        ";
                $no=0;
                 while($rSupplier=mysql_fetch_assoc($qSupplier))
                 {
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setData('".$rSupplier['supplierid']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['supplierid']."</td>
                         <td>".$rSupplier['namasupplier']."</td>
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
        case'getNopp':
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['nopp']."</td>
                        
                        </tr><tbody>
                        ";
                 //$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
                 $sSupplier="select distinct nopp from ".$dbname.".log_prapodt where nopp like '%".$kdNopp."%' and create_po='0'";
                 //exit("Error".$sSupplier);
                 $qSupplier=mysql_query($sSupplier) or die(mysql_error($conn));
                 while($rSupplier=mysql_fetch_assoc($qSupplier))
                 {
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setDataNopp('".$rSupplier['nopp']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['nopp']."</td>
                         
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
         case'getNopp2':
             if(strlen($kdNopp)<5)
             {
                 exit("error: Min 4 character");
             }
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['nopp']."</td>
                        
                        </tr><tbody>
                        ";
                 //$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
                 $sSupplier="select distinct nopp from ".$dbname.".log_perintaanhargaht where nopp like '%".$kdNopp."%'";
                 //exit("Error".$sSupplier);
                 $qSupplier=mysql_query($sSupplier) or die(mysql_error($conn));
                 while($rSupplier=mysql_fetch_assoc($qSupplier))
                 {
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setDataNopp('".$rSupplier['nopp']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['nopp']."</td>
                         
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
        case'getBarangPP':
			if($_SESSION['empl']['kodejabatan']=='5')
			{
			  // $sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and status!='3') and (selisih>0 or selisih is null)";
			  //$sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and status!='3') order by tgl_sdt desc";			
                          $sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and status!='3')"
                                  . " and (selisih>0 or selisih is null) order by tgl_sdt";
                          //and (selisih>0 or selisih is null) order by tgl_sdt";
                        }
			else
			{
			  // $sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and purchaser='".$_SESSION['standard']['userid']."' and status!='3') and (selisih>0 or selisih is null)";
			  //$sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and purchaser='".$_SESSION['standard']['userid']."' and status!='3') order by tgl_sdt desc";                         
                            $sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kdPt']."' and purchaser='".$_SESSION['standard']['userid']."' and status!='3')"
                                    . " and (selisih>0 or selisih is null) order by tgl_sdt";
                        }
                        
                        
                        
			$qPp=mysql_query($sql2) or die(mysql_error($conn));
			$no=0;$tab='';
			while($rPp=mysql_fetch_assoc($qPp))
			{
                $no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td style=width:20px>".$no."</td>";
				$tab.="<td style='width:180px' id=nopplst_".$no.">".$rPp['nopp']."</td>";
				$tab.="<td style='width:88px' id=kodebrg_".$no.">".$rPp['kodebarang']."</td>";
				$tab.="<td style=width:380px>".$optBarang[$rPp['kodebarang']]."</td>";
				//$tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$rPp['jumlah']."</td>";
                                
                                if(($rPp['selisih']=='')||is_null($rPp['selisih'])||$rPp['selisih']==0){
                                        //echo "<td align=center>".$rPp['selisih']."</td>";
                                        $tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$rPp['jumlah']."</td>";
                                } elseif($rPp['selisih']!=$rPp['realisasi']) {
                                        $blm_pesan=$rPp['selisih'];
                                        //echo "<td align=center>".$blm_pesan."</td>";
                                        $tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$blm_pesan."</td>";
                                
                                } else {
                                        //echo "<td align=center>".$rPp['selisih']."</td>";
                                        $tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$rPp['selisih']."</td>";
                                
                                }
                                
                                
				$tab.="<td style=width:55px>".$optSat[$rPp['kodebarang']]."</td>";
				$tab.="<td  style='width:10px' align=center><input type=checkbox id=pilBrg_".$no." /></td></tr>";
            }
         $tab.="<tr><td colspan=5 align=center><button class=mybutton onclick=lanjutAdd() >".$_SESSION['lang']['lanjut']."</button></td></tr>";
         //exit("Error:".$nourut);
         echo $tab;
         break;
         case'loadSuppier':
         $sData="select nomor,supplierid,nourut from ".$dbname.".log_perintaanhargaht 
                 where nomor='".$_POST['notrans']."'
                 order by nomor asc";
         $qData=mysql_query($sData) or die(mysql_error($conn));
		 $no=0;$tabl='';
         while($rData=mysql_fetch_assoc($qData))
         {
             $no++;
             $sNmsup="select distinct namasupplier from ".$dbname.".log_5supplier where supplierid='".$rData['supplierid']."'";
             $qNmsup=mysql_query($sNmsup) or die(mysql_error($conn));
             $rNmsup=mysql_fetch_assoc($qNmsup);
             $tabl.="<tr class=rowcontent>";
             $tabl.="<td>".$no."</td>";
             $tabl.="<td>".$rData['nomor']."</td>";
             $tabl.="<td>".$rNmsup['namasupplier']."</td>";
             $tabl.="<td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer('".$rData['nomor']."','".$rData['nourut']."');\"></td>";
             $tabl.="</tr>";
             
         }
         echo $tabl;
         break;
         case'addData':
            foreach($_POST['kdbrg'] as $row=>$Act)
                {  
                }

                $noppx=$_POST['lstnopp'][0];
                $nopplist=  explode('/', $noppx);
                $unit=$nopplist[4];



                if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
                {
                    $iOrg="select kodeorg from ".$dbname.".log_prapoht where nopp='".$noppx."'  ";
                    $nOrg=  mysql_query($iOrg) or die (mysql_error($conn));
                    $dOrg=  mysql_fetch_assoc($nOrg);
                    $unodph=$dOrg['kodeorg'];
                    
                }
                else
                {
                    $unodph=$unit;
                }
                
                
         
         $tgl=date('Ymd');
         if($_POST['notransaksi']=='')
         {
            $bln = substr($tgl,4,2);
            $thn = substr($tgl,0,4);
            $no="/".date('Y')."/DPH/".$unodph;
            $ql="select `nomor` from ".$dbname.".`log_perintaanhargaht` where nomor like '%".$no."%' order by `nomor` desc limit 0,1";
            $qr=mysql_query($ql) or die(mysql_error());
            $rp=mysql_fetch_object($qr);
            $awal=substr($rp->nomor,0,3);
            $awal=intval($awal);
            $cekbln=substr($rp->nomor,4,2);
            $cekthn=substr($rp->nomor,7,4);
            //if(($bln!=$cekbln)&&($thn!=$cekthn))
            if($thn!=$cekthn)
            {
            //echo $awal; exit();
            $awal=1;
            }
            else
            {
            $awal++;
            }
            $counter=addZero($awal,3);
            $no_permintaan=$counter."/".$bln."/".$thn."/DPH/".$unodph;
         }
         else{
             $no_permintaan=$_POST['notransaksi'];
             $scek="select distinct * from ".$dbname.".log_perintaanhargaht 
                    where nomor='".$no_permintaan."' and supplierid='".$supplier_id."'";
             //exit("error:".$scek."__".$no_permintaan);
             $qcek=mysql_query($scek) or die(mysql_error($conn));
             $rcek=mysql_num_rows($qcek);
             if($rcek!=0){
                 exit("error: Data tersebut sudah ada");
             }
         }
          $ins="insert into ".$dbname.".log_perintaanhargaht 
                (nomor, tanggal, purchaser, supplierid,nourut) values 
                ('".$no_permintaan."','".$tgl."','".$_SESSION['standard']['userid']."','".$supplier_id."','".$_POST['norurut']."')";
            //exit("error:".$ins);
            if(mysql_query($ins))
            {
//                echo"<pre>";
//                print_r($_POST['kdbrg']);
//                echo"</pre>";
//                exit("error:data");
                foreach($_POST['kdbrg'] as $row=>$Act)
                {
                    $kdbrg=$Act;
                    $jmlh=$_POST['jmlh'][$row];
                    $nopp=$_POST['lstnopp'][$row];

                    $sqp="insert into ".$dbname.".log_permintaanhargadt (`nomor`,`kodebarang`,`jumlah`,nopp,nourut) 
                        values('".$no_permintaan."','".$kdbrg."','".$jmlh."','".$nopp."','".$_POST['norurut']."')";
                        //echo"warning". $sqp; exit();
                        if(!mysql_query($sqp))
                        {
                                echo $sqp;
                                echo "Gagal,".(mysql_error($conn));exit();
                        }
                }
                $_POST['norurut']+=1;
                echo $no_permintaan."###".$_POST['norurut'];
            }
            else
            {
                 echo "Gagal,".$ins."__".(mysql_error($conn));
            }
         break;
        
                case 'cari_pp':
                    $limit=25;
                    $page=0;
                    if(isset($_POST['page']))
                    {
                    $page=$_POST['page'];
                    if($page<0)
                    $page=0;
                    }
                    $offset=$page*$limit;
                    $sql="select * from ".$dbname.".log_perintaanhargaht where purchaser='".$_SESSION['standard']['userid']."' order by tanggal desc LIMIT ".$offset.",".$limit."";
                    $sql2="select count(*) as jmlhrow from ".$dbname.".log_perintaanhargaht where purchaser='".$_SESSION['standard']['userid']."' order by tanggal desc";
                    $query2=mysql_query($sql2) or die(mysql_error());
                    while($jsl=mysql_fetch_object($query2)){
                    $jlhbrs= $jsl->jmlhrow;
                    }
                    //$sql="select * from ".$dbname.".log_perintaanhargaht order by nomor desc";
                    if($query=mysql_query($sql))
                    {
					$no=0;
                    while($res2=mysql_fetch_assoc($query))
                    {
                            $no+=1;
                            $dtkr="select * from ".$dbname.".datakaryawan where karyawanid='".$res2['purchaser']."'"; //echo $dtkr;
                            $qdtkr=mysql_query($dtkr) or die(mysql_error());
                            $rdtkr=mysql_fetch_object($qdtkr);

                            $splr="select * from ".$dbname.".log_5supplier where supplierid='".$res2['supplierid']."'"; //echo $splr;
                            $qsuplr=mysql_query($splr) or die(mysql_error());
                            $rsplr=mysql_fetch_object($qsuplr);
                            if($res2['ppn']!=0)
                            {
                            $ppn=($res2['ppn']/($res2['subtotal']-$res2['nilaidiskon']))*100;
                            }


                            echo
                            "<tr class=rowcontent>
                                    <td>".$no."</td>
                                    <td>".$res2['nomor']."</td>
                                    <td align=center>".$res2['nourut']."</td>
                                    <td>".tanggalnormal($res2['tanggal'])."</td>";
                                    //<td>".$rdtkr->namakaryawan."</td>
                                    echo"<td>".$rsplr->namasupplier."</td>";
                                    
                                    if($res2['flag']==0)
                                    {
										$status="";
									}
                                    else if($res2['flag']==1)
                                    {
                                        $status="<img src=images/box/drop-yes.gif class=resicon);\">";
                                    }
                                    else
                                    {
                                        $status="<img src=images/box/drop-no.gif class=resicon);\">";
                                    }
                                    
                                    if($res2['purchaser']==$_SESSION['standard']['userid'])
                                    {
                                         echo"<td>";
                                            echo"
                                            <img src=images/application/application_edit.png class=resicon  title='Edit Quotation Request' onclick=\"zPreview2('log_slave_save_permintaan_harga','". $res2['nomor']."','printContainer2');\">
                                            <img src=images/plus.png class=resicon  title='Add more supplier ' onclick=\"addSupplierPlus('".$res2['nomor']."','".$res2['nourut']."');\">
                                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer1('".$res2['nomor']."','".$res2['nourut']."');\">
											<img src=images/pdf.jpg class=resicon  title='Print RPH' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran',event);\">
											<img src=images/pdf.jpg class=resicon  title='Print QCF' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\">    
											<img onclick=datakeExcel(event,'".$res2['nomor']."') src=images/excel.jpg class=resicon title='MS.Excel'>    
                                        
											<img src=images/skyblue/posting.png class=resicon  title='posting data' onclick=\"alasan_batal('".$res2['nomor']."','".$res2['nourut']."');\">
											".$status."
                                        </td>";
                                    }
                                    else
                                    {
                                        echo"<td>
                                            <img src=images/pdf.jpg class=resicon  title='Print RPH' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran',event);\">
                                            <img src=images/pdf.jpg class=resicon  title='Print QCF' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\">
                                            <img onclick=datakeExcel(event,'".$res2['nomor'].") src=images/excel.jpg class=resicon title='MS.Excel'>          
                                            </td>";

                                    }
                                    echo"</tr>";
                    }
                    echo"
                     <tr><td colspan=6 align=center>
                    ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                    <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                    <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                    </td>
                    </tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$res2['nopp']."' />"; 
                    }
                    else
                    {
                    echo "Gagal,".(mysql_error($conn));
                    }

                                    break;
    case 'deleted':
        $strx="delete from ".$dbname.".log_perintaanhargaht where nomor='".$_POST['no_permintaan']."' and nourut='".$_POST['nourut']."'";
        /*if(!mysql_query($strx))
        {
            echo " Gagal,".addslashes(mysql_error($conn));
        }*/
        if(mysql_query($strx))
        {
            //echo"";
            $i="delete from ".$dbname.".log_permintaanhargadt where nomor='".$_POST['no_permintaan']."' and nourut='".$_POST['nourut']."'";
            if(mysql_query($i))
            {   
            }
            else
            {
                 echo "DB Error : ".mysql_error($conn);
            }
            
        }
        else
        {
            echo "DB Error : ".mysql_error($conn);
        }
        
     break;

                    case 'cari_permintaan':

                            $limit=25;
                    $page=0;
                    if(isset($_POST['page']))
                    {
                    $page=$_POST['page'];
                    if($page<0)
                    $page=0;
                    }
                    $offset=$page*$limit;

            if($_POST['txtSearch']!='')
            {
                    $where=" and nomor LIKE  '%".$_POST['txtSearch']."%'";
            }
            if($_POST['tglCari']!='')
            {
                    $txt_tgl=tanggalsystem($_POST['tglCari']);
                    $txt_tgl_t=substr($txt_tgl,0,4);
                    $txt_tgl_b=substr($txt_tgl,4,2);
                    $txt_tgl_tg=substr($txt_tgl,6,2);
                    $txt_tgl=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
                    $where.=" and tanggal LIKE '".$txt_tgl."'";
            }
            if($_POST['txtNopp']!='')
            {
                $where.=" and nopp='".$_POST['txtNopp']."'";
            }
            if($_POST['txtNmbrg']!='')
            {
                $sCek="select distinct nomor from ".$dbname.".log_permintaanhargadt where kodebarang in 
                      (select distinct kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$_POST['txtNmbrg']."%')";
                $qCek=mysql_query($sCek) or die(mysql_error($conn));
                while($rCek=mysql_fetch_assoc($qCek))
                {
                    $ard+=1;
                    if($ard==1)
                    {
                        $dtr="'".$rCek['nomor']."'";
                    }
                    else
                    {
                        $dtr.=",'".$rCek['nomor']."'";
                    }
                }
                $where.=" and nomor in (".$dtr.")";
            }
            $strx="SELECT * FROM ".$dbname.".log_perintaanhargaht where purchaser='".$_SESSION['standard']['userid']."' ".$where." ORDER BY tanggal DESC LIMIT ".$offset.",".$limit."";//echo $strx;	
            $sql2="select count(*) as jmlhrow from ".$dbname.".log_perintaanhargaht where purchaser='".$_SESSION['standard']['userid']."' ".$where." order by tanggal desc";	 

            //echo "warning:".$strx."__".$where;exit();
            $query2=mysql_query($sql2) or die(mysql_error());
                    while($jsl=mysql_fetch_object($query2)){
                    $jlhbrs= $jsl->jmlhrow;
                    }
                    if($query=mysql_query($strx))
                    {
                    while($res2=mysql_fetch_assoc($query))
                    {
                            $no+=1;
                            $dtkr="select * from ".$dbname.".datakaryawan where karyawanid='".$res2['purchaser']."'"; //echo $dtkr;
                            $qdtkr=mysql_query($dtkr) or die(mysql_error());
                            $rdtkr=mysql_fetch_object($qdtkr);

                            $splr="select * from ".$dbname.".log_5supplier where supplierid='".$res2['supplierid']."'"; //echo $splr;
                            $qsuplr=mysql_query($splr) or die(mysql_error());
                            $rsplr=mysql_fetch_object($qsuplr);
                            if($res2['ppn']!=0)
                            {
                             $ppn=($res2['ppn']/($res2['subtotal']-$res2['nilaidiskon']))*100;
                            }
                            echo
                            "<tr class=rowcontent>
                                    <td>".$no."</td>
                                    <td>".$res2['nomor']."</td>
                                    <td>".$res2['nourut']."</td>
                                    <td>".tanggalnormal($res2['tanggal'])."</td>";
                                    //<td>".$rdtkr->namakaryawan."</td>
                                    echo"<td>".$rsplr->namasupplier."</td>";
                                    
                                    
                                    if($res2['flag']==0)
                                    {
                                        $status="<img src=images/skyblue/posting.png class=resicon  title='posting data' onclick=\"alasan_batal('".$res2['nomor']."','".$res2['nourut']."');\">";
                                    }
                                    else  if($res2['flag']==1)
                                    {
                                        $status="<img src=images/box/drop-yes.gif class=resicon);\">";
                                    }
                                    else
                                    {
                                        $status="<img src=images/box/drop-no.gif class=resicon);\">";
                                    }
                                    
                                    if($res2['purchaser']==$_SESSION['standard']['userid'])
                                            {
                                            echo"
                <td>
                <img src=images/application/application_edit.png class=resicon  title='Edit Quotation Request' onclick=\"zPreview2('log_slave_save_permintaan_harga','". $res2['nomor']."','printContainer2');\">
                <img src=images/plus.png class=resicon  title='Tambah Supplier ' onclick=\"addSupplierPlus('".$res2['nomor']."','".$res2['nourut']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer1('".$res2['nomor']."','".$res2['nourut']."');\">
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran',event);\">
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\">    
                
                <img onclick=datakeExcel(event,'".$res2['nomor']."') src=images/excel.jpg class=resicon title='MS.Excel'>      
                ".$status." 
                </td>";
                                            }
                                            else
                                            {
                                                    echo"<td>
                                                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran',event);\">
                                                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$res2['nomor'].",".$res2['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\">    
                                                        <img onclick=datakeExcel(event,'".$res2['nomor'].") src=images/excel.jpg class=resicon title='MS.Excel'>          
                                                        </td>";

                                            }
                                            echo"
            </tr>";
                    }
                    echo"
                     <tr><td colspan=6 align=center>
                    ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                    <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                    <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                    </td>
                    </tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />"; 
                    }
                    else
                    {
                    echo "Gagal,".(mysql_error($conn));
                    }

                    break;
                    case'get_nopp':
                    $optNopp='';
                    $sql="SELECT a.nopp FROM ".$dbname.".`log_prapodt` a left join ".$dbname.".`log_prapoht` b on a.nopp=b.nopp where b.close='2' 
                    and (a.create_po is null or create_po='') 
                    and a.kodebarang='".$kd_brg."'"; //echo "warning".$sql;
                    $query=mysql_query($sql) or die(mysql_error());
                    while($res=mysql_fetch_assoc($query))
                    {
                            $optNopp.="<option value=".$res['nopp'].">".$res['nopp']."</option>";
                    }
                    echo $optNopp;
                    break;
                    case'getSpek':
                        $sSpek="select spesifikasi from ".$dbname.".log_5photobarang where kodebarang='".$kd_brg."'";
                        $qSpek=mysql_query($sSpek) or die(mysql_error());
                        $rSpek=mysql_fetch_assoc($qSpek);
                        echo $rSpek['spesifikasi'];
                    break;
                    case'getKurs':
                        $tgl=date("Ymd");
                        $sGet="select distinct kurs from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl."'";
                        $qGet=mysql_query($sGet) or die(mysql_error());
                        $rGet=mysql_fetch_assoc($qGet);
                        //echo "warning:".$rGet['kurs'];
                        if($mtUang=='IDR')
                        {
                                $rGet['kurs']=1;
                        }
                        else
                        {
                                if($rGet['kurs']!=0)
                                {
                                        $rGet['kurs']=$rGet['kurs'];
                                }
                                else
                                {
                                        $rGet['kurs']=1;
                                }
                        }
                    echo $rGet['kurs'];
                    break;

                    case'printExcel':

                    $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $optStock=$optTermPay;
                    $optKrm=$optTermPay;
                    $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
                    $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");   
					$arrFranco = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
					$arrOptTerm2 =  makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan,jenis','',4);
                    $sdtheder="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$_GET['no_permintaan']."'";
                    //exit("error:".$sdtheder);
                    $qdtheder=mysql_query($sdtheder) or die(mysql_error($conn));
                    while($rdtheder=mysql_fetch_assoc($qdtheder)){
                        $dtNomor[]=$rdtheder['nourut'];
                        $dtSupp[$rdtheder['nourut']]=$rdtheder['supplierid'];
                        $dtFranco[$rdtheder['nourut']]=$rdtheder['id_franco'];
                        $dtStock[$rdtheder['nourut']]=$rdtheder['stock'];
                        $dtCattn[$rdtheder['nourut']]=$rdtheder['catatan'];
                        $dtSisbyr[$rdtheder['nourut']]=$rdtheder['sisbayar'];
                        $dtSisbyr2[$rdtheder['nourut']]=$rdtheder['sisbayar2'];
                        $dtPpn[$rdtheder['nourut']]=$rdtheder['ppn'];
                        $dtSbtotal[$rdtheder['nourut']]=$rdtheder['subtotal'];
                        $dtDisknPrsn[$rdtheder['nourut']]=$rdtheder['diskonpersen'];
                        $dtNildis[$rdtheder['nourut']]=$rdtheder['nilaidiskon'];
                        $dtNilPer[$rdtheder['nourut']]=$rdtheder['nilaipermintaan'];
                        $dtMtuang[$rdtheder['nourut']]=$rdtheder['matauang'];
                        $dtTglDr[$rdtheder['nourut']]=$rdtheder['tgldari'];
                        $dtTglSmp[$rdtheder['nourut']]=$rdtheder['tglsmp'];
                        $kurs[$rdtheder['nourut']]=$rdtheder['kurs'];
                        $dtCttn[$rdtheder['nourut']]=$rdtheder['catatan'];
                    }


                    $sDetail="select distinct kodebarang,jumlah,nomor,harga,merk,nourut from ".$dbname.".log_permintaanhargadt where nomor='".$_GET['no_permintaan']."' ";
                    $qDetail=mysql_query($sDetail) or die(mysql_error());
                    while($rDetail=mysql_fetch_assoc($qDetail))
                    {
                        if($rDetail['harga']=='')
                        {
                            $rDetail['harga']=0;
                        }
                        $dtSub[$rDetail['nourut']][$rDetail['kodebarang']]=floatval($rDetail['jumlah'])*floatval($rDetail['harga']);
                        $dtHarga[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['harga'];
                        $dtMerk[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['merk'];
                        $arrJmlh[$rDetail['kodebarang']]=$rDetail['jumlah'];
                        $listBarang[$rDetail['kodebarang']]=$rDetail['kodebarang'];

                    }


                $tab="<table cellspacing=1 border=1 class=sortable >
                <thead class=rowheader>
                <tr>
                <td bgcolor=#DEDEDE rowspan=2 align=center>No.</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['jumlah']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>";
                $ard=0;
                foreach ($dtNomor as $brs){    
                     $ard+=1; 
                $tab.="<td bgcolor=#DEDEDE colspan=3 align=center>".$optNmSup[$dtSupp[$ard]]."</td>";
                  }
                $tab.="</tr><tr>";
                foreach ($dtNomor as $brs){
                    $tab.="<td   bgcolor=#DEDEDE align=center width=85px>".$_SESSION['lang']['merk']."</td><td  align=center width=85px bgcolor=#DEDEDE>".$_SESSION['lang']['harga']."</td><td align=center width=85px bgcolor=#DEDEDE>".$_SESSION['lang']['subtotal']."</td>";
                }
                  $tab.="<tr>";
                $tab.="</thead>
                <tbody>";
               $totRow=count($dtNomor);
               $totBrg=count($listBarang);
                        foreach($listBarang as $brsKdBrg){
                            $no+=1;
                            $tab.="<tr class='rowcontent'>";
                            $tab.="<td>".$no."</td>";
                            $tab.="<td id='kd_brg_".$no."'>".$brsKdBrg."</td>";
                            $tab.="<td title='".$arrNmBrg[$brsKdBrg]."'>".$arrNmBrg[$brsKdBrg]."</td>";
                            $tab.="<td align=right id='jumlah_".$no."'>".$arrJmlh[$brsKdBrg]."</td>";
                            $tab.="<td align=center>".$optSat[$brsKdBrg]."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                $tab.="<td align=left>".$dtMerk[$ard][$brsKdBrg]."</td>";
                                $tab.="<td align=right>".number_format($dtHarga[$ard][$brsKdBrg],2)."</td>";
                                $tab.="<td align=right>".number_format($dtSub[$ard][$brsKdBrg],2)."</td>";
                            }
                            $tab.="</tr>";
                        }
                            $tab.="<tr class='rowcontent'>";
                            
                            $tab.="<td rowspan=4 colspan=3 valign=top align=left>&nbsp</td><td colspan=2>".$_SESSION['lang']['subtotal']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                $tab.="<td align=right colspan=3 id=total_harga_po_".$ard.">".number_format($dtSbtotal[$ard],2)."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['diskon']."</td>";
                            foreach ($dtNomor as $brs)
                            {
                                $nor+=1;
                                    $tab.="<td align=right colspan=2>".$dtDisknPrsn[$nor]."%</td>";
                                    $tab.="<td align=right>".number_format($dtNildis[$nor],2)."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['ppn']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                    @$persen[$ard]=($dtPpn[$ard]/($dtSbtotal[$ard]-$dtNildis[$ard]))*100;
                                    $tab.="<td align=right colspan=2>".$persen[$ard]."</td>";
                                    $tab.="<td align=right >".number_format($dtPPN[$ard],2)."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['grnd_total']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                $tab.="<td align=right colspan=3 id=grand_total_".$ard.">".number_format($dtNilPer[$ard],2)."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td rowspan=10 colspan=3 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>";
                            $tab.="<td colspan=2>".$_SESSION['lang']['nopermintaan']."</td>";
                            $ard=0;
                                foreach ($dtNomor as $brs){
                                        $ard+=1;
                                        $tab.="<td colspan=3>".$_GET['no_permintaan']."</td>";
                                }
                                $tab.="</tr>";
                                $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['matauang']."</td>";
                                $ard=0;
                                foreach ($dtNomor as $brs){
                                    $ard+=1;                                    
                                        $tab.="<td colspan=3>".$dtMtuang[$ard]."</td>";
                                }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['kurs']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                    $tab.="<td colspan=3>".$kurs[$ard]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tgldari']."</td>";
                            $ard=0;
                                foreach ($dtNomor as $brs){
                                    $ard+=1;
                                        $tab.="<td colspan=3>".$dtTglDr[$ard]."</td>";
                                }
                            
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tglsmp']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)  {
                                $ard+=1;
                                $tab.="<td colspan=3>".$dtTglSmp[$ard]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['syaratPem']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
									if($dtSisbyr[$ard]!='0'){
										$hasilSyaratBayar = $arrOptTerm[$dtSisbyr[$ard]];
									}else{
										if($dtSisbyr2[$ard]!=''){
											$hasilSyaratBayar = $arrOptTerm2[$dtSisbyr2[$ard]];;
										}else{
											$hasilSyaratBayar = "";
										}
									}
                                    $tab.="<td colspan=3>".$hasilSyaratBayar."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['stock']."</td>";
                             $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                    $tab.="<td colspan=3>".$arrStock[$dtStock[$ard]]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['almt_kirim']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                $tab.="<td colspan=3>".$arrFranco[$dtFranco[$ard]]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['keterangan']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                $tab.="<td align=justify colspan=3>".$dtCttn[$ard]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class=rowcontent><td colspan=2></td>";
                            $ard=0;
                            $tab.="<td align=center colspan=3></td>";
                            $tab.="</tr>";
                        
                
                            $tab.="</tbody></table>";
                                        $nop_="form_permintaan_harga";
                                        if(strlen($tab)>0)
                                        {
                                        if ($handle = opendir('tempExcel')) {
                                        while (false !== ($file = readdir($handle))) {
                                        if ($file != "." && $file != "..") {
                                        @unlink('tempExcel/'.$file);
                                        }
                                        }	
                                        closedir($handle);
                                        }
                                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                                        if(!fwrite($handle,$tab))
                                        {
                                        echo "<script language=javascript1.2>
                                        parent.window.alert('Can't convert to excel format');
                                        </script>";
                                        exit;
                                        }
                                        else
                                        {
                                        echo "<script language=javascript1.2>
                                        window.location='tempExcel/".$nop_.".xls';
                                        </script>";
                                        }
                                        fclose($handle);
                                        }

                                break;
				
            case'getNotifikasi':
		$Sorg="select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qOrg=mysql_query($Sorg) or die(mysql_error());
		while($rOrg=mysql_fetch_assoc($qOrg))
		{
                    $dafUnit[]=$rOrg['kodeorganisasi'];
                }
                
                echo"<table border=0>";
				$ared=0;
                foreach($dafUnit as $lstKdOrg){
                    $ared+=1;
                    if($ared==1)
                    {
                        echo"<tr>";
                    }
		if($_SESSION['empl']['kodejabatan']=='5')
                {
                     $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$lstKdOrg."' and status!='3') and (selisih>0 or selisih is null)";
                    //$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$lstKdOrg."' and status!='3')";
                }
                else
                {
                    $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where  (kodept='".$lstKdOrg."' and purchaser='".$_SESSION['standard']['userid']."' and status!='3') and (selisih>0 or selisih is null)"; 
                   //$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where  (kodept='".$lstKdOrg."' and purchaser='".$_SESSION['standard']['userid']."' and status!='3')"; 
                }
                //exit("error:".$sList);
		//$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."'  and purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null) group by kodept";
		$qList=mysql_query($sList) or die(mysql_error());
                $rBaros=mysql_num_rows($qList);
                $rList=mysql_fetch_assoc($qList);
                    if(intval($rList['jmlhJob'])!=0)
                    {
                        if($rList['jmlhJob']=='')
                        {
                            $rList['jmlhJob']=0;
                        }
                            if(isset($_POST['status']) and $_POST['status']==1)
                            {
                                echo"<td>".$lstKdOrg."</td><td>: ".$rList['jmlhJob']."</td>";
                            }
                            else
                            {
                                echo"<td>".$lstKdOrg."</td><td>: <a href='#' onclick=\"getDtPP('".$lstKdOrg."')\">".$rList['jmlhJob']."</a></td>";
                            }
                    }
                    if($ared==9){
                        echo"</tr>";
                        $ared=0;
                    }
                }
                echo"</table>";
		break;
                case'cekBarang':
                foreach($_POST['lstnopp'] as $row =>$Rslt)
                {
                    for($a=0;$a<$row;$a++)
                    {
                        for($b=0;$b<$_POST['baris'];$b++)
                        {
                           if($a!=$b)
                           { 
                               if($_POST['kdbrg'][$a]==$_POST['kdbrg'][$b])
                               {
                                   $cek+=1;
                                   $cekBrg2=$_POST['kdbrg'][$a];
                               }
                           }  
                        }
                    }
                    if($cek!=0)
                    {
                        echo"warning:Kodebarang : ".$cekBrg2." Lebih Dari Satu";
                        exit();
                    }
                }
                break;
                case'listBarangDetail':
                    $tab.="<tr class=rowcontent><td colspan=7>&nbsp;</td></tr>";
                    $sPp="select distinct * from ".$dbname.". log_permintaanhargadt where nomor='".$_POST['notransaksi']."' and nourut='".$_POST['nourut']."'";
                    //echo $sPp;
                    //exit("error:".$sPp);
                    $qPp=mysql_query($sPp) or die(msyql_error($conn));
                     while($rPp=mysql_fetch_assoc($qPp))
                     {
                            $no++;
                            $tab.="<tr class=rowcontent>";
                            $tab.="<td style=width:20px>".$no."</td>";
                            $tab.="<td style='width:180px' id=nopplst_".$no.">".$rPp['nopp']."</td>";
                            $tab.="<td style='width:88px' id=kodebrg_".$no.">".$rPp['kodebarang']."</td>";
                            $tab.="<td style=width:380px>".$optBarang[$rPp['kodebarang']]."</td>";
                            $tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$rPp['jumlah']."</td>";
                            $tab.="<td style=width:55px>".$optSat[$rPp['kodebarang']]."</td>";
                            $tab.="<td  style='width:10px' align=center><input type=checkbox id=pilBrg_".$no." checked /></td></tr>";
                     }
                     echo $tab;
                break;
                case'preview2':
				
				
			  
                    
                $formPil=1;
                $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $optStock=$optTermPay;
                $optKrm=$optTermPay;
                $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
                $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");   
                
                $iCek="select count(*) as jumlah from ".$dbname.".log_perintaanhargaht where nomor='".$_POST['notransaksi']."'";
          
                $nCek=  mysql_query($iCek) or die (mysql_error($conn));
                $dCek=  mysql_fetch_assoc($nCek);
                if($dCek['jumlah']==0)
                {
                    exit("Error:Please input supplier");
                }
                
                
                
                $sdtheder="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$_POST['notransaksi']."'";
                //indra
                //exit("error:".$sdtheder);
                $qdtheder=mysql_query($sdtheder) or die(mysql_error($conn));
                while($rdtheder=mysql_fetch_assoc($qdtheder)){
                    $dtNomor[]=$rdtheder['nourut'];
                    $dtSupp[$rdtheder['nourut']]=$rdtheder['supplierid'];
                    $dtFranco[$rdtheder['nourut']]=$rdtheder['id_franco'];
                    $dtStock[$rdtheder['nourut']]=$rdtheder['stock'];
                    $dtCattn[$rdtheder['nourut']]=$rdtheder['catatan'];
                    $dtSisbyr[$rdtheder['nourut']]=$rdtheder['sisbayar'];
                    $dtSisbyr2[$rdtheder['nourut']]=$rdtheder['sisbayar2'];
                    $dtPpn[$rdtheder['nourut']]=$rdtheder['ppn'];
                    $dtSbtotal[$rdtheder['nourut']]=$rdtheder['subtotal'];
                    $dtDisknPrsn[$rdtheder['nourut']]=$rdtheder['diskonpersen'];
                    $dtNildis[$rdtheder['nourut']]=$rdtheder['nilaidiskon'];
                    $dtNilPer[$rdtheder['nourut']]=$rdtheder['nilaipermintaan'];
                    $dtMtuang[$rdtheder['nourut']]=$rdtheder['matauang'];
                    $dtTglDr[$rdtheder['nourut']]=$rdtheder['tgldari'];
                    $dtTglSmp[$rdtheder['nourut']]=$rdtheder['tglsmp'];
                    $kurs[$rdtheder['nourut']]=$rdtheder['kurs'];
                    $dtCttn[$rdtheder['nourut']]=$rdtheder['catatan'];
                }

              
                $sDetail="select distinct kodebarang,jumlah,nomor,harga,merk,nourut from ".$dbname.".log_permintaanhargadt where nomor='".$_POST['notransaksi']."' ";
                $qDetail=mysql_query($sDetail) or die(mysql_error());
                while($rDetail=mysql_fetch_assoc($qDetail))
                {
                    if($rDetail['harga']=='')
                    {
                        $rDetail['harga']=0;
                    }
                    $dtSub[$rDetail['nourut']][$rDetail['kodebarang']]=floatval($rDetail['jumlah'])*floatval($rDetail['harga']);
                    $dtHarga[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['harga'];
                    $dtMerk[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['merk'];
                    $dtJumlah[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['jumlah'];
                    $arrJmlh[$rDetail['kodebarang']]=$rDetail['jumlah'];
                    $listBarang[$rDetail['kodebarang']]=$rDetail['kodebarang'];
                     
                }
             

                $tab="<table cellspacing=1 border=0 class=sortable >
                <thead class=rowheader>
                <tr>
                <td rowspan=2 align=center>No.</td>
                <td rowspan=2 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=2 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=2 align=center width='50px'>".$_SESSION['lang']['satuan']."</td>";
                $ard=0;
                foreach ($dtNomor as $brs){    
                     $ard+=1;
                     
                     if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
                    {
                        $sortkode="and kodekelompok in ('S001','S002','S004')";
                    }
                    else
                    {
                        $sortkode="and kodekelompok in ('S004','S001')";
                    }
                     
                     $optSupplier="";
                        $sql="select namasupplier,supplierid from ".$dbname.".log_5supplier where"
                                . " status=1 ".$sortkode." order by namasupplier asc";
                        $query=mysql_query($sql) or die(mysql_error());
                        while($res=mysql_fetch_assoc($query))
                        {
                            $optSupplier.="<option value='".$res['supplierid']."' ".($res['supplierid']==$dtSupp[$ard]?"selected":"").">".$res['namasupplier']."</option>";
                        }
                    
                $tab.="<td colspan=4 align=center><select id=supplierId_".$ard.">".$optSupplier."</select></td>";
                    }
                $tab.="</tr><tr>";
               
                foreach ($dtNomor as $brs){
                    $tab.="<td  align=center width=85px>".$_SESSION['lang']['merk']."</td><td  align=center width=85px>".$_SESSION['lang']['jumlah']."</td><td  align=center width=85px>".$_SESSION['lang']['harga']."</td><td align=center width=85px>".$_SESSION['lang']['subtotal']."</td>";
                }
                  $tab.="<tr>";
                $tab.="</thead>
                <tbody>";
				$totRow=count($dtNomor);
				$totBrg=count($listBarang);
				$no=0;
                        foreach($listBarang as $brsKdBrg){
                            $no+=1;
                            $tab.="<tr class='rowcontent'>";
                            $tab.="<td>".$no."</td>";
                            $tab.="<td id='kd_brg_".$no."'>".$brsKdBrg."</td>";
                            $tab.="<td colspan=2 title='".$arrNmBrg[$brsKdBrg]."'>".$arrNmBrg[$brsKdBrg]."</td>";
                            // $tab.="<td align=right id='jumlah_".$no."'>".$arrJmlh[$brsKdBrg]."</td>";
                            $tab.="<td align=center>".$optSat[$brsKdBrg]."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td align=left>".$dtMerk[$ard][$brsKdBrg]."</td>";
                                    $tab.="<td align=left>".$dtJumlah[$ard][$brsKdBrg]."</td>";
									$tab.="<td align=right>".number_format($dtHarga[$ard][$brsKdBrg],2)."</td>";
                                    $tab.="<td align=right>".number_format($dtSub[$ard][$brsKdBrg],2)."</td>";
                                }
                                else
                                {
									$tab.="<td align=right><input type=text id=merk_".$no."_".$ard." value='".$dtMerk[$ard][$brsKdBrg]."' class='myinputtext' onkeypress='return tanpa_kutip(event)' maxlength=50 style='width:85px' /></td>";
									$tab.="<td align=right><input type=text id=qty_".$no."_".$ard." value='".$dtJumlah[$ard][$brsKdBrg]."' class='myinputtextnumber' onkeypress='return angka_doang(event)' onfocus='normal_number(".$no.",".$ard.",".$totBrg.")' onkeyup='calculate(".$no.",".$ard.",".$totBrg.")' style='width:85px' /></td>";
                                    $tab.="<td align=right><input type=text id=price_".$no."_".$ard." value='".$dtHarga[$ard][$brsKdBrg]."' class='myinputtextnumber' onkeypress='return angka_doang(event)' onfocus='normal_number(".$no.",".$ard.",".$totBrg.")' onkeyup='calculate(".$no.",".$ard.",".$totBrg.")' style='width:85px' /></td>";
                                    $tab.="<td align=right><input type=text id=total_".$no."_".$ard." disabled value='".$dtSub[$ard][$brsKdBrg]."'  class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:85px'  /></td>";
                                }
                            }
                            $tab.="</tr>";
                        }
                            $tab.="<tr class='rowcontent'>";
                            
                            $tab.="<td rowspan=4 colspan=3 valign=top align=left>&nbsp</td><td colspan=2>".$_SESSION['lang']['subtotal']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                $tab.="<td align=right colspan=4 id=total_harga_po_".$ard.">".$dtSbtotal[$ard]."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['diskon']."</td>";
							$nor=0;
                            foreach ($dtNomor as $brs)
                            {
                                $nor+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td align=right colspan=2>".number_format($dtDisknPrsn[$nor],2)."%</td>";
                                    $tab.="<td align=right>".number_format($dtNildis[$nor],2)."</td>";
                                }
                                else
                                {
                                    $tab.="<td align=right colspan=3><input type=text  id=diskon_".$nor." name=diskon_".$nor." class=myinputtextnumber onkeyup=calculate_diskon(".$nor.") maxlength=3 onkeypress=return angka_doang(event) onblur=\"getZero(".$nor.")\" value='".$dtDisknPrsn[$nor]."' style='width:85px'  /></td>";
                                    $tab.="<td align=right><input type=text  id=angDiskon_".$nor." name=angDiskon_".$nor." class=myinputtextnumber  onkeyup=calculate_angDiskon(".$nor.") onkeypress=return angka_doang(event) onblur=\"getZero(".$nor.")\" value='".$dtNildis[$nor]."' style='width:85px' /></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['ppn']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)
                            {
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td align=right colspan=3>".number_format($dtPPN[$ard],2)."</td>";
                                }
                                else
                                {
                                    @$persen[$ard]=($dtPpn[$ard]/($dtSbtotal[$ard]-$dtNildis[$ard]))*100;
                                    $tab.="<td align=right colspan=3><input type=text  id=ppN_".$ard." name=ppN_".$ard." class=myinputtextnumber  onkeyup=calculatePpn(".$ard.")  maxlength=2  onkeypress=return angka_doang(event) onblur=\"getZero(".$ard.")\"  value='".$persen[$ard]."' style='width:85px' /></td>";
                                    $tab.="<td align=right><input type=text  id=ppn_".$ard." name=ppn_".$ard." class=myinputtextnumber  disabled value='".$dtPpn[$ard]."' style='width:85px' /></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['grnd_total']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                $tab.="<td align=right colspan=4 id=grand_total_".$ard.">".number_format($dtNilPer[$ard],2)."</td>";
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td rowspan=10 colspan=3 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>";
                            $tab.="<td colspan=2>".$_SESSION['lang']['nopermintaan']."</td>";
                            $ard=0;
                                foreach ($dtNomor as $brs){
                                    $ard+=1;
                                    if($formPil!='1')
                                    {
                                        $tab.="<td colspan=4>".$_POST['notransaksi']."</td>";
                                    }
                                    else
                                    {
                                        $tab.="<td colspan=4><input type=text disabled id=no_prmntan_".$ard." value='".$_POST['notransaksi']."' class=myinputtext style='width:150px' /></td>";
                                    }
                                }
                                $tab.="</tr>";
                                $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['matauang']."</td>";
                                $ard=0;
                                foreach ($dtNomor as $brs){
                                    $ard+=1;
                                    $optMt="";
                                    $optMt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                                    $sMt="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
                                    $qMt=mysql_query($sMt) or die(mysql_error());
                                    while($rMt=mysql_fetch_assoc($qMt))
                                    {
                                        if($dtMtuang[$ard]!='')
                                        {
                                            $optMt.="<option value=".$rMt['kode']." ".($dtMtuang[$ard]==$rMt['kode']?"selected":" ").">".$rMt['kodeiso']."</option>";
                                        }
                                        else
                                        {
                                            $optMt.="<option value=".$rMt['kode'].">".$rMt['kodeiso']."</option>";
                                        }
                                    }
                                    if($formPil!='1')
                                    {
                                        $tab.="<td colspan=4>".$dtMtuang[$ard]."</td>";
                                    }
                                    else
                                    {
                                        $tab.="<td colspan=4><select id=\"mtUang_".$ard."\" name=\"mtUang_".$ard."\" style=\"width:150px;\" >".$optMt."</select></td>";
                                    }
                                }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['kurs']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td colspan=4>".$kurs[$ard]."</td>";
                                }
                                else
                                {
                                    $tab.="<td colspan=4><input type=\"text\" class=\"myinputtextnumber\" id=\"Kurs_".$ard."\" name=\"Kurs_".$ard."\" style=\"width:150px;\" onkeypress=\"return angka_doang(event)\" value=".$kurs[$ard]."  /></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tgldari']."</td>";
                            $ard=0;
                                foreach ($dtNomor as $brs){
                                    $ard+=1;
                                    if($formPil!='1')
                                    {
                                        $tab.="<td colspan=4>".$dtTglDr[$ard]."</td>";
                                    }
                                    else
                                    {
                                        $tab.="<td colspan=4><input type=text class=myinputtext style='width:150px' id=tgl_dari_".$ard." onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".tanggalnormal($dtTglDr[$ard])."' /></td>";
                                    }
                                }
                            
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tglsmp']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs)  {
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td colspan=4>".$dtTglSmp[$ard]."</td>";
                                }
                                else
                                {
                                    $tab.="<td colspan=4><input type=text class=myinputtext style='width:150px' id=tgl_smp_".$ard." onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".tanggalnormal($dtTglSmp[$ard])."' /></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['syaratPem']."</td>";
                            $ard=0;
							
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td colspan=4>".$arrOptTerm[$dtSisbyr[$ard]]."</td>";
                                }
                                else
                                {
									$optTermPay="";
                                    $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                                    
									$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar order by keterangan asc";
									$qSyp=mysql_query($sSyp) or die(mysql_error($conn));
									while($rSyp=mysql_fetch_assoc($qSyp))
									{
										if($dtSisbyr2[$ard]!=''){
											$optTermPay.="<option value='".$rSyp['kode']."' ".($rSyp['kode']==$dtSisbyr2[$ard]?"selected":"").">".$rSyp['keterangan']." (".$rSyp['jenis'].")</option>";
										}else{
											$optTermPay.="<option value=".$rSyp['kode'].">".$rSyp['keterangan']." (".$rSyp['jenis'].")</option>";
										}
									}
									
									// foreach($arrOptTerm as $brsOptTerm =>$listTerm)
                                    // {
                                        // if($dtSisbyr[$ard]!='0'){
                                            // $optTermPay.="<option value='".$brsOptTerm."' ".($brsOptTerm==$dtSisbyr[$ard]?"selected":"").">".$listTerm."</option>";
                                        // }else{
                                            // $optTermPay.="<option value='".$brsOptTerm."'>".$listTerm."</option>";
                                        // }
                                    // }
                                $tab.="<td colspan=4><select id='term_pay_".$ard."'  style='width:150px'>".$optTermPay."</select></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['stock']."</td>";
                             $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td colspan=4>".$arrStock[$dtStock[$ard]]."</td>";
                                }
                                else
                                {
                                    $optStock="";
                                    $optStock="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                                    foreach($arrStock as $brsStock => $listStock)
                                    {
                                        if($dtStock[$ard]!=''){
                                            $optStock.="<option value='".$brsStock."' ".($brsStock==$dtStock[$ard]?"selected":"").">".$listStock."</option>";
                                        }else{
                                            $optStock.="<option value='".$brsStock."'>".$listStock."</option>";
                                        }
                                    }
                                $tab.="<td colspan=4><select id=stockId_".$ard." style='width:150px'>".$optStock."</select></td>";   
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['almt_kirim']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td colspan=4>".$arrFranco[$dtFranco[$ard]]."</td>";
                                }
                                else
                                {
                                    $optKrm="";
                                    $optKrm="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                                    $sKrm="select id_franco,franco_name from ".$dbname.".setup_franco where status=0 order by franco_name asc";
                                    $qKrm=mysql_query($sKrm) or die(mysql_error($conn));
                                    while($rKrm=mysql_fetch_assoc($qKrm))
                                    {
                                        if($dtFranco[$ard]!='0'){
                                            $optKrm.="<option value=".$rKrm['id_franco']." ".($rKrm['id_franco']==$dtFranco[$ard]?"selected":"").">".$rKrm['franco_name']."</option>";
                                        }else{
                                            $optKrm.="<option value=".$rKrm['id_franco'].">".$rKrm['franco_name']."</option>";
                                        }
                                    }
                                        $tab.="<td colspan=4><select id=tmpt_krm_".$ard." style='width:150px'>".$optKrm."</select></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['keterangan']."</td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                $ard+=1;
                                if($formPil!='1')
                                {
                                    $tab.="<td align=justify colspan=4>".(isset($dtCttn[$ard])? $dtCttn[$ard]: '')."</td>";
                                }
                                else
                                {
                                    $tab.="<td align=justify colspan=4><textarea placeholder='Maximal character 128' maxlength=128 id='ketUraian_".$ard."' name='ketUraian_".$ard."' onkeypress='return tanpa_kutip(event);' cols=18 rows=3>".(isset($dtCttn[$ard])? $dtCttn[$ard]: '')."</textarea></td>";
                                }
                            }
                            $tab.="</tr>";
                            $tab.="<tr class=rowcontent><td colspan=2></td>";
                            $ard=0;
                            foreach ($dtNomor as $brs){
                                 $ard+=1;
                                if($formPil!='0')
                                {
                                    $tab.="<td align=center colspan=4><button class=mybutton id=save_".$ard." onclick=simpanSemua2(".$ard.",".$totBrg.")>".$_SESSION['lang']['save']."</button></td>";
                                }
                            }
                            $tab.="</tr>";
                        
                
                $tab.="</tbody></table>";
                echo $tab;
                break;
                case 'updateTransaksi'://indra
                    
                    $no_prmntan=$_POST['ckno_permintaan'];
                    
                $subTotal=str_replace(',', '', $subTotal);
                $nilaiPermintaan=str_replace(',', '', $nilaiPermintaan);
                $scek="select distinct supplierid from ".$dbname.".log_perintaanhargaht 
                       where nomor='".$no_prmntan."' and nourut='".$_POST['nourut']."'";
                //exit("error".$scek);
                $qcek=mysql_query($scek) or die(mysql_error($conn));
                $rcek=mysql_fetch_assoc($qcek);
                if($_POST['supplierId']==$rcek['$rcek']){
                    exit("error: Supplier Tersebut Sudah Terdaftar");
                }
                
				
				if(strlen($ketUraian)>128)
				{
					exit("Keterangan melebihi 128 character");
				}
				
                $sUpdate="update ".$dbname.".log_perintaanhargaht set id_franco='".intval($idFranco)."', stock='".intval($stockId)."', 
                          catatan='".$ketUraian."',sisbayar2='".$termPay."', ppn='".$nilPPn."', subtotal='".$subTotal."', 
                          diskonpersen='".$diskonPersen."', nilaidiskon='".$nilDiskon."', nilaipermintaan='".$nilaiPermintaan."', 
                          tgldari='".tanggalsystem($_POST['tglDari'])."', tglsmp='".tanggalsystem($_POST['tglSmp'])."', kurs='".$_POST['kurs']."',
                          matauang='".$_POST['mtUang']."',supplierid='".$_POST['supplierId']."'
                          where nomor='".$no_prmntan."' and nourut='".$_POST['nourut']."'";
              // exit("Error:$sUpdate");
              if(mysql_query($sUpdate))
              {
                  $totRow=count($_POST['kdbrg']);
                      foreach($_POST['kdbrg'] as $row=>$Act)
                       {

                        $kdbrg=$Act;
                        $merk=$_POST['merk'][$row];
                        $hrg=$_POST['price'][$row];
                        //$hrg=str_replace(',','',$hrg);
                        $jmlh=$_POST['jmlh'][$row];
                        $qty=$_POST['qty'][$row];

                        $sUpdate2="update ".$dbname.".log_permintaanhargadt set `jumlah`='".$qty."',`harga`='".$hrg."',`merk`='".$merk."' 
                                                where nomor='".$no_prmntan."' and kodebarang='".$kdbrg."' and nourut='".$_POST['nourut']."'";
                        //exit("Error".$sUpdate2);
                        if(mysql_query($sUpdate2))
                        $berhasil+=1;
                        else 
                        echo " Gagal,".$sUpdate2."\n detail".addslashes(mysql_error($conn));
                   }
              }
              else
              {
                  echo $sUpdate."\n";
                  echo " Gagal,".addslashes(mysql_error($conn));
              }
              if($totRow==$berhasil)
              {
                  exit("Done");
              }
            break;
            
            
            case 'get_alasan_batal':
                
                
                 $nomor=$_POST['nomor'];
            $nourut=$_POST['nourut'];
                
		$s_form="select * from ".$dbname.".log_poht where nopo='".$nopo."' ";
		$q_from=mysql_query($s_form) or die (mysql_error($conn));
		$r_form=mysql_fetch_assoc($q_from);
		echo "<div id=form_batal><fieldset><legend>".$nopo."</legend>
				<table cellspacing=1 border=0>
				<tr><td><textarea rows=5 cols=34 id='alasan' placeholder='Maximal character 128' maxlength=128></textarea></td>
				<td><button class=mybutton  onclick=\"postIni('".$nomor."','".$nourut."');\">";echo $_SESSION['lang']['save'];
		echo"</button></td></tr></table></filedset></div>";
		break;
            
            
            
            
            
            
            
            
            
            case'postingData':
                
            $nomor=$_POST['nomor'];
            $nourut=$_POST['nourut'];
            $alasan=$_POST['alasan'];

               
            $iUpdate="update ".$dbname.".log_perintaanhargaht set flag=2 where nomor='".$nomor."'  ";
            if(mysql_query($iUpdate))
            {    
            }
            else
            {
                 echo "Gagal,".$iUpdate."__".(mysql_error($conn));
            }
            
            $wUpdate="update ".$dbname.".log_perintaanhargaht set flag=1,catatanmenang='".$alasan."' "
                    . "where nomor='".$nomor."' and nourut='".$nourut."' ";
           
            if(mysql_query($wUpdate))
            {    
            }
            else
            {
                 echo "Gagal,".$wUpdate."__".(mysql_error($conn));
            }

            break;
            
            
    }


?>