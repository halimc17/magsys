<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('config/connection.php');
$pemisah=$_POST['pemisah'];
$jenisdata=$_POST['jenisdata'];
$path='tempExcel';

  if(is_dir($path))
  {
  	writeFile($path,$pemisah);
	//chmod($path, 0777);
  }
  else
  {
  	if(mkdir($path))
	{
                    writeFile($path,$pemisah);
	 // chmod($path, 0777);
	}
	else
	{
                        echo "<script> alert('Gagal, Can`t create folder for uploaded file');</script>";
                        exit(0);
	}
  } 
function writeFile($path,$pemisah)
{ 
          global $jenisdata;
               $dir=$path;
              $ext=explode('.', basename( $_FILES['filex']['name']));
                  $ext=$ext[count($ext)-1];
                  $ext=strtolower($ext);
                  if($ext=='csv')
                  {
                  $path = $dir."/".date('ymd').".".$ext;
                  @unlink($path);
                  try{
                         if(move_uploaded_file($_FILES['filex']['tmp_name'], $path))
                         {
                                $x=readCSV($path,$pemisah);
                                simpanData($x,$jenisdata);
                                
                         }
                   }
                   catch(Exception $e)
                   {
                         echo "<script>alert(\"Error Writing File".addslashes($e->getMessage())."\");</script>";
                   }
                  }
                  else
                  {
                         echo "<script>alert('Filetype not support');</script>";		 	
                  }
}
function simpanData($x,$jenisdata){
    global $dbname;
    global $conn;
    global $pemisah;
      $jlhbaris=count($x)-1;
      #baris pertama adalah header;
      foreach($x[0] as $val){
          $header[]=trim($val);
      }
      switch ($jenisdata) {
          case 'ACCBAL':  
              #ambil noakun
              $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $noakun[]=$bar->noakun;
              }
              #periksa kelengkapan data
              if(count($x[0])!=4){
                  exit("Error: Form not valid");
              }
          foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{
                  foreach($arr as $ids =>$rinc){
                      if($header[$ids]=='periode' and strlen($rinc)!=6){
                          exit("Error: some data on period not valid (line ".$key.")");
                      }
                      else if($header[$ids]=='noakun' and strlen($rinc)!=7){
                          exit("Error: some data on noakun not valid (line ".$key.")");
                      }
                      else if($header[$ids]=='kodeorg' and strlen($rinc)!=4){
                          exit("Error: some data on kodeorg not valid (line ".$key.$rinc.")");
                      }
                      else if($header[$ids]=='noakun' ){
                        #periksa noakun yang disubmit
                        $akunbermasalah[$rinc]=$rinc;
                        foreach($noakun as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($akunbermasalah[$rinc]);
                        }
                      }
                     }
              }
          }
          if(count($akunbermasalah)>0){
              echo "The following account number were not defined:<br>";
              print_r($akunbermasalah);
              exit();
          }

              #ambil  kolom periode
              foreach ($header as $ki=> $val){
                if($val=='periode'){
                    $index=$ki;
                  }
                if($val=='kodeorg'){
                    $idkOrg=$ki;
                }
              }
              $column='awal'.substr($x[1][$index],4,2);
              foreach($header as $ki=>$val){    
                  if($val=='saldo'){
                      $header[$ki]=$column;
                      $indexNumeric=$ki;
                  }
              }
              #delete first
              $str="delete from ".$dbname.".keu_saldobulanan where kodeorg='".$x[1][$idkOrg]."' and periode='".$x[1][$index]."'";
              mysql_query($str);
              #generate SQL:
              $stringSQL="insert into ".$dbname.".keu_saldobulanan(";
              foreach ($header as $ki=> $val){
                  if($ki==0)
                     $stringSQL.=$val;
                  else
                     $stringSQL.=",".$val;                      
              }
              $stringSQL.=") values";
               foreach($x as $key =>$arr){
                    if($key==0){
                        continue;
                    }else{
                            foreach($arr as $ki=>$val){
                                if($ki==0){
                                    if($key==1){
                                        $stringSQL.="('".trim($val)."'";
                                    }
                                    else{
                                        $stringSQL.=",('".trim($val)."'";
                                    }
                                }else{
                                    $stringSQL.=",'".trim($val)."'";
                                }
                            }
                            $stringSQL.=")";
                    }
               }
               $stringSQL.=";";
               if(mysql_query($stringSQL)){
                   echo "Uploaded";
               }
               else{
                   echo mysql_error($conn).$stringSQL;
               }
              break;
#===========================================================end  prevbal ======================================================             
           case 'JOURNAL':  
              #ambil noakun
              $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $noakun[]=$bar->noakun;
              }
              #ambil  nik
              $str="select karyawanid from ".$dbname.".datakaryawan";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $nik[]=$bar->karyawanid;
              }
              #ambil  kegiatan
              $str="select kodekegiatan from ".$dbname.".setup_kegiatan";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $kegiatan[]=$bar->kodekegiatan;
              }
              #ambil  supplier
              $str="select supplierid from ".$dbname.".log_5supplier";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $supplier[]=$bar->supplierid;
              }
              #ambil  custommer
              $str="select kodecustomer  from ".$dbname.".pmn_4customer";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $custommer[]=$bar->kodecustomer;
              }
              
              #ambil  blok
              $str="select kodeorg  from ".$dbname.".setup_blok";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $blok[]=$bar->kodeorg;
              }
              
              #periksa kelengkapan data
              $zz=0;
          foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                      $x[$key][$ids]=trim($rinc);
                      if($header[$ids]=='tanggal'){
                          $rinc=str_replace('-','',$rinc);
                          $rinc=str_replace('/','',$rinc);
                          if(strlen($rinc)!=8){
                            exit("Error: some data on date not valid (line ".$key.")");
                          }
                          else if(substr($rinc,0,4)<'2000'){
                              exit("Error: date not valid (line ".$key.")");
                          }
                          else
                            $x[$key][$ids]=$rinc;
                      }
                      if($header[$ids]=='noakun' and strlen($rinc)!=7 and $rinc!='0'){
                          exit("Error: some data on noakun not valid (line ".$key.")");
                      }
                      if($header[$ids]=='kodeorg' and strlen($rinc)!=4){
                          exit("Error: some data on kodeorg not valid (line ".$key.")");
                      }
                      if($header[$ids]=='matauang'){
                          if(trim($rinc)=='')
                          exit("Error: some data on currency not valid (line ".$key.")");
                      }                      
                      if($header[$ids]=='kurs'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=1;
                      }  
                      if($header[$ids]=='nourut'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=$zz++;
                      }  
                      
                      if($header[$ids]=='noakun' ){
                        #periksa noakun yang disubmit
                        $akunbermasalah[$rinc]=$rinc;
                        foreach($noakun as $bb=>$cc){
                                if($cc==$rinc or trim($rinc)=='0')
                                    unset($akunbermasalah[$rinc]);
                        }
                      }
                      
                      if($header[$ids]=='nik'  and trim($rinc)!=''){
                        #periksa nik yang disubmit
                        $nikbermasalah[$rinc]=$rinc;
                        foreach($nik as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($nikbermasalah[$rinc]);
                        }
                      }
                       if($header[$ids]=='kodekegiatan'   and trim($rinc)!=''){
                        $kegiatanbermasalah[$rinc]=$rinc;
                        foreach($kegiatan as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($kegiatanbermasalah[$rinc]);
                        }
                      }                     
                        if($header[$ids]=='kodesupplier'   and trim($rinc)!=''){
                        $supplierbermasalah[$rinc]=$rinc;
                        foreach($supplier as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($supplierbermasalah[$rinc]);
                        }
                      }                        
            
                        if($header[$ids]=='kodecustomer'   and trim($rinc)!=''){
                        $custommerbermasalah[$rinc]=$rinc;
                        foreach($custommer as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($custommerbermasalah[$rinc]);
                        }
                      }                         
            
                        if($header[$ids]=='kodeblok'   and trim($rinc)!=''){
                        $blokbermasalah[$rinc]=$rinc;
                        foreach($blok as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($blokbermasalah[$rinc]);
                        }
                      }                        
                  }
              }
          }
          $bermasalah=false;
          if(count($akunbermasalah)>0){
              echo "The folowing account number were not defined:<br>";
              print_r($akunbermasalah);
              $bermasalah=true;
          }
          if(count($nikbermasalah)>0){
              echo "The folowing NIK were not defined:<br>";
              print_r($nikbermasalah);
              $bermasalah=true;
          }
          if(count($kegiatanbermasalah)>0){
              echo "The folowing activity code were not defined:<br>";
              print_r($kegiatanbermasalah);
              $bermasalah=true;
          }
          if(count($supplierbermasalah)>0){
              echo "The folowing supplier/contractor code were not defined:<br>";
              print_r($supplierbermasalah);
              $bermasalah=true;
          }
          if(count($custommerbermasalah)>0){
              echo "The folowing custommer code were not defined:<br>";
              print_r($custommerbermasalah);
              $bermasalah=true;
          }
          if(count($blokbermasalah)>0){
              echo "The folowing block code were not defined:<br>";
              print_r($blokbermasalah);
              $bermasalah=true;
          }
        if($bermasalah){
            exit();
        }
     #periksa jumlah debet dan kredit
         foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                        if($header[$ids]=='jumlah'){
                            $total+=$rinc;
                            $tt+=abs($rinc);
                        }
                  } 
              }
         }
         $tdecre=$tt/2;
         if(abs($total)>100){
             exit("Error:Total amount not balance:".$total);
         }
     #create header journal
                    #ambil  kolom periode
              foreach ($header as $ki=> $val){
                if($val=='tanggal'){
                    $itanggal=$ki;
                  }
                if($val=='nojurnal'){
                    $inojurnal=$ki;
                }
                if($val=='kurs'){
                    $ikurs=$ki;
                }
                 if($val=='matauang'){
                    $imatauang=$ki;
                }           
                  if($val=='noreferensi'){
                    $inoreferensi=$ki;
                }                   
              }   
      
//              #delete first
              $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$x[2][$inojurnal]."'";
              mysql_query($str);
 
#generate header journal
         $str="insert into ".$dbname.".keu_jurnalht (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`, `posting`, `totaldebet`, `totalkredit`, `amountkoreksi`, `noreferensi`, `autojurnal`, `matauang`, `kurs`, `revisi`) VALUES
                    ('".$x[2][$inojurnal]."', 'Hist', '".$x[2][$itanggal]."', '".date('Ymd')."', 1,".$tdecre.", -".$tdecre.", '0', '".$x[2][$inoreferensi]."', 1, '".$x[2][$imatauang]."', ".$x[2][$ikurs].", 0);";
                 
//              #generate detail SQL:
              $stringSQL="insert into ".$dbname.".keu_jurnaldt(";
              foreach ($header as $ki=> $val){
                  if($ki==0)
                     $stringSQL.=$val;
                  else
                     $stringSQL.=",".$val;                      
              }
              $stringSQL.=") values";
               foreach($x as $key =>$arr){
                    if($key==0){
                        continue;
                    }else{
                            foreach($arr as $ki=>$val){
                                if($ki==0){
                                    if($key==1){
                                        $stringSQL.="('".trim($val)."'";
                                    }
                                    else{
                                        $stringSQL.=",('".trim($val)."'";
                                    }
                                }else{
                                    $stringSQL.=",'".trim($val)."'";
                                }
                            }
                            $stringSQL.=")";
                    }
               }
               $stringSQL.=";";
                if(mysql_query($str)){#insert header
                        if(mysql_query($stringSQL)){#insert detail
                            echo "Uploaded";
                        }
                        else{
                            echo "Error insert detail ".mysql_error($conn).$stringSQL;
                        }
                }else{
                    echo "Error insert header ". mysql_error($conn).$str;
                }
              break;
 #===========================================================end  prevbal ======================================================             
  #==============================BEGIN MATERIAL
              case 'INV':  

              #ambil  blok
              $str="select kodebarang  from ".$dbname.".log_5masterbarang";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $barang[]=$bar->kodebarang;
              }
              #ambil kodeorganisasi
              $str="select kodeorganisasi  from ".$dbname.".organisasi";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res)){
                  $org[]=$bar->kodeorganisasi;
              }
              #periksa kelengkapan data
              $zz=0;
          foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                      $x[$key][$ids]=trim($rinc);                     
                  }
              }
          }
              foreach ($header as $ki=> $val){
                if($val=='periode'){
                    $iperiode=$ki;
                  }
                if($val=='kodeorg'){
                    $ikodeorg=$ki;
                }
                if($val=='kodebarang'){
                    $ikodebarang=$ki;
                }
                 if($val=='saldoakhirqty'){
                    $isaldoakhirqty=$ki;
                }           
                  if($val=='hargarata'){
                    $ihargarata=$ki;
                }                   
                  if($val=='kodegudang'){
                    $ikodegudang=$ki;
                }                    
              }   

  #periksa periode gudang
          $str="select periode from ".$dbname.".setup_periodeakuntansi where periode='".$x[1][$iperiode]."' and
                    kodeorg='".  $x[1][$ikodegudang]."' and tutupbuku=0";
          $res=mysql_query($str);
          if(mysql_num_rows($res)<1){
              exit("Error: Accounting period for ".$x[1][$ikodegudang]." not defined");
          }
        #periksa pt apakah terdaftar atau tidak  
          $kodept=false;
          foreach($org as $bb=>$vb){
              if($x[1][$ikodeorg]==$vb){
                  $kodept=true;
              }    
          }
          if(!$kodept){
              exit("Error : Company code not found");
          }
          #periksa kode barang
            foreach($x as $key =>$arr){
                if($key==0){
                    continue;
                }else{             
                       $arrkodemasalah[$arr[$ikodebarang]]=$arr[$ikodebarang];
                       foreach($barang as $tt =>$gh){
                           if($arr[$ikodebarang]==$gh){
                               unset($arrkodemasalah[$arr[$ikodebarang]]);
                           }                          
                    }
                    if($arr[$ihargarata]=='0'){
                        $hargamasalah[$arr[$ikodebarang]]=$arr[$ikodebarang];
                    }
                    if($arr[$isaldoakhirqty]<='0'){
                        $qtymasalah[$arr[$isaldoakhirqty]]=$arr[$isaldoakhirqty];
                    }                    
                }
            }    
            if(count($arrkodemasalah)>0){
                echo" The folowing material code not defined on material master:";
                print_r($arrkodemasalah);
                exit();
            }
            else if(count($hargamasalah)>0){
                echo" The folowing material with blank price";
                print_r($hargamasalah);
                exit();                
            }
            else if(count($qtymasalah)>0){
                echo" The folowing material with blank qty";
                print_r($qtymasalah);
                exit();                
            }            
                       
      
//              #delete first
              $str="delete from ".$dbname.".log_5saldobulanan where periode='".$x[1][$iperiode]."' and kodegudang='".$x[1][$ikodegudang]."'";
              mysql_query($str);
               $str="delete from ".$dbname.".log_5masterbarangdt where  kodegudang='".$x[1][$ikodegudang]."'";
              mysql_query($str);
              
              
#generate sql
         $stringSQL="insert into ".$dbname.".log_5saldobulanan(kodeorg,kodebarang,saldoakhirqty,hargarata,lastuser,periode,
                   nilaisaldoakhir,kodegudang,saldoawalqty,hargaratasaldoawal,nilaisaldoawal) values";
         $stringSQL1="insert into ".$dbname.".log_5masterbarangdt(kodeorg,kodebarang,saldoqty,hargalastin,
                     hargalastout,lastuser,kodegudang) values";

               foreach($x as $key =>$arr){
                    if($key==0){
                        continue;
                    }else{                  
                            if($key=='1'){
                                $stringSQL.="('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."',0,'".$arr[$iperiode]."',
                                                        '".($arr[$isaldoakhirqty]*$arr[$ihargarata])."','".$arr[$ikodegudang]."','".$arr[$isaldoakhirqty]."',
                                                            '".$arr[$ihargarata]."','".($arr[$isaldoakhirqty]*$arr[$ihargarata])."')";
                                
                                $stringSQL1.="('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."','".$arr[$ihargarata]."',0,
                                                       '".$arr[$ikodegudang]."')";
                            }else{
                                $stringSQL.=",('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."',0,'".$arr[$iperiode]."',
                                                        '".($arr[$isaldoakhirqty]*$arr[$ihargarata])."','".$arr[$ikodegudang]."','".$arr[$isaldoakhirqty]."',
                                                            '".$arr[$ihargarata]."','".($arr[$isaldoakhirqty]*$arr[$ihargarata])."')";    
                                $stringSQL1.=",('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."','".$arr[$ihargarata]."',0,
                                                       '".$arr[$ikodegudang]."')";                                
                            }
                    }
               }
               $stringSQL.=";";
               $stringSQL1.=";";               
               //exit($stringSQL."<br>".$stringSQL1);
                if(mysql_query($stringSQL)){#insert header
                        if(mysql_query($stringSQL1)){#insert detail
                            echo "Uploaded";
                        }
                        else{
                            echo "Error insert masterbarangdt ".mysql_error($conn).$stringSQL1;
                        }
                }else{
                    echo "Error insert saldobulanan ". mysql_error($conn).$stringSQL;
                }
              break;                          
           #====================================================END INV  ================================================ 
      case 'PO':  
      #ambil  supplier
      $str="select supplierid from ".$dbname.".log_5supplier";
      $res=mysql_query($str);
      while($bar=mysql_fetch_object($res)){
          $supplier[]=$bar->supplierid;
      }
      #ambil  kodeorganisasi
      $str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
      $res=mysql_query($str);
      while($bar=mysql_fetch_object($res)){
          $kodept[]=$bar->kodeorganisasi;
      }
      #ambil  kodeorganisasi
      $str="select kodebarang from ".$dbname.".log_5masterbarang";
      $res=mysql_query($str);
      while($bar=mysql_fetch_object($res)){
          $kdbarang[]=$bar->kodebarang;
      }
        foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{
                  foreach($arr as $ids =>$rinc){
                      if($header[$ids]=='kodeorg' and strlen($rinc)!=3){
                          exit("Error: some data on kodeorg not ".$x[$key][$ids]."___".$ids."__".$key." valid (line ".$key.")");
                       }
                       if($header[$ids]=='matauang'){
                              if(trim($rinc)==''){
                                exit("Error: some data on currency not valid (line ".$key.")");
                              }
                       } 
                       if($header[$ids]=='kodesupplier'){     
                            $supplierbermasalah[$rinc]=$rinc;
                            foreach($supplier as $bb=>$cc){
                                    if($cc==$rinc)
                                        unset($supplierbermasalah[$rinc]);
                             }
                        }  
                       if($header[$ids]=='kodeorg' ){
                        #periksa kodeblok yang disubmit
                        $kdptbermasalah[$rinc]=$rinc;
                        foreach($kodept as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($kdptbermasalah[$rinc]);
                        }
                      }
                      if($header[$ids]=='kodebarang'   and trim($rinc)!=''){
                            $kdbarangbermasalah[$rinc]=$rinc;
                            foreach($kdbarang as $bb=>$cc){
                                    if($cc==$rinc)
                                        unset($kdbarangbermasalah[$rinc]);
                             }
                      }
                      if($header[$ids]=='kurs'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=1;
                      }  
                      if($header[$ids]=='tanggal'){
                              $rinc=str_replace('-','',$rinc);
                              if(strlen($rinc)!=8){
                                exit("Error: some data on date not valid (line ".$key.":".$rinc.")");
                              }
                              else if(substr($rinc,0,4)<'2000'){
                                  exit("Error: date not valid (line ".$key.")");
                              }
                              
                      }

                  }
                  
              }
        }
          if(count($supplierbermasalah)>0){
              echo "The following supplier/contractor code on were not defined:<br>";
              echo"<pre>";
              print_r($supplierbermasalah);
              echo"</pre>";
              exit();
          }
          if(count($kdptbermasalah)>0){
              echo "The following company code were not defined:<br>";
              echo"<pre>";
              print_r($kdptbermasalah);
              echo"</pre>";
              exit();
          }
           if(count($kdbarangbermasalah)>0){
              echo "The following material code were not defined:<br>";
              echo"<pre>";
              print_r($kdbarangbermasalah);
              echo"</pre>";
              exit();
          }
          
        $jmhrBrs=count($x[0]);
        $jmlhRow=count($x);
        
        $aer=0;
        foreach($x[0] as $lstDt=>$lstNama){
           if($aer==0){
                $sinsHed.="insert into ".$dbname.".log_poht (`".trim($lstNama)."`";
                $aet=0;
                $nopo=$lstNama;
            }else{
                if($aer<11){
                    $sinsHed.=",`".trim($lstNama)."`";
                }else{
                    if($aet==0){
                        $sinsHed.=",`statuspo`,`stat_release`,`lokalpusat`) values ";
                        $sInsDet.="insert into ".$dbname.".log_podt (`".trim($nopo)."`,`".trim($lstNama)."`";
                    }else{
                        if($aet<4){
                            $sInsDet.=",`".trim($lstNama)."`";
                        }
                    }
                    $aet++;
                }
            }
            if($aer<11){
                $aer++;
            } 
            if($aet==4){
                  $sInsDet.=",`harganormal`,`hargasbldiskon`)  ";
            }
        }
       
        for($aerto=1;$aerto<$jmlhRow;$aerto++){
                if($nopohead!=$x[$aerto][0]){
                    $nopohead="";
                    $headUtm="";
                    $headUtm.=$sinsHed;
                    $nopohead=$x[$aerto][0];
                    $scek="select * from ".$dbname.".log_poht where nopo='".$nopohead."'";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    if(mysql_num_rows($qcek)<1){
                            $headUtm.="('".trim($x[$aerto][0])."','".trim($x[$aerto][1])."','".trim($x[$aerto][2])."','".trim($x[$aerto][3])."','".trim($x[$aerto][4])."','".trim($x[$aerto][5])."','".trim($x[$aerto][6])."','".trim($x[$aerto][7])."','".trim($x[$aerto][8])."','".trim($x[$aerto][9])."','".trim($x[$aerto][10])."','2','1','".trim($x[$aerto][15])."')";
                            //exit("error:".$headUtm."__masuk sini");
                            if(!mysql_query($headUtm)){
                                exit("error:\n".$headUtm."__l".  mysql_error());
                            }else{
                                $detData="";
                                $detData.=$sInsDet." values ";
                                $hrgdis[$aerto]=trim($x[$aerto][14]);
                                if(intval($x[$aerto][6])!=0){
                                    $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                                }
                                $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                                if(mysql_query($sDelDt)){
                                    $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                                    if(!mysql_query($detData)){
                                      exit("error:\n".$detData."__uatas".  mysql_error());
                                    }
                                }
                            }
                    }else{
                        $sdel="delete from ".$dbname.".log_poht where nopo='".$x[$aerto][0]."'";
                        if(mysql_query($sdel)){
                            $headUtm.="('".trim($x[$aerto][0])."','".trim($x[$aerto][1])."','".trim($x[$aerto][2])."','".trim($x[$aerto][3])."','".trim($x[$aerto][4])."','".trim($x[$aerto][5])."','".trim($x[$aerto][6])."','".trim($x[$aerto][7])."','".trim($x[$aerto][8])."','".trim($x[$aerto][9])."','".trim($x[$aerto][10])."','2','1','".trim($x[$aerto][15])."')";
                            if(!mysql_query($headUtm)){
                                exit("error:\n".$headUtm."__s".  mysql_error($conn));
                            }else{
                                $detData="";
                                $detData.=$sInsDet." values ";
                                $hrgdis[$aerto]=trim($x[$aerto][14]);
                                if(intval($x[$aerto][6])!=0){
                                    $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                                }
                                $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                                if(mysql_query($sDelDt)){
                                    $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                                    if(!mysql_query($detData)){
                                      exit("error:\n".$detData."__t".  mysql_error($conn));
                                    }
                                }
                            }
                        }
                    }
                }else{
                    $detData="";
                    $detData.=$sInsDet." values ";
                    $hrgdis[$aerto]=trim($x[$aerto][14]);
                    if(intval($x[$aerto][6])!=0){
                        $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                    }
                    $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                    if(mysql_query($sDelDt)){
                        $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                        if(!mysql_query($detData)){
                          exit("error:\n".$detData."__u1".  mysql_error());
                        }
                    }   
              }
                
        }
        //exit("error:masuk".$headUtm."____".$detData."__".$aet."___".$jmlhRow);
       
      break;
        #  ====================================================END PO=====================================
    #====================================================START ABSENSI================================================ 
    case 'ABSENSI': 
		$data = $x;
		
		// Validasi Kosong
		if(empty($data)) exit("Isi File Kosong");
		
		// Cek apakah ada periode yang berbeda dan pengecekan format tanggal
		$periode = substr($data[1][0],0,7);
		$listNik = array();
		foreach($data as $key=>$row) {
			if($key>0) {
				$tmpDate = explode('-',$row[0]);
				
				// Validasi Format Tanggal
				if(count($tmpDate)!=3 or strlen($tmpDate[0])!=4 or strlen($tmpDate[1])!=2 or strlen($tmpDate[2])!=2)
					exit("Format Tanggal tidak valid.<br>Format tanggal = YYYY-MM-DD");
				
				// Validasi Periode
				if($periode != substr($row[0],0,7))
					exit("Warning: Ada periode yang tidak sama ".substr($row[0],0,7));
				
				$listNik[trim($row[1])] = trim($row[1]);
			}
		}
		
		// Kode Absen
		$str2="select kodeabsen from ".$dbname.".sdm_5absensi order by kelompok asc";
		$res2=mysql_query($str2);
		while($bar=mysql_fetch_object($res2)){
			$kdaben[]=$bar->kodeabsen;
		}
		
		// Get Data Karyawan
		$str="select karyawanid,nik,lokasitugas,subbagian,tanggalkeluar from ".$dbname.".datakaryawan where nik in ('".implode("','",$listNik)."') 
			and tipekaryawan!=0 order by nik asc";
		
		/*
		$str="select karyawanid,nik,lokasitugas,subbagian from ".$dbname.".datakaryawan where nik in ('".implode("','",$listNik)."') 
			and tanggalkeluar='0000-00-00' and tipekaryawan!=0 order by nik asc";
		*/	
		//echo $str;
		$res=mysql_query($str);
		$dataNik = $dataKaryId = $dataLokasi = $dataSubbagian = array();
		$listSubbagian = $listLokasi = $listAllLokasi = array();
		while($bar=mysql_fetch_object($res)){
			
			if($bar->tanggalkeluar=='0000-00-00' || $bar->tanggalkeluar>=$row[0])
			{
				$dataNik[$bar->nik] = $bar->nik;
				$dataKaryId[$bar->nik]=$bar->karyawanid;
				$dataLokasi[$bar->nik]=$bar->lokasitugas;
				if(!empty($bar->subbagian)) {
					$dataSubbagian[$bar->nik]=$bar->subbagian;
					$listSubbagian[$bar->subbagian] = $bar->subbagian;
					$listAllLokasi[$bar->subbagian] = $bar->subbagian;
				}
				$listLokasi[$bar->lokasitugas] = $bar->lokasitugas;
				$listAllLokasi[$bar->lokasitugas] = $bar->lokasitugas;
			}
		}
		
		// Get Tipe Karyawan
		$optTipe = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',
							  "karyawanid in ('".implode("','",$dataKaryId)."')");
		
		// Cek Periode Aktif
		$scek="select distinct kodeorg,periode from ".$dbname.".setup_periodeakuntansi where
			kodeorg in ('".implode("','",$listLokasi)."') and
			tutupbuku=0 order by periode asc";
		$resCek = fetchData($scek);
		
		// Cek Periode tidak sama
		$optPeriod = array();
		foreach($resCek as $row) {
			$optPeriod[$row['kodeorg']] = $row['periode'];
		}
		foreach($optPeriod as $org=>$p) {
			if($periode!=$p)
				exit("error: Period is not the same with active period :".$p);
		}
		
		// Cek Periode tidak ada
		foreach($listLokasi as $row) {
			if(!isset($optPeriod[$row]))
				exit("Warning: ".$row." tidak ada periode aktif");
		}
		
		// Cek Data Bermasalah
		$nopoisi=0;
		$nikbermasalah = $absenbermasalah = array();
		$tanggalNow = '';
		foreach($x as $key =>$arr){
			if($key==0){
				continue;
			}else{
				foreach($arr as $ids =>$rinc){
                    if($ids==0) $x[$key][0] = tanggalsystemn($rinc);
					if($nopoisi!=1){
                        $nopoisi=1;
                        if($header[0]=='tanggal'){
                            $rinc=str_replace('-','',$x[$key][0]);
							if(strlen($rinc)!=8){
								exit("Error: some data on date not valid (line ".$key."__".$rinc.")");
							} else if(substr($rinc,0,4)<'2000') {
								exit("Error: date not valid (line ".$key.")");
							}
                        }
                    }
					
					if(isset($header[1]) and $header[1]=='nik') {
						$rinc=$x[$key][1];
						$nikbermasalah[$rinc]=$rinc;
						foreach($dataKaryId as $bb=>$cc) {
							if($bb==$rinc) unset($nikbermasalah[$rinc]);
                        }
                    }
					
                    if(isset($header[3]) and $header[3]=='absensi') {
                        $rinc=$x[$key][3];
						$absenbermasalah[$rinc]=$rinc;
						foreach($kdaben as $bb=>$cc){
                            if($cc==$rinc) unset($absenbermasalah[$rinc]);
                        }
                    }
				}
			}//else
		}//foreach
		$bermasalah=false;
		if(count($nikbermasalah)>0){
			echo "Warning - The following nik on were not defined:<br>";
			echo"<pre>";
			print_r($nikbermasalah);
			echo"</pre>";
			$bermasalah=true;
		}
		if(count($absenbermasalah)>0){
			echo "Warning - The following absence on were not defined:<br>";
			echo"<pre>";
			print_r($absenbermasalah);
			echo"</pre>";
			$bermasalah=true;
		}
		if($bermasalah==true){
			exit();
		}
		
		// Get List Lokasi & Tanggal
		$lokTgl = array();
		foreach($data as $key=>$row) {
			if($key>0) {
				$tmpLok = (isset($dataSubbagian[$row[1]]))? $dataSubbagian[$row[1]]: $dataLokasi[$row[1]];
				$lokTgl[$tmpLok.$row[0]] = $tmpLok.$row[0];
			}
		}
		
		// Pool Data & Insert Header
		$dataD = array();
		foreach($data as $key=>$row) {
			if($key>0) {
				foreach($listAllLokasi as $lok) {
					if(isset($lokTgl[$lok.$row[0]])) {
						// Insert Header
						$dataH = array(
							'tanggal' => trim($row[0]),
							'kodeorg' => $lok,
							'periode' => $periode
						);
						$qIns = insertQuery($dbname,'sdm_absensiht',$dataH,
											array('tanggal','kodeorg','periode'));
						// Execute, jika gagal skip
						if(!mysql_query($qIns)) {
							if(mysql_errno()!=1062) {
								exit("Insert Header Error: ".mysql_error());
							}
						}
					}
				}
				
				$cekAbs = false;
				if($optTipe[ $dataKaryId[trim($row[1])] ] == 4) { // Jika PHL, cek BKM
					// Cek BKM Rawat
					$qRawat = selectQuery($dbname,'kebun_kehadiran_vw','*',
										  "karyawanid = '".$dataKaryId[trim($row[1])]."' and
										  tanggal = '".trim($row[0])."'");
					$resRawat = fetchData($qRawat);
					if(!empty($resRawat)) {
						$cekAbs = true;
						break;
					}
					
					// Cek BKM Panen
					$qPnn = selectQuery($dbname,'kebun_prestasi_vw','*',
										  "karyawanid = '".$dataKaryId[trim($row[1])]."' and
										  tanggal = '".trim($row[0])."'");
					$resPnn = fetchData($qPnn);
					if(!empty($resPnn)) {
						$cekAbs = true;
						break;
					}
				}
				
				// Pool Detail
				if(!$cekAbs) { // Jika Belum ada di BKM
					$org = (isset($dataSubbagian[$row[1]]))? $dataSubbagian[$row[1]]: $dataLokasi[$row[1]];
					if($dataLokasi[trim($row[1])] == substr($org,0,4)) {
						$dataD[] = array(
							'kodeorg' => $org,
							'tanggal' => trim($row[0]),
							'karyawanid' => $dataKaryId[trim($row[1])],
							'shift' => trim($row[2]),
							'absensi' => trim($row[3]),
							'jam' => trim($row[4]),
							'jamPlg' => trim($row[5]),
							'penjelasan' => trim($row[6]),
							'catu' => 0,
							'penaltykehadiran' => 0,
							'premi' => 0,
							'insentif' => 0,
							'fingerprint' => 1,
						);
					}
				}
			}
		}
		
		// Insert Detail
		foreach($dataD as $row) {
			$qIns = insertQuery($dbname,'sdm_absensidt',$row);
			if(!mysql_query($qIns)) {
				if(mysql_errno()!=1062) {
					exit("Insert Detail Error: ".mysql_error());
				}
			}
		}
		echo "Notice: Data berhasil diupload";
		break; 
      
      
      
      #====================================================START HARGA HARIAN PASAR================================================ 

      case 'HARGAHARIANPASAR':  
      
       
      $nopoisi=0;
      foreach($x as $key =>$arr){
          if($key==0){
              continue;
          }else{                 
              foreach($arr as $ids =>$rinc){
                     if($nopoisi!=1){
                          $nopoisi=1;
                         
                        if($header[2]=='tanggal')
                        {
                                  
                            if(strlen($x[$key][2])==1)
                            {
                              $x[$key][2]='0'.$x[$key][2];
                            }
                            else if(strlen($x[$key][2])==2)
                            {
                                $x[$key][2]=$x[$key][2];
                            }      
                           
                        }
                          
                     }
                       
              }
         }//else
      }//foreach  
      
	
	  
      $jmlhRow=count($x);
	  
	   
      $key=1;
       for($ind=1;$ind<$jmlhRow;$ind++){
          $w="insert into ".$dbname.".pmn_hargapasar (tanggal,kodeproduk,pasar,satuan,harga,matauang,statusharga,ffa,mni) values";
          $w.=" ('".$x[$ind][0].'-'.$x[$ind][1].'-'.$x[$ind][2]."','".$x[$ind][3]."','".$x[$ind][4]."','".$x[$ind][5]."',";
          $w.="'".trim($x[$ind][6])."','".trim($x[$ind][7])."','".trim($x[$ind][8])."','".trim($x[$ind][9])."','".trim($x[$ind][10])."')";
          
		  
		  
          if(!mysql_query($w)){
              exit("error: gagal".$w);
          }
		  
      }
      echo "Notice: Data berhasil diupload";
      break; 
   
    
            default:
            break;
      }
   
}
?>
