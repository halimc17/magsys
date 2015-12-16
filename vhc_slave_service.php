<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$lokasi=$_SESSION['empl']['lokasitugas'];
$codeOrg=checkPostGet('codeOrg','');
$trans_no=checkPostGet('trans_no','');
$vhc_code=checkPostGet('vhc_code','');
$kdTraksi=checkPostGet('kdTraksi','');
$tgl_ganti=  tanggalsystemn(checkPostGet('tgl_ganti',''));
$tgl_keluar=  tanggalsystemn(checkPostGet('tgl_keluar',''));
$dwnTime=checkPostGet('dwnTime','');
$kmmasuk=checkPostGet('kmmasuk','');
$kmkeluar=checkPostGet('kmkeluar','');
$descDmg=htmlentities(checkPostGet('descDmg',''));
$terlambat=htmlentities(checkPostGet('terlambat',''));
$kdTraksiDt=makeOption($dbname,'vhc_5master','kodevhc,kodetraksi');
$usr_id=$_SESSION['standard']['userid'];
$nodok=checkPostGet('nodok','');
$kdTraksiDt=makeOption($dbname,'vhc_5master','kodevhc,kodetraksi');

$kodeBarang=checkPostGet('kodeBarang','');
$jumlahBarang=checkPostGet('jumlahBarang','');
$keteranganBarang=checkPostGet('keteranganBarang','');
$satuanBarang=checkPostGet('satuanBarang','');
$karyawan=checkPostGet('karyawan','');

$namaBarangCari=checkPostGet('namaBarangCari','');

$jenisVhc=makeOption($dbname,'vhc_5master','kodevhc,jenisvhc');  
//$nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik',$whKar);
//$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
//$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
//$satBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',$whBrg);

$schTran=checkPostGet('schTran','');
$schTgl=tanggalsystemn(checkPostGet('schTgl',''));
$schRef=checkPostGet('schRef','');

// Get Nik dan Nama
$nikKar = $nmKar = array();
$qKary = selectQuery($dbname,'datakaryawan','karyawanid,namakaryawan,nik');
$resKary = fetchData($qKary);
foreach($resKary as $row) {
	$nikKar[$row['karyawanid']] = $row['nik'];
	$nmKar[$row['karyawanid']] = $row['namakaryawan'];
}

// Get Nama dan Satuan Barang
$nmBrg = $satBrg = array();
$qBrg = selectQuery($dbname,'log_5masterbarang','kodebarang,namabarang,satuan');
$resBrg = fetchData($qBrg);
foreach($resBrg as $row) {
	$nmBrg[$row['kodebarang']] = $row['namabarang'];
	$satBrg[$row['kodebarang']] = $row['satuan'];
}


if($schTgl=='--')
{
    $schTgl='';
}

switch($proses)
{
    
    case'getKm':
        $iKm="select * from ".$dbname.".vhc_kmhm_track where kodevhc='".$vhc_code."' order by kmhmakhir desc";
        $nKm=  mysql_query($iKm) or die (mysql_error($conn));
        $dKm=  mysql_fetch_assoc($nKm);
           // echo $dKm['kmhmakhir'];
            
        if($dKm['kmhmakhir']=='' || $dKm['kmhmakhir']=='0')
        {
            $dsb="0";
        }
        else
        {
            $dsb="1";
        }
            
            
            
            echo $dKm['kmhmakhir']."###".$dsb;
            
    break;
    
    
    
    case'generate_no':
        //lokasi tugas/y/m/no urut (4)
        if(!empty($notransaksi))
        {
                echo $notransaksi;
        }
        else
        {	
            $tgl=  date('Ymd');
            $bln = substr($tgl,4,2);
            $thn = substr($tgl,0,4);

            $notransaksi=$codeOrg."/".date('Y')."/".date('m')."/";
            $ql="select `notransaksi` from ".$dbname.".`vhc_penggantianht` where notransaksi like '%".$notransaksi."%' order by `notransaksi` desc limit 0,1";
            $qr=mysql_query($ql) or die(mysql_error());
            $rp=mysql_fetch_object($qr);
            setIt($rp->notransaksi,'');
            $awal=substr($rp->notransaksi,-4,4);
            $awal=intval($awal);
            $cekbln=substr($rp->notransaksi,-7,2);
            $cekthn=substr($rp->notransaksi,-12,4);
            if(($bln!=$cekbln)&&($thn!=$cekthn)) {
                    $awal=1;
            } else {
                $awal++;
            }
            $counter=addZero($awal,4);
            $notransaksi=$codeOrg."/".$thn."/".$bln."/".$counter;
        echo $notransaksi;
        }
    break;
        
    case'getVhc':
        $svhc="select distinct kodevhc, tahunperolehan from ".$dbname.".vhc_5master where kodetraksi='".$_POST['kdTraksi']."' order by kodevhc asc";
        //exit("error:".$svhc);
         $optVhc.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $qvhc=mysql_query($svhc) or die(mysql_error($conn));
        while($rvhc=mysql_fetch_assoc($qvhc)){
            if($_POST['kdVhc']==$rvhc['kodevhc']){
                    $optVhc.="<option value='".$rvhc['kodevhc']."' selected>".$rvhc['kodevhc']." [".$rvhc['tahunperolehan']."]</option>";
            }else{
                    $optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']." [".$rvhc['tahunperolehan']."]</option>";
            }
        }
        echo $optVhc;
     break;   
        
    case'insert':
        $iSave="insert into ".$dbname.".vhc_penggantianht (`kodeorg`,`kodevhc`,`tanggal`,`updateby`,`notransaksi`,
              `downtime`, `kerusakan`,`noreferensi`,`tanggalkeluar`,`kmmasuk`,`kmkeluar`,`terlambat`) values 
        ('".$codeOrg."','".$vhc_code."','".$tgl_ganti."','".$usr_id."','".$trans_no."','".$dwnTime."','".$descDmg."',
            '".$nodok."','".$tgl_keluar."','".$kmmasuk."','".$kmkeluar."','".$terlambat."')";
        //exit("Error:$iSave");
        if(mysql_query($iSave)) {
			updateKmHm($vhc_code);
		} else {
			echo "DB Error : ".mysql_error($conn);
		}
    break;
    
    case'update':
        $iUpdate="update ".$dbname.".vhc_penggantianht set  noreferensi='".$nodok."',tanggal='".$tgl_ganti."',
                  tanggalkeluar='".$tgl_keluar."',downtime='".$dwnTime."',kmmasuk='".$kmmasuk."',kmkeluar='".$kmkeluar."',
                  kerusakan='".$descDmg."',terlambat='".$terlambat."' 
                  where  notransaksi='".$trans_no."' and kodeorg='".$codeOrg."' and kodevhc='".$vhc_code."' ";  

        if(mysql_query($iUpdate)) {
			updateKmHm($vhc_code);
		} else {
			echo "DB Error : ".mysql_error($conn);
		}
    break;
    
    case'delete':
		$qTrans = selectQuery($dbname,'vhc_penggantianht',"kodevhc",
							  "notransaksi='".$trans_no."'");
		$resTrans = fetchData($qTrans);
		
        $iDelete="delete from ".$dbname.".vhc_penggantianht where  notransaksi='".$trans_no."'";
        if(mysql_query($iDelete)) {
			updateKmHm($resTrans[0]['kodevhc']);
		} else {
			echo "DB Error : ".mysql_error($conn);
		}
    break;
    
    case'getListBarang':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>
  
                    <table id=listCariBarang >
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['kodebarang']."</td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>".$_SESSION['lang']['satuan']."</td>
                    </tr></thead>";
                        
                    if($namaBarangCari=='')
                    {
                       
                    }
                    else
                    {
                        
                        $i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
                       
                        $n=mysql_query($i) or die (mysql_error($conn));
                        while ($d=mysql_fetch_assoc($n))
                        {
                           
                            
                            $whBrg="kodebarang='".$d['kodebarang']."'";
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$dHarga['hargarata']."');\">
                                    <td>".$no."</td>
                                    <td>".$d['kodebarang']."</td>
                                    <td>".$nmBrg[$d['kodebarang']]."</td>
                                    <td>".$satBrg[$d['kodebarang']]."</td>
                                    
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
    break; 
    
    case 'saveBarang':
		$iBarang="insert into ".$dbname.".vhc_penggantiandt (`notransaksi`,`kodebarang`,`satuan`,`jumlah`,`keterangan`)
		values ('".$trans_no."','".$kodeBarang."','".$satuanBarang."','".$jumlahBarang."','".$keteranganBarang."')";
		if(mysql_query($iBarang))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
    break;
	
    case 'loadDetailBarang':

        $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
        <thead>
        <tr class=rowheader>
        <td>".$_SESSION['lang']['nourut']."</td>
        <td>".$_SESSION['lang']['kodebarang']."</td>
        <td>".$_SESSION['lang']['namabarang']."</td>
        <td>".$_SESSION['lang']['satuan']."</td>
        <td>".$_SESSION['lang']['jumlah']."</td>
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>".$_SESSION['lang']['action']."</td></tr></thead>";
        $no=0;
        $iListBarang="select * from ".$dbname.".vhc_penggantiandt where notransaksi='".$trans_no."' ";

        $nListBarang=mysql_query($iListBarang) or die(mysql_error());
        while($dListBarang=mysql_fetch_assoc($nListBarang))
        {
            $whBrg="kodebarang='".$dListBarang['kodebarang']."'";
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=right>".$dListBarang['kodebarang']."</td>";
                $tab.="<td align=left>".$nmBrg[$dListBarang['kodebarang']]."</td>";
                $tab.="<td align=left>".$dListBarang['satuan']."</td>";
                $tab.="<td align=right>".$dListBarang['jumlah']."</td>";
                $tab.="<td align=left>".$dListBarang['keterangan']."</td>";
                $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                        onclick=\"deleteBarang('".$dListBarang['notransaksi']."','".$dListBarang['kodebarang']."');\" ></td>";
        }
        $tab.="</table>";
        echo $tab;
    break;
        
    case 'deleteBarang':
            $iDelBarang="delete from ".$dbname.".vhc_penggantiandt where notransaksi='".$trans_no."' and kodebarang='".$kodeBarang."' ";
            if(mysql_query($iDelBarang))
            {
            }
            else
            {
                    echo " Gagal,".addslashes(mysql_error($conn));
            }			
    break;	
        
        
    case'loadData':
        echo"
        <table cellspacing=1 border=0 class=sortable>
        <thead>
        <tr class=rowheader>
            <td align=center>No.</td>
            <td align=center>".$_SESSION['lang']['notransaksi']."</td>
            <td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
            <td align=center>".$_SESSION['lang']['tanggalkeluar']."</td>    
            <td align=center>".$_SESSION['lang']['jenisvch']."</td>    
            <td align=center>".$_SESSION['lang']['kodevhc']."</td>
            <td align=center>".$_SESSION['lang']['downtime']."</td>
            <td align=center>*</td>    
        </tr>
        </thead>
        <tbody>
        ";
		
        $sch = "";
        if($schTran!='')
        {
            $sch.=" and notransaksi like '%".$schTran."%' ";
        }
        if($schTgl!='')
        {
            $sch.=" and tanggal='".$schTgl."'";
        }
        if($schRef!='')
        {
            $sch.=" and noreferensi like '%".$schRef."%' ";
        }




        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_penggantianht where kodeorg like '%".substr($_SESSION['empl']['lokasitugas'],0,4)."%'  ".$sch." order by `notransaksi` desc";// echo $ql2;notran

        //exit("Error:$ql2");
        $query2=mysql_query($ql2) or die(mysql_error());
        while($jsl=mysql_fetch_object($query2)){
        $jlhbrs= $jsl->jmlhrow;
        }



        $iList="select * from ".$dbname.".vhc_penggantianht where  kodeorg like '%".substr($_SESSION['empl']['lokasitugas'],0,4)."%' ".$sch."  order by `notransaksi` desc limit ".$offset.",".$limit."";
        //exit("Error:$iList");
        $nList=mysql_query($iList) or die(mysql_error());
        $no=$maxdisplay;
        while($dList=mysql_fetch_assoc($nList))
        {
            //$whOrg="kodeorganisasi='".$dList['mesin']."'";

            $no+=1;
            echo"
            <tr class=rowcontent>
            <td align=center>".$no."</td>
            <td>".$dList['notransaksi']."</td>
            <td>".tanggalnormal($dList['tanggal'])."</td>    
            <td>".tanggalnormal($dList['tanggalkeluar'])."</td>        
            <td>".$jenisVhc[$dList['kodevhc']]."</td>
            <td>".$dList['kodevhc']."</td>
            <td>".$dList['downtime']."</td>  ";

            if($dList['posting']==1)
            {
            echo" <td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$dList['notransaksi'].",".$dList['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"> Posted</td>";    
            }
            else
            {
            
           echo" <td align=center>";//'".tanggalnormal(substr($dList['jammulai'],0,10))."'
//kodeorg	kodevhc	tanggal	noreferensi	tanggalkeluar	updateby	updatetime	notransaksi	posting	postingby	downtime	kmmasuk	kmkeluar		terlambat
//codeOrg,trans_no,kdTraksi,vhc_code,nodok,tgl_ganti,tgl_keluar,dwnTime,kmmasuk,kmkeluar,descDmg,
            echo"<img src=images/application/application_edit.png class=resicon  title='Edit' 
                 onclick=\"fillField('".$dList['kodeorg']."','".$dList['notransaksi']."',
                 '".$kdTraksiDt[$dList['kodevhc']]."','".$dList['kodevhc']."','".$dList['noreferensi']."',
                 '".tanggalnormal($dList['tanggal'])."','".tanggalnormal($dList['tanggalkeluar'])."','".$dList['downtime']."',
                 '".$dList['kmmasuk']."','".$dList['kmkeluar']."','".nl2br($dList['kerusakan'])."','".$dList['terlambat']."');\">        
            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteHead('".$dList['notransaksi']."');\" >
            <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$dList['notransaksi'].",".$dList['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"></td>
            </td></tr>";
            }
        }
        echo"
        <tr class=rowheader><td colspan=12 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr>";
        echo"</tbody></table>";
        break;
            
        #karyawan
        case 'saveKaryawan':
            $iKaryawan="insert into ".$dbname.".vhc_penggantiandt_karyawan (`notransaksi`,`karyawanid`,`updateby`)
            values ('".$trans_no."','".$karyawan."','".$_SESSION['standard']['userid']."')";
            
            if(mysql_query($iKaryawan))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
	break;
        
        case 'deleteKaryawan':
            $iDelPekerjaan="delete from ".$dbname.".vhc_penggantiandt_karyawan where notransaksi='".$trans_no."' 
                and karyawanid='".$karyawan."' ";
            if(mysql_query($iDelPekerjaan))
            {
            }
            else
            {
                echo " Gagal,".addslashes(mysql_error($conn));
            }			
        break;
        
	case 'loadDetailKaryawan':
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['nik']."</td>
            <td>".$_SESSION['lang']['namakaryawan']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListKaryawan="select * from ".$dbname.".vhc_penggantiandt_karyawan where notransaksi='".$trans_no."' ";
         
            $nListKaryawan=mysql_query($iListKaryawan) or die(mysql_error());
            while($dListKaryawan=mysql_fetch_assoc($nListKaryawan))
            {
                $whKar="karyawanid='".$dListKaryawan['karyawanid']."'";
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=right>".$nikKar[$dListKaryawan['karyawanid']]."</td>";
                $tab.="<td align=left>".$nmKar[$dListKaryawan['karyawanid']]."</td>";
                $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteKaryawan('".$dListKaryawan['notransaksi']."','".$dListKaryawan['karyawanid']."');\" ></td>";
                
            }
            $tab.="</table>";
            echo $tab;
	break;        
        
        
   default;
}


function updateKmHm($kodevhc) {
	global $dbname;
	
	// Get KM/HM Akhir
	$qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'");
	$resKm = fetchData($qKm);
	$kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];
	
	$dataIns = array($kodevhc,$kmhmAkhir);
	$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
	if(!mysql_query($qIns)) {
		$dataUpd = array('kmhmakhir'=>$kmhmAkhir);
		$qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,
							"kodevhc='".$kodevhc."'");
		if(!mysql_query($qUpd)) {
			exit("Update KM/HM Error: ".mysql_error());
		}
	}
}