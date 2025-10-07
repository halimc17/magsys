<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
$proses=checkPostGet('proses','');
$tglijin=tanggalsystem(checkPostGet('tglijin',''));
$jnsIjin=checkPostGet('jnsIjin','');
$jamDr=checkPostGet('jamDr','');
$jamSmp=checkPostGet('jamSmp','');
$keperluan=checkPostGet('keperluan','');
$ket=checkPostGet('ket','');
$atasan=checkPostGet('atasan','');
$tglAwal=explode("-",checkPostGet('tglAwal','00-00-0000'));
$tgl1=$tglAwal[2]."-".$tglAwal[1]."-".$tglAwal[0];
$tglEnd=explode("-",checkPostGet('tglEnd','00-00-0000'));
$tgl2=$tglEnd[2]."-".$tglEnd[1]."-".$tglEnd[0];
$jamDr1=$tgl1." ".$jamDr;
$jamSmp1=$tgl2." ".$jamSmp;
$arrNmkary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan=array("0"=>$_SESSION['lang']['diajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
//$where=" tanggal='".$tglijin."' and karyawanid='".$_SESSION['standard']['userid']."'";
$where=" (tanggal='".$tglijin."' and karyawanid='".$_SESSION['standard']['userid']."' and jenisijin='".$jnsIjin."' and '".$jamDr1."'>=darijam and '".$jamDr1."'<=sampaijam) or (tanggal='".$tglijin."' and karyawanid='".$_SESSION['standard']['userid']."' and jenisijin='".$jnsIjin."' and '".$jamSmp1."'>=darijam and '".$jamSmp1."'<=sampaijam)";
$atsSblm=checkPostGet('atsSblm','');
$hk=checkPostGet('jumlahhk','');
$hrd=checkPostGet('hrd','');
$periodec=checkPostGet('periodec','');

if(($proses=='update' or $proses=='insert') and $jnsIjin=='CUTI'){
//===============ambil sisa cuti
//
//
//

                //ambil cuti ybs
                $strf="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." 
                    and periodecuti=".$periodec;
                $res=mysql_query($strf);

                $sisa='';
                while($barf=mysql_fetch_object($res))
                {
                    $sisa=$barf->sisa;
                }
                if($sisa=='')
                    $sisa=0;
                //=============================      
            #ambil periode cuti terakhir
				$lastp='';
                $strfx="select max(periodecuti) as periodecuti from ".$dbname.".sdm_cutiht 
				where karyawanid=".$_SESSION['standard']['userid'];
                $resx=mysql_query($strfx);
                while($barx=mysql_fetch_object($resx)){
                    $lastp=$barx->periodecuti;
                }                
			#periksa apakah HRD tidak lupa setting saldo awal cuti
				  $tahunmulaiCuti=substr($_SESSION['empl']['signdate'],0,4)+1;
				  $tanggalAwalCuti=$tahunmulaiCuti.substr($_SESSION['empl']['signdate'],4,6);			
				
              //periksa apakah mengajukan cuti sebelum periode cuti berjalan
                $zz=substr($tgl1,0,4);
                if(($lastp<$zz and $lastp!='') or ($lastp=='' and $tanggalAwalCuti<substr($tgl1,0,10))){
                //insert cuti baru dan ubah sisa   
                    #ambil tanggal masuk
                    $str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas from ".$dbname.".datakaryawan
						where  karyawanid='".$_SESSION['standard']['userid']."'";
                        $res1=mysql_query($str1);
                        while($bar1=mysql_fetch_object($res1))
                        {
                                    //=================================
                                    //default
                                    $x=readTextFile('config/jumlahcuti.lst');
                                    if(intval($x)>0)
                                        $hakcuti=$x;
                                    else
                                        $hakcuti=12;  //default
                                    #jika bukan orang HO maka dapat 
                                    if($bar1->tipekaryawan==0 and substr($bar1->lokasitugas,2,2)!='HO')
                                            $hakcuti=12;
                                    else if($bar1->tipekaryawan!=0 and substr($bar1->lokasitugas,2,2)!='HO')
                                            $hakcuti=12;
                                    $sisa=$hakcuti;
                                    
                                    //lanjut jika tahun pertama
                                    if(substr($bar1->tanggalmasuk,0,4)>=$zz){
                                        continue;//tidak melakukan apa apa, karena belum berhak dapat cuti
                                    }
                                    
                                    //=================================
                                    $tgl=substr(str_replace("-","",$bar1->tanggalmasuk),4,4);		
                                    $dari=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$zz);
                                    $dari=date('Ymd',$dari);
                                    $sampai=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$zz+1);		
                                    $sampai=date('Ymd',$sampai);
                                    #jika periode masuk masih belum 1tahun maka 0
                                     $d=substr(str_replace("-","",$bar1->tanggalmasuk),0,4);
                                #ambil sisa cuti YBS
                                     $str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$bar1->karyawanid." 
                                               and periodecuti>".($periodec-2)." order by periodecuti desc limit 1";
                                     $resx=mysql_query($str);
                                     $sisalalu=0;
                                     while($barx=mysql_fetch_object($resx))
                                     {
                                         $sisalalu=$barx->sisa;
                                     }
                                #periksa apakah sudah ada pada periode yang sama
                                      $str="select * from ".$dbname.".sdm_cutiht where karyawanid=".$bar1->karyawanid." 
                                               and periodecuti=".$periodec." order by periodecuti desc limit 1";
                                     $resy=mysql_query($str);
                                     if(mysql_num_rows($resy)>0)
                                     {
                                         #berarti  saldo saat ini adalah sisalalu
                                         #$saldo=$sisalalu;
                                         #tidak ada perubahan
                                     }
                                     else
                                     {   
                                         $saldo=$hakcuti;
                                        #==========================periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
                                                        $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
                                                            where karyawanid=".$bar1->karyawanid."
                                                             and  daritanggal >=".$dari." and daritanggal<=".$sampai;
                                                        $diambil=0;#sudah diambil diambil tahun ini
                                                        $resx=mysql_query($strx);
                                                        while($barx=mysql_fetch_object($resx))
                                                        {
                                                                $diambil=$barx->diambil;
                                                                if($diambil=='')
                                                                    $diambil=0;
                                                        }
                                            $saldo=$saldo-$diambil;       
                                            $sisa=$saldo;
                                         #================================================================
                                         #maka insert periode baru
                                         $str="insert into ".$dbname.".sdm_cutiht(kodeorg, karyawanid, periodecuti, keterangan, dari, sampai, hakcuti, diambil, sisa)
                                                   values('".$bar1->lokasitugas."',".$bar1->karyawanid.",".$periodec.",'',".$dari.",".$sampai.",".$hakcuti.",0,".$saldo.")";
                                         mysql_query($str);
                                     } 
                        }              
                }
				function getRangeTanggal($tglAwal,$tglAkhir){
					$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
					$jlhHari = $jlh / (3600*24);
					return $jlhHari + 1;
				}
				
				if(getRangeTanggal($_POST['tglAwal'],$_POST['tglEnd']) <= 0){
					exit("Gagal : Periksa kembali periode tanggal cuti. Tanggal Awal lebih besar dari tanggal sampai.");
				}
				
				if(getRangeTanggal($_POST['tglAwal'],$_POST['tglEnd']) < $hk){
					exit("Gagal : Periksa kembali periode tanggal cuti, tidak sesuai dengan jumlah HK yang diambil. ".strtotime($_POST['tglAwal'])." - ".strtotime($_POST['tglEnd']));
				}
				
				$strf="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." 
					   and periodecuti=".$periodec;
				$res=mysql_query($strf);

				$sisa='';
				while($barf=mysql_fetch_object($res))
				{
					$sisa=$barf->sisa;
				}
				if($sisa=='')
					$sisa=0;
				
				if($hk > $sisa){
                    $potgaji=$hk - $sisa;
					//echo("Warning: Jumlah HK(Hari) melebihi jumlah sisa cuti untuk periode ".$periodec.". Apakah bersedia dipotong ".$potgaji." hari gaji?");
                    //exit("Warning: Jumlah HK(Hari) melebihi jumlah sisa cuti untuk periode ".$periodec.". Apakah bersedia dipotong ".$potgaji." hari gaji?");
				}
}



        switch($proses)
        {

                case'jumlahhari':

					$total_days=$param['total_days'];
					if($param['tglAwal']==''){
					   $tglAwal=tanggalsystem($param['tglEnd']);
					}else{
					   $tglAwal=tanggalsystem($param['tglAwal']);
					}
					if($param['tglEnd']==''){
					   $tglEnd=tanggalsystem($param['tglAwal']);
					}else{
					   $tglEnd=tanggalsystem($param['tglEnd']);
					}
					if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
					   $strlibur="select count(*) as jumlahlibur from ".$dbname.".sdm_5harilibur where kebun in ('HOLDING','GLOBAL') and keterangan='libur' and (tanggal>=$tglAwal and tanggal<=$tglEnd)";
					}else{
					   $strlibur="select count(*) as jumlahlibur from ".$dbname.".sdm_5harilibur where kebun in ('".substr($_SESSION['empl']['lokasitugas'],0,4)."','GLOBAL') and keterangan='libur' and (tanggal>=$tglAwal and tanggal<=$tglEnd)";
					}
					$reslibur=mysql_query($strlibur);
					$jmlhrlibur=0;
					while($barlibur=mysql_fetch_object($reslibur))
					{ 
						$jmlhrlibur=$barlibur->jumlahlibur;
					}
					$jumlahcuti=0;
					if($param['jnsIjin']=='CUTI' or $param['jnsIjin']=='MELAHIRKAN' or $param['jnsIjin']=='PERJALANAN' or $param['jnsIjin']=='SKRIPSI_TESIS' or $param['jnsIjin']=='ALASANPENTING'){
					   $jumlahcuti=$total_days-$jmlhrlibur;
                    }
					$res = array('jumlahhk' => $jumlahcuti);
					//echo json_encode($res);
					echo $res['jumlahhk'];
					break;
                         
                case'insert':
                  //  exit("Error:masuk");
                if(($tglijin=='')||($jnsIjin=='')||($jamDr1=='')||($jamSmp1=='')||($keperluan=='')||($atasan==''))
                {
                        echo"Warning: Please Complete The Form";
                        exit();
                }
				
				#periksa apakah periode yang diambil sudah lewar 1.5 tahun
				if($jnsIjin=='CUTI'){ 
				#periksa apakah sudah boleh cuti:
				  $tahunmulaiCuti=substr($_SESSION['empl']['signdate'],0,4)+1;
				  $tanggalAwalCuti=$tahunmulaiCuti.substr($_SESSION['empl']['signdate'],4,6);				  
				  if(substr($jamDr1,0,10)<$tanggalAwalCuti){
				     exit("Error: Anda belum memiliki hak cuti, hak cuti akan muncul pada tanggal: ".$tanggalAwalCuti);
				  }
				  $stt="select sampai from ".$dbname.".sdm_cutiht where periodecuti='".$periodec."' and
				        karyawanid=".$_SESSION['standard']['userid'];
				  $rett=mysql_query($stt);
			      while($batt=mysql_fetch_object($rett)){
				    $tanggalAkhir=str_replace("-","",$batt->sampai);
				  }	
				  $tahunAkh=intval(substr($tanggalAkhir,0,4));
				  $bulanAkh=intval(substr($tanggalAkhir,4,2));
				  $tanggalAkh=intval(substr($tanggalAkhir,6,2));
				  
				  $dudu=mktime(0,0,0,$bulanAkh+6,$tanggalAkh,$tahunAkh);
				  $akhirBanget=date('Y-m-d',$dudu);
				  if(substr($jamSmp1,0,10)>$akhirBanget){
				  #keluarkan disini jika sudah lebih dari 1.5 tahun
				     exit("Error: Maaf, Cuti atas masa bakti tahun ".$periodec." berakhir pada ".date('d-m-Y',$dudu));
				  }
				  $swcuti="select * from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'
															   and jenisijin='CUTI' 
															   and YEAR(darijam) = YEAR(now())
															   and darijam < NOW()
															   and stpersetujuan1='0'";
				  $qwcuti=mysql_query($swcuti);
                  if(mysql_num_rows($qwcuti)>0){
					  exit('Warning : Masih ada cuti yang belum diapprove!');
				  }
				}
				#==== end satu setengah tahun
				
                $wktu="0000-00-00 00:00:00";
                $sCek="select tanggal from ".$dbname.".sdm_ijin where  ".$where.""; //echo "warning:".$sCek;
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_row($qCek);
                if($rCek<1)
                {
                    if($atasan!='')
                    {
                        $wktu=date("Y-m-d H:i:s");
                    }
                        $sIns="insert into ".$dbname.".sdm_ijin (karyawanid, tanggal, keperluan, keterangan, persetujuan1, waktupengajuan, darijam, sampaijam, jenisijin,hrd,periodecuti,jumlahhari) 
                        values ('".$_SESSION['standard']['userid']."','".$tglijin."','".$keperluan."','".$ket."','".$atasan."','".$wktu."','".$jamDr1."','".$jamSmp1."','".$jnsIjin."',".$hrd.",".$periodec.",".$hk.")";
                            //exit("Error:$sIns");
                        if(mysql_query($sIns))
                        {
                            if($atasan!='')
                            {
                                #send an email to incharge person
                                    $to=getUserEmail($atasan);
                                    //$to='';
                                            $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
                                            $subject="[Notifikasi]Persetujuan Ijin ".$jnsIjin." a/n ".$namakaryawan;
                                            $body="<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Ijin ".$jnsIjin." (".$keperluan.")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode ".$periodec." : ".$sisa." Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Medco Agro System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                                            $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                                            
                                            
                                           #117 
                                           #print_r($_SESSION['empl']['regional']);
                                ##send email ke roa jika cuti
                                if($jnsIjin=='CUTI')
                                { 
                                    
                                  $iRoa="select karyawanid from ".$dbname.".datakaryawan where kodejabatan='117' and "
                                          . " lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment "
                                          . " where regional='".$_SESSION['empl']['regional']."' and kodeunit like '%RO%') ";  
                                  $nRoa=  mysql_query($iRoa) or die (mysql_error($conn));
                                  $dRoa=  mysql_fetch_assoc($nRoa);
                                    $roa=$dRoa['karyawanid'];

                                  //$toroa=getUserEmail($roa);
                                  $toroa='';
                                                $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
                                                $subject="[Notifikasi]Persetujuan Ijin ".$jnsIjin." a/n ".$namakaryawan;
                                                $body="<html>
                                                         <head>
                                                         <body>
                                                           <dd>Dengan Hormat,</dd><br>
                                                           <br>
                                                           Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Ijin ".$jnsIjin." (".$keperluan.")
                                                           kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                           <br>
                                                           <br>
                                                           Note: Sisa cuti ybs periode ".$periodec." : ".$sisa." Hari
                                                           <br>
                                                           <br>
                                                           Regards,<br>
                                                           Medco Agro System.
                                                         </body>
                                                         </head>
                                                       </html>
                                                       ";
                                                $kirimroa=kirimEmail($toroa,'',$subject,$body);    
                                }              
                                            
                                            
                            }
                            
                            
                            
                            
                        }
                        else
                        {
                            echo "DB Error : ".mysql_error($conn);
                        }
                }
                else
                {
                    exit("Error:Data Pada Tanggal ".$_POST['tglijin']." dan tanggal cuti Sudah ada");
                }
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

                $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'  order by `tanggal` desc";// echo $ql2;
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }

                $slvhc="select * from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'   order by `tanggal` desc limit ".$offset.",".$limit." ";
                $qlvhc=mysql_query($slvhc) or die(mysql_error());
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=mysql_fetch_assoc($qlvhc))
                {
                $no+=1;

                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".$rlvhc['keperluan']."</td>
                <td>".$rlvhc['jenisijin']."</td>
                <td>".$arrNmkary[$rlvhc['persetujuan1']]."</td>
                <td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>
                <td>".tanggalnormald($rlvhc['darijam'])."</td>
                <td>".tanggalnormald($rlvhc['sampaijam'])."</td>";
                if($rlvhc['stpersetujuan1']==0 and empty($rlvhc['stpersetujuanrd']))
                {
					if($rlvhc['darijam']>=date('Y-m-d')){
					  echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['keperluan']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['jenisijin']."','".$rlvhc['persetujuan1']."','".$rlvhc['stpersetujuan1']."','".$rlvhc['darijam']."','".$rlvhc['sampaijam']."','".$rlvhc['hrd']."','".$rlvhc['jumlahhari']."','".$rlvhc['periodecuti']."');\">
                      <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".tanggalnormal($rlvhc['tanggal'])."');\">
                      <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)\"></td>";
					}else{
					  echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)\"></td>"; 
					}
                }
                else
                {
                    echo "<td>".$arrKeputusan[$rlvhc['stpersetujuan1']]."</td>";
                    //"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['karyawanid']."',event)\"></td>";
                }//end if updateby

        }//end while
                echo"
                </tr><tr class=rowheader><td colspan=9 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
                case'getKet':
                $sket="select distinct keterangan from ".$dbname.".sdm_ijin where ".$where."";
                $qKet=mysql_query($sket) or die(mysql_error($conn));
                $rKet=mysql_fetch_assoc($qKet);

                echo $rKet['keterangan'];
                break;

                case'deleteData':
                $sket="select distinct stpersetujuan1 from ".$dbname.".sdm_ijin where ".$where."";
                $qKet=mysql_query($sket) or die(mysql_error($conn));
                $rKet=mysql_fetch_assoc($qKet); 
                if($rKet['stpersetujuan1']==0)
                {
                        $sDel="delete from ".$dbname.".sdm_ijin where ".$where."";
                        //exit("Error".$sDel);
                        if(mysql_query($sDel))
                        echo"";
                        else
                        echo "DB Error : ".mysql_error($conn);                        
                }
                else
                {
                    exit("Error:Sudah ada keputusan");
                }
                break;

                case'update':
 
                    //=============================		
                if(($jnsIjin=='')||($jamDr=='')||($jamSmp=='')||($keperluan=='')||($atasan==''))
                {
                        echo"warning:Please Complete The Form";
                        exit();
                }
                $sket="select distinct stpersetujuan1 from ".$dbname.".sdm_ijin where ".$where."";
                $qKet=mysql_query($sket) or die(mysql_error($conn));
                $rKet=mysql_fetch_assoc($qKet); 
                if($rKet['stpersetujuan1']==0)
                {
                    //(karyawanid, tanggal, keperluan, keterangan, persetujuan1, waktupengajuan, darijam, sampaijam, jenisijin) 
                        //values ('".$_SESSION['standard']['userid']."','".$tglijin."','".$keperluan."','".$ket."','".$atasan."','".$wktu."','".$jamDr."','".$jamSmp."','".$jnsIjin."')
                    $sUp="update  ".$dbname.".sdm_ijin set keperluan='".$keperluan."', keterangan='".$ket."', darijam='".$jamDr1."', 
                          sampaijam='".$jamSmp1."',jenisijin='".$jnsIjin."',
                          hrd=".$hrd.",periodecuti=".$periodec.",jumlahhari=".$hk;
                    if($atsSblm!=$atasan)
                    {
                         $wktu=date("Y-m-d H:i:s");
                         $sUp.=",persetujuan1='".$atasan."',waktupengajuan='".$wktu."'";
                    }
                    $sUp.=" where ".$where."";
                    if(mysql_query($sUp))
                    {
                        if($atsSblm!=$atasan)
                        {
                               #send an email to incharge person
                                    $to=getUserEmail($atasan);
                                            $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
                                            $subject="[Notifikasi]Persetujuan Ijin ".$jnsIjin." a/n ".$namakaryawan;
                                            $body="<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Ijin ".$jnsIjin." (".$keperluan.")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode ".$periodec." : ".$sisa." Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Medco Agro System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                                            $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                        }
                    }
                    //mysql_query($sUp) or die(mysql_error());
                }
                 else
                {
                    exit("Error:Sudah ada keputusan");
                }
                if($atsSblm!=$atasan)
                {
                                    $to=getUserEmail($atsSblm);
                                            $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
                                            $subject="[Notifikasi]Pembatalan Persetujuan Ijin ".$jnsIjin." a/n ".$namakaryawan;
                                            $body="<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Ijin ".$jnsIjin." (".$keperluan.")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode ".$periodec." : ".$sisa." Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Medco Agro System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                                            $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;

                }
                break;
                default:
                break;
        }


?>