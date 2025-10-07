<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method         =checkPostGet('method','');
$karyawanid     =checkPostGet('karyawanid','');
$tglmasuk       =checkPostGet('tglmasuk','');
$tglberhenti    =tanggalsystem(checkPostGet('tglberhenti',''));
$jenissk        =checkPostGet('jenissk','');
$masakerja      =checkPostGet('masakerjatahun','');
$gapok          =checkPostGet('gajipokok','');
$tunjangan      =checkPostGet('tunjanganjabatan','');
$tot_sblm_pajak =checkPostGet('tot_sblm_pajak','');
$kodeunit =checkPostGet('kodeunit','');
$table="";
switch($method)
{
    case 'getkodeunit':
        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
        $res=mysql_query($str);
        $bar=mysql_fetch_object($res);
        $unit=$bar->lokasitugas;
        $tglmasuk=$bar->tanggalmasuk;

        $sgapok="select distinct sum(jumlah) as jmlhgapok from ".$dbname.".sdm_5gajipokok 
                 where karyawanid='".$karyawanid."' and tahun='".date('Y')."' and idkomponen =1";
        $qgapok=mysql_query($sgapok) or die(mysql_error($conn));
        $rgapok=mysql_fetch_object($qgapok);
        $gapok=$rgapok->jmlhgapok;

        echo $unit."###".$tglmasuk."###".$gapok;
    break;

    case 'getmasakerja':
        $str="SELECT FLOOR(PERIOD_DIFF(DATE_FORMAT('".$tglberhenti."','%Y%m'),DATE_FORMAT('".$tglmasuk."','%Y%m'))/12) AS tahun, 
              MOD(PERIOD_DIFF(DATE_FORMAT('".$tglberhenti."','%Y%m'),DATE_FORMAT('".$tglmasuk."','%Y%m')),12) AS bulan";
    //    exit("error: ".$str);
        $res=mysql_query($str);
        $bar=mysql_fetch_object($res);
        $masakerjatahun=$bar->tahun;
        $masakerjabulan=$bar->bulan;
        $harimasuk=substr($tglmasuk,8,2);
        $hariberhenti=substr($tglberhenti,6,2);
        if($hariberhenti==$harimasuk){
            $masakerjahari=0;
        }
        else if($hariberhenti>$harimasuk){
            $masakerjahari=$hariberhenti-$harimasuk;
        }
        else{
            $masakerjahari=(30-$harimasuk)+$hariberhenti;
        }    
        echo $masakerjatahun."###".$masakerjabulan."###".$masakerjahari;
    break;
    
    case 'createTable':          
        $skodept="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
        $rkodept=mysql_query($skodept);
        $bkodept=mysql_fetch_object($rkodept);
        
        $str="select * from ".$dbname.".sdm_5pesangon where kodept='".$bkodept->kodeorganisasi."' and jenis='".$jenissk."' and masakerja<".$masakerja."
              order by masakerja desc";
        $res=mysql_query($str);
        $bar=mysql_fetch_object($res);
        $banyaknya=$bar->banyaknya;
        
        $sPenghargaan="select * from ".$dbname.".sdm_5pesangon where kodept='".$bkodept->kodeorganisasi."' and jenis='Penghargaan' and masakerja<".$masakerja."
                       order by masakerja desc";
        $rPenghargaan=mysql_query($sPenghargaan);
        $hasil=mysql_fetch_object($rPenghargaan);
        $penghargaan=$hasil->banyaknya;
                
        if($jenissk=='Uang Pisah'){
            $arr="##nosurat##tanggal##namakaryawan##kodeunit##tglberhenti##masakerjatahun##masakerjabulan##masakerjahari##gajipokok##tunjanganjabatan##jenissk##method";

            $gaji=$gapok + $tunjangan;
            $tot_uangpisah=$gaji * $banyaknya;
//            exit("error: ".$banyaknya);
            $table .= "<table>";
            $table .= "<tr><td colspan=4 align=left><strong>$jenissk :</strong></td></tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['uangpisah']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=banyaknya value='".$banyaknya."' style=width:100px; onkeypress='return angka_doang(event)'> x Gaji</td>";
            $table .= "<td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['uangpisah']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_uangpisah value='".number_format($tot_uangpisah,2,'.',',')."' style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculateUangPisah();\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1564a']."</td>";
            $table .= "<td><input type=text class=myinputtextnumber id=p1564a value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculateUangPisah();\"></td>";
            $table .= "<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['uangcuti']."</td>";
            $table .= "<td><input disabled type=text class=myinputtextnumber id=jmlh_p1564a value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1564b']."</td>";
            $table .= "<td><input type=text class=myinputtextnumber id=p1564b value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculateUangPisah();\"></td>";
            $table .= "<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['ongkospulang']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=jmlh_p1564b value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4><hr></td></tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['sebelum']." ".$_SESSION['lang']['pajak']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_sblm_pajak value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
          
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." I</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif1 value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." II</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif2 value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." III</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif3 value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pajak']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_pajak value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculateUangPisah();\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4><hr></td></tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pesangon']." yang ".$_SESSION['lang']['diterima']." </td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_pesangon value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculateUangPisah();\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4 align=center><input type=hidden value=insert id=method>
                       <button class=mybutton onclick=savePesangon() >".$_SESSION['lang']['save']."</button>
                       <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button></td></tr>";
            $table .= "</table>";
        }
        else{
            $gaji=$gapok + $tunjangan;
//            exit("error: ".$gapok);
            if($jenissk=='Pesangon'){
                $jml_pesangon=$gaji*$banyaknya;
            }
            else if($jenissk=='Penghargaan'){
                $jml_pesangon=$gaji*2*$banyaknya;
            }
            $tot_penghargaan=$gaji*$penghargaan;
            
            $table .= "<table>";
            $table .= "<tr><td colspan=4 align=left><strong>$jenissk :</strong></td></tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1562']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=p1562 value='".$banyaknya."' style=width:100px;> x Gaji</td>";
            $table .= "<td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pesangon']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=jml_pesangon value='".number_format($jml_pesangon,2,'.',',')."' style=width:100px; calculatePesangon();\></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1563']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=p1563 value='".$penghargaan."' style=width:100px;> x Gaji</td>";
            $table .= "<td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['penghargaan']."</td>";
            $table .= "<td><input disabled type=text class=myinputtextnumber id=tot_penghargaan value='".number_format($tot_penghargaan,2,'.',',')."' style=width:100px; calculatePesangon();\></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1564a']."</td>";
            $table .= "<td><input type=text class=myinputtextnumber id=p1564a value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculatePesangon();\"></td>";
            $table .= "<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['uangcuti']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=jmlh_p1564a value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1564b']."</td>";
            $table .= "<td><input type=text class=myinputtextnumber id=p1564b  value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculatePesangon();\"></td>";
            $table .= "<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['ongkospulang']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=jmlh_p1564b value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td>".$_SESSION['lang']['p1564c']."</td>";
            $table .= "<td><input type=text class=myinputtextnumber id=p1564c  value=0.15 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculatePesangon();\"></td>";
            $table .= "<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['perumobat']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=jmlh_p1564c value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4><hr></td></tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['sebelum']." ".$_SESSION['lang']['pajak']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_sblm_pajak style=width:100px;></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." I</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif1_ value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." II</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif2_ value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=left>".$_SESSION['lang']['pajakprogresif']." III</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=pajakprogresif3_ value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4><hr></td></tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pajak']."</td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_pajak_ value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculatePesangon();\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4><hr></td></tr>";
            $table .= "<tr>";
            $table .= "<td colspan=3 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pesangon']." yang ".$_SESSION['lang']['diterima']." </td>";
            $table .= "<td><input type=text disabled class=myinputtextnumber id=tot_pesangon value=0 style=width:100px; onkeypress='return angka_doang(event)' onblur=\"change_number(this);calculatePesangon();\"></td>";
            $table .= "</tr>";
            $table .= "<tr><td colspan=4 align=center><input type=hidden value=insert id=method>
                       <button class=mybutton onclick=savePesangon2()>".$_SESSION['lang']['save']."</button>
                       <button class=mybutton onclick=cancelIsi2()>".$_SESSION['lang']['cancel']."</button></td></tr>";
            $table .= "</table>";
        }
        
        echo $table;
    break;
    case'insert':
        $nosurat        =$_POST['nosurat'];
        $tanggal        =$_POST['tanggal'];
        $karyawanid     =$_POST['karyawanid'];
        $kodeunit       =$_POST['kodeunit'];
        $tglberhenti    =$_POST['tglberhenti'];
        $masakerjatahun =$_POST['masakerjatahun'];
        $masakerjabulan =$_POST['masakerjabulan'];
        $masakerjahari  =$_POST['masakerjahari'];
        $gajipokok      =$_POST['gajipokok'];
        $tunjanganjabatan =$_POST['tunjanganjabatan'];
        $jenissk        =$_POST['jenissk'];
        $p1562          =$_POST['p1562'];
        $jml_pesangon   =$_POST['jml_pesangon'];
        $p1563          =$_POST['p1563'];
        $tot_penghargaan=$_POST['tot_penghargaan'];
        
        $tot_uangpisah  =$_POST['tot_uangpisah'];
        $p1564a         =$_POST['p1564a'];
        $jmlh_p1564a    =$_POST['jmlh_p1564a'];
        $p1564b         =$_POST['p1564b'];
        $jmlh_p1564b    =$_POST['jmlh_p1564b'];
        $p1564c         =$_POST['p1564c'];
        $jmlh_p1564c    =$_POST['jmlh_p1564c'];
        $tot_sblm_pajak =$_POST['tot_sblm_pajak'];
        $pajakprogresif1=$_POST['pajakprogresif1'];
        $pajakprogresif2=$_POST['pajakprogresif2'];
        $pajakprogresif3=$_POST['pajakprogresif3'];
        $tot_pajak      =$_POST['tot_pajak'];
        $tot_pesangon   =$_POST['tot_pesangon'];
        
    
    if($nosurat==''){
        exit("error: ".$_SESSION['lang']['nosurat']." tidak boleh kosong");
    }
    if($tanggal==''){
        exit("error: ".$_SESSION['lang']['tanggal']." tidak boleh kosong");
    }
    if($karyawanid==''){
        exit("error: ".$_SESSION['lang']['namakaryawan']." tidak boleh kosong");
    }
    if($tglberhenti==''){
        exit("error: ".$_SESSION['lang']['tglberhenti']." tidak boleh kosong");
    }
    $scek="select * from ".$dbname.".sdm_pesangon where nosurat='".$nosurat."' and karyawanid='".$karyawanid."' ";
    $qcek=mysql_query($scek) or die(mysql_error($conn));
    $rcek=mysql_num_rows($qcek);
    if($rcek<1){
        $sIns="insert into ".$dbname.".sdm_pesangon(nosurat,karyawanid,kodeunit,tanggal,tanggalberhenti,masakerjatahun,masakerjabulan,masakerjahari,
               upahterakhir,tunjanganjabatan,jenispesangon,p1564a,jumlahp1564a,p1564b,jumlahp1564b,
               jumlahsebelumpajak,totalterima,updateby) 
               values ('".$nosurat."','".$karyawanid."','".$kodeunit."','".tanggalsystem($tanggal)."','".tanggalsystem($tglberhenti)."','".$masakerjatahun."','".$masakerjabulan."','".$masakerjahari."',
               '".$gajipokok."','".$tunjanganjabatan."','".$jenissk."','".$p1564a."','".$jmlh_p1564a."','".$p1564b."','".$jmlh_p1564b."',
               '".$tot_sblm_pajak."','".$tot_pesangon."','".$_SESSION['standard']['userid']."')";
//        exit("error: ".$sIns);
        if(!mysql_query($sIns)){
            echo"Gagal".mysql_error($conn);
        }
        else {echo 'Done.';}
    }
    else{
        $sUpd="update ".$dbname.".sdm_pesangon set kodeunit='".$kodeunit."',
               tanggal='".$tanggal."',tanggalberhenti='".$tglberhenti."',masakerjatahun='".$masakerjatahun."',
               masakerjabulan='".$masakerjabulan."',masakerjahari='".$masakerjahari."',upahterakhir='".$gajipokok."',
               tunjanganjabatan='".$tunjanganjabatan."',jenispesangon='".$jenissk."',p1564a='".$p1564a."',jumlahp1564a='".$jmlh_p1564a."',
               p1564b='".$p1564b."',jumlahp1564b='".$jmlh_p1564b."',jumlahsebelumpajak='".$tot_sblm_pajak."',totalterima='".$tot_pesangon."',
               updateby='".$_SESSION['standard']['userid']."'
               where nosurat='".$nosurat."' and karyawanid='".$karyawanid."'";
//        exit("error: ".$sUpd);
        if(!mysql_query($sUpd)) {
            echo "DB Error : ".mysql_error();
        }
        else{ echo 'Done.';}
    }
    break;
    
    case'insert2':
        $nosurat        =$_POST['nosurat'];
        $tanggal        =$_POST['tanggal'];
        $karyawanid     =$_POST['karyawanid'];
        $kodeunit       =$_POST['kodeunit'];
        $tglberhenti    =$_POST['tglberhenti'];
        $masakerjatahun =$_POST['masakerjatahun'];
        $masakerjabulan =$_POST['masakerjabulan'];
        $masakerjahari  =$_POST['masakerjahari'];
        $gajipokok      =$_POST['gajipokok'];
        $tunjanganjabatan =$_POST['tunjanganjabatan'];
        $jenissk        =$_POST['jenissk'];        
        $p1562          =$_POST['p1562'];        
        $jml_pesangon   =$_POST['jml_pesangon'];
        $p1563          =$_POST['p1563'];        
        $tot_penghargaan=$_POST['tot_penghargaan'];
        $p1564a         =$_POST['p1564a'];
        $jmlh_p1564a    =$_POST['jmlh_p1564a'];
        $p1564b         =$_POST['p1564b'];
        $jmlh_p1564b    =$_POST['jmlh_p1564b'];
        $p1564c         =$_POST['p1564c'];
        $jmlh_p1564c    =$_POST['jmlh_p1564c'];
        $tot_sblm_pajak =$_POST['tot_sblm_pajak'];
        $pajakprogresif1_=$_POST['pajakprogresif1_'];
        $pajakprogresif2_=$_POST['pajakprogresif2_'];
        $pajakprogresif3_=$_POST['pajakprogresif3_'];
        $tot_pajak_      =$_POST['tot_pajak_'];
        $tot_pesangon   =$_POST['tot_pesangon'];
    
    if($nosurat==''){
        exit("error: ".$_SESSION['lang']['nosurat']." tidak boleh kosong");
    }
    if($tanggal==''){
        exit("error: ".$_SESSION['lang']['tanggal']." tidak boleh kosong");
    }
    if($karyawanid==''){
        exit("error: ".$_SESSION['lang']['namakaryawan']." tidak boleh kosong");
    }
    if($tglberhenti==''){
        exit("error: ".$_SESSION['lang']['tglberhenti']." tidak boleh kosong");
    }
    $scek="select * from ".$dbname.".sdm_pesangon where nosurat='".$nosurat."' and karyawanid='".$karyawanid."' ";
    $qcek=mysql_query($scek) or die(mysql_error($conn));
    $rcek=mysql_num_rows($qcek);
    if($rcek<1){
        $sIns="insert into ".$dbname.".sdm_pesangon(nosurat,karyawanid,kodeunit,tanggal,tanggalberhenti,masakerjatahun,masakerjabulan,masakerjahari,
           upahterakhir,tunjanganjabatan,jenispesangon,p1562,jumlahp1562,p1563,jumlahp1563,p1564a,jumlahp1564a,p1564b,jumlahp1564b,
           p1564c,jumlahp1564c,jumlahsebelumpajak,totalterima,updateby) 
           values ('".$nosurat."','".$karyawanid."','".$kodeunit."','".tanggalsystem($tanggal)."','".tanggalsystem($tglberhenti)."','".$masakerjatahun."','".$masakerjabulan."','".$masakerjahari."',
           '".$gajipokok."','".$tunjanganjabatan."','".$jenissk."','".$p1562."','".$jml_pesangon."','".$p1563."','".$tot_penghargaan."','".$p1564a."','".$jmlh_p1564a."',
           '".$p1564b."','".$jmlh_p1564b."','".$p1564c."','".$jmlh_p1564c."',
           '".$tot_sblm_pajak."','".$tot_pesangon."','".$_SESSION['standard']['userid']."')";
//        exit("error: ".$sIns);
        if(!mysql_query($sIns)){
            echo"Gagal".mysql_error($conn);
        }
        else {echo 'Done.';}
    }
    else{
        $sUpd="update ".$dbname.".sdm_pesangon set kodeunit='".$kodeunit."',
               tanggal='".$tanggal."',tanggalberhenti='".$tglberhenti."',masakerjatahun='".$masakerjatahun."',
               masakerjabulan='".$masakerjabulan."',masakerjahari='".$masakerjahari."',upahterakhir='".$gajipokok."',
               tunjanganjabatan='".$tunjanganjabatan."',jenispesangon='".$jenissk."',p1562='".$p1562."',jumlahp1562='".$jml_pesangon."',
               p1563='".$p1563."',jumlahp1563='".$tot_penghargaan."',p1564a='".$p1564a."',jumlahp1564a='".$jmlh_p1564a."',
               p1564b='".$p1564b."',jumlahp1564b='".$jmlh_p1564b."',p1564c='".$p1564c."',jumlahp1564c='".$jmlh_p1564c."',
               jumlahsebelumpajak='".$tot_sblm_pajak."',totalterima='".$tot_pesangon."',
               updateby='".$_SESSION['standard']['userid']."'
               where nosurat='".$nosurat."' and karyawanid='".$karyawanid."'";
//        exit("error: ".$sUpd);
        if(!mysql_query($sUpd)) {
            echo "DB Error : ".mysql_error();
        }
        else{ echo 'Done.';}
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
       
        $sCount="select count(*) as jmlhrow from ".$dbname.".sdm_pesangon order by karyawanid asc";
        $qCount=mysql_query($sCount) or die(mysql_error());
        while($rCount=mysql_fetch_object($qCount)){
            $jlhbrs= $rCount->jmlhrow;
        }
        
        $offset=$page*$limit;
//        exit("error: ".$offset);
        if($jlhbrs<($offset))$page-=1;
        $offset=$page*$limit;
        $no=$offset;
        
        echo"<table class=sortable cellspacing=1 border=0><thead>
	  <tr class=rowheader>
           <td align=center>No.</td>
           <td align=center>".$_SESSION['lang']['nosurat']."</td>
           <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
	   <td align=center>".$_SESSION['lang']['tglberhenti']."</td>
           <td align=center>".$_SESSION['lang']['masakerja']."</td>
           <td align=center>Jenis Pesangon</td>
           <td align=center>Total Pesangon</td>
           <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
        
        $str="select a.nosurat as nosurat,a.tanggal as tanggal,a.karyawanid as karyawanid,b.namakaryawan as namakaryawan,
              a.kodeunit as kodeunit,a.tanggalberhenti as tanggalberhenti,a.masakerjatahun as masakerjatahun,
              a.masakerjabulan as masakerjabulan,a.masakerjahari as masakerjahari,a.upahterakhir as upahterakhir,
              a.tunjanganjabatan as tunjanganjabatan,a.jenispesangon as jenispesangon,a.p1564a as p1564a,a.jumlahp1564a as jumlahp1564a,
              a.p1564b as p1564b,a.jumlahp1564b as jumlahp1564b,a.jumlahsebelumpajak as jumlahsebelumpajak,a.totalterima as totalterima,
              a.p1564c as p1564c,a.jumlahp1564c as jumlahp1564c,a.p1562 as p1562,a.jumlahp1562 as jumlahp1562,
              a.p1563 as p1563,a.jumlahp1563 as jumlahp1563
              from ".$dbname.".sdm_pesangon a 
              left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
              order by a.tanggalberhenti desc ";
//        exit("error: ".$str);
        $res=mysql_query($str)or die(mysql_error());
        $baris=mysql_num_rows($res);
        $no=0;
        if($baris==0){
            echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
        }
        else{
            while($bar=mysql_fetch_assoc($res))
            {
                $no+=1;	
                echo"<tr class=rowcontent>
                <td align=center>".$no."</td>
                <td>".$bar['nosurat']."</td>
                <td>".$bar['namakaryawan']."</td>
                <td>".$bar['tanggalberhenti']."</td>
                <td>".$bar['masakerjatahun']." Tahun ".$bar['masakerjabulan']." Bulan ".$bar['masakerjahari']." Hari</td>
                <td>".$bar['jenispesangon']."</td>
                <td>".number_format($bar['totalterima'])."</td>
                <td>";
                
                $jenissk=rawurlencode($bar['jenispesangon']);
//                if($bar['jenispesangon']=='Uang Pisah'){
//                    echo "<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"gedit('".$bar['nosurat']."','".$bar['tanggal']."','".$bar['karyawanid']."','".$bar['kodeunit']."','".$bar['tanggalberhenti']."','".$bar['masakerjatahun']."','".$bar['masakerjabulan']."','".$bar['masakerjahari']."','".$bar['upahterakhir']."','".$bar['tunjanganjabatan']."','".$jenissk."','".$bar['p1564a']."','".$bar['jumlahp1564a']."','".$bar['p1564b']."','".$bar['jumlahp1564b']."','".$bar['jumlahsebelumpajak']."','".$bar['totalterima']."');\">";
//                }
//                else{
//                    echo "<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"gedit('".$bar['nosurat']."','".$bar['tanggal']."','".$bar['karyawanid']."','".$bar['kodeunit']."','".$bar['tanggalberhenti']."','".$bar['masakerjatahun']."','".$bar['masakerjabulan']."','".$bar['masakerjahari']."','".$bar['upahterakhir']."','".$bar['tunjanganjabatan']."','".$jenissk."','".$bar['p1562']."','".$bar['jumlahp1562']."','".$bar['p1563']."','".$bar['jumlahp1563']."',".$bar['p1564a']."','".$bar['jumlahp1564a']."','".$bar['p1564b']."','".$bar['jumlahp1564b']."','".$bar['p1564c']."','".$bar['jumlahp1564c']."','".$bar['jumlahsebelumpajak']."','".$bar['totalterima']."');\">";
//                }
                echo "<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"gedit('".$bar['nosurat']."','".$bar['tanggal']."','".$bar['karyawanid']."','".$bar['kodeunit']."','".$bar['tanggalberhenti']."','".$bar['masakerjatahun']."','".$bar['masakerjabulan']."','".$bar['masakerjahari']."','".$bar['upahterakhir']."','".$bar['tunjanganjabatan']."','".$jenissk."','".$bar['p1562']."','".$bar['jumlahp1562']."','".$bar['p1563']."','".$bar['jumlahp1563']."','".$bar['p1564a']."','".$bar['jumlahp1564a']."','".$bar['p1564b']."','".$bar['jumlahp1564b']."','".$bar['p1564c']."','".$bar['jumlahp1564c']."','".$bar['jumlahsebelumpajak']."','".$bar['totalterima']."');\">";
                echo "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['nosurat']."','".$bar['karyawanid']."','".$bar['jenispesangon']."');\">"; 
                echo "</td></tr>";	
            }  
        }

        echo"<tr class=rowheader><td colspan=9 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=pages(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=pages(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr></table>";
    break;

    case'update':
        $sUpd="update ".$dbname.".sdm_pesangon set kodeunit='".$kodeunit."',
               tanggal='".$tanggal."',tanggalberhenti='".$tglberhenti."',masakerjatahun='".$masakerjatahun."',
               masakerjabulan='".$masakerjabulan."',masakerjahari='".$masakerjahari."',upahterakhir='".$gajipokok."',
               tunjanganjabatan='".$tunjanganjabatan."',jenispesangon='".$jenissk."',p1564a='".$p1564a."',jumlahp1564a='".$jmlh_p1564a."',
               p1564b='".$p1564b."',jumlahp1564b='".$jmlh_p1564b."',jumlahsebelumpajak='".$tot_sblm_pajak."',totalterima='".$tot_pesangon."',
               updateby='".$_SESSION['standard']['userid']."'
               where nosurat='".$nosurat."' and karyawanid='".$karyawanid."'";
//        exit("error: ".$sUpd);
        if(!mysql_query($sUpd))
        {
            echo"Gagal".mysql_error($conn);
        }
   
    break;

    case 'deletedata':
    $nosurat        =$_POST['nosurat'];
    $karyawanid     =$_POST['karyawanid'];
    $jenispesangon  =$_POST['jenispesangon'];
    $sDel="delete from ".$dbname.".sdm_pesangon 
           where nosurat='".$nosurat."' and karyawanid='".$karyawanid."' and jenispesangon='".$jenispesangon."'";
    if(mysql_query($sDel))
        echo"";
    else
        echo "DB Error : ".mysql_error($conn);                        
    break;

    default:
    break;
}

?>