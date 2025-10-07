<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$periodeawal=checkPostGet('periodeawal','');
$periodeakhir=checkPostGet('periodeakhir','');
$exelcuti=checkPostGet('exelcuti','');
$excelkaryawanid=checkPostGet('excelkaryawanid','');
$proses=checkPostGet('proses','');
$_POST['tglijin']==''?$tglijin=tanggalsystem($_GET['tglijin']):$tglijin=tanggalsystem($_POST['tglijin']);
$_POST['krywnId']==''?$krywnId=$_GET['krywnId']:$krywnId=$_POST['krywnId'];
$stat=$_POST['stat'];
$ket=$_POST['ket'];
$arrNmkary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan=array("0"=>$_SESSION['lang']['diajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
$where=" tanggal='".$tglijin."' and karyawanid='".$krywnId."'";
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arragama=getEnum($dbname,'sdm_ijin','jenisijin');
$jnsCuti=$_POST['jnsCuti'];
$karyidCari=$_POST['karyidCari'];
$atasan=$_POST['atasan'];
$unit=$_POST['unit'];

$periodeakhir=checkPostGet('periodeakhir','');
$pAwal = tanggalsystem($periodeawal);
$pAkhir = tanggalsystem($periodeakhir);
$tahunakhir=substr($pAkhir,0,4);

//exit("Warning: ".$tahunakhir);
        switch($proses)
        {
			case'getKary':
				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
					//$sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in(0,1,2,3,6,7,8,9) and lokasitugas='".$unit."'
					//		order by namakaryawan asc";
					$sKary="select distinct(a.karyawanid) as karyawanid, b.namakaryawan from ".$dbname.".sdm_ijin a
							LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
							where if(b.subbagian='',b.lokasitugas,left(b.subbagian,4)) like '%".$unit."%' 
							order by namakaryawan asc";
					$qKary=mysql_query($sKary) or die(mysql_error($sKary));
					while($rKary=mysql_fetch_assoc($qKary))
					{
						$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
					}
				}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA' || $_SESSION['empl']['bagian'] == 'HHRS'){
					$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
					//$sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan where substr(lokasitugas,3,2)!='HO' and tipekaryawan in(0,1,2,3,7,8) and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' and lokasitugas='".$unit."' order by namakaryawan asc";
					$sKary="select distinct(a.karyawanid) as karyawanid, b.namakaryawan from ".$dbname.".sdm_ijin a
							LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
							where substr(b.lokasitugas,3,2)!='HO' and b.kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' 
							and if(b.subbagian='',b.lokasitugas,left(b.subbagian,4)) like '%".$unit."%' 
							order by namakaryawan asc";
					$qKary=mysql_query($sKary) or die(mysql_error($sKary));
					while($rKary=mysql_fetch_assoc($qKary))
					{
						$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
					}
				}else{
					$optKary="";
					$sKary="select distinct(a.karyawanid) as karyawanid, b.namakaryawan from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b 
						where a.karyawanid=b.karyawanid and (a.persetujuan1='".$_SESSION['standard']['userid']."' or hrd='".$_SESSION['standard']['userid']."')";
					$qKary=mysql_query($sKary) or die(mysql_error($sKary));
					while($rKary=mysql_fetch_assoc($qKary))
					{
						$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
					}
					$optKary.="<option value='".$_SESSION['standard']['userid']."'>".$_SESSION['empl']['name']."</option>";
				}
				echo $optKary;
				break;

			case'loadData':
				$limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
				
				$tmbWhere = '';
				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					//$tmbWhere = '';
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
				}else if($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' and $_SESSION['empl']['bagian'] == 'HRA'){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' and substr(b.lokasitugas,3,2)!='HO' and b.kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' ".$tmbWhere." order by a.tanggal desc";
				}else{
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
				}
				
//				$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
					$jlhbrs= $jsl->jmlhrow;
                }
				if($jlhbrs <= 0){
					echo"<tr class=rowcontent>
							<td colspan=14 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
						</tr>";
					exit();
				}
				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}else if($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' and $_SESSION['empl']['bagian'] == 'HRA'){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' and substr(b.lokasitugas,3,2)!='HO' and b.kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}else{
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}
//                $slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                if($_SESSION['language']=='ID'){
                        $dd=$rlvhc['jenisijin'];
                    }else{
                        switch($rlvhc['jenisijin']){
                            case 'TERLAMBAT':
                                $dd='Late for work';
                                break;
                            case 'KELUAR':
                                $dd='Out of Office';
                                break;         
                            case 'PULANGAWAL':
                                $dd='Home early';
                                break;     
                            case 'IJINLAIN':
                                $dd='Other purposes';
                                break;   
                            case 'CUTI':
                                $dd='Leave';
                                break;       
                            case 'MELAHIRKAN':
                                $dd='Maternity';
                                break;
							case 'PERJALANAN':
								$fal='Travel';
								break;           
							case 'SKRIPSI_TESIS':
                               $fal='Skripsi/Tesis';
                               break;           
                            default:
                                $dd='Important Reason';
                                break;                              
                        }      
                    }
                    
                $no+=1;
                //ambil sisa cuti
                $sSisa="select sisa from ".$dbname.".sdm_cutiht where karyawanid='".$rlvhc['karyawanid']."' 
                        and periodecuti='".$rlvhc['periodecuti']."'";
                $qSisa=mysql_query($sSisa) or die(mysql_error($conn));
                $rSisa=mysql_fetch_assoc($qSisa);
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".$arrNmkary[$rlvhc['karyawanid']]."</td>
                <td>".$rlvhc['keperluan']."</td>
                <td>".$dd."</td>
                <td>".$arrNmkary[$rlvhc['persetujuan1']]."</td>
                <td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>
                <td>".$rlvhc['darijam']."</td>
                <td>".$rlvhc['sampaijam']."</td>
                <td align=center>".$rlvhc['jumlahhari']."</td>
                <td align=center>".$rSisa['sisa']."</td>";
//atasan==============================                
                if($rlvhc['persetujuan1']==$_SESSION['standard']['userid'])
                {
                    if($rlvhc['stpersetujuan1']==0)
                    {
                      echo"<td align=center>
                         <button class=mybutton id=dtlForm onclick=appSetuju('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."')>".$_SESSION['lang']['disetujui']."</button>
                         <button class=mybutton id=dtlForm onclick=showAppTolak('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>".$_SESSION['lang']['ditolak']."</button>
                         <button class=mybutton id=dtlForm onclick=showAppForw('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>Forward</button></td>";
                    }
                    else if($rlvhc['stpersetujuan1']==2)
                       echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
                    else if($rlvhc['stpersetujuan1']==1)
                       echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";

                }
                else if($rlvhc['stpersetujuan1']==1)
                    echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                else if($rlvhc['stpersetujuan1']==0)
                    echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>";
                else 
                    echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
//=============hrd                
                if($rlvhc['hrd']==$_SESSION['standard']['userid'])
                {
                    if($rlvhc['stpersetujuanhrd']==0 and $rlvhc['stpersetujuan1']==1)
                    {
                      echo"<td align=center><button class=mybutton id=dtlForm onclick=appSetujuHRD('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."')>".$_SESSION['lang']['disetujui']."</button>
                         <button class=mybutton id=dtlForm onclick=showAppTolakHRD('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                    }
                    else if($rlvhc['stpersetujuan1']==0)
                       echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>";
                    else if($rlvhc['stpersetujuan1']==2 or $rlvhc['stpersetujuanhrd']==2)
                       echo"<td align=center>(".$_SESSION['lang']['ditolak']."</td>"; 
                    else if($rlvhc['stpersetujuanhrd']==1)
                       echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                }
                else
                {
                if($rlvhc['stpersetujuanhrd']=='0')
                   echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>"; 
                else if($rlvhc['stpersetujuanhrd']=='1')
                   echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                else 
                   echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
                }
//======================================                

                   echo"<td align=center> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)\"></td>";


              }//end while
                echo"
                </tr><tr class=rowheader><td colspan=13 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
           case'cariData':
                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
				
				$tmbWhere = '';
				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					//$tmbWhere = '';
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
				}else if($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' and $_SESSION['empl']['bagian'] == 'HRA'){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' and substr(b.lokasitugas,3,2)!='HO' and b.kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' ".$tmbWhere." order by a.tanggal desc";
				}else{
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
				}

                //$ql2="select count(a.karyawanid) as jmlhrow from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc";
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }
				
				if($jlhbrs <= 0){
					echo"<tr class=rowcontent>
							<td colspan=14 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
						</tr>";
					exit();
				}

				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}else if($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' and $_SESSION['empl']['bagian'] == 'HRA'){
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' and substr(b.lokasitugas,3,2)!='HO' and b.kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}else{
					$tmbWhere = " and a.karyawanid like '%".$karyidCari."%'";
					$tmbWhere .= " and b.lokasitugas like '".$unit."%'";
					$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
				}
                //$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$jnsCuti."%' ".$tmbWhere." order by a.tanggal desc limit ".$offset.",".$limit." ";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                    if($_SESSION['language']=='ID'){
                        $dd=$rlvhc['jenisijin'];
                    }else{
                        switch($rlvhc['jenisijin']){
                            case 'TERLAMBAT':
                                $dd='Late for work';
                                break;
                            case 'KELUAR':
                                $dd='Out of Office';
                                break;         
                            case 'PULANGAWAL':
                                $dd='Home early';
                                break;     
                            case 'IJINLAIN':
                                $dd='Other purposes';
                                break;   
                            case 'CUTI':
                                $dd='Leave';
                                break;       
                            case 'MELAHIRKAN':
                                $dd='Maternity';
                                break;
							case 'PERJALANAN':
								$fal='Travel';
								break;           
							case 'SKRIPSI_TESIS':
								$fal='Skripsi/Tesis';
								break;           
                            default:
                                $dd='Important Reason';
                                break;                              
                        }      
                    }                    
                $no+=1;
                //ambil sisa cuti
                $sSisa="select sisa from ".$dbname.".sdm_cutiht where karyawanid='".$rlvhc['karyawanid']."' and periodecuti='".$rlvhc['periodecuti']."'
                        order by periodecuti desc limit 1";
                $qSisa=mysql_query($sSisa) or die(mysql_error($conn));
                $rSisa=mysql_fetch_assoc($qSisa);
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".$arrNmkary[$rlvhc['karyawanid']]."</td>
                <td>".$rlvhc['keperluan']."</td>
                <td>".$dd."</td>
                <td>".$arrNmkary[$rlvhc['persetujuan1']]."</td>
                <td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>
                <td>".$rlvhc['darijam']."</td>
                <td>".$rlvhc['sampaijam']."</td>
                <td align=center>".$rlvhc['jumlahhari']."</td>
                <td align=center>".$rSisa['sisa']."</td>";
//atasan==============================                
                if($rlvhc['persetujuan1']==$_SESSION['standard']['userid'])
                {
                    if($rlvhc['stpersetujuan1']==0)
                    {
                      echo"<td align=center>
                          <button class=mybutton id=dtlForm onclick=appSetuju('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."')>".$_SESSION['lang']['disetujui']."</button>
                          <button class=mybutton id=dtlForm onclick=showAppTolak('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>".$_SESSION['lang']['ditolak']."</button>
                          <button class=mybutton id=dtlForm onclick=showAppForw('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>Forward</button></td>";
                    }
                    else if($rlvhc['stpersetujuan1']==2)
                       echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
                    else
                        echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";

                }
                else if($rlvhc['stpersetujuan1']==1)
                    echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                else if($rlvhc['stpersetujuan1']==0)
                    echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>";
                else 
                    echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
//=============hrd                
                if($rlvhc['hrd']==$_SESSION['standard']['userid'])
                {
                    if($rlvhc['stpersetujuanhrd']==0 and $rlvhc['stpersetujuan1']==1)
                    {
                      echo"<td align=center><button class=mybutton id=dtlForm onclick=appSetujuHRD('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."')>".$_SESSION['lang']['disetujui']."</button>
                         <button class=mybutton id=dtlForm onclick=showAppTolakHRD('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                    }
                    else if($rlvhc['stpersetujuan1']==0)
                       echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>"; 
                    else if($rlvhc['stpersetujuan1']==2 or $rlvhc['stpersetujuanhrd']==2)
                       echo"<td align=center>(".$_SESSION['lang']['ditolak']."</td>"; 
                    else if($rlvhc['stpersetujuanhrd']==1)
                       echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                }
                else if($rlvhc['stpersetujuanhrd']==1)
                    echo"<td align=center>".$_SESSION['lang']['disetujui']."</td>";
                else if($rlvhc['stpersetujuanhrd']==0)
                    echo"<td align=center>".$_SESSION['lang']['wait_approval']."</td>";
                else 
                    echo"<td align=center>".$_SESSION['lang']['ditolak']."</td>";
//======================================                

                   echo"<td align=center> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)\"></td>";


              }//end while
                echo"
                </tr><tr class=rowheader><td colspan=13 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
                case'appSetuju':
                $sket="select distinct jenisijin,stpersetujuan1,persetujuan1,hrd,tanggal from ".$dbname.".sdm_ijin where ".$where."";
                $qKet=mysql_query($sket) or die(mysql_error($conn));
                $rKet=mysql_fetch_assoc($qKet);
//                if(($rKet['stpersetujuan1']=='0')&&($rKet['persetujuan1']==$_SESSION['standard']['userid']))
//                {
                    if($stat==1)
                    {
                        $ket="permintaaan ".$arrNmkary[$krywnId]." ".$arrKeputusan[$stat]."";
                    }

                    $sUpdate="update ".$dbname.".sdm_ijin  set stpersetujuan1='".$stat."',komenst1='".$ket."' where ".$where."";
                    if(mysql_query($sUpdate))
                    {
                          #send an email to incharge person
                            $to=getUserEmail($rKet['hrd']);////email ke hrd setelah persetujuan atasan
                                    $namakaryawan=$arrNmkary[$krywnId];
                                    $subject="[Notifikasi]Persetujuan Ijin Keluar Kantor a/n ".$namakaryawan;
                                    $body="<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  ".tanggalnormal($rKet['tanggal'])." karyawan a/n  ".$namakaryawan." telah ".$arrKeputusan[$stat].". 
                                               Oleh atasan ybs. Selanjutnya, mohon persetujuan dari HRD. Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
                                    $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                    }
                    else
                    {
                        echo "DB Error : ".mysql_error($conn);     
                    }
//                }
//                else
//                {
//                    exit("Error:Sudah memiliki keputusan");
//                }
                break;

                case 'appSetujuHRD':
                $sket="select distinct darijam,sampaijam,jumlahhari,jenisijin,stpersetujuanhrd,hrd,tanggal,periodecuti from ".$dbname.".sdm_ijin where ".$where."";   
                $qKet=mysql_query($sket) or die(mysql_error($conn));
                $rKet=mysql_fetch_assoc($qKet);
//                if(($rKet['stpersetujuanhrd']=='0')&&($rKet['hrd']==$_SESSION['standard']['userid']))
//                {
                    if($stat==1)
                    {
                        $ket="permintaaan ".$arrNmkary[$krywnId]." ".$arrKeputusan[$stat]."";
                        //===============insert to sdm_cuti

                        $stru="select lokasitugas from ".$dbname.".datakaryawan where karyawanid=".$krywnId;
                        $resu=mysql_query($stru);
                        $kodeorg='';
                        while($baru=mysql_fetch_object($resu))
                        {
                            $kodeorg=$baru->lokasitugas;
                        }
                        if($kodeorg=='')
                            exit('Error: Karywan tidak memiliki loaksi tugas');

                        if($rKet['jenisijin']=='CUTI' or $rKet['jenisijin']=='MELAHIRKAN' or $rKet['jenisijin']=='PERJALANAN' or $rKet['jenisijin']=='SKRIPSI_TESIS' or $rKet['jenisijin']=='ALASANPENTING')
                        {
                              //insert to cuti
                            $str="insert into ".$dbname.".sdm_cutidt 
                                (kodeorg,karyawanid,periodecuti,daritanggal,
                                    sampaitanggal,jumlahcuti,keterangan
                                    )
                                values('".$kodeorg."',".$krywnId.",
                                    '".$rKet['periodecuti']."','".substr($rKet['darijam'],0,10)."','".substr($rKet['sampaijam'],0,10)."',".$rKet['jumlahhari'].",'".$rKet['jenisijin']."'
                                    )";

                            if(mysql_query($str))
                             {
                                //ambil sum jumlah diambil dan update table header
								/*
                                $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
                                    where kodeorg='".$kodeorg."' and keterangan = 'CUTI'
                                        and karyawanid=".$krywnId."
                                        and periodecuti='".$rKet['periodecuti']."'";
								*/
                                $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
                                    where upper(keterangan) like '%CUTI%'
                                        and karyawanid=".$krywnId."
                                        and periodecuti='".$rKet['periodecuti']."'";

                                $diambil=0;
                                $resx=mysql_query($strx);
                                while($barx=mysql_fetch_object($resx))
                                {
                                        $diambil=$barx->diambil;
                                }
                                if($rKet['jenisijin']=='CUTI')
                                if($diambil=='')
                                    $diambil=0;
								/*
                                $strup="update ".$dbname.".sdm_cutiht set diambil=".$diambil.",sisa=(hakcuti-".$diambil.")	
                                    where kodeorg='".$kodeorg."'
                                        and karyawanid=".$krywnId."
                                        and periodecuti='".$rKet['periodecuti']."'";
								*/
                                $strup="update ".$dbname.".sdm_cutiht set diambil=".$diambil.",sisa=(hakcuti-".$diambil.")	
                                    where karyawanid=".$krywnId."
                                        and periodecuti='".$rKet['periodecuti']."'";

                                if($rKet['jenisijin']=='CUTI')mysql_query($strup);
                            }  
                            else
                            {
                                echo mysql_error($conn);
                                exit("Error: Update table cuti");
                            } 
						}
					}
                    $sUpdate="update ".$dbname.".sdm_ijin  set stpersetujuanhrd='".$stat."',komenst2='".$ket."' where ".$where."";
                    if(mysql_query($sUpdate))
                    {
                          #send an email to incharge person
                            $to=getUserEmail($rKet['hrd']);
                                    $namakaryawan=getNamaKaryawan($krywnId);
                                    $subject="[Notifikasi]Persetujuan Ijin Keluar Kantor a/n ".$namakaryawan;
                                    $body="<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  ".tanggalnormal($rKet['tanggal'])." karyawan a/n  ".$namakaryawan." telah ".$arrKeputusan[$stat].". 
                                                   Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
                                    $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                    }
                    else
                    {
                        echo "DB Error : ".mysql_error($conn);     
                    }
                
//                }
//                else
//                {
//                    exit("Error:Sudah memiliki keputusan");
//                }    
                break;
					
    case'prevPdf':

        class PDF extends FPDF
        {

            function Header()
            {
                $path='images/logo.jpg';
                $this->Image($path,15,2,20);	
                    $this->SetFont('Arial','B',10);
                    $this->SetFillColor(255,255,255);	
                    $this->SetXY(0,25);   
                $this->Cell(60,5,$_SESSION['org']['namaorganisasi'],0,1,'C');	 
                    $this->SetFont('Arial','',15);
                $this->Cell(190,5,'',0,1,'C');
                    $this->SetFont('Arial','',6); 
                    $this->SetY(30);
                    $this->SetX(163);
            $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
                    $this->Line(10,32,200,32);	   

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }

        }

  $str="select * from ".$dbname.".sdm_ijin where ".$where."";	
  //exit("Error".$str);
  $res=mysql_query($str);
  while($bar=mysql_fetch_object($res))
  {

                $jabatan='';
                $namakaryawan='';
                $bagian='';	
                $karyawanid='';
		  $nik='';
		  $namabagian='';
                 $strc="select a.namakaryawan,a.karyawanid,a.nik,a.bagian,b.namajabatan,c.nama as namabagian
                    from ".$dbname.".datakaryawan a left join  ".$dbname.".sdm_5jabatan b
                        on a.kodejabatan=b.kodejabatan left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
                        where a.karyawanid=".$bar->karyawanid;
      $resc=mysql_query($strc);
          while($barc=mysql_fetch_object($resc))
          {
                $jabatan=$barc->namajabatan;
                $namakaryawan=$barc->namakaryawan;
                $bagian=$barc->bagian;
                $karyawanid=$barc->karyawanid;
                $nik=$barc->nik;
		  $namabagian=$barc->namabagian;
          }

          //===============================	  

                $perstatus=$bar->stpersetujuan1;
                $tgl=tanggalnormal($bar->tanggal);
                $kperluan=$bar->keperluan;
                $persetujuan=$bar->persetujuan1;
                $jns=$bar->jenisijin;
                $jmDr=$bar->darijam;
                $jmSmp=$bar->sampaijam;
                $koments=$bar->komenst1;
                $ket=$bar->keterangan;
                $periode=$bar->periodecuti;
                $sthrd=$bar->stpersetujuanhrd;
                $hk=$bar->jumlahhari;
                $hrd=$bar->hrd;
                $koments2=$bar->komenst2;
                if($_SESSION['language']=='ID'){
                        $dd=$jns;
                    }else{
                        switch($jns){
                            case 'TERLAMBAT':
                                $dd='Late for work';
                                break;
                            case 'KELUAR':
                                $dd='Out of Office';
                                break;         
                            case 'PULANGAWAL':
                                $dd='Home early';
                                break;     
                            case 'IJINLAIN':
                                $dd='Other purposes';
                                break;   
                            case 'CUTI':
                                $dd='Leave';
                                break;       
                            case 'MELAHIRKAN':
                                $dd='Maternity';
                                break;
							case 'PERJALANAN':
								$fal='Travel';
								break;           
							case 'SKRIPSI_TESIS':
								$fal='Skripsi/Tesis';
								break;           
                            default:
                                $dd='Important Reason';
                                break;                              
                        }  
                    }               
                
        // ambil sisa
		$strsisa="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$karyawanid." and periodecuti=".$periode;
		$ressisa=mysql_query($strsisa);
		$sisa=0;
		while($barsisa=mysql_fetch_object($ressisa)){
			$sisa=$barsisa->sisa;
		}
        
        //ambil bagian,jabatan persetujuan atasan
                $perjabatan='';
                $perbagian='';
                $pernama='';
        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=".$persetujuan;	   
        $resf=mysql_query($strf);
        while($barf=mysql_fetch_object($resf))
        {
                $perjabatan=$barf->namajabatan;
                $perbagian=$barf->bagian;
                $pernama=$barf->namakaryawan;
        }
        //ambil bagian,jabatan persetujuan hrd
                $perjabatanhrd='';
                $perbagianhrd='';
                $pernamahrd='';
        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=".$hrd;	   
        $resf=mysql_query($strf);
        while($barf=mysql_fetch_object($resf))
        {
                $perjabatanhrd=$barf->namajabatan;
                $perbagianhrd=$barf->bagian;
                $pernamahrd=$barf->namakaryawan;
        }       
  }

        $pdf=new PDF('P','mm','A4');
        $pdf->SetFont('Arial','B',14);
        $pdf->AddPage();
        $pdf->SetY(40);
        $pdf->SetX(20);
        $pdf->SetFillColor(255,255,255); 
        $pdf->Cell(175,5,strtoupper($_SESSION['lang']['ijin']."/".$_SESSION['lang']['cuti']),0,1,'C');
        $pdf->SetX(20);
        $pdf->SetFont('Arial','',8);
        //$pdf->Cell(175,5,'NO : '.$notransaksi,0,1,'C');	

        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['tanggal'],0,0,'L');	
                $pdf->Cell(50,5," : ".$tgl,0,1,'L');	
        $pdf->SetX(20);			
        $pdf->Cell(30,5,$_SESSION['lang']['nokaryawan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$nik,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['namakaryawan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$namakaryawan,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['bagian'],0,0,'L');	
                $pdf->Cell(50,5," : ".$bagian." - ".$namabagian,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['functionname'],0,0,'L');	
                $pdf->Cell(50,5," : ".$jabatan,0,1,'L');
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['jenisijin'],0,0,'L');	
                $pdf->Cell(50,5," : ".$dd,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['keperluan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$kperluan,0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['keterangan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$ket,0,1,'L');	
         $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['pengabdian']." ".$_SESSION['lang']['tahun'],0,0,'L');	
                $pdf->Cell(50,5," : ".$periode,0,1,'L');               
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['dari'],0,0,'L');	
                $pdf->Cell(50,5," : ".tanggalnormald($jmDr),0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['tglcutisampai'],0,0,'L');	
                $pdf->Cell(50,5," : ".tanggalnormald($jmSmp),0,1,'L');	
	if($jns=='CUTI' or $jns=='MELAHIRKAN' or $jns=='PERJALANAN' or $jns=='SKRIPSI_TESIS' or $jns=='ALASANPENTING'){ 
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['sisa']." ".$_SESSION['lang']['cuti'],0,0,'L');	
                $pdf->Cell(50,5," : ".$sisa." ".$_SESSION['lang']['hari'],0,1,'L');	
        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['hari'],0,0,'L');	
                $pdf->Cell(50,5," : ".$hk." ".$_SESSION['lang']['hari'],0,1,'L');
	}

        $pdf->Ln();	
        $pdf->SetX(20);	
        $pdf->SetFont('Arial','B',8);		
        $pdf->Cell(172,5,strtoupper($_SESSION['lang']['approval_status']),0,1,'L');	
        $pdf->SetX(21);
                $pdf->Cell(30,5,strtoupper($_SESSION['lang']['bagian']),1,0,'C');
                $pdf->Cell(50,5,strtoupper($_SESSION['lang']['namakaryawan']),1,0,'C');			
                $pdf->Cell(60,5,strtoupper($_SESSION['lang']['functionname']),1,0,'C');
                $pdf->Cell(37,5,strtoupper($_SESSION['lang']['keputusan']),1,1,'C');	 			

        $pdf->SetFont('Arial','',8);

        $pdf->SetX(21);
                $pdf->Cell(30,5,$perbagian,1,0,'L');
                $pdf->Cell(50,5,$pernama,1,0,'L');			
                $pdf->Cell(60,5,$perjabatan,1,0,'L');
                $pdf->Cell(37,5,$arrKeputusan[$perstatus],1,1,'L');
        $pdf->SetX(21);
                $pdf->Cell(30,5,$perbagianhrd,1,0,'L');
                $pdf->Cell(50,5,$pernamahrd,1,0,'L');			
                $pdf->Cell(60,5,$perjabatanhrd,1,0,'L');
                $pdf->Cell(37,5,$arrKeputusan[$sthrd],1,1,'L');

    $pdf->Ln();               

        $pdf->SetX(20);                
        $pdf->Cell(30,5,$_SESSION['lang']['keputusan']." ".$_SESSION['lang']['atasan'],0,0,'L');	
                $pdf->Cell(50,5," : ".$koments,0,1,'L');	

        $pdf->SetX(20);
        $pdf->Cell(40,25,'',1,1,'C');
        $pdf->SetX(20);
        $pdf->Cell(40,5,$pernama,1,1,'C');			
		$pdf->Ln();               

        $pdf->SetX(20);	
        $pdf->Cell(30,5,$_SESSION['lang']['keputusan']." ".$_SESSION['lang']['hrd'],0,0,'L');	
                $pdf->Cell(50,5," : ".$koments2,0,1,'L');

        $pdf->SetX(20);
        $pdf->Cell(40,25,'',1,1,'C');
        $pdf->SetX(20);
        $pdf->Cell(40,5,$pernamahrd,1,1,'C');			

   $pdf->Ln();	
   $pdf->Ln();	
   $pdf->Ln();	


//footer================================
    $pdf->Ln();		
        $pdf->Output();

                break;
				
                case'getExcel':
				$tmbWhere = '';
				if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')){
					$tmbWhere = '';
				}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA'){
					$tmbWhere = " and a.karyawanid like '%".$excelkaryawanid."%'";
				}else{
					$tmbWhere = " and a.karyawanid like '%".$excelkaryawanid."%'";
				}
				$slvhc="select a.* from ".$dbname.".sdm_ijin a, ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '".$pAwal."' and '".$pAkhir."' and a.jenisijin like '%".$exelcuti."%' ".$tmbWhere." order by a.tanggal desc ";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
				
				if(mysql_num_rows($qlvhc) <= 0){
					echo $_SESSION['lang']['datanotfound'];
					exit();
				}
				
				$tab.=" 
                <table class=sortable cellspacing=1 border=1 width=80%>
                <thead>
                <tr  >
                <td align=center bgcolor='#DFDFDF'>No.</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['tanggal']."</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['nama']."</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['keperluan']."</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['jenisijin']."</td>  
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['persetujuan']."</td>    
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['approval_status']."</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
                <td align=center bgcolor='#DFDFDF'>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
                </tr>  
                </thead><tbody>";
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                    if($_SESSION['language']=='ID'){
                        $dd=$rlvhc['jenisijin'];
                    }else{
                        switch($rlvhc['jenisijin']){
                            case 'TERLAMBAT':
                                $dd='Late for work';
                                break;
                            case 'KELUAR':
                                $dd='Out of Office';
                                break;         
                            case 'PULANGAWAL':
                                $dd='Home early';
                                break;     
                            case 'IJINLAIN':
                                $dd='Other purposes';
                                break;   
                            case 'CUTI':
                                $dd='Leave';
                                break;
                            case 'MELAHIRKAN':
                                $dd='Maternity';
                                break;
							case 'PERJALANAN':
								$fal='Travel';
								break;           
							case 'SKRIPSI_TESIS':
								$fal='Skripsi/Tesis';
								break;           
                            default:
                                $dd='Important Reason';
                                break;                              
                        }      
                    }                     
					
					$no+=1;
					$tab.="
						<tr class=rowcontent>
						<td>".$no."</td>
						<td>".$rlvhc['tanggal']."</td>
						<td>".$arrNmkary[$rlvhc['karyawanid']]."</td>
						<td>".$rlvhc['keperluan']."</td>
						<td>".$dd."</td>
						<td>".$arrNmkary[$rlvhc['persetujuan1']]."</td>
						<td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>
						<td>".$rlvhc['darijam']."</td>
						<td>".$rlvhc['sampaijam']."</td>";
				}
					
				$tab.="</tbody></table>";
					
                $nop_="listizinkeluarkantor";
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
				
                case'formForward':
                 $optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                 $sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan 
                         where alokasi='1' and karyawanid not in('".$_SESSION['standard']['userid']."','".$krywnId."') order by namakaryawan asc";
                $qKary=mysql_query($sKary) or die(mysql_error($sKary));
                while($rKary=mysql_fetch_assoc($qKary))
                {
                    $optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
                }
                $tab.="<fieldset><legend>".$arrNmkary[$krywnId].", ".$_SESSION['lang']['tanggal']." : ".tanggalnormal($tglijin)."</legend><table cellpadding=1 cellspacing=1 border=0>";
                $tab.="<tr><td>".$_SESSION['lang']['namakaryawan']."</td><td><select id=karywanId>".$optKary."</select></td></tr>";
                $tab.="<tr><td colspan=2><button class=mybutton id=dtlForm onclick=AppForw()>Forward</button></td></tr></table>";
                $tab.="</table></fieldset><input type='hidden' id=karyaid value=".$krywnId." /><input type=hidden id=tglIjin value=".tanggalnormal($tglijin)."/>";
                echo $tab;
                break;
                case'forwardData':
                    $sup="update ".$dbname.".sdm_ijin set persetujuan1='".$atasan."' where $where";
                    if(mysql_query($sup))
                    {
                        $sKar="select distinct * from ".$dbname.".sdm_ijin where $where";
                        $qKar=mysql_query($sKar) or die(mysql_error($conn));
                        $rKar=mysql_fetch_assoc($qKar);
                        $strf="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$krywnId." 
                        and periodecuti=".$rKar['periodecuti'];
                        $res=mysql_query($strf);

                        $sisa='';
                        while($barf=mysql_fetch_object($res))
                        {
                        $sisa=$barf->sisa;
                        }
                        if($sisa=='')
                        $sisa=0;
                    $to=getUserEmail($atasan);
                    $namakaryawan=getNamaKaryawan($krywnId);
                    $subject="[Notifikasi]Persetujuan Ijin Keluar Kantor a/n ".$namakaryawan;
                    $body="<html>
                    <head>
                    <body>
                    <dd>Dengan Hormat,</dd><br>
                    <br>
                    Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Ijin/".$rKar['jenisijin']." (".$rKar['keperluan'].")
                    kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                    <br>
                    <br>
                    Note: Sisa cuti ybs periode ".$rKar['periodecuti'].":".$sisa." Hari
                    <br>
                    <br>
                    Regards,<br>
                    Owl-Plantation System.
                    </body>
                    </head>
                    </html>
                    ";
                    $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                    }
                    else
                    {
                        echo "DB Error : ".mysql_error($conn);
                    }
                break;

                default:
                break;
        }


?>