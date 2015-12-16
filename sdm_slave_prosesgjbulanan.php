<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;
$namakar=Array();

$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
      $tipeOrg=$optTipe[$param['kodeorg']];


    $str="select * from ".$dbname.".bgt_regional_assignment 
        where kodeunit LIKE '".$param['kodeorg']."%'
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $regional=$bar->regional;
        $unit=$bar->kodeunit;
    }

#cek tutup atau belum periode gaji
$sCekPeriode="select distinct * from ".$dbname.".sdm_5periodegaji where periode='".$param['periodegaji']."' 
              and kodeorg='".$param['kodeorg']."' and sudahproses=1 and jenisgaji='B'";

$qCekPeriode=mysql_query($sCekPeriode) or die(mysql_error($conn));
if(mysql_num_rows($qCekPeriode)>0)
    $aktif2=false;
       else
     $aktif2=true;
  if(!$aktif2)
  {
      exit(" Payroll period has been closed");
  }
#periksa apakah sudah tutup buku

       $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$param['periodegaji']."' and 
             kodeorg='".$param['kodeorg']."' and tutupbuku=1";
       $res=mysql_query($str);
       if(mysql_num_rows($res)>0)
           $aktif=false;
       else
           $aktif=true;
  if(!$aktif)
  {
      exit("Accounting period has been closed");
  }
  
  
#periksa proses uang makan
$iCek="select * from ".$dbname.".sdm_premi where kodeorg='".$param['kodeorg']."' "
        . " and jenis='UANGMAKAN' and periode='".$param['periodegaji']."' "
        . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where sistemgaji='Bulanan') ";



$nCek=  mysql_query($iCek) or die (mysql_errno($conn));
if(mysql_num_rows($nCek)<1)
           $aktif=false;
       else
           $aktif=true;
  if(!$aktif)
  {
      exit("Uang makan untuk karyawan Bulanan belum di input");
  }
  
  
# Get Period Range
$qPeriod = selectQuery($dbname,'sdm_5periodegaji','tanggalmulai,tanggalsampai',
    "periode='".$param['periodegaji']."' and kodeorg='".
    $param['kodeorg']."' and jenisgaji='B'");
$resPeriod = fetchData($qPeriod);
$tanggal1 = $resPeriod[0]['tanggalmulai'];
$tanggal2 = $resPeriod[0]['tanggalsampai'];

#2. Get Karyawan bulanan yang penggajian=bulanan dan alokasi=0
$query1 = selectQuery($dbname,'datakaryawan','karyawanid,namakaryawan,statuspajak,npwp,jms,bpjs',"tipekaryawan in(1,2,6) and ".
    "lokasitugas='".$param['kodeorg']."' and ".
    "(tanggalkeluar>='".$tanggal1."' or tanggalkeluar='0000-00-00') and alokasi=0 and sistemgaji='Bulanan'".
     " and ( tanggalmasuk<='".$tanggal2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)");
$absRes = fetchData($query1);
# Error empty karyawan
if(empty($absRes)) {
    echo "Error : There is no prsence(kehadiran) on this period";
    exit();
}
else
{
    $id=Array();
    foreach($absRes as $row => $kar)
    {
      $id[$kar['karyawanid']][]=$kar['karyawanid'];
      $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
      //$nojms[$kar['karyawanid']]=trim($kar['jms']);
      //$nobpjs[$kar['karyawanid']]=trim($kar['bpjs']);
      
      $bpjstenaga[$kar['karyawanid']]=trim($kar['jms']);
      $bpjskes[$kar['karyawanid']]=trim($kar['bpjs']);
      
      $kamusKar[$kar['karyawanid']]['status']=$kar['statuspajak'];
      $kamusKar[$kar['karyawanid']]['npwp']=str_replace(" ","",str_replace(".","",$kar['npwp']));
        if (!is_numeric($kamusKar[$kar['karyawanid']]['npwp'])) {
            $kamusKar[$kar['karyawanid']]['npwp']='';
        }
        else if(intval($kamusKar[$kar['karyawanid']]['npwp'])>0 and strlen(intval($kamusKar[$kar['karyawanid']]['npwp'])>12))
        {
            
        }
        else
        {
           $kamusKar[$kar['karyawanid']]['npwp']=$kar['npwp']; 
        }   
      
      
    }  
}
#1ambil semua komponen dari gajipokok=====================
    $str1 = "select a.*,b.namakaryawan,b.tipekaryawan from ".$dbname.".sdm_5gajipokok a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where a.tahun=".substr($tanggal1,0,4)." and b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan'";
    $res1 = fetchData($str1);
#2 Get Jamsostek porsi==========================
    $query6 = selectQuery($dbname,'sdm_ho_hr_jms_porsi','value',"id='karyawan'");
    $jmsRes = fetchData($query6);
    $persenJms = $jmsRes[0]['value']/100;
        $tjms=Array();   
        $tipekaryawan=Array();
        foreach($res1 as $idx => $val)
        {
          if($id[$val['karyawanid']][0]==$val['karyawanid'])
          {
              if($val['tipekaryawan']=='2')
                 $tipekaryawan[$val['karyawanid']]='Kontrak';
               else  if($val['tipekaryawan']=='1')
                 $tipekaryawan[$val['karyawanid']]='KBL';
                else 
                 $tipekaryawan[$val['karyawanid']]='Kontrak Karya';
                
             #add to ready data================================================
              $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$val['karyawanid'],
                'idkomponen'=>$val['idkomponen'],
                'jumlah'=>$val['jumlah'],
                'pengali'=>1);
             if($val['idkomponen']==1 or $val['idkomponen']==2 or $val['idkomponen']==30 or $val['idkomponen']==31)
             { #ambil,
               #tunjangan jabatan
               #tunjangan masakerja
               #tunjangan Provesi
               #gaji pokok
                  if($nojms[$val['karyawanid']]!=''){#jika No. JMS diisi maka ada potongan jamsostek
                      setIt($tjms[$val['karyawanid']],0);
					  $tjms[$val['karyawanid']]+=$val['jumlah']; 
//                    echo "<pre>";
//                    print_r($val['karyawanid']."=>".$tjms[$val['karyawanid']]);
//                    echo "<pre>";
                  }
             }
          }
          #bentuk BPJS di sini
            if($val['idkomponen']==1)
            {   
                $gapok[$val['karyawanid']]=$val['jumlah'];
            }
            
            
            
        }
        
        
        /*#masukin BPJS ke sini
        foreach($bpjs as $key=>$nilai){
                #add bpjs to ready data====================================
                $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$key,
                'idkomponen'=>44,   
                'jumlah'=>($nilai/100),
                'pengali'=>1);  

        } */  
        
        
        
        /*foreach($tjms as $key=>$nilai){
                 #add jamsostek to ready data====================================
            if($tipekaryawan[$key]=='KBL' or $tipekaryawan[$key]=='Kontrak'){
                 $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$key,
                'idkomponen'=>3,   
                'jumlah'=>($nilai* $persenJms),
                'pengali'=>1);  
            }
            
        }*/
        
#3. Get Lembur Data
    $where2 = " a.kodeorg like '".$param['kodeorg']."%' and (tanggal>='".
        $tanggal1."' and tanggal<='".$tanggal2."')";
      $query2="select a.karyawanid,sum(a.uangkelebihanjam) as lembur from ".$dbname.".sdm_lemburdt a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan'                  
               and ".$where2." group by a.karyawanid";
    $lbrRes = fetchData($query2); 
    foreach($lbrRes as $idx=>$row) {  
          if(isset ($id[$row['karyawanid']]))
          {
                $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$row['karyawanid'],
                'idkomponen'=>33,   
                'jumlah'=>$row['lembur'],
                'pengali'=>1); 
          }
          else
          {
            //abaikan jika tidak terdaftar pada karyawanid  
          }   
    }

#4. Get Potongan Data============================================================
    $where3 = " kodeorg='".$param['kodeorg']."' and periodegaji='".
        $param['periodegaji']."'";
    //$query3 = selectQuery($dbname,'sdm_potongandt','nik,sum(jumlahpotongan) as potongan',$where3)." group by nik";
    $query3="select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from ".$dbname.".sdm_potongandt a left join 
              ".$dbname.".datakaryawan b on a.nik=b.karyawanid
               where b.tipekaryawan in('2','1','6') and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan'                   
               and ".$where3." group by a.nik,a.tipepotongan";
    $potRes = fetchData($query3);
    foreach($potRes as $idx=>$row) {  
          if(isset ($id[$row['karyawanid']]))
          {
                $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$row['karyawanid'],
                'idkomponen'=>$row['tipepotongan'],   
                'jumlah'=>$row['potongan'],
                'pengali'=>1); 
          }
          else
          {
            //abaikan jika tidak terdaftar pada karyawanid  
          }   
    }   

#5. Get Angsuran Data==========================================================
    $where4 = " start<='".$param['periodegaji']."' and end>='".$param['periodegaji']."'";
    //$query4 = selectQuery($dbname,'sdm_angsuran','karyawanid,bulanan,jenis',$where4)." group by karyawanid";
    $query4="select a.karyawanid,a.bulanan,a.jenis from ".$dbname.".sdm_angsuran a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.active=1                      
               and sistemgaji='Bulanan'                    
               and ".$where4;

    
    $angRes = fetchData($query4);
    foreach($angRes as $idx=>$row) { 
         if($id[$row['karyawanid']][0]==$row['karyawanid'])
          {

             #add to ready data================================================
              $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$row['karyawanid'],
                'idkomponen'=>$row['jenis'],
                'jumlah'=>$row['bulanan'],
                'pengali'=>1);
          }
    }
#6 Premi dan penalty =======================================================================
    #6.0 periksa posting transaksi
    #posting perawatan
    $stru1="select distinct(tanggal) from ".$dbname.".kebun_kehadiran_vw a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '".$param['kodeorg']."%' and a.jurnal=0 
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'    
               and sistemgaji='Bulanan' order by tanggal";
    $resu1 = mysql_query($stru1); 
    #posting panen
    $stru2="select distinct(tanggal) from ".$dbname.".kebun_prestasi_vw a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '".$param['kodeorg']."%' and a.jurnal=0
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
               and sistemgaji='Bulanan' order by tanggal";
   $resu2 = mysql_query($stru2); 
   #posting traksi
   $stru3="select distinct(tanggal)
           from ".$dbname.".vhc_runhk_vw a left join 
          ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
           where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
           and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
           and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
           and posting=0 and sistemgaji='Bulanan' order by tanggal";
   $resu3 = mysql_query($stru3);
   if(mysql_num_rows($resu1)>0 or mysql_num_rows($resu2)>0 or mysql_num_rows($resu3)>0)
   {
       echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
       echo"<table class=sortable border=0 cellspacing=1>
            <thead><tr class=rowheader>
            <td>".$_SESSION['lang']['jenis']."</td>
            <td>".$_SESSION['lang']['tanggal']."</td>
            </tr></thead><tbody>";
       while($bar=mysql_fetch_object($resu1))
       {
           echo"<tr class=rowcontent><td>Perawatan Kebun</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
       }
       while($bar=mysql_fetch_object($resu2))
       {
           echo"<tr class=rowcontent><td>Panen</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
       }
       while($bar=mysql_fetch_object($resu3))
       {
           echo"<tr class=rowcontent><td>Traksi Pekerjaan</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
       }
       echo "</tbody><tfoot></tfoot></table>";
       exit();//keluar dari proses
   }
   

    #6.3.1 Get Premi Kegiatan Perawatan
        $premi=Array();
        $penalty=Array();
        $penaltykehadiran=Array();
        $query5="select a.karyawanid,sum(a.insentif) as premi from ".$dbname.".kebun_kehadiran_vw a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '".$param['kodeorg']."%'
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
               and sistemgaji='Bulanan'                    
               group by a.karyawanid";
        $premRes = fetchData($query5);  
        foreach($premRes as $idx => $val)
        {
          if($val['premi']>0)
            $premi[$val['karyawanid']]=$val['premi'];
        }  
    #6.3.2 Get Premi Kegiatan Panen    
         $query6="select a.tanggal, a.karyawanid,sum(a.upahpremi+a.premibasis) as premi,sum(a.rupiahpenalty+a.upahpenalty) as penalty 
               from ".$dbname.".kebun_prestasi_vw a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '".$param['kodeorg']."%'  
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
               and sistemgaji='Bulanan'                    
               group by a.tanggal, a.karyawanid";
        $premRes1 = fetchData($query6); 
         foreach($premRes1 as $idx => $val)
        {
            // cari hari
            $day = date('D', strtotime($val['tanggal']));
            if($day=='Sun')$libur=true; else $libur=false;
            // kamus hari libur
            $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$val['tanggal']."'";
            $queorg=mysql_query($strorg) or die(mysql_error());
            while($roworg=mysql_fetch_assoc($queorg))
            {
//                $libur=true;
            if($roworg['keterangan']=='libur')$libur=true;
            if($roworg['keterangan']=='masuk')$libur=false;
            }        

             if($val['premi']>0)
             { 
                 if(isset ($premi[$val['karyawanid']]))
                     $premi[$val['karyawanid']]+=$val['premi'];
                 else
                     $premi[$val['karyawanid']]=$val['premi']; 
             }
             if($val['penalty']>0)    //$penalty[$val['karyawanid']]=$val['penalty'];
			 {
                 if(isset ($penalty[$val['karyawanid']]))
                     $penalty[$val['karyawanid']]+=$val['penalty'];
                 else
                     $penalty[$val['karyawanid']]=$val['penalty'];
             }
             
            // kalo hari kerja itung biasa
            if($libur==false){
                
            }else{// kalo hari libur dianggap kontanan? (masuk ke pengurang)
                if($val['premi']>0)
                { 
                    if(isset ($premikontanan[$val['karyawanid']]))
                    $premikontanan[$val['karyawanid']]+=$val['premi'];
                    else
                    $premikontanan[$val['karyawanid']]=$val['premi']; 
                }
                if($val['penalty']>0)//$penalty[$val['karyawanid']]=$val['penalty'];
				{
                    if(isset ($premikontanan[$val['karyawanid']]))
                    $premikontanan[$val['karyawanid']]-=$val['penalty'];
                    else
                    $premikontanan[$val['karyawanid']]=$val['penalty'];
                }
            }
             
        }         
     #6.3.3 Get Premi Transport
        $query7="select a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty 
               from ".$dbname.".vhc_runhk_vw a left join 
              ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and substr(a.notransaksi,1,4)='".$param['kodeorg']."'  
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
               and sistemgaji='Bulanan'                    
               group by a.idkaryawan";
        $premRes2 = fetchData($query7); 
         foreach($premRes2 as $idx => $val)
        {
             if($val['premi']>0)
             {   
                 if(isset ($premi[$val['karyawanid']]))
                     $premi[$val['karyawanid']]+=$val['premi'];
                 else
                     $premi[$val['karyawanid']]=$val['premi'];
             }
              if($val['penalty']>0)
             {              
                 if(isset ($penalty[$val['karyawanid']]))
                     $penalty[$val['karyawanid']]+=$val['penalty'];
                 else
                     $penalty[$val['karyawanid']]=$val['penalty'];   
             }
        }  
#6.3.4 Get Premi Kemandoran
        $query8="select sum(a.premiinput) as premi,a.karyawanid
               from ".$dbname.".kebun_premikemandoran a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg='".$param['kodeorg']."'  
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
               and b.sistemgaji='Bulanan'  and a.posting=1                   
               group by a.karyawanid";
        $premRes2 = fetchData($query8); 
         foreach($premRes2 as $idx => $val)
        {
             if($val['premi']>0)
             {   
                 if(isset ($premi[$val['karyawanid']]))
                     $premi[$val['karyawanid']]+=$val['premi'];
                 else
                     $premi[$val['karyawanid']]=$val['premi'];
             }
        }  
          #premi tetap dari absensi==========================================
            $stkh="select a.karyawanid,sum(a.premi+a.insentif) as premi from ".$dbname.".sdm_absensidt a 
                left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                          where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
            $reskh=mysql_query($stkh);
            while($barky=mysql_fetch_object($reskh)){
                 if(isset ($premi[$barky->karyawanid]))
                     $premi[$barky->karyawanid]+=$barky->premi;
                 else
                     $premi[$barky->karyawanid]=$barky->premi;
            }
       #end premi tetap dari absensi========================================== 
        
         foreach($premi as $idx=>$row) { 
           #add to ready data================================================
             if($row>0) {
                 $readyData[] = array(
                    'kodeorg'=>$param['kodeorg'],
                    'periodegaji'=>$param['periodegaji'],
                    'karyawanid'=>$idx,
                    'idkomponen'=>32,
                    'jumlah'=>$row,
                    'pengali'=>1);
                 }
             }    
       if(!empty($premikontanan))foreach($premikontanan as $idx=>$row) { 
           #add to ready data================================================
             if($row>0) {
                 $readyData[] = array(
                    'kodeorg'=>$param['kodeorg'],
                    'periodegaji'=>$param['periodegaji'],
                    'karyawanid'=>$idx,
                    'idkomponen'=>43,
                    'jumlah'=>$row,
                    'pengali'=>1);
                 }
             }                   
         foreach($penalty as $idx=>$row) { 
           #add to ready data================================================
             if($row>0) {             
              $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$idx,
                'idkomponen'=>34,
                'jumlah'=>$row,
                'pengali'=>1);
             }
             } 
           #penalty kehadiran dari absensi
            $stkh="select a.karyawanid,sum(a.penaltykehadiran) as penaltykehadiran from ".$dbname.".sdm_absensidt a 
                left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                          where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
               and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
            $reskh=mysql_query($stkh);
            while($barkh=mysql_fetch_object($reskh)){
                  if($barkh->penaltykehadiran>0)
                     $penaltykehadiran[$barkh->karyawanid]=$barkh->penaltykehadiran;
            }
         foreach($penaltykehadiran as $idx=>$row) { 
           #add to ready data================================================
             if($row>0) {             
              $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$idx,
                'idkomponen'=>41,
                'jumlah'=>$row,
                'pengali'=>1);
             }
             } 
             

     
#7. Uang Makan : sdm_premi
   
    $iMakan="select a.karyawanid,a.premi from ".$dbname.".sdm_premi a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan'  and   jenis='UANGMAKAN'  and periode='".$param['periodegaji']."'  group by a.karyawanid";
    $nMakan = fetchData($iMakan); 
    foreach($nMakan as $idx=>$row) {  
          if(isset ($id[$row['karyawanid']]))
          {
                $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$row['karyawanid'],
                'idkomponen'=>45,   
                'jumlah'=>$row['premi'],
                'pengali'=>1); 
          }
          else
          {
            //abaikan jika tidak terdaftar pada karyawanid  
          }   
    }           
    
    
    ##tj absensi: sdm_premi
    $iTjabsen="select a.karyawanid,a.premi from ".$dbname.".sdm_premi a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan' and jenis='TJABSEN' and periode='".$param['periodegaji']."'  group by a.karyawanid";
    $nTjabsen = fetchData($iTjabsen); 
    foreach($nTjabsen as $idx=>$row) {  
          if(isset ($id[$row['karyawanid']]))
          {
                $readyData[] = array(
                'kodeorg'=>$param['kodeorg'],
                'periodegaji'=>$param['periodegaji'],
                'karyawanid'=>$row['karyawanid'],
                'idkomponen'=>56,   
                'jumlah'=>$row['premi'],
                'pengali'=>1); 
          }
          else
          {
            //abaikan jika tidak terdaftar pada karyawanid  
          }   
    } 
    
    
   ## premi tetap : sdm_premi
    $iPremitetap="select a.karyawanid,a.premi from ".$dbname.".sdm_premi a left join 
              ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
               and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Bulanan' and jenis='PREMITETAP'  and periode='".$param['periodegaji']."'  group by a.karyawanid";
    $nPremitetap = fetchData($iPremitetap); 
    foreach($nPremitetap as $idx=>$row) {  
        if(isset ($id[$row['karyawanid']]))
        {
              $readyData[] = array(
              'kodeorg'=>$param['kodeorg'],
              'periodegaji'=>$param['periodegaji'],
              'karyawanid'=>$row['karyawanid'],
              'idkomponen'=>40,   
              'jumlah'=>$row['premi'],
              'pengali'=>1); 
        }
        else
        {
          //abaikan jika tidak terdaftar pada karyawanid  
        }   
    }
    
      
    
        $i="select * from ".$dbname.".sdm_pendapatanlaindt where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."' "
             . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='1' and"
             . " (tanggalkeluar>='".$tanggal1."' or tanggalkeluar='0000-00-00') and alokasi=0 and sistemgaji='Bulanan' and"
             . " (tanggalmasuk<='".$tanggal2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) ) ";        
        $n = fetchData($i);
      foreach($n as $idx => $val){
             $readyData[] = array(
             'kodeorg'=>$param['kodeorg'],
             'periodegaji'=>$param['periodegaji'],
             'karyawanid'=>$val['karyawanid'],
             'idkomponen'=>$val['idkomponen'],
             'jumlah'=>$val['jumlah'],
             'pengali'=>1);
 }

    
    if($tipeOrg=='PABRIK')
    { 
        $bpjsOrg='PABRIK';
    } 
    else 
    {
        $bpjsOrg='KEBUN';
    }
      
    
    $iKerja="select * from ".$dbname.".sdm_5bpjs where lokasibpjs='".$bpjsOrg."' and jenisbpjs='ketanagakerjaan' ";
    $nKerja=  mysql_query($iKerja) or die (mysql_error($conn));
    $dKerja=  mysql_fetch_assoc($nKerja);
        $bpjsKerja=$dKerja['bebankaryawan'];
        
        

    $iSehat="select * from ".$dbname.".sdm_5bpjs where lokasibpjs='".$bpjsOrg."' and jenisbpjs='kesehatan' ";
    $nSehat=  mysql_query($iSehat) or die (mysql_error($conn));
    $dSehat=  mysql_fetch_assoc($nSehat);
        $bpjsSehat=$dSehat['bebankaryawan'];
            
       // echo $bpjsKerja._.$bpjsSehat;
        
        
      #bpjstenaga
      //$bpjstenaga[$kar['karyawanid']]=trim($kar['jms']);
      
      #bpjskes
      //$bpjskes[$kar['karyawanid']]=trim($kar['bpjs']);
      
      #
      //$umrbulanan[$val['karyawanid']]=$val['umrbulanan'];
      
    /*echo"<pre>";
    print_r($gapok);
    echo"</pre>";    
        */
    #masukin BPJS Tenaga Kerja ke sini
    foreach($gapok as $key=>$nilai){
          if($bpjstenaga[$key]!='')
          {
            $readyData[] = array(
            'kodeorg'=>$param['kodeorg'],
            'periodegaji'=>$param['periodegaji'],
            'karyawanid'=>$key,
            'idkomponen'=>3,   
            'jumlah'=>($bpjsKerja/100*$nilai),
            'pengali'=>1);  
          }
    } 
    
    
    #masukin BPJS kesehatan ke sini
    foreach($gapok as $key=>$nilai){
          if($bpjskes[$key]!='')
          {
            $readyData[] = array(
            'kodeorg'=>$param['kodeorg'],
            'periodegaji'=>$param['periodegaji'],
            'karyawanid'=>$key,
            'idkomponen'=>44,   
            'jumlah'=>($bpjsSehat/100*$nilai),
            'pengali'=>1);  
          }
    }    
             
 //calculate to component
       $strx="select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp 
              FROM ".$dbname.".sdm_ho_component";
       $comRes = fetchData($strx); 
       $comp=Array();
       $nakomp=Array();
       foreach($comRes as $idx=>$row){
          $comp[$row['komponen']]=$row['pengali'];
          $nakomp[$row['komponen']]=$row['nakomp'];
       }       
       
       
   //=tampilan  ============================
           $listbutton="<button class=mybuttton name=postBtn id=postBtn onclick=post()>Proses</button>"; 
           $list0 ="<table class=sortable border=0 cellspacing=1>
                     <thead>
                     <tr class=rowheader>";
            $list0 .= "<td>".$_SESSION['lang']['nomor']."</td>";
            $list0 .= "<td>".$_SESSION['lang']['periodegaji']."</td>";
            $list0 .= "<td>".$_SESSION['lang']['karyawanid']."</td>";
            $list0.= "<td>".$_SESSION['lang']['jumlah']."</td>";
//            $list0.= "<td>PPh21</td>";
            $list0.="</tr></thead><tbody>";
            
//periksa gaji minus
    $negatif=false; 
    $list1='';
     $listx = "Masih ada gaji dibawah 0:";    
    $list2='';
    $list3='';
    $no=0;
    //ambil premi pengawas di sdm_gaji
 /*  $strsl="select karyawanid,jumlah from ".$dbname.".sdm_gaji where periodegaji='".$param['periodegaji']."'
         and kodeorg like '".$param['kodeorg']."%' and idkomponen=16";
    $slRes = fetchData($strsl); 
   foreach($slRes as $key=>$val)
   {
       $premPengawas[$val['karyawanid']]=$val['jumlah'];
   }*/

   if($readyData<1)
   {
       exit("Error:Data Kosong");
   }
    //print_r($readyData);exit("Error");
   
       foreach($id as $key=>$val){
           $sisa[$val[0]]=0;
           foreach($readyData as $dat=>$bar){
              if($val[0]==$bar['karyawanid'])
              {
                  $sisa[$val[0]]+=$bar['jumlah']*$comp[$bar['idkomponen']]; 
                  
                  // tambahan pph21
                    /*if (in_array($bar['idkomponen'], $komponenkenapajak)){
						setIt($gajikenapajak[$val[0]],0);
                        $gajikenapajak[$val[0]]+=$bar['jumlah']*$comp[$bar['idkomponen']];
                    }
                    if($bar['idkomponen']=='1'){
                        $gapok[$val[0]]=$bar['jumlah'];
                        $dJMS[$val[0]]=$bar['jumlah']*$plusJMS/100;
                    }*/
                  // endof tambahan pph21  
                     
                    
              }  
              continue;
           }
		   
                        
           if($sisa[$val[0]]<0)
           {
                $list1 .="<tr class=rowcontent>";
                $list1 .= "<td>-</td>";
                $list1 .= "<td>".$param['periodegaji']."</td>";
                $list1 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
                $list1 .= "<td>".number_format($sisa[$val[0]],0,',','.')."</td>";
//                $list1 .= "<td>".number_format($pph21[$val[0]],0,',','.')."</td>";
                $list1 .= "</tr>";                
                $negatif=true;                
           } 
           else
           {
               $no+=1; 
                $list2 .="<tr class=rowcontent>";
                $list2 .= "<td>".$no."</td>";
                $list2 .= "<td>".$param['periodegaji']."</td>";
                $list2 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
                $list2 .= "<td align=right>".number_format($sisa[$val[0]],0,',','.')."</td>";
//                $list2 .= "<td align=right>".number_format($pph21[$val[0]],0,',','.')."</td>";
                $list2 .= "</tr>";  
           }    
       }
     $list3="</tbody><table>";   
     
//echo "<pre>";     
//print_r($kamusKar);
//echo "</pre>";
     
switch($proses) {
    case 'list':
         if($negatif)
             echo $listx.$list0.$list1.$list3;
         else
             echo $listbutton.$list0.$list2.$list3;
         break;
    case 'post':
        # Insert All ready data
        
        #delete dulu baru insert
        $sdel="delete from ".$dbname.".sdm_gaji "
           . " where idkomponen not in ('28','46','47') "
            . " and periodegaji='".$param['periodegaji']."' and kodeorg='".$param['kodeorg']."' "
           . " and karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan "
            . " where sistemgaji='Bulanan' and lokasitugas='".$param['kodeorg']."')";
        //exit("Error:$sdel");
        mysql_query($sdel) or die(mysql_error($conn));
        
        
        $insError = "";
        foreach($readyData as $row) {
            if($row['jumlah']==0 or $row['jumlah']=='')
            {
                continue;
            }
            else{
            $queryIns = insertQuery($dbname,'sdm_gaji',$row);
//            exit("error: ".$queryIns);
            if(!mysql_query($queryIns)) {
                $queryUpd = updateQuery($dbname,'sdm_gaji',$row,
                    "kodeorg='".$row['kodeorg'].
                    "' and periodegaji='".$row['periodegaji'].
                    "' and karyawanid='".$row['karyawanid'].
                    "' and idkomponen=".$row['idkomponen']);
                $tmpErr = mysql_error();
                if(!mysql_query($queryUpd)) {
                    echo "DB Insert Error :".$tmpErr."\n";
                    echo "DB Update Error :".mysql_error()."\n";
                }
            }
            }  
        }
        break;
    default:
        break;
}