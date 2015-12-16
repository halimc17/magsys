<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
//##kdkegiatan##ket##satuan##nilsngtbaik##nilbaik##nilckp##nilkrg##method
$kodeorg=$_POST['kodeorg'];
$periode=$_POST['periode'];
#ambil kodePT:
$str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kodeorg,0,4)."'";
$res=mysql_query($str);
$kodept='';
while($bar=mysql_fetch_object($res)){
    $kodept=$bar->induk;
}
if($kodept==''){
    exit(' Error: Org code is missing');
}
#ambil periode akunting
$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where left(kodeorg,4)= '".$kodeorg."' and periode='".$periode."'";
$res=mysql_query($str);
$mulai='';
$sampai='';
while($bar=mysql_fetch_object($res)){
    $mulai=$bar->tanggalmulai;
     $sampai=$bar->tanggalsampai;
}
if($mulai=='' or $sampai==''){
    exit(" Error: periode akuntansi unit ".$kodeorg." belum terdaftar");
}else{   
#ambil transaksi material
    $bkmMat=Array();
    $str="select a.*,b.jurnal,b.tanggal,c.kodekegiatan from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
             left join ".$dbname.".kebun_prestasi c on a.notransaksi=c.notransaksi
              where b.tanggal>='".$mulai."' and b.tanggal<='".$sampai."' and b.jurnal=1 and b.kodeorg='".$kodeorg."'";
    $res=mysql_query($str);
    //echo mysql_error($conn);
    while($bar=mysql_fetch_array($res)){
             $bkmMat[]=$bar;
             $bkmLast[]=$bar;
    }

 #ambil transaksi gudang
    $str="select notransaksireferensi from ".$dbname.".log_transaksiht where kodegudang like '".$kodeorg."%' and tanggal>='".$mulai."' and tanggal<='".$sampai."'
              and tipetransaksi=5 and notransaksireferensi is not null and notransaksireferensi!='' order by kodegudang";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $log[]=$bar->notransaksireferensi;
    }
  if(count($log)>0){
    foreach($log as $key =>$val){
            foreach($bkmMat as $key1 => $val1){
                     if($val1['notransaksi']==$val){
                             unset($bkmLast[$key1]);
            }
        }
    }
  }
  #material BKMyg tidak ada di log_transasi
            if(count($bkmLast)==0){
                echo "Transaction clear, All BKM material on period ".$periode." has been listed on inventory";
            }  
            else{
                if(isset($_POST['preview'])){
                            echo "Below transaction not registered on inventory:<br>";
                            echo"<table class=sortable border=0 cellspacing=1>
                                     <thead>
                                      <tr class=rowheader>
                                         <td>".$_SESSION['lang']['notransaksi']."</td>
                                         <td>".$_SESSION['lang']['tanggal']."</td>
                                         <td>".$_SESSION['lang']['gudang']."</td>
                                         <td>".$_SESSION['lang']['kodebarang']."</td>
                                         <td>".$_SESSION['lang']['jumlah']."</td>
                                          <td>".$_SESSION['lang']['kodeblok']."</td>   
                                       </tr></thead><tbody>";
                             foreach($bkmLast as $key => $val){
                               echo "<tr class=rowcontent>
                                         <td>".$val['notransaksi']."</td>
                                         <td>".$val['tanggal']."</td>
                                         <td>".$val['kodegudang']."</td>
                                         <td>".$val['kodebarang']."</td>
                                         <td>".$val['kwantitas']."</td>
                                          <td>".$val['kodeorg']."</td>   
                                    </tr>";
                              }
                              echo "</tbody><tfoot></table></table>";
                }else{
                    #create transaction                              
                                         $nam=array();
                                        foreach($bkmLast as $key => $val){
                                                if(empty($nam[$val['kodegudang']])){
                                                    $sTgl="select tanggalsampai,tanggalmulai  from ".$dbname.".setup_periodeakuntansi where kodeorg='".$val['kodegudang']."' and periode='".$periode."'";
                                                    $qTgl=mysql_query($sTgl) or die(mysql_error($conn));
                                                    $rTgl=mysql_fetch_assoc($qTgl);
                                                    $str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi=5 and kodegudang='".$val['kodegudang']."' and tanggal between '".$rTgl['tanggalmulai']."' and '".$rTgl['tanggalsampai']."' 
                                                          and notransaksireferensi!='' order by notransaksi desc limit 1";
                                                    $qstr=mysql_query($str) or die(mysql_error($conn));
                                                    $rstr=mysql_fetch_assoc($qstr);
                                                    $nam[$val['kodegudang']]=substr($rstr['notransaksi'],7,4);    
                                                }
                                                $nam[$val['kodegudang']]=intval($nam[$val['kodegudang']])+1;     
                                                
                                                $num=str_pad($nam[$val['kodegudang']],4,"0",STR_PAD_LEFT);
                                                $num=  str_replace("-", "", $periode)."M".$num."-GI-".$val['kodegudang'];
                                                 #ambil satuan
                                                 $satuan='';
                                                 $str="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$val['kodebarang']."'";
                                                 $res=mysql_query($str);
                                        while($bar=mysql_fetch_object($res)){
                                            $satuan=$bar->satuan;
                                        }
                                        

                                        #create header
                                        $dataMat['header'][] = array(
                                                'tipetransaksi'=>'5',
                                                'notransaksi'=>$num, 
												'tanggal'=>$val['tanggal'], 
                                                 'kodept'=>$kodept, 
                                                 'untukpt'=>$kodept, 
                                                 'nopo'=>'', 
                                                 'nosj'=>'', 
                                                 'keterangan'=>'Material BKM ', 
                                                 'statusjurnal'=>'1', 
                                                 'kodegudang'=>$val['kodegudang'], 
                                                 'user'=>$_SESSION['standard']['userid'], 
                                                 'namapenerima'=>'0', 
                                                 'mengetahui'=>$_SESSION['standard']['userid'], 
                                                 'idsupplier'=>'', 
                                                 'nofaktur'=>'', 
                                                 'post'=>'1', 
                                                 'postedby'=>$_SESSION['standard']['userid'], 
                                                 'untukunit'=>substr($val['kodeorg'],0,4), 
                                                 'notransaksireferensi'=>$val['notransaksi'], 
                                                 'gudangx'=>'',
                                                 'lastupdate'=>date('Y-m-d H:i:s')
                                                    );
                                        #detail log_transaksidt 
                                                $dataMat['detail'][]=array(
                                                'notransaksi'=>$num,
												'nopp'=>'',
                                                'kodebarang'=>$val['kodebarang'], 
                                                'satuan'=>$satuan, 
                                                'jumlah'=>$val['kwantitas'], 
                                                'jumlahlalu'=>0, 
                                                'hargasatuan'=>'0', 
                                                'kodeblok'=>$val['kodeorg'],
                                                'waktutransaksi'=>date('Y-m-d H:i:s'),
                                                'updateby'=>$_SESSION['standard']['userid'], 
                                                'kodekegiatan'=>$val['kodekegiatan'], 
                                                'kodemesin'=>'', 
                                                'statussaldo'=>1, 
                                                'hargarata'=>$val['hargasatuan'],
												'nopo'=>'',
                                                'kodesegment' => '0000000001'
                                                );
                                                $num="";
                                    } 
                                   
                                                                         $errorX='';                                     
                                        foreach($dataMat['header'] as $key=>$dataX) {
                                            $queryD = insertQuery($dbname,'log_transaksiht',$dataX);
                                              if(!mysql_query($queryD)) {
                                                $errorX = " Error insert header material :".$queryD.":".mysql_error()."\n";
                                              }
                                        }
                                        if($errorX!=''){
                                              #rollback material
                                                        foreach($dataMat['header'] as $key=>$dataX) {
                                                               $queryD =" delete from ".$dbname.".log_transaksiht where notransaksi='".$dataX['notransaksi']."'";
                                                            mysql_query($queryD);
                                                            }
                                             echo $errorX;               
                                        }else{
                                            #insert detail
                                                        $errorY='';
                                                        foreach($dataMat['detail'] as $key=>$dataY) {
                                                        $queryD = insertQuery($dbname,'log_transaksidt',$dataY);
                                                           if(!mysql_query($queryD)) {
                                                            $errorY = " Error insert detail material :".$queryD.":".mysql_error()."\n";
                                                           }
                                                      }
                                                      if( $errorY!=''){#rollback header only
                                                        foreach($dataMat['header'] as $key=>$dataX) {
                                                               $queryD =" delete from ".$dbname.".log_transaksiht where notransaksi='".$dataX['notransaksi']."'";
                                                            mysql_query($queryD);
                                                            }  
                                                          echo $errorY;            
                                                      }
                                        }
                                    
                                    
                    } 
          }
}
?>
