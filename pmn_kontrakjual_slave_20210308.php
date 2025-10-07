<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
$lokasiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$txtSearch=$_POST['txtSearch'];
$kurs=$_POST['kurs'];
$ptSch=$_POST['ptSch'];
$ptKomoditi=$_POST['ptKomoditi'];
$ptCust=$_POST['ptCust'];

$arrBulan=array("01"=>"I","02"=>"II","03"=>"III","04"=>"IV","05"=>"V","06"=>"VI","07"=>"VII","08"=>"VIII","09"=>"IX","10"=>"X","11"=>"XI","12"=>"XII");
        switch($param['method']){
                case'LoadNew':
                    if($txtSearch!='')
                    {
                        $sort=" and nokontrak like '%".$txtSearch."%' ";
                    }
                    
                    if($ptSch!='')
                    {
                        $sort.=" and kodept like '%".$ptSch."%' ";
                    }
                    
                    if($ptKomoditi!='')
                    {
                        $sort.=" and kodebarang like '%".$ptKomoditi."%' ";
                    }
                    
                    if($ptCust!='')
                    {
                        $sort.=" and koderekanan like '%".$ptCust."%' ";
                    }
                   // exit("Error:$sort");
                    
                $limit=15;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".pmn_kontrakjual where kodebarang!='' ".$sort."  order by `tanggalkontrak` desc";// echo $ql2;
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }

                $slvhc="select * from ".$dbname.".pmn_kontrakjual where kodebarang!='' ".$sort."  order by `tanggalkontrak` desc limit ".$offset.",".$limit."";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($res=mysql_fetch_assoc($qlvhc))
                {
                        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer = '".$res['koderekanan']."'"; //echo $sCust;
                        $qCUst=mysql_query($sCust) or die(mysql_error());
                        $rCust=mysql_fetch_assoc($qCUst);

                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where `kodebarang`='".$res['kodebarang']."'";
                        $qBrg=mysql_query($sBrg) or die(mysql_error());
                        $rBrg=mysql_fetch_assoc($qBrg);

                        $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$res['kodept']."'";
                        $qOrg=mysql_query($sOrg) or die(mysql_error());
                        $rOrg=mysql_fetch_assoc($qOrg);

                $no+=1;$arr="##'".$res['nokontrak']."'";	
                echo"
                        <tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$res['nokontrak']."</td>
                        <td>".$rOrg['namaorganisasi']."</td>
                        <td>".$rCust['namacustomer']."</td>
                        <td align='center'>".tanggalnormal($res['tanggalkontrak'])."</td>
                        <td>".$rBrg['namabarang']."</td>
                        <td align='right'>".number_format($res['hargasatuan'],2)."</td>
                        <td align='center'>".($res['ppn']=='0' ? 'Exclude' : 'Include')."</td>
                        <td align='center'>".tanggalnormal($res['tanggalkirim'])."</td>";
                echo"<td width='8%'>";
                #cek apakah sudah terjurnal atau belum
                // $sSum="select distinct noreferensi from ".$dbname.".keu_jurnaldt 
                //        where noreferensi in (select distinct notransaksi from ".$dbname.".pabrik_timbangan where nokontrak='".$res['nokontrak']."') limit 1";
                // $qSum=  mysql_query($sSum) or die(mysql_error($conn));
                // $rSum= mysql_num_rows($qSum);
                // if($rSum==0){
                echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['nokontrak']."');\">&nbsp
                     <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$res['nokontrak']."');\" >&nbsp&nbsp";
                //}    
                echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_kontrakjual','".$res['nokontrak']."','','pmn_kontakjual_pdf',event)\">&nbsp
                     <img onclick=dataKeExcel(event,'pmn_slave_kontrakjual_excel.php','".$res['nokontrak']."') src=images/excel.jpg class=resicon title='MS.Excel'> "; 
                if($res['koderekanan']=='API'){
                    echo"<img src=images/plus.png class=resicon title='Add ".$_SESSION['lang']['nokontrakinduk']." ".$_SESSION['lang']['dari']." ".$res['nokontrak']."' onclick=addDetail('".$res['nokontrak']."','".$res['kuantitaskontrak']."','".$res['kodebarang']."',event) />";
                }


		 echo"</td>
                    </tr>";
                }
                echo"
                <tr class=rowheader><td colspan=9 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
                case'getSatuan':
				$optSatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sSat2="select distinct satuan from ".$dbname.".log_5masterbarang where kodebarang='".$param['kdBrg']."'";
                $qSat2=mysql_query($sSat2) or die(mysql_error());
                $rsat2=mysql_fetch_assoc($qSat2);

                /* $sSat="select distinct a.satuan,b.satuankonversi from ".$dbname.".log_5masterbarang a inner join ".$dbname.".log_5stkonversi b on a.satuan=b.satuankonversi where a.kodebarang='".$param['kdBrg']."' "; //echo"warning:".$sSat;
                $qSat=mysql_query($sSat) or die(mysql_query()); */
                $optSatuan.="<option value=".$rsat2['satuan']."  ".($rsat2['satuan']==$param['satuan']?'selected':'').">".$rsat2['satuan']."</option>";
                /* while($rSat=mysql_fetch_assoc($qSat))
                {
                        $optSatuan.="<option value=".$rSat['satuankonversi']." ".($rSat['satuankonversi']==$satuan?'selected':'').">".$rSat['satuankonversi']."</option>";
                } */
                echo $optSatuan;
                break;
                case'getLastData':
                case'getEditData':
                $sql="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['noKntrk']."'";
                $query=mysql_query($sql) or die(mysql_error());
                $res=mysql_fetch_assoc($query);
                #ambil satuan
                $optSatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sSat2="select distinct satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                $qSat2=mysql_query($sSat2) or die(mysql_error());
                $rsat2=mysql_fetch_assoc($qSat2);
                $optSatuan.="<option value='".$rsat2['satuan']."' selected>".$rsat2['satuan']."</option>";
				
                #ambil data kontak
                $optKom=$optCon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sCust="select distinct idkontak,nama,telepon  from ".$dbname.".pmn_4customercontact where kodecustomer = '".$res['koderekanan']."' order by nama";
                $qCUst=mysql_query($sCust) or die(mysql_error());
                while($rCust=mysql_fetch_assoc($qCUst)){
                        $optCon.="<option value='".$rCust['idkontak']."' ".($rCust['idkontak']==$res['idkontak']?'selected':'').">".$rCust['nama'].",".$rCust['telepon']."</option>";
                    }
                    #ambil data komoditi
                    $sCust2="select distinct kodebarang  from ".$dbname.".pmn_4komoditi where kodecustomer = '".$res['koderekanan']."' order by kodebarang";
                $qCUst2=mysql_query($sCust2) or die(mysql_error());
                while($rCust2=mysql_fetch_assoc($qCUst2)){
                            $whr="kodebarang='".$res['kodebarang']."'";
                            $optBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
                            $optKom.="<option value='".$rCust2['kodebarang']."' ".($rCust2['kodebarang']==$res['kodebarang']?'selected':'').">".$optBrg[$rCust2['kodebarang']]."</option>";
                    }
                        #ambil toleransi
                        $sTol="select distinct toleransipenyusutan  from ".$dbname.".pmn_4customer where kodecustomer='".$param['custId']."'";
                        $qTol=mysql_query($sTol) or die(mysql_error($conn));
                        $rTol=mysql_fetch_assoc($qTol);

                        #bayar ke
                        $optData=$optRek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sRek="select distinct rekening,noakun,namabank from ".$dbname.".keu_5akunbank where pemilik='".$res['kodept']."' order by namabank asc";
                        $qRek=mysql_query($sRek) or die(mysql_error($conn));
                        while($rCek=mysql_fetch_assoc($qRek)){
                                $optRek.="<option value='".$rCek['noakun']."' ".($rCek['noakun']==$res['rekening']?'selected':'').">".$rCek['namabank'].",".$rCek['rekening']."</option>";
                        }
				#ambil nokontrak referensi
				if($res['kodept']!='AMP'){
						$sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
						$qData=mysql_query($sData) or die(mysql_error($conn));
						while($rData=mysql_fetch_assoc($qData)){
							//if($rData['jmlh']<$rData['kuantitaskontrak']){
								$optData.="<option value='".$rData['nokontrak']."'  ".($rData['nokontrak']==$res['nokontrak_ref']?'selected':'').">".$rData['nokontrak']."</option>";
							//}
							
						}
				}
                echo $res['nokontrak']."###".$res['koderekanan']."###".tanggalnormal($res['tanggalkontrak'])."###".$optKom."###".$optSatuan."###".$res['hargasatuan']."###".$res['matauang']."###".$res['terbilang']."###".$res['kuantitaskontrak']."###".tanggalnormal($res['tanggalkirim'])."###".tanggalnormal($res['sdtanggal'])."###".tanggalnormal($res['tanggalkirim1'])."###".tanggalnormal($res['sdtanggal1'])."###".tanggalnormal($res['tanggalkirim2'])."###".tanggalnormal($res['sdtanggal2'])."###".tanggalnormal($res['tanggalkirim3'])."###".tanggalnormal($res['sdtanggal3'])."###".$res['kuantitaskirim']."###".$res['kuantitaskirim1']."###".$res['kuantitaskirim2']."###".$res['kuantitaskirim3']."###".$res['franco']."###".$res['ffa']."###".$res['dobi']."###".$res['mdani']."###".$res['toleransi']."###".$res['kdtermin']."###".$optRek."###".$res['penandatangan']."###".$res['namajabatan']."###".$res['penandatangan2']."###".$res['namajabatan2']."###".$res['catatanlain']."###".$optCon."###".$res['kodept']."###".$res['ppn']."###".tanggalnormal($res['tglpembayarpertama'])."###".$res['moist']."###".$res['dirt']."###".$res['grading']."###".$optData;
                break;
                case'insert':
                    $tgl=explode("-",$param['tlgKntrk']);
                    $whr="kodebarang='".$param['kdBrg']."'";
                    $optKd=makeOption($dbname,'pmn_4komoditi','kodebarang,kodekomoditi',$whr);
					if($optKd[$param['kdBrg']]=='')
					{
						exit("warning : Kode komoditi belum ada, silahkan hubungi admin.");
					}
                    $sCek="select max(nokontrak) as nokontrak from ".$dbname.".pmn_kontrakjual where kodept='".$param['kdPt']."' and left(tanggalkontrak,4)='".$tgl[2]."'";
                    $qCek=mysql_query($sCek) or die(mysql_error($conn));
                    $rCek=mysql_fetch_assoc($qCek);
                    $noKntak=explode("/",$rCek['nokontrak']);
                    if(intval($noKntak[0])==0){
                            $nourut=addZero((intval($noKntak[0])+1),3);
                    }else{
                            $nourut=addZero((intval($noKntak[0])+1),3);
                    }
					// if($optKd[$param['kdBrg']]=='KERNE'){
						// $hKomoditi = 'PK';
					// }else{
						// $hKomoditi = $optKd[$param['kdBrg']];
					// }
                    $nokontrak=$nourut."/".$param['kdPt']."/".$param['custId']."_".$optKd[$param['kdBrg']]."/".$arrBulan[$tgl[1]]."/".$tgl[2];
                    //exit("error:".$nokontrak);
                    if(($param['custId']=='')||($param['kdBrg']=='')||($param['HrgStn']=='')||($param['qty']=='')||($param['tlgKntrk']=='')||($param['satuan']=='')){
                                    echo"Warning: Please complete the form";
                                    exit();
                    }
                    $param['tglKrm0']==''?$param['tglKrm0']='0000-00-00':tanggalsystem($param['tglKrm0']);
                    $param['tglKrm1']==''?$param['tglKrm1']='0000-00-00':tanggalsystem($param['tglKrm1']);
                    $param['tglKrm2']==''?$param['tglKrm2']='0000-00-00':tanggalsystem($param['tglKrm2']);
                    $param['tglKrm3']==''?$param['tglKrm3']='0000-00-00':tanggalsystem($param['tglKrm3']);
                    $param['tglSd0']==''?$param['tglSd0']='0000-00-00':tanggalsystem($param['tglSd0']);
                    $param['tglSd1']==''?$param['tglSd1']='0000-00-00':tanggalsystem($param['tglSd1']);
                    $param['tglSd2']==''?$param['tglSd2']='0000-00-00':tanggalsystem($param['tglSd2']);
                    $param['tglSd3']==''?$param['tglSd3']='0000-00-00':tanggalsystem($param['tglSd3']);
                    $param['jmlh0']==''?$param['jmlh0']=0:$param['jmlh0']=$param['jmlh0'];
                    $param['jmlh1']==''?$param['jmlh1']=0:$param['jmlh1']=$param['jmlh1'];
                    $param['jmlh2']==''?$param['jmlh2']=0:$param['jmlh2']=$param['jmlh2'];
                    $param['jmlh3']==''?$param['jmlh3']=0:$param['jmlh3']=$param['jmlh3'];
                    $param['moist']==''?$param['moist']=0:$param['moist']=$param['moist'];
                    $param['dirt']==''?$param['dirt']=0:$param['dirt']=$param['dirt'];
                    $param['grading']==''?$param['grading']=0:$param['grading']=$param['grading'];
                    $param['kualitasffa']==''?$param['kualitasffa']=0:$param['kualitasffa']=$param['kualitasffa'];
                    $param['kualitasdob']==''?$param['kualitasdob']=0:$param['kualitasdob']=$param['kualitasdob'];
                    $param['kualitasmdani']==''?$param['kualitasmdani']=0:$param['kualitasmdani']=$param['kualitasmdani'];

                            $sIns="insert into ".$dbname.".pmn_kontrakjual (`nokontrak`, `tanggalkontrak`, `koderekanan`, `kodebarang`, `satuan`, `hargasatuan`, `terbilang`, `tanggalkirim`, `sdtanggal`, `tanggalkirim1`, `sdtanggal1`, `tanggalkirim2`, `sdtanggal2`, `tanggalkirim3`, `sdtanggal3`, `rekening`, `kdtermin`, `franco`, `ffa`, `dobi`, `mdani`, `kuantitaskirim`, `kuantitaskirim1`, `kuantitaskirim2`, `kuantitaskirim3`, `penandatangan`, `penandatangan2`, `namajabatan`, `namajabatan2`, `catatanlain`, `kuantitaskontrak`, `toleransi`, `kodeorg`, `kodept`, `matauang`,`idkontak`,`ppn`,`tglpembayarpertama`,`moist`,`dirt`,`grading`,`nokontrak_ref`) 
                                               values ('".$nokontrak."','".tanggalsystem($param['tlgKntrk'])."','".$param['custId']."','".$param['kdBrg']."','".$param['satuan']."','".$param['HrgStn']."','".$param['tBlg']."','".tanggalsystem($param['tglKrm0'])."','".tanggalsystem($param['tglSd0'])."','".tanggalsystem($param['tglKrm1'])."','".tanggalsystem($param['tglSd1'])."','".tanggalsystem($param['tglKrm2'])."','".tanggalsystem($param['tglSd2'])."','".tanggalsystem($param['tglKrm3'])."','".tanggalsystem($param['tglSd3'])."','".$param['byrKe']."','".$param['syrtByr']."','".$param['franco']."','".$param['kualitasffa']."','".$param['kualitasdob']."','".$param['kualitasmdani']."','".$param['jmlh0']."','".$param['jmlh1']."','".$param['jmlh2']."','".$param['jmlh3']."','".$param['tndtng']."','".$param['tndtngPembli']."','".$param['tndtngJbtn']."','".$param['jtbnPembli']."','".$param['cttnLain']."','".$param['qty']."','".$param['tlransi']."','".$_SESSION['empl']['lokasitugas']."','".$param['kdPt']."','".$param['kurs']."','".$param['nmPerson']."','".$param['ppnId']."','".tanggalsystem($param['tglByr'])."',".$param['moist'].",".$param['dirt'].",".$param['grading'].",'".$param['kntrkRef']."')"; //echo"warning:".$sIns;exit();
                                    //exit("Error".$sIns);
                                    if(mysql_query($sIns))
                                    echo"";
                                    else
                                    echo "DB Error : ".mysql_error($conn);	
                break;
                case'update':
                        if(($param['custId']=='')||($param['kdBrg']=='')||($param['HrgStn']=='')||($param['qty']=='')||($param['tlgKntrk']=='')||($param['satuan']=='')){
                                                echo"Warning: Please complete the form";
                                                exit();
                        }


                $sUpd="update ".$dbname.".pmn_kontrakjual set   `tanggalkontrak`='".tanggalsystem($param['tlgKntrk'])."', `koderekanan`='".$param['custId']."', 
                      `kodebarang`='".$param['kdBrg']."', `satuan`='".$param['satuan']."', `hargasatuan`='".$param['HrgStn']."', `terbilang`='".$param['tBlg']."', 
                      `tanggalkirim`='".tanggalsystem($param['tglKrm0'])."', `sdtanggal`='".tanggalsystem($param['tglSd0'])."', `tanggalkirim1`='".tanggalsystem($param['tglKrm1'])."', 
                      `sdtanggal1`='".tanggalsystem($param['tglSd1'])."', `tanggalkirim2`='".tanggalsystem($param['tglKrm2'])."', `sdtanggal2`='".tanggalsystem($param['tglSd2'])."', 
                      `tanggalkirim3`='".tanggalsystem($param['tglKrm3'])."', `sdtanggal3`='".tanggalsystem($param['tglSd3'])."', `rekening`='".$param['byrKe']."', `kdtermin`='".$param['syrtByr']."', 
                      `franco`='".$param['franco']."', `ffa`='".$param['kualitasffa']."', `dobi`='".$param['kualitasdob']."', `mdani`='".$param['kualitasmdani']."', `kuantitaskirim`='".$param['jmlh0']."', 
                      `kuantitaskirim1`='".$param['jmlh1']."', `kuantitaskirim2`='".$param['jmlh2']."', `kuantitaskirim3`='".$param['jmlh3']."', `penandatangan`='".$param['tndtng']."', `penandatangan2`='".$param['tndtngPembli']."', 
                      `namajabatan`='".$param['tndtngJbtn']."', `namajabatan2`='".$param['jtbnPembli']."', `catatanlain`='".$param['cttnLain']."', `kuantitaskontrak`='".$param['qty']."', `toleransi`='".$param['tlransi']."', `kodept`='".$param['kdPt']."', 
                      `matauang`='".$param['kurs']."',idkontak='".intval($param['nmPerson'])."',ppn='".$param['ppnId']."',`tglpembayarpertama`='".tanggalsystem($param['tglByr'])."',moist=".$param['moist'].",dirt=".$param['dirt'].",grading=".$param['grading'].",
                      `nokontrak_ref`='".$param['kntrkRef']."'  where nokontrak='".$param['noKntrk']."'"; 
				//echo"warning:".$sUpd;exit();
                        //exit("Error".$sUpd);
                        if(mysql_query($sUpd))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);	
                break;
                case'getCust':	
                            $optKom=$optCon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sCust="select distinct idkontak,nama,telepon  from ".$dbname.".pmn_4customercontact where kodecustomer = '".$param['custId']."' order by nama";
                $qCUst=mysql_query($sCust) or die(mysql_error());
                while($rCust=mysql_fetch_assoc($qCUst)){
                                $optCon.="<option value='".$rCust['idkontak']."'>".$rCust['nama'].",".$rCust['telepon']."</option>";
                        }
                        $sCust2="select distinct kodebarang  from ".$dbname.".pmn_4komoditi where kodecustomer = '".$param['custId']."' order by kodebarang";
                $qCUst2=mysql_query($sCust2) or die(mysql_error());
                while($rCust2=mysql_fetch_assoc($qCUst2)){
                            $whr="kodebarang='".$rCust2['kodebarang']."'";
                            $optBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
                            $optKom.="<option value='".$rCust2['kodebarang']."'>".$optBrg[$rCust2['kodebarang']]."</option>";
                    }
                    $sTol="select distinct toleransipenyusutan  from ".$dbname.".pmn_4customer where kodecustomer='".$param['custId']."'";
                    $qTol=mysql_query($sTol) or die(mysql_error($conn));
                    $rTol=mysql_fetch_assoc($qTol);
				
                echo $optCon."###".$optKom."###".$rTol['toleransipenyusutan'];
                break;

                case'dataDel':
                $sDel="delete from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['noKntrk']."'" ; //echo "warning:".$sDel;
                if(mysql_query($sDel))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);	
                break;
                    case'getRek': 
                            $optData=$optRek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                            $sRek="select distinct rekening,noakun,namabank from ".$dbname.".keu_5akunbank where pemilik='".$_POST['kdpt']."' order by namabank asc";
                            $qRek=mysql_query($sRek) or die(mysql_error($conn));
                            while($rCek=mysql_fetch_assoc($qRek)){
                                    $optRek.="<option value='".$rCek['noakun']."'>".$rCek['namabank'].",".$rCek['rekening']."</option>";
                            }
							if($_POST['kdpt']!='AMP'){
								$sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
								$qData=mysql_query($sData) or die(mysql_error($conn));
								while($rData=mysql_fetch_assoc($qData)){
									if($rData['jmlh']<$rData['kuantitaskontrak']){
										$optData.="<option value='".$rData['nokontrak']."'>".$rData['nokontrak']."</option>";
									}
									
								}
							}
							
                            echo $optRek."####".$optData;
                    break;
                case'getFormDet':
                        $optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sData="select kuantitaskontrak,nokontrak from ".$dbname.".pmn_kontrakjual"
                             . " where kodept='AMP' and kodebarang='".$_POST['komoditi']."' order by nokontrak";
                        $qData=mysql_query($sData) or die(mysql_error($conn));
                        while($rData=mysql_fetch_assoc($qData)){
                                $sSum="select sum(beratbersih) as jmlh from ".$dbname.".pabrik_timbangan where nokontrak='".$rData['nokontrak']."'";
                                $qSum=  mysql_query($sSum) or die(mysql_error($conn));
                                $rSum=  mysql_fetch_assoc($qSum);
                                //if($rSum['jmlh']<$rData['kuantitaskontrak']){
                                        $optData.="<option value='".$rData['nokontrak']."'>".$rData['nokontrak']."</option>";
                                //}
                        }
                        //echo $sData;
                    $tab.="<table cellpadding=1 cellspacing=1 border=0>";
                    $tab.="<thead><tr>";
                    $tab.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['volumekontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['nokontrakinduk']."</td>";
                    $tab.="<td>".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td>".$_SESSION['lang']['action']."</td>";
                    $tab.="<tr></thead><tbody>";
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td><input type=text id=nokontrak class=myinputtext value='".$_POST['nokontrak']."' readonly=readonly style=width:150px /></td>";
                    $tab.="<td><input type=text id=jmlHnokontrak class=myinputtextnumber value='".number_format($_POST['totKontrak'],0)."' readonly=readonly /></td>";
                    $tab.="<td><select id=nokntr_ref>".$optData."</select></td>";
                    $tab.="<td><input type=text class=myinputtextnumber id=jmlhRef onkeypress='return angka_doang(event)' /></td>";
                    $tab.="<td><input type=hidden id=nokntr_ref2 value='' /><img src=images/save.png class=resicon onclick=saveDet() /></td>";
                    $tab.="</tr>";
                    $tab.="</tbody></table><br />";
                    $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100%>";
                    $tab.="<thead><tr>";
                    $tab.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['volumekontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['nokontrakinduk']."</td>";
                    $tab.="<td>".$_SESSION['lang']['kuota']."</td>";
                    $tab.="<td>".$_SESSION['lang']['terpenuhi']."</td>";
                    $tab.="<td>".$_SESSION['lang']['sisa']."</td>";
                    $tab.="<td>".$_SESSION['lang']['action']."</td>";
                    $tab.="<tr></thead><tbody id=isidetail>";
                    $sData="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
                    $qData=  mysql_query($sData) or die(mysql_error($conn));
                    $rwDt=  mysql_num_rows($qData);
                    if($rwDt==0){
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }else{
                        while($rData=  mysql_fetch_assoc($qData)){
                            $tab.="<tr class=rowcontent>";
                            $tab.="<td>".$rData['nokontrak']."</td>";
                            $tab.="<td align=right>".number_format($_POST['totKontrak'],0)."</td>";
                            $tab.="<td>".$rData['nokontrak_ref']."</td>";
                            $tab.="<td align=right>".number_format($rData['kuota'],0)."</td>";
                            $tab.="<td align=right>".number_format($rData['terpenuhi'],0)."</td>";
                            $rData['sisa']=$rData['kuota']-$rData['terpenuhi'];
                            $tab.="<td align=right>".number_format($rData['sisa'],0)."</td>";
                            if($rData['terpenuhi']==0){
                                $tab.="<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\" ></td>";
                            }else{
                                $tab.="<td>&nbsp;</td>";
                            }
                            
                            $tab.="</tr>";
                        }
                    }
                    
                    $tab.="</tbody></table>";
                    echo $tab;
                break;
                case'loadDet':
                    $whr="nokontrak='".$_POST['nokontrak']."'";
                    $optTot=  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kuantitaskontrak',$whr);
                    $sData="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
                    $qData=  mysql_query($sData) or die(mysql_error($conn));
                    $rwDt=  mysql_num_rows($qData);
                    if($rwDt==0){
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }else{
                        while($rData=  mysql_fetch_assoc($qData)){
                            $tab.="<tr class=rowcontent>";
                            $tab.="<td>".$rData['nokontrak']."</td>";
                            $tab.="<td align=right>".number_format($optTot[$_POST['nokontrak']],0)."</td>";
                            $tab.="<td>".$rData['nokontrak_ref']."</td>";
                            $tab.="<td align=right>".number_format($rData['kuota'],0)."</td>";
                            $tab.="<td align=right>".number_format($rData['terpenuhi'],0)."</td>";
                            $rData['sisa']=$rData['kuota']-$rData['terpenuhi'];
                            $tab.="<td align=right>".number_format($rData['sisa'],0)."</td>";
                            if($rData['terpenuhi']==0){
                                $tab.="<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\" ></td>";
                            }else{
                                $tab.="<td>&nbsp;</td>";
                            }
                            $tab.="</tr>";
                        }
                    }
                    echo $tab;
                break;
                case'saveDet':
                    
                    $_POST['jmlHnokontrak']=  str_replace(",","", $_POST['jmlHnokontrak']);
                    $sCek="select terpenuhi from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                    
					$qCek=  mysql_query($sCek) or die(mysql_error($conn));
                    $rCek=  mysql_fetch_assoc($qCek);
                    if($rCek['terpenuhi']==0){
                        #cek apakah pembagian kuantitas kontrak induk sudah lebih atau belum
                        #query mengambil kuantitaskontrak nokontrak induk
                        $sCekKontrakInduk="select kuantitaskontrak from ".$dbname.".pmn_kontrakjual where nokontrak='".$_POST['nokntr_ref']."'";
                        $qCekKontrakInduk=  mysql_query($sCekKontrakInduk) or die(mysql_error($conn));
                        $rCekKontrakInduk=  mysql_fetch_assoc($qCekKontrakInduk);
                        #query cari data totalan kuota atas nokontrak induk
                        $sSum2="select sum(kuota) as total from ".$dbname.".pmn_kontrakjualdt where nokontrak_ref='".$_POST['nokntr_ref']."'";
                        $qSum2=  mysql_query($sSum2) or die(mysql_error($conn));
                        $rSum2= mysql_fetch_assoc($qSum2);
                        if(intval($rSum2['total'])>$rCekKontrakInduk['kuantitaskontrak']){
                            exit("warning: Total distribusi ".$_SESSION['lang']['kuota']." (".$rSum2['total'].") melebihi ".$_SESSION['lang']['volumekontrak']." (".$rCekKontrakInduk['kuantitaskontrak'].") ".$_SESSION['lang']['nokontrakinduk']." : ".$_POST['nokntr_ref']);
                        }
                        
                        #cek apakah sudah melebihi kuota kontrak detail
                        $sSum="select sum(kuota) as total from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
                        $qSum=  mysql_query($sSum) or die(mysql_error($conn));
                        $rSum= mysql_fetch_assoc($qSum);
                        if(($rSum['total']+$_POST['jmlhRef'])>$_POST['jmlHnokontrak']){
                            exit("warning: Total ".$_SESSION['lang']['kuota']." melebihi ".$_SESSION['lang']['volumekontrak']."  ".$_POST['nokontrak']);
                        }
                                if($_POST['nokntr_ref2']==''){                    
                                    #insert detail dari no induk
                                     $sdel="delete from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                                    if(mysql_query($sdel)){
                                        $sInsert="insert into ".$dbname.".pmn_kontrakjualdt values ('".$_POST['nokontrak']."','".$_POST['nokntr_ref']."','".$_POST['jmlhRef']."','0')";
                                        if(!mysql_query($sInsert)){
                                            exit("warning: ".mysql_error($conn)."___".$sInsert);
                                        }
                                    }else{
                                            exit("warning: ".mysql_error($conn)."___".$sdel);
                                    }
                                }else{
                                    $supdate="update ".$dbname.".pmn_kontrakjualdt set kuota='".$_POST['jmlhRef']."',nokontrak_ref='".$_POST['nokntr_ref']."' where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref2']."'";
                                    if(!mysql_query($supdate)){
                                            exit("warning: ".mysql_error($conn)."___".$supdate);
                                    }
                                }
                    }else{
                        exit("warning:  Jurnal Sudah Terbentuk");
                    }
                break;
                case'delDet':
                    $sCek="select terpenuhi from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                    $qCek=  mysql_query($sCek) or die(mysql_error($conn));
                    $rCek=  mysql_fetch_assoc($qCek);
                    if($rCek['terpenuhi']==0){
                         $sdel="delete from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                         if(!mysql_query($sdel)){
                              exit("warning: ".mysql_error($conn)."___".$sdel);
                         }
                    }else{
                        exit("warning: Jurnal Sudah Terbentuk");
                    }
                break;
                case'editDet':
                    $sCek="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                    $qCek=  mysql_query($sCek) or die(mysql_error($conn));
                    $rCek=  mysql_fetch_assoc($qCek);
                    if($rCek['terpenuhi']==0){
                        $optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
                        $qData=mysql_query($sData) or die(mysql_error($conn));
                        while($rData=mysql_fetch_assoc($qData)){
                                //if($rData['jmlh']<$rData['kuantitaskontrak']){
                                        $optData.="<option value='".$rData['nokontrak']."' ".($rCek['nokontrak_ref']==$rData['nokontrak']?"selected":"").">".$rData['nokontrak']."</option>";
                                //}
                        }
                        echo $rCek['nokontrak']."####".$optData."####".$rCek['kuota']."####".$rCek['nokontrak_ref'];
                    }else{
                        exit("warning: Jurnal Sudah Terbentuk");
                    }
                break;
                default:
                break;
        }

?>