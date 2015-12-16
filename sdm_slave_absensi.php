<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$txtFind=checkPostGet('txtfind','');
$absnId=explode("###",checkPostGet('absnId',''));
$tgl=isset($absnId[1])? tanggalsystem($absnId[1]): '';
$kdOrg=$absnId[0];
$krywnId=checkPostGet('krywnId','');
$shifTid=checkPostGet('shifTid','');
$asbensiId=checkPostGet('asbensiId','');
$Jam=checkPostGet('Jam','');
$Jam2=checkPostGet('Jam2','');
$ket=checkPostGet('ket','');
$periode=checkPostGet('period','');
$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
$catu=checkPostGet('catu','');
$penaltykehadiran=checkPostGet('dendakehadiran','');
$periodeAkutansi=$_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan'];
$kdJbtn=makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$tipeKary=makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan');


##buat cek proses gaji
$iPer="select * from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and "
        . " sudahproses=0 order by periode asc limit 1";
$nPer=  mysql_query($iPer) or die (mysql_error($conn));
$dPer= mysql_fetch_assoc($nPer);

$perGjSkrg=$dPer['periode'];







        switch($proses){
                case'cariOrg':
                //echo"warning:masuk";
                $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where namaorganisasi like '%".$txtFind."%' or kodeorganisasi like '%".$txtFind."%' "; //echo "warning:".$str;exit();
                if($res=mysql_query($str))
                {
                        echo"
          <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
                                 <thead>
                                 <tr class=rowheader>
                                 <td class=firsttd>
                                 No.
                                 </td>
                                 <td>".$_SESSION['lang']['kodeorg']."</td>
                                 <td>".$_SESSION['lang']['namaorganisasi']."</td>
                                 </tr>
                                 </thead>
                                 <tbody>";
                        $no=0;	 
                        while($bar=mysql_fetch_object($res))
                        {
                                $no+=1;
                                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg('".$bar->kodeorganisasi."','".$bar->namaorganisasi."')\" title='Click' >
                                          <td class=firsttd>".$no."</td>
                                          <td>".$bar->kodeorganisasi."</td>
                                          <td>".$bar->namaorganisasi."</td>
                                         </tr>";
                        }	 
                        echo "</tbody>
                                  <tfoot>
                                  </tfoot>
                                  </table></div></fieldset>";
                  }	
                  else
                        {
                                echo " Gagal,".addslashes(mysql_error($conn));
                        }	
                break;
                case'cariOrg2':
                //echo"warning:masuk";
                $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where namaorganisasi like '%".$txtFind."%' or kodeorganisasi like '%".$txtFind."%' "; //echo "warning:".$str;exit();
                if($res=mysql_query($str))
                {
                        echo"
          <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
                                 <thead>
                                 <tr class=rowheader>
                                 <td class=firsttd>
                                 No.
                                 </td>
                                 <td>".$_SESSION['lang']['kodeorg']."</td>
                                 <td>".$_SESSION['lang']['namaorganisasi']."</td>
                                 </tr>
                                 </thead>
                                 <tbody>";
                        $no=0;	 
                        while($bar=mysql_fetch_object($res))
                        {
                                $no+=1;
                                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg2('".$bar->kodeorganisasi."','".$bar->namaorganisasi."')\" title='Click' >
                                          <td class=firsttd>".$no."</td>
                                          <td>".$bar->kodeorganisasi."</td>
                                          <td>".$bar->namaorganisasi."</td>
                                         </tr>";
                        }	 
                        echo "</tbody>
                                  <tfoot>
                                  </tfoot>
                                  </table></div></fieldset>";
                  }	
                  else
                        {
                                echo " Gagal,".addslashes(mysql_error($conn));
                        }	
                break;
                case'cekData':
                if($kdOrg==''){
                    exit("error: Unit code must filled");
                }
                //exit("error:".$jumlah_jam);
                //echo"warning:masuk";
                //SELECT * FROM `sdm_5periodegaji` WHERE `kodeorg`='SOGE' and `sudahproses`=0 and `tanggalmulai`<'20110112' and `tanggalsampai`>'20110112'
                $sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and sudahproses=0 and tanggalmulai<='".$tgl."' and tanggalsampai>='".$tgl."'";
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_num_rows($qCek);
                if($rCek>0)
                {

                $sCek="select kodeorg,tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'"; //echo "warning".$sCek;nospb
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_row($qCek);
                  #cek premi
                                #jika status di datakaryawawan apakah menerima premi dan insentif 
                                #dan apakah sudah melebhi dari setup premi, jika lebih atau sama dengan maka premi=0
//                                $where="karyawanid='".$krywnId."'";
//                                $statPremi=makeOption($dbname, 'datakaryawan', 'karyawanid,statpremi',$where);
//                                if($statPremi[$krywnId]!=0){
//                                    $scek="select distinct sum(premi) as premi from ".$dbname.".sdm_absensidt 
//                                    where karyawanid='".$krywnId."' and tanggal like '".$periode."%'";
//                                    $qcek=mysql_query($scek) or die(mysql_error($conn));
//                                    $rcek=mysql_fetch_assoc($qcek);
//                                    $spremi="select distinct premitetap from ".$dbname.".sdm_5premitetap 
//                                                 where kodeorg='".substr($kdOrg,0,4)."' 
//                                                 and kodejabatan='".$kdJbtn[$krywnId]."'";
//
//                                        $qpremi=mysql_query($spremi) or die(mysql_error($conn));
//                                        $rpremi=mysql_fetch_assoc($qpremi);
//
//                                    if($rcek['premi']>=$rpremi['premitetap']){
//                                                    $premi=0;
//                                    }
//                                }else{
//                                                $premi=0;
//                                }   
                
                if($rCek<1)
                {
                        $sIns="insert into ".$dbname.".sdm_absensiht (`kodeorg`,`tanggal`,`periode`) values ('".$kdOrg."','".$tgl."','".$periode."')"; //echo"warning:".$sIns;
                        if(mysql_query($sIns))
                        {
                                if($_POST['premi']==''){
                                    $_POST['premi']=0;
                                }
                                if($_POST['insentif']==''){
                                    $_POST['insentif']=0;
                                }
                                if($_POST['premidt']==''){
                                    $_POST['premi']=0;
                                }
                                /*if(($asbensiId=='H')||($asbensiId=='AS')){
                                    if($_POST['premidt']!=$_POST['premi']){
                                        $_POST['premi']=$_POST['premidt'];
                                }
                                }else if(($asbensiId=='HL')||($asbensiId=='L')||($asbensiId=='MG')){
                                    if($_POST['premidt']!=$_POST['insentif']){
                                        $_POST['insentif']=$_POST['premidt'];
                                    }
                                }*/
                                $sdtCek="select distinct * from ".$dbname.".kebun_kehadiran_vw 
                                         where tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                                $qDtCek=mysql_query($sdtCek) or die(mysql_error($conn));
                                $rSource=mysql_fetch_assoc($qDtCek);
                                $rDtCek=mysql_num_rows($qDtCek);
                                if($rDtCek>0){
                                    exit("error: Employee registered on transaction : ".$rSource['notransaksi']);
                                }
                                
                                $sDetIns="insert into ".$dbname.".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`) 
                                          values ('".$kdOrg."','".$tgl."','".$krywnId."','".$shifTid."','".$asbensiId."','".$Jam."','".$Jam2."','".$ket."',".$catu.",".$penaltykehadiran.",".$_POST['premi'].",".$_POST['insentif'].")";
                               
                                if(mysql_query($sDetIns))
                                {
                                        echo"";
                                }
                                else
                                {echo "DB Error : ".mysql_error($conn);}
                        }
                        else
                        {
                                echo "DB Error : ".mysql_error($conn);
                        }
                }
                else
                {
                              
                                
                                if($_POST['premi']==''){
                                    $_POST['premi']=0;
                                }
                                if($_POST['insentif']==''){
                                    $_POST['insentif']=0;
                                }
                                if($_POST['premidt']==''){
                                    $_POST['premidt']=0;
                                }
                                
                                $sdtCek="select distinct * from ".$dbname.".kebun_kehadiran_vw 
                                         where tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                                $qDtCek=mysql_query($sdtCek) or die(mysql_error($conn));
                                $rSource=mysql_fetch_assoc($qDtCek);
                                $rDtCek=mysql_num_rows($qDtCek);
                                if($rDtCek>0){
                                    exit("error: Employee registered on transaction : ".$rSource['notransaksi']);
                                }
                               
                                
                        //$sDetIns="insert into ".$dbname.".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `penjelasan`,`catu`,`penaltykehadiran`) values ('".$kdOrg."','".$tgl."','".$krywnId."','".$shifTid."','".$asbensiId."','".$Jam."','".$Jam2."','".$ket."',".$catu.",".$penaltykehadiran.")";
                        $sDetIns="insert into ".$dbname.".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`) 
                                          values ('".$kdOrg."','".$tgl."','".$krywnId."','".$shifTid."','".$asbensiId."','".$Jam."','".$Jam2."','".$ket."',".$catu.",".$penaltykehadiran.",".$_POST['premidt'].",".$_POST['insentif'].")";
                                //exit("error:".$sDetIns);
                                //echo "warning:test".$dins;
                                if(mysql_query($sDetIns))
                                {
                                        echo"";
                                }
                                else
                                {
                                //echo "warning:masuk";
                                echo "DB Error : ".mysql_error($conn);
                                }
                }
//                exit(" Error:".$sDetIns);
                }
                else
                {
                        echo"warning:Date out of payment period";
                        exit();
                }
                break;
                case'loadNewData':
                   
                echo"
                <table cellspacing=1 border=0 class=sortable>
                <thead>
                <tr class=rowheader>
                <td>No.</td>
                <td>".$_SESSION['lang']['kodeorg']."</td>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>Action</td>
                </tr>
                </thead>
                <tbody>
                ";
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_absensiht where substring(kodeorg,1,4)='".$idOrg."' order by `tanggal` desc";// echo $ql2;
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }


                $slvhc="select * from ".$dbname.".sdm_absensiht where substring(kodeorg,1,4)='".$idOrg."' order by `tanggal` desc limit ".$offset.",".$limit."";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                        $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
                        $qOrg=mysql_query($sOrg) or die(mysql_error());
                        $rOrg=mysql_fetch_assoc($qOrg);
                $sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$rlvhc['kodeorg']."' and `periode`='".$rlvhc['periode']."'";
                $qGp=mysql_query($sGp) or die(mysql_error());
                $rGp=mysql_fetch_assoc($qGp);
                
               

                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rlvhc['kodeorg']."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".substr(tanggalnormal($rlvhc['periode']),1,7)."</td>
                <td>";
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
                   echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                    <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";     
                }
                
                //$perGjSkrg
                
                else if($rlvhc['periode']==$perGjSkrg){
                echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
                }
                else
                {
                        echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
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
                        echo"warning:Already Post This Data";
                        exit();
                }
                $scek="select distinct * from ".$dbname."";
                $sDel="delete from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";// echo "___".$sDel;exit();
                if(mysql_query($sDel))
                {
                        $sDelDetail="delete from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";
                        if(mysql_query($sDelDetail))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                }
                else
                {echo "DB Error : ".mysql_error($conn);}

                break;
                case'cekHeader':
                //echo"warning:masuk";
                    $abs=explode("###",$_POST['absnId']);
                    if($abs[0]==''){
                        exit("error: Unit code must filled");
                    }
                 $sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."' and sudahproses=0 and tanggalmulai<='".$tgl."' and tanggalsampai>='".$tgl."'";
                //    $sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."' and sudahproses=0";
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_num_rows($qCek);
                //$rCek=mysql_fetch_assoc($qCek);
                if($rCek<1)
               // if($rCek['tanggalmulai']<=$tgl || $rCek['tanggalsampai']>=$tgl)
                {
                        echo"warning:Date out of range";
                        exit();
                }
                //echo"warning:masuk".$aktif;exit();
                $sCek="select kodeorg,tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'"; //echo "warning".$sCek;nospb
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_row($qCek);
                if($rCek>0)
                {
                        echo"warning:This date and Organization Name already exist";
                        exit();
                }


                $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and
                kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=1";
               // exit("Error".$str) ;
                $res=mysql_query($str);
                if(mysql_num_rows($res)>0)
                $aktif=true;
                else
                $aktif=false;
                if($aktif==true)
                {
                exit("Error:Accounting period has been closed");
                }
                break;
                case'cariAbsn':
                echo"
                <div style=overflow:auto; height:350px;>
                <table cellspacing=1 border=0>
                <thead>
                <tr class=rowheader>
                <td>No.</td>
                <td>".$_SESSION['lang']['kodeorg']."</td>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>Action</td>
                </tr>
                </thead>
                <tbody>
                ";
                $where="";
                    if($kdOrg!='')
                    {
                        $where.=" and kodeorg='".$kdOrg."'";
                    }
                    if($tgl!='')
                    {
                        $bln=explode("-",$absnId[1]);

                        $where.=" and tanggal='".$bln[2]."-".$bln[1]."-".$bln[0]."'";
                    }

                $sCek="select * from ".$dbname.".sdm_absensiht where substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' ".$where."";//echo "warning".$sCek;exit();
                //echo $sCek;
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_num_rows($qCek);
                if($rCek>0)
                {


                        $slvhc="select * from ".$dbname.".sdm_absensiht where substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' ".$where."  order by `tanggal` desc ";
                        $qlvhc=mysql_query($slvhc) or die(mysql_error());
                        $user_online=$_SESSION['standard']['userid'];
                        while($rlvhc=mysql_fetch_assoc($qlvhc))
                        {
                                $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
                                $qOrg=mysql_query($sOrg) or die(mysql_error());
                                $rOrg=mysql_fetch_assoc($qOrg);
                                $sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$rlvhc['kodeorg']."' and `periode`='".$rlvhc['periode']."'";
                $qGp=mysql_query($sGp) or die(mysql_error());
                $rGp=mysql_fetch_assoc($qGp);
                        $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rlvhc['kodeorg']."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".substr(tanggalnormal($rlvhc['periode']),1,7)."</td>
                <td>";
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
                   echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                    <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";     
                }
                else if($rlvhc['periode']==$perGjSkrg){
                echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >	
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
                }
                else{
                    echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
                }
                echo"</td>
                </tr>
                ";
                        }

                        echo"</tbody></table></div>";
                }
                else
                {
                        echo"<tr class=rowcontent><td colspan=5 align=center>Not Found</td></tr></tbody></table></div>";
                }
                break;
                case'updateData':
                     if($_POST['premi']==''){
                                    $_POST['premi']=0;
                                }
                                if($_POST['insentif']==''){
                                    $_POST['insentif']=0;
                                }
                            if(($asbensiId=='H')||($asbensiId=='AS')){
                                    if($_POST['premidt']!=$_POST['premi']){
                                        $_POST['premi']=$_POST['premidt'];
                                    }
                                }else if(($asbensiId=='HL')||($asbensiId=='L')||($asbensiId=='MG')){
                                    if($_POST['premidt']!=$_POST['insentif']){
                                        $_POST['insentif']=$_POST['premidt'];
                                    }
                                }
                    if($kdOrg==''){
                        exit("error:Unit code must filled");
                    }
//                    #cek premi
//                    #jika status di datakaryawawan apakah menerima premi dan insentif 
//                    #dan apakah sudah melebhi dari setup premi, jika lebih atau sama dengan maka premi=0
//                    $where="karyawanid='".$krywnId."'";
//                    $statPremi=makeOption($dbname, 'datakaryawan', 'karyawanid,statpremi',$where);
//                    if($statPremi[$krywnId]!=0){
//                        $scek="select distinct sum(premi) as premi from ".$dbname.".sdm_absensidt 
//                        where karyawanid='".$krywnId."' and tanggal like '".$periode."%'";
//                        $qcek=mysql_query($scek) or die(mysql_error($conn));
//                        $rcek=mysql_fetch_assoc($qcek);
//                        $spremi="select distinct premitetap from ".$dbname.".sdm_5premitetap 
//                                     where kodeorg='".substr($kdOrg,0,4)."' 
//                                     and kodejabatan='".$kdJbtn[$krywnId]."'";
//
//                            $qpremi=mysql_query($spremi) or die(mysql_error($conn));
//                            $rpremi=mysql_fetch_assoc($qpremi);
//
//                        if($rcek['premi']>=$rpremi['premitetap']){
//                                        $premi=0;
//                        }
//                    }else{
//                                    $premi=0;
//                    }       
            $sdtCek="select distinct * from ".$dbname.".kebun_kehadiran_vw 
                                 where tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                        $qDtCek=mysql_query($sdtCek) or die(mysql_error($conn));
                        $rSource=mysql_fetch_assoc($qDtCek);
                        $rDtCek=mysql_num_rows($qDtCek);
                        if($rDtCek>0){
                            exit("error: Employee registered on transaction : ".$rSource['notransaksi']);
                        }
                $sUpd="update ".$dbname.".sdm_absensidt set shift='".$shifTid."',absensi='".$asbensiId."',jam='".$Jam."',jamPlg='".$Jam2."',penjelasan='".$ket."',
                       catu=".$catu.",penaltykehadiran=".$penaltykehadiran." ,`premi` ='".$_POST['premi']."',`insentif`='".$_POST['insentif']."'
                       where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
                //exit("error:".$sUpd);
                        if(mysql_query($sUpd))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                break;
                case'delDetail':
                    $dh="idkaryawan='".$krywnId."' and tanggal='".$tgl."'";
                    $optKar=makeOption($dbname,'vhc_runhk','idkaryawan,upah',$dh);
                    $optnotran=makeOption($dbname,'vhc_runhk','idkaryawan,notransaksi',$dh);
                    if($optKar[$krywnId]!=''){
                        exit("error: Tidak dapat menghapus data, karena ada absensi dari traksi ".$optnotran[$krywnId]." ");
                    }
                        $sDelDetail="delete from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' and kodeorg='".$kdOrg."' and karyawanid='".$krywnId."'";
                        if(mysql_query($sDelDetail))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);
                break;
                case'getPremi':
                    $insentif=0;
                    $premi=0;
                    $premitetap=0;
                    $where="karyawanid='".$_POST['karyId']."'";
                    $statPremi=makeOption($dbname, 'datakaryawan', 'karyawanid,statpremi',$where);
                    if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
                        exit();
                    }
                if($statPremi[$_POST['karyId']]==0){
                     //exit("error:".$_POST['absnId']);
                            $tgl=explode("-",$_POST['tglDt']);
                            $periode=$tgl[2]."-".$tgl[1];
                            $isi=$tgl[2]."-".$tgl[1]."-".$tgl[0];
                          

                            if($_POST['jamPlg']=='00:00'){
                                $_POST['jmMulai']="00:00";
                            }
                            $jm1=explode(":",$_POST['jmMulai']);
                            $jm2=explode(":",$_POST['jamPlg']);

                            $dtTmbh=0;
                            if($jm2<$jm1){
                                $dtTmbh=1;
                            }
                            $qwe=date('D', strtotime($isi));
                            //exit("error: ".$qwe);
                            $wktmsk=mktime(intval($jm1[0]),intval($jm1[1]),0,intval(substr($_POST['tglDt'],3,2)),intval(substr($_POST['tglDt'],0,2)),substr($_POST['tglDt'],6,4));
                            $wktplg=mktime(intval($jm2[0]),intval($jm2[1]),0,intval(substr($_POST['tglDt'],3,2)),intval(substr($_POST['tglDt'],0,2)+$dtTmbh),substr($_POST['tglDt'],6,4));
                            $slsihwaktu=$wktplg-$wktmsk;
                            $sisa = $slsihwaktu % 86400;
                            $jumlah_jam = floor($sisa/3600);  
                            if(($_POST['absnId']=='H')||($_POST['absnId']=='AS')){
                                   
                                    $spremi="select distinct premitetap from ".$dbname.".sdm_5premitetap 
                                             where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                                             and kodejabatan='".$kdJbtn[$_POST['karyId']]."'";

                                    $qpremi=mysql_query($spremi) or die(mysql_error($conn));
                                    $rpremi=mysql_fetch_assoc($qpremi);
                               
                                    //exit("error:".$jumlah_jam);
                                    $scek="select distinct sum(premi) as premi from ".$dbname.".sdm_absensidt 
                                           where karyawanid='".$_POST['karyId']."' and tanggal like '".$periode."%'";
                                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                                    $rcek=mysql_fetch_assoc($qcek);
                                    if($qwe=='Sat'){
                                         $premi=0;
                                          if($jumlah_jam>=5){
//                                            if($rcek['premi']>=$rpremi['premitetap']){
//                                                exit("error: Premi sudah melebihi maksimal premi bulanan");
//                                                //$premi=0;
//                                            }else{
                                                @$premi=$rpremi['premitetap']/25;
                                           // }
                                         }else{
                                             $premi=0;
                                         }
                                    }else{
                                        if($jumlah_jam>=7){
//                                            if($rcek['premi']>=$rpremi['premitetap']){
//                                                exit("error: Premi sudah melebihi maksimal premi bulanan");
//                                                //$premi=0;
//                                            }else{
                                                @$premi=$rpremi['premitetap']/25;
                                            //}
                                        }else{
                                            $premi=0;
                                        }
                                    }
                            }
                            if(($_POST['absnId']=='HL')||($_POST['absnId']=='L')||($_POST['absnId']=='MG')){
                                        
                                       $premi=0;
                                       $spremi="select distinct insentif from ".$dbname.".sdm_5insentif 
                                             where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                                             and tipekaryawan='".$tipeKary[$_POST['karyId']]."'";
                                        $qpremi=mysql_query($spremi) or die(mysql_error($conn));
                                        $rpremi=mysql_fetch_assoc($qpremi);
                                        if($jumlah_jam>=3){
                                            $insentif=$rpremi['insentif'];
                                        }elseif(($jumlah_jam<3)||($jumlah_jam>1)){
                                            @$insentif=$rpremi['insentif']/2;
                                        }
                                        //exit("error:masuk".$insentif);
                             }
                             $premitetap=$premi+$insentif;
                            echo $premitetap."####".$insentif."####".$premi;
                }
//                            //tentukan waktu tujuan
//$waktu_tujuan = mktime(8,0,0,9,20,2012);
//
////tentukan waktu saat ini
//$waktu_sekarang = mktime(date(“H”), date(“i”), date(“s”), date(“m”), date(“d”), date(“Y”));
//
////hitung selisih kedua waktu
//$selisih_waktu = $waktu_tujuan – $waktu_sekarang;
//
////Untuk menghitung jumlah dalam satuan hari:
//$jumlah_hari = floor($selisih_waktu/86400);
//
////Untuk menghitung jumlah dalam satuan jam:
//$sisa = $selisih_waktu % 86400;
//$jumlah_jam = floor($sisa/3600);
                break;
        }

?>