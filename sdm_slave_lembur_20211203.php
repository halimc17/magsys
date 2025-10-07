<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$proses=checkPostGet('proses','');
$txtFind=checkPostGet('txtfind','');
$absnId=explode("###",checkPostGet('absnId',''));
$tgl=count($absnId)>1? tanggalsystem($absnId[1]): '';
$kdOrg=$absnId[0];
$krywnId=checkPostGet('krywnId','');
$tpLmbr=checkPostGet('tpLmbr','');
$ungTrans=checkPostGet('ungTrans','');
$ungMkn=checkPostGet('ungMkn','');
$Jam=checkPostGet('Jam','');
$ungLbhjm=checkPostGet('ungLbhjm','');
$optKry='';
$optTipelembur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrsstk=array("0"=>$_SESSION['lang']['haribiasa'],"1"=>$_SESSION['lang']['hariminggu'],"2"=>$_SESSION['lang']['harilibur'],"3"=>$_SESSION['lang']['hariraya']);
$kodeOrg=checkPostGet('kodeOrg','');
$basisJam=checkPostGet('basisJam','');
$thnPeriod="";
//$arrsstk=getEnum($dbname,'sdm_5lembur','tipelembur');
foreach($arrsstk as $kei=>$fal)
{
        //print_r($kei);exit();
        $optTipelembur.="<option value='".$kei."'>".ucfirst($fal)."</option>";
} 

$tpLembur=checkPostGet('tpLembur','');
$basisJam=checkPostGet('basisJam','');
        switch($proses)
        {
                case'cekData':
                //exit("Error:MASUK");
                //echo"warning:masuk";
                $_SESSION['temp']['OrgKd2']=$kdOrg;
                $sCek="select kodeorg,tanggal from ".$dbname.".sdm_lemburht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'"; //echo "warning".$sCek;nospb
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_row($qCek);
                if($rCek<1)
                {
                        $sIns="insert into ".$dbname.".sdm_lemburht (`kodeorg`,`tanggal`) values ('".$kdOrg."','".$tgl."')"; //echo"warning:".$sIns;
                        if(mysql_query($sIns))
                        {
                                if(($tpLmbr!='')&&($Jam!=''))
                                {
                                        $sDetIns="insert into ".$dbname.".sdm_lemburdt 
                                        (`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`) values ('".$kdOrg."','".$tgl."','".$krywnId."','".$tpLmbr."','".$Jam."','".$ungMkn."','".$ungTrans."','".$ungLbhjm."')";
                                        //echo"warning:".$sDetIns;exit();

                                        if(mysql_query($sDetIns))
                                        echo"";
                                        else
                                        echo "DB Error : ".mysql_error($conn);
                                }
                                else
                                {
                                       if($_SESSION['language']=='ID'){ 
                                       echo"warning: Masukkan tipe lembur dan basis jam";
                                       }else{
                                        echo"warning: Please choose overtime type and actual hours";
                                       }
                                        exit();
                                }
                        }
                        else
                        {
                                echo "DB Error : ".mysql_error($conn);
                        }
                }
                else
                {
                        if(($tpLmbr!='')&&($Jam!=''))
                        {

                                $sDetIns="insert into ".$dbname.".sdm_lemburdt 
                                (`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`) values ('".$kdOrg."','".$tgl."','".$krywnId."','".$tpLmbr."','".$Jam."','".$ungMkn."','".$ungTrans."','".$ungLbhjm."')";
                        //echo"warning:".$sDetIns;exit();

                        if(mysql_query($sDetIns))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                        }
                        else
                        {
                                       if($_SESSION['language']=='ID'){ 
                                       echo"warning: Masukkan tipe lembur dan basis jam";
                                       }else{
                                        echo"warning: Please choose overtime type and actual hours";
                                       }
                                exit();
                        }
                }
                
                //exit("Error:$tgl");
                $per=substr($tgl,0,4).'-'.substr($tgl,4,2);
              
                
                $iLembur="select sum(uangkelebihanjam) as lembur from ".$dbname.".sdm_lemburdt where karyawanid='".$krywnId."'"
                        . " and tanggal like '%".$per."%' ";
                //exit("Error:$iLembur"); 
                $nLembur=  mysql_query($iLembur) or die (mysql_error($conn));
                $dLembur=  mysql_fetch_assoc($nLembur);
                    $lembur=$dLembur['lembur'];
                
                $iGaji="select jumlah from ".$dbname.".sdm_5gajipokok where tahun='".substr($tgl,0,4)."' and karyawanid='".$krywnId."' "
                        . " and idkomponen=1";
                $nGaji=  mysql_query($iGaji) or die (mysql_error($conn));
                $dGaji=  mysql_fetch_assoc($nGaji);
                    $gaji=$dGaji['jumlah']*(35/100);
                 
                    
                $whKar="karyawanid='".$krywnId."'";    
                $nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whKar);    
                    
                if($lembur>$gaji)
                {
                    echo "Lembur untuk karyawan $nmKar[$krywnId] di periode $per telah melebihi 35%  ";
                }
                    
                   
                break;
                case'loadNewData':
                echo"<table cellspacing='1' border='0' class='sortable'>
<thead>
<tr class=rowheader>
<td>No.</td>
<td>". $_SESSION['lang']['kodeorg'] ."</td>
<td>". $_SESSION['lang']['namaorganisasi'] ."</td>
<td>". $_SESSION['lang']['tanggal'] ."</td>
<td>Action</td>
</tr>
</thead><tbody>";
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_lemburht where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by `tanggal` desc";// echo $ql2;

                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }


                $slvhc="select * from ".$dbname.".sdm_lemburht where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by `tanggal` desc limit ".$offset.",".$limit."";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                        $thnPeriod=substr($rlvhc['tanggal'],0,7);

                        $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
                        $qOrg=mysql_query($sOrg) or die(mysql_error());
                        $rOrg=mysql_fetch_assoc($qOrg);
                        $sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$thnPeriod."' and tanggalmulai<='".$rlvhc['tanggal']."' and tanggalsampai>='".$rlvhc['tanggal']."'";
                        $qGp=mysql_query($sGp) or die(mysql_error());
                        $rGp=mysql_fetch_assoc($qGp);



                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rlvhc['kodeorg']."</td>
                <td>".$rOrg['namaorganisasi']."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>";
                if($rGp['sudahproses']==0)
                {
                echo"
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_lemburht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_slave_lemburPdf',event)\">";
                }
                else
                {
                        echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_lemburht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_slave_lemburPdf',event)\">";

                }
                echo"</td>
                </tr>
                ";
                }
                echo"
                <tr class=rowheader><td colspan=5 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                echo"</tbody></table>";
                break;
                case'delData':
                $sCek="select posting from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'"; //echo "warning".$sCek;;
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_assoc($qCek);
                if($rCek['posting']=='1')
                {
                        echo"warning: This data has been confirmed, can not continue";
                        exit();
                }
                $sDel="delete from ".$dbname.".sdm_lemburht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";// echo "___".$sDel;exit();
                if(mysql_query($sDel))
                {
                        $sDelDetail="delete from ".$dbname.".sdm_lemburdt where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";
                        if(mysql_query($sDelDetail))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                }
                else
                {echo "DB Error : ".mysql_error($conn);}

                break;
                case'cekHeader':
                $thn=substr($tgl,0,4);
                $bln=substr($tgl,4,2);
                $periode=$thn."-".$bln;
			#mencegah input data dengan tanggal lebih kecil dari periode penggajian unit
	        $sPeriode="select DISTINCT periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."' and sudahproses=0 and tanggalmulai<='".$tgl."' and tanggalsampai>='".$tgl."'";
	        $qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
			$rPeriode=mysql_fetch_assoc($qPeriode);
			$nPeriode=mysql_num_rows($qPeriode);
			if($nPeriode<1){
				echo"Warning: Transaction date out of range";
				exit();
			}
			#===========================================================================

                $sCek="select kodeorg,tanggal from ".$dbname.".sdm_lemburht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'"; //echo "warning".$sCek;nospb
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_row($qCek);
                if($rCek>0)
                {
                        echo"warning: Data already exist";
                        exit();
                }

                $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and
                kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=1";
                //exit("Error".$str) ;
                $res=mysql_query($str);
                if(mysql_num_rows($res)>0)
                $aktif=true;
                else
                $aktif=false;
                if($aktif==true)
                {
                exit("Error: Accounting period has been closed to this date");
                }
                break;
                case'cariAbsn':
                echo"
                <div style='overflow:auto;height:400px'>
                <table cellspacing='1' border='0' class='sortable'>
<thead>
<tr class=rowheader>
<td>No.</td>
<td>". $_SESSION['lang']['kodeorg'] ."</td>
<td>". $_SESSION['lang']['namaorganisasi'] ."</td>
<td>". $_SESSION['lang']['tanggal'] ."</td>
<td>Action</td>
</tr>
</thead><tbody>";
$limit=20;
$page=0;
if(isset($_POST['page']))
{
$page=$_POST['page'];
if($page<0)
$page=0;
}
$offset=$page*$limit;
                if(($tgl!='')&&($kdOrg!=''))
                {
                        $where=" kodeorg = '".$kdOrg."' and tanggal='".$tgl."'";
                }
                elseif($kdOrg!='')
                {
                        $where=" kodeorg ='".$kdOrg."'";
                }
                elseif($tgl!='')
                {
                        $where="kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' and tanggal='".$tgl."'";	
                }
                elseif(($tgl=='')&&($kdOrg==''))
                {
                        echo"warning: Please insert data";
                        exit();
                }
                //paging data
                $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_lemburht where ".$where." order by `tanggal`";// echo $ql2;

                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }

                //query data
                $slvhc="select * from ".$dbname.".sdm_lemburht where ".$where." order by `tanggal` limit ".$offset.",".$limit."";// echo "warning:".$slvhc;exit();
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                        $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
                        $qOrg=mysql_query($sOrg) or die(mysql_error());
                        $rOrg=mysql_fetch_assoc($qOrg);
                        $sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$thnPeriod."' and tanggalmulai<='".$rlvhc['tanggal']."' and tanggalsampai>='".$rlvhc['tanggal']."'";
                        $qGp=mysql_query($sGp) or die(mysql_error());
                        $rGp=mysql_fetch_assoc($qGp);
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rlvhc['kodeorg']."</td>
                <td>".$rOrg['namaorganisasi']."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>";
                if($rGp['sudahproses']==0)
                {
                echo"
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_lemburht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_slave_lemburPdf',event)\">";
                }
                else
                {
                        echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_lemburht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_slave_lemburPdf',event)\">";

                }
                echo"</td>
                </tr>
                ";
                }
                echo"
                <tr class=rowheader><td colspan=5 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                echo"</tbody></table></div>";

                break;
                case'updateDetail':
                if(($tpLmbr!='')&&($Jam!=''))
                {
                $sUp="update ".$dbname.".sdm_lemburdt set tipelembur='".$tpLmbr."',jamaktual='".$Jam."',uangmakan='".$ungMkn."',uangtransport='".$ungTrans."',uangkelebihanjam='".$ungLbhjm."' where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                if(mysql_query($sUp))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                }
                else
                        {
                                       if($_SESSION['language']=='ID'){ 
                                       echo"warning: Masukkan tipe lembur dan basis jam";
                                       }else{
                                        echo"warning: Please choose overtime type and actual hours";
                                       }
                                exit();
                        }
                break;
                case'delDetail':
                        $sDel="delete from ".$dbname.".sdm_lemburdt where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                if(mysql_query($sDel))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                break;
                case'createTable':
                if(strlen($kdOrg)>4)
                {
                        $where=" subbagian='".$kdOrg."'  and (tanggalkeluar>".$tgl." or tanggalkeluar='0000-00-00')";
                }
                else
                {
                        $where=" lokasitugas='".$kdOrg."' and (subbagian IS NULL or subbagian='0' or subbagian='') and (tanggalkeluar>".$tgl." or tanggalkeluar='0000-00-00')";
                }//namakaryawan,karyawanid,nik
                
                $optTipeKar=  makeOption($dbname, 'sdm_5tipekaryawan','id,tipe');
                $sKry="select * from ".$dbname.".datakaryawan where ".$where." and tipekaryawan not in ('0','7','8') order by namakaryawan asc";
                $qKry=mysql_query($sKry) or die(mysql_error($conn));
                while($rKry=mysql_fetch_assoc($qKry))
                {
                        $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']." [ ".$rKry['nik']." ] ".$optTipeKar[$rKry['tipekaryawan']]."</option>";
                }

                $table="<table id='ppDetailTable' cellspacing='1' border='0' class='sortable'>
                <thead>
                <tr class=rowheader>
                <td>".$_SESSION['lang']['namakaryawan']."</td>
                <td>".$_SESSION['lang']['tipelembur']."</td>
                <td>".$_SESSION['lang']['jamaktual']."</td>
                <td>".$_SESSION['lang']['uangkelebihanjam']."</td>
                <td>".$_SESSION['lang']['penggantiantransport']."</td>
                <td>".$_SESSION['lang']['uangmakan']."</td>
                <td>Action</td>
                </tr></thead>
                <tbody id='detailBody'>";

                $table.="<tr class=rowcontent><td><select id=krywnId name=krywnId style='width:200px' onchange='getUangLem()'>".$optKry."</select></td>
                <td><select id=tpLmbr name=tpLmbr style='width:100px' onchange='getLembur(0,0)'>".$optTipelembur."</select></td>
                <td><select id=jam name=jam style='width:100px' onchange='getUangLem()'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                <td><input type='text' class='myinputtextnumber' id='uang_lbhjm' name='uang_lbhjm' style='width:100px' onkeypress='return angka_doang(event)' value=0 disabled /></td>
                <td><input type='text' class='myinputtextnumber' id='uang_trnsprt' name='uang_trnsprt' style='width:100px' onkeypress='return angka_doang(event)' value=0  /></td>
                <td><input type='text' class='myinputtextnumber' id='uang_mkn' name='uang_mkn' style='width:100px' onkeypress='return angka_doang(event)' value=0 /></td>
                <td><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/></td>
                </tr>
                ";
                $table.="</tbody></table>";
                echo $table;
                break;
                case'getBasis':
                $dtOrg=$_SESSION['empl']['lokasitugas'];
                $optBasis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sBasis="select jamaktual from ".$dbname.".sdm_5lembur where kodeorg='".$dtOrg."' and tipelembur='".$tpLembur."'";
                $qBasis=mysql_query($sBasis) or die(mysql_error($conn));
                while($rBasis=mysql_fetch_assoc($qBasis))
                {
                        $optBasis.="<option value=".$rBasis['jamaktual']." ".($rBasis['jamaktual']==$basisJam?'selected':'').">".$rBasis['jamaktual']."</option>";
                }
                echo $optBasis;
                break;
                
                
                
                case'getUang':
                    
                    
                  
                $uangLembur='';
                $kodeOrg=substr($kodeOrg,0,4);
                $sPengali="select jamlembur from ".$dbname.".sdm_5lembur  where kodeorg='".$kodeOrg."' and tipelembur='".$tpLmbr."' and jamaktual='".$basisJam."' ";
                $qPengali=mysql_query($sPengali) or die(mysql_error());
                $rPengali=mysql_fetch_assoc($qPengali);

                $sGt="select sum(jumlah) as gapTun from ".$dbname.".sdm_5gajipokok where karyawanid='".$krywnId."' and idkomponen=1 and tahun=".$_POST['tahun'];
                $qGt=mysql_query($sGt) or die(mysql_error($conn));
                $rGt=mysql_fetch_assoc($qGt);
                
                $whTpKary="karyawanid='".$krywnId."'";
                $tipeKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan',$whTpKary);
                $pteKar=makeOption($dbname, 'datakaryawan', 'karyawanid,kodeorganisasi',$whTpKary);
                
                $tpKar=$tipeKar[$krywnId];
                $ptKar=$pteKar[$krywnId];
                
                #rubah format lembur untuk CKS dan karyawan PHL
              
                
                $uangLembur=($rGt['gapTun']*$rPengali['jamlembur'])/173;
                
                echo intval($uangLembur);
                break;
                default:
                break;
        }
?>