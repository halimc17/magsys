<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method=$_POST['method'];	

$optNm=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNik=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optDiv=makeOption($dbname, 'sdm_5departemen','kode,nama');

$whBrg='';
$whKar='';
$whOrg='';
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
$satBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',$whBrg);
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
$nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik',$whKar);
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whOrg);
/*$jam1=$_POST['jm1'].":".$_POST['mn1'].":00";
//exit("Error:$jam1");
$jam2=$_POST['jm2'].":".$_POST['mn2'].":00";*/



//$nodok=$_POST['nodok'];
$nodok=checkPostGet('nodok','');

//$tglOrder=tanggalsystemn($_POST['tglOrder']);
$tglOrder=tanggalsystemn(checkPostGet('tglOrder',''));

$tglOrderDok=tanggalsystem(checkPostGet('tglOrder',''));
//$jmOrder=$_POST['jmOrder'];
$jmOrder=checkPostGet('jmOrder','');

//$mnOrder=$_POST['mnOrder'];
$mnOrder=checkPostGet('mnOrder','');

$waktuOrder=$jmOrder.":".$mnOrder.":00";

//$namaPemohon=$_POST['namaPemohon'];
$namaPemohon=checkPostGet('namaPemohon','');

//$statusPemohon=$_POST['statusPemohon'];
$statusPemohon=checkPostGet('statusPemohon','');

//$pabrik=$_POST['pabrik'];
$pabrik=checkPostGet('pabrik','');


//$station=$_POST['station'];
$station=checkPostGet('station','');

//$mesin=$_POST['mesin'];
$mesin=checkPostGet('mesin','');

//$shift=$_POST['shift'];
$shift=checkPostGet('shift','');

//$tipePerbaikan=$_POST['tipePerbaikan'];
$tipePerbaikan=checkPostGet('tipePerbaikan','');

//$uraianKerusakan=$_POST['uraianKerusakan'];
$uraianKerusakan=checkPostGet('uraianKerusakan','');

//$tglMulai=tanggalsystemn($_POST['tglMulai']);
$tglMulai=tanggalsystemn(checkPostGet('tglMulai',''));


//$jmMulai=$_POST['jmMulai'];
$jmMulai=checkPostGet('jmMulai','');

//$mnMulai=$_POST['mnMulai'];
$mnMulai=checkPostGet('mnMulai','');

$waktuMulai=$tglMulai." ".$jmMulai.":".$mnMulai.":00";

//$tglSelesai=tanggalsystemn($_POST['tglSelesai']);
$tglSelesai=tanggalsystemn(checkPostGet('tglSelesai',''));

//$jmSelesai=$_POST['jmSelesai'];
$jmSelesai=checkPostGet('jmSelesai','');

//$mnSelesai=$_POST['mnSelesai'];
$mnSelesai=checkPostGet('mnSelesai','');

$waktuSelesai=$tglSelesai." ".$jmSelesai.":".$mnSelesai.":00";


//$jumlahJamPerbaikan=$_POST['jumlahJamPerbaikan'];
$jumlahJamPerbaikan=checkPostGet('jumlahJamPerbaikan','');

//$statusKetuntasann=$_POST['statusKetuntasan'];
$statusKetuntasan=checkPostGet('statusKetuntasan','');

//$hasilKerja=$_POST['hasilKerja'];
$hasilKerja=checkPostGet('hasilKerja','');

//$namaBarangCari=$_POST['namaBarangCari'];
$namaBarangCari=checkPostGet('namaBarangCari','');

#barang
//$kodeBarang=$_POST['kodeBarang'];
$kodeBarang=checkPostGet('kodeBarang','');

//$jumlahBarang=$_POST['jumlahBarang'];
$jumlahBarang=checkPostGet('jumlahBarang','');

//$keteranganBarang=$_POST['keteranganBarang'];
$keteranganBarang=checkPostGet('keteranganBarang','');

//$satuanBarang=$_POST['satuanBarang'];
$satuanBarang=checkPostGet('satuanBarang','');

//$hargabarang=$_POST['hargabarang'];
$hargabarang=checkPostGet('hargabarang','');


#karyawan
//$karyawan=$_POST['karyawan'];
$karyawan=checkPostGet('karyawan','');


#pekerjaan
//$nomor=$_POST['nomor'];
$nomor=checkPostGet('nomor','');

//$rincian=$_POST['rincian'];
$rincian=checkPostGet('rincian','');

//$kondisi=$_POST['kondisi'];
$kondisi=checkPostGet('kondisi','');


//$schNodok=$_POST['schNodok'];
$schNodok=checkPostGet('schNodok','');

//$schTgl=  tanggalsystemn($_POST['schTgl']);

$schTgl=tanggalsystemn(checkPostGet('schTgl',''));


#komentar ketinggalan
//$komMain=$_POST['komMain'];

$komMain=checkPostGet('komMain','');

//$komPros=$_POST['komPros'];

$komPros=checkPostGet('komPros','');

if($schTgl=='--')
{
    $schTgl='';
}
	
#kondisi opt
$arrKondisi=array('normal'=>'Normal','perbaikan'=>'Perlu Perbaikan','rusak'=>'Rusak');



switch($method)
{


    case'getListBarang':
	
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=cariListBarang('".$kegiatan."','".$kodeproject."')>cari</button>
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
                            <td hidden>".$_SESSION['lang']['harga']."</td>    
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
                            $iHarga="select hargarata,periode from ".$dbname.".log_5saldobulanan "
                                    . " where kodebarang='".$d['kodebarang']."' and"
                                    . " kodegudang like '%".$pabrik."%' and periode like '%".substr($tglOrder,0,7)."%' ";
                            $nHarga=  mysql_query($iHarga) or die (mysql_error($conn));
                            $dHarga=  mysql_fetch_assoc($nHarga);
							if($dHarga['hargarata']<=0){
								$iHarga="select hargalastout as hargarata from ".$dbname.".log_5masterbarangdt "
										. " where kodebarang='".$d['kodebarang']."' and kodegudang like '%".$pabrik."%' ";
								$nHarga=  mysql_query($iHarga) or die (mysql_error($conn));
								$dHarga=  mysql_fetch_assoc($nHarga);
							}
                            
                            $whBrg="kodebarang='".$d['kodebarang']."'";
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$dHarga['hargarata']."');\">
                                    <td>".$no."</td>
                                    <td>".$d['kodebarang']."</td>
                                    <td>".$nmBrg[$d['kodebarang']]."</td>
                                    <td>".$satBrg[$d['kodebarang']]."</td>
                                    <td hidden>".$dHarga['hargarata']."</td>    
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;  
    
    
     ########### case insert header
    case 'insert':  //$komMain=$_POST['komMain']; //$komPros=$_POST['komPros'];
      

            $iSave="insert into ".$dbname.".pabrik_rawatmesinht (`notransaksi`, `pabrik`, `tanggal`, `jam`,
                    `shift`, `statasiun`, `mesin`, `kegiatan`, `jammulai`, `jamselesai`, `updateby`,
                    `namapemohon`, `statuspemohon`, `tipeperbaikan`,
                    `jumlahjamperbaikan`, `statusketuntasan`, `hasilkerja`,`komentarmainten`,`komentarproses`) 
            values ('".$nodok."','".$pabrik."','".$tglOrder."','".$waktuOrder."',
                    '".$shift."','".$station."','".$mesin."','".$uraianKerusakan."','".$waktuMulai."','".$waktuSelesai."',
                    '".$_SESSION['standard']['userid']."','".$namaPemohon."','".$statusPemohon."','".$tipePerbaikan."',
                    '".$jumlahJamPerbaikan."','".$statusKetuntasan."','".$hasilKerja."','".$komMain."','".$komPros."')";
                    
        if(mysql_query($iSave))//'".$_SESSION['standard']['userid']."'
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
	break;
        
    case'update':
        //exit("Error:MASUK");
        $iUpdate="update ".$dbname.".pabrik_rawatmesinht set namapemohon='".$namaPemohon."',statuspemohon='".$statusPemohon."',
                  shift='".$shift."',tipeperbaikan='".$tipePerbaikan."',kegiatan='".$uraianKerusakan."',jammulai='".$waktuMulai."',
                  jamselesai='".$waktuSelesai."',jumlahjamperbaikan='".$jumlahJamPerbaikan."',statusketuntasan='".$statusKetuntasan."',
                  hasilkerja='".$hasilKerja."',komentarmainten='".$komMain."',komentarproses='".$komPros."' 
                  where notransaksi='".$nodok."'";
        //exit("Error:$iUpdate");
        if(mysql_query($iUpdate))
        echo"";
        else
        echo " Gagal,".addslashes(mysql_error($conn));
    break;
    
    case'getNodok':
        
        $iList="select notransaksi,tanggal,statasiun  from ".$dbname.".pabrik_rawatmesinht where statasiun ='".$station."' "
            . "and tanggal='".$tglOrder."' order by notransaksi desc limit 1";
      
        $nList=  mysql_query($iList) or die (mysql_error($conn));
        $dList=  mysql_fetch_assoc($nList);
       
            
        if($dList['notransaksi']!='')
        {
            $listDok=  explode('/', $dList['notransaksi']);
            $noUrut=$listDok[2]+1;
        }
        else
        {
            $noUrut=1;
        }
        $counter=addZero($noUrut,4);
        $noDok=$station.'/'.$tglOrderDok.'/'.$counter;
        echo $noDok;
        
    break;
    
    case'getMesin':
        $optMesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optMesin.="<option value='Others'>Others</option>";
        $iMesin="select * from ".$dbname.".organisasi where induk='".$station."' ";
        $nMesin=  mysql_query($iMesin) or die (mysql_error($conn));
        while($dMesin=  mysql_fetch_assoc($nMesin))
        {
            if($mesin==$dMesin['kodeorganisasi'])
            {$select="selected=selected";}
            else
            {$select="";}
           
            $optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">".$dMesin['namaorganisasi']."</option>";
        }
        echo $optMesin;
    break;
    
    
    case'loadData':
       //exit("Error:ASDASDAS");
            echo"
            <table cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center rowspan=2>No.</td>
            <td align=center rowspan=2>No. Transaksi</td>
            <td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['pabrik']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['station']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['mesin']."</td>
             <td align=center rowspan=2>".$_SESSION['lang']['status']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['action']."</td>
            </tr>
            <tr>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
            </tr>
            </thead>
            <tbody>
            ";//<td align=center>".$_SESSION['lang']['kdpabrik']."</td>

           //exit("Error:$schTgl");
            $nodokSch='';
            if($schNodok!='')
            {
                $nodokSch="and notransaksi='".$schNodok."' ";
            }
            $tglSch='';
            if($schTgl!='')
            {
                $tglSch="and tanggal like '%".$schTgl."%' ";
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
            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_rawatmesinht where pabrik='".$_SESSION['empl']['lokasitugas']."' ".$nodokSch." ".$tglSch." order by `notransaksi` desc";// echo $ql2;notran
            //exit("Error:$ql2");
            $query2=mysql_query($ql2) or die(mysql_error());
            while($jsl=mysql_fetch_object($query2)){
            $jlhbrs= $jsl->jmlhrow;
            }
			
            $iList="select * from ".$dbname.".pabrik_rawatmesinht where  pabrik='".$_SESSION['empl']['lokasitugas']."' ".$nodokSch." ".$tglSch."  order by `tanggal` desc, `notransaksi` desc limit ".$offset.",".$limit."";
           
//exit("Error:$iList");
            $nList=mysql_query($iList) or die(mysql_error());
            $no=$maxdisplay;
            while($dList=mysql_fetch_assoc($nList))
            {
                $whOrg="kodeorganisasi='".$dList['mesin']."'";
                setIt($nmOrg[$dList['mesin']],'');

                
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td align=center>".$no."</td>
                <td>".$dList['notransaksi']."</td>
                <td>".tanggalnormal($dList['tanggal'])."</td>    
                <td>".$dList['pabrik']."</td>
                <td>".$nmOrg[$dList['pabrik']]."</td>
                <td>".@$dList['statasiun']."</td>
                <td>".@$nmOrg[$dList['statasiun']]."</td>
                <td>".$dList['mesin']."</td>
                <td>".$nmOrg[$dList['mesin']]."</td>  
                    
                <td>".$dList['statusketuntasan']."</td>  

                <td align=center>";

                echo"<img src=images/application/application_edit.png class=resicon  title='Edit' 
                     onclick=\"fillField('".$dList['notransaksi']."','".tanggalnormal($dList['tanggal'])."',
                     '".substr($dList['jam'],0,2)."','".substr($dList['jam'],3,2)."','".$dList['namapemohon']."',
                     '".$dList['statuspemohon']."','".$dList['pabrik']."','".$dList['statasiun']."','".$dList['mesin']."',
                     '".$dList['shift']."','".$dList['tipeperbaikan']."','".str_replace("\n",'<br />',$dList['kegiatan'])."',
                     '".tanggalnormal(substr($dList['jammulai'],0,10))."','".substr($dList['jammulai'],11,2)."',
                     '".substr($dList['jammulai'],14,2)."',
                     '".tanggalnormal(substr($dList['jamselesai'],0,10))."','".substr($dList['jamselesai'],11,2)."',
                     '".substr($dList['jamselesai'],14,2)."','".$dList['jumlahjamperbaikan']."',
                     '".$dList['statusketuntasan']."','".str_replace("\n",'<br />',$dList['hasilkerja'])."','".$nmOrg[$dList['mesin']]."',
                     '".str_replace("\n",'<br />',$dList['komentarmainten'])."','".str_replace("\n",'<br />',$dList['komentarproses'])."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteHead('".$dList['notransaksi']."');\" >
                <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$dList['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\">
                </td></tr>";//,`komentarmainten`,`komentarproses`
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
		
		
	##########case delete
	case 'deleteHead':
		$iDel="delete from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$nodok."' ";
		if(mysql_query($iDel))
		{
		}
		else
		{
                    echo " Gagal,".addslashes(mysql_error($conn));
		}			
	break;
	
	
	
	########### case insert detail
	case 'saveBarang':
		$iBarang="insert into ".$dbname.".pabrik_rawatmesindt (`notransaksi`,`kodebarang`,`satuan`,`jumlah`,`keterangan`,`harga`)
		values ('".$nodok."','".$kodeBarang."','".$satuanBarang."','".$jumlahBarang."','".$keteranganBarang."','".$hargabarang."')";
		if(mysql_query($iBarang))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;
		
        
        
	#####LOAD DETAIL DATA	
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
            $iListBarang="select * from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$nodok."' ";
            
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
                $iDelBarang="delete from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$nodok."' and kodebarang='".$kodeBarang."' ";
                if(mysql_query($iDelBarang))
                {
                }
                else
                {
                        echo " Gagal,".addslashes(mysql_error($conn));
                }			
        break;	
        
        
        ##pekerjaan
        case 'savePekerjaan':
            $iKaryawan="insert into ".$dbname.".pabrik_rawatmesindt_pekerjaan (`notransaksi`,`nomor`,`rincian`,`kondisi`,`updateby`)
            values ('".$nodok."','".$nomor."','".$rincian."','".$kondisi."','".$_SESSION['standard']['userid']."')";
            if(mysql_query($iKaryawan))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
	break;
        
     
	case 'loadDetailPekerjaan':
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['uraiankerusakan']."</td>
            <td>".$_SESSION['lang']['kondisi']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListPekerjaan="select * from ".$dbname.".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."'"
                    . " order by nomor asc ";
         
            $nListPekerjaan=mysql_query($iListPekerjaan) or die(mysql_error($conn));
            while($dListPekerjaan=mysql_fetch_assoc($nListPekerjaan))
            {
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=right>".$dListPekerjaan['nomor']."</td>";
                    $tab.="<td align=left>".$dListPekerjaan['rincian']."</td>";
                    $tab.="<td align=left>".$arrKondisi[$dListPekerjaan['kondisi']]."</td>";
                    $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                            onclick=\"deletePekerjaan('".$dListPekerjaan['notransaksi']."','".$dListPekerjaan['nomor']."');\" ></td>";
            }
            $tab.="</table>";
            echo $tab;
	break;//
        
        case 'deletePekerjaan':
            $iDelPekerjaan="delete from ".$dbname.".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."' and nomor='".$nomor."' ";
            if(mysql_query($iDelPekerjaan))
            {
            }
            else
            {
                echo " Gagal,".addslashes(mysql_error($conn));
            }			
        break;
        
        #karyawan
        case 'saveKaryawan':
            $iKaryawan="insert into ".$dbname.".pabrik_rawatmesindt_karyawan (`notransaksi`,`karyawanid`,`updateby`)
            values ('".$nodok."','".$karyawan."','".$_SESSION['standard']['userid']."')";
            if(mysql_query($iKaryawan))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));
	break;
        
        case 'deleteKaryawan':
            $iDelPekerjaan="delete from ".$dbname.".pabrik_rawatmesindt_karyawan where notransaksi='".$nodok."' 
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
            $iListKaryawan="select * from ".$dbname.".pabrik_rawatmesindt_karyawan where notransaksi='".$nodok."' ";
          
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
        
        

	
	
}
?>	