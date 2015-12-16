<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$param = $_POST;
$proses = $_POST['proses'];

    $str="select * from ".$dbname.".bgt_regional_assignment 
        where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $regional=$bar->regional;
    }
 
switch($proses) {
    # Daftar Header
    case 'loadData':
	$where = "afdeling in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%')";
        
	$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%><thead><tr align=center>";
//        $tab.="<td>".$_SESSION['lang']['mandor']."</td>";
        $tab.="<td>".$_SESSION['lang']['afdeling']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td>".$_SESSION['lang']['section']."</td>";
        $tab.="<td>".$_SESSION['lang']['hasisa']."</td>";
        $tab.="<td>".$_SESSION['lang']['haesok']."</td>";
        $tab.="<td>".$_SESSION['lang']['jmlhpokok']."</td>";
        $tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody>";
        $limit=50;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        if(isset($_POST['page2']) and $_POST['page2']!=''){
         $page=$_POST['page2']-1;   
        }
        $offset=$page*$limit;
        
//        $sdata="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan 
//            where lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and tipekaryawan!='4' order by namakaryawan asc";
//        //echo $sdata;
//        $qdata=mysql_query($sdata) or die(mysql_error($conn));
//        while($rdata=  mysql_fetch_assoc($qdata)){
//            $kamuskaryawan[$rdata['karyawanid']]=$rdata['namakaryawan'];            
//        }
        
        $sdata="select distinct * from ".$dbname.".kebun_taksasi where ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
        //echo $sdata;
        $qdata=mysql_query($sdata) or die(mysql_error($conn));
        while($rdata=  mysql_fetch_assoc($qdata)){
            $tab.="<tr class=rowcontent align=center>";
//            $tab.="<td>".$kamuskaryawan[$rdata['karyawanid']]."</td>";
            $tab.="<td>".$rdata['afdeling']."</td>";
            $tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
            $tab.="<td>".$rdata['blok']."</td>";
            $tab.="<td>".$rdata['seksi']."</td>";
            $tab.="<td align=right>".$rdata['hasisa']."</td>";
            $tab.="<td align=right>".$rdata['haesok']."</td>";
            $tab.="<td align=right>".$rdata['jmlhpokok']."</td>";
            $tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
            $tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
            $tab.="</tr>";
        }
        $tab.="</tbody><tfoot>";
        $tab.="<tr>";
        $tab.="<td colspan=10 align=center>";
        $tab.="<img src=\"images/skyblue/first.png\" onclick='loadData(0)' style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/prev.png\" onclick='loadData(".($page-1).")'  style='cursor:pointer'>";
        
        $spage="select distinct * from ".$dbname.".kebun_taksasi where ".$where."";
        //echo $spage;
        $qpage=mysql_query($spage) or die(mysql_error($conn));
        $rpage=mysql_num_rows($qpage);
        $tab.="<select id='pages' style='width:50px' onchange='loadData(1.1)'>";
        @$totalPage=ceil($rpage/50);
        for($starAwal=1;$starAwal<=$totalPage;$starAwal++)
        {
            $_POST['page']=='1.1'?$_POST['page']=$_POST['page2']:$_POST['page']=$_POST['page'];
            $tab.="<option value='".($starAwal-1)."' ".($starAwal==($_POST['page']+1)?'selected':'').">".$starAwal."</option>";
        }
        $tab.="</select>";
        $tab.="<img src=\"images/skyblue/next.png\" onclick='loadData(".($page<($totalPage-1)? $page+1: $totalPage-1).")'  style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/last.png\" onclick='loadData(".intval($totalPage-1).")'  style='cursor:pointer'>";
        $tab.="</td></tr></tfoot></table>";
	 
	echo $tab;
	break;
        case 'cariData':
	$where = "afdeling in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%')";
//	$where = "afdeling in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING')";
        if(!empty($param['sNoTrans'])){
            $tgl=explode("-",$param['sNoTrans']);
            $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
            $where.=" and tanggal like '%".$param['tanggal']."%'";
        }
	$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%><thead><tr align=center>";
        $tab.="<td>".$_SESSION['lang']['afdeling']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td>".$_SESSION['lang']['section']."</td>";
        $tab.="<td>".$_SESSION['lang']['hasisa']."</td>";
        $tab.="<td>".$_SESSION['lang']['haesok']."</td>";
        $tab.="<td>".$_SESSION['lang']['jmlhpokok']."</td>";
        $tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody>";
        $limit=50;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        if(isset($_POST['page2']) and ($_POST['page2']!='')){
         $page=$_POST['page2']-1;   
        }
        $offset=$page*$limit;
        $sdata="select distinct * from ".$dbname.".kebun_taksasi where ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
        //echo $sdata;
        $qdata=mysql_query($sdata) or die(mysql_error($conn));
        while($rdata=  mysql_fetch_assoc($qdata)){
            $tab.="<tr class=rowcontent align=center>";
            $tab.="<td>".$rdata['afdeling']."</td>";
            $tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
            $tab.="<td>".$rdata['blok']."</td>";
            $tab.="<td>".$rdata['seksi']."</td>";
            $tab.="<td align=right>".$rdata['hasisa']."</td>";
            $tab.="<td align=right>".$rdata['haesok']."</td>";
            $tab.="<td align=right>".$rdata['jmlhpokok']."</td>";
            $tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
            $tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
            $tab.="</tr>";
        }
        $tab.="</tbody><tfoot>";
        $tab.="<tr>";
        $tab.="<td colspan=10 align=center>";
        $tab.="<img src=\"images/skyblue/first.png\" onclick='cariData(0)' style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/prev.png\" onclick='cariData(".($page-1).")'  style='cursor:pointer'>";
        
        $spage="select distinct * from ".$dbname.".kebun_taksasi where ".$where."";
        //echo $spage;
        $qpage=mysql_query($spage) or die(mysql_error($conn));
        $rpage=mysql_num_rows($qpage);
        $tab.="<select id='pages' style='width:50px' onchange='cariData(1.1)'>";
        @$totalPage=ceil($rpage/50);
        for($starAwal=1;$starAwal<=$totalPage;$starAwal++)
        {
            $_POST['page']=='1.1'?$_POST['page']=$_POST['page2']:$_POST['page']=$_POST['page'];
            $tab.="<option value='".($starAwal-1)."' ".($starAwal==($_POST['page']+1)?'selected':'').">".$starAwal."</option>";
        }
        $tab.="</select>";
        $tab.="<img src=\"images/skyblue/next.png\" onclick='cariData(".($page<($totalPage-1)? $page+1: $totalPage-1).")'  style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/last.png\" onclick='cariData(".intval($totalPage-1).")'  style='cursor:pointer'>";
        $tab.="</td></tr></tfoot></table>";
	# Content
	$cols = "notransaksi,tanggal,kodeorg,kodetangki,kuantitas,suhu";
	echo $tab;
	break;
   case'insert':
       #var ek//$arr="##tanggal##afdeling##blok##seksi##proses##hasisa##haesok##jmlhpokok##persenbuahmatang##jjgmasak##jjgoutput##hkdigunakan##bjr";
       $param['hasisa']==''?$param['hasisa']=0:$param['hasisa']=$param['hasisa'];
       $param['haesok']==''?$param['haesok']=0:$param['haesok']=$param['haesok'];
       $param['jmlhpokok']==''?$param['jmlhpokok']=0:$param['jmlhpokok']=$param['jmlhpokok'];
       $param['persenbuahmatang']==''?$param['persenbuahmatang']=0:$param['persenbuahmatang']=$param['persenbuahmatang'];
       $param['jjgmasak']==''?$param['jjgmasak']=0:$param['jjgmasak']=$param['jjgmasak'];
       $param['jjgoutput']==''?$param['jjgoutput']=0:$param['jjgoutput']=$param['jjgoutput'];
       $param['hkdigunakan']==''?$param['hkdigunakan']=0:$param['hkdigunakan']=$param['hkdigunakan'];
       $param['bjr']==''?$param['bjr']=0:$param['bjr']=$param['bjr'];
       
       // cek luas
        $query = "SELECT luasareaproduktif
            FROM ".$dbname.".`setup_blok` a
            WHERE a.`kodeorg` = '".$param['blok']."'
            ";
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $luas=$rDetail['luasareaproduktif'];
        }    
        
        if(($param['hasisa']+$param['haesok'])>$luas){
            exit("error: Luas tidak bisa lebih dari luas blok: ".$luas." Ha.");
        }
        
       #end var
       
       $tgl=explode("-",$param['tanggal']);
       $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
       
       $scek2="select distinct * from ".$dbname.".kebun_taksasi where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
       $qcek2=mysql_query($scek2) or die(mysql_error($conn));
       $rcek2=mysql_num_rows($qcek2);
       if($rcek2!=0){
//            exit("error: Data sudah pernah diinput.");
           
            $sins="update ".$dbname.".kebun_taksasi  set `seksi`='".$param['seksi']."',
            `hasisa`='".$param['hasisa']."', `haesok`='".$param['haesok']."', `jmlhpokok`='".$param['jmlhpokok']."', 
            `persenbuahmatang`='".$param['persenbuahmatang']."',`jjgmasak`='".$param['jjgmasak']."', `jjgoutput`='".$param['jjgoutput']."', 
            `hkdigunakan`='".$param['hkdigunakan']."', `bjr`='".$param['bjr']."'   
             where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
//                echo "error:".$sins;
            if(!mysql_query($sins)){
            exit("error:".mysql_error($conn)."__".$sins);
            }
       }else{
            $scek="select distinct * from ".$dbname.".kebun_taksasi 
              where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
            //exit("error:".$scek);
            $qcek=mysql_query($scek) or die(mysql_error($conn));
            $rcek=mysql_num_rows($qcek);
            if($rcek!=0){
            exit("error:Data Sudah Ada");
            }
            $sins="insert into ".$dbname.".kebun_taksasi  
            (`afdeling`,`tanggal`, `blok`, `seksi`, `hasisa`, `haesok`, `jmlhpokok`, `persenbuahmatang`, `jjgmasak`, `jjgoutput`, `hkdigunakan`, `bjr`)
            values ('".$param['afdeling']."','".$param['tanggal']."','".$param['blok']."','".$param['seksi']."','".$param['hasisa']."','".$param['haesok']."','".$param['jmlhpokok']."','".$param['persenbuahmatang']."','".$param['jjgmasak']."','".$param['jjgoutput']."','".$param['hkdigunakan']."','".$param['bjr']."')";
            if(!mysql_query($sins)){
            exit("error:".mysql_error($conn)."__".$sins);
            }
       }

   break;
   case'getData':
    $tgl=explode("-",$param['tanggal']);
    $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
    $str="select distinct * from ".$dbname.".kebun_taksasi 
          where tanggal='".$param['tanggal']."' and 
          afdeling='".$param['afdeling']."' and blok ='".$param['blok']."'";
   //exit("error:".$str);
   $qstr=mysql_query($str) or die(mysql_error($conn));
   $rts=mysql_fetch_assoc($qstr);
   
   echo $rts['afdeling']."###".tanggalnormal($rts['tanggal'])."###".$rts['blok']."###".$rts['seksi']."###".$rts['hasisa']."###".$rts['haesok']."###".$rts['jmlhpokok']."###".$rts['persenbuahmatang']."###".$rts['jjgmasak']."###".$rts['jjgoutput']."###".$rts['hkdigunakan']."###".$rts['bjr'];
   break;
    case 'delete': 
    $tgl=explode("-",$param['tanggal']);
    $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
	$where = "tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."'  and blok='".$param['blok']."'";
	$query = "delete from `".$dbname."`.`kebun_taksasi` where ".$where;
        //exit("error:".$query);
	if(!mysql_query($query)) {
	    echo "DB Error : ".mysql_error();
	    exit;
	} 
    break;
    case'getAfd':
        $optafd="";
    $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$param['kebun']."'";
    //echo $sorg;
    //exit("error:".$sorg);
    $qorg=mysql_query($sorg) or die(mysql_error($conn));
//            $optafd.="<option value=''></option>";
    while($rorg=mysql_fetch_assoc($qorg)){
        if($param['afdeling']!=''){
            $optafd.="<option value='".$rorg['kodeorganisasi']."' ".($param['afdeling']==$rorg['kodeorganisasi']?"selected":"").">".$rorg['namaorganisasi']."</option>";
        }
        else{
            $optafd.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
        }
    }
    $sorg2="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
            where tipe='BLOK' and kodeorganisasi like '".$param['kebun']."%' 
            and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['kebun']."' and luasareaproduktif!=0)";
    $qorg2=mysql_query($sorg2) or die(mysql_error($conn));
    $optafd2="<option value=''></option>";
    while($rorg2=mysql_fetch_assoc($qorg2)){
        if(!empty($param['blok'])){
            $optafd2.="<option value='".$rorg2['kodeorganisasi']."' ".($param['blok']==$rorg2['kodeorganisasi']?"selected":"").">".$rorg2['namaorganisasi']."</option>";
        }
        else{
            $optafd2.="<option value='".$rorg2['kodeorganisasi']."'>".$rorg2['namaorganisasi']."</option>";
        }
    }
//    $sorg2="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan 
//            where lokasitugas='".$param['kebun']."' and tipekaryawan!='4' order by namakaryawan asc";
//    
//    $qorg2=mysql_query($sorg2) or die(mysql_error($conn));
//    while($rorg2=mysql_fetch_assoc($qorg2)){
//        if($param['mandor']!=''){
//            $optafd2.="<option value='".$rorg2['karyawanid']."' ".($param['mandor']==$rorg2['karyawanid']?"selected":"").">".$rorg2['namakaryawan']."</option>";
//        }
//        else{
//            $optafd2.="<option value='".$rorg2['karyawanid']."'>".$rorg2['namakaryawan']."</option>";
//        }
//    }
    echo $optafd."###".$optafd2;
    break;
    
    case'getSPH':
        $sph=0;
//    REMOVE COMMENT, PINDAHIN KE BAWAH KALO YANG DIGUNAKAN ADALAH DATA SPH BUDGET    
//    $tgl=explode("-",$param['tanggal']);
//    $tahun=$tgl[2];
//    $sorg="select tahunbudget, kodeblok, pokokthnini, hathnini  from ".$dbname.".bgt_blok where kodeblok ='".$param['blok']."' and tahunbudget ='".$tahun."'";
    $sorg="select kodeorg, jumlahpokok as pokokthnini, luasareaproduktif as hathnini from ".$dbname.".setup_blok where kodeorg ='".$param['blok']."'";
    $qorg=mysql_query($sorg) or die(mysql_error($conn));
    while($rorg=mysql_fetch_assoc($qorg)){
        $pokok=$rorg['pokokthnini'];
        $luas=$rorg['hathnini'];
    }
    @$sph=round($pokok/$luas);

        $tahuntahuntahun=substr($param['tanggal'],6,4);
        $bulanbulanbulan=substr($param['tanggal'],3,2); 
        $tanggaltanggaltanggal=substr($param['tanggal'],0,2);
        $afdelingafdelingafdeling=substr($param['blok'],0,6); 
    
//        if($bulanbulanbulan=='01'){
//            $bulanbulanbulan='12';
//            $tahuntahuntahun-=1;
//        }else{
//            $bulanbulanbulan-=1;
//        }    
    
//        // cek spb vs tiket
//        $spbbelumdiinput='';
//        $query = "SELECT a.nospb, b.tanggal
//            FROM ".$dbname.".`pabrik_timbangan` a
//            LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
//            WHERE a.`tanggal` LIKE '".$tahuntahuntahun."-".$bulanbulanbulan."%' and a.`kodeorg` = '".substr($param['blok'],0,4)."'
//                AND b.`tanggal` is NULL";
////        echo "error:".$query;
////        exit;
//        $qDetail=mysql_query($query) or die(mysql_error($conn));
//        while($rDetail=mysql_fetch_assoc($qDetail))
//        {
//            $spbbelumdiinput.=$rDetail['nospb'].', ';
//        }        
//        if($spbbelumdiinput!=''){
//            $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
//            echo "WARNING: Ada SPB bulan lalu yang belum diinput: ".$spbbelumdiinput;
//            exit;
//        }
//
//        $spbbelumdiposting='';
//        $query = "SELECT nospb, tanggal
//            FROM ".$dbname.".`kebun_spb_vw`
//            WHERE `tanggal` LIKE '".$tahuntahuntahun."-".$bulanbulanbulan."%' and `blok` like '".substr($param['blok'],0,4)."%'
//                and posting = 0
//                ";
//        $qDetail=mysql_query($query) or die(mysql_error($conn));
//        while($rDetail=mysql_fetch_assoc($qDetail))
//        {
//            $spbbelumdiposting.=$rDetail['nospb'].', ';
//        }        
//        if($spbbelumdiposting!=''){
//            $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
//            echo "WARNING: Ada SPB bulan lalu yang belum diposting: ".$spbbelumdiposting;
//            exit;
//        }                
//
//        // ambil bjr budget
//        $query = "SELECT a.kodeblok, a.thntnm, b.bjr
//            FROM ".$dbname.".`bgt_blok` a
//            LEFT JOIN ".$dbname.".bgt_bjr b ON a.tahunbudget = b.tahunbudget
//                AND substr( a.kodeblok, 1, 4 ) = b.kodeorg
//                AND a.thntnm = b.thntanam
//            WHERE a.`tahunbudget` =".$tahuntahuntahun."
//                AND a.`kodeblok` LIKE '".$param['kodeorg']."'";
//	$res = fetchData($query);
//	if(!empty($res)) {
//            $bjr=$res[0]['bjr'];
//	}

        // cek bjr via SETUP
        $query = "SELECT *
            FROM ".$dbname.".`kebun_5bjr` a
            WHERE a.`tahunproduksi` = '".$tahuntahuntahun."' and a.`kodeorg` = '".$param['blok']."'
            ";
//        echo "error:".$query; exit;
        $qDetail=mysql_query($query) or die(mysql_error($conn));
        while($rDetail=mysql_fetch_assoc($qDetail))
        {
            $bjr=$rDetail['bjr'];
        }            
        
//// ambil bjr sesuaikan dengan algoritma LBM (lbm_slave_produksi_perblok.php)        
//$sProd="select distinct * from ".$dbname.".kebun_spb_bulanan_vw 
//        where blok like '".$param['blok']."' and periode = '".$tahuntahuntahun."-".$bulanbulanbulan."'
//        ";
//$qProd=mysql_query($sProd) or die(mysql_error($conn));
//while($rProd=  mysql_fetch_assoc($qProd))
//{
//    $dtKgBi=$rProd['nettotimbangan'];
//}        
//$sJjg="select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from ".$dbname.".kebun_prestasi_vw 
//       where kodeorg like '".$param['blok']."' and left(tanggal,7) = '".$tahuntahuntahun."-".$bulanbulanbulan."'
//       ";
//$qJjg=mysql_query($sJjg) or die(mysql_error($conn));
//while($rJjg=  mysql_fetch_assoc($qJjg))
//{
//    $jjgpanen=$rJjg['jjg'];
//}
//@$bjr=round($dtKgBi/$jjgpanen,2);        

        $basis=0;
        // ambil basis yang paling kecil
        
        /*
        
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr asc limit 1
            ";
	$res = fetchData($query);
	if(!empty($res)) {
            $bjrpalingkecil=$res[0]['bjr'];
	}
        // ambil basis yang paling besar
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr desc limit 1
            ";
	$res = fetchData($query);
	if(!empty($res)) {
            $bjrpalingbesar=$res[0]['bjr'];          
	}
        */
        
        $bjr2=$bjr;
        if($bjr<$bjrpalingkecil)$bjr2=$bjrpalingkecil;
        if($bjr>$bjrpalingbesar)$bjr2=$bjrpalingbesar;
        
        // ambil basis berdasarkan bjr + afdeling
        /*$query = "SELECT afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' and bjr = ".round($bjr2,2)."
            ";*/
        
        
        /*$query = "SELECT afdeling, basis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."'";*/
        $query = "SELECT afdeling, basis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling='".$_SESSION['org']['kodeorganisasi']."' ";
	$res = fetchData($query);
	if(!empty($res)) {
            $basis=$res[0]['basis'];
//            $premibasis=$res[0]['premibasis'];            
//            $premilebihbasis=$res[0]['premilebihbasis'];            
	}
        
        
      
        $hari = date('D', strtotime($tahuntahuntahun."-".$bulanbulanbulan."-".$tanggaltanggaltanggal));
        
        // kalo hari jumat basisnya 5/7
        if($hari=='Fri'){
            @$basis=5/7*$basis;
        }
        $basis=round($basis+($basis*0.1));    // BASIS UDAH DITAMBAH 10%    
        
        
      //  $basis=$res[0]['basis'];
        
//    $tanggal=$tahuntahuntahun.'-'.$bulanbulanbulan;
//    
//    echo "warning:".$tanggal;
//    exit;
    
    echo $sph."###".$basis."###".number_format($bjr,2);
    break;    
}
?>